<?php

namespace App\Jobs;

use App\Models\StudentAttendanceAlert;
use App\Models\User;
use App\Support\WhatsAppClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendStudentAbsenteeAlert implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly User $student,
        public readonly int $batchId,
        public readonly int $consecutiveDays,
        public readonly string $alertType = 'absent_streak'
    ) {}

    public function handle(WhatsAppClient $whatsapp): void
    {
        if (! $this->student->whatsapp_number) {
            return;
        }

        $whatsapp->sendTemplate(
            $this->student->whatsapp_number,
            'sacca_student_absent_alert',
            [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $this->student->name],
                        ['type' => 'text', 'text' => (string) $this->consecutiveDays],
                    ],
                ],
            ]
        );

        StudentAttendanceAlert::create([
            'student_id' => $this->student->id,
            'batch_id' => $this->batchId,
            'alert_type' => $this->alertType,
            'threshold_value' => $this->consecutiveDays,
            'sent_at' => now(),
            'channel' => 'whatsapp',
        ]);
    }
}
