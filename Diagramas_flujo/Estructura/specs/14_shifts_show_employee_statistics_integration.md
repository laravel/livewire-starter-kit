# 14. Analisis de Integracion: Estadisticas de Empleados en Vista Show de Shifts

**Fecha**: 2026-01-13
**Version**: 1.1
**Autor**: Architect Agent
**Estado**: Analisis Completado
**Prioridad**: ALTA
**Ultima Actualizacion**: 2026-01-13 - Agregado manejo de casos sin empleados (N/A)

---

## 1. Resumen Ejecutivo

### 1.1 Objetivo

Integrar contadores dinamicos de empleados en la vista "show" del CRUD de Shifts para mostrar:

1. **Total de empleados activos en el sistema** - Conteo global de empleados con status `active = true`
2. **Total de empleados inactivos en el sistema** - Conteo global de empleados con status `active = false`
3. **Total de empleados asociados al turno seleccionado** - Conteo de empleados asignados a ese turno especifico

### 1.2 Contexto Arquitectonico

El sistema utiliza una arquitectura **unificada** donde:

- **NO existe modelo `Employee` independiente**
- Los empleados son **usuarios (`User`)** con rol `employee` (via Spatie Permissions)
- La tabla `users` contiene campos extendidos para empleados (`shift_id`, `area_id`, `active`, etc.)
- El modelo `Shift` ya tiene relaciones definidas con `User` via `employees()` y `allEmployees()`

### 1.3 Alcance del Cambio

| Componente | Archivo | Tipo de Cambio |
|------------|---------|----------------|
| Componente Livewire | `app/Livewire/Admin/Shifts/ShiftShow.php` | Modificacion |
| Vista Blade | `resources/views/livewire/admin/shifts/shift-show.blade.php` | Modificacion |
| Modelo Shift | `app/Models/Shift.php` | Sin cambios (ya tiene relaciones) |
| Modelo User | `app/Models/User.php` | Sin cambios (ya tiene scopes) |

### 1.4 Impacto

- **Complejidad**: Baja - Cambios localizados en 2 archivos
- **Performance**: Optimizada mediante queries eficientes
- **Riesgos**: Minimos - No afecta esquema de base de datos
- **Tiempo Estimado**: 1-2 horas de implementacion

---

## 2. Analisis del Estado Actual

### 2.1 Componente Livewire `ShiftShow.php`

**Archivo**: `app/Livewire/Admin/Shifts/ShiftShow.php`

```php
<?php

namespace App\Livewire\Admin\Shifts;

use App\Models\Shift;
use Livewire\Component;

class ShiftShow extends Component
{
    public Shift $shift;

    public function mount(Shift $shift): void
    {
        $this->shift = $shift;
    }

    public function render()
    {
        return view('livewire.admin.shifts.shift-show');
    }
}
```

**Estado Actual**:
- Componente basico sin logica adicional
- NO carga relaciones (eager loading)
- NO calcula estadisticas de empleados
- NO pasa datos adicionales a la vista

### 2.2 Vista Blade `shift-show.blade.php` (Analisis de Seccion Relevante)

**Archivo**: `resources/views/livewire/admin/shifts/shift-show.blade.php`

**Seccion de Estadisticas de Empleados (lineas 136-166)**:
```blade
<!-- Estadisticas de Empleados -->
<div class="mt-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Estadisticas de Empleados</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                        Total Empleados
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                        0{{-- {{ $stats['total_employees'] }} //need create employees Migration and model --}}
                    </dd>
                </dl>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                        Empleados Activos
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                        0{{-- {{ $stats['active_employees'] }} //pending --}}
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
```

**Problemas Identificados**:
1. Muestra **valores hardcodeados a 0**
2. Codigo comentado referencia `$stats['total_employees']` y `$stats['active_employees']`
3. Comentarios obsoletos mencionan "need create employees Migration and model" (ya no aplica)
4. Solo tiene 2 tarjetas, **falta la tercera** para empleados del turno especifico

### 2.3 Vista Blade - Seccion de Tabla de Empleados (lineas 226-302)

