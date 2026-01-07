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
        $permissions = [
            // Dashboard
            'view-dashboard',
            'view-analytics',
            
            // Users
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            
            // Roles
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            
            // Permissions
            'view-permissions',
            'create-permissions',
            'edit-permissions',
            'delete-permissions',
            
            // Departments
            'view-departments',
            'create-departments',
            'edit-departments',
            'delete-departments',
            
            // Areas
            'view-areas',
            'create-areas',
            'edit-areas',
            'delete-areas',
            
            // Shifts
            'view-shifts',
            'create-shifts',
            'edit-shifts',
            'delete-shifts',
            
            // Break Times
            'view-break-times',
            'create-break-times',
            'edit-break-times',
            'delete-break-times',
            
            // Holidays
            'view-holidays',
            'create-holidays',
            'edit-holidays',
            'delete-holidays',
            
            // Parts
            'view-parts',
            'create-parts',
            'edit-parts',
            'delete-parts',
            
            // Prices
            'view-prices',
            'create-prices',
            'edit-prices',
            'delete-prices',
            
            // Purchase Orders
            'view-purchase-orders',
            'create-purchase-orders',
            'edit-purchase-orders',
            'delete-purchase-orders',
            'approve-purchase-orders',
            
            // Work Orders
            'view-work-orders',
            'create-work-orders',
            'edit-work-orders',
            'delete-work-orders',
            
            // Standards
            'view-standards',
            'create-standards',
            'edit-standards',
            'delete-standards',
            
            // Production Statuses
            'view-production-statuses',
            'create-production-statuses',
            'edit-production-statuses',
            'delete-production-statuses',
            
            // Tables (Mesas)
            'view-tables',
            'create-tables',
            'edit-tables',
            'delete-tables',
            
            // Semi-Automatics
            'view-semi-automatics',
            'create-semi-automatics',
            'edit-semi-automatics',
            'delete-semi-automatics',
            
            // Machines
            'view-machines',
            'create-machines',
            'edit-machines',
            'delete-machines',
            
            // Over Times
            'view-over-times',
            'create-over-times',
            'edit-over-times',
            'delete-over-times',
            
            // Capacity Calculator/Wizard
            'view-capacity',
            'use-capacity-wizard',
            
            // Sent Lists
            'view-sent-lists',
            'create-sent-lists',
            'edit-sent-lists',
            'delete-sent-lists',
            
            // Kits
            'view-kits',
            'create-kits',
            'edit-kits',
            'delete-kits',
            
            // Lots
            'view-lots',
            'create-lots',
            'edit-lots',
            'delete-lots',
            
            // Employees
            'view-employees',
            'create-employees',
            'edit-employees',
            'delete-employees',
            
            // Reports
            'view-reports',
            'create-reports',
            'export-reports',
            
            // Settings
            'view-settings',
            'edit-settings',
        ];

        // Crear permisos
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info('Permisos creados correctamente: ' . count($permissions) . ' permisos.');
    }
}
