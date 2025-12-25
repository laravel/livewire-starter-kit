<?php

namespace App\Http\Requests;

use App\Rules\AfterTimeOrNextDay;
use Illuminate\Foundation\Http\FormRequest;

class StoreOverTimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajustar según políticas de autorización
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'shift_id' => [
                'required',
                'integer',
                'exists:shifts,id',
            ],
            'date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                new AfterTimeOrNextDay($this->start_time ?? '00:00', 1),
            ],
            'break_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:480',
            ],
            'employees_qty' => [
                'required',
                'integer',
                'min:1',
                'max:1000',
            ],
            'comments' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del overtime es requerido.',
            'shift_id.required' => 'Debe seleccionar un turno.',
            'shift_id.exists' => 'El turno seleccionado no existe.',
            'date.required' => 'La fecha es requerida.',
            'date.after_or_equal' => 'No puede programar overtimes en el pasado.',
            'start_time.required' => 'La hora de inicio es requerida.',
            'end_time.required' => 'La hora de fin es requerida.',
            'break_minutes.min' => 'Los minutos de descanso no pueden ser negativos.',
            'break_minutes.max' => 'Los minutos de descanso no pueden exceder 8 horas.',
            'employees_qty.required' => 'Debe especificar la cantidad de empleados.',
            'employees_qty.min' => 'Debe haber al menos 1 empleado.',
            'employees_qty.max' => 'La cantidad de empleados no puede exceder 1000.',
        ];
    }
}
