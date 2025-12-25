# Análisis Técnico: Error en ProductionStatus Show

## Problema

Al hacer clic en la vista "show" de Production Statuses, se genera el siguiente error SQL:

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'productions.production_status_id'
in 'where clause' (Connection: mysql, SQL: select * from `productions`
where `productions`.`production_status_id` in (1))
```

## Diagnóstico del Problema

### 1. Relación Incorrecta en el Modelo ProductionStatus

**Archivo:** `app/Models/ProductionStatus.php`

**Líneas 42-45:**
```php
public function productions()
{
    return $this->hasMany(Production::class);
}
```

**Problema Identificado:**
- La relación `productions()` asume que existe una columna `production_status_id` en la tabla `productions`
- Laravel automáticamente busca `{model_singular}_id` cuando se define un `hasMany()`

### 2. Estructura Real de la Tabla `productions`

**Archivo:** `database/migrations/2025_12_16_042057_create_productions_table.php`

```php
Schema::create('productions', function (Blueprint $table) {
    $table->id();
    $table->string('number')->unique();
    $table->timestamps();
});
```

**Hallazgos:**
- La tabla `productions` NO tiene una columna `production_status_id`
- Solo contiene: `id`, `number`, `created_at`, `updated_at`
- El modelo `Production.php` está prácticamente vacío (solo tiene HasFactory)

### 3. Relaciones Correctas Identificadas

Según la arquitectura del sistema, las tablas que SÍ tienen relación con `ProductionStatus` son:

#### a) Tabla `tables`
**Modelo:** `app/Models/Table.php` (línea 28)
```php
protected $fillable = [
    // ...
    'production_status_id',
    'standard_id',
];
```

**Relación en Table.php (línea 57-60):**
```php
public function productionStatus()
{
    return $this->belongsTo(ProductionStatus::class);
}
```

#### b) Tabla `semi__automatics`
**Modelo:** `app/Models/Semi_Automatic.php`
- NO tiene `production_status_id` en el fillable
- NO tiene relación con ProductionStatus
- **NECESITA ser agregada**

#### c) Tabla `machines`
**Modelo:** `app/Models/Machine.php`
- NO tiene `production_status_id` en el fillable
- NO tiene relación con ProductionStatus
- **NECESITA ser agregada**

### 4. Uso en la Vista

**Archivo:** `app/Livewire/Admin/ProductionStatuses/ProductionStatusShow.php` (línea 14)

```php
$this->productionStatus = $productionStatus->load(['tables', 'productions']);
```

**Archivo:** `resources/views/livewire/admin/production-statuses/production-status-show.blade.php` (líneas 97, 102)

```php
<!-- Mesas con este estado -->
<dd>{{ $productionStatus->tables->count() }}</dd>

<!-- Producciones con este estado -->
<dd>{{ $productionStatus->productions->count() }}</dd>
```

**Problema:**
- Se está intentando cargar la relación `productions` que está mal definida
- La vista intenta mostrar el conteo de `productions` relacionadas

## Impacto Arquitectural

### Backend
- **Modelo ProductionStatus:** Relación `productions()` incorrecta
- **Modelo Production:** Incompleto, sin relaciones ni estructura
- **Modelos Semi_Automatic y Machine:** Falta agregar relación con ProductionStatus

### Frontend
- **Componente ProductionStatusShow:** Intenta cargar relación inexistente
- **Vista production-status-show.blade.php:** Muestra datos de relación incorrecta

### Base de Datos
- Tabla `productions` no tiene foreign key `production_status_id`
- Tablas `semi__automatics` y `machines` necesitan columna `production_status_id`

## Propuesta de Solución

### Opción A: Eliminar la Relación `productions` (RECOMENDADA)

Esta opción asume que la tabla `productions` no debería tener relación con `ProductionStatus`, ya que su estructura actual es muy básica y parece ser un stub/placeholder.

**Justificación:**
- La tabla `productions` está prácticamente vacía
- No existe evidencia de que necesite un estado de producción
- Las entidades reales de producción son: `tables`, `semi__automatics`, y `machines`

#### Cambios requeridos:

**1. Eliminar relación del Modelo ProductionStatus**

```php
// ELIMINAR líneas 42-45
public function productions()
{
    return $this->hasMany(Production::class);
}
```

**2. Agregar relaciones correctas para Semi_Automatic y Machine**

**En ProductionStatus.php:**
```php
/**
 * Estado de producción tiene múltiples semi-automáticos
 */
public function semiAutomatics()
{
    return $this->hasMany(Semi_Automatic::class);
}

/**
 * Estado de producción tiene múltiples máquinas
 */
public function machines()
{
    return $this->hasMany(Machine::class);
}
```

**3. Actualizar método canBeDeleted() en ProductionStatus**

```php
public function canBeDeleted()
{
    return $this->tables()->count() === 0
        && $this->semiAutomatics()->count() === 0
        && $this->machines()->count() === 0;
}
```

**4. Crear migraciones para agregar production_status_id**

**Migración para semi__automatics:**
```php
Schema::table('semi__automatics', function (Blueprint $table) {
    $table->foreignId('production_status_id')
          ->nullable()
          ->after('area_id')
          ->constrained('production_statuses')
          ->nullOnDelete();
});
```

**Migración para machines:**
```php
Schema::table('machines', function (Blueprint $table) {
    $table->foreignId('production_status_id')
          ->nullable()
          ->after('area_id')
          ->constrained('production_statuses')
          ->nullOnDelete();
});
```

**5. Actualizar modelos Semi_Automatic y Machine**

**En Semi_Automatic.php:**
```php
protected $fillable = [
    'number',
    'employees',
    'active',
    'comments',
    'area_id',
    'production_status_id', // AGREGAR
];

