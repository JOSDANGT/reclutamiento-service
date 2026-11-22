<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DataInicialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RolesPermisosSeeder::class,
            HabilidadCategoriasSeeder::class,
            HabilidadesSeeder::class,
            NivelHabilidadSeeder::class,
            PostulacionEstadosSeeder::class,
            UnidadSeeder::class,
            PuestoSeeder::class,
            TipoRequisitoSeeder::class,
            SuperAdminSeeder::class,
        ]);
    }
}
