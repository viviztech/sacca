<?php

namespace App\Modules\Compliance\Services;

use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\ComplianceReport;
use App\Models\Enrollment;
use App\Models\PlacementApplication;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ComplianceReportGenerator
{
    public function generate(Branch $branch, string $reportType, Carbon $from, Carbon $to): ComplianceReport
    {
        $data = match ($reportType) {
            'TAHDCO', 'SCDD' => $this->buildGovernmentReport($branch, $from, $to),
            'monthly_summary' => $this->buildMonthlySummary($branch, $from, $to),
            'annual' => $this->buildAnnualReport($branch, $from, $to),
            default => [],
        };

        $pdf = Pdf::loadView('pdf.compliance-report', [
            'branch' => $branch,
            'report_type' => $reportType,
            'from' => $from,
            'to' => $to,
            'data' => $data,
            'generated_at' => now(),
        ]);

        $filename = strtolower($reportType).'_'.$branch->code.'_'.$from->format('Y-m').'.pdf';
        $path = "compliance/{$branch->code}/{$filename}";

        Storage::disk('public')->put($path, $pdf->output());

        return ComplianceReport::create([
            'branch_id' => $branch->id,
            'report_type' => $reportType,
            'period_from' => $from,
            'period_to' => $to,
            'generated_by' => auth()->id(),
            'file_path' => $path,
            'generated_at' => now(),
        ]);
    }

    private function buildGovernmentReport(Branch $branch, Carbon $from, Carbon $to): array
    {
        $students = User::where('branch_id', $branch->id)->where('role', 'student')->where('is_active', true)->get();
        $staff = User::where('branch_id', $branch->id)->whereNotIn('role', ['student', 'visitor'])->where('is_active', true)->get();

        $placed = PlacementApplication::whereHas('student', fn ($q) => $q->where('branch_id', $branch->id))
            ->where('status', 'selected')
            ->whereBetween('shortlisted_at', [$from, $to])
            ->count();

        $avgAttendance = AttendanceRecord::where('branch_id', $branch->id)
            ->whereBetween('date', [$from, $to])
            ->whereIn('status', ['present', 'late'])
            ->count();

        $totalPossible = AttendanceRecord::where('branch_id', $branch->id)
            ->whereBetween('date', [$from, $to])
            ->count();

        return [
            'total_students' => $students->count(),
            'total_staff' => $staff->count(),
            'students_placed' => $placed,
            'average_attendance_pct' => $totalPossible > 0 ? round(($avgAttendance / $totalPossible) * 100, 1) : 0,
            'active_enrollments' => Enrollment::whereHas('batch', fn ($q) => $q->where('branch_id', $branch->id))
                ->where('status', 'active')->count(),
            'students_list' => $students->map(fn ($s) => [
                'name' => $s->name,
                'email' => $s->email,
                'employee_id' => $s->employee_id,
            ]),
            'staff_list' => $staff->map(fn ($s) => [
                'name' => $s->name,
                'role' => $s->role->label(),
                'employee_id' => $s->employee_id,
            ]),
        ];
    }

    private function buildMonthlySummary(Branch $branch, Carbon $from, Carbon $to): array
    {
        return $this->buildGovernmentReport($branch, $from, $to);
    }

    private function buildAnnualReport(Branch $branch, Carbon $from, Carbon $to): array
    {
        return $this->buildGovernmentReport($branch, $from, $to);
    }
}
