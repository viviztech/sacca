<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'branch_id', 'report_date', 'work_summary', 'tasks_completed', 'blockers', 'submitted_at', 'escalation_sent_at', 'status'])]
class DailyWorkReport extends Model
{
    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'submitted_at' => 'datetime',
            'escalation_sent_at' => 'datetime',
            'tasks_completed' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }
}
