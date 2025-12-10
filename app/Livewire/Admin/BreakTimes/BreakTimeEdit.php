<?php

namespace App\Livewire\Admin\BreakTimes;

use App\Models\BreakTime;
use App\Models\Shift;
use Carbon\Carbon;
use Livewire\Component;

class BreakTimeEdit extends Component
{
    public BreakTime $breakTime;
    public string $name = '';
    public string $start_break_time = '';
    public string $end_break_time = '';
    public string $shift_id = '';
    public bool $active = true;
    public string $comments = '';

    public function mount(BreakTime $breakTime): void
    {
        $this->breakTime = $breakTime;
        $this->name = $breakTime->name;
        $this->start_break_time = Carbon::parse($breakTime->start_break_time)->format('H:i');
        $this->end_break_time = Carbon::parse($breakTime->end_break_time)->format('H:i');
        $this->shift_id = (string) $breakTime->shift_id;
        $this->active = $breakTime->active;
        $this->comments = $breakTime->comments ?? '';
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:break_times,name,' . $this->breakTime->id,
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

    public function updateBreakTime(): void
    {
        $this->validate();

        $this->breakTime->update([
            'name' => $this->name,
            'start_break_time' => $this->start_break_time,
            'end_break_time' => $this->end_break_time,
            'shift_id' => $this->shift_id,
            'active' => $this->active,
            'comments' => $this->comments,
        ]);

        session()->flash('flash.banner', 'Descanso actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.break-times.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.break-times.break-time-edit', [
            'shifts' => Shift::orderBy('name')->get(),
        ]);
    }
}
