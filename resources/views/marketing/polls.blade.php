<x-marketing-layout>
    <x-slot name="title">Event Polls & Voting - Event Schedule</x-slot>
    <x-slot name="description">Add a poll to any event: a question, two to ten choices, one vote per signed-in guest. Guests read no count until they have voted, and closing the poll publishes it. A Pro feature, no credit card.</x-slot>
    <x-slot name="breadcrumbTitle">Event Polls</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Event Polls",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Audience Engagement Software",
        "operatingSystem": "Web",
        "description": "Add a poll to any event: a question and between two and ten fixed choices. Signed-in guests mark one, and the count comes back with their vote. Up to five polls per event on the Pro plan.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free to sign up; polls are included on the Pro plan"
        },
        "featureList": [
            "A question with between 2 and 10 fixed choices",
            "Up to 5 polls on one event",
            "One vote per signed-in guest, per poll, per date",
            "Results hidden until the guest votes",
            "Close a poll to publish the count to everyone",
            "Guest write-in options with an optional approval queue",
            "A separate count for every date of a recurring event",
            "Full-width voting buttons on phones"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    </x-slot>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           For-polls "The Vote" styles. A poll is a ballot. You print it
           with a fixed set of choices, one person gets one of them, the
           choices seal the moment the first vote drops in, and at the
           close somebody reads out the count.

           WHY A BALLOT AND NOT A LIVE RESULTS TICKER. The first-wave
           page ran a wall of bars animating up and down, which reads as
           a live stream of other people's votes. The product does not do
           that: results are FETCHED once, in the response to your own
           vote (EventController::votePoll returns results + total_votes),
           and they are HIDDEN from you until then. Drawing a live ticker
           taught the exact model the code refuses to give. So the
           signature device is a sheet of paper with mark boxes, and the
           count is a static declaration you only get to read after you
           have voted.

           COLOUR: the page keeps its existing blue family, but not the
           shared brand-blue to sky to cyan chrome gradient - that belongs
           to the site furniture, and a page accent that copies it has no
           identity. This is ONE flat printer's ink blue (#1d4ed8 light,
           #93b4fd dark) on ballot stock. Distinctiveness comes from the
           material and the structure, not from a new hue.

           NEVER use text-gray-500 here. It measures 4.83 on pure white
           but only about 4.4 on this page's warm ground. Use
           .es-vote-muted (7.16 light, 8.01 dark).

           FIXED PHYSICAL OBJECT: .es-vote-paper is a printed ballot. It
           must render IDENTICALLY with .dark on and off. Anything nested
           inside it that has a .dark rule needs a
           `.dark .es-vote-paper ...` pin AFTER the base rule, which is
           why .es-vote-box carries one.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------ */
        .es-vote-page { background-color: #f3f1ea; color: #16181c; }
        .dark .es-vote-page { background-color: #0d0f14; color: #e9e7e0; }
        .es-vote-ink { color: #16181c; }
        .dark .es-vote-ink { color: #e9e7e0; }
        .es-vote-muted { color: #4b5158; }
        .dark .es-vote-muted { color: #a2a8b0; }
        .es-vote-accent { color: #1d4ed8; }
        .dark .es-vote-accent { color: #93b4fd; }
        /* Always-lit accent, for the fixed-dark bands in both modes. */
        .es-vote-lit { color: #93b4fd; }

        /* --- Cards --------------------------------------------------- */
        /* Hairline separators between sections. Tailwind cannot generate an
           arbitrary rgba() border class that is not already in the built
           marketing-app CSS, so this is a real rule. */
        .es-vote-rule { border-color: rgba(22, 24, 28, 0.09); }
        .dark .es-vote-rule { border-color: rgba(233, 231, 224, 0.09); }

        .es-vote-card {
            border: 1px solid rgba(22, 24, 28, 0.13);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-vote-card {
            border-color: rgba(233, 231, 224, 0.12);
            background: rgba(233, 231, 224, 0.045);
        }
        .es-vote-band .es-vote-card {
            border-color: rgba(233, 231, 224, 0.14);
            background: rgba(233, 231, 224, 0.05);
        }

        /* --- Fixed-dark band ----------------------------------------- */
        .es-vote-band {
            background-color: #10141c;
            background-image: radial-gradient(120% 100% at 50% 0%, #1a2130 0%, #131923 55%, #0a0d13 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(233, 231, 224, 0.05);
        }
        .es-vote-band-ink { color: #e9e7e0; }
        .es-vote-band-muted { color: #9aa3b2; }
        /* Shared classes that would otherwise flip with the colour mode
           inside a band that is dark in BOTH modes. */
        .es-vote-band .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 231, 224, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(233, 231, 224, 0.05) 1px, transparent 1px);
        }
        .es-vote-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-vote-band .es-claim:focus-within {
            border-color: rgba(147, 180, 253, 0.75);
            box-shadow: 0 0 0 4px rgba(147, 180, 253, 0.22);
        }

        /* --- The ballot: a sheet of stock, same in both colour modes -- */
        .es-vote-paper {
            position: relative;
            border-radius: 0.35rem;
            background-color: #f0ece0;
            background-image: linear-gradient(180deg, #f4f0e6 0%, #ebe6d7 100%);
            border: 1px solid #cdc5b0;
            box-shadow: 0 24px 48px -24px rgba(22, 24, 28, 0.5);
            color: #1a1c20;
        }
        .es-vote-paper-ink { color: #1a1c20; }
        .es-vote-paper-muted { color: #5b5648; }
        .es-vote-paper-accent { color: #1d4ed8; }
        /* Printed hairline and the tear-off perforation. */
        .es-vote-paper-rule { border-top: 1px solid rgba(26, 28, 32, 0.18); }
        .es-vote-perf { border-top: 1px dashed rgba(26, 28, 32, 0.34); }

        /* A printed option line on the ballot. */
        .es-vote-row {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.5rem 0.7rem;
            border: 1px solid rgba(26, 28, 32, 0.16);
            border-radius: 0.3rem;
            background: rgba(255, 255, 255, 0.55);
        }
        .es-vote-row + .es-vote-row { margin-top: 0.4rem; }
        .es-vote-row-marked {
            border-color: rgba(29, 78, 216, 0.55);
            background: rgba(29, 78, 216, 0.08);
        }

        /* --- The mark box -------------------------------------------- */
        .es-vote-box {
            position: relative;
            flex: none;
            width: 1.05rem;
            height: 1.05rem;
            border: 1.5px solid rgba(26, 28, 32, 0.55);
            border-radius: 0.15rem;
            background: #fffdf7;
        }
        .es-vote-box-x { border-color: #1d4ed8; }
        .es-vote-box-x::before,
        .es-vote-box-x::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 50%;
            width: 1.6px;
            height: 0.82rem;
            margin: -0.41rem 0 0 -0.8px;
            border-radius: 1px;
            background: #1d4ed8;
        }
        .es-vote-box-x::before { transform: rotate(42deg); }
        .es-vote-box-x::after { transform: rotate(-42deg); }
        /* Off the paper, the box belongs to the interface and follows the
           colour mode. */
        .dark .es-vote-box {
            border-color: rgba(233, 231, 224, 0.5);
            background: rgba(233, 231, 224, 0.06);
        }
        .dark .es-vote-box-x { border-color: #93b4fd; }
        .dark .es-vote-box-x::before,
        .dark .es-vote-box-x::after { background: #93b4fd; }
        /* On the paper it does not. Pin it back. */
        .dark .es-vote-paper .es-vote-box {
            border-color: rgba(26, 28, 32, 0.55);
            background: #fffdf7;
        }
        .dark .es-vote-paper .es-vote-box-x { border-color: #1d4ed8; }
        .dark .es-vote-paper .es-vote-box-x::before,
        .dark .es-vote-paper .es-vote-box-x::after { background: #1d4ed8; }

        /* An unprinted line: the ballot in the finale has no words on it
           yet, because the question is the reader's to write. Hairlines,
           not dotted leaders (those connect a label to a value and nine
           other pages already own them). */
        .es-vote-blank {
            flex: 1;
            height: 1px;
            border-radius: 1px;
            background: rgba(26, 28, 32, 0.28);
        }
        .es-vote-blank-short { flex: 0 1 55%; }

        /* --- The stamp ------------------------------------------------ */
        .es-vote-stamp {
            display: inline-flex;
            align-items: center;
            padding: 0.28rem 0.7rem;
            border: 2px solid rgba(29, 78, 216, 0.6);
            border-radius: 0.2rem;
            color: #1d4ed8;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            transform: rotate(-3.5deg);
        }
        .es-vote-stamp-wait {
            border-color: rgba(26, 28, 32, 0.42);
            color: #4a4536;
        }

        /* --- Hand tally, five marks to a group ------------------------ */
        .es-vote-tally { display: inline-flex; align-items: center; gap: 0.45rem; }
        .es-vote-tally-g { position: relative; display: inline-flex; gap: 3px; }
        .es-vote-mark {
            display: block;
            width: 2px;
            height: 0.8rem;
            border-radius: 1px;
            background: currentColor;
            opacity: 0.8;
        }
        .es-vote-tally-g-full::after {
            content: "";
            position: absolute;
            left: -3px;
            right: -3px;
            top: 50%;
            height: 2px;
            border-radius: 1px;
            background: currentColor;
            opacity: 0.8;
            transform: rotate(-18deg);
        }

        /* --- Share bars ----------------------------------------------- */
        /* Three states, because the live poll draws three: the bar for the
           choice YOU marked is the schedule's accent at full strength with a
           soft glow, the leading choice gets the same colour at half
           strength, and every other bar is a neutral gray. Painting them all
           blue would claim a colour the product does not use. */
        .es-vote-track {
            position: relative;
            height: 0.5rem;
            border-radius: 9999px;
            background: rgba(22, 24, 28, 0.1);
            overflow: hidden;
        }
        .dark .es-vote-track { background: rgba(233, 231, 224, 0.12); }
        .es-vote-fill {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: var(--w, 50%);
            border-radius: 9999px;
            background: #9ca3af;
            transform-origin: left center;
        }
        .es-vote-fill-lead { background: rgba(29, 78, 216, 0.5); }
        .dark .es-vote-fill-lead { background: rgba(147, 180, 253, 0.5); }
        .es-vote-fill-mine { background: #1d4ed8; box-shadow: 0 0 8px rgba(29, 78, 216, 0.35); }
        .dark .es-vote-fill-mine { background: #93b4fd; box-shadow: 0 0 8px rgba(147, 180, 253, 0.3); }
        html.es-anim .es-vote-fill {
            animation: es-vote-grow 1s cubic-bezier(0.22, 1, 0.36, 1) both;
            animation-delay: var(--wd, 0s);
        }
        @keyframes es-vote-grow {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }

        /* --- A live option row (the interface, not the paper) --------- */
        .es-vote-opt {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.6rem 0.8rem;
            border: 1px solid rgba(22, 24, 28, 0.18);
            border-radius: 0.6rem;
        }
        .dark .es-vote-opt { border-color: rgba(233, 231, 224, 0.18); }
        .es-vote-opt + .es-vote-opt { margin-top: 0.4rem; }

        /* --- Eyebrow, numeral, plan tag, chip ------------------------- */
        .es-vote-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-vote-tag { color: #a2a8b0; }
        .es-vote-band .es-vote-tag { color: #93b4fd; }

        .es-vote-num {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.85rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(22, 24, 28, 0.18);
            background: #ffffff;
            color: #16181c;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .dark .es-vote-num {
            border-color: rgba(233, 231, 224, 0.2);
            background: rgba(233, 231, 224, 0.05);
            color: #e9e7e0;
        }
        .es-vote-band .es-vote-num {
            border-color: rgba(233, 231, 224, 0.2);
            background: rgba(233, 231, 224, 0.05);
            color: #e9e7e0;
        }
        .es-vote-num::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #1d4ed8;
        }
        .dark .es-vote-num::before { background: #93b4fd; }
        .es-vote-band .es-vote-num::before { background: #93b4fd; }

        .es-vote-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(29, 78, 216, 0.45);
            color: #1d4ed8;
        }
        .dark .es-vote-plan { border-color: rgba(147, 180, 253, 0.45); color: #93b4fd; }

        .es-vote-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.9rem;
            border-radius: 9999px;
            border: 1px solid rgba(22, 24, 28, 0.16);
            background: rgba(255, 255, 255, 0.7);
            color: #4b5158;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .dark .es-vote-chip {
            border-color: rgba(233, 231, 224, 0.16);
            background: rgba(233, 231, 224, 0.05);
            color: #b4bac2;
        }

        /* --- Links, buttons, hovers ----------------------------------- */
        .es-vote-link { color: #1d4ed8; }
        .es-vote-link:hover { color: #16181c; }
        .dark .es-vote-link { color: #93b4fd; }
        .dark .es-vote-link:hover { color: #e9e7e0; }

        .es-vote-btn {
            background-color: #1d4ed8;
            box-shadow: 0 18px 36px -14px rgba(29, 78, 216, 0.5);
        }
        .es-vote-btn:hover { background-color: #1a44bb; box-shadow: 0 22px 44px -14px rgba(29, 78, 216, 0.62); }
        .dark .es-vote-btn { background-color: #2f5fe0; }
        .dark .es-vote-btn:hover { background-color: #4272ea; }

        .es-vote-hover:hover { border-color: rgba(29, 78, 216, 0.45); }
        .dark .es-vote-hover:hover { border-color: rgba(147, 180, 253, 0.45); }
        .es-vote-hover:hover .es-vote-hover-title,
        .es-vote-hover:hover .es-vote-hover-arrow { color: #1d4ed8; }
        .dark .es-vote-hover:hover .es-vote-hover-title,
        .dark .es-vote-hover:hover .es-vote-hover-arrow { color: #93b4fd; }

        .es-vote-tip {
            border-color: rgba(22, 24, 28, 0.14);
            background: #ffffff;
            color: #16181c;
        }
        .dark .es-vote-tip {
            border-color: rgba(233, 231, 224, 0.12);
            background: #171a21;
            color: #e9e7e0;
        }

        /* --- Shared-system recolours (brand blue by default) ---------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(29, 78, 216, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(147, 180, 253, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(29, 78, 216, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(147, 180, 253, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #1d4ed8; }
        .dark .es-dot.is-active .es-dot-pip { background: #93b4fd; }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. -- */
        #es-vote-page a:focus-visible,
        #es-vote-page summary:focus-visible,
        #es-vote-page button:focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 3px;
        }
        .dark #es-vote-page a:focus-visible,
        .dark #es-vote-page summary:focus-visible,
        .dark #es-vote-page button:focus-visible {
            outline-color: #93b4fd;
        }
        .es-vote-band a:focus-visible,
        .es-vote-band summary:focus-visible,
        .es-vote-band button:focus-visible {
            outline-color: #93b4fd !important;
        }

        @media (prefers-reduced-motion: reduce) {
            html.es-anim .es-vote-fill { animation: none !important; transform: none !important; }
        }
    </style>

    @php
        // One ballot, carried through the page. 39 votes across four choices,
        // the last of them a write-in a guest added. The percentages are the
        // real rounding of these counts and they sum to 100.
        $ballotQuestion = 'Which support act should open on Saturday?';
        $ballot = [
            ['Sunhouse',          16, 41, false],
            ['Marker Bay',        11, 28, false],
            ['Two Rivers',         8, 21, false],
            ['The Lamplighters',   4, 10, true],
        ];
        $ballotTotal = 39;
        // The voter on this page picked Marker Bay, so that row carries the check.
        $myPick = 1;

        // Recurring event: the same poll, a separate count on each date.
        // Leading choice, votes cast, and the leader's share, per date. The bar
        // width IS the share, so the drawing does not imply a number the data
        // does not carry.
        $perDate = [
            ['Thu 4 Jun',  'Marker Bay',  22, 45],
            ['Thu 11 Jun', 'Sunhouse',    31, 52],
            ['Thu 18 Jun', 'Sunhouse',    27, 41],
            ['Thu 25 Jun', 'Two Rivers',  19, 37],
        ];

        // Questions people actually put on a ballot.
        $questionChips = [
            'Which support act opens?',
            'Early show or late show?',
            'What should the encore be?',
            'Which Saturday suits you?',
            'Bring the workshop back?',
            'Indoors or in the yard?',
            'Which film next month?',
            'Beginner or improver class?',
        ];

        $spec = [
            ['Polls on one event', 'Up to 5'],
            ['Choices on one poll', '2 to 10, write-ins included'],
            ['Length of a question', 'Up to 500 characters'],
            ['Length of a choice', 'Up to 200 characters'],
            ['Votes per person', 'One, per poll, per date'],
            ['Changing your vote', 'Not possible once cast'],
            ['Voting without an account', 'Not possible'],
            ['Reading the count', 'After you vote, or once the poll closes'],
            ['Editing the choices', 'Until the first vote lands'],
            ['Plan', 'Pro, and included on Enterprise'],
        ];

        $faqs = [
            [
                'q' => 'How do event polls work?',
                'a' => 'You add a poll to an event: a question, and between two and ten choices. It shows up on the event page and inside that event\'s card on your calendar. A signed-in guest marks one choice, the vote is recorded, and the count comes straight back with a bar, a number and a percentage against every choice. One event can carry up to five polls.',
            ],
            [
                'q' => 'Who can create polls?',
                'a' => 'Anyone who can edit the event, on a schedule with the Pro plan. Polls live on the Polls tab of the event editor\'s Engagement section, and you can read the results there whenever you like, whether or not you have voted yourself.',
            ],
            [
                'q' => 'Can guests see the results before voting?',
                'a' => 'No. Until a guest votes they see the choices and nothing else: no totals, no percentages, no leading option. The count appears the moment they mark the ballot. The one exception is a closed poll, because closing it publishes the result to everybody, including people who never voted and people who are not signed in.',
            ],
            [
                'q' => 'Do guests need an account to vote?',
                'a' => 'Yes. A vote is tied to an account, which is what stops one person voting twice. Guests who are not signed in see the choices with a sign-in link in place of the buttons. Votes cannot be changed once they are cast.',
            ],
            [
                'q' => 'Can guests add a choice of their own?',
                'a' => 'Yes, if you turn write-ins on for that poll. A signed-in guest can add a choice, up to the same ceiling of ten. Turn approval on as well and their suggestion waits in a pending queue on the Polls tab of the event editor until you approve or reject it, and approving it adds the choice with its author\'s vote already attached. A suggestion that duplicates a choice already on the ballot is refused before it reaches you.',
            ],
            [
                'q' => 'What happens on a recurring event?',
                'a' => 'Each date is polled separately. A vote is recorded against the date the guest is looking at, so a weekly event collects a fresh count every week instead of pooling a month of answers into one bar. On a one-off event there is simply the one count.',
            ],
            [
                'q' => 'Can I change the choices after people start voting?',
                'a' => 'No, and that is deliberate. Once a poll holds a single vote the option list is locked, so everyone who voted was answering the same ballot. The question text can still be edited, and you can close the poll, reopen it or delete it at any point.',
            ],
            [
                'q' => 'Is there a free version of polls?',
                'a' => 'Polls are a Pro feature at '.plan_price($proMonthly).' a month, included on Enterprise, and selfhosted installations get them too. If you want to ask your audience something on the free plan, newsletters are free at 10 emails a month, counted per recipient rather than per send, and collecting followers costs nothing on any plan.',
            ],
        ];

        $dotSections = [
            ['top', 'The ballot'],
            ['what', 'What a poll is'],
            ['states', 'Before and after'],
            ['seal', 'The seal'],
            ['count', 'The declaration'],
            ['writein', 'Write-ins'],
            ['dates', 'One date at a time'],
            ['where', 'Where it appears'],
            ['spec', 'The small print'],
            ['faq', 'Questions'],
            ['claim', 'Ask them'],
        ];
    @endphp

    <div id="es-vote-page" class="es-vote-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the printed ballot                                  -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 30%, rgba(29, 78, 216, 0.2), rgba(29, 78, 216, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 45%, rgba(147, 180, 253, 0.14), rgba(147, 180, 253, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 es-vote-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="es-vote-muted text-sm font-medium tracking-wide">Event polls</span>
                        <span class="es-vote-plan">Pro</span>
                    </div>

                    <h1 class="es-balance es-vote-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">You have a question.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">Print the <span class="es-vote-accent">ballot.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-vote-muted mb-10 max-w-xl text-lg sm:text-xl">
                        A poll is a question and up to ten fixed choices, sitting on the event page itself. Signed-in guests mark one. Nobody reads the count until they have voted.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-vote-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="{{ route('marketing.docs.creating_events') }}#polls" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Read the Polls guide
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The ballot. A fixed physical object: identical in light and dark. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-vote-paper p-6 sm:p-7">
                        <div class="mb-4 flex items-baseline justify-between gap-3">
                            <span class="es-vote-paper-muted text-[0.6rem] font-bold uppercase tracking-[0.3em]">Ballot</span>
                            <span class="es-vote-paper-muted font-mono text-[0.65rem]">Sat 6 Jun</span>
                        </div>
                        <div class="es-vote-paper-rule mb-4" aria-hidden="true"></div>

                        {{-- A sample question printed on a mock ballot, not a section
                             heading: keeping it out of the outline leaves the page's
                             first h2 to the real topic below. --}}
                        <p class="es-vote-paper-ink mb-4 text-lg font-bold leading-snug">{{ $ballotQuestion }}</p>

                        <div aria-hidden="true">
                            @foreach ($ballot as $bi => [$bName, $bCount, $bPct, $bWriteIn])
                                <div class="es-vote-row @if ($bi === $myPick) es-vote-row-marked @endif">
                                    <span class="es-vote-box @if ($bi === $myPick) es-vote-box-x @endif"></span>
                                    <span class="es-vote-paper-ink min-w-0 flex-1 truncate text-sm @if ($bi === $myPick) font-semibold @endif">{{ $bName }}</span>
                                    @if ($bWriteIn)
                                        <span class="es-vote-paper-accent flex-none text-[0.6rem] font-bold uppercase tracking-[0.14em]">Write-in</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="es-vote-perf mt-5 pt-4">
                            <p class="es-vote-paper-muted text-[0.7rem] leading-relaxed">
                                Mark one choice only. One ballot per person, per date. You will see the count as soon as you have marked it.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions people actually ask -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach ($questionChips as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-vote-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. What a poll actually is (fixed-dark band)                 -->
    <!-- ============================================================ -->
    <section id="what" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-vote-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(29, 78, 216, 0.24), rgba(29, 78, 216, 0) 60%); opacity: 0.6;"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-vote-num mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-vote-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What a poll is</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One question. A fixed set of choices. <span class="es-vote-lit">Nothing else to fill in.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-vote-card p-6" data-reveal="panel">
                        <p class="es-vote-tag mb-3">Per event</p>
                        <h3 class="mb-2 text-lg font-bold es-vote-band-ink">
                            Up to <span data-count-to="5">5</span> polls
                        </h3>
                        <p class="es-vote-band-muted text-sm">One event can carry five polls at once, each with its own question and its own count. Ask about the running order and the start time without making people fill in a form.</p>
                    </div>
                    <div class="es-vote-card p-6" data-reveal="panel">
                        <p class="es-vote-tag mb-3">Per poll</p>
                        <h3 class="mb-2 text-lg font-bold es-vote-band-ink">
                            2 to 10 choices
                        </h3>
                        <p class="es-vote-band-muted text-sm">A ballot needs at least two choices and holds at most ten. That ceiling counts write-ins too, so an open ballot fills up rather than sprawling.</p>
                    </div>
                    <div class="es-vote-card p-6" data-reveal="panel">
                        <p class="es-vote-tag mb-3">Per person</p>
                        <h3 class="mb-2 text-lg font-bold es-vote-band-ink">
                            One vote
                        </h3>
                        <p class="es-vote-band-muted text-sm">Voting needs an account, so the count is people rather than clicks. One mark each, and it cannot be taken back and cast again.</p>
                    </div>
                </div>

                <p class="mt-10 text-center es-vote-band-muted" data-reveal>
                    Polls are on the Pro plan.
                    <a href="{{ marketing_url('/pricing') }}" class="es-vote-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        See what that costs
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Before the mark, after the mark                           -->
    <!-- ============================================================ -->
    <section id="states" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-vote-num mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-vote-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The two states</p>
                <h2 class="es-balance es-vote-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Nobody sees the count <span class="es-vote-accent">until they have voted.</span>
                </h2>
                <p class="es-vote-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    This is the one rule that makes the answer worth reading. Until a guest marks the ballot they get choices and nothing else: no running total, no leading option, nothing to fall in behind. The instant they vote, the whole count comes back to them.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2" data-reveal-group="110">
                <!-- Before -->
                <div class="es-vote-card flex h-full flex-col p-6 sm:p-8" data-reveal="panel">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <p class="es-vote-tag">Before the mark</p>
                        <span class="es-vote-muted font-mono text-xs">what a guest sees</span>
                    </div>
                    <h3 class="es-vote-ink mb-2 text-xl font-bold">{{ $ballotQuestion }}</h3>
                    <p class="es-vote-muted mb-5 text-sm">Four choices, laid out full width so a thumb can hit them.</p>

                    <div aria-hidden="true">
                        @foreach ($ballot as [$bName, $bCount, $bPct, $bWriteIn])
                            <div class="es-vote-opt">
                                <span class="es-vote-box"></span>
                                <span class="es-vote-ink min-w-0 flex-1 truncate text-sm">{{ $bName }}</span>
                            </div>
                        @endforeach
                    </div>

                    <p class="es-vote-muted mt-auto pt-5 text-sm">
                        Not signed in? While the poll is open the choices are there to read, with a sign-in link where the buttons would be. Still no count.
                    </p>
                </div>

                <!-- After -->
                <div class="es-vote-card flex h-full flex-col p-6 sm:p-8" data-reveal="panel">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <p class="es-vote-tag">After the mark</p>
                        <span class="es-vote-muted font-mono text-xs">{{ $ballotTotal }} votes</span>
                    </div>
                    <h3 class="es-vote-ink mb-2 text-xl font-bold">{{ $ballotQuestion }}</h3>
                    <p class="es-vote-muted mb-5 text-sm">The same poll, one second later, from the same screen.</p>

                    <div class="space-y-3" aria-hidden="true">
                        @foreach ($ballot as $bi => [$bName, $bCount, $bPct, $bWriteIn])
                            <div>
                                <div class="mb-1.5 flex items-baseline justify-between gap-3 text-sm">
                                    <span class="es-vote-ink min-w-0 flex-1 truncate @if ($bi === 0) font-bold @endif">
                                        {{ $bName }}@if ($bi === $myPick)<span class="es-vote-accent"> &check;</span>@endif
                                    </span>
                                    <span class="es-vote-muted flex-none font-mono text-xs">{{ $bCount }} ({{ $bPct }}%)</span>
                                </div>
                                <div class="es-vote-track">
                                    <span class="es-vote-fill @if ($bi === $myPick) es-vote-fill-mine @elseif ($bi === 0) es-vote-fill-lead @endif" style="--w: {{ $bPct }}%; --wd: {{ $bi * 0.12 }}s;"></span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <p class="es-vote-muted mt-auto pt-5 text-sm">
                        The leading choice is set in bold. The bar for the one you marked is your schedule's accent colour at full strength with a check beside it, the leader gets the same colour at half strength, and every other bar stays gray.
                    </p>
                </div>
            </div>

            <p class="es-vote-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Organizers are the exception. You can read the count from the event editor whenever you like, whether or not you voted yourself.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The seal                                                  -->
    <!-- ============================================================ -->
    <section id="seal" class="scroll-mt-24 es-vote-rule border-y py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-vote-num mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-vote-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The seal</p>
                    <h2 class="es-balance es-vote-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        The first vote <span class="es-vote-accent">seals the choices.</span>
                    </h2>
                    <p class="es-vote-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Up to that moment a poll is yours to rewrite. From the moment one person has voted, the option list is locked. A count is only worth reading if everybody who answered was answering the same ballot, so the software will not let the ballot move underneath them.
                    </p>
                    <ul class="es-vote-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-vote-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Still yours: the wording of the question, the two write-in switches, whether the poll is open or closed, and deleting it outright.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-vote-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Set for good: the choices themselves, for as long as a single vote exists against them.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-vote-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Write-ins are the one way a choice joins a sealed ballot, and only if you allowed them.</span>
                        </li>
                    </ul>
                </div>

                <div data-reveal="panel">
                    <div class="es-vote-paper p-6 sm:p-7">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div>
                                <span class="es-vote-paper-muted block text-[0.6rem] font-bold uppercase tracking-[0.3em]">Ballot</span>
                                <span class="es-vote-paper-muted font-mono text-[0.65rem]">first vote 19:04</span>
                            </div>
                            <span class="es-vote-stamp">Sealed</span>
                        </div>
                        <div class="es-vote-paper-rule mb-4" aria-hidden="true"></div>

                        <h3 class="es-vote-paper-ink mb-4 text-base font-bold leading-snug">{{ $ballotQuestion }}</h3>

                        <div aria-hidden="true">
                            @foreach ($ballot as [$bName, $bCount, $bPct, $bWriteIn])
                                <div class="es-vote-row">
                                    <span class="es-vote-box"></span>
                                    <span class="es-vote-paper-ink min-w-0 flex-1 truncate text-sm">{{ $bName }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="es-vote-perf mt-5 pt-4">
                            <p class="es-vote-paper-muted text-[0.7rem] leading-relaxed">
                                Question: editable. Status: open or closed, your call. Write-ins: yours to switch on. Choices: fixed for the life of the poll.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The declaration                                           -->
    <!-- ============================================================ -->
    <section id="count" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-vote-num mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-vote-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The declaration</p>
                <h2 class="es-balance es-vote-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Close the poll and <span class="es-vote-accent">read out the count.</span>
                </h2>
                <p class="es-vote-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Closing stops new votes and leaves the result standing. From that point the count is on show to everyone: people who never voted, and people who are not signed in. It is labelled Closed, so nobody has to guess whether it is still running. Reopen it whenever you want and voting starts again.
                </p>
            </div>

            <div class="es-vote-card p-6 sm:p-8" data-reveal="panel">
                <div class="mb-5 flex flex-wrap items-baseline justify-between gap-3">
                    <h3 class="es-vote-ink text-lg font-bold">{{ $ballotQuestion }}</h3>
                    <span class="es-vote-muted font-mono text-xs">{{ $ballotTotal }} votes &middot; Closed</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <caption class="sr-only">Final count for the poll "{{ $ballotQuestion }}": each choice with its tally, vote count and share of the total.</caption>
                        <thead>
                            <tr class="es-vote-tag">
                                <th scope="col" class="pb-3 font-bold">Choice</th>
                                <th scope="col" class="hidden pb-3 font-bold sm:table-cell">Tally</th>
                                <th scope="col" class="pb-3 text-right font-bold">Votes</th>
                                <th scope="col" class="pb-3 text-right font-bold">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ballot as $bi => [$bName, $bCount, $bPct, $bWriteIn])
                                <tr class="es-vote-rule border-t">
                                    <th scope="row" class="es-vote-ink py-3 pe-3 align-middle text-sm @if ($bi === 0) font-bold @else font-semibold @endif">
                                        {{ $bName }}@if ($bi === $myPick)<span class="es-vote-accent"> &check;</span>@endif
                                        @if ($bWriteIn)<span class="es-vote-muted block text-[0.6rem] font-normal uppercase tracking-[0.14em]">Write-in</span>@endif
                                    </th>
                                    <td class="es-vote-accent hidden py-3 pe-3 align-middle sm:table-cell">
                                        <span class="es-vote-tally" aria-hidden="true">
                                            @for ($g = 0; $g < intdiv($bCount, 5); $g++)
                                                <span class="es-vote-tally-g es-vote-tally-g-full">
                                                    @for ($m = 0; $m < 4; $m++)<span class="es-vote-mark"></span>@endfor
                                                </span>
                                            @endfor

                                            @if ($bCount % 5 > 0)
                                                <span class="es-vote-tally-g">
                                                    @for ($m = 0; $m < $bCount % 5; $m++)<span class="es-vote-mark"></span>@endfor
                                                </span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="es-vote-ink py-3 pe-3 text-right align-middle font-mono text-sm">{{ $bCount }}</td>
                                    <td class="es-vote-muted py-3 text-right align-middle font-mono text-sm">{{ $bPct }}%</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="pb-3">
                                        <div class="es-vote-track" aria-hidden="true">
                                            <span class="es-vote-fill @if ($bi === $myPick) es-vote-fill-mine @elseif ($bi === 0) es-vote-fill-lead @endif" style="--w: {{ $bPct }}%; --wd: {{ $bi * 0.12 }}s;"></span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="es-vote-muted mt-4 text-xs">
                    Percentages are the share of {{ $ballotTotal }} votes cast on this date. The leading choice is set in bold, and the solid bar with the check beside it is the choice this voter made.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Write-ins                                                 -->
    <!-- ============================================================ -->
    <section id="writein" class="scroll-mt-24 es-vote-rule border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-vote-num mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-vote-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Write-ins</p>
                <h2 class="es-balance es-vote-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Let them add the choice <span class="es-vote-accent">you did not think of.</span>
                </h2>
                <p class="es-vote-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Turn write-ins on for a poll and a signed-in guest gets a box under the choices. Whatever they add joins the ballot, up to the same ceiling of ten, and it counts as their vote, so nobody has to add a choice and then remember to pick it.
                </p>
            </div>

            <div class="grid items-start gap-8 lg:grid-cols-[1.15fr_1fr]">
                <div class="grid gap-4 sm:grid-cols-2" data-reveal-group="100">
                    <div class="es-vote-card flex h-full flex-col p-6" data-reveal="panel">
                        <h3 class="es-vote-ink mb-3 text-lg font-bold">Straight onto the ballot</h3>
                        <p class="es-vote-muted text-sm">With approval off, a write-in appears in the choices immediately, with its author's vote already on it. Good for a small room you trust.</p>
                    </div>
                    <div class="es-vote-card flex h-full flex-col p-6" data-reveal="panel">
                        <h3 class="es-vote-ink mb-3 text-lg font-bold">Or into a queue</h3>
                        <p class="es-vote-muted text-sm">With approval on, it waits in a pending list on the event editor, counted on a badge on the Polls tab. Approve it and it joins the ballot with its author's vote attached. Reject it and it is gone.</p>
                    </div>
                    <div class="es-vote-card flex h-full flex-col p-6" data-reveal="panel">
                        <h3 class="es-vote-ink mb-3 text-lg font-bold">Duplicates never reach you</h3>
                        <p class="es-vote-muted text-sm">A suggestion that matches a choice already on the ballot, or one already waiting in the queue, is refused at the point somebody types it, whatever the capitalisation.</p>
                    </div>
                    <div class="es-vote-card flex h-full flex-col p-6" data-reveal="panel">
                        <h3 class="es-vote-ink mb-3 text-lg font-bold">A nudge when they pile up</h3>
                        <p class="es-vote-muted text-sm">Switch the notification on and, once your schedule has its own email settings, owners and admins get a once-a-day email while suggestions are sitting unanswered. It is off until you ask for it, and the badge in the editor is the signal either way.</p>
                    </div>
                </div>

                <div data-reveal="panel">
                    <div class="es-vote-paper p-6">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <span class="es-vote-paper-muted text-[0.6rem] font-bold uppercase tracking-[0.3em]">Write-in slip</span>
                            <span class="es-vote-stamp es-vote-stamp-wait">Pending</span>
                        </div>
                        <div class="es-vote-paper-rule mb-4" aria-hidden="true"></div>
                        <p class="es-vote-paper-muted mb-2 text-[0.7rem] uppercase tracking-[0.14em]">Added by a guest</p>
                        <p class="es-vote-paper-ink mb-5 text-lg font-bold">The Lamplighters</p>
                        <div class="es-vote-perf pt-4">
                            <p class="es-vote-paper-muted text-[0.7rem] leading-relaxed">
                                Waiting on the event editor. Approve it and it becomes choice number four, carrying the vote of the person who wrote it in.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. One date at a time                                        -->
    <!-- ============================================================ -->
    <section id="dates" class="scroll-mt-24 es-vote-rule border-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-vote-num mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-vote-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Recurring events</p>
                <h2 class="es-balance es-vote-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    A weekly night gets a <span class="es-vote-accent">fresh ballot every week.</span>
                </h2>
                <p class="es-vote-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    If the event repeats, the vote is recorded against the date the guest is looking at. Somebody who voted at last Thursday's session votes again at this Thursday's, and each date keeps its own count instead of pooling a month of answers into one bar. On a one-off event there is simply the one count.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="90">
                @foreach ($perDate as $di => [$dDate, $dWinner, $dVotes, $dShare])
                    <div class="es-vote-card flex h-full flex-col p-5" data-reveal="panel">
                        <span class="es-vote-muted mb-3 font-mono text-xs">{{ $dDate }}</span>
                        <span class="es-vote-tag mb-1">Leading</span>
                        <span class="es-vote-ink mb-3 text-base font-bold">{{ $dWinner }}</span>
                        <div class="es-vote-track mt-auto" aria-hidden="true">
                            <span class="es-vote-fill es-vote-fill-lead" style="--w: {{ $dShare }}%; --wd: {{ $di * 0.1 }}s;"></span>
                        </div>
                        <span class="es-vote-muted mt-2 font-mono text-[0.7rem]">{{ $dShare }}% of {{ $dVotes }} votes</span>
                    </div>
                @endforeach
            </div>

            <p class="es-vote-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                The poll itself is written once, on the event. It is the counting that splits by date.
                <a href="{{ marketing_url('/features/recurring-events') }}" class="es-vote-link font-medium hover:underline">How recurring events work</a>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Where the ballot turns up (bento)                         -->
    <!-- ============================================================ -->
    <section id="where" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-vote-num mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-vote-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Where it appears</p>
                <h2 class="es-balance es-vote-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Handed out where they already are.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-vote-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-vote-ink text-xl font-bold">On the event page</h3>
                                <span class="es-vote-plan">Pro</span>
                            </div>
                            <p class="es-vote-muted mb-4">The poll sits with the event's photos, videos and comments, under the details somebody came to read. No second link to send, no separate survey to chase.</p>
                            <p class="es-vote-muted text-sm">If the event is a draft, the poll is only reachable by the people who can already see the event. The same holds for the Internal and Unlisted visibilities, which are Enterprise.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-vote-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-vote-ink text-xl font-bold">Inside the calendar</h3>
                                <span class="es-vote-plan">Pro</span>
                            </div>
                            <p class="es-vote-muted">Open an event's card on your schedule and the poll is right there, votable without leaving the calendar.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-vote-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-vote-ink text-xl font-bold">In a newsletter</h3>
                                <span class="es-vote-plan">Pro</span>
                            </div>
                            <p class="es-vote-muted">Drop a poll block into a newsletter and it fills itself with the first open poll on your next event that has one: the question, the choices, and a Vote now button back to the event.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-vote-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-vote-ink text-xl font-bold">On a phone, which is where it will be read</h3>
                                <span class="es-vote-plan">Pro</span>
                            </div>
                            <p class="es-vote-muted mb-4">Each choice is a full-width button with a real touch target, so voting from a bar or a bus stop takes one tap and no pinching.</p>
                            <p class="es-vote-muted text-sm">The bars that come back use your schedule's accent colour for your own choice and the leading one, so the poll looks like the rest of your page rather than a bolted-on widget.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-vote-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-vote-ink text-xl font-bold">In the event editor</h3>
                                <span class="es-vote-plan">Pro</span>
                            </div>
                            <p class="es-vote-muted">Write the poll, watch the vote count against it, close it, reopen it, or delete it, all from the Polls tab under Engagement.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-vote-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-vote-ink text-xl font-bold">And the reason people are signed in at all</h3>
                                <span class="es-vote-plan">Free</span>
                            </div>
                            <p class="es-vote-muted mb-4">Voting needs an account, and an account is also how somebody follows your schedule. Following is free on every plan, and it is what lets you email them later about the thing they just voted on.</p>
                            <p class="es-vote-muted text-sm">
                                To be exact about it: following does not send anybody an automatic alert when you add an event. It builds the list you write to.
                                <a href="{{ marketing_url('/features/newsletters') }}" class="es-vote-link font-medium hover:underline">How newsletters work</a>
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. The small print                                           -->
    <!-- ============================================================ -->
    <section id="spec" class="scroll-mt-24 es-vote-rule border-t py-20 lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-vote-num mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-vote-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The small print</p>
                <h2 class="es-balance es-vote-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Every number on <span class="es-vote-accent">one page.</span>
                </h2>
                <p class="es-vote-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The limits are the feature. Here they all are, so nothing surprises you after you have asked three hundred people a question.
                </p>
            </div>

            <div class="es-vote-card p-6 sm:p-8" data-reveal="panel">
                <dl class="grid gap-x-8 sm:grid-cols-2">
                    @foreach ($spec as [$specName, $specValue])
                        <div class="flex items-baseline justify-between gap-4 es-vote-rule border-b py-3">
                            <dt class="es-vote-muted text-sm">{{ $specName }}</dt>
                            <dd class="es-vote-ink flex-none text-sm font-semibold">{{ $specValue }}</dd>
                        </div>
                    @endforeach
                </dl>
                <p class="es-vote-muted mt-5 text-xs">
                    Selfhosted installations have the Pro feature set, so polls are available there without a subscription.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Related features                                         -->
    <!-- ============================================================ -->
    <section class="py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-vote-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Post-Event Feedback" description="Ask attendees how it went once the event is over" :url="marketing_url('/features/feedback')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Fan Videos & Comments" description="Let your audience add photos, video and comments to an event" :url="marketing_url('/features/fan-videos')" icon-color="rose">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the people who follow your schedule, poll block included" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="One event, a pattern of dates, and a count on each of them" :url="marketing_url('/features/recurring-events')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Custom Fields" description="Ask ticket buyers a question at the point of checkout instead" :url="marketing_url('/features/custom-fields')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-vote-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Keep reading                                             -->
    <!-- ============================================================ -->
    <section class="es-vote-rule border-t py-16 lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-vote-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Keep reading</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3" data-reveal-group="70">
                @php
                    $keepReading = [
                        [route('marketing.docs.creating_events') . '#polls', 'The Polls guide', 'Every toggle in the Polls section, written out.'],
                        [marketing_url('/features/feedback'), 'Post-event feedback', 'The other question: how was it, afterwards.'],
                        [marketing_url('/features/newsletters'), 'Newsletters', 'Write to the people who voted.'],
                        [marketing_url('/for-live-qa-sessions'), 'For live Q and A sessions', 'When the audience is the programme.'],
                        [marketing_url('/for-music-venues'), 'For music venues', 'Asking a room what it wants to hear next.'],
                        [marketing_url('/pricing'), 'Pricing', 'What the Pro plan costs and what else is in it.'],
                    ];
                @endphp
                @foreach ($keepReading as [$krHref, $krName, $krBlurb])
                    <a href="{{ $krHref }}" class="es-vote-card es-vote-hover group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-vote-hover-title es-vote-ink mb-2 text-sm font-bold transition-colors">{{ $krName }}</span>
                        <span class="es-vote-muted mb-3 text-xs leading-relaxed">{{ $krBlurb }}</span>
                        <span class="es-vote-hover-arrow es-vote-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 12. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-vote-num mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <h2 class="es-balance es-vote-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-vote-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What people ask before they put a question to a room.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-vote-card es-vote-hover group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-vote-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-vote-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-vote-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-vote-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-vote-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 13. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-vote-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(29, 78, 216, 0.3), rgba(29, 78, 216, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <!-- The last ballot on the page is the blank one: same stock,
                         same mark boxes, nothing printed on it yet. -->
                    <div class="mx-auto mb-10 max-w-sm" aria-hidden="true">
                        <div class="es-vote-paper p-5 text-start">
                            <div class="mb-3 flex items-baseline justify-between gap-3">
                                <span class="es-vote-paper-muted text-[0.6rem] font-bold uppercase tracking-[0.3em]">Ballot</span>
                                <span class="es-vote-paper-muted font-mono text-[0.65rem]">unprinted</span>
                            </div>
                            <div class="es-vote-paper-rule mb-4"></div>
                            <div class="mb-4 flex items-center gap-3">
                                <span class="es-vote-paper-muted flex-none text-[0.6rem] font-bold uppercase tracking-[0.14em]">Question</span>
                                <span class="es-vote-blank"></span>
                            </div>
                            <div>
                                <div class="es-vote-row"><span class="es-vote-box"></span><span class="es-vote-blank"></span></div>
                                <div class="es-vote-row"><span class="es-vote-box"></span><span class="es-vote-blank es-vote-blank-short"></span></div>
                                <div class="es-vote-row"><span class="es-vote-box"></span><span class="es-vote-blank"></span></div>
                            </div>
                            <div class="es-vote-perf mt-5 pt-4">
                                <p class="es-vote-paper-muted text-[0.7rem] leading-relaxed">Two choices or ten. The question is yours.</p>
                            </div>
                        </div>
                    </div>

                    <p class="es-vote-tag mb-4">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Stop guessing. <span class="es-vote-lit">Put it to a vote.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg es-vote-band-muted">
                        Claim your schedule, publish your events, and add a poll to the next one. Publishing is free forever. Polls are on the Pro plan at {{ plan_price($proMonthly) }} a month.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-vote-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm es-vote-band-muted">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Desktop dot nav -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="es-vote-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <x-marketing.related-pages />

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
