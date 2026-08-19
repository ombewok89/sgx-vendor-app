<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldTeam extends Model
{
    protected $fillable = [
        'name',
        'leader_user_id',
        'area_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'leader_name',
        'leader_phone',
        'area_name',
    ];

    public function getLeaderNameAttribute()
    {
        return $this->leader ? $this->leader->name : null;
    }

    public function getLeaderPhoneAttribute()
    {
        return $this->leader ? $this->leader->phone : null;
    }

    public function getAreaNameAttribute()
    {
        return $this->area ? $this->area->name : null;
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_user_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'field_team_user')
            ->withTimestamps();
    }
}
