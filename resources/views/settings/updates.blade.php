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
                    <div class="mb-6 border-b border-gray-100 pb-4 dark:border-gray-800">
                        <x-breadcrumbs
                            :items="[
                                ['label' => __('messages.settings'), 'url' => route('settings.index')],
                                ['label' => __('messages.update_settings'), 'current' => true],
                            ]"
                            class="text-xs text-gray-500 dark:text-gray-400"
                        />
                        <h1 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ __('messages.update_settings') }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('messages.update_settings_description') }}
                        </p>
                    </div>

                    <section>

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
