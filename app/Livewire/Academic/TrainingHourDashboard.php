<?php

namespace App\Livewire\Academic;

use App\Enums\TrainingSessionType;
use App\Models\Batch;
use App\Models\TrainingHourLog;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Training Hours')]
class TrainingHourDashboard extends Component
{
    use WithPagination;

    public bool $showForm = false;

    #[Validate('required|exists:batches,id')]
    public string $batch_id = '';

    #[Validate('required|date|before_or_equal:today')]
    public string $log_date = '';

    #[Validate('required|numeric|min:0.5|max:12')]
    public string $hours_logged = '';

    #[Validate('required')]
    public string $session_type = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->log_date = today()->toDateString();
        $this->session_type = TrainingSessionType::Theory->value;
    }

    public function save(): void
    {
        $this->validate();

        TrainingHourLog::create([
            'user_id' => auth()->id(),
            'batch_id' => $this->batch_id,
            'log_date' => $this->log_date,
            'hours_logged' => $this->hours_logged,
            'session_type' => $this->session_type,
            'notes' => $this->notes ?: null,
        ]);

        $this->showForm = false;
        $this->reset(['batch_id', 'hours_logged', 'notes']);
        $this->log_date = today()->toDateString();
        session()->flash('success', 'Training hours logged.');
    }

    public function render(): View
    {
        $user = auth()->user();

        $logs = TrainingHourLog::with(['user', 'batch'])
            ->when($user->isFaculty() || $user->isStudent(), fn ($q) => $q->where('user_id', $user->id))
            ->latest('log_date')
            ->paginate(20);

        $batches = Batch::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->orderBy('name')
            ->get();

        // Summary for current user (or all if admin)
        $summary = TrainingHourLog::when(
            $user->isFaculty() || $user->isStudent(),
            fn ($q) => $q->where('user_id', $user->id)
        )
            ->selectRaw('session_type, SUM(hours_logged) as total_hours')
            ->groupBy('session_type')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->session_type->value => $row->total_hours]);

        return view('livewire.academic.training-hour-dashboard', [
            'logs' => $logs,
            'batches' => $batches,
            'sessionTypes' => TrainingSessionType::cases(),
            'summary' => $summary,
        ]);
    }
}
