<?php

namespace App\Livewire\Admin\Prices;

use App\Models\Price;
use App\Models\Part;
use Livewire\Component;

class PriceEdit extends Component
{
    public Price $price;
    public string $part_id = '';
    public string $unit_price = '';
    public string $tier_1_999 = '';
    public string $tier_1000_10999 = '';
    public string $tier_11000_99999 = '';
    public string $tier_100000_plus = '';
    public string $effective_date = '';
    public bool $active = true;
    public string $comments = '';

    public function mount(Price $price): void
    {
        $this->price = $price;
        $this->part_id = (string) $price->part_id;
        $this->unit_price = (string) $price->unit_price;
        $this->tier_1_999 = $price->tier_1_999 !== null ? (string) $price->tier_1_999 : '';
        $this->tier_1000_10999 = $price->tier_1000_10999 !== null ? (string) $price->tier_1000_10999 : '';
        $this->tier_11000_99999 = $price->tier_11000_99999 !== null ? (string) $price->tier_11000_99999 : '';
        $this->tier_100000_plus = $price->tier_100000_plus !== null ? (string) $price->tier_100000_plus : '';
        $this->effective_date = $price->effective_date->format('Y-m-d');
        $this->active = $price->active;
        $this->comments = $price->comments ?? '';
    }

    protected function rules(): array
    {
        return [
            'part_id' => 'required|exists:parts,id',
            'unit_price' => 'required|numeric|min:0',
            'tier_1_999' => 'nullable|numeric|min:0',
            'tier_1000_10999' => 'nullable|numeric|min:0',
            'tier_11000_99999' => 'nullable|numeric|min:0',
            'tier_100000_plus' => 'nullable|numeric|min:0',
            'effective_date' => 'required|date',
            'active' => 'boolean',
            'comments' => 'nullable|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'part_id.required' => 'Debe seleccionar una parte.',
            'part_id.exists' => 'La parte seleccionada no es válida.',
            'unit_price.required' => 'El precio unitario es obligatorio.',
            'unit_price.numeric' => 'El precio unitario debe ser un número.',
            'unit_price.min' => 'El precio unitario debe ser mayor o igual a 0.',
            'effective_date.required' => 'La fecha efectiva es obligatoria.',
            'effective_date.date' => 'La fecha efectiva debe ser una fecha válida.',
        ];
    }

    public function updatePrice(): void
    {
        $this->validate();

        $this->price->update([
            'part_id' => $this->part_id,
            'unit_price' => $this->unit_price,
            'tier_1_999' => $this->tier_1_999 !== '' ? $this->tier_1_999 : null,
            'tier_1000_10999' => $this->tier_1000_10999 !== '' ? $this->tier_1000_10999 : null,
            'tier_11000_99999' => $this->tier_11000_99999 !== '' ? $this->tier_11000_99999 : null,
            'tier_100000_plus' => $this->tier_100000_plus !== '' ? $this->tier_100000_plus : null,
            'effective_date' => $this->effective_date,
            'active' => $this->active,
            'comments' => $this->comments,
        ]);

        session()->flash('flash.banner', 'Precio actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.prices.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.prices.price-edit', [
            'parts' => Part::active()->orderBy('number')->get(),
        ]);
    }
}
