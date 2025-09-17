<x-app-admin-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="max-w-3xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('messages.email_settings') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('messages.email_settings_description') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('settings.mail.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label for="mail_mailer" :value="__('messages.mail_mailer')" />
                                <select id="mail_mailer" name="mail_mailer"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                    @foreach($availableMailers as $value => $label)
                                        <option value="{{ $value }}" {{ old('mail_mailer', $mailSettings['mailer']) === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('mail_mailer')" />
                            </div>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="mail_host" :value="__('messages.mail_host')" />
                                    <x-text-input id="mail_host" name="mail_host" type="text" class="mt-1 block w-full"
                                        :value="old('mail_host', $mailSettings['host'])" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('mail_host')" />
                                </div>

                                <div>
                                    <x-input-label for="mail_port" :value="__('messages.mail_port')" />
                                    <x-text-input id="mail_port" name="mail_port" type="number" class="mt-1 block w-full"
                                        :value="old('mail_port', $mailSettings['port'])" min="1" max="65535" />
                                    <x-input-error class="mt-2" :messages="$errors->get('mail_port')" />
                                </div>
                            </div>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="mail_username" :value="__('messages.mail_username')" />
                                    <x-text-input id="mail_username" name="mail_username" type="text" class="mt-1 block w-full"
                                        :value="old('mail_username', $mailSettings['username'])" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('mail_username')" />
                                </div>

                                <div>
                                    <x-input-label for="mail_password" :value="__('messages.mail_password')" />
                                    <x-text-input id="mail_password" name="mail_password" type="password" class="mt-1 block w-full"
                                        :value="old('mail_password')" autocomplete="new-password" />
                                    <x-input-error class="mt-2" :messages="$errors->get('mail_password')" />
                                </div>
                            </div>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="mail_encryption" :value="__('messages.mail_encryption')" />
                                    <select id="mail_encryption" name="mail_encryption"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        @foreach($availableEncryptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('mail_encryption', $mailSettings['encryption'] ?? '') === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('mail_encryption')" />
                                </div>

                                <div>
                                    <x-input-label for="mail_from_name" :value="__('messages.mail_from_name')" />
                                    <x-text-input id="mail_from_name" name="mail_from_name" type="text" class="mt-1 block w-full"
                                        :value="old('mail_from_name', $mailSettings['from_name'])" />
                                    <x-input-error class="mt-2" :messages="$errors->get('mail_from_name')" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="mail_from_address" :value="__('messages.mail_from_address')" />
                                <x-text-input id="mail_from_address" name="mail_from_address" type="email" class="mt-1 block w-full"
                                    :value="old('mail_from_address', $mailSettings['from_address'])" autocomplete="off" />
                                <x-input-error class="mt-2" :messages="$errors->get('mail_from_address')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                                @if (session('status') === 'mail-settings-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.email_settings_saved') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout>
