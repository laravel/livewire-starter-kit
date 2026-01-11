<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'price_id',
        'min_quantity',
        'max_quantity',
        'tier_price',
    ];

    protected $casts = [
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'tier_price' => 'decimal:4',
    ];

    /**
     * Get the price that owns this tier.
     */
    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }

    /**
     * Check if a quantity falls within this tier.
     */
    public function matchesQuantity(int $quantity): bool
    {
        if ($quantity < $this->min_quantity) {
            return false;
        }

        // Si max_quantity es null, significa "sin límite" (ej: 100000+)
        if ($this->max_quantity === null) {
            return true;
        }

        return $quantity <= $this->max_quantity;
    }

    /**
     * Get tier label for display.
     */
    public function getLabelAttribute(): string
    {
        if ($this->max_quantity === null) {
            return number_format($this->min_quantity) . '+';
        }

        return number_format($this->min_quantity) . ' - ' . number_format($this->max_quantity);
    }
}
