<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Puesto;
use Illuminate\Http\JsonResponse;

class PuestoController extends Controller
{
    public function index(): JsonResponse
    {
        $puestos = Puesto::query()
            ->with('unidad')
            ->where('estado', true)
            ->whereHas('unidad', function ($query) {
                $query->where('estado', true);
            })
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'data' => $puestos,
        ]);
    }

    public function requisitos(Puesto $puesto): JsonResponse
    {
        $requisitos = $puesto->requisitos()
            ->with('tipoRequisito')
            ->where('estado', true)
            ->orderByPivot('orden')
            ->get();

        return response()->json([
            'data' => $requisitos,
        ]);
    }
}
