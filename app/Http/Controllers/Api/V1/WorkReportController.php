<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailyWorkReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkReportController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $date = $request->get('date', today()->toDateString());
        $report = DailyWorkReport::where('user_id', $request->user()->id)
            ->whereDate('report_date', $date)
            ->first();

        return response()->json(['success' => true, 'data' => $report]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'work_summary' => ['required', 'string', 'min:20'],
            'blockers' => ['nullable', 'string'],
        ]);

        $report = DailyWorkReport::updateOrCreate(
            ['user_id' => $request->user()->id, 'report_date' => today()],
            [
                'branch_id' => $request->user()->branch_id,
                'work_summary' => $request->work_summary,
                'blockers' => $request->blockers,
                'submitted_at' => now(),
                'status' => 'submitted',
            ]
        );

        return response()->json(['success' => true, 'data' => $report, 'message' => 'Report submitted.'], 201);
    }
}
