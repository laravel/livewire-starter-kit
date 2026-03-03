<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Kit;
use App\Models\QualityWeighing;
use App\Models\PackagingRecord;

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
        'inspection_status',
        'inspection_comments',
        'inspection_completed_at',
        'inspection_completed_by',
        'material_status',
        'packaging_status',
        'packaging_comments',
        'packaging_inspected_by',
        'packaging_inspected_at',
        'viajero_received',
        'viajero_received_at',
        'viajero_received_by',
        'closure_decision',
        'closure_decided_by',
        'closure_decided_at',
        'surplus_received',
        'surplus_received_at',
        'surplus_received_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'raw_material_batch_numbers' => 'array',
        'receipt_date' => 'date',
        'expiration_date' => 'date',
        'inspection_completed_at' => 'datetime',
        'packaging_inspected_at' => 'datetime',
        'viajero_received' => 'boolean',
        'viajero_received_at' => 'datetime',
        'closure_decided_at' => 'datetime',
        'surplus_received' => 'boolean',
        'surplus_received_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Inspection Status constants
     */
    public const INSPECTION_PENDING = 'pending';
    public const INSPECTION_APPROVED = 'approved';
    public const INSPECTION_REJECTED = 'rejected';

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
        return $this->belongsToMany(Kit::class, 'kit_lot')->withPivot('created_at');
    }

    /**
     * Get the weighings (pesadas) for this lot.
     */
    public function weighings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Weighing::class);
    }

    /**
     * Get the quality weighings for this lot.
     */
    public function qualityWeighings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(QualityWeighing::class);
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
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
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

    /**
     * Get all available inspection statuses.
     */
    public static function getInspectionStatuses(): array
    {
        return [
            self::INSPECTION_PENDING => 'Pendiente',
            self::INSPECTION_APPROVED => 'Aprobado',
            self::INSPECTION_REJECTED => 'No Aprobado',
        ];
    }

    /**
     * Get the inspection status label.
     */
    public function getInspectionStatusLabelAttribute(): string
    {
        return self::getInspectionStatuses()[$this->inspection_status] ?? $this->inspection_status;
    }

    /**
     * Get the inspection status color for UI display.
     */
    public function getInspectionStatusColorAttribute(): string
    {
        return match ($this->inspection_status) {
            self::INSPECTION_PENDING => 'yellow',
            self::INSPECTION_APPROVED => 'green',
            self::INSPECTION_REJECTED => 'red',
            default => 'gray',
        };
    }

    /**
     * Check if the lot can be inspected.
     * For crimp parts: requires an associated Kit with status "released".
     * For non-crimp parts: lote = kit, so inspection is allowed if material_status is "released".
     */
    public function canBeInspected(): bool
    {
        $isCrimp = (bool) ($this->workOrder->purchaseOrder->part->is_crimp ?? true);

        if (!$isCrimp) {
            // Non-crimp: lote = kit, allow inspection if material approved
            return ($this->material_status ?? 'pending') === 'released';
        }

        return $this->kits()
            ->where('status', Kit::STATUS_RELEASED)
            ->exists();
    }

    /**
     * Get the released kit associated with this lot (if any).
     */
    public function getReleasedKit(): ?Kit
    {
        return $this->kits()
            ->where('status', Kit::STATUS_RELEASED)
            ->first();
    }

    /**
     * Get the reason why inspection is blocked.
     */
    public function getInspectionBlockedReason(): ?string
    {
        if ($this->canBeInspected()) {
            return null;
        }

        $isCrimp = (bool) ($this->workOrder->purchaseOrder->part->is_crimp ?? true);

        if (!$isCrimp) {
            $matStatus = $this->material_status ?? 'pending';
            return match ($matStatus) {
                'pending' => 'El material de este lote aun no ha sido aprobado. Materiales debe aprobar el material primero.',
                'rejected' => 'El material de este lote fue rechazado. Materiales debe corregir y aprobar el material.',
                default => 'El material de este lote no tiene un status valido para inspeccion.',
            };
        }

        $kit = $this->kits()->first();

        if (!$kit) {
            return 'Este lote no tiene un kit asociado. Materiales debe crear un kit primero.';
        }

        return match ($kit->status) {
            Kit::STATUS_PREPARING => 'El kit esta en preparacion. Materiales debe completar y liberar el kit primero.',
            Kit::STATUS_READY => 'El kit esta listo pero aun no ha sido liberado por Materiales.',
            Kit::STATUS_REJECTED => 'El kit fue rechazado. Materiales debe corregir y re-liberar el kit.',
            Kit::STATUS_IN_ASSEMBLY => 'El kit ya esta en ensamble.',
            default => 'El kit no tiene un status valido para inspeccion.',
        };
    }

    /**
     * Scope a query to only include lots with pending inspection.
     */
    public function scopeInspectionPending($query)
    {
        return $query->where('inspection_status', self::INSPECTION_PENDING);
    }

    /**
     * Scope a query to only include lots with approved inspection.
     */
    public function scopeInspectionApproved($query)
    {
        return $query->where('inspection_status', self::INSPECTION_APPROVED);
    }

    /**
     * Scope a query to only include lots with rejected inspection.
     */
    public function scopeInspectionRejected($query)
    {
        return $query->where('inspection_status', self::INSPECTION_REJECTED);
    }

    /**
     * Relationship with the user who completed the inspection.
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspection_completed_by');
    }

    /**
     * Check if inspection is pending.
     */
    public function isInspectionPending(): bool
    {
        return $this->inspection_status === self::INSPECTION_PENDING;
    }

    /**
     * Check if inspection is approved.
     */
    public function isInspectionApproved(): bool
    {
        return $this->inspection_status === self::INSPECTION_APPROVED;
    }

    /**
     * Check if inspection is rejected.
     */
    public function isInspectionRejected(): bool
    {
        return $this->inspection_status === self::INSPECTION_REJECTED;
    }

    /**
     * Check if lot can proceed to packing/shipping (must be inspection approved).
     */
    public function canProceedToShipping(): bool
    {
        return $this->isInspectionApproved() && $this->status === self::STATUS_COMPLETED;
    }

    // =====================================================
    // QUALITY WEIGHING HELPERS
    // =====================================================

    /**
     * Get total good pieces from production weighings for this lot.
     */
    public function getProductionGoodPieces(): int
    {
        return (int) $this->weighings()->sum('good_pieces');
    }

    /**
     * Get total bad pieces from production weighings for this lot.
     */
    public function getProductionBadPieces(): int
    {
        return (int) $this->weighings()->sum('bad_pieces');
    }

    /**
     * Get total pieces already weighed by production.
     */
    public function getProductionTotalWeighed(): int
    {
        return $this->getProductionGoodPieces() + $this->getProductionBadPieces();
    }

    /**
     * Get total pieces already verified by quality (good + bad).
     */
    public function getQualityAlreadyWeighed(): int
    {
        return (int) $this->qualityWeighings()
            ->selectRaw('COALESCE(SUM(good_pieces), 0) + COALESCE(SUM(bad_pieces), 0) as total')
            ->value('total');
    }

    /**
     * Get pieces pending quality verification.
     * = Production good pieces - Quality already weighed
     */
    public function getQualityPendingPieces(): int
    {
        return max(0, $this->getProductionGoodPieces() - $this->getQualityAlreadyWeighed());
    }

    /**
     * Get total quality approved pieces.
     */
    public function getQualityGoodPieces(): int
    {
        return (int) $this->qualityWeighings()->sum('good_pieces');
    }

    /**
     * Get total quality rejected pieces.
     */
    public function getQualityBadPieces(): int
    {
        return (int) $this->qualityWeighings()->sum('bad_pieces');
    }

    /**
     * Get pieces pending rework (deprecated - rework removed, rejected = discard).
     */
    public function getReworkPendingPieces(): int
    {
        return 0;
    }

    /**
     * Get the quality semaphore status.
     * gray = no production weighings
     * yellow = pending (production has weighings, quality hasn't verified all)
     * green = all production good pieces verified by quality
     */
    public function getQualitySemaphoreStatus(): string
    {
        $prodGood = $this->getProductionGoodPieces();

        if ($prodGood <= 0) {
            return 'gray';
        }

        $qualityWeighed = $this->getQualityAlreadyWeighed();

        if ($qualityWeighed <= 0) {
            return 'yellow';
        }

        if ($qualityWeighed >= $prodGood) {
            return 'green';
        }

        return 'yellow';
    }

    /**
     * Check if this lot has production weighings available for quality inspection.
     */
    public function hasProductionWeighings(): bool
    {
        return $this->weighings()->exists();
    }

    // =====================================================
    // PACKAGING HELPERS
    // =====================================================

    /**
     * Get the packaging records for this lot.
     */
    public function packagingRecords(): HasMany
    {
        return $this->hasMany(PackagingRecord::class);
    }

    /**
     * Relationship: viajero received by user.
     */
    public function viajeroReceivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viajero_received_by');
    }

    /**
     * Relationship: closure decided by user.
     */
    public function closureDecidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closure_decided_by');
    }

    /**
     * Relationship: surplus received by user.
     */
    public function surplusReceivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surplus_received_by');
    }

    /**
     * Get pieces available for packaging (quality approved pieces).
     */
    public function getPackagingAvailablePieces(): int
    {
        return $this->getQualityGoodPieces();
    }

    /**
     * Get total packed pieces across all packaging records.
     */
    public function getPackagingPackedPieces(): int
    {
        return (int) $this->packagingRecords()->sum('packed_pieces');
    }

    /**
     * Get pieces pending packaging.
     */
    public function getPackagingPendingPieces(): int
    {
        return max(0, $this->getPackagingAvailablePieces() - $this->getPackagingPackedPieces());
    }

    /**
     * Get total effective surplus: available − packed, minus any manual adjustment deltas.
     */
    public function getPackagingTotalSurplus(): int
    {
        $surplus = $this->getPackagingPendingPieces(); // available − packed

        // Apply manual adjustment deltas (original − adjusted) from recounts
        $adjustmentDelta = $this->packagingRecords
            ->filter(fn ($r) => $r->adjusted_surplus !== null)
            ->sum(fn ($r) => $r->surplus_pieces - $r->adjusted_surplus);

        return max(0, $surplus - $adjustmentDelta);
    }

    /**
     * Check if lot has any packaging records.
     */
    public function hasPackagingRecords(): bool
    {
        return $this->packagingRecords()->exists();
    }

    /**
     * Check if viajero has been received.
     */
    public function isViajeroReceived(): bool
    {
        return (bool) $this->viajero_received;
    }

    /**
     * Check if a closure decision has been made.
     */
    public function hasClosureDecision(): bool
    {
        return !is_null($this->closure_decision);
    }

    /**
     * Check if surplus has been received by Control de Materiales.
     */
    public function isSurplusReceived(): bool
    {
        return (bool) $this->surplus_received;
    }

    /**
     * Get the packaging semaphore status.
     * gray = quality hasn't approved any pieces yet
     * yellow = pieces available but not all packed
     * green = fully packed & viajero received & closed
     * blue = viajero received, pending closure decision
     * orange = closed with surplus, pending material reception
     */
    public function getPackagingSemaphoreStatus(): string
    {
        $available = $this->getPackagingAvailablePieces();
        if ($available <= 0) {
            return 'gray';
        }

        if ($this->isSurplusReceived()) {
            return 'green';
        }

        if (in_array($this->closure_decision, ['close_as_is', 'new_lot']) && !$this->isSurplusReceived()) {
            return 'orange';
        }

        if ($this->hasClosureDecision()) {
            return 'green';
        }

        if ($this->isViajeroReceived()) {
            return 'blue';
        }

        $packed = $this->getPackagingPackedPieces();
        if ($packed <= 0) {
            return 'yellow';
        }

        if ($packed >= $available && !$this->isViajeroReceived()) {
            return 'yellow';
        }

        return 'yellow';
    }

    /**
     * Closure decision constants.
     */
    public const CLOSURE_COMPLETE_LOT = 'complete_lot';
    public const CLOSURE_NEW_LOT = 'new_lot';
    public const CLOSURE_CLOSE_AS_IS = 'close_as_is';
}
