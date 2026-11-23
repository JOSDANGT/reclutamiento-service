<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequisitoRequest;
use App\Http\Requests\UpdateRequisitoRequest;
use App\Models\Requisito;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequisitoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'search' => ['sometimes', 'string', 'max:150'],
            'tipo_requisito_id' => ['sometimes', 'integer'],
            'estado' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Requisito::query()
            ->with('tipoRequisito');

        if (!empty($filtros['search'])) {
            $query->where(
                'nombre',
                'like',
                '%' . $filtros['search'] . '%'
            );
        }

        if (isset($filtros['tipo_requisito_id'])) {
            $query->where(
                'tipo_requisito_id',
                $filtros['tipo_requisito_id']
            );
        }

        if (array_key_exists('estado', $filtros)) {
            $query->where('estado', $filtros['estado']);
        }

        $requisitos = $query
            ->orderBy('nombre')
            ->paginate($filtros['per_page'] ?? 15);

        return response()->json([
            'success' => true,
            'data' => $requisitos,
        ]);
    }

    public function store(StoreRequisitoRequest $request): JsonResponse {
        $data = $request->validated();
        $data['estado'] = $data['estado'] ?? true;

        $requisito = Requisito::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Requisito registrado.',
            'data' => $requisito->load('tipoRequisito'),
        ], 201);
    }

    public function show(Requisito $requisito): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $requisito->load('tipoRequisito'),
        ]);
    }

    public function update(UpdateRequisitoRequest $request, Requisito $requisito): JsonResponse {
        $requisito->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Requisito actualizado.',
            'data' => $requisito->fresh()
                ->load('tipoRequisito'),
        ]);
    }

    public function destroy(Requisito $requisito): JsonResponse
    {
        if ($requisito->vacantes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un requisito asociado a vacantes.',
            ], 409);
        }

        $requisito->delete();

        return response()->json([
            'success' => true,
            'message' => 'Requisito eliminado.',
        ]);
    }
}
