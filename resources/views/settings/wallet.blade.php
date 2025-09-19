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
                                {{ __('messages.wallet_settings') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('messages.wallet_settings_intro') }}
                            </p>
                        </header>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="max-w-3xl">
                    <section>
                        <header>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('messages.apple_wallet') }}
                            </h3>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('messages.apple_wallet_settings_description') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('settings.wallet.apple.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
                            @csrf
                            @method('patch')

                            <div class="space-y-6">
                                <div>
                                    <x-checkbox name="apple_enabled"
                                        label="{{ __('messages.apple_wallet_enabled') }}"
                                        checked="{{ old('apple_enabled', $appleSettings['enabled']) }}" />
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('messages.apple_wallet_enabled_help') }}
                                    </p>
                                    <x-input-error class="mt-2" :messages="$errors->get('apple_enabled')" />
                                </div>

                                <div>
                                    <x-input-label for="apple_pass_type_identifier" :value="__('messages.apple_wallet_pass_type_identifier')" />
                                    <x-text-input id="apple_pass_type_identifier" name="apple_pass_type_identifier" type="text" class="mt-1 block w-full"
                                        :value="old('apple_pass_type_identifier', $appleSettings['pass_type_identifier'])" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('apple_pass_type_identifier')" />
                                </div>

                                <div>
                                    <x-input-label for="apple_team_identifier" :value="__('messages.apple_wallet_team_identifier')" />
                                    <x-text-input id="apple_team_identifier" name="apple_team_identifier" type="text" class="mt-1 block w-full"
                                        :value="old('apple_team_identifier', $appleSettings['team_identifier'])" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('apple_team_identifier')" />
                                </div>

                                <div>
                                    <x-input-label for="apple_organization_name" :value="__('messages.apple_wallet_organization_name')" />
                                    <x-text-input id="apple_organization_name" name="apple_organization_name" type="text" class="mt-1 block w-full"
                                        :value="old('apple_organization_name', $appleSettings['organization_name'])" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('apple_organization_name')" />
                                </div>

                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <x-input-label for="apple_background_color" :value="__('messages.apple_wallet_background_color')" />
                                        <x-text-input id="apple_background_color" name="apple_background_color" type="text" class="mt-1 block w-full"
                                            :value="old('apple_background_color', $appleSettings['background_color'])" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('apple_background_color')" />
                                    </div>
                                    <div>
                                        <x-input-label for="apple_foreground_color" :value="__('messages.apple_wallet_foreground_color')" />
                                        <x-text-input id="apple_foreground_color" name="apple_foreground_color" type="text" class="mt-1 block w-full"
                                            :value="old('apple_foreground_color', $appleSettings['foreground_color'])" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('apple_foreground_color')" />
                                    </div>
                                    <div>
                                        <x-input-label for="apple_label_color" :value="__('messages.apple_wallet_label_color')" />
                                        <x-text-input id="apple_label_color" name="apple_label_color" type="text" class="mt-1 block w-full"
                                            :value="old('apple_label_color', $appleSettings['label_color'])" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('apple_label_color')" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="apple_certificate" :value="__('messages.apple_wallet_certificate')" />
                                    <input id="apple_certificate" name="apple_certificate" type="file" accept=".p12,.pfx"
                                        class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:rounded-md file:border-0 file:bg-[#4E81FA] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#365fcc] dark:file:bg-[#4E81FA] dark:hover:file:bg-[#365fcc]" />
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('messages.apple_wallet_certificate_help') }}
                                    </p>
                                    @if ($appleFiles['certificate']['resolved_path'])
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 break-all">
                                            {{ __('messages.current_file') }}
                                            <span class="font-medium">{{ $appleFiles['certificate']['display_name'] ?? __('messages.unknown_file') }}</span>
                                            <br>{{ $appleFiles['certificate']['resolved_path'] }}
                                            @if (! $appleFiles['certificate']['exists'])
                                                <br><span class="text-red-600 dark:text-red-400">{{ __('messages.file_missing') }}</span>
                                            @endif
                                            @if ($appleFiles['certificate']['source'] === 'environment')
                                                <br>{{ __('messages.configured_via_environment') }}
                                            @endif
                                        </p>
                                    @endif
                                    @if ($appleFiles['certificate']['source'] === 'settings')
                                        <div class="mt-2">
                                            <x-checkbox name="apple_remove_certificate" label="{{ __('messages.remove_uploaded_file') }}"
                                                checked="{{ old('apple_remove_certificate') }}" />
                                        </div>
                                    @endif
                                    <x-input-error class="mt-2" :messages="$errors->get('apple_certificate')" />
                                </div>

                                <div>
                                    <x-input-label for="apple_certificate_password" :value="__('messages.apple_wallet_certificate_password')" />
                                    <x-text-input id="apple_certificate_password" name="apple_certificate_password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('messages.apple_wallet_certificate_password_help') }}
                                    </p>
                                    @if ($applePasswordStored)
                                        <div class="mt-2">
                                            <x-checkbox name="apple_clear_certificate_password" label="{{ __('messages.clear_saved_value') }}"
                                                checked="{{ old('apple_clear_certificate_password') }}" />
                                        </div>
                                    @endif
                                    <x-input-error class="mt-2" :messages="$errors->get('apple_certificate_password')" />
                                </div>

                                <div>
                                    <x-input-label for="apple_wwdr_certificate" :value="__('messages.apple_wallet_wwdr_certificate')" />
                                    <input id="apple_wwdr_certificate" name="apple_wwdr_certificate" type="file" accept=".cer,.pem,.der"
                                        class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:rounded-md file:border-0 file:bg-[#4E81FA] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#365fcc] dark:file:bg-[#4E81FA] dark:hover:file:bg-[#365fcc]" />
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('messages.apple_wallet_wwdr_certificate_help') }}
                                    </p>
                                    @if ($appleFiles['wwdr']['resolved_path'])
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 break-all">
                                            {{ __('messages.current_file') }}
                                            <span class="font-medium">{{ $appleFiles['wwdr']['display_name'] ?? __('messages.unknown_file') }}</span>
                                            <br>{{ $appleFiles['wwdr']['resolved_path'] }}
                                            @if (! $appleFiles['wwdr']['exists'])
                                                <br><span class="text-red-600 dark:text-red-400">{{ __('messages.file_missing') }}</span>
                                            @endif
                                            @if ($appleFiles['wwdr']['source'] === 'environment')
                                                <br>{{ __('messages.configured_via_environment') }}
                                            @endif
                                        </p>
                                    @endif
                                    @if ($appleFiles['wwdr']['source'] === 'settings')
                                        <div class="mt-2">
                                            <x-checkbox name="apple_remove_wwdr_certificate" label="{{ __('messages.remove_uploaded_file') }}"
                                                checked="{{ old('apple_remove_wwdr_certificate') }}" />
                                        </div>
                                    @endif
                                    <x-input-error class="mt-2" :messages="$errors->get('apple_wwdr_certificate')" />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                                @if (session('status') === 'apple-wallet-settings-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.wallet_settings_saved') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="max-w-3xl">
                    <section>
                        <header>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('messages.google_wallet') }}
                            </h3>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('messages.google_wallet_settings_description') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('settings.wallet.google.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
                            @csrf
                            @method('patch')

                            <div class="space-y-6">
                                <div>
                                    <x-checkbox name="google_enabled"
                                        label="{{ __('messages.google_wallet_enabled') }}"
                                        checked="{{ old('google_enabled', $googleSettings['enabled']) }}" />
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('messages.google_wallet_enabled_help') }}
                                    </p>
                                    <x-input-error class="mt-2" :messages="$errors->get('google_enabled')" />
                                </div>

                                <div>
                                    <x-input-label for="google_issuer_id" :value="__('messages.google_wallet_issuer_id')" />
                                    <x-text-input id="google_issuer_id" name="google_issuer_id" type="text" class="mt-1 block w-full"
                                        :value="old('google_issuer_id', $googleSettings['issuer_id'])" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('google_issuer_id')" />
                                </div>

                                <div>
                                    <x-input-label for="google_issuer_name" :value="__('messages.google_wallet_issuer_name')" />
                                    <x-text-input id="google_issuer_name" name="google_issuer_name" type="text" class="mt-1 block w-full"
                                        :value="old('google_issuer_name', $googleSettings['issuer_name'])" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('google_issuer_name')" />
                                </div>

                                <div>
                                    <x-input-label for="google_class_suffix" :value="__('messages.google_wallet_class_suffix')" />
                                    <x-text-input id="google_class_suffix" name="google_class_suffix" type="text" class="mt-1 block w-full"
                                        :value="old('google_class_suffix', $googleSettings['class_suffix'])" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('google_class_suffix')" />
                                </div>

                                <div>
                                    <x-input-label for="google_service_account_file" :value="__('messages.google_wallet_service_account_file')" />
                                    <input id="google_service_account_file" name="google_service_account_file" type="file" accept=".json"
                                        class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:rounded-md file:border-0 file:bg-[#4E81FA] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#365fcc] dark:file:bg-[#4E81FA] dark:hover:file:bg-[#365fcc]" />
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('messages.google_wallet_service_account_file_help') }}
                                    </p>
                                    @if ($googleFiles['service_account']['resolved_path'])
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 break-all">
                                            {{ __('messages.current_file') }}
                                            <span class="font-medium">{{ $googleFiles['service_account']['display_name'] ?? __('messages.unknown_file') }}</span>
                                            <br>{{ $googleFiles['service_account']['resolved_path'] }}
                                            @if (! $googleFiles['service_account']['exists'])
                                                <br><span class="text-red-600 dark:text-red-400">{{ __('messages.file_missing') }}</span>
                                            @endif
                                            @if ($googleFiles['service_account']['source'] === 'environment')
                                                <br>{{ __('messages.configured_via_environment') }}
                                            @endif
                                        </p>
                                    @endif
                                    @if ($googleFiles['service_account']['source'] === 'settings')
                                        <div class="mt-2">
                                            <x-checkbox name="google_remove_service_account_file" label="{{ __('messages.remove_uploaded_file') }}"
                                                checked="{{ old('google_remove_service_account_file') }}" />
                                        </div>
                                    @endif
                                    <x-input-error class="mt-2" :messages="$errors->get('google_service_account_file')" />
                                </div>

                                <div>
                                    <x-input-label for="google_service_account_json" :value="__('messages.google_wallet_service_account_json')" />
                                    <textarea id="google_service_account_json" name="google_service_account_json" rows="5"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA]">{{ old('google_service_account_json') }}</textarea>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('messages.google_wallet_service_account_json_help') }}
                                    </p>
                                    @if ($googleInlineStatus['stored'])
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.google_wallet_service_account_json_stored') }}
                                        </p>
                                        <div class="mt-2">
                                            <x-checkbox name="google_clear_service_account_json" label="{{ __('messages.clear_saved_value') }}"
                                                checked="{{ old('google_clear_service_account_json') }}" />
                                        </div>
                                    @elseif ($googleInlineStatus['configured'])
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.configured_via_environment') }}
                                        </p>
                                    @endif
                                    <x-input-error class="mt-2" :messages="$errors->get('google_service_account_json')" />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                                @if (session('status') === 'google-wallet-settings-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.wallet_settings_saved') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout>
