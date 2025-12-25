# Spec 08: Estrategias de Manejo de Status de Producción con Work Orders y Kits

**Fecha de Creación:** 2025-12-25
**Autor:** Agent Architect
**Fase del Proyecto:** FASE 2 - Planificación de Producción
**Estado:** Análisis Técnico Completo
**Versión:** 1.0
**Relacionado con:**
- ProductionStatus_Error_Analisis.md (docs)
- Spec 01 - Plan de Implementación Capacidad de Producción
- Spec 07 - Análisis Técnico Over Time Module
- db.mkd - Esquema de Base de Datos

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Contexto del Problema](#contexto-del-problema)
3. [Análisis de la Arquitectura Actual](#análisis-de-la-arquitectura-actual)
4. [Opciones de Diseño para Manejo de Status](#opciones-de-diseño-para-manejo-de-status)
5. [Análisis Comparativo de Opciones](#análisis-comparativo-de-opciones)
6. [Recomendación](#recomendación)
7. [Plan de Implementación](#plan-de-implementación)
8. [Consideraciones Técnicas](#consideraciones-técnicas)
9. [Ejemplos de Uso](#ejemplos-de-uso)
10. [Referencias](#referencias)

---

## Resumen Ejecutivo

### Problema a Resolver

Según el análisis previo (ProductionStatus_Error_Analisis.md), se determinó que:
- La tabla `productions` NO debe tener relación directa con `ProductionStatus`
- Las entidades reales de producción son: `tables`, `semi_automatics`, y `machines`
- Se eliminó la relación incorrecta `productions → production_status_id`

**PREGUNTA CLAVE DEL USUARIO:**
"¿Cómo podría manejar el status para producción cuando tenga en producción algunos WO (Work Orders) con sus kits?"

### Hallazgos Principales

Después de analizar el sistema completo, identificamos que:

1. **Work Orders (WO)** ya tienen su propio sistema de status → `StatusWO` (tabla `statuses_wo`)
2. **Production Status** controla el estado de **recursos físicos** (mesas, máquinas, semi-automáticos)
3. **Kits** son componentes que se ensamblan como parte de un WO (Fase 3 según flujo general)
4. **NO existe tabla `productions`** funcional - es un stub que debe eliminarse o redefinirse

### Decisión Arquitectural Crítica

**CONCLUSIÓN:** El sistema ya tiene los mecanismos necesarios para manejar estados de producción de WOs con kits:

- **WO Status** (StatusWO) → Estado del Work Order (Abierto, En Producción, Cerrado, BackOrder)
- **Production Status** (ProductionStatus) → Estado de los recursos físicos (Disponible, En Uso, Mantenimiento)
- **Lot Status** (campo en Lot) → Estado de los lotes (Pendiente, En Proceso, Completado)
- **Kit Status** (a implementar) → Estado de preparación de kits (Pendiente, Preparado, Asignado)

**NO se requiere nueva tabla de status** - solo implementar correctamente las relaciones existentes.

---

## Contexto del Problema

### Flujo de Producción según Diagrama General

```
flowchart TD
A[Recibir PO] --> B{Validar Precio}
B -->|OK| C[Crear WO]
B -->|Error| D[Solicitar Corrección]
C --> E[Calcular Capacidad]
E --> F[Lista Envío Preliminar]
F --> G[Preparar Kits]
G --> H[Ensamble]
H --> I[Inspección]
I -->|OK| J[Empaque]
I -->|Rechazo| K[Acción Correctiva]
K --> H
J --> L[Shipping List]
L --> M[Invoice]
M --> N{WO Completo?}
N -->|Sí| O[Cerrar WO]
N -->|No| P[BackOrder]
P --> E
```

### Entidades Clave en el Flujo

| Entidad | Propósito | Status Asociado | Fase |
|---------|-----------|-----------------|------|
| **PurchaseOrder** | Orden del cliente | `status` (pending, approved, rejected) | Fase 1 |
| **WorkOrder** | Orden de trabajo interna | `status_id` → `StatusWO` | Fase 1 |
| **Kit** | Componentes para ensamblar | `status` (a definir) | Fase 3 |
| **Lot** | Agrupación de producción | `status` (pending, in_process, completed) | Fase 3 |
| **Table/Machine/SemiAutomatic** | Recursos físicos | `production_status_id` → `ProductionStatus` | Fase 2 |

### Escenario del Usuario

> "Cuando tenga en producción algunos WO con sus kits"

Esto implica:
1. **Múltiples Work Orders activos simultáneamente** (WO-2025-00001, WO-2025-00002, etc.)
2. **Cada WO puede tener múltiples Kits** (Kit A, Kit B, Kit C)
3. **Cada Kit puede tener múltiples componentes** (tornillos, cables, placas)
4. **Los recursos físicos** (mesas, máquinas) están asignados a diferentes WOs
5. **Necesidad de rastrear el estado de cada WO y sus kits**

---

## Análisis de la Arquitectura Actual

### Modelo de Datos Existente

#### 1. Work Order → Status

**Archivo:** `app/Models/WorkOrder.php`

```php
class WorkOrder extends Model
{
    protected $fillable = [
        'wo_number',
        'purchase_order_id',
        'status_id',  // ← FK a statuses_wo
        'sent_pieces',
        'scheduled_send_date',
        'actual_send_date',
        'opened_date',
        'eq',
        'pr',
        'comments',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusWO::class, 'status_id');
    }
}
```

**Tabla:** `statuses_wo`

```php
class StatusWO extends Model
{
    protected $fillable = [
        'name',     // "Abierto", "En Producción", "Cerrado", etc.
        'color',    // Para UI
        'comments',
    ];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'status_id');
    }
}
```

#### 2. Production Status → Recursos Físicos

**Archivo:** `app/Models/ProductionStatus.php`

```php
class ProductionStatus extends Model
{
    protected $fillable = ['name', 'color', 'comments'];

    // Relaciones CORRECTAS (según análisis previo)
    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function semiAutomatics(): HasMany
    {
        return $this->hasMany(Semi_Automatic::class);
    }

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }

    // ELIMINADA: public function productions() ← Ya no existe
}
```

**Ejemplo de uso:**
- ProductionStatus: "Disponible" → Table #5 está libre
- ProductionStatus: "En Uso" → Machine #12 está produciendo
- ProductionStatus: "Mantenimiento" → SemiAutomatic #3 en reparación

#### 3. Tabla `productions` (PROBLEMA)

**Archivo:** `app/Models/Production.php`

```php
class Production extends Model
{
    use HasFactory;
    // ← VACÍO - Sin fillable, sin relaciones
}
```

**Migración:** `2025_12_16_042057_create_productions_table.php`

```php
Schema::create('productions', function (Blueprint $table) {
    $table->id();
    $table->string('number')->unique();
    $table->timestamps();
});
```

**ANÁLISIS:**
- Tabla stub sin propósito claro
- No tiene relaciones funcionales
- Nombre genérico que causa confusión
- **RECOMENDACIÓN:** Eliminar o redefinir con propósito específico

---

## Opciones de Diseño para Manejo de Status

### Opción 1: Usar StatusWO Existente (SIMPLE - RECOMENDADA)

**Concepto:** Aprovechar el sistema de status de Work Orders ya implementado

#### Estructura

```
┌──────────────┐         ┌──────────────┐
│  StatusWO    │◄───────┤  WorkOrder   │
│              │  FK     │              │
│ - id         │         │ - id         │
│ - name       │         │ - status_id  │
│ - color      │         │ - wo_number  │
└──────────────┘         └──────────────┘
                               │
                               │ hasMany
                               ▼
                         ┌──────────────┐
                         │    Lot       │
                         │              │
                         │ - id         │
                         │ - wo_id      │
                         │ - status     │
                         └──────────────┘
                               │
                               │ hasMany
                               ▼
                         ┌──────────────┐
                         │    Kit       │
                         │              │
                         │ - id         │
                         │ - lot_id     │
                         │ - status     │
                         └──────────────┘
```

#### Flujo de Estados del WO

```
[ABIERTO]
   ↓
   ├─→ Calcular Capacidad
   ├─→ Crear Lista Envío Preliminar
   ↓
[EN PRODUCCIÓN]
   ↓
   ├─→ Preparar Kits → Kit.status = "preparado"
   ├─→ Ensamblar → Lot.status = "en_proceso"
   ├─→ Inspeccionar → Inspection.result
   ├─→ Empacar
   ↓
[PARCIALMENTE ENVIADO]
   ↓
   ├─→ Crear Shipping List
   ├─→ Generar Invoice
   ├─→ sent_pieces aumenta
   ↓
[CERRADO] (si sent_pieces == PO.quantity)
   O
[BACKORDER] (si quedan piezas pendientes)
```

#### Estados Sugeridos de StatusWO

| ID | Name | Description | Color |
|----|------|-------------|-------|
| 1 | Abierto | WO creado, esperando planificación | Blue |
| 2 | Planificado | Capacidad calculada, en calendario | Yellow |
| 3 | En Producción | Kits preparados, ensamblando | Orange |
| 4 | En Inspección | Lotes completados, inspeccionando | Purple |
| 5 | Parcialmente Enviado | Algunos lotes enviados | Teal |
| 6 | Cerrado | Completado al 100% | Green |
| 7 | BackOrder | Pendiente con piezas restantes | Red |
| 8 | En Espera | Bloqueado por alguna razón | Gray |

#### Status en Nivel de Kit (Campo Enum/String)

```php
// En migración de Kits (Fase 3)
Schema::create('kits', function (Blueprint $table) {
    $table->id();
    $table->foreignId('lot_id')->constrained();
    $table->string('kit_number');
    $table->enum('status', [
        'pendiente',      // Kit creado, componentes no preparados
        'en_preparacion', // Recolectando componentes
        'preparado',      // Listo para ensamble
        'en_ensamble',    // Siendo ensamblado
        'inspeccionado',  // Pasó inspección
        'empacado',       // Listo para envío
        'enviado',        // Incluido en Shipping List
    ])->default('pendiente');
    $table->text('components')->nullable(); // JSON de componentes
    $table->timestamps();
});
```

#### Ventajas

✅ **Simplicidad:** No agrega nuevas tablas de status
✅ **Coherencia:** Usa arquitectura ya implementada
✅ **Claridad:** Cada nivel tiene su status (WO → Lot → Kit)
✅ **Escalabilidad:** Fácil agregar nuevos estados según necesidad
✅ **Mantenibilidad:** Menos tablas = menos complejidad
✅ **Performance:** Menos JOINs en queries

#### Desventajas

⚠️ **Status hardcodeados:** Kits usan enum en lugar de tabla dinámica
⚠️ **Migración de datos:** Si se cambian estados, requiere migración

---

### Opción 2: Crear Tabla KitStatus (COMPLEJA)

**Concepto:** Crear una tabla dedicada para estados de kits

#### Estructura

```
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│  StatusWO    │◄───────┤  WorkOrder   │────────►│ Production   │
│              │  FK     │              │  hasOne │  Tracking    │
└──────────────┘         └──────────────┘         └──────────────┘
                               │
                               │ hasMany
                               ▼
                         ┌──────────────┐
                         │    Lot       │
                         └──────────────┘
                               │
                               │ hasMany
                               ▼
┌──────────────┐         ┌──────────────┐
│  KitStatus   │◄───────┤    Kit       │
│              │  FK     │              │
│ - id         │         │ - kit_status_id
│ - name       │         └──────────────┘
│ - color      │
│ - order      │
└──────────────┘
```

#### Migración

```php
// Tabla kit_statuses
Schema::create('kit_statuses', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('color');
    $table->integer('order')->default(0); // Para ordenar en pipeline
    $table->text('description')->nullable();
    $table->boolean('active')->default(true);
    $table->timestamps();
});

// Agregar FK en kits
Schema::table('kits', function (Blueprint $table) {
    $table->foreignId('kit_status_id')
          ->constrained('kit_statuses')
          ->restrictOnDelete();
});
```

#### Seeder de KitStatus

```php
KitStatus::create([
    ['name' => 'Pendiente', 'slug' => 'pending', 'color' => 'gray', 'order' => 1],
    ['name' => 'En Preparación', 'slug' => 'preparing', 'color' => 'blue', 'order' => 2],
    ['name' => 'Preparado', 'slug' => 'ready', 'color' => 'yellow', 'order' => 3],
    ['name' => 'En Ensamble', 'slug' => 'assembling', 'color' => 'orange', 'order' => 4],
    ['name' => 'Inspeccionado', 'slug' => 'inspected', 'color' => 'purple', 'order' => 5],
    ['name' => 'Empacado', 'slug' => 'packed', 'color' => 'teal', 'order' => 6],
    ['name' => 'Enviado', 'slug' => 'shipped', 'color' => 'green', 'order' => 7],
]);
```

#### Ventajas

✅ **Flexibilidad:** Estados configurables sin migración
✅ **Reutilización:** Múltiples kits pueden compartir mismo status
✅ **UI Dinámica:** Colores y orden configurables
✅ **Auditabilidad:** Cambios de status rastreables
✅ **Extensibilidad:** Fácil agregar nuevos estados sin código

#### Desventajas

❌ **Complejidad:** Más tablas, más relaciones, más código
❌ **Over-Engineering:** Puede ser excesivo para el alcance actual
❌ **Performance:** JOINs adicionales en queries
❌ **Mantenimiento:** Más seeders, más factories, más tests

---

### Opción 3: Tabla Production_Tracking Unificada (HÍBRIDA)

**Concepto:** Redefinir tabla `productions` como Production_Tracking para unificar estado de producción

#### Propósito

Crear una tabla que consolide:
- Estado general de producción del WO
- Asignación de recursos (mesas, máquinas)
- Progreso de kits
- Métricas de producción

#### Estructura

```
┌──────────────────────┐
│ Production_Tracking  │
├──────────────────────┤
│ - id                 │
│ - work_order_id (FK) │
│ - started_at         │
│ - completed_at       │
│ - total_kits         │
│ - kits_completed     │
│ - assigned_table_id  │
│ - assigned_machine_id│
│ - current_phase      │ ← enum: prep, assembly, inspection, packing
│ - progress_percent   │
│ - comments           │
└──────────────────────┘
```

#### Migración

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
        'planning',      // Planificando
        'preparation',   // Preparando kits
        'assembly',      // Ensamblando
        'inspection',    // Inspeccionando
        'packing',       // Empacando
        'completed',     // Completado
    ])->default('planning');

    $table->text('comments')->nullable();
    $table->timestamps();
});
```

#### Modelo

```php
class ProductionTracking extends Model
{
    protected $fillable = [
        'work_order_id',
        'started_at',
        'completed_at',
        'total_kits',
        'kits_completed',
        'assigned_table_id',
        'assigned_machine_id',
        'current_phase',
        'progress_percent',
        'comments',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percent' => 'decimal:2',
    ];

    // Relaciones
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function assignedTable(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'assigned_table_id');
    }

    public function assignedMachine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'assigned_machine_id');
    }

    // Métodos de negocio
    public function updateProgress(): void
    {
        $this->progress_percent = ($this->kits_completed / max($this->total_kits, 1)) * 100;
        $this->save();
    }

    public function isInProgress(): bool
    {
        return $this->started_at !== null && $this->completed_at === null;
    }

    public function complete(): void
    {
        $this->completed_at = now();
        $this->progress_percent = 100;
        $this->current_phase = 'completed';
        $this->save();
    }
}
```

#### Ventajas

✅ **Centralización:** Un lugar para toda la info de producción del WO
✅ **Métricas:** Progreso calculado automáticamente
✅ **Asignación:** Recursos asignados visibles
✅ **Timeline:** Fechas de inicio y fin
✅ **Dashboard:** Fácil construir vistas de producción

#### Desventajas

⚠️ **Complejidad media:** Requiere lógica adicional de actualización
⚠️ **Sincronización:** Debe mantenerse sincronizado con Kits y Lots
⚠️ **Duplicación:** Alguna info puede estar en WO.status

---

### Opción 4: Event-Driven Status (AVANZADA)

**Concepto:** Usar eventos de Laravel para actualizar estados automáticamente

#### Arquitectura

```php
// Eventos
KitPrepared::class
KitAssembled::class
KitInspected::class
LotCompleted::class
WOPhaseChanged::class

// Listeners
UpdateKitStatus::class
UpdateLotStatus::class
UpdateWorkOrderStatus::class
NotifyProductionManager::class
RecalculateCapacity::class
```

#### Flujo de Ejemplo

```php
// En controlador de producción
public function markKitAsPrepared(Kit $kit): void
{
    $kit->update(['status' => 'preparado']);

    // Disparar evento
    event(new KitPrepared($kit));
}

// Listener: UpdateLotStatus
public function handle(KitPrepared $event): void
{
    $kit = $event->kit;
    $lot = $kit->lot;

    // Si todos los kits del lote están preparados
    if ($lot->kits()->where('status', '!=', 'preparado')->count() === 0) {
        $lot->update(['status' => 'ready_for_assembly']);
        event(new LotReadyForAssembly($lot));
    }
}

// Listener: UpdateWorkOrderStatus
public function handle(LotReadyForAssembly $event): void
{
    $lot = $event->lot;
    $wo = $lot->workOrder;

    // Si el WO estaba en "Abierto", cambiar a "En Producción"
    if ($wo->status->name === 'Abierto') {
        $newStatus = StatusWO::where('name', 'En Producción')->first();
        $wo->update(['status_id' => $newStatus->id]);
        event(new WOPhaseChanged($wo, 'production'));
    }
}
```

#### Ventajas

✅ **Automatización:** Estados se actualizan automáticamente
✅ **Desacoplamiento:** Lógica separada en listeners
✅ **Auditabilidad:** Eventos registrables en log
✅ **Extensibilidad:** Fácil agregar nuevas acciones
✅ **Testing:** Listeners testeables independientemente

#### Desventajas

❌ **Complejidad alta:** Muchos eventos y listeners
❌ **Debugging difícil:** Cascada de eventos puede ser confusa
❌ **Performance:** Overhead de dispatching eventos
❌ **Over-Engineering:** Puede ser excesivo para MVP

---

## Análisis Comparativo de Opciones

### Tabla de Comparación

| Criterio | Opción 1<br>StatusWO | Opción 2<br>KitStatus | Opción 3<br>Production_Tracking | Opción 4<br>Event-Driven |
|----------|---------------------|----------------------|--------------------------------|-------------------------|
| **Complejidad** | ⭐ Baja | ⭐⭐ Media | ⭐⭐⭐ Media-Alta | ⭐⭐⭐⭐ Alta |
| **Tiempo Implementación** | 1-2 días | 3-4 días | 4-5 días | 7-10 días |
| **Mantenibilidad** | ⭐⭐⭐⭐⭐ Alta | ⭐⭐⭐⭐ Alta | ⭐⭐⭐ Media | ⭐⭐ Baja |
| **Escalabilidad** | ⭐⭐⭐ Media | ⭐⭐⭐⭐ Alta | ⭐⭐⭐⭐ Alta | ⭐⭐⭐⭐⭐ Muy Alta |
| **Performance** | ⭐⭐⭐⭐⭐ Excelente | ⭐⭐⭐⭐ Buena | ⭐⭐⭐ Media | ⭐⭐⭐ Media |
| **Flexibilidad** | ⭐⭐⭐ Media | ⭐⭐⭐⭐⭐ Muy Alta | ⭐⭐⭐⭐ Alta | ⭐⭐⭐⭐⭐ Muy Alta |
| **Testing** | ⭐⭐⭐⭐⭐ Fácil | ⭐⭐⭐⭐ Fácil | ⭐⭐⭐ Medio | ⭐⭐ Difícil |
| **Documentación** | ⭐⭐⭐⭐ Buena | ⭐⭐⭐ Media | ⭐⭐⭐ Media | ⭐⭐ Baja |

### Casos de Uso Recomendados

#### Opción 1: StatusWO Existente
**RECOMENDADO PARA:**
- ✅ MVP / Proof of Concept
- ✅ Equipos pequeños
- ✅ Presupuesto/tiempo limitado
- ✅ Flujo de producción simple

**NO RECOMENDADO PARA:**
- ❌ Flujos de producción muy complejos
- ❌ Múltiples tipos de kits con estados diferentes
- ❌ Necesidad de reportes detallados por fase

#### Opción 2: KitStatus Dedicado
**RECOMENDADO PARA:**
- ✅ Producción con muchos tipos de kits
- ✅ Necesidad de configurar estados sin código
- ✅ Reportes detallados de kits
- ✅ UI con pipeline visual de kits

**NO RECOMENDADO PARA:**
- ❌ Producción simple con pocos kits
- ❌ Equipos sin experiencia en Laravel avanzado
- ❌ Proyectos con poco tiempo de desarrollo

#### Opción 3: Production_Tracking
**RECOMENDADO PARA:**
- ✅ Necesidad de dashboard de producción
- ✅ Asignación explícita de recursos
- ✅ Reportes de progreso en tiempo real
- ✅ Métricas de producción (KPIs)

**NO RECOMENDADO PARA:**
- ❌ Producción sin recursos asignados fijos
- ❌ WOs con flujos muy variables
- ❌ Equipos sin tiempo para sincronización de datos

#### Opción 4: Event-Driven
**RECOMENDADO PARA:**
- ✅ Sistemas grandes con muchas integraciones
- ✅ Necesidad de auditoría completa
- ✅ Equipos con experiencia en Event Sourcing
- ✅ Automatización de flujos complejos

**NO RECOMENDADO PARA:**
- ❌ MVP o prototipos
- ❌ Equipos junior
- ❌ Proyectos con deadline corto
- ❌ Sistemas sin necesidad de trazabilidad avanzada

---

## Recomendación

### Estrategia de Implementación en Fases

Después de analizar las 4 opciones, recomiendo un **enfoque híbrido progresivo**:

### FASE INICIAL (AHORA - MVP)

**IMPLEMENTAR: Opción 1 - StatusWO Existente**

**Justificación:**
1. ✅ Ya está implementado (tabla `statuses_wo`, modelo `StatusWO`)
2. ✅ Requiere mínimos cambios al código actual
3. ✅ Permite lanzar rápido y validar el flujo
4. ✅ Bajo riesgo de bugs o problemas de performance

**Cambios Requeridos:**

1. **Agregar Estados de Kit (enum simple)**
```php
// En migración de kits (Fase 3)
$table->enum('status', [
    'pendiente',
    'preparado',
    'en_ensamble',
    'inspeccionado',
    'empacado',
    'enviado'
])->default('pendiente');
```

2. **Agregar Estados de Lot (enum simple)**
```php
// Ya existe, verificar enum
$table->enum('status', [
    'pending',
    'in_process',
    'completed',
    'on_hold'
])->default('pending');
```

3. **Usar StatusWO para Work Orders**
```php
// Ya implementado - solo agregar estados necesarios
StatusWO::create(['name' => 'Planificado', 'color' => 'yellow']);
StatusWO::create(['name' => 'En Producción', 'color' => 'orange']);
StatusWO::create(['name' => 'Parcialmente Enviado', 'color' => 'teal']);
```

### FASE INTERMEDIA (3-6 MESES DESPUÉS)

**EVALUAR NECESIDAD DE: Opción 3 - Production_Tracking**

**Solo si se detectan estos problemas:**
- ⚠️ Dificultad para asignar recursos (mesas/máquinas) a WOs
- ⚠️ Necesidad de dashboards de producción en tiempo real
- ⚠️ Reportes de progreso requieren muchos JOINs

**Implementación incremental:**
1. Crear tabla `production_trackings` sin eliminar lo existente
2. Poblar automáticamente desde WOs existentes
3. Migrar queries de reportes gradualmente
4. Mantener retrocompatibilidad

### FASE AVANZADA (1+ AÑO DESPUÉS)

**CONSIDERAR: Opción 4 - Event-Driven**

**Solo si el sistema crece a:**
- 📈 100+ Work Orders simultáneos
- 📈 Múltiples plantas/locaciones
- 📈 Integraciones con sistemas externos (ERP, MES)
- 📈 Necesidad de auditoría completa

---

## Plan de Implementación

### PLAN INMEDIATO: Opción 1 - StatusWO

#### Paso 1: Completar Seeders de StatusWO (30 min)

**Archivo:** `database/seeders/StatusWOSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\StatusWO;
use Illuminate\Database\Seeder;

class StatusWOSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Abierto',
                'color' => 'blue',
                'comments' => 'WO creado, esperando planificación de capacidad'
            ],
            [
                'name' => 'Planificado',
                'color' => 'yellow',
                'comments' => 'Capacidad calculada, en calendario de producción'
            ],
            [
                'name' => 'En Producción',
                'color' => 'orange',
                'comments' => 'Kits preparados, ensamblando en piso de producción'
            ],
            [
                'name' => 'En Inspección',
                'color' => 'purple',
                'comments' => 'Lotes completados, en proceso de inspección de calidad'
            ],
            [
                'name' => 'Parcialmente Enviado',
                'color' => 'teal',
                'comments' => 'Algunos lotes enviados, otros pendientes'
            ],
            [
                'name' => 'Cerrado',
                'color' => 'green',
                'comments' => 'Completado al 100%, todas las piezas enviadas'
            ],
            [
                'name' => 'BackOrder',
                'color' => 'red',
                'comments' => 'Piezas pendientes, requiere nueva planificación'
            ],
            [
                'name' => 'En Espera',
                'color' => 'gray',
                'comments' => 'Bloqueado por falta de materiales u otra razón'
            ],
        ];

        foreach ($statuses as $status) {
            StatusWO::create($status);
        }

        $this->command->info('✅ Estados de Work Order creados');
    }
}
```

**Ejecutar:**
```bash
php artisan db:seed --class=StatusWOSeeder
```

#### Paso 2: Agregar Métodos Helper en WorkOrder (1 hora)

**Archivo:** `app/Models/WorkOrder.php`

```php
/**
 * Obtener el progreso del WO (porcentaje enviado)
 */
