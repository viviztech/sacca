<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'branch_id', 'date', 'check_in_at', 'check_out_at',
    'check_in_lat', 'check_in_lng', 'check_out_lat', 'check_out_lng',
    'check_in_accuracy', 'gps_verified', 'device_id', 'status',
    'late_minutes', 'overtime_minutes', 'remarks', 'marked_by',
])]
class AttendanceRecord extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'check_in_lat' => 'float',
            'check_in_lng' => 'float',
            'check_out_lat' => 'float',
            'check_out_lng' => 'float',
            'gps_verified' => 'boolean',
            'status' => AttendanceStatus::class,
            'late_minutes' => 'integer',
            'overtime_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function inOutLogs(): HasMany
    {
        return $this->hasMany(InOutLog::class);
    }

    public function totalWorkedMinutes(): int
    {
        if (! $this->check_in_at || ! $this->check_out_at) {
            return 0;
        }

        return (int) $this->check_in_at->diffInMinutes($this->check_out_at);
    }
}
