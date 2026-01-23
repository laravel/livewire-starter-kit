# Analisis Tecnico: Deteccion de Precio en PO - Error de Coincidencia

**Fecha:** 2026-01-22
**Modulo:** Fase 1 - Recibir PO
**Severidad:** Alta
**Estado:** Analisis Completado

---

## 1. Problema Reportado

Al crear un PO e ingresar el numero de parte, el sistema deberia traer el precio unitario guardado en el CRUD de Prices, pero NO esta funcionando correctamente. Sin embargo, con el numero de parte "H-M-2 (Table)" SI trae el precio correctamente.

---

## 2. Causa Raiz Identificada

### 2.1 Descripcion del Problema

Existe una **inconsistencia de datos entre los campos legacy del Standard y el workstation_type del Price**. El sistema utiliza dos fuentes de informacion que no estan sincronizadas:

1. **Standard.getAssemblyMode()**: Determina el tipo de estacion desde campos legacy (`work_table_id`, `semi_auto_work_table_id`, `machine_id`) o desde `StandardConfiguration`.

2. **Price.workstation_type**: Almacena el tipo de estacion como 'table', 'machine', o 'semi_automatic'.

### 2.2 Flujo del Error

```
Usuario selecciona Part ->
POPriceDetectionService.detectPriceForPart() ->
  Standard.getAssemblyMode() ->
    [Si work_table_id != NULL] -> return 'manual' ->
      mapAssemblyModeToWorkstationType('manual') -> return 'table' ->
        Part.activePriceForWorkstationType('table') ->
          [NO encuentra precio porque el precio tiene workstation_type='machine']
```

### 2.3 Mapeo de Tipos

**En POPriceDetectionService (lineas 13-18):**
```php
private const ASSEMBLY_MODE_MAP = [
    'manual' => Price::WORKSTATION_TABLE,        // 'manual' -> 'table'
    'semi_automatic' => Price::WORKSTATION_SEMI_AUTOMATIC,
    'machine' => Price::WORKSTATION_MACHINE,
];
```

**El problema:** Los Standards con campos legacy SIEMPRE devuelven el assembly_mode basado en el PRIMER campo no-null en este orden:
1. `work_table_id` -> 'manual'
2. `semi_auto_work_table_id` -> 'semi_automatic'
3. `machine_id` -> 'machine'

---

## 3. Evidencia del Problema

### 3.1 Caso que FUNCIONA: H-M-2 (Table)

```
Part ID: 20
Part Number: H-M-2 (Table)

Standard ID: 21
  work_table_id: NULL
  semi_auto_work_table_id: NULL
  machine_id: NULL
  is_migrated: No (pero tiene configuraciones)

  Configurations:
    - ID: 17, workstation_type: 'machine', is_default: Yes

  getAssemblyMode() -> 'machine' (desde configuracion)

Price ID: 26
  workstation_type: 'machine'
  active: Yes

RESULTADO: COINCIDE -> Precio encontrado correctamente
```

### 3.2 Caso que NO FUNCIONA: PART-002

```
Part ID: 2
Part Number: PART-002

Standard ID: 2
  work_table_id: 2  <-- Tiene valor
  semi_auto_work_table_id: NULL
  machine_id: NULL
  is_migrated: No

  Configurations count: 0

  getAssemblyMode() -> 'manual' (desde campo legacy)

  Mapeo: 'manual' -> 'table'

Price ID: 2
  workstation_type: 'machine'  <-- No coincide con 'table'
  active: Yes

RESULTADO: NO COINCIDE -> "No se encontro un precio activo para tipo Mesa de Trabajo"
```

### 3.3 Resumen de Partes Afectadas (muestra)

