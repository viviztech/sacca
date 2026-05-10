<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex justify-end mb-6 gap-3">
        <a href="{{ route('placement.companies') }}" class="border border-gray-300 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium">Manage Companies</a>
        <button wire:click="$set('showForm', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">+ New Drive</button>
    </div>

    @if($showForm)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">{{ $editing ? 'Edit Drive' : 'New Placement Drive' }}</h3>
        <form wire:submit="save" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Company *</label>
                <select wire:model="company_id" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('company_id') ? 'border-red-400' : 'border-gray-300' }}">
                    <option value="">Select company…</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
                @error('company_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Drive Title *</label>
                <input wire:model="title" type="text" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('title') ? 'border-red-400' : 'border-gray-300' }}">
                @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                <input wire:model="drive_date" type="date" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
                <input wire:model="venue" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Positions *</label>
                <input wire:model="positions" type="number" min="1" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Package (LPA)</label>
                <input wire:model="package_lpa" type="number" step="0.1" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Min. Attendance % for Eligibility</label>
                <input wire:model="min_attendance" type="number" min="0" max="100" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea wire:model="description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
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

    <div class="space-y-4">
        @forelse($drives as $drive)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $drive->title }}</h3>
                    <p class="text-sm text-gray-500">{{ $drive->company->name }} &middot; {{ $drive->drive_date->format('d M Y') }} @if($drive->venue) &middot; {{ $drive->venue }} @endif</p>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full">{{ $drive->positions }} position{{ $drive->positions > 1 ? 's' : '' }}</span>
                        @if($drive->package_lpa)
                            <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">₹{{ $drive->package_lpa }} LPA</span>
                        @endif
                        <span class="text-xs text-gray-400">{{ $drive->applications->count() }} applied &middot; {{ $drive->applications->where('status', 'shortlisted')->count() }} shortlisted</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="shortlist({{ $drive->id }})"
                        wire:confirm="Auto-shortlist eligible students and send WhatsApp invites?"
                        class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded-lg font-medium">
                        Shortlist & Notify
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">No placement drives yet.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $drives->links() }}</div>
</div>
