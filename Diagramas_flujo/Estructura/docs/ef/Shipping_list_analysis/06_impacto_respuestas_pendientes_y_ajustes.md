# Analisis Tecnico: Impacto de Respuestas Pendientes y Ajustes al Plan

**Fecha:** 2026-03-08
**Elaborado por:** Arquitecto de Software - FlexCon Tracker
**Version:** 1.0
**Proposito:** Analizar el impacto arquitectural de las respuestas recibidas del equipo operativo de FlexCon a las preguntas pendientes del documento `05_decisiones_confirmadas_y_plan_implementacion.md`, y producir los ajustes concretos al plan de implementacion que se derivan de dichas respuestas.
**Documentos previos:**
- `01_shipping_list_analysis.md` — Estructura del Packing Slip FPL-10
- `02_invoice_analysis.md` — Invoice FPL-12 y relacion 1:1 con el Packing Slip
- `03_field_mapping_lista_envio_to_packing_slip.md` — Mapeo de campos y reglas de negocio
- `04_empaque_to_shipping_list_transition.md` — Opciones de diseno y preguntas abiertas
- `05_decisiones_confirmadas_y_plan_implementacion.md` — Plan de implementacion v1.0 con preguntas pendientes

---

## 1. Tabla Resumen de Respuestas y Estado

| ID | Pregunta original | Respuesta del usuario | Impacto | Estado |
|---|---|---|---|---|
| P-05-01 | Campo `Date` del FPL-10 (columna G): que dato espera S.E.I.P., Inc.? | Sin responder. Se vera con el cliente. | Bajo (campo nullable en BD; valor provisional en Fase 3) | **PENDIENTE** |
| P-05-02 | `label_spec`: tabla `parts` o ingreso manual? | Ingreso manual mientras el cliente decide. | Bajo (ya previsto en doc 05; confirmar campo en BD y paso del wizard) | **RESUELTO** |
| P-05-03 | Precio unitario para Invoice FPL-12: existe `parts.unit_cost`? | La tabla `parts` tiene relacion con `prices`. Existe logica de rangos de precio por cantidad del PO. | Alto (logica de seleccion de tier para el Invoice) | **RESUELTO** |
| P-05-04 | Permisos de Empaque sobre el Packing Slip | Se definira en otra fase. Fuera de scope por ahora. | Bajo (Empaque queda con solo lectura en esta fase) | **DIFERIDO** |
| P-05-05 | WOs historicos: tienen numero externo de 7 digitos en BD? | Si existen, pero cada area maneja cosas diferentes. No se contempla en esta fase. | Bajo (solo WOs nuevos con `external_wo_number` en esta fase) | **DIFERIDO** |

---

## 2. Analisis Detallado por Respuesta

---

### 2.1 Campo `Date` del FPL-10 (P-05-01) — PENDIENTE

#### Contexto

El campo `Date` aparece en la columna G del Packing Slip FPL-10 y actualmente se mapea desde `lots.lot_number` (que tiene formato `YYMMDDXNN`, por ejemplo `250515A22`). Sin embargo, no se ha confirmado con S.E.I.P., Inc. si ese es el dato que esperan en ese campo.

#### Respuesta del usuario

> Sin responder aun. Lo vera con el cliente.

#### Impacto Arquitectural

Este campo ya fue previsto en el esquema de `packing_slip_items` como `lot_date_code` (varchar 20, nullable) en el documento 05. El impacto de no tener la respuesta en esta fase es **bajo** porque:

1. El campo `lot_date_code` ya existe en el esquema propuesto y es nullable.
2. El PDF del Packing Slip (Fase 3) puede mostrar el campo en blanco o con el valor provisional hasta que se confirme.
3. No bloquea el desarrollo de Fase 1 ni Fase 2.

#### Valor Provisional (hasta confirmacion con S.E.I.P., Inc.)

Se propone usar `lots.lot_number` como valor provisional para `packing_slip_items.lot_date_code` en el momento de crear el item del PS. Este valor se copia como snapshot inmutable. Si S.E.I.P., Inc. confirma que el dato correcto es otro (fecha del PO, fecha de manufactura, otro codigo), se debera:

1. Agregar el campo origen correcto al snapshot en `PackingSlipService::createFromLots()`.
2. Crear una migracion que actualice `lot_date_code` en los PS existentes si el cambio es retroactivo (poco probable).

