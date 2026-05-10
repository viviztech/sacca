<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $cbe = Branch::where('code', 'CBE')->first();
        $student = User::where('role', 'student')->first();
        $faculty = User::where('role', 'faculty')->first();
        $hr = User::where('role', 'hr_manager')->first();

        if (! $cbe) {
            return;
        }

        $complaints = [
            ['reporter' => $student, 'category' => 'facility', 'subject' => 'Air conditioning not working in Room 101', 'description' => 'The air conditioning unit in Room 101 has been non-functional for the past 3 days. It is extremely uncomfortable for students during afternoon sessions.', 'status' => 'open', 'anonymous' => false],
            ['reporter' => null, 'category' => 'academic', 'subject' => 'Course material not updated', 'description' => 'The LMS study materials for the Aviation Security module are outdated. The content does not reflect current BCAS regulations.', 'status' => 'in_review', 'anonymous' => true, 'assigned' => $hr],
            ['reporter' => $faculty, 'category' => 'general', 'subject' => 'Parking space insufficient for staff', 'description' => 'The parking area near Block B is consistently full by 9 AM. Additional spaces should be allocated for teaching staff.', 'status' => 'resolved', 'anonymous' => false, 'resolution' => 'Additional 10 parking slots have been allocated at Block C entrance effective immediately.'],
            ['reporter' => $student, 'category' => 'staff', 'subject' => 'Feedback on assessment format', 'description' => 'The practical assessment format for the Ground Handling module needs revision. Written tests alone do not assess hands-on skills adequately.', 'status' => 'open', 'anonymous' => false],
        ];

        foreach ($complaints as $c) {
            Complaint::firstOrCreate(
                ['subject' => $c['subject'], 'branch_id' => $cbe->id],
                [
                    'reported_by' => $c['reporter']?->id,
                    'category' => $c['category'],
                    'description' => $c['description'],
                    'is_anonymous' => $c['anonymous'],
                    'status' => $c['status'],
                    'assigned_to' => $c['assigned']?->id ?? null,
                    'resolution_notes' => $c['resolution'] ?? null,
                ]
            );
        }
    }
}
