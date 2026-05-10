<?php

return [
    'rules' => [
        [
            'id' => 'faculty_absent_streak',
            'description' => 'Faculty absent for 3+ consecutive days',
            'threshold' => 3,
            'notify_role' => 'hr_manager',
            'enabled' => true,
        ],
        [
            'id' => 'work_report_missing',
            'description' => 'EOD work report not submitted by 7 PM',
            'notify_role' => 'branch_admin',
            'enabled' => true,
        ],
        [
            'id' => 'student_low_attendance',
            'description' => 'Student attendance below 75%',
            'threshold' => 75,
            'notify_role' => 'academic_coordinator',
            'enabled' => true,
        ],
        [
            'id' => 'placement_eligible_nudge',
            'description' => 'Eligible student has not applied after drive posted',
            'days_after_drive' => 3,
            'enabled' => true,
        ],
    ],
];
