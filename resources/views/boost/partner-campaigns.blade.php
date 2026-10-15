<x-app-admin-layout>

    <x-slot name="head">
        @include('boost.partials.styles')
    </x-slot>

    @php
        // Every boost that went to Facebook under the partner's Page, for reconciling: what the
        // Business paid the partner beside what the ad spent. The spend of an ended boost keeps
        // moving for a few days, so the difference says "not final" until it stops.
        $bpField = 'block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]';
        $bpStatuses = ['in_review', 'active', 'completed', 'rejected', 'stopped'];
        $bpMonths = collect(range(0, 23))->map(fn ($back) => now()->startOfMonth()->subMonthsNoOverflow($back));
        $bpFiltered = collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty();
        $bpExportQuery = collect($filters)->filter(fn ($value) => filled($value))->all();
    @endphp

    <div class="page-shell">
        <x-page-header :title="__('messages.boost')" :lead="__('messages.boost_partner_lead')" />

        @include('boost.partials.partner-nav', ['tab' => 'campaigns'])

        <div class="page-stack">
            <x-page-flash :keys="['success' => 'success', 'error' => 'error']" />

            <form method="GET" action="{{ route('boost.partner.campaigns') }}" class="page-filters" style="margin-bottom: 0">
                <div class="page-filter">
                    <label for="bp-filter-status">{{ __('messages.status') }}</label>
                    <select id="bp-filter-status" name="status" class="{{ $bpField }}">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        @foreach ($bpStatuses as $bpStatus)
                        <option value="{{ $bpStatus }}" @selected(($filters['status'] ?? '') === $bpStatus)>{{ __('messages.boost_partner_status_'.$bpStatus) }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($schedules->count() > 1)
                <div class="page-filter is-grow">
                    <label for="bp-filter-schedule">{{ __('messages.schedule') }}</label>
                    <select id="bp-filter-schedule" name="schedule" data-searchable class="{{ $bpField }}">
                        <option value="">{{ __('messages.all_schedules') }}</option>
                        @foreach ($schedules as $bpSchedule)
                        @php $bpScheduleId = \App\Utils\UrlUtils::encodeId($bpSchedule->id); @endphp
                        <option value="{{ $bpScheduleId }}" @selected(($filters['schedule'] ?? '') === $bpScheduleId)>{{ $bpSchedule->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="page-filter">
                    <label for="bp-filter-month">{{ __('messages.boost_partner_month') }}</label>
                    <select id="bp-filter-month" name="month" class="{{ $bpField }}">
                        <option value="">{{ __('messages.boost_partner_all_months') }}</option>
                        @foreach ($bpMonths as $bpMonth)
                        <option value="{{ $bpMonth->format('Y-m') }}" @selected(($filters['month'] ?? '') === $bpMonth->format('Y-m'))>{{ $bpMonth->translatedFormat('F Y') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="page-filter">
                    <span>{{ __('messages.boost_partner_only') }}</span>
                    <div class="bp-filter-checks">
                        <label class="boost-check">
                            <input type="checkbox" name="unpaid" value="1" @checked(! empty($filters['unpaid']))
                                class="rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] shadow-sm focus:ring-[var(--brand-blue)]">
                            <span>{{ __('messages.boost_partner_filter_unpaid') }}</span>
                        </label>
                        <label class="boost-check">
                            <input type="checkbox" name="not_final" value="1" @checked(! empty($filters['not_final']))
                                class="rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] shadow-sm focus:ring-[var(--brand-blue)]">
                            <span>{{ __('messages.boost_partner_filter_not_final') }}</span>
                        </label>
                    </div>
                </div>
                <div class="is-end">
                    @if ($bpFiltered)
                    <a href="{{ route('boost.partner.campaigns') }}" class="event-link">{{ __('messages.clear_filters') }}</a>
                    @endif
                    <x-brand-button type="submit">{{ __('messages.filter') }}</x-brand-button>
                    <a href="{{ route('boost.partner.export', $bpExportQuery) }}" class="page-tool">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        {{ __('messages.boost_partner_export_csv') }}
                    </a>
                </div>
            </form>

            {{-- One strip of totals per currency: an ad account changes currency only with nothing
                 open, so a list can hold more than one. --}}
            @foreach ($totals as $bpTotal)
            @php
                $bpTotalCurrency = $bpTotal->currency_code;
                // Paid minus spend over the campaigns that are paid, as each row says it.
                $bpDifference = (float) $bpTotal->paid - (float) ($bpTotal->paid_spend ?? $bpTotal->spend);
            @endphp
            <div class="ap-card rounded-xl page-stats is-auto" @if ($totals->count() > 1) role="group" aria-label="{{ $bpTotalCurrency }}" @endif>
                <div class="page-stat">
                    <div class="page-stat-value">{{ \App\Utils\MoneyUtils::format($bpTotal->budget, $bpTotalCurrency) }}</div>
                    <div class="page-stat-label">{{ __('messages.ad_budget') }}</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ \App\Utils\MoneyUtils::format($bpTotal->paid, $bpTotalCurrency) }}</div>
                    <div class="page-stat-label">{{ __('messages.paid') }}</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ \App\Utils\MoneyUtils::format($bpTotal->spend, $bpTotalCurrency) }}</div>
                    <div class="page-stat-label">{{ __('messages.spend') }}</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value {{ $bpDifference < 0 ? 'is-bad' : '' }}">{{ \App\Utils\MoneyUtils::format($bpDifference, $bpTotalCurrency) }}</div>
                    <div class="page-stat-label">{{ __('messages.boost_partner_paid_minus_spend') }}</div>
                </div>
            </div>
            @endforeach

            @if ($campaigns->count() > 0)
            <div class="ap-card rounded-xl overflow-hidden">
                <div class="page-scroll">
                    <table class="page-table is-hover is-wide">
                        <thead>
                            <tr>
                                <th scope="col">{{ __('messages.event') }}</th>
                                <th scope="col">{{ __('messages.status') }}</th>
                                <th scope="col" class="c-num">{{ __('messages.ad_budget') }}</th>
                                <th scope="col" class="c-num">{{ __('messages.paid') }}</th>
                                <th scope="col" class="c-num">{{ __('messages.spend') }}</th>
                                <th scope="col" class="c-num">{{ __('messages.boost_partner_paid_minus_spend') }}</th>
                                <th scope="col">{{ __('messages.ends') }}</th>
                                <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($campaigns as $bpCampaign)
                            @php
                                $bpPaidCurrency = $bpCampaign->payment_currency ?: $bpCampaign->currency_code;
                                $bpHasPayment = $bpCampaign->payment_amount !== null;
                                $bpSameCurrency = strtoupper((string) $bpPaidCurrency) === strtoupper((string) $bpCampaign->currency_code);
                                $bpZone = $bpCampaign->role?->timezone ?: config('app.timezone');
                                $bpEnded = $bpCampaign->stopped_at ?? $bpCampaign->scheduled_end;
                            @endphp
                            <tr>
                                <td class="c-main c-strong">
                                    <a href="{{ route('boost.show', ['hash' => $bpCampaign->hashedId()]) }}" class="event-link"><bdi>{{ $bpCampaign->event?->translatedName() ?? $bpCampaign->event_name ?? __('messages.deleted_event') }}</bdi></a>
                                    <span class="c-sub"><bdi>{{ $bpCampaign->role?->getDisplayName(false) ?? $bpCampaign->schedule_name }}</bdi></span>
                                </td>
                                <td>@include('boost.partials.status', ['status' => $bpCampaign->status, 'partner' => true])</td>
                                <td class="c-num" data-label="{{ __('messages.ad_budget') }}">{{ \App\Utils\MoneyUtils::format($bpCampaign->user_budget, $bpCampaign->currency_code) }}</td>
                                <td class="c-num" data-label="{{ __('messages.paid') }}">@if ($bpHasPayment){{ \App\Utils\MoneyUtils::format($bpCampaign->payment_amount, $bpPaidCurrency) }}@if (filled($bpCampaign->payment_reference))<span class="c-sub c-mono"><span class="c-clip" title="{{ $bpCampaign->payment_reference }}" dir="ltr">{{ $bpCampaign->payment_reference }}</span></span>@endif @endif</td>
                                <td class="c-num" data-label="{{ __('messages.spend') }}">{{ \App\Utils\MoneyUtils::format($bpCampaign->actual_spend ?? 0, $bpCampaign->currency_code) }}</td>
                                <td class="c-num" data-label="{{ __('messages.boost_partner_paid_minus_spend') }}">@if ($bpHasPayment && $bpSameCurrency){{ \App\Utils\MoneyUtils::format((float) $bpCampaign->payment_amount - (float) ($bpCampaign->actual_spend ?? 0), $bpCampaign->currency_code) }}@unless ($bpCampaign->spend_final_at)<span class="c-sub">{{ __('messages.boost_partner_not_final') }}</span>@endunless @endif</td>
                                <td class="c-date" data-label="{{ __('messages.ends') }}">@if ($bpEnded){{ $bpEnded->copy()->tz($bpZone)->translatedFormat('M j, Y') }}@endif</td>
                                <td class="c-actions">
                                    @if (in_array($bpCampaign->status, \App\Models\BoostCampaign::PARTNER_LIVE_STATUSES, true))
                                    {{-- Stop, with a reason the Business is told (boost/partials/partner-dialogs). --}}
                                    <button type="button" class="page-tool is-danger" data-bp-open="stop"
                                        data-action="{{ route('boost.partner.stop', ['hash' => $bpCampaign->hashedId()]) }}"
                                        data-name="{{ $bpCampaign->event?->translatedName() ?? $bpCampaign->event_name }}">{{ __('messages.boost_partner_stop') }}</button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($campaigns->hasPages())
            <div class="page-pager">{{ $campaigns->links() }}</div>
            @endif
            @else
            <div class="ap-card rounded-xl">
                @if ($bpFiltered)
                <x-page-empty compact :title="__('messages.boost_partner_no_campaigns_match')">
                    <a href="{{ route('boost.partner.campaigns') }}" class="event-link">{{ __('messages.clear_filters') }}</a>
                </x-page-empty>
                @else
                <x-page-empty :title="__('messages.boost_partner_no_campaigns')" :text="__('messages.boost_partner_no_campaigns_text')"
                    icon="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                @endif
            </div>
            @endif
        </div>

        @include('boost.partials.partner-dialogs')
    </div>

</x-app-admin-layout>
