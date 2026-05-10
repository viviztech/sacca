<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Institute Overview</h2>
        <p class="text-sm text-gray-400 mt-1">{{ today()->format('l, d F Y') }} &middot; All Branches</p>
    </div>

    {{-- Global KPI row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['label' => 'Total Staff', 'value' => $globalStats['total_staff'], 'icon' => '👔', 'color' => 'indigo'],
            ['label' => 'Total Students', 'value' => $globalStats['total_students'], 'icon' => '🎓', 'color' => 'blue'],
            ['label' => 'Present Today', 'value' => $globalStats['present_today'], 'icon' => '✅', 'color' => 'green'],
            ['label' => 'Pending Leaves', 'value' => $globalStats['open_leaves'], 'icon' => '📅', 'color' => 'yellow'],
            ['label' => 'Open Complaints', 'value' => $globalStats['open_complaints'], 'icon' => '💬', 'color' => 'red'],
            ['label' => 'Pending Tasks', 'value' => $globalStats['pending_tasks'], 'icon' => '📋', 'color' => 'orange'],
            ['label' => 'Students Placed', 'value' => $globalStats['total_placed'], 'icon' => '✈️', 'color' => 'teal'],
            ['label' => 'Total Users', 'value' => $globalStats['total_users'], 'icon' => '👥', 'color' => 'purple'],
        ] as $stat)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500 font-medium">{{ $stat['label'] }}</p>
                <span class="text-lg">{{ $stat['icon'] }}</span>
            </div>
            <p class="text-3xl font-bold text-{{ $stat['color'] }}-600 mt-2">{{ $stat['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Branch comparison table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Branch-by-Branch Comparison</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Branch</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Staff</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Students</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Today's Attendance</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Open Leaves</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Pending Tasks</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Placed</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($branchStats as $stat)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                                {{ $stat['branch']->code }}
                            </span>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $stat['branch']->name }}</p>
                                <p class="text-xs text-gray-400">{{ $stat['branch']->city }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center font-medium text-gray-700">{{ $stat['total_staff'] }}</td>
                    <td class="px-6 py-4 text-center font-medium text-gray-700">{{ $stat['total_students'] }}</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                <div class="bg-{{ $stat['attendance_pct'] >= 80 ? 'green' : ($stat['attendance_pct'] >= 60 ? 'yellow' : 'red') }}-500 h-1.5 rounded-full" style="width: {{ $stat['attendance_pct'] }}%"></div>
                            </div>
                            <span class="text-xs font-medium {{ $stat['attendance_pct'] >= 80 ? 'text-green-600' : ($stat['attendance_pct'] >= 60 ? 'text-yellow-600' : 'text-red-500') }}">
                                {{ $stat['attendance_pct'] }}%
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="{{ $stat['open_leaves'] > 0 ? 'text-yellow-600 font-semibold' : 'text-gray-400' }}">{{ $stat['open_leaves'] }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="{{ $stat['pending_tasks'] > 5 ? 'text-red-500 font-semibold' : 'text-gray-600' }}">{{ $stat['pending_tasks'] }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full font-medium">{{ $stat['placed_students'] }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
