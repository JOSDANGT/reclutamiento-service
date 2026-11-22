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
                'descripcion' => 'Desarrollo y mantenimiento de sistemas, infraestructura, bases de datos, soporte técnico y seguridad informática.',
            ],
            [
                'nombre' => 'Recursos Humanos',
                'descripcion' => 'Reclutamiento, selección, contratación, capacitación, administración y desarrollo del talento humano.',
            ],
            [
                'nombre' => 'Créditos',
                'descripcion' => 'Recepción, análisis, verificación, evaluación, asignación y seguimiento de solicitudes de crédito.',
            ],
            [
                'nombre' => 'Formalización Crediticia',
                'descripcion' => 'Formalización de créditos, preparación y control de documentos, escrituración, garantías y coordinación de desembolsos.',
            ],
            [
                'nombre' => 'Operaciones',
                'descripcion' => 'Ejecución y supervisión de operaciones financieras, procesamiento de transacciones y gestión operativa de tarjetas.',
            ],
            [
                'nombre' => 'Contabilidad',
                'descripcion' => 'Registros contables, conciliaciones, control de pagos, obligaciones financieras y elaboración de estados financieros.',
            ],
            [
                'nombre' => 'Finanzas',
                'descripcion' => 'Administración de recursos financieros, tesorería, flujo de efectivo, presupuestos y planificación financiera.',
            ],
            [
                'nombre' => 'Auditoría Interna',
                'descripcion' => 'Evaluación independiente del control interno, revisión de procesos, auditorías y seguimiento de hallazgos.',
            ],
            [
                'nombre' => 'Cumplimiento',
                'descripcion' => 'Prevención de lavado de dinero y financiamiento del terrorismo, debida diligencia, controles PEP/CPE y cumplimiento normativo.',
            ],
            [
                'nombre' => 'Jurídico',
                'descripcion' => 'Asesoría legal, revisión y elaboración de contratos, documentación jurídica, garantías y trámites legales.',
            ],
            [
                'nombre' => 'Mercadeo',
                'descripcion' => 'Promoción de productos y servicios, campañas comerciales, comunicación institucional y posicionamiento de marca.',
            ],
            [
                'nombre' => 'Servicio al Asociado',
                'descripcion' => 'Atención de consultas, gestión de solicitudes, orientación, actualización de información y seguimiento a asociados.',
            ],
            [
                'nombre' => 'Agencias',
                'descripcion' => 'Administración, coordinación y supervisión de agencias, personal operativo y servicios financieros presenciales.',
            ],
            [
                'nombre' => 'Riesgos',
                'descripcion' => 'Identificación, medición, evaluación y seguimiento de riesgos crediticios, operativos y financieros.',
            ],
            [
                'nombre' => 'Administración',
                'descripcion' => 'Gestión de recursos materiales, compras, suministros, mantenimiento, instalaciones y servicios generales.',
            ],
            [
                'nombre' => 'Contact Center',
                'descripcion' => 'Atención telefónica, seguimiento de gestiones, coordinación de citas y comunicación con asociados.',
            ],
            [
                'nombre' => 'Cobros',
                'descripcion' => 'Gestión de cobranza preventiva y correctiva, recuperación de cartera y seguimiento de obligaciones crediticias.',
            ],
            [
                'nombre' => 'Archivo y Digitalización',
                'descripcion' => 'Recepción, clasificación, digitalización, custodia y control de expedientes y documentos institucionales.',
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
