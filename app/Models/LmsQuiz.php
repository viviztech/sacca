<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['material_id', 'title', 'time_limit_minutes', 'passing_marks'])]
class LmsQuiz extends Model
{
    protected function casts(): array
    {
        return ['time_limit_minutes' => 'integer', 'passing_marks' => 'integer'];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(LmsMaterial::class, 'material_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id')->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id');
    }

    public function totalMarks(): int
    {
        return (int) $this->questions()->sum('marks');
    }
}
