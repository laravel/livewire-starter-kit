<?php

namespace Database\Seeders;

use App\Models\StatusWO;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusWOSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Open',
                'color' => '#3B82F6', // Blue
                'comments' => 'Work order is open and ready to be processed',
            ],
            [
                'name' => 'In Progress',
                'color' => '#F59E0B', // Amber
                'comments' => 'Work order is currently being processed',
            ],
            [
                'name' => 'Completed',
                'color' => '#10B981', // Green
                'comments' => 'Work order has been completed successfully',
            ],
            [
                'name' => 'Cancelled',
                'color' => '#EF4444', // Red
                'comments' => 'Work order has been cancelled',
            ],
            [
                'name' => 'On Hold',
                'color' => '#6B7280', // Gray
                'comments' => 'Work order is on hold pending further action',
            ],
        ];

        foreach ($statuses as $status) {
            StatusWO::firstOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
}