```blade
<!-- Tabla de Empleados -->
<div class="mt-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Empleados en este turno</h3>

    {{-- @if ($shift->Employees->count() > 0)
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <!-- Tabla comentada -->
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
            <p class="text-gray-500 dark:text-gray-400 text-center">No hay empleados asignados a este turno.</p>
        </div>
    @endif --}}
</div>
```

**Estado**: Toda la seccion de tabla de empleados esta **comentada**.

### 2.4 Modelo Shift - Relaciones Existentes

**Archivo**: `app/Models/Shift.php`

```php
/**
 * Get all employees (users with employee role) for this shift
 * Only returns active users with 'employee' role
 */
public function employees(): HasMany
{
    return $this->hasMany(User::class, 'shift_id')
                ->role('employee')
                ->active()
                ->orderBy('name');
}

/**
 * Get all employees including inactive ones
 */
public function allEmployees(): HasMany
{
    return $this->hasMany(User::class, 'shift_id')
                ->role('employee')
                ->orderBy('name');
}

/**
 * Get employee count for this shift
 */
public function getEmployeeCountAttribute(): int
{
    return $this->employees()->count();
}
```

**Relaciones Disponibles**:
- `employees()` - Empleados activos con rol 'employee' del turno
- `allEmployees()` - Todos los empleados (activos e inactivos) del turno
- `employee_count` - Atributo calculado para conteo rapido

### 2.5 Modelo User - Scopes Disponibles

**Archivo**: `app/Models/User.php`

```php
public function scopeActive($query)
{
    return $query->where('active', true);
}

public function scopeInactive($query)
{
    return $query->where('active', false);
}

public function scopeEmployees($query)
{
    return $query->role('employee');
}

public function scopeByShift($query, $shiftId)
{
    return $query->where('shift_id', $shiftId);
}
```

**Scopes Disponibles**:
- `active()` - Filtra usuarios activos
- `inactive()` - Filtra usuarios inactivos
- `employees()` - Filtra usuarios con rol 'employee'
- `byShift($shiftId)` - Filtra por turno especifico

---

## 3. Diseno de la Solucion

### 3.1 Arquitectura de Datos

```
                         +-------------------+
                         |       User        |
                         |-------------------|
                         | id                |
                         | name              |
                         | last_name         |
                         | employee_number   |
                         | active (boolean)  |
                         | shift_id (FK)     |
                         | area_id (FK)      |
                         | roles (Spatie)    |
                         +-------------------+
                                  |
                                  | belongsTo
                                  v
+-------------------+    +-------------------+
|      Shift        |<---|    employees()    |
|-------------------|    +-------------------+
| id                |
| name              |    Relacion: Shift hasMany User
| start_time        |    Filtro: role('employee')
| end_time          |
| active            |
+-------------------+
```

### 3.2 Estructura de Estadisticas Requeridas

| Estadistica | Descripcion | Query |
|-------------|-------------|-------|
| `total_active_employees` | Empleados activos en TODO el sistema | `User::employees()->active()->count()` |
| `total_inactive_employees` | Empleados inactivos en TODO el sistema | `User::employees()->inactive()->count()` |
| `shift_employees_count` | Empleados asignados a ESTE turno | `$shift->allEmployees()->count()` |
| `shift_active_employees` | Empleados activos en ESTE turno | `$shift->employees()->count()` |
| `shift_inactive_employees` | Empleados inactivos en ESTE turno | `$shift->allEmployees()->inactive()->count()` |

### 3.3 Flujo de Datos

```
+------------------+      +----------------------+      +-------------------+
|   ShiftShow.php  | ---> |   Calcular Stats     | ---> | shift-show.blade  |
|   (Component)    |      |   - Global Stats     |      |   (Vista)         |
|                  |      |   - Shift Stats      |      |                   |
+------------------+      +----------------------+      +-------------------+
        |                          |                            |
        v                          v                            v
  mount($shift)            render() method              Mostrar tarjetas
  Load relationships       Build stats array            con estadisticas
```

---

## 4. Implementacion Propuesta

### 4.1 Modificacion del Componente `ShiftShow.php`

**Archivo**: `app/Livewire/Admin/Shifts/ShiftShow.php`

