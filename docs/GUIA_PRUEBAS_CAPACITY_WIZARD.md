# Guía de Pruebas - Capacity Wizard

Este documento contiene los datos exactos para probar el flujo completo del sistema, basado en las migraciones reales de la base de datos.

## Orden de Creación (por dependencias)

```
1. Departamento
2. Área (requiere: Departamento)
3. Production Status
4. Turno (Shift)
5. Break Time (requiere: Turno)
6. Mesa de Trabajo (requiere: Área)
7. Semi-Automático (requiere: Área)
8. Máquina (requiere: Área)
9. Parte
10. Estándar (requiere: Parte + Mesa/Semi/Máquina)
11. Precio (requiere: Parte) ← NECESARIO para validar PO
12. Purchase Order (requiere: Parte + Precio)
13. Aprobar PO → Crea Work Order automáticamente
14. Capacity Wizard (requiere: WO con estado Open)
```

---

## PASO 1: Departamento

**URL:** `/admin/departments/create`

| Campo | Valor | Requerido |
|-------|-------|-----------|
| name | Producción | ✓ |
| description | Departamento de producción principal | |
| comments | | |

---

## PASO 2: Área

**URL:** `/admin/areas/create`

| Campo | Valor | Requerido |
|-------|-------|-----------|
| name | Ensamble A | ✓ |
| description | Área de ensamble principal | |
| comments | | |
| department_id | (selecciona "Producción") | ✓ |
| user_id | (opcional) | |

---

## PASO 3: Production Status

**URL:** `/admin/production-statuses/create`

| Campo | Valor | Requerido |
|-------|-------|-----------|
| name | Disponible | ✓ |
| color | #10b981 | ✓ |
| order | 1 | ✓ |
| active | ✓ (checked) | |
| description | Estado disponible para producción | |

---

## PASO 4: Turno (Shift)

**URL:** `/admin/shifts/create`

| Campo | Valor | Requerido |
|-------|-------|-----------|
| name | Turno Matutino | ✓ |
| start_time | 06:00 | ✓ |
| end_time | 14:00 | ✓ |
| active | ✓ (checked) | |
| comments | | |

---

## PASO 5: Break Time (Descanso)

**URL:** `/admin/break-times/create`

| Campo | Valor | Requerido |
|-------|-------|-----------|
| name | Descanso Mañana | ✓ |
| start_break_time | 10:00 | ✓ |
| end_break_time | 10:30 | ✓ |
| shift_id | (selecciona "Turno Matutino") | ✓ |
| active | ✓ (checked) | |
| comments | | |

---

## PASO 6: Mesa de Trabajo (Table)

**URL:** `/admin/tables/create`

| Campo | Valor | Requerido |
|-------|-------|-----------|
| number | MES-001 | ✓ |
| employees | 3 | ✓ |
| active | ✓ (checked) | ✓ |
| comments | | |
| area_id | (selecciona "Ensamble A") | ✓ |

---

## PASO 7: Semi-Automático

**URL:** `/admin/semi-automatics/create`

| Campo | Valor | Requerido |
|-------|-------|-----------|
| number | SA-001 | ✓ |
| employees | 2 | ✓ |
| active | ✓ (checked) | ✓ |
| comments | | |
| area_id | (selecciona "Ensamble A") | ✓ |

---

## PASO 8: Máquina

**URL:** `/admin/machines/create`

| Campo | Valor | Requerido |
|-------|-------|-----------|
| name | Prensa Hidráulica 1 | ✓ |
| brand | ACME | |
| model | PH-500 | |
| sn | SN123456 | |
| asset_number | ACT-001 | |
| employees | 1 | ✓ |
| setup_time | 15 | ✓ |
| maintenance_time | 30 | ✓ |
| active | ✓ (checked) | ✓ |
| comments | | |
| area_id | (selecciona "Ensamble A") | ✓ |

---

## PASO 9: Parte

**URL:** `/admin/parts/create`

| Campo | Valor | Requerido |
|-------|-------|-----------|
| number | PN-12345 | ✓ |
| item_number | ITEM-001 | ✓ |
| unit_of_measure | PZA | |
| description | Componente de ensamble principal | |
| notes | | |
| active | ✓ (checked) | |

---

## PASO 10: Estándar

**URL:** `/admin/standards/create`

