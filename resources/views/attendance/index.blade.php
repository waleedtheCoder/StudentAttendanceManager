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

            @if (session('ai_error'))
                <div class="bg-red-100 text-red-800 text-sm rounded-md p-4">{{ session('ai_error') }}</div>
            @endif

            @can('mark', [App\Models\Attendance::class, $course])
                @if ($aiEnabled)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <div>
                                <div class="font-medium text-gray-900">AI Attendance Insights</div>
                                @if ($insights)
                                    <div class="text-xs text-gray-400">Generated {{ \Illuminate\Support\Carbon::parse($insights['generated_at'])->diffForHumans() }}</div>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('courses.attendance.insights', $course) }}" x-data="{ busy: false }" x-on:submit="busy = true">
                                @csrf
                                <button x-bind:disabled="busy" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 text-sm rounded-md hover:bg-indigo-100 disabled:opacity-50">
                                    <span x-show="!busy">{{ $insights ? 'Refresh' : 'Generate insights' }}</span>
                                    <span x-show="busy" x-cloak>Analyzing…</span>
                                </button>
                            </form>
                        </div>

                        @if ($insights)
                            <div class="px-6 py-4 space-y-4">
                                <p class="text-sm text-gray-700">{{ $insights['summary'] }}</p>

                                @if (count($insights['flagged']))
                                    <ul class="divide-y divide-gray-100 border border-gray-100 rounded-md">
                                        @foreach ($insights['flagged'] as $flag)
                                            <li class="px-4 py-3 text-sm">
                                                <div class="font-medium text-gray-900">{{ $flag['name'] }}</div>
                                                <div class="text-gray-600">{{ $flag['concern'] }}</div>
                                                <div class="text-indigo-700 mt-1">Suggested: {{ $flag['suggested_action'] }}</div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-sm text-green-700">No students flagged.</p>
                                @endif

                                <p class="text-xs text-gray-400">AI-generated from the records below. Check the records before acting on it.</p>
                            </div>
                        @else
                            <div class="px-6 py-4 text-sm text-gray-500">
                                Get a summary of attendance in this course and a list of students who may need attention.
                            </div>
                        @endif
                    </div>
                @endif

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