```php
<?php

namespace App\Livewire\Admin\Shifts;

use App\Models\Shift;
use App\Models\User;
use Livewire\Component;

class ShiftShow extends Component
{
    public Shift $shift;

    /**
     * Estadisticas globales de empleados
     */
    public array $globalStats = [];

    /**
     * Estadisticas de empleados del turno
     */
    public array $shiftStats = [];

    public function mount(Shift $shift): void
    {
        // Cargar shift con relaciones necesarias
        $this->shift = $shift->load(['BreakTimes', 'allEmployees']);

        // Calcular estadisticas
        $this->calculateStats();
    }

    /**
     * Calcula todas las estadisticas de empleados
     *
     * NOTA: Si no hay empleados en el sistema o en el turno,
     * los valores se establecen como null para mostrar "N/A" en la vista.
     */
    protected function calculateStats(): void
    {
        // Verificar si hay empleados en el sistema
        $totalEmployees = User::employees()->count();

        if ($totalEmployees === 0) {
            // No hay empleados en el sistema - mostrar N/A
            $this->globalStats = [
                'total_active' => null,   // Se mostrara como N/A en la vista
                'total_inactive' => null,
                'total_all' => null,
            ];
        } else {
            // Estadisticas globales del sistema
            $this->globalStats = [
                'total_active' => User::employees()->active()->count(),
                'total_inactive' => User::employees()->inactive()->count(),
                'total_all' => $totalEmployees,
            ];
        }

        // Verificar si hay empleados en este turno
        $shiftTotalEmployees = $this->shift->allEmployees()->count();

        if ($shiftTotalEmployees === 0) {
            // No hay empleados en este turno - mostrar N/A
            $this->shiftStats = [
                'total' => null,    // Se mostrara como N/A en la vista
                'active' => null,
                'inactive' => null,
            ];
        } else {
            // Estadisticas del turno actual
            $this->shiftStats = [
                'total' => $shiftTotalEmployees,
                'active' => $this->shift->employees()->count(),
                'inactive' => $this->shift->allEmployees()
                                          ->where('active', false)
                                          ->count(),
            ];
        }
    }

    /**
     * Refrescar estadisticas (util si se implementa actualizacion en tiempo real)
     */
    public function refreshStats(): void
    {
        $this->calculateStats();
    }

    public function render()
    {
        return view('livewire.admin.shifts.shift-show');
    }
}
```

### 4.2 Modificacion de la Vista `shift-show.blade.php`

#### 4.2.1 Seccion de Estadisticas de Empleados (Reemplazar lineas 136-166)

```blade
<!-- Estadisticas de Empleados -->
<div class="mt-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Estadisticas de Empleados</h3>

    <!-- Estadisticas Globales del Sistema -->
    <div class="mb-4">
        <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Estadisticas Globales</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Total Empleados Activos (Global) -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-green-500">
                <div class="px-4 py-5 sm:p-6">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Empleados Activos (Sistema)
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold {{ is_null($globalStats['total_active']) ? 'text-gray-400 dark:text-gray-500' : 'text-green-600 dark:text-green-400' }}">
                            {{ $globalStats['total_active'] ?? 'N/A' }}
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Total Empleados Inactivos (Global) -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-red-500">
                <div class="px-4 py-5 sm:p-6">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                            <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Empleados Inactivos (Sistema)
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold {{ is_null($globalStats['total_inactive']) ? 'text-gray-400 dark:text-gray-500' : 'text-red-600 dark:text-red-400' }}">
                            {{ $globalStats['total_inactive'] ?? 'N/A' }}
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Total General (Global) -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-blue-500">
                <div class="px-4 py-5 sm:p-6">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Total Empleados (Sistema)
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold {{ is_null($globalStats['total_all']) ? 'text-gray-400 dark:text-gray-500' : 'text-blue-600 dark:text-blue-400' }}">
                            {{ $globalStats['total_all'] ?? 'N/A' }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadisticas del Turno Actual -->
    <div class="mb-4">
        <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Estadisticas de este Turno: {{ $shift->name }}</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Empleados Asignados al Turno -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-indigo-500">
                <div class="px-4 py-5 sm:p-6">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            Total Asignados
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold {{ is_null($shiftStats['total']) ? 'text-gray-400 dark:text-gray-500' : 'text-indigo-600 dark:text-indigo-400' }}">
                            {{ $shiftStats['total'] ?? 'N/A' }}
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Empleados Activos en el Turno -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-emerald-500">
                <div class="px-4 py-5 sm:p-6">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Activos en Turno
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold {{ is_null($shiftStats['active']) ? 'text-gray-400 dark:text-gray-500' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ $shiftStats['active'] ?? 'N/A' }}
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Empleados Inactivos en el Turno -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-orange-500">
                <div class="px-4 py-5 sm:p-6">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                            Inactivos en Turno
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold {{ is_null($shiftStats['inactive']) ? 'text-gray-400 dark:text-gray-500' : 'text-orange-600 dark:text-orange-400' }}">
                            {{ $shiftStats['inactive'] ?? 'N/A' }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
```

