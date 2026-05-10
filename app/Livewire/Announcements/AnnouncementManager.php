<?php

namespace App\Livewire\Announcements;

use App\Enums\UserRole;
use App\Jobs\SendAnnouncementNotification;
use App\Models\Announcement;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Announcements')]
class AnnouncementManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public string $title = '';

    public string $body = '';

    public string $category = 'notice';

    public array $audience = [];

    public bool $publish_now = true;

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:200',
            'body' => 'required|string',
            'category' => 'required|in:notice,event,interview_drive,circular,emergency',
        ]);

        $user = auth()->user();
        $publishNow = $this->publish_now;

        $announcement = Announcement::create([
            'branch_id' => $user->isSuperAdmin() ? null : $user->branch_id,
            'title' => $this->title,
            'body' => $this->body,
            'category' => $this->category,
            'audience' => count($this->audience) > 0 ? $this->audience : null,
            'published_by' => $user->id,
            'published_at' => $publishNow ? now() : null,
        ]);

        if ($publishNow) {
            SendAnnouncementNotification::dispatch($announcement)->onQueue('notifications');
        }

        $this->reset(['title', 'body', 'audience']);
        $this->category = 'notice';
        $this->publish_now = true;
        $this->showForm = false;
        session()->flash('success', $publishNow ? 'Announcement published and push notifications sent.' : 'Announcement saved as draft.');
    }

    public function publish(int $id): void
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->update(['published_at' => now()]);
        SendAnnouncementNotification::dispatch($announcement)->onQueue('notifications');
        session()->flash('success', 'Announcement published and push notifications sent.');
    }

    public function delete(int $id): void
    {
        Announcement::findOrFail($id)->delete();
        session()->flash('success', 'Announcement deleted.');
    }

    public function render(): View
    {
        $user = auth()->user();

        $announcements = Announcement::with('publisher')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where(fn ($q2) => $q2->whereNull('branch_id')->orWhere('branch_id', $user->branch_id)))
            ->latest()
            ->paginate(20);

        $canManage = $user->hasRole(['super_admin', 'branch_admin', 'academic_coordinator']);
        $roles = UserRole::cases();

        return view('livewire.announcements.announcement-manager', compact('announcements', 'canManage', 'roles'));
    }
}
