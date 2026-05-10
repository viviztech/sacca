<?php

namespace Database\Seeders;

use App\Models\DailyWorkReport;
use App\Models\User;
use Illuminate\Database\Seeder;

class WorkReportSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::whereIn('role', ['faculty', 'academic_coordinator', 'placement_officer'])->where('is_active', true)->get();

        $summaries = [
            'Conducted theory class on Airport Operations for AGS batch. Marked student attendance. Reviewed 12 assignment submissions and provided feedback. Prepared quiz questions for next week.',
            'Completed LMS material upload for Safety Procedures module. Attended coordination meeting with placement team. Reviewed grooming inspection records and flagged 3 students for follow-up.',
            'Handled 2 student grievances regarding assessment. Updated timetable for next week. Sent reminder for pending assignments to CCT batch students. Coordinated with Air India HR for placement drive.',
            'Conducted practical session on baggage handling simulation. Submitted monthly training hour report. Prepared attendance summary for Q1 management review.',
        ];

        foreach ($staff as $employee) {
            for ($d = 0; $d < 14; $d++) {
                $date = today()->subDays($d);
                if ($date->isWeekend()) {
                    continue;
                }
                if (DailyWorkReport::where('user_id', $employee->id)->whereDate('report_date', $date)->exists()) {
                    continue;
                }

                // Today's report: 30% chance pending for demo
                $submitted = $d === 0 ? rand(0, 10) > 3 : true;

                DailyWorkReport::create([
                    'user_id' => $employee->id,
                    'branch_id' => $employee->branch_id,
                    'report_date' => $date,
                    'work_summary' => $summaries[array_rand($summaries)],
                    'blockers' => rand(0, 1) ? 'Internet connectivity was slow in the afternoon.' : null,
                    'submitted_at' => $submitted ? $date->copy()->setHour(rand(16, 19))->setMinute(rand(0, 59)) : null,
                    'status' => $submitted ? 'submitted' : 'pending',
                ]);
            }
        }
    }
}
