# Reporte Fase 3: Producción y Lotes

## Descripción General

La Fase 3 del FlexCon Tracker ERP implementa el sistema de **Producción y Lotes**, que permite gestionar la preparación de materiales (Kits) y el seguimiento de lotes de producción. Esta fase conecta la planificación (Fase 2) con el control de calidad y envío (Fase 4).

---

## Módulos Implementados

### 1. Kits (Preparación de Materiales)
**URL:** `/admin/kits`

Los kits representan conjuntos de materiales preparados para la producción de una Work Order específica.

#### Campos del Kit:
| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| `work_order_id` | Work Order asociada | WO-2025-00001 |
| `kit_number` | Número único del kit | KIT-WO-2025-00001-001 |
| `status` | Estado del kit | preparing, ready, released, in_assembly |
| `validated` | Si fue validado | true/false |
| `validation_notes` | Notas de validación | "Materiales completos" |
| `prepared_by` | Usuario que preparó | Juan Pérez |
| `released_by` | Usuario que liberó | María García |

#### Estados del Kit:
| Estado | Color | Descripción |
|--------|-------|-------------|
| `preparing` | 🟡 Amarillo | Kit en preparación |
| `ready` | 🔵 Azul | Kit listo para liberar |
| `released` | 🟢 Verde | Kit liberado para producción |
| `in_assembly` | 🟣 Morado | Kit en proceso de ensamble |

#### Flujo de Estados:
```
preparing → ready → released → in_assembly
     ↓
  (validar)
```

#### Ejemplo de Ingreso:
```
Work Order: WO-2025-00001 (PART-001 - Conector USB)
Notas de Validación: "Todos los componentes verificados contra BOM"

[Crear Kit]

→ Se genera automáticamente: KIT-WO-2025-00001-001
→ Estado inicial: preparing
```

---

### 2. Kit Incidents (Incidencias FCA-44)
**URL:** Dentro de `/admin/kits/{kit}`

Registro de incidencias durante la preparación o uso de kits.

#### Campos de Incidencia:
| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| `kit_id` | Kit asociado | KIT-WO-2025-00001-001 |
| `incident_type` | Tipo de incidencia | material_shortage, quality_issue, etc. |
| `description` | Descripción detallada | "Falta componente C-123" |
| `fca_44_reference` | Referencia FCA-44 | FCA-44-2025-001 |
| `resolved` | Si está resuelto | true/false |
| `resolved_by` | Usuario que resolvió | Carlos López |

#### Tipos de Incidencia:
| Tipo | Descripción |
|------|-------------|
| `material_shortage` | Falta de material |
| `quality_issue` | Problema de calidad |
| `documentation_error` | Error de documentación |
| `equipment_failure` | Falla de equipo |
| `other` | Otro |

#### Ejemplo de Registro:
```
Kit: KIT-WO-2025-00001-001
Tipo: Falta de Material
Descripción: "Componente resistor R-100 no disponible en almacén"
Referencia FCA-44: FCA-44-2025-015

[Registrar Incidencia]
```

---

### 3. Lots (Lotes de Producción)
**URL:** `/admin/lots`

Los lotes dividen la producción de una Work Order en cantidades manejables para seguimiento y control de calidad.

#### Campos del Lote:
| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| `work_order_id` | Work Order asociada | WO-2025-00001 |
| `lot_number` | Número secuencial | 001, 002, 003... |
| `description` | Descripción del lote | "Primer lote de producción" |
| `quantity` | Cantidad de piezas | 1,000 |
| `status` | Estado del lote | pending, in_progress, completed, cancelled |
| `comments` | Comentarios adicionales | "Producción sin incidentes" |

#### Estados del Lote:
| Estado | Color | Descripción |
|--------|-------|-------------|
| `pending` | 🟡 Amarillo | Lote creado, pendiente de iniciar |
| `in_progress` | 🔵 Azul | Lote en producción |
| `completed` | 🟢 Verde | Lote completado |
| `cancelled` | 🔴 Rojo | Lote cancelado |

#### Flujo de Estados:
```
pending → in_progress → completed
    ↓          ↓
 cancelled  cancelled
```

#### Generación Automática de Número:
El sistema genera automáticamente números de lote secuenciales por Work Order:
- Primer lote: `001`
- Segundo lote: `002`
- Tercer lote: `003`
- etc.

