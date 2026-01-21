<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalReference extends Model
{
    protected $fillable = [
        'local_type',
        'local_id',
        'remote_id',
        'source_system',
    ];
}
