# Tareas Backend - Over Time Module

## Contexto
El módulo Over Time ya tiene implementada la base:
- Migración ejecutada (`over_times` table creada)
- Modelo `OverTime.php` con todas las relaciones y métodos
- Factory y Seeder funcionando
- Custom Rule `AfterTimeOrNextDay.php` para validación de horarios

## Tareas Pendientes para Backend

### 1. Form Requests

Crear los siguientes Form Requests en `app/Http/Requests/`:

#### StoreOverTimeRequest.php
```php
<?php

namespace App\Http\Requests;

use App\Rules\AfterTimeOrNextDay;
use Illuminate\Foundation\Http\FormRequest;

class StoreOverTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajustar según políticas de autorización
    }

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
```

#### UpdateOverTimeRequest.php
```php
<?php

namespace App\Http\Requests;

use App\Rules\AfterTimeOrNextDay;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOverTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
                // NO validar "after_or_equal:today" en update (permitir editar pasados)
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

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del overtime es requerido.',
            'shift_id.required' => 'Debe seleccionar un turno.',
            'shift_id.exists' => 'El turno seleccionado no existe.',
            'date.required' => 'La fecha es requerida.',
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
```

### 2. Rutas en routes/admin.php

Agregar las siguientes rutas al archivo `routes/admin.php`:

```php
// Over Time Routes
Route::prefix('over-times')->name('over-times.')->group(function () {
    Route::get('/', OverTimeList::class)->name('index');
    Route::get('/create', OverTimeCreate::class)->name('create');
    Route::get('/{overTime}', OverTimeShow::class)->name('show');
    Route::get('/{overTime}/edit', OverTimeEdit::class)->name('edit');
});
```

### 3. Verificaciones Adicionales

- Asegurarse de que las rutas estén dentro del middleware de autenticación
- Verificar que el patrón de rutas sea consistente con otros módulos del sistema
- Los componentes Livewire serán creados por el subagente Frontend

## Archivos a Crear

1. `app/Http/Requests/StoreOverTimeRequest.php`
2. `app/Http/Requests/UpdateOverTimeRequest.php`
3. Actualizar `routes/admin.php` con las rutas de Over Time

## Comandos Artisan

```bash
# Crear Form Requests
php artisan make:request StoreOverTimeRequest
php artisan make:request UpdateOverTimeRequest
```

## Notas Importantes

- La Custom Rule `AfterTimeOrNextDay` ya está creada y funciona correctamente
- El modelo `OverTime` tiene todos los métodos necesarios (calculateNetHours, calculateTotalHours, etc.)
- Los Form Requests deben usar la Custom Rule para validar que end_time > start_time incluso cuando cruza medianoche
- En StoreOverTimeRequest, la fecha debe ser `after_or_equal:today`
- En UpdateOverTimeRequest, NO validar fecha futura (permitir editar overtimes pasados)
