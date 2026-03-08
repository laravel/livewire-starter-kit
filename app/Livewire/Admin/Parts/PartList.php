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

    public function deletePart(int $id): void
    {
        $part = Part::findOrFail($id);
        if (!$part->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar esta parte porque tiene precios asociados.');
            return;
        }
        $part->delete();
        session()->flash('flash.banner', 'Parte eliminada correctamente.');
        session()->flash('flash.bannerStyle', 'success');
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

        $totalParts = Part::count();
        $activeParts = Part::active()->count();
        $withPrices = Part::has('prices')->count();

        return view('livewire.admin.parts.part-list', [
            'parts' => $parts,
            'totalParts' => $totalParts,
            'activeParts' => $activeParts,
            'withPrices' => $withPrices,
        ]);
    }
}
