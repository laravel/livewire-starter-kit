# Analisis Tecnico: Invoice (Factura al Cliente) - FPL-12

**Fecha:** 2026-03-05
**Elaborado por:** Arquitecto de Software - FlexCon Tracker
**Version:** 1.0
**Archivo fuente analizado:** `FPL-12 Invoice 2025.xlsx`
**Clave del documento:** FPL-12, Revision 01
**Documento previo relacionado:** `01_shipping_list_analysis.md` (Packing Slip / Shipping List)

---

## 1. Resumen Ejecutivo

El **Invoice (Factura)** es el documento comercial-financiero que **FLEXCON** (Ensambles Formula) emite al cliente **S.E.I.P., Inc.** para cobrar el trabajo realizado en cada envio semanal. Es el ultimo paso del flujo de Empaque y representa la conversion del documento logistico (Packing Slip) en un documento financiero con precios y totales monetarios.

El archivo Excel `FPL-12 Invoice 2025.xlsx` contiene **22 hojas**, una por cada factura emitida en 2025, desde **Invoice #00932 (Ene-06-2025)** hasta **Invoice #00953 (May-28-2025)**. Cada hoja corresponde exactamente a un embarque semanal y referencia el numero de Packing Slip asociado.

### Hallazgo critico: Relacion 1 a 1 con Packing Slip

**Cada Invoice corresponde exactamente a un Packing Slip.** Esto se evidencia porque cada hoja del Invoice referencia explicitamente el numero de Packing Slip (ej: `Packing Slip #001249` en la hoja del `Invoice#00953`). La conversion es directa: una Packing Slip despachada genera un Invoice.

### Diferencias fundamentales respecto al Packing Slip

El Invoice es estructuralmente identico al Packing Slip en terminos de cabecera y lista de productos, pero **agrega dos columnas criticas que no existen en el Packing Slip:**

- **UNIT COST** (precio unitario por pieza)
- **TOTAL** (cantidad x precio unitario = subtotal por linea)

Ademas, el Invoice incluye **cargos adicionales** que no son de producto:
- `Machine Maintenance` (Mantenimiento de maquinaria) - siempre $800.00
- `Administration Fee` (Cargo administrativo) - siempre $250.00
- `SHIPPING COST` (Costo de envio) - siempre $450.00

Y al final de todos los items aparece una **fila de totales** con la suma de cantidades y la suma de importes.

**Conclusion ejecutiva:** El modulo de Invoice es una extension del modulo de Packing Slip. No se crea desde cero: se genera a partir de una Packing Slip existente, agregando los precios unitarios de cada parte y los cargos fijos adicionales. El sistema debe permitir generar un Invoice desde cualquier Packing Slip en estado `shipped`, con capacidad de capturar o confirmar el precio unitario de cada item.

---

## 2. Relacion con el Shipping List (Packing Slip)

### 2.1 Correspondencia Documentada

El analisis de las 22 hojas del Excel confirma la siguiente relacion entre Invoices y Packing Slips:

| Invoice # | Fecha Invoice | Packing Slip # | Fecha Embarque |
|---|---|---|---|
| 00953 | May-28-2025 | #001249 | May-28-2025 |
| 00952 | May-21-2025 | #001248 | May-21-2025 |
| 00951 | May-14-2025 | #001247 | May-14-2025 |
| 00950 | May-07-2025 | #001246 | May-07-2025 |
| 00949 | Apr-30-2025 | #001245 | Apr-30-2025 |
| 00948 | Apr-23-2025 | #001244 | Apr-23-2025 |
| 00947 | Apr-15-2025 | #001243 | Apr-15-2025 |
| 00946 | Apr-09-2025 | #001242 | Apr-09-2025 |
| 00945 | Apr-01-2025 | #001241 | Apr-01-2025 |
| 000944 | Mar-24-2025 | N/A (Solvents) | N/A |
| 00943 | Mar-26-2025 | #001240 | Mar-26-2025 |
| 00942 | Mar-19-2025 | #001239 | Mar-19-2025 |
| 00941 | Mar-12-2025 | #001238 | Mar-12-2025 |
| 00940 | Mar-05-2025 | #001237 | Mar-05-2025 |
| 00939 | Feb-26-2025 | #001236 | Feb-26-2025 |
| 00938 | Feb-19-2025 | #001235 | Feb-19-2025 |
| 00937 | Feb-12-2025 | #001234 | Feb-12-2025 |
| 00936 | Feb-05-2025 | #001233 | Feb-05-2025 |
| 00935 | Jan-29-2025 | #001232 | Jan-29-2025 |
| 00934 | Jan-22-2025 | #001231 | Jan-22-2025 |
| 00933 | Jan-15-2025 | #001230 | Jan-15-2025 |
| 00932 | Jan-06-2025 | #001229 | Jan-08-2025 |

**Observaciones:**

1. **Regla general:** Una Packing Slip = un Invoice. La fecha es la misma en ambos documentos.

2. **Excepcion - Invoice de Solventes (000944):** La hoja `Solvents 03-26-2025` contiene un Invoice con `Packing Slip: N/A`. Este Invoice factura consumibles (Alcohol isopropilico) entregados en 4 ocasiones distintas de 2024-2025. **No tiene Packing Slip asociada porque no es un despacho de producto terminado.** Tiene un formato diferente: direccion del emisor distinta, sin numero de pagina, con items que son entregas de solventes con referencia de recibo (Flx-Ref). Este caso especial requiere manejo separado en el sistema.

3. **Numeracion no es perfectamente correlativa:** El Invoice 000944 (Solvents) tiene un formato de numero diferente (`000944` vs `00953`). Existen numeros intermedios lo cual confirma que hay facturas que no corresponden a despachos de producto (pueden ser facturas de otros conceptos no incluidas en el Excel analizado).

4. **La fecha del Invoice y la fecha del Packing Slip son la misma.** El Invoice se genera el mismo dia del despacho, no despues.

### 2.2 Campos Compartidos entre Packing Slip e Invoice

| Campo | En Packing Slip (FPL-10) | En Invoice (FPL-12) | Observaciones |
|---|---|---|---|
| Empresa emisora | ENSAMBLES FORMULA | FLEXCON | **Nombre diferente.** Packing Slip usa nombre completo, Invoice usa nombre corto. |
| Clave del documento | FPL-10 | FPL-12 | Diferente clave |
| Direccion | 330 Rocky Woods Lane, Bigfork, MT | 330 Rocky Woods Lane, Bigfork, MT | Igual (salvo Invoice Solvents que tiene Everett, WA) |
| Telefono | PH# 425-466-2184 | PH# 425-466-2184 | Igual |
| Email | Frank@flexconinc.com | franknwflexcon@comcast.net | **Email diferente.** Packing Slip usa dominio corporativo, Invoice usa Comcast. |
| Sold to | S.E.I.P., Inc. | S.E.I.P., Inc. | Igual |
| Shipped to | S.E.I.P., Inc. | S.E.I.P., Inc. | Igual |
| Direccion cliente | 915 Armorlite Dr., San Marcos, CA 92069 | 915 Armorlite Dr., San Marcos, CA 92069 | Igual |
| F.O.B. | F.O.B: Tecate, Ca. | F.O.B: Tecate, Ca. | Igual |
| Fecha | DATE: May-28-2025 | DATE: May-28-2025 | Igual |
| Packing Slip # | Packing Slip #001249 | Packing Slip #001249 | **El Invoice referencia el numero del Packing Slip** |
| Description | STS H-ML-8 | STS H-ML-8 | Igual |
| Item No. | 189-10257 | 189-10257 | Igual |
| Lot No. | 250515A22 | 052625x01 | **Formato diferente.** Ver detalle en seccion 3.4 |
| P.O. No. | 49032 | 49032 | Igual |
| W.O. No. | W01980231001 | 1980231 | **Formato diferente.** El Invoice omite el prefijo 'W0' y el sufijo '001' |
| Quantity | 100000 | 100000 | Igual |

### 2.3 Campos Exclusivos del Invoice (no existen en Packing Slip)

