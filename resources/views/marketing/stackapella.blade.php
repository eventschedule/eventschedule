<x-marketing-layout :hp="true">
    <x-slot name="title">Stackapella and Event Schedule | Voice Lessons and Choirs</x-slot>
    <x-slot name="description">Event Schedule keeps the lessons, rehearsals and concerts. Stackapella is where singers learn their part between them. How voice teachers and choirs use both.</x-slot>
    <x-slot name="breadcrumbTitle">Stackapella</x-slot>

    {{-- Claims on this page, checked against both codebases on 2026-11-08:
         - WHAT WORKS TODAY, in Event Schedule (docs/FEATURES.md): appointment types with weekly
           hours, a public booking page, a private link to reschedule or cancel, a calendar invite
           and a reminder about a day before; one FREE type on the free plan, more types and a price
           on a lesson on Pro, paid by any connected gateway but Invoice Ninja, no platform fee; on
           hosted the appointment mail goes out through the schedule's own email settings. Recurring
           events with skipped and added dates; tickets need Pro, free registration does not.
         - WHAT WORKS TODAY, in Stackapella (server/lang/en/site.php, live on stackapella.com):
           choir groups are free, a director shares songs, gives each singer a part and sees who
           has started or recorded it, never their recordings or contact details, 18 and over;
           one song of part-by-part learning tracks free on request; the warm-up builder sends a
           warm-up by link that opens in each student's range, no account needed to make one;
           the courses are free (Vibrato, Range and registers, Breath and stamina, Perfect your
           pitch); coaching is "coming soon" there and coaches can write in.
         - THE CONNECTION IS NOT BUILT: stackapella docs/event-schedule-integration-plan.md (G1 to
           G5). Stackapella's side is built against a fake; Event Schedule's side (sign-in and
           approval with scopes, appointment webhooks, a bookings API, a members endpoint,
           Stackapella players on event pages) is not started. Everything about the connection is
           written in the future tense and sits under a "Being built" label, and is DRAWN dashed
           (the page's one visual rule: solid is today, dashed is being built). When it ships, this
           page's future tense, the "Being built" labels, the dashed cards (the ghost Thursday in
           the week, the two "When the two are connected" panels, the four cards of "The
           connection") and the FAQ "When does the connection open?" are the things to change,
           and so is the Partner apps card on /features/integrations.
         - The connection's plan rule is an open decision there, so no plan is named for it.
         - Same maker: stackapella.com/about names the maker of Invoice Ninja and Event Schedule.
         The one price is plan_price($proMonthly). --}}

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule and Stackapella"
        description="Event Schedule keeps a voice teacher's lessons and a choir's rehearsals and concerts. Stackapella is where singers learn their part between them, with a guide voice and feedback on every note, and the two are being connected."
        audience="Voice teachers, vocal coaches, choir directors and choir singers"
        keywords="Stackapella, voice lesson booking, choir rehearsal schedule, choir learning tracks, vocal coach scheduling, choir management" />
    </x-slot>

    @php
        $saHome = 'https://stackapella.com/?ref=eventschedule';
        $saUrl = fn (string $path) => 'https://stackapella.com'.$path;

        // The four parts. Each lane carries the colour it is drawn in and how long its bar is,
        // after the four bars of Stackapella's mark. The colours are the mark's first three and
        // a light Event Schedule blue for the bass, and they are never text: the label on a
        // lit bar is the page's near-black, on an unlit one near-white.
        $saParts = [
            ['soprano', 'Soprano', '#ff9139', '100%', 'the tune'],
            ['alto', 'Alto', '#ffea6b', '74%', 'under the tune'],
            ['tenor', 'Tenor', '#45eca4', '88%', 'above the bass'],
            ['bass', 'Bass', '#7da5ff', '62%', 'the floor of the chord'],
        ];

        // How far each part has to go, so the week reads differently for each choice.
        $saLeft = ['soprano' => 2, 'alto' => 3, 'tenor' => 2, 'bass' => 4];
        $saTidy = ['soprano' => 3, 'alto' => 5, 'tenor' => 4, 'bass' => 6];

        // The week, laid out as a two-line score: row 1 is Event Schedule (the dates), row 2 is
        // Stackapella (the practice), and the column is the day. 'es' is an Event Schedule entry,
        // 'sa' a Stackapella one, 'next' the one thing the connection will add (drawn dashed,
        // labelled Being built). A %s in a line is the part's name, and a {left} or {tidy} its
        // count. 'fill' is how much of the part a singer of each voice knows that day, the bar
        // at the foot of the card, in the part's colour.
        $saWeek = [
            ['day' => 'Mon', 'app' => 'sa', 'col' => 1, 'title' => 'Learn your part', 'body' => 'The %s of Danny Boy, a phrase at a time after the guide voice. {left} phrases to go.', 'fill' => ['soprano' => 60, 'alto' => 45, 'tenor' => 60, 'bass' => 30]],
            ['day' => 'Tue', 'app' => 'es', 'col' => 2, 'title' => 'Voice lesson, 5:30 PM', 'body' => 'Booked on your teacher\'s page, in an hour they keep open. The reminder came yesterday.'],
            ['day' => 'Wed', 'app' => 'sa', 'col' => 3, 'title' => 'Hold it with the others', 'body' => 'The %s on your own while the other three parts play. {tidy} notes to tidy.', 'fill' => ['soprano' => 85, 'alto' => 75, 'tenor' => 80, 'bass' => 65]],
            ['day' => 'Thu', 'app' => 'es', 'col' => 4, 'title' => 'Rehearsal, 7:30 PM', 'body' => 'Every Thursday on the choir\'s schedule. You walk in knowing the %s.', 'fill' => ['soprano' => 100, 'alto' => 100, 'tenor' => 100, 'bass' => 100]],
            ['day' => 'Thu', 'app' => 'next', 'col' => 4, 'title' => 'Thursday, in your group', 'body' => 'Once the two are connected, the rehearsal will show in your Stackapella group too, with the %s to practise.'],
            ['day' => 'Sat', 'app' => 'es', 'col' => 5, 'title' => 'Spring concert, 7 PM', 'body' => 'Tickets on the choir\'s page, scanned at the door. Danny Boy and Amazing Grace.'],
        ];

        $saFaqs = [
            [
                'q' => 'What is Stackapella?',
                'a' => 'A web app for singing harmony. A singer learns a part by ear from a guide voice, sees which notes landed as they sing, and can record every part of a song in turn, lined up exactly in time. It has a free course from a first note to four-part harmony, a warm-up builder, and choir groups where a director shares songs and gives each singer a part. It comes from the maker of Event Schedule and Invoice Ninja.',
            ],
            [
                'q' => 'Can I use the two together today?',
                'a' => 'Yes, side by side. Your lessons, rehearsals and concerts go on your Event Schedule schedule, where students book a lesson and the choir sees every date. Your singers practise in Stackapella between them, in a choir group you started or from a warm-up you sent by link, and you can put those links in an event\'s description so they sit beside the rehearsal they are for.',
            ],
            [
                'q' => 'When does the connection open?',
                'a' => 'It is being built now, and this page will say so the day it opens. Until then nothing on Event Schedule changes: lessons are booked on your own booking page and rehearsals live on your own schedule, which is also where the connection will read them from.',
            ],
            [
                'q' => 'Who is paid for a lesson booked through Stackapella?',
                'a' => 'You will be, through Stackapella\'s checkout, and the booking will reach Event Schedule already marked as paid there, so it is never charged twice or invoiced again through an Invoice Ninja connection. A lesson booked on your Event Schedule page is paid as it is today, into the payment account you connected, with no platform fee.',
            ],
            [
                'q' => 'Do my singers need an Event Schedule account?',
                'a' => 'No. Your schedule page is public, so anyone can see the rehearsal and concert dates, and a student books a lesson with a name and an email address, with everything after that run from a private link in their email. Stackapella has accounts of its own for the singers who practise there.',
            ],
            [
                'q' => 'Which plans do I need?',
                'a' => 'On Event Schedule\'s free plan you can run a choir\'s whole season of events and offer one free lesson type. A price on a lesson, more lesson types, or tickets with a price need Pro at '.plan_price($proMonthly).' a month, with no platform fee on any of them. On Stackapella, choir groups, the course and the warm-up builder are free. The plan the connection itself needs will be on this page when it opens.',
            ],
            [
                'q' => 'Can I selfhost either one?',
                'a' => 'Both. Event Schedule and Stackapella are open source and each can run on your own server. A selfhosted Event Schedule will connect by an API key and the address of your install, pasted into Stackapella\'s settings, because there is no sign-in to approve on a server we do not run.',
            ],
        ];
    @endphp

    {{-- Motion gate: the hidden pre-reveal states below only apply when this class is present, so
         no-JS visitors, crawlers and reduced-motion users always see the whole page. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           Stackapella: "Four parts, two lines".

           CONCEPT: Stackapella's mark is four stacked bars, one for each
           voice of a choir. The page turns that mark into its set piece:
           the four bars are the buttons that pick YOUR part, and under
           them the week is written as a two-line score. The top line is
           Event Schedule (the dates: a lesson, a rehearsal, a concert),
           the bottom line is Stackapella (the practice), and the part
           you picked is drawn along the practice line in its own colour,
           filling a little more each day until Thursday's rehearsal.

           ONE VISUAL RULE, used everywhere on the page: what works today
           is drawn solid; what the connection will do is drawn dashed,
           on a dotted "drafting" ground, and labelled Being built. A
           visitor who reads nothing still sees which is which.

           Written on the house style kit (partials/hp-kit). The picker
           needs no script: four radios are siblings BEFORE the lanes and
           the score, so a plain ~ selector switches them in every
           browser. A browser that cannot (none we serve) shows the
           soprano's week.

           The set sits on a panel of its own (#050814 at 88%), so its
           contrast holds over the night band's bright dark-mode glow.
           MEASURED on that panel:
             lanes lit: #0a1020 on #ff9139 8.6, on #ffea6b 15.1, on
                        #45eca4 11.7, on #7da5ff 7.6
             lanes unlit: #eef2ff on each colour at 26% over #050814,
                        6.4 to 9.9; hovered at 38%, 5.8 to 8.4
             cards: #c5cde2 on #0c1428 11.4, on #000 13.1
             SA side of the duets: #d0d0d0 on #000 13.8, #ffb57a 11.6
           ============================================================== */

        /* ---- Small shared pieces ---- */
        .es-sa-tag {
            display: inline-flex;
            flex: none;
            align-items: center;
            gap: 0.4rem;
            border-radius: 9999px;
            padding: 0.15rem 0.6rem 0.15rem 0.45rem;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }
        .es-sa-tag svg { width: 0.85rem; height: 0.85rem; flex: none; }
        .es-sa-tag.is-es { background-color: rgba(125, 165, 255, 0.16); color: #c9d8ff; }
        .es-sa-tag.is-sa { background-color: rgba(255, 145, 57, 0.16); color: #ffd2ad; }

        /* Today / being built, on the page's own grounds. */
        .es-sa-state {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border-radius: 9999px;
            padding: 0.125rem 0.625rem;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .es-sa-state.is-now { background-color: #d1fae5; color: #065f46; }
        .dark .es-sa-state.is-now { background-color: rgba(110, 231, 183, 0.14); color: #6ee7b7; }
        .es-sa-state.is-next { background-color: #fef3c7; color: #92400e; border: 1px dashed #d97706; }
        .dark .es-sa-state.is-next { background-color: #2a2312; color: #fcd34d; border-color: rgba(252, 211, 77, 0.6); }
        /* On grounds that are dark in both modes. */
        .es-sa-on-night .es-sa-state.is-now { background-color: rgba(110, 231, 183, 0.14); color: #6ee7b7; }
        .es-sa-on-night .es-sa-state.is-next { background-color: #2a2312; color: #fcd34d; border-color: rgba(252, 211, 77, 0.6); }

        .es-sa-mark { width: 1.9rem; height: 1.9rem; flex: none; }
        .es-sa-es-tile {
            display: inline-flex;
            flex: none;
            align-items: center;
            justify-content: center;
            width: 1.9rem;
            height: 1.9rem;
            border-radius: 0.55rem;
            background: linear-gradient(135deg, #2f66ea, #0b8fd8);
            color: #ffffff;
        }
        .es-sa-es-tile svg { width: 1.05rem; height: 1.05rem; }

        /* Dashed is "being built", everywhere on the page. */
        #hp {
            --es-sa-pencil: rgba(10, 16, 32, 0.32);
            --es-sa-dot: rgba(10, 16, 32, 0.08);
        }
        .dark #hp {
            --es-sa-pencil: rgba(255, 255, 255, 0.3);
            --es-sa-dot: rgba(255, 255, 255, 0.07);
        }
        .es-sa-draft {
            border: 1.5px dashed var(--es-sa-pencil);
            background-color: var(--hp-bg);
            background-image: radial-gradient(var(--es-sa-dot) 1.1px, transparent 1.4px);
            background-size: 18px 18px;
            box-shadow: none;
        }
        .hp-alt .es-sa-draft { background-color: var(--hp-bg-2); }
        .dark .hp-alt .es-sa-draft { background-color: var(--hp-bg-3); }

        /* ---- The set piece ---- */
        .es-sa-set {
            position: relative;
            min-width: 0;
            margin: 3rem 0 0;
            padding: clamp(1.25rem, 3.2vw, 2.75rem);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            background-color: rgba(5, 8, 20, 0.88);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 40px 90px -40px rgba(0, 0, 0, 0.9);
        }
        .es-sa-hint {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            margin-bottom: 1.1rem;
            font-family: var(--hp-mono);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #a9b8db;
        }
        .es-sa-hint svg { width: 1rem; height: 1rem; }
        .es-sa-lanes {
            display: grid;
            gap: 0.7rem;
            max-width: 46rem;
            margin-inline: auto;
        }
        .es-sa-lane {
            --lane: #ff9139;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            width: var(--len, 100%);
            min-width: min(100%, 15rem);
            min-height: 3.25rem;
            padding: 0.55rem 1rem 0.55rem 1.1rem;
            border-radius: 9999px;
            background-color: color-mix(in srgb, var(--lane) 26%, #050814);
            color: #eef2ff;
            cursor: pointer;
            transition: background-color 0.25s ease, color 0.25s ease, transform 0.25s ease, box-shadow 0.25s ease;
            user-select: none;
        }
        .es-sa-lane:hover { transform: translateX(4px); background-color: color-mix(in srgb, var(--lane) 38%, #050814); }
        [dir="rtl"] .es-sa-lane:hover { transform: translateX(-4px); }
        .es-sa-lane-ring {
            display: inline-flex;
            flex: none;
            align-items: center;
            justify-content: center;
            width: 1.2rem;
            height: 1.2rem;
            border: 2px solid currentColor;
            border-radius: 9999px;
            opacity: 0.85;
        }
        .es-sa-lane-ring::after {
            content: "";
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            background-color: currentColor;
            transform: scale(0);
            transition: transform 0.2s ease;
        }
        .es-sa-lane-text { display: flex; min-width: 0; flex: 1 1 auto; align-items: baseline; gap: 0.75rem; }
        .es-sa-lane-name { font-size: 1.05rem; font-weight: 700; font-variation-settings: 'wght' 760; letter-spacing: -0.01em; }
        .es-sa-lane-sub { display: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.8125rem; opacity: 0.85; }
        @media (min-width: 640px) { .es-sa-lane-sub { display: inline; } }
        .es-sa-lane-you {
            display: none;
            flex: none;
            border-radius: 9999px;
            padding: 0.1rem 0.55rem;
            background-color: rgba(10, 16, 32, 0.14);
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        /* The score. Below a wide laptop it is one column with the part's
           thread running down the start side; from 1180px it is two
           staves, the dates above and the practice below. */
        .es-sa-score { --part: #ff9139; position: relative; margin-top: clamp(2.25rem, 4.5vw, 3.25rem); }
        .es-sa-staff { display: none; }
        .es-sa-week {
            position: relative;
            display: grid;
            gap: 0.85rem;
            max-width: 40rem;
            margin-inline: auto;
            padding-inline-start: 1.75rem;
        }
        .es-sa-week::before {
            content: "";
            position: absolute;
            inset-block: 1.4rem 1.4rem;
            inset-inline-start: 0.5rem;
            width: 3px;
            border-radius: 3px;
            background: linear-gradient(var(--part), color-mix(in srgb, var(--part) 25%, transparent));
            transition: background 0.3s ease;
        }
        .es-sa-day {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
            min-width: 0;
            padding: 1.1rem 1.15rem 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 1.1rem;
            background-color: #0c1428;
        }
        .es-sa-day::before {
            content: "";
            position: absolute;
            top: 1.25rem;
            inset-inline-start: calc(-1.75rem + 0.5rem - 0.33rem + 1.5px);
            width: 0.8rem;
            height: 0.8rem;
            border: 2px solid #7da5ff;
            border-radius: 9999px;
            background-color: #050814;
        }
        .es-sa-day.is-sa { border-color: color-mix(in srgb, var(--part) 55%, transparent); background-color: #000000; }
        .es-sa-day.is-sa::before { border-color: var(--part); background-color: var(--part); }
        .es-sa-day.is-next {
            border: 1.5px dashed rgba(252, 211, 77, 0.55);
            background-color: rgba(5, 8, 20, 0.92);
            background-image: radial-gradient(rgba(255, 255, 255, 0.07) 1.1px, transparent 1.4px);
            background-size: 16px 16px;
        }
        .es-sa-day.is-next::before { border-style: dashed; border-color: #fcd34d; }
        .es-sa-day-top { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; }
        .es-sa-day-name { font-family: var(--hp-mono); font-size: 0.75rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: #a9b8db; }
        .es-sa-day-title { font-size: 1.05rem; font-weight: 700; font-variation-settings: 'wght' 720; line-height: 1.3; color: #ffffff; text-wrap: balance; }
        .es-sa-day-body { font-size: 0.875rem; line-height: 1.55; color: #c5cde2; }
        .es-sa-day-dot { display: inline-block; width: 0.55rem; height: 0.55rem; border-radius: 9999px; background-color: var(--part); vertical-align: 0.05em; margin-inline-end: 0.35rem; }
        .es-sa-meter { display: block; margin-top: auto; padding-top: 0.4rem; }
        .es-sa-meter > span { display: block; height: 0.35rem; overflow: hidden; border-radius: 9999px; background-color: rgba(255, 255, 255, 0.1); }
        .es-sa-meter i {
            display: block;
            height: 100%;
            width: var(--fill-soprano);
            border-radius: inherit;
            background-color: var(--part);
            transform-origin: 0 50%;
            transition: width 0.45s cubic-bezier(0.2, 0.7, 0.2, 1);
        }
        [dir="rtl"] .es-sa-meter i { transform-origin: 100% 50%; }

        @media (min-width: 1180px) {
            .es-sa-set { padding-inline: 2rem; }
            .es-sa-score {
                display: grid;
                grid-template-columns: 8.25rem repeat(5, minmax(0, 1fr));
                grid-template-rows: auto auto;
                gap: 1.1rem 0.8rem;
            }
            /* The two staves: a line for each row, drawn behind the cards. */
            .es-sa-score::before,
            .es-sa-score::after {
                content: "";
                grid-column: 1 / -1;
                align-self: center;
                height: 2px;
                border-radius: 2px;
            }
            .es-sa-score::before { grid-row: 1; background: linear-gradient(90deg, rgba(125, 165, 255, 0.8), rgba(125, 165, 255, 0.2)); }
            .es-sa-score::after { grid-row: 2; height: 3px; background: linear-gradient(90deg, var(--part), color-mix(in srgb, var(--part) 20%, transparent)); }
            [dir="rtl"] .es-sa-score::before { background: linear-gradient(-90deg, rgba(125, 165, 255, 0.8), rgba(125, 165, 255, 0.2)); }
            [dir="rtl"] .es-sa-score::after { background: linear-gradient(-90deg, var(--part), color-mix(in srgb, var(--part) 20%, transparent)); }
            .es-sa-staff {
                position: relative;
                z-index: 1;
                display: flex;
                flex-direction: column;
                gap: 0.45rem;
                grid-column: 1;
                align-self: center;
                padding: 0.75rem 0.8rem;
                border: 1px solid rgba(255, 255, 255, 0.12);
                border-radius: 0.95rem;
                background-color: #0a1020;
            }
            .es-sa-staff.is-es { grid-row: 1; }
            .es-sa-staff.is-sa { grid-row: 2; border-color: color-mix(in srgb, var(--part) 50%, transparent); }
            .es-sa-staff-name { display: flex; align-items: center; gap: 0.45rem; font-size: 0.8125rem; font-weight: 700; font-variation-settings: 'wght' 760; line-height: 1.2; color: #ffffff; }
            .es-sa-staff-name .es-sa-es-tile,
            .es-sa-staff-name .es-sa-mark { width: 1.4rem; height: 1.4rem; border-radius: 0.4rem; }
            .es-sa-staff-name .es-sa-es-tile svg { width: 0.8rem; height: 0.8rem; }
            .es-sa-staff-sub { font-family: var(--hp-mono); font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #a9b8db; }

            .es-sa-week {
                z-index: 1;
                grid-column: 2 / -1;
                grid-row: 1 / span 2;
                grid-template-columns: subgrid;
                grid-template-rows: subgrid;
                gap: normal;
                max-width: none;
                margin: 0;
                padding: 0;
            }
            .es-sa-week::before,
            .es-sa-day::before { display: none; }
            .es-sa-day { grid-column: var(--col); grid-row: 1; }
            .es-sa-day.is-sa,
            .es-sa-day.is-next { grid-row: 2; }
        }

        /* One part's words at a time. The soprano's show until a radio says otherwise. */
        .es-sa-set [data-part] { display: none; }
        .es-sa-set [data-part="soprano"] { display: inline; }

        @foreach ($saParts as [$partKey, $partName, $partColour])
            #es-sa-part-{{ $partKey }}:checked ~ .es-sa-score { --part: {{ $partColour }}; }
            #es-sa-part-{{ $partKey }}:checked ~ .es-sa-score [data-part] { display: none; }
            #es-sa-part-{{ $partKey }}:checked ~ .es-sa-score [data-part="{{ $partKey }}"] { display: inline; }
            #es-sa-part-{{ $partKey }}:checked ~ .es-sa-score .es-sa-meter i { width: var(--fill-{{ $partKey }}); }
            html.es-anim .es-sa-set.is-revealed #es-sa-part-{{ $partKey }}:checked ~ .es-sa-score .es-sa-meter i { animation: es-sa-fill-{{ $partKey }} 0.9s cubic-bezier(0.2, 0.7, 0.2, 1) both; animation-delay: var(--delay, 0s); }
            html.es-anim #es-sa-part-{{ $partKey }}:checked ~ .es-sa-score [data-part="{{ $partKey }}"] { animation: es-sa-in-{{ $partKey }} 0.45s ease both; }
            #es-sa-part-{{ $partKey }}:checked ~ .es-sa-lanes .es-sa-lane[for="es-sa-part-{{ $partKey }}"] {
                background-color: var(--lane);
                color: #0a1020;
                box-shadow: 0 0 0 4px color-mix(in srgb, var(--lane) 22%, transparent), 0 18px 40px -18px var(--lane);
            }
            #es-sa-part-{{ $partKey }}:checked ~ .es-sa-lanes .es-sa-lane[for="es-sa-part-{{ $partKey }}"] .es-sa-lane-you { display: inline-flex; margin-inline-start: auto; }
            #es-sa-part-{{ $partKey }}:checked ~ .es-sa-lanes .es-sa-lane[for="es-sa-part-{{ $partKey }}"] .es-sa-lane-sub { opacity: 1; }
            #es-sa-part-{{ $partKey }}:checked ~ .es-sa-lanes .es-sa-lane[for="es-sa-part-{{ $partKey }}"] .es-sa-lane-ring { opacity: 1; }
            #es-sa-part-{{ $partKey }}:checked ~ .es-sa-lanes .es-sa-lane[for="es-sa-part-{{ $partKey }}"] .es-sa-lane-ring::after { transform: scale(1); }
            #es-sa-part-{{ $partKey }}:focus-visible ~ .es-sa-lanes .es-sa-lane[for="es-sa-part-{{ $partKey }}"] {
                outline: 3px solid #ffffff;
                outline-offset: 3px;
            }
            {{-- One keyframe name per part, so choosing another part changes the animation's
                 name and the browser plays it again: the bars refill and the words come in. --}}
            @keyframes es-sa-fill-{{ $partKey }} { from { transform: scaleX(0); } to { transform: scaleX(1); } }
            @keyframes es-sa-in-{{ $partKey }} { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }
        @endforeach

        .es-sa-legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.6rem 1.5rem;
            margin-top: 1.75rem;
            font-size: 0.8125rem;
            color: #c5cde2;
        }
        .es-sa-legend li { display: inline-flex; align-items: center; gap: 0.55rem; }
        .es-sa-legend i { display: inline-block; width: 1.6rem; height: 0.95rem; border-radius: 0.3rem; }
        .es-sa-legend i.is-solid { border: 1px solid rgba(255, 255, 255, 0.35); background-color: #0c1428; }
        .es-sa-legend i.is-dashed { border: 1.5px dashed rgba(252, 211, 77, 0.7); }

        .es-sa-cta { display: flex; flex-direction: column; align-items: stretch; justify-content: center; gap: 0.75rem; max-width: 24rem; margin: 2.5rem auto 0; }
        @media (min-width: 640px) {
            .es-sa-cta { flex-direction: row; align-items: center; max-width: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-sa-lane, .es-sa-lane:hover { transition: none; transform: none; }
            .es-sa-meter i, .es-sa-lane-ring::after { transition: none; }
        }

        /* ---- The duets: the two apps, side by side, as one object ---- */
        .es-sa-duet {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            margin-top: 3rem;
            overflow: hidden;
            border: 1px solid var(--hp-line);
            border-radius: 1.9rem;
            box-shadow: var(--hp-card-shadow);
        }
        @media (min-width: 1024px) {
            .es-sa-duet { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .es-sa-half { display: flex; flex-direction: column; padding: clamp(1.5rem, 3.2vw, 2.75rem); }
        .es-sa-half.is-es { background-color: var(--hp-bg-2); }
        .hp-alt .es-sa-half.is-es { background-color: var(--hp-bg); }
        .es-sa-half.is-sa { background-color: #000000; color: #f5f5f5; }
        .es-sa-half-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.7rem; }
        .es-sa-half-head h3 { font-size: 1.25rem; font-weight: 700; font-variation-settings: 'wght' 780; letter-spacing: -0.02em; }
        .es-sa-half-say { margin-top: 0.85rem; font-size: 1.05rem; line-height: 1.5; color: var(--hp-ink-2); }
        .es-sa-half.is-sa .es-sa-half-say { color: #d0d0d0; }
        #hp .es-sa-half .hp-checks { margin-top: 1.5rem; }
        .es-sa-half.is-sa .hp-checks li { color: #d0d0d0; }
        .es-sa-half.is-sa .hp-ok { background-color: rgba(255, 145, 57, 0.2); color: #ffb57a; }
        .es-sa-foot { margin-top: auto; padding-top: 1.75rem; font-size: 0.875rem; line-height: 1.55; color: var(--hp-ink-3); }
        .es-sa-half.is-sa .es-sa-foot { color: #bdbdbd; }
        #hp .es-sa-half.is-sa a { color: #ffb57a; }
        #hp .es-sa-half.is-sa a:focus-visible { outline-color: #ffb57a; }
        .es-sa-links { display: flex; flex-wrap: wrap; gap: 0.5rem 1.25rem; }
        .es-sa-half.is-sa .es-sa-state.is-now { background-color: rgba(110, 231, 183, 0.16); color: #6ee7b7; }

        /* The bridge under a duet: what the connection will add, dashed,
           hung from the duet by a dashed wire with its label on it. */
        .es-sa-bridge {
            position: relative;
            margin-top: 3.25rem;
            padding: clamp(2.25rem, 4vw, 3rem) clamp(1.5rem, 3.2vw, 2.75rem) clamp(1.5rem, 3vw, 2.25rem);
            border-radius: 1.9rem;
        }
        .es-sa-bridge::before {
            content: "";
            position: absolute;
            bottom: 100%;
            left: 50%;
            height: 3.25rem;
            border-left: 1.5px dashed var(--es-sa-pencil);
        }
        .es-sa-bridge-tab { position: absolute; top: 0; left: 50%; transform: translate(-50%, -50%); }
        .es-sa-bridge-head { max-width: 40rem; margin-inline: auto; text-align: center; }
        .es-sa-bridge-head h3 { font-size: clamp(1.3rem, 0.6vw + 1.1rem, 1.6rem); font-weight: 700; font-variation-settings: 'wght' 790; letter-spacing: -0.025em; }
        .es-sa-bridge-head p { margin-top: 0.5rem; font-size: 0.95rem; color: var(--hp-ink-2); }
        .es-sa-steps { display: grid; gap: 1.5rem; margin-top: 2rem; }
        @media (min-width: 768px) {
            .es-sa-steps.is-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .es-sa-steps.is-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (min-width: 1280px) {
            .es-sa-steps.is-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
        .es-sa-step { display: flex; gap: 0.9rem; }
        .es-sa-step h4 { font-size: 1rem; font-weight: 700; font-variation-settings: 'wght' 740; color: var(--hp-ink); }
        .es-sa-step p { margin-top: 0.3rem; font-size: 0.875rem; line-height: 1.6; color: var(--hp-ink-2); }
        .es-sa-num {
            display: inline-flex;
            flex: none;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border: 1.5px dashed var(--hp-blue);
            border-radius: 9999px;
            background-color: var(--hp-bg-2);
            font-family: var(--hp-mono);
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--hp-blue);
        }
        .es-sa-bridge-foot { margin-top: 2rem; padding-top: 1.25rem; border-top: 1px dashed var(--es-sa-pencil); font-size: 0.875rem; color: var(--hp-ink-2); text-align: center; }

        /* ---- The connection ---- */
        @media (min-width: 1024px) {
            .es-sa-split { grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.15fr); }
        }
        .es-sa-plan { display: flex; flex-direction: column; padding: 1.5rem; border-radius: 1.25rem; }
        .es-sa-plan h3 { display: flex; align-items: center; gap: 0.65rem; font-size: 1rem; font-weight: 700; font-variation-settings: 'wght' 760; color: var(--hp-ink); }
        .es-sa-plan p { margin-top: 0.5rem; font-size: 0.875rem; line-height: 1.6; color: var(--hp-ink-2); }
        .es-sa-plan .es-sa-num { width: 1.9rem; height: 1.9rem; font-size: 0.8rem; }
        .es-sa-flow { display: flex; flex-direction: column; align-items: flex-start; gap: 0.5rem; margin-top: 1.75rem; }
        @media (min-width: 640px) {
            .es-sa-flow { flex-direction: row; align-items: center; gap: 0.6rem; }
        }
        .es-sa-flow-node { display: inline-flex; flex: none; align-items: center; white-space: nowrap; gap: 0.5rem; padding: 0.45rem 0.8rem 0.45rem 0.5rem; border: 1px solid var(--hp-line-2); border-radius: 9999px; background-color: var(--hp-bg-2); font-size: 0.875rem; font-weight: 700; font-variation-settings: 'wght' 700; color: var(--hp-ink); }
        .es-sa-flow-node .es-sa-es-tile,
        .es-sa-flow-node .es-sa-mark { width: 1.5rem; height: 1.5rem; border-radius: 0.45rem; }
        .es-sa-flow-node .es-sa-es-tile svg { width: 0.85rem; height: 0.85rem; }
        .es-sa-flow-wire { height: 1rem; margin-inline-start: 1.25rem; border-inline-start: 1.5px dashed var(--es-sa-pencil); }
        @media (min-width: 640px) {
            .es-sa-flow-wire { flex: 1 1 2.5rem; min-width: 0.75rem; max-width: 3rem; height: 0; margin: 0; border-inline-start: 0; border-top: 1.5px dashed var(--es-sa-pencil); }
        }

        /* ---- About Stackapella: its own black tile, as the mark sits on one ---- */
        @media (min-width: 1024px) {
            .es-sa-about { grid-template-columns: minmax(0, 1.35fr) minmax(0, 0.65fr); }
        }
        .es-sa-card-sa { background-color: #000000; color: #f5f5f5; border-color: rgba(255, 255, 255, 0.1); }
        .es-sa-card-sa .es-sa-card-muted { color: #c4c4c4; }
        #hp .es-sa-card-sa:focus-visible { outline-color: #ffb57a; }
        .es-sa-card-muted { color: var(--hp-ink-2); }
        .es-sa-card-link { color: var(--hp-blue); }
        .es-sa-poster { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem; }
        @media (min-width: 768px) {
            .es-sa-poster { grid-template-columns: minmax(0, 1fr) 11rem; align-items: center; }
        }
        .es-sa-poster-copy { display: flex; flex-direction: column; }
        /* The mark, drawn large beside the copy. On a phone the logo above the
           title already says it, so the bars are left out there. */
        .es-sa-poster-bars { display: none; }
        @media (min-width: 768px) { .es-sa-poster-bars { display: grid; gap: 0.9rem; max-width: 11rem; } }
        .es-sa-poster-bars i { display: block; width: var(--len); height: 1.35rem; border-radius: 9999px; background-color: var(--lane); }
        .es-sa-sa-btn {
            display: inline-flex;
            align-self: flex-start;
            align-items: center;
            gap: 0.55rem;
            min-height: 3.1rem;
            margin-top: auto;
            padding: 0 1.3rem;
            border-radius: 0.9rem;
            background-color: #ff9139;
            color: #0a1020;
            font-weight: 700;
            font-variation-settings: 'wght' 720;
            transition: background-color 0.2s ease, gap 0.2s ease;
        }
        .es-sa-sa-btn svg { width: 1.05rem; height: 1.05rem; }
        .es-sa-poster:hover .es-sa-sa-btn { background-color: #ffa95f; gap: 0.8rem; }
    </style>

    {{-- ============================================================
         1. Hero
         ============================================================ --}}
    <section id="top" class="es-hero hp-hero">
        <div class="hp-hero-sky" aria-hidden="true"></div>

        <div class="hp-hero-copy">
            <h1 class="hp-h1">
                <x-marketing.hero-eyebrow class="es-fade-up es-d-1 hp-eyebrow">
                    @include('marketing.partials.integration-logo', ['name' => 'stackapella', 'class' => 'h-5 w-5 shrink-0'])
                    Event Schedule and Stackapella, for singers
                </x-marketing.hero-eyebrow>
                <span class="es-mask"><span class="es-mask-line">Book the lesson.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="hp-ink-grad">Learn the part.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 hp-sub">
                Event Schedule keeps a voice teacher's lessons and a choir's rehearsals and concerts, where people book and see every date. Stackapella is where singers learn their part in between, with a guide voice and feedback on every note. Use them side by side today, and soon as one.
            </p>

            <div class="es-fade-up es-d-3 hp-hero-actions">
                <a href="{{ $saHome }}" target="_blank" rel="noopener" class="hp-btn hp-btn-ghost is-still">
                    Visit Stackapella
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
                <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary">
                    Start for free
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>

            <nav class="es-fade-up es-d-4 hp-toc" aria-label="On this page">
                @foreach ([['week', 'Your week'], ['teachers', 'Voice teachers'], ['choirs', 'Choirs'], ['connect', 'The connection'], ['stackapella', 'About Stackapella']] as $tocIndex => [$tocId, $tocLabel])
                    <a href="#{{ $tocId }}"><b>{{ sprintf('%02d', $tocIndex + 1) }}</b>{{ $tocLabel }}</a>
                @endforeach
            </nav>
        </div>
    </section>

    {{-- ============================================================
         2. The set piece: pick your part, and follow it through the week
         ============================================================ --}}
    <section id="week" class="hp-dark scroll-mt-24">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>Your week</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">Pick your part. <span class="hp-ink-grad">The week is yours.</span></h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                    Four bars, like the four voices of a choir and the four in Stackapella's mark. Press one and follow your part through a singer's week: the dates in Event Schedule, the practice in Stackapella.
                </p>
            </div>

            <fieldset class="es-sa-set es-sa-on-night" data-reveal="panel">
                <legend class="sr-only">Choose a part</legend>
                @foreach ($saParts as $partIndex => [$partKey, $partName])
                    <input type="radio" name="es-sa-part" id="es-sa-part-{{ $partKey }}" value="{{ $partKey }}" class="sr-only" @checked($partIndex === 0)>
                @endforeach

                <p class="es-sa-hint" aria-hidden="true">
                    Choose a part
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </p>

                <div class="es-sa-lanes">
                    @foreach ($saParts as [$partKey, $partName, $partColour, $partLength, $partSub])
                        <label for="es-sa-part-{{ $partKey }}" class="es-sa-lane" style="--lane: {{ $partColour }}; --len: {{ $partLength }};">
                            <span class="es-sa-lane-ring" aria-hidden="true"></span>
                            <span class="es-sa-lane-text">
                                <span class="es-sa-lane-name">{{ $partName }}</span>
                                <span class="es-sa-lane-sub">{{ $partSub }}</span>
                            </span>
                            <span class="es-sa-lane-you">Your part</span>
                        </label>
                    @endforeach
                </div>

                <div class="es-sa-score">
                    <div class="es-sa-staff is-es" aria-hidden="true">
                        <span class="es-sa-staff-name">
                            <span class="es-sa-es-tile"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></span>
                            Event Schedule
                        </span>
                        <span class="es-sa-staff-sub">The dates</span>
                    </div>
                    <div class="es-sa-staff is-sa" aria-hidden="true">
                        <span class="es-sa-staff-name">
                            @include('marketing.partials.integration-logo', ['name' => 'stackapella', 'class' => 'es-sa-mark'])
                            Stackapella
                        </span>
                        <span class="es-sa-staff-sub">The practice</span>
                    </div>

                    <ol class="es-sa-week" aria-label="A singer's week">
                        @foreach ($saWeek as $dayIndex => $day)
                            @php
                                $dayStyle = '--col: '.$day['col'].';';
                                if (isset($day['fill'])) {
                                    foreach ($day['fill'] as $fillPart => $fillPercent) {
                                        $dayStyle .= ' --fill-'.$fillPart.': '.$fillPercent.'%;';
                                    }
                                    $dayStyle .= ' --delay: '.number_format($day['col'] * 0.12, 2).'s;';
                                }
                            @endphp
                            <li class="es-sa-day is-{{ $day['app'] }}" style="{{ $dayStyle }}">
                                <div class="es-sa-day-top">
                                    <span class="es-sa-day-name">{{ $day['day'] }}</span>
                                    @if ($day['app'] === 'sa')
                                        <span class="es-sa-tag is-sa">
                                            @include('marketing.partials.integration-logo', ['name' => 'stackapella', 'class' => ''])
                                            Stackapella
                                        </span>
                                    @elseif ($day['app'] === 'es')
                                        <span class="es-sa-tag is-es">
                                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            Event Schedule
                                        </span>
                                    @else
                                        <span class="es-sa-state is-next">Being built</span>
                                    @endif
                                </div>
                                <p class="es-sa-day-title">
                                    @if ($day['app'] === 'sa')
                                        <span class="es-sa-day-dot" aria-hidden="true"></span>
                                    @endif
                                    {{ $day['title'] }}
                                </p>
                                <p class="es-sa-day-body">
                                    @if (str_contains($day['body'], '%s'))
                                        @foreach ($saParts as [$partKey, $partName])
                                            <span data-part="{{ $partKey }}">{{ str_replace(['{left}', '{tidy}'], [$saLeft[$partKey], $saTidy[$partKey]], sprintf($day['body'], strtolower($partName))) }}</span>
                                        @endforeach
                                    @else
                                        {{ $day['body'] }}
                                    @endif
                                </p>
                                @isset($day['fill'])
                                    <span class="es-sa-meter" aria-hidden="true"><span><i></i></span></span>
                                @endisset
                            </li>
                        @endforeach
                    </ol>
                </div>

                <ul class="es-sa-legend">
                    <li><i class="is-solid" aria-hidden="true"></i>Solid: works today, in one app or the other</li>
                    <li><i class="is-dashed" aria-hidden="true"></i>Dashed: the connection, being built</li>
                </ul>
            </fieldset>

            <div class="es-sa-cta" data-reveal>
                <a href="{{ $saHome }}" target="_blank" rel="noopener" class="hp-btn hp-btn-ghost is-still">
                    Learn a part on stackapella.com
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
                <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary">
                    Put your dates on a schedule
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- ============================================================
         3. Voice teachers. Both audiences share one pattern: a DUET (the
         two apps as two halves of one object, Event Schedule on paper and
         Stackapella on its own black, each saying in one line what it does
         for you) and under it a dashed BRIDGE, hung from the duet, for
         what the connection will add.
         ============================================================ --}}
    @php
        $teacherEs = [
            'Lesson types with your weekly hours, date overrides for the weeks you are away, and buffers between students.',
            'A booking page of your own. A student picks an open time and gets a calendar invite, a reminder about a day before, and a private link to move or cancel it.',
            'Sync with Google Calendar or Outlook, so a gig or an appointment there takes its time off the page.',
            'Put a price on a lesson and take it through Stripe, PayPal, Payfast or a payment link, with no platform fee.',
        ];
        $teacherSa = [
            'Warm-ups you build in a minute and send by link. Each one opens in the student\'s own range, which they can move lower or higher.',
            'Short courses to set between lessons: Vibrato, Range and registers, Breath and stamina, and Perfect your pitch. Every lesson is free.',
            'A picture of their own pitch as they sing, so they practise against something better than memory.',
        ];
        $teacherNext = [
            ['Offer your lesson types in Stackapella', 'Connect your schedule once and pick which lesson types to offer. Your hours, buffers and calendar sync stay in Event Schedule, where you already set them.'],
            ['Singers book without leaving', 'Your next open times appear on your Stackapella profile, in the singer\'s own time zone, and the booking lands in Event Schedule like any other.'],
            ['Changes go both ways', 'A lesson you move or cancel in Event Schedule moves or cancels in Stackapella too, and the singer is told there.'],
            ['Around the lesson', 'Before it, the singer can attach a take of what they want to work on. After it, you can add notes, voice notes and exercises to the same lesson.'],
        ];
    @endphp
    <section id="teachers" class="hp-sec scroll-mt-24">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>Voice teachers and vocal coaches</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">Your hours in one, <span class="hp-ink-grad">their homework in the other.</span></h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                    Students book the lesson from the hours you keep in Event Schedule. Between lessons they practise in Stackapella, on what you sent them.
                </p>
            </div>

            <div class="es-sa-duet" data-reveal="panel">
                <div class="es-sa-half is-es">
                    <div class="es-sa-half-head">
                        <span class="es-sa-es-tile" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></span>
                        <h3>In Event Schedule</h3>
                        <span class="es-sa-state is-now">Today</span>
                    </div>
                    <p class="es-sa-half-say">The hours you teach, and the page students book them from.</p>
                    <ul class="hp-checks">
                        @foreach ($teacherEs as $line)
                            <li>
                                <span class="hp-ok" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></span>
                                <span>{{ $line }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="es-sa-foot">
                        One free lesson type on the free plan; more types and a price need Pro at {{ plan_price($proMonthly) }} a month. On eventschedule.com the booking emails go out through your schedule's own email settings.
                        <x-link href="{{ marketing_url('/features/appointments') }}">Appointments</x-link>
                    </p>
                </div>

                <div class="es-sa-half is-sa">
                    <div class="es-sa-half-head">
                        @include('marketing.partials.integration-logo', ['name' => 'stackapella', 'class' => 'es-sa-mark'])
                        <h3>In Stackapella</h3>
                        <span class="es-sa-state is-now">Today</span>
                    </div>
                    <p class="es-sa-half-say">The practice between lessons, on what you set.</p>
                    <ul class="hp-checks">
                        @foreach ($teacherSa as $line)
                            <li>
                                <span class="hp-ok" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></span>
                                <span>{{ $line }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="es-sa-foot es-sa-links">
                        <x-link href="{{ $saUrl('/for/voice-teachers') }}" target="_blank" class="font-semibold">Stackapella for voice teachers</x-link>
                        <x-link href="{{ $saUrl('/warmups') }}" target="_blank" class="font-semibold">The warm-up builder</x-link>
                    </p>
                </div>
            </div>

            <div class="es-sa-bridge es-sa-draft" data-reveal="panel">
                <span class="es-sa-bridge-tab es-sa-state is-next">Being built</span>
                <div class="es-sa-bridge-head">
                    <h3>When the two are connected</h3>
                    <p>Not open yet. This is what the connection is being built to do for a teacher.</p>
                </div>
                <ol class="es-sa-steps is-4" data-reveal-group="80">
                    @foreach ($teacherNext as $stepIndex => [$stepTitle, $stepBody])
                        <li class="es-sa-step" data-reveal>
                            <span class="es-sa-num" aria-hidden="true">{{ $stepIndex + 1 }}</span>
                            <div>
                                <h4>{{ $stepTitle }}</h4>
                                <p>{{ $stepBody }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
                <p class="es-sa-bridge-foot">
                    Coaching is coming to Stackapella, from vocal coaches it approves.
                    <x-link href="{{ $saUrl('/for/coaches') }}" target="_blank">Read about coaching there</x-link>
                </p>
            </div>
        </div>
    </section>

    {{-- ============================================================
         4. Choirs: the same duet and bridge as the teachers.
         ============================================================ --}}
    @php
        $choirEs = [
            'The whole season on one schedule: a weekly rehearsal as one repeating event, with the weeks off skipped and the extra ones added.',
            'Concerts with tickets and a door scanner, or free registration with a limit on places.',
            'Your schedule on the choir\'s own website, and a newsletter to the people who follow it.',
            'A date only the choir should see, kept off the public calendar.',
        ];
        $choirSa = [
            'Choir groups, free: share a song, give each singer a part, and see who has started it and who has recorded it. You never see their recordings or their contact details.',
            'Part-by-part learning tracks of one song you are rehearsing, made for your group free on request.',
            'Each singer learns their part at home, a phrase at a time, and sings it with the other parts playing.',
        ];
        $choirNext = [
            ['The next rehearsal, in the group', 'A Stackapella group shows the next rehearsals and concerts from your schedule, with what to practise for each: the alto in Danny Boy, two phrases to go.'],
            ['The songs, on the event page', 'Link the songs to a concert in Stackapella and its page in Event Schedule shows them with a player and a Learn your part link.'],
            ['Your members, invited in one step', 'Invite the people on your schedule\'s team to the Stackapella group at once, and give out the parts.'],
        ];
    @endphp
    <section id="choirs" class="hp-sec hp-alt scroll-mt-24">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>Choirs and directors</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">Rehearsal is for <span class="hp-ink-grad">singing together.</span></h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                    Run the season in Event Schedule: every rehearsal and concert, with tickets for the ones that sell. In Stackapella, each singer learns their part at home first, and you see who is ready before Thursday.
                </p>
            </div>

            <div class="es-sa-duet" data-reveal="panel">
                <div class="es-sa-half is-es">
                    <div class="es-sa-half-head">
                        <span class="es-sa-es-tile" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></span>
                        <h3>In Event Schedule</h3>
                        <span class="es-sa-state is-now">Today</span>
                    </div>
                    <p class="es-sa-half-say">The season: every rehearsal and concert, and who comes.</p>
                    <ul class="hp-checks">
                        @foreach ($choirEs as $line)
                            <li>
                                <span class="hp-ok" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></span>
                                <span>{{ $line }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="es-sa-foot">
                        Events and free registration are free; tickets with a price need Pro.
                        <x-link href="{{ marketing_url('/features/recurring-events') }}">Recurring events</x-link>
                    </p>
                </div>

                <div class="es-sa-half is-sa">
                    <div class="es-sa-half-head">
                        @include('marketing.partials.integration-logo', ['name' => 'stackapella', 'class' => 'es-sa-mark'])
                        <h3>In Stackapella</h3>
                        <span class="es-sa-state is-now">Today</span>
                    </div>
                    <p class="es-sa-half-say">The parts, learned at home before the room fills.</p>
                    <ul class="hp-checks">
                        @foreach ($choirSa as $line)
                            <li>
                                <span class="hp-ok" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></span>
                                <span>{{ $line }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="es-sa-foot">
                        <p>Choir groups are for singers 18 and over.</p>
                        <p class="es-sa-links mt-2">
                            <x-link href="{{ $saUrl('/for/choir-directors') }}" target="_blank" class="font-semibold">Stackapella for directors</x-link>
                            <x-link href="{{ $saUrl('/features/choirs') }}" target="_blank" class="font-semibold">Choir groups</x-link>
                        </p>
                    </div>
                </div>
            </div>

            <div class="es-sa-bridge es-sa-draft" data-reveal="panel">
                <span class="es-sa-bridge-tab es-sa-state is-next">Being built</span>
                <div class="es-sa-bridge-head">
                    <h3>When the two are connected</h3>
                    <p>Not open yet. This is what the connection is being built to do for a choir.</p>
                </div>
                <ol class="es-sa-steps is-3" data-reveal-group="80">
                    @foreach ($choirNext as $stepIndex => [$stepTitle, $stepBody])
                        <li class="es-sa-step" data-reveal>
                            <span class="es-sa-num" aria-hidden="true">{{ $stepIndex + 1 }}</span>
                            <div>
                                <h4>{{ $stepTitle }}</h4>
                                <p>{{ $stepBody }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
                <p class="es-sa-bridge-foot">
                    Singing in the choir rather than leading it?
                    <x-link href="{{ $saUrl('/for/choir-singers') }}" target="_blank">Learn your choir part at home</x-link>
                </p>
            </div>
        </div>
    </section>

    {{-- ============================================================
         5. How the connection will work: drawn dashed, like every other
         thing on the page that is not built yet.
         ============================================================ --}}
    @php
        $connectFacts = [
            ['Sign in and approve', 'In Stackapella\'s settings, Connect Event Schedule sends you to Event Schedule to sign in and approve, and then you pick the schedule. There is no key to copy.'],
            ['Only what it needs', 'Stackapella asks for named parts of your account, such as reading your schedules and booking lessons, never the whole of it. You can take that back from Event Schedule\'s settings at any time.'],
            ['Your calendar stays put', 'Hours, buffers, calendar sync, reminders and your public pages stay in Event Schedule. Stackapella reads them; it does not keep a second copy to drift.'],
            ['Disconnect without losing a lesson', 'Lessons already booked stay booked in Event Schedule, with their invites and links. Only the offer to book in Stackapella goes away.'],
        ];
    @endphp
    <section id="connect" class="hp-sec scroll-mt-24">
        <div class="hp-wrap">
            <div class="es-sa-split grid grid-cols-1 items-start gap-12">
                <div class="hp-head">
                    <span class="hp-kicker" data-reveal>The connection</span>
                    <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">Two apps, <span class="hp-ink-grad">one set of times.</span></h2>
                    <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                        Your times are written down once, in Event Schedule, and Stackapella reads them from there. This is how it is being built.
                    </p>
                    <div class="es-sa-flow" data-reveal style="--reveal-delay: 0.2s;" aria-hidden="true">
                        <span class="es-sa-flow-node">
                            <span class="es-sa-es-tile"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></span>
                            Event Schedule
                        </span>
                        <span class="es-sa-flow-wire"></span>
                        <span class="es-sa-state is-next">Being built</span>
                        <span class="es-sa-flow-wire"></span>
                        <span class="es-sa-flow-node">
                            @include('marketing.partials.integration-logo', ['name' => 'stackapella', 'class' => 'es-sa-mark'])
                            Stackapella
                        </span>
                    </div>
                </div>

                <ol class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="80">
                    @foreach ($connectFacts as $factIndex => [$factTitle, $factBody])
                        <li class="es-sa-plan es-sa-draft" data-reveal="panel">
                            <h3><span class="es-sa-num" aria-hidden="true">{{ $factIndex + 1 }}</span>{{ $factTitle }}</h3>
                            <p>{{ $factBody }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- ============================================================
         6. About Stackapella, and the rest of the integrations
         ============================================================ --}}
    <section id="stackapella" class="hp-sec hp-alt scroll-mt-24">
        <div class="hp-wrap">
            <div class="es-sa-about grid grid-cols-1 gap-4" data-reveal-group="90">
                <a href="{{ $saHome }}" target="_blank" rel="noopener" class="hp-card es-sa-card-sa es-sa-poster p-7 sm:p-10" data-reveal="panel">
                    <div class="es-sa-poster-copy">
                        <div class="mb-6 flex items-center gap-3">
                            @include('marketing.partials.integration-logo', ['name' => 'stackapella', 'class' => 'h-11 w-11 shrink-0'])
                            <span class="text-2xl font-bold tracking-tight">Stackapella</span>
                        </div>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight sm:text-3xl">Harmony, made by you.</h2>
                        <p class="es-sa-card-muted mb-8 max-w-2xl text-base leading-relaxed">
                            Learn a part by ear from a guide voice, then record every part of a song yourself, one voice at a time, and hear them come together exactly in time. A free course goes from your first note to four-part harmony, and your pitch is tracked on your own device. Open source, and selfhostable like Event Schedule.
                        </p>
                        <span class="es-sa-sa-btn">
                            Visit stackapella.com
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </span>
                    </div>
                    <div class="es-sa-poster-bars" aria-hidden="true">
                        @foreach ($saParts as [$partKey, $partName, $partColour, $partLength])
                            <i style="--lane: {{ $partColour }}; --len: {{ $partLength }};"></i>
                        @endforeach
                    </div>
                </a>

                <div class="grid grid-cols-1 gap-4">
                    <a href="{{ $saUrl('/how-it-works') }}" target="_blank" rel="noopener" class="hp-card group flex flex-col p-6" data-reveal="panel">
                        <h3 class="mb-1 text-base font-bold text-gray-900 dark:text-white">How Stackapella works</h3>
                        <p class="es-sa-card-muted mb-4 text-sm">Learn a part, sing it with the others, record and stack.</p>
                        <span class="es-sa-card-link mt-auto inline-flex items-center gap-2 text-sm font-semibold transition-all group-hover:gap-3">
                            stackapella.com
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </span>
                    </a>
                    <a href="{{ marketing_url('/features/integrations') }}" class="hp-card group flex flex-col p-6" data-reveal="panel">
                        <h3 class="mb-1 text-base font-bold text-gray-900 dark:text-white">The other integrations</h3>
                        <p class="es-sa-card-muted mb-4 text-sm">Calendars, payments, webhooks and the REST API.</p>
                        <span class="es-sa-card-link mt-auto inline-flex items-center gap-2 text-sm font-semibold transition-all group-hover:gap-3">
                            View all integrations
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         7. Questions, related pages and the finale
         ============================================================ --}}
    <x-seo.faq-schema :items="$saFaqs" />
    <x-marketing.hp-faq :items="$saFaqs">Stackapella <span class="hp-ink-grad">questions</span></x-marketing.hp-faq>

    <x-marketing.related-pages />

    <x-marketing.hp-finale lead="Put the lessons, rehearsals and concerts on one schedule, free to start." placeholder="your-choir">
        The dates in one place, <span class="hp-ink-grad">the parts learned at home</span>
    </x-marketing.hp-finale>

    {{-- Load-bearing, not decoration: marketing.css hides every [data-reveal] element behind
         html.es-anim, so a page that sets that class and never loads the reveal observer renders
         completely blank below the nav. --}}
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
