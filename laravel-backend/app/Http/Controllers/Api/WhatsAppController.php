<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
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

        // Unique idempotency key for this test request (cooldown per minute per user)
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
