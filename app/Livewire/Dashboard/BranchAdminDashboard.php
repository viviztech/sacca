<?php

namespace App\Livewire\Dashboard;

use App\Models\AttendanceRecord;
use App\Models\Complaint;
use App\Models\Enrollment;
use App\Models\LeaveRequest;
use App\Models\PlacementApplication;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class BranchAdminDashboard extends Component
{
    public function render(): View
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $today = today();

        $cacheKey = "dashboard.branch.{$branchId}";

        $stats = Cache::remember($cacheKey, 60, function () use ($branchId, $today) {
            $totalStaff = User::where('branch_id', $branchId)
                ->where('is_active', true)
                ->whereNotIn('role', ['student', 'visitor'])
                ->count();

            $totalStudents = User::where('branch_id', $branchId)
                ->where('role', 'student')
                ->where('is_active', true)
                ->count();

            $presentToday = AttendanceRecord::where('branch_id', $branchId)
                ->whereDate('date', $today)
                ->whereIn('status', ['present', 'late'])
                ->count();

            $lateToday = AttendanceRecord::where('branch_id', $branchId)
                ->whereDate('date', $today)
                ->where('status', 'late')
                ->count();

            $absentToday = $totalStaff - AttendanceRecord::where('branch_id', $branchId)
                ->whereDate('date', $today)->count();

            $attendancePct = $totalStaff > 0 ? round(($presentToday / max($totalStaff, 1)) * 100) : 0;

            $pendingLeaves = LeaveRequest::whereHas('user', fn ($q) => $q->where('branch_id', $branchId))
                ->where('status', 'pending')->count();

            $pendingTasks = Task::where('branch_id', $branchId)
                ->whereIn('status', ['pending', 'in_progress'])->count();

            $overdueTasks = Task::where('branch_id', $branchId)
                ->where('status', '!=', 'completed')
                ->where('due_date', '<', $today)->count();

            $openComplaints = Complaint::where('branch_id', $branchId)
                ->where('status', 'open')->count();

            $missingReports = User::where('branch_id', $branchId)
                ->where('is_active', true)
                ->whereIn('role', ['faculty', 'academic_coordinator', 'placement_officer'])
                ->whereDoesntHave('dailyWorkReports', fn ($q) => $q->whereDate('report_date', $today)->where('status', 'submitted'))
                ->count();

            $totalEnrollments = Enrollment::whereHas('batch', fn ($q) => $q->where('branch_id', $branchId))
                ->where('status', 'active')->count();

            $placed = PlacementApplication::whereHas('student', fn ($q) => $q->where('branch_id', $branchId))
                ->where('status', 'selected')->count();

            return compact(
                'totalStaff', 'totalStudents', 'presentToday', 'lateToday',
                'absentToday', 'attendancePct', 'pendingLeaves', 'pendingTasks',
                'overdueTasks', 'openComplaints', 'missingReports', 'totalEnrollments', 'placed'
            );
        });

        // Recent activity — not cached (real-time)
        $recentLeaves = LeaveRequest::whereHas('user', fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'pending')->with(['user', 'leaveType'])->latest()->limit(5)->get();

        $recentTasks = Task::where('branch_id', $branchId)
            ->whereIn('status', ['pending', 'in_progress'])->with('assignedTo')->latest()->limit(5)->get();

        return view('livewire.dashboard.branch-admin-dashboard', compact('stats', 'recentLeaves', 'recentTasks'));
    }
}
