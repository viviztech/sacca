<?php

namespace Tests\Unit;

use App\Modules\Attendance\Services\GPSVerificationService;
use PHPUnit\Framework\TestCase;

class GPSVerificationServiceTest extends TestCase
{
    private GPSVerificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GPSVerificationService;
    }

    public function test_haversine_distance_between_same_points_is_zero(): void
    {
        $distance = $this->service->haversineDistance(11.0168, 76.9558, 11.0168, 76.9558);

        $this->assertEquals(0.0, $distance);
    }

    public function test_haversine_distance_between_coimbatore_and_kochi(): void
    {
        // Coimbatore: 11.0168, 76.9558 — Kochi: 9.9312, 76.2673 (~150 km apart)
        $distance = $this->service->haversineDistance(11.0168, 76.9558, 9.9312, 76.2673);

        $this->assertGreaterThan(140000, $distance);
        $this->assertLessThan(160000, $distance);
    }

    public function test_distance_within_100_meters_is_accurate(): void
    {
        // ~80 meters north of a point
        $distance = $this->service->haversineDistance(11.0168, 76.9558, 11.0175, 76.9558);

        $this->assertGreaterThan(50, $distance);
        $this->assertLessThan(100, $distance);
    }

    public function test_distance_is_symmetric(): void
    {
        $d1 = $this->service->haversineDistance(11.0168, 76.9558, 12.9716, 77.5946);
        $d2 = $this->service->haversineDistance(12.9716, 77.5946, 11.0168, 76.9558);

        $this->assertEquals($d1, $d2);
    }
}
