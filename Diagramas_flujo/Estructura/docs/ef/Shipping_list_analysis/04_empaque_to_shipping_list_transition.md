# Analisis Tecnico: Mecanismo de Transicion Empaque -> Shipping List

**Fecha:** 2026-03-07
**Elaborado por:** Arquitecto de Software - FlexCon Tracker
**Version:** 1.0
**Proposito:** Definir el mecanismo arquitectural mediante el cual un lote cerrado por el area de Empaque queda disponible para ser incluido en un Packing Slip (FPL-10) por el area de Shipping.
**Documentos previos relacionados:**
- `01_shipping_list_analysis.md` — Estructura del Packing Slip FPL-10
- `02_invoice_analysis.md` — Invoice FPL-12 y su relacion 1:1 con el Packing Slip
- `03_field_mapping_lista_envio_to_packing_slip.md` — Mapeo de campos y reglas de negocio

---

## 1. Descripcion del Problema: El Eslabon Faltante

### 1.1 Contexto Operativo

El flujo de produccion de FlexCon tiene dos extremos bien definidos:

- **Extremo inicial (Produccion):** Los lotes se crean, producen, pasan inspeccion de calidad y llegan a Empaque.
- **Extremo final (Shipping):** El area de Shipping toma los lotes completados y empacados, los consolida en un Packing Slip (FPL-10) y lo despacha al cliente S.E.I.P., Inc.

El problema es que **no existe un mecanismo definido que conecte ambos extremos**. Actualmente la tabla `lots` tiene el campo `packaging_status` con valores `pending / in_progress / completed`, pero:

1. No esta claro quien marca `packaging_status = 'completed'` ni bajo que criterio exacto.
2. No existe una definicion de cuando un lote pasa de "empacado" a "listo para despacho".
3. El area de Shipping no tiene una vista o cola donde ver cuales lotes estan listos.
4. No hay registro de la **cantidad real empacada** como dato independiente de la cantidad teorica del lote.

### 1.2 Actores Involucrados

| Actor | Rol en este proceso | Sistema actual |
|---|---|---|
| **Empaque (Equin)** | Empacar fisicamente el producto. Decidir cuando un lote esta totalmente empacado. Notificar a Materiales. | Usa la app: modulo de Empaque (packaging records, viajero, closure) |
| **Control de Materiales** | Registrar la decision de cierre del lote (nuevo lote / cerrar como esta / completar). Recibir el sobrante. | Usa la app: decide `closure_decision` en la tabla `lots` |
| **Shipping / Administracion** | Generar el Packing Slip formal con los lotes ya empacados y cerrados. Imprimir y adjuntar al embarque. | Actualmente no tiene modulo en la app: lo hace en Excel (FPL-10) |

### 1.3 El Gap Exacto

El gap se da entre el momento en que **Control de Materiales registra `surplus_received = true`** (el sobrante regreso, el lote esta cerrado operativamente) y el momento en que **Shipping puede incluir ese lote en un Packing Slip**.

```
Estado actual del flujo (con el gap marcado):

[Produccion] -> [Inspeccion QA] -> [Empaque fisico] -> [Viajero a Empaque]
    -> [Empaque registra packaging_records] -> [Empaque decide closure]
    -> [Materiales recibe sobrante]
                        |
                    GAP AQUI
                        |
                        v
              [???] -> [Packing Slip generado]
```

En el estado actual, una vez que `surplus_received = true` en `lots`, no hay nada en el sistema que:
- Indique a Shipping que el lote esta disponible.
- Registre la cantidad real empacada que va al Packing Slip.
- Marque el lote como "en cola para despacho".

---

## 2. Estado Actual del Campo `packaging_status` en la Tabla `lots`

### 2.1 Definicion en Base de Datos

El campo fue agregado en la migracion `2026_02_06_061234_add_packaging_and_final_quality_to_lots_table.php`:

```php
$table->string('packaging_status')->default('pending')->after('inspection_completed_by');
$table->string('packaging_comments')->nullable()->after('packaging_status');
$table->unsignedBigInteger('packaging_inspected_by')->nullable()->after('packaging_comments');
$table->timestamp('packaging_inspected_at')->nullable()->after('packaging_inspected_by');
```

### 2.2 Valores Conocidos

| Valor | Significado declarado | Quien lo asigna (actualmente) |
|---|---|---|
| `pending` | Empaque aun no ha iniciado en este lote | Valor por defecto al crear el lote |
| `in_progress` | Empaque esta trabajando en el lote | No definido aun en la app |
| `completed` | Empaque termino con el lote | No definido aun en la app |

### 2.3 Problema con el Diseño Actual

El campo `packaging_status` fue concebido como un semaforo de tres estados de alto nivel, pero **no es suficiente** para el flujo real por las siguientes razones:

1. **No captura la cantidad real empacada.** La tabla `packaging_records` registra lo que se empacan por sesion (packed_pieces), pero no hay un campo consolidado de "total empacado final" que el Packing Slip pueda consumir directamente.

2. **El cierre del lote en Empaque involucra multiples pasos.** El flujo real tiene: packaging_records -> viajero_received -> closure_decision -> surplus_received. El campo `packaging_status = 'completed'` no refleja en cual de estos pasos esta el lote.

3. **No hay distincion entre "empacado fisicamente" y "listo para despacho".** Un lote puede estar fisicamente empacado pero aun pendiente de que Materiales reciba el sobrante, lo que bloquea el cierre operativo.

4. **El campo `packaging_inspected_by` / `packaging_inspected_at` sugiere una inspeccion de empaque separada** que no esta modelada en el flujo actual del Lot model.

### 2.4 El Flujo Real de Empaque (segun el modelo actual)

Basandose en los campos del modelo `Lot` y las migraciones, el flujo real de cierre de un lote en Empaque es:

```
1. Quality aprueba piezas  ->  inspection_status = 'approved'
2. Empaque crea packaging_records (packed_pieces, surplus_pieces)
3. Empaque envia viajero a Control de Materiales
4. Control de Materiales registra viajero_received = true
5. Control de Materiales decide closure_decision:
   - 'complete_lot'  -> el lote se completa con las piezas empacadas
   - 'new_lot'       -> el sobrante genera un nuevo lote
   - 'close_as_is'   -> se cierra el lote con lo que hay
6. Control de Materiales registra surplus_received = true
   (el sobrante fisico regreso a Materiales)
7. [GAP] --------> Shipping deberia poder incluir el lote en PS
```

