{{-- Events with something still to come, by how people attend. $wide lays the three blocks out
     in a row for an install with no revenue card beside this one. Colour is on the bar and the
     dots; the words stay in the page's ink. --}}
@php
    $wide = $wide ?? false;
    $modes = [
        ['in_person', __('messages.in_person'), 'bg-cyan-600'],
        ['online', __('messages.online'), 'bg-purple-600 dark:bg-purple-500'],
        ['hybrid', __('messages.hybrid'), 'bg-pink-600'],
        ['no_location', __('messages.admin_dash_no_location'), 'bg-gray-400 dark:bg-gray-500'],
    ];
    $sub = 'text-xs font-medium text-gray-500 dark:text-gray-400 px-2 pb-1';
    $total = $events['total'] ?? 0;
    $maxCountry = $events ? max(1, collect($events['countries'])->max('count') ?? 1) : 1;
@endphp

<section id="dash-upcoming" class="ap-card rounded-xl flex flex-col scroll-mt-20">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">@lang('messages.admin_dash_upcoming_events')</h2>
        <span class="text-xs text-gray-500 dark:text-gray-400">@lang('messages.from_today')</span>
    </div>

    @if (! $events)
        @include('admin.dashboard._failed')
    @else
        <div class="px-4 sm:px-5">
            <p class="flex items-baseline gap-2">
                <span class="dashboard-stat-value text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($total) }}</span>
                <span class="text-sm text-gray-700 dark:text-gray-300">@lang('messages.admin_dash_events_to_come')</span>
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                <span class="whitespace-nowrap">@lang('messages.admin_dash_one_off') {{ number_format($events['one_off']) }}</span>
                <span aria-hidden="true">&middot;</span>
                <span class="whitespace-nowrap">@lang('messages.admin_dash_recurring_series') {{ number_format($events['recurring']) }}</span>
                <span aria-hidden="true">&middot;</span>
                <span>@lang('messages.admin_dash_series_note')</span>
            </p>

            @if ($total > 0)
                <div class="mt-4 flex h-2.5 gap-0.5" role="img" aria-label="{{ __('messages.admin_dash_how_attend') }}">
                    @foreach ($modes as [$key, $label, $color])
                        @if ($events[$key] > 0)
                            <span class="{{ $color }} rounded-sm" style="width: {{ $events[$key] / $total * 100 }}%" title="{{ $label }} {{ number_format($events[$key]) }}"></span>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <div class="px-2 sm:px-3 mt-3 grid sm:grid-cols-2 {{ $wide ? 'lg:grid-cols-3' : '' }} gap-x-6 gap-y-4 pb-4">
            <div>
                <p class="{{ $sub }}">@lang('messages.admin_dash_how_attend')</p>
                <dl>
                    @foreach ($modes as [$key, $label, $color])
                        <div class="flex items-center gap-2 px-2 py-1.5 text-sm">
                            <span class="w-2.5 h-2.5 rounded-sm shrink-0 {{ $color }}" aria-hidden="true"></span>
                            <dt class="flex-1 min-w-0 truncate text-gray-900 dark:text-gray-100">{{ $label }}</dt>
                            <dd class="tabular-nums text-xs text-gray-500 dark:text-gray-400 w-9 text-end">{{ $total > 0 ? round($events[$key] / $total * 100) : 0 }}%</dd>
                            <dd class="tabular-nums text-gray-700 dark:text-gray-300 w-12 text-end">{{ number_format($events[$key]) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div>
                <p class="{{ $sub }}">@lang('messages.admin_dash_by_country')</p>
                @if ($events['countries'])
                    <ul>
                        @foreach ($events['countries'] as $country)
                            <li class="relative flex items-center gap-2 px-2 py-1.5 text-sm rounded-md">
                                <span aria-hidden="true" class="absolute inset-y-0.5 start-0 rounded-md bg-gray-100 dark:bg-white/[0.04]" style="width: {{ max(2, round($country['count'] / $maxCountry * 100)) }}%"></span>
                                <span class="relative iti__flag iti__{{ preg_replace('/[^a-z]/', '', $country['code']) }} shrink-0" aria-hidden="true"></span>
                                <span class="relative flex-1 min-w-0 truncate text-gray-900 dark:text-gray-100">{{ $country['name'] }}</span>
                                <span class="relative tabular-nums text-gray-700 dark:text-gray-300">{{ number_format($country['count']) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="px-2 py-1.5 text-sm text-gray-400 dark:text-gray-500">-</p>
                @endif
            </div>

            {{-- One-off events only: a series is known to be running, not when it next happens.
                 All three run from now; "today" would be somebody's day and not everybody's. --}}
            <div class="sm:col-span-2 {{ $wide ? 'lg:col-span-1' : '' }}">
                <p class="{{ $sub }}">@lang('messages.admin_dash_one_off_by_start')</p>
                <div class="grid grid-cols-3 {{ $wide ? 'lg:grid-cols-1' : '' }} gap-2">
                    @foreach ([[__('messages.admin_dash_next_24_hours'), $events['next_24h']], [__('messages.admin_dash_next_7_days'), $events['next_7']], [__('messages.admin_dash_next_30_days'), $events['next_30']]] as [$label, $value])
                        <div class="rounded-xl px-3 py-2.5" style="background: var(--ap-tint-sunken)">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
                            <p class="mt-1 dashboard-stat-value text-2xl font-bold leading-7 text-gray-900 dark:text-white">{{ number_format($value) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-auto px-4 sm:px-5 py-3 text-xs text-gray-500 dark:text-gray-400" style="border-top: 1px solid var(--ap-hairline)">@lang('messages.admin_dash_upcoming_note')</div>
    @endif
</section>
