<?php

namespace Database\Seeders;

use App\Models\PostulacionEstado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostulacionEstadosSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['nombre' => 'RECIBIDA', 'orden' => 1],
            ['nombre' => 'EN REVISION', 'orden' => 2],
            ['nombre' => 'ENTREVISTA', 'orden' => 3],
            ['nombre' => 'SELECCIONADA', 'orden' => 4],
            ['nombre' => 'RECHAZADA', 'orden' => 5],
            ['nombre' => 'RETIRADA', 'orden' => 6],
            ['nombre' => 'CONTRATADA', 'orden' => 7],
            ['nombre' => 'OFERTA RECHAZADA', 'orden' => 8],
        ];

        foreach ($estados as $estado) {
            PostulacionEstado::updateOrCreate(
                ['nombre' => $estado['nombre']],
                [
                    'nombre' => $estado['nombre'],
                    'orden' => $estado['orden'],
                    'estado' => true,
                ]
            );
        }
    }
}