**Conclusion:** El campo `packaging_status = 'completed'` deberia ser el estado final del paso 6 (surplus_received = true), pero actualmente no hay logica en el sistema que lo actualice automaticamente.

---

## 3. Opciones de Diseño para el Mecanismo de Transicion

### Opcion A: Cierre Manual de Lote en Empaque

**Descripcion:** El operador de Empaque (o el responsable de Materiales) marca explicitamente un lote como "Empaque Completado" desde la app, mediante un boton de accion dentro de la vista del lote. Esta accion establece `lots.packaging_status = 'completed'` y el lote queda visible en la cola de Shipping.

**Flujo:**

```
Empaque -> App FlexCon Tracker -> Boton "Cerrar Empaque" en vista del lote
       -> lots.packaging_status = 'completed'
       -> lots.packaging_completed_at = now()
       -> lots.packaging_completed_by = user_id
       -> [Lote aparece en cola de Shipping]
```

**Criterio de habilitacion del boton:**
- `surplus_received = true` (el sobrante ya regreso a Materiales)
- O `closure_decision = 'complete_lot'` y `viajero_received = true`

**Pros:**
- Simple de implementar: una accion, un campo actualizado.
- Control explicito: alguien toma la decision consciente de cerrar el lote.
- Auditable: se sabe quien y cuando cerro el empaque.
- Consistente con el modelo mental del equipo operativo.

**Contras:**
- Requiere disciplina del equipo: si no hacen clic, el lote nunca pasa a Shipping.
- Paso adicional que puede olvidarse o retrasarse.
- Si el criterio de habilitacion no es claro, genera confusion sobre cuando usar el boton.

**Complejidad tecnica:** Baja. Agrega 3 campos a `lots` + un boton en el componente Livewire de detalle del lote.

**Cambios en BD:**
```sql
ALTER TABLE lots ADD COLUMN packaging_completed_at TIMESTAMP NULL;
ALTER TABLE lots ADD COLUMN packaging_completed_by BIGINT UNSIGNED NULL;
-- packaging_status ya existe; usar 'completed' como valor final
```

---

### Opcion B: Trigger Automatico desde el Cierre del Lote en Materiales

**Descripcion:** Cuando Control de Materiales registra `surplus_received = true` en el sistema (ultimo paso del proceso de cierre), el sistema automaticamente actualiza `packaging_status = 'completed'` y registra el timestamp. El lote queda disponible para Shipping sin intervencion adicional.

**Flujo:**

```
Materiales -> App: registra surplus_received = true en lots
           -> Observer o mutator en Lot model detecta el cambio
           -> Si surplus_received cambia a true:
              lots.packaging_status = 'completed'
              lots.packaging_completed_at = now()
           -> Lote aparece automaticamente en cola de Shipping
           -> (Opcional) Notificacion al responsable de Shipping
```

**Implementacion sugerida (Eloquent Observer):**

```php
// app/Observers/LotObserver.php
class LotObserver
{
    public function updated(Lot $lot): void
    {
        // Cuando el sobrante es recibido, marcar empaque como completado
        if ($lot->isDirty('surplus_received') && $lot->surplus_received === true) {
            $lot->updateQuietly([
                'packaging_status'       => 'completed',
                'packaging_completed_at' => now(),
                'packaging_completed_by' => auth()->id(),
            ]);
        }

        // Caso especial: closure_decision = complete_lot sin sobrante
        if ($lot->isDirty('closure_decision')
            && $lot->closure_decision === Lot::CLOSURE_COMPLETE_LOT
            && $lot->viajero_received === true) {
            $lot->updateQuietly([
                'packaging_status'       => 'completed',
                'packaging_completed_at' => now(),
                'packaging_completed_by' => auth()->id(),
            ]);
        }
    }
}
```

**Pros:**
- Cero pasos adicionales para el equipo operativo.
- Consistente: el cierre del lote en Materiales ES el evento que dispara la disponibilidad.
- No hay riesgo de que alguien "olvide" hacer el paso.
- El flujo es determinis: si surplus_received = true, el lote esta listo.

**Contras:**
- La logica esta oculta en un Observer; si algo falla silenciosamente, el lote no pasa a Shipping y es dificil de diagnosticar.
- Puede haber casos borde: lotes donde no hay sobrante (closure_decision = complete_lot) que no activan el trigger de surplus_received.
- Menos control explicito: Shipping no puede "retener" un lote que ya fue marcado automaticamente.

**Complejidad tecnica:** Media. Requiere un LotObserver con logica de casos borde para los tres tipos de closure_decision.

**Cambios en BD:**
```sql
ALTER TABLE lots ADD COLUMN packaging_completed_at TIMESTAMP NULL;
ALTER TABLE lots ADD COLUMN packaging_completed_by BIGINT UNSIGNED NULL;
-- packaging_status ya existe; se actualiza automaticamente via Observer
```

---

### Opcion C: Cola de Lotes Listos para Despacho (Staging Area)

**Descripcion:** Se crea una entidad intermedia `packaging_completions` que actua como una cola formal de "lotes listos para el proximo Packing Slip". Empaque (o Materiales) registra explicitamente cada lote terminado en esta cola, incluyendo la **cantidad real empacada** (que puede diferir de la cantidad teorica del lote). Shipping List consume esta cola para crear el Packing Slip.

**Estructura propuesta para la tabla `packaging_completions`:**

```sql
CREATE TABLE packaging_completions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lot_id          BIGINT UNSIGNED NOT NULL,        -- FK -> lots.id
    work_order_id   BIGINT UNSIGNED NOT NULL,        -- FK -> work_orders.id (desnormalizado para queries rapidos)
    quantity_packed INTEGER NOT NULL,                -- CANTIDAD REAL EMPACADA (puede diferir de lots.quantity)
    quantity_theoretical INTEGER NOT NULL,           -- Snapshot de lots.quantity al momento del cierre
    completed_at    TIMESTAMP NOT NULL,              -- Cuando se registro el cierre
    completed_by    BIGINT UNSIGNED NULL,            -- FK -> users.id
    notes           TEXT NULL,                       -- Observaciones del operador
    packing_slip_id BIGINT UNSIGNED NULL,            -- FK -> packing_slips.id (NULL hasta que se despacha)
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    deleted_at      TIMESTAMP NULL,                  -- SoftDeletes
    FOREIGN KEY (lot_id) REFERENCES lots(id),
    FOREIGN KEY (work_order_id) REFERENCES work_orders(id),
    FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (packing_slip_id) REFERENCES packing_slips(id) ON DELETE SET NULL
);
```

