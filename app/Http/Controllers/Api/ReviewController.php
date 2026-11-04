<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Revision;
use App\Models\WorkOrder;
use App\Services\AuditService;
use App\Services\BaDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function approve(Request $request, $workOrderId = null)
    {
        $id = $workOrderId ?: $request->work_order_id;
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang berwenang menyetujui pekerjaan.',
            ], 403);
        }

        $workOrder = WorkOrder::with(['evidencePhotos', 'items'])->findOrFail($id);

        if (!in_array($workOrder->status, ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW', 'REVISION'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pekerjaan belum dapat disetujui karena belum diajukan untuk review oleh tim lapangan.',
            ], 422);
        }

        return DB::transaction(function () use ($user, $workOrder, $request) {
            $old = $workOrder->toArray();

            $review = Review::create([
                'work_order_id' => $workOrder->id,
                'reviewer_user_id' => $user->id,
                'status' => 'APPROVED',
                'review_notes' => $request->review_notes ?? 'Pekerjaan disetujui.',
            ]);

            // Auto-generate & Finalize Berita Acara (BA) Opname
            $ba = BaDocumentService::createOrUpdateBa($user, $workOrder);

            // Automatic Terminal State Completion
            $workOrder->update([
                'status' => 'COMPLETED',
                'progress_percent' => 100,
            ]);

            $workOrder->items()->update(['status' => 'COMPLETED']);

            AuditService::log($user, 'APPROVE_AND_COMPLETE_WORK_ORDER', 'WORK_ORDER', $workOrder->id, $old, [
                'ba_id' => $ba->id,
                'ba_number' => $ba->ba_number,
                'status' => 'COMPLETED'
            ]);

            // Automated WhatsApp Notification for BA Issuance
            \App\Services\WhatsAppNotificationDispatcher::onBaIssued($workOrder, $ba);

            return response()->json([
                'success' => true,
                'message' => 'Pekerjaan berhasil disetujui, Berita Acara (BA) diterbitkan, dan SPK otomatis selesai (COMPLETED 100%).',
                'data' => [
                    'work_order' => $workOrder->fresh(['vendor', 'area', 'jobType', 'pic', 'assignments', 'items', 'evidencePhotos', 'baDocument']),
                    'ba_document' => $ba,
                ],
            ]);
        });
    }

    public function requestRevision(Request $request, $workOrderId = null)
    {
        $id = $workOrderId ?: $request->work_order_id;
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang berwenang meminta revisi.',
            ], 403);
        }

        $request->validate([
            'target_stage' => 'required|string|in:BEFORE,PROCESS,AFTER,ALL,GENERAL,ADDENDUM',
            'reason' => 'required|string',
        ]);

        $workOrder = WorkOrder::findOrFail($id);

        if (!in_array($workOrder->status, ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW', 'REVISION', 'IN_PROGRESS'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pekerjaan belum dalam tahap yang dapat diminta revisi.',
            ], 422);
        }

        return DB::transaction(function () use ($user, $workOrder, $request) {
            $review = Review::create([
                'work_order_id' => $workOrder->id,
                'reviewer_user_id' => $user->id,
                'status' => 'REVISION_REQUESTED',
                'review_notes' => $request->reason,
            ]);

            $revision = Revision::create([
                'work_order_id' => $workOrder->id,
                'review_id' => $review->id,
                'target_stage' => $request->target_stage,
                'reason' => $request->reason,
                'requested_by' => $user->id,
                'requested_at' => now(),
                'status' => 'OPEN',
            ]);

            $workOrder->update([
                'status' => 'REVISION',
            ]);

            try {
                AuditService::log($user, 'REQUEST_REVISION', 'WORK_ORDER', $workOrder->id, null, $revision->toArray());
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Audit log error: ' . $e->getMessage());
            }

            // Automated WhatsApp Notification to Field Team on Revision
            try {
                \App\Services\WhatsAppNotificationDispatcher::onCustomAlert(
                    $workOrder,
                    "⚠️ *Permintaan Revisi Pekerjaan*\nNo. SPK: *{$workOrder->spk_number}*\nLokasi: *{$workOrder->location_name}*\nTarget: *{$request->target_stage}*\nInstruksi: {$request->reason}\n\nMohon lakukan perbaikan dan lengkapi bukti foto kembali."
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Revision WA notification error: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Permintaan revisi berhasil dikirimkan ke tim teknisi lapangan.',
                'data' => $revision,
            ]);
        });
    }
}
