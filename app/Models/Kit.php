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
        'submitted_to_quality_at',
        'approved_at',
        'approved_by',
        'current_approval_cycle',
    ];

    protected $casts = [
        'validated' => 'boolean',
        'submitted_to_quality_at' => 'datetime',
        'approved_at' => 'datetime',
        'current_approval_cycle' => 'integer',
    ];

    /**
     * Status constants
     */
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_READY = 'ready';
    public const STATUS_RELEASED = 'released';
    public const STATUS_IN_ASSEMBLY = 'in_assembly';
    public const STATUS_REJECTED = 'rejected';

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
     * Get the user who approved the kit.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the lots that were used to create this kit.
     */
    public function lots(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Lot::class, 'kit_lot')->withPivot('created_at');
    }

    /**
     * Get the approval cycles for this kit.
     */
    public function approvalCycles(): HasMany
    {
        return $this->hasMany(KitApprovalCycle::class);
    }

    /**
     * Get the audit trail for this kit.
     */
    public function auditTrail(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AuditTrail::class, 'auditable');
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
            self::STATUS_REJECTED => 'Rechazado',
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
            self::STATUS_REJECTED => 'red',
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
        $workOrder = WorkOrder::with('purchaseOrder')->find($workOrderId);
        $wo = $workOrder->purchaseOrder->wo ?? $workOrder->wo_number;
        $count = self::where('work_order_id', $workOrderId)->count() + 1;
        
        return sprintf('KIT-%s-%03d', $wo, $count);
    }

    /**
     * Check if the kit has unresolved incidents.
     */
    public function hasUnresolvedIncidents(): bool
    {
        return $this->incidents()->where('resolved', false)->exists();
    }

    /**
     * Check if the kit can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_PREPARING, self::STATUS_REJECTED]);
    }

    /**
     * Check if the kit can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return $this->status === self::STATUS_PREPARING && !$this->submitted_to_quality_at;
    }

    /**
     * Submit the kit to Quality for approval.
     */
    public function submitToQuality(User $user): void
    {
        $this->update([
            'status' => self::STATUS_READY,
            'submitted_to_quality_at' => now(),
        ]);

        // Create approval cycle
        $this->approvalCycles()->create([
            'cycle_number' => $this->current_approval_cycle,
            'submitted_by' => $user->id,
            'submitted_at' => now(),
            'status' => KitApprovalCycle::STATUS_PENDING,
        ]);
    }

    /**
     * Approve the kit.
     */
    public function approve(User $user, ?string $comments = null): void
    {
        $this->update([
            'status' => self::STATUS_RELEASED,
            'approved_at' => now(),
            'approved_by' => $user->id,
            'validated' => true,
        ]);

        // Update current approval cycle
        $cycle = $this->getCurrentApprovalCycle();
        if ($cycle) {
            $cycle->update([
                'status' => KitApprovalCycle::STATUS_APPROVED,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'comments' => $comments,
            ]);
        }
    }

    /**
     * Reject the kit.
     */
    public function reject(User $user, string $reason, ?string $comments = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
        ]);

        // Update current approval cycle
        $cycle = $this->getCurrentApprovalCycle();
        if ($cycle) {
            $cycle->update([
                'status' => KitApprovalCycle::STATUS_REJECTED,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
                'comments' => $comments,
            ]);
        }
    }

    /**
     * Get the full traceability chain from raw materials to kit.
     */
    public function getTraceabilityChain(): array
    {
        $lots = $this->lots()->with('workOrder')->get();

        return [
            'kit_number' => $this->kit_number,
            'status' => $this->status,
            'work_order' => $this->workOrder->wo_number ?? null,
            'prepared_by' => $this->preparedBy->name ?? null,
            'approved_by' => $this->approver->name ?? null,
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'lots' => $lots->map(function ($lot) {
                return [
                    'lot_number' => $lot->lot_number,
                    'raw_material_batch_numbers' => $lot->raw_material_batch_numbers ?? [],
                    'supplier_name' => $lot->supplier_name,
                    'receipt_date' => $lot->receipt_date?->format('Y-m-d'),
                    'expiration_date' => $lot->expiration_date?->format('Y-m-d'),
                    'quantity' => $lot->quantity,
                ];
            })->toArray(),
            'approval_cycles' => $this->approvalCycles()->with(['submitter', 'reviewer'])->get()->map(function ($cycle) {
                return [
                    'cycle_number' => $cycle->cycle_number,
                    'status' => $cycle->status,
                    'submitted_by' => $cycle->submitter->name ?? null,
                    'submitted_at' => $cycle->submitted_at?->format('Y-m-d H:i:s'),
                    'reviewed_by' => $cycle->reviewer->name ?? null,
                    'reviewed_at' => $cycle->reviewed_at?->format('Y-m-d H:i:s'),
                    'rejection_reason' => $cycle->rejection_reason,
                    'comments' => $cycle->comments,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get the current (active) approval cycle.
     */
    public function getCurrentApprovalCycle(): ?KitApprovalCycle
    {
        return $this->approvalCycles()
            ->where('cycle_number', $this->current_approval_cycle)
            ->first();
    }
}
