<x-app-admin-layout>

    <x-slot name="head">
        @include('boost.partials.styles')
    </x-slot>

    @php
        // Each request names who asked and for which schedule, inside one sentence: the names are
        // somebody's own text, so each goes in <bdi> and the sentence around them is escaped.
        $bpSay = function (string $key, array $names) {
            $marks = [];
            foreach (array_keys($names) as $i => $name) {
                $marks[$name] = '@@bp'.$i.'@@';
            }
            $out = e(__($key, $marks));
            foreach ($names as $name => $value) {
                $out = str_replace($marks[$name], '<bdi>'.e((string) $value).'</bdi>', $out);
            }

            return $out;
        };

        // After a decline the Business edits and sends the request again; the note the partner
        // wrote stays on the request until the next decision. It is quoted beside the changes only
        // while the last decision on the request was that decline.
        $bpLastDecision = \App\Models\AuditLog::where('model_type', 'BoostCampaign')
            ->whereIn('model_id', $requests->pluck('id'))
            ->whereIn('action', [\App\Services\AuditService::BOOST_PARTNER_APPROVE, \App\Services\AuditService::BOOST_PARTNER_DECLINE])
            ->orderBy('id')
            ->get(['model_id', 'action'])
            ->groupBy('model_id')
            ->map(fn ($rows) => $rows->last()->action);

        $bpFocus = session('focus_request');
        $bpPageName = $partner->displayName();
    @endphp

    <div class="page-shell">
        <x-page-header :title="__('messages.boost')" :lead="__('messages.boost_partner_lead')" />

        @include('boost.partials.partner-nav', ['tab' => 'requests'])

        <div class="page-stack">
            <x-page-flash :keys="['success' => 'success', 'error' => 'error']" />

            @if ($schedules->count() > 1)
            <form method="GET" action="{{ route('boost.partner.requests') }}" class="page-filters" style="margin-bottom: 0">
                <div class="page-filter is-grow">
                    <label for="bp-request-schedule">{{ __('messages.schedule') }}</label>
                    <select id="bp-request-schedule" name="schedule" data-searchable data-auto-submit
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <option value="">{{ __('messages.all_schedules') }}</option>
                        @foreach ($schedules as $bpSchedule)
                        @php $bpScheduleId = \App\Utils\UrlUtils::encodeId($bpSchedule->id); @endphp
                        <option value="{{ $bpScheduleId }}" @selected($selectedSchedule === $bpScheduleId)>{{ $bpSchedule->name }}</option>
                        @endforeach
                    </select>
                </div>
                <noscript>
                    <div class="is-end"><x-brand-button type="submit">{{ __('messages.filter') }}</x-brand-button></div>
                </noscript>
            </form>
            @endif

            @if ($requests->isEmpty() && filled($selectedSchedule))
            <div class="ap-card rounded-xl">
                <x-page-empty compact :title="__('messages.boost_partner_no_requests_here')">
                    <a href="{{ route('boost.partner.requests') }}" class="event-link">{{ __('messages.all_schedules') }}</a>
                </x-page-empty>
            </div>
            @elseif ($requests->isEmpty())
            {{-- Nothing waits yet: what to do first, in order, each step done or not, each
                 leading to where it is done. --}}
            @php
                $bpSteps = [
                    ['done' => $partner->hasAssets(), 'title' => __('messages.boost_partner_setup_connect'), 'text' => __('messages.boost_partner_setup_connect_text'), 'href' => route('profile.edit').'#section-boost-account'],
                    ['done' => $partner->isUsable(), 'title' => __('messages.boost_partner_setup_confirm'), 'text' => __('messages.boost_partner_setup_confirm_text'), 'href' => route('profile.edit').'#section-boost-account'],
                    ['done' => $partner->hasCard(), 'title' => __('messages.boost_partner_setup_card'), 'text' => __('messages.boost_partner_setup_card_text'), 'href' => route('profile.edit').'#section-boost-card'],
                    ['done' => false, 'title' => __('messages.boost_partner_setup_tell'), 'text' => __('messages.boost_partner_setup_tell_text'), 'href' => null],
                ];
            @endphp
            <x-page-card :title="__('messages.boost_partner_setup_title')" :lead="__('messages.boost_partner_setup_lead')" flush>
                <ol class="bp-setup">
                    @foreach ($bpSteps as $bpIndex => $bpStep)
                    <li class="{{ $bpStep['done'] ? 'is-done' : '' }}">
                        <span class="bp-setup-mark {{ $bpStep['done'] ? 'border-transparent bg-[var(--brand-button-bg)] text-white' : '' }}" aria-hidden="true">{!! $bpStep['done'] ? '&#10003;' : $bpIndex + 1 !!}</span>
                        <span class="bp-setup-text">
                            <strong>{{ $bpStep['title'] }}@if ($bpStep['done'])<span class="sr-only"> ({{ __('messages.boost_partner_setup_done') }})</span>@endif</strong>
                            {{ $bpStep['text'] }}
                            @if ($bpStep['href'] && ! $bpStep['done'])
                            <a href="{{ $bpStep['href'] }}" class="event-link">{{ __('messages.boost_partner_open_settings') }}</a>
                            @endif
                        </span>
                    </li>
                    @endforeach
                </ol>
            </x-page-card>
            @else
            @foreach ($requests as $bpRequest)
            @php
                $bpHash = $bpRequest->hashedId();
                $bpVersion = $bpRequest->versions->firstWhere('version', $bpRequest->version);
                $bpPrevious = $bpRequest->version > 1 ? $bpRequest->versions->firstWhere('version', $bpRequest->version - 1) : null;
                $bpSince = $bpVersion?->created_at ?? $bpRequest->submitted_at ?? $bpRequest->created_at;
                $bpDays = $bpSince ? (int) floor($bpSince->diffInDays(now())) : 0;
                $bpDeclineNote = ($bpLastDecision[$bpRequest->id] ?? null) === \App\Services\AuditService::BOOST_PARTNER_DECLINE ? $bpRequest->approval_note : null;
                $bpEventName = $bpRequest->event?->translatedName() ?? $bpRequest->event_name;
                $bpScheduleName = $bpRequest->role?->getDisplayName(false) ?? $bpRequest->schedule_name;
            @endphp
            <article id="request-{{ $bpHash }}" class="ap-card rounded-xl bp-request" tabindex="-1" aria-labelledby="request-{{ $bpHash }}-title"
                @if ($bpFocus === $bpHash) data-bp-focus @endif>
                <div class="bp-request-head">
                    <div class="min-w-0">
                        <h2 id="request-{{ $bpHash }}-title" class="bp-request-title">
                            <a href="{{ route('boost.show', ['hash' => $bpHash]) }}"><bdi>{{ $bpEventName }}</bdi></a>
                        </h2>
                        <p class="bp-request-meta">{!! $bpSay('messages.boost_partner_requested_by', ['name' => $bpRequest->user?->name ?? __('messages.deleted'), 'schedule' => $bpScheduleName]) !!}</p>
                        <p class="bp-request-meta is-quiet">
                            {{ $bpDays === 0 ? __('messages.boost_partner_sent_today') : trans_choice('messages.boost_partner_waiting_days', $bpDays, ['count' => $bpDays]) }}
                            &middot; {{ __('messages.boost_partner_version', ['version' => $bpRequest->version]) }}
                        </p>
                    </div>
                    @include('boost.partials.status', ['status' => $bpRequest->status, 'partner' => true])
                </div>

                @include('boost.partials.partner-ad', ['bpCampaign' => $bpRequest, 'bpVersion' => $bpVersion, 'bpPageName' => $bpPageName])

                @include('boost.partials.partner-changes', ['bpVersion' => $bpVersion, 'bpPrevious' => $bpPrevious, 'bpCurrency' => $bpRequest->currency_code, 'bpDeclineNote' => $bpDeclineNote])

                @if ($bpRequest->status === 'ready' && ! $partner->isUsable())
                <div class="px-5 pb-4">
                    <x-page-notice tone="info">{{ __('messages.boost_partner_ready_waits_for_account') }}</x-page-notice>
                </div>
                @endif

                @include('boost.partials.partner-decisions', ['bpCampaign' => $bpRequest, 'bpIsPartner' => true, 'bpPageName' => $bpPageName])
            </article>
            @endforeach
            @endif
        </div>

        @include('boost.partials.partner-dialogs')
    </div>

    {{-- A request that changed while it was being answered: the page comes back here with the
         message as an alert, and the request it was about is the one in view and in focus. --}}
    <script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function () {
        var card = document.querySelector('[data-bp-focus]');
        if (! card) {
            return;
        }
        card.classList.add('is-focused');
        card.scrollIntoView({ block: 'start' });
        card.focus({ preventScroll: true });
        card.addEventListener('blur', function () {
            card.classList.remove('is-focused');
        }, { once: true });
    });
    </script>

</x-app-admin-layout>
