<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\Habilidad;
use App\Models\NivelHabilidad;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CandidatoHabilidadService
{
    /**
     * Obtener el perfil del usuario autenticado.
     */
    private function obtenerCandidato(int $usuarioId): Candidato
    {
        return Candidato::query()
            ->where('user_id', $usuarioId)
            ->where('estado', true)
            ->firstOrFail();
    }

    /**
     * Comprobar que la habilidad pertenece al candidato.
     */
    private function obtenerHabilidadAsociada(Candidato $candidato, int $habilidadId): Habilidad {
        return $candidato->habilidades()
            ->whereKey($habilidadId)
            ->firstOrFail();
    }

    /**
     * Consultar las habilidades del candidato.
     */
    public function listar(int $usuarioId): Collection
    {
        $candidato = $this->obtenerCandidato($usuarioId);

        $habilidades = $candidato->habilidades()
            ->with('categoria')
            ->orderBy('nombre')
            ->get();

        $niveles = NivelHabilidad::query()
            ->whereIn(
                'id',
                $habilidades->pluck('pivot.nivel_habilidad_id')
                    ->filter()
                    ->unique()
            )
            ->get()
            ->keyBy('id');

        return $habilidades->map(function ($habilidad) use ($niveles) {
            $nivelId = $habilidad->pivot->nivel_habilidad_id;

            return [
                'id' => $habilidad->id,
                'nombre' => $habilidad->nombre,
                'categoria' => $habilidad->categoria,
                'nivel_habilidad_id' => $nivelId,
                'nivel_habilidad' => $niveles->get($nivelId),
                'anios_experiencia' =>
                    $habilidad->pivot->anios_experiencia,
            ];
        });
    }

    /**
     * Asociar una habilidad al perfil.
     */
    public function asociar(int $usuarioId, array $datos): Collection {

        return DB::connection('sqlsrv')->transaction(
            function () use ($usuarioId, $datos) {

                $candidato = $this->obtenerCandidato($usuarioId);

                $habilidad = Habilidad::query()
                    ->where('estado', true)
                    ->findOrFail($datos['habilidad_id']);

                $nivel = NivelHabilidad::query()
                    ->where('activo', true)
                    ->findOrFail($datos['nivel_habilidad_id']);

                $existe = $candidato->habilidades()
                    ->whereKey($habilidad->id)
                    ->exists();

                if ($existe) {
                    throw ValidationException::withMessages([
                        'habilidad_id' =>
                            'La habilidad ya está asociada a su perfil.',
                    ]);
                }

                $pivot = [
                    'nivel_habilidad_id' => $nivel->id,
                    'anios_experiencia' =>
                        $datos['anios_experiencia'] ?? null,
                ];

                $candidato->habilidades()->attach(
                    $habilidad->id,
                    $pivot
                );

                $candidato->bitacoras()->create([
                    'accion' => 'ASOCIACION_HABILIDAD',
                    'descripcion' =>
                        'Se agregó una habilidad al perfil del candidato.',
                    'datos_anteriores' => null,
                    'datos_nuevos' => [
                        'habilidad_id' => $habilidad->id,
                        ...$pivot,
                    ],
                    'usuario_id' => $usuarioId,
                ]);

                return $this->listar($usuarioId);
            }
        );
    }

    /**
     * Actualizar nivel y años de experiencia.
     */
    public function actualizar(int $usuarioId, int $habilidadId, array $datos): Collection {

        return DB::connection('sqlsrv')->transaction(
            function () use ($usuarioId, $habilidadId, $datos) {

                $candidato = $this->obtenerCandidato($usuarioId);

                $habilidad = $this->obtenerHabilidadAsociada(
                    $candidato,
                    $habilidadId
                );

                if (array_key_exists('nivel_habilidad_id', $datos)) {
                    NivelHabilidad::query()
                        ->where('activo', true)
                        ->findOrFail($datos['nivel_habilidad_id']);
                }

                $anteriores = [
                    'nivel_habilidad_id' =>
                        $habilidad->pivot->nivel_habilidad_id,
                    'anios_experiencia' =>
                        $habilidad->pivot->anios_experiencia,
                ];

                $cambios = [];

                foreach ($datos as $campo => $valor) {
                    if (
                        array_key_exists($campo, $anteriores)
                        && $anteriores[$campo] != $valor
                    ) {
                        $cambios[$campo] = $valor;
                    }
                }

                if (!empty($cambios)) {
                    $candidato->habilidades()->updateExistingPivot(
                        $habilidadId,
                        $cambios
                    );

                    $candidato->bitacoras()->create([
                        'accion' => 'ACTUALIZACION_HABILIDAD_CANDIDATO',
                        'descripcion' =>
                            'Se actualizó una habilidad del candidato.',
                        'datos_anteriores' => array_intersect_key(
                            $anteriores,
                            $cambios
                        ),
                        'datos_nuevos' => $cambios,
                        'usuario_id' => $usuarioId,
                    ]);
                }

                return $this->listar($usuarioId);
            }
        );
    }

    /**
     * Quitar una habilidad del perfil.
     */
    public function eliminar(int $usuarioId, int $habilidadId): void {
        DB::connection('sqlsrv')->transaction(
            function () use ($usuarioId, $habilidadId) {

                $candidato = $this->obtenerCandidato($usuarioId);

                $habilidad = $this->obtenerHabilidadAsociada(
                    $candidato,
                    $habilidadId
                );

                $candidato->bitacoras()->create([
                    'accion' => 'DESASOCIACION_HABILIDAD',
                    'descripcion' =>
                        'Se retiró una habilidad del perfil.',
                    'datos_anteriores' => [
                        'habilidad_id' => $habilidad->id,
                        'nivel_habilidad_id' =>
                            $habilidad->pivot->nivel_habilidad_id,
                        'anios_experiencia' =>
                            $habilidad->pivot->anios_experiencia,
                    ],
                    'datos_nuevos' => null,
                    'usuario_id' => $usuarioId,
                ]);

                $candidato->habilidades()->detach($habilidadId);
            }
        );
    }
}
