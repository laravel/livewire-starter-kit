# Analisis Tecnico: Calculo y Visualizacion de Horas de Turno

## Documento de Especificacion Tecnica
- **Numero**: 15
- **Fecha**: 2026-01-13
- **Autor**: Agent Architect
- **Estado**: Borrador

---

## 1. Problema a Resolver

Se requiere implementar el calculo y visualizacion de horas de turno en dos ubicaciones:

1. **ShiftList (Listado)**: Mostrar las horas totales del turno en la columna "Horario"
2. **ShiftShow (Detalle)**: Mostrar horas totales, horas de descanso y horas laborables netas

### Ejemplo del Requerimiento:
```
Turno: 6:00 AM - 2:00 PM = 8 horas totales
Descansos: 2 breaks de 15 minutos = 30 minutos
Horas laborables netas: 8h - 30min = 7h 30min
```

---

## 2. Analisis del Estado Actual

### 2.1 Modelo Shift (`app/Models/Shift.php`)

**Campos relevantes:**
```php
protected $fillable = [
    'name',
    'start_time',   // time - Hora de inicio
    'end_time',     // time - Hora de fin
    'active',
    'comments',
];

protected $casts = [
    'active' => 'boolean',
    'start_time' => 'datetime:H:i',
    'end_time' => 'datetime:H:i',
];
```

**Relacion con BreakTimes:**
```php
public function BreakTimes(): HasMany
{
    return $this->hasMany(BreakTime::class);
}
```

### 2.2 Modelo BreakTime (`app/Models/BreakTime.php`)

**Campos relevantes:**
```php
protected $fillable = [
    'name',
    'start_break_time',  // time - Hora inicio descanso
    'end_break_time',    // time - Hora fin descanso
    'active',
    'comments',
    'shift_id',          // FK a shifts
];

protected $casts = [
    'active' => 'boolean',
    'start_break_time' => 'datetime:H:i',
    'end_break_time' => 'datetime:H:i',
];
```

### 2.3 ShiftList Component (`app/Livewire/Admin/Shifts/ShiftList.php`)

**Estado actual:**
- Usa `withCount('employees')` para contar empleados
- No tiene logica para calcular horas
- La vista muestra horario como texto simple: `start_time - end_time`

**Query actual:**
```php
$shifts = Shift::withCount('employees')
            ->search($this->search)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
```

### 2.4 ShiftShow Component

**Vistas encontradas:**
- `resources/views/livewire/admin/shifts/shift-show.blade.php` (Volt component)
- `app/Livewire/Admin/Shifts/ShiftShow.php` (Class component)

**Estado actual:**
- Carga relaciones: `BreakTimes`, `allEmployees`
- Calcula estadisticas de empleados
- Muestra duracion de breaks individualmente en la tabla
- **NO tiene calculo de horas totales del turno ni horas netas**

### 2.5 Vista shift-show.blade.php - Calculo Existente de Duracion

La vista ya tiene un calculo de duracion para cada break individual:
```php
@php
    $start = \Carbon\Carbon::parse($breakTime->start_break_time);
    $end = \Carbon\Carbon::parse($breakTime->end_break_time);
    $duration = $start->diff($end);
@endphp
{{ $duration->h > 0 ? $duration->h . 'h ' : '' }}{{ $duration->i }}min
```

---

## 3. Impacto Arquitectural

### 3.1 Backend - Modelo Shift

| Cambio | Descripcion | Tipo |
|--------|-------------|------|
| `getTotalHoursAttribute()` | Accessor para horas totales del turno | Nuevo |
| `getTotalBreakMinutesAttribute()` | Accessor para total minutos de descanso | Nuevo |
| `getNetWorkingHoursAttribute()` | Accessor para horas laborables netas | Nuevo |
| `getFormattedTotalHoursAttribute()` | Accessor formateado "Xh Ym" | Nuevo |
| `getFormattedNetWorkingHoursAttribute()` | Accessor formateado para horas netas | Nuevo |

### 3.2 Backend - Modelo BreakTime

| Cambio | Descripcion | Tipo |
|--------|-------------|------|
| `getDurationMinutesAttribute()` | Accessor para duracion en minutos | Nuevo |
| `getFormattedDurationAttribute()` | Accessor formateado "Xh Ym" o "Xmin" | Nuevo |

