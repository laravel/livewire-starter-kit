<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingSlipItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'packing_slip_id',
        'lot_id',
        'quantity_packed',
        'wo_number_ps',
        'lot_date_code',
        'label_spec',
        // Campos de Invoice FPL-12 (Fase 3):
        'unit_price',
        'price_tier_id',
        'price_source',
    ];

    protected $casts = [
        'quantity_packed' => 'integer',
        'unit_price'      => 'decimal:4',
    ];

    // =========================================================
    // Relaciones
    // =========================================================

    /**
     * Packing Slip al que pertenece este item.
     */
    public function packingSlip(): BelongsTo
    {
        return $this->belongsTo(PackingSlip::class);
    }

    /**
     * Lote incluido en este item.
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    /**
     * Tier de precio utilizado (para auditoria del Invoice).
     * NULL hasta que se genere el Invoice FPL-12 en Fase 3.
     */
    public function priceTier(): BelongsTo
    {
        return $this->belongsTo(PriceTier::class);
    }

    // =========================================================
    // Helpers del Invoice FPL-12
    // =========================================================

    /**
     * Calcula el total de esta linea para el Invoice (unit_price * quantity_packed).
     * Retorna NULL si el unit_price todavia no ha sido calculado (Fase 1/2).
     */
    public function getLineTotalAttribute(): ?float
    {
        if ($this->unit_price === null) {
            return null;
        }

        return (float) $this->unit_price * $this->quantity_packed;
    }

    /**
     * Etiqueta legible de la fuente del precio.
     */
    public function getPriceSourceLabelAttribute(): ?string
    {
        return match ($this->price_source) {
            'tier'   => 'Tier de cantidad',
            'sample' => 'Precio de muestra',
            'manual' => 'Ingreso manual',
            default  => null,
        };
    }
}
