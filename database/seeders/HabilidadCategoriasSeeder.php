<?php

namespace Database\Seeders;

use App\Models\HabilidadCategoria;
use Illuminate\Database\Seeder;

class HabilidadCategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Desarrollo Backend', 'descripcion' => 'Tecnologías de servidor y APIs'],
            ['nombre' => 'Desarrollo Frontend', 'descripcion' => 'Interfaces y aplicaciones web'],
            ['nombre' => 'Bases de Datos', 'descripcion' => 'Motores y gestión de datos'],
            ['nombre' => 'DevOps e Infraestructura', 'descripcion' => 'Despliegue e infraestructura'],
            ['nombre' => 'Ciberseguridad', 'descripcion' => 'Protección de sistemas e información'],
            ['nombre' => 'Análisis de Datos', 'descripcion' => 'Procesamiento y análisis de información'],
            ['nombre' => 'Gestión de Proyectos', 'descripcion' => 'Organización y administración de proyectos'],
            ['nombre' => 'Habilidades Blandas', 'descripcion' => 'Competencias interpersonales'],
        ];

        foreach ($categorias as $categoria) {
            HabilidadCategoria::updateOrCreate(
                ['nombre' => $categoria['nombre']],
                [
                    'descripcion' => $categoria['descripcion'],
                    'estado' => true,
                ]
            );
        }
    }
}
