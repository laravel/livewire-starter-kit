# Spec 02: Refactorización de Relaciones entre Standards y Estaciones de Trabajo

**Fecha de Creación:** 2025-12-19
**Autor:** Architect Agent
**Fase del Proyecto:** FASE 2 - Planificación de Producción
**Estado:** Propuesta - Análisis Técnico
**Versión:** 1.0
**Relacionado con:** Spec 01 - Plan de Implementación Capacidad de Producción

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Problema](#problema)
3. [Análisis de Estructura Actual](#análisis-de-estructura-actual)
4. [Impacto Arquitectural](#impacto-arquitectural)
5. [Evaluación de Opciones de Diseño](#evaluación-de-opciones-de-diseño)
6. [Propuesta de Solución](#propuesta-de-solución)
7. [Plan de Implementación Detallado](#plan-de-implementación-detallado)
8. [Migración de Datos](#migración-de-datos)
9. [Testing](#testing)
10. [Diagrama ER Actualizado](#diagrama-er-actualizado)
11. [Riesgos y Mitigaciones](#riesgos-y-mitigaciones)
12. [Referencias](#referencias)

---

## Resumen Ejecutivo

### Contexto

Durante el análisis del **Spec 01** se identificó que la tabla `standards` requiere agregar los campos `units_per_hour` y `assembly_mode`. Sin embargo, el sistema ya cuenta con tres tipos de estaciones de trabajo:

- **tables**: Mesas de trabajo manual
- **semi_automatics**: Mesas semiautomáticas
- **machines**: Máquinas automáticas

**Actualmente, la tabla `standards` ya tiene relaciones con estas tres entidades**, pero el diseño presenta inconsistencias y oportunidades de mejora.

### Problema Crítico Identificado

La migración actual de `standards` (líneas 18-20) tiene **tres foreign keys separadas**, creando complejidad innecesaria:

```php
$table->foreignId('work_table_id')->nullable()->constrained('tables')->onDelete('set null');
$table->foreignId('semi_auto_work_table_id')->nullable()->constrained('semi__automatics')->onDelete('set null');
$table->foreignId('machine_id')->nullable()->constrained()->onDelete('set null');
```

**Problemas:**
- Múltiples columnas nullable que deberían ser mutuamente excluyentes
- No hay validación a nivel de DB de que solo UNA estación puede estar asignada
- Campo `assembly_mode` propuesto en Spec 01 se vuelve redundante
- Queries complejos para determinar qué tipo de estación usa un standard

### Solución Propuesta

**MANTENER el enfoque actual de foreign keys directas** por las siguientes razones:

1. **Consistencia**: El código existente ya está implementado con este patrón
2. **Simplicidad**: No requiere refactorizar todo el código Livewire existente
3. **Performance**: Evita JOINs adicionales de relaciones polimórficas
4. **Compatibilidad**: No rompe datos existentes

**PERO mejorar con**:
- Validación a nivel de aplicación (reglas de validación)
- Computed attribute para determinar tipo de estación
- Métodos helper para obtener la estación activa
- Eliminar el campo `assembly_mode` redundante del Spec 01

### Objetivos

1. Mantener las foreign keys existentes pero agregar validaciones
2. Agregar campo `units_per_hour` requerido
3. NO agregar campo `assembly_mode` (se calcula dinámicamente)
4. Crear métodos helper en modelo Standard para simplificar lógica
5. Actualizar componentes Livewire con validación mejorada
6. Documentar patrones y convenciones

---

## Problema

### Contexto Detallado

El **Spec 01** propone agregar dos campos a `standards`:

```php
$table->integer('units_per_hour')->after('name');
$table->enum('assembly_mode', ['manual', 'semi_automatic', 'machine'])
      ->default('manual')
      ->after('units_per_hour');
```

Sin embargo, al analizar la migración existente de `standards` (creada el 2025-12-14), encontramos que **YA existe** una estructura para relacionar standards con estaciones:

**Migración Actual:**
```php
// database/migrations/2025_12_14_190425_create_standards_table.php (líneas 18-20)
$table->foreignId('work_table_id')->nullable()->constrained('tables')->onDelete('set null');
$table->foreignId('semi_auto_work_table_id')->nullable()->constrained('semi__automatics')->onDelete('set null');
$table->foreignId('machine_id')->nullable()->constrained()->onDelete('set null');
```

**Modelo Actual:**
```php
// app/Models/Standard.php (líneas 58-77)
public function workTable()
{
    return $this->belongsTo(Table::class, 'work_table_id');
}

public function semiAutoWorkTable()
{
    return $this->belongsTo(Semi_Automatic::class, 'semi_auto_work_table_id');
}

public function machine()
{
    return $this->belongsTo(Machine::class);
}
```

### Pregunta Arquitectural Clave

**¿Cómo debemos manejar la relación de un standard con sus estaciones de trabajo?**

**Opción A: Relaciones Polimórficas**
```php
// Un standard pertenece a UNA estación (polimórfico)
$table->morphs('workstationable'); // workstationable_type, workstationable_id
```

**Opción B: Foreign Keys Directas (actual)**
```php
// Un standard puede tener múltiples FK pero solo una activa
$table->foreignId('work_table_id')->nullable();
$table->foreignId('semi_auto_work_table_id')->nullable();
$table->foreignId('machine_id')->nullable();
```

### Implicaciones del Campo `assembly_mode`

Si mantenemos las FK directas, el campo `assembly_mode` se vuelve **redundante** porque podemos calcular el modo dinámicamente:

```php
// Enfoque actual (redundante)
assembly_mode = 'manual' (y work_table_id = 5)

// Enfoque mejorado (calculado)
assembly_mode = this->workTable ? 'manual' : (this->semiAutoWorkTable ? 'semi_automatic' : 'machine')
```

---

## Análisis de Estructura Actual

### Migraciones Existentes

#### 1. Areas (Base)

**Archivo:** `database/migrations/2025_07_20_170531_create_areas_table.php`

```php
Schema::create('areas', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('comments')->nullable();
    $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
    $table->foreignId('department_id')->constrained('departments');
    $table->timestamps();
});
```

**Análisis:**
- Todas las estaciones pertenecen a un área
- Relación: Area → hasMany → (Tables, SemiAutomatics, Machines)

#### 2. Semi_Automatics

**Archivo:** `database/migrations/2025_07_20_171916_create_semi__automatics_table.php`

```php
Schema::create('semi__automatics', function (Blueprint $table) {
    $table->id();
    $table->string('number');
    $table->integer('employees');
    $table->boolean('active');
    $table->string('comments')->nullable();
    $table->foreignId('area_id')->constrained('areas');
    $table->timestamps();
});
```

**Análisis:**
- Estructura casi idéntica a `tables`
- Campo `employees`: número de empleados requeridos
- Nombre de tabla con doble underscore: `semi__automatics` (inconsistencia de naming)

#### 3. Tables

**Archivo:** `database/migrations/2025_07_20_172105_create_tables_table.php`

```php
Schema::create('tables', function (Blueprint $table) {
    $table->id();
    $table->string('number')->unique();
    $table->integer('employees');
    $table->boolean('active');
    $table->text('comments')->nullable();
    $table->foreignId('area_id')->constrained('areas');
    $table->timestamps();
});
```

**Análisis:**
- Idéntica a `semi_automatics` excepto por tipo de dato en `comments` (text vs string)
- Campo `number` es unique

#### 4. Machines

**Archivo:** `database/migrations/2025_07_20_172007_create_machines_table.php`

```php
Schema::create('machines', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('brand')->nullable();
    $table->string('model')->nullable();
    $table->string('sn')->nullable();
    $table->string('asset_number')->nullable()->unique();
    $table->integer('employees');
    $table->decimal('setup_time', 8, 2);
    $table->decimal('maintenance_time', 8, 2);
    $table->boolean('active');
    $table->text('comments')->nullable();
    $table->foreignId('area_id')->constrained('areas');
    $table->timestamps();
});
```

**Análisis:**
- Estructura MÁS COMPLEJA que tables/semi_automatics
- Campos adicionales: brand, model, sn, asset_number
- Campos específicos: `setup_time`, `maintenance_time`
- Estos tiempos NO están en tables/semi_automatics

#### 5. Standards (Actual)

**Archivo:** `database/migrations/2025_12_14_190425_create_standards_table.php`

```php
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

    $table->index(['work_table_id', 'active'], 'standards_search_index');
    $table->index(['semi_auto_work_table_id', 'active'], 'standards_semi_auto_active_index');
    $table->index('effective_date', 'standards_effective_date_index');
    $table->index('active', 'standards_active_index');
    $table->index('machine_id', 'standards_machine_index');
    $table->index('part_id', 'standards_part_index');
});
```

**Análisis Crítico:**

**Relaciones:**
- `part_id`: REQUERIDO, cascade delete (correcto)
- `work_table_id`, `semi_auto_work_table_id`, `machine_id`: NULLABLE, set null on delete

**Problema de Diseño:**
- No hay validación a nivel DB de que solo UNA estación debe estar asignada
- Permite estados inválidos: todas en NULL o múltiples asignadas simultáneamente
- No hay constraint CHECK para mutua exclusividad

**Campos persons_1, persons_2, persons_3:**
- Propósito: Parece ser diferentes configuraciones de personal
- ¿Por qué 3 variantes? (falta documentación)
- Relación con `employees` de workstations no clara

**Índices:**
- Buenos índices compuestos para búsqueda por estación + activo
- Índice por fecha efectiva (para estándares históricos)

**FALTA CRÍTICO:**
- Campo `units_per_hour` (identificado en Spec 01)

### Modelos Existentes

#### 1. Modelo Table

**Archivo:** `app/Models/Table.php`

```php
class Table extends Model
{
    protected $fillable = [
        'number',
        'employees',
        'active',
        'comments',
        'area_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'employees' => 'integer',
    ];

    // Relación con Area
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    // FALTA: Relación inversa con Standards
    // public function standards() { return $this->hasMany(Standard::class, 'work_table_id'); }
}
```

**Análisis:**
- Modelo simple y limpio
- FALTA relación inversa con Standards
- Buenos scopes: `active()`, `inactive()`, `byArea()`, `search()`

#### 2. Modelo Semi_Automatic

**Archivo:** `app/Models/Semi_Automatic.php`

```php
class Semi_Automatic extends Model
{
    protected $fillable = [
        'number',
        'employees',
        'active',
        'comments',
        'area_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'employees' => 'integer',
    ];

    // Relación con Area
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    // FALTA: Relación inversa con Standards
}
```

**Análisis:**
- Idéntico a Table (código duplicado)
- FALTA relación inversa con Standards
- Mismo naming inconsistency: `Semi_Automatic` vs `semi__automatics`

#### 3. Modelo Machine

**Archivo:** `app/Models/Machine.php`

```php
class Machine extends Model
{
    protected $fillable = [
        'name',
        'brand',
        'model',
        'sn',
        'asset_number',
        'employees',
        'setup_time',
        'maintenance_time',
        'active',
        'comments',
        'area_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'setup_time' => 'decimal:2',
        'maintenance_time' => 'decimal:2',
        'employees' => 'integer',
    ];

    // Relación con Area
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    // Accessor: Full Identification
    public function getFullIdentificationAttribute()
    {
        $parts = array_filter([$this->brand, $this->model, $this->name]);
        return implode(' - ', $parts);
    }

    // FALTA: Relación inversa con Standards
}
```

**Análisis:**
- Modelo más rico que Table/Semi_Automatic
- Accessor útil para identificación
- FALTA relación inversa con Standards

#### 4. Modelo Standard (Actual)

**Archivo:** `app/Models/Standard.php`

```php
class Standard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'persons_1',
        'persons_2',
        'persons_3',
        'effective_date',
        'active',
        'description',
        'part_id',
        'work_table_id',
        'semi_auto_work_table_id',
        'machine_id'
    ];

    protected $casts = [
        'effective_date' => 'date',
        'persons_1' => 'integer',
        'persons_2' => 'integer',
        'persons_3' => 'integer',
        'active' => 'boolean',
        // ...
    ];

    // Relaciones
    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function workTable()
    {
        return $this->belongsTo(Table::class, 'work_table_id');
    }

    public function semiAutoWorkTable()
    {
        return $this->belongsTo(Semi_Automatic::class, 'semi_auto_work_table_id');
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        // Busca en relaciones workTable, semiAutoWorkTable, machine
    }
}
```

**Análisis Crítico:**

**FALTA:**
- Campo `units_per_hour` en fillable y casts
- Método para obtener la estación activa (helper)
- Método para calcular assembly_mode dinámicamente
- Validación de que solo UNA estación está asignada

**PRESENTE:**
- Búsqueda compleja que incluye todas las estaciones (líneas 104-122)
- Soft deletes implementado correctamente

### Componentes Livewire Existentes

#### StandardCreate.php

**Archivo:** `app/Livewire/Admin/Standards/StandardCreate.php`

```php
class StandardCreate extends Component
{
    public ?int $part_id = null;
    public ?int $work_table_id = null;
    public ?int $semi_auto_work_table_id = null;
    public ?int $machine_id = null;
    // ...

    protected function rules(): array
    {
        return [
            'part_id' => 'required|exists:parts,id',
            'work_table_id' => 'nullable|exists:tables,id',
            'semi_auto_work_table_id' => 'nullable|exists:semi_automatics,id',
            'machine_id' => 'nullable|exists:machines,id',
            // ...
        ];
    }

    public function saveStandard(): void
    {
        $this->validate();

        Standard::create([
            'part_id' => $this->part_id,
            'work_table_id' => $this->work_table_id ?: null,
            'semi_auto_work_table_id' => $this->semi_auto_work_table_id ?: null,
            'machine_id' => $this->machine_id ?: null,
            // ...
        ]);
    }

    public function render()
    {
        return view('livewire.admin.standards.standard-create', [
            'parts' => Part::orderBy('number')->get(),
            'tables' => Table::active()->orderBy('number')->get(),
            'semiAutomaticTables' => Semi_Automatic::active()->orderBy('number')->get(),
            'machines' => Machine::active()->orderBy('name')->get(),
        ]);
    }
}
```

**Análisis:**

**PROBLEMA CRÍTICO:**
- No hay validación de que solo UNA estación debe estar seleccionada
- Usuario puede seleccionar múltiples estaciones simultáneamente
- Permite guardar standard sin ninguna estación

**FALTA:**
- Campo `units_per_hour`
- Validación custom para mutua exclusividad

---

## Impacto Arquitectural

### Backend

#### Modelos Afectados

1. **Standard** (modificación CRÍTICA)
   - Agregar campo `units_per_hour`
   - Agregar método `getWorkstation()`: devuelve la estación activa
   - Agregar método `getAssemblyMode()`: calcula modo dinámicamente
   - Agregar método `calculateRequiredHours(int $quantity)`
   - Actualizar validaciones

2. **Table, Semi_Automatic, Machine** (actualización menor)
   - Agregar relación inversa `standards()`
   - NO requiere cambios en estructura

#### Servicios Afectados

1. **CapacityCalculatorService** (del Spec 01)
   - Usar `Standard::getAssemblyMode()` en lugar de campo `assembly_mode`
   - Adaptar lógica de búsqueda de standards

### Frontend

#### Componentes Livewire Afectados

1. **StandardCreate** (actualización CRÍTICA)
   - Agregar campo `units_per_hour`
   - Agregar validación custom: solo UNA estación
   - Mejorar UX: deshabilitar otras estaciones al seleccionar una

2. **StandardEdit** (actualización similar)
   - Mismos cambios que StandardCreate

3. **StandardList** (actualización menor)
   - Mostrar columna `units_per_hour`
   - Mostrar tipo de estación calculado dinámicamente

4. **StandardShow** (actualización menor)
   - Mostrar información de estación activa
   - Mostrar assembly_mode calculado

### Base de Datos

#### Migración Nueva

**Archivo:** `add_units_per_hour_to_standards_table.php`

**Campos a agregar:**
- `units_per_hour` (integer, NOT NULL, default 1)
- Índice compuesto para búsqueda por estación + parte

**NO agregar:**
- `assembly_mode` (se calcula dinámicamente)

#### Índices a Revisar

Los índices actuales cubren búsquedas por estación individual. Considerar agregar:

```php
$table->index(['part_id', 'active', 'effective_date'], 'standards_part_active_date_index');
```

---

## Evaluación de Opciones de Diseño

### Opción A: Relaciones Polimórficas

**Diseño:**
```php
Schema::table('standards', function (Blueprint $table) {
    // Eliminar FKs existentes
    $table->dropForeign(['work_table_id']);
    $table->dropForeign(['semi_auto_work_table_id']);
    $table->dropForeign(['machine_id']);
    $table->dropColumn(['work_table_id', 'semi_auto_work_table_id', 'machine_id']);

    // Agregar relación polimórfica
    $table->morphs('workstationable'); // workstationable_type, workstationable_id
});
```

**Modelo:**
```php
class Standard extends Model
{
    public function workstation()
    {
        return $this->morphTo('workstationable');
    }
}

// Uso
$standard->workstation; // devuelve Table, Semi_Automatic o Machine
$standard->workstationable_type; // 'App\Models\Table'
```

**VENTAJAS:**
- Garantiza a nivel DB que solo UNA estación está asignada
- Diseño más limpio y normalizado
- Simplifica queries (solo un JOIN)
- Elimina columnas redundantes

**DESVENTAJAS:**
- REQUIERE REFACTORIZACIÓN COMPLETA de:
  - Modelo Standard (cambiar 3 relaciones por 1 polimórfica)
  - StandardCreate/Edit components (cambiar lógica de selección)
  - Vistas blade (cambiar selects)
  - Scope search (cambiar búsqueda)
  - Factories y Seeders
- ROMPE DATOS EXISTENTES (requiere migración compleja)
- REQUIERE CÓDIGO DE MIGRACIÓN para convertir:
  ```php
  // Old: work_table_id = 5
  // New: workstationable_type = 'App\Models\Table', workstationable_id = 5
  ```
- Mayor complejidad en queries compuestos
- Pérdida de índices específicos por tipo de estación

**ESTIMACIÓN DE ESFUERZO:**
- 3-4 días de desarrollo
- Alto riesgo de bugs en producción
- Requiere testing extensivo

### Opción B: Mantener Foreign Keys Directas (RECOMENDADO)

**Diseño:**
```php
// Mantener estructura actual, solo agregar validación

Schema::table('standards', function (Blueprint $table) {
    $table->integer('units_per_hour')->after('part_id')->default(1);
    $table->index(['part_id', 'active', 'units_per_hour'], 'standards_part_performance_index');
});
```

**Modelo (métodos helper):**
```php
class Standard extends Model
{
    // Relaciones existentes (sin cambios)
    public function workTable() { ... }
    public function semiAutoWorkTable() { ... }
    public function machine() { ... }

    // NUEVOS MÉTODOS HELPER

    /**
     * Obtiene la estación de trabajo activa
     *
     * @return Table|Semi_Automatic|Machine|null
     */
    public function getWorkstation()
    {
        return $this->workTable ?? $this->semiAutoWorkTable ?? $this->machine;
    }

    /**
     * Obtiene el tipo de estación (assembly mode)
     *
     * @return string|null 'manual', 'semi_automatic', 'machine'
     */
    public function getAssemblyMode(): ?string
    {
        if ($this->workTable) return 'manual';
        if ($this->semiAutoWorkTable) return 'semi_automatic';
        if ($this->machine) return 'machine';
        return null;
    }

    /**
     * Verifica si tiene una estación asignada
     *
     * @return bool
     */
    public function hasWorkstation(): bool
    {
        return $this->work_table_id || $this->semi_auto_work_table_id || $this->machine_id;
    }

    /**
     * Cuenta cuántas estaciones están asignadas
     *
     * @return int
     */
    public function countAssignedWorkstations(): int
    {
        return collect([
            $this->work_table_id,
            $this->semi_auto_work_table_id,
            $this->machine_id,
        ])->filter()->count();
    }
}
```

**Validación (Livewire):**
```php
class StandardCreate extends Component
{
    protected function rules(): array
    {
        return [
            'part_id' => 'required|exists:parts,id',
            'units_per_hour' => 'required|integer|min:1|max:10000',
            'work_table_id' => [
                'nullable',
                'exists:tables,id',
                new OnlyOneWorkstation($this->semi_auto_work_table_id, $this->machine_id)
            ],
            'semi_auto_work_table_id' => [
                'nullable',
                'exists:semi_automatics,id',
                new OnlyOneWorkstation($this->work_table_id, $this->machine_id)
            ],
            'machine_id' => [
                'nullable',
                'exists:machines,id',
                new OnlyOneWorkstation($this->work_table_id, $this->semi_auto_work_table_id)
            ],
            // ...
        ];
    }

    public function updated($propertyName)
    {
        // Cuando se selecciona una estación, limpiar las otras
        if ($propertyName === 'work_table_id' && $this->work_table_id) {
            $this->semi_auto_work_table_id = null;
            $this->machine_id = null;
        }

        if ($propertyName === 'semi_auto_work_table_id' && $this->semi_auto_work_table_id) {
            $this->work_table_id = null;
            $this->machine_id = null;
        }

        if ($propertyName === 'machine_id' && $this->machine_id) {
            $this->work_table_id = null;
            $this->semi_auto_work_table_id = null;
        }
    }
}
```

**Custom Validation Rule:**
```php
// app/Rules/OnlyOneWorkstation.php

class OnlyOneWorkstation implements Rule
{
    protected $other1;
    protected $other2;

    public function __construct($other1, $other2)
    {
        $this->other1 = $other1;
        $this->other2 = $other2;
    }

    public function passes($attribute, $value)
    {
        if (!$value) {
            return true; // Si no hay valor, es válido
        }

        // Si hay valor, los otros dos deben ser null
        return is_null($this->other1) && is_null($this->other2);
    }

    public function message()
    {
        return 'Solo puede seleccionar UNA estación de trabajo (mesa, mesa semi-automática o máquina).';
    }
}
```

**VENTAJAS:**
- CERO REFACTORIZACIÓN del código existente
- CERO MIGRACIÓN de datos existentes
- Mantiene índices específicos por tipo
- Queries optimizados (índices existentes)
- Bajo riesgo de bugs
- Compatibilidad con código Livewire actual

**DESVENTAJAS:**
- No previene estados inválidos a nivel DB (solo en aplicación)
- Requiere validación custom en Livewire
- Columnas redundantes (3 FKs cuando solo 1 es activa)
- Scope search más complejo (pero ya está implementado)

**ESTIMACIÓN DE ESFUERZO:**
- 1 día de desarrollo
- Bajo riesgo
- Testing rápido

### Opción C: Tabla Intermedia workstation_standards (Overkill)

**NO RECOMENDADO** - Introduce complejidad innecesaria para un simple 1:1.

---

## Propuesta de Solución

### Decisión: OPCIÓN B - Mantener Foreign Keys Directas

**Justificación:**

1. **Pragmatismo**: El código ya está implementado y funcionando
2. **Costo-Beneficio**: Refactorizar a polimórficos requiere 3-4x más esfuerzo
3. **Riesgo**: Mantener estructura actual = bajo riesgo, polimórficos = alto riesgo
4. **Performance**: FKs directas con índices existentes = óptimo
5. **Consistencia**: El equipo ya conoce el patrón actual

### Cambios Propuestos

#### 1. Migración: Agregar `units_per_hour`

**SOLO agregar este campo**, NO agregar `assembly_mode`:

```php
Schema::table('standards', function (Blueprint $table) {
    $table->integer('units_per_hour')
          ->after('part_id')
          ->default(1)
          ->comment('Unidades producidas por hora en esta estación');

    // Índice para mejorar performance de cálculos de capacidad
    $table->index(['part_id', 'active', 'units_per_hour'], 'standards_part_performance_index');
});
```

#### 2. Modelo Standard: Agregar Métodos Helper

```php
class Standard extends Model
{
    // Agregar a $fillable
    protected $fillable = [
        'persons_1',
        'persons_2',
        'persons_3',
        'effective_date',
        'active',
        'description',
        'part_id',
        'work_table_id',
        'semi_auto_work_table_id',
        'machine_id',
        'units_per_hour', // NUEVO
    ];

    // Agregar a $casts
    protected $casts = [
        'effective_date' => 'date',
        'persons_1' => 'integer',
        'persons_2' => 'integer',
        'persons_3' => 'integer',
        'active' => 'boolean',
        'units_per_hour' => 'integer', // NUEVO
        // ...
    ];

    // ===============================================
    // NUEVOS MÉTODOS HELPER
    // ===============================================

    /**
     * Obtiene la estación de trabajo activa (primera no-null)
     *
     * @return Table|Semi_Automatic|Machine|null
     */
    public function getWorkstation()
    {
        return $this->workTable ?? $this->semiAutoWorkTable ?? $this->machine;
    }

    /**
     * Obtiene el tipo de ensamble (assembly mode)
     *
     * @return string|null 'manual', 'semi_automatic', 'machine'
     */
    public function getAssemblyMode(): ?string
    {
        if ($this->work_table_id) return 'manual';
        if ($this->semi_auto_work_table_id) return 'semi_automatic';
        if ($this->machine_id) return 'machine';
        return null;
    }

    /**
     * Accessor para assembly_mode (permite usar $standard->assembly_mode)
     *
     * @return string|null
     */
    public function getAssemblyModeAttribute(): ?string
    {
        return $this->getAssemblyMode();
    }

    /**
     * Verifica si tiene una estación asignada
     *
     * @return bool
     */
    public function hasWorkstation(): bool
    {
        return $this->work_table_id || $this->semi_auto_work_table_id || $this->machine_id;
    }

    /**
     * Cuenta cuántas estaciones están asignadas
     * Útil para validación y debugging
     *
     * @return int
     */
    public function countAssignedWorkstations(): int
    {
        return collect([
            $this->work_table_id,
            $this->semi_auto_work_table_id,
            $this->machine_id,
        ])->filter()->count();
    }

    /**
     * Obtiene el nombre de la estación para display
     *
     * @return string
     */
    public function getWorkstationNameAttribute(): string
    {
        $workstation = $this->getWorkstation();

        if (!$workstation) {
            return 'Sin estación asignada';
        }

        if ($workstation instanceof Machine) {
            return $workstation->full_identification ?? $workstation->name;
        }

        return $workstation->number;
    }

    /**
     * Calcula las horas requeridas para producir una cantidad
     *
     * Implementa Propiedad 4 del Spec 01
     *
     * @param int $quantity Cantidad a producir
     * @return float Horas requeridas
     * @throws \DivisionByZeroError Si units_per_hour es 0
     */
    public function calculateRequiredHours(int $quantity): float
    {
        if ($this->units_per_hour === 0) {
            throw new \DivisionByZeroError(
                "El estándar para la parte '{$this->part->number}' tiene units_per_hour = 0"
            );
        }

        return round($quantity / $this->units_per_hour, 2);
    }

    /**
     * Scope para filtrar por tipo de estación
     *
     * @param Builder $query
     * @param string $type 'manual', 'semi_automatic', 'machine'
     * @return Builder
     */
    public function scopeByAssemblyMode(Builder $query, string $type): Builder
    {
        return match($type) {
            'manual' => $query->whereNotNull('work_table_id'),
            'semi_automatic' => $query->whereNotNull('semi_auto_work_table_id'),
            'machine' => $query->whereNotNull('machine_id'),
            default => $query,
        };
    }
}
```

#### 3. Validación Custom Rule

**Archivo:** `app/Rules/OnlyOneWorkstation.php`

```php
<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class OnlyOneWorkstation implements Rule
{
    protected $otherWorkstation1;
    protected $otherWorkstation2;
    protected $fieldName;

    /**
     * Create a new rule instance.
     *
     * @param mixed $other1 Valor de la segunda estación
     * @param mixed $other2 Valor de la tercera estación
     * @param string $fieldName Nombre del campo para mensajes
     */
    public function __construct($other1, $other2, string $fieldName = 'estación')
    {
        $this->otherWorkstation1 = $other1;
        $this->otherWorkstation2 = $other2;
        $this->fieldName = $fieldName;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Si el campo actual no tiene valor, es válido
        if (!$value) {
            return true;
        }

        // Si el campo actual tiene valor, los otros dos deben ser null
        return is_null($this->otherWorkstation1) && is_null($this->otherWorkstation2);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Solo puede seleccionar UNA estación de trabajo. Por favor, deseleccione las otras opciones.';
    }
}
```

#### 4. Actualizar Componente StandardCreate

```php
<?php

namespace App\Livewire\Admin\Standards;

use App\Models\Machine;
use App\Models\Part;
use App\Models\Semi_Automatic;
use App\Models\Standard;
use App\Models\Table;
use App\Rules\OnlyOneWorkstation;
use Livewire\Component;

class StandardCreate extends Component
{
    public ?int $part_id = null;
    public ?int $work_table_id = null;
    public ?int $semi_auto_work_table_id = null;
    public ?int $machine_id = null;
    public int $units_per_hour = 1; // NUEVO campo
    public string $persons_1 = '';
    public string $persons_2 = '';
    public string $persons_3 = '';
    public string $effective_date = '';
    public bool $active = true;
    public string $description = '';

    public function mount(): void
    {
        $this->effective_date = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'part_id' => 'required|exists:parts,id',
            'units_per_hour' => 'required|integer|min:1|max:10000', // NUEVO
            'work_table_id' => [
                'nullable',
                'exists:tables,id',
                new OnlyOneWorkstation(
                    $this->semi_auto_work_table_id,
                    $this->machine_id,
                    'Mesa de trabajo'
                ),
            ],
            'semi_auto_work_table_id' => [
                'nullable',
                'exists:semi_automatics,id',
                new OnlyOneWorkstation(
                    $this->work_table_id,
                    $this->machine_id,
                    'Mesa semi-automática'
                ),
            ],
            'machine_id' => [
                'nullable',
                'exists:machines,id',
                new OnlyOneWorkstation(
                    $this->work_table_id,
                    $this->semi_auto_work_table_id,
                    'Máquina'
                ),
            ],
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
            'units_per_hour.required' => 'Las unidades por hora son requeridas.',
            'units_per_hour.min' => 'Las unidades por hora deben ser al menos 1.',
            'units_per_hour.max' => 'Las unidades por hora no pueden exceder 10,000.',
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

    /**
     * Al actualizar un campo de estación, limpiar los otros
     * Mejora UX: auto-deselección de estaciones
     */
    public function updated($propertyName)
    {
        if ($propertyName === 'work_table_id' && $this->work_table_id) {
            $this->semi_auto_work_table_id = null;
            $this->machine_id = null;
        }

        if ($propertyName === 'semi_auto_work_table_id' && $this->semi_auto_work_table_id) {
            $this->work_table_id = null;
            $this->machine_id = null;
        }

        if ($propertyName === 'machine_id' && $this->machine_id) {
            $this->work_table_id = null;
            $this->semi_auto_work_table_id = null;
        }
    }

    public function saveStandard(): void
    {
        $this->validate();

        // Validación adicional: al menos UNA estación debe estar seleccionada
        if (!$this->work_table_id && !$this->semi_auto_work_table_id && !$this->machine_id) {
            $this->addError('work_table_id', 'Debe seleccionar al menos UNA estación de trabajo.');
            return;
        }

        Standard::create([
            'part_id' => $this->part_id,
            'units_per_hour' => $this->units_per_hour, // NUEVO
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
            'tables' => Table::active()->orderBy('number')->get(),
            'semiAutomaticTables' => Semi_Automatic::active()->orderBy('number')->get(),
            'machines' => Machine::active()->orderBy('name')->get(),
        ]);
    }
}
```

#### 5. Actualizar Vista Blade

**Archivo:** `resources/views/livewire/admin/standards/standard-create.blade.php`

**Agregar campo units_per_hour:**

```blade
{{-- Después del campo part_id, agregar: --}}

<div class="mb-4">
    <label for="units_per_hour" class="block text-sm font-medium text-gray-700 mb-2">
        Unidades por Hora *
        <span class="text-gray-500 text-xs">(Productividad de esta estación)</span>
    </label>
    <input
        type="number"
        id="units_per_hour"
        wire:model="units_per_hour"
        min="1"
        max="10000"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        placeholder="Ej: 100"
        required
    >
    @error('units_per_hour')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
    <p class="text-xs text-gray-500 mt-1">
        Número de unidades que esta estación puede producir en una hora.
    </p>
</div>
```

**Mejorar sección de estaciones con radio buttons:**

```blade
<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Estación de Trabajo *
        <span class="text-gray-500 text-xs">(Seleccione solo UNA opción)</span>
    </label>

    <div class="space-y-3 border border-gray-200 rounded-lg p-4">
        {{-- Mesa Manual --}}
        <div class="flex items-start">
            <input
                type="radio"
                id="workstation_type_manual"
                name="workstation_type"
                wire:click="$set('work_table_id', '')"
                class="mt-1"
            >
            <div class="ml-3 flex-1">
                <label for="workstation_type_manual" class="font-medium text-gray-700">
                    Mesa de Trabajo Manual
                </label>
                @if($work_table_id || (!$semi_auto_work_table_id && !$machine_id))
                    <select
                        wire:model.live="work_table_id"
                        class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">Seleccionar mesa...</option>
                        @foreach($tables as $table)
                            <option value="{{ $table->id }}">
                                Mesa {{ $table->number }} - {{ $table->employees }} empleados ({{ $table->area->name }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        {{-- Mesa Semi-Automática --}}
        <div class="flex items-start">
            <input
                type="radio"
                id="workstation_type_semi"
                name="workstation_type"
                wire:click="$set('semi_auto_work_table_id', '')"
                class="mt-1"
            >
            <div class="ml-3 flex-1">
                <label for="workstation_type_semi" class="font-medium text-gray-700">
                    Mesa Semi-Automática
                </label>
                @if($semi_auto_work_table_id || (!$work_table_id && !$machine_id))
                    <select
                        wire:model.live="semi_auto_work_table_id"
                        class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">Seleccionar mesa semi-automática...</option>
                        @foreach($semiAutomaticTables as $table)
                            <option value="{{ $table->id }}">
                                Mesa {{ $table->number }} - {{ $table->employees }} empleados ({{ $table->area->name }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        {{-- Máquina --}}
        <div class="flex items-start">
            <input
                type="radio"
                id="workstation_type_machine"
                name="workstation_type"
                wire:click="$set('machine_id', '')"
                class="mt-1"
            >
            <div class="ml-3 flex-1">
                <label for="workstation_type_machine" class="font-medium text-gray-700">
                    Máquina
                </label>
                @if($machine_id || (!$work_table_id && !$semi_auto_work_table_id))
                    <select
                        wire:model.live="machine_id"
                        class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">Seleccionar máquina...</option>
                        @foreach($machines as $machine)
                            <option value="{{ $machine->id }}">
                                {{ $machine->full_identification }} - {{ $machine->employees }} empleados ({{ $machine->area->name }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>
    </div>

    @error('work_table_id')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
    @error('semi_auto_work_table_id')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
    @error('machine_id')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>
```

#### 6. Actualizar Modelos de Estaciones (Relaciones Inversas)

**Table.php:**
```php
/**
 * Una mesa puede tener múltiples estándares
 */
public function standards()
{
    return $this->hasMany(Standard::class, 'work_table_id');
}
```

**Semi_Automatic.php:**
```php
/**
 * Una mesa semi-automática puede tener múltiples estándares
 */
public function standards()
{
    return $this->hasMany(Standard::class, 'semi_auto_work_table_id');
}
```

**Machine.php:**
```php
/**
 * Una máquina puede tener múltiples estándares
 */
public function standards()
{
    return $this->hasMany(Standard::class, 'machine_id');
}
```

---

## Plan de Implementación Detallado

### FASE 1: Preparación (Día 1 - Mañana)

#### Tarea 1.1: Crear Migración

**Comando:**
```bash
php artisan make:migration add_units_per_hour_to_standards_table
```

**Archivo:** `database/migrations/YYYY_MM_DD_HHMMSS_add_units_per_hour_to_standards_table.php`

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
            // Agregar campo units_per_hour después de part_id
            $table->integer('units_per_hour')
                  ->after('part_id')
                  ->default(1)
                  ->comment('Unidades producidas por hora en esta estación');

            // Índice compuesto para optimizar búsquedas de capacidad
            $table->index(
                ['part_id', 'active', 'units_per_hour'],
                'standards_part_performance_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('standards', function (Blueprint $table) {
            $table->dropIndex('standards_part_performance_index');
            $table->dropColumn('units_per_hour');
        });
    }
};
```

**Ejecutar:**
```bash
php artisan migrate
```

#### Tarea 1.2: Crear Custom Validation Rule

**Comando:**
```bash
php artisan make:rule OnlyOneWorkstation
```

**Archivo:** `app/Rules/OnlyOneWorkstation.php`

(Ver código completo en sección "Propuesta de Solución")

#### Tarea 1.3: Actualizar Modelo Standard

**Archivo:** `app/Models/Standard.php`

**Modificaciones:**
1. Agregar `units_per_hour` a `$fillable`
2. Agregar cast para `units_per_hour`
3. Agregar métodos helper (ver código completo arriba)

### FASE 2: Actualización de Componentes Livewire (Día 1 - Tarde)

#### Tarea 2.1: Actualizar StandardCreate

**Archivo:** `app/Livewire/Admin/Standards/StandardCreate.php`

**Cambios:**
1. Agregar propiedad `$units_per_hour`
2. Actualizar `rules()` con validación de `units_per_hour` y `OnlyOneWorkstation`
3. Agregar método `updated()` para auto-deselección
4. Validar que al menos una estación esté seleccionada
5. Incluir `units_per_hour` en `create()`

#### Tarea 2.2: Actualizar StandardEdit

**Archivo:** `app/Livewire/Admin/Standards/StandardEdit.php`

**Cambios similares a StandardCreate:**
1. Cargar `units_per_hour` en `mount()`
2. Misma validación que StandardCreate
3. Incluir en `update()`

#### Tarea 2.3: Actualizar StandardList

**Archivo:** `app/Livewire/Admin/Standards/StandardList.php`

**Cambios:**
1. Agregar columna para mostrar `units_per_hour`
2. Agregar columna para mostrar tipo de estación (usando `getAssemblyMode()`)

#### Tarea 2.4: Actualizar StandardShow

**Archivo:** `app/Livewire/Admin/Standards/StandardShow.php`

**Cambios:**
1. Mostrar campo `units_per_hour`
2. Mostrar tipo de estación y nombre (usando helpers)

### FASE 3: Actualización de Vistas Blade (Día 2 - Mañana)

#### Tarea 3.1: Actualizar Vista Create

**Archivo:** `resources/views/livewire/admin/standards/standard-create.blade.php`

**Cambios:**
1. Agregar campo `units_per_hour`
2. Mejorar UX de selección de estación con radio buttons
3. Agregar tooltips explicativos

#### Tarea 3.2: Actualizar Vista Edit

**Archivo:** `resources/views/livewire/admin/standards/standard-edit.blade.php`

**Cambios similares a Create**

#### Tarea 3.3: Actualizar Vista List

**Archivo:** `resources/views/livewire/admin/standards/standard-list.blade.php`

**Agregar columnas:**
```blade
<th>Unidades/Hora</th>
<th>Estación</th>
<th>Tipo</th>

<!-- En tbody -->
<td>{{ $standard->units_per_hour }}</td>
<td>{{ $standard->workstation_name }}</td>
<td>
    <span class="px-2 py-1 text-xs rounded-full
        @if($standard->assembly_mode === 'manual') bg-blue-100 text-blue-800
        @elseif($standard->assembly_mode === 'semi_automatic') bg-yellow-100 text-yellow-800
        @else bg-green-100 text-green-800
        @endif
    ">
        {{ ucfirst(str_replace('_', ' ', $standard->assembly_mode ?? 'N/A')) }}
    </span>
</td>
```

#### Tarea 3.4: Actualizar Vista Show

**Archivo:** `resources/views/livewire/admin/standards/standard-show.blade.php`

**Agregar secciones:**
```blade
<div class="mb-4">
    <label class="font-semibold">Unidades por Hora:</label>
    <p>{{ $standard->units_per_hour }} unidades/hora</p>
</div>

<div class="mb-4">
    <label class="font-semibold">Tipo de Ensamble:</label>
    <p>{{ ucfirst(str_replace('_', ' ', $standard->assembly_mode ?? 'N/A')) }}</p>
</div>

<div class="mb-4">
    <label class="font-semibold">Estación de Trabajo:</label>
    <p>{{ $standard->workstation_name }}</p>
    @if($standard->workstation)
        <p class="text-sm text-gray-600">
            Empleados requeridos: {{ $standard->workstation->employees }}
        </p>
    @endif
</div>
```

### FASE 4: Actualización de Modelos de Estaciones (Día 2 - Tarde)

#### Tarea 4.1: Actualizar Table.php

Agregar relación inversa `standards()`

#### Tarea 4.2: Actualizar Semi_Automatic.php

Agregar relación inversa `standards()`

#### Tarea 4.3: Actualizar Machine.php

Agregar relación inversa `standards()`

### FASE 5: Testing (Día 3)

#### Tarea 5.1: Unit Tests - Modelo Standard

**Archivo:** `tests/Unit/Models/StandardTest.php`

**Tests:**
```php
/** @test */
public function test_getWorkstation_returns_work_table_when_assigned()
{
    $table = Table::factory()->create();
    $standard = Standard::factory()->create(['work_table_id' => $table->id]);

    $this->assertInstanceOf(Table::class, $standard->getWorkstation());
    $this->assertEquals($table->id, $standard->getWorkstation()->id);
}

/** @test */
public function test_getAssemblyMode_returns_manual_for_work_table()
{
    $standard = Standard::factory()->create(['work_table_id' => 1]);
    $this->assertEquals('manual', $standard->getAssemblyMode());
}

/** @test */
public function test_calculateRequiredHours_calculates_correctly()
{
    $standard = Standard::factory()->create(['units_per_hour' => 100]);
    $hours = $standard->calculateRequiredHours(500);

    $this->assertEquals(5.0, $hours);
}

/** @test */
public function test_countAssignedWorkstations_returns_zero_when_none()
{
    $standard = Standard::factory()->create([
        'work_table_id' => null,
        'semi_auto_work_table_id' => null,
        'machine_id' => null,
    ]);

    $this->assertEquals(0, $standard->countAssignedWorkstations());
}

/** @test */
public function test_countAssignedWorkstations_returns_one_when_single()
{
    $standard = Standard::factory()->create([
        'work_table_id' => 1,
        'semi_auto_work_table_id' => null,
        'machine_id' => null,
    ]);

    $this->assertEquals(1, $standard->countAssignedWorkstations());
}
```

#### Tarea 5.2: Feature Tests - StandardCreate Component

**Archivo:** `tests/Feature/Livewire/StandardCreateTest.php`

**Tests:**
```php
/** @test */
public function test_can_create_standard_with_work_table()
{
    $part = Part::factory()->create();
    $table = Table::factory()->create();

    Livewire::test(StandardCreate::class)
        ->set('part_id', $part->id)
        ->set('units_per_hour', 100)
        ->set('work_table_id', $table->id)
        ->call('saveStandard')
        ->assertRedirect(route('admin.standards.index'));

    $this->assertDatabaseHas('standards', [
        'part_id' => $part->id,
        'units_per_hour' => 100,
        'work_table_id' => $table->id,
        'semi_auto_work_table_id' => null,
        'machine_id' => null,
    ]);
}

/** @test */
public function test_cannot_select_multiple_workstations()
{
    $part = Part::factory()->create();
    $table = Table::factory()->create();
    $machine = Machine::factory()->create();

    Livewire::test(StandardCreate::class)
        ->set('part_id', $part->id)
        ->set('units_per_hour', 100)
        ->set('work_table_id', $table->id)
        ->set('machine_id', $machine->id)
        ->call('saveStandard')
        ->assertHasErrors(['work_table_id', 'machine_id']);
}

/** @test */
public function test_auto_deselects_other_workstations()
{
    $table = Table::factory()->create();
    $machine = Machine::factory()->create();

    Livewire::test(StandardCreate::class)
        ->set('work_table_id', $table->id)
        ->assertSet('semi_auto_work_table_id', null)
        ->assertSet('machine_id', null)
        ->set('machine_id', $machine->id)
        ->assertSet('work_table_id', null);
}

/** @test */
public function test_units_per_hour_is_required()
{
    $part = Part::factory()->create();

    Livewire::test(StandardCreate::class)
        ->set('part_id', $part->id)
        ->set('units_per_hour', '')
        ->call('saveStandard')
        ->assertHasErrors(['units_per_hour']);
}

/** @test */
public function test_units_per_hour_must_be_positive()
{
    $part = Part::factory()->create();

    Livewire::test(StandardCreate::class)
        ->set('part_id', $part->id)
        ->set('units_per_hour', 0)
        ->call('saveStandard')
        ->assertHasErrors(['units_per_hour']);
}
```

#### Tarea 5.3: Integration Tests - CapacityCalculatorService

**Archivo:** `tests/Integration/Services/CapacityCalculatorServiceTest.php`

**Tests:**
```php
/** @test */
public function test_calculateRequiredHours_uses_standard_units_per_hour()
{
    $part = Part::factory()->create();
    $standard = Standard::factory()->create([
        'part_id' => $part->id,
        'units_per_hour' => 100,
        'active' => true,
    ]);

    $service = new CapacityCalculatorService();
    $hours = $service->calculateRequiredHours($part, 500);

    $this->assertEquals(5.0, $hours);
}

/** @test */
public function test_calculateRequiredHours_filters_by_assembly_mode()
{
    $part = Part::factory()->create();

    Standard::factory()->create([
        'part_id' => $part->id,
        'work_table_id' => 1,
        'units_per_hour' => 100,
        'active' => true,
    ]);

    Standard::factory()->create([
        'part_id' => $part->id,
        'machine_id' => 1,
        'units_per_hour' => 200,
        'active' => true,
    ]);

    $service = new CapacityCalculatorService();

    $hoursManual = $service->calculateRequiredHours($part, 500, 'manual');
    $hoursMachine = $service->calculateRequiredHours($part, 500, 'machine');

    $this->assertEquals(5.0, $hoursManual);
    $this->assertEquals(2.5, $hoursMachine);
}
```

### FASE 6: Documentación y Refinamiento (Día 4)

#### Tarea 6.1: Crear/Actualizar Seeders

**StandardSeeder con units_per_hour:**
```php
<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\Standard;
use App\Models\Table;
use App\Models\Semi_Automatic;
use App\Models\Machine;
use Illuminate\Database\Seeder;

class StandardSeeder extends Seeder
{
    public function run(): void
    {
        $parts = Part::all();
        $tables = Table::all();
        $semiAutos = Semi_Automatic::all();
        $machines = Machine::all();

        foreach ($parts as $part) {
            // Crear standard para mesa manual
            if ($tables->isNotEmpty()) {
                Standard::create([
                    'part_id' => $part->id,
                    'work_table_id' => $tables->random()->id,
                    'units_per_hour' => rand(50, 200),
                    'persons_1' => rand(1, 3),
                    'effective_date' => now(),
                    'active' => true,
                    'description' => "Estándar manual para {$part->number}",
                ]);
            }

            // Crear standard para semi-automático
            if ($semiAutos->isNotEmpty()) {
                Standard::create([
                    'part_id' => $part->id,
                    'semi_auto_work_table_id' => $semiAutos->random()->id,
                    'units_per_hour' => rand(100, 300),
                    'persons_1' => rand(1, 2),
                    'effective_date' => now(),
                    'active' => true,
                    'description' => "Estándar semi-automático para {$part->number}",
                ]);
            }

            // Crear standard para máquina
            if ($machines->isNotEmpty()) {
                Standard::create([
                    'part_id' => $part->id,
                    'machine_id' => $machines->random()->id,
                    'units_per_hour' => rand(200, 500),
                    'persons_1' => rand(1, 2),
                    'effective_date' => now(),
                    'active' => true,
                    'description' => "Estándar automatizado para {$part->number}",
                ]);
            }
        }
    }
}
```

#### Tarea 6.2: Actualizar Factory

**StandardFactory:**
```php
<?php

namespace Database\Factories;

use App\Models\Machine;
use App\Models\Part;
use App\Models\Semi_Automatic;
use App\Models\Standard;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

class StandardFactory extends Factory
{
    protected $model = Standard::class;

    public function definition(): array
    {
        // Generar solo UNA estación aleatoriamente
        $workstationType = $this->faker->randomElement(['table', 'semi_auto', 'machine']);

        return [
            'part_id' => Part::factory(),
            'work_table_id' => $workstationType === 'table' ? Table::factory() : null,
            'semi_auto_work_table_id' => $workstationType === 'semi_auto' ? Semi_Automatic::factory() : null,
            'machine_id' => $workstationType === 'machine' ? Machine::factory() : null,
            'units_per_hour' => $this->faker->numberBetween(50, 500),
            'persons_1' => $this->faker->numberBetween(1, 5),
            'persons_2' => $this->faker->optional()->numberBetween(1, 5),
            'persons_3' => $this->faker->optional()->numberBetween(1, 5),
            'effective_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'active' => true,
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    public function withWorkTable(): static
    {
        return $this->state(fn (array $attributes) => [
            'work_table_id' => Table::factory(),
            'semi_auto_work_table_id' => null,
            'machine_id' => null,
        ]);
    }

    public function withSemiAutomatic(): static
    {
        return $this->state(fn (array $attributes) => [
            'work_table_id' => null,
            'semi_auto_work_table_id' => Semi_Automatic::factory(),
            'machine_id' => null,
        ]);
    }

    public function withMachine(): static
    {
        return $this->state(fn (array $attributes) => [
            'work_table_id' => null,
            'semi_auto_work_table_id' => null,
            'machine_id' => Machine::factory(),
        ]);
    }
}
```

#### Tarea 6.3: Documentar Patrones y Convenciones

**Crear:** `docs/standards-workstation-relationships.md`

```markdown
# Relaciones entre Standards y Estaciones de Trabajo

## Patrón de Diseño

Los `standards` se relacionan con estaciones de trabajo mediante **foreign keys directas** (no polimórficas).

### Estructura

Un `standard` puede tener UNA de las siguientes estaciones asignadas:
- `work_table_id` → Mesa de trabajo manual
- `semi_auto_work_table_id` → Mesa semi-automática
- `machine_id` → Máquina

### Validación

La validación de mutua exclusividad se hace a **nivel de aplicación**:
- Custom Rule: `OnlyOneWorkstation`
- Livewire component: auto-deselección en `updated()`
- Validación adicional: al menos UNA estación debe estar seleccionada

### Uso en Código

#### Obtener la estación activa
```php
$standard = Standard::find(1);
$workstation = $standard->getWorkstation(); // Devuelve Table|Semi_Automatic|Machine
$assemblyMode = $standard->assembly_mode; // 'manual', 'semi_automatic', 'machine'
$workstationName = $standard->workstation_name; // Para display
```

#### Calcular horas requeridas
```php
$hours = $standard->calculateRequiredHours(500); // 500 unidades
```

#### Filtrar por tipo de estación
```php
$manualStandards = Standard::byAssemblyMode('manual')->get();
$machineStandards = Standard::byAssemblyMode('machine')->get();
```

### Migración de Datos

Si existe data con múltiples estaciones asignadas:
```sql
-- Identificar problemas
SELECT id, part_id,
       work_table_id,
       semi_auto_work_table_id,
       machine_id
FROM standards
WHERE (work_table_id IS NOT NULL AND semi_auto_work_table_id IS NOT NULL)
   OR (work_table_id IS NOT NULL AND machine_id IS NOT NULL)
   OR (semi_auto_work_table_id IS NOT NULL AND machine_id IS NOT NULL);

-- Limpiar: mantener solo la primera estación no-null
UPDATE standards
SET semi_auto_work_table_id = NULL,
    machine_id = NULL
WHERE work_table_id IS NOT NULL
  AND (semi_auto_work_table_id IS NOT NULL OR machine_id IS NOT NULL);
```

### Testing

Siempre validar:
- Solo UNA estación asignada
- Auto-deselección funciona correctamente
- `calculateRequiredHours()` usa `units_per_hour` correcto
- Filtros por `assembly_mode` funcionan
```

---

## Migración de Datos

### Escenario 1: Base de Datos Vacía

**Acción:** Ninguna. Solo ejecutar migración.

```bash
php artisan migrate
```

### Escenario 2: Standards Existentes Sin Datos Inválidos

**Verificar:**
```sql
SELECT
    id,
    part_id,
    work_table_id,
    semi_auto_work_table_id,
    machine_id,
    (CASE WHEN work_table_id IS NOT NULL THEN 1 ELSE 0 END +
     CASE WHEN semi_auto_work_table_id IS NOT NULL THEN 1 ELSE 0 END +
     CASE WHEN machine_id IS NOT NULL THEN 1 ELSE 0 END) as workstation_count
FROM standards
WHERE (CASE WHEN work_table_id IS NOT NULL THEN 1 ELSE 0 END +
       CASE WHEN semi_auto_work_table_id IS NOT NULL THEN 1 ELSE 0 END +
       CASE WHEN machine_id IS NOT NULL THEN 1 ELSE 0 END) > 1;
```

**Si no hay resultados:** Datos válidos, solo ejecutar migración.

**Acción:**
```bash
php artisan migrate
```

**Post-migración:** Actualizar `units_per_hour` manualmente o mediante seeder:

```php
// Script one-time para actualizar units_per_hour
use App\Models\Standard;

Standard::chunk(100, function ($standards) {
    foreach ($standards as $standard) {
        // Asignar valor default basado en tipo de estación
        $unitsPerHour = 100; // default

        if ($standard->machine_id) {
            $unitsPerHour = 200; // Máquinas más rápidas
        } elseif ($standard->semi_auto_work_table_id) {
            $unitsPerHour = 150; // Semi-automáticas intermedias
        }

        $standard->update(['units_per_hour' => $unitsPerHour]);
    }
});
```

### Escenario 3: Standards con Datos Inválidos (Múltiples Estaciones)

**Identificar:**
```sql
SELECT
    id,
    part_id,
    work_table_id,
    semi_auto_work_table_id,
    machine_id
FROM standards
WHERE (work_table_id IS NOT NULL AND semi_auto_work_table_id IS NOT NULL)
   OR (work_table_id IS NOT NULL AND machine_id IS NOT NULL)
   OR (semi_auto_work_table_id IS NOT NULL AND machine_id IS NOT NULL);
```

**Estrategia de Limpieza:**

**Opción A: Priorizar por orden (work_table > semi_auto > machine)**
```sql
-- Limpiar: mantener solo work_table si existe
UPDATE standards
SET semi_auto_work_table_id = NULL,
    machine_id = NULL
WHERE work_table_id IS NOT NULL
  AND (semi_auto_work_table_id IS NOT NULL OR machine_id IS NOT NULL);

-- Limpiar: mantener solo semi_auto si work_table es null pero semi_auto existe
UPDATE standards
SET machine_id = NULL
WHERE work_table_id IS NULL
  AND semi_auto_work_table_id IS NOT NULL
  AND machine_id IS NOT NULL;
```

**Opción B: Crear registros duplicados (uno por estación)**
```php
use App\Models\Standard;

$invalidStandards = Standard::whereRaw(
    '(work_table_id IS NOT NULL AND semi_auto_work_table_id IS NOT NULL)
     OR (work_table_id IS NOT NULL AND machine_id IS NOT NULL)
     OR (semi_auto_work_table_id IS NOT NULL AND machine_id IS NOT NULL)'
)->get();

foreach ($invalidStandards as $standard) {
    $workstations = collect([
        ['type' => 'work_table_id', 'id' => $standard->work_table_id],
        ['type' => 'semi_auto_work_table_id', 'id' => $standard->semi_auto_work_table_id],
        ['type' => 'machine_id', 'id' => $standard->machine_id],
    ])->filter(fn($w) => $w['id'] !== null);

    // Mantener el primero, crear nuevos para los demás
    $first = true;
    foreach ($workstations as $workstation) {
        if ($first) {
            // Limpiar el original, dejando solo la primera estación
            $standard->update([
                'work_table_id' => $workstation['type'] === 'work_table_id' ? $workstation['id'] : null,
                'semi_auto_work_table_id' => $workstation['type'] === 'semi_auto_work_table_id' ? $workstation['id'] : null,
                'machine_id' => $workstation['type'] === 'machine_id' ? $workstation['id'] : null,
            ]);
            $first = false;
        } else {
            // Crear nuevo standard para las estaciones adicionales
            Standard::create([
                'part_id' => $standard->part_id,
                'work_table_id' => $workstation['type'] === 'work_table_id' ? $workstation['id'] : null,
                'semi_auto_work_table_id' => $workstation['type'] === 'semi_auto_work_table_id' ? $workstation['id'] : null,
                'machine_id' => $workstation['type'] === 'machine_id' ? $workstation['id'] : null,
                'units_per_hour' => 100, // Default
                'persons_1' => $standard->persons_1,
                'persons_2' => $standard->persons_2,
                'persons_3' => $standard->persons_3,
                'effective_date' => $standard->effective_date,
                'active' => $standard->active,
                'description' => $standard->description . ' (migrado)',
            ]);
        }
    }
}
```

**Recomendación:** Usar **Opción A** (priorizar) si los datos duplicados son errores. Usar **Opción B** (duplicar) si representan estándares legítimos diferentes.

### Escenario 4: Standards Sin Estación Asignada

**Identificar:**
```sql
SELECT id, part_id, description
FROM standards
WHERE work_table_id IS NULL
  AND semi_auto_work_table_id IS NULL
  AND machine_id IS NULL;
```

**Acción:** Marcar como inactivos o eliminar.

```sql
-- Opción A: Marcar como inactivos
UPDATE standards
SET active = 0
WHERE work_table_id IS NULL
  AND semi_auto_work_table_id IS NULL
  AND machine_id IS NULL;

-- Opción B: Soft delete
UPDATE standards
SET deleted_at = NOW()
WHERE work_table_id IS NULL
  AND semi_auto_work_table_id IS NULL
  AND machine_id IS NULL;
```

### Script de Migración Completo

**Archivo:** `database/migrations/helpers/migrate_standards_data.php`

```php
<?php

use App\Models\Standard;
use Illuminate\Support\Facades\DB;

/**
 * Script de migración de datos para standards
 *
 * Ejecutar DESPUÉS de correr la migración add_units_per_hour_to_standards_table
 */

DB::transaction(function () {
    // Paso 1: Identificar y reportar problemas
    $multipleWorkstations = Standard::whereRaw(
        '(work_table_id IS NOT NULL AND semi_auto_work_table_id IS NOT NULL)
         OR (work_table_id IS NOT NULL AND machine_id IS NOT NULL)
         OR (semi_auto_work_table_id IS NOT NULL AND machine_id IS NOT NULL)'
    )->count();

    $noWorkstation = Standard::whereNull('work_table_id')
        ->whereNull('semi_auto_work_table_id')
        ->whereNull('machine_id')
        ->count();

    echo "Reporte de Standards:\n";
    echo "- Con múltiples estaciones: {$multipleWorkstations}\n";
    echo "- Sin estación asignada: {$noWorkstation}\n\n";

    // Paso 2: Limpiar múltiples estaciones (priorizar work_table)
    if ($multipleWorkstations > 0) {
        echo "Limpiando múltiples estaciones...\n";

        DB::statement("
            UPDATE standards
            SET semi_auto_work_table_id = NULL,
                machine_id = NULL
            WHERE work_table_id IS NOT NULL
              AND (semi_auto_work_table_id IS NOT NULL OR machine_id IS NOT NULL)
        ");

        DB::statement("
            UPDATE standards
            SET machine_id = NULL
            WHERE work_table_id IS NULL
              AND semi_auto_work_table_id IS NOT NULL
              AND machine_id IS NOT NULL
        ");

        echo "Limpieza completada.\n\n";
    }

    // Paso 3: Marcar como inactivos los que no tienen estación
    if ($noWorkstation > 0) {
        echo "Marcando como inactivos los standards sin estación...\n";

        Standard::whereNull('work_table_id')
            ->whereNull('semi_auto_work_table_id')
            ->whereNull('machine_id')
            ->update(['active' => false]);

        echo "Standards sin estación marcados como inactivos.\n\n";
    }

    // Paso 4: Asignar units_per_hour basado en tipo de estación
    echo "Asignando units_per_hour...\n";

    // Máquinas: 200-500 uph
    Standard::whereNotNull('machine_id')
        ->where('units_per_hour', 1) // Solo los que tienen default
        ->update(['units_per_hour' => 300]);

    // Semi-automáticas: 100-300 uph
    Standard::whereNotNull('semi_auto_work_table_id')
        ->where('units_per_hour', 1)
        ->update(['units_per_hour' => 150]);

    // Manuales: 50-200 uph
    Standard::whereNotNull('work_table_id')
        ->where('units_per_hour', 1)
        ->update(['units_per_hour' => 100]);

    echo "units_per_hour asignado correctamente.\n\n";

    // Paso 5: Verificación final
    $validStandards = Standard::where('active', true)
        ->where(function ($query) {
            $query->whereNotNull('work_table_id')
                  ->orWhereNotNull('semi_auto_work_table_id')
                  ->orWhereNotNull('machine_id');
        })
        ->count();

    echo "Verificación final:\n";
    echo "- Standards válidos y activos: {$validStandards}\n";
    echo "Migración completada exitosamente.\n";
});
```

**Ejecutar:**
```bash
php database/migrations/helpers/migrate_standards_data.php
```

---

## Testing

### Tests de Migración

**Archivo:** `tests/Feature/Migrations/AddUnitsPerHourToStandardsTest.php`

```php
<?php

namespace Tests\Feature\Migrations;

use App\Models\Standard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AddUnitsPerHourToStandardsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_migration_adds_units_per_hour_column()
    {
        $this->assertTrue(Schema::hasColumn('standards', 'units_per_hour'));
    }

    /** @test */
    public function test_units_per_hour_has_default_value()
    {
        $standard = Standard::factory()->create();

        // Si no se especifica, debe tener valor default
        $this->assertNotNull($standard->units_per_hour);
        $this->assertGreaterThanOrEqual(1, $standard->units_per_hour);
    }

    /** @test */
    public function test_migration_adds_performance_index()
    {
        $indexes = Schema::getIndexes('standards');
        $indexNames = array_column($indexes, 'name');

        $this->assertContains('standards_part_performance_index', $indexNames);
    }
}
```

### Tests de Validación

Ver **Tarea 5.2** para tests completos de validación.

### Tests de Integración

Ver **Tarea 5.3** para tests de integración con CapacityCalculatorService.

---

## Diagrama ER Actualizado

### Relaciones Actuales

```
┌─────────────────┐
│     areas       │
└────────┬────────┘
         │ 1
         │
         │ N
    ┌────┴──────┬─────────────┬──────────────┐
    │           │             │              │
┌───▼────┐  ┌───▼─────┐  ┌───▼────────┐     │
│ tables │  │ semi_   │  │  machines  │     │
│        │  │ auto    │  │            │     │
│ number │  │ number  │  │ name       │     │
│ employees  │ employees  │ employees  │     │
│ active │  │ active  │  │ brand      │     │
│ comments│  │ comments│  │ model      │     │
│        │  │         │  │ setup_time │     │
│        │  │         │  │ maint_time │     │
└────┬───┘  └────┬────┘  └─────┬──────┘     │
     │ 1        │ 1           │ 1           │
     │          │             │             │
     │ N        │ N           │ N           │
     │          │             │             │
     └──────────┴─────────────┴─────────────┤
                                            │
                                      ┌─────▼──────────────────┐
                                      │      standards         │
                                      │                        │
                                      │ part_id (FK parts)     │
                                      │ work_table_id (FK)     │◄──┐
                                      │ semi_auto_id (FK)      │◄──┼─ Solo UNA
                                      │ machine_id (FK)        │◄──┘  puede ser
                                      │                        │      NOT NULL
                                      │ units_per_hour ✨NEW  │
                                      │ persons_1              │
                                      │ persons_2              │
                                      │ persons_3              │
                                      │ effective_date         │
                                      │ active                 │
                                      │                        │
                                      │ Computed:              │
                                      │ - assembly_mode()      │
                                      │ - workstation()        │
                                      └────────────────────────┘
                                               │ N
                                               │
                                               │ 1
                                      ┌────────▼───────┐
                                      │     parts      │
                                      │                │
                                      │ number         │
                                      │ item_number    │
                                      │ description    │
                                      └────────────────┘
```

### Cardinalidades

- **Area → Tables**: 1:N (un área tiene muchas mesas)
- **Area → Semi_Automatics**: 1:N
- **Area → Machines**: 1:N
- **Standard → Table**: N:1 (muchos standards pueden usar la misma mesa)
- **Standard → Semi_Automatic**: N:1
- **Standard → Machine**: N:1
- **Standard → Part**: N:1 (muchos standards para la misma parte, con diferentes estaciones/fechas)

### Constraints Importantes

**A nivel de DB:**
- `work_table_id`, `semi_auto_work_table_id`, `machine_id`: NULLABLE
- `part_id`: NOT NULL, cascade on delete
- `units_per_hour`: NOT NULL, default 1

**A nivel de Aplicación (validación):**
- Solo UNA de las tres estaciones puede tener valor (mutua exclusividad)
- Al menos UNA estación debe estar asignada
- `units_per_hour` debe ser > 0

---

## Riesgos y Mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| Datos existentes con múltiples estaciones | MEDIA | ALTO | Script de migración para limpiar datos |
| Standards sin estación asignada | BAJA | MEDIO | Validación en creación + marcar inactivos los existentes |
| División por cero en calculateRequiredHours | MEDIA | ALTO | Validación `min:1` en units_per_hour + throw exception |
| Usuario selecciona múltiples estaciones | ALTA | MEDIO | Custom validation rule + auto-deselección en Livewire |
| Performance en búsquedas complejas | BAJA | BAJO | Índice compuesto ya creado |
| Incompatibilidad con CapacityCalculatorService | BAJA | ALTO | Tests de integración extensivos |
| Confusión de usuarios con nueva UI | MEDIA | BAJO | Tooltips + mensajes claros + documentación |

---

## Referencias

### Archivos del Proyecto Analizados

1. **Migraciones:**
   - `database/migrations/2025_07_20_170531_create_areas_table.php`
   - `database/migrations/2025_07_20_171916_create_semi__automatics_table.php`
   - `database/migrations/2025_07_20_172007_create_machines_table.php`
   - `database/migrations/2025_07_20_172105_create_tables_table.php`
   - `database/migrations/2025_12_10_051116_create_parts_table.php`
   - `database/migrations/2025_12_14_190425_create_standards_table.php`

2. **Modelos:**
   - `app/Models/Table.php`
   - `app/Models/Semi_Automatic.php`
   - `app/Models/Machine.php`
   - `app/Models/Standard.php`

3. **Componentes Livewire:**
   - `app/Livewire/Admin/Standards/StandardCreate.php`
   - `app/Livewire/Admin/Standards/StandardEdit.php`
   - `app/Livewire/Admin/Standards/StandardList.php`
   - `app/Livewire/Admin/Standards/StandardShow.php`

4. **Specs Relacionados:**
   - `Diagramas_flujo/Estructura/specs/01_production_capacity_implementation_plan.md`

### Documentación Laravel

- **Eloquent Relationships**: https://laravel.com/docs/12.x/eloquent-relationships
- **Polymorphic Relationships**: https://laravel.com/docs/12.x/eloquent-relationships#polymorphic-relationships
- **Custom Validation Rules**: https://laravel.com/docs/12.x/validation#custom-validation-rules
- **Database Migrations**: https://laravel.com/docs/12.x/migrations

### Patrones de Diseño

- **Foreign Keys Directas vs Polimórficos**: Discusión sobre trade-offs
- **Validation at Application Layer**: Cuando DB constraints no son suficientes
- **Computed Attributes**: Usar accessors en lugar de columnas redundantes

---

## Conclusiones y Recomendaciones

### Decisión Final: Mantener Foreign Keys Directas

Después del análisis exhaustivo, se recomienda **NO refactorizar a relaciones polimórficas** por las siguientes razones:

1. **Pragmatismo**: El código actual funciona y es comprensible
2. **Costo-Beneficio**: 1 día de desarrollo vs 3-4 días de refactorización
3. **Riesgo**: Bajo vs Alto
4. **Performance**: Equivalente o mejor con índices actuales
5. **Compatibilidad**: Cero cambios breaking en código existente

### Mejoras Propuestas

1. **Agregar campo `units_per_hour`** (CRÍTICO para Spec 01)
2. **NO agregar campo `assembly_mode`** (calculado dinámicamente)
3. **Validación mejorada** con `OnlyOneWorkstation` rule
4. **Métodos helper** en modelo Standard para simplificar lógica
5. **UX mejorada** con auto-deselección y tooltips
6. **Relaciones inversas** en modelos de estaciones

### Próximos Pasos

1. Revisar y aprobar este Spec 02
2. Ejecutar Plan de Implementación (4 días estimados)
3. Validar con tests (coverage > 90%)
4. Actualizar Spec 01 para usar `Standard::getAssemblyMode()` en lugar de campo directo
5. Documentar patrones para futuros desarrolladores

---

## Historial de Cambios

| Versión | Fecha | Autor | Cambios |
|---------|-------|-------|---------|
| 1.0 | 2025-12-19 | Architect Agent | Creación inicial del spec - Análisis completo de relaciones standards-workstations |

---

**Fin del Spec 02**
