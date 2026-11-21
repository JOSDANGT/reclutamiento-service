<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidarTokenIdentity
{
    public function handle(Request $request, Closure $next, string $permiso): Response {

        // 1. Obtener el usuario autenticado por Sanctum
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autenticación inválido o expirado.',
            ], 401);
        }

        // 2. Validar que el usuario esté activo
        if (!$user->estado || $user->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario se encuentra inactivo.',
            ], 403);
        }

        // 3. Verificar permisos con Spatie
        if (!$user->can($permiso)) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para esta operación.',
            ], 403);
        }

        // 4. Guardar identidad para compatibilidad
        $request->attributes->set(
            'identity_user_id',
            $user->id
        );

        return $next($request);
    }
}
