<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verify required dependencies exist
        if (\App\Models\Area::count() === 0) {
            $this->command->warn('Skipping MachineSeeder: Missing required data (Areas)');
            return;
        }

        // Create machines using factory
        \App\Models\Machine::factory()->count(8)->create();

        $this->command->info('Machines created successfully!');
    }
}
