{{-- On the nexus: the selfhost installs that share their events here, what they list, and the
     visitors sent on to them. Anywhere else: this install's own sharing, from the same figures the
     Settings card shows. With sharing off it is one line, and no event query ran for it. --}}
@php
    $cell = 'rounded-xl px-3 py-2.5';
    $cellLabel = 'text-xs font-medium text-gray-500 dark:text-gray-400';
    $cellValue = 'mt-1 dashboard-stat-value text-2xl font-bold leading-7 text-gray-900 dark:text-white';
    $cellSub = 'mt-1 text-xs text-gray-500 dark:text-gray-400';
    $mode = $federation['mode'] ?? null;
    $settingsUrl = route('admin.settings', ['card' => 'federation']);
@endphp

@if (! $federation)
    <section class="ap-card rounded-xl flex flex-col">
        <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3"><h2 class="text-base font-semibold text-gray-900 dark:text-white">@lang('messages.federation')</h2></div>
        @include('admin.dashboard._failed')
    </section>
@elseif ($mode === 'hub')
    @php
        $installs = $federation['installs'];
        $byStatus = $installs['by_status'];
        $approved = (int) ($byStatus['approved'] ?? 0);
        $clicks = $federation['clicks'];
        $maxClicks = max(1, collect($clicks['top'])->max('clicks') ?? 1);
        $versions = array_slice($installs['by_version'], 0, 5, true);
        $maxVersion = max(1, $versions ? max($versions) : 1);
    @endphp
    <section class="ap-card rounded-xl flex flex-col">
        <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-1 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">@lang('messages.federation')</h2>
            <x-link :href="route('admin.federation')" class="text-sm font-medium whitespace-nowrap">@lang('messages.review')</x-link>
        </div>
        <p class="px-4 sm:px-5 text-sm text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_federation_hub_note')</p>

        @if (array_sum($byStatus) === 0)
            <p class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_fed_empty')</p>
        @else
            <div class="px-4 sm:px-5 mt-4 grid grid-cols-2 xl:grid-cols-4 gap-2">
                <div class="{{ $cell }}" style="background: var(--ap-tint-sunken)">
                    <p class="{{ $cellLabel }}">@lang('messages.admin_dash_installs')</p>
                    <p class="{{ $cellValue }}">{{ number_format($approved) }}</p>
                    <p class="{{ $cellSub }}">
                        <span class="whitespace-nowrap">@lang('messages.waiting') {{ number_format($byStatus['pending'] ?? 0) }}</span>
                        <span aria-hidden="true">&middot;</span>
                        <span class="whitespace-nowrap">@lang('messages.federation_status_suspended') {{ number_format($byStatus['suspended'] ?? 0) }}</span>
                    </p>
                </div>
                <div class="{{ $cell }}" style="background: var(--ap-tint-sunken)">
                    <p class="{{ $cellLabel }}">@lang('messages.active')</p>
                    <p class="{{ $cellValue }}">{{ number_format($installs['active_30d']) }}</p>
                    <p class="{{ $cellSub }}">@lang('messages.admin_dash_fed_active_note')</p>
                </div>
                <div class="{{ $cell }}" style="background: var(--ap-tint-sunken)">
                    <p class="{{ $cellLabel }}">@lang('messages.admin_dash_live_listings')</p>
                    <p class="{{ $cellValue }}">{{ number_format($federation['listings']['live']) }}</p>
                    <p class="{{ $cellSub }}"><span class="whitespace-nowrap">@lang('messages.admin_dash_schedules_sharing') {{ number_format($federation['listings']['schedules']) }}</span></p>
                </div>
                <div class="{{ $cell }}" style="background: var(--ap-tint-sunken)">
                    <p class="{{ $cellLabel }}">@lang('messages.admin_dash_clicks_sent')</p>
                    <p class="{{ $cellValue }}">{{ number_format($clicks['total']) }}</p>
                    <p class="{{ $cellSub }}">
                        @if ($clicks['change'] !== null)
                            <span class="whitespace-nowrap"><span class="font-medium {{ $tone($clicks['change']) }}">{{ $pct($clicks['change']) }}</span> @lang('messages.admin_dash_vs_previous_30_days')</span>
                        @else
                            @lang('messages.admin_dash_last_30_days')
                        @endif
                    </p>
                </div>
            </div>

            <div class="px-2 sm:px-3 mt-4 pb-3 grid lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] gap-x-6 gap-y-4">
                <div>
                    <div class="px-3 pb-1 flex items-baseline justify-between">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.admin_dash_most_clicked')</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400 flex gap-3"><span class="w-14 text-end">@lang('messages.admin_dash_listings')</span><span class="w-12 text-end">@lang('messages.clicks')</span></span>
                    </div>
                    @if ($clicks['top'])
                        <ul>
                            @foreach ($clicks['top'] as $install)
                                <li class="relative flex items-center gap-3 px-3 py-2 text-sm rounded-md">
                                    <span aria-hidden="true" class="absolute inset-y-0.5 start-0 rounded-md bg-[var(--brand-blue-a10)]" style="width: {{ max(2, round($install['clicks'] / $maxClicks * 100)) }}%"></span>
                                    <span class="relative min-w-0 flex-1 flex items-baseline gap-2">
                                        <span class="truncate text-gray-900 dark:text-gray-100"><bdi>{{ $install['name'] }}</bdi></span>
                                        {{-- Text, never a link: an install's own address lands a reviewer on a
                                             login page. /admin/federation links each schedule instead. --}}
                                        @if ($install['host'])
                                            <span class="hidden sm:inline truncate text-xs text-gray-500 dark:text-gray-400"><bdi dir="ltr">{{ $install['host'] }}</bdi></span>
                                        @endif
                                    </span>
                                    <span class="relative tabular-nums text-end text-xs text-gray-500 dark:text-gray-400 w-14">{{ number_format($install['listings']) }}</span>
                                    <span class="relative tabular-nums text-end text-gray-700 dark:text-gray-300 w-12">{{ number_format($install['clicks']) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="px-3 py-2 text-sm text-gray-400 dark:text-gray-500">-</p>
                    @endif
                </div>

                <div>
                    <div class="px-3 pb-1 flex items-baseline justify-between">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.app_versions')</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_installs')</span>
                    </div>
                    @if ($versions)
                        <ul>
                            @foreach ($versions as $version => $count)
                                <li class="relative flex items-center gap-3 px-3 py-2 text-sm rounded-md">
                                    <span aria-hidden="true" class="absolute inset-y-0.5 start-0 rounded-md bg-gray-100 dark:bg-white/[0.04]" style="width: {{ max(2, round($count / $maxVersion * 100)) }}%"></span>
                                    <span class="relative flex-1 min-w-0 truncate text-gray-900 dark:text-gray-100"><bdi dir="ltr">{{ $version }}</bdi></span>
                                    <span class="relative tabular-nums text-gray-700 dark:text-gray-300">{{ number_format($count) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="px-3 py-2 text-sm text-gray-400 dark:text-gray-500">-</p>
                    @endif
                </div>
            </div>
        @endif
    </section>
@elseif ($mode === 'sender')
    @php
        $statusLabels = [
            'pending' => __('messages.federation_status_pending'),
            'approved' => __('messages.federation_status_approved'),
            'suspended' => __('messages.federation_status_suspended'),
        ];
        $statusDots = ['approved' => 'bg-green-500', 'pending' => 'bg-amber-500', 'suspended' => 'bg-red-500'];
    @endphp
    <section class="ap-card rounded-xl flex flex-col">
        <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-1 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">@lang('messages.federation')</h2>
            <x-link :href="$settingsUrl" class="text-sm font-medium whitespace-nowrap">@lang('messages.settings')</x-link>
        </div>
        <p class="px-4 sm:px-5 text-sm text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_fed_sender_note')</p>

        <div class="px-4 sm:px-5 mt-4 pb-5 grid grid-cols-2 xl:grid-cols-4 gap-2">
            <div class="{{ $cell }}" style="background: var(--ap-tint-sunken)">
                <p class="{{ $cellLabel }}">@lang('messages.status')</p>
                <p class="{{ $cellValue }}">
                    <span class="inline-flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $statusDots[$federation['status']] ?? 'bg-gray-400' }}" aria-hidden="true"></span>{{ $statusLabels[$federation['status']] ?? __('messages.federation_not_connected') }}
                    </span>
                </p>
            </div>
            <div class="{{ $cell }}" style="background: var(--ap-tint-sunken)">
                <p class="{{ $cellLabel }}">@lang('messages.admin_dash_last_synced')</p>
                <p class="{{ $cellValue }}">{{ $federation['last_synced_at'] ? $federation['last_synced_at']->shortRelativeDiffForHumans() : '-' }}</p>
                @if (! $federation['last_synced_at'])
                    <p class="{{ $cellSub }}">@lang('messages.federation_never_synced')</p>
                @endif
            </div>
            <div class="{{ $cell }}" style="background: var(--ap-tint-sunken)">
                <p class="{{ $cellLabel }}">@lang('messages.admin_dash_events_shared')</p>
                <p class="{{ $cellValue }}">{{ __('messages.admin_dash_count_of_total', ['count' => number_format($federation['totals']['sent']), 'total' => number_format($federation['totals']['total'])]) }}</p>
                @if ($federation['listings_url'])
                    <p class="{{ $cellSub }}"><x-link :href="$federation['listings_url']" target="_blank">@lang('messages.federation_see_listings')</x-link></p>
                @endif
            </div>
            <div class="{{ $cell }}" style="background: var(--ap-tint-sunken)">
                <p class="{{ $cellLabel }}">@lang('messages.admin_dash_schedules_undecided')</p>
                <p class="{{ $cellValue }}">{{ number_format($federation['undecided']) }}</p>
            </div>
        </div>
    </section>
@else
    <p class="px-1 text-sm text-gray-500 dark:text-gray-400">
        @lang('messages.admin_dash_fed_off')
        <x-link :href="$settingsUrl">@lang('messages.settings')</x-link>
    </p>
@endif
