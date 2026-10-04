<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mark Attendance — {{ $course->code }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('courses.attendance.store', $course) }}">
                    @csrf

                    <div class="mb-6">
                        <x-input-label for="date" value="Date" />
                        <input type="date" id="date" name="date" value="{{ $date }}"
                               class="block mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                               onchange="window.location.href = '{{ route('courses.attendance.create', $course) }}?date=' + this.value" />
                    </div>

                    @if ($students->isEmpty())
                        <p class="text-sm text-gray-500">No students enrolled in this course yet.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($students as $student)
                                    @php $current = $existing->get($student->id)?->status ?? 'present'; @endphp
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $student->name }}</td>
                                        <td class="px-4 py-3">
                                            @foreach (['present', 'absent', 'late'] as $status)
                                                <label class="inline-flex items-center mr-4 text-sm">
                                                    <input type="radio" name="statuses[{{ $student->id }}]" value="{{ $status }}" {{ $current === $status ? 'checked' : '' }} class="mr-1">
                                                    {{ ucfirst($status) }}
                                                </label>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-6 flex justify-end">
                            <x-primary-button>Save Attendance</x-primary-button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
