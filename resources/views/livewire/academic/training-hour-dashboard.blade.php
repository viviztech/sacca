<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Summary cards --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        @foreach($sessionTypes as $type)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-500">{{ $type->label() }}</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ number_format($summary[$type->value] ?? 0, 1) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">hours total</p>
        </div>
        @endforeach
    </div>

    <div class="flex justify-end mb-4">
        <button wire:click="$set('showForm', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Log Hours</button>
    </div>

    @if($showForm)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">Log Training Hours</h3>
        <form wire:submit="save" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Batch *</label>
                <select wire:model="batch_id" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('batch_id') ? 'border-red-400' : 'border-gray-300' }}">
                    <option value="">Select batch…</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                    @endforeach
                </select>
                @error('batch_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                <input wire:model="log_date" type="date" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('log_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hours *</label>
                <input wire:model="hours_logged" type="number" step="0.5" min="0.5" max="12" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('hours_logged') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Session Type *</label>
                <select wire:model="session_type" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    @foreach($sessionTypes as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <input wire:model="notes" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="col-span-2 flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium">Save</button>
                <button type="button" wire:click="$set('showForm', false)" class="text-gray-500 text-sm">Cancel</button>
            </div>
        </form>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Date</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">User</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Batch</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Type</th>
                    <th class="px-6 py-3 text-right font-semibold text-gray-600">Hours</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 text-gray-600">{{ $log->log_date->format('d M Y') }}</td>
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $log->user->name }}</td>
                    <td class="px-6 py-3 text-gray-500 text-xs">{{ $log->batch->name }}</td>
                    <td class="px-6 py-3">
                        <span class="bg-indigo-50 text-indigo-700 text-xs px-2 py-0.5 rounded-full">{{ $log->session_type->label() }}</span>
                    </td>
                    <td class="px-6 py-3 text-right font-semibold text-gray-800">{{ $log->hours_logged }}h</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">No training hours logged yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">{{ $logs->links() }}</div>
    </div>
</div>
