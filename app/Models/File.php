<?php

namespace App\Models;

use App\Models\Lecture;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'name',
        'url',
        'path',
        'type',
        'description',
        'video_id',
        'lecture_id',
    ];

    public function lecture()
    {
        return $this->belongsTo(Lecture::class);
    }
}
