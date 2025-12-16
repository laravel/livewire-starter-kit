<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Standard extends Model
{
    /** @use HasFactory<\Database\Factories\StandardFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Status constants
     */
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'persons_1',
        'persons_2',
        'persons_3',
        'effective_date',
        'active',
        'description',
        'part_id',
        'area_id',
        'department_id'
    ];

    protected $casts = [
        'effective_date' => 'date',
        'persons_1' => 'integer',
        'persons_2' => 'integer',
        'persons_3' => 'integer',
        'active' => 'boolean',
        'description' => 'string',
        'part_id' => 'integer',
        'area_id' => 'integer',
        'department_id' => 'integer'
    ];

    /**
     * Get the part that owns the standard.
     */
    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Get the area that owns the standard.
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get the department that owns the standard.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Scope a query to only include active standards.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include inactive standards.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('active', false);
    }

    /**
     * Scope a query to search standards.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
              ->orWhereHas('part', function ($partQuery) use ($search) {
                  $partQuery->where('number', 'like', "%{$search}%")
                            ->orWhere('item_number', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
              })
              ->orWhereHas('area', function ($areaQuery) use ($search) {
                  $areaQuery->where('name', 'like', "%{$search}%");
              })
              ->orWhereHas('department', function ($departmentQuery) use ($search) {
                  $departmentQuery->where('name', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Check if this standard can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return !$this->part->purchaseOrders()->exists();
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive'
        ];
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->active] ?? $this->active;
    }

    /**
     * Get the status color for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->active) {
            self::STATUS_ACTIVE => 'green',
            self::STATUS_INACTIVE => 'red',
            default => 'gray',
        };
    }

    /**
     * Get standards statistics.
     */
    public static function getStats(): array
    {
        $total = self::count();
        $active = self::where('active', true)->count();
        $inactive = self::where('active', false)->count();
        $current = self::where('effective_date', '<=', now())
                       ->where('active', true)
                       ->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'current' => $current,
        ];
    }
}
