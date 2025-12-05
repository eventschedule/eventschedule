<x-app-admin-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="max-w-3xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('messages.settings') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('messages.settings_overview_description') }}
                            </p>
                        </header>

                        <div class="mt-6 grid gap-6 sm:grid-cols-2">
                            <a href="{{ route('settings.branding') }}"
                               class="group flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-6 transition hover:border-[#4E81FA] hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:hover:border-[#4E81FA]">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-[#4E81FA] dark:text-gray-100">
                                            {{ __('messages.branding_settings_heading') }}
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.branding_settings_description') }}
                                        </p>
                                    </div>
                                    <span class="text-gray-300 transition group-hover:text-[#4E81FA] dark:text-gray-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </span>
                                </div>
                                <span class="mt-auto text-sm font-medium text-[#4E81FA]">
                                    {{ __('messages.view_details') }}
                                </span>
                            </a>

                            <a href="{{ route('settings.email') }}"
                               class="group flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-6 transition hover:border-[#4E81FA] hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:hover:border-[#4E81FA]">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-[#4E81FA] dark:text-gray-100">
                                            {{ __('messages.email_settings') }}
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.email_settings_combined_description') }}
                                        </p>
                                    </div>
                                    <span class="text-gray-300 transition group-hover:text-[#4E81FA] dark:text-gray-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </span>
                                </div>
                                <span class="mt-auto text-sm font-medium text-[#4E81FA]">
                                    {{ __('messages.view_details') }}
                                </span>
                            </a>

                            <a href="{{ route('settings.event_types.index') }}"
                               class="group flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-6 transition hover:border-[#4E81FA] hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:hover:border-[#4E81FA]">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-[#4E81FA] dark:text-gray-100">
                                            {{ __('messages.event_type_settings') }}
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.event_type_settings_short_description') }}
                                        </p>
                                    </div>
                                    <span class="text-gray-300 transition group-hover:text-[#4E81FA] dark:text-gray-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </span>
                                </div>
                                <span class="mt-auto text-sm font-medium text-[#4E81FA]">
                                    {{ __('messages.view_details') }}
                                </span>
                            </a>

                            <a href="{{ route('settings.general') }}"
                               class="group flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-6 transition hover:border-[#4E81FA] hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:hover:border-[#4E81FA]">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-[#4E81FA] dark:text-gray-100">
                                            {{ __('messages.general_settings') }}
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.general_settings_description') }}
                                        </p>
                                    </div>
                                    <span class="text-gray-300 transition group-hover:text-[#4E81FA] dark:text-gray-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </span>
                                </div>
                                <span class="mt-auto text-sm font-medium text-[#4E81FA]">
                                    {{ __('messages.view_details') }}
                                </span>
                            </a>

                            <a href="{{ route('settings.home') }}"
                               class="group flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-6 transition hover:border-[#4E81FA] hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:hover:border-[#4E81FA]">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-[#4E81FA] dark:text-gray-100">
                                            {{ __('messages.home_settings_heading') }}
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.home_settings_card_description') }}
                                        </p>
                                    </div>
                                    <span class="text-gray-300 transition group-hover:text-[#4E81FA] dark:text-gray-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </span>
                                </div>
                                <span class="mt-auto text-sm font-medium text-[#4E81FA]">
                                    {{ __('messages.view_details') }}
                                </span>
                            </a>

                            <a href="{{ route('settings.integrations') }}"
                               class="group flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-6 transition hover:border-[#4E81FA] hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:hover:border-[#4E81FA]">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-[#4E81FA] dark:text-gray-100">
                                            {{ __('messages.integrations_settings') }}
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.integrations_settings_description') }}
                                        </p>
                                    </div>
                                    <span class="text-gray-300 transition group-hover:text-[#4E81FA] dark:text-gray-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </span>
                                </div>
                                <span class="mt-auto text-sm font-medium text-[#4E81FA]">
                                    {{ __('messages.view_details') }}
                                </span>
                            </a>

                            <a href="{{ route('settings.logging') }}"
                               class="group flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-6 transition hover:border-[#4E81FA] hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:hover:border-[#4E81FA]">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-[#4E81FA] dark:text-gray-100">
                                            {{ __('messages.logging_settings') }}
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.logging_settings_short_description') }}
                                        </p>
                                    </div>
                                    <span class="text-gray-300 transition group-hover:text-[#4E81FA] dark:text-gray-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </span>
                                </div>
                                <span class="mt-auto text-sm font-medium text-[#4E81FA]">
                                    {{ __('messages.view_details') }}
                                </span>
                            </a>

                            <a href="{{ route('settings.terms') }}"
                               class="group flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-6 transition hover:border-[#4E81FA] hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:hover:border-[#4E81FA]">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-[#4E81FA] dark:text-gray-100">
                                            {{ __('messages.terms_settings') }}
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.terms_settings_description') }}
                                        </p>
                                    </div>
                                    <span class="text-gray-300 transition group-hover:text-[#4E81FA] dark:text-gray-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </span>
                                </div>
                                <span class="mt-auto text-sm font-medium text-[#4E81FA]">
                                    {{ __('messages.view_details') }}
                                </span>
                            </a>

                            <a href="{{ route('settings.updates') }}"
                               class="group flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-6 transition hover:border-[#4E81FA] hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:hover:border-[#4E81FA]">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-[#4E81FA] dark:text-gray-100">
                                            {{ __('messages.update_settings') }}
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.update_settings_short_description') }}
                                        </p>
                                    </div>
                                    <span class="text-gray-300 transition group-hover:text-[#4E81FA] dark:text-gray-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </span>
                                </div>
                                <span class="mt-auto text-sm font-medium text-[#4E81FA]">
                                    {{ __('messages.view_details') }}
                                </span>
                            </a>

                            <a href="{{ route('settings.users.index') }}"
                               class="group flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-6 transition hover:border-[#4E81FA] hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:hover:border-[#4E81FA]">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-[#4E81FA] dark:text-gray-100">
                                            {{ __('messages.user_management') }}
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.user_management_short_description') }}
                                        </p>
                                    </div>
                                    <span class="text-gray-300 transition group-hover:text-[#4E81FA] dark:text-gray-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </span>
                                </div>
                                <span class="mt-auto text-sm font-medium text-[#4E81FA]">
                                    {{ __('messages.view_details') }}
                                </span>
                            </a>

                            <a href="{{ route('settings.wallet') }}"
                               class="group flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-6 transition hover:border-[#4E81FA] hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:hover:border-[#4E81FA]">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-[#4E81FA] dark:text-gray-100">
                                            {{ __('messages.wallet_settings') }}
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.wallet_settings_description') }}
                                        </p>
                                    </div>
                                    <span class="text-gray-300 transition group-hover:text-[#4E81FA] dark:text-gray-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </span>
                                </div>
                                <span class="mt-auto text-sm font-medium text-[#4E81FA]">
                                    {{ __('messages.view_details') }}
                                </span>
                            </a>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout>
