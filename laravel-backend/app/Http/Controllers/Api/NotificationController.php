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
        $query = NotificationFeed::with(['workOrder:id,spk_number,title', 'client:id,name']);

        if ($user->hasRole('CLIENT')) {
            $query->where('target_role', 'CLIENT')->orWhere('target_role', 'ALL');
        } elseif ($user->hasRole('VENDOR')) {
            $query->where('target_role', 'VENDOR')->orWhere('target_role', 'ALL');
        }

        $notifications = $query->orderByDesc('id')->limit(50)->get();

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
}
