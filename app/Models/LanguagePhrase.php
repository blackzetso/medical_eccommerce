<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LanguagePhrase extends Model
{
    protected $fillable = ['key', 'word', 'language_id', 'group'];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
