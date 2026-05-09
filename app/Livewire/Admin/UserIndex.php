<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Users')]
class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public string $branchFilter = '';

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);
    }

    public function render(): View
    {
        $currentUser = auth()->user();

        $users = User::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('employee_id', 'like', "%{$this->search}%"))
            ->when($this->roleFilter, fn ($q) => $q->where('role', $this->roleFilter))
            ->when($this->branchFilter, fn ($q) => $q->where('branch_id', $this->branchFilter))
            ->when(! $currentUser->isSuperAdmin(), fn ($q) => $q->where('branch_id', $currentUser->branch_id))
            ->with('branch')
            ->latest()
            ->paginate(20);

        $branches = $currentUser->isSuperAdmin()
            ? Branch::orderBy('name')->get()
            : collect();

        return view('livewire.admin.user-index', [
            'users' => $users,
            'branches' => $branches,
            'roles' => UserRole::cases(),
        ]);
    }
}