#### 4.2.2 Seccion de Tabla de Empleados (Descomentar y Actualizar lineas 226-302)

```blade
<!-- Tabla de Empleados del Turno -->
<div class="mt-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Empleados en este turno</h3>
        @if (Route::has('admin.users.create'))
            <a href="{{ route('admin.users.create', ['shift_id' => $shift->id]) }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                Agregar Empleado
            </a>
        @endif
    </div>

    @if ($shift->allEmployees->count() > 0)
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                No. Empleado
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Nombre
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Posicion
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Area
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Estado
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                        @foreach ($shift->allEmployees as $employee)
                            <tr class="{{ !$employee->active ? 'bg-gray-50 dark:bg-gray-800/50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $employee->employee_number ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                                    {{ $employee->initials }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $employee->full_name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $employee->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $employee->position ?? 'Sin posicion' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $employee->area_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($employee->active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Activo
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if (Route::has('admin.users.show'))
                                            <a href="{{ route('admin.users.show', $employee) }}"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                Ver
                                            </a>
                                        @endif
                                        @if (Route::has('admin.users.edit'))
                                            <a href="{{ route('admin.users.edit', $employee) }}"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                Editar
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay empleados</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    No hay empleados asignados a este turno.
                </p>
                @if (Route::has('admin.users.create'))
                    <div class="mt-6">
                        <a href="{{ route('admin.users.create', ['shift_id' => $shift->id]) }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Agregar Empleado
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
```

---

## 5. Consideraciones de Performance

### 5.1 Queries Generadas

**Con la implementacion propuesta**:

```sql
-- Query 1: Cargar shift con relaciones
SELECT * FROM shifts WHERE id = ?
SELECT * FROM break_times WHERE shift_id = ?
SELECT * FROM users
    WHERE shift_id = ?
    AND EXISTS (SELECT * FROM model_has_roles WHERE user_id = users.id AND role_id = ?)
    ORDER BY name

-- Query 2: Estadisticas globales (3 queries separadas o 1 con subconsultas)
SELECT COUNT(*) FROM users
    WHERE EXISTS (SELECT * FROM model_has_roles ...)
    AND active = 1

SELECT COUNT(*) FROM users
    WHERE EXISTS (SELECT * FROM model_has_roles ...)
    AND active = 0

SELECT COUNT(*) FROM users
    WHERE EXISTS (SELECT * FROM model_has_roles ...)

-- Query 3: Estadisticas del turno (ya cargado via eager loading)
-- No requiere queries adicionales si se usa la coleccion cargada
```

### 5.2 Optimizacion de Queries para Estadisticas Globales

**Opcion 1: Queries separadas (mas legible)**
```php
$this->globalStats = [
    'total_active' => User::employees()->active()->count(),
    'total_inactive' => User::employees()->inactive()->count(),
    'total_all' => User::employees()->count(),
];
```

**Opcion 2: Query unica con selectRaw (mas eficiente)**
```php
$stats = User::employees()
    ->selectRaw('
        COUNT(*) as total_all,
        SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as total_active,
        SUM(CASE WHEN active = 0 THEN 1 ELSE 0 END) as total_inactive
    ')
    ->first();

$this->globalStats = [
    'total_active' => $stats->total_active,
    'total_inactive' => $stats->total_inactive,
    'total_all' => $stats->total_all,
];
```

**Recomendacion**: Usar **Opcion 2** para reducir el numero de queries de 3 a 1.

### 5.3 Indices Recomendados

