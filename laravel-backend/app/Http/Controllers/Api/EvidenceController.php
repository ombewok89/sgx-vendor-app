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
        $file = $request->file('photo') ?? $request->file('file') ?? $request->file('image');
        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'File foto bukti wajib disertakan (photo/file/image).',
            ], 422);
        }

        $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'stage' => 'required|in:BEFORE,PROCESS,AFTER,ISSUE',
        ]);

        $user = $request->user();
        $workOrder = WorkOrder::findOrFail($request->work_order_id);

        // Anti-IDOR: Verify assignment for FIELD_TEAM
        if ($user->hasRole('FIELD_TEAM')) {
            $isAssigned = $workOrder->pic_user_id === $user->id ||
                $workOrder->assignments()->where('users.id', $user->id)->exists();
            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses Ditolak: Anda tidak ditugaskan pada Surat Perintah Kerja (SPK) ini.',
                ], 403);
            }
        }

        try {
            $photo = EvidenceService::storePhoto($user, $workOrder, $file, $request->all());

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

        if ($user->hasAnyRole(['VENDOR', 'CLIENT'])) {
            if ($user->vendor_id) {
                $query->whereHas('workOrder', fn($q) => $q->where('vendor_id', $user->vendor_id));
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->hasRole('FIELD_TEAM')) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('workOrder', function ($wq) use ($user) {
                      $wq->where('pic_user_id', $user->id)
                         ->orWhereHas('assignments', fn($aq) => $aq->where('users.id', $user->id));
                  });
            });
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

    public function issuesList(Request $request)
    {
        $query = Issue::with(['workOrder:id,spk_number,title,location_name', 'user:id,name', 'resolver:id,name']);
        if ($request->filled('work_order_id')) {
            $query->where('work_order_id', $request->work_order_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $issues = $query->orderByDesc('id')->get();
        return response()->json(['success' => true, 'data' => $issues]);
    }

    public function reportIssue(Request $request)
    {
        $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'issue_type' => 'required|string',
            'notes' => 'required|string',
        ]);

        $user = $request->user();
        $workOrder = WorkOrder::findOrFail($request->work_order_id);

        // Anti-IDOR: Verify assignment for FIELD_TEAM
        if ($user->hasRole('FIELD_TEAM')) {
            $isAssigned = $workOrder->pic_user_id === $user->id ||
                $workOrder->assignments()->where('users.id', $user->id)->exists();
            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses Ditolak: Anda tidak ditugaskan pada pekerjaan ini.',
                ], 403);
            }
        }

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
