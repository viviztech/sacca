<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $cbe = Branch::where('code', 'CBE')->first();
        $ker = Branch::where('code', 'KER')->first();
        $admin = User::where('role', 'branch_admin')->first();
        $faculty = User::where('role', 'faculty')->first();
        $coordinator = User::where('role', 'academic_coordinator')->first();

        $tasks = [
            ['title' => 'Prepare Q2 student progress report', 'assigned_to' => $coordinator, 'priority' => 'high', 'status' => 'pending', 'due' => now()->addDays(3), 'branch' => $cbe],
            ['title' => 'Update LMS with new aviation materials', 'assigned_to' => $faculty, 'priority' => 'medium', 'status' => 'in_progress', 'due' => now()->addDays(5), 'branch' => $cbe],
            ['title' => 'Coordinate IndiGo placement drive logistics', 'assigned_to' => $coordinator, 'priority' => 'critical', 'status' => 'pending', 'due' => now()->addDays(14), 'branch' => $cbe],
            ['title' => 'Submit monthly attendance report to management', 'assigned_to' => $faculty, 'priority' => 'high', 'status' => 'pending', 'due' => now()->subDays(2), 'branch' => $cbe],
            ['title' => 'Organize industrial visit permission forms', 'assigned_to' => $coordinator, 'priority' => 'medium', 'status' => 'completed', 'due' => now()->subDays(5), 'branch' => $cbe],
            ['title' => 'Review and update curriculum for CCT batch', 'assigned_to' => $faculty, 'priority' => 'medium', 'status' => 'in_progress', 'due' => now()->addDays(7), 'branch' => $cbe],
            ['title' => 'Kerala campus batch onboarding', 'assigned_to' => $coordinator, 'priority' => 'high', 'status' => 'pending', 'due' => now()->addDays(2), 'branch' => $ker],
        ];

        foreach ($tasks as $t) {
            if (! $t['assigned_to'] || ! $t['branch'] || ! $admin) {
                continue;
            }
            Task::firstOrCreate(
                ['title' => $t['title'], 'branch_id' => $t['branch']->id],
                [
                    'assigned_by' => $admin->id,
                    'assigned_to_user_id' => $t['assigned_to']->id,
                    'priority' => $t['priority'],
                    'status' => $t['status'],
                    'due_date' => $t['due'],
                    'completed_at' => $t['status'] === 'completed' ? now()->subDays(1) : null,
                ]
            );
        }
    }
}