**Flujo:**

```
Empaque/Materiales -> App: "Registrar cierre de empaque" para el lote
                  -> Crear registro en packaging_completions:
                     lot_id, quantity_packed (real), quantity_theoretical
                  -> lots.packaging_status = 'completed'

Shipping -> Vista "Cola de despacho": lista packaging_completions donde packing_slip_id IS NULL
         -> Selecciona los registros a incluir en el proximo PS
         -> Crea PackingSlip
         -> Crea PackingSlipItems usando quantity_packed (real) de packaging_completions
         -> Actualiza packaging_completions.packing_slip_id = nuevo PS id
```

**Pros:**
- Captura la cantidad real empacada separada de la cantidad teorica del lote. CRITICO para el Packing Slip.
- La cola es visible y auditoria para Shipping: ven exactamente que lotes estan pendientes de despacho.
- Permite que Shipping tenga control sobre cuales lotes incluir en cada PS (pueden acumular varios lotes antes de despachar).
- El campo `packing_slip_id` en `packaging_completions` es mas limpio que en `lots` (un lote podria teoricamente tener dos embarques parciales, aunque en la practica no es comun).
- Historial completo de todos los cierres de empaque, incluso los ya despachados.

**Contras:**
- Mayor complejidad: nueva tabla, nuevas relaciones, nueva logica de UI.
- Requiere que el operador haga un paso extra para registrar el cierre.
- Puede haber ambiguedad: si ya existe `packaging_records` en `lots`, la cantidad real ya esta calculable desde ahi. La nueva tabla duplica informacion.

**Complejidad tecnica:** Alta. Nueva tabla + modelo + relaciones + componente Livewire de "cola de despacho" + modificacion del modulo de Packing Slip para leer de esta cola.

---

### Opcion D: Integracion Directa Empaque → Packing Slip Draft

**Descripcion:** Al cerrar un lote en Empaque (cuando `surplus_received = true` o `closure_decision` final se registra), el sistema automaticamente crea o actualiza un Packing Slip en estado `draft` con ese lote pre-incluido. El responsable de Shipping simplemente abre el borrador, lo revisa y lo confirma para generar el PDF.

**Flujo:**

```
Materiales -> App: registra surplus_received = true (o closure_decision final)
           -> Sistema busca si existe un PackingSlip en estado 'draft' para esta semana/periodo
           -> Si existe: agrega el lote al draft existente (nuevo PackingSlipItem)
           -> Si no existe: crea un nuevo PackingSlip en estado 'draft'
                           agrega el lote como primer PackingSlipItem
           -> Notifica a Shipping que hay items pendientes de confirmacion

Shipping -> Abre el Packing Slip en estado 'draft'
         -> Revisa los lotes pre-cargados
         -> Ajusta si es necesario (eliminar lotes, cambiar orden)
         -> Confirma -> PackingSlip.status = 'confirmed'
         -> Genera PDF -> PackingSlip.status = 'shipped'
```

**Pros:**
- Shipping tiene el menor trabajo posible: solo revisar y confirmar.
- El PS ya tiene los datos cargados; reduce errores de transcripcion.
- El borrador acumula lotes a lo largo de la semana automaticamente.

**Contras:**
- Alta complejidad: logica de negocio para decidir a que draft se agrega cada lote.
- Si Materiales cierra un lote en el momento equivocado, el lote va al draft incorrecto.
- El concepto de "draft por semana" puede no coincidir con la operativa real (no siempre es un PS por semana).
- Revierte la responsabilidad: quien crea el PS es el sistema automaticamente, no Shipping. Esto puede generar confusion de ownership.
- Dificil de corregir si hay errores: un lote que fue incluido en un draft equivocado requiere logica de remocion.

**Complejidad tecnica:** Muy Alta. Implica logica de seleccion de draft, posibles conflictos de concurrencia, y un cambio de paradigma en quien controla la creacion del PS.

---

### Opcion E: Transicion Automatica por Semaforo de Empaque (Hibrida A+B)

**Descripcion:** Se combina la automatizacion de la Opcion B con la visibilidad explicita de la Opcion A. El sistema actualiza `packaging_status = 'completed'` automaticamente cuando el semaforo de empaque llega a verde (`getPackagingSemaphoreStatus() = 'green'`). Adicionalmente, la UI muestra este estado claramente y permite que Shipping filtre lotes por este semaforo.

La logica del semaforo verde (ya definida en el modelo `Lot`) es:
```
Verde = surplus_received OR (hasClosureDecision AND NOT en_espera_de_surplus)
```

Esta opcion NO requiere una nueva tabla. Aprovecha la logica de semaforo ya existente en el modelo `Lot` para determinar elegibilidad.

**Pros:**
- Reutiliza logica ya existente en el sistema (getPackagingSemaphoreStatus).
- No requiere pasos manuales adicionales del equipo.
- Consistente con el modelo mental de los operadores (el semaforo verde = listo).

**Contras:**
- La logica del semaforo puede cambiar en el futuro y afectar indirectamente la elegibilidad para el PS.
- No captura cantidad real empacada de forma explicita.

---

## 4. Analisis de la Cantidad Real vs Teorica

### 4.1 El Problema de las Cantidades

En la operativa real de FlexCon, la cantidad que se empacan en un lote **frecuentemente difiere** de la cantidad teorica planificada en la WO. Esto ocurre por:

- **Piezas rechazadas en inspeccion de calidad:** Calidad rechaza piezas que no cumplen spec.
- **Piezas en sobrante:** Algunas piezas superan la cantidad del lote y se envian al siguiente.
- **Ajustes de reconteo:** El campo `adjusted_surplus` en `packaging_records` indica que la cantidad fue ajustada manualmente.

### 4.2 Donde Existe la Cantidad Real Hoy

