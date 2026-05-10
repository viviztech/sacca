<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        $branchAdmin = User::where('role', 'branch_admin')->first();
        $cbe = Branch::where('code', 'CBE')->first();

        $announcements = [
            ['title' => '🎉 Republic Day Holiday — 26th January', 'body' => 'The institute will remain closed on 26th January 2026 on account of Republic Day. Classes will resume on 27th January. Wishing everyone a Happy Republic Day!', 'category' => 'notice', 'publisher' => $admin, 'branch_id' => null, 'days_ago' => 14],
            ['title' => '✈️ IndiGo Placement Drive — 25th May 2026', 'body' => 'IndiGo Airlines will be conducting a campus placement drive on 25th May 2026 for Ground Staff positions. Package: ₹3.5 LPA. Minimum 75% attendance required. Register at the placement office before 20th May.', 'category' => 'interview_drive', 'publisher' => $branchAdmin, 'branch_id' => $cbe?->id, 'days_ago' => 3],
            ['title' => '📚 New LMS Materials Uploaded', 'body' => 'New study materials for Airport Operations and Safety Procedures modules have been uploaded to the LMS. Students are advised to review them before the upcoming assessment.', 'category' => 'notice', 'publisher' => $branchAdmin, 'branch_id' => $cbe?->id, 'days_ago' => 2],
            ['title' => '🚨 Grooming Standards Reminder', 'body' => 'Students are reminded that proper uniform and grooming standards are mandatory every day. Daily inspections will be conducted from this week. Non-compliance will be recorded.', 'category' => 'circular', 'publisher' => $admin, 'branch_id' => null, 'days_ago' => 5],
            ['title' => '🏆 Annual Day Celebration — 30th May', 'body' => 'The Annual Day Celebration will be held on 30th May 2026 at the main auditorium. All students and staff are expected to attend. Cultural programmes and award ceremony will be held.', 'category' => 'event', 'publisher' => $admin, 'branch_id' => null, 'days_ago' => 1],
            ['title' => '⚠️ Water Supply Interruption — Tomorrow', 'body' => 'Due to maintenance work, water supply to the campus will be interrupted from 10 AM to 2 PM tomorrow. Please make necessary arrangements.', 'category' => 'emergency', 'publisher' => $branchAdmin, 'branch_id' => $cbe?->id, 'days_ago' => 0],
        ];

        foreach ($announcements as $a) {
            if (! $a['publisher']) {
                continue;
            }
            Announcement::firstOrCreate(
                ['title' => $a['title']],
                [
                    'branch_id' => $a['branch_id'],
                    'body' => $a['body'],
                    'category' => $a['category'],
                    'published_by' => $a['publisher']->id,
                    'published_at' => now()->subDays($a['days_ago']),
                    'audience' => null,
                ]
            );
        }
    }
}
