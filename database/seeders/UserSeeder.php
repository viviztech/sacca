<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $cbe = Branch::where('code', 'CBE')->first();
        $ker = Branch::where('code', 'KER')->first();

        // Super Admin
        User::firstOrCreate(['email' => 'admin@sacca.in'], [
            'name' => 'Super Admin',
            'email' => 'admin@sacca.in',
            'password' => Hash::make('Admin@1234'),
            'role' => UserRole::SuperAdmin,
            'branch_id' => null,
            'employee_id' => 'SA001',
            'is_active' => true,
        ]);

        // Branch Admin — Coimbatore
        User::firstOrCreate(['email' => 'cbe.admin@sacca.in'], [
            'name' => 'CBE Branch Admin',
            'email' => 'cbe.admin@sacca.in',
            'password' => Hash::make('Admin@1234'),
            'role' => UserRole::BranchAdmin,
            'branch_id' => $cbe?->id,
            'employee_id' => 'BA001',
            'is_active' => true,
        ]);

        // HR Manager
        User::firstOrCreate(['email' => 'hr@sacca.in'], [
            'name' => 'HR Manager',
            'email' => 'hr@sacca.in',
            'password' => Hash::make('Admin@1234'),
            'role' => UserRole::HrManager,
            'branch_id' => $cbe?->id,
            'employee_id' => 'HR001',
            'is_active' => true,
        ]);

        // Academic Coordinator
        User::firstOrCreate(['email' => 'academic@sacca.in'], [
            'name' => 'Academic Coordinator',
            'email' => 'academic@sacca.in',
            'password' => Hash::make('Admin@1234'),
            'role' => UserRole::AcademicCoordinator,
            'branch_id' => $cbe?->id,
            'employee_id' => 'AC001',
            'is_active' => true,
        ]);

        // Faculty
        User::firstOrCreate(['email' => 'faculty@sacca.in'], [
            'name' => 'Demo Faculty',
            'email' => 'faculty@sacca.in',
            'password' => Hash::make('Admin@1234'),
            'role' => UserRole::Faculty,
            'branch_id' => $cbe?->id,
            'employee_id' => 'FAC001',
            'is_active' => true,
        ]);

        // Student
        User::firstOrCreate(['email' => 'student@sacca.in'], [
            'name' => 'Demo Student',
            'email' => 'student@sacca.in',
            'password' => Hash::make('Admin@1234'),
            'role' => UserRole::Student,
            'branch_id' => $cbe?->id,
            'is_active' => true,
        ]);

        // Placement Officer
        User::firstOrCreate(['email' => 'placement@sacca.in'], [
            'name' => 'Placement Officer',
            'email' => 'placement@sacca.in',
            'password' => Hash::make('Admin@1234'),
            'role' => UserRole::PlacementOfficer,
            'branch_id' => $cbe?->id,
            'employee_id' => 'PO001',
            'is_active' => true,
        ]);

        // Kerala Branch Admin
        User::firstOrCreate(['email' => 'ker.admin@sacca.in'], [
            'name' => 'Kerala Branch Admin',
            'email' => 'ker.admin@sacca.in',
            'password' => Hash::make('Admin@1234'),
            'role' => UserRole::BranchAdmin,
            'branch_id' => $ker?->id,
            'employee_id' => 'BA002',
            'is_active' => true,
        ]);
    }
}
