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

        $permisos = [
            // Vacantes
            'Ver vacantes',
            'Crear vacantes',
            'Editar vacantes',
            'Eliminar vacantes',
            'Cambiar estado vacantes',

            // Requisitos
            'Ver requisitos',
            'Crear requisitos',
            'Editar requisitos',
            'Eliminar requisitos',

            // Tipos de requisitos
            'Ver tipos de requisitos',
            'Crear tipos de requisitos',
            'Editar tipos de requisitos',
            'Eliminar tipos de requisitos',

            // Postulaciones
            'Ver postulaciones',
            'Crear postulaciones',
            'Gestionar postulaciones',

            // Usuarios
            'Ver usuarios',
            'Crear usuarios',
            'Editar usuarios',
            'Eliminar usuarios',

            // Roles y permisos
            'Ver roles',
            'Crear roles',
            'Editar roles',
            'Eliminar roles',
            'Asignar roles',
            'Asignar permisos',

            // Unidades
            'Ver unidades',
            'Crear unidades',
            'Editar unidades',
        ];

        $permisosCandidatos = [
            'Crear perfil candidato',
            'Ver perfil candidato',
            'Editar perfil candidato',
            'Ver candidatos',
        ];

        $permisosHabilidades = [
            'Ver habilidades',
            'Crear habilidades',
            'Editar habilidades',
            'Eliminar habilidades',
            'Crear categorías habilidades',
            'Editar categorías habilidades',
            'Gestionar habilidades candidato',
        ];

        $permisosExperiencias = [
            'Gestionar experiencias laborales',
        ];

        $permisosFormaciones = [
            'Gestionar formaciones académicas',
        ];

        // Registrar todos los permisos
        $todosLosPermisos = array_unique(array_merge(
            $permisos,
            $permisosCandidatos,
            $permisosHabilidades,
            $permisosExperiencias,
            $permisosFormaciones,
        ));

        foreach ($todosLosPermisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        // Crear roles
        $admin = Role::findOrCreate('ADMIN', 'web');
        $reclutador = Role::findOrCreate('RECLUTADOR', 'web');
        $candidato = Role::findOrCreate('CANDIDATO', 'web');

        // ADMIN: todos los permisos
        $admin->syncPermissions($todosLosPermisos);

        // RECLUTADOR
        $reclutador->syncPermissions([
            'Ver vacantes',
            'Crear vacantes',
            'Editar vacantes',
            'Cambiar estado vacantes',

            'Ver requisitos',
            'Crear requisitos',
            'Editar requisitos',

            'Ver tipos de requisitos',
            'Crear tipos de requisitos',
            'Editar tipos de requisitos',

            'Ver postulaciones',
            'Gestionar postulaciones',

            'Ver candidatos',
            'Ver habilidades',
        ]);

        // CANDIDATO
        $candidato->syncPermissions([
            'Ver vacantes',
            'Ver requisitos',
            'Ver tipos de requisitos',

            'Crear postulaciones',

            'Crear perfil candidato',
            'Ver perfil candidato',
            'Editar perfil candidato',

            'Ver habilidades',
            'Gestionar habilidades candidato',

            'Gestionar experiencias laborales',

            'Gestionar formaciones académicas',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
