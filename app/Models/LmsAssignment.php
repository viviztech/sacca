<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['material_id', 'due_date', 'max_marks', 'instructions'])]
class LmsAssignment extends Model
{
    protected function casts(): array
    {
        return ['due_date' => 'datetime', 'max_marks' => 'integer'];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(LmsMaterial::class, 'material_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(LmsSubmission::class, 'assignment_id');
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast();
    }
}