#### Decision Provisional DP-01

> Usar `lots.lot_number` como fuente de `packing_slip_items.lot_date_code`. Campo nullable en BD. En el PDF de Fase 3, mostrar el campo como `lot_number`. Pendiente confirmacion con S.E.I.P., Inc. antes de lanzar el PDF a produccion.

---

### 2.2 Campo `label_spec`: Ingreso Manual (P-05-02) — RESUELTO

#### Contexto

El campo `label_spec` es la especificacion de etiqueta militar/aeronautica que aparece en cada fila del Packing Slip (por ejemplo: `M83519/2-8`, `SAE AS81824/1-2`). La pregunta era si este dato debe venir de la tabla `parts` (como atributo fijo del numero de parte) o debe ingresarse manualmente al crear el PS.

#### Respuesta del usuario

> Por el momento sera ingreso manual mientras el cliente decide algo diferente.

#### Impacto Arquitectural

**En Base de Datos:**

El campo `packing_slip_items.label_spec` (varchar 50, nullable) ya esta contemplado en el esquema del documento 05. No se requiere ningun cambio adicional a la tabla `parts` en esta fase.

**En el Wizard de Creacion del PS (Paso 2):**

El paso 2 del wizard `CreatePackingSlip` debe mostrar un campo de texto libre por cada lote/item seleccionado, con la etiqueta "Label Spec / Spec. Etiqueta". Las reglas de UI son:

| Aspecto | Definicion |
|---|---|
| Tipo de campo | `<input type="text">` con validacion de formato opcional |
| Longitud maxima | 50 caracteres (alineado con `label_spec varchar(50)`) |
| Obligatoriedad | Opcional (nullable). No bloquear la creacion del PS si queda vacio |
| Placeholder | Ej: `M83519/2-8` |
| Persistencia | Se guarda como snapshot en `packing_slip_items.label_spec` al confirmar el PS |
| Edicion posterior | Solo si el PS esta en estado `draft`. No editable si esta `confirmed` o `shipped` |

**Ruta de migracion futura (si el cliente decide moverlo a `parts`):**

Cuando se decida vincular `label_spec` a la tabla `parts`, el flujo seria:
1. Agregar campo `label_spec` (varchar 50, nullable) a la tabla `parts`.
2. El wizard pre-llenara el campo con `part.label_spec` si existe, permitiendo sobreescritura manual.
3. El snapshot en `packing_slip_items.label_spec` no cambia (sigue siendo texto libre al momento del PS).

#### Decision Confirmada D-06-02

> En esta fase `label_spec` es ingreso manual en el paso 2 del wizard. Campo nullable en `packing_slip_items`. Sin vinculo con la tabla `parts` hasta nueva instruccion del cliente. El campo en `parts` no se agrega en esta fase.

---

### 2.3 Precio Unitario para Invoice FPL-12 (P-05-03) — RESUELTO

#### Contexto

El Invoice FPL-12 es una extension del Packing Slip que agrega columnas de `Unit Cost` y `Total Amount` por cada linea. La pregunta era si existe un campo `parts.unit_cost` o donde esta configurado el precio unitario.

#### Respuesta del usuario

> La tabla `parts` tiene relacion con la tabla `prices` donde existe una relacion 1 a 1 (o por rangos): un numero de parte puede tener un rango de precios dependiendo de la cantidad del PO.

#### Estructura Real de la BD (verificada en migraciones y modelos)

El analisis de los archivos `2025_12_10_070000_create_prices_table.php`, `app/Models/Price.php` y `app/Models/PriceTier.php` revela la siguiente estructura real:

```
parts (1) ──hasMany──> prices (N)
                          |
                          └──hasMany──> price_tiers (N)
```

**Tabla `prices`:**
- `part_id` FK a `parts`
- `sample_price` — Precio de muestra / fallback
- `workstation_type` — Tipo de estacion: `table`, `machine`, `semi_automatic`
- `effective_date` — Fecha de vigencia (informativa; el campo `active` controla la vigencia real)
- `active` — Solo puede haber un precio activo por `(part_id, workstation_type)`

**Tabla `price_tiers`:**
- `price_id` FK a `prices`
- `min_quantity` — Cantidad minima del rango
- `max_quantity` — Cantidad maxima del rango (NULL = sin limite, ej: 100,000+)
- `tier_price` — Precio unitario para ese rango de cantidad

