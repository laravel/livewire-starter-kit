# Spec 03: Guía Rápida de Implementación - Standards Workstation Relationship

**Fecha de Creación:** 2025-12-20
**Autor:** Architect Agent
**Fase del Proyecto:** FASE 2 - Planificación de Producción
**Estado:** Aprobado para Implementación Inmediata
**Versión:** 1.0
**Relacionado con:**
- Spec 01 - Plan de Implementación Capacidad de Producción
- Spec 02 - Refactorización Standards-Workstation (Análisis Técnico Completo)

---

## Resumen Ejecutivo

### Decisión Final: Opción B - Mantener Foreign Keys Directas

Después del análisis técnico exhaustivo documentado en **Spec 02**, se decidió **MANTENER** la estructura actual de foreign keys directas en lugar de refactorizar a relaciones polimórficas.

### Justificación (4 Puntos Clave)

1. **Pragmatismo**: El código existente ya está implementado y funcionando con este patrón
2. **Costo-Beneficio**: Implementación en 1 día vs 3-4 días de refactorización completa
3. **Bajo Riesgo**: No rompe datos existentes ni requiere cambios breaking en código Livewire
4. **Performance Equivalente**: Los índices actuales ya optimizan las búsquedas

### Cambios INDISPENSABLES

#### Lo QUE SE VA A HACER:
1. Agregar campo `units_per_hour` a tabla `standards` (CRÍTICO)
2. Crear Custom Validation Rule `OnlyOneWorkstation`
3. Agregar métodos helper en modelo `Standard`:
   - `getWorkstation()`: obtiene estación activa
   - `getAssemblyMode()`: calcula modo dinámicamente ('manual', 'semi_automatic', 'machine')
   - `calculateRequiredHours(int $quantity)`: calcula horas requeridas
4. Actualizar componentes Livewire `StandardCreate` y `StandardEdit` con:
   - Campo `units_per_hour`
   - Validación de mutua exclusividad de estaciones
   - Auto-deselección de estaciones al seleccionar una
5. Actualizar vistas Blade con input `units_per_hour` y mejoras de UX

#### Lo QUE NO SE VA A HACER:
- NO agregar campo `assembly_mode` (redundante, se calcula dinámicamente)
- NO refactorizar a relaciones polimórficas
- NO cambiar la estructura de foreign keys existentes
- NO romper compatibilidad con código existente

---

## Tiempo Estimado Realista

**Total:** 4-6 horas de implementación + testing

| Fase | Tiempo | Descripción |
|------|--------|-------------|
| Migración + Modelo | 1 hora | Crear migración, actualizar modelo Standard, crear Rule |
| Componentes Livewire | 2 horas | StandardCreate + StandardEdit |
| Vistas Blade | 1 hora | Actualizar formularios y listas |
| Testing Manual | 1 hora | Validar funcionalidad end-to-end |
| Ajustes/Debugging | 1 hora | Buffer para issues inesperados |

---

## Riesgos Críticos

| Riesgo | Probabilidad | Mitigación |
|--------|--------------|------------|
| Datos con múltiples estaciones asignadas | MEDIA | Ejecutar query de verificación ANTES de migración |
| División por cero en cálculos | BAJA | Validación `min:1` en `units_per_hour` |
| Usuario confundido con validación | MEDIA | Mensajes claros + auto-deselección automática |
| Incompatibilidad con CapacityCalculatorService | BAJA | Usar `$standard->assembly_mode` accessor |

---

## Archivos a Modificar

### Nuevos Archivos (3):
1. `database/migrations/YYYY_MM_DD_HHMMSS_add_units_per_hour_to_standards_table.php`
2. `app/Rules/OnlyOneWorkstation.php`
3. (Opcional) `database/seeders/StandardSeeder.php` - actualizar con `units_per_hour`

### Archivos Existentes a Modificar (6):
1. `app/Models/Standard.php`
2. `app/Livewire/Admin/Standards/StandardCreate.php`
3. `app/Livewire/Admin/Standards/StandardEdit.php`
4. `resources/views/livewire/admin/standards/standard-create.blade.php`
5. `resources/views/livewire/admin/standards/standard-edit.blade.php`
6. (Opcional) `app/Models/Table.php`, `Semi_Automatic.php`, `Machine.php` - relaciones inversas

---

## Código Completo para Implementación

### 1. Migración

**Comando:**
```bash
php artisan make:migration add_units_per_hour_to_standards_table
```

**Archivo:** `database/migrations/YYYY_MM_DD_HHMMSS_add_units_per_hour_to_standards_table.php`

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

---

### 2. Custom Validation Rule

**Comando:**
```bash
php artisan make:rule OnlyOneWorkstation
```

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

---

### 3. Modelo Standard - Métodos Helper

**Archivo:** `app/Models/Standard.php`

**Modificaciones:**

#### 3.1 Actualizar `$fillable` (agregar `units_per_hour`):

```php
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
```

