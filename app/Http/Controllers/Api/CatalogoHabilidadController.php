<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habilidad;
use App\Models\NivelHabilidad;
use Illuminate\Http\JsonResponse;

class CatalogoHabilidadController extends Controller
{
    public function habilidades(): JsonResponse
    {
        $habilidades = Habilidad::query()
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $habilidades,
        ]);
    }

    public function niveles(): JsonResponse
    {
        $niveles = NivelHabilidad::query()
            ->select('id', 'nombre')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $niveles,
        ]);
    }
}
