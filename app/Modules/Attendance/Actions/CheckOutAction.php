<?php

namespace App\Modules\Attendance\Actions;

use App\Models\AttendanceRecord;
use App\Models\InOutLog;
use App\Models\User;
use Illuminate\Support\Carbon;

class CheckOutAction
{
    public function execute(User $user, float $lat, float $lng): array
    {
        $today = Carbon::today();

        $record = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (! $record || ! $record->check_in_at) {
            return ['success' => false, 'code' => 'not_checked_in', 'message' => 'You have not checked in today.'];
        }

        if ($record->check_out_at) {
            return ['success' => false, 'code' => 'already_checked_out', 'message' => 'You have already checked out today.'];
        }

        $now = Carbon::now();
        $workEndHour = 18;
        $workedMinutes = (int) $record->check_in_at->diffInMinutes($now);
        $expectedMinutes = ($workEndHour - 9) * 60;
        $overtimeMinutes = max(0, $workedMinutes - $expectedMinutes);

        $record->update([
            'check_out_at' => $now,
            'check_out_lat' => $lat,
            'check_out_lng' => $lng,
            'overtime_minutes' => $overtimeMinutes,
        ]);

        InOutLog::create([
            'attendance_record_id' => $record->id,
            'user_id' => $user->id,
            'event_type' => 'out',
            'event_at' => $now,
            'lat' => $lat,
            'lng' => $lng,
        ]);

        return ['success' => true, 'record' => $record->fresh(), 'worked_minutes' => $workedMinutes];
    }
}
