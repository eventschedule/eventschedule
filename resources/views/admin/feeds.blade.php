<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'feeds'])

    {{-- Every feed on the install. The site a feed reads, never its address; what a failing one
         answered as a reason and a status code, never a message. --}}
    @php
        $figure = 'dashboard-stat-value text-3xl font-bold text-center text-gray-900 dark:text-white';
        $filtered = request()->filled('search') || request()->filled('state');
        $select = 'block rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]';
        $when = fn ($moment) => $moment ? $moment->copy()->setTimezone(auth()->user()->timezone ?: config('app.timezone'))->translatedFormat('D j M, H:i') : null;
    @endphp

    <div class="page-head">
        <p class="page-lead">{{ __('messages.feeds_admin_lead') }}</p>
    </div>

    <div class="page-shell page-stack">
        @if ($manyFailing)
        <x-page-notice tone="warn">{{ __('messages.feeds_admin_many_failing') }}</x-page-notice>
        @endif

        <div class="grid grid-cols-3 gap-4">
            <x-admin-stat-tile :label="__('messages.total')" icon="M12.75 19.5v-.75a7.5 7.5 0 00-7.5-7.5H4.5m0-6.75h.75c7.87 0 14.25 6.38 14.25 14.25v.75M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"
                tint="bg-gray-100 dark:bg-gray-500/10" ink="text-gray-500" glow="rgba(107, 114, 128, 0.15)">
                <span class="{{ $figure }}">{{ number_format($total) }}</span>
            </x-admin-stat-tile>
            <x-admin-stat-tile :label="__('messages.feeds_admin_failing')" icon="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                tint="bg-red-50 dark:bg-red-500/10" ink="text-red-500" glow="rgba(239, 68, 68, 0.15)">
                <span class="{{ $figure }}">{{ number_format($failing) }}</span>
            </x-admin-stat-tile>
            <x-admin-stat-tile :label="__('messages.feeds_status_paused')" icon="M15.75 5.25v13.5m-7.5-13.5v13.5"
                tint="bg-amber-50 dark:bg-amber-500/10" ink="text-amber-500" glow="rgba(245, 158, 11, 0.15)">
                <span class="{{ $figure }}">{{ number_format($paused) }}</span>
            </x-admin-stat-tile>
        </div>

        <div>
            <form method="GET" action="{{ route('admin.feeds') }}" class="page-filters">
                <div class="page-filter is-grow">
                    <label for="search">@lang('messages.search')</label>
                    <x-text-input id="search" name="search" type="text" class="block w-full" :value="request('search')" :placeholder="__('messages.feeds_admin_search')" autocomplete="off" />
                </div>
                <label class="page-filter">
                    <span>@lang('messages.status')</span>
                    <select id="state" name="state" class="{{ $select }}">
                        <option value="">@lang('messages.all')</option>
                        <option value="failing" {{ request('state') === 'failing' ? 'selected' : '' }}>@lang('messages.feeds_admin_failing')</option>
                        <option value="paused" {{ request('state') === 'paused' ? 'selected' : '' }}>@lang('messages.feeds_status_paused')</option>
                        <option value="waiting" {{ request('state') === 'waiting' ? 'selected' : '' }}>@lang('messages.feeds_admin_waiting')</option>
                    </select>
                </label>
                <div class="is-end">
                    @if ($filtered)
                    <a href="{{ route('admin.feeds') }}" class="page-tool">@lang('messages.clear')</a>
                    @endif
                    <x-brand-button type="submit" size="sm" class="!py-2">@lang('messages.search')</x-brand-button>
                </div>
            </form>

            @if ($feeds->count() > 0)
            <div class="ap-card rounded-xl overflow-hidden">
                <div class="page-scroll">
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.schedule')</th>
                            <th scope="col">@lang('messages.feeds_col_feed')</th>
                            <th scope="col">@lang('messages.status')</th>
                            <th scope="col">@lang('messages.feeds_admin_last_good')</th>
                            <th scope="col">@lang('messages.feeds_admin_next_try')</th>
                            <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($feeds as $feed)
                        @php
                            $hash = \App\Utils\UrlUtils::encodeId($feed->id);
                            $allowed = \App\Models\EventFeed::allowedFor($feed->role);
                            $isFailing = ! $feed->isPaused() && $feed->failure_count > 0;
                            $http = $feed->stats['last_http'] ?? null;
                            $waiting = $feed->waiting_count + $feed->decide_count;
                        @endphp
                        <tr>
                            <td class="c-main">
                                <a href="{{ route('admin.schedules.edit', $feed->role) }}" class="c-strong"><bdi>{{ $feed->role->name }}</bdi></a>
                                <span class="c-sub" dir="ltr">{{ $feed->role->subdomain }}</span>
                            </td>
                            <td class="c-wrap">
                                <span class="c-strong"><bdi>{{ $feed->name }}</bdi></span>
                                <span class="c-sub"><span dir="ltr">{{ $feed->host }}</span> &middot; {{ __('messages.feeds_kind_'.$feed->kind) }} &middot; {{ trans_choice('messages.feeds_events_count', $feed->events_count, ['count' => number_format($feed->events_count)]) }}</span>
                            </td>
                            <td>
                                @if (! $allowed)
                                <span class="event-status">{{ __('messages.feeds_status_off_plan') }}</span>
                                @elseif ($feed->isPaused())
                                <span class="event-status is-warn">{{ __('messages.feeds_status_paused') }}</span>
                                <span class="c-sub">{{ __('messages.feeds_admin_reason_'.($feed->pause_reason ?: 'owner')) }}</span>
                                @elseif ($isFailing)
                                <span class="event-status is-bad">{{ __('messages.feeds_admin_failing') }}</span>
                                {{-- A reason key and a status code. Never what the other server said. --}}
                                <span class="c-sub"><span class="c-mono" dir="ltr">{{ $feed->last_status }}{{ $http ? ' '.$http : '' }}</span> &middot; {{ trans_choice('messages.feeds_admin_tries', $feed->failure_count, ['count' => number_format($feed->failure_count)]) }}</span>
                                @else
                                <span class="event-status is-on">{{ __('messages.feeds_status_ok') }}</span>
                                @endif
                                @if ($waiting > 0)
                                <span class="c-sub">{{ trans_choice('messages.feeds_admin_waiting_count', $waiting, ['count' => number_format($waiting)]) }}</span>
                                @endif
                            </td>
                            <td class="c-date">{{ $when($feed->last_success_at) ?? __('messages.never') }}</td>
                            <td class="c-date">{{ $feed->isPaused() || ! $allowed ? '' : ($when($feed->next_check_at) ?? '') }}</td>
                            <td class="c-actions">
                                @if ($feed->isPaused())
                                <form method="POST" action="{{ route('admin.feeds.resume', ['hash' => $hash]) }}">
                                    @csrf
                                    <button type="submit" class="event-link">@lang('messages.resume')</button>
                                </form>
                                @elseif ($allowed)
                                <form method="POST" action="{{ route('admin.feeds.read', ['hash' => $hash]) }}">
                                    @csrf
                                    <button type="submit" class="event-link">@lang('messages.feeds_read_now')</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            @elseif ($filtered || $feeds->currentPage() > 1)
            <div class="ap-card rounded-xl">
                <x-page-empty :title="__('messages.no_results_found')" icon="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z">
                    <x-secondary-link :href="route('admin.feeds')">@lang('messages.clear_filters')</x-secondary-link>
                </x-page-empty>
            </div>
            @else
            <div class="ap-card rounded-xl">
                <x-page-empty :title="__('messages.feeds_empty_title')" :text="__('messages.feeds_admin_empty')"
                    icon="M12.75 19.5v-.75a7.5 7.5 0 00-7.5-7.5H4.5m0-6.75h.75c7.87 0 14.25 6.38 14.25 14.25v.75M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
            </div>
            @endif

            @if ($feeds->hasPages())
            <div class="page-pager">
                {{ $feeds->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-admin-layout>
