<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class SetInertiaRootView
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the request is for admin section
        if ($request->is('admin') || $request->is('admin/*')) {
            Inertia::setRootView('admin');
        } else {
            Inertia::setRootView('app');
        }

        return $next($request);
    }
}
