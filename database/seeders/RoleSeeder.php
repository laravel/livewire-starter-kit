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
            'Admin',
            'HR',
            'Maintenance',
            'Production',
            'Shipping',
            'Warehouse',
            'Materials'
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        // Log the created roles
        $this->command->info('Roles created successfully!');
    }
}