| Campo | Columna | Tipo | Descripcion | Ejemplo |
|---|---|---|---|---|
| UNIT COST | H | Decimal (6 decimales) | Precio unitario por pieza en USD | 0.138 |
| TOTAL | I | Decimal (calculado) | Quantity x Unit Cost | 13800.00 |
| Machine Maintenance | - | Fijo $800 | Cargo fijo por mantenimiento de maquinaria | 800.00 |
| Administration Fee | - | Fijo $250 | Cargo fijo administrativo | 250.00 |
| SHIPPING COST | - | Fijo $450 | Cargo fijo de envio | 450.00 |
| GRAND TOTAL (Cantidad) | G | Integer (suma) | Total de piezas despachadas | 539,200 |
| GRAND TOTAL (Importe) | I | Decimal (suma) | Total del Invoice en USD | $80,052.11 |

### 2.4 Campos Exclusivos del Packing Slip (no existen en Invoice)

| Campo | Descripcion |
|---|---|
| Label Spec | Especificacion militar (M83519/2-8, etc.) |
| Total de cajas 404-10003 | Conteo de tipo de caja especifica |
| Total cajas 20x20x8-1/2" | Conteo de tipo de caja |
| Firma: Revision de Empaque | Firma del revisor de empaque |
| Firma: Inspeccion de Empaque | Firma del inspector |
| Firma: Revision CM | Firma de Control de Materiales |
| Numero de pagina | Packing Slip puede ser multipagina |

---

## 3. Analisis del Excel - Estructura Detallada

### 3.1 Estructura General del Archivo

| Concepto | Valor |
|---|---|
| Total de hojas | 22 |
| Invoices de producto terminado | 21 (uno por semana) |
| Invoices de solventes/consumibles | 1 (caso especial: "Solvents 03-26-2025") |
| Periodo cubierto | Ene-08-2025 a May-28-2025 |
| Numeracion de Invoices | #00932 a #00953 |
| Packing Slips referenciadas | #001229 a #001249 |
| Filas por hoja | 37 a 57 filas |
| Columnas activas | 8 columnas (B a I) |

### 3.2 Estructura del Encabezado (Filas 1 a 14 de cada hoja)

```
Fila 1:  FLEXCON                          (nombre corto de la empresa)
Fila 2:  [vacio] | INVOICE | [vacio] | Clave: FPL-12
Fila 3:  [vacio] | [vacio] | [vacio] | Revision: 01
Fila 4:  330 Rocky Woods Lane - Bigfork, Montana - 59911
Fila 5:  [vacio]
Fila 6:  PH# 425-466-2184 | [vacio] | [vacio] | franknwflexcon@comcast.net
Fila 7:  [vacio]
Fila 8:  "Sold to:" | [nombre] | "Shipped to:" | [nombre] | Packing Slip | #001249
Fila 9:  S.E.I.P., Inc. | S.E.I.P., Inc.
Fila 10: 915 Armorlite Dr. | 915 Armorlite Dr.
Fila 11: San Marcos, Ca. 92069 | San Marcos, Ca. 92069 | F.O.B: Tecate, Ca.
Fila 12: [vacio]
Fila 13: Invoice#00953 | [vacio] | [vacio] | DATE: May-28-2025
Fila 14: [vacio]
```

**Campos del encabezado identificados:**

| Campo | Tipo | Ejemplo | Diferencia vs Packing Slip |
|---|---|---|---|
| Nombre empresa emisora | Texto fijo | "FLEXCON" | Usa "FLEXCON" en vez de "ENSAMBLES FORMULA" |
| Clave del documento | Texto fijo | "FPL-12" | FPL-12 vs FPL-10 |
| Revision | Texto fijo | "01" | Revision 01 (Packing Slip es Revision 02) |
| Direccion emisor | Texto fijo | "330 Rocky Woods Lane, Bigfork, MT 59911" | Igual |
| Telefono emisor | Texto fijo | "PH# 425-466-2184" | Igual |
| Email emisor | Texto fijo | "franknwflexcon@comcast.net" | **Diferente** al del Packing Slip |
| Sold to (nombre) | Texto | "S.E.I.P., Inc." | Igual |
| Sold to (direccion) | Texto | "915 Armorlite Dr., San Marcos, CA 92069" | Igual |
| Shipped to (nombre) | Texto | "S.E.I.P., Inc." | Igual |
| Shipped to (direccion) | Texto | "915 Armorlite Dr., San Marcos, CA 92069" | Igual |
| Numero de Packing Slip referenciada | String | "#001249" | **NUEVO en Invoice** - referencia cruzada |
| F.O.B. | Texto fijo | "F.O.B: Tecate, Ca." | Igual |
| Numero de Invoice | String | "Invoice#00953" | **NUEVO** - numeracion propia del Invoice |
| Fecha | Fecha | "May-28-2025" | Igual (mismo dia que Packing Slip) |

### 3.3 Encabezado de la Tabla de Items (Fila 15)

```
DESCRIPTION | Item No. | LOT NO. | P.O No. | W.O No. | QUANTITY | UNIT COST | TOTAL
```

| Columna | Nombre | Tipo de Dato | Ejemplo | Diferencia vs Packing Slip |
|---|---|---|---|---|
| Col B | DESCRIPTION | String | "STS H-ML-8" | Igual a "Description" en Packing Slip |
| Col C | Item No. | String (codigo de parte) | "189-10257" | Igual a "Item no" en Packing Slip |
| Col D | LOT NO. | String (codigo de lote) | "052625x01" | **Formato diferente** (ver 3.4) |
| Col E | P.O No. | Integer | 49032 | Igual a "PO #" en Packing Slip |
| Col F | W.O No. | Integer | 1980231 | **Formato diferente** - sin prefijo W0 ni sufijo 001 |
| Col G | QUANTITY | Integer | 100000 | Igual |
| Col H | UNIT COST | Decimal | 0.138 | **NUEVO** - no existe en Packing Slip |
| Col I | TOTAL | Decimal | 13800.00 | **NUEVO** - no existe en Packing Slip |

### 3.4 Diferencias de Formato en Campos Compartidos

**Campo LOT NO.:**
- Packing Slip: `250515A22` (formato `YYMMDD` + letra + secuencia de mesa)
- Invoice: `052625x01` (formato `MMDDYY` + 'x' + secuencia)

**Observacion:** Los dos formatos representan la misma fecha de produccion pero con orden diferente. El Lot No del Invoice con formato `052625x01` = produccion del 26-Mayo-2025, lote x01. El Lot No del Packing Slip `250515A22` = 15-Mayo-2025 (no es la misma fecha, son envios distintos). Esto indica que **el campo LOT NO. en el Invoice es un codigo propio diferente al `lot_number` del sistema**, posiblemente un batch code del proceso fisico que usa otro formato.

Ejemplo cruzado del envio 05-28-2025:
- Packing Slip SL001249 usa `lot_date_code` como `250515A22` (de los lotes del sistema)
- Invoice #00953 usa `LOT NO.` como `052625x01` (formato propio del Invoice)

**Implicacion tecnica:** El Invoice usa un `lot_code` de batch de produccion con formato `MMDDYY` + identificador, mientras la Packing Slip usa el `lot_number` interno del sistema. Ambos son el mismo lote fisico pero expresado con convenciones distintas. El Invoice parece usar el codigo que el operador de planta asigna al batch de produccion, mientras la Packing Slip usa el codigo interno del sistema de la app.

**Campo W.O No.:**
- Packing Slip: `W01980231001` (formato `W0` + 7 digitos + `001`)
- Invoice: `1980231` (solo los 7 digitos centrales, sin prefijo ni sufijo)

Esto confirma que el **W.O. number del sistema legacy** tiene la estructura `W0{NNNNNNNN}001` donde los 7 u 8 digitos del centro son el identificador real del WO. El Invoice simplifica mostrando solo esos digitos.

### 3.5 Filas de Items (Datos de Producto)

**Patron de datos observado:**

A diferencia del Packing Slip que agrupa items por PO con filas de "Total:" intermedias, el Invoice **NO tiene filas de subtotal intermedias**. Los items aparecen listados uno tras otro sin agrupaciones formales, ordenados generalmente de mayor a menor cantidad.

Ejemplo tipico de Invoice (hoja 05-28-2025):

