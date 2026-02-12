<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_name',
        'description',
        'color',
        'icon',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function getDocumentCountAttribute(): int
    {
        return $this->documents()->count();
    }

    public function scopeWithDocumentCount($query)
    {
        return $query->withCount('documents');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('category_name', 'asc');
    }
}
