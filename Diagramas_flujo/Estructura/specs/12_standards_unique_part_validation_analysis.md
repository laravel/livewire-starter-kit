# Analisis Tecnico: Validacion de Unicidad part_id en Standards

**Documento:** 12_standards_unique_part_validation_analysis.md
**Fecha:** 2026-01-12
**Version:** 2.0
**Autor:** Agent Architect
**Proyecto:** Flexcon-Tracker
**Fase:** FASE 2 - Production Capacity Management
**Actualizacion:** Comparación Solución Simple vs Compleja

---

## PREGUNTA CLAVE DEL USUARIO

"¿Es la única solución? ¿No podría hacer una solución directa desde la migración?"

**RESPUESTA CORTA:** Si, PODRIAS usar un simple UNIQUE constraint en la migración. PERO dependiendo de tu caso de uso, puede que NO sea la mejor solución. A continuación te muestro AMBAS opciones con EVIDENCIA REAL de tu sistema.

---

## COMPARACIÓN: SOLUCIÓN SIMPLE vs COMPLEJA

### Contexto: Datos Reales del Sistema

**Situación actual en la base de datos:**
```
Total Standards: 20
Standards Activos: 20
Standards Inactivos: 0
Parts con duplicados: 2

Duplicados encontrados:
- Part 18 (STS H-C-3): 2 estándares activos
  - Standard #17: 2500 units/hr (creado: 2026-01-13 06:42)
  - Standard #14: 2500 units/hr (creado: 2026-01-13 06:39)

- Part 22 (STS H-M-4): 3 estándares activos
  - Standard #21: 43 units/hr (creado: 2026-01-13 06:45:31)
  - Standard #20: 43 units/hr (creado: 2026-01-13 06:45:30)
  - Standard #18: 166 units/hr (creado: 2026-01-13 06:43)
```

**Observaciones críticas:**
- TODOS los estándares están activos (active = true)
- NO hay estándares inactivos en el sistema
- El campo `effective_date` existe pero no se usa para diferenciar versiones
- Los duplicados tienen valores diferentes (Part 22: 43 vs 166 units/hr)
- Soft deletes habilitado pero no utilizado

---

### OPCIÓN A: SOLUCIÓN SIMPLE (UNIQUE en migración)

**Implementación:**

```php
// database/migrations/2026_01_13_add_unique_part_id_to_standards.php

public function up(): void
{
    // OPCIÓN A1: Unicidad absoluta - UN estándar por parte
    Schema::table('standards', function (Blueprint $table) {
        $table->unique('part_id', 'unique_part_id');
    });
}
```

**Ventajas:**
- Implementación inmediata (1 línea de código)
- Garantizado a nivel de base de datos
- Imposible crear duplicados (error DB)
- No requiere lógica adicional en la aplicación
- Fácil de entender y mantener
- Performance óptimo (índice único)

**Desventajas:**
- NO permite historial de estándares
- NO permite versiones de estándares
- Si cambia el proceso → debes ELIMINAR el viejo
- No puedes desactivar, solo DELETE
- Pierdes auditoría de cambios
- No puedes preparar estándares futuros

**¿Cuándo usar OPCIÓN A?**
- Si NUNCA cambiarán los estándares de producción
- Si NO necesitas auditoría de cambios
- Si cuando cambias un estándar → eliminas el anterior
- Si NO necesitas planificar estándares futuros
- Si tu caso de uso es SIMPLE

**Ejemplo de uso:**
```
Hoy: Part 22 → Standard A (166 units/hr)
   ✅ UN estándar activo

Mañana: Cambió el proceso
   ❌ NO puedes crear Standard B
   ✅ Debes DELETE Standard A
   ✅ Luego crear Standard B (43 units/hr)
   ⚠️ Se perdió el historial de Standard A
```

---

### OPCIÓN B: SOLUCIÓN COMPLEJA (validación en app + índice compuesto)

**Implementación:**

```php
// database/migrations/2026_01_13_add_unique_active_standard_index.php

public function up(): void
{
    // OPCIÓN B: Permite múltiples estándares, solo UNO activo
    Schema::table('standards', function (Blueprint $table) {
        // Índice compuesto para performance
        $table->index(['part_id', 'active'], 'idx_part_active_standard');
    });
}
```

```php
// app/Rules/UniqueActiveStandard.php

namespace App\Rules;

use App\Models\Standard;
use Illuminate\Contracts\Validation\Rule;

class UniqueActiveStandard implements Rule
{
    private int $partId;
    private ?int $exceptId;

    public function __construct(int $partId, ?int $exceptId = null)
    {
        $this->partId = $partId;
        $this->exceptId = $exceptId;
    }

    public function passes($attribute, $value): bool
    {
        if (!$value) {
            return true; // Si active = false, permitir
        }

        $query = Standard::where('part_id', $this->partId)
            ->where('active', true);

        if ($this->exceptId) {
            $query->where('id', '!=', $this->exceptId);
        }

        return $query->count() === 0;
    }

    public function message(): string
    {
        return 'Ya existe un estándar activo para esta parte. Desactiva el anterior primero.';
    }
}
```

```php
// app/Livewire/Admin/Standards/StandardCreate.php

protected function rules(): array
{
    return [
        'part_id' => 'required|exists:parts,id',
        'active' => ['boolean', new UniqueActiveStandard($this->part_id)],
        // ... otros campos
    ];
}
```

**Ventajas:**
- PERMITE historial completo de estándares
- PERMITE versiones y auditoría
- Puedes desactivar el viejo y activar el nuevo
- Puedes preparar estándares futuros (active=false, effective_date futura)
- Flexibilidad para reportes históricos
- Trazabilidad completa de cambios
- Soft deletes mantienen registros borrados

**Desventajas:**
- Requiere validación custom en múltiples lugares
- Más código para mantener
- Posible race condition (sin transacciones)
- Depende de lógica de aplicación (no DB)
- Más complejo de entender
- Requiere testing adicional

**¿Cuándo usar OPCIÓN B?**
- Si los estándares CAMBIAN con el tiempo
- Si necesitas auditoría completa
- Si planeas reportes históricos
- Si quieres preparar estándares futuros
- Si el campo `effective_date` tiene valor de negocio
- Si necesitas trazabilidad

**Ejemplo de uso:**
```
Hoy: Part 22
   → Standard A (166 units/hr, active=true, effective_date=2026-01-01)

Mañana: Cambió el proceso
   ✅ Desactivas Standard A (active=false)
   ✅ Creas Standard B (43 units/hr, active=true, effective_date=2026-01-15)
   ✅ Mantienes historial completo

Reporte Futuro:
   ✅ "¿Qué estándar teníamos en Enero 2026?" → Standard A
   ✅ "¿Cuándo cambió el estándar?" → 2026-01-15
   ✅ "¿Por qué cambió la capacidad?" → Ver historial
```

---

### TABLA COMPARATIVA

| Criterio | OPCIÓN A: UNIQUE Simple | OPCIÓN B: Validación App |
|----------|------------------------|-------------------------|
| **Implementación** | 1 línea (migración) | ~100 líneas (rule + validaciones) |
| **Complejidad** | Muy simple | Moderada |
| **Garantía** | Base de datos | Aplicación |
| **Historial** | ❌ NO | ✅ SI |
| **Auditoría** | ❌ NO | ✅ SI |
| **Versiones** | ❌ NO | ✅ SI |
| **Estándares futuros** | ❌ NO | ✅ SI |
| **Soft deletes útil** | ❌ NO | ✅ SI |
| **Performance** | Excelente | Buena |
| **Race conditions** | ❌ NO | ⚠️ Posible |
| **Testing requerido** | Mínimo | Extenso |
| **Mantenibilidad** | Alta | Media |

---

### EVIDENCIA: ¿Qué dice tu código actual?

**1. Existencia de soft deletes:**
```php
// database/migrations/2025_12_14_190425_create_standards_table.php
$table->softDeletes(); // ¿Por qué? Si no hay historial, no tiene sentido
```

**2. Campo effective_date:**
```php
$table->date('effective_date')->nullable(); // ¿Para qué? Sugiere versionado
```

**3. Campo active:**
```php
$table->boolean('active')->default(true); // ¿Por qué no solo DELETE?
```

**4. Scopes para activo/inactivo:**
```php
// app/Models/Standard.php
public function scopeActive(Builder $query): Builder
{
    return $query->where('active', true);
}

public function scopeInactive(Builder $query): Builder
{
    return $query->where('active', false);
}
```
Si solo puede haber UN estándar por parte, ¿para qué un scope de inactivos?

**5. Método getStats() cuenta inactivos:**
```php
$inactive = self::where('active', false)->count(); // ¿Por qué contarlos?
```

**CONCLUSIÓN DE EVIDENCIA:**
Tu arquitectura actual SUGIERE que SI necesitas historial:
- Soft deletes implementado
- Campo `active` para activar/desactivar
- Campo `effective_date` para fechas
- Scopes para filtrar activos/inactivos
- Stats que cuentan inactivos

Si solo necesitaras UN estándar por parte → TODO esto sería innecesario.

---

### ANÁLISIS DE TU CASO ESPECÍFICO

**Datos actuales:**
- 20 estándares, TODOS activos
- 0 estándares inactivos
- 2 partes con duplicados

**Preguntas para decidir:**

1. **¿Por qué hay duplicados?**
   - ¿Error de usuario? → OPCIÓN A (simple)
   - ¿Cambio de proceso? → OPCIÓN B (historial)

2. **Part 22 tiene 43 y 166 units/hr**
   - ¿Cuál es el correcto? ¿Por qué hay 2 valores?
   - ¿Cambió el método de producción?
   - ¿Necesitas saber por qué cambió?

3. **¿Qué pasa cuando cambia un estándar?**
   - ¿Lo eliminas y creas uno nuevo? → OPCIÓN A
   - ¿Lo desactivas y creas uno nuevo? → OPCIÓN B

4. **¿Necesitas auditoría?**
   - ISO, compliance, reportes → OPCIÓN B
   - Sistema interno simple → OPCIÓN A

5. **¿Planeas usar effective_date?**
   - "Este estándar aplica desde 2026-03-01" → OPCIÓN B
   - No → OPCIÓN A (y elimina el campo)

---

## RECOMENDACIÓN BASADA EN EVIDENCIA

### SI tu respuesta es:

**"Solo queremos UN estándar por parte, punto":**
```php
// OPCIÓN A: Usa UNIQUE constraint
Schema::table('standards', function (Blueprint $table) {
    $table->unique('part_id');
});

// Y considera ELIMINAR:
// - Campo 'active' (ya no tiene sentido)
// - Campo 'effective_date' (ya no tiene sentido)
// - Soft deletes (ya no tiene sentido)
// - Scopes active/inactive (ya no tienen sentido)
```

**"Queremos historial de cambios y auditoría":**
```php
// OPCIÓN B: Usa validación en app
// - Mantén soft deletes
// - Mantén 'active' para activar/desactivar
// - Usa 'effective_date' para saber CUÁNDO aplica cada versión
// - Implementa la validación custom
```

---

### Mi Recomendación Final

**Para Flexcon-Tracker, recomiendo OPCIÓN B** por las siguientes razones:

1. **Tu arquitectura ya está preparada:**
   - Soft deletes habilitado
   - Campo `active` implementado
   - Campo `effective_date` existe
   - Scopes para activos/inactivos

2. **Contexto de manufactura:**
   - Los procesos de producción CAMBIAN
   - Necesitas saber "¿qué estándar usábamos en Marzo?"
   - Auditoría para ISO/compliance
   - Reportes de mejora continua (antes: 166 u/hr → ahora: 43 u/hr)

3. **Datos actuales sugieren versionado:**
   - Part 22: valores diferentes (166 vs 43)
   - Esto NO es un error, es un CAMBIO de proceso
   - Si eliminas el de 166, pierdes información valiosa

4. **Flexibilidad futura:**
   - Preparar estándares futuros
   - "En 3 meses cambia el proceso, ya tengo el estándar listo"
   - Reportes históricos de capacidad