```
DESCRIPTION          | Item No.  | LOT NO.   | P.O No. | W.O No. | QTY    | UNIT  | TOTAL
STS H-ML-8           | 189-10257 | 052625x01 | 49032   | 1980231 | 100000 | 0.138 | 13800.00
STS H-M-3            | 189-10179 | 052625x01 | 49110   | 1982798 | 100000 | 0.091 |  9120.00
STS H-CR-436-37-CRIMP| 189-10492 | 052625x01 | 48322   | 1955992 | 100000 | 0.168 | 16830.00
STS H-CR-436-37-CRIMP| 189-10492 | 052625x01 | 48321   | 1955984 |  49000 | 0.168 |  8246.70
...
```

**Nota sobre misma parte con multiple POs en el mismo Invoice:** La misma parte (`189-10492 STS H-CR-436-37-CRIMP`) puede aparecer en multiples filas con diferentes POs y diferentes cantidades. No hay "Total por parte" en el Invoice (a diferencia del Packing Slip que si tenia la fila de "Total:" por grupo de PO).

### 3.6 Cargos Adicionales (Filas de Cargo Fijo)

Antes de la fila de totales, **todos los Invoices de producto terminado** incluyen tres cargos fijos:

| Concepto | Columna H (importe) | Columna I (total) | Frecuencia |
|---|---|---|---|
| Machine Maintenance | 800 | 800 | En todos los Invoices de producto |
| Administration Fee | 250 | 250 | En todos los Invoices de producto |
| SHIPPING COST | 450 | 450 | En todos los Invoices de producto |
| **Subtotal cargos fijos** | | **$1,500.00** | Siempre |

**Observacion:** Estos cargos NO tienen Item No., LOT NO., P.O. No. ni W.O. No. Solo tienen descripcion y monto. El campo UNIT COST tiene el monto (ej: 800) y el campo TOTAL tiene el mismo monto (ej: 800), porque la "cantidad" implicita es 1.

**Excepcion - Invoice de Solventes:** El Invoice especial de solventes tiene un formato totalmente diferente, sin cargos fijos de maquinaria y con items que son facturas de entrega de consumibles (Alcohol isopropilico).

### 3.7 Fila de Totales

La ultima fila de datos antes de los renglones vacios es la **fila de totales**:

| Columna | Contenido |
|---|---|
| Col G | Suma total de piezas (ej: 539,200) |
| Col I | Suma total del Invoice en USD (ej: $80,052.11) |

**Observacion:** El total en USD incluye los cargos fijos. Por ejemplo, en el Invoice 05-28-2025 los items de producto suman aprox. $78,252.11 y los cargos fijos $1,500, dando $79,752.11. El valor real observado es $80,052.11, lo que confirma que los cargos fijos estan incluidos en el total del Invoice.

### 3.8 Estadisticas del Invoice (Muestra del 2025)

| Hoja | Invoice # | Packing Slip # | Items de Producto | Total Piezas | Total USD |
|---|---|---|---|---|---|
| 05-28-2025 | 00953 | #001249 | 15 | 539,200 | $80,052.11 |
| 05-21-2025 | 00952 | #001248 | 22 | 474,600 | $71,012.19 |
| 05-14-2025 | 00951 | #001247 | 20 | 525,900 | $76,753.94 |
| 05-07-2025 | 00950 | #001246 | 24 | 520,600 | $76,985.29 |
| 04-30-2025 | 00949 | #001245 | 28 | 497,600 | $76,630.13 |
| 04-23-2025 | 00948 | #001244 | 25 | 357,100 | $57,905.74 |
| 04-15-2025 | 00947 | #001243 | 22 | 519,100 | $70,954.12 |
| 04-09-2025 | 00946 | #001242 | 29 | 558,200 | $81,873.93 |
| 04-01-2025 | 00945 | #001241 | 23 | 513,000 | $68,254.63 |
| 03-26-2025 | 00943 | #001240 | 25 | 427,300 | $64,696.20 |
| 03-19-2025 | 00942 | #001239 | 18 | 407,000 | $68,619.81 |
| 03-12-2025 | 00941 | #001238 | 17 | 425,200 | $64,383.80 |
| 03-05-2025 | 00940 | #001237 | 12 | 319,000 | $53,078.42 |
| 02-26-2025 | 00939 | #001236 | 20 | 405,400 | $60,278.28 |
| 02-19-2025 | 00938 | #001235 | 17 | 424,800 | $65,645.49 |
| 02-12-2025 | 00937 | #001234 | 12 | 416,300 | $62,922.36 |
| 02-05-2025 | 00936 | #001233 | 26 | 401,900 | $62,435.71 |
| 01-29-2025 | 00935 | #001232 | 19 | 425,900 | $59,928.45 |
| 01-22-2025 | 00934 | #001231 | 31 | 495,400 | $68,288.21 |
| 01-15-2025 | 00933 | #001230 | 20 | 474,800 | $72,368.31 |
| 01-08-2025 | 00932 | #001229 | 12 | 300,100 | $46,244.66 |

**Promedios:**
- Items por Invoice: ~20 lineas de producto
- Piezas por Invoice: ~450,000 piezas
- Importe por Invoice: ~$68,500 USD
- Frecuencia: semanal (martes/miercoles)

### 3.9 Caso Especial: Invoice de Solventes

La hoja `Solvents 03-26-2025` contiene un Invoice de naturaleza completamente diferente:

```
Invoice#000944  |  DATE: Mar-24-2025
Packing Slip: N/A
Direccion emisor: 10512 19th Ave. SE - Suite 101 - Everett, WA - 98208
FAX# 425-657-0309 (no aparece en Invoices normales)
```

Items:
```
Alcohol | Delivered 11-19-2024 (Flx-Ref. A-115878) | PO: 26669 | WO: n/a | QTY: 1 | Cost: $546.02
Alcohol | Delivered 01-21-2025 (Flx-Ref. A-117109) | PO: 26669 | WO: n/a | QTY: 1 | Cost: $507.10
Alcohol | Delivered 02-13-2025 (Flx-Ref. A-118310) | PO: 26669 | WO: n/a | QTY: 1 | Cost: $517.57
Alcohol | Delivered 03-18-2025 (Flx-Ref. A-119344) | PO: 26669 | WO: n/a | QTY: 1 | Cost: $519.40
Total: 4 entregas | Total: $2,090.08
```

Este Invoice es para facturar **consumibles de proceso** (solventes) que FlexCon suministra a S.E.I.P. y que son diferentes al producto terminado. No tiene Packing Slip asociada porque no es un despacho de manufactura.

**Implicacion:** El sistema deberia poder crear Invoices "standalone" (sin Packing Slip) para facturar este tipo de consumibles o servicios. Este es un tipo de Invoice diferente al flujo principal Packing Slip -> Invoice.

### 3.10 Schema del Excel (Estructura de Datos del Invoice)

```
Invoice
  ├── invoice_number: string (ej: "00953")
  ├── invoice_date: date
  ├── packing_slip_number: string (ej: "#001249") -- referencia al PS
  ├── fob_location: string (constante "Tecate, Ca.")
  ├── sold_to: { name, address, city_state_zip }
  ├── shipped_to: { name, address, city_state_zip }
  ├── items[]:
  │     ├── description: string
  │     ├── item_number: string (codigo de parte)
  │     ├── lot_number: string (formato MMDDYYxNN)  -- diferente al PS
  │     ├── po_number: integer (nullable)
  │     ├── wo_number: integer (solo el core numerico)  -- diferente al PS
  │     ├── quantity: integer
  │     ├── unit_cost: decimal(10,6)  -- NUEVO vs Packing Slip
  │     └── line_total: decimal(10,2)  -- calculado: qty x unit_cost
  ├── fixed_charges[]:
  │     ├── { description: "Machine Maintenance", amount: 800.00 }
  │     ├── { description: "Administration Fee",  amount: 250.00 }
  │     └── { description: "SHIPPING COST",       amount: 450.00 }
  └── totals:
        ├── total_quantity: integer (suma de quantities)
        └── grand_total: decimal(10,2) (suma de line_totals + fixed_charges)
```

---

## 4. Gap Analysis

### 4.1 Gaps de Modelo de Datos (Invoice vs Sistema Actual)

