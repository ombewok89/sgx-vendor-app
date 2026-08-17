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

        $request->validate([
            'pic_user_id' => 'required|exists:users,id',
        ]);

        return DB::transaction(function () use ($user, $workOrder, $request) {
            $workOrder->update([
                'pic_user_id' => $request->pic_user_id,
                'status' => in_array($workOrder->status, ['READY', 'DRAFT']) ? 'ASSIGNED' : $workOrder->status,
            ]);

            $sync = [
                $request->pic_user_id => ['role_in_team' => 'PIC', 'assigned_at' => now()]
            ];

            if ($request->has('member_ids') && is_array($request->member_ids)) {
                foreach ($request->member_ids as $mId) {
                    $sync[$mId] = ['role_in_team' => 'MEMBER', 'assigned_at' => now()];
                }
            }

            $workOrder->assignments()->sync($sync);

            AuditService::log($user, 'ASSIGN_TEAM', 'WORK_ORDER', $workOrder->id, null, $sync);

            return response()->json([
                'success' => true,
                'message' => 'Tim teknisi lapangan berhasil ditugaskan.',
                'data' => $workOrder->fresh(['pic', 'assignments']),
            ]);
        });
    }

    public function submit(Request $request, $id)
    {
        $user = $request->user();
        $workOrder = WorkOrder::findOrFail($id);

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

        $workOrder->update(['status' => 'REVIEW']);
        AuditService::log($user, 'SUBMIT_FOR_REVIEW', 'WORK_ORDER', $workOrder->id);

        return response()->json([
            'success' => true,
            'message' => 'Pekerjaan berhasil diajukan untuk review dan persetujuan Admin.',
            'data' => $workOrder,
        ]);
    }
}
