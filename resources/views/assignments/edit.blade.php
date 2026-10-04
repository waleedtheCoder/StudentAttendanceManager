<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Assignment</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('assignments.update', $assignment) }}">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="title" value="Title" />
                        <x-text-input id="title" name="title" class="block mt-1 w-full" :value="old('title', $assignment->title)" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="description" value="Description" />
                        <textarea id="description" name="description" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $assignment->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="due_date" value="Due Date" />
                        <input type="datetime-local" id="due_date" name="due_date" value="{{ old('due_date', $assignment->due_date->format('Y-m-d\TH:i')) }}"
                               class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required />
                        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('assignments.show', $assignment) }}" class="text-sm text-gray-600 hover:underline self-center">Cancel</a>
                        <x-primary-button>Save Changes</x-primary-button>
                    </div>
                </form>

                <form method="POST" action="{{ route('assignments.destroy', $assignment) }}" class="mt-6 pt-6 border-t border-gray-200" onsubmit="return confirm('Delete this assignment?');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>Delete Assignment</x-danger-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
