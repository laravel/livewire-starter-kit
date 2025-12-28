<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class KitIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'kit_id',
        'incident_type',
        'description',
        'fca_44_reference',
        'resolved',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /**
     * Incident type constants
     */
    public const TYPE_MISSING_PART = 'missing_part';
    public const TYPE_DAMAGED_PART = 'damaged_part';
    public const TYPE_WRONG_PART = 'wrong_part';
    public const TYPE_QUALITY_ISSUE = 'quality_issue';
    public const TYPE_OTHER = 'other';

    /**
     * Get the kit that owns the incident.
     */
    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class);
    }

    /**
     * Get the user who resolved the incident.
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope a query to only include unresolved incidents.
     */
    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->where('resolved', false);
    }

    /**
     * Scope a query to only include resolved incidents.
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('resolved', true);
    }

    /**
     * Scope a query to filter by incident type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('incident_type', $type);
    }

    /**
     * Scope a query to search incidents.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
              ->orWhere('fca_44_reference', 'like', "%{$search}%")
              ->orWhere('resolution_notes', 'like', "%{$search}%");
        });
    }

    /**
     * Get all available incident types.
     */
    public static function getIncidentTypes(): array
    {
        return [
            self::TYPE_MISSING_PART => 'Parte Faltante',
            self::TYPE_DAMAGED_PART => 'Parte Dañada',
            self::TYPE_WRONG_PART => 'Parte Incorrecta',
            self::TYPE_QUALITY_ISSUE => 'Problema de Calidad',
            self::TYPE_OTHER => 'Otro',
        ];
    }

    /**
     * Get the incident type label.
     */
    public function getIncidentTypeLabelAttribute(): string
    {
        return self::getIncidentTypes()[$this->incident_type] ?? $this->incident_type;
    }

    /**
     * Get the incident type color for UI display.
     */
    public function getIncidentTypeColorAttribute(): string
    {
        return match ($this->incident_type) {
            self::TYPE_MISSING_PART => 'red',
            self::TYPE_DAMAGED_PART => 'orange',
            self::TYPE_WRONG_PART => 'yellow',
            self::TYPE_QUALITY_ISSUE => 'purple',
            self::TYPE_OTHER => 'gray',
            default => 'gray',
        };
    }

    /**
     * Mark the incident as resolved.
     */
    public function resolve(int $userId, ?string $notes = null): void
    {
        $this->update([
            'resolved' => true,
            'resolved_by' => $userId,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }
}
