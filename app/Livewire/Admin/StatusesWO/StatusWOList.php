<?php

namespace App\Livewire\Admin\StatusesWO;

use App\Models\StatusWO;
use Livewire\Component;
use Livewire\WithPagination;

class StatusWOList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 10;

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

    public function deleteStatus(int $id): void
    {
        $status = StatusWO::findOrFail($id);
        if (!$status->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar este estado porque tiene órdenes de trabajo asociadas.');
            return;
        }
        $status->delete();
        session()->flash('flash.banner', 'Estado eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');
    }

    public function render()
    {
        $statuses = StatusWO::search($this->search)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $totalStatuses = StatusWO::count();

        return view('livewire.admin.statuses-wo.status-wo-list', [
            'statuses' => $statuses,
            'totalStatuses' => $totalStatuses,
        ]);
    }
}
