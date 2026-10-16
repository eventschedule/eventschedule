{{-- A funnel on the Revenue tab: what a boost (or a newsletter) put in front of people, and how
     many went on at each step. Each step says its number, and the rate from the step before
     stands on the arrow between them, where it belongs: it is the step, not either end. Then the
     campaigns of the window as a list.

     Given: $funnel ('boost' or 'newsletter'), $stats (AnalyticsService::getBoostStats() or
     getNewsletterStats()). --}}
@php
    $fmoney = fn ($amount, $currency) => \App\Utils\MoneyUtils::format($amount, $currency ?: 'USD');
    $pct = fn ($part, $whole, $places = 1) => $whole > 0 ? number_format($part / $whole * 100, $places).'%' : null;
    if ($funnel === 'boost') {
        $spendCurrency = $stats['spend_currency'] ?? null;
        $steps = [
            ['label' => __('messages.impressions'), 'value' => $stats['total_impressions'], 'note' => $fmoney($stats['total_spend'], $spendCurrency).' '.__('messages.spent')],
            ['label' => __('messages.clicks'), 'value' => $stats['total_clicks'], 'rate' => $stats['avg_ctr'] > 0 ? number_format($stats['avg_ctr'], 1).'% CTR' : null, 'note' => $stats['avg_cpc'] > 0 ? $fmoney($stats['avg_cpc'], $spendCurrency).' / '.__('messages.click') : null],
            ['label' => __('messages.realtime_views'), 'value' => $stats['boost_views'], 'rate' => $stats['total_clicks'] > 0 && $stats['boost_views'] <= $stats['total_clicks'] ? $pct($stats['boost_views'], $stats['total_clicks'], 0).' '.__('messages.landed') : null, 'note' => $stats['cost_per_view'] > 0 ? $fmoney($stats['cost_per_view'], $spendCurrency).' / '.__('messages.view') : null],
            ['label' => __('messages.sales'), 'value' => $stats['boost_sales'], 'rate' => $stats['boost_views'] > 0 && $stats['boost_sales'] > 0 ? $pct($stats['boost_sales'], $stats['boost_views']).' '.__('messages.converted') : null, 'note' => $stats['cost_per_sale'] > 0 ? $fmoney($stats['cost_per_sale'], $spendCurrency).' / '.__('messages.sale') : null],
        ];
        $roas = $stats['roas'] ?? null;
        $revenueLine = collect($stats['boost_revenue_by_currency'] ?? [])->map(fn ($e) => $fmoney($e['amount'], $e['currency_code']))->implode(' + ');
    } else {
        $steps = [
            ['label' => __('messages.total_sent'), 'value' => $stats['total_sent'], 'note' => __('messages.newsletters').': '.count($stats['campaigns'])],
            ['label' => __('messages.opens'), 'value' => $stats['total_opens'], 'rate' => $stats['open_rate'].'% '.__('messages.open_rate')],
            ['label' => __('messages.clicks'), 'value' => $stats['total_clicks'], 'rate' => $stats['click_rate'].'% '.__('messages.click_rate')],
            // A reader opens a page more than once, so page views can outrun clicks: a "landed"
            // above 100% read as broken, and is left out.
            ['label' => __('messages.realtime_views'), 'value' => $stats['newsletter_views'], 'rate' => $stats['total_clicks'] > 0 && $stats['newsletter_views'] <= $stats['total_clicks'] ? $pct($stats['newsletter_views'], $stats['total_clicks'], 0).' '.__('messages.landed') : null],
            ['label' => __('messages.sales'), 'value' => $stats['newsletter_sales'], 'note' => collect($stats['newsletter_revenue_by_currency'] ?? [])->map(fn ($e) => $fmoney($e['amount'], $e['currency_code']))->implode(' + ') ?: null],
        ];
        $roas = null;
        $revenueLine = null;
    }
    // Completed is a plain mark: in the link's blue it read as one more link beside View Details.
    $statusTone = ['active' => 'is-on', 'paused' => 'is-warn'];
