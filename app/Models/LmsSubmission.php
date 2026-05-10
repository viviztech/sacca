<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['assignment_id', 'student_id', 'file_path', 'notes', 'submitted_at', 'marks_obtained', 'feedback', 'graded_at', 'graded_by', 'status'])]
class LmsSubmission extends Model
{
    protected function casts(): array
    {
        return ['submitted_at' => 'datetime', 'graded_at' => 'datetime', 'marks_obtained' => 'integer'];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(LmsAssignment::class, 'assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
