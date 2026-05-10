<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $cbe = Branch::where('code', 'CBE')->first();
        $ker = Branch::where('code', 'KER')->first();
        $coordinator = User::where('role', 'academic_coordinator')->first();

        $agsId = Course::where('code', 'AGS')->value('id');
        $cctId = Course::where('code', 'CCT')->value('id');
        $atrId = Course::where('code', 'ATR')->value('id');
        $cglId = Course::where('code', 'CGL')->value('id');

        $batches = [
            ['branch_id' => $cbe->id, 'course_id' => $agsId, 'name' => 'AGS-CBE-2026-A', 'start_date' => '2026-01-15', 'capacity' => 30],
            ['branch_id' => $cbe->id, 'course_id' => $cctId, 'name' => 'CCT-CBE-2026-A', 'start_date' => '2026-02-01', 'capacity' => 25],
            ['branch_id' => $cbe->id, 'course_id' => $atrId, 'name' => 'ATR-CBE-2026-A', 'start_date' => '2026-03-01', 'capacity' => 20],
            ['branch_id' => $ker->id, 'course_id' => $agsId, 'name' => 'AGS-KER-2026-A', 'start_date' => '2026-01-20', 'capacity' => 25],
            ['branch_id' => $ker->id, 'course_id' => $cglId, 'name' => 'CGL-KER-2026-A', 'start_date' => '2026-02-15', 'capacity' => 20],
        ];

        foreach ($batches as $batch) {
            Batch::firstOrCreate(['name' => $batch['name']], array_merge($batch, [
                'academic_coordinator_id' => $coordinator?->id,
                'is_active' => true,
            ]));
        }
    }
}
