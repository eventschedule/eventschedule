@php
    $hasSubscribers = $subscribers && $subscribers->total() > 0;
    $followerSortBy = $sortBy ?? '';
    $followerSortDir = $sortDir ?? 'desc';
    $sortMark = fn (string $column) => $followerSortBy === $column ? ($followerSortDir === 'asc' ? ' ↑' : ' ↓') : '';
    $initialOf = fn ($name, $email) => mb_strtoupper(mb_substr(trim((string) ($name ?: $email)), 0, 1));
@endphp

@if($followers->isEmpty() && ! $hasSubscribers)

{{-- Nobody yet: what to share to get the first one. A link the organizer can copy, deep-linked
     straight to the subscribe form; the QR code points at the same place. --}}
@php
    $subscribeShareUrl = ($role->getGuestUrl(true) ?: $role->getGuestUrl());
    $subscribeShareUrl .= (str_contains($subscribeShareUrl, '?') ? '&' : '?').'subscribe=1';
@endphp
<div class="ap-card rounded-xl page-empty">
    <svg class="page-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
    </svg>
    <h3>{{ __('messages.no_followers') }}</h3>
    <p>{{ __('messages.share_your_event_schedule_link') }}</p>
    <div class="page-actions">
        <x-secondary-link href="{{ route('role.view_guest', ['subdomain' => $role->subdomain, 'embed' => 'true', 'form' => 'subscribe']) }}"
            class="js-open-subscribe-embed">
            {{ __('messages.embed_subscribe_form') }}
        </x-secondary-link>
        <x-secondary-link href="{{ route('role.qr_code', ['subdomain' => $role->subdomain]) }}">
            {{ __('messages.qr_code') }}
        </x-secondary-link>
    </div>
    <div class="page-empty-link">
        <label for="subscribe-share-url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ __('messages.audience_share_link') }}
        </label>
        <x-copy-link id="subscribe-share-url" :value="$subscribeShareUrl" />
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.audience_share_link_help') }}</p>
    </div>
</div>

@else

{{-- One deduped statement of the audience, above both lists.
     Necessary rather than decorative: a confirmed subscriber now also holds a follower pivot, so
     accountOnlyFollowers() excludes them from the Followers list - and an owner watching this ship
     would otherwise see that count drop with no explanation while a list called "Email
     subscribers" quietly became the main one. Adding the two totals by hand is exactly the
     arithmetic this saves them. --}}
@php
    // The two figures beside it, summed - not a headcount of the lists below. NOT followers +
    // every subscriber row: accountOnlyFollowers() excludes a follower only when the subscription
    // created their pivot, so an account follower who also has a pending row is in both
    // paginators, and people who pressed Unsubscribe would be inside a number called "can be
    // emailed".
    $audienceMailable = $subscriberStats['confirmed'] ?? 0;
    $audienceFollowers = $followersWithRoles->total();
@endphp
<div class="page-head">
    <div class="ap-card rounded-xl page-stats">
        <div class="page-stat">
            <div class="page-stat-value">{{ number_format($audienceMailable + $audienceFollowers) }}</div>
            <div class="page-stat-label">{{ __('messages.audience_reachable') }}</div>
        </div>
        <div class="page-stat">
            <div class="page-stat-value">{{ number_format($audienceMailable) }}</div>
            <div class="page-stat-label">{{ __('messages.audience_get_new_event_emails') }}</div>
        </div>
        <div class="page-stat">
            <div class="page-stat-value">{{ number_format($audienceFollowers) }}</div>
            <div class="page-stat-label">{{ __('messages.audience_newsletter_only') }}</div>
        </div>
    </div>
    <div class="page-actions">
        {{-- Opens the Embed dialog (included by role/show-admin) on its signup-form widget. A real
             href for no-JS and middle-click: the guest form itself. --}}
        <x-secondary-link href="{{ route('role.view_guest', ['subdomain' => $role->subdomain, 'embed' => 'true', 'form' => 'subscribe']) }}"
            class="js-open-subscribe-embed">
            {{ __('messages.embed_subscribe_form') }}
        </x-secondary-link>
        <x-secondary-link href="{{ route('role.qr_code', ['subdomain' => $role->subdomain]) }}">
            {{ __('messages.qr_code') }}
        </x-secondary-link>
    </div>
