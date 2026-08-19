<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'channel',
        'provider',
        'recipient',
        'message_type',
        'reference_type',
        'reference_id',
        'idempotency_key',
        'payload',
        'status',
        'attempts',
        'failure_type',
        'provider_response',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at'  => 'datetime',
        'attempts' => 'integer',
    ];
}
