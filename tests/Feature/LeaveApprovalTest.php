<?php

namespace Tests\Feature;

use App\Enums\LeaveStatus;
use App\Models\Branch;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Modules\Leave\Actions\ApproveLeaveAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LeaveApprovalTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private LeaveType $leaveType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Test Branch', 'code' => 'TST', 'is_active' => true]);
        $this->leaveType = LeaveType::create([
            'name' => 'Casual Leave',
            'code' => 'CL',
            'max_days_per_year' => 12,
            'carry_forward' => false,
            'requires_document' => false,
            'is_active' => true,
        ]);
    }

    public function test_hr_manager_can_approve_leave_via_api(): void
    {
        Queue::fake();

        $employee = User::factory()->create(['branch_id' => $this->branch->id, 'is_active' => true]);
        $hr = User::factory()->hrManager()->create(['branch_id' => $this->branch->id]);

        $leave = LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $this->leaveType->id,
            'from_date' => now()->addDays(2)->toDateString(),
            'to_date' => now()->addDays(3)->toDateString(),
            'total_days' => 2,
            'reason' => 'Personal work',
            'status' => LeaveStatus::Pending,
        ]);

        LeaveBalance::create([
            'user_id' => $employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => now()->year,
            'total_days' => 12,
            'used_days' => 0,
            'pending_days' => 2,
        ]);

        app(ApproveLeaveAction::class)->approve($leave, $hr);

        $leave->refresh();
        $this->assertEquals(LeaveStatus::Approved, $leave->status);
        $this->assertEquals($hr->id, $leave->approver_id);
        $this->assertNotNull($leave->approved_at);
    }

    public function test_approved_leave_updates_balance(): void
    {
        Queue::fake();

        $employee = User::factory()->create(['branch_id' => $this->branch->id]);
        $hr = User::factory()->hrManager()->create(['branch_id' => $this->branch->id]);

        $leave = LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $this->leaveType->id,
            'from_date' => now()->addDays(2)->toDateString(),
            'to_date' => now()->addDays(4)->toDateString(),
            'total_days' => 3,
            'reason' => 'Travel',
            'status' => LeaveStatus::Pending,
        ]);

        $balance = LeaveBalance::create([
            'user_id' => $employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => now()->year,
            'total_days' => 12,
            'used_days' => 0,
            'pending_days' => 3,
        ]);

        app(ApproveLeaveAction::class)->approve($leave, $hr);

        $balance->refresh();
        $this->assertEquals(3, $balance->used_days);
        $this->assertEquals(0, $balance->pending_days);
    }

    public function test_rejected_leave_restores_pending_balance(): void
    {
        Queue::fake();

        $employee = User::factory()->create(['branch_id' => $this->branch->id]);
        $hr = User::factory()->hrManager()->create(['branch_id' => $this->branch->id]);

        $leave = LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $this->leaveType->id,
            'from_date' => now()->addDays(2)->toDateString(),
            'to_date' => now()->addDays(2)->toDateString(),
            'total_days' => 1,
            'reason' => 'Personal',
            'status' => LeaveStatus::Pending,
        ]);

        $balance = LeaveBalance::create([
            'user_id' => $employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => now()->year,
            'total_days' => 12,
            'used_days' => 0,
            'pending_days' => 1,
        ]);

        app(ApproveLeaveAction::class)->reject($leave, $hr, 'Not sufficient reason');

        $balance->refresh();
        $this->assertEquals(LeaveStatus::Rejected, $leave->fresh()->status);
        $this->assertEquals(0, $balance->pending_days);
        $this->assertEquals(0, $balance->used_days);
    }

    public function test_leave_balance_available_days_calculation(): void
    {
        $balance = new LeaveBalance([
            'total_days' => 12,
            'used_days' => 5,
            'pending_days' => 2,
        ]);

        $this->assertEquals(5, $balance->availableDays());
    }

    public function test_available_days_cannot_be_negative(): void
    {
        $balance = new LeaveBalance([
            'total_days' => 5,
            'used_days' => 5,
            'pending_days' => 3,
        ]);

        $this->assertEquals(0, $balance->availableDays());
    }
}
