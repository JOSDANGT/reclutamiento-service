<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Habilidad\AsociarHabilidadRequest;
use App\Http\Requests\Habilidad\UpdateCandidatoHabilidadRequest;
use App\Services\CandidatoHabilidadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CandidatoHabilidadController extends Controller
{
    public function __construct(
        private readonly CandidatoHabilidadService $service
    ) {}

    /**
     * Consultar las habilidades del candidato autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $usuarioId = (int) $request->attributes->get('identity_user_id');

        $habilidades = $this->service->listar($usuarioId);

        return response()->json([
            'success' => true,
            'data' => $habilidades,
        ]);
    }

    /**
     * Asociar una habilidad al perfil del candidato.
     */
    public function store(AsociarHabilidadRequest $request): JsonResponse {
        $usuarioId = (int) $request->attributes->get(
            'identity_user_id'
        );

        $habilidades = $this->service->asociar(
            $usuarioId,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Habilidad asociada correctamente.',
            'data' => $habilidades,
        ], 201);
    }

    /**
     * Actualizar el nivel y los años de experiencia.
     */
    public function update(
        UpdateCandidatoHabilidadRequest $request,
        int $habilidad
    ): JsonResponse {
        $usuarioId = (int) $request->attributes->get(
            'identity_user_id'
        );

        $habilidades = $this->service->actualizar(
            $usuarioId,
            $habilidad,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Habilidad actualizada correctamente.',
            'data' => $habilidades,
        ]);
    }

    /**
     * Desasociar una habilidad del perfil del candidato.
     */
    public function destroy(
        Request $request,
        int $habilidad
    ): JsonResponse {
        $usuarioId = (int) $request->attributes->get(
            'identity_user_id'
        );

        $this->service->eliminar(
            $usuarioId,
            $habilidad
        );

        return response()->json([
            'success' => true,
            'message' => 'Habilidad eliminada del perfil correctamente.',
        ]);
    }
}
