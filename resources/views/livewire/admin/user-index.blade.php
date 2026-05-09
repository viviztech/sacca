<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search users…"
            class="flex-1 min-w-48 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">

        <select wire:model.live="roleFilter" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            <option value="">All Roles</option>
            @foreach($roles as $role)
                <option value="{{ $role->value }}">{{ $role->label() }}</option>
            @endforeach
        </select>

        @if($branches->isNotEmpty())
        <select wire:model.live="branchFilter" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            <option value="">All Branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
        </select>
        @endif

        <a href="{{ route('admin.users.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            + New User
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Name</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Email</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Role</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Branch</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Status</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $user->name }}</p>
                                    @if($user->employee_id)
                                        <p class="text-xs text-gray-400">{{ $user->employee_id }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <span class="bg-indigo-50 text-indigo-700 text-xs px-2 py-1 rounded-full font-medium">
                                {{ $user->role->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $user->branch?->name ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <button wire:click="toggleActive({{ $user->id }})" class="cursor-pointer">
                                @if($user->is_active)
                                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">Active</span>
                                @else
                                    <span class="bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full font-medium">Inactive</span>
                                @endif
                            </button>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:underline text-xs font-medium">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>
</div>
