# Analisis Tecnico: Formal Shipping List (Packing Slip) - FPL-10

**Fecha:** 2026-03-05
**Elaborado por:** Arquitecto de Software - FlexCon Tracker
**Version:** 1.0
**Archivo fuente analizado:** `FPL-10 Shipping List 2025.xlsx`
**Clave del documento:** FPL-10, Revision 02

---

## 1. Resumen Ejecutivo

La **Shipping List (Packing Slip)** es el documento formal y externo que **Ensambles Formula (FlexCon)** emite al momento de despachar un embarque fisico hacia su cliente **S.E.I.P., Inc.** (San Marcos, CA). Es el equivalente comercial-logistico de un albaran de entrega: acredita exactamente que productos, en que cantidades, de que Work Orders y con que especificaciones de etiqueta (Label Spec) se estan enviando.

El archivo Excel `FPL-10 Shipping List 2025.xlsx` contiene **44 hojas**, cada una representando una pagina de una Packing Slip numerada. Se identificaron Packing Slips desde el numero **SL001229** hasta **SL001249**, lo que representa aproximadamente 21 embarques distintos (varios con 2 o 3 paginas cada uno). Esto indica un volumen operativo de al menos un embarque semanal.

La aplicacion FlexCon Tracker ya cuenta con un modulo llamado "Sent List" (Lista de Envio Preliminar) que cubre el flujo de planificacion interna y produccion. Sin embargo, **la Packing Slip formal es un documento diferente**: se genera al final del proceso, consolida los lotes realmente completados, empacados e inspeccionados, y produce el documento que sale fisicamente con la mercancia y es enviado al cliente.

**Conclusion ejecutiva:** Se requiere implementar un nuevo modulo de **Formal Shipping (Packing Slip)** que tome como insumo los lotes completados y empacados del sistema actual, y genere el documento FPL-10 en formato imprimible/PDF, con numeracion correlativa automatica, siguiendo exactamente la estructura del Excel analizado.

---

## 2. Analisis del Excel - Estructura Detallada

### 2.1 Estructura General del Archivo

| Concepto | Valor |
|---|---|
| Total de hojas | 44 |
| Packing Slips identificadas | SL001229 a SL001249 (21 distintas) |
| Hojas por Packing Slip | 1 a 3 paginas (segun volumen de items) |
| Filas por hoja | 39 a 61 filas (incluye encabezado y cuerpo) |
| Columnas de datos activas | 8 columnas utiles (en 15 columnas totales del sheet) |
| Formato de identificacion de hoja | `SL{NUMERO} Pag {N}` |

### 2.2 Estructura del Encabezado (Filas 1 a 10 de cada hoja)

Cada hoja del Excel replica un encabezado estandarizado con la siguiente informacion:

```
Fila 1:  ENSAMBLES FORMULA                        (nombre de la empresa emisora)
Fila 2:  [vacio] | SHIPPING LIST | [vacio] | Clave: FPL-10
Fila 3:  [vacio] | [vacio]       | [vacio] | Revision: 02
Fila 4:  Direccion: 330 Rocky Woods Lane, Bigfork, Montana 59911
Fila 5:  PH# 425-466-2184 | Frank@flexconinc.com
Fila 6:  "Sold to:" | [nombre] | "Shipped to:" | [nombre] | Packing Slip #XXXXXX
Fila 7:  S.E.I.P., Inc.  |  S.E.I.P., Inc.  |  [FOB info]
Fila 8:  915 Armorlite Dr. | 915 Armorlite Dr. | F.O.B: Tecate, Ca.
Fila 9:  San Marcos, Ca. 92069  |  San Marcos, Ca. 92069
Fila 10: [vacio]  | [vacio]  | [vacio]  | DATE: May-28-2025
```

**Campos del encabezado identificados:**

| Campo | Tipo | Ejemplo | Requerido |
|---|---|---|---|
| Empresa emisora | Texto fijo | "ENSAMBLES FORMULA" | Si (constante) |
| Clave del documento | Texto fijo | "FPL-10" | Si (constante) |
| Revision | Texto fijo | "02" | Si (constante) |
| Direccion emisor | Texto fijo | "330 Rocky Woods Lane, Bigfork, MT 59911" | Si (constante) |
| Telefono emisor | Texto fijo | "PH# 425-466-2184" | Si (constante) |
| Email emisor | Texto fijo | "Frank@flexconinc.com" | Si (constante) |
| Sold to (nombre) | Texto | "S.E.I.P., Inc." | Si |
| Sold to (direccion) | Texto | "915 Armorlite Dr., San Marcos, CA 92069" | Si |
| Shipped to (nombre) | Texto | "S.E.I.P., Inc." | Si |
| Shipped to (direccion) | Texto | "915 Armorlite Dr., San Marcos, CA 92069" | Si |
| Numero de Packing Slip | Numerico secuencial | "Packing Slip #001249" | Si |
| F.O.B. | Texto | "F.O.B: Tecate, Ca." | Si (constante) |
| Fecha del envio | Fecha | "May-28-2025" | Si |

### 2.3 Encabezado de la Tabla de Items (Fila 11)

```
Work Order | PO # | Item no | Description | Quantity | Date | Label Spec
```

| Columna | Nombre | Tipo de Dato | Ejemplo | Nullable |
|---|---|---|---|---|
| Col B | Work Order | String (codigo) | "W01980231001" | No |
| Col C | PO # | Integer | 49032 | Si (algunos no tienen PO) |
| Col D | Item no | String (codigo de parte) | "189-10257" | No |
| Col E | Description | String | "STS H-ML-8" | No |
| Col F | Quantity | Integer | 100000 | No |
| Col G | Date | String (codigo de lote) | "250515A22" | Si |
| Col H | Label Spec | String (especificacion militar/aeronautica) | "M83519/2-8" | Si |

### 2.4 Filas de Datos (Cuerpo de la Tabla)

**Patron de datos observado:**

Los items se agrupan por PO / parte. Cuando una misma PO tiene multiples Work Orders (lotes), aparecen las filas de cada lote y despues una **fila de total** que consolida la cantidad de esa PO:

```
(None, 'W01982798001', 49110, '189-10179', 'STS H-M-3', 58900, None, 'M83519/1-3')
(None, 'W01982798002', 49110, '189-10179', 'STS H-M-3', 34000, None, 'M83519/1-3')
(None, 'W01982798003', 49110, '189-10179', 'STS H-M-3',  7100, None, 'M83519/1-3')
(None, None, None, None, 'Total:', 100000, None, None)  <- fila de total
```

**Observaciones clave sobre los datos:**

1. **Work Order Number format:** Sigue el patron `W` + secuencia numerica larga + secuencia de 3 digitos (ej: `W01980231001`). Este formato **difiere** del formato usado en la app actual (`WO-YYYY-XXXXX`). Implica que el sistema actual y el Excel pueden ser sistemas paralelos o que los WO del Excel son del sistema legacy del cliente.

2. **PO # nullable:** Algunas lineas no tienen PO# (columna = None). Esto sucede cuando el lote proviene de WOs sin PO directamente asociado o cuando es un backorder.

