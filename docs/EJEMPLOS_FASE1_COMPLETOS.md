# Ejemplos Completos - Fase 1: Fundamentos de Órdenes

Este documento contiene ejemplos de datos que cumplen con todos los requisitos de la Fase 1 y están listos para usar con el Capacity Wizard.

## Resumen del Flujo Fase 1

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        FLUJO COMPLETO FASE 1                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  1. PARTE          2. PRECIO         3. ESTÁNDAR        4. PO               │
│  ┌──────────┐      ┌──────────┐      ┌──────────┐      ┌──────────┐         │
│  │ Crear    │ ──▶  │ Crear    │ ──▶  │ Crear    │ ──▶  │ Crear    │         │
│  │ Part     │      │ Price    │      │ Standard │      │ PO       │         │
│  └──────────┘      └──────────┘      └──────────┘      └────┬─────┘         │
│                                                              │               │
│                                                              ▼               │
│                                                        ┌──────────┐         │
│                                                        │ Aprobar  │         │
│                                                        │ PO       │         │
│                                                        └────┬─────┘         │
│                                                              │               │
│                                                              ▼               │
│                                                        ┌──────────┐         │
│                                                        │ WO       │         │
│                                                        │ (Auto)   │         │
│                                                        └────┬─────┘         │
│                                                              │               │
│                                                              ▼               │
│                                                     ┌────────────────┐      │
│                                                     │ CAPACITY       │      │
│                                                     │ WIZARD         │      │
│                                                     └────────────────┘      │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Flujo Detallado del Purchase Order

El flujo del Purchase Order es el corazón de la Fase 1. Aquí se valida el precio y se crea automáticamente el Work Order.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    FLUJO DETALLADO DEL PURCHASE ORDER                        │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│                         ┌─────────────────┐                                  │
│                         │  📄 Recibir PO  │                                  │
│                         │   (pending)     │                                  │
│                         └────────┬────────┘                                  │
│                                  │                                           │
│                                  ▼                                           │
│                         ┌─────────────────┐                                  │
│                         │ 🔍 Validar      │                                  │
│                         │    Precio       │                                  │
│                         └────────┬────────┘                                  │
│                                  │                                           │
│                    ┌─────────────┼─────────────┐                             │
│                    │             │             │                             │
│                    ▼             ▼             ▼                             │
│           ┌──────────────┐ ┌──────────┐ ┌──────────────┐                     │
│           │ ❌ Precio    │ │ ✅ Precio│ │ ❌ Rechazar  │                     │
│           │ Incorrecto   │ │ Correcto │ │ Manualmente  │                     │
│           └──────┬───────┘ └────┬─────┘ └──────┬───────┘                     │
│                  │              │              │                             │
│                  ▼              ▼              ▼                             │
│           ┌──────────────┐ ┌──────────┐ ┌──────────────┐                     │
│           │ pending_     │ │ approved │ │  rejected    │                     │
│           │ correction   │ │          │ │              │                     │
│           └──────┬───────┘ └────┬─────┘ └──────────────┘                     │
│                  │              │                                            │
│                  │              ▼                                            │
│                  │       ┌──────────────┐                                    │
│                  │       │ 📋 Crear WO  │                                    │
│                  │       │ Automático   │                                    │
│                  │       │ (Open)       │                                    │
│                  │       └──────────────┘                                    │
│                  │                                                           │
│                  ▼                                                           │
│           ┌──────────────┐                                                   │
│           │ 👤 Usuario   │                                                   │
│           │ Corrige      │                                                   │
│           │ Precio       │                                                   │
│           └──────┬───────┘                                                   │
│                  │                                                           │
│                  └──────────────▶ (Vuelve a Validar)                         │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Estados del Purchase Order

| Estado | Descripción | Siguiente Acción |
|--------|-------------|------------------|
| `pending` | PO recién creado, esperando aprobación | Aprobar o Rechazar |
| `approved` | Precio validado, WO creado automáticamente | Listo para producción |
| `pending_correction` | Precio incorrecto, necesita corrección | Editar PO y corregir precio |
| `rejected` | PO rechazado manualmente | Fin del flujo |

### Lógica de Validación de Precios

Cuando se intenta aprobar un PO, el sistema:

