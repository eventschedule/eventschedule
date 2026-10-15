<x-app-admin-layout>

    <x-slot name="head">
        @include('boost.partials.styles')
    </x-slot>

    @php
        // One boost that runs through a partner's own Page, for the two people who work on it: the
        // Business that asked (its own words for each state, nothing it cannot act on, and never
        // the word "version") and the partner that answers it.
        $bpPageName = $partner?->displayName() ?: (string) $campaign->meta_page_name;
        $bpEventName = $campaign->event?->translatedName() ?? $campaign->event_name ?? __('messages.deleted_event');
        $bpScheduleName = $campaign->role?->getDisplayName(false) ?? $campaign->schedule_name;
        $bpVersion = $campaign->versions->firstWhere('version', $campaign->version);
        $bpPrevious = $campaign->version > 1 ? $campaign->versions->firstWhere('version', $campaign->version - 1) : null;
        $bpOpen = in_array($campaign->status, \App\Models\BoostCampaign::PARTNER_OPEN_STATUSES, true);
        $bpLaunched = $campaign->launched_at !== null;
        // A request declined because its schedule changed hands has nobody left to send it to.
        $bpCanEdit = ! $isPartnerViewer && ! in_array($campaign->status, ['completed', 'stopped'], true)
            && $campaign->decline_reason !== \App\Services\BoostPartnerService::DECLINE_OWNERSHIP_CHANGED
            && $campaign->event;
        // Dates are the schedule's, as the request form picks them.
        $bpZone = $campaign->role?->timezone ?: config('app.timezone');
        \App\Utils\HelpUtils::useDoc(\App\Utils\HelpUtils::BOOST_PARTNER_REQUESTS);
        // How to pay the partner, with its addresses as links: the text around them is escaped
        // here, and each address goes to the linker as it was typed (it escapes what it prints;
        // given escaped text it printed "&amp;amp;" into the link).
        $bpPayHtml = collect(preg_split('/((?:https?|ftp):\/\/[^\s<]+)/i', (string) $partner?->payment_instructions, -1, PREG_SPLIT_DELIM_CAPTURE))
            ->map(fn ($part, $i) => $i % 2 === 1 ? \App\Utils\UrlUtils::convertUrlsToLinks($part) : e($part))
            ->implode('');
        $bpTimeFormat = get_use_24_hour_time($campaign->role) ? 'M j, Y H:i' : 'M j, Y g:i A';

        // Where the partner came from: a request still waits on it in Requests; anything else is
        // one of its Campaigns.
        $bpBack = $isPartnerViewer
            ? (in_array($campaign->status, ['pending_approval', 'awaiting_payment', 'ready'], true)
                ? [route('boost.partner.requests').'#request-'.$campaign->hashedId(), __('messages.requests')]
                : [route('boost.partner.campaigns'), __('messages.campaigns')])
            : [route('boost.index'), __('messages.boost')];

        // The history in words. The partner reads versions; the Business reads what happened.
        $bpActors = \App\Models\User::whereIn('id', $history->pluck('user_id')->filter()->unique())->pluck('name', 'id');
        $bpHistoryLine = function ($row) use ($isPartnerViewer) {
            $values = is_array($row->new_values) ? $row->new_values : [];
            $version = $values['version'] ?? null;

            return match ($row->action) {
                \App\Services\AuditService::BOOST_PARTNER_SUBMIT => $isPartnerViewer && $version ? __('messages.boost_partner_history_sent_version', ['version' => $version]) : __('messages.boost_partner_history_sent'),
                \App\Services\AuditService::BOOST_PARTNER_EDIT => ! empty($values['needs_approval'])
                    ? ($isPartnerViewer && $version ? __('messages.boost_partner_history_sent_version', ['version' => $version]) : __('messages.boost_partner_history_resent'))
                    : __('messages.boost_partner_history_changed'),
                \App\Services\AuditService::BOOST_PARTNER_APPROVE => ! empty($values['with_payment'])
                    ? ($isPartnerViewer && $version ? __('messages.boost_partner_history_approved_paid_version', ['version' => $version]) : __('messages.boost_partner_history_approved_paid'))
                    : ($isPartnerViewer && $version ? __('messages.boost_partner_history_approved_version', ['version' => $version]) : __('messages.boost_partner_history_approved')),
                \App\Services\AuditService::BOOST_PARTNER_DECLINE => __('messages.boost_partner_status_declined'),
                \App\Services\AuditService::BOOST_PARTNER_PAYMENT => __('messages.boost_partner_history_paid'),
                \App\Services\AuditService::BOOST_PARTNER_PAYMENT_WITHDRAWN => __('messages.boost_partner_history_payment_withdrawn'),
                \App\Services\AuditService::BOOST_PARTNER_STOP => ($values['code'] ?? null) === \App\Services\BoostPartnerService::STOP_WITHDRAWN
                    ? __('messages.boost_partner_history_withdrawn')
                    : __('messages.boost_partner_status_stopped'),
                \App\Services\AuditService::BOOST_PARTNER_LAUNCH => __('messages.boost_partner_history_launched'),
                default => \Illuminate\Support\Str::headline(\Illuminate\Support\Str::afterLast($row->action, '.')),
            };
        };
        // Who did it: the partner by its Page's name, anybody else by their own.
        $bpHistoryWho = function ($row) use ($bpActors, $partner, $bpPageName) {
            if (! $row->user_id) {
                return null;
            }
            if ($partner && (int) $row->user_id === (int) $partner->user_id) {
                return $bpPageName;
            }

            return $bpActors[$row->user_id] ?? null;
        };

        // Why a stopped boost stopped, in words, when nobody wrote a reason.
        $bpStopWords = [
            \App\Services\BoostPartnerService::STOP_BY_PARTNER => __('messages.boost_partner_stop_code_partner', ['page' => $bpPageName]),
            \App\Services\BoostPartnerService::STOP_BY_BUSINESS => __('messages.boost_partner_stop_code_business'),
            \App\Services\BoostPartnerService::STOP_WITHDRAWN => __('messages.boost_partner_stop_code_withdrawn'),
            \App\Services\BoostPartnerService::STOP_ACCESS_WITHDRAWN => __('messages.boost_partner_stop_code_access'),
            \App\Services\BoostPartnerService::STOP_EVENT_REMOVED => __('messages.boost_partner_stop_code_event'),
        ];
    @endphp

    <div class="page-shell">
        <x-page-header :title="$bpEventName" :lead="$bpScheduleName" :back="$bpBack[0]" :back-label="$bpBack[1]">
            <x-slot name="actions">
                @include('boost.partials.status', ['status' => $campaign->status, 'partner' => true])

                @if (! $isPartnerViewer && $bpOpen)
                {{-- Before launch it is a request taken back; after, the ad is stopped for good. --}}
                <form method="POST" action="{{ route('boost.partner.stop', ['hash' => $campaign->hashedId()]) }}"
                      data-confirm="{{ $bpLaunched ? __('messages.boost_partner_stop_confirm') : __('messages.boost_partner_withdraw_confirm', ['page' => $bpPageName]) }}">
                    @csrf
                    <x-danger-button class="boost-danger">{{ $bpLaunched ? __('messages.boost_partner_stop_boost') : __('messages.boost_partner_withdraw_request') }}</x-danger-button>
                </form>
                @endif

                @if ($bpCanEdit)
                <x-secondary-link href="{{ route('boost.edit', ['hash' => $campaign->hashedId()]) }}">{{ __('messages.edit') }}</x-secondary-link>
                @endif
            </x-slot>
        </x-page-header>

        <div class="page-stack">
            <x-page-flash :keys="['success' => 'success', 'error' => 'error']" />

            {{-- What needs doing, first. --}}
            @if ($campaign->status === 'declined')
                @if ($campaign->decline_reason === null || $campaign->decline_reason === \App\Services\BoostPartnerService::DECLINE_BY_PARTNER)
                <x-page-notice tone="warn" :title="$isPartnerViewer ? __('messages.boost_partner_you_asked_for_changes') : __('messages.boost_partner_asked_for_changes', ['page' => $bpPageName])">
                    @if (filled($campaign->approval_note))
                    <p class="bp-text-block"><bdi>{{ $campaign->approval_note }}</bdi></p>
                    @endif
                    @if ($bpCanEdit)
                    <x-slot name="action"><a href="{{ route('boost.edit', ['hash' => $campaign->hashedId()]) }}" class="event-link">{{ __('messages.boost_partner_edit_and_resend') }}</a></x-slot>
                    @endif
                </x-page-notice>
                @else
                <x-page-notice tone="warn" :title="__('messages.boost_partner_status_declined')">
                    {{ $campaign->decline_reason === \App\Services\BoostPartnerService::DECLINE_END_PASSED ? __('messages.boost_partner_declined_end_passed') : __('messages.boost_partner_declined_ownership') }}
                    @if ($bpCanEdit && $campaign->decline_reason === \App\Services\BoostPartnerService::DECLINE_END_PASSED)
                    <x-slot name="action"><a href="{{ route('boost.edit', ['hash' => $campaign->hashedId()]) }}" class="event-link">{{ __('messages.boost_partner_edit_and_resend') }}</a></x-slot>
                    @endif
                </x-page-notice>
                @endif
            @elseif ($campaign->status === 'rejected')
            <x-page-notice tone="error" :title="__('messages.boost_partner_status_rejected')">
                <p class="bp-text-block"><bdi>{{ $campaign->meta_rejection_reason ?: __('messages.boost_partner_rejected_default_reason') }}</bdi></p>
                @if ($bpCanEdit)
                <x-slot name="action"><a href="{{ route('boost.edit', ['hash' => $campaign->hashedId()]) }}" class="event-link">{{ __('messages.boost_partner_edit_and_resend') }}</a></x-slot>
                @endif
            </x-page-notice>
            @elseif ($campaign->status === 'stopped')
            <x-page-notice tone="info" :title="__('messages.boost_partner_status_stopped')">
                <p class="bp-text-block"><bdi>{{ $campaign->stop_reason ?: ($bpStopWords[$campaign->stop_code] ?? '') }}</bdi></p>
            </x-page-notice>
            @elseif (! $isPartnerViewer && $campaign->status === 'awaiting_payment' && ! $campaign->isPaidCurrent())
            <x-page-notice tone="info" :title="__('messages.boost_partner_how_to_pay', ['page' => $bpPageName])">
                @if (filled($partner?->payment_instructions))
                <p class="bp-text-block">{!! $bpPayHtml !!}</p>
                @else
                <p>{{ __('messages.boost_partner_pay_page_no_instructions', ['page' => $bpPageName]) }}</p>
                @endif
            </x-page-notice>
            @elseif ($campaign->status === 'ready' && $partner && ! $partner->isUsable())
            <x-page-notice tone="info">{{ $isPartnerViewer ? __('messages.boost_partner_ready_waits_for_account') : __('messages.boost_partner_ready_waits_for_page', ['page' => $bpPageName]) }}</x-page-notice>
            @endif

            @if ($campaign->pause_pending_at)
            <x-page-notice tone="warn">{{ __('messages.boost_partner_pause_pending') }}</x-page-notice>
            @endif

            {{-- The two checks, in words, and for the partner its answers. --}}
            <section class="ap-card rounded-xl" aria-label="{{ __('messages.boost_partner_checks_label') }}">
                @include('boost.partials.partner-decisions', ['bpCampaign' => $campaign, 'bpIsPartner' => $isPartnerViewer, 'bpPageName' => $bpPageName])
            </section>

            @if ($bpLaunched)
            <div class="ap-card rounded-xl page-stats is-auto">
                <div class="page-stat">
                    <div class="page-stat-value">{{ \App\Utils\MoneyUtils::format($campaign->actual_spend ?? 0, $campaign->currency_code) }}</div>
                    <div class="page-stat-label">{{ __('messages.spend') }}</div>
                    <div class="page-stat-sub">
                        @if ($campaign->spend_final_at)
                        {{ __('messages.boost_partner_spend_final') }}
                        @elseif ($campaign->analytics_synced_at)
                        {{ __('messages.boost_partner_spend_as_of', ['time' => $campaign->analytics_synced_at->diffForHumans()]) }}
                        @endif
                    </div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($campaign->impressions ?? 0) }}</div>
                    <div class="page-stat-label">{{ __('messages.impressions') }}</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($campaign->reach ?? 0) }}</div>
                    <div class="page-stat-label">{{ __('messages.reach') }}</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($campaign->clicks ?? 0) }}</div>
                    <div class="page-stat-label">{{ __('messages.clicks') }}</div>
                </div>
            </div>
            @endif

            {{-- The ad as it stands now, and every word of it. --}}
            <x-page-card :title="__('messages.boost_partner_the_ad')" flush>
                @include('boost.partials.partner-ad', ['bpCampaign' => $campaign, 'bpVersion' => $bpVersion, 'bpPageName' => $bpPageName])
                @if ($isPartnerViewer && $campaign->status === 'pending_approval')
                @include('boost.partials.partner-changes', ['bpVersion' => $bpVersion, 'bpPrevious' => $bpPrevious, 'bpCurrency' => $campaign->currency_code, 'bpDeclineNote' => null])
                @endif
            </x-page-card>

            {{-- The partner ends a boost with a reason the Business is told. --}}
            @if ($isPartnerViewer && $bpOpen)
            <x-page-card :title="__('messages.boost_partner_stop_title')" :lead="__('messages.boost_partner_stop_lead')">
                <form method="POST" action="{{ route('boost.partner.stop', ['hash' => $campaign->hashedId()]) }}" class="page-form-fields"
                      data-confirm="{{ __('messages.boost_partner_stop_confirm_partner') }}">
                    @csrf
                    <div>
                        <x-input-label for="bp-stop-reason" :value="__('messages.boost_partner_stop_reason')" />
                        <textarea id="bp-stop-reason" name="reason" rows="2" maxlength="500" dir="auto"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">{{ old('reason') }}</textarea>
                        <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                    </div>
                    <div class="page-form-actions" style="margin-top: 0">
                        <x-danger-button class="boost-danger">{{ __('messages.boost_partner_stop_boost') }}</x-danger-button>
                    </div>
                </form>
            </x-page-card>
            @endif

            @if ($history->isNotEmpty())
            <x-page-card :title="__('messages.boost_partner_history')" flush>
                <ol class="bp-history">
                    @foreach ($history as $bpRow)
                    @php $bpWho = $bpHistoryWho($bpRow); @endphp
                    <li>
                        <span class="bp-history-what">{{ $bpHistoryLine($bpRow) }}</span>
                        @if ($bpWho)
                        <span class="bp-history-who"><bdi>{{ $bpWho }}</bdi></span>
                        @endif
                        <time datetime="{{ $bpRow->created_at?->toIso8601String() }}">{{ $bpRow->created_at?->copy()->tz($bpZone)->translatedFormat($bpTimeFormat) }}</time>
                    </li>
                    @endforeach
                </ol>
            </x-page-card>
            @endif
        </div>

        @if ($isPartnerViewer)
        @include('boost.partials.partner-dialogs')
        @endif
    </div>

</x-app-admin-layout>
