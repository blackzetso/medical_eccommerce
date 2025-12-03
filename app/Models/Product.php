<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'slug',
        'description',
        'short_description',
        'price',
        'sale_price',
        'discount_type',
        'discount_value',
        'stock_quantity',
        'manage_stock',
        'in_stock',
        'is_featured',
        'status',
        'sku',
        'weight',
        'dimensions',
        'images',
        'meta_title',
        'meta_description',
        'category_id',
        'brand_id'
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'weight' => 'decimal:2',
        'manage_stock' => 'boolean',
        'in_stock' => 'boolean',
        'is_featured' => 'boolean',
        'status' => 'boolean',
        'colors' => 'array' // Cast colors as an array
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the brand that owns the product.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the attributes for this product.
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
                    ->withPivot('attribute_value_id', 'price_adjustment')
                    ->withTimestamps();
    }

    /**
     * Get the attribute values for this product.
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attributes')
                    ->withPivot('attribute_id', 'price_adjustment')
                    ->withTimestamps();
    }

    /**
     * Get the final price based on discount type
     */
    public function getFinalPriceAttribute()
    {
        if ($this->discount_type === 'none' || !$this->discount_value) {
            return $this->price;
        }

        if ($this->discount_type === 'fixed') {
            return max(0, $this->price - $this->discount_value);
        }

        if ($this->discount_type === 'percentage') {
            $discountAmount = ($this->price * $this->discount_value) / 100;
            return max(0, $this->price - $discountAmount);
        }

        return $this->price;
    }

    /**
     * Check if product has a discount
     */
    public function getHasDiscountAttribute()
    {
        return $this->discount_type !== 'none' && $this->discount_value > 0;
    }

    /**
     * Get the calculated sale price
     */
    public function getCalculatedSalePriceAttribute()
    {
        if (!$this->has_discount) {
            return null;
        }

        return $this->final_price;
    }

    /**
     * Get the main image
     */
    public function getMainImageAttribute()
    {
        if ($this->images && count($this->images) > 0) {
            return $this->images[0];
        }
        return '/front/theme1/images/no-image.png';
    }

    /**
     * Get images as array (for debugging)
     */
    public function getImagesListAttribute()
    {
        Log::info('Images raw from DB:', ['images' => $this->attributes['images'] ?? null]);
        Log::info('Images after cast:', ['images' => $this->images]);
        return $this->images;
    }
}
