<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Attendance — {{ $course->code }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 text-sm rounded-md p-4">{{ session('status') }}</div>
            @endif

            @can('mark', [App\Models\Attendance::class, $course])
                <div class="text-right">
                    <a href="{{ route('courses.attendance.create', $course) }}" class="inline-block px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                        Mark Attendance
                    </a>
                </div>
            @endcan

            @forelse ($attendances as $date => $rows)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 font-medium text-gray-900">{{ \Illuminate\Support\Carbon::parse($date)->format('l, M j, Y') }}</div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($rows as $row)
                                <tr>
                                    @if (auth()->user()->isTeacher() || auth()->user()->isAdmin())
                                        <td class="px-6 py-3 text-sm text-gray-900">{{ $row->student->name }}</td>
                                    @endif
                                    <td class="px-6 py-3 text-sm">
                                        <span @class([
                                            'px-2 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-100 text-green-800' => $row->status === 'present',
                                            'bg-red-100 text-red-800' => $row->status === 'absent',
                                            'bg-yellow-100 text-yellow-800' => $row->status === 'late',
                                        ])>
                                            {{ ucfirst($row->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-sm text-gray-500">
                    No attendance records yet.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
