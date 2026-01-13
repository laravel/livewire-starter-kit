# Spec 11: Análisis Técnico - Inconsistencia del Campo "Unidades por Hora" en CRUD de Standards

**Fecha de Creación:** 2026-01-12
**Autor:** Architect Agent
**Fase del Proyecto:** FASE 2 - Corrección y Mantenimiento
**Estado:** Análisis Completo
**Versión:** 1.0
**Prioridad:** Alta

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Problema Identificado](#problema-identificado)
3. [Análisis de la Estructura del CRUD](#análisis-de-la-estructura-del-crud)
4. [Análisis de Dependencias](#análisis-de-dependencias)
5. [Evaluación de Impacto](#evaluación-de-impacto)
6. [Propuesta de Solución](#propuesta-de-solución)
7. [Plan de Implementación](#plan-de-implementación)
8. [Validaciones y Pruebas](#validaciones-y-pruebas)
9. [Referencias](#referencias)

---

## Resumen Ejecutivo

### Problema
Existe una **inconsistencia crítica** en las vistas del CRUD de Standards:
- La vista `standard-create.blade.php` **contiene** un input "Unidades por Hora"
- La vista `standard-edit.blade.php` **NO contiene** ese input
- Esta inconsistencia genera confusión en la UX y puede llevar a errores operacionales

### Diagnóstico
**El campo "Unidades por Hora" NO debe eliminarse**. Es un campo **crítico** para el sistema de cálculo de capacidad de producción (Capacity Calculator). La inconsistencia real es que **FALTA en la vista de edición**, no que sobre en la vista de creación.

### Recomendación
**AGREGAR** el campo "Unidades por Hora" a la vista de edición para mantener consistencia y permitir actualizaciones del valor cuando cambien los estándares de producción.

---

## Problema Identificado

### Descripción del Problema

#### Vista de Creación (`standard-create.blade.php`)
```blade
<!-- Líneas 48-61 -->
<div>
    <label for="units_per_hour" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Unidades por Hora <span class="text-red-500">*</span>
    </label>
    <input wire:model="units_per_hour" id="units_per_hour" type="number" min="1"
        placeholder="Ej: 50"
        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg..."
        required />
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        Cantidad de unidades que se producen por hora en esta estación
    </p>
    @error('units_per_hour')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
```

#### Vista de Edición (`standard-edit.blade.php`)
```blade
<!-- Este input NO existe en la vista de edición -->
<!-- La vista pasa directamente de "Part" a "Work Stations" -->
```

### Manifestación del Problema

| Aspecto | Vista CREATE | Vista EDIT | Consistencia |
|---------|--------------|------------|--------------|
| Campo "Unidades por Hora" | ✅ Presente (obligatorio) | ❌ Ausente | ❌ INCONSISTENTE |
| Validación backend | ✅ `required|integer|min:1` | ❌ No valida | ❌ INCONSISTENTE |
| Propiedad en componente | ✅ `public string $units_per_hour = ''` | ❌ No existe | ❌ INCONSISTENTE |
| Guardado en BD | ✅ Se guarda | ⚠️ No se actualiza | ❌ INCONSISTENTE |

---

## Análisis de la Estructura del CRUD

### 1. Modelo: `Standard.php`

#### Análisis del Modelo
```php
// Archivo: app/Models/Standard.php

class Standard extends Model
{
    use HasFactory, SoftDeletes;

    // ✅ Campo DECLARADO en $fillable
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
        'units_per_hour'  // ← PRESENTE en fillable
    ];

    // ✅ Campo CASTEADO correctamente
    protected $casts = [
        // ...
        'units_per_hour' => 'integer'
    ];

    // ✅ Método CRÍTICO que USA el campo
    public function calculateRequiredHours(int $quantity): float
    {
        if ($this->units_per_hour === 0) {
            throw new \DivisionByZeroError(
                "El estándar para la parte '{$this->part->number}' tiene units_per_hour = 0"
            );
        }

        return round($quantity / $this->units_per_hour, 2);
    }
}
```

**Conclusión del Modelo:**
- ✅ El campo está correctamente definido
- ✅ Tiene casts apropiados
- ⚠️ Es usado por método crítico `calculateRequiredHours()`
- ⚠️ **NO puede ser eliminado**

---

### 2. Migración de Base de Datos

#### Migración Original (2025-12-14)
```php
// Archivo: database/migrations/2025_12_14_190425_create_standards_table.php

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

    // ❌ NO incluye units_per_hour

    $table->softDeletes();
    $table->timestamps();
});
```

#### Migración de Adición (2025-12-20)
```php
// Archivo: database/migrations/2025_12_20_081207_add_units_per_hour_to_standards_table.php

Schema::table('standards', function (Blueprint $table) {
    // ✅ Campo agregado posteriormente
    $table->integer('units_per_hour')
          ->after('part_id')
          ->default(1)
          ->comment('Unidades producidas por hora en esta estación');

    // ✅ Índice compuesto para optimización
    $table->index(
        ['part_id', 'active', 'units_per_hour'],
        'standards_part_performance_index'
    );
});
```

**Conclusión de Migraciones:**
- ✅ El campo existe en la base de datos (agregado el 2025-12-20)
- ✅ Tiene índice compuesto para optimización de consultas
- ✅ Tiene valor por defecto (1) para evitar divisiones por cero
- ⚠️ La migración se ejecutó **DESPUÉS** de la creación del CRUD

---

### 3. Componente Livewire: `StandardCreate.php`

#### Análisis del Componente de Creación
```php
// Archivo: app/Livewire/Admin/Standards/StandardCreate.php

class StandardCreate extends Component
{
    // ✅ Propiedad DECLARADA
    public ?int $part_id = null;
    public string $units_per_hour = '';  // ← PRESENTE
    public ?int $work_table_id = null;
    // ... otras propiedades

    protected function rules(): array
    {
        return [
            'part_id' => 'required|exists:parts,id',
            // ✅ Validación ESTRICTA
            'units_per_hour' => 'required|integer|min:1',  // ← OBLIGATORIO
            'work_table_id' => 'nullable|exists:tables,id',
            // ... otras validaciones
        ];
    }

    protected function messages(): array
    {
        return [
            // ✅ Mensajes de error personalizados
            'units_per_hour.required' => 'Las unidades por hora son obligatorias.',
            'units_per_hour.integer' => 'Las unidades por hora deben ser un número entero.',
            'units_per_hour.min' => 'Las unidades por hora deben ser al menos 1.',
        ];
    }

    public function saveStandard(): void
    {
        $this->validate();

        // ✅ Se GUARDA en la base de datos
        Standard::create([
            'part_id' => $this->part_id,
            'units_per_hour' => $this->units_per_hour,  // ← SE GUARDA
            'work_table_id' => $this->work_table_id ?: null,
            // ... otros campos
        ]);
    }
}
```

**Conclusión del Componente CREATE:**
- ✅ El campo está correctamente implementado
- ✅ Tiene validación obligatoria (required)
- ✅ Se guarda correctamente en la base de datos

---

### 4. Componente Livewire: `StandardEdit.php`

#### Análisis del Componente de Edición
```php
// Archivo: app/Livewire/Admin/Standards/StandardEdit.php

class StandardEdit extends Component
{
    public Standard $standard;
    // ❌ NO tiene propiedad $units_per_hour
    public ?int $part_id = null;
    public ?int $work_table_id = null;
    // ... otras propiedades (FALTA units_per_hour)

    public function mount(Standard $standard): void
    {
        $this->standard = $standard;
        $this->part_id = $standard->part_id;
        // ❌ NO carga units_per_hour
        $this->work_table_id = $standard->work_table_id;
        // ... carga otros campos
    }

    protected function rules(): array
    {
        return [
            'part_id' => 'required|exists:parts,id',
            // ❌ NO valida units_per_hour
            'work_table_id' => 'nullable|exists:tables,id',
            // ... otras validaciones
        ];
    }

    public function updateStandard(): void
    {
        $this->validate();

        // ❌ NO actualiza units_per_hour
        $this->standard->update([
            'part_id' => $this->part_id,
            // FALTA: 'units_per_hour' => $this->units_per_hour,
            'work_table_id' => $this->work_table_id ?: null,
            // ... otros campos
        ]);
    }
}
```

**Conclusión del Componente EDIT:**
- ❌ Falta la propiedad `units_per_hour`
- ❌ No carga el valor en `mount()`
- ❌ No valida el campo en `rules()`
- ❌ No actualiza el campo en `updateStandard()`
- ⚠️ **INCONSISTENCIA CRÍTICA**

---

### 5. Vistas Blade

#### Vista de Listado (`standard-list.blade.php`)
```blade
<!-- NO muestra units_per_hour en la tabla -->
<table>
    <thead>
        <tr>
            <th>Parte</th>
            <th>Mesa de Trabajo</th>
            <th>Mesa Semi-Auto</th>
            <th>Máquina</th>
            <th>Personas</th>  <!-- Muestra persons_1, persons_2, persons_3 -->
            <th>Fecha Efectiva</th>
            <th>Estado</th>
            <!-- ❌ NO muestra units_per_hour -->
        </tr>
    </thead>
</table>
```

#### Vista de Detalle (`standard-show.blade.php`)
```blade
<!-- NO muestra units_per_hour en el detalle -->
<dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <dt>Parte</dt>
        <dd>{{ $standard->part->number }}</dd>
    </div>
    <div>
        <dt>Personas 1</dt>
        <dd>{{ $standard->persons_1 ?? 'N/A' }}</dd>
    </div>
    <!-- ❌ NO muestra units_per_hour -->
</dl>
```

**Conclusión de Vistas:**
- ❌ El campo no se muestra en ninguna vista de consulta
- ⚠️ El usuario no puede ver el valor actual de `units_per_hour`
- ⚠️ Dificulta la verificación y auditoría de datos

---

### 6. Rutas

```php
// Archivo: routes/admin.php

Route::get('/standards', \App\Livewire\Admin\Standards\StandardList::class)
    ->name('standards.index');

Route::get('/standards/create', \App\Livewire\Admin\Standards\StandardCreate::class)
    ->name('standards.create');

Route::get('/standards/{standard}', \App\Livewire\Admin\Standards\StandardShow::class)
    ->name('standards.show');

Route::get('/standards/{standard}/edit', \App\Livewire\Admin\Standards\StandardEdit::class)
    ->name('standards.edit');
```

**Conclusión de Rutas:**
- ✅ Rutas correctamente definidas
- ✅ Usa Livewire components para todas las vistas
- ✅ Sigue el patrón RESTful

---

## Análisis de Dependencias

### Uso del Campo `units_per_hour` en el Sistema

#### 1. Servicio de Cálculo de Capacidad (`CapacityCalculatorService.php`)

```php
// Archivo: app/Services/CapacityCalculatorService.php
// Líneas: 100-125

/**
 * Calculate required hours for a work order.
 *
 * Formula: quantity / units_per_hour (from standard)
 */
public function calculateRequiredHours(int $part_id, int $quantity, string $assembly_mode = '1_person'): float
{
    // Obtener el estándar activo
    $standard = Standard::where('part_id', $part_id)
        ->where('active', true)
        ->first();

    if (!$standard) {
        throw new \Exception("No active standard found for part {$part->number}");
    }

    // ⚠️ CRÍTICO: USA units_per_hour del estándar
    $units_per_hour = $standard->units_per_hour ?? 0;

    if ($units_per_hour === 0) {
        throw new \Exception("Standard for part {$part->number} has units_per_hour = 0");
    }

    // ⚠️ CRÍTICO: Cálculo de horas requeridas
    return round($quantity / $units_per_hour, 2);
}
```

**Impacto:**
- 🔴 **CRÍTICO**: Este servicio es el core del módulo de planificación de capacidad
- 🔴 Si `units_per_hour` es 0 o null, lanza excepción
- 🔴 Usado por el Capacity Wizard para calcular factibilidad de producción

---

#### 2. Capacity Wizard (`CapacityWizard.php`)

```php
// Archivo: app/Livewire/Admin/CapacityWizard.php
// Líneas: 283-338

public function addWorkOrderItem()
{
    // ... validaciones previas

    $part = Part::find($this->currentPartId);

    // ⚠️ CRÍTICO: Obtiene estándar con units_per_hour
    $standard = $part->standards()->where('active', true)->first();

    if (!$standard || !$standard->units_per_hour || $standard->units_per_hour == 0) {
        // 🔴 Error si no hay units_per_hour válido
        $this->errorMessage = "No hay estándar activo con unidades por hora para la parte {$part->number}.";
        return;
    }

    // ⚠️ CRÍTICO: Usa units_per_hour para calcular horas
    $requiredHours = round($this->currentQuantity / $standard->units_per_hour, 2);

    $this->workOrderItems[] = [
        'part_id' => $this->currentPartId,
        'part_number' => $part->number,
        'quantity' => $this->currentQuantity,
        'required_hours' => $requiredHours,
        'units_per_hour' => $standard->units_per_hour,  // ⚠️ Se almacena para referencia
    ];

    // ... resto del método
}
```

**Impacto:**
- 🔴 **CRÍTICO**: El wizard no puede agregar partes sin `units_per_hour` válido
- 🔴 Validación explícita: rechaza standards con `units_per_hour == 0`
- 🔴 Usado en la interfaz principal de planificación de capacidad

---

#### 3. Renderizado del Capacity Wizard

```php
// Archivo: app/Livewire/Admin/CapacityWizard.php
// Líneas: 439-451

public function render()
{
    // ⚠️ CRÍTICO: Solo muestra partes con estándar válido
    $partsWithStandard = Part::active()
        ->whereHas('standards', fn($q) =>
            $q->where('active', true)->where('units_per_hour', '>', 0)  // ⚠️ Filtra por units_per_hour > 0
        )
        ->orderBy('number')
        ->get();

    return view('livewire.admin.capacity-wizard', [
        'shifts' => Shift::active()->get(),
        'parts' => $partsWithStandard,  // Solo partes con estándar válido
    ]);
}
```

**Impacto:**
- 🔴 **CRÍTICO**: Filtra partes sin `units_per_hour` válido
- 🔴 Las partes sin estándar válido NO aparecen en el wizard
- 🔴 Si se edita un Standard y se borra accidentalmente `units_per_hour`, la parte desaparece del wizard

---

#### 4. Seeder de Standards

```php
// Archivo: database/seeders/StandardSeeder.php
// Líneas: 46-58

Standard::create([
    'part_id' => $part->id,
    'work_table_id' => $tableId,
    'machine_id' => $machineId,
    'semi_auto_work_table_id' => $semiAutoId,
    'persons_1' => rand(800, 1500),
    'persons_2' => rand(1200, 2000),
    'persons_3' => rand(1800, 2800),
    'units_per_hour' => rand(100, 350), // ⚠️ Crítico para cálculo de capacidad
    'effective_date' => now()->subDays(rand(1, 30)),
    'active' => true,
    'description' => "Estándar de producción para {$part->number}",
]);
```

**Impacto:**
- ⚠️ El seeder siempre genera valores válidos (100-350)
- ⚠️ Los datos de prueba tienen `units_per_hour` válido
- ⚠️ Puede enmascarar el problema en desarrollo

---

#### 5. Factory de Standards

```php
// Archivo: database/factories/StandardFactory.php
// Líneas: 21-35

public function definition(): array
{
    return [
        'part_id' => Part::factory(),
        // ... otros campos
        'units_per_hour' => $this->faker->numberBetween(50, 500), // ⚠️ Para cálculo de capacidad
        'effective_date' => $this->faker->dateTimeBetween('-1 year', '+1 month'),
        'active' => $this->faker->boolean(80),
    ];
}

public function activeWithCapacity(): static
{
    return $this->state(fn (array $attributes) => [
        'active' => true,
        'units_per_hour' => $this->faker->numberBetween(100, 400), // ⚠️ Garantiza valor válido
    ]);
}
```

**Impacto:**
- ⚠️ El factory genera valores válidos por defecto
- ⚠️ Método `activeWithCapacity()` para tests de capacidad
- ⚠️ Facilita testing del módulo de capacidad

---

### Mapa de Dependencias

```
┌─────────────────────────────────────────────────────────────┐
│                     standards.units_per_hour                │
│                        (Campo Crítico)                       │
└────────────────────┬────────────────────────────────────────┘
                     │
         ┌───────────┴───────────┐
         │                       │
         ▼                       ▼
┌────────────────────┐  ┌────────────────────────┐
│  Standard Model    │  │ CapacityCalculator     │
│                    │  │      Service           │
│ - calculateRequired│  │                        │
│   Hours()          │  │ - calculateRequired    │
│   (CRITICAL)       │  │   Hours() (CRITICAL)   │
└────────┬───────────┘  └───────┬────────────────┘
         │                       │
         └───────────┬───────────┘
                     │
                     ▼
         ┌───────────────────────┐
         │   CapacityWizard      │
         │   (Livewire)          │
         │                       │
         │ - addWorkOrderItem()  │
         │ - render() filters    │
         │   (CRITICAL)          │
         └───────────────────────┘
                     │
                     ▼
         ┌───────────────────────┐
         │   Capacity Wizard UI  │
         │   (Production Plan)   │
         │   (USER FACING)       │
         └───────────────────────┘
```

---

## Evaluación de Impacto

### Impacto de NO Tener el Campo en Edit

#### 1. Escenarios Problemáticos

##### Escenario A: Cambio de Estándar de Producción
```
Situación:
- Una parte tiene units_per_hour = 100
- La ingeniería actualiza el proceso de producción
- Nuevo estándar: units_per_hour = 150 (50% más eficiente)

Problema:
❌ El usuario NO puede actualizar este valor en la interfaz
❌ Debe ir a la base de datos directamente
❌ Riesgo de error humano
❌ No hay auditoría del cambio
```

##### Escenario B: Corrección de Error
```
Situación:
- Se creó un estándar con units_per_hour = 10 (error de tipeo)
- El valor correcto es 100

Problema:
❌ No se puede corregir desde la UI
❌ El Capacity Wizard mostrará datos incorrectos
❌ Las planificaciones de capacidad estarán mal calculadas
```

##### Escenario C: Optimización Continua
```
Situación:
- El área de manufactura optimiza procesos
- units_per_hour aumenta gradualmente (100 → 110 → 120)

Problema:
❌ No hay forma de actualizar los valores
❌ Las planificaciones usan datos obsoletos
❌ Subutilización de capacidad real
```

---

#### 2. Impacto en Funcionalidades

| Funcionalidad | Impacto Sin Campo en Edit | Severidad |
|---------------|---------------------------|-----------|
| **Capacity Calculator** | Usa valores obsoletos o incorrectos | 🔴 CRÍTICA |
| **Capacity Wizard** | Planificaciones inexactas | 🔴 CRÍTICA |
| **Work Order Planning** | Asignación incorrecta de horas | 🔴 CRÍTICA |
| **Production Reports** | Métricas incorrectas | 🟠 ALTA |
| **Auditoría de Cambios** | No hay trazabilidad de actualizaciones | 🟠 ALTA |
| **UX/Consistencia** | Confusión del usuario | 🟡 MEDIA |

---

#### 3. Impacto en Datos Existentes

##### Consulta de Verificación (SQL)
```sql
-- Verificar si hay datos con units_per_hour
SELECT
    COUNT(*) as total_standards,
    COUNT(units_per_hour) as with_units_per_hour,
    COUNT(CASE WHEN units_per_hour = 0 THEN 1 END) as with_zero,
    COUNT(CASE WHEN units_per_hour IS NULL THEN 1 END) as with_null,
    AVG(units_per_hour) as avg_units_per_hour,
    MIN(units_per_hour) as min_units_per_hour,
    MAX(units_per_hour) as max_units_per_hour
FROM standards
WHERE deleted_at IS NULL;

-- Partes sin estándar válido para Capacity Wizard
SELECT
    p.id,
    p.number,
    p.description,
    s.units_per_hour,
    s.active
FROM parts p
LEFT JOIN standards s ON s.part_id = p.id AND s.active = 1 AND s.deleted_at IS NULL
WHERE p.active = 1
AND (s.units_per_hour IS NULL OR s.units_per_hour = 0);
```

##### Impacto Esperado
- ⚠️ Si hay Standards existentes con `units_per_hour` válido, NO se perderán datos
- ⚠️ El campo tiene default = 1 en la migración
- ⚠️ Los seeders generan valores válidos (100-350)
- ✅ **Baja probabilidad de datos NULL o 0 en producción** (si se ejecutaron seeders)

---

#### 4. Impacto en Testing

```php
// Tests que dependen de units_per_hour

/** @test */
public function capacity_calculator_requires_units_per_hour()
{
    $standard = Standard::factory()->create(['units_per_hour' => 0]);

    $this->expectException(\DivisionByZeroError::class);

    $standard->calculateRequiredHours(100);
}

/** @test */
public function capacity_wizard_filters_parts_without_units_per_hour()
{
    $partWithStandard = Part::factory()->create();
    Standard::factory()->create([
        'part_id' => $partWithStandard->id,
        'active' => true,
        'units_per_hour' => 100,
    ]);

    $partWithoutStandard = Part::factory()->create();
    Standard::factory()->create([
        'part_id' => $partWithoutStandard->id,
        'active' => true,
        'units_per_hour' => 0, // ❌ Valor inválido
    ]);

    $wizard = Livewire::test(CapacityWizard::class);

    // Solo debe aparecer la parte con estándar válido
    $this->assertContains($partWithStandard->id, $wizard->get('parts')->pluck('id'));
    $this->assertNotContains($partWithoutStandard->id, $wizard->get('parts')->pluck('id'));
}
```

**Impacto:**
- ⚠️ Los tests existentes **NO validan** la capacidad de editar `units_per_hour`
- ⚠️ Faltan tests de integración para el flujo completo de edición

---

### Análisis de Riesgo

#### Matriz de Riesgos

| Riesgo | Probabilidad | Impacto | Severidad | Mitigación |
|--------|--------------|---------|-----------|------------|
| **Datos incorrectos en planificación** | Alta | Crítico | 🔴 CRÍTICA | Agregar campo a edit |
| **Planificaciones de capacidad erróneas** | Alta | Crítico | 🔴 CRÍTICA | Agregar campo a edit |
| **Partes desaparecen del Capacity Wizard** | Media | Crítico | 🔴 CRÍTICA | Validación + campo en edit |
| **Inconsistencia UX** | Alta | Medio | 🟠 ALTA | Agregar campo a edit |
| **Acceso directo a BD para ediciones** | Media | Alto | 🟠 ALTA | Agregar campo a edit |
| **Pérdida de auditoría** | Media | Medio | 🟡 MEDIA | Agregar campo a edit |

---

## Propuesta de Solución

### Solución Recomendada: AGREGAR el Campo a la Vista de Edición

#### Justificación
1. **Integridad de Datos**: El campo es crítico para el sistema de capacidad
2. **Necesidad de Actualización**: Los estándares de producción cambian con el tiempo
3. **Consistencia UX**: Las vistas de crear y editar deben ser simétricas
4. **Auditoría**: Los cambios deben ser trazables en el sistema
5. **Usabilidad**: Los usuarios deben poder actualizar valores sin acceder a la BD

#### Cambios Necesarios

##### 1. Componente Livewire: `StandardEdit.php`

```php
// Archivo: app/Livewire/Admin/Standards/StandardEdit.php

class StandardEdit extends Component
{
    public Standard $standard;
    public ?int $part_id = null;

    // ✅ AGREGAR: Propiedad para units_per_hour
    public string $units_per_hour = '';  // ← NUEVO

    public ?int $work_table_id = null;
    // ... resto de propiedades

    public function mount(Standard $standard): void
    {
        $this->standard = $standard;
        $this->part_id = $standard->part_id;

        // ✅ AGREGAR: Cargar units_per_hour
        $this->units_per_hour = $standard->units_per_hour
            ? (string) $standard->units_per_hour
            : '';  // ← NUEVO

        $this->work_table_id = $standard->work_table_id;
        // ... resto del mount
    }

    protected function rules(): array
    {
        return [
            'part_id' => 'required|exists:parts,id',

            // ✅ AGREGAR: Validación para units_per_hour
            'units_per_hour' => 'required|integer|min:1',  // ← NUEVO

            'work_table_id' => 'nullable|exists:tables,id',
            // ... resto de validaciones
        ];
    }

    protected function messages(): array
    {
        return [
            'part_id.required' => 'Debe seleccionar una parte.',
            'part_id.exists' => 'La parte seleccionada no existe.',

            // ✅ AGREGAR: Mensajes personalizados
            'units_per_hour.required' => 'Las unidades por hora son obligatorias.',  // ← NUEVO
            'units_per_hour.integer' => 'Las unidades por hora deben ser un número entero.',  // ← NUEVO
            'units_per_hour.min' => 'Las unidades por hora deben ser al menos 1.',  // ← NUEVO

            'work_table_id.exists' => 'La mesa de trabajo seleccionada no existe.',
            // ... resto de mensajes
        ];
    }

    public function updateStandard(): void
    {
        $this->validate();

        $this->standard->update([
            'part_id' => $this->part_id,

            // ✅ AGREGAR: Actualizar units_per_hour
            'units_per_hour' => $this->units_per_hour,  // ← NUEVO

            'work_table_id' => $this->work_table_id ?: null,
            // ... resto de campos
        ]);

        session()->flash('flash.banner', 'Estándar actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.standards.index'), navigate: true);
    }
}
```

---

##### 2. Vista Blade: `standard-edit.blade.php`

```blade
<!-- Archivo: resources/views/livewire/admin/standards/standard-edit.blade.php -->

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <!-- ... (sin cambios) -->

        <!-- Card Container -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <form wire:submit="updateStandard" class="space-y-6">
                    <!-- Part -->
                    <div>
                        <label for="part_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Parte <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="part_id" id="part_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                            <option value="">Seleccione una parte</option>
                            @foreach($parts as $part)
                                <option value="{{ $part->id }}">{{ $part->number }} - {{ Str::limit($part->description, 40) }}</option>
                            @endforeach
                        </select>
                        @error('part_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ✅ AGREGAR: Units Per Hour (NUEVO BLOQUE) -->
                    <div>
                        <label for="units_per_hour" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Unidades por Hora <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="units_per_hour" id="units_per_hour" type="number" min="1"
                            placeholder="Ej: 50"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                            required />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Cantidad de unidades que se producen por hora en esta estación
                        </p>
                        @error('units_per_hour')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- FIN NUEVO BLOQUE -->

                    <!-- Work Stations -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- ... (sin cambios) -->
                    </div>

                    <!-- Persons 1, 2, 3 -->
                    <!-- ... (sin cambios) -->

                    <!-- Effective Date -->
                    <!-- ... (sin cambios) -->

                    <!-- Active Status -->
                    <!-- ... (sin cambios) -->

                    <!-- Description -->
                    <!-- ... (sin cambios) -->

                    <!-- Buttons -->
                    <!-- ... (sin cambios) -->
                </form>
            </div>
        </div>
    </div>
</div>
```

**Ubicación del Nuevo Campo:**
- **DESPUÉS** del campo "Parte"
- **ANTES** del grid de "Work Stations"
- **Mismo diseño y estilo** que en la vista de creación

---

##### 3. Vista Blade: `standard-show.blade.php` (OPCIONAL pero RECOMENDADO)

```blade
<!-- Archivo: resources/views/livewire/admin/standards/standard-show.blade.php -->
<!-- Línea: Después del campo "Parte" (~línea 55) -->

<dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Parte</dt>
        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">
            {{ $standard->part->number }}
            <span class="text-gray-500 dark:text-gray-400 font-normal">- {{ $standard->part->description }}</span>
        </dd>
    </div>

    <!-- ✅ AGREGAR: Mostrar units_per_hour (NUEVO) -->
    <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Unidades por Hora</dt>
        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">
            {{ $standard->units_per_hour }}
            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">unidades/hora</span>
        </dd>
    </div>
    <!-- FIN NUEVO -->

    <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</dt>
        <!-- ... -->
    </div>

    <!-- Resto de los campos -->
</dl>
```

---

##### 4. Vista Blade: `standard-list.blade.php` (OPCIONAL pero RECOMENDADO)

```blade
<!-- Archivo: resources/views/livewire/admin/standards/standard-list.blade.php -->
<!-- Agregar columna en la tabla (después de "Parte") -->

<thead class="bg-gray-50 dark:bg-gray-900">
    <tr>
        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            Parte
        </th>

        <!-- ✅ AGREGAR: Columna Units Per Hour (NUEVO) -->
        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            Unid/Hora
        </th>
        <!-- FIN NUEVO -->

        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            Mesa de Trabajo
        </th>
        <!-- ... resto de columnas -->
    </tr>
</thead>

<tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
    @foreach($standards as $standard)
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ $standard->part->number }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ Str::limit($standard->part->description, 30) }}
                </div>
            </td>

            <!-- ✅ AGREGAR: Celda Units Per Hour (NUEVO) -->
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white font-semibold">
                    {{ $standard->units_per_hour }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    unid/h
                </div>
            </td>
            <!-- FIN NUEVO -->

            <td class="px-6 py-4 whitespace-nowrap">
                <!-- ... -->
            </td>
            <!-- ... resto de celdas -->
        </tr>
    @endforeach
</tbody>
```

---

### Cambios NO Necesarios

#### ❌ NO Modificar:
1. **Modelo `Standard.php`**: Ya está correctamente configurado
2. **Migraciones**: Ya existen y están correctas
3. **`StandardCreate.php`**: Ya funciona correctamente
4. **Vista `standard-create.blade.php`**: Ya funciona correctamente
5. **Rutas**: Ya están correctamente definidas
6. **Servicios (`CapacityCalculatorService`)**: Ya usan el campo correctamente
7. **`CapacityWizard.php`**: Ya filtra y valida correctamente

---

## Plan de Implementación

### Fase 1: Análisis y Preparación (Completado)
- [x] Análisis de la estructura del CRUD
- [x] Identificación de dependencias
- [x] Evaluación de impacto
- [x] Documentación del problema

### Fase 2: Implementación de Cambios

#### Paso 1: Backup de Seguridad
```bash
# Backup de base de datos
php artisan db:backup  # O tu método de backup

# Backup de archivos
git add .
git commit -m "backup: Before adding units_per_hour to edit view"
```

#### Paso 2: Modificar `StandardEdit.php`
```bash
# Editar el archivo
# Archivo: app/Livewire/Admin/Standards/StandardEdit.php
```

**Cambios:**
1. Agregar propiedad `public string $units_per_hour = ''`
2. Cargar valor en `mount()`: `$this->units_per_hour = $standard->units_per_hour ? (string) $standard->units_per_hour : ''`
3. Agregar validación en `rules()`: `'units_per_hour' => 'required|integer|min:1'`
4. Agregar mensajes en `messages()`
5. Actualizar campo en `updateStandard()`: `'units_per_hour' => $this->units_per_hour`

**Tiempo estimado:** 15 minutos

---

#### Paso 3: Modificar `standard-edit.blade.php`
```bash
# Editar el archivo
# Archivo: resources/views/livewire/admin/standards/standard-edit.blade.php
```

**Cambios:**
1. Copiar el bloque de "Units Per Hour" de `standard-create.blade.php`
2. Insertar después del campo "Parte" (línea ~59)
3. Verificar que tenga el mismo estilo y estructura

**Tiempo estimado:** 10 minutos

---

#### Paso 4: (OPCIONAL) Modificar `standard-show.blade.php`
```bash
# Editar el archivo
# Archivo: resources/views/livewire/admin/standards/standard-show.blade.php
```

**Cambios:**
1. Agregar campo de visualización para `units_per_hour`
2. Insertar después del campo "Parte"

**Tiempo estimado:** 10 minutos

---

#### Paso 5: (OPCIONAL) Modificar `standard-list.blade.php`
```bash
# Editar el archivo
# Archivo: resources/views/livewire/admin/standards/standard-list.blade.php
```

**Cambios:**
1. Agregar columna "Unid/Hora" en el `<thead>`
2. Agregar celda correspondiente en el `<tbody>`

**Tiempo estimado:** 15 minutos

---

### Fase 3: Testing Manual

#### Test 1: Crear un Standard
```
✅ Objetivo: Verificar que el flujo de creación sigue funcionando

Pasos:
1. Ir a /admin/standards/create
2. Llenar todos los campos incluyendo "Unidades por Hora" = 100
3. Guardar
4. Verificar que se creó correctamente
5. Ir al listado y verificar que aparece

Resultado esperado: Standard creado con units_per_hour = 100
```

#### Test 2: Editar un Standard (Cambiar units_per_hour)
```
✅ Objetivo: Verificar que ahora se puede editar units_per_hour

Pasos:
1. Ir al listado de Standards
2. Hacer clic en "Editar" en un standard existente
3. VERIFICAR que el campo "Unidades por Hora" ESTÉ PRESENTE
4. VERIFICAR que el campo muestre el valor actual
5. Cambiar el valor de 100 a 150
6. Guardar
7. Ir a la vista de detalle y verificar el cambio
8. Verificar en la base de datos:
   SELECT id, part_id, units_per_hour FROM standards WHERE id = [id del standard];

Resultado esperado:
- Campo visible en la vista de edición
- Valor actualizado correctamente en BD
```

#### Test 3: Validación de units_per_hour
```
✅ Objetivo: Verificar que la validación funciona

Pasos:
1. Ir a editar un standard
2. Intentar guardar con units_per_hour vacío
3. Verificar mensaje de error: "Las unidades por hora son obligatorias."
4. Intentar guardar con units_per_hour = 0
5. Verificar mensaje de error: "Las unidades por hora deben ser al menos 1."
6. Intentar guardar con units_per_hour = "abc"
7. Verificar mensaje de error: "Las unidades por hora deben ser un número entero."

Resultado esperado: Todas las validaciones funcionan correctamente
```

#### Test 4: Integración con Capacity Wizard
```
✅ Objetivo: Verificar que el Capacity Wizard usa el valor actualizado

Pasos:
1. Editar un standard y cambiar units_per_hour de 100 a 200
2. Ir al Capacity Wizard (/admin/capacity-wizard)
3. Configurar Step 1 (turnos, fechas)
4. En Step 2, agregar la parte cuyo standard se editó
5. Ingresar cantidad = 100
6. VERIFICAR que las horas requeridas sean: 100 / 200 = 0.5 horas

Resultado esperado: El wizard usa el valor actualizado (200) correctamente
```

#### Test 5: Vista de Detalle (si se implementó)
```
✅ Objetivo: Verificar que la vista de detalle muestra units_per_hour

Pasos:
1. Ir al listado de Standards
2. Hacer clic en "Ver" en un standard
3. VERIFICAR que aparezca el campo "Unidades por Hora"
4. VERIFICAR que muestre el valor correcto

Resultado esperado: Campo visible con valor correcto
```

#### Test 6: Vista de Listado (si se implementó)
```
✅ Objetivo: Verificar que la vista de listado muestra units_per_hour

Pasos:
1. Ir al listado de Standards (/admin/standards)
2. VERIFICAR que haya una columna "Unid/Hora"
3. VERIFICAR que muestre los valores correctos para cada standard

Resultado esperado: Columna visible con valores correctos
```

---

### Fase 4: Testing Automatizado (Recomendado)

#### Test Feature: `StandardEditTest.php`

```php
<?php

namespace Tests\Feature\Livewire\Admin\Standards;

use App\Livewire\Admin\Standards\StandardEdit;
use App\Models\Standard;
use App\Models\Part;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StandardEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Standard $standard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $part = Part::factory()->create();
        $this->standard = Standard::factory()->create([
            'part_id' => $part->id,
            'units_per_hour' => 100,
            'active' => true,
        ]);
    }

    /** @test */
    public function it_loads_units_per_hour_in_edit_form()
    {
        Livewire::actingAs($this->admin)
            ->test(StandardEdit::class, ['standard' => $this->standard])
            ->assertSet('units_per_hour', '100')
            ->assertSee('Unidades por Hora');
    }

    /** @test */
    public function it_validates_units_per_hour_is_required()
    {
        Livewire::actingAs($this->admin)
            ->test(StandardEdit::class, ['standard' => $this->standard])
            ->set('units_per_hour', '')
            ->call('updateStandard')
            ->assertHasErrors(['units_per_hour' => 'required']);
    }

    /** @test */
    public function it_validates_units_per_hour_is_integer()
    {
        Livewire::actingAs($this->admin)
            ->test(StandardEdit::class, ['standard' => $this->standard])
            ->set('units_per_hour', 'abc')
            ->call('updateStandard')
            ->assertHasErrors(['units_per_hour' => 'integer']);
    }

    /** @test */
    public function it_validates_units_per_hour_minimum_value()
    {
        Livewire::actingAs($this->admin)
            ->test(StandardEdit::class, ['standard' => $this->standard])
            ->set('units_per_hour', '0')
            ->call('updateStandard')
            ->assertHasErrors(['units_per_hour' => 'min']);
    }

    /** @test */
    public function it_updates_units_per_hour_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(StandardEdit::class, ['standard' => $this->standard])
            ->set('units_per_hour', '150')
            ->call('updateStandard')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.standards.index'));

        $this->assertDatabaseHas('standards', [
            'id' => $this->standard->id,
            'units_per_hour' => 150,
        ]);
    }

    /** @test */
    public function it_allows_updating_units_per_hour_without_affecting_other_fields()
    {
        $originalPartId = $this->standard->part_id;
        $originalDescription = $this->standard->description;

        Livewire::actingAs($this->admin)
            ->test(StandardEdit::class, ['standard' => $this->standard])
            ->set('units_per_hour', '200')
            ->call('updateStandard');

        $this->standard->refresh();

        $this->assertEquals(200, $this->standard->units_per_hour);
        $this->assertEquals($originalPartId, $this->standard->part_id);
        $this->assertEquals($originalDescription, $this->standard->description);
    }

    /** @test */
    public function updated_units_per_hour_is_used_by_capacity_calculator()
    {
        // Actualizar units_per_hour
        Livewire::actingAs($this->admin)
            ->test(StandardEdit::class, ['standard' => $this->standard])
            ->set('units_per_hour', '200')
            ->call('updateStandard');

        $this->standard->refresh();

        // Verificar que el cálculo usa el nuevo valor
        $requiredHours = $this->standard->calculateRequiredHours(100);

        $this->assertEquals(0.5, $requiredHours); // 100 / 200 = 0.5
    }
}
```

**Ejecutar tests:**
```bash
php artisan test --filter StandardEditTest
```

---

### Fase 5: Verificación de Datos Existentes

#### Script de Verificación SQL

```sql
-- Verificar estado de units_per_hour en todos los standards
SELECT
    'Total Standards' as metric,
    COUNT(*) as value
FROM standards
WHERE deleted_at IS NULL

UNION ALL

SELECT
    'Standards con units_per_hour válido (> 0)' as metric,
    COUNT(*) as value
FROM standards
WHERE deleted_at IS NULL
AND units_per_hour > 0

UNION ALL

SELECT
    'Standards con units_per_hour = 0' as metric,
    COUNT(*) as value
FROM standards
WHERE deleted_at IS NULL
AND units_per_hour = 0

UNION ALL

SELECT
    'Standards con units_per_hour NULL' as metric,
    COUNT(*) as value
FROM standards
WHERE deleted_at IS NULL
AND units_per_hour IS NULL;

-- Partes afectadas si units_per_hour es inválido
SELECT
    p.id,
    p.number,
    p.description,
    s.units_per_hour,
    s.active,
    CASE
        WHEN s.units_per_hour IS NULL THEN 'NULL'
        WHEN s.units_per_hour = 0 THEN 'ZERO'
        WHEN s.units_per_hour < 0 THEN 'NEGATIVE'
        ELSE 'VALID'
    END as status
FROM parts p
INNER JOIN standards s ON s.part_id = p.id
WHERE p.active = 1
AND s.deleted_at IS NULL
AND (s.units_per_hour IS NULL OR s.units_per_hour <= 0);
```

#### Script de Corrección (si hay datos inválidos)

```sql
-- SOLO EJECUTAR SI HAY DATOS INVÁLIDOS

-- Opción 1: Establecer default = 1 para valores NULL o 0
UPDATE standards
SET units_per_hour = 1
WHERE (units_per_hour IS NULL OR units_per_hour = 0)
AND deleted_at IS NULL;

-- Opción 2: Calcular un valor aproximado basado en persons_1
-- (Asume que persons_1 es una métrica de producción)
UPDATE standards
SET units_per_hour = GREATEST(persons_1 / 10, 1)
WHERE (units_per_hour IS NULL OR units_per_hour = 0)
AND deleted_at IS NULL
AND persons_1 > 0;

-- Verificar resultados
SELECT
    id,
    part_id,
    units_per_hour,
    persons_1,
    updated_at
FROM standards
WHERE deleted_at IS NULL
ORDER BY updated_at DESC
LIMIT 10;
```

---

### Fase 6: Documentación y Comunicación

#### 1. Changelog / Release Notes

```markdown
## [Versión X.X.X] - 2026-01-12

### Agregado
- Campo "Unidades por Hora" ahora editable en la vista de edición de Standards
- Columna "Unid/Hora" en el listado de Standards (opcional)
- Visualización de "Unidades por Hora" en la vista de detalle (opcional)

### Corregido
- Inconsistencia entre vista de creación y edición de Standards
- Imposibilidad de actualizar el valor de units_per_hour después de crear un Standard

### Técnico
- Agregada propiedad `units_per_hour` a `StandardEdit.php`
- Agregada validación `required|integer|min:1` para `units_per_hour` en edición
- Actualizadas vistas Blade para consistencia UX
```

#### 2. Comunicación al Equipo

**Email / Slack:**
```
🔧 Actualización: Standards - Campo "Unidades por Hora"

Hola equipo,

Se ha corregido una inconsistencia en el módulo de Standards:

✅ ANTES:
- El campo "Unidades por Hora" solo se podía establecer al crear
- No era posible actualizarlo después

✅ AHORA:
- El campo "Unidades por Hora" es editable en cualquier momento
- Se mantiene visible en la vista de edición
- Validaciones aplicadas para evitar valores inválidos

⚠️ IMPORTANTE:
- Este campo es CRÍTICO para el Capacity Wizard
- Valores incorrectos afectan las planificaciones de producción
- Por favor, revisar y actualizar standards obsoletos

📋 Cómo actualizar:
1. Ir a Admin > Standards
2. Click en "Editar" en el standard deseado
3. Actualizar "Unidades por Hora"
4. Guardar

Cualquier duda, estoy disponible.

Saludos,
[Tu nombre]
```

---

## Validaciones y Pruebas

### Checklist de Implementación

#### Backend
- [ ] Propiedad `units_per_hour` agregada a `StandardEdit.php`
- [ ] Método `mount()` carga el valor de `units_per_hour`
- [ ] Validación `required|integer|min:1` agregada a `rules()`
- [ ] Mensajes de error personalizados en `messages()`
- [ ] Método `updateStandard()` actualiza el campo en BD
- [ ] No hay errores de sintaxis en PHP

#### Frontend
- [ ] Campo "Unidades por Hora" agregado a `standard-edit.blade.php`
- [ ] Campo ubicado en la posición correcta (después de "Parte")
- [ ] Estilo consistente con la vista de creación
- [ ] Etiqueta correcta: "Unidades por Hora" + asterisco rojo
- [ ] Input type="number" con min="1"
- [ ] Placeholder: "Ej: 50"
- [ ] Texto de ayuda visible
- [ ] Manejo de errores con `@error` directive
- [ ] No hay errores de sintaxis en Blade

#### Vistas Adicionales (Opcional)
- [ ] `standard-show.blade.php` muestra el valor (si se implementó)
- [ ] `standard-list.blade.php` tiene la columna (si se implementó)

#### Testing Manual
- [ ] Test 1: Crear standard funciona correctamente
- [ ] Test 2: Editar units_per_hour actualiza en BD
- [ ] Test 3: Validaciones funcionan (vacío, 0, no numérico)
- [ ] Test 4: Capacity Wizard usa valor actualizado
- [ ] Test 5: Vista de detalle muestra valor (si aplica)
- [ ] Test 6: Vista de listado muestra columna (si aplica)

#### Testing Automatizado (Recomendado)
- [ ] Tests de feature creados y ejecutados
- [ ] Todos los tests pasan sin errores
- [ ] Cobertura de código > 80% (para nuevos cambios)

#### Datos
- [ ] Script de verificación SQL ejecutado
- [ ] No hay standards con units_per_hour NULL o 0
- [ ] Datos existentes migrados correctamente (si fue necesario)

#### Documentación
- [ ] Changelog actualizado
- [ ] Comunicación enviada al equipo
- [ ] README actualizado (si aplica)

#### Deploy
- [ ] Cambios commiteados a Git
- [ ] Pull Request creado y revisado
- [ ] CI/CD pipeline ejecutado sin errores
- [ ] Deploy a staging exitoso
- [ ] Pruebas de smoke en staging
- [ ] Deploy a producción (con plan de rollback)

---

### Métricas de Éxito

| Métrica | Objetivo | Método de Medición |
|---------|----------|-------------------|
| **Consistencia UX** | 100% | Ambas vistas (crear/editar) tienen el campo |
| **Capacidad de edición** | 100% | Usuarios pueden actualizar units_per_hour |
| **Validación funcional** | 100% | No se permiten valores inválidos (0, NULL, negativos) |
| **Integración con Capacity Wizard** | 100% | Usa valores actualizados correctamente |
| **Tests pasando** | 100% | Todos los tests automatizados pasan |
| **Datos válidos** | 100% | No hay standards con units_per_hour inválido |
| **Sin regresiones** | 0 bugs | No se introducen nuevos errores |

---

## Referencias

### Documentos Relacionados

1. **Spec 01:** `01_production_capacity_implementation_plan.md`
   - Define la importancia crítica de `units_per_hour`
   - Formula de cálculo: `required_hours = quantity / units_per_hour`

2. **Spec 05:** `05_standards_structure_analysis.md`
   - Análisis de la estructura del modelo Standard

3. **Spec 09:** `09_production_capacity_calculator_implementation_analysis.md`
   - Uso de `units_per_hour` en el Capacity Calculator

### Archivos Afectados

#### Backend
```
app/
├── Livewire/
│   └── Admin/
│       └── Standards/
│           └── StandardEdit.php              ← MODIFICAR
├── Models/
│   └── Standard.php                          ← Sin cambios (ya correcto)
└── Services/
    └── CapacityCalculatorService.php         ← Sin cambios (ya usa el campo)
```

#### Frontend
```
resources/
└── views/
    └── livewire/
        └── admin/
            └── standards/
                ├── standard-create.blade.php  ← Sin cambios (referencia)
                ├── standard-edit.blade.php    ← MODIFICAR
                ├── standard-show.blade.php    ← MODIFICAR (opcional)
                └── standard-list.blade.php    ← MODIFICAR (opcional)
```

#### Database
```
database/
├── migrations/
│   ├── 2025_12_14_190425_create_standards_table.php                ← Sin cambios
│   └── 2025_12_20_081207_add_units_per_hour_to_standards_table.php ← Sin cambios (ya existe)
├── factories/
│   └── StandardFactory.php                                         ← Sin cambios
└── seeders/
    └── StandardSeeder.php                                          ← Sin cambios
```

#### Testing
```
tests/
└── Feature/
    └── Livewire/
        └── Admin/
            └── Standards/
                └── StandardEditTest.php       ← CREAR (recomendado)
```

---

## Conclusión

### Resumen del Análisis

1. **Problema Real**: Falta el campo "Unidades por Hora" en la vista de **EDICIÓN**, no que sobre en la vista de **CREACIÓN**.

2. **Impacto**: CRÍTICO para el sistema de planificación de capacidad.

3. **Solución**: AGREGAR el campo a la vista de edición, manteniendo consistencia.

4. **Riesgo**: BAJO - Los cambios son aditivos, no destructivos.

5. **Beneficio**: ALTO - Permite actualizar estándares de producción sin acceso directo a BD.

### Próximos Pasos

1. ✅ **Implementar cambios** en `StandardEdit.php` y `standard-edit.blade.php`
2. ⚠️ **Testing exhaustivo** (manual + automatizado)
3. ✅ **Verificar integración** con Capacity Wizard
4. ⚠️ **Comunicar cambios** al equipo
5. ✅ **Documentar** en changelog

### Recomendaciones Finales

- ✅ **Implementar** los cambios propuestos
- ✅ **NO eliminar** el campo de ninguna parte del sistema
- ✅ **Agregar** el campo a las vistas de detalle y listado (opcional pero recomendado)
- ✅ **Crear tests** automatizados para prevenir regresiones futuras
- ✅ **Revisar** periódicamente que todos los standards tengan `units_per_hour` válido

---

**Documento creado por:** Architect Agent
**Fecha:** 2026-01-12
**Versión:** 1.0
**Estado:** Completo y Listo para Implementación
