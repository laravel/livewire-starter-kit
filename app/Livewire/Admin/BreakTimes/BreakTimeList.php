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
    public ?int $deleteId = null;
    public bool $confirmingDeletion = false;
    public string $filterActive = 'all'; // all, active, inactive
    public string $filterShift = 'all'; // all, or shift_id

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

    public function confirmDeletion(int $id): void
    {
        $breakTime = BreakTime::findOrFail($id);

        if (!$breakTime->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar este descanso.');
            return;
        }

        $this->deleteId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete(): void
    {
        $breakTime = BreakTime::findOrFail($this->deleteId);

        if (!$breakTime->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar este descanso.');
            $this->confirmingDeletion = false;
            return;
        }

        $breakTime->delete();

        session()->flash('flash.banner', 'Descanso eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->confirmingDeletion = false;
    }

    public function render()
    {
        $query = BreakTime::with('shift')->search($this->search);

        // Filtrar por estado activo/inactivo
        if ($this->filterActive === 'active') {
            $query->active();
        } elseif ($this->filterActive === 'inactive') {
            $query->inactive();
        }

        // Filtrar por turno
        if ($this->filterShift !== 'all') {
            $query->where('shift_id', $this->filterShift);
        }

        $breakTimes = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $shifts = Shift::orderBy('name')->get();

        return view('livewire.admin.break-times.break-time-list', [
            'breakTimes' => $breakTimes,
            'shifts' => $shifts,
        ]);
    }
}
