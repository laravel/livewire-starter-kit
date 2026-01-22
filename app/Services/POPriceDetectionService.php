<?php

namespace App\Services;

use App\Models\Price;
use App\Models\PurchaseOrder;
use App\Models\Standard;

class POPriceDetectionService
{
    /**
     * Mapeo de assembly mode a workstation type
     */
    private const ASSEMBLY_MODE_MAP = [
        'manual' => Price::WORKSTATION_TABLE,
        'semi_automatic' => Price::WORKSTATION_SEMI_AUTOMATIC,
        'machine' => Price::WORKSTATION_MACHINE,
    ];

    /**
     * Detecta el precio correcto para un PO basado en el Standard
     * 
     * @param PurchaseOrder $po
     * @return PriceDetectionResult
     */
    public function detectPrice(PurchaseOrder $po): PriceDetectionResult
    {
        // Verificar que el PO tenga un part_id
        if (!$po->part_id) {
            return new PriceDetectionResult(
                price: null,
                workstationType: '',
                found: false,
                error: 'El Purchase Order no tiene una parte asociada'
            );
        }

        // Cargar la relación part si no está cargada
        if (!$po->relationLoaded('part')) {
            $po->load('part');
        }

        // Obtener el Standard activo para la parte
        $standard = $po->part->standards()->active()->first();

        if (!$standard) {
            return new PriceDetectionResult(
                price: null,
                workstationType: '',
                found: false,
                error: 'No hay Standard activo para esta parte'
            );
        }

        // Obtener el workstation_type del Standard
        $workstationType = $this->getWorkstationTypeFromStandard($standard);

        if (!$workstationType) {
            return new PriceDetectionResult(
                price: null,
                workstationType: '',
                found: false,
                error: 'El Standard no tiene un tipo de estación de trabajo definido'
            );
        }

        // Buscar el precio activo para el workstation_type
        $price = $po->part->activePriceForWorkstationType($workstationType);

        if (!$price) {
            $typeLabel = Price::WORKSTATION_TYPES[$workstationType] ?? $workstationType;
            return new PriceDetectionResult(
                price: null,
                workstationType: $workstationType,
                found: false,
                error: "No se encontró un precio activo para el tipo de estación {$typeLabel}"
            );
        }

        return new PriceDetectionResult(
            price: $price,
            workstationType: $workstationType,
            found: true,
            error: null
        );
    }

    /**
     * Detecta el precio correcto basado en part_id y quantity
     * Útil para formularios donde aún no existe el PO
     * 
     * @param int $partId
     * @param int $quantity
     * @return PriceDetectionResult
     */
    public function detectPriceForPart(int $partId, int $quantity): PriceDetectionResult
    {
        $part = \App\Models\Part::find($partId);

        if (!$part) {
            return new PriceDetectionResult(
                price: null,
                workstationType: '',
                found: false,
                error: 'La parte no existe'
            );
        }

        // Obtener el Standard activo para la parte
        $standard = $part->standards()->active()->first();

        if (!$standard) {
            return new PriceDetectionResult(
                price: null,
                workstationType: '',
                found: false,
                error: 'No hay Standard activo para esta parte'
            );
        }

        // Obtener el workstation_type del Standard
        $workstationType = $this->getWorkstationTypeFromStandard($standard);

        if (!$workstationType) {
            return new PriceDetectionResult(
                price: null,
                workstationType: '',
                found: false,
                error: 'El Standard no tiene un tipo de estación de trabajo definido'
            );
        }

        // Buscar el precio activo para el workstation_type
        $price = $part->activePriceForWorkstationType($workstationType);

        if (!$price) {
            $typeLabel = Price::WORKSTATION_TYPES[$workstationType] ?? $workstationType;
            return new PriceDetectionResult(
                price: null,
                workstationType: $workstationType,
                found: false,
                error: "No se encontró un precio activo para el tipo de estación {$typeLabel}"
            );
        }

        return new PriceDetectionResult(
            price: $price,
            workstationType: $workstationType,
            found: true,
            error: null
        );
    }

    /**
     * Valida que el precio del PO sea correcto antes de aprobar
     * 
     * @param PurchaseOrder $po
     * @return ValidationResult
     */
    public function validatePOPrice(PurchaseOrder $po): ValidationResult
    {
        $detection = $this->detectPrice($po);

        if (!$detection->found) {
            return new ValidationResult(
                isValid: false,
                errors: [$detection->error],
                conflictingPrice: null
            );
        }

        // Comparar el precio del PO con el precio detectado
        $expectedPrice = $detection->price->sample_price;
        $actualPrice = $po->unit_price;

        // Usar comparación con tolerancia para decimales
        if (abs($expectedPrice - $actualPrice) > 0.0001) {
            $typeLabel = Price::WORKSTATION_TYPES[$detection->workstationType] ?? $detection->workstationType;
            return new ValidationResult(
                isValid: false,
                errors: [
                    "El precio del PO ({$actualPrice}) no coincide con el precio activo ({$expectedPrice}) para el tipo de estación {$typeLabel}"
                ],
                conflictingPrice: $detection->price
            );
        }

        return new ValidationResult(
            isValid: true,
            errors: [],
            conflictingPrice: null
        );
    }

    /**
     * Obtiene el workstation_type del Standard asociado al Part
     * 
     * @param Standard $standard
     * @return string|null
     */
    private function getWorkstationTypeFromStandard(Standard $standard): ?string
    {
        $assemblyMode = $standard->getAssemblyMode();

        if (!$assemblyMode) {
            return null;
        }

        return $this->mapAssemblyModeToWorkstationType($assemblyMode);
    }

    /**
     * Mapea assembly_mode a workstation_type
     * 
     * @param string $assemblyMode
     * @return string|null
     */
    private function mapAssemblyModeToWorkstationType(string $assemblyMode): ?string
    {
        return self::ASSEMBLY_MODE_MAP[$assemblyMode] ?? null;
    }
}

/**
 * Resultado de la detección de precio
 */
class PriceDetectionResult
{
    public function __construct(
        public ?Price $price,
        public string $workstationType,
        public bool $found,
        public ?string $error
    ) {}
}

/**
 * Resultado de validación
 */
class ValidationResult
{
    public function __construct(
        public bool $isValid,
        public array $errors,
        public ?Price $conflictingPrice
    ) {}
}
