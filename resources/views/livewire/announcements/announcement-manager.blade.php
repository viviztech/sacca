<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    @if($canManage)
    <div class="flex justify-end mb-6">
        <button wire:click="$set('showForm', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">+ New Announcement</button>
    </div>
    @endif

    @if($showForm)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">New Announcement</h3>
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input wire:model="title" type="text" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('title') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select wire:model="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        @foreach(['notice'=>'Notice','event'=>'Event','interview_drive'=>'Interview Drive','circular'=>'Circular','emergency'=>'Emergency'] as $val => $lbl)
                            <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end pb-2">
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input wire:model="publish_now" type="checkbox" class="rounded border-gray-300 text-indigo-600" checked>
                        Publish immediately
                    </label>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience (leave empty = all)</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($roles as $role)
                        <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer">
                            <input type="checkbox" wire:model="audience" value="{{ $role->value }}" class="rounded border-gray-300 text-indigo-600">
                            {{ $role->label() }}
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Body *</label>
                    <textarea wire:model="body" rows="5" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('body') ? 'border-red-400' : 'border-gray-300' }}"></textarea>
                    @error('body') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                    <span wire:loading.remove>{{ $publish_now ? 'Publish' : 'Save Draft' }}</span>
                    <span wire:loading>Saving…</span>
                </button>
                <button type="button" wire:click="$set('showForm', false)" class="text-gray-500 text-sm">Cancel</button>
            </div>
        </form>
    </div>
    @endif

    <div class="space-y-4">
        @forelse($announcements as $announcement)
        @php
            $catColors = ['notice'=>'gray','event'=>'indigo','interview_drive'=>'green','circular'=>'blue','emergency'=>'red'];
            $color = $catColors[$announcement->category] ?? 'gray';
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 {{ $announcement->category === 'emergency' ? 'border-l-4 border-l-red-500' : '' }}">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="bg-{{ $color }}-100 text-{{ $color }}-700 text-xs px-2 py-0.5 rounded-full capitalize font-medium">{{ str_replace('_', ' ', $announcement->category) }}</span>
                        @if(! $announcement->published_at)
                            <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full">Draft</span>
                        @endif
                        @if($announcement->branch_id === null)
                            <span class="bg-purple-100 text-purple-700 text-xs px-2 py-0.5 rounded-full">All Branches</span>
                        @endif
                    </div>
                    <h3 class="font-semibold text-gray-800">{{ $announcement->title }}</h3>
                    <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $announcement->body }}</p>
                    <p class="text-xs text-gray-400 mt-2">By {{ $announcement->publisher->name }} &middot; {{ $announcement->published_at?->format('d M Y h:i A') ?? 'Draft' }}</p>
                </div>
                @if($canManage)
                <div class="flex gap-2 flex-shrink-0">
                    @if(! $announcement->published_at)
                        <button wire:click="publish({{ $announcement->id }})" class="text-green-600 hover:underline text-xs font-medium">Publish</button>
                    @endif
                    <button wire:click="delete({{ $announcement->id }})" wire:confirm="Delete this announcement?" class="text-red-500 hover:underline text-xs">Delete</button>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">No announcements yet.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $announcements->links() }}</div>
</div>
