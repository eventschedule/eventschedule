{{-- The new homepage, "Keep the money" (a page round of App\Utils\HeroExperiment: a variant whose
     page is 'new'). A whole page, hero to closing panel, beside today's in one document while the
     round runs: hidden unless #hp carries data-home="new", which the picker at the top of #hp sets
     before either page is parsed. Its styles are marketing/partials/home-new-styles, in the page's
     head. Designed 2026-11 (~/.claude/plans/new-homepage, direction B): proof first (the real
     wall), then five promises, each answering one objection with one fact, the fee as the set
     piece.

     What it shares with today's page, so both arms work the same way: the name boxes (.es-claim,
     which initClaim() in marketing-home.js carries into sign-up), the posters that take the typed
     name (data-claim-poster), the dock at the foot of a phone's screen, and the wall's posters
     ($wallCards: the same images). Every picture here is lazy: today's page, hidden or not, asks
     for the first six eagerly, so they are in the cache by the time this wall is laid out, and the
     homepage's eager budget (ImageVariantsTest) holds for the document as a whole.

     Expects from the homepage: $hpNewCopy (the variant's headline and subtitle), $hpNewAlone (it
     is the only page printed), $wallCards, $hpWallIsReal, $claimSuffix and $hpArrow. Every
     price of ours goes through plan_price(). Written in English, like the page it is tested
     against. --}}
@php
    $hnPro = plan_price(\App\Utils\PlatformPricing::proMonthly());
    $hnEnterprise = plan_price(\App\Utils\PlatformPricing::enterpriseMonthly());
    $hnCheck = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4 10 4 4 8-8"/></svg>';
    // Two drifting rows of the wall's posters: the first starts with the visitor's own.
    $hnRows = [array_slice($wallCards, 0, 12), array_slice($wallCards, 12)];
    $hnFaqs = [
        ['q' => 'Do my buyers pay a fee on top?', 'a' => 'No. Event Schedule adds nothing at checkout, so your buyers pay the price you set. Your payment provider takes its own processing fee out of the payment, as it would anywhere.'],
        ['q' => 'How is this different from Eventbrite?', 'a' => 'Eventbrite adds its own fee to every paid ticket. Event Schedule takes no platform fee, and payments go straight to your own Stripe or PayPal account. Your events live on a calendar of your own that people can follow, with newsletters and appointment booking built in, and the code is open source.'],
        ['q' => 'Can I refund a ticket?', 'a' => 'Yes, in full or in part, from the Sales page. Refund a Stripe, PayPal or Razorpay sale and the money goes back through that provider; a sale paid any other way can be marked as refunded.'],
        ['q' => 'Do the performers and venues I list need an account?', 'a' => 'No. Name them and the event page still shows them, with a page of their own they can claim later by signing in with the address you entered.'],
        ['q' => 'Can I use my own domain?', 'a' => 'Every schedule gets a free subdomain. On Enterprise the whole page can run on a domain you own, and a selfhosted install runs on your domain from day one.'],
        ['q' => 'Who is it for?', 'a' => 'Anyone who keeps a schedule other people need to see: venues, bars and theatres, musicians and comedians, promoters and curators, studios, libraries and community groups.'],
    ];
    // The headline is the page's h1 only when this page is printed alone. While the round runs
    // the document holds both pages, and today's hero carries the one h1: this one is a heading
    // by role, which is what a reader given this page hears (today's is hidden from them).
    $hnHeadingTag = $hpNewAlone ? 'h1' : 'p';
@endphp

@if ($hpNewAlone)
    <x-seo.faq-schema :items="$hnFaqs" />
@endif

