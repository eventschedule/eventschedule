@php
    // The addresses this schedule keeps reading for events (App\Models\EventFeed), for the people
    // who run it. Set by RoleController::viewAdmin on this tab only.
    $feeds = $feeds ?? collect();
    $feedsAllowed = \App\Models\EventFeed::allowedFor($role);
    $use24 = get_use_24_hour_time($role);
    $zone = $role->captureTimezone();
    $clock = fn ($at) => $at->copy()->setTimezone($zone)->format($use24 ? 'H:i' : 'g:i A');
    $addUrl = route('role.feeds.create', ['subdomain' => $role->subdomain]);

    // What each feed's state is, in the order it wants somebody: a decision first, then one that
    // cannot be read, one that is waiting for a word, a first read, and last the ones that are fine.
    $states = $feeds->map(function ($feed) use ($feedsAllowed, $clock, $zone) {
        $failingSince = $feed->last_success_at ?? $feed->created_at;
        $failing = $feed->failure_count > 0 && $failingSince->lt(now()->subDay());
        $next = $feed->next_check_at;
        // A try that is due is "within a minute", never "2 minutes ago".
        $nextTry = $next && $next->gt(now()->addMinute()) ? $next : now()->addSeconds(90);
        $read = $feed->last_success_at
            ? __('messages.feeds_read_at', ['time' => $clock($feed->last_success_at), 'next' => $next ? $clock($next) : $clock(now()->addHour())])
            : __('messages.feeds_read_soon');

        return match (true) {
            ! $feedsAllowed => [5, '', __('messages.feeds_status_off_plan'), ''],
            $feed->decide_count > 0 => [0, 'is-warn', trans_choice('messages.feeds_status_decide', $feed->decide_count, ['count' => number_format($feed->decide_count)]), $read],
            $feed->isPaused() => [2, 'is-warn', __('messages.feeds_status_paused'), ''],
            $failing => [1, 'is-bad', $feed->last_success_at
                ? __('messages.feeds_status_failing', ['date' => $feed->last_success_at->copy()->setTimezone($zone)->translatedFormat('D j M')])
                : __('messages.feeds_status_failing_never'),
                __('messages.feeds_trying_again', ['when' => $nextTry->diffForHumans()])],
            $feed->baseline_done_at === null => [3, '', $feed->last_success_at ? __('messages.feeds_status_first') : __('messages.feeds_status_never'), $read],
            default => [4, 'is-on', __('messages.feeds_status_ok'), $read],
        };
    });
    $ordered = $feeds->keys()->sortBy(fn ($index) => [$states[$index][0], mb_strtolower($feeds[$index]->name)])->values();
    $deciding = (int) $feeds->sum('decide_count');
@endphp

@include('feed.partials.styles')

@if (! $feedsAllowed && $feeds->isEmpty())
{{-- Not on this plan, and none to show: what a feed is, and what to do instead today. --}}
<x-plan-gate tier="enterprise" :title="__('messages.feeds_tab')" :subdomain="$role->subdomain" :role="$role"
    :canUpgrade="auth()->id() == $role->user_id" source="feeds"
    :bullets="[__('messages.feeds_gate_bullet_sources'), __('messages.feeds_gate_bullet_edits'), __('messages.feeds_gate_bullet_review'), __('messages.feeds_gate_bullet_gone')]">
    {{ __('messages.feeds_gate_text') }}
    <x-slot name="instead">
        <a href="{{ route('event.show_import_ai', ['subdomain' => $role->subdomain]) }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">{{ __('messages.feeds_import_once') }}</a>
    </x-slot>
</x-plan-gate>
@else

@if (! $feedsAllowed)
{{-- Feeds from when the plan had them. They are not read; what they made is still the owner's
     to publish, skip or remove. --}}
<x-plan-gate tier="enterprise" variant="banner" :title="__('messages.feeds_tab')" :subdomain="$role->subdomain" :role="$role"
    :canUpgrade="auth()->id() == $role->user_id" source="feeds">
    {{ __('messages.feeds_need_enterprise') }}
