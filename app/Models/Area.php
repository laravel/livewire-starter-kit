<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    /** @use HasFactory<\Database\Factories\AreaFactory> */
    use HasFactory;


        protected $fillable = [
        'name',
        'description',
        'comments',
        'user_id',
        'department_id',
    ];


       // ===============================================
    // RELACIONES
    // ===============================================

    /**
     * Un área pertenece a un usuario (supervisor)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un área pertenece a un departamento
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Un área tiene múltiples máquinas
     */
    public function machines()
    {
        return $this->hasMany(Machine::class);
    }

    /**
     * Un área tiene múltiples mesas
     */
    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    /**
     * Un área tiene múltiples mesas semi-automáticas
     */
    public function semiAutomatics()
    {
        return $this->hasMany(Semi_Automatic::class);
    }


      // ===============================================
    // ACCESSORS
    // ===============================================

    /**
     * Nombre del supervisor del área
     */
    public function getSupervisorNameAttribute()
    {
        return $this->user ? $this->user->full_name : 'Sin supervisor';
    }

    /**
     * Nombre del departamento
     */
    public function getDepartmentNameAttribute()
    {
        return $this->department ? $this->department->name : 'Sin departamento';
    }

    // ===============================================
    // SCOPES
    // ===============================================

    /**
     * Solo áreas activas
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Áreas por departamento
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Áreas por supervisor
     */
    public function scopeBySupervisor($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Buscar áreas
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    // ===============================================
    // MÉTODOS AUXILIARES
    // ===============================================

    /**
     * Verificar si el área se puede eliminar
     */
    public function canBeDeleted()
    {
        return $this->machines()->count() === 0
            && $this->tables()->count() === 0
            && $this->semiAutomatics()->count() === 0;
    }

    /**
     * Obtener estadísticas del área
     */
    public function getStats()
    {
        return [
            'total_machines' => $this->machines()->count(),
            'active_machines' => $this->machines()->where('active', true)->count(),
            'total_tables' => $this->tables()->count(),
            'active_tables' => $this->tables()->where('active', true)->count(),
            'total_semi_automatic' => $this->semiAutomatics()->count(),
            'active_semi_automatic' => $this->semiAutomatics()->where('active', true)->count(),
            'total_equipment' => $this->machines()->count() + $this->tables()->count() + $this->semiAutomatics()->count(),
        ];
    }

    /**
     * Obtener todo el equipo del área
     */
    public function getAllEquipment()
    {
        $machines = $this->machines->map(function ($machine) {
            $machine->equipment_type = 'machine';
            return $machine;
        });

        $tables = $this->tables->map(function ($table) {
            $table->equipment_type = 'table';
            return $table;
        });

        $semiAutomatic = $this->semiAutomatics->map(function ($table) {
            $table->equipment_type = 'semi_automatic';
            return $table;
        });

        return $machines->concat($tables)->concat($semiAutomatic);
    }
}
