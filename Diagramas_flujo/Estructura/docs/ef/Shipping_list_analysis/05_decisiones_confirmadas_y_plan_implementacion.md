# Analisis Tecnico: Decisiones Confirmadas y Plan de Implementacion

**Fecha:** 2026-03-08
**Elaborado por:** Arquitecto de Software - FlexCon Tracker
**Version:** 1.0
**Proposito:** Consolidar las respuestas del equipo operativo de FlexCon a las preguntas abiertas del documento `04_empaque_to_shipping_list_transition.md`, analizar su impacto arquitectural y producir el plan de implementacion definitivo para el modulo de Packing Slip (FPL-10) y su transicion desde el cierre de Empaque.
**Documentos previos:**
- `01_shipping_list_analysis.md` — Estructura del Packing Slip FPL-10
- `02_invoice_analysis.md` — Invoice FPL-12 y relacion 1:1 con el Packing Slip
- `03_field_mapping_lista_envio_to_packing_slip.md` — Mapeo de campos y reglas de negocio
- `04_empaque_to_shipping_list_transition.md` — Opciones de diseno y preguntas abiertas

---

## 1. Resumen de Respuestas del Equipo Operativo

Las siguientes respuestas fueron dadas por el equipo de FlexCon a las preguntas abiertas del documento 04. Cada respuesta se reproduce textualmente y se interpreta arquitecturalmente.

### R-04-01: Evento de cierre de empaque (P-04-01)

**Respuesta recibida:**
> El evento exacto del cierre de empaque ya esta hecho. Se definio que se puede cerrar en 3 opciones que ya estan hechas: a) Completar el lote, b) Crear un nuevo lote, c) Cerrar el lote.

**Interpretacion arquitectural:**

Las tres opciones corresponden exactamente a los valores del campo `lots.closure_decision`:
- `complete_lot` — Completar el lote (sin sobrante)
- `new_lot` — Crear un nuevo lote (el sobrante se convierte en nuevo lote)
- `close_as_is` — Cerrar el lote tal como esta

**Conclusion:** El mecanismo de cierre ya existe en la base de datos y en la logica de negocio. El Observer (`LotPackagingObserver`) debe escuchar el evento de escritura de `closure_decision` en combinacion con las condiciones de `viajero_received` y `surplus_received` para disparar la transicion a `packaging_status = 'completed'`.

**Decision confirmada D-04-01:** El trigger automatico (Opcion B) es la arquitectura correcta. Los tres eventos de cierre ya estan implementados.

---

### R-04-02: Cantidad a usar en el Packing Slip (P-04-02)

**Respuesta recibida:**
> Es posible 2 escenarios para esta respuesta:
> - a) El WO se cierra corto, es decir que se termina en las 99,850 aunque falten 150 piezas por terminar.
> - b) Se continua con la WO hasta terminar las 150 piezas faltantes.

**Interpretacion arquitectural:**

Esta respuesta confirma que ambos escenarios son posibles en la operacion real, y el sistema debe soportarlos a traves del mecanismo de `closure_decision`:

| Escenario | `closure_decision` | Cantidad en Packing Slip |
|---|---|---|
| WO se cierra corto | `close_as_is` | `quantity_packed_final` = SUM(packed_pieces) = 99,850 |
| WO continua con nuevo lote | `new_lot` | El lote original se despacha con 99,850; el nuevo lote con las 150 piezas va en un PS futuro |
| WO se completa exactamente | `complete_lot` | `quantity_packed_final` = SUM(packed_pieces) = 100,000 |

**Conclusion critica:** La cantidad que va al Packing Slip es **siempre la cantidad real empacada** (`quantity_packed_final = SUM(packaging_records.packed_pieces)`), NO la cantidad teorica del lote (`lots.quantity`). Esto es consistente con la observacion del documento 03: las cantidades del PS SL001249 coinciden con la Lista de Envio porque la Lista de Envio ya refleja las cantidades reales de cada lote ajustadas al cierre.

**Decision confirmada D-04-03:** `packing_slip_items.quantity` = `lots.quantity_packed_final` (cantidad real, no teorica).

---

### R-04-03: Numero de WO externo (P-04-03)

**Respuesta recibida:**
> Debe agregarse un Work Order diferente ya que es un proceso diferente en Shipping List. En el packing list cambio el WO: se le agrega una constante al principio (`W0 + el_numero_de_WO + el_numero_de_lote`). Ejemplo: `W0` + `19802131` + `001`. Asi que es una concatenacion de una constante `W0` mas el numero de WO mas el numero de lote. Si el WO tuvo mas de 1 lote, habra mas lineas en el packing list.
>
> Se puede crear una variable en un componente de Livewire que se llame `wo_packing_list_const = 'W0'` para despues jalar el WO con sus lotes y crear una funcion adicional para crear esta concatenacion de `W0 + WO + Lotes` y en la misma lista traer el WO con la PO, el numero de parte, la descripcion y la cantidad.

**Interpretacion arquitectural:**

El equipo confirma dos aspectos fundamentales:

1. **El numero de WO del Packing Slip es una construccion sintetica**, no un campo pre-existente. Se construye dinamicamente: `"W0" + wo_number_externo + lot_sequence`.

2. **El `wo_number` actual en `work_orders` (formato `WO-2025-00001`) NO es el numero que aparece en el Packing Slip**. El numero de 7 digitos que aparece en FPL-10 (ej: `1980231`) es diferente del numero de WO del sistema.

3. **La sugerencia de una variable `wo_packing_list_const = 'W0'` en Livewire** es una confirmacion de que el prefijo es constante, pero la logica debe estar en el modelo/servicio (no solo en el componente Livewire) para garantizar consistencia y reutilizacion.

**Implicacion critica:** La tabla `work_orders` necesita un campo adicional para almacenar el numero de WO del sistema legacy/externo (el numero de 7 digitos que aparece en la Lista de Envio FPL-02 y en el Packing Slip FPL-10).

