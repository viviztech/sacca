<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Casual Leave', 'code' => 'CL', 'max_days_per_year' => 12, 'carry_forward' => false, 'requires_document' => false],
            ['name' => 'Sick Leave', 'code' => 'SL', 'max_days_per_year' => 10, 'carry_forward' => false, 'requires_document' => true],
            ['name' => 'Duty Leave', 'code' => 'DL', 'max_days_per_year' => 5, 'carry_forward' => false, 'requires_document' => true],
            ['name' => 'Emergency Leave', 'code' => 'EL', 'max_days_per_year' => 3, 'carry_forward' => false, 'requires_document' => false],
            ['name' => 'Maternity Leave', 'code' => 'ML', 'max_days_per_year' => 90, 'carry_forward' => false, 'requires_document' => true],
        ];

        foreach ($types as $type) {
            LeaveType::firstOrCreate(['code' => $type['code']], array_merge($type, ['is_active' => true]));
        }
    }
}
