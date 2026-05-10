<?php

namespace App\Livewire\Visitors;

use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Visitor Management')]
class VisitorCheckIn extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public string $visitor_name = '';

    public string $phone = '';

    public string $email = '';

    public string $organization = '';

    public string $purpose = '';

    public string $host_user_id = '';

    public string $badge_number = '';

    public function checkIn(): void
    {
        $this->validate([
            'visitor_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'purpose' => 'required|string|max:200',
        ]);

        VisitorLog::create([
            'branch_id' => auth()->user()->branch_id,
            'visitor_name' => $this->visitor_name,
            'phone' => $this->phone,
            'email' => $this->email ?: null,
            'organization' => $this->organization ?: null,
            'purpose' => $this->purpose,
            'host_user_id' => $this->host_user_id ?: null,
            'check_in_at' => now(),
            'badge_number' => $this->badge_number ?: null,
        ]);

        $this->reset(['visitor_name', 'phone', 'email', 'organization', 'purpose', 'host_user_id', 'badge_number']);
        $this->showForm = false;
        session()->flash('success', 'Visitor checked in successfully.');
    }

    public function checkOut(int $id): void
    {
        VisitorLog::findOrFail($id)->update(['check_out_at' => now()]);
    }

    public function render(): View
    {
        $user = auth()->user();

        $visitors = VisitorLog::with(['host'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->latest('check_in_at')
            ->paginate(20);

        $hosts = User::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->orderBy('name')->get();

        return view('livewire.visitors.visitor-check-in', compact('visitors', 'hosts'));
    }
}
