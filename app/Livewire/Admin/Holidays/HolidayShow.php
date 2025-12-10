<?php

namespace App\Livewire\Admin\Holidays;

use App\Models\Holiday;
use Livewire\Component;

class HolidayShow extends Component
{
    public Holiday $holiday;

    public function mount(Holiday $holiday): void
    {
        $this->holiday = $holiday;
    }

    public function render()
    {
        return view('livewire.admin.holidays.holiday-show');
    }
}
