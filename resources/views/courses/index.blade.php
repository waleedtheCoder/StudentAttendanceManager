<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Courses') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 text-sm rounded-md p-4">{{ session('status') }}</div>
            @endif

            @can('create', App\Models\Course::class)
                <div class="text-right">
                    <a href="{{ route('courses.create') }}" class="inline-block px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                        + New Course
                    </a>
                </div>
            @endcan

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
                            @if (auth()->user()->isAdmin() || auth()->user()->isTeacher())
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Students</th>
                            @endif
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($courses as $course)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $course->code }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $course->title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $course->teacher->name ?? '—' }}</td>
                                @if (auth()->user()->isAdmin() || auth()->user()->isTeacher())
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $course->students_count ?? $course->students()->count() }}</td>
                                @endif
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('courses.show', $course) }}" class="text-indigo-600 hover:underline">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">No courses yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (auth()->user()->isStudent() && $availableCourses->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 font-medium text-gray-900">Available Courses</div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($availableCourses as $course)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $course->code }} — {{ $course->title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $course->teacher->name }}</td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <form method="POST" action="{{ route('courses.enroll', $course) }}">
                                            @csrf
                                            <button class="text-indigo-600 hover:underline">Enroll</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
