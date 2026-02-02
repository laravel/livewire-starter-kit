<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Lot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_id',
        'lot_number',
        'description',
        'quantity',
        'status',
        'comments',
        'raw_material_batch_numbers',
        'supplier_id',
        'supplier_name',
        'receipt_date',
        'expiration_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'raw_material_batch_numbers' => 'array',
        'receipt_date' => 'date',
        'expiration_date' => 'date',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate lot_number when creating a new lot
        static::creating(function ($lot) {
            if (empty($lot->lot_number) && $lot->work_order_id) {
                $lot->lot_number = self::generateLotNumber($lot->work_order_id);
            }
        });

        // When a lot is created with completed status, update the work order's sent_pieces
        static::created(function ($lot) {
            if ($lot->status === self::STATUS_COMPLETED) {
                $lot->workOrder->updateSentPieces();
            }
        });

        // When a lot status changes, update the work order's sent_pieces
        static::updated(function ($lot) {
            if ($lot->isDirty('status')) {
                $lot->workOrder->updateSentPieces();
            }
        });

        // When a lot is deleted, update the work order's sent_pieces
        static::deleted(function ($lot) {
            if ($lot->status === self::STATUS_COMPLETED) {
                $lot->workOrder->updateSentPieces();
            }
        });

        // When a lot is restored, update the work order's sent_pieces
        static::restored(function ($lot) {
            if ($lot->status === self::STATUS_COMPLETED) {
                $lot->workOrder->updateSentPieces();
            }
        });
    }

    /**
     * Get the work order that owns the lot.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Get the kits that were created from this lot.
     */
    public function kits(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Kit::class, 'kit_lot')->withTimestamps();
    }

    /**
     * Get the audit trail for this lot.
     */
    public function auditTrail(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AuditTrail::class, 'auditable');
    }

    /**
     * Get the inspections for this lot.
     * NOTE: Inspection model not implemented yet
     */
    // public function inspections(): HasMany
    // {
    //     return $this->hasMany(Inspection::class);
    // }

    /**
     * Scope a query to only include lots with a specific status.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include pending lots.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include in progress lots.
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope a query to only include completed lots.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to search lots.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('lot_number', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
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
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_IN_PROGRESS => 'En Progreso',
            self::STATUS_COMPLETED => 'Completado',
            self::STATUS_CANCELLED => 'Cancelado',
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
            self::STATUS_PENDING => 'yellow',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
    }

    /**
     * Generate a sequential lot number for a work order.
     */
    public static function generateLotNumber(int $workOrderId): string
    {
        $count = self::withTrashed()
            ->where('work_order_id', $workOrderId)
            ->count() + 1;
        
        return sprintf('%03d', $count);
    }

    /**
     * Check if the lot can be started.
     */
    public function canBeStarted(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the lot can be completed.
     */
    public function canBeCompleted(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if the lot can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Check if the lot can be deleted.
     */
    public function canBeDeleted(): bool
    {
        // Cannot delete if lot has associated kits
        if ($this->kits()->exists()) {
            return false;
        }
        
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Get complete traceability data for this lot.
     */
    public function getTraceabilityData(): array
    {
        return [
            'lot_number' => $this->lot_number,
            'work_order' => $this->workOrder->wo_number ?? null,
            'raw_material_batch_numbers' => $this->raw_material_batch_numbers ?? [],
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier_name,
            'receipt_date' => $this->receipt_date?->format('Y-m-d'),
            'expiration_date' => $this->expiration_date?->format('Y-m-d'),
            'quantity' => $this->quantity,
            'status' => $this->status,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'kits' => $this->kits->map(fn($kit) => [
                'kit_number' => $kit->kit_number,
                'status' => $kit->status,
            ])->toArray(),
        ];
    }

    /**
     * Check if the lot has expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expiration_date) {
            return false;
        }

        return $this->expiration_date->isPast();
    }
}
