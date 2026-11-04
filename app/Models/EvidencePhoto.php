<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvidencePhoto extends Model
{
    protected $fillable = [
        'work_order_id',
        'item_id',
        'user_id',
        'stage',
        'sequence',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'file_hash',
        'server_timestamp',
        'latitude',
        'longitude',
        'accuracy',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'accuracy' => 'decimal:2',
        'sequence' => 'integer',
        'file_size' => 'integer',
        'server_timestamp' => 'datetime',
    ];

    protected $appends = [
        'file_url',
    ];

    public function getFileUrlAttribute()
    {
        if (empty($this->file_path)) {
            return null;
        }
        $clean = ltrim(str_replace('/storage/', '', $this->file_path), '/');
        return '/stream.php?file=' . urlencode($clean);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(WorkOrderItem::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
