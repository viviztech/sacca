<div>
    {{-- Stats row --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        @foreach([
            ['label' => 'Total Staff', 'value' => $stats['total_staff'], 'color' => 'indigo'],
            ['label' => 'Present', 'value' => $stats['present'], 'color' => 'green'],
            ['label' => 'Absent', 'value' => $stats['absent'], 'color' => 'red'],
            ['label' => 'Late', 'value' => $stats['late'], 'color' => 'yellow'],
            ['label' => 'On Leave', 'value' => $stats['on_leave'], 'color' => 'blue'],
        ] as $stat)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-500 font-medium">{{ $stat['label'] }}</p>
            <p class="text-3xl font-bold text-{{ $stat['color'] }}-600 mt-1">{{ $stat['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="flex items-center gap-3 mb-4">
        @if($branches->isNotEmpty())
        <select wire:model.live="branchFilter" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            <option value="">All Branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
        </select>
        @endif
        <div class="flex items-center gap-1.5 text-xs text-gray-400 ml-auto">
            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
            Auto-refreshes every 30s
        </div>
    </div>

    {{-- Live table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Today — {{ today()->format('D, d M Y') }}</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Employee</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Check In</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Check Out</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Status</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Worked</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">GPS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($records as $record)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr($record->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 text-sm">{{ $record->user->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $record->user->role->label() }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-gray-600">
                            {{ $record->check_in_at?->format('h:i A') ?? '—' }}
                            @if($record->late_minutes > 0)
                                <span class="text-xs text-orange-500 ml-1">(+{{ $record->late_minutes }}m late)</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ $record->check_out_at?->format('h:i A') ?? '—' }}</td>
                        <td class="px-6 py-3">
                            @php $color = $record->status->color(); @endphp
                            <span class="bg-{{ $color }}-100 text-{{ $color }}-700 text-xs px-2 py-1 rounded-full font-medium">
                                {{ $record->status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-500 text-xs">
                            @php $mins = $record->totalWorkedMinutes(); @endphp
                            {{ $mins > 0 ? floor($mins/60).'h '.($mins%60).'m' : '—' }}
                        </td>
                        <td class="px-6 py-3">
                            @if($record->gps_verified)
                                <span class="text-green-500 text-xs">✓ Verified</span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400">No attendance records for today yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
