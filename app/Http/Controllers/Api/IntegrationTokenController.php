<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationTokenController extends Controller
{
    /**
     * Generate a new Sanctum token for the authenticated user.
     * Rotates existing tokens with the same name to keep a single active token.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();

        // Optional: enforce role/permission check here if needed.
        // abort_unless($user->hasRole('admin'), 403);

        $tokenName = $request->input('name', 'erp-sync');

        // Rotate previous token with same name.
        $user->tokens()->where('name', $tokenName)->delete();

        $token = $user->createToken($tokenName);

        return response()->json([
            'token' => $token->plainTextToken,
            'type' => 'Bearer',
            'name' => $tokenName,
        ], 201);
    }
}
