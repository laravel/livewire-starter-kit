<?php

namespace App\Livewire\Admin\Employees;

use Livewire\Component;
use App\Models\User;

class EmployeeShow extends Component
{
    public User $employee;

    public function mount(User $employee)
    {
        $this->employee = $employee->load(['area', 'shift']);
    }

    public function render()
    {
        return view('livewire.admin.employees.employee-show');
    }
}
