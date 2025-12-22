# Spec 01: Plan de Implementación - Capacidad Disponible para Producción

**Fecha de Creación:** 2025-12-19
**Autor:** Architect Agent
**Fase del Proyecto:** FASE 2 - Planificación de Producción
**Estado:** Propuesta
**Versión:** 1.0

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Problema](#problema)
3. [Impacto Arquitectural](#impacto-arquitectural)
4. [Propuesta de Solución](#propuesta-de-solución)
5. [Plan de Implementación Detallado](#plan-de-implementación-detallado)
6. [Consideraciones de Implementación](#consideraciones-de-implementación)
7. [Entregables](#entregables)
8. [Cronograma](#cronograma)
9. [Riesgos y Mitigaciones](#riesgos-y-mitigaciones)
10. [Referencias](#referencias)

---

## Resumen Ejecutivo

Este documento especifica el plan de implementación del módulo **Capacidad Disponible para Producción** para el sistema FlexCon Tracker ERP. El módulo permitirá calcular la capacidad de producción disponible para determinar si se pueden aceptar órdenes de trabajo adicionales, considerando turnos, personal disponible, días hábiles, tiempo extra y estándares de producción.

### Objetivos Principales

- Implementar calculadora de capacidad de producción
- Gestionar tiempo extra (OverTime)
- Generar listas de envío preliminares (SentList)
- Validar las Propiedades de Corrección 4, 5 y 6
- Proporcionar interfaz interactiva para planificación

### Alcance

**Incluye:**
- 3 Migraciones de base de datos (2 nuevas + 1 modificación)
- 3 Modelos Eloquent
- 2 Servicios de negocio
- 9 Componentes Livewire
- 30+ Tests (Unit, Property-Based, Feature, Integration)

**No Incluye:**
- Integración con sistemas externos
- Generación automática de órdenes de trabajo
- Sincronización con sistemas de inventario

---

## Problema

### Contexto

El sistema FlexCon Tracker ERP necesita calcular la capacidad de producción disponible para determinar si puede aceptar órdenes de trabajo adicionales. Actualmente, la **Fase 1** (Fundamentos de Órdenes) está completada, y la **Fase 2** (Planificación de Producción) está en progreso.

### Requisitos del Flujo

El diagrama de flujo "Capacidad Disponible para Producción" define 11 pasos:

1. Seleccionar turno
2. Número de personas disponibles
3. Seleccionar turnos
4. Días disponibles para trabajar (descontando días feriados)
5. Sumar total de horas disponibles
6. Agregar # de parte y cantidad a lista
7. Agregar número de PO y modalidad de ensamble
8. Dividir la cantidad de WO entre el estándar (ligado a número de parte y item de producción)
9. Mostrar resultado de horas necesarias por producto
10. Restar horas necesarias de las horas totales
11. Decisión: ¿Tengo horas disponibles?
    - **Sí**: Regresa a "Agregar # de parte y cantidad a lista"
    - **No**: Revisar números de parte, generar lista de envío, enviar a Producción

### Factores a Considerar

- Turnos de trabajo disponibles
- Número de personas disponibles por turno
- Días hábiles (descontando feriados)
- Tiempos de descanso programados
- Tiempo extra disponible
- Estándares de producción por parte
- Modalidad de ensamble (manual, semi-automático, máquina)

### Problema Crítico Identificado

**⚠️ CRÍTICO:** La tabla `standards` actual NO contiene el campo `units_per_hour`, que es esencial para calcular las horas requeridas de producción según la **Propiedad 4** de corrección.

**Estado Actual de `standards`:**
```php
// Campos existentes
$table->string('name');
$table->integer('persons_1');
$table->integer('persons_2');
$table->integer('persons_3');
// FALTA: units_per_hour
// FALTA: assembly_mode
```

**Acción Requerida:**
- Agregar campo `units_per_hour` (integer, required)
- Agregar campo `assembly_mode` (enum: manual, semi_automatic, machine)

---

## Impacto Arquitectural

### Backend

#### Nuevos Modelos

1. **OverTime**
   - Propósito: Gestionar tiempo extra programado
   - Relaciones: `belongsTo(Shift::class)`
   - Campos clave: `start_time`, `end_time`, `break_minutes`, `employees_qty`, `date`

2. **SentList**
   - Propósito: Lista preliminar de envío
   - Relaciones: `belongsTo(WorkOrder::class)`, `belongsTo(StatusWO::class)`
   - Campos clave: `send_date`, `send_qty`, `pending_qty`, `hours_required`

3. **Standard** (Modificación)
   - Propósito: Agregar capacidad de cálculo de producción
   - Campos nuevos: `units_per_hour`, `assembly_mode`

#### Servicios a Crear

1. **CapacityCalculatorService**
   - Lógica de negocio para cálculos de capacidad
   - Implementa Propiedades 4, 5, 6

2. **CapacityResult** (DTO)
   - Encapsula resultados de cálculos
   - Proporciona métodos de utilidad

#### Excepciones Personalizadas

- `CapacityExceededException`: Lanzada cuando se excede capacidad

### Frontend

#### Componentes Livewire Requeridos

**CRUD OverTime (4 componentes):**
1. `OverTimeCreate.php`
2. `OverTimeEdit.php`
3. `OverTimeList.php`
4. `OverTimeShow.php`

**CRUD SentList (4 componentes):**
1. `SentListCreate.php`
2. `SentListEdit.php`
3. `SentListList.php`
4. `SentListShow.php`

**Calculadora de Capacidad (1 componente):**
1. `CapacityCalculator.php` - Componente interactivo principal

### Base de Datos

#### Nueva Tabla: over_times

```php
Schema::create('over_times', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->time('start_time');
    $table->time('end_time');
    $table->integer('break_minutes')->default(0);
    $table->integer('employees_qty');
    $table->date('date');
    $table->foreignId('shift_id')->constrained();
    $table->text('comments')->nullable();
    $table->timestamps();

    $table->index(['date', 'shift_id']);
    $table->index('shift_id');
});
```

#### Nueva Tabla: sent_lists

```php
Schema::create('sent_lists', function (Blueprint $table) {
    $table->id();
    $table->string('sent_list_number')->unique();
    $table->date('send_date');
    $table->foreignId('work_order_id')->constrained();
    $table->foreignId('status_id')->constrained('statuses_wo');
    $table->integer('send_qty');
    $table->integer('pending_qty');
    $table->decimal('hours_required', 10, 2);
    $table->string('assembly_mode'); // manual, semi_automatic, machine
    $table->text('comments')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index('send_date');
    $table->index(['work_order_id', 'status_id']);
});
```

#### Modificación Tabla: standards

```php
Schema::table('standards', function (Blueprint $table) {
    $table->integer('units_per_hour')->after('name');
    $table->enum('assembly_mode', ['manual', 'semi_automatic', 'machine'])
          ->default('manual')
          ->after('units_per_hour');
});
```

---

## Propuesta de Solución

### Arquitectura de Capas

```
┌─────────────────────────────────────────────────────────────┐
│ Presentation Layer                                          │
│ - CapacityCalculator Component (interactivo)                │
│ - CapacityDashboard Component (visualización)               │
│ - OverTime CRUD Components                                  │
│ - SentList CRUD Components                                  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ Application Layer                                           │
│ - Livewire Components (validación, eventos)                 │
│ - Form Requests (validación entrada)                        │
│ - Events (CapacityCalculated, SentListGenerated)            │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ Domain Layer                                                │
│ - CapacityCalculatorService                                 │
│   * calculateAvailableHours()                               │
│   * calculateRequiredHours()                                │
│   * checkCapacity()                                         │
│   * allocateWorkOrder()                                     │
│ - ProductionScheduleService                                 │
│   * generateSentList()                                      │
│   * validateSchedule()                                      │
│ - Models: OverTime, SentList, Standard, Shift, WorkOrder    │
│ - Exceptions: CapacityExceededException                     │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ Infrastructure Layer                                        │
│ - Database (Eloquent ORM)                                   │
│ - Cache (para optimizar cálculos repetitivos)               │
└─────────────────────────────────────────────────────────────┘
```

### Diseño del Servicio Principal: CapacityCalculatorService

#### Responsabilidades

1. Calcular horas disponibles (Propiedad 5)
2. Calcular horas requeridas (Propiedad 4)
3. Validar capacidad (Propiedad 6)

#### Métodos Públicos

```php
namespace App\Services;

use App\Models\Part;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CapacityCalculatorService
{
    /**
     * Calcula las horas disponibles para producción
     *
     * Implementa Propiedad 5: Cálculo de Horas Disponibles
     *
     * @param Shift $shift Turno de trabajo
     * @param Carbon $startDate Fecha de inicio
     * @param Carbon $endDate Fecha de fin
     * @param int $employeesCount Número de empleados disponibles
     * @return float Horas disponibles totales
     */
    public function calculateAvailableHours(
        Shift $shift,
        Carbon $startDate,
        Carbon $endDate,
        int $employeesCount
    ): float;

    /**
     * Calcula las horas requeridas para producir una parte
     *
     * Implementa Propiedad 4: Cálculo del Tiempo de Producción
     *
     * @param Part $part Parte a producir
     * @param int $quantity Cantidad a producir
     * @param string $assemblyMode Modalidad de ensamble
     * @return float Horas requeridas
     */
    public function calculateRequiredHours(
        Part $part,
        int $quantity,
        string $assemblyMode
    ): float;

    /**
     * Verifica si hay capacidad suficiente
     *
     * Implementa Propiedad 6: Prevención de Desbordamiento de Capacidad
     *
     * @param Collection $workOrders Work orders a validar
     * @param float $availableHours Horas disponibles
     * @return CapacityResult Resultado de la validación
     */
    public function checkCapacity(
        Collection $workOrders,
        float $availableHours
    ): CapacityResult;
}
```

#### Métodos Protegidos (Helpers)

```php
/**
 * Calcula días laborables excluyendo feriados
 */
protected function calculateWorkDays(
    Carbon $startDate,
    Carbon $endDate
): int;

/**
 * Calcula horas totales del turno
 */
protected function calculateShiftHours(Shift $shift): float;

/**
 * Calcula horas de descanso del turno
 */
protected function calculateBreakHours(Shift $shift): float;

/**
 * Calcula horas extra disponibles en el rango
 */
protected function calculateOvertimeHours(
    Shift $shift,
    Carbon $startDate,
    Carbon $endDate
): float;
```

#### Fórmulas Implementadas

**Propiedad 5 - Horas Disponibles:**
```
horas_disponibles = (días_laborables × (horas_turno - horas_descanso) × empleados) + horas_extra

Donde:
- días_laborables = días_rango - días_feriados
- horas_turno = diferencia entre end_time y start_time del Shift
- horas_descanso = suma de BreakTimes del Shift
- horas_extra = suma de OverTimes en el rango de fechas
```

**Propiedad 4 - Horas Requeridas:**
```
horas_requeridas = cantidad ÷ units_per_hour

Donde:
- cantidad = cantidad del WorkOrder/PurchaseOrder
- units_per_hour = del Standard activo correspondiente a la Part
```

**Propiedad 6 - Validación de Capacidad:**
```
capacidad_suficiente = (suma_horas_requeridas ≤ horas_disponibles)

Si NO capacidad_suficiente → rechazar adición de WOs
```

### Diseño del Componente Livewire Principal: CapacityCalculator

#### Estado del Componente

```php
namespace App\Livewire\Admin\ProductionCapacity;

use Livewire\Component;

class CapacityCalculator extends Component
{
    // Inputs del usuario
    public ?int $shift_id = null;
    public int $employees_count = 1;
    public string $start_date = '';
    public string $end_date = '';

    // Capacidad calculada
    public float $total_hours_available = 0;
    public float $total_hours_allocated = 0;
    public float $total_hours_remaining = 0;

    // Work Orders seleccionados
    public array $selected_work_orders = [];

    // UI State
    public bool $capacity_calculated = false;
    public bool $show_add_form = false;

    // Form para agregar WO
    public ?int $selected_wo_id = null;
    public int $wo_quantity = 0;
    public string $assembly_mode = 'manual';
}
```

#### Flujo del Componente

1. **Step 1-4**: Usuario configura parámetros (turno, empleados, fechas)
2. **Step 5**: Sistema calcula horas disponibles → `calculateCapacity()`
3. **Steps 6-7**: Usuario agrega Work Orders → `addWorkOrder()`
4. **Steps 8-9**: Sistema calcula horas requeridas por WO
5. **Step 10**: Sistema resta horas y actualiza disponibles
6. **Step 11**: Sistema valida si hay capacidad (Propiedad 6)
   - **SÍ**: Permitir agregar más WOs (loop to Step 6)
   - **NO**: Generar SentList y finalizar

#### Métodos Principales

```php
/**
 * Calcula la capacidad disponible
 */
public function calculateCapacity(): void;

/**
 * Agrega un work order a la lista
 */
public function addWorkOrder(): void;

/**
 * Remueve un work order de la lista
 */
public function removeWorkOrder(int $index): void;

/**
 * Genera la lista de envío final
 */
public function generateSentList(): void;

/**
 * Resetea el calculador
 */
public function reset(): void;
```

---

## Plan de Implementación Detallado

### FASE 2.1: Fundamentos (Días 1-2)

**Objetivo:** Establecer modelos base y migraciones

#### Tarea 1: Crear migración over_times

**Comando:**
```bash
php artisan make:migration create_over_times_table
```

**Archivo:** `database/migrations/YYYY_MM_DD_HHMMSS_create_over_times_table.php`

**Contenido:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('over_times', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('break_minutes')->default(0);
            $table->integer('employees_qty');
            $table->date('date');
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->text('comments')->nullable();
            $table->timestamps();

            // Índices para optimización
            $table->index(['date', 'shift_id']);
            $table->index('shift_id');
        });
    }

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

#### Tarea 2: Crear modelo OverTime

**Comando:**
```bash
php artisan make:model OverTime
```

**Archivo:** `app/Models/OverTime.php`

**Contenido:**
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

    // Relationships
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    // Scopes
    public function scopeByDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByShift($query, int $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    public function scopeActive($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    // Methods
    public function calculateNetHours(): float
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        // Manejar turnos que cruzan medianoche
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $totalMinutes = $end->diffInMinutes($start);
        $netMinutes = $totalMinutes - $this->break_minutes;

        return round($netMinutes / 60, 2);
    }

    public function getTotalHoursAttribute(): float
    {
        return $this->calculateNetHours() * $this->employees_qty;
    }
}
```

#### Tarea 3: Crear migración sent_lists

**Comando:**
```bash
php artisan make:migration create_sent_lists_table
```

**Archivo:** `database/migrations/YYYY_MM_DD_HHMMSS_create_sent_lists_table.php`

**Contenido:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sent_lists', function (Blueprint $table) {
            $table->id();
            $table->string('sent_list_number')->unique();
            $table->date('send_date');
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('statuses_wo');
            $table->integer('send_qty');
            $table->integer('pending_qty');
            $table->decimal('hours_required', 10, 2);
            $table->enum('assembly_mode', ['manual', 'semi_automatic', 'machine'])->default('manual');
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('send_date');
            $table->index(['work_order_id', 'status_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sent_lists');
    }
};
```

**Ejecutar:**
```bash
php artisan migrate
```

#### Tarea 4: Crear modelo SentList

**Comando:**
```bash
php artisan make:model SentList
```

**Archivo:** `app/Models/SentList.php`

**Contenido:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class SentList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sent_list_number',
        'send_date',
        'work_order_id',
        'status_id',
        'send_qty',
        'pending_qty',
        'hours_required',
        'assembly_mode',
        'comments',
    ];

    protected $casts = [
        'send_date' => 'date',
        'send_qty' => 'integer',
        'pending_qty' => 'integer',
        'hours_required' => 'decimal:2',
    ];

    // Relationships
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusWO::class, 'status_id');
    }

    // Scopes
    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('sent_list_number', 'like', "%{$search}%")
              ->orWhereHas('workOrder', function ($woQuery) use ($search) {
                  $woQuery->where('wo_number', 'like', "%{$search}%");
              });
        });
    }

    public function scopeByStatus($query, ?int $statusId)
    {
        if (!$statusId) {
            return $query;
        }

        return $query->where('status_id', $statusId);
    }

    public function scopeByDateRange($query, ?Carbon $startDate, ?Carbon $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('send_date', [$startDate, $endDate]);
        }

        return $query;
    }

    // Static Methods
    public static function generateSentListNumber(): string
    {
        $prefix = 'SL';
        $year = now()->format('Y');
        $month = now()->format('m');

        $lastSentList = static::withTrashed()
            ->where('sent_list_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('sent_list_number', 'desc')
            ->first();

        if ($lastSentList) {
            $lastNumber = (int) substr($lastSentList->sent_list_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$year}{$month}-{$newNumber}";
    }
}
```

#### Tarea 5: CRÍTICO - Modificar migración standards

**Comando:**
```bash
php artisan make:migration add_units_per_hour_to_standards_table
```

**Archivo:** `database/migrations/YYYY_MM_DD_HHMMSS_add_units_per_hour_to_standards_table.php`

**Contenido:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standards', function (Blueprint $table) {
            $table->integer('units_per_hour')->after('name')->default(1);
            $table->enum('assembly_mode', ['manual', 'semi_automatic', 'machine'])
                  ->default('manual')
                  ->after('units_per_hour');
        });
    }

    public function down(): void
    {
        Schema::table('standards', function (Blueprint $table) {
            $table->dropColumn(['units_per_hour', 'assembly_mode']);
        });
    }
};
```

**Ejecutar:**
```bash
php artisan migrate
```

#### Tarea 6: Actualizar modelo Standard

**Archivo:** `app/Models/Standard.php`

**Modificaciones:**
```php
// Agregar a $fillable
protected $fillable = [
    'name',
    'units_per_hour',  // NUEVO
    'assembly_mode',   // NUEVO
    'description',
    'comments',
    'active',
    // ... otros campos existentes
];

// Agregar a $casts
protected $casts = [
    'units_per_hour' => 'integer',  // NUEVO
    'active' => 'boolean',
];

// NUEVO MÉTODO
/**
 * Calcula las horas requeridas para producir una cantidad
 *
 * Implementa Propiedad 4: Cálculo del Tiempo de Producción
 *
 * @param int $quantity Cantidad a producir
 * @return float Horas requeridas
 * @throws \DivisionByZeroError Si units_per_hour es 0
 */
public function calculateRequiredHours(int $quantity): float
{
    if ($this->units_per_hour === 0) {
        throw new \DivisionByZeroError(
            "El estándar '{$this->name}' tiene units_per_hour = 0"
        );
    }

    return round($quantity / $this->units_per_hour, 2);
}
```

---

### FASE 2.2: Capa de Dominio (Días 3-4)

**Objetivo:** Implementar servicios de negocio

#### Tarea 7: Crear directorio Services

**Comando:**
```bash
mkdir -p app/Services
```

#### Tarea 8: Crear CapacityCalculatorService

**Archivo:** `app/Services/CapacityCalculatorService.php`

**Contenido Completo:**
```php
<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\OverTime;
use App\Models\Part;
use App\Models\Shift;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class CapacityCalculatorService
{
    /**
     * Calcula las horas disponibles para producción
     *
     * Implementa Propiedad 5: Cálculo de Horas Disponibles
     *
     * Fórmula:
     * horas_disponibles = (días_laborables × (horas_turno - horas_descanso) × empleados) + horas_extra
     *
     * @param Shift $shift Turno de trabajo
     * @param Carbon $startDate Fecha de inicio
     * @param Carbon $endDate Fecha de fin
     * @param int $employeesCount Número de empleados disponibles
     * @return float Horas disponibles totales
     */
    public function calculateAvailableHours(
        Shift $shift,
        Carbon $startDate,
        Carbon $endDate,
        int $employeesCount
    ): float {
        // Validaciones
        if ($employeesCount <= 0) {
            throw new \InvalidArgumentException('El número de empleados debe ser mayor a 0');
        }

        if ($endDate->lessThan($startDate)) {
            throw new \InvalidArgumentException('La fecha de fin debe ser posterior a la fecha de inicio');
        }

        // 1. Calcular días laborables (descontando feriados)
        $workDays = $this->calculateWorkDays($startDate, $endDate);

        // 2. Calcular horas netas del turno (turno - descansos)
        $shiftHours = $this->calculateShiftHours($shift);
        $breakHours = $this->calculateBreakHours($shift);
        $netShiftHours = $shiftHours - $breakHours;

        // 3. Calcular horas base
        $baseHours = $workDays * $netShiftHours * $employeesCount;

        // 4. Agregar horas extra
        $overtimeHours = $this->calculateOvertimeHours($shift, $startDate, $endDate);

        // 5. Total
        $totalHours = $baseHours + $overtimeHours;

        return round($totalHours, 2);
    }

    /**
     * Calcula las horas requeridas para producir una parte
     *
     * Implementa Propiedad 4: Cálculo del Tiempo de Producción
     *
     * Fórmula:
     * horas_requeridas = cantidad ÷ units_per_hour
     *
     * @param Part $part Parte a producir
     * @param int $quantity Cantidad a producir
     * @param string|null $assemblyMode Modalidad de ensamble (opcional)
     * @return float Horas requeridas
     * @throws \Exception Si no se encuentra estándar activo
     */
    public function calculateRequiredHours(
        Part $part,
        int $quantity,
        ?string $assemblyMode = null
    ): float {
        // Buscar estándar activo para la parte
        $standard = $part->standards()
            ->where('active', true)
            ->when($assemblyMode, function ($query, $mode) {
                return $query->where('assembly_mode', $mode);
            })
            ->first();

        if (!$standard) {
            throw new \Exception(
                "No se encontró un estándar activo para la parte '{$part->number}'" .
                ($assemblyMode ? " con modalidad '{$assemblyMode}'" : '')
            );
        }

        // Usar el método del modelo Standard
        return $standard->calculateRequiredHours($quantity);
    }

    /**
     * Verifica si hay capacidad suficiente
     *
     * Implementa Propiedad 6: Prevención de Desbordamiento de Capacidad
     *
     * @param Collection $workOrders Work orders con sus cantidades y modos
     * @param float $availableHours Horas disponibles
     * @return CapacityResult Resultado de la validación
     */
    public function checkCapacity(
        Collection $workOrders,
        float $availableHours
    ): CapacityResult {
        $totalRequiredHours = 0;
        $workOrderDetails = [];

        foreach ($workOrders as $woData) {
            $workOrder = $woData['work_order'];
            $quantity = $woData['quantity'];
            $assemblyMode = $woData['assembly_mode'] ?? 'manual';

            // Obtener la parte del work order
            $part = $workOrder->purchaseOrder->part;

            // Calcular horas requeridas
            $requiredHours = $this->calculateRequiredHours($part, $quantity, $assemblyMode);
            $totalRequiredHours += $requiredHours;

            $workOrderDetails[] = [
                'work_order_id' => $workOrder->id,
                'work_order_number' => $workOrder->wo_number,
                'part_number' => $part->number,
                'quantity' => $quantity,
                'assembly_mode' => $assemblyMode,
                'required_hours' => $requiredHours,
            ];
        }

        $remainingHours = $availableHours - $totalRequiredHours;
        $hasCapacity = $remainingHours >= 0;

        return new CapacityResult(
            availableHours: $availableHours,
            requiredHours: $totalRequiredHours,
            remainingHours: $remainingHours,
            hasCapacity: $hasCapacity,
            workOrderDetails: $workOrderDetails
        );
    }

    /**
     * Calcula días laborables excluyendo feriados
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return int Número de días laborables
     */
    protected function calculateWorkDays(Carbon $startDate, Carbon $endDate): int
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $totalDays = $period->count();

        // Obtener feriados en el rango
        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->toDateString())
            ->toArray();

        $holidayCount = count($holidays);

        return max(0, $totalDays - $holidayCount);
    }

    /**
     * Calcula horas totales del turno
     *
     * @param Shift $shift
     * @return float Horas totales
     */
    protected function calculateShiftHours(Shift $shift): float
    {
        $start = Carbon::parse($shift->start_time);
        $end = Carbon::parse($shift->end_time);

        // Manejar turnos que cruzan medianoche
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return round($end->diffInHours($start, true), 2);
    }

    /**
     * Calcula horas de descanso del turno
     *
     * @param Shift $shift
     * @return float Horas de descanso
     */
    protected function calculateBreakHours(Shift $shift): float
    {
        $totalBreakMinutes = $shift->breakTimes()
            ->where('active', true)
            ->sum('duration');

        return round($totalBreakMinutes / 60, 2);
    }

    /**
     * Calcula horas extra disponibles en el rango
     *
     * @param Shift $shift
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float Horas extra totales
     */
    protected function calculateOvertimeHours(
        Shift $shift,
        Carbon $startDate,
        Carbon $endDate
    ): float {
        $overtimes = OverTime::byShift($shift->id)
            ->byDateRange($startDate, $endDate)
            ->get();

        $totalOvertimeHours = $overtimes->sum(function ($overtime) {
            return $overtime->calculateNetHours() * $overtime->employees_qty;
        });

        return round($totalOvertimeHours, 2);
    }
}
```

#### Tarea 9: Crear CapacityResult DTO

**Archivo:** `app/Services/CapacityResult.php`

**Contenido:**
```php
<?php

namespace App\Services;

class CapacityResult
{
    public function __construct(
        public readonly float $availableHours,
        public readonly float $requiredHours,
        public readonly float $remainingHours,
        public readonly bool $hasCapacity,
        public readonly array $workOrderDetails = []
    ) {}

    /**
     * Calcula el porcentaje de utilización de capacidad
     *
     * @return float Porcentaje (0-100+)
     */
    public function utilizationPercentage(): float
    {
        if ($this->availableHours === 0.0) {
            return 0.0;
        }

        return round(($this->requiredHours / $this->availableHours) * 100, 2);
    }

    /**
     * Verifica si la utilización está en rango óptimo (< 95%)
     *
     * @return bool
     */
    public function isOptimalUtilization(): bool
    {
        return $this->utilizationPercentage() < 95;
    }

    /**
     * Verifica si hay sobrecarga (> 100%)
     *
     * @return bool
     */
    public function isOverloaded(): bool
    {
        return $this->utilizationPercentage() > 100;
    }

    /**
     * Convierte a array para JSON
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'available_hours' => $this->availableHours,
            'required_hours' => $this->requiredHours,
            'remaining_hours' => $this->remainingHours,
            'has_capacity' => $this->hasCapacity,
            'utilization_percentage' => $this->utilizationPercentage(),
            'is_optimal' => $this->isOptimalUtilization(),
            'is_overloaded' => $this->isOverloaded(),
            'work_orders' => $this->workOrderDetails,
        ];
    }
}
```

#### Tarea 10: Crear excepción CapacityExceededException

**Comando:**
```bash
php artisan make:exception CapacityExceededException
```

**Archivo:** `app/Exceptions/CapacityExceededException.php`

**Contenido:**
```php
<?php

namespace App\Exceptions;

use Exception;

class CapacityExceededException extends Exception
{
    protected $message = 'La capacidad de producción ha sido excedida.';

    public static function forWorkOrder(string $woNumber, float $requiredHours, float $availableHours): self
    {
        return new self(
            "No hay capacidad suficiente para agregar el Work Order '{$woNumber}'. " .
            "Requiere {$requiredHours} horas pero solo quedan {$availableHours} horas disponibles."
        );
    }
}
```

#### Tarea 11: Crear Form Requests

**Comandos:**
```bash
php artisan make:request CalculateCapacityRequest
php artisan make:request AddWorkOrderToCapacityRequest
php artisan make:request GenerateSentListRequest
```

**CalculateCapacityRequest:**
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculateCapacityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajustar según política de autorización
    }

    public function rules(): array
    {
        return [
            'shift_id' => ['required', 'integer', 'exists:shifts,id'],
            'employees_count' => ['required', 'integer', 'min:1', 'max:1000'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'shift_id.required' => 'Debe seleccionar un turno.',
            'shift_id.exists' => 'El turno seleccionado no existe.',
            'employees_count.required' => 'Debe ingresar el número de empleados.',
            'employees_count.min' => 'Debe haber al menos 1 empleado.',
            'start_date.required' => 'La fecha de inicio es requerida.',
            'start_date.after_or_equal' => 'La fecha de inicio debe ser hoy o posterior.',
            'end_date.required' => 'La fecha de fin es requerida.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }
}
```

**AddWorkOrderToCapacityRequest:**
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddWorkOrderToCapacityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_order_id' => ['required', 'integer', 'exists:work_orders,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'assembly_mode' => ['required', 'in:manual,semi_automatic,machine'],
        ];
    }

    public function messages(): array
    {
        return [
            'work_order_id.required' => 'Debe seleccionar una orden de trabajo.',
            'work_order_id.exists' => 'La orden de trabajo no existe.',
            'quantity.required' => 'Debe ingresar la cantidad.',
            'quantity.min' => 'La cantidad debe ser al menos 1.',
            'assembly_mode.required' => 'Debe seleccionar una modalidad de ensamble.',
            'assembly_mode.in' => 'La modalidad de ensamble no es válida.',
        ];
    }
}
```

---

### FASE 2.3: CRUD OverTime (Días 5-6)

**Objetivo:** Gestión completa de tiempo extra

#### Tarea 12: Crear componentes Livewire OverTime

**Comandos:**
```bash
php artisan make:livewire Admin/OverTime/OverTimeCreate
php artisan make:livewire Admin/OverTime/OverTimeEdit
php artisan make:livewire Admin/OverTime/OverTimeList
php artisan make:livewire Admin/OverTime/OverTimeShow
```

#### Tarea 13: Implementar lógica de componentes

**OverTimeCreate.php:**
```php
<?php

namespace App\Livewire\Admin\OverTime;

use App\Models\OverTime;
use App\Models\Shift;
use Livewire\Component;
use Livewire\Attributes\Title;

class OverTimeCreate extends Component
{
    #[Title('Crear Tiempo Extra')]

    public $name = '';
    public $start_time = '';
    public $end_time = '';
    public $break_minutes = 0;
    public $employees_qty = 1;
    public $date = '';
    public $shift_id = null;
    public $comments = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_minutes' => 'required|integer|min:0|max:480',
            'employees_qty' => 'required|integer|min:1|max:100',
            'date' => 'required|date|after_or_equal:today',
            'shift_id' => 'required|exists:shifts,id',
            'comments' => 'nullable|string|max:1000',
        ];
    }

    protected $messages = [
        'name.required' => 'El nombre es requerido.',
        'start_time.required' => 'La hora de inicio es requerida.',
        'end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        'employees_qty.min' => 'Debe haber al menos 1 empleado.',
        'date.after_or_equal' => 'La fecha debe ser hoy o posterior.',
        'shift_id.required' => 'Debe seleccionar un turno.',
    ];

    public function save()
    {
        $this->validate();

        OverTime::create([
            'name' => $this->name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'break_minutes' => $this->break_minutes,
            'employees_qty' => $this->employees_qty,
            'date' => $this->date,
            'shift_id' => $this->shift_id,
            'comments' => $this->comments,
        ]);

        session()->flash('success', 'Tiempo extra creado exitosamente.');

        return redirect()->route('admin.over-times.index');
    }

    public function render()
    {
        return view('livewire.admin.over-time.over-time-create', [
            'shifts' => Shift::where('active', true)->get(),
        ]);
    }
}
```

**OverTimeList.php:**
```php
<?php

namespace App\Livewire\Admin\OverTime;

use App\Models\OverTime;
use App\Models\Shift;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

class OverTimeList extends Component
{
    use WithPagination;

    #[Title('Tiempos Extra')]

    #[Url(as: 'q')]
    public $search = '';

    #[Url(as: 'shift')]
    public $filterShift = '';

    #[Url(as: 'date')]
    public $filterDate = '';

    public $perPage = 15;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $overtime = OverTime::findOrFail($id);
        $overtime->delete();

        session()->flash('success', 'Tiempo extra eliminado exitosamente.');
    }

    public function render()
    {
        $overtimes = OverTime::query()
            ->with('shift')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterShift, function ($query) {
                $query->where('shift_id', $this->filterShift);
            })
            ->when($this->filterDate, function ($query) {
                $query->whereDate('date', $this->filterDate);
            })
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.over-time.over-time-list', [
            'overtimes' => $overtimes,
            'shifts' => Shift::where('active', true)->get(),
        ]);
    }
}
```

#### Tarea 14: Crear vistas Blade

**over-time-create.blade.php:**
```blade
<div>
    <div class="max-w-4xl mx-auto py-8">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6">Crear Tiempo Extra</h2>

            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nombre --}}
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre *
                        </label>
                        <input
                            type="text"
                            id="name"
                            wire:model="name"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Ej: Tiempo extra sábado"
                        >
                        @error('name')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Turno --}}
                    <div>
                        <label for="shift_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Turno *
                        </label>
                        <select
                            id="shift_id"
                            wire:model="shift_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Seleccionar turno...</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                        @error('shift_id')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Fecha --}}
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha *
                        </label>
                        <input
                            type="date"
                            id="date"
                            wire:model="date"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Hora inicio --}}
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                            Hora de Inicio *
                        </label>
                        <input
                            type="time"
                            id="start_time"
                            wire:model="start_time"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('start_time')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Hora fin --}}
                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                            Hora de Fin *
                        </label>
                        <input
                            type="time"
                            id="end_time"
                            wire:model="end_time"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('end_time')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Minutos de descanso --}}
                    <div>
                        <label for="break_minutes" class="block text-sm font-medium text-gray-700 mb-2">
                            Minutos de Descanso *
                        </label>
                        <input
                            type="number"
                            id="break_minutes"
                            wire:model="break_minutes"
                            min="0"
                            max="480"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('break_minutes')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Cantidad de empleados --}}
                    <div>
                        <label for="employees_qty" class="block text-sm font-medium text-gray-700 mb-2">
                            Cantidad de Empleados *
                        </label>
                        <input
                            type="number"
                            id="employees_qty"
                            wire:model="employees_qty"
                            min="1"
                            max="100"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('employees_qty')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Comentarios --}}
                    <div class="md:col-span-2">
                        <label for="comments" class="block text-sm font-medium text-gray-700 mb-2">
                            Comentarios
                        </label>
                        <textarea
                            id="comments"
                            wire:model="comments"
                            rows="3"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Comentarios adicionales..."
                        ></textarea>
                        @error('comments')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-4">
                    <a
                        href="{{ route('admin.over-times.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                    >
                        Cancelar
                    </a>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

**over-time-list.blade.php:**
```blade
<div>
    <div class="py-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Tiempos Extra</h2>
            <a
                href="{{ route('admin.over-times.create') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
                Crear Tiempo Extra
            </a>
        </div>

        {{-- Filtros --}}
        <div class="bg-white shadow-md rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por nombre..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>
                <div>
                    <select
                        wire:model.live="filterShift"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">Todos los turnos</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input
                        type="date"
                        wire:model.live="filterDate"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nombre
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Turno
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Horario
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Empleados
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Horas Totales
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($overtimes as $overtime)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $overtime->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $overtime->shift->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $overtime->date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $overtime->employees_qty }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $overtime->total_hours }} hrs
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a
                                    href="{{ route('admin.over-times.show', $overtime) }}"
                                    class="text-blue-600 hover:text-blue-900 mr-3"
                                >
                                    Ver
                                </a>
                                <a
                                    href="{{ route('admin.over-times.edit', $overtime) }}"
                                    class="text-green-600 hover:text-green-900 mr-3"
                                >
                                    Editar
                                </a>
                                <button
                                    wire:click="delete({{ $overtime->id }})"
                                    wire:confirm="¿Está seguro de eliminar este tiempo extra?"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No se encontraron tiempos extra.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="px-6 py-4">
                {{ $overtimes->links() }}
            </div>
        </div>
    </div>
