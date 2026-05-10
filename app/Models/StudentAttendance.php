<?php

namespace App\Models;

use App\Enums\StudentAttendanceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['enrollment_id', 'timetable_id', 'class_date', 'status', 'marked_by', 'marked_at', 'remarks'])]
class StudentAttendance extends Model
{
    protected $table = 'student_attendance';

    protected function casts(): array
    {
        return [
            'class_date' => 'date',
            'marked_at' => 'datetime',
            'status' => StudentAttendanceStatus::class,
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function markedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
