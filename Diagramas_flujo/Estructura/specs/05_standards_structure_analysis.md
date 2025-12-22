# Analisis Tecnico: Estructura del Modulo Standards

**Documento:** 001-standards-structure-analysis.md
**Fecha:** 2025-12-22
**Version:** 1.0
**Autor:** Agent Architect

---

## 1. Resumen Ejecutivo

El modulo `Standards` es un componente central del sistema Flexcon-Tracker que define los estandares de produccion para cada parte (Part). Un estandar representa la configuracion operativa de como una pieza debe ser producida, incluyendo la estacion de trabajo, personal requerido, capacidad productiva y parametros temporales.

### Proposito del Modulo

- Definir configuraciones de produccion por parte
- Establecer relaciones entre partes y estaciones de trabajo (Manual, Semi-Automatica, Maquina)
- Calcular capacidad de produccion y horas requeridas
- Mantener historial de cambios mediante soft deletes
- Proveer metricas y estadisticas de estandares activos/inactivos

---

## 2. Estructura de Base de Datos

### 2.1 Esquema de la Tabla `standards`

```sql
CREATE TABLE standards (
    -- Identificadores
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Relaciones (Foreign Keys)
    part_id BIGINT UNSIGNED NOT NULL,                    -- Parte asociada (OBLIGATORIO)
    work_table_id BIGINT UNSIGNED NULL,                  -- Mesa de trabajo manual (OPCIONAL)
    semi_auto_work_table_id BIGINT UNSIGNED NULL,        -- Mesa semi-automatica (OPCIONAL)
    machine_id BIGINT UNSIGNED NULL,                     -- Maquina (OPCIONAL)

    -- Capacidad y Performance
    units_per_hour INT NOT NULL DEFAULT 1,               -- Unidades producidas por hora

    -- Configuracion de Personal (por numero de personas)
    persons_1 INT NULL,                                  -- Rendimiento con 1 persona
    persons_2 INT NULL,                                  -- Rendimiento con 2 personas
    persons_3 INT NULL,                                  -- Rendimiento con 3 personas

    -- Control de Estado
    effective_date DATE NULL,                            -- Fecha efectiva del estandar
    active BOOLEAN DEFAULT TRUE,                         -- Estado activo/inactivo
    description TEXT NULL,                               -- Descripcion del estandar

    -- Auditoria
    deleted_at TIMESTAMP NULL,                           -- Soft delete
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    -- Constraints
    FOREIGN KEY (part_id) REFERENCES parts(id) ON DELETE CASCADE,
    FOREIGN KEY (work_table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (semi_auto_work_table_id) REFERENCES semi__automatics(id) ON DELETE SET NULL,
    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE SET NULL
);
```

### 2.2 Indices Optimizados

El esquema incluye multiples indices para optimizar consultas frecuentes:

| Nombre del Indice | Columnas | Proposito |
|-------------------|----------|-----------|
| `standards_search_index` | `work_table_id, active` | Busquedas rapidas por mesa manual |
| `standards_semi_auto_active_index` | `semi_auto_work_table_id, active` | Busquedas por mesa semi-automatica |
| `standards_effective_date_index` | `effective_date` | Filtrado por fecha efectiva |
| `standards_active_index` | `active` | Filtrado por estado |
| `standards_machine_index` | `machine_id` | Busquedas por maquina |
| `standards_part_index` | `part_id` | Busquedas por parte |
| `standards_part_performance_index` | `part_id, active, units_per_hour` | Calculos de capacidad |

**Observacion Tecnica:** El indice compuesto `standards_part_performance_index` es especialmente critico para calculos de capacidad de produccion y planning de Work Orders.

---

## 3. Modelo Eloquent: `App\Models\Standard`

### 3.1 Propiedades y Configuracion

```php
class Standard extends Model
{
    use HasFactory, SoftDeletes;

    // Tabla: standards
    // Primary Key: id
    // Timestamps: created_at, updated_at
    // Soft Delete: deleted_at
}
```

### 3.2 Constantes de Negocio

```php
public const STATUS_ACTIVE = 1;
public const STATUS_INACTIVE = 0;
```

### 3.3 Atributos Fillable (Mass Assignment)

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
    'units_per_hour'
];
```

### 3.4 Casting de Tipos

```php
protected $casts = [
    'effective_date' => 'date',
    'persons_1' => 'integer',
    'persons_2' => 'integer',
    'persons_3' => 'integer',
    'active' => 'boolean',
    'description' => 'string',
    'part_id' => 'integer',
    'work_table_id' => 'integer',
    'semi_auto_work_table_id' => 'integer',
    'machine_id' => 'integer',
    'units_per_hour' => 'integer'
];
```

**Nota Tecnica:** El cast de `effective_date` a `date` permite operaciones de comparacion temporal directas con Carbon.

---

## 4. Relaciones del Modelo

### 4.1 Diagrama de Relaciones

```
┌─────────────────┐
│     Standard    │
└────────┬────────┘
         │
         ├─── belongsTo ──> Part (part_id) [OBLIGATORIO]
         │
         ├─── belongsTo ──> Table (work_table_id) [OPCIONAL]
         │
         ├─── belongsTo ──> Semi_Automatic (semi_auto_work_table_id) [OPCIONAL]
         │
         └─── belongsTo ──> Machine (machine_id) [OPCIONAL]
