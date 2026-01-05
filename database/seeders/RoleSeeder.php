<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            'admin',     // Cambiado a minúsculas para consistencia
            'HR',
            'Maintenance',
            'Production',
            'Shipping',
            'Warehouse',
            'Materials',
            'employee',  // Rol para empleados (panel de empleado)
        ];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            
            // Asignar permisos según el rol
            switch ($roleName) {
                case 'admin':
                    // Admin tiene todos los permisos
                    $role->syncPermissions(Permission::all());
                    break;
                    
                case 'HR':
                    // HR puede gestionar usuarios y ver reportes
                    $role->syncPermissions([
                        'view-dashboard',
                        'view-users',
                        'create-users',
                        'edit-users',
                        'delete-users',
                        'view-roles',
                        'view-departments',
                        'view-areas',
                        'view-reports',
                        'create-reports',
                        'export-reports',
                    ]);
                    break;
                    
                case 'Maintenance':
                case 'Production':
                case 'Shipping':
                case 'Warehouse':
                case 'Materials':
                    // Roles operativos tienen permisos básicos
                    $role->syncPermissions([
                        'view-dashboard',
                        'view-users',
                        'view-reports',
                    ]);
                    break;
                    
                case 'employee':
                    // Empleados solo pueden ver su dashboard
                    $role->syncPermissions([
                        'view-dashboard',
                    ]);
                    break;
            }
        }

        // Log the created roles
        $this->command->info('Roles y permisos asignados correctamente!');
    }
}
