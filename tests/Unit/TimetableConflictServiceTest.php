<?php

namespace Tests\Unit;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Timetable;
use App\Models\User;
use App\Modules\Academic\Services\TimetableConflictService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimetableConflictServiceTest extends TestCase
{
    use RefreshDatabase;

    private TimetableConflictService $service;

    private Batch $batch;

    private Batch $otherBatch;

    private User $faculty;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TimetableConflictService;

        $branch = Branch::create(['name' => 'Test Branch', 'code' => 'TST', 'is_active' => true]);
        $course = Course::create(['name' => 'Aviation', 'code' => 'AV1', 'duration_hours' => 100, 'is_active' => true]);

        $this->batch = Batch::create([
            'branch_id' => $branch->id, 'course_id' => $course->id,
            'name' => 'Batch A', 'start_date' => today(), 'is_active' => true, 'capacity' => 30,
        ]);
        $this->otherBatch = Batch::create([
            'branch_id' => $branch->id, 'course_id' => $course->id,
            'name' => 'Batch B', 'start_date' => today(), 'is_active' => true, 'capacity' => 30,
        ]);
        $this->faculty = User::factory()->faculty()->create(['branch_id' => $branch->id]);
    }

    public function test_no_conflict_when_no_existing_timetables(): void
    {
        $result = $this->service->check($this->batch->id, $this->faculty->id, 1, '09:00', '10:00');
        $this->assertFalse($result['has_conflict']);
    }

    public function test_detects_faculty_conflict_on_same_day_and_time(): void
    {
        Timetable::create([
            'batch_id' => $this->otherBatch->id,
            'faculty_id' => $this->faculty->id,
            'subject' => 'Math',
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'effective_from' => today(),
            'is_active' => true,
        ]);

        $result = $this->service->check($this->batch->id, $this->faculty->id, 1, '09:30', '10:30');
        $this->assertTrue($result['has_conflict']);
        $this->assertStringContainsString('Faculty', $result['reason']);
    }

    public function test_detects_batch_conflict_on_same_day_and_time(): void
    {
        $otherFaculty = User::factory()->faculty()->create(['branch_id' => $this->faculty->branch_id]);

        Timetable::create([
            'batch_id' => $this->batch->id,
            'faculty_id' => $otherFaculty->id,
            'subject' => 'English',
            'day_of_week' => 2,
            'start_time' => '14:00',
            'end_time' => '15:00',
            'effective_from' => today(),
            'is_active' => true,
        ]);

        $result = $this->service->check($this->batch->id, $this->faculty->id, 2, '14:00', '15:00');
        $this->assertTrue($result['has_conflict']);
        $this->assertStringContainsString('Batch', $result['reason']);
    }

    public function test_no_conflict_for_adjacent_non_overlapping_slots(): void
    {
        Timetable::create([
            'batch_id' => $this->batch->id,
            'faculty_id' => $this->faculty->id,
            'subject' => 'Physics',
            'day_of_week' => 3,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'effective_from' => today(),
            'is_active' => true,
        ]);

        // Starts exactly when the previous ends — no overlap
        $result = $this->service->check($this->otherBatch->id, $this->faculty->id, 3, '10:00', '11:00');
        $this->assertFalse($result['has_conflict']);
    }

    public function test_no_conflict_on_different_day(): void
    {
        Timetable::create([
            'batch_id' => $this->batch->id,
            'faculty_id' => $this->faculty->id,
            'subject' => 'Chemistry',
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'effective_from' => today(),
            'is_active' => true,
        ]);

        $result = $this->service->check($this->batch->id, $this->faculty->id, 2, '09:00', '10:00');
        $this->assertFalse($result['has_conflict']);
    }

    public function test_edit_excludes_own_entry(): void
    {
        $existing = Timetable::create([
            'batch_id' => $this->batch->id,
            'faculty_id' => $this->faculty->id,
            'subject' => 'Science',
            'day_of_week' => 4,
            'start_time' => '11:00',
            'end_time' => '12:00',
            'effective_from' => today(),
            'is_active' => true,
        ]);

        // Editing the same entry should not conflict with itself
        $result = $this->service->check($this->batch->id, $this->faculty->id, 4, '11:00', '12:00', $existing->id);
        $this->assertFalse($result['has_conflict']);
    }
}
