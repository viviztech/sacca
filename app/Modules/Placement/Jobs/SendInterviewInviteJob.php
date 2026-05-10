<?php

namespace App\Modules\Placement\Jobs;

use App\Models\PlacementApplication;
use App\Support\WhatsAppClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendInterviewInviteJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly PlacementApplication $application) {}

    public function handle(WhatsAppClient $whatsapp): void
    {
        $this->application->loadMissing(['student', 'drive.company']);

        $student = $this->application->student;
        if (! $student->whatsapp_number) {
            return;
        }

        $whatsapp->sendTemplate(
            $student->whatsapp_number,
            'sacca_interview_invite',
            [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $student->name],
                        ['type' => 'text', 'text' => $this->application->drive->company->name],
                        ['type' => 'text', 'text' => $this->application->drive->drive_date->format('d M Y')],
                    ],
                ],
            ]
        );
    }
}
