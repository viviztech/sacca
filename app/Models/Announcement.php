<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['branch_id', 'title', 'body', 'category', 'audience', 'published_by', 'published_at', 'expires_at', 'attachments'])]
class Announcement extends Model
{
    protected function casts(): array
    {
        return [
            'audience' => 'array',
            'attachments' => 'array',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function isActive(): bool
    {
        return $this->published_at && $this->published_at->isPast()
            && (! $this->expires_at || $this->expires_at->isFuture());
    }
}
