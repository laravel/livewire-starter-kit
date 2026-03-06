# Referencia Tecnica: Mapeo de Campos FPL-02 -> FPL-10

**Fecha:** 2026-03-05
**Elaborado por:** Arquitecto de Software - FlexCon Tracker
**Version:** 1.0
**Proposito:** Referencia tecnica detallada del flujo de datos desde la Lista de Envio (FPL-02) hasta el Packing Slip (FPL-10), incluyendo reglas de negocio, construccion de campos calculados y puntos pendientes de confirmacion.

---

## 1. Analisis de FPL-02 (Lista de Envio)

### 1.1 Descripcion General

La **Lista de Envio (FPL-02)** es el documento interno de planificacion y seguimiento de Work Orders que Ensambles Formula mantiene semana a semana. Es el **origen primario de datos** para generar la Packing Slip formal (FPL-10).

| Concepto | Valor |
|---|---|
| Clave del documento | FPL-02 |
| Revision | 06 |
| Tipo de archivo | Excel (.xlsx) |
| Total de hojas analizadas | 22 (una por semana, de enero a junio 2025) |
| Nombre de hojas | Fecha en formato `MM-DD-YYYY` (ej: `05-28-2025`) |
| Columnas de datos | 13 columnas activas (A a M) |
| Filas por hoja | Variable (puede llegar a 3,285 filas por hoja acumulada) |

### 1.2 Estructura del Encabezado (Filas 1-5)

```
Fila 1:  ENSAMBLES FORMULA
Fila 2:  [vacio] | LISTA DE ENVIO | [vacio] | Clave: FPL-02
Fila 3:  [vacio] | [vacio]        | [vacio] | Revision: 06
Filas 4-5: vacías
```

### 1.3 Encabezado de Columnas (Fila 6)

| Columna Excel | Nombre de Campo | Tipo de Dato | Ejemplo | Nullable |
|---|---|---|---|---|
| A | DOC | String enum | `WO`, `Viajero`, vacio | Si |
| B | WO # | Integer o String | `1980231` (fila WO) / `1980231 001` (sub-lote) | No en filas WO |
| C | Item # | String | `189-10257` | Solo en filas WO |
| D | Descripcion | String | `STS H-ML-8` | No |
| E | Cantidad WO | Integer | `100000` | No en filas WO |
| F | Piezas Enviadas | Integer o String | `0`, `51000`, ` -   ` | Si |
| G | Cantidad pendiente | Integer | `100000` | Si |
| H | Cantidad a Enviar | Integer | `100000` | Si |
| I | Fecha Progr. A Enviar | Date | `2025-05-28` | Si |
| J | Fecha de Envio | Date | `2025-05-28` | Si |
| K | Fecha de Apertura | Date | `2025-03-12` | Si |
| L | Eq | String | `S21`, `A01`, `S01`, `T01` | Si |
| M | PR | Integer | `1`, `2`, `4` | Si |

### 1.4 Tipos de Filas en el Cuerpo

El cuerpo de la Lista de Envio contiene cuatro tipos de filas, identificables por el valor de la columna A:

#### Tipo 1: Fila de Work Order Principal (col A = "WO")

Es la cabecera de cada Work Order. Contiene todos los datos del WO.

```
Ejemplo:
Col A: WO
Col B: 1980231          <- numero de WO (entero puro)
Col C: 189-10257        <- numero de parte
Col D: STS H-ML-8       <- descripcion de la parte
Col E: 100000           <- cantidad total de la WO
Col F: 0                <- piezas ya enviadas en ciclos anteriores
Col G: 100000           <- cantidad pendiente (= E - F)
Col H: 100000           <- cantidad a enviar en el ciclo actual
Col I: 2025-05-28       <- fecha programada del envio
Col J: 2025-05-28       <- fecha real del envio
Col K: 2025-03-12       <- fecha de apertura del WO
Col L: S21              <- equipo de produccion
Col M: 4                <- prioridad
```

#### Tipo 2: Fila de Sub-lote (col A = vacio, col B = "[WO] [lote]")