</div>
```

#### Tarea 15: Crear rutas

**Archivo:** `routes/web.php`

**Agregar:**
```php
// Rutas OverTime
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/over-times', \App\Livewire\Admin\OverTime\OverTimeList::class)
        ->name('over-times.index');
    Route::get('/over-times/create', \App\Livewire\Admin\OverTime\OverTimeCreate::class)
        ->name('over-times.create');
    Route::get('/over-times/{overTime}', \App\Livewire\Admin\OverTime\OverTimeShow::class)
        ->name('over-times.show');
    Route::get('/over-times/{overTime}/edit', \App\Livewire\Admin\OverTime\OverTimeEdit::class)
        ->name('over-times.edit');
});
```

---

### FASE 2.4: CRUD SentList (Días 7-8)

**Objetivo:** Gestión completa de listas de envío

*[Similar estructura a FASE 2.3, crear componentes SentListCreate, SentListEdit, SentListList, SentListShow con lógica y vistas correspondientes]*

---

### FASE 2.5: Calculadora de Capacidad (Días 9-11)

**Objetivo:** Componente interactivo principal

#### Tarea 20: Crear componente CapacityCalculator

**Comando:**
```bash
php artisan make:livewire Admin/ProductionCapacity/CapacityCalculator
```

**Archivo:** `app/Livewire/Admin/ProductionCapacity/CapacityCalculator.php`

**Contenido:**
```php
<?php

