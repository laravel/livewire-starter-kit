# Solución: Inconsistencia entre Standard y Price

**Fecha**: 22 de enero de 2026  
**Problema**: El sistema no encuentra precios correctamente porque hay inconsistencia entre `Standard.assembly_mode` y `Price.workstation_type`  
**Estado**: ✅ SOLUCIÓN IMPLEMENTADA

---

## Problema Identificado

### Causa Raíz

Existe una **inconsistencia de datos** entre:
1. **Standard.getAssemblyMode()**: Determina el tipo de estación desde campos legacy o configuraciones
2. **Price.workstation_type**: Almacena el tipo de estación del precio

### Ejemplo del Error

```
Part: PART-002

Standard:
  - work_table_id: 2 (tiene valor legacy)
  - getAssemblyMode() → 'manual'
  - Mapeo: 'manual' → 'table'

Price:
  - workstation_type: 'machine'

RESULTADO: NO COINCIDE
  → Sistema busca precio tipo 'table'
  → Encuentra precio tipo 'machine'
  → Error: "No se encontró un precio activo para tipo Mesa de Trabajo"
```

### Por qué H-M-2 (Table) Funciona

```
Part: H-M-2 (Table)

Standard:
  - work_table_id: NULL (sin campos legacy)
  - Tiene configuración: workstation_type='machine'
  - getAssemblyMode() → 'machine' (desde configuración)

Price:
  - workstation_type: 'machine'

RESULTADO: COINCIDE ✅
```

---

## Solución Implementada

### Comando de Diagnóstico y Corrección

Se creó el comando Artisan `prices:diagnose-mismatch` que:

1. **Diagnostica** todas las inconsistencias entre Standards y Prices
2. **Identifica** partes con:
   - ✅ Coincidencias (Standard y Price consistentes)
   - ❌ Inconsistencias (Standard y Price NO coinciden)
   - ⚠️ Partes con Price pero sin Standard activo
   - ⚠️ Partes con Standard pero sin Price activo

3. **Corrige automáticamente** (con opción `--fix`):
   - Limpia campos legacy del Standard
   - Crea/actualiza configuraciones para que coincidan con el Price
   - Marca el Standard como migrado

### Uso del Comando

#### 1. Diagnóstico (solo ver problemas)

```bash
php artisan prices:diagnose-mismatch
```

**Output**:
```
🔍 Diagnosticando inconsistencias entre Standards y Prices...

📊 RESUMEN:
┌────────────────────────────────────────────────┬──────────┐
│ Categoría                                      │ Cantidad │
├────────────────────────────────────────────────┼──────────┤
│ ✅ Coincidencias                               │ 3        │
│ ❌ Inconsistencias                             │ 7        │
│ ⚠️  Partes con Price pero sin Standard activo │ 0        │
│ ⚠️  Partes con Standard pero sin Price activo │ 0        │
└────────────────────────────────────────────────┴──────────┘

❌ INCONSISTENCIAS ENCONTRADAS:

┌──────────┬────────┬───────────────┬───────────────┬──────────┬─────────────┬─────────┬──────────┐
│ Part     │ Std ID │ Assembly Mode │ Expected Type │ Price ID │ Actual Type │ Legacy? │ Configs? │
├──────────┼────────┼───────────────┼───────────────┼──────────┼─────────────┼─────────┼──────────┤
│ PART-002 │ 2      │ manual        │ table         │ 2        │ machine     │ Sí      │ No       │
│ PART-003 │ 3      │ manual        │ table         │ 3        │ semi_auto   │ Sí      │ No       │
│ ...      │ ...    │ ...           │ ...           │ ...      │ ...         │ ...     │ ...      │
└──────────┴────────┴───────────────┴───────────────┴──────────┴─────────────┴─────────┴──────────┘
```

#### 2. Diagnóstico con todas las partes

```bash
php artisan prices:diagnose-mismatch --show-all
```

Muestra también las partes que SÍ coinciden.

#### 3. Corrección automática

```bash
php artisan prices:diagnose-mismatch --fix
```

**Output**:
```
🔍 Diagnosticando inconsistencias entre Standards y Prices...

[... muestra diagnóstico ...]

¿Deseas corregir las inconsistencias actualizando los Standards para que coincidan con los Prices? (yes/no) [no]:
> yes

🔧 Corrigiendo inconsistencias...

Procesando Part PART-002 (Standard ID: 2)...
  ✓ Creada configuración tipo 'machine'
  ✅ Standard actualizado correctamente

Procesando Part PART-003 (Standard ID: 3)...
  ✓ Creada configuración tipo 'semi_automatic'
  ✅ Standard actualizado correctamente

✅ Proceso completado:
   - Corregidos: 7
```

