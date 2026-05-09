<?php

namespace App\Livewire\Admin;

use App\Models\Branch;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Branches')]
class BranchIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function deleteBranch(int $id): void
    {
        Branch::findOrFail($id)->delete();
        session()->flash('success', 'Branch deleted successfully.');
    }

    public function render(): View
    {
        $branches = Branch::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%"))
            ->with('manager')
            ->latest()
            ->paginate(15);

        return view('livewire.admin.branch-index', compact('branches'));
    }
}
