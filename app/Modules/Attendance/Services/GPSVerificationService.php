<?php

namespace App\Modules\Attendance\Services;

use App\Models\AttendanceGeoFence;
use App\Models\AttendanceRecord;
use App\Models\User;

class GPSVerificationService
{
    /** Maximum acceptable GPS accuracy in meters */
    private const MAX_ACCURACY_METERS = 50;

    /** Speed threshold for spoofing detection: 50 km/min = ~833 m/s */
    private const MAX_SPEED_KM_PER_MIN = 50;

    public function verify(
        User $user,
        float $lat,
        float $lng,
        float $accuracy,
        string $deviceId
    ): GPSVerificationResult {
        if ($accuracy > self::MAX_ACCURACY_METERS) {
            $max = self::MAX_ACCURACY_METERS;

            return GPSVerificationResult::fail('low_accuracy', "GPS accuracy ({$accuracy}m) exceeds {$max}m threshold.");
        }

        $fence = AttendanceGeoFence::where('branch_id', $user->branch_id)
            ->where('is_active', true)
            ->first();

        if (! $fence) {
            return GPSVerificationResult::pass(true, 'No geo-fence configured for branch — check-in accepted.');
        }

        $distance = $this->haversineDistance($lat, $lng, $fence->latitude, $fence->longitude);

        if ($distance > $fence->radius_meters) {
            return GPSVerificationResult::fail(
                'location_mismatch',
                "You are {$distance}m from the branch. Maximum allowed distance is {$fence->radius_meters}m."
            );
        }

        if ($this->isSuspiciousLocation($user, $lat, $lng)) {
            return GPSVerificationResult::fail('suspicious_location', 'Unusually rapid location change detected.');
        }

        return GPSVerificationResult::pass(true, 'Location verified.');
    }

    /**
     * Haversine formula — returns distance in meters.
     */
    public function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    private function isSuspiciousLocation(User $user, float $lat, float $lng): bool
    {
        $lastRecord = AttendanceRecord::where('user_id', $user->id)
            ->whereNotNull('check_in_at')
            ->whereNotNull('check_in_lat')
            ->latest('check_in_at')
            ->first();

        if (! $lastRecord) {
            return false;
        }

        $distance = $this->haversineDistance(
            $lat, $lng,
            (float) $lastRecord->check_in_lat,
            (float) $lastRecord->check_in_lng
        );

        $minutesAgo = now()->diffInMinutes($lastRecord->check_in_at);

        if ($minutesAgo < 1) {
            return false;
        }

        $speedKmPerMin = ($distance / 1000) / $minutesAgo;

        return $speedKmPerMin > self::MAX_SPEED_KM_PER_MIN;
    }
}
