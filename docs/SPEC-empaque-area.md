# SPEC: Área de Empaque (Packaging Module)

> **Fecha:** 2026-02-27  
> **Ubicación:** Shipping List Display (`/admin/sent-lists/display`)  
> **Componente:** `ShippingListDisplay`  
> **Estado actual:** El modal de empaque solo tiene approve/reject con comentarios. Necesita rediseño completo.

---

## 1. Contexto y Flujo General

```
Producción ─pesa─► Calidad ─verifica─► Empaque ─empaca─► Ctrl. Materiales ─decide─► Cierre
```

### Ejemplo numérico (un lote de 10,000 pz):

| Paso | Dept. | Acción | Resultado |
|------|-------|--------|-----------|
| 1 | Producción | Pesa 10,000 pz | `weighings.good_pieces = 10,000` |
| 2 | Calidad | Verifica: 9,950 buenas, 50 malas (scrap) | `quality_weighings: good=9,950, bad=50` |
| 3 | Empaque | Recibe 9,950 pz. Empaca 9,900. Sobrantes = 50 | `packed_pieces=9,900, surplus=50` |
| 4 | Empaque | Ajusta sobrantes: reconteo da 30 | `adjusted_surplus=30` (de 50 original) |
| 5 | Empaque | Confirma empaque: 9,900 empacadas, 30 sobrantes finales | Empaque confirmado |
| 6 | Empaque | Click **"Recibí Viajero"** | Señala fin de producción+empaque |
| 7 | Ctrl. Mat. | Ve 3 opciones y elige una | Decisión de cierre |
| 8 | Ctrl. Mat. | Si hay sobrantes: confirma recepción de material | Sobrantes eliminados de shipping list |

---

## 2. Cálculos Clave

```
piezas_disponibles_empaque = SUM(quality_weighings.good_pieces) para el lote
                           (= lo que Calidad aprobó)

piezas_empacadas           = valor ingresado por Empaque

sobrantes_iniciales        = piezas_disponibles_empaque - piezas_empacadas

sobrantes_ajustados        = valor corregido por Empaque (reconteo manual)
                           (default = sobrantes_iniciales si no se ajusta)

piezas_pendientes_empaque  = piezas_disponibles_empaque - ya_empacadas_acumulado
```

---

## 3. Base de Datos

### 3.1. Nueva tabla: `packaging_records`

Registra cada acción de empaque (similar a `quality_weighings` para Calidad). Permite múltiples registros de empaque por lote.

```php
Schema::create('packaging_records', function (Blueprint $table) {
    $table->id();
    $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
    $table->foreignId('kit_id')->nullable()->constrained()->nullOnDelete();
    $table->integer('available_pieces');        // piezas que recibió de Calidad
    $table->integer('packed_pieces');           // piezas realmente empacadas
    $table->integer('surplus_pieces');          // sobrantes = available - packed
    $table->integer('adjusted_surplus')->nullable(); // sobrantes tras reconteo
    $table->text('comments')->nullable();
    $table->timestamp('packed_at');
    $table->foreignId('packed_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
    $table->softDeletes();
});
```

### 3.2. Nuevas columnas en tabla `lots`

```php
Schema::table('lots', function (Blueprint $table) {
    // Viajero
    $table->boolean('viajero_received')->default(false);
    $table->timestamp('viajero_received_at')->nullable();
    $table->foreignId('viajero_received_by')->nullable()->constrained('users')->nullOnDelete();

    // Decisión de cierre (por Control de Materiales)
    $table->string('closure_decision')->nullable();
    // Valores: 'complete_lot' | 'new_lot' | 'close_as_is'
    $table->foreignId('closure_decided_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('closure_decided_at')->nullable();

    // Recepción de sobrantes por Ctrl. Materiales
    $table->boolean('surplus_received')->default(false);
    $table->timestamp('surplus_received_at')->nullable();
    $table->foreignId('surplus_received_by')->nullable()->constrained('users')->nullOnDelete();
});
```

### 3.3. Nuevo modelo: `PackagingRecord`

```
App\Models\PackagingRecord
- lot(): BelongsTo → Lot
- kit(): BelongsTo → Kit (nullable)
- packedBy(): BelongsTo → User
```

### 3.4. Actualizar modelo `Lot`

