<?php

namespace App\Modules\Payroll\Services;

use App\Models\AttendanceRecord;
use App\Models\PayrollCycle;
use App\Models\Payslip;
use App\Models\User;
use App\Modules\Payroll\Jobs\GeneratePayslipPdf;

class PayrollGeneratorService
{
    public function generate(PayrollCycle $cycle): int
    {
        $cycle->update(['status' => 'processing', 'generated_at' => now()]);

        $staff = User::where('branch_id', $cycle->branch_id)
            ->where('is_active', true)
            ->whereNotIn('role', ['student', 'visitor'])
            ->get();

        $workingDays = $this->calculateWorkingDays($cycle->month, $cycle->year);
        $generated = 0;

        foreach ($staff as $employee) {
            $attendance = $this->getAttendanceSummary($employee->id, $cycle->month, $cycle->year);

            $basicSalary = $this->getBasicSalary($employee);
            $perDayRate = $basicSalary / $workingDays;
            $grossSalary = round($perDayRate * $attendance['present_days'], 2);

            $deductions = [];
            $netSalary = $grossSalary;

            $payslip = Payslip::updateOrCreate(
                ['payroll_cycle_id' => $cycle->id, 'user_id' => $employee->id],
                [
                    'working_days' => $workingDays,
                    'present_days' => $attendance['present_days'],
                    'absent_days' => $attendance['absent_days'],
                    'basic_salary' => $basicSalary,
                    'deductions' => $deductions,
                    'gross_salary' => $grossSalary,
                    'net_salary' => $netSalary,
                ]
            );

            GeneratePayslipPdf::dispatch($payslip)->onQueue('reports');
            $generated++;
        }

        $cycle->update(['status' => 'draft']);

        return $generated;
    }

    public function publish(PayrollCycle $cycle): void
    {
        $cycle->payslips()->update(['published_at' => now()]);
        $cycle->update(['status' => 'published']);
    }

    private function calculateWorkingDays(int $month, int $year): int
    {
        $days = 0;
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dow = date('N', mktime(0, 0, 0, $month, $d, $year));
            if ($dow < 7) {
                $days++;
            }
        }

        return $days;
    }

    private function getAttendanceSummary(int $userId, int $month, int $year): array
    {
        $records = AttendanceRecord::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $presentDays = $records->whereIn('status.value', ['present', 'late', 'half_day', 'on_leave'])->count();
        $absentDays = $records->where('status.value', 'absent')->count();

        return ['present_days' => $presentDays, 'absent_days' => $absentDays];
    }

    private function getBasicSalary(User $employee): float
    {
        // Default salary — in a real system this comes from a salary_grades table
        return match ($employee->role->value) {
            'faculty' => 35000,
            'academic_coordinator' => 45000,
            'hr_manager' => 40000,
            'branch_admin' => 55000,
            'placement_officer' => 38000,
            default => 30000,
        };
    }
}
