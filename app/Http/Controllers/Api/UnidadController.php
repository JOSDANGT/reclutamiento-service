<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unidad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnidadController extends Controller
{
    public function index(): JsonResponse
    {
        $unidades = Unidad::query()
            ->orderBy('nombre')
            ->get();


        return response()->json([
            'success' => true,
            'data' => $unidades,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('dbreclutamiento.unidades', 'nombre'),
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'estado' => ['sometimes', 'boolean'],
        ]);

        $unidad = Unidad::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Unidad registrada correctamente.',
            'data' => $unidad,
        ], 201);
    }

    public function show(Unidad $unidad): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $unidad,
        ]);
    }

    public function update(Request $request, Unidad $unidad): JsonResponse
    {
        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('dbreclutamiento.unidades', 'nombre')
                    ->ignore($unidad->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        $unidad->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Unidad actualizada correctamente.',
            'data' => $unidad,
        ]);
    }

    public function cambiarEstado(Unidad $unidad): JsonResponse
    {
        $unidad->update([
            'estado' => !$unidad->estado,
        ]);

        return response()->json([
            'success' => true,
            'message' => $unidad->estado
                ? 'Unidad activada correctamente.'
                : 'Unidad desactivada correctamente.',
            'data' => $unidad,
        ]);
    }
}
