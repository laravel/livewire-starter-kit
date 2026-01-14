# Spec 11: Employee CRUD Architecture & Shift Integration

**Fecha**: 2025-12-27
**Modulo**: Employees Management (CRUD completo)
**Fase**: FASE 2 - Post Production Module
**Prioridad**: ALTA
**Estado**: PENDING IMPLEMENTATION

---

## Resumen Ejecutivo

Este documento define la arquitectura completa para implementar el CRUD de Empleados (Employees) en el sistema Flexcon-Tracker. Actualmente existe la estructura base (migración, modelo, controller, factory, seeder) pero NO está completamente implementada. El objetivo es crear un módulo completo y funcional que gestione empleados y su relación con turnos (Shifts), áreas (Areas) y el módulo de producción.

**Problema identificado**:
- La vista `shift-show.blade.php` hace referencia a una relación `$shift->Employees` que existe pero está comentada.
- El modelo `Employee` existe pero está vacío (sin fillable, casts, relaciones, scopes).
- No existen componentes Livewire para el CRUD de empleados.
- La relación Shift ↔ Employee está comentada en el modelo Shift.

**Solución propuesta**:
Implementar un módulo completo de gestión de empleados siguiendo los patrones arquitectónicos ya establecidos en el proyecto (Clean Architecture, Livewire 3.x, Volt components).

---

## 1. Estado Actual del Sistema

### 1.1 Componentes Existentes

#### Base de Datos
- **Migración**: `2025_12_01_051656_create_employees_table.php` (EJECUTADA)
- **Estado**: Migración ejecutada correctamente
- **Tabla**: `employees` existe en la base de datos

**Estructura de la tabla `employees`**:
```php
Schema::create('employees', function (Blueprint $table) {
    $table->id();

    // Información básica
    $table->string('name');                      // Nombre
    $table->string('last_name');                 // Apellido
    $table->string('email')->unique();           // Email único
    $table->string('password');                  // Contraseña
    $table->string('number')->unique();          // Número de empleado (único)
    $table->string('position')->nullable();      // Puesto/cargo
    $table->date('birth_date')->nullable();      // Fecha de nacimiento
    $table->date('entry_date')->nullable();      // Fecha de ingreso

    // Estado
    $table->tinyInteger('active')->default(1)
          ->comment('1: Activo, 0: Inactivo');
    $table->string('comments')->nullable();      // Comentarios

    // Relaciones
    $table->foreignId('area_id')
          ->constrained('areas')
          ->onDelete('cascade');                 // FK a areas
    $table->foreignId('shift_id')
          ->constrained('shifts')
          ->onDelete('cascade');                 // FK a shifts

    // Timestamps y soft deletes
    $table->softDeletes();
    $table->timestamps();
});
```

#### Modelos

**Employee Model** (`app/Models/Employee.php`):
```php
// ESTADO ACTUAL: VACÍO (solo tiene HasFactory)
class Employee extends Model
{
    use HasFactory;
    // NO tiene fillable
    // NO tiene casts
    // NO tiene relaciones
    // NO tiene scopes
    // NO tiene métodos auxiliares
}
```

**Shift Model** (`app/Models/Shift.php`):
```php
// Relación COMENTADA:
/* public function Employees(): HasMany
{
    return $this->hasMany(Employee::class);
} */

// Métodos que dependen de Employee COMENTADOS:
/* public function getStats()
{
    return [
        'total_employees' => $this->Employees()->count(),
        'active_employees' => $this->Employees()->where('active', true)->count(),
    ];
} */
```

**Area Model** (`app/Models/Area.php`):
```php
// NO tiene relación con Employee definida
// Debería tener: hasMany(Employee::class)
```

#### Controllers

**EmployeeController** (`app/Http\Controllers\EmployeeController.php`):
```php
// ESTADO: BÁSICO, solo retorna vistas
class EmployeeController extends Controller
{
    public function index() {
        return view('employees.index');
    }

    public function create() {
        return view('employees.create');
    }

    public function show(employee $employee) {  // BUG: lowercase 'employee'
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee) {
        return view('employees.edit', compact('employee'));
    }

    // Falta: store(), update(), destroy()
    // NO usa Livewire
}
```

**PROBLEMA**: El controller está configurado para vistas tradicionales de Blade, NO para Livewire.

#### Factory y Seeder

**EmployeeFactory** (`database/factories/EmployeeFactory.php`):
```php
// VACÍO - no tiene definición de datos
public function definition(): array
{
    return [
        // vacío
    ];
}
```

**EmployeeSeeder** (`database/seeders/EmployeeSeeder.php`):
```php
// VACÍO
public function run(): void
{
    // vacío
}
```

#### Vistas
- **Directory**: `resources/views/employees` → NO EXISTE
- **Livewire Components**: NO EXISTEN
- **Volt Components**: NO EXISTEN

#### Rutas
- **File**: `routes/employee.php` → Existe pero es para el PANEL de empleados (dashboard), NO para gestión admin de empleados
- **Admin routes**: NO EXISTEN rutas para admin.employees.*

### 1.2 Dependencias del Sistema

**Relaciones con otros módulos**:

1. **Shifts (Turnos)**:
   - Relación: `Employee belongsTo Shift` (1:N)
   - Shift tiene hasMany Employees
   - Un empleado pertenece a UN turno
   - Un turno puede tener MUCHOS empleados

2. **Areas**:
   - Relación: `Employee belongsTo Area` (1:N)
   - Area tiene hasMany Employees
   - Un empleado pertenece a UN área
   - Un área puede tener MUCHOS empleados

3. **Production Module**:
   - Relación potencial con `ProductionSession` (futuro)
   - Capacidad de cálculo de producción usa shift y podría usar employee data

4. **Authentication**:
   - Employee tiene email y password
   - Potencial login de empleados (ya existe `routes/employee.php`)

### 1.3 Gaps Identificados

**CRÍTICOS**:
- [ ] Modelo Employee sin implementación (fillable, casts, relaciones)
- [ ] Relación Shift->Employees comentada
- [ ] Relación Area->Employees no existe
- [ ] No existen componentes Livewire para CRUD
- [ ] No existen vistas funcionales
- [ ] Factory y Seeder vacíos
- [ ] No hay validaciones (FormRequests)
- [ ] No hay tests
- [ ] EmployeeController con bug tipográfico (lowercase 'employee')

