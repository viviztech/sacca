<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['branch_id', 'visitor_name', 'phone', 'email', 'organization', 'purpose', 'host_user_id', 'check_in_at', 'check_out_at', 'badge_number'])]
class VisitorLog extends Model
{
    protected function casts(): array
    {
        return ['check_in_at' => 'datetime', 'check_out_at' => 'datetime'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function isCheckedOut(): bool
    {
        return ! is_null($this->check_out_at);
    }
}
