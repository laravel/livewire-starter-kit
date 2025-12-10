<?php

namespace App\Livewire\Admin\Departments;

use App\Models\Department;
use Livewire\Component;

class DepartmentEdit extends Component
{
    public Department $department;
    public string $name = '';
    public string $description = '';
    public string $comments = '';

    public function mount(Department $department): void
    {
        $this->department = $department;
        $this->name = $department->name;
        $this->description = $department->description ?? '';
        $this->comments = $department->comments ?? '';
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:departments,name,' . $this->department->id,
            'description' => 'nullable|string',
            'comments' => 'nullable|string|max:255',
        ];
    }

    public function updateDepartment(): void
    {
        $this->validate();

        $this->department->update([
            'name' => $this->name,
            'description' => $this->description,
            'comments' => $this->comments,
        ]);

        session()->flash('flash.banner', 'Departamento actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.departments.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.departments.department-edit');
    }
}