**Decision confirmada D-04-07:** Se debe agregar el campo `external_wo_number` (o `legacy_wo_number`) a la tabla `work_orders`.

---

### R-04-04: Frecuencia de despacho (P-04-04)

**Respuesta recibida:**
> - a) Es posible que se mande una vez por semana incluyendo todos los lotes listos en esa semana.
> - b) O Shipping decide sin una frecuencia fija.
>
> Esto no es una decision estrategica asi, puede cambiar dependiendo lo que el cliente nos diga en la siguiente reunion.

**Interpretacion arquitectural:**

La frecuencia de despacho es variable y esta bajo el control discrecional del area de Shipping. El sistema no debe imponer una periodicidad fija. La vista de "cola de Shipping" debe mostrar todos los lotes listos, sin agruparlos forzosamente por semana.

**Implicacion en diseno de UI:** La vista de la cola de Shipping lista todos los lotes con `packaging_status = 'completed'` y `packing_slip_id IS NULL`, ordenados por `packaging_completed_at ASC`, sin filtros de periodo fijo. Shipping selecciona libremente cuales incluir en cada PS.

**Decision confirmada:** La cola de Shipping es una lista plana de lotes listos, sin agrupacion forzada por semana. Shipping controla completamente cuando y que despachar.

---

### R-04-05: Permisos para crear el Packing Slip (P-04-05)

**Respuesta recibida:**
> En escaso podrian ser varios: el Administrador, el area de Empaque y Shipping.

**Interpretacion arquitectural:**

Los permisos de Spatie para el modulo de Packing Slip deben cubrir al menos tres roles:
- `admin` (Administrador / Frank)
- `empaque` (Area de Empaque)
- `shipping` (Area de Shipping)

Segun el nivel de acceso, se distinguen dos tipos de permisos:
- `packing_slip.view` — Ver la lista de PS y el detalle de cada uno
- `packing_slip.create` — Crear un nuevo Packing Slip desde la cola
- `packing_slip.confirm` — Confirmar un PS en borrador y generar PDF
- `packing_slip.manage` — Editar, eliminar o revertir un PS (solo Administrador)

**Decision a confirmar (pendiente):** Se recomienda que la creacion del PS quede en manos de Shipping o Admin, y que Empaque solo tenga permiso de vista. Esto se debe confirmar con Frank en la siguiente reunion.

---

### R-04-06: Manejo de lotes con sobrante (P-04-06)

**Respuesta recibida:**
> - a) Se cierra con la cantidad empacada. El area de Materiales define si se abre un nuevo lote o se va asi porque ellos controlan los materiales para los lotes.
> - b) Se puede esperar a que termine el nuevo lote para cerrar la WO completa.

**Interpretacion arquitectural:**

Esto confirma que cuando `closure_decision = 'new_lot'`:
- El lote **original** se cierra inmediatamente con `quantity_packed_final = SUM(packed_pieces)` del lote original y queda disponible para un PS.
- El lote **nuevo** (generado por el sobrante) sigue su propio ciclo de produccion y queda como un lote independiente que se despachara en un PS futuro cuando este listo.
- La decision de si el lote nuevo debe ir en el mismo PS que el original o en uno posterior la toma Shipping al momento de armar el PS (los dos estaran en la cola si ambos estan listos).

**Implicacion en el Observer:** Cuando `closure_decision` se guarda como `new_lot`, el lote original debe marcarse como `packaging_status = 'completed'` de inmediato (no esperar al lote nuevo). El lote nuevo seguira su propio flujo independiente.

**Decision confirmada:** El Observer marca el lote original como completado en el momento en que se registra `closure_decision`, sin esperar el nuevo lote.

---

### R-04-07: Notificaciones al area de Shipping (P-04-07)

**Respuesta recibida:**
> Todas las areas tendran acceso a ver la Lista de Envio y podran ver como va cada lote.

**Interpretacion arquitectural:**

No se requiere un sistema de notificaciones activas (emails, alertas push). Es suficiente con que los lotes listos aparezcan en la cola de Shipping cuando el usuario ingrese al modulo. La visibilidad compartida del estado de los lotes (que ya existe en el modulo de Lista de Envio) es suficiente para la coordinacion entre areas.

**Decision confirmada:** No se implementan notificaciones en Fase 1. La cola de Shipping es la unica interfaz requerida para este flujo.

---

### R-04-08: Reversion de un lote cerrado por error (P-04-08)

**Respuesta recibida:**
> Ya existe esta logica para el negocio y esta integrada en la Lista de Envio.

**Interpretacion arquitectural:**

La logica de reversion ya esta implementada en el modulo de Lista de Envio. Para el modulo de Packing Slip, se debe verificar que la reversion de un cierre de empaque (`packaging_status = 'completed'` -> estado anterior) solo sea posible si el lote aun no ha sido incluido en un Packing Slip (`packing_slip_id IS NULL`). Si el lote ya fue incluido en un PS, la reversion requiere primero remover el lote del PS (o cancelar el PS si ya fue confirmado).

**Decision confirmada:** La logica de reversion existente en la Lista de Envio es suficiente para Fase 1. No se necesita logica nueva, pero se debe agregar una validacion que bloquee la reversion si `packing_slip_id IS NOT NULL`.

---

## 2. Tabla de Decisiones Definitivas

