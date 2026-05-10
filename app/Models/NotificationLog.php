<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['recipient_user_id', 'channel', 'template_name', 'payload', 'status', 'provider_message_id', 'sent_at'])]
class NotificationLog extends Model
{
    protected function casts(): array
    {
        return ['payload' => 'array', 'sent_at' => 'datetime'];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