**IMPORTANTES**:
- [ ] Faltan rutas admin para employees CRUD
- [ ] Falta integración con módulo de producción
- [ ] Falta autorización y políticas
- [ ] Falta manejo de cambios de turno
- [ ] Falta auditoría de cambios

---

## 2. Análisis de Relaciones

### 2.1 Relación Shift ↔ Employee

**Tipo**: One-to-Many (1:N)
**Cardinalidad**: Un turno tiene muchos empleados, un empleado pertenece a un turno

```
┌──────────────┐         ┌────────────────┐
│   Shifts     │1      N │   Employees    │
│──────────────│◄────────│────────────────│
│ id           │         │ id             │
│ name         │         │ name           │
│ start_time   │         │ shift_id (FK)  │
│ end_time     │         │ area_id (FK)   │
│ active       │         │ active         │
└──────────────┘         └────────────────┘
```

**Modelo Shift**:
```php
public function employees(): HasMany
{
    return $this->hasMany(Employee::class);
}

// Scope: empleados activos en el turno
public function activeEmployees(): HasMany
{
    return $this->employees()->where('active', 1);
}

// Stats del turno
public function getStats(): array
{
    return [
        'total_employees' => $this->employees()->count(),
        'active_employees' => $this->activeEmployees()->count(),
    ];
}
```

**Modelo Employee**:
```php
public function shift(): BelongsTo
{
    return $this->belongsTo(Shift::class);
}
```

### 2.2 Relación Area ↔ Employee

**Tipo**: One-to-Many (1:N)
**Cardinalidad**: Un área tiene muchos empleados, un empleado pertenece a un área

```
┌──────────────┐         ┌────────────────┐
│    Areas     │1      N │   Employees    │
│──────────────│◄────────│────────────────│
│ id           │         │ id             │
│ name         │         │ name           │
│ description  │         │ shift_id (FK)  │
│ user_id      │         │ area_id (FK)   │
└──────────────┘         └────────────────┘
```

**Modelo Area**:
```php
public function employees(): HasMany
{
    return $this->hasMany(Employee::class);
}

public function activeEmployees(): HasMany
{
    return $this->employees()->where('active', 1);
}
```

**Modelo Employee**:
```php
public function area(): BelongsTo
{
    return $this->belongsTo(Area::class);
}
```

### 2.3 Validaciones de Integridad

**Reglas de negocio**:

1. **Un empleado DEBE tener**:
   - Un turno asignado (shift_id NOT NULL)
   - Un área asignada (area_id NOT NULL)
   - Email único en el sistema
   - Número de empleado único

2. **Cambios de turno**:
   - Un empleado puede cambiar de turno
   - Auditar cambios de turno (historial)
   - Validar que el nuevo turno existe y está activo

3. **Eliminación en cascada**:
   - Si se elimina un Shift con empleados → ERROR (no permitir)
   - Si se elimina un Area con empleados → ERROR (no permitir)
   - Usar soft deletes en Employee

4. **Estados**:
   - `active = 1`: Empleado activo (puede trabajar)
   - `active = 0`: Empleado inactivo (no cuenta en capacidad)

---

## 3. Diseño de Base de Datos

### 3.1 Tabla Employees (Existente)

**Estado**: Ya creada y migrada correctamente

**Índices recomendados** (PENDIENTE agregar):
```php
// En una nueva migración: add_indexes_to_employees_table
$table->index('shift_id');      // Para búsquedas por turno
$table->index('area_id');       // Para búsquedas por área
$table->index('active');        // Para filtrar activos/inactivos
$table->index('entry_date');    // Para reportes por fecha de ingreso
$table->index(['shift_id', 'active']); // Compuesto para stats
```

### 3.2 Tabla Employee_Shift_History (NUEVA - OPCIONAL para FASE 3)

**Propósito**: Auditar cambios de turno de empleados

```php
Schema::create('employee_shift_history', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained()->onDelete('cascade');
    $table->foreignId('old_shift_id')->nullable()->constrained('shifts');
    $table->foreignId('new_shift_id')->constrained('shifts');
    $table->foreignId('changed_by_user_id')->constrained('users'); // Quién hizo el cambio
    $table->text('reason')->nullable(); // Razón del cambio
    $table->timestamp('changed_at');
    $table->timestamps();
});
```

**Nota**: Esta tabla es OPCIONAL para una fase futura si se requiere auditoría completa.

---

## 4. Diseño del Modelo Employee

### 4.1 Propiedades del Modelo

**Archivo**: `app/Models/Employee.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    // ===============================================
    // CONFIGURACIÓN DEL MODELO
    // ===============================================

    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'number',
        'position',
        'birth_date',
        'entry_date',
        'active',
        'comments',
        'area_id',
        'shift_id',
    ];

    protected $hidden = [
        'password', // Ocultar en JSON
    ];

    protected $casts = [
        'active' => 'boolean',
        'birth_date' => 'date',
        'entry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'full_name',
        'years_in_company',
    ];

    // ===============================================
    // RELACIONES
    // ===============================================

    /**
     * Un empleado pertenece a un turno
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Un empleado pertenece a un área
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    // ===============================================
    // ACCESSORS
    // ===============================================

    /**
     * Nombre completo del empleado
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->name} {$this->last_name}";
    }

    /**
     * Años en la compañía
     */
    public function getYearsInCompanyAttribute(): ?int
    {
        if (!$this->entry_date) {
            return null;
        }

        return $this->entry_date->diffInYears(now());
    }

    /**
     * Nombre del turno
     */
    public function getShiftNameAttribute(): string
    {
        return $this->shift ? $this->shift->name : 'Sin turno';
    }

    /**
     * Nombre del área
     */
    public function getAreaNameAttribute(): string
    {
        return $this->area ? $this->area->name : 'Sin área';
    }

    // ===============================================
    // MUTATORS
    // ===============================================

    /**
     * Hashear contraseña automáticamente
     */
    public function setPasswordAttribute($value): void
    {
        if ($value) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    // ===============================================
    // SCOPES
    // ===============================================

    /**
     * Solo empleados activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Solo empleados inactivos
     */
    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    /**
     * Empleados por turno
     */
    public function scopeByShift($query, $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    /**
     * Empleados por área
     */
    public function scopeByArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    /**
     * Buscar empleados
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('number', 'like', "%{$search}%")
              ->orWhere('position', 'like', "%{$search}%");
        });
    }

    /**
     * Ordenar por campo
     */
    public function scopeSortByField($query, $field = 'name', $direction = 'asc')
    {
        return $query->orderBy($field, $direction);
    }

    /**
     * Empleados contratados en un rango de fechas
     */
    public function scopeHiredBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    // ===============================================
    // MÉTODOS AUXILIARES
    // ===============================================

    /**
     * Verificar si el empleado se puede eliminar
     */
    public function canBeDeleted(): bool
    {
        // Agregar lógica según dependencias futuras
        // Por ejemplo: si tiene production sessions, no se puede eliminar
        return true;
    }

    /**
     * Cambiar turno del empleado
     */
    public function changeShift(int $newShiftId, ?string $reason = null): bool
    {
        $oldShiftId = $this->shift_id;

        $this->shift_id = $newShiftId;
        $saved = $this->save();

        // TODO: Registrar en employee_shift_history si existe la tabla
        // EmployeeShiftHistory::create([...]);

        return $saved;
    }

    /**
     * Activar empleado
     */
    public function activate(): bool
    {
        $this->active = true;
        return $this->save();
    }

    /**
     * Desactivar empleado
     */
    public function deactivate(): bool
    {
        $this->active = false;
        return $this->save();
    }

    /**
     * Verificar si el empleado está activo
     */
    public function isActive(): bool
    {
        return (bool) $this->active;
    }
}
```