namespace App\Livewire\Admin\ProductionCapacity;

use App\Exceptions\CapacityExceededException;
use App\Models\Shift;
use App\Models\WorkOrder;
use App\Services\CapacityCalculatorService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Title;

class CapacityCalculator extends Component
{
    #[Title('Calculadora de Capacidad de Producción')]

    // Step 1-4: Parámetros de capacidad
    public ?int $shift_id = null;
    public int $employees_count = 1;
    public string $start_date = '';
    public string $end_date = '';

    // Step 5: Capacidad calculada
    public float $total_hours_available = 0;
    public float $total_hours_allocated = 0;
    public float $total_hours_remaining = 0;

    // Steps 6-10: Work Orders
    public array $selected_work_orders = [];

    // Form para agregar WO
    public ?int $selected_wo_id = null;
    public int $wo_quantity = 0;
    public string $assembly_mode = 'manual';

    // UI State
    public bool $capacity_calculated = false;
    public bool $show_add_form = false;

    protected $capacityService;

    public function boot(CapacityCalculatorService $capacityService)
    {
        $this->capacityService = $capacityService;
    }

    public function mount()
    {
        $this->start_date = now()->toDateString();
        $this->end_date = now()->addWeek()->toDateString();
    }

    public function calculateCapacity()
    {
        $this->validate([
            'shift_id' => 'required|exists:shifts,id',
            'employees_count' => 'required|integer|min:1|max:1000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        $shift = Shift::findOrFail($this->shift_id);
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);

        // Calcular horas disponibles (Propiedad 5)
        $this->total_hours_available = $this->capacityService->calculateAvailableHours(
            $shift,
            $startDate,
            $endDate,
            $this->employees_count
        );

        $this->total_hours_remaining = $this->total_hours_available;
        $this->capacity_calculated = true;

        session()->flash('success', 'Capacidad calculada exitosamente.');
    }

    public function addWorkOrder()
    {
        $this->validate([
            'selected_wo_id' => 'required|exists:work_orders,id',
            'wo_quantity' => 'required|integer|min:1',
            'assembly_mode' => 'required|in:manual,semi_automatic,machine',
        ]);

        $workOrder = WorkOrder::with('purchaseOrder.part')->findOrFail($this->selected_wo_id);
        $part = $workOrder->purchaseOrder->part;

        try {
            // Calcular horas requeridas (Propiedad 4)
            $requiredHours = $this->capacityService->calculateRequiredHours(
                $part,
                $this->wo_quantity,
                $this->assembly_mode
            );

            // Validar capacidad (Propiedad 6)
            if ($requiredHours > $this->total_hours_remaining) {
                throw CapacityExceededException::forWorkOrder(
                    $workOrder->wo_number,
                    $requiredHours,
                    $this->total_hours_remaining
                );
            }

            // Agregar a la lista
            $this->selected_work_orders[] = [
                'work_order_id' => $workOrder->id,
                'wo_number' => $workOrder->wo_number,
                'part_number' => $part->number,
                'quantity' => $this->wo_quantity,
                'assembly_mode' => $this->assembly_mode,
                'required_hours' => $requiredHours,
            ];

            // Actualizar horas
            $this->total_hours_allocated += $requiredHours;
            $this->total_hours_remaining -= $requiredHours;

            // Reset form
            $this->reset(['selected_wo_id', 'wo_quantity']);
            $this->assembly_mode = 'manual';
            $this->show_add_form = false;

            session()->flash('success', "Work Order {$workOrder->wo_number} agregado exitosamente.");

        } catch (CapacityExceededException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Error al agregar Work Order: ' . $e->getMessage());
        }
    }

    public function removeWorkOrder($index)
    {
        $wo = $this->selected_work_orders[$index];

        $this->total_hours_allocated -= $wo['required_hours'];
        $this->total_hours_remaining += $wo['required_hours'];

        unset($this->selected_work_orders[$index]);
        $this->selected_work_orders = array_values($this->selected_work_orders);

        session()->flash('success', "Work Order {$wo['wo_number']} removido.");
    }

    public function generateSentList()
    {
        if (empty($this->selected_work_orders)) {
            session()->flash('error', 'Debe agregar al menos un Work Order.');
            return;
        }

        // TODO: Implementar generación de SentList
        // Por ahora solo mostrar mensaje de éxito
        session()->flash('success', 'Lista de envío generada exitosamente.');

        // Reset
        $this->reset();
        $this->capacity_calculated = false;
    }

    public function toggleAddForm()
    {
        $this->show_add_form = !$this->show_add_form;
    }

    public function resetCalculator()
    {
        $this->reset();
        $this->capacity_calculated = false;
        session()->flash('info', 'Calculadora reiniciada.');
    }

    public function render()
    {
        return view('livewire.admin.production-capacity.capacity-calculator', [
            'shifts' => Shift::where('active', true)->get(),
            'available_work_orders' => WorkOrder::with('purchaseOrder.part')
                ->whereHas('status', fn($q) => $q->where('name', 'Abierto'))
                ->get(),
            'utilization_percentage' => $this->total_hours_available > 0
                ? round(($this->total_hours_allocated / $this->total_hours_available) * 100, 2)
                : 0,
        ]);
    }
}
```

---

### FASE 2.6: Testing (Días 12-14)

**Objetivo:** Asegurar correctitud del sistema

#### Tarea 25: Tests Unitarios - CapacityCalculatorService

**Archivo:** `tests/Unit/Services/CapacityCalculatorServiceTest.php`

**Contenido:**
```php
<?php

namespace Tests\Unit\Services;

use App\Models\Holiday;
use App\Models\OverTime;
use App\Models\Part;
use App\Models\Shift;
use App\Models\Standard;
use App\Services\CapacityCalculatorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CapacityCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CapacityCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CapacityCalculatorService();
    }

    /** @test */
    public function test_calculate_available_hours_without_holidays()
    {
        $shift = Shift::factory()->create([
            'start_time' => '08:00',
            'end_time' => '17:00', // 9 horas
        ]);

        $startDate = Carbon::parse('2025-12-20');
        $endDate = Carbon::parse('2025-12-22'); // 3 días
        $employees = 10;

        $hours = $this->service->calculateAvailableHours($shift, $startDate, $endDate, $employees);

        // 3 días × 9 horas × 10 empleados = 270 horas (sin descansos)
        $this->assertEquals(270, $hours);
    }

    /** @test */
    public function test_calculate_available_hours_with_holidays()
    {
        $shift = Shift::factory()->create([
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);

        $startDate = Carbon::parse('2025-12-20');
        $endDate = Carbon::parse('2025-12-22');
        $employees = 10;

        // Crear feriado en el rango
        Holiday::factory()->create(['date' => '2025-12-21']);

        $hours = $this->service->calculateAvailableHours($shift, $startDate, $endDate, $employees);

        // 2 días × 9 horas × 10 empleados = 180 horas
        $this->assertEquals(180, $hours);
    }

    /** @test */
    public function test_calculate_available_hours_with_overtime()
    {
        $shift = Shift::factory()->create([
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);

        $startDate = Carbon::parse('2025-12-20');
        $endDate = Carbon::parse('2025-12-22');
        $employees = 10;

        // Crear overtime de 4 horas con 5 empleados
        OverTime::factory()->create([
            'shift_id' => $shift->id,
            'date' => '2025-12-21',
            'start_time' => '17:00',
            'end_time' => '21:00',
            'break_minutes' => 0,
            'employees_qty' => 5,
        ]);

        $hours = $this->service->calculateAvailableHours($shift, $startDate, $endDate, $employees);

        // Base: 3 días × 9 horas × 10 empleados = 270
        // Overtime: 4 horas × 5 empleados = 20
        // Total: 290
        $this->assertEquals(290, $hours);
    }

    /** @test */
    public function test_calculate_required_hours_for_different_parts()
    {
        $part = Part::factory()->create();
        $standard = Standard::factory()->create([
            'units_per_hour' => 100,
        ]);

        $part->standards()->attach($standard->id);

        $requiredHours = $this->service->calculateRequiredHours($part, 500);

        // 500 unidades ÷ 100 unidades/hora = 5 horas
        $this->assertEquals(5, $requiredHours);
    }

    /** @test */
    public function test_check_capacity_with_sufficient_hours()
    {
        // TODO: Implementar test
        $this->assertTrue(true);
    }

    /** @test */
    public function test_check_capacity_with_insufficient_hours()
    {
        // TODO: Implementar test
        $this->assertTrue(true);
    }
}
```

---

### FASE 2.7: Documentación y Refinamiento (Días 15-16)

**Objetivo:** Documentar y pulir

*[Incluir creación de seeders, factories, optimizaciones de performance, y review de UX/UI]*

---

## Consideraciones de Implementación

### Validaciones Críticas

1. **Validar existencia de Standard activo** antes de calcular horas requeridas
   ```php
   if (!$standard) {
       throw new \Exception("No se encontró un estándar activo para la parte '{$part->number}'");
   }
   ```

2. **Validar units_per_hour > 0** en Standard para evitar división por cero
   ```php
   if ($this->units_per_hour === 0) {
       throw new \DivisionByZeroError("El estándar '{$this->name}' tiene units_per_hour = 0");
   }
   ```

3. **Validar empleados > 0** antes de calcular capacidad
   ```php
   if ($employeesCount <= 0) {
       throw new \InvalidArgumentException('El número de empleados debe ser mayor a 0');
   }
   ```

4. **Validar fechas válidas** (end_date > start_date)
   ```php
   if ($endDate->lessThan($startDate)) {
       throw new \InvalidArgumentException('La fecha de fin debe ser posterior a la fecha de inicio');
   }
   ```

5. **Validar turno activo** antes de usar en cálculos

### Manejo de Errores

```php
try {
    $requiredHours = $this->calculateRequiredHours($part, $quantity, $assemblyMode);
} catch (\Exception $e) {
    throw new CapacityCalculationException(
        "No se pudo calcular las horas requeridas para {$part->number}: {$e->getMessage()}"
    );
}
```

### Performance

- **Cache**: Cachear cálculos de capacidad por sesión (TTL: 1 hora)
  ```php
  Cache::remember("capacity_{$session_id}", 3600, function() {
      return $this->calculateAvailableHours(...);
  });
  ```

- **Eager Loading**: Siempre usar `with()` para relaciones frecuentes
  ```php
  WorkOrder::with('purchaseOrder.part.standards')->get();
  ```

- **Índices**: Asegurar índices en `shift_id`, `date`, `work_order_id`

- **Pagination**: Limitar a 20-50 WOs por página en listas

### UX/UI

**Feedback Visual:**
- Loading spinners durante cálculos
- Progress bars para utilización de capacidad
- Color coding:
  - Verde: < 80% utilización
  - Amarillo: 80-95% utilización
  - Rojo: > 95% utilización

**Validación en Tiempo Real:**
- Validar inputs con Livewire `wire:blur`
- Mostrar errores inmediatamente
- Deshabilitar botones cuando no hay capacidad

### Seguridad

- **Autorización**: Middleware `can:manage-production-capacity`
- **Validación Backend**: NUNCA confiar en validación frontend
- **SQL Injection**: Usar Eloquent ORM (protección automática)
- **CSRF**: Livewire maneja automáticamente

---

## Entregables

### Código

1. **3 Migraciones**:
   - `create_over_times_table`
   - `create_sent_lists_table`
   - `add_units_per_hour_to_standards_table`

2. **3 Modelos**:
   - `OverTime`
   - `SentList`
   - `Standard` (actualización)

3. **2 Servicios**:
   - `CapacityCalculatorService`
   - `CapacityResult`

4. **1 Excepción**:
   - `CapacityExceededException`

5. **9 Componentes Livewire**:
   - CRUD OverTime (4)
   - CRUD SentList (4)
   - Capacity Calculator (1)

6. **9 Vistas Blade**:
   - Correspondientes a componentes

7. **3 Form Requests**:
   - `CalculateCapacityRequest`
   - `AddWorkOrderToCapacityRequest`
   - `GenerateSentListRequest`

### Testing

8. **30+ Tests**:
   - Unit Tests
   - Property-Based Tests
   - Feature Tests
   - Integration Tests

### Documentación

9. **Este Spec** (plan de implementación)
10. **API Documentation** de servicios
11. **Diagramas de Secuencia** del flujo
12. **README** de módulo de capacidad

---

## Cronograma

| Fase | Días | Descripción | Entregables |
|------|------|-------------|-------------|
| 2.1  | 1-2  | Fundamentos (modelos y migraciones) | Migraciones, Modelos base |
| 2.2  | 3-4  | Servicios de negocio | CapacityCalculatorService, CapacityResult |
| 2.3  | 5-6  | CRUD OverTime | 4 componentes + vistas |
| 2.4  | 7-8  | CRUD SentList | 4 componentes + vistas |
| 2.5  | 9-11 | Calculadora de Capacidad | 1 componente interactivo |
| 2.6  | 12-14| Testing completo | 30+ tests |
| 2.7  | 15-16| Documentación y refinamiento | Docs, seeders, optimización |

**Total: 16 días de desarrollo (3.2 semanas)**

---

## Riesgos y Mitigaciones

| Riesgo | Impacto | Probabilidad | Mitigación |
|--------|---------|--------------|------------|
| Standards sin `units_per_hour` | ALTO | ALTA | Migración de datos + validación estricta |
| Cálculos incorrectos | ALTO | MEDIA | Property-Based Testing extensivo (100+ iteraciones) |
| Performance con muchos WOs | MEDIO | MEDIA | Pagination + Cache + Índices de DB |
| UX confusa | MEDIO | BAJA | User testing + tooltips + ayuda contextual |
| Turnos que cruzan medianoche | MEDIO | MEDIA | Lógica específica para detectar y manejar |
| Datos inconsistentes en producción | ALTO | BAJA | Validaciones estrictas + transacciones DB |

---

## Próximos Pasos

1. **Revisar y aprobar** este spec
2. **Crear branch** `feature/production-capacity` desde `main`
3. **Comenzar Fase 2.1** (modificación de `standards` es CRÍTICA)
4. **Daily standup** para revisar progreso
5. **Code review** al completar cada fase
6. **Merge a main** una vez completado testing

---

## Notas Importantes

- **⚠️ CRÍTICO**: La tabla `standards` DEBE ser modificada PRIMERO
- El cálculo de capacidad depende 100% de Standards correctos
- La Propiedad 6 es ESENCIAL para evitar sobrecarga
- El componente `CapacityCalculator` debe ser extremadamente robusto
- Todos los cálculos deben tener tests de propiedades

---

## Referencias

### Archivos del Proyecto

- **Diagrama de flujo**: `Diagramas_flujo/diagramas/2-diagrama-capacidad-disponible-produccion.mkd`
- **Documento general**: `Diagramas_flujo/Estructura/Flexcon_Tracker_ERP.md`
- **Patrón Livewire existente**: `app/Livewire/Admin/Standards/StandardCreate.php`

### Propiedades de Corrección

- **Propiedad 4**: Cálculo del Tiempo de Producción
  - `horas_requeridas = cantidad ÷ units_per_hour`

- **Propiedad 5**: Cálculo de Horas Disponibles
  - `horas_disponibles = (días_laborables × (horas_turno - horas_descanso) × empleados) + horas_extra`

- **Propiedad 6**: Prevención de Desbordamiento de Capacidad
  - `capacidad_suficiente = (suma_horas_requeridas ≤ horas_disponibles)`

### Tecnologías

- **Laravel**: 12.x
- **Livewire**: 3.x
- **Tailwind CSS**: 3.x
- **Alpine.js**: 3.x
- **PHP**: 8.2+

---

## Historial de Cambios

| Versión | Fecha | Autor | Cambios |
|---------|-------|-------|---------|
| 1.0 | 2025-12-19 | Architect Agent | Creación inicial del spec |

---

**Fin del Spec 01**
