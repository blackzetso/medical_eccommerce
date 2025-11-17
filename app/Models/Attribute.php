<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Attribute extends Model
{
    protected $fillable = [
        'name',
        'slug', 
        'type',
        'description',
        'is_required',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'status' => 'boolean'
    ];

    /**
     * Get the attribute values for this attribute.
     */
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    /**
     * Get the products that have this attribute.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attributes')
                    ->withPivot('attribute_value_id', 'price_adjustment')
                    ->withTimestamps();
    }
}
