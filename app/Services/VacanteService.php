<?php

namespace App\Services;

use App\Models\Vacante;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VacanteService
{
    public function listar(array $filtros = []): LengthAwarePaginator
    {
        $query = Vacante::query()
            ->with('requisitos')
            ->withCount('requisitos');

        if (!empty($filtros['search'])) {
            $search = $filtros['search'];

            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['modalidad'])) {
            $query->where('modalidad', $filtros['modalidad']);
        }

        $perPage = min(
            max((int) ($filtros['per_page'] ?? 15), 1),
            100
        );

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function obtener(Vacante $vacante): Vacante
    {
        return $vacante->load([
            'requisitos.tipoRequisito',
            'bitacoras',
        ]);
    }

    public function crear(array $data, ?int $usuarioId = null): Vacante {
        return DB::connection('sqlsrv')->transaction(
            function () use ($data, $usuarioId) {

                $requisitos = $data['requisitos'] ?? [];

                unset($data['requisitos'], $data['creado_por']);

                $data['creado_por'] = $usuarioId;

                $vacante = Vacante::create($data);

                $this->sincronizarRequisitos(
                    $vacante,
                    $requisitos
                );

                $this->registrarBitacora(
                    $vacante,
                    'CREACION',
                    'Se registró la vacante.',
                    null,
                    $vacante->fresh()
                        ->load('requisitos')
                        ->toArray(),
                    $usuarioId
                );

                return $this->obtener($vacante);
            }
        );
    }

    public function actualizar(Vacante $vacante, array $data, ?int $usuarioId = null): Vacante {
        return DB::connection('sqlsrv')->transaction(
            function () use ($vacante, $data, $usuarioId) {

                $anteriores = $this->obtener($vacante)
                    ->toArray();

                $actualizarRequisitos = array_key_exists(
                    'requisitos',
                    $data
                );

                $requisitos = $data['requisitos'] ?? [];

                unset($data['requisitos'], $data['creado_por']);

                $vacante->update($data);

                if ($actualizarRequisitos) {
                    $this->sincronizarRequisitos(
                        $vacante,
                        $requisitos
                    );
                }

                $nuevos = $vacante->fresh()
                    ->load('requisitos')
                    ->toArray();

                $this->registrarBitacora(
                    $vacante,
                    'ACTUALIZACION',
                    'Se actualizó la información de la vacante.',
                    $anteriores,
                    $nuevos,
                    $usuarioId
                );

                return $this->obtener($vacante->fresh());
            }
        );
    }

    public function cambiarEstado(Vacante $vacante, string $estado, ?int $usuarioId = null): Vacante {
        return DB::connection('sqlsrv')->transaction(
            function () use ($vacante, $estado, $usuarioId) {

                $estadoAnterior = $vacante->estado;

                $vacante->update([
                    'estado' => $estado,
                ]);

                if ($estadoAnterior !== $estado) {
                    $this->registrarBitacora(
                        $vacante,
                        'CAMBIO_ESTADO',
                        "Estado actualizado a {$estado}.",
                        ['estado' => $estadoAnterior],
                        ['estado' => $estado],
                        $usuarioId
                    );
                }

                return $this->obtener($vacante->fresh());
            }
        );
    }

    public function eliminar(
        Vacante $vacante,
        ?int $usuarioId = null
    ): void {
        DB::connection('sqlsrv')->transaction(
            function () use ($vacante, $usuarioId) {

                $this->registrarBitacora(
                    $vacante,
                    'ELIMINACION',
                    'Se eliminó lógicamente la vacante.',
                    ['estado' => $vacante->estado],
                    null,
                    $usuarioId
                );

                $vacante->delete();
            }
        );
    }

    private function sincronizarRequisitos(Vacante $vacante, array $requisitos): void {
        $datos = [];

        foreach ($requisitos as $requisito) {
            $datos[$requisito['id']] = [
                'obligatorio' => $requisito['obligatorio'],
                'orden' => $requisito['orden'],
            ];
        }

        $vacante->requisitos()->sync($datos);
    }

    private function registrarBitacora(
        Vacante $vacante,
        string $accion,
        string $descripcion,
        ?array $anteriores,
        ?array $nuevos,
        ?int $usuarioId
    ): void {
        $vacante->bitacoras()->create([
            'accion' => $accion,
            'descripcion' => $descripcion,
            'datos_anteriores' => $anteriores,
            'datos_nuevos' => $nuevos,
            'usuario_id' => $usuarioId,
        ]);
    }
}
