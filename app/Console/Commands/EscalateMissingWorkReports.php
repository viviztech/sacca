<?php

namespace App\Console\Commands;

use App\Models\DailyWorkReport;
use App\Models\User;
use App\Support\WhatsAppClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reports:escalate-missing')]
#[Description('Escalate missing EOD work reports to branch admin')]
class EscalateMissingWorkReports extends Command
{
    public function handle(WhatsAppClient $whatsapp): int
    {
        $today = today();
        $escalated = 0;

        $staff = User::whereIn('role', ['faculty', 'academic_coordinator', 'hr_manager', 'placement_officer'])
            ->where('is_active', true)
            ->get();

        foreach ($staff as $employee) {
            $report = DailyWorkReport::where('user_id', $employee->id)
                ->whereDate('report_date', $today)
                ->first();

            if (! $report || $report->status === 'pending') {
                // Mark as escalated
                DailyWorkReport::updateOrCreate(
                    ['user_id' => $employee->id, 'report_date' => $today],
                    ['status' => 'escalated', 'escalation_sent_at' => now(), 'branch_id' => $employee->branch_id,
                        'work_summary' => 'Not submitted']
                );

                $manager = User::whereIn('role', ['branch_admin', 'super_admin'])
                    ->where('branch_id', $employee->branch_id)
                    ->first();

                if ($manager?->whatsapp_number) {
                    $whatsapp->sendTemplate($manager->whatsapp_number, 'sacca_work_report_escalation', [
                        ['type' => 'body', 'parameters' => [
                            ['type' => 'text', 'text' => $employee->name],
                            ['type' => 'text', 'text' => $today->format('d M Y')],
                        ]],
                    ]);
                    $escalated++;
                }
            }
        }

        $this->info("Escalated {$escalated} missing reports.");

        return self::SUCCESS;
    }
}