#### 3.2 Actualizar `$casts` (agregar cast para `units_per_hour`):

```php
protected $casts = [
    'effective_date' => 'date',
    'persons_1' => 'integer',
    'persons_2' => 'integer',
    'persons_3' => 'integer',
    'active' => 'boolean',
    'units_per_hour' => 'integer', // NUEVO
];
```

#### 3.3 Agregar Métodos Helper (al final de la clase, después de relaciones existentes):

```php
/**
 * ===============================================
 * MÉTODOS HELPER PARA WORKSTATION MANAGEMENT
 * ===============================================
 */

/**
 * Obtiene la estación de trabajo activa (primera no-null)
 *
 * @return \App\Models\Table|\App\Models\Semi_Automatic|\App\Models\Machine|null
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

    if ($workstation instanceof \App\Models\Machine) {
        return $workstation->full_identification ?? $workstation->name;
    }

    return $workstation->number ?? 'N/A';
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
 * @param \Illuminate\Database\Eloquent\Builder $query
 * @param string $type 'manual', 'semi_automatic', 'machine'
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeByAssemblyMode($query, string $type)
{
    return match($type) {
        'manual' => $query->whereNotNull('work_table_id'),
        'semi_automatic' => $query->whereNotNull('semi_auto_work_table_id'),
        'machine' => $query->whereNotNull('machine_id'),
        default => $query,
    };
}
```

---

### 4. Componente StandardCreate

**Archivo:** `app/Livewire/Admin/Standards/StandardCreate.php`

**Modificaciones Completas:**

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
                'exists:semi__automatics,id',
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

---

### 5. Componente StandardEdit

**Archivo:** `app/Livewire/Admin/Standards/StandardEdit.php`

**Modificaciones:** Similares a StandardCreate. Cambios clave:

1. Agregar propiedad `public int $units_per_hour = 1;`
2. Actualizar `mount()` para cargar `units_per_hour`:
   ```php
   $this->units_per_hour = $this->standard->units_per_hour;
   ```
3. Copiar `rules()` de StandardCreate
4. Copiar `messages()` de StandardCreate
5. Copiar `updated()` de StandardCreate
6. Actualizar `updateStandard()` para incluir `units_per_hour`:
   ```php
   $this->standard->update([
       'part_id' => $this->part_id,
       'units_per_hour' => $this->units_per_hour, // NUEVO
       // ... resto de campos
   ]);
   ```

---

### 6. Vista Blade - StandardCreate

**Archivo:** `resources/views/livewire/admin/standards/standard-create.blade.php`

**Agregar campo units_per_hour después del campo part_id:**

```blade
{{-- Campo Units Per Hour --}}
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

**Actualizar sección de estaciones (reemplazar los 3 selects actuales):**

```blade
{{-- Sección de Estaciones de Trabajo --}}
<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Estación de Trabajo *
        <span class="text-gray-500 text-xs">(Seleccione solo UNA opción)</span>
    </label>

    <div class="space-y-3 border border-gray-200 rounded-lg p-4">
        {{-- Mesa Manual --}}
        <div>
            <label class="font-medium text-gray-700">Mesa de Trabajo Manual</label>
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
            @error('work_table_id')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Mesa Semi-Automática --}}
        <div>
            <label class="font-medium text-gray-700">Mesa Semi-Automática</label>
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
            @error('semi_auto_work_table_id')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Máquina --}}
        <div>
            <label class="font-medium text-gray-700">Máquina</label>
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
            @error('machine_id')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
```

---

### 7. Vista Blade - StandardEdit

**Archivo:** `resources/views/livewire/admin/standards/standard-edit.blade.php`

**Modificaciones:** Idénticas a `standard-create.blade.php`:
- Agregar campo `units_per_hour`
- Actualizar sección de estaciones con el mismo markup

---

## Orden de Implementación Paso a Paso

### PASO 1: Crear Migración (5 minutos)

```bash
php artisan make:migration add_units_per_hour_to_standards_table
```

Copiar código de migración de la sección 1 anterior.

**NO ejecutar aún** - primero verificar datos existentes.

---

### PASO 2: Verificar Datos Existentes (5 minutos)

Ejecutar query para detectar problemas:

```sql
-- Verificar standards con múltiples estaciones
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

**Si hay resultados:** Limpiar datos primero (ver Spec 02 sección Migración de Datos).

**Si NO hay resultados:** Continuar al paso 3.

---

### PASO 3: Crear Custom Rule (5 minutos)

```bash
php artisan make:rule OnlyOneWorkstation
```

Copiar código de la sección 2 anterior.

---

### PASO 4: Actualizar Modelo Standard (15 minutos)

Editar `app/Models/Standard.php`:

1. Agregar `units_per_hour` a `$fillable`
2. Agregar cast para `units_per_hour`
3. Copiar métodos helper de sección 3.3

---

### PASO 5: Actualizar StandardCreate Component (20 minutos)

Editar `app/Livewire/Admin/Standards/StandardCreate.php`:

