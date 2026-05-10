<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Database\Seeder;

class VisitorSeeder extends Seeder
{
    public function run(): void
    {
        $cbe = Branch::where('code', 'CBE')->first();
        if (! $cbe) {
            return;
        }

        $host = User::where('role', 'branch_admin')->first();

        $visitors = [
            ['name' => 'Mr. Rajesh Verma', 'phone' => '9800012345', 'org' => 'IndiGo HR Team', 'purpose' => 'Placement drive discussion', 'hours_ago' => 2, 'out' => true],
            ['name' => 'Dr. Meenakshi Iyer', 'phone' => '9800023456', 'org' => 'DGCA Inspection', 'purpose' => 'Annual institute audit', 'hours_ago' => 26, 'out' => true],
            ['name' => 'Mr. Arun Krishnan', 'phone' => '9800034567', 'org' => 'Parent', 'purpose' => 'Meeting with coordinator regarding student attendance', 'hours_ago' => 1, 'out' => false],
            ['name' => 'Ms. Preethi Sundaram', 'phone' => '9800045678', 'org' => 'SpiceJet Recruitment', 'purpose' => 'Pre-placement talk for students', 'hours_ago' => 48, 'out' => true],
        ];

        foreach ($visitors as $v) {
            $checkIn = now()->subHours($v['hours_ago']);
            VisitorLog::firstOrCreate(
                ['visitor_name' => $v['name'], 'branch_id' => $cbe->id],
                [
                    'phone' => $v['phone'],
                    'organization' => $v['org'],
                    'purpose' => $v['purpose'],
                    'host_user_id' => $host?->id,
                    'check_in_at' => $checkIn,
                    'check_out_at' => $v['out'] ? $checkIn->copy()->addHours(rand(1, 3)) : null,
                    'badge_number' => 'VIS-'.str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                ]
            );
        }
    }
}
