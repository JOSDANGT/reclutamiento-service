<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'Admin1234';

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Crear el rol si no existe
        $rol = Role::findOrCreate('ADMIN', 'web');

        // Asignar todos los permisos registrados
        $permisos = Permission::where('guard_name', 'web')->get();

        $rol->syncPermissions($permisos);

        // Buscar o crear el usuario
        $user = User::firstOrCreate(
            [
                'usuario_dominio' => 'superadmin',
            ],
            [
                'primer_nombre' => 'Super',
                'primer_apellido' => 'Administrador',
                'email' => 'superadmin@talenthub.local',
                'password' => Hash::make($password),
                'estado' => true,
            ]
        );

        // Asignar rol
        $user->assignRole($rol);

        $this->command->info(
            'Usuario SUPERADMIN configurado correctamente.'
        );
    }
}
