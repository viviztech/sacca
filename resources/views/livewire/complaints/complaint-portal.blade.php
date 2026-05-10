<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <select wire:model.live="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">All Status</option>
            @foreach(['open'=>'Open','in_review'=>'In Review','resolved'=>'Resolved','closed'=>'Closed'] as $val => $lbl)
                <option value="{{ $val }}">{{ $lbl }}</option>
            @endforeach
        </select>
        <button wire:click="$set('showForm', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Submit Complaint</button>
    </div>

    @if($showForm)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">Submit Complaint / Suggestion</h3>
        <form wire:submit="submit" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select wire:model="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        @foreach(['academic'=>'Academic','facility'=>'Facility','staff'=>'Staff','general'=>'General'] as $val => $lbl)
                            <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
                    <input wire:model="subject" type="text" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('subject') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('subject') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea wire:model="description" rows="4" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                    @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-2">
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input wire:model="is_anonymous" type="checkbox" class="rounded border-gray-300 text-indigo-600">
                        Submit anonymously (your name will not be shown)
                    </label>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium">Submit</button>
                <button type="button" wire:click="$set('showForm', false)" class="text-gray-500 text-sm">Cancel</button>
            </div>
        </form>
    </div>
    @endif

    <div class="space-y-4">
        @forelse($complaints as $complaint)
        @php $statusColors = ['open'=>'red','in_review'=>'yellow','resolved'=>'green','closed'=>'gray']; @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full capitalize">{{ $complaint->category }}</span>
                        <span class="bg-{{ $statusColors[$complaint->status] ?? 'gray' }}-100 text-{{ $statusColors[$complaint->status] ?? 'gray' }}-700 text-xs px-2 py-0.5 rounded-full capitalize">{{ str_replace('_', ' ', $complaint->status) }}</span>
                        @if($complaint->is_anonymous)
                            <span class="bg-purple-100 text-purple-600 text-xs px-2 py-0.5 rounded-full">Anonymous</span>
                        @endif
                    </div>
                    <h3 class="font-semibold text-gray-800">{{ $complaint->subject }}</h3>
                    <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $complaint->description }}</p>
                    @if(! $complaint->is_anonymous && $complaint->reporter)
                        <p class="text-xs text-gray-400 mt-1">By {{ $complaint->reporter->name }}</p>
                    @endif
                    @if($complaint->resolution_notes)
                        <div class="mt-2 bg-green-50 border border-green-200 rounded p-2 text-xs text-green-700">
                            <strong>Resolution:</strong> {{ $complaint->resolution_notes }}
                        </div>
                    @endif
                </div>
                @if($isResolver && $complaint->status === 'open')
                <div class="flex gap-2">
                    <button wire:click="updateStatus({{ $complaint->id }}, 'in_review')" class="text-yellow-600 hover:underline text-xs font-medium">Review</button>
                    <button wire:click="updateStatus({{ $complaint->id }}, 'resolved', 'Issue reviewed and resolved.')" class="text-green-600 hover:underline text-xs font-medium">Resolve</button>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">No complaints found.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $complaints->links() }}</div>
</div>
