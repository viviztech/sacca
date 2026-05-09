<?php

namespace App\Livewire\Leave;

use App\Enums\LeaveStatus;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Modules\Leave\Actions\ApproveLeaveAction;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Leave Management')]
class LeaveIndex extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public bool $showRequestForm = false;

    public bool $showApprovalModal = false;

    public ?LeaveRequest $selectedRequest = null;

    public string $rejectionReason = '';

    public bool $isRejecting = false;

    // New request form
    #[Validate('required|exists:leave_types,id')]
    public string $leave_type_id = '';

    #[Validate('required|date')]
    public string $from_date = '';

    #[Validate('required|date|after_or_equal:from_date')]
    public string $to_date = '';

    #[Validate('required|string|max:1000')]
    public string $reason = '';

    public function submitRequest(): void
    {
        $this->validate();

        $user = auth()->user();
        $leaveType = LeaveType::findOrFail($this->leave_type_id);

        $totalDays = (int) now()->parse($this->from_date)->diffInDaysFiltered(
            fn ($date) => ! $date->isWeekend(),
            now()->parse($this->to_date)->addDay()
        );

        $balance = LeaveBalance::firstOrCreate(
            ['user_id' => $user->id, 'leave_type_id' => $leaveType->id, 'year' => now()->year],
            ['total_days' => $leaveType->max_days_per_year]
        );

        if ($balance->availableDays() < $totalDays) {
            $this->addError('leave_type_id', "Insufficient balance. Available: {$balance->availableDays()} days.");

            return;
        }

        LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
            'total_days' => $totalDays,
            'reason' => $this->reason,
            'status' => LeaveStatus::Pending,
        ]);

        $balance->increment('pending_days', $totalDays);

        $this->showRequestForm = false;
        $this->reset(['leave_type_id', 'from_date', 'to_date', 'reason']);
        session()->flash('success', 'Leave request submitted.');
    }

    public function openApproval(int $id, bool $rejecting = false): void
    {
        $this->selectedRequest = LeaveRequest::with(['user', 'leaveType'])->findOrFail($id);
        $this->isRejecting = $rejecting;
        $this->showApprovalModal = true;
    }

    public function approve(): void
    {
        abort_unless($this->selectedRequest && $this->selectedRequest->isPending(), 422);

        app(ApproveLeaveAction::class)->approve($this->selectedRequest, auth()->user());

        $this->showApprovalModal = false;
        session()->flash('success', 'Leave approved.');
    }

    public function reject(): void
    {
        $this->validate(['rejectionReason' => 'required|string|min:5']);
        abort_unless($this->selectedRequest && $this->selectedRequest->isPending(), 422);

        app(ApproveLeaveAction::class)->reject($this->selectedRequest, auth()->user(), $this->rejectionReason);

        $this->showApprovalModal = false;
        $this->rejectionReason = '';
        session()->flash('success', 'Leave rejected.');
    }

    public function render(): View
    {
        $user = auth()->user();
        $isApprover = $user->hasRole(['super_admin', 'branch_admin', 'hr_manager']);

        $query = $isApprover
            ? LeaveRequest::when(
                ! $user->isSuperAdmin(),
                fn ($q) => $q->whereHas('user', fn ($u) => $u->where('branch_id', $user->branch_id))
            )
            : LeaveRequest::where('user_id', $user->id);

        $requests = $query
            ->with(['user', 'leaveType'])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(20);

        $balances = $isApprover ? collect() : LeaveBalance::where('user_id', $user->id)
            ->where('year', now()->year)
            ->with('leaveType')
            ->get();

        return view('livewire.leave.leave-index', [
            'requests' => $requests,
            'balances' => $balances,
            'leaveTypes' => LeaveType::where('is_active', true)->get(),
            'statuses' => LeaveStatus::cases(),
            'isApprover' => $isApprover,
        ]);
    }
}
