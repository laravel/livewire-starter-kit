<?php

namespace App\Livewire\Admin\Parts;

use App\Models\Part;
use App\Models\Price;
use Livewire\Component;

class PartShow extends Component
{
    public Part $part;
    public array $pricesByType = [];

    public function mount(Part $part): void
    {
        $this->part = $part;
        $this->loadPricesByType();
    }

    private function loadPricesByType(): void
    {
        $prices = $this->part->prices()->with('tiers')->get();
        
        // Inicializar array con todos los tipos
        foreach (Price::WORKSTATION_TYPES as $type => $label) {
            $this->pricesByType[$type] = [
                'label' => $label,
                'prices' => $prices
                    ->where('workstation_type', $type)
                    ->sortByDesc('effective_date')
                    ->values()
                    ->all()
            ];
        }
    }

    public function render()
    {
        return view('livewire.admin.parts.part-show');
    }
}
