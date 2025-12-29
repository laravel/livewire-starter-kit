# Reporte Fase 2: Planificación de Producción

## Descripción General

La Fase 2 del FlexCon Tracker ERP implementa el sistema de **Planificación de Producción**, que permite calcular la capacidad disponible de producción y generar listas de envío preliminares basadas en turnos, personal y estándares de producción.

---

## Módulos Implementados

### 1. Standards (Estándares de Producción)
**URL:** `/admin/standards`

Los estándares definen cuántas unidades por hora se pueden producir para cada parte, según el modo de ensamble.

#### Campos del Estándar:
| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| `part_id` | Parte asociada | Part #12345 |
| `work_table_id` | Mesa de trabajo (manual) | Mesa 1 |
| `semi_auto_work_table_id` | Mesa semi-automática | Semi-Auto 2 |
| `machine_id` | Máquina | Máquina CNC-01 |
| `persons_1` | Unidades/hora con 1 persona | 50 |
| `persons_2` | Unidades/hora con 2 personas | 90 |
| `persons_3` | Unidades/hora con 3 personas | 120 |
| `units_per_hour` | Unidades/hora base | 50 |
| `effective_date` | Fecha efectiva | 2025-01-01 |
| `active` | Estado activo | true |

#### Ejemplo de Ingreso:
```
Parte: PART-001 (Conector USB)
Mesa de Trabajo: Mesa 5
Unidades/hora (1 persona): 100
Unidades/hora (2 personas): 180
Unidades/hora (3 personas): 250
Fecha Efectiva: 2025-01-01
Activo: Sí
```

---

### 2. OverTime (Tiempo Extra)
**URL:** `/admin/over-times`

Registra las horas extra programadas que se suman a la capacidad disponible.

#### Campos del OverTime:
| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| `name` | Nombre descriptivo | "Overtime Sábado" |
| `start_time` | Hora inicio | 08:00 |
| `end_time` | Hora fin | 14:00 |
| `break_minutes` | Minutos de descanso | 30 |
| `employees_qty` | Cantidad de empleados | 5 |
| `date` | Fecha del overtime | 2025-01-04 |
| `shift_id` | Turno asociado | Turno Matutino |

#### Ejemplo de Ingreso:
```
Nombre: Overtime Fin de Semana
Hora Inicio: 08:00
Hora Fin: 14:00
Descanso: 30 minutos
Empleados: 5
Fecha: 2025-01-04 (Sábado)
Turno: Turno Matutino
```

#### Cálculo de Horas:
```
Horas Netas = (14:00 - 08:00) - 30min = 5.5 horas
Horas Totales = 5.5 horas × 5 empleados = 27.5 horas-hombre
```

---

### 3. Capacity Calculator (Calculador de Capacidad)
**URL:** `/admin/capacity-calculator`

Herramienta principal para planificar la producción calculando horas disponibles vs requeridas.

#### Flujo de Uso:

**Paso 1: Configurar Parámetros**
```
Purchase Order: PO-2025-00001
Turnos Seleccionados: [✓] Turno Matutino, [✓] Turno Vespertino
Número de Personas: 3
Fecha Inicio: 2025-01-06
Fecha Fin: 2025-01-10
```

**Paso 2: Calcular Capacidad**
El sistema calcula automáticamente:
```
Días disponibles: 5 (Lun-Vie, sin feriados)
Horas por turno: 8 horas (Matutino) + 8 horas (Vespertino) = 16 horas
Horas regulares: 5 días × 16 horas × 3 personas = 240 horas
Overtime en rango: +27.5 horas
─────────────────────────────────────────
Total Disponible: 267.5 horas
```

**Paso 3: Agregar Work Orders**
```
Parte: PART-001 (Conector USB)
Cantidad: 5,000 unidades
Modo Ensamble: 2 personas

Cálculo:
- Standard para PART-001: 180 unidades/hora (2 personas)
- Horas Requeridas: 5,000 ÷ 180 = 27.78 horas

[Agregar WO]
```

**Paso 4: Resumen de Capacidad**
```
┌─────────────────────────────────────────┐
│ Horas Disponibles:     267.50 hrs       │
│ Horas Usadas:           27.78 hrs       │
│ Horas Restantes:       239.72 hrs       │
│ Utilización:            10.4%           │
└─────────────────────────────────────────┘
```

**Paso 5: Generar Lista de Envío**
Al hacer clic en "Generate SentList", se crea:
- Una SentList con los parámetros configurados
- Work Orders asociados a la lista

---

### 4. Sent Lists (Listas de Envío)
**URL:** `/admin/sent-lists`

Gestiona las listas de envío preliminares generadas desde el calculador.

#### Estados de la Lista:
| Estado | Color | Descripción |
|--------|-------|-------------|
| `pending` | 🟡 Amarillo | Lista creada, pendiente de confirmación |
| `confirmed` | 🟢 Verde | Lista confirmada para producción |
| `canceled` | 🔴 Rojo | Lista cancelada |

#### Vista Index (`/admin/sent-lists`):
Muestra todas las listas con:
- ID y número de PO
- Parte asociada
- Período (fecha inicio - fecha fin)
- Número de personas
- Capacidad (disponible/usado/restante)
- Estado
- Acciones (ver, editar, eliminar)

