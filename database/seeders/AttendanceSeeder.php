<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceGeoFence;
use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // Geo-fences per branch
        $cbe = Branch::where('code', 'CBE')->first();
        $ker = Branch::where('code', 'KER')->first();

        AttendanceGeoFence::firstOrCreate(['branch_id' => $cbe->id], [
            'name' => 'CBE Main Campus', 'latitude' => 11.0168, 'longitude' => 76.9558, 'radius_meters' => 300, 'is_active' => true,
        ]);
        AttendanceGeoFence::firstOrCreate(['branch_id' => $ker->id], [
            'name' => 'Kerala Campus', 'latitude' => 9.9312, 'longitude' => 76.2673, 'radius_meters' => 300, 'is_active' => true,
        ]);

        $staff = User::where('is_active', true)->whereNotIn('role', ['visitor'])->get();

        // Seed last 30 days of attendance
        for ($d = 29; $d >= 0; $d--) {
            $date = Carbon::today()->subDays($d);
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($staff as $user) {
                if (! $user->branch_id) {
                    continue;
                }
                if (AttendanceRecord::where('user_id', $user->id)->whereDate('date', $date)->exists()) {
                    continue;
                }

                $rand = rand(1, 10);
                $status = match (true) {
                    $rand <= 7 => AttendanceStatus::Present,
                    $rand <= 8 => AttendanceStatus::Late,
                    default => AttendanceStatus::Absent,
                };

                $checkIn = $status !== AttendanceStatus::Absent
                    ? $date->copy()->setHour($status === AttendanceStatus::Late ? rand(9, 10) : 9)->setMinute(rand(0, 20))
                    : null;
                $checkOut = $checkIn ? $date->copy()->setHour(rand(17, 19))->setMinute(rand(0, 59)) : null;

                AttendanceRecord::create([
                    'user_id' => $user->id,
                    'branch_id' => $user->branch_id,
                    'date' => $date,
                    'status' => $status,
                    'check_in_at' => $checkIn,
                    'check_out_at' => $checkOut,
                    'check_in_lat' => 11.0168,
                    'check_in_lng' => 76.9558,
                    'gps_verified' => true,
                    'late_minutes' => $status === AttendanceStatus::Late ? rand(10, 45) : 0,
                    'overtime_minutes' => $checkOut && $checkOut->hour >= 18 ? rand(0, 90) : 0,
                ]);
            }
        }
    }
}
