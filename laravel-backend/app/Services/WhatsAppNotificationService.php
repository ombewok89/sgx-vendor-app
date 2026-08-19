<?php

namespace App\Services;

use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\BaDocument;
use App\Models\Revision;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Log;

/**
 * WhatsAppNotificationService — Business event dispatcher for WhatsApp notifications.
 *
 * PRODUCTION HARDENED:
 *   - Dispatches via SendWhatsAppNotificationJob (queued/async, non-blocking).
 *   - Dynamic recipient resolution from database.
 *   - Never breaks business transactions.
 */
class WhatsAppNotificationService
{
    /**
     * Helper to dispatch job safely without crashing.
     */
    public static function dispatch(
        string $phone,
        string $templateKey,
        array $params = [],
        ?string $idempotencyKey = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $existingLogId = null
    ): void {
        try {
            if (empty(trim($phone))) return;

            SendWhatsAppNotificationJob::dispatch(
                $phone,
                $templateKey,
                $params,
                $idempotencyKey,
                $referenceType,
                $referenceId,
                $existingLogId
            );
        } catch (\Throwable $e) {
            Log::warning('[WhatsApp Dispatcher] Failed to dispatch job, falling back to sync: ' . $e->getMessage());
            FonnteService::sendTemplatedMessage(
                $phone,
                $templateKey,
                $params,
                $idempotencyKey,
                $referenceType,
                $referenceId,
                $existingLogId
            );
        }
    }

    /**
     * Trigger 1: WORK_ORDER_CREATED
     */
    public static function onWorkOrderCreated(WorkOrder $workOrder): void
    {
        try {
            $workOrder->loadMissing(['vendor', 'area', 'jobType', 'pic']);

            $params = [
                'spk_number'    => $workOrder->spk_number,
                'project_name'  => $workOrder->title,
                'location_name' => $workOrder->location_name,
                'client_name'   => $workOrder->vendor?->name ?? 'Mitra Klien',
                'status'        => $workOrder->status,
                'date'          => $workOrder->deadline ? date('d-m-Y', strtotime($workOrder->deadline)) : '-',
            ];

            // 1. Notify Client / Vendor Contact Person
            $vendorPhone = $workOrder->vendor?->phone;
            if (!empty($vendorPhone)) {
                self::dispatch(
                    $vendorPhone,
                    'WORK_ORDER_CREATED',
                    $params,
                    "WORK_ORDER_CREATED:{$workOrder->id}:VENDOR",
                    'WORK_ORDER',
                    $workOrder->id
                );
            }

            // 2. If PIC was already assigned at creation time, also notify PIC
            if ($workOrder->pic && !empty($workOrder->pic->phone)) {
                self::onWorkOrderAssigned($workOrder, $workOrder->pic);
            }
        } catch (\Throwable $e) {
            Log::warning('[WhatsApp] onWorkOrderCreated notification error: ' . $e->getMessage());
        }
    }

    /**
     * Trigger 2: WORK_ORDER_ASSIGNED
     */
    public static function onWorkOrderAssigned(WorkOrder $workOrder, ?User $picUser = null): void
    {
        try {
            $workOrder->loadMissing(['vendor', 'area', 'jobType', 'pic']);
            $targetUser = $picUser ?? $workOrder->pic;

            if (!$targetUser || empty($targetUser->phone)) {
                return;
            }

            $params = [
                'user_name'     => $targetUser->name,
                'spk_number'    => $workOrder->spk_number,
                'project_name'  => $workOrder->title,
                'location_name' => $workOrder->location_name,
                'date'          => $workOrder->deadline ? date('d-m-Y', strtotime($workOrder->deadline)) : '-',
            ];

            self::dispatch(
                $targetUser->phone,
                'WORK_ORDER_ASSIGNED',
                $params,
                "WORK_ORDER_ASSIGNED:{$workOrder->id}:{$targetUser->id}",
                'WORK_ORDER',
                $workOrder->id
            );
        } catch (\Throwable $e) {
            Log::warning('[WhatsApp] onWorkOrderAssigned notification error: ' . $e->getMessage());
        }
    }

