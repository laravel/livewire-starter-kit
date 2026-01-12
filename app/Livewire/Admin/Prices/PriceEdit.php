<?php

namespace App\Livewire\Admin\Prices;

use App\Models\Price;
use App\Models\Part;
use Livewire\Component;

class PriceEdit extends Component
{
    public Price $price;
    public string $part_id = '';
    public string $sample_price = '';
    public string $workstation_type = 'table';
    public array $tier_prices = [];
    public string $effective_date = '';
    public bool $active = true;
    public string $comments = '';

    public function mount(Price $price): void
    {
        $this->price = $price->load('tiers');
        $this->part_id = (string) $price->part_id;
        $this->sample_price = (string) $price->sample_price;
        $this->workstation_type = $price->workstation_type ?? 'table';
        $this->tier_prices = $price->tiers_array;
        $this->effective_date = $price->effective_date->format('Y-m-d');
        $this->active = $price->active;
        $this->comments = $price->comments ?? '';
    }

    public function updatedWorkstationType(): void
    {
        // Reinicializar tiers cuando cambia el tipo
        $config = Price::getTierConfigForType($this->workstation_type);
        $this->tier_prices = array_fill(0, count($config), '');
    }

    protected function rules(): array
    {
        return [
            'part_id' => 'required|exists:parts,id',
            'sample_price' => 'required|numeric|min:0',
            'workstation_type' => 'required|in:table,machine,semi_automatic',
            'effective_date' => 'required|date',
            'active' => 'boolean',
            'comments' => 'nullable|string',
            'tier_prices' => 'array',
            'tier_prices.*' => 'nullable|numeric|min:0',
        ];
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

    public function updatePrice(): void
    {
        $this->validate();

        $this->price->update([
            'part_id' => $this->part_id,
            'sample_price' => $this->sample_price,
            'workstation_type' => $this->workstation_type,
            'effective_date' => $this->effective_date,
            'active' => $this->active,
            'comments' => $this->comments,
        ]);

        // Sincronizar los tiers
        $this->price->syncTiers($this->tier_prices);

        session()->flash('flash.banner', 'Precio actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.prices.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.prices.price-edit', [
            'parts' => Part::active()->orderBy('number')->get(),
            'workstationTypes' => Price::WORKSTATION_TYPES,
            'tierConfig' => Price::getTierConfigForType($this->workstation_type),
        ]);
    }
}
