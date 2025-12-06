<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'website',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    /**
     * Include the computed logo URL when serializing the model to arrays / JSON.
     */
    protected $appends = ['logo_url'];

    /**
     * Get the products for the brand.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Accessor: get full URL for the stored logo path.
     * Returns default logo if no logo is set. If the logo already looks like a URL
     * or an absolute path, it is returned as-is.
     */
    public function getLogoUrlAttribute(): ?string
    {
        $logo = $this->attributes['logo'] ?? null;
        if (!$logo) {
            return '/front/theme1/images/no-logo.png';
        }

        // If already a full URL, return as is
        if (Str::startsWith($logo, ['http://', 'https://'])) {
            return $logo;
        }

        // If already starts with /, return as is (supports both /uploads/ and /storage/)
        if (Str::startsWith($logo, '/')) {
            return $logo;
        }

        // For old paths without /, assume they're in public/uploads/brands
        return '/uploads/brands/' . ltrim($logo, '/');
    }
}
