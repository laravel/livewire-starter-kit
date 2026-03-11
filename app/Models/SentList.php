<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SentList extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sent_lists';

    protected $fillable = [
        'po_id',
        'shift_ids',
        'num_persons',
        'start_date',
        'end_date',
        'total_available_hours',
        'used_hours',
        'remaining_hours',
        'status',
        'current_department',
        'department_history',
        'materials_approved_at',
        'materials_approved_by',
        'inspection_approved_at',
        'inspection_approved_by',
        'production_approved_at',
        'production_approved_by',
        'quality_approved_at',
        'quality_approved_by',
        'shipping_approved_at',
        'shipping_approved_by',
        'notes',
    ];

    protected $casts = [
        'shift_ids' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'total_available_hours' => 'decimal:2',
        'used_hours' => 'decimal:2',
        'remaining_hours' => 'decimal:2',
        'department_history' => 'array',
        'materials_approved_at' => 'datetime',
        'inspection_approved_at' => 'datetime',
        'production_approved_at' => 'datetime',
        'quality_approved_at' => 'datetime',
        'shipping_approved_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELED = 'canceled';

    /**
     * Department constants — order matters for flow
     */
    public const DEPT_MATERIALS = 'materiales';
    public const DEPT_INSPECTION = 'inspeccion';
    public const DEPT_PRODUCTION = 'produccion';
    public const DEPT_QUALITY = 'calidad';
    public const DEPT_SHIPPING = 'envios';

    /**
     * Get the purchase order that owns the sent list.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    /**
     * Get the work orders for the sent list.
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'sent_list_id');
    }

    /**
     * Returns all WOs for this SentList, merging:
     * - WOs directly linked via sent_list_id (new flow)
     * - WOs belonging to POs in the pivot (legacy flow where sent_list_id was never set)
     * Assumes purchaseOrders.workOrder and workOrders relationships are already loaded.
     */
    public function getEffectiveWorkOrders(): \Illuminate\Support\Collection
    {
        $direct = $this->workOrders ?? collect();
        $pivot  = $this->purchaseOrders
            ? $this->purchaseOrders->map->workOrder->filter()->values()
            : collect();

        return $direct->merge($pivot)->unique('id')->values();
    }

    /**
     * Get the shifts for the sent list (many-to-many).
     */
    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'sent_list_shift');
    }

    /**
     * Get the purchase orders for this sent list (many-to-many).
     */
    public function purchaseOrders(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseOrder::class, 'sent_list_purchase_orders')
            ->withPivot(['quantity', 'required_hours', 'lot_number'])
            ->withTimestamps();
    }

    /**
     * Get the users who approved at each department.
     */
    public function materialsApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'materials_approved_by');
    }

    public function productionApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'production_approved_by');
    }

    public function inspectionApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspection_approved_by');
    }

    public function qualityApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quality_approved_by');
    }

    public function shippingApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipping_approved_by');
    }

    /**
     * Get all rejection records for this sent list.
     */
    public function rejections(): HasMany
    {
        return $this->hasMany(SentListRejection::class);
    }

    /**
     * Get unresolved rejections (pending correction by the receiving department).
     */
    public function unresolvedRejections(): HasMany
    {
        return $this->hasMany(SentListRejection::class)->whereNull('resolved_at');
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CANCELED => 'Canceled',
        ];
    }

    /**
     * Get all available departments in order.
     */
    public static function getDepartments(): array
    {
        return [
            self::DEPT_MATERIALS  => 'Materiales',
            self::DEPT_INSPECTION => 'Inspección',
            self::DEPT_PRODUCTION => 'Producción',
            self::DEPT_QUALITY    => 'Calidad',
            self::DEPT_SHIPPING   => 'Empaque',
        ];
    }

    /**
     * Get the department label.
     */
    public function getDepartmentLabelAttribute(): string
    {
        return self::getDepartments()[$this->current_department] ?? $this->current_department;
    }

    /**
     * Check if the list is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Move to next department in workflow.
     * Flow: Materiales → Inspección → Producción → Calidad → Empaque
     */
    public function moveToNextDepartment(?int $userId = null): bool
    {
        $flow = [
            self::DEPT_MATERIALS  => self::DEPT_INSPECTION,
            self::DEPT_INSPECTION => self::DEPT_PRODUCTION,
            self::DEPT_PRODUCTION => self::DEPT_QUALITY,
            self::DEPT_QUALITY    => self::DEPT_SHIPPING,
        ];

        $currentDept = $this->current_department;

        if (!isset($flow[$currentDept])) {
            return false; // Ya está en el último departamento
        }

        $approvalField = match($currentDept) {
            self::DEPT_MATERIALS  => 'materials_approved',
            self::DEPT_INSPECTION => 'inspection_approved',
            self::DEPT_PRODUCTION => 'production_approved',
            self::DEPT_QUALITY    => 'quality_approved',
            default => null,
        };

        $updates = ['current_department' => $flow[$currentDept]];

        if ($approvalField && $userId) {
            $updates["{$approvalField}_at"] = now();
            $updates["{$approvalField}_by"] = $userId;
        }

        $history = $this->department_history ?? [];
        $history[] = [
            'from'      => $currentDept,
            'to'        => $flow[$currentDept],
            'action'    => 'approved',
            'user_id'   => $userId,
            'timestamp' => now()->toISOString(),
        ];
        $updates['department_history'] = $history;

        return $this->update($updates);
    }

    /**
     * Move back to a previous department (rejection flow).
     * Records a rejection log entry.
     */
    public function moveToPreviousDepartment(string $targetDept, int $userId, string $reason, ?int $lotId = null): bool
    {
        $currentDept = $this->current_department;

        // Create rejection record
        $this->rejections()->create([
            'from_department' => $currentDept,
            'to_department'   => $targetDept,
            'rejected_by'     => $userId,
            'reason'          => $reason,
            'lot_id'          => $lotId,
        ]);

        $history = $this->department_history ?? [];
        $history[] = [
            'from'      => $currentDept,
            'to'        => $targetDept,
            'action'    => 'rejected',
            'user_id'   => $userId,
            'reason'    => $reason,
            'timestamp' => now()->toISOString(),
        ];

        return $this->update([
            'current_department' => $targetDept,
            'department_history' => $history,
        ]);
    }

    /**
     * Check if department can edit this list.
     */
    public function canDepartmentEdit(string $department): bool
    {
        return $this->current_department === $department && 
               $this->status !== self::STATUS_CONFIRMED &&
               $this->status !== self::STATUS_CANCELED;
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
            self::STATUS_CONFIRMED => 'green',
            self::STATUS_PENDING => 'yellow',
            self::STATUS_CANCELED => 'red',
            default => 'gray',
        };
    }

    /**
     * Scope a query to only include pending sent lists.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include confirmed sent lists.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope a query to only include canceled sent lists.
     */
    public function scopeCanceled($query)
    {
        return $query->where('status', self::STATUS_CANCELED);
    }

    /**
     * Check if the sent list is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the sent list can be deleted.
     */
    public function canBeDeleted(): bool
    {
        // Can only delete pending lists
        return $this->isPending() && $this->workOrders()->count() === 0;
    }

    /**
     * Get capacity utilization percentage.
     */
    public function getCapacityUtilizationAttribute(): float
    {
        if ($this->total_available_hours <= 0) {
            return 0;
        }

        return round(($this->used_hours / $this->total_available_hours) * 100, 2);
    }

    /**
     * Check if the sent list is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }
}
