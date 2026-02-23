<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrgaSoftApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = Setting::get('orgasoft_api_key', 'AHMED_ADEL');
        $receivedKey = $request->header('API-KEY');

        if (!$receivedKey || !hash_equals($expectedKey, $receivedKey)) {
            return response()->json([
                'message' => 'Unauthorized: Invalid API-KEY',
            ], 401);
        }

        return $next($request);
    }
}
