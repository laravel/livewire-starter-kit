# Diagnóstico de Detección de Precios

## Resumen

El sistema de detección de precios está funcionando correctamente. Los mensajes de error "No hay precio registrado para esta parte" son **correctos** porque indican inconsistencias en los datos.

## Cómo Funciona el Sistema

El sistema detecta automáticamente el precio correcto basándose en:

1. **Standard activo** de la parte → determina el `assembly_mode` (manual, semi_automatic, machine)
2. **Assembly mode** → se mapea a `workstation_type`:
   - `manual` → `table`
   - `semi_automatic` → `semi_automatic`
   - `machine` → `machine`
3. **Precio activo** → debe existir un precio con el `workstation_type` correcto

## Problemas Encontrados

### ❌ Tipo 1: Precio no coincide con Standard (6 partes)

Estas partes tienen Standards con `assembly_mode=manual` pero sus precios están asignados a otros tipos:

| Part ID | Número | Standard Mode | Precio Tipo | Acción Requerida |
|---------|--------|---------------|-------------|------------------|
| 2 | PART-002 | manual (→table) | machine | Cambiar precio a tipo `table` o cambiar Standard |
| 3 | PART-003 | manual (→table) | semi_automatic | Cambiar precio a tipo `table` o cambiar Standard |
| 5 | PART-005 | manual (→table) | machine | Cambiar precio a tipo `table` o cambiar Standard |
| 6 | PART-006 | manual (→table) | semi_automatic | Cambiar precio a tipo `table` o cambiar Standard |
| 8 | PART-008 | manual (→table) | machine | Cambiar precio a tipo `table` o cambiar Standard |

### ❌ Tipo 2: Standard sin workstation asignado (1 parte)

| Part ID | Número | Problema | Acción Requerida |
|---------|--------|----------|------------------|
| 9 | PART-009 | Standard sin work_table_id, semi_auto_work_table_id, ni machine_id | Asignar una estación al Standard |

### ❌ Tipo 3: Sin Standard activo (7 partes)

Estas partes tienen precios pero no tienen Standards activos:

| Part ID | Número | Precio Tipo | Acción Requerida |
|---------|--------|-------------|------------------|
| 11 | PART-TEST-1 | table | Crear Standard activo |
| 12 | PART-TEST-2 | table | Crear Standard activo |
| 13 | PART-TEST-3 | machine | Crear Standard activo |
| 14 | PART-TEST-4 | machine | Crear Standard activo |
| 15 | PART-TEST-5 | semi_automatic | Crear Standard activo |
| 22 | H-M-2 (Table) | ninguno | Crear Standard y Precio |

### ✅ Partes funcionando correctamente (9 partes)

Estas partes tienen todo configurado correctamente:

- PART-001, PART-004, PART-007, PART-010
- WIZARD-001, WIZARD-002, WIZARD-003, WIZARD-004, WIZARD-005, WIZARD-006

## Soluciones

### Opción A: Corregir los Precios

Para cada parte con precio incorrecto, editar el precio y cambiar su `workstation_type` al tipo correcto:

```sql
-- Ejemplo para PART-002 (cambiar de machine a table)
UPDATE prices 
SET workstation_type = 'table' 
WHERE part_id = 2 AND active = 1;
```

### Opción B: Corregir los Standards

Si el precio es correcto pero el Standard está mal, actualizar el Standard para que use la estación correcta:

```sql
-- Ejemplo para PART-002 (cambiar de manual/table a machine)
UPDATE standards 
SET work_table_id = NULL, 
    machine_id = [ID_DE_MAQUINA] 
WHERE id = 2;
```

### Opción C: Crear Standards Faltantes

Para partes sin Standard activo, crear uno nuevo desde la UI de administración o mediante seeder.

## Script de Diagnóstico

Para verificar el estado de cualquier parte:

```bash
# Ver todas las partes
php debug_price.php

# Ver una parte específica
php debug_price.php [PART_ID]
```

## Mejoras Implementadas

1. **Mensajes de error detallados**: Ahora el sistema indica exactamente qué falta (Standard, Precio, o tipo de estación)
2. **Validación en tiempo real**: El formulario de PO muestra el tipo de estación detectado
3. **Script de diagnóstico**: Permite identificar rápidamente problemas en los datos

## Próximos Pasos

1. Revisar cada parte con error y decidir si corregir el precio o el Standard
2. Crear Standards para las partes de prueba (PART-TEST-*)
3. Verificar que Part 9 tenga una estación asignada en su Standard
4. Ejecutar el script de diagnóstico nuevamente para confirmar que todo está correcto