1. **Busca el precio activo** para la parte (`prices` donde `active = true`)
2. **Determina el tier** según la cantidad del PO:
   - Cantidad 1-999 → `tier_1_999`
   - Cantidad 1,000-10,999 → `tier_1000_10999`
   - Cantidad 11,000-99,999 → `tier_11000_99999`
   - Cantidad 100,000+ → `tier_100000_plus`
3. **Compara el precio** del PO con el tier correspondiente
4. **Si coincide** → Aprueba PO y crea WO
5. **Si NO coincide** → Marca como `pending_correction`

### Ejemplo de Validación

```
Parte: CONN-001
Cantidad en PO: 5,000 unidades
Precio en PO: $2.25

Precios registrados:
├── tier_1_999: $2.50
├── tier_1000_10999: $2.25  ← Este aplica (5000 está en rango 1000-10999)
├── tier_11000_99999: $2.00
└── tier_100000_plus: $1.80

Resultado: ✅ PRECIO VÁLIDO (2.25 == 2.25)
→ PO aprobado
→ WO creado automáticamente
```

### Ejemplo de Error de Precio

```
Parte: CONN-001
Cantidad en PO: 5,000 unidades
Precio en PO: $2.50  ← INCORRECTO

Precios registrados:
├── tier_1_999: $2.50
├── tier_1000_10999: $2.25  ← Este debería aplicar
├── tier_11000_99999: $2.00
└── tier_100000_plus: $1.80

Resultado: ❌ PRECIO INVÁLIDO (2.50 != 2.25)
→ PO marcado como pending_correction
→ Usuario debe editar y cambiar precio a $2.25
```

---

## Requisitos Previos (Catálogos Base)

Antes de crear los datos de Fase 1, necesitas tener estos catálogos:

### 1. Departamento
**URL:** `/admin/departments/create`

| Campo | Valor |
|-------|-------|
| name | Producción |
| description | Departamento de producción |

### 2. Área
**URL:** `/admin/areas/create`

| Campo | Valor |
|-------|-------|
| name | Ensamble Principal |
| department_id | Producción |

### 3. Turno
**URL:** `/admin/shifts/create`

| Campo | Valor |
|-------|-------|
| name | Turno Matutino |
| start_time | 06:00 |
| end_time | 14:00 |
| active | ✓ |

### 4. Break Time
**URL:** `/admin/break-times/create`

| Campo | Valor |
|-------|-------|
| name | Descanso |
| start_break_time | 10:00 |
| end_break_time | 10:30 |
| shift_id | Turno Matutino |
| active | ✓ |

### 5. Mesa de Trabajo
**URL:** `/admin/tables/create`

| Campo | Valor |
|-------|-------|
| number | MESA-001 |
| employees | 3 |
| area_id | Ensamble Principal |
| active | ✓ |

---

## Ejemplo 1: Conector Eléctrico (Flujo Exitoso)

### Paso 1.1: Crear Parte
**URL:** `/admin/parts/create`

| Campo | Valor |
|-------|-------|
| number | CONN-001 |
| item_number | ITEM-CONN-001 |
| unit_of_measure | PZA |
| description | Conector eléctrico tipo A |
| active | ✓ |

### Paso 1.2: Crear Precio
**URL:** `/admin/prices/create`

| Campo | Valor |
|-------|-------|
| part_id | CONN-001 |
| unit_price | 2.5000 |
| tier_1_999 | 2.5000 |
| tier_1000_10999 | 2.2500 |
| tier_11000_99999 | 2.0000 |
| tier_100000_plus | 1.8000 |
| effective_date | 2025-01-01 |
| active | ✓ |

### Paso 1.3: Crear Estándar
**URL:** `/admin/standards/create`

| Campo | Valor |
|-------|-------|
| part_id | CONN-001 |
| units_per_hour | 100 |
| work_table_id | MESA-001 |
| persons_1 | 80 |
| persons_2 | 150 |
| persons_3 | 200 |
| effective_date | 2025-01-01 |
| active | ✓ |
| description | Estándar para conector eléctrico |

### Paso 1.4: Crear Purchase Order
**URL:** `/admin/purchase-orders/create`

| Campo | Valor |
|-------|-------|
| po_number | PO-2026-001 |
| part_id | CONN-001 |
| po_date | 2026-01-01 |
| due_date | 2026-01-15 |
| quantity | 5000 |
| unit_price | 2.2500 |
| comments | Orden de conectores para cliente ABC |
| pdf_file | (cualquier PDF) |

