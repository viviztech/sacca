<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['name' => 'Airport Ground Staff', 'code' => 'AGS', 'duration_hours' => 480],
            ['name' => 'Cabin Crew Training', 'code' => 'CCT', 'duration_hours' => 360],
            ['name' => 'Air Ticketing & Reservation', 'code' => 'ATR', 'duration_hours' => 240],
            ['name' => 'Aviation Security', 'code' => 'AVS', 'duration_hours' => 300],
            ['name' => 'Cargo & Logistics', 'code' => 'CGL', 'duration_hours' => 200],
        ];

        foreach ($courses as $course) {
            Course::firstOrCreate(['code' => $course['code']], array_merge($course, ['is_active' => true, 'description' => 'Professional aviation training course.']));
        }
    }
}
