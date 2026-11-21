<?php

namespace App\Services;

use App\Models\HabilidadCategoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HabilidadCategoriaService
{
    public function listar(array $filtros): LengthAwarePaginator
    {
        return HabilidadCategoria::query()
            ->withCount('habilidades')
            ->when(
                !empty($filtros['search']),
                fn ($query) => $query->where(
                    'nombre',
                    'like',
                    '%' . $filtros['search'] . '%'
                )
            )
            ->when(
                isset($filtros['estado']),
                fn ($query) => $query->where(
                    'estado',
                    $filtros['estado']
                )
            )
            ->orderBy('nombre')
            ->paginate($filtros['per_page'] ?? 15);
    }

    public function crear(array $datos): HabilidadCategoria
    {
        return HabilidadCategoria::create($datos);
    }

    public function actualizar(
        HabilidadCategoria $categoria,
        array $datos
    ): HabilidadCategoria {
        $categoria->update($datos);

        return $categoria->refresh();
    }
}
