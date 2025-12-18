<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class DocumentSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'user_id',
        'signature_path',
        'signed_pdf_path',
        'signed_at',
        'ip_address',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    /**
     * Get the purchase order that owns the signature.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the user who created the signature.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to order signatures chronologically.
     */
    public function scopeChronological(Builder $query): Builder
    {
        return $query->orderBy('signed_at', 'asc');
    }

    /**
     * Get the full URL for the signature image.
     */
    public function getSignatureUrlAttribute(): string
    {
        return Storage::url($this->signature_path);
    }
}
