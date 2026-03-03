<?php

namespace App\Providers;

use Inertia\Inertia;
use App\Models\Language;
use App\Models\LanguagePhrase;
use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Order::observe(OrderObserver::class);

        // Customize route model binding for Role
        // Try 'web' guard first, then fallback to any guard
        Route::bind('role', function ($value) {
            $role = Role::where('id', $value)
                ->where('guard_name', 'web')
                ->first();
            
            // If not found with 'web' guard, try to find with any guard
            if (!$role) {
                \Log::info("Role with ID {$value} not found with 'web' guard, trying any guard");
                $role = Role::where('id', $value)->first();
                
                if ($role) {
                    \Log::info("Found role with ID {$value} using guard '{$role->guard_name}'");
                }
            }
            
            if (!$role) {
                \Log::warning("Role with ID {$value} not found in database");
                abort(404, 'Role not found');
            }
            
            return $role;
        });
        Inertia::share([
            'auth' => function () {
                return [
                    'user' => Auth::user(),
                ];
            },
            'canLogin' => fn () => Route::has('login'),
            'canRegister' => fn () => Route::has('register'),
            'laravelVersion' => fn () => Application::VERSION,
            'phpVersion' => fn () => PHP_VERSION,

            'cartItems' => function () {
                $user = Auth::user();
                if ($user) {
                    return \App\Models\Cart::with('product')->where('user_id', $user->id)->get();
                }
                return [];
            },

            'translations' => function () {
                $locale = app()->getLocale();

                // Try to get translations for current locale
                $translations = \App\Models\LanguagePhrase::query()
                    ->join('languages', 'languages.id', '=', 'language_phrases.language_id')
                    ->where('languages.code', $locale)
                    ->where('languages.status', 'enabled')
                    ->where('language_phrases.group', 'general')
                    ->pluck('word', 'key')
                    ->toArray();

                // If no translations found for current locale, use default language
                if (empty($translations)) {
                    $defaultLanguage = Language::where('is_default', 1)
                        ->where('status', 'enabled')
                        ->first();
                    
                    if ($defaultLanguage) {
                        $translations = \App\Models\LanguagePhrase::query()
                            ->where('language_id', $defaultLanguage->id)
                            ->where('group', 'general')
                            ->pluck('word', 'key')
                            ->toArray();
                    }
                }

                return $translations;
            },

            'languages' => function () {
                $languages = Language::where('status', 'enabled')
                    ->select('id', 'name', 'code', 'is_default', 'is_rtl')
                    ->orderBy('is_default', 'desc')
                    ->orderBy('name', 'asc')
                    ->get();
                
                // Return as array with proper mapping
                return $languages->map(function ($lang) {
                    return [
                        'id' => $lang->id,
                        'name' => $lang->name,
                        'code' => $lang->code,
                        'is_default' => (bool) $lang->is_default,
                        'is_rtl' => (bool) $lang->is_rtl,
                    ];
                })->values()->toArray();
            },

            'locale' => fn () => app()->getLocale(),

            'is_rtl' => function () {
                $locale = app()->getLocale();
                $currentLanguage = Language::where('code', $locale)
                    ->where('status', 'enabled')
                    ->first();
                
                return $currentLanguage ? (bool) $currentLanguage->is_rtl : false;
            },
        ]);
    }
}
