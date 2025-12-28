<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates standard shift configurations for production planning.
     * These are initial/default shifts that can be modified by administrators.
     */
    public function run(): void
    {
        // Clear existing shifts if running in fresh environment
        if (app()->environment('local')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Shift::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $shifts = [
            [
                'name' => 'First Shift (Morning)',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'active' => 1,
                'comments' => 'Standard morning shift - 8 hours',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Second Shift (Afternoon)',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'active' => 1,
                'comments' => 'Standard afternoon shift - 8 hours',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Third Shift (Night)',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'active' => 1,
                'comments' => 'Standard night shift - 8 hours (crosses midnight)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Day Shift (Standard)',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'active' => 1,
                'comments' => 'Alternative day shift - 8 hours',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(
                ['name' => $shift['name']], // Unique identifier
                $shift // All attributes
            );
        }

        $this->command->info('Shifts seeded successfully!');
        $this->command->info('Total shifts created: ' . Shift::count());
    }
}
