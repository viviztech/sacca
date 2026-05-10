<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['application_id', 'result', 'offer_letter_path', 'joining_date', 'salary_offered'])]
class PlacementOutcome extends Model
{
    protected function casts(): array
    {
        return ['joining_date' => 'date', 'salary_offered' => 'float'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PlacementApplication::class, 'application_id');
    }
}