### 3.3 Frontend - ShiftList

| Cambio | Descripcion | Tipo |
|--------|-------------|------|
| Columna Horario | Agregar horas totales despues del rango | Modificacion |

### 3.4 Frontend - ShiftShow

| Cambio | Descripcion | Tipo |
|--------|-------------|------|
| Seccion "Informacion de Horas" | Nueva seccion con cards de horas | Nuevo |
| Card: Horas Totales | Mostrar duracion total del turno | Nuevo |
| Card: Tiempo de Descansos | Mostrar suma de todos los breaks | Nuevo |
| Card: Horas Netas Laborables | Mostrar horas efectivas de trabajo | Nuevo |

### 3.5 Base de Datos

**No se requieren cambios en la base de datos.** Los calculos se realizaran en tiempo de ejecucion usando los campos existentes:
- `shifts.start_time`
- `shifts.end_time`
- `break_times.start_break_time`
- `break_times.end_break_time`

---

## 4. Propuesta de Solucion Tecnica

### 4.1 Formulas de Calculo

#### 4.1.1 Horas Totales del Turno
```
Horas Totales = end_time - start_time

Ejemplo:
start_time = 06:00
end_time = 14:00
Horas Totales = 14:00 - 06:00 = 8 horas (480 minutos)
```

#### 4.1.2 Total de Minutos de Descanso
```
Total Descansos = SUM(end_break_time - start_break_time) para cada BreakTime activo

Ejemplo:
Break 1: 09:00 - 09:15 = 15 min
Break 2: 12:00 - 12:15 = 15 min
Total Descansos = 30 minutos
```

#### 4.1.3 Horas Laborables Netas
```
Horas Netas = Horas Totales - Total Descansos

Ejemplo:
Horas Totales = 480 min
Total Descansos = 30 min
Horas Netas = 450 min = 7h 30min
```

### 4.2 Consideraciones Especiales

#### 4.2.1 Turnos Nocturnos (Cruce de Medianoche)
Si `end_time < start_time`, el turno cruza la medianoche:
```php
if ($endTime < $startTime) {
    // Turno nocturno: agregar 24 horas al end_time
    $totalMinutes = (24 * 60) - $startTime->diffInMinutes($endTime);
}
```

Ejemplo:
```
start_time = 22:00 (10 PM)
end_time = 06:00 (6 AM)
Calculo: (24:00 - 22:00) + 06:00 = 2h + 6h = 8 horas
```

#### 4.2.2 Solo Descansos Activos
Solo se consideran los breaks con `active = true` para el calculo.

#### 4.2.3 Formato de Salida
| Formato | Uso | Ejemplo |
|---------|-----|---------|
| `Xh` | Horas exactas | "8h" |
| `Xh Ym` | Horas con minutos | "7h 30m" |
| `X.Y hrs` | Decimal | "7.5 hrs" |
| `Xmin` | Solo minutos (< 1 hora) | "30min" |

**Formato recomendado**: `Xh Ym` para mejor legibilidad.

---

## 5. Implementacion Detallada

### 5.1 Modelo Shift - Nuevos Accessors

