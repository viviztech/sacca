<?php

namespace App\Jobs;

use App\Models\User;
use App\Support\WhatsAppClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendAttendanceAbsentAlert implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly User $user, public readonly string $date) {}

    public function handle(WhatsAppClient $whatsapp): void
    {
        if (! $user->whatsapp_number) {
            return;
        }

        $whatsapp->sendTemplate(
            $this->user->whatsapp_number,
            'sacca_attendance_absent',
            [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $this->user->name],
                        ['type' => 'text', 'text' => $this->date],
                    ],
                ],
            ]
        );
    }
}
