<?php

namespace App\Console\Commands;

use App\Models\DailyWorkReport;
use App\Models\User;
use App\Support\WhatsAppClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reports:send-work-reminder')]
#[Description('Send EOD work report reminder to all active staff')]
class SendWorkReportReminder extends Command
{
    public function handle(WhatsAppClient $whatsapp): int
    {
        $today = today();
        $sent = 0;

        $staff = User::whereIn('role', ['faculty', 'academic_coordinator', 'hr_manager', 'placement_officer'])
            ->where('is_active', true)
            ->get();

        foreach ($staff as $employee) {
            $submitted = DailyWorkReport::where('user_id', $employee->id)
                ->whereDate('report_date', $today)
                ->where('status', 'submitted')
                ->exists();

            if (! $submitted && $employee->whatsapp_number) {
                $whatsapp->sendTemplate($employee->whatsapp_number, 'sacca_work_report_reminder', [
                    ['type' => 'body', 'parameters' => [
                        ['type' => 'text', 'text' => $employee->name],
                        ['type' => 'text', 'text' => $today->format('d M Y')],
                    ]],
                ]);
                $sent++;
            }
        }

        $this->info("Sent {$sent} work report reminders.");

        return self::SUCCESS;
    }
}
