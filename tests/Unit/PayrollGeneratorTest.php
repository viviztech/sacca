<?php

namespace Tests\Unit;

use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\PayrollCycle;
use App\Models\User;
use App\Modules\Payroll\Services\PayrollGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PayrollGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private PayrollGeneratorService $service;

    private Branch $branch;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->service = new PayrollGeneratorService;
        $this->branch = Branch::create(['name' => 'Test', 'code' => 'TST', 'is_active' => true]);
        $this->admin = User::factory()->branchAdmin()->create(['branch_id' => $this->branch->id]);
    }

    public function test_payroll_generates_payslip_for_each_staff_member(): void
    {
        User::factory()->faculty()->count(3)->create(['branch_id' => $this->branch->id]);

        $cycle = PayrollCycle::create([
            'branch_id' => $this->branch->id,
            'month' => now()->month,
            'year' => now()->year,
            'generated_by' => $this->admin->id,
            'status' => 'draft',
        ]);

        // admin + 3 faculty = 4 staff (students excluded)
        $count = $this->service->generate($cycle);

        $this->assertEquals(4, $count);
        $this->assertDatabaseCount('payslips', 4);
    }

    public function test_payslip_net_salary_reflects_attendance(): void
    {
        $faculty = User::factory()->faculty()->create(['branch_id' => $this->branch->id]);

        // 10 present days out of 22 working days this month
        for ($i = 1; $i <= 10; $i++) {
            AttendanceRecord::create([
                'user_id' => $faculty->id,
                'branch_id' => $this->branch->id,
                'date' => now()->startOfMonth()->addDays($i - 1),
                'status' => 'present',
                'gps_verified' => true,
            ]);
        }

        $cycle = PayrollCycle::create([
            'branch_id' => $this->branch->id,
            'month' => now()->month,
            'year' => now()->year,
            'generated_by' => $this->admin->id,
            'status' => 'draft',
        ]);

        $this->service->generate($cycle);

        $payslip = $cycle->payslips()->where('user_id', $faculty->id)->first();
        $this->assertNotNull($payslip);
        $this->assertEquals(10, $payslip->present_days);
        $this->assertLessThan($payslip->basic_salary, $payslip->net_salary);
    }

    public function test_publish_updates_all_payslips_with_published_at(): void
    {
        User::factory()->faculty()->create(['branch_id' => $this->branch->id]);

        $cycle = PayrollCycle::create([
            'branch_id' => $this->branch->id,
            'month' => now()->month,
            'year' => now()->year,
            'generated_by' => $this->admin->id,
            'status' => 'draft',
        ]);

        $this->service->generate($cycle);
        $this->service->publish($cycle);

        $cycle->refresh();
        $this->assertEquals('published', $cycle->status);
        $this->assertDatabaseMissing('payslips', ['payroll_cycle_id' => $cycle->id, 'published_at' => null]);
    }

    public function test_month_name_returns_correct_string(): void
    {
        $cycle = new PayrollCycle(['month' => 5, 'year' => 2026]);
        $this->assertEquals('May', $cycle->monthName());
    }
}
