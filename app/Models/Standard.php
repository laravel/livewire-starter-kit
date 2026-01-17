<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Standard
 *
 * Representa un estandar de produccion para un numero de parte.
 * Cada standard puede tener multiples configuraciones (StandardConfiguration)
 * que definen la productividad segun tipo de estacion y cantidad de personas.
 *
 * Referencia: Spec 06 - Multiple Standards por Numero de Parte
 *
 * @property int $id
 * @property int $part_id
 * @property int|null $work_table_id
 * @property int|null $semi_auto_work_table_id
 * @property int|null $machine_id
 * @property int|null $persons_1
 * @property int|null $persons_2
 * @property int|null $persons_3
 * @property int|null $units_per_hour
 * @property \Illuminate\Support\Carbon|null $effective_date
 * @property bool $active
 * @property bool $is_migrated
 * @property string|null $description
 *
 * @property-read Part $part
 * @property-read Table|null $workTable
 * @property-read Semi_Automatic|null $semiAutoWorkTable
 * @property-read Machine|null $machine
 * @property-read Collection<StandardConfiguration> $configurations
 * @property-read StandardConfiguration|null $defaultConfiguration
 * @property-read array $configurations_summary
 */
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
        'is_migrated',
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
        'is_migrated' => 'boolean',
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

    // ==========================================
    // NUEVAS RELACIONES - STANDARD CONFIGURATIONS
    // ==========================================

    /**
     * Obtiene todas las configuraciones del standard
     *
     * Cada configuracion define una combinacion de:
     * - Tipo de estacion (manual, semi_automatic, machine)
     * - Cantidad de personas (1-3)
     * - Productividad (units_per_hour)
     *
     * @return HasMany
     */
    public function configurations(): HasMany
    {
        return $this->hasMany(StandardConfiguration::class);
    }

    /**
     * Obtiene la configuracion marcada como default
     *
     * @return HasOne
     */
    public function defaultConfiguration(): HasOne
    {
        return $this->hasOne(StandardConfiguration::class)
                    ->where('is_default', true);
    }

    /**
     * Obtiene configuraciones filtradas por tipo de estacion
     *
     * @param string $type Uno de: manual, semi_automatic, machine
     * @return HasMany
     */
    public function configurationsByType(string $type): HasMany
    {
        return $this->configurations()
                    ->where('workstation_type', $type)
                    ->orderBy('persons_required');
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

    /**
     * Scope para filtrar standards migrados a configuraciones
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeMigrated(Builder $query): Builder
    {
        return $query->where('is_migrated', true);
    }

    /**
     * Scope para filtrar standards NO migrados
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeNotMigrated(Builder $query): Builder
    {
        return $query->where('is_migrated', false);
    }

    // ==========================================
    // NUEVOS METODOS DE NEGOCIO - CONFIGURATIONS
    // ==========================================

    /**
     * Obtiene la configuracion optima para un numero de empleados disponibles
     *
     * Busca la configuracion con mayor productividad (units_per_hour)
     * que requiera igual o menos personas que las disponibles.
     *
     * @param int $availableEmployees Numero de empleados disponibles
     * @param string|null $preferredType Tipo de estacion preferido (opcional)
     * @return StandardConfiguration|null La configuracion optima o null si no hay ninguna disponible
     */
    public function getOptimalConfiguration(
        int $availableEmployees,
        ?string $preferredType = null
    ): ?StandardConfiguration
    {
        $query = $this->configurations()
                      ->where('persons_required', '<=', $availableEmployees);

        if ($preferredType) {
            // Si hay tipo preferido, intentar primero con ese tipo
            $withType = (clone $query)->where('workstation_type', $preferredType)
                                       ->orderBy('units_per_hour', 'desc')
                                       ->first();
            if ($withType) {
                return $withType;
            }
            // Si no hay con el tipo preferido, buscar en cualquier tipo
        }

        // Buscar la configuracion con mayor productividad
        return $query->orderBy('units_per_hour', 'desc')->first();
    }

    /**
     * Calcula las horas requeridas usando la configuracion optima
     *
     * @param int $quantity Cantidad a producir
     * @param int $availableEmployees Empleados disponibles
     * @param string|null $preferredType Tipo de estacion preferido
     * @return array{hours: float, configuration: StandardConfiguration, persons_used: int, productivity: int, workstation_type: string}
     * @throws \RuntimeException Si no hay configuracion disponible
     */
    public function calculateRequiredHoursOptimal(
        int $quantity,
        int $availableEmployees,
        ?string $preferredType = null
    ): array
    {
        $config = $this->getOptimalConfiguration($availableEmployees, $preferredType);

        if (!$config) {
            $partNumber = $this->part->number ?? 'N/A';
            throw new \RuntimeException(
                "No hay configuracion disponible para {$availableEmployees} empleado(s) " .
                "en el standard ID {$this->id} (Part: {$partNumber})"
            );
        }

        return [
            'hours' => $config->calculateRequiredHours($quantity),
            'configuration' => $config,
            'persons_used' => $config->persons_required,
            'productivity' => $config->units_per_hour,
            'workstation_type' => $config->workstation_type,
        ];
    }

    /**
     * Verifica si el standard tiene configuraciones migradas
     *
     * @return bool
     */
    public function hasMigratedConfigurations(): bool
    {
        return $this->configurations()->exists();
    }

    /**
     * Obtiene el resumen de configuraciones agrupado por tipo de estacion
     *
     * Util para mostrar en UI una vista resumida de todas las configuraciones.
     *
     * @return array
     */
    public function getConfigurationsSummaryAttribute(): array
    {
        if (!$this->relationLoaded('configurations')) {
            $this->load('configurations');
        }

        return $this->configurations
                    ->sortBy(['workstation_type', 'persons_required'])
                    ->groupBy('workstation_type')
                    ->map(function ($configs) {
                        return $configs->map(function ($config) {
                            return [
                                'id' => $config->id,
                                'persons_required' => $config->persons_required,
                                'units_per_hour' => $config->units_per_hour,
                                'is_default' => $config->is_default,
                                'label' => $config->label,
                            ];
                        })->values()->toArray();
                    })
                    ->toArray();
    }

    /**
     * Obtiene la configuracion con mayor productividad
     *
     * @return StandardConfiguration|null
     */
    public function getMostProductiveConfiguration(): ?StandardConfiguration
    {
        return $this->configurations()
                    ->orderBy('units_per_hour', 'desc')
                    ->first();
    }

    /**
     * Obtiene la configuracion con menor requerimiento de personas
     *
     * @return StandardConfiguration|null
     */
    public function getMinPersonsConfiguration(): ?StandardConfiguration
    {
        return $this->configurations()
                    ->orderBy('persons_required', 'asc')
                    ->orderBy('units_per_hour', 'desc')
                    ->first();
    }

    /**
     * Obtiene todas las configuraciones de un tipo especifico
     *
     * @param string $type Tipo de estacion
     * @return Collection<StandardConfiguration>
     */
    public function getConfigurationsForType(string $type): Collection
    {
        return $this->configurations()
                    ->where('workstation_type', $type)
                    ->orderBy('persons_required')
                    ->get();
    }

    /**
     * Verifica si tiene configuracion para un tipo y cantidad de personas especificos
     *
     * @param string $type Tipo de estacion
     * @param int $persons Cantidad de personas
     * @return bool
     */
    public function hasConfiguration(string $type, int $persons): bool
    {
        return $this->configurations()
                    ->where('workstation_type', $type)
                    ->where('persons_required', $persons)
                    ->exists();
    }

    /**
     * Obtiene una configuracion especifica por tipo y personas
     *
     * @param string $type Tipo de estacion
     * @param int $persons Cantidad de personas
     * @return StandardConfiguration|null
     */
    public function getConfiguration(string $type, int $persons): ?StandardConfiguration
    {
        return $this->configurations()
                    ->where('workstation_type', $type)
                    ->where('persons_required', $persons)
                    ->first();
    }

    /**
     * Obtiene estadisticas de las configuraciones del standard
     *
     * @return array{total: int, by_type: array, min_productivity: int|null, max_productivity: int|null, has_default: bool}
     */
    public function getConfigurationsStats(): array
    {
        $configs = $this->configurations;

        if ($configs->isEmpty()) {
            return [
                'total' => 0,
                'by_type' => [],
                'min_productivity' => null,
                'max_productivity' => null,
                'has_default' => false,
            ];
        }

        return [
            'total' => $configs->count(),
            'by_type' => $configs->groupBy('workstation_type')
                                 ->map(fn($group) => $group->count())
                                 ->toArray(),
            'min_productivity' => $configs->min('units_per_hour'),
            'max_productivity' => $configs->max('units_per_hour'),
            'has_default' => $configs->contains('is_default', true),
        ];
    }

}
