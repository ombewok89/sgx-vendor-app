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
            'photo' => 'nullable|file|image|mimes:jpeg,jpg,png,webp|max:15360',
            'file' => 'nullable|file|image|mimes:jpeg,jpg,png,webp|max:15360',
            'image' => 'nullable|file|image|mimes:jpeg,jpg,png,webp|max:15360',
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

            // Automated WhatsApp Notification to Admin/Supervisor
            \App\Services\WhatsAppNotificationDispatcher::onEvidenceUpload($workOrder, $user, $photo);

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
                'file_url' => url('/api/storage-stream/' . ltrim(str_replace('/storage/', '', $p->file_path), '/')),
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

    public function streamPhoto($id)
    {
        $photo = EvidencePhoto::findOrFail($id);
        $path = $photo->file_path;
        $clean = preg_replace('#^(storage/|/storage/|public/)+#', '', ltrim($path, '/'));
        $filename = basename($path);

        $candidatePaths = array_unique(array_filter([
            storage_path('app/public/' . $clean),
            storage_path('app/public/' . $path),
            storage_path('app/public/uploads/' . $filename),
            storage_path('app/uploads/' . $filename),
            storage_path('app/' . $clean),
            storage_path('app/private/' . $clean),
            base_path('storage/app/public/' . $clean),
            base_path('storage/app/public/uploads/' . $filename),
            base_path('../laravel-backend/storage/app/public/' . $clean),
            base_path('../laravel-backend/storage/app/public/uploads/' . $filename),
            public_path('storage/' . $clean),
            public_path('uploads/' . $filename),
            base_path('public/uploads/' . $filename),
            base_path('../uploads/' . $filename),
            base_path('../public/uploads/' . $filename),
        ]));

        foreach ($candidatePaths as $file) {
            if ($file && file_exists($file) && !is_dir($file)) {
                $content = file_get_contents($file);
                $mimeType = @mime_content_type($file) ?: ($photo->mime_type ?: 'image/jpeg');
                return response($content, 200, [
                    'Content-Type' => $mimeType,
                    'Content-Length' => strlen($content),
                    'Cache-Control' => 'public, max-age=31536000',
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
                ]);
            }
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"><rect width="400" height="300" fill="#0f172a"/><text x="50%" y="42%" fill="#38bdf8" font-family="sans-serif" font-size="14" font-weight="bold" text-anchor="middle">FOTO BUKTI #' . $photo->sequence . ' (' . $photo->stage . ')</text><text x="50%" y="58%" fill="#94a3b8" font-family="monospace" font-size="11" text-anchor="middle">' . htmlspecialchars(substr($photo->file_name, 0, 26)) . '</text><text x="50%" y="72%" fill="#34d399" font-family="monospace" font-size="9" text-anchor="middle">SHA-256: ' . substr($photo->file_hash ?? 'VERIFIED', 0, 16) . '...</text></svg>';
        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-cache',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    public function deletePhoto(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Superuser dan Administrator yang berwenang menghapus foto bukti forensik.',
            ], 403);
        }

        $photo = EvidencePhoto::with('workOrder')->findOrFail($id);
        $workOrder = $photo->workOrder;

        // Guardrail: Jika SPK sudah BA_OPNAME dan bukan Superuser, cegah kerusakan Berita Acara
        if ($workOrder && in_array($workOrder->status, ['BA_OPNAME', 'APPROVED']) && !$user->hasRole('SUPERUSER')) {
            return response()->json([
                'success' => false,
                'message' => "Peringatan: SPK {$workOrder->spk_number} telah disetujui / diterbitkan Berita Acara (BA). Foto ini terkunci untuk menjaga keabsahan dokumen.",
            ], 422);
        }

        // Delete physical file
        $relativeDiskPath = str_replace('/storage/', '', $photo->file_path);
        Storage::disk('public')->delete($relativeDiskPath);

        AuditService::log($user, 'DELETE_PHOTO', 'EVIDENCE_PHOTO', $photo->id, null, [
            'file_name' => $photo->file_name,
            'stage' => $photo->stage,
            'spk_number' => $workOrder?->spk_number,
        ]);

        $photo->delete();

        if ($workOrder) {
            \App\Services\WorkOrderService::recalculateProgress($workOrder);
        }

        return response()->json([
            'success' => true,
            'message' => 'Foto bukti forensik berhasil dihapus dari sistem.',
        ]);
    }

    public function bulkDeletePhotos(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Superuser dan Administrator yang berwenang menghapus foto bukti secara massal.',
            ], 403);
        }

        $request->validate([
            'photo_ids' => 'required|array|min:1',
            'photo_ids.*' => 'integer|exists:evidence_photos,id',
        ]);

        $photoIds = $request->photo_ids;
        $photos = EvidencePhoto::with('workOrder')->whereIn('id', $photoIds)->get();

        $deletedCount = 0;
        $affectedWorkOrders = [];

        foreach ($photos as $photo) {
            $workOrder = $photo->workOrder;

            // Guardrail: Skip foto yang SPK-nya sudah BA_OPNAME jika bukan Superuser
            if ($workOrder && in_array($workOrder->status, ['BA_OPNAME', 'APPROVED']) && !$user->hasRole('SUPERUSER')) {
                continue;
            }

            // Hapus file fisik
            $relativeDiskPath = str_replace('/storage/', '', $photo->file_path);
            Storage::disk('public')->delete($relativeDiskPath);

            if ($workOrder && !in_array($workOrder->id, $affectedWorkOrders)) {
                $affectedWorkOrders[] = $workOrder->id;
            }

            AuditService::log($user, 'BULK_DELETE_PHOTO', 'EVIDENCE_PHOTO', $photo->id, null, [
                'file_name' => $photo->file_name,
                'stage' => $photo->stage,
                'spk_number' => $workOrder?->spk_number,
            ]);

            $photo->delete();
            $deletedCount++;
        }

        // Recalculate progress untuk semua SPK terkait
        foreach ($affectedWorkOrders as $woId) {
            $wo = WorkOrder::find($woId);
            if ($wo) {
                \App\Services\WorkOrderService::recalculateProgress($wo);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sebanyak {$deletedCount} foto bukti berhasil dihapus secara permanen.",
            'deleted_count' => $deletedCount,
        ]);
    }

    public function issuesList(Request $request)
    {
        $user = $request->user();
        $query = Issue::with(['workOrder:id,spk_number,title,location_name,vendor_id', 'user:id,name', 'resolver:id,name']);

        // Scope to client vendor if user is a client/vendor
        if ($user && in_array($user->role, ['VENDOR', 'CLIENT'])) {
            $vendorId = $user->vendor_id;
            if ($vendorId) {
                $query->whereHas('workOrder', function ($q) use ($vendorId) {
                    $q->where('vendor_id', $vendorId);
                });
            }
        }

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

        // Automated WhatsApp Notification to Admin/Supervisor
        \App\Services\WhatsAppNotificationDispatcher::onIssueReported($workOrder, $user, $issue);

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
