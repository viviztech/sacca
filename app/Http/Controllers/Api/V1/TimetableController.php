<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TimetableController extends Controller
{
    public function week(Request $request): JsonResponse
    {
        $date = $request->get('date') ? Carbon::parse($request->date) : today();
        $startOfWeek = $date->copy()->startOfWeek(Carbon::MONDAY);

        $user = $request->user();

        $timetables = Timetable::with(['batch', 'faculty'])
            ->where('is_active', true)
            ->where('effective_from', '<=', $date)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date))
            ->when($user->isFaculty(), fn ($q) => $q->where('faculty_id', $user->id))
            ->when($user->isStudent(), fn ($q) => $q->whereHas('batch', fn ($b) => $b->whereHas('enrollments', fn ($e) => $e->where('student_id', $user->id)->where('status', 'active'))))
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('branch_id', $user->branch_id)))
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $week = [];
        for ($d = 1; $d <= 6; $d++) {
            $dayDate = $startOfWeek->copy()->addDays($d - 1);
            $week[$d] = [
                'date' => $dayDate->toDateString(),
                'day' => $dayDate->format('D'),
                'classes' => $timetables->where('day_of_week', $d)->map(fn ($tt) => [
                    'id' => $tt->id,
                    'subject' => $tt->subject,
                    'faculty' => $tt->faculty->name,
                    'batch' => $tt->batch->name,
                    'start_time' => $tt->start_time,
                    'end_time' => $tt->end_time,
                    'room' => $tt->room,
                ])->values(),
            ];
        }

        return response()->json(['success' => true, 'data' => array_values($week)]);
    }
}