### 4.2 Actualizaciones en Modelos Relacionados

**Shift Model** (descomentar y mejorar):
```php
// En app/Models/Shift.php

public function employees(): HasMany
{
    return $this->hasMany(Employee::class);
}

public function activeEmployees(): HasMany
{
    return $this->employees()->where('active', true);
}

public function getStats(): array
{
    return [
        'total_employees' => $this->employees()->count(),
        'active_employees' => $this->activeEmployees()->count(),
    ];
}

public function getEmployeeStats(): array
{
    return [
        'total_production_sessions' => $this->ProductionSessions()->count(),
        'active_production_sessions' => $this->ProductionSessions()->where('active', true)->count(),
        'total_break_times' => $this->BreakTimes()->count(),
        'active_break_times' => $this->BreakTimes()->where('active', true)->count(),
    ];
}

// Actualizar método canBeDeleted
public function canBeDeleted(): bool
{
    return $this->employees()->count() === 0
        && $this->ProductionSessions()->count() === 0
        && $this->BreakTimes()->count() === 0
        && $this->overTimes()->count() === 0;
}
```

**Area Model** (agregar relación):
```php
// En app/Models/Area.php

/**
 * Un área tiene múltiples empleados
 */
public function employees(): HasMany
{
    return $this->hasMany(Employee::class);
}

public function activeEmployees(): HasMany
{
    return $this->employees()->where('active', true);
}

// Actualizar canBeDeleted
public function canBeDeleted(): bool
{
    return $this->machines()->count() === 0
        && $this->tables()->count() === 0
        && $this->semiAutomatics()->count() === 0
        && $this->employees()->count() === 0;  // AGREGAR ESTA LÍNEA
}

// Actualizar getStats
public function getStats(): array
{
    return [
        'total_machines' => $this->machines()->count(),
        'active_machines' => $this->machines()->where('active', true)->count(),
        'total_tables' => $this->tables()->count(),
        'active_tables' => $this->tables()->where('active', true)->count(),
        'total_semi_automatic' => $this->semiAutomatics()->count(),
        'active_semi_automatic' => $this->semiAutomatics()->where('active', true)->count(),
        'total_equipment' => $this->machines()->count() + $this->tables()->count() + $this->semiAutomatics()->count(),
        'total_employees' => $this->employees()->count(),      // AGREGAR
        'active_employees' => $this->activeEmployees()->count(), // AGREGAR
    ];
}
```

---

## 5. Validaciones (Form Requests)

### 5.1 StoreEmployeeRequest

**Archivo**: `app/Http/Requests/StoreEmployeeRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // O implementar policy
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:employees,email'],
            'password' => ['required', 'string', 'min:8'],
            'number' => ['required', 'string', 'unique:employees,number', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'entry_date' => ['nullable', 'date', 'before_or_equal:today'],
            'active' => ['required', 'boolean'],
            'comments' => ['nullable', 'string', 'max:1000'],
            'area_id' => ['required', 'exists:areas,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'number.required' => 'El número de empleado es obligatorio.',
            'number.unique' => 'Este número de empleado ya existe.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'entry_date.before_or_equal' => 'La fecha de ingreso no puede ser futura.',
            'area_id.required' => 'Debe seleccionar un área.',
            'area_id.exists' => 'El área seleccionada no existe.',
            'shift_id.required' => 'Debe seleccionar un turno.',
            'shift_id.exists' => 'El turno seleccionado no existe.',
        ];
    }
}
```

### 5.2 UpdateEmployeeRequest

**Archivo**: `app/Http/Requests/UpdateEmployeeRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('employees')->ignore($employeeId)],
            'password' => ['nullable', 'string', 'min:8'], // Opcional en update
            'number' => ['required', 'string', 'max:255', Rule::unique('employees')->ignore($employeeId)],
            'position' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'entry_date' => ['nullable', 'date', 'before_or_equal:today'],
            'active' => ['required', 'boolean'],
            'comments' => ['nullable', 'string', 'max:1000'],
            'area_id' => ['required', 'exists:areas,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'number.required' => 'El número de empleado es obligatorio.',
            'number.unique' => 'Este número de empleado ya existe.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'entry_date.before_or_equal' => 'La fecha de ingreso no puede ser futura.',
            'area_id.required' => 'Debe seleccionar un área.',
            'area_id.exists' => 'El área seleccionada no existe.',
            'shift_id.required' => 'Debe seleccionar un turno.',
            'shift_id.exists' => 'El turno seleccionado no existe.',
        ];
    }
}
```

---

## 6. Componentes Livewire (Volt)

### 6.1 Lista de Empleados (Index)

**Archivo**: `resources/views/livewire/admin/employees/employee-index.blade.php`

**Funcionalidades**:
- Listado con paginación
- Búsqueda en tiempo real
- Filtros: por área, por turno, por estado (activo/inactivo)
- Ordenamiento por columnas
- Acciones: Ver, Editar, Eliminar (con confirmación)
- Botón: Crear nuevo empleado

