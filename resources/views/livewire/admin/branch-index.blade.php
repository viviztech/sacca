<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div class="flex-1 max-w-sm">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search branches…"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <a href="{{ route('admin.branches.create') }}" class="ml-4 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            + New Branch
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Name</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Code</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">City</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Manager</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Status</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($branches as $branch)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $branch->name }}</td>
                        <td class="px-6 py-4 text-gray-500 font-mono">{{ $branch->code }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $branch->city ?? '—' }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $branch->manager?->name ?? '—' }}</td>
                        <td class="px-6 py-4">
                            @if($branch->is_active)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">Active</span>
                            @else
                                <span class="bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full font-medium">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 flex items-center gap-3">
                            <a href="{{ route('admin.branches.edit', $branch) }}" class="text-indigo-600 hover:underline text-xs font-medium">Edit</a>
                            <button wire:click="deleteBranch({{ $branch->id }})"
                                wire:confirm="Delete this branch?"
                                class="text-red-500 hover:underline text-xs font-medium">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400">No branches found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $branches->links() }}
        </div>
    </div>
</div>
