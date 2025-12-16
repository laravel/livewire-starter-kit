<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\Standard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StandardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only create standards if we have the required data
        if (Part::count() === 0) {
            $this->command->warn('Skipping StandardSeeder: Missing required data (Parts)');
            return;
        }

        Standard::factory()->count(10)->create();
        $this->command->info('Standards created successfully!');
    }
}
