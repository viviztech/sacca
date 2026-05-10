<?php

namespace App\Livewire\Placement;

use App\Models\Company;
use App\Models\PlacementApplication;
use App\Models\PlacementDrive;
use App\Models\User;
use App\Modules\Placement\Jobs\SendInterviewInviteJob;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Placement Drives')]
class PlacementDriveManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?PlacementDrive $editing = null;

    public string $company_id = '';

    public string $title = '';

    public string $description = '';

    public string $drive_date = '';

    public string $venue = '';

    public string $positions = '1';

    public string $package_lpa = '';

    public string $min_attendance = '75';

    public function mount(): void
    {
        $this->drive_date = today()->addDays(7)->toDateString();
    }

    public function save(): void
    {
        $this->validate([
            'company_id' => 'required|exists:companies,id',
            'title' => 'required|string|max:200',
            'drive_date' => 'required|date',
            'positions' => 'required|integer|min:1',
        ]);

        $data = [
            'company_id' => $this->company_id,
            'title' => $this->title,
            'description' => $this->description ?: null,
            'drive_date' => $this->drive_date,
            'venue' => $this->venue ?: null,
            'positions' => (int) $this->positions,
            'package_lpa' => $this->package_lpa ?: null,
            'eligibility_criteria' => ['min_attendance' => (float) $this->min_attendance],
            'branch_id' => auth()->user()->branch_id,
            'created_by' => auth()->id(),
            'is_active' => true,
        ];

        $this->editing ? $this->editing->update($data) : PlacementDrive::create($data);

        session()->flash('success', $this->editing ? 'Drive updated.' : 'Drive created.');
        $this->resetForm();
    }

    public function shortlist(int $driveId): void
    {
        $drive = PlacementDrive::findOrFail($driveId);
        $minAttendance = $drive->eligibility_criteria['min_attendance'] ?? 75;

        $eligibleStudents = User::where('role', 'student')
            ->where('branch_id', $drive->branch_id)
            ->whereHas('enrollments', fn ($q) => $q->where('status', 'active'))
            ->get();

        $shortlisted = 0;
        foreach ($eligibleStudents as $student) {
            $existing = PlacementApplication::where('drive_id', $driveId)
                ->where('student_id', $student->id)
                ->first();

            if (! $existing) {
                $app = PlacementApplication::create([
                    'drive_id' => $driveId,
                    'student_id' => $student->id,
                    'status' => 'shortlisted',
                    'applied_at' => now(),
                    'shortlisted_at' => now(),
                ]);
                SendInterviewInviteJob::dispatch($app)->onQueue('notifications');
                $shortlisted++;
            }
        }

        session()->flash('success', "Shortlisted {$shortlisted} students. Invites dispatched.");
    }

    private function resetForm(): void
    {
        $this->editing = null;
        $this->showForm = false;
        $this->reset(['company_id', 'title', 'description', 'venue', 'package_lpa']);
        $this->positions = '1';
        $this->min_attendance = '75';
        $this->drive_date = today()->addDays(7)->toDateString();
        $this->resetValidation();
    }

    public function render(): View
    {
        $drives = PlacementDrive::with(['company', 'applications'])
            ->when(! auth()->user()->isSuperAdmin(), fn ($q) => $q->where('branch_id', auth()->user()->branch_id))
            ->latest('drive_date')
            ->paginate(15);

        return view('livewire.placement.placement-drive-manager', [
            'drives' => $drives,
            'companies' => Company::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