```php
<?php
// Archivo: app/Models/Shift.php

use Carbon\Carbon;

/**
 * Calcula las horas totales del turno en minutos.
 * Maneja turnos nocturnos que cruzan la medianoche.
 *
 * @return int Total de minutos del turno
 */
public function getTotalMinutesAttribute(): int
{
    $start = Carbon::parse($this->start_time);
    $end = Carbon::parse($this->end_time);

    // Si end_time es menor que start_time, el turno cruza la medianoche
    if ($end->lt($start)) {
        // Agregar un dia al end_time para calculo correcto
        $end->addDay();
    }

    return $start->diffInMinutes($end);
}

/**
 * Retorna las horas totales como numero decimal.
 *
 * @return float Horas totales (ej: 8.0, 7.5)
 */
public function getTotalHoursAttribute(): float
{
    return round($this->total_minutes / 60, 2);
}

/**
 * Calcula el total de minutos de descanso (solo breaks activos).
 *
 * @return int Total de minutos de descanso
 */
public function getTotalBreakMinutesAttribute(): int
{
    return $this->BreakTimes()
        ->where('active', true)
        ->get()
        ->sum(function ($break) {
            $start = Carbon::parse($break->start_break_time);
            $end = Carbon::parse($break->end_break_time);
            return $start->diffInMinutes($end);
        });
}

/**
 * Retorna el total de horas de descanso como decimal.
 *
 * @return float Horas de descanso (ej: 0.5)
 */
public function getTotalBreakHoursAttribute(): float
{
    return round($this->total_break_minutes / 60, 2);
}

/**
 * Calcula las horas laborables netas en minutos.
 *
 * @return int Minutos netos laborables
 */
public function getNetWorkingMinutesAttribute(): int
{
    return max(0, $this->total_minutes - $this->total_break_minutes);
}

/**
 * Retorna las horas laborables netas como decimal.
 *
 * @return float Horas netas (ej: 7.5)
 */
public function getNetWorkingHoursAttribute(): float
{
    return round($this->net_working_minutes / 60, 2);
}

/**
 * Formatea las horas totales del turno.
 * Formato: "Xh" o "Xh Ym"
 *
 * @return string Horas formateadas
 */
public function getFormattedTotalHoursAttribute(): string
{
    return $this->formatMinutesToHoursString($this->total_minutes);
}

/**
 * Formatea el total de horas de descanso.
 * Formato: "Xh Ym" o "Xm"
 *
 * @return string Tiempo de descanso formateado
 */
public function getFormattedBreakTimeAttribute(): string
{
    $minutes = $this->total_break_minutes;

    if ($minutes === 0) {
        return 'Sin descansos';
    }

    return $this->formatMinutesToHoursString($minutes);
}

/**
 * Formatea las horas laborables netas.
 * Formato: "Xh Ym"
 *
 * @return string Horas netas formateadas
 */
public function getFormattedNetWorkingHoursAttribute(): string
{
    return $this->formatMinutesToHoursString($this->net_working_minutes);
}

/**
 * Convierte minutos a string formateado "Xh Ym".
 *
 * @param int $minutes Total de minutos
 * @return string Formato "Xh Ym", "Xh", o "Ym"
 */
protected function formatMinutesToHoursString(int $minutes): string
{
    $hours = intdiv($minutes, 60);
    $mins = $minutes % 60;

    if ($hours === 0) {
        return "{$mins}m";
    }

    if ($mins === 0) {
        return "{$hours}h";
    }

    return "{$hours}h {$mins}m";
}
```

### 5.2 Modelo BreakTime - Nuevos Accessors

```php
<?php
// Archivo: app/Models/BreakTime.php

use Carbon\Carbon;

/**
 * Calcula la duracion del descanso en minutos.
 *
 * @return int Duracion en minutos
 */
public function getDurationMinutesAttribute(): int
{
    $start = Carbon::parse($this->start_break_time);
    $end = Carbon::parse($this->end_break_time);

    return $start->diffInMinutes($end);
}

/**
 * Formatea la duracion del descanso.
 * Formato: "Xh Ym" o "Xm"
 *
 * @return string Duracion formateada
 */
public function getFormattedDurationAttribute(): string
{
    $minutes = $this->duration_minutes;
    $hours = intdiv($minutes, 60);
    $mins = $minutes % 60;

    if ($hours === 0) {
        return "{$mins}m";
    }

    if ($mins === 0) {
        return "{$hours}h";
    }

    return "{$hours}h {$mins}m";
}
```

### 5.3 Vista ShiftList - Modificacion Columna Horario

```blade
{{-- Archivo: resources/views/livewire/admin/shifts/shift-list.blade.php --}}
{{-- Modificar la celda de la columna Horario (linea 176-181) --}}

<td class="px-6 py-4 whitespace-nowrap">
    <div class="text-sm text-gray-500 dark:text-gray-400">
        <div class="flex items-center">
            <span>
                {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} -
                {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
            </span>
            <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">
                {{ $shift->formatted_total_hours }}
            </span>
        </div>
    </div>
</td>
```

### 5.4 Vista ShiftShow - Nueva Seccion de Horas