| ID | Decision | Estado anterior | Estado confirmado | Notas |
|---|---|---|---|---|
| D-01 | Mecanismo de transicion: Opcion B (trigger automatico via Observer) | PROPUESTO | **CONFIRMADO** | Las 3 opciones de cierre ya existen en el sistema |
| D-02 | Persistir `quantity_packed_final` en `lots` al cierre de empaque | PROPUESTO | **CONFIRMADO** | Critico para ambos escenarios de cierre |
| D-03 | Packing Slip usa cantidad real (`quantity_packed_final`), no teorica | PENDIENTE | **CONFIRMADO** | Ambos escenarios (cerrar corto / continuar WO) usan la cantidad real |
| D-04 | Agregar `packaging_completed_at`, `packaging_completed_by` a `lots` | PROPUESTO | **CONFIRMADO** | Metadata de auditoria del cierre |
| D-05 | Agregar `packing_slip_id` a `lots` si no existe | PENDIENTE VERIFICAR | **REQUERIDO** | No existe en migraciones actuales; debe crearse |
| D-06 | Vista "Cola de despacho" en Livewire sin agrupacion fija por semana | PROPUESTO | **CONFIRMADO** | Shipping decide libremente que despachar y cuando |
| D-07 | Agregar `external_wo_number` a `work_orders` | PENDIENTE | **CONFIRMADO** | El numero de 7 digitos del legacy es diferente al formato `WO-YYYY-NNNNN` |
| D-08 | Prefijo WO Packing Slip es constante `'W0'`, construido en capa de modelo/servicio | NUEVO | **CONFIRMADO** | No en Livewire component; debe estar en modelo o servicio PHP |
| D-09 | Observer marca lote original como completado inmediatamente al registrar `closure_decision` | NUEVO | **CONFIRMADO** | Para `new_lot`: el lote original no espera al nuevo lote |
| D-10 | No implementar notificaciones activas en Fase 1 | NUEVO | **CONFIRMADO** | La visibilidad en la cola de Shipping es suficiente |
| D-11 | Permisos para PS: Admin, Empaque, Shipping (detallar niveles en reunion con Frank) | NUEVO | **PARCIALMENTE CONFIRMADO** | Confirmar si Empaque tiene solo vista o tambien puede crear PS |
| D-12 | Reversion bloqueada si `packing_slip_id IS NOT NULL` | NUEVO | **CONFIRMADO** | Validacion adicional requerida en la logica existente |

---

## 3. Impacto Arquitectural Detallado

### 3.1 Backend (Laravel)

#### 3.1.1 Cambios en Modelos

**Modelo `Lot` (`app/Models/Lot.php`):**
- Agregar constante `CLOSURE_CLOSE_AS_IS = 'close_as_is'` (verificar si ya existe `CLOSURE_COMPLETE_LOT` y `CLOSURE_NEW_LOT`)
- Agregar relacion `belongsTo` -> `PackingSlip` via `packing_slip_id`
- Agregar scope `readyForShipping()` (lotes con packaging_status = 'completed' y packing_slip_id IS NULL)
- Agregar accesores para `quantity_packed_final` (fallback al calculo dinamico si el campo es NULL)
- Agregar validacion en el metodo de reversion: bloquear si `packing_slip_id IS NOT NULL`

**Modelo `WorkOrder` (`app/Models/WorkOrder.php`):**
- Agregar campo `external_wo_number` (nullable string, 20 caracteres)
- Agregar metodo estatico `buildPackingSlipWoNumber(string $externalWoNumber, int $lotSequence): string`
- El metodo es: `'W0' . $externalWoNumber . str_pad($lotSequence, 3, '0', STR_PAD_LEFT)`

**Nuevos Modelos:**
- `PackingSlip` (`app/Models/PackingSlip.php`)
- `PackingSlipItem` (`app/Models/PackingSlipItem.php`)

#### 3.1.2 Nuevo Observer

**`LotPackagingObserver` (`app/Observers/LotPackagingObserver.php`):**

Este es el componente central de la transicion. Escucha los cambios en el modelo `Lot` y dispara el cierre de empaque cuando se cumplen las condiciones.

Logica de disparo segun `closure_decision`:

| `closure_decision` | Condicion adicional | Accion del Observer |
|---|---|---|
| `complete_lot` | `viajero_received = true` Y `packed_pieces > 0` | Marcar `packaging_status = 'completed'` inmediatamente |
| `new_lot` | Nuevo lote creado (el sobrante fue registrado) | Marcar lote original como `packaging_status = 'completed'` inmediatamente |
| `close_as_is` | `viajero_received = true` Y `packed_pieces > 0` | Marcar `packaging_status = 'completed'` inmediatamente |

En los tres casos, el Observer calcula y persiste `quantity_packed_final = SUM(packaging_records.packed_pieces WHERE lot_id = X AND deleted_at IS NULL)`.

**Caso especial `new_lot`:** El Observer debe verificar que el nuevo lote hijo ya fue creado en el sistema antes de marcar el lote padre como completado. La deteccion puede hacerse via el campo `closure_decision = 'new_lot'` combinado con `surplus_received = true` (Materiales confirmo que el sobrante fisico fue recibido, lo que implica que ya se registro el nuevo lote).

#### 3.1.3 Nuevos Servicios

**`PackingSlipService` (`app/Services/PackingSlipService.php`):**
- `createFromLots(array $lotIds, array $slipData): PackingSlip` — Crea el PS y sus items desde los lotes seleccionados
- `confirmPackingSlip(PackingSlip $slip): PackingSlip` — Confirma el PS (status draft -> confirmed)
- `markAsShipped(PackingSlip $slip, array $shippingData): PackingSlip` — Marca como despachado y actualiza `lots.packing_slip_id` y `work_orders.actual_send_date`
- `generateSlipNumber(): string` — Genera el numero correlativo `SL{NNNNNN}`

#### 3.1.4 Permisos Spatie (nuevos)

```
packing_slip.view    — Ver lista y detalle de Packing Slips
packing_slip.create  — Crear un nuevo Packing Slip desde la cola
packing_slip.confirm — Confirmar un PS en borrador
packing_slip.ship    — Marcar un PS como despachado y generar PDF
packing_slip.manage  — Editar, eliminar, revertir un PS (solo Admin)
```

Roles que reciben cada permiso (propuesta inicial, confirmar con Frank):

| Permiso | Admin | Shipping | Empaque | QA | Produccion |
|---|---|---|---|---|---|
| packing_slip.view | Si | Si | Si | No | No |
| packing_slip.create | Si | Si | No | No | No |
| packing_slip.confirm | Si | Si | No | No | No |
| packing_slip.ship | Si | Si | No | No | No |
| packing_slip.manage | Si | No | No | No | No |

