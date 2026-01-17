<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo StandardConfiguration
 *
 * Representa una configuracion individual de produccion para un standard.
 * Cada configuracion define la productividad (units_per_hour) para una
 * combinacion especifica de:
 * - Tipo de estacion de trabajo (manual, semi_automatic, machine)
 * - Cantidad de personas requeridas (1-3)
 *
 * Un standard puede tener multiples configuraciones, permitiendo:
 * - Diferentes productividades segun cantidad de personas
 * - Diferentes productividades segun tipo de estacion
 *
 * Referencia: Spec 06 - Multiple Standards por Numero de Parte
 *
 * @property int $id
 * @property int $standard_id
 * @property string $workstation_type
 * @property int|null $workstation_id
 * @property int $persons_required
 * @property int $units_per_hour
 * @property bool $is_default
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Standard $standard
 * @property-read Table|Semi_Automatic|Machine|null $workstation
 * @property-read string $label
 * @property-read string $workstation_type_label
 */
class StandardConfiguration extends Model
{
    use HasFactory;

    // ==========================================
    // CONSTANTES - TIPOS DE ESTACION
    // ==========================================

    /**
     * Mesa de trabajo manual
     */
    public const TYPE_MANUAL = 'manual';

    /**
     * Mesa semi-automatica
     */
    public const TYPE_SEMI_AUTOMATIC = 'semi_automatic';

    /**
     * Maquina
     */
    public const TYPE_MACHINE = 'machine';

    // ==========================================
    // CONSTANTES - LIMITES
    // ==========================================

    /**
     * Numero maximo de personas permitido por configuracion
     */
    public const MAX_PERSONS = 3;

    /**
     * Numero minimo de personas permitido
     */
    public const MIN_PERSONS = 1;

    /**
     * Productividad minima permitida (units_per_hour)
     */
    public const MIN_UNITS_PER_HOUR = 1;

    // ==========================================
    // CONFIGURACION DEL MODELO
    // ==========================================

    /**
     * Nombre de la tabla
     */
    protected $table = 'standard_configurations';

    /**
     * Campos asignables masivamente
     */
    protected $fillable = [
        'standard_id',
        'workstation_type',
        'workstation_id',
        'persons_required',
        'units_per_hour',
        'is_default',
        'notes',
    ];