| ID | Gap | Severidad | Descripcion |
|---|---|---|---|
| IG-01 | Alta | No existe una tabla `invoices` en la base de datos |
| IG-02 | Alta | No existe numeracion correlativa automatica para Invoices (00932...00953+) |
| IG-03 | Alta | No hay relacion entre un Invoice y su Packing Slip origen |
| IG-04 | Alta | La tabla `parts` no tiene campo `unit_price` (precio unitario por parte) |
| IG-05 | Alta | No existe mecanismo para almacenar el precio unitario al momento de facturacion (precio puede cambiar en el tiempo) |
| IG-06 | Media | Los cargos fijos (Machine Maintenance, Administration Fee, Shipping Cost) no estan configurados como entidades en el sistema |
| IG-07 | Media | No existe tabla `invoice_items` que registre los items facturados con precios snapshot |
| IG-08 | Media | El formato de `lot_number` en el Invoice (`MMDDYYxNN`) difiere del formato del sistema y del Packing Slip - requiere mapeo |
| IG-09 | Baja | No existe tabla de tipos de Invoice (producto terminado vs consumibles/servicios) |
| IG-10 | Baja | El numero de Invoice usa formato de 5 digitos (`00953`) vs el Packing Slip de 6 digitos (`001249`) |

### 4.2 Gaps de Logica de Negocio

| ID | Gap | Severidad | Descripcion |
|---|---|---|---|
| IG-11 | Alta | No existe flujo de conversion de Packing Slip a Invoice |
| IG-12 | Alta | No existe logica para calcular el monto total del Invoice (suma de items + cargos fijos) |
| IG-13 | Alta | No hay validacion de que solo Packing Slips en estado `shipped` puedan generar un Invoice |
| IG-14 | Media | No existe logica para aplicar cargos fijos automaticamente al crear un Invoice |
| IG-15 | Media | No hay control de "un Invoice por Packing Slip" (restriccion 1:1) |
| IG-16 | Media | No existe manejo del caso especial de Invoice sin Packing Slip (solventes/consumibles) |
| IG-17 | Baja | No hay logica de revision de precios (verificar si el precio unitario de cada parte es correcto antes de emitir) |

### 4.3 Gaps de Interfaz de Usuario

| ID | Gap | Severidad | Descripcion |
|---|---|---|---|
| IG-18 | Alta | No existe vista para crear un Invoice desde una Packing Slip despachada |
| IG-19 | Alta | No existe vista de prevista de impresion del documento FPL-12 (Invoice) |
| IG-20 | Media | No existe listado de Invoices con filtros y busqueda |
| IG-21 | Media | No existe formulario de edicion/confirmacion de precios unitarios antes de emitir |
| IG-22 | Baja | No existe dashboard financiero (total facturado por semana, mes, por parte) |

### 4.4 Gaps de Integracion

| ID | Gap | Severidad | Descripcion |
|---|---|---|---|
| IG-23 | Alta | No hay endpoint/ruta para generar el PDF del Invoice en formato FPL-12 |
| IG-24 | Media | No existe integracion desde el Packing Slip (`shipped`) para generar el Invoice correspondiente |
| IG-25 | Baja | No hay notificaciones al emitir un Invoice |

---

## 5. Requerimientos Funcionales

### 5.1 Requerimientos de Creacion de Invoice

**RF-INV-01:** El sistema debe permitir crear un Invoice a partir de una Packing Slip en estado `shipped`. La creacion debe pre-poblar automaticamente todos los campos del Invoice con los datos del Packing Slip correspondiente.

**RF-INV-02:** El sistema debe auto-generar el numero de Invoice de forma secuencial (ej: 00954 al continuar desde 00953).

**RF-INV-03:** Al crear el Invoice desde una Packing Slip, el sistema debe:
- Copiar todos los items del Packing Slip al Invoice
- Agregar automaticamente los tres cargos fijos (Machine Maintenance $800, Administration Fee $250, Shipping Cost $450)
- Presentar al usuario los precios unitarios sugeridos de cada parte (del catalogo de partes)
- Permitir al usuario confirmar o ajustar el precio unitario antes de guardar

**RF-INV-04:** El sistema debe calcular automaticamente el `line_total` de cada item (quantity x unit_cost) y el `grand_total` del Invoice (suma de todos los line_totals incluyendo cargos fijos).

**RF-INV-05:** Solo debe permitirse un Invoice por Packing Slip. El sistema debe bloquear la creacion de un segundo Invoice para una Packing Slip que ya tiene Invoice asociado.

**RF-INV-06:** El sistema debe permitir crear Invoices "standalone" (sin Packing Slip) para facturar consumibles, servicios o cargos especiales. Este tipo de Invoice debe tener una marca `type = 'standalone'` y no requiere Packing Slip.

### 5.2 Requerimientos de Precios

**RF-INV-07:** El catalogo de partes (`parts`) debe tener un campo `unit_price` (precio unitario) que sirva como valor sugerido al crear un Invoice.

**RF-INV-08:** El precio unitario en el Invoice debe almacenarse como **snapshot** en la tabla `invoice_items` (precio en el momento de facturacion). Cambios futuros en `parts.unit_price` no deben afectar Invoices ya emitidos.

**RF-INV-09:** El historial de precios unitarios por parte debe ser consultable (para auditorias o comparaciones).

**RF-INV-10:** Los cargos fijos (Machine Maintenance, Administration Fee, Shipping Cost) deben ser configurables en el sistema, no hardcodeados. Sus valores por defecto son $800, $250 y $450 respectivamente.

### 5.3 Requerimientos de Generacion de PDF

**RF-INV-11:** El sistema debe generar un PDF que reproduzca fielmente el formato FPL-12 del Excel, con el mismo layout de encabezado (FLEXCON, FPL-12, datos de contacto), tabla de items (con columnas UNIT COST y TOTAL), cargos fijos y fila de totales.

**RF-INV-12:** El PDF debe poder descargarse directamente desde la interfaz.

**RF-INV-13:** El nombre del archivo PDF debe seguir el formato: `Invoice_{NUMERO}_{FECHA}.pdf`.

**RF-INV-14:** A diferencia del Packing Slip, el Invoice tipicamente cabe en una sola pagina (20-30 items). Sin embargo, el sistema debe soportar impresion multipagina si los items exceden el limite por hoja.

### 5.4 Requerimientos de Gestion de Invoices

**RF-INV-15:** El Invoice debe tener estados: `draft` (borrador), `issued` (emitido/PDF generado), `paid` (pagado - opcional Fase 2).

**RF-INV-16:** Un Invoice en estado `issued` no debe poder modificarse en sus items ni precios. Solo debe poderse re-descargar el PDF.

**RF-INV-17:** El sistema debe mostrar un listado de todos los Invoices con filtros por: rango de fechas, numero de Invoice, Packing Slip asociada, estado.

**RF-INV-18:** Desde la vista de detalle de una Packing Slip despachada, debe existir un boton prominente "Crear Invoice" que inicie el flujo de creacion.

---

## 6. Arquitectura Propuesta

### 6.1 Nuevas Tablas de Base de Datos

#### Tabla: `invoices`

```sql
CREATE TABLE invoices (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number   VARCHAR(20)    NOT NULL UNIQUE,   -- '00954'
    invoice_date     DATE           NOT NULL,
    status           ENUM('draft','issued','paid') DEFAULT 'draft',
    type             ENUM('product','standalone') DEFAULT 'product',
    -- Referencia al Packing Slip (nullable para invoices standalone)
    packing_slip_id  BIGINT UNSIGNED NULLABLE FK -> packing_slips(id),
    -- Datos del encabezado (algunos se copian del PS, otros son propios del Invoice)
    fob_location     VARCHAR(100)   NOT NULL DEFAULT 'Tecate, Ca.',
    sold_to_name     VARCHAR(200)   NOT NULL,
    sold_to_address  TEXT           NOT NULL,
    shipped_to_name  VARCHAR(200)   NOT NULL,
    shipped_to_address TEXT         NOT NULL,
    -- Cargos fijos (snapshot al momento de emision)
    charge_machine_maintenance DECIMAL(10,2) NOT NULL DEFAULT 800.00,
    charge_administration_fee  DECIMAL(10,2) NOT NULL DEFAULT 250.00,
    charge_shipping_cost       DECIMAL(10,2) NOT NULL DEFAULT 450.00,
    -- Totales calculados (desnormalizados para rendimiento)
    total_quantity   INT            NULLABLE,           -- suma de quantities de items
    subtotal_items   DECIMAL(12,2)  NULLABLE,           -- suma de line_totals de items
    subtotal_charges DECIMAL(10,2)  NULLABLE,           -- suma de cargos fijos
    grand_total      DECIMAL(12,2)  NULLABLE,           -- subtotal_items + subtotal_charges
    -- Control
    notes            TEXT           NULLABLE,
    issued_at        TIMESTAMP      NULLABLE,
    paid_at          TIMESTAMP      NULLABLE,
    created_by       BIGINT UNSIGNED NULLABLE FK -> users(id),
    issued_by        BIGINT UNSIGNED NULLABLE FK -> users(id),
    created_at       TIMESTAMP,
    updated_at       TIMESTAMP,
    deleted_at       TIMESTAMP NULLABLE,  -- SoftDeletes

    INDEX idx_inv_number (invoice_number),
    INDEX idx_inv_packing_slip (packing_slip_id),
    INDEX idx_inv_date (invoice_date),
    INDEX idx_inv_status (status)
);
```