| Campo | Tabla | Descripcion | Limitacion |
|---|---|---|---|
| `lots.quantity` | `lots` | Cantidad teorica planificada del lote | No refleja rechazos ni sobrantes |
| `packaging_records.packed_pieces` | `packaging_records` | Piezas empacadas por sesion de empaque | Parcial: un lote puede tener multiples registros |
| `packaging_records.surplus_pieces` | `packaging_records` | Sobrante calculado por sesion | Parcial |
| `packaging_records.adjusted_surplus` | `packaging_records` | Sobrante ajustado por reconteo | Parcial |
| `Lot::getPackagingPackedPieces()` | Calculado via hasMany | Suma de `packed_pieces` de todos los registros | Calculado en PHP, no persistido |
| `Lot::getPackagingTotalSurplus()` | Calculado via hasMany | Surplus efectivo total | Calculado en PHP, no persistido |

### 4.3 Cantidad que Debe ir al Packing Slip

La cantidad que aparece en el Packing Slip (campo `Quantity` en `packing_slip_items`) es:

```
quantity_for_packing_slip = Lot::getPackagingPackedPieces()
                          = SUM(packaging_records.packed_pieces)
                            WHERE lot_id = X AND deleted_at IS NULL
```

Este valor es calculable desde la BD pero **no esta persistido** en ningun campo de la tabla `lots`. Cuando se crea el `packing_slip_item`, este valor debe calcularse y guardarse como snapshot.

### 4.4 Recomendacion sobre la Cantidad

Se recomienda **persistir la cantidad real empacada** en el momento del cierre del lote. Esto evita recalculos y garantiza que el snapshot del Packing Slip sea consistente con el estado en el momento del cierre, incluso si en el futuro se eliminan o modifican `packaging_records` por error.

**Campo sugerido:**
```sql
ALTER TABLE lots ADD COLUMN quantity_packed_final INTEGER NULL AFTER quantity;
-- Se popula en el momento del cierre de empaque
-- Es el valor que se usara en el Packing Slip como quantity del lote
```

---

## 5. Flujo de Datos Propuesto (Opcion Recomendada)

La recomendacion arquitectural se presenta en la seccion 7. El flujo a continuacion asume la adopcion de la **Opcion B mejorada con persistencia de cantidad real** (ver seccion 7).

### 5.1 Diagrama de Flujo Completo

```
╔══════════════════════════════════════════════════════════════╗
║           MODULO DE EMPAQUE (App FlexCon Tracker)           ║
║                                                              ║
║  lots.status = 'completed'                                   ║
║  lots.inspection_status = 'approved'                         ║
║                                                              ║
║  [Empaque] -> Crear packaging_records                        ║
║           -> packed_pieces (por sesion)                      ║
║           -> surplus_pieces                                  ║
║           -> adjusted_surplus (si hubo reconteo)             ║
║                                                              ║
║  [Empaque] -> Envia viajero fisico a Materiales              ║
║                                                              ║
║  [Materiales] -> lots.viajero_received = true                ║
║              -> lots.closure_decision = [decision]           ║
╚══════════════════════════════════════════════════════════════╝
                          |
          Segun closure_decision:
          |                    |                   |
    complete_lot          new_lot            close_as_is
          |                    |                   |
    No hay sobrante    Crea nuevo Lot       Cierra con lo empacado
          |            Surplus -> nuevo     |
          |            Lot.quantity         |
          +------------------------------------+
                          |
                          v
╔══════════════════════════════════════════════════════════════╗
║        ULTIMO PASO: MATERIALES RECIBE SOBRANTE              ║
║                                                              ║
║  lots.surplus_received = true                                ║
║  (o si no hay sobrante: closure_decision = complete_lot)    ║
║                                                              ║
║  -> EVENTO DISPARADOR (Observer o Service):                  ║
║     lots.packaging_status = 'completed'                      ║
║     lots.quantity_packed_final = SUM(packaging_records       ║
║                                      .packed_pieces)         ║
║     lots.packaging_completed_at = NOW()                      ║
║     lots.packaging_completed_by = auth()->id()               ║
╚══════════════════════════════════════════════════════════════╝
                          |
                          | Lote ahora cumple todos los criterios
                          | de elegibilidad para Packing Slip:
                          |
                          |  lots.status = 'completed'
                          |  lots.inspection_status = 'approved'
                          |  lots.packaging_status = 'completed'
                          |  lots.packing_slip_id IS NULL
                          |
                          v
╔══════════════════════════════════════════════════════════════╗
║       MODULO DE SHIPPING LIST (App FlexCon Tracker)         ║
║                                                              ║
║  Vista: "Lotes Listos para Despacho"                         ║
║  Query:                                                      ║
║    SELECT lots.*                                             ║
║    FROM lots                                                 ║
║    WHERE lots.status = 'completed'                           ║
║      AND lots.inspection_status = 'approved'                 ║
║      AND lots.packaging_status = 'completed'                 ║
║      AND lots.packing_slip_id IS NULL                        ║
║      AND lots.deleted_at IS NULL                             ║
║    ORDER BY lots.packaging_completed_at ASC                  ║
║                                                              ║
║  Shipping selecciona lotes -> Crea PackingSlip               ║
║  PackingSlip.slip_number = 'SL001250' (correlativo)          ║
╚══════════════════════════════════════════════════════════════╝
                          |
                          v
╔══════════════════════════════════════════════════════════════╗
║         CREACION DEL PACKING SLIP ITEM                       ║
║                                                              ║
║  Por cada lote seleccionado:                                 ║
║                                                              ║
║  PackingSlipItem::create([                                   ║
║    'packing_slip_id' => $ps->id,                             ║
║    'lot_id'          => $lot->id,                            ║
║    'work_order_id'   => $lot->work_order_id,                 ║
║    -- Snapshots inmutables:                                  ║
║    'wo_number'       => 'W0' + external_wo_num + lot_seq,    ║
║    'po_number'       => $po->po_number,                      ║
║    'item_number'     => $part->number,                       ║
║    'description'     => $part->description,                  ║
║    'quantity'        => $lot->quantity_packed_final,         ║
║    'label_spec'      => (ingreso manual de Shipping),        ║
║  ]);                                                         ║
║                                                              ║
║  Luego:                                                      ║
║  lots.packing_slip_id = $ps->id  <- marca como despachado    ║
╚══════════════════════════════════════════════════════════════╝
                          |
                          v
╔══════════════════════════════════════════════════════════════╗
║           PACKING SLIP GENERADO (FPL-10)                    ║
║                                                              ║
║  Packing Slip #SL001250                                      ║
║  DATE: Mar-07-2026                                           ║
║                                                              ║
║  WO#           | PO#   | Item#     | Desc    | Qty  | Label  ║
║  W01980231001  | 49032 | 189-10257 | STS H.. | 99850| M83..  ║
║  W01982798001  | 49110 | 189-10179 | STS H.. | 58900| M83..  ║
║  W01982798002  | 49110 | 189-10179 | STS H.. | 34000| M83..  ║
║  W01982798003  | 49110 | 189-10179 | STS H.. |  7100| M83..  ║
║                              Total:           199850          ║
║                                                              ║
║  Total cajas 404-10003: ___   Total 20x20x8: ___            ║
║  Firma Empaque: ___  Firma QA: ___  Firma CM: ___           ║
╚══════════════════════════════════════════════════════════════╝
                          |
                          v
╔══════════════════════════════════════════════════════════════╗
║                 INVOICE FPL-12                               ║
║  (Generado desde el Packing Slip confirmado)                 ║
║  Agrega: Unit Cost, Total por linea, Cargos fijos            ║
╚══════════════════════════════════════════════════════════════╝
```

