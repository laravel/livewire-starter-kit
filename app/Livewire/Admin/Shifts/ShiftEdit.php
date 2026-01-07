<?php

namespace App\Livewire\Admin\Shifts;

use App\Models\Shift;
use Livewire\Component;

class ShiftEdit extends Component
{
    public Shift $shift;
    public string $name = '';
    public string $start_time = '';
    public string $end_time = '';
    public bool $active = true;
    public string $comments = '';

    public function mount(Shift $shift): void
    {
        $this->shift = $shift;
        $this->name = $shift->name;
        $this->start_time = $shift->start_time ? \Carbon\Carbon::parse($shift->start_time)->format('H:i') : '';
        $this->end_time = $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('H:i') : '';
        $this->active = $shift->active;
        $this->comments = $shift->comments ?? '';
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:shifts,name,' . $this->shift->id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'active' => 'boolean',
            'comments' => 'nullable|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'start_time.required' => 'La hora de inicio es requerida.',
            'start_time.date_format' => 'El formato de hora debe ser HH:MM.',
            'end_time.required' => 'La hora de fin es requerida.',
            'end_time.date_format' => 'El formato de hora debe ser HH:MM.',
        ];
    }

    public function updateShift(): void
    {
        $this->validate();

        $this->shift->update([
            'name' => $this->name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'active' => $this->active,
            'comments' => $this->comments,
        ]);

        session()->flash('flash.banner', 'Turno actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.shifts.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.shifts.shift-edit');
    }
}
