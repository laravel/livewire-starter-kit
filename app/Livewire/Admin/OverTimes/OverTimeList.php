<?php

namespace App\Livewire\Admin\OverTimes;

use App\Models\OverTime;
use App\Models\Shift;
use Livewire\Component;
use Livewire\WithPagination;

class OverTimeList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'date';
    public string $sortDirection = 'desc';
    public string $filterShift = '';
    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterShift(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function deleteOverTime(int $id): void
    {
        $overTime = OverTime::findOrFail($id);
        $overTime->delete();
        session()->flash('flash.banner', 'Tiempo extra eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');
    }

    public function render()
    {
        $shifts = Shift::orderBy('name')->get();

        $query = OverTime::with('shift')
            ->when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->filterShift, fn ($q) => $q->byShift($this->filterShift))
            ->orderBy($this->sortBy, $this->sortDirection);

        $overTimes = $query->paginate($this->perPage);

        // total_hours es un accessor (calculado), no una columna en BD
        $totalHours = OverTime::all()->sum(fn (OverTime $o) => $o->total_hours);

        $stats = [
            'total' => OverTime::count(),
            'total_hours' => round($totalHours, 2),
            'upcoming' => OverTime::active()->count(),
        ];

        return view('livewire.admin.over-times.over-time-list', compact('overTimes', 'shifts', 'stats'));
    }
}
