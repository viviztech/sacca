<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['timetable_id', 'override_date', 'substitute_faculty_id', 'reason', 'status'])]
class TimetableOverride extends Model
{
    protected function casts(): array
    {
        return ['override_date' => 'date'];
    }

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function substituteFaculty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'substitute_faculty_id');
    }
}