**Columnas de la tabla**:
- Número de empleado
- Nombre completo
- Email
- Área
- Turno
- Puesto
- Estado (badge activo/inactivo)
- Acciones

### 6.2 Crear Empleado (Create)

**Archivo**: `resources/views/livewire/admin/employees/employee-create.blade.php`

**Formulario**:
```
┌─────────────────────────────────────────────────┐
│ Información Personal                            │
├─────────────────────────────────────────────────┤
│ [Nombre*]          [Apellido*]                  │
│ [Email*]           [Número de Empleado*]        │
│ [Contraseña*]      [Confirmar Contraseña*]      │
│ [Fecha Nacimiento] [Fecha Ingreso]              │
├─────────────────────────────────────────────────┤
│ Información Laboral                             │
├─────────────────────────────────────────────────┤
│ [Área*]            [Turno*]                     │
│ [Puesto/Cargo]                                  │
│ [Estado] ○ Activo  ○ Inactivo                   │
│ [Comentarios]                                   │
│ (Textarea)                                      │
├─────────────────────────────────────────────────┤
│ [Cancelar]                      [Crear Empleado]│
└─────────────────────────────────────────────────┘
```

**Validaciones en tiempo real**:
- Email único
- Número de empleado único
- Campos requeridos

### 6.3 Editar Empleado (Edit)

**Archivo**: `resources/views/livewire/admin/employees/employee-edit.blade.php`

**Similar a Create pero**:
- Campos pre-llenados con datos actuales
- Contraseña opcional (solo si se quiere cambiar)
- Mostrar fecha de creación y última actualización
- Botón "Guardar cambios"

### 6.4 Mostrar Empleado (Show)

**Archivo**: `resources/views/livewire/admin/employees/employee-show.blade.php`

**Secciones**:

1. **Header**: Nombre completo, estado (badge), botones Editar/Volver

2. **Información Personal**:
   - Nombre completo
   - Email
   - Número de empleado
   - Fecha de nacimiento (+ edad)
   - Fecha de ingreso (+ años en la empresa)

3. **Información Laboral**:
   - Área (con link a área)
   - Turno (con link a turno)
   - Puesto/Cargo
   - Estado

4. **Estadísticas** (futuro, si se integra con production):
   - Horas trabajadas este mes
   - Sesiones de producción
   - Productos ensamblados

5. **Auditoría**:
   - Fecha de creación
   - Última actualización
   - Comentarios

### 6.5 Estructura de los Componentes Volt

**Patrón a seguir** (similar a Shifts):

```php
<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Employee;
use App\Models\Area;
use App\Models\Shift;

new class extends Component {
    use WithPagination;

    // Propiedades públicas
    public $search = '';
    public $filterArea = '';
    public $filterShift = '';
    public $filterStatus = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    // Listeners
    protected $listeners = ['employeeCreated' => '$refresh'];

    // Métodos
    public function with(): array
    {
        return [
            'employees' => Employee::query()
                ->when($this->search, function($query) {
                    $query->search($this->search);
                })
                ->when($this->filterArea, function($query) {
                    $query->byArea($this->filterArea);
                })
                ->when($this->filterShift, function($query) {
                    $query->byShift($this->filterShift);
                })
                ->when($this->filterStatus !== '', function($query) {
                    $query->where('active', $this->filterStatus);
                })
                ->with(['area', 'shift'])
                ->sortByField($this->sortField, $this->sortDirection)
                ->paginate(10),
            'areas' => Area::all(),
            'shifts' => Shift::active()->get(),
        ];
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);

        if ($employee->canBeDeleted()) {
            $employee->delete();
            session()->flash('success', 'Empleado eliminado correctamente.');
        } else {
            session()->flash('error', 'No se puede eliminar el empleado.');
        }
    }
}; ?>

<div>
    <!-- Vista HTML aquí -->
</div>
```

---

## 7. Rutas

### 7.1 Rutas Admin para CRUD

**Archivo**: `routes/admin.php`

```php
// Agregar estas rutas en el grupo admin
Route::prefix('employees')->name('employees.')->group(function () {
    Route::get('/', function() {
        return view('admin.employees.index');
    })->name('index');

    Route::get('/create', function() {
        return view('admin.employees.create');
    })->name('create');

    Route::get('/{employee}', function(Employee $employee) {
        return view('admin.employees.show', compact('employee'));
    })->name('show');

    Route::get('/{employee}/edit', function(Employee $employee) {
        return view('admin.employees.edit', compact('employee'));
    })->name('edit');
});
```

**Nota**: Las rutas solo renderizan vistas. La lógica está en los componentes Livewire.

### 7.2 Integración en el Menú Admin

**Archivo**: `resources/views/components/layouts/admin.blade.php` (o donde esté el sidebar)

Agregar en la sección de Configuración:

```html
<li>
    <a href="{{ route('admin.employees.index') }}"
       class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
        <svg><!-- Icon de empleados --></svg>
        <span>Empleados</span>
    </a>
</li>
```

---

## 8. Factory y Seeder

### 8.1 EmployeeFactory

**Archivo**: `database/factories/EmployeeFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password', // Se hasheará automáticamente por el mutator
            'number' => fake()->unique()->numerify('EMP-####'),
            'position' => fake()->randomElement([
                'Operator',
                'Technician',
                'Supervisor',
                'Quality Inspector',
                'Assembly Worker',
                'Machine Operator',
            ]),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'entry_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'active' => fake()->boolean(90), // 90% activos
            'comments' => fake()->optional(0.3)->sentence(),
            'area_id' => Area::inRandomOrder()->first()?->id ?? Area::factory(),
            'shift_id' => Shift::active()->inRandomOrder()->first()?->id ?? Shift::factory(),
        ];
    }

    /**
     * Estado: Empleado activo
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    /**
     * Estado: Empleado inactivo
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
```

### 8.2 EmployeeSeeder

**Archivo**: `database/seeders/EmployeeSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Area;
use App\Models\Shift;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar que existan áreas y turnos
        if (Area::count() === 0) {
            $this->command->warn('No hay áreas creadas. Ejecuta AreaSeeder primero.');
            return;
        }

        if (Shift::count() === 0) {
            $this->command->warn('No hay turnos creados. Ejecuta ShiftSeeder primero.');
            return;
        }

        $this->command->info('Creando empleados de prueba...');

        // Crear 50 empleados de prueba
        Employee::factory()
            ->count(50)
            ->create();

        $this->command->info('Empleados creados exitosamente.');
        $this->command->info('Total empleados: ' . Employee::count());
        $this->command->info('Empleados activos: ' . Employee::active()->count());
    }
}
```

