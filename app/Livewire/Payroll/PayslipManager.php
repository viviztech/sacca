<?php

namespace App\Livewire\Payroll;

use App\Models\PayrollCycle;
use App\Modules\Payroll\Services\PayrollGeneratorService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Payroll')]
class PayslipManager extends Component
{
    use WithPagination;

    public string $month = '';

    public string $year = '';

    public function mount(): void
    {
        $this->month = (string) now()->month;
        $this->year = (string) now()->year;
    }

    public function generate(): void
    {
        $this->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2024',
        ]);

        $user = auth()->user();

        $cycle = PayrollCycle::firstOrCreate(
            ['branch_id' => $user->branch_id, 'month' => $this->month, 'year' => $this->year],
            ['generated_by' => $user->id, 'status' => 'draft']
        );

        if ($cycle->status === 'published') {
            session()->flash('error', 'This payroll cycle is already published.');

            return;
        }

        $count = app(PayrollGeneratorService::class)->generate($cycle);
        session()->flash('success', "Generated {$count} payslips for {$cycle->monthName()} {$cycle->year}. PDFs are being generated in the background.");
    }

    public function publish(int $cycleId): void
    {
        $cycle = PayrollCycle::findOrFail($cycleId);
        abort_unless(auth()->user()->canAccessBranch($cycle->branch_id), 403);

        app(PayrollGeneratorService::class)->publish($cycle);
        session()->flash('success', 'Payslips published. Staff will receive WhatsApp notifications.');
    }

    public function render(): View
    {
        $user = auth()->user();

        $cycles = PayrollCycle::with(['branch', 'payslips'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->latest()
            ->paginate(10);

        return view('livewire.payroll.payslip-manager', compact('cycles'));
    }
}
