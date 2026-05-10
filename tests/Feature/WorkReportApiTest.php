<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\DailyWorkReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkReportApiTest extends TestCase
{
    use RefreshDatabase;

    private User $faculty;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch = Branch::create(['name' => 'Test', 'code' => 'TST', 'is_active' => true]);
        $this->faculty = User::factory()->faculty()->create(['branch_id' => $this->branch->id, 'is_active' => true]);
    }

    public function test_work_report_submission_requires_auth(): void
    {
        $this->postJson('/api/v1/work-reports', ['work_summary' => 'Done some work today.'])
            ->assertUnauthorized();
    }

    public function test_faculty_can_submit_work_report(): void
    {
        $this->actingAs($this->faculty, 'sanctum')
            ->postJson('/api/v1/work-reports', [
                'work_summary' => 'Completed lesson planning for batch A and reviewed quiz results.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('daily_work_reports', [
            'user_id' => $this->faculty->id,
            'status' => 'submitted',
        ]);
    }

    public function test_work_summary_must_be_at_least_20_characters(): void
    {
        $this->actingAs($this->faculty, 'sanctum')
            ->postJson('/api/v1/work-reports', ['work_summary' => 'Too short'])
            ->assertUnprocessable();
    }

    public function test_submitting_twice_updates_existing_report(): void
    {
        DailyWorkReport::create([
            'user_id' => $this->faculty->id,
            'branch_id' => $this->branch->id,
            'report_date' => today(),
            'work_summary' => 'First submission of the day.',
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $this->actingAs($this->faculty, 'sanctum')
            ->postJson('/api/v1/work-reports', [
                'work_summary' => 'Updated: completed all planned tasks for today successfully.',
            ])
            ->assertStatus(201);

        $this->assertDatabaseCount('daily_work_reports', 1);
    }

    public function test_get_todays_report(): void
    {
        DailyWorkReport::create([
            'user_id' => $this->faculty->id,
            'branch_id' => $this->branch->id,
            'report_date' => today(),
            'work_summary' => 'Finished all tasks.',
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $this->actingAs($this->faculty, 'sanctum')
            ->getJson('/api/v1/work-reports')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
