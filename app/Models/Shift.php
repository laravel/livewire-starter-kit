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

}
