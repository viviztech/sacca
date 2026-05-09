<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('User')]
class UserForm extends Component
{
    public ?User $user = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $role = '';

    public string $branch_id = '';

    public string $employee_id = '';

    public string $whatsapp_number = '';

    public bool $is_active = true;

    public string $password = '';

    public function mount(?User $user = null): void
    {
        if ($user?->exists) {
            $this->user = $user;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->role = $user->role->value;
            $this->branch_id = (string) ($user->branch_id ?? '');
            $this->employee_id = $user->employee_id ?? '';
            $this->whatsapp_number = $user->whatsapp_number ?? '';
            $this->is_active = $user->is_active;
        }
    }

    protected function rules(): array
    {
        $emailRule = $this->user?->exists
            ? Rule::unique('users', 'email')->ignore($this->user->id)
            : Rule::unique('users', 'email');

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', $emailRule],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'employee_id' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
            'password' => $this->user?->exists ? ['nullable', 'min:8'] : ['required', 'min:8'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'role' => $this->role,
            'branch_id' => $this->branch_id ?: null,
            'employee_id' => $this->employee_id ?: null,
            'whatsapp_number' => $this->whatsapp_number ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->user?->exists) {
            $this->user->update($data);
            session()->flash('success', 'User updated successfully.');
        } else {
            User::create($data);
            session()->flash('success', 'User created successfully.');
        }

        $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.user-form', [
            'branches' => Branch::orderBy('name')->get(),
            'roles' => UserRole::cases(),
        ]);
    }
}
