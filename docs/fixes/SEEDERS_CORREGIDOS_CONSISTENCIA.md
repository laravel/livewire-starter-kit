# Seeders Corregidos para Consistencia Standard-Price

**Fecha**: 22 de enero de 2026  
**Problema**: Los seeders creaban datos inconsistentes entre Standard y Price  
**Estado**: ✅ CORREGIDO

---

## Problema

Los seeders originales creaban:
- **Standards con campos legacy** (`work_table_id`, `machine_id`, `semi_auto_work_table_id`)
- **Prices con `workstation_type`** que NO coincidía con el tipo derivado del Standard

Esto causaba que el sistema NO encontrara precios al crear POs.

---

## Solución Implementada

### 1. StandardSeeder.php

**ANTES**:
```php
Standard::create([
    'part_id' => $part->id,
    'work_table_id' => $tableId,  // ❌ Campo legacy
    'machine_id' => $machineId,
    'semi_auto_work_table_id' => $semiAutoId,
    // ...
]);
```

**DESPUÉS**:
```php
// Crear Standard SIN campos legacy
$standard = Standard::create([
    'part_id' => $part->id,
    'work_table_id' => null,  // ✅ NO usar campos legacy
    'machine_id' => null,
    'semi_auto_work_table_id' => null,
    'is_migrated' => true,  // ✅ Marcar como migrado
    // ...
]);

// Crear configuraciones
StandardConfiguration::create([
    'standard_id' => $standard->id,
    'workstation_type' => $workstationType,  // ✅ Usar configuraciones
    'persons_required' => 1,
    'units_per_hour' => $baseProductivity,
    'is_default' => true,
]);
```

**Cambios**:
- ✅ NO usa campos legacy
- ✅ Crea StandardConfiguration para cada Standard
- ✅ Marca `is_migrated = true`
- ✅ Crea 3 configuraciones por Standard (1, 2, 3 personas)

---

### 2. PriceSeeder.php

**ANTES**:
```php
// Alternar entre tipos de estación
$types = ['table', 'machine', 'semi_automatic'];
$type = $types[$index % 3];  // ❌ Tipo aleatorio, no relacionado con Standard
```

**DESPUÉS**:
```php
// Obtener el Standard activo de la parte para usar el mismo workstation_type
$standard = $part->standards()->where('active', true)->first();

if ($standard) {
    $defaultConfig = $standard->configurations()->where('is_default', true)->first();
    $type = $defaultConfig ? $defaultConfig->workstation_type : 'table';
} else {
    // Si no tiene Standard, alternar entre tipos
    $types = ['table', 'machine', 'semi_automatic'];
    $type = $types[$index % 3];
}
```

**Cambios**:
- ✅ Verifica el Standard de la parte
- ✅ Usa el mismo `workstation_type` que la configuración default del Standard
- ✅ Garantiza consistencia entre Standard y Price

---

### 3. CapacityWizardTestSeeder.php

**ANTES**:
```php
// Crear estándar con campos legacy
$standard = Standard::create([
    'work_table_id' => $tables['table1']->id ?? null,  // ❌ Campo legacy
    // ...
]);

// Crear precio con tipo fijo
$price = Price::create([
    'workstation_type' => 'table',  // ❌ Siempre 'table', no relacionado con Standard
]);
```

**DESPUÉS**:
```php
// Crear estándar SIN campos legacy
$standard = Standard::create([
    'work_table_id' => null,  // ✅ NO usar campos legacy
    'machine_id' => null,
    'semi_auto_work_table_id' => null,
    'is_migrated' => true,
    // ...
]);

// Crear precio consistente con Standard
$standard = $part->standards()->where('active', true)->first();
$workstationType = 'table';

if ($standard) {
    $defaultConfig = $standard->configurations()->where('is_default', true)->first();
    if ($defaultConfig) {
        $workstationType = $defaultConfig->workstation_type;
    }
}

$price = Price::create([
    'workstation_type' => $workstationType,  // ✅ Mismo tipo que Standard
]);
```

