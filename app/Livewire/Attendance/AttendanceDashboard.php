<?php

namespace App\Livewire\Attendance;

use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Poll;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Attendance Dashboard')]
class AttendanceDashboard extends Component
{
    public string $branchFilter = '';

    #[Poll('30s')]
    public function render(): View
    {
        $currentUser = auth()->user();
        $today = today();

        $query = AttendanceRecord::whereDate('date', $today)
            ->with(['user', 'inOutLogs'])
            ->when(
                $this->branchFilter,
                fn ($q) => $q->where('branch_id', $this->branchFilter)
            )
            ->when(
                ! $currentUser->isSuperAdmin(),
                fn ($q) => $q->where('branch_id', $currentUser->branch_id)
            );

        $records = $query->latest('check_in_at')->get();

        $branchId = $this->branchFilter ?: ($currentUser->isSuperAdmin() ? null : $currentUser->branch_id);
        $totalStaff = User::where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereNotIn('role', ['student', 'visitor'])
            ->count();

        $stats = [
            'present' => $records->whereIn('status.value', ['present', 'late'])->count(),
            'absent' => $totalStaff - $records->count(),
            'late' => $records->where('status.value', 'late')->count(),
            'on_leave' => $records->where('status.value', 'on_leave')->count(),
            'total_staff' => $totalStaff,
        ];

        $branches = $currentUser->isSuperAdmin() ? Branch::orderBy('name')->get() : collect();

        return view('livewire.attendance.attendance-dashboard', compact('records', 'stats', 'branches'));
    }
}