| Part | Standard work_table_id | Assembly Mode | Price Type | Estado |
|------|------------------------|---------------|------------|--------|
| PART-001 | 1 | manual -> table | table | OK |
| PART-002 | 2 | manual -> table | machine | ERROR |
| PART-003 | 3 | manual -> table | semi_automatic | ERROR |
| PART-004 | 4 | manual -> table | table | OK |
| PART-005 | 5 | manual -> table | machine | ERROR |
| PART-006 | 6 | manual -> table | semi_automatic | ERROR |
| PART-007 | 7 | manual -> table | table | OK |
| PART-008 | 8 | manual -> table | machine | ERROR |
| PART-009 | 1 | manual -> table | semi_automatic | ERROR |
| PART-010 | 2 | manual -> table | table | OK |
| H-M-2 (Table) | NULL | machine (config) | machine | OK |

---

## 4. Archivos Afectados

### 4.1 Archivos Principales del Problema

| Archivo | Lineas | Descripcion |
|---------|--------|-------------|
| `app/Services/POPriceDetectionService.php` | 13-18, 201-221 | Mapeo de assembly_mode a workstation_type |
| `app/Models/Standard.php` | 287-307 | Metodo getAssemblyMode() con logica legacy |
| `app/Models/Price.php` | 16-24 | Constantes de workstation_type |

### 4.2 Codigo Critico en Standard.php (lineas 287-307)

```php
public function getAssemblyMode(): ?string
{
    // Primero intentar con los campos legacy
    if ($this->work_table_id) return 'manual';          // <-- PROBLEMA: Prioriza legacy
    if ($this->semi_auto_work_table_id) return 'semi_automatic';
    if ($this->machine_id) return 'machine';

    // Si no hay campos legacy, buscar en las configuraciones
    $defaultConfig = $this->configurations()->where('is_default', true)->first();
    if ($defaultConfig) {
        return $defaultConfig->workstation_type;
    }

    $firstConfig = $this->configurations()->first();
    if ($firstConfig) {
        return $firstConfig->workstation_type;
    }

    return null;
}
```

### 4.3 Codigo Critico en POPriceDetectionService.php (lineas 13-18)

```php
private const ASSEMBLY_MODE_MAP = [
    'manual' => Price::WORKSTATION_TABLE,        // 'manual' -> 'table'
    'semi_automatic' => Price::WORKSTATION_SEMI_AUTOMATIC,
    'machine' => Price::WORKSTATION_MACHINE,
];
```

---

## 5. Soluciones Recomendadas

### 5.1 Solucion A: Corregir Inconsistencia de Datos (Recomendada)

**Descripcion:** Asegurar que el `workstation_type` del Price coincida con el `assembly_mode` derivado del Standard.

**Pasos:**
1. Identificar todas las partes con inconsistencia
2. Actualizar los precios para que coincidan con el assembly_mode del Standard
3. O actualizar los Standards para que coincidan con el workstation_type del Price

**Query para identificar inconsistencias:**
```sql
SELECT
    p.id as part_id,
    p.number as part_number,
    s.id as standard_id,
    s.work_table_id,
    s.semi_auto_work_table_id,
    s.machine_id,
    CASE
        WHEN s.work_table_id IS NOT NULL THEN 'manual'
        WHEN s.semi_auto_work_table_id IS NOT NULL THEN 'semi_automatic'
        WHEN s.machine_id IS NOT NULL THEN 'machine'
        ELSE NULL
    END as derived_assembly_mode,
    pr.id as price_id,
    pr.workstation_type as price_workstation_type,
    pr.active as price_active
FROM parts p
JOIN standards s ON s.part_id = p.id AND s.active = 1
JOIN prices pr ON pr.part_id = p.id AND pr.active = 1
WHERE
    (s.work_table_id IS NOT NULL AND pr.workstation_type != 'table')
    OR (s.work_table_id IS NULL AND s.semi_auto_work_table_id IS NOT NULL AND pr.workstation_type != 'semi_automatic')
    OR (s.work_table_id IS NULL AND s.semi_auto_work_table_id IS NULL AND s.machine_id IS NOT NULL AND pr.workstation_type != 'machine');
```

### 5.2 Solucion B: Modificar la Logica de Deteccion de Precio

