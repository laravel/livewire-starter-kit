<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Semi_Automatic extends Model
{
    use HasFactory, SoftDeletes;

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

    /**
     * Obtiene los estándares asociados a esta mesa semi-automática
     */
    public function standards()
    {
        return $this->hasMany(Standard::class, 'semi_auto_work_table_id');
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

    // ===============================================
    // ESTADISTICAS
    // ===============================================

    /**
     * Obtener estadisticas de semi-automaticos
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