---

### 3.2 Base de Datos

Ver Seccion 4 (Esquema de BD Definitivo) para el detalle completo.

**Tablas afectadas:**
- `lots` — 4 campos nuevos + 1 campo (packing_slip_id) que debe verificarse
- `work_orders` — 1 campo nuevo (`external_wo_number`)
- `packing_slips` — Tabla nueva
- `packing_slip_items` — Tabla nueva

---

### 3.3 Frontend (Livewire + Alpine.js + Tailwind)

#### Nuevos Componentes Livewire

**1. `Shipping/ReadyForDispatch` (vista: cola de lotes listos)**
- Lista paginada de lotes con `packaging_status = 'completed'` y `packing_slip_id IS NULL`
- Columnas: Lote, WO Externo, Item#, Descripcion, PO#, Cantidad empacada, Fecha cierre empaque
- Checkbox de seleccion multiple para elegir que lotes incluir en el PS
- Boton "Crear Packing Slip con seleccionados"
- Filtros opcionales: por WO, por Item#, por rango de fechas de cierre de empaque

**2. `Shipping/CreatePackingSlip` (wizard de creacion del PS)**
- Paso 1: Confirmar lotes seleccionados y sus cantidades
- Paso 2: Ingresar `label_spec` por cada lote (campo manual, texto libre)
- Paso 3: Ingresar datos del pie de pagina (total_boxes_404, total_boxes_20x20, firmas)
- Paso 4: Revisar preview del PS y confirmar

**3. `Shipping/PackingSlipList` (lista de PS generados)**
- Lista paginada de todos los Packing Slips (draft, confirmed, shipped)
- Acciones: ver detalle, confirmar (si draft), generar PDF, marcar como despachado

**4. `Shipping/PackingSlipDetail` (detalle de un PS)**
- Cabecera del PS: numero, fecha, estado
- Tabla de items: WO#, PO#, Item#, Descripcion, Cantidad, Label Spec
- Filas de Total por WO (calculadas automaticamente)
- Pie de pagina: cajas, firmas
- Botones de accion segun estado

---

### 3.4 Flujo de Estados Definitivo

```
                     [LOTE EN PRODUCCION]
                      status = 'in_progress'
                             |
                             v
                     [PRODUCCION COMPLETADA]
                      status = 'completed'
                             |
                             v
                  [INSPECCION DE CALIDAD]
              inspection_status = 'approved'
                             |
                     (si 'rejected' -> Accion Correctiva)
                             |
                             v
                    [PESADA DE EMPAQUE]
              packaging_records creados
              packaging_status = 'in_progress'
                             |
                             v
              [VIAJERO ENVIADO A MATERIALES]
              viajero_received = true
                             |
                             v
         [DECISION DE CIERRE - CONTROL DE MATERIALES]
         closure_decision = 'complete_lot' | 'new_lot' | 'close_as_is'
                             |
              +--------------+---------------+
              |              |               |
       complete_lot       new_lot        close_as_is
              |              |               |
         No sobrante   Crea nuevo Lot   Cierra con lo empacado
              |         (sobrante)           |
              |         surplus_received=true|
              +------------------------------+
                             |
               [OBSERVER DETECTA CIERRE COMPLETO]
                             |
                             v
              [EMPAQUE COMPLETADO - LISTO PARA PS]
              packaging_status = 'completed'
              quantity_packed_final = SUM(packed_pieces)
              packaging_completed_at = NOW()
              packaging_completed_by = auth()->id()
                             |
                  (Aparece en cola de Shipping)
                             |
                             v
              [INCLUIDO EN PACKING SLIP - DRAFT]
              packing_slip_id = [ID del PS draft]
                             |
                  (Shipping confirma el PS)
                             |
                             v
              [PACKING SLIP CONFIRMADO]
              packing_slips.status = 'confirmed'
                             |
                  (Shipping despacha y genera PDF)
                             |
                             v
              [LOTE DESPACHADO]
              packing_slips.status = 'shipped'
              work_orders.actual_send_date = fecha de despacho
              work_orders.sent_pieces += quantity_packed_final
```

---

## 4. Esquema de BD Definitivo

### 4.1 Modificaciones a la Tabla `lots`

**Migracion:** `YYYY_MM_DD_HHMMSS_add_packing_slip_readiness_to_lots_table.php`

```php
Schema::table('lots', function (Blueprint $table) {
    // Cantidad real empacada al cierre del lote
    // Calculada como SUM(packaging_records.packed_pieces) en el momento del cierre
    // Es el valor que se usa en packing_slip_items.quantity
    $table->unsignedInteger('quantity_packed_final')
          ->nullable()
          ->after('quantity')
          ->comment('Cantidad real empacada al cierre. Snapshot de SUM(packaging_records.packed_pieces)');

    // Metadata del cierre de empaque (cuando el lote quedo listo para despacho)
    $table->timestamp('packaging_completed_at')
          ->nullable()
          ->after('packaging_inspected_at')
          ->comment('Timestamp del cierre de empaque (disparo del Observer)');

    $table->foreignId('packaging_completed_by')
          ->nullable()
          ->after('packaging_completed_at')
          ->constrained('users')
          ->nullOnDelete()
          ->comment('Usuario autenticado al momento del cierre de empaque');

    // Referencia al Packing Slip donde fue incluido este lote
    // NULL = pendiente de despacho / disponible en cola de Shipping
    // NOT NULL = ya incluido en un PS (no aparece en la cola)
    $table->foreignId('packing_slip_id')
          ->nullable()
          ->after('packaging_completed_by')
          ->constrained('packing_slips')
          ->nullOnDelete()
          ->comment('ID del Packing Slip. NULL = pendiente de despacho');
});
```

> **NOTA de orden de migraciones:** La tabla `packing_slips` debe crearse ANTES de esta migracion porque `lots.packing_slip_id` referencia `packing_slips.id`.

