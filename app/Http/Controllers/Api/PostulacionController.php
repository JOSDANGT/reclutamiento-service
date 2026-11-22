<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostulacionRequest;
use App\Models\Candidato;
use App\Models\Postulacion;
use App\Models\Vacante;
use App\Services\PostulacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostulacionController extends Controller
{
    public function __construct(
        private readonly PostulacionService $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'vacante_id' => ['nullable', 'integer'],
            'estado_id' => ['nullable', 'integer'],
            'candidato_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $postulaciones = $this->service->listar($filtros);

        return response()->json([
            'data' => $postulaciones,
        ]);
    }


    public function verificarPostulacion(Request $request, Vacante $vacante): JsonResponse {
        $usuarioId = (int) $request->attributes->get('identity_user_id');

        if (!$usuarioId) {
            return response()->json([
                'message' => 'Usuario no autenticado.',
            ], 401);
        }

        $candidato = Candidato::query()
            ->where('user_id', $usuarioId)
            ->first();

        if (!$candidato) {
            return response()->json([
                'data' => [
                    'postulado' => false,
                    'postulacion' => null,
                ],
            ]);
        }

        $postulacion = Postulacion::query()
            ->with('estado')
            ->where('candidato_id', $candidato->id)
            ->where('vacante_id', $vacante->id)
            ->first();

        return response()->json([
            'data' => [
                'postulado' => $postulacion !== null,
                'postulacion' => $postulacion ? [
                    'id' => $postulacion->id,
                    'fecha_postulacion' => $postulacion->fecha_postulacion,
                    'estado' => $postulacion->estado?->nombre,
                ] : null,
            ],
        ]);
    }


    public function show(Postulacion $postulacion): JsonResponse
    {
        return response()->json([
            'data' => $this->service->obtener($postulacion),
        ]);
    }


    public function store(StorePostulacionRequest $request): JsonResponse
    {
        $usuarioId = $request->attributes->get('identity_user_id');

        if (!$usuarioId) {
            return response()->json([
                'message' => 'No se pudo identificar al usuario autenticado.',
            ], 401);
        }

        $postulacion = $this->service->crear(
            $request->validated(),
            (int) $usuarioId
        );

        return response()->json([
            'message' => 'Postulación registrada correctamente.',
            'data' => $postulacion,
        ], 201);
    }

}
