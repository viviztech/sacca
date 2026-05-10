<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LmsMaterial;
use App\Models\LmsQuiz;
use App\Models\LmsSubmission;
use App\Models\QuizAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LmsController extends Controller
{
    public function materials(Request $request): JsonResponse
    {
        $user = $request->user();

        $materials = LmsMaterial::with(['batch', 'faculty'])
            ->where('is_published', true)
            ->when($request->batch_id, fn ($q) => $q->where('batch_id', $request->batch_id))
            ->when($user->isStudent(), fn ($q) => $q->whereHas('batch', fn ($b) => $b->whereHas('enrollments', fn ($e) => $e->where('student_id', $user->id)->where('status', 'active'))))
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $materials->map(fn ($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'type' => $m->type->value,
                'type_label' => $m->type->label(),
                'batch' => $m->batch->name,
                'faculty' => $m->faculty->name,
                'file_path' => $m->file_path,
                'external_url' => $m->external_url,
                'published_at' => $m->published_at?->toISOString(),
            ]),
            'meta' => ['page' => $materials->currentPage(), 'total' => $materials->total()],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $material = LmsMaterial::with(['batch', 'faculty', 'assignment', 'quiz.questions'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $material]);
    }

    public function submitAssignment(Request $request, int $assignmentId): JsonResponse
    {
        $request->validate(['notes' => ['nullable', 'string']]);

        $existing = LmsSubmission::where('assignment_id', $assignmentId)
            ->where('student_id', $request->user()->id)
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Already submitted.'], 422);
        }

        $submission = LmsSubmission::create([
            'assignment_id' => $assignmentId,
            'student_id' => $request->user()->id,
            'notes' => $request->notes,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        return response()->json(['success' => true, 'data' => $submission, 'message' => 'Assignment submitted.'], 201);
    }

    public function startQuiz(Request $request, int $quizId): JsonResponse
    {
        $quiz = LmsQuiz::with('questions')->findOrFail($quizId);
        $studentId = $request->user()->id;

        $existing = QuizAttempt::where('quiz_id', $quizId)->where('student_id', $studentId)->first();

        if ($existing?->submitted_at) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz already submitted.',
                'data' => ['score' => $existing->score, 'passed' => $existing->passed],
            ], 422);
        }

        $attempt = $existing ?? QuizAttempt::create([
            'quiz_id' => $quizId,
            'student_id' => $studentId,
            'started_at' => now(),
            'answers' => [],
        ]);

        $elapsed = (int) $attempt->started_at->diffInSeconds(now());
        $remainingSeconds = max(0, ($quiz->time_limit_minutes * 60) - $elapsed);

        return response()->json([
            'success' => true,
            'data' => [
                'attempt_id' => $attempt->id,
                'remaining_seconds' => $remainingSeconds,
                'questions' => $quiz->questions->map(fn ($q) => [
                    'id' => $q->id,
                    'text' => $q->question_text,
                    'type' => $q->type->value,
                    'marks' => $q->marks,
                    'options' => $q->options,
                    'saved_answer' => $attempt->answers[$q->id] ?? null,
                ]),
            ],
        ]);
    }

    public function submitQuiz(Request $request, int $quizId): JsonResponse
    {
        $request->validate(['answers' => ['required', 'array']]);

        $quiz = LmsQuiz::with('questions')->findOrFail($quizId);
        $studentId = $request->user()->id;

        $attempt = QuizAttempt::where('quiz_id', $quizId)->where('student_id', $studentId)->firstOrFail();

        if ($attempt->submitted_at) {
            return response()->json(['success' => false, 'message' => 'Already submitted.'], 422);
        }

        $score = 0;
        foreach ($quiz->questions as $question) {
            $answer = $request->answers[$question->id] ?? '';
            if ($question->isCorrect($answer)) {
                $score += $question->marks;
            }
        }

        $passed = $score >= $quiz->passing_marks;

        $attempt->update([
            'answers' => $request->answers,
            'submitted_at' => now(),
            'score' => $score,
            'passed' => $passed,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'score' => $score,
                'total' => $quiz->totalMarks(),
                'passed' => $passed,
                'passing_marks' => $quiz->passing_marks,
            ],
        ]);
    }
}
