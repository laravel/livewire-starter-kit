<?php

namespace App\Livewire\Admin\Holidays;

use App\Models\Holiday;
use Livewire\Component;

class HolidayEdit extends Component
{
    public Holiday $holiday;
    public string $name = '';
    public string $date = '';
    public string $description = '';

    public function mount(Holiday $holiday): void
    {
        $this->holiday = $holiday;
        $this->name = $holiday->name;
        $this->date = $holiday->date;
        $this->description = $holiday->description ?? '';
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ];
    }

    public function updateHoliday(): void
    {
        $this->validate();

        $this->holiday->update([
            'name' => $this->name,
            'date' => $this->date,
            'description' => $this->description,
        ]);

        session()->flash('flash.banner', 'Holiday actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.holidays.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.holidays.holiday-edit');
    }
}
