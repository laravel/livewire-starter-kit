<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'number',
        'name',
        'employees',
        'active',
        'comments',
        'area_id',
        'brand',
        'model',
        's_n',
        'asset_number',
        'description',
        'production_status_id',
        'standard_id',
    ];

    /**
     * Casting de tipos de datos
     */
    protected $casts = [
        'active' => 'boolean',
        'employees' => 'integer',
        'production_status_id' => 'integer',
        'standard_id' => 'integer',
    ];

    // ===============================================
    // RELACIONES
    // ===============================================

    /**
     * Una mesa pertenece a un área
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Una mesa pertenece a un estado de producción
     */
    public function productionStatus()
    {
        return $this->belongsTo(ProductionStatus::class);
    }

    /**
     * Una mesa pertenece a un standard
     */
    public function standard()
    {
        return $this->belongsTo(Standard::class);
    }

    /**
     * Relación inversa: una mesa tiene muchos standards
     */
    public function standards()
    {
        return $this->hasMany(Standard::class, 'work_table_id');
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
     * Buscar mesas
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('number', 'like', "%{$search}%");
    }

    // ===============================================
    // ESTADISTICAS
    // ===============================================

    /**
     * Obtener estadisticas de mesas
     */
    public static function getStats(): array
    {
        $total = self::count();
        $active = self::where('active', true)->count();
        $inactive = self::where('active', false)->count();
        $avgEmployees = round(self::avg('employees') ?? 0, 2);
        $byArea = self::select('area_id', \DB::raw('count(*) as total'))
            ->with('area:id,name')
            ->groupBy('area_id')
            ->get()
            ->pluck('total', 'area.name')
            ->toArray();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'avg_employees' => $avgEmployees,
            'by_area' => $byArea,
        ];
    }
}
