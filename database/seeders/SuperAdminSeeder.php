<?php

namespace Database\Seeders;

use App\Models\Puesto;
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
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Crear o recuperar los roles
        $admin = Role::findOrCreate('ADMIN', 'web');
        Role::findOrCreate('RECLUTADOR', 'web');

        // Asignar todos los permisos al administrador
        $permisos = Permission::where('guard_name', 'web')->get();

        $admin->syncPermissions($permisos);

        // Usuarios internos de TalentHub
        $usuarios = [
            [
                'usuario_dominio' => 'superadmin',
                'primer_nombre' => 'Super',
                'primer_apellido' => 'Administrador',
                'email' => 'superadmin@talenthub.local',
                'puesto' => null,
                'rol' => 'ADMIN',
            ],
            [
                'usuario_dominio' => 'gerenterh',
                'primer_nombre' => 'Carlos',
                'segundo_nombre' => 'Eduardo',
                'primer_apellido' => 'Morales',
                'segundo_apellido' => 'López',
                'email' => 'gerenterh@talenthub.local',
                'puesto' => 'Gerente de Recursos Humanos',
                'rol' => 'ADMIN',
            ],
            [
                'usuario_dominio' => 'coordinadorrh',
                'primer_nombre' => 'María',
                'segundo_nombre' => 'Fernanda',
                'primer_apellido' => 'González',
                'segundo_apellido' => 'Pérez',
                'email' => 'coordinadorrh@talenthub.local',
                'puesto' => 'Coordinador de Reclutamiento y Selección',
                'rol' => 'RECLUTADOR',
            ],
            [
                'usuario_dominio' => 'analistareclutamiento',
                'primer_nombre' => 'José',
                'segundo_nombre' => 'Daniel',
                'primer_apellido' => 'Ramírez',
                'segundo_apellido' => 'García',
                'email' => 'analistareclutamiento@talenthub.local',
                'puesto' => 'Analista de Reclutamiento',
                'rol' => 'RECLUTADOR',
            ],
            [
                'usuario_dominio' => 'especialistaseleccion',
                'primer_nombre' => 'Ana',
                'segundo_nombre' => 'Lucía',
                'primer_apellido' => 'Martínez',
                'segundo_apellido' => 'Castillo',
                'email' => 'especialistaseleccion@talenthub.local',
                'puesto' => 'Especialista de Selección de Personal',
                'rol' => 'RECLUTADOR',
            ],
        ];

        foreach ($usuarios as $datos) {
            $rol = $datos['rol'];
            $nombrePuesto = $datos['puesto'];

            unset($datos['rol'], $datos['puesto']);

            // Identificar el puesto dentro de Recursos Humanos
            $puestoId = null;

            if ($nombrePuesto !== null) {
                $puestoId = Puesto::query()
                    ->where('nombre', $nombrePuesto)
                    ->whereHas('unidad', function ($query) {
                        $query->where('nombre', 'Recursos Humanos');
                    })
                    ->firstOrFail()
                    ->id;
            }

            $datos['puesto_id'] = $puestoId;

            // Buscar o crear el usuario
            $usuario = User::firstOrNew([
                'usuario_dominio' => $datos['usuario_dominio'],
            ]);

            $usuario->fill($datos);
            $usuario->estado = true;

            // No cambiar la contraseña de usuarios existentes
            if (!$usuario->exists) {
                $usuario->password = Hash::make('Admin1234');
            }

            $usuario->save();

            // Sincronizar el rol
            $usuario->syncRoles([$rol]);

            $this->command->info(
                "Usuario {$usuario->usuario_dominio} configurado con rol {$rol}."
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info(
            'Usuarios internos de TalentHub configurados correctamente.'
        );
    }
}
