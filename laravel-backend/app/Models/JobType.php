<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'doc_mode',
        'min_photos_per_stage',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_photos_per_stage' => 'integer',
    ];

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}
