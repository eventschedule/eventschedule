<x-app-layout>

    @vite([
    'resources/js/jquery-3.3.1.min.js',
    'resources/js/countrySelect.min.js',
    'resources/css/countrySelect.min.css',
    ])

    <x-slot name="head">

        <style>
        .country-select {
            width: 100%;
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            $("#country").countrySelect({
                defaultCountry: '{{ $role->country_code }}',
            });
        });

        function toggleAddress() {
            var type = $('input[name="type"]:checked').val();
            if (type == 'talent') {
                $('#address').fadeOut();
            } else {
                $('#address').fadeIn();
            }
        }

        function onCountryChange() {
            var selected = $('#country').countrySelect('getSelectedCountryData');
            $('#country_code').val(selected.iso2);
        }
        </script>

    </x-slot>

    <form method="POST" action="{{ $role->exists ? url($role->subdomain . '/update') : route('role.store') }}">

        @csrf
        @if($role->exists)
        @method('put')
        @endif

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('Details') }}
                        </h2>

                        @if(! $role->exists)
                        <fieldset>
                            <x-input-label for="type" :value="__('Type')" />
                            <div class="mt-2 mb-6 space-y-6 sm:flex sm:items-center sm:space-x-10 sm:space-y-0"
                                onclick="toggleAddress()">
                                <div class="flex items-center">
                                    <input id="venue" name="type" type="radio" value="venue" checked
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    <label for="venue"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900">Venue</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="talent" name="type" type="radio" value="talent"
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    <label for="talent"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900">Talent</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="vendor" name="type" type="radio" value="vendor"
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    <label for="vendor"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900">Vendor</label>
                                </div>
                            </div>
                        </fieldset>
                        @endif

                        <div class="mb-6">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $role->name)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $role->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('Contact Info') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                :value="old('email', $role->exists ? $role->email : auth()->user()->email)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="phone" :value="__('Phone')" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                :value="old('phone', $role->phone)" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="website" :value="__('Website')" />
                            <x-text-input id="website" name="website" type="url" class="mt-1 block w-full"
                                :value="old('website', $role->website)" />
                            <x-input-error class="mt-2" :messages="$errors->get('website')" />
                        </div>

                    </div>
                </div>


                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg" id="address">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('Address') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="address1" :value="__('Street Address')" />
                            <x-text-input id="address1" name="address1" type="text" class="mt-1 block w-full"
                                :value="old('address1', $role->address1)" />
                            <x-input-error class="mt-2" :messages="$errors->get('address1')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="city" :value="__('City')" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                                :value="old('city', $role->city)" />
                            <x-input-error class="mt-2" :messages="$errors->get('city')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="state" :value="__('State / Province')" />
                            <x-text-input id="state" name="state" type="text" class="mt-1 block w-full"
                                :value="old('state', $role->state)" />
                            <x-input-error class="mt-2" :messages="$errors->get('state')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="postal_code" :value="__('Postal Code')" />
                            <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full"
                                :value="old('postal_code', $role->postal_code)" />
                            <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="country" :value="__('Country')" />
                            <x-text-input id="country" name="country" type="text" class="mt-1 block w-full"
                                :value="old('country', $role->country)" onchange="onCountryChange()" />
                            <x-input-error class="mt-2" :messages="$errors->get('country')" />
                            <input type="hidden" id="country_code" name="country_code" />
                        </div>

                    </div>
                </div>



                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg" id="address">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('Settings') }}
                        </h2>

                        <div class="mb-6">
                            <x-checkbox name="accept_talent_requests" label="{{ __('Accept talent requests') }}"
                                checked="{{ old('accept_talent_requests', $role->accept_talent_requests) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('accept_talent_requests')" />
                        </div>

                        <div class="mb-6">
                            <x-checkbox name="accept_vendor_requests" label="{{ __('Accept vendor requests') }}"
                                checked="{{ old('accept_vendor_requests', $role->accept_vendor_requests) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('accept_vendor_requests')" />
                        </div>

                        <div class="mb-6">
                            <x-checkbox name="use_24_hour_time" label="{{ __('Use 24-hour time format') }}"
                                checked="{{ old('use_24_hour_time', $role->use_24_hour_time) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('use_24_hour_time')" />
                        </div>

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