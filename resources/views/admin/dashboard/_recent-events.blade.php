{{-- The newest events an admin can be shown. A row is words only (2026-11, as the main
     dashboard's lists): no flyer or icon tile, and the flag (a draft, a guest submission, an
     import) and a burst from one schedule ("+N", one row for the lot) are grey words, not chips.
     On a phone the date gets a line of its own so it is never the part cut. --}}
@php
    $modeLabels = [
        'in_person' => __('messages.in_person'),
        'online' => __('messages.online'),
        'hybrid' => __('messages.hybrid'),
        'no_location' => __('messages.admin_dash_no_location'),
    ];
    $flagLabels = [
        'draft' => __('messages.draft'),
        'submitted' => __('messages.submitted'),
        'imported' => __('messages.admin_dash_imported'),
    ];
    $rows = $recentEvents['rows'] ?? [];
@endphp

<section id="dash-recent-events" class="ap-card rounded-xl flex flex-col overflow-hidden scroll-mt-20" data-new-list>
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
            @lang('messages.admin_dash_recent_events')
            <span data-new-chip data-label="{{ __('messages.new') }}" title="{{ __('messages.admin_dash_new_since_visit') }}"
                class="hidden ms-2 align-middle inline-flex items-center px-1.5 py-0.5 rounded-md text-[11px] font-medium leading-4 bg-[var(--brand-blue-a10)] text-[var(--brand-blue)]"></span>
        </h2>
        @if ($events)
            <span class="text-xs text-gray-500 dark:text-gray-400">
                <span class="whitespace-nowrap">@lang('messages.total') {{ number_format($events['all_time']) }}</span>
                <span aria-hidden="true">&middot;</span>
                <span class="whitespace-nowrap">@lang('messages.admin_dash_30_days') <span dir="ltr">+{{ number_format($events['new_30d']) }}</span></span>
            </span>
        @endif
    </div>

    @if (! $recentEvents)
        @include('admin.dashboard._failed')
    @elseif (empty($rows))
        <p class="px-5 pb-5 text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_events_yet')</p>
    @else
        <ul class="divide-y divide-gray-100 dark:divide-white/[0.06]" style="border-top: 1px solid var(--ap-hairline)">
            @foreach ($rows as $index => $row)
                @php
                    $flag = $row['flag'] ? $flagLabels[$row['flag']] : null;
                    // A burst says how many came with this one: on the flag when there is one.
                    $chip = trim(($flag ?? '').($row['more'] > 0 ? ($flag ? ', ' : '').'+'.number_format($row['more']) : ''));
                    $when = $row['series'] ? __('messages.recurring') : $row['when'];
                @endphp
                <li class="flex items-center gap-3 px-4 sm:px-5 py-3 hover:bg-gray-50 dark:hover:bg-black/10 transition-all duration-200 {{ $index >= \App\Services\AdminDashboard::LIST_VISIBLE ? 'hidden' : '' }}"
                    data-created="{{ $row['created_at']?->getTimestamp() }}"
                    @if ($index >= \App\Services\AdminDashboard::LIST_VISIBLE) data-more-row @endif>
                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-2 min-w-0">
                            @if ($row['url'] && $row['public'])
                                {{-- The guest page, in a new tab: the arrow says so (components/public-page-link). --}}
                                <a href="{{ $row['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ __('messages.admin_open_public_page', ['label' => $row['name']]) }}"
                                   class="public-page-name group inline-flex items-center gap-1 min-w-0 text-sm font-medium text-gray-900 dark:text-white rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"><span class="truncate group-hover:underline"><bdi>{{ $row['name'] }}</bdi></span><x-public-page-icon class="public-page-icon text-gray-500 dark:text-gray-400 transition-all duration-200" /></a>
                            @elseif ($row['url'])
                                {{-- A draft, or an event no schedule serves yet: its edit page, so no arrow. --}}
                                <a href="{{ $row['url'] }}" target="_blank" rel="noopener noreferrer" class="truncate text-sm font-medium text-gray-900 dark:text-white hover:underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"><bdi>{{ $row['name'] }}</bdi></a>
                            @else
                                <span class="truncate text-sm font-medium text-gray-900 dark:text-white"><bdi>{{ $row['name'] }}</bdi></span>
                            @endif
                            @if ($chip !== '')
                                <span class="hidden sm:inline text-xs whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $chip }}</span>
                            @endif
                        </p>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            @if ($row['schedule'] && ($row['schedule_admin_url'] ?? null))
                                {{-- The schedule, opened in the admin (the event's own name leads to its page). --}}
                                <a href="{{ $row['schedule_admin_url'] }}" title="{{ __('messages.admin_open_in_admin', ['label' => $row['schedule']]) }}" class="rounded hover:underline hover:text-gray-900 dark:hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"><bdi>{{ $row['schedule'] }}</bdi></a>
                            @elseif ($row['schedule'])
                                <bdi>{{ $row['schedule'] }}</bdi>
                            @endif
                            <span class="hidden sm:inline">
                                @if ($row['schedule'] && $when)
                                    &middot;
                                @endif
                                {{ $when }}
                            </span>
                        </p>
                        <p class="sm:hidden mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-700 dark:text-gray-300">
                            @if ($when)
                                <span class="whitespace-nowrap">{{ $when }}</span>
                            @endif
                            @if ($chip !== '')
                                <span class="whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $chip }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="shrink-0 text-end">
                        <p class="flex items-center justify-end gap-1.5 text-xs text-gray-700 dark:text-gray-300 whitespace-nowrap">
                            <span data-new-dot role="img" aria-label="{{ __('messages.admin_dash_new_since_visit') }}" class="hidden w-1.5 h-1.5 rounded-full bg-[var(--brand-blue)] shrink-0"></span>
                            {{ $row['created_at']?->shortRelativeDiffForHumans() }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $modeLabels[$row['mode']] }}</p>
                    </div>
                </li>
            @endforeach
        </ul>

        @if (count($rows) > \App\Services\AdminDashboard::LIST_VISIBLE)
            <div class="mt-auto px-4 sm:px-5 py-3 flex items-center justify-between gap-3" style="border-top: 1px solid var(--ap-hairline)">
                @include('admin.dashboard._show-more', ['count' => count($rows)])
            </div>
        @endif
    @endif
</section>
