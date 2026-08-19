<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Revision;
use App\Models\WorkOrder;
use App\Services\AuditService;
use App\Services\BaDocumentService;
use App\Services\WhatsAppNotificationService;
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

            return [
                'work_order'  => $workOrder->fresh(['vendor', 'area', 'jobType', 'pic', 'assignments', 'items', 'evidencePhotos', 'baDocument']),
                'ba_document' => $ba,
            ];
        });

        // WhatsApp Notification (Post-Commit Trigger 4A & 5)
        WhatsAppNotificationService::onReviewApproved($result['work_order']);

        return response()->json([
            'success' => true,
            'message' => 'Pekerjaan berhasil disetujui, Berita Acara (BA) diterbitkan, dan SPK otomatis selesai (COMPLETED 100%).',
            'data' => [
                'work_order' => $result['work_order'],
                'ba_document' => $result['ba_document'],
            ],
        ]);
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
            'target_stage' => 'required|in:BEFORE,PROCESS,AFTER',
            'reason' => 'required|string',
        ]);

        $workOrder = WorkOrder::findOrFail($id);

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

            AuditService::log($user, 'REQUEST_REVISION', 'WORK_ORDER', $workOrder->id, null, $revision->toArray());

            return $revision;
        });

        // WhatsApp Notification (Post-Commit Trigger 4B)
        WhatsAppNotificationService::onRevisionRequired($workOrder, $revision);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan revisi berhasil dikirimkan ke tim teknisi lapangan.',
            'data' => $revision,
        ]);
    }
}
