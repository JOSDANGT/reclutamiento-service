<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\FormacionAcademica;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FormacionAcademicaService
{
    private function obtenerCandidato(int $usuarioId): Candidato
    {
        return Candidato::query()
            ->where('user_id', $usuarioId)
            ->where('estado', true)
            ->firstOrFail();
    }

    private function obtenerFormacion(Candidato $candidato, int $formacionId): FormacionAcademica {
        return $candidato->formacionesAcademicas()
            ->findOrFail($formacionId);
    }

    public function listar(int $usuarioId): Collection
    {
        $candidato = $this->obtenerCandidato($usuarioId);

        return $candidato->formacionesAcademicas()
            ->orderByDesc('en_curso')
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->get();
    }

    public function obtener(
        int $usuarioId,
        int $formacionId
    ): FormacionAcademica {
        $candidato = $this->obtenerCandidato($usuarioId);

        return $this->obtenerFormacion($candidato, $formacionId);
    }

    private function validarFormacion(array $datos): void
    {
        Validator::make($datos, [
            'fecha_inicio' => [
                'nullable',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'fecha_fin' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:fecha_inicio',
            ],
            'en_curso' => ['required', 'boolean'],
            'finalizado' => ['required', 'boolean'],
        ])->after(function ($validator) use ($datos) {

            $enCurso = (bool) $datos['en_curso'];
            $finalizado = (bool) $datos['finalizado'];
            $fechaFin = $datos['fecha_fin'] ?? null;

            if ($enCurso && $finalizado) {
                $validator->errors()->add(
                    'finalizado',
                    'Una formación en curso no puede estar finalizada.'
                );
            }

            if ($enCurso && $fechaFin !== null) {
                $validator->errors()->add(
                    'fecha_fin',
                    'Una formación en curso no debe tener fecha de finalización.'
                );
            }

            if ($finalizado && $fechaFin === null) {
                $validator->errors()->add(
                    'fecha_fin',
                    'Debe indicar la fecha de finalización.'
                );
            }

        })->validate();
    }

    public function crear(int $usuarioId, array $datos): FormacionAcademica {
        return DB::connection('sqlsrv')->transaction(
            function () use ($usuarioId, $datos) {

                $candidato = $this->obtenerCandidato($usuarioId);

                $datos['fecha_fin'] = $datos['fecha_fin'] ?? null;

                $this->validarFormacion($datos);

                $formacion = $candidato->formacionesAcademicas()
                    ->create($datos);

                $formacion->bitacoras()->create([
                    'accion' => 'CREACION_FORMACION_ACADEMICA',
                    'descripcion' =>
                        'Se registró una formación académica.',
                    'datos_anteriores' => null,
                    'datos_nuevos' => $formacion->getAttributes(),
                    'usuario_id' => $usuarioId,
                ]);

                return $formacion->refresh();
            }
        );
    }

    public function actualizar(int $usuarioId, int $formacionId, array $datos): FormacionAcademica {
        return DB::connection('sqlsrv')->transaction(
            function () use ($usuarioId, $formacionId, $datos) {

                $candidato = $this->obtenerCandidato($usuarioId);

                $formacion = $this->obtenerFormacion(
                    $candidato,
                    $formacionId
                );

                $actuales = [
                    'fecha_inicio' =>
                        $formacion->fecha_inicio?->format('Y-m-d'),
                    'fecha_fin' =>
                        $formacion->fecha_fin?->format('Y-m-d'),
                    'en_curso' => $formacion->en_curso,
                    'finalizado' => $formacion->finalizado,
                ];

                // Al continuar estudiando, no hay fecha de fin
                // y tampoco se considera finalizada.
                if (
                    array_key_exists('en_curso', $datos)
                    && $datos['en_curso'] === true
                ) {
                    $datos['fecha_fin'] = null;
                    $datos['finalizado'] = false;
                }

                // Si finalizó, ya no está en curso.
                if (
                    array_key_exists('finalizado', $datos)
                    && $datos['finalizado'] === true
                ) {
                    $datos['en_curso'] = false;
                }

                $resultado = array_replace(
                    $actuales,
                    array_intersect_key($datos, $actuales)
                );

                $this->validarFormacion($resultado);

                $formacion->fill($datos);

                $cambios = $formacion->getDirty();

                if (!empty($cambios)) {
                    $anteriores = array_intersect_key(
                        $formacion->getRawOriginal(),
                        $cambios
                    );

                    $formacion->save();

                    $formacion->bitacoras()->create([
                        'accion' => 'ACTUALIZACION_FORMACION_ACADEMICA',
                        'descripcion' =>
                            'Se actualizó una formación académica.',
                        'datos_anteriores' => $anteriores,
                        'datos_nuevos' => $cambios,
                        'usuario_id' => $usuarioId,
                    ]);
                }

                return $formacion->refresh();
            }
        );
    }

    public function eliminar(int $usuarioId, int $formacionId): void {
        DB::connection('sqlsrv')->transaction(
            function () use ($usuarioId, $formacionId) {

                $candidato = $this->obtenerCandidato($usuarioId);

                $formacion = $this->obtenerFormacion(
                    $candidato,
                    $formacionId
                );

                $formacion->bitacoras()->create([
                    'accion' => 'ELIMINACION_FORMACION_ACADEMICA',
                    'descripcion' =>
                        'Se eliminó una formación académica.',
                    'datos_anteriores' => [
                        'institucion' => $formacion->institucion,
                        'titulo' => $formacion->titulo,
                    ],
                    'datos_nuevos' => null,
                    'usuario_id' => $usuarioId,
                ]);

                $formacion->delete();
            }
        );
    }
}
