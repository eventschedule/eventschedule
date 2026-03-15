<x-app-admin-layout>
    <div>
        
        <!-- Get Started Panel -->
        @if($schedules->isEmpty() && $venues->isEmpty() && $curators->isEmpty() && auth()->user()->tickets()->count() === 0 && !is_demo_mode())
        <div class="mb-8">
            <!-- Header -->
            <div class="text-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
                    Welcome {{ auth()->user()->firstName() }}, let's get started
                </h2>
                <p class="text-gray-500 dark:text-gray-400">{{ __('messages.create_your_first_schedule') }}</p>
            </div>

            <!-- Schedule Types -->
            <div class="get-started-container rounded-2xl p-2 bg-gray-100 dark:bg-transparent">
                <div class="flex flex-col lg:flex-row">
                    <!-- Talent -->
                    <a href="{{ route('new', ['type' => 'talent']) }}" class="get-started-card group flex flex-col flex-1 rounded-xl p-6 transition-all duration-200">
                        <div class="mb-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-blue-50 dark:bg-blue-500/10">
                                <svg class="w-6 h-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ __('messages.talent') }}</h3>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.talent_tagline') }}</p>
                        <span class="inline-block mt-3 mb-3 px-3 py-1 text-xs font-medium rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 self-start">{{ __('messages.talent_best_for') }}</span>
                        <ul class="space-y-2 mb-4 flex-1">
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.talent_feature_1') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.talent_feature_2') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.talent_feature_3') }}
                            </li>
                        </ul>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3"><b><i>Your events</i></b> at various venues</p>
                        <div class="flex items-center gap-1 text-sm font-semibold text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                            {{ __('messages.get_started_cta') }}
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>

                    <!-- Separator -->
                    <div class="hidden lg:flex items-stretch py-8" aria-hidden="true">
                        <div class="w-px bg-black/[0.06] dark:bg-white/[0.08]"></div>
                    </div>
                    <div class="lg:hidden" aria-hidden="true">
                        <div class="h-px mx-4 bg-black/[0.06] dark:bg-white/[0.08]"></div>
                    </div>

                    <!-- Venue -->
                    <a href="{{ route('new', ['type' => 'venue']) }}" class="get-started-card group flex flex-col flex-1 rounded-xl p-6 transition-all duration-200">
                        <div class="mb-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-sky-50 dark:bg-sky-500/10">
                                <svg class="w-6 h-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ __('messages.venue') }}</h3>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.venue_tagline') }}</p>
                        <span class="inline-block mt-3 mb-3 px-3 py-1 text-xs font-medium rounded-full bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400 self-start">{{ __('messages.venue_best_for') }}</span>
                        <ul class="space-y-2 mb-4 flex-1">
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.venue_feature_1') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.venue_feature_2') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.venue_feature_3') }}
                            </li>
                        </ul>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Various events at <b><i>your venue</i></b></p>
                        <div class="flex items-center gap-1 text-sm font-semibold text-sky-600 dark:text-sky-400 group-hover:gap-2 transition-all mt-auto">
                            {{ __('messages.get_started_cta') }}
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>

                    <!-- Separator -->
                    <div class="hidden lg:flex items-stretch py-8" aria-hidden="true">
                        <div class="w-px bg-black/[0.06] dark:bg-white/[0.08]"></div>
                    </div>
                    <div class="lg:hidden" aria-hidden="true">
                        <div class="h-px mx-4 bg-black/[0.06] dark:bg-white/[0.08]"></div>
                    </div>

                    <!-- Curator -->
                    <a href="{{ route('new', ['type' => 'curator']) }}" class="get-started-card group flex flex-col flex-1 rounded-xl p-6 transition-all duration-200">
                        <div class="mb-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-indigo-50 dark:bg-indigo-500/10">
                                <svg class="w-6 h-6 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ __('messages.curator') }}</h3>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.curator_tagline') }}</p>
                        <span class="inline-block mt-3 mb-3 px-3 py-1 text-xs font-medium rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 self-start">{{ __('messages.curator_best_for') }}</span>
                        <ul class="space-y-2 mb-4 flex-1">
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.curator_feature_1') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.curator_feature_2') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.curator_feature_3') }}
                            </li>
                        </ul>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Various events at various venues</p>
                        <div class="flex items-center gap-1 text-sm font-semibold text-indigo-600 dark:text-indigo-400 group-hover:gap-2 transition-all mt-auto">
                            {{ __('messages.get_started_cta') }}
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        @endif

        @if (config('app.hosted'))
        {{-- Dashboard Header Actions with Feedback --}}
        <div class="flex flex-col sm:flex-row sm:items-start gap-4 mb-4">
            {{-- Feedback textarea --}}
            <div class="flex-1">
                <form id="feedback-form" class="h-full">
                    @csrf
                    <div class="relative h-full">
                        <textarea
                            id="feedback-textarea"
                            name="feedback"
                            placeholder="{{ __('messages.feedback_placeholder') }}"
                            class="w-full h-full px-4 py-2 pr-12 border border-gray-300 dark:border-white/[0.06] rounded-lg focus:ring-2 focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)] resize-none bg-white dark:bg-[#252526] text-gray-900 dark:text-gray-100"
                            rows="2"
                            dir="auto"
                        ></textarea>
                        <button
                            type="button"
                            id="feedback-submit-btn"
                            class="absolute bottom-2 right-2 p-2 mb-2 bg-[var(--brand-button-bg)] text-white rounded-lg hover:bg-[var(--brand-button-bg-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 transition-all opacity-0 pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed"
                            style="transition: opacity 0.2s ease-in-out;"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Buttons --}}
            <div class="flex items-start gap-3 flex-shrink-0">
                {{-- Customize Button --}}
                <button type="button" x-data x-on:click="$dispatch('open-modal', 'customize-dashboard')"
                    class="inline-flex items-center justify-center px-4 py-3 bg-white dark:bg-[#2d2d30] border border-gray-300 dark:border-white/[0.06] rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 shadow-sm dark:shadow-none transition-all duration-200 hover:bg-gray-50 dark:hover:bg-[#252526] hover:scale-105 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    <svg class="w-4 h-4 me-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ __('messages.customize_dashboard') }}
                </button>

                {{-- New Schedule Dropdown --}}
                @if(!is_demo_mode())
                <div class="relative inline-block text-left">
                    <button type="button" data-popup-target="dashboard-new-schedule-menu" class="popup-toggle inline-flex items-center justify-center px-4 py-3 bg-white dark:bg-[#2d2d30] border border-gray-300 dark:border-white/[0.06] rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 shadow-sm dark:shadow-none transition-all duration-200 hover:bg-gray-50 dark:hover:bg-[#252526] hover:scale-105 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800" aria-expanded="true" aria-haspopup="true">
                        {{ __('messages.new_schedule') }}
                        <svg class="ms-1.5 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div id="dashboard-new-schedule-menu" class="ap-dropdown pop-up-menu hidden absolute end-0 z-10 mt-2 w-64 {{ is_rtl() ? 'origin-top-left' : 'origin-top-right' }} divide-y divide-gray-100 dark:divide-white/[0.06] rounded-md ring-1 ring-black/5 dark:ring-white/[0.06] focus:outline-none" role="menu" aria-orientation="vertical" tabindex="-1">
                        <div class="py-1" role="none" data-popup-target="dashboard-new-schedule-menu">
                            <a href="{{ route('new', ['type' => 'talent']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-black/10" role="menuitem" tabindex="-1">
                                <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/>
                                </svg>
                                <div>
                                    {{ __('messages.talent') }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.new_schedule_tooltip') }}</div>
                                </div>
                            </a>
                            <a href="{{ route('new', ['type' => 'venue']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-black/10" role="menuitem" tabindex="-1">
                                <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z" />
                                </svg>
                                <div>
                                    {{ __('messages.venue') }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.new_venue_tooltip') }}</div>
                                </div>
                            </a>
                            <a href="{{ route('new', ['type' => 'curator']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-black/10" role="menuitem" tabindex="-1">
                                <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12,19.2C9.5,19.2 7.29,17.92 6,16C6.03,14 10,12.9 12,12.9C14,12.9 17.97,14 18,16C16.71,17.92 14.5,19.2 12,19.2M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z" />
                                </svg>
                                <div>
                                    {{ __('messages.curator') }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.new_curator_tooltip') }}</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @else
        {{-- Dashboard Header Actions (non-hosted) --}}
        <div class="flex justify-end items-center gap-3 mb-4">
            {{-- Customize Button --}}
            <button type="button" x-data x-on:click="$dispatch('open-modal', 'customize-dashboard')"
                class="inline-flex items-center justify-center px-4 py-3 bg-white dark:bg-[#2d2d30] border border-gray-300 dark:border-white/[0.06] rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 shadow-sm dark:shadow-none transition-all duration-200 hover:bg-gray-50 dark:hover:bg-[#252526] hover:scale-105 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <svg class="w-4 h-4 me-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ __('messages.customize_dashboard') }}
            </button>

            {{-- New Schedule Dropdown --}}
            @if(!is_demo_mode())
            <div class="relative inline-block text-left">
                <button type="button" data-popup-target="dashboard-new-schedule-menu" class="popup-toggle inline-flex items-center justify-center px-4 py-3 bg-white dark:bg-[#2d2d30] border border-gray-300 dark:border-white/[0.06] rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 shadow-sm dark:shadow-none transition-all duration-200 hover:bg-gray-50 dark:hover:bg-[#252526] hover:scale-105 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800" aria-expanded="true" aria-haspopup="true">
                    {{ __('messages.new_schedule') }}
                    <svg class="ms-1.5 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div id="dashboard-new-schedule-menu" class="ap-dropdown pop-up-menu hidden absolute end-0 z-10 mt-2 w-64 {{ is_rtl() ? 'origin-top-left' : 'origin-top-right' }} divide-y divide-gray-100 dark:divide-white/[0.06] rounded-md ring-1 ring-black/5 dark:ring-white/[0.06] focus:outline-none" role="menu" aria-orientation="vertical" tabindex="-1">
                    <div class="py-1" role="none" data-popup-target="dashboard-new-schedule-menu">
                        <a href="{{ route('new', ['type' => 'talent']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-black/10" role="menuitem" tabindex="-1">
                            <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/>
                            </svg>
                            <div>
                                {{ __('messages.talent') }}
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.new_schedule_tooltip') }}</div>
                            </div>
                        </a>
                        <a href="{{ route('new', ['type' => 'venue']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-black/10" role="menuitem" tabindex="-1">
                            <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z" />
                            </svg>
                            <div>
                                {{ __('messages.venue') }}
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.new_venue_tooltip') }}</div>
                            </div>
                        </a>
                        <a href="{{ route('new', ['type' => 'curator']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-black/10" role="menuitem" tabindex="-1">
                            <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12,19.2C9.5,19.2 7.29,17.92 6,16C6.03,14 10,12.9 12,12.9C14,12.9 17.97,14 18,16C16.71,17.92 14.5,19.2 12,19.2M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z" />
                            </svg>
                            <div>
                                {{ __('messages.curator') }}
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.new_curator_tooltip') }}</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Configurable Dashboard Panels --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-4">
            @foreach($dashboardConfig['panels'] as $panel)
                @if($panel['visible'])
                    <div class="{{ ($panel['size'] ?? 2) === 1 ? 'lg:col-span-1' : 'lg:col-span-2' }}">
                        @include('home.panels.' . $panel['id'])
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Calendar (always shown) --}}
        <div class="mb-4">
            @include('role/partials/calendar', ['route' => 'home', 'tab' => ''])
        </div>

        {{-- Customize Dashboard Modal --}}
        <x-modal name="customize-dashboard" maxWidth="lg">
            <div x-data="customizeDashboard()">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('messages.customize_dashboard') }}</h3>
                    <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6">
                    <div x-ref="panelList" class="flex flex-col gap-2">
                        <template x-for="(panel, index) in panels" :key="panel.id">
                            <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-200 dark:border-gray-700/50 transition-opacity duration-200 select-none" :class="{ 'opacity-50': !panel.visible }">
                                <div class="flex items-center gap-3 p-3">
                                    {{-- Drag Handle --}}
                                    <div class="drag-handle cursor-grab active:cursor-grabbing flex-shrink-0" :class="panel.visible ? 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300' : 'text-gray-300 dark:text-gray-600'">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="5" r="1.5"/><circle cx="15" cy="5" r="1.5"/><circle cx="9" cy="10" r="1.5"/><circle cx="15" cy="10" r="1.5"/><circle cx="9" cy="15" r="1.5"/><circle cx="15" cy="15" r="1.5"/><circle cx="9" cy="20" r="1.5"/><circle cx="15" cy="20" r="1.5"/></svg>
                                    </div>

                                    {{-- Toggle --}}
                                    <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                                        <input type="checkbox" x-model="panel.visible" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                                        <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                    </label>

                                    {{-- Label --}}
                                    <span class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-300" x-text="labels[panel.id]"></span>

                                    {{-- Gear Icon --}}
                                    <button type="button" x-on:click="toggleSettings(panel.id)" class="flex-shrink-0 p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200">
                                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-90': expandedPanel === panel.id }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                </div>

                                {{-- Expandable Settings --}}
                                <div x-show="expandedPanel === panel.id"
                                     x-transition:enter="transition-all ease-out duration-200"
                                     x-transition:enter-start="opacity-0 max-h-0"
                                     x-transition:enter-end="opacity-100 max-h-40"
                                     x-transition:leave="transition-all ease-in duration-150"
                                     x-transition:leave-start="opacity-100 max-h-40"
                                     x-transition:leave-end="opacity-0 max-h-0"
                                     x-cloak class="px-3 pb-3 overflow-hidden">
                                    <div class="flex flex-wrap items-center gap-4 pt-2 border-t border-gray-200 dark:border-gray-700/50">
                                        {{-- Size Selector --}}
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('messages.panel_size') }}:</span>
                                            <div class="inline-flex rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                                                <button type="button" x-on:click="panel.size = 1" class="px-2.5 py-1.5 text-xs font-medium transition-all duration-200 flex items-center gap-1" :class="panel.size === 1 ? 'bg-[var(--brand-button-bg)] text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="currentColor"><rect x="2" y="2" width="5" height="12" rx="1"/></svg>
                                                    {{ __('messages.panel_half_width') }}
                                                </button>
                                                <button type="button" x-on:click="panel.size = 2" class="px-2.5 py-1.5 text-xs font-medium transition-all duration-200 flex items-center gap-1 border-l border-gray-200 dark:border-gray-600" :class="panel.size === 2 ? 'bg-[var(--brand-button-bg)] text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="currentColor"><rect x="2" y="2" width="12" height="12" rx="1"/></svg>
                                                    {{ __('messages.panel_full_width') }}
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Period Selector --}}
                                        <template x-if="panelMeta[panel.id]?.periods">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('messages.panel_period') }}:</span>
                                                <div class="inline-flex rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                                                    <template x-for="(p, pi) in panelMeta[panel.id].periods" :key="p">
                                                        <button type="button" x-on:click="panel.period = p" class="px-2.5 py-1.5 text-xs font-medium transition-all duration-200" :class="[panel.period === p ? 'bg-[var(--brand-button-bg)] text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600', pi > 0 ? 'border-l border-gray-200 dark:border-gray-600' : '']" x-text="p + 'd'"></button>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Count Selector --}}
                                        <template x-if="panelMeta[panel.id]?.counts">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('messages.panel_items') }}:</span>
                                                <div class="inline-flex rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                                                    <template x-for="(c, ci) in panelMeta[panel.id].counts" :key="c">
                                                        <button type="button" x-on:click="panel.count = c" class="px-2.5 py-1.5 text-xs font-medium transition-all duration-200" :class="[panel.count === c ? 'bg-[var(--brand-button-bg)] text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600', ci > 0 ? 'border-l border-gray-200 dark:border-gray-600' : '']" x-text="c"></button>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <button type="button" x-on:click="resetDefaults()" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-200">
                        {{ __('messages.reset_defaults') }}
                    </button>
                    <div class="flex gap-3">
                        <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200">
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="button" x-on:click="save()" :disabled="saving" class="px-4 py-2 text-sm font-medium text-white bg-[var(--brand-button-bg)] rounded-lg hover:bg-[var(--brand-button-bg-hover)] disabled:opacity-50 transition-all duration-200">
                            <span x-show="!saving">{{ __('messages.save') }}</span>
                            <span x-show="saving" x-cloak>{{ __('messages.saving') }}...</span>
                        </button>
                    </div>
                </div>
            </div>
        </x-modal>

    </div>

    @if(collect($dashboardConfig['panels'])->where('id', 'views')->where('visible', true)->isNotEmpty())
    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
    @endif
    <script src="{{ asset('js/sortable.min.js') }}" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>
        function customizeDashboard() {
            return {
                panels: {{ Js::from($dashboardConfig['panels']) }},
                defaultPanels: {{ Js::from($dashboardConfig['defaultPanels']) }},
                labels: {{ Js::from([
                    'upcoming_count' => __('messages.panel_upcoming_count'),
                    'views' => __('messages.panel_views'),
                    'followers' => __('messages.panel_followers'),
                    'upcoming_events' => __('messages.panel_upcoming_events'),
                    'recent_activity' => __('messages.panel_recent_activity'),
                    'revenue' => __('messages.panel_revenue'),
                    'top_events' => __('messages.panel_top_events'),
                    'newsletters' => __('messages.panel_newsletters'),
                    'boosts' => __('messages.panel_boosts'),
                    'traffic_sources' => __('messages.panel_traffic_sources'),
                ]) }},
                panelMeta: {
                    upcoming_count: { defaultSize: 1 },
                    views: { defaultSize: 1, periods: [7, 14, 30] },
                    followers: { defaultSize: 1 },
                    revenue: { defaultSize: 1, periods: [7, 14, 30] },
                    upcoming_events: { defaultSize: 2, counts: [3, 5] },
                    recent_activity: { defaultSize: 2, counts: [5, 10] },
                    top_events: { defaultSize: 2, counts: [3, 5], periods: [7, 14, 30] },
                    newsletters: { defaultSize: 2, counts: [3, 5] },
                    boosts: { defaultSize: 2, counts: [3, 5] },
                    traffic_sources: { defaultSize: 2, counts: [3, 5, 10], periods: [7, 14, 30] }
                },
                expandedPanel: null,
                saving: false,
                sortableInstance: null,
                saveConfigUrl: {{ Js::from(route('home.save_config')) }},
                init() {
                    this.$nextTick(() => this.initSortable());
                },
                initSortable() {
                    const list = this.$refs.panelList;
                    if (!list || typeof Sortable === 'undefined') return;
                    if (this.sortableInstance) this.sortableInstance.destroy();
                    this.sortableInstance = Sortable.create(list, {
                        handle: '.drag-handle',
                        animation: 150,
                        ghostClass: 'opacity-50',
                        fallbackOnBody: true,
                        onStart: (evt) => {
                            this._childOrder = [...evt.from.children];
                        },
                        onEnd: (evt) => {
                            this._childOrder.forEach(child => evt.from.appendChild(child));
                            const item = this.panels.splice(evt.oldIndex, 1)[0];
                            this.panels.splice(evt.newIndex, 0, item);
                        }
                    });
                },
                toggleSettings(panelId) {
                    this.expandedPanel = this.expandedPanel === panelId ? null : panelId;
                },
                resetDefaults() {
                    this.panels = JSON.parse(JSON.stringify(this.defaultPanels));
                },
                async save() {
                    this.saving = true;
                    try {
                        const response = await fetch(this.saveConfigUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ panels: this.panels })
                        });
                        if (!response.ok) throw new Error('Request failed');
                        const data = await response.json();
                        if (data.success) {
                            window.location.reload();
                        }
                    } catch (e) {
                        this.saving = false;
                    }
                }
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            const feedbackForm = document.getElementById('feedback-form');
            const feedbackTextarea = document.getElementById('feedback-textarea');
            const submitButton = document.getElementById('feedback-submit-btn');
            
            if (feedbackForm && feedbackTextarea && submitButton) {
                // Show/hide submit button based on textarea content
                function toggleSubmitButton() {
                    const hasText = feedbackTextarea.value.trim().length > 0;
                    if (hasText) {
                        submitButton.classList.remove('opacity-0', 'pointer-events-none');
                        submitButton.classList.add('opacity-100');
                    } else {
                        submitButton.classList.add('opacity-0', 'pointer-events-none');
                        submitButton.classList.remove('opacity-100');
                    }
                }
                
                // Listen for input changes
                feedbackTextarea.addEventListener('input', toggleSubmitButton);
                feedbackTextarea.addEventListener('keyup', toggleSubmitButton);
                
                // Handle form submission
                async function submitFeedback() {
                    const feedback = feedbackTextarea.value.trim();
                    if (!feedback) {
                        return;
                    }
                    
                    submitButton.disabled = true;
                    
                    try {
                        const formData = new FormData();
                        formData.append('feedback', feedback);
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}');
                        
                        const response = await fetch('{{ route("home.feedback") }}', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });
                        if (!response.ok) throw new Error('Request failed');
                        const data = await response.json();
                        
                        if (data.success) {
                            Toastify({
                                text: data.message || @json(__("messages.feedback_submitted")),
                                duration: 3000,
                                position: 'center',
                                stopOnFocus: true,
                                style: {
                                    background: '#4BB543',
                                }
                            }).showToast();
                            
                            feedbackTextarea.value = '';
                            toggleSubmitButton();
                        } else {
                            Toastify({
                                text: data.message || @json(__("messages.feedback_failed")),
                                duration: 5000,
                                position: 'center',
                                stopOnFocus: true,
                                style: {
                                    background: '#FF0000',
                                }
                            }).showToast();
                        }
                    } catch (error) {
                        Toastify({
                            text: @json(__("messages.feedback_failed")),
                            duration: 5000,
                            position: 'center',
                            stopOnFocus: true,
                            style: {
                                background: '#FF0000',
                            }
                        }).showToast();
                    } finally {
                        submitButton.disabled = false;
                    }
                }
                
                // Handle button click
                submitButton.addEventListener('click', submitFeedback);
                
                // Handle Enter key (Ctrl+Enter or Cmd+Enter to submit)
                feedbackTextarea.addEventListener('keydown', function(e) {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                        e.preventDefault();
                        submitFeedback();
                    }
                });
            }

            // Sparkline chart
            const sparklineCanvas = document.getElementById('sparkline-chart');
            if (sparklineCanvas && typeof Chart !== 'undefined') {
                const sparklineData = @json($sparklineData ?? []);
                const ctx = sparklineCanvas.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 48);
                gradient.addColorStop(0, 'rgba(14, 165, 233, 0.3)');
                gradient.addColorStop(1, 'rgba(14, 165, 233, 0)');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: sparklineData.map((_, i) => i),
                        datasets: [{
                            data: sparklineData,
                            borderColor: '#0ea5e9',
                            backgroundColor: gradient,
                            borderWidth: 1.5,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { enabled: false } },
                        scales: {
                            x: { display: false },
                            y: { display: false, beginAtZero: true }
                        },
                        animation: false,
                    }
                });
            }
        });
    </script>
</x-app-admin-layout>
