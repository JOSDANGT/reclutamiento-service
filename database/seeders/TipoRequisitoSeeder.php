<?php

namespace Database\Seeders;

use App\Models\TipoRequisito;
use Illuminate\Database\Seeder;

class TipoRequisitoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            [
                'nombre' => 'Formación académica',
                'descripcion' => 'Nivel académico, estudios universitarios y títulos requeridos para desempeñar el puesto.',
            ],
            [
                'nombre' => 'Experiencia laboral',
                'descripcion' => 'Experiencia profesional previa, años de experiencia y conocimientos adquiridos en puestos similares.',
            ],
            [
                'nombre' => 'Conocimientos técnicos',
                'descripcion' => 'Conocimientos especializados, herramientas, tecnologías y procedimientos necesarios para el puesto.',
            ],
            [
                'nombre' => 'Habilidades y competencias',
                'descripcion' => 'Competencias personales y profesionales, comunicación, liderazgo, organización y trabajo en equipo.',
            ],
            [
                'nombre' => 'Certificaciones',
                'descripcion' => 'Certificaciones profesionales, acreditaciones y cursos especializados requeridos.',
            ],
            [
                'nombre' => 'Idiomas',
                'descripcion' => 'Idiomas extranjeros y niveles de dominio requeridos para el desempeño del puesto.',
            ],
            [
                'nombre' => 'Conocimientos normativos',
                'descripcion' => 'Conocimiento de legislación, regulaciones, políticas institucionales y normativa aplicable.',
            ],
            [
                'nombre' => 'Manejo de herramientas y sistemas',
                'descripcion' => 'Manejo de programas informáticos, sistemas institucionales y herramientas de trabajo.',
            ],
            [
                'nombre' => 'Disponibilidad y condiciones laborales',
                'descripcion' => 'Disponibilidad de horario, movilidad, viajes y condiciones particulares del puesto.',
            ],
            [
                'nombre' => 'Otros requisitos',
                'descripcion' => 'Requisitos adicionales que no corresponden a las categorías anteriores.',
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoRequisito::updateOrCreate(
                ['nombre' => $tipo['nombre']],
                [
                    'descripcion' => $tipo['descripcion'],
                    'estado' => true,
                ]
            );
        }

        $this->command->info(
            'Tipos de requisitos configurados correctamente.'
        );
    }
}