**Integración en DatabaseSeeder**:
```php
// En database/seeders/DatabaseSeeder.php
public function run(): void
{
    // ... otros seeders
    $this->call(ShiftSeeder::class);
    $this->call(AreaSeeder::class);
    $this->call(EmployeeSeeder::class); // AGREGAR AQUÍ
}
```

---

## 9. Testing

### 9.1 Unit Tests

**Archivo**: `tests/Unit/EmployeeTest.php`

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\Area;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function employee_belongs_to_a_shift()
    {
        $shift = Shift::factory()->create();
        $employee = Employee::factory()->create(['shift_id' => $shift->id]);

        $this->assertInstanceOf(Shift::class, $employee->shift);
        $this->assertEquals($shift->id, $employee->shift->id);
    }

    /** @test */
    public function employee_belongs_to_an_area()
    {
        $area = Area::factory()->create();
        $employee = Employee::factory()->create(['area_id' => $area->id]);

        $this->assertInstanceOf(Area::class, $employee->area);
        $this->assertEquals($area->id, $employee->area->id);
    }

    /** @test */
    public function employee_has_full_name_accessor()
    {
        $employee = Employee::factory()->create([
            'name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $employee->full_name);
    }

    /** @test */
    public function employee_can_be_activated()
    {
        $employee = Employee::factory()->inactive()->create();

        $employee->activate();

        $this->assertTrue($employee->isActive());
        $this->assertEquals(1, $employee->active);
    }

    /** @test */
    public function employee_can_be_deactivated()
    {
        $employee = Employee::factory()->active()->create();

        $employee->deactivate();

        $this->assertFalse($employee->isActive());
        $this->assertEquals(0, $employee->active);
    }

    /** @test */
    public function employee_password_is_hashed()
    {
        $employee = Employee::factory()->create([
            'password' => 'plaintext',
        ]);

        $this->assertNotEquals('plaintext', $employee->password);
        $this->assertTrue(\Hash::check('plaintext', $employee->password));
    }

    /** @test */
    public function employee_can_change_shift()
    {
        $oldShift = Shift::factory()->create();
        $newShift = Shift::factory()->create();
        $employee = Employee::factory()->create(['shift_id' => $oldShift->id]);

        $result = $employee->changeShift($newShift->id, 'Cambio de turno por necesidad');

        $this->assertTrue($result);
        $this->assertEquals($newShift->id, $employee->fresh()->shift_id);
    }
}
```

### 9.2 Feature Tests

**Archivo**: `tests/Feature/EmployeeManagementTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\Area;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuario admin
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function admin_can_view_employee_list()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.employees.index'));

        $response->assertStatus(200);
        $response->assertSee('Empleados');
    }

    /** @test */
    public function admin_can_view_employee_create_form()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.employees.create'));

        $response->assertStatus(200);
        $response->assertSee('Crear Empleado');
    }

    /** @test */
    public function admin_can_view_employee_details()
    {
        $this->actingAs($this->admin);
        $employee = Employee::factory()->create();

        $response = $this->get(route('admin.employees.show', $employee));

        $response->assertStatus(200);
        $response->assertSee($employee->full_name);
    }

    /** @test */
    public function employee_requires_validation()
    {
        $this->actingAs($this->admin);

        Livewire::test('admin.employees.employee-create')
            ->set('name', '')
            ->set('email', 'invalid-email')
            ->call('save')
            ->assertHasErrors(['name', 'email']);
    }

    /** @test */
    public function employee_email_must_be_unique()
    {
        $this->actingAs($this->admin);
        $existingEmployee = Employee::factory()->create(['email' => 'test@example.com']);

        Livewire::test('admin.employees.employee-create')
            ->set('email', 'test@example.com')
            ->call('save')
            ->assertHasErrors(['email']);
    }
}
```

---

## 10. Plan de Implementación por Fases

### FASE 1: Fundamentos (CRÍTICO) - Estimado: 4-6 horas

**Objetivo**: Implementar el modelo Employee y relaciones básicas

- [ ] **Paso 1.1**: Completar modelo Employee
  - Agregar fillable, casts, hidden
  - Implementar relaciones shift() y area()
  - Agregar accessors (full_name, years_in_company, etc.)
  - Agregar mutators (password hashing)
  - Agregar scopes básicos
  - Agregar métodos auxiliares

- [ ] **Paso 1.2**: Actualizar modelos relacionados
  - Descomentar y mejorar relación Shift->employees()
  - Agregar relación Area->employees()
  - Actualizar métodos canBeDeleted() en Shift y Area
  - Descomentar métodos getStats() en Shift

- [ ] **Paso 1.3**: Crear Form Requests
  - Crear StoreEmployeeRequest con validaciones completas
  - Crear UpdateEmployeeRequest con validaciones y unique ignore

- [ ] **Paso 1.4**: Implementar Factory y Seeder
  - Completar EmployeeFactory con datos realistas
  - Completar EmployeeSeeder
  - Integrar en DatabaseSeeder
  - Ejecutar seeder y validar datos

**Entregables FASE 1**:
- Modelo Employee completamente funcional
- Relaciones bidireccionales funcionando
- Factory y Seeder generando datos de prueba
- Validaciones listas para uso en componentes

**Validación FASE 1**:
```bash
php artisan tinker
>>> Employee::with(['shift', 'area'])->first()
>>> Shift::with('employees')->first()
>>> Area::with('employees')->first()
```

---

### FASE 2: Componentes Livewire (CRÍTICO) - Estimado: 6-8 horas

**Objetivo**: Crear componentes Volt para CRUD completo

- [ ] **Paso 2.1**: Crear estructura de vistas
  ```bash
  mkdir resources/views/admin/employees
  mkdir resources/views/livewire/admin/employees
  ```

- [ ] **Paso 2.2**: Implementar employee-index.blade.php (Listado)
  - Componente Volt con paginación
  - Búsqueda en tiempo real
  - Filtros: área, turno, estado
  - Ordenamiento por columnas
  - Tabla con acciones (Ver, Editar, Eliminar)
  - Modal de confirmación para eliminar

- [ ] **Paso 2.3**: Implementar employee-create.blade.php (Crear)
  - Formulario completo con todos los campos
  - Validación en tiempo real
  - Selects dinámicos para área y turno
  - Manejo de errores
  - Redirección después de crear

- [ ] **Paso 2.4**: Implementar employee-edit.blade.php (Editar)
  - Similar a create pero con datos precargados
  - Contraseña opcional
  - Validación de unicidad excluyendo el propio registro
  - Actualización exitosa con mensaje

- [ ] **Paso 2.5**: Implementar employee-show.blade.php (Detalle)
  - Vista de solo lectura con toda la información
  - Secciones organizadas (Personal, Laboral, Auditoría)
  - Badges para estado
  - Links a área y turno relacionados
  - Botones de acción (Editar, Volver)

- [ ] **Paso 2.6**: Crear vistas wrapper en admin/employees
  - index.blade.php → incluye @livewire('admin.employees.employee-index')
  - create.blade.php → incluye @livewire('admin.employees.employee-create')
  - edit.blade.php → incluye @livewire('admin.employees.employee-edit')
  - show.blade.php → incluye @livewire('admin.employees.employee-show')

**Entregables FASE 2**:
- 4 componentes Volt completamente funcionales
- CRUD completo operativo
- Validaciones en tiempo real
- UX consistente con el resto del sistema

**Validación FASE 2**:
- Crear empleado desde UI
- Editar empleado existente
- Ver detalles de empleado
- Eliminar empleado
- Filtrar y buscar empleados

---

### FASE 3: Rutas y Navegación (IMPORTANTE) - Estimado: 2 horas

**Objetivo**: Integrar empleados en el sistema de rutas y menú

- [ ] **Paso 3.1**: Agregar rutas en routes/admin.php
  - Grupo employees con prefijo y name
  - Rutas: index, create, show, edit
  - Route model binding para Employee

- [ ] **Paso 3.2**: Actualizar menú de navegación
  - Agregar ítem "Empleados" en sidebar admin
  - Ícono apropiado
  - Clase active cuando estamos en employees.*

- [ ] **Paso 3.3**: Breadcrumbs (opcional)
  - Agregar breadcrumbs en cada vista
  - Admin > Empleados > [Acción]

**Entregables FASE 3**:
- Rutas funcionando correctamente
- Menú actualizado
- Navegación intuitiva

---

### FASE 4: Integración con Shifts (CRÍTICO) - Estimado: 2-3 horas

**Objetivo**: Descomentar y activar sección de empleados en shift-show

- [ ] **Paso 4.1**: Actualizar shift-show.blade.php
  - Descomentar sección de empleados (líneas 230-307)
  - Verificar que la relación $shift->employees funciona
  - Ajustar nombres de propiedades si es necesario

- [ ] **Paso 4.2**: Actualizar componente Volt de shift-show
  - Descomentar líneas de stats
  - Cargar relación 'employees' en mount
  - Activar métodos getStats() y getEmployeeStats()

- [ ] **Paso 4.3**: Agregar link "Agregar Empleado a este Turno"
  - En shift-show, agregar botón que lleve a employees.create?shift_id={id}
  - Pre-seleccionar el turno en el formulario de creación

**Entregables FASE 4**:
- Vista shift-show mostrando empleados del turno
- Estadísticas de empleados funcionando
- Integración bidireccional completa

**Validación FASE 4**:
```
1. Ir a un Shift
2. Ver lista de empleados asignados
3. Ver estadísticas de empleados
4. Crear nuevo empleado desde shift-show
5. Verificar que aparece en la lista
```

---

### FASE 5: Testing y Refinamiento (IMPORTANTE) - Estimado: 3-4 horas

**Objetivo**: Asegurar calidad y robustez del módulo

- [ ] **Paso 5.1**: Implementar Unit Tests
  - Tests de modelo (relaciones, accessors, mutators)
  - Tests de scopes
  - Tests de métodos auxiliares

- [ ] **Paso 5.2**: Implementar Feature Tests
  - Tests de rutas y autorización
  - Tests de componentes Livewire
  - Tests de validaciones
  - Tests de CRUD completo

- [ ] **Paso 5.3**: Testing manual exhaustivo
  - Crear 20 empleados de prueba
  - Probar todos los filtros y búsquedas
  - Probar ordenamiento
  - Probar eliminación con dependencias
  - Probar cambios de turno

- [ ] **Paso 5.4**: Refinamiento de UX
  - Mensajes de éxito/error claros
  - Loading states en botones
  - Confirmaciones antes de eliminar
  - Tooltips donde sean necesarios

**Entregables FASE 5**:
- Suite de tests completa
- Cobertura de al menos 80% en modelo Employee
- Manual de usuario (opcional)

---

### FASE 6: Features Avanzados (OPCIONAL - Futuro) - Estimado: 4-6 horas

**Objetivo**: Agregar funcionalidades adicionales

- [ ] **Paso 6.1**: Historial de cambios de turno
  - Crear tabla employee_shift_history
  - Migración
  - Modelo EmployeeShiftHistory
  - Registrar cambios automáticamente
  - Vista de historial en employee-show

- [ ] **Paso 6.2**: Exportación de datos
  - Exportar lista de empleados a Excel
  - Exportar a PDF
  - Filtros aplicados a la exportación

- [ ] **Paso 6.3**: Importación masiva
  - Importar empleados desde Excel/CSV
  - Validación de datos
  - Preview antes de importar

- [ ] **Paso 6.4**: Políticas y permisos
  - EmployeePolicy
  - Verificar permisos en componentes
  - Restringir acciones según rol

- [ ] **Paso 6.5**: Notificaciones
  - Notificar a supervisor cuando empleado cambia de turno
  - Notificar a empleado cuando es creado (email de bienvenida)

**Entregables FASE 6**:
- Features avanzados según prioridad del negocio

---

## 11. Diagramas

### 11.1 Diagrama Entidad-Relación

```
┌──────────────────┐              ┌──────────────────┐
│     Shifts       │1           N │    Employees     │
│──────────────────│◄─────────────│──────────────────│
│ id               │              │ id               │
│ name             │              │ name             │
│ start_time       │              │ last_name        │
│ end_time         │              │ email            │
│ active           │              │ password         │
│ comments         │              │ number           │
│ timestamps       │              │ position         │
└──────────────────┘              │ birth_date       │
                                  │ entry_date       │
                                  │ active           │
                                  │ comments         │
                                  │ shift_id (FK)    │
                                  │ area_id (FK)     │
                                  │ timestamps       │
                                  │ soft_deletes     │
                                  └──────────────────┘
                                           ▲
                                           │
                                           │ N
                                           │
                                           │
                                  ┌────────┴─────────┐
                                  │      Areas       │1
                                  │──────────────────│
                                  │ id               │
                                  │ name             │
                                  │ description      │
                                  │ user_id (FK)     │
                                  │ department_id(FK)│
                                  │ timestamps       │
                                  └──────────────────┘
