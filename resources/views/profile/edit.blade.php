<x-app-admin-layout>

    <x-slot name="head">
        <style {!! nonce_attr() !!}>
        /* Hide all sections except the first one by default */
        .section-content {
            display: none;
        }
        .section-content:first-of-type {
            display: block;
        }

        /* Mobile accordion styles */
        .mobile-section-header.active .accordion-chevron {
            transform: rotate(180deg);
        }
        .mobile-section-header.active {
            color: #4E81FA;
            border-color: #4E81FA;
        }
        .mobile-section-header.validation-error {
            border-color: #dc2626 !important;
        }
        </style>
    </x-slot>

    <h2 class="pb-4 text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
        {{ __('messages.settings') }}
    </h2>

    <div class="py-5">
        <div class="mx-auto lg:grid lg:grid-cols-12 lg:gap-6">
            <!-- Sidebar Navigation (hidden on small screens, visible on lg+) -->
            <div class="hidden lg:block lg:col-span-3">
                <div class="sticky top-6">
                    <nav class="space-y-1">
                        <a href="#section-profile" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-profile">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            {{ __('messages.profile_information') }}
                        </a>
                        <a href="#section-payment-methods" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-payment-methods">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                            </svg>
                            {{ __('messages.payment_methods') }}
                        </a>
                        <a href="#section-api" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-api">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                            </svg>
                            {{ __('messages.api_settings') }}
                        </a>
                        <a href="#section-google-calendar" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-google-calendar">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            {{ __('messages.google_settings') }}
                        </a>
                        @if (! config('app.hosted') && ! config('app.is_testing'))
                        <a href="#section-app" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-app">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ __('messages.app_update') }}
                        </a>
                        @endif
                        <a href="#section-password" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-password">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            {{ __('messages.update_password') }}
                        </a>
                        <a href="#section-two-factor" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-two-factor">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                            {{ __('messages.two_factor_authentication') }}
                        </a>
                        @if (config('app.hosted') || config('app.is_testing'))
                        <a href="#section-delete" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-delete">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            {{ __('messages.delete_account') }}
                        </a>
                        @endif
                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="lg:col-span-9 space-y-6 lg:space-y-0">
                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-profile">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        {{ __('messages.profile_information') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-profile" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-payment-methods">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                        {{ __('messages.payment_methods') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-payment-methods" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">
                        @include('profile.partials.update-payments-form')
                    </div>
                </div>

                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-api">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                        </svg>
                        {{ __('messages.api_settings') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-api" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">
                        @include('profile.partials.api-settings-form')
                    </div>
                </div>

                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-google-calendar">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        {{ __('messages.google_settings') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-google-calendar" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">
                        @include('profile.partials.google-calendar-form')
                    </div>
                </div>

                @if (! config('app.hosted') && ! config('app.is_testing'))
                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-app">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ __('messages.app_update') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-app" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">
                        @include('profile.partials.update-app-form')
                    </div>
                </div>
                @endif

                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-password">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        {{ __('messages.update_password') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-password" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-two-factor">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        {{ __('messages.two_factor_authentication') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-two-factor" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">
                        @include('profile.partials.two-factor-form')
                    </div>
                </div>

                @if (config('app.hosted') || config('app.is_testing'))
                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-delete">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        {{ __('messages.delete_account') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-delete" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
    var _scrollGuard = function() { window.scrollTo(0, 0); };
    window.addEventListener('scroll', _scrollGuard);

    // Section navigation functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sectionLinks = document.querySelectorAll('.section-nav-link');
        const sections = document.querySelectorAll('.section-content');
        const mobileHeaders = document.querySelectorAll('.mobile-section-header');

        // Function to sync mobile accordion headers
        function syncMobileHeaders(sectionId) {
            mobileHeaders.forEach(header => {
                if (header.getAttribute('data-section') === sectionId) {
                    header.classList.add('active');
                } else {
                    header.classList.remove('active');
                }
            });
        }

        // Function to show a specific section and hide others
        function showSection(sectionId, preventScroll = false) {
            sections.forEach(section => {
                if (section.id === sectionId) {
                    section.style.display = 'block';
                } else {
                    section.style.display = 'none';
                }
            });

            // Update active link
            sectionLinks.forEach(link => {
                if (link.getAttribute('data-section') === sectionId) {
                    link.classList.add('bg-gray-100', 'dark:bg-gray-700', 'text-gray-900', 'dark:text-white', 'font-bold', 'border-[#4E81FA]');
                    link.classList.remove('text-gray-700', 'dark:text-gray-300', 'font-medium', 'border-transparent');
                } else {
                    link.classList.remove('bg-gray-100', 'dark:bg-gray-700', 'text-gray-900', 'dark:text-white', 'font-bold', 'border-[#4E81FA]');
                    link.classList.add('text-gray-700', 'dark:text-gray-300', 'font-medium', 'border-transparent');
                }
            });

            // Sync mobile accordion headers
            syncMobileHeaders(sectionId);

            // Update URL hash
            if (history.pushState) {
                history.pushState(null, null, '#' + sectionId);
            } else {
                window.location.hash = sectionId;
            }

            // Prevent scroll if requested
            if (preventScroll) {
                window.scrollTo(0, 0);
            }
        }

        // Handle navigation link clicks
        sectionLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const sectionId = this.getAttribute('data-section');
                showSection(sectionId);
            });
        });

        // Handle mobile accordion header clicks
        mobileHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const sectionId = this.getAttribute('data-section');
                showSection(sectionId);
            });
        });

        // Check if we're on a large screen
        function isLargeScreen() {
            return window.matchMedia('(min-width: 1024px)').matches;
        }

        // Initialize: show section based on hash or first section
        function initializeSections() {
            // Check URL hash first
            const hash = window.location.hash.replace('#', '');
            if (hash && document.getElementById(hash)) {
                showSection(hash, true);
            } else {
                // Show first section
                const firstSection = sections[0];
                if (firstSection) {
                    showSection(firstSection.id, true);
                }
            }
        }

        // Handle hash changes
        window.addEventListener('hashchange', function() {
            const hash = window.location.hash.replace('#', '');
            if (hash && document.getElementById(hash)) {
                showSection(hash);
            }
        });

        // Initialize on page load
        initializeSections();

        // Highlight phone field if redirected from boost
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('highlight') === 'phone') {
            const phoneField = document.getElementById('phone-field');
            if (phoneField) {
                phoneField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                phoneField.classList.add('ring-2', 'ring-[#4E81FA]', 'rounded-md', 'p-2', '-m-2');
                setTimeout(() => {
                    phoneField.classList.remove('ring-2', 'ring-[#4E81FA]', 'rounded-md', 'p-2', '-m-2');
                }, 5000);
            }
        }
    });

    window.addEventListener('load', function() {
        window.scrollTo(0, 0);
        requestAnimationFrame(function() {
            window.scrollTo(0, 0);
            setTimeout(function() {
                window.removeEventListener('scroll', _scrollGuard);
            }, 300);
        });
    });
    </script>

</x-app-admin-layout>
