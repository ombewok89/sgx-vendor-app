<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log($user, string $action, string $entityType, ?int $entityId, $oldValue = null, $newValue = null)
    {
        try {
            AuditLog::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_value' => $oldValue ? (is_string($oldValue) ? $oldValue : json_encode($oldValue)) : null,
                'new_value' => $newValue ? (is_string($newValue) ? $newValue : json_encode($newValue)) : null,
                'ip_address' => Request::ip() ?? '127.0.0.1',
            ]);
        } catch (\Throwable $e) {
            // Silently ignore audit logging errors
        }
    }
}
