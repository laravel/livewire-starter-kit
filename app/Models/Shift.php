<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Shift extends Model
{
    /** @use HasFactory<\Database\Factories\ShiftFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'active',
        'comments',
    ];

    protected $casts = [
        'active' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Relationships with other models
     */

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


    // TODO: Uncomment when ProductionSession model is created
    // public function ProuctionSessions(): HasMany
    // {
    //     return $this->hasMany(ProductionSession::class);
    // }

    public function BreakTimes(): HasMany
    {
        return $this->hasMany(BreakTime::class);
    }

    /**
     * Get all overtime records for this shift
     */
    public function overTimes(): HasMany
    {
        return $this->hasMany(OverTime::class);
    }

    /**
     * Get overtime records for a specific date
     */
    public function overTimesForDate(Carbon $date): Collection
    {
        return $this->overTimes()
                    ->where('date', $date->toDateString())
                    ->get();
    }

    /**
     * Calculate total overtime hours for a date range
     */
    public function getTotalOvertimeHours(Carbon $startDate, Carbon $endDate): float
    {
        return $this->overTimes()
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get()
                    ->sum('total_hours');
    }

    /**
     * Scopes
     */

    // Solo turnos activos
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // Solo turnos inactivos
    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    // Buscar turnos por nombre
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
        ->orWhere('start_time', 'like', "%{$search}%")
        ->orWhere('end_time', 'like', "%{$search}%")
        ->orWhere('comments', 'like', "%{$search}%");
    }

    // Ordenar por campo dinámico
    public function scopeSortByField($query, $field = 'name', $direction = 'asc')
    {
        return $query->orderBy($field, $direction);
    }

    // Filtrar por rango de tiempo
    public function scopeByTimeRange($query, $startTime = null, $endTime = null)
    {
        if ($startTime) {
            $query->where('start_time', '>=', $startTime);
        }
        if ($endTime) {
            $query->where('end_time', '<=', $endTime);
        }
        return $query;
    }

    // Ordenar por hora de inicio
    public function scopeOrderByTime($query, $direction = 'asc')
    {
        return $query->orderBy('start_time', $direction);
    }

    /**
     * Auxiliar methods
     */

    // Método unificado para verificar si se puede eliminar
    public function canBeDeleted(): bool
    {
        return $this->allEmployees()->count() === 0
            // && $this->ProuctionSessions()->count() === 0  // TODO: Uncomment when ProductionSession model exists
            && $this->BreakTimes()->count() === 0
            && $this->overTimes()->count() === 0;
    }

    // Métodos individuales (mantener por compatibilidad)
    // TODO: Uncomment when ProductionSession model is created
    // public function canBeDeletedProductionSessions()
    // {
    //     return $this->ProuctionSessions()->count() == 0;
    // }

    public function canBeDeletedBreakTimes()
    {
        return $this->BreakTimes()->count() == 0;
    }

    //pending migratio  model and controller
    /* public function getStats()
    {
        return [
            'total_employees' => $this->Employees()->count(),
            'active_employees' => $this->Employees()->where('active', true)->count(),
        ];
    }

    public function getEmployeeStats()
    {
        return [
            'total_production_sessions' => $this->ProuctionSessions()->count(),
            'active_production_sessions' => $this->ProuctionSessions()->where('active', true)->count(),
            'total_break_times' => $this->BreakTimes()->count(),
            'active_break_times' => $this->BreakTimes()->where('active', true)->count(),
        ];
    } */

    /**
     * Hours Calculation Accessors
     * These accessors calculate shift hours, break times, and net working hours
     */

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
}
