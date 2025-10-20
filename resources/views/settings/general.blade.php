<x-app-admin-layout>
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
                                    :value="old('public_url', $generalSettings['public_url'])" autocomplete="off" />
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.public_url_help') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('public_url')" />
                            </div>

                            <div>
                                <x-input-label for="update_repository_url" :value="__('messages.update_repository_url')" />
                                <x-text-input id="update_repository_url" name="update_repository_url" type="url"
                                    class="mt-1 block w-full"
                                    :value="old('update_repository_url', $generalSettings['update_repository_url'])"
                                    autocomplete="off" />
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.update_repository_url_help') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('update_repository_url')" />
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
                                        :value="old('log_syslog_host', $generalSettings['log_syslog_host'])" autocomplete="off" />
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('messages.log_syslog_host_help') }}
                                    </p>
                                    <x-input-error class="mt-2" :messages="$errors->get('log_syslog_host')" />
                                </div>

                                <div>
                                    <x-input-label for="log_syslog_port" :value="__('messages.log_syslog_port')" />
                                    <x-text-input id="log_syslog_port" name="log_syslog_port" type="number" min="1"
                                        max="65535" class="mt-1 block w-full"
                                        :value="old('log_syslog_port', $generalSettings['log_syslog_port'])" autocomplete="off" />
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
                                            @selected(old('log_level', $generalSettings['log_level']) === $levelValue)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.log_level_help') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('log_level')" />
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
                        ])
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-admin-layout>
