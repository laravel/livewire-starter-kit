<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number',
        'wo',
        'part_id',
        'po_date',
        'due_date',
        'quantity',
        'unit_price',
        'status',
        'comments',
        'pdf_path',
    ];

    protected $casts = [
        'po_date' => 'date',
        'due_date' => 'date',
        'quantity' => 'integer',
        'unit_price' => 'decimal:4',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PENDING_CORRECTION = 'pending_correction';

    /**
     * Get the part that owns the purchase order.
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Get the work order associated with this purchase order.
     */
    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }

    /**
     * Get the sent lists that include this purchase order (many-to-many).
     */
    public function sentLists(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(SentList::class, 'sent_list_purchase_orders')
            ->withPivot(['quantity', 'required_hours', 'lot_number'])
            ->withTimestamps();
    }

    /**
     * Get the signatures for this purchase order.
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(DocumentSignature::class);
    }

    /**
     * Check if the purchase order has been signed.
     */
    public function isSigned(): bool
    {
        return $this->signatures()->exists();
    }

    /**
     * Scope a query to only include pending purchase orders.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include approved purchase orders.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope a query to only include rejected purchase orders.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope a query to only include purchase orders pending price correction.
     */
    public function scopePendingCorrection(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_CORRECTION);
    }

    /**
     * Scope a query to search purchase orders.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('po_number', 'like', "%{$search}%")
              ->orWhereHas('part', function ($partQuery) use ($search) {
                  $partQuery->where('number', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeFilterByStatus(Builder $query, ?string $status): Builder
    {
        if (empty($status) || $status === 'all') {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Check if the purchase order can be approved.
     */
    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the purchase order has a work order.
     */
    public function hasWorkOrder(): bool
    {
        return $this->workOrder()->exists();
    }

    /**
     * Check if this purchase order can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return !$this->hasWorkOrder();
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_APPROVED => 'Aprobada',
            self::STATUS_REJECTED => 'Rechazada',
            self::STATUS_PENDING_CORRECTION => 'Corrección de Precio',
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
            self::STATUS_APPROVED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_PENDING_CORRECTION => 'orange',
            default => 'gray',
        };
    }
}
