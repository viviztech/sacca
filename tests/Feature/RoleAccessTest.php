<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_branches_page(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.branches.index'))
            ->assertSuccessful();
    }

    public function test_student_cannot_access_branches_page(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('admin.branches.index'))
            ->assertForbidden();
    }

    public function test_branch_admin_can_access_users_page(): void
    {
        $admin = User::factory()->branchAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertSuccessful();
    }

    public function test_faculty_cannot_access_admin_users_page(): void
    {
        $faculty = User::factory()->faculty()->create();

        $this->actingAs($faculty)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_has_role_trait_returns_correct_results(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $student = User::factory()->student()->create();

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($superAdmin->isStudent());

        $this->assertTrue($student->isStudent());
        $this->assertFalse($student->isSuperAdmin());

        $this->assertTrue($superAdmin->hasRole([UserRole::SuperAdmin, UserRole::BranchAdmin]));
        $this->assertFalse($student->hasRole(UserRole::Faculty));
    }

    public function test_super_admin_can_access_branch_regardless_of_branch_id(): void
    {
        $superAdmin = User::factory()->superAdmin()->create(['branch_id' => null]);

        $this->assertTrue($superAdmin->canAccessBranch(1));
        $this->assertTrue($superAdmin->canAccessBranch(999));
    }
}