@endphp
<x-page-card :title="__($funnel === 'boost' ? 'messages.boost_funnel' : 'messages.newsletter_funnel')" class="an-card an-funnel">
    @if ($funnel === 'boost' && $revenueLine)
    <x-slot:aside>
        <span class="an-roas {{ is_null($roas) ? '' : ($roas >= 1 ? 'is-good' : 'is-warn') }}" title="{{ __('messages.roas') }}">
            @if (! is_null($roas))<strong>{{ number_format($roas, 1) }}×</strong> {{ __('messages.roas') }} <span aria-hidden="true">·</span>@endif
            {{ $revenueLine }} {{ __('messages.revenue') }}
        </span>
    </x-slot:aside>
    @endif
    <ol class="an-steps" style="--n: {{ count($steps) }}" role="list">
        @foreach ($steps as $step)
        <li class="an-step">
            @if (! $loop->first)
            <span class="an-step-arrow" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg></span>
            @endif
            <p class="an-step-label">{{ $step['label'] }}</p>
            <p class="an-step-value">{{ number_format($step['value']) }}</p>
            @if (! empty($step['rate']))<p class="an-step-rate">{{ $step['rate'] }}</p>@endif
            @if (! empty($step['note']))<p class="an-step-note">{{ $step['note'] }}</p>@endif
        </li>
        @endforeach
    </ol>

    @if (count($stats['campaigns']) > 0)
    <h3 class="an-subtitle">{{ __($funnel === 'boost' ? 'messages.boost_campaigns_in_period' : 'messages.newsletters_in_period') }}</h3>
    <div class="page-scroll">
    <table class="page-table is-compact an-table">
        <thead>
            <tr>
                @if ($funnel === 'boost')
                <th scope="col">{{ __('messages.event') }}</th>
                <th scope="col">{{ __('messages.status') }}</th>
                <th scope="col" class="c-num">{{ __('messages.spend') }}</th>
                <th scope="col" class="c-num">{{ __('messages.impressions') }}</th>
                <th scope="col" class="c-num">{{ __('messages.clicks') }}</th>
                @else
                <th scope="col">{{ __('messages.subject') }}</th>
                <th scope="col">{{ __('messages.date') }}</th>
                <th scope="col" class="c-num">{{ __('messages.sent') }}</th>
                <th scope="col" class="c-num">{{ __('messages.opens') }}</th>
                <th scope="col" class="c-num">{{ __('messages.clicks') }}</th>
                @endif
                <th scope="col"><span class="sr-only">{{ __('messages.view_details') }}</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stats['campaigns'] as $campaign)
            <tr>
                @if ($funnel === 'boost')
                <td class="c-main c-strong" v-pre><bdi>{{ $campaign['event_name'] }}</bdi></td>
                <td><span class="event-status {{ $statusTone[$campaign['status']] ?? '' }}">{{ __('messages.boost_status_'.$campaign['status']) }}</span></td>
                <td data-label="{{ __('messages.spend') }}" class="c-num">{{ $fmoney($campaign['spend'], $campaign['spend_currency'] ?? ($stats['spend_currency'] ?? null)) }}</td>
                <td data-label="{{ __('messages.impressions') }}" class="c-num">{{ number_format($campaign['impressions']) }}</td>
                <td data-label="{{ __('messages.clicks') }}" class="c-num">{{ number_format($campaign['clicks']) }}</td>
                <td class="c-actions"><a href="{{ route('boost.show', ['hash' => $campaign['hash']]) }}" class="event-link">{{ __('messages.view_details') }}</a></td>
                @else
                <td class="c-main c-strong" v-pre><bdi>{{ $campaign['subject'] }}</bdi></td>
                <td class="c-date">{{ $campaign['sent_at']->locale(app()->getLocale())->isoFormat('ll') }}</td>
                <td data-label="{{ __('messages.sent') }}" class="c-num">{{ number_format($campaign['sent_count']) }}</td>
                <td data-label="{{ __('messages.opens') }}" class="c-num">{{ number_format($campaign['open_count']) }} <span class="c-quiet">{{ $pct($campaign['open_count'], $campaign['sent_count'], 0) }}</span></td>
                <td data-label="{{ __('messages.clicks') }}" class="c-num">{{ number_format($campaign['click_count']) }} <span class="c-quiet">{{ $pct($campaign['click_count'], $campaign['sent_count'], 0) }}</span></td>
                <td class="c-actions"><a href="{{ route('newsletter.stats', ['hash' => $campaign['hash']]) }}" class="event-link">{{ __('messages.view_details') }}</a></td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif
    @if ($selectedEventId) @include('analytics._scope') @endif
</x-page-card>
