<?php

namespace App\Livewire\Admin\Prices;

use App\Models\Price;
use App\Models\Part;
use Livewire\Component;

class PriceCreate extends Component
{
    public string $part_id = '';
    public string $unit_price = '';
    public string $tier_1_999 = '';
    public string $tier_1000_10999 = '';
    public string $tier_11000_99999 = '';
    public string $tier_100000_plus = '';
    public string $effective_date = '';
    public bool $active = true;
    public string $comments = '';

    public function mount(): void
    {
        $this->effective_date = now()->format('Y-m-d');
        
        if (request()->has('part_id')) {
            $this->part_id = request('part_id');
        }
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

    public function savePrice(): void
    {
        $this->validate();

        Price::create([
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

        session()->flash('flash.banner', 'Precio creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.prices.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.prices.price-create', [
            'parts' => Part::active()->orderBy('number')->get(),
        ]);
    }
}
