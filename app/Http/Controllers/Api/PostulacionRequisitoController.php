<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Postulacion;
use App\Models\PostulacionEstado;
use App\Models\PostulacionRequisito;
use App\Services\BitacoraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class PostulacionRequisitoController extends Controller
{
    public function index(Postulacion $postulacion): JsonResponse
    {
        $requisitosVacante = $postulacion->vacante
            ->requisitos()
            ->orderByPivot('orden')
            ->get();

        $evaluaciones = PostulacionRequisito::query()
            ->where('postulacion_id', $postulacion->id)
            ->get()
            ->keyBy('requisito_id');

        $requisitos = $requisitosVacante->map(function ($requisito) use ($evaluaciones) {
            $evaluacion = $evaluaciones->get($requisito->id);
            $cumple = $evaluacion?->cumple;

            return [
                'id' => $requisito->id,
                'nombre' => $requisito->nombre,
                'descripcion' => $requisito->descripcion,
                'obligatorio' => (bool) $requisito->pivot->obligatorio,
                'orden' => $requisito->pivot->orden,
                'cumple' => $cumple === null ? null : (bool) $cumple,
                'observacion' => $evaluacion?->observacion,
                'evaluado_por' => $evaluacion?->evaluado_por,
                'evaluado_at' => $evaluacion?->evaluado_at,
            ];
        });

        $obligatorios = $requisitos->whereStrict('obligatorio', true);
        $incumplidos = $obligatorios->whereStrict('cumple', false)->count();
        $pendientes = $obligatorios->whereNull('cumple')->count();

        $resultado = 'CUMPLE';

        if ($incumplidos > 0) {
            $resultado = 'NO_CUMPLE';
        } elseif ($pendientes > 0) {
            $resultado = 'PENDIENTE';
        }

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

    public function guardar(Request $request, Postulacion $postulacion, BitacoraService $bitacoraService): JsonResponse {
        $datos = $request->validate([
            'requisitos' => ['required', 'array', 'min:1'],
            'requisitos.*.id' => ['required', 'integer', 'distinct'],
            'requisitos.*.cumple' => ['present', 'nullable', 'boolean'],
            'requisitos.*.observacion' => ['nullable', 'string', 'max:500'],
        ]);

        $usuarioId = auth()->id();

        DB::beginTransaction();

        try {
            $postulacion = Postulacion::query()
                ->lockForUpdate()
                ->findOrFail($postulacion->getKey());

            if ((int) $postulacion->estado_id !== PostulacionEstado::EN_REVISION) {
                throw ValidationException::withMessages([
                    'postulacion' => 'Solo puedes editar los requisitos cuando la postulación está en revisión.',
                ]);
            }

            $requisitoIds = array_map('intval', array_column($datos['requisitos'], 'id'));

            $requisitosVacante = $postulacion->vacante
                ->requisitos()
                ->whereKey($requisitoIds)
                ->get()
                ->modelKeys();

            $idsInvalidos = array_diff($requisitoIds, $requisitosVacante);

            if ($idsInvalidos) {
                throw ValidationException::withMessages([
                    'requisitos' => 'Estos requisitos no pertenecen a la vacante: '
                        . implode(', ', $idsInvalidos) . '.',
                ]);
            }

            $evaluaciones = PostulacionRequisito::query()
                ->where('postulacion_id', $postulacion->id)
                ->whereIn('requisito_id', $requisitoIds)
                ->get()
                ->keyBy('requisito_id');

            foreach ($datos['requisitos'] as $item) {
                $requisitoId = (int) $item['id'];
                $evaluacion = $evaluaciones->get($requisitoId);

                $cumpleAnterior = $evaluacion?->cumple;

                $anteriores = [
                    'cumple' => $cumpleAnterior === null ? null : (bool) $cumpleAnterior,
                    'observacion' => $evaluacion?->observacion,
                ];

                $nuevos = [
                    'cumple' => $item['cumple'] === null ? null : (bool) $item['cumple'],
                    'observacion' => $item['observacion'] ?? null,
                ];

                if ($anteriores === $nuevos) {
                    continue;
                }

                if ($evaluacion === null) {
                    $evaluacion = new PostulacionRequisito();
                    $evaluacion->postulacion_id = $postulacion->id;
                    $evaluacion->requisito_id = $requisitoId;
                }

                $evaluacion->cumple = $nuevos['cumple'];
                $evaluacion->observacion = $nuevos['observacion'];
                $evaluacion->evaluado_por = $usuarioId;
                $evaluacion->evaluado_at = now();
                $evaluacion->save();

                $bitacoraService->registrar(
                    $postulacion,
                    'EVALUACION_REQUISITO',
                    'Se evaluó un requisito de la postulación.',
                    ['requisito_id' => $requisitoId, ...$anteriores],
                    ['requisito_id' => $requisitoId, ...$nuevos],
                    $usuarioId
                );
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        return $this->index($postulacion);
    }
}
