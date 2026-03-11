<?php

namespace App\Livewire\Admin\Shifts;

use App\Models\Shift;
use Livewire\Component;
use Livewire\WithPagination;

class ShiftList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 10;

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

    public function deleteShift(int $id): void
    {
        $shift = Shift::findOrFail($id);
        if (!$shift->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar este turno porque tiene empleados, sesiones de producción o descansos asociados.');
            return;
        }
        $shift->delete();
        session()->flash('flash.banner', 'Turno eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');
    }

    public function render()
    {
        $shifts = Shift::withCount('employees')
            ->search($this->search)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $totalShifts = Shift::count();
        $activeShifts = Shift::where('active', true)->count();
        $employeesAssigned = (int) \App\Models\User::role('employee')->whereNotNull('shift_id')->active()->count();

        return view('livewire.admin.shifts.shift-list', [
            'shifts' => $shifts,
            'totalShifts' => $totalShifts,
            'activeShifts' => $activeShifts,
            'employeesAssigned' => $employeesAssigned,
        ]);
    }
}
