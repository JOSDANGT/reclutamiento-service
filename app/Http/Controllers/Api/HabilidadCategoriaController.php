<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HabilidadCategoria\StoreHabilidadCategoriaRequest;
use App\Http\Requests\HabilidadCategoria\UpdateHabilidadCategoriaRequest;
use App\Models\HabilidadCategoria;
use App\Services\HabilidadCategoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HabilidadCategoriaController extends Controller
{
    public function __construct(
        private readonly HabilidadCategoriaService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'estado' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->service->listar($filtros),
        ]);
    }

    public function store(StoreHabilidadCategoriaRequest $request): JsonResponse {
        $categoria = $this->service->crear($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada correctamente.',
            'data' => $categoria,
        ], 201);
    }

    public function update(
        UpdateHabilidadCategoriaRequest $request,
        HabilidadCategoria $categoria
    ): JsonResponse {
        $categoria = $this->service->actualizar(
            $categoria,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada correctamente.',
            'data' => $categoria,
        ]);
    }
}
