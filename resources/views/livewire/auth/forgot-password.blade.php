<div class="bg-white rounded-2xl shadow-lg p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-2 text-center">Forgot Password</h2>
    <p class="text-sm text-gray-500 text-center mb-6">Enter your email and we'll send a reset link.</p>

    @if($sent)
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm text-center">
            ✓ Reset link sent! Check your inbox.
        </div>
        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:underline">Back to login</a>
        </div>
    @else
        <form wire:submit="sendLink" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                <input wire:model="email" type="email" autocomplete="email"
                    class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }}"
                    placeholder="you@sacca.in">
                @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
                <span wire:loading.remove>Send Reset Link</span>
                <span wire:loading>Sending…</span>
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to login</a>
        </div>
    @endif
</div>
