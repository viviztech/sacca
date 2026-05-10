<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex justify-end mb-6">
        <button wire:click="$set('showForm', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Plan Visit</button>
    </div>

    @if($showForm)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">Plan Airport / Industrial Visit</h3>
        <form wire:submit="save" class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Visit Title *</label>
                <input wire:model="title" type="text" placeholder="e.g. IndiGo Airport Tour" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('title') ? 'border-red-400' : 'border-gray-300' }}">
                @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Visit Date *</label>
                <input wire:model="visit_date" type="date" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('visit_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Destination *</label>
                <input wire:model="destination" type="text" placeholder="e.g. Coimbatore Airport" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('destination') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Purpose *</label>
                <input wire:model="purpose" type="text" placeholder="e.g. Industrial exposure" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('purpose') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Permission Form (PDF)</label>
                <input wire:model="permission_form" type="file" accept=".pdf,.doc,.docx" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700">
            </div>
            <div class="col-span-2 flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium">Create Visit</button>
                <button type="button" wire:click="$set('showForm', false)" class="text-gray-500 text-sm">Cancel</button>
            </div>
        </form>
    </div>
    @endif

    {{-- Visit list --}}
    <div class="space-y-4">
        @forelse($visits as $visit)
        @php $statusColors = ['planned'=>'blue','completed'=>'green','cancelled'=>'red']; @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="bg-{{ $statusColors[$visit->status] ?? 'gray' }}-100 text-{{ $statusColors[$visit->status] ?? 'gray' }}-700 text-xs px-2 py-0.5 rounded-full capitalize font-medium">{{ $visit->status }}</span>
                    </div>
                    <h3 class="font-semibold text-gray-800">{{ $visit->title }}</h3>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $visit->destination }} &middot; {{ $visit->visit_date->format('d M Y') }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Coordinator: {{ $visit->coordinator->name }} &middot; {{ $visit->participants->count() }} participants</p>
                </div>
                <div class="flex flex-col gap-2">
                    <button wire:click="selectVisit({{ $visit->id }})" class="text-indigo-600 hover:underline text-xs font-medium">Manage Participants</button>
                    @if($visit->status === 'planned')
                        <button wire:click="addAllStudents({{ $visit->id }})" class="text-green-600 hover:underline text-xs font-medium">Add All Students</button>
                        <button wire:click="updateStatus({{ $visit->id }}, 'completed')" wire:confirm="Mark as completed?" class="text-gray-500 hover:underline text-xs">Mark Completed</button>
                    @endif
                </div>
            </div>

            @if($selectedVisit && $selectedVisit->id === $visit->id && $selectedVisit->participants->count() > 0)
            <div class="mt-4 border-t border-gray-100 pt-4">
                <table class="w-full text-xs">
                    <thead><tr>
                        <th class="text-left text-gray-500 pb-2">Student</th>
                        <th class="text-center text-gray-500 pb-2">Attended</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($selectedVisit->participants as $participant)
                        <tr>
                            <td class="py-1.5 text-gray-700">{{ $participant->student->name }}</td>
                            <td class="py-1.5 text-center">
                                <input type="checkbox"
                                    wire:click="markAttended({{ $visit->id }}, {{ $participant->student_id }}, {{ $participant->attended ? 'false' : 'true' }})"
                                    {{ $participant->attended ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-green-600">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">No visits planned yet.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $visits->links() }}</div>
</div>
