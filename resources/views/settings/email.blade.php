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
                                {{ __('messages.email_settings') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('messages.email_settings_description') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('settings.mail.update') }}" class="mt-6 space-y-6"
                            x-ref="form"
                            x-data="{
                                testing: false,
                                result: null,
                                async sendTestEmail() {
                                    this.testing = true;
                                    this.result = null;

                                    const formData = new FormData(this.$refs.form);
                                    formData.delete('_method');

                                    try {
                                        const response = await fetch('{{ route('settings.mail.test', [], false) }}', {
                                            method: 'POST',
                                            headers: {
                                                'Accept': 'application/json',
                                                'X-Requested-With': 'XMLHttpRequest',
                                            },
                                            body: formData,
                                        });

                                        const data = await response.json().catch(() => ({}));

                                        const logs = Array.isArray(data.logs) ? data.logs : [];
                                        const failures = Array.isArray(data.failures) ? data.failures : [];

                                        if (response.ok && data.status === 'success') {
                                            this.result = {
                                                success: true,
                                                message: data.message || '{{ __('messages.test_email_sent') }}',
                                                logs,
                                                failures,
                                            };
                                        } else {
                                            this.result = {
                                                success: false,
                                                message: data.message || '{{ __('messages.test_email_failed') }}',
                                                error: data.error || null,
                                                logs,
                                                failures,
                                            };
                                        }
                                    } catch (error) {
                                        this.result = {
                                            success: false,
                                            message: '{{ __('messages.test_email_failed') }}',
                                            error: error.message,
                                            logs: [],
                                            failures: [],
                                        };
                                    } finally {
                                        this.testing = false;
                                    }
                                }
                            }"
                            x-on:submit="result = null">
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

                            <div class="flex flex-wrap items-center gap-4">
                                <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                                <x-secondary-button type="button" x-on:click="sendTestEmail" x-bind:disabled="testing">
                                    <span x-text="testing ? '{{ __('messages.sending_test_email') }}' : '{{ __('messages.send_test_email') }}'"
                                        class="whitespace-nowrap"></span>
                                </x-secondary-button>

                                @if (session('status') === 'mail-settings-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.email_settings_saved') }}</p>
                                @endif
                            </div>

                            <div x-cloak x-show="result" x-transition
                                :class="result?.success ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-700'"
                                class="rounded-md border px-4 py-3 text-sm">
                                <p class="font-medium" x-text="result?.message ?? ''"></p>
                                <template x-if="!result?.success && result?.error">
                                    <p class="mt-2"><span class="font-medium">{{ __('messages.error_details') }}</span>
                                        <span x-text="result.error"></span>
                                    </p>
                                </template>
                                <template x-if="result?.failures?.length">
                                    <div class="mt-2">
                                        <p class="font-medium">{{ __('messages.test_email_failed_recipients') }}</p>
                                        <ul class="mt-1 list-inside list-disc space-y-1">
                                            <template x-for="(failure, index) in result.failures" :key="index">
                                                <li class="font-mono" x-text="failure"></li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                                <template x-if="result?.logs?.length">
                                    <div class="mt-4 rounded-md border border-gray-200 bg-white p-3 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                        <p class="mb-2 font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-[11px]">
                                            {{ __('messages.test_email_logs') }}
                                        </p>
                                        <div class="space-y-1">
                                            <template x-for="(logLine, index) in result.logs" :key="index">
                                                <div class="font-mono" x-text="logLine"></div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout>
