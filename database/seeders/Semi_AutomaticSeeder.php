<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Semi_AutomaticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verify required dependencies exist
        if (\App\Models\Area::count() === 0) {
            $this->command->warn('Skipping Semi_AutomaticSeeder: Missing required data (Areas)');
            return;
        }

        // Create semi-automatic tables using factory
        \App\Models\Semi_Automatic::factory()->count(10)->create();

        $this->command->info('Semi-automatic tables created successfully!');
    }
}
