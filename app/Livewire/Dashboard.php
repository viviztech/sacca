<?php

namespace App\Livewire;

use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\Complaint;
use App\Models\DailyWorkReport;
use App\Models\Enrollment;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LmsMaterial;
use App\Models\PlacementApplication;
use App\Models\PlacementDrive;
use App\Models\Task;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render(): View
    {
        $user = auth()->user();
        $today = today();
        $data = [];

        // ── Super Admin ──────────────────────────────────────────────
        if ($user->isSuperAdmin()) {
            $branches = Branch::where('is_active', true)->get();

            $data['branch_stats'] = $branches->map(fn (Branch $branch) => [
                'branch' => $branch,
                'total_staff' => User::where('branch_id', $branch->id)
                    ->where('is_active', true)
                    ->whereNotIn('role', ['student', 'visitor'])->count(),
                'total_students' => User::where('branch_id', $branch->id)
                    ->where('role', 'student')->where('is_active', true)->count(),
                'present_today' => AttendanceRecord::where('branch_id', $branch->id)
                    ->whereDate('date', $today)->whereIn('status', ['present', 'late'])->count(),
                'attendance_pct' => User::where('branch_id', $branch->id)->where('is_active', true)
                    ->whereNotIn('role', ['student', 'visitor'])->count() > 0
                    ? round((AttendanceRecord::where('branch_id', $branch->id)->whereDate('date', $today)
                        ->whereIn('status', ['present', 'late'])->count()
                        / User::where('branch_id', $branch->id)->where('is_active', true)
                            ->whereNotIn('role', ['student', 'visitor'])->count()) * 100)
                    : 0,
                'open_leaves' => LeaveRequest::whereHas('user', fn ($q) => $q->where('branch_id', $branch->id))
                    ->where('status', 'pending')->count(),
                'pending_tasks' => Task::where('branch_id', $branch->id)
                    ->whereIn('status', ['pending', 'in_progress'])->count(),
                'placed_students' => PlacementApplication::whereHas('student', fn ($q) => $q->where('branch_id', $branch->id))
                    ->where('status', 'selected')->count(),
            ]);

            $data['global'] = [
                'total_users' => User::where('is_active', true)->count(),
                'total_staff' => User::where('is_active', true)->whereNotIn('role', ['student', 'visitor'])->count(),
                'total_students' => User::where('role', 'student')->where('is_active', true)->count(),
                'present_today' => AttendanceRecord::whereDate('date', $today)->whereIn('status', ['present', 'late'])->count(),
                'open_leaves' => LeaveRequest::where('status', 'pending')->count(),
                'open_complaints' => Complaint::where('status', 'open')->count(),
                'pending_tasks' => Task::whereIn('status', ['pending', 'in_progress'])->count(),
                'total_placed' => PlacementApplication::where('status', 'selected')->count(),
            ];
        }

        // ── Branch Admin / HR Manager ────────────────────────────────
        if ($user->isBranchAdmin() || $user->isHrManager()) {
            $branchId = $user->branch_id;

            $totalStaff = User::where('branch_id', $branchId)->where('is_active', true)
                ->whereNotIn('role', ['student', 'visitor'])->count();

            $presentToday = AttendanceRecord::where('branch_id', $branchId)
                ->whereDate('date', $today)->whereIn('status', ['present', 'late'])->count();

            $data['stats'] = [
                'total_staff' => $totalStaff,
                'total_students' => User::where('branch_id', $branchId)->where('role', 'student')->where('is_active', true)->count(),
                'present_today' => $presentToday,
                'late_today' => AttendanceRecord::where('branch_id', $branchId)->whereDate('date', $today)->where('status', 'late')->count(),
                'absent_today' => max(0, $totalStaff - AttendanceRecord::where('branch_id', $branchId)->whereDate('date', $today)->count()),
                'attendance_pct' => $totalStaff > 0 ? round(($presentToday / $totalStaff) * 100) : 0,
                'pending_leaves' => LeaveRequest::whereHas('user', fn ($q) => $q->where('branch_id', $branchId))->where('status', 'pending')->count(),
                'pending_tasks' => Task::where('branch_id', $branchId)->whereIn('status', ['pending', 'in_progress'])->count(),
                'overdue_tasks' => Task::where('branch_id', $branchId)->where('status', '!=', 'completed')->where('due_date', '<', $today)->count(),
                'open_complaints' => Complaint::where('branch_id', $branchId)->where('status', 'open')->count(),
                'missing_reports' => User::where('branch_id', $branchId)->where('is_active', true)
                    ->whereIn('role', ['faculty', 'academic_coordinator', 'placement_officer'])
                    ->whereDoesntHave('dailyWorkReports', fn ($q) => $q->whereDate('report_date', $today)->where('status', 'submitted'))->count(),
                'total_enrollments' => Enrollment::whereHas('batch', fn ($q) => $q->where('branch_id', $branchId))->where('status', 'active')->count(),
                'placed' => PlacementApplication::whereHas('student', fn ($q) => $q->where('branch_id', $branchId))->where('status', 'selected')->count(),
            ];

            $data['recent_leaves'] = LeaveRequest::whereHas('user', fn ($q) => $q->where('branch_id', $branchId))
                ->where('status', 'pending')->with(['user', 'leaveType'])->latest()->limit(5)->get();

            $data['recent_tasks'] = Task::where('branch_id', $branchId)
                ->whereIn('status', ['pending', 'in_progress'])->with('assignedTo')->latest()->limit(5)->get();
        }

        // ── Faculty / Academic Coordinator ───────────────────────────
        if ($user->isFaculty() || $user->isAcademicCoordinator()) {
            $data['today_attendance'] = AttendanceRecord::where('user_id', $user->id)->whereDate('date', $today)->first();
            $data['pending_tasks'] = Task::where('assigned_to_user_id', $user->id)->whereIn('status', ['pending', 'in_progress'])->count();
            $data['todays_report'] = DailyWorkReport::where('user_id', $user->id)->whereDate('report_date', $today)->first();
            $data['recent_announcements'] = Announcement::where('published_at', '<=', now())
                ->where(fn ($q) => $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id))
                ->latest('published_at')->limit(3)->get();
        }

        // ── Student ──────────────────────────────────────────────────
        if ($user->isStudent()) {
            $data['enrollments'] = Enrollment::where('student_id', $user->id)->where('status', 'active')->with(['batch.course'])->get();
            $data['leave_balances'] = LeaveBalance::where('user_id', $user->id)->where('year', now()->year)->with('leaveType')->get();
            $data['upcoming_drives'] = PlacementDrive::where('is_active', true)->where('drive_date', '>=', $today)
                ->where('branch_id', $user->branch_id)->with('company')->limit(3)->get();
            $data['recent_materials'] = LmsMaterial::where('is_published', true)
                ->whereHas('batch.enrollments', fn ($q) => $q->where('student_id', $user->id)->where('status', 'active'))
                ->latest()->limit(5)->get();
            $data['recent_announcements'] = Announcement::where('published_at', '<=', now())
                ->where(fn ($q) => $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id))
                ->latest('published_at')->limit(3)->get();
            $data['pending_leave'] = LeaveRequest::where('user_id', $user->id)->where('status', 'pending')->count();
        }

        // ── Placement Officer ────────────────────────────────────────
        if ($user->isPlacementOfficer()) {
            $data['active_drives'] = PlacementDrive::where('branch_id', $user->branch_id)->where('is_active', true)->where('drive_date', '>=', $today)->count();
            $data['pending_tasks'] = Task::where('assigned_to_user_id', $user->id)->whereIn('status', ['pending', 'in_progress'])->count();
            $data['recent_announcements'] = Announcement::where('published_at', '<=', now())
                ->where(fn ($q) => $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id))
                ->latest('published_at')->limit(3)->get();
        }

        return view('livewire.dashboard', compact('user', 'data'));
    }
}
