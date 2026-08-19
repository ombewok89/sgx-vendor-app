<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Services\AuditService;
use App\Services\FonnteService;
use App\Services\WhatsAppTemplateService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    /**
     * Test gateway connection / token status with Fonnte.
     * Admin/Superuser only.
     */
    public function testConnection(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang dapat menguji WhatsApp Gateway.',
            ], 403);
        }

        $result = FonnteService::testConnection();

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => [
                'http_status' => $result['http_status'],
                'device'      => $result['device'],
                'enabled'     => FonnteService::isEnabled(),
            ],
        ]);
    }

    /**
     * Send a test WhatsApp message using Fonnte.
     * Admin/Superuser only.
     */
    public function sendTestMessage(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang dapat mengirim pesan uji coba.',
            ], 403);
        }

        $request->validate([
            'phone'        => 'required|string',
            'template_key' => 'nullable|string',
            'custom_text'  => 'nullable|string|max:1000',
        ]);

        $templateKey = $request->template_key ?: 'TEST_MESSAGE';
        $params = [
            'user_name'     => $user->name,
            'date'          => now()->translatedFormat('d F Y, H:i') . ' WIB',
            'custom_message'=> $request->custom_text,
        ];

        // Idempotency key for test (per user per minute)
        $idempotencyKey = 'TEST_MESSAGE:' . $user->id . ':' . now()->format('YmdHi');

        $result = FonnteService::sendTemplatedMessage(
            $request->phone,
            $templateKey,
            $params,
            $idempotencyKey,
            'USER',
            $user->id
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => [
                'target_masked' => FonnteService::maskPhone($request->phone),
                'http_status'   => $result['status'] ?? null,
                'skipped'       => $result['skipped'] ?? false,
            ],
        ], $result['success'] ? 200 : 400);
    }

    /**
     * List WhatsApp notification logs.
     * Admin/Superuser only.
     */
    public function logs(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang dapat melihat log WhatsApp.',
            ], 403);
        }

        $query = NotificationLog::where('channel', 'WHATSAPP')->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('failure_type')) {
            $query->where('failure_type', $request->failure_type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('recipient', 'LIKE', "%{$s}%")
                  ->orWhere('message_type', 'LIKE', "%{$s}%")
                  ->orWhere('payload', 'LIKE', "%{$s}%")
                  ->orWhere('error_message', 'LIKE', "%{$s}%");
            });
        }

        $limit = min((int) ($request->limit ?? 50), 100);
        $logs = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data'    => $logs,
        ]);
    }

    /**
     * Retry sending a failed notification.
     * Admin/Superuser only.
     */
    public function retry(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang dapat melakukan retry notifikasi.',
            ], 403);
        }

        $log = NotificationLog::findOrFail($id);

        if ($log->status === 'SENT') {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ini sudah berstatus TERKIRIM sebelumnya.',
                'data'    => $log,
            ]);
        }

        $payload = json_decode($log->payload, true) ?? [];
        $text = $payload['text'] ?? '';

        if (empty($text)) {
            return response()->json([
                'success' => false,
                'message' => 'Isi pesan tidak ditemukan pada log ini.',
            ], 422);
        }

        // Retry without creating duplicate log record (pass existingLogId)
        $result = FonnteService::sendMessage(
            $log->recipient,
            $text,
            $log->message_type,
            $log->idempotency_key,
            $log->reference_type,
            $log->reference_id,
            $log->id
        );

        AuditService::log($user, 'RETRY_WHATSAPP_NOTIFICATION', 'NOTIFICATION_LOG', $log->id, [
            'previous_status' => 'FAILED',
            'attempts'        => $log->attempts,
        ], [
            'new_status' => $result['success'] ? 'SENT' : 'FAILED',
            'result'     => $result['message'],
        ]);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => $log->fresh(),
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Gateway stats for monitoring dashboard.
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak.',
            ], 403);
        }

        $totalSent    = NotificationLog::where('channel', 'WHATSAPP')->where('status', 'SENT')->count();
        $totalFailed  = NotificationLog::where('channel', 'WHATSAPP')->where('status', 'FAILED')->count();
        $totalSkipped = NotificationLog::where('channel', 'WHATSAPP')->where('status', 'SKIPPED')->count();
        $lastSent     = NotificationLog::where('channel', 'WHATSAPP')->where('status', 'SENT')->latest('sent_at')->first();
        $lastFailed   = NotificationLog::where('channel', 'WHATSAPP')->where('status', 'FAILED')->latest('updated_at')->first();

        $isEnabled = FonnteService::isEnabled();

        return response()->json([
            'success' => true,
            'data'    => [
                'gateway_status' => $isEnabled ? 'ACTIVE' : 'DISABLED',
                'total_sent'     => $totalSent,
                'total_failed'   => $totalFailed,
                'total_skipped'  => $totalSkipped,
                'last_sent_at'   => $lastSent?->sent_at,
                'last_failed_at' => $lastFailed?->updated_at,
                'last_error'     => $lastFailed?->error_message,
            ],
        ]);
    }

    /**
     * List message templates with sample previews.
     */
    public function templates()
    {
        $templates = WhatsAppTemplateService::listTemplates();

        return response()->json([
            'success' => true,
            'data'    => $templates,
        ]);
    }
}
