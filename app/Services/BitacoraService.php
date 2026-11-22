<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class BitacoraService
{
    public function registrar(
        Model $registro,
        string $accion,
        string $descripcion,
        ?array $anteriores,
        ?array $nuevos,
        int $usuarioId
    ): void {
        $registro->bitacoras()->create([
            'accion' => $accion,
            'descripcion' => $descripcion,
            'datos_anteriores' => $anteriores,
            'datos_nuevos' => $nuevos,
            'usuario_id' => $usuarioId,
        ]);
    }
}
