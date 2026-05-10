<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['branch_id', 'report_type', 'period_from', 'period_to', 'generated_by', 'file_path', 'generated_at'])]
class ComplianceReport extends Model
{
    protected function casts(): array
    {
        return ['period_from' => 'date', 'period_to' => 'date', 'generated_at' => 'datetime'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
