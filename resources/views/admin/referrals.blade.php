<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'referrals'])

    @php
        // The states a referral moves through, with the word and the mark each one wears. Amber is
        // the one that asks for something: qualified, and its credit not yet given.
        $statuses = [
            'pending' => [__('messages.pending'), '', $pending],
            'subscribed' => [__('messages.status_subscribed'), 'is-info', $subscribed],
            'qualified' => [__('messages.qualified'), 'is-warn', $qualified],
            'credited' => [__('messages.credited'), 'is-on', $credited],
            'expired' => [__('messages.expired'), 'is-bad', $expired],
        ];
    @endphp

    <div class="page-head">
        <p class="page-lead">{{ __('messages.admin_referrals_lead') }}</p>
    </div>

    <div class="page-shell page-stack">
        {{-- One strip for the figures that were six boxes, and the seventh state the list could
             already be narrowed to but the boxes never counted. --}}
        <div class="ap-card rounded-xl page-stats is-auto insight-strip">
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($totalReferrals) }}</div>
                <div class="page-stat-label">{{ __('messages.total_referrals') }}</div>
            </div>
            @foreach ($statuses as [$label, $tone, $count])
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($count) }}</div>
                <div class="page-stat-label">{{ $label }}</div>
            </div>
            @endforeach
            <div class="page-stat">
                <div class="page-stat-value"><span dir="ltr">{{ $conversionRate }}%</span></div>
                <div class="page-stat-label">{{ __('messages.conversion_rate') }}</div>
            </div>
        </div>

        <div>
            <nav class="page-filters" aria-label="{{ __('messages.filter') }}">
                <span class="event-chips-label">{{ __('messages.status') }}</span>
                <a href="{{ route('admin.referrals') }}" class="page-pill" @if (! $statusFilter) aria-current="true" @endif>{{ __('messages.all') }}</a>
                @foreach ($statuses as $status => [$label, $tone, $count])
                <a href="{{ route('admin.referrals', ['status' => $status]) }}" class="page-pill" @if ($statusFilter === $status) aria-current="true" @endif>{{ $label }}</a>
                @endforeach
            </nav>

            @if ($referrals->count() > 0)
            <div class="ap-card rounded-xl overflow-hidden page-scroll">
                <table class="page-table is-wide">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.referred_user') }}</th>
                            <th scope="col">{{ __('messages.referrer') }}</th>
                            <th scope="col">{{ __('messages.plan_tier') }}</th>
                            <th scope="col">{{ __('messages.status') }}</th>
                            <th scope="col">{{ __('messages.credited_to') }}</th>
                            <th scope="col">{{ __('messages.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($referrals as $referral)
                        <tr>
                            <td class="c-main c-wrap">
                                <span class="c-strong"><bdi>{{ $referral->referredUser->name ?? '-' }}</bdi></span>
                                <span class="c-sub" dir="ltr">{{ $referral->referredUser->email ?? '' }}</span>
                            </td>
                            <td class="c-wrap" data-label="{{ __('messages.referrer') }}">
                                <bdi>{{ $referral->referrer->name ?? '-' }}</bdi>
                                <span class="c-sub" dir="ltr">{{ $referral->referrer->email ?? '' }}</span>
                            </td>
                            <td>@if ($referral->plan_type)<span class="event-chip">{{ in_array($referral->plan_type, ['pro', 'enterprise'], true) ? __('messages.'.$referral->plan_type) : ucfirst($referral->plan_type) }}</span>@endif</td>
                            <td>@isset($statuses[$referral->status])<span class="event-status {{ $statuses[$referral->status][1] }}">{{ $statuses[$referral->status][0] }}</span>@endisset</td>
                            <td class="c-wrap" data-label="{{ __('messages.credited_to') }}">@if ($referral->creditedRole)<bdi>{{ $referral->creditedRole->name }}</bdi>@endif</td>
                            <td class="c-date">{{ $referral->created_at->format('M j, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($referrals->hasPages())
            <div class="page-pager">
                {{ $referrals->links() }}
            </div>
            @endif
            @else
            <div class="ap-card rounded-xl">
                <x-page-empty :title="__('messages.no_referrals_found')"
                    icon="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z">
                    @if ($statusFilter)
                    <x-secondary-link :href="route('admin.referrals')">{{ __('messages.clear_filter') }}</x-secondary-link>
                    @endif
                </x-page-empty>
            </div>
            @endif
        </div>
    </div>

    <x-slot name="head">
        @include('admin.partials._insight-styles')
    </x-slot>

</x-app-admin-layout>
