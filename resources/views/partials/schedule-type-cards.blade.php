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
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{!! __('messages.talent_footer') !!}</p>
            <div class="flex items-center gap-1 text-sm font-semibold text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                {{ __('messages.get_started_cta') }}
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{!! __('messages.venue_footer') !!}</p>
            <div class="flex items-center gap-1 text-sm font-semibold text-sky-600 dark:text-sky-400 group-hover:gap-2 transition-all mt-auto">
                {{ __('messages.get_started_cta') }}
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{!! __('messages.curator_footer') !!}</p>
            <div class="flex items-center gap-1 text-sm font-semibold text-indigo-600 dark:text-indigo-400 group-hover:gap-2 transition-all mt-auto">
                {{ __('messages.get_started_cta') }}
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </a>
    </div>
</div>
