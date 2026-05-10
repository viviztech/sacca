<?php

namespace App\Livewire\Compliance;

use App\Models\Branch;
use App\Models\ComplianceReport;
use App\Modules\Compliance\Services\ComplianceReportGenerator;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Compliance Reports')]
class ComplianceManager extends Component
{
    public string $report_type = 'TAHDCO';

    public string $branch_id = '';

    public string $period_from = '';

    public string $period_to = '';

    public bool $generating = false;

    public function mount(): void
    {
        $this->period_from = now()->startOfMonth()->toDateString();
        $this->period_to = now()->endOfMonth()->toDateString();
        $this->branch_id = (string) (auth()->user()->branch_id ?? '');
    }

    public function generate(): void
    {
        $this->validate([
            'report_type' => 'required|in:TAHDCO,SCDD,monthly_summary,annual',
            'branch_id' => 'required|exists:branches,id',
            'period_from' => 'required|date',
            'period_to' => 'required|date|after_or_equal:period_from',
        ]);

        $branch = Branch::findOrFail($this->branch_id);

        app(ComplianceReportGenerator::class)->generate(
            $branch,
            $this->report_type,
            Carbon::parse($this->period_from),
            Carbon::parse($this->period_to)
        );

        session()->flash('success', "{$this->report_type} report generated successfully.");
    }

    public function render(): View
    {
        $user = auth()->user();
        $branches = Branch::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('id', $user->branch_id))
            ->get();

        $reports = ComplianceReport::with(['branch', 'generatedBy'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->latest('generated_at')
            ->paginate(15);

        return view('livewire.compliance.compliance-manager', compact('branches', 'reports'));
    }
}