```

### 4.2 Relacion: `part()` - BelongsTo

```php
public function part()
{
    return $this->belongsTo(Part::class);
}
```

**Caracteristicas:**
- Relacion OBLIGATORIA (NOT NULL en BD)
- Cascade on delete: Si se elimina la parte, se eliminan sus estandares
- Un estandar pertenece a UNA sola parte
- Una parte puede tener MULTIPLES estandares (diferentes configuraciones)

### 4.3 Relacion: `workTable()` - BelongsTo

```php
public function workTable()
{
    return $this->belongsTo(Table::class, 'work_table_id');
}
```

**Caracteristicas:**
- Relacion OPCIONAL (nullable)
- Set NULL on delete: Si se elimina la mesa, el campo se setea a NULL
- Representa estaciones de trabajo MANUAL
- Foreign key explicita: `work_table_id`

### 4.4 Relacion: `semiAutoWorkTable()` - BelongsTo

```php
public function semiAutoWorkTable()
{
    return $this->belongsTo(Semi_Automatic::class, 'semi_auto_work_table_id');
}
```

**Caracteristicas:**
- Relacion OPCIONAL (nullable)
- Set NULL on delete
- Representa estaciones SEMI-AUTOMATICAS
- Tabla relacionada: `semi__automatics` (notese el doble underscore)

### 4.5 Relacion: `machine()` - BelongsTo

```php
public function machine()
{
    return $this->belongsTo(Machine::class);
}
```

**Caracteristicas:**
- Relacion OPCIONAL (nullable)
- Set NULL on delete
- Representa estaciones completamente AUTOMATIZADAS

### 4.6 Patron de Workstation Mutually Exclusive

**OBSERVACION CRITICA:** Un estandar puede tener SOLO UNA estacion de trabajo activa:
- `work_table_id` XOR `semi_auto_work_table_id` XOR `machine_id`

**Problema Identificado:** No existe constraint en base de datos que valide esta exclusividad. Es posible tener multiples estaciones asignadas simultaneamente.

**Recomendacion:** Implementar validacion custom o check constraint a nivel de base de datos.

---

## 5. Relaciones Inversas (Missing)

### 5.1 Relacion Faltante en Part Model

**Estado Actual:** El modelo `Part` NO tiene definida la relacion inversa hacia `Standards`.

**Impacto:**
- No se puede hacer `$part->standards` directamente
- No se pueden usar eager loading eficiente desde Part
- Queries N+1 potenciales al navegar de Part a Standards

**Recomendacion:** Agregar a `Part.php`:

```php
public function standards(): HasMany
{
    return $this->hasMany(Standard::class);
}
```

### 5.2 Relaciones Faltantes en Table, Semi_Automatic, Machine

Similar situacion en los modelos de estaciones de trabajo. No existe relacion inversa `hasMany` hacia Standards.

---

## 6. Scopes del Modelo

### 6.1 Scope: `active()`

```php
public function scopeActive(Builder $query): Builder
{
    return $query->where('active', true);
}
```

**Uso:** `Standard::active()->get()`

### 6.2 Scope: `inactive()`

```php
public function scopeInactive(Builder $query): Builder
{
    return $query->where('active', false);
}
```

### 6.3 Scope: `search(?string $search)`

```php
public function scopeSearch(Builder $query, ?string $search): Builder
{
    if (empty($search)) {
        return $query;
    }

    return $query->where(function ($q) use ($search) {
        $q->where('description', 'like', "%{$search}%")
          ->orWhereHas('part', function ($partQuery) use ($search) {
              $partQuery->where('number', 'like', "%{$search}%")
                        ->orWhere('item_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
          })
          ->orWhereHas('workTable', function ($tableQuery) use ($search) {
              $tableQuery->where('number', 'like', "%{$search}%");
          })
          ->orWhereHas('semiAutoWorkTable', function ($semiAutoQuery) use ($search) {
              $semiAutoQuery->where('number', 'like', "%{$search}%");
          })
          ->orWhereHas('machine', function ($machineQuery) use ($search) {
              $machineQuery->where('name', 'like', "%{$search}%")
                           ->orWhere('brand', 'like', "%{$search}%")
                           ->orWhere('model', 'like', "%{$search}%");
          });
    });
}
```

**Caracteristicas:**
- Busqueda fuzzy con LIKE
- Busca en descripcion del estandar
- Busca en relaciones: part, workTable, semiAutoWorkTable, machine
- Utiliza whereHas (puede generar queries lentas en datasets grandes)

**Observacion de Performance:** Este scope genera multiples subqueries. En tablas con +10k registros puede ser lento.

### 6.4 Scope: `byAssemblyMode(string $type)`

```php
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

**Uso:** Filtrar estandares por tipo de estacion de trabajo.

---

## 7. Metodos del Modelo

### 7.1 Metodos de Estado

#### `getStatuses(): array` (Static)

```php
public static function getStatuses(): array
{
    return [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'Inactive'
    ];
}
```

#### `getStatusLabelAttribute(): string` (Accessor)

```php
public function getStatusLabelAttribute(): string
{
    return self::getStatuses()[$this->active] ?? $this->active;
}
```

**Uso:** `$standard->status_label` retorna "Active" o "Inactive"

#### `getStatusColorAttribute(): string` (Accessor)

```php
public function getStatusColorAttribute(): string
{
    return match ($this->active) {
        self::STATUS_ACTIVE => 'green',
        self::STATUS_INACTIVE => 'red',
        default => 'gray',
    };
}
```

**Uso:** Para UI, retorna colores Tailwind-compatible

### 7.2 Metodos de Workstation Management

#### `getWorkstation()` - Obtener Estacion Activa

```php
public function getWorkstation()
{
    return $this->workTable ?? $this->semiAutoWorkTable ?? $this->machine;
}
```

**Logica:** Retorna la primera estacion no-null en orden de prioridad:
1. workTable (manual)
2. semiAutoWorkTable
3. machine

**Problema Potencial:** Si hay multiples estaciones asignadas simultaneamente, solo retorna la primera.

#### `getAssemblyMode(): ?string`

```php
public function getAssemblyMode(): ?string
{
    if ($this->work_table_id) return 'manual';
    if ($this->semi_auto_work_table_id) return 'semi_automatic';
    if ($this->machine_id) return 'machine';
    return null;
}
```

**Retorno:** String con el tipo de ensamble o NULL si no hay estacion asignada.

#### `getAssemblyModeAttribute(): ?string` (Accessor)

Permite usar `$standard->assembly_mode` directamente.

#### `getWorkstationNameAttribute(): string` (Accessor)

```php
public function getWorkstationNameAttribute(): string
{
    $workstation = $this->getWorkstation();

    if (!$workstation) {
        return 'Sin estacion asignada';
    }

    if ($workstation instanceof \App\Models\Machine) {
        return $workstation->full_identification ?? $workstation->name;
    }

    return $workstation->number ?? 'N/A';
}
```

**Uso:** `$standard->workstation_name` retorna nombre legible de la estacion.

### 7.3 Metodos de Calculo

#### `calculateRequiredHours(int $quantity): float`

```php
public function calculateRequiredHours(int $quantity): float
{
    if ($this->units_per_hour === 0) {
        throw new \DivisionByZeroError(
            "El estandar para la parte '{$this->part->number}' tiene units_per_hour = 0"
        );
    }

    return round($quantity / $this->units_per_hour, 2);
}
```

**Formula:** `horas_requeridas = cantidad_a_producir / units_per_hour`

**Validacion:** Lanza excepcion si `units_per_hour` es 0.

**Precision:** Redondea a 2 decimales.

**Ejemplo:**
- Producir 1000 unidades con `units_per_hour = 250`
- Resultado: `1000 / 250 = 4.00 horas`

### 7.4 Metodos de Validacion

#### `canBeDeleted(): bool`

```php
public function canBeDeleted(): bool
{
    return true;
}
```

**Observacion:** Siempre retorna `true` porque se usa soft delete. El historial se mantiene.

### 7.5 Metodos Estadisticos

#### `getStats(): array` (Static)

```php
public static function getStats(): array
{
    $total = self::count();
    $active = self::where('active', true)->count();
    $inactive = self::where('active', false)->count();
    $current = self::where('effective_date', '<=', now())
                   ->where('active', true)
                   ->count();

    return [
        'total' => $total,
        'active' => $active,
        'inactive' => $inactive,
        'current' => $current,
    ];
}
```

**Metricas Retornadas:**
- `total`: Total de estandares (incluyendo soft deleted si no se scope)
- `active`: Estandares con `active = true`
- `inactive`: Estandares con `active = false`
- `current`: Estandares activos Y con fecha efectiva vigente

**Uso:** Dashboard y reportes estadisticos.

---

## 8. Migraciones

### 8.1 Migracion Principal: `2025_12_14_190425_create_standards_table.php`

Crea la tabla `standards` con:
- Campos de relaciones (part_id, work_table_id, semi_auto_work_table_id, machine_id)
- Campos de configuracion (persons_1, persons_2, persons_3)
- Campos de control (effective_date, active, description)
- Soft deletes y timestamps
- Indices de optimizacion

### 8.2 Migracion Adicional: `2025_12_20_081207_add_units_per_hour_to_standards_table.php`

**Fecha:** 2025-12-20 (RECIENTE)

**Cambios:**
- Agrega columna `units_per_hour` tipo INTEGER
- Default: 1
- Posicion: Despues de `part_id`
- Comentario: "Unidades producidas por hora en esta estacion"
- Agrega indice compuesto: `standards_part_performance_index` sobre `(part_id, active, units_per_hour)`

**Razon de Ser:** Implementar calculos de capacidad y tiempo de produccion.

---

## 9. Factory: `StandardFactory`

```php
public function definition(): array
{
    return [
        'part_id' => Part::factory(),
        'work_table_id' => $this->faker->boolean(60) ? Table::inRandomOrder()->first()?->id : null,
        'semi_auto_work_table_id' => $this->faker->boolean(40) ? Semi_Automatic::inRandomOrder()->first()?->id : null,
        'machine_id' => $this->faker->boolean(50) ? Machine::inRandomOrder()->first()?->id : null,
        'persons_1' => $this->faker->numberBetween(1, 3000),
        'persons_2' => $this->faker->numberBetween(1, 3000),
        'persons_3' => $this->faker->numberBetween(1, 3000),
        'effective_date' => $this->faker->dateTimeBetween('-1 year', '+1 month'),
        'active' => $this->faker->boolean(80),
        'description' => $this->faker->optional()->sentence(),
    ];
}
```

**Probabilidades de Generacion:**
- `work_table_id`: 60% de tener valor
- `semi_auto_work_table_id`: 40% de tener valor
- `machine_id`: 50% de tener valor
- `active`: 80% de estar activo

**PROBLEMA CRITICO:** El factory puede generar standards con MULTIPLES estaciones asignadas simultaneamente, violando la exclusividad mutua.

**Problema de Datos Faltantes:** No genera valor para `units_per_hour`, por lo que usara el default de migracion (1).

---

## 10. Seeder: `StandardSeeder`

```php
public function run(): void
{
    if (Part::count() === 0) {
        $this->command->warn('Skipping StandardSeeder: Missing required data (Parts)');
        return;
    }

    Standard::factory()->count(10)->create();
    $this->command->info('Standards created successfully!');
}
```

**Validacion:** Verifica existencia de Parts antes de crear Standards.

**Cantidad:** Genera 10 estandares de prueba.

**Dependencias:** Requiere que existan Parts en la BD.

---

## 11. Controlador: `StandardController`

```php
class StandardController extends Controller
{
    //
}
```

**Estado:** VACIO, sin implementacion.

**Observacion:** El proyecto usa Livewire Components en lugar de controladores tradicionales.

---

## 12. Componentes Livewire

### 12.1 Arquitectura General

El modulo Standards sigue patron CRUD completo con Livewire:

```
StandardList    (Index/Listado)
StandardCreate  (Crear nuevo)
StandardEdit    (Editar existente)
StandardShow    (Ver detalles)
```

### 12.2 `StandardList` - Listado y Gestion

**Ubicacion:** `app/Livewire/Admin/Standards/StandardList.php`

**Propiedades Publicas:**
```php
public string $search = '';              // Busqueda
public string $sortField = 'created_at'; // Campo de ordenamiento
public string $sortDirection = 'desc';   // Direccion de orden
public int $perPage = 10;                // Registros por pagina
public ?int $deleteId = null;            // ID para confirmacion
public bool $confirmingDeletion = false; // Estado modal de confirmacion
public string $filterStatus = 'all';     // Filtro de estado
```

**Metodos:**

1. `updatingSearch()`: Resetea paginacion al buscar
2. `updatingFilterStatus()`: Resetea paginacion al filtrar
3. `sortBy(string $field)`: Toggle de ordenamiento
4. `confirmDeletion(int $id)`: Abre modal de confirmacion
5. `delete()`: Ejecuta soft delete
6. `toggleActive(int $id)`: Activa/desactiva estandar
7. `render()`: Renderiza vista con datos

**Query de Renderizado:**
```php
$query = Standard::with(['part', 'workTable', 'semiAutoWorkTable', 'machine'])
    ->search($this->search);

if ($this->filterStatus === 'active') {
    $query->active();
} elseif ($this->filterStatus === 'inactive') {
    $query->inactive();
}

$standards = $query->orderBy($this->sortField, $this->sortDirection)
    ->paginate($this->perPage);
```

**Eager Loading:** Carga todas las relaciones para evitar N+1.

### 12.3 `StandardCreate` - Creacion de Estandares

**Ubicacion:** `app/Livewire/Admin/Standards/StandardCreate.php`

**Propiedades de Formulario:**
```php
public ?int $part_id = null;
public ?int $work_table_id = null;
public ?int $semi_auto_work_table_id = null;
public ?int $machine_id = null;
public string $persons_1 = '';
public string $persons_2 = '';
public string $persons_3 = '';
public string $effective_date = '';
public bool $active = true;
public string $description = '';
```

**Validacion:**
- `part_id`: required, exists en parts
- Estaciones de trabajo: nullable, exists
- persons_*: nullable, integer, min:1
- effective_date: nullable, date
- active: boolean
- description: nullable, string

**Valores por Defecto:**
- `effective_date` se setea a fecha actual en `mount()`
- `active` es `true` por defecto

**Datos Cargados en Vista:**
```php
'parts' => Part::orderBy('number')->get(),
'workTables' => Table::active()->orderBy('number')->get(),
'semiAutoWorkTables' => Semi_Automatic::active()->orderBy('number')->get(),
'machines' => Machine::active()->orderBy('name')->get(),
```

**PROBLEMA:** No hay validacion que impida seleccionar multiples estaciones simultaneamente.

### 12.4 `StandardEdit` - Edicion de Estandares

**Ubicacion:** `app/Livewire/Admin/Standards/StandardEdit.php`

**Estructura:** Similar a StandardCreate pero:
- Recibe `Standard $standard` por route model binding
- Metodo `mount()` carga datos existentes en propiedades
- Usa `update()` en lugar de `create()`

**Conversion de Tipos en Mount:**
```php
$this->persons_1 = $standard->persons_1 ? (string) $standard->persons_1 : '';
```

Convierte integers a strings para binding de inputs.

### 12.5 `StandardShow` - Vista de Detalles

**Ubicacion:** `app/Livewire/Admin/Standards/StandardShow.php`

**Propiedades:**
```php
public Standard $standard;
public bool $is_current = false;
```

**Metodo `calculateInfo()`:**
```php
protected function calculateInfo(): void
{
    $this->is_current = $this->standard->active &&
                       $this->standard->effective_date &&
                       $this->standard->effective_date->lte(now());
}
```

Calcula si el estandar esta "vigente" (activo Y con fecha efectiva <= hoy).

**Acciones:**
- `toggleActive()`: Cambiar estado activo/inactivo
- `delete()`: Eliminar estandar (soft delete)

---

## 13. Rutas

```
GET|HEAD  admin/standards ................... admin.standards.index
GET|HEAD  admin/standards/create ........... admin.standards.create
GET|HEAD  admin/standards/{standard} ....... admin.standards.show
GET|HEAD  admin/standards/{standard}/edit .. admin.standards.edit
```

**Patron:** Rutas RESTful standard

**Middleware:** Presumiblemente `auth` y `verified` (no visible en output de route:list)

**Binding:** Route model binding automatico de `{standard}`

---

## 14. Vistas Blade (No Analizadas en Detalle)

**Ubicaciones:**
```
resources/views/admin/standards/index.blade.php
resources/views/livewire/admin/standards/standard-list.blade.php
resources/views/livewire/admin/standards/standard-create.blade.php
resources/views/livewire/admin/standards/standard-edit.blade.php
resources/views/livewire/admin/standards/standard-show.blade.php
```

**Observacion:** Existe separacion entre vista principal (`index.blade.php`) y componentes Livewire.

---

## 15. Analisis de Integridad Referencial

### 15.1 Relaciones ON DELETE

| Relacion | Accion ON DELETE | Impacto |
|----------|------------------|---------|
| `part_id` | CASCADE | Eliminar parte elimina todos sus estandares |
| `work_table_id` | SET NULL | Eliminar mesa setea campo a NULL |
| `semi_auto_work_table_id` | SET NULL | Eliminar mesa setea campo a NULL |
| `machine_id` | SET NULL | Eliminar maquina setea campo a NULL |

### 15.2 Riesgos de Integridad

**Riesgo 1: Estandares sin Estacion**

Si se elimina una estacion de trabajo y ese era el unico `work_table_id/semi_auto_work_table_id/machine_id` del estandar, el estandar queda sin estacion asignada.

**Mitigacion:** El metodo `getWorkstation()` retorna NULL en este caso, pero no hay validacion que prevenga este estado.

**Riesgo 2: Part CASCADE Delete**

Eliminar una Part elimina TODOS sus estandares. Esto puede ser destructivo.

**Mitigacion:** Se usa soft delete en Standards, asi que se mantiene historial.

---

## 16. Problemas y Gaps Identificados

### 16.1 Validacion de Exclusividad de Workstation

**Problema:** No existe validacion que impida tener multiples estaciones asignadas simultaneamente.

**Severidad:** ALTA

**Impacto:**
- Datos inconsistentes
- Logica de negocio ambigua
- Metodo `getWorkstation()` solo retorna la primera, ignorando otras

**Solucion Propuesta:**
```php
// En StandardCreate y StandardEdit
protected function validateWorkstationExclusivity()
{
    $assigned = collect([
        $this->work_table_id,
        $this->semi_auto_work_table_id,
        $this->machine_id
    ])->filter()->count();

    if ($assigned > 1) {
        throw ValidationException::withMessages([
            'workstation' => 'Solo puede asignar UNA estacion de trabajo (Manual, Semi-Automatica o Maquina)'
        ]);
    }
}
```

### 16.2 Falta de Relacion Inversa en Part

**Problema:** Part no tiene `standards()` relationship.

**Severidad:** MEDIA

**Impacto:**
- No se puede usar `$part->standards`
- Eager loading complicado
- Queries N+1 potenciales

**Solucion:** Agregar a `Part.php`:
```php
public function standards(): HasMany
{
    return $this->hasMany(Standard::class);
}
```

### 16.3 Factory Genera Datos Invalidos

**Problema:** `StandardFactory` puede generar multiples estaciones asignadas simultaneamente.

**Severidad:** MEDIA

**Impacto:**
- Datos de prueba inconsistentes
- Tests fallan o pasan con datos invalidos

**Solucion:**
```php
public function definition(): array
{
    $mode = $this->faker->randomElement(['manual', 'semi_auto', 'machine', null]);

    return [
        'part_id' => Part::factory(),
        'work_table_id' => $mode === 'manual' ? Table::inRandomOrder()->first()?->id : null,
        'semi_auto_work_table_id' => $mode === 'semi_auto' ? Semi_Automatic::inRandomOrder()->first()?->id : null,
        'machine_id' => $mode === 'machine' ? Machine::inRandomOrder()->first()?->id : null,
        'units_per_hour' => $this->faker->numberBetween(10, 500),
        // ... resto
    ];
}
```

### 16.4 Units Per Hour Faltante en Factory

**Problema:** Factory no genera `units_per_hour`, usa default de 1.

**Severidad:** BAJA

**Impacto:** Datos de prueba poco realistas.

**Solucion:** Incluida en 16.3.

### 16.5 Scope Search con Performance Issues

**Problema:** `scopeSearch()` usa multiples `whereHas` que generan subqueries.

**Severidad:** BAJA (en datasets pequeños), ALTA (en datasets grandes)

**Impacto:** Lentitud en busquedas con miles de registros.

**Solucion:** Usar joins en lugar de whereHas o implementar full-text search.

### 16.6 Falta Validacion de units_per_hour > 0

**Problema:** El metodo `calculateRequiredHours()` lanza excepcion si `units_per_hour = 0`, pero no hay validacion en formulario.

**Severidad:** MEDIA

**Impacto:** Error en runtime en lugar de validacion de formulario.

**Solucion:** Agregar regla de validacion:
```php
'units_per_hour' => 'required|integer|min:1',
```

### 16.7 Controlador Vacio

**Problema:** `StandardController` existe pero esta vacio.

**Severidad:** BAJA

**Impacto:** Archivo innecesario en codebase.

**Solucion:** Eliminar archivo o documentar que se usa Livewire.

---

## 17. Metricas del Modulo

| Metrica | Valor |
|---------|-------|
| Lineas de codigo del Modelo | 284 |
| Numero de relaciones | 4 (belongsTo) |
| Numero de scopes | 4 |
| Numero de accessors | 4 |
| Numero de metodos publicos | 11 |
| Numero de componentes Livewire | 4 |
| Numero de migraciones | 2 |
| Numero de indices DB | 7 |
| Campos en tabla | 16 (incluyendo timestamps) |

---

## 18. Dependencias del Modulo

### 18.1 Modelos Relacionados

- `App\Models\Part` (OBLIGATORIO)
- `App\Models\Table` (OPCIONAL)
- `App\Models\Semi_Automatic` (OPCIONAL)
- `App\Models\Machine` (OPCIONAL)

### 18.2 Tablas de Base de Datos

- `standards` (principal)
- `parts` (foreign key)
- `tables` (foreign key)
- `semi__automatics` (foreign key)
- `machines` (foreign key)

### 18.3 Paquetes Laravel

- `Illuminate\Database\Eloquent\Model`
- `Illuminate\Database\Eloquent\SoftDeletes`
- `Illuminate\Database\Eloquent\Factories\HasFactory`
- `Livewire\Component`
- `Livewire\WithPagination`

---

## 19. Casos de Uso del Modulo

### 19.1 Caso de Uso 1: Definir Estandar de Produccion

**Actor:** Production Manager

**Flujo:**
1. Accede a admin/standards/create
2. Selecciona Part
3. Selecciona UNA estacion de trabajo (Table, Semi_Automatic o Machine)
4. Define capacidad (`units_per_hour`)
5. Define configuracion de personal (persons_1, persons_2, persons_3)
6. Establece fecha efectiva
7. Guarda estandar

**Postcondicion:** Estandar creado, activo por defecto.

### 19.2 Caso de Uso 2: Calcular Tiempo de Produccion

**Actor:** Production Planner

**Flujo:**
1. Obtiene estandar de una parte: `$standard = Part::find($partId)->standards()->active()->first()`
2. Calcula horas requeridas: `$hours = $standard->calculateRequiredHours($quantity)`
3. Usa calculo para Work Order planning

**Formula:** `horas = cantidad / units_per_hour`

### 19.3 Caso de Uso 3: Buscar Estandares

**Actor:** Usuario del sistema

**Flujo:**
1. Accede a admin/standards
2. Ingresa termino de busqueda
3. Sistema busca en:
   - Descripcion del estandar
   - Numero de parte
   - Numero de mesa/maquina
   - Marca/modelo de maquina

**Resultado:** Lista filtrada de estandares.

### 19.4 Caso de Uso 4: Obtener Estadisticas

**Actor:** Dashboard/Reporting

**Flujo:**
```php
$stats = Standard::getStats();
// Retorna: ['total' => 100, 'active' => 80, 'inactive' => 20, 'current' => 75]
```

---

## 20. Diagrama Entidad-Relacion (ERD)

```
┌─────────────────────────────────────┐
│             PARTS                   │
│─────────────────────────────────────│
│ id (PK)                             │
│ number                              │
│ item_number                         │
│ description                         │
│ active                              │
└──────────────┬──────────────────────┘
               │
               │ 1:N
               │
┌──────────────┴──────────────────────┐
│           STANDARDS                 │
│─────────────────────────────────────│
│ id (PK)                             │
│ part_id (FK) ────────────┐          │
│                          │          │
│ work_table_id (FK) ──────┼────┐     │
│ semi_auto_work_table_id ─┼────┼─┐   │
│ machine_id (FK) ─────────┼────┼─┼─┐ │
│                          │    │ │ │ │
│ units_per_hour          │    │ │ │ │
│ persons_1               │    │ │ │ │
│ persons_2               │    │ │ │ │
│ persons_3               │    │ │ │ │
│ effective_date          │    │ │ │ │
│ active                  │    │ │ │ │
│ description             │    │ │ │ │
└─────────────────────────┼────┼─┼─┼─┘
                          │    │ │ │
                          ▼    ▼ ▼ ▼
              ┌──────────────────────────┐
              │        TABLES            │
              │──────────────────────────│
              │ id (PK)                  │
              │ number                   │
              │ employees                │
              │ active                   │
              │ area_id                  │
              └──────────────────────────┘

              ┌──────────────────────────┐
              │   SEMI__AUTOMATICS       │
              │──────────────────────────│
              │ id (PK)                  │
              │ number                   │
              │ employees                │
              │ active                   │
              │ area_id                  │
              └──────────────────────────┘

              ┌──────────────────────────┐
              │       MACHINES           │
              │──────────────────────────│
              │ id (PK)                  │
              │ name                     │
              │ brand                    │
              │ model                    │
              │ active                   │
              │ area_id                  │
              └──────────────────────────┘
```

---

## 21. Patrones de Diseño Aplicados

### 21.1 Repository Pattern (Implicito)

Eloquent actua como Repository Pattern, abstrayendo acceso a datos.

### 21.2 Factory Pattern

`StandardFactory` implementa Factory Pattern para generacion de datos de prueba.

### 21.3 Scope Pattern

Los Query Scopes (`active()`, `search()`, etc.) implementan patron de filtrado reutilizable.

### 21.4 Soft Delete Pattern

Implementacion de eliminacion logica para mantener historial.

### 21.5 Accessor/Mutator Pattern

Uso de accessors para propiedades calculadas (`status_label`, `assembly_mode`, `workstation_name`).

---

## 22. Consideraciones de Seguridad

### 22.1 Mass Assignment Protection

Uso de `$fillable` protege contra mass assignment vulnerabilities.

### 22.2 SQL Injection Protection

Uso de Eloquent y Query Builder protege contra SQL injection.

### 22.3 Authorization (No Implementada)

**Observacion:** No se evidencia uso de Policies o Gates para autorizar acciones sobre Standards.

**Recomendacion:** Implementar `StandardPolicy` con metodos:
- `viewAny()`
- `view()`
- `create()`
- `update()`
- `delete()`

---

## 23. Consideraciones de Performance

### 23.1 Eager Loading en Listados

StandardList usa eager loading correctamente:
```php
Standard::with(['part', 'workTable', 'semiAutoWorkTable', 'machine'])
```

Esto previene N+1 queries.

### 23.2 Indices de Base de Datos

Indices bien diseñados para queries frecuentes:
- Busqueda por estacion de trabajo
- Filtrado por estado
- Ordenamiento por fecha efectiva

### 23.3 Paginacion

Uso de `paginate()` en listados previene carga de todos los registros.

### 23.4 Area de Mejora: Search Scope

El scope `search()` puede ser optimizado con full-text indexes en MySQL:

```sql
ALTER TABLE standards ADD FULLTEXT INDEX search_idx (description);
```

---

## 24. Testing Considerations

### 24.1 Unit Tests Recomendados

```php
// StandardTest.php
test('calculates required hours correctly')
test('throws exception when units per hour is zero')
test('returns correct assembly mode')
test('returns correct workstation')
test('can be soft deleted')
test('scopes filter correctly')
```

### 24.2 Feature Tests Recomendados

```php
// StandardCRUDTest.php
test('can create standard')
test('can update standard')
test('can delete standard')
test('can search standards')
test('can toggle active status')
test('validates required fields')
test('prevents multiple workstations assignment')
```

### 24.3 Tests Faltantes

No se evidencian tests en el codebase analizado.

---

## 25. Documentacion y Comentarios

### 25.1 Calidad de Documentacion

**Positivo:**
- Docblocks en metodos publicos
- Comentarios de seccion en codigo
- Separacion clara de responsabilidades

**Negativo:**
- Falta documentacion de reglas de negocio
- No hay diagramas de flujo
- Comentarios de migracion podrian ser mas descriptivos

### 25.2 Nomenclatura

**Positivo:**
- Nombres descriptivos de metodos
- Consistencia en naming conventions
- Uso de verbos claros (calculate, get, toggle)

**Observacion:** Uso de `persons_1`, `persons_2`, `persons_3` no es muy descriptivo. Podria ser `persons_capacity_scenario_1`, etc.

---

## 26. Roadmap de Mejoras Sugeridas

### 26.1 Prioridad ALTA

1. Implementar validacion de exclusividad de workstation
2. Agregar validacion `units_per_hour > 0` en formularios
3. Agregar relacion inversa `standards()` en modelo Part
4. Corregir StandardFactory para generar datos validos

### 26.2 Prioridad MEDIA

5. Implementar StandardPolicy para autorizacion
6. Optimizar scope `search()` con full-text indexes
7. Agregar tests unitarios y de feature
8. Documentar reglas de negocio

### 26.3 Prioridad BAJA

9. Mejorar nomenclatura de `persons_1/2/3`
10. Eliminar StandardController vacio
11. Agregar check constraint en BD para exclusividad de workstation
12. Implementar versionado de estandares (si es requerimiento futuro)

---

## 27. Conclusiones

### 27.1 Fortalezas del Modulo

1. **Arquitectura Limpia:** Separacion clara de responsabilidades usando Livewire
2. **Soft Deletes:** Mantiene historial de cambios
3. **Indices Optimizados:** Buena estrategia de indexacion
4. **Eager Loading:** Prevencion de N+1 queries
5. **Scopes Reutilizables:** Query scopes bien diseñados
6. **Metodos Helper:** Logica de negocio bien encapsulada

### 27.2 Debilidades Criticas

1. **Falta Validacion de Exclusividad:** Puede haber multiples workstations asignadas
2. **Factory Genera Datos Invalidos:** Viola regla de negocio
3. **Sin Autorizacion:** No hay policies implementadas
4. **Sin Tests:** Falta cobertura de tests
5. **Performance en Search:** Puede ser lento en datasets grandes

### 27.3 Estado General

El modulo Standards esta **funcionalmente completo** pero requiere **refactoring y hardening** para produccion. La estructura es solida pero necesita:
- Validaciones mas estrictas
- Tests comprehensivos
- Autorizacion implementada
- Optimizaciones de performance

**Puntuacion:** 7/10
- Funcionalidad: 9/10
- Calidad de Codigo: 8/10
- Testing: 0/10
- Seguridad: 6/10
- Performance: 7/10
- Documentacion: 7/10

---

## 28. Referencias

- Laravel Documentation: https://laravel.com/docs
- Livewire Documentation: https://livewire.laravel.com/docs
- Eloquent ORM: https://laravel.com/docs/eloquent
- Database Indexes: https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html

---

**Fin del Analisis Tecnico**

Documento generado por Agent Architect
Flexcon-Tracker Project
Version 1.0 - 2025-12-22