Los indices ya existen en la tabla `users`:
- `shift_id` - Para filtrar por turno
- `active` - Para filtrar por estado

**Indice compuesto recomendado** (si no existe):
```sql
CREATE INDEX users_shift_id_active_index ON users(shift_id, active);
```

### 5.4 Caching (Opcional para Futuro)

Si las estadisticas globales se consultan frecuentemente, considerar cache:

```php
$this->globalStats = Cache::remember('employee_global_stats', 300, function () {
    return [
        'total_active' => User::employees()->active()->count(),
        'total_inactive' => User::employees()->inactive()->count(),
        'total_all' => User::employees()->count(),
    ];
});
```

**Nota**: Invalidar cache cuando se creen/actualicen/eliminen empleados.

---

## 6. Manejo de Casos Sin Empleados

Esta seccion describe el comportamiento del sistema cuando no existen empleados registrados.

### 6.1 Escenarios Contemplados

| Escenario | Condicion | Comportamiento |
|-----------|-----------|----------------|
| Sin empleados en el sistema | `User::employees()->count() === 0` | `globalStats` muestra "N/A" |
| Sin empleados en el turno | `$shift->allEmployees()->count() === 0` | `shiftStats` muestra "N/A" |
| Con empleados en sistema pero no en turno | Sistema tiene empleados, turno no | `globalStats` muestra numeros, `shiftStats` muestra "N/A" |

### 6.2 Implementacion en Backend (ShiftShow.php)

La logica en el metodo `calculateStats()` verifica la existencia de empleados antes de calcular estadisticas:

```php
protected function calculateStats(): void
{
    // Verificar si hay empleados en el sistema
    $totalEmployees = User::employees()->count();

    if ($totalEmployees === 0) {
        // No hay empleados en el sistema - establecer valores como null
        $this->globalStats = [
            'total_active' => null,   // Se mostrara como N/A
            'total_inactive' => null,
            'total_all' => null,
        ];
    } else {
        // Calcular estadisticas normalmente
        $this->globalStats = [
            'total_active' => User::employees()->active()->count(),
            'total_inactive' => User::employees()->inactive()->count(),
            'total_all' => $totalEmployees,
        ];
    }

    // Verificar si hay empleados en este turno
    $shiftTotalEmployees = $this->shift->allEmployees()->count();

    if ($shiftTotalEmployees === 0) {
        // No hay empleados en este turno - establecer valores como null
        $this->shiftStats = [
            'total' => null,
            'active' => null,
            'inactive' => null,
        ];
    } else {
        // Calcular estadisticas del turno normalmente
        $this->shiftStats = [
            'total' => $shiftTotalEmployees,
            'active' => $this->shift->employees()->count(),
            'inactive' => $this->shift->allEmployees()
                                      ->where('active', false)
                                      ->count(),
        ];
    }
}
```

**Puntos clave del backend**:
- Se usa `null` en lugar de `0` para distinguir "sin datos" de "cero empleados"
- La verificacion se hace con `=== 0` para ser explicita
- Cada grupo de estadisticas (global y turno) se maneja independientemente

### 6.3 Implementacion en Frontend (Vista Blade)

En la vista, se utiliza el operador null coalescing (`??`) para mostrar "N/A" y clases condicionales para el estilo:

```blade
{{-- Ejemplo para una tarjeta de estadisticas --}}
<dd class="mt-1 text-3xl font-semibold {{ is_null($globalStats['total_active']) ? 'text-gray-400 dark:text-gray-500' : 'text-green-600 dark:text-green-400' }}">
    {{ $globalStats['total_active'] ?? 'N/A' }}
</dd>
```

**Desglose de la logica**:

1. **Operador null coalescing (`??`)**:
   - Si `$globalStats['total_active']` es `null`, muestra `'N/A'`
   - Si tiene un valor (incluso `0`), muestra ese valor

2. **Clase CSS condicional**:
   - Si es `null`: aplica `text-gray-400` (color gris apagado para indicar "sin datos")
   - Si tiene valor: aplica el color normal de la tarjeta (verde, rojo, azul, etc.)

### 6.4 Alternativa con Directiva Blade

Tambien se puede usar una directiva `@if` para mayor claridad:

