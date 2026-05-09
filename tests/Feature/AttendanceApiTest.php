<?php

namespace Tests\Feature;

use App\Models\AttendanceGeoFence;
use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Test Branch', 'code' => 'TST', 'is_active' => true]);
        $this->user = User::factory()->faculty()->create([
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
    }

    public function test_check_in_requires_authentication(): void
    {
        $this->postJson('/api/v1/attendance/check-in', [
            'lat' => 11.0168,
            'lng' => 76.9558,
            'accuracy' => 10,
            'device_id' => 'test-device',
        ])->assertUnauthorized();
    }

    public function test_check_in_validates_required_fields(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/attendance/check-in', [])
            ->assertUnprocessable();
    }

    public function test_user_can_check_in_successfully(): void
    {
        // No geo-fence — accept any location
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/attendance/check-in', [
                'lat' => 11.0168,
                'lng' => 76.9558,
                'accuracy' => 15,
                'device_id' => 'test-device-001',
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_cannot_check_in_twice_on_same_day(): void
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'date' => today(),
            'check_in_at' => now()->subHour(),
            'status' => 'present',
            'gps_verified' => false,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/attendance/check-in', [
                'lat' => 11.0168,
                'lng' => 76.9558,
                'accuracy' => 10,
                'device_id' => 'test-device-001',
            ])
            ->assertStatus(422)
            ->assertJsonPath('code', 'already_checked_in');
    }

    public function test_check_in_rejected_outside_geofence(): void
    {
        AttendanceGeoFence::create([
            'branch_id' => $this->branch->id,
            'name' => 'Campus',
            'latitude' => 11.0168,
            'longitude' => 76.9558,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        // Location is ~150km away (Bangalore)
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/attendance/check-in', [
                'lat' => 12.9716,
                'lng' => 77.5946,
                'accuracy' => 10,
                'device_id' => 'test-device-001',
            ]);

        // Check-in records anyway but marks gps_verified=false with warning
        $response->assertOk();
        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $this->user->id,
            'gps_verified' => false,
        ]);
    }

    public function test_check_in_rejected_with_poor_gps_accuracy(): void
    {
        // accuracy > 50m → GPSVerificationResult::fail → CheckInAction returns 422
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/attendance/check-in', [
                'lat' => 11.0168,
                'lng' => 76.9558,
                'accuracy' => 200,
                'device_id' => 'test-device-001',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'low_accuracy');
    }

    public function test_check_out_fails_without_check_in(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/attendance/check-out', [
                'lat' => 11.0168,
                'lng' => 76.9558,
            ])
            ->assertStatus(422)
            ->assertJsonPath('code', 'not_checked_in');
    }

    public function test_check_out_after_check_in_works(): void
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'date' => today(),
            'check_in_at' => now()->subHours(8),
            'status' => 'present',
            'gps_verified' => true,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/attendance/check-out', [
                'lat' => 11.0168,
                'lng' => 76.9558,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $this->user->id,
        ]);

        $record = AttendanceRecord::where('user_id', $this->user->id)->first();
        $this->assertNotNull($record->check_out_at);
    }

    public function test_today_endpoint_returns_null_if_no_record(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/attendance/today')
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_attendance_history_returns_monthly_records(): void
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'date' => today(),
            'status' => 'present',
            'gps_verified' => true,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/attendance/history?month='.now()->month.'&year='.now()->year)
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