#### Tabla: `invoice_items`

```sql
CREATE TABLE invoice_items (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id       BIGINT UNSIGNED NOT NULL FK -> invoices(id) CASCADE DELETE,
    -- Referencia al packing_slip_item origen (nullable para items standalone)
    packing_slip_item_id BIGINT UNSIGNED NULLABLE FK -> packing_slip_items(id),
    lot_id           BIGINT UNSIGNED NULLABLE FK -> lots(id),
    work_order_id    BIGINT UNSIGNED NULLABLE FK -> work_orders(id),
    purchase_order_id BIGINT UNSIGNED NULLABLE FK -> purchase_orders(id),
    part_id          BIGINT UNSIGNED NULLABLE FK -> parts(id),
    -- Datos desnormalizados (snapshot al momento de facturacion)
    description      VARCHAR(255)  NOT NULL,          -- snapshot de part.description
    item_number      VARCHAR(100)  NULLABLE,           -- snapshot de part.number
    lot_number       VARCHAR(50)   NULLABLE,           -- snapshot del lot code del Invoice
    po_number        VARCHAR(50)   NULLABLE,           -- snapshot de po_number
    wo_number        VARCHAR(50)   NULLABLE,           -- snapshot de wo_number (formato Invoice)
    quantity         INT           NOT NULL,
    unit_cost        DECIMAL(10,6) NOT NULL,           -- precio unitario en el momento
    line_total       DECIMAL(12,2) NOT NULL,           -- quantity x unit_cost (calculado y guardado)
    sort_order       SMALLINT      NOT NULL DEFAULT 0,
    is_fixed_charge  BOOLEAN       NOT NULL DEFAULT FALSE, -- TRUE para Machine Maint, Admin Fee, Shipping
    created_at       TIMESTAMP,
    updated_at       TIMESTAMP,

    INDEX idx_ii_invoice (invoice_id),
    INDEX idx_ii_psi (packing_slip_item_id),
    INDEX idx_ii_lot (lot_id),
    INDEX idx_ii_wo (work_order_id)
);
```

#### Modificacion a tabla `parts` (migracion adicional):

```sql
ALTER TABLE parts
ADD COLUMN unit_price DECIMAL(10,6) NULLABLE AFTER label_spec
COMMENT 'Precio unitario por pieza en USD (valor de referencia para facturacion)';
```

**Nota:** `label_spec` ya fue propuesto en el analisis del Packing Slip. Si ambas migraciones se aplican secuencialmente, `unit_price` va despues de `label_spec`.

#### Modificacion a tabla `packing_slips` (vincular con Invoice):

```sql
ALTER TABLE packing_slips
ADD COLUMN invoice_id BIGINT UNSIGNED NULLABLE
AFTER shipped_by,
ADD FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL;
```

Esto permite la consulta rapida: "esta Packing Slip ya tiene Invoice generado?"

### 6.2 Configuracion de Cargos Fijos

Se propone una tabla de configuracion (o una entrada en una tabla general `settings`) para los cargos fijos:

```sql
-- Opcion A: En tabla settings general (si ya existe en el sistema)
INSERT INTO settings (key, value, description) VALUES
  ('invoice_charge_machine_maintenance', '800.00', 'Cargo fijo: Machine Maintenance'),
  ('invoice_charge_administration_fee',  '250.00', 'Cargo fijo: Administration Fee'),
  ('invoice_charge_shipping_cost',       '450.00', 'Cargo fijo: Shipping Cost');

-- Opcion B: Tabla dedicada
CREATE TABLE invoice_charge_configs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULLABLE,
    default_amount DECIMAL(10,2) NOT NULL,
    is_active   BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order  SMALLINT NOT NULL DEFAULT 0,
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);
```

### 6.3 Modelos Laravel

#### Modelo: `Invoice`

```
app/Models/Invoice.php

Relaciones:
- hasMany(InvoiceItem::class)
- belongsTo(PackingSlip::class)          -- nullable
- belongsTo(User::class, 'created_by')
- belongsTo(User::class, 'issued_by')

Constantes:
STATUS_DRAFT  = 'draft'
STATUS_ISSUED = 'issued'
STATUS_PAID   = 'paid'
TYPE_PRODUCT    = 'product'
TYPE_STANDALONE = 'standalone'

Metodos de interes:
- generateInvoiceNumber(): string   -- genera siguiente numero correlativo
- createFromPackingSlip(PackingSlip $ps): static  -- factory method
- addFixedCharges(): void           -- agrega los 3 cargos fijos automaticamente
- calculateTotals(): void           -- recalcula subtotales y grand_total
- generatePDF(): response           -- genera PDF en formato FPL-12
- markAsIssued(int $userId): bool   -- cambia status y registra issued_at
- canBeModified(): bool             -- solo draft puede modificarse
```

#### Modelo: `InvoiceItem`

```
app/Models/InvoiceItem.php

Relaciones:
- belongsTo(Invoice::class)
- belongsTo(PackingSlipItem::class)  -- nullable
- belongsTo(Lot::class)              -- nullable
- belongsTo(WorkOrder::class)        -- nullable
- belongsTo(PurchaseOrder::class)    -- nullable
- belongsTo(Part::class)             -- nullable

Accessors:
- getLineTotalAttribute(): recalcular si es necesario (quantity x unit_cost)
```

### 6.4 Servicio de Conversion Packing Slip -> Invoice

```
app/Services/InvoiceFromPackingSlipService.php

Responsabilidades:
1. Recibir un PackingSlip model en estado 'shipped'
2. Validar que no tiene Invoice ya generado
3. Crear el registro Invoice con encabezado copiado del PS
4. Por cada PackingSlipItem, crear un InvoiceItem con:
   - Datos copiados del PSItem (descripcion, item_number, qty, etc.)
   - unit_cost tomado de parts.unit_price (si existe) o 0 (a confirmar)
   - line_total calculado
5. Agregar los tres registros de cargos fijos (InvoiceItems con is_fixed_charge=TRUE)
6. Calcular y guardar totales en la tabla invoices
7. Marcar el Invoice como 'draft' (el usuario aun puede ajustar precios)
8. Retornar el Invoice creado

Manejo de errores:
- Si el PS no esta en estado 'shipped': lanzar BusinessException
- Si el PS ya tiene un Invoice: lanzar BusinessException
- Usar DB::transaction() para atomicidad
```

### 6.5 Servicio de Generacion de PDF del Invoice

```
app/Services/InvoicePDFService.php

Responsabilidades:
- Recibir un Invoice model con items cargados
- Renderizar vista Blade del Invoice (similar a la del Packing Slip pero con columnas adicionales)
- Calcular paginacion si los items exceden el limite (raro pero posible)
- Generar el PDF con dompdf
- Retornar el PDF como response (descarga) o path en storage

Vista Blade:
resources/views/invoices/print.blade.php

Layout: sin navegacion, ancho Letter (8.5" x 11")
Encabezado: FLEXCON, FPL-12, datos de contacto, referencia al PS
Tabla de items: Description, Item No., Lot No., P.O. No., W.O. No., Qty, Unit Cost, Total
Cargos fijos: Machine Maintenance, Administration Fee, Shipping Cost
Fila totales: suma de qty y suma de importes
```