protected $casts = [
    'active' => 'boolean',
    'employees' => 'integer',
    'production_status_id' => 'integer', // AGREGAR
];

// AGREGAR relación
public function productionStatus()
{
    return $this->belongsTo(ProductionStatus::class);
}
```

**En Machine.php:**
```php
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
    'production_status_id', // AGREGAR
];

protected $casts = [
    'active' => 'boolean',
    'setup_time' => 'decimal:2',
    'maintenance_time' => 'decimal:2',
    'employees' => 'integer',
    'production_status_id' => 'integer', // AGREGAR
];

// AGREGAR relación
public function productionStatus()
{
    return $this->belongsTo(ProductionStatus::class);
}
```

**6. Actualizar componente ProductionStatusShow**

```php
public function mount(ProductionStatus $productionStatus): void
{
    $this->productionStatus = $productionStatus->load([
        'tables',
        'semiAutomatics',
        'machines'
    ]);
}
```

**7. Actualizar vista production-status-show.blade.php**

```blade
<!-- Usage Statistics -->
<div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Uso del Estado</h3>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mesas con este estado</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $productionStatus->tables->count() }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Semi-automáticos con este estado</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $productionStatus->semiAutomatics->count() }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Máquinas con este estado</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $productionStatus->machines->count() }}</dd>
            </div>
        </dl>
    </div>
</div>
```

### Opción B: Agregar production_status_id a productions (NO RECOMENDADA)

Esta opción solo sería válida si la tabla `productions` realmente necesita tener estados de producción.

**Problema:**
- No hay evidencia de que `productions` sea una entidad de producción real
- El modelo está vacío
- No hay CRUD implementado
- Duplicaría funcionalidad con tables, semi_automatics, machines

## Plan de Implementación (Opción A - Recomendada)

### Paso 1: Crear Migraciones
```bash
php artisan make:migration add_production_status_id_to_semi_automatics_table --table=semi__automatics
php artisan make:migration add_production_status_id_to_machines_table --table=machines
```

### Paso 2: Actualizar Modelo ProductionStatus
1. Eliminar relación `productions()`
2. Agregar relación `semiAutomatics()`
3. Agregar relación `machines()`
4. Actualizar método `canBeDeleted()`

### Paso 3: Actualizar Modelos Semi_Automatic y Machine
1. Agregar `production_status_id` a fillable
2. Agregar cast para `production_status_id`
3. Agregar relación `productionStatus()`

### Paso 4: Actualizar Componente ProductionStatusShow
1. Cambiar `load(['tables', 'productions'])` a `load(['tables', 'semiAutomatics', 'machines'])`

### Paso 5: Actualizar Vista Blade
1. Reemplazar sección de "Producciones" con "Semi-automáticos" y "Máquinas"
2. Cambiar de grid-cols-2 a grid-cols-3

### Paso 6: Ejecutar Migraciones
```bash
php artisan migrate
```

### Paso 7: Probar
1. Acceder a vista show de un ProductionStatus
2. Verificar que no hay errores SQL
3. Verificar que se muestran correctamente los conteos

## Archivos Afectados

```
app/Models/ProductionStatus.php          [MODIFICAR]
app/Models/Semi_Automatic.php            [MODIFICAR]
app/Models/Machine.php                   [MODIFICAR]
app/Livewire/Admin/ProductionStatuses/ProductionStatusShow.php  [MODIFICAR]
resources/views/livewire/admin/production-statuses/production-status-show.blade.php  [MODIFICAR]
database/migrations/YYYY_MM_DD_add_production_status_id_to_semi_automatics_table.php  [CREAR]
database/migrations/YYYY_MM_DD_add_production_status_id_to_machines_table.php  [CREAR]
```

## Consideraciones Adicionales

### 1. ¿Qué hacer con la tabla `productions`?
- **Opción 1:** Eliminarla si no se usa (requiere eliminar migración y modelo)
- **Opción 2:** Dejarla para uso futuro (documentar su propósito)
- **Opción 3:** Redefinir su estructura para un propósito específico

### 2. Actualización de CRUDs existentes
- Verificar que los CRUDs de Tables, Semi_Automatics y Machines incluyan el campo `production_status_id`
- Agregar selección de ProductionStatus en formularios de creación/edición

### 3. Seeders
- Actualizar seeders existentes para incluir `production_status_id` en datos de prueba

### 4. Validaciones
- Agregar validaciones en los componentes Livewire para `production_status_id`
- El campo debería ser opcional (nullable) para no romper registros existentes

## Conclusión

El error se debe a una relación mal definida en el modelo `ProductionStatus`. La tabla `productions` no tiene (ni debería tener) una columna `production_status_id`.

La solución recomendada es:
1. Eliminar la relación incorrecta con `productions`
2. Agregar relaciones correctas con `semi_automatics` y `machines`
3. Crear migraciones para agregar `production_status_id` a las tablas correctas
4. Actualizar los modelos y vistas correspondientes

Esta solución mantiene la consistencia arquitectural del sistema y corrige el error sin introducir complejidad innecesaria.
