<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Branch Dashboard</h2>
        <p class="text-sm text-gray-400 mt-1">{{ today()->format('l, d F Y') }}</p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Total Staff</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $stats['totalStaff'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Total Students</p>
            <p class="text-3xl font-bold text-blue-600 mt-1">{{ $stats['totalStudents'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['totalEnrollments'] }} active enrollments</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Present Today</p>
            <p class="text-3xl font-bold text-{{ $stats['attendancePct'] >= 80 ? 'green' : ($stats['attendancePct'] >= 60 ? 'yellow' : 'red') }}-600 mt-1">{{ $stats['presentToday'] }}</p>
            <div class="flex items-center gap-2 mt-1">
                <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                    <div class="bg-{{ $stats['attendancePct'] >= 80 ? 'green' : ($stats['attendancePct'] >= 60 ? 'yellow' : 'red') }}-500 h-1.5 rounded-full" style="width: {{ $stats['attendancePct'] }}%"></div>
                </div>
                <span class="text-xs font-medium text-gray-500">{{ $stats['attendancePct'] }}%</span>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Late / Absent</p>
            <p class="text-3xl font-bold text-orange-500 mt-1">{{ $stats['lateToday'] }} / {{ $stats['absentToday'] }}</p>
        </div>
    </div>

    {{-- Alert cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        @foreach([
            ['label' => 'Pending Leaves', 'value' => $stats['pendingLeaves'], 'color' => $stats['pendingLeaves'] > 0 ? 'yellow' : 'gray', 'route' => 'leaves.index'],
            ['label' => 'Pending Tasks', 'value' => $stats['pendingTasks'], 'color' => $stats['pendingTasks'] > 5 ? 'orange' : 'gray', 'route' => 'tasks.index'],
            ['label' => 'Overdue Tasks', 'value' => $stats['overdueTasks'], 'color' => $stats['overdueTasks'] > 0 ? 'red' : 'gray', 'route' => 'tasks.index'],
            ['label' => 'Open Complaints', 'value' => $stats['openComplaints'], 'color' => $stats['openComplaints'] > 0 ? 'red' : 'gray', 'route' => 'complaints.index'],
            ['label' => 'Missing Reports', 'value' => $stats['missingReports'], 'color' => $stats['missingReports'] > 0 ? 'red' : 'gray', 'route' => 'work-reports.index'],
        ] as $alert)
        <a href="{{ route($alert['route']) }}" class="bg-white rounded-xl shadow-sm border border-{{ $alert['color'] !== 'gray' ? $alert['color'] . '-200' : 'gray-200' }} p-4 hover:shadow-md transition">
            <p class="text-xs text-gray-500">{{ $alert['label'] }}</p>
            <p class="text-2xl font-bold text-{{ $alert['color'] }}-{{ $alert['color'] !== 'gray' ? '600' : '400' }} mt-1">{{ $alert['value'] }}</p>
        </a>
        @endforeach
    </div>

    <div class="grid grid-cols-2 gap-6">
        {{-- Pending leaves --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Pending Leave Requests</h3>
                <a href="{{ route('leaves.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentLeaves as $leave)
                <div class="px-6 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $leave->user->name }}</p>
                        <p class="text-xs text-gray-400">{{ $leave->leaveType->name }} · {{ $leave->total_days }}d · {{ $leave->from_date->format('d M') }}</p>
                    </div>
                    <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-0.5 rounded-full">Pending</span>
                </div>
                @empty
                <div class="px-6 py-6 text-center text-xs text-gray-400">No pending leaves 🎉</div>
                @endforelse
            </div>
        </div>

        {{-- Active tasks --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Active Tasks</h3>
                <a href="{{ route('tasks.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentTasks as $task)
                @php $priorityColors = ['low'=>'gray','medium'=>'blue','high'=>'orange','critical'=>'red']; @endphp
                <div class="px-6 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $task->title }}</p>
                        <p class="text-xs text-gray-400">→ {{ $task->assignedTo?->name ?? 'Unassigned' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($task->isOverdue())
                            <span class="text-xs text-red-500 font-medium">Overdue</span>
                        @endif
                        <span class="bg-{{ $priorityColors[$task->priority] ?? 'gray' }}-100 text-{{ $priorityColors[$task->priority] ?? 'gray' }}-700 text-xs px-2 py-0.5 rounded-full capitalize">{{ $task->priority }}</span>
                    </div>
                </div>
                @empty
                <div class="px-6 py-6 text-center text-xs text-gray-400">No active tasks 🎉</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
