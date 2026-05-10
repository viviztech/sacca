<?php

namespace App\Livewire\Academic;

use App\Enums\StudentAttendanceStatus;
use App\Models\Enrollment;
use App\Models\StudentAttendance;
use App\Models\Timetable;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Mark Student Attendance')]
class StudentAttendanceForm extends Component
{
    public string $timetable_id = '';

    public string $class_date = '';

    public array $attendance = []; // [enrollment_id => status]

    public bool $submitted = false;

    public function mount(): void
    {
        $this->class_date = today()->toDateString();
    }

    public function loadClass(): void
    {
        $this->validate([
            'timetable_id' => 'required|exists:timetables,id',
            'class_date' => 'required|date',
        ]);

        $timetable = Timetable::findOrFail($this->timetable_id);

        // Check if already submitted
        $existingCount = StudentAttendance::where('timetable_id', $this->timetable_id)
            ->whereDate('class_date', $this->class_date)
            ->count();

        $this->submitted = $existingCount > 0;

        // Load all students in this batch
        $enrollments = Enrollment::where('batch_id', $timetable->batch_id)
            ->where('status', 'active')
            ->with('student')
            ->get();

        $this->attendance = [];
        foreach ($enrollments as $enrollment) {
            $existing = StudentAttendance::where('enrollment_id', $enrollment->id)
                ->where('timetable_id', $this->timetable_id)
                ->whereDate('class_date', $this->class_date)
                ->first();

            $this->attendance[$enrollment->id] = $existing?->status->value ?? 'present';
        }
    }

    public function submit(): void
    {
        if ($this->submitted) {
            return;
        }

        $this->validate(['timetable_id' => 'required', 'class_date' => 'required|date']);

        $markedAt = now();
        $markedBy = auth()->id();

        foreach ($this->attendance as $enrollmentId => $status) {
            StudentAttendance::updateOrCreate(
                [
                    'enrollment_id' => $enrollmentId,
                    'timetable_id' => $this->timetable_id,
                    'class_date' => $this->class_date,
                ],
                [
                    'status' => $status,
                    'marked_by' => $markedBy,
                    'marked_at' => $markedAt,
                ]
            );
        }

        $this->submitted = true;
        session()->flash('success', 'Attendance saved for '.count($this->attendance).' students.');
    }

    public function render(): View
    {
        $user = auth()->user();

        $timetables = Timetable::with(['batch', 'faculty'])
            ->where('is_active', true)
            ->when($user->isFaculty(), fn ($q) => $q->where('faculty_id', $user->id))
            ->when(! $user->isSuperAdmin(), function ($q) use ($user) {
                $q->whereHas('batch', fn ($b) => $b->where('branch_id', $user->branch_id));
            })
            ->orderBy('day_of_week')
            ->get();

        $enrollments = [];
        if ($this->timetable_id) {
            $timetable = Timetable::find($this->timetable_id);
            if ($timetable) {
                $enrollments = Enrollment::where('batch_id', $timetable->batch_id)
                    ->where('status', 'active')
                    ->with('student')
                    ->get();
            }
        }

        return view('livewire.academic.student-attendance-form', [
            'timetables' => $timetables,
            'enrollments' => $enrollments,
            'statuses' => StudentAttendanceStatus::cases(),
        ]);
    }
}
