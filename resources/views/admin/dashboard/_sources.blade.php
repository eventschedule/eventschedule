{{-- Where the last 30 days' organizers came from (App\Utils\SignupSource). Every row is a share
     of all of them, "Not recorded" included and always last, so the list adds up to the headline
     instead of to a base the reader cannot see. --}}
@php
    $icons = \App\Utils\RealtimeIcons::PATHS;
    $channelIcons = ['referral' => 'users', 'unrecorded' => 'cancel'];
    $sources = $signups['sources'] ?? null;
    $max = $sources ? max(1, collect($sources['channels'])->max('count') ?? 1) : 1;
    $maxLanding = $sources ? max(1, collect($sources['landing'])->max('count') ?? 1) : 1;
@endphp

<section class="ap-card rounded-xl flex flex-col">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">@lang('messages.admin_dash_sources_title')</h2>
        <span class="text-xs text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_organizers') &middot; @lang('messages.admin_dash_last_30_days')</span>
    </div>

    @if (! $signups)
        @include('admin.dashboard._failed')
    @elseif ($sources['total'] === 0)
        <p class="px-5 pb-5 text-sm text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_no_signups')</p>
    @else
        <ul class="px-2 pb-2">
            @foreach ($sources['channels'] as $channel)
                @php
                    $muted = $channel['channel'] === 'unrecorded';
                @endphp
                <li class="relative flex items-center gap-3 px-3 py-2 text-sm rounded-md">
                    <span aria-hidden="true" class="absolute inset-y-0.5 start-0 rounded-md {{ $muted ? 'bg-gray-100 dark:bg-white/[0.04]' : 'bg-[var(--brand-blue-a10)]' }}" style="width: {{ max(2, round($channel['count'] / $max * 100)) }}%"></span>
                    <span class="relative flex items-center gap-2 min-w-0 flex-1">
                        <svg class="w-4 h-4 shrink-0 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$channelIcons[$channel['channel']] ?? $channel['channel']] ?? $icons['other'] }}" />
                        </svg>
                        <span class="truncate {{ $muted ? 'text-gray-600 dark:text-gray-400' : 'text-gray-900 dark:text-gray-100' }}">{{ $channel['label'] }}</span>
                        @if ($channel['names'])
                            <span class="hidden sm:inline truncate text-xs text-gray-500 dark:text-gray-400"><bdi dir="ltr">{{ collect($channel['names'])->map(fn ($name) => $name['name'].' '.number_format($name['count']))->implode(' · ') }}</bdi></span>
                        @endif
                    </span>
                    <span class="relative text-xs tabular-nums text-gray-500 dark:text-gray-400 w-9 text-end">{{ $channel['share'] }}%</span>
                    <span class="relative tabular-nums text-end text-gray-700 dark:text-gray-300 w-8">{{ number_format($channel['count']) }}</span>
                </li>
            @endforeach
        </ul>

        @if ($sources['landing'])
            <div class="px-2 pb-3">
                <p class="px-3 pt-2 pb-1 text-xs font-medium text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_first_page')</p>
                <ul>
                    @foreach ($sources['landing'] as $page)
                        <li class="relative flex items-center gap-3 px-3 py-1.5 text-sm rounded-md">
                            <span aria-hidden="true" class="absolute inset-y-0.5 start-0 rounded-md bg-gray-100 dark:bg-white/[0.04]" style="width: {{ max(2, round($page['count'] / $maxLanding * 100)) }}%"></span>
                            <span class="relative flex-1 min-w-0 truncate text-gray-900 dark:text-gray-100">
                                @if ($page['path'] === '/')
                                    @lang('messages.admin_dash_home_page')
                                @else
                                    <bdi dir="ltr">{{ $page['path'] }}</bdi>
                                @endif
                            </span>
                            <span class="relative tabular-nums text-end text-gray-700 dark:text-gray-300 w-8">{{ number_format($page['count']) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-auto px-4 sm:px-5 py-3 flex flex-wrap items-center justify-between gap-x-4 gap-y-2" style="border-top: 1px solid var(--ap-hairline)">
            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-[24rem]">@lang('messages.admin_dash_consent_note')</p>
            <x-link :href="route('admin.users')" class="text-sm font-medium whitespace-nowrap">@lang('messages.traffic_sources')</x-link>
        </div>
    @endif
</section>
