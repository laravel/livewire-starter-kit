<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'title' => 'Annual Company Meeting',
                'description' => 'Join us for our annual company meeting where we will discuss our achievements and future plans.',
                'start_time' => Carbon::now()->addDays(7)->setHour(9)->setMinute(0),
                'end_time' => Carbon::now()->addDays(7)->setHour(17)->setMinute(0),
                'location' => 'Main Conference Room'
            ],
            [
                'title' => 'Team Building Workshop',
                'description' => 'A fun-filled day of team building activities and exercises to strengthen our collaboration.',
                'start_time' => Carbon::now()->addDays(14)->setHour(10)->setMinute(0),
                'end_time' => Carbon::now()->addDays(14)->setHour(16)->setMinute(0),
                'location' => 'Recreation Center'
            ],
            [
                'title' => 'Project Launch Meeting',
                'description' => 'Kickoff meeting for our new project. All team members are required to attend.',
                'start_time' => Carbon::tomorrow()->setHour(13)->setMinute(0),
                'end_time' => Carbon::tomorrow()->setHour(15)->setMinute(0),
                'location' => 'Meeting Room 2B'
            ]
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
