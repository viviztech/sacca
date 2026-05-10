<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\GroomingInspection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroomingInspectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_grooming_score_calculates_correctly(): void
    {
        $inspection = new GroomingInspection([
            'uniform_ok' => true,
            'hair_ok' => true,
            'nails_ok' => false,
            'shoes_ok' => true,
            'id_card_ok' => false,
        ]);

        $this->assertEquals(3, $inspection->calculateScore());
    }

    public function test_perfect_grooming_score_is_five(): void
    {
        $inspection = new GroomingInspection([
            'uniform_ok' => true,
            'hair_ok' => true,
            'nails_ok' => true,
            'shoes_ok' => true,
            'id_card_ok' => true,
        ]);

        $this->assertEquals(5, $inspection->calculateScore());
    }

    public function test_zero_grooming_score(): void
    {
        $inspection = new GroomingInspection([
            'uniform_ok' => false,
            'hair_ok' => false,
            'nails_ok' => false,
            'shoes_ok' => false,
            'id_card_ok' => false,
        ]);

        $this->assertEquals(0, $inspection->calculateScore());
    }

    public function test_inspection_persisted_to_database(): void
    {
        $branch = Branch::create(['name' => 'Test', 'code' => 'T1', 'is_active' => true]);
        $student = User::factory()->student()->create(['branch_id' => $branch->id]);
        $faculty = User::factory()->faculty()->create(['branch_id' => $branch->id]);

        GroomingInspection::create([
            'student_id' => $student->id,
            'inspected_by' => $faculty->id,
            'inspection_date' => today(),
            'uniform_ok' => true,
            'hair_ok' => false,
            'nails_ok' => true,
            'shoes_ok' => true,
            'id_card_ok' => true,
            'overall_score' => 4,
        ]);

        $this->assertDatabaseHas('grooming_inspections', [
            'student_id' => $student->id,
            'overall_score' => 4,
        ]);
    }
}
