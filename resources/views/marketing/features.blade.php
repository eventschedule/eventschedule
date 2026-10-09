<x-marketing-layout :hp="true">
    <x-slot name="title">{{ __('marketing.features_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.features_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">Features</x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule Features"
        description="Every Event Schedule feature: ticketing through Stripe or PayPal with no platform fee, two-way calendar sync, newsletters, AI event import, analytics, and an open-source codebase you can selfhost." />
    </x-slot>

    {{--
        /features, "The board" (2026-10). Under a text-only hero, one console holds the product
        as forty keys in five banks, one bank per chapter. A key is a link to its feature page;
        the script at the foot of this file turns it into a switch: pressed, it lights, one part
        of the scene on the stage appears, the readout says what the feature does and the plan
        it needs, and the tally names the plan the lit keys add up to. The server draws the
        board as the Free plan (every free key lit), which is also what a visitor without
        scripts and a crawler get: forty links, the free ones lit.

        Everything about a feature is said ONCE, in $fbKeys: its bank, the plan it first arrives
        on (docs/FEATURES.md is the reference, FeaturesPageTest holds the two together), its one
        true sentence, the page behind it and the words the find box answers to. The key, the
        chapter's card and the readout are all drawn from that row.

        The five chapters below keep their numbers, names and ids (each bank's heading links
        its chapter). Each is the chapter's line, one real screen from the user guide and the
        bank's eight features as a ledger. An entry's lamp shows whether its key is lit.
    --}}
    @php
        $fbPlans = ['free' => 'Free', 'pro' => 'Pro', 'ent' => 'Enterprise'];

        // The five banks, in the order of the chapters. `k` is the bank's light, as an RGB
        // triplet: it colours the lit keys of its column and the glow under the stage.
        $fbBanks = [
            'sell' => [
                'no' => '01', 'name' => 'Sell', 'scene' => 'The checkout', 'k' => '96 165 250', 'ink' => '#1d4ed8',
                'title' => 'Take the money',
                'lede' => 'Tickets, passes, gift cards and paid bookings, with zero platform fees and payouts straight to your own Stripe account.',
                'shot' => 'tickets--sales', 'frame' => 'app.eventschedule.com/sales',
                'alt' => 'The Sales page: each order with its event, amount and status, beside the Import, Check-in and Scan Ticket buttons',
            ],
            'schedule' => [
                'no' => '02', 'name' => 'Schedule', 'scene' => 'The month', 'k' => '56 189 248', 'ink' => '#0369a1',
                'title' => 'Keep the calendar straight',
                'lede' => 'Two-way sync with the calendar you already live in, repeating events that build themselves, and AI that turns a flyer into a listing.',
                'shot' => 'managing-schedules--schedule-tab', 'frame' => 'app.eventschedule.com/schedule',
                'alt' => 'The Schedule tab: a month of events on a calendar, with This Month, Filter Events and Add Event above it',
            ],
            'promote' => [
                'no' => '03', 'name' => 'Promote', 'scene' => 'The word', 'k' => '34 211 238', 'ink' => '#0e7490',
                'title' => 'Fill the room',
                'lede' => 'Publishing an event already tells the people who subscribed. Newsletters, shareable graphics and Meta ads cover everyone else, without opening a single ad manager.',
                'shot' => 'newsletters--create', 'frame' => 'app.eventschedule.com/newsletters',
                'alt' => 'The newsletter builder: a list of content blocks on one side and a live preview of the email on the other',
            ],
            'engage' => [
                'no' => '04', 'name' => 'Engage', 'scene' => 'The night', 'k' => '52 211 153', 'ink' => '#047857',
                'title' => 'Turn a crowd into a following',
                'lede' => 'Fan videos, polls, star ratings and privacy-first analytics, so the people who showed up once have a reason to come back.',
                'shot' => 'analytics--dashboard', 'frame' => 'app.eventschedule.com/analytics',
                'alt' => 'The analytics page: total views, views in the period, a chart of views over time and a breakdown by device',
            ],
            'own-it' => [
                'no' => '05', 'name' => 'Make it yours', 'scene' => 'The address', 'k' => '251 191 36', 'ink' => '#b45309',
                'title' => 'Make it yours',
                'lede' => 'Your domain, your branding, your team, your server. Event Schedule is open source, so nothing here is locked behind us.',
                'shot' => 'schedule-styling--section-style', 'frame' => 'app.eventschedule.com/edit',
                'alt' => 'The Style section of a schedule: its profile image, accent colour and typeface beside a live preview of the page',
            ],
        ];

        // id, bank, name, plan, the sentence, a note on the plan (or null), the page, find words,
        // and the key it cannot work without (or null).
        $fbKeys = [
            ['tickets', 'sell', 'Tickets', 'pro', 'Priced ticket types, paid by Stripe, PayPal, Payfast, an Invoice Ninja invoice, a payment link or cash. The platform fee is zero.', 'A Stripe or PayPal sale can be refunded in full or in part.', '/features/ticketing', 'ticketing paid sell stripe paypal payfast cash refund checkout price', null],
            ['registration', 'sell', 'Free registration', 'free', 'Sign-ups with no payment step, a cap per date and a QR code for the door.', null, '/features/registration', 'rsvp sign up capacity guest list', null],
            ['promo', 'sell', 'Promo codes', 'pro', 'A percentage or a fixed amount off, with a usage limit and an expiry date.', null, '/features/promo-codes', 'discount coupon voucher code add-ons', 'tickets'],
            ['passes', 'sell', 'Passes', 'pro', 'One purchase used across several events: class packs, memberships, season and festival passes.', null, '/features/passes', 'membership subscription season class pack multi visit', 'tickets'],
            ['gift', 'sell', 'Gift cards', 'pro', 'Sent to someone by email and redeemed toward tickets for any event on your schedule.', null, '/features/gift-cards', 'voucher balance present', 'tickets'],
            ['installments', 'sell', 'Installments', 'pro', 'A buyer splits a ticket over monthly charges, taken off the saved card.', 'Through Stripe only.', '/features/installments', 'payment plan split instalments', 'tickets'],
            ['waitlist', 'sell', 'Waitlist', 'pro', 'When a sold-out event has tickets again, the people waiting are told automatically.', 'The waitlist for free registration is on every plan.', '/features/waitlist', 'sold out notify queue', 'tickets'],
            ['seating', 'sell', 'Reserved seating', 'ent', 'Draw the room once and buyers pick their own seats off the map.', null, '/features/allocated-seating', 'seat map allocated assigned box office seats', 'tickets'],

            ['recurring', 'schedule', 'Recurring events', 'free', 'Daily, weekly, every few weeks, monthly or yearly, with single dates added or skipped.', null, '/features/recurring-events', 'repeat repeating weekly monthly series', null],
            ['sync', 'schedule', 'Calendar sync', 'free', 'Two-way sync with Google Calendar, Outlook and any CalDAV server.', null, '/features/calendar-sync', 'google outlook caldav ical apple microsoft 365 feed', null],
            ['import', 'schedule', 'AI import', 'free', 'Paste text, a link or a flyer and the details are read into an event.', 'Daily limits apply.', '/features/ai', 'flyer parse paste poster link', null],
            ['subs', 'schedule', 'Sub-schedules', 'free', 'Sort events by stage, series or room. Each one gets a colour, a visitor filter and its own URL.', null, '/features/sub-schedules', 'stage room category filter', null],
            ['online', 'schedule', 'Online events', 'free', 'Tick Online and paste the link people join on: Zoom, YouTube, Teams or your own page.', null, '/features/online-events', 'virtual zoom stream webinar teams youtube', null],
            ['appointments', 'schedule', 'Appointments', 'free', 'Bookable appointment types with weekly hours. Guests pick an open time in their own timezone.', 'One free type on Free. More types and paid bookings are Pro.', '/features/appointments', 'booking calendly slots hours lessons', null],
            ['requests', 'schedule', 'Booking requests', 'free', 'Promoters ask to book an act and acts ask a venue for a date, on a form on your schedule page.', null, '/features/booking-requests', 'submit event request form gig', null],
            ['availability', 'schedule', 'Availability', 'ent', 'Everyone on a talent schedule marks the dates they cannot play, where the whole team can see them.', null, '/features/availability', 'unavailable dates away band', null],

            ['newsletters', 'promote', 'Newsletters', 'free', 'Subscribers get a digest when you publish new events. Write the rest in a drag-and-drop builder.', '10 emails a month on Free, 100 on Pro, 1,000 on Enterprise.', '/features/newsletters', 'email digest campaign mailing a/b segments', null],
            ['graphics', 'promote', 'Event graphics', 'free', 'A shareable image and formatted text of your upcoming events, ready for Instagram or WhatsApp.', null, '/features/event-graphics', 'image social instagram poster share', null],
            ['signup', 'promote', 'Email sign-up', 'free', 'One field. A confirmed address gets your new-event digest and an account.', null, '/features/newsletters#list', 'subscribe subscribers followers audience list', null],
            ['links', 'promote', 'Short links', 'free', 'Each schedule link gets a short address like /instagram, with clicks counted.', null, '/features/analytics#short-links', 'link in bio utm url', null],
            ['embedcal', 'promote', 'Embed calendar', 'free', 'Drop your schedule into any website with a single iframe. No plugin required.', null, '/features/embed-calendar', 'iframe website widget wordpress site', null],
            ['lineup', 'promote', 'Lineup', 'free', 'Every act you list shows on the event page, linked where it has a page of its own.', null, '/features/lineup', 'acts performers bill venue pages curator sources', null],
            ['boost', 'promote', 'Boost ads', 'pro', 'Turn an event into a live Facebook and Instagram ad. No ad manager required.', null, '/features/boost', 'meta facebook instagram ads advertising', null],
            ['embedtix', 'promote', 'Embed tickets', 'pro', 'Sell tickets straight from your own site with an embeddable checkout.', null, '/features/embed-tickets', 'widget iframe website checkout', 'tickets'],

            ['checkin', 'engage', 'Check-in', 'free', 'Scan the QR code on any ticket at the door with a phone. Each ticket admits once.', 'The live count of who is in is Pro.', '/features/check-in', 'qr scan door scanner attendance', null],
            ['fan', 'engage', 'Fan videos', 'free', 'Fans add YouTube videos, photos and comments. Everything waits in an approval queue.', '25 fan photos on Free, no cap on Pro.', '/features/fan-videos', 'photos comments youtube ugc community', null],
            ['analytics', 'engage', 'Analytics', 'free', 'Web traffic, revenue and check-ins, with no third-party tracker involved.', null, '/features/analytics', 'views referrers utm stats visitors revenue', null],
            ['polls', 'engage', 'Polls', 'pro', 'Add a poll to any event. Signed-in guests pick one choice and see the result at once.', null, '/features/polls', 'vote voting survey question', null],
            ['feedback', 'engage', 'Feedback', 'pro', 'Attendees are emailed after the event for a star rating and a comment.', null, '/features/feedback', 'rating stars review survey after', null],
            ['carpool', 'engage', 'Carpool', 'pro', 'Attendees offer and claim rides to your event, with driver approval and reviews.', null, '/features/carpool', 'ride share lift driver', null],
            ['gallery', 'engage', 'Photo gallery', 'pro', 'Your own photos on an event page, with captions, credits and a full-screen view.', null, '/features/fan-videos#gallery', 'photos pictures images', null],
            ['sponsors', 'engage', 'Sponsor logos', 'pro', 'A tiered logo wall on your schedule page, for the people funding it.', null, '/features/lineup#sponsors', 'partners sponsor logo wall', null],

            ['whitelabel', 'own-it', 'White label', 'pro', 'Remove Event Schedule branding so the schedule your guests see is entirely yours.', 'A selfhosted install keeps one small credit line.', '/features/white-label', 'branding powered by remove logo banner', null],
            ['css', 'own-it', 'Custom CSS', 'pro', 'Take full control of your schedule styling with your own stylesheet.', null, '/features/custom-css', 'style theme design stylesheet', null],
            ['labels', 'own-it', 'Custom labels', 'pro', 'Rename the words your guests see so the schedule speaks your language.', null, '/features/custom-labels', 'rename wording words language', null],
            ['fields', 'own-it', 'Custom fields', 'pro', 'Ask ticket buyers for dietary needs, t-shirt sizes or anything else you need to know.', 'Questions on a ticket type are on every plan.', '/features/custom-fields', 'form questions checkout dietary', null],
            ['api', 'own-it', 'API and webhooks', 'pro', 'A REST API plus webhooks, so Event Schedule fits into whatever you already built.', null, '/features/integrations#block', 'rest developer webhook integrate', null],
            ['domain', 'own-it', 'Custom domain', 'ent', 'Point one DNS record at us and the certificate is issued automatically.', null, '/features/custom-domain', 'dns ssl cname url own domain', null],
            ['private', 'own-it', 'Private events', 'ent', 'Internal events for the team, and Unlisted ones reachable only by link, with an optional password.', 'Public and Draft are on every plan.', '/features/private-events', 'password unlisted internal hidden draft visibility', null],
            ['team', 'own-it', 'Team members', 'ent', 'Invite people by email as admins or viewers and run the schedule together.', null, '/features/team-scheduling', 'collaborate admin viewer staff invite', null],
        ];
        $fbKeys = array_map(fn ($k) => array_combine(['id', 'bank', 'name', 'plan', 'says', 'note', 'path', 'find', 'needs'], $k), $fbKeys);
        $fbByBank = [];
        foreach ($fbKeys as $fbKey) {
            $fbByBank[$fbKey['bank']][] = $fbKey;
        }
        $fbFreeCount = count(array_filter($fbKeys, fn ($k) => $k['plan'] === 'free'));

        // The address the mock schedule lives at: this install's own domain, as the claim box
        // at the foot of the page prints it.
        $fbHost = _base_domain();
        $fbHost = str_contains($fbHost, '.') && ! filter_var($fbHost, FILTER_VALIDATE_IP) ? $fbHost : 'eventschedule.com';
    @endphp

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present, so no-JS
         visitors, crawlers and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    {{-- The page's own styles (fb-*), on the house kit's tokens. Plain CSS on purpose: a utility
         class that is not already in the built stylesheet renders as nothing. Rules are written
         under the id where the kit's own (the weight it gives every b, the tracking it gives every
         heading) would otherwise outrank a bare class. --}}
    <style {!! nonce_attr() !!}>
        #hp [hidden] { display: none !important; }

        {{-- ---------- Hero: short, so the board's dusk shows in the first window ---------- --}}
        #hp .fb-hero { padding-block: clamp(2.75rem, 7vh, 5rem) clamp(2.25rem, 4.5vh, 3.5rem); }

        {{-- ---------- The board ---------- --}}
        #hp .fb-board { padding-block: clamp(5.5rem, 7vw, 7rem) clamp(6rem, 10vw, 9rem); }
        .fb-wrap { width: min(100% - 2.5rem, 84rem); margin-inline: auto; }
        .fb-board-head { display: grid; gap: 1.25rem 3rem; align-items: end; margin-bottom: clamp(1.5rem, 2.6vw, 2.25rem); }
        .fb-board-head .hp-h2 { margin-top: 0.9rem; }
        #hp .fb-board-say .hp-lead { max-width: 36rem; font-size: 1.1rem; }
        @media (min-width: 960px) {
            .fb-board-head { grid-template-columns: minmax(0, 1fr) minmax(0, 36rem); }
        }
        .fb-play {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            min-height: 2.9rem;
            margin-top: 1rem;
            padding: 0 1.15rem 0 0.95rem;
            border: 1px solid rgba(141, 176, 255, 0.5);
            border-radius: 999px;
            background: rgba(141, 176, 255, 0.12);
            font-size: 1rem;
            font-weight: 700;
            font-variation-settings: 'wght' 700;
            color: #fff;
            transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.12s ease;
        }
        .fb-play svg { width: 1.05rem; height: 1.05rem; color: #8db0ff; }
        .fb-play:hover { border-color: #8db0ff; background: rgba(141, 176, 255, 0.2); }
        .fb-play:active { transform: translateY(1px); }
        .fb-play[aria-pressed="true"] { border-color: #fff; background: #fff; color: #050814; }
        .fb-play[aria-pressed="true"] svg { color: #050814; }

        {{-- The console. From a small laptop up it is a grid: the stage, the find box and the
             readout beside it, the keys under them, the tally along the foot. The stage's width
             follows the window's HEIGHT, so the whole console fits one window where it can. --}}
        .fb-console {
            --k: 96 165 250;
            position: relative;
            padding: clamp(0.75rem, 1.5vw, 1.4rem);
            border: 1px solid rgba(125, 165, 255, 0.24);
            border-radius: clamp(1.4rem, 2.4vw, 2.1rem);
            background:
                radial-gradient(60rem 26rem at 22% -8rem, rgb(var(--k) / 0.2), transparent 70%),
                linear-gradient(180deg, #0d1533 0%, #080d20 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.09), 0 60px 130px -60px rgba(47, 102, 234, 0.75);
        }
        @media (min-width: 900px) {
            .fb-console {
                display: grid;
                grid-template-columns: clamp(24rem, calc((100vh - 32rem) * 1.68), 57%) minmax(0, 1fr);
                grid-template-rows: auto minmax(0, 1fr) auto auto auto;
                grid-template-areas: "stage find" "stage read" "stage tally" "tabs tabs" "keys keys";
                gap: 0.8rem clamp(0.8rem, 1.4vw, 1.3rem);
            }
            .fb-stagewrap { grid-area: stage; align-self: start; }
            .fb-find { grid-area: find; }
            .fb-read { grid-area: read; }
            .fb-tabs { grid-area: tabs; }
            .fb-keys { grid-area: keys; }
            .fb-tally { grid-area: tally; }
        }
        {{-- A window too short for that: the find box and the tally go to the foot of the
             console, and the readout has the stage's height to itself. --}}
        @media (min-width: 900px) and (max-height: 919.98px) {
            .fb-console {
                grid-template-columns: clamp(30rem, calc((100vh - 36rem) * 1.68), 57%) minmax(0, 1fr);
                grid-template-rows: auto auto auto auto;
                grid-template-areas: "stage read" "tabs tabs" "keys keys" "find tally";
            }
            .fb-find { align-self: center; }
            #hp .fb-tally { padding: 0; border: 0; background: none; }
            #hp .fb-tally-says { flex: 0 1 auto; }
            .fb-tally-foot { display: none; }
        }

        {{-- The stage: a lit screen on a dark desk, light in both of the site's modes. Everything
             inside is sized in em off one font size that follows the stage's own width, so a scene
             scales like a picture. --}}
        .fb-stagewrap { position: relative; min-width: 0; }
        .fb-stage {
            --s-bg: #f4f6fb;
            --s-2: #ffffff;
            --s-3: #e6ebf5;
            --s-ink: #0a1020;
            --s-ink2: #46516d;
            --s-line: rgba(10, 16, 32, 0.12);
            --s-dash: rgba(10, 16, 32, 0.3);
            --s-blue: #2f66ea;
            position: relative;
            container-type: inline-size;
            aspect-ratio: 420 / 250;
            overflow: hidden;
            border-radius: 1.15rem;
            background: var(--s-bg);
            color: var(--s-ink);
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.12), 0 30px 70px -34px #000, 0 0 90px -24px rgb(var(--k) / 0.6);
            transition: box-shadow 0.5s ease;
        }
        .fb-scene {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            gap: 0.85em;
            padding: 1.2em 1.3em 1.15em;
            font-size: 11px;
            font-size: calc(100cqw / 420 * 11);
            line-height: 1.3;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0s linear 0.3s, transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .fb-scene.is-live { opacity: 1; visibility: visible; transition-delay: 0s; }
        #hp .fb-scene b { font-variation-settings: 'wght' 760; }
        .fb-scene small { display: block; font-size: 0.84em; color: var(--s-ink2); }
        .fb-scene img { display: block; max-width: none; object-fit: cover; }
        .fb-stage-tab {
            position: absolute;
            z-index: 3;
            inset-block-start: -0.7rem;
            inset-inline-start: 1.1rem;
            padding: 0.3rem 0.7rem;
            border: 1px solid rgb(var(--k) / 0.5);
            border-radius: 999px;
            background: #0a1128;
            font-family: var(--hp-mono);
            font-size: 0.68rem;
            font-weight: 700;
            font-variation-settings: normal;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #dfe6fb;
        }
        .fb-stage-tab b { color: rgb(var(--k)); }

        {{-- A part of a scene that a key switches on. One with a socket keeps its place while
             its key is off: a dashed outline with the feature's name, so the scene shows what a
             press would add. --}}
        .fb-stage [data-fb-slot]:not(.is-on):not([data-fb-socket]) { display: none; }
        .fb-stage [data-fb-unless].is-off { display: none; }
        #hp .fb-stage [data-fb-socket]:not(.is-on) {
            position: relative;
            border: 1px dashed var(--s-dash);
            background: transparent;
            box-shadow: none;
        }
        .fb-stage [data-fb-socket]:not(.is-on) > * { visibility: hidden; }
        .fb-stage [data-fb-socket]:not(.is-on)::before { display: none; }
        .fb-stage [data-fb-socket]:not(.is-on)::after {
            content: attr(data-fb-socket);
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            border: 0;
            background: none;
            transform: none;
            font-family: var(--hp-mono);
            font-size: 0.72em;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            white-space: nowrap;
            color: var(--s-ink2);
        }
        @keyframes fb-pop {
            0% { opacity: 0; transform: translateY(0.5em) scale(0.97); }
            60% { opacity: 1; }
            100% { opacity: 1; transform: none; }
        }
        @keyframes fb-flash {
            0% { box-shadow: 0 0 0 0 rgb(var(--k) / 0.9); }
            100% { box-shadow: 0 0 0 0.9em rgb(var(--k) / 0); }
        }
        html.es-anim .fb-stage [data-fb-slot].is-new { animation: fb-pop 0.42s cubic-bezier(0.22, 1, 0.36, 1) both, fb-flash 0.9s ease-out 0.1s 1; }

        {{-- Parts shared by the scenes --}}
        .fb-ev { display: flex; align-items: center; gap: 0.9em; }
        .fb-ev-art { flex: none; width: 4.6em; height: 3.5em; border-radius: 0.7em; }
        .fb-ev-what { display: block; min-width: 0; }
        #hp .fb-ev-what b { display: block; font-size: 1.5em; font-variation-settings: 'wght' 840; letter-spacing: -0.035em; line-height: 1.05; }
        .fb-ev-what > span { display: block; margin-top: 0.25em; font-size: 0.92em; color: var(--s-ink2); }
        .fb-pill {
            display: inline-flex;
            flex: none;
            align-items: center;
            gap: 0.4em;
            min-height: 2em;
            padding: 0 0.85em;
            border: 1px solid var(--s-line);
            border-radius: 999px;
            background: var(--s-2);
            font-size: 0.86em;
            font-style: normal;
            font-weight: 700;
            font-variation-settings: 'wght' 700;
            white-space: nowrap;
            color: var(--s-ink);
        }
        .fb-pill.is-go { border-color: transparent; background: var(--s-blue); color: #fff; }
        .fb-card2 { border: 1px solid var(--s-line); border-radius: 0.9em; background: var(--s-2); }

        {{-- Scene 1, the checkout --}}
        .fb-co { display: grid; flex: 1; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); gap: 0.9em; min-height: 0; }
        .fb-co-list { display: flex; flex-direction: column; gap: 0.36em; min-height: 0; }
        .fb-row {
            display: flex;
            align-items: center;
            gap: 0.7em;
            min-height: 2.3em;
            padding: 0.2em 0.45em 0.2em 0.75em;
            border: 1px solid var(--s-line);
            border-radius: 0.75em;
            background: var(--s-2);
        }
        .fb-row > span:first-child { overflow: hidden; min-width: 0; margin-inline-end: auto; text-overflow: ellipsis; white-space: nowrap; }
        .fb-row b { display: inline; }
        .fb-row small { display: inline; margin-inline-start: 0.5em; }
        .fb-row .fb-pill { min-height: 1.75em; }
        #hp .fb-price { font-size: 1.1em; font-weight: 700; font-variation-settings: 'wght' 800; }
        .fb-step { display: inline-flex; align-items: center; gap: 0.5em; padding: 0.1em 0.45em; border: 1px solid var(--s-line); border-radius: 0.6em; }
        .fb-step i { font-style: normal; color: var(--s-ink2); }
        .fb-seats { display: flex; align-items: center; gap: 0.8em; min-height: 2.5em; padding: 0.3em 0.75em; border: 1px solid var(--s-line); border-radius: 0.75em; background: var(--s-2); }
        .fb-seats > b { flex: none; white-space: nowrap; }
        .fb-seats-map { display: grid; flex: 1; min-width: 0; grid-template-columns: repeat(18, 1fr); gap: 0.16em; }
        .fb-seats-map i { aspect-ratio: 1.25; border-radius: 0.18em 0.18em 0.3em 0.3em; background: var(--s-3); }
        .fb-seats-map i.is-taken { background: #8792ab; }
        .fb-seats-map i.is-mine { background: var(--s-blue); box-shadow: 0 0 0 0.1em var(--s-2), 0 0 0 0.2em var(--s-blue); }
        .fb-co-sum { display: flex; flex-direction: column; gap: 0.3em; padding: 0.7em 0.85em 0.75em; border: 1px solid var(--s-line); border-radius: 0.9em; background: var(--s-2); }
        #hp .fb-sum-h { font-size: 0.78em; font-family: var(--hp-mono); font-variation-settings: normal; letter-spacing: 0.12em; text-transform: uppercase; color: var(--s-ink2); }
        .fb-line { display: flex; justify-content: space-between; gap: 0.6em; min-height: 1.45em; padding-inline: 0.1em; border-radius: 0.3em; font-size: 0.9em; line-height: 1.45; }
        .fb-line span:last-child { font-variant-numeric: tabular-nums; }
        .fb-line.is-less span:last-child { color: #15803d; }
        .fb-line.is-fee { color: var(--s-ink2); }
        .fb-total { display: flex; align-items: baseline; justify-content: space-between; margin-top: auto; padding-top: 0.4em; border-top: 1px solid var(--s-line); }
        #hp .fb-total b { font-size: 1.5em; line-height: 1.1; font-variation-settings: 'wght' 860; letter-spacing: -0.04em; font-variant-numeric: tabular-nums; }
        .fb-inst { font-size: 0.8em; line-height: 1.2; color: var(--s-ink2); text-align: end; }
        .fb-pay { display: grid; place-items: center; min-height: 2.1em; border-radius: 0.75em; background: var(--s-blue); font-weight: 700; font-variation-settings: 'wght' 720; color: #fff; }

        {{-- Scene 2, the month --}}
        .fb-mo-head { display: flex; align-items: center; gap: 0.7em; min-height: 1.9em; }
        #hp .fb-mo-head > b { font-size: 1.5em; font-variation-settings: 'wght' 840; letter-spacing: -0.035em; }
        .fb-mo-subs { display: flex; gap: 0.4em; }
        .fb-mo-subs .fb-pill { min-height: 1.75em; padding-inline: 0.65em; font-size: 0.78em; }
        .fb-dot { flex: none; width: 0.55em; height: 0.55em; border-radius: 999px; background: currentColor; }
        .fb-mo-sync { margin-inline-start: auto; }
        .fb-mo-sync.fb-pill { min-height: 1.75em; font-size: 0.78em; color: var(--s-ink2); }
        .fb-mo-sync .fb-dot { color: #16a34a; }
        .fb-mo { display: grid; flex: 1; grid-template-columns: repeat(7, minmax(0, 1fr)); grid-template-rows: auto; grid-auto-rows: minmax(0, 1fr); overflow: hidden; border: 1px solid var(--s-line); border-radius: 0.9em; background: var(--s-2); min-height: 0; }
        .fb-wd { padding: 0.3em 0.45em; border-bottom: 1px solid var(--s-line); font-family: var(--hp-mono); font-size: 0.6em; letter-spacing: 0.1em; text-transform: uppercase; color: var(--s-ink2); }
        .fb-day { position: relative; display: flex; flex-direction: column; gap: 0.16em; min-width: 0; padding: 0.28em 0.3em 0.2em; border-inline-end: 1px solid var(--s-line); border-bottom: 1px solid var(--s-line); }
        .fb-day:nth-child(7n) { border-inline-end: 0; }
        .fb-day:nth-last-child(-n+7) { border-bottom: 0; }
        .fb-day > i { font-size: 0.72em; font-style: normal; line-height: 1; color: var(--s-ink2); }
        .fb-day.is-off > i { opacity: 0.45; }
        .fb-chip {
            --c: 86 97 124;
            display: block;
            overflow: hidden;
            padding: 0.2em 0.4em;
            border-radius: 0.38em;
            background: rgb(var(--c) / 0.14);
            font-size: 0.7em;
            font-weight: 700;
            font-variation-settings: 'wght' 680;
            line-height: 1.2;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: var(--s-ink);
        }
        .fb-chip.is-top { background: var(--s-blue); color: #fff; }
        .fb-chip.is-open { border: 1px solid rgb(var(--c) / 0.6); background: transparent; color: var(--s-ink2); }
        .fb-chip.is-ask { border: 1px dashed rgba(217, 119, 6, 0.9); background: rgba(245, 158, 11, 0.12); }
        .fb-away { position: absolute; inset-inline-start: 1.9em; bottom: 1.75em; padding: 0.45em 0.75em; border-radius: 0.7em; background: #070b18; box-shadow: 0 0.8em 1.6em -0.8em #000; font-size: 0.78em; color: #dfe6fb; }
        #hp .fb-away b { color: #fff; }
        .fb-stage.has-subs .fb-chip[data-sub="main"] { --c: 47 102 234; }
        .fb-stage.has-subs .fb-chip[data-sub="cellar"] { --c: 16 185 129; }
        .fb-stage.has-subs .fb-chip[data-sub="yard"] { --c: 245 158 11; }
        .fb-stage.has-subs .fb-chip[data-sub]:not(.is-top) { background: rgb(var(--c) / 0.22); }

        {{-- Scene 3, the word --}}
        .fb-word { display: grid; flex: 1; grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.95fr) minmax(0, 1.05fr); gap: 0.8em; min-height: 0; }
        .fb-col { display: flex; flex-direction: column; gap: 0.55em; min-width: 0; min-height: 0; }
        .fb-mail { display: flex; flex: 1; flex-direction: column; overflow: hidden; min-height: 0; }
        .fb-mail-art { flex: 1 1 2.5em; width: 100%; min-height: 2.5em; height: 2.5em; }
        .fb-mail-body { display: flex; flex-direction: column; align-items: flex-start; gap: 0.35em; padding: 0.6em 0.75em 0.7em; }
        #hp .fb-mail-body > b { font-size: 1.02em; font-variation-settings: 'wght' 800; letter-spacing: -0.02em; line-height: 1.15; }
        .fb-mail-body .fb-pill { min-height: 1.85em; margin-top: 0.15em; }
        .fb-field { display: flex; align-items: center; gap: 0.4em; min-height: 2.5em; padding: 0.3em 0.3em 0.3em 0.75em; }
        .fb-field > span { overflow: hidden; margin-inline-end: auto; font-size: 0.88em; text-overflow: ellipsis; white-space: nowrap; color: var(--s-ink2); }
        .fb-field .fb-pill { min-height: 1.85em; }
        .fb-ad { padding: 0.5em 0.75em 0.55em; }
        #hp .fb-ad b { display: block; font-size: 0.95em; }
        .fb-ad-tag { font-family: var(--hp-mono); font-size: 0.64em; letter-spacing: 0.1em; text-transform: uppercase; color: var(--s-ink2); }
        .fb-poster { position: relative; display: flex; flex: 1; flex-direction: column; justify-content: flex-end; gap: 0.25em; overflow: hidden; padding: 0.75em 0.8em; border-radius: 0.9em; background: #0b1027; color: #fff; }
        .fb-poster img { position: absolute; inset: 0; width: 100%; height: 100%; }
        .fb-poster::before { content: ""; position: absolute; inset: 0; z-index: 1; background: linear-gradient(180deg, rgba(5, 8, 20, 0.1) 0%, rgba(5, 8, 20, 0.88) 62%); }
        .fb-poster > b,
        .fb-poster > span { position: relative; z-index: 2; }
        #hp .fb-poster > b { font-size: 1.3em; font-variation-settings: 'wght' 880; letter-spacing: -0.03em; line-height: 1; text-transform: uppercase; }
        .fb-poster span { display: flex; justify-content: space-between; gap: 0.5em; padding-top: 0.25em; border-top: 1px solid rgba(255, 255, 255, 0.3); font-size: 0.8em; }
        .fb-poster span i { font-style: normal; opacity: 0.85; }
        .fb-short { display: flex; align-items: baseline; justify-content: space-between; gap: 0.5em; padding: 0.45em 0.75em; }
        .fb-short b { font-size: 0.9em; color: var(--s-blue); }
        .fb-acts { display: flex; flex-wrap: wrap; align-items: center; gap: 0.3em; padding: 0.45em 0.6em 0.5em; }
        .fb-acts small { flex-basis: 100%; font-family: var(--hp-mono); font-size: 0.62em; letter-spacing: 0.1em; text-transform: uppercase; }
        .fb-acts .fb-pill { min-height: 1.7em; padding-inline: 0.55em; font-size: 0.74em; color: var(--s-blue); }
        .fb-site { display: flex; flex: 1; flex-direction: column; overflow: hidden; }
        .fb-bar { display: flex; align-items: center; gap: 0.3em; padding: 0.45em 0.6em; border-bottom: 1px solid var(--s-line); }
        .fb-bar > i { width: 0.5em; height: 0.5em; border-radius: 999px; background: var(--s-3); }
        .fb-bar > span { overflow: hidden; margin-inline-start: 0.4em; font-family: var(--hp-mono); font-size: 0.72em; text-overflow: ellipsis; white-space: nowrap; color: var(--s-ink2); }
        .fb-site-art { width: 100%; height: 3.4em; }
        .fb-site-body { display: flex; flex: 1; flex-direction: column; gap: 0.45em; padding: 0.65em 0.7em; }
        .fb-ghost { display: grid; gap: 0.34em; margin-top: auto; }
        .fb-ghost i { height: 0.42em; border-radius: 999px; background: var(--s-3); }
        .fb-ghost i:last-child { width: 62%; }
        #hp .fb-site-body > b { font-size: 1.05em; font-variation-settings: 'wght' 820; }
        .fb-frame { display: grid; gap: 0.3em; padding: 0.45em; border: 1px solid var(--s-blue); border-radius: 0.6em; }
        .fb-frame .fb-chip { font-size: 0.72em; }
        .fb-frame-tix { display: flex; align-items: center; justify-content: space-between; gap: 0.4em; min-height: 2.4em; padding: 0.3em 0.35em 0.3em 0.6em; border: 1px solid var(--s-blue); border-radius: 0.6em; font-size: 0.85em; }
        .fb-frame-tix .fb-pill { min-height: 1.8em; }

        {{-- Scene 4, the night --}}
        .fb-night { display: grid; flex: 1; grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr) minmax(0, 1.1fr); gap: 0.8em; min-height: 0; }
        .fb-tkt { display: flex; flex: 1; flex-direction: column; align-items: center; justify-content: center; gap: 0.6em; padding: 0.8em 0.7em; text-align: center; }
        .fb-qr { width: 6.6em; height: 6.6em; color: var(--s-ink); }
        .fb-in { display: inline-flex; align-items: center; gap: 0.4em; padding: 0.3em 0.7em; border-radius: 999px; background: rgba(22, 163, 74, 0.16); font-size: 0.86em; font-weight: 700; font-variation-settings: 'wght' 720; color: #15803d; }
        .fb-ride { display: flex; align-items: center; justify-content: space-between; gap: 0.5em; min-height: 2.6em; padding: 0.35em 0.4em 0.35em 0.75em; font-size: 0.9em; }
        .fb-ride .fb-pill { min-height: 1.85em; }
        .fb-video { position: relative; display: grid; flex: 1; place-items: center; overflow: hidden; min-height: 5.5em; border-radius: 0.9em; background: #0b1027; }
        .fb-video img { position: absolute; inset: 0; width: 100%; height: 100%; }
        .fb-video > i { position: relative; display: grid; place-items: center; width: 2.6em; height: 2.6em; border-radius: 999px; background: rgba(255, 255, 255, 0.94); }
        .fb-video > i::after { content: ""; border-style: solid; border-width: 0.5em 0 0.5em 0.8em; border-color: transparent transparent transparent #0b1027; transform: translateX(0.1em); }
        .fb-video > span { position: absolute; inset-inline-start: 0.6em; bottom: 0.5em; padding: 0.15em 0.5em; border-radius: 999px; background: rgba(5, 8, 20, 0.7); font-size: 0.74em; color: #fff; }
        .fb-thumbs { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.35em; }
        .fb-thumbs img { width: 100%; height: auto; aspect-ratio: 1; border-radius: 0.5em; }
        #hp .fb-stage .fb-thumbs[data-fb-socket]:not(.is-on) { min-height: 2.6em; border-radius: 0.6em; }
        .fb-logos { display: flex; align-items: center; justify-content: space-between; gap: 0.4em; min-height: 2.3em; padding: 0.35em 0.75em; white-space: nowrap; }
        #hp .fb-logos b { font-size: 0.74em; font-variation-settings: 'wght' 860; letter-spacing: 0.04em; text-transform: uppercase; color: var(--s-ink2); }
        .fb-stat { display: flex; flex: 1; flex-direction: column; padding: 0.65em 0.75em; }
        .fb-stat-top { display: flex; align-items: baseline; justify-content: space-between; gap: 0.5em; }
        #hp .fb-stat-top b { font-size: 1.35em; font-variation-settings: 'wght' 860; letter-spacing: -0.03em; }
        .fb-spark { display: flex; flex: 1; align-items: flex-end; gap: 0.2em; min-height: 2.3em; margin-top: 0.4em; }
        .fb-spark i { flex: 1; border-radius: 0.2em 0.2em 0 0; background: var(--s-blue); opacity: 0.85; }
        .fb-poll { padding: 0.55em 0.75em 0.6em; }
        .fb-poll-row { position: relative; display: flex; justify-content: space-between; overflow: hidden; margin-top: 0.3em; padding: 0.26em 0.5em; border-radius: 0.45em; background: var(--s-3); font-size: 0.82em; }
        .fb-poll-row::before { content: ""; position: absolute; inset: 0 auto 0 0; width: var(--w); background: rgba(47, 102, 234, 0.3); }
        .fb-poll-row span { position: relative; }
        .fb-stars { display: flex; align-items: center; gap: 0.5em; min-height: 2.4em; padding: 0.35em 0.75em; }
        .fb-stars i { font-style: normal; letter-spacing: 0.08em; color: #b45309; }

        {{-- Scene 5, the address --}}
        .fb-win { display: flex; flex: 1; flex-direction: column; overflow: hidden; min-height: 0; }
        .fb-url { display: flex; flex: 1; align-items: center; gap: 0.4em; min-width: 0; margin-inline-start: 0.5em; padding: 0.28em 0.7em; border-radius: 999px; background: var(--s-3); font-family: var(--hp-mono); font-size: 0.8em; color: var(--s-ink); }
        .fb-url svg { flex: none; width: 1em; height: 1em; color: #15803d; }
        .fb-page { position: relative; display: flex; flex: 1; flex-direction: column; gap: 0.5em; padding: 0.8em 0.95em 0.6em; min-height: 0; transition: background-color 0.4s ease; }
        .fb-page-top { display: flex; align-items: center; gap: 0.6em; }
        .fb-page-top > img { flex: none; width: 2.2em; height: 2.2em; border-radius: 0.6em; }
        #hp .fb-page-top > b { font-size: 1.3em; font-variation-settings: 'wght' 840; letter-spacing: -0.03em; }
        .fb-crew { display: flex; }
        .fb-crew i { display: grid; place-items: center; width: 1.9em; height: 1.9em; margin-inline-start: -0.4em; border: 0.15em solid var(--s-2); border-radius: 999px; background: #2f66ea; font-size: 0.72em; font-style: normal; font-weight: 700; color: #fff; }
        .fb-crew i:nth-child(2) { background: #0e7490; }
        .fb-crew i:nth-child(3) { background: #b45309; }
        #hp .fb-page-h { font-family: var(--hp-mono); font-size: 0.7em; font-variation-settings: normal; letter-spacing: 0.14em; text-transform: uppercase; color: var(--s-ink2); }
        .fb-page-rows { display: grid; gap: 0.35em; }
        .fb-prow { display: flex; align-items: center; gap: 0.6em; min-height: 3em; padding: 0.35em 0.45em 0.35em 0.65em; border: 1px solid var(--s-line); border-radius: 0.7em; }
        .fb-prow > span:first-child { min-width: 0; margin-inline-end: auto; }
        .fb-prow b { display: block; font-size: 0.95em; }
        .fb-when { flex: none; font-size: 0.86em; font-variant-numeric: tabular-nums; color: var(--s-ink2); }
        .fb-page-top .fb-pill { min-height: 1.8em; margin-inline-start: auto; font-size: 0.78em; }
        .fb-page-top .fb-crew { margin-inline-start: 0.6em; }
        .fb-lock { display: inline-flex; align-items: center; gap: 0.3em; font-family: var(--hp-mono); font-size: 0.68em; letter-spacing: 0.06em; text-transform: uppercase; color: var(--s-ink2); }
        .fb-lock svg { width: 1.1em; height: 1.1em; }
        .fb-ask { font-size: 0.8em; color: var(--s-ink2); }
        .fb-powered { margin-top: auto; font-size: 0.74em; text-align: center; color: var(--s-ink2); }
        .fb-hook { position: absolute; inset-inline-end: 0.8em; bottom: 0.7em; padding: 0.4em 0.65em; border-radius: 0.6em; background: #070b18; box-shadow: 0 0.8em 1.6em -0.8em #000; font-family: var(--hp-mono); font-size: 0.72em; color: #a5f3c4; }
        .fb-hook i { font-style: normal; color: #9fb1d6; }
        {{-- Custom CSS: the same page in the owner's own stylesheet. --}}
        .fb-stage.has-css .fb-page { background: #fbf4e6; color: #2a1608; }
        .fb-stage.has-css .fb-page-top > b { font-family: Georgia, 'Times New Roman', serif; font-variation-settings: normal; font-style: italic; letter-spacing: -0.01em; }
        .fb-stage.has-css .fb-page-top > img { border-radius: 999px; }
        .fb-stage.has-css .fb-prow { border-color: rgba(42, 22, 8, 0.2); border-radius: 0; border-width: 0 0 1px; }
        .fb-stage.has-css .fb-when,
        .fb-stage.has-css .fb-prow small,
        .fb-stage.has-css .fb-page-h,
        .fb-stage.has-css .fb-ask,
        .fb-stage.has-css .fb-lock,
        .fb-stage.has-css .fb-powered { color: #6b4a2f; }
        .fb-stage.has-css .fb-page-top .fb-pill { border-radius: 0.2em; background: #9a3412; color: #fff; }
        #hp .fb-stage.has-css .fb-prow[data-fb-socket]:not(.is-on) { border-width: 1px; border-color: rgba(42, 22, 8, 0.35); }

        {{-- The find box --}}
        .fb-find { display: flex; align-items: center; gap: 0.6rem; min-width: 0; min-height: 2.9rem; padding: 0 0.95rem; border: 1px solid rgba(125, 165, 255, 0.34); border-radius: 0.85rem; background: #0a1128; color: #9fb1d6; transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .fb-find:focus-within { border-color: #8db0ff; box-shadow: 0 0 0 4px rgba(141, 176, 255, 0.28); }
        .fb-find svg { flex: none; width: 1.1rem; height: 1.1rem; }
        #hp .fb-find input { flex: 1; width: 0; min-width: 0; border: 0; background: transparent; padding: 0; box-shadow: none; outline: 0; font: inherit; font-size: 1rem; color: #fff; }
        #hp .fb-find input::placeholder { color: #9fb1d6; opacity: 1; }
        #hp .fb-find input::-webkit-search-cancel-button { filter: invert(1); }
        #hp .fb-find kbd { flex: none; display: grid; place-items: center; width: 1.5rem; height: 1.5rem; border: 1px solid rgba(255, 255, 255, 0.18); border-radius: 0.4rem; font-size: 0.78rem; color: #9fb1d6; }
        .fb-find:focus-within kbd { display: none; }
        @media (hover: none) {
            #hp .fb-find kbd { display: none; }
        }
        #hp:not(.fb-js) .fb-find { display: none; }

        {{-- The readout. Every line keeps its room whether it has words or not, so the keys
             under it never move when a different key is read. --}}
        .fb-read {
            display: flex;
            flex-direction: column;
            min-width: 0;
            padding: clamp(1rem, 1.6vw, 1.5rem);
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 1.15rem;
            background: rgba(255, 255, 255, 0.035);
        }
        .fb-read-live { display: flex; flex: 1; flex-direction: column; min-height: 0; }
        .fb-read-top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; min-height: 1.75rem; }
        .fb-read-bank { font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: rgb(var(--k)); }
        .fb-plan {
            display: inline-flex;
            flex: none;
            align-items: center;
            min-height: 1.75rem;
            padding: 0 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.28);
            border-radius: 999px;
            font-family: var(--hp-mono);
            font-size: 0.7rem;
            font-weight: 700;
            font-variation-settings: normal;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            white-space: nowrap;
            color: #eef2ff;
        }
        #hp .fb-read-name { overflow: hidden; margin-top: 0.6rem; font-size: clamp(1.7rem, 1.5vw + 1rem, 2.5rem); font-weight: 700; font-variation-settings: 'wght' 830; letter-spacing: -0.04em; line-height: 1.05; text-overflow: ellipsis; white-space: nowrap; color: #fff; }
        .fb-read-says { min-height: 3.15rem; margin-top: 0.6rem; font-size: 1.05rem; line-height: 1.5; color: #c5cde2; text-wrap: pretty; }
        .fb-read-note { min-height: 1.35rem; margin-top: 0.35rem; font-size: 0.92rem; line-height: 1.45; color: #9fb1d6; }
        #hp .fb-read .hp-more { align-self: flex-start; margin-top: auto; padding-top: 0.5rem; }
        .fb-found { margin-top: 0.5rem; font-size: 0.92rem; line-height: 1.5; color: #9fb1d6; }
        .fb-found a { font-weight: 700; font-variation-settings: 'wght' 680; color: #a9c3ff; text-decoration: underline; text-underline-offset: 0.2em; }
        #hp:not(.fb-js) .fb-read-live { display: none; }
        #hp.fb-js .fb-read-rest { display: none; }
        @media (min-width: 900px) and (max-width: 1239.98px) {
            .fb-read-says { min-height: 4.7rem; }
        }

        {{-- The keys --}}
        .fb-tabs { display: none; }
        .fb-keys { display: grid; gap: 1.1rem; margin-top: 0.4rem; }
        .fb-bank { min-width: 0; }
        .fb-bank-head { display: flex; align-items: baseline; justify-content: space-between; gap: 0.5rem; padding-bottom: 0.5rem; margin-bottom: 0.5rem; border-bottom: 2px solid rgb(var(--k) / 0.75); }
        #hp .fb-bank-head h3 { font-size: 0.95rem; font-weight: 700; font-variation-settings: 'wght' 760; letter-spacing: -0.01em; color: #fff; }
        .fb-bank-head h3 a { display: inline-flex; align-items: baseline; gap: 0.5rem; }
        .fb-bank-head h3 a:hover { color: rgb(var(--k)); }
        .fb-bank-head h3 b { font-family: var(--hp-mono); font-size: 0.7rem; font-variation-settings: normal; letter-spacing: 0.1em; color: rgb(var(--k)); }
        .fb-bank-head > span { font-family: var(--hp-mono); font-size: 0.7rem; letter-spacing: 0.08em; color: #9fb1d6; font-variant-numeric: tabular-nums; }
        .fb-bank ul { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.4rem; }
        {{-- A key: a cap that stands a little proud while it is off, and sits lit when it is on. --}}
        .fb-key {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            width: 100%;
            min-height: 2.75rem;
            padding: 0.3rem 0.65rem;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.7rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.075), rgba(255, 255, 255, 0.03));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.09), 0 2px 0 rgba(0, 0, 0, 0.45);
            font-size: 0.9rem;
            font-weight: 700;
            font-variation-settings: 'wght' 640;
            line-height: 1.2;
            text-align: start;
            color: #c3cde6;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
            transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease, box-shadow 0.25s ease, transform 0.1s ease, opacity 0.25s ease;
        }
        .fb-key > i { flex: none; width: 0.5rem; height: 0.5rem; border-radius: 999px; background: rgba(255, 255, 255, 0.22); transition: background-color 0.25s ease, box-shadow 0.25s ease; }
        .fb-key > span { min-width: 0; }
        .fb-key > em { flex: none; margin-inline-start: auto; font-family: var(--hp-mono); font-size: 0.62rem; font-style: normal; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.1em; text-transform: uppercase; color: #9fb1d6; }
        .fb-key:hover { border-color: rgb(var(--k) / 0.75); color: #fff; }
        .fb-key:active { transform: translateY(2px); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.09), 0 0 0 rgba(0, 0, 0, 0.45); }
        .fb-key.is-on {
            transform: translateY(1px);
            border-color: rgb(var(--k) / 0.65);
            background: linear-gradient(180deg, rgb(var(--k) / 0.28), rgb(var(--k) / 0.13));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.16), 0 1px 0 rgba(0, 0, 0, 0.45), 0 0 26px -8px rgb(var(--k) / 0.9);
            color: #fff;
        }
        .fb-key.is-on > i { background: rgb(var(--k)); box-shadow: 0 0 0.6rem 0.08rem rgb(var(--k) / 0.95); }
        .fb-key.is-on > em { color: #e3eafc; }
        #hp .fb-key.is-cur { border-color: #fff; }
        .fb-console { --hp-blue: #8db0ff; }
        .fb-key.is-dim { opacity: 0.5; }
        @media (min-width: 1100px) {
            .fb-keys { grid-template-columns: repeat(5, minmax(0, 1fr)); gap: clamp(0.7rem, 1.1vw, 1.1rem); }
            .fb-bank ul { grid-template-columns: minmax(0, 1fr); gap: 0.3rem; }
            .fb-key { min-height: 2.1rem; padding-block: 0.15rem; }
        }
        {{-- One bank at a time below a laptop, once a script can turn the tabs. --}}
        @media (max-width: 1099.98px) {
            #hp.fb-js .fb-tabs { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 0.35rem; }
            #hp.fb-js .fb-keys { margin-top: 0; }
            #hp.fb-js .fb-bank:not(.is-open) { display: none; }
            #hp.fb-js .fb-bank-head { display: none; }
        }
        .fb-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            min-width: 0;
            min-height: 2.75rem;
            padding: 0 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 999px;
            font-size: 0.92rem;
            font-weight: 700;
            font-variation-settings: 'wght' 680;
            white-space: nowrap;
            color: #c3cde6;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .fb-tab b { font-family: var(--hp-mono); font-size: 0.68rem; font-variation-settings: normal; color: rgb(var(--k)); }
        .fb-tab[aria-pressed="true"] { border-color: rgb(var(--k) / 0.75); background: rgb(var(--k) / 0.2); color: #fff; }
        .fb-tab [data-short] { display: none; }
        .fb-tab.has-hit::after { content: ""; flex: none; width: 0.4rem; height: 0.4rem; border-radius: 999px; background: rgb(var(--k)); }

        {{-- The tally along the foot of the console --}}
        .fb-tally { display: flex; flex-wrap: wrap; align-items: center; gap: 0.45rem 1rem; min-width: 0; padding: clamp(0.85rem, 1.3vw, 1.15rem) clamp(1rem, 1.6vw, 1.5rem); border: 1px solid rgba(255, 255, 255, 0.09); border-radius: 1.15rem; background: rgba(255, 255, 255, 0.035); }
        .fb-tally-n { display: inline-flex; align-items: baseline; gap: 0.45rem; font-size: 0.95rem; white-space: nowrap; color: #9fb1d6; }
        #hp .fb-tally-n b { min-width: 1.25em; font-size: 1.9rem; font-variation-settings: 'wght' 860; letter-spacing: -0.05em; line-height: 1; font-variant-numeric: tabular-nums; text-align: end; color: #fff; }
        .fb-ladder { display: flex; align-items: center; gap: 0.3rem; margin-inline-start: auto; }
        .fb-ladder li { padding: 0.28rem 0.7rem; border: 1px solid rgba(255, 255, 255, 0.16); border-radius: 999px; font-family: var(--hp-mono); font-size: 0.68rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #9fb1d6; transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease; }
        .fb-tally[data-plan="free"] .fb-ladder li[data-p="free"],
        .fb-tally[data-plan="pro"] .fb-ladder li[data-p="pro"],
        .fb-tally[data-plan="ent"] .fb-ladder li[data-p="ent"] { border-color: #fff; background: #fff; color: #050814; }
        .fb-tally-says { flex: 1 1 100%; font-size: 1.05rem; font-weight: 700; font-variation-settings: 'wght' 660; line-height: 1.4; color: #fff; }
        .fb-tally-foot { flex: 1 1 12rem; font-size: 0.9rem; line-height: 1.45; color: #9fb1d6; }
        .fb-tally-foot a { color: #a9c3ff; text-decoration: underline; text-underline-offset: 0.2em; }
        .fb-reset { min-height: 2.4rem; padding: 0 0.95rem; border: 1px solid rgba(125, 165, 255, 0.4); border-radius: 0.75rem; background: #101833; font-size: 0.9rem; font-weight: 700; font-variation-settings: 'wght' 700; white-space: nowrap; color: #eef2ff; transition: border-color 0.2s ease; }
        .fb-reset:hover { border-color: #8db0ff; }

        {{-- The board wakes once, when it is first seen: its lit keys come on down each bank,
             and each piece of the month lands on the beat its key lights. --}}
        @keyframes fb-wake {
            0% { transform: none; border-color: rgba(255, 255, 255, 0.12); background: rgba(255, 255, 255, 0.05); box-shadow: none; color: #c3cde6; }
        }
        @keyframes fb-led {
            0% { background: rgba(255, 255, 255, 0.22); box-shadow: none; }
            60% { box-shadow: 0 0 1.1rem 0.3rem rgb(var(--k)); }
        }
        html.es-anim #hp.fb-js .fb-console:not(.is-seen) .fb-key.is-on { transform: none; border-color: rgba(255, 255, 255, 0.12); background: rgba(255, 255, 255, 0.05); box-shadow: none; color: #c3cde6; }
        html.es-anim #hp.fb-js .fb-console:not(.is-seen) .fb-key.is-on > i { background: rgba(255, 255, 255, 0.22); box-shadow: none; }
        html.es-anim #hp.fb-js .fb-console.is-waking .fb-key.is-on { animation: fb-wake 0.5s ease-out both; animation-delay: calc(var(--i) * 70ms + var(--b) * 110ms); }
        html.es-anim #hp.fb-js .fb-console.is-waking .fb-key.is-on > i { animation: fb-led 0.7s ease-out both; animation-delay: calc(var(--i) * 70ms + var(--b) * 110ms); }
        html.es-anim #hp.fb-js .fb-console:not(.is-seen) .fb-scene.is-live [data-fb-slot].is-on { opacity: 0; }
        html.es-anim #hp.fb-js .fb-console.is-waking .fb-scene.is-live [data-fb-slot].is-on { animation: fb-pop 0.45s cubic-bezier(0.22, 1, 0.36, 1) both; animation-delay: var(--at, 0ms); }
        {{-- The first key to press: a ring that beats three times and stops. --}}
        @keyframes fb-beat {
            0% { box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.09), 0 2px 0 rgba(0, 0, 0, 0.45), 0 0 0 0 rgb(var(--k) / 0.85); }
            100% { box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.09), 0 2px 0 rgba(0, 0, 0, 0.45), 0 0 0 0.85rem rgb(var(--k) / 0); }
        }
        html.es-anim .fb-key.is-hint { animation: fb-beat 1.3s ease-out 0.4s 3; }
        .fb-key.is-hint { border-color: rgb(var(--k) / 0.9); color: #fff; }

        {{-- Below a small laptop the stage stays in the window while the keys under it are
             pressed, and the readout follows the keys: on a phone what a press changed would
             otherwise be a screen above the finger. --}}
        @media (max-width: 899.98px) {
            .fb-console { display: flex; flex-direction: column; gap: 0.7rem; }
            .fb-stagewrap { order: 1; width: 100%; max-width: calc(45vh * 1.68); margin-inline: auto; }
            #hp.fb-js .fb-stage-tab { display: none; }
            .fb-find { order: 2; }
            .fb-tabs { order: 3; }
            .fb-keys { order: 4; }
            .fb-read { order: 5; }
            .fb-tally { order: 6; }
            .fb-tab [data-long],
            .fb-tab b { display: none; }
            .fb-tab [data-short] { display: inline; }
            .fb-tab { font-size: 0.84rem; padding: 0 0.2rem; }
            .fb-read-says { min-height: 4.7rem; }
        }
        @media (max-width: 899.98px) and (min-height: 34rem) {
            #hp.fb-js .fb-stagewrap { position: sticky; top: var(--fb-top, 4.1rem); z-index: 6; }
            #hp.fb-js .fb-stage { box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.12), 0 22px 40px -14px #000, 0 0 70px -24px rgb(var(--k) / 0.6); }
        }
        @media (max-width: 379.98px) {
            .fb-tab { font-size: 0.76rem; }
            .fb-key { font-size: 0.84rem; }
        }
        @media (max-width: 339.98px) {
            #hp.fb-js .fb-bank ul { grid-template-columns: minmax(0, 1fr); }
        }
        {{-- On a phone the stage is a window onto the scene at one and a half times its size,
             and it moves to whatever the last press changed: a whole scene at this width would
             be type nobody could read. --}}
        @media (max-width: 639.98px) {
            #hp .fb-board { padding-block: 5rem 5.5rem; }
            .fb-wrap { width: min(100% - 1.5rem, 84rem); }
            .fb-console { padding: 0.65rem; }
            .fb-stage { aspect-ratio: 342 / 236; }
            #hp.fb-js .fb-scene {
                inset: 0 auto auto 0;
                width: 150%;
                height: auto;
                aspect-ratio: 420 / 250;
                font-size: calc(150cqw / 420 * 11);
                transform: translate(var(--px, 0px), var(--py, 0px));
            }
            .fb-read { padding: 1rem; }
            #hp .fb-read-name { font-size: 1.6rem; }
            .fb-read-says { font-size: 1rem; }
            .fb-play { width: 100%; justify-content: center; }
        }

        {{-- ---------- The chapters ---------- --}}
        .fb-ch { position: relative; padding-block: clamp(4rem, 7.5vw, 7rem); }
        .fb-ch-head { position: relative; display: grid; gap: 1rem clamp(2rem, 4vw, 4rem); align-items: end; }
        {{-- The numeral is the chapter's mark. On a laptop it has the end of the row to itself;
             below that it stands in the corner over the title. --}}
        .fb-ch-num {
            position: absolute;
            inset-block-start: -0.18em;
            inset-inline-end: 0;
            font-size: clamp(5.5rem, 22vw, 9rem);
            font-weight: 700;
            font-variation-settings: 'wght' 900;
            letter-spacing: -0.07em;
            line-height: 0.8;
            color: rgb(var(--k));
            opacity: 0.3;
            pointer-events: none;
            user-select: none;
        }
        @media (min-width: 1024px) {
            .fb-ch-head { grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr) auto; }
            .fb-ch-num { position: static; order: 3; align-self: end; margin-bottom: -0.04em; font-size: clamp(8rem, 11vw, 11.5rem); }
        }
        .fb-ch-label { display: inline-flex; align-items: center; gap: 0.6rem; font-family: var(--hp-mono); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--c-ink); }
        .fb-ch-label::before { content: ""; width: 0.5rem; height: 0.5rem; border-radius: 0.15rem; background: rgb(var(--k)); }
        #hp .fb-ch-title { margin-top: 1rem; font-size: clamp(2.3rem, 3.4vw + 1rem, 4.4rem); font-weight: 700; font-variation-settings: 'wght' 850; letter-spacing: -0.045em; line-height: 0.98; text-wrap: balance; }
        .fb-ch-lede { max-width: 34rem; font-size: clamp(1.1rem, 0.5vw + 1rem, 1.3rem); line-height: 1.55; color: var(--hp-ink-2); text-wrap: pretty; }
        .fb-ch-lit { display: inline-flex; align-items: center; gap: 0.55rem; margin-top: 1rem; font-family: var(--hp-mono); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--hp-ink-3); }
        .fb-ch-lit i { width: 0.5rem; height: 0.5rem; border-radius: 999px; background: rgb(var(--k)); box-shadow: 0 0 0.55rem 0.05rem rgb(var(--k) / 0.9); }
        #hp:not(.fb-js) .fb-ch-lit { display: none; }

        {{-- A real screen from the user guide, across the page. A guide screenshot is 1280 by 757
             with a 288px sidebar and a 56px top bar: from a tablet up the window shows the top
             of the 992px beside the sidebar, larger than life so its type can be read; on a phone
             the first part of that. --}}
        .fb-shot { position: relative; overflow: hidden; margin-top: clamp(1.75rem, 3.5vw, 3rem); border: 1px solid var(--hp-line-2); border-radius: 1.25rem; background: var(--hp-bg-2); box-shadow: var(--hp-pop-shadow); }
        .fb-shot-bar { display: flex; align-items: center; gap: 0.4rem; padding: 0.65rem 0.9rem; border-bottom: 1px solid var(--hp-line); }
        .fb-shot-bar i { width: 0.6rem; height: 0.6rem; border-radius: 999px; background: var(--hp-line-2); }
        .fb-shot-bar span { margin-inline-start: 0.5rem; font-family: var(--hp-mono); font-size: 0.72rem; color: var(--hp-ink-3); }
        .fb-shot-pic { overflow: hidden; aspect-ratio: 4 / 3; background: var(--hp-bg-3); }
        .fb-shot-pic picture { display: block; }
        .fb-shot-pic .is-night,
        .dark .fb-shot-pic .is-day { display: none; }
        .dark .fb-shot-pic .is-night { display: block; }
        .fb-shot-pic { direction: ltr; }
        .fb-shot-pic img { display: block; max-width: none; width: 240%; height: auto; margin-left: -54%; margin-top: -10.5%; }
        @media (min-width: 640px) {
            .fb-shot-pic { aspect-ratio: 992 / 440; }
            .fb-shot-pic img { width: 129.4%; margin-left: -29.4%; margin-top: -5.75%; }
        }

        {{-- The bank's eight, as a ledger: the name, the plan, one sentence. The lamp beside a
             name is lit while its key is lit on the board. --}}
        .fb-ledger { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0 2rem; margin-top: clamp(1.5rem, 3vw, 2.5rem); }
        @media (min-width: 640px) {
            .fb-ledger { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .fb-ledger { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
        .fb-item { position: relative; padding: 1rem 0 1.15rem; border-top: 1px solid var(--hp-line-2); }
        .fb-item-top { display: flex; align-items: center; gap: 0.6rem; }
        .fb-item-top > i { flex: none; width: 0.55rem; height: 0.55rem; border-radius: 999px; background: var(--hp-line-2); transition: background-color 0.25s ease, box-shadow 0.25s ease; }
        .fb-item.is-on .fb-item-top > i { background: rgb(var(--k)); box-shadow: 0 0 0.55rem 0.05rem rgb(var(--k) / 0.9); }
        #hp .fb-item h3 { min-width: 0; font-size: 1.12rem; font-weight: 700; font-variation-settings: 'wght' 780; letter-spacing: -0.025em; line-height: 1.2; transition: color 0.2s ease; }
        .fb-item h3 a::after { content: ""; position: absolute; inset: 0; }
        .fb-item:hover h3 { color: var(--hp-blue); }
        .fb-item .fb-tier { margin-inline-start: auto; }
        .fb-item p { margin-top: 0.45rem; font-size: 0.95rem; line-height: 1.5; color: var(--hp-ink-2); }
        .fb-item small { display: block; margin-top: 0.35rem; font-size: 0.86rem; line-height: 1.45; color: var(--hp-ink-3); }
        .fb-tier { display: inline-flex; flex: none; align-items: center; min-height: 1.45rem; padding: 0 0.55rem; border: 1px solid var(--hp-line-2); border-radius: 999px; font-family: var(--hp-mono); font-size: 0.64rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; white-space: nowrap; color: var(--hp-ink-2); }
        .fb-ch-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem 2rem; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--hp-line-2); font-size: 0.98rem; color: var(--hp-ink-2); }
        {{-- What it plugs into, at the foot of the calendar chapter. --}}
        .fb-with { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem 1.25rem; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--hp-line-2); }
        #hp .fb-with h3 { font-size: 1.05rem; font-weight: 700; font-variation-settings: 'wght' 760; letter-spacing: -0.02em; }
        .fb-with ul { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .fb-with li a { display: inline-flex; align-items: center; gap: 0.5rem; min-height: 2.6rem; padding: 0 0.9rem 0 0.7rem; border: 1px solid var(--hp-line-2); border-radius: 999px; background: var(--hp-bg-2); font-size: 0.92rem; font-weight: 700; font-variation-settings: 'wght' 680; color: var(--hp-ink-2); transition: border-color 0.2s ease, color 0.2s ease; }
        .fb-with li a:hover { border-color: var(--hp-blue); color: var(--hp-ink); }
        .fb-with li svg,
        .fb-with li img { width: 1.35rem; height: 1.35rem; }
        .fb-with .hp-more { margin-inline-start: auto; }

        {{-- Chapter 03 is the page's second night: dark in both modes, its colours literal. --}}
        #hp .hp-dark .fb-ch { padding-block: clamp(1rem, 3vw, 2.5rem); }
        .hp-dark .fb-ch-label { color: rgb(var(--k)); }
        .hp-dark .fb-ch-lede { color: #c5cde2; }
        .hp-dark .fb-ch-lit { color: #9fb1d6; }
        .hp-dark .fb-shot { border-color: rgba(125, 165, 255, 0.28); background: #0b1124; box-shadow: 0 40px 90px -40px rgba(47, 102, 234, 0.8); }
        .hp-dark .fb-shot-bar { border-bottom-color: rgba(255, 255, 255, 0.1); }
        .hp-dark .fb-shot-bar i { background: rgba(255, 255, 255, 0.2); }
        .hp-dark .fb-shot-bar span { color: #9fb1d6; }
        .hp-dark .fb-item { border-top-color: rgba(255, 255, 255, 0.16); }
        .hp-dark .fb-item-top > i { background: rgba(255, 255, 255, 0.22); }
        .hp-dark .fb-item:hover h3 { color: #a9c3ff; }
        .hp-dark .fb-item p { color: #c5cde2; }
        .hp-dark .fb-item small { color: #9fb1d6; }
        .hp-dark .fb-tier { border-color: rgba(255, 255, 255, 0.28); color: #dfe6fb; }
        .dark .fb-ch-label { color: rgb(var(--k)); }

        {{-- ---------- The small print ---------- --}}
        .es-also-filter { display: inline-flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem; margin-top: 1.75rem; }
        .es-also-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            min-height: 2.75rem;
            padding: 0.35rem 1rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 9999px;
            background-color: var(--hp-bg-2);
            color: var(--hp-ink-2);
            font-size: 0.92rem;
            font-weight: 700;
            font-variation-settings: 'wght' 700;
            transition: background-color 0.2s, border-color 0.2s, color 0.2s;
        }
        .es-also-pill span { font-weight: 400; font-variation-settings: 'wght' 400; color: var(--hp-ink-3); font-variant-numeric: tabular-nums; }
        .es-also-pill:hover { border-color: var(--hp-blue); }
        .es-also-pill[aria-pressed="true"] { background-color: var(--hp-ink); border-color: var(--hp-ink); color: var(--hp-bg); }
        .es-also-pill[aria-pressed="true"] span { color: var(--hp-bg); opacity: 0.75; }
        .fb-also { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0 2.25rem; margin-top: clamp(2rem, 4vw, 3rem); }
        @media (min-width: 640px) {
            .fb-also { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .fb-also { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        .fb-also > div { padding: 0.95rem 0 1.05rem; border-top: 1px solid var(--hp-line-2); }
        .fb-also dt { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
        .fb-also dt a { font-weight: 700; font-variation-settings: 'wght' 720; line-height: 1.3; color: var(--hp-ink); text-decoration: underline; text-decoration-color: var(--hp-line-2); text-decoration-thickness: 1px; text-underline-offset: 0.25em; transition: color 0.2s ease, text-decoration-color 0.2s ease; }
        .fb-also dt a:hover { color: var(--hp-blue); text-decoration-color: var(--hp-blue); }
        .fb-also dd { margin-top: 0.35rem; font-size: 0.93rem; line-height: 1.5; color: var(--hp-ink-2); }
        .fb-also-more { display: none; }
        @media (max-width: 639.98px) {
            #hp.fb-js .fb-also.is-short > div:nth-child(n+9) { display: none; }
            #hp.fb-js .fb-also.is-short + .fb-also-more { display: flex; width: 100%; justify-content: center; margin-top: 1.25rem; }
        }
        .fb-also-above { max-width: 56rem; margin: 2rem auto 0; font-size: 0.95rem; line-height: 1.7; text-align: center; color: var(--hp-ink-2); }
        .fb-also-above b { color: var(--hp-ink); }
        .fb-also-end { margin-top: 2.5rem; font-size: 0.95rem; text-align: center; color: var(--hp-ink-3); }

        {{-- The finale's own line: what the visitor lit. --}}
        .fb-yours { display: inline-flex; align-items: baseline; gap: 0.6rem; max-width: 100%; margin-top: 1.5rem; padding: 0.6rem 1.05rem; border: 1px solid rgba(125, 165, 255, 0.3); border-radius: 1.25rem; font-size: 0.95rem; line-height: 1.45; text-align: start; color: #dfe6fb; }
        .fb-yours > i { flex: none; width: 0.5rem; height: 0.5rem; border-radius: 999px; background: #8db0ff; box-shadow: 0 0 0.6rem 0.08rem rgba(141, 176, 255, 0.9); transform: translateY(-0.1em); }

        @media (prefers-reduced-motion: reduce) {
            .fb-key,
            .fb-key > i,
            .fb-item-top > i,
            .fb-scene,
            .fb-stage,
            .fb-play,
            .fb-reset { transition: none; }
            .fb-key.is-hint,
            .fb-stage [data-fb-slot].is-new { animation: none; }
        }
    </style>

    {{-- ============================================================ --}}
    {{-- Hero: the headline, the lede and the two buttons, nothing else --}}
    {{-- ============================================================ --}}
    <section id="top" class="es-hero hp-hero fb-hero">
        <div class="hp-hero-sky" aria-hidden="true"></div>

        <div class="hp-hero-copy">
            <h1 class="hp-h1">
                <x-marketing.hero-eyebrow class="es-fade-up es-d-1 hp-eyebrow">
                    <span class="hp-live" aria-hidden="true"><i></i></span>
                    Event management software
                </x-marketing.hero-eyebrow>
                <span class="es-mask"><span class="es-mask-line">Every feature,</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="hp-ink-grad">on one board</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 hp-sub">
                Everything you need to fill seats, from calendars and ticketing to newsletters and analytics.
            </p>

            <div class="es-fade-up es-d-3 hp-hero-actions">
                <a href="#board" class="hp-btn hp-btn-ghost is-down">
                    Explore features
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </a>
                <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary">
                    Start for free
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>

        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- The board                                                    --}}
    {{-- ============================================================ --}}
    @php
        // What is lit when the page arrives: the Free plan. A part of a scene carries is-on,
        // and the stage a has-<key> class, for exactly these.
        $fbLit = array_column(array_filter($fbKeys, fn ($k) => $k['plan'] === 'free'), 'id');
        $fbOn = fn (string $id) => in_array($id, $fbLit, true) ? ' is-on' : '';
        $fbHas = implode(' ', array_map(fn ($id) => 'has-'.$id, $fbLit));
        $fbPic = fn (string $name) => asset('images/demo/demo_'.$name.'.webp');

        // October 2026 begins on a Thursday. One line per day that holds something:
        // [label, sub-schedule, the key that puts it there (null: always), a class].
        $fbMonthDays = [
            2 => ['Jazz Night', 'main', 'recurring', ''],
            6 => ['Lesson', null, 'appointments', 'is-open'],
            7 => ['Open Mic', 'cellar', null, ''],
            9 => ['Jazz Night', 'main', 'recurring', ''],
            10 => ['Quartet', 'main', null, ''],
            14 => ['Online set', 'cellar', 'online', ''],
            15 => ['Blues Jam', 'cellar', null, ''],
            16 => ['Jazz Night', 'main', 'recurring', ''],
            18 => ['Record Fair', 'yard', 'import', ''],
            20 => ['Lesson', null, 'appointments', 'is-open'],
            23 => ['Jazz Night', 'main', null, 'is-top'],
            24 => ['Big Band', 'main', null, ''],
            30 => ['Jazz Night', 'main', 'recurring', ''],
            31 => ['Request', null, 'requests', 'is-ask'],
        ];
        // The room, three rows of eighteen: 0 free, 1 taken, 2 the buyer's own two.
        $fbSeats = [
            [0, 0, 1, 1, 0, 0, 1, 0, 0, 1, 1, 0, 1, 0, 0, 1, 0, 0],
            [0, 1, 1, 0, 0, 1, 2, 2, 0, 0, 1, 1, 0, 0, 1, 0, 1, 0],
            [1, 0, 0, 1, 1, 0, 0, 1, 1, 0, 0, 1, 0, 1, 1, 0, 0, 1],
        ];
        $fbBars = [34, 52, 41, 66, 58, 88, 72, 95, 64, 80];

        // A picture of a QR code, not a code: the three corner marks and a fixed scatter
        // between them, as one path. The same for every visitor (the page is cached).
        $fbQr = '';
        $fbQrSeed = 20261009;
        for ($fbQy = 0; $fbQy < 21; $fbQy++) {
            for ($fbQx = 0; $fbQx < 21; $fbQx++) {
                $fbQon = null;
                foreach ([[0, 0], [14, 0], [0, 14]] as [$fbQox, $fbQoy]) {
                    $fbQdx = $fbQx - $fbQox;
                    $fbQdy = $fbQy - $fbQoy;
                    if ($fbQdx >= -1 && $fbQdx <= 7 && $fbQdy >= -1 && $fbQdy <= 7) {
                        $fbQin = $fbQdx >= 0 && $fbQdx <= 6 && $fbQdy >= 0 && $fbQdy <= 6;
                        $fbQon = $fbQin && ($fbQdx === 0 || $fbQdx === 6 || $fbQdy === 0 || $fbQdy === 6 || ($fbQdx >= 2 && $fbQdx <= 4 && $fbQdy >= 2 && $fbQdy <= 4));
                    }
                }
                if ($fbQon === null) {
                    $fbQrSeed = ($fbQrSeed * 1103515245 + 12345) % 2147483648;
                    $fbQon = intdiv($fbQrSeed, 65536) % 5 < 2;
                }
                if ($fbQon) {
                    $fbQr .= 'M'.$fbQx.' '.$fbQy.'h1v1h-1z';
                }
            }
        }
    @endphp
    <section id="board" class="hp-dark fb-board" aria-labelledby="fb-board-title">
        <div class="fb-wrap">
            <header class="fb-board-head">
                <div>
                    <span class="hp-kicker" data-reveal>The board</span>
                    <h2 id="fb-board-title" class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">Forty features. <span class="hp-ink-grad">Press one.</span></h2>
                </div>
                <div class="fb-board-say" data-reveal style="--reveal-delay: 0.14s;">
                    <p class="hp-lead">
                        Every key is a feature. The seventeen lit ones are what the Free plan gives you. Press any key and one night at The Indigo Room changes.
                    </p>
                    <button type="button" class="fb-play" data-fb-play hidden>
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 001.53.85l10.2-6.5a1 1 0 000-1.7l-10.2-6.5A1 1 0 008 5.5z" /></svg>
                        <span data-fb-play-label>Play all forty</span>
                    </button>
                </div>
            </header>

            <div class="fb-console" data-fb-console data-reveal="panel"
                 data-pricing="{{ marketing_url('/pricing#compare') }}"
                 data-price-pro="{{ plan_price($proMonthly) }} a month"
                 data-price-ent="{{ plan_price($entMonthly) }} a month"
                 style="--k: {{ $fbBanks['schedule']['k'] }};">

                {{-- The stage: one night, seen where each bank touches it. A picture for the
                     eye; the words are in the readout beside it. It is a lit screen on a dark
                     desk, so it stays light in both of the site's modes. --}}
                <div class="fb-stagewrap" aria-hidden="true">
                    <span class="fb-stage-tab"><b data-fb-scene-no>02</b> <span data-fb-scene-name>The month</span></span>
                    <div class="fb-stage {{ $fbHas }}" data-fb-stage>

                        {{-- 01 The checkout --}}
                        <div class="fb-scene" data-fb-scene="sell">
                            <div class="fb-ev">
                                <img class="fb-ev-art" src="{{ $fbPic('flyer_jazz') }}" alt="" width="800" height="600" loading="lazy" decoding="async">
                                <span class="fb-ev-what"><b>Jazz Night</b><span>Fri, Oct 23, <bdi dir="ltr">8:00 PM</bdi> at The Indigo Room</span></span>
                            </div>
                            <div class="fb-co">
                                <div class="fb-co-list">
                                    <div class="fb-row{{ $fbOn('registration') }}" data-fb-slot="registration" data-fb-socket="Free registration"><span><b>Free entry</b><small>42 of 80 places left</small></span><em class="fb-pill is-go">Register</em></div>
                                    <div class="fb-row{{ $fbOn('tickets') }}" data-fb-slot="tickets" data-fb-socket="Tickets"><span><b>General</b><small>Standing</small></span><span class="fb-price">$24</span><span class="fb-step"><i>-</i><b>2</b><i>+</i></span></div>
                                    <div class="fb-row{{ $fbOn('tickets') }}" data-fb-slot="tickets"><span><b>VIP table</b><small>Seats four</small></span><span class="fb-price">$64</span><span class="fb-step"><i>-</i><b>0</b><i>+</i></span></div>
                                    <div class="fb-row{{ $fbOn('passes') }}" data-fb-slot="passes" data-fb-socket="Passes"><span><b>10-visit pass</b><small>7 visits left</small></span><em class="fb-pill">Use pass</em></div>
                                    <div class="fb-row{{ $fbOn('waitlist') }}" data-fb-slot="waitlist" data-fb-socket="Waitlist"><span><b>Waitlist</b><small>Opens if the night sells out</small></span></div>
                                    <div class="fb-seats{{ $fbOn('seating') }}" data-fb-slot="seating" data-fb-socket="Reserved seating">
                                        <span class="fb-seats-map">
                                            @foreach ($fbSeats as $fbSeatRow)
                                                @foreach ($fbSeatRow as $fbSeat)<i @class(['is-taken' => $fbSeat === 1, 'is-mine' => $fbSeat === 2])></i>@endforeach
                                            @endforeach
                                        </span>
                                        <b>B7, B8</b>
                                    </div>
                                </div>
                                <div class="fb-co-sum">
                                    <b class="fb-sum-h">Your order</b>
                                    <div class="fb-line{{ $fbOn('registration') }}" data-fb-slot="registration"><span>1 x Free entry</span><span>Free</span></div>
                                    <div class="fb-line{{ $fbOn('tickets') }}" data-fb-slot="tickets"><span>2 x General</span><span>$48.00</span></div>
                                    <div class="fb-line is-less{{ $fbOn('promo') }}" data-fb-slot="promo" data-fb-socket="Promo code"><span>Code JAZZ10</span><span>-10%</span></div>
                                    <div class="fb-line is-less{{ $fbOn('gift') }}" data-fb-slot="gift" data-fb-socket="Gift card"><span>Gift card</span><span>-$20.00</span></div>
                                    <div class="fb-line is-fee"><span>Platform fee</span><span>0%</span></div>
                                    <div class="fb-total"><span>Total</span><b data-fb-total>Free</b></div>
                                    <div class="fb-inst{{ $fbOn('installments') }}" data-fb-slot="installments">or 3 monthly payments</div>
                                    <span class="fb-pay" data-fb-pay>Register</span>
                                </div>
                            </div>
                        </div>

                        {{-- 02 The month --}}
                        <div class="fb-scene is-live" data-fb-scene="schedule">
                            <div class="fb-mo-head">
                                <b>October</b>
                                <span class="fb-mo-subs{{ $fbOn('subs') }}" data-fb-slot="subs">
                                    <span class="fb-pill"><span class="fb-dot" style="color: #2f66ea;"></span>Main room</span>
                                    <span class="fb-pill"><span class="fb-dot" style="color: #10b981;"></span>Cellar</span>
                                    <span class="fb-pill"><span class="fb-dot" style="color: #f59e0b;"></span>Yard</span>
                                </span>
                                <span class="fb-pill fb-mo-sync{{ $fbOn('sync') }}" data-fb-slot="sync"><span class="fb-dot"></span>Google Calendar, in sync</span>
                            </div>
                            <div class="fb-mo">
                                @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $fbWeekday)
                                    <span class="fb-wd">{{ $fbWeekday }}</span>
                                @endforeach
                                @for ($fbCell = 0; $fbCell < 35; $fbCell++)
                                    @php
                                        $fbDate = $fbCell - 3;
                                        $fbShown = $fbDate < 1 ? 30 + $fbDate : $fbDate;
                                        $fbDay = $fbDate >= 1 ? ($fbMonthDays[$fbDate] ?? null) : null;
                                    @endphp
                                    <div @class(['fb-day', 'is-off' => $fbDate < 1])>
                                        <i>{{ $fbShown }}</i>
                                        @if ($fbDay)
                                            <span class="fb-chip {{ $fbDay[3] }}{{ $fbDay[2] ? $fbOn($fbDay[2]) : '' }}" @if ($fbDay[1]) data-sub="{{ $fbDay[1] }}" @endif @if ($fbDay[2]) data-fb-slot="{{ $fbDay[2] }}" @endif>{{ $fbDay[0] }}</span>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                            <span class="fb-away{{ $fbOn('availability') }}" data-fb-slot="availability"><b>Mara Quill Trio</b> cannot play Oct 12 or 13</span>
                        </div>

                        {{-- 03 The word --}}
                        <div class="fb-scene" data-fb-scene="promote">
                            <div class="fb-word">
                                <div class="fb-col">
                                    <div class="fb-card2 fb-mail{{ $fbOn('newsletters') }}" data-fb-slot="newsletters" data-fb-socket="Newsletter">
                                        <img class="fb-mail-art" src="{{ $fbPic('flyer_jazz') }}" alt="" width="800" height="600" loading="lazy" decoding="async">
                                        <span class="fb-mail-body">
                                            <small>The Indigo Room</small>
                                            <b>This week: Jazz Night, Big Band and a record fair</b>
                                            <span class="fb-pill is-go">See the week</span>
                                        </span>
                                    </div>
                                    <div class="fb-card2 fb-field{{ $fbOn('signup') }}" data-fb-slot="signup" data-fb-socket="Email sign-up"><span>Your email</span><em class="fb-pill is-go">Subscribe</em></div>
                                    <div class="fb-card2 fb-ad{{ $fbOn('boost') }}" data-fb-slot="boost" data-fb-socket="Boost ad"><span class="fb-ad-tag">Sponsored</span><b>Jazz Night, this Friday</b><small>2,400 reached, 86 clicks</small></div>
                                </div>
                                <div class="fb-col">
                                    <div class="fb-poster{{ $fbOn('graphics') }}" data-fb-slot="graphics" data-fb-socket="Event graphic">
                                        <img src="{{ $fbPic('flyer_jazz') }}" alt="" width="800" height="600" loading="lazy" decoding="async">
                                        <b>This week</b>
                                        <span>Jazz Night <i>Fri</i></span>
                                        <span>Big Band <i>Sat</i></span>
                                        <span>Record Fair <i>Sun</i></span>
                                    </div>
                                    <div class="fb-card2 fb-short{{ $fbOn('links') }}" data-fb-slot="links" data-fb-socket="Short link"><b>/instagram</b><small>148 clicks</small></div>
                                    <div class="fb-card2 fb-acts{{ $fbOn('lineup') }}" data-fb-slot="lineup" data-fb-socket="Lineup"><small>On the bill</small><span class="fb-pill">Mara Quill Trio</span><span class="fb-pill">Lena Ortiz</span></div>
                                </div>
                                <div class="fb-col">
                                    <div class="fb-card2 fb-site">
                                        <span class="fb-bar"><i></i><i></i><i></i><span>yourwebsite.com</span></span>
                                        <img class="fb-site-art" src="{{ $fbPic('header_theater') }}" alt="" width="1200" height="400" loading="lazy" decoding="async">
                                        <span class="fb-site-body">
                                            <b>Live music</b>
                                            <span class="fb-frame{{ $fbOn('embedcal') }}" data-fb-slot="embedcal" data-fb-socket="Embed calendar">
                                                <span class="fb-chip is-top">Fri, Jazz Night</span>
                                                <span class="fb-chip">Sat, Big Band</span>
                                                <span class="fb-chip">Sun, Record Fair</span>
                                            </span>
                                            <span class="fb-frame-tix{{ $fbOn('embedtix') }}" data-fb-slot="embedtix" data-fb-socket="Embed tickets"><span>2 x General</span><em class="fb-pill is-go">Pay</em></span>
                                            <span class="fb-ghost"><i></i><i></i></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 04 The night --}}
                        <div class="fb-scene" data-fb-scene="engage">
                            <div class="fb-night">
                                <div class="fb-col">
                                    <div class="fb-card2 fb-tkt{{ $fbOn('checkin') }}" data-fb-slot="checkin" data-fb-socket="Check-in">
                                        <svg class="fb-qr" viewBox="0 0 21 21" shape-rendering="crispEdges"><path fill="currentColor" d="{{ $fbQr }}" /></svg>
                                        <span class="fb-in">Admitted</span>
                                        <small>General, scanned at the door</small>
                                    </div>
                                    <div class="fb-card2 fb-ride{{ $fbOn('carpool') }}" data-fb-slot="carpool" data-fb-socket="Carpool"><span>2 seats from Riverside</span><em class="fb-pill">Ask</em></div>
                                </div>
                                <div class="fb-col">
                                    <div class="fb-video{{ $fbOn('fan') }}" data-fb-slot="fan" data-fb-socket="Fan video">
                                        <img src="{{ $fbPic('header_concert') }}" alt="" width="1200" height="400" loading="lazy" decoding="async">
                                        <i></i>
                                        <span>Added by a fan</span>
                                    </div>
                                    <div class="fb-thumbs{{ $fbOn('gallery') }}" data-fb-slot="gallery" data-fb-socket="Photo gallery">
                                        @foreach (['profile_jazz' => [1200, 1200], 'flyer_openmic' => [800, 600], 'header_theater' => [1200, 400], 'flyer_jazz' => [800, 600]] as $fbThumb => [$fbThumbW, $fbThumbH])
                                            <img src="{{ $fbPic($fbThumb) }}" alt="" width="{{ $fbThumbW }}" height="{{ $fbThumbH }}" loading="lazy" decoding="async">
                                        @endforeach
                                    </div>
                                    <div class="fb-card2 fb-logos{{ $fbOn('sponsors') }}" data-fb-slot="sponsors" data-fb-socket="Sponsors"><b>Kiln</b><b>Northside</b><b>Wave FM</b></div>
                                </div>
                                <div class="fb-col">
                                    <div class="fb-card2 fb-stat{{ $fbOn('analytics') }}" data-fb-slot="analytics" data-fb-socket="Analytics">
                                        <span class="fb-stat-top"><small>Views this week</small><b>1,284</b></span>
                                        <span class="fb-spark">@foreach ($fbBars as $fbBar)<i style="height: {{ $fbBar }}%;"></i>@endforeach</span>
                                    </div>
                                    <div class="fb-card2 fb-poll{{ $fbOn('polls') }}" data-fb-slot="polls" data-fb-socket="Poll">
                                        <b>Next month?</b>
                                        <span class="fb-poll-row" style="--w: 62%;"><span>Big band</span><span>62%</span></span>
                                        <span class="fb-poll-row" style="--w: 38%;"><span>Latin night</span><span>38%</span></span>
                                    </div>
                                    <div class="fb-card2 fb-stars{{ $fbOn('feedback') }}" data-fb-slot="feedback" data-fb-socket="Feedback"><i>&#9733;&#9733;&#9733;&#9733;&#9733;</i><b>4.7</b><small>86 ratings</small></div>
                                </div>
                            </div>
                        </div>

                        {{-- 05 The address --}}
                        <div class="fb-scene" data-fb-scene="own-it">
                            <div class="fb-card2 fb-win">
                                <span class="fb-bar">
                                    <i></i><i></i><i></i>
                                    <span class="fb-url" dir="ltr">
                                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M16.5 10.5V7a4.5 4.5 0 10-9 0v3.5M6 10.5h12a1 1 0 011 1V20a1 1 0 01-1 1H6a1 1 0 01-1-1v-8.5a1 1 0 011-1z" /></svg>
                                        <span data-fb-unless="domain">indigo-room.{{ $fbHost }}</span><span data-fb-slot="domain" class="{{ trim($fbOn('domain')) }}">events.yourdomain.com</span>
                                    </span>
                                </span>
                                <div class="fb-page">
                                    <div class="fb-page-top">
                                        <img src="{{ $fbPic('profile_jazz') }}" alt="" width="1200" height="1200" loading="lazy" decoding="async"><b>The Indigo Room</b>
                                        <em class="fb-pill is-go"><span data-fb-unless="labels">Follow</span><span data-fb-slot="labels" class="{{ trim($fbOn('labels')) }}">Join the club</span></em>
                                        <span class="fb-crew{{ $fbOn('team') }}" data-fb-slot="team"><i>M</i><i>J</i><i>R</i></span>
                                    </div>
                                    <span class="fb-page-h"><span data-fb-unless="labels">Events</span><span data-fb-slot="labels" class="{{ trim($fbOn('labels')) }}">Shows</span></span>
                                    <div class="fb-page-rows">
                                        <div class="fb-prow">
                                            <span><b>Jazz Night</b><small>Fri, Oct 23</small><small class="fb-ask{{ $fbOn('fields') }}" data-fb-slot="fields">Asks at checkout: any dietary needs?</small></span>
                                            <bdi dir="ltr" class="fb-when">8:00 PM</bdi>
                                        </div>
                                        <div class="fb-prow">
                                            <span><b>Big Band</b><small>Sat, Oct 24</small></span>
                                            <bdi dir="ltr" class="fb-when">9:00 PM</bdi>
                                        </div>
                                        <div class="fb-prow{{ $fbOn('private') }}" data-fb-slot="private" data-fb-socket="Private event">
                                            <span><b>Staff rehearsal</b><small>Sun, Oct 25</small></span>
                                            <span class="fb-lock"><svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16.5 10.5V7a4.5 4.5 0 10-9 0v3.5M6 10.5h12a1 1 0 011 1V20a1 1 0 01-1 1H6a1 1 0 01-1-1v-8.5a1 1 0 011-1z" /></svg>Internal</span>
                                        </div>
                                    </div>
                                    <span class="fb-powered" data-fb-unless="whitelabel">Powered by Event Schedule</span>
                                    <span class="fb-hook{{ $fbOn('api') }}" data-fb-slot="api" dir="ltr"><i>POST</i> sale.created <i>200</i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <label class="fb-find">
                    <span class="sr-only">Find a feature</span>
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.3-4.3M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" /></svg>
                    <input type="search" id="fb-find" placeholder="Find a feature: refunds, QR, Zoom" autocomplete="off" spellcheck="false" maxlength="40" data-fb-find>
                    <kbd aria-hidden="true">/</kbd>
                </label>

                {{-- The readout: what the key under the finger does, and the plan it needs. --}}
                <div class="fb-read">
                    <div class="fb-read-live" data-fb-read>
                        <div class="fb-read-top">
                            <span class="fb-read-bank" data-fb-read-bank>01 / Sell</span>
                            <span class="fb-plan" data-fb-read-plan>Pro, {{ plan_price($proMonthly) }} a month</span>
                        </div>
                        <h3 class="fb-read-name" data-fb-read-name>Start with Tickets</h3>
                        <p class="fb-read-says" data-fb-read-says>It is off, because a ticket with a price is a Pro feature. Press it and see what the checkout gains.</p>
                        <p class="fb-read-note" data-fb-read-note></p>
                        <a href="{{ marketing_url('/features/ticketing') }}" class="hp-more" data-fb-read-more>
                            <span data-fb-read-more-label>See how ticketing works</span>
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <p class="fb-found" data-fb-found hidden></p>
                    </div>
                    <div class="fb-read-rest">
                        <div class="fb-read-top"><span class="fb-read-bank">The whole product</span></div>
                        <h3 class="fb-read-name">Forty keys, five banks</h3>
                        <p class="fb-read-says">Each key opens the page about that feature. The lit ones are on the Free plan.</p>
                    </div>
                </div>

                {{-- One bank at a time below a laptop. --}}
                <div class="fb-tabs" role="group" aria-label="Banks" data-fb-tabs>
                    @foreach ($fbBanks as $fbBankId => $fbBank)
                        <button type="button" class="fb-tab" data-fb-tab="{{ $fbBankId }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" style="--k: {{ $fbBank['k'] }};"><b>{{ $fbBank['no'] }}</b><span data-long>{{ $fbBank['name'] }}</span><span data-short>{{ $fbBankId === 'own-it' ? 'Yours' : $fbBank['name'] }}</span></button>
                    @endforeach
                </div>

                <div class="fb-keys">
                    @foreach ($fbBanks as $fbBankId => $fbBank)
                        @php $fbBankLit = count(array_filter($fbByBank[$fbBankId], fn ($k) => $k['plan'] === 'free')); @endphp
                        <section @class(['fb-bank', 'is-open' => $loop->first]) data-fb-bank="{{ $fbBankId }}" data-no="{{ $fbBank['no'] }}" data-name="{{ $fbBank['name'] }}" data-scene="{{ $fbBank['scene'] }}" data-title="{{ $fbBank['title'] }}" style="--k: {{ $fbBank['k'] }}; --b: {{ $loop->index }};" aria-label="{{ $fbBank['name'] }}">
                            <div class="fb-bank-head">
                                <h3><a href="#{{ $fbBankId }}"><b>{{ $fbBank['no'] }}</b>{{ $fbBank['name'] }}</a></h3>
                                <span><span data-fb-bank-n>{{ $fbBankLit }}</span>/{{ count($fbByBank[$fbBankId]) }}</span>
                            </div>
                            <ul>
                                @foreach ($fbByBank[$fbBankId] as $fbKey)
                                    <li><a href="{{ marketing_url($fbKey['path']) }}" @class(['fb-key', 'is-on' => $fbKey['plan'] === 'free']) data-fb-key="{{ $fbKey['id'] }}" data-plan="{{ $fbKey['plan'] }}" data-find="{{ $fbKey['find'] }}" @if ($fbKey['needs']) data-needs="{{ $fbKey['needs'] }}" @endif style="--i: {{ $loop->index }};"><i aria-hidden="true"></i><span>{{ $fbKey['name'] }}</span>@if ($fbKey['plan'] === 'ent')<em><span aria-hidden="true">Ent</span><span class="sr-only">Enterprise</span></em>@else<em>{{ $fbPlans[$fbKey['plan']] }}</em>@endif</a></li>
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                </div>

                {{-- What a press did, said once for a screen reader: the readout itself changes
                     with every key the pointer passes, which would be noise if it spoke. --}}
                <p class="sr-only" aria-live="polite" data-fb-live></p>

                {{-- What the lit keys add up to. --}}
                <div class="fb-tally" data-fb-tally data-plan="free">
                    <span class="fb-tally-n"><b data-fb-n>{{ $fbFreeCount }}</b> of {{ count($fbKeys) }} on</span>
                    <ol class="fb-ladder" aria-hidden="true">
                        @foreach ($fbPlans as $fbPlanId => $fbPlanName)
                            <li data-p="{{ $fbPlanId }}">{{ $fbPlanName }}</li>
                        @endforeach
                    </ol>
                    <p class="fb-tally-says">
                        <span data-fb-needs="free">Everything lit is on the Free plan: {{ plan_price(0) }}, permanently.</span>
                        <span data-fb-needs="pro" hidden>This board runs on Pro: {{ plan_price($proMonthly) }} a month.</span>
                        <span data-fb-needs="ent" hidden>This board runs on Enterprise: {{ plan_price($entMonthly) }} a month.</span>
                    </p>
                    <p class="fb-tally-foot">No platform fee on tickets, whatever is lit. <a href="{{ marketing_url('/selfhost') }}">Selfhost it</a> and every Enterprise feature is included.</p>
                    <button type="button" class="fb-reset" data-fb-reset hidden>Back to Free</button>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- The five chapters: the line, one real screen, the bank's eight --}}
    {{-- ============================================================ --}}
    @foreach ($fbBanks as $fbBankId => $fbBank)
        {{-- Chapter 03 is the page's second night: one wrapper draws the dark ground and its
             dusk and dawn, and the chapter inside it is clear. --}}
        @if ($fbBankId === 'promote')
            <div class="hp-dark is-run">
        @endif
        <section id="{{ $fbBankId }}" @class(['fb-ch', 'hp-alt' => $fbBankId === 'schedule' || $fbBankId === 'own-it']) data-fb-ch="{{ $fbBankId }}" style="--k: {{ $fbBank['k'] }}; --c-ink: {{ $fbBank['ink'] }};">
            <div class="hp-wrap">
                <header class="fb-ch-head" data-reveal>
                    <span class="fb-ch-num" aria-hidden="true">{{ $fbBank['no'] }}</span>
                    <div>
                        <span class="fb-ch-label">Chapter {{ $fbBank['no'] }}</span>
                        <h2 class="fb-ch-title">{{ $fbBank['title'] }}</h2>
                    </div>
                    <div>
                        <p class="fb-ch-lede">{{ $fbBank['lede'] }}</p>
                        <p class="fb-ch-lit"><i aria-hidden="true"></i><span><span data-fb-ch-n>{{ count(array_filter($fbByBank[$fbBankId], fn ($k) => $k['plan'] === 'free')) }}</span> of {{ count($fbByBank[$fbBankId]) }} lit on your board</span></p>
                    </div>
                </header>

                <figure class="fb-shot" data-reveal="panel">
                    <div class="fb-shot-bar" aria-hidden="true"><i></i><i></i><i></i><span dir="ltr">{{ $fbBank['frame'] }}</span></div>
                    <div class="fb-shot-pic">
                        @foreach (['' => 'is-day', '-dark' => 'is-night'] as $fbShotSuffix => $fbShotTheme)
                            <picture class="{{ $fbShotTheme }}">
                                <source srcset="{{ url('images/docs/'.$fbBank['shot'].$fbShotSuffix.'.webp') }}" type="image/webp">
                                <img src="{{ url('images/docs/'.$fbBank['shot'].$fbShotSuffix.'.png') }}" alt="{{ $fbBank['alt'] }}" width="1280" height="757" loading="lazy" decoding="async">
                            </picture>
                        @endforeach
                    </div>
                </figure>

                <ul class="fb-ledger" data-reveal-group="40">
                    @foreach ($fbByBank[$fbBankId] as $fbKey)
                        <li @class(['fb-item', 'is-on' => $fbKey['plan'] === 'free']) data-fb-card="{{ $fbKey['id'] }}" data-reveal>
                            <div class="fb-item-top">
                                <i aria-hidden="true"></i>
                                <h3><a href="{{ marketing_url($fbKey['path']) }}">{{ $fbKey['name'] }}</a></h3>
                                <span class="fb-tier">{{ $fbPlans[$fbKey['plan']] }}</span>
                            </div>
                            <p>{{ $fbKey['says'] }}</p>
                            @if ($fbKey['note'])
                                <small>{{ $fbKey['note'] }}</small>
                            @endif
                        </li>
                    @endforeach
                </ul>

                @if ($fbBankId === 'schedule')
                    {{-- The calendars and the payment tools it plugs into. --}}
                    <div class="fb-with" data-reveal>
                        <h3>Works with what you already use</h3>
                        <ul>
                            @foreach ([['google', 'Google Calendar', '/google-calendar'], ['outlook', 'Outlook', '/outlook-calendar'], ['caldav', 'CalDAV', '/caldav'], ['stripe', 'Stripe', '/stripe'], ['invoiceninja', 'Invoice Ninja', '/invoiceninja']] as [$fbLogo, $fbLogoName, $fbLogoPath])
                                <li>
                                    <a href="{{ marketing_url($fbLogoPath) }}">
                                        @include('marketing.partials.integration-logo', ['name' => $fbLogo, 'class' => 'fb-with-logo'])
                                        <span>{{ $fbLogoName }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('marketing.integrations') }}" class="hp-more">See all integrations <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg></a>
                    </div>
                @endif
                @if ($fbBankId === 'own-it')
                    <p class="fb-ch-foot" data-reveal>
                        <span>Open source under the Attribution Assurance License. <a href="{{ marketing_url('/docs/account-settings#backup') }}" class="hp-inline">Backup and restore</a> takes out everything you have made, and brings it back.</span>
                        <a href="{{ marketing_url('/open-source') }}" class="hp-more">Read the source <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg></a>
                    </p>
                @endif
            </div>
        </section>
        @if ($fbBankId === 'promote')
            </div>
        @endif
    @endforeach

    {{-- ============================================================ --}}
    {{-- The small print                                              --}}
    {{-- ============================================================ --}}
    {{-- Thirty more things the app does, none of them a key on the board, and every one of them
         links to a FEATURE page, never to the user guide: a page of its own where it has one,
         otherwise the section of a feature page that describes it, by an id that page carries
         for this list (MarketingFeaturesSmallPrintTest fails the build on a row with no link, a
         link into /docs, or an anchor its page does not have). A new row with no home gets a
         section written for it first. Every row cites the gate it is really behind
         (docs/FEATURES.md), because a list like this goes stale the moment a tier moves. And the
         list is COMPLETE against that file: every feature in its Free, Pro and Enterprise tables
         is a row here, or is named in its plan's line under the list ($alsoAbove), or is left
         out for a reason the same test writes down. Keep the count a multiple of six so the
         grid ends on a full row at two columns and at three, and keep the "N more things"
         sentence in step with it. The find box on the board reads these rows too. --}}
    @php
        $alsoIncluded = [
            ['Ticket add-ons', 'Parking, merchandise or a workshop, each with its own stock', 'Pro', '/features/promo-codes#addons'],
            ['Multi-event cart', 'One checkout across several of your events, paid as a single amount', 'Free', '/features/ticketing#cart'],
            ['Sales windows and group rates', 'Open and close each ticket type on a date, and discount bigger orders', 'Free', '/features/ticketing#types'],
            ['PayPal checkout', 'Buyers pay into your own PayPal account, multi-event cart included', 'Pro', '/paypal'],
            ['Individual tickets', 'Every guest on an order gets their own email and their own QR code', 'Pro', '/features/ticketing#individual-tickets'],
            ['Refunds, full or partial', 'Money goes back through Stripe or PayPal, straight from the Sales page', 'Pro', '/features/ticketing#refunds'],
            ['Interest list', 'A "tell me when tickets go on sale" option that asks only for an email', 'Free', '/features/ticketing#interest-list'],
            ['Add to Google Wallet', 'Buyers save the ticket, QR and all, once the site operator switches it on', 'Free', '/features/integrations#google-wallet'],
            ['Bulk attendee import', 'Up to 5,000 rows from a CSV, for a list you already hold', 'Pro', '/features/ticketing#attendee-import'],
            ['Sales CSV export', 'Every sale across every schedule you own, custom fields included', 'Pro', '/features/ticketing#sales-export'],
            ['Sale notification emails', 'An email each time a ticket sells, with the buyer, the type and the amount', 'Pro', '/features/ticketing#sale-notifications'],
            ['Push notifications', 'Browser and mobile web push mirroring your email alerts', 'Pro', '/features/integrations#web-push'],
            ['Eventbrite import', 'Bring an existing run of events across in one go', 'Pro', '/features/integrations#eventbrite'],
            ['Import from a link', 'Paste your events page or a calendar address and up to 100 events come across', 'Free', '/features/ai#link-import'],
            ['Feeds from other sites', 'A calendar, an RSS feed or an events page your schedule re-reads about once an hour', 'Enterprise', '/features/ai#feeds'],
            ['Event templates', 'Save an event you repeat and start the next one from it', 'Pro', '/features/recurring-events#templates'],
            ['Event cloning', 'Duplicate any event as the starting point for the next one', 'Free', '/features/recurring-events#clone'],
            ['Agenda scanning', 'Photograph a running order and get the parts back as event parts', 'Enterprise', '/features/ai#agenda'],
            ['WhatsApp event creation', 'Message or photograph an event and it lands on the schedule', 'Enterprise', '/features/ai#whatsapp'],
            ['AI flyers and styles', 'A poster from the event details, or a whole look for your schedule, generated', 'Enterprise', '/features/ai#generate'],
            ['AI-written descriptions', 'A category and a description for an event, or a description for the schedule', 'Enterprise', '/features/ai#generate'],
            ['Scheduled graphic emails', 'Your events graphic emailed daily, weekly or monthly to the addresses you list', 'Enterprise', '/features/event-graphics#scheduled-email'],
            ['Live calendar and RSS feeds', 'Guests subscribe from Add to Calendar, and a moved date updates itself', 'Free', '/features/calendar-sync#feed'],
            ['Schedule transfer', 'Hand a schedule and its ticket revenue to another account', 'Free', '/features/team-scheduling#transfer'],
            ['Audit log', 'When, who and what, for every event, member, sale and check-in on a schedule', 'Free', '/features/team-scheduling#log'],
            ['Pages for the acts you list', 'Name an act or venue who is not here yet and they get a page to claim', 'Free', '/features/lineup#pages'],
            ['Event sources for curators', 'Follow venue and act schedules and everything they publish lands on your guide', 'Free', '/features/lineup#sources'],
            ['Venue logo wall', 'A header of the venues you play, or the acts you host, from your approved events', 'Free', '/features/lineup#wall'],
            ['Announcement banner', 'A banner of your own across the top of your schedule\'s public pages', 'Pro', '/features/white-label#banner'],
            ['Nearby accommodation map', 'Lodging near the venue on your event pages, once the site operator switches it on', 'Free', '/features/integrations#accommodation-map'],
        ];
        // A plan's rows are only the part of it that has no section above, so pressing a plan
        // used to answer "what is on Enterprise?" with two rows. These are the rest of each
        // plan's answer: what first arrives on that plan and has a banner or a card further up,
        // by the feature page behind it. The tier of every entry is its table in
        // docs/FEATURES.md, and MarketingFeaturesSmallPrintTest holds the two together.
        $alsoAbove = [
            'Free' => [
                ['Calendar sync', '/features/calendar-sync'],
                ['Recurring events', '/features/recurring-events'],
                ['Sub-schedules', '/features/sub-schedules'],
                ['Online events', '/features/online-events'],
                ['AI import', '/features/ai'],
                ['Newsletters, 10 emails a month', '/features/newsletters'],
                ['Event graphics', '/features/event-graphics'],
                ['Fan videos and photos', '/features/fan-videos'],
                ['Analytics', '/features/analytics'],
                ['Booking requests', '/features/booking-requests'],
                ['Embed calendar', '/features/embed-calendar'],
                ['One appointment type', '/features/appointments'],
                ['Ticket scanning at the door', '/features/check-in'],
                ['Free registration', '/features/registration'],
                ['Email sign-up', '/features/newsletters#list'],
                ['Short links', '/features/analytics#short-links'],
                ['The lineup', '/features/lineup'],
            ],
            'Pro' => [
                ['Paid tickets', '/features/ticketing'],
                ['Passes', '/features/passes'],
                ['Gift cards', '/features/gift-cards'],
                ['Paid appointments', '/features/appointments'],
                ['Custom fields', '/features/custom-fields'],
                ['Event polls', '/features/polls'],
                ['Post-event feedback', '/features/feedback'],
                ['Boost', '/features/boost'],
                ['White label', '/features/white-label'],
                ['Custom CSS', '/features/custom-css'],
                ['Custom labels', '/features/custom-labels'],
                ['Embed tickets', '/features/embed-tickets'],
                ['Carpool', '/features/carpool'],
                ['Check-in dashboard', '/features/check-in'],
                ['API and webhooks', '/features/integrations#block'],
                ['Invoice Ninja', '/invoiceninja'],
                ['100 newsletter emails a month', '/features/newsletters'],
                ['Unlimited fan photos', '/features/fan-videos'],
                ['Promo codes', '/features/promo-codes'],
                ['Installments', '/features/installments'],
                ['Ticket waitlist', '/features/waitlist'],
                ['Photo gallery', '/features/fan-videos#gallery'],
                ['Sponsor logos', '/features/lineup#sponsors'],
            ],
            'Enterprise' => [
                ['Custom domains', '/features/custom-domain'],
                ['Team members', '/features/team-scheduling'],
                ['Internal and unlisted events', '/features/private-events'],
                ['Availability', '/features/availability'],
                ['1,000 newsletter emails a month', '/features/newsletters'],
                ['Reserved seating', '/features/allocated-seating'],
            ],
        ];
    @endphp
    <section id="also" class="hp-sec">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>Off the board</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">And the small print, which is mostly good news</h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.14s;">Thirty more things the app does, and the plan each one sits on.</p>
                {{-- "What do I get for free?" is the question this list is read for, so it can be
                     asked of it. The buttons show the rows that carry that plan's badge; every row
                     is in the page from the start, so a reader without JavaScript (and a crawler)
                     gets all of them and no buttons, which the script below un-hides. --}}
                <div id="also-filter" class="es-also-filter" role="group" aria-label="Show by plan" hidden>
                    <button type="button" class="es-also-pill" data-also-tier="" aria-pressed="true">All <span>{{ count($alsoIncluded) }}</span></button>
                    @foreach (['Free', 'Pro', 'Enterprise'] as $alsoTierName)
                        <button type="button" class="es-also-pill" data-also-tier="{{ $alsoTierName }}" aria-pressed="false">{{ $alsoTierName }} <span>{{ count(array_filter($alsoIncluded, fn ($alsoCounted) => $alsoCounted[2] === $alsoTierName)) }}</span></button>
                    @endforeach
                </div>
            </div>
            <dl id="also-list" class="fb-also is-short" data-reveal-group="30">
                @foreach ($alsoIncluded as $alsoRow)
                    @php [$alsoName, $alsoDesc, $alsoTier, $alsoPath] = $alsoRow; @endphp
                    <div data-also-row="{{ $alsoTier }}" data-reveal>
                        <dt>
                            <a href="{{ marketing_url($alsoPath) }}">{{ $alsoName }}</a>
                            <span class="fb-tier" data-plan="{{ ['Free' => 'free', 'Pro' => 'pro', 'Enterprise' => 'ent'][$alsoTier] }}">{{ $alsoTier }}</span>
                        </dt>
                        <dd>{{ $alsoDesc }}</dd>
                    </div>
                @endforeach
            </dl>
            <button type="button" class="hp-btn hp-btn-ghost is-small fb-also-more" data-fb-also-more>Show all thirty</button>
            {{-- Shown with a plan pressed, by the script below: the rest of that plan's answer. --}}
            <div id="also-above" class="fb-also-above" hidden>
                @foreach ($alsoAbove as $alsoAboveTier => $alsoAboveLinks)
                    <p data-also-above="{{ $alsoAboveTier }}" hidden>
                        <b>{{ $alsoAboveTier }} also brings these, each further up this page:</b>
                        @foreach ($alsoAboveLinks as [$alsoAboveName, $alsoAbovePath])
                            <a href="{{ marketing_url($alsoAbovePath) }}" class="hp-inline">{{ $alsoAboveName }}</a>{{ $loop->last ? '.' : ',' }}
                        @endforeach
                    </p>
                @endforeach
            </div>
            <p class="fb-also-end" data-reveal>
                A selfhosted install has every row above.
                <a href="{{ marketing_url('/pricing#compare') }}" class="hp-inline">See the full plan comparison</a>.
            </p>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Questions, the strip, the sign-up panel                      --}}
    {{-- ============================================================ --}}
    @php
        $faqs = [
            [
                'q' => 'Is Event Schedule really free?',
                'a' => 'Yes. Unlimited events, unlimited schedules, calendar sync, free registration with capacity limits, analytics and QR check-in at the door are all included on the free plan, with no time limit and no credit card required. Newsletters are metered rather than unlimited, at 10 emails a month with each recipient counting as one. A free schedule is also a team of one, with one free appointment type, and a price on a ticket or a booking is where Pro begins.',
            ],
            [
                'q' => 'Do you take a cut of ticket sales?',
                'a' => 'No. Event Schedule charges zero platform fees on tickets, on every plan including free. You connect your own Stripe or PayPal account and payouts go straight to you, so the only deduction is what Stripe or PayPal charges to process the payment. Charging for a ticket is a Pro feature, but the fee is zero whatever you charge and whatever plan you are on.',
            ],
            [
                'q' => 'Which payment methods can I accept?',
                'a' => 'Stripe and PayPal, each paying into your own account, plus Payfast for events priced in South African rand, an Invoice Ninja invoice, a payment link of your own, or cash. They come with Pro, because that is the plan that puts a price on a ticket, and you choose one per event. Installment plans, also on Pro, run on Stripe only.',
            ],
            [
                'q' => 'Can I refund a ticket?',
                'a' => 'Yes, on Pro. From the Sales page, a Stripe or PayPal sale can be refunded in full or in part, and the status only changes once the money has gone back through the provider. A partial refund keeps the tickets valid; a full one puts them back on sale. An installment plan is refunded in full, one payment at a time. Any other method, such as cash, a payment link, Payfast or Invoice Ninja, shows Mark as Refunded instead, which records the refund without moving money.',
            ],
            [
                'q' => 'Can visitors ask to be told when tickets go on sale?',
                'a' => 'Yes, on every plan. Switch on the "Notify me" card and, from Add to Calendar on the event page, a visitor leaves just an email address, with no account, and gets one email when tickets go on sale, one if it is cancelled, a reminder shortly before it starts, and any notice you choose to send if the date or venue changes. Each date of a recurring event is separate, every email has a one-click unsubscribe, and the list does not count against your newsletter allowance. You can see how many people are waiting on the Tickets panel of the event editor.',
            ],
            [
                'q' => 'Can buyers choose their own seat?',
                'a' => 'Yes, on the Enterprise plan. Draw your room once on a venue schedule as a reusable seating plan - levels, sections, rows, tables, standing areas and wheelchair spaces - attach it to an event, and buyers pick their seats off the map. One plan covers every date of a run, and a single date can be changed on its own. Your box office gets the same map to hold seats back, take a booking over the phone, move somebody or release one seat.',
            ],
            [
                'q' => 'Can I use my own domain?',
                'a' => 'Yes. Custom domains are available on the Enterprise plan. You add one CNAME record at your registrar, and the SSL certificate is issued automatically once it resolves.',
            ],
            [
                'q' => 'Can I run it on my own server?',
                'a' => 'Yes. Event Schedule is open source under the Attribution Assurance License. Selfhosted installs include every Enterprise feature at no cost, and the app updates itself with one click from the admin panel.',
            ],
            [
                'q' => 'Do I need a credit card to start?',
                'a' => 'No. You can create a schedule and start publishing events without entering any payment details. You only add a card if you choose to upgrade to Pro or Enterprise.',
            ],
        ];
    @endphp
    <x-seo.faq-schema :items="$faqs" />
    <x-marketing.hp-faq :items="$faqs" class="hp-alt">Common <span class="hp-ink-grad">questions</span></x-marketing.hp-faq>

    <x-marketing.related-pages />

    <x-marketing.hp-finale lead="Create your free event schedule in seconds. No credit card required." placeholder="your-schedule" :foot="false">
        Ready to <span class="hp-ink-grad">get started?</span>
        <x-slot name="after">
            {{-- What the visitor lit on the board, said back to them where they sign up. --}}
            <p class="fb-yours" data-fb-yours>
                <i aria-hidden="true"></i>
                <span>
                    <span data-fb-yours-says="free">The board at the top of this page is lit for the Free plan, and that is where everyone starts.</span>
                    <span data-fb-yours-says="pro" hidden><b data-fb-yours-n></b> keys are lit on your board, which makes it a Pro board. You start on Free and move up when you need to.</span>
                    <span data-fb-yours-says="ent" hidden><b data-fb-yours-n></b> keys are lit on your board, which makes it an Enterprise board. You start on Free and move up when you need to.</span>
                </span>
            </p>
        </x-slot>
    </x-marketing.hp-finale>

    {{-- The board (plain DOM script, no inline handlers, nothing written as HTML). It reads
         what a key says from the key's own entry in the chapters below, so no sentence is in the
         page twice. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var hp = document.getElementById('hp');
            var desk = document.querySelector('[data-fb-console]');
            if (!hp || !desk) return;

            var stage = desk.querySelector('[data-fb-stage]');
            var keys = Array.prototype.slice.call(desk.querySelectorAll('[data-fb-key]'));
            var banks = Array.prototype.slice.call(desk.querySelectorAll('[data-fb-bank]'));
            var tabs = Array.prototype.slice.call(desk.querySelectorAll('[data-fb-tab]'));
            var read = desk.querySelector('[data-fb-read]');
            var tally = desk.querySelector('[data-fb-tally]');
            var find = desk.querySelector('[data-fb-find]');
            var found = desk.querySelector('[data-fb-found]');
            var live = desk.querySelector('[data-fb-live]');
            var resetBtn = desk.querySelector('[data-fb-reset]');
            var playBtn = document.querySelector('[data-fb-play]');
            var playLabel = playBtn ? playBtn.querySelector('[data-fb-play-label]') : null;
            var yours = document.querySelector('[data-fb-yours]');
            if (!stage || !read || !tally || !find || !found || !keys.length) return;

            var el = {
                bank: read.querySelector('[data-fb-read-bank]'),
                plan: read.querySelector('[data-fb-read-plan]'),
                name: read.querySelector('[data-fb-read-name]'),
                says: read.querySelector('[data-fb-read-says]'),
                note: read.querySelector('[data-fb-read-note]'),
                more: read.querySelector('[data-fb-read-more]'),
                moreLabel: read.querySelector('[data-fb-read-more-label]')
            };
            var rank = { free: 0, pro: 1, ent: 2 };
            var plans = ['free', 'pro', 'ent'];
            var planNames = { free: 'Free', pro: 'Pro', ent: 'Enterprise' };
            var prices = { free: '', pro: desk.getAttribute('data-price-pro') || '', ent: desk.getAttribute('data-price-ent') || '' };
            var motion = document.documentElement.classList.contains('es-anim');
            var fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
            var narrow = window.matchMedia('(max-width: 1099.98px)');
            var zoomed = window.matchMedia('(max-width: 639.98px)');
            var byId = {};
            var touched = false;
            var current = null;
            var playing = false;
            var timers = [];
            var first = { bank: el.bank.textContent, plan: el.plan.textContent, name: el.name.textContent, says: el.says.textContent, href: el.more.href, label: el.moreLabel.textContent };

            keys.forEach(function (key) { byId[key.getAttribute('data-fb-key')] = key; });
            {{-- From here the styles treat the board as live (one bank at a time, a sticky stage).
                 If anything below throws, they are told it is not. --}}
            hp.classList.add('fb-js');
            try {

            function each(selector, fn, root) {
                Array.prototype.forEach.call((root || document).querySelectorAll(selector), fn);
            }
            function idOf(key) { return key.getAttribute('data-fb-key'); }
            function isOn(id) { return !!byId[id] && byId[id].classList.contains('is-on'); }
            function bankOf(key) { return key.closest('[data-fb-bank]'); }
            function cardOf(id) { return document.querySelector('[data-fb-card="' + id + '"]'); }
            function planLabel(plan) { return planNames[plan] + (prices[plan] ? ', ' + prices[plan] : ''); }
            function later(fn, ms) { timers.push(window.setTimeout(fn, ms)); }

            {{-- One key on or off: the key, its parts of the scene, its entry in the chapter.
                 A key that cannot work without another brings it on, and goes off with it. --}}
            function set(id, on, fresh) {
                var key = byId[id];
                if (!key || isOn(id) === on) return;
                key.classList.toggle('is-on', on);
                key.setAttribute('aria-pressed', on ? 'true' : 'false');
                stage.classList.toggle('has-' + id, on);
                each('[data-fb-slot="' + id + '"]', function (part) {
                    part.classList.toggle('is-on', on);
                    part.classList.toggle('is-new', !!(on && fresh));
                }, stage);
                each('[data-fb-unless="' + id + '"]', function (part) { part.classList.toggle('is-off', on); }, stage);
                var card = cardOf(id);
                if (card) card.classList.toggle('is-on', on);
                var needs = key.getAttribute('data-needs');
                if (on && needs && byId[needs] && !isOn(needs)) set(needs, true, fresh);
                if (!on) {
                    keys.forEach(function (other) {
                        if (other.getAttribute('data-needs') === id && other.classList.contains('is-on')) set(idOf(other), false, false);
                    });
                }
            }

            {{-- On a phone the stage is a window onto a larger scene: move it to the part a key
                 changed. Anywhere else the scene is whole and stays put. --}}
            function aim(id) {
                var scene = stage.querySelector('.fb-scene.is-live');
                if (!scene) return;
                var x = 0;
                var y = 0;
                if (zoomed.matches && id) {
                    var part = scene.querySelector('[data-fb-slot="' + id + '"]') || scene.querySelector('[data-fb-unless="' + id + '"]');
                    if (part && part.getClientRects().length) {
                        var p = part.getBoundingClientRect();
                        var s = scene.getBoundingClientRect();
                        x = Math.min(0, Math.max(stage.clientWidth - scene.offsetWidth, stage.clientWidth / 2 - (p.left - s.left + p.width / 2)));
                        y = Math.min(0, Math.max(stage.clientHeight - scene.offsetHeight, stage.clientHeight / 2 - (p.top - s.top + p.height / 2)));
                    }
                }
                scene.style.setProperty('--px', Math.round(x) + 'px');
                scene.style.setProperty('--py', Math.round(y) + 'px');
            }

            {{-- The scene of a bank on the stage, and the bank's light on the console. --}}
            function scene(bank) {
                var id = bank.getAttribute('data-fb-bank');
                desk.style.setProperty('--k', getComputedStyle(bank).getPropertyValue('--k').trim());
                each('[data-fb-scene]', function (s) { s.classList.toggle('is-live', s.getAttribute('data-fb-scene') === id); }, stage);
                desk.querySelector('[data-fb-scene-no]').textContent = bank.getAttribute('data-no');
                desk.querySelector('[data-fb-scene-name]').textContent = bank.getAttribute('data-scene');
                banks.forEach(function (b) { b.classList.toggle('is-open', b === bank); });
                tabs.forEach(function (t) { t.setAttribute('aria-pressed', t.getAttribute('data-fb-tab') === id ? 'true' : 'false'); });
            }

            {{-- The readout, in whatever words it is given. --}}
            function say(o) {
                el.bank.textContent = o.bank;
                el.plan.hidden = !o.plan;
                if (o.plan) el.plan.textContent = planLabel(o.plan);
                el.name.textContent = o.name;
                el.says.textContent = o.text;
                el.note.textContent = o.note || '';
                el.more.hidden = !o.href;
                if (o.href) el.more.href = o.href;
                el.moreLabel.textContent = o.label || 'See how it works';
            }

            function intro() {
                say({ bank: first.bank, name: first.name, text: first.says, href: first.href, label: first.label });
                el.plan.hidden = false;
                el.plan.textContent = first.plan;
            }

            {{-- The readout for one key, in the words of its entry in the chapter. --}}
            function show(id) {
                var key = byId[id];
                var card = cardOf(id);
                if (!key || !card) return;
                var bank = bankOf(key);
                var note = card.querySelector('small');
                var link = card.querySelector('h3 a');
                current = id;
                keys.forEach(function (k) { k.classList.toggle('is-cur', k === key); });
                scene(bank);
                say({
                    bank: bank.getAttribute('data-no') + ' / ' + bank.getAttribute('data-name'),
                    plan: key.getAttribute('data-plan'),
                    name: link.textContent,
                    text: card.querySelector('p').textContent,
                    note: note ? note.textContent : '',
                    href: link.href
                });
                aim(id);
            }

            {{-- What the lit keys add up to. --}}
            function count() {
                var on = keys.filter(function (k) { return k.classList.contains('is-on'); });
                var top = 0;
                var same = true;
                on.forEach(function (k) { top = Math.max(top, rank[k.getAttribute('data-plan')]); });
                keys.forEach(function (k) { if (k.classList.contains('is-on') !== (k.getAttribute('data-plan') === 'free')) same = false; });
                var plan = plans[top];
                tally.setAttribute('data-plan', plan);
                tally.querySelector('[data-fb-n]').textContent = on.length;
                each('[data-fb-needs]', function (p) { p.hidden = p.getAttribute('data-fb-needs') !== plan; }, tally);
                if (resetBtn) resetBtn.hidden = same;
                banks.forEach(function (bank) {
                    var lit = bank.querySelectorAll('.fb-key.is-on').length;
                    bank.querySelector('[data-fb-bank-n]').textContent = lit;
                    var chapter = document.querySelector('[data-fb-ch="' + bank.getAttribute('data-fb-bank') + '"] [data-fb-ch-n]');
                    if (chapter) chapter.textContent = lit;
                });
                if (yours) {
                    var mine = touched && !same ? plan : 'free';
                    each('[data-fb-yours-says]', function (s) { s.hidden = s.getAttribute('data-fb-yours-says') !== mine; }, yours);
                    each('[data-fb-yours-n]', function (n) { n.textContent = on.length; }, yours);
                }
                {{-- The order on the checkout: two General, less the code, less the gift card. --}}
                var total = isOn('tickets') ? 48 : 0;
                if (isOn('promo')) total = total * 0.9;
                if (isOn('gift')) total = Math.max(0, total - 20);
                stage.querySelector('[data-fb-total]').textContent = total > 0 ? '$' + total.toFixed(2) : 'Free';
                stage.querySelector('[data-fb-pay]').textContent = total > 0 ? 'Pay' : 'Register';
                return { n: on.length, plan: plan };
            }

            {{-- Anything the visitor does takes the board back from whatever was running on it. --}}
            function mine() {
                touched = true;
                timers.forEach(function (t) { window.clearTimeout(t); });
                timers = [];
                playing = false;
                desk.classList.remove('is-waking');
                desk.classList.add('is-seen');
                each('.is-hint', function (k) { k.classList.remove('is-hint'); }, desk);
                if (playBtn) {
                    playBtn.setAttribute('aria-pressed', 'false');
                    playLabel.textContent = 'Play all forty';
                }
            }

            function press(id) {
                mine();
                var on = !isOn(id);
                var needs = byId[id].getAttribute('data-needs');
                var brought = on && needs && !isOn(needs);
                set(id, on, true);
                show(id);
                var sum = count();
                if (live) {
                    live.textContent = byId[id].querySelector('span').textContent + (on ? ' on' : ' off')
                        + (brought ? ', and ' + byId[needs].querySelector('span').textContent + ' with it' : '') + '. '
                        + sum.n + ' of ' + keys.length + ' on, ' + planNames[sum.plan] + ' plan.';
                }
            }

            keys.forEach(function (key) {
                var id = idOf(key);
                var dwell = null;
                key.setAttribute('role', 'button');
                key.setAttribute('aria-pressed', key.classList.contains('is-on') ? 'true' : 'false');
                {{-- One stop per bank for the Tab key; the arrows move between keys. --}}
                key.tabIndex = key === bankOf(key).querySelector('[data-fb-key]') ? 0 : -1;
                key.addEventListener('click', function (e) {
                    {{-- A key is still a link: a new tab or a new window is the visitor's to open. --}}
                    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button > 0) return;
                    e.preventDefault();
                    press(id);
                });
                key.addEventListener('keydown', function (e) {
                    if (e.key === ' ' || e.key === 'Spacebar') {
                        e.preventDefault();
                        if (!e.repeat) press(id);
                        return;
                    }
                    var bank = bankOf(key);
                    var column = Array.prototype.slice.call(bank.querySelectorAll('[data-fb-key]'));
                    var row = column.indexOf(key);
                    var next = null;
                    {{-- Below a laptop a bank is two keys wide, so down is two along. --}}
                    var wide = column.length > 1 && column[1].offsetTop === column[0].offsetTop;
                    if (e.key === 'ArrowDown') next = column[row + (wide ? 2 : 1)];
                    if (e.key === 'ArrowUp') next = column[row - (wide ? 2 : 1)];
                    if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                        var step = (e.key === 'ArrowRight') === (getComputedStyle(desk).direction !== 'rtl') ? 1 : -1;
                        if (wide || narrow.matches) {
                            next = column[row + step];
                        } else {
                            var beside = banks[banks.indexOf(bank) + step];
                            if (beside) next = beside.querySelectorAll('[data-fb-key]')[row];
                        }
                    }
                    if (next) {
                        e.preventDefault();
                        next.focus();
                    }
                });
                key.addEventListener('focus', function () {
                    each('[data-fb-key]', function (k) { k.tabIndex = k === key ? 0 : -1; }, bankOf(key));
                    if (!playing && !find.value) show(id);
                });
                if (fine) {
                    key.addEventListener('mouseenter', function () {
                        dwell = window.setTimeout(function () { if (!playing && !find.value) show(id); }, 110);
                    });
                    key.addEventListener('mouseleave', function () { window.clearTimeout(dwell); });
                }
            });

            banks.forEach(function (bank) {
                var list = bank.querySelector('ul');
                list.setAttribute('role', 'toolbar');
                list.setAttribute('aria-label', bank.getAttribute('data-name') + ': arrow keys move between keys');
                each('li', function (li) { li.setAttribute('role', 'none'); }, list);
            });

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    mine();
                    var bank = desk.querySelector('[data-fb-bank="' + tab.getAttribute('data-fb-tab') + '"]');
                    var lead = bank.querySelector('.fb-key.is-on') || bank.querySelector('.fb-key');
                    show(idOf(lead));
                });
            });

            if (resetBtn) {
                resetBtn.addEventListener('click', function () {
                    mine();
                    keys.forEach(function (k) { set(idOf(k), k.getAttribute('data-plan') === 'free', false); });
                    count();
                    if (current) { show(current); } else { intro(); }
                    if (live) live.textContent = 'Back to the Free plan.';
                });
            }

            {{-- Play all forty: the board goes dark, then comes on bank by bank, the stage cutting
                 to each bank's scene as its keys light and the tally counting up beside it. Any
                 press stops it where it stands. --}}
            if (playBtn) {
                playBtn.hidden = false;
                playBtn.setAttribute('aria-pressed', 'false');
                playBtn.addEventListener('click', function () {
                    var wasPlaying = playing;
                    mine();
                    if (wasPlaying) return;
                    var end = function () {
                        mine();
                        count();
                        keys.forEach(function (k) { k.classList.remove('is-cur'); });
                        current = null;
                        say({ bank: 'The whole product', plan: 'ent', name: 'All forty on', text: 'Every key lit. That is the Enterprise plan, or a server of your own.', href: desk.getAttribute('data-pricing'), label: 'Compare the plans' });
                    };
                    if (!motion) {
                        keys.forEach(function (k) { set(idOf(k), true, false); });
                        end();
                        return;
                    }
                    var top = desk.getBoundingClientRect().top;
                    if (top < 60 || top > window.innerHeight * 0.4) window.scrollTo({ top: window.scrollY + top - 84, behavior: 'smooth' });
                    playing = true;
                    playBtn.setAttribute('aria-pressed', 'true');
                    playLabel.textContent = 'Stop';
                    keys.forEach(function (k) { set(idOf(k), false, false); k.classList.remove('is-cur'); });
                    current = null;
                    count();
                    var at = 350;
                    banks.forEach(function (bank) {
                        var column = Array.prototype.slice.call(bank.querySelectorAll('[data-fb-key]'));
                        var chapter = document.querySelector('[data-fb-ch="' + bank.getAttribute('data-fb-bank') + '"] .fb-ch-lede');
                        later(function () {
                            scene(bank);
                            aim(null);
                            say({ bank: bank.getAttribute('data-no') + ' / ' + bank.getAttribute('data-name'), name: bank.getAttribute('data-title'), text: chapter ? chapter.textContent : '', href: '#' + bank.getAttribute('data-fb-bank'), label: 'Go to the chapter' });
                        }, at);
                        column.forEach(function (k, i) {
                            later(function () { set(idOf(k), true, true); count(); aim(idOf(k)); }, at + 300 + i * 150);
                        });
                        at += 300 + column.length * 150 + 650;
                    });
                    later(end, at);
                });
            }

            {{-- Find: a key by its name and the words beside it, or a row of the small print,
                 which has no key. A name that matches is the answer; nothing is dimmed on a miss. --}}
            var rows = [];
            each('#also-list [data-also-row]', function (row) {
                var link = row.querySelector('dt a');
                var tier = row.querySelector('dt span');
                var desc = row.querySelector('dd');
                if (link) rows.push({ name: link.textContent, href: link.href, plan: tier ? tier.getAttribute('data-plan') : 'free', desc: desc ? desc.textContent : '' });
            });
            function search() {
                var words = find.value.toLowerCase().replace(/[^a-z0-9 ]+/g, ' ').trim().split(/\s+/).filter(Boolean);
                {{-- "refunds" finds "refund": a word may lose its last s. --}}
                var has = function (text) {
                    text = text.toLowerCase();
                    return words.every(function (w) { return text.indexOf(w) !== -1 || (w.length > 3 && w.slice(-1) === 's' && text.indexOf(w.slice(0, -1)) !== -1); });
                };
                while (found.firstChild) found.removeChild(found.firstChild);
                found.hidden = true;
                if (!words.length) {
                    keys.forEach(function (k) { k.classList.remove('is-dim'); });
                    tabs.forEach(function (t) { t.classList.remove('has-hit'); });
                    if (current) { show(current); } else { intro(); }
                    return;
                }
                mine();
                var named = keys.filter(function (k) { return has(k.querySelector('span').textContent + ' ' + planNames[k.getAttribute('data-plan')]); });
                var hits = keys.filter(function (k) { return named.indexOf(k) !== -1 || has(k.getAttribute('data-find')); });
                var rowNamed = rows.filter(function (r) { return has(r.name); });
                var rowHits = rows.filter(function (r) { return rowNamed.indexOf(r) !== -1 || has(r.desc); });
                keys.forEach(function (k) { k.classList.toggle('is-dim', hits.length > 0 && hits.indexOf(k) === -1); });
                tabs.forEach(function (t) {
                    t.classList.toggle('has-hit', hits.some(function (k) { return bankOf(k).getAttribute('data-fb-bank') === t.getAttribute('data-fb-tab'); }));
                });
                var answer = null;
                var keep = current;
                if (named.length) {
                    show(idOf(named[0]));
                } else if (rowNamed.length) {
                    answer = rowNamed[0];
                } else if (hits.length) {
                    show(idOf(hits[0]));
                } else if (rowHits.length) {
                    answer = rowHits[0];
                } else {
                    keys.forEach(function (k) { k.classList.remove('is-cur'); });
                    say({ bank: 'Find', name: 'Nothing here by that name', text: 'No key and no line of the small print matches. The plan comparison lists everything, line by line.', href: desk.getAttribute('data-pricing'), label: 'See the full plan comparison' });
                }
                current = keep;
                if (answer) {
                    keys.forEach(function (k) { k.classList.toggle('is-cur', hits.indexOf(k) === 0); });
                    say({ bank: 'In the small print', plan: answer.plan, name: answer.name, text: answer.desc + '.', href: answer.href });
                }
                var also = rowHits.filter(function (r) { return r !== answer; }).slice(0, 3);
                if (also.length) {
                    found.appendChild(document.createTextNode('Also: '));
                    also.forEach(function (r, i) {
                        var a = document.createElement('a');
                        a.href = r.href;
                        a.textContent = r.name;
                        found.appendChild(a);
                        found.appendChild(document.createTextNode(i < also.length - 1 ? ', ' : '.'));
                    });
                    found.hidden = false;
                }
            }
            find.addEventListener('input', search);
            find.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter') return;
                e.preventDefault();
                var hit = desk.querySelector('.fb-key.is-cur') || (find.value ? desk.querySelector('.fb-key:not(.is-dim)') : null);
                if (find.value && hit && desk.querySelector('.fb-key.is-dim')) hit.focus();
            });
            {{-- A slash goes to the find box while the keyboard is on the board, and nowhere else:
                 a shortcut of one character must not act on a page the visitor is only reading. --}}
            desk.addEventListener('keydown', function (e) {
                if (e.key !== '/' || e.metaKey || e.ctrlKey || e.altKey || e.target === find) return;
                e.preventDefault();
                find.focus();
            });

            {{-- The first time the board is seen its lit keys come on, bank by bank, each piece of
                 the scene landing on the beat its key lights. Then the one key to press first is
                 marked. Nothing here runs again. --}}
            keys.forEach(function (key) {
                var delay = (parseInt(key.style.getPropertyValue('--i'), 10) || 0) * 70 + banks.indexOf(bankOf(key)) * 110;
                each('[data-fb-slot="' + idOf(key) + '"]', function (part) { part.style.setProperty('--at', delay + 'ms'); }, stage);
            });
            function wake() {
                desk.classList.add('is-seen', 'is-waking');
                later(function () {
                    desk.classList.remove('is-waking');
                    if (!touched && byId.tickets) byId.tickets.classList.add('is-hint');
                }, 1700);
            }
            if (motion && 'IntersectionObserver' in window) {
                {{-- As soon as any of the stage is a quarter of the way up the window: a share of
                     the console's own height can be out of reach in a short or zoomed window. --}}
                var seen = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) return;
                        seen.disconnect();
                        if (!touched) wake();
                    });
                }, { threshold: 0, rootMargin: '0px 0px -25% 0px' });
                seen.observe(stage);
            } else {
                desk.classList.add('is-seen');
                if (byId.tickets) byId.tickets.classList.add('is-hint');
            }

            {{-- One bank shows at a time below a laptop, so the stage shows the bank that is open,
                 from the start and when the window is made narrower. --}}
            function fit() {
                var header = document.querySelector('body > header');
                if (header) desk.style.setProperty('--fb-top', header.offsetHeight + 'px');
                if (narrow.matches) {
                    var open = stage.querySelector('.fb-scene.is-live');
                    var bank = current ? bankOf(byId[current]) : (touched && open ? desk.querySelector('[data-fb-bank="' + open.getAttribute('data-fb-scene') + '"]') : banks[0]);
                    scene(bank);
                }
                aim(current);
            }
            fit();
            window.addEventListener('resize', fit);

            {{-- The small print on a phone: eight rows, and the rest behind one button. --}}
            var alsoList = document.getElementById('also-list');
            var alsoMore = document.querySelector('[data-fb-also-more]');
            if (alsoList && alsoMore) {
                alsoMore.addEventListener('click', function () {
                    alsoList.classList.remove('is-short');
                    each('[data-also-row]', function (row) { row.classList.add('is-revealed'); }, alsoList);
                });
            }

            count();
            } catch (error) {
                hp.classList.remove('fb-js');
                throw error;
            }
        })();
    </script>

    {{-- Plan filter over the small print (plain DOM script, no inline handlers) --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var bar = document.getElementById('also-filter');
            var list = document.getElementById('also-list');
            if (!bar || !list) return;
            var pills = bar.querySelectorAll('[data-also-tier]');
            var rows = list.querySelectorAll('[data-also-row]');
            var above = document.getElementById('also-above');
            var aboveLines = above ? above.querySelectorAll('[data-also-above]') : [];
            bar.hidden = false;
            bar.addEventListener('click', function (e) {
                var pill = e.target.closest ? e.target.closest('[data-also-tier]') : null;
                if (!pill) return;
                var tier = pill.getAttribute('data-also-tier');
                pills.forEach(function (p) { p.setAttribute('aria-pressed', p === pill ? 'true' : 'false'); });
                {{-- A plan asked for is shown whole, on a phone too. --}}
                list.classList.remove('is-short');
                rows.forEach(function (row) {
                    row.hidden = tier !== '' && row.getAttribute('data-also-row') !== tier;
                    {{-- A row the scroll has not reached yet would otherwise appear empty and fade
                         in later, in the middle of a list the reader just asked for. --}}
                    row.classList.add('is-revealed');
                });
                if (above) {
                    above.hidden = tier === '';
                    aboveLines.forEach(function (line) { line.hidden = line.getAttribute('data-also-above') !== tier; });
                }
            });
        })();
    </script>

    {{-- Motion engines (the finale brings its own confetti) --}}
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
