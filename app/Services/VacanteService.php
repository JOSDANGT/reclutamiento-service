<?php

namespace App\Services;

use App\Models\Puesto;
use App\Models\Vacante;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class VacanteService
{
    public function listar(array $filtros = []): LengthAwarePaginator
    {
        $query = Vacante::query()
            ->with([
                'puesto.unidad',
                'requisitos',
            ])
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
            'puesto.unidad',
            'requisitos.tipoRequisito',
            'bitacoras.usuario',
        ]);
    }

    public function crear(array $data, ?int $usuarioId = null): Vacante
    {
        DB::beginTransaction();

        try {
            $requisitos = $data['requisitos'] ?? [];

            unset($data['requisitos'], $data['creado_por']);

            $data['creado_por'] = $usuarioId;

            $vacante = Vacante::create($data);

            // Asociar requisitos a la vacante.
            $this->sincronizarRequisitos($vacante, $requisitos);

            // Incorporar los requisitos nuevos a la plantilla del puesto.
            $this->agregarRequisitosPuesto($vacante, $requisitos);

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

            DB::commit();

            return $this->obtener($vacante->fresh());

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(Vacante $vacante, array $data, ?int $usuarioId = null): Vacante {
        DB::beginTransaction();

        try {
            $actualizarRequisitos = array_key_exists('requisitos', $data);
            $requisitos = $data['requisitos'] ?? [];

            unset($data['requisitos'], $data['creado_por']);

            $vacante->fill($data);

            $cambios = $vacante->getDirty();

            $anteriores = [];
            $nuevos = [];

            foreach ($cambios as $campo => $valorNuevo) {
                $anteriores[$campo] = $vacante->getOriginal($campo);
                $nuevos[$campo] = $valorNuevo;
            }

            if (!empty($cambios)) {
                $vacante->save();
            }

            if ($actualizarRequisitos) {
                $requisitosAnteriores = $this->obtenerResumenRequisitos(
                    $vacante
                );

                $this->sincronizarRequisitos(
                    $vacante,
                    $requisitos
                );

                $this->agregarRequisitosPuesto(
                    $vacante,
                    $requisitos
                );

                $requisitosNuevos = $this->obtenerResumenRequisitos(
                    $vacante
                );

                if ($requisitosAnteriores !== $requisitosNuevos) {
                    $anteriores['requisitos'] = $requisitosAnteriores;
                    $nuevos['requisitos'] = $requisitosNuevos;
                }
            }

            if (!empty($anteriores) || !empty($nuevos)) {
                $this->registrarBitacora(
                    $vacante,
                    'ACTUALIZACION',
                    'Se actualizó la información de la vacante.',
                    $anteriores,
                    $nuevos,
                    $usuarioId
                );
            }

            DB::commit();

            return $this->obtener($vacante->fresh());

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function cambiarEstado(
        Vacante $vacante,
        string $estado,
        ?int $usuarioId = null
    ): Vacante {
        DB::beginTransaction();

        try {
            $vacante = Vacante::query()
                ->lockForUpdate()
                ->findOrFail($vacante->id);

            $estadoAnterior = $vacante->estado;

            $transiciones = [
                'BORRADOR' => 'PUBLICADA',
                'PUBLICADA' => 'CERRADA',
            ];

            if (($transiciones[$estadoAnterior] ?? null) !== $estado) {
                throw ValidationException::withMessages([
                    'estado' => "No se permite cambiar el estado de {$estadoAnterior} a {$estado}.",
                ]);
            }

            $vacante->update([
                'estado' => $estado,
            ]);

            $this->registrarBitacora(
                $vacante,
                'CAMBIO_ESTADO',
                "Estado actualizado a {$estado}.",
                ['estado' => $estadoAnterior],
                ['estado' => $estado],
                $usuarioId
            );

            DB::commit();

            return $this->obtener($vacante->fresh());

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function eliminar(
        Vacante $vacante,
        ?int $usuarioId = null
    ): void {
        DB::beginTransaction();

        try {
            $this->registrarBitacora(
                $vacante,
                'ELIMINACION',
                'Se eliminó lógicamente la vacante.',
                ['estado' => $vacante->estado],
                null,
                $usuarioId
            );

            $vacante->delete();

            DB::commit();

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function sincronizarRequisitos(
        Vacante $vacante,
        array $requisitos
    ): void {
        $datos = [];

        foreach ($requisitos as $requisito) {
            $datos[$requisito['id']] = [
                'obligatorio' => (bool) $requisito['obligatorio'],
                'orden' => (int) $requisito['orden'],
            ];
        }

        $vacante->requisitos()->sync($datos);
    }

    private function agregarRequisitosPuesto(
        Vacante $vacante,
        array $requisitos
    ): void {
        if (!$vacante->puesto_id || empty($requisitos)) {
            return;
        }

        $puesto = Puesto::findOrFail($vacante->puesto_id);

        // Requisitos que ya forman parte de la plantilla del puesto.
        $existentes = $puesto->requisitos()
            ->get()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $datos = [];

        foreach ($requisitos as $requisito) {
            $requisitoId = (int) $requisito['id'];

            // No modificar los requisitos previamente configurados.
            if (in_array($requisitoId, $existentes, true)) {
                continue;
            }

            $datos[$requisitoId] = [
                'obligatorio' => (bool) $requisito['obligatorio'],
                'orden' => (int) $requisito['orden'],
            ];
        }

        if (!empty($datos)) {
            $puesto->requisitos()->syncWithoutDetaching($datos);
        }
    }

    private function obtenerResumenRequisitos(Vacante $vacante): array
    {
        return $vacante->requisitos()
            ->get()
            ->map(fn ($requisito) => [
                'id' => (int) $requisito->id,
                'nombre' => $requisito->nombre,
                'obligatorio' => (bool) $requisito->pivot->obligatorio,
                'orden' => (int) $requisito->pivot->orden,
            ])
            ->sortBy('id')
            ->values()
            ->toArray();
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
