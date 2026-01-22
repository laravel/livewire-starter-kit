<?php

namespace App\Services;

use App\Models\Price;

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
