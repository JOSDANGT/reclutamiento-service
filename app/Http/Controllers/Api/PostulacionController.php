<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostulacionRequest;
use App\Models\Postulacion;
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