</x-plan-gate>
@endif

{{-- With no feed yet, the card below says what the page is for and holds the one button. --}}
@if ($feeds->isNotEmpty())
<div class="page-head">
    <p class="page-lead">{{ __('messages.feeds_lead') }}</p>
    <div class="page-actions">
        @if ($feedsAllowed)
        <x-brand-link href="{{ $addUrl }}">
            <svg class="-ms-0.5 me-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            {{ __('messages.feeds_add') }}
        </x-brand-link>
        @endif
    </div>
</div>
@endif

@if ($deciding > 0)
<x-page-notice tone="warn" class="mb-4">
    <p class="font-semibold">{{ trans_choice('messages.feeds_decide_title', $deciding, ['count' => number_format($deciding)]) }}</p>
    <p>{{ __('messages.feeds_decide_text') }}</p>
</x-page-notice>
@endif

@if ($feeds->isEmpty())
<div class="ap-card rounded-xl">
    <x-page-empty :title="__('messages.feeds_empty_title')" :text="__('messages.feeds_empty_text')"
        icon="M12.75 19.5v-.75a7.5 7.5 0 0 0-7.5-7.5H4.5m0-6.75h.75c7.87 0 14.25 6.38 14.25 14.25v.75M6 18.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z">
        <x-brand-link href="{{ $addUrl }}">{{ __('messages.feeds_add') }}</x-brand-link>
    </x-page-empty>
</div>
@else
<div class="ap-card rounded-xl overflow-hidden">
    <table class="page-table feed-list">
        <colgroup><col style="width:19.5rem"><col style="width:5rem"><col style="width:10.5rem"><col><col style="width:5rem"></colgroup>
        <thead>
            <tr>
                <th scope="col">{{ __('messages.feeds_col_feed') }}</th>
                <th scope="col">{{ __('messages.events') }}</th>
                <th scope="col">{{ __('messages.feeds_col_new') }}</th>
                <th scope="col">{{ __('messages.feeds_col_status') }}</th>
                <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ordered as $index)
            @php
                $feed = $feeds[$index];
                [, $tone, $status, $statusSub] = $states[$index];
                $feedUrl = route('role.feeds.show', ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($feed->id)]);
            @endphp
            <tr>
                <td class="c-main">
                    {{-- The feed's name is whatever its source calls itself: text, never markup. --}}
                    <div class="event-list-name feed-name"><a href="{{ $feedUrl }}" v-pre><bdi>{{ $feed->name }}</bdi></a><span class="event-chip">{{ __('messages.feeds_kind_'.$feed->kind) }}</span></div>
                    {{-- The site, never the address: for a private calendar the address is the key to it. --}}
                    <div class="event-list-sub"><span class="feed-addr" dir="ltr">{{ $feed->host }}</span></div>
                </td>
                {{-- A bare number under its column's heading; on a phone, where the heading is
                     gone, the number with its noun in the right number ("1 event"). --}}
                <td><span class="feed-num"><span class="hidden sm:inline">{{ number_format($feed->events_count) }}</span><span class="sm:hidden">{{ trans_choice('messages.feeds_events_count', $feed->events_count, ['count' => number_format($feed->events_count)]) }}</span></span></td>
                <td data-label="{{ __('messages.feeds_col_new') }}">
                    {{ __($feed->publishes() ? 'messages.feeds_mode_publish' : 'messages.feeds_mode_draft') }}
                    @if ($feed->waiting_count > 0)
                    <div class="event-list-sub"><a href="{{ $feedUrl }}#waiting" class="event-link">{{ trans_choice('messages.feeds_waiting', $feed->waiting_count, ['count' => number_format($feed->waiting_count)]) }}</a></div>
                    @endif
                </td>
                <td>
                    <span class="event-status {{ $tone }}">{{ $status }}</span>
                    @if ($statusSub !== '')
                    <div class="event-list-sub">{{ $statusSub }}</div>
                    @endif
                </td>
                <td class="c-actions"><a href="{{ $feedUrl }}" class="event-link">{{ __('messages.feeds_open') }}</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endif
