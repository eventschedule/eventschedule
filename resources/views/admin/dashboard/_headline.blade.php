{{-- The four numbers the page leads with. Each tile jumps to the card that explains it, where
     that card is on the page. --}}
@php
    $icons = \App\Utils\RealtimeIcons::PATHS;
    $figure = 'dashboard-stat-value text-3xl font-bold text-center text-gray-900 dark:text-white';
    $caption = 'mt-0.5 text-xs text-gray-500 dark:text-gray-400 text-center';
    $bolt = 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z';
    // A new install has the numbers and one card, so there is nothing for a tile to jump to.
    $jump = fn (string $card) => $dashboard['firstRun'] ? null : '#'.$card;
@endphp

<div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
    <x-admin-stat-tile :href="$jump('dash-signups')" :label="__('messages.admin_dash_new_organizers')" :icon="$icons['signup']"
        tint="bg-blue-50 dark:bg-blue-500/10" ink="text-blue-500" glow="rgba(59, 130, 246, 0.15)">
        @if ($signups)
            <div class="grid grid-cols-2 w-full">
                <div class="flex flex-col items-center">
                    <span class="{{ $figure }}">{{ number_format($signups['organizers']['last_24h']) }}</span>
                    <span class="{{ $caption }}">@lang('messages.admin_dash_24_hours')</span>
                </div>
                <div class="flex flex-col items-center" style="border-inline-start: 1px solid var(--ap-hairline)">
                    <span class="{{ $figure }}">{{ number_format($signups['organizers']['last_30d']) }}</span>
                    <span class="{{ $caption }}">@lang('messages.admin_dash_30_days')</span>
                </div>
            </div>
            @if ($signups['organizers']['change'] !== null)
                <x-slot:footer>
                    <span class="whitespace-nowrap"><span class="font-medium {{ $tone($signups['organizers']['change']) }}">{{ $pct($signups['organizers']['change']) }}</span> @lang('messages.admin_dash_vs_previous_30_days')</span>
                </x-slot:footer>
            @endif
        @endif
    </x-admin-stat-tile>

    <x-admin-stat-tile :href="$jump('dash-active')" :label="__('messages.admin_dash_active_users')" :icon="$bolt"
        tint="bg-green-50 dark:bg-green-500/10" ink="text-green-500" glow="rgba(34, 197, 94, 0.15)">
        @if ($active && $active['available'])
            <div class="flex flex-col items-center">
                <span class="{{ $figure }}">{{ number_format($active['active_7d']) }}</span>
                <span class="{{ $caption }}">@lang('messages.admin_dash_7_days')</span>
            </div>
            <x-slot:footer>
                @if ($active['change'] !== null)
                    <span class="whitespace-nowrap"><span class="font-medium {{ $tone($active['change']) }}">{{ $pct($active['change']) }}</span> @lang('messages.admin_dash_vs_previous_7_days')</span>
                @elseif (! $active['exact'])
                    {{-- An exact week against an estimated one would read as growth that did
                         not happen, so until both are exact the tile only says what this is. --}}
                    @lang('messages.admin_dash_estimate')
                @endif
            </x-slot:footer>
        @endif
    </x-admin-stat-tile>

    @if ($hosted)
        <x-admin-stat-tile :href="$jump('dash-revenue')" label="MRR" :icon="$icons['paid']"
            tint="bg-emerald-50 dark:bg-emerald-500/10" ink="text-emerald-500" glow="rgba(16, 185, 129, 0.15)">
            @if ($revenue)
                <div class="flex flex-col items-center">
                    <span class="{{ $figure }}">{{ plan_price($revenue['totals']['mrr']) }}</span>
                    <span class="{{ $caption }}">
                        <span class="whitespace-nowrap">@lang('messages.admin_dash_paying') {{ number_format($revenue['totals']['billing_count']) }}</span>
                        <span aria-hidden="true">&middot;</span>
                        <span class="whitespace-nowrap">@lang('messages.trial') {{ number_format($revenue['totals']['trialing_count']) }}</span>
                    </span>
                </div>
                <x-slot:footer>
                    <span class="whitespace-nowrap">ARR {{ plan_price($revenue['totals']['arr']) }}</span>
                </x-slot:footer>
            @endif
        </x-admin-stat-tile>
    @else
        {{-- No billing on this install, so the third number is what its schedules are adding. --}}
        <x-admin-stat-tile :href="$jump('dash-recent-events')" :label="__('messages.admin_dash_events_added')" :icon="$icons['schedule']"
            tint="bg-emerald-50 dark:bg-emerald-500/10" ink="text-emerald-500" glow="rgba(16, 185, 129, 0.15)">
            @if ($events)
                <div class="flex flex-col items-center">
                    <span class="{{ $figure }}">{{ number_format($events['new_30d']) }}</span>
                    <span class="{{ $caption }}">@lang('messages.admin_dash_30_days')</span>
                </div>
                @if ($events['new_change'] !== null)
                    <x-slot:footer>
                        <span class="whitespace-nowrap"><span class="font-medium {{ $tone($events['new_change']) }}">{{ $pct($events['new_change']) }}</span> @lang('messages.admin_dash_vs_previous_30_days')</span>
                    </x-slot:footer>
                @endif
            @endif
        </x-admin-stat-tile>
    @endif

    <x-admin-stat-tile :href="$jump('dash-upcoming')" :label="__('messages.admin_dash_upcoming_events')" :icon="$icons['events']"
        tint="bg-purple-50 dark:bg-purple-500/10" ink="text-purple-500" glow="rgba(168, 85, 247, 0.15)">
        @if ($events)
            <div class="flex flex-col items-center">
                <span class="{{ $figure }}">{{ number_format($events['total']) }}</span>
                <span class="{{ $caption }}">@lang('messages.from_today')</span>
            </div>
            @if ($events['total'] > 0)
                <x-slot:footer>
                    <span class="whitespace-nowrap">@lang('messages.in_person') {{ number_format($events['in_person']) }}</span>
                    <span aria-hidden="true">&middot;</span>
                    <span class="whitespace-nowrap">@lang('messages.online') {{ number_format($events['online']) }}</span>
                </x-slot:footer>
            @endif
        @endif
    </x-admin-stat-tile>
</div>