```blade
{{-- Archivo: resources/views/livewire/admin/shifts/shift-show.blade.php --}}
{{-- Agregar despues de la seccion "Detalles del Turno" (despues de linea 202) --}}

<!-- Informacion de Horas del Turno -->
<div class="mt-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informacion de Horas</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Horas Totales del Turno -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-blue-500">
            <div class="px-4 py-5 sm:p-6">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Horas Totales
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold text-blue-600 dark:text-blue-400">
                        {{ $shift->formatted_total_hours }}
                    </dd>
                    <dd class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                    </dd>
                </dl>
            </div>
        </div>

        <!-- Tiempo de Descansos -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-orange-500">
            <div class="px-4 py-5 sm:p-6">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                        <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Tiempo de Descansos
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold text-orange-600 dark:text-orange-400">
                        {{ $shift->formatted_break_time }}
                    </dd>
                    <dd class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        {{ $shift->BreakTimes->where('active', true)->count() }} descanso(s) activo(s)
                    </dd>
                </dl>
            </div>
        </div>

        <!-- Horas Netas Laborables -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-green-500">
            <div class="px-4 py-5 sm:p-6">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Horas Laborables Netas
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold text-green-600 dark:text-green-400">
                        {{ $shift->formatted_net_working_hours }}
                    </dd>
                    <dd class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Tiempo efectivo de trabajo
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Detalle del Calculo -->
    <div class="mt-4 bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Detalle del Calculo</h4>
        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
            <p>
                <span class="font-medium">Turno completo:</span>
                {{ $shift->formatted_total_hours }}
                ({{ $shift->total_minutes }} minutos)
            </p>
            <p>
                <span class="font-medium">(-) Descansos:</span>
                {{ $shift->formatted_break_time }}
                ({{ $shift->total_break_minutes }} minutos)
            </p>
            <p class="pt-1 border-t border-gray-200 dark:border-gray-700">
                <span class="font-medium">(=) Tiempo neto:</span>
                {{ $shift->formatted_net_working_hours }}
                ({{ $shift->net_working_minutes }} minutos)
            </p>
        </div>
    </div>
</div>
```

### 5.5 Actualizacion Tabla de BreakTimes en ShiftShow

```blade
{{-- Reemplazar el calculo inline de duracion (lineas 594-599) por el accessor --}}

<td class="px-6 py-4 whitespace-nowrap">
    <div class="text-sm text-gray-500 dark:text-gray-400">
        {{ $breakTime->formatted_duration }}
    </div>
</td>
```

---

## 6. Casos de Prueba

### 6.1 Test Case: Turno Estandar

| Parametro | Valor |
|-----------|-------|
| start_time | 06:00 |
| end_time | 14:00 |
| Breaks | 09:00-09:15, 12:00-12:15 |

**Resultado esperado:**
- Horas totales: 8h (480 min)
- Tiempo descansos: 30m
- Horas netas: 7h 30m (450 min)

### 6.2 Test Case: Turno Nocturno

| Parametro | Valor |
|-----------|-------|
| start_time | 22:00 |
| end_time | 06:00 |
| Breaks | 02:00-02:30 |

**Resultado esperado:**
- Horas totales: 8h (480 min)
- Tiempo descansos: 30m
- Horas netas: 7h 30m (450 min)

### 6.3 Test Case: Sin Descansos

| Parametro | Valor |
|-----------|-------|
| start_time | 08:00 |
| end_time | 12:00 |
| Breaks | Ninguno |

**Resultado esperado:**
- Horas totales: 4h (240 min)
- Tiempo descansos: Sin descansos
- Horas netas: 4h (240 min)

### 6.4 Test Case: Descansos Inactivos

| Parametro | Valor |
|-----------|-------|
| start_time | 06:00 |
| end_time | 14:00 |
| Break activo | 09:00-09:15 (active=true) |
| Break inactivo | 12:00-12:30 (active=false) |

**Resultado esperado:**
- Horas totales: 8h
- Tiempo descansos: 15m (solo el activo)
- Horas netas: 7h 45m

---

## 7. Plan de Implementacion

