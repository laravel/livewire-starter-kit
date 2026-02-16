<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityWeighing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lot_id',
        'kit_id',
        'production_good_pieces',
        'good_pieces',
        'bad_pieces',
        'disposition',
        'rework_status',
        'weighed_at',
        'weighed_by',
        'comments',
    ];

    protected $casts = [
        'production_good_pieces' => 'integer',
        'good_pieces' => 'integer',
        'bad_pieces' => 'integer',
        'weighed_at' => 'datetime',
    ];

    /**
     * Disposition constants
     */
    public const DISPOSITION_REWORK = 'rework';
    public const DISPOSITION_SCRAP = 'scrap';

    /**
     * Rework status constants
     */
    public const REWORK_PENDING = 'pending_rework';
    public const REWORK_IN_PROGRESS = 'in_rework';
    public const REWORK_COMPLETE = 'rework_complete';

    /**
     * Get the lot that owns the quality weighing.
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    /**
     * Get the kit associated with the quality weighing.
     */
    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class);
    }

    /**
     * Get the user who performed the quality weighing.
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

    /**
     * Check if this weighing has pending rework.
     */
    public function hasPendingRework(): bool
    {
        return $this->bad_pieces > 0 && $this->rework_status === self::REWORK_PENDING;
    }
}
