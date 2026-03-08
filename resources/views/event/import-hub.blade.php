<x-app-admin-layout>

    <div class="flex justify-between items-center gap-6 pb-6">
        @if (is_rtl())
            <div class="flex items-center gap-3">
                <button type="button" class="js-back-btn inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 px-4 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    {{ __('messages.back') }}
                </button>
            </div>

            <div class="flex items-center text-end">
                @if ($role->profile_image_url)
                    <div class="pe-4">
                        <img src="{{ $role->profile_image_url }}" class="rounded-lg h-14 w-14 flex-none">
                    </div>
                @endif
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ __('messages.import_sources') }}
                </h2>
            </div>
        @else
            <div class="flex items-center">
                @if ($role->profile_image_url)
                    <div class="pe-4">
                        <img src="{{ $role->profile_image_url }}" class="rounded-lg h-14 w-14 flex-none">
                    </div>
                @endif
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ __('messages.import_sources') }}
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" class="js-back-btn inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 px-4 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    {{ __('messages.back') }}
                </button>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Eventbrite Import Card --}}
        @if ($role->isPro())
            <a href="{{ route('event.show_import_eventbrite', ['subdomain' => $role->subdomain]) }}" class="group block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-[var(--brand-blue)] p-6 transition-all duration-200 hover:shadow-md">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-[#F05537]/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F05537]" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.996 6.002l-1.477 4.533c-.124.378.1.77.497.868l5.14 1.28c.394.098.582.54.334.858-1.186 1.512-3.038 3.8-5.15 5.296-2.048 1.452-3.89 1.998-5.293 2.144-.396.04-.7-.32-.584-.7l1.478-4.85c.123-.4-.107-.81-.514-.908l-4.822-1.166c-.394-.096-.574-.54-.326-.858 1.172-1.504 3.018-3.8 5.128-5.294 2.048-1.452 3.888-1.998 5.29-2.146.397-.04.702.32.586.7-.044.056-.217.78-.287.943z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 group-hover:text-[var(--brand-blue)] transition-colors">
                            {{ __('messages.import_from_eventbrite') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('messages.eventbrite_import_description') }}
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-[var(--brand-blue)] transition-colors {{ is_rtl() ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
            </a>
        @else
            <div class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 opacity-60">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-[#F05537]/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F05537]" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.996 6.002l-1.477 4.533c-.124.378.1.77.497.868l5.14 1.28c.394.098.582.54.334.858-1.186 1.512-3.038 3.8-5.15 5.296-2.048 1.452-3.89 1.998-5.293 2.144-.396.04-.7-.32-.584-.7l1.478-4.85c.123-.4-.107-.81-.514-.908l-4.822-1.166c-.394-.096-.574-.54-.326-.858 1.172-1.504 3.018-3.8 5.128-5.294 2.048-1.452 3.888-1.998 5.29-2.146.397-.04.702.32.586.7-.044.056-.217.78-.287.943z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            {{ __('messages.import_from_eventbrite') }}
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                Pro
                            </span>
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('messages.eventbrite_import_description') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- AI Import Card --}}
        @php
            $canUseAiImport = $role->isEnterprise() || !config('app.hosted');
        @endphp
        @if ($canUseAiImport)
            <a href="{{ route('event.show_import_ai', ['subdomain' => $role->subdomain]) }}" class="group block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-[var(--brand-blue)] p-6 transition-all duration-200 hover:shadow-md">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 group-hover:text-[var(--brand-blue)] transition-colors">
                            {{ __('messages.ai_import') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('messages.ai_import_description') }}
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-[var(--brand-blue)] transition-colors {{ is_rtl() ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
            </a>
        @else
            <div class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 opacity-60">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            {{ __('messages.ai_import') }}
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                Enterprise
                            </span>
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('messages.ai_import_description') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.js-back-btn')) {
                history.back();
            }
        });
    </script>

</x-app-admin-layout>
