<?php

namespace App\Services;

use App\Models\EvidencePhoto;
use App\Models\WorkOrder;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EvidenceService
{
    public static function storePhoto($user, WorkOrder $workOrder, UploadedFile $file, array $metadata): EvidencePhoto
    {
        // 1. Policy / Access check
        if ($user->hasRole('FIELD_TEAM')) {
            $isAssigned = $workOrder->pic_user_id === $user->id ||
                $workOrder->assignments()->where('users.id', $user->id)->exists();
            if (!$isAssigned) {
                throw new Exception('Akses Ditolak: Anda tidak ditugaskan pada Surat Perintah Kerja (SPK) ini.');
            }
        }

        // 2. Compute SHA-256 Hash
        $fileHash = hash_file('sha256', $file->getRealPath());

        // 3. Save File to physical storage directories with standard secure permissions
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowedExts)) {
            $extension = 'jpg';
        }
        $filename = time() . '-' . Str::random(12) . '.' . $extension;

        // Target direct physical folders
        $primaryDir = storage_path('app/public/uploads');
        if (!is_dir($primaryDir)) {
            @mkdir($primaryDir, 0755, true);
        }

        // Also ensure secondary mirrors exist
        $rootStorageDir = base_path('../storage/uploads');
        if (!is_dir($rootStorageDir) && file_exists(base_path('../.htaccess'))) {
            @mkdir($rootStorageDir, 0755, true);
        }

        $destFile = $primaryDir . DIRECTORY_SEPARATOR . $filename;
        
        // Copy physical bytes directly
        if (!copy($file->getRealPath(), $destFile)) {
            // Fallback to storeAs
            $path = $file->storeAs('uploads', $filename, 'public');
        } else {
            @chmod($destFile, 0644);
            $path = 'uploads/' . $filename;
        }

        // Mirror to root storage if accessible
        if (is_dir($rootStorageDir)) {
            @copy($destFile, $rootStorageDir . DIRECTORY_SEPARATOR . $filename);
            @chmod($rootStorageDir . DIRECTORY_SEPARATOR . $filename, 0644);
        }

        // Verify write succeeded
        if (!file_exists($destFile) || filesize($destFile) === 0) {
            // Last resort: standard storeAs
            $path = $file->storeAs('uploads', $filename, 'public');
        }

        // 4. Calculate sequence
        $stage = strtoupper($metadata['stage'] ?? 'AFTER');
        $itemId = !empty($metadata['item_id']) ? (int)$metadata['item_id'] : null;

        $lastSeq = EvidencePhoto::where('work_order_id', $workOrder->id)
            ->where('stage', $stage)
            ->when($itemId, fn($q) => $q->where('item_id', $itemId))
            ->max('sequence') ?? 0;
        $sequence = $lastSeq + 1;

        // 5. Intelligent Multi-Tier GPS Resolver (Zero-Zero Guard)
        $lat = !empty($metadata['latitude']) && abs((float)$metadata['latitude']) > 0.0001 ? (float)$metadata['latitude'] : null;
        $lng = !empty($metadata['longitude']) && abs((float)$metadata['longitude']) > 0.0001 ? (float)$metadata['longitude'] : null;
        $accuracy = !empty($metadata['accuracy']) ? (float)$metadata['accuracy'] : 8.0;

        // Tier 2: Check GPS from EXIF
        if ($lat === null || $lng === null) {
            try {
                if (function_exists('exif_read_data')) {
                    $exif = @exif_read_data($file->getRealPath(), 'GPS');
                    if (!empty($exif['GPSLatitude']) && !empty($exif['GPSLongitude'])) {
                        $lat = self::getGpsCoordinate($exif['GPSLatitude'], $exif['GPSLatitudeRef'] ?? 'N');
                        $lng = self::getGpsCoordinate($exif['GPSLongitude'], $exif['GPSLongitudeRef'] ?? 'E');
                    }
                }
            } catch (\Throwable $e) {}
        }

        // Tier 3: Check GPS from Field Team's Check-In
        if ($lat === null || $lng === null) {
            $latestCheckIn = $workOrder->checkIns()->latest('id')->first();
            if ($latestCheckIn && abs((float)$latestCheckIn->latitude) > 0.0001) {
                $lat = (float)$latestCheckIn->latitude;
                $lng = (float)$latestCheckIn->longitude;
                $accuracy = (float)($latestCheckIn->accuracy ?: 10.0);
            }
        }

        // Tier 4: Check GPS from Work Order Target Location
        if ($lat === null || $lng === null) {
            if ($workOrder->target_lat && abs((float)$workOrder->target_lat) > 0.0001) {
                $lat = (float)$workOrder->target_lat;
                $lng = (float)$workOrder->target_lng;
                $accuracy = 15.0;
            }
        }

        // Tier 5: Operational Regional Fallback (Bengkulu / Sumatera Region) if entirely blank
        if ($lat === null || $lng === null) {
            $lat = -3.824921;
            $lng = 102.286299;
            $accuracy = 25.0;
        }

        // 6. Create Database Record
        $photo = EvidencePhoto::create([
            'work_order_id' => $workOrder->id,
            'item_id' => $itemId,
            'user_id' => $user->id,
            'stage' => $stage,
            'sequence' => $sequence,
            'file_path' => '/storage/' . $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType() ?: 'image/jpeg',
            'file_hash' => $fileHash,
            'server_timestamp' => now(),
            'latitude' => $lat,
            'longitude' => $lng,
            'accuracy' => $accuracy,
            'notes' => $metadata['notes'] ?? null,
        ]);

        AuditService::log($user, 'UPLOAD_PHOTO', 'EVIDENCE_PHOTO', $photo->id, null, [
            'stage' => $stage,
            'sequence' => $sequence,
            'file_hash' => $fileHash,
            'latitude' => $lat,
            'longitude' => $lng,
        ]);

        WorkOrderService::recalculateProgress($workOrder);

        return $photo;
    }

    private static function getGpsCoordinate($coordinate, $ref)
    {
        if (!is_array($coordinate) || count($coordinate) < 3) return null;
        $degrees = self::gpsFractionToFloat($coordinate[0]);
        $minutes = self::gpsFractionToFloat($coordinate[1]);
        $seconds = self::gpsFractionToFloat($coordinate[2]);
        $flip = ($ref === 'S' || $ref === 'W') ? -1 : 1;
        return $flip * ($degrees + ($minutes / 60) + ($seconds / 3600));
    }

    private static function gpsFractionToFloat($fraction)
    {
        if (is_numeric($fraction)) return (float)$fraction;
        $parts = explode('/', $fraction);
        if (count($parts) === 2 && (float)$parts[1] > 0) {
            return (float)$parts[0] / (float)$parts[1];
        }
        return (float)$fraction;
    }
}
