<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['attendance_record_id', 'user_id', 'event_type', 'event_at', 'lat', 'lng', 'reason'])]
class InOutLog extends Model
{
    protected $table = 'inout_logs';

    protected function casts(): array
    {
        return [
            'event_at' => 'datetime',
            'lat' => 'float',
            'lng' => 'float',
        ];
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
