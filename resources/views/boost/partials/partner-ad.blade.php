{{-- One version of a partner boost's ad: as it will look in a feed, and beside it every word of it
     in full, with the budget, the dates and the audience. The preview cuts the headline and the
     link to one line, so what is approved is read here.

     Expects $bpCampaign (BoostCampaign), $bpVersion (BoostAdVersion or null) and $bpPageName
     (the Page the ad runs under). Names of their own: an include receives every variable of the
     view that includes it. --}}
@php
    $bpAdCta = [
        'LEARN_MORE' => __('messages.cta_learn_more'),
        'GET_TICKETS' => __('messages.cta_get_tickets'),
        'SIGN_UP' => __('messages.cta_sign_up'),
        'BOOK_TRAVEL' => __('messages.cta_book_now'),
    ];
    $bpAdLink = (string) ($bpVersion?->destination_url ?? '');
    $bpAdHost = $bpAdLink !== '' ? parse_url($bpAdLink, PHP_URL_HOST) : null;
    $bpAdZone = $bpCampaign->role?->timezone ?: config('app.timezone');
    $bpAdEnd = $bpVersion?->end_date ?? $bpCampaign->scheduled_end;
    $bpAdBudget = $bpVersion?->budget ?? $bpCampaign->user_budget;
    $bpAdAudience = \App\Utils\BoostAudience::summary($bpVersion?->targeting ?? $bpCampaign->targeting, $bpCampaign->event);
@endphp
<div class="bp-ad">
    <div>
        @include('boost.partials.ad-preview-mockup', [
            'headline' => $bpVersion?->headline,
            'primaryText' => $bpVersion?->primary_text,
            'imageUrl' => $bpVersion?->imageUrl(),
            'cta' => $bpVersion?->call_to_action,
            'pageName' => $bpPageName,
            'linkHost' => $bpAdHost,
            'description' => $bpVersion?->description,
        ])
    </div>
    <dl class="bp-fields">
        <div class="is-wide">
            <dt>{{ __('messages.headline') }}</dt>
            <dd><bdi>{{ $bpVersion?->headline }}</bdi></dd>
        </div>
        <div class="is-wide">
            <dt>{{ __('messages.primary_text') }}</dt>
            <dd><bdi class="bp-text-block">{{ $bpVersion?->primary_text }}</bdi></dd>
        </div>
        <div>
            <dt>{{ __('messages.description') }}</dt>
            @if (filled($bpVersion?->description))
            <dd><bdi>{{ $bpVersion->description }}</bdi></dd>
            @else
            <dd class="is-empty">{{ __('messages.none') }}</dd>
            @endif
        </div>
        <div>
            <dt>{{ __('messages.call_to_action') }}</dt>
            <dd>{{ $bpAdCta[$bpVersion?->call_to_action] ?? \Illuminate\Support\Str::headline(strtolower((string) $bpVersion?->call_to_action)) }}</dd>
        </div>
        <div class="is-wide">
            <dt>{{ __('messages.boost_partner_link') }}</dt>
            <dd class="is-link" dir="ltr">{{ $bpAdLink }}</dd>
        </div>
        <div>
            <dt>{{ __('messages.ad_budget') }}</dt>
            <dd>{{ \App\Utils\MoneyUtils::format($bpAdBudget, $bpCampaign->currency_code) }}</dd>
        </div>
        <div>
            <dt>{{ __('messages.boost_partner_runs') }}</dt>
            <dd>
                @if ($bpCampaign->scheduled_start && $bpAdEnd)
                {{ __('messages.boost_partner_date_range', ['start' => $bpCampaign->scheduled_start->copy()->tz($bpAdZone)->translatedFormat('M j, Y'), 'end' => $bpAdEnd->copy()->tz($bpAdZone)->translatedFormat('M j, Y')]) }}
                @elseif ($bpAdEnd)
                {{ __('messages.boost_partner_until_date', ['date' => $bpAdEnd->copy()->tz($bpAdZone)->translatedFormat('M j, Y')]) }}
                @endif
            </dd>
        </div>
        @if ($bpAdAudience !== '')
        <div class="is-wide">
            <dt>{{ __('messages.boost_partner_audience') }}</dt>
            <dd><bdi>{{ $bpAdAudience }}</bdi></dd>
        </div>
        @endif
    </dl>
</div>
