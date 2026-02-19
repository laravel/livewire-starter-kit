<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Part extends Model
{
    /** @use HasFactory<\Database\Factories\PartFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number',
        'item_number',
        'unit_of_measure',
        'active',
        'is_crimp',
        'description',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_crimp' => 'boolean',
    ];

    /**
     * Get the prices for the part.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    /**
     * Get the standards for the part.
     */
    public function standards(): HasMany
    {
        return $this->hasMany(Standard::class);
    }

    /**
     * Get the purchase orders for the part.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get the active price for this part.
     */
    public function activePrice(): ?Price
    {
        return $this->prices()
            ->where('active', true)
            ->where('effective_date', '<=', now())
            ->orderBy('effective_date', 'desc')
            ->first();
    }

    /**
     * Get the active price for a specific workstation type.
     * 
     * @param string $workstationType One of: 'table', 'machine', 'semi_automatic'
     * @return Price|null
     */
    public function activePriceForWorkstationType(string $workstationType): ?Price
    {
        return $this->prices()
            ->activeForWorkstationType($workstationType)
            ->first();
    }

    /**
     * Get all prices grouped by workstation type.
     * 
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<Price>>
     */
    public function pricesByWorkstationType(): \Illuminate\Support\Collection
    {
        return $this->prices()
            ->with('tiers')
            ->get()
            ->groupBy('workstation_type');
    }

    /**
     * Check if this part has an active price for a specific workstation type.
     * 
     * @param string $workstationType One of: 'table', 'machine', 'semi_automatic'
     * @return bool
     */
    public function hasPriceForWorkstationType(string $workstationType): bool
    {
        return $this->activePriceForWorkstationType($workstationType) !== null;
    }

    /**
     * Scope a query to only include active parts.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to search parts.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('number', 'like', "%{$search}%")
              ->orWhere('item_number', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Check if this part can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return $this->prices()->count() === 0 && $this->purchaseOrders()->count() === 0;
    }
}