### 5.2 Diagrama de Estado del Lote

```
                    [CREACION DEL LOTE]
                           |
                    status = 'pending'
                    inspection_status = 'pending'
                    packaging_status = 'pending'
                           |
                           v
                    [PRODUCCION ACTIVA]
                    status = 'in_progress'
                           |
                           v
                    [PRODUCCION TERMINADA]
                    status = 'completed'
                           |
                           v
                  [INSPECCION DE CALIDAD]
              inspection_status = 'approved' (o 'rejected')
                           |
                     (si approved)
                           v
                   [PESADA DE EMPAQUE]
              packaging_records creados
              packed_pieces registradas
                           |
                           v
              [VIAJERO ENVIADO A MATERIALES]
              viajero_received = true
                           |
                           v
              [DECISION DE CIERRE - MATERIALES]
              closure_decision = 'complete_lot'
                            | 'new_lot'
                            | 'close_as_is'
                           |
                    (si hay sobrante)
                           v
              [SOBRANTE RECIBIDO - MATERIALES]
              surplus_received = true
                           |
                    (EVENTO DISPARADOR)
                           v
              [EMPAQUE COMPLETADO - LISTO PARA DESPACHO]
              packaging_status = 'completed'
              quantity_packed_final = [calculado]
              packaging_completed_at = [timestamp]
                           |
                  (Aparece en cola Shipping)
                           v
              [INCLUIDO EN PACKING SLIP]
              packing_slip_id = [ID del PS]
                           |
                           v
                       [DESPACHADO]
```

---

## 6. Cambios de Esquema Necesarios

### 6.1 Cambios en la Tabla `lots` (Recomendados)

```sql
-- Cantidad real empacada al cierre del lote (puede diferir de lots.quantity)
ALTER TABLE lots
    ADD COLUMN quantity_packed_final INT UNSIGNED NULL
    AFTER quantity;

-- Metadata del cierre de empaque
ALTER TABLE lots
    ADD COLUMN packaging_completed_at TIMESTAMP NULL
    AFTER packaging_inspected_at;

ALTER TABLE lots
    ADD COLUMN packaging_completed_by BIGINT UNSIGNED NULL
    AFTER packaging_completed_at;

-- Foreign key (opcional, dependiendo de si packaging_completed_by puede quedar NULL)
ALTER TABLE lots
    ADD CONSTRAINT fk_lots_packaging_completed_by
    FOREIGN KEY (packaging_completed_by) REFERENCES users(id) ON DELETE SET NULL;
```

**Migracion Laravel equivalente:**

```php
// database/migrations/YYYY_MM_DD_HHMMSS_add_shipping_readiness_to_lots_table.php
Schema::table('lots', function (Blueprint $table) {
    $table->unsignedInteger('quantity_packed_final')
          ->nullable()
          ->after('quantity')
          ->comment('Cantidad real empacada al cierre. Usada en packing_slip_items.quantity');

    $table->timestamp('packaging_completed_at')
          ->nullable()
          ->after('packaging_inspected_at')
          ->comment('Timestamp del cierre de empaque (cuando el lote quedo listo para despacho)');

    $table->foreignId('packaging_completed_by')
          ->nullable()
          ->after('packaging_completed_at')
          ->constrained('users')
          ->nullOnDelete()
          ->comment('Usuario que ejecuto o disparo el cierre de empaque');
});
```

### 6.2 Cambios en la Tabla `lots` (Ya Existentes, Solo Documentados)

| Campo existente | Uso en la transicion | Estado |
|---|---|---|
| `packaging_status` | Valor final `'completed'` indica listo para despacho | Existe, no requiere cambio estructural |
| `packing_slip_id` | Segun el contexto del documento 03, se propone este campo en `lots` | **VERIFICAR si ya existe o si solo esta en `packing_slip_items`** |

> **ATENCION:** El documento `03_field_mapping_lista_envio_to_packing_slip.md` menciona `lots.packing_slip_id` como criterio de elegibilidad (`IS NULL = sin despachar`). Este campo DEBE existir en la tabla `lots`. Si no existe aun, debe agregarse:

```php
// Agregar si no existe en lots
$table->foreignId('packing_slip_id')
      ->nullable()
      ->constrained('packing_slips')
      ->nullOnDelete()
      ->comment('ID del Packing Slip donde fue incluido este lote. NULL = pendiente de despacho');
```

### 6.3 Nueva Tabla `packing_slips` (Si No Existe)

```php
Schema::create('packing_slips', function (Blueprint $table) {
    $table->id();
    $table->string('slip_number', 20)->unique()
          ->comment('Numero correlativo. Formato: SL001250, SL001251...');
    $table->date('slip_date')
          ->comment('Fecha del despacho/embarque');
    $table->enum('status', ['draft', 'confirmed', 'shipped'])
          ->default('draft');
    $table->foreignId('created_by')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete();

    // Datos del pie de pagina (ultima pagina del PS)
    $table->integer('total_boxes_404')->nullable()
          ->comment('Total de cajas 404-10003');
    $table->integer('total_boxes_20x20')->nullable()
          ->comment('Total de cajas 20x20x8-1/2"');

    // Firmas (Fase 1: texto; Fase 2: firma digital)
    $table->string('reviewed_by_packaging')->nullable();
    $table->string('reviewed_by_inspection')->nullable();
    $table->string('reviewed_by_cm')->nullable();

    $table->timestamps();
    $table->softDeletes();
});
```

