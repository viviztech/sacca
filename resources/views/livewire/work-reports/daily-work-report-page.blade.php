<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Today's report submission (for non-managers) --}}
    @if(!$isManager)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Today's Work Report — {{ today()->format('d M Y') }}</h3>
            @if($todaySubmitted)
                <span class="bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full font-medium">✓ Submitted</span>
            @else
                <span class="bg-yellow-100 text-yellow-700 text-xs px-3 py-1 rounded-full font-medium">Pending</span>
            @endif
        </div>

        @if(!$todaySubmitted)
        <form wire:submit="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Work Summary * <span class="text-gray-400 font-normal">(min 20 characters)</span></label>
                <textarea wire:model="work_summary" rows="5" placeholder="Describe what you accomplished today…"
                    class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('work_summary') ? 'border-red-400' : 'border-gray-300' }}"></textarea>
                @error('work_summary') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Blockers / Issues <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea wire:model="blockers" rows="2" placeholder="Any blockers or challenges?" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
                <span wire:loading.remove>Submit Report</span>
                <span wire:loading>Submitting…</span>
            </button>
        </form>
        @else
        <div class="bg-gray-50 rounded-lg p-4">
            @if($todayReport)
            <p class="text-sm text-gray-700"><strong>Work Summary:</strong> {{ $todayReport->work_summary }}</p>
            @if($todayReport->blockers)
                <p class="text-sm text-gray-700 mt-2"><strong>Blockers:</strong> {{ $todayReport->blockers }}</p>
            @endif
            <p class="text-xs text-gray-400 mt-2">Submitted at {{ $todayReport->submitted_at?->format('h:i A') }}</p>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- Report history --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Report History</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Date</th>
                    @if($isManager) <th class="px-6 py-3 text-left font-semibold text-gray-600">Staff</th> @endif
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Summary</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Status</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Submitted</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($reports as $report)
                @php $statusColors = ['submitted'=>'green','pending'=>'yellow','escalated'=>'red']; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $report->report_date->format('d M Y') }}</td>
                    @if($isManager) <td class="px-6 py-3 text-gray-500 text-xs">{{ $report->user->name }}</td> @endif
                    <td class="px-6 py-3 text-gray-600 text-xs max-w-xs truncate">{{ $report->work_summary }}</td>
                    <td class="px-6 py-3">
                        <span class="bg-{{ $statusColors[$report->status] ?? 'gray' }}-100 text-{{ $statusColors[$report->status] ?? 'gray' }}-700 text-xs px-2 py-0.5 rounded-full capitalize">{{ $report->status }}</span>
                    </td>
                    <td class="px-6 py-3 text-gray-400 text-xs">{{ $report->submitted_at?->format('h:i A') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">No reports found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">{{ $reports->links() }}</div>
    </div>
</div>
