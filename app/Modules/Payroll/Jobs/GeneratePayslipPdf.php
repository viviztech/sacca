<?php

namespace App\Modules\Payroll\Jobs;

use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GeneratePayslipPdf implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public readonly Payslip $payslip) {}

    public function handle(): void
    {
        $this->payslip->loadMissing(['user', 'payrollCycle.branch']);

        $pdf = Pdf::loadView('pdf.payslip', ['payslip' => $this->payslip]);

        $path = "payslips/{$this->payslip->payrollCycle->year}/{$this->payslip->payrollCycle->month}/{$this->payslip->user_id}.pdf";

        Storage::disk('public')->put($path, $pdf->output());

        $this->payslip->update(['pdf_path' => $path]);
    }
}
