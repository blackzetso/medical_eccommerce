<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'pharmacy_name',
        'address',
        'notes',
        'status',
        'converted_by',
        'converted_at',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
    ];

    /**
     * Get the user who converted this lead
     */
    public function convertedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by');
    }

    /**
     * Check if lead is converted
     */
    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }
}
