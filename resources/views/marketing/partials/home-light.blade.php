{{-- The light homepage's middle: what stands between the hero and the closing panel, which both
     homepages share, for a visitor given the light page (App\Utils\HeroExperiment, a variant whose
     page is 'light'). Hidden unless #hp carries data-home="light"; its styles are
     marketing/partials/home-light-styles, in the page's head. Nothing in it moves and it needs no
     script: no reveal, no counter, no marquee. It is written in English, like the page it is
     tested against. --}}
@php
    $hpLightFor = [
        ['Musicians', marketing_url('/for-musicians')],
        ['Venues', marketing_url('/for-venues')],
        ['DJs', marketing_url('/for-djs')],
        ['Promoters', marketing_url('/for-curators')],
        ['Comedians', marketing_url('/for-comedians')],
        ['Theaters', marketing_url('/for-theaters')],
        ['Festivals', marketing_url('/for-festivals')],
        ['Yoga Studios', marketing_url('/for-fitness-and-yoga')],
    ];
@endphp

<div class="hp-lite">

    <section class="hp-lt-for" aria-label="Who uses Event Schedule">
        <div class="hp-wrap">
            <ul>
                <li>Made for</li>
                @foreach ($hpLightFor as [$hpLightName, $hpLightHref])
                    <li><a href="{{ $hpLightHref }}">{{ $hpLightName }}</a></li>
                @endforeach
                <li><a href="{{ marketing_url('/use-cases') }}">Everyone else {!! $hpArrow !!}</a></li>
            </ul>
        </div>
    </section>

    <section class="hp-lt-acts" aria-labelledby="hp-lt-acts-title">
        <div class="hp-wrap">
            <h2 class="sr-only" id="hp-lt-acts-title">Plan, promote and sell from one calendar</h2>
            <div class="hp-lt-stage">
            {{-- Today's poster and the ticket to it, standing still: the one show the three acts are about. --}}
            <div class="hp-show" aria-hidden="true">
                <div class="hp-show-in">
                <div class="hp-poster">
                    <img src="{{ asset('images/demo/demo_flyer_jazz.webp') }}" alt="" width="800" height="600" loading="lazy" decoding="async">
                    <div class="hp-poster-top"><span>Live</span><span>{{ $wkSat->format('D j M') }}</span></div>
                    <div class="hp-poster-type">
                        <small>The Blue Note presents</small>
                        <strong>Jazz<br>Night</strong>
                        <span>Doors 8:00 PM · {{ $hpShowPriceText }}</span>
                    </div>
                </div>
                <div class="hp-stub">
                    <div class="hp-stub-head">
                        <small>Event Schedule</small>
                        <strong>Jazz Night</strong>
                        <span>Live jazz, five nights a week</span>
                    </div>
                    <div class="hp-stub-body">
                        <span class="hp-stub-rows">
                            <span><i>Where</i><b>The Blue Note</b></span>
                            <span><i>When</i><b>{{ $wkSat->format('D, M j') }}</b></span>
                            <span><i>Doors</i><b>8:00 PM</b></span>
                            <span><i>Tickets</i><b>{{ $hpShowTickets }} at {{ $hpShowPriceText }}</b></span>
                        </span>
                        <span class="hp-stub-foot"><span>GA x1</span><span>#0042</span></span>
                    </div>
                </div>
                </div>
            </div>
            <div class="hp-lt-three">
                <div class="hp-lt-act">
                    <p class="hp-lt-ix" aria-hidden="true">01 / 03</p>
                    <span class="hp-vbars" aria-hidden="true"><i class="is-now"></i><i></i><i></i></span>
                    <h3 class="hp-lt-verb"><span class="hp-ink-grad">Plan.</span></h3>
                    <p>Paste a poster or a link and get an event page. Your open hours for bookings sit on the same calendar.</p>
                    <a href="{{ marketing_url('/features') }}" class="hp-more">See every feature {!! $hpArrow !!}</a>
                </div>
                <div class="hp-lt-act">
                    <p class="hp-lt-ix" aria-hidden="true">02 / 03</p>
                    <span class="hp-vbars" aria-hidden="true"><i class="is-done"></i><i class="is-now"></i><i></i></span>
                    <h3 class="hp-lt-verb"><span class="hp-ink-grad">Promote.</span></h3>
                    <p>One link for your site, your bio and a QR code. People follow your calendar and hear from you by email.</p>
                    <a href="{{ route('marketing.demos') }}" class="hp-more">See real schedules {!! $hpArrow !!}</a>
                </div>
                <div class="hp-lt-act">
                    <p class="hp-lt-ix" aria-hidden="true">03 / 03</p>
                    <span class="hp-vbars" aria-hidden="true"><i class="is-done"></i><i class="is-done"></i><i class="is-now"></i></span>
                    <h3 class="hp-lt-verb"><span class="hp-ink-grad">Sell.</span></h3>
                    <p>Tickets with zero platform fees, paid straight into your own Stripe or PayPal account. Scan them at the door.</p>
                    <a href="{{ marketing_url('/features/ticketing') }}" class="hp-more">More about ticketing {!! $hpArrow !!}</a>
                </div>
            </div>
            </div>
        </div>
    </section>

    <section class="hp-lt-cost hp-alt" aria-labelledby="hp-lt-cost-title">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker">What it costs</span>
                <h2 class="hp-h2" id="hp-lt-cost-title">Free to start. <span class="hp-ink-grad hp-lt-line">Zero platform fees.</span></h2>
            </div>
            <div class="hp-figs">
                <div class="hp-fig">
                    <div class="text-gradient hp-num">0%</div>
                    <strong>No platform fees</strong>
                    <p>Payments land in your own Stripe or PayPal account, not ours</p>
                </div>
                <div class="hp-fig">
                    <div class="text-gradient hp-num">{{ plan_price(0) }}</div>
                    <strong>Free forever</strong>
                    <p>Unlimited events, schedules and free registrations</p>
                </div>
                <div class="hp-fig">
                    <div class="text-gradient hp-num">100%</div>
                    <strong>Open source</strong>
                    <p>AAL licensed. Selfhost it and every paid feature is included</p>
                </div>
            </div>
            <p class="hp-figs-note">{{ $hpPricedTicketNote }}</p>
            <p class="hp-lt-links">
                <a href="{{ marketing_url('/pricing') }}" class="hp-more">See the plans {!! $hpArrow !!}</a>
                <a href="{{ marketing_url('/open-source') }}" class="hp-more">Read the licence and the code {!! $hpArrow !!}</a>
                <a href="{{ marketing_url('/selfhost') }}" class="hp-more">Install it on your own server {!! $hpArrow !!}</a>
            </p>
        </div>
    </section>

    {{-- The quotes today's page prints (App\Utils\HomeQuotes), when there are any: proof, and
         short. A block of its own rather than the same one, so no id stands in the page twice
         and nothing here waits on a reveal. --}}
    @if (! empty($homeQuotes))
        <section class="hp-lt-quotes" aria-labelledby="hp-lt-quotes-title">
            <div class="hp-wrap">
                <h2 class="sr-only" id="hp-lt-quotes-title">Organizers on Event Schedule</h2>
                <div class="hp-quotes is-{{ count($homeQuotes) }}">
                    @foreach ($homeQuotes as $homeQuote)
                        @php
                            $quoteBy = $homeQuote['person'] !== '' ? $homeQuote['person'] : $homeQuote['name'];
                            $quoteAs = $homeQuote['title'] !== '' ? $homeQuote['title'] : ($homeQuote['person'] !== '' ? $homeQuote['name'] : '');
                        @endphp
                        <figure @class(['hp-quote', 'is-long' => mb_strlen($homeQuote['quote']) > 240])>
                            <blockquote><p><bdi>&ldquo;{{ $homeQuote['quote'] }}&rdquo;</bdi></p></blockquote>
                            <figcaption>
                                @if ($homeQuote['image'])
                                    <img src="{{ $homeQuote['image'] }}" alt="" width="48" height="48" loading="lazy" decoding="async">
                                @else
                                    <span class="hp-quote-initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($homeQuote['name'], 0, 1)) }}</span>
                                @endif
                                <span class="hp-quote-who">
                                    <b><bdi>{{ $quoteBy }}</bdi></b>
                                    @if ($quoteAs !== '')
                                        <i><bdi>{{ $quoteAs }}</bdi></i>
                                    @endif
                                </span>
                                <a href="{{ $homeQuote['url'] }}" class="hp-more">See their schedule<span class="sr-only">: {{ $homeQuote['name'] }}</span> {!! $hpArrow !!}</a>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</div>
