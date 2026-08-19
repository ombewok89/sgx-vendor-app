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

        // 5. Create Database Record
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
            'latitude' => !empty($metadata['latitude']) ? (float)$metadata['latitude'] : null,
            'longitude' => !empty($metadata['longitude']) ? (float)$metadata['longitude'] : null,
            'accuracy' => !empty($metadata['accuracy']) ? (float)$metadata['accuracy'] : null,
            'notes' => $metadata['notes'] ?? null,
        ]);

        AuditService::log($user, 'UPLOAD_PHOTO', 'EVIDENCE_PHOTO', $photo->id, null, [
            'stage' => $stage,
            'sequence' => $sequence,
            'file_hash' => $fileHash,
        ]);

        WorkOrderService::recalculateProgress($workOrder);

        return $photo;
    }
}