```php
// Nueva relación
public function packagingRecords(): HasMany
{
    return $this->hasMany(PackagingRecord::class);
}

// Helpers
public function getPackagingAvailablePieces(): int
{
    // Piezas que Calidad aprobó (disponibles para empaque)
    return $this->getQualityGoodPieces();
}

public function getPackagingPackedPieces(): int
{
    return (int) $this->packagingRecords()->sum('packed_pieces');
}

public function getPackagingPendingPieces(): int
{
    return max(0, $this->getPackagingAvailablePieces() - $this->getPackagingPackedPieces());
}

public function getPackagingTotalSurplus(): int
{
    // Usar adjusted_surplus si existe, sino surplus_pieces
    return (int) $this->packagingRecords()->sum(
        DB::raw('COALESCE(adjusted_surplus, surplus_pieces)')
    );
}

public function isViajeroReceived(): bool
{
    return (bool) $this->viajero_received;
}

public function hasClosureDecision(): bool
{
    return !is_null($this->closure_decision);
}

public function isSurplusReceived(): bool
{
    return (bool) $this->surplus_received;
}
```

---

## 4. Modal de Empaque (Rediseño)

El modal actual (`openPackagingModal`) se reemplaza por un modal con **4 fases** que se habilitan secuencialmente.

### Fase 1: Registrar Empaque
> **Quién:** Empaque  
> **Cuándo:** Calidad ya verificó piezas (hay `quality_weighings.good_pieces > 0`)

**Campos del formulario:**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| Piezas disponibles | Read-only | `getPackagingAvailablePieces()` (de Calidad) |
| Ya empacadas | Read-only | `getPackagingPackedPieces()` (acumulado) |
| Pendientes de empacar | Read-only | `getPackagingPendingPieces()` |
| **Piezas empacadas** | Input number | Lo que empacó esta vez |
| Sobrantes | Auto-calc | `pendientes - empacadas` (se muestra en vivo) |
| Fecha/hora | Datetime | Default: now |
| Comentarios | Textarea | Opcional |

**Validaciones:**
- `packed_pieces >= 0`
- `packed_pieces <= pendientes_empaque`
- Al guardar: `surplus_pieces = pendientes - packed_pieces`

**Tabla de registros previos** (como el modal de Calidad):
- Lista de `packaging_records` del lote con edit/delete
- Columnas: Empacadas | Sobrantes | Ajuste | Fecha | Usuario | Acciones

### Fase 2: Ajustar Sobrantes
> **Quién:** Empaque  
> **Cuándo:** Después de registrar empaque, si hay sobrantes

Empaque puede recontear las piezas sobrantes y ajustar el número. Se edita el campo `adjusted_surplus` del registro.

**Campos:**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| Sobrantes originales | Read-only | `surplus_pieces` |
| **Sobrantes ajustados** | Input number | Reconteo real |
| Motivo ajuste | Textarea | Requerido si se cambia |

**Validación:**
- `adjusted_surplus >= 0`
- `adjusted_surplus <= surplus_pieces` (no puede ser mayor que el original, las piezas no aparecen de la nada)

### Fase 3: Recibí Viajero
> **Quién:** Empaque  
> **Cuándo:** Cuando todo el lote está empacado (pendientes = 0) O se decide cerrar

**Botón: "Recibí Viajero"** — Prominente, con confirmación.

- Marca `lots.viajero_received = true`, `viajero_received_at = now()`, `viajero_received_by = auth`
- Significa: "Producción terminó, empaque terminó, el viajero físico fue recibido"
- **Irreversible** (requiere confirm dialog)
- Una vez clickeado, habilita la Fase 4

**Resumen visual al momento de click:**
```
┌─────────────────────────────────┐
│ RESUMEN DE LOTE                 │
│ Lote: 001                       │
│ Piezas empacadas: 9,900         │
│ Sobrantes finales: 30           │
│                                 │
│ [✓ Recibí Viajero]              │
└─────────────────────────────────┘
```

### Fase 4: Decisión de Control de Materiales
> **Quién:** Control de Materiales  
> **Cuándo:** Después de "Recibí Viajero"

Se muestran **3 botones/opciones:**

#### Opción 1: Completar Lote
- Crea un nuevo kit asociado al lote con `quantity = sobrantes_ajustados`
- Ese kit pasa por el flujo completo: Producción → Calidad → Empaque
- El lote sigue abierto hasta que se empaquen esas piezas adicionales
- `closure_decision = 'complete_lot'`

#### Opción 2: Crear Nuevo Lote
- Crea un nuevo `Lot` en la misma Work Order
- `quantity = sobrantes_ajustados`
- `lot_number` auto-generado
- El nuevo lote inicia desde cero: Kit → Inspección → Producción → Calidad → Empaque
- El lote actual se cierra
- `closure_decision = 'new_lot'`

#### Opción 3: Cerrar Lote (con sobrante)
- El lote se cierra tal cual
- Los sobrantes se devuelven a materiales
- Se habilita botón **"Confirmar Recepción de Materiales"**
- `closure_decision = 'close_as_is'`

