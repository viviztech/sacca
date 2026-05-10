<?php

namespace App\Models;

use App\Enums\LmsMaterialType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['batch_id', 'course_id', 'faculty_id', 'title', 'description', 'type', 'file_path', 'external_url', 'is_published', 'published_at'])]
class LmsMaterial extends Model
{
    protected function casts(): array
    {
        return [
            'type' => LmsMaterialType::class,
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(LmsAssignment::class, 'material_id');
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(LmsQuiz::class, 'material_id');
    }
}
