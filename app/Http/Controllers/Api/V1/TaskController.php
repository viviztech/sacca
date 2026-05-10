<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $tasks = Task::with(['assignedBy', 'assignedTo'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->where(fn ($q) => $q->where('assigned_to_user_id', $user->id)->orWhere('assigned_by', $user->id))
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $tasks->map(fn ($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'priority' => $t->priority,
                'status' => $t->status,
                'due_date' => $t->due_date?->toDateString(),
                'is_overdue' => $t->isOverdue(),
                'assigned_by' => $t->assignedBy?->name,
                'assigned_to' => $t->assignedTo?->name,
            ]),
            'meta' => ['page' => $tasks->currentPage(), 'total' => $tasks->total()],
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:pending,in_progress,completed']);

        $task = Task::findOrFail($id);
        abort_unless($request->user()->canAccessBranch($task->branch_id), 403);

        $task->update([
            'status' => $request->status,
            'completed_at' => $request->status === 'completed' ? now() : null,
        ]);

        return response()->json(['success' => true, 'message' => 'Task status updated.']);
    }
}
