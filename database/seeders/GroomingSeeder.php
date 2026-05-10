<?php

namespace Database\Seeders;

use App\Models\GroomingInspection;
use App\Models\User;
use Illuminate\Database\Seeder;

class GroomingSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $inspector = User::where('role', 'faculty')->first() ?? User::where('role', 'academic_coordinator')->first();
        if (! $inspector || $students->isEmpty()) {
            return;
        }

        foreach ($students as $student) {
            for ($d = 0; $d < 5; $d++) {
                $date = today()->subDays($d);
                if ($date->isWeekend()) {
                    continue;
                }
                if (GroomingInspection::where('student_id', $student->id)->whereDate('inspection_date', $date)->exists()) {
                    continue;
                }

                $uniform = (bool) rand(0, 1);
                $hair = (bool) rand(0, 1);
                $nails = (bool) rand(0, 1);
                $shoes = rand(0, 10) > 2;
                $id_card = rand(0, 10) > 1;
                $score = array_sum([$uniform, $hair, $nails, $shoes, $id_card]);

                GroomingInspection::create([
                    'student_id' => $student->id,
                    'inspected_by' => $inspector->id,
                    'inspection_date' => $date,
                    'uniform_ok' => $uniform, 'hair_ok' => $hair,
                    'nails_ok' => $nails, 'shoes_ok' => $shoes, 'id_card_ok' => $id_card,
                    'overall_score' => $score,
                    'remarks' => $score < 4 ? 'Needs improvement in grooming standards.' : null,
                ]);
            }
        }
    }
}
