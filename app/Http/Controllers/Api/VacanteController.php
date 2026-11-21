<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVacanteRequest;
use App\Http\Requests\UpdateVacanteRequest;
use App\Models\Vacante;
use App\Services\VacanteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VacanteController extends Controller
{
    public function __construct(private readonly VacanteService $vacanteService) {}

    public function index(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'search' => ['sometimes', 'string', 'max:150'],
            'estado' => ['sometimes', 'string', 'max:50'],
            'modalidad' => ['sometimes', 'string', 'max:50'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $vacantes = $this->vacanteService->listar($filtros);

        return response()->json([
            'success' => true,
            'message' => 'Vacantes obtenidas correctamente.',
            'data' => $vacantes,
        ]);
    }

    public function store(StoreVacanteRequest $request): JsonResponse {
        $vacante = $this->vacanteService->crear(
            $request->validated(),
            $request->attributes->get('identity_user_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Vacante registrada correctamente.',
            'data' => $vacante,
        ], 201);
    }

    public function show(Vacante $vacante): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Vacante obtenida correctamente.',
            'data' => $this->vacanteService->obtener($vacante),
        ]);
    }

    public function update(UpdateVacanteRequest $request, Vacante $vacante): JsonResponse {
        $vacante = $this->vacanteService->actualizar(
            $vacante,
            $request->validated(),
            $request->attributes->get('identity_user_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Vacante actualizada correctamente.',
            'data' => $vacante,
        ]);
    }

    public function cambiarEstado(Request $request, Vacante $vacante): JsonResponse {
        $data = $request->validate([
            'estado' => [
                'required',
                Rule::in(['BORRADOR', 'PUBLICADA', 'CERRADA']),
            ],
        ]);

        $vacante = $this->vacanteService->cambiarEstado(
            $vacante,
            $data['estado'],
            $request->attributes->get('identity_user_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente.',
            'data' => $vacante,
        ]);
    }

    public function destroy(Request $request, Vacante $vacante): JsonResponse
    {
        $this->vacanteService->eliminar(
            $vacante,
            $request->attributes->get('identity_user_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Vacante eliminada correctamente.',
        ]);
    }
}
