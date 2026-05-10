<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Class / Subject *</label>
                <select wire:model="timetable_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Select class…</option>
                    @foreach($timetables as $tt)
                        <option value="{{ $tt->id }}">{{ $tt->subject }} — {{ $tt->batch->name }} ({{ $tt->dayName() }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Class Date *</label>
                <input wire:model="class_date" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-end">
                <button wire:click="loadClass" class="w-full bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    <span wire:loading.remove wire:target="loadClass">Load Roster</span>
                    <span wire:loading wire:target="loadClass">Loading…</span>
                </button>
            </div>
        </div>
    </div>

    @if(count($attendance) > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">{{ count($attendance) }} Students</h3>
            @if($submitted)
                <span class="bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full font-medium">✓ Submitted</span>
            @endif
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Student</th>
                    @foreach($statuses as $status)
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">{{ $status->label() }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($enrollments as $enrollment)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3">
                        <p class="font-medium text-gray-800">{{ $enrollment->student->name }}</p>
                        <p class="text-xs text-gray-400">{{ $enrollment->roll_number ?? '—' }}</p>
                    </td>
                    @foreach($statuses as $status)
                    <td class="px-4 py-3 text-center">
                        <input
                            type="radio"
                            wire:model="attendance.{{ $enrollment->id }}"
                            value="{{ $status->value }}"
                            {{ $submitted ? 'disabled' : '' }}
                            class="w-4 h-4 text-indigo-600 border-gray-300"
                        >
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
        @if(!$submitted)
        <div class="px-6 py-4 border-t border-gray-100">
            <button wire:click="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
                <span wire:loading.remove wire:target="submit">Submit Attendance</span>
                <span wire:loading wire:target="submit">Submitting…</span>
            </button>
        </div>
        @endif
    </div>
    @elseif($timetable_id)
        <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400">
            No active students enrolled in this batch.
        </div>
    @endif
</div>
