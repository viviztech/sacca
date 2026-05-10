<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['drive_id', 'student_id', 'status', 'applied_at', 'shortlisted_at'])]
class PlacementApplication extends Model
{
    protected function casts(): array
    {
        return ['applied_at' => 'datetime', 'shortlisted_at' => 'datetime'];
    }

    public function drive(): BelongsTo
    {
        return $this->belongsTo(PlacementDrive::class, 'drive_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function outcome(): HasOne
    {
        return $this->hasOne(PlacementOutcome::class, 'application_id');
    }

    public function isShortlisted(): bool
    {
        return in_array($this->status, ['shortlisted', 'interviewed', 'selected']);
    }
}
