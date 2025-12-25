<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class OverTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'break_minutes',
        'employees_qty',
        'date',
        'shift_id',
        'comments',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'date' => 'date',
        'break_minutes' => 'integer',
        'employees_qty' => 'integer',
    ];

    /**
     * ================================================
     * RELATIONSHIPS
     * ================================================
     */

    /**
     * Turno al que pertenece este overtime
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * ================================================
     * SCOPES
     * ================================================
     */

    /**
     * Filtrar por rango de fechas
     */
    public function scopeByDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Filtrar por turno
     */
    public function scopeByShift($query, int $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    /**
     * Solo overtimes activos (fecha >= hoy)
     */
    public function scopeActive($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    /**
     * Solo overtimes pasados
     */
    public function scopePast($query)
    {
        return $query->where('date', '<', now()->toDateString());
    }

    /**
     * Buscar por nombre
     */
    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where('name', 'like', "%{$search}%")
                     ->orWhereHas('shift', function ($q) use ($search) {
                         $q->where('name', 'like', "%{$search}%");
                     });
    }

    /**
     * ================================================
     * BUSINESS LOGIC METHODS
     * ================================================
     */

    /**
     * Calcula las horas netas de trabajo (descontando descansos)
     *
     * Maneja correctamente overtimes que cruzan medianoche
     *
     * @return float Horas netas de trabajo
     */
    public function calculateNetHours(): float
    {
        // Obtener solo las horas y minutos, no la fecha completa
        $startTime = $this->getRawOriginal('start_time');
        $endTime = $this->getRawOriginal('end_time');

        $start = Carbon::createFromFormat('H:i:s', $startTime);
        $end = Carbon::createFromFormat('H:i:s', $endTime);

        // Manejar overtimes que cruzan medianoche
        // Ejemplo: 22:00 a 02:00 (4 horas, no -20 horas)
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $totalMinutes = $start->diffInMinutes($end, false);
        $netMinutes = $totalMinutes - $this->break_minutes;

        // Evitar horas negativas (validación defensiva)
        $netMinutes = max(0, $netMinutes);

        return round($netMinutes / 60, 2);
    }

    /**
     * Calcula las horas totales del overtime (horas netas × empleados)
     *
     * Este es el valor que se suma a la capacidad disponible
     *
     * @return float Horas-hombre totales
     */
    public function calculateTotalHours(): float
    {
        return $this->calculateNetHours() * $this->employees_qty;
    }

    /**
     * ================================================
     * ACCESSORS
     * ================================================
     */

    /**
     * Accessor para obtener horas totales como atributo
     * Uso: $overtime->total_hours
     */
    public function getTotalHoursAttribute(): float
    {
        return $this->calculateTotalHours();
    }

    /**
     * Accessor para obtener horas netas como atributo
     * Uso: $overtime->net_hours
     */
    public function getNetHoursAttribute(): float
    {
        return $this->calculateNetHours();
    }

    /**
     * ================================================
     * VALIDATION HELPERS
     * ================================================
     */

    /**
     * Verifica si el overtime está en el futuro
     */
    public function isFuture(): bool
    {
        return $this->date->isFuture();
    }

    /**
     * Verifica si el overtime es para hoy
     */
    public function isToday(): bool
    {
        return $this->date->isToday();
    }

    /**
     * Verifica si el overtime ya pasó
     */
    public function isPast(): bool
    {
        return $this->date->isPast();
    }
}
