<div>
    @php $today = today(); @endphp

    {{-- Welcome header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Welcome back, {{ auth()->user()->name }} 👋</h2>
            <p class="text-sm text-gray-400 mt-1">
                {{ $today->format('l, d F Y') }}
                @if(auth()->user()->branch)
                    &middot; <span class="text-indigo-600 font-medium">{{ auth()->user()->branch->name }}</span>
                @endif
            </p>
        </div>
        <div class="text-right">
            <span class="bg-indigo-100 text-indigo-700 text-sm px-3 py-1 rounded-full font-medium">
                {{ auth()->user()->role->label() }}
            </span>
        </div>
    </div>

    {{-- ======================== FACULTY / ACADEMIC COORDINATOR ======================== --}}
    @if(auth()->user()->isFaculty() || auth()->user()->isAcademicCoordinator())

    {{-- Quick status row --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        {{-- Today's attendance --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs text-gray-500 font-medium">Today's Attendance</p>
            @if($data['today_attendance'])
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $data['today_attendance']->status->label() }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    In: {{ $data['today_attendance']->check_in_at?->format('h:i A') ?? '—' }}
                    @if($data['today_attendance']->check_out_at)
                        · Out: {{ $data['today_attendance']->check_out_at->format('h:i A') }}
                    @endif
                </p>
            @else
                <p class="text-2xl font-bold text-red-500 mt-1">Not Marked</p>
                <p class="text-xs text-gray-400 mt-0.5">Use mobile app to check in</p>
            @endif
        </div>

        {{-- Pending tasks --}}
        <a href="{{ route('tasks.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
            <p class="text-xs text-gray-500 font-medium">My Pending Tasks</p>
            <p class="text-2xl font-bold {{ $data['pending_tasks'] > 0 ? 'text-orange-500' : 'text-gray-300' }} mt-1">
                {{ $data['pending_tasks'] }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $data['pending_tasks'] > 0 ? 'Tap to view' : 'All clear 🎉' }}</p>
        </a>

        {{-- Work report --}}
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

    {{-- Quick links --}}
    <div class="grid grid-cols-4 gap-3 mb-6">
        @foreach([
            ['label' => 'Attendance', 'route' => 'attendance.index', 'icon' => '📋', 'color' => 'indigo'],
            ['label' => 'Leave', 'route' => 'leaves.index', 'icon' => '📅', 'color' => 'blue'],
            ['label' => 'Timetable', 'route' => 'timetable.index', 'icon' => '📆', 'color' => 'purple'],
            ['label' => 'LMS', 'route' => 'lms.index', 'icon' => '🎥', 'color' => 'green'],
        ] as $link)
        <a href="{{ route($link['route']) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center hover:shadow-md hover:border-{{ $link['color'] }}-300 transition">
            <div class="text-2xl mb-1">{{ $link['icon'] }}</div>
            <p class="text-xs font-medium text-gray-600">{{ $link['label'] }}</p>
        </a>
        @endforeach
    </div>

    {{-- Recent announcements --}}
    @if($data['recent_announcements']->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Recent Announcements</h3>
            <a href="{{ route('announcements.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($data['recent_announcements'] as $announcement)
            @php $catColors = ['notice'=>'gray','event'=>'indigo','interview_drive'=>'green','circular'=>'blue','emergency'=>'red']; @endphp
            <div class="px-6 py-3">
                <div class="flex items-start gap-3">
                    <span class="bg-{{ $catColors[$announcement->category] ?? 'gray' }}-100 text-{{ $catColors[$announcement->category] ?? 'gray' }}-700 text-xs px-2 py-0.5 rounded-full mt-0.5 flex-shrink-0 capitalize">{{ str_replace('_', ' ', $announcement->category) }}</span>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $announcement->title }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $announcement->published_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif {{-- end faculty --}}


    {{-- ======================== STUDENT ======================== --}}
    @if(auth()->user()->isStudent())

    {{-- Enrolled batches --}}
    @if($data['enrollments']->count() > 0)
    <div class="grid grid-cols-{{ min($data['enrollments']->count(), 3) }} gap-4 mb-6">
        @foreach($data['enrollments'] as $enrollment)
        <div class="bg-indigo-600 text-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-indigo-200 font-medium">Enrolled Course</p>
            <p class="font-bold text-lg mt-1">{{ $enrollment->batch->name }}</p>
            <p class="text-xs text-indigo-200 mt-0.5">{{ $enrollment->batch->course->name }} · Roll: {{ $enrollment->roll_number ?? '—' }}</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Stats row --}}
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

    {{-- Quick links for students --}}
    <div class="grid grid-cols-4 gap-3 mb-6">
        @foreach([
            ['label' => 'LMS', 'route' => 'lms.index', 'icon' => '📚'],
            ['label' => 'Placement', 'route' => 'placement.index', 'icon' => '✈️'],
            ['label' => 'Leave', 'route' => 'leaves.index', 'icon' => '📅'],
            ['label' => 'Complaints', 'route' => 'complaints.index', 'icon' => '💬'],
        ] as $link)
        <a href="{{ route($link['route']) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center hover:shadow-md transition">
            <div class="text-2xl mb-1">{{ $link['icon'] }}</div>
            <p class="text-xs font-medium text-gray-600">{{ $link['label'] }}</p>
        </a>
        @endforeach
    </div>

    {{-- Upcoming placement drives --}}
    @if($data['upcoming_drives']->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Upcoming Placement Drives</h3>
            <a href="{{ route('placement.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($data['upcoming_drives'] as $drive)
            <div class="px-6 py-3 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $drive->company->name }}</p>
                    <p class="text-xs text-gray-400">{{ $drive->title }} · {{ $drive->drive_date->format('d M Y') }}</p>
                </div>
                @if($drive->package_lpa)
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">₹{{ $drive->package_lpa }} LPA</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent LMS materials --}}
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
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $material->title }}</p>
                    <p class="text-xs text-gray-400">{{ $material->type->label() }} · {{ $material->published_at?->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif {{-- end student --}}


    {{-- ======================== PLACEMENT OFFICER ======================== --}}
    @if(auth()->user()->isPlacementOfficer())

    <div class="grid grid-cols-3 gap-4 mb-6">
        <a href="{{ route('placement.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
            <p class="text-xs text-gray-500 font-medium">Active Drives</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $data['active_drives'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Upcoming placements</p>
        </a>
        <a href="{{ route('tasks.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
            <p class="text-xs text-gray-500 font-medium">My Pending Tasks</p>
            <p class="text-3xl font-bold {{ $data['pending_tasks'] > 0 ? 'text-orange-500' : 'text-gray-300' }} mt-1">{{ $data['pending_tasks'] }}</p>
        </a>
        <a href="{{ route('work-reports.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
            <p class="text-xs text-gray-500 font-medium">Work Report</p>
            <p class="text-sm font-bold text-gray-600 mt-2">Submit today's EOD report</p>
        </a>
    </div>

    <div class="grid grid-cols-3 gap-3 mb-6">
        @foreach([
            ['label' => 'Placement Drives', 'route' => 'placement.index', 'icon' => '✈️'],
            ['label' => 'Companies', 'route' => 'placement.companies', 'icon' => '🏢'],
            ['label' => 'Announcements', 'route' => 'announcements.index', 'icon' => '📢'],
        ] as $link)
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
            @foreach($data['recent_announcements'] as $announcement)
            <div class="px-6 py-3">
                <p class="text-sm font-medium text-gray-800">{{ $announcement->title }}</p>
                <p class="text-xs text-gray-400">{{ $announcement->published_at->diffForHumans() }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif {{-- end placement officer --}}


    {{-- ======================== VISITOR / FALLBACK ======================== --}}
    @if(auth()->user()->role->value === 'visitor')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <div class="text-5xl mb-4">👋</div>
        <h3 class="text-xl font-semibold text-gray-700">Welcome to Sacca</h3>
        <p class="text-gray-400 mt-2">Aviation Training Institute Management System</p>
        <p class="text-sm text-gray-400 mt-1">You have read-only visitor access.</p>
    </div>
    @endif

</div>