3. **Campo Date (columna G):** No es una fecha calendario sino un **codigo de lote** con formato `YYMMDD` + letra + secuencia (ej: `250515A22` = produccion del 15-May-2025, lote A, maquina/mesa 22). Este codigo es el `lot_number` del modelo `Lot` existente en la app.

4. **Label Spec:** Especificacion militar o aeronautica bajo la cual se certifica el componente. Es un atributo de la parte (Part), no del envio. Puede estar vacio para partes sin especificacion aplicable.

5. **Misma parte en multiples POs:** Una misma descripcion de parte puede aparecer en distintas POs dentro de la misma Packing Slip (ej: `189-10635 STS H-HC-2-0-H` aparece en POs 48806, 48807, 48808 en la misma hoja).

### 2.5 Pie de Pagina (Ultima pagina de cada Packing Slip)

Solo la **ultima pagina** de cada Packing Slip contiene el pie con campos de firma:

```
Total de cajas 404-10003:    [cantidad]   Revision de Empaque realizado por: [firma]
Total cajas 20x20x8-1/2":   [cantidad]   Inspeccion de Empaque realizado por: [firma]
                             Revision CM: [firma]
```

| Campo | Tipo | Descripcion | Requerido |
|---|---|---|---|
| Total de cajas 404-10003 | Integer | Cantidad de cajas tipo 404-10003 usadas | Si |
| Total cajas 20x20x8-1/2" | Integer | Cantidad de cajas de ese tamano | Si |
| Revision de Empaque | Firma/Nombre | Persona que reviso el empaque | Si |
| Inspeccion de Empaque | Firma/Nombre | Inspector que valido el empaque | Si |
| Revision CM | Firma/Nombre | Revision del Control de Materiales | Si |

### 2.6 Ejemplos de Packing Slips Analizadas

| Packing Slip | Fecha | Paginas | Items aprox. | Partes distintas |
|---|---|---|---|---|
| SL001249 | May-28-2025 | 2 | 17 WOs | 9 partes |
| SL001248 | May-21-2025 | 2 | 19 WOs | 12 partes |
| SL001247 | May-14-2025 | 2 | 20 WOs | 13 partes |
| SL001246 | May-07-2025 | 2 | 22 WOs | 11 partes |

**Patron de embarque:** Frecuencia semanal (cada miercoles aproximadamente), con 15-25 Work Orders por embarque y multiples partes distintas.

### 2.7 Estructura de Datos Resumida (Schema del Excel)

```
PackingSlip
  ├── packing_slip_number: string (ej: "001249")
  ├── date: date
  ├── fob_location: string (constante "Tecate, Ca.")
  ├── sold_to: { name, address, city_state_zip }
  ├── shipped_to: { name, address, city_state_zip }
  ├── pages: integer
  ├── items[]:
  │     ├── work_order_number: string
  │     ├── po_number: integer (nullable)
  │     ├── item_number: string (codigo de parte)
  │     ├── description: string
  │     ├── quantity: integer
  │     ├── lot_date_code: string (nullable)
  │     └── label_spec: string (nullable)
  └── footer (ultima pagina):
        ├── total_boxes_404_10003: integer
        ├── total_boxes_20x20: integer
        ├── packaging_reviewer: string
        ├── packaging_inspector: string
        └── cm_reviewer: string
```

---

## 3. Estado Actual - Lo que Existe en la Aplicacion

### 3.1 Modulo "Sent List" (Lista de Envio Preliminar)

La aplicacion ya tiene un modulo de **Sent List** que sirve como **lista de planificacion interna**. Su proposito es diferente al de la Packing Slip formal:

| Aspecto | Sent List (actual) | Packing Slip (Excel) |
|---|---|---|
| **Proposito** | Planificar capacidad y asignar POs a produccion | Documento formal de despacho al cliente |
| **Momento** | Inicio del ciclo (antes de producir) | Fin del ciclo (al despachar) |
| **Audiencia** | Interna (Materiales, Produccion, Calidad, Envios) | Externa (cliente S.E.I.P.) |
| **Contenido** | POs, horas disponibles, capacidad | WOs completados, lotes, cantidades reales enviadas |
| **Numero** | No tiene numero publico secuencial | Numero correlativo publico (SL001249) |
| **PDF exportable** | No implementado | Formato estandarizado FPL-10 |

### 3.2 Componentes Existentes Relevantes

**Modelos:**

| Modelo | Tabla | Relevancia para Shipping |
|---|---|---|
| `SentList` | `sent_lists` | Contiene el workflow de aprobaciones departamentales |
| `WorkOrder` | `work_orders` | Tiene `wo_number`, `sent_pieces`, `scheduled_send_date`, `actual_send_date` |
| `PurchaseOrder` | `purchase_orders` | Tiene `po_number`, `quantity`, `unit_price` |
| `Part` | `parts` | Tiene `number` (= item_no), `description` |
| `Lot` | `lots` | Tiene `lot_number`, `quantity`, `status`, `inspection_status`, `packaging_status` |
| `PackagingRecord` | `packaging_records` | Registra cajas y piezas empacadas por lote |

**Livewire Components:**

| Componente | Ruta | Descripcion |
|---|---|---|
| `ShippingListDisplay` | `/admin/sent-lists/display` | Vista operativa interna del estado de la lista de envio |
| `SentListDepartmentView` | N/A | Vista de departamento especifico |
| `CapacityWizard` | `/admin/capacity-wizard` | Calculo de capacidad y creacion de SentList |

**Rutas existentes:**

```
GET  /admin/sent-lists              -> SentListController@index
GET  /admin/sent-lists/display      -> ShippingListDisplay (Livewire)
GET  /admin/sent-lists/{id}         -> SentListController@show
GET  /admin/sent-lists/{id}/edit    -> SentListController@edit
PUT  /admin/sent-lists/{id}         -> SentListController@update
DELETE /admin/sent-lists/{id}       -> SentListController@destroy
```

**Base de datos - Tabla `sent_lists`:**

```sql
id, po_id, shift_ids (JSON), num_persons,
start_date, end_date, total_available_hours, used_hours, remaining_hours,
status (pending/confirmed/canceled),
current_department, department_history (JSON),
materials_approved_at, materials_approved_by,
production_approved_at, production_approved_by,
inspection_approved_at, inspection_approved_by,
shipping_approved_at, shipping_approved_by,
notes, timestamps, soft_deletes
```

**Tabla `work_orders`:**

```sql
id, wo_number (WO-YYYY-XXXXX), purchase_order_id, sent_list_id,
assembly_mode, required_hours, status_id,
sent_pieces, scheduled_send_date, actual_send_date, opened_date,
eq, pr, comments, timestamps, soft_deletes
```

**Tabla `lots`:**

```sql
id, work_order_id, lot_number, description, quantity, status,
raw_material_batch_numbers (JSON),
inspection_status, inspection_comments, inspection_completed_at/by,
material_status, packaging_status, packaging_comments,
packaging_inspected_at/by, viajero_received, viajero_received_at/by,
closure_decision, surplus_received, timestamps, soft_deletes
```

**Tabla `packaging_records`:**

Registra el detalle de piezas empacadas por lote, con conteos de cajas.

### 3.3 Flujo Actual vs Flujo Requerido

**Flujo actual en la app:**