### Fase 1: Backend - Modelo Shift (Prioridad Alta)
| Tarea | Complejidad | Estimacion |
|-------|-------------|------------|
| 1.1 Agregar accessor `getTotalMinutesAttribute` | Baja | 15 min |
| 1.2 Agregar accessor `getTotalHoursAttribute` | Baja | 5 min |
| 1.3 Agregar accessor `getTotalBreakMinutesAttribute` | Media | 20 min |
| 1.4 Agregar accessor `getTotalBreakHoursAttribute` | Baja | 5 min |
| 1.5 Agregar accessor `getNetWorkingMinutesAttribute` | Baja | 5 min |
| 1.6 Agregar accessor `getNetWorkingHoursAttribute` | Baja | 5 min |
| 1.7 Agregar accessor `getFormattedTotalHoursAttribute` | Baja | 10 min |
| 1.8 Agregar accessor `getFormattedBreakTimeAttribute` | Baja | 10 min |
| 1.9 Agregar accessor `getFormattedNetWorkingHoursAttribute` | Baja | 10 min |
| 1.10 Agregar metodo helper `formatMinutesToHoursString` | Baja | 10 min |
| **Subtotal Fase 1** | | **~1.5 horas** |

### Fase 2: Backend - Modelo BreakTime (Prioridad Media)
| Tarea | Complejidad | Estimacion |
|-------|-------------|------------|
| 2.1 Agregar accessor `getDurationMinutesAttribute` | Baja | 10 min |
| 2.2 Agregar accessor `getFormattedDurationAttribute` | Baja | 10 min |
| **Subtotal Fase 2** | | **~20 min** |

### Fase 3: Frontend - ShiftList (Prioridad Alta)
| Tarea | Complejidad | Estimacion |
|-------|-------------|------------|
| 3.1 Modificar columna Horario en vista | Baja | 15 min |
| 3.2 Verificar estilos y responsividad | Baja | 10 min |
| **Subtotal Fase 3** | | **~25 min** |

### Fase 4: Frontend - ShiftShow (Prioridad Alta)
| Tarea | Complejidad | Estimacion |
|-------|-------------|------------|
| 4.1 Agregar seccion "Informacion de Horas" | Media | 30 min |
| 4.2 Agregar card "Horas Totales" | Baja | 10 min |
| 4.3 Agregar card "Tiempo de Descansos" | Baja | 10 min |
| 4.4 Agregar card "Horas Netas" | Baja | 10 min |
| 4.5 Agregar detalle del calculo | Baja | 15 min |
| 4.6 Actualizar tabla de breaks para usar accessor | Baja | 10 min |
| **Subtotal Fase 4** | | **~1.25 horas** |

### Fase 5: Testing (Prioridad Alta)
| Tarea | Complejidad | Estimacion |
|-------|-------------|------------|
| 5.1 Unit tests para accessors de Shift | Media | 45 min |
| 5.2 Unit tests para accessors de BreakTime | Baja | 20 min |
| 5.3 Feature tests para ShiftList | Media | 30 min |
| 5.4 Feature tests para ShiftShow | Media | 30 min |
| 5.5 Tests de caso borde (turno nocturno) | Media | 20 min |
| **Subtotal Fase 5** | | **~2.5 horas** |

### Resumen de Estimacion

| Fase | Estimacion |
|------|------------|
| Fase 1: Modelo Shift | 1.5 horas |
| Fase 2: Modelo BreakTime | 20 min |
| Fase 3: ShiftList Vista | 25 min |
| Fase 4: ShiftShow Vista | 1.25 horas |
| Fase 5: Testing | 2.5 horas |
| **Total Estimado** | **~6 horas** |

---

## 8. Consideraciones de Performance

### 8.1 Eager Loading
Para evitar el problema N+1, asegurar que se carguen los BreakTimes:

```php
// En ShiftList
$shifts = Shift::with(['BreakTimes' => function($query) {
    $query->where('active', true);
}])
->withCount('employees')
->search($this->search)
->orderBy($this->sortField, $this->sortDirection)
->paginate($this->perPage);
```

### 8.2 Caching (Opcional)
Para turnos con muchos registros, considerar cache:

```php
public function getTotalBreakMinutesAttribute(): int
{
    return Cache::remember(
        "shift_{$this->id}_break_minutes",
        now()->addHour(),
        fn() => $this->calculateBreakMinutes()
    );
}
```

### 8.3 Invalidacion de Cache
Si se implementa cache, invalidar cuando se modifiquen breaks:

```php
// En BreakTime model
protected static function booted()
{
    static::saved(function ($break) {
        Cache::forget("shift_{$break->shift_id}_break_minutes");
    });

    static::deleted(function ($break) {
        Cache::forget("shift_{$break->shift_id}_break_minutes");
    });
}
```

