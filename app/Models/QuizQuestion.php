<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['quiz_id', 'question_text', 'type', 'marks', 'options', 'correct_answer', 'sort_order'])]
class QuizQuestion extends Model
{
    protected function casts(): array
    {
        return ['type' => QuestionType::class, 'options' => 'array', 'marks' => 'integer', 'sort_order' => 'integer'];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(LmsQuiz::class, 'quiz_id');
    }

    public function isCorrect(string $answer): bool
    {
        return strtolower(trim($answer)) === strtolower(trim($this->correct_answer ?? ''));
    }
}
