<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Services\CheckInService;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'required|numeric',
        ]);

        return $this->checkIn($request, $request->work_order_id);
    }

    public function checkIn(Request $request, $workOrderId)
    {
        $user = $request->user();
        $workOrder = WorkOrder::findOrFail($workOrderId);

        // Verify assignment
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

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'required|numeric',
        ]);

        try {
            $checkIn = CheckInService::checkIn($workOrder, $user, $request->all());

            // Automated WhatsApp Notification to Admin/Supervisor
            \App\Services\WhatsAppNotificationDispatcher::onGpsCheckIn($workOrder, $user, $checkIn);

            return response()->json([
                'success' => true,
                'message' => 'Presensi check-in lokasi berhasil diverifikasi.',
                'data' => $checkIn,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
