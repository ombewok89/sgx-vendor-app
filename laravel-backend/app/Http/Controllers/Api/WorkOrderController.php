<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Services\AuditService;
use App\Services\WorkOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Auto-normalize legacy 'REVIEW' status to standard 'SUBMITTED'
        WorkOrder::where('status', 'REVIEW')->update(['status' => 'SUBMITTED', 'progress_percent' => 100]);

        $query = WorkOrderService::getScopedQuery($user);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('spk_number', 'LIKE', "%{$s}%")
                  ->orWhere('title', 'LIKE', "%{$s}%")
                  ->orWhere('location_name', 'LIKE', "%{$s}%");
            });
        }

        $workOrders = $query->orderByDesc('id')->get();

        // Calculate and normalize real progress for each work order
        foreach ($workOrders as $wo) {
            $calc = WorkOrderService::recalculateProgress($wo);
            $wo->progress_percent = $calc;
        }

        return response()->json([
            'success' => true,
            'data' => $workOrders,
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $workOrder = WorkOrderService::getScopedQuery($user)->find($id);

        if (!$workOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Surat Perintah Kerja (SPK) tidak ditemukan atau Anda tidak memiliki akses.',
            ], 404);
        }

        $calc = WorkOrderService::recalculateProgress($workOrder);
        $workOrder->progress_percent = $calc;

        return response()->json([
            'success' => true,
            'data' => $workOrder,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang berwenang membuat SPK.',
            ], 403);
        }

        $request->validate([
            'title' => 'required|string',
            'vendor_id' => 'required|exists:vendors,id',
            'area_id' => 'required|exists:areas,id',
            'job_type_id' => 'required|exists:job_types,id',
            'location_name' => 'required|string',
            'deadline' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
        ]);

        return DB::transaction(function () use ($user, $request) {
            $spkNumber = WorkOrderService::generateSpkNumber();

            $workOrder = WorkOrder::create([
                'spk_number' => $spkNumber,
                'title' => $request->title,
                'vendor_id' => $request->vendor_id,
                'area_id' => $request->area_id,
                'job_type_id' => $request->job_type_id,
                'location_name' => $request->location_name,
                'target_lat' => $request->target_lat,
                'target_lng' => $request->target_lng,
                'pic_user_id' => $request->pic_user_id,
                'start_date' => $request->start_date ?? now()->toDateString(),
                'deadline' => $request->deadline,
                'doc_mode' => $request->doc_mode ?? 'BEFORE_PROCESS_AFTER',
                'require_checkin' => $request->boolean('require_checkin', true),
                'status' => $request->pic_user_id ? 'ASSIGNED' : 'READY',
                'progress_percent' => 0,
                'notes' => $request->notes,
                'created_by' => $user->id,
            ]);

            // Save Items
            $itemCount = count($request->items);
            $weight = floor(100 / $itemCount);
            foreach ($request->items as $idx => $item) {
                WorkOrderItem::create([
                    'work_order_id' => $workOrder->id,
                    'item_name' => $item['item_name'],
                    'job_type_id' => $item['job_type_id'] ?? $request->job_type_id,
                    'doc_mode' => $item['doc_mode'] ?? $workOrder->doc_mode,
                    'weight_percent' => ($idx === $itemCount - 1) ? (100 - ($weight * ($itemCount - 1))) : $weight,
                    'status' => 'PENDING',
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Assign PIC
            if ($request->filled('pic_user_id')) {
                $workOrder->assignments()->syncWithoutDetaching([
                    $request->pic_user_id => ['role_in_team' => 'PIC', 'assigned_at' => now()]
                ]);
            }

            // Assign Members
            if ($request->has('member_ids') && is_array($request->member_ids)) {
                $syncMembers = [];
                foreach ($request->member_ids as $mId) {
                    $syncMembers[$mId] = ['role_in_team' => 'MEMBER', 'assigned_at' => now()];
                }
                $workOrder->assignments()->syncWithoutDetaching($syncMembers);
            }

            AuditService::log($user, 'CREATE_WORK_ORDER', 'WORK_ORDER', $workOrder->id, null, $workOrder->toArray());

            return response()->json([
                'success' => true,
                'message' => "Surat Perintah Kerja {$workOrder->spk_number} berhasil diterbitkan.",
                'data' => $workOrder->load(['vendor', 'area', 'jobType', 'pic', 'assignments', 'items']),
            ], 201);
        });
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'SUPERVISOR'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Pengguna Supervisor / Superuser yang memiliki wewenang untuk mengubah data dan pengaturan SPK.',
            ], 403);
        }

        $workOrder = WorkOrder::with(['vendor', 'area', 'jobType', 'pic', 'assignments', 'items'])->findOrFail($id);
        $oldData = $workOrder->toArray();

        return DB::transaction(function () use ($request, $user, $workOrder, $oldData) {
            $data = $request->only([
                'title', 'spk_number', 'vendor_id', 'area_id', 'job_type_id', 'location_name',
                'target_lat', 'target_lng', 'start_date', 'deadline', 'notes',
                'doc_mode', 'status'
            ]);

            if ($request->has('require_checkin')) {
                $data['require_checkin'] = $request->boolean('require_checkin');
            }

            if ($request->has('pic_user_id')) {
                $data['pic_user_id'] = $request->pic_user_id ?: null;
                // Update assignment pivot for PIC
                if ($data['pic_user_id']) {
                    $workOrder->assignments()->syncWithoutDetaching([
                        $data['pic_user_id'] => ['role_in_team' => 'PIC', 'assigned_at' => now()]
                    ]);
                    if (in_array($workOrder->status, ['DRAFT', 'READY']) && empty($data['status'])) {
                        $data['status'] = 'ASSIGNED';
                    }
                }
            }

            $workOrder->update($data);

            // Sync additional team members if passed
            if ($request->has('member_ids') && is_array($request->member_ids)) {
                $syncAssignments = [];
                if ($workOrder->pic_user_id) {
                    $syncAssignments[$workOrder->pic_user_id] = ['role_in_team' => 'PIC', 'assigned_at' => now()];
                }
                foreach ($request->member_ids as $mId) {
                    if ($mId != $workOrder->pic_user_id) {
                        $syncAssignments[$mId] = ['role_in_team' => 'MEMBER', 'assigned_at' => now()];
                    }
                }
                $workOrder->assignments()->sync($syncAssignments);
            }

            // Sync checklist items if passed
            if ($request->has('items') && is_array($request->items) && count($request->items) > 0) {
                $itemCount = count($request->items);
                $weight = floor(100 / $itemCount);
                
                // Get existing item IDs that are kept
                $keptItemIds = [];
                foreach ($request->items as $idx => $itemData) {
                    $itemWeight = ($idx === $itemCount - 1) ? (100 - ($weight * ($itemCount - 1))) : $weight;
                    if (!empty($itemData['id'])) {
                        $item = WorkOrderItem::where('work_order_id', $workOrder->id)->find($itemData['id']);
                        if ($item) {
                            $item->update([
                                'item_name' => $itemData['item_name'],
                                'job_type_id' => $itemData['job_type_id'] ?? $workOrder->job_type_id,
                                'doc_mode' => $itemData['doc_mode'] ?? $workOrder->doc_mode,
                                'weight_percent' => $itemWeight,
                                'notes' => $itemData['notes'] ?? null,
                            ]);
                            $keptItemIds[] = $item->id;
                            continue;
                        }
                    }

                    // Create new item
                    $newItem = WorkOrderItem::create([
                        'work_order_id' => $workOrder->id,
                        'item_name' => $itemData['item_name'],
                        'job_type_id' => $itemData['job_type_id'] ?? $workOrder->job_type_id,
                        'doc_mode' => $itemData['doc_mode'] ?? $workOrder->doc_mode,
                        'weight_percent' => $itemWeight,
                        'status' => 'PENDING',
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                    $keptItemIds[] = $newItem->id;
                }

                // Delete items that were removed
                if (count($keptItemIds) > 0) {
                    WorkOrderItem::where('work_order_id', $workOrder->id)
                        ->whereNotIn('id', $keptItemIds)
                        ->delete();
                }
            }

            AuditService::log($user, 'UPDATE_WORK_ORDER', 'WORK_ORDER', $workOrder->id, $oldData, $workOrder->toArray());

            return response()->json([
                'success' => true,
                'message' => "Data dan pengaturan SPK {$workOrder->spk_number} berhasil diperbarui oleh Supervisor.",
                'data' => $workOrder->fresh(['vendor', 'area', 'jobType', 'pic', 'assignments', 'items']),
            ]);
        });
    }

    public function updateLocation(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang berwenang mengubah lokasi SPK.',
            ], 403);
        }

        $id = $request->input('work_order_id', $request->input('id'));
        $workOrder = WorkOrder::findOrFail($id);

        $workOrder->update([
            'target_lat' => $request->target_lat,
            'target_lng' => $request->target_lng,
        ]);

        AuditService::log($user, 'UPDATE_LOCATION', 'WORK_ORDER', $workOrder->id, null, [
            'target_lat' => $request->target_lat,
            'target_lng' => $request->target_lng,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Koordinat target GPS SPK berhasil disinkronkan.',
            'data' => $workOrder->fresh(['vendor', 'area', 'jobType', 'pic', 'assignments', 'items']),
        ]);
    }

    public function toggleCheckin(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang berwenang mengubah aturan check-in.',
            ], 403);
        }

        $id = $request->input('work_order_id', $request->input('id'));
        $workOrder = WorkOrder::findOrFail($id);

        $requireCheckin = $request->boolean('require_checkin');
        $workOrder->update([
            'require_checkin' => $requireCheckin,
        ]);

        AuditService::log($user, 'TOGGLE_CHECKIN_REQUIREMENT', 'WORK_ORDER', $workOrder->id, null, [
            'require_checkin' => $requireCheckin,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan wajib cek lokasi berhasil diperbarui.',
            'data' => $workOrder->fresh(['vendor', 'area', 'jobType', 'pic', 'assignments', 'items']),
        ]);
    }

    public function assignTeam(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang berwenang menugaskan tim.',
            ], 403);
        }

        $workOrder = WorkOrder::findOrFail($id);

        $picId = $request->input('pic_user_id', $request->input('picUserId'));
        $memberIds = $request->input('member_ids', $request->input('memberUserIds', []));

        if (!$picId) {
            return response()->json([
                'success' => false,
                'message' => 'PIC tim lapangan wajib dipilih.',
            ], 422);
        }

        return DB::transaction(function () use ($user, $workOrder, $picId, $memberIds) {
            $workOrder->update([
                'pic_user_id' => $picId,
                'status' => in_array($workOrder->status, ['READY', 'DRAFT']) ? 'ASSIGNED' : $workOrder->status,
            ]);

            $sync = [
                $picId => ['role_in_team' => 'PIC', 'assigned_at' => now()]
            ];

            if (!empty($memberIds) && is_array($memberIds)) {
                foreach ($memberIds as $mId) {
                    if ($mId != $picId) {
                        $sync[$mId] = ['role_in_team' => 'MEMBER', 'assigned_at' => now()];
                    }
                }
            }

            $workOrder->assignments()->sync($sync);

            AuditService::log($user, 'ASSIGN_TEAM', 'WORK_ORDER', $workOrder->id, null, [
                'pic_user_id' => $picId,
                'member_ids' => $memberIds,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Penugasan tim lapangan berhasil diperbarui.',
                'data' => $workOrder->load(['pic', 'assignments', 'vendor', 'area', 'jobType']),
            ]);
        });
    }

    public function submit(Request $request, $id)
    {
        $user = $request->user();
        $workOrder = WorkOrder::with(['evidencePhotos', 'jobType'])->findOrFail($id);

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

        // Validate Minimum Required Evidence Photos (M-01)
        $minPhotos = $workOrder->jobType?->min_photos_per_stage ?: 1;
        $beforeCount = $workOrder->evidencePhotos()->where('stage', 'BEFORE')->count();
        $processCount = $workOrder->evidencePhotos()->where('stage', 'PROCESS')->count();
        $afterCount = $workOrder->evidencePhotos()->where('stage', 'AFTER')->count();

        $missing = [];
        if ($beforeCount < $minPhotos) {
            $missing[] = "BEFORE (kurang " . ($minPhotos - $beforeCount) . " foto)";
        }
        if ($processCount < $minPhotos) {
            $missing[] = "PROCESS (kurang " . ($minPhotos - $processCount) . " foto)";
        }
        if ($afterCount < $minPhotos) {
            $missing[] = "AFTER (kurang " . ($minPhotos - $afterCount) . " foto)";
        }

        if (!empty($missing)) {
            return response()->json([
                'success' => false,
                'message' => 'Pekerjaan belum dapat diajukan: Foto bukti belum lengkap pada tahap: ' . implode(', ', $missing) . '. Wajib minimal ' . $minPhotos . ' foto per tahap.',
            ], 422);
        }

        $workOrder->update([
            'status' => 'SUBMITTED',
            'progress_percent' => 100,
        ]);
        AuditService::log($user, 'SUBMIT_FOR_REVIEW', 'WORK_ORDER', $workOrder->id);

        return response()->json([
            'success' => true,
            'message' => 'Pekerjaan berhasil diajukan untuk review tim admin.',
            'data' => $workOrder->fresh(['vendor', 'area', 'jobType', 'pic', 'assignments', 'items', 'evidencePhotos']),
        ]);
    }

    public function complete(Request $request, $id = null)
    {
        $targetId = $id ?: $request->input('work_order_id', $request->input('id'));
        $user = $request->user();

        $workOrder = WorkOrder::find($targetId);
        if (!$workOrder) {
            return response()->json([
                'success' => false,
                'message' => "Pekerjaan dengan ID '{$targetId}' tidak ditemukan.",
            ], 404);
        }

        $old = $workOrder->toArray();
        $workOrder->update([
            'status' => 'COMPLETED',
            'progress_percent' => 100
        ]);
        
        $workOrder->items()->update(['status' => 'COMPLETED']);

        if ($user) {
            AuditService::log($user, 'COMPLETE_WORK_ORDER', 'WORK_ORDER', $workOrder->id, $old, $workOrder->toArray());
        }

        return response()->json([
            'success' => true,
            'message' => 'Pekerjaan ' . $workOrder->spk_number . ' berhasil diselesaikan (COMPLETED 100%).',
            'data' => $workOrder->fresh(['vendor', 'area', 'jobType', 'pic', 'assignments', 'items', 'evidencePhotos', 'baDocument']),
        ]);
    }
}
