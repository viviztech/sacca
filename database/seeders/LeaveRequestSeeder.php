<?php

namespace Database\Seeders;

use App\Enums\LeaveStatus;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::where('is_active', true)->whereNotIn('role', ['student', 'visitor', 'super_admin'])->get();
        $leaveTypes = LeaveType::all();
        if ($leaveTypes->isEmpty()) {
            return;
        }

        foreach ($staff as $employee) {
            foreach ($leaveTypes as $type) {
                LeaveBalance::firstOrCreate(
                    ['user_id' => $employee->id, 'leave_type_id' => $type->id, 'year' => 2026],
                    ['total_days' => $type->max_days_per_year, 'used_days' => rand(0, 3), 'pending_days' => 0]
                );
            }

            $cl = $leaveTypes->firstWhere('code', 'CL');
            if (! $cl) {
                continue;
            }

            // Approved leave (past)
            LeaveRequest::firstOrCreate(
                ['user_id' => $employee->id, 'leave_type_id' => $cl->id, 'from_date' => now()->subDays(rand(10, 20))->toDateString()],
                [
                    'to_date' => now()->subDays(rand(7, 9))->toDateString(),
                    'total_days' => 2, 'reason' => 'Personal work',
                    'status' => LeaveStatus::Approved, 'approver_id' => User::where('role', 'hr_manager')->value('id'),
                    'approved_at' => now()->subDays(10),
                ]
            );
        }

        // One pending leave for HR to action
        $faculty = User::where('role', 'faculty')->first();
        $sl = $leaveTypes->firstWhere('code', 'SL');
        if ($faculty && $sl) {
            LeaveRequest::firstOrCreate(
                ['user_id' => $faculty->id, 'leave_type_id' => $sl->id, 'from_date' => now()->addDays(3)->toDateString()],
                [
                    'to_date' => now()->addDays(4)->toDateString(),
                    'total_days' => 2, 'reason' => 'Medical appointment',
                    'status' => LeaveStatus::Pending,
                ]
            );
            LeaveBalance::where('user_id', $faculty->id)->where('leave_type_id', $sl->id)->where('year', 2026)
                ->increment('pending_days', 2);
        }
    }
}
