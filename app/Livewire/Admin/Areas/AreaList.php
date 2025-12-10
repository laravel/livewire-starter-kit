<?php

namespace App\Livewire\Admin\Areas;

use App\Models\Area;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class AreaList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 10;
    public ?int $deleteId = null;
    public bool $confirmingDeletion = false;
    public string $departmentFilter = '';

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
        $area = Area::findOrFail($id);
        
        if (!$area->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar esta área porque tiene equipos asociados.');
            return;
        }

        $this->deleteId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete(): void
    {
        $area = Area::findOrFail($this->deleteId);
        
        if (!$area->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar esta área porque tiene equipos asociados.');
            $this->confirmingDeletion = false;
            return;
        }
        
        $area->delete();
        
        session()->flash('flash.banner', 'Área eliminada correctamente.');
        session()->flash('flash.bannerStyle', 'success');
        
        $this->confirmingDeletion = false;
    }

    public function render()
    {
        $areasQuery = Area::search($this->search);
        
        if (!empty($this->departmentFilter)) {
            $areasQuery->byDepartment($this->departmentFilter);
        }
        
        $areas = $areasQuery->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
        
        return view('livewire.admin.areas.area-list', [
            'areas' => $areas,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }
}