**Rangos por tipo de estacion (definidos en `Price::TIER_CONFIG`):**

| Tipo de estacion | Rango 1 | Rango 2 | Rango 3 | Rango 4 |
|---|---|---|---|---|
| `table` | 1 - 999 | 1,000 - 10,999 | 11,000 - 99,999 | 100,000+ |
| `machine` | 1 - 9,999 | 10,000 - 49,999 | 50,000+ | — |
| `semi_automatic` | 2,000 - 10,000 | 11,000+ | — | — |

**Metodo clave en `Price`:**

```php
// Devuelve el tier_price del rango que coincide con $quantity,
// o $this->sample_price como fallback si ningun tier cubre la cantidad.
public function getPriceForQuantity(int $quantity): ?float
```

#### Problema de Diseno para el Invoice FPL-12

El Invoice necesita un precio unitario **unico** por linea de Packing Slip Item, pero la estructura de `prices` tiene una dimension adicional: `workstation_type`. Esto significa que para un mismo numero de parte y una misma cantidad, pueden existir hasta 3 precios activos distintos (uno por tipo de estacion).

**Pregunta de negocio que surge:** ?Cual de los tres tipos de estacion (`table`, `machine`, `semi_automatic`) se usa para facturar al cliente S.E.I.P., Inc.?

El analisis del Packing Slip FPL-10 y del Invoice FPL-12 no muestra el tipo de estacion como campo visible. La facturacion al cliente es por cantidad, no por tipo de proceso interno. Por lo tanto, se propone la siguiente logica de seleccion.

#### Logica de Seleccion del Precio para el Invoice (Propuesta)

**Contexto adicional:** La cantidad relevante para seleccionar el tier es la cantidad de la Purchase Order (PO), no la cantidad de un lote individual. Esto es consistente con la respuesta del usuario: "un rango de precios **dependiendo de la cantidad del PO**".

**Algoritmo de seleccion:**

```
DADO un packing_slip_item con:
  - part_id (via work_order -> purchase_order -> part)
  - quantity_po (cantidad total de la PO, no del lote individual)

1. Obtener el Price activo para la parte:
   Price::getActivePriceForPart($partId)
   -> Retorna el Price con active=true mas reciente por effective_date
   -> Si hay multiples workstation_types activos, usar el siguiente criterio de prioridad:
      a) Si el lot tiene workstation_type registrado en packaging_records, usar ese tipo
      b) Si no, usar el tipo con el precio mas reciente (effective_date DESC)
      c) Fallback final: usar sample_price del primer Price activo encontrado

2. Con el Price obtenido, calcular el precio unitario:
   $price->getPriceForQuantity($poQuantity)
   -> Busca el tier donde min_quantity <= $poQuantity <= max_quantity
   -> Si ningun tier coincide, usa $price->sample_price como fallback

3. Resultado: precio unitario para la linea del Invoice
```

**Nota importante:** El metodo `getPriceForQuantity()` ya existe en el modelo `Price` y hace exactamente este calculo. No es necesario reimplementarlo.

#### Campos Adicionales en `packing_slip_items` para el Invoice

Para soportar el Invoice sin recalcular precios en el futuro (principio de snapshot inmutable), se propone agregar dos campos a `packing_slip_items` en el momento de generar el Invoice:

| Campo | Tipo | Descripcion |
|---|---|---|
| `unit_price` | `decimal(10,4)` nullable | Snapshot del precio unitario al generar el Invoice. NULL hasta que el Invoice sea generado. |
| `price_tier_id` | `foreignId` nullable | Referencia al tier de precio usado. Permite auditoria de como se calculo el precio. |
| `price_source` | `enum('tier','sample','manual')` nullable | Indica si el precio vino de un tier, del sample_price, o fue ingresado manualmente (por si el admin necesita corregirlo). |

Alternativamente, el Invoice puede calcularse dinamicamente cada vez que se genera el PDF (sin persistir en BD), siempre que el Price activo no haya cambiado. Sin embargo, dado que los precios pueden cambiar en el tiempo, el snapshot es la opcion mas robusta para documentos de facturacion.

#### Ajuste al Plan de Implementacion (Fase 3, Paso 3.2)

El documento 05 decia en el paso 3.2:

> "Requiere que las partes tengan precio unitario configurado en la BD (campo `parts.unit_cost` si no existe)"

