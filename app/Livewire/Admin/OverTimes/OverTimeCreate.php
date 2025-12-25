<?php

namespace App\Livewire\Admin\OverTimes;

use App\Models\OverTime;
use App\Models\Shift;
use Livewire\Component;
use Carbon\Carbon;

class OverTimeCreate extends Component
{
    public string $name = '';
    public string $date = '';
    public string $shift_id = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $break_minutes = '0';
    public string $employees_qty = '1';
    public string $comments = '';

    public function mount(): void
    {
        // Set default date to today
        $this->date = now()->toDateString();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'date' => 'required|date|after_or_equal:today',
            'shift_id' => 'required|exists:shifts,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_minutes' => 'required|integer|min:0',
            'employees_qty' => 'required|integer|min:1',
            'comments' => 'nullable|string',
        ];
    }

    public function getNetHoursProperty(): float
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        try {
            $start = Carbon::createFromFormat('H:i', $this->start_time);
            $end = Carbon::createFromFormat('H:i', $this->end_time);

            // Handle overnight shifts
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $totalMinutes = $start->diffInMinutes($end);
            $breakMinutes = (int) ($this->break_minutes ?: 0);
            $netMinutes = max(0, $totalMinutes - $breakMinutes);

            return round($netMinutes / 60, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getTotalHoursProperty(): float
    {
        $netHours = $this->net_hours;
        $employees = (int) ($this->employees_qty ?: 1);

        return round($netHours * $employees, 2);
    }

    public function save()
    {
        $this->validate();

        OverTime::create([
            'name' => $this->name,
            'date' => $this->date,
            'shift_id' => $this->shift_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'break_minutes' => $this->break_minutes,
            'employees_qty' => $this->employees_qty,
            'comments' => $this->comments ?: null,
        ]);

        session()->flash('flash.banner', 'Over Time creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('admin.over-times.index');
    }

    public function render()
    {
        $shifts = Shift::active()->orderBy('name')->get();

        return view('livewire.admin.over-times.over-time-create', compact('shifts'));
    }
}
