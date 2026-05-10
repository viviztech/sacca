<?php

namespace App\Modules\Academic\Services;

use App\Models\Timetable;

class TimetableConflictService
{
    /**
     * Check if the given slot conflicts with existing timetable entries.
     * Conflicts: same faculty on same day+time, or same batch on same day+time.
     *
     * @return array{has_conflict: bool, reason: string}
     */
    public function check(
        int $batchId,
        int $facultyId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $excludeId = null
    ): array {
        $query = Timetable::where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($inner) use ($startTime, $endTime) {
                    $inner->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $facultyConflict = (clone $query)->where('faculty_id', $facultyId)->first();
        if ($facultyConflict) {
            return [
                'has_conflict' => true,
                'reason' => "Faculty is already scheduled for {$facultyConflict->subject} ({$facultyConflict->start_time}–{$facultyConflict->end_time}) on this day.",
            ];
        }

        $batchConflict = (clone $query)->where('batch_id', $batchId)->first();
        if ($batchConflict) {
            return [
                'has_conflict' => true,
                'reason' => "Batch already has {$batchConflict->subject} ({$batchConflict->start_time}–{$batchConflict->end_time}) at this time.",
            ];
        }

        return ['has_conflict' => false, 'reason' => ''];
    }
}
