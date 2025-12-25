<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionStatus extends Model
{
    /** @use HasFactory<\Database\Factories\ProductionStatusFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'color',
        'order',
        'active',
        'description',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    // ===============================================
    // RELACIONES
    // ===============================================

    /**
     * Estado de producción tiene múltiples tables
     */
    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    /**
     * Estado de producción tiene múltiples semi-automáticos
     */
    public function semiAutomatics()
    {
        return $this->hasMany(Semi_Automatic::class);
    }

    /**
     * Estado de producción tiene múltiples máquinas
     */
    public function machines()
    {
        return $this->hasMany(Machine::class);
    }

    // ===============================================
    // SCOPES
    // ===============================================

    /**
     * Solo estados activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    /**
     * Ordenar por campo order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    // ===============================================
    // MÉTODOS AUXILIARES
    // ===============================================

    /**
     * Verificar si el estado se puede eliminar
     */
    public function canBeDeleted()
    {
        return $this->tables()->count() === 0
            && $this->semiAutomatics()->count() === 0
            && $this->machines()->count() === 0;
    }
}
