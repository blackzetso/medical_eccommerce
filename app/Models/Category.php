<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = ['name','parent_id', 'status', 'image'];

    // Note: 'status' is stored as enum('enable','disable') in DB,
    // so we should NOT cast it to boolean. Keep it as string.

    /**
     * Include the computed image URL when serializing the model to arrays / JSON.
     */
    protected $appends = ['image_url'];

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('children');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the products for the category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Accessor: get full URL for the stored image path on the public disk.
     * Returns null if no image is set. If the image already looks like a URL
     * or an absolute path, it is returned as-is.
     */
    public function getImageUrlAttribute(): ?string
    {
        $image = $this->attributes['image'] ?? null;
        if (!$image) {
            return null;
        }

        // If already a full URL, return as is
        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        // If already starts with /, return as is (supports both /uploads/ and /storage/)
        if (Str::startsWith($image, '/')) {
            return $image;
        }

        // For old paths without /, assume they're in public/uploads
        return '/uploads/categories/' . ltrim($image, '/');
    }
}
