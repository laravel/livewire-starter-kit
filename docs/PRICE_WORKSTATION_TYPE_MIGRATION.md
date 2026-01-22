# Migración de Precios por Tipo de Estación de Trabajo

## Descripción

Este documento describe el comando de migración de datos para el sistema de precios separados por tipo de estación de trabajo en Flexcon ERP.

## Comando

```bash
php artisan prices:migrate-workstation-types
```

## Opciones

### --dry-run

Ejecuta el comando en modo simulación sin modificar datos en la base de datos. Útil para ver qué cambios se realizarían antes de aplicarlos.

```bash
php artisan prices:migrate-workstation-types --dry-run
```

## Qué hace el comando

El comando realiza las siguientes acciones:

1. **Analiza precios sin workstation_type**
   - Busca precios con `workstation_type` NULL o vacío
   - Asigna el tipo por defecto `table` (Mesa de Trabajo)

2. **Detecta precios duplicados activos**
   - Busca múltiples precios activos del mismo tipo para una misma parte
   - Mantiene el precio más reciente (por `effective_date`)
   - Desactiva los precios duplicados más antiguos

3. **Valida reglas de unicidad**
   - Verifica que solo exista un precio activo por tipo de estación por parte
   - Reporta cualquier violación encontrada

4. **Genera reporte detallado**
   - Muestra estadísticas antes y después de la migración
   - Lista los cambios realizados
   - Muestra distribución de precios por tipo de estación

## Ejemplos de Uso

### 1. Ejecutar en modo simulación (recomendado primero)

```bash
php artisan prices:migrate-workstation-types --dry-run
```

**Salida esperada:**
```
🔍 Ejecutando en modo DRY-RUN (no se modificarán datos)

📊 Analizando precios existentes...

+------------------+-------+
| Métrica          | Valor |
+------------------+-------+
| Total de precios | 150   |
| Precios activos  | 45    |
+------------------+-------+

⚠️  Encontrados 5 precios sin workstation_type
   → Se asignarían 5 precios a tipo 'table'

🔍 Buscando precios duplicados activos...
⚠️  Encontrados 3 grupos de precios duplicados

+----------+------------------+-------------+----------------+
| Part     | Tipo             | Mantener ID | Desactivar IDs |
+----------+------------------+-------------+----------------+
| PART-001 | Mesa de Trabajo  | 12          | 8, 5           |
| PART-002 | Máquina          | 25          | 20             |
| PART-003 | Semi-Automática  | 38          | 35, 32         |
+----------+------------------+-------------+----------------+

   → Se desactivarían 5 precios duplicados

✅ Todas las reglas de unicidad se cumplen correctamente

⚠️  Modo DRY-RUN: No se realizaron cambios en la base de datos
   Ejecute sin --dry-run para aplicar los cambios
```

### 2. Aplicar cambios reales

```bash
php artisan prices:migrate-workstation-types
```

**Salida esperada:**
```
📊 Analizando precios existentes...

⚠️  Encontrados 5 precios sin workstation_type
Asignando workstation_type por defecto (table)...
✅ Asignados 5 precios a tipo 'table'

🔍 Buscando precios duplicados activos...
⚠️  Encontrados 3 grupos de precios duplicados

✅ Desactivados 5 precios duplicados

✅ Todas las reglas de unicidad se cumplen correctamente

📋 Reporte Final:

+---------------------------+-------+
| Métrica                   | Valor |
+---------------------------+-------+
| Total de precios          | 150   |
| Precios activos           | 40    |
| Precios por tipo:         |       |
|   - Mesa de Trabajo       | 25    |
|   - Máquina               | 10    |
|   - Semi-Automática       | 5     |
+---------------------------+-------+

✅ Migración completada exitosamente
```

## Cuándo ejecutar este comando

- **Después de actualizar el código**: Si acabas de actualizar el sistema con los cambios de separación de precios por tipo de estación
- **Antes de producción**: Ejecuta primero con `--dry-run` en staging para verificar los cambios
- **Mantenimiento periódico**: Si sospechas que hay precios duplicados en el sistema

## Notas Importantes

⚠️ **IMPORTANTE**: 
- Siempre ejecuta primero con `--dry-run` para ver qué cambios se realizarán
- Haz un backup de la base de datos antes de ejecutar sin `--dry-run`
- El comando mantiene el precio más reciente cuando hay duplicados
- Los precios desactivados NO se eliminan, solo se marcan como inactivos

## Solución de Problemas

### El comando reporta violaciones de unicidad después de ejecutar

Si después de ejecutar el comando aún hay violaciones, ejecuta el comando nuevamente. Esto puede ocurrir si hay múltiples niveles de duplicación.

### Quiero mantener un precio específico en lugar del más reciente

Antes de ejecutar el comando, actualiza manualmente el `effective_date` del precio que quieres mantener para que sea el más reciente.

### Necesito revertir los cambios

Los precios desactivados pueden reactivarse manualmente desde la interfaz de administración, pero ten cuidado de no crear duplicados nuevamente.

## Soporte

Para más información o problemas, contacta al equipo de desarrollo.
