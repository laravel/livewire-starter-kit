<?php

namespace App\Livewire\Admin\SemiAutomatics;

use App\Models\Semi_Automatic;
use App\Models\Area;
use Livewire\Component;
use Livewire\WithPagination;

class SemiAutomaticList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'number';
    public string $sortDirection = 'asc';
    public string $filterArea = '';
    public string $filterStatus = '';
    public bool $showDeleteModal = false;
    public ?int $semiAutomaticToDelete = null;

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

    public function confirmDelete(int $semiAutomaticId): void
    {
        $this->semiAutomaticToDelete = $semiAutomaticId;
        $this->showDeleteModal = true;
    }

    public function deleteSemiAutomatic(): void
    {
        if ($this->semiAutomaticToDelete) {
            Semi_Automatic::find($this->semiAutomaticToDelete)->delete();
            session()->flash('flash.banner', 'Semi-automático eliminado correctamente.');
            session()->flash('flash.bannerStyle', 'success');
        }
        $this->showDeleteModal = false;
        $this->semiAutomaticToDelete = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->semiAutomaticToDelete = null;
    }

    public function render()
    {
        $areas = Area::orderBy('name')->get();

        $query = Semi_Automatic::with('area')
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

        $semiAutomatics = $query->paginate(10);
        $stats = Semi_Automatic::getStats();

        return view('livewire.admin.semi-automatics.semi-automatic-list', compact('semiAutomatics', 'areas', 'stats'));
    }
}
