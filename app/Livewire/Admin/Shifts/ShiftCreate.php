<?php

namespace App\Livewire\Admin\Shifts;

use App\Models\Shift;
use Livewire\Component;

class ShiftCreate extends Component
{
    public string $name = '';
    public string $start_time = '';
    public string $end_time = '';
    public bool $active = true;
    public string $comments = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:shifts,name',
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

    public function saveShift(): void
    {
        $this->validate();

        Shift::create([
            'name' => $this->name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'active' => $this->active,
            'comments' => $this->comments,
        ]);

        session()->flash('flash.banner', 'Turno creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.shifts.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.shifts.shift-create');
    }
}
