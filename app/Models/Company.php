<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'industry', 'contact_person', 'contact_email', 'contact_phone', 'address', 'website', 'is_active'])]
class Company extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function placementDrives(): HasMany
    {
        return $this->hasMany(PlacementDrive::class);
    }
}
