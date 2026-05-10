<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    <div class="flex justify-end mb-6">
        <button wire:click="$set('showForm', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Add Company</button>
    </div>
    @if($showForm)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">{{ $editing ? 'Edit Company' : 'New Company' }}</h3>
        <form wire:submit="save" class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Company Name *</label>
                <input wire:model="name" type="text" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }}">
                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Industry</label><input wire:model="industry" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label><input wire:model="contact_person" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input wire:model="contact_email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label><input wire:model="contact_phone" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"></div>
            <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Website</label><input wire:model="website" type="url" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"></div>
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
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Company</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Industry</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Contact</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($companies as $company)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $company->name }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $company->industry ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-500 text-xs">{{ $company->contact_person ?? '' }} {{ $company->contact_email ? "· {$company->contact_email}" : '' }}</td>
                    <td class="px-6 py-3"><button wire:click="edit({{ $company->id }})" class="text-indigo-600 hover:underline text-xs font-medium">Edit</button></td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400">No companies yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">{{ $companies->links() }}</div>
    </div>
</div>
