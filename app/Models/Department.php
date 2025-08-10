<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Department extends Model
{
    /** @use HasFactory<\Database\Factories\DepartmentFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'comments',
    ];

    /**
     * Get the areas associated with the department.
     */

    public function areas()
    {
        return $this->hasMany(Area::class);
    }


    public function machines()
    {
        return $this->hasManyThrough(Machine::class, Area::class);
    }

    public function tables(){
    return $this->hasManyThrough(Table::class, Area::class);
    }

    public function semiAutomatics()
    {
        return $this->hasManyThrough(Semi_Automatic::class, Area::class);
    }


        // ===============================================
    // SCOPES
    // ===============================================

    /**
     * Solo departamentos activos
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Buscar departamentos por nombre
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    // ===============================================
    // MÉTODOS AUXILIARES
    // ===============================================

    /**
     * Verificar si el departamento se puede eliminar
     */
    public function canBeDeleted()
    {
        return $this->areas()->count() === 0;
    }

    /**
     * Obtener estadísticas del departamento
     */
    public function getStats()
    {
        return [
            'total_areas' => $this->areas()->count(),
            'total_machines' => $this->machines()->count(),
            'total_tables' => $this->tables()->count(),
            'total_semi_automatic' => $this->semiAutomatics()->count(),
            'active_machines' => $this->machines()->where('active', true)->count(),
            'active_tables' => $this->tables()->where('active', true)->count(),
            'active_semi_automatic' => $this->semiAutomatics()->where('active', true)->count(),
        ];
    }


}
