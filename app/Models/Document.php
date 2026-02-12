<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'category_name',
        'document_name',
        'url',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
