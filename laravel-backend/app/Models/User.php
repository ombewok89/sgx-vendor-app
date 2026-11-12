<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $guard_name = 'sanctum';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'vendor_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function assignedWorkOrders()
    {
        return $this->belongsToMany(WorkOrder::class, 'work_order_user')
            ->withPivot('role_in_team', 'assigned_at')
            ->withTimestamps();
    }

    public function fieldTeams()
    {
        return $this->belongsToMany(FieldTeam::class, 'field_team_user')
            ->withTimestamps();
    }

    public function readNotifications()
    {
        return $this->belongsToMany(NotificationFeed::class, 'notification_feed_user_read', 'user_id', 'notification_feed_id')
            ->withPivot('read_at');
    }

    /**
     * Enhanced role check that handles Sanctum, Web, column role, and memory caches safely.
     */
    public function hasRole($roles, string $guard = null): bool
    {
        $roleList = is_array($roles) ? $roles : [$roles];
        
        $userRoles = [];
        // 1. Direct column on users table (Single Source of Truth)
        if (!empty($this->role)) {
            $userRoles[] = strtoupper(trim((string)$this->role));
        }

        // 2. Try Spatie roles relation safely (catch exception if table missing or corrupted)
        try {
            $relationRoles = $this->relationLoaded('roles')
                ? $this->roles->pluck('name')->toArray()
                : $this->roles()->pluck('name')->toArray();
            if (!empty($relationRoles)) {
                $userRoles = array_unique(array_merge($userRoles, array_map('strtoupper', $relationRoles)));
            }
        } catch (\Throwable $e) {
            // Silently fall back to users.role column
        }

        foreach ($roleList as $r) {
            $rUpper = strtoupper(trim((string)$r));
            if (in_array($rUpper, $userRoles, true)) {
                return true;
            }
            // SUPERUSER satisfies checks for SUPERVISOR or ADMIN
            if (in_array('SUPERUSER', $userRoles, true) && in_array($rUpper, ['SUPERVISOR', 'ADMIN'], true)) {
                return true;
            }
            // SUPERVISOR / ADMIN interchangeability for management operations
            if (in_array($rUpper, ['SUPERVISOR', 'ADMIN'], true) && (in_array('SUPERVISOR', $userRoles, true) || in_array('ADMIN', $userRoles, true))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enhanced hasAnyRole check.
     */
    public function hasAnyRole(...$roles): bool
    {
        $flattened = [];
        foreach ($roles as $role) {
            if (is_array($role)) {
                $flattened = array_merge($flattened, $role);
            } else {
                $flattened[] = $role;
            }
        }
        return $this->hasRole($flattened);
    }
}