#### Vista Show (`/admin/sent-lists/{id}`):
Detalle completo con:
- Información de la Orden de Compra
- Información de Planificación (fechas, turnos, personas)
- Resumen de Capacidad con barra de progreso
- Lista de Work Orders asociados

---

## Fórmulas de Cálculo

### Horas Disponibles
```
Horas_Disponibles = (Días_Laborables × Horas_Turno × Num_Personas) + Horas_Overtime

Donde:
- Días_Laborables = Total_Días - Feriados - Fines_de_Semana
- Horas_Turno = Σ(Hora_Fin - Hora_Inicio - Break_Time) para cada turno
```

### Horas Requeridas
```
Horas_Requeridas = Cantidad ÷ Unidades_Por_Hora

Donde:
- Unidades_Por_Hora se obtiene del Standard según el modo de ensamble:
  - 1_person → persons_1
  - 2_persons → persons_2
  - 3_persons → persons_3
```

### Validación de Capacidad
```
Si Horas_Restantes < Horas_Requeridas:
    → Lanzar CapacityExceededException
    → No permitir agregar más Work Orders
```

---

## Ejemplo Completo de Uso

### Escenario:
Una empresa necesita producir 10,000 conectores USB (PART-001) para la semana del 6 al 10 de enero de 2025.

### Datos de Entrada:

**Turnos Disponibles:**
- Turno Matutino: 06:00 - 14:00 (30 min break) = 7.5 hrs netas
- Turno Vespertino: 14:00 - 22:00 (30 min break) = 7.5 hrs netas

**Standard para PART-001:**
- 1 persona: 100 unidades/hora
- 2 personas: 180 unidades/hora
- 3 personas: 250 unidades/hora

**Overtime Programado:**
- Sábado 4 de enero: 08:00-14:00, 5 empleados, 30 min break

### Cálculo:

```
1. Días Laborables: 5 (Lun 6 - Vie 10, sin feriados)

2. Horas por Turno: 7.5 + 7.5 = 15 horas/día

3. Con 2 personas:
   Horas Regulares = 5 días × 15 hrs × 2 personas = 150 horas

4. Overtime:
   Horas Netas = 6 hrs - 0.5 hrs = 5.5 hrs
   Horas Totales = 5.5 × 5 empleados = 27.5 horas

5. Total Disponible = 150 + 27.5 = 177.5 horas

6. Horas Requeridas (modo 2_persons):
   10,000 ÷ 180 = 55.56 horas

7. Resultado:
   ✅ Capacidad Suficiente
   Utilización: 55.56 / 177.5 = 31.3%
   Horas Restantes: 121.94 horas
```

---

## URLs de Acceso

| Módulo | URL | Descripción |
|--------|-----|-------------|
| Standards | `/admin/standards` | Lista de estándares |
| Standards Create | `/admin/standards/create` | Crear estándar |
| Standards Show | `/admin/standards/{id}` | Ver detalle |
| Standards Edit | `/admin/standards/{id}/edit` | Editar estándar |
| OverTimes | `/admin/over-times` | Lista de overtime |
| OverTimes Create | `/admin/over-times/create` | Crear overtime |
| OverTimes Show | `/admin/over-times/{id}` | Ver detalle |
| OverTimes Edit | `/admin/over-times/{id}/edit` | Editar overtime |
| Capacity Calculator | `/admin/capacity-calculator` | Calculador |
| Sent Lists | `/admin/sent-lists` | Lista de envíos |
| Sent Lists Show | `/admin/sent-lists/{id}` | Ver detalle |
| Sent Lists Edit | `/admin/sent-lists/{id}/edit` | Editar estado |

---

## Archivos Principales

```
app/
├── Models/
│   ├── Standard.php          # Modelo de estándares
│   ├── OverTime.php          # Modelo de tiempo extra
│   └── SentList.php          # Modelo de listas de envío
├── Services/
│   └── CapacityCalculatorService.php  # Lógica de cálculo
├── Livewire/
│   ├── CapacityCalculator.php         # Componente calculador
│   └── Admin/
│       ├── Standards/                  # CRUD Standards
│       └── OverTimes/                  # CRUD OverTimes
├── Http/Controllers/
│   └── SentListController.php         # Controller SentList
└── Exceptions/
    └── CapacityExceededException.php  # Excepción capacidad

resources/views/
├── livewire/
│   └── capacity-calculator.blade.php  # Vista calculador
└── sent-lists/
    ├── index.blade.php                # Lista
    ├── show.blade.php                 # Detalle
    └── edit.blade.php                 # Editar

database/migrations/
├── create_standards_table.php
├── create_over_times_table.php
├── create_sent_lists_table.php
└── add_sent_list_fields_to_work_orders_table.php
```

---

## Notas Importantes

1. **Prerequisitos:** Antes de usar el calculador, asegúrate de tener:
   - Turnos configurados (`/admin/shifts`)
   - Breaks configurados (`/admin/break-times`)
   - Feriados registrados (`/admin/holidays`)
   - Standards para las partes (`/admin/standards`)

2. **Validación de Capacidad:** El sistema no permite agregar Work Orders si exceden la capacidad disponible.

3. **Estados de SentList:** Solo las listas en estado `pending` pueden ser editadas o eliminadas.

4. **Relación con Work Orders:** Al generar una SentList, se crean automáticamente los Work Orders asociados con el campo `sent_list_id`.
