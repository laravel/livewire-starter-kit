<?php

namespace App\Livewire\Admin\Prices;

use App\Models\Price;
use App\Models\Part;
use Livewire\Component;

class PriceCreate extends Component
{
    public string $part_id = '';
    public string $sample_price = '';
    public string $workstation_type = 'table';
    public array $tier_prices = [];
    public string $effective_date = '';
    public bool $active = true;
    public string $comments = '';

    public function mount(): void
    {
        $this->effective_date = now()->format('Y-m-d');
        $this->initializeTierPrices();
        
        if (request()->has('part_id')) {
            $this->part_id = request('part_id');
        }
    }

    public function updatedWorkstationType(): void
    {
        $this->initializeTierPrices();
    }

    protected function initializeTierPrices(): void
    {
        $config = Price::getTierConfigForType($this->workstation_type);
        $this->tier_prices = array_fill(0, count($config), '');
    }

    protected function rules(): array
    {
        $rules = [
            'part_id' => 'required|exists:parts,id',
            'sample_price' => 'required|numeric|min:0',
            'workstation_type' => 'required|in:table,machine,semi_automatic',
            'effective_date' => 'required|date',
            'active' => 'boolean',
            'comments' => 'nullable|string',
            'tier_prices' => 'array',
            'tier_prices.*' => 'nullable|numeric|min:0',
        ];

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'part_id.required' => 'Debe seleccionar una parte.',
            'part_id.exists' => 'La parte seleccionada no es válida.',
            'sample_price.required' => 'El precio de muestra es obligatorio.',
            'sample_price.numeric' => 'El precio de muestra debe ser un número.',
            'sample_price.min' => 'El precio de muestra debe ser mayor o igual a 0.',
            'workstation_type.required' => 'Debe seleccionar un tipo de estación de trabajo.',
            'workstation_type.in' => 'El tipo de estación de trabajo no es válido.',
            'effective_date.required' => 'La fecha efectiva es obligatoria.',
            'effective_date.date' => 'La fecha efectiva debe ser una fecha válida.',
        ];
    }

    public function savePrice(): void
    {
        $this->validate();

        $price = Price::create([
            'part_id' => $this->part_id,
            'sample_price' => $this->sample_price,
            'workstation_type' => $this->workstation_type,
            'effective_date' => $this->effective_date,
            'active' => $this->active,
            'comments' => $this->comments,
        ]);

        // Sincronizar los tiers
        $price->syncTiers($this->tier_prices);

        session()->flash('flash.banner', 'Precio creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.prices.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.prices.price-create', [
            'parts' => Part::active()->orderBy('number')->get(),
            'workstationTypes' => Price::WORKSTATION_TYPES,
            'tierConfig' => Price::getTierConfigForType($this->workstation_type),
        ]);
    }
}
