<x-app-admin-layout>
    @php
        $updateSettings = $updateSettings ?? [];

        if ($updateSettings instanceof \Illuminate\Support\Collection) {
            $updateSettings = $updateSettings->toArray();
        } elseif (is_object($updateSettings)) {
            $updateSettings = (array) $updateSettings;
        }

        $availableUpdateChannels = $availableUpdateChannels ?? [];

        if ($availableUpdateChannels instanceof \Illuminate\Support\Collection) {
            $availableUpdateChannels = $availableUpdateChannels->toArray();
        } elseif (is_object($availableUpdateChannels)) {
            $availableUpdateChannels = (array) $availableUpdateChannels;
        }

        $availableUpdateChannels = collect($availableUpdateChannels)
            ->mapWithKeys(function ($option, $key) {
                if (is_array($option) || is_object($option)) {
                    $value = data_get($option, 'value', is_string($key) ? $key : null);
                    $label = data_get($option, 'label', $value);
                } else {
                    $value = is_string($key) ? $key : $option;
                    $label = $option;
                }

                if (! is_string($value)) {
                    $value = is_scalar($value) ? (string) $value : null;
                }

                if (! is_string($label)) {
                    $label = is_scalar($label) ? (string) $label : $value;
                }

                if ($value === null || $value === '') {
                    return [];
                }

                return [$value => $label];
            })
            ->filter()
            ->toArray();

        $versionInstalled = $versionInstalled ?? null;
        $versionAvailable = $versionAvailable ?? null;
        $selectedUpdateChannel = $selectedUpdateChannel ?? null;
    @endphp
    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="max-w-3xl">
                    <div class="mb-6">
                        <a href="{{ route('settings.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-[#4E81FA] hover:text-[#365fcc]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                            {{ __('messages.back_to_settings') }}
                        </a>
                    </div>

                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('messages.update_settings') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('messages.update_settings_description') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('settings.updates.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label for="update_repository_url" :value="__('messages.update_repository_url')" />
                                <x-text-input id="update_repository_url" name="update_repository_url" type="url"
                                    class="mt-1 block w-full"
                                    :value="old('update_repository_url', data_get($updateSettings, 'update_repository_url'))"
                                    autocomplete="off" />
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.update_repository_url_help') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('update_repository_url')" />
                            </div>

                            <div>
                                <x-input-label for="update_release_channel" :value="__('messages.update_channel')" />
                                <select id="update_release_channel" name="update_release_channel"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA]">
                                    @foreach ($availableUpdateChannels as $value => $label)
                                        <option value="{{ $value }}"
                                            @selected(old('update_release_channel', data_get($updateSettings, 'update_release_channel')) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.default_update_channel_help') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('update_release_channel')" />
                            </div>

                            <div>
                                <x-checkbox name="url_utils_verify_ssl"
                                    label="{{ __('messages.verify_download_ssl') }}"
                                    :checked="old('url_utils_verify_ssl', data_get($updateSettings, 'url_utils_verify_ssl'))" />
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.verify_download_ssl_help') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('url_utils_verify_ssl')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                                @if (session('status') === 'update-settings-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.update_settings_saved') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            @if (! config('app.hosted') && ! config('app.testing'))
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-3xl">
                        @include('profile.partials.update-app-form', [
                            'version_installed' => $versionInstalled,
                            'version_available' => $versionAvailable,
                            'availableChannels' => $availableUpdateChannels,
                            'selectedChannel' => $selectedUpdateChannel,
                        ])
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-admin-layout>
