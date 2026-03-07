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
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Talent -->
                <a href="{{ route('new', ['type' => 'talent']) }}" class="group flex flex-col rounded-xl bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700/50 hover:border-blue-300 dark:hover:border-blue-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/10 overflow-hidden">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/20 p-6 flex items-center justify-center">
                        <svg class="w-16 h-16 text-blue-500 dark:text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,19.2C9.5,19.2 7.29,17.92 6,16C6.03,14 10,12.9 12,12.9C14,12.9 17.97,14 18,16C16.71,17.92 14.5,19.2 12,19.2M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z"/>
                        </svg>
                    </div>
                    <div class="p-5 flex flex-col flex-1">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ __('messages.talent') }}</h3>
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400 mt-1">{{ __('messages.talent_tagline') }}</p>
                        <span class="inline-block mt-3 mb-3 px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 self-start">{{ __('messages.talent_best_for') }}</span>
                        <ul class="space-y-2 mb-4 flex-1">
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.talent_feature_1') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.talent_feature_2') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.talent_feature_3') }}
                            </li>
                        </ul>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3"><b><i>Your events</i></b> at various venues</p>
                        <div class="flex items-center gap-1 text-sm font-semibold text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all">
                            {{ __('messages.get_started_cta') }}
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </a>

                <!-- Venue -->
                <a href="{{ route('new', ['type' => 'venue']) }}" class="group flex flex-col rounded-xl bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700/50 hover:border-sky-300 dark:hover:border-sky-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-sky-500/10 overflow-hidden">
                    <div class="bg-gradient-to-br from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-800/20 p-6 flex items-center justify-center">
                        <svg class="w-16 h-16 text-sky-500 dark:text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z"/>
                        </svg>
                    </div>
                    <div class="p-5 flex flex-col flex-1">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ __('messages.venue') }}</h3>
                        <p class="text-sm font-medium text-sky-600 dark:text-sky-400 mt-1">{{ __('messages.venue_tagline') }}</p>
                        <span class="inline-block mt-3 mb-3 px-3 py-1 text-xs font-medium rounded-full bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300 self-start">{{ __('messages.venue_best_for') }}</span>
                        <ul class="space-y-2 mb-4 flex-1">
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.venue_feature_1') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.venue_feature_2') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.venue_feature_3') }}
                            </li>
                        </ul>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Various events at <b><i>your venue</i></b></p>
                        <div class="flex items-center gap-1 text-sm font-semibold text-sky-600 dark:text-sky-400 group-hover:gap-2 transition-all">
                            {{ __('messages.get_started_cta') }}
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </a>

                <!-- Curator -->
                <a href="{{ route('new', ['type' => 'curator']) }}" class="group flex flex-col rounded-xl bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700/50 hover:border-indigo-300 dark:hover:border-indigo-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/10 overflow-hidden">
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-800/20 p-6 flex items-center justify-center">
                        <svg class="w-16 h-16 text-indigo-500 dark:text-indigo-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/>
                        </svg>
                    </div>
                    <div class="p-5 flex flex-col flex-1">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ __('messages.curator') }}</h3>
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 mt-1">{{ __('messages.curator_tagline') }}</p>
                        <span class="inline-block mt-3 mb-3 px-3 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 self-start">{{ __('messages.curator_best_for') }}</span>
                        <ul class="space-y-2 mb-4 flex-1">
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.curator_feature_1') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.curator_feature_2') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.curator_feature_3') }}
                            </li>
                        </ul>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Various events at various venues</p>
                        <div class="flex items-center gap-1 text-sm font-semibold text-indigo-600 dark:text-indigo-400 group-hover:gap-2 transition-all">
                            {{ __('messages.get_started_cta') }}
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        @elseif (config('app.hosted'))
        <div class="mb-6 w-full">
            <form id="feedback-form" class="w-full">
                @csrf
                <div class="relative w-full">
                    <textarea
                        id="feedback-textarea"
                        name="feedback"
                        placeholder="{{ __('messages.feedback_placeholder') }}"
                        class="w-full px-4 py-2 pr-12 pb-10 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 min-h-[135px] sm:min-h-0"
                        rows="2"
                        dir="auto"
                    ></textarea>
                    <button 
                        type="button"
                        id="feedback-submit-btn"
                        class="absolute bottom-2 right-2 p-2 mb-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all opacity-0 pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed"
                        style="transition: opacity 0.2s ease-in-out;"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
        @endif

        @if($roleIds->isNotEmpty())
        {{-- Quick Actions Bar --}}
        <div class="flex flex-wrap gap-3 mb-6">
            <x-secondary-link :href="route('event.create_default')" class="!py-2 !text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('messages.create_event') }}
            </x-secondary-link>
            @php $firstSchedule = $schedules->first() ?? $venues->first() ?? $curators->first(); @endphp
            @if($firstSchedule)
            <x-secondary-link :href="route('role.view_guest', ['subdomain' => $firstSchedule->subdomain])" class="!py-2 !text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                {{ __('messages.view_schedule') }}
            </x-secondary-link>
            @endif
            <x-secondary-link :href="route('analytics')" class="!py-2 !text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                {{ __('messages.view_analytics') }}
            </x-secondary-link>
        </div>

        {{-- Stat Cards Row --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Upcoming Events --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700/50">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30">
                        <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.upcoming_events') }}</span>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($dashboardStats['upcoming_count']) }}</p>
            </div>

            {{-- Page Views (30d) with sparkline --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700/50 lg:col-span-2">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 rounded-lg bg-sky-50 dark:bg-sky-900/30">
                                <svg class="w-5 h-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.total_views') }} (30d)</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($dashboardStats['views_30d']) }}</p>
                            @if($dashboardStats['views_change'] != 0)
                            <span class="text-sm font-medium {{ $dashboardStats['views_change'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $dashboardStats['views_change'] > 0 ? '+' : '' }}{{ $dashboardStats['views_change'] }}%
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="w-24 h-12 flex-shrink-0">
                        <canvas id="sparkline-chart" width="96" height="48"></canvas>
                    </div>
                </div>
            </div>

            {{-- Followers or Total Events --}}
            @if($dashboardStats['followers_count'] > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700/50">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 rounded-lg bg-green-50 dark:bg-green-900/30">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.followers') }}</span>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($dashboardStats['followers_count']) }}</p>
            </div>
            @else
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700/50">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 rounded-lg bg-green-50 dark:bg-green-900/30">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.events') }}</span>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($dashboardStats['total_events_count']) }}</p>
            </div>
            @endif
        </div>

        {{-- Two-Column Section: Upcoming Events + Recent Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Upcoming Events List --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700/50">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700/50">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.upcoming_events') }}</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($upcomingEvents as $event)
                    @php $eventRole = $event->roles->first(); @endphp
                    <a href="{{ $eventRole ? route('event.edit', ['subdomain' => $eventRole->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)]) : '#' }}" class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        @if($event->getImageUrl())
                        <img src="{{ $event->getImageUrl() }}" alt="" class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
                        @else
                        <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $event->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $event->starts_at ? $event->starts_at->format('M j, g:i A') : '' }}
                                @if($eventRole)
                                <span class="mx-1">&middot;</span> {{ $eventRole->name }}
                                @endif
                            </p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    @empty
                    <div class="px-5 py-8 text-center">
                        <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_upcoming_events') }}</p>
                        <a href="{{ route('event.create_default') }}" class="inline-block mt-3 text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">{{ __('messages.create_event') }}</a>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Activity Feed --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700/50">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700/50">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.recent_activity') }}</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($recentActivity as $activity)
                    <div class="flex items-start gap-3 px-5 py-3">
                        @if($activity['type'] === 'sale')
                        <div class="mt-1 w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                {{ __('messages.new_ticket_sale') }}
                                @if($activity['description'])
                                <span class="font-medium text-gray-900 dark:text-white">{{ $activity['description'] }}</span>
                                @endif
                                @if(!empty($activity['amount']))
                                <span class="text-green-600 dark:text-green-400 font-medium">${{ number_format($activity['amount'], 2) }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $activity['date']->diffForHumans() }}</p>
                        </div>
                        @elseif($activity['type'] === 'follower')
                        <div class="mt-1 w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                @if($activity['description'])
                                <span class="font-medium text-gray-900 dark:text-white">{{ $activity['description'] }}</span>
                                @endif
                                {{ __('messages.started_following') }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $activity['date']->diffForHumans() }}</p>
                        </div>
                        @elseif($activity['type'] === 'newsletter')
                        <div class="mt-1 w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $activity['description'] }}</span>
                                {{ __('messages.newsletter_sent_to') }} {{ $activity['sent_count'] ?? 0 }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $activity['date']->diffForHumans() }}</p>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="px-5 py-8 text-center">
                        <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_recent_activity') }}</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endif

        @include('role/partials/calendar', ['route' => 'home', 'tab' => ''])

    </div>

    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>
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
