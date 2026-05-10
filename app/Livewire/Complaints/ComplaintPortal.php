<?php

namespace App\Livewire\Complaints;

use App\Models\Complaint;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Complaints & Suggestions')]
class ComplaintPortal extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public string $category = 'general';

    public string $subject = '';

    public string $description = '';

    public bool $is_anonymous = false;

    public string $statusFilter = '';

    public function submit(): void
    {
        $this->validate([
            'category' => 'required|in:academic,facility,staff,general',
            'subject' => 'required|string|max:200',
            'description' => 'required|string|min:10',
        ]);

        Complaint::create([
            'reported_by' => $this->is_anonymous ? null : auth()->id(),
            'branch_id' => auth()->user()->branch_id,
            'category' => $this->category,
            'subject' => $this->subject,
            'description' => $this->description,
            'is_anonymous' => $this->is_anonymous,
            'status' => 'open',
        ]);

        $this->reset(['category', 'subject', 'description', 'is_anonymous']);
        $this->showForm = false;
        session()->flash('success', 'Your complaint/suggestion has been submitted.');
    }

    public function updateStatus(int $id, string $status, ?string $notes = null): void
    {
        $complaint = Complaint::findOrFail($id);
        $complaint->update([
            'status' => $status,
            'assigned_to' => auth()->id(),
            'resolution_notes' => $notes,
        ]);
    }

    public function render(): View
    {
        $user = auth()->user();
        $isResolver = $user->hasRole(['super_admin', 'branch_admin', 'hr_manager', 'academic_coordinator']);

        $complaints = Complaint::with(['reporter', 'assignedTo'])
            ->when($user->isStudent(), fn ($q) => $q->where(fn ($q2) => $q2->where('reported_by', $user->id)->orWhere('is_anonymous', true)
            ))
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(20);

        return view('livewire.complaints.complaint-portal', compact('complaints', 'isResolver'));
    }
}
