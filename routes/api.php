<?php

use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\LeaveController;
use App\Http\Controllers\Api\V1\LmsController;
use App\Http\Controllers\Api\V1\PayslipController;
use App\Http\Controllers\Api\V1\PlacementController;
use App\Http\Controllers\Api\V1\StudentAttendanceController;
use App\Http\Controllers\Api\V1\TimetableController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/fcm-token', [AuthController::class, 'updateFcmToken']);

    // Attendance
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
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
});