### 6.6 Livewire Components Propuestos

| Componente | Ruta | Descripcion |
|---|---|---|
| `InvoiceList` | `/admin/invoices` | Listado de Invoices con filtros |
| `InvoiceCreate` | `/admin/invoices/create?packing_slip={id}` | Formulario de creacion desde PS (con precios) |
| `InvoiceShow` | `/admin/invoices/{id}` | Vista de detalle + boton generar PDF + boton emitir |
| `InvoicePrintView` | `/admin/invoices/{id}/print` | Vista solo de impresion para PDF |
| `InvoiceStandaloneCreate` | `/admin/invoices/create-standalone` | Creacion de Invoice sin PS (consumibles) |

### 6.7 Rutas Propuestas

```php
Route::middleware(['auth', 'verified', 'role:admin|Facturacion|Envios'])->group(function () {
    Route::get('/invoices', InvoiceList::class)->name('invoices.index');
    Route::get('/invoices/create', InvoiceCreate::class)->name('invoices.create');
    Route::get('/invoices/create-standalone', InvoiceStandaloneCreate::class)
        ->name('invoices.create-standalone');
    Route::get('/invoices/{invoice}', InvoiceShow::class)->name('invoices.show');
    Route::get('/invoices/{invoice}/print', InvoicePrintView::class)->name('invoices.print');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPDF'])
        ->name('invoices.pdf');
});
```

### 6.8 Logica de Numeracion de Invoices

```php
// En Invoice::generateInvoiceNumber()
public static function generateInvoiceNumber(): string
{
    $last = static::withTrashed()
        ->where('type', 'product')  // solo Invoices de producto para la correlativa normal
        ->orderByRaw("CAST(invoice_number AS UNSIGNED) DESC")
        ->first();

    if (!$last) {
        return '00954'; // Continuar desde el ultimo del Excel
    }

    $lastNumber = (int) $last->invoice_number;
    return str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
}
```

**Importante:** El Excel termina en Invoice #00953. El primer Invoice del sistema debe ser **00954** para mantener continuidad.

### 6.9 Diagrama de Relaciones de la Arquitectura Completa (PS + Invoice)

```
purchase_orders
  └── po_number, quantity, unit_price

work_orders
  └── wo_number, purchase_order_id, sent_list_id

lots
  ├── work_order_id
  └── packing_slip_id -> packing_slips (nuevo, del analisis PS)

parts
  ├── number (item_no)
  ├── description
  ├── label_spec       (nuevo, del analisis PS)
  └── unit_price       (nuevo, del analisis Invoice)

packing_slips
  ├── slip_number, slip_date, status
  ├── sold_to_*, shipped_to_*
  ├── footer fields (cajas, firmas)
  ├── invoice_id -> invoices (nuevo - referencia al Invoice generado)
  └── created_by, shipped_by -> users

packing_slip_items
  ├── packing_slip_id -> packing_slips
  ├── lot_id -> lots
  ├── work_order_id -> work_orders
  ├── purchase_order_id -> purchase_orders (nullable)
  └── snapshot fields (wo_number, po_number, item_number, description,
                        quantity, lot_date_code, label_spec)

invoices
  ├── invoice_number, invoice_date, status, type
  ├── packing_slip_id -> packing_slips (nullable)
  ├── sold_to_*, shipped_to_*, fob_location
  ├── fixed charges (machine_maintenance, admin_fee, shipping_cost)
  ├── totals (total_quantity, subtotal_items, subtotal_charges, grand_total)
  └── created_by, issued_by -> users

invoice_items
  ├── invoice_id -> invoices
  ├── packing_slip_item_id -> packing_slip_items (nullable)
  ├── lot_id -> lots (nullable)
  ├── work_order_id -> work_orders (nullable)
  ├── purchase_order_id -> purchase_orders (nullable)
  ├── part_id -> parts (nullable)
  ├── snapshot fields (description, item_number, lot_number, po_number,
  │                    wo_number, quantity)
  ├── unit_cost, line_total
  └── is_fixed_charge (para Machine Maint, Admin Fee, Shipping Cost)
```

---

## 7. Flujo de Conversion: Packing Slip -> Invoice

### 7.1 Flujo de Negocio (Perspectiva del Usuario)

```
[EMPAQUE]
   |
   v
Packing Slip en estado 'shipped'
   |
   v
Usuario de Envios/Facturacion accede al detalle del Packing Slip
   |
   v
Hace clic en "Crear Invoice"
   |
   v
[SISTEMA]
  1. Valida que PS este en estado 'shipped'
  2. Valida que PS no tenga Invoice ya
  3. Crea Invoice en estado 'draft' con datos del PS
  4. Copia todos los PSItems al Invoice con unit_cost de parts.unit_price
  5. Agrega los 3 cargos fijos automaticamente
  6. Calcula totales
   |
   v
[USUARIO]
Vista de edicion del Invoice (draft)
  - Encabezado pre-llenado (editable)
  - Lista de items con precios pre-llenados (editables)
  - Cargos fijos (editables en caso de excepcion)
  - Total calculado en tiempo real
   |
   v
Usuario confirma/ajusta precios y hace clic en "Emitir Invoice"
   |
   v
[SISTEMA]
  1. Recalcula todos los totales con los precios finales
  2. Cambia estado a 'issued'
  3. Genera PDF en formato FPL-12
  4. Registra issued_at y issued_by
   |
   v
[USUARIO]
Descarga el PDF del Invoice
```

### 7.2 Flujo Tecnico (Perspectiva del Codigo)

```php
// PASO 1: Llamada desde InvoiceCreate Livewire component
// URL: /admin/invoices/create?packing_slip_id=123

// PASO 2: InvoiceFromPackingSlipService::createFromPackingSlip()
DB::transaction(function () use ($packingSlip, $userId) {

    // Crear Invoice base
    $invoice = Invoice::create([
        'invoice_number'    => Invoice::generateInvoiceNumber(),
        'invoice_date'      => $packingSlip->slip_date,
        'status'            => Invoice::STATUS_DRAFT,
        'type'              => Invoice::TYPE_PRODUCT,
        'packing_slip_id'   => $packingSlip->id,
        'fob_location'      => $packingSlip->fob_location,
        'sold_to_name'      => $packingSlip->sold_to_name,
        'sold_to_address'   => $packingSlip->sold_to_address,
        'shipped_to_name'   => $packingSlip->shipped_to_name,
        'shipped_to_address'=> $packingSlip->shipped_to_address,
        'charge_machine_maintenance' => config('invoice.charge_machine_maintenance', 800.00),
        'charge_administration_fee'  => config('invoice.charge_administration_fee', 250.00),
        'charge_shipping_cost'       => config('invoice.charge_shipping_cost', 450.00),
        'created_by'        => $userId,
    ]);

    // Crear InvoiceItems desde PackingSlipItems
    $sortOrder = 1;
    foreach ($packingSlip->items()->orderBy('sort_order')->get() as $psItem) {
        $unitCost = $psItem->lot?->workOrder?->purchaseOrder?->unit_price
                    ?? $psItem->part?->unit_price
                    ?? 0;

        InvoiceItem::create([
            'invoice_id'           => $invoice->id,
            'packing_slip_item_id' => $psItem->id,
            'lot_id'               => $psItem->lot_id,
            'work_order_id'        => $psItem->work_order_id,
            'purchase_order_id'    => $psItem->purchase_order_id,
            'part_id'              => $psItem->lot?->workOrder?->purchaseOrder?->part_id,
            'description'          => $psItem->description,
            'item_number'          => $psItem->item_number,
            'lot_number'           => $psItem->lot_date_code,
            'po_number'            => $psItem->po_number,
            'wo_number'            => $psItem->wo_number,
            'quantity'             => $psItem->quantity,
            'unit_cost'            => $unitCost,
            'line_total'           => $psItem->quantity * $unitCost,
            'sort_order'           => $sortOrder++,
            'is_fixed_charge'      => false,
        ]);
    }

    // Agregar cargos fijos
    $fixedCharges = [
        ['description' => 'Machine Maintenance', 'amount' => $invoice->charge_machine_maintenance],
        ['description' => 'Administration Fee',  'amount' => $invoice->charge_administration_fee],
        ['description' => 'SHIPPING COST',        'amount' => $invoice->charge_shipping_cost],
    ];

    foreach ($fixedCharges as $charge) {
        InvoiceItem::create([
            'invoice_id'    => $invoice->id,
            'description'   => $charge['description'],
            'quantity'      => 1,
            'unit_cost'     => $charge['amount'],
            'line_total'    => $charge['amount'],
            'sort_order'    => $sortOrder++,
            'is_fixed_charge' => true,
        ]);
    }

    // Calcular totales
    $invoice->calculateTotals()->save();

    // Vincular el invoice al packing_slip
    $packingSlip->update(['invoice_id' => $invoice->id]);

    return $invoice;
});
```