**Cambios**:
- ✅ NO usa campos legacy en Standards
- ✅ Prices usan el mismo `workstation_type` que el Standard
- ✅ Garantiza que el flujo completo funcione correctamente

---

## Archivos Modificados

1. ✅ `database/seeders/StandardSeeder.php`
2. ✅ `database/seeders/PriceSeeder.php`
3. ✅ `database/seeders/CapacityWizardTestSeeder.php`

---

## Cómo Usar los Seeders Corregidos

### Opción 1: Refresh completo (CUIDADO: Borra todos los datos)

```bash
php artisan migrate:fresh --seed
```

### Opción 2: Solo ejecutar seeders específicos

```bash
# Primero, limpiar datos inconsistentes existentes
php artisan prices:diagnose-mismatch --fix

# Luego, ejecutar seeders corregidos
php artisan db:seed --class=StandardSeeder
php artisan db:seed --class=PriceSeeder
php artisan db:seed --class=CapacityWizardTestSeeder
```

### Opción 3: Crear datos de prueba desde cero

```bash
# 1. Limpiar datos de prueba anteriores
php artisan tinker
>>> Part::where('number', 'LIKE', 'WIZARD-%')->delete();
>>> Part::where('number', 'LIKE', 'PART-%')->delete();
>>> exit

# 2. Ejecutar seeders
php artisan db:seed --class=CapacityWizardTestSeeder
```

---

## Verificación

Después de ejecutar los seeders, verifica que todo esté consistente:

```bash
php artisan prices:diagnose-mismatch
```

**Resultado esperado**:
```
📊 RESUMEN:
┌────────────────────────────────────────────────┬──────────┐
│ Categoría                                      │ Cantidad │
├────────────────────────────────────────────────┼──────────┤
│ ✅ Coincidencias                               │ X        │
│ ❌ Inconsistencias                             │ 0        │  ← Debe ser 0
│ ⚠️  Partes con Price pero sin Standard activo │ 0        │
│ ⚠️  Partes con Standard pero sin Price activo │ 0        │
└────────────────────────────────────────────────┴──────────┘
```

---

## Testing

### Test 1: Crear PO con parte de prueba

1. Ir a `/admin/purchase-orders/create`
2. Seleccionar parte `WIZARD-001`
3. ✅ Debe traer el precio correctamente
4. ✅ NO debe mostrar error de "No se encontró precio"

### Test 2: Capacity Wizard

1. Ir a `/admin/capacity-calculator`
2. Seleccionar turnos
3. Click "Cargar desde POs"
4. Seleccionar POs `PO-WIZARD-001` a `PO-WIZARD-006`
5. ✅ Todas deben cargar correctamente con sus precios

### Test 3: Verificar consistencia

```bash
php artisan prices:diagnose-mismatch --show-all
```

✅ Todas las partes deben aparecer en "Coincidencias"

---

## Beneficios

✅ **Datos consistentes desde el inicio**: Standards y Prices siempre coinciden  
✅ **No más errores de detección de precio**: El sistema encuentra precios correctamente  
✅ **Flujo completo funcional**: Capacity Wizard funciona sin problemas  
✅ **Fácil de probar**: Seeders crean datos listos para usar  
✅ **Arquitectura moderna**: Usa StandardConfiguration en lugar de campos legacy  

---

## Próximos Pasos

1. ✅ Ejecutar seeders corregidos
2. ✅ Verificar con `php artisan prices:diagnose-mismatch`
3. ✅ Probar creación de POs
4. ✅ Probar Capacity Wizard completo

---

## Referencias

- Análisis del problema: `docs/fixes/18_po_price_detection_mismatch_analysis.md`
- Solución implementada: `docs/fixes/SOLUCION_PRICE_STANDARD_MISMATCH.md`
- Comando de diagnóstico: `app/Console/Commands/DiagnosePriceStandardMismatch.php`
