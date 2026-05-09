<?php

namespace App\Console\Commands;

use App\Enums\AttendanceStatus;
use App\Jobs\SendAttendanceAbsentAlert;
use App\Models\AttendanceRecord;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('attendance:send-alerts')]
#[Description('Dispatch WhatsApp alerts for absent staff')]
class SendAttendanceAlerts extends Command
{
    public function handle(): int
    {
        $today = Carbon::today();

        $absentRecords = AttendanceRecord::whereDate('date', $today)
            ->where('status', AttendanceStatus::Absent)
            ->with('user')
            ->get();

        $dispatched = 0;
        foreach ($absentRecords as $record) {
            if ($record->user->whatsapp_number) {
                SendAttendanceAbsentAlert::dispatch($record->user, $today->toDateString())
                    ->onQueue('notifications');
                $dispatched++;
            }
        }

        $this->info("Dispatched {$dispatched} absence alerts for {$today->toDateString()}.");

        return self::SUCCESS;
    }
}
