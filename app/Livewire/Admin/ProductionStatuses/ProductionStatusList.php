<?php

namespace App\Livewire\Admin\ProductionStatuses;

use App\Models\ProductionStatus;
use Livewire\Component;
use Livewire\WithPagination;

class ProductionStatusList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'order';
    public string $sortDirection = 'asc';
    public string $filterStatus = '';
    public bool $showDeleteModal = false;
    public ?int $statusToDelete = null;

    public function updatingSearch(): void
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

    public function confirmDelete(int $statusId): void
    {
        $status = ProductionStatus::find($statusId);

        if ($status && !$status->canBeDeleted()) {
            session()->flash('flash.banner', 'No se puede eliminar este estado porque está siendo utilizado.');
            session()->flash('flash.bannerStyle', 'danger');
            return;
        }

        $this->statusToDelete = $statusId;
        $this->showDeleteModal = true;
    }

    public function deleteStatus(): void
    {
        if ($this->statusToDelete) {
            ProductionStatus::find($this->statusToDelete)->delete();
            session()->flash('flash.banner', 'Estado de producción eliminado correctamente.');
            session()->flash('flash.bannerStyle', 'success');
        }
        $this->showDeleteModal = false;
        $this->statusToDelete = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->statusToDelete = null;
    }

    public function render()
    {
        $query = ProductionStatus::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatus !== '', function ($query) {
                if ($this->filterStatus === '1') {
                    $query->active();
                } else {
                    $query->where('active', false);
                }
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        $productionStatuses = $query->paginate(10);

        $stats = [
            'total' => ProductionStatus::count(),
            'active' => ProductionStatus::where('active', true)->count(),
            'inactive' => ProductionStatus::where('active', false)->count(),
        ];

        return view('livewire.admin.production-statuses.production-status-list', compact('productionStatuses', 'stats'));
    }
}
