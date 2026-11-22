<?php

namespace Database\Seeders;

use App\Models\Puesto;
use App\Models\Unidad;
use Illuminate\Database\Seeder;

class PuestoSeeder extends Seeder
{
    public function run(): void
    {
        $puestos = [
            'Tecnología de la Información' => [
                'Gerente de Tecnología',
                'Jefe de Tecnología',
                'Coordinador de Desarrollo de Software',
                'Analista de Sistemas',
                'Desarrollador Backend',
                'Desarrollador Frontend',
                'Desarrollador Full Stack',
                'Administrador de Base de Datos',
                'Administrador de Infraestructura',
                'Analista de Seguridad Informática',
                'Técnico de Soporte',
            ],

            'Recursos Humanos' => [
                'Gerente de Recursos Humanos',
                'Jefe de Recursos Humanos',
                'Coordinador de Recursos Humanos',
                'Coordinador de Reclutamiento y Selección',
                'Analista de Reclutamiento',
                'Especialista de Selección de Personal',
                'Analista de Nómina',
                'Analista de Capacitación',
                'Asistente de Recursos Humanos',
            ],

            'Créditos' => [
                'Gerente de Créditos',
                'Jefe de Créditos',
                'Coordinador de Créditos',
                'Analista de Créditos',
                'Verificador de Créditos',
                'Asesor de Créditos',
                'Auxiliar de Créditos',
            ],

            'Formalización Crediticia' => [
                'Jefe de Formalización Crediticia',
                'Coordinador de Formalización',
                'Analista de Formalización',
                'Gestor de Formalización',
                'Gestor de Garantías',
                'Analista de Desembolsos',
                'Auxiliar de Formalización',
            ],

            'Operaciones' => [
                'Gerente de Operaciones',
                'Jefe de Operaciones',
                'Coordinador de Operaciones',
                'Analista de Operaciones',
                'Operador de Tarjetas',
                'Analista de Transacciones',
                'Auxiliar de Operaciones',
            ],

            'Contabilidad' => [
                'Contador General',
                'Subcontador',
                'Analista Contable',
                'Auxiliar Contable',
                'Analista de Conciliaciones',
                'Analista de Cuentas por Pagar',
            ],

            'Finanzas' => [
                'Gerente Financiero',
                'Jefe de Finanzas',
                'Jefe de Tesorería',
                'Analista Financiero',
                'Analista de Presupuestos',
                'Auxiliar de Tesorería',
            ],

            'Auditoría Interna' => [
                'Auditor Interno',
                'Jefe de Auditoría Interna',
                'Auditor de Procesos',
                'Auditor de Sistemas',
                'Auxiliar de Auditoría',
            ],

            'Cumplimiento' => [
                'Oficial de Cumplimiento',
                'Oficial de Cumplimiento Suplente',
                'Analista de Cumplimiento',
                'Analista de Prevención LD/FT',
                'Auxiliar de Cumplimiento',
            ],

            'Jurídico' => [
                'Jefe de Asesoría Jurídica',
                'Asesor Jurídico',
                'Abogado',
                'Analista Jurídico',
                'Gestor Jurídico',
                'Auxiliar Jurídico',
            ],

            'Mercadeo' => [
                'Gerente de Mercadeo',
                'Coordinador de Mercadeo',
                'Analista de Mercadeo',
                'Diseñador Gráfico',
                'Especialista de Comunicación',
                'Ejecutivo de Mercadeo',
            ],

            'Servicio al Asociado' => [
                'Jefe de Servicio al Asociado',
                'Coordinador de Servicio al Asociado',
                'Asesor de Servicio al Asociado',
                'Ejecutivo de Atención al Asociado',
                'Auxiliar de Servicio al Asociado',
            ],

            'Agencias' => [
                'Gerente de Agencias',
                'Supervisor de Agencias',
                'Jefe de Agencia',
                'Subjefe de Agencia',
                'Secretaria de Agencia',
                'Técnico Rotativo',
                'Receptor Pagador',
                'Cajero',
            ],

            'Riesgos' => [
                'Gerente de Riesgos',
                'Jefe de Riesgos',
                'Analista de Riesgo Crediticio',
                'Analista de Riesgo Operativo',
                'Analista de Riesgo Financiero',
                'Auxiliar de Riesgos',
            ],

            'Administración' => [
                'Gerente Administrativo',
                'Jefe Administrativo',
                'Coordinador Administrativo',
                'Analista Administrativo',
                'Asistente Administrativo',
                'Encargado de Compras',
                'Encargado de Activos Fijos',
                'Encargado de Servicios Generales',
            ],

            'Contact Center' => [
                'Jefe de Contact Center',
                'Supervisor de Contact Center',
                'Coordinador de Contact Center',
                'Operador de Contact Center',
                'Agente de Seguimiento',
                'Asesor Telefónico',
            ],

            'Cobros' => [
                'Jefe de Cobros',
                'Coordinador de Cobros',
                'Analista de Cobros',
                'Gestor de Cobranza',
                'Asesor de Recuperación de Cartera',
            ],

            'Archivo y Digitalización' => [
                'Coordinador de Archivo',
                'Encargado de Archivo',
                'Analista Documental',
                'Operador de Digitalización',
                'Auxiliar de Archivo',
            ],
        ];

        foreach ($puestos as $nombreUnidad => $nombresPuestos) {
            $unidad = Unidad::query()
                ->where('nombre', $nombreUnidad)
                ->firstOrFail();

            foreach ($nombresPuestos as $nombrePuesto) {
                Puesto::updateOrCreate(
                    [
                        'unidad_id' => $unidad->id,
                        'nombre' => $nombrePuesto,
                    ],
                    [
                        'estado' => true,
                    ]
                );
            }
        }

        $this->command->info(
            'Puestos organizacionales configurados correctamente.'
        );
    }
}
