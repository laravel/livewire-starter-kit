<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verify required dependencies exist
        if (\App\Models\Area::count() === 0) {
            $this->command->warn('Skipping TableSeeder: Missing required data (Areas)');
            return;
        }

        if (\App\Models\ProductionStatus::count() === 0) {
            $this->command->warn('Skipping TableSeeder: Missing required data (Production Statuses)');
            return;
        }

        // Create tables using factory
        \App\Models\Table::factory()->count(15)->create();

        $this->command->info('Tables created successfully!');
    }
}
