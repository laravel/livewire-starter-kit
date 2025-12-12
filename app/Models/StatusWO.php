<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusWO extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'statuses_wo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'color',
        'comments',
    ];

    /**
     * Relationships with other models
     */

    /**
     * Get the work orders for this status.
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'status_id');
    }

    /**
     * Scopes
     */

    /**
     * Search statuses by name or comments.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('comments', 'like', "%{$search}%");
    }

    /**
     * Order by field dynamically.
     */
    public function scopeSortByField($query, $field = 'name', $direction = 'asc')
    {
        return $query->orderBy($field, $direction);
    }

    /**
     * Auxiliary methods
     */

    /**
     * Check if this status can be deleted.
     * A status cannot be deleted if it has associated work orders.
     */
    public function canBeDeleted(): bool
    {
        return $this->workOrders()->count() === 0;
    }
}