### 7.3 Diagrama de Estados del Invoice

```
DRAFT
  |
  |-- [Usuario edita precios/confirma] --> DRAFT (sin cambio de estado)
  |
  |-- [Usuario hace clic "Emitir"] --> ISSUED
  |                                       |
  |                                       |-- [PDF generado, datos bloqueados]
  |                                       |
  |                                       |-- [Opcional: marcar como Pagado] --> PAID
  |
  |-- [Usuario cancela] --> DELETED (soft delete, solo si esta en DRAFT)
```

---

## 8. Evaluacion de Riesgos

### 8.1 Riesgos Tecnicos

| ID | Riesgo | Probabilidad | Impacto | Mitigacion |
|---|---|---|---|---|
| RI-01 | Los precios unitarios en `parts.unit_price` pueden estar desactualizados o en NULL, requiriendo entrada manual en cada Invoice | Alta | Media | Mostrar el campo como editable y obligatorio antes de emitir; importar precios historicos del Excel al inicializar el catalogo |
| RI-02 | Errores de precision en calculos de punto flotante (ej: 100000 x 0.138 = 13800.000000000002) | Alta | Baja | Usar `DECIMAL(10,6)` en BD para unit_cost; usar `bcmul()`/`number_format()` en PHP para calculos; redondear a 2 decimales en TOTAL |
| RI-03 | El formato del lot_number del Invoice (MMDDYYxNN) no tiene correspondencia directa con el lot_number del sistema (YYMMDD+letra+mesa) | Media | Media | Almacenar el lot_number del Invoice como snapshot sin intentar hacer join con lots; documentar la diferencia para el equipo |
| RI-04 | El formato del W.O. No. del Invoice (7 digitos) no coincide con el de la app (WO-2025-XXXXX) ni con el del Packing Slip (W01980231001) | Media | Media | Almacenar como snapshot; si se confirma que los numeros son del sistema legacy, agregar campo `legacy_wo_reference` |
| RI-05 | Concurrencia: dos usuarios creando el Invoice de la misma Packing Slip al mismo tiempo | Baja | Alta | Usar `DB::transaction()` con lock optimista; agregar constraint UNIQUE en `invoices.packing_slip_id` |
| RI-06 | El layout del PDF del Invoice tiene mas columnas que el Packing Slip (agrega UNIT COST y TOTAL), lo que puede hacer mas critico el ajuste del ancho de columnas | Media | Media | Usar unidades absolutas en la vista Blade de impresion; testear con datasets reales desde el inicio |

### 8.2 Riesgos de Negocio

| ID | Riesgo | Probabilidad | Impacto | Mitigacion |
|---|---|---|---|---|
| RI-07 | Inconsistencia entre los datos del Packing Slip (items, cantidades) y los del Invoice si el usuario modifica items en el Invoice draft | Media | Alta | Limitar la edicion del Invoice draft a solo los precios unitarios, no a los items ni cantidades (que deben ser identicos al PS) |
| RI-08 | El Invoice puede emitirse con precios incorrectos si unit_price en parts no esta actualizado | Alta | Alta | Agregar paso de revision/confirmacion de precios antes de emitir; mostrar alerta si unit_price es 0 o NULL |
| RI-09 | Los cargos fijos ($800 + $250 + $450 = $1,500) pueden cambiar en el futuro | Baja | Media | Hacer los montos configurables desde el inicio; guardar los valores como snapshot en la tabla invoices |
| RI-10 | El caso especial de Invoice de Solventes puede crear confusion con el flujo normal | Baja | Baja | Crear un tipo separado `TYPE_STANDALONE` y una interfaz de creacion diferente |
| RI-11 | La numeracion del Invoice podria saltar numeros si se crean Invoices de tipos distintos (producto vs consumibles) o si hay Invoices legacy no incluidos en el Excel | Media | Media | Inicializar desde 00954; documentar claramente que puede haber gaps en la numeracion |

### 8.3 Riesgos de Implementacion

| ID | Riesgo | Probabilidad | Impacto | Mitigacion |
|---|---|---|---|---|
| RI-12 | Dependencia de la implementacion del Packing Slip: el Invoice no puede desarrollarse hasta que la Packing Slip este implementada | Alta (es una dependencia real) | Alta | Desarrollar en paralelo: BD e infraestructura del Invoice puede crearse antes; la UI del Invoice espera a la UI del Packing Slip |
| RI-13 | La migracion de `unit_price` a `parts` puede entrar en conflicto con la migracion de `label_spec` del analisis de Packing Slip | Media | Baja | Crear una sola migracion que agregue ambos campos juntos |
| RI-14 | El servicio `InvoiceFromPackingSlipService` necesita que la relacion `PackingSlipItem -> Part -> unit_price` este bien definida; si el catalogo no tiene precios, el servicio creara items con unit_cost = 0 | Alta | Media | Agregar validacion previa al crear el Invoice: advertir si hay items sin precio; nunca bloquear la creacion, pero si forzar revision |

---

## 9. Fases de Implementacion (Continuando del Shipping List)

Las fases del Invoice son **dependientes** de las fases del Packing Slip. Se deben implementar en orden secuencial.

### Fase 5: Infraestructura del Invoice (Estimado: 2-3 dias)

*Prerequisito: Fase 1 del Packing Slip completada (modelos y BD base)*

**Alcance:**
- Migracion: agregar campo `unit_price` a tabla `parts` (junto con `label_spec` si no se hizo antes)
- Migracion: crear tabla `invoices`
- Migracion: crear tabla `invoice_items`
- Migracion: agregar campo `invoice_id` a tabla `packing_slips`
- Modelo `Invoice` con relaciones, constantes, `generateInvoiceNumber()`, `calculateTotals()`, `canBeModified()`
- Modelo `InvoiceItem` con relaciones
- Actualizacion del modelo `PackingSlip` (relacion con Invoice)
- Actualizacion del modelo `Part` (campo `unit_price`)
- Tests unitarios de los nuevos modelos

**Entregables:**
- 4 migraciones
- 2 nuevos modelos
- 2 modelos actualizados
- Suite de tests unitarios

**Criterio de exito:** Las migraciones corren sin errores; los modelos tienen relaciones funcionales.

---

### Fase 6: Servicio de Conversion y Logica de Negocio (Estimado: 2-3 dias)

*Prerequisito: Fases 1-2 del Packing Slip + Fase 5 del Invoice*

**Alcance:**
- `InvoiceFromPackingSlipService`: logica completa de conversion PS -> Invoice
- `InvoicePDFService`: generacion del PDF en formato FPL-12
- Vista Blade de impresion del Invoice (`resources/views/invoices/print.blade.php`)
- Configuracion de los cargos fijos (en `config/invoice.php` o tabla `settings`)
- Tests de integracion del servicio de conversion
- Tests unitarios del servicio PDF

**Entregables:**
- 2 servicios
- 1 vista Blade de impresion
- 1 archivo de configuracion
- Tests de integracion y unitarios

**Criterio de exito:** La conversion PS -> Invoice crea correctamente todos los InvoiceItems con precios; el PDF generado es fiel al formato FPL-12.

---

### Fase 7: Interfaz de Usuario del Invoice (Estimado: 3-4 dias)

*Prerequisito: Fases 2-3 del Packing Slip + Fase 6 del Invoice*

**Alcance:**
- Componente `InvoiceList`: listado paginado con filtros por fecha, numero, estado, PS asociada
- Componente `InvoiceCreate`: formulario de creacion desde PS con confirmacion de precios
  - Pre-cargado con datos del PS
  - Tabla editable de precios unitarios (solo los precios son editables, no items ni cantidades)
  - Calculo en tiempo real del total con Alpine.js
  - Boton "Guardar como Borrador" y "Emitir"
