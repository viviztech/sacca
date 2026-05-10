<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private User $employee;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch = Branch::create(['name' => 'Test', 'code' => 'TST', 'is_active' => true]);
        $this->manager = User::factory()->branchAdmin()->create(['branch_id' => $this->branch->id]);
        $this->employee = User::factory()->faculty()->create(['branch_id' => $this->branch->id]);
    }

    public function test_task_status_update_via_api(): void
    {
        $task = Task::create([
            'title' => 'Prepare lesson plan',
            'assigned_by' => $this->manager->id,
            'assigned_to_user_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $this->actingAs($this->employee, 'sanctum')
            ->patchJson("/api/v1/tasks/{$task->id}/status", ['status' => 'in_progress'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertEquals('in_progress', $task->fresh()->status);
    }

    public function test_completing_task_sets_completed_at(): void
    {
        $task = Task::create([
            'title' => 'Review reports',
            'assigned_by' => $this->manager->id,
            'assigned_to_user_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'priority' => 'high',
            'status' => 'in_progress',
        ]);

        $this->actingAs($this->employee, 'sanctum')
            ->patchJson("/api/v1/tasks/{$task->id}/status", ['status' => 'completed'])
            ->assertOk();

        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_overdue_task_is_detected_correctly(): void
    {
        $task = new Task([
            'due_date' => now()->subDays(2),
            'status' => 'pending',
        ]);
        $this->assertTrue($task->isOverdue());
    }

    public function test_completed_task_is_not_overdue(): void
    {
        $task = new Task([
            'due_date' => now()->subDays(2),
            'status' => 'completed',
        ]);
        $this->assertFalse($task->isOverdue());
    }

    public function test_task_list_api_requires_auth(): void
    {
        $this->getJson('/api/v1/tasks')->assertUnauthorized();
    }

    public function test_employee_sees_assigned_tasks(): void
    {
        Task::create([
            'title' => 'My task',
            'assigned_by' => $this->manager->id,
            'assigned_to_user_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'priority' => 'low',
            'status' => 'pending',
        ]);

        $this->actingAs($this->employee, 'sanctum')
            ->getJson('/api/v1/tasks')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
