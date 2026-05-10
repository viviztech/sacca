<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        $faculty = User::where('role', 'faculty')->first();
        $coordinator = User::where('role', 'academic_coordinator')->first();
        $batches = Batch::where('is_active', true)->limit(3)->get();

        $subjects = ['Air Navigation', 'Airport Operations', 'Customer Service', 'Safety Procedures', 'Cargo Handling'];
        $rooms = ['Room 101', 'Room 102', 'Lab A', 'Simulator Bay', 'Hall B'];

        foreach ($batches as $batch) {
            for ($day = 1; $day <= 5; $day++) {
                $slots = [['09:00', '10:30'], ['10:45', '12:15'], ['13:30', '15:00']];
                foreach ($slots as $i => [$start, $end]) {
                    Timetable::firstOrCreate(
                        ['batch_id' => $batch->id, 'day_of_week' => $day, 'start_time' => $start],
                        [
                            'faculty_id' => ($i % 2 === 0 ? $faculty : $coordinator)?->id ?? $faculty?->id,
                            'subject' => $subjects[array_rand($subjects)],
                            'end_time' => $end,
                            'room' => $rooms[array_rand($rooms)],
                            'effective_from' => '2026-01-01',
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}
