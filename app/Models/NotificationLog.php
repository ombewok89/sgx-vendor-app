<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $table = 'notification_logs';

    protected $fillable = [
        'notification_feed_id',
        'recipient',
        'message_type',
        'message',
        'status',
        'fonnte_response_id',
        'error_message',
        'payload',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function notificationFeed()
    {
        return $this->belongsTo(NotificationFeed::class, 'notification_feed_id');
    }
}