---

### 4.2 Modificaciones a la Tabla `work_orders`

**Migracion:** `YYYY_MM_DD_HHMMSS_add_external_wo_number_to_work_orders_table.php`

```php
Schema::table('work_orders', function (Blueprint $table) {
    // Numero de WO del sistema legacy/externo (el numero de 7 digitos de la Lista de Envio FPL-02)
    // Ejemplo: '1980231', '1982798', '1955984'
    // Este numero es el que aparece en el Packing Slip FPL-10 como parte del campo Work Order
    // Formato resultante: 'W0' + external_wo_number + lot_sequence_padded
    // Ejemplo: 'W0' + '1980231' + '001' = 'W01980231001'
    $table->string('external_wo_number', 20)
          ->nullable()
          ->after('wo_number')
          ->comment('Numero WO del sistema legacy (7 digitos). Usado para construir el WO# del Packing Slip');

    $table->index('external_wo_number');
});
```

---

### 4.3 Nueva Tabla `packing_slips`

**Migracion:** `YYYY_MM_DD_HHMMSS_create_packing_slips_table.php`

```php
Schema::create('packing_slips', function (Blueprint $table) {
    $table->id();

    // Numero correlativo del Packing Slip. Continua desde SL001249.
    // Formato: 'SL' + 6 digitos con ceros: SL001250, SL001251, ...
    $table->string('slip_number', 20)->unique()
          ->comment('Numero correlativo. Formato SL001250, SL001251...');

    // Fecha del despacho/embarque fisico
    $table->date('slip_date')
          ->comment('Fecha del despacho o embarque');

    // Estado del ciclo de vida del Packing Slip
    // draft     = en construccion (puede modificarse)
    // confirmed = revisado y aprobado por Shipping (listo para imprimir/PDF)
    // shipped   = despachado fisicamente al cliente S.E.I.P., Inc.
    $table->enum('status', ['draft', 'confirmed', 'shipped'])
          ->default('draft')
          ->comment('Estado del PS: draft | confirmed | shipped');

    $table->foreignId('created_by')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete()
          ->comment('Usuario que creo el PS');

    $table->foreignId('confirmed_by')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete()
          ->comment('Usuario que confirmo el PS');

    $table->timestamp('confirmed_at')->nullable();

    $table->foreignId('shipped_by')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete()
          ->comment('Usuario que marco el PS como despachado');

    $table->timestamp('shipped_at')->nullable();

    // Datos del pie de pagina (ultima pagina del PS impreso)
    $table->unsignedSmallInteger('total_boxes_404')->nullable()
          ->comment('Total de cajas tipo 404-10003');

    $table->unsignedSmallInteger('total_boxes_20x20')->nullable()
          ->comment('Total de cajas 20x20x8-1/2 pulgadas');

    // Firmas del pie de pagina (Fase 1: texto libre; Fase 2: firma digital)
    $table->string('reviewed_by_packaging', 100)->nullable()
          ->comment('Nombre o firma del revisor de Empaque');

    $table->string('reviewed_by_inspection', 100)->nullable()
          ->comment('Nombre o firma del revisor de Inspeccion de Empaque');

    $table->string('reviewed_by_cm', 100)->nullable()
          ->comment('Nombre o firma del revisor de Control de Materiales');

    // Notas adicionales (uso interno, no aparece en el PS impreso)
    $table->text('notes')->nullable();

    $table->timestamps();
    $table->softDeletes();

    $table->index('status');
    $table->index('slip_date');
});
```

---

### 4.4 Nueva Tabla `packing_slip_items`

**Migracion:** `YYYY_MM_DD_HHMMSS_create_packing_slip_items_table.php`

```php
Schema::create('packing_slip_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('packing_slip_id')
          ->constrained()
          ->cascadeOnDelete()
          ->comment('FK al Packing Slip padre');

    // Referencia al lote del sistema (nullable por si el item fue ingresado manualmente)
    $table->foreignId('lot_id')
          ->nullable()
          ->constrained()
          ->nullOnDelete()
          ->comment('FK al lote de produccion. Nullable para items manuales');

    // Referencia al Work Order del sistema
    $table->foreignId('work_order_id')
          ->nullable()
          ->constrained()
          ->nullOnDelete()
          ->comment('FK al Work Order. Nullable para items manuales');

    // ---------------------------------------------------------------
    // SNAPSHOTS INMUTABLES: valores al momento de crear el item del PS
    // Estos campos NO deben modificarse despues de creado el item.
    // Garantizan que el PS historico no cambie si los datos del lote
    // o del WO se modifican en el futuro.
    // ---------------------------------------------------------------

    // Numero de WO del Packing Slip: 'W0' + external_wo_number + lot_sequence
    // Ejemplo: 'W01980231001', 'W01982798003'
    $table->string('wo_number', 20)
          ->comment('WO# del Packing Slip. Formato: W0 + wo_externo + 3dig_lote');

    // PO# de la Purchase Order asociada al WO
    $table->string('po_number', 20)->nullable()
          ->comment('Snapshot del PO# al momento del despacho');

    // Numero de parte (Item#)
    $table->string('item_number', 30)->nullable()
          ->comment('Snapshot del numero de parte al momento del despacho');

    // Descripcion de la parte
    $table->string('description', 200)->nullable()
          ->comment('Snapshot de la descripcion de la parte al momento del despacho');

    // Cantidad: siempre es quantity_packed_final del lote (cantidad real, no teorica)
    $table->unsignedInteger('quantity')
          ->comment('Snapshot de quantity_packed_final. Cantidad real empacada');

    // Especificacion de etiqueta militar/aeronautica
    // Ejemplo: 'M83519/2-8', 'SAE AS81824/1-2', 'NAS1745-15'
    // Ingreso manual del operador de Shipping al crear el PS
    $table->string('label_spec', 50)->nullable()
          ->comment('Especificacion de etiqueta (ej: M83519/2-8). Ingreso manual');

    // Codigo de lote de produccion (campo Date del FPL-10)
    // Formato: YYMMDDXNN (ej: 250515A22)
    // Corresponde a lots.lot_number
    // PENDIENTE: confirmar con S.E.I.P., Inc. si este campo es requerido
    $table->string('lot_date_code', 20)->nullable()
          ->comment('Codigo de lote (ej: 250515A22). Pendiente confirmar con cliente final');

    // ---------------------------------------------------------------
    // Campos de presentacion / orden en el documento impreso
    // ---------------------------------------------------------------

    // Orden de aparicion dentro del Packing Slip
    $table->unsignedSmallInteger('sort_order')->default(0)
          ->comment('Orden de aparicion en el PS. Menor = primero');

    // Numero de pagina del PS (el PS puede ocupar varias paginas)
    $table->unsignedSmallInteger('page_number')->default(1)
          ->comment('Pagina del PS donde aparece este item');

    // Tipo de fila:
    // 'item'  = linea normal de lote con cantidad
    // 'total' = fila de total (suma de items del mismo WO, sin lot_id)
    $table->enum('row_type', ['item', 'total'])->default('item')
          ->comment('item = linea normal, total = fila de subtotal por WO');

    $table->timestamps();

    $table->index(['packing_slip_id', 'sort_order']);
    $table->index(['packing_slip_id', 'page_number']);
    $table->index('lot_id');
    $table->index('work_order_id');
});
```

