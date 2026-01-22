# Guía Rápida: Corregir Datos de Precios

## Resumen Ejecutivo

El sistema de detección de precios está funcionando. Los errores que ves son porque **los datos están inconsistentes** (el tipo de precio no coincide con el tipo de estación del Standard).

## Diagnóstico Rápido

```bash
php debug_price.php
```

Este comando te muestra todas las partes y sus problemas.

## Partes con Problemas

### Tipo 1: Precio no coincide con Standard (6 partes)

| Parte | Standard | Precio Actual | Acción |
|-------|----------|---------------|--------|
| PART-002 | manual (table) | machine | Cambiar precio a `table` |
| PART-003 | manual (table) | semi_automatic | Cambiar precio a `table` |
| PART-005 | manual (table) | machine | Cambiar precio a `table` |
| PART-006 | manual (table) | semi_automatic | Cambiar precio a `table` |
| PART-008 | manual (table) | machine | Cambiar precio a `table` |

**Cómo corregir**:
1. Ir a http://flexcon-tracker.test:8088/admin/parts/[ID]
2. En la pestaña de precios, editar el precio activo
3. Cambiar `workstation_type` de `machine` o `semi_automatic` a `table`
4. Guardar

### Tipo 2: Standard sin estación (1 parte)

| Parte | Problema | Acción |
|-------|----------|--------|
| PART-009 | Standard sin work_table_id, semi_auto_work_table_id, ni machine_id | Asignar una estación al Standard |

**Cómo corregir**:
1. Ir a la página de Standards
2. Buscar el Standard de PART-009
3. Editar y asignar una estación (work_table, semi_auto, o machine)
4. Guardar

### Tipo 3: Sin Standard activo (7 partes)

| Parte | Acción |
|-------|--------|
| PART-TEST-1, PART-TEST-2, PART-TEST-3, PART-TEST-4, PART-TEST-5, H-M-2 | Crear Standard activo |

**Cómo corregir**:
1. Ir a la página de Standards
2. Crear nuevo Standard para la parte
3. Asignar estación de trabajo
4. Marcar como activo
5. Guardar

## Verificación

Después de corregir, ejecutar:

```bash
php debug_price.php
```

Debes ver: ✅ DETECCIÓN: OK para todas las partes.

## Ejemplo de Corrección

### Antes (PART-002):
```
❌ Standard: manual (→table)
❌ Precio: machine
❌ DETECCIÓN FALLÓ: No se encontró un precio activo para el tipo de estación Mesa de Trabajo
```

### Después (PART-002):
```
✅ Standard: manual (→table)
✅ Precio: table
✅ DETECCIÓN: OK
```

## Mapeo de Tipos

| Assembly Mode (Standard) | Workstation Type (Precio) | Nombre en UI |
|--------------------------|---------------------------|--------------|
| manual | table | Mesa de Trabajo |
| semi_automatic | semi_automatic | Semi-Automática |
| machine | machine | Máquina |

## Preguntas Frecuentes

### ¿Por qué no puedo crear un PO para PART-002?

Porque el Standard dice que se ensambla en "Mesa de Trabajo" (manual) pero el precio está configurado para "Máquina" (machine). El sistema no puede determinar qué precio usar.

### ¿Qué hago si el precio está correcto pero el Standard está mal?

Cambia el Standard en lugar del precio:
1. Si el precio es tipo `machine`, el Standard debe tener `machine_id` asignado
2. Si el precio es tipo `table`, el Standard debe tener `work_table_id` asignado
3. Si el precio es tipo `semi_automatic`, el Standard debe tener `semi_auto_work_table_id` asignado

### ¿Puedo tener múltiples precios activos para una parte?

Sí, pero **cada uno debe ser de un tipo diferente**:
- ✅ 1 precio activo tipo `table` + 1 precio activo tipo `machine` = OK
- ❌ 2 precios activos tipo `table` = ERROR

### ¿Cómo sé qué tipo de precio necesito?

Mira el Standard de la parte:
- Si tiene `work_table_id` → necesitas precio tipo `table`
- Si tiene `semi_auto_work_table_id` → necesitas precio tipo `semi_automatic`
- Si tiene `machine_id` → necesitas precio tipo `machine`

## Contacto

Si tienes dudas o necesitas ayuda, consulta:
- `docs/PRICE_DETECTION_DIAGNOSIS.md` - Diagnóstico completo
- `docs/PRICE_DETECTION_IMPLEMENTATION_SUMMARY.md` - Resumen de implementación
