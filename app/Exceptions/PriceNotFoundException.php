<?php

namespace App\Exceptions;

use Exception;

class PriceNotFoundException extends Exception
{
    protected $message = 'No se encontró un precio activo para el tipo de estación especificado';
    protected $code = 404;

    public function __construct(string $workstationType = '', int $partId = 0)
    {
        if ($workstationType && $partId) {
            $this->message = "No se encontró un precio activo para el tipo de estación '{$workstationType}' en la parte ID {$partId}";
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
