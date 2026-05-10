<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Upload Learning Material</h2>
        <form wire:submit="save" class="space-y-5">
            <div class="grid grid-cols-2 gap-4">
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course *</label>
                    <select wire:model="course_id" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('course_id') ? 'border-red-400' : 'border-gray-300' }}">
                        <option value="">Select course…</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                    </select>
                    @error('course_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input wire:model="title" type="text" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('title') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                    <select wire:model.live="type" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('type') ? 'border-red-400' : 'border-gray-300' }}">
                        <option value="">Select type…</option>
                        @foreach($types as $t)
                            <option value="{{ $t->value }}">{{ $t->label() }}</option>
                        @endforeach
                    </select>
                    @error('type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                @if(in_array($type, ['link']))
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">External URL</label>
                    <input wire:model="external_url" type="url" placeholder="https://…" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                @endif
                @if(in_array($type, ['note', 'video', 'assignment']))
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Upload</label>
                    <input wire:model="file" type="file" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:text-sm file:font-medium hover:file:bg-indigo-100">
                    <div wire:loading wire:target="file" class="text-xs text-gray-400 mt-1">Uploading…</div>
                </div>
                @endif
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>
                @if($type === 'assignment')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                    <input wire:model="due_date" type="datetime-local" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Marks</label>
                    <input wire:model="max_marks" type="number" min="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assignment Instructions</label>
                    <textarea wire:model="instructions" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>
                @endif
                <div class="col-span-2">
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input wire:model="publish_now" type="checkbox" class="rounded border-gray-300 text-indigo-600">
                        Publish immediately (visible to students)
                    </label>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
                    <span wire:loading.remove>Upload Material</span>
                    <span wire:loading>Uploading…</span>
                </button>
                <a href="{{ route('lms.index') }}" class="text-gray-500 text-sm self-center">Cancel</a>
            </div>
        </form>
    </div>
</div>
