<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $assignment->title }}</h2>
            @can('update', $assignment)
                <a href="{{ route('assignments.edit', $assignment) }}" class="text-sm text-indigo-600 hover:underline">Edit</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 text-sm rounded-md p-4">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-2">
                    {{ $assignment->course->code }} · Due {{ $assignment->due_date->format('M j, Y g:ia') }}
                </p>
                <p class="text-gray-700">{{ $assignment->description ?: 'No description provided.' }}</p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 font-medium text-gray-900">
                    Submissions ({{ $submissions->count() }})
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">File</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grade</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($submissions as $submission)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $submission->student->name }}</td>
                                <td class="px-6 py-3 text-sm text-gray-500">{{ $submission->submitted_at->format('M j, g:ia') }}</td>
                                <td class="px-6 py-3 text-sm">
                                    <a href="{{ route('submissions.download', $submission) }}" class="text-indigo-600 hover:underline">Download</a>
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    <form method="POST" action="{{ route('submissions.grade', $submission) }}" class="space-y-2"
                                          x-data="feedbackDraft(@js(route('submissions.feedback-draft', $submission)))">
                                        @csrf
                                        @method('PUT')
                                        <div class="flex items-center gap-2">
                                            <input type="number" name="grade" min="0" max="100" value="{{ $submission->grade }}" x-ref="grade"
                                                   class="w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="—" required>
                                            <button class="text-indigo-600 hover:underline text-sm">Save</button>
                                            @if ($aiEnabled)
                                                <button type="button" x-on:click="draft" x-bind:disabled="loading"
                                                        class="ml-auto px-2 py-1 bg-indigo-50 text-indigo-700 text-xs rounded-md hover:bg-indigo-100 disabled:opacity-50">
                                                    <span x-show="!loading">Draft with AI</span>
                                                    <span x-show="loading" x-cloak>Drafting…</span>
                                                </button>
                                            @endif
                                        </div>
                                        <textarea name="feedback" rows="2" x-ref="feedback" placeholder="Feedback for the student (optional)"
                                                  class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ $submission->feedback }}</textarea>
                                        <p x-show="error" x-text="error" x-cloak class="text-xs text-red-600"></p>
                                        <p x-show="drafted" x-cloak class="text-xs text-gray-500">AI draft filled in. Review and edit it, then click Save.</p>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-sm text-gray-500 text-center">No submissions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function feedbackDraft(url) {
            return {
                loading: false,
                error: '',
                drafted: false,
                async draft() {
                    this.loading = true;
                    this.error = '';
                    this.drafted = false;
                    try {
                        const { data } = await window.axios.post(url);
                        this.$refs.grade.value = data.grade;
                        this.$refs.feedback.value = data.feedback;
                        this.drafted = true;
                    } catch (e) {
                        this.error = e.response?.data?.message ?? 'Could not draft feedback. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>
