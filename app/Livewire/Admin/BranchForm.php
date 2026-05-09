<?php

namespace App\Livewire\Admin;

use App\Models\Branch;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Branch')]
class BranchForm extends Component
{
    public ?Branch $branch = null;

    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required|string|max:10')]
    public string $code = '';

    #[Validate('nullable|string|max:500')]
    public string $address = '';

    #[Validate('nullable|string|max:100')]
    public string $city = '';

    #[Validate('nullable|string|max:100')]
    public string $state = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    #[Validate('nullable|email|max:150')]
    public string $email = '';

    public bool $is_active = true;

    public function mount(?Branch $branch = null): void
    {
        if ($branch?->exists) {
            $this->branch = $branch;
            $this->name = $branch->name;
            $this->code = $branch->code;
            $this->address = $branch->address ?? '';
            $this->city = $branch->city ?? '';
            $this->state = $branch->state ?? '';
            $this->phone = $branch->phone ?? '';
            $this->email = $branch->email ?? '';
            $this->is_active = $branch->is_active;
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'address' => $this->address ?: null,
            'city' => $this->city ?: null,
            'state' => $this->state ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->branch?->exists) {
            $this->branch->update($data);
            session()->flash('success', 'Branch updated successfully.');
        } else {
            Branch::create($data);
            session()->flash('success', 'Branch created successfully.');
        }

        $this->redirect(route('admin.branches.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.branch-form');
    }
}
