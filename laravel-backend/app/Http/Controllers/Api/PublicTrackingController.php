<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicTrackingController extends Controller
{
    private function ensureShareColumnsExist()
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('work_orders', 'share_token')) {
                \Illuminate\Support\Facades\Schema::table('work_orders', function ($table) {
                    $table->string('share_token', 64)->nullable()->unique()->after('notes');
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('work_orders', 'is_shareable')) {
                \Illuminate\Support\Facades\Schema::table('work_orders', function ($table) {
                    $table->boolean('is_shareable')->default(true)->after('share_token');
                });
            }
        } catch (\Exception $e) {
            \Log::warning('Auto-migration for share_token columns: ' . $e->getMessage());
        }
    }

    /**
     * Public Guest Endpoint to view live tracking data of a Work Order.
     * No authentication required. Financial data is strictly excluded.
     */
    public function track(string $token)
    {
        $this->ensureShareColumnsExist();

        try {
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
                    'vendor:id,name,code,address',
                    'area:id,name',
                    'jobType:id,name,code',
                    'pic:id,name',
                    'checkIns' => function ($q) {
                        $q->orderBy('id', 'desc');
                    },
                    'evidencePhotos' => function ($q) {
                        $q->with('item:id,item_name')->orderBy('id', 'asc');
                    },
                    'items',
                    'baDocument:id,work_order_id,ba_number,status,created_at,pdf_path'
                ])
                ->first();

            if (!$workOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tautan pemantauan SPK tidak ditemukan atau sudah kedaluwarsa.',
                ], 404);
            }

            // If is_shareable is explicitly false, reject access
            if ($workOrder->is_shareable === false || $workOrder->is_shareable === 0) {
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
                        'check_in_time' => $latestCheckIn->created_at ? date('d/m/Y H:i:s', strtotime($latestCheckIn->created_at)) : null,
                    ] : null,
                    'photos' => $workOrder->evidencePhotos->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'item_id' => $p->work_order_item_id ?? $p->item_id,
                            'item_name' => $p->item?->item_name,
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
                            'weight_percent' => $i->weight_percent ?? 0,
                            'is_addendum' => (bool)$i->is_addendum,
                        ];
                    }),
                    'ba_document' => $workOrder->baDocument ? [
                        'id' => $workOrder->baDocument->id,
                        'ba_number' => $workOrder->baDocument->ba_number,
                        'status' => $workOrder->baDocument->status,
                        'created_at' => $workOrder->baDocument->created_at ? date('d/m/Y', strtotime($workOrder->baDocument->created_at)) : null,
                    ] : null,
                ]
            ]);
        } catch (\Throwable $e) {
            \Log::error('Public track endpoint error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat pelacakan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate or regenerate share token for a Work Order (Authenticated).
     */
    public function getOrCreateShareToken(Request $request, $id)
    {
        $this->ensureShareColumnsExist();

        try {
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
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat tautan bagikan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle is_shareable status (Authenticated).
     */
    public function toggleShareable(Request $request, $id)
    {
        $this->ensureShareColumnsExist();

        try {
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
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status tautan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
