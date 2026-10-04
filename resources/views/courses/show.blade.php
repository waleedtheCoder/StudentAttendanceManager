<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $course->code }} — {{ $course->title }}
            </h2>
            @can('update', $course)
                <a href="{{ route('courses.edit', $course) }}" class="text-sm text-indigo-600 hover:underline">Edit Course</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 text-sm rounded-md p-4">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-1">Taught by {{ $course->teacher->name }}</p>
                <p class="text-gray-700">{{ $course->description ?: 'No description provided.' }}</p>

                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('courses.attendance.index', $course) }}" class="px-3 py-1.5 bg-gray-100 text-sm rounded-md hover:bg-gray-200">
                        Attendance
                    </a>

                    @can('mark', [App\Models\Attendance::class, $course])
                        <a href="{{ route('courses.attendance.create', $course) }}" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                            Mark Today's Attendance
                        </a>
                    @endcan

                    @can('create', [App\Models\Assignment::class, $course])
                        <a href="{{ route('assignments.create', $course) }}" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                            + New Assignment
                        </a>
                    @endcan

                    @if (auth()->user()->isStudent())
                        <form method="POST" action="{{ route('courses.unenroll', $course) }}" onsubmit="return confirm('Leave this course?');">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-1.5 bg-red-50 text-red-700 text-sm rounded-md hover:bg-red-100">Leave Course</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 font-medium text-gray-900">
                        Roster ({{ $course->students->count() }})
                    </div>
                    <ul class="divide-y divide-gray-200">
                        @forelse ($course->students as $student)
                            <li class="px-6 py-3 text-sm text-gray-700">{{ $student->name }} <span class="text-gray-400">{{ $student->email }}</span></li>
                        @empty
                            <li class="px-6 py-3 text-sm text-gray-500">No students enrolled yet.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 font-medium text-gray-900">Assignments</div>
                    <ul class="divide-y divide-gray-200">
                        @forelse ($course->assignments as $assignment)
                            <li class="px-6 py-3 text-sm flex justify-between items-center">
                                <a href="{{ route('assignments.show', $assignment) }}" class="text-indigo-600 hover:underline">{{ $assignment->title }}</a>
                                <span class="text-gray-400">Due {{ $assignment->due_date->format('M j, Y g:ia') }}</span>
                            </li>
                        @empty
                            <li class="px-6 py-3 text-sm text-gray-500">No assignments yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