| Campo | Valor | Requerido |
|-------|-------|-----------|
| part_id | (selecciona "PN-12345") | ✓ |
| units_per_hour | 50 | ✓ |
| work_table_id | (selecciona "MES-001") | |
| semi_auto_work_table_id | (opcional - "SA-001") | |
| machine_id | (opcional - "Prensa Hidráulica 1") | |
| persons_1 | 100 | |
| persons_2 | 150 | |
| persons_3 | 200 | |
| effective_date | 2024-12-30 | |
| active | ✓ (checked) | |
| description | Estándar para parte PN-12345 | |

---

## PASO 11: Purchase Order (PO)

**URL:** `/admin/purchase-orders/create`

**IMPORTANTE:** El Capacity Wizard requiere que exista un Purchase Order APROBADO para cada parte antes de poder usarla.

### Paso 11.1: Crear un Price (si no existe)

**URL:** `/admin/prices/create`

| Campo | Valor | Requerido |
|-------|-------|-----------|
| part_id | (selecciona "PN-12345") | ✓ |
| unit_price | 10.5000 | ✓ |
| tier_1_999 | 10.5000 | |
| tier_1000_10999 | 10.0000 | |
| tier_11000_99999 | 9.5000 | |
| tier_100000_plus | 9.0000 | |
| effective_date | 2025-01-01 | ✓ |
| active | ✓ (checked) | |

### Paso 11.2: Crear el Purchase Order

**URL:** `/admin/purchase-orders/create`

| Campo | Valor | Requerido |
|-------|-------|-----------|
| po_number | PO-2025-001 | ✓ |
| part_id | (selecciona "PN-12345") | ✓ |
| po_date | 2025-12-31 | ✓ |
| due_date | 2026-01-31 | ✓ |
| quantity | 2000 | ✓ |
| unit_price | 10.0000 | ✓ (debe coincidir con el tier correspondiente) |
| comments | Orden de prueba para capacity wizard | |
| pdf_file | (sube cualquier PDF) | ✓ |

**Nota sobre precios:** 
- Para cantidad 2000, el sistema usará `tier_1000_10999` = 10.0000
- El `unit_price` del PO DEBE coincidir con el precio del tier correspondiente
- Si no coincide, el PO quedará en estado "pending_correction"

### Paso 11.3: Aprobar el PO

1. Ve a `/admin/purchase-orders`
2. Haz clic en el PO creado (PO-2025-001)
3. Haz clic en **"Aprobar y Crear WO"**
4. Si el precio es válido:
   - El PO cambia a estado "approved"
   - Se crea automáticamente un Work Order con estado "Open"
5. Si el precio NO es válido:
   - El PO cambia a estado "pending_correction"
   - Debes editar el PO y corregir el precio

---

## PROBAR CAPACITY WIZARD

**URL:** `/admin/capacity-wizard`

### FLUJO CORRECTO (según Fase 1)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           FLUJO DEL SISTEMA                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  1. CREAR PO          2. APROBAR PO         3. CAPACITY WIZARD              │
│  ┌─────────────┐      ┌─────────────┐       ┌─────────────────────┐         │
│  │ Purchase    │ ──▶  │ Validar     │ ──▶   │ Seleccionar WOs     │         │
│  │ Order       │      │ Precio      │       │ existentes          │         │
│  └─────────────┘      └──────┬──────┘       └──────────┬──────────┘         │
│                              │                         │                     │
│                              ▼                         ▼                     │
│                       ┌─────────────┐          ┌─────────────────────┐      │
│                       │ Crear WO    │          │ Generar SentList    │      │
│                       │ automático  │          │ para Materiales     │      │
│                       └─────────────┘          └─────────────────────┘      │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

### PASOS PARA PROBAR:

#### PASO A: Crear y Aprobar un PO (si no tienes WOs)

1. Ve a `/admin/purchase-orders/create`
2. Crea un PO con los datos del PASO 11 de esta guía
3. Ve a `/admin/purchase-orders` y haz clic en el PO creado
4. Haz clic en **"Aprobar y Crear WO"**
5. El sistema:
   - Valida el precio contra la tabla de precios
   - Si es válido, aprueba el PO
   - Crea automáticamente un Work Order con estado "Open"

#### PASO B: Verificar que tienes WOs disponibles

