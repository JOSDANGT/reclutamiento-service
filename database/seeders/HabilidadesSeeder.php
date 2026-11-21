<?php

namespace Database\Seeders;

use App\Models\Habilidad;
use App\Models\HabilidadCategoria;
use Illuminate\Database\Seeder;

class HabilidadesSeeder extends Seeder
{
    public function run(): void
    {
        $habilidades = [
            'Desarrollo Backend' => [
                'PHP', 'Laravel', 'Java', 'C#', 'Node.js', 'Python',
            ],
            'Desarrollo Frontend' => [
                'HTML', 'CSS', 'JavaScript', 'Vue.js', 'React', 'Nuxt',
            ],
            'Bases de Datos' => [
                'SQL Server', 'MySQL', 'PostgreSQL', 'MongoDB',
            ],
            'DevOps e Infraestructura' => [
                'Docker', 'Git', 'CI/CD', 'Linux', 'Azure',
            ],
            'Ciberseguridad' => [
                'OWASP', 'Seguridad de APIs', 'Zero Trust',
            ],
            'Análisis de Datos' => [
                'Power BI', 'Excel', 'Análisis estadístico',
            ],
            'Gestión de Proyectos' => [
                'Scrum', 'Kanban', 'Planificación de proyectos',
            ],
            'Habilidades Blandas' => [
                'Comunicación efectiva', 'Liderazgo',
                'Trabajo en equipo', 'Resolución de problemas',
            ],
        ];

        foreach ($habilidades as $nombreCategoria => $nombres) {
            $categoria = HabilidadCategoria::where(
                'nombre',
                $nombreCategoria
            )->firstOrFail();

            foreach ($nombres as $nombre) {
                Habilidad::updateOrCreate(
                    ['nombre' => $nombre],
                    [
                        'categoria_id' => $categoria->id,
                        'estado' => true,
                    ]
                );
            }
        }
    }
}
