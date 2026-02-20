<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /**
     * Permissions organized by area.
     * Format: 'area.permission-name'
     * This prefix is used to group them in the UI.
     */
    public static function groupedPermissions(): array
    {
        return [
            // ── ADMINISTRACIÓN GENERAL ──
            'admin' => [
                'admin.view-dashboard',
                'admin.view-analytics',
                'admin.view-settings',
                'admin.edit-settings',
                'admin.view-reports',
                'admin.create-reports',
                'admin.export-reports',
            ],

            // ── USUARIOS & SEGURIDAD ──
            'usuarios' => [
                'usuarios.view-users',
                'usuarios.create-users',
                'usuarios.edit-users',
                'usuarios.delete-users',
                'usuarios.view-roles',
                'usuarios.create-roles',
                'usuarios.edit-roles',
                'usuarios.delete-roles',
                'usuarios.view-permissions',
                'usuarios.create-permissions',
                'usuarios.edit-permissions',
                'usuarios.delete-permissions',
                'usuarios.view-employees',
                'usuarios.create-employees',
                'usuarios.edit-employees',
                'usuarios.delete-employees',
            ],

            // ── CATÁLOGOS ──
            'catalogos' => [
                'catalogos.view-departments',
                'catalogos.create-departments',
                'catalogos.edit-departments',
                'catalogos.delete-departments',
                'catalogos.view-areas',
                'catalogos.create-areas',
                'catalogos.edit-areas',
                'catalogos.delete-areas',
                'catalogos.view-shifts',
                'catalogos.create-shifts',
                'catalogos.edit-shifts',
                'catalogos.delete-shifts',
                'catalogos.view-break-times',
                'catalogos.create-break-times',
                'catalogos.edit-break-times',
                'catalogos.delete-break-times',
                'catalogos.view-holidays',
                'catalogos.create-holidays',
                'catalogos.edit-holidays',
                'catalogos.delete-holidays',
                'catalogos.view-parts',
                'catalogos.create-parts',
                'catalogos.edit-parts',
                'catalogos.delete-parts',
                'catalogos.view-prices',
                'catalogos.create-prices',
                'catalogos.edit-prices',
                'catalogos.delete-prices',
                'catalogos.view-production-statuses',
                'catalogos.create-production-statuses',
                'catalogos.edit-production-statuses',
                'catalogos.delete-production-statuses',
                'catalogos.view-statuses-wo',
                'catalogos.create-statuses-wo',
                'catalogos.edit-statuses-wo',
                'catalogos.delete-statuses-wo',
            ],

            // ── ÓRDENES (PO / WO) ──
            'ordenes' => [
                'ordenes.view-purchase-orders',
                'ordenes.create-purchase-orders',
                'ordenes.edit-purchase-orders',
                'ordenes.delete-purchase-orders',
                'ordenes.approve-purchase-orders',
                'ordenes.view-work-orders',
                'ordenes.create-work-orders',
                'ordenes.edit-work-orders',
                'ordenes.delete-work-orders',
                'ordenes.view-sent-lists',
                'ordenes.create-sent-lists',
                'ordenes.edit-sent-lists',
                'ordenes.delete-sent-lists',
            ],

            // ── PRODUCCIÓN ──
            'produccion' => [
                'produccion.view-dashboard',
                'produccion.view-weighings',
                'produccion.create-weighings',
                'produccion.edit-weighings',
                'produccion.delete-weighings',
                'produccion.view-tables',
                'produccion.create-tables',
                'produccion.edit-tables',
                'produccion.delete-tables',
                'produccion.view-semi-automatics',
                'produccion.create-semi-automatics',
                'produccion.edit-semi-automatics',
                'produccion.delete-semi-automatics',
                'produccion.view-machines',
                'produccion.create-machines',
                'produccion.edit-machines',
                'produccion.delete-machines',
                'produccion.view-standards',
                'produccion.create-standards',
                'produccion.edit-standards',
                'produccion.delete-standards',
                'produccion.view-over-times',
                'produccion.create-over-times',
                'produccion.edit-over-times',
                'produccion.delete-over-times',
                'produccion.view-capacity',
                'produccion.use-capacity-wizard',
            ],

            // ── CALIDAD ──
            'calidad' => [
                'calidad.view-dashboard',
                'calidad.view-inspection',
                'calidad.approve-inspection',
                'calidad.reject-inspection',
                'calidad.view-weighings',
                'calidad.create-weighings',
                'calidad.edit-weighings',
                'calidad.delete-weighings',
                'calidad.approve-kits',
                'calidad.reject-kits',
            ],

            // ── MATERIALES ──
            'materiales' => [
                'materiales.view-dashboard',
                'materiales.view-manage',
                'materiales.manage-lots',
                'materiales.manage-kits',
                'materiales.submit-to-quality',
            ],
        ];
    }

    public function run(): void
    {
        $allPermissions = [];

        foreach (self::groupedPermissions() as $group => $permissions) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web',
                ]);
                $allPermissions[] = $permission;
            }
        }

        $this->command->info('Permisos creados correctamente: ' . count($allPermissions) . ' permisos (organizados en ' . count(self::groupedPermissions()) . ' grupos).');
    }
}
