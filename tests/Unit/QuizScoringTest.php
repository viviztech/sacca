<?php

namespace Tests\Unit;

use App\Enums\QuestionType;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\LmsMaterial;
use App\Models\LmsQuiz;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizScoringTest extends TestCase
{
    use RefreshDatabase;

    private LmsQuiz $quiz;

    private QuizQuestion $q1;

    private QuizQuestion $q2;

    private QuizQuestion $q3;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Branch::create(['name' => 'TST', 'code' => 'T1', 'is_active' => true]);
        $course = Course::create(['name' => 'C', 'code' => 'C1', 'duration_hours' => 10, 'is_active' => true]);
        $batch = Batch::create(['branch_id' => $branch->id, 'course_id' => $course->id, 'name' => 'B1', 'start_date' => today(), 'is_active' => true, 'capacity' => 10]);
        $faculty = User::factory()->faculty()->create(['branch_id' => $branch->id]);
        $this->student = User::factory()->student()->create(['branch_id' => $branch->id]);

        $material = LmsMaterial::create([
            'batch_id' => $batch->id, 'course_id' => $course->id, 'faculty_id' => $faculty->id,
            'title' => 'Test Quiz', 'type' => 'quiz', 'is_published' => true,
        ]);

        $this->quiz = LmsQuiz::create([
            'material_id' => $material->id, 'title' => 'Quiz 1',
            'time_limit_minutes' => 30, 'passing_marks' => 6,
        ]);

        $this->q1 = QuizQuestion::create([
            'quiz_id' => $this->quiz->id, 'question_text' => 'What is 2+2?',
            'type' => QuestionType::Mcq, 'marks' => 2,
            'options' => [['text' => '3'], ['text' => '4'], ['text' => '5']],
            'correct_answer' => '4', 'sort_order' => 1,
        ]);
        $this->q2 = QuizQuestion::create([
            'quiz_id' => $this->quiz->id, 'question_text' => 'Sky is blue?',
            'type' => QuestionType::TrueFalse, 'marks' => 3,
            'correct_answer' => 'True', 'sort_order' => 2,
        ]);
        $this->q3 = QuizQuestion::create([
            'quiz_id' => $this->quiz->id, 'question_text' => 'Capital of France?',
            'type' => QuestionType::ShortAnswer, 'marks' => 5,
            'correct_answer' => 'Paris', 'sort_order' => 3,
        ]);
    }

    public function test_quiz_total_marks_calculated_correctly(): void
    {
        $this->assertEquals(10, $this->quiz->totalMarks());
    }

    public function test_correct_answer_is_scored(): void
    {
        $this->assertTrue($this->q1->isCorrect('4'));
        $this->assertTrue($this->q2->isCorrect('True'));
        $this->assertTrue($this->q3->isCorrect('Paris'));
    }

    public function test_wrong_answer_not_scored(): void
    {
        $this->assertFalse($this->q1->isCorrect('3'));
        $this->assertFalse($this->q2->isCorrect('False'));
        $this->assertFalse($this->q3->isCorrect('London'));
    }

    public function test_answer_comparison_is_case_insensitive(): void
    {
        $this->assertTrue($this->q3->isCorrect('paris'));
        $this->assertTrue($this->q3->isCorrect('PARIS'));
        $this->assertTrue($this->q2->isCorrect('true'));
    }

    public function test_full_quiz_score_all_correct(): void
    {
        $answers = [
            $this->q1->id => '4',
            $this->q2->id => 'True',
            $this->q3->id => 'Paris',
        ];

        $score = 0;
        foreach ($this->quiz->questions as $q) {
            if ($q->isCorrect($answers[$q->id] ?? '')) {
                $score += $q->marks;
            }
        }

        $this->assertEquals(10, $score);
        $this->assertTrue($score >= $this->quiz->passing_marks);
    }

    public function test_partial_score_fails_if_below_passing(): void
    {
        // Only q1 correct = 2 marks, passing = 6
        $answers = [$this->q1->id => '4', $this->q2->id => 'False', $this->q3->id => 'Rome'];

        $score = 0;
        foreach ($this->quiz->questions as $q) {
            if ($q->isCorrect($answers[$q->id] ?? '')) {
                $score += $q->marks;
            }
        }

        $this->assertEquals(2, $score);
        $this->assertFalse($score >= $this->quiz->passing_marks);
    }
}
