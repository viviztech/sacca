<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PlacementApplication;
use App\Models\PlacementDrive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlacementController extends Controller
{
    public function drives(Request $request): JsonResponse
    {
        $drives = PlacementDrive::with('company')
            ->where('is_active', true)
            ->where('drive_date', '>=', today())
            ->when(! $request->user()->isSuperAdmin(), fn ($q) => $q->where('branch_id', $request->user()->branch_id))
            ->orderBy('drive_date')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $drives->map(fn ($d) => [
                'id' => $d->id,
                'title' => $d->title,
                'company' => $d->company->name,
                'drive_date' => $d->drive_date->toDateString(),
                'venue' => $d->venue,
                'positions' => $d->positions,
                'package_lpa' => $d->package_lpa,
                'description' => $d->description,
            ]),
            'meta' => ['page' => $drives->currentPage(), 'total' => $drives->total()],
        ]);
    }

    public function apply(Request $request, int $driveId): JsonResponse
    {
        $drive = PlacementDrive::findOrFail($driveId);
        $studentId = $request->user()->id;

        $existing = PlacementApplication::where('drive_id', $driveId)->where('student_id', $studentId)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Already applied.'], 422);
        }

        PlacementApplication::create([
            'drive_id' => $driveId,
            'student_id' => $studentId,
            'status' => 'applied',
            'applied_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Application submitted successfully.'], 201);
    }

    public function myApplications(Request $request): JsonResponse
    {
        $applications = PlacementApplication::with(['drive.company'])
            ->where('student_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $applications->map(fn ($a) => [
                'id' => $a->id,
                'drive' => $a->drive->title,
                'company' => $a->drive->company->name,
                'drive_date' => $a->drive->drive_date->toDateString(),
                'status' => $a->status,
                'applied_at' => $a->applied_at->toISOString(),
            ]),
        ]);
    }
}