**Nota:** El precio 2.2500 corresponde al tier_1000_10999 (cantidad 5000 está en rango 1000-10999)

### Paso 1.5: Aprobar PO y Crear WO
1. Ve a `/admin/purchase-orders`
2. Haz clic en PO-2026-001
3. Haz clic en **"Aprobar y Crear WO"**
4. El sistema creará automáticamente WO-2026-00001 con estado "Open"

---

## Ejemplo 2: Arnés de Cables (Flujo Exitoso)

### Paso 2.1: Crear Parte
**URL:** `/admin/parts/create`

| Campo | Valor |
|-------|-------|
| number | HARNESS-001 |
| item_number | ITEM-HARNESS-001 |
| unit_of_measure | PZA |
| description | Arnés de cables 12 pines |
| active | ✓ |

### Paso 2.2: Crear Precio
**URL:** `/admin/prices/create`

| Campo | Valor |
|-------|-------|
| part_id | HARNESS-001 |
| unit_price | 15.0000 |
| tier_1_999 | 15.0000 |
| tier_1000_10999 | 13.5000 |
| tier_11000_99999 | 12.0000 |
| tier_100000_plus | 10.5000 |
| effective_date | 2025-01-01 |
| active | ✓ |

### Paso 2.3: Crear Estándar
**URL:** `/admin/standards/create`

| Campo | Valor |
|-------|-------|
| part_id | HARNESS-001 |
| units_per_hour | 25 |
| work_table_id | MESA-001 |
| persons_1 | 20 |
| persons_2 | 40 |
| persons_3 | 55 |
| effective_date | 2025-01-01 |
| active | ✓ |
| description | Estándar para arnés de cables |

### Paso 2.4: Crear Purchase Order
**URL:** `/admin/purchase-orders/create`

| Campo | Valor |
|-------|-------|
| po_number | PO-2026-002 |
| part_id | HARNESS-001 |
| po_date | 2026-01-01 |
| due_date | 2026-01-20 |
| quantity | 500 |
| unit_price | 15.0000 |
| comments | Arneses para proyecto XYZ |
| pdf_file | (cualquier PDF) |

**Nota:** El precio 15.0000 corresponde al tier_1_999 (cantidad 500 está en rango 1-999)

### Paso 2.5: Aprobar PO y Crear WO
1. Ve a `/admin/purchase-orders`
2. Haz clic en PO-2026-002
3. Haz clic en **"Aprobar y Crear WO"**
4. El sistema creará automáticamente WO-2026-00002 con estado "Open"

---

## Ejemplo 3: Sensor de Proximidad (Flujo Exitoso)

### Paso 3.1: Crear Parte
**URL:** `/admin/parts/create`

| Campo | Valor |
|-------|-------|
| number | SENSOR-001 |
| item_number | ITEM-SENSOR-001 |
| unit_of_measure | PZA |
| description | Sensor de proximidad inductivo |
| active | ✓ |

### Paso 3.2: Crear Precio
**URL:** `/admin/prices/create`

| Campo | Valor |
|-------|-------|
| part_id | SENSOR-001 |
| unit_price | 8.0000 |
| tier_1_999 | 8.0000 |
| tier_1000_10999 | 7.2000 |
| tier_11000_99999 | 6.5000 |
| tier_100000_plus | 5.8000 |
| effective_date | 2025-01-01 |
| active | ✓ |

### Paso 3.3: Crear Estándar
**URL:** `/admin/standards/create`

| Campo | Valor |
|-------|-------|
| part_id | SENSOR-001 |
| units_per_hour | 60 |
| work_table_id | MESA-001 |
| persons_1 | 50 |
| persons_2 | 100 |
| persons_3 | 140 |
| effective_date | 2025-01-01 |
| active | ✓ |
| description | Estándar para sensor de proximidad |

### Paso 3.4: Crear Purchase Order
**URL:** `/admin/purchase-orders/create`

| Campo | Valor |
|-------|-------|
| po_number | PO-2026-003 |
| part_id | SENSOR-001 |
| po_date | 2026-01-01 |
| due_date | 2026-01-25 |
| quantity | 2000 |
| unit_price | 7.2000 |
| comments | Sensores para línea de producción |
| pdf_file | (cualquier PDF) |

