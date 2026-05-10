<?php

namespace App\Livewire\Placement;

use App\Models\Company;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Companies')]
class CompanyIndex extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?Company $editing = null;

    #[Validate('required|string|max:200')]
    public string $name = '';

    #[Validate('nullable|string|max:100')]
    public string $industry = '';

    #[Validate('nullable|string|max:100')]
    public string $contact_person = '';

    #[Validate('nullable|email|max:150')]
    public string $contact_email = '';

    #[Validate('nullable|string|max:20')]
    public string $contact_phone = '';

    #[Validate('nullable|url|max:255')]
    public string $website = '';

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'industry' => $this->industry ?: null,
            'contact_person' => $this->contact_person ?: null,
            'contact_email' => $this->contact_email ?: null,
            'contact_phone' => $this->contact_phone ?: null,
            'website' => $this->website ?: null,
            'is_active' => true,
        ];

        $this->editing ? $this->editing->update($data) : Company::create($data);

        session()->flash('success', $this->editing ? 'Company updated.' : 'Company added.');
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $company = Company::findOrFail($id);
        $this->editing = $company;
        $this->name = $company->name;
        $this->industry = $company->industry ?? '';
        $this->contact_person = $company->contact_person ?? '';
        $this->contact_email = $company->contact_email ?? '';
        $this->contact_phone = $company->contact_phone ?? '';
        $this->website = $company->website ?? '';
        $this->showForm = true;
    }

    private function resetForm(): void
    {
        $this->editing = null;
        $this->showForm = false;
        $this->reset(['name', 'industry', 'contact_person', 'contact_email', 'contact_phone', 'website']);
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.placement.company-index', [
            'companies' => Company::orderBy('name')->paginate(20),
        ]);
    }
}