```
PO recibida -> Validacion de precio -> SentList (Capacity Wizard)
-> Asignacion WOs -> Produccion (pesadas) -> Calidad (inspeccion)
-> Empaque (packaging records) -> [FIN - no hay documento de despacho]
```

**Flujo requerido para cerrar el ciclo:**

```
[...igual que arriba...] -> Empaque completado
-> Crear Packing Slip (seleccionar lotes listos)
-> Generar PDF formato FPL-10
-> Registrar despacho (fecha, numero, firmas)
-> Actualizar WOs como despachados
-> Marcar Packing Slip como enviada
```

---

## 4. Gap Analysis

### 4.1 Gaps de Modelo de Datos

| Gap | Severidad | Descripcion |
|---|---|---|
| G-01 | Alta | No existe una tabla `packing_slips` que represente el documento formal de despacho |
| G-02 | Alta | No existe numeracion correlativa automatica para Packing Slips (SL001229...SL001249+) |
| G-03 | Alta | No hay relacion entre una Packing Slip y los lotes que consolida |
| G-04 | Media | El modelo `Part` no tiene campo `label_spec` (especificacion militar/aeronautica). **NOTA: Segun contexto del cliente, este campo se ingresa manualmente en el Packing Slip. La modificacion a `parts` esta suspendida hasta confirmar (ver P-11).** |
| G-05 | Media | La tabla `packaging_records` no consolida las cajas totales por tipo de caja (404-10003 vs 20x20x8.5) |
| G-06 | Media | No hay campos de firma digital para empaque, inspeccion de empaque y CM en el documento de despacho |
| G-07 | Media | El formato `wo_number` en la app (`WO-2025-00001`) difiere del formato del Packing Slip (`W01980231001`). **ACLARACION RECIBIDA:** El numero del Packing Slip se construye como `W0` + `[WO# de FPL-02]` + `[numero de lote]`. El WO# de FPL-02 es un numero entero de 7 digitos (ej: `1980231`) que NO es el mismo que el `wo_number` de la app. Pendiente confirmar si estos son WOs del sistema legacy del cliente o si la app debe almacenar el numero del WO de la Lista de Envio en un campo adicional. |
| G-08 | Baja | No existe una tabla de clientes (`customers`/`clients`) - actualmente los datos de S.E.I.P. estan hardcodeados en el Excel |

### 4.2 Gaps de Logica de Negocio

| Gap | Severidad | Descripcion |
|---|---|---|
| G-09 | Alta | No existe logica para seleccionar que lotes se incluyen en una Packing Slip especifica |
| G-10 | Alta | No hay proceso de "cierre de envio" que marque lotes/WOs como oficialmente despachados |
| G-11 | Alta | No existe generacion de PDF en formato FPL-10 |
| G-12 | Media | No hay validacion de que solo lotes con empaque completado y aprobado puedan incluirse en un despacho |
| G-13 | Media | No existe el concepto de "paginas multiples" para una Packing Slip grande |
| G-14 | Baja | No hay historial de Packing Slips enviadas al cliente con su estado (enviada, confirmada por cliente, etc.) |

### 4.3 Gaps de Interfaz de Usuario

| Gap | Severidad | Descripcion |
|---|---|---|
| G-15 | Alta | No existe vista para crear/componer una Packing Slip |
| G-16 | Alta | No existe vista de prevista de impresion del documento FPL-10 |
| G-17 | Media | No existe listado de Packing Slips generadas con filtros y busqueda |
| G-18 | Media | No existe flujo de aprobacion/firmas para la Packing Slip antes de despachar |
| G-19 | Baja | No existe dashboard de envios con metricas (total piezas enviadas por semana/mes por parte) |

### 4.4 Gaps de Integracion

| Gap | Severidad | Descripcion |
|---|---|---|
| G-20 | Alta | No hay mecanismo para exportar/descargar el PDF de la Packing Slip |
| G-21 | Media | No existe integracion con la vista actual de `ShippingListDisplay` para iniciar la creacion de un despacho |
| G-22 | Baja | No hay notificaciones (email/sistema) al momento de generar una Packing Slip |

---

## 4b. Flujo de Datos: Lista de Envio (FPL-02) hacia Packing Slip (FPL-10)

Esta seccion documenta como los datos del documento interno FPL-02 (Lista de Envio) se transforman en los campos del documento externo FPL-10 (Packing Slip / Shipping List), segun el contexto confirmado por el cliente.

### 4b.1 Estructura de la Lista de Envio (FPL-02)

El archivo FPL-02 contiene **22 hojas**, una por cada semana de envio (desde enero 2025 hasta junio 2025). Cada hoja tiene la siguiente estructura:

**Encabezado (filas 1-5):**

```
Fila 1:  ENSAMBLES FORMULA
Fila 2:  [vacio] | LISTA DE ENVIO | [vacio] | Clave: FPL-02
Fila 3:  [vacio] | [vacio]        | [vacio] | Revision: 06
Filas 4-5: vacías
```

**Encabezado de columnas (fila 6):**

| Col | Nombre | Tipo | Descripcion |
|-----|--------|------|-------------|
| A | DOC | String | Tipo de fila: `WO` (linea principal), `Viajero` (detalle de viajero de crimp), o vacio (sub-lote) |
| B | WO # | Integer/String | Numero de WO puro (ej: `1980231`) en fila principal; `[WO] [lote]` (ej: `1980231 001`) en sub-filas |
| C | Item # | String | Numero de parte (ej: `189-10257`). Solo en fila principal |
| D | Descripcion | String | Descripcion de la parte (ej: `STS H-ML-8`) |
| E | Cantidad WO | Integer | Cantidad total comprometida en la Work Order |
| F | Piezas Enviadas | Integer/String | Piezas ya enviadas en ciclos anteriores (puede ser `0` o texto ` -   `) |
| G | Cantidad pendiente | Integer | Cantidad aun por enviar (= E - F) |
| H | Cantidad a Enviar | Integer | Cantidad que se enviara en el ciclo actual (puede ser menor que G si se hace envio parcial) |
| I | Fecha Progr. A Enviar | Date | Fecha programada del envio |
| J | Fecha de Envio | Date | Fecha real/confirmada del envio |
| K | Fecha de Apertura | Date | Fecha en que se abrio la Work Order |
| L | Eq | String | Equipo o linea de produccion asignada (ej: `S01`, `S21`, `A01`, `T01`) |
| M | PR | Integer | Prioridad del WO (1 = alta, 2 = normal, 4 = baja) |

**Tipos de filas en el cuerpo:**

```
Fila tipo WO (col A = "WO"):
  → Es la linea principal del Work Order
  → Col B contiene el numero de WO puro (entero: 1980231)
  → Contiene todos los datos del WO

Fila tipo sub-lote (col A = vacio, col B = "[WO] [lote]"):
  → Es un detalle de lote especifico dentro del WO
  → Ej: "1980231 001" = lote 1 del WO 1980231
  → Col E contiene la cantidad de piezas de ese lote especifico
  → Col F puede contener comentarios (ej: "Viajero de 66,000")

Fila tipo Total (col D = "Total:"):
  → Sumatoria de sub-lotes de un WO con multiples lotes
  → Col E contiene el total acumulado

Fila de seccion (ej: col B = "Mesas", "Maquinas", "Mesas Semi-Automaticas"):
  → Separadores visuales entre grupos de WOs por tipo de equipo
  → No contienen datos de produccion
```

