<?php

namespace App\Livewire;

use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\DailyWorkReport;
use App\Models\Enrollment;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LmsMaterial;
use App\Models\PlacementDrive;
use App\Models\Task;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function mount(): void
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            $this->redirect(route('dashboard.super'), navigate: true);
        } elseif ($user->isBranchAdmin() || $user->isHrManager()) {
            $this->redirect(route('dashboard.branch'), navigate: true);
        }
    }

    public function render(): View
    {
        $user = auth()->user();
        $today = today();
        $data = [];

        // Faculty / Academic Coordinator
        if ($user->isFaculty() || $user->isAcademicCoordinator()) {
            $data['today_attendance'] = AttendanceRecord::where('user_id', $user->id)
                ->whereDate('date', $today)->first();

            $data['pending_tasks'] = Task::where('assigned_to_user_id', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])->count();

            $data['todays_report'] = DailyWorkReport::where('user_id', $user->id)
                ->whereDate('report_date', $today)->first();

            $data['recent_announcements'] = Announcement::where('published_at', '<=', now())
                ->where(fn ($q) => $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id))
                ->latest('published_at')->limit(3)->get();
        }

        // Student
        if ($user->isStudent()) {
            $data['enrollments'] = Enrollment::where('student_id', $user->id)
                ->where('status', 'active')->with(['batch.course'])->get();

            $data['leave_balances'] = LeaveBalance::where('user_id', $user->id)
                ->where('year', now()->year)->with('leaveType')->get();

            $data['upcoming_drives'] = PlacementDrive::where('is_active', true)
                ->where('drive_date', '>=', $today)
                ->where('branch_id', $user->branch_id)
                ->with('company')->limit(3)->get();

            $data['recent_materials'] = LmsMaterial::where('is_published', true)
                ->whereHas('batch.enrollments', fn ($q) => $q->where('student_id', $user->id)->where('status', 'active'))
                ->latest()->limit(5)->get();

            $data['recent_announcements'] = Announcement::where('published_at', '<=', now())
                ->where(fn ($q) => $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id))
                ->latest('published_at')->limit(3)->get();

            $data['pending_leave'] = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'pending')->count();
        }

        // Placement Officer
        if ($user->isPlacementOfficer()) {
            $data['active_drives'] = PlacementDrive::where('branch_id', $user->branch_id)
                ->where('is_active', true)->where('drive_date', '>=', $today)->count();

            $data['pending_tasks'] = Task::where('assigned_to_user_id', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])->count();

            $data['recent_announcements'] = Announcement::where('published_at', '<=', now())
                ->where(fn ($q) => $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id))
                ->latest('published_at')->limit(3)->get();
        }

        return view('livewire.dashboard', compact('user', 'data'));
    }
}