    /**
     * Trigger 3: SUBMISSION_RECEIVED
     */
    public static function onSubmissionReceived(WorkOrder $workOrder): void
    {
        try {
            $workOrder->loadMissing(['vendor', 'pic', 'creator']);

            $adminPhone = SystemSetting::where('key', 'admin_whatsapp_number')->value('value');
            if (empty($adminPhone) && $workOrder->creator && !empty($workOrder->creator->phone)) {
                $adminPhone = $workOrder->creator->phone;
            }

            if (empty($adminPhone)) {
                return;
            }

            $params = [
                'spk_number'    => $workOrder->spk_number,
                'location_name' => $workOrder->location_name,
                'user_name'     => $workOrder->pic?->name ?? 'Teknisi Lapangan',
                'date'          => now()->translatedFormat('d F Y, H:i') . ' WIB',
            ];

            self::dispatch(
                $adminPhone,
                'SUBMISSION_RECEIVED',
                $params,
                "SUBMISSION_RECEIVED:{$workOrder->id}",
                'WORK_ORDER',
                $workOrder->id
            );
        } catch (\Throwable $e) {
            Log::warning('[WhatsApp] onSubmissionReceived notification error: ' . $e->getMessage());
        }
    }

    /**
     * Trigger 4A: REVIEW_APPROVED
     */
    public static function onReviewApproved(WorkOrder $workOrder): void
    {
        try {
            $workOrder->loadMissing(['vendor', 'pic', 'baDocument']);

            $params = [
                'spk_number'    => $workOrder->spk_number,
                'location_name' => $workOrder->location_name,
                'client_name'   => $workOrder->vendor?->name ?? 'Mitra Klien',
                'date'          => now()->translatedFormat('d F Y, H:i') . ' WIB',
            ];

            // 1. Notify PIC Technician
            if ($workOrder->pic && !empty($workOrder->pic->phone)) {
                self::dispatch(
                    $workOrder->pic->phone,
                    'REVIEW_APPROVED',
                    $params,
                    "REVIEW_APPROVED:{$workOrder->id}:PIC",
                    'WORK_ORDER',
                    $workOrder->id
                );
            }

            // 2. Notify Client Contact
            if ($workOrder->vendor && !empty($workOrder->vendor->phone)) {
                self::dispatch(
                    $workOrder->vendor->phone,
                    'REVIEW_APPROVED',
                    $params,
                    "REVIEW_APPROVED:{$workOrder->id}:CLIENT",
                    'WORK_ORDER',
                    $workOrder->id
                );
            }
        } catch (\Throwable $e) {
            Log::warning('[WhatsApp] onReviewApproved notification error: ' . $e->getMessage());
        }
    }

    /**
     * Trigger 4B: REVISION_REQUIRED
     */
    public static function onRevisionRequired(WorkOrder $workOrder, Revision $revision): void
    {
        try {
            $workOrder->loadMissing(['pic']);

            if (!$workOrder->pic || empty($workOrder->pic->phone)) {
                return;
            }

            $params = [
                'user_name'     => $workOrder->pic->name,
                'spk_number'    => $workOrder->spk_number,
                'location_name' => $workOrder->location_name,
                'notes'         => $revision->reason ?: 'Foto perlu diperbaiki sesuai standar.',
            ];

            self::dispatch(
                $workOrder->pic->phone,
                'REVISION_REQUIRED',
                $params,
                "REVISION_REQUIRED:{$workOrder->id}:{$revision->id}",
                'WORK_ORDER',
                $workOrder->id
            );
        } catch (\Throwable $e) {
            Log::warning('[WhatsApp] onRevisionRequired notification error: ' . $e->getMessage());
        }
    }

    /**
     * Trigger 5: BA_ISSUED
     */
    public static function onBaIssued(BaDocument $ba): void
    {
        try {
            $ba->loadMissing(['workOrder.vendor']);
            $wo = $ba->workOrder;

            if (!$wo || !$wo->vendor || empty($wo->vendor->phone)) {
                return;
            }

            $params = [
                'ba_number'     => $ba->ba_number,
                'spk_number'    => $wo->spk_number,
                'location_name' => $wo->location_name,
                'client_name'   => $wo->vendor->name,
                'date'          => $ba->ba_date ? date('d-m-Y', strtotime($ba->ba_date)) : date('d-m-Y'),
            ];

            self::dispatch(
                $wo->vendor->phone,
                'BA_ISSUED',
                $params,
                "BA_ISSUED:{$ba->id}",
                'BA_DOCUMENT',
                $ba->id
            );
        } catch (\Throwable $e) {
            Log::warning('[WhatsApp] onBaIssued notification error: ' . $e->getMessage());
        }
    }
}
