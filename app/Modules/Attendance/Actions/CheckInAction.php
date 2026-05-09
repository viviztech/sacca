<?php

namespace App\Modules\Attendance\Actions;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\InOutLog;
use App\Models\User;
use App\Modules\Attendance\Services\GPSVerificationService;
use Illuminate\Support\Carbon;

class CheckInAction
{
    public function __construct(private readonly GPSVerificationService $gps) {}

    public function execute(
        User $user,
        float $lat,
        float $lng,
        float $accuracy,
        string $deviceId
    ): array {
        $today = Carbon::today();

        $existing = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing?->check_in_at) {
            return ['success' => false, 'code' => 'already_checked_in', 'message' => 'You have already checked in today.'];
        }

        $gpsResult = $this->gps->verify($user, $lat, $lng, $accuracy, $deviceId);

        // Hard reject for poor GPS accuracy — soft warn for location mismatch
        if ($gpsResult->failed() && $gpsResult->errorCode === 'low_accuracy') {
            return ['success' => false, 'code' => $gpsResult->errorCode, 'message' => $gpsResult->message];
        }

        $now = Carbon::now();
        $workStartHour = 9;
        $lateThresholdMinutes = 15;
        $isLate = $now->hour > $workStartHour
            || ($now->hour === $workStartHour && $now->minute > $lateThresholdMinutes);

        $record = AttendanceRecord::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
                'branch_id' => $user->branch_id,
                'check_in_at' => $now,
                'check_in_lat' => $lat,
                'check_in_lng' => $lng,
                'check_in_accuracy' => $accuracy,
                'gps_verified' => $gpsResult->passed(),
                'device_id' => $deviceId,
                'status' => $isLate ? AttendanceStatus::Late : AttendanceStatus::Present,
                'late_minutes' => $isLate ? ($now->hour * 60 + $now->minute) - ($workStartHour * 60 + $lateThresholdMinutes) : 0,
            ]
        );

        InOutLog::create([
            'attendance_record_id' => $record->id,
            'user_id' => $user->id,
            'event_type' => 'in',
            'event_at' => $now,
            'lat' => $lat,
            'lng' => $lng,
        ]);

        if ($gpsResult->failed()) {
            return [
                'success' => true,
                'gps_verified' => false,
                'warning' => $gpsResult->message,
                'record' => $record,
            ];
        }

        return ['success' => true, 'gps_verified' => true, 'record' => $record];
    }
}
