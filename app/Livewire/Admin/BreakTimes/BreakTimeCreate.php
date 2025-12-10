<?php

namespace App\Livewire\Admin\BreakTimes;

use App\Models\BreakTime;
use App\Models\Shift;
use Livewire\Component;

class BreakTimeCreate extends Component
{
    public string $name = '';
    public string $start_break_time = '';
    public string $end_break_time = '';
    public string $shift_id = '';
    public bool $active = true;
    public string $comments = '';

    public function mount(): void
    {
        // Si viene un shift_id por query string, lo pre-seleccionamos
        if (request()->has('shift_id')) {
            $this->shift_id = request('shift_id');
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:break_times,name',
            'start_break_time' => 'required|date_format:H:i',
            'end_break_time' => 'required|date_format:H:i',
            'shift_id' => 'required|exists:shifts,id',
            'active' => 'boolean',
            'comments' => 'nullable|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique' => 'Ya existe un descanso con este nombre.',
            'start_break_time.required' => 'La hora de inicio es obligatoria.',
            'start_break_time.date_format' => 'La hora de inicio debe tener formato HH:MM.',
            'end_break_time.required' => 'La hora de fin es obligatoria.',
            'end_break_time.date_format' => 'La hora de fin debe tener formato HH:MM.',
            'shift_id.required' => 'Debe seleccionar un turno.',
            'shift_id.exists' => 'El turno seleccionado no es válido.',
        ];
    }

    public function saveBreakTime(): void
    {
        $this->validate();

        BreakTime::create([
            'name' => $this->name,
            'start_break_time' => $this->start_break_time,
            'end_break_time' => $this->end_break_time,
            'shift_id' => $this->shift_id,
            'active' => $this->active,
            'comments' => $this->comments,
        ]);

        session()->flash('flash.banner', 'Descanso creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.break-times.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.break-times.break-time-create', [
            'shifts' => Shift::orderBy('name')->get(),
        ]);
    }
}
