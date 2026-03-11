<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SentListRejection extends Model
{
    use HasFactory;

    protected $fillable = [
        'sent_list_id',
        'from_department',
        'to_department',
        'rejected_by',
        'reason',
        'lot_id',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function sentList(): BelongsTo
    {
        return $this->belongsTo(SentList::class);
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function resolve(int $userId): void
    {
        $this->update([
            'resolved_at' => now(),
            'resolved_by' => $userId,
        ]);
    }
}
