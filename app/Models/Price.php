<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Price extends Model
{
    use HasFactory;

    // Constantes para tipos de estación de trabajo
    public const WORKSTATION_TABLE = 'table';
    public const WORKSTATION_MACHINE = 'machine';
    public const WORKSTATION_SEMI_AUTOMATIC = 'semi_automatic';

    public const WORKSTATION_TYPES = [
        self::WORKSTATION_TABLE => 'Mesa de Trabajo',
        self::WORKSTATION_MACHINE => 'Máquina',
        self::WORKSTATION_SEMI_AUTOMATIC => 'Semi-Automática',
    ];

    // Configuración de tiers por tipo de estación
    public const TIER_CONFIG = [
        self::WORKSTATION_TABLE => [
            ['min' => 1, 'max' => 999, 'label' => '1-999'],
            ['min' => 1000, 'max' => 10999, 'label' => '1,000-10,999'],
            ['min' => 11000, 'max' => 99999, 'label' => '11,000-99,999'],
            ['min' => 100000, 'max' => null, 'label' => '100,000+'],
        ],
        self::WORKSTATION_MACHINE => [
            ['min' => 1, 'max' => 9999, 'label' => '1-9,999'],
            ['min' => 10000, 'max' => 49999, 'label' => '10,000-49,999'],
            ['min' => 50000, 'max' => null, 'label' => '50,000+'],
        ],
        self::WORKSTATION_SEMI_AUTOMATIC => [
            ['min' => 2000, 'max' => 10000, 'label' => '2,000-10,000'],
            ['min' => 11000, 'max' => null, 'label' => '11,000+'],
        ],
    ];

    protected $fillable = [
        'part_id',
        'sample_price',
        'workstation_type',
        'effective_date',
        'active',
        'comments',
    ];

    protected $casts = [
        'sample_price' => 'decimal:4',
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
     * Get the price tiers for this price.
     */
    public function tiers(): HasMany
    {
        return $this->hasMany(PriceTier::class)->orderBy('min_quantity');
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
     * Scope a query to filter by workstation type.
     */
    public function scopeForWorkstationType(Builder $query, string $type): Builder
    {
        return $query->where('workstation_type', $type);
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

        // Buscar el tier que coincida con la cantidad
        $tier = $this->tiers()
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($query) use ($quantity) {
                $query->whereNull('max_quantity')
                      ->orWhere('max_quantity', '>=', $quantity);
            })
            ->first();

        if ($tier) {
            return (float) $tier->tier_price;
        }

        // Fallback al precio de muestra
        return (float) $this->sample_price;
    }

    /**
     * Get the active price for a part.
     */
    public static function getActivePriceForPart(int $partId): ?self
    {
        return static::forPart($partId)->first();
    }

    /**
     * Get workstation type label.
     */
    public function getWorkstationTypeLabelAttribute(): string
    {
        return self::WORKSTATION_TYPES[$this->workstation_type] ?? $this->workstation_type;
    }

    /**
     * Get tier configuration for this price's workstation type.
     */
    public function getTierConfigAttribute(): array
    {
        return self::TIER_CONFIG[$this->workstation_type] ?? [];
    }

    /**
     * Get tier configuration for a specific workstation type.
     */
    public static function getTierConfigForType(string $type): array
    {
        return self::TIER_CONFIG[$type] ?? [];
    }

    /**
     * Sync tiers from an array of prices.
     * @param array $tierPrices Array of tier prices indexed by tier index
     */
    public function syncTiers(array $tierPrices): void
    {
        $config = $this->tier_config;
        
        // Eliminar tiers existentes
        $this->tiers()->delete();
        
        // Crear nuevos tiers
        foreach ($config as $index => $tierConfig) {
            $price = $tierPrices[$index] ?? null;
            
            if ($price !== null && $price !== '') {
                $this->tiers()->create([
                    'min_quantity' => $tierConfig['min'],
                    'max_quantity' => $tierConfig['max'],
                    'tier_price' => $price,
                ]);
            }
        }
    }

    /**
     * Get tiers as an indexed array for form binding.
     */
    public function getTiersArrayAttribute(): array
    {
        $config = $this->tier_config;
        $result = [];
        
        foreach ($config as $index => $tierConfig) {
            $tier = $this->tiers
                ->first(fn ($t) => $t->min_quantity == $tierConfig['min']);
            
            $result[$index] = $tier ? (string) $tier->tier_price : '';
        }
        
        return $result;
    }

    /**
     * Check if this price can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return true;
    }
}
