<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderItem extends Model
{
    protected $fillable = [
        'work_order_id',
        'item_name',
        'job_type_id',
        'doc_mode',
        'weight_percent',
        'status',
        'is_addendum',
        'notes',
    ];

    protected $casts = [
        'weight_percent' => 'integer',
        'is_addendum' => 'boolean',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function jobType()
    {
        return $this->belongsTo(JobType::class);
    }

    public function evidencePhotos()
    {
        return $this->hasMany(EvidencePhoto::class, 'item_id');
    }
}
