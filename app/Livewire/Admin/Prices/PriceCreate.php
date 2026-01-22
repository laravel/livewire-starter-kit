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
    
    // Validación en tiempo real
    public string $validation_message = '';
    public bool $has_conflict = false;
    
    // Almacenamiento temporal de valores por tipo de estación
    protected array $savedTierValues = [
        'table' => [],
        'machine' => [],
        'semi_automatic' => [],
    ];
    
    // Guardar el tipo anterior para detectar cambios
    protected string $previousWorkstationType = 'table';

    public function mount(): void
    {
        $this->effective_date = now()->format('Y-m-d');
        $this->previousWorkstationType = $this->workstation_type;
        $this->initializeTierPrices();
        
        if (request()->has('part_id')) {
            $this->part_id = request('part_id');
            $this->checkForConflicts();
        }
        
        if (request()->has('workstation_type')) {
            $this->workstation_type = request('workstation_type');
            $this->previousWorkstationType = $this->workstation_type;
            $this->initializeTierPrices();
            $this->checkForConflicts();
        }
    }

    public function updatedPartId(): void
    {
        $this->checkForConflicts();
    }

    public function updatedWorkstationType($value): void
    {
        // Guardar los valores del tipo anterior
        if (!empty($this->tier_prices) && $this->previousWorkstationType) {
            $this->savedTierValues[$this->previousWorkstationType] = $this->tier_prices;
        }
        
        // Actualizar el tipo anterior
        $this->previousWorkstationType = $value;
        
        // Cargar los valores guardados del nuevo tipo, o inicializar vacío
        if (!empty($this->savedTierValues[$value])) {
            $this->tier_prices = $this->savedTierValues[$value];
        } else {
            $this->initializeTierPrices();
        }
        
        // Verificar conflictos con el nuevo tipo
        $this->checkForConflicts();
    }
    
    public function updatedActive(): void
    {
        $this->checkForConflicts();
    }
    
    protected function checkForConflicts(): void
    {
        $this->validation_message = '';
        $this->has_conflict = false;
        
        if (empty($this->part_id) || !$this->active) {
            return;
        }
        
        // Buscar precios activos existentes para esta parte y tipo de estación
        $existingPrice = Price::where('part_id', $this->part_id)
            ->where('workstation_type', $this->workstation_type)
            ->where('active', true)
            ->first();
        
        if ($existingPrice) {
            $this->has_conflict = true;
            $typeLabel = Price::WORKSTATION_TYPES[$this->workstation_type] ?? $this->workstation_type;
            $this->validation_message = "Ya existe un precio activo para esta parte con tipo de estación {$typeLabel}. Debes desactivar el precio existente primero o crear este precio como inactivo.";
        }
    }
    
    public function updatedTierPrices(): void
    {
        // Guardar automáticamente cuando se actualiza un tier
        $this->savedTierValues[$this->workstation_type] = $this->tier_prices;
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
        // Validar primero si hay conflictos
        if ($this->has_conflict && $this->active) {
            $this->addError('part_id', $this->validation_message);
            return;
        }
        
        $this->validate();

        try {
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
        } catch (\Exception $e) {
            $this->addError('general', 'Error al crear el precio: ' . $e->getMessage());
        }
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
