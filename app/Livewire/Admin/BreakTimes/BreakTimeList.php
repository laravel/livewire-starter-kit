<?php

namespace App\Livewire\Admin\BreakTimes;

use App\Models\BreakTime;
use App\Models\Shift;
use Livewire\Component;
use Livewire\WithPagination;

class BreakTimeList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 10;
    public string $filterActive = 'all';
    public string $filterShift = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function deleteBreakTime(int $id): void
    {
        $breakTime = BreakTime::findOrFail($id);
        if (!$breakTime->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar este descanso.');
            return;
        }
        $breakTime->delete();
        session()->flash('flash.banner', 'Descanso eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');
    }

    public function render()
    {
        $query = BreakTime::with('shift')->search($this->search);

        if ($this->filterActive === 'active') {
            $query->active();
        } elseif ($this->filterActive === 'inactive') {
            $query->inactive();
        }

        if ($this->filterShift !== 'all') {
            $query->where('shift_id', $this->filterShift);
        }

        $breakTimes = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $shifts = Shift::orderBy('name')->get();
        $totalBreakTimes = BreakTime::count();
        $activeBreakTimes = BreakTime::active()->count();
        $shiftsWithBreaks = Shift::has('BreakTimes')->count();

        return view('livewire.admin.break-times.break-time-list', [
            'breakTimes' => $breakTimes,
            'shifts' => $shifts,
            'totalBreakTimes' => $totalBreakTimes,
            'activeBreakTimes' => $activeBreakTimes,
            'shiftsWithBreaks' => $shiftsWithBreaks,
        ]);
    }
}
