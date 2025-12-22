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
        'work_table_id',
        'semi_auto_work_table_id',
        'machine_id',
        'units_per_hour'
    ];

    protected $casts = [
        'effective_date' => 'date',
        'persons_1' => 'integer',
        'persons_2' => 'integer',
        'persons_3' => 'integer',
        'active' => 'boolean',
        'description' => 'string',
        'part_id' => 'integer',
        'work_table_id' => 'integer',
        'semi_auto_work_table_id' => 'integer',
        'machine_id' => 'integer',
        'units_per_hour' => 'integer'
    ];

    /**
     * Get the part that owns the standard.
     */
    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Get the work table that owns the standard.
     */
    public function workTable()
    {
        return $this->belongsTo(Table::class, 'work_table_id');
    }

    /**
     * Get the semi-automatic work table that owns the standard.
     */
    public function semiAutoWorkTable()
    {
        return $this->belongsTo(Semi_Automatic::class, 'semi_auto_work_table_id');
    }

    /**
     * Get the machine that owns the standard.
     */
    public function machine()
    {
        return $this->belongsTo(Machine::class);
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
              ->orWhereHas('workTable', function ($tableQuery) use ($search) {
                  $tableQuery->where('number', 'like', "%{$search}%");
              })
              ->orWhereHas('semiAutoWorkTable', function ($semiAutoQuery) use ($search) {
                  $semiAutoQuery->where('number', 'like', "%{$search}%");
              })
              ->orWhereHas('machine', function ($machineQuery) use ($search) {
                  $machineQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('brand', 'like', "%{$search}%")
                               ->orWhere('model', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Check if this standard can be deleted.
     * Standards can always be deleted (soft delete maintains history).
     */
    public function canBeDeleted(): bool
    {
        return true;
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

    /**
     * ===============================================
     * MÉTODOS HELPER PARA WORKSTATION MANAGEMENT
     * ===============================================
     */

    /**
     * Obtiene la estación de trabajo activa (primera no-null)
     *
     * @return \App\Models\Table|\App\Models\Semi_Automatic|\App\Models\Machine|null
     */
    public function getWorkstation()
    {
        return $this->workTable ?? $this->semiAutoWorkTable ?? $this->machine;
    }

    /**
     * Obtiene el tipo de ensamble (assembly mode)
     *
     * @return string|null 'manual', 'semi_automatic', 'machine'
     */
    public function getAssemblyMode(): ?string
    {
        if ($this->work_table_id) return 'manual';
        if ($this->semi_auto_work_table_id) return 'semi_automatic';
        if ($this->machine_id) return 'machine';
        return null;
    }

    /**
     * Accessor para assembly_mode (permite usar $standard->assembly_mode)
     *
     * @return string|null
     */
    public function getAssemblyModeAttribute(): ?string
    {
        return $this->getAssemblyMode();
    }

    /**
     * Obtiene el nombre de la estación para display
     *
     * @return string
     */
    public function getWorkstationNameAttribute(): string
    {
        $workstation = $this->getWorkstation();

        if (!$workstation) {
            return 'Sin estación asignada';
        }

        if ($workstation instanceof \App\Models\Machine) {
            return $workstation->full_identification ?? $workstation->name;
        }

        return $workstation->number ?? 'N/A';
    }

    /**
     * Calcula las horas requeridas para producir una cantidad
     *
     * Implementa Propiedad 4 del Spec 01
     *
     * @param int $quantity Cantidad a producir
     * @return float Horas requeridas
     * @throws \DivisionByZeroError Si units_per_hour es 0
     */
    public function calculateRequiredHours(int $quantity): float
    {
        if ($this->units_per_hour === 0) {
            throw new \DivisionByZeroError(
                "El estándar para la parte '{$this->part->number}' tiene units_per_hour = 0"
            );
        }

        return round($quantity / $this->units_per_hour, 2);
    }

    /**
     * Scope para filtrar por tipo de estación
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type 'manual', 'semi_automatic', 'machine'
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAssemblyMode($query, string $type)
    {
        return match($type) {
            'manual' => $query->whereNotNull('work_table_id'),
            'semi_automatic' => $query->whereNotNull('semi_auto_work_table_id'),
            'machine' => $query->whereNotNull('machine_id'),
            default => $query,
        };
    }

}
