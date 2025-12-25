# Spec 07: Análisis Técnico de Implementación - Over Time Module

**Fecha de Creación:** 2024-12-24
**Autor:** Agent Architect
**Fase del Proyecto:** FASE 2 - Planificación de Producción
**Estado:** Análisis Técnico Completo
**Versión:** 1.0
**Relacionado con:**
- Spec 01 - Plan de Implementación Capacidad de Producción
- db.mkd - Esquema de Base de Datos

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Análisis del Esquema de Base de Datos](#análisis-del-esquema-de-base-de-datos)
3. [Análisis de Relaciones](#análisis-de-relaciones)
4. [Impacto Arquitectural](#impacto-arquitectural)
5. [Propuesta de Solución](#propuesta-de-solución)
6. [Plan de Implementación Detallado](#plan-de-implementación-detallado)
7. [Consideraciones Técnicas](#consideraciones-técnicas)
8. [Testing](#testing)
9. [Referencias](#referencias)

---

## Resumen Ejecutivo

### Propósito del Módulo

El módulo **Over_Time** (Tiempo Extra) gestiona las horas extra programadas para la producción, permitiendo calcular con precisión la capacidad disponible total al combinar:
- Horas regulares del turno (Shift)
- Horas extra programadas (Over_Time)
- Empleados disponibles
- Días laborables (descontando feriados - Holidays)

### Decisión Crítica de Arquitectura

**PREGUNTA CLAVE:** ¿Debe Over_Time relacionarse directamente con WO (Work Order), o mantener la relación indirecta a través de Production_Capacity?

**RESPUESTA:** **MANTENER RELACIÓN INDIRECTA** - Over_Time NO debe tener FK directa a WO

### Justificación (4 Puntos Fundamentales)

1. **Separación de Responsabilidades (Clean Architecture)**
   - Over_Time es un recurso de planificación temporal (WHEN + WHO)
   - WO es una orden de trabajo específica (WHAT + HOW MUCH)
   - Production_Capacity es la asignación que une recursos con órdenes (ALLOCATION)

2. **Flexibilidad Operacional**
   - Un tiempo extra puede beneficiar a MÚLTIPLES work orders
   - Un tiempo extra se programa sin saber aún qué WOs se asignarán
   - Production_Capacity actúa como tabla de asignación (many-to-many implícito)

3. **Coherencia con el Flujo de Negocio**
   - Paso 1-5 del flujo: Calcular capacidad disponible (usa Shift + Over_Time)
   - Paso 6-10 del flujo: Asignar WOs a esa capacidad (crea Production_Capacity)
   - Over_Time es INPUT para cálculo, no OUTPUT de asignación

4. **Consistencia con Arquitectura Existente**
   - Shift tampoco tiene FK a WO (es un recurso reutilizable)
   - Holiday tampoco tiene FK a WO (es una restricción temporal)
   - Over_Time sigue el mismo patrón: recurso temporal reutilizable

### Pros y Contras Detallados

#### Opción A: Over_Time → Production_Capacity → WO (RECOMENDADA)

**PROS:**
- ✅ Clean Architecture: Separación clara de capas
- ✅ Reutilización: Un overtime puede usarse para múltiples WOs
- ✅ Flexibilidad: Programar overtime sin asignar WO aún
- ✅ Escalabilidad: Fácil agregar nuevas formas de asignar capacidad
- ✅ Mantenibilidad: Cambios en WO no afectan Over_Time
- ✅ Coherencia: Mismo patrón que Shift y Holiday
- ✅ Cálculos independientes: Capacidad disponible vs. capacidad asignada

**CONTRAS:**
- ⚠️ Complejidad de queries: Requiere JOIN de 3 tablas para ver WOs de un overtime
- ⚠️ Indirección: No es obvio que Over_Time afecta WOs sin conocer Production_Capacity

#### Opción B: Over_Time → WO (DESCARTADA)

**PROS:**
- ✅ Simplicidad aparente: Relación directa fácil de entender
- ✅ Queries más simples: `$overtime->workOrders()` directo

**CONTRAS:**
- ❌ Acoplamiento fuerte: Over_Time depende de WO
- ❌ Falta de flexibilidad: No se puede programar overtime sin WO asignado
- ❌ Violación de SRP: Over_Time tendría dos responsabilidades (recurso + asignación)
- ❌ Inconsistencia: Shift no tiene FK a WO, pero Over_Time sí
- ❌ Problemas de escalabilidad: ¿Qué pasa si un overtime beneficia a 5 WOs?
- ❌ Duplicación: Production_Capacity ya maneja la asignación WO ↔ recursos
- ❌ Dificultad para cálculos: Mezcla capacidad disponible con asignada

### Conclusión

**DECISIÓN FINAL:** Implementar Over_Time con relación indirecta a WO a través de Production_Capacity (Opción A).

**Estructura de Relaciones:**
```
Over_Time → belongsTo(Shift)
Production_Capacity → belongsTo(WO) + hasOne(Over_Time) [NO RECOMENDADO]

MEJOR ENFOQUE:
Over_Time es un recurso global usado en cálculos
Production_Capacity referencia Shift (que ya considera Over_Time en cálculos)
```

**IMPORTANTE:** Según db.mkd, Production_Capacity tiene `Over_Time_ID` FK, pero esto puede ser problemático. Ver sección "Análisis de Relaciones" para propuesta mejorada.

---

## Análisis del Esquema de Base de Datos

### Estructura según db.mkd

```markdown
## Over_Time
- **PK**: Over_Time_ID
- Name
- Start_Time
- End_Time
- Breaktime
- Employees_Qty
- Date
- Comments

## Production_Capacity
- **PK**: PC_ID
- **FK**: Part_ID
- **FK**: WO_ID
- Hours_Per_Machine
- Hours_Per_Person_2
- Hours_Per_Person_3
- **FK**: Shift_ID
- **FK**: Over_Time_ID
```

### Análisis de Campos de Over_Time

| Campo | Tipo Recomendado | Descripción | Validaciones | Índices |
|-------|------------------|-------------|--------------|---------|
| `Over_Time_ID` | `bigint UNSIGNED` | PK autoincremental | PRIMARY KEY | PRIMARY |
| `Name` | `string(255)` | Nombre descriptivo del overtime | required, max:255 | - |
| `Start_Time` | `time` | Hora de inicio | required, before:End_Time | - |
| `End_Time` | `time` | Hora de fin | required, after:Start_Time | - |
| `Breaktime` | `integer` | Minutos de descanso | default:0, min:0, max:480 | - |
| `Employees_Qty` | `integer` | Cantidad de empleados | required, min:1, max:1000 | - |
| `Date` | `date` | Fecha del overtime | required, after_or_equal:today | INDEX |
| `Comments` | `text` | Comentarios adicionales | nullable, max:1000 | - |
| `Shift_ID` | `bigint UNSIGNED` | FK a Shift | required, exists:shifts,id | FOREIGN KEY + INDEX |
| `timestamps` | - | created_at, updated_at | Laravel automático | - |

### Campos FALTANTES en db.mkd (Requeridos)

**CRÍTICO:** db.mkd NO incluye `Shift_ID` en la tabla Over_Time, pero es ESENCIAL para relacionar el overtime con un turno específico.

**Adición Obligatoria:**
```markdown
## Over_Time
- **PK**: Over_Time_ID
- Name
- Start_Time
- End_Time
- Breaktime
- Employees_Qty
- Date
- **FK**: Shift_ID  ← AGREGAR ESTE CAMPO
- Comments
```

**Justificación:**
- Un overtime siempre ocurre en el contexto de un turno específico
- Necesario para cálculos de capacidad (Spec 01 - Propiedad 5)
- Permite validar que Start_Time > Shift.End_Time (overtime después del turno)
- Facilita queries: "Mostrar todos los overtimes del Turno 1"

---

## Análisis de Relaciones

### Problema Identificado en db.mkd

```markdown
## Production_Capacity
- **FK**: Shift_ID
- **FK**: Over_Time_ID  ← PROBLEMA: Relación 1:1 limitante
```

**PROBLEMA:** Esta estructura implica que:
- 1 Production_Capacity → 1 Over_Time
- ¿Qué pasa si hay 3 overtimes en un día?
- ¿Cómo se asignan múltiples overtimes a un WO?

### Análisis de 3 Enfoques Posibles

#### Enfoque 1: Over_Time en Production_Capacity (db.mkd actual)

**Estructura:**
```
Production_Capacity
├── FK: Shift_ID
└── FK: Over_Time_ID  (nullable)
```

**PROBLEMA:**
- Solo puede referenciar 1 overtime por Production_Capacity
- Si hay 3 overtimes en un día, necesitaría 3 Production_Capacity (duplicación)
- Mezcla concepto de capacidad planificada con overtime específico

**VEREDICTO:** ❌ NO RECOMENDADO

---

#### Enfoque 2: Over_Time como Recurso Global (RECOMENDADO)

**Estructura:**
```
Over_Time
├── belongsTo(Shift)
└── (sin FK a WO ni Production_Capacity)

Cálculo de Capacidad:
1. Calcular horas base del Shift
2. Sumar horas de TODOS los Over_Times en el rango de fechas
3. Total = capacidad disponible
4. Asignar WOs hasta agotar capacidad
5. Production_Capacity guarda asignación WO ↔ Shift (ya incluye overtime en cálculo)
```

**VENTAJAS:**
- ✅ Over_Time es INPUT para cálculo, no OUTPUT de asignación
- ✅ Múltiples overtimes se suman automáticamente
- ✅ No hay duplicación de Production_Capacity
- ✅ Separación limpia: recursos (Shift, Over_Time) vs. asignaciones (Production_Capacity)

**Modificación a Production_Capacity:**
```markdown
## Production_Capacity
- **PK**: PC_ID
- **FK**: Part_ID
- **FK**: WO_ID
- **FK**: Shift_ID
- Hours_Allocated  (calculado considerando shift + overtime)
- Date_Start
- Date_End
- ❌ ELIMINAR: Over_Time_ID  (ya no se necesita)
```

**VEREDICTO:** ✅ **RECOMENDADO** - Sigue principios de Clean Architecture

---

#### Enfoque 3: Tabla Pivot Over_Time ↔ Production_Capacity

**Estructura:**
```
Over_Time_Production_Capacity (pivot table)
├── FK: Over_Time_ID
├── FK: Production_Capacity_ID
└── Hours_Contributed
```

**VENTAJAS:**
- ✅ Permite asignar múltiples overtimes a un Production_Capacity
- ✅ Registra exactamente cuántas horas aportó cada overtime

**DESVENTAJAS:**
- ⚠️ Complejidad innecesaria para el caso de uso actual
- ⚠️ Requiere lógica adicional para distribuir horas entre overtimes
- ⚠️ Overhead de mantenimiento

**VEREDICTO:** ⚠️ OVER-ENGINEERING para el alcance actual

---

### Decisión de Arquitectura de Relaciones

**ENFOQUE SELECCIONADO:** **Enfoque 2 - Over_Time como Recurso Global**

**Estructura Final de Relaciones:**

```
┌─────────────┐         ┌─────────────────────┐
│   Shift     │◄───────┤   Over_Time         │
│             │  FK     │                     │
│ - id        │         │ - id                │
│ - name      │         │ - shift_id          │
│ - start     │         │ - date              │
│ - end       │         │ - start_time        │
│ - break     │         │ - end_time          │
└─────────────┘         │ - break_minutes     │
      ▲                 │ - employees_qty     │
      │                 └─────────────────────┘
      │ FK
      │
┌─────────────────────┐           ┌──────────────┐
│ Production_Capacity │           │  Work_Order  │
│                     │──────────►│              │
│ - id                │    FK     │ - id         │
│ - shift_id          │           │ - po_id      │
│ - wo_id             │◄──────────│ - status     │
│ - part_id           │           └──────────────┘
│ - hours_allocated   │
│ - date_start        │
│ - date_end          │
└─────────────────────┘
```

**Relaciones en Eloquent:**

```php
// Over_Time Model
public function shift(): BelongsTo
{
    return $this->belongsTo(Shift::class);
}

// Shift Model (agregar)
public function overTimes(): HasMany
{
    return $this->hasMany(OverTime::class);
}

// Production_Capacity Model (ELIMINAR over_time_id)
public function shift(): BelongsTo
{
    return $this->belongsTo(Shift::class);
}

public function workOrder(): BelongsTo
{
    return $this->belongsTo(WorkOrder::class, 'wo_id');
}
```

**Cálculo de Capacidad (CapacityCalculatorService):**

```php
public function calculateAvailableHours(
    Shift $shift,
    Carbon $startDate,
    Carbon $endDate,
    int $employeesCount
): float {
    // 1. Horas base del turno
    $workDays = $this->calculateWorkDays($startDate, $endDate);
    $shiftHours = $this->calculateShiftHours($shift);
    $breakHours = $this->calculateBreakHours($shift);
    $baseHours = $workDays * ($shiftHours - $breakHours) * $employeesCount;

    // 2. Horas extra (TODOS los overtimes del shift en el rango)
    $overtimeHours = OverTime::where('shift_id', $shift->id)
        ->whereBetween('date', [$startDate, $endDate])
        ->get()
        ->sum(function ($overtime) {
            $hours = $overtime->calculateNetHours();
            return $hours * $overtime->employees_qty;
        });

    return $baseHours + $overtimeHours;
}
```

---

## Impacto Arquitectural

### Backend

#### Nuevos Componentes

1. **Migración**
   - `create_over_times_table.php`
   - Campos: Ver tabla en sección "Análisis de Campos"
   - Índices: `shift_id`, `date`, compuesto `[shift_id, date]`

2. **Modelo Eloquent**
   - `app/Models/OverTime.php`
   - Relaciones: `belongsTo(Shift)`
   - Métodos: `calculateNetHours()`, scopes de búsqueda
   - Casts: `date`, `start_time`, `end_time`

3. **Factory**
   - `database/factories/OverTimeFactory.php`
   - Datos realistas para testing y seeding

4. **Seeder**
   - `database/seeders/OverTimeSeeder.php`
   - Ejemplos útiles de overtimes

5. **Form Request** (opcional pero recomendado)
   - `app/Http/Requests/StoreOverTimeRequest.php`
   - `app/Http/Requests/UpdateOverTimeRequest.php`

#### Modificaciones a Componentes Existentes

1. **Shift Model**
   - Agregar relación `hasMany(OverTime::class)`
   - Opcional: Método helper `getTotalOvertimeHours(Carbon $date)`

2. **CapacityCalculatorService** (Spec 01)
   - Ya incluye método `calculateOvertimeHours()` que usa Over_Time
   - No requiere modificaciones estructurales

3. **Production_Capacity** (SI SE IMPLEMENTA)
   - **ELIMINAR** campo `over_time_id` de db.mkd
   - Mantener solo `shift_id`

### Frontend (Opcional - CRUD Completo)

Si se decide implementar CRUD completo de Over_Time (similar a Spec 01):

#### Componentes Livewire (4)

1. `OverTimeCreate.php` - Formulario de creación
2. `OverTimeEdit.php` - Formulario de edición
3. `OverTimeList.php` - Listado con búsqueda y filtros
4. `OverTimeShow.php` - Vista de detalle

#### Vistas Blade (4)

1. `over-time-create.blade.php`
2. `over-time-edit.blade.php`
3. `over-time-list.blade.php`
4. `over-time-show.blade.php`

#### Rutas

```php
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/over-times', OverTimeList::class)->name('over-times.index');
    Route::get('/over-times/create', OverTimeCreate::class)->name('over-times.create');
    Route::get('/over-times/{overTime}', OverTimeShow::class)->name('over-times.show');
    Route::get('/over-times/{overTime}/edit', OverTimeEdit::class)->name('over-times.edit');
});
```

### Base de Datos

#### Nueva Tabla: over_times

**Migración Completa:**

```php
Schema::create('over_times', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->time('start_time');
    $table->time('end_time');
    $table->integer('break_minutes')->default(0)->comment('Minutos de descanso');
    $table->integer('employees_qty')->comment('Cantidad de empleados disponibles');
    $table->date('date')->comment('Fecha específica del overtime');
    $table->foreignId('shift_id')
          ->constrained('shifts')
          ->cascadeOnDelete()
          ->comment('Turno al que pertenece este overtime');
    $table->text('comments')->nullable();
    $table->timestamps();

    // Índices para optimización
    $table->index('shift_id', 'idx_over_times_shift');
    $table->index('date', 'idx_over_times_date');
    $table->index(['shift_id', 'date'], 'idx_over_times_shift_date');
});
```

**Explicación de Índices:**

1. `idx_over_times_shift`: Para queries "overtimes de un turno específico"
2. `idx_over_times_date`: Para queries "overtimes en un rango de fechas"
3. `idx_over_times_shift_date`: Índice compuesto para query más común:
   ```sql
   SELECT * FROM over_times
   WHERE shift_id = ? AND date BETWEEN ? AND ?
   ```

---

## Propuesta de Solución

### Diseño del Modelo OverTime

**Archivo:** `app/Models/OverTime.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class OverTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'break_minutes',
        'employees_qty',
        'date',
        'shift_id',
        'comments',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'date' => 'date',
        'break_minutes' => 'integer',
        'employees_qty' => 'integer',
    ];

    /**
     * ================================================
     * RELATIONSHIPS
     * ================================================
     */

    /**
     * Turno al que pertenece este overtime
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * ================================================
     * SCOPES
     * ================================================
     */

    /**
     * Filtrar por rango de fechas
     */
    public function scopeByDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Filtrar por turno
     */
    public function scopeByShift($query, int $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    /**
     * Solo overtimes activos (fecha >= hoy)
     */
    public function scopeActive($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    /**
     * Solo overtimes pasados
     */
    public function scopePast($query)
    {
        return $query->where('date', '<', now()->toDateString());
    }

    /**
     * Buscar por nombre
     */
    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where('name', 'like', "%{$search}%")
                     ->orWhereHas('shift', function ($q) use ($search) {
                         $q->where('name', 'like', "%{$search}%");
                     });
    }

    /**
     * ================================================
     * BUSINESS LOGIC METHODS
     * ================================================
     */

    /**
     * Calcula las horas netas de trabajo (descontando descansos)
     *
     * Maneja correctamente overtimes que cruzan medianoche
     *
     * @return float Horas netas de trabajo
     */
    public function calculateNetHours(): float
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        // Manejar overtimes que cruzan medianoche
        // Ejemplo: 22:00 a 02:00 (4 horas, no -20 horas)
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $totalMinutes = $end->diffInMinutes($start);
        $netMinutes = $totalMinutes - $this->break_minutes;

        // Evitar horas negativas (validación defensiva)
        $netMinutes = max(0, $netMinutes);

        return round($netMinutes / 60, 2);
    }

    /**
     * Calcula las horas totales del overtime (horas netas × empleados)
     *
     * Este es el valor que se suma a la capacidad disponible
     *
     * @return float Horas-hombre totales
     */
    public function calculateTotalHours(): float
    {
        return $this->calculateNetHours() * $this->employees_qty;
    }

    /**
     * ================================================
     * ACCESSORS
     * ================================================
     */

    /**
     * Accessor para obtener horas totales como atributo
     * Uso: $overtime->total_hours
     */
    public function getTotalHoursAttribute(): float
    {
        return $this->calculateTotalHours();
    }

    /**
     * Accessor para obtener horas netas como atributo
     * Uso: $overtime->net_hours
     */
    public function getNetHoursAttribute(): float
    {
        return $this->calculateNetHours();
    }

    /**
     * ================================================
     * VALIDATION HELPERS
     * ================================================
     */

    /**
     * Verifica si el overtime está en el futuro
     */
    public function isFuture(): bool
    {
        return $this->date->isFuture();
    }

    /**
     * Verifica si el overtime es para hoy
     */
    public function isToday(): bool
    {
        return $this->date->isToday();
    }

    /**
     * Verifica si el overtime ya pasó
     */
    public function isPast(): bool
    {
        return $this->date->isPast();
    }
}
```

### Validaciones de Negocio

#### Reglas de Validación en Form Request

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
                'after_or_equal:today', // No permitir overtimes en el pasado
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time', // IMPORTANTE: No funciona con cruces de medianoche
                // Para validar cruces de medianoche, usar Custom Rule
            ],
            'break_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:480', // Máximo 8 horas de descanso (poco realista)
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
            'end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'break_minutes.min' => 'Los minutos de descanso no pueden ser negativos.',
            'break_minutes.max' => 'Los minutos de descanso no pueden exceder 8 horas.',
            'employees_qty.required' => 'Debe especificar la cantidad de empleados.',
            'employees_qty.min' => 'Debe haber al menos 1 empleado.',
            'employees_qty.max' => 'La cantidad de empleados no puede exceder 1000.',
        ];
    }
}
```

#### Custom Validation Rule para End_Time > Start_Time (con medianoche)

**PROBLEMA:** Laravel's `after:start_time` NO funciona correctamente con horarios que cruzan medianoche.

**Ejemplo:**
- `start_time = 22:00`
- `end_time = 02:00` (siguiente día)
- Validación `after:start_time` FALLA porque 02:00 < 22:00

**SOLUCIÓN:** Custom Rule `AfterTimeOrNextDay`

```php
<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

class AfterTimeOrNextDay implements Rule
{
    protected $startTime;
    protected $minimumHours;

    /**
     * Create a new rule instance.
     *
     * @param string $startTime Hora de inicio (formato H:i)
     * @param int $minimumHours Mínimo de horas de duración (default: 1)
     */
    public function __construct(string $startTime, int $minimumHours = 1)
    {
        $this->startTime = $startTime;
        $this->minimumHours = $minimumHours;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        try {
            $start = Carbon::parse($this->startTime);
            $end = Carbon::parse($value);

            // Si end < start, asumir que cruza medianoche
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $hours = $end->diffInHours($start, true);

            // Validar que haya al menos X horas de duración
            return $hours >= $this->minimumHours;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return "La hora de fin debe ser al menos {$this->minimumHours} hora(s) después de la hora de inicio.";
    }
}
```

**Uso en Form Request:**

```php
use App\Rules\AfterTimeOrNextDay;

public function rules(): array
{
    return [
        'start_time' => 'required|date_format:H:i',
        'end_time' => [
            'required',
            'date_format:H:i',
            new AfterTimeOrNextDay($this->start_time, 1), // Mínimo 1 hora
        ],
        // ... otros campos
    ];
}
```

---

## Plan de Implementación Detallado

### FASE 1: Base de Datos (30 minutos)

#### Tarea 1.1: Crear Migración

**Comando:**
```bash
php artisan make:migration create_over_times_table
```

**Contenido Completo:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('over_times', function (Blueprint $table) {
            $table->id();

            // Información básica
            $table->string('name')->comment('Nombre descriptivo del overtime');

            // Horario
            $table->time('start_time')->comment('Hora de inicio del overtime');
            $table->time('end_time')->comment('Hora de fin del overtime');
            $table->integer('break_minutes')
                  ->default(0)
                  ->comment('Minutos de descanso durante el overtime');

            // Recursos
            $table->integer('employees_qty')
                  ->comment('Cantidad de empleados disponibles');

            // Fecha
            $table->date('date')->comment('Fecha específica del overtime');

            // Relaciones
            $table->foreignId('shift_id')
                  ->constrained('shifts')
                  ->cascadeOnDelete()
                  ->comment('Turno al que pertenece este overtime');

            // Comentarios
            $table->text('comments')->nullable();

            // Timestamps
            $table->timestamps();

            // Índices para optimización de queries
            $table->index('shift_id', 'idx_over_times_shift');
            $table->index('date', 'idx_over_times_date');
            $table->index(['shift_id', 'date'], 'idx_over_times_shift_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('over_times');
    }
};
```

**Ejecutar:**
```bash
php artisan migrate
```

**Verificar:**
```bash
php artisan tinker
>>> Schema::hasTable('over_times');
=> true
>>> Schema::hasColumn('over_times', 'shift_id');
=> true
```

---

#### Tarea 1.2: Actualizar Modelo Shift

**Archivo:** `app/Models/Shift.php`

**Agregar relación:**

```php
/**
 * Get all overtime records for this shift
 */
public function overTimes(): HasMany
{
    return $this->hasMany(OverTime::class);
}

/**
 * Get overtime records for a specific date
 */
public function overTimesForDate(Carbon $date): Collection
{
    return $this->overTimes()
                ->where('date', $date->toDateString())
                ->get();
}

/**
 * Calculate total overtime hours for a date range
 */
public function getTotalOvertimeHours(Carbon $startDate, Carbon $endDate): float
{
    return $this->overTimes()
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->sum('total_hours');
}
```

**No olvidar importar:**
```php
use Illuminate\Support\Collection;
use Carbon\Carbon;
```

---

### FASE 2: Modelo y Factory (45 minutos)

#### Tarea 2.1: Crear Modelo OverTime

**Comando:**
```bash
php artisan make:model OverTime
```

**Contenido:** Ver sección "Diseño del Modelo OverTime" completa arriba.

---

#### Tarea 2.2: Crear Factory

**Comando:**
```bash
php artisan make:factory OverTimeFactory
```

**Archivo:** `database/factories/OverTimeFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\OverTime;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OverTime>
 */
class OverTimeFactory extends Factory
{
    protected $model = OverTime::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $shift = Shift::inRandomOrder()->first() ?? Shift::factory()->create();

        // Generar overtime realista:
        // - Empieza después del turno normal (shift->end_time)
        // - Dura entre 2-4 horas
        // - Puede ser en la noche (22:00 - 02:00) o madrugada

        $shiftEndTime = \Carbon\Carbon::parse($shift->end_time);

        // Start time: 30 min después del turno normal
        $startTime = $shiftEndTime->copy()->addMinutes(30);

        // End time: 2-4 horas después
        $duration = $this->faker->numberBetween(2, 4);
        $endTime = $startTime->copy()->addHours($duration);

        return [
            'name' => $this->faker->randomElement([
                'Overtime Producción Urgente',
                'Tiempo Extra - Pedido Especial',
                'Overtime Fin de Semana',
                'Producción Extra Cliente Prioritario',
                'Overtime Recuperación',
            ]),
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
            'break_minutes' => $this->faker->randomElement([0, 15, 30]), // 0, 15 o 30 min
            'employees_qty' => $this->faker->numberBetween(5, 20),
            'date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'shift_id' => $shift->id,
            'comments' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Overtime nocturno (22:00 a 02:00)
     */
    public function nightShift(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Overtime Nocturno',
            'start_time' => '22:00',
            'end_time' => '02:00', // Cruza medianoche
            'break_minutes' => 30,
            'employees_qty' => $this->faker->numberBetween(5, 15),
        ]);
    }

    /**
     * Overtime de fin de semana
     */
    public function weekend(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Overtime Fin de Semana',
            'date' => $this->faker->dateTimeBetween('now', '+30 days')
                                  ->modify('next saturday')
                                  ->format('Y-m-d'),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'break_minutes' => 60,
            'employees_qty' => $this->faker->numberBetween(10, 25),
        ]);
    }

    /**
     * Overtime corto (2 horas)
     */
    public function short(): static
    {
        $start = \Carbon\Carbon::parse('17:00');
        $end = $start->copy()->addHours(2);

        return $this->state(fn (array $attributes) => [
            'name' => 'Overtime Corto',
            'start_time' => $start->format('H:i'),
            'end_time' => $end->format('H:i'),
            'break_minutes' => 0,
            'employees_qty' => $this->faker->numberBetween(5, 10),
        ]);
    }

    /**
     * Overtime largo (6+ horas)
     */
    public function long(): static
    {
        $start = \Carbon\Carbon::parse('17:00');
        $end = $start->copy()->addHours(6);

        return $this->state(fn (array $attributes) => [
            'name' => 'Overtime Extendido',
            'start_time' => $start->format('H:i'),
            'end_time' => $end->format('H:i'),
            'break_minutes' => 60,
            'employees_qty' => $this->faker->numberBetween(15, 30),
        ]);
    }
}
```

**Testing en Tinker:**
```php
php artisan tinker

>>> OverTime::factory()->create();
>>> OverTime::factory()->nightShift()->create();
>>> OverTime::factory()->weekend()->create();
>>> OverTime::factory()->count(5)->create();
```

---

#### Tarea 2.3: Crear Seeder

**Comando:**
```bash
php artisan make:seeder OverTimeSeeder
```

**Archivo:** `database/seeders/OverTimeSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\OverTime;
use App\Models\Shift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OverTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan turnos
        if (Shift::count() === 0) {
            $this->command->warn('⚠️  No hay turnos creados. Ejecute ShiftSeeder primero.');
            return;
        }

        $this->command->info('🕐 Creando overtimes de ejemplo...');

        $shift1 = Shift::where('name', 'like', '%Turno 1%')->first()
                      ?? Shift::first();
        $shift2 = Shift::where('name', 'like', '%Turno 2%')->first()
                      ?? Shift::skip(1)->first()
                      ?? $shift1;

        // Overtime 1: Producción urgente - Turno 1
        OverTime::create([
            'name' => 'Overtime Producción Urgente - Cliente ABC',
            'shift_id' => $shift1->id,
            'date' => Carbon::today()->addDays(2),
            'start_time' => '17:00',
            'end_time' => '21:00', // 4 horas
            'break_minutes' => 15,
            'employees_qty' => 12,
            'comments' => 'Pedido urgente cliente ABC - Deadline viernes',
        ]);

        // Overtime 2: Fin de semana
        OverTime::create([
            'name' => 'Overtime Fin de Semana',
            'shift_id' => $shift1->id,
            'date' => Carbon::today()->next('Saturday'),
            'start_time' => '08:00',
            'end_time' => '17:00', // 9 horas
            'break_minutes' => 60,
            'employees_qty' => 20,
            'comments' => 'Producción extra para cumplir cuota mensual',
        ]);

        // Overtime 3: Nocturno (cruza medianoche)
        OverTime::create([
            'name' => 'Overtime Nocturno - Turno 2',
            'shift_id' => $shift2->id,
            'date' => Carbon::today()->addDays(5),
            'start_time' => '22:00',
            'end_time' => '02:00', // Cruza medianoche - 4 horas
            'break_minutes' => 30,
            'employees_qty' => 8,
            'comments' => 'Producción nocturna - máquina X disponible',
        ]);

        // Overtime 4: Corto - 2 horas
        OverTime::create([
            'name' => 'Overtime Corto - Completar Lote',
            'shift_id' => $shift1->id,
            'date' => Carbon::today()->addDays(3),
            'start_time' => '17:00',
            'end_time' => '19:00', // 2 horas
            'break_minutes' => 0,
            'employees_qty' => 6,
            'comments' => 'Completar lote L-12345',
        ]);

        // Overtime 5: Extendido - 6 horas
        OverTime::create([
            'name' => 'Overtime Extendido - Pedido Especial',
            'shift_id' => $shift1->id,
            'date' => Carbon::today()->addWeeks(1),
            'start_time' => '17:00',
            'end_time' => '23:00', // 6 horas
            'break_minutes' => 45,
            'employees_qty' => 15,
            'comments' => 'Cliente prioritario - pago de overtime 2x',
        ]);

        // Generar 10 overtimes aleatorios adicionales
        OverTime::factory()->count(10)->create();

        $this->command->info('✅ Overtimes creados exitosamente');
        $this->command->info('   - 5 overtimes específicos');
        $this->command->info('   - 10 overtimes aleatorios');
        $this->command->table(
            ['Total de Overtimes'],
            [[OverTime::count()]]
        );
    }
}
```

**Actualizar DatabaseSeeder:**

```php
// database/seeders/DatabaseSeeder.php

public function run(): void
{
    $this->call([
        // ... seeders existentes
        ShiftSeeder::class,
        OverTimeSeeder::class, // AGREGAR AQUÍ
        // ... otros seeders
    ]);
}
```

**Ejecutar:**
```bash
php artisan db:seed --class=OverTimeSeeder
```

---

### FASE 3: Validación y Testing (30 minutos)

#### Tarea 3.1: Crear Form Requests

**Comando:**
```bash
php artisan make:request StoreOverTimeRequest
php artisan make:request UpdateOverTimeRequest
```

**StoreOverTimeRequest:** Ver sección "Validaciones de Negocio" completa arriba.

**UpdateOverTimeRequest:** Igual que Store, pero sin `after_or_equal:today` en date:

```php
'date' => [
    'required',
    'date',
    // NO validar "after_or_equal:today" en update (permitir editar pasados)
],
```

---

#### Tarea 3.2: Crear Custom Rule

**Comando:**
```bash
php artisan make:rule AfterTimeOrNextDay
```

**Contenido:** Ver sección "Custom Validation Rule" completa arriba.

---

#### Tarea 3.3: Testing en Tinker

```bash
php artisan tinker
```

**Tests manuales:**

```php
// 1. Crear overtime simple
$overtime = OverTime::create([
    'name' => 'Test Overtime',
    'shift_id' => 1,
    'date' => '2024-12-30',
    'start_time' => '17:00',
    'end_time' => '21:00',
    'break_minutes' => 15,
    'employees_qty' => 10,
]);

// 2. Verificar cálculos
$overtime->calculateNetHours();
// Esperado: 3.75 horas (4 horas - 15 min)

$overtime->calculateTotalHours();
// Esperado: 37.5 horas (3.75 × 10 empleados)

// 3. Test con medianoche
$midnight = OverTime::create([
    'name' => 'Midnight Test',
    'shift_id' => 1,
    'date' => '2024-12-30',
    'start_time' => '22:00',
    'end_time' => '02:00',
    'break_minutes' => 30,
    'employees_qty' => 5,
]);

$midnight->calculateNetHours();
// Esperado: 3.5 horas (4 horas - 30 min)

// 4. Test scopes
OverTime::byShift(1)->count();
OverTime::active()->count();
OverTime::byDateRange(now(), now()->addWeek())->count();

// 5. Test relaciones
$shift = Shift::first();
$shift->overTimes;
$shift->getTotalOvertimeHours(now(), now()->addMonth());
```

---

### FASE 4: CRUD Livewire (Opcional - 2 horas)

**NOTA:** Esta fase es OPCIONAL si solo se necesita el modelo para cálculos de capacidad.

Si se desea implementar interfaz de gestión completa, seguir el patrón de Spec 01 (OverTime CRUD):

1. Crear componentes Livewire (Create, Edit, List, Show)
2. Crear vistas Blade
3. Configurar rutas
4. Implementar permisos con Spatie

**Referencia:** Spec 01, sección "FASE 2.3: CRUD OverTime"

---

## Consideraciones Técnicas

### 1. Manejo de Medianoche

**PROBLEMA:** Overtimes que cruzan medianoche (ej: 22:00 a 02:00)

**SOLUCIONES:**

#### En Base de Datos:
- NO usar tipo `datetime` (incluye fecha, causaría confusión)
- USAR tipo `time` (solo hora, independiente de fecha)
- Campo `date` separado indica el día de INICIO del overtime

#### En Modelo:
```php
public function calculateNetHours(): float
{
    $start = Carbon::parse($this->start_time);
    $end = Carbon::parse($this->end_time);

    // Si end < start, asumir que cruza medianoche
    if ($end->lessThan($start)) {
        $end->addDay();
    }

    $totalMinutes = $end->diffInMinutes($start);
    return round(($totalMinutes - $this->break_minutes) / 60, 2);
}
```

#### En Validación:
- Usar Custom Rule `AfterTimeOrNextDay` (ver arriba)
- NO usar `after:start_time` de Laravel (falla con medianoche)

---

### 2. Zona Horaria

**RECOMENDACIÓN:** Usar configuración de Laravel:

```php
// config/app.php
'timezone' => 'America/Mexico_City', // Ajustar según ubicación

// En modelo Over_Time, usar Carbon con timezone de config
protected $casts = [
    'start_time' => 'datetime:H:i',
    'end_time' => 'datetime:H:i',
    'date' => 'date',
];
```

---

### 3. Cálculo de Horas en CapacityCalculatorService

**Integración con Spec 01:**

```php
// CapacityCalculatorService.php

protected function calculateOvertimeHours(
    Shift $shift,
    Carbon $startDate,
    Carbon $endDate
): float {
    $overtimes = OverTime::byShift($shift->id)
        ->byDateRange($startDate, $endDate)
        ->get();

    $totalOvertimeHours = $overtimes->sum(function ($overtime) {
        return $overtime->calculateTotalHours();
    });

    return round($totalOvertimeHours, 2);
}
```

**NO MODIFICAR** este método - ya está implementado correctamente en Spec 01.

---

### 4. Performance

#### Índices Críticos

```sql
-- Query más común: Overtimes de un turno en un rango de fechas
SELECT * FROM over_times
WHERE shift_id = ?
  AND date BETWEEN ? AND ?;

-- Índice compuesto optimiza esta query
INDEX (shift_id, date)
```

#### Eager Loading

```php
// MAL (N+1 queries)
$overtimes = OverTime::all();
foreach ($overtimes as $overtime) {
    echo $overtime->shift->name; // Query por cada overtime
}

// BIEN (2 queries)
$overtimes = OverTime::with('shift')->get();
foreach ($overtimes as $overtime) {
    echo $overtime->shift->name; // Sin query adicional
}
```

#### Cache (Opcional)

```php
// Cachear cálculo de overtimes para capacidad
$cacheKey = "overtime_hours_{$shift->id}_{$startDate}_{$endDate}";

$overtimeHours = Cache::remember($cacheKey, 3600, function () use ($shift, $startDate, $endDate) {
    return $this->calculateOvertimeHours($shift, $startDate, $endDate);
});
```

---

### 5. Validaciones Defensivas

```php
// En calculateNetHours()
$netMinutes = max(0, $totalMinutes - $this->break_minutes);
// Evita horas negativas si break_minutes > totalMinutes (error de datos)

// En Factory
'break_minutes' => $this->faker->numberBetween(0, $duration * 60 / 2),
// Asegura que break_minutes no exceda la mitad de la duración
```

---

## Testing

### Tests Unitarios Sugeridos

**Archivo:** `tests/Unit/Models/OverTimeTest.php`

```php
<?php

namespace Tests\Unit\Models;

use App\Models\OverTime;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OverTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_net_hours_correctly()
    {
        $overtime = OverTime::factory()->create([
            'start_time' => '17:00',
            'end_time' => '21:00', // 4 horas
            'break_minutes' => 15,
        ]);

        $this->assertEquals(3.75, $overtime->calculateNetHours());
    }

    /** @test */
    public function it_calculates_net_hours_crossing_midnight()
    {
        $overtime = OverTime::factory()->create([
            'start_time' => '22:00',
            'end_time' => '02:00', // 4 horas (cruza medianoche)
            'break_minutes' => 30,
        ]);

        $this->assertEquals(3.5, $overtime->calculateNetHours());
    }

    /** @test */
    public function it_calculates_total_hours_correctly()
    {
        $overtime = OverTime::factory()->create([
            'start_time' => '17:00',
            'end_time' => '21:00',
            'break_minutes' => 0,
            'employees_qty' => 10,
        ]);

        $this->assertEquals(40.0, $overtime->calculateTotalHours());
    }

    /** @test */
    public function it_belongs_to_a_shift()
    {
        $shift = Shift::factory()->create();
        $overtime = OverTime::factory()->create(['shift_id' => $shift->id]);

        $this->assertInstanceOf(Shift::class, $overtime->shift);
        $this->assertEquals($shift->id, $overtime->shift->id);
    }

    /** @test */
    public function it_prevents_negative_hours_with_excessive_breaks()
    {
        $overtime = OverTime::factory()->create([
            'start_time' => '17:00',
            'end_time' => '19:00', // 2 horas
            'break_minutes' => 180, // 3 horas (más que la duración)
        ]);

        // Debe retornar 0, no negativo
        $this->assertEquals(0.0, $overtime->calculateNetHours());
    }

    /** @test */
    public function scope_by_shift_filters_correctly()
    {
        $shift1 = Shift::factory()->create();
        $shift2 = Shift::factory()->create();

        OverTime::factory()->count(3)->create(['shift_id' => $shift1->id]);
        OverTime::factory()->count(2)->create(['shift_id' => $shift2->id]);

        $this->assertEquals(3, OverTime::byShift($shift1->id)->count());
        $this->assertEquals(2, OverTime::byShift($shift2->id)->count());
    }

    /** @test */
    public function scope_active_only_returns_future_overtimes()
    {
        OverTime::factory()->create(['date' => now()->addDays(5)]); // Futuro
        OverTime::factory()->create(['date' => now()->subDays(2)]); // Pasado

        $this->assertEquals(1, OverTime::active()->count());
    }
}
```

**Ejecutar:**
```bash
php artisan test --filter=OverTimeTest
```

---

### Tests de Integración Sugeridos

**Archivo:** `tests/Feature/OverTimeIntegrationTest.php`

```php
/** @test */
public function capacity_calculator_includes_overtime_hours()
{
    $shift = Shift::factory()->create([
        'start_time' => '08:00',
        'end_time' => '17:00', // 9 horas
    ]);

    // Crear overtime de 4 horas con 5 empleados
    OverTime::factory()->create([
        'shift_id' => $shift->id,
        'date' => now()->addDays(2),
        'start_time' => '17:00',
        'end_time' => '21:00',
        'break_minutes' => 0,
        'employees_qty' => 5,
    ]);

    $service = new CapacityCalculatorService();
    $hours = $service->calculateAvailableHours(
        $shift,
        now(),
        now()->addDays(5),
        10 // empleados base
    );

    // Base: 6 días × 9 horas × 10 empleados = 540
    // Overtime: 4 horas × 5 empleados = 20
    // Total: 560
    $this->assertEquals(560, $hours);
}
```

---

## Referencias

### Documentación del Proyecto

- **db.mkd:** `Diagramas_flujo/DB/db.mkd`
- **Spec 01:** Plan de Implementación Capacidad de Producción
- **Diagrama de Flujo:** `Diagramas_flujo/diagramas/2-diagrama-capacidad-disponible-produccion.mkd`

### Modelos Relacionados

- `app/Models/Shift.php` - Turno de trabajo
- `app/Models/Holiday.php` - Días festivos
- `app/Services/CapacityCalculatorService.php` - Cálculo de capacidad

### Tecnologías

- **Laravel:** 12.x
- **PHP:** 8.2+
- **Base de Datos:** MySQL 8.0+ / PostgreSQL 13+
- **Carbon:** 2.x (manipulación de fechas)

---

## Checklist de Implementación

### Base de Datos
- [ ] Migración `create_over_times_table` creada
- [ ] Campos verificados según especificación
- [ ] Índices agregados (shift_id, date, compuesto)
- [ ] FK a shifts configurada con cascade delete
- [ ] Migración ejecutada sin errores

### Modelo
- [ ] Modelo `OverTime` creado
- [ ] `$fillable` configurado
- [ ] `$casts` configurado (date, times, integers)
- [ ] Relación `belongsTo(Shift)` implementada
- [ ] Método `calculateNetHours()` implementado
- [ ] Método `calculateTotalHours()` implementado
- [ ] Scopes implementados (byShift, byDateRange, active, past)
- [ ] Accessors implementados (total_hours, net_hours)

### Shift Model
- [ ] Relación `hasMany(OverTime)` agregada
- [ ] Método `getTotalOvertimeHours()` agregado (opcional)

### Factory & Seeder
- [ ] Factory `OverTimeFactory` creado
- [ ] Estados (nightShift, weekend, short, long) implementados
- [ ] Seeder `OverTimeSeeder` creado
- [ ] Ejemplos realistas agregados
- [ ] Factory testeado en Tinker

### Validación
- [ ] Form Request `StoreOverTimeRequest` creado
- [ ] Form Request `UpdateOverTimeRequest` creado
- [ ] Custom Rule `AfterTimeOrNextDay` creado
- [ ] Reglas de validación verificadas

### Testing
- [ ] Tests unitarios escritos
- [ ] Test de medianoche verificado
- [ ] Test de relaciones verificado
- [ ] Test de scopes verificado
- [ ] Tests ejecutados exitosamente

### Integración
- [ ] CapacityCalculatorService usa Over_Time correctamente (ya implementado en Spec 01)
- [ ] No rompe funcionalidad existente
- [ ] Documentación actualizada

---

## Historial de Cambios

| Versión | Fecha | Autor | Cambios |
|---------|-------|-------|---------|
| 1.0 | 2024-12-24 | Agent Architect | Creación inicial del spec |

---

**Fin del Spec 07 - Análisis Técnico Over Time**