- Componente `InvoiceShow`: vista de detalle + boton PDF + boton emitir (si draft)
- `InvoiceController@downloadPDF`: endpoint para generar y descargar PDF
- Agregar rutas en `admin.php`
- Actualizar campo `unit_price` en formulario de creacion/edicion de Parts
- Boton "Crear Invoice" en la vista de detalle del Packing Slip despachado

**Entregables:**
- 3 componentes Livewire
- 3 vistas Blade correspondientes
- Actualizacion de rutas
- Actualizacion de la vista de Packing Slip Show

**Criterio de exito:** El flujo completo desde PS -> crear Invoice -> editar precios -> emitir -> descargar PDF funciona sin errores.

---

### Fase 8: Caso Especial y Refinamientos (Estimado: 2-3 dias)

*Prerequisito: Fase 7*

**Alcance:**
- Componente `InvoiceStandaloneCreate`: creacion de Invoice sin PS (para solventes/servicios)
- Importacion historica de precios del Excel (poblar `parts.unit_price` con los precios del archivo Invoice 2025.xlsx para que el catalogo tenga precios de referencia)
- Indicador en la vista `PackingSlipShow` si el Packing Slip ya tiene Invoice generado
- Tests de integracion del flujo completo
- Actualizacion de documentacion

**Entregables:**
- 1 componente Livewire adicional
- Script de importacion de precios historicos (Seeder o comando Artisan)
- Tests de integracion

**Criterio de exito:** El catalogo de Parts tiene precios unitarios importados; el Invoice standalone funciona; no hay regresiones en el flujo PS.

---

### Resumen de Estimaciones (Invoice + Packing Slip)

| Fase | Nombre | Dias estimados | Dependencias |
|---|---|---|---|
| 1 | Infra Packing Slip (BD + Modelos) | 3-4 | Ninguna |
| 2 | UI Packing Slip (Listado + Creacion) | 4-5 | Fase 1 |
| 3 | Vista Detalle PS + PDF | 4-5 | Fase 2 |
| 4 | Integracion PS con Flujo Existente | 2-3 | Fase 3 |
| **Subtotal MVP Packing Slip** | | **13-17 dias** | |
| 5 | Infra Invoice (BD + Modelos) | 2-3 | Fase 1 |
| 6 | Servicio Conversion + PDF Invoice | 2-3 | Fases 3 + 5 |
| 7 | UI Invoice Completa | 3-4 | Fases 4 + 6 |
| 8 | Caso Especial + Refinamientos | 2-3 | Fase 7 |
| **Subtotal MVP Invoice** | | **9-13 dias** | |
| **TOTAL MVP Completo (PS + Invoice)** | | **22-30 dias** | |
| Fase 9 (opcional) | Dashboard Financiero + Reportes | 3-5 | Fase 8 |
| **TOTAL con Fase 9** | | **25-35 dias** | |

---

## 10. Preguntas Abiertas

Las siguientes preguntas requieren respuesta antes o durante la implementacion:

**P-INV-01 (Alta prioridad - Fase 5):**
Los precios unitarios del Excel varian entre facturas para la misma parte y PO. Por ejemplo `189-10071 STS H-C4-1` aparece con `0.1486`, `0.1634` y `0.1486` en diferentes semanas. Pregunta: el precio por unidad es fijo por parte o varia por PO (Purchase Order)? Si varia por PO, el precio correcto deberia venir de `purchase_orders.unit_price` (ya existe en el modelo PO), no de `parts.unit_price`.

**P-INV-02 (Alta prioridad - Fase 5):**
El Excel muestra precios con hasta 4 decimales (ej: `0.0912`, `0.1752`, `0.1683`). La precision requerida es de 4 decimales? Confirmar si en la tabla `purchase_orders` el campo `unit_price` ya tiene la precision adecuada o si requiere alteracion.

**P-INV-03 (Alta prioridad - Fase 6):**
El campo LOT NO. del Invoice tiene formato `MMDDYYxNN` (ej: `052625x01`) mientras el Packing Slip usa `YYMMDD+letra+mesa` (ej: `250515A22`). Pregunta: son realmente el mismo campo o son codigos diferentes que coexisten? Quien asigna el LOT NO. del Invoice (el sistema o el operador manualmente)?

**P-INV-04 (Alta prioridad - Fase 6):**
El W.O. No. del Invoice solo muestra los digitos del medio (ej: `1980231`) mientras el Packing Slip tiene el formato completo `W01980231001`. Confirmar que son el mismo WO y que es seguro mapearlos. Si la logica de busqueda debe coincidir ambos formatos, se necesita una funcion de normalizacion.

**P-INV-05 (Media prioridad - Fase 5):**
Los cargos fijos siempre son $800 + $250 + $450 en todos los Invoices del Excel. Confirmar: estos montos son invariables o pueden cambiar para ciertos envios? Si son invariables, pueden quedarse en configuracion. Si cambian por cliente o por tipo de envio, se necesita un modelo de tarifas mas sofisticado.

**P-INV-06 (Media prioridad - Fase 7):**
En el Invoice, los items se listan en un orden aparente (mayor a menor cantidad). El orden es relevante para el cliente o es libre? Si el orden es relevante, debe heredarse del Packing Slip o puede reordenarse en el Invoice?

**P-INV-07 (Media prioridad - Fase 8):**
El Invoice de Solventes (Invoice#000944) tiene un formato diferente de numero (`000944` con 6 digitos) y una direccion del emisor diferente (Everett, WA en vez de Bigfork, MT). Existe algun criterio para cuando usar cada direccion/formato? Este tipo de Invoice especial sera frecuente?

**P-INV-08 (Baja prioridad - General):**
El historial del Excel cubre desde Invoice #00932 (Ene-2025) hasta #00953 (May-2025). Existen Invoices anteriores a 2025 que deban importarse al sistema? O solo se trabaja hacia adelante desde la implementacion?

**P-INV-09 (Baja prioridad - General):**
En el futuro, podria haber Invoices parciales (facturar solo parte de un Packing Slip)? El diseno actual asume 1 Invoice = 1 Packing Slip completo. Si se requieren Invoices parciales, el schema necesita modificacion.

**P-INV-10 (Baja prioridad - Fase 9):**
Para el modulo de reportes financieros, que metricas son prioritarias? Sugeridas: (a) total facturado por semana/mes, (b) top partes por importe, (c) comparacion de precios vs PO original, (d) cuentas por cobrar (Invoices issued sin marcar como paid).

---

## 11. Referencias Tecnicas

- **Archivo Excel fuente:** `C:/xampp/htdocs/flexcon-tracker/Diagramas_flujo/Estructura/docs/ef/FPL-12 Invoice 2025.xlsx`
- **Clave del documento:** FPL-12, Revision 01
- **Documento relacionado:** `01_shipping_list_analysis.md` (Packing Slip FPL-10)
- **Modelos existentes relacionados:** `PackingSlip`, `PackingSlipItem`, `WorkOrder`, `PurchaseOrder`, `Part`, `Lot`
- **Modelos nuevos propuestos:** `Invoice`, `InvoiceItem`
- **Servicios propuestos:** `InvoiceFromPackingSlipService`, `InvoicePDFService`
- **Componentes Livewire propuestos:** `InvoiceList`, `InvoiceCreate`, `InvoiceShow`, `InvoicePrintView`, `InvoiceStandaloneCreate`
- **Libreria PDF:** `barryvdh/laravel-dompdf` (misma que para Packing Slip, evita dependencia adicional)
- **Rango de Invoice historico:** #00932 (Ene-2025) a #00953 (May-2025); siguiente: #00954
- **Rango de Packing Slip referenciado:** #001229 (Ene-2025) a #001249 (May-2025)
- **Monto tipico por Invoice:** $46,000 - $82,000 USD
- **Cargos fijos constantes:** Machine Maintenance $800 + Administration Fee $250 + Shipping Cost $450 = $1,500 total

---

*Documento generado el 2026-03-05. Version 1.0 - Analisis inicial basado en lectura del Excel FPL-12 y contexto del analisis previo del Packing Slip FPL-10.*
