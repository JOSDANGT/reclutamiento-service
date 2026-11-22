<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\Postulacion;
use App\Models\PostulacionEstado;
use App\Models\Vacante;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PostulacionService
{
    public function listar(array $filtros = []): LengthAwarePaginator
    {
        $query = Postulacion::query()
            ->with([
                'candidato',
                'vacante.puesto.unidad',
                'estado',
            ]);

        if (!empty($filtros['vacante_id'])) {
            $query->where('vacante_id', $filtros['vacante_id']);
        }

        if (!empty($filtros['estado_id'])) {
            $query->where('estado_id', $filtros['estado_id']);
        }

        if (!empty($filtros['candidato_id'])) {
            $query->where('candidato_id', $filtros['candidato_id']);
        }

        $perPage = min(
            max((int) ($filtros['per_page'] ?? 15), 1),
            100
        );

        return $query
            ->orderByDesc('fecha_postulacion')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function obtener(Postulacion $postulacion): Postulacion
    {
        return $postulacion->load([
            'candidato',
            'vacante.puesto.unidad',
            'vacante.requisitos.tipoRequisito',
            'estado',
            'documentos',
            'bitacoras.usuario',
        ]);
    }


    public function crear(array $data, int $usuarioId): Postulacion
    {
        DB::beginTransaction();

        try {
            // Identificar al candidato por su usuario de Identity Service.
            $candidato = Candidato::query()
                ->where('user_id', $usuarioId)
                ->first();

            if (!$candidato) {
                throw ValidationException::withMessages([
                    'candidato' => 'Debe completar su perfil de candidato antes de postularse.',
                ]);
            }

            // Bloquear la vacante mientras se registra la postulación.
            $vacante = Vacante::query()
                ->whereKey($data['vacante_id'])
                ->lockForUpdate()
                ->first();

            if (!$vacante || $vacante->estado !== 'PUBLICADA') {
                throw ValidationException::withMessages([
                    'vacante_id' => 'La vacante no está disponible para postulaciones.',
                ]);
            }

            // Evitar postulaciones duplicadas.
            $existe = Postulacion::query()
                ->where('candidato_id', $candidato->id)
                ->where('vacante_id', $vacante->id)
                ->exists();

            if ($existe) {
                throw ValidationException::withMessages([
                    'vacante_id' => 'Ya tiene una postulación registrada para esta vacante.',
                ]);
            }

            // Obtener el estado inicial desde el catálogo existente.
            $estado = PostulacionEstado::query()
                ->where('nombre', 'RECIBIDA')
                ->firstOrFail();

            $postulacion = Postulacion::create([
                'candidato_id' => $candidato->id,
                'vacante_id' => $vacante->id,
                'estado_id' => $estado->id,
                'fecha_postulacion' => now(),
                'carta_presentacion' => $data['carta_presentacion'] ?? null,
            ]);

            $postulacion->bitacoras()->create([
                'accion' => 'CREACION',
                'descripcion' => 'El candidato registró su postulación.',
                'datos_anteriores' => null,
                'datos_nuevos' => [
                    'candidato_id' => $candidato->id,
                    'vacante_id' => $vacante->id,
                    'estado_id' => $estado->id,
                    'fecha_postulacion' => $postulacion->fecha_postulacion,
                ],
                'usuario_id' => $usuarioId,
            ]);

            DB::commit();

            return $this->obtener($postulacion->fresh());

        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

}