```

### 11.2 Flujo de Creación de Empleado

```
┌──────────────┐
│   Usuario    │
│   (Admin)    │
└──────┬───────┘
       │
       ├─► Click en "Empleados"
       │
       ├─► Vista: employee-index
       │
       ├─► Click en "Crear Empleado"
       │
       ├─► Vista: employee-create
       │
       ├─► Llena formulario
       │   ┌─────────────────────────┐
       │   │ - Nombre*               │
       │   │ - Apellido*             │
       │   │ - Email* (único)        │
       │   │ - Password*             │
       │   │ - Número* (único)       │
       │   │ - Área* (select)        │
       │   │ - Turno* (select)       │
       │   │ - Puesto                │
       │   │ - Fechas                │
       │   │ - Estado                │
       │   └─────────────────────────┘
       │
       ├─► Click en "Crear"
       │
       ├─► Validación en Livewire
       │   │
       │   ├─► ¿Válido?
       │   │   │
       │   │   NO──► Mostrar errores
       │   │   │
       │   │   SÍ──► Guardar en DB
       │           │
       │           ├─► Password hasheado
       │           │
       │           ├─► Crear Employee
       │           │
       │           ├─► Mensaje de éxito
       │           │
       │           └─► Redirect a employee-show
       │
       └─► Ver empleado creado
