<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wo_number',
        'purchase_order_id',
        'sent_list_id',
        'assembly_mode',
        'required_hours',
        'status_id',
        'sent_pieces',
        'scheduled_send_date',
        'actual_send_date',
        'opened_date',
        'eq',
        'pr',
        'comments',
    ];

    protected $casts = [
        'scheduled_send_date' => 'date',
        'actual_send_date' => 'date',
        'opened_date' => 'date',
        'sent_pieces' => 'integer',
        'required_hours' => 'decimal:2',
    ];

    /**
     * Get the purchase order that owns the work order.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the status of the work order.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusWO::class, 'status_id');
    }


    /**
     * Get the lots for the work order.
     * Note: Lot model will be created in Phase 3
     */
    public function lots(): HasMany
    {
        // Return empty relation if Lot model doesn't exist yet
        if (!class_exists(\App\Models\Lot::class)) {
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        return $this->hasMany(\App\Models\Lot::class);
    }

    /**
     * Get the sent list that owns the work order.
     */
    public function sentList(): BelongsTo
    {
        return $this->belongsTo(SentList::class, 'sent_list_id');
    }

    /**
     * Get the status logs for the work order.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(WOStatusLog::class);
    }

    /**
     * Generate a unique WO number.
     * Format: WO-YYYY-XXXXX (year + sequential number)
     */
    public static function generateWONumber(): string
    {
        $year = Carbon::now()->year;
        $prefix = "WO-{$year}-";

        // Get the last WO number for this year
        $lastWO = static::withTrashed()
            ->where('wo_number', 'like', "{$prefix}%")
            ->orderByRaw('CAST(SUBSTRING(wo_number, -5) AS UNSIGNED) DESC')
            ->first();

        if ($lastWO) {
            // Extract the sequential number and increment
            $lastNumber = (int) substr($lastWO->wo_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeFilterByStatus(Builder $query, ?int $statusId): Builder
    {
        if (empty($statusId)) {
            return $query;
        }

        return $query->where('status_id', $statusId);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeFilterByDateRange(Builder $query, ?string $startDate, ?string $endDate): Builder
    {
        if ($startDate) {
            $query->where('opened_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('opened_date', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope a query to search work orders.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('wo_number', 'like', "%{$search}%")
              ->orWhereHas('purchaseOrder', function ($poQuery) use ($search) {
                  $poQuery->where('po_number', 'like', "%{$search}%");
              })
              ->orWhereHas('purchaseOrder.part', function ($partQuery) use ($search) {
                  $partQuery->where('number', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Get the original quantity from the purchase order.
     */
    public function getOriginalQuantityAttribute(): int
    {
        return $this->purchaseOrder->quantity ?? 0;
    }

    /**
     * Get the pending quantity (original - sent).
     */
    public function getPendingQuantityAttribute(): int
    {
        return max(0, $this->original_quantity - $this->sent_pieces);
    }

    /**
     * Check if the work order is complete.
     */
    public function isComplete(): bool
    {
        return $this->pending_quantity === 0;
    }

    /**
     * Check if this work order can be deleted.
     * Now allows deletion always (will cascade delete).
     */
    public function canBeDeleted(): bool
    {
        // Allow deletion always, will cascade to related records
        return true;
    }

    /**
     * Force delete this work order and all related records.
     */
    public function forceDeleteWithRelations(): bool
    {
        // Force delete related lots and their children
        foreach ($this->lots as $lot) {
            $lot->kits()->detach();
            $lot->weighings()->forceDelete();
            $lot->qualityWeighings()->forceDelete();
            $lot->forceDelete();
        }

        // Force delete kits belonging to this WO
        $this->kits()->forceDelete();

        // Delete status logs
        $this->statusLogs()->forceDelete();

        // Finally force delete the work order
        return $this->forceDelete();
    }

    /**
     * Update sent_pieces based on completed lots.
     */
    public function updateSentPieces(): void
    {
        $completedQuantity = $this->lots()
            ->where('status', Lot::STATUS_COMPLETED)
            ->sum('quantity');
        
        $this->update(['sent_pieces' => $completedQuantity]);
    }

    /**
     * Get the kits for the work order.
     */
    public function kits(): HasMany
    {
        return $this->hasMany(Kit::class);
    }
}
