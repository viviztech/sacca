<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\User;
use App\Support\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendAnnouncementNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly Announcement $announcement) {}

    public function handle(FcmService $fcm): void
    {
        $this->announcement->loadMissing('branch');

        // Build audience query
        $query = User::where('is_active', true)->whereNotNull('fcm_token');

        // Scope to branch if not global
        if ($this->announcement->branch_id) {
            $query->where('branch_id', $this->announcement->branch_id);
        }

        // Filter by audience roles if specified
        if (! empty($this->announcement->audience)) {
            $query->whereIn('role', $this->announcement->audience);
        }

        $tokens = $query->pluck('fcm_token')->filter()->values()->toArray();

        if (empty($tokens)) {
            return;
        }

        $fcm->sendToMany(
            $tokens,
            $this->announcement->title,
            strip_tags($this->announcement->body),
            [
                'type' => 'announcement',
                'id' => (string) $this->announcement->id,
                'category' => $this->announcement->category,
            ]
        );
    }
}