#### Ejemplo de Ingreso:
```
Work Order: WO-2025-00001 (PART-001 - Conector USB)
  - Cantidad Original: 10,000 unidades
  - Piezas Enviadas: 0
  - Pendiente: 10,000

Cantidad del Lote: 2,500
Descripción: "Primer lote - Línea de producción A"
Comentarios: "Prioridad alta"

[Crear Lote]

→ Se genera automáticamente: Lote #001
→ Estado inicial: pending
```

---

## Sincronización Automática

### Actualización de Work Order al Completar Lote

Cuando un lote cambia a estado `completed`, el sistema automáticamente:

1. **Suma la cantidad del lote** a `sent_pieces` de la Work Order
2. **Recalcula** `pending_quantity` = `original_quantity` - `sent_pieces`

#### Ejemplo:
```
Work Order: WO-2025-00001
Cantidad Original: 10,000

Lote 001 (2,500 piezas) → completed
  → sent_pieces = 2,500
  → pending_quantity = 7,500

Lote 002 (2,500 piezas) → completed
  → sent_pieces = 5,000
  → pending_quantity = 5,000

Lote 003 (3,000 piezas) → completed
  → sent_pieces = 8,000
  → pending_quantity = 2,000
```

### Eventos que Actualizan la WO:
| Evento | Acción |
|--------|--------|
| Lote creado con status `completed` | Suma cantidad a sent_pieces |
| Lote cambia a `completed` | Suma cantidad a sent_pieces |
| Lote `completed` es eliminado | Resta cantidad de sent_pieces |
| Lote `completed` es restaurado | Suma cantidad a sent_pieces |
| Lote cambia de `completed` a otro estado | Resta cantidad de sent_pieces |

---

## Datos de Ejemplo para Pruebas

### Prerequisitos
Antes de probar esta fase, asegúrate de tener:
1. **Parts** creados (`/admin/parts`)
2. **Purchase Orders** creados (`/admin/purchase-orders`)
3. **Work Orders** creados (`/admin/work-orders`)

### Ejemplo 1: Crear Kit para Work Order

**Paso 1:** Ir a `/admin/kits/create`

```
Work Order: WO-2025-00001 (PART-001 - Conector USB)
Notas de Validación: "Verificado contra lista de materiales BOM-001"

[Crear Kit]
```

**Paso 2:** Validar el Kit
```
En la vista del kit (/admin/kits/{id}):
[Validar Kit] → El kit queda marcado como validado
```

**Paso 3:** Marcar como Listo
```
[Marcar como Listo] → Estado cambia a "ready"
```

**Paso 4:** Liberar Kit
```
[Liberar Kit] → Estado cambia a "released"
```

**Paso 5:** Iniciar Ensamble
```
[Iniciar Ensamble] → Estado cambia a "in_assembly"
```

---

### Ejemplo 2: Registrar Incidencia en Kit

**En la vista del kit:**
```
[Registrar Incidencia]

Tipo: Falta de Material
Descripción: "El componente capacitor C-220 no está disponible. 
             Se requieren 500 unidades y solo hay 200 en stock."
Referencia FCA-44: FCA-44-2025-023

[Registrar Incidencia]
```

**Para resolver:**
```
[Resolver] → La incidencia queda marcada como resuelta
```

---

### Ejemplo 3: Crear Lotes de Producción

**Escenario:** Work Order con 10,000 unidades, dividir en 4 lotes

**Paso 1:** Ir a `/admin/lots/create`

```
Work Order: WO-2025-00001
  Cantidad Original: 10,000
  Enviadas: 0
  Pendiente: 10,000

Cantidad: 2,500
Descripción: "Lote 1 - Línea A, turno matutino"

[Crear Lote]
→ Se crea Lote #001
```

**Paso 2:** Crear más lotes
```
Lote #002: 2,500 unidades - "Línea A, turno vespertino"
Lote #003: 3,000 unidades - "Línea B, turno matutino"
Lote #004: 2,000 unidades - "Línea B, turno vespertino"
```

**Paso 3:** Gestionar estados

```
Lote #001:
  [Iniciar Producción] → in_progress
  [Completar Lote] → completed
  → WO sent_pieces = 2,500

Lote #002:
  [Iniciar Producción] → in_progress
  [Completar Lote] → completed
  → WO sent_pieces = 5,000

Lote #003:
  [Iniciar Producción] → in_progress
  (en producción...)

Lote #004:
  (pendiente)
```

---

### Ejemplo 4: Flujo Completo de Producción