```blade
@if(is_null($globalStats['total_active']))
    <dd class="mt-1 text-3xl font-semibold text-gray-400 dark:text-gray-500">N/A</dd>
@else
    <dd class="mt-1 text-3xl font-semibold text-green-600 dark:text-green-400">
        {{ $globalStats['total_active'] }}
    </dd>
@endif
```

### 6.5 Por que N/A en Lugar de 0

| Mostrar | Significado | Cuando Usar |
|---------|-------------|-------------|
| `0` | Hay empleados registrados pero ninguno cumple el criterio | Ej: 0 empleados activos (hay inactivos) |
| `N/A` | No aplica porque no hay datos base | Ej: No hay empleados en el sistema |

**Razon**: Evita confusion al usuario. Si se muestra `0` cuando no hay empleados, el usuario podria pensar que hay un error o que los datos no se cargaron correctamente.

### 6.6 Consideraciones de UX

1. **Color diferenciado**: El gris (`text-gray-400`) indica visualmente que el dato no esta disponible
2. **Consistencia**: Todas las tarjetas del mismo grupo muestran N/A si no hay datos
3. **Mensaje complementario**: La tabla de empleados ya muestra "No hay empleados asignados" cuando esta vacia

---

## 7. Diagrama de Flujo de la Vista (Actualizado)

```
+-----------------------------------------------------------------------------------+
|                              SHIFT SHOW VIEW                                       |
+-----------------------------------------------------------------------------------+
|                                                                                    |
|  +-----------------------------------------------------------------------------+  |
|  |  HEADER: Nombre del Turno + Botones (Editar, Volver)                        |  |
|  +-----------------------------------------------------------------------------+  |
|                                                                                    |
|  +-----------------------------------------------------------------------------+  |
|  |  DETALLES DEL TURNO                                                         |  |
|  |  - Nombre, Horario, Estado, Comentarios, Fechas                             |  |
|  +-----------------------------------------------------------------------------+  |
|                                                                                    |
|  +-----------------------------------------------------------------------------+  |
|  |  ESTADISTICAS DE EMPLEADOS                                                  |  |
|  |                                                                              |  |
|  |  Estadisticas Globales:                                                      |  |
|  |  +---------------------------+  +---------------------------+  +------------+  |
|  |  | [GREEN] Activos (Sistema) |  | [RED] Inactivos (Sistema) |  | [BLUE]     |  |
|  |  |         XX                |  |          XX               |  | Total XX   |  |
|  |  +---------------------------+  +---------------------------+  +------------+  |
|  |                                                                              |  |
|  |  Estadisticas del Turno:                                                     |  |
|  |  +---------------------------+  +---------------------------+  +------------+  |
|  |  | [INDIGO] Total Asignados  |  | [EMERALD] Activos Turno   |  | [ORANGE]   |  |
|  |  |         XX                |  |          XX               |  | Inact. XX  |  |
|  |  +---------------------------+  +---------------------------+  +------------+  |
|  +-----------------------------------------------------------------------------+  |
|                                                                                    |
|  +-----------------------------------------------------------------------------+  |
|  |  ESTADISTICAS DE PRODUCCION                                                 |  |
|  |  - Total Sesiones, Sesiones Activas, Total Descansos, Descansos Activos     |  |
|  +-----------------------------------------------------------------------------+  |
|                                                                                    |
|  +-----------------------------------------------------------------------------+  |
|  |  TABLA: EMPLEADOS EN ESTE TURNO                                             |  |
|  |  +--------+------------+----------+------+---------+-----------+            |  |
|  |  | No.Emp | Nombre     | Posicion | Area | Estado  | Acciones  |            |  |
|  |  +--------+------------+----------+------+---------+-----------+            |  |
|  |  | EMP001 | Juan Perez | Operador | Prod | [Activo]| Ver|Edit  |            |  |
|  |  | EMP002 | Ana Lopez  | Tecnico  | QA   | [Inact] | Ver|Edit  |            |  |
|  |  +--------+------------+----------+------+---------+-----------+            |  |
|  +-----------------------------------------------------------------------------+  |
|                                                                                    |
|  +-----------------------------------------------------------------------------+  |
|  |  TABLA: DESCANSOS EN ESTE TURNO                                             |  |
|  |  - Nombre, Horario, Duracion, Estado, Acciones                              |  |
|  +-----------------------------------------------------------------------------+  |
|                                                                                    |
+-----------------------------------------------------------------------------------+
```

