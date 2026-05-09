<?php

namespace App\Support\Traits;

use App\Enums\UserRole;

trait HasRole
{
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isBranchAdmin(): bool
    {
        return $this->role === UserRole::BranchAdmin;
    }

    public function isHrManager(): bool
    {
        return $this->role === UserRole::HrManager;
    }

    public function isAcademicCoordinator(): bool
    {
        return $this->role === UserRole::AcademicCoordinator;
    }

    public function isFaculty(): bool
    {
        return $this->role === UserRole::Faculty;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function isPlacementOfficer(): bool
    {
        return $this->role === UserRole::PlacementOfficer;
    }

    public function isStaff(): bool
    {
        return $this->role->isStaff();
    }

    /**
     * @param  string|UserRole|array<string|UserRole>  $role
     */
    public function hasRole(string|UserRole|array $role): bool
    {
        $roles = collect(is_array($role) ? $role : [$role])
            ->map(fn ($r) => $r instanceof UserRole ? $r->value : $r);

        return $roles->contains($this->role->value);
    }

    public function canAccessBranch(int $branchId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->branch_id === $branchId;
    }
}
