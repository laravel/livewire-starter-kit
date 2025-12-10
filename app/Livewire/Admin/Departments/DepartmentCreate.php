<?php

namespace App\Livewire\Admin\Departments;

use App\Models\Department;
use Livewire\Component;

class DepartmentCreate extends Component
{
    public string $name = '';
    public string $description = '';
    public string $comments = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string',
            'comments' => 'nullable|string|max:255',
        ];
    }

    public function saveDepartment(): void
    {
        $this->validate();

        Department::create([
            'name' => $this->name,
            'description' => $this->description,
            'comments' => $this->comments,
        ]);

        session()->flash('flash.banner', 'Departamento creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.departments.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.departments.department-create');
    }
}
