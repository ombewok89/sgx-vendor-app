<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicTrackingController extends Controller
{
    /**
     * Public Guest Endpoint to view live tracking data of a Work Order.
     * No authentication required. Financial data is strictly excluded.
     */
    public function track(string $token)
    {
        $normalizedToken = trim($token);
        $cleanSpk = strtoupper(str_replace(['spk-', 'spk_'], '', strtolower($normalizedToken)));

        $workOrder = WorkOrder::where('share_token', $normalizedToken)
            ->orWhere('share_token', strtolower($normalizedToken))
            ->orWhere('spk_number', $normalizedToken)
            ->orWhere('spk_number', strtoupper($normalizedToken))
            ->orWhere('spk_number', 'SPK-' . $cleanSpk)
            ->orWhere('spk_number', $cleanSpk)
            ->orWhere('id', is_numeric($normalizedToken) ? (int)$normalizedToken : 0)
            ->with([
                'vendor:id,name,code,address,logo_url',
                'area:id,name,code',
                'jobType:id,name,code',
                'pic:id,name',
                'checkIns' => function ($q) {
                    $q->orderBy('id', 'desc');
                },
                'evidencePhotos' => function ($q) {
                    $q->orderBy('id', 'asc');
                },
                'items',
                'baDocument:id,work_order_id,ba_number,status,created_at,pdf_url'
            ])
            ->first();

        if (!$workOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Tautan pemantauan SPK tidak ditemukan atau sudah kedaluwarsa.',
            ], 404);
        }

        if (!$workOrder->is_shareable) {
            return response()->json([
                'success' => false,
                'message' => 'Akses pemantauan publik untuk SPK ini telah dinonaktifkan oleh administrator.',
            ], 403);
        }

        $latestCheckIn = $workOrder->checkIns->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $workOrder->id,
                'spk_number' => $workOrder->spk_number,
                'title' => $workOrder->title,
                'status' => $workOrder->status,
                'progress_percent' => $workOrder->progress_percent,
                'location_name' => $workOrder->location_name,
                'area_name' => $workOrder->area?->name,
                'job_type_name' => $workOrder->jobType?->name,
                'doc_mode' => $workOrder->doc_mode,
                'start_date' => $workOrder->start_date ? date('d/m/Y', strtotime($workOrder->start_date)) : null,
                'deadline' => $workOrder->deadline ? date('d/m/Y', strtotime($workOrder->deadline)) : null,
                'notes' => $workOrder->notes,
                'target_lat' => $workOrder->target_lat,
                'target_lng' => $workOrder->target_lng,
                'vendor' => [
                    'name' => $workOrder->vendor?->name ?? 'Client',
                    'code' => $workOrder->vendor?->code,
                    'address' => $workOrder->vendor?->address,
                    'logo_url' => $workOrder->vendor?->logo_url,
                ],
                'pic' => [
                    'name' => $workOrder->pic?->name ?? 'Tim Lapangan',
                ],
                'check_in' => $latestCheckIn ? [
                    'id' => $latestCheckIn->id,
                    'latitude' => $latestCheckIn->latitude,
                    'longitude' => $latestCheckIn->longitude,
                    'accuracy' => $latestCheckIn->accuracy,
                    'distance_meters' => $latestCheckIn->distance_meters,
                    'is_within_radius' => $latestCheckIn->is_within_radius,
                    'check_in_time' => $latestCheckIn->created_at ? $latestCheckIn->created_at->format('d/m/Y H:i:s') : null,
                ] : null,
                'photos' => $workOrder->evidencePhotos->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'stage' => $p->stage,
                        'file_path' => $p->file_path,
                        'notes' => $p->notes,
                        'latitude' => $p->latitude,
                        'longitude' => $p->longitude,
                        'captured_at' => $p->captured_at ? date('d/m/Y H:i:s', strtotime($p->captured_at)) : null,
                        'file_hash' => $p->file_hash,
                    ];
                }),
                'items' => $workOrder->items->map(function ($i) {
                    return [
                        'id' => $i->id,
                        'item_name' => $i->item_name,
                        'status' => $i->status,
                        'notes' => $i->notes,
                    ];
                }),
                'ba_document' => $workOrder->baDocument ? [
                    'id' => $workOrder->baDocument->id,
                    'ba_number' => $workOrder->baDocument->ba_number,
                    'status' => $workOrder->baDocument->status,
                    'created_at' => $workOrder->baDocument->created_at ? $workOrder->baDocument->created_at->format('d/m/Y') : null,
                ] : null,
            ]
        ]);
    }

    /**
     * Generate or regenerate share token for a Work Order (Authenticated).
     */
    public function getOrCreateShareToken(Request $request, $id)
    {
        $workOrder = is_numeric($id) ? WorkOrder::find((int)$id) : null;
        if (!$workOrder) {
            $workOrder = WorkOrder::where('spk_number', $id)
                ->orWhere('spk_number', strtoupper($id))
                ->firstOrFail();
        }

        if (empty($workOrder->share_token)) {
            $workOrder->share_token = 'spk-' . strtolower(Str::random(10));
            $workOrder->is_shareable = true;
            $workOrder->save();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'share_token' => $workOrder->share_token,
                'is_shareable' => (bool)$workOrder->is_shareable,
                'share_url' => $workOrder->share_url,
            ]
        ]);
    }

    /**
     * Toggle is_shareable status (Authenticated).
     */
    public function toggleShareable(Request $request, $id)
    {
        $workOrder = is_numeric($id) ? WorkOrder::find((int)$id) : null;
        if (!$workOrder) {
            $workOrder = WorkOrder::where('spk_number', $id)
                ->orWhere('spk_number', strtoupper($id))
                ->firstOrFail();
        }

        $workOrder->is_shareable = !$workOrder->is_shareable;
        $workOrder->save();

        return response()->json([
            'success' => true,
            'is_shareable' => (bool)$workOrder->is_shareable,
            'message' => $workOrder->is_shareable ? 'Tautan pemantauan publik telah diaktifkan.' : 'Tautan pemantauan publik telah dinonaktifkan.',
        ]);
    }
}
