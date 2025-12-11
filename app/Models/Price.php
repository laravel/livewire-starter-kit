<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'unit_price',
        'tier_1_999',
        'tier_1000_10999',
        'tier_11000_99999',
        'tier_100000_plus',
        'effective_date',
        'active',
        'comments',
    ];

    protected $casts = [
        'unit_price' => 'decimal:4',
        'tier_1_999' => 'decimal:4',
        'tier_1000_10999' => 'decimal:4',
        'tier_11000_99999' => 'decimal:4',
        'tier_100000_plus' => 'decimal:4',
        'effective_date' => 'date',
        'active' => 'boolean',
    ];

    /**
     * Get the part that owns the price.
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Scope a query to only include active prices.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include inactive prices.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('active', false);
    }

    /**
     * Scope a query to get the active price for a specific part.
     */
    public function scopeForPart(Builder $query, int $partId): Builder
    {
        return $query->where('part_id', $partId)
            ->where('active', true)
            ->where('effective_date', '<=', now())
            ->orderBy('effective_date', 'desc');
    }

    /**
     * Scope a query to search prices by part number or description.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->whereHas('part', function ($q) use ($search) {
            $q->where('number', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('item_number', 'like', "%{$search}%");
        });
    }

    /**
     * Get the price for a specific quantity based on pricing tiers.
     */
    public function getPriceForQuantity(int $quantity): ?float
    {
        if ($quantity < 1) {
            return null;
        }

        if ($quantity >= 100000 && $this->tier_100000_plus !== null) {
            return (float) $this->tier_100000_plus;
        }

        if ($quantity >= 11000 && $quantity <= 99999 && $this->tier_11000_99999 !== null) {
            return (float) $this->tier_11000_99999;
        }

        if ($quantity >= 1000 && $quantity <= 10999 && $this->tier_1000_10999 !== null) {
            return (float) $this->tier_1000_10999;
        }

        if ($quantity >= 1 && $quantity <= 999 && $this->tier_1_999 !== null) {
            return (float) $this->tier_1_999;
        }

        // Fallback to unit_price if no tier matches or tier is null
        return (float) $this->unit_price;
    }

    /**
     * Get the active price for a part.
     */
    public static function getActivePriceForPart(int $partId): ?self
    {
        return static::forPart($partId)->first();
    }

    /**
     * Check if this price can be deleted.
     */
    public function canBeDeleted(): bool
    {
        // Add any business logic here to prevent deletion
        // For now, always allow deletion
        return true;
    }
}
