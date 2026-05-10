<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ $quiz->title }}</h2>
            <p class="text-sm text-gray-400">{{ $quiz->questions->count() }} questions &middot; {{ $quiz->time_limit_minutes }} min &middot; Pass: {{ $quiz->passing_marks }} marks</p>
        </div>
        @if(!$submitted)
        <div class="bg-orange-50 border border-orange-200 text-orange-700 px-4 py-2 rounded-lg text-sm font-mono font-semibold"
             x-data="{ remaining: {{ $remainingSeconds }} }"
             x-init="setInterval(() => { if(remaining > 0) remaining--; else $wire.submit(); }, 1000)"
             x-text="Math.floor(remaining/60).toString().padStart(2,'0') + ':' + (remaining%60).toString().padStart(2,'0')">
        </div>
        @endif
    </div>

    @if($submitted && $attempt)
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6 text-center">
        @if($attempt->passed)
            <div class="text-green-600 text-4xl mb-2">🎉</div>
            <h3 class="text-xl font-bold text-green-700">Passed!</h3>
        @else
            <div class="text-red-500 text-4xl mb-2">📚</div>
            <h3 class="text-xl font-bold text-red-600">Not Passed</h3>
        @endif
        <p class="text-gray-600 mt-2">Score: <span class="font-bold text-gray-800">{{ $attempt->score }}</span> / {{ $quiz->totalMarks() }} marks</p>
        <a href="{{ route('lms.index') }}" class="inline-block mt-4 text-indigo-600 hover:underline text-sm">Back to LMS</a>
    </div>
    @endif

    <div class="space-y-6">
        @foreach($quiz->questions as $i => $question)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex gap-3">
                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold">{{ $i + 1 }}</span>
                <div class="flex-1">
                    <p class="font-medium text-gray-800 mb-3">{{ $question->question_text }}</p>
                    <p class="text-xs text-gray-400 mb-3">{{ $question->marks }} mark{{ $question->marks > 1 ? 's' : '' }}</p>

                    @if($question->type->value === 'mcq' && $question->options)
                        <div class="space-y-2">
                            @foreach($question->options as $idx => $option)
                            @php $optText = is_array($option) ? $option['text'] : $option; @endphp
                            <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer
                                {{ !$submitted ? 'hover:bg-gray-50 border-gray-200' : '' }}
                                {{ $submitted && isset($answers[$question->id]) && $answers[$question->id] === $optText && $question->isCorrect($optText) ? 'bg-green-50 border-green-400' : '' }}
                                {{ $submitted && isset($answers[$question->id]) && $answers[$question->id] === $optText && !$question->isCorrect($optText) ? 'bg-red-50 border-red-400' : '' }}">
                                <input type="radio"
                                    name="q_{{ $question->id }}"
                                    value="{{ $optText }}"
                                    {{ isset($answers[$question->id]) && $answers[$question->id] === $optText ? 'checked' : '' }}
                                    {{ $submitted ? 'disabled' : '' }}
                                    wire:click="saveAnswer({{ $question->id }}, '{{ addslashes($optText) }}')"
                                    class="text-indigo-600">
                                <span class="text-sm text-gray-700">{{ $optText }}</span>
                            </label>
                            @endforeach
                        </div>
                    @elseif($question->type->value === 'true_false')
                        <div class="flex gap-3">
                            @foreach(['True', 'False'] as $option)
                            <label class="flex items-center gap-2 px-4 py-2 rounded-lg border cursor-pointer
                                {{ isset($answers[$question->id]) && $answers[$question->id] === $option ? 'bg-indigo-50 border-indigo-400' : 'border-gray-200 hover:bg-gray-50' }}">
                                <input type="radio" name="q_{{ $question->id }}" value="{{ $option }}"
                                    {{ isset($answers[$question->id]) && $answers[$question->id] === $option ? 'checked' : '' }}
                                    {{ $submitted ? 'disabled' : '' }}
                                    wire:click="saveAnswer({{ $question->id }}, '{{ $option }}')"
                                    class="text-indigo-600">
                                <span class="text-sm">{{ $option }}</span>
                            </label>
                            @endforeach
                        </div>
                    @else
                        <input type="text" placeholder="Your answer…"
                            value="{{ $answers[$question->id] ?? '' }}"
                            {{ $submitted ? 'disabled' : '' }}
                            wire:blur="saveAnswer({{ $question->id }}, $event.target.value)"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if(!$submitted)
    <div class="mt-6 flex justify-end">
        <button wire:click="submit" wire:confirm="Submit quiz? You cannot change answers after submitting."
            class="bg-green-600 hover:bg-green-700 text-white px-8 py-2.5 rounded-lg text-sm font-medium">
            Submit Quiz
        </button>
    </div>
    @endif
</div>
