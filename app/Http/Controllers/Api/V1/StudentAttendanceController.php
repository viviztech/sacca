<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\StudentAttendance;
use App\Models\Timetable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentAttendanceController extends Controller
{
    public function classRoster(Request $request, int $timetableId): JsonResponse
    {
        $timetable = Timetable::findOrFail($timetableId);
        $date = $request->get('date', today()->toDateString());

        $enrollments = Enrollment::where('batch_id', $timetable->batch_id)
            ->where('status', 'active')
            ->with('student')
            ->get();

        $existing = StudentAttendance::where('timetable_id', $timetableId)
            ->whereDate('class_date', $date)
            ->get()
            ->keyBy('enrollment_id');

        return response()->json([
            'success' => true,
            'data' => [
                'timetable' => [
                    'id' => $timetable->id,
                    'subject' => $timetable->subject,
                    'batch' => $timetable->batch->name,
                ],
                'date' => $date,
                'submitted' => $existing->isNotEmpty(),
                'students' => $enrollments->map(fn ($e) => [
                    'enrollment_id' => $e->id,
                    'student_id' => $e->student_id,
                    'name' => $e->student->name,
                    'roll_number' => $e->roll_number,
                    'status' => $existing->get($e->id)?->status->value ?? 'present',
                ]),
            ],
        ]);
    }

    public function bulkMark(Request $request, int $timetableId): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
            'attendance' => ['required', 'array'],
            'attendance.*.enrollment_id' => ['required', 'exists:enrollments,id'],
            'attendance.*.status' => ['required', 'in:present,absent,late,excused'],
        ]);

        $markedAt = now();
        $markedBy = $request->user()->id;

        foreach ($request->attendance as $row) {
            StudentAttendance::updateOrCreate(
                [
                    'enrollment_id' => $row['enrollment_id'],
                    'timetable_id' => $timetableId,
                    'class_date' => $request->date,
                ],
                [
                    'status' => $row['status'],
                    'marked_by' => $markedBy,
                    'marked_at' => $markedAt,
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Attendance marked for '.count($request->attendance).' students.']);
    }
}
