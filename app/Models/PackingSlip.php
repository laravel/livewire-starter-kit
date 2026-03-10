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
    public const STATUS_PENDING   = 'pending';
    public const STATUS_SHIPPED   = 'shipped';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING   => 'Pendiente',
        self::STATUS_SHIPPED   => 'Despachado',
        self::STATUS_CANCELLED => 'Cancelado',
    ];

    protected $fillable = [
        'ps_number',
        'created_by',
        'status',
        'document_date',
        'shipped_at',
        'shipped_by',
        'notes',
    ];

    protected $casts = [
        'document_date' => 'date',
        'shipped_at'    => 'datetime',
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

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeShipped(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SHIPPED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
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

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isShipped(): bool
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
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
            self::STATUS_PENDING   => 'orange',
            self::STATUS_SHIPPED   => 'green',
            self::STATUS_CANCELLED => 'red',
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