```
┌─────────────────────────────────────────────────────────────┐
│                    FLUJO DE PRODUCCIÓN                       │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  1. PURCHASE ORDER                                           │
│     PO-2025-00001                                           │
│     Parte: PART-001 (Conector USB)                          │
│     Cantidad: 10,000 unidades                               │
│                    ↓                                         │
│  2. WORK ORDER                                               │
│     WO-2025-00001                                           │
│     Status: Open                                             │
│                    ↓                                         │
│  3. KIT                                                      │
│     KIT-WO-2025-00001-001                                   │
│     preparing → ready → released → in_assembly              │
│                    ↓                                         │
│  4. LOTES                                                    │
│     ┌──────────┬──────────┬──────────┬──────────┐          │
│     │ Lote 001 │ Lote 002 │ Lote 003 │ Lote 004 │          │
│     │ 2,500    │ 2,500    │ 3,000    │ 2,000    │          │
│     │ ✅ done  │ ✅ done  │ 🔄 prog  │ ⏳ pend  │          │
│     └──────────┴──────────┴──────────┴──────────┘          │
│                    ↓                                         │
│  5. WORK ORDER ACTUALIZADA                                   │
│     Enviadas: 5,000 (Lotes 001 + 002)                       │
│     Pendiente: 5,000                                         │
│     Progreso: 50%                                            │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## URLs de Acceso

| Módulo | URL | Descripción |
|--------|-----|-------------|
| Kits | `/admin/kits` | Lista de kits |
| Kits Create | `/admin/kits/create` | Crear kit |
| Kits Show | `/admin/kits/{id}` | Ver detalle y acciones |
| Lots | `/admin/lots` | Lista de todos los lotes |
| Lots por WO | `/admin/lots?workOrderId={id}` | Lotes de una WO específica |
| Lots Create | `/admin/lots/create` | Crear lote |
| Lots Create para WO | `/admin/lots/create?workOrderId={id}` | Crear lote para WO específica |
| Lots Show | `/admin/lots/{id}` | Ver detalle del lote |

---

## Archivos Principales

```
app/
├── Models/
│   ├── Kit.php              # Modelo de kits
│   ├── KitIncident.php      # Modelo de incidencias
│   └── Lot.php              # Modelo de lotes
├── Livewire/Admin/
│   ├── Kits/
│   │   ├── KitList.php      # Lista de kits
│   │   ├── KitCreate.php    # Crear kit
│   │   └── KitShow.php      # Detalle y acciones
│   └── Lots/
│       ├── LotList.php      # Lista de lotes
│       ├── LotCreate.php    # Crear lote
│       └── LotShow.php      # Detalle y acciones

resources/views/livewire/admin/
├── kits/
│   ├── kit-list.blade.php
│   ├── kit-create.blade.php
│   └── kit-show.blade.php
└── lots/
    ├── lot-list.blade.php
    ├── lot-create.blade.php
    └── lot-show.blade.php

database/migrations/
├── create_kits_table.php
├── create_kit_incidents_table.php
└── create_lots_table.php
```

---

## Notas Importantes

1. **Prerequisitos:** Antes de crear kits o lotes, necesitas:
   - Work Orders creadas (`/admin/work-orders`)
   - Las WO deben tener estado "Open" o "In Progress"

2. **Números Automáticos:**
   - Kits: `KIT-{WO_NUMBER}-{SECUENCIAL}`
   - Lotes: `001`, `002`, `003`... por Work Order

3. **Validación de Cantidad:**
   - Al crear un lote, la cantidad no puede exceder la cantidad pendiente de la WO
   - El sistema muestra el máximo disponible

4. **Sincronización Automática:**
   - Al completar un lote, `sent_pieces` de la WO se actualiza automáticamente
   - Al eliminar/cancelar un lote completado, se recalcula `sent_pieces`

5. **Incidencias FCA-44:**
   - Se registran desde la vista de detalle del kit
   - Pueden marcarse como resueltas
   - Mantienen historial completo

6. **Relación con Fase 4:**
   - Los lotes completados estarán disponibles para inspección (Fase 4)
   - Las inspecciones se asocian a lotes específicos

---

## Próximos Pasos (Fase 4)

La Fase 4 implementará:
- **Inspections:** Control de calidad de lotes
- **Shipping Lists:** Listas de envío finales
- **Quality Forms:** Formularios FCA-07, FCA-10, FCA-16
