<?php

namespace App\Livewire\Admin\Machines;

use App\Models\Machine;
use App\Models\Area;
use Livewire\Component;
use Livewire\WithPagination;

class MachineList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public string $filterArea = '';
    public string $filterStatus = '';
    public bool $showDeleteModal = false;
    public ?int $machineToDelete = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterArea(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function confirmDelete(int $machineId): void
    {
        $this->machineToDelete = $machineId;
        $this->showDeleteModal = true;
    }

    public function deleteMachine(): void
    {
        if ($this->machineToDelete) {
            Machine::find($this->machineToDelete)->delete();
            session()->flash('flash.banner', 'Máquina eliminada correctamente.');
            session()->flash('flash.bannerStyle', 'success');
        }
        $this->showDeleteModal = false;
        $this->machineToDelete = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->machineToDelete = null;
    }

    public function render()
    {
        $areas = Area::orderBy('name')->get();

        $query = Machine::with('area')
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->filterArea, function ($query) {
                $query->byArea($this->filterArea);
            })
            ->when($this->filterStatus !== '', function ($query) {
                if ($this->filterStatus === '1') {
                    $query->active();
                } else {
                    $query->inactive();
                }
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        $machines = $query->paginate(10);
        $stats = Machine::getStats();

        return view('livewire.admin.machines.machine-list', compact('machines', 'areas', 'stats'));
    }
}
