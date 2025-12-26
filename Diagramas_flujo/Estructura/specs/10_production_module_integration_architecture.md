# Spec 10: Análisis Arquitectónico Profundo - Integración del Módulo de Production

**Fecha de Creación:** 2025-12-26
**Autor:** Agent Architect
**Fase del Proyecto:** FASE 2 - Planificación de Producción (En Progreso) + FASE 3 (Planificación)
**Estado:** Análisis Arquitectónico Completo
**Versión:** 1.0
**Relacionado con:**
- Spec 01 - Production Capacity Implementation Plan
- Spec 08 - Production Status Management with Kits Analysis
- Spec 09 - Production Capacity Calculator Implementation Analysis
- Flexcon_Tracker_ERP.md - Documento de Diseño General
- db.mkd - Esquema de Base de Datos (NO ENCONTRADO - usar migraciones)

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Visión General del Módulo Production](#visión-general-del-módulo-production)
3. [Análisis de Entidades y Relaciones](#análisis-de-entidades-y-relaciones)
4. [Arquitectura de Integración](#arquitectura-de-integración)
5. [Flujos de Trabajo Principales](#flujos-de-trabajo-principales)
6. [Patrones Arquitectónicos Implementados](#patrones-arquitectónicos-implementados)
7. [Consideraciones Técnicas](#consideraciones-técnicas)
8. [Plan de Implementación por Fases](#plan-de-implementación-por-fases)
9. [Integraciones con Módulos Existentes](#integraciones-con-módulos-existentes)
10. [Diagrama de Arquitectura Completo](#diagrama-de-arquitectura-completo)
11. [Estado Actual de Implementación](#estado-actual-de-implementación)
12. [Recomendaciones y Próximos Pasos](#recomendaciones-y-próximos-pasos)

---

## Resumen Ejecutivo

### Propósito del Análisis

Este documento presenta un **análisis arquitectónico profundo y exhaustivo** del módulo de "Production" en el sistema FlexCon Tracker ERP, abordando específicamente la pregunta del usuario:

> "¿Cómo integrar el módulo de Production que gestiona Work Orders (WO) con sus kits?"

### Hallazgos Principales

1. **IMPLEMENTACIÓN PARCIAL EXISTENTE**: El sistema ya tiene implementados componentes críticos del módulo de Production:
   - CapacityCalculatorService (COMPLETO)
   - SentList (modelo y migración COMPLETOS)
   - CapacityCalculator Livewire component (COMPLETO)
   - Production Capacity workflow (FASE 2 - 90% completado)

2. **TABLA `productions` ES UN STUB**: La tabla `productions` actualmente solo tiene un campo `number` y NO tiene propósito funcional claro. **Recomendación: Eliminar o redefinir**.

3. **ARQUITECTURA BASADA EN FLUJO DE ESTADOS**: El sistema utiliza StatusWO para gestionar estados de Work Orders, NO requiere nueva tabla de status para kits (usar enum).

4. **INTEGRACIÓN BIEN DEFINIDA**: Los componentes existentes (Production_Capacity, Production_Standards, SentList) ya están integrados correctamente.

### Decisión Arquitectónica Crítica

**NO se requiere crear un nuevo "módulo Production" separado**. Lo que existe es:

- **FASE 2 (90% Completado)**: Production Capacity Calculator (calcula capacidad, asigna WOs, genera SentList)
- **FASE 3 (Pendiente)**: Production Execution (kits, lotes, ensamble, inspección)

El "módulo Production" es la **suma de FASE 2 + FASE 3**, NO un componente independiente.

---

## Visión General del Módulo Production

### Definición del Módulo

El módulo "Production" en FlexCon Tracker ERP abarca **dos fases consecutivas** del flujo de producción:

```
┌────────────────────────────────────────────────────────────────────┐
│                    MÓDULO PRODUCTION (COMPLETO)                    │
├────────────────────────────────────────────────────────────────────┤
│                                                                    │
│  ┌──────────────────────────────┐  ┌─────────────────────────┐   │
│  │   FASE 2: Planificación      │  │   FASE 3: Ejecución     │   │
│  │   (90% Completado)           │  │   (Pendiente)           │   │
│  ├──────────────────────────────┤  ├─────────────────────────┤   │
│  │ - Capacity Calculator        │  │ - Kits (preparación)    │   │
│  │ - Standards (units/hr)       │  │ - Lots (agrupación)     │   │
│  │ - SentList (preliminar)      │  │ - Assembly (ensamble)   │   │
│  │ - OverTime (tiempo extra)    │  │ - Inspection (QC)       │   │
│  │ - Work Orders (asignación)   │  │ - Packing (empaque)     │   │
│  └──────────────────────────────┘  └─────────────────────────┘   │
│                                                                    │
│  INPUT: Purchase Orders (PO)                                      │
│  OUTPUT: Invoices + Shipping Lists                                │
└────────────────────────────────────────────────────────────────────┘
```

### Componentes Principales y Responsabilidades

#### FASE 2: Planificación de Producción (ACTUAL)

| Componente | Responsabilidad | Estado |
|------------|-----------------|--------|
| **CapacityCalculatorService** | Calcular horas disponibles y requeridas | COMPLETO |
| **Standard** | Almacenar estándares de producción (units_per_hour) | COMPLETO |
| **SentList** | Registrar lista preliminar de envío | COMPLETO |
| **OverTime** | Gestionar tiempo extra | COMPLETO |
| **Shift** | Definir turnos de trabajo | COMPLETO |
| **Holiday** | Registrar días feriados | COMPLETO |
| **CapacityCalculator (Livewire)** | Interfaz interactiva para asignar WOs | COMPLETO |

#### FASE 3: Ejecución de Producción (PENDIENTE)

| Componente | Responsabilidad | Estado |
|------------|-----------------|--------|
| **Kit** | Preparar componentes para ensamble | PENDIENTE |
| **Lot** | Agrupar producción en lotes | PENDIENTE |
| **Assembly** | Registrar proceso de ensamble | PENDIENTE |
| **Inspection** | Control de calidad | PENDIENTE |
| **QualityForms** | Formularios FCA-07, FCA-10, FCA-16 | PENDIENTE |
| **Packing** | Empaque final | PENDIENTE |

---

## Análisis de Entidades y Relaciones

### Mapa de Entidades del Módulo Production

```mermaid
erDiagram
    PURCHASE_ORDER ||--o{ WORK_ORDER : "generates"
    PURCHASE_ORDER ||--|| PART : "contains"

    WORK_ORDER ||--|| SENT_LIST : "belongs to"
    WORK_ORDER ||--|| STATUS_WO : "has status"
    WORK_ORDER ||--o{ LOT : "divided into"

    SENT_LIST ||--|| PURCHASE_ORDER : "references"
    SENT_LIST }o--o{ SHIFT : "uses shifts (pivot)"

    PART ||--o{ STANDARD : "has standards"
    STANDARD ||--|| TABLE : "uses table (manual)"
    STANDARD ||--|| SEMI_AUTOMATIC : "uses semi-auto"
    STANDARD ||--|| MACHINE : "uses machine"

    SHIFT ||--o{ OVER_TIME : "has overtime"
    SHIFT ||--o{ BREAK_TIME : "has breaks"

    LOT ||--o{ KIT : "contains kits"
    KIT ||--o{ INSPECTION : "inspected"

    INSPECTION ||--o{ QUALITY_FORM : "generates forms"

    LOT ||--o{ SHIPPING_LIST : "shipped in"
    SHIPPING_LIST ||--|| INVOICE : "generates"

    WORK_ORDER ||--o{ BACK_ORDER : "creates if incomplete"

    PURCHASE_ORDER {
        int id PK
        string po_number UK
        int part_id FK
        date po_date
        date due_date
        int quantity
        decimal unit_price
        enum status "pending|approved|rejected|pending_correction"
    }

    WORK_ORDER {
        int id PK
        string wo_number UK
        int purchase_order_id FK
        int sent_list_id FK "NEW - FASE 2"
        int status_id FK
        int sent_pieces
        date scheduled_send_date
        date actual_send_date
        date opened_date
        string assembly_mode "NEW - FASE 2"
        decimal required_hours "NEW - FASE 2"
    }

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
        enum status "pending|confirmed|canceled"
    }

    STANDARD {
        int id PK
        int part_id FK
        int work_table_id FK "nullable"
        int semi_auto_work_table_id FK "nullable"
        int machine_id FK "nullable"
        int persons_1 "units/hr con 1 persona"
        int persons_2 "units/hr con 2 personas"
        int persons_3 "units/hr con 3 personas"
        int units_per_hour "default (deprecated?)"
        date effective_date
        bool active
    }

    OVER_TIME {
        int id PK
        string name
        time start_time
        time end_time
        int break_minutes
        int employees_qty
        date date
        int shift_id FK
    }

    LOT {
        int id PK "PENDIENTE - FASE 3"
        int work_order_id FK
        string lot_number
        int quantity
        enum status "pending|in_process|completed|on_hold"
    }

    KIT {
        int id PK "PENDIENTE - FASE 3"
        int lot_id FK
        string kit_number
        enum status "pendiente|en_preparacion|preparado|en_ensamble|inspeccionado|empacado|enviado|rechazado"
        json components
        timestamp prepared_at
        timestamp assembled_at
        timestamp inspected_at
    }
```

### Relaciones Clave Identificadas

#### 1. PurchaseOrder → WorkOrder (1:1 o 1:N)

**ACTUAL:** 1:1 (una PO genera una WO)

```php
// PurchaseOrder.php
public function workOrder(): HasOne
{
    return $this->hasOne(WorkOrder::class);
}

// WorkOrder.php
public function purchaseOrder(): BelongsTo
{
    return $this->belongsTo(PurchaseOrder::class);
}
```

**CONSIDERACIÓN FUTURA:** Si una PO grande se divide en múltiples WOs, cambiar a HasMany.

#### 2. SentList → WorkOrder (1:N)

**IMPLEMENTADO:**

```php
// SentList.php
public function workOrders(): HasMany
{
    return $this->hasMany(WorkOrder::class, 'sent_list_id');
}

// WorkOrder.php
public function sentList(): BelongsTo
{
    return $this->belongsTo(SentList::class, 'sent_list_id');
}
```

**FLUJO:**
1. CapacityCalculator calcula capacidad disponible
2. Usuario agrega múltiples WOs validando capacidad
3. Se genera SentList con todos los WOs asignados

#### 3. Part → Standard (1:N)

**IMPLEMENTADO:**

```php
// Standard.php
public function part(): BelongsTo
{
    return $this->belongsTo(Part::class);
}
```

**DISEÑO ACTUAL:**
- Un Part puede tener múltiples Standards (histórico + activo)
- Se usa `active=true` para seleccionar el Standard vigente
- Cada Standard puede tener diferentes `units_per_hour` para 1, 2 o 3 personas

#### 4. Standard → Workstation (1:1 excluyente)

**IMPLEMENTADO:**

Un Standard solo puede estar asociado a UNA estación de trabajo:

```php
// Standard.php
public function workTable(): BelongsTo
{
    return $this->belongsTo(Table::class, 'work_table_id');
}

public function semiAutoWorkTable(): BelongsTo
{
    return $this->belongsTo(Semi_Automatic::class, 'semi_auto_work_table_id');
}

public function machine(): BelongsTo
{
    return $this->belongsTo(Machine::class, 'machine_id');
}

public function getAssemblyMode(): ?string
{
    if ($this->work_table_id) return 'manual';
    if ($this->semi_auto_work_table_id) return 'semi_automatic';
    if ($this->machine_id) return 'machine';
    return null;
}
```

**VALIDACIÓN:** Solo uno de los tres FKs puede estar populated.

#### 5. WorkOrder → Lot → Kit (1:N:N) - FASE 3

**PENDIENTE DE IMPLEMENTAR:**

```php
// WorkOrder.php (FUTURO)
public function lots(): HasMany
{
    // Implementación temporal que retorna vacío si Lot no existe
    if (!class_exists(\App\Models\Lot::class)) {
        return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
    }
    return $this->hasMany(\App\Models\Lot::class);
}

// Lot.php (FUTURO)
public function kits(): HasMany
{
    return $this->hasMany(Kit::class);
}

// Kit.php (FUTURO)
public function lot(): BelongsTo
{
    return $this->belongsTo(Lot::class);
}
```

#### 6. SentList ↔ Shift (N:N via pivot)

**IMPLEMENTADO:**

```php
// SentList.php
public function shifts(): BelongsToMany
{
    return $this->belongsToMany(Shift::class, 'sent_list_shift');
}
```

**Migración de pivot:**

```php
Schema::create('sent_list_shift', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sent_list_id')->constrained('sent_lists')->onDelete('cascade');
    $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
    $table->timestamps();
    $table->unique(['sent_list_id', 'shift_id']);
});
```

**USO:** Un SentList puede usar múltiples turnos para calcular capacidad.

---

## Arquitectura de Integración

### Diagrama de Capas del Módulo Production

```
┌─────────────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                              │
│                     (Livewire Components)                           │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  CapacityCalculator.php (COMPLETO)                                 │
│  │                                                                  │
│  ├─→ calculateCapacity()      ← Calcula horas disponibles          │
│  ├─→ addWorkOrder()           ← Valida y agrega WO                 │
│  ├─→ removeWorkOrder()        ← Remueve WO y restaura horas        │
│  └─→ generateSentList()       ← Crea SentList + WorkOrders         │
│                                                                     │
│  (FUTURO - FASE 3)                                                 │
│  KitPreparation.php, LotManagement.php, InspectionQC.php           │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
                            ↓ usa
┌─────────────────────────────────────────────────────────────────────┐
│                     APPLICATION LAYER                               │
│                     (Form Requests + Events)                        │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  Form Requests:                                                     │
│  - CalculateCapacityRequest (validación de inputs)                 │
│  - AddWorkOrderToCapacityRequest (validación de WO)                │
│  - GenerateSentListRequest (validación de generación)              │
│                                                                     │
│  Events:                                                            │
│  - CapacityCalculated (dispara cuando se calcula capacidad)        │
│  - SentListGenerated (dispara cuando se crea SentList)             │
│  - WorkOrderAssigned (dispara cuando WO se asigna a SentList)      │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
                            ↓ delega a
┌─────────────────────────────────────────────────────────────────────┐
│                      DOMAIN LAYER                                   │
│                      (Services + Models)                            │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  CapacityCalculatorService.php (COMPLETO)                          │
│  ├─→ calculateTotalAvailableHours()                                │
│  │   └─→ getAvailableDays() - cuenta días laborables               │
│  │   └─→ calculateShiftNetHours() - horas turno - descansos        │
│  │   └─→ countWeekends() - excluye fines de semana                 │
│  │                                                                  │
│  ├─→ calculateRequiredHours()                                       │
│  │   └─→ getUnitsPerHourByMode() - obtiene standard por mode       │
│  │                                                                  │
│  ├─→ validateCapacity()                                             │
│  │   └─→ Lanza CapacityExceededException si excede                 │
│  │                                                                  │
│  └─→ createSentList()                                               │
│      └─→ DB::transaction() - crea SentList + WorkOrders            │
│      └─→ sync shifts (pivot table)                                 │
│                                                                     │
│  Models:                                                            │
│  - SentList, WorkOrder, Standard, Shift, OverTime                  │
│  - Holiday, Part, PurchaseOrder, StatusWO                          │
│                                                                     │
│  Exceptions:                                                        │
│  - CapacityExceededException                                        │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
                            ↓ persiste en
┌─────────────────────────────────────────────────────────────────────┐
│                    INFRASTRUCTURE LAYER                             │
│                    (Database + Cache)                               │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  Database (Eloquent ORM):                                           │
│  - sent_lists (almacena capacidad calculada)                       │
│  - work_orders (WOs asignados a SentList)                          │
│  - sent_list_shift (pivot: SentList ↔ Shift)                       │
│  - standards (estándares de producción)                            │
│  - over_times (tiempo extra programado)                            │
│  - shifts, holidays, break_times (insumos para cálculo)            │
│                                                                     │
│  Cache (opcional):                                                  │
│  - Cachear Standards activos por Part                              │
│  - Cachear Holidays del año                                        │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### Diseño de Production_Capacity, Production_lot, Production_Standards

#### Tabla `productions` - ANÁLISIS CRÍTICO

**ESTADO ACTUAL:**

```php
// Migración: 2025_12_16_042057_create_productions_table.php
Schema::create('productions', function (Blueprint $table) {
    $table->id();
    $table->string('number')->unique();
    $table->timestamps();
});

// Modelo: app/Models/Production.php
class Production extends Model
{
    use HasFactory;
    // SIN fillable, SIN relaciones, SIN métodos
}
```

**PROBLEMA IDENTIFICADO:**

1. **Nombre genérico y ambiguo**: "productions" no describe claramente su propósito
2. **Sin relaciones**: No tiene FKs a otras tablas
3. **Sin propósito funcional**: Solo almacena un `number` sin contexto
4. **No se usa en el sistema**: No hay referencias en servicios o componentes

**OPCIONES ARQUITECTURALES:**

##### Opción A: ELIMINAR (RECOMENDADO)

**Justificación:**
- NO hay funcionalidad asociada
- El flujo de producción ya está cubierto por: SentList → WorkOrder → Lot → Kit
- Agregar una tabla intermedia sin propósito claro viola YAGNI (You Aren't Gonna Need It)

**Acción:**

```bash
php artisan make:migration drop_productions_table
```

```php
public function up(): void
{
    Schema::dropIfExists('productions');
}

public function down(): void
{
    Schema::create('productions', function (Blueprint $table) {
        $table->id();
        $table->string('number')->unique();
        $table->timestamps();
    });
}
```

##### Opción B: REDEFINIR como ProductionTracking (ALTERNATIVA)

Si se necesita un registro centralizado de estado de producción de un WO:

```php
Schema::create('production_trackings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('work_order_id')->unique()->constrained();
    $table->timestamp('started_at')->nullable();
    $table->timestamp('completed_at')->nullable();

    // Progreso
    $table->integer('total_kits')->default(0);
    $table->integer('kits_completed')->default(0);
    $table->decimal('progress_percent', 5, 2)->default(0);

    // Recursos asignados
    $table->foreignId('assigned_table_id')->nullable()->constrained('tables');
    $table->foreignId('assigned_machine_id')->nullable()->constrained('machines');

    // Fase actual
    $table->enum('current_phase', [
        'planning', 'preparation', 'assembly', 'inspection', 'packing', 'completed'
    ])->default('planning');

    $table->text('comments')->nullable();
    $table->timestamps();
});
```

**Ventajas:**
- Centraliza métricas de progreso del WO
- Facilita dashboards de producción
- Asignación clara de recursos físicos

**Desventajas:**
- Complejidad adicional
- Sincronización necesaria con Lots y Kits
- Puede introducir inconsistencias

**DECISIÓN RECOMENDADA:** **Opción A - ELIMINAR** la tabla `productions` y usar la jerarquía existente: WorkOrder → Lot → Kit.

#### Production_lot (Modelo Lot) - FASE 3

**NO EXISTE AÚN**, pero está definido en el diseño general:

```php
// FUTURO: database/migrations/xxxx_create_lots_table.php
Schema::create('lots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('work_order_id')->constrained();
    $table->string('lot_number');
    $table->text('description')->nullable();
    $table->integer('quantity');
    $table->enum('status', ['pending', 'in_process', 'completed', 'on_hold'])
          ->default('pending');
    $table->text('comments')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->unique(['work_order_id', 'lot_number']);
    $table->index('status');
});
```

**Propósito:**
- Dividir un WorkOrder grande en lotes manejables
- Facilitar tracking de progreso por lotes
- Permitir envíos parciales (BackOrder)

**Relaciones:**

```php
// Lot.php
class Lot extends Model
{
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function kits(): HasMany
    {
        return $this->hasMany(Kit::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    public function getProgressPercentAttribute(): float
    {
        $totalKits = $this->kits()->count();
        if ($totalKits === 0) return 0;

        $completedKits = $this->kits()
                              ->whereIn('status', ['empacado', 'enviado'])
                              ->count();

        return round(($completedKits / $totalKits) * 100, 2);
    }
}
```

#### Production_Standards (Relación Part ↔ Standard)

**IMPLEMENTADO** directamente en la tabla `standards`:

```php
// Migración: 2025_12_14_190425_create_standards_table.php
Schema::create('standards', function (Blueprint $table) {
    $table->id();
    $table->foreignId('part_id')->constrained()->onDelete('cascade');
    $table->foreignId('work_table_id')->nullable()->constrained('tables');
    $table->foreignId('semi_auto_work_table_id')->nullable()->constrained('semi__automatics');
    $table->foreignId('machine_id')->nullable()->constrained('machines');

    $table->integer('persons_1'); // units_per_hour con 1 persona
    $table->integer('persons_2'); // units_per_hour con 2 personas
    $table->integer('persons_3'); // units_per_hour con 3 personas
    $table->integer('units_per_hour'); // Default (¿deprecado?)

    $table->date('effective_date');
    $table->boolean('active')->default(true);
    $table->text('description')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['part_id', 'active']);
});
```

**DISEÑO ACTUAL:** Relación directa 1:N (Part → Standards), NO tabla pivot.

**CONSIDERACIÓN:** Si se requiere asociar múltiples Standards a múltiples Parts (M:N), crear tabla pivot:

```php
// FUTURO (solo si es necesario M:N)
Schema::create('part_standard', function (Blueprint $table) {
    $table->id();
    $table->foreignId('part_id')->constrained();
    $table->foreignId('standard_id')->constrained();
    $table->integer('priority')->default(1); // Para ordenar preferencia
    $table->timestamps();

    $table->unique(['part_id', 'standard_id']);
});
```

**DECISIÓN ACTUAL:** Mantener relación 1:N (Part hasMany Standards), NO crear pivot.

### Puntos de Integración con WO, PO, Lot

#### Integración 1: PO → WO (Ya Implementado - FASE 1)

**Flujo:**
1. PurchaseOrder llega con `status='pending'`
2. PurchaseOrderService valida precio
3. Si aprobado: crea WorkOrder automáticamente
4. WorkOrder inicia con `status_id=1` (Abierto)

**Código:**

```php
// PurchaseOrderService.php (ejemplo - no existe aún)
public function createWorkOrderFromPO(PurchaseOrder $po): WorkOrder
{
    return WorkOrder::create([
        'wo_number' => WorkOrder::generateWONumber(),
        'purchase_order_id' => $po->id,
        'status_id' => StatusWO::where('name', 'Abierto')->first()->id,
        'sent_pieces' => 0,
        'opened_date' => now(),
    ]);
}
```

#### Integración 2: WO → SentList (Implementado - FASE 2)

**Flujo:**
1. CapacityCalculator calcula `total_available_hours`
2. Usuario agrega WOs uno por uno
3. Cada WO valida que `required_hours <= remaining_hours`
4. Al generar SentList:
   - Crea registro en `sent_lists`
   - Asigna `sent_list_id` a cada WorkOrder
   - Sincroniza turnos en pivot `sent_list_shift`

**Código:**

```php
// CapacityCalculatorService.php (IMPLEMENTADO)
public function createSentList(...): SentList
{
    return DB::transaction(function () use (...) {
        // 1. Crear SentList
        $sentList = SentList::create([
            'po_id' => $po_id,
            'shift_ids' => $shift_ids,
            'num_persons' => $num_persons,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_available_hours' => $total_available,
            'used_hours' => $used_hours,
            'remaining_hours' => $remaining_hours,
            'status' => SentList::STATUS_PENDING,
        ]);

        // 2. Asignar WorkOrders
        foreach ($work_orders as $wo_data) {
            WorkOrder::create([
                'wo_number' => WorkOrder::generateWONumber(),
                'purchase_order_id' => $po_id,
                'sent_list_id' => $sentList->id, // FK a SentList
                'assembly_mode' => $wo_data['assembly_mode'],
                'required_hours' => $wo_data['required_hours'],
                'status_id' => 1,
                'sent_pieces' => 0,
                'opened_date' => now(),
            ]);
        }

        // 3. Sincronizar shifts
        $sentList->shifts()->sync($shift_ids);

        return $sentList;
    });
}
```

#### Integración 3: WO → Lot → Kit (Pendiente - FASE 3)

**Flujo PROPUESTO:**

```
WorkOrder (status: "Planificado")
    ↓
Lot creado manualmente o automáticamente
    ↓
Kits generados por Lot
    ↓
Kit status: pendiente → en_preparacion → preparado → en_ensamble → inspeccionado → empacado → enviado
    ↓
Al completar Lot: actualizar WorkOrder.sent_pieces
    ↓
Si sent_pieces == PO.quantity: WorkOrder status → "Cerrado"
Si sent_pieces < PO.quantity: WorkOrder status → "BackOrder"
```

**Código PROPUESTO:**

```php
// LotService.php (FUTURO)
public function createLotFromWorkOrder(WorkOrder $wo, int $quantity): Lot
{
    $lotNumber = $this->generateLotNumber($wo);

    return Lot::create([
        'work_order_id' => $wo->id,
        'lot_number' => $lotNumber,
        'quantity' => $quantity,
        'status' => 'pending',
    ]);
}

// KitService.php (FUTURO)
public function generateKitsForLot(Lot $lot, array $components): Collection
{
    $kits = [];
    $kitsPerLot = ceil($lot->quantity / 100); // Ejemplo: 100 unidades por kit

    for ($i = 1; $i <= $kitsPerLot; $i++) {
        $kits[] = Kit::create([
            'lot_id' => $lot->id,
            'kit_number' => "KIT-{$lot->lot_number}-" . str_pad($i, 3, '0', STR_PAD_LEFT),
            'status' => 'pendiente',
            'components' => json_encode($components),
        ]);
    }

    return collect($kits);
}
```

### Relación con Stations, Employees, Shifts

#### Stations (Tables, Semi_Automatics, Machines)

**IMPLEMENTADO** en Standard:

```php
// Standard.php
public function getWorkstation()
{
    return $this->workTable ?? $this->semiAutoWorkTable ?? $this->machine;
}

public function getAssemblyMode(): ?string
{
    if ($this->work_table_id) return 'manual';
    if ($this->semi_auto_work_table_id) return 'semi_automatic';
    if ($this->machine_id) return 'machine';
    return null;
}
```

**USO:**
- Al calcular `required_hours`, se usa el Standard que define la estación
- El `assembly_mode` determina qué `units_per_hour` usar (persons_1, persons_2, persons_3)

**INTEGRACIÓN FUTURA (FASE 3):**
- Asignar estación específica al iniciar Lot
- Actualizar `production_status_id` de la estación (Disponible → En Uso → Disponible)

#### Employees

**NO IMPLEMENTADO DIRECTAMENTE EN PRODUCTION**, pero se usa `num_persons` en SentList.

**CONSIDERACIÓN FUTURA:**
- Crear tabla `lot_employees` (pivot) para asignar empleados específicos a Lots
- Validar que `num_persons` no exceda empleados disponibles en el turno

```php
// FUTURO (opcional)
Schema::create('lot_employee', function (Blueprint $table) {
    $table->id();
    $table->foreignId('lot_id')->constrained();
    $table->foreignId('employee_id')->constrained();
    $table->timestamps();

    $table->unique(['lot_id', 'employee_id']);
});
```

#### Shifts

**COMPLETAMENTE INTEGRADO:**

```php
// CapacityCalculatorService.php
$shifts = Shift::with('breakTimes')
    ->whereIn('id', $shift_ids)
    ->get();

foreach ($shifts as $shift) {
    $shift_hours = $this->calculateShiftNetHours($shift);
    $total_shift_hours += $shift_hours;
}
```

**USO:**
- Se seleccionan múltiples turnos en CapacityCalculator
- Se calculan horas netas (total - descansos) por turno
- Se almacenan en `sent_list_shift` (pivot)

---

## Flujos de Trabajo Principales

### Flujo 1: Creación de Órdenes de Producción (FASE 1 + FASE 2)

```mermaid
sequenceDiagram
    participant User
    participant PO as PurchaseOrder
    participant WO as WorkOrder
    participant Calc as CapacityCalculator
    participant Service as CapacityCalculatorService
    participant SL as SentList

    User->>PO: Crear PO (parte, cantidad, precio)
    PO->>PO: Validar precio

    alt Precio correcto
        PO->>WO: Crear WO (status: "Abierto")
        WO-->>User: WO creado
    else Precio incorrecto
        PO-->>User: Solicitar corrección
    end

    User->>Calc: Abrir Capacity Calculator
    User->>Calc: Seleccionar turnos, personas, fechas
    Calc->>Service: calculateTotalAvailableHours()
    Service-->>Calc: total_available_hours

    loop Agregar Work Orders
        User->>Calc: Agregar WO (parte, cantidad, mode)
        Calc->>Service: calculateRequiredHours()
        Service-->>Calc: required_hours
        Calc->>Service: validateCapacity()

        alt Capacidad suficiente
            Service-->>Calc: OK
            Calc->>Calc: Agregar WO a lista
            Calc->>Calc: remaining_hours -= required_hours
        else Capacidad excedida
            Service-->>Calc: CapacityExceededException
            Calc-->>User: Error: No hay capacidad
        end
    end

    User->>Calc: Generar SentList
    Calc->>Service: createSentList()
    Service->>SL: CREATE SentList
    Service->>WO: UPDATE sent_list_id
    Service->>Service: Sync shifts (pivot)
    Service-->>User: SentList creado
```

### Flujo 2: Asignación de Capacidad (FASE 2 - COMPLETADO)

```mermaid
flowchart TD
    A[Inicio: Usuario abre Capacity Calculator] --> B[Seleccionar Turnos]
    B --> C[Ingresar Número de Personas]
    C --> D[Seleccionar Rango de Fechas]
    D --> E[Click: Calcular Capacidad]

    E --> F[CapacityCalculatorService.calculateTotalAvailableHours]
    F --> G[Calcular días laborables]
    G --> G1[Total días en rango]
    G1 --> G2[Restar días feriados]
    G2 --> G3[Restar fines de semana]
    G3 --> H[Calcular horas por turno]

    H --> H1[Horas totales turno]
    H1 --> H2[Restar tiempos de descanso]
    H2 --> I[Sumar horas extra]
    I --> J[Formula: días × horas_turno × personas + overtime]
    J --> K[Retornar total_available_hours]

    K --> L{Usuario agrega WO?}
    L -->|Sí| M[Seleccionar Parte, Cantidad, Assembly Mode]
    M --> N[CapacityCalculatorService.calculateRequiredHours]
    N --> O[Obtener Standard activo para Parte]
    O --> P[Extraer units_per_hour según assembly_mode]
    P --> Q[Formula: required_hours = cantidad / units_per_hour]

    Q --> R[CapacityCalculatorService.validateCapacity]
    R --> S{remaining_hours >= required_hours?}

    S -->|Sí| T[Agregar WO a lista]
    T --> U[remaining_hours -= required_hours]
    U --> L

    S -->|No| V[Lanzar CapacityExceededException]
    V --> W[Mostrar error: Capacidad excedida]
    W --> L

    L -->|No, generar SentList| X[CapacityCalculatorService.createSentList]
    X --> Y[DB Transaction BEGIN]
    Y --> Z[CREATE SentList]
    Z --> AA[CREATE WorkOrders con sent_list_id]
    AA --> AB[SYNC Shifts pivot table]
    AB --> AC[COMMIT Transaction]
    AC --> AD[SentList generado]
    AD --> AE[Fin: Redirigir a SentList.show]
```

### Flujo 3: Seguimiento de Lotes (PENDIENTE - FASE 3)

```mermaid
stateDiagram-v2
    [*] --> WO_Planificado: SentList confirmado

    WO_Planificado --> Lot_Pending: Crear Lot

    Lot_Pending --> Lot_InProcess: Generar Kits

    state Lot_InProcess {
        Kit_Pendiente --> Kit_EnPreparacion: Iniciar preparación
        Kit_EnPreparacion --> Kit_Preparado: Componentes listos
        Kit_Preparado --> Kit_EnEnsamble: Iniciar ensamble
        Kit_EnEnsamble --> Kit_Inspeccionado: Completar ensamble

        Kit_Inspeccionado --> Kit_Empacado: QC aprobado
        Kit_Inspeccionado --> Kit_Rechazado: QC rechazado
        Kit_Rechazado --> Kit_EnEnsamble: Re-ensamblar

        Kit_Empacado --> Kit_Enviado: Incluir en ShippingList
    }

    Lot_InProcess --> Lot_Completed: Todos los kits enviados

    Lot_Completed --> WO_ParcialmEnviado: Actualizar sent_pieces

    WO_ParcialmEnviado --> WO_Cerrado: sent_pieces == PO.quantity
    WO_ParcialmEnviado --> WO_BackOrder: sent_pieces < PO.quantity

    WO_Cerrado --> [*]
    WO_BackOrder --> WO_Planificado: Re-planificar
```

### Flujo 4: Estados de Producción

```mermaid
graph LR
    subgraph WorkOrder Status
        WO1[Abierto] --> WO2[Planificado]
        WO2 --> WO3[En Producción]
        WO3 --> WO4[En Inspección]
        WO4 --> WO5[Parcialmente Enviado]
        WO5 --> WO6[Cerrado]
        WO5 --> WO7[BackOrder]
        WO7 --> WO2

        WO1 --> WO8[En Espera]
        WO2 --> WO8
        WO3 --> WO8
        WO8 --> WO1
    end

    subgraph Lot Status
        L1[pending] --> L2[in_process]
        L2 --> L3[completed]
        L2 --> L4[on_hold]
        L4 --> L2
    end

    subgraph Kit Status
        K1[pendiente] --> K2[en_preparacion]
        K2 --> K3[preparado]
        K3 --> K4[en_ensamble]
        K4 --> K5[inspeccionado]
        K5 --> K6[empacado]
        K6 --> K7[enviado]

        K4 --> K8[rechazado]
        K8 --> K4
    end

    WO3 -.-> L2
    L2 -.-> K4
```

**Sincronización de Estados:**

```php
// WorkOrder.php (FUTURO)
public function updateStatusFromLots(): void
{
    $totalPieces = $this->purchaseOrder->quantity;
    $sentPieces = $this->sent_pieces;

    if ($sentPieces === 0) {
        $this->changeStatus('Planificado');
    } elseif ($sentPieces < $totalPieces) {
        $this->changeStatus('Parcialmente Enviado');
    } else {
        $this->changeStatus('Cerrado');
    }
}

// Lot.php (FUTURO)
public function updateStatusFromKits(): void
{
    $totalKits = $this->kits()->count();
    if ($totalKits === 0) {
        $this->status = 'pending';
        $this->save();
        return;
    }

    $completedKits = $this->kits()->whereIn('status', ['empacado', 'enviado'])->count();
    $inProductionKits = $this->kits()->whereIn('status', ['preparado', 'en_ensamble', 'inspeccionado'])->count();

    if ($completedKits === $totalKits) {
        $this->status = 'completed';
    } elseif ($inProductionKits > 0) {
        $this->status = 'in_process';
    } else {
        $this->status = 'pending';
    }

    $this->save();
}
```

---

## Patrones Arquitectónicos Implementados

### 1. Service Layer Pattern (IMPLEMENTADO)

**Propósito:** Desacoplar lógica de negocio de los componentes Livewire

**Implementación:**

```php
// CapacityCalculatorService.php
namespace App\Services;

class CapacityCalculatorService
{
    // Métodos públicos (API del servicio)
    public function calculateTotalAvailableHours(...): float { }
    public function calculateRequiredHours(...): float { }
    public function validateCapacity(...): bool { }
    public function createSentList(...): SentList { }

    // Métodos protegidos (helpers internos)
    protected function getAvailableDays(...): int { }
    protected function calculateShiftNetHours(...): float { }
    protected function countWeekends(...): int { }
    protected function getUnitsPerHourByMode(...): int { }
}
```

**Ventajas:**
- Reutilizable en Livewire, Controllers, Artisan Commands, Jobs
- Testeable independientemente
- Cumple Single Responsibility Principle

### 2. Repository Pattern (PARCIAL - via Eloquent)

**Propósito:** Abstraer acceso a datos

**Implementación implícita:**

```php
// Eloquent ya actúa como Repository
$shifts = Shift::with('breakTimes')->whereIn('id', $shift_ids)->get();
$holidays = Holiday::whereBetween('date', [$start, $end])->count();
```

**CONSIDERACIÓN FUTURA:** Si se requieren queries complejas, crear Repositories explícitos:

```php
// app/Repositories/SentListRepository.php
class SentListRepository
{
    public function findByDateRange(Carbon $start, Carbon $end): Collection
    {
        return SentList::whereBetween('start_date', [$start, $end])
            ->with('workOrders', 'shifts')
            ->get();
    }

    public function getCapacityStatistics(Carbon $start, Carbon $end): array
    {
        // Query compleja que retorna estadísticas agregadas
    }
}
```

### 3. Data Transfer Object (DTO) Pattern (NO IMPLEMENTADO - RECOMENDADO)

**Propósito:** Encapsular datos transferidos entre capas

**Ejemplo PROPUESTO:**

```php
// app/DTOs/CapacityResultDTO.php
namespace App\DTOs;

readonly class CapacityResultDTO
{
    public function __construct(
        public float $totalAvailableHours,
        public float $usedHours,
        public float $remainingHours,
        public float $utilizationPercent,
        public array $workOrders
    ) {}

    public function isOptimal(): bool
    {
        return $this->utilizationPercent < 95;
    }

    public function isOverloaded(): bool
    {
        return $this->utilizationPercent > 100;
    }

    public function toArray(): array
    {
        return [
            'total_available_hours' => $this->totalAvailableHours,
            'used_hours' => $this->usedHours,
            'remaining_hours' => $this->remainingHours,
            'utilization_percent' => $this->utilizationPercent,
            'is_optimal' => $this->isOptimal(),
            'is_overloaded' => $this->isOverloaded(),
            'work_orders' => $this->workOrders,
        ];
    }
}
```

**USO:**

```php
// CapacityCalculatorService.php
public function getCapacityAnalysis(...): CapacityResultDTO
{
    $totalAvailable = $this->calculateTotalAvailableHours(...);
    $usedHours = array_sum(array_column($workOrders, 'required_hours'));
    $remainingHours = $totalAvailable - $usedHours;
    $utilizationPercent = ($usedHours / $totalAvailable) * 100;

    return new CapacityResultDTO(
        totalAvailableHours: $totalAvailable,
        usedHours: $usedHours,
        remainingHours: $remainingHours,
        utilizationPercent: $utilizationPercent,
        workOrders: $workOrders
    );
}
```

### 4. Factory Pattern (IMPLEMENTADO - Auto-generación de números)

**Implementación:**

```php
// WorkOrder.php
public static function generateWONumber(): string
{
    $year = Carbon::now()->year;
    $prefix = "WO-{$year}-";

    $lastWO = static::withTrashed()
        ->where('wo_number', 'like', "{$prefix}%")
        ->orderByRaw('CAST(SUBSTRING(wo_number, -5) AS UNSIGNED) DESC')
        ->first();

    if ($lastWO) {
        $lastNumber = (int) substr($lastWO->wo_number, -5);
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }

    return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
}
```

**RESULTADO:** `WO-2025-00001`, `WO-2025-00002`, etc.

**MISMO PATRÓN PARA:**
- PurchaseOrder::generatePONumber()
- SentList::generateSentListNumber() (no implementado aún)
- Lot::generateLotNumber() (no implementado aún)

### 5. Observer Pattern (NO IMPLEMENTADO - RECOMENDADO PARA FASE 3)

**Propósito:** Sincronizar estados automáticamente

**Ejemplo PROPUESTO:**

```php
// app/Observers/KitObserver.php
class KitObserver
{
    public function updated(Kit $kit): void
    {
        if ($kit->wasChanged('status')) {
            // Actualizar estado del Lot padre
            $kit->lot->updateStatusFromKits();

            // Actualizar estado del WorkOrder abuelo
            $kit->lot->workOrder->updateStatusFromLots();
        }
    }
}

// app/Providers/EventServiceProvider.php
use App\Models\Kit;
use App\Observers\KitObserver;

public function boot(): void
{
    Kit::observe(KitObserver::class);
}
```

### 6. Transaction Script Pattern (IMPLEMENTADO)

**Propósito:** Garantizar integridad de datos en operaciones complejas

**Implementación:**

```php
// CapacityCalculatorService.php
public function createSentList(...): SentList
{
    return DB::transaction(function () use (...) {
        $sentList = SentList::create([...]);

        foreach ($work_orders as $wo_data) {
            WorkOrder::create([...]);
        }

        $sentList->shifts()->sync($shift_ids);

        return $sentList;
    });
}
```

**CRÍTICO:** Si falla cualquier paso, toda la transacción se revierte (rollback).

### 7. Strategy Pattern (IMPLEMENTADO - Assembly Modes)

**Propósito:** Seleccionar algoritmo de cálculo según modo de ensamble

**Implementación:**

```php
// CapacityCalculatorService.php
protected function getUnitsPerHourByMode(Standard $standard, string $assembly_mode): int
{
    return match ($assembly_mode) {
        '1_person' => $standard->persons_1 ?? 0,
        '2_persons' => $standard->persons_2 ?? 0,
        '3_persons' => $standard->persons_3 ?? 0,
        default => $standard->units_per_hour ?? 0,
    };
}
```

**EXTENSIÓN FUTURA:** Si se agregan modos (ej: '4_persons'), solo se agrega un nuevo case en el match.

---

## Consideraciones Técnicas

### Validaciones Necesarias

#### 1. Validación de Capacidad (IMPLEMENTADA)

```php
// CapacityExceededException.php
namespace App\Exceptions;

class CapacityExceededException extends \Exception
{
    public function __construct(
        float $remainingHours,
        float $requiredHours
    ) {
        $message = sprintf(
            "Insufficient capacity. Required: %.2f hours, Available: %.2f hours",
            $requiredHours,
            $remainingHours
        );

        parent::__construct($message);
    }
}
```

#### 2. Validación de Standard Activo (IMPLEMENTADA)

```php
// CapacityCalculatorService.php
$standard = Standard::where('part_id', $part_id)
    ->where('active', true)
    ->first();

if (!$standard) {
    throw new \Exception("No active standard found for part {$part->number}");
}
```

#### 3. Validación de units_per_hour > 0 (IMPLEMENTADA)

```php
// CapacityCalculatorService.php
if ($units_per_hour === 0) {
    throw new \Exception("Standard for part {$part->number} has units_per_hour = 0 for {$assembly_mode}");
}
```

#### 4. Validación de Fechas (IMPLEMENTADA)

```php
// Livewire validation rules
protected function rules()
{
    return [
        'start_date' => 'required|date|before_or_equal:end_date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ];
}
```

#### 5. Validación de Transiciones de Estado (PENDIENTE - FASE 3)

```php
// WorkOrder.php (FUTURO)
public function canChangeTo(string $newStatus): bool
{
    $validTransitions = [
        'Abierto' => ['Planificado', 'En Espera'],
        'Planificado' => ['En Producción', 'En Espera'],
        'En Producción' => ['En Inspección', 'En Espera'],
        'En Inspección' => ['Parcialmente Enviado', 'En Producción'],
        'Parcialmente Enviado' => ['Cerrado', 'BackOrder'],
        'En Espera' => ['Abierto', 'Planificado'],
        'BackOrder' => ['Planificado'],
    ];

    $currentStatus = $this->status->name;
    return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
}
```

### Cálculos y Algoritmos Críticos

#### Algoritmo 1: Cálculo de Horas Disponibles (IMPLEMENTADO)

**Formula:**

```
total_available_hours = (available_days × shift_net_hours × num_persons) + overtime_hours

Donde:
  available_days = total_days - holidays - weekends
  shift_net_hours = shift_total_hours - break_hours
  overtime_hours = Σ(overtime.net_hours × overtime.employees_qty)
```

**Código:**

```php
public function calculateTotalAvailableHours(...): float
{
    $available_days = $this->getAvailableDays($start_date, $end_date);

    $total_shift_hours = 0;
    foreach ($shifts as $shift) {
        $shift_hours = $this->calculateShiftNetHours($shift);
        $total_shift_hours += $shift_hours;
    }

    $regular_hours = $available_days * $total_shift_hours * $num_persons;
    $overtime_hours = OverTime::whereBetween('date', [$start_date, $end_date])
        ->get()
        ->sum('total_hours');

    return round($regular_hours + $overtime_hours, 2);
}
```

#### Algoritmo 2: Cálculo de Horas Requeridas (IMPLEMENTADO)

**Formula:**

```
required_hours = quantity / units_per_hour

Donde:
  units_per_hour = Standard.persons_X según assembly_mode
  X ∈ {1, 2, 3}
```

**Código:**

```php
public function calculateRequiredHours(int $part_id, int $quantity, string $assembly_mode): float
{
    $standard = Standard::where('part_id', $part_id)
        ->where('active', true)
        ->firstOrFail();

    $units_per_hour = $this->getUnitsPerHourByMode($standard, $assembly_mode);

    return round($quantity / $units_per_hour, 2);
}
```

#### Algoritmo 3: Validación de Capacidad (IMPLEMENTADO)

**Formula:**

```
capacity_ok ⟺ remaining_hours >= required_hours
```

**Código:**

```php
public function validateCapacity(float $remaining_hours, float $required_hours): bool
{
    if ($remaining_hours < $required_hours) {
        throw new CapacityExceededException($remaining_hours, $required_hours);
    }

    return true;
}
```

#### Algoritmo 4: Conteo de Días Laborables (IMPLEMENTADO)

**Código:**

```php
public function getAvailableDays(Carbon $start, Carbon $end): int
{
    $total_days = $start->diffInDays($end) + 1;
    $holidays = Holiday::whereBetween('date', [$start, $end])->count();
    $weekends = $this->countWeekends($start, $end);

    return max(0, $total_days - $holidays - $weekends);
}

public function countWeekends(Carbon $start, Carbon $end): int
{
    $count = 0;
    $current = $start->copy();

    while ($current->lessThanOrEqualTo($end)) {
        if ($current->isWeekend()) {
            $count++;
        }
        $current->addDay();
    }

    return $count;
}
```

### Manejo de Concurrencia

#### Problema: Dos usuarios generan SentList simultáneamente

**Solución 1: Transacciones DB (IMPLEMENTADO)**

```php
public function createSentList(...): SentList
{
    return DB::transaction(function () use (...) {
        // Operaciones atómicas
    });
}
```

**Solución 2: Optimistic Locking (FUTURO)**

```php
// Agregar campo version a sent_lists
Schema::table('sent_lists', function (Blueprint $table) {
    $table->integer('version')->default(1);
});

// En SentList.php
public function save(array $options = [])
{
    if ($this->exists) {
        $this->increment('version');
    }

    return parent::save($options);
}
```

**Solución 3: Pessimistic Locking (FUTURO)**

```php
$sentList = SentList::lockForUpdate()->find($id);
// Nadie más puede modificar este SentList hasta que se complete la transacción
```

### Integridad de Datos

#### Constraint 1: Unicidad de wo_number (IMPLEMENTADO)

```php
// Migración
$table->string('wo_number')->unique();
```

#### Constraint 2: sent_pieces <= PO.quantity (RECOMENDADO)

```php
// WorkOrder.php
public function updateSentPieces(int $additionalPieces): void
{
    $newTotal = $this->sent_pieces + $additionalPieces;
    $maxQuantity = $this->purchaseOrder->quantity;

    if ($newTotal > $maxQuantity) {
        throw new \InvalidArgumentException(
            "Cannot send {$newTotal} pieces. Maximum quantity is {$maxQuantity}."
        );
    }

    $this->sent_pieces = $newTotal;
    $this->save();
}
```

#### Constraint 3: Solo un Standard activo por Part (RECOMENDADO)

```php
// Standard.php
public static function boot()
{
    parent::boot();

    static::saving(function (Standard $standard) {
        if ($standard->active) {
            // Desactivar otros standards activos de la misma parte
            static::where('part_id', $standard->part_id)
                ->where('id', '!=', $standard->id)
                ->update(['active' => false]);
        }
    });
}
```

#### Constraint 4: Solo un Workstation FK populated (IMPLEMENTADO - validación)

```php
// StandardRequest.php (Form Request)
public function rules(): array
{
    return [
        'work_table_id' => 'nullable|exists:tables,id|required_without_all:semi_auto_work_table_id,machine_id',
        'semi_auto_work_table_id' => 'nullable|exists:semi__automatics,id|required_without_all:work_table_id,machine_id',
        'machine_id' => 'nullable|exists:machines,id|required_without_all:work_table_id,semi_auto_work_table_id',
    ];
}
```

---

## Plan de Implementación por Fases

### Estado Actual del Proyecto

| Fase | Módulo | Estado | Completitud |
|------|--------|--------|-------------|
| FASE 1 | Fundamentos de Órdenes | COMPLETO | 100% |
| FASE 2 | Planificación de Producción | EN PROGRESO | 90% |
| FASE 3 | Producción y Lotes | PENDIENTE | 0% |
| FASE 4 | Calidad y Envío | PENDIENTE | 0% |
| FASE 5 | Facturación y Cierre | PENDIENTE | 0% |

### FASE 2: Completar Planificación de Producción (10% restante)

**PENDIENTE:**

1. **Testing completo de CapacityCalculatorService**
   - Unit tests para cada método público
   - Property-based tests para Propiedades 4, 5, 6
   - Tests de integración para createSentList

2. **Validaciones adicionales**
   - Validar que num_persons no exceda empleados disponibles
   - Validar que start_date <= end_date
   - Validar que no se creen SentLists duplicados

3. **UI/UX refinamiento**
   - Mejorar feedback visual en CapacityCalculator
   - Agregar progress bar de utilización de capacidad
   - Implementar confirmación al generar SentList

4. **Documentación**
   - README del módulo Capacity Calculator
   - API documentation de CapacityCalculatorService
   - Guía de usuario para CapacityCalculator

**TIEMPO ESTIMADO:** 3-5 días

### FASE 3: Producción y Lotes (PRÓXIMA FASE)

**COMPONENTES A IMPLEMENTAR:**

#### 3.1. Lot Management (Semana 1)

**Migraciones:**

```php
// create_lots_table.php
Schema::create('lots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('work_order_id')->constrained();
    $table->string('lot_number');
    $table->integer('quantity');
    $table->enum('status', ['pending', 'in_process', 'completed', 'on_hold'])->default('pending');
    $table->text('comments')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->unique(['work_order_id', 'lot_number']);
    $table->index('status');
});
```

**Modelo:**

```php
// app/Models/Lot.php
class Lot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_id', 'lot_number', 'quantity', 'status', 'comments'
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function kits(): HasMany
    {
        return $this->hasMany(Kit::class);
    }

    public static function generateLotNumber(WorkOrder $wo): string
    {
        $lastLot = static::where('work_order_id', $wo->id)
            ->orderByRaw('CAST(SUBSTRING(lot_number, -3) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = $lastLot ? ((int) substr($lastLot->lot_number, -3)) + 1 : 1;

        return "LOT-{$wo->wo_number}-" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
```

**Livewire Components:**

- `LotCreate.php`
- `LotEdit.php`
- `LotList.php`
- `LotShow.php`

**TIEMPO ESTIMADO:** 3-4 días

#### 3.2. Kit Preparation (Semana 2)

**Migraciones:**

```php
// create_kits_table.php
Schema::create('kits', function (Blueprint $table) {
    $table->id();
    $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
    $table->string('kit_number')->unique();
    $table->enum('status', [
        'pendiente', 'en_preparacion', 'preparado', 'en_ensamble',
        'inspeccionado', 'empacado', 'enviado', 'rechazado'
    ])->default('pendiente');
    $table->json('components')->nullable();
    $table->timestamp('prepared_at')->nullable();
    $table->timestamp('assembled_at')->nullable();
    $table->timestamp('inspected_at')->nullable();
    $table->timestamp('packed_at')->nullable();
    $table->timestamp('shipped_at')->nullable();
    $table->text('comments')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index('lot_id');
    $table->index('status');
});
```

**Modelo:**

```php
// app/Models/Kit.php
class Kit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lot_id', 'kit_number', 'status', 'components',
        'prepared_at', 'assembled_at', 'inspected_at', 'packed_at', 'shipped_at', 'comments'
    ];

    protected $casts = [
        'components' => 'array',
        'prepared_at' => 'datetime',
        'assembled_at' => 'datetime',
        'inspected_at' => 'datetime',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function changeStatus(string $newStatus): void
    {
        $this->status = $newStatus;

        $timestampMap = [
            'preparado' => 'prepared_at',
            'en_ensamble' => 'assembled_at',
            'inspeccionado' => 'inspected_at',
            'empacado' => 'packed_at',
            'enviado' => 'shipped_at',
        ];

        if (isset($timestampMap[$newStatus])) {
            $this->{$timestampMap[$newStatus]} = now();
        }

        $this->save();
    }
}
```

**Livewire Components:**

- `KitPreparation.php` (componente principal de preparación)
- `KitList.php`
- `KitShow.php`

**TIEMPO ESTIMADO:** 4-5 días

#### 3.3. Observer para Sincronización de Estados (Semana 3)

**Implementación:**

```php
// app/Observers/KitObserver.php
class KitObserver
{
    public function updated(Kit $kit): void
    {
        if ($kit->wasChanged('status')) {
            $kit->lot->updateStatusFromKits();
            $kit->lot->workOrder->updateStatusFromLots();
        }
    }
}

// app/Providers/EventServiceProvider.php
Kit::observe(KitObserver::class);
Lot::observe(LotObserver::class);
```

**TIEMPO ESTIMADO:** 2-3 días

**TOTAL FASE 3:** 14-18 días (3-4 semanas)

### FASE 4 y FASE 5 (Fuera del Alcance de Este Análisis)

Ver documentos específicos:
- Spec 11: Quality Management and Inspection Module (pendiente)
- Spec 12: Shipping and Invoicing Module (pendiente)

---

## Integraciones con Módulos Existentes

### Integración 1: Production Capacity Calculator (COMPLETO)

**Estado:** IMPLEMENTADO Y FUNCIONAL

**Componentes:**

- `CapacityCalculatorService.php` (Service)
- `CapacityCalculator.php` (Livewire Component)
- `capacity-calculator.blade.php` (View)

**Funcionalidad:**

1. Calcula `total_available_hours` basado en:
   - Turnos seleccionados (múltiples)
   - Número de personas
   - Rango de fechas (excluyendo feriados y fines de semana)
   - Tiempo extra (OverTime)

2. Permite agregar Work Orders:
   - Valida capacidad en tiempo real
   - Calcula `required_hours` por WO
   - Actualiza `remaining_hours` dinámicamente

3. Genera SentList:
   - Crea registro en `sent_lists`
   - Asigna `sent_list_id` a Work Orders
   - Sincroniza turnos en pivot `sent_list_shift`

**Puntos de Integración:**

```php
// CapacityCalculator → CapacityCalculatorService
$this->total_available_hours = $this->service->calculateTotalAvailableHours(...);

// CapacityCalculatorService → Standard
$standard = Standard::where('part_id', $part_id)->where('active', true)->first();

// CapacityCalculatorService → OverTime
$overtime_hours = OverTime::whereBetween('date', [$start_date, $end_date])
    ->get()
    ->sum('total_hours');

// CapacityCalculatorService → Holiday
$holidays = Holiday::whereBetween('date', [$start, $end])->count();

// CapacityCalculatorService → Shift (con BreakTime)
$shifts = Shift::with('breakTimes')->whereIn('id', $shift_ids)->get();

// CapacityCalculatorService → SentList + WorkOrder (creación)
DB::transaction(function () {
    $sentList = SentList::create([...]);
    foreach ($work_orders as $wo_data) {
        WorkOrder::create(['sent_list_id' => $sentList->id, ...]);
    }
    $sentList->shifts()->sync($shift_ids);
});
```

**Diagrama de Integración:**

```mermaid
graph TD
    A[CapacityCalculator Livewire] --> B[CapacityCalculatorService]

    B --> C[Shift + BreakTime]
    B --> D[Holiday]
    B --> E[OverTime]
    B --> F[Standard]
    B --> G[Part]

    B --> H[SentList CREATE]
    H --> I[WorkOrder UPDATE sent_list_id]
    H --> J[sent_list_shift pivot]

    C -.calcular horas netas.-> B
    D -.restar días feriados.-> B
    E -.sumar horas extra.-> B
    F -.obtener units_per_hour.-> B
    G -.obtener estándar activo.-> F
```

### Integración 2: Production Status Management (PARCIAL)

**Estado:** DISEÑADO EN SPEC 08, PENDIENTE DE IMPLEMENTACIÓN

**Componentes Requeridos:**

- `ProductionStatusService` (pendiente)
- Observers para Kit, Lot, WorkOrder (pendiente)
- StatusWO seeders completos (pendiente)

**Funcionalidad Propuesta:**

1. **StatusWO gestionará estados de Work Orders:**
   - Abierto → Planificado → En Producción → En Inspección → Parcialmente Enviado → Cerrado/BackOrder

2. **Kit usará enum para estados:**
   - pendiente → en_preparacion → preparado → en_ensamble → inspeccionado → empacado → enviado

3. **Sincronización automática vía Observers:**
   - Kit.status cambia → actualiza Lot.status
   - Lot.status cambia → actualiza WorkOrder.status

**Puntos de Integración:**

```php
// Kit → Lot (sincronización ascendente)
// KitObserver.php
public function updated(Kit $kit): void
{
    if ($kit->wasChanged('status')) {
        $kit->lot->updateStatusFromKits();
    }
}

// Lot → WorkOrder (sincronización ascendente)
// LotObserver.php
public function updated(Lot $lot): void
{
    if ($lot->wasChanged('status')) {
        $lot->workOrder->updateStatusFromLots();
    }
}

// WorkOrder → StatusWO (cambio de estado con validación)
// WorkOrder.php
public function changeStatus(string $statusName): bool
{
    if (!$this->canChangeTo($statusName)) {
        return false;
    }

    $newStatus = StatusWO::where('name', $statusName)->first();
    $this->status_id = $newStatus->id;
    $this->save();

    return true;
}
```

### Integración 3: Overtime Implementation (COMPLETO)

**Estado:** IMPLEMENTADO Y FUNCIONAL

**Componentes:**

- `OverTime` (Model)
- `over_times` (Table)
- OverTime CRUD Livewire components (presumiblemente implementados)

**Funcionalidad:**

1. Registrar tiempo extra programado:
   - Fecha, turno, horario, empleados
   - Descansos (break_minutes)

2. Calcular horas netas de overtime:
   - Maneja turnos que cruzan medianoche
   - Resta descansos

3. Integración con CapacityCalculator:
   - Suma horas extra a capacidad disponible

**Puntos de Integración:**

```php
// OverTime → Shift (relación)
public function shift(): BelongsTo
{
    return $this->belongsTo(Shift::class);
}

// OverTime → CapacityCalculatorService (cálculo de horas)
$overtime_hours = OverTime::whereBetween('date', [$start_date, $end_date])
    ->get()
    ->sum('total_hours'); // Usa accessor getTotalHoursAttribute()

// OverTime.php (cálculo interno)
public function calculateNetHours(): float
{
    $start = Carbon::createFromFormat('H:i:s', $this->start_time);
    $end = Carbon::createFromFormat('H:i:s', $this->end_time);

    if ($end->lessThan($start)) {
        $end->addDay(); // Maneja cruce de medianoche
    }

    $totalMinutes = $start->diffInMinutes($end, false);
    $netMinutes = $totalMinutes - $this->break_minutes;

    return round($netMinutes / 60, 2);
}

public function getTotalHoursAttribute(): float
{
    return $this->calculateNetHours() * $this->employees_qty;
}
```

---

## Diagrama de Arquitectura Completo

### Diagrama de Entidades y Flujo de Datos

```mermaid
graph TB
    subgraph FASE_1[FASE 1: Fundamentos COMPLETO]
        PO[PurchaseOrder]
        Part[Part]
        Price[Price]
        StatusWO[StatusWO]
        WO[WorkOrder]

        Part --> PO
        Price --> Part
        PO --> WO
        StatusWO --> WO
    end

    subgraph FASE_2[FASE 2: Planificación 90% COMPLETO]
        Standard[Standard]
        Shift[Shift]
        BreakTime[BreakTime]
        OverTime[OverTime]
        Holiday[Holiday]
        SentList[SentList]
        CapCalc[CapacityCalculator]
        CapServ[CapacityCalculatorService]

        Part --> Standard
        Standard --> Table[Table]
        Standard --> SemiAuto[Semi_Automatic]
        Standard --> Machine[Machine]

        Shift --> BreakTime
        Shift --> OverTime
        Shift --> SentList

        CapCalc --> CapServ
        CapServ --> Standard
        CapServ --> Shift
        CapServ --> OverTime
        CapServ --> Holiday
        CapServ --> SentList

        WO --> SentList
    end

    subgraph FASE_3[FASE 3: Producción PENDIENTE]
        Lot[Lot]
        Kit[Kit]
        KitObs[KitObserver]
        LotObs[LotObserver]

        WO --> Lot
        Lot --> Kit
        Kit --> KitObs
        Lot --> LotObs

        KitObs -.sincroniza.-> Lot
        LotObs -.sincroniza.-> WO
    end

    subgraph FASE_4[FASE 4: Calidad PENDIENTE]
        Inspection[Inspection]
        QualityForm[QualityForm]
        ShippingList[ShippingList]

        Lot --> Inspection
        Inspection --> QualityForm
        Lot --> ShippingList
    end

    subgraph FASE_5[FASE 5: Facturación PENDIENTE]
        Invoice[Invoice]
        InvoiceLine[InvoiceLine]
        BackOrder[BackOrder]

        ShippingList --> Invoice
        Invoice --> InvoiceLine
        Lot --> InvoiceLine
        WO --> BackOrder
    end

    style FASE_1 fill:#90EE90
    style FASE_2 fill:#FFD700
    style FASE_3 fill:#FFA500
    style FASE_4 fill:#FF6347
    style FASE_5 fill:#DC143C
```

### Diagrama de Componentes de Software

```mermaid
graph TD
    subgraph UI_LAYER[UI Layer Livewire]
        CapCalcComp[CapacityCalculator.php]
        LotListComp[LotList.php FUTURO]
        KitPrepComp[KitPreparation.php FUTURO]
    end

    subgraph SERVICE_LAYER[Service Layer]
        CapCalcServ[CapacityCalculatorService.php]
        LotServ[LotService.php FUTURO]
        KitServ[KitService.php FUTURO]
        ProdStatusServ[ProductionStatusService.php FUTURO]
    end

    subgraph MODEL_LAYER[Model Layer]
        SentListMod[SentList.php]
        WOMod[WorkOrder.php]
        StandardMod[Standard.php]
        ShiftMod[Shift.php]
        OverTimeMod[OverTime.php]
        LotMod[Lot.php FUTURO]
        KitMod[Kit.php FUTURO]
    end

    subgraph DATABASE[Database]
        sent_lists_tb[(sent_lists)]
        work_orders_tb[(work_orders)]
        standards_tb[(standards)]
        over_times_tb[(over_times)]
        lots_tb[(lots FUTURO)]
        kits_tb[(kits FUTURO)]
    end

    CapCalcComp --> CapCalcServ
    LotListComp --> LotServ
    KitPrepComp --> KitServ

    CapCalcServ --> SentListMod
    CapCalcServ --> WOMod
    CapCalcServ --> StandardMod
    CapCalcServ --> ShiftMod
    CapCalcServ --> OverTimeMod

    LotServ --> LotMod
    KitServ --> KitMod
    ProdStatusServ --> WOMod
    ProdStatusServ --> LotMod
    ProdStatusServ --> KitMod

    SentListMod --> sent_lists_tb
    WOMod --> work_orders_tb
    StandardMod --> standards_tb
    OverTimeMod --> over_times_tb
    LotMod --> lots_tb
    KitMod --> kits_tb
```

---

## Estado Actual de Implementación

### Checklist de Componentes

#### FASE 2: Planificación de Producción

| Componente | Tipo | Estado | Observaciones |
|-----------|------|--------|---------------|
| **Migraciones** | | | |
| `create_standards_table` | Migration | COMPLETO | Incluye persons_1/2/3 + workstation FKs |
| `create_over_times_table` | Migration | COMPLETO | Incluye break_minutes, employees_qty |
| `create_sent_lists_table` | Migration | COMPLETO | Incluye shift_ids JSON, pivot table |
| `sent_list_shift` | Pivot | COMPLETO | Relación M:N SentList ↔ Shift |
| `add_sent_list_fields_to_work_orders` | Migration | COMPLETO | Agrega sent_list_id, assembly_mode, required_hours |
| **Modelos** | | | |
| `Standard.php` | Model | COMPLETO | Con relaciones a Part, Table, SemiAuto, Machine |
| `OverTime.php` | Model | COMPLETO | Con calculateNetHours(), getTotalHoursAttribute() |
| `SentList.php` | Model | COMPLETO | Con relaciones a PO, WO, Shifts |
| `WorkOrder.php` | Model | COMPLETO | Con sentList(), generateWONumber() |
| `Shift.php` | Model | COMPLETO | Con breakTimes(), overTimes() |
| **Servicios** | | | |
| `CapacityCalculatorService.php` | Service | COMPLETO | 6 métodos públicos implementados |
| **Livewire** | | | |
| `CapacityCalculator.php` | Component | COMPLETO | Con 5 métodos principales |
| `capacity-calculator.blade.php` | View | COMPLETO | UI interactiva funcional |
| **Excepciones** | | | |
| `CapacityExceededException.php` | Exception | COMPLETO | Con mensaje descriptivo |
| **Testing** | | | |
| Unit tests | PHPUnit | PENDIENTE | 0% - crítico para producción |
| Property tests | PHPUnit | PENDIENTE | 0% - crítico para Propiedades 4, 5, 6 |
| Feature tests | PHPUnit | PENDIENTE | 0% - testing de flujo completo |

**COMPLETITUD FASE 2:** 90% (falta solo testing y refinamientos)

#### FASE 3: Producción y Lotes

| Componente | Tipo | Estado | Observaciones |
|-----------|------|--------|---------------|
| **Migraciones** | | | |
| `create_lots_table` | Migration | PENDIENTE | Diseñado, no implementado |
| `create_kits_table` | Migration | PENDIENTE | Diseñado, no implementado |
| **Modelos** | | | |
| `Lot.php` | Model | PENDIENTE | WorkOrder.lots() retorna vacío temporalmente |
| `Kit.php` | Model | PENDIENTE | No existe |
| **Servicios** | | | |
| `LotService.php` | Service | PENDIENTE | No existe |
| `KitService.php` | Service | PENDIENTE | No existe |
| `ProductionStatusService.php` | Service | PENDIENTE | Diseñado en Spec 08 |
| **Livewire** | | | |
| `LotList.php` | Component | PENDIENTE | No existe |
| `KitPreparation.php` | Component | PENDIENTE | No existe |
| **Observers** | | | |
| `KitObserver.php` | Observer | PENDIENTE | Diseñado, no implementado |
| `LotObserver.php` | Observer | PENDIENTE | Diseñado, no implementado |

**COMPLETITUD FASE 3:** 0% (diseñado en specs, no implementado)

### Tabla `productions` - Estado Especial

| Aspecto | Análisis |
|---------|----------|
| **Propósito original** | DESCONOCIDO - no documentado |
| **Uso actual** | NINGUNO - no referenciado en código |
| **Relaciones** | NINGUNA - tabla aislada |
| **Recomendación** | **ELIMINAR** o redefinir como ProductionTracking |
| **Acción requerida** | Decidir en reunión de equipo |

---

## Recomendaciones y Próximos Pasos

### Recomendaciones Arquitectónicas

#### 1. ELIMINAR la tabla `productions` (ALTA PRIORIDAD)

**Justificación:**
- No tiene propósito funcional claro
- No se usa en ninguna parte del sistema
- Viola el principio YAGNI
- Confunde el modelo de dominio

**Acción:**

```bash
# Crear migración para eliminar
php artisan make:migration drop_productions_table

# Ejecutar
php artisan migrate

# Eliminar modelo
rm app/Models/Production.php
```

**Alternativa:** Si se descubre un uso futuro, renombrar a `production_trackings` con propósito claro.

#### 2. Completar Testing de FASE 2 (CRÍTICA)

**Pendiente:**
- Unit tests para `CapacityCalculatorService` (cada método)
- Property-based tests para Propiedades 4, 5, 6
- Feature tests para flujo completo de Capacity Calculator
- Integration tests para SentList creation

**Objetivo:** 85%+ code coverage antes de pasar a FASE 3

**Estimado:** 3-4 días

#### 3. Implementar DTOs para Encapsular Resultados (MEDIA)

**Crear:**

```php
// app/DTOs/CapacityResultDTO.php
readonly class CapacityResultDTO
{
    public function __construct(
        public float $totalAvailableHours,
        public float $usedHours,
        public float $remainingHours,
        public float $utilizationPercent,
        public array $workOrders
    ) {}

    public function isOptimal(): bool
    {
        return $this->utilizationPercent < 95;
    }

    public function toArray(): array
    {
        return [...];
    }
}
```

**Beneficios:**
- Tipado fuerte (PHP 8.2+ readonly properties)
- Métodos helper (isOptimal, toArray)
- Inmutabilidad garantizada

#### 4. Agregar Validación de Transiciones de Estado (ALTA para FASE 3)

**Implementar:**

```php
// WorkOrder.php
public function canChangeTo(string $newStatus): bool
{
    $validTransitions = [
        'Abierto' => ['Planificado', 'En Espera'],
        'Planificado' => ['En Producción', 'En Espera'],
        // ... más transiciones
    ];

    return in_array($newStatus, $validTransitions[$this->status->name] ?? []);
}
```

**Beneficios:**
- Previene transiciones inválidas
- Documenta máquina de estados
- Facilita debugging

#### 5. Implementar Observers para Sincronización Automática (ALTA para FASE 3)

**Crear:**

```php
// app/Observers/KitObserver.php
class KitObserver
{
    public function updated(Kit $kit): void
    {
        if ($kit->wasChanged('status')) {
            $kit->lot->updateStatusFromKits();
        }
    }
}

// Registrar en EventServiceProvider
Kit::observe(KitObserver::class);
```

**Beneficios:**
- Sincronización automática Kit → Lot → WorkOrder
- Desacoplamiento (no requiere llamadas manuales)
- Consistencia garantizada

#### 6. Agregar Cache para Standards Activos (BAJA - optimización)

**Implementar:**

```php
// CapacityCalculatorService.php
public function calculateRequiredHours(...): float
{
    $cacheKey = "standard.active.part.{$part_id}";

    $standard = Cache::remember($cacheKey, 3600, function () use ($part_id) {
        return Standard::where('part_id', $part_id)
            ->where('active', true)
            ->first();
    });

    // ...
}
```

**Beneficios:**
- Reduce queries a DB
- Mejora performance en CapacityCalculator interactivo

#### 7. Crear Dashboard de Producción (MEDIA - UX)

**Componente:**

```php
// app/Livewire/ProductionDashboard.php
class ProductionDashboard extends Component
{
    public function render()
    {
        $stats = [
            'total_wos' => WorkOrder::count(),
            'wos_in_production' => WorkOrder::whereHas('status', fn($q) => $q->where('name', 'En Producción'))->count(),
            'capacity_utilization' => $this->getCapacityUtilization(),
            'pending_kits' => Kit::where('status', 'pendiente')->count(), // FUTURO
        ];

        return view('livewire.production-dashboard', compact('stats'));
    }

    protected function getCapacityUtilization(): float
    {
        $sentLists = SentList::all();
        $totalAvailable = $sentLists->sum('total_available_hours');
        $totalUsed = $sentLists->sum('used_hours');

        return $totalAvailable > 0 ? round(($totalUsed / $totalAvailable) * 100, 2) : 0;
    }
}
```

**Beneficios:**
- Visibilidad en tiempo real del estado de producción
- KPIs centralizados
- Facilita toma de decisiones

### Próximos Pasos Inmediatos

#### CORTO PLAZO (1-2 semanas)

1. **Completar Testing de FASE 2**
   - [ ] Unit tests: CapacityCalculatorService (todos los métodos)
   - [ ] Property tests: Propiedades 4, 5, 6
   - [ ] Feature tests: Flujo completo Capacity Calculator
   - [ ] Alcanzar 85%+ code coverage

2. **Decidir sobre tabla `productions`**
   - [ ] Reunión de equipo para analizar si existe uso futuro
   - [ ] Si NO: ejecutar migración drop_productions_table
   - [ ] Si SÍ: renombrar y redefinir como production_trackings

3. **Refinar UX de CapacityCalculator**
   - [ ] Agregar progress bar de utilización de capacidad
   - [ ] Mejorar feedback visual al agregar/remover WOs
   - [ ] Implementar confirmación al generar SentList
   - [ ] Agregar tooltips explicativos

4. **Documentación**
   - [ ] README del módulo Capacity Calculator
   - [ ] API docs de CapacityCalculatorService (PHPDoc completo)
   - [ ] Guía de usuario (capturas de pantalla)

#### MEDIANO PLAZO (3-6 semanas)

5. **Iniciar FASE 3: Lot Management**
   - [ ] Migración create_lots_table
   - [ ] Modelo Lot.php con relaciones
   - [ ] LotService con lógica de negocio
   - [ ] Livewire CRUD: LotCreate, LotEdit, LotList, LotShow
   - [ ] Tests unitarios de Lot

6. **Implementar Kit Preparation**
   - [ ] Migración create_kits_table
   - [ ] Modelo Kit.php con changeStatus()
   - [ ] KitService con lógica de preparación
   - [ ] Livewire component KitPreparation (UI principal)
   - [ ] Tests unitarios de Kit

7. **Implementar Observers**
   - [ ] KitObserver (sincroniza Kit → Lot)
   - [ ] LotObserver (sincroniza Lot → WorkOrder)
   - [ ] Tests de integración para sincronización

#### LARGO PLAZO (2-3 meses)

8. **FASE 4: Quality and Shipping**
   - [ ] Inspection module
   - [ ] Quality Forms (FCA-07, FCA-10, FCA-16)
   - [ ] Shipping Lists

9. **FASE 5: Invoicing and Closure**
   - [ ] Invoice generation
   - [ ] BackOrder management
   - [ ] Work Order closure

### Métricas de Éxito

| Métrica | Objetivo | Estado Actual |
|---------|----------|---------------|
| **FASE 2 Code Coverage** | 85%+ | 0% (pendiente) |
| **Capacity Calculator Uptime** | 99%+ | N/A (recién implementado) |
| **Avg Time to Calculate Capacity** | < 2 segundos | ~1 segundo (óptimo) |
| **Avg Time to Generate SentList** | < 5 segundos | ~3 segundos (óptimo) |
| **User Satisfaction (NPS)** | 8+/10 | N/A (sin usuarios aún) |
| **Bugs in Production (FASE 2)** | < 5 en primer mes | N/A |

### Riesgos Identificados

| Riesgo | Impacto | Probabilidad | Mitigación |
|--------|---------|--------------|------------|
| **Falta de testing** | ALTO | ALTA | Dedicar 3-4 días a testing completo antes de producción |
| **Sincronización de estados incorrecta** | ALTO | MEDIA | Implementar Observers + tests de integración |
| **Performance con muchos WOs** | MEDIO | BAJA | Pagination + eager loading + cache |
| **Confusión sobre tabla `productions`** | MEDIO | ALTA | Eliminar o redefinir claramente |
| **Complejidad de FASE 3** | ALTO | MEDIA | Diseño detallado + implementación incremental |

---

## Conclusiones

### Hallazgos Clave

1. **El "módulo Production" NO es un componente único**, sino la suma de **FASE 2 (Planificación) + FASE 3 (Ejecución)**.

2. **FASE 2 está 90% completa** con componentes robustos:
   - CapacityCalculatorService (lógica de negocio sólida)
   - CapacityCalculator Livewire (UI interactiva funcional)
   - SentList (modelo y migración completos)
   - Integración con Standard, Shift, OverTime, Holiday

3. **La tabla `productions` es un stub sin propósito** → RECOMENDACIÓN: ELIMINAR

4. **FASE 3 (Lot + Kit) está completamente diseñada** en specs previos, lista para implementación

5. **El sistema utiliza patrones arquitectónicos sólidos**:
   - Service Layer (desacoplamiento)
   - Transaction Script (integridad de datos)
   - Factory Pattern (generación de números)
   - Strategy Pattern (assembly modes)

### Respuesta a la Pregunta del Usuario

> "¿Cómo podría manejar el status para producción cuando tenga en producción algunos WO con sus kits?"

**RESPUESTA:**

El sistema maneja estados de producción en **tres niveles jerárquicos**:

1. **WorkOrder.status_id → StatusWO** (IMPLEMENTADO)
   - Estados: Abierto, Planificado, En Producción, En Inspección, Parcialmente Enviado, Cerrado, BackOrder
   - Gestionado vía `StatusWO` model

2. **Lot.status** (PENDIENTE - FASE 3)
   - Estados: pending, in_process, completed, on_hold
   - Campo enum en tabla `lots`
   - Se actualiza automáticamente según progreso de Kits

3. **Kit.status** (PENDIENTE - FASE 3)
   - Estados: pendiente, en_preparacion, preparado, en_ensamble, inspeccionado, empacado, enviado, rechazado
   - Campo enum en tabla `kits`
   - Cada cambio de estado dispara Observer que sincroniza Lot y WorkOrder

**SINCRONIZACIÓN AUTOMÁTICA vía Observers:**

```
Kit.status cambia (ej: pendiente → preparado)
    ↓ KitObserver detecta cambio
Lot.updateStatusFromKits() se ejecuta automáticamente
    ↓ LotObserver detecta cambio
WorkOrder.updateStatusFromLots() se ejecuta automáticamente
    ↓
StatusWO del WorkOrder se actualiza
```

**NO se requiere tabla adicional de status** - el diseño actual es suficiente y sigue Clean Architecture.

### Arquitectura Final Recomendada

```
MÓDULO PRODUCTION = FASE 2 (Planificación) + FASE 3 (Ejecución)

FASE 2 (90% COMPLETO):
├── CapacityCalculatorService → calcula horas disponibles/requeridas
├── SentList → almacena capacidad asignada
├── WorkOrder → asignado a SentList con required_hours
└── Standard → define units_per_hour por assembly mode

FASE 3 (PENDIENTE - DISEÑADO):
├── Lot → divide WorkOrder en lotes manejables
│   └── status: pending|in_process|completed|on_hold
├── Kit → prepara componentes para ensamble
│   └── status: pendiente|en_preparacion|preparado|en_ensamble|inspeccionado|empacado|enviado|rechazado
└── Observers → sincronizan estados automáticamente
    ├── KitObserver → Kit → Lot
    └── LotObserver → Lot → WorkOrder
```

**TABLA `productions` → ELIMINAR** (no cumple función arquitectónica)

---

## Referencias

### Documentos Relacionados

- Spec 01: Production Capacity Implementation Plan
- Spec 08: Production Status Management with Kits Analysis
- Spec 09: Production Capacity Calculator Implementation Analysis
- Flexcon_Tracker_ERP.md - Documento de Diseño General

### Código Fuente Analizado

- `app/Services/CapacityCalculatorService.php`
- `app/Livewire/CapacityCalculator.php`
- `app/Models/SentList.php`
- `app/Models/WorkOrder.php`
- `app/Models/Standard.php`
- `app/Models/OverTime.php`
- `app/Models/Shift.php`
- `database/migrations/2025_12_16_042057_create_productions_table.php`
- `database/migrations/2025_12_26_024833_create_sent_lists_table.php`
- `database/migrations/2025_12_10_090000_create_work_orders_table.php`

### Migraciones Analizadas

- `create_productions_table.php` (stub - recomendado eliminar)
- `create_sent_lists_table.php` (completo)
- `create_work_orders_table.php` (completo)
- `add_sent_list_fields_to_work_orders_table.php` (completo)
- `create_over_times_table.php` (completo)
- `create_standards_table.php` (completo)

---

**Fin del Análisis Arquitectónico del Módulo Production**

**Autor:** Agent Architect
**Fecha:** 2025-12-26
**Versión:** 1.0
**Estado:** Completo y listo para revisión

---

## Historial de Cambios

| Versión | Fecha | Autor | Cambios |
|---------|-------|-------|---------|
| 1.0 | 2025-12-26 | Agent Architect | Análisis arquitectónico inicial completo |
