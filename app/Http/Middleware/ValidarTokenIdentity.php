<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Symfony\Component\HttpFoundation\Response;

class ValidarTokenIdentity
{
    public function handle(Request $request, Closure $next, string $permiso): Response {

        // 1. Obtener el token del usuario
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autenticación requerido.',
            ], 401);
        }

        // 2. Consultar la configuración interna
        $url = config('services.identity.url');
        $serviceKey = config('services.identity.key');

        if (!is_string($url) || $url === '' || !is_string($serviceKey) || $serviceKey === '') {
            return response()->json([
                'success' => false,
                'message' => 'Servicio de autenticación no configurado.',
            ], 503);
        }

        // 3. Consultar Identity Service
        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'X-Service-Key' => $serviceKey,
                ])
                ->connectTimeout(2)
                ->timeout(5)
                ->post(
                    rtrim($url, '/') . '/api/internal/authorize',
                    [
                        'token' => $token,
                        'permission' => $permiso,
                    ]
                );

        } catch (ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio de autenticación no disponible.',
            ], 503);
        }

        // 4. Diferenciar autenticación de autorización
        if ($response->status() === 401) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas o expiradas.',
            ], 401);
        }

        if ($response->status() === 403) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para esta operación.',
            ], 403);
        }

        \Log::info('RESPUESTA IDENTITY SERVICE', [
            'url' => $url,
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'No fue posible validar la autorización.',
            ], 503);
        }

        // 5. Comprobar respuesta de Identity
        $authorized = $response->json('authorized');
        $userId = $response->json('user_id');

        if (
            $authorized !== true ||
            !is_int($userId) ||
            $userId < 1
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Respuesta de autorización inválida.',
            ], 503);
        }

        // 6. Guardar identidad verificada para el controlador
        $request->attributes->set(
            'identity_user_id',
            $userId
        );

        return $next($request);
    }
}
