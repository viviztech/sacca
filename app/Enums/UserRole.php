<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case BranchAdmin = 'branch_admin';
    case HrManager = 'hr_manager';
    case AcademicCoordinator = 'academic_coordinator';
    case Faculty = 'faculty';
    case Student = 'student';
    case PlacementOfficer = 'placement_officer';
    case Visitor = 'visitor';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::BranchAdmin => 'Branch Admin',
            self::HrManager => 'HR Manager',
            self::AcademicCoordinator => 'Academic Coordinator',
            self::Faculty => 'Faculty',
            self::Student => 'Student',
            self::PlacementOfficer => 'Placement Officer',
            self::Visitor => 'Visitor',
        };
    }

    public function isStaff(): bool
    {
        return in_array($this, [
            self::SuperAdmin,
            self::BranchAdmin,
            self::HrManager,
            self::AcademicCoordinator,
            self::Faculty,
            self::PlacementOfficer,
        ]);
    }
}
