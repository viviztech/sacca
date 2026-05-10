<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Task Board')]
class TaskBoard extends Component
{
    public bool $showForm = false;

    public string $title = '';

    public string $description = '';

    public string $assigned_to_user_id = '';

    public string $priority = 'medium';

    public string $due_date = '';

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:200',
            'assigned_to_user_id' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high,critical',
            'due_date' => 'nullable|date',
        ]);

        Task::create([
            'title' => $this->title,
            'description' => $this->description ?: null,
            'assigned_by' => auth()->id(),
            'assigned_to_user_id' => $this->assigned_to_user_id,
            'branch_id' => auth()->user()->branch_id,
            'priority' => $this->priority,
            'due_date' => $this->due_date ?: null,
            'status' => 'pending',
        ]);

        $this->reset(['title', 'description', 'assigned_to_user_id', 'due_date']);
        $this->showForm = false;
        session()->flash('success', 'Task created.');
    }

    public function updateStatus(int $id, string $status): void
    {
        $task = Task::findOrFail($id);
        abort_unless(auth()->user()->canAccessBranch($task->branch_id), 403);

        $task->update([
            'status' => $status,
            'completed_at' => $status === 'completed' ? now() : null,
        ]);
    }

    public function render(): View
    {
        $user = auth()->user();

        $query = Task::with(['assignedTo', 'assignedBy'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->when($user->isFaculty() || $user->isStudent(), fn ($q) => $q->where(fn ($q2) => $q2->where('assigned_to_user_id', $user->id)->orWhere('assigned_by', $user->id)));

        $tasks = [
            'pending' => (clone $query)->where('status', 'pending')->latest()->get(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->latest()->get(),
            'completed' => (clone $query)->where('status', 'completed')->latest()->limit(20)->get(),
        ];

        $assignableUsers = User::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->orderBy('name')->get();

        return view('livewire.tasks.task-board', compact('tasks', 'assignableUsers'));
    }
}
