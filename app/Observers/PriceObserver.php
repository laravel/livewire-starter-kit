<?php

namespace App\Observers;

use App\Models\Price;
use App\Services\PriceValidationService;
use Illuminate\Support\Facades\Log;

class PriceObserver
{
    protected PriceValidationService $validationService;

    public function __construct(PriceValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Handle the Price "saving" event.
     * Valida unicidad antes de guardar
     */
    public function saving(Price $price): void
    {
        // Solo validar si el precio está activo
        if (!$price->active) {
            return;
        }

        // Validar unicidad
        $result = $this->validationService->validateActivation($price);

        if (!$result->isValid) {
            Log::warning('Intento de guardar precio duplicado activo', [
                'price_id' => $price->id,
                'part_id' => $price->part_id,
                'workstation_type' => $price->workstation_type,
                'errors' => $result->errors
            ]);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'workstation_type' => $result->errors
            ]);
        }
    }

    /**
     * Handle the Price "saved" event.
     * Desactiva precios conflictivos después de guardar
     */
    public function saved(Price $price): void
    {
        // Solo desactivar conflictos si el precio guardado está activo
        if (!$price->active) {
            return;
        }

        // Desactivar precios conflictivos
        $deactivated = $this->validationService->deactivateConflicting($price);

        if ($deactivated > 0) {
            Log::info('Observer desactivó precios conflictivos', [
                'price_id' => $price->id,
                'part_id' => $price->part_id,
                'workstation_type' => $price->workstation_type,
                'deactivated_count' => $deactivated
            ]);
        }
    }

    /**
     * Handle the Price "creating" event.
     */
    public function creating(Price $price): void
    {
        Log::info('Creando nuevo precio', [
            'part_id' => $price->part_id,
            'workstation_type' => $price->workstation_type,
            'active' => $price->active,
            'sample_price' => $price->sample_price
        ]);
    }

    /**
     * Handle the Price "updating" event.
     */
    public function updating(Price $price): void
    {
        // Detectar si se está activando un precio que estaba inactivo
        if ($price->isDirty('active') && $price->active) {
            Log::info('Activando precio', [
                'price_id' => $price->id,
                'part_id' => $price->part_id,
                'workstation_type' => $price->workstation_type
            ]);
        }
    }
}
