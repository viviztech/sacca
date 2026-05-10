<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex justify-end mb-6">
        <button wire:click="create" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Add Class</button>
    </div>

    @if($showForm)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">{{ $editing ? 'Edit Class Slot' : 'New Class Slot' }}</h3>

        @if($conflict_message)
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                ⚠ Conflict: {{ $conflict_message }}
            </div>
        @endif

        <form wire:submit="save" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Batch *</label>
                <select wire:model="batch_id" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('batch_id') ? 'border-red-400' : 'border-gray-300' }}">
                    <option value="">Select batch…</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->name }} ({{ $batch->course->code }})</option>
                    @endforeach
                </select>
                @error('batch_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Faculty *</label>
                <select wire:model="faculty_id" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('faculty_id') ? 'border-red-400' : 'border-gray-300' }}">
                    <option value="">Select faculty…</option>
                    @foreach($faculties as $f)
                        <option value="{{ $f->id }}">{{ $f->name }}</option>
                    @endforeach
                </select>
                @error('faculty_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
                <input wire:model="subject" type="text" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('subject') ? 'border-red-400' : 'border-gray-300' }}">
                @error('subject') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Day *</label>
                <select wire:model="day_of_week" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('day_of_week') ? 'border-red-400' : 'border-gray-300' }}">
                    <option value="">Select day…</option>
                    @foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday'] as $d => $name)
                        <option value="{{ $d }}">{{ $name }}</option>
                    @endforeach
                </select>
                @error('day_of_week') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Time *</label>
                <input wire:model="start_time" type="time" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('start_time') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Time *</label>
                <input wire:model="end_time" type="time" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('end_time') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Room</label>
                <input wire:model="room" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Effective From *</label>
                <input wire:model="effective_from" type="date" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('effective_from') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2 flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                    <span wire:loading.remove>Save</span><span wire:loading>Saving…</span>
                </button>
                <button type="button" wire:click="$set('showForm', false)" class="text-gray-500 text-sm">Cancel</button>
            </div>
        </form>
    </div>
    @endif

    {{-- Weekly timetable view --}}
    @foreach($days as $dayNum => $dayName)
        @if(isset($timetables[$dayNum]) && $timetables[$dayNum]->count())
        <div class="mb-4">
            <h4 class="text-sm font-semibold text-gray-600 mb-2">{{ $dayName }}</h4>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Time</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Subject</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Batch</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Faculty</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Room</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($timetables[$dayNum] as $tt)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-mono text-xs text-gray-600">{{ $tt->start_time }} – {{ $tt->end_time }}</td>
                            <td class="px-4 py-2 font-medium text-gray-800">{{ $tt->subject }}</td>
                            <td class="px-4 py-2 text-gray-600 text-xs">{{ $tt->batch->name }}</td>
                            <td class="px-4 py-2 text-gray-600 text-xs">{{ $tt->faculty->name }}</td>
                            <td class="px-4 py-2 text-gray-400 text-xs">{{ $tt->room ?? '—' }}</td>
                            <td class="px-4 py-2 flex gap-2">
                                <button wire:click="edit({{ $tt->id }})" class="text-indigo-600 hover:underline text-xs">Edit</button>
                                <button wire:click="delete({{ $tt->id }})" wire:confirm="Delete this class slot?" class="text-red-500 hover:underline text-xs">Delete</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    @endforeach

    @if($timetables->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">
            No timetable entries yet. Click "Add Class" to get started.
        </div>
    @endif
</div>
