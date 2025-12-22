<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Staff extends User
{
    protected $table = 'users';

    /**
     * Default attributes for staff members.
     */
    protected $attributes = [
        'user_type' => 'admin',
    ];

    /**
     * Guard name used by spatie/permission.
     */
    protected string $guard_name = 'web';

    /**
     * Limit queries to admin users by default.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('staff', function (Builder $builder) {
            $builder->where('user_type', 'admin');
        });
    }
}
