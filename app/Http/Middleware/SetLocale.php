<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Language;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($locale = session('locale')) {
            app()->setLocale($locale);
        } else {
            // Get default language from database
            $defaultLanguage = Language::where('is_default', 1)
                ->where('status', 'enabled')
                ->first();
            
            if ($defaultLanguage) {
                $locale = $defaultLanguage->code;
                session(['locale' => $locale]);
            } else {
                $locale = config('app.locale');
            }
            
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
