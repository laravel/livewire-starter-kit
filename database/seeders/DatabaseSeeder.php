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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'account' => 'test',
            'password' => Hash::make('password'),
        ]);

        // Call the seeders in order
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            StatusWOSeeder::class,
            TableSeeder::class,              // Optional: Creates test table data
            Semi_AutomaticSeeder::class,     // Creates semi-automatic tables
            MachineSeeder::class,            // Creates machines
            WorkOrderTestSeeder::class,
            //StandardSeeder::class,
        ]);
    }
}
