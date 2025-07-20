<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'account',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

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
     * RELACIONSHIPS
     */

    public function areas(){
        return $this->hasMany(Area::class);
    }


    /**
     * Iniciales del usuario
     */

    public function getInitialsAttribute(): string{
        return strtoupper(substr($this->name, 0, 1 )) . substr($this->last_name, 0, 1);
    }

    /**
     * Scope Query
     */

    public function scopeActive($query){
        return $query->whereNull('deleted_at');
    }

    public function scopeSearch($query, $search){
        return $query->where(function($q) use ($search){
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('account', 'like', "%{$search}%");
        });
    }


    /*
    User Verification
     */

    public function canSupervise(Area $area){
        return $this->areas()->where('íd', $area->id)->exists();
    }

    public function getMachinesSupervised(){
        return Machine::whereIn('area_id', $this->area->pluck('id'));
    }


    /*
    Get Stats
     */
    public function getSupervisorStats(){
    $areas = $this->areas()->count();
    $machies = $this->getMachinesSupervised()->count();
    $tables = Table::whereIn('area_id', $this->areas->pluck('id'))->count();
    $semiAutomatic = SemiAutomaticTable::whereIn('area_id', $this->areas->pluck('id'))->count();

        return [
            'areas_supervised' => $areas,
            'machines_supervised' => $machies,
            'tables_supervised' => $tables,
            'semi_automatic_supervised' => $semiAutomatic,
            'total_supervised' => $areas + $machies + $tables + $semiAutomatic,
        ];


    }
}


