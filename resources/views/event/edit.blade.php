<x-app-layout>

    <form method="POST" action="{{ $event->exists ? url('/update') : route('role.store') }}">

        @csrf
        @if($event->exists)
        @method('put')
        @endif

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('Venue') }}
                        </h2>

                        @if($venue)
                        <div>{{ $venue->name }}</div>
                        @else
                        <div class="mb-6">
                            <x-input-label for="venue_name" :value="__('Name')" />
                            <x-text-input id="venue_name" name="venue_name" type="text" class="mt-1 block w-full"
                                :value="old('venue_name')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('venue_name')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="venue_email" :value="__('Email')" />
                            <x-text-input id="venue_email" name="venue_email" type="email" class="mt-1 block w-full"
                                :value="old('venue_email')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('venue_name')" />
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

    </form>

</x-app-layout>