---

### 4.5 Resumen de Cambios por Tabla

| Tabla | Accion | Campos afectados | Migracion |
|---|---|---|---|
| `lots` | MODIFICAR | + `quantity_packed_final`, `packaging_completed_at`, `packaging_completed_by`, `packing_slip_id` | 1 migracion nueva |
| `work_orders` | MODIFICAR | + `external_wo_number` | 1 migracion nueva |
| `packing_slips` | CREAR | Tabla completa | 1 migracion nueva |
| `packing_slip_items` | CREAR | Tabla completa | 1 migracion nueva |

**Total: 4 migraciones nuevas.**

**Orden de ejecucion (importante por dependencias FK):**
1. Crear `packing_slips`
2. Modificar `lots` (agrega FK a `packing_slips`)
3. Modificar `work_orders` (sin FK nuevas, puede ir en cualquier orden)
4. Crear `packing_slip_items` (depende de `packing_slips`, `lots`, `work_orders`)

---

## 5. Plan de Implementacion Actualizado

El plan se divide en 3 fases. Las fases 1 y 2 son las criticas para tener el modulo funcional. La fase 3 agrega refinamientos.

---

### Fase 1: Infraestructura de BD y Transicion de Empaque (Estimado: 3-4 dias)

**Objetivo:** Que los lotes cierren automaticamente y aparezcan en la cola de Shipping.

**Paso 1.1 — Migraciones de BD**
- Crear migracion `create_packing_slips_table`
- Crear migracion `add_packing_slip_readiness_to_lots_table` (4 campos nuevos en `lots`)
- Crear migracion `add_external_wo_number_to_work_orders_table`
- Crear migracion `create_packing_slip_items_table`

**Paso 1.2 — Modelos Eloquent**
- Crear `app/Models/PackingSlip.php` con relaciones, constantes de estado y `generateSlipNumber()`
- Crear `app/Models/PackingSlipItem.php` con relaciones y el metodo `buildWoNumber()`
- Actualizar `app/Models/Lot.php`:
  - Agregar relacion `belongsTo(PackingSlip::class)`
  - Agregar scope `readyForShipping()`
  - Agregar accesor `getQuantityForPackingSlipAttribute()` (devuelve `quantity_packed_final` o calcula si es null)
- Actualizar `app/Models/WorkOrder.php`:
  - Agregar campo `external_wo_number` al `$fillable`
  - Agregar metodo estatico `buildPackingSlipWoNumber()`

**Paso 1.3 — Observer de transicion**
- Crear `app/Observers/LotPackagingObserver.php`
- Registrar en `app/Providers/AppServiceProvider.php`
- Cubrir los 3 casos de `closure_decision` (`complete_lot`, `new_lot`, `close_as_is`)
- Agregar tests unitarios para el Observer (PHPUnit)

**Paso 1.4 — Poblar `external_wo_number` en datos existentes**
- Crear un Seeder o Artisan command para poblar `work_orders.external_wo_number` en los registros historicos que ya esten en BD
- Si los WOs no tienen este numero en el sistema actual, proveer una interfaz simple para que el operador lo ingrese manualmente

**Entregable de Fase 1:** Los lotes cierran automaticamente al registrar `closure_decision`. Los campos `quantity_packed_final`, `packaging_completed_at` y `packaging_completed_by` se populan correctamente.

---

### Fase 2: Modulo de Packing Slip en Livewire (Estimado: 5-7 dias)

**Objetivo:** Interfaz completa para crear, confirmar y despachar Packing Slips.

**Paso 2.1 — Componente: Cola de Shipping**
- Crear `app/Livewire/Shipping/ReadyForDispatch.php`
- Vista: tabla paginada con checkboxes, filtros y boton "Crear PS con seleccionados"
- Incluir columna de WO# de Packing Slip construido dinamicamente para preview

**Paso 2.2 — Componente: Creacion del Packing Slip**
- Crear `app/Livewire/Shipping/CreatePackingSlip.php`
- Wizard de 4 pasos (confirmacion de lotes, label specs, datos del pie, preview)
- Llamar a `PackingSlipService::createFromLots()` en el paso de confirmacion

**Paso 2.3 — Servicio de Packing Slip**
- Crear `app/Services/PackingSlipService.php`
- Metodos: `createFromLots()`, `confirmPackingSlip()`, `markAsShipped()`, `generateSlipNumber()`
- En `createFromLots()`: crear `packing_slip_items` con snapshots + filas de total por WO

