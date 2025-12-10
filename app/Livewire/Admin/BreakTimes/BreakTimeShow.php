<?php

namespace App\Livewire\Admin\BreakTimes;

use App\Models\BreakTime;
use Livewire\Component;

class BreakTimeShow extends Component
{
    public BreakTime $breakTime;

    public function mount(BreakTime $breakTime): void
    {
        $this->breakTime = $breakTime->load('shift');
    }

    public function getDuration(): string
    {
        $start = \Carbon\Carbon::parse($this->breakTime->start_break_time);
        $end = \Carbon\Carbon::parse($this->breakTime->end_break_time);
        $duration = $start->diff($end);

        $hours = $duration->h;
        $minutes = $duration->i;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'min';
        }
        return $minutes . 'min';
    }

    public function render()
    {
        return view('livewire.admin.break-times.break-time-show');
    }
}
