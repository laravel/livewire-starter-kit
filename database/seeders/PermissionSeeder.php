<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permisos para gestión de usuarios
        $userPermissions = [
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
        ];

        // Permisos para gestión de roles
        $rolePermissions = [
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
        ];

        // Permisos para gestión de permisos
        $permissionPermissions = [
            'view-permissions',
            'create-permissions',
            'edit-permissions',
            'delete-permissions',
        ];

        // Permisos para áreas
        $areaPermissions = [
            'view-areas',
            'create-areas',
            'edit-areas',
            'delete-areas',
        ];

        // Permisos para departamentos
        $departmentPermissions = [
            'view-departments',
            'create-departments',
            'edit-departments',
            'delete-departments',
        ];

        // Permisos para reportes
        $reportPermissions = [
            'view-reports',
            'create-reports',
            'edit-reports',
            'delete-reports',
            'export-reports',
        ];

        // Permisos para configuración
        $settingsPermissions = [
            'view-settings',
            'edit-settings',
        ];

        // Permisos para dashboard
        $dashboardPermissions = [
            'view-dashboard',
            'view-analytics',
        ];

        // Combinar todos los permisos
        $allPermissions = array_merge(
            $userPermissions,
            $rolePermissions,
            $permissionPermissions,
            $areaPermissions,
            $departmentPermissions,
            $reportPermissions,
            $settingsPermissions,
            $dashboardPermissions
        );

        // Crear permisos
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info('Permisos creados correctamente.');
    }
}