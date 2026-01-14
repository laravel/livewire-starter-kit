<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakTime extends Model
{
    /** @use HasFactory<\Database\Factories\BreakTimeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'start_break_time',
        'end_break_time',
        'active',
        'comments',
        'shift_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'start_break_time' => 'datetime:H:i',
        'end_break_time' => 'datetime:H:i',
    ];

    /**
     * Relationships starts here
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Scope starts here
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
        ->orWhere('start_break_time', 'like', "%{$search}%")
        ->orWhere('end_break_time', 'like', "%{$search}%")
        ->orWhere('comments', 'like', "%{$search}%");
    }

    public function scopeSortByField($query, $field = 'name', $direction = 'asc')
    {
        return $query->orderBy($field, $direction);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    public function scopeByTimeRange($query, $startTime = null, $endTime = null)
    {
        if ($startTime) {
            $query->where('start_break_time', '>=', $startTime);
        }
        if ($endTime) {
            $query->where('end_break_time', '<=', $endTime);
        }
        return $query;
    }

    public function scopeOrderByTime($query, $direction = 'asc')
    {
        return $query->orderBy('start_break_time', $direction);
    }

    /**
     * Auxiliary methods starts here
     */
    public function canBeDeleted()
    {
        return true;
    }

    public function canBeUpdated()
    {
        return true;
    }

    /**
     * Duration Calculation Accessors
     */

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
}
