<?php

use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\LeaveController;
use App\Http\Controllers\Api\V1\LmsController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PayslipController;
use App\Http\Controllers\Api\V1\PlacementController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\StudentAttendanceController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TimetableController;
use App\Http\Controllers\Api\V1\WorkReportController;
use Illuminate\Support\Facades\Route;

// Auth — rate limited separately
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/fcm-token', [AuthController::class, 'updateFcmToken']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);

    // Attendance — strict rate limit on check-in/check-out (max 5 per minute)
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
    });
    Route::get('/attendance/today', [AttendanceController::class, 'today']);
    Route::get('/attendance/history', [AttendanceController::class, 'history']);
    Route::get('/attendance/summary', [AttendanceController::class, 'summary']);

    // Leave
    Route::get('/leaves', [LeaveController::class, 'index']);
    Route::post('/leaves', [LeaveController::class, 'store']);
    Route::get('/leaves/balances', [LeaveController::class, 'balances']);
    Route::get('/leaves/{leave}', [LeaveController::class, 'show']);
    Route::patch('/leaves/{leave}/cancel', [LeaveController::class, 'cancel']);

    // Timetable
    Route::get('/timetable/week', [TimetableController::class, 'week']);

    // Student Attendance (faculty)
    Route::get('/student-attendance/class/{timetableId}', [StudentAttendanceController::class, 'classRoster']);
    Route::post('/student-attendance/class/{timetableId}', [StudentAttendanceController::class, 'bulkMark']);

    // LMS
    Route::get('/lms/materials', [LmsController::class, 'materials']);
    Route::get('/lms/materials/{id}', [LmsController::class, 'show']);
    Route::post('/lms/assignments/{assignmentId}/submit', [LmsController::class, 'submitAssignment']);
    Route::get('/lms/quizzes/{quizId}/start', [LmsController::class, 'startQuiz']);
    Route::post('/lms/quizzes/{quizId}/submit', [LmsController::class, 'submitQuiz']);

    // Placement
    Route::get('/placement/drives', [PlacementController::class, 'drives']);
    Route::post('/placement/drives/{driveId}/apply', [PlacementController::class, 'apply']);
    Route::get('/placement/my-applications', [PlacementController::class, 'myApplications']);

    // Payslips
    Route::get('/payslips', [PayslipController::class, 'index']);
    Route::get('/payslips/{id}/download', [PayslipController::class, 'download']);

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus']);

    // Announcements
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::get('/announcements/{id}', [AnnouncementController::class, 'show']);

    // Daily Work Reports
    Route::get('/work-reports', [WorkReportController::class, 'show']);
    Route::post('/work-reports', [WorkReportController::class, 'store']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
});