### 6.4 Nueva Tabla `packing_slip_items` (Si No Existe)

```php
Schema::create('packing_slip_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('packing_slip_id')->constrained()->cascadeOnDelete();
    $table->foreignId('lot_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();

    // Snapshots inmutables al momento del despacho
    $table->string('wo_number', 20)
          ->comment('Formato: W01980231001 = W0 + 7dig WO + 3dig lote');
    $table->string('po_number', 20)->nullable();
    $table->string('item_number', 30)->nullable()
          ->comment('Numero de parte del producto');
    $table->string('description', 200)->nullable();
    $table->unsignedInteger('quantity')
          ->comment('Snapshot de quantity_packed_final del lote al momento del despacho');
    $table->string('label_spec', 50)->nullable()
          ->comment('Especificacion de etiqueta militar/aero (ej: M83519/2-8). Ingreso manual');
    $table->string('lot_date_code', 20)->nullable()
          ->comment('Codigo de lote de produccion (ej: 250515A22). PENDIENTE confirmar con cliente');

    // Orden de aparicion en el documento PDF
    $table->unsignedSmallInteger('sort_order')->default(0);
    $table->unsignedSmallInteger('page_number')->default(1);

    // Tipo de fila: 'item' = linea normal, 'total' = fila de total del WO
    $table->enum('row_type', ['item', 'total'])->default('item');

    $table->timestamps();
});
```

### 6.5 Resumen de Cambios por Tabla

| Tabla | Accion | Campos afectados |
|---|---|---|
| `lots` | MODIFICAR | Agregar: `quantity_packed_final`, `packaging_completed_at`, `packaging_completed_by` |
| `lots` | MODIFICAR (si no existe) | Agregar: `packing_slip_id` |
| `lots` | SIN CAMBIO ESTRUCTURAL | `packaging_status` usa valor existente `'completed'` |
| `packing_slips` | CREAR | Tabla nueva completa |
| `packing_slip_items` | CREAR | Tabla nueva completa |

---

## 7. Recomendacion Arquitectural

### 7.1 Opcion Recomendada: B Mejorada (Trigger Automatico + Persistencia de Cantidad)

Para el contexto operativo de FlexCon (empresa manufacturera pequena-mediana, un turno, operadores con acceso a tablet/PC en planta), **se recomienda la Opcion B con las siguientes mejoras:**

**B1. Trigger automatico en el cierre del lote:**
El sistema actualiza `packaging_status = 'completed'` automaticamente cuando el ultimo paso del proceso de Materiales se completa. No requiere un paso adicional del equipo.

**B2. Persistencia de la cantidad real:**
En el mismo evento, se calcula y persiste `quantity_packed_final = SUM(packaging_records.packed_pieces)`. Este valor es el que va al Packing Slip.

**B3. Vista de cola en Shipping:**
Un componente Livewire de Shipping muestra todos los lotes con `packaging_status = 'completed'` y `packing_slip_id IS NULL`, ordenados por `packaging_completed_at`. Shipping elige cuales incluir en el proximo PS.

### 7.2 Justificacion de la Recomendacion

| Criterio | Opcion A | Opcion B (recomendada) | Opcion C | Opcion D |
|---|---|---|---|---|
| Pasos adicionales para el equipo | 1 paso extra | 0 pasos extra | 1 paso extra | 0 pasos extra |
| Captura cantidad real | No (sin campo nuevo) | Si (con mejora B2) | Si | Depende |
| Complejidad de implementacion | Baja | Media | Alta | Muy Alta |
| Riesgo de que el lote "se quede atascado" | Alto (pueden olvidar el paso) | Bajo (automatico) | Medio | Bajo |
| Flexibilidad para Shipping | Media | Alta (Shipping elige cuando despachar) | Alta | Baja (PS creado automaticamente) |
| Consistencia con flujo existente | Alta | Alta | Media | Baja |
| Auditoría | Si | Si | Si | Si |

**Razon principal para elegir B sobre D:** La Opcion D toma el control de la creacion del PS fuera de Shipping. En operaciones reales, el area de Shipping necesita flexibilidad para decidir que lotes incluir en cada embarque (por ejemplo, pueden acumular lotes de varios dias antes de despachar, o pueden excluir un lote porque el transporte no esta listo). La Opcion B respeta esta flexibilidad.

**Razon principal para elegir B sobre C:** La Opcion C introduce una tabla intermedia (`packaging_completions`) que duplica informacion ya calculable desde `packaging_records`. La mejora B2 (persistir `quantity_packed_final` en `lots`) es mas simple y cumple el mismo objetivo.

**Razon principal para elegir B sobre A:** En un entorno de planta, los pasos manuales son propensos a omisiones. Un trigger automatico garantiza que el sistema siempre refleje el estado real del lote sin depender de que el operador recuerde hacer un paso adicional.

### 7.3 Implementacion Paso a Paso (Opcion B Mejorada)

**Paso 1: Migracion de BD**

```php
// Crear: database/migrations/YYYY_add_shipping_readiness_to_lots_table.php
Schema::table('lots', function (Blueprint $table) {
    $table->unsignedInteger('quantity_packed_final')->nullable()->after('quantity');
    $table->timestamp('packaging_completed_at')->nullable()->after('packaging_inspected_at');
    $table->foreignId('packaging_completed_by')->nullable()
          ->constrained('users')->nullOnDelete()->after('packaging_completed_at');
    // Si no existe aun:
    $table->foreignId('packing_slip_id')->nullable()
          ->constrained('packing_slips')->nullOnDelete()->after('packaging_completed_by');
});
```

**Paso 2: Observer en el modelo Lot**

