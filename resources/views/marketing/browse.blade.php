<x-marketing-layout>
    {{-- SEO Slots --}}
    <x-slot name="title">Upcoming Events: Music, Comedy, Classes | Event Schedule</x-slot>
    <x-slot name="description">Upcoming live music, comedy, classes, markets and meetups, soonest first. Free to browse, no account needed. Search by event, city or schedule.</x-slot>
    <x-slot name="breadcrumbTitle">Browse</x-slot>

    <x-slot name="headMeta">
        @if (font_stylesheet_url('Red Hat Display'))
            <link rel="stylesheet" href="{{ font_stylesheet_url('Red Hat Display') }}">
        @endif
    </x-slot>

    {{-- Structured data: list only the publicly visible events --}}
    <x-slot name="structuredData">
    @php
        $itemListElements = [];
        $pos = 1;
        foreach ($events as $e) {
            $u = $e->getGuestUrl();
            if (! $u) {
                continue;
            }
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $pos++,
                'url' => $u,
                'name' => $e->name,
            ];
        }
    @endphp
    @if(count($itemListElements))
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {{-- SeoUtils::jsonLd escapes < and >, which is load-bearing here: this block is echoed
         raw, so a closing script tag inside an event name would otherwise terminate the
         element and let the rest of the name run as markup. --}}
    {!! \App\Utils\SeoUtils::jsonLd([
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'Upcoming events on Event Schedule',
        'url' => url('/browse'),
        'itemListElement' => $itemListElements,
    ]) !!}
    </script>
    @endif
    </x-slot>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    @include('marketing.partials.browse-styles')

    @php
        $tiles = $wall['tiles'];
        $rows = $wall['rows'];
        $networkCount = $federatedEvents->count();
        $hasNetworkSection = $networkCount > 0 || $federatedCountry || $federatedLanguage || $federatedInstance;
        $schedules = $wall['count'];

        // Questions a visitor actually asks, then the one an organizer asks.
        $faqs = [
            [
                'q' => 'Do I need an account to browse?',
                'a' => 'No. Everything here is free to read without signing in. Adding an event to your calendar, subscribing to a schedule\'s calendar feed and, where the organizer offers it, asking to be told when tickets go on sale need no account either. You only need one to follow a schedule or publish events of your own.',
            ],
            [
                'q' => 'What if an event\'s tickets are not on sale yet?',
                'a' => 'Open the event and, where the organizer offers it, use Tell me when tickets go on sale, on the page or in its Add to Calendar menu. You leave an email address and nothing else, and you hear when tickets go on sale, if it is cancelled and shortly before it starts, and you get any notice the organizer sends if the date or venue changes. It covers that one date, not the whole schedule, and every email has a one-click unsubscribe.',
            ],
            [
                'q' => 'Why is there only one event from each schedule?',
                'a' => 'So that the page shows who is putting things on rather than who posts the most. Each schedule is here once, with the next thing it has on. Its name under the poster opens the schedule, where the rest are.',
            ],
            [
                'q' => 'Why are some listings from other websites?',
                'a' => 'Event Schedule is open source, so other people run their own copies of it and some choose to share their events here. Those posters open on the site that published them.',
            ],
            [
                'q' => 'Can I browse by city?',
                'a' => 'Not with a filter, but search will do it. Type in a city and you get the schedules based there.',
            ],
            [
                'q' => 'How does an event end up on this page?',
                'a' => 'By being public, on a schedule whose owner has confirmed an email address or phone number, and by having a picture. That is either the event\'s own flyer, or the profile photo on a talent or venue schedule, which covers every event on it. Nothing else is required: no application and no fee. Each schedule is shown once, with its next event, and the page holds the 24 that are on soonest.',
            ],
        ];
    @endphp

    <div id="bw">

    {{-- ============================================================ --}}
    {{-- 1. Hero: words only. The posters are the picture.            --}}
    {{-- ============================================================ --}}
    <section id="top" class="bw-hero noise">
        <div class="bw-hero-light" aria-hidden="true"></div>

        <div class="bw-col bw-hero-in">
            <div class="bw-hero-say">
                <p class="bw-eyebrow es-fade-up">
                    @if ($schedules > 0)
                        <span class="bw-pulse" aria-hidden="true"></span>
                    @endif
                    What's on
                    @if ($schedules > 0)
                        <span class="bw-eyebrow-sep" aria-hidden="true"></span>
                        <span>{{ $schedules }} {{ $schedules === 1 ? 'event' : 'events' }}@if ($wall['countries'] > 1) in {{ $wall['countries'] }} countries @endif</span>
                    @endif
                </p>
                {{-- Broken by hand from a tablet up, where the type is sized to its column so the
                     second line always fits; left to balance itself on a phone. --}}
                <h1 class="bw-h1 es-fade-up es-d-1">Upcoming events, from <br>the people <span class="bw-lit">putting them on.</span></h1>
                <p class="bw-lede es-fade-up es-d-2">Live music, comedy, classes, markets and more. Free to browse, no account needed.</p>
            </div>

            <div class="bw-hero-do es-fade-up es-d-3">
                <form action="{{ marketing_url('/search') }}" method="GET" class="bw-search" role="search">
                    <label for="browse-search" class="bw-search-label">Search by event, city or schedule</label>
                    <div class="bw-search-row">
                        <div class="bw-search-box">
                            <svg aria-hidden="true" class="bw-search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input id="browse-search" type="search" name="q" placeholder="Try jazz, or Lisbon" autocomplete="off">
                        </div>
                        <button type="submit" class="bw-btn">{{ __('messages.search') }}</button>
                    </div>
                </form>

                @if (count($wall['bands']) > 1 || $hasNetworkSection)
                    <nav class="bw-jump" aria-label="Jump to">
                        @foreach ($wall['bands'] as $band)
                            <a href="#when-{{ $band['band'] }}">{{ $band['title'] }} <span>{{ $band['count'] }}</span></a>
                        @endforeach
                        @if ($hasNetworkSection)
                            <a href="#network">Other sites @if ($networkCount)<span>{{ $federatedTotal }}</span>@endif</a>
                        @endif
                    </nav>
                @endif
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- 2. The wall                                                  --}}
    {{-- ============================================================ --}}
    <section id="events" class="bw-stage">
        {{-- The colours of the poster under the pointer, thrown across the wall. Filled in by
             the script at the foot of the page; empty, as it is at rest, it is nothing at all. --}}
        <div class="bw-ambient" aria-hidden="true">
            <div class="bw-ambient-in"><img alt=""><img alt=""></div>
        </div>

        {{-- The wall runs the width of the window. With nothing on it, the words that stand in
             for it keep to the column the rest of the page's words are on. --}}
        <div class="{{ $wall['count'] > 3 ? 'bw-bleed' : 'bw-col' }}">
            <h2 class="sr-only">{{ __('messages.upcoming_events') }}</h2>

            @foreach (['message' => 'bw-flash', 'error' => 'bw-flash bw-flash-bad'] as $flashKey => $flashClass)
                @if (session($flashKey))
                    <p class="{{ $flashClass }}" role="status">{{ session($flashKey) }}</p>
                @endif
            @endforeach

            @if (count($tiles))
                @include('marketing.partials.browse-wall', ['tiles' => $tiles, 'rows' => $rows, 'admin' => $browseAdmin])

                <p class="bw-after">
                    Not seeing it?
                    <a href="{{ marketing_url('/search') }}">Search for something specific</a>
                </p>
            @else
                {{-- Nothing on. Say so once, and go straight to the one thing that can be offered. --}}
                <div class="bw-empty">
                    <div>
                        <h3>No upcoming events yet</h3>
                        <p>If you run events, yours could be the first ones here. It is free to publish.</p>
                        <a href="{{ app_url('/sign_up') }}" class="bw-btn">
                            {{ __('messages.create_your_schedule') }}
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                    <div class="bw-empty-sheet" aria-hidden="true">
                        <span>This space is free</span>
                        <b>Your event here</b>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Federated listings from other Event Schedule installs.

         Their own section rather than mixed into the wall above: provenance stays obvious, and
         the local query keeps its single ordering and limit. Not rendered at all when the
         network has nothing to show. --}}
    @if($hasNetworkSection)
        <section id="network" class="bw-stage bw-network">
            <div class="bw-col">
                <div class="bw-head">
                    <div>
                        <h2>{{ __('messages.federation_browse_heading') }}</h2>
                        <p>{{ __('messages.federation_browse_intro') }}</p>
                    </div>

                    {{-- Plain GET filters. In-person events make location filtering essential, and
                         keeping this server-side means the page stays crawlable, shareable, and
                         free of a JS mount. --}}
                    <form method="GET" action="{{ marketing_url('/browse') }}" class="bw-filters">
                        {{-- Carried through, or picking a country would silently drop the
                             instance scope this page was opened with. --}}
                        @if($federatedInstance)
                            <input type="hidden" name="instance" value="{{ $federatedInstance }}">
                        @endif
                        <label for="federated-country" class="sr-only">{{ __('messages.federation_filter_all_countries') }}</label>
                        <select id="federated-country" name="country" data-auto-submit>
                            <option value="">{{ __('messages.federation_filter_all_countries') }}</option>
                            @foreach($federatedCountries as $code)
                                <option value="{{ $code }}" @selected($federatedCountry === $code)>{{ \App\Utils\CountryUtils::getName($code) ?: $code }}</option>
                            @endforeach
                        </select>

                        <label for="federated-language" class="sr-only">{{ __('messages.federation_filter_all_languages') }}</label>
                        <select id="federated-language" name="lang" data-auto-submit>
                            <option value="">{{ __('messages.federation_filter_all_languages') }}</option>
                            @foreach($federatedLanguages as $code)
                                <option value="{{ $code }}" @selected($federatedLanguage === $code)>{{ ucfirst(config('app.supported_languages')[$code] ?? $code) }}</option>
                            @endforeach
                        </select>

                        <noscript>
                            <button type="submit" class="bw-btn bw-btn-sm">{{ __('messages.filter') }}</button>
                        </noscript>
                    </form>
                </div>
            </div>

            <div class="{{ $networkCount > 0 ? 'bw-bleed' : 'bw-col' }}">
                @if($networkCount > 0)
                    @include('marketing.partials.browse-wall', ['tiles' => $network['tiles'], 'rows' => [], 'admin' => $browseAdmin, 'grid' => true])

                    @if($federatedTotal > $networkCount && $federatedLimit < 96)
                        <p class="bw-more">
                            <a href="{{ request()->fullUrlWithQuery(['federated_limit' => $federatedLimit + 24]) }}#network" class="bw-ghost">
                                {{ __('messages.federation_show_more') }}
                            </a>
                        </p>
                    @endif
                @else
                    {{-- Visitor-facing wording: this branch is only ever reached with a filter set. --}}
                    <p class="bw-none">
                        {{ __('messages.federation_browse_no_results') }}
                        @if($federatedCountry || $federatedLanguage || $federatedInstance)
                            <a href="{{ marketing_url('/browse') }}#network">Show every country and language</a>
                        @endif
                    </p>
                @endif
            </div>
        </section>
    @endif

    {{-- Admin-only: hidden events management --}}
    @if($hiddenEvents->count() > 0)
        <section class="bw-stage bw-hidden">
            <div class="bw-col">
                <div class="bw-head">
                    <div>
                        <h2>Hidden events</h2>
                        <p>Only admins see this. These events are hidden from the homepage, Browse, and search.</p>
                    </div>
                    <p class="bw-head-count">{{ $hiddenEvents->count() }} hidden</p>
                </div>
            </div>

            <div class="bw-bleed">
                @include('marketing.partials.browse-wall', ['tiles' => $hidden['tiles'], 'rows' => [], 'admin' => $browseAdmin, 'grid' => true])
            </div>
        </section>
    @endif

    {{-- ============================================================ --}}
    {{-- 3. Questions                                                 --}}
    {{-- ============================================================ --}}
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="bw-faq">
        <div class="bw-col bw-faq-in">
            <div class="bw-faq-say">
                <h2 data-reveal>Common <span class="bw-lit">questions</span></h2>
                <p data-reveal style="--reveal-delay: 0.05s;">
                    <a href="#events">Back to the events</a>
                </p>
            </div>

            <div class="bw-faq-list" data-reveal-group="60">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal>
                        <summary>
                            <h3>{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <div class="bw-keep">
        <x-marketing.related-pages />
    </div>

    {{-- ============================================================ --}}
    {{-- 4. For organizers. Dark in both modes: it is the wall at     --}}
    {{--    night, with one poster on it that is not printed yet.     --}}
    {{-- ============================================================ --}}
    <section id="organizers" class="bw-end">
        <div class="bw-col">
            <div class="bw-end-panel noise" data-reveal="panel">
                <div class="bw-end-say">
                    <p class="bw-end-kicker">For the people who run events</p>
                    <h2>Get your next event <span class="bw-lit">on this page</span></h2>
                    {{-- "with a flyer" is deliberate: the picture is the one check an organizer can
                         fail while doing everything else right. --}}
                    <p class="bw-end-lede">
                        Publish a public event with a flyer, or put a profile photo on your talent or venue schedule, and your next event takes its place here as its date comes up. No application and no fee.
                    </p>

                    <div class="bw-claim-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim bw-claim">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30">
                            <span>.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="bw-btn bw-btn-lit">
                            Start for free
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>

                    <p class="bw-end-note">
                        No credit card required
                        <span aria-hidden="true">&middot;</span>
                        <a href="{{ marketing_url('/features') }}">Features</a>
                        <span aria-hidden="true">&middot;</span>
                        <a href="{{ marketing_url('/pricing') }}">Pricing</a>
                    </p>
                </div>

                {{-- The poster that is not printed yet. It takes the name typed beside it. --}}
                <div class="bw-end-show" aria-hidden="true">
                    <div class="bw-end-halo"></div>
                    <div class="bw-end-sheet">
                        <span class="bw-end-when">Next up</span>
                        <span class="bw-end-name" data-bw-echo data-empty="Your name here">Your name here</span>
                        <span class="bw-end-url"><b data-bw-echo-slug data-empty="your-name">your-name</b>.eventschedule.com</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    </div>

    <script {!! nonce_attr() !!}>
        (function () {
            {{-- Submit a filter on change. The AP layout has a shared data-auto-submit handler,
                 but marketing pages do not load it. --}}
            document.addEventListener('change', function (e) {
                var control = e.target.closest('[data-auto-submit]');
                if (control && control.form) control.form.submit();
            });

            {{-- Count a visit to another site without touching the href, which must stay a
                 direct followable link to the origin. Delegated: CSP blocks inline handlers. --}}
            document.addEventListener('click', function (e) {
                var link = e.target.closest('[data-federated-click]');
                if (!link || !navigator.sendBeacon) return;
                var body = new FormData();
                body.append('_token', '{{ csrf_token() }}');
                navigator.sendBeacon('{{ marketing_url('/browse/federated/') }}' + link.dataset.federatedClick + '/click', body);
            });

            {{-- The wall takes the colours of the poster under the pointer, and gives them back
                 when the pointer leaves. Nothing on a screen that is touched, not pointed at. --}}
            var wall = document.querySelector('#events .bw-wall');
            var lights = document.querySelectorAll('#events .bw-ambient img');
            if (wall && lights.length === 2 && window.matchMedia('(hover: hover)').matches) {
                var lit = 0;
                var shown = '';
                var wait = 0;
                var light = function (poster) {
                    var img = poster && poster.querySelector('.bw-img');
                    var src = img ? (img.currentSrc || img.src) : '';
                    if (src === shown) return;
                    shown = src;
                    if (!src) {
                        lights[0].classList.remove('is-on');
                        lights[1].classList.remove('is-on');
                        return;
                    }
                    var next = lights[lit = 1 - lit];
                    next.src = src;
                    next.classList.add('is-on');
                    lights[1 - lit].classList.remove('is-on');
                };
                {{-- A beat before it changes, so a pointer crossing the wall does not strobe it. --}}
                var soon = function (poster) {
                    clearTimeout(wait);
                    wait = setTimeout(function () { light(poster); }, 140);
                };
                wall.addEventListener('pointerover', function (e) { soon(e.target.closest('.bw-flyer')); });
                wall.addEventListener('pointerleave', function () { soon(null); });
                wall.addEventListener('focusin', function (e) { soon(e.target.closest('.bw-flyer')); });
                wall.addEventListener('focusout', function () { soon(null); });
            }

            {{-- The name typed in the box goes up on the blank poster. --}}
            var box = document.getElementById('es-claim-input');
            var name = document.querySelector('[data-bw-echo]');
            var slug = document.querySelector('[data-bw-echo-slug]');
            if (box && name && slug) {
                box.addEventListener('input', function () {
                    {{-- The shared claim script turns what is typed into an address as it goes,
                         so the name on the poster is read back from the address. --}}
                    var clean = box.value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                    name.textContent = clean ? clean.replace(/-/g, ' ') : name.dataset.empty;
                    slug.textContent = clean || slug.dataset.empty;
                    name.closest('.bw-end-sheet').classList.toggle('is-named', !!clean);
                });
            }
        })();
    </script>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
