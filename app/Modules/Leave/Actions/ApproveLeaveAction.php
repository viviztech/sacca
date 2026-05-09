<?php

namespace App\Modules\Leave\Actions;

use App\Enums\LeaveStatus;
use App\Jobs\SendLeaveStatusNotification;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Carbon;

class ApproveLeaveAction
{
    public function approve(LeaveRequest $request, User $approver, ?string $comment = null): void
    {
        $request->update([
            'status' => LeaveStatus::Approved,
            'approver_id' => $approver->id,
            'approved_at' => Carbon::now(),
        ]);

        $this->updateLeaveBalance($request, 'approve');

        SendLeaveStatusNotification::dispatch($request)->onQueue('notifications');
    }

    public function reject(LeaveRequest $request, User $approver, string $reason): void
    {
        $request->update([
            'status' => LeaveStatus::Rejected,
            'approver_id' => $approver->id,
            'rejection_reason' => $reason,
        ]);

        $this->updateLeaveBalance($request, 'reject');

        SendLeaveStatusNotification::dispatch($request)->onQueue('notifications');
    }

    public function cancel(LeaveRequest $request): void
    {
        $request->update(['status' => LeaveStatus::Cancelled]);

        $this->updateLeaveBalance($request, 'cancel');
    }

    private function updateLeaveBalance(LeaveRequest $request, string $action): void
    {
        $balance = LeaveBalance::firstOrCreate(
            [
                'user_id' => $request->user_id,
                'leave_type_id' => $request->leave_type_id,
                'year' => $request->from_date->year,
            ],
            ['total_days' => $request->leaveType->max_days_per_year]
        );

        if ($action === 'approve') {
            $balance->decrement('pending_days', $request->total_days);
            $balance->increment('used_days', $request->total_days);
        } elseif ($action === 'reject' || $action === 'cancel') {
            $balance->decrement('pending_days', $request->total_days);
        }
    }
}
