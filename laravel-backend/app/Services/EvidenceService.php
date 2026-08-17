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

        // 3. Save File to public disk
        if (!Storage::disk('public')->exists('uploads')) {
            Storage::disk('public')->makeDirectory('uploads');
        }

        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = time() . '-' . Str::random(10) . '.' . $extension;
        $path = $file->storeAs('uploads', $filename, 'public');

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

        return $photo;
    }
}
