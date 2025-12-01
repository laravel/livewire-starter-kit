<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    /** @use HasFactory<\Database\Factories\HolidayFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'date',
        'description',
    ];

    /**
     * Scope a query to search holidays by name or description.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%")
                     ->orWhere('description', 'like', "%{$term}%");
    }

    public function scopeDate($query, $date)
    {
        return $query->where('date', $date);
    }

}
