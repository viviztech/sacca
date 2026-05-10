<?php

namespace App\Livewire\Visits;

use App\Models\User;
use App\Models\Visit;
use App\Models\VisitParticipant;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Visit Planner')]
class VisitPlanner extends Component
{
    use WithFileUploads, WithPagination;

    public bool $showForm = false;

    public ?Visit $selectedVisit = null;

    public string $title = '';

    public string $visit_date = '';

    public string $destination = '';

    public string $purpose = '';

    public $permission_form = null;

    public function mount(): void
    {
        $this->visit_date = today()->addDays(7)->toDateString();
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:200',
            'visit_date' => 'required|date|after_or_equal:today',
            'destination' => 'required|string|max:200',
            'purpose' => 'required|string|max:200',
        ]);

        $path = null;
        if ($this->permission_form) {
            $path = $this->permission_form->store('visits/permissions', 'public');
        }

        Visit::create([
            'title' => $this->title,
            'branch_id' => auth()->user()->branch_id,
            'visit_date' => $this->visit_date,
            'destination' => $this->destination,
            'purpose' => $this->purpose,
            'coordinator_id' => auth()->id(),
            'permission_form_path' => $path,
            'status' => 'planned',
        ]);

        $this->reset(['title', 'destination', 'purpose', 'permission_form']);
        $this->visit_date = today()->addDays(7)->toDateString();
        $this->showForm = false;
        session()->flash('success', 'Visit planned successfully.');
    }

    public function markAttended(int $visitId, int $studentId, bool $attended): void
    {
        VisitParticipant::where('visit_id', $visitId)
            ->where('student_id', $studentId)
            ->update(['attended' => $attended]);
    }

    public function updateStatus(int $id, string $status): void
    {
        Visit::findOrFail($id)->update(['status' => $status]);
    }

    public function selectVisit(int $id): void
    {
        $this->selectedVisit = Visit::with('participants.student')->findOrFail($id);
    }

    public function addAllStudents(int $visitId): void
    {
        $visit = Visit::findOrFail($visitId);
        $studentIds = User::where('branch_id', $visit->branch_id)
            ->where('role', 'student')
            ->where('is_active', true)
            ->pluck('id');

        foreach ($studentIds as $studentId) {
            VisitParticipant::firstOrCreate([
                'visit_id' => $visitId,
                'student_id' => $studentId,
            ]);
        }

        $this->selectedVisit = $visit->fresh(['participants.student']);
        session()->flash('success', count($studentIds).' students added.');
    }

    public function render(): View
    {
        $user = auth()->user();

        $visits = Visit::with(['coordinator', 'participants'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->latest('visit_date')
            ->paginate(15);

        return view('livewire.visits.visit-planner', compact('visits'));
    }
}
