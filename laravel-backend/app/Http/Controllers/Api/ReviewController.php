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
    public function approve(Request $request, $workOrderId)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang berwenang menyetujui pekerjaan.',
            ], 403);
        }

        $workOrder = WorkOrder::findOrFail($workOrderId);

        return DB::transaction(function () use ($user, $workOrder, $request) {
            $review = Review::create([
                'work_order_id' => $workOrder->id,
                'reviewer_user_id' => $user->id,
                'status' => 'APPROVED',
                'review_notes' => $request->review_notes ?? 'Pekerjaan disetujui.',
            ]);

            $workOrder->update([
                'status' => 'APPROVED',
                'progress_percent' => 100,
            ]);

            // Auto-generate Berita Acara (BA) Opname
            $ba = BaDocumentService::createOrUpdateBa($user, $workOrder);

            AuditService::log($user, 'APPROVE_WORK_ORDER', 'WORK_ORDER', $workOrder->id, null, ['ba_id' => $ba->id]);

            return response()->json([
                'success' => true,
                'message' => 'Pekerjaan berhasil disetujui & Berita Acara (BA) telah otomatis diterbitkan.',
                'data' => [
                    'work_order' => $workOrder->fresh(),
                    'ba_document' => $ba,
                ],
            ]);
        });
    }

    public function requestRevision(Request $request, $workOrderId)
    {
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

        $workOrder = WorkOrder::findOrFail($workOrderId);

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

            return response()->json([
                'success' => true,
                'message' => 'Permintaan revisi berhasil dikirimkan ke tim teknisi lapangan.',
                'data' => $revision,
            ]);
        });
    }
}
