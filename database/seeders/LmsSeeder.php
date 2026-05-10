<?php

namespace Database\Seeders;

use App\Enums\LmsMaterialType;
use App\Enums\QuestionType;
use App\Models\Batch;
use App\Models\Course;
use App\Models\LmsAssignment;
use App\Models\LmsMaterial;
use App\Models\LmsQuiz;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;

class LmsSeeder extends Seeder
{
    public function run(): void
    {
        $faculty = User::where('role', 'faculty')->first();
        if (! $faculty) {
            return;
        }

        $batch = Batch::where('is_active', true)->first();
        $course = Course::first();
        if (! $batch || ! $course) {
            return;
        }

        $materials = [
            ['title' => 'Introduction to Airport Operations', 'type' => LmsMaterialType::Note, 'description' => 'Comprehensive notes on airport ground operations, check-in procedures, and baggage handling.'],
            ['title' => 'Safety Procedures Video Lecture', 'type' => LmsMaterialType::Video, 'description' => 'Video covering safety protocols on the tarmac and in terminal areas.'],
            ['title' => 'IATA Reference Guide', 'type' => LmsMaterialType::Link, 'description' => 'Official IATA documentation for cabin crew and ground staff.', 'external_url' => 'https://www.iata.org'],
            ['title' => 'Passenger Handling Assignment', 'type' => LmsMaterialType::Assignment, 'description' => 'Practical assignment on handling difficult passengers.'],
            ['title' => 'Aviation Fundamentals Quiz', 'type' => LmsMaterialType::Quiz, 'description' => 'Test your knowledge of basic aviation concepts.'],
        ];

        foreach ($materials as $mat) {
            $material = LmsMaterial::firstOrCreate(
                ['title' => $mat['title'], 'batch_id' => $batch->id],
                [
                    'course_id' => $course->id,
                    'faculty_id' => $faculty->id,
                    'type' => $mat['type'],
                    'description' => $mat['description'],
                    'external_url' => $mat['external_url'] ?? null,
                    'is_published' => true,
                    'published_at' => now()->subDays(rand(1, 10)),
                ]
            );

            if ($mat['type'] === LmsMaterialType::Assignment) {
                LmsAssignment::firstOrCreate(['material_id' => $material->id], [
                    'due_date' => now()->addDays(7),
                    'max_marks' => 100,
                    'instructions' => 'Write a 500-word essay on handling a flight delay scenario. Include communication strategy and customer service approach.',
                ]);
            }

            if ($mat['type'] === LmsMaterialType::Quiz) {
                $quiz = LmsQuiz::firstOrCreate(['material_id' => $material->id], [
                    'title' => 'Aviation Fundamentals Quiz',
                    'time_limit_minutes' => 20,
                    'passing_marks' => 6,
                ]);

                $questions = [
                    ['text' => 'What does IATA stand for?', 'type' => QuestionType::Mcq, 'marks' => 2, 'correct' => 'International Air Transport Association', 'options' => [['text' => 'International Air Transport Association'], ['text' => 'Indian Aviation Transport Agency'], ['text' => 'International Airport Terminal Association']]],
                    ['text' => 'The check-in counter closes how many minutes before departure?', 'type' => QuestionType::Mcq, 'marks' => 2, 'correct' => '45 minutes', 'options' => [['text' => '30 minutes'], ['text' => '45 minutes'], ['text' => '60 minutes']]],
                    ['text' => 'A boarding pass is required to enter the departure lounge.', 'type' => QuestionType::TrueFalse, 'marks' => 1, 'correct' => 'True', 'options' => null],
                    ['text' => 'Excess baggage fees apply when luggage weight exceeds the allowed limit.', 'type' => QuestionType::TrueFalse, 'marks' => 1, 'correct' => 'True', 'options' => null],
                    ['text' => 'What is the primary role of a cabin crew member?', 'type' => QuestionType::ShortAnswer, 'marks' => 4, 'correct' => 'Passenger safety', 'options' => null],
                ];

                foreach ($questions as $i => $q) {
                    QuizQuestion::firstOrCreate(
                        ['quiz_id' => $quiz->id, 'question_text' => $q['text']],
                        ['type' => $q['type'], 'marks' => $q['marks'], 'correct_answer' => $q['correct'], 'options' => $q['options'], 'sort_order' => $i + 1]
                    );
                }
            }
        }
    }
}
