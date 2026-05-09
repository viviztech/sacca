<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $leaves = LeaveRequest::where('user_id', $request->user()->id)
            ->with('leaveType')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $leaves->items(),
            'meta' => [
                'page' => $leaves->currentPage(),
                'total' => $leaves->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'from_date' => ['required', 'date', 'after_or_equal:today'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        $totalDays = now()->parse($request->from_date)->diffInDaysFiltered(
            fn ($date) => ! $date->isWeekend(),
            now()->parse($request->to_date)->addDay()
        );

        $balance = LeaveBalance::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'leave_type_id' => $leaveType->id,
                'year' => now()->year,
            ],
            ['total_days' => $leaveType->max_days_per_year]
        );

        if ($balance->availableDays() < $totalDays) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient leave balance. Available: {$balance->availableDays()} days.",
            ], 422);
        }

        $leave = LeaveRequest::create([
            'user_id' => $request->user()->id,
            'leave_type_id' => $leaveType->id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => LeaveStatus::Pending,
        ]);

        $balance->increment('pending_days', $totalDays);

        return response()->json([
            'success' => true,
            'data' => $leave->load('leaveType'),
            'message' => 'Leave request submitted successfully.',
        ], 201);
    }

    public function show(Request $request, LeaveRequest $leave): JsonResponse
    {
        abort_unless($leave->user_id === $request->user()->id, 403);

        return response()->json([
            'success' => true,
            'data' => $leave->load(['leaveType', 'approver']),
        ]);
    }

    public function cancel(Request $request, LeaveRequest $leave): JsonResponse
    {
        abort_unless($leave->user_id === $request->user()->id, 403);
        abort_unless($leave->isPending(), 422, 'Only pending leave requests can be cancelled.');

        $leave->update(['status' => LeaveStatus::Cancelled]);

        LeaveBalance::where([
            'user_id' => $leave->user_id,
            'leave_type_id' => $leave->leave_type_id,
            'year' => $leave->from_date->year,
        ])->decrement('pending_days', $leave->total_days);

        return response()->json(['success' => true, 'message' => 'Leave request cancelled.']);
    }

    public function balances(Request $request): JsonResponse
    {
        $balances = LeaveBalance::where('user_id', $request->user()->id)
            ->where('year', now()->year)
            ->with('leaveType')
            ->get()
            ->map(fn ($b) => [
                'leave_type' => $b->leaveType->name,
                'total_days' => $b->total_days,
                'used_days' => $b->used_days,
                'pending_days' => $b->pending_days,
                'available_days' => $b->availableDays(),
            ]);

        return response()->json(['success' => true, 'data' => $balances]);
    }
}
