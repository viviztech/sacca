<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex justify-end mb-6">
        <button wire:click="$set('showForm', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Check In Visitor</button>
    </div>

    @if($showForm)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">Visitor Check-In</h3>
        <form wire:submit="checkIn" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Visitor Name *</label>
                <input wire:model="visitor_name" type="text" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('visitor_name') ? 'border-red-400' : 'border-gray-300' }}">
                @error('visitor_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                <input wire:model="phone" type="text" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('phone') ? 'border-red-400' : 'border-gray-300' }}">
                @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Organization</label>
                <input wire:model="organization" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input wire:model="email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Purpose *</label>
                <input wire:model="purpose" type="text" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('purpose') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Visiting (Host)</label>
                <select wire:model="host_user_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">No specific host</option>
                    @foreach($hosts as $host)
                        <option value="{{ $host->id }}">{{ $host->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Badge Number</label>
                <input wire:model="badge_number" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="col-span-2 flex gap-3">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium">Check In</button>
                <button type="button" wire:click="$set('showForm', false)" class="text-gray-500 text-sm">Cancel</button>
            </div>
        </form>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Visitor</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Organization</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Purpose</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Host</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Check In</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Check Out</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($visitors as $visitor)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3">
                        <p class="font-medium text-gray-800">{{ $visitor->visitor_name }}</p>
                        <p class="text-xs text-gray-400">{{ $visitor->phone }}</p>
                    </td>
                    <td class="px-6 py-3 text-gray-500 text-xs">{{ $visitor->organization ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-600 text-xs">{{ $visitor->purpose }}</td>
                    <td class="px-6 py-3 text-gray-500 text-xs">{{ $visitor->host?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-600 text-xs">{{ $visitor->check_in_at->format('h:i A') }}</td>
                    <td class="px-6 py-3 text-xs {{ $visitor->check_out_at ? 'text-gray-500' : 'text-green-600 font-medium' }}">
                        {{ $visitor->check_out_at?->format('h:i A') ?? 'Still in' }}
                    </td>
                    <td class="px-6 py-3">
                        @if(! $visitor->check_out_at)
                            <button wire:click="checkOut({{ $visitor->id }})" class="text-red-500 hover:underline text-xs font-medium">Check Out</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400">No visitors today.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">{{ $visitors->links() }}</div>
    </div>
</div>
