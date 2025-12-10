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
        $department = Department::findOrFail($id);
        
        if (!$department->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar este departamento porque tiene áreas asociadas.');
            return;
        }

        $this->deleteId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete(): void
    {
        $department = Department::findOrFail($this->deleteId);
        
        if (!$department->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar este departamento porque tiene áreas asociadas.');
            $this->confirmingDeletion = false;
            return;
        }
        
        $department->delete();
        
        session()->flash('flash.banner', 'Departamento eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');
        
        $this->confirmingDeletion = false;
    }

    public function render()
    {
        $departments = Department::search($this->search)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.departments.department-list', [
            'departments' => $departments,
        ]);
    }
}
