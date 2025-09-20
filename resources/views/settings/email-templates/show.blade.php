<x-app-admin-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="max-w-3xl">
                    <div class="mb-6">
                        <a href="{{ route('settings.email_templates') }}" class="inline-flex items-center gap-2 text-sm font-medium text-[#4E81FA] hover:text-[#365fcc]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                            {{ __('messages.back_to_email_templates') }}
                        </a>
                    </div>

                    <section>
                        <header class="space-y-2">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ $template['label'] }}
                            </h2>

                            @if(!empty($template['description']))
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $template['description'] }}
                                </p>
                            @endif
                        </header>

                        <form method="post" action="{{ route('settings.mail_templates.update', ['template' => $template['key']]) }}" class="mt-6 space-y-6"
                            x-data="{
                                enabled: @json((bool) $template['enabled']),
                                curated: false,
                                testing: false,
                                testResult: null,
                                testUrl: '{{ route('settings.mail_templates.test', ['template' => $template['key']]) }}',
                                toggleEnabled() {
                                    this.enabled = !this.enabled;
                                },
                                async sendTest() {
                                    if (this.testing) {
                                        return;
                                    }

                                    this.testing = true;
                                    this.testResult = null;

                                    const formData = new FormData();
                                    formData.append('_token', '{{ csrf_token() }}');

                                    if (this.curated) {
                                        formData.append('curated', '1');
                                    }

                                    try {
                                        const response = await fetch(this.testUrl, {
                                            method: 'POST',
                                            headers: {
                                                'Accept': 'application/json',
                                                'X-Requested-With': 'XMLHttpRequest',
                                            },
                                            body: formData,
                                        });

                                        const data = await response.json().catch(() => ({}));
                                        const failures = Array.isArray(data.failures) ? data.failures : [];

                                        if (response.ok && data.status === 'success') {
                                            this.testResult = {
                                                success: true,
                                                message: data.message ?? '{{ __('messages.test_email_sent') }}',
                                                failures,
                                            };
                                        } else {
                                            this.testResult = {
                                                success: false,
                                                message: data.message ?? '{{ __('messages.test_email_failed') }}',
                                                error: data.error ?? null,
                                                failures,
                                            };
                                        }
                                    } catch (error) {
                                        this.testResult = {
                                            success: false,
                                            message: '{{ __('messages.test_email_failed') }}',
                                            error: error.message,
                                            failures: [],
                                        };
                                    } finally {
                                        this.testing = false;
                                    }
                                }
                            }">
                            @csrf
                            @method('patch')

                            <input type="hidden" name="enabled" x-bind:value="enabled ? '1' : '0'">

                            <div class="space-y-6">
                                <div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ __('messages.email_template_status') }}
                                    </span>

                                    <div class="mt-3 flex items-center gap-3">
                                        <button type="button" role="switch" x-on:click="toggleEnabled"
                                            :aria-checked="enabled"
                                            :class="enabled ? 'bg-[#4E81FA]' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2">
                                            <span aria-hidden="true"
                                                :class="enabled ? 'translate-x-5' : 'translate-x-1'"
                                                class="inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition"></span>
                                        </button>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="enabled ? '{{ __('messages.enabled') }}' : '{{ __('messages.disabled') }}'"></span>
                                    </div>

                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400" x-show="!enabled" x-cloak>
                                        {{ __('messages.email_template_disabled_message') }}
                                    </p>
                                </div>

                                @if(isset($template['subject']))
                                    <div>
                                        <x-input-label for="mail_template_subject" :value="__('messages.email_template_subject')" />
                                        <x-text-input id="mail_template_subject" name="subject" type="text" class="mt-1 block w-full"
                                            :value="old('subject', $template['subject'])" />
                                        <x-input-error class="mt-2" :messages="$errors->get('subject')" />
                                    </div>
                                @endif

                                @if(!empty($template['has_subject_curated']) && isset($template['subject_curated']))
                                    <div>
                                        <x-input-label for="mail_template_subject_curated" :value="__('messages.email_template_subject_curated')" />
                                        <x-text-input id="mail_template_subject_curated" name="subject_curated" type="text" class="mt-1 block w-full"
                                            :value="old('subject_curated', $template['subject_curated'])" />
                                        <x-input-error class="mt-2" :messages="$errors->get('subject_curated')" />
                                    </div>
                                @endif

                                @if(isset($template['body']))
                                    <div>
                                        <x-input-label for="mail_template_body" :value="__('messages.email_template_body')" />
                                        <textarea id="mail_template_body" name="body" rows="8"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">{{ old('body', $template['body']) }}</textarea>
                                        <x-input-error class="mt-2" :messages="$errors->get('body')" />
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            {{ __('messages.email_template_markdown_hint') }}
                                        </p>
                                    </div>
                                @endif

                                @if(!empty($template['has_body_curated']) && isset($template['body_curated']))
                                    <div>
                                        <x-input-label for="mail_template_body_curated" :value="__('messages.email_template_body') . ' (' . __('messages.curators') . ')'" />
                                        <textarea id="mail_template_body_curated" name="body_curated" rows="8"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">{{ old('body_curated', $template['body_curated']) }}</textarea>
                                        <x-input-error class="mt-2" :messages="$errors->get('body_curated')" />
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            {{ __('messages.email_template_markdown_hint') }}
                                        </p>
                                    </div>
                                @endif

                                @if(!empty($template['placeholders']))
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        <p class="font-medium">
                                            {{ __('messages.email_template_placeholders') }}
                                        </p>
                                        <ul class="mt-2 space-y-1 text-xs sm:text-sm">
                                            @foreach($template['placeholders'] as $placeholder => $description)
                                                <li>
                                                    <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-900 dark:text-gray-100 rounded">
                                                        {{ $placeholder }}
                                                    </code>
                                                    <span class="ml-2">{{ $description }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-4">
                                <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                                <x-secondary-button type="button" x-on:click="sendTest" x-bind:disabled="testing">
                                    <span x-text="testing ? '{{ __('messages.sending_test_email') }}' : '{{ __('messages.send_test_email') }}'" class="whitespace-nowrap"></span>
                                </x-secondary-button>

                                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <input type="checkbox" x-model="curated"
                                        class="rounded border-gray-300 text-[#4E81FA] shadow-sm focus:ring-[#4E81FA] dark:border-gray-600 dark:bg-gray-900" />
                                    <span>{{ __('messages.email_template_test_curated_label') }}</span>
                                </label>

                                @if (session('status') === 'mail-template-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.email_templates_saved') }}</p>
                                @endif
                            </div>

                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('messages.email_template_test_description') }}
                            </p>

                            <div x-cloak x-show="testResult" x-transition
                                :class="testResult?.success ? 'border-green-200 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-900/40 dark:text-green-200' : 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/40 dark:text-red-200'"
                                class="rounded-md border px-4 py-3 text-sm">
                                <p class="font-medium" x-text="testResult?.message ?? ''"></p>
                                <template x-if="!testResult?.success && testResult?.error">
                                    <p class="mt-2">
                                        <span class="font-medium">{{ __('messages.error_details') }}</span>
                                        <span x-text="testResult.error"></span>
                                    </p>
                                </template>
                                <template x-if="testResult?.failures?.length">
                                    <div class="mt-2">
                                        <p class="font-medium">{{ __('messages.test_email_failed_recipients') }}</p>
                                        <ul class="mt-1 list-inside list-disc space-y-1">
                                            <template x-for="(failure, index) in testResult.failures" :key="index">
                                                <li class="font-mono" x-text="failure"></li>
                                            </template>
                                        </ul>
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
