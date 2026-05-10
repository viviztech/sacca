<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id', 'title', 'description', 'drive_date', 'venue', 'positions', 'package_lpa', 'eligibility_criteria', 'branch_id', 'created_by', 'is_active'])]
class PlacementDrive extends Model
{
    protected function casts(): array
    {
        return [
            'drive_date' => 'date',
            'eligibility_criteria' => 'array',
            'is_active' => 'boolean',
            'package_lpa' => 'float',
            'positions' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(PlacementApplication::class, 'drive_id');
    }
}
