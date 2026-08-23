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
        'use_timestamp',
        'status',
        'progress_percent',
        'is_archived',
        'archived_at',
        'notes',
        'share_token',
        'is_shareable',
        'created_by',
    ];

    protected $casts = [
        'target_lat' => 'decimal:7',
        'target_lng' => 'decimal:7',
        'require_checkin' => 'boolean',
        'use_timestamp' => 'boolean',
        'is_shareable' => 'boolean',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
        'progress_percent' => 'integer',
        'start_date' => 'date:Y-m-d',
        'deadline' => 'date:Y-m-d',
    ];

    protected $appends = [
        'vendor_name',
        'vendor_code',
        'pic_name',
        'pic_phone',
        'area_name',
        'job_type_name',
        'share_url',
    ];

    public function getShareUrlAttribute()
    {
        return $this->share_token ? url('/track/' . $this->share_token) : null;
    }

    public function getVendorNameAttribute()
    {
        return $this->vendor ? $this->vendor->name : null;
    }

    public function getVendorCodeAttribute()
    {
        return $this->vendor ? $this->vendor->code : null;
    }

    public function getPicNameAttribute()
    {
        return $this->pic ? $this->pic->name : null;
    }

    public function getPicPhoneAttribute()
    {
        return $this->pic ? $this->pic->phone : null;
    }

    public function getAreaNameAttribute()
    {
        return $this->area ? $this->area->name : null;
    }

    public function getJobTypeNameAttribute()
    {
        return $this->jobType ? $this->jobType->name : null;
    }

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
