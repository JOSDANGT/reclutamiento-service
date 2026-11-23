<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExperienciaLaboral\StoreExperienciaLaboralRequest;
use App\Http\Requests\ExperienciaLaboral\UpdateExperienciaLaboralRequest;
use App\Services\ExperienciaLaboralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExperienciaLaboralController extends Controller
{
    public function __construct(
        private readonly ExperienciaLaboralService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $usuarioId = auth()->id();

        return response()->json([
            'success' => true,
            'data' => $this->service->listar($usuarioId),
        ]);
    }

    public function show(Request $request, int $experiencia): JsonResponse {
        $usuarioId = auth()->id();

        return response()->json([
            'success' => true,
            'data' => $this->service->obtener(
                $usuarioId,
                $experiencia
            ),
        ]);
    }

    public function store(
        StoreExperienciaLaboralRequest $request
    ): JsonResponse {
        $usuarioId = auth()->id();

        $experiencia = $this->service->crear(
            $usuarioId,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Experiencia laboral registrada correctamente.',
            'data' => $experiencia,
        ], 201);
    }

    public function update(UpdateExperienciaLaboralRequest $request, int $experiencia): JsonResponse {
        $usuarioId = auth()->id();

        $resultado = $this->service->actualizar(
            $usuarioId,
            $experiencia,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Experiencia laboral actualizada correctamente.',
            'data' => $resultado,
        ]);
    }

    public function destroy(
        Request $request,
        int $experiencia
    ): JsonResponse {
        $usuarioId = auth()->id();

        $this->service->eliminar(
            $usuarioId,
            $experiencia
        );

        return response()->json([
            'success' => true,
            'message' => 'Experiencia laboral eliminada correctamente.',
        ]);
    }
}
