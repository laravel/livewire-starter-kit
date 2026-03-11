<?php

namespace App\Livewire\Admin\Prices;

use App\Models\Price;
use App\Models\Part;
use Livewire\Component;
use Livewire\WithPagination;

class PriceList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'effective_date';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public string $filterActive = 'all';
    public string $filterPart = 'all';

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

    public function deletePrice(int $id): void
    {
        $price = Price::findOrFail($id);
        if (!$price->canBeDeleted()) {
            session()->flash('error', 'No se puede eliminar este precio.');
            return;
        }
        $price->delete();
        session()->flash('flash.banner', 'Precio eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');
    }

    public function render()
    {
        $query = Price::with(['part', 'tiers'])->search($this->search);

        if ($this->filterActive === 'active') {
            $query->active();
        } elseif ($this->filterActive === 'inactive') {
            $query->inactive();
        }

        if ($this->filterPart !== 'all') {
            $query->where('part_id', $this->filterPart);
        }

        $prices = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $parts = Part::active()->orderBy('number')->get();

        $totalPrices = Price::count();
        $activePrices = Price::active()->count();
        $partsWithPrice = Price::distinct('part_id')->count('part_id');

        return view('livewire.admin.prices.price-list', [
            'prices' => $prices,
            'parts' => $parts,
            'totalPrices' => $totalPrices,
            'activePrices' => $activePrices,
            'partsWithPrice' => $partsWithPrice,
        ]);
    }
}
