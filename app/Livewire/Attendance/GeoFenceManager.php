<?php

namespace App\Livewire\Attendance;

use App\Models\AttendanceGeoFence;
use App\Models\Branch;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Geo-Fence Settings')]
class GeoFenceManager extends Component
{
    public ?AttendanceGeoFence $editing = null;

    public bool $showForm = false;

    #[Validate('required|exists:branches,id')]
    public string $branch_id = '';

    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required|numeric|between:-90,90')]
    public string $latitude = '';

    #[Validate('required|numeric|between:-180,180')]
    public string $longitude = '';

    #[Validate('required|integer|min:50|max:5000')]
    public string $radius_meters = '200';

    public bool $is_active = true;

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $fence = AttendanceGeoFence::findOrFail($id);
        $this->editing = $fence;
        $this->branch_id = (string) $fence->branch_id;
        $this->name = $fence->name;
        $this->latitude = (string) $fence->latitude;
        $this->longitude = (string) $fence->longitude;
        $this->radius_meters = (string) $fence->radius_meters;
        $this->is_active = $fence->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'branch_id' => $this->branch_id,
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius_meters' => $this->radius_meters,
            'is_active' => $this->is_active,
        ];

        if ($this->editing) {
            $this->editing->update($data);
            session()->flash('success', 'Geo-fence updated.');
        } else {
            AttendanceGeoFence::create($data);
            session()->flash('success', 'Geo-fence created.');
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        AttendanceGeoFence::findOrFail($id)->delete();
        session()->flash('success', 'Geo-fence deleted.');
    }

    private function resetForm(): void
    {
        $this->editing = null;
        $this->showForm = false;
        $this->branch_id = '';
        $this->name = '';
        $this->latitude = '';
        $this->longitude = '';
        $this->radius_meters = '200';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render(): View
    {
        $fences = AttendanceGeoFence::with('branch')
            ->when(
                ! auth()->user()->isSuperAdmin(),
                fn ($q) => $q->where('branch_id', auth()->user()->branch_id)
            )
            ->latest()
            ->get();

        $branches = auth()->user()->isSuperAdmin()
            ? Branch::where('is_active', true)->orderBy('name')->get()
            : Branch::where('id', auth()->user()->branch_id)->get();

        return view('livewire.attendance.geo-fence-manager', compact('fences', 'branches'));
    }
}
