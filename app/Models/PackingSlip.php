<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PackingSlip extends Model
{
    use HasFactory, SoftDeletes;

    // =========================================================
    // Constantes de estado del ciclo de vida
    // =========================================================
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_SHIPPED   = 'shipped';

    public const STATUSES = [
        self::STATUS_DRAFT     => 'Borrador',
        self::STATUS_CONFIRMED => 'Confirmado',
        self::STATUS_SHIPPED   => 'Despachado',
    ];

    protected $fillable = [
        'ps_number',
        'created_by',
        'status',
        'shipped_at',
        'shipped_by',
        'notes',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
    ];

    // =========================================================
    // Boot: auto-generacion de ps_number
    // =========================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (PackingSlip $ps) {
            if (empty($ps->ps_number)) {
                $ps->ps_number = static::generatePsNumber();
            }
        });
    }

    /**
     * Genera un numero unico de Packing Slip con el formato PS-YYYY-NNNN.
     * Incluye registros soft-deleted para evitar colisiones de unicidad.
     */
    public static function generatePsNumber(): string
    {
        $year   = Carbon::now()->year;
        $prefix = "PS-{$year}-";

        $last = static::withTrashed()
            ->where('ps_number', 'like', "{$prefix}%")
            ->orderByRaw('CAST(SUBSTRING(ps_number, -4) AS UNSIGNED) DESC')
            ->first();

        $next = $last ? ((int) substr($last->ps_number, -4)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    // =========================================================
    // Relaciones
    // =========================================================

    /**
     * Usuario que creo el Packing Slip.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que realizo el despacho.
     */
    public function shipper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    /**
     * Items (lotes) incluidos en este Packing Slip.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PackingSlipItem::class);
    }

    // =========================================================
    // Scopes
    // =========================================================

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeShipped(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SHIPPED);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where('ps_number', 'like', "%{$search}%");
    }

    // =========================================================
    // Helpers de estado
    // =========================================================

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isShipped(): bool
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    /**
     * Etiqueta legible del estado actual.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Color de badge para UI (Tailwind CSS).
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT     => 'yellow',
            self::STATUS_CONFIRMED => 'blue',
            self::STATUS_SHIPPED   => 'green',
            default                => 'gray',
        };
    }

    /**
     * Total de piezas empacadas sumando todos los items del PS.
     */
    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->items()->sum('quantity_packed');
    }

    /**
     * Total de lineas (items) en este PS.
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items()->count();
    }
}