</div>

@if ($hasSubscribers)
{{--
    Account-less subscribers: people who gave this schedule an email address on the guest portal
    without creating an account. Owner-facing only - these addresses must never appear on a guest
    surface, an embed or public stats.
--}}
@php
    $canManageSubscribers = auth()->user() && auth()->user()->isEditor($role->subdomain);
    $subscriberPending = $subscriberStats['pending'] ?? 0;
@endphp
<div class="page-subhead">
    <h2>{{ __('messages.all_subscribers') }} ({{ number_format($subscribers->total()) }})</h2>
    @if ($subscriberStats)
    <p>
        {{ __('messages.subscriber_breakdown', [
            'confirmed' => number_format($subscriberStats['confirmed']),
            'pending' => number_format($subscriberStats['pending']),
            'unsubscribed' => number_format($subscriberStats['unsubscribed']),
        ]) }}
    </p>
    @endif
</div>
{{-- Says how a person GETS into this list, which is the question the two-list split raises. --}}
@if (public_registration_enabled())
<p class="event-hint">{{ __('messages.subscribers_help') }}</p>
@endif

@if ($subscriberPending)
{{-- The pending count and the recipient count on a send legitimately differ, because an
     unconfirmed row is never resolved as a recipient. --}}
<p class="event-hint">{{ trans_choice('messages.subscriber_pending_notice', $subscriberPending, ['count' => number_format($subscriberPending)]) }}</p>
@endif

