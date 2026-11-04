<?php

namespace App\Services;

use App\Models\CheckIn;
use App\Models\WorkOrder;
use Exception;

class CheckInService
{
    public static function calculateDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }

    public static function checkIn(WorkOrder $workOrder, $user, array $data): CheckIn
    {
        $lat = (float) ($data['latitude'] ?? 0);
        $lng = (float) ($data['longitude'] ?? 0);
        $accuracy = (float) ($data['accuracy'] ?? 0);
        $addressNote = $data['address_note'] ?? ($data['notes'] ?? null);

        $settingRadius = \App\Models\SystemSetting::where('key', 'geofence_default_radius_meters')->value('value');
        $maxRadiusMeters = (float) ($settingRadius ?: ($data['max_radius_meters'] ?? 500));

        $isRequireCheckin = filter_var($workOrder->require_checkin, FILTER_VALIDATE_BOOLEAN);

        // Hanya lakukan validasi radius ketat jika require_checkin AKTIF dan target koordinat tersedia
        if ($isRequireCheckin && !empty($workOrder->target_lat) && !empty($workOrder->target_lng) && $lat && $lng) {
            $distance = self::calculateDistanceMeters(
                $lat,
                $lng,
                (float) $workOrder->target_lat,
                (float) $workOrder->target_lng
            );

            if ($distance > $maxRadiusMeters) {
                throw new Exception("Posisi Anda berada di luar radius lokasi pekerjaan ({$distance} meter dari target, batas maksimal {$maxRadiusMeters} meter). Silakan mendekat ke lokasi cabang atau nonaktifkan wajib cek lokasi.");
            }
        }

        $checkIn = CheckIn::create([
            'work_order_id' => $workOrder->id,
            'user_id' => $user->id,
            'server_timestamp' => now(),
            'client_timestamp' => $data['client_timestamp'] ?? now()->toIso8601String(),
            'latitude' => $lat,
            'longitude' => $lng,
            'accuracy' => $accuracy,
            'address_note' => $addressNote,
        ]);

        if (in_array($workOrder->status, ['READY', 'ASSIGNED', 'DRAFT'])) {
            $workOrder->update(['status' => 'IN_PROGRESS']);
        }

        AuditService::log($user, 'CHECK_IN', 'WORK_ORDER', $workOrder->id, null, [
            'latitude' => $lat,
            'longitude' => $lng,
            'accuracy' => $accuracy,
            'address_note' => $addressNote,
        ]);

        return $checkIn;
    }
}
