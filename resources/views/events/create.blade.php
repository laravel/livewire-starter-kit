<x-layouts.app :title="__('Create Event')">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Create New Event</h2>

            <form method="POST" action="{{ route('events.store') }}" class="space-y-6">
                @csrf

                <div>
                    <x-input-label for="title" :value="__('Event Title')" />
                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                </div>

                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <x-textarea id="description" name="description" class="mt-1 block w-full" rows="4" required>{{ old('description') }}</x-textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <div>
                    <x-input-label for="date" :value="__('Event Date')" />
                    <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('date')" />
                </div>

                <div>
                    <x-input-label for="time" :value="__('Event Time')" />
                    <x-text-input id="time" name="time" type="time" class="mt-1 block w-full" :value="old('time')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('time')" />
                </div>

                <div>
                    <x-input-label for="location" :value="__('Location')" />
                    <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" :value="old('location')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('location')" />
                </div>

                <div>
                    <x-input-label for="capacity" :value="__('Capacity')" />
                    <x-text-input id="capacity" name="capacity" type="number" class="mt-1 block w-full" :value="old('capacity')" required min="1" />
                    <x-input-error class="mt-2" :messages="$errors->get('capacity')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Create Event') }}</x-primary-button>
                    <a href="{{ route('events.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                        {{ __('Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
