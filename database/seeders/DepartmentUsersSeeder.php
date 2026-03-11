<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DepartmentUsersSeeder extends Seeder
{
    /**
     * Crea un usuario de prueba por cada rol de departamento.
     * Ejecutar después de PermissionSeeder y RoleSeeder.
     */
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Admin Sistema',
                'email'    => 'admin@flexcon.com',
                'account'  => 'admin',
                'role'     => 'admin',
            ],
            [
                'name'     => 'Usuario Materiales',
                'email'    => 'materiales@flexcon.com',
                'account'  => 'materiales',
                'role'     => 'Materiales',
            ],
            [
                'name'     => 'Usuario Producción',
                'email'    => 'produccion@flexcon.com',
                'account'  => 'produccion',
                'role'     => 'Produccion',
            ],
            [
                'name'     => 'Usuario Calidad',
                'email'    => 'calidad@flexcon.com',
                'account'  => 'calidad',
                'role'     => 'Calidad',
            ],
            [
                'name'     => 'Usuario Empaques',
                'email'    => 'empaques@flexcon.com',
                'account'  => 'empaques',
                'role'     => 'Empaques',
            ],
        ];

        foreach ($users as $data) {
            // Ensure role exists
            Role::firstOrCreate(['name' => $data['role']]);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'account'  => $data['account'],
                    'password' => Hash::make('password'),
                    'active'   => true,
                ]
            );

            // Remove all roles and assign the correct one
            $user->syncRoles([$data['role']]);

            $this->command->info("Usuario [{$data['role']}]: {$data['email']} / password");
        }
    }
}