```

### 11.3 Flujo de Integración Shift-Employee

```
┌─────────────────────────────────────────────────┐
│           Vista: shift-show                     │
├─────────────────────────────────────────────────┤
│                                                 │
│  Turno: "First Shift (Morning)"                 │
│  Horario: 06:00 - 14:00                         │
│                                                 │
├─────────────────────────────────────────────────┤
│  Estadísticas de Empleados                      │
├─────────────────────────────────────────────────┤
│  Total Empleados: 15                            │
│  Empleados Activos: 14                          │
├─────────────────────────────────────────────────┤
│  Empleados en este turno                        │
├─────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────┐   │
│  │ Nombre      │ Código  │ Estado │ Acción │   │
│  ├─────────────────────────────────────────┤   │
│  │ John Doe    │ EMP-001 │ Activo │ [Ver]  │   │
│  │ Jane Smith  │ EMP-002 │ Activo │ [Ver]  │   │
│  │ Mike Wilson │ EMP-003 │ Activo │ [Ver]  │   │
│  └─────────────────────────────────────────┘   │
│                                                 │
│  [+ Agregar Empleado a este Turno]              │
└─────────────────────────────────────────────────┘
```

---

## 12. Consideraciones de Seguridad

### 12.1 Autenticación de Empleados

**Problema**: La tabla `employees` tiene campos `email` y `password`, lo que sugiere que los empleados pueden autenticarse.

**Solución**:
- Actualmente existe `routes/employee.php` para el panel de empleados
- Si se requiere login de empleados, implementar:
  1. Guard separado para empleados en `config/auth.php`
  2. Middleware para rutas de empleados
  3. Login independiente del admin

**Configuración recomendada** (`config/auth.php`):
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'employee' => [
        'driver' => 'session',
        'provider' => 'employees',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
    'employees' => [
        'driver' => 'eloquent',
        'model' => App\Models\Employee::class,
    ],
],
```

### 12.2 Políticas de Acceso

**EmployeePolicy** (futuro):
```php
class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('employees.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('employees.create');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasPermission('employees.update');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasPermission('employees.delete')
            && $employee->canBeDeleted();
    }
}
```

### 12.3 Protección de Datos Sensibles

- **Password**: Hash automático mediante mutator
- **Email**: Unique validation
- **Soft Deletes**: Datos no se eliminan físicamente
- **Hidden**: Password oculto en JSON responses

---

## 13. Mejores Prácticas y Convenciones

### 13.1 Naming Conventions

**Seguir el patrón del proyecto**:
- Modelos: PascalCase singular (`Employee`)
- Tablas: snake_case plural (`employees`)
- Relaciones: camelCase (`shift()`, `employees()`)
- Rutas: kebab-case (`admin.employees.index`)
- Vistas: kebab-case (`employee-index.blade.php`)
- Variables: camelCase (`$filterArea`)

### 13.2 Código Limpio

- Usar type hints en todos los métodos
- Documentar métodos públicos con PHPDoc
- Separar lógica de negocio en métodos auxiliares
- Mantener componentes Livewire pequeños y enfocados
- Reutilizar componentes UI (buttons, badges, modals)

### 13.3 Performance

**Optimizaciones**:
- Eager loading en listados: `->with(['shift', 'area'])`
- Paginación en lugar de `->get()`
- Índices en columnas de FK y búsqueda
- Cache de selects estáticos (áreas, turnos)

**Ejemplo de optimización en employee-index**:
```php
public function with(): array
{
    return [
        'employees' => Employee::query()
            ->with(['shift:id,name', 'area:id,name']) // Solo cargar lo necesario
            ->when($this->search, fn($q) => $q->search($this->search))
            ->select(['id', 'name', 'last_name', 'email', 'number', 'active', 'shift_id', 'area_id'])
            ->paginate(15),
    ];
}
```

---

## 14. Próximos Pasos y Recomendaciones

### 14.1 Prioridad INMEDIATA

1. **Implementar FASE 1** (Modelo y relaciones)
   - Completar Employee model
   - Actualizar Shift y Area models
   - Crear Form Requests
   - Completar Factory y Seeder

2. **Implementar FASE 2** (Componentes Livewire)
   - Crear los 4 componentes Volt
   - CRUD completo funcional

3. **Implementar FASE 4** (Integración Shifts)
   - Descomentar sección en shift-show
   - Validar funcionamiento

### 14.2 Prioridad ALTA (después de inmediato)

4. **Implementar FASE 3** (Rutas y navegación)
5. **Implementar FASE 5** (Testing)

### 14.3 Prioridad MEDIA (futuro)