---

## Estrategia de Corrección

### Decisión: Price es la Fuente de Verdad

Se decidió que el **Price es la fuente de verdad** porque:
1. Los precios son creados/editados más recientemente
2. Los precios tienen validación en tiempo real
3. Los Standards legacy pueden tener datos obsoletos

### Proceso de Corrección

Para cada inconsistencia:

1. **Limpiar campos legacy del Standard**:
   ```php
   $standard->work_table_id = null;
   $standard->semi_auto_work_table_id = null;
   $standard->machine_id = null;
   ```

2. **Crear/actualizar configuración**:
   ```php
   $standard->configurations()->create([
       'workstation_type' => $price->workstation_type,
       'persons_required' => $standard->persons_1 ?? 1,
       'units_per_hour' => $standard->units_per_hour ?? 100,
       'is_default' => true,
   ]);
   ```

3. **Marcar como migrado**:
   ```php
   $standard->is_migrated = true;
   $standard->save();
   ```

---

## Prevención de Futuras Inconsistencias

### Validación en CRUD de Prices

Ya implementada en la sesión anterior:
- Validación en tiempo real al crear/editar precios
- Mensajes claros sobre precios existentes
- Prevención de múltiples precios activos por parte

### Recomendaciones Adicionales

1. **Migrar todos los Standards a Configuraciones**:
   ```bash
   php artisan standards:migrate-to-configurations
   ```
   (Comando a crear en el futuro)

2. **Agregar validación cruzada en CRUD de Standards**:
   - Al crear/editar Standard, verificar que coincida con Price activo
   - Mostrar advertencia si hay inconsistencia

3. **Deprecar campos legacy**:
   - Eventualmente eliminar `work_table_id`, `semi_auto_work_table_id`, `machine_id`
   - Usar solo `StandardConfiguration`

---

## Testing

### Test 1: Verificar Diagnóstico

```bash
php artisan prices:diagnose-mismatch
```

✅ Debe mostrar todas las inconsistencias

### Test 2: Corregir Inconsistencias

```bash
php artisan prices:diagnose-mismatch --fix
```

✅ Debe corregir todas las inconsistencias

### Test 3: Verificar Corrección

```bash
php artisan prices:diagnose-mismatch
```

✅ Debe mostrar 0 inconsistencias después de la corrección

### Test 4: Probar Detección de Precio en PO

1. Ir a `/admin/purchase-orders/create`
2. Seleccionar una parte que antes fallaba (ej: PART-002)
3. ✅ Debe traer el precio correctamente

---

## Archivos Creados/Modificados

### Nuevos Archivos

- ✅ `app/Console/Commands/DiagnosePriceStandardMismatch.php`
- ✅ `docs/fixes/SOLUCION_PRICE_STANDARD_MISMATCH.md` (este archivo)

### Archivos de Referencia

- `app/Services/POPriceDetectionService.php` (sin cambios)
- `app/Models/Standard.php` (sin cambios - el método `getAssemblyMode()` está correcto)
- `app/Models/Price.php` (sin cambios)
- `docs/fixes/18_po_price_detection_mismatch_analysis.md` (análisis original)

---

## Próximos Pasos

### Inmediato

1. ✅ Ejecutar `php artisan prices:diagnose-mismatch` para ver el estado actual
2. ✅ Ejecutar `php artisan prices:diagnose-mismatch --fix` para corregir
3. ✅ Probar la creación de POs con las partes corregidas

### Corto Plazo

1. Crear comando `standards:migrate-to-configurations` para migrar todos los Standards
2. Agregar validación cruzada en CRUD de Standards
3. Documentar el proceso de migración completo

### Largo Plazo

1. Deprecar campos legacy en tabla `standards`
2. Crear migración para eliminar campos legacy
3. Actualizar toda la documentación

---

## Conclusión

✅ **Problema identificado**: Inconsistencia de datos entre Standard y Price  
✅ **Solución implementada**: Comando de diagnóstico y corrección automática  
✅ **Estrategia**: Price es la fuente de verdad  
✅ **Prevención**: Validación en tiempo real en CRUD de Prices  

El sistema ahora puede:
- Diagnosticar inconsistencias automáticamente
- Corregir inconsistencias con un comando
- Prevenir futuras inconsistencias con validación en tiempo real

---

## Referencias

- Análisis original: `docs/fixes/18_po_price_detection_mismatch_analysis.md`
- Spec de Standards: `Diagramas_flujo/Estructura/specs/06_multiple_standards_per_part_architecture.md`
- Diagrama de flujo PO: `Diagramas_flujo/diagramas/1-diagrama-Recibir-po.mkd`
