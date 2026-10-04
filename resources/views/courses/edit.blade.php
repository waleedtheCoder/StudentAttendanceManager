<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Course') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('courses.update', $course) }}">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="code" value="Course Code" />
                        <x-text-input id="code" name="code" class="block mt-1 w-full" :value="old('code', $course->code)" required autofocus />
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="title" value="Title" />
                        <x-text-input id="title" name="title" class="block mt-1 w-full" :value="old('title', $course->title)" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="description" value="Description" />
                        <textarea id="description" name="description" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $course->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('courses.show', $course) }}" class="text-sm text-gray-600 hover:underline self-center">Cancel</a>
                        <x-primary-button>Save Changes</x-primary-button>
                    </div>
                </form>

                <form method="POST" action="{{ route('courses.destroy', $course) }}" class="mt-6 pt-6 border-t border-gray-200" onsubmit="return confirm('Delete this course? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>Delete Course</x-danger-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