Este enunciado es incorrecto: NO existe `parts.unit_cost`. El precio esta en `prices` + `price_tiers`. El paso 3.2 actualizado es:

> **Paso 3.2 revisado — Invoice FPL-12:**
> - Usar `Price::getActivePriceForPart($partId)->getPriceForQuantity($poQuantity)` para obtener el `unit_price` de cada linea
> - La cantidad del PO (`$poQuantity`) se obtiene de `work_orders -> purchase_order -> quantity`
> - Persistir `unit_price`, `price_tier_id` y `price_source` en `packing_slip_items` al generar el Invoice
> - El Invoice solo puede generarse cuando el PS esta en estado `shipped`
> - Agregar una migracion `add_invoice_price_fields_to_packing_slip_items_table` para los 3 campos nuevos

#### Decision Confirmada D-06-03

> El precio unitario para el Invoice FPL-12 se obtiene de la tabla `prices` + `price_tiers` usando `Price::getPriceForQuantity($poQuantity)`. La cantidad de referencia para el tier es la cantidad de la PO (no del lote). El precio se persiste como snapshot en `packing_slip_items.unit_price` al generar el Invoice. No existe ni se crea `parts.unit_cost`.

---

### 2.4 Permisos de Empaque sobre el Packing Slip (P-05-04) — DIFERIDO

#### Respuesta del usuario

> Esto se definira en otra fase del proyecto. Fuera de scope por ahora.

#### Impacto Arquitectural

Este tema queda **fuera del alcance de esta fase**. La decision tiene las siguientes implicaciones inmediatas:

**Alcance de permisos en esta fase (Fase 1 y 2):**

| Permiso | Admin | Shipping | Empaque |
|---|---|---|---|
| `packing_slip.view` | Si | Si | Si (solo lectura de la cola de Shipping) |
| `packing_slip.create` | Si | Si | **No** |
| `packing_slip.confirm` | Si | Si | **No** |
| `packing_slip.ship` | Si | Si | **No** |
| `packing_slip.manage` | Si | No | **No** |

**Comportamiento del modulo para el rol Empaque en esta fase:**
- El rol `empaque` puede ver la cola de lotes listos (`ReadyForDispatch`) en modo de solo lectura (sin checkboxes de seleccion ni boton "Crear PS").
- El rol `empaque` puede ver la lista de Packing Slips (`PackingSlipList`) y el detalle de cada uno, pero sin botones de accion (confirmar, despachar).
- Esta restriccion se implementa en los componentes Livewire con guards de `$this->authorize('packing_slip.create')` antes de mostrar los controles de accion.

**Para fases futuras:**
- Se dejara un TODO en el seeder de permisos y en el componente `ReadyForDispatch` indicando que el acceso de creacion para `empaque` esta pendiente de definicion.
- La decision debe tomarse con Frank antes de Fase 3.

#### Decision Provisional D-06-04

> En esta fase el rol `empaque` tiene unicamente `packing_slip.view`. La capacidad de crear PS queda reservada a `admin` y `shipping`. Revisar en la fase siguiente con Frank.

---

### 2.5 WOs Historicos con Numero Externo (P-05-05) — DIFERIDO

#### Respuesta del usuario

> Si existen, pero no habria un historico general ya que cada area maneja cosas diferentes. En esta fase aun no se esta contemplando. Quedara para una fase futura.

#### Impacto Arquitectural

Este alcance confirma que el campo `work_orders.external_wo_number` **solo se poblara para WOs nuevos** que se ingresen al sistema a partir de esta fase. Los WOs existentes en la BD que no tienen este numero quedan con `external_wo_number = NULL`.

**Consecuencias directas:**

1. **Cola de Shipping:** Un lote cuyo WO tiene `external_wo_number = NULL` tecnicamente puede aparecer en la cola (si `packaging_status = 'completed'`), pero al intentar incluirlo en un PS, el sistema no podra construir el `wo_number` del Packing Slip. Se requiere una validacion.

2. **Validacion en `PackingSlipService::createFromLots()`:** Antes de crear el PS, verificar que todos los lotes seleccionados tienen un WO con `external_wo_number IS NOT NULL`. Si alguno no lo tiene, mostrar un aviso al usuario de Shipping con la opcion de ingresar el numero manualmente desde la misma vista.

