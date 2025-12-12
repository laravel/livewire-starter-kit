<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WOStatusLog extends Model
{
    use HasFactory;

    protected $table = 'wo_status_logs';

    protected $fillable = [
        'work_order_id',
        'from_status_id',
        'to_status_id',
        'user_id',
        'comments',
    ];

    /**
     * Get the work order that owns the log.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Get the previous status.
     */
    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(StatusWO::class, 'from_status_id');
    }

    /**
     * Get the new status.
     */
    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(StatusWO::class, 'to_status_id');
    }

    /**
     * Get the user who made the change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
