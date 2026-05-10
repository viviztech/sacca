<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementApiTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private User $user;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch = Branch::create(['name' => 'Test', 'code' => 'TST', 'is_active' => true]);
        $this->user = User::factory()->student()->create(['branch_id' => $this->branch->id, 'is_active' => true]);
        $this->admin = User::factory()->branchAdmin()->create(['branch_id' => $this->branch->id]);
    }

    public function test_announcements_api_requires_auth(): void
    {
        $this->getJson('/api/v1/announcements')->assertUnauthorized();
    }

    public function test_published_announcements_are_returned(): void
    {
        Announcement::create([
            'title' => 'Holiday Notice',
            'body' => 'Institute closed on Monday.',
            'category' => 'notice',
            'published_by' => $this->admin->id,
            'published_at' => now()->subHour(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/announcements')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_draft_announcements_not_returned(): void
    {
        Announcement::create([
            'title' => 'Draft Notice',
            'body' => 'Not published yet.',
            'category' => 'notice',
            'published_by' => $this->admin->id,
            'published_at' => null,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/announcements')
            ->assertOk();

        $this->assertCount(0, $response->json('data'));
    }

    public function test_expired_announcements_not_returned(): void
    {
        Announcement::create([
            'title' => 'Old Event',
            'body' => 'This event already passed.',
            'category' => 'event',
            'published_by' => $this->admin->id,
            'published_at' => now()->subDays(5),
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/announcements')
            ->assertOk();

        $this->assertCount(0, $response->json('data'));
    }

    public function test_announcement_is_active_logic(): void
    {
        $active = new Announcement(['published_at' => now()->subHour(), 'expires_at' => null]);
        $this->assertTrue($active->isActive());

        $draft = new Announcement(['published_at' => null]);
        $this->assertFalse($draft->isActive());

        $expired = new Announcement(['published_at' => now()->subDays(2), 'expires_at' => now()->subHour()]);
        $this->assertFalse($expired->isActive());
    }
}
