<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class OnlyOneWorkstation implements Rule
{
    protected $otherWorkstation1;
    protected $otherWorkstation2;
    protected $fieldName;

    /**
     * Create a new rule instance.
     *
     * @param mixed $other1 Valor de la segunda estación
     * @param mixed $other2 Valor de la tercera estación
     * @param string $fieldName Nombre del campo para mensajes
     */
    public function __construct($other1, $other2, string $fieldName = 'estación')
    {
        $this->otherWorkstation1 = $other1;
        $this->otherWorkstation2 = $other2;
        $this->fieldName = $fieldName;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Si el campo actual no tiene valor, es válido
        if (!$value) {
            return true;
        }

        // Si el campo actual tiene valor, los otros dos deben ser null
        return is_null($this->otherWorkstation1) && is_null($this->otherWorkstation2);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Solo puede seleccionar UNA estación de trabajo. Por favor, deseleccione las otras opciones.';
    }
}