```php
// app/Observers/LotPackagingObserver.php
namespace App\Observers;

use App\Models\Lot;

class LotPackagingObserver
{
    /**
     * Cuando el lote es actualizado, verificar si el cierre de empaque
     * se ha completado y marcar packaging_status = 'completed'.
     */
    public function updated(Lot $lot): void
    {
        // Evitar recursion si ya fue marcado como completed
        if ($lot->packaging_status === 'completed') {
            return;
        }

        if ($this->isPackagingClosureComplete($lot)) {
            $quantityPacked = $lot->getPackagingPackedPieces();

            $lot->updateQuietly([
                'packaging_status'       => 'completed',
                'quantity_packed_final'  => $quantityPacked,
                'packaging_completed_at' => now(),
                'packaging_completed_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Determinar si el cierre de empaque esta completo segun la decision de Materiales.
     *
     * Regla: El lote esta completamente cerrado cuando:
     * - closure_decision = 'complete_lot' Y viajero_received = true
     *   (no hay sobrante, el viajero confirma el cierre)
     * - O surplus_received = true
     *   (hay sobrante y ya fue recibido por Materiales)
     */
    private function isPackagingClosureComplete(Lot $lot): bool
    {
        // Caso 1: closure sin sobrante (complete_lot)
        if ($lot->isDirty('closure_decision')
            && $lot->closure_decision === Lot::CLOSURE_COMPLETE_LOT
            && $lot->viajero_received === true
            && $lot->getPackagingPackedPieces() > 0) {
            return true;
        }

        // Caso 2: surplus recibido (new_lot o close_as_is)
        if ($lot->isDirty('surplus_received') && $lot->surplus_received === true) {
            return true;
        }

        return false;
    }
}
```

**Paso 3: Registrar el Observer**

```php
// app/Providers/AppServiceProvider.php (metodo boot)
use App\Models\Lot;
use App\Observers\LotPackagingObserver;

public function boot(): void
{
    Lot::observe(LotPackagingObserver::class);
    // ... otros observers existentes
}
```

**Paso 4: Scope en el modelo Lot para la cola de Shipping**

```php
// En app/Models/Lot.php
/**
 * Scope: lotes listos para incluir en un Packing Slip.
 * Criterios: completados + inspeccion aprobada + empaque completo + sin PS asignado.
 */
public function scopeReadyForShipping(Builder $query): Builder
{
    return $query
        ->where('status', self::STATUS_COMPLETED)
        ->where('inspection_status', self::INSPECTION_APPROVED)
        ->where('packaging_status', 'completed')
        ->whereNull('packing_slip_id')
        ->orderBy('packaging_completed_at', 'asc');
}
```

**Paso 5: Componente Livewire para la cola de Shipping**

```php
// app/Livewire/Shipping/ReadyForDispatch.php (nuevo componente)
namespace App\Livewire\Shipping;

use App\Models\Lot;
use Livewire\Component;

class ReadyForDispatch extends Component
{
    public function render()
    {
        $lots = Lot::readyForShipping()
            ->with(['workOrder.purchaseOrder.part'])
            ->get();

        return view('livewire.shipping.ready-for-dispatch', compact('lots'));
    }
}
```

---

## 8. Preguntas Abiertas

Las siguientes preguntas deben responderse con el cliente (Frank / equipo operativo de FlexCon) antes de iniciar la implementacion:

### P-04-01 [ALTA PRIORIDAD]: Definicion exacta del evento de cierre de empaque

**Pregunta:** Para el tipo de cierre `complete_lot` (sin sobrante), el cierre de empaque se confirma con:
- a) El registro de `viajero_received = true` por Materiales, o
- b) La firma explicita del responsable de empaque en la app, o
- c) Otra accion especifica?

**Respuesta:** El evento exacto del cierre de empaque, ya esta hecho se definio que se puede cerrar en 3 opciones que ya estan hechas 
- a) Completar el lote
- b) Crear un nuevo Lote
- b) Cerrar el lote

**Impacto:** Define cuando exactamente se dispara el Observer para marcar `packaging_status = 'completed'`.

---

### P-04-02 [ALTA PRIORIDAD]: Cantidad a usar en el Packing Slip cuando hay discrepancia

**Pregunta:** Si la cantidad teorica del lote es 100,000 piezas pero `SUM(packaging_records.packed_pieces)` = 99,850 (por rechazos en QA):
- a) El Packing Slip muestra 99,850 (cantidad real empacada)?
- b) El Packing Slip muestra 100,000 (cantidad teorica del WO)?
- c) El operador de Shipping decide cuantas anotar manualmente?

**Respuesta:** Es posible 2 esenarios para esta respuesta, 
- a) El WO  se cierra corto es decir que se termina en las 99,850 aun que falten 150 piezas por terminar 
- b) Se continua con la WO hasta terminar las 150 piezas faltantes 

**Impacto:** Determina si `packing_slip_items.quantity` toma el valor de `quantity_packed_final` (real) o de `lots.quantity` (teorico).

**Contexto:** En el analisis del documento 03, se observo que las cantidades del Packing Slip SL001249 coinciden exactamente con las de la Lista de Envio (FPL-02), lo que sugiere que se usa la cantidad teorica. Sin embargo, si la Lista de Envio ya refleja los ajustes, podria ser la cantidad real.


---

### P-04-03 [ALTA PRIORIDAD]: Numero de WO externo (7 digitos) en la tabla `work_orders`

**Pregunta (ya planteada en el doc 03, P-01):** El campo `external_wo_number` (ej: `1980231`) ya existe en la tabla `work_orders` de la app o debe agregarse?

**Respuesta:** Debe agregarse un Work Order diferente ya que es un proceso diferente en Shipping List ya que en el packing list cambio el WO se le agrega una constante al principio del (`W0+el_numero_de_WO+el_numero_de_lote`) ejemplo: `W0`+`19802131`+`001` asi que es una concatenacion de una constante que en este caso es `W0` mas el numnero de W mas el numero de lote y si por ejemplo el WO tubo mas de 1 un lote pues va aver mas lineas en el packing list como se ve en el documento de Excel FPL-10 Shipping List 2025 Rev.xlsx  donde se puede ver claramente en el Work Order `W01982798001` hay mas `W01982798002` y `W01982798003` si comparamos este archivo con el de FPL-02 Lista de Envio mayo 2025.xlsx se ven los mismo lotes, asi que es importante concatener estos numeros.    

**Impacto directo en la transicion:** Al construir el `wo_number` del Packing Slip (`W01980231001`), el sistema necesita el numero de WO de 7 digitos. Si no existe en BD, no se puede construir automaticamente.

