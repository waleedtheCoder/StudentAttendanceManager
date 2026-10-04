<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="mb-4">
                        Welcome back, <strong>{{ auth()->user()->name }}</strong>
                        ({{ ucfirst(auth()->user()->role) }}).
                    </p>

                    @if (auth()->user()->isTeacher())
                        <p class="text-gray-600">Manage the courses you teach, mark attendance, and grade assignments.</p>
                    @elseif (auth()->user()->isStudent())
                        <p class="text-gray-600">Browse and enroll in courses, check your attendance, and submit assignments.</p>
                    @else
                        <p class="text-gray-600">You have administrator access to every course in the system.</p>
                    @endif

                    <a href="{{ route('courses.index') }}" class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                        Go to Courses
                    </a>
                </div>
            </div>

            @if (! auth()->user()->isStudent())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6 text-gray-900 space-y-4">
                        <div>
                            <h3 class="font-medium">Ask about your classes</h3>
                            <p class="text-sm text-gray-500">
                                For example: "Who has missed more than 3 classes in CS101?" or "Which students haven't submitted Assignment 1?"
                            </p>
                        </div>

                        @if ($aiEnabled)
                            <form method="POST" action="{{ route('ai.ask') }}" class="flex gap-2" x-data="{ busy: false }" x-on:submit="busy = true">
                                @csrf
                                <input type="text" name="question" value="{{ old('question') }}" maxlength="500" required
                                       class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                       placeholder="Ask a question…">
                                <button x-bind:disabled="busy" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 disabled:opacity-50">
                                    <span x-show="!busy">Ask</span>
                                    <span x-show="busy" x-cloak>Thinking…</span>
                                </button>
                            </form>
                            <x-input-error :messages="$errors->get('question')" />

                            @if (session('ai_error'))
                                <div class="bg-red-100 text-red-800 text-sm rounded-md p-4">{{ session('ai_error') }}</div>
                            @endif

                            @if (session('ai_answer'))
                                <div class="bg-gray-50 border border-gray-200 rounded-md p-4 text-sm text-gray-800 whitespace-pre-line">{{ session('ai_answer') }}</div>
                                <p class="text-xs text-gray-400">AI-generated from your course records.</p>
                            @endif
                        @else
                            <p class="text-sm text-gray-500">AI features are off. Add <code>ANTHROPIC_API_KEY</code> to your <code>.env</code> file to turn them on.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
