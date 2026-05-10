<?php

namespace App\Livewire\Grooming;

use App\Models\GroomingInspection;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Grooming Inspection')]
class GroomingInspectionForm extends Component
{
    use WithPagination;

    public string $student_id = '';

    public bool $uniform_ok = false;

    public bool $hair_ok = false;

    public bool $nails_ok = false;

    public bool $shoes_ok = false;

    public bool $id_card_ok = false;

    public string $remarks = '';

    public string $inspection_date = '';

    public function mount(): void
    {
        $this->inspection_date = today()->toDateString();
    }

    public function save(): void
    {
        $this->validate([
            'student_id' => 'required|exists:users,id',
            'inspection_date' => 'required|date',
        ]);

        $score = collect(['uniform_ok', 'hair_ok', 'nails_ok', 'shoes_ok', 'id_card_ok'])
            ->filter(fn ($f) => $this->{$f})
            ->count();

        GroomingInspection::create([
            'student_id' => $this->student_id,
            'inspected_by' => auth()->id(),
            'inspection_date' => $this->inspection_date,
            'uniform_ok' => $this->uniform_ok,
            'hair_ok' => $this->hair_ok,
            'nails_ok' => $this->nails_ok,
            'shoes_ok' => $this->shoes_ok,
            'id_card_ok' => $this->id_card_ok,
            'overall_score' => $score,
            'remarks' => $this->remarks ?: null,
        ]);

        $this->reset(['student_id', 'uniform_ok', 'hair_ok', 'nails_ok', 'shoes_ok', 'id_card_ok', 'remarks']);
        session()->flash('success', 'Grooming inspection saved.');
    }

    public function render(): View
    {
        $user = auth()->user();

        $students = User::where('role', 'student')
            ->where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->orderBy('name')->get();

        $records = GroomingInspection::with(['student', 'inspector'])
            ->when(! $user->isSuperAdmin(), function ($q) use ($user) {
                $q->whereHas('student', fn ($s) => $s->where('branch_id', $user->branch_id));
            })
            ->latest('inspection_date')
            ->paginate(20);

        return view('livewire.grooming.grooming-inspection-form', compact('students', 'records'));
    }
}
