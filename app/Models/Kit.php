<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Kit extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'kit_number',
        'status',
        'validated',
        'validation_notes',
        'prepared_by',
        'released_by',
    ];

    protected $casts = [
        'validated' => 'boolean',
    ];

    /**
     * Status constants
     */
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_READY = 'ready';
    public const STATUS_RELEASED = 'released';
    public const STATUS_IN_ASSEMBLY = 'in_assembly';

    /**
     * Get the work order that owns the kit.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Get the user who prepared the kit.
     */
    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    /**
     * Get the user who released the kit.
     */
    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    /**
     * Get the incidents for this kit.
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(KitIncident::class);
    }

    /**
     * Scope a query to only include kits with a specific status.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include preparing kits.
     */
    public function scopePreparing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PREPARING);
    }

    /**
     * Scope a query to only include ready kits.
     */
    public function scopeReady(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_READY);
    }

    /**
     * Scope a query to only include released kits.
     */
    public function scopeReleased(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RELEASED);
    }

    /**
     * Scope a query to only include kits in assembly.
     */
    public function scopeInAssembly(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_ASSEMBLY);
    }

    /**
     * Scope a query to only include validated kits.
     */
    public function scopeValidated(Builder $query): Builder
    {
        return $query->where('validated', true);
    }

    /**
     * Scope a query to search kits.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('kit_number', 'like', "%{$search}%")
              ->orWhereHas('workOrder', function ($woQuery) use ($search) {
                  $woQuery->where('wo_number', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PREPARING => 'En Preparación',
            self::STATUS_READY => 'Listo',
            self::STATUS_RELEASED => 'Liberado',
            self::STATUS_IN_ASSEMBLY => 'En Ensamble',
        ];
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Get the status color for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PREPARING => 'yellow',
            self::STATUS_READY => 'blue',
            self::STATUS_RELEASED => 'green',
            self::STATUS_IN_ASSEMBLY => 'purple',
            default => 'gray',
        };
    }

    /**
     * Check if the kit can be marked as ready.
     */
    public function canBeReady(): bool
    {
        return $this->status === self::STATUS_PREPARING;
    }

    /**
     * Check if the kit can be released.
     */
    public function canBeReleased(): bool
    {
        return $this->status === self::STATUS_READY && $this->validated;
    }

    /**
     * Check if the kit can start assembly.
     */
    public function canStartAssembly(): bool
    {
        return $this->status === self::STATUS_RELEASED;
    }

    /**
     * Generate a unique kit number.
     */
    public static function generateKitNumber(int $workOrderId): string
    {
        $workOrder = WorkOrder::find($workOrderId);
        $count = self::where('work_order_id', $workOrderId)->count() + 1;
        
        return sprintf('KIT-%s-%03d', $workOrder->wo_number, $count);
    }

    /**
     * Check if the kit has unresolved incidents.
     */
    public function hasUnresolvedIncidents(): bool
    {
        return $this->incidents()->where('resolved', false)->exists();
    }
}
