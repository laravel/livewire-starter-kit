<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MaterialsRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for Materials area
        $permissions = [
            'view_materials_area',
            'manage_lots',
            'manage_kits',
            'submit_to_quality',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Materials role
        $materialsRole = Role::firstOrCreate(['name' => 'Materials']);

        // Assign all permissions to Materials role
        $materialsRole->syncPermissions($permissions);

        $this->command->info('Materials role and permissions created successfully.');

        // Create Quality role permissions if they don't exist
        $qualityPermissions = [
            'view_quality_area',
            'approve_kits',
            'reject_kits',
            'create_kit_incidents',
        ];

        foreach ($qualityPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Quality role
        $qualityRole = Role::firstOrCreate(['name' => 'Quality']);

        // Assign permissions to Quality role
        $qualityRole->syncPermissions($qualityPermissions);

        $this->command->info('Quality role and permissions created successfully.');
    }
}
