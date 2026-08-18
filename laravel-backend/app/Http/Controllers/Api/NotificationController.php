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

        $notifications = $query->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(function ($n) use ($user) {
                $isRead = $n->readUsers()->where('users.id', $user->id)->exists();
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
        $notification = NotificationFeed::findOrFail($id);
        $notification->readUsers()->syncWithoutDetaching([$user->id => ['read_at' => now()]]);

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
        $syncData = [];
        foreach ($allIds as $nid) {
            $syncData[$nid] = ['read_at' => now()];
        }

        $user->readNotifications()->syncWithoutDetaching($syncData);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi yang relevan telah ditandai sebagai dibaca.',
        ]);
    }
}
