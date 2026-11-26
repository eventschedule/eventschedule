<x-marketing-layout :hp="true">
    <x-slot name="title">Vendor Map for Markets, Fairs and Expos</x-slot>
    <x-slot name="description">A vendor map of your market, fair or expo: draw it or let AI draft it from photos, send vendors one link to claim their stall, and give shoppers a QR map.</x-slot>
    <x-slot name="breadcrumbTitle">Vendor Map</x-slot>

    {{-- Claims on this page, checked against the code and docs/FEATURES.md ("Vendor Map"):
         - One map per venue or curator schedule (VendorMapController::roleFor() refuses the other
           type), for one event the schedule runs. Kinds: VendorMapKinds::KINDS.
         - Free: drawing by hand (the designer), the visitors' map page /{schedule}/map, the A4
           entrance sign with its QR and a 300 dpi PNG, the "this week" band on the schedule and
           event pages, adding vendors by hand, moves, "not coming" and cancelling a day.
         - Pro, gated on the MAP's schedule: the vendor link (VendorMap::takesClaims()) and the AI
           draft (VendorMapAi::available(), Gemini, up to 20 photos and one PDF, counted against
           the daily AI allowance). When Pro lapses approved vendors stay, "Not coming" keeps
           working, and the link stops taking new requests.
         - Vendors sign in with an emailed code or Google (VendorSpotController); nobody is asked
           for a password. A vendor added by hand needs no account.
         - Cancel this day: an excluded date on a recurring event, a cancelled event otherwise; the
           map then says the market is closed that day, with the reason.
         - The embed is an iframe of the map page (VendorMapAdmin.vue embedCode).
         No price is quoted here, so nothing needs plan_price(). --}}

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule - Vendor Map"
        description="A map of a farmers market, craft fair, flea market, food festival or expo. The organizer draws it or has AI draft it from photos, vendors claim their own spot from one link, and shoppers open the map from a QR code at the entrance."
        audience="Market managers, fair organizers and expo organizers"
        keywords="vendor map, market map, stall map, farmers market map, craft fair layout, expo floor plan, booth map, vendor management" />
    </x-slot>

    @php
        $vmShot = fn (string $name) => asset('images/marketing/vendor-map/'.$name.'.webp');

        $vmFaqs = [
            ['q' => 'What is a vendor map?', 'a' => 'A plan of your market, fair or expo with every stall, table or booth on it, and who is standing at each one on a given day. You keep it on the Vendor map tab of your schedule, your vendors keep their own spot up to date from one link, and shoppers open it on their phones to find the honey stall or the coffee.'],
            ['q' => 'Which kinds of event is it for?', 'a' => 'Five, and each one is worded for itself: a farmers market (stalls), a craft fair (tables), a flea market (stalls), a food festival (stands) and a conference or expo (booths). The map lives on a venue or curator schedule and serves one of its events, usually the weekly market day or the dates of the fair.'],
            ['q' => 'Do my vendors need an account?', 'a' => 'A vendor who claims a spot from the vendor link signs in with a code sent to their email or with Google, so only they can change their place later. Nobody sets a password. A vendor you add yourself, for the season or for one day, needs no account at all.'],
            ['q' => 'How does the AI draw the map?', 'a' => 'You take a few photos of the market, or upload the map you already print, and it reads the spots, the areas and the names on the signs into a first draft. It takes about a minute. A photo from above or a printed map gives a near-final map; photos from the aisles give a rough layout and the vendors\' names, which you then check. It is part of Pro, and each photo counts against the daily AI allowance.'],
            ['q' => 'Can I draw the map myself instead?', 'a' => 'Yes, on every plan. Add a row of spots in one go and number them in walking order, place single spots anywhere (along a path, around a square), group them into areas, and add the entrance, restrooms, parking, a stage and where the "You are here" sign hangs. It works on a computer and on a phone.'],
            ['q' => 'What happens when a market day is rained off?', 'a' => 'Press Cancel this day on the Vendor map tab and choose why: weather, a holiday or something else. The map tells shoppers there is no market that day and why, and the date of a weekly market comes off the calendar too. A fair or an expo is called off on the map alone, so its own event is yours to cancel or keep. Open this day again puts it back.'],
            ['q' => 'What if a vendor cannot come one week?', 'a' => 'They tap Not coming on their own page, from the same link they joined with or from the email that confirmed their spot, and you are told. On the map their spot reads "Not here today" for that date only. You can mark it for them too.'],
            ['q' => 'What if two vendors ask for the same spot?', 'a' => 'Both requests wait for you, and each says that two vendors want the spot. Approve one and move the other somewhere free. Approve all without conflicts approves every request nobody else is asking for, in one press.'],
            ['q' => 'Do shoppers need an app?', 'a' => 'No. They scan the QR code on the entrance sign with their phone camera, or open the link, and see who is here today. They can search by a vendor\'s name or by what they sell, and filter by category.'],
            ['q' => 'Can I put the map on my own website?', 'a' => 'Yes. The Vendor map tab gives you a short snippet to paste where the map should appear, and it shows the same live map as the QR code.'],
            ['q' => 'Is this the map of venues on a schedule page?', 'a' => 'No. That map places venues on a street map. The vendor map is a plan of one market, fair or expo, drawn by you, with the vendors on it.'],
            ['q' => 'Which plan do I need?', 'a' => 'The map itself is free: drawing it, adding vendors by hand, moves, Not coming, cancelling a day, the shoppers\' map, the QR sign and the vendors on your schedule page. The vendor link and drafting the map from photos are part of Pro. If Pro ends, the vendors you approved stay and Not coming keeps working; the link stops taking new requests. A selfhosted install has all of it, with drafting from photos once a Gemini API key is set.'],
        ];

        $vmKinds = [
            ['Farmers market', 'Stall 12', 'Find your stall', '/for-farmers-markets', 'For farmers markets'],
            ['Craft fair', 'Table 12', 'Find your table', '/for-craft-fairs', 'For craft fairs'],
            ['Flea market', 'Stall 12', 'Find your stall', '/for-flea-markets', 'For flea markets'],
            ['Food festival', 'Stand 12', 'Find your stand', '/for-festivals', 'For festivals'],
            ['Conference or expo', 'Booth 12', 'Find your booth', '/for-conferences-and-expos', 'For conferences and expos'],
        ];
    @endphp

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present, so no-JS
         visitors, crawlers and reduced-motion users always see the whole page. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           Vendor map: "The plan at the gate".

           CONCEPT: the page is the product's own screens. A market map
           is a picture of a real place, so every picture here is a real
           screen of the app on a made-up market, never a drawing of one.
           The page is written on the house style kit (partials/hp-kit)
           and adds only the frames the screens sit in and the plan pills.

           DELIBERATELY NOT: /features/allocated-seating owns seats sold
           off a plan; /for-farmers-markets owns the season and the rain
           call. This page is the map itself.
           ============================================================== */

        .es-vm-split { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; align-items: center; }
        .es-vm-split.is-wide { gap: 2rem; align-items: start; }
        @media (min-width: 1024px) {
            .es-vm-split.is-even { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .es-vm-split.is-wide { grid-template-columns: minmax(0, 2.2fr) minmax(0, 1fr); }
        }

        .es-vm-frame {
            overflow: hidden;
            border: 1px solid var(--hp-line);
            border-radius: 1.1rem;
            background: var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
        }
        .es-vm-frame img { display: block; width: 100%; height: auto; }
        .es-vm-frame img.is-night { display: none; }
        .dark .es-vm-frame img.is-night { display: block; }
        .dark .es-vm-frame img.is-day { display: none; }
        .es-vm-phone {
            overflow: hidden;
            border: 1px solid var(--hp-line-2);
            border-radius: 2rem;
            background: #0b1220;
            box-shadow: var(--hp-card-shadow);
            max-width: 19rem;
            margin-inline: auto;
        }
        .es-vm-phone img { display: block; width: 100%; height: auto; }
        .es-vm-cap { margin-top: 0.75rem; font-size: 0.85rem; color: var(--hp-ink-3); text-align: center; }

        .es-vm-pill {
            display: inline-flex;
            align-items: center;
            min-height: 1.5rem;
            padding: 0 0.6rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .es-vm-pill.is-free { background: rgba(16, 185, 129, 0.12); color: #047857; }
        .dark .es-vm-pill.is-free { background: rgba(16, 185, 129, 0.16); color: #6ee7b7; }
        .es-vm-pill.is-pro { background: rgba(78, 129, 250, 0.12); color: #1d4ed8; }
        .dark .es-vm-pill.is-pro { background: rgba(78, 129, 250, 0.18); color: #a5c0ff; }

        .es-vm-tips { display: grid; gap: 1rem; }
        @media (min-width: 640px) { .es-vm-tips { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .es-vm-tip { display: flex; flex-direction: column; padding: 1.25rem; }
        .es-vm-tip b { display: block; font-family: var(--hp-mono); font-size: 0.75rem; letter-spacing: 0.08em; color: var(--hp-blue); }
        .es-vm-tip h3 { margin-top: 0.4rem; font-weight: 700; color: var(--hp-ink); }
        .es-vm-tip p { margin-top: 0.35rem; font-size: 0.9rem; line-height: 1.55; color: var(--hp-ink-2); }

        .es-vm-kinds { display: grid; gap: 1rem; }
        @media (min-width: 640px) { .es-vm-kinds { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .es-vm-kinds { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .es-vm-kind { display: flex; flex-direction: column; padding: 1.4rem; }
        .es-vm-kind h3 { font-weight: 800; font-size: 1.1rem; color: var(--hp-ink); }
        .es-vm-kind dl { margin-top: 0.75rem; display: grid; gap: 0.3rem; font-size: 0.9rem; }
        .es-vm-kind dl div { display: flex; justify-content: space-between; gap: 1rem; }
        .es-vm-kind dt { color: var(--hp-ink-3); }
        .es-vm-kind dd { color: var(--hp-ink); font-weight: 600; }
        .es-vm-kind a { margin-top: auto; padding-top: 1rem; }

        .es-vm-plans { display: grid; gap: 1rem; }
        @media (min-width: 768px) { .es-vm-plans { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .es-vm-plan { display: flex; flex-direction: column; padding: 1.6rem; }
        .es-vm-plan ul { margin-top: 1rem; display: grid; gap: 0.6rem; }
        .es-vm-plan li { display: flex; gap: 0.6rem; align-items: flex-start; color: var(--hp-ink-2); font-size: 0.95rem; }
        .es-vm-plan li .hp-ok { margin-top: 0.1rem; }
        .es-vm-plan .es-vm-plan-foot { margin-top: auto; padding-top: 1.25rem; font-size: 0.85rem; color: var(--hp-ink-3); }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero                                                      -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero hp-hero">
        <div class="hp-hero-sky" aria-hidden="true"></div>

        <div class="hp-hero-copy">
            <h1 class="hp-h1">
                <x-marketing.hero-eyebrow class="es-fade-up es-d-1 hp-eyebrow">
                    <span class="hp-live" aria-hidden="true"><i></i></span>
                    Vendor map for markets, fairs and expos
                </x-marketing.hero-eyebrow>
                <span class="es-mask"><span class="es-mask-line">Every stall on one map,</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="hp-ink-grad">kept by the people at them</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 hp-sub">
                Draw your market once, or let AI draft it from a few photos. Send your vendors one link and each finds their own spot and says what they sell. Shoppers scan a code at the entrance and see who is here today.
            </p>

            <div class="es-fade-up es-d-3 hp-hero-actions">
                <a href="{{ marketing_url('/docs/vendor-maps') }}" class="hp-btn hp-btn-ghost is-still">
                    Read the guide
                </a>
                <a href="{{ app_url('/sign_up?type=venue&vendor_map=market') }}" class="hp-btn hp-btn-primary">
                    Set up your map
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>

            <nav class="es-fade-up es-d-4 hp-toc" aria-label="On this page">
                @foreach ([['screens', 'The map'], ['photos', 'From photos'], ['link', 'The vendor link'], ['day', 'Market day'], ['shoppers', 'Shoppers'], ['kinds', 'Kinds'], ['plans', 'Plans']] as $tocIndex => [$tocId, $tocLabel])
                    <a href="#{{ $tocId }}"><b>{{ sprintf('%02d', $tocIndex + 1) }}</b>{{ $tocLabel }}</a>
                @endforeach
            </nav>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The screens: the organizer's tab and the shopper's phone  -->
    <!-- ============================================================ -->
    <section id="screens" class="hp-sec hp-alt">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>The map</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">One plan, <span class="hp-ink-grad">two sides of the gate.</span></h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                    You run the market from the Vendor map tab of your schedule: who is coming on which day, who is waiting for an answer, and which spots are empty. Shoppers get the same map on their phone, with the names on it.
                </p>
            </div>

            <div class="es-vm-split is-wide mt-12">
                <figure data-reveal="panel">
                    <div class="es-vm-frame">
                        <img src="{{ $vmShot('tab-desktop-light') }}" width="1440" height="1400" loading="lazy" decoding="async" class="is-day"
                             alt="The Vendor map tab of a farmers market: a card for vendors with the link and a Send on WhatsApp button, a card for shoppers, the market days across the top, the map with two spots waiting, and two requests for the same spot">
                        <img src="{{ $vmShot('tab-desktop-dark') }}" width="1440" height="1400" loading="lazy" decoding="async" class="is-night"
                             alt="The Vendor map tab of a farmers market in dark mode: the vendor link, the shoppers card, the market days, the map and two requests waiting">
                    </div>
                    <figcaption class="es-vm-cap">The organizer's side: the Vendor map tab, on a market day with three requests waiting.</figcaption>
                </figure>
                <figure data-reveal="panel" style="--reveal-delay: 0.1s;">
                    <div class="es-vm-phone">
                        <img src="{{ $vmShot('map-phone') }}" width="780" height="1688" loading="lazy" decoding="async"
                             alt="The shoppers' map on a phone: the areas of the market and their numbered spots, with a search box for a name or what they sell and the list of who is here that day">
                    </div>
                    <figcaption class="es-vm-cap">The shopper's side, from the QR code at the entrance.</figcaption>
                </figure>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. From photos                                               -->
    <!-- ============================================================ -->
    <section id="photos" class="hp-sec">
        <div class="hp-wrap">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                <div class="min-w-0">
                    <div class="hp-head">
                        <span class="hp-kicker" data-reveal>From photos</span>
                        <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">Walk the market with your phone. <span class="hp-ink-grad">Get a map back.</span></h2>
                        <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                            Take a few photos, or upload the map you already print, and AI drafts the plan: the spots, the areas they sit in and the names on the vendors' signs. It takes about a minute, and you can leave the page while it works.
                        </p>
                    </div>
                    <p class="mt-6 text-sm text-gray-600 dark:text-gray-400" data-reveal>
                        A photo from above, a PDF or the map you print gives a near-final map. Photos from the aisles give a rough layout and the names. Either way you check the names the AI was least sure of first, then save.
                    </p>
                    <p class="mt-6 flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-400" data-reveal>
                        <span class="es-vm-pill is-pro">Pro</span>
                        <span>Drawing the map by hand is free on every plan.</span>
                    </p>
                </div>

                <figure data-reveal="panel">
                    <div class="es-vm-frame">
                        <img src="{{ $vmShot('designer-desktop-light') }}" width="1440" height="900" loading="lazy" decoding="async" class="is-day"
                             alt="The map designer: rows of numbered spots in a hall, spots placed one by one in a second area, food trucks, a crafts corner, the entrance and parking, with Add a spot, Add a row, Number in order and Add a landmark across the top">
                        <img src="{{ $vmShot('designer-desktop-dark') }}" width="1440" height="900" loading="lazy" decoding="async" class="is-night"
                             alt="The map designer in dark mode, with its areas, rows and landmarks">
                    </div>
                    <figcaption class="es-vm-cap">The designer. Rows where the stalls stand in rows, single spots where they do not, and the landmarks shoppers look for.</figcaption>
                </figure>
            </div>

            <div class="es-vm-tips mt-12" data-reveal-group="80">
                @foreach ([
                    ['01', 'Stand somewhere high', 'A step, a balcony, the top of the car park. The more of the ground a photo sees, the truer the layout.'],
                    ['02', 'A whole row in each photo', 'Spots are counted along a row, so a row cut in two is a row read twice.'],
                    ['03', 'Signs readable', 'The names come off the vendors\' own signs. A sign you can read in the photo is a name you do not type.'],
                ] as [$tipNo, $tipTitle, $tipBody])
                    <div class="hp-card es-vm-tip" data-reveal>
                        <b>{{ $tipNo }}</b>
                        <h3>{{ $tipTitle }}</h3>
                        <p>{{ $tipBody }}</p>
                    </div>
                @endforeach
            </div>
            <ul class="hp-checks mt-12 grid gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    'Add a row of spots in one go, numbered from the left or the right, from any first number.',
                    'Place a single spot anywhere, turn it and size it: a field, a car park or a town square is rarely a grid.',
                    'Number in order: tap the spots in the order people walk past them.',
                    'Areas with their own colour: the covered hall, the food trucks, the crafts corner.',
                    'Landmarks: the entrance, restrooms, parking, a stage, any label, and where the "You are here" sign hangs.',
                    'Undo every step, and the same tools on a phone, where the panel becomes a sheet.',
                ] as $drawCheck)
                    <li data-reveal>
                        <span class="hp-ok" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></span>
                        <span>{{ $drawCheck }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The vendor link                                           -->
    <!-- ============================================================ -->
    <section id="link" class="hp-sec hp-alt">
        <div class="hp-wrap">
            <div class="es-vm-split is-even">
                <div class="grid grid-cols-2 gap-4" data-reveal="panel">
                    <figure>
                        <div class="es-vm-phone">
                            <img src="{{ $vmShot('claim-phone') }}" width="780" height="1688" loading="lazy" decoding="async"
                                 alt="The vendor link on a phone: Is one of these you? Find your stall, with a list of stalls by number, the names already on them, and the free ones">
                        </div>
                    </figure>
                    <figure class="mt-10">
                        <div class="es-vm-phone">
                            <img src="{{ $vmShot('claim-map-phone') }}" width="780" height="940" loading="lazy" decoding="async"
                                 alt="The same link showing the map, where a vendor taps their own spot">
                        </div>
                    </figure>
                </div>

                <div class="min-w-0">
                    <div class="hp-head">
                        <span class="hp-kicker" data-reveal>The vendor link</span>
                        <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">One link in the vendors' group. <span class="hp-ink-grad">Everyone finds their own spot.</span></h2>
                        <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                            Press Send on WhatsApp and the message is written for you. A vendor taps the link, sees "Is one of these you?" with the names the map already has, and claims their spot in about two minutes.
                        </p>
                    </div>
                    <ul class="hp-checks" data-reveal-group="70">
                        @foreach ([
                            'They find themselves by name or number, on a list or on the map, and say which days they come: every market day, or the dates they choose.',
                            'About them: their name at the market, what they sell in a tap or two, a line of their own, a photo if they like, and a phone number only you see.',
                            'They confirm with a code sent to their email, or with Google. No password, ever.',
                            'You review each request on the map, approve or decline with a note, and tell them on WhatsApp in one tap.',
                            'Approve all without conflicts takes every request nobody else wants; two vendors on one spot are shown side by side.',
                            'The same link is their home afterwards: their days, Not coming for one of them, and Share where to find me.',
                        ] as $linkCheck)
                            <li data-reveal>
                                <span class="hp-ok" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></span>
                                <span>{{ $linkCheck }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-8 flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-400" data-reveal>
                        <span class="es-vm-pill is-pro">Pro</span>
                        <span>Adding vendors yourself is free on every plan.</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Market day                                                -->
    <!-- ============================================================ -->
    <section id="day" class="hp-sec">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>Market day</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">The map knows <span class="hp-ink-grad">which day it is.</span></h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                    Pick a date along the top of the tab and the map shows that day: who is coming, who is not, which spots are empty and what is waiting for you. A change for one day stays on that day.
                </p>
            </div>

            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                @foreach ([
                    ['Move a vendor', 'Tap Move to and then the new spot. Choose This day only, or From now on for the rest of the season. Two vendors trade places when you move one onto the other.'],
                    ['Not coming', 'A vendor marks a day from their own page, or you mark it for them. That spot reads "Not here today" on the shoppers\' map, for that date only, and Coming after all puts it back.'],
                    ['A guest for one day', 'Tap an empty spot and Add a vendor for this day: a name and what they sell, no account needed. They are gone from the map the next week.'],
                    ['Cancel this day', 'Rain, a holiday, anything else. The map tells shoppers there is no market that day, and why, and a weekly market\'s date comes off your calendar. Open this day again if it goes ahead after all.'],
                    ['Told, not chased', 'You get an email when vendors are waiting or cannot come, gathered into one message rather than one per vendor.'],
                    ['Renumbered safely', 'Change the numbers in the designer and vendors see their new number on their own page. A spot that still has a vendor for the season cannot be deleted until you move them.'],
                ] as [$dayTitle, $dayBody])
                    <div class="hp-card flex flex-col p-6" data-reveal>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $dayTitle }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-300">{{ $dayBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Shoppers                                                  -->
    <!-- ============================================================ -->
    <section id="shoppers" class="hp-sec hp-alt">
        <div class="hp-wrap">
            <div class="es-vm-split is-even">
                <div class="min-w-0">
                    <div class="hp-head">
                        <span class="hp-kicker" data-reveal>Shoppers</span>
                        <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">"Is the cheese man here today?" <span class="hp-ink-grad">Answered at the gate.</span></h2>
                        <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                            Print the entrance sign and hang it where people come in. One scan opens the map with "You are here" where the sign hangs, and nothing to install.
                        </p>
                    </div>
                    <ul class="hp-checks" data-reveal-group="70">
                        @foreach ([
                            'Search by a vendor\'s name or by what they sell, or tap a category: bread, honey, flowers, hot food.',
                            'The list follows the date: here today, not here today, and the next date the market is on.',
                            'A vendor with a page of their own is one tap away: Visit their page.',
                            'The sign prints on A4, and downloads as a 300 dpi image for the print shop.',
                            'Paste the map into your own website, and show who is at the market this week on your schedule and event pages.',
                        ] as $shopCheck)
                            <li data-reveal>
                                <span class="hp-ok" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></span>
                                <span>{{ $shopCheck }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-8 flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-400" data-reveal>
                        <span class="es-vm-pill is-free">Free</span>
                        <span>Everything shoppers see is on every plan.</span>
                    </p>
                </div>

                <figure data-reveal="panel">
                    <div class="es-vm-frame mx-auto max-w-md">
                        <img src="{{ $vmShot('sign') }}" width="900" height="1270" loading="lazy" decoding="async"
                             alt="The printable entrance sign: the market's name, What's at the market today?, the whole map with You are here by the entrance, and a large QR code to scan with a phone camera">
                    </div>
                    <figcaption class="es-vm-cap">The entrance sign, ready to print.</figcaption>
                </figure>
            </div>

            <figure class="mt-12" data-reveal="panel">
                <div class="es-vm-frame">
                    <img src="{{ $vmShot('map-desktop') }}" width="1440" height="900" loading="lazy" decoding="async"
                         alt="The shoppers' map on a computer: a search box, category buttons and the list of who is here on the left, the map of the market with each spot's number and name on the right">
                </div>
                <figcaption class="es-vm-cap">The same map on a computer, and in the snippet for your website.</figcaption>
            </figure>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Kinds                                                     -->
    <!-- ============================================================ -->
    <section id="kinds" class="hp-sec">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>Five kinds</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">A stall, a table, a stand <span class="hp-ink-grad">or a booth.</span></h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                    Tell it what you run and every screen speaks your language, in the vendors' link, the shoppers' map and the sign at the gate.
                </p>
            </div>

            <div class="es-vm-kinds mt-12" data-reveal-group="70">
                @foreach ($vmKinds as [$kindName, $kindSpot, $kindFind, $kindPage, $kindLink])
                    <div class="hp-card es-vm-kind" data-reveal>
                        <h3>{{ $kindName }}</h3>
                        <dl>
                            <div><dt>A spot is</dt><dd>{{ $kindSpot }}</dd></div>
                            <div><dt>The vendor link says</dt><dd>{{ $kindFind }}</dd></div>
                        </dl>
                        <a href="{{ marketing_url($kindPage) }}" class="hp-more">
                            {{ $kindLink }}
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                @endforeach
                <div class="hp-card es-vm-kind" data-reveal>
                    <h3>On a venue or curator schedule</h3>
                    <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-300">The map belongs to the schedule that runs the market: a venue that owns the ground, or a curator that organizes it somewhere else. It serves one of that schedule's events, often the weekly market day.</p>
                    <a href="{{ marketing_url('/docs/vendor-maps') }}#turning-it-on" class="hp-more">
                        Turning it on
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Plans                                                     -->
    <!-- ============================================================ -->
    <section id="plans" class="hp-sec hp-alt">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>Which plan</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">The map is free. <span class="hp-ink-grad">The shortcuts are Pro.</span></h2>
            </div>

            <div class="es-vm-plans mt-12" data-reveal-group="90">
                <div class="hp-card es-vm-plan" data-reveal>
                    <span class="es-vm-pill is-free self-start">Free</span>
                    <ul>
                        @foreach (['Drawing the map: rows, spots, areas and landmarks', 'Adding vendors yourself, for the season or one day', 'Moves, Not coming, and cancelling a day', 'The shoppers\' map, the QR entrance sign and the embed', 'Who is here this week, on your schedule and event pages'] as $freeLine)
                            <li><span class="hp-ok" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></span><span>{{ $freeLine }}</span></li>
                        @endforeach
                    </ul>
                    <p class="es-vm-plan-foot">On every plan, with no limit on vendors or market days.</p>
                </div>
                <div class="hp-card es-vm-plan" data-reveal>
                    <span class="es-vm-pill is-pro self-start">Pro</span>
                    <ul>
                        @foreach (['The vendor link: vendors claim their own spot and keep it up to date', 'Reviewing requests on the map, and Approve all without conflicts', 'A first draft of the map by AI, from photos or your printed map'] as $proLine)
                            <li><span class="hp-ok" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></span><span>{{ $proLine }}</span></li>
                        @endforeach
                    </ul>
                    <p class="es-vm-plan-foot">If Pro ends, approved vendors stay and Not coming keeps working; the link stops taking new requests. A selfhosted install has all of it.</p>
                </div>
            </div>
            <div class="mt-8 flex flex-wrap justify-center gap-3" data-reveal>
                <a href="{{ route('marketing.pricing') }}" class="hp-btn hp-btn-ghost is-still">See pricing</a>
                <a href="{{ app_url('/sign_up?type=venue&vendor_map=market') }}" class="hp-btn hp-btn-primary">
                    Set up your map
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Questions, the strip and the finale                       -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$vmFaqs" />
    <x-marketing.hp-faq :items="$vmFaqs">Vendor map <span class="hp-ink-grad">questions</span></x-marketing.hp-faq>

    <x-marketing.related-pages />

    <x-marketing.hp-finale lead="Draw it once, send one link, and hang the sign at the gate. The map is free on every plan." placeholder="your-market">
        Put your market <span class="hp-ink-grad">on the map</span>
    </x-marketing.hp-finale>

    {{-- Load-bearing, not decoration: marketing.css hides every [data-reveal] element behind
         html.es-anim, so a page that sets that class and never loads the reveal observer renders
         completely blank below the nav. --}}
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
