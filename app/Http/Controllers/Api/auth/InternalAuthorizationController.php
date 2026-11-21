<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternalAuthorizationController extends Controller
{
    public function authorizeRequest(Request $request): JsonResponse
    {
        // 1. Autenticar al microservicio solicitante
        $configuredKey = config('services.internal.key');
        $receivedKey = $request->header('X-Service-Key');

        if (
            !is_string($configuredKey) ||
            $configuredKey === '' ||
            !is_string($receivedKey) ||
            !hash_equals($configuredKey, $receivedKey)
        ) {
            return response()->json([
                'message' => 'Servicio no autorizado.',
            ], 401);
        }

        // 2. Validar entrada
        $data = $request->validate([
            'token' => ['required', 'string'],
            'permission' => ['required', 'string', 'max:150'],
        ]);

        // 3. Buscar el token real de Sanctum
        $accessToken = PersonalAccessToken::findToken(
            $data['token']
        );

        if (!$accessToken) {
            return response()->json([
                'message' => 'Token inválido.',
            ], 401);
        }

        // 4. Comprobar expiración individual y global
        $expirationMinutes = config('sanctum.expiration');

        $expiredByConfig = $expirationMinutes !== null
            && $accessToken->created_at->lte(
                now()->subMinutes((int) $expirationMinutes)
            );

        if (
            $expiredByConfig ||
            ($accessToken->expires_at?->isPast() ?? false)
        ) {
            return response()->json([
                'message' => 'Token expirado.',
            ], 401);
        }

        // 5. Obtener el propietario del token
        $user = $accessToken->tokenable;

        if (
            !$user instanceof User ||
            !$user->estado ||
            $user->trashed()
        ) {
            return response()->json([
                'message' => 'Usuario no autorizado.',
            ], 401);
        }

        // 6. Verificar capacidades del token
        if (!$accessToken->can('*')
            && !$accessToken->can($data['permission'])) {
            return response()->json([
                'message' => 'Token sin capacidad suficiente.',
            ], 403);
        }

        // 7. Verificar autorización con Spatie
        if (!$user->can($data['permission'])) {
            return response()->json([
                'message' => 'Permiso insuficiente.',
            ], 403);
        }

        return response()->json([
            'authorized' => true,
            'user_id' => $user->id,
        ]);
    }
}
