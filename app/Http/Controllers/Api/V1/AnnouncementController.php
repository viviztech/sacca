<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $announcements = Announcement::where('published_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->where(fn ($q) => $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id))
            ->latest('published_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $announcements->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'body' => $a->body,
                'category' => $a->category,
                'published_at' => $a->published_at?->toISOString(),
            ]),
            'meta' => ['page' => $announcements->currentPage(), 'total' => $announcements->total()],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        return response()->json(['success' => true, 'data' => $announcement]);
    }
}
