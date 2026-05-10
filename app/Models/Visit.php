<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'branch_id', 'visit_date', 'destination', 'purpose', 'coordinator_id', 'permission_form_path', 'status'])]
class Visit extends Model
{
    protected function casts(): array
    {
        return ['visit_date' => 'date'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(VisitParticipant::class);
    }
}
