<?php

namespace App\Livewire\Timetable;

use App\Models\Batch;
use App\Models\Timetable;
use App\Models\User;
use App\Modules\Academic\Services\TimetableConflictService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Timetable Builder')]
class TimetableBuilder extends Component
{
    public ?Timetable $editing = null;

    public bool $showForm = false;

    public string $batch_id = '';

    public string $faculty_id = '';

    public string $subject = '';

    public string $day_of_week = '';

    public string $start_time = '';

    public string $end_time = '';

    public string $room = '';

    public string $effective_from = '';

    public string $conflict_message = '';

    public function mount(): void
    {
        $this->effective_from = today()->toDateString();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $tt = Timetable::findOrFail($id);
        $this->editing = $tt;
        $this->batch_id = (string) $tt->batch_id;
        $this->faculty_id = (string) $tt->faculty_id;
        $this->subject = $tt->subject;
        $this->day_of_week = (string) $tt->day_of_week;
        $this->start_time = $tt->start_time;
        $this->end_time = $tt->end_time;
        $this->room = $tt->room ?? '';
        $this->effective_from = $tt->effective_from->toDateString();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'batch_id' => 'required|exists:batches,id',
            'faculty_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:100',
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'effective_from' => 'required|date',
        ]);

        $conflict = app(TimetableConflictService::class)->check(
            (int) $this->batch_id,
            (int) $this->faculty_id,
            (int) $this->day_of_week,
            $this->start_time,
            $this->end_time,
            $this->editing?->id
        );

        if ($conflict['has_conflict']) {
            $this->conflict_message = $conflict['reason'];

            return;
        }

        $data = [
            'batch_id' => $this->batch_id,
            'faculty_id' => $this->faculty_id,
            'subject' => $this->subject,
            'day_of_week' => $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'room' => $this->room ?: null,
            'effective_from' => $this->effective_from,
            'is_active' => true,
        ];

        if ($this->editing) {
            $this->editing->update($data);
            session()->flash('success', 'Timetable entry updated.');
        } else {
            Timetable::create($data);
            session()->flash('success', 'Timetable entry created.');
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Timetable::findOrFail($id)->delete();
        session()->flash('success', 'Timetable entry deleted.');
    }

    private function resetForm(): void
    {
        $this->editing = null;
        $this->showForm = false;
        $this->batch_id = $this->faculty_id = $this->subject = '';
        $this->day_of_week = $this->start_time = $this->end_time = $this->room = '';
        $this->effective_from = today()->toDateString();
        $this->conflict_message = '';
        $this->resetValidation();
    }

    public function render(): View
    {
        $user = auth()->user();

        $batches = Batch::where('is_active', true)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->with('course')
            ->orderBy('name')
            ->get();

        $faculties = User::where('is_active', true)
            ->whereIn('role', ['faculty', 'academic_coordinator'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->orderBy('name')
            ->get();

        $timetables = Timetable::with(['batch.course', 'faculty'])
            ->when(! $user->isSuperAdmin(), function ($q) use ($user) {
                $q->whereHas('batch', fn ($b) => $b->where('branch_id', $user->branch_id));
            })
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];

        return view('livewire.timetable.timetable-builder', compact('batches', 'faculties', 'timetables', 'days'));
    }
}
