<?php

namespace App\Livewire\Admin\Parts;

use App\Models\Part;
use Livewire\Component;
use Livewire\WithPagination;

class PartList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'number';
    public string $sortDirection = 'asc';
    public int $perPage = 10;
    public ?int $deleteId = null;
    public bool $confirmingDeletion = false;
    public string $filterActive = 'all';

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

    public function confirmDeletion(int $id): void
    {
        $part = Part::findOrFail($id);

        if (!$part->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar esta parte porque tiene precios asociados.');
            return;
        }

        $this->deleteId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete(): void
    {
        $part = Part::findOrFail($this->deleteId);

        if (!$part->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar esta parte porque tiene precios asociados.');
            $this->confirmingDeletion = false;
            return;
        }

        $part->delete();

        session()->flash('flash.banner', 'Parte eliminada correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->confirmingDeletion = false;
    }

    public function render()
    {
        $query = Part::search($this->search);

        if ($this->filterActive === 'active') {
            $query->active();
        } elseif ($this->filterActive === 'inactive') {
            $query->where('active', false);
        }

        $parts = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.parts.part-list', [
            'parts' => $parts,
        ]);
    }
}
