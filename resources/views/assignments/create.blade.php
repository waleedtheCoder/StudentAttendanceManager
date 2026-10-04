<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            New Assignment — {{ $course->code }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('assignments.store', $course) }}">
                    @csrf

                    <div>
                        <x-input-label for="title" value="Title" />
                        <x-text-input id="title" name="title" class="block mt-1 w-full" :value="old('title')" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="description" value="Description" />
                        <textarea id="description" name="description" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="due_date" value="Due Date" />
                        <input type="datetime-local" id="due_date" name="due_date" value="{{ old('due_date') }}"
                               class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required />
                        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-primary-button>Create Assignment</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
