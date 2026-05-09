<div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-500">Role</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ auth()->user()->role->label() }}</p>
        </div>
        @if(auth()->user()->branch)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-500">Branch</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ auth()->user()->branch->name }}</p>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
        <h3 class="text-lg font-semibold text-gray-700">Welcome, {{ auth()->user()->name }}</h3>
        <p class="text-gray-400 mt-2 text-sm">Sacca Institute Management System — Phase 1 foundation is ready.</p>
    </div>
</div>