**Paso 2.4 — Componente: Lista y Detalle de Packing Slips**
- Crear `app/Livewire/Shipping/PackingSlipList.php`
- Crear `app/Livewire/Shipping/PackingSlipDetail.php`
- Acciones por estado: confirmar (draft -> confirmed), despachar (confirmed -> shipped)

**Paso 2.5 — Permisos Spatie**
- Agregar permisos en `DatabaseSeeder` o seeder de permisos:
  `packing_slip.view`, `packing_slip.create`, `packing_slip.confirm`, `packing_slip.ship`, `packing_slip.manage`
- Asignar permisos a roles (Admin, Shipping, Empaque segun tabla de la Seccion 3.1.4)

**Paso 2.6 — Rutas y vistas blade**
- Agregar rutas en `routes/web.php` bajo el prefijo `/shipping`
- Crear layouts/vistas blade para los componentes Livewire

**Entregable de Fase 2:** Un Packing Slip puede crearse desde la cola de Shipping, confirmarse y marcarse como despachado. El sistema asigna `lots.packing_slip_id` correctamente.

---

### Fase 3: Generacion de PDF y mejoras (Estimado: 3-4 dias)

**Objetivo:** Generacion del PDF del Packing Slip en formato FPL-10, lista para enviar al cliente.

**Paso 3.1 — PDF del Packing Slip (FPL-10)**
- Evaluar libreria: Laravel-DOMPDF (ya incluido en muchos proyectos Laravel) o mPDF
- Crear template Blade para el PDF: cabecera, tabla de items con paginacion, pie de pagina
- Respetar el formato exacto del Excel FPL-10: fuentes, columnas, posicion de datos
- El PDF se genera al confirmar o al solicitar descarga desde la vista de detalle

**Paso 3.2 — Invoice FPL-12 (generado desde el PS)**
- Una vez que el PS esta en estado `shipped`, habilitar la generacion del Invoice FPL-12
- El Invoice es el PS + columnas de Unit Cost y Total monetario + cargos fijos
- Requiere que las partes tengan precio unitario configurado en la BD (campo `parts.unit_cost` si no existe)

**Paso 3.3 — Historial y auditoria**
- Agregar Activity Log en `PackingSlip` para registrar cada cambio de estado
- Vista de historial en el detalle del PS

**Paso 3.4 — Campo `Date` del Packing Slip (PENDIENTE confirmar con S.E.I.P., Inc.)**
- El campo `Date` (columna G del FPL-10) esta pendiente de confirmacion con el cliente final
- Actualmente se deja `NULL` o se mapea desde `lots.lot_number` (codigo de lote tipo `250515A22`)
- Una vez confirmado, actualizar el mapeo en `packing_slip_items.lot_date_code`

---

## 6. Riesgos y Dependencias

### 6.1 Riesgos Identificados

| # | Riesgo | Probabilidad | Impacto | Mitigacion |
|---|---|---|---|---|
| R-01 | `work_orders.external_wo_number` no tiene datos para los WOs existentes | Alta | Alto | Proveer interfaz de carga masiva; permitir ingreso manual antes de generar PS |
| R-02 | El Observer falla silenciosamente para algun caso de `closure_decision` | Media | Alto | Tests unitarios exhaustivos + log de errores en el Observer + vista de diagnostico |
| R-03 | Un lote cierra sin `packaging_records` (packed_pieces = 0) | Media | Medio | Validacion en el Observer: `getPackagingPackedPieces() > 0` antes de marcar como completed |
| R-04 | Lotes historicos con `packaging_status = 'completed'` ya existentes que no tienen `quantity_packed_final` | Alta | Medio | Script de migracion de datos para calcular y poblar `quantity_packed_final` en lotes ya completados |
| R-05 | El campo `Date` del FPL-10 no se resuelve antes de la implementacion | Media | Bajo | Se deja `lot_date_code = NULL` en Fase 2; se implementa en Fase 3 cuando S.E.I.P., Inc. confirme |
| R-06 | Permisos de Empaque para crear PS no definidos con precision | Baja | Bajo | Bloquear creacion para Empaque en Fase 2; confirmar con Frank antes de Fase 3 |

### 6.2 Dependencias Tecnicas

| Dependencia | Descripcion | Estado |
|---|---|---|
| Tabla `lots` con `packaging_records` | El Observer calcula `quantity_packed_final` sumando `packaging_records.packed_pieces` | Ya existe |
| Tabla `work_orders` con `purchase_order_id` | El Packing Slip obtiene el PO# via esta FK | Ya existe |
| Modelo `Lot` con `getPackagingPackedPieces()` | Metodo calculado necesario para el Observer | Verificar si ya existe o implementar |
| Libreria de PDF (DOMPDF/mPDF) | Necesaria para Fase 3 | Verificar si esta en `composer.json` |
| Spatie Laravel Permission | Necesaria para los nuevos permisos de Packing Slip | Ya instalado segun descripcion del proyecto |

### 6.3 Preguntas Respondidas (actualizacion 2026-03-08)

Las siguientes preguntas fueron respondidas por el equipo operativo en la sesion del 2026-03-08. El analisis de impacto arquitectural de cada respuesta se desarrolla en el documento `06_impacto_respuestas_pendientes_y_ajustes.md`.

