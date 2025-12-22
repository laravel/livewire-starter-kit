# Spec 04: Análisis Técnico - Validación de Personas y Estaciones de Trabajo en Standards

**Fecha de Creación:** 2025-12-20
**Autor:** Agent Architect
**Fase del Proyecto:** FASE 2 - Planificación de Producción
**Estado:** Análisis y Propuesta
**Versión:** 1.0
**Relacionado con:**
- Spec 01 - Plan de Implementación Capacidad de Producción
- Spec 02 - Refactorización Standards-Workstation (Análisis Técnico Completo)
- Spec 03 - Guía Rápida de Implementación - Standards Workstation Relationship

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Análisis del Problema](#análisis-del-problema)
3. [Propuesta de Solución](#propuesta-de-solución)
4. [Impacto Arquitectural](#impacto-arquitectural)
5. [Plan de Implementación](#plan-de-implementación)
6. [Riesgos y Mitigaciones](#riesgos-y-mitigaciones)
7. [Referencias](#referencias)

---

## Resumen Ejecutivo

### Problema Identificado

El sistema actual de `standards` permite definir configuraciones de personal (`persons_1`, `persons_2`, `persons_3`) pero carece de validaciones robustas que garanticen la coherencia entre:

1. El número de personas requeridas para producir una parte
2. La capacidad de la estación de trabajo asignada
3. La relación lógica entre configuraciones de personal alternativas

**Estado Actual:** La implementación cumple con el requisito básico de "una estación por standard", pero no considera adecuadamente la semántica de negocio detrás de las configuraciones de personal.

### Hallazgos Críticos

1. **Productividad Diferenciada CONFIRMADA**: El usuario confirmó que cada configuración (`persons_1`, `persons_2`, `persons_3`) puede tener productividad diferente ("cada uno tiene una cantidad diferente"). Esta es la limitación más crítica del diseño actual.

2. **Semántica Ambigua (ACLARADA)**:
   - ¿Son configuraciones alternativas de producción? **SÍ** - La empresa usa algunos estándares para cuando un número de parte se puede correr con 1, 2 o 3 empleados.
   - ¿Cada configuración tiene productividad diferente? **SÍ** - En casos específicos, cada configuración (1, 2 o 3 personas) tiene una cantidad de producción diferente.
   - ¿Máximo de personas por configuración? **MÁXIMO 3 PERSONAS** - El sistema está limitado a un máximo de 3 personas por configuración.

3. **Falta de Coherencia con Estaciones (PRIORIDAD BAJA)**: La validación actual NO verifica si el número de personas es compatible con la capacidad (`employees`) de la estación asignada. **NOTA DEL USUARIO:** "Esto es un dato adicional, no es obligatoriamente requerido, es superficial."

4. **Inconsistencia de Datos Posible (PRIORIDAD BAJA)**: El sistema permite crear standards donde `persons_2 = 5` pero la estación asignada solo tiene `employees = 2`. **NOTA DEL USUARIO:** "No es necesario pero se podría ver en un futuro."

5. **Ausencia de Regla de Negocio Clara**: No hay documentación ni validación sobre cuándo usar `persons_1` vs `persons_2` vs `persons_3`.

### Propuesta de Solución

**DECISIÓN ACTUALIZADA basada en confirmación del usuario:**

**Para CORTO PLAZO (Implementación Inmediata):**
→ **Opción B** (Validaciones Mejoradas) - CON ADVERTENCIAS, NO BLOQUEANTES

**Para MEDIANO PLAZO (CRÍTICO - 6-8 semanas después):**
→ **Opción A** (Configuraciones Múltiples) - NECESARIA por productividad diferenciada

**Justificación del Cambio de Prioridad:**
El usuario confirmó que "cada uno tiene una cantidad diferente" (productividad diferenciada por configuración). Esto hace que Opción A NO sea solo "recomendada" sino **CRÍTICA** para el negocio, ya que la Opción B NO puede almacenar productividades diferentes para cada configuración.

**Opción B - Implementación Inmediata:**
1. Redefinir el significado de `persons_1`, `persons_2`, `persons_3` como **configuraciones alternativas de producción**
2. Implementar validación de coherencia con capacidad de estación (COMO ADVERTENCIA, NO BLOQUEANTE)
3. Agregar campo `default_persons_config` para indicar configuración preferida
4. Crear validación custom `ValidWorkstationCapacity` (modo warning)
5. Documentar limitación de productividad única

**Tiempo Estimado:** 6-8 horas de implementación + testing
**Riesgo:** MEDIO (limitación conocida de productividad única)

---

## Análisis del Problema

### 1. Estado Actual de la Estructura

#### Esquema de Base de Datos (tabla `standards`)

```sql
CREATE TABLE standards (
    id BIGINT PRIMARY KEY,
    part_id BIGINT NOT NULL,
    work_table_id BIGINT NULL,
    semi_auto_work_table_id BIGINT NULL,
    machine_id BIGINT NULL,
    units_per_hour INT NOT NULL,
    persons_1 INT NULL,
    persons_2 INT NULL,
    persons_3 INT NULL,
    effective_date DATE NULL,
    active BOOLEAN DEFAULT TRUE,
    description TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    CONSTRAINT fk_part FOREIGN KEY (part_id) REFERENCES parts(id),
    CONSTRAINT fk_work_table FOREIGN KEY (work_table_id) REFERENCES tables(id),
    CONSTRAINT fk_semi_auto FOREIGN KEY (semi_auto_work_table_id) REFERENCES semi__automatics(id),
    CONSTRAINT fk_machine FOREIGN KEY (machine_id) REFERENCES machines(id)
);
```

#### Validaciones Actuales

**Componente `StandardCreate.php` / `StandardEdit.php`:**

```php
protected function rules(): array
{
    return [
        'part_id' => 'required|exists:parts,id',
        'units_per_hour' => 'required|integer|min:1|max:10000',
        'work_table_id' => [
            'nullable',
            'exists:tables,id',
            new OnlyOneWorkstation($this->semi_auto_work_table_id, $this->machine_id),
        ],
        'semi_auto_work_table_id' => [
            'nullable',
            'exists:semi__automatics,id',
            new OnlyOneWorkstation($this->work_table_id, $this->machine_id),
        ],
        'machine_id' => [
            'nullable',
            'exists:machines,id',
            new OnlyOneWorkstation($this->work_table_id, $this->semi_auto_work_table_id),
        ],
        'persons_1' => 'nullable|integer|min:1',  // ❌ No valida coherencia con estación
        'persons_2' => 'nullable|integer|min:1',  // ❌ No valida coherencia con estación
        'persons_3' => 'nullable|integer|min:1',  // ❌ No valida coherencia con estación
        'effective_date' => 'nullable|date',
        'active' => 'boolean',
        'description' => 'nullable|string',
    ];
}
```

**Validación adicional en `saveStandard()`:**

```php
// Validación adicional: al menos UNA estación debe estar seleccionada
if (!$this->work_table_id && !$this->semi_auto_work_table_id && !$this->machine_id) {
    $this->addError('work_table_id', 'Debe seleccionar al menos UNA estación de trabajo.');
    return;
}
```

#### Modelos de Estaciones de Trabajo

**Capacidad de Estaciones:**

```php
// Table.php (Mesa Manual)
protected $fillable = [
    'number',
    'employees',  // ⚠️ Capacidad de personas
    'area_id',
    'active'
];

// Semi_Automatic.php (Mesa Semi-Automática)
protected $fillable = [
    'number',
    'employees',  // ⚠️ Capacidad de personas
    'area_id',
    'active'
];

// Machine.php (Máquina)
protected $fillable = [
    'name',
    'brand',
    'model',
    'employees',  // ⚠️ Capacidad de personas
    'area_id',
    'active'
];
```

### 2. Análisis de Problemas Identificados

#### Problema 1: Semántica Ambigua de `persons_1`, `persons_2`, `persons_3`

**¿Qué significan estos campos?**

**Hipótesis A: Configuraciones Alternativas de Producción**
```
Part: ABC-123
Estación: Mesa Manual #5 (capacity: 3 employees)

persons_1 = 1  →  1 persona produce 50 units/hour
persons_2 = 2  →  2 personas producen 100 units/hour
persons_3 = 3  →  3 personas producen 150 units/hour
```

**Hipótesis B: Diferentes Standards para Diferentes Configuraciones**
```
Part: ABC-123

Standard #1:
  - work_table_id: 5
  - persons_1: 1
  - units_per_hour: 50

Standard #2:
  - work_table_id: 5
  - persons_1: 2
  - units_per_hour: 100
```

**Hipótesis C: Configuración Principal + Alternativas**
```
Part: ABC-123
Estación: Mesa Manual #5

persons_1 = 2  →  Configuración estándar (2 personas)
persons_2 = 3  →  Configuración con ayuda adicional (producción más rápida)
persons_3 = NULL
```

**Análisis de la Implementación Actual:**

Revisando el código, NO hay lógica que utilice `persons_1`, `persons_2`, `persons_3` en ningún cálculo. Estos campos:
- Se almacenan en la BD
- Se validan mínimamente (`min:1`)
- NO se relacionan con `units_per_hour`
- NO se relacionan con la capacidad de la estación

**Conclusión:** Los campos existen pero carecen de propósito funcional claro. Esto sugiere un **diseño incompleto** o **feature no implementada**.

#### Problema 2: Falta de Coherencia con Capacidad de Estación

**Escenario Problemático:**

```php
// Mesa Manual #5
Table {
    id: 5,
    number: "M-05",
    employees: 2,  // ⚠️ Capacidad máxima: 2 personas
    area_id: 1,
    active: true
}

// Standard creado
Standard {
    part_id: 100,
    work_table_id: 5,
    persons_1: 1,
    persons_2: 2,
    persons_3: 5,  // ❌ ¡INCOHERENTE! La mesa solo tiene capacidad para 2
    units_per_hour: 100,
    active: true
}
```

**El sistema actual PERMITE crear este standard sin errores.**

**Impacto:**
- Datos inconsistentes en base de datos
- Cálculos de capacidad incorrectos si se implementan en el futuro
- Confusión para usuarios del sistema
- Reportes de producción inválidos

#### Problema 3: Relación entre `persons_X` y `units_per_hour`

**Pregunta Crítica:** ¿Cómo se relaciona el número de personas con la productividad?

**Escenario A: Productividad Proporcional**
```
persons_1 = 1  →  units_per_hour = 50
persons_2 = 2  →  units_per_hour = 100  (2x personas = 2x producción)
persons_3 = 3  →  units_per_hour = 150
```

**Escenario B: Productividad No-Lineal (Ley de Rendimientos Decrecientes)**
```
persons_1 = 1  →  units_per_hour = 50
persons_2 = 2  →  units_per_hour = 90   (no es 2x, hay overhead de coordinación)
persons_3 = 3  →  units_per_hour = 120
```

**Escenario C: Productividad Fija Independiente de Personas**
```
La parte ABC-123 siempre produce 100 units/hour en la Mesa #5,
independientemente de si hay 1, 2 o 3 personas asignadas.
```

**Estado Actual:** El campo `units_per_hour` es único por standard. NO hay relación explícita con configuraciones de personas.

**Consecuencia:** Si `persons_1`, `persons_2`, `persons_3` representan configuraciones alternativas, el sistema NO puede almacenar diferentes `units_per_hour` para cada una.

#### Problema 4: Falta de Validación de Reglas de Negocio

**Reglas Faltantes:**

1. **Al menos una configuración debe estar definida:**
   - ¿Es válido un standard sin `persons_1`, `persons_2`, `persons_3`?
   - Actualmente: SÍ (todos son `nullable`)

2. **Orden lógico de configuraciones:**
   - ¿Debe cumplirse `persons_1 < persons_2 < persons_3`?
   - Actualmente: NO se valida

3. **Configuración por defecto:**
   - ¿Cuál configuración usar si hay múltiples?
   - Actualmente: NO está definido

4. **Coherencia con tipo de estación:**
   - ¿Las máquinas requieren configuraciones diferentes a mesas manuales?
   - Actualmente: NO se considera

### 3. Análisis de Casos de Uso

#### Caso de Uso 1: Crear Standard para Mesa Manual

**Escenario:**
- Part: "CABLE-001"
- Estación: Mesa Manual M-03 (employees: 3)
- Productividad: 1 persona → 40 units/hour, 2 personas → 75 units/hour, 3 personas → 100 units/hour

**Problema Actual:**
```php
// ❌ El sistema solo permite UN valor de units_per_hour
Standard::create([
    'part_id' => 1,
    'work_table_id' => 3,
    'units_per_hour' => 100,  // ¿Para cuántas personas?
    'persons_1' => 1,
    'persons_2' => 2,
    'persons_3' => 3,
    // ...
]);
```

**Pregunta Sin Respuesta:** ¿El `units_per_hour = 100` corresponde a `persons_1`, `persons_2` o `persons_3`?

#### Caso de Uso 2: Calcular Capacidad de Producción

**Flujo del Spec 01:**

```php
// CapacityCalculatorService necesita saber:
// 1. ¿Cuántas personas están disponibles?
$availableEmployees = 2;

// 2. ¿Qué standard usar?
$standard = Standard::where('part_id', 1)
    ->where('active', true)
    ->first();

// 3. ¿Qué configuración de persons usar?
// ❌ PROBLEMA: No hay lógica para seleccionar persons_1 vs persons_2 vs persons_3
// ❌ PROBLEMA: units_per_hour no está diferenciado por configuración

// 4. Validar coherencia
if ($availableEmployees > $standard->getWorkstation()->employees) {
    // ❌ PROBLEMA: No hay validación
}
```

**Conclusión:** El diseño actual NO soporta adecuadamente el caso de uso de cálculo de capacidad.

#### Caso de Uso 3: Optimización de Asignación de Personal

**Objetivo:** Maximizar producción con personal disponible limitado.

**Escenario:**
- Disponible: 5 empleados
- Mesa M-01 (capacity: 3)
- Mesa M-02 (capacity: 2)
- Part ABC-123 puede producirse en cualquiera

**Problema:**
```php
// ¿Cómo decidir la asignación óptima?
// Si persons_1, persons_2, persons_3 representan configuraciones,
// necesitamos elegir la que maximize producción:

// Opción A: 3 personas en M-01 + 2 personas en M-02
// Opción B: 2 personas en M-01 + 3 personas en M-02
// Opción C: 5 personas en una sola mesa (inválido por capacity)

// ❌ PROBLEMA: No hay lógica para esta optimización
```

### 4. Evaluación de Datos Existentes

**Query de Verificación Necesaria:**

```sql
-- Verificar standards con configuraciones inconsistentes
SELECT
    s.id,
    s.part_id,
    p.number as part_number,
    s.work_table_id,
    s.semi_auto_work_table_id,
    s.machine_id,
    s.persons_1,
    s.persons_2,
    s.persons_3,
    s.units_per_hour,
    COALESCE(t.employees, sa.employees, m.employees) as station_capacity,
    CASE
        WHEN s.persons_1 > COALESCE(t.employees, sa.employees, m.employees) THEN 'persons_1 excede capacidad'
        WHEN s.persons_2 > COALESCE(t.employees, sa.employees, m.employees) THEN 'persons_2 excede capacidad'
        WHEN s.persons_3 > COALESCE(t.employees, sa.employees, m.employees) THEN 'persons_3 excede capacidad'
        ELSE 'OK'
    END as validation_status
FROM standards s
JOIN parts p ON s.part_id = p.id
LEFT JOIN tables t ON s.work_table_id = t.id
LEFT JOIN semi__automatics sa ON s.semi_auto_work_table_id = sa.id
LEFT JOIN machines m ON s.machine_id = m.id
WHERE s.deleted_at IS NULL
HAVING validation_status != 'OK';
```

**Riesgo:** Si esta query retorna resultados, hay datos inconsistentes que necesitan limpieza.

### 5. Resolución de Ambigüedad: `units_per_hour` y Configuraciones de Personas

**Problema Identificado:**
Según confirmación del usuario, cada configuración (`persons_1`, `persons_2`, `persons_3`) puede tener productividad diferente. Sin embargo, el diseño actual tiene UN solo campo `units_per_hour` por standard.

**Preguntas Críticas para Definir Implementación:**

#### Opción 1: `units_per_hour` es para configuración específica

```php
Standard {
    persons_1: 1,
    persons_2: 2,
    persons_3: 3,
    units_per_hour: 50,  // Solo aplica a persons_1
    default_persons_config: 'persons_1'
}
// ¿Cómo se almacena la productividad de persons_2 y persons_3?
// RESPUESTA: NO SE PUEDE con el diseño actual
```

#### Opción 2: `units_per_hour` puede ser NULL

```php
Standard {
    persons_1: 1,
    persons_2: 2,
    persons_3: 3,
    units_per_hour: NULL,  // No se usa este campo
    // Productividad se maneja por configuración en otra estructura
}
// PROBLEMA: Rompe validación actual de 'required'
```

#### Opción 3: `units_per_hour` es promedio o base

```php
Standard {
    persons_1: 1,
    persons_2: 2,
    persons_3: 3,
    units_per_hour: 100,  // Valor de referencia o promedio
    // Cada configuración puede ajustar este valor
}
// PROBLEMA: ¿Cómo se almacenan los ajustes por configuración?
```

**DECISIÓN ARQUITECTURAL REQUERIDA:**

Dado que Opción B (implementación inmediata) mantiene el campo único `units_per_hour`, y la Opción A (futuro) creará tabla `standard_configurations` con productividad diferenciada:

**¿Qué hacer con `units_per_hour` en Opción B?**

1. **Nullable + Migración Futura:**
   - Hacer `units_per_hour` nullable
   - Permitir standards SIN este campo si tienen configuraciones complejas
   - En Opción A, migrar a tabla separada
   - **VENTAJA:** Flexibilidad máxima
   - **DESVENTAJA:** Rompe lógica actual, cálculos de capacidad fallan

2. **Requerido + Configuración Default:**
   - Mantener `units_per_hour` requerido
   - Representa productividad de `default_persons_config`
   - Documentar limitación: otras configuraciones no tienen productividad almacenada
   - **VENTAJA:** Compatible con código actual
   - **DESVENTAJA:** Datos incompletos (solo 1 de 3 configuraciones tiene productividad)

3. **Requerido + Documentación de Limitación:**
   - Mantener como está
   - Documentar que es una limitación temporal
   - Usuarios deben entender que representa UN escenario (no todos)
   - **VENTAJA:** Mínimos cambios
   - **DESVENTAJA:** Ambigüedad persiste

**RECOMENDACIÓN TÉCNICA:**
**Opción 2 (Requerido + Configuración Default)** con migración planificada a Opción A en Fase 3.

**Justificación:**
- Mantiene compatibilidad con código existente (CapacityCalculatorService)
- Permite documentar claramente qué representa `units_per_hour`
- Facilita migración futura: en Opción A, este valor se convierte en la productividad de la configuración default
- Los usuarios pueden crear standards separados si necesitan productividades muy diferentes

#### Tabla Comparativa de Opciones para `units_per_hour`

| Aspecto | Opción 1: Nullable | Opción 2: Requerido + Default | Opción 3: Requerido + Limitación |
|---------|-------------------|-------------------------------|----------------------------------|
| **Compatibilidad con código actual** | ❌ Rompe cálculos | ✅ Total | ✅ Total |
| **Claridad semántica** | ⚠️ Media | ✅ Alta | ❌ Baja |
| **Complejidad de validación** | 🔴 Alta | 🟢 Baja | 🟢 Baja |
| **Migración a Opción A** | ✅ Directa | ✅ Directa | ⚠️ Requiere interpretación |
| **Riesgo de errores** | 🔴 Alto | 🟢 Bajo | 🟡 Medio |
| **Datos completos** | ⚠️ Algunos standards sin productividad | ⚠️ Solo config default tiene productividad | ⚠️ Ambiguo qué config tiene productividad |
| **Workaround para productividad diferenciada** | ❌ No hay | ✅ Crear múltiples standards | ⚠️ Crear múltiples standards (no documentado) |
| **Cambios requeridos en código** | 🔴 Muchos | 🟢 Mínimos | 🟢 Ninguno |
| **Tiempo de implementación** | 4-6 horas | 1-2 horas | 0 horas |

**DECISIÓN FINAL RECOMENDADA:** Opción 2 (Requerido + Configuración Default)

---

## Propuesta de Solución

### Decisión Arquitectónica: Dos Enfoques Posibles

#### Opción A: Configuraciones Múltiples (Recomendada)

**Concepto:** `persons_1`, `persons_2`, `persons_3` representan configuraciones alternativas de producción con productividades diferentes.

**Refactorización Requerida:**

1. **Nueva Tabla: `standard_configurations`**

```php
Schema::create('standard_configurations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('standard_id')->constrained()->onDelete('cascade');
    $table->integer('persons_required');
    $table->integer('units_per_hour');
    $table->boolean('is_default')->default(false);
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->index(['standard_id', 'persons_required']);
    $table->index(['standard_id', 'is_default']);
});
```

2. **Migración de Datos:**

```php
// Migrar datos existentes de standards
foreach (Standard::all() as $standard) {
    if ($standard->persons_1) {
        StandardConfiguration::create([
            'standard_id' => $standard->id,
            'persons_required' => $standard->persons_1,
            'units_per_hour' => $standard->units_per_hour,
            'is_default' => true,
        ]);
    }

    if ($standard->persons_2) {
        StandardConfiguration::create([
            'standard_id' => $standard->id,
            'persons_required' => $standard->persons_2,
            'units_per_hour' => calculateProductivity($standard, 2),
            'is_default' => false,
        ]);
    }

    // Similar para persons_3
}

// Eliminar columnas obsoletas
Schema::table('standards', function (Blueprint $table) {
    $table->dropColumn(['persons_1', 'persons_2', 'persons_3', 'units_per_hour']);
});
```

3. **Nuevo Modelo: `StandardConfiguration`**

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StandardConfiguration extends Model
{
    protected $fillable = [
        'standard_id',
        'persons_required',
        'units_per_hour',
        'is_default',
        'notes'
    ];

    protected $casts = [
        'persons_required' => 'integer',
        'units_per_hour' => 'integer',
        'is_default' => 'boolean',
    ];

    public function standard()
    {
        return $this->belongsTo(Standard::class);
    }

    /**
     * Scope para obtener configuración por defecto
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Validar que persons_required no excede capacidad de estación
     */
    public function validateCapacity(): bool
    {
        $workstation = $this->standard->getWorkstation();

        if (!$workstation) {
            return false;
        }

        return $this->persons_required <= $workstation->employees;
    }
}
```

4. **Actualizar Modelo `Standard`:**

```php
// Standard.php

public function configurations()
{
    return $this->hasMany(StandardConfiguration::class);
}

public function defaultConfiguration()
{
    return $this->hasOne(StandardConfiguration::class)->where('is_default', true);
}

/**
 * Obtener configuración óptima para número de empleados disponibles
 */
public function getOptimalConfiguration(int $availableEmployees): ?StandardConfiguration
{
    $workstation = $this->getWorkstation();

    if (!$workstation) {
        return null;
    }

    // No puede exceder capacidad de estación
    $maxEmployees = min($availableEmployees, $workstation->employees);

    // Buscar configuración con mayor productividad sin exceder disponibilidad
    return $this->configurations()
        ->where('persons_required', '<=', $maxEmployees)
        ->orderBy('units_per_hour', 'desc')
        ->first();
}

/**
 * Calcular horas requeridas considerando configuración óptima
 */
public function calculateRequiredHours(int $quantity, int $availableEmployees): array
{
    $config = $this->getOptimalConfiguration($availableEmployees);

    if (!$config) {
        throw new \RuntimeException(
            "No hay configuración disponible para {$availableEmployees} empleados"
        );
    }

    $hours = round($quantity / $config->units_per_hour, 2);

    return [
        'hours' => $hours,
        'configuration' => $config,
        'persons_used' => $config->persons_required,
        'productivity' => $config->units_per_hour,
    ];
}
```

**Ventajas:**
- Semántica clara y explícita
- Soporte para N configuraciones (no limitado a 3)
- Productividad diferenciada por configuración
- Coherencia forzada con capacidad de estación
- Fácil extensión futura

**Desventajas:**
- Requiere migración compleja de datos existentes
- Cambios breaking en código Livewire
- Tiempo de implementación: 2-3 días

---

#### Opción B: Validaciones Mejoradas (Pragmática)

**Concepto:** Mantener estructura actual pero agregar validaciones robustas y documentación clara.

**Cambios Requeridos:**

1. **Custom Validation Rule: `ValidWorkstationCapacity`**

```php
namespace App\Rules;

use App\Models\Machine;
use App\Models\Semi_Automatic;
use App\Models\Table;
use Illuminate\Contracts\Validation\Rule;

class ValidWorkstationCapacity implements Rule
{
    protected $workstationId;
    protected $workstationType; // 'table', 'semi_auto', 'machine'
    protected $personsFields;   // ['persons_1' => 1, 'persons_2' => 2, ...]
    protected $failedField;

    public function __construct($workstationId, $workstationType, array $personsFields)
    {
        $this->workstationId = $workstationId;
        $this->workstationType = $workstationType;
        $this->personsFields = $personsFields;
    }

    public function passes($attribute, $value)
    {
        // Si no hay estación asignada, no validar aún
        if (!$this->workstationId) {
            return true;
        }

        // Obtener capacidad de estación
        $workstation = $this->getWorkstation();

        if (!$workstation) {
            return false;
        }

        $capacity = $workstation->employees;

        // Validar cada configuración de personas
        foreach ($this->personsFields as $field => $persons) {
            if ($persons && $persons > $capacity) {
                $this->failedField = $field;
                return false;
            }
        }

        return true;
    }

    protected function getWorkstation()
    {
        return match($this->workstationType) {
            'table' => Table::find($this->workstationId),
            'semi_auto' => Semi_Automatic::find($this->workstationId),
            'machine' => Machine::find($this->workstationId),
            default => null,
        };
    }

    public function message()
    {
        return "El campo {$this->failedField} excede la capacidad de la estación seleccionada.";
    }
}
```

2. **Actualizar Validaciones en Livewire:**

```php
// StandardCreate.php / StandardEdit.php

protected function rules(): array
{
    return [
        'part_id' => 'required|exists:parts,id',
        'units_per_hour' => [
            'required',
            'integer',
            'min:1',
            'max:10000',
            // NOTA: ValidWorkstationCapacity NO se aplica aquí porque es solo warning
            // Se maneja en método validatePersonsCapacity() del modelo
        ],
        'work_table_id' => [
            'nullable',
            'exists:tables,id',
            new OnlyOneWorkstation($this->semi_auto_work_table_id, $this->machine_id),
        ],
        // ... resto igual
        'persons_1' => 'nullable|integer|min:1',
        'persons_2' => 'nullable|integer|min:1', // ❌ ELIMINADO: gte:persons_1
        'persons_3' => 'nullable|integer|min:1', // ❌ ELIMINADO: gte:persons_2
        'default_persons_config' => [
            'required',
            'in:persons_1,persons_2,persons_3',
            new DefaultConfigMustExist(
                $this->persons_1,
                $this->persons_2,
                $this->persons_3,
                $this->default_persons_config
            ),
        ],
        // ...
    ];
}

protected function messages(): array
{
    return [
        // ... mensajes existentes
        'default_persons_config.required' => 'Debe seleccionar una configuración por defecto.',
        'default_persons_config.in' => 'La configuración seleccionada no es válida.',
    ];
}

protected function getSelectedWorkstationId(): ?int
{
    return $this->work_table_id
        ?? $this->semi_auto_work_table_id
        ?? $this->machine_id;
}

protected function getSelectedWorkstationType(): ?string
{
    if ($this->work_table_id) return 'table';
    if ($this->semi_auto_work_table_id) return 'semi_auto';
    if ($this->machine_id) return 'machine';
    return null;
}
```

3. **Agregar Campo `default_persons_config` a tabla `standards`:**

```php
Schema::table('standards', function (Blueprint $table) {
    $table->enum('default_persons_config', ['persons_1', 'persons_2', 'persons_3'])
          ->default('persons_1')
          ->after('persons_3')
          ->comment('Configuración de personas a usar por defecto');
});
```

4. **Actualizar Modelo `Standard`:**

```php
protected $fillable = [
    // ... campos existentes
    'default_persons_config',
];

protected $casts = [
    // ... casts existentes
    'default_persons_config' => 'string',
];

/**
 * Obtener número de personas de la configuración por defecto
 */
public function getDefaultPersonsAttribute(): ?int
{
    return match($this->default_persons_config) {
        'persons_1' => $this->persons_1,
        'persons_2' => $this->persons_2,
        'persons_3' => $this->persons_3,
        default => $this->persons_1,
    };
}

/**
 * Calcular horas requeridas usando configuración específica
 */
public function calculateRequiredHoursWithConfig(
    int $quantity,
    string $config = null
): float
{
    $config = $config ?? $this->default_persons_config;

    if ($this->units_per_hour === 0) {
        throw new \DivisionByZeroError(
            "El estándar para la parte '{$this->part->number}' tiene units_per_hour = 0"
        );
    }

    // NOTE: En esta opción, units_per_hour es único para el standard,
    // no diferenciado por configuración de personas
    return round($quantity / $this->units_per_hour, 2);
}

/**
 * Validar coherencia de configuración de personas con capacidad de estación
 * MODIFICADO: Retorna warnings, no errors (validación superficial)
 */
public function validatePersonsCapacity(): array
{
    $workstation = $this->getWorkstation();

    if (!$workstation) {
        return [
            'has_warnings' => false,
            'warnings' => [],
            'capacity' => null,
        ];
    }

    $capacity = $workstation->employees;
    $warnings = [];

    if ($this->persons_1 && $this->persons_1 > $capacity) {
        $warnings[] = "⚠️ Configuración 1 ({$this->persons_1} personas) excede capacidad de estación ({$capacity})";
    }

    if ($this->persons_2 && $this->persons_2 > $capacity) {
        $warnings[] = "⚠️ Configuración 2 ({$this->persons_2} personas) excede capacidad de estación ({$capacity})";
    }

    if ($this->persons_3 && $this->persons_3 > $capacity) {
        $warnings[] = "⚠️ Configuración 3 ({$this->persons_3} personas) excede capacidad de estación ({$capacity})";
    }

    return [
        'has_warnings' => !empty($warnings),
        'warnings' => $warnings,
        'capacity' => $capacity,
    ];
}
```

5. **Actualizar Vista Blade:**

```blade
{{-- standard-create.blade.php --}}

<!-- Persons 1, 2, 3 -->
<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Configuraciones de Personal
        <span class="text-gray-500 text-xs">(Personas requeridas para producción)</span>
    </label>

    {{-- ADVERTENCIA CRÍTICA sobre productividad única --}}
    <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-3">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-yellow-800 mb-1">
                    IMPORTANTE: Limitación de Productividad Única
                </p>
                <p class="text-sm text-yellow-700">
                    El campo <strong>Units per Hour</strong> es único para todo el standard.
                    Si define múltiples configuraciones (persons_1, persons_2, persons_3),
                    el valor de Units per Hour aplicará SOLO a la <strong>configuración por defecto</strong>
                    seleccionada abajo.
                </p>
                <p class="text-xs text-yellow-600 mt-2">
                    Si cada configuración tiene productividad diferente, deberá crear standards separados
                    para la misma parte. Esta limitación se resolverá en futuras versiones.
                </p>
            </div>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">
        <p class="text-sm text-blue-800">
            <strong>Nota:</strong> Defina diferentes configuraciones de personal para esta parte.
            El sistema usará la configuración por defecto para cálculos de capacidad.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label for="persons_1" class="block text-sm font-medium text-gray-700 mb-1">
                Configuración 1 (Estándar)
            </label>
            <input wire:model="persons_1" id="persons_1" type="number" min="1"
                   placeholder="Ej: 1"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
            @error('persons_1')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="persons_2" class="block text-sm font-medium text-gray-700 mb-1">
                Configuración 2 (Alternativa)
            </label>
            <input wire:model="persons_2" id="persons_2" type="number" min="1"
                   placeholder="Ej: 2"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
            @error('persons_2')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="persons_3" class="block text-sm font-medium text-gray-700 mb-1">
                Configuración 3 (Alternativa)
            </label>
            <input wire:model="persons_3" id="persons_3" type="number" min="1"
                   placeholder="Ej: 3"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
            @error('persons_3')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Selector de configuración por defecto --}}
    <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Configuración por Defecto
            <span class="text-red-600">*</span>
        </label>
        <select wire:model="default_persons_config"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            <option value="persons_1">Configuración 1</option>
            <option value="persons_2">Configuración 2</option>
            <option value="persons_3">Configuración 3</option>
        </select>
        <p class="text-xs text-gray-500 mt-1">
            Esta configuración se usará por defecto en cálculos de capacidad y
            determinará a qué configuración aplica el campo Units per Hour.
        </p>
        @error('default_persons_config')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Warning dinámico si hay múltiples configuraciones --}}
    @if(($persons_1 && $persons_2) || ($persons_2 && $persons_3) || ($persons_1 && $persons_3))
        <div class="mt-3 bg-orange-50 border border-orange-200 rounded-lg p-3">
            <p class="text-sm text-orange-800">
                <strong>Advertencia:</strong> Ha definido múltiples configuraciones.
                Recuerde que Units per Hour ({{ $units_per_hour ?? 'sin definir' }})
                aplicará SOLO a la configuración <strong>{{ $default_persons_config }}</strong>.
            </p>
        </div>
    @endif
</div>
```

**Ventajas:**
- Implementación rápida (6-8 horas)
- Compatibilidad con datos existentes
- No rompe código existente
- Validaciones robustas

**Desventajas:**
- No resuelve el problema fundamental de productividad diferenciada
- `units_per_hour` sigue siendo único (no por configuración)
- Semántica aún ambigua
- Limitado a 3 configuraciones

---

### Recomendación Final (ACTUALIZADA)

**Para Implementación INMEDIATA (esta semana):**
→ **Opción B** (Validaciones Mejoradas) - **CON ADVERTENCIAS, NO BLOQUEANTES**

**Razones:**
1. Bajo riesgo de breaking changes
2. Tiempo de implementación corto (1 día)
3. Mejora significativa sobre estado actual
4. Compatible con migración futura a Opción A

**CAMBIOS IMPORTANTES en Opción B:**
- Validaciones de capacidad son WARNINGS, no errores bloqueantes (confirmado por usuario: "es superficial")
- Validaciones de orden (`persons_2 >= persons_1`) ELIMINADAS (usuario confirmó que no es obligatorio)
- Documentación CLARA de la limitación de productividad única

**Para Roadmap MEDIANO PLAZO (6-8 semanas después de Opción B):**
→ **Opción A** (Configuraciones Múltiples) - **PRIORIDAD CRÍTICA**

**Razones ACTUALIZADAS:**
1. Diseño arquitecturalmente superior
2. **CRÍTICO:** Soporte para productividad diferenciada (confirmado por usuario: "cada uno tiene una cantidad diferente")
3. Escalabilidad a N configuraciones
4. Productividad diferenciada correctamente modelada
5. **IMPACTO DE NEGOCIO:** Sin Opción A, el sistema NO puede almacenar correctamente las productividades reales

**TIMELINE RECOMENDADO:**
- **Semana 1:** Implementar Opción B
- **Semanas 2-4:** Implementar Capacity Calculator con limitaciones documentadas
- **Semanas 5-6:** Diseñar e implementar Opción A (tabla `standard_configurations`)
- **Semana 7:** Migración de datos de Opción B a Opción A
- **Semana 8:** Testing y validación

**NOTA IMPORTANTE:** La limitación de productividad única en Opción B puede generar workarounds temporales (crear múltiples standards para la misma parte). Es fundamental comunicar a los usuarios que esta es una solución temporal.

---

## Impacto Arquitectural

### Backend

#### Cambios en Modelos

**`Standard.php` (modificado):**
- Agregar campo `default_persons_config`
- Agregar método `getDefaultPersonsAttribute()`
- Agregar método `validatePersonsCapacity()`
- Actualizar método `calculateRequiredHours()` (si es necesario)

**`Table.php`, `Semi_Automatic.php`, `Machine.php` (sin cambios):**
- Mantener campo `employees` (capacidad)

#### Nuevas Validaciones

**`ValidWorkstationCapacity.php` (nueva):**
- Validar coherencia de `persons_X` con capacidad de estación
- Validación transversal a múltiples campos

#### Servicios

**`CapacityCalculatorService.php` (actualización futura):**
```php
/**
 * Calcular horas requeridas considerando configuración de personas
 */
public function calculateRequiredHours(
    Standard $standard,
    int $quantity,
    ?int $availableEmployees = null
): CapacityResult
{
    // Si se especifican empleados disponibles, validar coherencia
    if ($availableEmployees !== null) {
        $defaultPersons = $standard->default_persons;

        if ($defaultPersons > $availableEmployees) {
            throw new InsufficientEmployeesException(
                "La configuración requiere {$defaultPersons} personas, pero solo hay {$availableEmployees} disponibles"
            );
        }
    }

    $hours = $standard->calculateRequiredHoursWithConfig($quantity);

    return new CapacityResult([
        'hours_required' => $hours,
        'persons_required' => $standard->default_persons,
        'units_per_hour' => $standard->units_per_hour,
        'configuration_used' => $standard->default_persons_config,
    ]);
}
```

### Frontend

#### Componentes Livewire

**`StandardCreate.php` / `StandardEdit.php` (modificados):**
- Agregar propiedad `public string $default_persons_config = 'persons_1'`
- Actualizar `rules()` con validaciones mejoradas
- Agregar métodos helper `getSelectedWorkstationId()` y `getSelectedWorkstationType()`
- Actualizar `saveStandard()` / `updateStandard()` para incluir `default_persons_config`

#### Vistas Blade

**`standard-create.blade.php` / `standard-edit.blade.php` (modificadas):**
- Mejorar sección de "Configuraciones de Personal"
- Agregar selector de configuración por defecto
- Agregar tooltips explicativos
- Mejorar mensajes de error

### Base de Datos

#### Migración: `add_default_persons_config_to_standards_table.php`

```php
public function up(): void
{
    Schema::table('standards', function (Blueprint $table) {
        $table->enum('default_persons_config', ['persons_1', 'persons_2', 'persons_3'])
              ->default('persons_1')
              ->after('persons_3')
              ->comment('Configuración de personas a usar por defecto');
    });

    // Actualizar registros existentes: usar persons_1 como default
    DB::table('standards')->update([
        'default_persons_config' => 'persons_1'
    ]);
}

public function down(): void
{
    Schema::table('standards', function (Blueprint $table) {
        $table->dropColumn('default_persons_config');
    });
}
```

### Testing

#### Nuevos Tests Requeridos

**`ValidWorkstationCapacityTest.php` (Unit Test):**
```php
class ValidWorkstationCapacityTest extends TestCase
{
    /** @test */
    public function it_validates_persons_do_not_exceed_table_capacity()
    {
        $table = Table::factory()->create(['employees' => 2]);

        $rule = new ValidWorkstationCapacity($table->id, 'table', [
            'persons_1' => 1,
            'persons_2' => 2,
            'persons_3' => 3, // Excede capacidad
        ]);

        $this->assertFalse($rule->passes('units_per_hour', 100));
    }

    /** @test */
    public function it_allows_persons_within_capacity()
    {
        $table = Table::factory()->create(['employees' => 5]);

        $rule = new ValidWorkstationCapacity($table->id, 'table', [
            'persons_1' => 1,
            'persons_2' => 3,
            'persons_3' => 5,
        ]);

        $this->assertTrue($rule->passes('units_per_hour', 100));
    }
}
```

**`StandardPersonsValidationTest.php` (Feature Test):**
```php
class StandardPersonsValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_rejects_persons_2_less_than_persons_1()
    {
        $part = Part::factory()->create();
        $table = Table::factory()->create(['employees' => 3]);

        Livewire::test(StandardCreate::class)
            ->set('part_id', $part->id)
            ->set('work_table_id', $table->id)
            ->set('units_per_hour', 100)
            ->set('persons_1', 2)
            ->set('persons_2', 1) // ❌ Menor que persons_1
            ->call('saveStandard')
            ->assertHasErrors(['persons_2']);
    }

    /** @test */
    public function it_rejects_persons_exceeding_workstation_capacity()
    {
        $part = Part::factory()->create();
        $table = Table::factory()->create(['employees' => 2]);

        Livewire::test(StandardCreate::class)
            ->set('part_id', $part->id)
            ->set('work_table_id', $table->id)
            ->set('units_per_hour', 100)
            ->set('persons_1', 1)
            ->set('persons_2', 2)
            ->set('persons_3', 5) // ❌ Excede capacidad (2)
            ->call('saveStandard')
            ->assertHasErrors(['units_per_hour']); // Error en validación custom
    }
}
```

---

## Plan de Implementación

### Fase 1: Preparación y Análisis (2 horas)

#### Paso 1.1: Verificar Datos Existentes
```bash
# Ejecutar query de verificación
php artisan tinker
```

```php
// Verificar standards con configuraciones inconsistentes
$inconsistent = DB::select("
    SELECT
        s.id,
        s.part_id,
        s.persons_1,
        s.persons_2,
        s.persons_3,
        COALESCE(t.employees, sa.employees, m.employees) as station_capacity
    FROM standards s
    LEFT JOIN tables t ON s.work_table_id = t.id
    LEFT JOIN semi__automatics sa ON s.semi_auto_work_table_id = sa.id
    LEFT JOIN machines m ON s.machine_id = m.id
    WHERE s.deleted_at IS NULL
        AND (
            (s.persons_1 > COALESCE(t.employees, sa.employees, m.employees)) OR
            (s.persons_2 > COALESCE(t.employees, sa.employees, m.employees)) OR
            (s.persons_3 > COALESCE(t.employees, sa.employees, m.employees))
        )
");

dd($inconsistent);
```

**Acción:** Si hay inconsistencias, limpiar datos antes de continuar.

#### Paso 1.2: Documentar Hallazgos
- Crear documento con estadísticas de datos actuales
- Identificar patrones de uso de `persons_1`, `persons_2`, `persons_3`
- Validar con stakeholders el significado de estos campos

#### Paso 1.3: Validar Semántica de `units_per_hour`
**Tiempo:** 30 minutos

**Objetivo:** Confirmar con usuarios cómo se usa actualmente `units_per_hour`

**Preguntas a Validar:**
1. Cuando un standard tiene `persons_1`, `persons_2`, `persons_3` definidos, ¿qué representa el campo `units_per_hour`?
2. ¿Existen standards donde la productividad varía significativamente entre configuraciones?
3. ¿Es aceptable que `units_per_hour` sea NULL en algunos casos?

**Acción:**
- Revisar 5-10 standards existentes con múltiples configuraciones
- Documentar patrones de uso actual
- Confirmar decisión: Opción 2 (Requerido + Config Default) vs Opción 1 (Nullable)

**Query de Análisis:**
```sql
-- Encontrar standards con múltiples configuraciones de personas
SELECT
    s.id,
    p.number as part_number,
    s.persons_1,
    s.persons_2,
    s.persons_3,
    s.units_per_hour,
    CASE
        WHEN s.persons_1 IS NOT NULL AND s.persons_2 IS NOT NULL AND s.persons_3 IS NOT NULL THEN '3 configuraciones'
        WHEN (s.persons_1 IS NOT NULL AND s.persons_2 IS NOT NULL) OR
             (s.persons_2 IS NOT NULL AND s.persons_3 IS NOT NULL) THEN '2 configuraciones'
        WHEN s.persons_1 IS NOT NULL OR s.persons_2 IS NOT NULL OR s.persons_3 IS NOT NULL THEN '1 configuración'
        ELSE 'Sin configuraciones'
    END as config_count
FROM standards s
JOIN parts p ON s.part_id = p.id
WHERE s.deleted_at IS NULL
ORDER BY config_count DESC, s.id
LIMIT 10;
```

**Resultado Esperado:** Confirmar que Opción 2 (Requerido + Configuración Default) es la adecuada.

---

### Fase 2: Implementación Core (4 horas)

#### Paso 2.1: Crear Custom Validation Rule (MODIFICADO)
**Tiempo:** 30 minutos

**CAMBIO IMPORTANTE:** Esta validación NO debe ser bloqueante (error), debe ser informativa (warning).

```bash
php artisan make:rule ValidWorkstationCapacity
```

**Archivo:** `C:\xampp\htdocs\flexcon-tracker\app\Rules\ValidWorkstationCapacity.php`

**IMPLEMENTACIÓN MODIFICADA:**

En lugar de retornar `false` en el método `passes()`, esta regla debe:
1. SIEMPRE retornar `true` (permitir que pase la validación)
2. Almacenar advertencias en una propiedad del componente Livewire
3. Mostrar warnings en la UI (no errores bloqueantes)

**ALTERNATIVA RECOMENDADA:** Implementar validación "after" que agrega mensajes de advertencia sin bloquear el guardado.

**Código Sugerido:**

```php
namespace App\Rules;

use App\Models\Machine;
use App\Models\Semi_Automatic;
use App\Models\Table;
use Illuminate\Contracts\Validation\Rule;

class ValidWorkstationCapacity implements Rule
{
    protected $workstationId;
    protected $workstationType;
    protected $personsFields;
    protected $warnings = [];

    public function __construct($workstationId, $workstationType, array $personsFields)
    {
        $this->workstationId = $workstationId;
        $this->workstationType = $workstationType;
        $this->personsFields = $personsFields;
    }

    public function passes($attribute, $value)
    {
        // Si no hay estación asignada, pasar validación
        if (!$this->workstationId) {
            return true;
        }

        $workstation = $this->getWorkstation();

        if (!$workstation) {
            return true; // No bloquear si no se encuentra estación
        }

        $capacity = $workstation->employees;

        // Verificar cada configuración (solo genera warnings)
        foreach ($this->personsFields as $field => $persons) {
            if ($persons && $persons > $capacity) {
                $this->warnings[] = "⚠️ {$field} ({$persons}) excede la capacidad de la estación ({$capacity})";
            }
        }

        // SIEMPRE retornar true (no bloquear guardado)
        return true;
    }

    protected function getWorkstation()
    {
        return match($this->workstationType) {
            'table' => Table::find($this->workstationId),
            'semi_auto' => Semi_Automatic::find($this->workstationId),
            'machine' => Machine::find($this->workstationId),
            default => null,
        };
    }

    public function message()
    {
        // Este mensaje solo se usa si passes() retorna false
        // Como siempre retorna true, no se mostrará
        return 'Advertencia de capacidad de estación';
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
```

**NOTA:** En el componente Livewire, deberás capturar los warnings después de la validación y mostrarlos en la UI.

#### Paso 2.2: Crear Migración
**Tiempo:** 15 minutos

```bash
php artisan make:migration add_default_persons_config_to_standards_table
```

**Archivo:** `database/migrations/YYYY_MM_DD_HHMMSS_add_default_persons_config_to_standards_table.php`

Copiar código de migración de la sección anterior.

#### Paso 2.3: Actualizar Modelo Standard
**Tiempo:** 30 minutos

**Archivo:** `C:\xampp\htdocs\flexcon-tracker\app\Models\Standard.php`

1. Agregar `default_persons_config` a `$fillable`
2. Agregar cast para `default_persons_config`
3. Agregar métodos `getDefaultPersonsAttribute()` y `validatePersonsCapacity()`

#### Paso 2.4: Actualizar Componente StandardCreate
**Tiempo:** 1 hora

**Archivo:** `C:\xampp\htdocs\flexcon-tracker\app\Livewire\Admin\Standards\StandardCreate.php`

1. Agregar propiedad `public string $default_persons_config = 'persons_1'`
2. Actualizar `rules()` con:
   - Validación `persons_2 >= persons_1`
   - Validación `persons_3 >= persons_2`
   - `ValidWorkstationCapacity` custom rule
3. Agregar métodos helper `getSelectedWorkstationId()` y `getSelectedWorkstationType()`
4. Actualizar `saveStandard()` para incluir `default_persons_config`

#### Paso 2.5: Actualizar Componente StandardEdit
**Tiempo:** 45 minutos

**Archivo:** `C:\xampp\htdocs\flexcon-tracker\app\Livewire\Admin\Standards\StandardEdit.php`

Aplicar mismos cambios que StandardCreate, más:
- Actualizar `mount()` para cargar `default_persons_config`

#### Paso 2.6: Actualizar Vistas Blade
**Tiempo:** 1 hora

**Archivos:**
- `resources/views/livewire/admin/standards/standard-create.blade.php`
- `resources/views/livewire/admin/standards/standard-edit.blade.php`

Copiar markup de la sección "Opción B" anterior.

#### Paso 2.7: Actualizar Validación de `units_per_hour` y Crear Regla `DefaultConfigMustExist`
**Tiempo:** 30 minutos

**Decisión Basada en Paso 1.3:**

**Si Opción 2 (Requerido + Config Default) - RECOMENDADO:**

```php
// StandardCreate.php / StandardEdit.php

protected function rules(): array
{
    return [
        // ... otros campos
        'units_per_hour' => [
            'required',  // Mantener requerido
            'integer',
            'min:1',
            'max:10000',
        ],
        'default_persons_config' => [
            'required',
            'in:persons_1,persons_2,persons_3',
            new DefaultConfigMustExist(
                $this->persons_1,
                $this->persons_2,
                $this->persons_3,
                $this->default_persons_config
            ),
        ],
    ];
}
```

**Nueva Regla:** `DefaultConfigMustExist`

```bash
php artisan make:rule DefaultConfigMustExist
```

**Archivo:** `C:\xampp\htdocs\flexcon-tracker\app\Rules\DefaultConfigMustExist.php`

```php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DefaultConfigMustExist implements Rule
{
    protected $persons1;
    protected $persons2;
    protected $persons3;
    protected $defaultConfig;

    public function __construct($persons1, $persons2, $persons3, $defaultConfig)
    {
        $this->persons1 = $persons1;
        $this->persons2 = $persons2;
        $this->persons3 = $persons3;
        $this->defaultConfig = $defaultConfig;
    }

    public function passes($attribute, $value)
    {
        // Si default_persons_config = 'persons_2',
        // entonces $this->persons_2 debe estar definido
        return match($this->defaultConfig) {
            'persons_1' => $this->persons1 !== null && $this->persons1 !== '',
            'persons_2' => $this->persons2 !== null && $this->persons2 !== '',
            'persons_3' => $this->persons3 !== null && $this->persons3 !== '',
            default => false,
        };
    }

    public function message()
    {
        return 'La configuración por defecto seleccionada debe tener un valor definido.';
    }
}
```

**Si Opción 1 (Nullable) - ALTERNATIVA:**

```php
protected function rules(): array
{
    return [
        // ... otros campos
        'units_per_hour' => [
            'nullable',  // Permitir NULL
            'integer',
            'min:1',
            'max:10000',
            new RequiredIfNoConfigurations(
                $this->persons_1,
                $this->persons_2,
                $this->persons_3
            ),
        ],
    ];
}
```

**RECOMENDACIÓN:** Usar Opción 2 (Requerido + Config Default) por compatibilidad con código existente.

---

### Fase 3: Testing (2 horas)

#### Paso 3.1: Ejecutar Migración
```bash
php artisan migrate
```

Verificar sin errores.

#### Paso 3.2: Testing Manual - Crear Standard

**Escenario 1: Configuración Válida**
1. Navegar a `/admin/standards/create`
2. Seleccionar parte
3. Seleccionar Mesa Manual con `employees = 3`
4. Ingresar:
   - `units_per_hour`: 100
   - `persons_1`: 1
   - `persons_2`: 2
   - `persons_3`: 3
   - `default_persons_config`: persons_2
5. Guardar
6. ✅ Verificar guardado exitoso

**Escenario 2: Validación de Capacidad**
1. Navegar a `/admin/standards/create`
2. Seleccionar parte
3. Seleccionar Mesa Manual con `employees = 2`
4. Ingresar:
   - `units_per_hour`: 100
   - `persons_1`: 1
   - `persons_2`: 2
   - `persons_3`: 5  // ❌ Excede capacidad
5. Intentar guardar
6. ✅ Verificar mensaje de error

**Escenario 3: Validación de Orden**
1. Navegar a `/admin/standards/create`
2. Seleccionar parte
3. Seleccionar estación
4. Ingresar:
   - `units_per_hour`: 100
   - `persons_1`: 3
   - `persons_2`: 2  // ❌ Menor que persons_1
5. Intentar guardar
6. ✅ Verificar mensaje de error

#### Paso 3.3: Testing Manual - Editar Standard

Repetir escenarios anteriores en modo edición.

#### Paso 3.4: Testing en Tinker

```php
php artisan tinker

$standard = Standard::first();

// Probar método validatePersonsCapacity()
$validation = $standard->validatePersonsCapacity();
dump($validation);

// Probar accessor getDefaultPersonsAttribute()
dump($standard->default_persons);

// Probar cambio de configuración
$standard->default_persons_config = 'persons_2';
dump($standard->default_persons);
```

#### Paso 3.4.1: Testing de `units_per_hour` y Configuraciones

**Escenarios a Probar:**

**1. Standard con configuración única:**
```php
use App\Models\Standard;
use App\Models\Part;
use App\Models\Table;

$part = Part::first();
$table = Table::first();

$standard = Standard::create([
    'part_id' => $part->id,
    'work_table_id' => $table->id,
    'persons_1' => 2,
    'persons_2' => null,
    'persons_3' => null,
    'units_per_hour' => 100,
    'default_persons_config' => 'persons_1',
    'active' => true,
]);

// ✅ Debe guardarse correctamente
dump('Standard creado: ' . $standard->id);
dump('Default persons: ' . $standard->default_persons); // Debe ser 2
```

**2. Standard con múltiples configuraciones:**
```php
$standard = Standard::create([
    'part_id' => $part->id,
    'work_table_id' => $table->id,
    'persons_1' => 1,
    'persons_2' => 2,
    'persons_3' => 3,
    'units_per_hour' => 50,  // Para persons_1 (default)
    'default_persons_config' => 'persons_1',
    'active' => true,
]);

// ✅ Debe guardarse
// ⚠️ ADVERTENCIA: persons_2 y persons_3 NO tienen productividad definida
dump('Standard con múltiples configs creado: ' . $standard->id);
dump('Units per hour aplica solo a: ' . $standard->default_persons_config);
```

**3. Default config sin valor (debe fallar):**
```php
try {
    $standard = Standard::create([
        'part_id' => $part->id,
        'work_table_id' => $table->id,
        'persons_1' => 1,
        'persons_2' => null,
        'persons_3' => null,
        'units_per_hour' => 100,
        'default_persons_config' => 'persons_2',  // ❌ persons_2 es NULL
        'active' => true,
    ]);
} catch (\Exception $e) {
    dump('❌ Error esperado: ' . $e->getMessage());
    // Debe fallar con error de validación DefaultConfigMustExist
}
```

**4. Testing de warnings de capacidad:**
```php
$tableSmall = Table::factory()->create(['employees' => 2]);

$standard = Standard::create([
    'part_id' => $part->id,
    'work_table_id' => $tableSmall->id,
    'persons_1' => 1,
    'persons_2' => 2,
    'persons_3' => 5,  // ⚠️ Excede capacidad (2)
    'units_per_hour' => 100,
    'default_persons_config' => 'persons_1',
    'active' => true,
]);

// ✅ Debe guardarse (warning, no error)
dump('Standard con warning de capacidad: ' . $standard->id);
$validation = $standard->validatePersonsCapacity();
dump($validation); // Debe mostrar warning pero valid = true (o warning específico)
```

#### Paso 3.5: Crear Tests Automatizados

**Crear archivos:**
```bash
php artisan make:test Rules/ValidWorkstationCapacityTest --unit
php artisan make:test Standards/StandardPersonsValidationTest
```

Copiar código de tests de la sección anterior.

**Ejecutar:**
```bash
php artisan test --filter=ValidWorkstationCapacity
php artisan test --filter=StandardPersonsValidation
```

---

### Fase 4: Documentación y Cleanup (1 hora)

#### Paso 4.1: Actualizar Documentación (EXPANDIDO)

**Archivo:** `C:\xampp\htdocs\flexcon-tracker\Diagramas_flujo\Estructura\docs\standards_usage_guide.md`

```markdown
# Guía de Uso: Standards - Configuraciones de Personal

## Concepto

Un **Standard** define cómo se produce una parte en una estación específica.
Los campos `persons_1`, `persons_2`, `persons_3` representan **configuraciones alternativas**
de personal que pueden utilizarse para producir la misma parte.

## Configuraciones de Personal

### ¿Qué son?

Cada configuración especifica cuántas personas se requieren para producir la parte.
El campo `units_per_hour` representa la productividad del standard (actualmente única,
no diferenciada por configuración).

### Ejemplo

```
Part: CABLE-ABC-001
Estación: Mesa Manual M-05 (capacity: 3 employees)

Configuración 1 (Estándar):     persons_1 = 1
Configuración 2 (Alternativa):  persons_2 = 2
Configuración 3 (Alternativa):  persons_3 = 3

units_per_hour: 100  (aplica solo a configuración default)
default_persons_config: 'persons_1'
```

### Configuración por Defecto

El campo `default_persons_config` indica cuál configuración usar en cálculos de capacidad.
Valores posibles: `persons_1`, `persons_2`, `persons_3`.

## Limitación Actual: Productividad Única

**IMPORTANTE:** En la implementación actual (Opción B), el campo `units_per_hour`
es ÚNICO por standard, aunque existan múltiples configuraciones de personas.

### ¿Qué significa esto?

Cuando un standard tiene:
```php
persons_1: 1
persons_2: 2
persons_3: 3
units_per_hour: 50
default_persons_config: 'persons_1'
```

- El valor `units_per_hour = 50` representa la productividad de la configuración DEFAULT (`persons_1`)
- Las configuraciones `persons_2` y `persons_3` NO tienen productividad almacenada en el sistema
- Para cálculos de capacidad, se usará siempre `units_per_hour` del standard

### ¿Cómo afecta esto?

**En Planificación de Producción:**
- El sistema solo podrá calcular capacidad con la configuración default
- Si se asignan 2 personas (persons_2), el cálculo usará productividad de persons_1 (INCORRECTO)

**Workaround Temporal:**
- Crear standards separados para cada configuración de personas si la productividad varía significativamente
- Ejemplo:
  ```
  Standard #1: part_id=1, persons_1=1, units_per_hour=50, default_persons_config='persons_1'
  Standard #2: part_id=1, persons_1=2, units_per_hour=90, default_persons_config='persons_1'
  Standard #3: part_id=1, persons_1=3, units_per_hour=120, default_persons_config='persons_1'
  ```

**IMPORTANTE:** Aunque esto genera datos duplicados (3 standards para la misma parte),
es la única forma de almacenar productividades diferentes hasta que se implemente Opción A.

### Solución Futura (Fase 3)

La migración a **Opción A** (tabla `standard_configurations`) resolverá esta limitación:
- Cada configuración tendrá su propio `units_per_hour`
- Cálculos de capacidad serán precisos para cualquier número de personas
- Soporte para N configuraciones (no limitado a 3)

**Fecha Planificada:** 6-8 semanas después de la implementación de Opción B
**Prioridad:** CRÍTICA (confirmado por usuario: "cada uno tiene una cantidad diferente")

## Reglas de Validación

1. **Capacidad de Estación (ADVERTENCIA, NO BLOQUEANTE):** Si una configuración excede
   la capacidad (`employees`) de la estación asignada, se mostrará una advertencia pero
   NO se bloqueará el guardado. Esto es por diseño, ya que la validación es superficial.

2. **Orden Lógico:** NO se fuerza ningún orden entre `persons_1`, `persons_2`, `persons_3`.
   Las configuraciones pueden tener cualquier valor.

3. **Al menos una configuración:** Se recomienda definir al menos `persons_1`.

4. **Configuración default debe existir:** La configuración seleccionada como default
   DEBE tener un valor definido. Esto es una validación BLOQUEANTE.

## Uso en Cálculos de Capacidad

```php
$standard = Standard::find(1);

// Obtener personas de configuración por defecto
$requiredPersons = $standard->default_persons;

// Validar coherencia (genera warnings, no errores)
$validation = $standard->validatePersonsCapacity();
if (!empty($validation['warnings'])) {
    // Mostrar warnings al usuario
    foreach ($validation['warnings'] as $warning) {
        echo $warning;
    }
}

// Calcular horas requeridas (usa units_per_hour del standard)
$hours = $standard->calculateRequiredHours(500);
```

## Notas Importantes

- **Productividad Única:** Actualmente, `units_per_hour` es único por standard,
  NO diferenciado por configuración de personas.

- **Workaround Requerido:** Para partes con productividades muy diferentes entre
  configuraciones, crear standards separados (ver sección "Limitación Actual").

- **Evolución Futura:** En 6-8 semanas, se implementará un sistema de configuraciones
  múltiples con productividad diferenciada (ver Spec 04 - Opción A).
```

#### Paso 4.2: Actualizar Seeders

**Archivo:** `database/seeders/StandardSeeder.php`

Actualizar para incluir `default_persons_config`:

```php
Standard::create([
    'part_id' => 1,
    'work_table_id' => 1,
    'units_per_hour' => 100,
    'persons_1' => 1,
    'persons_2' => 2,
    'persons_3' => null,
    'default_persons_config' => 'persons_1', // ✅ Nuevo campo
    'effective_date' => now(),
    'active' => true,
]);
```

#### Paso 4.3: Verificación Final

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Verificar migración
php artisan migrate:status

# Verificar tests
php artisan test

# Verificar estructura de tabla
php artisan tinker
>>> Schema::hasColumn('standards', 'default_persons_config');
>>> true
```

---

### Checklist de Implementación (ACTUALIZADO)

- [ ] **Fase 1: Preparación**
  - [ ] Query de verificación de datos ejecutada
  - [ ] Inconsistencias documentadas y resueltas
  - [ ] **NUEVO:** Semántica de `units_per_hour` validada con usuarios
  - [ ] **NUEVO:** Decisión sobre nullable vs requerido documentada
  - [ ] **NUEVO:** Análisis de impacto de productividad única completado
  - [ ] **NUEVO:** Query de análisis de standards con múltiples configuraciones ejecutada

- [ ] **Fase 2: Implementación**
  - [ ] **MODIFICADO:** `ValidWorkstationCapacity` implementada como WARNING (no bloqueante)
    - [ ] Método `passes()` siempre retorna `true`
    - [ ] Método `getWarnings()` implementado
    - [ ] Componente Livewire captura warnings
  - [ ] Migración `add_default_persons_config` creada
  - [ ] Modelo `Standard` actualizado:
    - [ ] `default_persons_config` en `$fillable`
    - [ ] Cast agregado
    - [ ] Métodos `getDefaultPersonsAttribute()` agregado
    - [ ] **MODIFICADO:** Método `validatePersonsCapacity()` retorna warnings, no errors
  - [ ] **NUEVO:** Regla `DefaultConfigMustExist` creada
  - [ ] `StandardCreate` actualizado:
    - [ ] Propiedad `default_persons_config` agregada
    - [ ] **MODIFICADO:** Validaciones sin orden forzado (persons_2 >= persons_1 ELIMINADO)
    - [ ] Métodos helper agregados
    - [ ] **NUEVO:** Sistema de warnings para validaciones suaves
    - [ ] **NUEVO:** Validación `DefaultConfigMustExist` integrada
  - [ ] `StandardEdit` actualizado (igual que Create)
  - [ ] Vista `standard-create.blade.php` actualizada:
    - [ ] **NUEVO:** Sección de advertencia sobre productividad única
    - [ ] Selector de configuración default
    - [ ] **NUEVO:** Warning visual cuando hay múltiples configuraciones
  - [ ] Vista `standard-edit.blade.php` actualizada
  - [ ] Migración ejecutada sin errores

- [ ] **Fase 3: Testing**
  - [ ] Testing manual - Crear standard válido
  - [ ] **MODIFICADO:** Testing manual - Warnings de capacidad (no errors)
  - [ ] **ELIMINADO:** ~~Testing manual - Validación de orden~~ (ya no aplica)
  - [ ] **NUEVO:** Testing manual - Default config debe existir
  - [ ] **NUEVO:** Testing manual - Standard con múltiples configs
  - [ ] **NUEVO:** Testing manual - Standard con configuración única
  - [ ] Testing manual - Editar standard
  - [ ] Testing en Tinker exitoso
  - [ ] **NUEVO:** Tests de ambigüedad units_per_hour:
    - [ ] Test: Standard con configuración única
    - [ ] Test: Standard con múltiples configuraciones
    - [ ] Test: Default config sin valor (debe fallar)
    - [ ] Test: Warnings de capacidad (no bloqueantes)
  - [ ] Tests unitarios creados y pasando
  - [ ] Tests de feature creados y pasando

- [ ] **Fase 4: Documentación**
  - [ ] Guía de uso creada
  - [ ] **NUEVO:** Sección "Limitación Actual: Productividad Única" agregada
  - [ ] **NUEVO:** Workaround de standards múltiples documentado
  - [ ] **NUEVO:** Advertencias sobre configuraciones no-default sin productividad
  - [ ] Seeders actualizados
  - [ ] Verificación final completada
  - [ ] **NUEVO:** Roadmap de migración a Opción A comunicado (6-8 semanas)

- [ ] **NUEVO: Fase 5: Comunicación y Planificación**
  - [ ] Comunicar limitación de productividad única a stakeholders
  - [ ] Identificar parts críticas que requieren productividad diferenciada
  - [ ] Planificar implementación de Opción A en roadmap (semanas 5-8)
  - [ ] Preparar script de migración de datos para Opción A
  - [ ] Validar con usuarios si workaround es aceptable temporalmente

---

## Riesgos y Mitigaciones

### Riesgo 1: Datos Existentes Inconsistentes

**Probabilidad:** MEDIA
**Impacto:** ALTO

**Descripción:**
Standards existentes pueden tener `persons_X` que exceden la capacidad de sus estaciones.

**Mitigación:**
1. Ejecutar query de verificación ANTES de implementar validaciones
2. Crear script de limpieza de datos:

```php
// database/scripts/clean_inconsistent_standards.php

$inconsistentStandards = Standard::all()->filter(function ($standard) {
    $validation = $standard->validatePersonsCapacity();
    return !$validation['valid'];
});

foreach ($inconsistentStandards as $standard) {
    $workstation = $standard->getWorkstation();
    $capacity = $workstation->employees;

    // Ajustar configuraciones para no exceder capacidad
    if ($standard->persons_1 > $capacity) {
        $standard->persons_1 = $capacity;
    }
    if ($standard->persons_2 > $capacity) {
        $standard->persons_2 = $capacity;
    }
    if ($standard->persons_3 > $capacity) {
        $standard->persons_3 = $capacity;
    }

    $standard->save();

    echo "Standard {$standard->id} ajustado a capacidad {$capacity}\n";
}
```

3. Documentar cambios realizados

---

### Riesgo 2: Breaking Changes en Código Dependiente

**Probabilidad:** BAJA
**Impacto:** MEDIO

**Descripción:**
Código existente que usa `persons_1`, `persons_2`, `persons_3` puede romper si se cambian validaciones.

**Mitigación:**
1. Buscar todos los usos de estos campos:
```bash
grep -r "persons_1" app/
grep -r "persons_2" app/
grep -r "persons_3" app/
```

2. Revisar y actualizar código dependiente
3. Mantener retrocompatibilidad agregando validaciones graduales

---

### Riesgo 3: Confusión de Usuarios

**Probabilidad:** ALTA
**Impacto:** BAJO

**Descripción:**
Usuarios pueden no entender el propósito de múltiples configuraciones de personas.

**Mitigación:**
1. Agregar tooltips explicativos en UI
2. Crear documentación de usuario (User Guide)
3. Proveer ejemplos en formulario
4. Agregar validación con mensajes claros

---

### Riesgo 4: Performance en Validaciones

**Probabilidad:** BAJA
**Impacto:** BAJO

**Descripción:**
La validación `ValidWorkstationCapacity` hace queries a BD, puede impactar performance.

**Mitigación:**
1. Usar Livewire `wire:model.live` solo donde sea necesario
2. Cachear relaciones de workstation en componente
3. Considerar validación asíncrona para formularios largos

---

### Riesgo 5: Limitación de Productividad Única (NUEVO RIESGO CRÍTICO)

**Probabilidad:** ALTA (100% - es una limitación conocida)
**Impacto:** ALTO

**Descripción:**
La implementación de Opción B mantiene `units_per_hour` único por standard,
pero el usuario confirmó que cada configuración de personas puede tener
productividad diferente ("cada uno tiene una cantidad diferente"). Esto genera:

1. **Datos incompletos:** Solo se almacena productividad de configuración default
2. **Cálculos incorrectos:** Si se usan configuraciones no-default, la productividad será inexacta
3. **Workarounds necesarios:** Usuarios deben crear múltiples standards para la misma parte
4. **Confusión de usuarios:** No es intuitivo que persons_2 y persons_3 no tengan productividad asociada

**Impacto en el Negocio:**
- **Planificación de Producción:** Cálculos de capacidad pueden ser incorrectos si se cambia de configuración
- **Reportes:** Métricas de productividad pueden ser engañosas
- **Toma de Decisiones:** Gerentes no tendrán datos precisos para optimizar asignación de personal

**Mitigación:**

1. **Corto Plazo (Opción B - Inmediato):**
   - Documentar CLARAMENTE la limitación en UI y documentación
   - Implementar campo `default_persons_config` para indicar a qué configuración aplica `units_per_hour`
   - Agregar warnings en UI cuando hay múltiples configuraciones:
     ```
     ⚠️ ADVERTENCIA: units_per_hour (100) aplica solo a la configuración default (persons_1).
     Las configuraciones persons_2 y persons_3 NO tienen productividad almacenada.
     ```
   - Proveer guía para crear standards separados si es necesario
   - Agregar sección en documentación: "Workaround para Productividad Diferenciada"

2. **Mediano Plazo (Migrar a Opción A - 6-8 semanas):**
   - **PRIORIDAD CRÍTICA:** Planificar implementación de tabla `standard_configurations`
   - Crear script de migración de datos:
     ```php
     // Migrar standards con configuración única (fácil)
     foreach (Standard::where('persons_2', null)->where('persons_3', null)->get() as $standard) {
         StandardConfiguration::create([
             'standard_id' => $standard->id,
             'persons_required' => $standard->persons_1,
             'units_per_hour' => $standard->units_per_hour,
             'is_default' => true,
         ]);
     }

     // Migrar standards con múltiples configuraciones (requiere decisión de usuario)
     // ¿Cómo asignar units_per_hour a persons_2 y persons_3?
     ```
   - Comunicar a usuarios sobre la mejora futura
   - Incluir en roadmap visible (semanas 5-8)

3. **Validación con Usuarios:**
   - Confirmar si workaround de standards múltiples es aceptable temporalmente (6-8 semanas)
   - Identificar parts críticas que requieren productividad diferenciada URGENTE
   - Si el impacto es MUY ALTO, considerar implementar Opción A ANTES de CapacityCalculatorService

**Timeline Recomendado:**
- **Semana 1:** Implementar Opción B con documentación CLARA de limitación
- **Semana 2:** Feedback de usuarios sobre workaround
- **Semanas 3-4:** Continuar con CapacityCalculatorService
- **Semanas 5-6:** Implementar Opción A (PRIORIDAD ALTA)
- **Semana 7:** Migración de datos y testing
- **Semana 8:** Validación con usuarios reales

**Indicador de Éxito para Priorización:**
Si >30% de standards requieren productividades diferentes, Opción A debe implementarse en semana 3-4 (antes de CapacityCalculator).

### Riesgo 6: Evolución a Configuraciones Múltiples (Opción A) - Complejidad de Migración

**Probabilidad:** ALTA (futuro planificado)
**Impacto:** MEDIO

**Descripción:**
Cuando se implemente Opción A en el futuro, la migración de datos será compleja,
especialmente para standards con múltiples configuraciones donde solo hay un `units_per_hour`.

**Mitigación:**
1. Documentar claramente limitaciones de Opción B
2. Diseñar Opción B con migración futura en mente (campo `default_persons_config` facilita migración)
3. Mantener datos consistentes para facilitar migración
4. Planificar Opción A en Roadmap de Fase 3
5. Crear script de migración que:
   - Migre automáticamente standards con configuración única
   - Identifique standards con múltiples configuraciones que requieren intervención manual
   - Provea UI para que usuarios ingresen productividades faltantes

---

## Referencias

### Documentos Relacionados

1. **Spec 01** - Plan de Implementación Capacidad de Producción
   - Archivo: `C:\xampp\htdocs\flexcon-tracker\Diagramas_flujo\Estructura\specs\01_production_capacity_implementation_plan.md`
   - Sección relevante: Propiedad 4 (Cálculo de Horas Requeridas)

2. **Spec 02** - Refactorización Standards-Workstation
   - Archivo: `C:\xampp\htdocs\flexcon-tracker\Diagramas_flujo\Estructura\specs\02_standards_workstation_relationship_refactor.md`
   - Decisión: Mantener foreign keys directas

3. **Spec 03** - Guía Rápida de Implementación
   - Archivo: `C:\xampp\htdocs\flexcon-tracker\Diagramas_flujo\Estructura\specs\03_standards_implementation_quick_guide.md`
   - Implementación actual de validación `OnlyOneWorkstation`

### Archivos Clave

**Modelos:**
- `app/Models/Standard.php`
- `app/Models/Table.php`
- `app/Models/Semi_Automatic.php`
- `app/Models/Machine.php`

**Componentes Livewire:**
- `app/Livewire/Admin/Standards/StandardCreate.php`
- `app/Livewire/Admin/Standards/StandardEdit.php`

**Vistas:**
- `resources/views/livewire/admin/standards/standard-create.blade.php`
- `resources/views/livewire/admin/standards/standard-edit.blade.php`

**Validaciones:**
- `app/Rules/OnlyOneWorkstation.php`
- `app/Rules/ValidWorkstationCapacity.php` (a crear)

**Migraciones:**
- `database/migrations/2025_12_14_190425_create_standards_table.php`
- `database/migrations/2025_12_20_081207_add_units_per_hour_to_standards_table.php`
- `database/migrations/YYYY_MM_DD_HHMMSS_add_default_persons_config_to_standards_table.php` (a crear)

### Recursos Externos

**Laravel Documentation:**
- Validation: https://laravel.com/docs/12.x/validation
- Custom Validation Rules: https://laravel.com/docs/12.x/validation#custom-validation-rules
- Eloquent Accessors: https://laravel.com/docs/12.x/eloquent-mutators#accessors-and-mutators

**Livewire Documentation:**
- Form Validation: https://livewire.laravel.com/docs/validation
- Real-time Validation: https://livewire.laravel.com/docs/validation#real-time-validation

---

## Apéndices

### Apéndice A: Diagrama de Flujo de Validación

```
┌─────────────────────────────────────────────────────────────┐
│ Usuario ingresa datos en StandardCreate                    │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Validaciones Laravel Standard                               │
│ - part_id: required, exists                                 │
│ - units_per_hour: required, min:1                           │
│ - persons_1, persons_2, persons_3: nullable, integer, min:1 │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Validación Custom: OnlyOneWorkstation                       │
│ ✓ Solo UNA estación seleccionada                            │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Validación Custom: ValidWorkstationCapacity                 │
│ ✓ persons_X <= workstation.employees                        │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Validación: Orden de Configuraciones                        │
│ ✓ persons_2 >= persons_1                                    │
│ ✓ persons_3 >= persons_2                                    │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ Validación Adicional: Al menos UNA estación                 │
│ ✓ work_table_id OR semi_auto_work_table_id OR machine_id    │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ ✅ TODAS las validaciones pasaron                           │
│ → Standard::create() ejecutado                              │
└─────────────────────────────────────────────────────────────┘
```

### Apéndice B: Comparación de Opciones

| Aspecto | Opción A (Configuraciones Múltiples) | Opción B (Validaciones Mejoradas) |
|---------|--------------------------------------|-----------------------------------|
| **Tiempo de Implementación** | 2-3 días | 6-8 horas |
| **Complejidad** | Alta | Media |
| **Riesgo de Breaking Changes** | Alto | Bajo |
| **Productividad Diferenciada** | ✅ Sí (units_per_hour por config) | ❌ No (único units_per_hour) |
| **Número de Configuraciones** | Ilimitado | Máximo 3 |
| **Coherencia con Capacidad** | ✅ Forzada por validación | ✅ Forzada por validación |
| **Migración de Datos** | ✅ Requerida | ⚠️ Mínima (solo default_persons_config) |
| **Escalabilidad Futura** | ✅ Excelente | ⚠️ Limitada |
| **Compatibilidad con CapacityCalculatorService** | ✅ Ideal | ⚠️ Requiere adaptaciones |
| **Semántica de Negocio** | ✅ Clara y explícita | ⚠️ Mejorada pero aún ambigua |
| **Mantenibilidad** | ✅ Alta (diseño limpio) | ⚠️ Media (solución pragmática) |

**Recomendación General:**
- **Corto Plazo (inmediato):** Opción B
- **Largo Plazo (Fase 3+):** Migrar a Opción A

### Apéndice C: Query de Análisis de Datos Existentes

```sql
-- Estadísticas de configuraciones de personas
SELECT
    'Total Standards' as metric,
    COUNT(*) as value
FROM standards
WHERE deleted_at IS NULL

UNION ALL

SELECT
    'Standards con persons_1',
    COUNT(*)
FROM standards
WHERE persons_1 IS NOT NULL AND deleted_at IS NULL

UNION ALL

SELECT
    'Standards con persons_2',
    COUNT(*)
FROM standards
WHERE persons_2 IS NOT NULL AND deleted_at IS NULL

UNION ALL

SELECT
    'Standards con persons_3',
    COUNT(*)
FROM standards
WHERE persons_3 IS NOT NULL AND deleted_at IS NULL

UNION ALL

SELECT
    'Standards con múltiples configuraciones',
    COUNT(*)
FROM standards
WHERE (persons_1 IS NOT NULL AND persons_2 IS NOT NULL)
   OR (persons_2 IS NOT NULL AND persons_3 IS NOT NULL)
   AND deleted_at IS NULL;

-- Distribución de capacidad de estaciones
SELECT
    'Table' as workstation_type,
    employees as capacity,
    COUNT(*) as count
FROM tables
WHERE active = 1
GROUP BY employees

UNION ALL

SELECT
    'Semi_Automatic',
    employees,
    COUNT(*)
FROM semi__automatics
WHERE active = 1
GROUP BY employees

UNION ALL

SELECT
    'Machine',
    employees,
    COUNT(*)
FROM machines
WHERE active = 1
GROUP BY employees
ORDER BY workstation_type, capacity;

-- Standards con posibles inconsistencias
SELECT
    s.id,
    p.number as part_number,
    s.persons_1,
    s.persons_2,
    s.persons_3,
    COALESCE(t.employees, sa.employees, m.employees) as station_capacity,
    CASE
        WHEN s.work_table_id IS NOT NULL THEN CONCAT('Table-', t.number)
        WHEN s.semi_auto_work_table_id IS NOT NULL THEN CONCAT('SemiAuto-', sa.number)
        WHEN s.machine_id IS NOT NULL THEN CONCAT('Machine-', m.name)
    END as workstation,
    CASE
        WHEN s.persons_1 > COALESCE(t.employees, sa.employees, m.employees) THEN 'persons_1 EXCEDE'
        WHEN s.persons_2 > COALESCE(t.employees, sa.employees, m.employees) THEN 'persons_2 EXCEDE'
        WHEN s.persons_3 > COALESCE(t.employees, sa.employees, m.employees) THEN 'persons_3 EXCEDE'
        WHEN s.persons_2 < s.persons_1 THEN 'persons_2 < persons_1'
        WHEN s.persons_3 < s.persons_2 THEN 'persons_3 < persons_2'
        ELSE 'OK'
    END as issue
FROM standards s
JOIN parts p ON s.part_id = p.id
LEFT JOIN tables t ON s.work_table_id = t.id
LEFT JOIN semi__automatics sa ON s.semi_auto_work_table_id = sa.id
LEFT JOIN machines m ON s.machine_id = m.id
WHERE s.deleted_at IS NULL
HAVING issue != 'OK';
```

---

**Fin del Spec 04 - Análisis Técnico: Validación de Personas y Estaciones de Trabajo en Standards**

---

**Próximos Pasos:**

1. Revisar y aprobar este spec con stakeholders
2. Ejecutar queries de análisis de datos (Apéndice C)
3. Decidir entre implementación inmediata (Opción B) o planificación de Opción A
4. Si se aprueba Opción B: Seguir Plan de Implementación
5. Actualizar Roadmap con fecha para migración a Opción A (si aplica)
