<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    @if(!auth()->user()->isStudent())
    <div class="flex justify-end mb-6">
        <button wire:click="$set('showForm', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Upload Document</button>
    </div>
    @endif

    @if($showForm)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">Upload Document</h3>
        <form wire:submit="save" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Person *</label>
                <select wire:model="owner_id" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('owner_id') ? 'border-red-400' : 'border-gray-300' }}">
                    <option value="">Select person…</option>
                    @foreach($staffAndStudents as $person)
                        <option value="{{ $person->id }}">{{ $person->name }} ({{ $person->role->label() }})</option>
                    @endforeach
                </select>
                @error('owner_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                <select wire:model="category" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Select category…</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ str_replace('_', ' ', ucfirst($cat)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input wire:model="title" type="text" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                <input wire:model="expires_at" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">File *</label>
                <input wire:model="file" type="file" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700">
                @error('file') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2 flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium">Upload</button>
                <button type="button" wire:click="$set('showForm', false)" class="text-gray-500 text-sm">Cancel</button>
            </div>
        </form>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Document</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Person</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Category</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Expiry</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($documents as $doc)
                <tr class="hover:bg-gray-50 {{ $doc->isExpired() ? 'bg-red-50' : ($doc->expiresSoon() ? 'bg-yellow-50' : '') }}">
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $doc->title }}</td>
                    <td class="px-6 py-3 text-gray-500 text-xs">{{ $doc->owner->name }}</td>
                    <td class="px-6 py-3"><span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ str_replace('_', ' ', ucfirst($doc->category)) }}</span></td>
                    <td class="px-6 py-3 text-xs {{ $doc->isExpired() ? 'text-red-600 font-bold' : ($doc->expiresSoon() ? 'text-yellow-600' : 'text-gray-400') }}">
                        {{ $doc->expires_at?->format('d M Y') ?? 'No expiry' }}
                        @if($doc->isExpired()) <span class="ml-1">⚠ Expired</span> @endif
                        @if($doc->expiresSoon()) <span class="ml-1">⏰ Soon</span> @endif
                    </td>
                    <td class="px-6 py-3 flex gap-2">
                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-indigo-600 hover:underline text-xs font-medium">View</a>
                        @if(!auth()->user()->isStudent())
                            <button wire:click="delete({{ $doc->id }})" wire:confirm="Delete this document?" class="text-red-500 hover:underline text-xs">Delete</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">No documents found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">{{ $documents->links() }}</div>
    </div>
</div>
