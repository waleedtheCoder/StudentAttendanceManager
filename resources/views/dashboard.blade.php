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
        </div>
    </div>
</x-app-layout>
