<?php

namespace App\Exceptions;

use Exception;

class InvalidStandardException extends Exception
{
    protected $message = 'El Standard no tiene un tipo de estación de trabajo definido';
    protected $code = 422;

    public function __construct(int $standardId = 0)
    {
        if ($standardId) {
            $this->message = "El Standard ID {$standardId} no tiene un tipo de estación de trabajo definido (work_table_id, semi_auto_work_table_id, o machine_id)";
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
