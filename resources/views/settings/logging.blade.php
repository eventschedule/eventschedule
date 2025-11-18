<x-app-admin-layout>
    @php
        $loggingSettings = $loggingSettings ?? [];

        if ($loggingSettings instanceof \Illuminate\Support\Collection) {
            $loggingSettings = $loggingSettings->toArray();
        } elseif (is_object($loggingSettings)) {
            $loggingSettings = (array) $loggingSettings;
        }

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
    @endphp
    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="max-w-3xl">
                    <div class="mb-6 border-b border-gray-100 pb-4 dark:border-gray-800">
                        <x-breadcrumbs
                            :items="[
                                ['label' => __('messages.settings'), 'url' => route('settings.index')],
                                ['label' => __('messages.logging_settings'), 'current' => true],
                            ]"
                            class="text-xs text-gray-500 dark:text-gray-400"
                        />
                        <h1 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ __('messages.logging_settings') }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('messages.logging_settings_description') }}
                        </p>
                    </div>

                    <section>

                        <form method="post" action="{{ route('settings.logging.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="log_syslog_host" :value="__('messages.log_syslog_host')" />
                                    <x-text-input id="log_syslog_host" name="log_syslog_host" type="text"
                                        class="mt-1 block w-full"
                                        :value="old('log_syslog_host', data_get($loggingSettings, 'log_syslog_host'))" autocomplete="off" />
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('messages.log_syslog_host_help') }}
                                    </p>
                                    <x-input-error class="mt-2" :messages="$errors->get('log_syslog_host')" />
                                </div>

                                <div>
                                    <x-input-label for="log_syslog_port" :value="__('messages.log_syslog_port')" />
                                    <x-text-input id="log_syslog_port" name="log_syslog_port" type="number" min="1"
                                        max="65535" class="mt-1 block w-full"
                                        :value="old('log_syslog_port', data_get($loggingSettings, 'log_syslog_port'))" autocomplete="off" />
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
                                            @selected(old('log_level', data_get($loggingSettings, 'log_level')) === $levelValue)>
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
                                    :checked="old('log_disabled', data_get($loggingSettings, 'log_disabled'))" />
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.log_disabled_help') }}
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                                @if (session('status') === 'logging-settings-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.logging_settings_saved') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout>