public function getProgressPercentAttribute(): float
{
    if ($this->original_quantity === 0) {
        return 0;
    }

    return round(($this->sent_pieces / $this->original_quantity) * 100, 2);
}

/**
 * Cambiar estado del WO con validación
 */
public function changeStatus(string $statusName): bool
{
    $newStatus = StatusWO::where('name', $statusName)->first();

    if (!$newStatus) {
        throw new \Exception("Status '{$statusName}' no existe");
    }

    $this->status_id = $newStatus->id;
    $this->save();

    // Log del cambio (opcional)
    $this->statusLogs()->create([
        'status_id' => $newStatus->id,
        'changed_at' => now(),
        'changed_by' => auth()->id(),
    ]);

    return true;
}

/**
 * Verificar si puede cambiar a cierto estado
 */
public function canChangeTo(string $statusName): bool
{
    $currentStatus = $this->status->name;

    // Definir transiciones válidas
    $validTransitions = [
        'Abierto' => ['Planificado', 'En Espera'],
        'Planificado' => ['En Producción', 'En Espera'],
        'En Producción' => ['En Inspección', 'En Espera'],
        'En Inspección' => ['Parcialmente Enviado', 'En Producción', 'En Espera'],
        'Parcialmente Enviado' => ['Cerrado', 'BackOrder'],
        'En Espera' => ['Abierto', 'Planificado'],
        'BackOrder' => ['Planificado'],
    ];

    return in_array($statusName, $validTransitions[$currentStatus] ?? []);
}

