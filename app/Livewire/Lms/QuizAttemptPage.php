<?php

namespace App\Livewire\Lms;

use App\Models\LmsQuiz;
use App\Models\QuizAttempt;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Quiz')]
class QuizAttemptPage extends Component
{
    #[Locked]
    public int $quizId;

    public ?QuizAttempt $attempt = null;

    public array $answers = [];

    public bool $submitted = false;

    public int $remainingSeconds = 0;

    public function mount(int $quizId): void
    {
        $this->quizId = $quizId;
        $quiz = LmsQuiz::with('questions')->findOrFail($quizId);

        $existing = QuizAttempt::where('quiz_id', $quizId)
            ->where('student_id', auth()->id())
            ->first();

        if ($existing) {
            $this->attempt = $existing;
            $this->answers = $existing->answers ?? [];
            $this->submitted = $existing->submitted_at !== null;
        } else {
            $this->attempt = QuizAttempt::create([
                'quiz_id' => $quizId,
                'student_id' => auth()->id(),
                'started_at' => now(),
                'answers' => [],
            ]);
        }

        $elapsed = (int) $this->attempt->started_at->diffInSeconds(now());
        $this->remainingSeconds = max(0, ($quiz->time_limit_minutes * 60) - $elapsed);
    }

    public function saveAnswer(int $questionId, string $answer): void
    {
        if ($this->submitted) {
            return;
        }
        $this->answers[$questionId] = $answer;
        $this->attempt->update(['answers' => $this->answers]);
    }

    public function submit(): void
    {
        if ($this->submitted) {
            return;
        }

        $quiz = LmsQuiz::with('questions')->findOrFail($this->quizId);
        $score = 0;

        foreach ($quiz->questions as $question) {
            $givenAnswer = $this->answers[$question->id] ?? '';
            if ($question->isCorrect($givenAnswer)) {
                $score += $question->marks;
            }
        }

        $passed = $score >= $quiz->passing_marks;

        $this->attempt->update([
            'submitted_at' => now(),
            'score' => $score,
            'passed' => $passed,
            'answers' => $this->answers,
        ]);

        $this->submitted = true;
        $this->attempt->refresh();
    }

    public function render(): View
    {
        $quiz = LmsQuiz::with(['questions' => fn ($q) => $q->orderBy('sort_order')])->findOrFail($this->quizId);

        return view('livewire.lms.quiz-attempt-page', compact('quiz'));
    }
}