**Ejemplo concreto (hoja 05-28-2025, WO 1980231):**

```
Fila 62: WO | 1980231 | 189-10257 | STS H-ML-8 | 100000 | 0 | 100000 | 100000 | 2025-05-28 | 2025-05-28 | 2025-03-12 | S21 | 4
Fila 63:    | 1980231 001 | | STS H-ML-8 | 100000 | | | | | | | |
```

### 4b.2 Construccion del Numero de Work Order en el Packing Slip

El campo `Work Order` del Packing Slip (FPL-10) no se toma directamente de la Lista de Envio. Se **construye** con la siguiente formula:

```
W.O. del Packing Slip = "W0" + [WO# de FPL-02] + [numero_de_lote]
```

**Descomposicion del ejemplo:**

```
W0  +  1980231  +  001  =  W01980231001

Donde:
  W0       = prefijo constante (siempre "W0", dos caracteres)
  1980231  = numero de WO de la Lista de Envio (columna B de FPL-02)
  001      = numero del sub-lote (los 3 digitos al final de "1980231 001" en FPL-02)
```

**Regla de construccion en el sistema:**

Cuando el operador selecciona el sub-lote `1980231 001` desde la Lista de Envio para incluirlo en la Packing Slip, el sistema extrae los componentes y construye:

```php
// En el sistema Laravel
$woNumber = '1980231';      // Numero WO puro de FPL-02
$loteNum  = '001';          // Numero del sub-lote (3 digitos con cero a izquierda)
$packingSlipWO = 'W0' . $woNumber . $loteNum;  // -> 'W01980231001'
```

La columna `packing_slip_items.wo_number` almacena el resultado construido (`W01980231001`), no el numero original del WO de la app (`WO-2025-XXXXX`).

### 4b.3 Obtencion del PO# en el Packing Slip

El campo `PO #` del Packing Slip **no aparece en la Lista de Envio (FPL-02)**. Se obtiene de forma automatica via la relacion del sistema:

```
Lista de Envio (FPL-02)
       |
       | WO# = 1980231
       v
Tabla work_orders
  [wo_number relacionado con purchase_order_id]
       |
       v
Tabla purchase_orders
  [po_number = 49032]  <-- este es el valor que aparece en el Packing Slip
```

El sistema realiza un JOIN automatico para obtener el `po_number` a partir del `work_order_id`:

```php
// Pseudocodigo del JOIN en el sistema
$workOrder = WorkOrder::find($workOrderId);
$poNumber  = $workOrder->purchaseOrder->po_number;  // -> 49032
```

Este dato se guarda como snapshot en `packing_slip_items.po_number` al momento de crear el item del Packing Slip.

### 4b.4 Tabla de Mapeo Campo por Campo

