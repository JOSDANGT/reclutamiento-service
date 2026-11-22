<?php

namespace Database\Seeders;

use App\Models\Unidad;
use Illuminate\Database\Seeder;

class UnidadSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            [
                'nombre' => 'Tecnología de la Información',
                'descripcion' => 'Desarrollo de sistemas, infraestructura tecnológica, soporte técnico y seguridad informática.',
            ],
            [
                'nombre' => 'Recursos Humanos',
                'descripcion' => 'Reclutamiento, selección, capacitación y administración del talento humano.',
            ],
            [
                'nombre' => 'Créditos',
                'descripcion' => 'Evaluación, análisis, aprobación y seguimiento de solicitudes de crédito.',
            ],
            [
                'nombre' => 'Operaciones',
                'descripcion' => 'Gestión y supervisión de las operaciones de la cooperativa.',
            ],
            [
                'nombre' => 'Contabilidad',
                'descripcion' => 'Registro contable, conciliaciones y elaboración de estados financieros.',
            ],
            [
                'nombre' => 'Finanzas',
                'descripcion' => 'Administración de recursos financieros, presupuestos y planificación financiera.',
            ],
            [
                'nombre' => 'Auditoría Interna',
                'descripcion' => 'Evaluación de controles internos, procesos y cumplimiento institucional.',
            ],
            [
                'nombre' => 'Cumplimiento',
                'descripcion' => 'Prevención de lavado de dinero, gestión de riesgos normativos y cumplimiento regulatorio.',
            ],
            [
                'nombre' => 'Jurídico',
                'descripcion' => 'Asesoría legal, formalización de contratos y gestión de asuntos jurídicos.',
            ],
            [
                'nombre' => 'Mercadeo',
                'descripcion' => 'Promoción de productos, comunicación institucional y estrategias comerciales.',
            ],
            [
                'nombre' => 'Servicio al Asociado',
                'descripcion' => 'Atención de consultas, solicitudes y seguimiento de asociados.',
            ],
            [
                'nombre' => 'Agencias',
                'descripcion' => 'Administración y coordinación de las agencias de la cooperativa.',
            ],
            [
                'nombre' => 'Riesgos',
                'descripcion' => 'Identificación, evaluación y seguimiento de riesgos institucionales.',
            ],
            [
                'nombre' => 'Administración',
                'descripcion' => 'Gestión de recursos materiales, servicios generales y procesos administrativos.',
            ],
        ];

        foreach ($unidades as $unidad) {
            Unidad::updateOrCreate(
                ['nombre' => $unidad['nombre']],
                [
                    'descripcion' => $unidad['descripcion'],
                    'estado' => true,
                ]
            );
        }
    }
}
