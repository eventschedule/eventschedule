<x-app-admin-layout>
    @php
        $generalSettings = $generalSettings ?? [];

        if ($generalSettings instanceof \Illuminate\Support\Collection) {
            $generalSettings = $generalSettings->toArray();
        } elseif (is_object($generalSettings)) {
            $generalSettings = (array) $generalSettings;
        } elseif (! is_array($generalSettings)) {
            $generalSettings = [];
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

        $availableLogLevels = $availableLogLevels ?? [];

        if ($availableLogLevels instanceof \Illuminate\Support\Collection) {
            $availableLogLevels = $availableLogLevels->toArray();
        } elseif (is_object($availableLogLevels)) {
            $availableLogLevels = (array) $availableLogLevels;
        }

        $availableLogLevels = collect($availableLogLevels)
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

        $selectedUpdateChannel = $selectedUpdateChannel ?? null;
        $versionInstalled = $versionInstalled ?? null;
        $versionAvailable = $versionAvailable ?? null;
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
                                {{ __('messages.general_settings') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('messages.general_settings_description') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('settings.general.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label for="public_url" :value="__('messages.public_url')" />
                                <x-text-input id="public_url" name="public_url" type="url" class="mt-1 block w-full"
                                    :value="old('public_url', data_get($generalSettings, 'public_url'))" autocomplete="off" />
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.public_url_help') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('public_url')" />
                            </div>

                            <div>
                                <x-input-label for="update_repository_url" :value="__('messages.update_repository_url')" />
                                <x-text-input id="update_repository_url" name="update_repository_url" type="url"
                                    class="mt-1 block w-full"
                                    :value="old('update_repository_url', data_get($generalSettings, 'update_repository_url'))"
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
                                            @selected(old('update_release_channel', data_get($generalSettings, 'update_release_channel')) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.default_update_channel_help') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('update_release_channel')" />
                            </div>

                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('messages.logging_settings') }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.logging_settings_description') }}
                                </p>
                            </div>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="log_syslog_host" :value="__('messages.log_syslog_host')" />
                                    <x-text-input id="log_syslog_host" name="log_syslog_host" type="text"
                                        class="mt-1 block w-full"
                                        :value="old('log_syslog_host', data_get($generalSettings, 'log_syslog_host'))" autocomplete="off" />
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('messages.log_syslog_host_help') }}
                                    </p>
                                    <x-input-error class="mt-2" :messages="$errors->get('log_syslog_host')" />
                                </div>

                                <div>
                                    <x-input-label for="log_syslog_port" :value="__('messages.log_syslog_port')" />
                                    <x-text-input id="log_syslog_port" name="log_syslog_port" type="number" min="1"
                                        max="65535" class="mt-1 block w-full"
                                        :value="old('log_syslog_port', data_get($generalSettings, 'log_syslog_port'))" autocomplete="off" />
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('messages.log_syslog_port_help') }}
                                    </p>
                                    <x-input-error class="mt-2" :messages="$errors->get('log_syslog_port')" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="log_level" :value="__('messages.log_level')" />
                                <select id="log_level" name="log_level"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA]">
                                    @foreach ($availableLogLevels as $levelValue => $label)
                                        <option value="{{ $levelValue }}"
                                            @selected(old('log_level', data_get($generalSettings, 'log_level')) === $levelValue)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.log_level_help') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('log_level')" />
                            </div>

                            <div>
                                <x-checkbox name="log_disabled" label="{{ __('messages.log_disabled') }}"
                                    :checked="old('log_disabled', data_get($generalSettings, 'log_disabled'))" />
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.log_disabled_help') }}
                                </p>
                            </div>

                            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('messages.branding_settings_heading') }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.branding_settings_description') }}
                                </p>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                    {{ __('messages.branding_powered_by_notice') }}
                                </p>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <x-input-label for="branding_logo" :value="__('messages.branding_logo_label')" />
                                    <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center">
                                        @if (data_get($brandingSettings, 'logo_url'))
                                            <img src="{{ data_get($brandingSettings, 'logo_url') }}"
                                                alt="{{ data_get($brandingSettings, 'logo_alt') }}"
                                                class="h-16 w-auto rounded-md border border-gray-200 bg-white p-2 shadow-sm dark:border-gray-700 dark:bg-gray-900" />
                                        @else
                                            <div class="flex h-16 w-16 items-center justify-center rounded-md border border-dashed border-gray-300 text-sm text-gray-400 dark:border-gray-600 dark:text-gray-500">
                                                {{ __('messages.none') }}
                                            </div>
                                        @endif

                                        <div class="flex-1">
                                            <input type="file" id="branding_logo" name="branding_logo"
                                                accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                                class="block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:rounded-md file:border-0 file:bg-[#4E81FA] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#365fcc] dark:file:bg-[#4E81FA] dark:hover:file:bg-[#365fcc]" />
                                            <x-input-error class="mt-2" :messages="$errors->get('branding_logo')" />
                                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                {{ __('messages.branding_logo_help') }}
                                            </p>
                                        </div>
                                    </div>

                                    <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="branding_remove_logo" value="1"
                                            @checked(old('branding_remove_logo'))
                                            class="h-4 w-4 rounded border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]" />
                                        {{ __('messages.branding_logo_remove') }}
                                    </label>
                                </div>

                                <div>
                                    <x-input-label for="branding_logo_alt" :value="__('messages.branding_logo_alt_label')" />
                                    <x-text-input id="branding_logo_alt" name="branding_logo_alt" type="text"
                                        class="mt-1 block w-full"
                                        :value="old('branding_logo_alt', data_get($brandingSettings, 'logo_alt'))" />
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('messages.branding_logo_alt_help') }}
                                    </p>
                                    <x-input-error class="mt-2" :messages="$errors->get('branding_logo_alt')" />
                                </div>

                                <div>
                                    <x-input-label :value="__('messages.branding_color_help')" />
                                    <div class="mt-3 grid gap-4 sm:grid-cols-3">
                                        <div>
                                            <label for="branding_primary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ __('messages.branding_primary_color') }}
                                            </label>
                                            <input type="color" id="branding_primary_color" name="branding_primary_color"
                                                class="mt-2 h-10 w-full cursor-pointer rounded-md border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"
                                                value="{{ old('branding_primary_color', data_get($brandingSettings, 'primary_color')) }}" />
                                            <x-input-error class="mt-2" :messages="$errors->get('branding_primary_color')" />
                                        </div>
                                        <div>
                                            <label for="branding_secondary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ __('messages.branding_secondary_color') }}
                                            </label>
                                            <input type="color" id="branding_secondary_color" name="branding_secondary_color"
                                                class="mt-2 h-10 w-full cursor-pointer rounded-md border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"
                                                value="{{ old('branding_secondary_color', data_get($brandingSettings, 'secondary_color')) }}" />
                                            <x-input-error class="mt-2" :messages="$errors->get('branding_secondary_color')" />
                                        </div>
                                        <div>
                                            <label for="branding_tertiary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ __('messages.branding_tertiary_color') }}
                                            </label>
                                            <input type="color" id="branding_tertiary_color" name="branding_tertiary_color"
                                                class="mt-2 h-10 w-full cursor-pointer rounded-md border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"
                                                value="{{ old('branding_tertiary_color', data_get($brandingSettings, 'tertiary_color')) }}" />
                                            <x-input-error class="mt-2" :messages="$errors->get('branding_tertiary_color')" />
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="branding_default_language" :value="__('messages.branding_default_language')" />
                                    <select id="branding_default_language" name="branding_default_language"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA]">
                                        @foreach ($languageOptions as $code => $label)
                                            <option value="{{ $code }}"
                                                @selected(old('branding_default_language', data_get($brandingSettings, 'default_language')) === $code)>
                                                {{ strtoupper($code) }} — {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('messages.branding_default_language_help') }}
                                    </p>
                                    <x-input-error class="mt-2" :messages="$errors->get('branding_default_language')" />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                                @if (session('status') === 'general-settings-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.general_settings_saved') }}</p>
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
