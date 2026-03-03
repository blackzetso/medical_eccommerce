<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SyncApiAuth
{
    /**
     * Allow request if authenticated via Sanctum (Bearer token) OR valid API-KEY (OrgaSoft key).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Option 1: Valid API-KEY (same as OrgaSoft desktop integration)
        $expectedKey = Setting::get('orgasoft_api_key', 'AHMED_ADEL');
        $receivedKey = $request->header('API-KEY');
        if ($receivedKey && hash_equals($expectedKey, $receivedKey)) {
            return $next($request);
        }

        // Option 2: Sanctum Bearer token (e.g. from POST /api/integration/token)
        if ($request->bearerToken() && Auth::guard('sanctum')->user()) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Unauthorized. Provide valid Authorization Bearer token or API-KEY header.',
        ], 401);
    }
}
