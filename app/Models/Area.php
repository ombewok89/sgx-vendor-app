<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
        'name',
        'province',
        'city',
        'district',
    ];

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function fieldTeams()
    {
        return $this->hasMany(FieldTeam::class);
    }
}
