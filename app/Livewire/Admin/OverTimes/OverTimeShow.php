<?php

namespace App\Livewire\Admin\OverTimes;

use App\Models\OverTime;
use Livewire\Component;

class OverTimeShow extends Component
{
    public OverTime $overTime;

    public function mount(OverTime $overTime): void
    {
        $this->overTime = $overTime->load('shift');
    }

    public function render()
    {
        return view('livewire.admin.over-times.over-time-show');
    }
}
