{{-- The newest events an admin can be shown, with the flyer (or the schedule's photo standing in
     for one). A draft, a guest submission and an import carry a flag; a burst from one schedule is
     one row with "+N". On a phone the date gets a line of its own so it is never the part cut. --}}
@php
    $modeDots = [
        'in_person' => 'bg-cyan-600',
        'online' => 'bg-purple-600 dark:bg-purple-500',
        'hybrid' => 'bg-pink-600',
        'no_location' => 'bg-gray-400 dark:bg-gray-500',
    ];
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
                <li class="flex items-center gap-3 px-4 sm:px-5 py-2.5 hover:bg-gray-50 dark:hover:bg-black/10 transition-all duration-200 {{ $index >= \App\Services\AdminDashboard::LIST_VISIBLE ? 'hidden' : '' }}"
                    data-created="{{ $row['created_at']?->getTimestamp() }}"
                    @if ($index >= \App\Services\AdminDashboard::LIST_VISIBLE) data-more-row @endif>
                    @if ($row['image'])
                        <img src="{{ $row['image'] }}" alt="" width="56" height="56" loading="lazy" class="w-14 h-14 rounded-xl object-cover shrink-0">
                    @else
                        <span class="w-14 h-14 rounded-xl shrink-0 flex items-center justify-center text-gray-500 dark:text-gray-400" style="background: var(--ap-tint-2)" aria-hidden="true">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ \App\Utils\RealtimeIcons::PATHS['schedule'] }}" /></svg>
                        </span>
                    @endif

                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-2 min-w-0">
                            @if ($row['url'])
                                <a href="{{ $row['url'] }}" target="_blank" rel="noopener noreferrer" class="truncate text-sm font-medium text-gray-900 dark:text-white hover:underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"><bdi>{{ $row['name'] }}</bdi></a>
                            @else
                                <span class="truncate text-sm font-medium text-gray-900 dark:text-white"><bdi>{{ $row['name'] }}</bdi></span>
                            @endif
                            @if ($chip !== '')
                                <span class="hidden sm:inline-flex items-center px-1.5 py-0.5 rounded-md text-[11px] font-medium leading-4 whitespace-nowrap bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ $chip }}</span>
                            @endif
                        </p>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            @if ($row['schedule'])
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
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[11px] font-medium leading-4 whitespace-nowrap bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ $chip }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="shrink-0 text-end">
                        <p class="flex items-center justify-end gap-1.5 text-xs text-gray-700 dark:text-gray-300 whitespace-nowrap">
                            <span data-new-dot role="img" aria-label="{{ __('messages.admin_dash_new_since_visit') }}" class="hidden w-1.5 h-1.5 rounded-full bg-[var(--brand-blue)] shrink-0"></span>
                            {{ $row['created_at']?->shortRelativeDiffForHumans() }}
                        </p>
                        {{-- The dot carries which, the text stays in the page's ink. --}}
                        <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center justify-end gap-1.5 whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full {{ $modeDots[$row['mode']] }}" aria-hidden="true"></span>{{ $modeLabels[$row['mode']] }}
                        </p>
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
