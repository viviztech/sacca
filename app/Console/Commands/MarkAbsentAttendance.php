<?php

namespace App\Console\Commands;

use App\Enums\AttendanceStatus;
use App\Enums\UserRole;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('attendance:mark-absent')]
#[Description('Mark all staff who have not checked in today as absent')]
class MarkAbsentAttendance extends Command
{
    public function handle(): int
    {
        $today = Carbon::today();

        $staffWhoCheckedIn = AttendanceRecord::whereDate('date', $today)
            ->pluck('user_id');

        $absentStaff = User::where('is_active', true)
            ->whereNotIn('role', [UserRole::Student->value, UserRole::Visitor->value])
            ->whereNotIn('id', $staffWhoCheckedIn)
            ->get();

        $count = 0;
        foreach ($absentStaff as $user) {
            AttendanceRecord::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'date' => $today,
                'status' => AttendanceStatus::Absent,
                'gps_verified' => false,
            ]);
            $count++;
        }

        $this->info("Marked {$count} staff as absent for {$today->toDateString()}.");

        return self::SUCCESS;
    }
}
