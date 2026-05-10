<div class="bg-white rounded-2xl shadow-lg p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Reset Password</h2>

    <form wire:submit="submit" class="space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input wire:model="email" type="email"
                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }}">
            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
            <input wire:model="password" type="password"
                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }}"
                placeholder="Min. 8 characters">
            @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input wire:model="password_confirmation" type="password"
                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('password_confirmation') ? 'border-red-400' : 'border-gray-300' }}">
        </div>

        <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
            <span wire:loading.remove>Reset Password</span>
            <span wire:loading>Resetting…</span>
        </button>
    </form>
</div>
