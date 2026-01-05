<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EmployeeRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear rol de empleado si no existe
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        // Permisos básicos para empleados
        $permissions = [
            'view dashboard',
            'view own profile',
            'edit own profile',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Asignar permisos al rol
        $employeeRole->syncPermissions($permissions);

        $this->command->info('Rol de empleado creado/actualizado correctamente.');
    }
}
