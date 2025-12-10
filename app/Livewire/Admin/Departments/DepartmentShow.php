<?php

namespace App\Livewire\Admin\Departments;

use App\Models\Department;
use Livewire\Component;

class DepartmentShow extends Component
{
    public Department $department;
    public array $stats = [];

    public function mount(Department $department): void
    {
        $this->department = $department;
        $this->stats = $department->getStats();
    }

    public function render()
    {
        return view('livewire.admin.departments.department-show');
    }
}
