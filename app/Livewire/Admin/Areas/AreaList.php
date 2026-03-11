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

    public function deleteArea(int $id): void
    {
        $area = Area::findOrFail($id);
        
        if (!$area->canBeDeleted()) {
            session()->flash('flash.banner', 'No se puede eliminar esta área porque tiene equipos asociados.');
            session()->flash('flash.bannerStyle', 'danger');
            return;
        }
        
        $area->delete();
        
        session()->flash('flash.banner', 'Área eliminada correctamente.');
        session()->flash('flash.bannerStyle', 'success');
    }

    public function render()
    {
        $areasQuery = Area::with(['department', 'machines', 'tables', 'semiAutomatics'])
            ->search($this->search);
        
        if (!empty($this->departmentFilter)) {
            $areasQuery->byDepartment($this->departmentFilter);
        }
        
        $areas = $areasQuery->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
        
        $totalAreas = Area::count();
        $totalDepartments = Department::count();
        
        return view('livewire.admin.areas.area-list', [
            'areas' => $areas,
            'departments' => Department::orderBy('name')->get(),
            'totalAreas' => $totalAreas,
            'totalDepartments' => $totalDepartments,
        ]);
    }
}