---

## 7. Plan de Implementacion

### 7.1 Fase 1: Modificar Componente Livewire (30 min)

**Archivo**: `app/Livewire/Admin/Shifts/ShiftShow.php`

**Pasos**:
1. Agregar import de `User`
2. Declarar propiedades `$globalStats` y `$shiftStats`
3. Implementar metodo `calculateStats()`
4. Actualizar metodo `mount()` para cargar relaciones y calcular stats
5. (Opcional) Agregar metodo `refreshStats()` para actualizacion en tiempo real

**Validacion**:
```bash
php artisan tinker
>>> $shift = App\Models\Shift::first();
>>> $component = new App\Livewire\Admin\Shifts\ShiftShow();
>>> $component->mount($shift);
>>> dd($component->globalStats, $component->shiftStats);
```

### 7.2 Fase 2: Actualizar Seccion de Estadisticas (45 min)

**Archivo**: `resources/views/livewire/admin/shifts/shift-show.blade.php`

**Pasos**:
1. Localizar seccion "Estadisticas de Empleados" (lineas 136-166)
2. Reemplazar contenido con las 6 tarjetas de estadisticas
3. Agregar subtitulos "Estadisticas Globales" y "Estadisticas del Turno"
4. Aplicar colores distintivos (green, red, blue, indigo, emerald, orange)
5. Agregar iconos SVG para cada tarjeta

### 7.3 Fase 3: Habilitar Tabla de Empleados (30 min)

**Archivo**: `resources/views/livewire/admin/shifts/shift-show.blade.php`

**Pasos**:
1. Localizar seccion "Tabla de Empleados" (lineas 226-302)
2. Descomentar todo el bloque
3. Actualizar referencias de `$shift->Employees` a `$shift->allEmployees`
4. Actualizar campos segun estructura actual de User (employee_number, full_name, etc.)
5. Actualizar rutas a `admin.users.*` en lugar de `employees.*`
6. Agregar avatar con iniciales
7. Agregar columna de Area

### 7.4 Fase 4: Pruebas (15 min)

**Pruebas Manuales**:
1. Acceder a vista Show de un turno CON empleados asignados
2. Verificar que las 6 tarjetas muestren valores correctos
3. Verificar que la tabla de empleados muestre datos correctos
4. Acceder a vista Show de un turno SIN empleados asignados
5. Verificar mensaje "No hay empleados asignados"
6. Verificar links de acciones (Ver, Editar)

**Pruebas de Performance**:
```bash
# Habilitar query log en .env
DB_LOG_QUERIES=true

# Verificar numero de queries en la vista
# Debe ser <= 5 queries
```

---

## 8. Consideraciones Adicionales

### 8.1 Accesibilidad

- Los colores de las tarjetas deben tener suficiente contraste
- Los iconos tienen atributos descriptivos
- Las tablas usan encabezados `th` con `scope="col"`

### 8.2 Responsive Design

- Grid usa `grid-cols-1 md:grid-cols-3` para adaptarse a pantallas
- Tabla tiene `overflow-x-auto` para scroll horizontal en movil
- Texto se trunca con `truncate` donde es necesario

### 8.3 Dark Mode

- Todos los elementos tienen clases `dark:` correspondientes
- Colores de texto: `dark:text-white`, `dark:text-gray-400`
- Fondos: `dark:bg-gray-800`, `dark:bg-gray-900`

### 8.4 Compatibilidad con Rutas

La implementacion verifica existencia de rutas antes de mostrar links:
```blade
@if (Route::has('admin.users.show'))
    <!-- Link -->
@endif
```

Esto previene errores si las rutas no estan definidas.

---

## 9. Riesgos y Mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigacion |
|--------|--------------|---------|------------|
| Queries lentas con muchos empleados | Baja | Medio | Eager loading + indices compuestos |
| Rutas de usuarios no definidas | Media | Bajo | Verificacion con `Route::has()` |
| Datos inconsistentes en cache | Baja | Bajo | No implementar cache inicialmente |
| Empleados sin rol 'employee' | Baja | Bajo | Scope `employees()` filtra por rol |
| Sistema sin empleados muestra 0 | Media | Medio | Mostrar N/A con valores null (ver seccion 6) |
| Confusion usuario con valores vacios | Media | Bajo | Color gris diferenciado para N/A |

