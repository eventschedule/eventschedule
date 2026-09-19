<x-app-admin-layout>

    <div class="space-y-4">
        @include('admin.partials._navigation', ['active' => 'federation'])

        <div class="ap-card rounded-xl p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">@lang('messages.federation')</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-2xl">@lang('messages.federation_admin_intro')</p>
                </div>

                {{-- Status filter --}}
                <div class="flex items-center gap-1 rounded-xl bg-gray-100 dark:bg-gray-800 p-1">
                    {{-- Flagged only while something is flagged: it is where the dashboard
                         alert points, and a tab that is empty on every healthy install is
                         noise the rest of the time. --}}
                    @foreach (array_filter(['pending', 'approved', 'flagged', 'suspended', 'all'], fn ($k) => $k !== 'flagged' || $flaggedCount > 0) as $key)
                        <a href="{{ route('admin.federation', ['status' => $key]) }}"
                           class="rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-200 {{ $status === $key ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}"
                           @if ($status === $key) style="box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.08);" @endif>
                            @lang('messages.federation_status_'.$key)
                            @if ($key === 'pending' && $pendingCount > 0)
                                <span class="ms-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold text-white bg-red-500 rounded-full">{{ $pendingCount }}</span>
                            @elseif ($key === 'flagged')
                                <span class="ms-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold text-white bg-red-500 rounded-full">{{ $flaggedCount }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        @if ($instances->isEmpty())
            <div class="ap-card rounded-xl p-12 text-center">
                <p class="text-gray-500 dark:text-gray-400">@lang('messages.federation_no_instances')</p>
            </div>
        @else
            <form method="POST" action="{{ route('admin.federation.bulk') }}" class="space-y-4">
                @csrf

                {{-- The form's default button, disabled on purpose. Pressing Enter in a form clicks
                     its first submit button, and in this one that is a row action - a Suspend, or
                     a Resend that mails the operator again. A disabled default button makes Enter
                     do nothing. --}}
                <button type="submit" disabled hidden aria-hidden="true" tabindex="-1"></button>

                @foreach ($instances as $instance)
                    @php $hash = \App\Utils\UrlUtils::encodeId($instance->id); @endphp
                    <div class="ap-card rounded-xl p-6">
                        {{-- flex-1 with a minimum width: the details wrap beside the actions, and the
                             actions only drop below them when even that minimum does not fit. Sized by
                             their content, a long meta line pushed the actions down on some rows and
                             not others. --}}
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex flex-1 items-start gap-3 min-w-[16rem]">
                                <input type="checkbox" name="hashes[]" value="{{ $hash }}"
                                       class="mt-1 rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                <div class="min-w-0">
                                    {{-- Instance-supplied text: escape and keep it out of any Vue template.
                                         Applies to the schedule names and URLs below too. --}}
                                    <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $instance->name ?: $instance->site_url }}</p>
                                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                        {{-- Demoted from the row's headline link to one identifier among
                                             several. On a selfhost install / redirects to the login page,
                                             so this never showed a reviewer anything - the schedule links
                                             below are what it should have been all along. Still a link:
                                             it is the host every backlink is checked against and the
                                             subject of the flagged_at warning. --}}
                                        @if ($instance->name)
                                            <span class="break-all">
                                                <a href="{{ $instance->site_url }}" target="_blank" rel="noopener nofollow" class="hover:underline">{{ $instance->site_url }}</a>
                                            </span>
                                            &middot;
                                        @endif
                                        {{ $instance->contact_email ?: __('messages.none') }}
                                        &middot; {{ $instance->app_version ?: '-' }}
                                        &middot; {{ trans_choice('messages.federation_listing_count', $instance->events_count, ['count' => number_format($instance->events_count)]) }}
                                        @if ($instance->last_seen_at)
                                            &middot; {{ $instance->last_seen_at->diffForHumans() }}
                                        @endif
                                        @if ($instance->isApproved() && $instance->approved_at)
                                            &middot; @lang('messages.federation_approved_on', ['date' => $instance->approved_at->format('M j, Y')])
                                        @endif
                                    </p>

                                    {{-- The welcome's state. Installs approved before the welcome existed
                                         have never had one, and the button below is how they get it.
                                         "Queued" rather than "sent": delivery happens later, on the worker,
                                         and a send that fails every retry hands the claim back. The preview
                                         is on pending rows too - approving is what sends it. --}}
                                    @if ($instance->status !== 'suspended')
                                        @php
                                            $welcomeService = app(\App\Services\FederationWelcomeService::class);
                                        @endphp
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            @if ($instance->isApproved() && $instance->welcomed_at)
                                                @lang('messages.federation_welcome_queued_at', ['time' => $instance->welcomed_at->diffForHumans()])
                                                @if ($welcomeService->addressChangedSinceWelcome($instance))
                                                    &middot; <span class="text-amber-700 dark:text-amber-400">@lang('messages.federation_welcome_email_changed')</span>
                                                @endif
                                                @if ($instance->contact_email && $welcomeService->canResend($instance))
                                                    &middot;
                                                    <button type="submit" formaction="{{ route('admin.federation.welcome', $hash) }}"
                                                            class="font-medium text-[var(--brand-blue)] hover:underline focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">
                                                        @lang('messages.federation_resend_welcome')
                                                    </button>
                                                @endif
                                                &middot;
                                            @endif
                                            <x-link href="{{ route('admin.federation.welcome_preview', $hash) }}" target="_blank">
                                                @lang('messages.federation_welcome_preview')
                                            </x-link>
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium
                                    @if ($instance->status === 'approved') bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400
                                    @elseif ($instance->status === 'suspended') bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400
                                    @else bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 @endif">
                                    @lang('messages.federation_status_'.$instance->status)
                                </span>

                                {{-- Approved rows sort by listing count, so the ones that have sent
                                     nothing sink to the bottom. This is how they stand out: an
                                     approved install with nothing live is one to nudge. Live, not
                                     received: rows that are all blocked or expired publish nothing
                                     either. --}}
                                @if ($instance->isApproved() && $instance->live_events_count === 0)
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                        @lang('messages.federation_no_listings_pill')
                                    </span>
                                @endif

                                {{-- Destructive first, forward action last. --}}
                                @if ($instance->status !== 'suspended')
                                    <button type="submit" formaction="{{ route('admin.federation.suspend', $hash) }}"
                                            class="px-4 py-3 text-base rounded-lg font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all duration-200">
                                        @lang('messages.federation_suspend')
                                    </button>
                                @endif
                                @if ($instance->isApproved() && $instance->contact_email && ! $instance->welcomed_at)
                                    <button type="submit" formaction="{{ route('admin.federation.welcome', $hash) }}"
                                            class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                        @lang('messages.federation_send_welcome')
                                    </button>
                                @endif
                                @if ($instance->status !== 'approved')
                                    {{-- Says what approving does beyond the status: the FIRST approval
                                         emails the operator their setup steps. Approving again sends the
                                         short note instead, so it gets no hint. --}}
                                    <button type="submit" formaction="{{ route('admin.federation.approve', $hash) }}"
                                            @if ($instance->contact_email && ! $instance->welcomed_at) title="{{ __('messages.federation_approve_hint', ['email' => $instance->contact_email]) }}" @endif
                                            class="px-4 py-3 text-base rounded-lg font-medium text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200">
                                        @lang('messages.federation_approve')
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if ($instance->flagged_at)
                            {{-- Bordered panel, not coloured text, per the AP warning convention. --}}
                            <div class="mt-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <div class="min-w-0">
                                        {{-- Two flag sources, and only one of them is resolvable here.
                                             reported_site_url set means a PUSH claimed a different
                                             address: the instance stays approved, so there is no
                                             Approve button to settle it and the copy must not talk
                                             about approving. Null means register() raised it, which
                                             already moved site_url and sent the row back to pending,
                                             where Approve and Suspend do settle it. --}}
                                        @if ($instance->reported_site_url)
                                            <p class="text-sm text-amber-800 dark:text-amber-200">@lang('messages.federation_address_changed_warning')</p>
                                            <dl class="mt-2 space-y-1 text-sm">
                                                <div class="flex flex-wrap gap-x-2">
                                                    <dt class="text-amber-700 dark:text-amber-300">@lang('messages.federation_address_on_record'):</dt>
                                                    <dd class="font-medium text-amber-900 dark:text-amber-100 break-all">{{ $instance->site_url }}</dd>
                                                </div>
                                                <div class="flex flex-wrap gap-x-2">
                                                    {{-- Plain text, never a link: this address is unverified by
                                                         definition, which is the whole point of the warning. --}}
                                                    <dt class="text-amber-700 dark:text-amber-300">@lang('messages.federation_address_reported'):</dt>
                                                    <dd class="font-medium text-amber-900 dark:text-amber-100 break-all">{{ $instance->reported_site_url }}</dd>
                                                </div>
                                            </dl>
                                            <div class="mt-3">
                                                <button type="submit" formaction="{{ route('admin.federation.accept_address', $hash) }}"
                                                        data-confirm="{{ __('messages.federation_accept_address_confirm', ['url' => $instance->reported_site_url]) }}"
                                                        class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                                    @lang('messages.federation_accept_address')
                                                </button>
                                            </div>
                                        @elseif ($instance->isApproved())
                                            {{-- Flagged with nothing to adopt: either raised before
                                                 reported_site_url existed, or raised by a push over a
                                                 full-URL mismatch and then outliving its claim, which
                                                 register() drops on any re-registration while only
                                                 flagging a HOST change. The per-row Approve button is
                                                 hidden on an approved row and acceptAddress() has nothing
                                                 to accept, so without this the only way out was Suspend -
                                                 which drops the install off the network and mails its
                                                 operator twice to get back. Posts to approve: approving an
                                                 already-approved instance IS this review, and one route
                                                 keeps the two from drifting. --}}
                                            <p class="text-sm text-amber-800 dark:text-amber-200">@lang('messages.federation_flagged_unknown_warning')</p>
                                            {{-- The subject of the warning, beside the button that settles
                                                 it. Otherwise "the address above" means the meta line,
                                                 which only prints site_url when the instance sent a name. --}}
                                            <dl class="mt-2 space-y-1 text-sm">
                                                <div class="flex flex-wrap gap-x-2">
                                                    <dt class="text-amber-700 dark:text-amber-300">@lang('messages.federation_address_on_record'):</dt>
                                                    <dd class="font-medium text-amber-900 dark:text-amber-100 break-all">{{ $instance->site_url }}</dd>
                                                </div>
                                            </dl>
                                            <div class="mt-3">
                                                <button type="submit" formaction="{{ route('admin.federation.approve', $hash) }}"
                                                        data-confirm="{{ __('messages.federation_mark_reviewed_confirm', ['url' => $instance->site_url]) }}"
                                                        class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                                    @lang('messages.federation_mark_reviewed')
                                                </button>
                                            </div>
                                        @else
                                            {{-- Not approved, so the register path sent it back for review
                                                 and the row already carries the buttons that settle it:
                                                 Approve on a pending row, and on a suspended one an Approve
                                                 that changes status. This only has to say so. --}}
                                            <p class="text-sm text-amber-800 dark:text-amber-200">@lang('messages.federation_flagged_warning')</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- A pending install on the same site as a suspended one: most likely the
                             same operator under a new identity. --}}
                        @if ($instance->status === 'pending' && $instance->host() && isset($suspendedHosts[$instance->host()]))
                            <div class="mt-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <p class="text-sm text-amber-800 dark:text-amber-200">@lang('messages.federation_same_host_suspended_warning')</p>
                                </div>
                            </div>
                        @endif

                        {{-- contact_email is optional on registration, so a decision on an instance
                             that never supplied one notifies nobody. Say so here rather than leaving
                             the admin to read it out of the "None" above. --}}
                        @if (! $instance->contact_email)
                            <div class="mt-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <p class="text-sm text-amber-800 dark:text-amber-200">@lang('messages.federation_no_contact_email_warning')</p>
                                </div>
                            </div>
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
                            <div class="mt-4 rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">@lang('messages.federation_public_schedules')</p>
                                <ul class="space-y-2">
                                    @foreach ($shownSchedules as $schedule)
                                        <li class="flex flex-wrap items-baseline gap-x-2 text-sm">
                                            {{-- Only linked when it lives on the host the instance
                                                 registered. Rows stored before that check existed at
                                                 intake still hold whatever the sender sent, and a
                                                 re-push will not rewrite them, so this is the guard
                                                 that actually matters. --}}
                                            @if ($instance->ownsUrl($schedule->schedule_url))
                                                <x-link href="{{ $schedule->schedule_url }}" target="_blank" :nofollow="true" class="font-medium">
                                                    {{ $schedule->schedule_label ?: $schedule->schedule_url }}
                                                </x-link>
                                            @else
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $schedule->schedule_label ?: __('messages.none') }}</span>
                                            @endif
                                            <span class="text-gray-500 dark:text-gray-400">
                                                {{ trans_choice('messages.federation_listing_count', $schedule->listing_count, ['count' => number_format($schedule->listing_count)]) }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>

                                @if ($schedules->count() > $shownSchedules->count())
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
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
                            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">@lang('messages.federation_no_listings_yet')</p>
                        @endif

                        {{-- What is actually being approved. Approving on a name alone is
                             approving unseen third-party content onto this domain. --}}
                        @if (! empty($samples[$instance->id]) && count($samples[$instance->id]))
                            <div class="mt-4 rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">@lang('messages.federation_sample_listings')</p>
                                <ul class="space-y-2">
                                    @foreach ($samples[$instance->id] as $sample)
                                        <li class="flex flex-wrap items-baseline gap-x-2 text-sm">
                                            <a href="{{ $sample->url }}" target="_blank" rel="noopener nofollow"
                                               class="font-medium text-gray-900 dark:text-white hover:underline">{{ $sample->name }}</a>
                                            <span class="text-gray-500 dark:text-gray-400">
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
                    </div>
                @endforeach

                <div class="ap-card rounded-xl p-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.federation_bulk_hint')</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" name="action" value="suspend"
                                class="px-4 py-3 text-base rounded-lg font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all duration-200">
                            @lang('messages.federation_suspend_selected')
                        </button>
                        {{-- Only reaches approved installs that were never welcomed; the
                             service skips everything else rather than mailing it twice. So it is
                             only offered where approved rows are listed. --}}
                        @if (in_array($status, ['approved', 'all'], true))
                            <button type="submit" name="action" value="welcome"
                                    class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                @lang('messages.federation_bulk_welcome')
                            </button>
                        @endif
                        <button type="submit" name="action" value="approve"
                                class="px-4 py-3 text-base rounded-lg font-medium text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200">
                            @lang('messages.federation_approve_selected')
                        </button>
                    </div>
                </div>
            </form>

            <div>{{ $instances->links() }}</div>
        @endif
    </div>

</x-app-admin-layout>
