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
            HabilidadCategoria::DESARROLLO_BACKEND => [
                'PHP', 'Laravel', 'Java', 'C#', 'Node.js', 'Python',
            ],
            HabilidadCategoria::DESARROLLO_FRONTEND => [
                'HTML', 'CSS', 'JavaScript', 'Vue.js', 'React', 'Nuxt',
            ],
            HabilidadCategoria::BASES_DE_DATOS => [
                'SQL Server', 'MySQL', 'PostgreSQL', 'MongoDB',
            ],
            HabilidadCategoria::DEVOPS_E_INFRAESTRUCTURA => [
                'Docker', 'Git', 'CI/CD', 'Linux', 'Azure',
            ],
            HabilidadCategoria::CIBERSEGURIDAD => [
                'OWASP', 'Seguridad de APIs', 'Zero Trust',
            ],
            HabilidadCategoria::ANALISIS_DE_DATOS => [
                'Power BI', 'Excel', 'Análisis estadístico',
            ],
            HabilidadCategoria::GESTION_PROYECTOS => [
                'Scrum', 'Kanban', 'Planificación de proyectos',
            ],
            HabilidadCategoria::HABILIDADES_BLANDAS => [
                'Comunicación efectiva', 'Liderazgo',
                'Trabajo en equipo', 'Resolución de problemas',
            ],
        ];

        foreach ($habilidades as $categorias => $nombres) {
            $categoria = HabilidadCategoria::where(
                'id',
                $categorias
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
