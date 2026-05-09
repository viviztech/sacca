<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Modules\Attendance\Actions\CheckInAction;
use App\Modules\Attendance\Actions\CheckOutAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly CheckInAction $checkIn,
        private readonly CheckOutAction $checkOut,
    ) {}

    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['required', 'numeric', 'min:0'],
            'device_id' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->checkIn->execute(
            $request->user(),
            (float) $request->lat,
            (float) $request->lng,
            (float) $request->accuracy,
            $request->device_id,
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'code' => $result['code'],
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'gps_verified' => $result['gps_verified'],
            'warning' => $result['warning'] ?? null,
            'data' => $this->formatRecord($result['record']),
            'message' => 'Checked in successfully.',
        ]);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $result = $this->checkOut->execute(
            $request->user(),
            (float) $request->lat,
            (float) $request->lng,
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'code' => $result['code'],
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatRecord($result['record']),
            'worked_minutes' => $result['worked_minutes'],
            'message' => 'Checked out successfully.',
        ]);
    }

    public function today(Request $request): JsonResponse
    {
        $record = AttendanceRecord::where('user_id', $request->user()->id)
            ->whereDate('date', today())
            ->with('inOutLogs')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $record ? $this->formatRecord($record) : null,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'min:2024'],
        ]);

        $month = $request->integer('month', now()->month);
        $year = $request->integer('year', now()->year);

        $records = AttendanceRecord::where('user_id', $request->user()->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $records->map(fn ($r) => $this->formatRecord($r)),
            'meta' => [
                'month' => $month,
                'year' => $year,
                'present' => $records->where('status.value', 'present')->count(),
                'absent' => $records->where('status.value', 'absent')->count(),
                'late' => $records->where('status.value', 'late')->count(),
            ],
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $month = now()->month;
        $year = now()->year;

        $records = AttendanceRecord::where('user_id', $request->user()->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'present' => $records->where('status.value', 'present')->count(),
                'absent' => $records->where('status.value', 'absent')->count(),
                'late' => $records->where('status.value', 'late')->count(),
                'half_day' => $records->where('status.value', 'half_day')->count(),
                'on_leave' => $records->where('status.value', 'on_leave')->count(),
                'total_worked_minutes' => $records->sum(fn ($r) => $r->totalWorkedMinutes()),
                'total_overtime_minutes' => $records->sum('overtime_minutes'),
            ],
        ]);
    }

    private function formatRecord(AttendanceRecord $record): array
    {
        return [
            'id' => $record->id,
            'date' => $record->date?->toDateString(),
            'check_in_at' => $record->check_in_at?->toISOString(),
            'check_out_at' => $record->check_out_at?->toISOString(),
            'status' => $record->status?->value,
            'status_label' => $record->status?->label(),
            'gps_verified' => $record->gps_verified,
            'late_minutes' => $record->late_minutes,
            'overtime_minutes' => $record->overtime_minutes,
            'worked_minutes' => $record->totalWorkedMinutes(),
        ];
    }
}