| Campo FPL-10 (Packing Slip) | Origen | Mecanismo | Estado |
|-----------------------------|--------|-----------|--------|
| **Work Order** | FPL-02 columna B (WO#) + sub-lote | Construido: `W0 + WO# + lote` | Automatico |
| **PO #** | Tabla `purchase_orders` | JOIN via `work_orders.purchase_order_id` | Automatico |
| **Item no** | FPL-02 columna C (Item#) | Copia directa | Automatico |
| **Description** | FPL-02 columna D (Descripcion) | Copia directa | Automatico |
| **Quantity** | FPL-02 columna H (Cantidad a Enviar) del sub-lote | Copia directa de la cantidad del lote especifico | Automatico |
| **Date** | ? | ? | **PENDIENTE CONFIRMAR** (ver P-10) |
| **Label Spec** | Sin origen en FPL-02 | Ingreso manual del operador | Manual |
| **Packing Slip #** | Secuencia correlativa del sistema | Auto-generado (SL001250, SL001251...) | Automatico |
| **DATE** (encabezado) | Fecha del despacho | Campo de la Packing Slip | Automatico/Manual |
| **Sold to / Shipped to** | Datos del cliente | Configuracion fija (S.E.I.P., Inc.) | Constante |
| **F.O.B.** | Ubicacion de despacho | Configuracion fija (`Tecate, Ca.`) | Constante |

### 4b.5 Notas Arquitecturales sobre la Obtencion del PO

La arquitectura actual del sistema ya tiene la relacion `WorkOrder -> PurchaseOrder`. Por tanto:

- **No se necesita** almacenar el PO# directamente en la Lista de Envio
- **Si se necesita** que el modelo `WorkOrder` tenga una relacion `belongsTo(PurchaseOrder::class)` funcional y con datos correctos
- Al crear un `PackingSlipItem`, el sistema debe hacer el lookup del PO en ese momento y guardarlo como snapshot en el campo `packing_slip_items.po_number`
- Esto protege la integridad historica: si el PO cambia en el futuro, el snapshot del Packing Slip permanece inalterado

### 4b.6 Nota sobre el Campo `label_spec` en la Tabla `parts`

El analisis arquitectural previo proponia agregar `label_spec` a la tabla `parts`. Segun el contexto confirmado por el cliente:

- El `label_spec` es actualmente **ingresado manualmente** por el operador en cada Packing Slip
- El cliente no tiene claro si se necesita un catalogo que ligue parte -> especificacion
- **Por ahora:** el campo `packing_slip_items.label_spec` existente (nullable) es suficiente para la Fase 1
- **Nota en la propuesta de migracion:** La linea `ALTER TABLE parts ADD COLUMN label_spec` queda **suspendida** hasta confirmar requerimiento. Ver P-11.

---

## 5. Requerimientos Funcionales

### 5.1 Requerimientos de Creacion de Packing Slip

**RF-01:** El sistema debe permitir crear una nueva Packing Slip seleccionando lotes desde la **Lista de Envio (FPL-02)** existente. Los lotes deben cumplir las siguientes condiciones:
- `status = 'completed'`
- `inspection_status = 'approved'`
- `packaging_status` indica empaque terminado
- No este ya incluido en otra Packing Slip

**RF-01b:** Al seleccionar los lotes desde la Lista de Envio, el sistema debe presentar los WOs agrupados por su numero de WO, mostrando los sub-lotes numerados (ej: `1980231 001`, `1980231 002`, etc.) para que el usuario pueda seleccionar cuales se incluyen en el despacho.

**RF-02:** El sistema debe auto-generar el numero de Packing Slip de forma secuencial y correlativa (ej: SL001250 al continuar desde SL001249).

**RF-02b:** El sistema debe auto-construir el numero de Work Order del Packing Slip con el formato: `W0` + `[WO#]` + `[lote_numero]`. Ejemplo: WO `1980231` con lote `001` genera `W01980231001`.

**RF-02c:** El PO# de cada linea del Packing Slip debe obtenerse automaticamente via la relacion `work_orders -> purchase_orders` en el sistema. El PO no se ingresa manualmente.

**RF-03:** La Packing Slip debe registrar: fecha de despacho, numero de paginas, FOB, sold-to, shipped-to.

**RF-04:** Cada item de la Packing Slip debe capturar: Work Order number (construido automaticamente), PO# (obtenido via JOIN), Item number, Description, Quantity, Lot code (Date), Label Spec.

**RF-05:** La ultima pagina debe capturar: total de cajas por tipo, nombre del revisor de empaque, nombre del inspector de empaque, nombre del revisor CM.

**RF-06:** El sistema debe calcular automaticamente el numero de paginas basado en la cantidad de items (considerando el limite de filas por pagina, aproximadamente 30-35 items por pagina).

### 5.2 Requerimientos de Generacion de PDF

**RF-07:** El sistema debe generar un PDF que reproduzca fielmente el formato FPL-10 del Excel, con el mismo layout de encabezado, tabla de items y pie de firma.

**RF-08:** El PDF debe ser multi-pagina cuando los items excedan el limite por hoja.

**RF-09:** El PDF debe poder descargarse y/o imprimirse directamente desde la interfaz.

**RF-10:** El nombre del archivo PDF debe seguir el formato: `PackingSlip_{NUMERO}_{FECHA}.pdf`.

### 5.3 Requerimientos de Gestion de Packing Slips

**RF-11:** El sistema debe mostrar un listado de todas las Packing Slips con filtros por: rango de fechas, numero de Packing Slip, estado.

**RF-12:** Una Packing Slip debe tener estados: `draft` (borrador), `generated` (PDF generado), `shipped` (despachada).

**RF-13:** Al marcar una Packing Slip como despachada, el sistema debe actualizar el campo `actual_send_date` de los Work Orders afectados.

**RF-14:** Una Packing Slip en estado `shipped` no debe poder modificarse (solo visualizarse y re-descargarse).

### 5.4 Requerimientos de Integracion con el Flujo Actual

**RF-15:** Desde el modulo de `ShippingListDisplay` (vista del departamento Envios), debe existir un boton/accion para iniciar la creacion de una Packing Slip con los lotes listos.

**RF-16 (SUSPENDIDO - ver P-11):** El campo `label_spec` a nivel de catalogo de `parts` esta **pendiente de confirmacion**. Por ahora, el campo `label_spec` en `packing_slip_items` es nullable y el operador lo ingresa manualmente al crear cada linea del Packing Slip. No se modifica la tabla `parts` hasta recibir confirmacion del cliente sobre si se necesita un catalogo de especificaciones por parte.

**RF-17:** Los tipos de caja disponibles (404-10003, 20x20x8-1/2") deben ser configurables o al menos estar documentados como constantes del sistema.

---

## 6. Arquitectura Propuesta

### 6.1 Nuevas Tablas de Base de Datos

#### Tabla: `packing_slips`

```sql
CREATE TABLE packing_slips (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slip_number     VARCHAR(20)    NOT NULL UNIQUE,   -- 'SL001250'
    slip_date       DATE           NOT NULL,
    status          ENUM('draft','generated','shipped') DEFAULT 'draft',
    fob_location    VARCHAR(100)   NOT NULL DEFAULT 'Tecate, Ca.',
    sold_to_name    VARCHAR(200)   NOT NULL,
    sold_to_address TEXT           NOT NULL,
    shipped_to_name VARCHAR(200)   NOT NULL,
    shipped_to_address TEXT        NOT NULL,
    total_pages     TINYINT        NOT NULL DEFAULT 1,
    -- Pie de pagina (ultima pagina)
    total_boxes_404    SMALLINT   NULLABLE,  -- cajas tipo 404-10003
    total_boxes_20x20  SMALLINT   NULLABLE,  -- cajas 20x20x8-1/2"
    packaging_reviewer VARCHAR(150) NULLABLE,
    packaging_inspector VARCHAR(150) NULLABLE,
    cm_reviewer        VARCHAR(150) NULLABLE,
    -- Control
    notes              TEXT        NULLABLE,
    shipped_at         TIMESTAMP   NULLABLE,
    generated_at       TIMESTAMP   NULLABLE,
    created_by         BIGINT UNSIGNED NULLABLE FK -> users(id),
    shipped_by         BIGINT UNSIGNED NULLABLE FK -> users(id),
    created_at         TIMESTAMP,
    updated_at         TIMESTAMP,
    deleted_at         TIMESTAMP NULLABLE  -- SoftDeletes
);
```

#### Tabla: `packing_slip_items`

```sql
CREATE TABLE packing_slip_items (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    packing_slip_id  BIGINT UNSIGNED NOT NULL FK -> packing_slips(id) CASCADE DELETE,
    lot_id           BIGINT UNSIGNED NOT NULL FK -> lots(id),
    work_order_id    BIGINT UNSIGNED NOT NULL FK -> work_orders(id),
    purchase_order_id BIGINT UNSIGNED NULLABLE FK -> purchase_orders(id),
    -- Datos desnormalizados (snapshot al momento del despacho)
    wo_number        VARCHAR(50)  NOT NULL,   -- snapshot de wo_number
    po_number        VARCHAR(50)  NULLABLE,   -- snapshot de po_number
    item_number      VARCHAR(100) NOT NULL,   -- snapshot de part.number (item_no)
    description      VARCHAR(255) NOT NULL,   -- snapshot de part.description
    quantity         INT          NOT NULL,   -- cantidad real despachada
    lot_date_code    VARCHAR(50)  NULLABLE,   -- snapshot de lot.lot_number
    label_spec       VARCHAR(100) NULLABLE,   -- snapshot de part.label_spec
    sort_order       SMALLINT     NOT NULL DEFAULT 0,  -- orden de aparicion en el doc
    page_number      TINYINT      NOT NULL DEFAULT 1,
    created_at       TIMESTAMP,
    updated_at       TIMESTAMP,

    INDEX idx_psi_slip (packing_slip_id),
    INDEX idx_psi_lot (lot_id),
    INDEX idx_psi_wo (work_order_id)
);
```

#### Modificacion a tabla `parts` (migracion):

> **NOTA - PENDIENTE (ver P-11):** La siguiente migracion esta **suspendida** hasta confirmar con el cliente si se requiere un catalogo de especificaciones por parte. Segun el contexto actual, el campo `label_spec` se ingresa manualmente en cada linea del Packing Slip (`packing_slip_items.label_spec`) y no existe certeza de si se necesita este campo a nivel de catalogo de partes. No implementar hasta recibir confirmacion.

```sql
-- SUSPENDIDO - NO IMPLEMENTAR HASTA CONFIRMAR REQUERIMIENTO (P-11)
-- ALTER TABLE parts
-- ADD COLUMN label_spec VARCHAR(100) NULLABLE AFTER item_number
-- COMMENT 'Especificacion militar/aeronautica (ej: M83519/2-8, SAE AS81824/1-2)';
```

#### Modificacion a tabla `lots` (migracion):

```sql
ALTER TABLE lots
ADD COLUMN packing_slip_id BIGINT UNSIGNED NULLABLE
AFTER surplus_received_by,
ADD FOREIGN KEY (packing_slip_id) REFERENCES packing_slips(id) ON DELETE SET NULL;
```

Esto permite consultar rapidamente: "este lote, ya fue despachado? en cual Packing Slip?"

### 6.2 Modelos Laravel

#### Modelo: `PackingSlip`

```
app/Models/PackingSlip.php

Relaciones:
- hasMany(PackingSlipItem::class)     -- items de la lista
- hasMany(Lot::class)                  -- lotes incluidos
- belongsTo(User::class, 'created_by') -- quien creo el documento
- belongsTo(User::class, 'shipped_by') -- quien marco como despachado

Constantes:
STATUS_DRAFT = 'draft'
STATUS_GENERATED = 'generated'
STATUS_SHIPPED = 'shipped'

Metodos de interes:
- generateSlipNumber(): string         -- genera SL{siguiente_correlativo}
- generatePDF(): response              -- usa libreria PDF
- markAsShipped(int $userId): bool     -- cambia status y actualiza WOs
- canBeModified(): bool                -- solo draft puede modificarse
```

#### Modelo: `PackingSlipItem`

```
app/Models/PackingSlipItem.php

Relaciones:
- belongsTo(PackingSlip::class)
- belongsTo(Lot::class)
- belongsTo(WorkOrder::class)
- belongsTo(PurchaseOrder::class)
```

### 6.3 Livewire Components Propuestos

| Componente | Ruta | Descripcion |
|---|---|---|
| `PackingSlipList` | `/admin/packing-slips` | Listado con filtros y acciones |
| `PackingSlipCreate` | `/admin/packing-slips/create` | Wizard para seleccionar lotes y componer el documento |
| `PackingSlipShow` | `/admin/packing-slips/{id}` | Vista de detalle + boton de generar PDF + boton despachar |
| `PackingSlipPrintView` | `/admin/packing-slips/{id}/print` | Vista solo de impresion (sin nav) que se convierte a PDF |

### 6.4 Servicio de Generacion de PDF

**Libreria recomendada:** `barryvdh/laravel-dompdf` (ya ampliamente usada con Laravel, madura y compatible con Tailwind inline styles).

```
app/Services/PackingSlipPDFService.php

Responsabilidades:
- Recibir un PackingSlip model con sus items cargados
- Renderizar la vista Blade de impresion
- Calcular paginacion interna (items por pagina)
- Generar el PDF con dompdf
- Retornar el PDF como response (descarga) o path en storage
```

**Vista Blade de impresion:**

```
resources/views/packing-slips/print.blade.php

Layout: sin navegacion, ancho de pagina Letter (8.5" x 11")
Fuente: monoespaciada o sans-serif limpia
Encabezado: replicar exactamente el formato FPL-10
Tabla: Work Order, PO#, Item No, Description, Quantity, Date, Label Spec
Pie: solo en ultima pagina, campos de firma
```

### 6.5 Rutas Propuestas

```php
// En routes/admin.php, dentro del middleware auth|admin o auth|Envios

Route::middleware(['auth', 'verified', 'role:admin|Envios'])->group(function () {
    Route::get('/packing-slips', PackingSlipList::class)->name('packing-slips.index');
    Route::get('/packing-slips/create', PackingSlipCreate::class)->name('packing-slips.create');
    Route::get('/packing-slips/{packingSlip}', PackingSlipShow::class)->name('packing-slips.show');
    Route::get('/packing-slips/{packingSlip}/print', PackingSlipPrintView::class)
        ->name('packing-slips.print');
    Route::get('/packing-slips/{packingSlip}/pdf', [PackingSlipController::class, 'downloadPDF'])
        ->name('packing-slips.pdf');
});
```

### 6.6 Diagrama de Relaciones de la Nueva Arquitectura

```
parts
  └── label_spec (nuevo campo)

packing_slips
  ├── id, slip_number, slip_date, status
  ├── sold_to_*, shipped_to_*, fob_location
  ├── footer fields (cajas, firmas)
  ├── created_by -> users
  └── shipped_by -> users

packing_slip_items
  ├── packing_slip_id -> packing_slips
  ├── lot_id          -> lots
  ├── work_order_id   -> work_orders
  ├── purchase_order_id -> purchase_orders (nullable)
  ├── snapshot fields (wo_number, po_number, item_number, description,
  │                    quantity, lot_date_code, label_spec)
  ├── sort_order, page_number
  └── timestamps

lots
  └── packing_slip_id -> packing_slips (nuevo campo, nullable)
```

### 6.7 Logica de Numeracion de Packing Slips

```php
// En PackingSlip::generateSlipNumber()
public static function generateSlipNumber(): string
{
    $last = static::withTrashed()
        ->orderByRaw("CAST(SUBSTRING(slip_number, 3) AS UNSIGNED) DESC")
        ->first();

    if (!$last) {
        return 'SL001229'; // Continuar desde el ultimo del Excel
    }

    $lastNumber = (int) substr($last->slip_number, 2);
    return 'SL' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
}
```

**Importante:** El Excel termina en SL001249. El primer numero del sistema debe ser SL001250 para mantener continuidad con el historial.

---

## 7. Evaluacion de Riesgos

### 7.1 Riesgos Tecnicos

| ID | Riesgo | Probabilidad | Impacto | Mitigacion |
|---|---|---|---|---|
| R-01 | La generacion de PDF con dompdf no replica perfectamente el layout del Excel | Media | Alta | Usar tablas HTML con estilos inline; hacer pruebas tempranas con datos reales; alternativa: usar `spatie/browsershot` con Puppeteer para mayor fidelidad |
| R-02 | El rendimiento del componente `PackingSlipCreate` al cargar muchos lotes elegibles | Baja | Media | Paginacion en la seleccion de lotes, lazy loading, indices adecuados en BD |
| R-03 | Conflictos de formato entre `wo_number` del sistema (WO-2025-XXXXX) y el del Excel (W01980231001) | Media | Media | Los WOs en la Packing Slip se almacenan como snapshot; aclarar con el cliente si necesitan el formato del sistema legacy |
| R-04 | Concurrencia: dos usuarios creando Packing Slips al mismo tiempo y generando numeros duplicados | Baja | Alta | Usar `DB::transaction()` con bloqueo optimista al generar el `slip_number` |
| R-05 | Lotes que fueron incluidos en una Packing Slip y luego la Packing Slip se elimina (lotes quedan "bloqueados") | Baja | Media | Solo permitir eliminar Packing Slips en estado `draft`; al eliminar, limpiar `packing_slip_id` en los lotes afectados |

### 7.2 Riesgos de Integracion

| ID | Riesgo | Probabilidad | Impacto | Mitigacion |
|---|---|---|---|---|
| R-06 | El campo `label_spec` puede no estar disponible para todas las partes en el catalogo actual | Alta | Media | Permitir edicion manual del campo en el item de la Packing Slip antes de generar; hacer el campo nullable |
| R-07 | No todos los lotes completados tienen el `packaging_status` correctamente registrado | Media | Alta | Auditar el estado de los lotes existentes antes de lanzar el modulo; documentar los criterios de elegibilidad |
| R-08 | La vista `ShippingListDisplay` actual es muy compleja; integrar el boton de "crear Packing Slip" puede generar regresiones | Media | Media | Agregar el punto de entrada como un link externo en la barra de herramientas, sin modificar la logica interna del componente |

### 7.3 Riesgos de Tiempo y Recursos

| ID | Riesgo | Probabilidad | Impacto | Mitigacion |
|---|---|---|---|---|
| R-09 | La fidelidad del PDF puede requerir muchas iteraciones de ajuste fino | Alta | Media | Reservar tiempo especifico de QA para validacion del PDF; involucrar al usuario final desde el primer prototipo |
| R-10 | Requisitos no documentados en el Excel (ej: logica de agrupacion de items, orden de aparicion) | Media | Media | Validar con el equipo operativo antes de implementar la logica de composicion |
| R-11 | Dependencia de `barryvdh/laravel-dompdf`; si hay incompatibilidad con Laravel 12.x | Baja | Alta | Verificar compatibilidad antes de iniciar; alternativa: `tecnickcom/tcpdf` |

### 7.4 Riesgos de Proceso

| ID | Riesgo | Probabilidad | Impacto | Mitigacion |
|---|---|---|---|---|
| R-12 | El numero de Packing Slip en el sistema no coincide con el que usa el equipo de envios en el Excel actual | Media | Alta | Inicializar el correlativo desde SL001250; permitir que admin ajuste el numero inicial |
| R-13 | Las firmas en el pie de pagina pueden requerir firma digital o biometrica, no solo nombre de texto | Baja | Media | En Fase 1, usar texto (nombre del usuario logueado); en Fase 2, evaluar firma digital con el modelo `DocumentSignature` ya existente |

---

## 8. Fases de Implementacion Propuestas

### Fase 1: Infraestructura y Modelo de Datos (Estimado: 3-4 dias)

**Alcance:**

- Migracion: crear tabla `packing_slips`
- Migracion: crear tabla `packing_slip_items`
- Migracion: agregar columna `label_spec` a `parts`
- Migracion: agregar columna `packing_slip_id` a `lots`
- Modelo `PackingSlip` con relaciones, constantes y metodo `generateSlipNumber()`
- Modelo `PackingSlipItem` con relaciones
- Actualizacion de modelos `Part` y `Lot` (nuevas relaciones y fillables)
- Tests unitarios de los nuevos modelos

**Entregables:**
- 4 migraciones
- 2 nuevos modelos
- 2 modelos actualizados
- Suite de tests unitarios basicos

**Criterio de exito:** Las migraciones corren sin errores; los modelos tienen relaciones funcionales verificadas con tests.

---

### Fase 2: Interfaz de Creacion y Listado (Estimado: 4-5 dias)

**Alcance:**

- Componente Livewire `PackingSlipList`: listado paginado con filtros por fecha, numero, estado
- Componente Livewire `PackingSlipCreate`: wizard de 2 pasos:
  - Paso 1: Configurar encabezado (fecha, sold-to, shipped-to, FOB)
  - Paso 2: Seleccionar lotes elegibles (filtro por parte, WO, fecha) y ordenarlos
- Logica de elegibilidad de lotes (status completado + inspeccion aprobada + empaque terminado + sin Packing Slip asignada)
- Agregar rutas en `admin.php`
- Agregar entradas en el menu lateral (sidebar)
- Actualizar el campo `label_spec` en el formulario de creacion/edicion de Parts

**Entregables:**
- 2 componentes Livewire
- 2 vistas Blade correspondientes
- Actualizacion de rutas y sidebar
- Logica de seleccion y ordenamiento de lotes

**Criterio de exito:** Se puede crear una Packing Slip con items seleccionados; se guarda correctamente en BD con los snapshots de datos.

---

### Fase 3: Vista de Detalle y Generacion de PDF (Estimado: 4-5 dias)

**Alcance:**

- Instalar y configurar `barryvdh/laravel-dompdf`
- Crear vista Blade de impresion (`resources/views/packing-slips/print.blade.php`) replicando el formato FPL-10
- Componente Livewire `PackingSlipShow`: vista de detalle con opciones de accion
- Controlador `PackingSlipController@downloadPDF`: genera y descarga el PDF
- Logica de paginacion interna del PDF (items por pagina, encabezado en cada pagina, pie solo en ultima)
- Captura de firmas/nombre de revisores en el pie de pagina
- Boton "Marcar como Despachada" (actualiza status y `actual_send_date` de WOs)

**Entregables:**
- Vista Blade de impresion (CSS imprimible)
- Generacion de PDF descargable
- Componente `PackingSlipShow`
- Controlador con endpoint PDF
- Logica de transicion de estado (`draft` -> `generated` -> `shipped`)

**Criterio de exito:** El PDF generado es fiel al formato del Excel FPL-10; se puede descargar y tiene el numero de paginas correcto.

---

### Fase 4: Integracion con Flujo Existente (Estimado: 2-3 dias)

**Alcance:**

- Agregar punto de entrada desde `ShippingListDisplay` hacia `PackingSlipCreate`
- Indicador visual en `ShippingListDisplay` para lotes que ya tienen Packing Slip asignada
- Agregar `label_spec` en la vista de detalle de Parts (`POShow`, `PartShow`)
- Actualizar seeders y datos de prueba
- Tests de integracion del flujo completo (crear Packing Slip -> generar PDF -> despachar)

**Entregables:**
- Modificacion de `ShippingListDisplay` (solo UI, no logica interna)
- Tests de integracion
- Actualizacion de documentacion

**Criterio de exito:** El flujo completo desde "lotes listos" hasta "Packing Slip despachada con PDF descargado" funciona sin errores.

---

### Fase 5 (Opcional): Mejoras y Reportes (Estimado: 3-4 dias)

**Alcance (pendiente de prioridad):**

- Dashboard de metricas de envios (piezas enviadas por semana/mes, por parte, por cliente)
- Historial de Packing Slips con capacidad de busqueda por WO number
- Re-generacion de PDF para Packing Slips ya despachadas (sin modificar datos)
- Notificaciones internas al generar una Packing Slip
- Firma digital usando el modelo `DocumentSignature` existente

---

### Resumen de Estimaciones

| Fase | Nombre | Dias estimados | Dependencias |
|---|---|---|---|
| 1 | Infraestructura y Modelo | 3-4 | Ninguna |
| 2 | Interfaz de Creacion y Listado | 4-5 | Fase 1 |
| 3 | Vista de Detalle y PDF | 4-5 | Fase 2 |
| 4 | Integracion con Flujo Existente | 2-3 | Fase 3 |
| 5 | Mejoras y Reportes (opcional) | 3-4 | Fase 4 |
| **Total MVP (F1-F4)** | | **13-17 dias** | |
| **Total con F5** | | **16-21 dias** | |

---

## 9. Preguntas Abiertas

Las siguientes preguntas deben resolverse antes o durante la implementacion para evitar retrabajo:

**P-01 (Alta prioridad - Fase 1) - PARCIALMENTE RESUELTA:**
El formato del `wo_number` en el Packing Slip (FPL-10) es `W01980231001`. Segun el cliente, este numero se construye asi: `W0` (prefijo fijo) + `1980231` (numero real del WO, que existe en FPL-02) + `001` (numero de lote). Este numero WO de la Lista de Envio (FPL-02) es un identificador numerico puro (7 digitos) diferente al formato `WO-YYYY-XXXXX` de la app actual. Pendiente confirmar si el sistema de la app debe almacenar este numero de WO legacy en un campo adicional (`legacy_wo_number` o `external_wo_number`) o si el modulo de Packing Slip lo construye dinamicamente en base a los datos disponibles.

**P-02 (Alta prioridad - Fase 1):**
El numero inicial de la Packing Slip en el sistema digital debe ser SL001250 (continuando desde el ultimo del Excel). Confirmar: el equipo de envios esta de acuerdo con continuar la numeracion desde ese punto? O prefieren empezar desde SL000001 para el sistema digital y mantener el Excel como referencia historica?

**P-03 (Alta prioridad - Fase 2):**
Para la seleccion de lotes en una Packing Slip, se necesita definir el criterio exacto de "lote elegible". Se propone: `status = 'completed'` AND `inspection_status = 'approved'` AND `packing_slip_id IS NULL`. Confirmar si hay otros criterios adicionales que el equipo de Envios aplica manualmente hoy.

**P-04 (Media prioridad - Fase 2):**
En el Excel, los items de la Packing Slip se agrupan por PO y dentro de cada PO hay una fila de "Total:". Esta logica de agrupacion es requerida en el PDF digital, o los items pueden listarse en cualquier orden (ej: por fecha de produccion o por parte)?

**P-05 (Media prioridad - Fase 3):**
Las firmas en el pie de pagina del Excel son firmas fisicas. En el sistema digital, se quiere: (a) solo el nombre del usuario que aprueba, (b) una firma dibujada digitalmente, o (c) usar el modelo `DocumentSignature` ya existente en la app?

**P-06 (Media prioridad - Fase 3):**
Para el PDF: la libreria `dompdf` tiene limitaciones con CSS complejo. Se prefiere: (a) PDF con layout tabular simple (mas facil de implementar, menos fiel al Excel) o (b) layout con alta fidelidad al Excel (mas complejo, podria requerir `spatie/browsershot` con headless Chrome)?

**P-07 (Baja prioridad - Fase 4):**
Los tipos de caja en el pie de pagina (404-10003 y 20x20x8-1/2") son fijos o pueden variar segun el envio? Si pueden variar, se necesita una tabla de tipos de caja configurable.

**P-08 (Baja prioridad - Fase 5):**
El campo `sold_to` y `shipped_to` en el Excel siempre es S.E.I.P., Inc. Existe la posibilidad de envios a otros clientes en el futuro? Si la respuesta es si, conviene crear una tabla `customers` desde el principio.

**P-09 (Baja prioridad - General):**
El archivo Excel contiene 44 hojas con el historial de 2025. Se requiere importar ese historial historico al sistema o solo se gestionan Packing Slips nuevas (a partir de la implementacion)?

**P-10 (Alta prioridad - Campo Date) - POR CONFIRMAR con cliente final:**
La columna `Date` en el Packing Slip (FPL-10) muestra codigos del tipo `250515A22` (que corresponden al `lot_number` del sistema de produccion: `YYMMDD + Letra + Mesa`). Sin embargo, en la Packing Slip mas reciente analizada (SL001249) este campo aparece **completamente vacio** para todos los items. No esta claro si el campo `Date` es: (a) el `lot_number` de produccion del lote enviado, (b) la fecha del PO, o (c) la fecha de creacion del lote del Packing Slip. **El cliente confirmara con el cliente final (S.E.I.P., Inc.) que dato espera ver en esa columna. NO asumir hasta tener respuesta.**

**P-11 (Media prioridad - Label Spec) - POR CONFIRMAR:**
El campo `Label Spec` en el Packing Slip (ej: `M83519/2-8`, `SAE AS81824/1-2`, `ASNE0160-1-0H`) es actualmente ingresado de forma **manual** por el operador al crear cada Packing Slip. No existe en la Lista de Envio (FPL-02). El cliente no tiene claro todavia si se necesita una tabla en BD que ligue el numero de parte (`parts`) con su especificacion militar/aeronautica. **Por ahora NO modificar la tabla `parts` para agregar `label_spec`. Dejar como campo editable manual en `packing_slip_items.label_spec`. Revisar en futuro si se requiere catalogo.**

---

## 10. Referencias Tecnicas

- **Archivo Excel fuente:** `C:/xampp/htdocs/flexcon-tracker/Diagramas_flujo/Estructura/docs/ef/FPL-10 Shipping List 2025.xlsx`
- **Clave del documento:** FPL-10, Revision 02
- **Modelos relacionados:** `SentList`, `WorkOrder`, `PurchaseOrder`, `Part`, `Lot`, `PackagingRecord`
- **Componente Livewire existente:** `App\Livewire\Admin\SentLists\ShippingListDisplay`
- **Controlador existente:** `App\Http\Controllers\SentListController`
- **Rutas relevantes:** `routes/admin.php` - seccion Sent Lists y Packaging
- **Migraciones relacionadas:** `2025_12_26_024833_create_sent_lists_table.php`, `2026_02_27_070000_create_packaging_records_and_update_lots.php`
- **Libreria PDF recomendada:** `barryvdh/laravel-dompdf` (compatible con Laravel 12.x)
- **Alternativa PDF:** `spatie/browsershot` con Chromium

---

*Documento generado el 2026-03-05. Version 1.0 - Analisis inicial basado en lectura del Excel FPL-10 y exploracion del codigo fuente del proyecto FlexCon Tracker.*

---

## Contexto Adicional: Flujo hacia Invoice

El Shipping List (Packing Slip) es el **primer documento** del flujo de cierre del area de Empaque, pero no el ultimo. Una vez que la Packing Slip es despachada (estado `shipped`), el proceso continua con la generacion del **Invoice (Factura al cliente)**, documento financiero que convierte el registro logistico de despacho en un cobro formal a S.E.I.P., Inc.

### Relacion entre ambos documentos

- **Relacion:** 1 Packing Slip -> 1 Invoice (correspondencia directa)
- **Momento:** El Invoice se genera el mismo dia del despacho
- **Origen del Invoice:** Los datos del Invoice se copian directamente del Packing Slip; la diferencia clave es que el Invoice agrega el precio unitario por pieza y el total monetario de cada linea
- **Cargos adicionales:** El Invoice incluye tres cargos fijos que no existen en el Packing Slip: Machine Maintenance ($800), Administration Fee ($250) y Shipping Cost ($450)
- **Numeracion propia:** Los Invoices tienen su propia serie numerica correlativa (Invoice #00953 corresponde al Packing Slip #001249)

### El menu de Empaque tendra dos secciones

```
Empaque
  ├── Shipping List (Packing Slip) -- este modulo (FPL-10)
  └── Invoice                      -- modulo siguiente (FPL-12)
```

### Flujo completo del area de Empaque

```
Lotes completados e inspeccionados
       |
       v
  [Packing Slip]          <- Este documento (FPL-10)
  Estado: draft -> shipped
       |
       v
  [Invoice]               <- Siguiente documento (FPL-12)
  Estado: draft -> issued
       |
       v
  Cobro al cliente (S.E.I.P., Inc.)
```

### Referencia al analisis del Invoice

Para el detalle tecnico completo del modulo de Invoice (tablas, modelos, servicios, flujo de conversion, fases de implementacion y preguntas abiertas), consultar:

`02_invoice_analysis.md` - ubicado en el mismo directorio que este archivo.

Las fases de implementacion del Invoice (Fases 5 a 8) son una continuacion directa de las fases del Packing Slip (Fases 1 a 4) y dependen de que estas ultimas esten completadas primero.
