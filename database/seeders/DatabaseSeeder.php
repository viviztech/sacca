<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            // Foundation
            BranchSeeder::class,
            UserSeeder::class,
            LeaveTypeSeeder::class,
            // Academic structure
            CourseSeeder::class,
            BatchSeeder::class,
            EnrollmentSeeder::class,
            TimetableSeeder::class,
            // HR
            AttendanceSeeder::class,
            LeaveRequestSeeder::class,
            WorkReportSeeder::class,
            // Academic content
            LmsSeeder::class,
            // Placement
            PlacementSeeder::class,
            // Operations
            TaskSeeder::class,
            AnnouncementSeeder::class,
            ComplaintSeeder::class,
            VisitorSeeder::class,
            GroomingSeeder::class,
        ]);
    }
}
