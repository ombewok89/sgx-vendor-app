<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Services\AuditService;
use App\Services\WorkOrderService;
use Illuminate\Http\Request;

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
                'message' => 'Surat Perintah Kerja (SPK) tidak ditemukan atau Anda tidak memiliki hak akses.',
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
                'message' => 'Akses Ditolak: Hanya Administrator yang berwenang menerbitkan SPK baru.',
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'vendor_id' => 'required|exists:vendors,id',
            'area_id' => 'required|exists:areas,id',
            'location_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $workOrder = WorkOrderService::createWorkOrder($user, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Surat Perintah Kerja berhasil diterbitkan.',
                'data' => $workOrder,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menerbitkan SPK: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function assignTeam(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN', 'VENDOR'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki wewenang untuk menugaskan tim.',
            ], 403);
        }

        $workOrder = WorkOrderService::getScopedQuery($user)->findOrFail($id);

        $request->validate([
            'pic_user_id' => 'required|exists:users,id',
            'member_user_ids' => 'nullable|array',
        ]);

        $assignments = [];
        $assignments[$request->pic_user_id] = ['role_in_team' => 'PIC', 'assigned_at' => now()];

        if ($request->filled('member_user_ids')) {
            foreach ($request->member_user_ids as $mId) {
                if ($mId != $request->pic_user_id) {
                    $assignments[$mId] = ['role_in_team' => 'MEMBER', 'assigned_at' => now()];
                }
            }
        }

        $workOrder->assignments()->sync($assignments);
        $workOrder->update([
            'pic_user_id' => $request->pic_user_id,
            'status' => $workOrder->status === 'DRAFT' ? 'ASSIGNED' : $workOrder->status,
        ]);

        AuditService::log($user, 'ASSIGN_TEAM', 'WORK_ORDER', $workOrder->id, null, $assignments);

        return response()->json([
            'success' => true,
            'message' => 'Penugasan tim lapangan berhasil diperbarui.',
            'data' => $workOrder->fresh(['pic', 'assignments']),
        ]);
    }
}
