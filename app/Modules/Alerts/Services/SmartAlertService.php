<?php

namespace App\Modules\Alerts\Services;

use App\Models\AttendanceRecord;
use App\Models\DailyWorkReport;
use App\Models\Enrollment;
use App\Models\PlacementApplication;
use App\Models\PlacementDrive;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Support\WhatsAppClient;
use Illuminate\Support\Carbon;

class SmartAlertService
{
    public function __construct(private readonly WhatsAppClient $whatsapp) {}

    public function runAll(): array
    {
        $results = [];
        $rules = config('sacca_alerts.rules', []);

        foreach ($rules as $rule) {
            if (! ($rule['enabled'] ?? true)) {
                continue;
            }

            $count = match ($rule['id']) {
                'faculty_absent_streak' => $this->checkFacultyAbsentStreak($rule['threshold']),
                'work_report_missing' => $this->checkMissingWorkReports(),
                'student_low_attendance' => $this->checkStudentLowAttendance($rule['threshold']),
                'placement_eligible_nudge' => $this->checkPlacementNudge($rule['days_after_drive']),
                default => 0,
            };

            $results[$rule['id']] = $count;
        }

        return $results;
    }

    private function checkFacultyAbsentStreak(int $threshold): int
    {
        $alerted = 0;
        $today = Carbon::today();
        $from = $today->copy()->subDays($threshold - 1);

        $facultyUsers = User::whereIn('role', ['faculty', 'academic_coordinator'])
            ->where('is_active', true)
            ->get();

        foreach ($facultyUsers as $faculty) {
            $absentDays = AttendanceRecord::where('user_id', $faculty->id)
                ->whereBetween('date', [$from, $today])
                ->where('status', 'absent')
                ->count();

            if ($absentDays >= $threshold) {
                $hrManagers = User::where('role', 'hr_manager')
                    ->where('branch_id', $faculty->branch_id)
                    ->get();

                foreach ($hrManagers as $hr) {
                    if ($hr->whatsapp_number) {
                        $this->whatsapp->sendTemplate($hr->whatsapp_number, 'sacca_faculty_absent_alert', [
                            ['type' => 'body', 'parameters' => [
                                ['type' => 'text', 'text' => $faculty->name],
                                ['type' => 'text', 'text' => (string) $threshold],
                            ]],
                        ]);
                        $alerted++;
                    }
                }
            }
        }

        return $alerted;
    }

    private function checkMissingWorkReports(): int
    {
        $alerted = 0;
        $today = Carbon::today();

        $staff = User::whereIn('role', ['faculty', 'academic_coordinator', 'hr_manager', 'placement_officer'])
            ->where('is_active', true)
            ->get();

        foreach ($staff as $employee) {
            $report = DailyWorkReport::where('user_id', $employee->id)
                ->whereDate('report_date', $today)
                ->first();

            if (! $report || $report->status === 'pending') {
                $managers = User::whereIn('role', ['branch_admin', 'super_admin'])
                    ->where('branch_id', $employee->branch_id)
                    ->get();

                foreach ($managers as $manager) {
                    if ($manager->whatsapp_number) {
                        $this->whatsapp->sendTemplate($manager->whatsapp_number, 'sacca_work_report_escalation', [
                            ['type' => 'body', 'parameters' => [
                                ['type' => 'text', 'text' => $employee->name],
                                ['type' => 'text', 'text' => $today->format('d M Y')],
                            ]],
                        ]);
                        $alerted++;
                    }
                }
            }
        }

        return $alerted;
    }

    private function checkStudentLowAttendance(int $threshold): int
    {
        $alerted = 0;
        $month = now()->month;
        $year = now()->year;

        $enrollments = Enrollment::where('status', 'active')->with(['student', 'batch'])->get();

        foreach ($enrollments as $enrollment) {
            $totalClasses = StudentAttendance::where('enrollment_id', $enrollment->id)
                ->whereYear('class_date', $year)
                ->whereMonth('class_date', $month)
                ->count();

            if ($totalClasses < 5) {
                continue;
            }

            $presentClasses = StudentAttendance::where('enrollment_id', $enrollment->id)
                ->whereYear('class_date', $year)
                ->whereMonth('class_date', $month)
                ->whereIn('status', ['present', 'late'])
                ->count();

            $attendancePct = ($presentClasses / $totalClasses) * 100;

            if ($attendancePct < $threshold) {
                $student = $enrollment->student;
                if ($student->whatsapp_number) {
                    $this->whatsapp->sendTemplate($student->whatsapp_number, 'sacca_low_attendance_alert', [
                        ['type' => 'body', 'parameters' => [
                            ['type' => 'text', 'text' => $student->name],
                            ['type' => 'text', 'text' => round($attendancePct, 1).'%'],
                        ]],
                    ]);
                    $alerted++;
                }
            }
        }

        return $alerted;
    }

    private function checkPlacementNudge(int $daysAfterDrive): int
    {
        $alerted = 0;
        $cutoff = Carbon::today()->subDays($daysAfterDrive);

        $drives = PlacementDrive::where('is_active', true)
            ->where('created_at', '<=', $cutoff)
            ->get();

        foreach ($drives as $drive) {
            $appliedStudentIds = PlacementApplication::where('drive_id', $drive->id)
                ->pluck('student_id');

            $eligibleStudents = User::where('role', 'student')
                ->where('branch_id', $drive->branch_id)
                ->whereNotIn('id', $appliedStudentIds)
                ->get();

            foreach ($eligibleStudents as $student) {
                if ($student->whatsapp_number) {
                    $this->whatsapp->sendTemplate($student->whatsapp_number, 'sacca_placement_nudge', [
                        ['type' => 'body', 'parameters' => [
                            ['type' => 'text', 'text' => $student->name],
                            ['type' => 'text', 'text' => $drive->title],
                        ]],
                    ]);
                    $alerted++;
                }
            }
        }

        return $alerted;
    }
}
