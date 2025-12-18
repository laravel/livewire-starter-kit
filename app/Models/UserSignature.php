<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UserSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'signature_path',
    ];

    /**
     * Get the user that owns the signature.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full URL for the signature image.
     */
    public function getSignatureUrlAttribute(): string
    {
        return Storage::url($this->signature_path);
    }
}
