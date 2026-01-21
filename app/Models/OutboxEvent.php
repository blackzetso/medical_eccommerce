<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutboxEvent extends Model
{
    protected $fillable = [
        'event_type',
        'payload',
        'target_system',
        'source',
        'status',
        'attempts',
        'last_error',
        'available_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'available_at' => 'datetime',
        'attempts' => 'integer',
    ];
}
