<?php

namespace App\Livewire\Dashboard;

use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\Complaint;
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
#[Title('Super Admin Dashboard')]
class SuperAdminDashboard extends Component
{
    public function render(): View
    {
        $today = today();

        $branches = Cache::remember('dashboard.branches', 300, fn () => Branch::where('is_active', true)->get());

        $branchStats = $branches->map(function (Branch $branch) use ($today) {
            $totalStaff = User::where('branch_id', $branch->id)
                ->where('is_active', true)
                ->whereNotIn('role', ['student', 'visitor'])
                ->count();

            $totalStudents = User::where('branch_id', $branch->id)
                ->where('role', 'student')
                ->where('is_active', true)
                ->count();

            $presentToday = AttendanceRecord::where('branch_id', $branch->id)
                ->whereDate('date', $today)
                ->whereIn('status', ['present', 'late'])
                ->count();

            $attendancePct = $totalStaff > 0 ? round(($presentToday / $totalStaff) * 100) : 0;

            $openLeaves = LeaveRequest::whereHas('user', fn ($q) => $q->where('branch_id', $branch->id))
                ->where('status', 'pending')
                ->count();

            $pendingTasks = Task::where('branch_id', $branch->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();

            $placedStudents = PlacementApplication::whereHas('student', fn ($q) => $q->where('branch_id', $branch->id))
                ->where('status', 'selected')
                ->count();

            return [
                'branch' => $branch,
                'total_staff' => $totalStaff,
                'total_students' => $totalStudents,
                'present_today' => $presentToday,
                'attendance_pct' => $attendancePct,
                'open_leaves' => $openLeaves,
                'pending_tasks' => $pendingTasks,
                'placed_students' => $placedStudents,
            ];
        });

        $globalStats = [
            'total_users' => User::where('is_active', true)->count(),
            'total_staff' => User::where('is_active', true)->whereNotIn('role', ['student', 'visitor'])->count(),
            'total_students' => User::where('role', 'student')->where('is_active', true)->count(),
            'present_today' => AttendanceRecord::whereDate('date', $today)->whereIn('status', ['present', 'late'])->count(),
            'open_leaves' => LeaveRequest::where('status', 'pending')->count(),
            'open_complaints' => Complaint::where('status', 'open')->count(),
            'pending_tasks' => Task::whereIn('status', ['pending', 'in_progress'])->count(),
            'total_placed' => PlacementApplication::where('status', 'selected')->count(),
        ];

        return view('livewire.dashboard.super-admin-dashboard', compact('branchStats', 'globalStats'));
    }
}
