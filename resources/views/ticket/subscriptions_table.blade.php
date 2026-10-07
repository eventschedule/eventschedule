{{-- The Subscriptions tab of ticket/sales: who holds a pass, how much of it is used, and where.
     One .page-table whose rows open (ticket/sales owns the script), where the old tab was a
     stack of disclosure rows that hid the pass's name and its expiry on a phone. --}}
@php
    $subscriptions = collect($subscriptions ?? []);
    $totalVisits = $subscriptions->sum(fn ($s) => count($s['usages']));
    $subscriptionTones = ['active' => 'is-on', 'used_up' => 'is-warn'];
    $statusLabels = [
        'active' => __('messages.subscription_active'),
        'expired' => __('messages.subscription_expired'),
        'used_up' => __('messages.subscription_used_up'),
    ];
    $usageMarks = [
        'booked' => ['is-info', __('messages.booked')],
        'forfeited' => ['is-warn', __('messages.forfeited')],
        'attended' => ['is-on', __('messages.attended')],
    ];
@endphp

@if ($subscriptions->isEmpty())
<div class="ap-card rounded-xl">
    <x-page-empty :title="__('messages.no_subscriptions_yet')" :text="__('messages.no_subscriptions_yet_help')"
        icon="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
</div>
@else
<div class="page-stack">
    <div class="ap-card rounded-xl page-stats is-auto">
        <div class="page-stat">
            <div class="page-stat-value">{{ $subscriptions->count() }}</div>
            <div class="page-stat-label">{{ __('messages.subscriptions') }}</div>
        </div>
        <div class="page-stat">
            <div class="page-stat-value">{{ $totalVisits }}</div>
            <div class="page-stat-label">{{ __('messages.visits_redeemed') }}</div>
        </div>
    </div>

    <div class="ap-card rounded-xl overflow-hidden">
        <div class="page-scroll">
            <table class="page-table is-hover">
                <thead>
                    <tr>
                        <th scope="col">{{ __('messages.name') }}</th>
                        <th scope="col">{{ __('messages.ticket_type') }}</th>
                        <th scope="col">{{ __('messages.visits_used') }}</th>
                        <th scope="col">{{ __('messages.expires') }}</th>
                        <th scope="col">{{ __('messages.status') }}</th>
                        <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subscriptions as $sub)
                    <tr>
                        <td class="c-main">
                            <div class="sales-customer">
                                <button type="button" class="sales-open" data-toggle-row="pass-{{ $loop->index }}" aria-expanded="false">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                    <span class="sr-only">{{ __('messages.details') }}</span>
                                </button>
                                <div class="page-person-text">
                                    <span class="c-strong"><bdi>{{ $sub['name'] }}</bdi></span>
                                    <span class="c-sub"><a href="mailto:{{ $sub['email'] }}" class="event-link" dir="ltr" title="{{ $sub['email'] }}">{{ $sub['email'] }}</a></span>
                                </div>
                            </div>
                        </td>
                        <td class="c-wrap"><bdi>{{ $sub['ticket_type'] }}</bdi></td>
                        <td data-label="{{ __('messages.visits_used') }}">{{ $sub['limit_label'] }}</td>
                        <td class="c-date" @if ($sub['expires_at']) data-label="{{ __('messages.expires') }}" @endif>{{ $sub['expires_at'] }}</td>
                        <td><span class="event-status {{ $subscriptionTones[$sub['status']] ?? '' }}">{{ $statusLabels[$sub['status']] ?? $sub['status'] }}</span></td>
                        <td class="c-actions">
                            <a href="{{ $sub['ticket_url'] }}" target="_blank" rel="noopener" class="event-link">{{ __('messages.view_ticket') }}</a>
                        </td>
                    </tr>
                    <tr class="detail-row-pass-{{ $loop->index }} sales-detail hidden">
                        <td colspan="6" class="c-main">
                            <div class="sales-detail-body">
                                @if (count($sub['usages']) > 0)
                                <ul class="sales-lines">
                                    @foreach ($sub['usages'] as $usage)
                                    @php
                                        $usageMark = $usageMarks[$usage['kind'] ?? 'attended'] ?? $usageMarks['attended'];
                                        // The occurrence's day as the venue counts it, stored as text.
                                        try {
                                            $usageDay = $usage['date'] ? \Carbon\Carbon::parse($usage['date'])->translatedFormat('M j, Y') : '';
                                        } catch (\Exception $usageDayError) {
                                            $usageDay = $usage['date'];
                                        }
                                    @endphp
                                    <li>
                                        <span><bdi>{{ $usage['event'] }}</bdi></span>
                                        <span>{{ $usageDay }}</span>
                                        <span class="c-quiet">{{ $usage['time'] }}</span>
                                        <span class="event-status {{ $usageMark[0] }}">{{ $usageMark[1] }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                                @else
                                <p class="c-quiet">{{ __('messages.no_visits_yet') }}</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
