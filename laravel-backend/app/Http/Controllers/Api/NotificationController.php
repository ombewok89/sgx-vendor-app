<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationFeed;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = NotificationFeed::with(['workOrder:id,spk_number,title,location_name', 'client:id,name']);

        if ($user->hasRole('CLIENT')) {
            $query->where(function ($q) {
                $q->where('target_role', 'CLIENT')->orWhere('target_role', 'ALL');
            });
        } elseif ($user->hasRole('VENDOR')) {
            $query->where(function ($q) {
                $q->where('target_role', 'VENDOR')->orWhere('target_role', 'ALL');
            });
        }

        // Fast in-memory lookup for user's read notifications (O(1) complexity)
        $readIds = \Illuminate\Support\Facades\DB::table('notification_feed_user_read')
            ->where('user_id', $user->id)
            ->pluck('notification_feed_id')
            ->toArray();
        $readSet = array_flip($readIds);

        $notifications = $query->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(function ($n) use ($user, $readSet) {
                $isRead = isset($readSet[$n->id]) || (bool)$n->is_read;
                return [
                    'id' => $n->id,
                    'work_order_id' => $n->work_order_id,
                    'spk_number' => $n->workOrder?->spk_number,
                    'title' => $n->title,
                    'message' => $n->message,
                    'category' => $n->category,
                    'target_role' => $n->target_role,
                    'client_name' => $n->client?->name,
                    'location_name' => $n->workOrder?->location_name,
                    'created_at' => $n->created_at,
                    'is_read' => $isRead,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        
        \Illuminate\Support\Facades\DB::table('notification_feed_user_read')->updateOrInsert(
            [
                'notification_feed_id' => (int)$id,
                'user_id' => (int)$user->id,
            ],
            [
                'read_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi telah ditandai dibaca.',
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $query = NotificationFeed::query();

        if ($user->hasRole('CLIENT')) {
            $query->where(function ($q) {
                $q->where('target_role', 'CLIENT')->orWhere('target_role', 'ALL');
            });
        } elseif ($user->hasRole('VENDOR')) {
            $query->where(function ($q) {
                $q->where('target_role', 'VENDOR')->orWhere('target_role', 'ALL');
            });
        }

        $allIds = $query->pluck('id')->toArray();
        if (!empty($allIds)) {
            $now = now();
            $data = [];
            foreach ($allIds as $nid) {
                $data[] = [
                    'notification_feed_id' => (int)$nid,
                    'user_id' => (int)$user->id,
                    'read_at' => $now,
                ];
            }
            \Illuminate\Support\Facades\DB::table('notification_feed_user_read')->upsert(
                $data,
                ['notification_feed_id', 'user_id'],
                ['read_at']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi yang relevan telah ditandai sebagai dibaca.',
        ]);
    }

    public function whatsappLogs(Request $request)
    {
        $limit = (int)$request->get('limit', 100);
        $logs = \App\Models\NotificationLog::orderBy('id', 'desc')->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
