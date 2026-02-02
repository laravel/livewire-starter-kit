<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AuditTrail extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable entity.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get a human-readable summary of changes.
     */
    public function getChangeSummary(): string
    {
        if (empty($this->old_values) && empty($this->new_values)) {
            return "Action: {$this->action}";
        }

        $changes = [];
        
        if ($this->action === 'create') {
            return "Created with " . count($this->new_values ?? []) . " fields";
        }

        if ($this->action === 'delete') {
            return "Deleted";
        }

        if ($this->action === 'update' && $this->old_values && $this->new_values) {
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? null;
                if ($oldValue !== $newValue) {
                    $changes[] = "{$key}: '{$oldValue}' → '{$newValue}'";
                }
            }
        }

        return empty($changes) ? "No changes detected" : implode(', ', $changes);
    }

    /**
     * Scope to filter by specific entity.
     */
    public function scopeForEntity(Builder $query, Model $model): Builder
    {
        return $query->where('auditable_type', get_class($model))
                    ->where('auditable_id', $model->id);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeForDateRange(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Scope to filter by action type.
     */
    public function scopeForAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }
}
