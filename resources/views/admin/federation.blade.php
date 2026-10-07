<x-app-admin-layout>
    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            /* A small button that destroys something: the weight of .page-tool, in red. */
            .page-tool.is-danger {
              border-color: rgba(220, 38, 38, 0.4);
              color: #b91c1c;
            }
            .page-tool.is-danger:hover {
              border-color: #dc2626;
              background: rgba(220, 38, 38, 0.08);
              color: #b91c1c;
            }
            .dark .page-tool.is-danger,
            .dark .page-tool.is-danger:hover {
              border-color: rgba(248, 113, 113, 0.5);
              color: #f87171;
            }
            /* One install: who it is and how it stands, then what can be done about it. The
               details wrap beside the actions, and the actions only drop below them when even
               their minimum does not fit. */
            .fed-head {
              display: flex;
              flex-wrap: wrap;
              align-items: flex-start;
              justify-content: space-between;
              gap: 0.75rem 1rem;
            }
            .fed-who {
              display: flex;
              flex: 1 1 16rem;
              align-items: flex-start;
              gap: 0.75rem;
              min-width: 0;
            }
            .fed-who > div {
              min-width: 0;
            }
            .fed-who input[type="checkbox"] {
              margin-top: 0.25rem;
            }
            .fed-name {
              margin: 0;
              font-size: 1rem;
              font-weight: 600;
              line-height: 1.5rem;
              color: rgb(var(--ap-ink));
              overflow-wrap: anywhere;
            }
            .fed-meta {
              margin: 0.25rem 0 0;
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
              overflow-wrap: anywhere;
            }
            .fed-meta a:hover {
              text-decoration: underline;
            }
            .fed-card > * + * {
              margin-top: 1rem;
            }
            .fed-card .event-chip {
              margin-inline-start: 0;
            }
            .sys-panel {
              border-radius: 0.75rem;
              padding: 0.875rem 1rem;
              background: var(--ap-tint-1);
            }
            .sys-panel-label {
              margin: 0 0 0.5rem;
              font-size: 0.75rem;
              font-weight: 600;
              letter-spacing: 0.04em;
              text-transform: uppercase;
              color: rgb(var(--ap-ink-3));
            }
            .sys-list {
              display: grid;
              gap: 0.375rem;
              margin: 0;
              padding: 0;
              list-style: none;
              font-size: 0.875rem;
            }
            .sys-list li {
              display: flex;
              flex-wrap: wrap;
              align-items: baseline;
              gap: 0.125rem 0.625rem;
              min-width: 0;
            }
            .sys-list li > :first-child {
              font-weight: 500;
              overflow-wrap: anywhere;
            }
            /* A schedule that is linked keeps the link's blue; a name alone is ink. */
            .sys-list li > span:first-child,
            .sys-list li > .sys-plain {
              color: rgb(var(--ap-ink));
            }
            .sys-list li > span + span,
            .sys-list li > a + span {
              color: rgb(var(--ap-ink-3));
            }
            .sys-help {
              margin: 0.5rem 0 0;
              font-size: 0.75rem;
              color: rgb(var(--ap-ink-3));
            }
            .sys-foot-row {
              display: flex;
              flex-wrap: wrap;
              align-items: center;
              justify-content: space-between;
              gap: 0.75rem 1.5rem;
            }
            .sys-foot-row p {
              margin: 0;
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
            }
        </style>
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'federation'])

    <div class="page-head">
        <p class="page-lead">{{ __('messages.federation_admin_intro') }}</p>
    </div>

    <div class="page-shell">
        {{-- The four states are the kit's tabs, from one list for the strip and the phone's
             dropdown: as pills in the card's corner they ran off a phone's edge at "Suspended".
             Flagged only while something is flagged: it is where the dashboard alert points,
             and a tab that is empty on every healthy install is noise the rest of the time. --}}
        @php
            $federationTabs = collect(['pending', 'approved', 'flagged', 'suspended', 'all'])
                ->map(fn ($key) => $key === 'flagged' && $flaggedCount < 1 ? null : [
                    'label' => __('messages.federation_status_'.$key),
                    'href' => route('admin.federation', ['status' => $key]),
                    'current' => $status === $key,
                    'count' => match ($key) { 'pending' => $pendingCount, 'flagged' => $flaggedCount, default => null },
                    'waiting' => true,
                ])->all();
        @endphp
        <x-page-tabs :tabs="$federationTabs" id="federation-tabs" strip :label="__('messages.status')" />

        @if ($instances->isEmpty())
            <div class="ap-card rounded-xl">
                <x-page-empty :title="__('messages.federation_no_instances')"
                    icon="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
            </div>
        @else
            <form method="POST" action="{{ route('admin.federation.bulk') }}" class="page-stack">
                @csrf

                {{-- The form's default button, disabled on purpose. Pressing Enter in a form clicks
                     its first submit button, and in this one that is a row action - a Suspend, or
                     a Resend that mails the operator again. A disabled default button makes Enter
                     do nothing. --}}
                <button type="submit" disabled hidden aria-hidden="true" tabindex="-1"></button>

                @foreach ($instances as $instance)
                    @php $hash = \App\Utils\UrlUtils::encodeId($instance->id); @endphp
                    <section class="ap-card rounded-xl page-card fed-card">
                        <div class="fed-head">
                            <div class="fed-who">
                                <input type="checkbox" name="hashes[]" value="{{ $hash }}" aria-label="{{ $instance->name ?: $instance->site_url }}"
                                       class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                <div>
                                    {{-- Instance-supplied text: escape and keep it out of any Vue template.
                                         Applies to the schedule names and URLs below too. --}}
                                    <h2 class="fed-name"><bdi>{{ $instance->name ?: $instance->site_url }}</bdi></h2>
                                    <p class="fed-meta">
                                        {{-- One identifier among several, not the row's headline link. On
                                             a selfhost install / redirects to the login page, so it never
                                             showed a reviewer anything - the schedule links below are what
                                             to look at. Still a link: it is the host every backlink is
                                             checked against and the subject of the flagged_at warning. --}}
                                        @if ($instance->name)
                                            <a href="{{ $instance->site_url }}" target="_blank" rel="noopener nofollow"><bdi dir="ltr">{{ $instance->site_url }}</bdi></a>
                                            &middot;
                                        @endif
                                        <bdi dir="ltr">{{ $instance->contact_email ?: __('messages.none') }}</bdi>
                                        &middot; <bdi dir="ltr">{{ $instance->app_version ?: '-' }}</bdi>
                                        &middot; {{ trans_choice('messages.federation_listing_count', $instance->events_count, ['count' => number_format($instance->events_count)]) }}
                                        @if ($instance->last_seen_at)
                                            &middot; {{ $instance->last_seen_at->diffForHumans() }}
                                        @endif
                                        @if ($instance->isApproved() && $instance->approved_at)
                                            &middot; {{ __('messages.federation_approved_on', ['date' => $instance->approved_at->format('M j, Y')]) }}
                                        @endif
                                    </p>

                                    {{-- The welcome's state. Installs approved before the welcome existed
                                         have never had one, and the button beside this is how they get it.
                                         "Queued" rather than "sent": delivery happens later, on the worker,
                                         and a send that fails every retry hands the claim back. The preview
                                         is on pending rows too - approving is what sends it. --}}
                                    @if ($instance->status !== 'suspended')
                                        @php
                                            $welcomeService = app(\App\Services\FederationWelcomeService::class);
                                        @endphp
                                        <p class="fed-meta">
                                            @if ($instance->isApproved() && $instance->welcomed_at)
                                                {{ __('messages.federation_welcome_queued_at', ['time' => $instance->welcomed_at->diffForHumans()]) }}
                                                @if ($welcomeService->addressChangedSinceWelcome($instance))
                                                    &middot; <span class="event-status is-warn">{{ __('messages.federation_welcome_email_changed') }}</span>
                                                @endif
                                                @if ($instance->contact_email && $welcomeService->canResend($instance))
                                                    &middot;
                                                    <button type="submit" formaction="{{ route('admin.federation.welcome', $hash) }}" class="event-link">{{ __('messages.federation_resend_welcome') }}</button>
                                                @endif
                                                &middot;
                                            @endif
                                            <x-link href="{{ route('admin.federation.welcome_preview', $hash) }}" target="_blank">
                                                {{ __('messages.federation_welcome_preview') }}
                                            </x-link>
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="page-actions">
                                <span class="event-status {{ ['approved' => 'is-on', 'suspended' => 'is-bad'][$instance->status] ?? 'is-warn' }}">{{ __('messages.federation_status_'.$instance->status) }}</span>

                                {{-- Approved rows sort by listing count, so the ones that have sent
                                     nothing sink to the bottom. This is how they stand out: an
                                     approved install with nothing live is one to nudge. Live, not
                                     received: rows that are all blocked or expired publish nothing
                                     either. --}}
                                @if ($instance->isApproved() && $instance->live_events_count === 0)
                                    <span class="event-chip">{{ __('messages.federation_no_listings_pill') }}</span>
                                @endif

                                {{-- Destroying first, forward action last. --}}
                                @if ($instance->status !== 'suspended')
                                    <button type="submit" formaction="{{ route('admin.federation.suspend', $hash) }}" class="page-tool is-danger">{{ __('messages.federation_suspend') }}</button>
                                @endif
                                @if ($instance->isApproved() && $instance->contact_email && ! $instance->welcomed_at)
                                    <button type="submit" formaction="{{ route('admin.federation.welcome', $hash) }}" class="page-tool">{{ __('messages.federation_send_welcome') }}</button>
                                @endif
                                @if ($instance->status !== 'approved')
                                    {{-- Says what approving does beyond the status: the FIRST approval
                                         emails the operator their setup steps. Approving again sends the
                                         short note instead, so it gets no hint. --}}
                                    <x-brand-button type="submit" size="sm" :formaction="route('admin.federation.approve', $hash)"
                                        :title="$instance->contact_email && ! $instance->welcomed_at ? __('messages.federation_approve_hint', ['email' => $instance->contact_email]) : null">{{ __('messages.federation_approve') }}</x-brand-button>
                                @endif
                            </div>
                        </div>

                        @if ($instance->flagged_at)
                            {{-- A notice, not coloured text, per the AP warning convention. --}}
                            <x-page-notice tone="warn">
                                                                        {{-- Branch on whether there is an address an admin could
                                             actually adopt, NOT on the column being non-null. The
                                             push path stores whatever an install reports without
                                             validating it, so a misconfigured APP_URL put junk in
                                             that column - and branching on the column alone drew an
                                             Accept button whose action then refused it, on a row
                                             that had no other exit. hasAdoptableAddress() is the
                                             same predicate acceptAddress() and settleFlag() use.

                                             Adoptable means a push is claiming a different address.
                                             That leaves the instance at whatever status it had, so
                                             on an approved row there is no Approve button and the
                                             copy must not talk about approving. Not adoptable means
                                             either register() raised the flag - it moves site_url
                                             itself and sends an approved row back to pending, where
                                             Approve and Suspend settle it - or the claim is gone or
                                             was never usable, and confirming the address on record
                                             is what settles it. --}}
                                        @if ($instance->hasAdoptableAddress())
                                            <p>{{ __('messages.federation_address_changed_warning') }}</p>
                                            <dl class="mt-2 space-y-1 text-sm">
                                                <div class="flex flex-wrap gap-x-2">
                                                    <dt class="text-amber-700 dark:text-amber-300">{{ __('messages.federation_address_on_record') }}:</dt>
                                                    <dd class="font-medium text-amber-900 dark:text-amber-100 break-all">{{ $instance->site_url }}</dd>
                                                </div>
                                                <div class="flex flex-wrap gap-x-2">
                                                    {{-- Plain text, never a link: this address is unverified by
                                                         definition, which is the whole point of the warning. --}}
                                                    <dt class="text-amber-700 dark:text-amber-300">{{ __('messages.federation_address_reported') }}:</dt>
                                                    <dd class="font-medium text-amber-900 dark:text-amber-100 break-all">{{ $instance->reported_site_url }}</dd>
                                                </div>
                                            </dl>
                                            <div class="mt-3">
                                                <button type="submit" formaction="{{ route('admin.federation.accept_address', $hash) }}"
                                                        data-confirm="{{ __('messages.federation_accept_address_confirm', ['url' => $instance->reported_site_url]) }}"
                                                        class="page-tool">
                                                    {{ __('messages.federation_accept_address') }}
                                                </button>
                                            </div>
                                        @elseif ($instance->isApproved())
                                            {{-- Flagged with nothing to adopt: raised before
                                                 reported_site_url existed; or raised by a push over a
                                                 full-URL mismatch and then outliving its claim, which
                                                 register() drops on any re-registration while only
                                                 flagging a HOST change; or the reported address is
                                                 junk the push path never validated. The per-row Approve button is
                                                 hidden on an approved row and acceptAddress() has nothing
                                                 to accept, so without this the only way out was Suspend -
                                                 which drops the install off the network and mails its
                                                 operator twice to get back. Posts to approve: approving an
                                                 already-approved instance IS this review, and one route
                                                 keeps the two from drifting. --}}
                                            <p>{{ __('messages.federation_flagged_unknown_warning') }}</p>
                                            {{-- The subject of the warning, beside the button that settles
                                                 it. Otherwise "the address above" means the meta line,
                                                 which only prints site_url when the instance sent a name. --}}
                                            <dl class="mt-2 space-y-1 text-sm">
                                                <div class="flex flex-wrap gap-x-2">
                                                    <dt class="text-amber-700 dark:text-amber-300">{{ __('messages.federation_address_on_record') }}:</dt>
                                                    <dd class="font-medium text-amber-900 dark:text-amber-100 break-all">{{ $instance->site_url }}</dd>
                                                </div>
                                            </dl>
                                            <div class="mt-3">
                                                <button type="submit" formaction="{{ route('admin.federation.approve', $hash) }}"
                                                        data-confirm="{{ __('messages.federation_mark_reviewed_confirm', ['url' => $instance->site_url]) }}"
                                                        class="page-tool">
                                                    {{ __('messages.federation_mark_reviewed') }}
                                                </button>
                                            </div>
                                        @else
                                            {{-- Not approved, so the register path sent it back for review
                                                 and the row already carries the buttons that settle it:
                                                 Approve on a pending row, and on a suspended one an Approve
                                                 that changes status. This only has to say so. --}}
                                            <p>{{ __('messages.federation_flagged_warning') }}</p>
                                        @endif
                            </x-page-notice>
                        @endif

                        {{-- A pending install on the same site as a suspended one: most likely the
                             same operator under a new identity. --}}
                        @if ($instance->status === 'pending' && $instance->host() && isset($suspendedHosts[$instance->host()]))
                            <x-page-notice tone="warn">{{ __('messages.federation_same_host_suspended_warning') }}</x-page-notice>
                        @endif

                        {{-- contact_email is optional on registration, so a decision on an instance
                             that never supplied one notifies nobody. Say so here rather than leaving
                             the admin to read it out of the "None" above. --}}
                        @if (! $instance->contact_email)
                            <x-page-notice tone="warn">{{ __('messages.federation_no_contact_email_warning') }}</x-page-notice>
                        @endif

                        {{-- The origin's own public schedule pages: what a reviewer would
                             otherwise have no way to look at. site_url above lands on the
                             install's login screen, and a selfhost install publishes no
                             index of its schedules anywhere. --}}
                        @php
                            $schedules = $scheduleLinks[$instance->id] ?? collect();
                            $shownSchedules = $schedules->take(\App\Http\Controllers\AdminFederationController::MAX_SCHEDULES);
                        @endphp

                        @if ($schedules->isNotEmpty())
                            <div class="sys-panel">
                                <p class="sys-panel-label">{{ __('messages.federation_public_schedules') }}</p>
                                <ul class="sys-list">
                                    @foreach ($shownSchedules as $schedule)
                                        <li>
                                            {{-- Only linked when it lives on the host the instance
                                                 registered. Rows stored before that check existed at
                                                 intake still hold whatever the sender sent, and a
                                                 re-push will not rewrite them, so this is the guard
                                                 that actually matters. --}}
                                            @if ($instance->ownsUrl($schedule->schedule_url))
                                                <x-link href="{{ $schedule->schedule_url }}" target="_blank" :nofollow="true" class="font-medium">
                                                    <bdi>{{ $schedule->schedule_label ?: $schedule->schedule_url }}</bdi>
                                                </x-link>
                                            @else
                                                <span><bdi>{{ $schedule->schedule_label ?: __('messages.none') }}</bdi></span>
                                            @endif
                                            <span>
                                                {{ trans_choice('messages.federation_listing_count', $schedule->listing_count, ['count' => number_format($schedule->listing_count)]) }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>

                                @if ($schedules->count() > $shownSchedules->count())
                                    <p class="sys-help">
                                        {{ __('messages.federation_preview_more', ['count' => number_format($schedules->count() - $shownSchedules->count())]) }}
                                    </p>
                                @endif

                                {{-- Gated on approval because listable() requires it: for a
                                     pending or suspended instance this page would be empty. --}}
                                @if ($instance->isApproved() && $instance->live_events_count > 0)
                                    <p class="mt-3 text-sm">
                                        <x-link href="{{ marketing_url('/browse?instance='.$hash).'#network' }}" target="_blank" :nofollow="true">
                                            {{ trans_choice('messages.federation_view_live_listings', $instance->live_events_count, ['count' => number_format($instance->live_events_count)]) }}
                                        </x-link>
                                    </p>
                                @endif
                            </div>
                        @elseif ($instance->events_count === 0)
                            {{-- Says so rather than rendering an empty card. Nothing has been
                                 received, so there is genuinely nothing to preview. That is
                                 normal for a new install - every schedule starts undecided - so
                                 the rule is to approve it, which emails the operator the steps
                                 to list their schedules, or suspend it. Pending rows are only
                                 pruned once they also stop checking in, and the queue caps at
                                 ApiFederationController::MAX_PENDING_INSTANCES. --}}
                            <p class="fed-meta">{{ __('messages.federation_no_listings_yet') }}</p>
                        @endif

                        {{-- What is actually being approved. Approving on a name alone is
                             approving unseen third-party content onto this domain. --}}
                        @if (! empty($samples[$instance->id]) && count($samples[$instance->id]))
                            <div class="sys-panel">
                                <p class="sys-panel-label">{{ __('messages.federation_sample_listings') }}</p>
                                <ul class="sys-list">
                                    @foreach ($samples[$instance->id] as $sample)
                                        <li>
                                            <a href="{{ $sample->url }}" target="_blank" rel="noopener nofollow" class="sys-plain hover:underline"><bdi>{{ $sample->name }}</bdi></a>
                                            <span>
                                                {{ $sample->locationLabel() ?: __('messages.online') }}
                                                @if ($sample->next_occurrence_at)
                                                    &middot; {{ $sample->next_occurrence_at->format('M j, Y') }}
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </section>
                @endforeach

                {{-- What acts on every ticked install. Destroying first, forward action last. --}}
                <div class="ap-card rounded-xl page-card sys-foot-row">
                    <p>{{ __('messages.federation_bulk_hint') }}</p>
                    <div class="page-actions">
                        <button type="submit" name="action" value="suspend" class="page-tool is-danger">{{ __('messages.federation_suspend_selected') }}</button>
                        {{-- Only reaches approved installs that were never welcomed; the
                             service skips everything else rather than mailing it twice. So it is
                             only offered where approved rows are listed. --}}
                        @if (in_array($status, ['approved', 'all'], true))
                            <button type="submit" name="action" value="welcome" class="page-tool">{{ __('messages.federation_bulk_welcome') }}</button>
                        @endif
                        <x-brand-button type="submit" size="sm" name="action" value="approve">{{ __('messages.federation_approve_selected') }}</x-brand-button>
                    </div>
                </div>
            </form>

            @if ($instances->hasPages())
            <div class="page-pager">{{ $instances->links() }}</div>
            @endif
        @endif
    </div>

</x-app-admin-layout>
