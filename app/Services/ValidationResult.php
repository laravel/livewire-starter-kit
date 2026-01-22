<?php

namespace App\Services;

use App\Models\Price;

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
