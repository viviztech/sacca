<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap items-center gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search materials…"
            class="flex-1 min-w-40 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">

        <select wire:model.live="batchFilter" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">All Batches</option>
            @foreach($batches as $batch)
                <option value="{{ $batch->id }}">{{ $batch->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="typeFilter" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">All Types</option>
            @foreach($types as $type)
                <option value="{{ $type->value }}">{{ $type->label() }}</option>
            @endforeach
        </select>

        @if($canUpload)
            <a href="{{ route('lms.upload') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Upload Material</a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($materials as $material)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col gap-3">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full font-medium">{{ $material->type->label() }}</span>
                    <h3 class="font-semibold text-gray-800 mt-2 leading-tight">{{ $material->title }}</h3>
                    <p class="text-xs text-gray-400 mt-1">{{ $material->batch->name }} &middot; {{ $material->faculty->name }}</p>
                </div>
                @if($material->is_published)
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full ml-2">Published</span>
                @else
                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full ml-2">Draft</span>
                @endif
            </div>
            @if($material->description)
                <p class="text-xs text-gray-500 line-clamp-2">{{ $material->description }}</p>
            @endif
            <div class="flex gap-2 mt-auto pt-2 border-t border-gray-100">
                @if($material->file_path)
                    <a href="{{ Storage::url($material->file_path) }}" target="_blank" class="text-indigo-600 hover:underline text-xs font-medium">Download</a>
                @elseif($material->external_url)
                    <a href="{{ $material->external_url }}" target="_blank" class="text-indigo-600 hover:underline text-xs font-medium">Open Link</a>
                @endif
                @if($canUpload)
                    <button wire:click="publish({{ $material->id }})" class="text-gray-500 hover:text-gray-700 text-xs ml-auto">
                        {{ $material->is_published ? 'Unpublish' : 'Publish' }}
                    </button>
                    <button wire:click="delete({{ $material->id }})" wire:confirm="Delete this material?" class="text-red-500 hover:underline text-xs">Delete</button>
                @endif
            </div>
        </div>
        @empty
            <div class="col-span-3 bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">
                No materials found.
            </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $materials->links() }}</div>
</div>
