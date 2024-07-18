<x-app-layout>

    <form method="POST"
        action="{{ $event->exists ? url('/update') : url('/' . $subdomain1 . '/store_event/' . $subdomain2) }}">

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

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('Details') }}
                        </h2>

                        @if($talent)
                        <div>{{ $talent->name }}</div>
                        @elseif($vendor)
                        <div>{{ $vendor->name }}</div>
                        @else

                        <fieldset>
                            <x-input-label for="role_type" :value="__('Type')" />
                            <div class="mt-2 mb-6 space-y-6 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                                <div class="flex items-center">
                                    <input id="talent" name="role_type" type="radio" value="talent"
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    <label for="talent"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900">Talent</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="vendor" name="role_type" type="radio" value="vendor"
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    <label for="vendor"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900">Vendor</label>
                                </div>
                            </div>
                        </fieldset>


                        <div class="mb-6">
                            <x-input-label for="role_name" :value="__('Name')" />
                            <x-text-input id="role_name" name="role_name" type="text" class="mt-1 block w-full"
                                :value="old('role_name')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('role_name')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="role_email" :value="__('Email')" />
                            <x-text-input id="role_email" name="role_email" type="email" class="mt-1 block w-full"
                                :value="old('role_email')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('role_email')" />
                        </div>

                        @endif

                    </div>
                </div>
            </div>
        </div>


        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Save') }}</x-primary-button>
            </div>
        </div>

    </form>

</x-app-layout>