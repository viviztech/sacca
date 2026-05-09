<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['model_type', 'model_id', 'step_number', 'approver_id', 'status', 'actioned_at', 'comment'])]
class ApprovalWorkflow extends Model
{
    protected function casts(): array
    {
        return [
            'actioned_at' => 'datetime',
            'status' => ApprovalStatus::class,
            'step_number' => 'integer',
        ];
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
