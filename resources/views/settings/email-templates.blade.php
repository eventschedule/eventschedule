<x-app-admin-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="max-w-3xl">
                    <div class="mb-6 border-b border-gray-100 pb-4 dark:border-gray-800">
                        <x-breadcrumbs
                            :items="[
                                ['label' => __('messages.settings'), 'url' => route('settings.index')],
                                ['label' => __('messages.email_templates'), 'current' => true],
                            ]"
                            class="text-xs text-gray-500 dark:text-gray-400"
                        />
                        <p class="text-sm font-medium text-indigo-600">{{ __('messages.settings') }}</p>
                        <h1 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ __('messages.email_templates') }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('messages.email_templates_description') }}
                        </p>
                    </div>

                    <section>

                        <div class="mt-6 space-y-4">
                            @foreach($mailTemplates as $template)
                                <a href="{{ route('settings.email_templates.show', ['template' => $template['key']]) }}"
                                    class="block rounded-lg border border-gray-200 bg-white px-4 py-4 transition hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $template['label'] }}
                                            </h3>

                                            @if(!empty($template['description']))
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $template['description'] }}
                                                </p>
                                            @endif
                                        </div>

                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $template['enabled'] ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' }}">
                                            {{ $template['enabled'] ? __('messages.enabled') : __('messages.disabled') }}
                                        </span>
                                    </div>

                                    <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                        <span>{{ __('messages.email_template_status') }}: {{ $template['enabled'] ? __('messages.enabled') : __('messages.disabled') }}</span>
                                        <span class="inline-flex items-center gap-1 text-[#4E81FA]">
                                            <span>{{ __('messages.preview') }}</span>
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout>