**Verificar en la base de datos:**
```sql
-- Verificar Work Orders disponibles (Open, sin SentList)
SELECT wo.id, wo.wo_number, wo.sent_list_id, s.name as status, 
       po.po_number, p.number as part_number
FROM work_orders wo
JOIN statuses_wo s ON wo.status_id = s.id
JOIN purchase_orders po ON wo.purchase_order_id = po.id
JOIN parts p ON po.part_id = p.id
WHERE wo.sent_list_id IS NULL 
  AND s.name = 'Open'
  AND wo.deleted_at IS NULL;
```

**O verificar en la UI:**
- Ve a `/admin/work-orders`
- Busca WOs con estado "Open" y sin SentList asignada

#### PASO C: Usar el Capacity Wizard

**URL:** `/admin/capacity-wizard`

### Step 1 - Disponibilidad de Horas
1. Selecciona el turno "Turno Matutino"
2. Ingresa número de personas (ej: 5)
3. Selecciona rango de fechas (máximo 5 días)
4. El sistema mostrará:
   - Horas por turno (descontando descansos)
   - Total de horas disponibles = (horas netas × personas × días)
5. Haz clic en "Siguiente"

### Step 2 - Agregar Números de Parte
1. Selecciona un **número de parte** del dropdown
   - Solo aparecen partes que tienen WO disponible (Open, sin SentList)
2. Ingresa la **cantidad** a producir
3. Haz clic en **"Agregar"**
4. El sistema:
   - Busca el WO existente para esa parte
   - Calcula horas requeridas = cantidad / units_per_hour (del estándar)
   - Muestra la diferencia entre horas disponibles y requeridas
5. Repite para agregar más partes si es necesario
6. Si hay déficit de horas, se muestra sugerencia de tiempo extra
7. Haz clic en "Siguiente"

### Step 3 - Cierre y Generación
1. Revisa el resumen de la planificación
2. Haz clic en **"Generar Lista para Materiales"**
3. El sistema:
   - Crea una SentList con la configuración
   - Asigna los WOs seleccionados a esa SentList
   - Muestra mensaje de éxito
4. Puedes ver la lista generada o crear una nueva calculación

---

## Diagrama de Dependencias

```
┌─────────────┐
│ Departamento│
└──────┬──────┘
       │
       ▼
┌─────────────┐     ┌──────────────────┐
│    Área     │     │ Production Status│
└──────┬──────┘     └────────┬─────────┘
       │                     │
       ├─────────────────────┤
       │                     │
       ▼                     ▼
┌─────────────┐     ┌──────────────────┐     ┌─────────────┐
│    Mesa     │     │  Semi-Automático │     │   Máquina   │
└──────┬──────┘     └────────┬─────────┘     └──────┬──────┘
       │                     │                      │
       └─────────────────────┼──────────────────────┘
                             │
                             ▼
┌─────────────┐        ┌───────────┐
│    Parte    │───────▶│  Estándar │
└──────┬──────┘        └───────────┘
       │
       ├───────────────────────────────────────────┐
       │                                           │
       ▼                                           ▼
┌─────────────┐                              ┌───────────┐
│   Precio    │                              │    PO     │
└─────────────┘                              └─────┬─────┘
                                                   │
                                                   ▼ (al aprobar)
                                             ┌───────────┐
                                             │    WO     │
                                             └─────┬─────┘
                                                   │
                                                   ▼
┌─────────────┐                        ┌───────────────────┐
│    Turno    │───────────────────────▶│ Capacity Wizard   │
└──────┬──────┘                        └───────────────────┘
       │
       ▼
┌─────────────┐
│ Break Time  │
└─────────────┘
```

---

## Notas Importantes

1. **Orden estricto**: Debes crear los registros en el orden indicado debido a las dependencias de foreign keys.

2. **Campos requeridos**: Los campos marcados con ✓ son obligatorios y causarán error si no se llenan.

3. **Estándar**: Debe tener al menos una de las tres opciones (Mesa, Semi-Automático o Máquina) para que el cálculo funcione correctamente.

4. **units_per_hour**: Este campo es CRÍTICO para el cálculo de capacidad. Define cuántas unidades se producen por hora.

5. **Break Times**: Se restan automáticamente de las horas disponibles del turno.

6. **Nota técnica**: La tabla de semi-automáticos en la BD se llama `semi__automatics` (con doble guión bajo).
