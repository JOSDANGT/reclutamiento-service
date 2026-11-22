<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Postulacion;
use App\Models\PostulacionRequisito;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PostulacionRequisitoController extends Controller
{
    public function index(Postulacion $postulacion): JsonResponse
    {
        $requisitos = DB::connection('sqlsrv')
            ->table('dbreclutamiento.vacantes_has_requisitos as vr')
            ->join(
                'dbreclutamiento.requisitos as r',
                'r.id',
                '=',
                'vr.requisito_id'
            )
            ->leftJoin(
                'dbreclutamiento.postulacion_requisitos as pr',
                function ($join) use ($postulacion) {
                    $join->on('pr.requisito_id', '=', 'r.id')
                        ->where('pr.postulacion_id', $postulacion->id);
                }
            )
            ->where('vr.vacante_id', $postulacion->vacante_id)
            ->orderBy('vr.orden')
            ->select([
                'r.id',
                'r.nombre',
                'r.descripcion',
                'vr.obligatorio',
                'vr.orden',
                'pr.cumple',
                'pr.observacion',
                'pr.evaluado_por',
                'pr.evaluado_at',
            ])
            ->get();

        $obligatorios = $requisitos->where('obligatorio', true);

        $incumplidos = $obligatorios->filter(
            fn ($r) => $r->cumple !== null && !(bool) $r->cumple
        )->count();

        $pendientes = $obligatorios->filter(
            fn ($r) => $r->cumple === null
        )->count();

        $resultado = $incumplidos > 0
            ? 'NO_CUMPLE'
            : ($pendientes > 0 ? 'PENDIENTE' : 'CUMPLE');

        return response()->json([
            'success' => true,
            'data' => [
                'requisitos' => $requisitos,
                'resumen' => [
                    'total' => $requisitos->count(),
                    'obligatorios' => $obligatorios->count(),
                    'incumplidos' => $incumplidos,
                    'pendientes' => $pendientes,
                    'resultado' => $resultado,
                ],
            ],
        ]);
    }

    public function guardar(Request $request, Postulacion $postulacion): JsonResponse {
        $data = $request->validate([
            'requisitos' => ['required', 'array', 'min:1'],
            'requisitos.*.id' => ['required', 'integer', 'distinct'],
            'requisitos.*.cumple' => ['present', 'nullable', 'boolean'],
            'requisitos.*.observacion' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $usuarioId = $request->attributes->get('identity_user_id');

        DB::connection('sqlsrv')->transaction(
            function () use ($postulacion, $data, $usuarioId) {
                foreach ($data['requisitos'] as $item) {
                    $existe = DB::connection('sqlsrv')
                        ->table('dbreclutamiento.vacantes_has_requisitos')
                        ->where('vacante_id', $postulacion->vacante_id)
                        ->where('requisito_id', $item['id'])
                        ->exists();

                    if (!$existe) {
                        throw ValidationException::withMessages([
                            'requisitos' => 'El requisito '
                                . $item['id']
                                . ' no pertenece a la vacante.',
                        ]);
                    }

                    $evaluacion = PostulacionRequisito::query()
                        ->where('postulacion_id', $postulacion->id)
                        ->where('requisito_id', $item['id'])
                        ->lockForUpdate()
                        ->first();

                    $anteriores = $evaluacion
                        ? [
                            'cumple' => $evaluacion->cumple,
                            'observacion' => $evaluacion->observacion,
                        ]
                        : [
                            'cumple' => null,
                            'observacion' => null,
                        ];

                    $nuevos = [
                        'cumple' => $item['cumple'],
                        'observacion' => $item['observacion'] ?? null,
                    ];

                    if ($anteriores == $nuevos) {
                        continue;
                    }

                    if (!$evaluacion) {
                        $evaluacion = new PostulacionRequisito();
                        $evaluacion->postulacion_id = $postulacion->id;
                        $evaluacion->requisito_id = $item['id'];
                    }

                    $evaluacion->cumple = $item['cumple'];
                    $evaluacion->observacion = $item['observacion'] ?? null;
                    $evaluacion->evaluado_por = $usuarioId;
                    $evaluacion->evaluado_at = now();
                    $evaluacion->save();

                    // Registrar el movimiento sobre la postulación.
                    $bitacora = new Bitacora();

                    $bitacora->forceFill([
                        'model_type' => $postulacion->getMorphClass(),
                        'model_id' => $postulacion->getKey(),
                        'accion' => 'EVALUACION_REQUISITO',
                        'descripcion' => 'Se evaluó un requisito de la postulación.',
                        'datos_anteriores' => [
                            'requisito_id' => $item['id'],
                            ...$anteriores,
                        ],
                        'datos_nuevos' => [
                            'requisito_id' => $item['id'],
                            ...$nuevos,
                        ],
                        'usuario_id' => $usuarioId,
                    ]);

                    $bitacora->save();
                }
            }
        );

        return $this->index($postulacion);
    }
}
