<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Gestión administrativa de vacantes.
        $permisosVacantes = [
            'Ver vacantes',
            'Crear vacantes',
            'Editar vacantes',
            'Eliminar vacantes',
            'Cambiar estado vacantes',
        ];

        // Portal de oportunidades laborales.
        $permisosOportunidades = [
            'Ver oportunidades laborales',
        ];

        // Catálogo de requisitos.
        $permisosRequisitos = [
            'Ver requisitos',
            'Crear requisitos',
            'Editar requisitos',
            'Eliminar requisitos',
        ];

        // Catálogo de tipos de requisitos.
        $permisosTiposRequisitos = [
            'Ver tipos de requisitos',
            'Crear tipos de requisitos',
            'Editar tipos de requisitos',
            'Eliminar tipos de requisitos',
        ];

        // Gestión administrativa de postulaciones.
        $permisosPostulaciones = [
            'Ver postulaciones',
            'Gestionar postulaciones',
            'Evaluar requisitos postulaciones',
        ];

        // Acciones propias del candidato.
        $permisosMisPostulaciones = [
            'Crear postulaciones',
            'Ver mis postulaciones',
            'Retirar mis postulaciones',
        ];

        // Administración de usuarios.
        $permisosUsuarios = [
            'Ver usuarios',
            'Crear usuarios',
            'Editar usuarios',
            'Eliminar usuarios',
        ];

        // Administración de roles y permisos.
        $permisosRoles = [
            'Ver roles',
            'Crear roles',
            'Editar roles',
            'Eliminar roles',
            'Asignar roles',
            'Asignar permisos',
        ];

        // Unidades organizacionales.
        $permisosUnidades = [
            'Ver unidades',
            'Crear unidades',
            'Editar unidades',
        ];

        // Perfiles de candidatos.
        $permisosCandidatos = [
            'Crear perfil candidato',
            'Ver perfil candidato',
            'Editar perfil candidato',
            'Ver candidatos',
        ];

        // Catálogo y gestión de habilidades.
        $permisosHabilidades = [
            'Ver habilidades',
            'Crear habilidades',
            'Editar habilidades',
            'Eliminar habilidades',
            'Crear categorías habilidades',
            'Editar categorías habilidades',
            'Gestionar habilidades candidato',
        ];

        // Experiencias laborales.
        $permisosExperiencias = [
            'Gestionar experiencias laborales',
        ];

        // Formación académica.
        $permisosFormaciones = [
            'Gestionar formaciones académicas',
        ];

        // Registrar todos los permisos.
        $todosLosPermisos = array_values(array_unique(array_merge(
            $permisosVacantes,
            $permisosOportunidades,
            $permisosRequisitos,
            $permisosTiposRequisitos,
            $permisosPostulaciones,
            $permisosMisPostulaciones,
            $permisosUsuarios,
            $permisosRoles,
            $permisosUnidades,
            $permisosCandidatos,
            $permisosHabilidades,
            $permisosExperiencias,
            $permisosFormaciones,
        )));

        foreach ($todosLosPermisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        // Crear roles.
        $admin = Role::findOrCreate('ADMIN', 'web');
        $reclutador = Role::findOrCreate('RECLUTADOR', 'web');
        $candidato = Role::findOrCreate('CANDIDATO', 'web');

        // ADMIN: acceso a todos los permisos.
        $admin->syncPermissions($todosLosPermisos);

        // RECLUTADOR: administración del reclutamiento.
        $reclutador->syncPermissions([
            // Vacantes.
            'Ver vacantes',
            'Crear vacantes',
            'Editar vacantes',
            'Cambiar estado vacantes',

            // Oportunidades.
            'Ver oportunidades laborales',

            // Requisitos.
            'Ver requisitos',
            'Crear requisitos',
            'Editar requisitos',

            // Tipos de requisitos.
            'Ver tipos de requisitos',
            'Crear tipos de requisitos',
            'Editar tipos de requisitos',

            // Postulaciones.
            'Ver postulaciones',
            'Gestionar postulaciones',
            'Evaluar requisitos postulaciones',

            // Candidatos.
            'Ver candidatos',

            // Habilidades.
            'Ver habilidades',
        ]);

        // CANDIDATO: acceso a su portal personal.
        $candidato->syncPermissions([
            // Consulta de vacantes publicadas.
            'Ver oportunidades laborales',

            // Postulaciones propias.
            'Crear postulaciones',
            'Ver mis postulaciones',
            'Retirar mis postulaciones',

            // Perfil propio.
            'Crear perfil candidato',
            'Ver perfil candidato',
            'Editar perfil candidato',

            // Habilidades propias.
            'Ver habilidades',
            'Gestionar habilidades candidato',

            // Experiencias laborales.
            'Gestionar experiencias laborales',

            // Formación académica.
            'Gestionar formaciones académicas',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
