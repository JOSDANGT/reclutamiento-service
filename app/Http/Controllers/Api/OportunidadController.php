<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\Vacante;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OportunidadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $usuario = Auth::user();

        $candidato = Candidato::query()
            ->where('user_id', $usuario->id)
            ->first();

        $postulacionesUsuario = $candidato
            ? $candidato->postulaciones()->pluck('vacante_id')->toArray()
            : [];

        $query = Vacante::query()
            ->select([
                'id',
                'titulo',
                'puesto_id',
                'descripcion',
                'cantidad_plazas',
                'modalidad',
                'tipo_contrato',
                'nivel_experiencia',
                'fecha_inicio',
                'fecha_limite',
                'created_at',
            ])
            ->with([
                'puesto:id,unidad_id,nombre',
                'puesto.unidad:id,nombre',
            ])
            ->where('estado', 'PUBLICADA');

        if (!empty($filtros['search'])) {
            $search = $filtros['search'];

            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        $vacantes = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($filtros['per_page'] ?? 9);

        $vacantes->getCollection()->transform(function ($vacante) use ($postulacionesUsuario) {
            $vacante->postulado = in_array($vacante->id, $postulacionesUsuario);

            return $vacante;
        });

        return response()->json([
            'data' => $vacantes,
        ]);
    }

    public function show(Vacante $vacante): JsonResponse
    {

        $vacante->load([
            'puesto.unidad',
            'requisitos.tipoRequisito',
        ]);

        $requisitos = $vacante->requisitos
            ->sortBy(fn ($requisito) => (int) $requisito->pivot->orden)
            ->values()
            ->map(fn ($requisito) => [
                'id' => $requisito->id,
                'nombre' => $requisito->nombre,
                'descripcion' => $requisito->descripcion,
                'tipo_requisito' => $requisito->tipoRequisito
                    ? [
                        'id' => $requisito->tipoRequisito->id,
                        'nombre' => $requisito->tipoRequisito->nombre,
                    ]
                    : null,
                'pivot' => [
                    'obligatorio' => (bool) $requisito->pivot->obligatorio,
                    'orden' => (int) $requisito->pivot->orden,
                ],
            ]);

        return response()->json([
            'data' => [
                'id' => $vacante->id,
                'titulo' => $vacante->titulo,
                'descripcion' => $vacante->descripcion,
                'cantidad_plazas' => $vacante->cantidad_plazas,
                'modalidad' => $vacante->modalidad,
                'tipo_contrato' => $vacante->tipo_contrato,
                'nivel_experiencia' => $vacante->nivel_experiencia,
                'salario_minimo' => $vacante->salario_minimo,
                'salario_maximo' => $vacante->salario_maximo,
                'fecha_inicio' => $vacante->fecha_inicio,
                'fecha_limite' => $vacante->fecha_limite,
                'estado' => $vacante->estado,
                'puesto' => $vacante->puesto
                    ? [
                        'id' => $vacante->puesto->id,
                        'nombre' => $vacante->puesto->nombre,
                        'unidad' => $vacante->puesto->unidad
                            ? [
                                'id' => $vacante->puesto->unidad->id,
                                'nombre' => $vacante->puesto->unidad->nombre,
                            ]
                            : null,
                    ]
                    : null,
                'requisitos' => $requisitos,
            ],
        ]);
    }
}
