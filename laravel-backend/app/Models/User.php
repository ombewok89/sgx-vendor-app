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

    /**
     * Enhanced role check that handles Sanctum, Web, and memory caches safely.
     */
    public function hasRole($roles, string $guard = null): bool
    {
        $roleList = is_array($roles) ? $roles : [$roles];
        
        // Get user role names from relation or eager-loaded collection
        $userRoles = $this->relationLoaded('roles')
            ? $this->roles->pluck('name')->toArray()
            : $this->roles()->pluck('name')->toArray();

        foreach ($roleList as $r) {
            if (in_array($r, $userRoles, true)) {
                return true;
            }
            // SUPERUSER is also SUPERVISOR and vice versa for system permissions
            if (in_array($r, ['SUPERUSER', 'SUPERVISOR'], true) && (in_array('SUPERUSER', $userRoles, true) || in_array('SUPERVISOR', $userRoles, true))) {
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