6. **FASE 6** (Features avanzados según necesidad)
7. **Documentación de usuario final**
8. **Capacitación a usuarios admin**

### 14.4 Coordinación con Producción

**IMPORTANTE**: Antes de avanzar mucho, coordinar con Mau sobre:
- ¿Se necesita relación Employee ↔ ProductionSession?
- ¿Los empleados registran su producción individualmente?
- ¿Se necesita tracking de horas trabajadas por empleado?
- ¿Se implementará asistencia/check-in de empleados?

---

## 15. Resumen de Archivos a Crear/Modificar

### Archivos NUEVOS a crear:

**Models & Requests**:
- `app/Http/Requests/StoreEmployeeRequest.php`
- `app/Http/Requests/UpdateEmployeeRequest.php`

**Livewire/Volt Components**:
- `resources/views/livewire/admin/employees/employee-index.blade.php`
- `resources/views/livewire/admin/employees/employee-create.blade.php`
- `resources/views/livewire/admin/employees/employee-edit.blade.php`
- `resources/views/livewire/admin/employees/employee-show.blade.php`

**Vistas Wrapper**:
- `resources/views/admin/employees/index.blade.php`
- `resources/views/admin/employees/create.blade.php`
- `resources/views/admin/employees/edit.blade.php`
- `resources/views/admin/employees/show.blade.php`

**Tests**:
- `tests/Unit/EmployeeTest.php`
- `tests/Feature/EmployeeManagementTest.php`

**Migraciones (opcional)**:
- `database/migrations/XXXX_add_indexes_to_employees_table.php`
- `database/migrations/XXXX_create_employee_shift_history_table.php` (FASE 6)

### Archivos EXISTENTES a modificar:

**Modelos**:
- `app/Models/Employee.php` (COMPLETAR)
- `app/Models/Shift.php` (descomentar relaciones y métodos)
- `app/Models/Area.php` (agregar relación employees)

**Factory & Seeder**:
- `database/factories/EmployeeFactory.php` (COMPLETAR)
- `database/seeders/EmployeeSeeder.php` (COMPLETAR)
- `database/seeders/DatabaseSeeder.php` (agregar call a EmployeeSeeder)

**Rutas**:
- `routes/admin.php` (agregar grupo employees)

**Layout/Navegación**:
- `resources/views/components/layouts/admin.blade.php` (agregar menú item)

**Vistas**:
- `resources/views/livewire/admin/shifts/shift-show.blade.php` (descomentar sección empleados)

**Controller** (OPCIONAL - si se decide no usar Livewire puro):
- `app/Http/Controllers/EmployeeController.php` (actualizar o eliminar)

---

## 16. Checklist de Implementación Completa

### FASE 1: Fundamentos
- [ ] Employee model completo con fillable, casts, hidden
- [ ] Relaciones shift() y area() implementadas
- [ ] Accessors implementados (full_name, years_in_company, etc.)
- [ ] Mutators implementados (password hashing)
- [ ] Scopes implementados (active, byShift, byArea, search, etc.)
- [ ] Métodos auxiliares (canBeDeleted, changeShift, activate, etc.)
- [ ] Shift model actualizado (relación employees, métodos stats)
- [ ] Area model actualizado (relación employees, canBeDeleted)
- [ ] StoreEmployeeRequest creado con validaciones
- [ ] UpdateEmployeeRequest creado con validaciones
- [ ] EmployeeFactory completado
- [ ] EmployeeSeeder completado
- [ ] DatabaseSeeder actualizado
- [ ] Seeder ejecutado y validado

### FASE 2: Componentes Livewire
- [ ] Estructura de directorios creada
- [ ] employee-index.blade.php implementado
- [ ] employee-create.blade.php implementado
- [ ] employee-edit.blade.php implementado
- [ ] employee-show.blade.php implementado
- [ ] Vistas wrapper creadas
- [ ] Búsqueda en tiempo real funcionando
- [ ] Filtros funcionando (área, turno, estado)
- [ ] Ordenamiento funcionando
- [ ] Paginación funcionando
- [ ] Validaciones en tiempo real
- [ ] Mensajes de éxito/error

### FASE 3: Rutas y Navegación
- [ ] Rutas agregadas en admin.php
- [ ] Rutas probadas y funcionando
- [ ] Menú actualizado en sidebar
- [ ] Ítem "Empleados" con ícono
- [ ] Active state funcionando
- [ ] Breadcrumbs implementados (opcional)

### FASE 4: Integración Shifts
- [ ] Sección de empleados descomentada en shift-show
- [ ] Stats funcionando
- [ ] Lista de empleados visible
- [ ] Link a employee-show desde shift-show
- [ ] Botón "Agregar empleado" (opcional)
- [ ] Pre-selección de turno en create (opcional)

### FASE 5: Testing
- [ ] Unit tests de modelo
- [ ] Unit tests de relaciones
- [ ] Unit tests de accessors/mutators
- [ ] Feature tests de rutas
- [ ] Feature tests de componentes
- [ ] Feature tests de validaciones
- [ ] Testing manual completo
- [ ] Cobertura de al menos 80%

### FASE 6: Features Avanzados (OPCIONAL)
- [ ] Historial de cambios de turno
- [ ] Exportación a Excel/PDF
- [ ] Importación masiva
- [ ] Políticas y permisos
- [ ] Notificaciones

---

## 17. Conclusiones

Este spec define la arquitectura completa para implementar el módulo de Empleados en Flexcon-Tracker. La implementación se divide en fases progresivas que permiten tener funcionalidad básica rápidamente (FASES 1-2-4) y luego agregar mejoras (FASES 3-5-6).

**Puntos clave**:

1. La estructura base ya existe (migración ejecutada, modelos creados)
2. Se requiere completar la implementación de modelos y relaciones
3. El patrón a seguir es consistente con Shifts (Livewire Volt + Clean Architecture)
4. La relación 1:N con Shifts y Areas está claramente definida
5. Se incluyen validaciones robustas y testing comprehensivo
6. El plan de implementación es claro y ejecutable

**Próximo paso inmediato**: Comenzar con FASE 1 - Completar el modelo Employee y sus relaciones.

**Contacto con Product Owner**: Coordinar con Mau sobre la integración con el módulo de Producción antes de avanzar a FASE 6.

---

**Fecha de creación**: 2025-12-27
**Autor**: Claude (Agent Architect)
**Versión**: 1.0
**Estado**: Pendiente de aprobación e implementación