    /**
     * Casting de tipos
     */
    protected $casts = [
        'standard_id' => 'integer',
        'workstation_id' => 'integer',
        'persons_required' => 'integer',
        'units_per_hour' => 'integer',
        'is_default' => 'boolean',
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    /**
     * Relacion con Standard padre
     *
     * @return BelongsTo
     */
    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standard::class);
    }

    /**
     * Obtiene la estacion de trabajo asociada
     *
     * Esta es una relacion "polimorfica manual" basada en workstation_type.
     * Retorna el modelo correspondiente segun el tipo:
     * - manual -> Table
     * - semi_automatic -> Semi_Automatic
     * - machine -> Machine
     *
     * @return Table|Semi_Automatic|Machine|null
     */
    public function getWorkstationAttribute(): Table|Semi_Automatic|Machine|null
    {
        if (!$this->workstation_id) {
            return null;
        }

        return match($this->workstation_type) {
            self::TYPE_MANUAL => Table::find($this->workstation_id),
            self::TYPE_SEMI_AUTOMATIC => Semi_Automatic::find($this->workstation_id),
            self::TYPE_MACHINE => Machine::find($this->workstation_id),
            default => null,
        };
    }

    /**
     * Relacion con Mesa de trabajo (si es tipo manual)
     *
     * @return BelongsTo
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'workstation_id');
    }

    /**
     * Relacion con Mesa semi-automatica (si es tipo semi_automatic)
     *
     * @return BelongsTo
     */
    public function semiAutomatic(): BelongsTo
    {
        return $this->belongsTo(Semi_Automatic::class, 'workstation_id');
    }

    /**
     * Relacion con Maquina (si es tipo machine)
     *
     * @return BelongsTo
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'workstation_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Filtrar por tipo de estacion de trabajo
     *
     * @param Builder $query
     * @param string $type Uno de: manual, semi_automatic, machine
     * @return Builder
     */
    public function scopeByWorkstationType(Builder $query, string $type): Builder
    {
        return $query->where('workstation_type', $type);
    }

    /**
     * Filtrar configuraciones de tipo manual (mesas de trabajo)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeManual(Builder $query): Builder
    {
        return $query->where('workstation_type', self::TYPE_MANUAL);
    }

    /**
     * Filtrar configuraciones de tipo semi-automatico
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeSemiAutomatic(Builder $query): Builder
    {
        return $query->where('workstation_type', self::TYPE_SEMI_AUTOMATIC);
    }

    /**
     * Filtrar configuraciones de tipo maquina
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeMachine(Builder $query): Builder
    {
        return $query->where('workstation_type', self::TYPE_MACHINE);
    }

    /**
     * Filtrar por cantidad de personas requeridas
     *
     * @param Builder $query
     * @param int $persons
     * @return Builder
     */
    public function scopeByPersons(Builder $query, int $persons): Builder
    {
        return $query->where('persons_required', $persons);
    }

    /**
     * Filtrar configuraciones donde personas requeridas no exceda un limite
     *
     * @param Builder $query
     * @param int $maxPersons
     * @return Builder
     */
    public function scopeWithMaxPersons(Builder $query, int $maxPersons): Builder
    {
        return $query->where('persons_required', '<=', $maxPersons);
    }

    /**
     * Obtener solo configuraciones marcadas como default
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Ordenar por productividad (units_per_hour)
     *
     * @param Builder $query
     * @param string $direction 'asc' o 'desc'
     * @return Builder
     */
    public function scopeOrderByProductivity(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('units_per_hour', $direction);
    }

    /**
     * Ordenar por personas requeridas
     *
     * @param Builder $query
     * @param string $direction 'asc' o 'desc'
     * @return Builder
     */
    public function scopeOrderByPersons(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('persons_required', $direction);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Obtiene etiqueta descriptiva de la configuracion
     *
     * Formato: "Mesa Manual - 2 persona(s) - 150 uph"
     *
     * @return string
     */
    public function getLabelAttribute(): string
    {
        $typeLabel = $this->workstation_type_label;
        $persons = $this->persons_required;
        $personWord = $persons === 1 ? 'persona' : 'personas';

        return "{$typeLabel} - {$persons} {$personWord} - {$this->units_per_hour} uph";
    }

    /**
     * Obtiene etiqueta legible del tipo de estacion
     *
     * @return string
     */
    public function getWorkstationTypeLabelAttribute(): string
    {
        return match($this->workstation_type) {
            self::TYPE_MANUAL => 'Mesa Manual',
            self::TYPE_SEMI_AUTOMATIC => 'Mesa Semi-Automatica',
            self::TYPE_MACHINE => 'Maquina',
            default => 'Desconocido',
        };
    }

    /**
     * Obtiene el nombre de la estacion especifica (si esta asignada)
     *
     * @return string
     */
    public function getWorkstationNameAttribute(): string
    {
        $workstation = $this->workstation;

        if (!$workstation) {
            return 'Sin estacion asignada';
        }

        if ($workstation instanceof Machine) {
            return $workstation->full_identification ?? $workstation->name ?? 'N/A';
        }

        return $workstation->number ?? 'N/A';
    }

    // ==========================================
    // METODOS DE NEGOCIO
    // ==========================================

    /**
     * Valida que las personas requeridas no excedan la capacidad de la estacion
     *
     * Compara persons_required con el campo 'employees' de la estacion de trabajo.
     *
     * @return array{is_valid: bool, message: string, capacity: int|null}
     */
    public function validateCapacity(): array
    {
        $workstation = $this->workstation;

        if (!$workstation) {
            return [
                'is_valid' => true,
                'message' => 'Sin estacion asignada - no se puede validar capacidad',
                'capacity' => null,
            ];
        }

        $capacity = $workstation->employees ?? 0;
        $isValid = $this->persons_required <= $capacity;

        return [
            'is_valid' => $isValid,
            'message' => $isValid
                ? 'Capacidad validada correctamente'
                : "Personas requeridas ({$this->persons_required}) excede la capacidad de la estacion ({$capacity})",
            'capacity' => $capacity,
        ];
    }

    /**
     * Calcula las horas requeridas para producir una cantidad dada
     *
     * Formula: horas = cantidad / units_per_hour
     *
     * @param int $quantity Cantidad de unidades a producir
     * @return float Horas requeridas (redondeado a 2 decimales)
     * @throws \DivisionByZeroError Si units_per_hour es 0
     */
    public function calculateRequiredHours(int $quantity): float
    {
        if ($this->units_per_hour === 0 || $this->units_per_hour === null) {
            throw new \DivisionByZeroError(
                "La configuracion ID {$this->id} tiene units_per_hour = 0"
            );
        }

        return round($quantity / $this->units_per_hour, 2);
    }

    /**
     * Calcula cuantas unidades se pueden producir en un tiempo dado
     *
     * Formula: unidades = hours * units_per_hour
     *
     * @param float $hours Horas disponibles
     * @return int Unidades que se pueden producir
     */
    public function calculateUnitsInHours(float $hours): int
    {
        return (int) floor($hours * $this->units_per_hour);
    }

    /**
     * Verifica si esta configuracion es mas productiva que otra
     *
     * @param StandardConfiguration $other
     * @return bool
     */
    public function isMoreProductiveThan(StandardConfiguration $other): bool
    {
        return $this->units_per_hour > $other->units_per_hour;
    }

    /**
     * Calcula la eficiencia por persona
     *
     * Formula: units_per_hour / persons_required
     *
     * @return float Productividad por persona
     */
    public function getEfficiencyPerPerson(): float
    {
        if ($this->persons_required === 0) {
            return 0;
        }

        return round($this->units_per_hour / $this->persons_required, 2);
    }

    // ==========================================
    // METODOS ESTATICOS
    // ==========================================

    /**
     * Obtiene los tipos de estacion disponibles con sus etiquetas
     *
     * @return array<string, string>
     */
    public static function getWorkstationTypes(): array
    {
        return [
            self::TYPE_MANUAL => 'Mesa de Trabajo Manual',
            self::TYPE_SEMI_AUTOMATIC => 'Mesa Semi-Automatica',
            self::TYPE_MACHINE => 'Maquina',
        ];
    }

    /**
     * Obtiene las opciones de cantidad de personas
     *
     * @return array<int, string>
     */
    public static function getPersonsOptions(): array
    {
        $options = [];
        for ($i = self::MIN_PERSONS; $i <= self::MAX_PERSONS; $i++) {
            $word = $i === 1 ? 'Persona' : 'Personas';
            $options[$i] = "{$i} {$word}";
        }
        return $options;
    }

    /**
     * Valida si un tipo de estacion es valido
     *
     * @param string $type
     * @return bool
     */
    public static function isValidWorkstationType(string $type): bool
    {
        return in_array($type, [
            self::TYPE_MANUAL,
            self::TYPE_SEMI_AUTOMATIC,
            self::TYPE_MACHINE,
        ], true);
    }

    /**
     * Valida si una cantidad de personas es valida
     *
     * @param int $persons
     * @return bool
     */
    public static function isValidPersonsCount(int $persons): bool
    {
        return $persons >= self::MIN_PERSONS && $persons <= self::MAX_PERSONS;
    }
}