Detalla los lotes especificos que componen el envio de un WO. Un WO puede tener uno o varios lotes.

```
Ejemplo (sub-lote del WO 1980231):
Col A: (vacio)
Col B: 1980231 001      <- numero de WO + espacio + numero de lote (3 digitos)
Col C: (vacio)
Col D: STS H-ML-8       <- descripcion repetida
Col E: 100000           <- cantidad de piezas de ESTE lote especifico
Col F: (vacio o comentario, ej: "Viajero de 66,000")
```

#### Tipo 3: Fila de Total (col D = "Total:")

Aparece cuando un WO tiene multiples sub-lotes. Consolida la cantidad.

```
Col A: (vacio)
Col B: (vacio)
Col C: (vacio)
Col D: Total:
Col E: [suma de los sub-lotes]
```

#### Tipo 4: Separadores de Seccion

Filas de organizacion visual que separan grupos de WOs por tipo de equipo. No contienen datos de produccion.

```
Ejemplos:
Col B: "Mesas"
Col B: "Maquinas"
Col B: "Mesas Semi-Automaticas"
Col B: "Viajero"  (en WOs de crimp, indica el viajero fisico del proceso)
```

### 1.5 Patron de WOs con Multiples Lotes (Ejemplo Real)

El siguiente ejemplo muestra el WO 1982798 con 3 sub-lotes, tal como aparece en la hoja `05-28-2025`:

```
Fila 65: WO | 1982798 | 189-10179 | STS H-M-3 | 100000 | 0 | 100000 | 100000 | 2025-05-28 | 2025-05-28 | 2025-03-27 | S22 | 4
Fila 66:    | 1982798 001 | | STS H-M-3 | 58900  | Viajero de 66,000 |
Fila 67:    | 1982798 002 | | STS H-M-3 | 34000  | |
Fila 68:    | 1982798 003 | | STS H-M-3 |  7100  | |
Fila 69:    | | | Total: | 100000 | |
```

Este WO genera 3 lineas en la Packing Slip:
- `W01982798001` con 58,900 piezas
- `W01982798002` con 34,000 piezas
- `W01982798003` con  7,100 piezas
- Fila de Total: 100,000 piezas

---

## 2. Tabla de Mapeo Campo por Campo

La siguiente tabla es la referencia definitiva de como cada campo del Packing Slip (FPL-10) se obtiene a partir de la Lista de Envio (FPL-02) o de otras fuentes del sistema.

### 2.1 Campos del Encabezado del Packing Slip

| Campo en FPL-10 | Origen | Mecanismo de Obtencion | Estado | Notas |
|---|---|---|---|---|
| Nombre emisor (ENSAMBLES FORMULA) | Constante del sistema | Hardcoded o configuracion | Automatico | Nunca cambia |
| Clave FPL-10 | Constante | Hardcoded | Automatico | Siempre "FPL-10" |
| Revision 02 | Constante | Hardcoded | Automatico | Siempre "02" |
| Direccion emisor | Constante del sistema | Configuracion o hardcoded | Automatico | "330 Rocky Woods Lane, Bigfork, MT 59911" |
| Telefono y email | Constante del sistema | Configuracion o hardcoded | Automatico | "PH# 425-466-2184 / Frank@flexconinc.com" |
| Packing Slip # | Secuencia del sistema | Auto-generado correlativo | Automatico | Formato: `SL{NNNNNN}`, continua desde SL001249 |
| Sold to: nombre | Datos del cliente | Configuracion del cliente | Automatico/Constante | "S.E.I.P., Inc." (unico cliente actual) |
| Sold to: direccion | Datos del cliente | Configuracion del cliente | Automatico/Constante | "915 Armorlite Dr., San Marcos, CA 92069" |
| Shipped to: nombre | Datos del cliente | Configuracion del cliente | Automatico/Constante | Mismo que Sold to |
| Shipped to: direccion | Datos del cliente | Configuracion del cliente | Automatico/Constante | Mismo que Sold to |
| F.O.B. | Constante del sistema | Hardcoded o configuracion | Automatico | "Tecate, Ca." |
| DATE (del encabezado) | Fecha del despacho | Campo `packing_slips.slip_date` | Automatico (ingresado al crear) | Formato en el Excel: "May-28-2025" |

