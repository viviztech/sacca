<div>
    @php $today = today(); $user = auth()->user(); @endphp

    {{-- Welcome header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Welcome back, {{ $user->name }} 👋</h2>
            <p class="text-sm text-gray-400 mt-1">
                {{ $today->format('l, d F Y') }}
                @if($user->branch)
                    &middot; <span class="text-indigo-600 font-medium">{{ $user->branch->name }}</span>
                @endif
            </p>
        </div>
        <span class="bg-indigo-100 text-indigo-700 text-sm px-3 py-1 rounded-full font-medium">
            {{ $user->role->label() }}
        </span>
    </div>

    {{-- ══════════════════ SUPER ADMIN ══════════════════ --}}
    @if($user->isSuperAdmin())

    {{-- Global KPI row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['label'=>'Total Staff','value'=>$data['global']['total_staff'],'icon'=>'👔','color'=>'indigo'],
            ['label'=>'Total Students','value'=>$data['global']['total_students'],'icon'=>'🎓','color'=>'blue'],
            ['label'=>'Present Today','value'=>$data['global']['present_today'],'icon'=>'✅','color'=>'green'],
            ['label'=>'Pending Leaves','value'=>$data['global']['open_leaves'],'icon'=>'📅','color'=>'yellow'],
            ['label'=>'Open Complaints','value'=>$data['global']['open_complaints'],'icon'=>'💬','color'=>'red'],
            ['label'=>'Pending Tasks','value'=>$data['global']['pending_tasks'],'icon'=>'📋','color'=>'orange'],
            ['label'=>'Students Placed','value'=>$data['global']['total_placed'],'icon'=>'✈️','color'=>'teal'],
            ['label'=>'Total Users','value'=>$data['global']['total_users'],'icon'=>'👥','color'=>'purple'],
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
            <h3 class="font-semibold text-gray-800">Branch-by-Branch Overview</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Branch</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Staff</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Students</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Attendance Today</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Leaves</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Tasks</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600">Placed</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data['branch_stats'] as $stat)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">{{ $stat['branch']->code }}</span>
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
                                <div class="h-1.5 rounded-full bg-{{ $stat['attendance_pct'] >= 80 ? 'green' : ($stat['attendance_pct'] >= 60 ? 'yellow' : 'red') }}-500" style="width:{{ $stat['attendance_pct'] }}%"></div>
                            </div>
                            <span class="text-xs font-semibold {{ $stat['attendance_pct'] >= 80 ? 'text-green-600' : ($stat['attendance_pct'] >= 60 ? 'text-yellow-600' : 'text-red-500') }}">{{ $stat['attendance_pct'] }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center {{ $stat['open_leaves'] > 0 ? 'text-yellow-600 font-semibold' : 'text-gray-400' }}">{{ $stat['open_leaves'] }}</td>
                    <td class="px-6 py-4 text-center {{ $stat['pending_tasks'] > 5 ? 'text-red-500 font-semibold' : 'text-gray-600' }}">{{ $stat['pending_tasks'] }}</td>
                    <td class="px-6 py-4 text-center"><span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full font-medium">{{ $stat['placed_students'] }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @endif {{-- end super admin --}}


    {{-- ══════════════════ BRANCH ADMIN / HR MANAGER ══════════════════ --}}
    @if($user->isBranchAdmin() || $user->isHrManager())

    {{-- KPI cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Staff</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $data['stats']['total_staff'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Students</p>
            <p class="text-3xl font-bold text-blue-600 mt-1">{{ $data['stats']['total_students'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $data['stats']['total_enrollments'] }} enrolled</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Present Today</p>
            <p class="text-3xl font-bold text-{{ $data['stats']['attendance_pct'] >= 80 ? 'green' : ($data['stats']['attendance_pct'] >= 60 ? 'yellow' : 'red') }}-600 mt-1">{{ $data['stats']['present_today'] }}</p>
            <div class="flex items-center gap-2 mt-1">
                <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full bg-{{ $data['stats']['attendance_pct'] >= 80 ? 'green' : ($data['stats']['attendance_pct'] >= 60 ? 'yellow' : 'red') }}-500" style="width:{{ $data['stats']['attendance_pct'] }}%"></div>
                </div>
                <span class="text-xs font-medium text-gray-500">{{ $data['stats']['attendance_pct'] }}%</span>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Late / Absent</p>
            <p class="text-3xl font-bold text-orange-500 mt-1">{{ $data['stats']['late_today'] }} / {{ $data['stats']['absent_today'] }}</p>
        </div>
    </div>

    {{-- Alert cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        @foreach([
            ['label'=>'Pending Leaves','value'=>$data['stats']['pending_leaves'],'color'=>$data['stats']['pending_leaves']>0?'yellow':'gray','route'=>'leaves.index'],
            ['label'=>'Pending Tasks','value'=>$data['stats']['pending_tasks'],'color'=>$data['stats']['pending_tasks']>5?'orange':'gray','route'=>'tasks.index'],
            ['label'=>'Overdue Tasks','value'=>$data['stats']['overdue_tasks'],'color'=>$data['stats']['overdue_tasks']>0?'red':'gray','route'=>'tasks.index'],
            ['label'=>'Open Complaints','value'=>$data['stats']['open_complaints'],'color'=>$data['stats']['open_complaints']>0?'red':'gray','route'=>'complaints.index'],
            ['label'=>'Missing Reports','value'=>$data['stats']['missing_reports'],'color'=>$data['stats']['missing_reports']>0?'red':'gray','route'=>'work-reports.index'],
        ] as $alert)
        <a href="{{ route($alert['route']) }}" class="bg-white rounded-xl shadow-sm border border-{{ $alert['color'] !== 'gray' ? $alert['color'].'-200' : 'gray-200' }} p-4 hover:shadow-md transition">
            <p class="text-xs text-gray-500">{{ $alert['label'] }}</p>
            <p class="text-2xl font-bold text-{{ $alert['color'] }}-{{ $alert['color'] !== 'gray' ? '600' : '400' }} mt-1">{{ $alert['value'] }}</p>
        </a>
        @endforeach
    </div>

    {{-- Recent data widgets --}}
    <div class="grid grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Pending Leave Requests</h3>
                <a href="{{ route('leaves.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($data['recent_leaves'] as $leave)
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
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Active Tasks</h3>
                <a href="{{ route('tasks.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($data['recent_tasks'] as $task)
                @php $pColors=['low'=>'gray','medium'=>'blue','high'=>'orange','critical'=>'red']; @endphp
                <div class="px-6 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $task->title }}</p>
                        <p class="text-xs text-gray-400">→ {{ $task->assignedTo?->name ?? 'Unassigned' }}</p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        @if($task->isOverdue()) <span class="text-xs text-red-500">⚠</span> @endif
                        <span class="bg-{{ $pColors[$task->priority]??'gray' }}-100 text-{{ $pColors[$task->priority]??'gray' }}-700 text-xs px-2 py-0.5 rounded-full capitalize">{{ $task->priority }}</span>
                    </div>
                </div>
                @empty
                <div class="px-6 py-6 text-center text-xs text-gray-400">No active tasks 🎉</div>
                @endforelse
            </div>
        </div>
    </div>

    @endif {{-- end branch admin / hr --}}


    {{-- ══════════════════ FACULTY / ACADEMIC COORDINATOR ══════════════════ --}}
    @if($user->isFaculty() || $user->isAcademicCoordinator())

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-500 font-medium">Today's Attendance</p>
            @if($data['today_attendance'])
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $data['today_attendance']->status->label() }}</p>
                <p class="text-xs text-gray-400 mt-0.5">In: {{ $data['today_attendance']->check_in_at?->format('h:i A') ?? '—' }}@if($data['today_attendance']->check_out_at) · Out: {{ $data['today_attendance']->check_out_at->format('h:i A') }}@endif</p>
            @else
                <p class="text-2xl font-bold text-red-500 mt-1">Not Marked</p>
                <p class="text-xs text-gray-400 mt-0.5">Use mobile app to check in</p>
            @endif
        </div>
        <a href="{{ route('tasks.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
            <p class="text-xs text-gray-500 font-medium">My Pending Tasks</p>
            <p class="text-2xl font-bold {{ $data['pending_tasks'] > 0 ? 'text-orange-500' : 'text-gray-300' }} mt-1">{{ $data['pending_tasks'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $data['pending_tasks'] > 0 ? 'Tap to view' : 'All clear 🎉' }}</p>
        </a>
        <a href="{{ route('work-reports.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
            <p class="text-xs text-gray-500 font-medium">Today's Work Report</p>
            @if($data['todays_report']?->isSubmitted())
                <p class="text-2xl font-bold text-green-600 mt-1">Submitted ✓</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $data['todays_report']->submitted_at->format('h:i A') }}</p>
            @else
                <p class="text-2xl font-bold text-red-500 mt-1">Pending</p>
                <p class="text-xs text-gray-400 mt-0.5">Due by 7:00 PM</p>
            @endif
        </a>
    </div>

    <div class="grid grid-cols-4 gap-3 mb-6">
        @foreach([['label'=>'Attendance','route'=>'attendance.index','icon'=>'📋'],['label'=>'Leave','route'=>'leaves.index','icon'=>'📅'],['label'=>'Timetable','route'=>'timetable.index','icon'=>'📆'],['label'=>'LMS','route'=>'lms.index','icon'=>'🎥']] as $link)
        <a href="{{ route($link['route']) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center hover:shadow-md transition">
            <div class="text-2xl mb-1">{{ $link['icon'] }}</div>
            <p class="text-xs font-medium text-gray-600">{{ $link['label'] }}</p>
        </a>
        @endforeach
    </div>

    @if($data['recent_announcements']->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Recent Announcements</h3>
            <a href="{{ route('announcements.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($data['recent_announcements'] as $a)
            @php $cc=['notice'=>'gray','event'=>'indigo','interview_drive'=>'green','circular'=>'blue','emergency'=>'red']; @endphp
            <div class="px-6 py-3 flex items-start gap-3">
                <span class="bg-{{ $cc[$a->category]??'gray' }}-100 text-{{ $cc[$a->category]??'gray' }}-700 text-xs px-2 py-0.5 rounded-full mt-0.5 flex-shrink-0 capitalize">{{ str_replace('_',' ',$a->category) }}</span>
                <div><p class="text-sm font-medium text-gray-800">{{ $a->title }}</p><p class="text-xs text-gray-400">{{ $a->published_at->diffForHumans() }}</p></div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif {{-- end faculty --}}


    {{-- ══════════════════ STUDENT ══════════════════ --}}
    @if($user->isStudent())

    @if($data['enrollments']->count() > 0)
    <div class="grid grid-cols-{{ min($data['enrollments']->count(),3) }} gap-4 mb-6">
        @foreach($data['enrollments'] as $enrollment)
        <div class="bg-indigo-600 text-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-indigo-200 font-medium">Enrolled Course</p>
            <p class="font-bold text-lg mt-1">{{ $enrollment->batch->name }}</p>
            <p class="text-xs text-indigo-200 mt-0.5">{{ $enrollment->batch->course->name }} · Roll: {{ $enrollment->roll_number ?? '—' }}</p>
        </div>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach($data['leave_balances'] as $balance)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500">{{ $balance->leaveType->name }}</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $balance->availableDays() }}</p>
            <p class="text-xs text-gray-400">of {{ $balance->total_days }} days left</p>
        </div>
        @endforeach
        @if($data['pending_leave'] > 0)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
            <p class="text-xs text-yellow-700">Pending Leaves</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $data['pending_leave'] }}</p>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-4 gap-3 mb-6">
        @foreach([['label'=>'LMS','route'=>'lms.index','icon'=>'📚'],['label'=>'Placement','route'=>'placement.index','icon'=>'✈️'],['label'=>'Leave','route'=>'leaves.index','icon'=>'📅'],['label'=>'Complaints','route'=>'complaints.index','icon'=>'💬']] as $link)
        <a href="{{ route($link['route']) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center hover:shadow-md transition">
            <div class="text-2xl mb-1">{{ $link['icon'] }}</div>
            <p class="text-xs font-medium text-gray-600">{{ $link['label'] }}</p>
        </a>
        @endforeach
    </div>

    @if($data['upcoming_drives']->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Upcoming Placement Drives</h3>
            <a href="{{ route('placement.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($data['upcoming_drives'] as $drive)
            <div class="px-6 py-3 flex items-center justify-between">
                <div><p class="text-sm font-medium text-gray-800">{{ $drive->company->name }}</p><p class="text-xs text-gray-400">{{ $drive->title }} · {{ $drive->drive_date->format('d M Y') }}</p></div>
                @if($drive->package_lpa) <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">₹{{ $drive->package_lpa }} LPA</span> @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($data['recent_materials']->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Recent Study Materials</h3>
            <a href="{{ route('lms.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($data['recent_materials'] as $material)
            <div class="px-6 py-3 flex items-center gap-3">
                <span class="text-lg">{{ $material->type->icon() }}</span>
                <div><p class="text-sm font-medium text-gray-800">{{ $material->title }}</p><p class="text-xs text-gray-400">{{ $material->type->label() }} · {{ $material->published_at?->diffForHumans() }}</p></div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif {{-- end student --}}


    {{-- ══════════════════ PLACEMENT OFFICER ══════════════════ --}}
    @if($user->isPlacementOfficer())
    <div class="grid grid-cols-3 gap-4 mb-6">
        <a href="{{ route('placement.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
            <p class="text-xs text-gray-500 font-medium">Active Drives</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $data['active_drives'] }}</p>
        </a>
        <a href="{{ route('tasks.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
            <p class="text-xs text-gray-500 font-medium">Pending Tasks</p>
            <p class="text-3xl font-bold {{ $data['pending_tasks'] > 0 ? 'text-orange-500' : 'text-gray-300' }} mt-1">{{ $data['pending_tasks'] }}</p>
        </a>
        <a href="{{ route('work-reports.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
            <p class="text-xs text-gray-500 font-medium">EOD Work Report</p>
            <p class="text-sm font-semibold text-gray-600 mt-2">Submit today's report</p>
        </a>
    </div>
    <div class="grid grid-cols-3 gap-3 mb-6">
        @foreach([['label'=>'Placement Drives','route'=>'placement.index','icon'=>'✈️'],['label'=>'Companies','route'=>'placement.companies','icon'=>'🏢'],['label'=>'Announcements','route'=>'announcements.index','icon'=>'📢']] as $link)
        <a href="{{ route($link['route']) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center hover:shadow-md transition">
            <div class="text-2xl mb-1">{{ $link['icon'] }}</div>
            <p class="text-xs font-medium text-gray-600">{{ $link['label'] }}</p>
        </a>
        @endforeach
    </div>
    @if(isset($data['recent_announcements']) && $data['recent_announcements']->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-800 text-sm">Recent Announcements</h3></div>
        <div class="divide-y divide-gray-100">
            @foreach($data['recent_announcements'] as $a)
            <div class="px-6 py-3"><p class="text-sm font-medium text-gray-800">{{ $a->title }}</p><p class="text-xs text-gray-400">{{ $a->published_at->diffForHumans() }}</p></div>
            @endforeach
        </div>
    </div>
    @endif
    @endif {{-- end placement officer --}}


    {{-- ══════════════════ VISITOR ══════════════════ --}}
    @if($user->role->value === 'visitor')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <div class="text-5xl mb-4">👋</div>
        <h3 class="text-xl font-semibold text-gray-700">Welcome to Sacca</h3>
        <p class="text-gray-400 mt-2">Aviation Training Institute Management System</p>
        <p class="text-sm text-gray-400 mt-1">You have read-only visitor access.</p>
    </div>
    @endif

</div>
