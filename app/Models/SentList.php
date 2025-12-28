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
    ];

    protected $casts = [
        'shift_ids' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'total_available_hours' => 'decimal:2',
        'used_hours' => 'decimal:2',
        'remaining_hours' => 'decimal:2',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELED = 'canceled';

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
     * Get the shifts for the sent list (many-to-many).
     */
    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'sent_list_shift');
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
     * Check if the sent list is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if the sent list is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the sent list is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * Calculate capacity utilization percentage.
     */
    public function getCapacityUtilizationAttribute(): float
    {
        if ($this->total_available_hours == 0) {
            return 0;
        }

        return round(($this->used_hours / $this->total_available_hours) * 100, 2);
    }

    /**
     * Check if this sent list can be deleted.
     */
    public function canBeDeleted(): bool
    {
        // Cannot delete confirmed sent lists
        return !$this->isConfirmed();
    }
}
