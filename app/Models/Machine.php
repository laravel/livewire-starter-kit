<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'name',
        'brand',
        'model',
        'sn',
        'asset_number',
        'employees',
        'setup_time',
        'maintenance_time',
        'active',
        'comments',
        'area_id',
    ];

    /**
     * Casting de tipos de datos
     */
    protected $casts = [
        'active' => 'boolean',
        'setup_time' => 'decimal:2',
        'maintenance_time' => 'decimal:2',
        'employees' => 'integer',
    ];

    // ===============================================
    // RELACIONES
    // ===============================================

    /**
     * Una máquina pertenece a un área
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Obtiene los estándares asociados a esta máquina
     */
    public function standards()
    {
        return $this->hasMany(Standard::class, 'machine_id');
    }

    // ===============================================
    // ACCESSORS
    // ===============================================

    /**
     * Identificación completa de la máquina
     */
    public function getFullIdentificationAttribute()
    {
        $parts = array_filter([$this->brand, $this->model, $this->name]);
        return implode(' - ', $parts);
    }

    /**
     * Estado de la máquina en texto
     */
    public function getStatusTextAttribute()
    {
        return $this->active ? 'Activa' : 'Inactiva';
    }

    // ===============================================
    // SCOPES
    // ===============================================

    /**
     * Solo máquinas activas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Solo máquinas inactivas
     */
    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    /**
     * Máquinas por área
     */
    public function scopeByArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    /**
     * Buscar máquinas
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('brand', 'like', "%{$search}%")
              ->orWhere('model', 'like', "%{$search}%")
              ->orWhere('asset_number', 'like', "%{$search}%");
        });
    }

    // ===============================================
    // ESTADISTICAS
    // ===============================================

    /**
     * Obtener estadisticas de maquinas
     */
    public static function getStats(): array
    {
        $total = self::count();
        $active = self::where('active', true)->count();
        $inactive = self::where('active', false)->count();
        $avgSetupTime = round(self::avg('setup_time') ?? 0, 2);
        $avgMaintenanceTime = round(self::avg('maintenance_time') ?? 0, 2);
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
            'avg_setup_time' => $avgSetupTime,
            'avg_maintenance_time' => $avgMaintenanceTime,
            'by_area' => $byArea,
        ];
    }
}
