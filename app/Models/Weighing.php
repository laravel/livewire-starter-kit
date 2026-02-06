<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Weighing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lot_id',
        'kit_id',
        'quantity',
        'good_pieces',
        'bad_pieces',
        'weighed_at',
        'weighed_by',
        'comments',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'good_pieces' => 'integer',
        'bad_pieces' => 'integer',
        'weighed_at' => 'datetime',
    ];

    /**
     * Get the lot that owns the weighing.
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    /**
     * Get the kit associated with the weighing.
     */
    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class);
    }

    /**
     * Get the user who performed the weighing.
     */
    public function weighedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'weighed_by');
    }

    /**
     * Get total pieces (good + bad).
     */
    public function getTotalPiecesAttribute(): int
    {
        return $this->good_pieces + $this->bad_pieces;
    }
}
