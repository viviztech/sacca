<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('role', 'student')->first();
        if (! $student) {
            return;
        }

        $cbeBatches = Batch::whereHas('branch', fn ($q) => $q->where('code', 'CBE'))->limit(2)->get();

        foreach ($cbeBatches as $i => $batch) {
            Enrollment::firstOrCreate(
                ['student_id' => $student->id, 'batch_id' => $batch->id],
                ['enrollment_date' => $batch->start_date, 'status' => 'active', 'roll_number' => 'CBE-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT)]
            );
        }

        // Create 5 more demo students and enroll them
        $branch = $student->branch;
        $batch = $cbeBatches->first();
        if (! $batch) {
            return;
        }

        $names = ['Priya Sharma', 'Arjun Patel', 'Meera Nair', 'Karthik Raj', 'Divya Menon'];
        foreach ($names as $j => $name) {
            $email = strtolower(str_replace(' ', '.', $name)).'@student.sacca.in';
            $s = User::firstOrCreate(['email' => $email], [
                'name' => $name, 'password' => bcrypt('Student@1234'),
                'role' => 'student', 'branch_id' => $branch->id, 'is_active' => true,
                'phone' => '9'.str_pad(rand(100000000, 999999999), 9, '0'),
            ]);
            Enrollment::firstOrCreate(
                ['student_id' => $s->id, 'batch_id' => $batch->id],
                ['enrollment_date' => $batch->start_date, 'status' => 'active', 'roll_number' => 'CBE-'.str_pad($j + 10, 3, '0', STR_PAD_LEFT)]
            );
        }
    }
}