3. **No se crea un Seeder/Command de carga masiva en esta fase.** El documento 05 mencionaba la posibilidad de un Seeder para WOs historicos; eso queda para una fase futura.

**Ajuste al paso 1.4 del Plan de Implementacion:**

El documento 05 decia en Paso 1.4:

> "Crear un Seeder o Artisan command para poblar `work_orders.external_wo_number` en los registros historicos"

Este paso **queda fuera de alcance**. En su lugar, el paso 1.4 se redefine como:

> **Paso 1.4 revisado:** Agregar un campo editable `external_wo_number` al formulario de creacion y edicion de Work Orders en la interfaz existente, para que los operadores puedan ingresar el numero al crear WOs nuevos. Agregar validacion en `PackingSlipService` que rechace lotes de WOs sin `external_wo_number` y muestre mensaje de accion al usuario.

#### Decision Confirmada D-06-05

> Solo los WOs nuevos (creados a partir de esta fase) tendran `external_wo_number`. Los WOs historicos quedan con NULL. Se agrega validacion en el servicio de PS para manejar este caso. La carga historica queda para una fase futura.

---

## 3. Diagrama de Relaciones Actualizado: parts -> prices -> price_tiers

El siguiente diagrama muestra la relacion completa entre las tablas relevantes para el calculo del precio del Invoice FPL-12:

```
parts
  id (PK)
  number           <-- numero de parte (ej: M83519/2-8)
  item_number      <-- Item# del FPL-10
  description
  ...
    |
    | hasMany
    v
prices
  id (PK)
  part_id (FK)     <-- un numero de parte puede tener multiples registros de precio
  sample_price     <-- precio base / fallback si ningun tier coincide
  workstation_type <-- 'table' | 'machine' | 'semi_automatic'
  effective_date   <-- informativo; 'active' es el que controla la vigencia
  active           <-- solo 1 precio activo por (part_id, workstation_type)
    |
    | hasMany (ordenado por min_quantity ASC)
    v
price_tiers
  id (PK)
  price_id (FK)
  min_quantity     <-- cantidad minima del rango
  max_quantity     <-- cantidad maxima (NULL = sin limite superior)
  tier_price       <-- precio unitario para este rango
```

**Flujo de obtencion del precio para el Invoice:**

```
Invoice FPL-12 necesita el Unit Price de la linea X
    |
    v
Obtener part_id via:
  packing_slip_items.lot_id
    -> lots.work_order_id
    -> work_orders.purchase_order_id
    -> purchase_orders.part_id
    |
    v
Price::getActivePriceForPart($partId)
    -> prices WHERE part_id = X AND active = 1
    -> Con relacion tiers cargada (eager load)
    |
    v
$price->getPriceForQuantity($poQuantity)
    -> Busca en price_tiers: min_quantity <= $poQuantity AND (max_quantity IS NULL OR max_quantity >= $poQuantity)
    -> Si encuentra tier: retorna tier->tier_price
    -> Si no encuentra tier: retorna $price->sample_price (fallback)
    |
    v
Resultado: unit_price para packing_slip_items.unit_price (snapshot)
```

**Caso de multiples workstation_types activos:**

Si una parte tiene precios activos para mas de un tipo de estacion (lo cual es posible segun el modelo), se aplica la siguiente prioridad para seleccionar el registro `Price` correcto:

| Prioridad | Condicion | Accion |
|---|---|---|
| 1 | El lote tiene `workstation_type` conocido (via `packaging_records`) | Usar el `Price` activo con ese `workstation_type` |
| 2 | No hay informacion de tipo de estacion | Usar el `Price` activo con `effective_date` mas reciente |
| 3 | No hay ningun `Price` activo | `unit_price = NULL`; Invoice muestra advertencia |

Esta logica de prioridad debe implementarse en `PackingSlipService` (no en el modelo `Price` directamente, ya que implica contexto del lote).

---

## 4. Resumen de Ajustes al Plan de Implementacion

### 4.1 Cambios en Fase 1

| Paso | Estado anterior (doc 05) | Estado actualizado (doc 06) |
|---|---|---|
| Paso 1.4 | Crear Seeder/Command para poblar `external_wo_number` en WOs historicos | **REEMPLAZADO:** Agregar `external_wo_number` al formulario de creacion/edicion de WOs. Sin migracion de datos historicos en esta fase. |

### 4.2 Cambios en Fase 2

