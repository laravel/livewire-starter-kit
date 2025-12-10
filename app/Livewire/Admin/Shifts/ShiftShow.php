<?php

namespace App\Livewire\Admin\Shifts;

use App\Models\Shift;
use Livewire\Component;

class ShiftShow extends Component
{
    public Shift $shift;

    public function mount(Shift $shift): void
    {
        $this->shift = $shift;
    }

    public function render()
    {
        return view('livewire.admin.shifts.shift-show');
    }
}