### Fase 4b: Confirmar Recepción de Sobrantes
> **Quién:** Control de Materiales  
> **Cuándo:** Si `closure_decision = 'close_as_is'` y hay sobrantes

**Botón: "Material Recibido"**
- `lots.surplus_received = true`, `surplus_received_at = now()`, `surplus_received_by = auth`
- **Al confirmar:** Los sobrantes se **eliminan** de la shipping list
  - Se actualiza `lots.packaging_status = 'approved'` (o 'completed')
  - El lote cambia a `status = 'completed'`
  - Se recalcula `work_order.sent_pieces` (solo cuenta piezas realmente empacadas)

---

## 5. Semáforo de Empaque (Columna EMP.)

Actualizar el semáforo en la tabla de shipping list:

| Color | Condición |
|-------|-----------|
| **Gris** | Calidad no ha verificado nada aún (`quality_good_pieces = 0`) |
| **Amarillo** | Hay piezas disponibles para empacar pero aún no se empaca todo |
| **Verde** | Todo empacado y viajero recibido |
| **Azul** | Viajero recibido, pendiente decisión de Ctrl. Materiales |
| **Naranja** | Cerrado con sobrantes, pendiente recepción de materiales |

```php
public function getPackagingSemaphoreStatus(): string
{
    $available = $this->getPackagingAvailablePieces();
    if ($available <= 0) return 'gray';

    if ($this->isSurplusReceived()) return 'green';
    if ($this->closure_decision === 'close_as_is') return 'orange';
    if ($this->isViajeroReceived()) return 'blue';

    $packed = $this->getPackagingPackedPieces();
    if ($packed <= 0) return 'yellow';
    if ($packed >= $available) return 'green';

    return 'yellow';
}
```

---

## 6. Impacto en Shipping List Display

### 6.1. Columna "Pz Sobrantes" (nivel WO)

Actualmente muestra `getQualityPendingPieces()` (piezas pendientes de verificar en calidad). **Debe cambiar** para reflejar los sobrantes de empaque cuando existan:

```
Si el lote tiene packaging_records:
    sobrantes = getPackagingTotalSurplus()  (sobrantes de empaque)
Si no:
    sobrantes = getQualityPendingPieces()   (legacy: pendientes calidad)
```

### 6.2. Columna "Cant. a Enviar" (nivel WO)

Debe reflejar las piezas realmente empacadas:
```
cant_a_enviar = SUM(piezas empacadas por lote) para lotes con empaque
             + SUM(quality_good_pieces) para lotes sin empaque aún
```

### 6.3. Eliminación de sobrantes de la Shipping List

Cuando Control de Materiales confirma recepción (`surplus_received = true`):
- Las piezas sobrantes ya NO se cuentan en "Pz Sobrantes"
- El lote se marca como `completed`
- `sent_pieces` del WO se actualiza basado en piezas empacadas (no en cantidad del lote)

---

## 7. Propiedades Livewire Nuevas (ShippingListDisplay)

```php
// Modal de Empaque (rediseño)
public $showPackagingModal = false;
public $selectedLotForPackaging = null;

// Fase 1: Registro de empaque
public $pkgAvailablePieces = 0;      // read-only: de calidad
public $pkgAlreadyPacked = 0;        // read-only: acumulado
public $pkgPendingPieces = 0;        // read-only: disponible - empacado
public $pkgPackedPieces = 0;         // input
public $pkgSurplusPieces = 0;        // auto-calc
public $pkgPackedAt = '';             // datetime input
public $pkgComments = '';             // textarea
public $pkgRecordsList = [];         // tabla de registros previos
public $pkgEditingId = null;         // editing existing record

// Fase 2: Ajuste de sobrantes
public $pkgAdjustedSurplus = null;
public $pkgAdjustmentReason = '';

// Fase 3 & 4: flags read from lot
public $pkgViajeroReceived = false;
public $pkgClosureDecision = null;
public $pkgSurplusReceived = false;

// Para Opción 2 (Crear nuevo lote)
public $pkgNewLotQuantity = 0;
```

---

## 8. Métodos Livewire Nuevos

| Método | Descripción |
|--------|-------------|
| `openPackagingModal($lotId)` | Abre modal y carga datos del lote (reemplaza actual) |
| `closePackagingModal()` | Cierra y resetea |
| `savePackaging()` | Guarda/actualiza PackagingRecord |
| `editPackagingRecord($id)` | Carga registro para edición |
| `cancelEditPackaging()` | Cancela edición |
| `deletePackagingRecord($id)` | Elimina registro |
| `adjustSurplus($recordId)` | Guarda `adjusted_surplus` en registro |
| `receiveViajero()` | Marca `viajero_received` en lote |
| `completeLot()` | Opción 1: crea kit complementario |
| `createNewLot()` | Opción 2: crea nuevo lote en el WO |
| `closeAsIs()` | Opción 3: cierra lote con sobrantes |
| `confirmSurplusReceived()` | Ctrl. Mat. confirma recepción de sobrantes |

