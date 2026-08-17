<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function dashboardKpis(Request $request)
    {
        $user = $request->user();
        $query = WorkOrderService::getScopedQuery($user);

        $total = (clone $query)->count();
        $inProgress = (clone $query)->where('status', 'IN_PROGRESS')->count();
        $waitingCheckin = (clone $query)->where('status', 'ASSIGNED')->count();
        $waitingReview = (clone $query)->where('status', 'REVIEW')->count();
        $revision = (clone $query)->where('status', 'REVISION')->count();
        $completed = (clone $query)->whereIn('status', ['APPROVED', 'COMPLETED'])->count();

        $alerts = [];
        if ($revision > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => "Terdapat {$revision} pekerjaan yang memerlukan tindakan revisi dari teknisi lapangan."
            ];
        }
        if ($waitingReview > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Terdapat {$waitingReview} pekerjaan yang menunggu verifikasi & review Admin."
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'in_progress' => $inProgress,
                'waiting_checkin' => $waitingCheckin,
                'waiting_review' => $waitingReview,
                'revision' => $revision,
                'completed' => $completed,
                'alerts' => $alerts,
            ]
        ]);
    }
}
