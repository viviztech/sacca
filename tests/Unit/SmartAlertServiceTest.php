<?php

namespace Tests\Unit;

use App\Modules\Alerts\Services\SmartAlertService;
use App\Support\WhatsAppClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SmartAlertServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_smart_alert_config_loads_correctly(): void
    {
        $rules = config('sacca_alerts.rules');
        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);
        $this->assertArrayHasKey('id', $rules[0]);
        $this->assertArrayHasKey('enabled', $rules[0]);
    }

    public function test_faculty_absent_streak_rule_exists(): void
    {
        $rules = collect(config('sacca_alerts.rules'));
        $rule = $rules->firstWhere('id', 'faculty_absent_streak');
        $this->assertNotNull($rule);
        $this->assertTrue($rule['enabled']);
        $this->assertEquals(3, $rule['threshold']);
    }

    public function test_student_low_attendance_threshold_is_75(): void
    {
        $rules = collect(config('sacca_alerts.rules'));
        $rule = $rules->firstWhere('id', 'student_low_attendance');
        $this->assertNotNull($rule);
        $this->assertEquals(75, $rule['threshold']);
    }

    public function test_smart_alert_service_runs_without_error(): void
    {
        $whatsapp = Mockery::mock(WhatsAppClient::class);
        $whatsapp->shouldReceive('sendTemplate')->andReturn(true);

        $service = new SmartAlertService($whatsapp);
        $results = $service->runAll();

        $this->assertIsArray($results);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
