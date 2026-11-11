<x-marketing-layout :hp="true">
    <x-slot name="title">Event Lineup: Running Order, Set Times and a Page per Act</x-slot>
    <x-slot name="description">How to build an event lineup: running order, set times, changeovers and announcing in waves. Every act you name is credited and linked to a page of their own.</x-slot>
    <x-slot name="breadcrumbTitle">Event Lineup</x-slot>

    {{-- Claims on this page, checked against the code, the user guide and docs/FEATURES.md:
         - The lineup is the event form's Participants tab. Every participant is named on the public
           event page, and one with a page of its own, claimed or not, is linked to it
           (docs/creating-events#participants and #claim). Free.
         - Naming a performer or a venue who is not on Event Schedule creates a schedule for them
           at once ("Claim a page created for you" in FEATURES.md, free). Until it is claimed the
           page opens with claim_strip_title / claim_strip_body, lists up to twenty upcoming public
           dates, carries no contact details, no follow button and no tickets, and is kept out of
           search engines. Claiming is signing in with a verified account on the contact the page
           carries (User::claimSchedule(), Role::isClaimable()). "This is not me" takes the page
           down for whoever holds that contact and is recorded for review for anyone else.
         - The invitation email is hosted only ("On eventschedule.com"), sent once per tick of the
           box, and never for a draft.
         - Approved list: a claim by email or by the Claim button carries the schedules that
           already listed the act onto its approved list; a claim by verifying a phone number
           does not. So that sentence names the two ways it holds.
         - The logo wall is Role::logoWallRoles(): venues for a talent or curator schedule, talent
           for a venue; a profile image is required; only publicly listed events count (not a
           draft, unlisted, cancelled or password-protected one); a schedule somebody runs
           qualifies only once the date is accepted on it; ordered by logo_wall_order, capped at
           36. It is a Header Image choice and needs the Banner header style. Free.
         - Sponsors are Pro ($role->isPro()). A logo is required; name, link and a tier of Gold,
           Silver or Bronze are optional; config('app.max_sponsors') per schedule and per event.
           The Show sponsors switch is NOT plan-gated. An event shows the schedule's sponsors,
           none, or its own. The newsletter builder has a Sponsors block.
         - Event sources are a curator schedule's ("Curator event sources" in FEATURES.md, free):
           only talent and venue schedules can be a source; everything they publish, past and
           upcoming, is listed; drafts, internal and unlisted events and anything the source has
           not accepted are left out; a source can be filed under a sub-schedule; each one shows
           how many of its events are on the calendar (docs/creating-schedules#event-sources).
         - Running order and announcing (#running-order, #waves): the Agenda tab is free; a part has
           an optional Start Time and End Time, a required name and a description; the form's order is
           the page's (sort_order); a timed agenda draws as a timeline (event/show-guest #gp-agenda);
           Show times off hides the time fields and SAVING with it off removes the times
           (docs/creating-events#agenda). AI agenda import is Enterprise (#agenda-import). Editing the
           agenda or the Participants tab triggers no notify prompt: that is only a one-time event's
           date or time, or any event's venue or online link (docs/creating-events#notify-attendees).
           Removing a participant detaches it (EventRepo roles()->sync()). Draft is members-only.
           The new-event digest goes to confirmed subscribers on publish, can be turned off, and
           never re-sends for a later edit (docs/managing-schedules, FEATURES "Automatic new-event
           announcements"). The interest list behind the Notify me card is off until switched on,
           free, and outside the newsletter allowance (docs/tickets#interest-list).
         No price is quoted here, so nothing needs plan_price(). --}}

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule - Event Lineup"
        description="Name the acts and the venue on an event and each is credited on the event page and linked to a page of their own, which they can claim. A wall of venue logos across your schedule's header is free, and sponsor and partner logos come with Pro."
        audience="Performers, venues and event curators"
        keywords="event lineup, running order, set times, event lineup page, performer pages, claim a page, venue logo wall, sponsor logos, event sponsors" />
    </x-slot>

    @php
        $maxSponsors = config('app.max_sponsors');

        $lineupFaqs = [
            ['q' => 'What is an event lineup?', 'a' => 'The people performing, speaking or hosting at an event, as opposed to the people attending it. On Event Schedule you name them on the event\'s Participants tab, and every one of them appears on the public event page, each linked to a page of their own.'],
            ['q' => 'What is the difference between a lineup and a running order?', 'a' => 'The lineup is who is performing. The running order is when: each act\'s start and finish time, with the changeovers between them, worked out between doors and the curfew. Many events announce the lineup weeks ahead and the running order in the final week. On Event Schedule the lineup is the Participants tab and the running order is the Agenda tab, one part per set with a start and end time, both free on every plan.'],
            ['q' => 'Where does the headliner go in a lineup?', 'a' => 'At a gig or a concert, last: they play when the room is fullest, and the support acts warm it up. At a club night the strongest slot is usually the busiest hour, which is often the middle of the night rather than the end. At a conference a well-known speaker usually opens the day, with another strong session at the end to keep people in the room. Whatever the format, name the headliner in the event\'s title, because that is what people see in a listing or a shared link.'],
            ['q' => 'When should you announce set times?', 'a' => 'Usually in the last week before the event. Announcing them earlier invites people to come for one act and leave, and every change after that has to be announced again. Until then you can show the agenda in order without times: on Event Schedule, switch Show times off on the Agenda tab, then switch it back on and type the times when you publish them.'],
            ['q' => 'How long should changeovers between acts be?', 'a' => 'As a rule of thumb, 15 to 20 minutes between full bands, longer when a headliner brings their own equipment or needs a line check, and 5 to 10 minutes between DJs who share the same setup. Ask each act how long they need when you book them, add the changeovers to the running order as gaps, and keep 10 minutes spare before the curfew.'],
            ['q' => 'Do the acts I list need an account?', 'a' => 'No. Naming somebody who is not on Event Schedule creates a page for them there and then, in the name you typed, with every date you list them on. They can claim it later by signing in with the email address or phone number you entered for them, and from then on it is an ordinary schedule they run themselves.'],
            ['q' => 'What does a page show before it is claimed?', 'a' => 'It opens by saying which schedule created it and that the act has not claimed it yet, so nobody mistakes it for a page they built. Under that it lists up to twenty upcoming public dates, each credited to the schedule that added it. It carries no contact details, no follow button and no tickets, and search engines are kept away from it until it is claimed.'],
            ['q' => 'What if the page is not theirs, or they do not want it?', 'a' => 'The page has a This is not me button beside Claim this page. Somebody who holds the email address or phone number on the page can take it down straight away. Anybody else is asked to sign in and their request is recorded for review, so the button cannot be aimed at a competitor.'],
            ['q' => 'How do other schedules\' events get onto a curator\'s guide?', 'a' => 'By naming them as event sources. On a curator schedule you list the talent and venue schedules you cover, and everything they publish, past and upcoming, appears on your calendar on its own, each new event within a few minutes of going live. What a source keeps private stays off your guide, and it is free on every plan.'],
            ['q' => 'What is the venue logo wall?', 'a' => 'A header for your schedule page made of logos instead of a picture. On a talent or curator schedule it shows the venues hosting your public events, and on a venue schedule it shows the acts playing there. Each logo links to that schedule\'s own page, you set the order by dragging, and it is free on every plan.'],
            ['q' => 'Are sponsor logos free?', 'a' => 'Sponsor and partner logos are a Pro feature. The lineup, the pages made for the acts you list and the logo wall are free on every plan, and a selfhosted install includes all four. The switch that hides your sponsors stays available on every plan, so logos added on Pro can always be taken off the page.'],
            ['q' => 'Can one event show different sponsors?', 'a' => 'Yes. An event shows its schedule\'s sponsors unless you choose otherwise in the Sponsors row of the event\'s Page content tab: no sponsors at all, or a list that belongs to that event alone. An event\'s own list changes nothing on the schedule.'],
        ];

        // The acts and rooms in the pictures below. Initials on a tile stand in for a profile
        // image; the colours stay inside the site's blue-to-green family.
        $billActs = [
            ['PL', 'The Paper Lanterns', 'Folk, four piece', '#1d4ed8'],
            ['MO', 'Mara Okoro', 'Singer and guitarist', '#0e7490'],
            ['SB', 'The Slow Burners', 'Support', '#047857'],
        ];
        $wallRooms = [
            ['RH', 'Riverside Hall', '#1d4ed8'],
            ['CE', 'The Corn Exchange', '#0e7490'],
            ['OM', 'Old Mill Stage', '#047857'],
            ['HB', 'Harbour Bar', '#b45309'],
            ['LY', 'The Lyric', '#0369a1'],
            ['NG', 'Northgate Arts', '#0f766e'],
        ];
        $sponsorTiles = [
            ['Mill Street Brewing', 'Gold', 'is-gold'],
            ['Hudson Savings', 'Gold', 'is-gold'],
            ['Okoro Strings', 'Silver', 'is-silver'],
            ['Riverside Print', 'Bronze', 'is-bronze'],
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
           Event lineup: "The Bill".

           CONCEPT: the names on a poster. An event is a bill of several
           names, and this page follows a name from the event page it is
           printed on, to the page it links to, to the wall and the band
           of logos on the schedule. The page is written on the house
           style kit (partials/hp-kit) and adds only what the kit has no
           word for: the four pictures of guest pages.

           DELIBERATELY NOT: /event-landing-page owns the anatomy of the
           whole event page; /features/booking-requests owns an act asking
           a venue for a date; /features/white-label owns taking our name
           off. This page is the other names on yours.

           The four pictures are FIXED white in both modes: each is a
           picture of a guest page, which a visitor may see in either
           theme. Their inks are literals for that reason, all on #ffffff:
             ink #111827 17.7, muted #4b5563 7.6, link #1d4ed8 6.7
           ============================================================== */

        .es-bill-pill {
            display: inline-flex;
            flex: none;
            align-items: center;
            border-radius: 9999px;
            padding: 0.125rem 0.625rem;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .es-bill-pill.is-free { background-color: #d1fae5; color: #065f46; }
        .dark .es-bill-pill.is-free { background-color: rgba(110, 231, 183, 0.14); color: #6ee7b7; }
        .es-bill-pill.is-pro { background-color: #dbeafe; color: #1e40af; }
        .dark .es-bill-pill.is-pro { background-color: rgba(125, 165, 255, 0.16); color: #a9c3ff; }

        .es-bill-num { background-color: rgba(78, 129, 250, 0.14); color: var(--hp-blue); }

        /* ---- THE PICTURES. Fixed in both modes; see the contract above. ---- */
        .es-bill-shot {
            background-color: #ffffff;
            color: #111827;
            border: 1px solid rgba(17, 24, 39, 0.12);
            border-radius: 1.25rem;
            box-shadow: 0 18px 45px rgba(17, 24, 39, 0.16);
            overflow: hidden;
        }
        .dark .es-bill-shot { box-shadow: 0 18px 45px rgba(0, 0, 0, 0.55); }
        .es-bill-shot-muted { color: #4b5563; }
        .es-bill-shot-link { color: #1d4ed8; }
        .es-bill-shot-label {
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #4b5563;
        }
        .es-bill-shot-rule { border-top: 1px solid #e5e7eb; }
        .es-bill-flyer {
            background-image: linear-gradient(135deg, #0c2a6b 0%, #1d4ed8 50%, #0891b2 100%);
            color: #ffffff;
        }
        .es-bill-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            background-color: #f9fafb;
        }
        .es-bill-av {
            display: inline-flex;
            flex: none;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 9999px;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .es-bill-notice {
            border: 1px solid #bfdbfe;
            border-radius: 0.75rem;
            background-color: #eff6ff;
            padding: 0.9rem 1rem;
        }
        .es-bill-go { background-color: #1d4ed8; color: #ffffff; }
        .es-bill-quiet { border: 1px solid #d1d5db; color: #374151; }

        /* The wall: logos on the header of a schedule page. */
        .es-bill-wall {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.6rem;
            padding: 1.1rem;
            background-image: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 55%, #ecfeff 100%);
        }
        @media (min-width: 640px) {
            .es-bill-wall { grid-template-columns: repeat(6, minmax(0, 1fr)); padding: 1.5rem; }
        }
        .es-bill-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            min-height: 5.25rem;
            padding: 0.6rem 0.4rem;
            border: 1px solid rgba(17, 24, 39, 0.08);
            border-radius: 0.85rem;
            background-color: #ffffff;
            font-size: 0.6875rem;
            font-weight: 600;
            line-height: 1.25;
            text-align: center;
            color: #374151;
        }
        .es-bill-logo .es-bill-av { border-radius: 0.6rem; }

        /* The band: sponsors, each with its tier. */
        .es-bill-band {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
        @media (min-width: 640px) {
            .es-bill-band { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
        .es-bill-sponsor {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            text-align: center;
        }
        .es-bill-mark {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 4.25rem;
            padding: 0.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.85rem;
            background-color: #f9fafb;
            font-size: 0.8125rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            line-height: 1.2;
            color: #111827;
        }
        .es-bill-tier {
            border-radius: 0.375rem;
            padding: 0.1rem 0.4rem;
            font-size: 0.6875rem;
            font-weight: 600;
        }
        .es-bill-tier.is-gold { background-color: #fef9c3; color: #854d0e; }
        .es-bill-tier.is-silver { background-color: #e5e7eb; color: #374151; }
        .es-bill-tier.is-bronze { background-color: #ffedd5; color: #9a3412; }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: the headline, and the four parts by number          -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero hp-hero">
        <div class="hp-hero-sky" aria-hidden="true"></div>

        <div class="hp-hero-copy">
            <h1 class="hp-h1">
                <x-marketing.hero-eyebrow class="es-fade-up es-d-1 hp-eyebrow">
                    <span class="hp-live" aria-hidden="true"><i></i></span>
                    Event lineup, with a page for every name on it
                </x-marketing.hero-eyebrow>
                <span class="es-mask"><span class="es-mask-line">Everyone on the bill,</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="hp-ink-grad">credited by name</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 hp-sub">
                Add the acts on an event and each one is named on its page and linked to a page of their own, even an act that has never heard of Event Schedule. The rooms you play and the people backing you get their logos on your schedule.
            </p>

            <div class="es-fade-up es-d-3 hp-hero-actions">
                <a href="{{ marketing_url('/docs/creating-events') }}#participants" class="hp-btn hp-btn-ghost is-still">
                    Read the guide
                </a>
                <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary">
                    Start for free
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>

            <nav class="es-fade-up es-d-4 hp-toc" aria-label="On this page">
                @foreach ([['bill', 'The lineup'], ['running-order', 'Running order'], ['waves', 'Announcing it'], ['pages', 'Pages to claim'], ['sources', 'Sources'], ['wall', 'Logo wall'], ['sponsors', 'Sponsors']] as $tocIndex => [$tocId, $tocLabel])
                    <a href="#{{ $tocId }}"><b>{{ sprintf('%02d', $tocIndex + 1) }}</b>{{ $tocLabel }}</a>
                @endforeach
            </nav>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The whole lineup                                          -->
    <!-- ============================================================ -->
    <section id="bill" class="hp-sec hp-alt">
        <div class="hp-wrap">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                <div class="min-w-0">
                    <div class="hp-head">
                        <span class="hp-kicker" data-reveal>The lineup</span>
                        <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">Name them once. <span class="hp-ink-grad">They are on the bill.</span></h2>
                        <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                            The Participants tab of an event names the people performing, speaking or hosting. Every one of them appears on the public event page, and each name is a link to that act's own page.
                        </p>
                    </div>
                    <ul class="hp-checks" data-reveal-group="70">
                        @foreach ([
                            'Pick one of your own schedules, or choose Someone New and type a name. Nothing else is required.',
                            'Add an email address and anybody already on Event Schedule with it is offered as a match, so you link the act who exists instead of making a second one.',
                            'The date goes on each act\'s own page as well. On a page somebody runs it waits for them to accept it, unless they have already approved your schedule.',
                            'It is for the people on the stage. The people in the room are tickets and registrations, and nobody lists those in public.',
                        ] as $billCheck)
                            <li data-reveal>
                                <span class="hp-ok" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></span>
                                <span>{{ $billCheck }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-8 flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-400" data-reveal>
                        <span class="es-bill-pill is-free">Free</span>
                        <span>On every plan, for as many acts as the night has.</span>
                    </p>
                </div>

                <div class="es-bill-shot mx-auto w-full max-w-md" data-reveal="panel" aria-label="Example lineup on an event page">
                    <div class="es-bill-flyer flex h-32 flex-col justify-end p-5">
                        <p class="text-xs font-semibold uppercase tracking-widest opacity-80">Sat 10 Oct &middot; Riverside Hall</p>
                        <p class="mt-1 text-2xl font-black leading-tight">Harvest Night</p>
                    </div>
                    <div class="space-y-5 p-5">
                        <div>
                            <p class="es-bill-shot-label mb-3">Lineup</p>
                            <div class="space-y-2">
                                @foreach ($billActs as [$actInitials, $actName, $actNote, $actColour])
                                    <div class="es-bill-row">
                                        <span class="es-bill-av" style="background-color: {{ $actColour }};" aria-hidden="true">{{ $actInitials }}</span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-bold">{{ $actName }}</span>
                                            <span class="es-bill-shot-muted block truncate text-xs">{{ $actNote }}</span>
                                        </span>
                                        <span class="es-bill-shot-link flex-none text-xs font-semibold">View page</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="es-bill-shot-rule pt-4">
                            <p class="es-bill-shot-label mb-3">Venue</p>
                            <div class="es-bill-row">
                                <span class="es-bill-av" style="background-color: #0369a1;" aria-hidden="true">RH</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-bold">Riverside Hall</span>
                                    <span class="es-bill-shot-muted block truncate text-xs">12 Mill Street, Hudson</span>
                                </span>
                                <span class="es-bill-shot-link flex-none text-xs font-semibold">View page</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2a. The running order: set times, and the night itself       -->
    <!-- ============================================================ -->
    @php
        // A worked running order. Sets 30 + 40 + 65 = 135 minutes, changeovers 15 + 20 = 35,
        // 8:00 PM to an 11:00 PM curfew is 180 minutes, so 10 are left as the buffer.
        $runRows = [
            ['7:30 PM', 'Doors', 'The time on the ticket and in calendars', false],
            ['8:00 PM', 'The Slow Burners', '30 minutes, opener', true],
            ['8:30 PM', 'Changeover', '15 minutes', false],
            ['8:45 PM', 'Mara Okoro', '40 minutes, main support', true],
            ['9:25 PM', 'Changeover', '20 minutes', false],
            ['9:45 PM', 'The Paper Lanterns', '65 minutes, headliner, ends 10:50 PM', true],
            ['11:00 PM', 'Curfew', '10 minutes of buffer before it', false],
        ];
        $runSteps = [
            ['Start from the fixed edges', 'Write down doors, the curfew (when the sound has to stop) and anything else nobody can move: a late train, a room booked after you, a shared backline that leaves at midnight. The running order fills the space between them.'],
            ['Place the headliner first', 'At a gig the headliner plays last, when the room is fullest. At a club night the busiest hour is often the middle of the night, not the end. At a conference a known speaker opens the day, and another strong one closes it so people stay. Pick their slot, then build the rest around it.'],
            ['Size the sets by billing', 'A common shape is 30 minutes for an opener, 40 to 45 for main support and 60 to 90 for a headliner. Agree each length with the act when you book them, not on the night, and write it down.'],
            ['Leave room for changeovers', 'Allow 15 to 20 minutes between full bands, more when the headliner brings their own gear, and 5 to 10 between DJs who share equipment. A changeover you did not plan for comes out of somebody\'s set.'],
            ['Keep a buffer, and decide who gives it back', 'Nights run late. Hold 10 minutes before the curfew, and agree in advance that if time is lost it comes out of the support sets, never the headliner\'s.'],
        ];
        $nightFacts = [
            ['One running order for everyone', 'Send the same times to every act, the sound engineer and the door. The agenda on the event page is the audience\'s copy of that sheet, so when one changes, change both.'],
            ['When it runs late', 'Open the event on your phone, change the times on its Agenda tab and save, and the event page shows the new times from then on. Editing the agenda emails nobody, so a change people need to know about, such as the headliner going on later, also goes where your audience is looking.'],
            ['When an act drops out', 'Remove them on the Participants tab and take their part off the agenda, and the date comes off their page too. Nothing is sent to ticket buyers for a lineup change, so if they were the reason people bought, tell buyers yourself.'],
            ['Doors are not a set', 'Give the event the doors time as its start, because that is what lands on the ticket and in calendars, and let the agenda say when the first act is on. People arrive for doors and stay for the times on the page.'],
        ];
    @endphp
    <section id="running-order" class="hp-sec">
        <div class="hp-wrap">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                <div class="min-w-0">
                    <div class="hp-head">
                        <span class="hp-kicker" data-reveal>Running order</span>
                        <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">Who plays when, <span class="hp-ink-grad">worked back from the curfew.</span></h2>
                        <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                            A lineup says who is on the bill. A running order says when each of them is on stage, and it is built from the times nobody can move. Five steps hold for a bar gig, a club night and a one-day conference alike.
                        </p>
                    </div>
                    <ol class="mt-8 space-y-6" data-reveal-group="80">
                        @foreach ($runSteps as $stepIndex => [$stepTitle, $stepBody])
                            <li class="flex gap-4" data-reveal>
                                <span class="es-bill-num flex h-9 w-9 flex-none items-center justify-center rounded-full text-sm font-bold" aria-hidden="true">{{ $stepIndex + 1 }}</span>
                                <div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $stepTitle }}</h3>
                                    <p class="mt-1 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $stepBody }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <div class="es-bill-shot mx-auto w-full max-w-md p-5" data-reveal="panel" aria-label="Example running order for a three-act night">
                    <p class="es-bill-shot-label mb-3">Running order &middot; Harvest Night</p>
                    <div class="space-y-2">
                        @foreach ($runRows as [$runTime, $runName, $runNote, $runIsSet])
                            <div class="es-bill-row">
                                <span class="w-16 flex-none text-xs font-bold">{{ $runTime }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm {{ $runIsSet ? 'font-bold' : 'es-bill-shot-muted font-semibold' }}">{{ $runName }}</span>
                                    <span class="es-bill-shot-muted block text-xs">{{ $runNote }}</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <p class="es-bill-shot-muted es-bill-shot-rule mt-4 pt-4 text-xs leading-relaxed">From the first note to the curfew is 180 minutes. The sets take 135 (30 + 40 + 65), the changeovers 35 (15 + 20), and 10 are left for the night running late.</p>
                </div>
            </div>

            <div class="hp-card mt-12 p-6 sm:p-8" data-reveal="panel">
                <div class="grid gap-8 md:grid-cols-2">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Putting it on the event page</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                            Name each act on the Participants tab, then open the Agenda tab and add one part per set, with its Start Time and End Time. Drag the parts into the running order, and the order on the form is the order on the page, shown as a timeline. Not ready to publish times yet? Switch Show times off and the agenda lists the sets in order without them. Saving with it off removes any times already typed, so keep the real ones on your own sheet until you announce them. Both tabs are free on every plan, and <x-link href="{{ marketing_url('/docs/creating-events') }}#agenda-import">reading a set list or a photo of a printed lineup</x-link> into the agenda with AI is Enterprise.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Billing the headliner</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                            The event's name is what shows in a listing, a calendar and a shared link, so put the headliner in it: "The Paper Lanterns with Mara Okoro" says more than "Harvest Night" to somebody scrolling past. On a festival or conference with a name of its own, keep the name and let the short description carry the top two or three acts.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-12 hp-head">
                <h3 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white" data-reveal>On the night</h3>
            </div>
            <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="70">
                @foreach ($nightFacts as [$factTitle, $factBody])
                    <div class="hp-card flex flex-col p-6" data-reveal="panel">
                        <h4 class="text-base font-bold text-gray-900 dark:text-white">{{ $factTitle }}</h4>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $factBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2b. Announcing the lineup in waves                           -->
    <!-- ============================================================ -->
    @php
        $waveCards = [
            ['Wave one', 'The date, the room and the headliner', 'The headliner sells the early tickets, so lead with them, with tickets on sale the same day if you can. If sales open later, switch on the Notify me card first: people who find the page early can leave an email address and are told when tickets go on sale.'],
            ['Wave two', 'The support acts, a few weeks on', 'Each act you add brings fans who had not heard of the headliner. Name them on the Participants tab and save, and the event page shows them at once. Give each act the link: the date goes on their own page as well (on a page they run, once they accept it, unless your schedule is on their approved list), and on eventschedule.com an act with no account yet can be sent an email that you added them.'],
            ['Wave three', 'Set times, in the final week', 'Holding the times back until close to the night keeps people coming for the whole bill rather than one set, and leaves room for the changes that always come. When you are ready, switch Show times on in the agenda and type them in.'],
        ];
    @endphp
    <section id="waves" class="hp-sec hp-alt">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>Announcing it</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">Announce in waves, <span class="hp-ink-grad">not all at once.</span></h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                    One announcement is one moment of attention. Three give people a reason to look again, and each one reaches a different crowd of fans. A pattern many promoters use runs like this.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-4 lg:grid-cols-3" data-reveal-group="70">
                @foreach ($waveCards as $waveIndex => [$waveLabel, $waveTitle, $waveBody])
                    <div class="hp-card flex flex-col p-6" data-reveal="panel">
                        <div class="flex items-center gap-3">
                            <span class="es-bill-num flex h-9 w-9 flex-none items-center justify-center rounded-full text-sm font-bold" aria-hidden="true">{{ $waveIndex + 1 }}</span>
                            <span class="hp-tag">{{ $waveLabel }}</span>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-gray-900 dark:text-white">{{ $waveTitle }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $waveBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="hp-card mt-8 p-6 sm:p-8" data-reveal="panel">
                <div class="grid gap-8 md:grid-cols-2">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Build it before you announce it</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                            Set the event to Draft and only your schedule's members can see it, so the page, the tickets and the first names can be ready and checked days ahead. Switch it to Public on announcement day. Booked acts you are not announcing yet stay off the Participants tab until their wave, because everything on it is public the moment the event is.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Who hears about each wave</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                            Publishing a public event sends your confirmed email subscribers the automatic digest of new events, unless you have turned it off. Adding names to it later sends them nothing new, so waves two and three go out as a <x-link href="{{ marketing_url('/features/newsletters') }}">newsletter</x-link> (its allowance is counted in emails a month and grows with the plan) and on your own channels. The <x-link href="{{ marketing_url('/docs/tickets') }}#interest-list">interest list</x-link> behind the Notify me card is free on every plan and does not count against that allowance.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Pages for the acts you list                               -->
    <!-- ============================================================ -->
    @php
        $pageSteps = [
            ['It exists the moment you save', 'Name a performer or a venue who has never signed up and a page is made for them there and then. It carries the name you typed and every date you list them on, whether or not you invite them: it is what lets their name appear on your event at all.'],
            ['It says whose it is', 'Until it is claimed the page opens with the schedule that created it and says the act has not claimed it yet. It lists up to twenty upcoming public dates, each credited to the schedule that added it, with no contact details, no follow button and no tickets. Search engines are kept away until it is claimed.'],
            ['You can tell them', 'On eventschedule.com, tick the box beside their email address and they get one email saying you added them, with a link to their page. Nothing is sent for a draft, or to somebody who already has an account.'],
            ['They claim it, or say it is not them', 'Claim this page asks them to sign in with a verified account on the email address or phone number the page carries. That is the whole check, so the contact you enter decides who can take it over. This is not me takes the page down for whoever holds that contact.'],
        ];
    @endphp
    <section id="pages" class="hp-sec">
        <div class="hp-wrap">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                <div class="es-bill-shot order-last mx-auto w-full max-w-md p-5 lg:order-first" data-reveal="panel" aria-label="Example of a page nobody has claimed yet">
                    <div class="es-bill-notice">
                        <p class="text-sm font-bold">This page was created by Riverside Hall</p>
                        <p class="es-bill-shot-muted mt-1 text-xs leading-relaxed">The Paper Lanterns has not claimed it yet. Each date below is credited to the schedule that added it.</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="es-bill-go rounded-lg px-3 py-1.5 text-xs font-semibold">Claim this page</span>
                            <span class="es-bill-quiet rounded-lg px-3 py-1.5 text-xs font-semibold">This is not me</span>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center gap-3">
                        <span class="es-bill-av" style="background-color: #1d4ed8;" aria-hidden="true">PL</span>
                        <p class="text-lg font-bold">The Paper Lanterns</p>
                    </div>

                    <div class="mt-4 space-y-2">
                        @foreach ([['Sat 10 Oct', 'Harvest Night', 'Riverside Hall'], ['Fri 23 Oct', 'Autumn Sessions', 'The Corn Exchange'], ['Sat 7 Nov', 'Bonfire Weekender', 'Old Mill Stage']] as [$dateDay, $dateName, $dateBy])
                            <div class="es-bill-row">
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-bold">{{ $dateName }}</span>
                                    <span class="es-bill-shot-muted block truncate text-xs">{{ $dateDay }} &middot; Added by {{ $dateBy }}</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="min-w-0">
                    <div class="hp-head">
                        <span class="hp-kicker" data-reveal>Pages to claim</span>
                        <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">Not signed up yet? <span class="hp-ink-grad">They have a page anyway.</span></h2>
                    </div>
                    <ol class="mt-8 space-y-6" data-reveal-group="80">
                        @foreach ($pageSteps as $stepIndex => [$stepTitle, $stepBody])
                            <li class="flex gap-4" data-reveal>
                                <span class="es-bill-num flex h-9 w-9 flex-none items-center justify-center rounded-full text-sm font-bold" aria-hidden="true">{{ $stepIndex + 1 }}</span>
                                <div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $stepTitle }}</h3>
                                    <p class="mt-1 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $stepBody }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                    <p class="mt-8 flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-400" data-reveal>
                        <span class="es-bill-pill is-free">Free</span>
                        <span>For you to make, and for them to claim.</span>
                    </p>
                </div>
            </div>

            <div class="hp-card mt-12 p-6 sm:p-8" data-reveal="panel">
                <div class="grid gap-8 md:grid-cols-2">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Once it is theirs</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                            A claimed page is an ordinary schedule, and they can change everything on it. The dates you listed stay where they are. When they claim it by email or with the button, the schedules that had already listed them are carried onto their approved list, so your next date appears without waiting. Anybody else who lists them from then on sends a request.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">A page about somebody else</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                            The page is public from the moment it exists, so use a name they would recognise and a contact address that is really theirs. Give a booking agent's address and it is the agent who can claim the page, not the act.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Event sources: a curator's guide that fills itself        -->
    <!-- ============================================================ -->
    @php
        $sourceRows = [
            ['RH', 'Riverside Hall', 'Venue', '42 events', '#1d4ed8'],
            ['CE', 'The Corn Exchange', 'Venue', '18 events', '#0e7490'],
            ['PL', 'The Paper Lanterns', 'Talent', '9 events', '#047857'],
        ];
        $sourceFacts = [
            ['Venues and acts, not other guides', 'Search by name and pick a talent or a venue schedule. Only those two kinds can be a source, so one guide never chains onto another.'],
            ['Only what they made public', 'Drafts, internal events, unlisted events and anything a schedule has not accepted are left out. What stays private there stays private on yours.'],
            ['Filed where you want it', 'Send everything from one source to a sub-schedule, so visitors can filter the club from the theatre. Change it later and that source\'s events move with it.'],
            ['You can see it working', 'Each source shows how many of its events are on your calendar right now, past and upcoming, so you can tell at a glance whether it is feeding your guide.'],
        ];
    @endphp
    <section id="sources" class="hp-sec hp-alt">
        <div class="hp-wrap">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                <div class="min-w-0">
                    <div class="hp-head">
                        <span class="hp-kicker" data-reveal>Event sources</span>
                        <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">Run a guide? <span class="hp-ink-grad">Let it fill itself.</span></h2>
                        <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                            A curator schedule is the page for a town, a scene or a festival. List the venue and talent schedules you cover as its sources and everything they publish shows up on your calendar on its own: what they have already run as well as what is coming, each new event within a few minutes of going live.
                        </p>
                    </div>
                    <p class="mt-8 flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-400" data-reveal>
                        <span class="es-bill-pill is-free">Free</span>
                        <span>On every plan. For events nobody has listed yet, take them as <x-link href="{{ marketing_url('/features/booking-requests') }}">requests</x-link> instead.</span>
                    </p>
                </div>

                <div class="es-bill-shot mx-auto w-full max-w-md p-5" data-reveal="panel" aria-label="Example list of event sources on a curator schedule">
                    <p class="es-bill-shot-label mb-3">Event sources</p>
                    <div class="space-y-2">
                        @foreach ($sourceRows as [$sourceInitials, $sourceName, $sourceType, $sourceCount, $sourceColour])
                            <div class="es-bill-row">
                                <span class="es-bill-av" style="background-color: {{ $sourceColour }};" aria-hidden="true">{{ $sourceInitials }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-bold">{{ $sourceName }}</span>
                                    <span class="es-bill-shot-muted block truncate text-xs">{{ $sourceType }}</span>
                                </span>
                                <span class="es-bill-shot-muted flex-none text-xs font-semibold">{{ $sourceCount }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="es-bill-shot-muted es-bill-shot-rule mt-4 pt-4 text-xs leading-relaxed">Everything these three publish is on Hudson Tonight, without anybody copying a listing.</p>
                </div>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="70">
                @foreach ($sourceFacts as [$factTitle, $factBody])
                    <div class="hp-card flex flex-col p-6" data-reveal="panel">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $factTitle }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $factBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The venue logo wall                                       -->
    <!-- ============================================================ -->
    @php
        $wallFacts = [
            ['It fills itself', 'The wall is read from your events: every venue on a public event of yours that has a profile image. Play somewhere new and that room joins the wall.'],
            ['Only what is public', 'A draft, an unlisted event, a cancelled one or one behind a password never puts a name on the wall. A venue that runs its own page appears once the date has been accepted onto it.'],
            ['In your order', 'Drag the list under the dropdown to choose who comes first, and each logo links to that schedule\'s own page. The wall shows up to 36.'],
            ['One setting', 'It is one of the Header Image choices, drawn by the Banner header style. Until one venue has a profile image the form tells you the wall is still empty.'],
        ];
    @endphp
    <section id="wall" class="hp-sec">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>Logo wall</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">The rooms you play, <span class="hp-ink-grad">across the top of your page.</span></h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                    Choose Venue logo wall as your header and the top of your schedule page becomes the venues hosting your events, each one a link. On a venue's schedule it runs the other way and is called Talent logo wall: the acts that play there.
                </p>
            </div>

            <div class="es-bill-shot mx-auto mt-12 w-full max-w-4xl" data-reveal="panel" aria-label="Example venue logo wall on a schedule page">
                <div class="es-bill-wall">
                    @foreach ($wallRooms as [$roomInitials, $roomName, $roomColour])
                        <div class="es-bill-logo">
                            <span class="es-bill-av" style="background-color: {{ $roomColour }};" aria-hidden="true">{{ $roomInitials }}</span>
                            <span>{{ $roomName }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 p-5">
                    <div class="flex items-center gap-3">
                        <span class="es-bill-av" style="background-color: #1d4ed8;" aria-hidden="true">PL</span>
                        <div>
                            <p class="text-base font-bold">The Paper Lanterns</p>
                            <p class="es-bill-shot-muted text-xs">Six venues this season</p>
                        </div>
                    </div>
                    <span class="es-bill-go rounded-lg px-4 py-2 text-xs font-semibold">Follow</span>
                </div>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="70">
                @foreach ($wallFacts as [$factTitle, $factBody])
                    <div class="hp-card flex flex-col p-6" data-reveal="panel">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $factTitle }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $factBody }}</p>
                    </div>
                @endforeach
            </div>

            <p class="mt-8 flex flex-wrap items-center justify-center gap-3 text-sm text-gray-600 dark:text-gray-400" data-reveal>
                <span class="es-bill-pill is-free">Free</span>
                <span>On every plan, as are the <x-link href="{{ marketing_url('/features/white-label') }}#yours">logo, colour and type</x-link> of the rest of your page.</span>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Sponsor and partner logos                                 -->
    <!-- ============================================================ -->
    @php
        $sponsorFacts = [
            ['A logo, and what you add to it', 'Each sponsor needs a logo. A name, a link to their site and a tier of Gold, Silver or Bronze are optional, and a schedule holds up to ' . $maxSponsors . '.'],
            ['In your order', 'Drag a sponsor by its handle to set where it stands in the band. Nothing reaches the public page until you save.'],
            ['A band that suits the page', 'Leave it on the page\'s own panel, make it transparent so your background shows through, or give it a colour of yours. The text adjusts to stay readable.'],
            ['Other sponsors for one event', 'An event shows its schedule\'s sponsors unless you say otherwise: none at all, or a list that belongs to that event alone and changes nothing on the schedule.'],
            ['Hidden, not lost', 'One switch takes the sponsors off your schedule page and your event pages and keeps every logo for when you turn it back on. That switch stays on every plan.'],
            ['In the newsletter too', 'The newsletter builder has a Sponsors block, so the names on your page can go out to your subscribers as well.'],
        ];
    @endphp
    <section id="sponsors" class="hp-sec hp-alt">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>Sponsors and partners</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">The people funding it, <span class="hp-ink-grad">thanked in public.</span></h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                    A band of sponsor and partner logos on your schedule page and your event pages, in the order you choose, each with its name, its tier and a link to its own site.
                </p>
            </div>

            <div class="es-bill-shot mx-auto mt-12 w-full max-w-3xl p-6 sm:p-8" data-reveal="panel" aria-label="Example sponsors band on a schedule page">
                <p class="mb-6 text-center text-lg font-bold">Our Sponsors</p>
                <div class="es-bill-band">
                    @foreach ($sponsorTiles as [$sponsorName, $sponsorTier, $sponsorTierClass])
                        <div class="es-bill-sponsor">
                            <span class="es-bill-mark">{{ $sponsorName }}</span>
                            <span class="es-bill-tier {{ $sponsorTierClass }}">{{ $sponsorTier }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                @foreach ($sponsorFacts as [$factTitle, $factBody])
                    <div class="hp-card flex flex-col p-6" data-reveal="panel">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $factTitle }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $factBody }}</p>
                    </div>
                @endforeach
            </div>

            <p class="mt-8 flex flex-wrap items-center justify-center gap-3 text-sm text-gray-600 dark:text-gray-400" data-reveal>
                <span class="es-bill-pill is-pro">Pro</span>
                <span>The heading over the band is one of the words <x-link href="{{ marketing_url('/features/custom-labels') }}">custom labels</x-link> can rename, and a <x-link href="{{ marketing_url('/selfhost') }}">selfhosted</x-link> install includes all of it.</span>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Questions, the strip and the finale                       -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$lineupFaqs" />
    <x-marketing.hp-faq :items="$lineupFaqs">Lineup <span class="hp-ink-grad">questions</span></x-marketing.hp-faq>

    <x-marketing.related-pages />

    <x-marketing.hp-finale lead="A page for every act you name and a wall for every room you play, free on every plan." placeholder="your-schedule">
        Put the whole bill <span class="hp-ink-grad">on the page</span>
    </x-marketing.hp-finale>

    {{-- Load-bearing, not decoration: marketing.css hides every [data-reveal] element behind
         html.es-anim, so a page that sets that class and never loads the reveal observer renders
         completely blank below the nav. --}}
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