### 2.2 Campos de Cada Linea del Packing Slip

| Campo en FPL-10 | Origen en FPL-02 | Columna FPL-02 | Mecanismo de Obtencion | Estado |
|---|---|---|---|---|
| **Work Order** | WO# + sub-lote | Col B (fila WO) + Col B (sub-fila) | Construido: `W0 + [WO#] + [lote]` | Automatico |
| **PO #** | No existe en FPL-02 | N/A | JOIN: `work_orders.purchase_order_id` -> `purchase_orders.po_number` | Automatico via sistema |
| **Item no** | Item # | Col C de la fila WO | Copia directa | Automatico |
| **Description** | Descripcion | Col D | Copia directa (o via `parts.description` usando el Item#) | Automatico |
| **Quantity** | Cantidad a Enviar del sub-lote | Col E de la **sub-fila** de lote | Copia directa de la cantidad del lote especifico | Automatico |
| **Date** | Desconocido | Desconocido | **VER SECCION 4 - PENDIENTE CONFIRMAR** | Pendiente |
| **Label Spec** | No existe en FPL-02 | N/A | Ingreso manual del operador | Manual |

### 2.3 Campos del Pie de la Ultima Pagina

| Campo en FPL-10 | Origen | Mecanismo | Estado |
|---|---|---|---|
| Total de cajas 404-10003 | Conteo fisico del operador | Ingreso manual | Manual |
| Total cajas 20x20x8-1/2" | Conteo fisico del operador | Ingreso manual | Manual |
| Revision de Empaque (firma) | Usuario del sistema / firma manual | Campo de texto (Fase 1) / Firma digital (Fase 2) | Manual |
| Inspeccion de Empaque (firma) | Usuario del sistema / firma manual | Campo de texto (Fase 1) / Firma digital (Fase 2) | Manual |
| Revision CM (firma) | Usuario del sistema / firma manual | Campo de texto (Fase 1) / Firma digital (Fase 2) | Manual |

---

## 3. Reglas de Negocio

### 3.1 Construccion del Numero de Work Order en el Packing Slip

Esta es la regla de negocio mas importante del mapeo. El campo `Work Order` del Packing Slip se construye a partir de dos elementos de la Lista de Envio:

```
FORMULA:
W.O. del Packing Slip = "W0" + [WO# puro de FPL-02] + [numero de lote con 3 digitos]

COMPONENTES:
  Prefijo:      "W0"       -> constante, siempre los mismos 2 caracteres
  WO#:          1980231    -> numero de WO de la columna B de FPL-02 (fila tipo WO)
  Lote:         001        -> los 3 digitos al final de la sub-fila (ej: "1980231 001" -> "001")

RESULTADO:
  "W0" + "1980231" + "001" = "W01980231001"
```

**Implementacion sugerida en PHP (Laravel):**

```php
/**
 * Construye el numero de Work Order del Packing Slip.
 *
 * @param  string|int $woNumber  Numero de WO de la Lista de Envio (ej: 1980231)
 * @param  int        $loteNum   Numero de lote (ej: 1)
 * @return string               (ej: 'W01980231001')
 */
public static function buildPackingSlipWoNumber(string|int $woNumber, int $loteNum): string
{
    $lote = str_pad($loteNum, 3, '0', STR_PAD_LEFT);  // '001', '002', etc.
    return 'W0' . $woNumber . $lote;
}

// Uso:
// buildPackingSlipWoNumber('1980231', 1)  -> 'W01980231001'
// buildPackingSlipWoNumber('1982798', 3)  -> 'W01982798003'
```

**Parsing inverso** (para buscar un WO del Packing Slip en FPL-02):

```php
/**
 * Extrae el WO# y el numero de lote de un numero de Work Order del Packing Slip.
 *
 * @param  string $packingSlipWo  (ej: 'W01980231001')
 * @return array                  ['wo_number' => '1980231', 'lote' => '001']
 */
public static function parsePackingSlipWoNumber(string $packingSlipWo): array
{
    // Formato: W0 + 7 digitos WO + 3 digitos lote
    preg_match('/^W0(\d+)(\d{3})$/', $packingSlipWo, $matches);
    return [
        'wo_number' => $matches[1] ?? null,   // '1980231'
        'lote'      => $matches[2] ?? null,   // '001'
    ];
}
```

### 3.2 Obtencion del PO# via JOIN

El PO# no existe en la Lista de Envio. Se obtiene automaticamente en el momento de crear el `PackingSlipItem`:

```
Lista de Envio (FPL-02)
  WO# = 1980231
       |
       v (busqueda en el sistema por WO#)
Tabla work_orders
  id = X, wo_number = '...', purchase_order_id = Y, [numero legacy = 1980231]
       |
       v (JOIN)
Tabla purchase_orders
  id = Y, po_number = 49032
       |
       v (snapshot)
Tabla packing_slip_items
  po_number = 49032  <- guardado como snapshot inmutable
```

### 3.3 Seleccion de Lotes Elegibles desde la Lista de Envio

Al crear una Packing Slip, el sistema debe mostrar los sub-lotes de la Lista de Envio que cumplen los siguientes criterios:

| Criterio | Descripcion | Tabla/Campo |
|---|---|---|
| Lote completado | El lote termino produccion | `lots.status = 'completed'` |
| Inspeccion aprobada | El lote paso la inspeccion de calidad | `lots.inspection_status = 'approved'` |
| Empaque terminado | El lote tiene empaque registrado y aprobado | `lots.packaging_status` indica terminado |
| Sin Packing Slip asignada | El lote no ha sido incluido en otro despacho | `lots.packing_slip_id IS NULL` |

### 3.4 Agrupacion de Items en el Packing Slip

El Packing Slip agrupa los items por WO y PO. Dentro de cada grupo, los sub-lotes aparecen en orden secuencial de numero de lote. Cuando un WO tiene multiples sub-lotes, se agrega una fila de Total al final del grupo.

Ejemplo de agrupacion en el Packing Slip:
```
WO 1982798 (PO 49110, parte 189-10179):
  W01982798001  49110  189-10179  STS H-M-3  58,900
  W01982798002  49110  189-10179  STS H-M-3  34,000
  W01982798003  49110  189-10179  STS H-M-3   7,100
  [fila Total]  49110  189-10179  Total:     100,000
```

### 3.5 Numeracion Correlativa de Packing Slips

El sistema debe continuar la numeracion desde el ultimo Packing Slip del Excel historico:

```
Ultimo Packing Slip en Excel:  SL001249
Primer Packing Slip del sistema: SL001250

Formato: 'SL' + numero de 6 digitos con ceros a izquierda
Ejemplos: SL001250, SL001251, SL001252...
```

```php
// Logica de generacion en PackingSlip::generateSlipNumber()
public static function generateSlipNumber(): string
{
    $last = static::withTrashed()
        ->orderByRaw("CAST(SUBSTRING(slip_number, 3) AS UNSIGNED) DESC")
        ->first();

    if (!$last) {
        return 'SL001250';  // Primer numero al inicializar el sistema
    }

    $lastNumber = (int) substr($last->slip_number, 2);
    return 'SL' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
}
```

---

## 4. Campos Pendientes / Preguntas Abiertas

### 4.1 Campo `Date` (columna G del Packing Slip) - ALTA PRIORIDAD

**Estado:** PENDIENTE CONFIRMAR con el cliente final (S.E.I.P., Inc.)

**Descripcion del problema:**

En el Packing Slip (FPL-10), la columna `Date` muestra codigos del tipo `250515A22`. El analisis de los datos disponibles sugiere que este codigo es el **codigo de lote de produccion** generado por el sistema de produccion de FlexCon, con el siguiente formato:

```
250515A22
  25   = año 2025
  05   = mes mayo
  15   = dia 15
  A    = secuencia de produccion del dia (A, B, C... hasta Z)
  22   = numero de mesa o maquina (01, 02, 21, 22, etc.)
```

Este codigo corresponde al `lot_number` en la tabla `lots` de la app actual.

**Evidencia observada:**

| Packing Slip | Campo Date | Observacion |
|---|---|---|
| SL001249 (mas reciente) | **VACIO en todas las filas** | No hay codigo de lote en este PS |
| SL001248 | `250515A22`, `250516B22`, `250519C22` | Codigos de lote presentes |
| SL001247 | `250508A22`, `250512B22`, `250512C22` | Codigos de lote presentes |
| SL001246 y anteriores | Codigos de lote presentes | Patron consistente |

**Interrogantes sin resolver:**

1. En SL001249, el campo Date esta completamente vacio. Fue esto intencional? Es un error? Cambio de criterio?
2. El codigo `250515A22` corresponde al `lot_number` de la tabla `lots` de la app o es un campo diferente?
3. El campo `Date` del Packing Slip es la fecha del **lote de produccion** (lot_date_code), la fecha del **PO**, o la fecha de **creacion del Packing Slip**?

> **DECISION PENDIENTE:** El cliente confirmara con el cliente final (S.E.I.P., Inc.) que dato se espera ver en esa columna antes de implementar la logica. NO implementar hasta tener respuesta definitiva.

**Impacto arquitectural:**

- Si es el `lot_number` del sistema: el campo ya existe en `lots.lot_number` y el mapeo es directo
- Si es la fecha del PO: requiere hacer un JOIN adicional a `purchase_orders.date`
- Si es otra fecha: puede requerir un campo nuevo en `packing_slip_items`

### 4.2 Campo `Label Spec` - MEDIA PRIORIDAD

**Estado:** PENDIENTE CONFIRMAR si se necesita catalogo en BD

**Descripcion del problema:**

El campo `Label Spec` del Packing Slip contiene especificaciones militares y aeronauticas como:
- `M83519/2-8`
- `SAE AS81824/1-2`
- `ASNE0160-1-0H`
- `NAS1745-15`
- `JSFQ53-B2`

Estas especificaciones estan asociadas al numero de parte (ej: la parte `189-10257` siempre lleva `M83519/2-8`).

**Situacion actual:**

- El operador ingresa este dato **manualmente** al crear el Packing Slip
- No existe en la Lista de Envio (FPL-02)
- No existe en el catalogo de `parts` de la app
- El campo `packing_slip_items.label_spec` (nullable) cubre la necesidad inmediata

**Opciones para el futuro:**

| Opcion | Descripcion | Ventaja | Desventaja |
|---|---|---|---|
| A | Mantener ingreso manual (estado actual) | Simple, sin cambios en BD | Propenso a errores, inconsistencias entre PS |
| B | Agregar `label_spec` a la tabla `parts` | Auto-poblado al crear PS, consistente | Requiere migrar datos historicos, confirmacion del cliente |
| C | Tabla catalogo `part_label_specs` | Permite multiples specs por parte, mas flexible | Mas compleja, puede ser sobrediseno |

> **DECISION PENDIENTE:** El cliente determinara si necesita este catalogo en el futuro. Por ahora se mantiene la Opcion A (ingreso manual en cada PS). Si se decide la Opcion B, se agrega la migracion en la Fase 1 sin modificar las Fases ya completadas (el campo es nullable, no rompe nada).

### 4.3 Numero de WO del sistema vs numero de WO de la Lista de Envio - ALTA PRIORIDAD

**Estado:** PENDIENTE CONFIRMAR

**Descripcion del problema:**

La app FlexCon Tracker tiene Work Orders con formato `WO-2025-00001`. La Lista de Envio (FPL-02) tiene WOs con numeros puros de 7 digitos (ej: `1980231`). El Packing Slip construye el W.O. usando el numero de la Lista de Envio, NO el numero de la app.

**Interrogantes:**

1. Los WOs de la Lista de Envio (1980231, 1982798, etc.) son los **WOs del sistema legacy** de FlexCon (el Excel que usaban antes de la app) o son WOs del sistema de su cliente S.E.I.P.?
2. En la app actual, la tabla `work_orders` tiene un campo `wo_number` con formato `WO-2025-00001`. Debe agregarse un campo adicional `external_wo_number` o `legacy_wo_number` para almacenar el numero de 7 digitos de la Lista de Envio?
3. O el modulo de Packing Slip construye el W.O. del Packing Slip de forma dinamica usando el numero de WO de la Lista de Envio (que podria ser un archivo importado, no una tabla de la app)?

> **DECISION PENDIENTE:** Esta es la pregunta arquitectural mas critica para el modulo de Packing Slip. Definir esto antes de iniciar la Fase 1 de implementacion.

---

## 5. Diagrama de Flujo de Datos

El siguiente diagrama muestra el flujo completo desde la Lista de Envio hasta el Invoice, pasando por el Packing Slip.

```
╔══════════════════════════════════════════╗
║       FPL-02: LISTA DE ENVIO            ║
║  (Origen: Excel interno semanal)        ║
║                                          ║
║  ┌─────────────────────────────────┐    ║
║  │ WO | WO# | Item# | Desc | ...  │    ║
║  │ WO | 1980231 | 189-10257 | ... │    ║
║  │    | 1980231 001 | ...         │    ║
║  │    | 1980231 002 | ...         │    ║
║  │ WO | 1982798 | 189-10179 | ... │    ║
║  │    | 1982798 001 | ...         │    ║
║  │    | 1982798 002 | ...         │    ║
║  │    | 1982798 003 | ...         │    ║
║  └─────────────────────────────────┘    ║
╚══════════════════════════════════════════╝
                    |
                    | Operador selecciona sub-lotes elegibles
                    | (completados, inspeccionados, empacados, sin PS)
                    v
╔══════════════════════════════════════════╗
║         SISTEMA FLEXCON TRACKER         ║
║                                          ║
║  Datos de la seleccion:                  ║
║   - WO# puro:        1980231            ║
║   - Sub-lote:        001                ║
║   - Item#:           189-10257          ║
║   - Descripcion:     STS H-ML-8         ║
║   - Cantidad:        100000             ║
║                                          ║
║  Datos calculados automaticamente:       ║
║   - W.O. PS:   W0 + 1980231 + 001       ║
║               = W01980231001            ║
║   - PO#:       JOIN work_orders         ║
║               -> purchase_orders        ║
║               = 49032                   ║
║                                          ║
║  Datos manuales del operador:            ║
║   - Label Spec: M83519/2-8              ║
║   - Date: [PENDIENTE CONFIRMAR]          ║
╚══════════════════════════════════════════╝
                    |
                    v
╔══════════════════════════════════════════╗
║     FPL-10: PACKING SLIP (Generado)     ║
║                                          ║
║  Packing Slip #001250                   ║
║  DATE: Jun-04-2025                       ║
║                                          ║
║  Work Order    | PO# | Item no | Desc   | Qty    | Date | Label Spec
║  W01980231001  | 49032| 189-10257|STS H-ML-8|100000|  ?   | M83519/2-8
║  W01982798001  | 49110| 189-10179|STS H-M-3 | 58900|  ?   | M83519/1-3
║  W01982798002  | 49110| 189-10179|STS H-M-3 | 34000|  ?   | M83519/1-3
║  W01982798003  | 49110| 189-10179|STS H-M-3 |  7100|  ?   | M83519/1-3
║              Total: | 100000
║                                          ║
║  [Pie de pagina - ultima pagina]         ║
║   Total cajas 404-10003: ___            ║
║   Total cajas 20x20x8: ___             ║
║   Firmas: ___  ___  ___                 ║
╚══════════════════════════════════════════╝
                    |
                    | Al marcar como "Despachado"
                    | Sistema actualiza lots.packing_slip_id
                    | y work_orders.actual_send_date
                    v
╔══════════════════════════════════════════╗
║           FPL-12: INVOICE               ║
║                                          ║
║  Invoice #00954                         ║
║  (mismo contenido que Packing Slip)     ║
║  + precio unitario por parte            ║
║  + total monetario por linea            ║
║  + cargos fijos:                         ║
║     Machine Maintenance: $800           ║
║     Administration Fee:  $250           ║
║     Shipping Cost:       $450           ║
╚══════════════════════════════════════════╝
```

---

## 6. Ejemplos Verificados del Mapeo

Los siguientes ejemplos fueron verificados cruzando los datos reales del archivo FPL-02 (hoja `05-28-2025`) con el Packing Slip SL001249 del archivo FPL-10.

### Ejemplo 1: WO 1980231 (un solo lote)

| Paso | Documento | Campo | Valor |
|---|---|---|---|
| Origen | FPL-02 hoja 05-28-2025 | WO# (Col B) | `1980231` |
| Origen | FPL-02 hoja 05-28-2025 | Sub-lote (Col B) | `1980231 001` |
| Origen | FPL-02 hoja 05-28-2025 | Item# (Col C) | `189-10257` |
| Origen | FPL-02 hoja 05-28-2025 | Descripcion (Col D) | `STS H-ML-8` |
| Origen | FPL-02 hoja 05-28-2025 | Cantidad a Enviar del lote (Col E sub-fila) | `100000` |
| Calculado | Sistema | W.O. del PS | `W0` + `1980231` + `001` = **`W01980231001`** |
| Calculado | Sistema (JOIN) | PO# | **`49032`** (de tabla `purchase_orders`) |
| Resultado | FPL-10 SL001249 Pag 1 fila 15 | Work Order | `W01980231001` ✓ |
| Resultado | FPL-10 SL001249 Pag 1 fila 15 | PO # | `49032` ✓ |
| Resultado | FPL-10 SL001249 Pag 1 fila 15 | Item no | `189-10257` ✓ |
| Resultado | FPL-10 SL001249 Pag 1 fila 15 | Description | `STS H-ML-8` ✓ |
| Resultado | FPL-10 SL001249 Pag 1 fila 15 | Quantity | `100000` ✓ |
| Resultado | FPL-10 SL001249 Pag 1 fila 15 | Date | `(vacio)` |
| Resultado | FPL-10 SL001249 Pag 1 fila 15 | Label Spec | `M83519/2-8` (ingresado manualmente) |

### Ejemplo 2: WO 1982798 (tres lotes)

| Paso | Documento | Campo | Valor |
|---|---|---|---|
| Origen | FPL-02 hoja 05-28-2025 | WO# | `1982798` |
| Origen | FPL-02 sub-filas | Sub-lotes | `1982798 001`, `1982798 002`, `1982798 003` |
| Origen | FPL-02 sub-fila 001 | Cantidad | `58900` |
| Origen | FPL-02 sub-fila 002 | Cantidad | `34000` |
| Origen | FPL-02 sub-fila 003 | Cantidad | `7100` |
| Calculado | Sistema | W.O. lote 001 | **`W01982798001`** |
| Calculado | Sistema | W.O. lote 002 | **`W01982798002`** |
| Calculado | Sistema | W.O. lote 003 | **`W01982798003`** |
| Calculado | Sistema (JOIN) | PO# | **`49110`** |
| Resultado | FPL-10 SL001249 filas 17-19 | 3 lineas con Total = 100000 | ✓ Verificado |

### Ejemplo 3: WO 1955984 con lotes 004, 005, 006 (lotes parciales de un WO grande)

Este ejemplo muestra que un WO puede haber tenido lotes anteriores (001, 002, 003) enviados en semanas previas, y en este ciclo se envian solo los lotes 004, 005, 006:

| Campo | Valor en FPL-02 | Valor en FPL-10 | Observacion |
|---|---|---|---|
| WO# | `1955984` | N/A | El WO existe en la lista |
| Sub-lotes | `1955984 004`, `005`, `006` | W01955984004, 005, 006 | Solo los lotes de este ciclo |
| PO# | (no en FPL-02) | `48321` | Obtenido via JOIN |
| Cantidades | `17700`, `21100`, `10200` | `17700`, `21100`, `10200` | Coinciden ✓ |
| Total | `49000` | Fila Total: `49000` | ✓ |

---

## 7. Consideraciones para la Implementacion

### 7.1 Importar o Vincular la Lista de Envio

El modulo de Packing Slip necesita acceder a los datos de la Lista de Envio. Hay dos enfoques:

**Enfoque A: Lista de Envio como tabla en BD (recomendado)**

La Lista de Envio se modela como una tabla propia en la BD, sincronizada con el Excel o reemplazandolo. Los operadores gestionan los WOs de la Lista de Envio directamente en la app.

Ventajas: Datos en tiempo real, validaciones, trazabilidad completa.
Desventajas: Requiere implementar el modulo de gestion de Lista de Envio completo.

**Enfoque B: Lista de Envio como referencia via WOs del sistema**

Los WOs de la Lista de Envio se vinculan con los WOs de la tabla `work_orders` existente via un campo `external_wo_number` o similar.

Ventajas: No requiere nueva tabla, reutiliza estructura existente.
Desventajas: Requiere que el numero de WO del Excel este almacenado en la app.

> **PENDIENTE:** La decision entre Enfoque A y B depende de la respuesta a la pregunta P-01 / P-01 actualizada (seccion 9 del analisis principal). Confirmar antes de iniciar Fase 1.

### 7.2 Snapshot de Datos en packing_slip_items

Al crear un `PackingSlipItem`, el sistema guarda un **snapshot inmutable** de todos los campos al momento del despacho. Esto protege la integridad historica:

```php
// Al crear un PackingSlipItem
PackingSlipItem::create([
    'packing_slip_id'   => $packingSlip->id,
    'lot_id'            => $lot->id,
    'work_order_id'     => $workOrder->id,
    'purchase_order_id' => $purchaseOrder->id,
    // Snapshots inmutables:
    'wo_number'         => buildPackingSlipWoNumber($externalWoNumber, $loteNum),
    'po_number'         => $purchaseOrder->po_number,
    'item_number'       => $part->number,
    'description'       => $part->description,
    'quantity'          => $lot->cantidad_a_enviar,
    'lot_date_code'     => null,        // PENDIENTE CONFIRMAR (campo Date)
    'label_spec'        => $request->label_spec,  // Ingreso manual
    'sort_order'        => $sortOrder,
    'page_number'       => $pageNumber,
]);
```

### 7.3 Validacion de Integridad antes de Generar el Packing Slip

El sistema debe verificar antes de permitir la generacion del PDF:

```
- Todos los items tienen Work Order number construido correctamente
- Todos los items tienen PO# (no nulo, si el WO tiene PO asociado)
- Todos los items tienen Quantity > 0
- Los items de Label Spec que sean requeridos por el tipo de parte esten completos
- El pie de pagina tiene los totales de cajas y nombres de revisores
```

---

## 8. Referencias

- **Archivo FPL-02:** `C:/xampp/htdocs/flexcon-tracker/Diagramas_flujo/Estructura/docs/ef/FPL-02 Lista de Envio mayo 2025 Rev.xlsx`
- **Archivo FPL-10:** `C:/xampp/htdocs/flexcon-tracker/Diagramas_flujo/Estructura/docs/ef/FPL-10 Shipping List 2025.xlsx`
- **Analisis principal:** `01_shipping_list_analysis.md` (mismo directorio)
- **Analisis del Invoice:** `02_invoice_analysis.md` (mismo directorio)
- **Modelos relevantes:** `WorkOrder`, `PurchaseOrder`, `Part`, `Lot`, `PackingSlip`, `PackingSlipItem`
- **Tabla BD clave:** `work_orders.purchase_order_id` (FK para obtener el PO# via JOIN)

---

*Documento generado el 2026-03-05. Version 1.0 - Basado en lectura y analisis cruzado de FPL-02 y FPL-10 con contexto del cliente sobre la construccion del numero de Work Order y la relacion con el sistema.*
