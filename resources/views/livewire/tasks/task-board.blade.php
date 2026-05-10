<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex justify-end mb-6">
        <button wire:click="$set('showForm', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">+ New Task</button>
    </div>

    @if($showForm)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">New Task</h3>
        <form wire:submit="save" class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input wire:model="title" type="text" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('title') ? 'border-red-400' : 'border-gray-300' }}">
                @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assign To *</label>
                <select wire:model="assigned_to_user_id" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Select person…</option>
                    @foreach($assignableUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role->label() }})</option>
                    @endforeach
                </select>
                @error('assigned_to_user_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select wire:model="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                <input wire:model="due_date" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea wire:model="description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
            </div>
            <div class="col-span-2 flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium">Create Task</button>
                <button type="button" wire:click="$set('showForm', false)" class="text-gray-500 text-sm">Cancel</button>
            </div>
        </form>
    </div>
    @endif

    {{-- Kanban Board --}}
    <div class="grid grid-cols-3 gap-6">
        @foreach(['pending' => ['Pending', 'gray'], 'in_progress' => ['In Progress', 'yellow'], 'completed' => ['Completed', 'green']] as $status => [$label, $color])
        <div class="bg-gray-50 rounded-xl p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700 text-sm">{{ $label }}</h3>
                <span class="bg-{{ $color }}-100 text-{{ $color }}-700 text-xs px-2 py-0.5 rounded-full font-medium">{{ $tasks[$status]->count() }}</span>
            </div>
            <div class="space-y-3">
                @forelse($tasks[$status] as $task)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-2">
                        <p class="font-medium text-gray-800 text-sm leading-tight">{{ $task->title }}</p>
                        @php $priorityColors = ['low'=>'gray','medium'=>'blue','high'=>'orange','critical'=>'red']; @endphp
                        <span class="flex-shrink-0 text-xs bg-{{ $priorityColors[$task->priority] ?? 'gray' }}-100 text-{{ $priorityColors[$task->priority] ?? 'gray' }}-700 px-1.5 py-0.5 rounded capitalize">{{ $task->priority }}</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $task->assignedTo?->name ?? 'Unassigned' }}</p>
                    @if($task->due_date)
                        <p class="text-xs mt-1 {{ $task->isOverdue() ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                            Due: {{ $task->due_date->format('d M') }}{{ $task->isOverdue() ? ' ⚠' : '' }}
                        </p>
                    @endif
                    <div class="flex gap-2 mt-3">
                        @if($status === 'pending')
                            <button wire:click="updateStatus({{ $task->id }}, 'in_progress')" class="text-xs text-yellow-600 hover:underline">Start</button>
                        @elseif($status === 'in_progress')
                            <button wire:click="updateStatus({{ $task->id }}, 'completed')" class="text-xs text-green-600 hover:underline">Complete</button>
                            <button wire:click="updateStatus({{ $task->id }}, 'pending')" class="text-xs text-gray-400 hover:underline">Back</button>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-4">No {{ strtolower($label) }} tasks</p>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</div>
