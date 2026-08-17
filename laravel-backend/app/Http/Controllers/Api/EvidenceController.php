<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EvidencePhoto;
use App\Models\Issue;
use App\Models\WorkOrder;
use App\Services\AuditService;
use App\Services\EvidenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvidenceController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'photo' => 'required|image|max:16384', // 16MB max
            'stage' => 'required|in:BEFORE,PROCESS,AFTER,ISSUE',
        ]);

        $user = $request->user();
        $workOrder = WorkOrder::findOrFail($request->work_order_id);

        try {
            $photo = EvidenceService::storePhoto($user, $workOrder, $request->file('photo'), $request->all());

            return response()->json([
                'success' => true,
                'message' => "Foto bukti tahap {$photo->stage} berhasil diunggah.",
                'data' => $photo,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function gallery(Request $request)
    {
        $user = $request->user();
        $query = EvidencePhoto::with(['workOrder:id,spk_number,title,location_name,vendor_id', 'user:id,name', 'item:id,item_name']);

        if ($user->hasRole('VENDOR')) {
            $query->whereHas('workOrder', fn($q) => $q->where('vendor_id', $user->vendor_id));
        } elseif ($user->hasRole('FIELD_TEAM')) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('work_order_id')) {
            $query->where('work_order_id', $request->work_order_id);
        }
        if ($request->filled('stage') && $request->stage !== 'ALL') {
            $query->where('stage', $request->stage);
        }

        $photos = $query->orderByDesc('id')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'work_order_id' => $p->work_order_id,
                'spk_number' => $p->workOrder?->spk_number,
                'work_order_title' => $p->workOrder?->title,
                'location_name' => $p->workOrder?->location_name,
                'vendor_id' => $p->workOrder?->vendor_id,
                'stage' => $p->stage,
                'sequence' => $p->sequence,
                'file_path' => $p->file_path,
                'file_name' => $p->file_name,
                'file_hash' => $p->file_hash,
                'server_timestamp' => $p->server_timestamp,
                'latitude' => $p->latitude,
                'longitude' => $p->longitude,
                'accuracy' => $p->accuracy,
                'notes' => $p->notes,
                'uploader_name' => $p->user?->name,
                'work_item_name' => $p->item?->item_name,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $photos,
        ]);
    }

    public function deletePhoto(Request $request, $id)
    {
        $user = $request->user();
        $photo = EvidencePhoto::findOrFail($id);

        // Security check: Only uploader or admin can delete
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN']) && $photo->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk menghapus foto ini.',
            ], 403);
        }

        // Delete physical file
        $relativeDiskPath = str_replace('/storage/', '', $photo->file_path);
        Storage::disk('public')->delete($relativeDiskPath);

        AuditService::log($user, 'DELETE_PHOTO', 'EVIDENCE_PHOTO', $photo->id, $photo->toArray());
        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Foto bukti berhasil dihapus.',
        ]);
    }

    public function reportIssue(Request $request)
    {
        $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'issue_type' => 'required|string',
            'notes' => 'required|string',
        ]);

        $user = $request->user();
        $issue = Issue::create([
            'work_order_id' => $request->work_order_id,
            'user_id' => $user->id,
            'has_issue' => true,
            'issue_type' => $request->issue_type,
            'notes' => $request->notes,
            'status' => 'OPEN',
        ]);

        AuditService::log($user, 'REPORT_ISSUE', 'ISSUE', $issue->id, null, $issue->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Kendala teknis lapangan berhasil dilaporkan.',
            'data' => $issue,
        ], 201);
    }

    public function resolveIssue(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang dapat menyelesaikan laporan kendala.',
            ], 403);
        }

        $request->validate([
            'resolution_notes' => 'required|string',
        ]);

        $issue = Issue::findOrFail($id);
        $issue->update([
            'status' => 'RESOLVED',
            'resolution_notes' => $request->resolution_notes,
            'resolved_at' => now(),
            'resolved_by' => $user->id,
        ]);

        AuditService::log($user, 'RESOLVE_ISSUE', 'ISSUE', $issue->id, null, $issue->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Laporan kendala telah ditandai sebagai terselesaikan.',
            'data' => $issue,
        ]);
    }
}
