<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex justify-between mb-6">
        <div></div>
        <button wire:click="create" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            + New Geo-Fence
        </button>
    </div>

    @if($showForm)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">{{ $editing ? 'Edit Geo-Fence' : 'New Geo-Fence' }}</h3>
        <form wire:submit="save" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch <span class="text-red-500">*</span></label>
                <select wire:model="branch_id" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('branch_id') ? 'border-red-400' : 'border-gray-300' }}">
                    <option value="">Select branch…</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('branch_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input wire:model="name" type="text" placeholder="e.g. Main Campus" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }}">
                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Latitude <span class="text-red-500">*</span></label>
                <input wire:model="latitude" type="number" step="0.0000001" placeholder="e.g. 11.0168" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('latitude') ? 'border-red-400' : 'border-gray-300' }}">
                @error('latitude') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Longitude <span class="text-red-500">*</span></label>
                <input wire:model="longitude" type="number" step="0.0000001" placeholder="e.g. 76.9558" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('longitude') ? 'border-red-400' : 'border-gray-300' }}">
                @error('longitude') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Radius (meters) <span class="text-red-500">*</span></label>
                <input wire:model="radius_meters" type="number" min="50" max="5000" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('radius_meters') ? 'border-red-400' : 'border-gray-300' }}">
                @error('radius_meters') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end pb-2">
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                    <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600"> Active
                </label>
            </div>
            <div class="col-span-2 flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                    <span wire:loading.remove>Save</span><span wire:loading>Saving…</span>
                </button>
                <button type="button" wire:click="$set('showForm', false)" class="text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
            </div>
        </form>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Branch</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Name</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Coordinates</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Radius</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Status</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($fences as $fence)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $fence->branch->name }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $fence->name }}</td>
                    <td class="px-6 py-3 text-gray-500 text-xs font-mono">{{ $fence->latitude }}, {{ $fence->longitude }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $fence->radius_meters }}m</td>
                    <td class="px-6 py-3">
                        @if($fence->is_active)
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">Active</span>
                        @else
                            <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded-full">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 flex gap-3">
                        <button wire:click="edit({{ $fence->id }})" class="text-indigo-600 hover:underline text-xs font-medium">Edit</button>
                        <button wire:click="delete({{ $fence->id }})" wire:confirm="Delete this geo-fence?" class="text-red-500 hover:underline text-xs font-medium">Delete</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">No geo-fences configured yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
