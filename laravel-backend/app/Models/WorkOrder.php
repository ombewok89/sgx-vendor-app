<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $fillable = [
        'spk_number',
        'title',
        'vendor_id',
        'area_id',
        'job_type_id',
        'location_name',
        'target_lat',
        'target_lng',
        'pic_user_id',
        'start_date',
        'deadline',
        'doc_mode',
        'require_checkin',
        'status',
        'progress_percent',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'target_lat' => 'decimal:7',
        'target_lng' => 'decimal:7',
        'require_checkin' => 'boolean',
        'progress_percent' => 'integer',
        'start_date' => 'date:Y-m-d',
        'deadline' => 'date:Y-m-d',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function jobType()
    {
        return $this->belongsTo(JobType::class);
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments()
    {
        return $this->belongsToMany(User::class, 'work_order_user')
            ->withPivot('role_in_team', 'assigned_at')
            ->withTimestamps();
    }

    public function items()
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    public function evidencePhotos()
    {
        return $this->hasMany(EvidencePhoto::class);
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class);
    }

    public function baDocument()
    {
        return $this->hasOne(BaDocument::class);
    }
}
