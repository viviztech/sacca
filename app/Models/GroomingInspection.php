<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'inspected_by', 'inspection_date', 'uniform_ok', 'hair_ok', 'nails_ok', 'shoes_ok', 'id_card_ok', 'overall_score', 'remarks'])]
class GroomingInspection extends Model
{
    protected function casts(): array
    {
        return [
            'inspection_date' => 'date',
            'uniform_ok' => 'boolean',
            'hair_ok' => 'boolean',
            'nails_ok' => 'boolean',
            'shoes_ok' => 'boolean',
            'id_card_ok' => 'boolean',
            'overall_score' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function calculateScore(): int
    {
        return collect(['uniform_ok', 'hair_ok', 'nails_ok', 'shoes_ok', 'id_card_ok'])
            ->filter(fn ($field) => $this->{$field})
            ->count();
    }
}
