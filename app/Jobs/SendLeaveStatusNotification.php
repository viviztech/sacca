<?php

namespace App\Jobs;

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Support\WhatsAppClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendLeaveStatusNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly LeaveRequest $leaveRequest) {}

    public function handle(WhatsAppClient $whatsapp): void
    {
        $this->leaveRequest->loadMissing(['user', 'leaveType']);
        $user = $this->leaveRequest->user;

        if (! $user->whatsapp_number) {
            return;
        }

        $templateName = match ($this->leaveRequest->status) {
            LeaveStatus::Approved => 'sacca_leave_approved',
            LeaveStatus::Rejected => 'sacca_leave_rejected',
            default => null,
        };

        if (! $templateName) {
            return;
        }

        $dates = $this->leaveRequest->from_date->format('d M').' – '.$this->leaveRequest->to_date->format('d M Y');

        $whatsapp->sendTemplate(
            $user->whatsapp_number,
            $templateName,
            [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $user->name],
                        ['type' => 'text', 'text' => $dates],
                        ['type' => 'text', 'text' => $this->leaveRequest->leaveType->name],
                    ],
                ],
            ]
        );
    }
}