<div class="hn">

    {{-- The address the posters print after a typed name, which the page's script reads from
         today's film screen. Printed alone, this page brings its own. --}}
    @if ($hpNewAlone)
        <span data-claim-echo hidden>{{ ltrim($claimSuffix, '.') }}</span>
    @endif

    {{-- Hero: the promise, the box, three facts. Text only. --}}
    <section id="hn-top" class="hn-hero">
        <div class="hn-wrap hn-hgrid">
            <div>
                <p class="hn-eyebrow">The free event calendar</p>
                <{{ $hnHeadingTag }} class="hn-h1" @if (! $hpNewAlone) role="heading" aria-level="1" @endif>
                    <span>{{ $hpNewCopy['line1'] }}</span>
                    <span class="hp-ink-grad">{{ $hpNewCopy['line2'] }}</span>
                </{{ $hnHeadingTag }}>
                <p class="hn-lede">{{ $hpNewCopy['subtitle'] }}</p>

                <div class="hn-claimwrap">
                    <label for="hn-claim-hero" class="hp-claim-label">Pick your link</label>
                    <div class="hp-claimrow">
                        <div dir="ltr" class="es-claim hp-claim">
                            <input id="hn-claim-hero" type="text" placeholder="your-name" autocomplete="off" autocapitalize="none" spellcheck="false" maxlength="30">
                            <span class="hp-claim-suffix">{{ $claimSuffix }}</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary">Claim it free {!! $hpArrow !!}</a>
                    </div>
                    <p class="hn-under">No credit card. Set up in under two minutes.</p>
                </div>
            </div>

            <dl class="hn-ledger" aria-label="In short">
                <div><dt class="sr-only">Cost</dt><dd><b>{{ plan_price(0) }}</b><span>to start, and to stay on</span></dd></div>
                <div><dt class="sr-only">Platform fee</dt><dd><b class="hp-ink-grad">0%</b><span>platform fee on tickets</span></dd></div>
                <div><dt class="sr-only">Code</dt><dd><b>100%</b><span>open source</span></dd></div>
            </dl>
        </div>
    </section>

    {{-- Proof first: the real wall, drifting, with the visitor's own poster at its head. The copy
         of each row after the first pass is for the loop and is hidden from a screen reader. --}}
    <section class="hn-wall" aria-label="{{ $hpWallIsReal ? 'Events coming up on Event Schedule' : 'Posters' }}">
        <div class="hn-wrap hn-wall-cap">
            @if ($hpWallIsReal)
                <b><i aria-hidden="true"></i>Coming up on Event Schedule</b>
            @else
                <b>Your poster, among them</b>
            @endif
            <a href="{{ marketing_url('/browse') }}">See them all</a>
        </div>
        @foreach ($hnRows as $hnRowIndex => $hnRow)
            @if (count($hnRow))
                <div class="hn-row {{ $hnRowIndex ? 'is-rev' : '' }}" style="--t: {{ $hnRowIndex ? 130 : 110 }}s">
                    @foreach ([false, true] as $hnCopy)
                        @if ($hnRowIndex === 0)
                            <figure class="hn-fig" @if ($hnCopy) aria-hidden="true" @endif>
                                <div class="hn-mine" data-claim-poster data-claim-for="hn-claim-hero" aria-hidden="true">
                                    <small><span>On the wall</span><span>This week</span></small>
                                    <strong data-claim-name="title">Your name</strong>
                                    <span data-claim-name="url">your-name{{ $claimSuffix }}</span>
                                </div>
                                <figcaption>Your next event</figcaption>
                            </figure>
                        @endif
                        @foreach ($hnRow as $hnCard)
                            <figure class="hn-fig" @if ($hnCopy) aria-hidden="true" @endif>
                                @if ($hnCard['url'] && ! $hnCopy)
                                    <a href="{{ $hnCard['url'] }}" class="hn-card">
                                        <img src="{{ $hnCard['img'] }}" alt="{{ $hnCard['name'] }}" width="208" height="277" loading="lazy" decoding="async">
                                    </a>
                                @else
                                    <span class="hn-card">
                                        <img src="{{ $hnCard['img'] }}" alt="" width="208" height="277" loading="lazy" decoding="async">
                                    </span>
                                @endif
                                @if ($hnCard['name'])
                                    <figcaption>{{ $hnCard['name'] }}</figcaption>
                                @endif
                            </figure>
                        @endforeach
                    @endforeach
                </div>
            @endif
        @endforeach
    </section>

    {{-- 01: what free really includes, with its limits named. --}}
    <section class="hn-ch">
        <div class="hn-wrap hn-ch-grid">
            <div>
                <p class="hn-no">01 <i>/ 05</i></p>
                <h2 class="hn-h2">Free means free.</h2>
                <p class="hn-sub">No trial clock and no card. The free plan is a whole event calendar, kept free for as long as you use it.</p>
                <ul class="hn-free">
                    <li>{!! $hnCheck !!}Unlimited events and schedules</li>
                    <li>{!! $hnCheck !!}Unlimited free registration and RSVPs</li>
                    <li>{!! $hnCheck !!}QR tickets scanned at the door</li>
                    <li>{!! $hnCheck !!}Two-way calendar sync and an embed for your site</li>
                    <li>{!! $hnCheck !!}Appointment booking<span>one free type</span></li>
                    <li>{!! $hnCheck !!}Followers and newsletters<span>10 emails a month</span></li>
                </ul>
            </div>
            <div class="hn-zero-price">
                <div class="hn-big0">{{ plan_price(0) }}</div>
                <p class="hn-sub">Pro is {{ $hnPro }} a month when you put a price on a ticket. Enterprise is {{ $hnEnterprise }} a month for a team, your own domain and reserved seating.</p>
            </div>
        </div>
    </section>

    {{-- 02: the set piece. The fee, on the visitor's own numbers. --}}
    <section class="hn-ch hn-dark">
        <div class="hn-wrap hn-ch-grid">
            <div>
                <p class="hn-no">02 <i>/ 05</i></p>
                <p class="hn-pct" aria-hidden="true">0%</p>
                <h2 class="hn-pct-cap">0% of your ticket sales, on every plan.</h2>
                <p class="hn-sub">No platform fee, no per-ticket charge, nothing added at checkout. Your buyers pay the price you set. Move the sliders and see what stays with you.</p>
            </div>
            <div class="hn-calc" data-hn-calc data-money="{{ plan_price(7) }}" data-pro="{{ \App\Utils\PlatformPricing::proMonthly() }}">
                <h3>Your night</h3>
                <div class="hn-sl">
                    <label for="hn-q">Tickets sold <b data-hn-q-out>150</b></label>
                    <input id="hn-q" type="range" min="10" max="500" step="10" value="150" data-hn-q>
                </div>
                <div class="hn-sl">
                    <label for="hn-p">Ticket price <b data-hn-p-out>25</b></label>
                    <input id="hn-p" type="range" min="5" max="100" step="1" value="25" data-hn-p>
                </div>
                <div class="hn-out" aria-live="polite">
                    <div><span>Ticket sales</span><b data-hn-gross></b></div>
                    <div class="is-zero"><span>Event Schedule's cut</span><b>{{ plan_price(0) }}</b></div>
                    {{-- Stripe's own published US rate: a figure of theirs, not a price of ours. --}}
                    <div><span>Stripe's processing</span><b data-hn-stripe></b></div>
                    <div><span>Pro, this month</span><b>&minus;{{ $hnPro }}</b></div>
                    <div class="is-keep"><span>Yours</span><b data-hn-keep></b></div>
                </div>
                <p>Stripe's standard US card rate, 2.9% + 30&cent; per ticket. A free event costs nothing at all. Where a platform takes 5% a ticket, the same night would cost <b data-hn-five></b> more.</p>
            </div>
        </div>
    </section>

    {{-- 03: getting paid. Selling with a price is Pro, which 01 already said. --}}
    <section class="hn-ch">
        <div class="hn-wrap hn-ch-grid">
            <div>
                <p class="hn-no">03 <i>/ 05</i></p>
                <h2 class="hn-h2">Paid straight to you.</h2>
                <p class="hn-sub">Connect your own account and every sale lands there. We never hold your money, so there's no payout to wait for from us.</p>
            </div>
            <ul class="hn-pay">
                <li>Stripe<small>Cards, wallets, refunds</small></li>
                <li>PayPal<small>Your own account</small></li>
                <li>Payfast<small>South African rand</small></li>
                <li>Razorpay<small>Indian rupees</small></li>
                <li>Invoice Ninja<small>Invoices for orders</small></li>
                <li>Payment link<small>Any link of yours</small></li>
                <li class="is-wide">Cash at the door<small>Mark it paid, and your records still match</small></li>
            </ul>
        </div>
    </section>

    {{-- 04: switching. --}}
    <section class="hn-ch">
        <div class="hn-wrap hn-ch-grid">
            <div>
                <p class="hn-no">04 <i>/ 05</i></p>
                <h2 class="hn-h2">Nothing to type twice.</h2>
                <p class="hn-sub">Your events already live somewhere. Bring them the way they are: from a calendar, a page or the poster you already made.</p>
            </div>
            <ul class="hn-paths">
                <li><i aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M4 10h16M9 3v4m6-4v4"/></svg></i><div><b>Sync a calendar</b><span>{{ outlook_sync_enabled() ? 'Google Calendar, Microsoft 365 or CalDAV' : 'Google Calendar or CalDAV' }}, both ways. Free.</span></div></li>
                <li><i aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 14a4 4 0 0 0 5.66 0l3-3a4 4 0 0 0-5.66-5.66l-1 1"/><path d="M14 10a4 4 0 0 0-5.66 0l-3 3a4 4 0 0 0 5.66 5.66l1-1"/></svg></i><div><b>Paste a link</b><span>An events page or a calendar feed. Free.</span></div></li>
                <li><i aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h3"/></svg></i><div><b>Drop a poster</b><span>A flyer or a screenshot. AI reads it into an event, 50 a day free.</span></div></li>
                <li><i aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h12m-4-4 4 4-4 4M20 4v16"/></svg></i><div><b>Leaving Eventbrite?</b><span>Bring your events over with the built-in import, on Pro.</span></div></li>
            </ul>
        </div>
    </section>

    {{-- 05: lock-in. --}}
    <section class="hn-ch">
        <div class="hn-wrap hn-ch-grid">
            <div>
                <p class="hn-no">05 <i>/ 05</i></p>
                <h2 class="hn-h2">Free to leave, so you'll stay.</h2>
                <p class="hn-sub">Your audience and your events are yours. So is the code.</p>
            </div>
            <dl class="hn-keep">
                <div><dt>Open source</dt><dd>Every line is public. <a href="{{ marketing_url('/open-source') }}">Read about the code</a></dd></div>
                <div><dt>Back it up</dt><dd>Export a schedule with its events and images on any plan, and restore it on another install.</dd></div>
                <div><dt>Selfhost it</dt><dd>Run it on your own server, on your own domain, with every paid feature included. <a href="{{ marketing_url('/selfhost') }}">Install it</a></dd></div>
            </dl>
        </div>
    </section>

    {{-- What the one link does: a complete 3 x 2 grid. --}}
    <section class="hn-ch hn-alt">
        <div class="hn-wrap">
            <p class="hn-no">Your link</p>
            <h2 class="hn-h2">One link. Everything people need to come.</h2>
            <p class="hn-sub">Put it in your bio, print its QR code on a poster, or embed it on your own site.</p>
            <ul class="hn-bento">
                @foreach ([
                    ['#4E81FA', 'Every date, one page', 'Recurring nights, one-offs and sub-schedules, on a page that looks like you.', 'M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM3 10h18M8 3v4m8-4v4'],
                    ['#0EA5E9', 'Tickets and the door', 'Ticket types, QR codes and a scanner that opens in any phone\'s browser.', 'M4 8a2 2 0 0 0 2-2h12a2 2 0 0 0 2 2v8a2 2 0 0 0-2 2H6a2 2 0 0 0-2-2zM10 6v12'],
                    ['#0891B2', 'Bookings', 'Your open hours on the same page. Guests pick a time in their own timezone.', 'M12 4a8 8 0 1 0 0 16 8 8 0 0 0 0-16zM12 8v4l3 2'],
                    ['#16A34A', 'Followers and newsletters', 'Followers hear about new dates by email, and you can write to them yourself.', 'M4 6h16v12H4zM4 7l8 6 8-6'],
                    ['#F59E0B', 'Calendars, both ways', 'Guests subscribe to your schedule, and a moved date moves in their calendar too.', 'M4 12a8 8 0 0 1 14-5.3M20 12a8 8 0 0 1-14 5.3M18 3v4h-4M6 21v-4h4'],
                    ['#EF4444', 'Analytics built in', 'Views, sources, check-ins and revenue per event, with no Google Analytics account needed.', 'M4 20V10m6 10V4m6 16v-7m4 7H3'],
                ] as [$hnColor, $hnTitle, $hnText, $hnPath])
                    <li class="hn-card-box" style="--c: {{ $hnColor }}">
                        <span class="hn-ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $hnPath }}"/></svg></span>
                        <h3>{{ $hnTitle }}</h3>
                        <p>{{ $hnText }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- The first hour, made small. --}}
    <section class="hn-ch">
        <div class="hn-wrap">
            <p class="hn-no">Switching</p>
            <h2 class="hn-h2">Up and selling this afternoon.</h2>
            <ol class="hn-steps">
                <li class="hn-card-box"><h3>Claim your link</h3><p>Pick a name, sign up and save your schedule.</p><small>Under two minutes</small></li>
                <li class="hn-card-box"><h3>Bring your events</h3><p>Sync a calendar, paste a link or drop a poster. Add tickets where you need them.</p><small>Free registration on every plan</small></li>
                <li class="hn-card-box"><h3>Share it</h3><p>Your bio, a QR code on the poster, an embed on your site. Followers get the new dates by email.</p><small>One link for all of it</small></li>
            </ol>
        </div>
    </section>

    {{-- What the promises do not already answer. --}}
    <section class="hn-ch">
        <div class="hn-wrap hn-faq">
            <div>
                <p class="hn-no">Questions</p>
                <h2 class="hn-h2">Asked often.</h2>
                <p class="hn-sub">More in the <a href="{{ marketing_url('/faq') }}">full FAQ</a>.</p>
            </div>
            <div>
                @foreach ($hnFaqs as $hnFaqIndex => $hnFaq)
                    <details name="hn-faq" @if ($hnFaqIndex === 0) open @endif>
                        <summary>{{ $hnFaq['q'] }}</summary>
                        <p>{{ $hnFaq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- The payoff, personal: the name box again, and the visitor's poster. --}}
    <section id="hn-claim" class="hn-fin">
        <div class="hn-wrap hn-fgrid">
            <div>
                <p class="hn-eyebrow">Your turn</p>
                <h2 class="hn-h2 hn-fin-h">Your next night, <span class="hp-ink-grad">on sale tonight.</span></h2>
                <div class="hn-claimwrap">
                    <label for="hn-claim-end" class="hp-claim-label">Pick your link</label>
                    <div class="hp-claimrow">
                        <div dir="ltr" class="es-claim hp-claim">
                            <input id="hn-claim-end" type="text" placeholder="your-name" autocomplete="off" autocapitalize="none" spellcheck="false" maxlength="30">
                            <span class="hp-claim-suffix">{{ $claimSuffix }}</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary">Claim it free {!! $hpArrow !!}</a>
                    </div>
                    <p class="hn-under">No credit card. Zero platform fees. Open source.</p>
                </div>
            </div>
            <div class="hn-fposter" data-claim-poster data-claim-for="hn-claim-end" aria-hidden="true">
                <small><span>On the wall</span><span>Tonight</span></small>
                <strong data-claim-name="title">Your name</strong>
                <span data-claim-name="url">your-name{{ $claimSuffix }}</span>
                <em>Get tickets</em>
            </div>
        </div>
    </section>

</div>

{{-- The night, in numbers: the two sliders multiplied, Stripe's published rate taken off, and
     Pro's month. Money is printed the way the platform prints a whole amount (data-money is how it
     prints 7), with cents where there are any. --}}
<script {!! nonce_attr() !!}>
    (function () {
        var calc = document.querySelector('[data-hn-calc]');
        if (!calc) {
            return;
        }
        var q = calc.querySelector('[data-hn-q]');
        var p = calc.querySelector('[data-hn-p]');
        var pattern = calc.getAttribute('data-money') || '$7';
        var pro = parseFloat(calc.getAttribute('data-pro')) || 0;
        var money = function (value, cents) {
            var text = value.toLocaleString('en-US', { minimumFractionDigits: cents ? 2 : 0, maximumFractionDigits: cents ? 2 : 0 });
            return pattern.replace('7', text);
        };
        var set = function (name, text) {
            var el = calc.querySelector('[data-hn-' + name + ']');
            if (el) {
                el.textContent = text;
            }
        };
        var run = function () {
            var count = parseInt(q.value, 10) || 0;
            var price = parseInt(p.value, 10) || 0;
            var gross = count * price;
            var stripe = count * (price * 0.029 + 0.30);
            set('q-out', String(count));
            set('p-out', money(price, false));
            set('gross', money(gross, true));
            set('stripe', '−' + money(stripe, true));
            set('keep', money(Math.max(0, gross - stripe - pro), true));
            set('five', money(gross * 0.05, true));
            [q, p].forEach(function (range) {
                range.style.setProperty('--p', ((range.value - range.min) / (range.max - range.min) * 100) + '%');
            });
        };
        q.addEventListener('input', run);
        p.addEventListener('input', run);
        run();
    })();
</script>
