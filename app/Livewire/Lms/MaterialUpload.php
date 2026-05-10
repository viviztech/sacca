<?php

namespace App\Livewire\Lms;

use App\Enums\LmsMaterialType;
use App\Models\Batch;
use App\Models\Course;
use App\Models\LmsAssignment;
use App\Models\LmsMaterial;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Upload Material')]
class MaterialUpload extends Component
{
    use WithFileUploads;

    #[Validate('required|exists:batches,id')]
    public string $batch_id = '';

    #[Validate('required|exists:courses,id')]
    public string $course_id = '';

    #[Validate('required|string|max:200')]
    public string $title = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('required')]
    public string $type = '';

    #[Validate('nullable|url|max:500')]
    public string $external_url = '';

    public $file = null;

    // Assignment extras
    public string $due_date = '';

    public string $max_marks = '100';

    public string $instructions = '';

    public bool $publish_now = false;

    public function save(): void
    {
        $this->validate();

        $filePath = null;
        if ($this->file) {
            $filePath = $this->file->store('lms', 'public');
        }

        $material = LmsMaterial::create([
            'batch_id' => $this->batch_id,
            'course_id' => $this->course_id,
            'faculty_id' => auth()->id(),
            'title' => $this->title,
            'description' => $this->description ?: null,
            'type' => $this->type,
            'file_path' => $filePath,
            'external_url' => $this->external_url ?: null,
            'is_published' => $this->publish_now,
            'published_at' => $this->publish_now ? now() : null,
        ]);

        if ($this->type === LmsMaterialType::Assignment->value && $this->due_date) {
            LmsAssignment::create([
                'material_id' => $material->id,
                'due_date' => $this->due_date,
                'max_marks' => (int) $this->max_marks,
                'instructions' => $this->instructions ?: null,
            ]);
        }

        session()->flash('success', 'Material uploaded successfully.');
        $this->redirect(route('lms.index'), navigate: true);
    }

    public function render(): View
    {
        $user = auth()->user();
        $batches = Batch::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->orderBy('name')->get();
        $courses = Course::where('is_active', true)->orderBy('name')->get();

        return view('livewire.lms.material-upload', [
            'batches' => $batches,
            'courses' => $courses,
            'types' => LmsMaterialType::cases(),
        ]);
    }
}
