<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTipoRequisitoRequest;
use App\Http\Requests\UpdateTipoRequisitoRequest;
use App\Models\TipoRequisito;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TipoRequisitoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'search' => ['sometimes', 'string', 'max:150'],
            'estado' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = TipoRequisito::query()
            ->withCount('requisitos');

        if (!empty($filtros['search'])) {
            $query->where(
                'nombre',
                'like',
                '%' . $filtros['search'] . '%'
            );
        }

        if (array_key_exists('estado', $filtros)) {
            $query->where('estado', $filtros['estado']);
        }

        $tipos = $query
            ->orderBy('nombre')
            ->paginate($filtros['per_page'] ?? 15);

        return response()->json([
            'success' => true,
            'data' => $tipos,
        ]);
    }

    public function store(StoreTipoRequisitoRequest $request): JsonResponse {
        $data = $request->validated();
        $data['estado'] = $data['estado'] ?? true;

        $tipo = TipoRequisito::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Tipo de requisito registrado.',
            'data' => $tipo,
        ], 201);
    }

    public function show(TipoRequisito $tipoRequisito): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $tipoRequisito->load('requisitos'),
        ]);
    }

    public function update(UpdateTipoRequisitoRequest $request, TipoRequisito $tipoRequisito): JsonResponse {
        $tipoRequisito->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tipo de requisito actualizado.',
            'data' => $tipoRequisito->fresh(),
        ]);
    }

    public function destroy(TipoRequisito $tipoRequisito): JsonResponse {
        if ($tipoRequisito->requisitos()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un tipo que tiene requisitos asociados.',
            ], 409);
        }

        $tipoRequisito->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de requisito eliminado.',
        ]);
    }
}
