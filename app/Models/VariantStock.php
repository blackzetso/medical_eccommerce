<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantStock extends Model
{
    protected $fillable = [
        'variant_id',
        'available',
        'reserved',
    ];

    protected $casts = [
        'available' => 'integer',
        'reserved' => 'integer',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