| ID | Pregunta | Respuesta del usuario | Estado |
|---|---|---|---|
| P-05-01 | El campo `Date` (columna G) del FPL-10: confirmar con S.E.I.P., Inc. si es el `lot_number`, la fecha del PO u otro dato | Sin responder aun. Se vera con el cliente en proxima reunion. | **PENDIENTE** — Ver doc 06 para valor provisional |
| P-05-02 | El `label_spec` para cada numero de parte: confirmar si debe agregarse a la tabla `parts` o se mantiene ingreso manual | Por el momento sera ingreso manual mientras el cliente decide algo diferente. | **RESUELTO** — Ingreso manual en el wizard de creacion del PS |
| P-05-03 | Precio unitario por parte para el Invoice FPL-12: confirmar donde esta configurado actualmente (existe `parts.unit_cost`?) | La tabla `parts` tiene relacion con la tabla `prices` donde existe una relacion 1 a 1 (o por rangos): un numero de parte puede tener un rango de precios dependiendo de la cantidad del PO. | **RESUELTO** — Ver doc 06 para logica de seleccion por tier |
| P-05-04 | Permisos exactos del area de Empaque: puede Empaque crear un PS o solo verlo? | Esto se definira en otra fase del proyecto. Fuera de scope por ahora. | **DIFERIDO** — Fase futura. En esta fase Empaque tiene solo lectura sobre la cola de Shipping |
| P-05-05 | Los WOs historicos que ya estan en el sistema: tienen un numero externo de 7 digitos? Donde se encuentra ese numero? | Si existen, pero no habria un historico general ya que cada area maneja cosas diferentes. En esta fase aun no se esta contemplando. Quedara para una fase futura. | **DIFERIDO** — Solo WOs nuevos con `external_wo_number` en esta fase |

**Nota:** La unica pregunta que permanece abierta para proxima reunion con S.E.I.P., Inc. es P-05-01 (campo `Date` del FPL-10).

---

## 7. Diagrama de Componentes del Modulo de Packing Slip

```
┌─────────────────────────────────────────────────────────────────┐
│                    MODULO DE PACKING SLIP                        │
│                                                                   │
│  ┌─────────────────────────────┐                                 │
│  │  ReadyForDispatch (Livewire)│ <- Query: Lot::readyForShipping()│
│  │  Cola de lotes listos       │    (packaging_status='completed' │
│  │  Checkbox multi-seleccion   │     AND packing_slip_id IS NULL) │
│  └──────────────┬──────────────┘                                 │
│                 │ Seleccion + "Crear PS"                          │
│                 v                                                 │
│  ┌─────────────────────────────┐   ┌──────────────────────────┐  │
│  │  CreatePackingSlip (Livewire)│  │  PackingSlipService       │  │
│  │  Wizard 4 pasos:            │-->│  createFromLots()         │  │
│  │  1. Confirmar lotes         │   │  generateSlipNumber()     │  │
│  │  2. Label Specs             │   │  confirmPackingSlip()     │  │
│  │  3. Datos pie de pagina     │   │  markAsShipped()          │  │
│  │  4. Preview y confirmar     │   └──────────────────────────┘  │
│  └─────────────────────────────┘                                 │
│                                                                   │
│  ┌─────────────────────────────┐                                 │
│  │  PackingSlipList (Livewire) │ <- Tabla: packing_slips          │
│  │  Lista paginada de todos PS │                                 │
│  │  Filtros por estado, fecha  │                                 │
│  └──────────────┬──────────────┘                                 │
│                 │ Ver detalle                                     │
│                 v                                                 │
│  ┌─────────────────────────────┐   ┌──────────────────────────┐  │
│  │  PackingSlipDetail (Livewire)│  │  PDF Generator (Fase 3)   │  │
│  │  Cabecera + items + pie     │-->│  DOMPDF/mPDF              │  │
│  │  Botones: confirmar/despachar│  │  Template FPL-10          │  │
│  └─────────────────────────────┘   └──────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                    DATOS EN BD                                    │
│                                                                   │
│  lots ──────────────────────────────────> packing_slips          │
│  (packaging_status = 'completed')         (slip_number, status)  │
│  (quantity_packed_final = X)                      |              │
│  (packing_slip_id = FK) <─────────────────────────┤              │
│                                                   |              │
│  packaging_records                          packing_slip_items   │
│  (packed_pieces por sesion)               (snapshots inmutables) │
│  -> SUM = quantity_packed_final           (wo_number, quantity,  │
│                                            label_spec, po_number)│
│                                                                   │
│  work_orders                                                     │
│  (external_wo_number = '1980231')                               │
│  -> 'W0' + '1980231' + '001' = 'W01980231001' en PS item        │
└─────────────────────────────────────────────────────────────────┘
```

---

## 8. Referencias

- **Documento 01:** `01_shipping_list_analysis.md` — Estructura del Packing Slip FPL-10
- **Documento 02:** `02_invoice_analysis.md` — Invoice FPL-12 y relacion 1:1 con el PS
- **Documento 03:** `03_field_mapping_lista_envio_to_packing_slip.md` — Mapeo de campos FPL-02 a FPL-10
- **Documento 04:** `04_empaque_to_shipping_list_transition.md` — Opciones de diseno y preguntas abiertas (con respuestas del equipo)
- **Documento 06:** `06_impacto_respuestas_pendientes_y_ajustes.md` — Impacto arquitectural de las respuestas del 2026-03-08 y ajustes al plan
- **Migracion packaging_records:** `database/migrations/2026_02_27_070000_create_packaging_records_and_update_lots.php`
- **Migracion surplus_delivered:** `database/migrations/2026_03_06_062718_add_surplus_delivered_fields_to_lots_table.php`
- **Migracion packaging en lots:** `database/migrations/2026_02_06_061234_add_packaging_and_final_quality_to_lots_table.php`
- **Migracion work_orders:** `database/migrations/2025_12_10_090000_create_work_orders_table.php`
- **Migracion parts:** `database/migrations/2025_12_10_051116_create_parts_table.php`
- **Migracion prices / price_tiers:** `database/migrations/2025_12_10_070000_create_prices_table.php`
- **Modelo Lot:** `app/Models/Lot.php`
- **Modelo WorkOrder:** `app/Models/WorkOrder.php`
- **Modelo Part:** `app/Models/Part.php`
- **Modelo Price:** `app/Models/Price.php`
- **Modelo PriceTier:** `app/Models/PriceTier.php`

---

*Documento generado el 2026-03-08. Version 1.0. Basado en las respuestas del equipo operativo de FlexCon a las preguntas del documento 04, el analisis de las migraciones existentes y el contexto del proyecto FlexCon Tracker.*
