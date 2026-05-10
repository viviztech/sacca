<?php

namespace App\Models;

use App\Enums\TrainingSessionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'batch_id', 'log_date', 'hours_logged', 'session_type', 'notes', 'verified_by'])]
class TrainingHourLog extends Model
{
    protected function casts(): array
    {
        return ['log_date' => 'date', 'hours_logged' => 'float', 'session_type' => TrainingSessionType::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