/**
 * Obtener color del status para UI
 */
public function getStatusColorAttribute(): string
{
    return $this->status->color ?? 'gray';
}

/**
 * Scope: Filtrar por nombre de status
 */
public function scopeWithStatus(Builder $query, string $statusName): Builder
{
    return $query->whereHas('status', function ($q) use ($statusName) {
        $q->where('name', $statusName);
    });
}
```

#### Paso 3: Implementar Migración de Kits con Status (Fase 3)

**NOTA:** Esto es para cuando se implemente la Fase 3

**Archivo:** `database/migrations/YYYY_MM_DD_create_kits_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->string('kit_number')->unique();

            // Status del kit
            $table->enum('status', [
                'pendiente',      // Creado, componentes no preparados
                'en_preparacion', // Recolectando componentes
                'preparado',      // Listo para ensamble
                'en_ensamble',    // Siendo ensamblado
                'inspeccionado',  // Pasó inspección
                'empacado',       // Listo para envío
                'enviado',        // Incluido en Shipping List
                'rechazado',      // Falló inspección
            ])->default('pendiente');

            // Componentes del kit (JSON)
            $table->json('components')->nullable()->comment('Lista de componentes requeridos');

            // Trazabilidad
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('assembled_at')->nullable();
            $table->timestamp('inspected_at')->nullable();
            $table->timestamp('packed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();

            $table->text('comments')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('lot_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kits');
    }
};
```

#### Paso 4: Crear Modelo Kit (Fase 3)

**Archivo:** `app/Models/Kit.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lot_id',
        'kit_number',
        'status',
        'components',
        'prepared_at',
        'assembled_at',
        'inspected_at',
        'packed_at',
        'shipped_at',
        'comments',
    ];

    protected $casts = [
        'components' => 'array',
        'prepared_at' => 'datetime',
        'assembled_at' => 'datetime',
        'inspected_at' => 'datetime',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    /**
     * Relación con Lot
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    /**
     * Cambiar estado del kit con timestamp
     */
    public function changeStatus(string $newStatus): void
    {
        $this->status = $newStatus;

        // Actualizar timestamp correspondiente
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

    /**
     * Verificar si el kit está listo para ensamble
     */
    public function isReadyForAssembly(): bool
    {
        return $this->status === 'preparado';
    }

    /**
     * Verificar si el kit está completado
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['empacado', 'enviado']);
    }

    /**
     * Scope: Kits por estado
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Kits pendientes
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pendiente', 'en_preparacion']);
    }

    /**
     * Scope: Kits en producción
     */
    public function scopeInProduction($query)
    {
        return $query->whereIn('status', ['preparado', 'en_ensamble', 'inspeccionado']);
    }
}
```

#### Paso 5: Actualizar Modelo Lot (1 hora)

**Archivo:** `app/Models/Lot.php`

```php
/**
 * Relación con Kits
 */
public function kits(): HasMany
{
    return $this->hasMany(Kit::class);
}

/**
 * Obtener progreso del lote (% de kits completados)
 */
public function getProgressPercentAttribute(): float
{
    $total = $this->kits()->count();

    if ($total === 0) {
        return 0;
    }

    $completed = $this->kits()
                      ->whereIn('status', ['empacado', 'enviado'])
                      ->count();

    return round(($completed / $total) * 100, 2);
}

/**
 * Cambiar estado del lote automáticamente según kits
 */
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

#### Paso 6: Crear Servicio de Gestión de Status (2 horas)

**Archivo:** `app/Services/ProductionStatusService.php`

```php
<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\Lot;
use App\Models\Kit;
use App\Models\StatusWO;

class ProductionStatusService
{
    /**
     * Actualizar status de WO basado en progreso de kits
     */
    public function updateWorkOrderStatus(WorkOrder $wo): void
    {
        $totalPieces = $wo->original_quantity;
        $sentPieces = $wo->sent_pieces;
        $pendingPieces = $totalPieces - $sentPieces;

        // Completado
        if ($pendingPieces === 0) {
            $wo->changeStatus('Cerrado');
            return;
        }

        // Parcialmente enviado
        if ($sentPieces > 0) {
            $wo->changeStatus('Parcialmente Enviado');
            return;
        }

        // Verificar si tiene kits en inspección
        $hasKitsInInspection = $wo->lots()
            ->whereHas('kits', function ($q) {
                $q->where('status', 'inspeccionado');
            })
            ->exists();

        if ($hasKitsInInspection) {
            $wo->changeStatus('En Inspección');
            return;
        }

        // Verificar si tiene kits en producción
        $hasKitsInProduction = $wo->lots()
            ->whereHas('kits', function ($q) {
                $q->whereIn('status', ['preparado', 'en_ensamble']);
            })
            ->exists();

        if ($hasKitsInProduction) {
            $wo->changeStatus('En Producción');
            return;
        }

        // Si no tiene kits o están todos pendientes
        $wo->changeStatus('Planificado');
    }

    /**
     * Actualizar status de todos los lotes de un WO
     */
    public function updateLotsStatus(WorkOrder $wo): void
    {
        $lots = $wo->lots;

        foreach ($lots as $lot) {
            $lot->updateStatusFromKits();
        }
    }

    /**
     * Obtener resumen de status de un WO
     */
    public function getWorkOrderStatusSummary(WorkOrder $wo): array
    {
        $lots = $wo->lots()->with('kits')->get();

        $summary = [
            'wo_status' => $wo->status->name,
            'wo_progress' => $wo->progress_percent,
            'total_lots' => $lots->count(),
            'total_kits' => 0,
            'kits_by_status' => [],
            'lots_by_status' => [],
        ];

        foreach ($lots as $lot) {
            $summary['total_kits'] += $lot->kits->count();

            // Agrupar kits por status
            foreach ($lot->kits as $kit) {
                $status = $kit->status;
                $summary['kits_by_status'][$status] = ($summary['kits_by_status'][$status] ?? 0) + 1;
            }

            // Agrupar lotes por status
            $lotStatus = $lot->status;
            $summary['lots_by_status'][$lotStatus] = ($summary['lots_by_status'][$lotStatus] ?? 0) + 1;
        }

        return $summary;
    }

    /**
     * Verificar si un WO puede iniciar producción
     */
    public function canStartProduction(WorkOrder $wo): bool
    {
        // Verificar que tenga lotes
        if ($wo->lots()->count() === 0) {
            return false;
        }

        // Verificar que los lotes tengan kits
        $hasKits = $wo->lots()
            ->whereHas('kits')
            ->exists();

        if (!$hasKits) {
            return false;
        }

        // Verificar que el status sea adecuado
        return in_array($wo->status->name, ['Abierto', 'Planificado']);
    }
}
```

**Registrar en Service Provider:**

```php
// app/Providers/AppServiceProvider.php
use App\Services\ProductionStatusService;

public function register(): void
{
    $this->app->singleton(ProductionStatusService::class);
}
```

#### Paso 7: Testing (2 horas)

**Archivo:** `tests/Unit/Services/ProductionStatusServiceTest.php`

```php
<?php

namespace Tests\Unit\Services;

use App\Models\WorkOrder;
use App\Models\Lot;
use App\Models\Kit;
use App\Models\StatusWO;
use App\Services\ProductionStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProductionStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProductionStatusService::class);

        // Seed statuses
        $this->artisan('db:seed', ['--class' => 'StatusWOSeeder']);
    }

    /** @test */
    public function it_updates_wo_status_to_en_produccion_when_kits_are_ready()
    {
        $wo = WorkOrder::factory()->create([
            'status_id' => StatusWO::where('name', 'Planificado')->first()->id
        ]);

        $lot = Lot::factory()->create(['work_order_id' => $wo->id]);
        Kit::factory()->create(['lot_id' => $lot->id, 'status' => 'preparado']);

        $this->service->updateWorkOrderStatus($wo);

        $this->assertEquals('En Producción', $wo->fresh()->status->name);
    }

    /** @test */
    public function it_updates_wo_status_to_cerrado_when_all_pieces_sent()
    {
        $wo = WorkOrder::factory()->create([
            'sent_pieces' => 1000,
            'status_id' => StatusWO::where('name', 'Parcialmente Enviado')->first()->id
        ]);

        $wo->purchaseOrder->update(['quantity' => 1000]);

        $this->service->updateWorkOrderStatus($wo);

        $this->assertEquals('Cerrado', $wo->fresh()->status->name);
    }

    /** @test */
    public function it_gets_accurate_status_summary()
    {
        $wo = WorkOrder::factory()->create();
        $lot = Lot::factory()->create(['work_order_id' => $wo->id]);

        Kit::factory()->create(['lot_id' => $lot->id, 'status' => 'preparado']);
        Kit::factory()->create(['lot_id' => $lot->id, 'status' => 'en_ensamble']);
        Kit::factory()->create(['lot_id' => $lot->id, 'status' => 'empacado']);

        $summary = $this->service->getWorkOrderStatusSummary($wo);

        $this->assertEquals(3, $summary['total_kits']);
        $this->assertEquals(1, $summary['kits_by_status']['preparado']);
        $this->assertEquals(1, $summary['kits_by_status']['en_ensamble']);
        $this->assertEquals(1, $summary['kits_by_status']['empacado']);
    }
}
```

---

## Consideraciones Técnicas

### 1. Transiciones de Estado Válidas

Es importante definir qué cambios de estado son válidos para evitar inconsistencias:

**Work Order:**
```
Abierto → Planificado → En Producción → En Inspección → Parcialmente Enviado → Cerrado
   ↓                                                            ↓
En Espera ←───────────────────────────────────────────────→ BackOrder
```

**Kit:**
```
pendiente → en_preparacion → preparado → en_ensamble → inspeccionado → empacado → enviado
                                                              ↓
                                                         rechazado → en_ensamble
```

### 2. Sincronización de Estados

Cuando cambia el estado de un Kit, puede ser necesario actualizar:
- Estado del Lot (padre)
- Estado del WorkOrder (abuelo)
- Status de recursos físicos (Table/Machine)

**Estrategia recomendada:**
- Usar **Observers** para cambios automáticos
- Implementar cola (Queue) para actualizaciones pesadas
- Cachear resúmenes de status para performance

**Ejemplo de Observer:**

```php
// app/Observers/KitObserver.php
class KitObserver
{
    public function updated(Kit $kit): void
    {
        // Si el status cambió, actualizar el lote
        if ($kit->wasChanged('status')) {
            $kit->lot->updateStatusFromKits();

            // Actualizar WO si todos los lotes cambiaron
            $workOrder = $kit->lot->workOrder;
            app(ProductionStatusService::class)->updateWorkOrderStatus($workOrder);
        }
    }
}
```

### 3. Performance en Queries

Con múltiples WOs y kits, los queries pueden volverse lentos. Optimizaciones:

#### Usar Eager Loading
```php
// MAL (N+1 queries)
$workOrders = WorkOrder::all();
foreach ($workOrders as $wo) {
    echo $wo->lots->count(); // Query por cada WO
    foreach ($wo->lots as $lot) {
        echo $lot->kits->count(); // Query por cada Lot
    }
}

// BIEN (3 queries)
$workOrders = WorkOrder::with(['lots.kits', 'status'])->get();
foreach ($workOrders as $wo) {
    echo $wo->lots->count();
    foreach ($wo->lots as $lot) {
        echo $lot->kits->count();
    }
}
```

#### Cachear Resúmenes
```php
use Illuminate\Support\Facades\Cache;

public function getWorkOrderStatusSummary(WorkOrder $wo): array
{
    $cacheKey = "wo_status_summary_{$wo->id}";

    return Cache::remember($cacheKey, 300, function () use ($wo) {
        // Cálculo pesado de resumen
        return $this->calculateSummary($wo);
    });
}

// Invalidar cache al actualizar
public function updated(Kit $kit): void
{
    Cache::forget("wo_status_summary_{$kit->lot->work_order_id}");
}
```

### 4. Validaciones de Negocio

Implementar validaciones para evitar estados inconsistentes:

```php
class ChangeKitStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'kit_id' => 'required|exists:kits,id',
            'new_status' => [
                'required',
                'in:pendiente,en_preparacion,preparado,en_ensamble,inspeccionado,empacado,enviado,rechazado',
                new ValidKitStatusTransition($this->kit_id),
            ],
            'comments' => 'nullable|string|max:500',
        ];
    }
}

// Custom Rule
class ValidKitStatusTransition implements Rule
{
    protected Kit $kit;

    public function __construct(int $kitId)
    {
        $this->kit = Kit::findOrFail($kitId);
    }

    public function passes($attribute, $value): bool
    {
        $validTransitions = [
            'pendiente' => ['en_preparacion'],
            'en_preparacion' => ['preparado'],
            'preparado' => ['en_ensamble'],
            'en_ensamble' => ['inspeccionado', 'rechazado'],
            'rechazado' => ['en_ensamble'],
            'inspeccionado' => ['empacado'],
            'empacado' => ['enviado'],
        ];

        $currentStatus = $this->kit->status;

        return in_array($value, $validTransitions[$currentStatus] ?? []);
    }

    public function message(): string
    {
        return "No se puede cambiar de '{$this->kit->status}' a ':input'.";
    }
}
```

### 5. Auditabilidad y Logs

Para trazabilidad completa, registrar cambios de status:

```php
// Migración de status_logs
Schema::create('wo_status_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('work_order_id')->constrained();
    $table->foreignId('old_status_id')->nullable()->constrained('statuses_wo');
    $table->foreignId('new_status_id')->constrained('statuses_wo');
    $table->foreignId('changed_by')->nullable()->constrained('users');
    $table->text('comments')->nullable();
    $table->timestamp('changed_at');
    $table->timestamps();
});

// En WorkOrder::changeStatus()
$this->statusLogs()->create([
    'old_status_id' => $this->getOriginal('status_id'),
    'new_status_id' => $newStatus->id,
    'changed_by' => auth()->id(),
    'changed_at' => now(),
    'comments' => $comments ?? null,
]);
```

---

## Ejemplos de Uso

### Ejemplo 1: Crear WO y Preparar Kits

```php
use App\Models\PurchaseOrder;
use App\Models\WorkOrder;
use App\Models\Lot;
use App\Models\Kit;
use App\Services\ProductionStatusService;

// 1. Crear Work Order desde Purchase Order
$po = PurchaseOrder::find(1);
$wo = WorkOrder::create([
    'wo_number' => WorkOrder::generateWONumber(),
    'purchase_order_id' => $po->id,
    'status_id' => StatusWO::where('name', 'Abierto')->first()->id,
    'opened_date' => now(),
]);

// 2. Crear Lot para el WO
$lot = Lot::create([
    'work_order_id' => $wo->id,
    'lot_number' => 'L-' . $wo->wo_number . '-001',
    'quantity' => 100,
    'status' => 'pending',
]);

// 3. Crear Kits para el Lot
for ($i = 1; $i <= 10; $i++) {
    Kit::create([
        'lot_id' => $lot->id,
        'kit_number' => "KIT-{$lot->lot_number}-{$i}",
        'status' => 'pendiente',
        'components' => [
            'tornillos' => 20,
            'cables' => 5,
            'placas' => 1,
        ],
    ]);
}

// 4. Cambiar status de WO a "Planificado"
$wo->changeStatus('Planificado');

echo "✅ WO {$wo->wo_number} creado con {$lot->kits->count()} kits";
```

### Ejemplo 2: Iniciar Producción

```php
// 1. Marcar kits como preparados
$lot = Lot::where('lot_number', 'L-WO-2025-00001-001')->first();

foreach ($lot->kits as $kit) {
    $kit->changeStatus('preparado');
}

// 2. Actualizar status del lote automáticamente
$lot->updateStatusFromKits();

// 3. Actualizar status del WO
$service = app(ProductionStatusService::class);
$service->updateWorkOrderStatus($lot->workOrder);

echo "✅ Producción iniciada para {$lot->workOrder->wo_number}";
echo "\n📊 Status: {$lot->workOrder->status->name}";
```

### Ejemplo 3: Procesar Ensamble e Inspección

```php
// 1. Tomar kit preparado
$kit = Kit::where('status', 'preparado')->first();

// 2. Iniciar ensamble
$kit->changeStatus('en_ensamble');

// ... proceso de ensamble ...

// 3. Completar ensamble
$kit->changeStatus('inspeccionado');

// 4. Si pasa inspección
if (inspectionPassed()) {
    $kit->changeStatus('empacado');
} else {
    $kit->changeStatus('rechazado');
    // Volver a ensamblar
    $kit->changeStatus('en_ensamble');
}

// 5. Actualizar WO automáticamente (vía Observer)
echo "✅ Kit procesado: {$kit->kit_number}";
echo "\n📊 Status WO: {$kit->lot->workOrder->status->name}";
```

### Ejemplo 4: Dashboard de Producción

```php
use App\Services\ProductionStatusService;

$service = app(ProductionStatusService::class);

// Obtener todos los WOs en producción
$activeWOs = WorkOrder::with(['lots.kits', 'status', 'purchaseOrder.part'])
    ->withStatus('En Producción')
    ->get();

foreach ($activeWOs as $wo) {
    $summary = $service->getWorkOrderStatusSummary($wo);

    echo "WO: {$wo->wo_number} | {$wo->purchaseOrder->part->number}\n";
    echo "Status: {$summary['wo_status']} ({$summary['wo_progress']}%)\n";
    echo "Lotes: {$summary['total_lots']} | Kits: {$summary['total_kits']}\n";
    echo "Kits por status:\n";

    foreach ($summary['kits_by_status'] as $status => $count) {
        echo "  - {$status}: {$count}\n";
    }

    echo "---\n";
}
```

**Output:**
```
WO: WO-2025-00001 | PART-12345
Status: En Producción (45%)
Lotes: 2 | Kits: 20
Kits por status:
  - preparado: 5
  - en_ensamble: 8
  - inspeccionado: 4
  - empacado: 3
---
WO: WO-2025-00002 | PART-67890
Status: En Producción (30%)
Lotes: 1 | Kits: 10
Kits por status:
  - preparado: 3
  - en_ensamble: 5
  - inspeccionado: 2
---
```

### Ejemplo 5: Reportes de Progreso

```php
// Obtener estadísticas generales de producción
$stats = [
    'total_wos' => WorkOrder::count(),
    'wos_en_produccion' => WorkOrder::withStatus('En Producción')->count(),
    'wos_cerrados_mes' => WorkOrder::withStatus('Cerrado')
        ->whereMonth('completed_at', now()->month)
        ->count(),
    'kits_pendientes' => Kit::byStatus('pendiente')->count(),
    'kits_en_proceso' => Kit::inProduction()->count(),
    'kits_completados_hoy' => Kit::byStatus('empacado')
        ->whereDate('packed_at', today())
        ->count(),
];

// WOs con mayor urgencia (menos días para due_date)
$urgentWOs = WorkOrder::with(['purchaseOrder', 'status'])
    ->whereHas('purchaseOrder', function ($q) {
        $q->where('due_date', '>=', now())
          ->orderBy('due_date', 'asc');
    })
    ->withStatus('En Producción')
    ->limit(10)
    ->get();

foreach ($urgentWOs as $wo) {
    $daysLeft = now()->diffInDays($wo->purchaseOrder->due_date);
    echo "{$wo->wo_number} - Due en {$daysLeft} días ({$wo->progress_percent}% completado)\n";
}
```

---

## Referencias

### Documentación del Proyecto

- **ProductionStatus_Error_Analisis.md:** `Diagramas_flujo/Estructura/docs/`
- **db.mkd:** `Diagramas_flujo/DB/db.mkd`
- **Flexcon_Tracker_ERP.md:** `Diagramas_flujo/Estructura/Flexcon_Tracker_ERP.md`
- **Spec 01:** Plan de Implementación Capacidad de Producción
- **Spec 07:** Análisis Técnico Over Time Module

### Modelos Relacionados

- `app/Models/WorkOrder.php` - Orden de trabajo
- `app/Models/StatusWO.php` - Estados de Work Order
- `app/Models/Lot.php` - Lote de producción
- `app/Models/Kit.php` - Kit de componentes (Fase 3)
- `app/Models/Table.php` - Mesa de trabajo
- `app/Models/Machine.php` - Máquina de producción
- `app/Models/ProductionStatus.php` - Estado de recursos físicos

### Tecnologías

- **Laravel:** 12.x
- **PHP:** 8.2+
- **Livewire:** 3.x
- **Base de Datos:** MySQL 8.0+ / PostgreSQL 13+

---

## Checklist de Implementación

### Fase Inmediata (StatusWO)

- [ ] Seeder `StatusWOSeeder` completado con 8 estados
- [ ] Estados de StatusWO insertados en base de datos
- [ ] Métodos helper agregados a `WorkOrder.php`
  - [ ] `getProgressPercentAttribute()`
  - [ ] `changeStatus()`
  - [ ] `canChangeTo()`
  - [ ] `getStatusColorAttribute()`
  - [ ] `scopeWithStatus()`
- [ ] Service `ProductionStatusService` creado
  - [ ] `updateWorkOrderStatus()`
  - [ ] `updateLotsStatus()`
  - [ ] `getWorkOrderStatusSummary()`
  - [ ] `canStartProduction()`
- [ ] Service registrado en `AppServiceProvider`
- [ ] Tests unitarios escritos y pasando
- [ ] Documentación actualizada

### Fase 3 (Kits)

- [ ] Migración `create_kits_table` creada
- [ ] Enum de status de Kit definido
- [ ] Modelo `Kit.php` creado con relaciones
- [ ] Métodos helper de Kit implementados
  - [ ] `changeStatus()`
  - [ ] `isReadyForAssembly()`
  - [ ] `isCompleted()`
  - [ ] Scopes: `byStatus()`, `pending()`, `inProduction()`
- [ ] Modelo `Lot.php` actualizado
  - [ ] Relación `kits()`
  - [ ] `getProgressPercentAttribute()`
  - [ ] `updateStatusFromKits()`
- [ ] Observer `KitObserver` creado (opcional)
- [ ] Custom Rule `ValidKitStatusTransition` creado
- [ ] Factory `KitFactory` creado
- [ ] Tests de integración escritos

### Auditoría (Opcional)

- [ ] Migración `create_wo_status_logs_table` creada
- [ ] Modelo `WOStatusLog` creado
- [ ] Registro de cambios implementado en `changeStatus()`
- [ ] Interface de visualización de logs (Livewire)

---

## Historial de Cambios

| Versión | Fecha | Autor | Cambios |
|---------|-------|-------|---------|
| 1.0 | 2025-12-25 | Agent Architect | Creación inicial del spec |

---

**Fin del Spec 08 - Estrategias de Manejo de Status de Producción con Work Orders y Kits**
