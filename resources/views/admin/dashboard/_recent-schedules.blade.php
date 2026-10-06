{{-- The newest real schedules: claimed, live, not demo content. data-created is what the page's
     script compares with the admin's last visit to mark the new ones. --}}
@php
    $planTones = [
        'trial' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
        'pro' => 'bg-[var(--brand-blue-a10)] text-[var(--brand-blue)]',
        'enterprise' => 'bg-[var(--brand-blue-a10)] text-[var(--brand-blue)]',
    ];
@endphp

<section id="dash-recent" class="ap-card rounded-xl flex flex-col overflow-hidden scroll-mt-20" data-new-list>
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
            @lang('messages.admin_dash_recent_schedules')
            <span data-new-chip data-label="{{ __('messages.new') }}" title="{{ __('messages.admin_dash_new_since_visit') }}"
                class="hidden ms-2 align-middle inline-flex items-center px-1.5 py-0.5 rounded-md text-[11px] font-medium leading-4 bg-[var(--brand-blue-a10)] text-[var(--brand-blue)]"></span>
        </h2>
        @if ($schedules)
            <span class="text-xs text-gray-500 dark:text-gray-400">
                <span class="whitespace-nowrap">@lang('messages.total') {{ number_format($schedules['total']) }}</span>
                <span aria-hidden="true">&middot;</span>
                <span class="whitespace-nowrap">@lang('messages.admin_dash_30_days') <span dir="ltr">+{{ number_format($schedules['new_30d']) }}</span></span>
            </span>
        @endif
    </div>

    @if (! $schedules)
        @include('admin.dashboard._failed')
    @elseif (empty($schedules['rows']))
        <p class="px-5 pb-5 text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_schedules_yet')</p>
    @else
        <ul class="divide-y divide-gray-100 dark:divide-white/[0.06]" style="border-top: 1px solid var(--ap-hairline)">
            @foreach ($schedules['rows'] as $index => $row)
                <li class="flex items-center gap-3 px-4 sm:px-5 py-2.5 hover:bg-gray-50 dark:hover:bg-black/10 transition-all duration-200 {{ $index >= \App\Services\AdminDashboard::LIST_VISIBLE ? 'hidden' : '' }}"
                    data-created="{{ $row['created_at']?->getTimestamp() }}"
                    @if ($index >= \App\Services\AdminDashboard::LIST_VISIBLE) data-more-row @endif>
                    @if ($row['image'])
                        <img src="{{ $row['image'] }}" alt="" width="56" height="56" loading="lazy" class="w-14 h-14 rounded-xl object-cover shrink-0">
                    @else
                        <span class="w-14 h-14 rounded-xl shrink-0 flex items-center justify-center text-lg font-semibold text-gray-500 dark:text-gray-400" style="background: var(--ap-tint-2)" aria-hidden="true">{{ mb_strtoupper(mb_substr((string) $row['name'], 0, 1)) }}</span>
                    @endif

                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-2 min-w-0">
                            @if ($row['url'])
                                <a href="{{ $row['url'] }}" target="_blank" rel="noopener noreferrer" class="truncate text-sm font-medium text-gray-900 dark:text-white hover:underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"><bdi>{{ $row['name'] }}</bdi></a>
                            @else
                                <span class="truncate text-sm font-medium text-gray-900 dark:text-white"><bdi>{{ $row['name'] }}</bdi></span>
                            @endif
                            @if ($row['plan'])
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[11px] font-medium leading-4 whitespace-nowrap {{ $planTones[$row['plan']] }}">{{ __('messages.'.$row['plan']) }}</span>
                            @endif
                        </p>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ __('messages.'.$row['type']) }}
                            @if ($row['place'])
                                &middot; <bdi>{{ $row['place'] }}</bdi>
                            @endif
                            @if ($row['owner'])
                                &middot; <bdi>{{ $row['owner'] }}</bdi>
                            @endif
                        </p>
                    </div>

                    <div class="shrink-0 text-end">
                        <p class="flex items-center justify-end gap-1.5 text-xs text-gray-700 dark:text-gray-300 whitespace-nowrap">
                            <span data-new-dot role="img" aria-label="{{ __('messages.admin_dash_new_since_visit') }}" class="hidden w-1.5 h-1.5 rounded-full bg-[var(--brand-blue)] shrink-0"></span>
                            {{ $row['created_at']?->shortRelativeDiffForHumans() }}
                        </p>
                        @if ($row['events'] > 0)
                            <p class="text-xs whitespace-nowrap text-gray-500 dark:text-gray-400">{{ trans_choice('messages.realtime_count_events', $row['events'], ['count' => number_format($row['events'])]) }}</p>
                        @else
                            {{-- A schedule with nothing on it is the one worth a second look. --}}
                            <p class="text-xs whitespace-nowrap text-amber-700 dark:text-amber-400">@lang('messages.realtime_no_events_yet')</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-auto px-4 sm:px-5 py-3 flex items-center justify-between gap-3" style="border-top: 1px solid var(--ap-hairline)">
            @include('admin.dashboard._show-more', ['count' => count($schedules['rows'])])
            <x-link :href="route('admin.schedules')" class="text-sm font-medium whitespace-nowrap">@lang('messages.all_schedules')</x-link>
        </div>
    @endif
</section>
