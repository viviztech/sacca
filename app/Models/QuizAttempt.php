<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['quiz_id', 'student_id', 'started_at', 'submitted_at', 'score', 'passed', 'answers'])]
class QuizAttempt extends Model
{
    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'submitted_at' => 'datetime', 'score' => 'integer', 'passed' => 'boolean', 'answers' => 'array'];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(LmsQuiz::class, 'quiz_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function isExpired(): bool
    {
        if ($this->submitted_at) {
            return false;
        }

        return now()->diffInMinutes($this->started_at) >= $this->quiz->time_limit_minutes;
    }
}
