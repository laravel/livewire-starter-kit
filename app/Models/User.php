<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'last_name',
        'account',
        'email',
        'password',
        // Campos de empleado
        'employee_number',
        'position',
        'birth_date',
        'entry_date',
        'comments',
        'active',
        'area_id',
        'shift_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'entry_date' => 'date',
            'active' => 'boolean',
        ];
    }

    // ===============================================
    // BOOT METHOD
    // ===============================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Solo generar número de empleado si tiene rol employee y no tiene número
            if (empty($user->employee_number) && $user->area_id) {
                $user->employee_number = self::generateEmployeeNumber();
            }
        });
    }

    /**
     * Generate a unique employee number
     */
    public static function generateEmployeeNumber(): string
    {
        $prefix = 'EMP';
        $year = date('y');
        
        $lastUser = self::withTrashed()
            ->whereNotNull('employee_number')
            ->where('employee_number', 'like', "{$prefix}{$year}%")
            ->orderBy('employee_number', 'desc')
            ->first();

        if ($lastUser) {
            $lastNumber = (int) substr($lastUser->employee_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // ===============================================
    // RELATIONSHIPS
    // ===============================================

    /**
     * User belongs to an Area (for employees)
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * User belongs to a Shift (for employees)
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Areas supervised by this user (for supervisors)
     */
    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    /**
     * Get the user's saved signature.
     */
    public function signature()
    {
        return $this->hasOne(UserSignature::class);
    }

    // ===============================================
    // ACCESSORS
    // ===============================================

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get initials attribute
     */
    public function getInitialsAttribute(): string
    {
        $first = substr($this->name ?? '', 0, 1);
        $last = substr($this->last_name ?? '', 0, 1);
        return strtoupper($first . $last);
    }

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->name} {$this->last_name}");
    }

    /**
     * Get the area name
     */
    public function getAreaNameAttribute(): string
    {
        return $this->area ? $this->area->name : 'Sin área';
    }

    /**
     * Get the shift name
     */
    public function getShiftNameAttribute(): string
    {
        return $this->shift ? $this->shift->name : 'Sin turno';
    }

    // ===============================================
    // SCOPES
    // ===============================================

    /**
     * Scope to filter only active users
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to filter only inactive users
     */
    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    /**
     * Scope to filter employees (users with employee role)
     */
    public function scopeEmployees($query)
    {
        return $query->role('employee');
    }

    /**
     * Scope to filter admins
     */
    public function scopeAdmins($query)
    {
        return $query->role('admin');
    }

    /**
     * Scope to filter by area
     */
    public function scopeByArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    /**
     * Scope to filter by shift
     */
    public function scopeByShift($query, $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    /**
     * Scope to search users
     */
    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }
        
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('account', 'like', "%{$search}%")
              ->orWhere('employee_number', 'like', "%{$search}%")
              ->orWhere('position', 'like', "%{$search}%");
        });
    }

    // ===============================================
    // HELPER METHODS
    // ===============================================

    /**
     * Check if user is an employee
     */
    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if the user has a saved signature.
     */
    public function hasSavedSignature(): bool
    {
        return $this->signature()->exists();
    }

    /**
     * Check if user can supervise an area
     */
    public function canSupervise(Area $area): bool
    {
        return $this->areas()->where('id', $area->id)->exists();
    }

    /**
     * Get machines supervised by this user
     */
    public function getMachinesSupervised()
    {
        return Machine::whereIn('area_id', $this->areas->pluck('id'));
    }

    /**
     * Get supervisor stats
     */
    public function getSupervisorStats(): array
    {
        $areas = $this->areas()->count();
        $machines = $this->getMachinesSupervised()->count();
        $tables = Table::whereIn('area_id', $this->areas->pluck('id'))->count();
        $semiAutomatic = Semi_Automatic::whereIn('area_id', $this->areas->pluck('id'))->count();

        return [
            'areas_supervised' => $areas,
            'machines_supervised' => $machines,
            'tables_supervised' => $tables,
            'semi_automatic_supervised' => $semiAutomatic,
            'total_supervised' => $areas + $machines + $tables + $semiAutomatic,
        ];
    }

    /**
     * Get employee statistics
     */
    public function getEmployeeStats(): array
    {
        return [
            'area' => $this->area_name,
            'shift' => $this->shift_name,
            'position' => $this->position ?? 'Sin posición',
            'entry_date' => $this->entry_date ? $this->entry_date->format('d/m/Y') : 'No registrada',
            'status' => $this->active ? 'Activo' : 'Inactivo',
        ];
    }
}
