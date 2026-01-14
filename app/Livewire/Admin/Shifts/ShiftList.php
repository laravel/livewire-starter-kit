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
    public ?int $deleteId = null;
    public bool $confirmingDeletion = false;

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
        $shift = Shift::findOrFail($id);

        if (!$shift->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar este turno porque tiene empleados, sesiones de producción o descansos asociados.');
            return;
        }

        $this->deleteId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete(): void
    {
        $shift = Shift::findOrFail($this->deleteId);

        if (!$shift->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar este turno porque tiene empleados, sesiones de producción o descansos asociados.');
            $this->confirmingDeletion = false;
            return;
        }

        $shift->delete();

        session()->flash('flash.banner', 'Turno eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->confirmingDeletion = false;
    }

    public function render()
    {
        $shifts = Shift::with([
                        'employees' => function ($query) {
                            $query->select('id', 'name', 'last_name', 'shift_id', 'active');
                        }
                    ])
                    ->search($this->search)
                    ->orderBy($this->sortField, $this->sortDirection)
                    ->paginate($this->perPage);

        return view('livewire.admin.shifts.shift-list', [
            'shifts' => $shifts,
        ]);
    }
}
