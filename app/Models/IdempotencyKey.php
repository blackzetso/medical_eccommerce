<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'method',
        'path',
        'body_hash',
        'response',
    ];

    protected $casts = [
        'response' => 'array',
    ];
}
