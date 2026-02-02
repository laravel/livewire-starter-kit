<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class KitApprovalCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'kit_id',
        'cycle_number',
        'submitted_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'status',
        'comments',
        'rejection_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Get the kit that owns the approval cycle.
     */
    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class);
    }

    /**
     * Get the user who submitted the kit.
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the user who reviewed the kit.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if the cycle is pending review.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the cycle was approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the cycle was rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Get the duration from submission to review.
     */
    public function getDuration(): ?string
    {
        if (!$this->reviewed_at) {
            return null;
        }

        $duration = $this->submitted_at->diff($this->reviewed_at);
        
        if ($duration->days > 0) {
            return $duration->days . ' day' . ($duration->days > 1 ? 's' : '');
        }
        
        if ($duration->h > 0) {
            return $duration->h . ' hour' . ($duration->h > 1 ? 's' : '');
        }
        
        return $duration->i . ' minute' . ($duration->i > 1 ? 's' : '');
    }
}
