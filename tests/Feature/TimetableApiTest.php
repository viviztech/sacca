<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimetableApiTest extends TestCase
{
    use RefreshDatabase;

    private User $faculty;

    private Timetable $timetable;

    private Batch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Branch::create(['name' => 'Test', 'code' => 'TST', 'is_active' => true]);
        $course = Course::create(['name' => 'Aviation', 'code' => 'AV1', 'duration_hours' => 100, 'is_active' => true]);
        $this->batch = Batch::create([
            'branch_id' => $branch->id, 'course_id' => $course->id,
            'name' => 'Batch A', 'start_date' => today(), 'is_active' => true, 'capacity' => 30,
        ]);
        $this->faculty = User::factory()->faculty()->create(['branch_id' => $branch->id, 'is_active' => true]);

        $this->timetable = Timetable::create([
            'batch_id' => $this->batch->id,
            'faculty_id' => $this->faculty->id,
            'subject' => 'Air Navigation',
            'day_of_week' => now()->dayOfWeekIso,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'effective_from' => today()->subDay(),
            'is_active' => true,
        ]);
    }

    public function test_timetable_week_requires_auth(): void
    {
        $this->getJson('/api/v1/timetable/week')->assertUnauthorized();
    }

    public function test_faculty_sees_own_timetable(): void
    {
        $this->actingAs($this->faculty, 'sanctum')
            ->getJson('/api/v1/timetable/week')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_bulk_mark_student_attendance(): void
    {
        $student = User::factory()->student()->create(['branch_id' => $this->faculty->branch_id]);
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'batch_id' => $this->batch->id,
            'enrollment_date' => today(),
            'status' => 'active',
        ]);

        $this->actingAs($this->faculty, 'sanctum')
            ->postJson("/api/v1/student-attendance/class/{$this->timetable->id}", [
                'date' => today()->toDateString(),
                'attendance' => [
                    ['enrollment_id' => $enrollment->id, 'status' => 'present'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('student_attendance', [
            'enrollment_id' => $enrollment->id,
            'timetable_id' => $this->timetable->id,
            'status' => 'present',
        ]);
    }

    public function test_class_roster_returns_enrolled_students(): void
    {
        $student = User::factory()->student()->create(['branch_id' => $this->faculty->branch_id]);
        Enrollment::create([
            'student_id' => $student->id,
            'batch_id' => $this->batch->id,
            'enrollment_date' => today(),
            'status' => 'active',
        ]);

        $this->actingAs($this->faculty, 'sanctum')
            ->getJson("/api/v1/student-attendance/class/{$this->timetable->id}?date=".today()->toDateString())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.submitted', false);
    }
}
