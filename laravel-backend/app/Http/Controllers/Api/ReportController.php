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
        $inProgress = (clone $query)->whereIn('status', ['IN_PROGRESS', 'CHECKED_IN'])->count();
        $waitingCheckin = (clone $query)->whereIn('status', ['READY', 'ASSIGNED'])->count();
        $waitingReview = (clone $query)->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'])->count();
        $revision = (clone $query)->where('status', 'REVISION')->count();
        $completed = (clone $query)->whereIn('status', ['APPROVED', 'COMPLETED', 'BA_OPNAME'])->count();

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
