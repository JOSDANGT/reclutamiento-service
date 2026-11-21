<?php

namespace Database\Seeders;

use App\Models\NivelHabilidad;
use Illuminate\Database\Seeder;

class NivelHabilidadSeeder extends Seeder
{
    public function run(): void
    {
        $niveles = [
            'BÁSICO',
            'INTERMEDIO',
            'AVANZADO',
            'EXPERTO',
        ];

        foreach ($niveles as $nombre) {
            NivelHabilidad::updateOrCreate(
                ['nombre' => $nombre],
                ['activo' => true]
            );
        }
    }
}
