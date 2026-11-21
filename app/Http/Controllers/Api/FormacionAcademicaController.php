<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FormacionAcademica\StoreFormacionAcademicaRequest;
use App\Http\Requests\FormacionAcademica\UpdateFormacionAcademicaRequest;
use App\Services\FormacionAcademicaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormacionAcademicaController extends Controller
{
    public function __construct(
        private readonly FormacionAcademicaService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $usuarioId = (int) $request->attributes
            ->get('identity_user_id');

        return response()->json([
            'success' => true,
            'data' => $this->service->listar($usuarioId),
        ]);
    }

    public function show(
        Request $request,
        int $formacion
    ): JsonResponse {
        $usuarioId = (int) $request->attributes
            ->get('identity_user_id');

        return response()->json([
            'success' => true,
            'data' => $this->service->obtener(
                $usuarioId,
                $formacion
            ),
        ]);
    }

    public function store(
        StoreFormacionAcademicaRequest $request
    ): JsonResponse {
        $usuarioId = (int) $request->attributes
            ->get('identity_user_id');

        $formacion = $this->service->crear(
            $usuarioId,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Formación académica registrada correctamente.',
            'data' => $formacion,
        ], 201);
    }

    public function update(
        UpdateFormacionAcademicaRequest $request,
        int $formacion
    ): JsonResponse {
        $usuarioId = (int) $request->attributes
            ->get('identity_user_id');

        $resultado = $this->service->actualizar(
            $usuarioId,
            $formacion,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Formación académica actualizada correctamente.',
            'data' => $resultado,
        ]);
    }

    public function destroy(
        Request $request,
        int $formacion
    ): JsonResponse {
        $usuarioId = (int) $request->attributes
            ->get('identity_user_id');

        $this->service->eliminar($usuarioId, $formacion);

        return response()->json([
            'success' => true,
            'message' => 'Formación académica eliminada correctamente.',
        ]);
    }
}
