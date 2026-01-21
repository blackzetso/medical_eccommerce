<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'cost',
        'profit_margin',
        'sale_price',
        'discount_type',
        'discount_value',
        'stock_quantity',
        'manage_stock',
        'in_stock',
        'is_featured',
        'status',
        'sku',
        'product_code',
        'weight',
        'dimensions',
        'images',
        'main_image',
        'meta_title',
        'meta_description',
        'category_id',
        'brand_id'
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'profit_margin' => 'decimal:2',
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
     * Product variants for sync layer.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the attributes for this product.
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
                    ->withPivot('attribute_value_id', 'price_adjustment', 'stock_quantity')
                    ->withTimestamps();
    }

    /**
     * Get the attribute values for this product.
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attributes')
                    ->withPivot('attribute_id', 'price_adjustment', 'stock_quantity')
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
        // إذا كان هناك main_image محدد، استخدمه
        if ($this->attributes['main_image'] ?? null) {
            return $this->attributes['main_image'];
        }
        // وإلا استخدم أول صورة من المصفوفة
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
        return $this->images;
    }

    /**
     * Calculate price from cost and profit margin
     * Price = Cost × (1 + Profit Margin / 100)
     */
    public function calculatePriceFromCostAndMargin()
    {
        if ($this->cost !== null && $this->profit_margin !== null) {
            return $this->cost * (1 + $this->profit_margin / 100);
        }
        return $this->price;
    }

    /**
     * Check if product has attributes
     */
    public function hasAttributes()
    {
        return $this->attributes()->count() > 0;
    }

    /**
     * Get stock quantity for a specific attribute value
     * @param int $attributeId
     * @param int $attributeValueId
     * @return int
     */
    public function getAttributeStock($attributeId, $attributeValueId)
    {
        $pivot = \DB::table('product_attributes')
            ->where('product_id', $this->id)
            ->where('attribute_id', $attributeId)
            ->where('attribute_value_id', $attributeValueId)
            ->first();
        
        return $pivot ? ($pivot->stock_quantity ?? 0) : 0;
    }

    /**
     * Get stock quantity for multiple attribute values (combination)
     * @param array $attributes [attribute_id => attribute_value_id]
     * @return int|null Returns null if product has no attributes, otherwise returns the minimum stock
     */
    public function getAttributesStock($attributes = [])
    {
        // If product has no attributes, return product stock
        if (!$this->hasAttributes()) {
            return $this->stock_quantity;
        }

        // If attributes array is empty, return null (attributes must be selected)
        if (empty($attributes)) {
            return null;
        }

        // Get minimum stock from all selected attribute values
        $stocks = [];
        foreach ($attributes as $attributeId => $attributeValueId) {
            $stock = $this->getAttributeStock($attributeId, $attributeValueId);
            $stocks[] = $stock;
        }

        // Return minimum stock (the limiting factor)
        return !empty($stocks) ? min($stocks) : 0;
    }

    /**
     * Deduct stock from attribute value
     * @param int $attributeId
     * @param int $attributeValueId
     * @param int $quantity
     * @return bool
     */
    public function deductAttributeStock($attributeId, $attributeValueId, $quantity)
    {
        $affected = \DB::table('product_attributes')
            ->where('product_id', $this->id)
            ->where('attribute_id', $attributeId)
            ->where('attribute_value_id', $attributeValueId)
            ->where('stock_quantity', '>=', $quantity)
            ->decrement('stock_quantity', $quantity);
        
        return $affected > 0;
    }

    /**
     * Get total stock quantity for the product
     * If product has attributes, returns sum of all attribute stock quantities
     * Otherwise, returns product-level stock_quantity
     * @return int
     */
    public function getTotalStockAttribute()
    {
        // If product has no attributes, return product-level stock
        if (!$this->hasAttributes()) {
            return $this->stock_quantity ?? 0;
        }

        // If product has attributes, sum all attribute stock quantities
        $totalStock = \DB::table('product_attributes')
            ->where('product_id', $this->id)
            ->sum('stock_quantity');
        
        return (int) $totalStock;
    }

    /**
     * Boot method to auto-calculate price when cost or profit_margin changes
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($product) {
            // Calculate price from cost and profit margin if both are set
            if ($product->cost !== null && $product->profit_margin !== null && $product->isDirty(['cost', 'profit_margin'])) {
                $product->price = $product->cost * (1 + $product->profit_margin / 100);
            }
        });
    }
}
