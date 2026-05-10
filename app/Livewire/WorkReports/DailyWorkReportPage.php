<?php

namespace App\Livewire\WorkReports;

use App\Models\DailyWorkReport;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Daily Work Reports')]
class DailyWorkReportPage extends Component
{
    use WithPagination;

    public string $work_summary = '';

    public string $blockers = '';

    public bool $todaySubmitted = false;

    public ?DailyWorkReport $todayReport = null;

    public function mount(): void
    {
        $this->todayReport = DailyWorkReport::where('user_id', auth()->id())
            ->whereDate('report_date', today())
            ->first();

        $this->todaySubmitted = $this->todayReport?->isSubmitted() ?? false;

        if ($this->todayReport) {
            $this->work_summary = $this->todayReport->work_summary;
            $this->blockers = $this->todayReport->blockers ?? '';
        }
    }

    public function submit(): void
    {
        $this->validate([
            'work_summary' => 'required|string|min:20',
        ]);

        DailyWorkReport::updateOrCreate(
            ['user_id' => auth()->id(), 'report_date' => today()],
            [
                'branch_id' => auth()->user()->branch_id,
                'work_summary' => $this->work_summary,
                'blockers' => $this->blockers ?: null,
                'submitted_at' => now(),
                'status' => 'submitted',
            ]
        );

        $this->todaySubmitted = true;
        session()->flash('success', 'Work report submitted for today.');
    }

    public function render(): View
    {
        $user = auth()->user();
        $isManager = $user->hasRole(['super_admin', 'branch_admin', 'hr_manager']);

        $reports = DailyWorkReport::with('user')
            ->when(! $isManager, fn ($q) => $q->where('user_id', $user->id))
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->latest('report_date')
            ->paginate(20);

        return view('livewire.work-reports.daily-work-report-page', compact('reports', 'isManager'));
    }
}
