<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\ExperienciaLaboral;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExperienciaLaboralService
{
    private function obtenerCandidato(int $usuarioId): Candidato
    {
        return Candidato::query()
            ->where('user_id', $usuarioId)
            ->where('estado', true)
            ->firstOrFail();
    }

    private function obtenerExperiencia(Candidato $candidato, int $experienciaId): ExperienciaLaboral {
        return $candidato->experienciasLaborales()
            ->findOrFail($experienciaId);
    }

    public function listar(int $usuarioId): Collection
    {
        $candidato = $this->obtenerCandidato($usuarioId);

        return $candidato->experienciasLaborales()
            ->orderByDesc('trabajo_actual')
            ->orderByDesc('fecha_inicio')
            ->get();
    }

    public function obtener(
        int $usuarioId,
        int $experienciaId
    ): ExperienciaLaboral {
        $candidato = $this->obtenerCandidato($usuarioId);

        return $this->obtenerExperiencia(
            $candidato,
            $experienciaId
        );
    }

    private function validarFechas(array $datos): void
    {
        Validator::make($datos, [
            'fecha_inicio' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'trabajo_actual' => [
                'required',
                'boolean',
            ],
            'fecha_fin' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:fecha_inicio',
            ],
        ])->after(function ($validator) use ($datos) {
            $trabajoActual = (bool) $datos['trabajo_actual'];
            $fechaFin = $datos['fecha_fin'] ?? null;

            if ($trabajoActual && $fechaFin !== null) {
                $validator->errors()->add(
                    'fecha_fin',
                    'Un trabajo actual no puede tener fecha de finalización.'
                );
            }

            if (!$trabajoActual && $fechaFin === null) {
                $validator->errors()->add(
                    'fecha_fin',
                    'Debe indicar la fecha de finalización.'
                );
            }
        })->validate();
    }

    public function crear(
        int $usuarioId,
        array $datos
    ): ExperienciaLaboral {
        return DB::connection('sqlsrv')->transaction(
            function () use ($usuarioId, $datos) {

                $candidato = $this->obtenerCandidato($usuarioId);

                $datos['fecha_fin'] = $datos['fecha_fin'] ?? null;

                $this->validarFechas($datos);

                $experiencia = $candidato
                    ->experienciasLaborales()
                    ->create($datos);

                $experiencia->bitacoras()->create([
                    'accion' => 'CREACION_EXPERIENCIA_LABORAL',
                    'descripcion' =>
                        'Se registró una experiencia laboral.',
                    'datos_anteriores' => null,
                    'datos_nuevos' => $experiencia->only([
                        'empresa',
                        'puesto',
                        'tipo_empleo',
                        'ubicacion',
                        'fecha_inicio',
                        'fecha_fin',
                        'trabajo_actual',
                        'descripcion',
                        'logros',
                    ]),
                    'usuario_id' => $usuarioId,
                ]);

                return $experiencia->refresh();
            }
        );
    }

    public function actualizar(
        int $usuarioId,
        int $experienciaId,
        array $datos
    ): ExperienciaLaboral {
        return DB::connection('sqlsrv')->transaction(
            function () use ($usuarioId, $experienciaId, $datos) {

                $candidato = $this->obtenerCandidato($usuarioId);

                $experiencia = $this->obtenerExperiencia(
                    $candidato,
                    $experienciaId
                );

                // Combinar los datos existentes con el PATCH.
                $actuales = $experiencia->only([
                    'fecha_inicio',
                    'fecha_fin',
                    'trabajo_actual',
                ]);

                $actuales['fecha_inicio'] = $experiencia
                    ->fecha_inicio?->format('Y-m-d');

                $actuales['fecha_fin'] = $experiencia
                    ->fecha_fin?->format('Y-m-d');

                // Si pasa a trabajo actual, quitar fecha final.
                if (
                    array_key_exists('trabajo_actual', $datos)
                    && (bool) $datos['trabajo_actual']
                ) {
                    $datos['fecha_fin'] = null;
                }

                $fechas = array_replace($actuales, array_intersect_key(
                    $datos,
                    $actuales
                ));

                $this->validarFechas($fechas);

                $experiencia->fill($datos);

                $cambios = $experiencia->getDirty();

                if (!empty($cambios)) {
                    $anteriores = [];

                    foreach ($cambios as $campo => $valor) {
                        $anterior = $experiencia->getRawOriginal($campo);

                        if (in_array(
                            $campo,
                            ['fecha_inicio', 'fecha_fin'],
                            true
                        )) {
                            $anterior = $anterior
                                ? substr((string) $anterior, 0, 10)
                                : null;
                        }

                        $anteriores[$campo] = $anterior;
                    }

                    $experiencia->save();

                    $experiencia->bitacoras()->create([
                        'accion' => 'ACTUALIZACION_EXPERIENCIA_LABORAL',
                        'descripcion' =>
                            'Se actualizó una experiencia laboral.',
                        'datos_anteriores' => $anteriores,
                        'datos_nuevos' => $cambios,
                        'usuario_id' => $usuarioId,
                    ]);
                }

                return $experiencia->refresh();
            }
        );
    }

    public function eliminar(
        int $usuarioId,
        int $experienciaId
    ): void {
        DB::connection('sqlsrv')->transaction(
            function () use ($usuarioId, $experienciaId) {

                $candidato = $this->obtenerCandidato($usuarioId);

                $experiencia = $this->obtenerExperiencia(
                    $candidato,
                    $experienciaId
                );

                $experiencia->bitacoras()->create([
                    'accion' => 'ELIMINACION_EXPERIENCIA_LABORAL',
                    'descripcion' =>
                        'Se eliminó una experiencia laboral.',
                    'datos_anteriores' => [
                        'empresa' => $experiencia->empresa,
                        'puesto' => $experiencia->puesto,
                    ],
                    'datos_nuevos' => null,
                    'usuario_id' => $usuarioId,
                ]);

                $experiencia->delete();
            }
        );
    }
}
