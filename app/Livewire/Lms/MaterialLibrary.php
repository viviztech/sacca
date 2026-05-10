<?php

namespace App\Livewire\Lms;

use App\Enums\LmsMaterialType;
use App\Models\Batch;
use App\Models\LmsMaterial;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('LMS — Materials')]
class MaterialLibrary extends Component
{
    use WithPagination;

    public string $batchFilter = '';

    public string $typeFilter = '';

    public string $search = '';

    public function publish(int $id): void
    {
        $material = LmsMaterial::findOrFail($id);
        $material->update(['is_published' => ! $material->is_published, 'published_at' => now()]);
    }

    public function delete(int $id): void
    {
        LmsMaterial::findOrFail($id)->delete();
        session()->flash('success', 'Material deleted.');
    }

    public function render(): View
    {
        $user = auth()->user();

        $materials = LmsMaterial::with(['batch', 'course', 'faculty'])
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->batchFilter, fn ($q) => $q->where('batch_id', $this->batchFilter))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($user->isFaculty(), fn ($q) => $q->where('faculty_id', $user->id))
            ->when($user->isStudent(), fn ($q) => $q->where('is_published', true)
                ->whereHas('batch', fn ($b) => $b->whereHas('enrollments', fn ($e) => $e->where('student_id', $user->id)->where('status', 'active'))))
            ->when(! $user->isSuperAdmin() && ! $user->isStudent(), function ($q) use ($user) {
                $q->whereHas('batch', fn ($b) => $b->where('branch_id', $user->branch_id));
            })
            ->latest()
            ->paginate(20);

        $batches = Batch::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->orderBy('name')
            ->get();

        return view('livewire.lms.material-library', [
            'materials' => $materials,
            'batches' => $batches,
            'types' => LmsMaterialType::cases(),
            'canUpload' => $user->hasRole(['super_admin', 'branch_admin', 'academic_coordinator', 'faculty']),
        ]);
    }
}
