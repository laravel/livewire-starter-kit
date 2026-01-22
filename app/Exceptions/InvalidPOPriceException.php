<?php

namespace App\Exceptions;

use Exception;

class InvalidPOPriceException extends Exception
{
    protected $message = 'El precio del Purchase Order no coincide con el precio activo para el tipo de estación';
    protected $code = 422;

    public function __construct(float $poPrice = 0, float $activePrice = 0, string $workstationType = '')
    {
        if ($poPrice && $activePrice && $workstationType) {
            $this->message = "El precio del PO ({$poPrice}) no coincide con el precio activo ({$activePrice}) para el tipo de estación {$workstationType}";
        }

        parent::__construct($this->message, $this->code);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $this->message,
                'code' => $this->code
            ], $this->code);
        }

        return back()->with('error', $this->message);
    }
}
