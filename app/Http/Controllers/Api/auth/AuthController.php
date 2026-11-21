<?php

namespace App\Http\Controllers\Api\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'primer_nombre' => ['required', 'string', 'max:100'],
            'segundo_nombre' => ['nullable', 'string', 'max:100'],
            'tercer_nombre' => ['nullable', 'string', 'max:100'],
            'primer_apellido' => ['required', 'string', 'max:100'],
            'segundo_apellido' => ['nullable', 'string', 'max:100'],
            'apellido_casada' => ['nullable', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique(User::class, 'email'),
            ],
            'telefono' => ['nullable', 'string', 'max:25'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ]);


        try {
            DB::beginTransaction();

            $data['estado'] = true;

            $user = User::create($data);

            $user->assignRole('CANDIDATO');

            DB::commit();


            $token = $user->createToken(
                'talenthub',
                ['*'],
                now()->addHours(8)
            )->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado correctamente.',
                'data' => [
                    'user' => $user,
                    'roles' => $user->getRoleNames(),
                    'permisos' => $user->getAllPermissions()->pluck('name'),
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el usuario.',
                'data' => null,
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {

        $data = $request->validate([
            'usuario_dominio' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where(function ($query) use ($data) {
                $query
                    ->where('usuario_dominio', $data['usuario_dominio'])
                    ->orWhere('email', $data['usuario_dominio']);
            })
            ->where('estado', true)
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'usuario_dominio' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $token = $user->createToken(
            'talenthub',
            ['*'],
            now()->addHours(8)
        )->plainTextToken;

        $user->forceFill([
            'ultimo_acceso_at' => now(),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Sesión iniciada correctamente.',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 28800,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->estado || $user->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autorizado.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'primer_nombre' => $user->primer_nombre,
                    'segundo_nombre' => $user->segundo_nombre,
                    'primer_apellido' => $user->primer_apellido,
                    'segundo_apellido' => $user->segundo_apellido,
                    'email' => $user->email,
                ],
                'roles' => $user->getRoleNames(),
                'permisos' => $user->getAllPermissions()->pluck('name'),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }
}
