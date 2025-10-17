<x-app-admin-layout>
    <div class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h1>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Create a simple page so automated browser tests can exercise the role flows.') }}
            </p>

            <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <form method="POST" action="{{ route('role.store') }}">
                    @csrf

                    <input type="hidden" name="type" value="{{ $role->type }}">
                    <input type="hidden" name="timezone" value="{{ $role->timezone }}">
                    <input type="hidden" name="language_code" value="{{ $role->language_code }}">
                    <input type="hidden" name="background" value="{{ $role->background }}">
                    <input type="hidden" name="background_colors" value="{{ $role->background_colors }}">
                    <input type="hidden" name="background_color" value="{{ $role->background_color }}">
                    <input type="hidden" name="background_image" value="{{ $role->background_image }}">
                    <input type="hidden" name="accent_color" value="{{ $role->accent_color }}">
                    <input type="hidden" name="font_color" value="{{ $role->font_color }}">
                    <input type="hidden" name="font_family" value="{{ $role->font_family }}">
                    <input type="hidden" name="country_code" value="{{ $role->country_code ?? 'us' }}">
                    <input type="hidden" name="use_24_hour_time" value="0">
                    <input type="hidden" name="custom_color1" value="#1A2980">
                    <input type="hidden" name="custom_color2" value="#26D0CE">
                    <input type="hidden" name="email" value="{{ $user->email ?? $role->email }}">

                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Name') }}
                            </label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name', $role->name) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                required
                            >
                        </div>

                        <div>
                            <label for="website" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Website') }}
                            </label>
                            <input
                                id="website"
                                name="website"
                                type="url"
                                value="{{ old('website') }}"
                                placeholder="https://example.com"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            >
                        </div>

                        <div>
                            <label for="address1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Address Line 1') }}
                            </label>
                            <input
                                id="address1"
                                name="address1"
                                type="text"
                                value="{{ old('address1') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            >
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('City') }}
                                </label>
                                <input
                                    id="city"
                                    name="city"
                                    type="text"
                                    value="{{ old('city') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                >
                            </div>

                            <div>
                                <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('State / Region') }}
                                </label>
                                <input
                                    id="state"
                                    name="state"
                                    type="text"
                                    value="{{ old('state') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                >
                            </div>

                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Postal Code') }}
                                </label>
                                <input
                                    id="postal_code"
                                    name="postal_code"
                                    type="text"
                                    value="{{ old('postal_code') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Description') }}
                            </label>
                            <textarea
                                id="description"
                                name="description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            >{{ old('description') }}</textarea>
                        </div>

                        <div class="flex items-center space-x-3">
                            <input
                                id="accept_requests"
                                name="accept_requests"
                                type="checkbox"
                                value="1"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                {{ old('accept_requests') ? 'checked' : '' }}
                            >
                            <label for="accept_requests" class="text-sm text-gray-700 dark:text-gray-300">
                                {{ __('Allow booking requests') }}
                            </label>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                            >
                                {{ __('SAVE') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-admin-layout>