---

## 10. Checklist de Implementacion

### Componente Livewire
- [ ] Agregar import `use App\Models\User;`
- [ ] Declarar `public array $globalStats = [];`
- [ ] Declarar `public array $shiftStats = [];`
- [ ] Implementar metodo `calculateStats()` con validacion de empleados vacios
- [ ] Verificar caso sin empleados globales (retornar null para N/A)
- [ ] Verificar caso sin empleados en turno (retornar null para N/A)
- [ ] Actualizar `mount()` con eager loading
- [ ] (Opcional) Agregar metodo `refreshStats()`

### Vista Blade - Estadisticas
- [ ] Reemplazar seccion de estadisticas hardcodeadas
- [ ] Agregar 3 tarjetas de estadisticas globales
- [ ] Agregar 3 tarjetas de estadisticas del turno
- [ ] Aplicar colores distintivos a cada tarjeta
- [ ] Agregar iconos SVG
- [ ] Implementar operador `??` para mostrar "N/A" en valores null
- [ ] Agregar clases CSS condicionales para color gris cuando es N/A

### Vista Blade - Tabla de Empleados
- [ ] Descomentar seccion de tabla
- [ ] Actualizar referencias a `$shift->allEmployees`
- [ ] Actualizar campos (employee_number, full_name, etc.)
- [ ] Actualizar rutas a `admin.users.*`
- [ ] Agregar columna de Area
- [ ] Agregar avatar con iniciales
- [ ] Verificar mensaje cuando no hay empleados

### Manejo de Casos Sin Empleados
- [ ] Verificar que globalStats muestre N/A cuando no hay empleados en sistema
- [ ] Verificar que shiftStats muestre N/A cuando no hay empleados en turno
- [ ] Confirmar que el color gris se aplica a valores N/A
- [ ] Verificar que la tabla muestre mensaje "No hay empleados asignados"

### Pruebas
- [ ] Probar con turno CON empleados
- [ ] Probar con turno SIN empleados
- [ ] Probar con sistema SIN empleados (eliminar todos temporalmente)
- [ ] Verificar conteos correctos
- [ ] Verificar que N/A se muestre correctamente
- [ ] Verificar links de acciones
- [ ] Verificar performance (queries <= 5)
- [ ] Probar en modo oscuro
- [ ] Probar en dispositivo movil

---

## 11. Archivos Afectados

| Archivo | Cambio | Lineas Afectadas |
|---------|--------|------------------|
| `app/Livewire/Admin/Shifts/ShiftShow.php` | Modificacion | Agregar ~30 lineas |
| `resources/views/livewire/admin/shifts/shift-show.blade.php` | Modificacion | Lineas 136-166, 226-302 |
| `app/Models/Shift.php` | Sin cambios | N/A |
| `app/Models/User.php` | Sin cambios | N/A |

---

## 12. Conclusion

Esta especificacion proporciona una guia completa para integrar las estadisticas de empleados en la vista Show de Shifts. La implementacion:

1. **Reutiliza** relaciones y scopes existentes en los modelos
2. **Optimiza** queries mediante eager loading
3. **Mantiene consistencia** con el diseno visual del sistema
4. **Soporta** dark mode y responsive design
5. **Minimiza riesgos** con verificaciones de rutas
6. **Maneja casos edge** mostrando "N/A" cuando no hay empleados

**Tiempo estimado total**: 2 horas

**Dependencias**: Ninguna (todos los modelos y relaciones ya existen)

---

## Historial de Cambios

| Version | Fecha | Descripcion |
|---------|-------|-------------|
| 1.0 | 2026-01-13 | Documento inicial |
| 1.1 | 2026-01-13 | Agregado manejo de casos sin empleados (N/A) - Seccion 6, actualizacion de codigo en seccion 4 |

---

**Fecha de creacion**: 2026-01-13
**Autor**: Architect Agent
**Version**: 1.1
**Estado**: Listo para implementacion
