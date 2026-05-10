<?php

namespace App\Console\Commands;

use App\Models\LeaveBalance;
use App\Models\User;
use App\Support\WhatsAppClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('leaves:send-balance-summary')]
#[Description('Send weekly leave balance summary to all active staff via WhatsApp')]
class SendLeaveBalanceSummary extends Command
{
    public function handle(WhatsAppClient $whatsapp): int
    {
        $sent = 0;

        $staff = User::whereNotIn('role', ['student', 'visitor'])
            ->where('is_active', true)
            ->whereNotNull('whatsapp_number')
            ->get();

        foreach ($staff as $employee) {
            $balances = LeaveBalance::where('user_id', $employee->id)
                ->where('year', now()->year)
                ->with('leaveType')
                ->get();

            if ($balances->isEmpty()) {
                continue;
            }

            $summary = $balances->map(fn ($b) => $b->leaveType->code.': '.$b->availableDays().'d')
                ->join(', ');

            $whatsapp->sendTemplate(
                $employee->whatsapp_number,
                'sacca_leave_balance_summary',
                [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $employee->name],
                            ['type' => 'text', 'text' => $summary],
                        ],
                    ],
                ]
            );

            $sent++;
        }

        $this->info("Sent leave balance summary to {$sent} staff members.");

        return self::SUCCESS;
    }
}