**Nota:** El precio 7.2000 corresponde al tier_1000_10999 (cantidad 2000 está en rango 1000-10999)

### Paso 3.5: Aprobar PO y Crear WO
1. Ve a `/admin/purchase-orders`
2. Haz clic en PO-2026-003
3. Haz clic en **"Aprobar y Crear WO"**
4. El sistema creará automáticamente WO-2026-00003 con estado "Open"

---

## Probar Capacity Wizard

Una vez creados los 3 ejemplos anteriores, tendrás:
- 3 Partes activas con estándares activos
- 3 Work Orders con estado "Open" y sin SentList

**URL:** `/admin/capacity-wizard`

### Step 1 - Disponibilidad
| Campo | Valor |
|-------|-------|
| Turno | Turno Matutino |
| Personas | 5 |
| Fecha inicio | 2026-01-06 |
| Fecha fin | 2026-01-10 |

**Resultado esperado:** ~187.5 horas disponibles (7.5 hrs/turno × 5 personas × 5 días)

### Step 2 - Agregar Partes

| Parte | Cantidad | Horas Requeridas |
|-------|----------|------------------|
| CONN-001 | 5000 | 50 hrs (5000 ÷ 100 units/hr) |
| HARNESS-001 | 500 | 20 hrs (500 ÷ 25 units/hr) |
| SENSOR-001 | 2000 | 33.33 hrs (2000 ÷ 60 units/hr) |

**Total requerido:** ~103.33 horas
**Diferencia:** ~84.17 horas restantes (capacidad suficiente)

### Step 3 - Generar Lista
Haz clic en "Generar Lista para Materiales" para crear la SentList.

---

## Tabla de Referencia de Precios por Tier

| Tier | Rango de Cantidad | Descripción |
|------|-------------------|-------------|
| tier_1_999 | 1 - 999 | Precio para cantidades pequeñas |
| tier_1000_10999 | 1,000 - 10,999 | Precio para cantidades medianas |
| tier_11000_99999 | 11,000 - 99,999 | Precio para cantidades grandes |
| tier_100000_plus | 100,000+ | Precio para cantidades muy grandes |
| unit_price | Fallback | Precio base si no hay tier aplicable |

**Importante:** El `unit_price` del PO DEBE coincidir exactamente con el tier correspondiente a la cantidad, de lo contrario el PO quedará en estado "pending_correction".

---

## Verificación de Datos

Para verificar que todo está correcto, puedes ejecutar esta consulta SQL:

```sql
-- Partes con todo completo (Parte + Precio + Estándar + WO Open)
SELECT 
    p.number AS parte,
    pr.unit_price AS precio_base,
    s.units_per_hour AS unidades_hora,
    wo.wo_number AS work_order,
    sw.name AS estado_wo
FROM parts p
JOIN prices pr ON pr.part_id = p.id AND pr.active = 1
JOIN standards s ON s.part_id = p.id AND s.active = 1
JOIN purchase_orders po ON po.part_id = p.id AND po.status = 'approved'
JOIN work_orders wo ON wo.purchase_order_id = po.id
JOIN statuses_wo sw ON sw.id = wo.status_id
WHERE p.active = 1
  AND wo.sent_list_id IS NULL
  AND sw.name = 'Open'
  AND p.deleted_at IS NULL
  AND wo.deleted_at IS NULL;
```

---

## Errores Comunes y Soluciones

### Error: "No hay partes con Work Orders disponibles"
**Causa:** No hay partes que cumplan TODAS las condiciones:
1. Parte activa
2. Con estándar activo (con units_per_hour > 0)
3. Con PO aprobado
4. Con WO en estado "Open"
5. WO sin SentList asignada

**Solución:** Sigue los ejemplos de este documento paso a paso.

### Error: "El precio no coincide"
**Causa:** El unit_price del PO no coincide con el tier correspondiente.

**Solución:** Verifica la cantidad y usa el precio del tier correcto:
- Cantidad 1-999 → usar tier_1_999
- Cantidad 1000-10999 → usar tier_1000_10999
- etc.

### Error: "No hay estándar activo"
**Causa:** La parte no tiene un estándar con `active = true` y `units_per_hour > 0`.

**Solución:** Crea un estándar para la parte con units_per_hour definido.

---

*Documento generado para FlexCon Tracker ERP - Fase 1*
*Última actualización: Enero 2026*
