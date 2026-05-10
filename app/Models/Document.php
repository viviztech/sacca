<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['owner_id', 'category', 'title', 'file_path', 'file_size', 'mime_type', 'uploaded_by', 'expires_at'])]
class Document extends Model
{
    protected function casts(): array
    {
        return ['expires_at' => 'date', 'file_size' => 'integer'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function expiresSoon(): bool
    {
        return $this->expires_at && $this->expires_at->isAfter(now()) && $this->expires_at->isBefore(now()->addDays(30));
    }
}
