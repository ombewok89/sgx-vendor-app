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
