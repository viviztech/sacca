<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">Generate Payroll</h3>
        <form wire:submit="generate" class="flex gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                <select wire:model="month" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                <select wire:model="year" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    @foreach(range(now()->year - 1, now()->year + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                <span wire:loading.remove>Generate Payslips</span>
                <span wire:loading>Generating…</span>
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Period</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Branch</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Payslips</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Status</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($cycles as $cycle)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $cycle->monthName() }} {{ $cycle->year }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $cycle->branch->name }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $cycle->payslips->count() }} payslips</td>
                    <td class="px-6 py-3">
                        @php $colors = ['draft'=>'gray','processing'=>'yellow','published'=>'green']; @endphp
                        <span class="bg-{{ $colors[$cycle->status] ?? 'gray' }}-100 text-{{ $colors[$cycle->status] ?? 'gray' }}-700 text-xs px-2 py-1 rounded-full capitalize">{{ $cycle->status }}</span>
                    </td>
                    <td class="px-6 py-3">
                        @if($cycle->status === 'draft')
                            <button wire:click="publish({{ $cycle->id }})"
                                wire:confirm="Publish payslips? Staff will receive notifications."
                                class="text-green-600 hover:underline text-xs font-medium">Publish</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">No payroll cycles yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">{{ $cycles->links() }}</div>
    </div>
</div>