| Paso | Estado anterior (doc 05) | Estado actualizado (doc 06) |
|---|---|---|
| Paso 2.2 — Wizard Paso 2 | Ingresar `label_spec` por cada lote (mencionado brevemente) | **DETALLADO:** Campo texto libre, 50 chars, opcional, placeholder `M83519/2-8`. Editable solo en estado `draft`. |
| Paso 2.5 — Permisos Spatie | Empaque recibe `packing_slip.view` segun tabla | **CONFIRMADO Y ACOTADO:** Empaque SOLO recibe `packing_slip.view`. No recibe `packing_slip.create`. Componentes Livewire ocultan controles de accion para este rol. |
| Paso 2.3 — Servicio PS | `createFromLots()` sin validacion de `external_wo_number` | **AJUSTE:** Agregar validacion en `createFromLots()`: rechazar si algun WO tiene `external_wo_number = NULL`. Mostrar aviso al usuario con opcion de ingresar el numero. |

### 4.3 Cambios en Fase 3

| Paso | Estado anterior (doc 05) | Estado actualizado (doc 06) |
|---|---|---|
| Paso 3.2 — Invoice FPL-12 | "Requiere `parts.unit_cost`" | **CORREGIDO:** No existe `parts.unit_cost`. Usar `Price::getActivePriceForPart($partId)->getPriceForQuantity($poQuantity)`. Agregar migracion para campos `unit_price`, `price_tier_id`, `price_source` en `packing_slip_items`. |
| Paso 3.4 — Campo `Date` del FPL-10 | Pendiente confirmar | **ACLARADO:** Usar `lots.lot_number` como valor provisional. Campo nullable en BD. No bloquea desarrollo. Confirmar con S.E.I.P., Inc. antes del lanzamiento del PDF. |

### 4.4 Nueva Migracion Requerida (Fase 3)

**`YYYY_MM_DD_HHMMSS_add_invoice_price_fields_to_packing_slip_items_table.php`**

```php
Schema::table('packing_slip_items', function (Blueprint $table) {
    // Precio unitario snapshot al generar el Invoice
    // NULL hasta que el Invoice sea generado (Fase 3)
    $table->decimal('unit_price', 10, 4)
          ->nullable()
          ->after('label_spec')
          ->comment('Snapshot del precio unitario al generar el Invoice FPL-12');

    // Referencia al tier de precio utilizado (para auditoria)
    $table->foreignId('price_tier_id')
          ->nullable()
          ->after('unit_price')
          ->constrained('price_tiers')
          ->nullOnDelete()
          ->comment('Tier de precio utilizado para calcular unit_price');

    // Fuente del precio: tier calculado, sample_price, o ingreso manual
    $table->enum('price_source', ['tier', 'sample', 'manual'])
          ->nullable()
          ->after('price_tier_id')
          ->comment('Fuente del unit_price: tier|sample|manual');
});
```

Esta migracion se ejecuta **en Fase 3**, no en Fase 1 ni Fase 2. La tabla `packing_slip_items` funciona sin estos campos hasta que se implemente el Invoice.

---

## 5. Tabla de Decisiones Nuevas (este documento)

| ID | Decision | Estado |
|---|---|---|
| D-06-01 | Valor provisional de `lot_date_code` = `lots.lot_number`. Pendiente confirmacion con S.E.I.P., Inc. | **PROVISIONAL** |
| D-06-02 | `label_spec` es ingreso manual en el wizard. Sin vinculo con `parts` en esta fase. | **CONFIRMADO** |
| D-06-03 | Precio del Invoice via `Price::getPriceForQuantity($poQuantity)`. Cantidad de referencia = cantidad de la PO. Snapshot en `packing_slip_items.unit_price`. | **CONFIRMADO** |
| D-06-04 | Rol `empaque` tiene solo `packing_slip.view` en esta fase. Definicion de permisos adicionales queda para fase futura. | **CONFIRMADO (FASE ACTUAL)** |
| D-06-05 | Solo WOs nuevos tendran `external_wo_number`. Sin migracion de datos historicos en esta fase. Validacion en `PackingSlipService` para manejar WOs sin numero externo. | **CONFIRMADO** |

---

## 6. Tabla de Decisiones Actualizada (consolidado con doc 05)

Esta tabla reemplaza y extiende la Tabla de Decisiones Definitivas del documento 05 con las actualizaciones de este documento.

