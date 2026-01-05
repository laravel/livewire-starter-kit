<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the seeders in order (roles first!)
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            StatusWOSeeder::class,
            ShiftSeeder::class,              // Creates default shifts for production
            TableSeeder::class,              // Optional: Creates test table data
            Semi_AutomaticSeeder::class,     // Creates semi-automatic tables
            MachineSeeder::class,            // Creates machines
            WorkOrderTestSeeder::class,
            //StandardSeeder::class,
        ]);

        // Create admin user AFTER roles are created
        $adminUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'account' => 'test',
            'password' => Hash::make('password'),
        ]);
        
        // Assign admin role
        $adminUser->assignRole('Admin');
        
        $this->command->info('Admin user created: test@test.com / password');
    }
}
