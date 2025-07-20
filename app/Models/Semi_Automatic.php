<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semi_Automatic extends Model
{
    use HasFactory;

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'number',
        'employees',
        'active',
        'comments',
        'area_id',
    ];

    /**
     * Casting de tipos de datos
     */
    protected $casts = [
        'active' => 'boolean',
        'employees' => 'integer',
    ];

    // ===============================================
    // RELACIONES
    // ===============================================

    /**
     * Una mesa semi-automática pertenece a un área
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    // ===============================================
    // ACCESSORS
    // ===============================================

    /**
     * Estado de la mesa en texto
     */
    public function getStatusTextAttribute()
    {
        return $this->active ? 'Activa' : 'Inactiva';
    }

    // ===============================================
    // SCOPES
    // ===============================================

    /**
     * Solo mesas activas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Solo mesas inactivas
     */
    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    /**
     * Mesas por área
     */
    public function scopeByArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    /**
     * Buscar mesas semi-automáticas
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('number', 'like', "%{$search}%");
    }
}
