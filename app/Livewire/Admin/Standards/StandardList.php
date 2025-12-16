<?php

namespace App\Livewire\Admin\Standards;

use App\Models\Standard;
use Livewire\Component;
use Livewire\WithPagination;

class StandardList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public ?int $deleteId = null;
    public bool $confirmingDeletion = false;
    public string $filterStatus = 'all';

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
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function confirmDeletion(int $id): void
    {
        $standard = Standard::findOrFail($id);

        if (!$standard->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar este estándar porque tiene órdenes de compra asociadas.');
            return;
        }

        $this->deleteId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete(): void
    {
        $standard = Standard::findOrFail($this->deleteId);

        if (!$standard->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar este estándar porque tiene órdenes de compra asociadas.');
            $this->confirmingDeletion = false;
            return;
        }

        $standard->delete();

        session()->flash('flash.banner', 'Estándar eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->confirmingDeletion = false;
    }

    public function toggleActive(int $id): void
    {
        $standard = Standard::findOrFail($id);
        $standard->active = !$standard->active;
        $standard->save();

        $status = $standard->active ? 'activado' : 'desactivado';
        session()->flash('flash.banner', "Estándar {$status} correctamente.");
        session()->flash('flash.bannerStyle', 'success');
    }

    public function render()
    {
        $query = Standard::with(['part', 'area', 'department'])
            ->search($this->search);

        // Apply status filter
        if ($this->filterStatus === 'active') {
            $query->active();
        } elseif ($this->filterStatus === 'inactive') {
            $query->inactive();
        }

        $standards = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.standards.standard-list', [
            'standards' => $standards,
            'stats' => Standard::getStats(),
        ]);
    }
}
