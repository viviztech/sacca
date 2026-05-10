<div class="bg-white rounded-2xl shadow-lg p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Sign in to your account</h2>

    <form wire:submit="login" class="space-y-5">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
            <input
                wire:model="email"
                type="email"
                id="email"
                autocomplete="email"
                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }}"
                placeholder="you@example.com"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input
                wire:model="password"
                type="password"
                id="password"
                autocomplete="current-password"
                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }}"
                placeholder="••••••••"
            >
            @error('password')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input wire:model="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:underline">Forgot password?</a>
        </div>

        <button
            type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition text-sm"
        >
            <span wire:loading.remove>Sign in</span>
            <span wire:loading>Signing in…</span>
        </button>
    </form>
</div>
