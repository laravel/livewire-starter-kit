<?php

namespace App\Livewire\Admin\Departments;

use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentList extends Component
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

    public function deleteDepartment(int $id): void
    {
        $department = Department::findOrFail($id);
        
        if (!$department->canBeDeleted()) {
            session()->flash('flash.banner', 'No se puede eliminar este departamento porque tiene áreas asociadas.');
            session()->flash('flash.bannerStyle', 'danger');
            return;
        }
        
        $department->delete();
        
        session()->flash('flash.banner', 'Departamento eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');
    }

    public function render()
    {
        $departments = Department::with('areas')
            ->search($this->search)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $totalDepartments = Department::count();
        $totalAreas = \App\Models\Area::count();

        return view('livewire.admin.departments.department-list', [
            'departments' => $departments,
            'totalDepartments' => $totalDepartments,
            'totalAreas' => $totalAreas,
        ]);
    }
}