**Descripcion:** Cambiar el servicio para buscar el precio activo sin considerar el workstation_type, o usar una logica mas flexible.

**Cambio propuesto en POPriceDetectionService:**
```php
public function detectPriceForPart(int $partId, int $quantity): PriceDetectionResult
{
    $part = \App\Models\Part::find($partId);
    if (!$part) {
        return new PriceDetectionResult(/*...*/);
    }

    // Opcion 1: Buscar cualquier precio activo sin filtrar por workstation_type
    $price = $part->prices()->where('active', true)->first();

    if (!$price) {
        return new PriceDetectionResult(/*error: no hay precio activo*/);
    }

    return new PriceDetectionResult(
        price: $price,
        workstationType: $price->workstation_type,
        found: true,
        error: null
    );
}
```

**Nota:** Esta solucion simplifica la logica pero pierde la validacion de consistencia entre Standard y Price.

### 5.3 Solucion C: Migrar todos los Standards a Configuraciones

**Descripcion:** Eliminar la dependencia de campos legacy migrando todos los Standards a usar `StandardConfiguration`.

**Pasos:**
1. Crear un comando Artisan para migrar Standards legacy
2. Para cada Standard con campos legacy, crear la configuracion correspondiente
3. Limpiar los campos legacy

**Comando propuesto:**
```php
php artisan standards:migrate-to-configurations
```

---

## 6. Impacto Arquitectural

### 6.1 Backend
- El modelo `Standard` tiene logica dual (legacy + configuraciones)
- El servicio `POPriceDetectionService` depende de la consistencia de datos
- El modelo `Price` no tiene validacion cruzada con `Standard`

### 6.2 Base de Datos
- Tabla `standards`: Campos legacy (`work_table_id`, `semi_auto_work_table_id`, `machine_id`) coexisten con `standard_configurations`
- Tabla `prices`: Campo `workstation_type` no esta validado contra el Standard correspondiente

### 6.3 Frontend
- El CRUD de Prices permite seleccionar cualquier `workstation_type` sin validar contra el Standard
- El formulario de PO no muestra por que falla la deteccion de precio

---

## 7. Plan de Implementacion Recomendado

### Fase 1: Diagnostico Completo (1-2 horas)
1. Ejecutar query de inconsistencias para identificar todas las partes afectadas
2. Documentar la cantidad y naturaleza de las inconsistencias
3. Determinar si los datos correctos estan en Standards o en Prices

### Fase 2: Correccion de Datos (2-4 horas)
1. Crear backup de tablas `standards`, `prices`, `standard_configurations`
2. Ejecutar script de correccion segun la solucion elegida
3. Verificar que H-M-2 (Table) sigue funcionando
4. Verificar que las partes previamente fallidas ahora funcionan

### Fase 3: Prevencion (4-8 horas)
1. Agregar validacion en `PriceCreate` y `PriceEdit` para verificar consistencia con Standard
2. Agregar validacion en `StandardCreate` y `StandardEdit` para verificar consistencia con Price
3. Considerar migrar todos los Standards a configuraciones (Solucion C)

---

## 8. Conclusiones

1. **La causa raiz es una inconsistencia de datos**, no un bug de codigo
2. **El caso H-M-2 (Table) funciona** porque su Standard no tiene campos legacy y usa configuraciones correctamente
3. **La mayoria de las partes fallan** porque tienen campos legacy en Standard que fuerzan `assembly_mode = 'manual'` pero sus precios tienen `workstation_type` diferente
4. **Se necesita una decision de negocio**: Cual es la fuente de verdad para el tipo de estacion - el Standard o el Price?

---

## 9. Referencias

- Diagrama de flujo: `Diagramas_flujo/diagramas/1-diagrama-Recibir-po.mkd`
- Spec de Standards: `Diagramas_flujo/Estructura/specs/06_multiple_standards_per_part_architecture.md`
- Modelo Price: `app/Models/Price.php`
- Modelo Standard: `app/Models/Standard.php`
- Servicio de Deteccion: `app/Services/POPriceDetectionService.php`
