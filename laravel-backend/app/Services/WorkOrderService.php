<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Models\NotificationFeed;
use Exception;
use Illuminate\Support\Facades\DB;

class WorkOrderService
{
    public static function generateSpkNumber(): string
    {
        $prefix = 'SPK-SGX-' . date('Ymd') . '-';
        $latest = WorkOrder::where('spk_number', 'LIKE', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        if ($latest) {
            $parts = explode('-', $latest->spk_number);
            $seq = intval(end($parts)) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public static function getScopedQuery($user)
    {
        $query = WorkOrder::with([
            'vendor:id,code,name',
            'area:id,name,province,city',
            'jobType:id,code,name,doc_mode',
            'pic:id,name,phone',
            'assignments:id,name,phone',
            'items',
            'checkIns',
            'evidencePhotos',
            'issues',
            'reviews',
            'revisions',
            'baDocument'
        ]);

        if ($user->hasAnyRole(['VENDOR', 'CLIENT'])) {
            if ($user->vendor_id) {
                $query->where('vendor_id', $user->vendor_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->hasRole('FIELD_TEAM')) {
            $query->where(function ($q) use ($user) {
                $q->where('pic_user_id', $user->id)
                  ->orWhereHas('assignments', function ($aq) use ($user) {
                      $aq->where('users.id', $user->id);
                  });
            });
        }

        return $query;
    }

    public static function createWorkOrder($user, array $data): WorkOrder
    {
        return DB::transaction(function () use ($user, $data) {
            $spkNumber = self::generateSpkNumber();

            $workOrder = WorkOrder::create([
                'spk_number' => $spkNumber,
                'title' => $data['title'],
                'vendor_id' => $data['vendor_id'],
                'area_id' => $data['area_id'],
                'job_type_id' => $data['job_type_id'] ?? null,
                'location_name' => $data['location_name'],
                'target_lat' => $data['target_lat'] ?? null,
                'target_lng' => $data['target_lng'] ?? null,
                'pic_user_id' => $data['pic_user_id'] ?? null,
                'start_date' => $data['start_date'],
                'deadline' => $data['deadline'],
                'doc_mode' => $data['doc_mode'] ?? 'BEFORE_PROCESS_AFTER',
                'require_checkin' => $data['require_checkin'] ?? true,
                'status' => !empty($data['pic_user_id']) ? 'ASSIGNED' : 'DRAFT',
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            // Assign PIC & Members
            $assignments = [];
            if (!empty($data['pic_user_id'])) {
                $assignments[$data['pic_user_id']] = ['role_in_team' => 'PIC', 'assigned_at' => now()];
            }
            if (!empty($data['member_user_ids']) && is_array($data['member_user_ids'])) {
                foreach ($data['member_user_ids'] as $mId) {
                    if ($mId != $data['pic_user_id']) {
                        $assignments[$mId] = ['role_in_team' => 'MEMBER', 'assigned_at' => now()];
                    }
                }
            }
            if (!empty($assignments)) {
                $workOrder->assignments()->sync($assignments);
            }

            // Create Sub Work Items
            if (!empty($data['items']) && is_array($data['items'])) {
                $weight = floor(100 / count($data['items']));
                foreach ($data['items'] as $item) {
                    WorkOrderItem::create([
                        'work_order_id' => $workOrder->id,
                        'item_name' => is_string($item) ? $item : ($item['item_name'] ?? 'Item Pekerjaan'),
                        'job_type_id' => is_array($item) ? ($item['job_type_id'] ?? $workOrder->job_type_id) : $workOrder->job_type_id,
                        'doc_mode' => is_array($item) ? ($item['doc_mode'] ?? $workOrder->doc_mode) : $workOrder->doc_mode,
                        'weight_percent' => is_array($item) ? ($item['weight_percent'] ?? $weight) : $weight,
                        'status' => 'PENDING',
                    ]);
                }
            }

            AuditService::log($user, 'CREATE_WORK_ORDER', 'WORK_ORDER', $workOrder->id, null, $workOrder->toArray());

            return $workOrder->load(['vendor', 'area', 'jobType', 'pic', 'assignments', 'items']);
        });
    }

    public static function recalculateProgress(WorkOrder $workOrder): int
    {
        $items = $workOrder->items()->get();
        $photos = $workOrder->evidencePhotos()->get();

        if (in_array($workOrder->status, ['APPROVED', 'COMPLETED', 'BA_OPNAME'])) {
            $workOrder->update(['progress_percent' => 100]);
            return 100;
        }

        if (in_array($workOrder->status, ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'])) {
            $workOrder->update(['progress_percent' => 100]);
            return 100;
        }

        $totalProgress = 0;

        if ($items->count() > 0) {
            foreach ($items as $item) {
                $itemPhotos = $photos->where('item_id', $item->id);
                $hasBefore = $itemPhotos->where('stage', 'BEFORE')->count() > 0;
                $hasProcess = $itemPhotos->where('stage', 'PROCESS')->count() > 0;
                $hasAfter = $itemPhotos->where('stage', 'AFTER')->count() > 0;

                $weight = $item->weight_percent ?: floor(100 / $items->count());
                $ratio = 0;

                if ($hasAfter) {
                    $ratio = 1.0;
                    $item->update(['status' => 'COMPLETED']);
                } elseif ($hasProcess) {
                    $ratio = 0.65;
                    $item->update(['status' => 'IN_PROGRESS']);
                } elseif ($hasBefore) {
                    $ratio = 0.35;
                    $item->update(['status' => 'IN_PROGRESS']);
                } else {
                    $item->update(['status' => 'PENDING']);
                }

                $totalProgress += ($weight * $ratio);
            }
        } else {
            // Fallback for work order without explicit sub-items
            $hasBefore = $photos->where('stage', 'BEFORE')->count() > 0;
            $hasProcess = $photos->where('stage', 'PROCESS')->count() > 0;
            $hasAfter = $photos->where('stage', 'AFTER')->count() > 0;

            if ($hasAfter) {
                $totalProgress = 100;
            } elseif ($hasProcess) {
                $totalProgress = 65;
            } elseif ($hasBefore) {
                $totalProgress = 35;
            } elseif ($workOrder->checkIns()->count() > 0) {
                $totalProgress = 15;
            }
        }

        $calcPercent = (int) round($totalProgress);
        $calcPercent = max(0, min(100, $calcPercent));

        $updateData = ['progress_percent' => $calcPercent];
        if ($calcPercent > 0 && in_array($workOrder->status, ['DRAFT', 'READY', 'ASSIGNED'])) {
            $updateData['status'] = 'IN_PROGRESS';
        }

        $workOrder->update($updateData);

        return $calcPercent;
    }
}
