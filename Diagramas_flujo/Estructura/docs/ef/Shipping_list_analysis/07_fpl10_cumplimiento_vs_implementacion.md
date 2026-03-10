# Analisis Tecnico: FPL-10 Cumplimiento vs Implementacion

**Fecha:** 2026-03-09
**Elaborado por:** Arquitecto de Software - FlexCon Tracker
**Version:** 1.0
**Proposito:** Comparar el documento Excel FPL-10 Shipping List 2025 (plantilla real utilizada con S.E.I.P., Inc.) contra el codigo implementado en el modulo de Packing Slip, identificar discrepancias, bugs y elementos pendientes, y generar un plan de correccion priorizado.
**Documentos previos:**
- `01_shipping_list_analysis.md` — Estructura del Packing Slip FPL-10
- `02_invoice_analysis.md` — Invoice FPL-12 y relacion 1:1 con el PS
- `03_field_mapping_lista_envio_to_packing_slip.md` — Mapeo de campos
- `04_empaque_to_shipping_list_transition.md` — Opciones de diseno
- `05_decisiones_confirmadas_y_plan_implementacion.md` — Plan de implementacion v1.0
- `06_impacto_respuestas_pendientes_y_ajustes.md` — Ajustes al plan

---

## 1. Estructura del Documento Excel FPL-10

El Excel FPL-10 "Shipping List 2025" utilizado en produccion con S.E.I.P., Inc. tiene la siguiente estructura:

### 1.1 Encabezado (Header del documento)

| Campo en el Excel | Descripcion |
|---|---|
| Logo FlexCon | Logotipo de la empresa en la parte superior izquierda |
| **DATE** | Fecha del documento (campo del header, distinto de la columna G de items) |
| **SHIPPING LIST** | Titulo del documento |
| Direccion FlexCon | Datos de la empresa emisora |
| **CUSTOMER** | Nombre del cliente (S.E.I.P., Inc.) |
| **CUSTOMER ADDRESS** | Direccion del cliente |

### 1.2 Tabla de Items — Columnas del FPL-10

| Col | Nombre de columna | Fuente de datos (Excel) | Notas |
|---|---|---|---|
| B | **Work Order** | Numero de WO del cliente | Formato: `W0` + external_wo_number + lot_seq (3 digitos) |
| C | **PO#** | Numero de Purchase Order | PO del cliente |
| D | **Item no** | Numero de item del catalogo S.E.I.P. | Formato esperado: `189-XXXXX` |
| E | **Description** | Descripcion del numero de parte | Ej: `STS H-ML-8` |
| F | **Quantity** | Cantidad real empacada y despachada | Numero entero |
| G | **Date** | Codigo de fecha del lote | Ej: `20250512A22` (formato `lot_number`) |
| H | **Label Spec** | Especificacion de etiqueta | Ej: `M83519/2-8`, `SAE AS81824/1-2` |

### 1.3 Logica de Subtotales

