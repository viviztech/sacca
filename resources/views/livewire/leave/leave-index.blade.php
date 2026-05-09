<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Balance cards (for non-approvers) --}}
    @if($balances->isNotEmpty())
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach($balances as $balance)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500">{{ $balance->leaveType->name }}</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $balance->availableDays() }}</p>
            <p class="text-xs text-gray-400 mt-0.5">of {{ $balance->total_days }} days available</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <select wire:model.live="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            <option value="">All Status</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
        @if(!$isApprover)
        <button wire:click="$set('showRequestForm', true)" class="ml-auto bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            + Apply for Leave
        </button>
        @endif
    </div>

    {{-- Request form --}}
    @if($showRequestForm)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">New Leave Request</h3>
        <form wire:submit="submitRequest" class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type *</label>
                <select wire:model="leave_type_id" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('leave_type_id') ? 'border-red-400' : 'border-gray-300' }}">
                    <option value="">Select type…</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('leave_type_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date *</label>
                <input wire:model="from_date" type="date" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('from_date') ? 'border-red-400' : 'border-gray-300' }}">
                @error('from_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date *</label>
                <input wire:model="to_date" type="date" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('to_date') ? 'border-red-400' : 'border-gray-300' }}">
                @error('to_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                <textarea wire:model="reason" rows="3" class="w-full px-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 {{ $errors->has('reason') ? 'border-red-400' : 'border-gray-300' }}"></textarea>
                @error('reason') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2 flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                    <span wire:loading.remove>Submit Request</span><span wire:loading>Submitting…</span>
                </button>
                <button type="button" wire:click="$set('showRequestForm', false)" class="text-gray-500 text-sm">Cancel</button>
            </div>
        </form>
    </div>
    @endif

    {{-- Leave requests table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    @if($isApprover) <th class="px-6 py-3 text-left font-semibold text-gray-600">Employee</th> @endif
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Type</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Dates</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Days</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Status</th>
                    @if($isApprover) <th class="px-6 py-3 text-left font-semibold text-gray-600">Actions</th> @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($requests as $req)
                <tr class="hover:bg-gray-50">
                    @if($isApprover)
                    <td class="px-6 py-3">
                        <p class="font-medium text-gray-800">{{ $req->user->name }}</p>
                        <p class="text-xs text-gray-400">{{ $req->user->role->label() }}</p>
                    </td>
                    @endif
                    <td class="px-6 py-3 text-gray-600">{{ $req->leaveType->name }}</td>
                    <td class="px-6 py-3 text-gray-600 text-xs">
                        {{ $req->from_date->format('d M') }} – {{ $req->to_date->format('d M Y') }}
                    </td>
                    <td class="px-6 py-3 text-gray-600">{{ $req->total_days }}d</td>
                    <td class="px-6 py-3">
                        @php $color = $req->status->color(); @endphp
                        <span class="bg-{{ $color }}-100 text-{{ $color }}-700 text-xs px-2 py-1 rounded-full font-medium">
                            {{ $req->status->label() }}
                        </span>
                    </td>
                    @if($isApprover)
                    <td class="px-6 py-3 flex gap-2">
                        @if($req->isPending())
                        <button wire:click="openApproval({{ $req->id }})" class="text-green-600 hover:underline text-xs font-medium">Approve</button>
                        <button wire:click="openApproval({{ $req->id }}, true)" class="text-red-500 hover:underline text-xs font-medium">Reject</button>
                        @endif
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">No leave requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">{{ $requests->links() }}</div>
    </div>

    {{-- Approval modal --}}
    @if($showApprovalModal && $selectedRequest)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="font-semibold text-gray-800 mb-3">
                {{ $isRejecting ? 'Reject Leave Request' : 'Approve Leave Request' }}
            </h3>
            <div class="bg-gray-50 rounded-lg p-4 mb-4 text-sm text-gray-600 space-y-1">
                <p><span class="font-medium">Employee:</span> {{ $selectedRequest->user->name }}</p>
                <p><span class="font-medium">Type:</span> {{ $selectedRequest->leaveType->name }}</p>
                <p><span class="font-medium">Dates:</span> {{ $selectedRequest->from_date->format('d M Y') }} – {{ $selectedRequest->to_date->format('d M Y') }} ({{ $selectedRequest->total_days }} days)</p>
                <p><span class="font-medium">Reason:</span> {{ $selectedRequest->reason }}</p>
            </div>
            @if($isRejecting)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason *</label>
                    <textarea wire:model="rejectionReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500"></textarea>
                    @error('rejectionReason') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-3">
                    <button wire:click="reject" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-medium">Reject</button>
                    <button wire:click="$set('showApprovalModal', false)" class="text-gray-500 text-sm">Cancel</button>
                </div>
            @else
                <div class="flex gap-3">
                    <button wire:click="approve" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium">Approve</button>
                    <button wire:click="$set('showApprovalModal', false)" class="text-gray-500 text-sm">Cancel</button>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