---

## 9. Compatibilidad y Migracion

### 9.1 Retrocompatibilidad
- Los nuevos accessors son **aditivos**, no modifican funcionalidad existente
- El codigo existente seguira funcionando sin cambios
- Las vistas que no usen los nuevos accessors no se veran afectadas

### 9.2 Versionado
- No se requiere migracion de datos
- No se requieren cambios en API existentes
- Los nuevos accessors estan disponibles inmediatamente

---

## 10. Diagrama de Flujo del Calculo

```
+----------------+     +------------------+     +-------------------+
|   start_time   | --> |  Calcular diff   | --> |  total_minutes    |
|   end_time     |     |  (con nocturno)  |     |  (ej: 480)        |
+----------------+     +------------------+     +-------------------+
                                                         |
                                                         v
+----------------+     +------------------+     +-------------------+
|  BreakTimes    | --> |  SUM duraciones  | --> | total_break_mins  |
|  (active=1)    |     |  de cada break   |     |  (ej: 30)         |
+----------------+     +------------------+     +-------------------+
                                                         |
                                                         v
                                               +-------------------+
                                               | net_working_mins  |
                                               | = 480 - 30 = 450  |
                                               +-------------------+
                                                         |
                                                         v
                                               +-------------------+
                                               | Formatear salida  |
                                               | "7h 30m"          |
                                               +-------------------+
```

---

## 11. Mockups de UI

### 11.1 ShiftList - Columna Horario Actualizada

```
+----------+------------------+--------+-----------+---------+
| Nombre   | Horario          | Estado | Empleados | Acciones|
+----------+------------------+--------+-----------+---------+
| Turno 1  | 06:00 - 14:00 8h | Activo | 12        | Ver ... |
| Turno 2  | 14:00 - 22:00 8h | Activo | 8         | Ver ... |
| Turno 3  | 22:00 - 06:00 8h | Activo | 5         | Ver ... |
+----------+------------------+--------+-----------+---------+
```

### 11.2 ShiftShow - Seccion de Horas

```
+--------------------------------------------------+
| Informacion de Horas                              |
+--------------------------------------------------+
|                                                   |
| +---------------+ +---------------+ +------------+|
| | Horas Totales | | T. Descansos  | | Hrs Netas  ||
| |     8h        | |    30m        | |  7h 30m    ||
| | 06:00 - 14:00 | | 2 descanso(s) | | Efectivo   ||
| +---------------+ +---------------+ +------------+|
|                                                   |
| Detalle del Calculo:                              |
| Turno completo: 8h (480 minutos)                  |
| (-) Descansos: 30m (30 minutos)                   |
| (=) Tiempo neto: 7h 30m (450 minutos)             |
+--------------------------------------------------+
```

---

## 12. Archivos a Modificar

| Archivo | Tipo Cambio | Lineas Aprox |
|---------|-------------|--------------|
| `app/Models/Shift.php` | Agregar accessors | +80 lineas |
| `app/Models/BreakTime.php` | Agregar accessors | +25 lineas |
| `resources/views/livewire/admin/shifts/shift-list.blade.php` | Modificar columna | ~10 lineas |
| `resources/views/livewire/admin/shifts/shift-show.blade.php` | Nueva seccion | +70 lineas |
| `app/Livewire/Admin/Shifts/ShiftList.php` | Eager loading (opcional) | ~5 lineas |

---

## 13. Referencias

- Laravel Accessors/Mutators: https://laravel.com/docs/eloquent-mutators
- Carbon Date Library: https://carbon.nesbot.com/docs/
- Tailwind CSS: https://tailwindcss.com/docs
- Livewire 3: https://livewire.laravel.com/docs

---

## 14. Aprobaciones

| Rol | Nombre | Fecha | Firma |
|-----|--------|-------|-------|
| Arquitecto | Agent Architect | 2026-01-13 | Pendiente |
| Desarrollador | | | Pendiente |
| QA | | | Pendiente |

---

## Historial de Cambios

| Version | Fecha | Autor | Descripcion |
|---------|-------|-------|-------------|
| 1.0 | 2026-01-13 | Agent Architect | Documento inicial |
