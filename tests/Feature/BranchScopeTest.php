<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_scope_middleware_sets_current_branch_id(): void
    {
        $branch = Branch::create([
            'name' => 'Test Branch',
            'code' => 'TST',
            'is_active' => true,
        ]);

        $user = User::factory()->branchAdmin()->create(['branch_id' => $branch->id]);

        $this->actingAs($user)->get(route('dashboard'));

        $this->assertEquals($branch->id, app('current_branch_id'));
    }

    public function test_super_admin_has_null_branch_id(): void
    {
        $admin = User::factory()->superAdmin()->create(['branch_id' => null]);

        $this->assertNull($admin->branch_id);
        $this->assertTrue($admin->isSuperAdmin());
    }

    public function test_user_can_access_own_branch(): void
    {
        $branch = Branch::create([
            'name' => 'Test Branch',
            'code' => 'TST',
            'is_active' => true,
        ]);

        $user = User::factory()->branchAdmin()->create(['branch_id' => $branch->id]);

        $this->assertTrue($user->canAccessBranch($branch->id));
    }

    public function test_user_cannot_access_another_branch(): void
    {
        $branch1 = Branch::create(['name' => 'Branch 1', 'code' => 'B1', 'is_active' => true]);
        $branch2 = Branch::create(['name' => 'Branch 2', 'code' => 'B2', 'is_active' => true]);

        $user = User::factory()->branchAdmin()->create(['branch_id' => $branch1->id]);

        $this->assertFalse($user->canAccessBranch($branch2->id));
    }
}