1. Agregar propiedad `public int $units_per_hour = 1;`
2. Actualizar `rules()` con validación de `units_per_hour` y `OnlyOneWorkstation`
3. Agregar `messages()` completo
4. Agregar método `updated()` para auto-deselección
5. Actualizar `saveStandard()` para incluir validación adicional y `units_per_hour`

---

### PASO 6: Actualizar StandardEdit Component (20 minutos)

Editar `app/Livewire/Admin/Standards/StandardEdit.php`:

Aplicar mismos cambios que StandardCreate, más:
- Actualizar `mount()` para cargar `units_per_hour`
- Actualizar `updateStandard()` para incluir `units_per_hour`

---

### PASO 7: Actualizar Vistas Blade (30 minutos)

1. Editar `resources/views/livewire/admin/standards/standard-create.blade.php`:
   - Agregar campo `units_per_hour`
   - Actualizar sección de estaciones

2. Editar `resources/views/livewire/admin/standards/standard-edit.blade.php`:
   - Mismas modificaciones que Create

---

### PASO 8: Ejecutar Migración (2 minutos)

```bash
php artisan migrate
```

Verificar salida sin errores.

---

### PASO 9: Testing Manual (30 minutos)

1. Crear nuevo standard:
   - Verificar campo `units_per_hour` aparece
   - Seleccionar mesa manual → otros selects se limpian automáticamente
   - Intentar seleccionar dos estaciones → ver mensaje de error
   - Guardar con `units_per_hour = 100` → verificar en DB

2. Editar standard existente:
   - Verificar `units_per_hour` carga correctamente
   - Cambiar tipo de estación → auto-deselección funciona
   - Actualizar → cambios persisten

3. Verificar en Tinker:
   ```php
   $standard = Standard::first();
   $standard->assembly_mode; // 'manual', 'semi_automatic', 'machine'
   $standard->workstation_name; // Nombre de la estación
   $standard->calculateRequiredHours(500); // Resultado del cálculo
   ```

---

### PASO 10: Verificación Final (10 minutos)

```bash
# Verificar estructura de tabla
php artisan tinker
>>> Schema::hasColumn('standards', 'units_per_hour');
>>> true

# Verificar índice
>>> DB::select("SHOW INDEX FROM standards WHERE Key_name = 'standards_part_performance_index'");

# Verificar datos
>>> Standard::where('units_per_hour', 1)->count(); // Debería ser > 0 si hay datos con default
```

---

## Checklist de Implementación

- [ ] Migración creada y revisada
- [ ] Datos existentes verificados (query de múltiples estaciones)
- [ ] Custom Rule `OnlyOneWorkstation` creada
- [ ] Modelo `Standard` actualizado:
  - [ ] `units_per_hour` en `$fillable`
  - [ ] Cast para `units_per_hour`
  - [ ] Métodos helper agregados
- [ ] `StandardCreate` actualizado:
  - [ ] Propiedad `units_per_hour`
  - [ ] Validación actualizada
  - [ ] Auto-deselección implementada
- [ ] `StandardEdit` actualizado (mismo patrón que Create)
- [ ] Vista `standard-create.blade.php` actualizada:
  - [ ] Campo `units_per_hour` agregado
  - [ ] Sección de estaciones mejorada
- [ ] Vista `standard-edit.blade.php` actualizada
- [ ] Migración ejecutada sin errores
- [ ] Testing manual completado:
  - [ ] Crear standard funciona
  - [ ] Editar standard funciona
  - [ ] Auto-deselección funciona
  - [ ] Validación de mutua exclusividad funciona
  - [ ] Métodos helper funcionan en Tinker

---

## Comandos de Rollback (Si algo sale mal)

```bash
# Revertir migración
php artisan migrate:rollback --step=1

# Limpiar cache de Livewire
php artisan livewire:clear

# Limpiar caché de aplicación
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Referencias Rápidas

- **Spec 02 Completo:** `Diagramas_flujo/Estructura/specs/02_standards_workstation_relationship_refactor.md`
- **Spec 01:** `Diagramas_flujo/Estructura/specs/01_production_capacity_implementation_plan.md`
- **Documentación Laravel Validation:** https://laravel.com/docs/12.x/validation
- **Documentación Livewire:** https://livewire.laravel.com/docs/validation

---

## Notas Finales

Esta implementación es **conservadora y pragmática**. Mantiene la arquitectura existente mientras agrega la funcionalidad crítica requerida para el cálculo de capacidad de producción (Spec 01).

**Ventajas de este enfoque:**
- Bajo riesgo de bugs
- Compatibilidad total con código existente
- Implementación rápida (4-6 horas)
- Fácil de testear y revertir si es necesario

**Siguiente paso después de esta implementación:**
- Actualizar CapacityCalculatorService (Spec 01) para usar `$standard->assembly_mode` y `$standard->calculateRequiredHours()`

---

**Fin del Spec 03 - Guía Rápida de Implementación**
