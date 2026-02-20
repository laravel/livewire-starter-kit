<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class AreaUsersSeeder extends Seeder
{
    /**
     * Create one user per area with the correct role and permissions.
     */
    public function run(): void
    {
        // ── 1. Ensure roles exist with correct permissions ──

        // Producción role
        $produccionRole = Role::firstOrCreate(['name' => 'Produccion', 'guard_name' => 'web']);
        $produccionPerms = Permission::where('name', 'like', 'produccion.%')->pluck('name')->toArray();
        $produccionRole->syncPermissions($produccionPerms);

        // Calidad role
        $calidadRole = Role::firstOrCreate(['name' => 'Calidad', 'guard_name' => 'web']);
        $calidadPerms = Permission::where('name', 'like', 'calidad.%')->pluck('name')->toArray();
        $calidadRole->syncPermissions($calidadPerms);

        // Materiales role
        $materialesRole = Role::firstOrCreate(['name' => 'Materiales', 'guard_name' => 'web']);
        $materialesPerms = Permission::where('name', 'like', 'materiales.%')->pluck('name')->toArray();
        $materialesRole->syncPermissions($materialesPerms);

        // Admin role gets ALL permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        // ── 2. Create one user per area ──

        $users = [
            [
                'name' => 'Usuario Materiales',
                'email' => 'materiales@flexcon.com',
                'account' => 'materiales',
                'password' => Hash::make('password'),
                'role' => 'Materiales',
            ],
            [
                'name' => 'Usuario Producción',
                'email' => 'produccion@flexcon.com',
                'account' => 'produccion',
                'password' => Hash::make('password'),
                'role' => 'Produccion',
            ],
            [
                'name' => 'Usuario Calidad',
                'email' => 'calidad@flexcon.com',
                'account' => 'calidad',
                'password' => Hash::make('password'),
                'role' => 'Calidad',
            ],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Remove any existing roles and assign the correct one
            $user->syncRoles([$roleName]);

            $this->command->info("Usuario '{$user->name}' creado/actualizado con rol '{$roleName}' — {$user->email} / password");
        }

        $this->command->info('');
        $this->command->info('=== Usuarios por área creados ===');
        $this->command->info('materiales@flexcon.com / password → Dashboard + Gestión Materiales');
        $this->command->info('produccion@flexcon.com / password → Dashboard + Pesadas Producción');
        $this->command->info('calidad@flexcon.com   / password → Dashboard + Inspección + Pesadas Calidad');
    }
}