El documento Excel implementa **subtotales por PO** dentro de la tabla de items. Cuando cambia el valor de la columna C (PO#), el Excel inserta una fila de subtotal con:
- Suma parcial de la columna F (Quantity) para ese PO
- Etiqueta: `SUBTOTAL` o equivalente

### 1.4 Footer del Documento

El footer del FPL-10 contiene:
- **Cantidad de cajas** (boxes/cartons) del envio
- **Firma del emisor** (representante de FlexCon)
- **Firma del receptor** (representante de S.E.I.P., Inc. o carrier)
- Posiblemente numero de tracking del carrier

---

## 2. Analisis de Cumplimiento Campo por Campo

### Columna B — Work Order

**Campo en BD:** `packing_slip_items.wo_number_ps`
**Fuente:** `WorkOrder::buildWoCode(int $lotSeq)`

```
Formato implementado: 'W0' + external_wo_number + str_pad(lotSeq, 3, '0', STR_PAD_LEFT)
Ejemplo: 'W0' + '1980231' + '001' = 'W01980231001'
```

**Estado de cumplimiento:** ✅ CORRECTO

El formato `W0` + `external_wo_number` + secuencia de 3 digitos es identico al documento Excel FPL-10 real. El prefijo es `W0` (W + cero numerico), no `WO` (W + O mayuscula), lo cual esta correctamente documentado en el codigo fuente.

**Archivos relevantes:**
- `app/Models/WorkOrder.php` linea 104 — metodo `buildWoCode()`
- `app/Livewire/Admin/PackingSlips/PackingSlipCreate.php` linea 80 — llamada `$lot->workOrder->buildWoCode((int) $lot->lot_number)`
- `database/migrations/2026_03_08_100003_create_packing_slip_items_table.php` linea 53 — campo `wo_number_ps varchar(30)`

---

### Columna C — PO#

**Campo en BD:** No persiste como snapshot en `packing_slip_items`; se obtiene en tiempo real via relacion
**Fuente:** `$item->lot->workOrder->purchaseOrder->po_number`

**Estado de cumplimiento:** ✅ CORRECTO

El PO# se muestra correctamente en la vista show usando la cadena de relaciones eager-loaded. No se almacena como snapshot porque el PO no cambia una vez creado (es un campo de solo lectura en produccion).

**Vista show:**
- `resources/views/livewire/admin/packing-slips/packing-slip-show.blade.php` linea 138 — `$item->lot?->workOrder?->purchaseOrder?->po_number`

**Nota de riesgo:** Si un PO fuera eliminado con soft-delete, la cadena de relaciones devolveria null y el campo mostraria `-`. Esto es bajo riesgo en la practica dado que los POs no se eliminan mientras tienen WOs activos.

---

### Columna D — Item no

**Campo en BD:** No persiste como snapshot; se obtiene via `parts.item_number`
**Fuente:** `$item->lot->workOrder->purchaseOrder->part->item_number`

**Estado de cumplimiento:** ⚠️ DISCREPANCIA POTENCIAL — REQUIERE VERIFICACION

El codigo muestra `parts.item_number` que es el campo correcto segun el modelo. Sin embargo, se debe verificar que el catalogo de partes tiene ingresados valores con el formato `189-XXXXX` que usa S.E.I.P., Inc. como identificador de catalogo.

**El modelo `Part` tiene tres campos de identificacion:**
- `parts.number` — Numero de parte (ej: `M83519/2-8`, `SAE AS81824/1-2`)
- `parts.item_number` — Numero de item del catalogo S.E.I.P. (ej: `189-XXXXX`)
- `parts.description` — Descripcion textual

El campo `item_number` es semanticamente correcto para la columna D del Excel. El riesgo es que los registros existentes en la BD no tengan `item_number` poblado.

**Vista show:**
- `resources/views/livewire/admin/packing-slips/packing-slip-show.blade.php` linea 141 — `$item->lot?->workOrder?->purchaseOrder?->part?->item_number`

**Accion requerida:** Auditar la tabla `parts` para confirmar que `item_number` tiene el formato `189-XXXXX` en todos los registros activos que se usan con S.E.I.P., Inc.

---

### Columna E — Description

**Campo en BD:** No persiste como snapshot; se obtiene via `parts.number`
**Fuente:** `$item->lot->workOrder->purchaseOrder->part->number`

**Estado de cumplimiento:** ✅ CORRECTO — CONFIRMADO 2026-03-10

**Confirmacion real en BD:**
```
parts.number      = "H-M-2"      → campo correcto para la columna E (Description)
parts.item_number = "189-10178"  → campo correcto para la columna D (Item No)
parts.description = "189-10178"  → no se usa en el FPL-10
```

El campo `parts.number` contiene el numero de parte en formato Flexcon/cliente (ej: `H-M-2`, `H-ML-8`), que es exactamente el valor que aparece en la columna E "Description" del Excel FPL-10.

El campo `parts.description` contiene el mismo valor que `item_number` en los registros reales, por lo que NO es el campo correcto para la columna E.

**Vista show:** `packing-slip-show.blade.php` — `$item->lot?->workOrder?->purchaseOrder?->part?->number` ✅
**Vista create:** `packing-slip-create.blade.php` — `$lot->workOrder?->purchaseOrder?->part?->number` ✅

**Nota:** El Architect habia cambiado este campo a `parts->description` en la iteracion anterior. Se revertio a `parts->number` el 2026-03-10 al confirmar con datos reales de produccion que `parts.number` = `H-M-2` es el valor correcto del FPL-10.

---

### Columna F — Quantity

**Campo en BD:** `packing_slip_items.quantity_packed` (snapshot inmutable)
**Fuente en creacion:** `$lot->quantity_packed_final ?? $lot->quantity ?? 0`

**Estado de cumplimiento:** ✅ CORRECTO

La cantidad se persiste como snapshot inmutable al momento de crear el Packing Slip, tomada de `lots.quantity_packed_final` (cantidad real empacada calculada por el LotPackagingObserver al cierre del lote). El fallback a `lots.quantity` es un guard de seguridad para lotes sin dato de empaque.

**Archivos relevantes:**
- `database/migrations/2026_03_08_100003_create_packing_slip_items_table.php` linea 46 — campo `quantity_packed integer`
- `app/Livewire/Admin/PackingSlips/PackingSlipCreate.php` linea 84 — asignacion del snapshot

---

### Columna G — Date

**Campo en BD:** `packing_slip_items.lot_date_code` (varchar 20, nullable)
**Fuente en creacion (PackingSlipCreate):** `$this->dateSpecs[$lot->id] ?? null` — ingreso manual del usuario
**Fuente en edicion (PackingSlipEdit):** `$lot->receipt_date?->format('Y-m-d') ?? null` — BUG: usa `receipt_date`

**Estado de cumplimiento:** ⚠️ BUG EN PackingSlipEdit — REQUIERE CORRECCION INMEDIATA

**Analisis detallado:**

El componente `PackingSlipCreate.php` maneja correctamente el campo `Date`: lo inicializa como cadena vacia (`$this->dateSpecs[$lot->id] = ''`) y permite que el usuario lo ingrese manualmente antes de crear el PS. El valor se guarda como null si esta vacio.

Sin embargo, `PackingSlipEdit.php` tiene una inconsistencia en linea 124:

```php
// PackingSlipEdit.php, linea 124 — CODIGO CON BUG
'lot_date_code' => $lot->receipt_date?->format('Y-m-d') ?? null,
```

Este codigo inicializa `lot_date_code` usando `lots.receipt_date` (fecha de recepcion del lote), que **no es el campo esperado por S.E.I.P., Inc.** La decision provisional D-06-01 establece que el valor debe ser `lots.lot_number` hasta confirmar con el cliente, no `receipt_date`.

Ademas, este codigo solo se ejecuta cuando se **agrega un lote nuevo** a un PS existente en edicion (el bloque `else` del condicional en linea 111-128). Los items ya existentes en el PS no se ven afectados.

**Archivos relevantes:**
- `app/Livewire/Admin/PackingSlips/PackingSlipEdit.php` linea 124 — BUG: `$lot->receipt_date?->format('Y-m-d')`
- `app/Livewire/Admin/PackingSlips/PackingSlipCreate.php` linea 86 — CORRECTO: `$this->dateSpecs[$lot->id] ?? null`
- `database/migrations/2026_03_08_100003_create_packing_slip_items_table.php` linea 61 — campo `lot_date_code varchar(20)` nullable

**Correccion requerida en PackingSlipEdit.php linea 124:**
```php
// ANTES (con bug):
'lot_date_code' => $lot->receipt_date?->format('Y-m-d') ?? null,

// DESPUES (correccion):
'lot_date_code' => $lot->lot_number ?? null,
```

**Nota:** La edicion inline del campo Date desde la vista show (`PackingSlipShow`) funciona correctamente. El metodo `updateItemDate()` en `PackingSlipShow.php` linea 56 acepta texto libre y lo guarda directamente.

---

### Columna H — Label Spec

**Campo en BD:** `packing_slip_items.label_spec` (varchar 50, nullable)
**Fuente:** Ingreso manual del usuario en el wizard de creacion y edicion

**Estado de cumplimiento:** ✅ CORRECTO

El campo `label_spec` se maneja correctamente como texto libre ingresado por el usuario. La decision D-06-02 confirma que no hay vinculo con la tabla `parts` en esta fase. El campo es opcional (nullable) y editable inline desde la vista show mientras el PS no este en estado `shipped`.

**Archivos relevantes:**
- `app/Livewire/Admin/PackingSlips/PackingSlipCreate.php` lineas 15, 25-26, 87 — declaracion, validacion y asignacion
- `app/Livewire/Admin/PackingSlips/PackingSlipEdit.php` lineas 15, 33, 116 — carga, actualizacion de items existentes
- `app/Livewire/Admin/PackingSlips/PackingSlipShow.php` lineas 65-72 — edicion inline
- `resources/views/livewire/admin/packing-slips/packing-slip-show.blade.php` lineas 169-185 — UI de edicion inline con Alpine.js

---

## 3. Campos del Header del Excel vs Implementacion

| Campo Header FPL-10 | En BD | En UI | Estado |
|---|---|---|---|
| **DATE** (fecha del documento) | ❌ No existe campo `document_date` en `packing_slips` | ❌ No se muestra | ❌ NO IMPLEMENTADO |
| **CUSTOMER** | No en BD directa — inferable via `purchase_orders -> part -> ...` pero no hay tabla de clientes | Hardcoded o no mostrado | ⚠️ PARCIAL |
| **CUSTOMER ADDRESS** | No en BD | No en UI | ❌ NO IMPLEMENTADO |
| Logo FlexCon | N/A (asset estatico) | N/A (para PDF Fase 3) | ⏳ PENDIENTE FASE 3 |
| Numero correlativo del PS | `packing_slips.ps_number` formato `PS-YYYY-NNNN` | ✅ Mostrado en header | ✅ CORRECTO |
| Creado por | `packing_slips.created_by` -> `users.name` | ✅ Mostrado | ✅ CORRECTO |
| Fecha de creacion | `packing_slips.created_at` | ✅ Mostrado | ✅ CORRECTO |
| Fecha de despacho | `packing_slips.shipped_at` | ✅ Mostrado si estado = shipped | ✅ CORRECTO |

---

## 4. Funcionalidades de Estructura del Excel No Implementadas

| Elemento FPL-10 | Estado | Notas |
|---|---|---|
| **Subtotales por PO** (fila de suma parcial cuando cambia el PO#) | ❌ NO IMPLEMENTADO | Solo existe el grand total en el `<tfoot>` de la tabla. Ver seccion 5 de este documento. |
| **DATE del documento** (campo del header) | ❌ NO IMPLEMENTADO | Falta campo `document_date` en `packing_slips`. Ver seccion 3. |
| **Footer: cantidad de cajas** | ❌ NO IMPLEMENTADO | No existe campo en BD ni UI. |
| **Footer: firma del emisor** | ❌ NO IMPLEMENTADO | No existe en el modulo actual. |
| **Footer: firma del receptor** | ❌ NO IMPLEMENTADO | No existe en el modulo actual. |
| **PDF generado del FPL-10** | ❌ NO IMPLEMENTADO | Fase 3 del plan de implementacion. |
| **Enlace Sidebar a "Cola de Shipping"** | ❌ NO IMPLEMENTADO | La ruta `admin.shipping.queue` existe pero no hay enlace en el sidebar. |

---

## 5. Resumen Ejecutivo

### 5.1 Implementados Correctamente (sin accion requerida)

| # | Campo / Funcionalidad | Confianza |
|---|---|---|
| 1 | `wo_number_ps` — Formato W0+external_wo_number+lot_seq_3d | Alta |
| 2 | `po_number` — Via relacion purchaseOrder.po_number | Alta |
| 3 | `quantity_packed` — Snapshot inmutable desde `lots.quantity_packed_final` | Alta |
| 4 | `label_spec` — Ingreso manual, campo nullable, edicion inline | Alta |
| 5 | Ciclo de vida del PS (draft -> confirmed -> shipped) | Alta |
| 6 | Validacion de `external_wo_number` al crear/editar PS | Alta |
| 7 | Edicion inline de `lot_date_code` desde PackingSlipShow | Alta |
| 8 | Grand total de piezas en `<tfoot>` | Alta |

### 5.2 Discrepancias que Requieren Atencion

| # | Problema | Severidad | Archivo | Linea |
|---|---|---|---|---|
| D-1 | **BUG:** `PackingSlipEdit` inicializa `lot_date_code` con `receipt_date` en lugar de `lot_number` | Alta | `PackingSlipEdit.php` | 124 |
| D-2 | ~~**Semantica:** La columna E usa `parts->number` pero el Excel muestra descripcion comercial. Verificar si debe ser `parts->description`~~ **RESUELTO 2026-03-10**: `parts.number` = `H-M-2` es correcto. Se revertio el cambio del Architect. | ✅ | `packing-slip-show.blade.php` | 144 |
| D-3 | **Datos:** `parts.item_number` debe contener el formato `189-XXXXX`. Requiere auditoria de datos en BD | Media | BD tabla `parts` | N/A |

### 5.3 No Implementados (pendientes de fase futura)

| # | Elemento | Fase de implementacion prevista |
|---|---|---|
| N-1 | `document_date` en header del PS (campo `DATE` del FPL-10) | Fase 2 (requiere migracion) |
| N-2 | Subtotales por PO en la tabla de items | Fase 3 (logica en PDF) |
| N-3 | Footer: cajas, firmas | Fase 3 (en generacion de PDF) |
| N-4 | PDF generado del FPL-10 (documento imprimible/descargable) | Fase 3 |
| N-5 | Enlace en sidebar a "Cola de Shipping" (`admin.shipping.queue`) | Inmediato (cambio menor) |
| N-6 | Precio unitario en `packing_slip_items` para Invoice FPL-12 | Fase 3 (campos ya en BD, logica pendiente) |

---

## 6. Plan de Correccion Priorizado

### CRITICO — Debe resolverse antes de usar en produccion

#### C-1: BUG en PackingSlipEdit — lot_date_code con receipt_date

**Archivo:** `app/Livewire/Admin/PackingSlips/PackingSlipEdit.php`
**Linea:** 124
**Impacto:** Cuando se agrega un lote nuevo a un PS en estado `draft` via edicion, el campo `lot_date_code` se inicializa con la fecha de recepcion del lote (`receipt_date`) en formato `Y-m-d` en lugar del `lot_number` que es el valor provisional correcto segun decision D-06-01.

**Correccion:**
```php
// Linea 124 — ANTES (bug):
'lot_date_code'   => $lot->receipt_date?->format('Y-m-d') ?? null,

// Linea 124 — DESPUES (correccion):
'lot_date_code'   => $lot->lot_number ?? null,
```

---

### ALTO — Debe resolverse antes del primer envio real con FPL-10

#### A-1: Verificar semantica de la columna E (Description)

**Archivo:** `resources/views/livewire/admin/packing-slips/packing-slip-show.blade.php`
**Linea:** 144
**Problema:** El codigo muestra `part->number` (numero estandar militar como `M83519/2-8`) pero el Excel FPL-10 muestra en la columna E la descripcion comercial (como `STS H-ML-8`).

**Accion:** Confirmar con Frank / S.E.I.P., Inc. si la columna E del FPL-10 debe mostrar:
- `parts.number` (numero de parte estandar: `M83519/2-8`)
- `parts.description` (descripcion comercial: `STS H-ML-8`)

Si la respuesta es `parts.description`, actualizar la vista show y el futuro snapshot del PDF.

#### A-2: Auditar campo `parts.item_number` en la BD

**Tabla:** `parts`
**Campo:** `item_number`
**Problema:** No hay garantia de que todos los registros activos de partes tengan el campo `item_number` con el formato `189-XXXXX` que usa S.E.I.P., Inc. Si el campo esta vacio, la columna D del FPL-10 aparecera como `-`.

**Accion:** Ejecutar una consulta de auditoria y poblar los `item_number` faltantes.

---

### MEDIO — Debe resolverse antes de Fase 3 (generacion de PDF)

#### M-1: Agregar campo `document_date` a la tabla `packing_slips`

**Componente afectado:** Modelo `PackingSlip`, migracion, formulario de creacion
**Problema:** El header del FPL-10 tiene un campo `DATE` que es la fecha del documento de envio. Actualmente no existe este campo en la tabla `packing_slips`. El campo `created_at` no es semanticamente equivalente porque el documento puede tener una fecha de emision diferente a la fecha de registro en el sistema.

**Accion:** Crear migracion `add_document_date_to_packing_slips_table` con campo `document_date date nullable`. Agregar al formulario de creacion y edicion. En la vista show, mostrar como campo editable.

#### M-2: Agregar enlace al sidebar para "Cola de Shipping"

**Archivo:** `resources/views/components/layouts/admin/sidebar.blade.php`
**Linea:** 102-112 (grupo `Empaques`)
**Problema:** La ruta `admin.shipping.queue` (componente `ShippingQueue`) existe y esta funcional en `routes/admin.php` linea 216, pero no hay un enlace en el sidebar que permita acceder a ella desde la navegacion principal.

**Accion:** Agregar item al grupo `Empaques` del sidebar:
```blade
<flux:navlist.item icon="queue-list" :href="route('admin.shipping.queue')"
    :current="request()->routeIs('admin.shipping.queue')" wire:navigate>{{ __('Cola de Shipping') }}
</flux:navlist.item>
```

---

### BAJO — Puede resolverse en Fase 3 o posterior

#### B-1: Subtotales por PO en tabla de items

**Descripcion:** El FPL-10 tiene filas de subtotal cuando cambia el PO#. La vista show actual solo tiene un grand total en el `<tfoot>`. Los subtotales son importantes para documentos con multiples POs en el mismo PS.

**Accion:** Implementar en la vista show (y en el PDF de Fase 3) una agrupacion por `purchaseOrder.po_number` con subtotal de `quantity_packed` por grupo.

#### B-2: Footer del documento FPL-10 (cajas y firmas)

**Descripcion:** El footer del FPL-10 requiere campos de cantidad de cajas y espacios de firma. Estos son propios del documento impreso y no requieren persistencia en BD mas alla de un campo `box_count integer nullable` en `packing_slips`.

**Accion:** Agregar campo `box_count` en la misma migracion de `document_date` (punto M-1). Las firmas son parte del PDF y no requieren modelo en BD.

#### B-3: Campo `label_spec` vinculado a `parts` en fase futura

**Descripcion:** Actualmente `label_spec` es ingreso manual. Cuando el cliente confirme el catalogo, se debera agregar `label_spec varchar(50)` a la tabla `parts` y pre-llenar el campo en el wizard.

**Accion:** Sin implementacion en esta fase. Documento de migracion en el documento 06, seccion 2.2.

---

## 7. Lista de Archivos Clave

| Archivo | Descripcion | Lineas relevantes |
|---|---|---|
| `app/Livewire/Admin/PackingSlips/PackingSlipCreate.php` | Componente de creacion del PS | L80: buildWoCode; L84: snapshot quantity; L86: dateSpecs (correcto); L87: labelSpecs |
| `app/Livewire/Admin/PackingSlips/PackingSlipEdit.php` | Componente de edicion del PS | **L124: BUG lot_date_code = receipt_date** |
| `app/Livewire/Admin/PackingSlips/PackingSlipShow.php` | Componente de visualizacion | L56-72: edicion inline Date y Label Spec |
| `app/Livewire/Admin/PackingSlips/PackingSlipList.php` | Listado de PS | N/A |
| `resources/views/livewire/admin/packing-slips/packing-slip-show.blade.php` | Vista de detalle del PS | L122-129: headers de tabla (7 columnas); L138: po_number; L141: item_number; **L144: part->number (verificar vs description)**; L147: quantity_packed; L151: lot_date_code editable; L170: label_spec editable |
| `app/Models/WorkOrder.php` | Modelo WO | L104-111: `buildWoCode()` — formato W0+external_wo_number+lot_seq |
| `app/Models/PackingSlip.php` | Modelo PS | L62-75: `generatePsNumber()` — formato PS-YYYY-NNNN |
| `app/Models/PackingSlipItem.php` | Modelo item del PS | Snapshot de campos del FPL-10 |
| `app/Models/Part.php` | Modelo de parte | L17-23: campos `number`, `item_number`, `description` |
| `database/migrations/2026_03_08_100003_create_packing_slip_items_table.php` | Esquema de `packing_slip_items` | L46: quantity_packed; L53: wo_number_ps; L61: lot_date_code; L68: label_spec; L79: unit_price (Fase 3); L84: price_tier_id (Fase 3); L94: price_source (Fase 3) |
| `database/migrations/2026_03_08_100002_create_packing_slips_table.php` | Esquema de `packing_slips` | Campos principales del PS |
| `resources/views/components/layouts/admin/sidebar.blade.php` | Sidebar de navegacion | L109-111: enlace a Packing Slips; **Falta enlace a shipping-queue** |
| `routes/admin.php` | Definicion de rutas | L216: ruta `admin.shipping.queue` |
| `app/Livewire/Admin/Shipping/ShippingQueue.php` | Cola de Shipping (componente) | Componente existente sin enlace en sidebar |

---

## 8. Tabla de Estado Global

| ID | Campo / Elemento FPL-10 | Estado | Prioridad | Issue |
|---|---|---|---|---|
| F-01 | Columna B: Work Order (`wo_number_ps`) | ✅ CORRECTO | — | — |
| F-02 | Columna C: PO# (via relacion) | ✅ CORRECTO | — | — |
| F-03 | Columna D: Item no (`parts.item_number`) | ⚠️ VERIFICAR | Alto | Auditar datos en BD |
| F-04 | Columna E: Description (`parts.number`) | ✅ CORRECTO | — | Confirmado 2026-03-10: `parts.number` = `H-M-2` es el valor correcto |
| F-05 | Columna F: Quantity (snapshot `quantity_packed`) | ✅ CORRECTO | — | — |
| F-06 | Columna G: Date en PackingSlipCreate (`dateSpecs`) | ✅ CORRECTO | — | — |
| F-07 | Columna G: Date en PackingSlipEdit (nuevos items) | ❌ BUG | **Critico** | Linea 124: usa `receipt_date` |
| F-08 | Columna G: Date edicion inline en PackingSlipShow | ✅ CORRECTO | — | — |
| F-09 | Columna H: Label Spec (ingreso manual) | ✅ CORRECTO | — | — |
| H-01 | Header: DATE del documento | ❌ NO IMPLEMENTADO | Medio | Falta campo `document_date` en BD |
| H-02 | Header: ps_number correlativo | ✅ CORRECTO | — | — |
| H-03 | Header: CUSTOMER / CUSTOMER ADDRESS | ❌ NO IMPLEMENTADO | Bajo | Solo en PDF Fase 3 |
| S-01 | Subtotales por PO en tabla de items | ❌ NO IMPLEMENTADO | Bajo | Implementar en Fase 3 |
| S-02 | Grand total de piezas | ✅ CORRECTO | — | Solo total global, sin subtotales |
| FT-01 | Footer: cantidad de cajas | ❌ NO IMPLEMENTADO | Bajo | Campo `box_count` en Fase 3 |
| FT-02 | Footer: firmas | ❌ NO IMPLEMENTADO | Bajo | Solo en PDF Fase 3 |
| UI-01 | Enlace sidebar a Cola de Shipping | ❌ NO IMPLEMENTADO | Medio | Agregar en sidebar.blade.php |
| UI-02 | PDF generado del FPL-10 | ❌ NO IMPLEMENTADO | — | Fase 3 completa |

---

## 9. Preguntas Abiertas que Derivan de Este Analisis

| ID | Pregunta | Para quien | Impacto si no se resuelve |
|---|---|---|---|
| P-07-01 | ~~Columna E del FPL-10: confirmar si S.E.I.P., Inc. espera `parts.number` o `parts.description`~~ **RESUELTO 2026-03-10**: `parts.number` = `H-M-2` confirmado con datos reales de BD. | — | — |
| P-07-02 | Columna D del FPL-10: confirmar que todos los registros de `parts` activos tienen `item_number` en formato `189-XXXXX` | Equipo de datos / Admin | La columna D aparece como `-` en los PS generados |
| P-07-03 | Columna G del FPL-10 (campo `DATE`): confirmar con S.E.I.P., Inc. si el formato `lot_number` (`YYMMDDXNN`) es aceptable o si esperan otro formato. Ver P-06-01 del documento 06, aun pendiente. | Frank / S.E.I.P., Inc. | No bloquea Fase 1/2; bloquea lanzamiento de PDF en Fase 3 |

---

## 11. Cambios Sesion 2026-03-10 — Edicion de Date y Label Spec

### 11.1 Problema identificado

- La vista lista (`/admin/packing-slips`) mostraba el boton "Editar" unicamente cuando el PS estaba en estado `draft`. Para PSs en estado `confirmed` o `shipped`, la columna Acciones solo tenia el enlace "Ver", sin forma rapida de acceder a la edicion de los campos de items.
- Los campos Date (`lot_date_code`) y Label Spec (`label_spec`) eran `readonly` en la vista show cuando el PS estaba en estado `shipped`, tanto a nivel de blade (bloque `@if (!$packingSlip->isShipped())`) como a nivel del componente PHP (guard `if ($this->packingSlip->isShipped()) return;` en ambos metodos de actualizacion).

### 11.2 Cambios implementados

| Archivo | Cambio |
|---|---|
| `resources/views/livewire/admin/packing-slips/packing-slip-list.blade.php` | Se agrego boton "Editar" (con icono de lapiz) visible para todos los estados del PS. El boton navega a la ruta `admin.packing-slips.show` donde el inline editing esta disponible. El boton "Eliminar" sigue restringido a estado `draft`. |
| `resources/views/livewire/admin/packing-slips/packing-slip-show.blade.php` | Se elimino el bloque `@if (!$packingSlip->isShipped()) ... @else <input readonly> @endif` para las celdas Date y Label Spec. Ambas celdas ahora muestran siempre el modo inline editable (span clickeable + input al editar), independientemente del estado del PS. |
| `app/Livewire/Admin/PackingSlips/PackingSlipShow.php` | Se elimino la linea `if ($this->packingSlip->isShipped()) return;` de los metodos `updateItemDate()` y `updateItemLabelSpec()`. Ambos metodos ahora persisten cambios en BD sin importar el estado del PS. |

### 11.3 Logica de negocio justificada

Date y Label Spec son datos operativos de produccion que pueden necesitar correccion posterior al despacho. Casos de uso reales:

- **Date (`lot_date_code`):** El codigo de fecha del lote puede haberse capturado mal (ej: `250512A22` en lugar de `250512B22`). Esta correccion es necesaria antes de generar el PDF del FPL-10 para el cliente.
- **Label Spec:** La especificacion de etiqueta puede necesitar ajuste si el cliente solicita un cambio de estandar o se detecto un error de captura.

Los demas campos del PS y sus items (Work Order, PO#, Quantity) permanecen inmutables porque son datos de negocio que afectan el inventario y la facturacion. Solo los campos descriptivos que el usuario captura manualmente se permiten corregir post-despacho.

### 11.4 Nota sobre la vista Edit

La vista `packing-slip-edit.blade.php` y su componente `PackingSlipEdit.php` permanecen sin cambios. Esta vista esta disenada para el flujo de gestion de lotes (agregar/quitar lotes de un PS en estado `draft`) y hace un redirect inmediato si el PS no es `draft`. No es la via correcta para editar Date y Label Spec en PSs despachados; esa funcion recae en el inline editing de la vista show.

---

## 12. Cambios Sesion 2026-03-10 — Correccion de Logica de Eliminacion de Packing Slip

### 12.1 Problema identificado / Motivacion

Al analizar el flujo de eliminacion de Packing Slips en estado `draft`, se identifico un bug critico de integridad de datos:

El metodo `delete()` en `PackingSlipList.php` ejecutaba directamente `$packingSlip->delete()`, que activa el **SoftDelete** del modelo `PackingSlip` (la tabla tiene columna `deleted_at`). Sin embargo, la tabla `packing_slip_items` **no tiene SoftDeletes** y tampoco se activa el cascade de BD en una operacion de soft-delete, ya que SoftDelete es una actualizacion de la columna `deleted_at` (UPDATE SQL), no un DELETE SQL real.

**Consecuencia del bug sin correccion:**

1. El PS quedaba soft-deleted (invisible en la app) pero sus registros en `packing_slip_items` permanecian en BD.
2. La tabla `packing_slip_items` tiene la constraint `lot_id UNIQUE`. Los lotes referenciados por esos items huerfanos quedaban permanentemente bloqueados: el scope `scopeReadyForShipping` del modelo `Lot` usa `whereDoesntHave('packingSlipItem')` para filtrar lotes disponibles, por lo tanto seguian excluidos de la cola de shipping.
3. Los lotes nunca podrian ser asignados a un nuevo Packing Slip sin intervencion directa en la BD.

### 12.2 Analisis del esquema real (verificado antes de implementar)

| Elemento | Esquema |
|---|---|
| `packing_slips.deleted_at` | SoftDeletes activado en el modelo `PackingSlip` |
| `packing_slip_items` FK a `packing_slips` | `onDelete('cascade')` — solo actua con DELETE SQL real, no con SoftDelete |
| `packing_slip_items.lot_id` | `UNIQUE` constraint — bloquea la reasignacion del lote |
| Vinculo lote — PS | Indirecto via `packing_slip_items`. El modelo `Lot` no tiene campo `packing_slip_id`. |
| Liberacion de lote | Solo ocurre cuando desaparece el registro en `packing_slip_items` para ese `lot_id` |
| `Lot::scopeReadyForShipping` | `whereDoesntHave('packingSlipItem')` — el lote solo aparece disponible si no tiene item en ningun PS |

### 12.3 Cambios implementados

| Archivo | Tipo de cambio | Descripcion |
|---|---|---|
| `app/Livewire/Admin/PackingSlips/PackingSlipList.php` | Correccion de bug | Se agrego `$packingSlip->items()->delete()` antes de `$packingSlip->delete()` en el metodo `delete()` |

**Codigo antes (con bug):**
```php
$packingSlip->delete();
```

**Codigo despues (corregido):**
```php
// Eliminar explicitamente los items antes del soft-delete del PS.
// Esto libera los lotes (packing_slip_items.lot_id UNIQUE) para que
// puedan ser asignados a un nuevo Packing Slip. El cascade de BD no
// se activa con SoftDeletes ya que no es un DELETE SQL real.
$packingSlip->items()->delete();

$packingSlip->delete();
```

### 12.4 Estado de la UI (verificado con Playwright)

La vista lista (`/admin/packing-slips`) ya tenia implementado correctamente:
- Boton "Eliminar" con icono de lapiz visible solo cuando `$ps->isDraft()` (condicional `@if ($ps->isDraft())`)
- Modal de confirmacion con Alpine.js (`@if ($confirmingDeletion)`) que muestra el mensaje: "Esta accion no se puede deshacer y los lotes quedaran disponibles nuevamente."
- Botones "Eliminar" y "Cancelar" en el modal, conectados via `wire:click="delete"` y `wire:click="cancelDeletion"`
- Guard de doble validacion: tanto en `confirmDeletion()` como en `delete()` se verifica `$packingSlip->isDraft()` antes de proceder

No fue necesario modificar la vista Blade ni agregar ningun elemento nuevo a la UI.

### 12.5 Logica de negocio justificada

**Por que solo se permite eliminar en estado `draft`:**

El ciclo de vida `draft -> confirmed -> shipped` representa compromisos de negocio crecientes:
- `confirmed`: el PS ha sido revisado y aprobado. Los lotes estan comprometidos para ese despacho.
- `shipped`: el PS es un documento historico de auditoria que registro un despacho real a S.E.I.P., Inc. Eliminarlo boraria evidencia de una operacion completada.

Solo `draft` es un borrador de trabajo sin compromisos formales, por lo tanto es el unico estado donde la eliminacion tiene sentido operativo.

**Por que se eliminan los items explicitamente y no se confía en el cascade:**

Laravel SoftDeletes transforma el `Model::delete()` en un `UPDATE SET deleted_at = NOW()`. La constraint `onDelete('cascade')` de la FK `packing_slip_id` en `packing_slip_items` solo se activa ante un `DELETE FROM packing_slips WHERE id = ?` a nivel SQL. Al usar SoftDeletes, ese DELETE SQL nunca ocurre, por lo que el cascade de BD es inerte en este flujo.

**Comportamiento de los lotes al eliminar un PS:**

1. Se llama `$packingSlip->items()->delete()` — esto ejecuta `DELETE FROM packing_slip_items WHERE packing_slip_id = ?`. Los registros se eliminan permanentemente (sin SoftDelete en esa tabla).
2. La constraint `UNIQUE` en `packing_slip_items.lot_id` queda liberada para cada `lot_id` que estaba en esos items.
3. El scope `Lot::scopeReadyForShipping` usa `whereDoesntHave('packingSlipItem')`. Al no existir el item, el lote vuelve a aparecer en la cola de shipping disponible para ser incluido en un nuevo PS.
4. El campo `lots.ready_for_shipping` permanece `true` — el lote sigue listo para despacho, solo se libera de la asignacion al PS eliminado.
5. Se llama `$packingSlip->delete()` — SoftDelete del PS. El registro permanece en BD con `deleted_at` poblado, permitiendo auditoria si fuera necesario.

### 12.6 Resumen de archivos modificados

| Archivo | Accion | Descripcion |
|---|---|---|
| `app/Livewire/Admin/PackingSlips/PackingSlipList.php` | Modificado | Agrega `$packingSlip->items()->delete()` antes del soft-delete del PS en el metodo `delete()` |

---

## 13. Cambios Sesion 2026-03-09 — Nuevos Estados "Pendiente" y "Cerrado" + Selector de Estado Libre

### 13.1 Motivacion / Problema

El ciclo de vida original del Packing Slip era estrictamente unidireccional:

```
draft --> confirmed --> shipped
```

Este flujo forzado impide que el usuario retroceda un PS a un estado anterior o lo marque con estados intermedios que reflejen la realidad operativa. En produccion surgen casos como:

- Un PS marcado como `shipped` por error que debe volver a `confirmed` para correccion.
- Un PS en espera de aprobacion interna que el equipo quiere distinguir de los borradores activos.
- Un PS completado y archivado que debe diferenciarse visualmente de los PSs activos.

La solucion implementada reemplaza los botones de transicion condicional ("Confirmar" y "Marcar como Despachado") por un selector de estado universal que permite cambiar libremente entre cualquiera de los 5 estados disponibles.

---

### 13.2 Nuevos estados agregados

| Constante | Valor en BD | Etiqueta UI | Color badge | Descripcion de negocio |
|---|---|---|---|---|
| `STATUS_PENDING` | `pending` | Pendiente | naranja (`orange`) | PS en espera de aprobacion, revision o documentacion complementaria. Util para PSs que no son borradores activos pero tampoco estan confirmados. |
| `STATUS_CLOSED` | `closed` | Cerrado | gris (`gray`) | PS archivado o cancelado. Representa el fin del ciclo de vida sin necesidad de ser despachado. Util para PSs que se cancelaron o se archivaron por decision operativa. |

**Nota:** No se creo migracion de base de datos. La columna `packing_slips.status` es `varchar` y acepta cualquier string valido. Los nuevos estados son compatibles con todos los PSs existentes que tienen `draft`, `confirmed` o `shipped` — no se altera ninguna fila existente.

---

### 13.3 Nuevo flujo de estados

El flujo ya no es unidireccional. El usuario puede transitar entre cualquier par de estados:

```
draft <--> confirmed <--> shipped <--> pending <--> closed
   \           \             \            \
    +-----------+-------------+------------+--> cualquier estado
```

**Flujo libre:** El selector muestra los 5 estados y el usuario elige el destino. No hay validacion de transicion en el servidor — cualquier combinacion es valida.

**Excepcion: logica especial para el estado `shipped`:**

- Al cambiar **A** `shipped`: se registra automaticamente `shipped_at = now()` y `shipped_by = Auth::id()`. Esto preserva la trazabilidad del despacho.
- Al cambiar **DESDE** `shipped` hacia cualquier otro estado: se limpian `shipped_at = null` y `shipped_by = null`. Esto evita que el panel de informacion muestre datos de despacho incorrectos en un PS que ya no esta en estado despachado.

La logica de eliminacion permanece sin cambios: **solo los PSs en estado `draft` pueden ser eliminados**.

---

### 13.4 Cambios implementados por archivo

#### `app/Models/PackingSlip.php`

| Elemento | Cambio |
|---|---|
| Constante `STATUS_PENDING` | Agregada: `public const STATUS_PENDING = 'pending'` |
| Constante `STATUS_CLOSED` | Agregada: `public const STATUS_CLOSED = 'closed'` |
| Array `STATUSES` | Agregadas las entradas `'pending' => 'Pendiente'` y `'closed' => 'Cerrado'` |
| Scope `scopePending()` | Agregado: `return $query->where('status', self::STATUS_PENDING)` |
| Scope `scopeClosed()` | Agregado: `return $query->where('status', self::STATUS_CLOSED)` |
| Helper `isPending()` | Agregado: `return $this->status === self::STATUS_PENDING` |
| Helper `isClosed()` | Agregado: `return $this->status === self::STATUS_CLOSED` |
| `getStatusColorAttribute()` | Agregados los cases `pending => 'orange'` y `closed => 'gray'` |

#### `app/Livewire/Admin/PackingSlips/PackingSlipShow.php`

| Elemento | Cambio |
|---|---|
| Propiedad `$selectedStatus` | Agregada: `public string $selectedStatus = ''` |
| `mount()` | Se inicializa `$this->selectedStatus = $this->packingSlip->status` |
| Metodo `confirm()` | **Eliminado** (reemplazado por `updateStatus()`) |
| Metodo `markAsShipped()` | **Eliminado** (reemplazado por `updateStatus()`) |
| Metodo `updateStatus()` | **Nuevo**: valida el estado contra `array_keys(PackingSlip::STATUSES)`, actualiza el PS, gestiona la logica especial de `shipped_at`/`shipped_by`, refresca el modelo y emite flash de exito. |

#### `resources/views/livewire/admin/packing-slips/packing-slip-show.blade.php`

| Elemento | Cambio |
|---|---|
| Botones "Confirmar" y "Marcar como Despachado" | **Eliminados**. Eran condicionales por estado. |
| Selector de estado + boton "Guardar estado" | **Agregado**: visible siempre en el header. `<select wire:model="selectedStatus">` itera `PackingSlip::STATUSES`. El boton llama `wire:click="updateStatus"`. |
| Boton "Editar lotes" | Se mantiene pero ahora solo aparece en estado `draft` (sin cambio semantico, ajuste de texto de "Editar" a "Editar lotes"). |
| Badge de estado en el header | Actualizado el `match()` para incluir `'pending'` (orange) y `'closed'` (gray). |

#### `app/Livewire/Admin/PackingSlips/PackingSlipList.php`

| Elemento | Cambio |
|---|---|
| Query de filtro por estado | Agregados los bloques `elseif ($this->filterStatus === 'pending') $query->pending()` y `elseif ($this->filterStatus === 'closed') $query->closed()` |
| Array `$stats` en `render()` | Agregadas las claves `'pending' => PackingSlip::pending()->count()` y `'closed' => PackingSlip::closed()->count()` |

#### `resources/views/livewire/admin/packing-slips/packing-slip-list.blade.php`

| Elemento | Cambio |
|---|---|
| `<select>` de filtro por estado | Agregadas las `<option>` para `pending` y `closed` |
| Match de `$badgeClasses` en la tabla | Agregados los cases `'pending'` (orange) y `'closed'` (gray) |
| Stats cards | La grilla cambio de `md:grid-cols-4` a `md:grid-cols-3 lg:grid-cols-6`. Se agregaron 2 cards nuevas: "Pendiente" (icono de reloj, fondo naranja) y "Cerrado" (icono de badge verificado, fondo gris). |

---

### 13.5 Archivos NO modificados

| Archivo | Razon |
|---|---|
| `app/Livewire/Admin/PackingSlips/PackingSlipEdit.php` | El Edit solo opera en estado `draft` y hace redirect si el PS no es draft. El selector de estado no aplica aqui — la vista Edit es exclusiva para gestion de lotes. |
| `resources/views/livewire/admin/packing-slips/packing-slip-edit.blade.php` | Misma razon. |
| `database/migrations/*` | No se requiere migracion. La columna `status varchar` ya acepta los nuevos valores. |
| `app/Livewire/Admin/PackingSlips/PackingSlipCreate.php` | El Create siempre crea en estado `draft`. Sin cambios. |

---

### 13.6 Retrocompatibilidad

Todos los PSs existentes con estado `draft`, `confirmed` o `shipped` continuan funcionando sin modificacion. Los helpers `isDraft()`, `isConfirmed()` e `isShipped()` no fueron alterados. El badge `default => 'bg-gray-100 text-gray-800'` del match sigue cubriendo cualquier valor desconocido como fallback.

---

## 10. Referencias

- **Documento 01:** `01_shipping_list_analysis.md`
- **Documento 02:** `02_invoice_analysis.md`
- **Documento 03:** `03_field_mapping_lista_envio_to_packing_slip.md`
- **Documento 04:** `04_empaque_to_shipping_list_transition.md`
- **Documento 05:** `05_decisiones_confirmadas_y_plan_implementacion.md`
- **Documento 06:** `06_impacto_respuestas_pendientes_y_ajustes.md`
- **Migracion PS:** `database/migrations/2026_03_08_100002_create_packing_slips_table.php`
- **Migracion PS Items:** `database/migrations/2026_03_08_100003_create_packing_slip_items_table.php`
- **Componente Create:** `app/Livewire/Admin/PackingSlips/PackingSlipCreate.php`
- **Componente Edit:** `app/Livewire/Admin/PackingSlips/PackingSlipEdit.php`
- **Componente Show:** `app/Livewire/Admin/PackingSlips/PackingSlipShow.php`
- **Vista Show:** `resources/views/livewire/admin/packing-slips/packing-slip-show.blade.php`
- **Modelo WorkOrder:** `app/Models/WorkOrder.php`
- **Modelo PackingSlip:** `app/Models/PackingSlip.php`
- **Modelo Part:** `app/Models/Part.php`
- **Sidebar:** `resources/views/components/layouts/admin/sidebar.blade.php`
- **Rutas admin:** `routes/admin.php`

---

## 14. Simplificacion del Ciclo de Vida: 3 Estados Canonicos

**Fecha:** 2026-03-09
**Motivo:** El ciclo de vida del Packing Slip tenia 5 estados (`draft`, `confirmed`, `pending`, `shipped`, `closed`) que generaban ambiguedad operativa. Se redujo a 3 estados claros y suficientes para el flujo real de produccion.

---

### 14.1 Estados Eliminados y Razon

| Estado eliminado | Valor BD | Razon de eliminacion |
|---|---|---|
| Borrador | `draft` | Redundante con `pending`. Un PS recien creado ya es operativo desde el inicio; no requiere estado intermedio de "borrador" antes de estar activo. |
| Confirmado | `confirmed` | No existia ninguna accion de negocio asociada a este estado. Representaba un paso de flujo sin efecto real en el sistema. |
| Cerrado | `closed` | Semanticamente confuso respecto a `shipped`. Un PS despachado ya esta "cerrado" por definicion. |

### 14.2 Nuevo Estado Agregado

| Estado | Valor BD | Descripcion |
|---|---|---|
| Cancelado | `cancelled` | Permite marcar un PS que no procedera a despacho. Solo cambia el label y el color del badge; no tiene logica especial adicional (no libera lotes automaticamente). |

### 14.3 Tabla Final de Estados

| Valor | Label | Color badge | Comportamiento especial |
|---|---|---|---|
| `pending` | Pendiente | Naranja | Estado por defecto al crear un PS. Permite edicion de lotes. |
| `shipped` | Despachado | Verde | Al transicionar a este estado: registra `shipped_at = now()` y `shipped_by = Auth::id()`. Al salir de este estado: limpia ambos campos. Bloquea edicion de lotes. |
| `cancelled` | Cancelado | Rojo | Solo label. Sin logica adicional. |

### 14.4 Cambio en Estado por Defecto al Crear

- **Antes:** `PackingSlip::STATUS_DRAFT` (`'draft'`)
- **Ahora:** `PackingSlip::STATUS_PENDING` (`'pending'`)

Afecta dos puntos de creacion:
- `PackingSlipCreate.php` — formulario manual
- `ShippingQueue.php` — creacion rapida desde la cola de shipping

### 14.5 Cambio en Logica de Eliminacion

La eliminacion de un PS solo esta permitida cuando el PS es susceptible de descarte sin consecuencias operativas.

- **Antes:** `isDraft()` — solo se podia eliminar en estado `draft`
- **Ahora:** `isPending()` — se puede eliminar en estado `pending`

El comportamiento tecnico es identico: se eliminan los items primero (libera los lotes del scope `readyForShipping`) y luego se aplica soft-delete al PS.

### 14.6 Cambio en Visibilidad del Boton "Editar lotes"

- **Antes:** el boton se mostraba solo cuando `isDraft()` era verdadero
- **Ahora:** el boton se muestra cuando `!isShipped()`, es decir, en `pending` y `cancelled`

Esto es mas permisivo y coherente: un PS cancelado tambien puede corregirse antes de ser eliminado o re-activado.

### 14.7 Datos Existentes en BD

Los registros existentes con `status = 'draft'` o `status = 'confirmed'` o `status = 'closed'` conservan esos valores en la base de datos. La UI los mostrara con el badge `default` (gris neutro) ya que el `match` de Blade tiene un caso `default`. Son datos de prueba y no afectan produccion.

No se requiere migracion porque el campo `status` es `VARCHAR` sin restriccion de enum.

### 14.8 Archivos Modificados

| Archivo | Tipo de cambio |
|---|---|
| `app/Models/PackingSlip.php` | Elimino constantes `STATUS_DRAFT`, `STATUS_CONFIRMED`, `STATUS_CLOSED`; agrego `STATUS_CANCELLED`. Actualizo `STATUSES`, scopes, helpers y `getStatusColorAttribute()`. |
| `app/Livewire/Admin/PackingSlips/PackingSlipCreate.php` | Estado inicial cambiado de `STATUS_DRAFT` a `STATUS_PENDING`. |
| `app/Livewire/Admin/PackingSlips/PackingSlipList.php` | Stats: elimino `draft`, `confirmed`, `closed`; agrego `cancelled`. Query filtro: elimino ramas `draft`, `confirmed`, `closed`; agrego `cancelled`. Guards `isDraft()` reemplazados por `isPending()`. |
| `app/Livewire/Admin/PackingSlips/PackingSlipEdit.php` | Guard `isDraft()` reemplazado por `isShipped()`. Mensajes actualizados. |
| `app/Livewire/Admin/Shipping/ShippingQueue.php` | Estado inicial cambiado de `STATUS_DRAFT` a `STATUS_PENDING`. Comentarios actualizados. |
| `resources/views/livewire/admin/packing-slips/packing-slip-show.blade.php` | Match de badge actualizado a 3 estados. Boton "Editar lotes" cambiado de `isDraft()` a `!isShipped()`. |
| `resources/views/livewire/admin/packing-slips/packing-slip-list.blade.php` | Stats cards: de 6 a 4 (Total, Pendiente, Despachado, Cancelado). Select filtro: 3 opciones. Match de badge: 3 estados. Boton eliminar: `isDraft()` reemplazado por `isPending()`. |
| `resources/views/livewire/admin/packing-slips/packing-slip-edit.blade.php` | Condicion `!isDraft()` reemplazada por `isShipped()`. Texto de aviso actualizado. |

---

## 15. Cambios Sesion 2026-03-09 — Renombrado de "Cola de Despacho" a "WO Listos para PS"

### 15.1 Contexto y decision

Durante la revision del modulo de Packing Slips, el usuario identifico que el nombre **"Cola de Despacho"** no describe correctamente la funcionalidad de la vista `ShippingQueue`. El nombre implica que ya se esta realizando un despacho, cuando en realidad la vista muestra **Work Orders con lotes que ya terminaron el proceso de empaque** y estan disponibles para ser seleccionados y agrupados en un Packing Slip.

**Nombre anterior:** Cola de Despacho
**Nombre nuevo:** WO Listos para PS

**Razonamiento:** El nombre "WO Listos para PS" describe exactamente lo que el usuario ve: Work Orders cuyos lotes ya completaron empaque y pueden incluirse en un Packing Slip. Es directo, tecnico y sin ambiguedad sobre el estado del proceso.

### 15.2 Flujo real de la vista (documentado para referencia)

```
Lote finalizado en Empaque (ready_for_shipping = true)
        ↓
Aparece en "WO Listos para PS"
        ↓
Usuario selecciona los lotes (con checkbox) que quiere agrupar
        ↓
Click en "Crear Packing Slip" → PS generado en estado "Pendiente"
        ↓
El lote desaparece de la vista automaticamente
(el scope Lot::scopeReadyForShipping usa whereDoesntHave('packingSlipItem'))
```

### 15.3 Archivos modificados

| Archivo | Cambio |
|---|---|
| `resources/views/components/layouts/admin/sidebar.blade.php` | Texto del enlace: "Cola de Despacho" → "WO Listos para PS" |
| `resources/views/livewire/admin/shipping/shipping-queue.blade.php` | Titulo `<h1>`: "Cola de Despacho" → "WO Listos para PS". Subtitulo actualizado a "Work Orders con lotes disponibles para crear un Packing Slip (FPL-10)" |

### 15.4 Lo que NO se cambio

- Nombre del componente PHP (`ShippingQueue.php`) — cambio interno sin impacto en UX
- Nombre de la ruta (`admin.shipping.queue`) — cambiar la ruta requeriria actualizar todos los links y redirects; sin beneficio practico
- Nombre del archivo blade (`shipping-queue.blade.php`) — idem anterior

---

## 16. Refactorizacion 2026-03-09 — Consolidacion de Show y Edit en Vista Unica

### 16.1 Motivacion

Durante el analisis del modulo de Packing Slips se detecto que existian dos componentes Livewire con alto grado de superposicion funcional:

- `PackingSlipShow` + `packing-slip-show.blade.php` — vista principal del PS
- `PackingSlipEdit` + `packing-slip-edit.blade.php` — vista de edicion de lotes

Ademas se identifico un **bug critico de navegacion**: el boton "Editar" en la lista de PS (`packing-slip-list.blade.php`, linea 223) apuntaba incorrectamente a la ruta `admin.packing-slips.show` en lugar de `admin.packing-slips.edit`, lo que significaba que la vista Edit era practicamente inaccesible desde la UI principal.

La vista Show ya tenia capacidad de edicion inline (columnas Date y Label Spec editables via Alpine.js). La vista Edit solo agregaba la gestion estructural del PS (agregar/quitar lotes, cambiar document_date y notes). Mantener dos vistas separadas generaba:

1. Duplicidad de codigo y responsabilidades solapadas
2. Confusion para el usuario (dos URL distintas para el mismo documento)
3. Un bug de navegacion activo sin detectar
4. Mayor superficie de mantenimiento

### 16.2 Tabla comparativa: funcionalidades antes y despues

| Funcionalidad | Vista Show (antes) | Vista Edit (antes) | Vista Show Unificada (despues) |
|---|---|---|---|
| Ver PS number, badge de estado | Si | No | Si |
| Cambiar estado (pending/shipped/cancelled) | Si | No | Si |
| Registrar shipped_at / shipped_by | Si | No | Si |
| Ver document_date, notas, creado_por | Si (lectura) | Si (edicion via form) | Si (lectura) |
| Editar document_date | No | Si (input date en form) | Pendiente (siguiente iteracion) |
| Editar notas | No | Si (textarea en form) | Pendiente (siguiente iteracion) |
| Tabla de items agrupados por PO | Si | No | Si |
| Subtotales por PO y total general | Si | No | Si |
| Edicion inline de Date por item | Si (Alpine.js) | No | Si (Alpine.js, sin cambios) |
| Edicion inline de Label Spec por item | Si (Alpine.js) | No | Si (Alpine.js, sin cambios) |
| Agregar / quitar lotes del PS | No | Si (checkboxes) | Si (panel integrado toggle) |
| Validar external_wo_number en lotes | No | Si | Si (heredado de Edit) |
| Crear / eliminar PackingSlipItem | No | Si | Si (heredado de Edit) |
| Metadatos (created_at, updated_at) | Si | No | Si |
| Proteccion contra edicion si shipped | N/A | Si (redirect en mount) | Si (guard en toggleEditingLots y updateLots) |
| Boton "Editar lotes" | Link a ruta edit (URL separada) | N/A | Toggle de panel integrado (misma URL) |

### 16.3 Como quedo la vista unificada

La vista `PackingSlipShow` ahora opera en dos modos dentro de la misma URL (`/admin/packing-slips/{id}`):

**Modo lectura (por defecto):**
- Header con PS number, badge de estado, selector de estado, boton "Editar lotes", boton "Volver"
- Seccion de informacion general (lectura)
- Tabla de items con edicion inline de Date y Label Spec
- Metadatos de auditoria

**Modo edicion de lotes (`$editingLots = true`):**
- El panel de edicion aparece entre la seccion de informacion general y la tabla de items
- Borde ambar doble para distinguirlo visualmente del resto
- Tabla de checkboxes con todos los lotes disponibles (readyForShipping + los ya incluidos en el PS)
- Preview del `wo_number_ps` generado para cada lote
- Input de Label Spec por lote seleccionado
- Botones "Cancelar" (colapsa el panel) y "Guardar cambios de lotes" (llama `updateLots()`)
- Al guardar: sincroniza items, recarga el PS con todas las relaciones, cierra el panel

**Comportamiento de seguridad:**
- Si el PS esta en estado `shipped`, el boton "Editar lotes" no aparece en el header
- El metodo `toggleEditingLots()` tiene un guard que retorna inmediatamente si `isShipped()`
- El metodo `updateLots()` tiene un guard identico al que tenia `PackingSlipEdit::update()`
- Al cambiar el estado a `shipped` via `updateStatus()`, el panel se cierra automaticamente si estuviera abierto

### 16.4 Logica de inicializacion de lotes

El componente mantiene sincronizacion entre los items del PS y el estado del panel:

```
mount() → initLotSelection()
    Lee todos los items del PS
    Populea $selectedLotIds[] y $labelSpecs[]

toggleEditingLots() → al abrir:
    Recarga $packingSlip con relaciones
    Re-ejecuta initLotSelection() para asegurar estado fresco

updateLots() → al guardar:
    Valida, sincroniza DB
    Recarga $packingSlip
    Re-ejecuta initLotSelection()
    Cierra el panel ($editingLots = false)
```

### 16.5 Archivos modificados

| Archivo | Cambio | Tipo |
|---|---|---|
| `app/Livewire/Admin/PackingSlips/PackingSlipShow.php` | Fusionada logica de Edit: propiedades `$editingLots`, `$selectedLotIds`, `$labelSpecs`; metodos `initLotSelection`, `toggleEditingLots`, `toggleLot`, `rulesForLots`, `updateLots`; render ampliado con `$availableLots` | Modificado |
| `resources/views/livewire/admin/packing-slips/packing-slip-show.blade.php` | Boton "Editar lotes" cambiado de link a button toggle; panel de edicion de lotes integrado con borde ambar | Modificado |
| `resources/views/livewire/admin/packing-slips/packing-slip-list.blade.php` | Eliminado boton "Editar" duplicado (apuntaba incorrectamente a `show`); la accion "Ver" es suficiente | Modificado |
| `routes/admin.php` | Eliminada ruta `admin.packing-slips.edit` (`GET /packing-slips/{packingSlip}/edit`) | Modificado |

### 16.6 Archivos eliminados

| Archivo | Motivo |
|---|---|
| `app/Livewire/Admin/PackingSlips/PackingSlipEdit.php` | Toda su logica fue fusionada en `PackingSlipShow.php` |
| `resources/views/livewire/admin/packing-slips/packing-slip-edit.blade.php` | Toda su UI fue fusionada en `packing-slip-show.blade.php` |

### 16.7 Verificacion de referencias

Antes de eliminar, se verifico con `grep` que `packing-slips.edit` y `PackingSlipEdit` solo existian en 3 puntos del proyecto:

1. `routes/admin.php` — definicion de la ruta (eliminada)
2. `packing-slip-show.blade.php` — link en boton "Editar lotes" (convertido a button toggle)
3. `packing-slip-list.blade.php` — boton "Editar" con bug (apuntaba a `show`, eliminado)

Ninguna otra parte del sistema (controllers, jobs, otros componentes) referenciaba la ruta o el componente Edit.

### 16.8 Rutas de Packing Slips post-refactorizacion

```
GET /admin/packing-slips              → PackingSlipList   (index)
GET /admin/packing-slips/create       → PackingSlipCreate (create)
GET /admin/packing-slips/{id}         → PackingSlipShow   (show + edicion de lotes integrada)
```

---