**PERO:** Si realmente NO necesitas historial → usa OPCIÓN A y simplifica todo el código.

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#1-resumen-ejecutivo)
2. [Problema Identificado](#2-problema-identificado)
3. [Analisis del Estado Actual](#3-analisis-del-estado-actual)
4. [Analisis de Impacto](#4-analisis-de-impacto)
5. [Consideraciones de Negocio](#5-consideraciones-de-negocio)
6. [Diseño de la Solucion](#6-diseño-de-la-solucion)
7. [Plan de Implementacion](#7-plan-de-implementacion)
8. [Migracion de Datos](#8-migracion-de-datos)
9. [Validaciones en Capa de Aplicacion](#9-validaciones-en-capa-de-aplicacion)
10. [Testing](#10-testing)
11. [Documentacion y Mensajes](#11-documentacion-y-mensajes)
12. [Alternativas Evaluadas](#12-alternativas-evaluadas)
13. [Riesgos y Mitigaciones](#13-riesgos-y-mitigaciones)
14. [Conclusiones y Recomendaciones](#14-conclusiones-y-recomendaciones)

---

## 1. Resumen Ejecutivo

### 1.1 Problema

Actualmente es posible crear **multiples registros de Standards con el mismo `part_id`**, lo que permite tener duplicados de estándares para la misma parte. Esto genera:

- **Ambigüedad operativa**: ¿Cual estandar usar al calcular capacidad?
- **Inconsistencia de datos**: Multiples estandares activos para la misma parte
- **Errores en produccion**: CapacityCalculatorService toma solo el primero (`.first()`)
- **Confusion para usuarios**: No esta claro cual estandar es el "correcto"

### 1.2 Estado Actual Detectado

**Datos en Produccion:**
```
Total parts with duplicate standards: 2

Part ID: 22 (STS H-M-4) - 3 estandares duplicados
Part ID: 18 (STS H-C-3) - 2 estandares duplicados

Total Standards: 20
Duplicates Found: 5 registros afectados
```

### 1.3 Solucion Propuesta

**Opcion Recomendada: Modelo de Versionado Historico**

En lugar de imponer unicidad estricta, implementar un sistema donde:
- Una parte puede tener **MULTIPLES estandares** (historial, versiones)
- Solo **UN estandar puede estar ACTIVO** a la vez por parte
- Los estandares inactivos se mantienen como historial
- El campo `effective_date` indica desde cuando aplica cada version

**Ventajas:**
- Permite historial completo de cambios en estandares
- Flexibilidad para planificacion futura (estandares con fecha futura)
- No destruye datos existentes
- Alineado con necesidades de auditoría y trazabilidad

**Constraint de Unicidad:**
```sql
UNIQUE (part_id, active) WHERE active = 1
-- O en MySQL 8.0+:
UNIQUE KEY unique_active_standard (part_id, active) WHERE active = 1
```

**Nota:** MySQL 5.7 no soporta `WHERE` en indices, por lo que usaremos validacion a nivel de aplicacion.

---

## 2. Problema Identificado

### 2.1 Descripcion del Problema

**Comportamiento Actual:**
```php
// Esto es posible y NO genera error:
Standard::create(['part_id' => 22, 'units_per_hour' => 100, 'active' => true]);
Standard::create(['part_id' => 22, 'units_per_hour' => 150, 'active' => true]);
Standard::create(['part_id' => 22, 'units_per_hour' => 200, 'active' => true]);
// Resultado: 3 estandares ACTIVOS para la misma parte
```

**Impacto en CapacityCalculatorService:**
```php
// app/Services/CapacityCalculatorService.php (lineas 109-111)
$standard = Standard::where('part_id', $part_id)
    ->where('active', true)
    ->first(); // <-- Toma el PRIMERO, ignora otros
```

**Problema:** Si hay multiples estandares activos, el sistema usa el primero encontrado, lo cual es **no-deterministico** (depende del orden de insercion/ID).

### 2.2 Escenarios Problematicos

**Escenario 1: Calculos Incorrectos de Capacidad**

Usuario A crea estandar con 100 units/hour
Usuario B crea estandar con 200 units/hour (ambos para misma parte)

**Resultado:** El Capacity Calculator usa 100 units/hour (el primero), generando calculos incorrectos de tiempo de produccion.

**Escenario 2: Confusion en UI**

En StandardList se muestran 3 estandares activos para la misma parte. Usuario no sabe cual es el "correcto".

**Escenario 3: Edicion Simultanea**

Usuario A edita estandar ID 18
Usuario B crea nuevo estandar para misma parte
Ahora hay 2 estandares activos, violando logica de negocio.

### 2.3 Causa Raiz

**Falta de constraint de unicidad** en:
1. **Base de datos**: No hay `UNIQUE KEY` sobre `part_id` (con condicion de `active`)
2. **Modelo Laravel**: No hay validacion `unique` en reglas
3. **Componentes Livewire**: No hay validacion custom en StandardCreate/StandardEdit

---

## 3. Analisis del Estado Actual

### 3.1 Estructura de la Tabla `standards`

**Migracion Actual:**
```php
// database/migrations/2025_12_14_190425_create_standards_table.php
Schema::create('standards', function (Blueprint $table) {
    $table->id();
    $table->foreignId('part_id')->constrained()->onDelete('cascade');
    $table->foreignId('work_table_id')->nullable()->constrained('tables')->onDelete('set null');
    $table->foreignId('semi_auto_work_table_id')->nullable()->constrained('semi__automatics')->onDelete('set null');
    $table->foreignId('machine_id')->nullable()->constrained()->onDelete('set null');

    $table->integer('persons_1')->nullable();
    $table->integer('persons_2')->nullable();
    $table->integer('persons_3')->nullable();
    $table->date('effective_date')->nullable();
    $table->boolean('active')->default(true);
    $table->text('description')->nullable();

    $table->softDeletes();
    $table->timestamps();

    // Indices existentes
    $table->index(['work_table_id', 'active'], 'standards_search_index');
    $table->index(['semi_auto_work_table_id', 'active'], 'standards_semi_auto_active_index');
    $table->index('effective_date', 'standards_effective_date_index');
    $table->index('active', 'standards_active_index');
    $table->index('machine_id', 'standards_machine_index');
    $table->index('part_id', 'standards_part_index');

    // FALTANTE: No hay UNIQUE constraint sobre part_id
});
```

**Observacion Critica:** Existe indice simple sobre `part_id`, pero NO es UNIQUE.

### 3.2 Modelo Standard - Validaciones Actuales

**Archivo:** `app/Models/Standard.php`

**Fillable:**
```php
protected $fillable = [
    'persons_1', 'persons_2', 'persons_3',
    'effective_date', 'active', 'description',
    'part_id', 'work_table_id', 'semi_auto_work_table_id',
    'machine_id', 'units_per_hour'
];
```

**Observacion:** NO hay reglas de validacion a nivel de modelo (Laravel no usa validacion en modelos por defecto).

### 3.3 Componente StandardCreate - Validaciones

**Archivo:** `app/Livewire/Admin/Standards/StandardCreate.php`

**Reglas de Validacion (lineas 31-46):**
```php
protected function rules(): array
{
    return [
        'part_id' => 'required|exists:parts,id',
        'units_per_hour' => 'required|integer|min:1',
        'work_table_id' => 'nullable|exists:tables,id',
        'semi_auto_work_table_id' => 'nullable|exists:semi__automatics,id',
        'machine_id' => 'nullable|exists:machines,id',
        'persons_1' => 'nullable|integer|min:1',
        'persons_2' => 'nullable|integer|min:1',
        'persons_3' => 'nullable|integer|min:1',
        'effective_date' => 'nullable|date',
        'active' => 'boolean',
        'description' => 'nullable|string',
    ];
}
```

**Observacion Critica:** `part_id` solo valida `required|exists`, **NO valida unicidad**.

### 3.4 Componente StandardEdit - Validaciones

**Archivo:** `app/Livewire/Admin/Standards/StandardEdit.php`

**Reglas de Validacion (lineas 43-58):**
```php
protected function rules(): array
{
    return [
        'part_id' => 'required|exists:parts,id',
        // ... (iguales a StandardCreate)
    ];
}
```

**Problema:** Al editar, permite cambiar `part_id` a uno que YA existe en otro estandar activo.

### 3.5 CapacityCalculatorService - Uso de Standards

**Archivo:** `app/Services/CapacityCalculatorService.php`

**Metodo calculateRequiredHours (lineas 100-125):**
```php
public function calculateRequiredHours(int $part_id, int $quantity, string $assembly_mode = '1_person'): float
{
    $part = Part::find($part_id);

    if (!$part) {
        throw new \Exception("Part with ID {$part_id} not found");
    }

    // Get the active standard for this part
    $standard = Standard::where('part_id', $part_id)
        ->where('active', true)
        ->first(); // <-- PROBLEMA: Toma el primero si hay multiples

    if (!$standard) {
        throw new \Exception("No active standard found for part {$part->number}");
    }

    $units_per_hour = $standard->units_per_hour ?? 0;

    if ($units_per_hour === 0) {
        throw new \Exception("Standard for part {$part->number} has units_per_hour = 0");
    }

    return round($quantity / $units_per_hour, 2);
}
```

**Comportamiento con Duplicados:**
- Si hay 3 estandares activos con `units_per_hour` de 100, 150, 200
- Siempre usara el que tenga menor ID (el primero insertado)
- **No hay advertencia ni error** si existen duplicados

### 3.6 Datos Existentes - Analisis de Duplicados

**Script de Analisis Ejecutado:**
```php
$duplicates = DB::table('standards')
    ->select('part_id', DB::raw('COUNT(*) as count'))
    ->whereNull('deleted_at')
    ->groupBy('part_id')
    ->having('count', '>', 1)
    ->get();
```

**Resultados:**

**Caso 1: Part ID 22 (STS H-M-4)**
```
- Standard ID: 18 | Active: Yes | Units/Hour: 166 | Workstation: MT-005 | Created: 2026-01-13 06:43:26
- Standard ID: 20 | Active: Yes | Units/Hour: 43  | Workstation: MT-006 | Created: 2026-01-13 06:45:30
- Standard ID: 21 | Active: Yes | Units/Hour: 43  | Workstation: MT-006 | Created: 2026-01-13 06:45:31
```

**Analisis:**
- 3 estandares activos
- **Problema:** ID 20 y 21 son casi identicos (mismo units/hour, misma workstation, creados con 1 segundo de diferencia)
- **Causa probable:** Doble click en boton de submit
- **Impacto:** CapacityCalculator usara ID 18 (166 units/hour), ignorando otros

**Caso 2: Part ID 18 (STS H-C-3)**
```
- Standard ID: 14 | Active: Yes | Units/Hour: 2500 | Workstation: MT-003              | Created: 2026-01-13 06:39:19
- Standard ID: 17 | Active: Yes | Units/Hour: 2500 | Workstation: Haas - VF-4 - CNC   | Created: 2026-01-13 06:42:36
```

**Analisis:**
- 2 estandares activos
- **Diferencia:** Distinta workstation (Manual table vs Machine)
- **Problema:** Ambiguedad sobre cual estacion usar
- **Impacto:** CapacityCalculator usara ID 14 (MT-003), ignorando ID 17 (Haas)

### 3.7 Relacion Part → Standards

**Archivo:** `app/Models/Part.php`

**Relacion hasMany (lineas 38-43):**
```php
public function standards(): HasMany
{
    return $this->hasMany(Standard::class);
}
```

**Observacion:** La relacion es `hasMany`, lo cual permite multiples estandares por parte. **Esto es correcto** si queremos historial, pero falta logica de "solo uno activo".

---

## 4. Analisis de Impacto

### 4.1 Impacto en Backend

**Componentes Afectados:**

| Componente | Ubicacion | Impacto | Cambios Requeridos |
|------------|-----------|---------|-------------------|
| **StandardCreate** | `app/Livewire/Admin/Standards/StandardCreate.php` | ALTO | Agregar validacion unique condicional |
| **StandardEdit** | `app/Livewire/Admin/Standards/StandardEdit.php` | ALTO | Agregar validacion unique condicional (ignorar registro actual) |
| **Standard Model** | `app/Models/Standard.php` | MEDIO | Agregar scope `activeForPart()`, metodo `deactivateOthers()` |
| **CapacityCalculatorService** | `app/Services/CapacityCalculatorService.php` | BAJO | Agregar logging/warning si detecta duplicados |
| **Migraciones** | `database/migrations/` | ALTO | Nueva migracion para constraint (o indice compuesto) |

### 4.2 Impacto en CapacityCalculatorService

**Situacion Actual:**
```php
$standard = Standard::where('part_id', $part_id)
    ->where('active', true)
    ->first();
```

**Comportamiento con Validacion Implementada:**
- Solo habra UN estandar activo por parte
- `.first()` siempre retorna el unico activo
- **No requiere cambios en logica**, pero podemos mejorar:

**Mejora Propuesta:**
```php
$standard = Standard::where('part_id', $part_id)
    ->where('active', true)
    ->first();

// Agregar validacion de seguridad (defensive programming)
$duplicates = Standard::where('part_id', $part_id)
    ->where('active', true)
    ->count();

if ($duplicates > 1) {
    Log::warning("Multiple active standards found for part {$part_id}. Using first one (ID: {$standard->id})");
}
```

### 4.3 Impacto en Capacity Wizard

**Componente:** (Presumiblemente existe un wizard para calculo de capacidad)

**Impacto:** BAJO
- El wizard consume CapacityCalculatorService
- Si el service funciona correctamente, el wizard no necesita cambios
- **Beneficio:** Resultados mas predecibles y consistentes

### 4.4 Impacto en Frontend (Livewire Components)

**StandardList:**
- **Beneficio:** Listado mas limpio (solo un estandar activo por parte)
- **Cambio requerido:** Ningun cambio necesario en logica
- **Mejora opcional:** Agregar indicador visual si existen estandares inactivos (historial)

**StandardCreate:**
- **Cambio requerido:** Validacion adicional antes de `create()`
- **UX:** Mensaje claro si intenta crear duplicado activo
- **Sugerencia:** Mostrar estandar activo existente si intenta duplicar

**StandardEdit:**
- **Cambio requerido:** Validacion que permita editar registro actual
- **Regla:** `unique:standards,part_id,{id}` (ignore actual)
- **Consideracion adicional:** Al cambiar `part_id`, validar que nuevo part_id no tenga estandar activo

### 4.5 Impacto en Datos Existentes

**Registros Afectados:** 5 estandares duplicados

**Decision de Negocio Requerida:**

**Opcion A: Mantener Mas Reciente**
```sql
-- Desactivar duplicados antiguos
UPDATE standards s1
SET active = 0
WHERE part_id IN (22, 18)
  AND id NOT IN (
      SELECT MAX(id) FROM standards s2 WHERE s2.part_id = s1.part_id AND active = 1
  );
```

**Opcion B: Mantener con Mayor units_per_hour**
```sql
-- Mantener el de mayor capacidad
UPDATE standards s1
SET active = 0
WHERE part_id IN (22, 18)
  AND id NOT IN (
      SELECT id FROM (
          SELECT id FROM standards s2
          WHERE s2.part_id = s1.part_id AND active = 1
          ORDER BY units_per_hour DESC
          LIMIT 1
      ) tmp
  );
```

**Opcion C: Revision Manual**
- Revisar con Production Manager cual estandar es correcto
- Desactivar manualmente los incorrectos
- **Recomendado** para casos de negocio critico

---

## 5. Consideraciones de Negocio

### 5.1 Pregunta Critica: ¿Unicidad Estricta o Historial?

**Opcion 1: Unicidad Estricta**
```sql
UNIQUE KEY (part_id) -- Solo UN estandar por parte, sin importar estado
```

**Ventajas:**
- Simplicidad maxima
- Imposible tener duplicados
- No requiere campo `active`

**Desventajas:**
- NO permite historial de cambios
- No permite planificar estandares futuros
- Al actualizar estandar, se pierde configuracion anterior
- Dificil auditoría de cambios

**Opcion 2: Un Activo, Multiples Inactivos (Historial)**
```sql
-- Conceptual (MySQL 8.0+):
UNIQUE KEY (part_id, active) WHERE active = 1
-- O validacion en aplicacion
```

**Ventajas:**
- **Historial completo** de cambios en estandares
- Permite tener estandares futuros (con `effective_date` posterior)
- Auditoria y trazabilidad
- Rollback facil (reactivar estandar anterior)

**Desventajas:**
- Mas complejo de implementar
- Requiere validacion adicional en aplicacion

### 5.2 Rol del Campo `effective_date`

**Pregunta:** ¿El `effective_date` debe influir en la unicidad?

**Escenario de Negocio:**

**Caso de Uso: Planificacion Futura**
```
Part: STS H-M-4
Standard A: Active=true, effective_date=2026-01-01, units_per_hour=100
Standard B: Active=true, effective_date=2026-06-01, units_per_hour=150
```

**Interpretacion:**
- Standard A es el actual (vigente desde 2026-01-01)
- Standard B es el futuro (tomara efecto el 2026-06-01)
- **Pregunta:** ¿Deberia permitirse tener ambos activos si tienen fechas distintas?

**Analisis:**

**Opcion A: Unicidad en (part_id, active) sin considerar effective_date**
- Solo UN estandar activo a la vez
- Para cambiar estandar, desactivar el anterior y activar el nuevo
- **Simplicidad:** SI
- **Flexibilidad para futuro:** NO

**Opcion B: Unicidad en (part_id, effective_date, active)**
- Permite multiples estandares activos con fechas efectivas distintas
- Sistema usa el estandar cuya `effective_date <= today()`
- **Complejidad:** ALTA (requiere logica adicional en queries)
- **Flexibilidad:** MAXIMA

**Opcion C: Solo un activo + campo status adicional**
```php
'status' => ['draft', 'active', 'scheduled', 'archived']
```
- Un estandar `active` (el actual)
- Multiples `scheduled` (futuros)
- Multiples `archived` (historicos)
- **Balance:** Complejidad media, flexibilidad alta

### 5.3 Recomendacion de Negocio

**Opcion Recomendada: Opcion 2 (Un Activo, Multiples Inactivos)**

**Razon:**
1. **Auditoria:** CRITICO para entorno de manufactura
2. **Historial:** Permite ver como ha evolucionado el proceso
3. **Rollback:** Facil revertir a configuracion anterior
4. **Compliance:** Cumple requisitos de trazabilidad

**Implementacion:**
- Constraint: Solo UN estandar con `active=1` por `part_id`
- Permitir MULTIPLES con `active=0` (historial)
- Campo `effective_date` es informativo, NO restrictivo

**Regla de Oro:**
> "Una parte puede tener muchos estandares en su historial, pero solo UNO puede estar activo en cualquier momento dado."

### 5.4 Campo `active` vs `status`

**Actual:**
```php
'active' => 'boolean' // true/false
```

**Alternativa (NO recomendada para esta fase):**
```php
'status' => 'enum' // ['draft', 'active', 'scheduled', 'archived']
```

**Decision:** **Mantener campo `active` boolean**

**Razon:**
- Codigo existente ya usa `active`
- Cambiar a `status` requiere refactoring masivo
- Boolean es suficiente para caso de uso actual
- Puede agregarse `status` en futuro sin romper `active`

---

## 6. Diseño de la Solucion

### 6.1 Arquitectura de Validacion en Capas

**Capa 1: Base de Datos (Opcional - Dependiente de MySQL version)**

MySQL 8.0+ soporta indices funcionales:
```sql
-- NO IMPLEMENTAR si MySQL < 8.0
CREATE UNIQUE INDEX unique_active_standard
ON standards (part_id, active)
WHERE active = 1;
```

**Problema:** MySQL 5.7 (comun en XAMPP) NO soporta `WHERE` en indices.

**Alternativa para MySQL 5.7:**
- Crear indice compuesto normal
- Validar en triggers (complejidad alta, NO recomendado)
- **O validar solo en capa de aplicacion** (RECOMENDADO)

**Capa 2: Modelo Laravel (Opcional)**

Agregar validation rules a modelo:
```php
// app/Models/Standard.php
public static function boot()
{
    parent::boot();

    static::creating(function ($standard) {
        if ($standard->active) {
            $exists = static::where('part_id', $standard->part_id)
                ->where('active', true)
                ->exists();

            if ($exists) {
                throw new \Exception("Ya existe un estandar activo para esta parte");
            }
        }
    });

    static::updating(function ($standard) {
        if ($standard->active && $standard->isDirty('active')) {
            $exists = static::where('part_id', $standard->part_id)
                ->where('active', true)
                ->where('id', '!=', $standard->id)
                ->exists();

            if ($exists) {
                throw new \Exception("Ya existe un estandar activo para esta parte");
            }
        }
    });
}
```

**Problema:** Exceptions en boot hooks no generan mensajes de validacion user-friendly.

**Capa 3: Componentes Livewire (RECOMENDADO - Principal)**

Validacion en `StandardCreate` y `StandardEdit`:
```php
// Custom validation rule
protected function rules(): array
{
    return [
        'part_id' => [
            'required',
            'exists:parts,id',
            // Custom rule en CREATE
            function ($attribute, $value, $fail) {
                if ($this->active) {
                    $exists = Standard::where('part_id', $value)
                        ->where('active', true)
                        ->exists();

                    if ($exists) {
                        $part = Part::find($value);
                        $fail("Ya existe un estandar activo para la parte {$part->number}. Desactive el estandar existente primero.");
                    }
                }
            }
        ],
        'units_per_hour' => 'required|integer|min:1',
        // ... resto
    ];
}
```

**Capa 4: Constraint Base de Datos (RECOMENDADO - Respaldo)**

Para MySQL 5.7, crear constraint usando trigger:
```sql
-- Trigger de validacion (opcional, para seguridad adicional)
DELIMITER $$
CREATE TRIGGER check_unique_active_standard_insert
BEFORE INSERT ON standards
FOR EACH ROW
BEGIN
    DECLARE active_count INT;

    IF NEW.active = 1 THEN
        SELECT COUNT(*) INTO active_count
        FROM standards
        WHERE part_id = NEW.part_id
          AND active = 1
          AND deleted_at IS NULL;

        IF active_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Ya existe un estandar activo para esta parte';
        END IF;
    END IF;
END$$

CREATE TRIGGER check_unique_active_standard_update
BEFORE UPDATE ON standards
FOR EACH ROW
BEGIN
    DECLARE active_count INT;

    IF NEW.active = 1 THEN
        SELECT COUNT(*) INTO active_count
        FROM standards
        WHERE part_id = NEW.part_id
          AND active = 1
          AND deleted_at IS NULL
          AND id != NEW.id;

        IF active_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Ya existe un estandar activo para esta parte';
        END IF;
    END IF;
END$$
DELIMITER ;
```

**Nota:** Los triggers son opcionales y agregan complejidad. **Priorizar validacion en Livewire.**

### 6.2 Solucion Elegida: Validacion en Livewire + Unique Index Compuesto

**Estrategia:**

1. **Validacion principal en Livewire** (StandardCreate, StandardEdit)
2. **Indice compuesto** en BD para performance (NO unique, solo index)
3. **Metodos helper en modelo** para facilitar queries
4. **NO usar triggers** (excesiva complejidad)

**Ventajas:**
- Mensajes de error user-friendly en UI
- Performance optima con indice compuesto
- Facil de mantener y testear
- Compatible con MySQL 5.7 y 8.0+

### 6.3 Estructura de Base de Datos Modificada

**Nueva Migracion:**
```php
// database/migrations/2026_01_12_add_unique_active_standard_validation.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standards', function (Blueprint $table) {
            // Agregar indice compuesto para optimizar query de validacion
            // NO es UNIQUE porque MySQL 5.7 no soporta partial unique index
            // La unicidad se valida en capa de aplicacion
            $table->index(['part_id', 'active', 'deleted_at'], 'standards_part_active_validation');
        });
    }

    public function down(): void
    {
        Schema::table('standards', function (Blueprint $table) {
            $table->dropIndex('standards_part_active_validation');
        });
    }
};
```

**Comentarios:**
- Indice compuesto para acelerar query de validacion
- Incluye `deleted_at` para considerar soft deletes
- NO es `unique()` porque MySQL 5.7 no permite `WHERE` clause

### 6.4 Metodos Helper en Modelo Standard

**Agregar a app/Models/Standard.php:**

```php
/**
 * Scope para obtener el estandar activo de una parte
 *
 * @param \Illuminate\Database\Eloquent\Builder $query
 * @param int $partId
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeActiveForPart($query, int $partId)
{
    return $query->where('part_id', $partId)
                 ->where('active', true)
                 ->orderBy('effective_date', 'desc')
                 ->orderBy('id', 'desc');
}

/**
 * Verifica si existe otro estandar activo para la misma parte
 *
 * @param int $partId
 * @param int|null $exceptId ID del registro a excluir (para updates)
 * @return bool
 */
public static function hasActiveStandardForPart(int $partId, ?int $exceptId = null): bool
{
    $query = static::where('part_id', $partId)
                   ->where('active', true);

    if ($exceptId) {
        $query->where('id', '!=', $exceptId);
    }

    return $query->exists();
}

/**
 * Desactiva todos los estandares de una parte excepto el especificado
 *
 * @param int $partId
 * @param int $exceptId ID del estandar que debe quedar activo
 * @return int Numero de registros actualizados
 */
public static function deactivateOthersForPart(int $partId, int $exceptId): int
{
    return static::where('part_id', $partId)
                 ->where('id', '!=', $exceptId)
                 ->where('active', true)
                 ->update(['active' => false]);
}

/**
 * Obtiene el estandar activo actual para una parte
 * (considera effective_date)
 *
 * @param int $partId
 * @return Standard|null
 */
public static function getCurrentStandardForPart(int $partId): ?Standard
{
    return static::where('part_id', $partId)
                 ->where('active', true)
                 ->where(function($query) {
                     $query->whereNull('effective_date')
                           ->orWhere('effective_date', '<=', now());
                 })
                 ->orderBy('effective_date', 'desc')
                 ->first();
}

/**
 * Activa este estandar y desactiva todos los demas de la misma parte
 *
 * @return bool
 */
public function activateAsOnly(): bool
{
    return DB::transaction(function () {
        // Desactivar otros
        static::where('part_id', $this->part_id)
             ->where('id', '!=', $this->id)
             ->where('active', true)
             ->update(['active' => false]);

        // Activar este
        $this->active = true;
        return $this->save();
    });
}
```

---

## 7. Plan de Implementacion

### 7.1 Fases de Implementacion

**Fase 1: Preparacion (Analisis Completado)**
- [x] Analisis de estado actual
- [x] Deteccion de duplicados existentes
- [x] Diseño de solucion
- [x] Documento de especificacion

**Fase 2: Migracion de Datos**
- [ ] Identificar duplicados con Production Manager
- [ ] Decidir cual estandar mantener activo por parte
- [ ] Ejecutar script de desactivacion de duplicados
- [ ] Validar que cada parte tenga solo un estandar activo

**Fase 3: Implementacion Backend**
- [ ] Crear migracion para indice compuesto
- [ ] Agregar metodos helper a modelo Standard
- [ ] Actualizar CapacityCalculatorService (logging opcional)
- [ ] Ejecutar tests unitarios

**Fase 4: Implementacion Frontend**
- [ ] Actualizar StandardCreate con validacion
- [ ] Actualizar StandardEdit con validacion
- [ ] Agregar mensajes de error user-friendly
- [ ] Agregar UI hint mostrando estandar activo existente

**Fase 5: Testing**
- [ ] Tests unitarios de metodos helper
- [ ] Tests de feature para validacion
- [ ] Tests de integracion en CapacityCalculator
- [ ] Tests de UI en Livewire components

**Fase 6: Deployment**
- [ ] Ejecutar migraciones en staging
- [ ] Validar en staging con datos reales
- [ ] Deploy a produccion
- [ ] Monitorear logs por errores

### 7.2 Orden de Implementacion Detallado

**Step 1: Limpieza de Datos (MANUAL, CRITICO)**

```sql
-- 1. Backup de tabla standards
CREATE TABLE standards_backup_20260112 AS SELECT * FROM standards;

-- 2. Identificar duplicados
SELECT
    part_id,
    COUNT(*) as count,
    GROUP_CONCAT(id ORDER BY id) as standard_ids,
    GROUP_CONCAT(units_per_hour ORDER BY id) as capacities
FROM standards
WHERE active = 1 AND deleted_at IS NULL
GROUP BY part_id
HAVING count > 1;

-- 3. Desactivar duplicados (REVISAR CON PRODUCTION MANAGER)
-- Ejemplo para Part 22: Mantener ID 18 (mayor capacidad), desactivar 20 y 21
UPDATE standards SET active = 0 WHERE id IN (20, 21);

-- Ejemplo para Part 18: Mantener ID 17 (maquina mas moderna), desactivar 14
UPDATE standards SET active = 0 WHERE id IN (14);

-- 4. Validar que no queden duplicados
SELECT
    part_id,
    COUNT(*) as count
FROM standards
WHERE active = 1 AND deleted_at IS NULL
GROUP BY part_id
HAVING count > 1;
-- Resultado esperado: 0 rows
```

**Step 2: Crear Migracion de Indice**

```bash
php artisan make:migration add_part_active_validation_index_to_standards_table
```

**Contenido:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('standards', function (Blueprint $table) {
            // Indice compuesto para optimizar validacion de unicidad
            // Incluye deleted_at para considerar soft deletes
            $table->index(
                ['part_id', 'active', 'deleted_at'],
                'standards_part_active_validation_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('standards', function (Blueprint $table) {
            $table->dropIndex('standards_part_active_validation_idx');
        });
    }
};
```

**Step 3: Actualizar Modelo Standard**

Agregar metodos helper (codigo en seccion 6.4).

**Step 4: Crear Custom Validation Rule**

```bash
php artisan make:rule UniqueActiveStandard
```

**Contenido:**
```php
<?php

namespace App\Rules;

use App\Models\Standard;
use App\Models\Part;
use Illuminate\Contracts\Validation\Rule;

class UniqueActiveStandard implements Rule
{
    protected ?int $exceptId;
    protected bool $isActive;
    protected ?string $partNumber = null;

    public function __construct(bool $isActive, ?int $exceptId = null)
    {
        $this->isActive = $isActive;
        $this->exceptId = $exceptId;
    }

    public function passes($attribute, $value): bool
    {
        // Solo validar si el estandar sera activo
        if (!$this->isActive) {
            return true;
        }

        // Guardar numero de parte para mensaje de error
        $part = Part::find($value);
        $this->partNumber = $part ? $part->number : 'N/A';

        // Verificar si existe otro estandar activo
        return !Standard::hasActiveStandardForPart($value, $this->exceptId);
    }

    public function message(): string
    {
        return "Ya existe un estandar activo para la parte {$this->partNumber}. " .
               "Por favor, desactive el estandar existente antes de crear uno nuevo, " .
               "o marque este estandar como inactivo para agregarlo al historial.";
    }
}
```

**Step 5: Actualizar StandardCreate Component**

```php
<?php

namespace App\Livewire\Admin\Standards;

use App\Models\Machine;
use App\Models\Part;
use App\Models\Semi_Automatic;
use App\Models\Standard;
use App\Models\Table;
use App\Rules\UniqueActiveStandard;
use Livewire\Component;

class StandardCreate extends Component
{
    public ?int $part_id = null;
    public string $units_per_hour = '';
    public ?int $work_table_id = null;
    public ?int $semi_auto_work_table_id = null;
    public ?int $machine_id = null;
    public string $persons_1 = '';
    public string $persons_2 = '';
    public string $persons_3 = '';
    public string $effective_date = '';
    public bool $active = true;
    public string $description = '';

    // Nueva propiedad para mostrar advertencia
    public ?Standard $existingActiveStandard = null;

    public function mount(): void
    {
        $this->effective_date = now()->format('Y-m-d');
    }

    public function updatedPartId($value): void
    {
        // Cuando cambia part_id, verificar si existe estandar activo
        if ($value && $this->active) {
            $this->existingActiveStandard = Standard::activeForPart($value)->first();
        } else {
            $this->existingActiveStandard = null;
        }
    }

    public function updatedActive($value): void
    {
        // Cuando cambia active, verificar si hay conflicto
        if ($value && $this->part_id) {
            $this->existingActiveStandard = Standard::activeForPart($this->part_id)->first();
        } else {
            $this->existingActiveStandard = null;
        }
    }

    protected function rules(): array
    {
        return [
            'part_id' => [
                'required',
                'exists:parts,id',
                new UniqueActiveStandard($this->active)
            ],
            'units_per_hour' => 'required|integer|min:1',
            'work_table_id' => 'nullable|exists:tables,id',
            'semi_auto_work_table_id' => 'nullable|exists:semi__automatics,id',
            'machine_id' => 'nullable|exists:machines,id',
            'persons_1' => 'nullable|integer|min:1',
            'persons_2' => 'nullable|integer|min:1',
            'persons_3' => 'nullable|integer|min:1',
            'effective_date' => 'nullable|date',
            'active' => 'boolean',
            'description' => 'nullable|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'part_id.required' => 'Debe seleccionar una parte.',
            'part_id.exists' => 'La parte seleccionada no existe.',
            'units_per_hour.required' => 'Las unidades por hora son obligatorias.',
            'units_per_hour.integer' => 'Las unidades por hora deben ser un número entero.',
            'units_per_hour.min' => 'Las unidades por hora deben ser al menos 1.',
            'work_table_id.exists' => 'La mesa de trabajo seleccionada no existe.',
            'semi_auto_work_table_id.exists' => 'La mesa semi-automática seleccionada no existe.',
            'machine_id.exists' => 'La máquina seleccionada no existe.',
            'persons_1.integer' => 'El campo Personas 1 debe ser un número entero.',
            'persons_1.min' => 'El campo Personas 1 debe ser al menos 1.',
            'persons_2.integer' => 'El campo Personas 2 debe ser un número entero.',
            'persons_2.min' => 'El campo Personas 2 debe ser al menos 1.',
            'persons_3.integer' => 'El campo Personas 3 debe ser un número entero.',
            'persons_3.min' => 'El campo Personas 3 debe ser al menos 1.',
            'effective_date.date' => 'La fecha efectiva no es válida.',
        ];
    }

    public function saveStandard(): void
    {
        $this->validate();

        Standard::create([
            'part_id' => $this->part_id,
            'units_per_hour' => $this->units_per_hour,
            'work_table_id' => $this->work_table_id ?: null,
            'semi_auto_work_table_id' => $this->semi_auto_work_table_id ?: null,
            'machine_id' => $this->machine_id ?: null,
            'persons_1' => $this->persons_1 ?: null,
            'persons_2' => $this->persons_2 ?: null,
            'persons_3' => $this->persons_3 ?: null,
            'effective_date' => $this->effective_date ?: null,
            'active' => $this->active,
            'description' => $this->description ?: null,
        ]);

        session()->flash('flash.banner', 'Estándar creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.standards.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.standards.standard-create', [
            'parts' => Part::orderBy('number')->get(),
            'workTables' => Table::active()->orderBy('number')->get(),
            'semiAutoWorkTables' => Semi_Automatic::active()->orderBy('number')->get(),
            'machines' => Machine::active()->orderBy('name')->get(),
        ]);
    }
}
```

**Step 6: Actualizar StandardEdit Component**

```php
<?php

namespace App\Livewire\Admin\Standards;

use App\Models\Machine;
use App\Models\Part;
use App\Models\Semi_Automatic;
use App\Models\Standard;
use App\Models\Table;
use App\Rules\UniqueActiveStandard;
use Livewire\Component;

class StandardEdit extends Component
{
    public Standard $standard;
    public ?int $part_id = null;
    public string $units_per_hour = '';
    public ?int $work_table_id = null;
    public ?int $semi_auto_work_table_id = null;
    public ?int $machine_id = null;
    public string $persons_1 = '';
    public string $persons_2 = '';
    public string $persons_3 = '';
    public string $effective_date = '';
    public bool $active = true;
    public string $description = '';

    // Nueva propiedad para advertencia
    public ?Standard $existingActiveStandard = null;

    public function mount(Standard $standard): void
    {
        $this->standard = $standard;
        $this->part_id = $standard->part_id;
        $this->units_per_hour = $standard->units_per_hour ? (string) $standard->units_per_hour : '';
        $this->work_table_id = $standard->work_table_id;
        $this->semi_auto_work_table_id = $standard->semi_auto_work_table_id;
        $this->machine_id = $standard->machine_id;
        $this->persons_1 = $standard->persons_1 ? (string) $standard->persons_1 : '';
        $this->persons_2 = $standard->persons_2 ? (string) $standard->persons_2 : '';
        $this->persons_3 = $standard->persons_3 ? (string) $standard->persons_3 : '';
        $this->effective_date = $standard->effective_date ? $standard->effective_date->format('Y-m-d') : '';
        $this->active = $standard->active;
        $this->description = $standard->description ?? '';
    }

    public function updatedPartId($value): void
    {
        // Verificar si existe otro estandar activo (excluyendo este)
        if ($value && $this->active && $value != $this->standard->part_id) {
            $this->existingActiveStandard = Standard::activeForPart($value)->first();
        } else {
            $this->existingActiveStandard = null;
        }
    }

    public function updatedActive($value): void
    {
        // Verificar conflicto al activar
        if ($value && $this->part_id) {
            $existing = Standard::where('part_id', $this->part_id)
                ->where('active', true)
                ->where('id', '!=', $this->standard->id)
                ->first();

            $this->existingActiveStandard = $existing;
        } else {
            $this->existingActiveStandard = null;
        }
    }

    protected function rules(): array
    {
        return [
            'part_id' => [
                'required',
                'exists:parts,id',
                new UniqueActiveStandard($this->active, $this->standard->id)
            ],
            'units_per_hour' => 'required|integer|min:1',
            'work_table_id' => 'nullable|exists:tables,id',
            'semi_auto_work_table_id' => 'nullable|exists:semi__automatics,id',
            'machine_id' => 'nullable|exists:machines,id',
            'persons_1' => 'nullable|integer|min:1',
            'persons_2' => 'nullable|integer|min:1',
            'persons_3' => 'nullable|integer|min:1',
            'effective_date' => 'nullable|date',
            'active' => 'boolean',
            'description' => 'nullable|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'part_id.required' => 'Debe seleccionar una parte.',
            'part_id.exists' => 'La parte seleccionada no existe.',
            'units_per_hour.required' => 'Las unidades por hora son obligatorias.',
            'units_per_hour.integer' => 'Las unidades por hora deben ser un número entero.',
            'units_per_hour.min' => 'Las unidades por hora deben ser al menos 1.',
            'work_table_id.exists' => 'La mesa de trabajo seleccionada no existe.',
            'semi_auto_work_table_id.exists' => 'La mesa semi-automática seleccionada no existe.',
            'machine_id.exists' => 'La máquina seleccionada no existe.',
            'persons_1.integer' => 'El campo Personas 1 debe ser un número entero.',
            'persons_1.min' => 'El campo Personas 1 debe ser al menos 1.',
            'persons_2.integer' => 'El campo Personas 2 debe ser un número entero.',
            'persons_2.min' => 'El campo Personas 2 debe ser al menos 1.',
            'persons_3.integer' => 'El campo Personas 3 debe ser un número entero.',
            'persons_3.min' => 'El campo Personas 3 debe ser al menos 1.',
            'effective_date.date' => 'La fecha efectiva no es válida.',
        ];
    }

    public function updateStandard(): void
    {
        $this->validate();

        $this->standard->update([
            'part_id' => $this->part_id,
            'units_per_hour' => $this->units_per_hour,
            'work_table_id' => $this->work_table_id ?: null,
            'semi_auto_work_table_id' => $this->semi_auto_work_table_id ?: null,
            'machine_id' => $this->machine_id ?: null,
            'persons_1' => $this->persons_1 ?: null,
            'persons_2' => $this->persons_2 ?: null,
            'persons_3' => $this->persons_3 ?: null,
            'effective_date' => $this->effective_date ?: null,
            'active' => $this->active,
            'description' => $this->description ?: null,
        ]);

        session()->flash('flash.banner', 'Estándar actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.standards.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.standards.standard-edit', [
            'parts' => Part::orderBy('number')->get(),
            'workTables' => Table::active()->orderBy('number')->get(),
            'semiAutoWorkTables' => Semi_Automatic::active()->orderBy('number')->get(),
            'machines' => Machine::active()->orderBy('name')->get(),
        ]);
    }
}
```

**Step 7: Actualizar Vista Blade (Opcional - Warning UI)**

En `standard-create.blade.php` y `standard-edit.blade.php`:

```blade
{{-- Warning si existe estandar activo --}}
@if($existingActiveStandard && $active)
    <div class="mb-4 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">
                    Ya existe un estándar activo para esta parte:
                </p>
                <ul class="mt-2 text-sm">
                    <li><strong>Estándar ID:</strong> {{ $existingActiveStandard->id }}</li>
                    <li><strong>Unidades/Hora:</strong> {{ $existingActiveStandard->units_per_hour }}</li>
                    <li><strong>Estación:</strong> {{ $existingActiveStandard->workstation_name }}</li>
                </ul>
                <p class="mt-2 text-sm">
                    Para crear un nuevo estándar activo, primero desactive el existente o marque este como inactivo.
                </p>
            </div>
        </div>
    </div>
@endif
```

---

## 8. Migracion de Datos

### 8.1 Script de Identificacion de Duplicados

**Archivo:** `database/scripts/identify_duplicate_standards.sql`

```sql
-- ============================================================
-- Script: Identificacion de Standards Duplicados
-- Proposito: Encontrar parts con multiples estandares activos
-- Autor: Agent Architect
-- Fecha: 2026-01-12
-- ============================================================

-- 1. Backup de seguridad
CREATE TABLE IF NOT EXISTS standards_backup_20260112 AS
SELECT * FROM standards;

-- 2. Identificar duplicados activos
SELECT
    s.part_id,
    p.number AS part_number,
    p.description AS part_description,
    COUNT(*) AS active_standards_count,
    GROUP_CONCAT(s.id ORDER BY s.id SEPARATOR ', ') AS standard_ids,
    GROUP_CONCAT(s.units_per_hour ORDER BY s.id SEPARATOR ', ') AS units_per_hour_values,
    GROUP_CONCAT(s.created_at ORDER BY s.id SEPARATOR ', ') AS created_dates
FROM standards s
INNER JOIN parts p ON s.part_id = p.id
WHERE s.active = 1
  AND s.deleted_at IS NULL
GROUP BY s.part_id, p.number, p.description
HAVING COUNT(*) > 1
ORDER BY active_standards_count DESC, s.part_id;

-- 3. Detalles completos de cada duplicado
SELECT
    s.id AS standard_id,
    s.part_id,
    p.number AS part_number,
    s.units_per_hour,
    s.active,
    s.effective_date,
    s.created_at,
    COALESCE(t.number, sa.number, m.name) AS workstation,
    CASE
        WHEN s.work_table_id IS NOT NULL THEN 'Manual Table'
        WHEN s.semi_auto_work_table_id IS NOT NULL THEN 'Semi-Automatic'
        WHEN s.machine_id IS NOT NULL THEN 'Machine'
        ELSE 'No Workstation'
    END AS workstation_type
FROM standards s
INNER JOIN parts p ON s.part_id = p.id
LEFT JOIN tables t ON s.work_table_id = t.id
LEFT JOIN semi__automatics sa ON s.semi_auto_work_table_id = sa.id
LEFT JOIN machines m ON s.machine_id = m.id
WHERE s.part_id IN (
    SELECT part_id
    FROM standards
    WHERE active = 1 AND deleted_at IS NULL
    GROUP BY part_id
    HAVING COUNT(*) > 1
)
  AND s.active = 1
  AND s.deleted_at IS NULL
ORDER BY s.part_id, s.created_at;

-- 4. Estadisticas generales
SELECT
    'Total Standards' AS metric,
    COUNT(*) AS value
FROM standards
WHERE deleted_at IS NULL
UNION ALL
SELECT
    'Active Standards' AS metric,
    COUNT(*) AS value
FROM standards
WHERE active = 1 AND deleted_at IS NULL
UNION ALL
SELECT
    'Parts with Duplicates' AS metric,
    COUNT(DISTINCT part_id) AS value
FROM (
    SELECT part_id
    FROM standards
    WHERE active = 1 AND deleted_at IS NULL
    GROUP BY part_id
    HAVING COUNT(*) > 1
) AS duplicates;
```

### 8.2 Script de Limpieza - Estrategia Automatica

**Archivo:** `database/scripts/cleanup_duplicate_standards_auto.sql`

```sql
-- ============================================================
-- Script: Limpieza Automatica de Duplicados
-- Estrategia: Mantener el mas reciente (mayor ID)
-- WARNING: REVISAR ANTES DE EJECUTAR EN PRODUCCION
-- ============================================================

-- 1. Verificar duplicados antes de limpiar
SELECT
    part_id,
    COUNT(*) as duplicates
FROM standards
WHERE active = 1 AND deleted_at IS NULL
GROUP BY part_id
HAVING COUNT(*) > 1;

-- 2. Desactivar duplicados (mantener el mas reciente)
UPDATE standards s1
SET
    s1.active = 0,
    s1.updated_at = NOW()
WHERE s1.active = 1
  AND s1.deleted_at IS NULL
  AND s1.part_id IN (
      -- Parts con duplicados
      SELECT part_id
      FROM (
          SELECT part_id
          FROM standards
          WHERE active = 1 AND deleted_at IS NULL
          GROUP BY part_id
          HAVING COUNT(*) > 1
      ) AS dups
  )
  AND s1.id NOT IN (
      -- Mantener solo el de mayor ID (mas reciente)
      SELECT MAX(id)
      FROM standards s2
      WHERE s2.part_id = s1.part_id
        AND s2.active = 1
        AND s2.deleted_at IS NULL
  );

-- 3. Verificar resultado
SELECT
    part_id,
    COUNT(*) as remaining_active
FROM standards
WHERE active = 1 AND deleted_at IS NULL
GROUP BY part_id
HAVING COUNT(*) > 1;
-- Resultado esperado: 0 rows

-- 4. Log de cambios realizados
SELECT
    'Standards desactivados' AS action,
    COUNT(*) AS affected_rows
FROM standards
WHERE active = 0
  AND updated_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

### 8.3 Script de Limpieza - Estrategia Manual

**Archivo:** `database/scripts/cleanup_duplicate_standards_manual.sql`

```sql
-- ============================================================
-- Script: Limpieza Manual de Duplicados Especificos
-- Proposito: Desactivar estandares especificos identificados
-- ============================================================

-- Caso 1: Part ID 22 (STS H-M-4)
-- Decision: Mantener Standard ID 18 (mayor capacidad: 166 units/hour)
-- Desactivar: Standards ID 20 y 21 (43 units/hour, duplicados accidentales)

UPDATE standards
SET
    active = 0,
    description = CONCAT(
        COALESCE(description, ''),
        ' [DESACTIVADO: Duplicado detectado el 2026-01-12]'
    ),
    updated_at = NOW()
WHERE id IN (20, 21)
  AND part_id = 22;

-- Caso 2: Part ID 18 (STS H-C-3)
-- Decision: Mantener Standard ID 17 (Maquina: Haas - VF-4)
-- Desactivar: Standard ID 14 (Manual Table MT-003)
-- Razon: Preferencia por proceso automatizado

UPDATE standards
SET
    active = 0,
    description = CONCAT(
        COALESCE(description, ''),
        ' [DESACTIVADO: Proceso migrado a maquina CNC]'
    ),
    updated_at = NOW()
WHERE id = 14
  AND part_id = 18;

-- Verificacion final
SELECT
    s.id,
    s.part_id,
    p.number AS part_number,
    s.active,
    s.units_per_hour,
    s.description
FROM standards s
INNER JOIN parts p ON s.part_id = p.id
WHERE s.part_id IN (22, 18)
ORDER BY s.part_id, s.active DESC, s.id;
```

### 8.4 Rollback Plan

```sql
-- ============================================================
-- Rollback: Restaurar Standards desde Backup
-- USO: Solo si la limpieza falla o fue incorrecta
-- ============================================================

-- 1. Verificar existencia del backup
SELECT COUNT(*) FROM standards_backup_20260112;

-- 2. Restaurar registros especificos
UPDATE standards s
INNER JOIN standards_backup_20260112 b ON s.id = b.id
SET
    s.active = b.active,
    s.description = b.description,
    s.updated_at = NOW()
WHERE s.id IN (14, 20, 21); -- IDs que fueron modificados

-- 3. Verificar restauracion
SELECT
    'Restored' AS status,
    COUNT(*) AS count
FROM standards s
INNER JOIN standards_backup_20260112 b ON s.id = b.id
WHERE s.active = b.active
  AND s.id IN (14, 20, 21);
```

---

## 9. Validaciones en Capa de Aplicacion

### 9.1 Custom Validation Rule

**Archivo:** `app/Rules/UniqueActiveStandard.php`

(Codigo completo en seccion 7.2, Step 4)

**Uso:**
```php
'part_id' => [
    'required',
    'exists:parts,id',
    new UniqueActiveStandard($this->active, $exceptId)
]
```

### 9.2 Validacion en Livewire (Real-time)

**Propiedad Computed en StandardCreate:**

```php
public function getConflictingStandardProperty()
{
    if (!$this->part_id || !$this->active) {
        return null;
    }

    return Standard::where('part_id', $this->part_id)
        ->where('active', true)
        ->with(['part', 'workTable', 'semiAutoWorkTable', 'machine'])
        ->first();
}
```

**Uso en Blade:**
```blade
@if($this->conflictingStandard)
    <x-alert type="warning">
        Ya existe un estándar activo para {{ $this->conflictingStandard->part->number }}
    </x-alert>
@endif
```

### 9.3 Validacion en Modelo (Boot Events)

**Agregar a Standard.php:**

```php
protected static function booted(): void
{
    // Validacion adicional al crear
    static::creating(function (Standard $standard) {
        if ($standard->active) {
            $exists = static::hasActiveStandardForPart($standard->part_id);

            if ($exists) {
                throw new \RuntimeException(
                    "Ya existe un estándar activo para la parte ID {$standard->part_id}. " .
                    "Desactive el estándar existente antes de crear uno nuevo."
                );
            }
        }
    });

    // Validacion adicional al actualizar
    static::updating(function (Standard $standard) {
        if ($standard->active && $standard->isDirty('active')) {
            $exists = static::where('part_id', $standard->part_id)
                ->where('active', true)
                ->where('id', '!=', $standard->id)
                ->exists();

            if ($exists) {
                throw new \RuntimeException(
                    "Ya existe un estándar activo para la parte ID {$standard->part_id}. " .
                    "Desactive el estándar existente antes de activar este."
                );
            }
        }
    });
}
```

**Nota:** Esta validacion es redundante con la de Livewire, pero actua como "safety net" si se crean estandares via seeders, console commands, o APIs.

---

## 10. Testing

### 10.1 Unit Tests - Modelo Standard

**Archivo:** `tests/Unit/Models/StandardTest.php`

```php
<?php

namespace Tests\Unit\Models;

use App\Models\Part;
use App\Models\Standard;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StandardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_check_if_active_standard_exists_for_part()
    {
        $part = Part::factory()->create();

        // No existe estandar activo
        $this->assertFalse(Standard::hasActiveStandardForPart($part->id));

        // Crear estandar activo
        Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true
        ]);

        // Ahora existe
        $this->assertTrue(Standard::hasActiveStandardForPart($part->id));
    }

    /** @test */
    public function it_can_exclude_specific_standard_when_checking_duplicates()
    {
        $part = Part::factory()->create();
        $standard = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true
        ]);

        // Sin excluir, encuentra el estandar
        $this->assertTrue(Standard::hasActiveStandardForPart($part->id));

        // Excluyendo el mismo, no encuentra duplicados
        $this->assertFalse(Standard::hasActiveStandardForPart($part->id, $standard->id));
    }

    /** @test */
    public function it_can_get_active_standard_for_part()
    {
        $part = Part::factory()->create();
        $standard1 = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => false,
            'created_at' => now()->subDays(2)
        ]);
        $standard2 = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true,
            'created_at' => now()->subDays(1)
        ]);

        $active = Standard::getCurrentStandardForPart($part->id);

        $this->assertNotNull($active);
        $this->assertEquals($standard2->id, $active->id);
    }

    /** @test */
    public function it_can_deactivate_other_standards_for_part()
    {
        $part = Part::factory()->create();

        $standard1 = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true
        ]);
        $standard2 = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true
        ]);
        $standard3 = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true
        ]);

        // Desactivar todos excepto standard2
        $deactivated = Standard::deactivateOthersForPart($part->id, $standard2->id);

        $this->assertEquals(2, $deactivated);

        $this->assertFalse($standard1->fresh()->active);
        $this->assertTrue($standard2->fresh()->active);
        $this->assertFalse($standard3->fresh()->active);
    }

    /** @test */
    public function it_respects_soft_deletes_in_active_check()
    {
        $part = Part::factory()->create();
        $standard = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true
        ]);

        $this->assertTrue(Standard::hasActiveStandardForPart($part->id));

        // Soft delete
        $standard->delete();

        // Ya no debe encontrarlo
        $this->assertFalse(Standard::hasActiveStandardForPart($part->id));
    }

    /** @test */
    public function activate_as_only_deactivates_others()
    {
        $part = Part::factory()->create();

        $standard1 = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true
        ]);
        $standard2 = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => false
        ]);

        // Activar standard2 como unico
        $result = $standard2->activateAsOnly();

        $this->assertTrue($result);
        $this->assertFalse($standard1->fresh()->active);
        $this->assertTrue($standard2->fresh()->active);
    }
}
```

### 10.2 Feature Tests - Validacion en Components

**Archivo:** `tests/Feature/Livewire/StandardCreateValidationTest.php`

```php
<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Admin\Standards\StandardCreate;
use App\Models\Part;
use App\Models\Standard;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StandardCreateValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_prevents_creating_duplicate_active_standard()
    {
        $part = Part::factory()->create();
        $table = Table::factory()->create(['active' => true]);

        // Crear primer estandar activo
        Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true,
            'units_per_hour' => 100
        ]);

        // Intentar crear segundo estandar activo
        Livewire::test(StandardCreate::class)
            ->set('part_id', $part->id)
            ->set('units_per_hour', 150)
            ->set('work_table_id', $table->id)
            ->set('active', true)
            ->call('saveStandard')
            ->assertHasErrors('part_id');
    }

    /** @test */
    public function it_allows_creating_inactive_standard_even_if_active_exists()
    {
        $part = Part::factory()->create();
        $table = Table::factory()->create(['active' => true]);

        // Crear primer estandar activo
        Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true,
            'units_per_hour' => 100
        ]);

        // Crear segundo estandar INACTIVO (debe permitirse)
        Livewire::test(StandardCreate::class)
            ->set('part_id', $part->id)
            ->set('units_per_hour', 150)
            ->set('work_table_id', $table->id)
            ->set('active', false)
            ->call('saveStandard')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.standards.index'));

        $this->assertEquals(2, Standard::where('part_id', $part->id)->count());
    }

    /** @test */
    public function it_shows_existing_active_standard_warning()
    {
        $part = Part::factory()->create();
        $existingStandard = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true,
            'units_per_hour' => 100
        ]);

        Livewire::test(StandardCreate::class)
            ->set('part_id', $part->id)
            ->set('active', true)
            ->assertSet('existingActiveStandard.id', $existingStandard->id);
    }

    /** @test */
    public function it_clears_warning_when_setting_inactive()
    {
        $part = Part::factory()->create();
        Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true
        ]);

        Livewire::test(StandardCreate::class)
            ->set('part_id', $part->id)
            ->set('active', true)
            ->assertNotNull('existingActiveStandard')
            ->set('active', false)
            ->assertNull('existingActiveStandard');
    }

    /** @test */
    public function it_allows_creating_active_standard_for_different_part()
    {
        $part1 = Part::factory()->create();
        $part2 = Part::factory()->create();
        $table = Table::factory()->create(['active' => true]);

        Standard::factory()->create([
            'part_id' => $part1->id,
            'active' => true
        ]);

        // Crear estandar para part2 (debe permitirse)
        Livewire::test(StandardCreate::class)
            ->set('part_id', $part2->id)
            ->set('units_per_hour', 200)
            ->set('work_table_id', $table->id)
            ->set('active', true)
            ->call('saveStandard')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.standards.index'));
    }
}
```

**Archivo:** `tests/Feature/Livewire/StandardEditValidationTest.php`

```php
<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Admin\Standards\StandardEdit;
use App\Models\Part;
use App\Models\Standard;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StandardEditValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_allows_updating_standard_without_changing_part()
    {
        $part = Part::factory()->create();
        $standard = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true,
            'units_per_hour' => 100
        ]);

        Livewire::test(StandardEdit::class, ['standard' => $standard])
            ->set('units_per_hour', 150)
            ->call('updateStandard')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.standards.index'));

        $this->assertEquals(150, $standard->fresh()->units_per_hour);
    }

    /** @test */
    public function it_prevents_changing_part_to_one_with_active_standard()
    {
        $part1 = Part::factory()->create();
        $part2 = Part::factory()->create();

        $standard1 = Standard::factory()->create([
            'part_id' => $part1->id,
            'active' => true
        ]);

        $standard2 = Standard::factory()->create([
            'part_id' => $part2->id,
            'active' => true
        ]);

        // Intentar cambiar standard1 a part2 (que ya tiene estandar activo)
        Livewire::test(StandardEdit::class, ['standard' => $standard1])
            ->set('part_id', $part2->id)
            ->call('updateStandard')
            ->assertHasErrors('part_id');
    }

    /** @test */
    public function it_allows_activating_inactive_standard_if_no_active_exists()
    {
        $part = Part::factory()->create();
        $standard = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => false
        ]);

        Livewire::test(StandardEdit::class, ['standard' => $standard])
            ->set('active', true)
            ->call('updateStandard')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.standards.index'));

        $this->assertTrue($standard->fresh()->active);
    }

    /** @test */
    public function it_prevents_activating_if_another_is_already_active()
    {
        $part = Part::factory()->create();

        $standard1 = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true
        ]);

        $standard2 = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => false
        ]);

        // Intentar activar standard2
        Livewire::test(StandardEdit::class, ['standard' => $standard2])
            ->set('active', true)
            ->call('updateStandard')
            ->assertHasErrors('part_id');
    }

    /** @test */
    public function it_allows_deactivating_active_standard()
    {
        $part = Part::factory()->create();
        $standard = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true
        ]);

        Livewire::test(StandardEdit::class, ['standard' => $standard])
            ->set('active', false)
            ->call('updateStandard')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.standards.index'));

        $this->assertFalse($standard->fresh()->active);
    }
}
```

### 10.3 Integration Tests - CapacityCalculatorService

**Archivo:** `tests/Feature/Services/CapacityCalculatorServiceTest.php`

```php
<?php

namespace Tests\Feature\Services;

use App\Models\Part;
use App\Models\Standard;
use App\Services\CapacityCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CapacityCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CapacityCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CapacityCalculatorService();
    }

    /** @test */
    public function it_uses_only_active_standard_for_calculation()
    {
        $part = Part::factory()->create();

        // Estandar inactivo (no debe usarse)
        Standard::factory()->create([
            'part_id' => $part->id,
            'active' => false,
            'units_per_hour' => 50
        ]);

        // Estandar activo (debe usarse)
        Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true,
            'units_per_hour' => 100
        ]);

        $hours = $this->service->calculateRequiredHours($part->id, 1000);

        // 1000 / 100 = 10 horas
        $this->assertEquals(10.00, $hours);
    }

    /** @test */
    public function it_throws_exception_if_no_active_standard_exists()
    {
        $part = Part::factory()->create();

        // Solo estandares inactivos
        Standard::factory()->create([
            'part_id' => $part->id,
            'active' => false
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No active standard found for part {$part->number}");

        $this->service->calculateRequiredHours($part->id, 1000);
    }

    /** @test */
    public function it_handles_multiple_active_standards_gracefully()
    {
        $part = Part::factory()->create(['number' => 'TEST-001']);

        // Crear multiples activos (caso de error)
        $standard1 = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true,
            'units_per_hour' => 100,
            'created_at' => now()->subDays(2)
        ]);

        $standard2 = Standard::factory()->create([
            'part_id' => $part->id,
            'active' => true,
            'units_per_hour' => 200,
            'created_at' => now()->subDays(1)
        ]);

        // Debe usar el primero encontrado (menor ID)
        $hours = $this->service->calculateRequiredHours($part->id, 1000);

        // Deberia usar standard1 (100 units/hour) => 1000 / 100 = 10
        $this->assertEquals(10.00, $hours);
    }
}
```

### 10.4 Test de Migracion

**Archivo:** `tests/Feature/Migrations/UniqueActiveStandardMigrationTest.php`

```php
<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UniqueActiveStandardMigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function migration_creates_validation_index()
    {
        $indexes = Schema::getIndexes('standards');

        $indexNames = array_column($indexes, 'name');

        $this->assertContains(
            'standards_part_active_validation_idx',
            $indexNames,
            'Index standards_part_active_validation_idx should exist'
        );
    }

    /** @test */
    public function validation_index_includes_correct_columns()
    {
        $indexes = Schema::getIndexes('standards');

        $validationIndex = collect($indexes)->firstWhere(
            'name',
            'standards_part_active_validation_idx'
        );

        $this->assertNotNull($validationIndex);
        $this->assertEquals(
            ['part_id', 'active', 'deleted_at'],
            $validationIndex['columns']
        );
    }
}
```

### 10.5 Casos de Prueba Edge Cases

```php
/** @test */
public function it_handles_part_with_no_standards()
{
    $part = Part::factory()->create();

    $this->assertFalse(Standard::hasActiveStandardForPart($part->id));
    $this->assertNull(Standard::getCurrentStandardForPart($part->id));
}

/** @test */
public function it_handles_null_effective_date()
{
    $part = Part::factory()->create();
    $standard = Standard::factory()->create([
        'part_id' => $part->id,
        'active' => true,
        'effective_date' => null
    ]);

    $current = Standard::getCurrentStandardForPart($part->id);
    $this->assertEquals($standard->id, $current->id);
}

/** @test */
public function it_prefers_standard_with_latest_effective_date()
{
    $part = Part::factory()->create();

    // Crear dos activos con fechas distintas (caso hipotetico)
    $standard1 = Standard::factory()->create([
        'part_id' => $part->id,
        'active' => true,
        'effective_date' => now()->subDays(10)
    ]);

    $standard2 = Standard::factory()->create([
        'part_id' => $part->id,
        'active' => true,
        'effective_date' => now()->subDays(5)
    ]);

    $current = Standard::getCurrentStandardForPart($part->id);

    // Debe retornar el mas reciente
    $this->assertEquals($standard2->id, $current->id);
}
```

---

## 11. Documentacion y Mensajes

### 11.1 Mensajes de Error User-Friendly

**Mensaje en Validacion:**
```
"Ya existe un estándar activo para la parte {part_number}. Por favor, desactive el estándar existente antes de crear uno nuevo, o marque este estándar como inactivo para agregarlo al historial."
```

**Mensaje en UI Warning:**
```
Ya existe un estándar activo para esta parte:
- Estándar ID: 18
- Unidades/Hora: 166
- Estación: MT-005

Para crear un nuevo estándar activo, primero desactive el existente o marque este como inactivo.
```

**Mensaje en Exception:**
```
RuntimeException: Ya existe un estándar activo para la parte ID 22. Desactive el estándar existente antes de crear uno nuevo.
```

### 11.2 Documentacion en Codigo

**Docblock en modelo:**
```php
/**
 * Verifica si existe otro estandar activo para la misma parte.
 *
 * Esta validacion es critica para mantener la integridad del sistema
 * de calculo de capacidad. Solo debe existir UN estandar activo por parte
 * en cualquier momento dado.
 *
 * @param int $partId ID de la parte a verificar
 * @param int|null $exceptId ID del registro a excluir (usado en updates)
 * @return bool True si existe otro estandar activo, False en caso contrario
 *
 * @example
 * // Verificar al crear nuevo estandar
 * if (Standard::hasActiveStandardForPart($partId)) {
 *     throw new ValidationException("Duplicado detectado");
 * }
 *
 * // Verificar al editar (excluyendo registro actual)
 * if (Standard::hasActiveStandardForPart($partId, $currentId)) {
 *     throw new ValidationException("Duplicado detectado");
 * }
 */
public static function hasActiveStandardForPart(int $partId, ?int $exceptId = null): bool
```

### 11.3 Comentarios en Migracion

```php
/**
 * Agrega indice compuesto para validacion de unicidad de estandares activos.
 *
 * CONTEXTO:
 * - Un part_id puede tener multiples standards (historial)
 * - Solo UN standard puede estar activo (active=1) a la vez
 * - MySQL 5.7 no soporta indices parciales con WHERE clause
 * - Por eso se valida en capa de aplicacion
 *
 * PROPOSITO DEL INDICE:
 * - Optimizar query de validacion: WHERE part_id=X AND active=1 AND deleted_at IS NULL
 * - Sin este indice, la validacion seria O(n) en tabla completa
 * - Con indice, es O(log n)
 *
 * COLUMNAS:
 * - part_id: Para filtrar por parte
 * - active: Para filtrar solo activos
 * - deleted_at: Para excluir soft-deleted
 */
$table->index(['part_id', 'active', 'deleted_at'], 'standards_part_active_validation_idx');
```

### 11.4 README Section

Agregar a `docs/standards_module.md`:

```markdown
## Validacion de Unicidad de Estandares Activos

### Regla de Negocio

> Una parte puede tener MULTIPLES estandares en su historial, pero solo UNO puede estar ACTIVO en cualquier momento dado.

### Implementacion

La validacion se implementa en multiples capas:

1. **Livewire Components** (StandardCreate, StandardEdit)
   - Validacion custom rule `UniqueActiveStandard`
   - Warning UI mostrando estandar existente
   - Permite crear inactivos aunque exista activo

2. **Modelo Eloquent** (Standard.php)
   - Metodos helper: `hasActiveStandardForPart()`, `activateAsOnly()`
   - Boot events para validacion adicional
   - Scopes: `activeForPart()`

3. **Base de Datos**
   - Indice compuesto: `standards_part_active_validation_idx`
   - Optimiza queries de validacion

### Uso en Codigo

**Verificar duplicados:**
```php
if (Standard::hasActiveStandardForPart($partId)) {
    // Ya existe estandar activo
}
```

**Activar estandar desactivando otros:**
```php
$standard->activateAsOnly(); // Desactiva otros automaticamente
```

**Obtener estandar activo:**
```php
$standard = Standard::getCurrentStandardForPart($partId);
```

### Manejo de Historial

Para mantener historial de cambios:
- Desactivar estandar anterior (`active = 0`)
- Crear nuevo estandar activo (`active = 1`)
- El anterior se mantiene en BD para auditoria
```

---

## 12. Alternativas Evaluadas

### 12.1 Opcion A: Unique Constraint Estricto (DESCARTADA)

**Implementacion:**
```sql
UNIQUE KEY unique_part_id (part_id)
```

**Ventajas:**
- Mas simple
- Garantizado por BD

**Desventajas:**
- NO permite historial
- NO permite estandares inactivos
- Al actualizar, se pierde data anterior
- NO cumple requisitos de auditoria

**Decision:** DESCARTADA - Demasiado restrictiva

### 12.2 Opcion B: Partial Unique Index MySQL 8.0 (NO COMPATIBLE)

**Implementacion:**
```sql
CREATE UNIQUE INDEX unique_active_standard
ON standards (part_id, active)
WHERE active = 1;
```

**Ventajas:**
- Perfecto para caso de uso
- Garantizado por BD

**Desventajas:**
- Requiere MySQL 8.0+
- XAMPP usa MySQL 5.7 por defecto
- No es portable

**Decision:** DESCARTADA - Incompatibilidad de version

### 12.3 Opcion C: Trigger de Validacion (DESCARTADA)

**Implementacion:**
```sql
CREATE TRIGGER check_unique_active_standard
BEFORE INSERT ON standards
FOR EACH ROW
BEGIN
    -- Validacion logic
END;
```

**Ventajas:**
- Garantizado por BD
- Compatible MySQL 5.7

**Desventajas:**
- Dificil de testear
- Dificil de mantener
- Mensajes de error no user-friendly
- Agrega complejidad

**Decision:** DESCARTADA - Complejidad excesiva

### 12.4 Opcion D: Validacion en Aplicacion + Indice (ELEGIDA)

**Implementacion:**
- Custom validation rule en Livewire
- Metodos helper en modelo
- Indice compuesto para performance

**Ventajas:**
- Mensajes user-friendly
- Facil de testear
- Facil de mantener
- Compatible MySQL 5.7+
- Buena performance con indice

**Desventajas:**
- No garantizado 100% por BD (posible race condition)

**Decision:** ELEGIDA - Mejor balance

**Mitigacion de Race Condition:**
```php
DB::transaction(function() {
    if (Standard::hasActiveStandardForPart($partId)) {
        throw new ValidationException(...);
    }
    Standard::create([...]);
});
```

### 12.5 Opcion E: Campo status ENUM (FUTURO)

**Implementacion:**
```php
'status' => ['draft', 'active', 'scheduled', 'archived']
```

**Ventajas:**
- Mas estados
- Permite planificacion futura

**Desventajas:**
- Requiere refactoring masivo
- Breaking change
- Overkill para necesidad actual

**Decision:** POSPUESTA - Considerar para v2.0

---

## 13. Riesgos y Mitigaciones

### 13.1 Riesgo: Race Condition

**Descripcion:** Dos usuarios crean estandar simultaneamente para misma parte

**Probabilidad:** BAJA (UI es lenta, usuarios son pocos)

**Impacto:** MEDIO (genera duplicados)

**Mitigacion:**
```php
// Usar transaccion con lock
DB::transaction(function() use ($data) {
    DB::table('standards')
      ->where('part_id', $data['part_id'])
      ->where('active', true)
      ->lockForUpdate() // Row-level lock
      ->get();

    if (Standard::hasActiveStandardForPart($data['part_id'])) {
        throw new ValidationException(...);
    }

    Standard::create($data);
});
```

### 13.2 Riesgo: Performance en Query de Validacion

**Descripcion:** Query de validacion se vuelve lento con miles de registros

**Probabilidad:** MEDIA (si sistema crece mucho)

**Impacto:** BAJO (solo afecta UX al crear/editar)

**Mitigacion:**
- Indice compuesto ya implementado
- Monitor query time con telescope
- Si supera 100ms, agregar cache:

```php
$cacheKey = "active_standard_part_{$partId}";
$exists = Cache::remember($cacheKey, 60, function() use ($partId) {
    return Standard::hasActiveStandardForPart($partId);
});
```

### 13.3 Riesgo: Migracion de Datos Incorrecta

**Descripcion:** Al limpiar duplicados, se mantiene estandar incorrecto

**Probabilidad:** MEDIA (decision manual es subjetiva)

**Impacto:** ALTO (afecta calculos de produccion)

**Mitigacion:**
- Backup OBLIGATORIO antes de limpieza
- Revision con Production Manager
- Script de rollback preparado
- Validacion post-migracion

### 13.4 Riesgo: Usuarios Confundidos por Cambio

**Descripcion:** Usuarios intentan crear duplicados y no entienden error

**Probabilidad:** ALTA (cambio de comportamiento)

**Impacto:** BAJO (solo UX)

**Mitigacion:**
- Mensaje de error claro y descriptivo
- Warning proactivo en UI
- Documentacion de usuario
- Training session

### 13.5 Riesgo: Soft Deletes No Considerados

**Descripcion:** Validacion no considera `deleted_at`, genera falsos positivos

**Probabilidad:** BAJA (ya implementado correctamente)

**Impacto:** ALTO (bloquea operaciones validas)

**Mitigacion:**
- Query de validacion incluye `whereNull('deleted_at')`
- Indice incluye columna `deleted_at`
- Tests cubren este escenario

---

## 14. Conclusiones y Recomendaciones

### 14.1 Conclusiones

**1. Problema Validado:**
- Existen 5 registros duplicados en produccion (2 parts afectadas)
- El sistema actualmente NO previene duplicados
- CapacityCalculatorService usa primer registro encontrado (no-deterministico)

**2. Solucion Optima:**
- Validacion en capa de aplicacion (Livewire)
- Indice compuesto para performance
- Modelo de "un activo, multiples inactivos" (historial)
- Custom validation rule reutilizable

**3. Impacto Controlado:**
- Cambios localizados en StandardCreate y StandardEdit
- NO requiere cambios en CapacityCalculatorService
- NO requiere cambios en base de datos existente (solo indice)
- Migracion de datos simple (5 registros)

### 14.2 Recomendaciones

**Prioridad ALTA (Implementar Inmediatamente):**

1. **Ejecutar limpieza de duplicados existentes**
   - Usar script manual con revision de Production Manager
   - Backup obligatorio antes de ejecutar

2. **Implementar validacion en Livewire components**
   - Custom rule `UniqueActiveStandard`
   - Warning UI proactivo

3. **Agregar metodos helper a modelo Standard**
   - `hasActiveStandardForPart()`
   - `getCurrentStandardForPart()`
   - `activateAsOnly()`

4. **Crear y ejecutar migracion de indice**
   - Indice compuesto: `(part_id, active, deleted_at)`

**Prioridad MEDIA (Implementar en Sprint Siguiente):**

5. **Agregar tests comprehensivos**
   - Unit tests de metodos helper
   - Feature tests de validacion
   - Integration tests de CapacityCalculator

6. **Actualizar documentacion**
   - README section sobre unicidad
   - Docblocks en codigo
   - User documentation

7. **Agregar logging en CapacityCalculator**
   - Warning si detecta multiples activos
   - Facilita deteccion temprana de problemas

**Prioridad BAJA (Backlog):**

8. **Considerar campo `status` enum para futuro**
   - Permitiria estados: draft, active, scheduled, archived
   - Mayor flexibilidad para planificacion

9. **Implementar UI para historial de estandares**
   - Vista timeline de cambios
   - Comparacion entre versiones
   - Rollback facil a version anterior

### 14.3 Criterios de Exito

**Validacion Exitosa si:**
- No es posible crear dos estandares activos para misma parte via UI
- Mensaje de error es claro y descriptivo
- Permite crear estandares inactivos (historial)
- Performance de validacion < 50ms
- Todos los tests pasan
- Cero duplicados en produccion post-deployment

**Metricas a Monitorear:**
- Numero de intentos de crear duplicados (debe ser > 0, indica que validacion esta funcionando)
- Tiempo de response de StandardCreate/Edit (debe mantenerse < 500ms)
- Numero de estandares activos por parte (debe ser siempre 1)

### 14.4 Siguientes Pasos

**Fase Inmediata (Esta Semana):**
1. Aprobar documento de analisis
2. Coordinar con Production Manager para limpieza de duplicados
3. Crear branch: `feature/unique-active-standard-validation`
4. Implementar custom validation rule
5. Actualizar componentes Livewire

**Fase 2 (Proxima Semana):**
6. Crear y ejecutar migracion de indice
7. Implementar metodos helper en modelo
8. Escribir tests unitarios y de feature
9. Code review
10. Merge a develop

**Fase 3 (Deployment):**
11. Deploy a staging
12. Testing en staging con datos reales
13. Deploy a produccion
14. Monitorear logs por 48 horas

---

## Apendices

### Apendice A: Queries Utiles

**Verificar estado actual:**
```sql
SELECT
    COUNT(*) as total_standards,
    SUM(active = 1) as active_standards,
    SUM(active = 0) as inactive_standards,
    COUNT(DISTINCT part_id) as unique_parts,
    COUNT(DISTINCT CASE WHEN active = 1 THEN part_id END) as parts_with_active
FROM standards
WHERE deleted_at IS NULL;
```

**Encontrar parts sin estandar activo:**
```sql
SELECT
    p.id,
    p.number,
    p.description
FROM parts p
LEFT JOIN standards s ON p.id = s.part_id AND s.active = 1 AND s.deleted_at IS NULL
WHERE s.id IS NULL
  AND p.active = 1;
```

**Encontrar ultimos cambios en estandares:**
```sql
SELECT
    s.id,
    p.number AS part,
    s.active,
    s.units_per_hour,
    s.updated_at,
    u.name AS updated_by
FROM standards s
INNER JOIN parts p ON s.part_id = p.id
LEFT JOIN users u ON s.updated_by = u.id
WHERE s.deleted_at IS NULL
ORDER BY s.updated_at DESC
LIMIT 20;
```

### Apendice B: Comandos Artisan Utiles

**Crear custom rule:**
```bash
php artisan make:rule UniqueActiveStandard
```

**Crear test:**
```bash
php artisan make:test Livewire/StandardCreateValidationTest --unit
```

**Ejecutar tests especificos:**
```bash
php artisan test --filter StandardCreateValidationTest
php artisan test tests/Feature/Livewire/
```

**Ejecutar migracion:**
```bash
php artisan migrate
php artisan migrate:rollback --step=1
```

### Apendice C: Configuracion de Indices

**Ver indices actuales:**
```sql
SHOW INDEX FROM standards;
```

**Analizar performance de query:**
```sql
EXPLAIN SELECT * FROM standards
WHERE part_id = 22
  AND active = 1
  AND deleted_at IS NULL;
```

**Estadisticas de indice:**
```sql
ANALYZE TABLE standards;
```

---

---

## GUÍA DE IMPLEMENTACIÓN SIMPLIFICADA

### 📋 ¿Qué está pasando? (El Problema Explicado)

**Situación Actual:**

Imagina que tienes la **Parte 22** (STS H-M-4). Ahora mismo, en la base de datos existen **3 estándares ACTIVOS** para esta misma parte:

- ✅ Estándar ID 18: Produce 166 unidades/hora en la estación MT-005
- ✅ Estándar ID 20: Produce 43 unidades/hora en la estación MT-006
- ✅ Estándar ID 21: Produce 43 unidades/hora en la estación MT-006 (duplicado accidental)

**¿Por qué es un problema?**

Cuando el sistema necesita calcular cuánto tiempo tomará producir 500 unidades de la Parte 22, el `CapacityCalculatorService` busca el estándar activo. Pero como hay 3 activos, el sistema simplemente toma **el primero que encuentra** (en este caso, ID 18 con 166 unidades/hora) e **ignora completamente los otros dos**.

Esto genera:
- ⚠️ **Confusión operativa**: Los usuarios ven 3 estándares activos y no saben cuál es el correcto
- ⚠️ **Cálculos inconsistentes**: Dependiendo del orden, el sistema podría usar cualquiera de los 3
- ⚠️ **Desperdicio de datos**: Los estándares ID 20 y 21 están "ahí" pero nunca se usan
- ⚠️ **Errores de planeación**: Si alguien edita el estándar "equivocado", los cálculos siguen usando el ID 18

**¿Qué necesitamos lograr?**

Una regla simple: **UNA PARTE = UN ESTÁNDAR ACTIVO**. Los demás estándares pueden existir (para historial), pero deben estar marcados como `active = 0` (inactivos).

---

### 🎯 La Solución en Palabras Simples

**Regla de Negocio:**

> "Una parte puede tener muchos estándares registrados (historial), pero **SOLO UNO** puede estar activo a la vez."

**Cómo funcionará desde la perspectiva del usuario:**

1. **Al crear un nuevo estándar activo:**
   - Si ya existe un estándar activo para esa parte → ❌ El sistema muestra error
   - El usuario debe primero desactivar el estándar anterior
   - O crear el nuevo estándar como inactivo (para agregarlo al historial)

2. **Al editar un estándar existente:**
   - Si cambias de parte y la nueva parte ya tiene un estándar activo → ❌ Error
   - Si solo modificas datos (capacidad, estación, etc.) → ✅ Sin problema
   - Si activas un estándar inactivo y ya hay uno activo → ❌ Error

3. **Visualización:**
   - En la lista de estándares verás claramente cuál está activo (✅) y cuáles son históricos (📋)
   - El sistema siempre usará el único estándar activo para cálculos

**Ejemplos concretos:**

| Acción | Parte | Estándar Existente | Resultado |
|--------|-------|-------------------|-----------|
| Crear estándar activo | Part 22 | ✅ Ya existe uno activo (ID 18) | ❌ **BLOQUEADO** - Mensaje: "Ya existe un estándar activo para la parte STS H-M-4. Desactive el estándar existente primero." |
| Crear estándar **inactivo** | Part 22 | ✅ Ya existe uno activo (ID 18) | ✅ **PERMITIDO** - Se crea como historial |
| Editar estándar actual | Part 22 | Es el único activo | ✅ **PERMITIDO** - Modificación libre |
| Activar estándar histórico | Part 22 | ✅ Ya existe uno activo (ID 18) | ❌ **BLOQUEADO** - Debe desactivar el ID 18 primero |

---

### 🔧 Pasos de Implementación ESPECÍFICOS y EN ORDEN

#### **PASO 1: Limpieza de Duplicados Existentes** (⚠️ MANUAL - REQUIERE DECISIÓN)

**Archivo a usar:** Cliente SQL (phpMyAdmin, DBeaver, o consola MySQL)

**Qué hacer:**

1. **Hacer un backup de seguridad:**
   ```sql
   CREATE TABLE standards_backup_20260112 AS SELECT * FROM standards;
   ```
   ☝️ Esto crea una copia completa de la tabla por si algo sale mal

2. **Ver los duplicados actuales:**
   ```sql
   SELECT
       s.part_id,
       p.number AS part_number,
       COUNT(*) as cantidad_activos,
       GROUP_CONCAT(
           CONCAT('ID:', s.id, ' | ', s.units_per_hour, ' u/h | ',
                  COALESCE(t.number, sa.number, m.name, 'Sin estación'))
           ORDER BY s.id SEPARATOR ' || '
       ) AS detalles
   FROM standards s
   INNER JOIN parts p ON s.part_id = p.id
   LEFT JOIN tables t ON s.work_table_id = t.id
   LEFT JOIN semi__automatics sa ON s.semi_auto_work_table_id = sa.id
   LEFT JOIN machines m ON s.machine_id = m.id
   WHERE s.active = 1
     AND s.deleted_at IS NULL
   GROUP BY s.part_id, p.number
   HAVING cantidad_activos > 1;
   ```

   📊 **Resultado esperado:**
   ```
   part_id | part_number | cantidad_activos | detalles
   --------|-------------|------------------|----------
   22      | STS H-M-4   | 3                | ID:18 | 166 u/h | MT-005 || ID:20 | 43 u/h | MT-006 || ID:21 | 43 u/h | MT-006
   18      | STS H-C-3   | 2                | ID:14 | 2500 u/h | MT-003 || ID:17 | 2500 u/h | Haas - VF-4 - CNC
   ```

3. **Decidir cuál mantener activo** (CONSULTAR CON PRODUCTION MANAGER):

   **Para Part 22 (STS H-M-4):**
   - Mantener: ID 18 (166 u/h en MT-005) - Mayor capacidad
   - Desactivar: ID 20 y 21 (son duplicados accidentales)

   **Para Part 18 (STS H-C-3):**
   - Mantener: ID 17 (Haas CNC - máquina más moderna)
   - Desactivar: ID 14 (Mesa manual - método antiguo)

4. **Ejecutar limpieza:**
   ```sql
   -- PARA PART 22: Desactivar ID 20 y 21, mantener ID 18
   UPDATE standards
   SET active = 0,
       description = CONCAT(
           COALESCE(description, ''),
           ' [Desactivado 2026-01-12: Duplicado accidental]'
       )
   WHERE id IN (20, 21);

   -- PARA PART 18: Desactivar ID 14, mantener ID 17
   UPDATE standards
   SET active = 0,
       description = CONCAT(
           COALESCE(description, ''),
           ' [Desactivado 2026-01-12: Método antiguo, reemplazado por CNC]'
       )
   WHERE id IN (14);
   ```

   💡 **Qué hace cada línea:**
   - `SET active = 0` → Marca el estándar como inactivo
   - `description = CONCAT(...)` → Agrega una nota explicando por qué se desactivó
   - `WHERE id IN (...)` → Solo afecta los IDs duplicados

5. **Verificar que quedó limpio:**
   ```sql
   SELECT
       part_id,
       COUNT(*) as cantidad_activos
   FROM standards
   WHERE active = 1 AND deleted_at IS NULL
   GROUP BY part_id
   HAVING cantidad_activos > 1;
   ```

   ✅ **Resultado esperado:** `0 rows` (ningún duplicado)

---

#### **PASO 2: Crear Índice de Base de Datos** (Optimización de Performance)

**Comando:**
```bash
php artisan make:migration add_part_active_validation_index_to_standards_table
```

**Archivo creado:** `database/migrations/2026_01_12_XXXXXX_add_part_active_validation_index_to_standards_table.php`

**Contenido del archivo:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standards', function (Blueprint $table) {
            // Índice para acelerar la validación de unicidad
            // (part_id + active + deleted_at)
            $table->index(
                ['part_id', 'active', 'deleted_at'],
                'standards_part_active_validation_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('standards', function (Blueprint $table) {
            $table->dropIndex('standards_part_active_validation_idx');
        });
    }
};
```

**Ejecutar migración:**
```bash
php artisan migrate
```

💡 **Qué hace:** Crea un índice que acelera las búsquedas cuando validamos si existe un estándar activo duplicado. Sin este índice, las validaciones serían lentas con muchos registros.

---

#### **PASO 3: Agregar Métodos Helper al Modelo Standard**

**Archivo:** `app/Models/Standard.php`

**Qué agregar:** Al final de la clase, antes del cierre `}`, agregar estos métodos:

```php
/**
 * Verifica si existe otro estandar activo para la misma parte
 *
 * @param int $partId ID de la parte a verificar
 * @param int|null $exceptId ID del registro a excluir (para updates)
 * @return bool true si existe otro activo, false si no
 */
public static function hasActiveStandardForPart(int $partId, ?int $exceptId = null): bool
{
    $query = static::where('part_id', $partId)
                   ->where('active', true);

    if ($exceptId) {
        $query->where('id', '!=', $exceptId);
    }

    return $query->exists();
}

/**
 * Scope para obtener el estandar activo de una parte
 *
 * @param \Illuminate\Database\Eloquent\Builder $query
 * @param int $partId
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeActiveForPart($query, int $partId)
{
    return $query->where('part_id', $partId)
                 ->where('active', true)
                 ->orderBy('effective_date', 'desc')
                 ->orderBy('id', 'desc');
}
```

💡 **Qué hacen:**
- `hasActiveStandardForPart()`: Método rápido para verificar si existe un duplicado
- `scopeActiveForPart()`: Método para buscar el estándar activo de una parte

---

#### **PASO 4: Crear Custom Validation Rule**

**Comando:**
```bash
php artisan make:rule UniqueActiveStandard
```

**Archivo creado:** `app/Rules/UniqueActiveStandard.php`

**Contenido completo:**
```php
<?php

namespace App\Rules;

use App\Models\Standard;
use App\Models\Part;
use Illuminate\Contracts\Validation\Rule;

class UniqueActiveStandard implements Rule
{
    protected ?int $exceptId;
    protected bool $isActive;
    protected ?string $partNumber = null;

    /**
     * @param bool $isActive Si el estándar será activo
     * @param int|null $exceptId ID del registro actual (para ediciones)
     */
    public function __construct(bool $isActive, ?int $exceptId = null)
    {
        $this->isActive = $isActive;
        $this->exceptId = $exceptId;
    }

    /**
     * Determina si la validación pasa
     */
    public function passes($attribute, $value): bool
    {
        // Solo validar si el estandar será activo
        if (!$this->isActive) {
            return true; // Si es inactivo, permitir siempre
        }

        // Guardar número de parte para mensaje de error
        $part = Part::find($value);
        $this->partNumber = $part ? $part->number : 'N/A';

        // Verificar si existe otro estandar activo
        return !Standard::hasActiveStandardForPart($value, $this->exceptId);
    }

    /**
     * Mensaje de error cuando la validación falla
     */
    public function message(): string
    {
        return "Ya existe un estándar activo para la parte {$this->partNumber}. " .
               "Desactive el estándar existente antes de crear uno nuevo, " .
               "o marque este estándar como inactivo para agregarlo al historial.";
    }
}
```

💡 **Qué hace:**
- Valida que no exista otro estándar activo para la misma parte
- Solo valida cuando `active = true` (permite crear inactivos libremente)
- Muestra mensaje claro con el número de parte

---

#### **PASO 5: Actualizar StandardCreate Component**

**Archivo:** `app/Livewire/Admin/Standards/StandardCreate.php`

**Cambio 1:** Agregar el `use` al inicio del archivo (después de los otros `use`):
```php
use App\Rules\UniqueActiveStandard;
```

**Cambio 2:** Modificar el método `rules()` (líneas 31-46):

**BUSCAR:**
```php
protected function rules(): array
{
    return [
        'part_id' => 'required|exists:parts,id',
```

**REEMPLAZAR CON:**
```php
protected function rules(): array
{
    return [
        'part_id' => [
            'required',
            'exists:parts,id',
            new UniqueActiveStandard($this->active)
        ],
```

💡 **Qué hace:** Agrega la validación de unicidad al crear un nuevo estándar

---

#### **PASO 6: Actualizar StandardEdit Component**

**Archivo:** `app/Livewire/Admin/Standards/StandardEdit.php`

**Cambio 1:** Agregar el `use` al inicio del archivo:
```php
use App\Rules\UniqueActiveStandard;
```

**Cambio 2:** Modificar el método `rules()` (líneas 43-58):

**BUSCAR:**
```php
protected function rules(): array
{
    return [
        'part_id' => 'required|exists:parts,id',
```

**REEMPLAZAR CON:**
```php
protected function rules(): array
{
    return [
        'part_id' => [
            'required',
            'exists:parts,id',
            new UniqueActiveStandard($this->active, $this->standard->id)
        ],
```

💡 **Qué hace:** Agrega la validación de unicidad al editar, pero EXCLUYE el registro actual (permite guardar sin cambiar la parte)

---

### 📊 Ejemplos Visuales de Flujo

#### **Flujo: Crear Nuevo Estándar ACTIVO**

```
Usuario entra a "Crear Estándar"
    ↓
Selecciona Part 22 (STS H-M-4)
    ↓
Marca "Active" = ✅ (true)
    ↓
Llena datos: 200 units/hour, MT-007
    ↓
Click en "Guardar"
    ↓
┌─────────────────────────────────────┐
│ VALIDACIÓN: UniqueActiveStandard    │
│ - Busca en BD: ¿Existe Part 22      │
│   con active=1?                      │
│ - Resultado: SÍ (ID 18 está activo) │
└─────────────────────────────────────┘
    ↓
❌ ERROR MOSTRADO:
"Ya existe un estándar activo para la parte STS H-M-4.
Desactive el estándar existente antes de crear uno nuevo,
o marque este estándar como inactivo para agregarlo al historial."
    ↓
Usuario tiene 2 opciones:
    A) Ir a lista → Desactivar ID 18 → Volver y crear nuevo
    B) Desmarcar "Active" → Crear como historial
```

---

#### **Flujo: Crear Nuevo Estándar INACTIVO (Historial)**

```
Usuario entra a "Crear Estándar"
    ↓
Selecciona Part 22 (STS H-M-4)
    ↓
Desmarca "Active" = ❌ (false)
    ↓
Llena datos: 180 units/hour, MT-008
    ↓
Click en "Guardar"
    ↓
┌─────────────────────────────────────┐
│ VALIDACIÓN: UniqueActiveStandard    │
│ - Detecta active=false               │
│ - Resultado: SKIP (no valida)       │
└─────────────────────────────────────┘
    ↓
✅ ÉXITO: Estándar creado como historial
    ↓
En la BD ahora hay:
    - ID 18: Part 22, active=1 (EL ÚNICO ACTIVO)
    - ID 22: Part 22, active=0 (NUEVO - HISTORIAL)
```

---

#### **Flujo: Editar Estándar Existente**

```
Usuario entra a "Editar Estándar ID 18"
    ↓
Cambia units/hour: 166 → 180
    ↓
Mantiene Part 22, active=true
    ↓
Click en "Guardar"
    ↓
┌─────────────────────────────────────┐
│ VALIDACIÓN: UniqueActiveStandard    │
│ - Busca Part 22 activo != ID 18     │
│ - Resultado: NO existe otro         │
└─────────────────────────────────────┘
    ↓
✅ ÉXITO: Cambios guardados
```

---

#### **Antes y Después de la Solución**

**ANTES (Estado Problemático):**
```
📊 BASE DE DATOS - standards table

part_id | id | active | units_per_hour | workstation
--------|-------|--------|----------------|-------------
22      | 18    | 1      | 166            | MT-005      ← CapacityCalculator usa este
22      | 20    | 1      | 43             | MT-006      ← Ignorado
22      | 21    | 1      | 43             | MT-006      ← Ignorado
18      | 14    | 1      | 2500           | MT-003      ← CapacityCalculator usa este
18      | 17    | 1      | 2500           | Haas CNC    ← Ignorado

❌ Problemas:
- 5 estándares, 2 partes = 2.5 estándares activos por parte (MALO)
- Sistema usa el primero, ignora otros
- Confusión en la UI
```

**DESPUÉS (Estado Correcto):**
```
📊 BASE DE DATOS - standards table

part_id | id | active | units_per_hour | workstation
--------|-------|--------|----------------|-------------
22      | 18    | 1      | 166            | MT-005      ← CapacityCalculator usa este ✅
22      | 20    | 0      | 43             | MT-006      ← Historial
22      | 21    | 0      | 43             | MT-006      ← Historial
18      | 14    | 0      | 2500           | MT-003      ← Historial (método antiguo)
18      | 17    | 1      | 2500           | Haas CNC    ← CapacityCalculator usa este ✅

✅ Solución:
- 5 estándares, 2 partes = 1 estándar activo por parte (CORRECTO)
- Sistema SIEMPRE usa el único activo
- UI clara: activos vs históricos
- Validación bloquea duplicados
```

---

### 🗂️ Scripts SQL Listos para Copiar y Pegar

#### **Script 1: Ver Duplicados Actuales**

```sql
-- ========================================
-- VER TODOS LOS DUPLICADOS ACTUALES
-- ========================================
-- Muestra qué partes tienen múltiples estándares activos
-- con detalles completos

SELECT
    s.part_id,
    p.number AS part_number,
    p.description AS part_description,
    COUNT(*) as total_activos,
    GROUP_CONCAT(
        CONCAT(
            'ID:', s.id,
            ' | ', s.units_per_hour, ' u/h',
            ' | ', DATE_FORMAT(s.created_at, '%Y-%m-%d %H:%i'),
            ' | ', COALESCE(t.number, sa.number, m.name, 'Sin estación')
        )
        ORDER BY s.id
        SEPARATOR '\n      '
    ) AS detalles_estandares
FROM standards s
INNER JOIN parts p ON s.part_id = p.id
LEFT JOIN tables t ON s.work_table_id = t.id
LEFT JOIN semi__automatics sa ON s.semi_auto_work_table_id = sa.id
LEFT JOIN machines m ON s.machine_id = m.id
WHERE s.active = 1
  AND s.deleted_at IS NULL
GROUP BY s.part_id, p.number, p.description
HAVING total_activos > 1
ORDER BY total_activos DESC, p.number;

-- Si el resultado es "0 rows" → ¡No hay duplicados! ✅
-- Si aparecen filas → Hay duplicados que necesitan limpieza ⚠️
```

---

#### **Script 2: Limpiar Duplicados (CON EXPLICACIONES)**

```sql
-- ========================================
-- PASO 1: CREAR BACKUP DE SEGURIDAD
-- ========================================
-- Siempre crear backup antes de modificar datos
-- Esta tabla quedará intacta, puedes restaurar si algo sale mal

CREATE TABLE standards_backup_20260112 AS
SELECT * FROM standards;

-- Verificar que el backup se creó correctamente:
SELECT COUNT(*) FROM standards_backup_20260112;
-- Debe mostrar el mismo número que: SELECT COUNT(*) FROM standards;


-- ========================================
-- PASO 2: DESACTIVAR DUPLICADOS
-- ========================================
-- IMPORTANTE: Revisa con el Production Manager cuáles mantener activos

-- Para PART 22 (STS H-M-4):
-- Mantener: ID 18 (166 u/h, mayor capacidad)
-- Desactivar: ID 20, 21 (duplicados accidentales)

UPDATE standards
SET
    active = 0,  -- Marca como inactivo
    description = CONCAT(
        COALESCE(description, ''),  -- Mantiene descripción anterior si existe
        IF(description IS NOT NULL, ' | ', ''),  -- Separador si había texto
        '[Desactivado 2026-01-12: Duplicado accidental, mantener ID 18]'
    )
WHERE id IN (20, 21)
  AND part_id = 22;

-- Ver el resultado:
SELECT id, part_id, active, units_per_hour, description
FROM standards
WHERE part_id = 22;


-- Para PART 18 (STS H-C-3):
-- Mantener: ID 17 (Haas CNC, máquina moderna)
-- Desactivar: ID 14 (Mesa manual, método antiguo)

UPDATE standards
SET
    active = 0,
    description = CONCAT(
        COALESCE(description, ''),
        IF(description IS NOT NULL, ' | ', ''),
        '[Desactivado 2026-01-12: Método antiguo, reemplazado por CNC Haas (ID 17)]'
    )
WHERE id = 14
  AND part_id = 18;

-- Ver el resultado:
SELECT id, part_id, active, units_per_hour, description
FROM standards
WHERE part_id = 18;


-- ========================================
-- OPCIÓN ALTERNATIVA: Desactivar TODOS menos el más reciente
-- ========================================
-- Si prefieres automatizar (mantener el último creado):

UPDATE standards s1
SET
    active = 0,
    description = CONCAT(
        COALESCE(description, ''),
        IF(description IS NOT NULL, ' | ', ''),
        CONCAT('[Desactivado 2026-01-12: Estándar más reciente ID:',
               (SELECT MAX(id) FROM standards s2
                WHERE s2.part_id = s1.part_id AND s2.active = 1),
               ' está activo]')
    )
WHERE part_id IN (
    -- Subquery: encontrar partes con duplicados
    SELECT part_id
    FROM (
        SELECT part_id
        FROM standards
        WHERE active = 1 AND deleted_at IS NULL
        GROUP BY part_id
        HAVING COUNT(*) > 1
    ) AS duplicates
)
AND id NOT IN (
    -- Subquery: mantener solo el más reciente por parte
    SELECT MAX(id)
    FROM standards
    WHERE active = 1 AND deleted_at IS NULL
    GROUP BY part_id
);
```

---

#### **Script 3: Verificar que Quedó Limpio**

```sql
-- ========================================
-- VERIFICACIÓN FINAL
-- ========================================

-- 1. Verificar que NO hay duplicados activos:
SELECT
    part_id,
    COUNT(*) as total_activos
FROM standards
WHERE active = 1 AND deleted_at IS NULL
GROUP BY part_id
HAVING total_activos > 1;

-- ✅ RESULTADO ESPERADO: 0 rows
-- ❌ Si aparecen filas: Aún hay duplicados, revisar


-- 2. Ver resumen general:
SELECT
    COUNT(*) as total_standards,
    SUM(active = 1) as activos,
    SUM(active = 0) as inactivos,
    COUNT(DISTINCT part_id) as partes_unicas,
    COUNT(DISTINCT CASE WHEN active = 1 THEN part_id END) as partes_con_activo
FROM standards
WHERE deleted_at IS NULL;

-- ✅ RESULTADO ESPERADO:
-- - total_standards: Número total de registros
-- - activos: Número de estándares activos
-- - inactivos: Número de estándares históricos
-- - partes_unicas: Total de partes distintas
-- - partes_con_activo: Debe ser IGUAL a 'activos'


-- 3. Ver estado por parte (detallado):
SELECT
    p.id,
    p.number,
    p.description,
    SUM(CASE WHEN s.active = 1 THEN 1 ELSE 0 END) as activos,
    SUM(CASE WHEN s.active = 0 THEN 1 ELSE 0 END) as historicos,
    GROUP_CONCAT(
        CASE WHEN s.active = 1
        THEN CONCAT('✅ ID:', s.id, ' (', s.units_per_hour, ' u/h)')
        END
    ) as estandar_activo
FROM parts p
LEFT JOIN standards s ON p.id = s.part_id AND s.deleted_at IS NULL
WHERE p.active = 1
GROUP BY p.id, p.number, p.description
ORDER BY activos DESC, p.number;

-- ✅ RESULTADO ESPERADO:
-- - Columna 'activos': debe ser 1 o 0 (nunca mayor a 1)
-- - Columna 'historicos': puede ser cualquier número
-- - Columna 'estandar_activo': debe mostrar solo UN estándar por parte


-- 4. Verificar los que fueron desactivados:
SELECT
    s.id,
    p.number AS part,
    s.active,
    s.units_per_hour,
    s.description,
    s.updated_at
FROM standards s
INNER JOIN parts p ON s.part_id = p.id
WHERE s.description LIKE '%Desactivado 2026-01-12%'
  AND s.deleted_at IS NULL
ORDER BY s.part_id, s.id;

-- Muestra todos los estándares que fueron desactivados por esta limpieza
```

---

### ✅ Checklist de Validación

**Después de implementar TODO, verifica:**

#### **1. Base de Datos:**
- [ ] ✅ Backup de tabla `standards` creado exitosamente
- [ ] ✅ Duplicados limpiados (Script 3 muestra `0 rows`)
- [ ] ✅ Índice `standards_part_active_validation_idx` creado (ver con `SHOW INDEX FROM standards`)
- [ ] ✅ Cada parte tiene máximo 1 estándar activo

#### **2. Código Backend:**
- [ ] ✅ Modelo `Standard.php` tiene método `hasActiveStandardForPart()`
- [ ] ✅ Modelo `Standard.php` tiene scope `activeForPart()`
- [ ] ✅ Clase `UniqueActiveStandard` existe en `app/Rules/`
- [ ] ✅ `StandardCreate.php` usa `new UniqueActiveStandard($this->active)`
- [ ] ✅ `StandardEdit.php` usa `new UniqueActiveStandard($this->active, $this->standard->id)`

#### **3. Pruebas Funcionales:**

**Prueba A: Crear duplicado activo (debe FALLAR)**
1. Ve a Admin → Standards → Crear
2. Selecciona una parte que YA tiene estándar activo (ej: Part 22)
3. Marca "Active" = ✅
4. Llena datos y guarda
5. **Resultado esperado:** ❌ Error: "Ya existe un estándar activo para la parte STS H-M-4..."

**Prueba B: Crear como inactivo (debe FUNCIONAR)**
1. Ve a Admin → Standards → Crear
2. Selecciona la misma parte del test anterior
3. Marca "Active" = ❌ (desmarcado)
4. Llena datos y guarda
5. **Resultado esperado:** ✅ "Estándar creado correctamente" - Se crea como historial

**Prueba C: Editar sin cambiar parte (debe FUNCIONAR)**
1. Ve a Admin → Standards → Lista
2. Edita un estándar activo existente
3. Cambia solo `units_per_hour` (ej: 166 → 170)
4. Mantén `active = true` y la misma parte
5. Guarda
6. **Resultado esperado:** ✅ Cambios guardados sin error

**Prueba D: Cambiar a parte duplicada (debe FALLAR)**
1. Edita un estándar activo (ej: Part 22, ID 18)
2. Cambia `part_id` a otra parte que YA tiene estándar activo (ej: Part 18)
3. Guarda
4. **Resultado esperado:** ❌ Error: "Ya existe un estándar activo para la parte STS H-C-3..."

**Prueba E: CapacityCalculator usa el correcto**
1. Ve a la sección de cálculo de capacidad (o donde se use el CapacityCalculatorService)
2. Calcula capacidad para Part 22 (500 unidades)
3. Verifica en logs o resultado que usa el estándar correcto (ID 18, 166 u/h)
4. **Resultado esperado:** Cálculo correcto: 500 / 166 = ~3.01 horas

#### **4. UI/UX:**
- [ ] ✅ Mensajes de error son claros y en español
- [ ] ✅ Los mensajes sugieren acción al usuario ("Desactive el estándar existente primero")
- [ ] ✅ No hay errores 500 ni pantallas blancas
- [ ] ✅ La lista de estándares muestra claramente cuál está activo

---

### 🚨 ¿Qué Hacer Si Algo Sale Mal?

#### **Problema 1: Error al ejecutar migración**

**Error:** `SQLSTATE[42S21]: Column already exists: 1060 Duplicate column name`

**Solución:**
```bash
# Ver si el índice ya existe:
php artisan tinker
DB::select("SHOW INDEX FROM standards WHERE Key_name = 'standards_part_active_validation_idx'");

# Si existe, hacer rollback y volver a crear:
php artisan migrate:rollback --step=1
php artisan migrate
```

---

#### **Problema 2: Validación no funciona, permite duplicados**

**Posibles causas:**
1. El `use App\Rules\UniqueActiveStandard;` no se agregó al componente
2. La regla no está en el array de rules
3. El método `hasActiveStandardForPart()` no está en el modelo

**Diagnóstico:**
```bash
# Verificar que la regla existe:
ls app/Rules/UniqueActiveStandard.php

# Verificar sintaxis del componente:
php artisan view:clear
php artisan optimize:clear
```

---

#### **Problema 3: Después de limpiar duplicados, una parte no tiene estándar activo**

**Diagnóstico:**
```sql
-- Ver partes sin estándar activo:
SELECT
    p.id,
    p.number,
    p.description
FROM parts p
LEFT JOIN standards s ON p.id = s.part_id AND s.active = 1 AND s.deleted_at IS NULL
WHERE s.id IS NULL
  AND p.active = 1;
```

**Solución:**
```sql
-- Reactivar el último estándar desactivado:
UPDATE standards
SET active = 1
WHERE id = (
    SELECT MAX(id)
    FROM (SELECT * FROM standards) AS s
    WHERE part_id = [ID_DE_LA_PARTE_PROBLEMA]
      AND deleted_at IS NULL
)
LIMIT 1;
```

---

#### **Problema 4: Restaurar desde backup**

**Si TODO salió mal y necesitas volver atrás:**

```sql
-- 1. Ver que el backup existe:
SELECT COUNT(*) FROM standards_backup_20260112;

-- 2. Restaurar TODOS los datos:
TRUNCATE TABLE standards;  -- ⚠️ CUIDADO: Borra todo
INSERT INTO standards SELECT * FROM standards_backup_20260112;

-- 3. Verificar que se restauró:
SELECT COUNT(*) FROM standards;
-- Debe ser igual al count del backup
```

---

### 📝 Resumen de Archivos Modificados

| Archivo | Acción | Ubicación |
|---------|--------|-----------|
| **Nueva migración** | CREAR | `database/migrations/2026_01_12_*_add_part_active_validation_index_to_standards_table.php` |
| **UniqueActiveStandard.php** | CREAR | `app/Rules/UniqueActiveStandard.php` |
| **Standard.php** | MODIFICAR | `app/Models/Standard.php` - Agregar 2 métodos al final |
| **StandardCreate.php** | MODIFICAR | `app/Livewire/Admin/Standards/StandardCreate.php` - Modificar `rules()` |
| **StandardEdit.php** | MODIFICAR | `app/Livewire/Admin/Standards/StandardEdit.php` - Modificar `rules()` |
| **Base de datos** | LIMPIAR | Ejecutar scripts SQL de limpieza |

---

### 🎓 Conceptos Clave para Entender la Solución

**1. ¿Por qué no usar UNIQUE constraint en la BD?**

En MySQL 5.7 (común en XAMPP) no puedes hacer:
```sql
UNIQUE KEY (part_id, active) WHERE active = 1  -- ❌ No soportado
```

Por eso validamos en el código de Laravel, que es más flexible y da mejores mensajes de error.

**2. ¿Por qué permitir múltiples inactivos?**

Para mantener **historial**. Si la parte STS H-M-4 antes producía 150 u/h y ahora produce 180 u/h, queremos:
- El viejo estándar (150) con `active=0` (historial)
- El nuevo estándar (180) con `active=1` (actual)

Así puedes ver la evolución y, si algo sale mal, "reactivar" el anterior.

**3. ¿Qué pasa con `effective_date`?**

Por ahora, `effective_date` es solo **informativo**. El sistema usa el estándar con `active=1`, sin importar la fecha. En el futuro se podría mejorar para considerar fechas, pero eso es más complejo.

---

**FIN DE LA GUÍA SIMPLIFICADA**

Si después de leer esta guía aún tienes dudas, pregunta específicamente sobre el paso que no está claro. Esta guía fue diseñada para ser **100% práctica y ejecutable**.

---

**FIN DEL DOCUMENTO**

**Documento Preparado Por:** Agent Architect
**Fecha:** 2026-01-12
**Version:** 1.1 (Guía Simplificada Agregada)
**Estado:** COMPLETO - LISTO PARA REVISION

**Proxima Accion:** Revision y aprobacion por equipo de desarrollo y Production Manager
