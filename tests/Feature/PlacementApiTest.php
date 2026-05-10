<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\PlacementApplication;
use App\Models\PlacementDrive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlacementApiTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private Branch $branch;

    private PlacementDrive $drive;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Test', 'code' => 'TST', 'is_active' => true]);
        $this->student = User::factory()->student()->create(['branch_id' => $this->branch->id, 'is_active' => true]);
        $creator = User::factory()->branchAdmin()->create(['branch_id' => $this->branch->id]);

        $company = Company::create(['name' => 'IndiGo Airlines', 'is_active' => true]);
        $this->drive = PlacementDrive::create([
            'company_id' => $company->id,
            'title' => 'Cabin Crew Recruitment',
            'drive_date' => today()->addDays(10),
            'branch_id' => $this->branch->id,
            'created_by' => $creator->id,
            'positions' => 5,
            'is_active' => true,
        ]);
    }

    public function test_drives_list_requires_auth(): void
    {
        $this->getJson('/api/v1/placement/drives')->assertUnauthorized();
    }

    public function test_student_can_list_placement_drives(): void
    {
        $this->actingAs($this->student, 'sanctum')
            ->getJson('/api/v1/placement/drives')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_student_can_apply_for_drive(): void
    {
        $this->actingAs($this->student, 'sanctum')
            ->postJson("/api/v1/placement/drives/{$this->drive->id}/apply")
            ->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('placement_applications', [
            'drive_id' => $this->drive->id,
            'student_id' => $this->student->id,
            'status' => 'applied',
        ]);
    }

    public function test_student_cannot_apply_twice(): void
    {
        PlacementApplication::create([
            'drive_id' => $this->drive->id,
            'student_id' => $this->student->id,
            'status' => 'applied',
            'applied_at' => now(),
        ]);

        $this->actingAs($this->student, 'sanctum')
            ->postJson("/api/v1/placement/drives/{$this->drive->id}/apply")
            ->assertStatus(422);
    }

    public function test_my_applications_returns_student_applications(): void
    {
        PlacementApplication::create([
            'drive_id' => $this->drive->id,
            'student_id' => $this->student->id,
            'status' => 'applied',
            'applied_at' => now(),
        ]);

        $this->actingAs($this->student, 'sanctum')
            ->getJson('/api/v1/placement/my-applications')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
