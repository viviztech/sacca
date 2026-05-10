<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">Daily Grooming Inspection</h3>
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student *</label>
                    <select wire:model="student_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Select student…</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input wire:model="inspection_date" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Checklist</label>
                <div class="grid grid-cols-5 gap-3">
                    @foreach(['uniform_ok' => 'Uniform', 'hair_ok' => 'Hair', 'nails_ok' => 'Nails', 'shoes_ok' => 'Shoes', 'id_card_ok' => 'ID Card'] as $field => $label)
                    <label class="flex flex-col items-center gap-2 p-3 border rounded-lg cursor-pointer {{ $this->{$field} ? 'bg-green-50 border-green-400' : 'border-gray-200 hover:bg-gray-50' }}">
                        <input wire:model.live="{{ $field }}" type="checkbox" class="w-4 h-4 text-green-600 rounded">
                        <span class="text-xs font-medium text-gray-600">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-2">Score: {{ collect(['uniform_ok', 'hair_ok', 'nails_ok', 'shoes_ok', 'id_card_ok'])->filter(fn ($f) => $this->{$f})->count() }}/5</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                <input wire:model="remarks" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium">Save Inspection</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Date</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Student</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Uniform</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Hair</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Nails</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Shoes</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">ID Card</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Score</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($records as $rec)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 text-gray-600">{{ $rec->inspection_date->format('d M Y') }}</td>
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $rec->student->name }}</td>
                    @foreach(['uniform_ok', 'hair_ok', 'nails_ok', 'shoes_ok', 'id_card_ok'] as $field)
                        <td class="px-6 py-3 text-center">{{ $rec->{$field} ? '✓' : '✗' }}</td>
                    @endforeach
                    <td class="px-6 py-3 text-center">
                        <span class="font-bold {{ $rec->overall_score >= 4 ? 'text-green-600' : ($rec->overall_score >= 3 ? 'text-yellow-600' : 'text-red-500') }}">
                            {{ $rec->overall_score }}/5
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-10 text-center text-gray-400">No inspection records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">{{ $records->links() }}</div>
    </div>
</div>