| ID | Decision | Fuente | Estado |
|---|---|---|---|
| D-01 | Transicion via Observer (Opcion B). Tres eventos de cierre ya implementados. | Doc 05 | **CONFIRMADO** |
| D-02 | Persistir `quantity_packed_final` en `lots` al cierre de empaque. | Doc 05 | **CONFIRMADO** |
| D-03 | PS usa `quantity_packed_final` (real), no `lots.quantity` (teorica). | Doc 05 | **CONFIRMADO** |
| D-04 | Agregar `packaging_completed_at`, `packaging_completed_by` a `lots`. | Doc 05 | **CONFIRMADO** |
| D-05 | Agregar `packing_slip_id` a `lots`. | Doc 05 | **REQUERIDO** |
| D-06 | Cola de Shipping sin agrupacion fija por semana. | Doc 05 | **CONFIRMADO** |
| D-07 | Agregar `external_wo_number` a `work_orders`. | Doc 05 | **CONFIRMADO** |
| D-08 | Prefijo WO PS es `'W0'`, construido en modelo/servicio (no en Livewire). | Doc 05 | **CONFIRMADO** |
| D-09 | Observer marca lote original como completado al registrar `closure_decision` (sin esperar nuevo lote). | Doc 05 | **CONFIRMADO** |
| D-10 | Sin notificaciones activas en Fase 1. Cola de Shipping es suficiente. | Doc 05 | **CONFIRMADO** |
| D-11 | Permisos PS: Admin y Shipping pueden crear/confirmar/despachar. Empaque solo view. | Doc 05 + Doc 06 | **CONFIRMADO (fase actual)** |
| D-12 | Reversion bloqueada si `packing_slip_id IS NOT NULL`. | Doc 05 | **CONFIRMADO** |
| D-06-01 | `lot_date_code` provisional = `lots.lot_number`. Pendiente S.E.I.P., Inc. | Doc 06 | **PROVISIONAL** |
| D-06-02 | `label_spec` ingreso manual en wizard. Sin campo en `parts`. | Doc 06 | **CONFIRMADO** |
| D-06-03 | Precio Invoice via `price_tiers` por cantidad de PO. Sin `parts.unit_cost`. | Doc 06 | **CONFIRMADO** |
| D-06-04 | Empaque: solo `packing_slip.view`. Permisos adicionales en fase futura. | Doc 06 | **CONFIRMADO** |
| D-06-05 | Solo WOs nuevos con `external_wo_number`. Sin carga historica en esta fase. | Doc 06 | **CONFIRMADO** |

---

## 7. Preguntas Abiertas (solo las que siguen pendientes)

| ID | Pregunta | Prioridad | Para quien | Bloquea |
|---|---|---|---|---|
| P-06-01 | Campo `Date` (columna G) del FPL-10: confirmar con S.E.I.P., Inc. si esperan `lot_number` (formato `YYMMDDXNN`), la fecha de despacho, la fecha del PO u otro dato. | Alta | Frank / S.E.I.P., Inc. | Solo bloquea el lanzamiento del PDF FPL-10 a produccion (Fase 3). No bloquea Fase 1 ni 2. |

---

## 8. Referencias

- **Documento 01:** `01_shipping_list_analysis.md` — Estructura del Packing Slip FPL-10
- **Documento 02:** `02_invoice_analysis.md` — Invoice FPL-12 y relacion 1:1 con el PS
- **Documento 03:** `03_field_mapping_lista_envio_to_packing_slip.md` — Mapeo de campos FPL-02 a FPL-10
- **Documento 04:** `04_empaque_to_shipping_list_transition.md` — Opciones de diseno y preguntas abiertas
- **Documento 05:** `05_decisiones_confirmadas_y_plan_implementacion.md` — Plan de implementacion v1.0
- **Migracion parts:** `database/migrations/2025_12_10_051116_create_parts_table.php`
- **Migracion prices / price_tiers:** `database/migrations/2025_12_10_070000_create_prices_table.php`
- **Migracion unique price constraint:** `database/migrations/2026_01_22_060906_add_unique_active_price_constraint_to_prices_table.php`
- **Modelo Part:** `app/Models/Part.php`
- **Modelo Price:** `app/Models/Price.php`
- **Modelo PriceTier:** `app/Models/PriceTier.php`

---
