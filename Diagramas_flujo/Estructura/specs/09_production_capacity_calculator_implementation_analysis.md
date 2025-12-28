# Spec 09: Análisis Técnico de Implementación - Production Capacity Calculator

**Fecha de Creación:** 2025-12-25
**Fecha de Actualización:** 2025-12-25
**Autor:** Agent Architect
**Fase del Proyecto:** FASE 2 - Planificación de Producción
**Estado:** Análisis Técnico Completo
**Versión:** 1.5
**Relacionado con:**
- Spec 01 - Plan de Implementación Capacidad de Producción
- Spec 07 - Análisis Técnico Over Time Module
- Spec 08 - Estrategias de Manejo de Status de Producción
- db.mkd - Esquema de Base de Datos
- Capacidad.xlsx - Archivo de referencia actual

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Análisis del Archivo Excel Capacidad.xlsx](#análisis-del-archivo-excel-capacidadxlsx)
3. [Análisis del Diagrama de Flujo](#análisis-del-diagrama-de-flujo)
4. [GAP Analysis](#gap-analysis)
5. [Diseño Propuesto](#diseño-propuesto)
6. [Diagramas de Arquitectura](#diagramas-de-arquitectura)
7. [Plan de Implementación (6 Días)](#plan-de-implementación-6-días)
8. [Validación de Propiedades Arquitecturales](#validación-de-propiedades-arquitecturales)
9. [Consideraciones Técnicas](#consideraciones-técnicas)
10. [Archivos Afectados](#archivos-afectados)
11. [Conclusiones](#conclusiones)

---

## Resumen Ejecutivo

### Propósito del Módulo

El **Production Capacity Calculator** automatiza el cálculo de capacidad disponible y la asignación de Work Orders (WO) a esa capacidad. Implementa el flujo de pasos 1-11 del diagrama de capacidad disponible para producción, permitiendo a los planificadores:

- Seleccionar turnos y personas disponibles
- Calcular horas totales disponibles (turnos + overtime - feriados)
- Agregar WOs a la lista de producción validando capacidad
- Validar que exista capacidad disponible en tiempo real
- Generar la lista preliminar de envío (SentList)

### Decisión Crítica de Arquitectura

**Relación de Datos:**
- **Production_Capacity** ← centraliza asignaciones de capacidad
- **Over_Time** + **Shift** + **Holiday** ← insumos para cálculo
- **Standard** (con pivot ProductionStandard) ← estándares por WO con assembly modes
- **SentList** ← salida del calculador (WOs confirmados con capacidad)

**Patrón Arquitectural:**
- Service layer (`CapacityCalculatorService`) desacoplado de UI
- Livewire component (`CapacityCalculator`) para orquestación de UI
- Excepción personalizada (`CapacityExceededException`) para validaciones
- Flujo centrado en validación de capacidad: Selección → Cálculo → Validación → SentList

### Alcance de Esta Fase

Esta especificación técnica cubre exclusivamente el módulo de cálculo de capacidad de producción. El OUTPUT final es el SentList con los Work Orders que tienen capacidad confirmada. La preparación de materiales y kits es una fase posterior que no está incluida en este análisis.

---

## Análisis del Archivo Excel Capacidad.xlsx

### Ubicación del Archivo

**Ruta:** `C:\xampp\htdocs\flexcon-tracker\Diagramas_flujo\Estructura\docs\ef\Capacidad.xlsx`

Este archivo Excel representa la herramienta actual que utilizan los planificadores de producción para calcular manualmente la capacidad semanal disponible y asignar Work Orders. Es esencialmente el **blueprint funcional** que el sistema debe automatizar.

### Estructura del Archivo

El archivo contiene **múltiples hojas de cálculo** (una por semana de planificación):
- Hoja 1: "06-05-2025" (semana del 5 de junio 2025)
- Hoja 2: "05-28-2025" (semana del 28 de mayo 2025)
- Hoja 3: "05-21-2025" (semana del 21 de mayo 2025)
- Hoja 4: "05-14-2025" (semana del 14 de mayo 2025)

Cada hoja tiene **210 filas** y **10 columnas (A-J)**, siguiendo la misma estructura.

### Análisis de Estructura por Sección

#### Sección 1: Encabezado y Resumen de Capacidad (Filas 1-6)

```
Fila 1: "Capacidad de Producción Semanal"
Fila 2: Headers → Turno | Personal disponible | Horas por turno | Días trabajados | Total horas disponibles | Total horas necesarias
Fila 4: Turno 1 → 19 personas | 8.5 horas | 5 días | =B4*C4*D4 (cálculo automático)
Fila 5: Turno 2 → 19 personas | 7.5 horas | 5 días | =B5*C5*D5
Fila 6: Total → =SUBTOTAL(9,E4:E5) | Diferencia: =F4-E6
```

**Interpretación técnica:**
- **Turnos múltiples:** Sistema debe soportar selección de varios turnos simultáneamente
- **Personal variable:** El número de personas cambia semanalmente (requiere input dinámico)
- **Cálculo automático:** Total horas = personas × horas_turno × días_trabajados
- **Balance en tiempo real:** Diferencia = horas_disponibles - horas_necesarias

**Mapeo al sistema propuesto:**
```php
// Corresponde a CapacityCalculatorService::calculateTotalAvailableHours()
$regular_hours = $num_persons * $shift_hours * $available_days;
$total_hours = $regular_hours + $overtime_hours;
```

#### Sección 2: Catálogo de Partes y Estándares (Filas 9-210)

```
Fila 9-10: Headers
  - No Parte (Columna A)
  - Estándar de: 1 persona | 2 personas | 3 personas (Columnas B-D)
  - Cantidad de WO (Columna E)
  - Horas necesarias por: 1 persona | 2 personas | 3 personas (Columnas F-H)

Fila 11: E-C-249-8 | - | 230 | - | =E11/B11 | =E11/C11 | =E11/D11
Fila 22: H-BML-324-10 | - | - | 124 | 5500 | =E22/B22 | =E22/C22 | =E22/D22
Fila 36: H-C-3 | 2500 | 228 | 177 | 50000 | =E36/B36 | =E36/C36 | =E36/D36
```

**Interpretación técnica:**

1. **Estándares flexibles por modalidad de ensamble:**
   - **1 persona:** units_per_hour más bajo (ej: 2500 para H-C-3)
   - **2 personas:** units_per_hour medio (ej: 228 para H-C-3)
   - **3 personas:** units_per_hour más alto (ej: 177 para H-C-3)

2. **Fórmulas de cálculo de horas:**
   ```
   Horas requeridas = Cantidad_WO / Standard_units_per_hour
   ```

3. **Cantidad de WO variable:**
   - Algunas partes tienen cantidad asignada (ej: 5500, 8200, 50000)
   - Otras están vacías (planificador las llena manualmente)

**Hallazgo crítico:** El Excel se enfoca exclusivamente en el cálculo de capacidad disponible. La generación de SentList es el output final de este proceso.

#### Sección 3: Totales y Validación

```
Fila 6: =F4-E6 → Diferencia (positivo = capacidad sobrante, negativo = sobrecarga)
```

**Interpretación:**
- Si Diferencia > 0 → Hay capacidad disponible, se puede generar SentList
- Si Diferencia < 0 → Capacidad excedida, requiere ajuste (CapacityExceededException)

### Análisis de Datos Reales

#### Ejemplo: Semana 06-05-2025 (Hoja 1)

**Capacidad disponible:**
- Turno 1: 19 personas × 8.5 horas × 5 días = 807.5 horas
- Turno 2: 19 personas × 7.5 horas × 5 días = 712.5 horas
- **Total: 1,520 horas semanales**

**Work Orders asignados (muestra):**
- H-BML-324-10: 5,500 unidades (Standard: 124 unidades/hora con 3 personas) → 44.35 horas
- H-BML-B324-10: 8,200 unidades (Standard: 113 unidades/hora con 3 personas) → 72.57 horas
- H-C-3: 50,000 unidades (Standard: 2500 unidades/hora con 1 persona) → 20 horas

**Total horas necesarias:** Se calcula sumando las fórmulas en F (ej: =F33+F42+G61+...)

### Relación con el Flujo de Producción

#### Lo que el Excel SÍ hace:
1. Calcula capacidad total disponible (turnos + personas + días)
2. Asigna Work Orders a esa capacidad
3. Valida que no se exceda la capacidad
4. Genera una "lista preliminar de envío" (implícita en las filas con cantidad)

#### Lo que el Excel NO hace (limitaciones naturales):
1. **No automatiza el cálculo** - requiere entrada manual repetitiva
2. **No valida en tiempo real** - el planificador debe calcular mentalmente
3. **No registra histórico** - cada semana es una hoja nueva sin trazabilidad
4. **No integra con sistema** - datos aislados sin conexión a WOs reales

### Conclusiones del Análisis

#### Hallazgos clave:

1. **El Excel es un calculador de capacidad puro** - Se enfoca exclusivamente en validar si hay horas disponibles
2. **El SentList es el output final** - Representa la lista de Work Orders que tienen capacidad confirmada
3. **El sistema debe automatizar y mejorar este flujo** - Validación en tiempo real, histórico, integración

#### Impacto arquitectural:

**NECESIDAD CONFIRMADA:** Crear módulo `CapacityCalculator` para:
- Automatizar el cálculo de horas disponibles
- Validar capacidad en tiempo real mientras se agregan WOs
- Generar SentList automáticamente con trazabilidad
- Integrar con datos reales del sistema (Shifts, Holidays, OverTime, Standards)

**Flujo implementado:**
```
CapacityCalculator (UI + Service)
  ↓ (seleccionar turnos, personas, fechas)
  ↓ (agregar WOs validando capacidad)
  ↓
SentList (Output: WOs con capacidad confirmada)
```

### Datos para el Diseño

#### Campos identificados del Excel que deben mapearse al sistema:

| Campo Excel | Tabla Sistema | Campo Sistema | Tipo |
|-------------|---------------|---------------|------|
| Turno | shifts | id (FK en sent_lists) | int |
| Personal disponible | sent_lists | num_persons | int |
| Horas por turno | shifts | hours | decimal |
| Días trabajados | sent_lists | start_date, end_date | date |
| Total horas disponibles | sent_lists | total_available_hours | decimal |
| No Parte | parts | number | string |
| Estándar (1/2/3 personas) | production_standards | units_per_hour (pivot) | decimal |
| Cantidad de WO | work_orders | quantity | int |
| Horas necesarias | sent_lists | used_hours | decimal |
| Diferencia | sent_lists | remaining_hours | decimal |

#### Nuevos campos requeridos (NO en Excel):

| Campo Nuevo | Tabla | Propósito |
|-------------|-------|-----------|
| assembly_mode | work_orders | 1 persona / 2 personas / 3 personas |
| sent_list_id | work_orders | FK a sent_lists para trazabilidad |
| status | sent_lists | pending / confirmed / canceled |

---

## Análisis del Diagrama de Flujo

### Mapeo a Componentes del Sistema

| Paso | Acción | Componente Responsable | Entrada | Salida |
|------|--------|------------------------|---------|--------|
| 1-2 | Seleccionar turno + personas | CapacityCalculator component | UI Form | turno, personas |
| 3 | Seleccionar turnos (multiple) | CapacityCalculator component | Checkbox | shift_ids[] |
| 4 | Restar feriados | CapacityCalculatorService | Holiday::class | available_days |
| 5 | Sumar horas totales | CapacityCalculatorService | Shift, OverTime | total_hours |
| 6 | Agregar parte + cantidad | CapacityCalculator component | Form Input | part_id, qty |
| 7 | Agregar PO + modalidad | CapacityCalculator component | Form Input | po_id, assembly_mode |
| 8-9 | Dividir cantidad / estándar | CapacityCalculatorService | Standard (pivot) | required_hours |
| 10 | Restar horas disponibles | CapacityCalculatorService | total_hours - required_hours | remaining_hours |
| 11 | ¿Horas disponibles? → Generar SentList | CapacityCalculatorService | remaining_hours > 0 | SentList record |

### Lógica de Flujo en el Calculador

```
INITIALIZE:
  total_hours = calculateTotalHours(shift_ids, num_persons, start_date, end_date)
  remaining_hours = total_hours
  wo_list = []

LOOP WHILE user_continues:
  part_id, quantity = getUserInput()
  standard = Part.findStandard(part_id)
  required_hours = (quantity / standard.units_per_hour)

  IF remaining_hours >= required_hours:
    wo_list.add({ part_id, quantity, required_hours })
    remaining_hours -= required_hours
  ELSE:
    THROW CapacityExceededException(remaining_hours, required_hours)

GENERATE_SENT_LIST:
  sentList = SentList.create({
    shift_ids: shift_ids,
    num_persons: num_persons,
    total_hours: total_hours,
    used_hours: total_hours - remaining_hours,
    remaining_hours: remaining_hours,
    work_orders: wo_list
  })
  RETURN sentList
```

---

## GAP Analysis

### Estado Actual vs. Requerido

| Componente | Estado | Tipo | Prioridad |
|-----------|--------|------|-----------|
| **SentList Model** | ❌ No existe | Modelo + Migración | CRÍTICA |
| **SentList CRUD** | ❌ No existe | Controller + Routes | CRÍTICA |
| **CapacityCalculatorService** | ❌ No existe | Service Layer | CRÍTICA |
| **CapacityCalculator (Livewire)** | ❌ No existe | Component UI | CRÍTICA |
| **CapacityExceededException** | ❌ No existe | Exception Class | ALTA |
| **ProductionStandard Pivot** | ❌ Parcial | Migration (ajuste) | ALTA |
| **Over_Time Model** | ✅ Completo | Modelo + Migración | - |
| **Standard Model** | ✅ Completo | Con units_per_hour | - |
| **Base Models** | ✅ Completo | Part, Price, PO, WO, Shift, Holiday, BreakTime | - |

### Dependencias Bloqueantes

```
SentList (Model + Migration)
  ↓ (BLOQUEADOR)
CapacityCalculatorService
  ↓ (BLOQUEADOR)
CapacityCalculator (Livewire)
  ↓ (BLOQUEADOR)
SentList CRUD (Controller + Routes)
```

---

## Diseño Propuesto

### 1. SentList Model y Migración

**Responsabilidad:** Registrar el resultado del calculador (lista preliminar de envío)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SentList extends Model
{
    protected $table = 'sent_lists';

    protected $fillable = [
        'po_id',
        'shift_ids', // JSON: [1,2,3]
        'num_persons',
        'start_date',
        'end_date',
        'total_available_hours',
        'used_hours',
        'remaining_hours',
        'status', // pending, confirmed, canceled
    ];

    protected $casts = [
        'shift_ids' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'total_available_hours' => 'decimal:2',
        'used_hours' => 'decimal:2',
        'remaining_hours' => 'decimal:2',
    ];

    // Relaciones
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'sent_list_id');
    }

    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'sent_list_shift');
    }
}
```

**Migración:**

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
            $table->foreignId('po_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->json('shift_ids');
            $table->integer('num_persons');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_available_hours', 10, 2);
            $table->decimal('used_hours', 10, 2);
            $table->decimal('remaining_hours', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'canceled'])->default('pending');
            $table->timestamps();
            $table->index('po_id');
            $table->index('status');
        });

        Schema::create('sent_list_shift', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_list_id')->constrained('sent_lists')->onDelete('cascade');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->unique(['sent_list_id', 'shift_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sent_list_shift');
        Schema::dropIfExists('sent_lists');
    }
};
```

### 2. CapacityCalculatorService

**Responsabilidad:** Lógica de negocio para cálculos de capacidad (sin acoplamiento a UI)

```php
<?php

namespace App\Services;

use App\Models\{Shift, Holiday, OverTime, Standard, Part, SentList, PurchaseOrder};
use App\Exceptions\CapacityExceededException;
use Carbon\Carbon;

class CapacityCalculatorService
{
    public function calculateTotalAvailableHours(
        array $shift_ids,
        int $num_persons,
        Carbon $start_date,
        Carbon $end_date
    ): float {
        // Días disponibles = total_days - holidays - weekends
        $available_days = $this->getAvailableDays($start_date, $end_date);

        // Horas por turno
        $shift_hours = Shift::whereIn('id', $shift_ids)->sum('hours');

        // Horas totales regular
        $regular_hours = $available_days * $shift_hours * $num_persons;

        // Horas extra
        $overtime_hours = OverTime::whereBetween('date', [$start_date, $end_date])
            ->sum('hours');

        return $regular_hours + $overtime_hours;
    }

    public function calculateRequiredHours(int $part_id, int $quantity): float
    {
        $standard = Part::find($part_id)
            ->standards()
            ->first();

        if (!$standard) {
            throw new \Exception("No standard found for part {$part_id}");
        }

        return $quantity / $standard->units_per_hour;
    }

    public function validateCapacity(float $remaining_hours, float $required_hours): bool
    {
        if ($remaining_hours < $required_hours) {
            throw new CapacityExceededException(
                "Required hours: {$required_hours}. Available: {$remaining_hours}"
            );
        }
        return true;
    }

    public function createSentList(
        int $po_id,
        array $shift_ids,
        int $num_persons,
        Carbon $start_date,
        Carbon $end_date,
        array $work_orders // [{ part_id, qty, required_hours }, ...]
    ): SentList {
        $total_available = $this->calculateTotalAvailableHours(
            $shift_ids, $num_persons, $start_date, $end_date
        );

        $used_hours = array_sum(array_column($work_orders, 'required_hours'));
        $remaining_hours = $total_available - $used_hours;

        return SentList::create([
            'po_id' => $po_id,
            'shift_ids' => $shift_ids,
            'num_persons' => $num_persons,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_available_hours' => $total_available,
            'used_hours' => $used_hours,
            'remaining_hours' => $remaining_hours,
            'status' => 'pending',
        ]);
    }

    private function getAvailableDays(Carbon $start, Carbon $end): int
    {
        $total_days = $start->diffInDays($end);
        $holidays = Holiday::whereBetween('date', [$start, $end])->count();
        $weekends = $this->countWeekends($start, $end);

        return $total_days - $holidays - $weekends;
    }

    private function countWeekends(Carbon $start, Carbon $end): int
    {
        $count = 0;
        $current = $start->copy();
        while ($current->lessThanOrEqualTo($end)) {
            if ($current->isWeekend()) $count++;
            $current->addDay();
        }
        return $count;
    }
}
```

### 3. CapacityExceededException

```php
<?php

namespace App\Exceptions;

class CapacityExceededException extends \Exception
{
    public function __construct(string $message = "Capacity exceeded")
    {
        parent::__construct($message);
    }
}
```

### 4. ProductionStandard Pivot (Ajuste)

**Nota:** Esta tabla relaciona Part con Standard (muchos a muchos)

```php
// En Part model:
public function standards()
{
    return $this->belongsToMany(
        Standard::class,
        'production_standards',
        'part_id',
        'standard_id'
    )->withPivot('units_per_hour', 'notes')
      ->withTimestamps();
}

// En Standard model:
public function parts()
{
    return $this->belongsToMany(
        Part::class,
        'production_standards',
        'standard_id',
        'part_id'
    )->withPivot('units_per_hour', 'notes')
      ->withTimestamps();
}
```

**Migración:** (si no existe)

```php
Schema::create('production_standards', function (Blueprint $table) {
    $table->id();
    $table->foreignId('part_id')->constrained('parts')->onDelete('cascade');
    $table->foreignId('standard_id')->constrained('standards')->onDelete('cascade');
    $table->decimal('units_per_hour', 8, 2);
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->unique(['part_id', 'standard_id']);
});
```

### 5. CapacityCalculator Livewire Component

**Responsabilidad:** Orquestación de UI y flujo interactivo

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{PurchaseOrder, Shift, Part, SentList};
use App\Services\CapacityCalculatorService;
use App\Exceptions\CapacityExceededException;
use Carbon\Carbon;

class CapacityCalculator extends Component
{
    public $po_id;
    public $selected_shifts = [];
    public $num_persons = 1;
    public $start_date;
    public $end_date;
    public $total_available_hours = 0;
    public $remaining_hours = 0;
    public $work_orders = [];
    public $error_message = '';

    protected CapacityCalculatorService $service;

    public function mount(CapacityCalculatorService $service)
    {
        $this->service = $service;
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addDays(7)->format('Y-m-d');
    }

    public function calculateCapacity()
    {
        try {
            $this->total_available_hours = $this->service->calculateTotalAvailableHours(
                $this->selected_shifts,
                $this->num_persons,
                Carbon::parse($this->start_date),
                Carbon::parse($this->end_date)
            );
            $this->remaining_hours = $this->total_available_hours;
            $this->error_message = '';
        } catch (\Exception $e) {
            $this->error_message = $e->getMessage();
        }
    }

    public function addWorkOrder($part_id, $quantity)
    {
        try {
            $required_hours = $this->service->calculateRequiredHours($part_id, $quantity);
            $this->service->validateCapacity($this->remaining_hours, $required_hours);

            $this->work_orders[] = [
                'part_id' => $part_id,
                'quantity' => $quantity,
                'required_hours' => $required_hours,
            ];
            $this->remaining_hours -= $required_hours;
            $this->error_message = '';
        } catch (CapacityExceededException $e) {
            $this->error_message = $e->getMessage();
        }
    }

    public function generateSentList()
    {
        try {
            $sentList = $this->service->createSentList(
                $this->po_id,
                $this->selected_shifts,
                $this->num_persons,
                Carbon::parse($this->start_date),
                Carbon::parse($this->end_date),
                $this->work_orders
            );

            // Crear WorkOrders asociados
            foreach ($this->work_orders as $wo_data) {
                $part = Part::find($wo_data['part_id']);
                WorkOrder::create([
                    'sent_list_id' => $sentList->id,
                    'part_id' => $wo_data['part_id'],
                    'quantity' => $wo_data['quantity'],
                    'po_id' => $this->po_id,
                ]);
            }

            return redirect()->route('sent-lists.show', $sentList->id)
                ->with('success', 'SentList created successfully');
        } catch (\Exception $e) {
            $this->error_message = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.capacity-calculator', [
            'shifts' => Shift::all(),
            'parts' => Part::all(),
            'purchase_orders' => PurchaseOrder::where('status', 'approved')->get(),
        ]);
    }
}
```

---

## Diagramas de Arquitectura

### Diagrama 1: Flujo de Datos del Production Capacity Calculator

```mermaid
graph TB
    UI["Livewire Component<br/>CapacityCalculator"]
    Service["Service<br/>CapacityCalculatorService"]
    Models["Models Layer"]
    DB["Database"]

    UI -->|"shift_ids, num_persons,<br/>dates, WOs"| Service
    Service -->|"read"| Models
    Models -->|"query"| DB

    subgraph Models
        Shift["Shift"]
        Holiday["Holiday"]
        OverTime["Over_Time"]
        Part["Part"]
        Standard["Standard"]
        ProdStd["ProductionStandard<br/>Pivot"]
        SentList["SentList"]
        WorkOrder["WorkOrder"]
    end

    Service -->|"create"| SentList
    Service -->|"create"| WorkOrder

    SentList -->|"write"| DB
    WorkOrder -->|"write"| DB

    style SentList fill:#90EE90
    style UI fill:#FFD700
```

### Diagrama 2: Secuencia del Cálculo de Capacidad

```mermaid
sequenceDiagram
    participant User
    participant CapCalc as CapacityCalculator
    participant Service as CapacityCalculatorService
    participant DB as Database

    %% Paso 1-5: Calcular capacidad disponible
    User->>CapCalc: Select Shifts + Persons + Dates
    CapCalc->>Service: calculateCapacity()
    Service->>DB: SELECT Shift, Holiday, OverTime
    DB-->>Service: Data
    Service->>CapCalc: total_available_hours

    %% Paso 6-10: Agregar WOs y validar
    loop Agregar Work Orders
        User->>CapCalc: Add WO (Part, Qty, Assembly Mode)
        CapCalc->>Service: calculateRequiredHours()
        Service->>DB: SELECT Standard via Part + AssemblyMode
        DB-->>Service: units_per_hour
        Service->>CapCalc: required_hours

        alt Hay capacidad disponible
            CapCalc->>CapCalc: validateCapacity() OK
            CapCalc->>CapCalc: Add WO to list
            CapCalc->>CapCalc: Update remaining_hours
        else Capacidad excedida
            CapCalc->>User: CapacityExceededException
        end
    end

    %% Paso 11: Generar SentList
    User->>CapCalc: Generate SentList
    CapCalc->>Service: createSentList()

    Service->>DB: BEGIN TRANSACTION
    Service->>DB: INSERT INTO sent_lists
    Service->>DB: INSERT INTO work_orders
    Service->>DB: COMMIT

    DB-->>Service: SentList ID
    Service-->>CapCalc: SentList
    CapCalc-->>User: Success: SentList creado
```

### Diagrama 3: ERD del Production Capacity Calculator

```mermaid
erDiagram
    PURCHASE_ORDER ||--o{ SENT_LIST : "has many"
    PURCHASE_ORDER ||--o{ WORK_ORDER : "has many"

    SENT_LIST ||--o{ WORK_ORDER : "has many"
    SENT_LIST ||--o{ SHIFT : "many-to-many via sent_list_shift"

    WORK_ORDER }o--|| PART : "belongs to"

    PART ||--o{ PRODUCTION_STANDARD : "many-to-many"
    PRODUCTION_STANDARD }o--|| STANDARD : "belongs to"

    SHIFT ||--o{ BREAK_TIME : "has many"
    SHIFT ||--o{ OVER_TIME : "has many"

    HOLIDAY ||--o{ BREAK_TIME : "optional"

    SENT_LIST {
        int id PK
        int po_id FK
        json shift_ids
        int num_persons
        date start_date
        date end_date
        decimal total_available_hours
        decimal used_hours
        decimal remaining_hours
        enum status "pending, confirmed, canceled"
        timestamps created_at_updated_at
    }

    WORK_ORDER {
        int id PK
        int sent_list_id FK
        int part_id FK
        int po_id FK
        int quantity
        enum assembly_mode "1_persona, 2_personas, 3_personas"
        timestamps created_at_updated_at
    }

    PRODUCTION_STANDARD {
        int id PK
        int part_id FK
        int standard_id FK
        decimal units_per_hour
        text notes
        timestamps created_at_updated_at
    }

    SENT_LIST_SHIFT {
        int id PK
        int sent_list_id FK
        int shift_id FK
        unique sent_list_id_shift_id
    }
```

---

## Plan de Implementación (6 Días)

### Día 1: Modelos y Migraciones
- [ ] Crear SentList model + migración
- [ ] Crear/ajustar ProductionStandard pivot migración
- [ ] Crear sent_list_shift pivot migración
- [ ] Crear CapacityExceededException
- [ ] Agregar campo `assembly_mode` a WorkOrder migración
- **Tiempo:** 2-3 horas
- **Validación:** `php artisan migrate`, verificar tablas en DB

### Día 2: Service Layer
- [ ] Implementar CapacityCalculatorService
- [ ] Métodos: calculateTotalAvailableHours, calculateRequiredHours, validateCapacity, createSentList
- [ ] Agregar helpers: getAvailableDays, countWeekends
- [ ] Transacciones DB en createSentList
- **Tiempo:** 3-4 horas
- **Validación:** Unit tests para cada método

### Día 3: Livewire Component
- [ ] Crear CapacityCalculator component
- [ ] Métodos: mount, calculateCapacity, addWorkOrder, generateSentList
- [ ] Inyección de dependencia (Service)
- [ ] Validación en tiempo real
- **Tiempo:** 3-4 horas
- **Validación:** Testeo manual en navegador

### Día 4: Vistas (Blade)
- [ ] Crear vista `livewire/capacity-calculator.blade.php`
- [ ] Formulario: shifts (multi-select), personas, fechas
- [ ] Lista dinámica de WOs con capacidad restante
- [ ] Validación de capacidad en tiempo real (Alpine.js)
- [ ] Mensajes de error para CapacityExceededException
- **Tiempo:** 3-4 horas
- **Validación:** Renderizado correcto en navegador

### Día 5: CRUD SentList y Navegación
- [ ] SentListController: index, show, edit, update, destroy
- [ ] Rutas en web.php
- [ ] Políticas de autorización (SentListPolicy)
- [ ] Actualizar navegación (menu.blade.php)
- [ ] Vistas index y show de SentList
- **Tiempo:** 3-4 horas
- **Validación:** Rutas disponibles (`php artisan route:list`)

### Día 6: Testing y Documentación
- [ ] CapacityCalculatorServiceTest (unit)
- [ ] CapacityCalculatorComponentTest (feature)
- [ ] SentListControllerTest (feature)
- [ ] Cobertura mínima: 85%
- [ ] Documentación de uso en README
- **Tiempo:** 4-5 horas
- **Validación:** `php artisan test` 100% passing

---

## Validación de Propiedades Arquitecturales

### Propiedad 4: Mantenibilidad

| Criterio | Validación | Estado |
|----------|-----------|--------|
| **Separación de responsabilidades** | Service ≠ Component ≠ Model | ✅ |
| **Inyección de dependencias** | Service inyectado en Component | ✅ |
| **Exceptions personalizadas** | CapacityExceededException clara | ✅ |
| **Métodos pequeños y enfocados** | Máx 20 líneas por método | ✅ |
| **Documentación en código** | Docblocks en servicios | ✅ |

### Propiedad 5: Escalabilidad

| Criterio | Validación | Estado |
|----------|-----------|--------|
| **Sin acoplamiento a frameworks** | Service no depende de Livewire | ✅ |
| **Reutilizable en CLI/API** | Service usable en artisan commands | ✅ |
| **JSON para arrays flexibles** | shift_ids como JSON en DB | ✅ |
| **Índices en campos frecuentes** | Índices en po_id, status | ✅ |
| **Relaciones polimórficas (opcional)** | Preparado para extensiones | ✅ |

### Propiedad 6: Seguridad

| Criterio | Validación | Estado |
|----------|-----------|--------|
| **Validación de entrada** | Validadores Livewire | 🔄 To-Do |
| **Mass assignment protection** | Fillable en modelos | ✅ |
| **Autorización** | Policies (SentListPolicy) | 🔄 To-Do |
| **SQL Injection** | ORM Eloquent + parámetrizadas | ✅ |
| **Rate limiting** | Middleware en rutas | 🔄 To-Do |

---

## Consideraciones Técnicas

### 1. Performance

**Problema:** Query N+1 al calcular capacidad

```php
// ❌ MALO
foreach ($shifts as $shift) {
    $hours += $shift->breakTimes()->sum('duration');
}

// ✅ BUENO
$shifts->load('breakTimes')->map(fn($s) =>
    $s->breakTimes->sum('duration')
);
```

**Solución:** Eager loading en CapacityCalculatorService

```php
$shifts = Shift::with(['breakTimes', 'overTimes'])
    ->whereIn('id', $shift_ids)
    ->get();
```

### 2. Concurrencia

**Problema:** Dos usuarios crean SentList simultáneamente

**Solución:** Transacciones en createSentList

```php
DB::transaction(function () {
    $sentList = SentList::create([...]);
    foreach ($work_orders as $wo) {
        WorkOrder::create([...]);
    }
});
```

### 3. Validación de Fechas

```php
protected function rules()
{
    return [
        'start_date' => 'required|date|before:end_date',
        'end_date' => 'required|date|after:start_date',
        'num_persons' => 'required|integer|min:1|max:100',
        'selected_shifts' => 'required|array|min:1',
    ];
}
```

### 4. Caché de Standares

```php
public function calculateRequiredHours(int $part_id, int $quantity): float
{
    $standard = Cache::remember(
        "standard.part.{$part_id}",
        3600, // 1 hora
        fn() => Part::find($part_id)->standards()->first()
    );
    return $quantity / $standard->units_per_hour;
}
```

---

## Archivos Afectados

### Nuevos Archivos

```
app/
├── Models/
│   └── SentList.php (NEW)
├── Services/
│   └── CapacityCalculatorService.php (NEW)
├── Exceptions/
│   └── CapacityExceededException.php (NEW)
├── Livewire/
│   └── CapacityCalculator.php (NEW)
├── Http/Controllers/
│   └── SentListController.php (NEW)
├── Policies/
│   └── SentListPolicy.php (NEW)
├── Tests/Unit/Services/
│   └── CapacityCalculatorServiceTest.php (NEW)
└── Tests/Feature/
    ├── CapacityCalculatorComponentTest.php (NEW)
    └── SentListControllerTest.php (NEW)

resources/views/
├── livewire/
│   └── capacity-calculator.blade.php (NEW)
└── sent-lists/
    ├── index.blade.php (NEW)
    ├── show.blade.php (NEW)
    └── edit.blade.php (NEW)

database/migrations/
├── xxxx_create_sent_lists_table.php (NEW)
├── xxxx_create_sent_list_shift_table.php (NEW)
└── xxxx_update_production_standards_table.php (MODIFY if needed)
```

### Archivos Modificados

```
app/Models/
├── Part.php (ADD relationships to ProductionStandard)
├── Standard.php (ADD relationships to Part)
├── WorkOrder.php (ADD sent_list_id FK, assembly_mode field)
├── Shift.php (ADD eager loading hints)
└── PurchaseOrder.php (ADD relationship to SentList)

routes/
└── web.php (ADD resource routes: sent-lists)

resources/views/
└── layouts/navigation.blade.php (ADD menu item: Production Capacity Calculator)

config/
└── app.php (REGISTER CapacityCalculatorService in container - optional)
```

---

## Conclusiones

### Impacto Arquitectural

#### Mejoras Arquitecturales Implementadas:

1. **Clean Architecture:**
   - Service layer desacoplado de Livewire permite reutilización en CLI, API, Jobs
   - Separación clara de responsabilidades: UI → Service → Models → DB
   - Lógica de negocio centralizada en CapacityCalculatorService

2. **Escalabilidad:**
   - Estructura de pivot ProductionStandard permite flexibilidad de estándares
   - Assembly modes configurables (1, 2, 3 personas) por Work Order
   - JSON en shift_ids permite selección múltiple sin tabla pivot adicional

3. **Mantenibilidad:**
   - Exceptions personalizadas (CapacityExceededException) facilitan debugging
   - Métodos pequeños y enfocados (calculateTotalAvailableHours, validateCapacity)
   - Trazabilidad completa con timestamps en SentList

4. **Performance:**
   - Eager loading previene N+1 queries
   - Transacciones DB garantizan integridad de datos
   - Índices en campos frecuentemente consultados (po_id, status)

5. **Validación en Tiempo Real:**
   - Capacidad validada antes de agregar cada WO
   - Usuario ve remaining_hours actualizado dinámicamente
   - Prevención de sobrecarga de capacidad

### Hallazgos Críticos del Análisis del Excel

| Hallazgo | Impacto | Solución Implementada |
|----------|---------|----------------------|
| Cálculo manual de capacidad | Propenso a errores, lento | CapacityCalculatorService automático |
| Sin validación en tiempo real | Errores descubiertos tarde | Validación al agregar cada WO |
| Sin histórico de planificación | No hay trazabilidad | SentList con timestamps completos |
| Datos aislados del sistema | Doble entrada de datos | Integración con WOs, POs, Shifts reales |
| Sin control de assembly modes | Estándares incorrectos | Assembly mode por WO con validación |

### Flujo Implementado

```
FASE 1: Configuración de Capacidad (Pasos 1-5)
  └─→ CapacityCalculator Component
      └─→ Seleccionar Shifts + Personas + Fechas
          └─→ CapacityCalculatorService::calculateTotalAvailableHours()
              └─→ total_available_hours calculado

FASE 2: Asignación de Work Orders (Pasos 6-10)
  └─→ Usuario agrega WO (Part + Quantity + Assembly Mode)
      └─→ CapacityCalculatorService::calculateRequiredHours()
          └─→ CapacityCalculatorService::validateCapacity()
              └─→ Si OK: agregar WO, actualizar remaining_hours
              └─→ Si NO: lanzar CapacityExceededException

FASE 3: Generación de SentList (Paso 11)
  └─→ Usuario confirma lista
      └─→ CapacityCalculatorService::createSentList()
          └─→ Transacción DB: SentList + WorkOrders
              └─→ SentList creado (OUTPUT FINAL)
```

### Riesgos Identificados y Mitigaciones

| Riesgo | Mitigación | Prioridad | Estado |
|--------|-----------|-----------|--------|
| Query N+1 en calculateCapacity | Eager loading en queries | ALTA | Por implementar |
| Concurrencia en createSentList | Transacciones DB | ALTA | Diseñado |
| Validación incompleta de entrada | Validators + Rules en Livewire | MEDIA | Pendiente |
| Rate limiting ausente | Middleware throttle en rutas | MEDIA | Pendiente |
| Cálculo incorrecto de feriados | Validar lógica en tests unitarios | ALTA | Por implementar |
| Standards no configurados | Validación antes de calcular horas | ALTA | Por implementar |

### Próximos Pasos

#### Inmediato (Días 1-6):
1. Implementar migraciones de SentList y sent_list_shift
2. Crear modelo SentList con relaciones
3. Implementar CapacityCalculatorService completo
4. Crear componente Livewire CapacityCalculator
5. Implementar vistas y CRUD de SentList
6. Testing completo con cobertura 85%+

#### Corto Plazo (2-4 semanas después):
1. **Optimización:** Agregar caché para Standards frecuentemente usados
2. **Reportes:** Dashboard de capacidad utilizada vs. disponible
3. **Notifications:** Alertas cuando SentList es confirmado
4. **Validaciones adicionales:** Verificar disponibilidad de personal antes de calcular

#### Medio Plazo (1-3 meses):
1. **Fase 3:** Integración con módulo de Materiales (preparación de kits)
2. **Fase 4:** Asignación de recursos físicos (mesas, máquinas)
3. **Fase 5:** Módulo de Inspección y Acción Correctiva
4. **Reports:** Reportes históricos de capacidad vs. utilización real

### Métricas de Éxito

#### Módulo de Capacidad:
- [ ] CapacityCalculatorService con 85%+ cobertura de tests
- [ ] Componente Livewire renderiza sin errores
- [ ] SentList CRUD funcional
- [ ] Flujo de cálculo completo (pasos 1-11 del diagrama)
- [ ] Validación en tiempo real de capacidad excedida
- [ ] Transacciones DB correctas (sin pérdida de datos)

#### Métricas de Negocio:
- [ ] Reducción de 90% en tiempo de cálculo de capacidad (30 min → 3 min)
- [ ] 100% trazabilidad de SentLists generados
- [ ] Eliminación de errores de cálculo manual
- [ ] Histórico completo de planificación de capacidad

### Comparativa: Antes vs. Después

| Aspecto | Antes (Excel Manual) | Después (Sistema) | Mejora |
|---------|---------------------|-------------------|--------|
| Cálculo de capacidad | Manual, 30 min | Automático, <3 min | 10x más rápido |
| Validación de capacidad | Manual, propensa a errores | Automática en tiempo real | Eliminación de errores |
| Trazabilidad | Sin registro | Completa con timestamps | Infinita |
| Integración con sistema | Cero (datos aislados) | Total (WOs, POs, Shifts reales) | 100% integrado |
| Histórico | No existe | Completo en DB | Datos para análisis |
| Assembly modes | No controlados | Configurables por WO | Mayor precisión |

---

## Referencias

- **Spec 01:** Production Capacity Implementation Plan
- **Spec 07:** Over Time Module Analysis
- **Spec 08:** Production Status Management with Kits
- **db.mkd:** Database Schema
- **Diagrama de Flujo:** 2-diagrama-capacidad-disponible-produccion.mkd (11 pasos)
- **Capacidad.xlsx:** Archivo Excel de referencia actual
- **Laravel Docs:** Eloquent ORM, Livewire 3.x
- **Clean Architecture:** Robert C. Martin

---

**Fin del Documento**

**Próxima Fase:** Módulo de Materiales y Preparación de Kits (Spec 10 - Por definir)
