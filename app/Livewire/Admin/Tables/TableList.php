<?php

namespace App\Livewire\Admin\Tables;

use App\Models\Table;
use App\Models\Area;
use Livewire\Component;
use Livewire\WithPagination;

class TableList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'number';
    public string $sortDirection = 'asc';
    public string $filterArea = '';
    public string $filterStatus = '';
    public bool $showDeleteModal = false;
    public ?int $tableToDelete = null;

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

    public function confirmDelete(int $tableId): void
    {
        $this->tableToDelete = $tableId;
        $this->showDeleteModal = true;
    }

    public function deleteTable(): void
    {
        if ($this->tableToDelete) {
            Table::find($this->tableToDelete)->delete();
            session()->flash('flash.banner', 'Mesa eliminada correctamente.');
            session()->flash('flash.bannerStyle', 'success');
        }
        $this->showDeleteModal = false;
        $this->tableToDelete = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->tableToDelete = null;
    }

    public function render()
    {
        $areas = Area::orderBy('name')->get();

        $query = Table::with('area')
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

        $tables = $query->paginate(10);
        $stats = Table::getStats();

        return view('livewire.admin.tables.table-list', compact('tables', 'areas', 'stats'));
    }
}
