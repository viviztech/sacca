<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['payroll_cycle_id', 'user_id', 'working_days', 'present_days', 'absent_days', 'basic_salary', 'deductions', 'gross_salary', 'net_salary', 'pdf_path', 'published_at'])]
class Payslip extends Model
{
    protected function casts(): array
    {
        return [
            'deductions' => 'array',
            'basic_salary' => 'float',
            'gross_salary' => 'float',
            'net_salary' => 'float',
            'published_at' => 'datetime',
            'working_days' => 'integer',
            'present_days' => 'integer',
            'absent_days' => 'integer',
        ];
    }

    public function payrollCycle(): BelongsTo
    {
        return $this->belongsTo(PayrollCycle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
