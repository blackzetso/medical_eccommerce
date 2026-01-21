<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'attributes',
        'status',
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function price(): HasOne
    {
        return $this->hasOne(VariantPrice::class, 'variant_id');
    }

    public function stock(): HasOne
    {
        return $this->hasOne(VariantStock::class, 'variant_id');
    }
}
