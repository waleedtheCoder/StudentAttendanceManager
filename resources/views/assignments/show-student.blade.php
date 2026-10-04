<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $assignment->title }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 text-sm rounded-md p-4">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-2">
                    {{ $assignment->course->code }} · Due {{ $assignment->due_date->format('M j, Y g:ia') }}
                </p>
                <p class="text-gray-700">{{ $assignment->description ?: 'No description provided.' }}</p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium text-gray-900 mb-4">Your Submission</h3>

                @if ($mySubmission)
                    <p class="text-sm text-gray-600">
                        Submitted {{ $mySubmission->submitted_at->format('M j, Y g:ia') }} —
                        <a href="{{ route('submissions.download', $mySubmission) }}" class="text-indigo-600 hover:underline">Download your file</a>
                    </p>
                    <p class="text-sm text-gray-600 mt-2">
                        Grade:
                        @if (! is_null($mySubmission->grade))
                            <span class="font-medium">{{ $mySubmission->grade }}/100</span>
                        @else
                            <span class="text-gray-400">Not graded yet</span>
                        @endif
                    </p>
                    @if ($mySubmission->feedback)
                        <p class="text-sm text-gray-600 mt-2">Feedback: {{ $mySubmission->feedback }}</p>
                    @endif

                    <p class="text-xs text-gray-400 mt-4">Submitting again will replace your current file.</p>
                @endif

                <form method="POST" action="{{ route('submissions.store', $assignment) }}" enctype="multipart/form-data" class="mt-4">
                    @csrf
                    <input type="file" name="file" required class="block text-sm">
                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                    <x-primary-button class="mt-4">{{ $mySubmission ? 'Resubmit' : 'Submit' }}</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
