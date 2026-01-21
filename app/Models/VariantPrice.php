<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantPrice extends Model
{
    protected $fillable = [
        'variant_id',
        'amount',
        'currency',
        'tax_included',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_included' => 'boolean',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
