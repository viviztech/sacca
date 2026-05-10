<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Document Vault')]
class DocumentVault extends Component
{
    use WithFileUploads, WithPagination;

    public bool $showForm = false;

    public string $owner_id = '';

    public string $category = '';

    public string $title = '';

    public string $expires_at = '';

    public $file = null;

    public function save(): void
    {
        $this->validate([
            'owner_id' => 'required|exists:users,id',
            'category' => 'required|string',
            'title' => 'required|string|max:200',
            'file' => 'required|file|max:10240',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $path = $this->file->store('documents', 'public');

        Document::create([
            'owner_id' => $this->owner_id,
            'category' => $this->category,
            'title' => $this->title,
            'file_path' => $path,
            'file_size' => $this->file->getSize(),
            'mime_type' => $this->file->getMimeType(),
            'uploaded_by' => auth()->id(),
            'expires_at' => $this->expires_at ?: null,
        ]);

        $this->showForm = false;
        $this->reset(['owner_id', 'category', 'title', 'expires_at', 'file']);
        session()->flash('success', 'Document uploaded.');
    }

    public function delete(int $id): void
    {
        Document::findOrFail($id)->delete();
        session()->flash('success', 'Document deleted.');
    }

    public function render(): View
    {
        $user = auth()->user();

        $documents = Document::with(['owner', 'uploader'])
            ->when($user->isStudent(), fn ($q) => $q->where('owner_id', $user->id))
            ->when(! $user->isSuperAdmin() && ! $user->isStudent(), function ($q) use ($user) {
                $q->whereHas('owner', fn ($u) => $u->where('branch_id', $user->branch_id));
            })
            ->latest()
            ->paginate(20);

        $categories = ['certificate', 'offer_letter', 'id_proof', 'passport', 'visa', 'student_record', 'other'];

        $staffAndStudents = $user->isStudent()
            ? collect()
            : User::where('is_active', true)
                ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
                ->orderBy('name')->get();

        return view('livewire.documents.document-vault', compact('documents', 'categories', 'staffAndStudents'));
    }
}
