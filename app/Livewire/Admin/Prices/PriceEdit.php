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
    
    // Validación en tiempo real
    public string $validation_message = '';
    public bool $has_conflict = false;
    public string $info_message = '';
    public bool $has_existing_prices = false;
    
    // Almacenamiento temporal de valores por tipo de estación
    protected array $savedTierValues = [
        'table' => [],
        'machine' => [],
        'semi_automatic' => [],
    ];
    
    // Guardar el tipo anterior para detectar cambios
    protected string $previousWorkstationType = '';

    public function mount(Price $price): void
    {
        $this->price = $price->load('tiers');
        $this->part_id = (string) $price->part_id;
        $this->sample_price = (string) $price->sample_price;
        $this->workstation_type = $price->workstation_type ?? 'table';
        $this->previousWorkstationType = $this->workstation_type;
        $this->tier_prices = $price->tiers_array;
        $this->effective_date = $price->effective_date->format('Y-m-d');
        $this->active = $price->active;
        $this->comments = $price->comments ?? '';
        
        // Guardar los valores iniciales en el almacenamiento temporal
        $this->savedTierValues[$this->workstation_type] = $this->tier_prices;
        
        // Verificar conflictos iniciales
        $this->checkForConflicts();
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
            // Inicializar con valores vacíos según la configuración del nuevo tipo
            $config = Price::getTierConfigForType($value);
            $this->tier_prices = array_fill(0, count($config), '');
        }
        
        // Verificar conflictos con el nuevo tipo
        $this->checkForConflicts();
    }
    
    public function updatedActive(): void
    {
        $this->checkForConflicts();
    }
    
    public function updatedTierPrices(): void
    {
        // Guardar automáticamente cuando se actualiza un tier
        $this->savedTierValues[$this->workstation_type] = $this->tier_prices;
    }
    
    protected function checkForConflicts(): void
    {
        $this->validation_message = '';
        $this->has_conflict = false;
        $this->info_message = '';
        $this->has_existing_prices = false;
        
        if (empty($this->part_id)) {
            return;
        }
        
        // Verificar si la parte tiene algún precio activo (de cualquier tipo)
        // Excluir el precio actual que se está editando
        if ($this->active) {
            $existingActivePrice = Price::where('part_id', $this->part_id)
                ->where('active', true)
                ->where('id', '!=', $this->price->id)
                ->first();
            
            if ($existingActivePrice) {
                $this->has_conflict = true;
                $typeLabel = Price::WORKSTATION_TYPES[$existingActivePrice->workstation_type] ?? $existingActivePrice->workstation_type;
                $this->validation_message = "Esta parte ya tiene otro precio activo (Tipo: {$typeLabel}). Solo puede haber un precio activo por parte. Debes desactivar el precio existente primero o guardar este precio como inactivo.";
                return;
            }
        }
        
        // Mostrar información de otros precios existentes
        $allPrices = Price::where('part_id', $this->part_id)
            ->where('id', '!=', $this->price->id)
            ->get();
        
        if ($allPrices->isNotEmpty()) {
            $this->has_existing_prices = true;
            $activePrices = $allPrices->where('active', true);
            $inactivePrices = $allPrices->where('active', false);
            
            $info = [];
            if ($activePrices->isNotEmpty()) {
                $types = $activePrices->pluck('workstation_type')->map(function($type) {
                    return Price::WORKSTATION_TYPES[$type] ?? $type;
                })->join(', ');
                $info[] = "Activos: {$types}";
            }
            if ($inactivePrices->isNotEmpty()) {
                $types = $inactivePrices->pluck('workstation_type')->map(function($type) {
                    return Price::WORKSTATION_TYPES[$type] ?? $type;
                })->join(', ');
                $info[] = "Inactivos: {$types}";
            }
            
            $this->info_message = "Esta parte tiene otros precios registrados - " . implode(' | ', $info);
        }
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
        // Validar primero si hay conflictos
        if ($this->has_conflict && $this->active) {
            $this->addError('part_id', $this->validation_message);
            return;
        }
        
        $this->validate();

        try {
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
        } catch (\Exception $e) {
            $this->addError('general', 'Error al actualizar el precio: ' . $e->getMessage());
        }
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
