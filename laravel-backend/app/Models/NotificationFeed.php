<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationFeed extends Model
{
    protected $table = 'notifications_feed';

    protected $fillable = [
        'work_order_id',
        'client_id',
        'target_user_id',
        'target_role',
        'category',
        'title',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function client()
    {
        return $this->belongsTo(Vendor::class, 'client_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function readUsers()
    {
        return $this->belongsToMany(User::class, 'notification_feed_user_read')
            ->withPivot('read_at')
            ->withTimestamps();
    }
}
