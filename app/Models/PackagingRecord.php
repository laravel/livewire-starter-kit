<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagingRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lot_id',
        'kit_id',
        'available_pieces',
        'packed_pieces',
        'surplus_pieces',
        'adjusted_surplus',
        'adjustment_reason',
        'comments',
        'packed_at',
        'packed_by',
    ];

    protected $casts = [
        'available_pieces' => 'integer',
        'packed_pieces' => 'integer',
        'surplus_pieces' => 'integer',
        'adjusted_surplus' => 'integer',
        'packed_at' => 'datetime',
    ];

    /**
     * Get the lot that owns the packaging record.
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    /**
     * Get the kit associated with the packaging record.
     */
    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class);
    }

    /**
     * Get the user who packed.
     */
    public function packedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packed_by');
    }

    /**
     * Get effective surplus (adjusted if available, otherwise original).
     */
    public function getEffectiveSurplusAttribute(): int
    {
        return $this->adjusted_surplus ?? $this->surplus_pieces;
    }
}
