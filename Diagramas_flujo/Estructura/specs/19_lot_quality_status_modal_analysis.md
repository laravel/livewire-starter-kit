# Analisis Tecnico: Modal de Status de Calidad por Lote en Lista de Envio

**Fecha de Creacion:** 2026-02-02
**Autor:** Agent Architect
**Fase del Proyecto:** FASE 3 - Lista de Envio y Control de Calidad
**Estado:** Analisis Completado
**Version:** 2.0
**Relacionado con:**
- Spec 08: Estrategias de Manejo de Status de Produccion con Work Orders y Kits
- ShippingListDisplay Component
- Modelo Lot
- **Modelo Kit (CRUD de Materiales)**

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Contexto del Problema](#contexto-del-problema)
3. [Analisis del Codigo Existente](#analisis-del-codigo-existente)
4. [Descripcion Funcional](#descripcion-funcional)
5. [Flujo del Usuario](#flujo-del-usuario)
6. [Dependencia MAT -> CAL](#dependencia-mat---cal)
7. [Analisis del CRUD de Kits](#analisis-del-crud-de-kits)
8. [Componentes Involucrados](#componentes-involucrados)
9. [Estructura de Datos](#estructura-de-datos)
10. [Diseno del Modal de Calidad por Lote](#diseno-del-modal-de-calidad-por-lote)
11. [Consideraciones Tecnicas](#consideraciones-tecnicas)
12. [Plan de Implementacion](#plan-de-implementacion)
13. [Referencias](#referencias)

---

## Resumen Ejecutivo

### Problema a Resolver

El usuario necesita agregar funcionalidad en la columna "CAL." (Calidad) de la Lista de Envio para poder cambiar el status de calidad entre "Aprobado" y "No Aprobado" **a nivel de lote individual**, mediante un modal similar al existente para edicion de lotes.

**NUEVO REQUERIMIENTO v2.0:** La columna CAL. (Calidad) solo podra cambiar su status cuando la columna MAT. (Materiales) tenga status "Listo" (released). Es decir, Materiales debe liberar el kit PRIMERO antes de que Calidad pueda hacer su inspeccion.

### Situacion Actual

- **Modal de Lotes Existente:** Permite editar cantidad y numero de lote (funcional).
- **Semaforo de Calidad:** Existe un boton en la columna "Cal." que actualmente dispara `openDepartmentStatusModal()` a nivel de **Work Order**, no de **Lote**.
- **Estado de Lotes:** Los lotes tienen un campo `status` con valores: `pending`, `in_progress`, `completed`, `cancelled`.
- **CRUD de Kits:** Existe un sistema completo de gestion de Kits con flujo de aprobacion (Materiales -> Calidad).

### Solucion Propuesta

Crear un **nuevo modal de status de calidad por lote** que permita:
1. Ver todos los lotes de una Work Order
2. **Validar que el Kit asociado al Lote tenga status "released" (MAT. Listo)**
3. Cambiar el status de calidad (Aprobado/No Aprobado) de cada lote individualmente
4. Registrar quien aprobo/rechazo y cuando
5. Agregar comentarios de calidad por lote

---

## Contexto del Problema

### Flujo de Produccion segun Diagrama General

```
[...] --> [Preparar Kits] --> [Liberar Kit (MAT.)] --> [Inspeccion (CAL.)] --> [Ensamble] --> [Empaque]
                                     |                        |
                                     |                        | Rechazo
                                     |                        v
                                     |                  [Accion Correctiva]
                                     |
                                     v
                           Dependencia: MAT. debe estar "Listo"
                           ANTES de que CAL. pueda inspeccionar
```

La inspeccion de calidad ocurre **a nivel de lote**, no a nivel de Work Order completo. Cada lote puede tener un resultado de inspeccion diferente:

- **Lote A:** Aprobado -> Pasa a empaque
- **Lote B:** Rechazado -> Requiere accion correctiva
- **Lote C:** Pendiente -> Aun en inspeccion

### Requisitos del Negocio

1. **Granularidad:** El status de calidad debe ser por lote, no por WO
2. **Trazabilidad:** Registrar quien aprobo/rechazo y cuando
3. **Visibilidad:** Mostrar claramente el status de calidad de cada lote
4. **Workflow:** El status de calidad afecta si el lote puede pasar a empaque/envio
5. **NUEVO - Dependencia MAT -> CAL:** Calidad solo puede actuar despues de que Materiales libere el kit

---

## Analisis del Codigo Existente

### 1. Componente Livewire: ShippingListDisplay.php

**Ubicacion:** `app/Livewire/Admin/SentLists/ShippingListDisplay.php`

**Funcionalidad Actual:**

```php
// Modal de lotes existente
public $showLotModal = false;
public $selectedWorkOrderId = null;
public $selectedWorkOrder = null;
public $lots = []; // Array de lotes: [['id' => 1, 'number' => '001', 'quantity' => 100], ...]

// Modal de estado de departamentos (nivel WO, NO lote)
public $showDepartmentStatusModal = false;
public $selectedWoForStatus = null;
public $departmentStatuses = [
    'materials' => 'pending',
    'quality' => 'pending',
    'production' => 'pending',
];
```

**Observaciones:**
- El modal de lotes (`showLotModal`) funciona correctamente para editar lotes
- El modal de departamentos (`showDepartmentStatusModal`) opera a nivel de WO, no de lote
- Los estados de departamento estan **hardcodeados** en memoria, no persisten en BD

### 2. Vista Blade: shipping-list-display.blade.php

**Ubicacion:** `resources/views/livewire/admin/sent-lists/shipping-list-display.blade.php`

**Elementos Relevantes:**

```blade
{{-- Semaforo de Calidad (nivel WO) --}}
<td class="px-4 py-3 text-center">
    @php
        $qualityColor = match($departmentStatuses['quality']) {
            'rejected' => 'bg-red-500',
            'pending' => 'bg-yellow-400',
            'in_progress' => 'bg-blue-500',
            'approved' => 'bg-green-500',
            default => 'bg-gray-400',
        };
    @endphp
    <button wire:click="openDepartmentStatusModal({{ $wo->id }}, 'quality')"
            class="w-6 h-6 rounded {{ $qualityColor }} hover:opacity-80 transition-opacity"
            title="Calidad">
    </button>
</td>
```

**Filas de Lotes:**
```blade
{{-- Filas de Lotes --}}
@foreach($allLots as $lot)
    <tr class="bg-gray-50 dark:bg-gray-700/20">
        <td class="px-4 py-2 pl-8 text-xs text-gray-600 dark:text-gray-400">Lote</td>
        <td class="px-4 py-2 text-xs">
            <button wire:click="openLotModal({{ $wo->id }})"
                    class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                {{ $lot->lot_number }}
            </button>
        </td>
        {{-- ... --}}
        {{-- Estado del lote en lugar de semaforos --}}
        <td colspan="3" class="px-4 py-2 text-center">
            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded {{ $lotStatusInfo['bg'] }} {{ $lotStatusInfo['text'] }}">
                {{ $lotStatusInfo['label'] }}
            </span>
        </td>
    </tr>
@endforeach
```

### 3. Modelo Lot.php

**Ubicacion:** `app/Models/Lot.php`

**Campos Actuales:**
```php
protected $fillable = [
    'work_order_id',
    'lot_number',
    'description',
    'quantity',
    'status',           // pending, in_progress, completed, cancelled
    'comments',
    'raw_material_batch_numbers',
    'supplier_id',
    'supplier_name',
    'receipt_date',
    'expiration_date',
];
```

**Status Existentes:**
```php
public const STATUS_PENDING = 'pending';
public const STATUS_IN_PROGRESS = 'in_progress';
public const STATUS_COMPLETED = 'completed';
public const STATUS_CANCELLED = 'cancelled';
```

**Relacion con Kits:**
```php
/**
 * Get the kits that were created from this lot.
 */
public function kits(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
{
    return $this->belongsToMany(Kit::class, 'kit_lot')->withTimestamps();
}
```

**Observacion Critica:** El modelo **NO tiene campos para status de calidad** actualmente. El campo `status` representa el estado de produccion, no el resultado de inspeccion de calidad.

### 4. Migracion de Tabla lots

**Ubicacion:** `database/migrations/2025_12_28_202009_create_lots_table.php`

**Estructura Actual:**
```php
Schema::create('lots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
    $table->string('lot_number');
    $table->text('description')->nullable();
    $table->integer('quantity');
    $table->string('status')->default('pending');
    $table->text('comments')->nullable();
    $table->timestamps();
    $table->softDeletes();
    // ... indices
});
```

---

## Descripcion Funcional

### Nueva Funcionalidad: Modal de Status de Calidad por Lote

#### Objetivo

Permitir al usuario (departamento de Calidad) cambiar el status de inspeccion de cada lote individualmente desde la Lista de Envio.

#### Comportamiento Esperado

1. **Trigger:** Click en la columna "Cal." de la fila de un **lote** (no WO)
2. **Validacion de Dependencia:** Verificar que el Kit asociado al Lote tenga status `released`
3. **Modal:** Se abre mostrando informacion del lote y opciones de status
4. **Opciones de Status:**
   - **Pendiente:** Lote aun no inspeccionado (default)
   - **Aprobado:** Lote paso inspeccion de calidad
   - **No Aprobado:** Lote rechazo, requiere accion correctiva
5. **Campos Adicionales:**
   - Comentarios de calidad (motivo de rechazo, observaciones)
   - Fecha de inspeccion (auto-llenada al guardar)
   - Inspector (usuario actual)
6. **Persistencia:** Guardar en base de datos con auditoria

### Casos de Uso

| Actor | Accion | Resultado Esperado |
|-------|--------|-------------------|
| Inspector de Calidad | Click en "Cal." de un lote SIN kit liberado | Mensaje de error: "El kit debe ser liberado por Materiales primero" |
| Inspector de Calidad | Click en "Cal." de un lote CON kit liberado | Abre modal de status de calidad |
| Inspector de Calidad | Selecciona "Aprobado" | Lote marcado como aprobado, semaforo verde |
| Inspector de Calidad | Selecciona "No Aprobado" | Lote marcado como rechazado, semaforo rojo |
| Inspector de Calidad | Agrega comentario | Comentario guardado con timestamp |
| Supervisor | Ve lista de envio | Ve status de calidad por lote con colores |

---

## Flujo del Usuario

### Flujo Principal: Aprobar/Rechazar Lote (con validacion de dependencia MAT -> CAL)

```
[Lista de Envio]
      |
      | Usuario ve tabla con WOs y sus lotes
      v
[Identifica lote a inspeccionar]
      |
      | Click en indicador de calidad del lote (columna Cal.)
      v
+-------------------------------------+
|  Sistema verifica dependencia       |
|-------------------------------------|
|  ¿El Kit del lote tiene status      |
|   "released" (MAT. Listo)?          |
+-------------------------------------+
      |
      |-----> NO: Mostrar mensaje de error
      |           "El kit debe ser liberado por
      |            Materiales antes de la inspeccion"
      |           [Cerrar]
      |
      v SI
+-------------------------------------+
|  Modal: Status de Calidad - Lote    |
|-------------------------------------|
|  WO: WO-2025-00001                  |
|  Lote: 001                          |
|  Kit: KIT-WO-2025-00001-001         |
|  Parte: PART-12345                  |
|  Cantidad: 100                      |
|-------------------------------------|
|  Status de MAT.: [Listo] (verde)    |
|-------------------------------------|
|  Status de Calidad:                 |
|  ( ) Pendiente                      |
|  (o) Aprobado                       |
|  ( ) No Aprobado                    |
|-------------------------------------|
|  Comentarios:                       |
|  [________________________]         |
|-------------------------------------|
|  [Cancelar]        [Guardar]        |
+-------------------------------------+
      |
      | Click "Guardar"
      v
[Sistema actualiza status]
      |
      | Cambia color de semaforo
      v
[Lista actualizada]
```

### Flujo Alternativo: Rechazo con Comentario Obligatorio

```
[Usuario selecciona "No Aprobado"]
      |
      v
[Campo de comentarios se vuelve requerido]
      |
      | Usuario debe explicar motivo
      v
[Ingresa comentario: "Defecto visual en superficie"]
      |
      v
[Guardar habilitado]
      |
      v
[Sistema registra rechazo con motivo]
```

---

## Dependencia MAT -> CAL

### Descripcion de la Regla de Negocio

La columna **CAL. (Calidad)** solo podra cambiar su status cuando la columna **MAT. (Materiales)** tenga status **"Listo"** (released en el modelo Kit).

**Flujo de Dependencia:**
```
MAT. (Materiales) libera Kit (status = "released")
           |
           v
ENTONCES CAL. (Calidad) puede inspeccionar y cambiar status del Lote
```

### Implementacion Tecnica de la Dependencia

#### 1. Verificacion en el Modelo Lot

```php
// Agregar al modelo Lot.php

/**
 * Check if the lot can be inspected by Quality.
 * Quality can only inspect lots that have an associated Kit with status "released".
 */
public function canBeInspectedByQuality(): bool
{
    // Verificar si el lote tiene al menos un kit con status "released"
    return $this->kits()
        ->where('status', Kit::STATUS_RELEASED)
        ->exists();
}

/**
 * Get the released kit associated with this lot (if any).
 */
public function getReleasedKit(): ?Kit
{
    return $this->kits()
        ->where('status', Kit::STATUS_RELEASED)
        ->first();
}

/**
 * Get the reason why quality inspection is blocked.
 */
public function getQualityBlockedReason(): ?string
{
    if ($this->canBeInspectedByQuality()) {
        return null;
    }

    $kit = $this->kits()->first();

    if (!$kit) {
        return 'Este lote no tiene un kit asociado. Materiales debe crear un kit primero.';
    }

    return match ($kit->status) {
        Kit::STATUS_PREPARING => 'El kit esta en preparacion. Materiales debe completar y liberar el kit primero.',
        Kit::STATUS_READY => 'El kit esta listo pero aun no ha sido liberado por Materiales.',
        Kit::STATUS_REJECTED => 'El kit fue rechazado. Materiales debe corregir y re-liberar el kit.',
        Kit::STATUS_IN_ASSEMBLY => 'El kit ya esta en ensamble.',
        default => 'El kit no tiene un status valido para inspeccion.',
    };
}
```

#### 2. Validacion en el Componente Livewire

```php
// En ShippingListDisplay.php

/**
 * Open quality status modal for a specific lot.
 */
public function openQualityModal($lotId)
{
    $this->selectedLotId = $lotId;
    $this->selectedLot = Lot::with(['workOrder.purchaseOrder.part', 'kits'])->find($lotId);

    if (!$this->selectedLot) {
        session()->flash('error', 'Lote no encontrado.');
        return;
    }

    // VALIDACION DE DEPENDENCIA MAT -> CAL
    if (!$this->selectedLot->canBeInspectedByQuality()) {
        $reason = $this->selectedLot->getQualityBlockedReason();
        session()->flash('error', $reason);
        return;
    }

    // Cargar valores actuales
    $this->qualityStatus = $this->selectedLot->quality_status ?? 'pending';
    $this->qualityComments = $this->selectedLot->quality_comments ?? '';

    $this->showQualityModal = true;
}
```

#### 3. Indicador Visual en la Vista

```blade
{{-- Semaforo de calidad por lote CON validacion de dependencia --}}
@php
    $canInspect = $lot->canBeInspectedByQuality();
    $qualityColor = match($lot->quality_status ?? 'pending') {
        'rejected' => 'bg-red-500',
        'pending' => 'bg-yellow-400',
        'approved' => 'bg-green-500',
        default => 'bg-gray-400',
    };

    // Si no puede ser inspeccionado, mostrar color gris con candado
    if (!$canInspect) {
        $qualityColor = 'bg-gray-300';
    }
@endphp

<button
    wire:click="openQualityModal({{ $lot->id }})"
    class="w-5 h-5 rounded {{ $qualityColor }} {{ $canInspect ? 'hover:opacity-80' : 'cursor-not-allowed opacity-60' }} transition-opacity relative"
    title="{{ $canInspect ? 'Status de Calidad: ' . ($lot->quality_status_label ?? 'Pendiente') : 'Bloqueado: ' . $lot->getQualityBlockedReason() }}"
    @if(!$canInspect) disabled @endif
>
    @if(!$canInspect)
        {{-- Icono de candado para indicar que esta bloqueado --}}
        <svg class="w-3 h-3 absolute inset-0 m-auto text-gray-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
        </svg>
    @endif
</button>
```

### Matriz de Estados MAT -> CAL

| Status Kit (MAT.) | Puede CAL. Inspeccionar? | Color Semaforo CAL. | Accion Disponible |
|-------------------|--------------------------|---------------------|-------------------|
| `preparing` | NO | Gris + Candado | Ninguna |
| `ready` | NO | Gris + Candado | Ninguna |
| `released` | SI | Amarillo/Verde/Rojo | Cambiar status |
| `rejected` | NO | Gris + Candado | Ninguna |
| `in_assembly` | SI (ya paso) | Mantiene color actual | Solo lectura |
| Sin Kit | NO | Gris + Candado | Ninguna |

---

## Analisis del CRUD de Kits

### Resumen del Sistema de Kits

El sistema de Kits en Flexcon-Tracker esta completamente implementado y proporciona la gestion de materiales para produccion. Los kits representan el conjunto de materiales preparados para una Work Order, y su flujo de aprobacion es critico para el proceso de produccion.

### Modelo Kit

**Ubicacion:** `app/Models/Kit.php`

#### Campos del Modelo

```php
protected $fillable = [
    'work_order_id',
    'kit_number',
    'status',
    'validated',
    'validation_notes',
    'prepared_by',
    'released_by',
    'submitted_to_quality_at',
    'approved_at',
    'approved_by',
    'current_approval_cycle',
];

protected $casts = [
    'validated' => 'boolean',
    'submitted_to_quality_at' => 'datetime',
    'approved_at' => 'datetime',
    'current_approval_cycle' => 'integer',
];
```

#### Constantes de Status del Kit

```php
public const STATUS_PREPARING = 'preparing';    // En Preparacion
public const STATUS_READY = 'ready';            // Listo (pendiente de liberacion)
public const STATUS_RELEASED = 'released';      // Liberado (MAT. Listo)
public const STATUS_IN_ASSEMBLY = 'in_assembly'; // En Ensamble
public const STATUS_REJECTED = 'rejected';      // Rechazado
```

#### Relaciones del Modelo Kit

```php
// Work Order que posee el kit
public function workOrder(): BelongsTo
{
    return $this->belongsTo(WorkOrder::class);
}

// Usuario que preparo el kit
public function preparedBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'prepared_by');
}

// Usuario que libero el kit
public function releasedBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'released_by');
}

// Usuario que aprobo el kit
public function approver(): BelongsTo
{
    return $this->belongsTo(User::class, 'approved_by');
}

// Lotes usados para crear este kit (muchos a muchos)
public function lots(): BelongsToMany
{
    return $this->belongsToMany(Lot::class, 'kit_lot')->withPivot('created_at');
}

// Ciclos de aprobacion del kit
public function approvalCycles(): HasMany
{
    return $this->hasMany(KitApprovalCycle::class);
}

// Audit trail del kit
public function auditTrail(): MorphMany
{
    return $this->morphMany(AuditTrail::class, 'auditable');
}

// Incidentes del kit
public function incidents(): HasMany
{
    return $this->hasMany(KitIncident::class);
}
```

#### Metodos de Validacion del Kit

```php
// Puede marcarse como listo
public function canBeReady(): bool
{
    return $this->status === self::STATUS_PREPARING;
}

// Puede ser liberado (debe estar listo Y validado)
public function canBeReleased(): bool
{
    return $this->status === self::STATUS_READY && $this->validated;
}

// Puede iniciar ensamble
public function canStartAssembly(): bool
{
    return $this->status === self::STATUS_RELEASED;
}

// Puede ser editado
public function canBeEdited(): bool
{
    return in_array($this->status, [self::STATUS_PREPARING, self::STATUS_REJECTED]);
}

// Puede ser eliminado
public function canBeDeleted(): bool
{
    return $this->status === self::STATUS_PREPARING && !$this->submitted_to_quality_at;
}
```

#### Metodos de Flujo de Aprobacion

```php
// Enviar kit a Calidad para aprobacion
public function submitToQuality(User $user): void
{
    $this->update([
        'status' => self::STATUS_READY,
        'submitted_to_quality_at' => now(),
    ]);

    // Crear ciclo de aprobacion
    $this->approvalCycles()->create([
        'cycle_number' => $this->current_approval_cycle,
        'submitted_by' => $user->id,
        'submitted_at' => now(),
        'status' => KitApprovalCycle::STATUS_PENDING,
    ]);
}

// Aprobar el kit
public function approve(User $user, ?string $comments = null): void
{
    $this->update([
        'status' => self::STATUS_RELEASED,
        'approved_at' => now(),
        'approved_by' => $user->id,
        'validated' => true,
    ]);

    // Actualizar ciclo de aprobacion actual
    $cycle = $this->getCurrentApprovalCycle();
    if ($cycle) {
        $cycle->update([
            'status' => KitApprovalCycle::STATUS_APPROVED,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'comments' => $comments,
        ]);
    }
}

// Rechazar el kit
public function reject(User $user, string $reason, ?string $comments = null): void
{
    $this->update([
        'status' => self::STATUS_REJECTED,
    ]);

    // Actualizar ciclo de aprobacion actual
    $cycle = $this->getCurrentApprovalCycle();
    if ($cycle) {
        $cycle->update([
            'status' => KitApprovalCycle::STATUS_REJECTED,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
            'comments' => $comments,
        ]);
    }
}
```

### Relacion Kit - Lot - WorkOrder

```
WorkOrder (1) -----> (N) Lot
    |                    |
    |                    | (N:M via kit_lot)
    |                    v
    +-------------> (N) Kit
```

**Diagrama de Relaciones:**

```
+---------------+       +---------------+       +---------------+
|   WorkOrder   |       |     Lot       |       |      Kit      |
+---------------+       +---------------+       +---------------+
| id            |<--+   | id            |       | id            |
| wo_number     |   |   | work_order_id |---+   | work_order_id |---+
| status_id     |   |   | lot_number    |   |   | kit_number    |   |
| ...           |   |   | quantity      |   |   | status        |   |
+---------------+   |   | status        |   |   | validated     |   |
                    |   | ...           |   |   | ...           |   |
                    |   +---------------+   |   +---------------+   |
                    |          |            |          |            |
                    +----------+            +----------+            |
                    |                                               |
                    +-----------------------------------------------+

                    +---------------+
                    |    kit_lot    |  (Tabla Pivote)
                    +---------------+
                    | kit_id        |----> Kit.id
                    | lot_id        |----> Lot.id
                    | created_at    |
                    +---------------+
```

### Tabla Pivote kit_lot

**Ubicacion:** `database/migrations/2026_02_02_032603_create_kit_lot_table.php`

```php
Schema::create('kit_lot', function (Blueprint $table) {
    $table->id();
    $table->foreignId('kit_id')->constrained()->onDelete('cascade');
    $table->foreignId('lot_id')->constrained()->onDelete('cascade');
    $table->timestamp('created_at');

    // Indexes
    $table->index('kit_id');
    $table->index('lot_id');
    $table->unique(['kit_id', 'lot_id']);
});
```

### Componentes Livewire para Kits

#### 1. KitList (Lista de Kits)

**Ubicacion:** `app/Livewire/Admin/Kits/KitList.php`

**Funcionalidades:**
- Listado paginado de kits con busqueda
- Filtro por status
- Ordenamiento por campos
- Acciones: Marcar como listo, Liberar, Eliminar

```php
public function markAsReady(int $id): void
{
    $kit = Kit::find($id);
    if ($kit && $kit->canBeReady()) {
        $kit->update([
            'status' => Kit::STATUS_READY,
            'prepared_by' => auth()->id(),
        ]);
    }
}

public function release(int $id): void
{
    $kit = Kit::find($id);
    if ($kit && $kit->canBeReleased()) {
        $kit->update([
            'status' => Kit::STATUS_RELEASED,
            'released_by' => auth()->id(),
        ]);
    }
}
```

#### 2. KitCreate (Crear Kit)

**Ubicacion:** `app/Livewire/Admin/Kits/KitCreate.php`

**Funcionalidades:**
- Seleccionar Work Order
- Generar numero de kit automaticamente
- Agregar notas de validacion

```php
public function save(): void
{
    $this->validate();

    $kit = Kit::create([
        'work_order_id' => $this->work_order_id,
        'kit_number' => Kit::generateKitNumber($this->work_order_id),
        'status' => Kit::STATUS_PREPARING,
        'validated' => false,
        'validation_notes' => $this->validation_notes,
    ]);
}
```

#### 3. KitShow (Ver Kit)

**Ubicacion:** `app/Livewire/Admin/Kits/KitShow.php`

**Funcionalidades:**
- Ver detalles del kit
- Validar/Invalidar kit
- Cambiar status (Listo, Liberar, Iniciar Ensamble)
- Registrar incidentes
- Resolver incidentes

#### 4. KitManagement (Gestion desde Materiales)

**Ubicacion:** `app/Livewire/Admin/Materials/KitManagement.php`

**Funcionalidades Completas:**
- Listado de kits con filtros avanzados
- Seleccion de lotes disponibles para crear kit
- Crear/Editar kits con lotes seleccionados
- Enviar kit a Calidad (`submitToQuality`)
- Ver historial de aprobaciones
- Eliminar kits

**Metodo clave para el flujo MAT -> CAL:**

```php
public function submitToQuality(int $kitId): void
{
    $kit = Kit::findOrFail($kitId);

    if ($kit->status !== Kit::STATUS_PREPARING) {
        session()->flash('error', 'Solo se pueden enviar kits en estado "En Preparacion".');
        return;
    }

    DB::transaction(function () use ($kit) {
        $kit->submitToQuality(Auth::user());

        // Record audit trail
        $this->auditTrailService->recordStatusChange(
            $kit,
            Auth::user(),
            Kit::STATUS_PREPARING,
            Kit::STATUS_READY
        );
    });

    session()->flash('message', 'Kit enviado a Calidad para aprobacion.');
}
```

### Flujo de Status de Kits (MAT.)

```
+-------------+     +--------+     +----------+     +-------------+
| preparing   | --> | ready  | --> | released | --> | in_assembly |
| (En Prep.)  |     | (Listo)|     | (Liberado|     | (Ensamble)  |
+-------------+     +--------+     +----------+     +-------------+
                         |              ^
                         v              |
                    +----------+        |
                    | rejected |--------+
                    | (Rechaz.)|  (Re-submit)
                    +----------+
```

**Descripcion del Flujo:**

1. **preparing (En Preparacion):** Kit recien creado, Materiales esta preparando
2. **ready (Listo):** Kit preparado, enviado a Calidad para revision
3. **released (Liberado):** **<-- ESTE ES EL STATUS QUE HABILITA CAL.**
4. **in_assembly (En Ensamble):** Kit en proceso de ensamble
5. **rejected (Rechazado):** Kit rechazado, requiere correccion

### Vistas Relacionadas

| Vista | Ubicacion | Descripcion |
|-------|-----------|-------------|
| kit-list.blade.php | `resources/views/livewire/admin/kits/kit-list.blade.php` | Lista de kits con estadisticas |
| kit-create.blade.php | `resources/views/livewire/admin/kits/kit-create.blade.php` | Formulario de creacion |
| kit-show.blade.php | `resources/views/livewire/admin/kits/kit-show.blade.php` | Detalle del kit |
| kit-management.blade.php | `resources/views/livewire/admin/materials/kit-management.blade.php` | Gestion completa de kits |

---

## Componentes Involucrados

### 1. Componente Livewire: ShippingListDisplay.php

**Cambios Requeridos:**

```php
// Nuevas propiedades para modal de calidad por lote
public $showQualityModal = false;
public $selectedLotId = null;
public $selectedLot = null;
public $qualityStatus = 'pending'; // pending, approved, rejected
public $qualityComments = '';

// Nuevos metodos
public function openQualityModal($lotId)
public function closeQualityModal()
public function saveQualityStatus()
```

### 2. Vista Blade: shipping-list-display.blade.php

**Cambios Requeridos:**
- Agregar indicador de calidad clickeable en fila de lote (con validacion MAT -> CAL)
- Agregar nuevo modal de status de calidad
- Mostrar color de semaforo basado en `quality_status` del lote
- Mostrar indicador visual cuando calidad esta bloqueada (candado)

### 3. Modelo Lot.php

**Cambios Requeridos:**
- Agregar constantes de quality status
- Agregar metodos helper para calidad
- Agregar scopes de filtrado por calidad
- **Agregar metodo `canBeInspectedByQuality()`**
- **Agregar metodo `getQualityBlockedReason()`**

### 4. Nueva Migracion

**Archivo:** `database/migrations/YYYY_MM_DD_add_quality_fields_to_lots_table.php`

**Campos a agregar:**
- `quality_status`
- `quality_comments`
- `quality_inspected_at`
- `quality_inspected_by`

---

## Estructura de Datos

### Nueva Migracion: add_quality_fields_to_lots_table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            // Status de calidad
            $table->string('quality_status')->default('pending')
                  ->after('status')
                  ->comment('pending, approved, rejected');

            // Comentarios de calidad (motivo de rechazo, observaciones)
            $table->text('quality_comments')->nullable()
                  ->after('quality_status');

            // Fecha de inspeccion
            $table->timestamp('quality_inspected_at')->nullable()
                  ->after('quality_comments');

            // Usuario que inspecciono
            $table->foreignId('quality_inspected_by')->nullable()
                  ->after('quality_inspected_at')
                  ->constrained('users')
                  ->nullOnDelete();

            // Indice para consultas frecuentes
            $table->index('quality_status');
            $table->index(['work_order_id', 'quality_status']);
        });
    }

    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropForeign(['quality_inspected_by']);
            $table->dropIndex(['quality_status']);
            $table->dropIndex(['work_order_id', 'quality_status']);
            $table->dropColumn([
                'quality_status',
                'quality_comments',
                'quality_inspected_at',
                'quality_inspected_by',
            ]);
        });
    }
};
```

### Actualizacion del Modelo Lot.php

```php
// Agregar a $fillable
protected $fillable = [
    // ... campos existentes ...
    'quality_status',
    'quality_comments',
    'quality_inspected_at',
    'quality_inspected_by',
];

// Agregar a $casts
protected $casts = [
    // ... casts existentes ...
    'quality_inspected_at' => 'datetime',
];

/**
 * Quality Status constants
 */
public const QUALITY_PENDING = 'pending';
public const QUALITY_APPROVED = 'approved';
public const QUALITY_REJECTED = 'rejected';

/**
 * Get all available quality statuses.
 */
public static function getQualityStatuses(): array
{
    return [
        self::QUALITY_PENDING => 'Pendiente',
        self::QUALITY_APPROVED => 'Aprobado',
        self::QUALITY_REJECTED => 'No Aprobado',
    ];
}

/**
 * Get the quality status label.
 */
public function getQualityStatusLabelAttribute(): string
{
    return self::getQualityStatuses()[$this->quality_status] ?? $this->quality_status;
}

/**
 * Get the quality status color for UI display.
 */
public function getQualityStatusColorAttribute(): string
{
    return match ($this->quality_status) {
        self::QUALITY_PENDING => 'yellow',
        self::QUALITY_APPROVED => 'green',
        self::QUALITY_REJECTED => 'red',
        default => 'gray',
    };
}

/**
 * Check if the lot can be inspected by Quality.
 * Quality can only inspect lots that have an associated Kit with status "released".
 */
public function canBeInspectedByQuality(): bool
{
    return $this->kits()
        ->where('status', Kit::STATUS_RELEASED)
        ->exists();
}

/**
 * Get the released kit associated with this lot (if any).
 */
public function getReleasedKit(): ?Kit
{
    return $this->kits()
        ->where('status', Kit::STATUS_RELEASED)
        ->first();
}

/**
 * Get the reason why quality inspection is blocked.
 */
public function getQualityBlockedReason(): ?string
{
    if ($this->canBeInspectedByQuality()) {
        return null;
    }

    $kit = $this->kits()->first();

    if (!$kit) {
        return 'Este lote no tiene un kit asociado. Materiales debe crear un kit primero.';
    }

    return match ($kit->status) {
        Kit::STATUS_PREPARING => 'El kit esta en preparacion. Materiales debe completar y liberar el kit primero.',
        Kit::STATUS_READY => 'El kit esta listo pero aun no ha sido liberado por Materiales.',
        Kit::STATUS_REJECTED => 'El kit fue rechazado. Materiales debe corregir y re-liberar el kit.',
        Kit::STATUS_IN_ASSEMBLY => 'El kit ya esta en ensamble.',
        default => 'El kit no tiene un status valido para inspeccion.',
    };
}

/**
 * Scope a query to only include lots with pending quality.
 */
public function scopeQualityPending($query)
{
    return $query->where('quality_status', self::QUALITY_PENDING);
}

/**
 * Scope a query to only include lots with approved quality.
 */
public function scopeQualityApproved($query)
{
    return $query->where('quality_status', self::QUALITY_APPROVED);
}

/**
 * Scope a query to only include lots with rejected quality.
 */
public function scopeQualityRejected($query)
{
    return $query->where('quality_status', self::QUALITY_REJECTED);
}

/**
 * Relationship with the user who inspected quality.
 */
public function qualityInspector(): BelongsTo
{
    return $this->belongsTo(User::class, 'quality_inspected_by');
}

/**
 * Check if quality inspection is pending.
 */
public function isQualityPending(): bool
{
    return $this->quality_status === self::QUALITY_PENDING;
}

/**
 * Check if quality is approved.
 */
public function isQualityApproved(): bool
{
    return $this->quality_status === self::QUALITY_APPROVED;
}

/**
 * Check if quality is rejected.
 */
public function isQualityRejected(): bool
{
    return $this->quality_status === self::QUALITY_REJECTED;
}

/**
 * Check if lot can proceed to packing/shipping (must be quality approved).
 */
public function canProceedToShipping(): bool
{
    return $this->isQualityApproved() && $this->status === self::STATUS_COMPLETED;
}
```

---

## Diseno del Modal de Calidad por Lote

### Wireframe del Modal (Actualizado con validacion MAT -> CAL)

```
+----------------------------------------------------------+
|  Status de Calidad - Lote                            [X] |
+----------------------------------------------------------+
|                                                          |
|  Informacion del Lote                                    |
|  +----------------------------------------------------+  |
|  | WO:         WO-2025-00001                          |  |
|  | Lote:       001                                    |  |
|  | Kit:        KIT-WO-2025-00001-001                  |  |
|  | Parte:      PART-12345 - Descripcion de la parte   |  |
|  | Cantidad:   100 piezas                             |  |
|  +----------------------------------------------------+  |
|                                                          |
|  Status de Materiales                                    |
|  +----------------------------------------------------+  |
|  |  [*] Liberado (MAT. Listo)          [Verde]        |  |
|  +----------------------------------------------------+  |
|                                                          |
|  Status de Calidad                                       |
|  +----------------------------------------------------+  |
|  |  +---------------+  +---------------+  +---------+ |  |
|  |  |  [*] Pendiente |  |  [ ] Aprobado |  | [ ] No  | |  |
|  |  |      (Amarillo)|  |     (Verde)   |  | Aprobado| |  |
|  +---------------+  +---------------+  |  (Rojo) | |  |
|  |                                        +---------+ |  |
|  +----------------------------------------------------+  |
|                                                          |
|  Comentarios de Calidad                                  |
|  +----------------------------------------------------+  |
|  | [                                                 ]|  |
|  | [                                                 ]|  |
|  | [_________________________________________________]|  |
|  +----------------------------------------------------+  |
|  * Requerido si el status es "No Aprobado"              |
|                                                          |
|  +----------------------------------------------------+  |
|  |  [Cancelar]                         [Guardar]      |  |
|  +----------------------------------------------------+  |
+----------------------------------------------------------+
```

### Codigo del Modal (Blade) - Actualizado

```blade
{{-- Modal de Status de Calidad por Lote --}}
@if($showQualityModal && $selectedLot)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeQualityModal"></div>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status de Calidad - Lote</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                WO: {{ $selectedLot->workOrder->purchaseOrder->wo }} |
                                Lote: {{ $selectedLot->lot_number }}
                            </p>
                        </div>
                        <button wire:click="closeQualityModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 space-y-6">
                    {{-- Informacion del Lote --}}
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Informacion del Lote</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Parte:</span>
                                <span class="ml-2 text-gray-900 dark:text-white font-medium">
                                    {{ $selectedLot->workOrder->purchaseOrder->part->number }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Cantidad:</span>
                                <span class="ml-2 text-gray-900 dark:text-white font-medium">
                                    {{ number_format($selectedLot->quantity) }} piezas
                                </span>
                            </div>
                            @php
                                $releasedKit = $selectedLot->getReleasedKit();
                            @endphp
                            @if($releasedKit)
                            <div class="col-span-2">
                                <span class="text-gray-500 dark:text-gray-400">Kit:</span>
                                <span class="ml-2 text-green-600 dark:text-green-400 font-medium">
                                    {{ $releasedKit->kit_number }} (Liberado)
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Status de Materiales (Solo lectura) --}}
                    <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg border border-green-200 dark:border-green-800">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full bg-green-500 mr-3"></div>
                            <span class="text-sm font-medium text-green-800 dark:text-green-200">
                                MAT. Liberado - Habilitado para inspeccion de calidad
                            </span>
                        </div>
                    </div>

                    {{-- Status de Calidad --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Status de Calidad
                        </label>
                        <div class="grid grid-cols-3 gap-3">
                            {{-- Pendiente --}}
                            <button
                                wire:click="$set('qualityStatus', 'pending')"
                                class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $qualityStatus === 'pending' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300' }}"
                            >
                                <div class="w-8 h-8 rounded-full bg-yellow-400 mb-2"></div>
                                <span class="text-sm font-medium {{ $qualityStatus === 'pending' ? 'text-yellow-700 dark:text-yellow-300' : 'text-gray-700 dark:text-gray-300' }}">
                                    Pendiente
                                </span>
                            </button>

                            {{-- Aprobado --}}
                            <button
                                wire:click="$set('qualityStatus', 'approved')"
                                class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $qualityStatus === 'approved' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300' }}"
                            >
                                <div class="w-8 h-8 rounded-full bg-green-500 mb-2"></div>
                                <span class="text-sm font-medium {{ $qualityStatus === 'approved' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300' }}">
                                    Aprobado
                                </span>
                            </button>

                            {{-- No Aprobado --}}
                            <button
                                wire:click="$set('qualityStatus', 'rejected')"
                                class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $qualityStatus === 'rejected' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300' }}"
                            >
                                <div class="w-8 h-8 rounded-full bg-red-500 mb-2"></div>
                                <span class="text-sm font-medium {{ $qualityStatus === 'rejected' ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300' }}">
                                    No Aprobado
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- Comentarios --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Comentarios de Calidad
                            @if($qualityStatus === 'rejected')
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        <textarea
                            wire:model="qualityComments"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="{{ $qualityStatus === 'rejected' ? 'Describa el motivo del rechazo...' : 'Observaciones adicionales (opcional)...' }}"
                        ></textarea>
                        @if($qualityStatus === 'rejected')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                                * El motivo del rechazo es requerido
                            </p>
                        @endif
                        @error('qualityComments')
                            <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <button
                        wire:click="closeQualityModal"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="saveQualityStatus"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                    >
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
```

### Codigo del Componente Livewire (Actualizado con validacion MAT -> CAL)

```php
// Agregar propiedades al componente ShippingListDisplay.php

// Modal de calidad por lote
public $showQualityModal = false;
public $selectedLotId = null;
public $selectedLot = null;
public $qualityStatus = 'pending';
public $qualityComments = '';

/**
 * Open quality status modal for a specific lot.
 */
public function openQualityModal($lotId)
{
    $this->selectedLotId = $lotId;
    $this->selectedLot = Lot::with(['workOrder.purchaseOrder.part', 'kits'])->find($lotId);

    if (!$this->selectedLot) {
        session()->flash('error', 'Lote no encontrado.');
        return;
    }

    // VALIDACION DE DEPENDENCIA MAT -> CAL
    if (!$this->selectedLot->canBeInspectedByQuality()) {
        $reason = $this->selectedLot->getQualityBlockedReason();
        session()->flash('error', $reason);
        return;
    }

    // Cargar valores actuales
    $this->qualityStatus = $this->selectedLot->quality_status ?? 'pending';
    $this->qualityComments = $this->selectedLot->quality_comments ?? '';

    $this->showQualityModal = true;
}

/**
 * Close quality status modal.
 */
public function closeQualityModal()
{
    $this->showQualityModal = false;
    $this->selectedLotId = null;
    $this->selectedLot = null;
    $this->qualityStatus = 'pending';
    $this->qualityComments = '';
    $this->resetErrorBag();
}

/**
 * Save quality status for the selected lot.
 */
public function saveQualityStatus()
{
    // Validar
    $rules = [
        'qualityStatus' => 'required|in:pending,approved,rejected',
    ];

    // Comentario requerido si es rechazado
    if ($this->qualityStatus === 'rejected') {
        $rules['qualityComments'] = 'required|string|min:5|max:1000';
    } else {
        $rules['qualityComments'] = 'nullable|string|max:1000';
    }

    $this->validate($rules, [
        'qualityStatus.required' => 'Debe seleccionar un status de calidad.',
        'qualityComments.required' => 'Debe indicar el motivo del rechazo.',
        'qualityComments.min' => 'El comentario debe tener al menos 5 caracteres.',
    ]);

    if (!$this->selectedLot) {
        session()->flash('error', 'Lote no encontrado.');
        return;
    }

    // Doble verificacion de dependencia MAT -> CAL
    if (!$this->selectedLot->canBeInspectedByQuality()) {
        session()->flash('error', 'Este lote ya no puede ser inspeccionado. El kit asociado no esta liberado.');
        $this->closeQualityModal();
        return;
    }

    // Actualizar lote
    $this->selectedLot->update([
        'quality_status' => $this->qualityStatus,
        'quality_comments' => $this->qualityComments,
        'quality_inspected_at' => now(),
        'quality_inspected_by' => auth()->id(),
    ]);

    $statusLabel = Lot::getQualityStatuses()[$this->qualityStatus];
    session()->flash('message', "Status de calidad actualizado a: {$statusLabel}");

    $this->closeQualityModal();
    $this->dispatch('refresh-display');
}
```

---

## Consideraciones Tecnicas

### 1. Permisos y Autorizacion

Se recomienda crear un permiso especifico para esta funcionalidad:

```php
// Permiso recomendado (Spatie Permissions)
'lot.quality.update' => 'Actualizar status de calidad de lotes'

// Validacion en el componente
public function saveQualityStatus()
{
    $this->authorize('lot.quality.update');
    // ... resto del codigo
}
```

### 2. Auditoria y Trazabilidad

Los cambios de status de calidad son criticos para ISO. Se recomienda:

```php
// En el modelo Lot, agregar evento de auditoria
protected static function boot()
{
    parent::boot();

    static::updated(function ($lot) {
        if ($lot->isDirty('quality_status')) {
            // Registrar en audit trail
            $lot->auditTrail()->create([
                'action' => 'quality_status_changed',
                'old_value' => $lot->getOriginal('quality_status'),
                'new_value' => $lot->quality_status,
                'user_id' => auth()->id(),
                'comments' => $lot->quality_comments,
            ]);
        }
    });
}
```

### 3. Notificaciones

Cuando un lote es rechazado, puede ser util notificar:

```php
// En saveQualityStatus(), despues de guardar
if ($this->qualityStatus === 'rejected') {
    // Notificar a produccion para accion correctiva
    event(new LotQualityRejected($this->selectedLot));
}
```

### 4. Impacto en Workflow

El status de calidad afecta si un lote puede pasar a envio:

```php
// En el proceso de crear Shipping List
$lotsToShip = $workOrder->lots()
    ->qualityApproved()  // Solo lotes aprobados
    ->completed()        // Solo lotes completados
    ->get();
```

### 5. Validacion de Dependencia MAT -> CAL

**CRITICO:** La validacion de dependencia debe realizarse en multiples puntos:

1. **En la vista:** Deshabilitar boton si no puede ser inspeccionado
2. **En el componente (openQualityModal):** Verificar antes de abrir modal
3. **En el componente (saveQualityStatus):** Verificar antes de guardar (doble verificacion)

```php
// Doble verificacion para evitar race conditions
if (!$this->selectedLot->canBeInspectedByQuality()) {
    session()->flash('error', 'El estado del kit ha cambiado. Por favor recargue la pagina.');
    $this->closeQualityModal();
    return;
}
```

### 6. Visualizacion en la Tabla

Modificar la fila de lotes para mostrar indicador de calidad clickeable con validacion:

```blade
{{-- En la fila de lotes, agregar semaforo de calidad con validacion MAT -> CAL --}}
<td class="px-4 py-2 text-center">
    @php
        $canInspect = $lot->canBeInspectedByQuality();
        $qualityColor = match($lot->quality_status ?? 'pending') {
            'rejected' => 'bg-red-500',
            'pending' => 'bg-yellow-400',
            'approved' => 'bg-green-500',
            default => 'bg-gray-400',
        };

        if (!$canInspect) {
            $qualityColor = 'bg-gray-300';
        }
    @endphp
    <button
        wire:click="openQualityModal({{ $lot->id }})"
        class="w-5 h-5 rounded {{ $qualityColor }} {{ $canInspect ? 'hover:opacity-80' : 'cursor-not-allowed opacity-60' }} transition-opacity relative"
        title="{{ $canInspect ? 'Status de Calidad: ' . ($lot->quality_status_label ?? 'Pendiente') : $lot->getQualityBlockedReason() }}"
    >
        @if(!$canInspect)
            <svg class="w-3 h-3 absolute inset-0 m-auto text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
        @endif
    </button>
</td>
<td class="px-4 py-2 text-center">
    {{-- Status de produccion --}}
    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded {{ $lotStatusInfo['bg'] }} {{ $lotStatusInfo['text'] }}">
        {{ $lotStatusInfo['label'] }}
    </span>
</td>
<td class="px-4 py-2 text-center text-xs text-gray-500">
    {{-- Espacio para produccion si se necesita --}}
    -
</td>
```

---

## Plan de Implementacion

### Fase 1: Base de Datos (30 min)

1. **Crear migracion** para agregar campos de calidad a tabla `lots`
2. **Ejecutar migracion**: `php artisan migrate`
3. **Verificar** que los campos fueron agregados correctamente

```bash
php artisan make:migration add_quality_fields_to_lots_table --table=lots
php artisan migrate
```

### Fase 2: Modelo (1.5 horas)

1. **Actualizar modelo Lot.php** con:
   - Nuevos campos en `$fillable`
   - Nuevos `$casts`
   - Constantes de quality status
   - Metodos helper
   - Scopes
   - Relacion con User (inspector)
   - **Metodo `canBeInspectedByQuality()`**
   - **Metodo `getQualityBlockedReason()`**
   - **Metodo `getReleasedKit()`**

2. **Escribir tests unitarios** para los nuevos metodos

### Fase 3: Componente Livewire (2.5 horas)

1. **Agregar propiedades** al componente ShippingListDisplay
2. **Implementar metodos**:
   - `openQualityModal()` **con validacion MAT -> CAL**
   - `closeQualityModal()`
   - `saveQualityStatus()` **con doble validacion**
3. **Agregar validaciones**

### Fase 4: Vista Blade (2.5 horas)

1. **Agregar modal** de status de calidad (actualizado con info de kit)
2. **Modificar fila de lotes** para incluir indicador clickeable **con validacion visual**
3. **Agregar indicador de candado** cuando calidad esta bloqueada
4. **Ajustar estilos** y responsive
5. **Agregar soporte dark mode**

### Fase 5: Testing (1.5 horas)

1. **Tests de Feature** para el componente
2. **Tests de integracion** para validar dependencia MAT -> CAL
3. **Tests manuales** en navegador
4. **Verificar** responsive en movil

### Fase 6: Permisos (30 min)

1. **Crear permiso** `lot.quality.update`
2. **Asignar permiso** a roles apropiados (Quality Inspector, Admin)
3. **Agregar validacion** en componente

### Checklist de Implementacion

- [ ] Migracion creada y ejecutada
- [ ] Modelo Lot actualizado con campos y metodos
- [ ] **Metodo canBeInspectedByQuality() implementado**
- [ ] **Metodo getQualityBlockedReason() implementado**
- [ ] Componente Livewire con logica del modal
- [ ] **Validacion MAT -> CAL en openQualityModal()**
- [ ] **Doble validacion en saveQualityStatus()**
- [ ] Vista Blade con modal y botones
- [ ] **Indicador visual de bloqueo (candado)**
- [ ] Tests escritos y pasando
- [ ] Permisos configurados
- [ ] Documentacion actualizada

---

## Resumen de Archivos a Modificar/Crear

| Archivo | Accion | Descripcion |
|---------|--------|-------------|
| `database/migrations/XXXX_add_quality_fields_to_lots_table.php` | Crear | Nueva migracion |
| `app/Models/Lot.php` | Modificar | Agregar campos, metodos, scopes, **validacion MAT -> CAL** |
| `app/Livewire/Admin/SentLists/ShippingListDisplay.php` | Modificar | Agregar logica del modal **con validacion** |
| `resources/views/livewire/admin/sent-lists/shipping-list-display.blade.php` | Modificar | Agregar modal y botones **con indicador de bloqueo** |
| `database/seeders/PermissionSeeder.php` | Modificar | Agregar permiso |
| `tests/Feature/ShippingListQualityTest.php` | Crear | Tests de la funcionalidad |
| `tests/Feature/LotQualityDependencyTest.php` | Crear | **Tests de dependencia MAT -> CAL** |

---

## Referencias

### Documentacion del Proyecto

- **Spec 08:** Estrategias de Manejo de Status de Produccion con Work Orders y Kits
- **Diagrama de flujo general:** `Diagramas_flujo/Estructura/Flexcon_Tracker_ERP.md`
- **Modelo Lot:** `app/Models/Lot.php`
- **Modelo Kit:** `app/Models/Kit.php`
- **Componente existente:** `app/Livewire/Admin/SentLists/ShippingListDisplay.php`
- **CRUD de Kits:** `app/Livewire/Admin/Kits/`
- **Gestion de Materiales:** `app/Livewire/Admin/Materials/KitManagement.php`

### Tecnologias

- **Laravel:** 12.x
- **Livewire:** 3.x
- **Tailwind CSS:** 3.x
- **Alpine.js:** 3.x
- **Base de Datos:** MySQL/PostgreSQL

---

## Historial de Cambios

| Version | Fecha | Autor | Cambios |
|---------|-------|-------|---------|
| 1.0 | 2026-02-02 | Agent Architect | Creacion inicial del spec |
| 2.0 | 2026-02-02 | Agent Architect | Agregada dependencia MAT -> CAL, Analisis completo del CRUD de Kits, Actualizacion de flujos y validaciones |

---

**Fin del Spec 19 - Modal de Status de Calidad por Lote en Lista de Envio v2.0**