**Nota** Se puede crear un varible en un componente de livewire que se llame wo_packing_list_const = 'W0' para despues jalar el WO  con suslotes y crear una funcion adicionar para crear esta concatenacion de 'W0+WO+Lotes' para crear la lista y en la misma lista traerme WO con la PO  y el Numero parte y la descripcion y por ultimo la cantidad
---

### P-04-04 [MEDIA PRIORIDAD]: Frecuencia de despacho y agrupacion de lotes

**Pregunta:** Los Packing Slips se generan:
- a) Una vez por semana (el dia de envio semanal), incluyendo todos los lotes listos de esa semana?
- b) Cada vez que se termina de empacar un WO completo, sin importar el dia?
- c) Cuando Shipping decide, sin una frecuencia fija?

**Respuestas:**
- a) Es posible que semande una vez por semana incluyendo todos los lotes listos en esa semana
- b) O shipping decide sin una frecuencia fija

**Impacto:** Determina si la vista de "cola de Shipping" debe agrupar lotes por semana o simplemente listarlos todos.

**Nota:** Esto no es una decicion estraiva asi puede cambiar dependiento el cliente que nos diga en la siguiente reunion
---

### P-04-05 [MEDIA PRIORIDAD]: Quien tiene permiso para crear el Packing Slip

**Pregunta:** El modulo de Shipping List / Packing Slip en la app, quien puede usarlo?
- a) Solo Administracion / Frank
- b) El area de Empaque (Equin)
- c) Un rol especifico de Shipping

**Respuesta:** En escaso podrian ser varios el Adminsitrador, El area Empaque y Shiping

**Impacto:** Define los permisos de Spatie para el nuevo modulo (`shipping.create`, `shipping.view`, etc.).

---

### P-04-06 [MEDIA PRIORIDAD]: Manejo de lotes con sobrante entregado al siguiente lote

**Pregunta:** Cuando `closure_decision = 'new_lot'`, el sobrante fisico se convierte en un nuevo Lot en el sistema. El `Lot` original (que genero el sobrante):
- a) Se cierra con la cantidad empacada (`quantity_packed_final`) y queda listo para el PS?
- b) Se espera a que el nuevo lote tambien termine antes de despachar ambos en el mismo PS?
- c) Otra logica?

**Respuesta:** Se puede decir que existen 2 posibilidades:
- a) Se cierra con la cantidad empacada, El area de Materiales define si se abre un nuevo lote o se va asi porque ellos controlan los materiales para los lotes 
- b) Se puede esperar aque temrine el nuevo lote para cerrar la WO completa

**Impacto:** Afecta si el Observer marca el lote original como `packaging_status = 'completed'` inmediatamente cuando se crea el nuevo lote, o si debe esperar.

---

### P-04-07 [BAJA PRIORIDAD]: Notificaciones al area de Shipping

**Pregunta:** Cuando un lote queda listo para despacho, se necesita alguna notificacion activa (email, alerta en pantalla) o es suficiente con que aparezca en la cola de Shipping al entrar al modulo?

**Respuesta:** Todas las areas tendran acceso a ver la Lista de Envio y podran ver como va cada lote 

**Impacto:** Si se requieren notificaciones, implica integrar Laravel Notifications o un sistema de alertas en tiempo real (ej: Livewire polling o Laravel Echo).

---

### P-04-08 [BAJA PRIORIDAD]: Reversion de un lote ya marcado como "listo para despacho"

**Pregunta:** Si un lote fue marcado como `packaging_status = 'completed'` por error, existe un flujo para revertirlo a un estado anterior?

**Respuesta:** ya existe esta logica para el negicio ya este integrada en la Lista de Envio

**Impacto:** Define si se necesita un boton de "deshacer cierre de empaque" con permisos especiales y registro en AuditTrail.

---

## 9. Resumen Ejecutivo de Decisiones

| # | Decision | Estado | Responsable |
|---|---|---|---|
| D-04-01 | Mecanismo de transicion: Opcion B (trigger automatico al cierre de Materiales) | **PROPUESTO** | Frank / FlexCon |
| D-04-02 | Persistir `quantity_packed_final` en `lots` al cierre de empaque | **PROPUESTO** | Arquitecto |
| D-04-03 | El Packing Slip usa `quantity_packed_final` (real) como cantidad del item | **PENDIENTE P-04-02** | Frank / FlexCon |
| D-04-04 | Agregar `packaging_completed_at`, `packaging_completed_by` a `lots` | **PROPUESTO** | Arquitecto |
| D-04-05 | Agregar `packing_slip_id` a `lots` si no existe | **PENDIENTE VERIFICAR** | Arquitecto |
| D-04-06 | Vista "Cola de despacho" en Livewire usando scope `readyForShipping()` | **PROPUESTO** | Arquitecto |
| D-04-07 | Numero de WO externo (7 digitos) en `work_orders` | **PENDIENTE P-04-03** | Frank / FlexCon |

---

## 10. Referencias

- **Modelo Lot:** `C:/xampp/htdocs/flexcon-tracker/app/Models/Lot.php`
- **Modelo PackagingRecord:** `C:/xampp/htdocs/flexcon-tracker/app/Models/PackagingRecord.php`
- **Modelo WorkOrder:** `C:/xampp/htdocs/flexcon-tracker/app/Models/WorkOrder.php`
- **Migracion packaging_records:** `database/migrations/2026_02_27_070000_create_packaging_records_and_update_lots.php`
- **Migracion packaging_status en lots:** `database/migrations/2026_02_06_061234_add_packaging_and_final_quality_to_lots_table.php`
- **Migracion surplus_delivered:** `database/migrations/2026_03_06_062718_add_surplus_delivered_fields_to_lots_table.php`
- **Analisis previo Packing Slip:** `01_shipping_list_analysis.md`
- **Analisis previo Invoice:** `02_invoice_analysis.md`
- **Mapeo de campos:** `03_field_mapping_lista_envio_to_packing_slip.md`

---

*Documento generado el 2026-03-07. Version 1.0 - Basado en analisis del codigo fuente del proyecto FlexCon Tracker (modelos Lot, PackagingRecord, WorkOrder y migraciones) y los analisis previos de los documentos 01, 02 y 03 de esta serie.*