---

## 9. Permisos / Roles (Sugerencia)

| Acción | Rol(es) |
|--------|---------|
| Registrar empaque | Empaque |
| Ajustar sobrantes | Empaque |
| Recibí Viajero | Empaque |
| Completar lote / Nuevo lote / Cerrar | Control de Materiales |
| Confirmar recepción sobrantes | Control de Materiales |

> **Nota:** Si aún no hay sistema de roles granulares, se puede controlar con flags o se implementa después. El spec no depende de esto.

---

## 10. Diagrama de Estados del Lote (Empaque)

```
                    ┌─────────────┐
                    │  Sin empaque │  (Calidad aún no verifica)
                    │   (gray)     │
                    └──────┬──────┘
                           │ Calidad verifica piezas
                           ▼
                    ┌─────────────┐
                    │  Empacando   │  (Hay piezas disponibles)
                    │  (yellow)    │◄──── Empaque registra piezas
                    └──────┬──────┘      parcialmente
                           │ Todo empacado
                           ▼
                    ┌─────────────┐
                    │  Viajero     │  (Click "Recibí Viajero")
                    │  Recibido    │
                    │  (blue)      │
                    └──────┬──────┘
                           │ Ctrl. Materiales decide
                    ┌──────┼──────────────┐
                    ▼      ▼              ▼
             ┌──────────┐ ┌──────────┐ ┌──────────────┐
             │Completar │ │Nuevo Lote│ │ Cerrar c/    │
             │  Lote    │ │          │ │ sobrantes    │
             │          │ │          │ │  (orange)    │
             └────┬─────┘ └────┬─────┘ └──────┬───────┘
                  │            │               │
                  │ Kit nuevo  │ Lote nuevo    │ Ctrl. Mat.
                  │ → flujo    │ → flujo       │ confirma
                  │ completo   │ completo      │ recepción
                  ▼            ▼               ▼
             ┌─────────────────────────────────────┐
             │           COMPLETADO (green)         │
             └─────────────────────────────────────┘
```

---

## 11. Impacto en Columnas de la Shipping List

### Nivel WO (fila principal):

| Columna | Cálculo Actual | Cálculo Nuevo |
|---------|---------------|---------------|
| Pz Enviadas | `lots.completed.sum(quantity)` | Sin cambio |
| Cant. Pendiente | `cant_WO - pz_enviadas` | Sin cambio |
| Cant. a Enviar | `cant_WO - pz_enviadas` | Sin cambio |
| **Pz Sobrantes** | `quality_pending_pieces` | `SUM(packaging_surplus)` si hay empaque, else `quality_pending` |

### Nivel Lote (sub-fila):

| Columna | Cálculo Actual | Cálculo Nuevo |
|---------|---------------|---------------|
| Cant. a Enviar | `lot.quantity` | `lot.quantity` |
| **Pz Sobrantes** | `quality_pending_pieces` | `packaging_total_surplus` si hay empaque |

### Nueva info visible en fila de lote:
- **Piezas empacadas**: mostrar total empacado en color verde si > 0
- **Status empaque**: icono/badge junto al semáforo (Viajero recibido, Pendiente decisión, etc.)

---

## 12. Notas de Implementación

1. **Migración**: Crear `packaging_records` y agregar columnas a `lots` en una sola migración
2. **Modelo**: Crear `PackagingRecord` con relaciones
3. **Componente**: Reescribir el bloque `PACKAGING (EMPAQUE) MODAL` del `ShippingListDisplay.php`
4. **Vista**: Reescribir el modal de empaque en el blade (actualmente es approve/reject simple)
5. **Semáforo**: Actualizar la lógica del semáforo en el blade
6. **Eliminar sobrantes**: Cuando `surplus_received = true`, recalcular totales del WO

### Orden sugerido de implementación:
1. Migración + Modelo `PackagingRecord`
2. Helpers en `Lot` (cálculos de empaque)
3. Modal Fase 1 (registrar empaque con tabla de registros)
4. Modal Fase 2 (ajuste de sobrantes)
5. Modal Fase 3 (Recibí Viajero)
6. Modal Fase 4 (decisión Ctrl. Materiales + recepción)
7. Actualizar semáforo y columna sobrantes
8. Testing end-to-end del flujo completo
