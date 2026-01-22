<?php

namespace App\Services;

use App\Models\Price;
use Illuminate\Support\Facades\Log;

class PriceValidationService
{
    /**
     * Valida que un precio pueda ser activado
     * 
     * @param Price $price
     * @return ValidationResult
     */
    public function validateActivation(Price $price): ValidationResult
    {
        if (!$price->active) {
            return new ValidationResult(
                isValid: true,
                errors: [],
                conflictingPrice: null
            );
        }

        try {
            $conflicting = Price::where('part_id', $price->part_id)
                ->where('workstation_type', $price->workstation_type)
                ->where('active', true)
                ->where('id', '!=', $price->id ?? 0)
                ->first();

            if ($conflicting) {
                $typeLabel = Price::WORKSTATION_TYPES[$price->workstation_type] ?? $price->workstation_type;
                return new ValidationResult(
                    isValid: false,
                    errors: ["Ya existe un precio activo para esta parte con tipo de estación {$typeLabel}. Por favor, desactive el precio existente primero."],
                    conflictingPrice: $conflicting
                );
            }

            return new ValidationResult(
                isValid: true,
                errors: [],
                conflictingPrice: null
            );

        } catch (\Exception $e) {
            Log::error('Error validating price activation', [
                'price_id' => $price->id,
                'part_id' => $price->part_id,
                'workstation_type' => $price->workstation_type,
                'error' => $e->getMessage()
            ]);

            return new ValidationResult(
                isValid: false,
                errors: ["Error al validar el precio: " . $e->getMessage()],
                conflictingPrice: null
            );
        }
    }

    /**
     * Desactiva precios conflictivos antes de activar uno nuevo
     * 
     * @param Price $newPrice
     * @return int Cantidad de precios desactivados
     */
    public function deactivateConflicting(Price $newPrice): int
    {
        if (!$newPrice->active) {
            return 0;
        }

        try {
            $deactivated = Price::where('part_id', $newPrice->part_id)
                ->where('workstation_type', $newPrice->workstation_type)
                ->where('active', true)
                ->where('id', '!=', $newPrice->id ?? 0)
                ->update(['active' => false]);

            if ($deactivated > 0) {
                Log::info('Precios conflictivos desactivados automáticamente', [
                    'new_price_id' => $newPrice->id,
                    'part_id' => $newPrice->part_id,
                    'workstation_type' => $newPrice->workstation_type,
                    'deactivated_count' => $deactivated
                ]);
            }

            return $deactivated;

        } catch (\Exception $e) {
            Log::error('Error desactivando precios conflictivos', [
                'new_price_id' => $newPrice->id,
                'part_id' => $newPrice->part_id,
                'workstation_type' => $newPrice->workstation_type,
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }

    /**
     * Valida la unicidad de un precio
     * 
     * @param int $partId
     * @param string $workstationType
     * @param bool $isActive
     * @param int|null $excludePriceId
     * @return bool
     */
    public function isUnique(
        int $partId,
        string $workstationType,
        bool $isActive,
        ?int $excludePriceId = null
    ): bool {
        if (!$isActive) {
            return true; // Los precios inactivos no necesitan validación de unicidad
        }

        $query = Price::where('part_id', $partId)
            ->where('workstation_type', $workstationType)
            ->where('active', true);

        if ($excludePriceId !== null) {
            $query->where('id', '!=', $excludePriceId);
        }

        return !$query->exists();
    }
}
