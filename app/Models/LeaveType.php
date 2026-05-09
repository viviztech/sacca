<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'max_days_per_year', 'carry_forward', 'requires_document', 'is_active'])]
class LeaveType extends Model
{
    protected function casts(): array
    {
        return [
            'carry_forward' => 'boolean',
            'requires_document' => 'boolean',
            'is_active' => 'boolean',
            'max_days_per_year' => 'integer',
        ];
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }
}