<div class="ap-card rounded-xl overflow-hidden">
    <table role="table" class="page-table">
        <colgroup><col><col class="col-status"><col class="col-date"><col class="col-act"></colgroup>
        <thead role="rowgroup">
            <tr role="row">
                <th scope="col" role="columnheader">{{ __('messages.name') }}</th>
                <th scope="col" role="columnheader">{{ __('messages.status') }}</th>
                <th scope="col" role="columnheader">{{ __('messages.date') }}</th>
                <th scope="col" role="columnheader"><span class="sr-only">{{ __('messages.delete') }}</span></th>
            </tr>
        </thead>
        <tbody role="rowgroup">
            @foreach ($subscribers as $subscriber)
            <tr role="row">
                <td role="cell" class="c-main">
                    <div class="page-person">
                        <span class="event-avatar" aria-hidden="true">{{ $initialOf($subscriber->name, $subscriber->email) }}</span>
                        <div class="page-person-text">
                            <div class="event-list-name">
                                @if ($subscriber->name)
                                    <x-user-text>{{ $subscriber->name }}</x-user-text>
                                @else
                                    <span dir="ltr" v-pre>{{ $subscriber->email }}</span>
                                @endif
                                {{-- Neutral chips, never a second coloured mark beside the status:
                                     where the row came from, and whether the person has an account. --}}
                                @if ($subscriber->source === 'embed')
                                <span data-subscriber-source="embed" class="event-chip">{{ __('messages.subscriber_source_website') }}</span>
                                @endif
                                @if ($subscriber->confirmed_at && in_array(strtolower($subscriber->email), $subscriberAccountEmails ?? [], true))
                                <span class="event-chip">{{ __('messages.subscriber_has_account') }}</span>
                                @endif
                            </div>
                            @if ($subscriber->name)
                            <div class="event-list-sub" dir="ltr" v-pre>{{ $subscriber->email }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td role="cell">
                    @if ($subscriber->has_unsubscribed)
                        <span class="event-status">{{ __('messages.subscriber_unsubscribed') }}</span>
                    @elseif ($subscriber->confirmed_at)
                        <span class="event-status is-on">{{ __('messages.subscriber_confirmed') }}</span>
                    @else
                        {{-- Never mailed. The amber panel above the list explains the resulting
                             discrepancy to the owner. --}}
                        <span class="event-status is-warn">{{ __('messages.subscriber_pending') }}</span>
                    @endif
                </td>
                <td role="cell" class="c-date">{{ $subscriber->created_at->translatedFormat('M j, Y') }}</td>
                {{-- isEditor, matching RoleSubscriberController::remove(). viewAdmin admits
                     isMember, which includes viewers - who used to see this button on every row
                     and get a bare 403 on click. --}}
                <td role="cell" class="c-actions">@if ($canManageSubscribers)
                    <form method="POST" action="{{ route('role.subscribers.remove', ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($subscriber->id)]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" data-confirm="{{ __('messages.are_you_sure') }}" class="event-link is-danger">{{ __('messages.delete') }}</button>
                    </form>
                @endif</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if ($subscribers->hasPages())
<div class="mt-4">
    {{ $subscribers->links() }}
</div>
@endif
@endif


{{--
    Account followers. Guarded on isNotEmpty() because the branch above only takes the empty-state
    branch when BOTH lists are empty - so a schedule whose whole audience is account-less (the
    likeliest state for any schedule using the subscribe panel) used to render this list with its
    column headers and no rows, unlabelled, directly above "Email subscribers".
--}}
@if ($followersWithRoles->isNotEmpty())
@php
    // The schedule a follower runs is worth a column only when someone on this page has one: it
    // was an empty column on most pages.
    $anyFollowerSchedule = $followersWithRoles->contains(fn ($follower) => $follower->roles->isNotEmpty() && $follower->roles->first()->isClaimed());
@endphp
<div class="page-subhead">
    <h2>{{ __('messages.followers') }} ({{ number_format($followersWithRoles->total()) }})</h2>
</div>
{{-- The same reframing the marketing pages carry, next to the list it describes: following on
     its own does not sign anybody up for automatic email. --}}
<p class="event-hint">{{ __('messages.followers_account_only_help') }}</p>

<div class="ap-card rounded-xl overflow-hidden">
    <table role="table" class="page-table is-compact">
        <colgroup><col><col class="col-status"><col class="col-date"><col class="col-act"></colgroup>
        <thead role="rowgroup">
            <tr role="row">
                <th scope="col" role="columnheader" @if ($followerSortBy === 'name') aria-sort="{{ $followerSortDir === 'asc' ? 'ascending' : 'descending' }}" @endif><button type="button" class="page-sort" data-sort="name">{{ __('messages.name') }}{{ $sortMark('name') }}</button></th>
                <th scope="col" role="columnheader">@if ($anyFollowerSchedule){{ __('messages.schedule') }}@else<span class="sr-only">{{ __('messages.schedule') }}</span>@endif</th>
                <th scope="col" role="columnheader" @if ($followerSortBy === 'pivot_created_at') aria-sort="{{ $followerSortDir === 'asc' ? 'ascending' : 'descending' }}" @endif><button type="button" class="page-sort" data-sort="pivot_created_at">{{ __('messages.date') }}{{ $sortMark('pivot_created_at') }}</button></th>
                <th scope="col" role="columnheader"><span class="sr-only">{{ __('messages.actions') }}</span></th>
            </tr>
        </thead>
        <tbody role="rowgroup">
            @foreach ($followersWithRoles as $follower)
            @php $followerSchedule = $follower->roles->isNotEmpty() && $follower->roles->first()->isClaimed() ? $follower->roles->first() : null; @endphp
            <tr role="row">
                <td role="cell" class="c-main">
                    <div class="page-person">
                        <span class="event-avatar" aria-hidden="true">{{ $initialOf($follower->name, $follower->email) }}</span>
                        <div class="page-person-text">
                            <div class="event-list-name">
                                @if ($follower->name)
                                    <x-user-text>{{ $follower->name }}</x-user-text>
                                @else
                                    <span dir="ltr" v-pre>{{ $follower->email }}</span>
                                @endif
                            </div>
                            @if ($follower->name)
                            <div class="event-list-sub" dir="ltr" v-pre>{{ $follower->email }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td role="cell">@if ($followerSchedule)<x-link href="{{ $followerSchedule->getGuestUrl() }}" target="_blank"><span dir="auto" v-pre>{{ $followerSchedule->name }}</span></x-link>@endif</td>
                <td role="cell" class="c-date">{{ $follower->pivot->created_at->translatedFormat('M j, Y') }}</td>
                <td role="cell"></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if ($followersWithRoles->hasPages())
<div class="mt-4">
    {{ $followersWithRoles->links() }}
</div>
@endif
@endif
@endif
