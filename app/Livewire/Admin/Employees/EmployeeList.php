<?php

namespace App\Livewire\Admin\Employees;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Area;
use App\Models\Shift;

class EmployeeList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterArea = '';
    public $filterShift = '';
    public $filterStatus = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterArea' => ['except' => ''],
        'filterShift' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterArea()
    {
        $this->resetPage();
    }

    public function updatingFilterShift()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterArea', 'filterShift', 'filterStatus']);
        $this->resetPage();
    }

    public function deleteEmployee($employeeId)
    {
        $employee = User::find($employeeId);
        
        if ($employee) {
            $employee->delete();
            session()->flash('flash.banner', 'Empleado eliminado correctamente.');
            session()->flash('flash.bannerStyle', 'success');
        } else {
            session()->flash('flash.banner', 'No se puede eliminar el empleado.');
            session()->flash('flash.bannerStyle', 'danger');
        }
    }

    public function render()
    {
        // Obtener usuarios con rol 'employee'
        $employees = User::query()
            ->role('employee')
            ->with(['area', 'shift'])
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->filterArea, fn($q) => $q->byArea($this->filterArea))
            ->when($this->filterShift, fn($q) => $q->byShift($this->filterShift))
            ->when($this->filterStatus !== '', function ($q) {
                if ($this->filterStatus === '1') {
                    return $q->active();
                } elseif ($this->filterStatus === '0') {
                    return $q->inactive();
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.employees.employee-list', [
            'employees' => $employees,
            'areas' => Area::orderBy('name')->get(),
            'shifts' => Shift::active()->orderBy('name')->get(),
        ]);
    }
}
