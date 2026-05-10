<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['branch_id', 'month', 'year', 'status', 'generated_by', 'generated_at'])]
class PayrollCycle extends Model
{
    protected function casts(): array
    {
        return ['month' => 'integer', 'year' => 'integer', 'generated_at' => 'datetime'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function monthName(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }
}
