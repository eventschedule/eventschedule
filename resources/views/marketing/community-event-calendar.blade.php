<x-marketing-layout :hp="true">
    <x-slot name="title">Community Event Calendar | One Calendar for the Whole Town</x-slot>
    <x-slot name="description">Run a community event calendar for a town, neighbourhood or local paper: organizers submit, you approve, and it embeds on your site. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">Community Event Calendar</x-slot>

    {{-- Plan claims on this page, checked against docs/FEATURES.md:
         - Event requests (AI Import or Booking Form, Require Account, Require Approval, approved
           schedules) are ungated. Curator event sources are ungated.
         - Sub-schedules, recurring events, embedding the calendar, the live iCal feed, email
           subscribers and the automatic new-event digest (floored at 72 hours, outside the
           newsletter allowance), calendar sync and schedule graphics are free.
         - Newsletters: 10 / 100 / 1,000 emails a month on Free / Pro / Enterprise, each recipient
           counting as one; unlimited selfhosted or with the schedule's own email settings.
         - One team member on Free; more members (admins and viewers) are Enterprise. Custom
           domains are Enterprise. Removing branding is Pro.
         - Federation is for installs that are NOT eventschedule.com, off until the operator
           enables it, opt-in per schedule.
         - The auto import from URLs is selfhost-only; this page does not claim it.
         - The new-event digest covers only events the schedule created itself (sourced and
           listed events are left out, docs/creating-schedules#notifications-to-followers); a
           repeating newsletter whose events block is a date range lists the calendar page's own
           list (EventRepo::upcomingForGuest), up to 25 events.
         - A cancelled event is hidden from the public curator calendar (RoleController::viewGuest).
         Differentiation: /for-curators ("local events guide") is written for one person's scene
         guide. This page is for an institution running the calendar of record for a place, so it
         leads on intake from many organizers, moderation, and publishing on a site it already
         has. /for-community-centers is one building's programme. --}}

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Community Event Calendars"
        description="A shared community event calendar for a town, neighbourhood, local news site or tourism board: local organizers submit events, an editor approves them, and the calendar embeds on the site readers already visit. Free forever."
        audience="Towns, Neighbourhood Associations, Local News Sites, Tourism Boards"
        keywords="community event calendar, town event calendar, local events calendar, neighbourhood calendar, community calendar software, submit local events" />
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to start a community event calendar",
        "description": "Create a curator schedule, open it to submissions, and publish it where your readers already are.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Create a curator schedule",
                "text": "Sign up and create a schedule of the Curator type, named for the place it covers."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Add sub-schedules",
                "text": "Split the calendar into sub-schedules such as neighbourhoods, family events or sport, so readers can filter it."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Open it to organizers",
                "text": "Keep Accept requests on and share the submission link. Submissions wait for approval until you accept them, and while Require Account stays on, as it starts, schedules you trust can skip the queue."
            },
            {
                "@type": "HowToStep",
                "position": 4,
                "name": "Publish it",
                "text": "Embed the calendar on your website from Actions, Embed Schedule, and point readers to the sign-up panel and the live calendar feed."
            }
        ]
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
           Community event calendar "The Noticeboard" styles.

           CONCEPT: the board outside the town hall, where every club,
           church and library pins its own notice and one person keeps it
           tidy. The page's argument is that the calendar is fed by many
           hands and edited by one, so the hero is a week of listings with
           the organizer who sent each one named beside it, and one still
           marked as waiting for approval.

           DELIBERATELY NOT: /for-curators owns the scene guide and its
           press-sheet device; /for-community-centers owns one building's
           lobby timetable. This page is the whole place's calendar.

           COLOUR: olive. Lime-800 #3f6212 on a pale sage ground and
           lime-300 #bef264 on a near-black green one: municipal, outdoors,
           and nowhere near the purple family.
             light ground #f5f7f0: ink #161a10 16.8, muted #4b5443 7.4,
                                   accent #3f6212 7.3
             dark ground  #0d100a: ink #eef2e6 17.1, muted #a3ad98 8.2,
                                   accent #bef264 14.6
           The board is FIXED light in both modes: it is a physical board,
           and a paper-coloured card reads as one on either ground.
           ============================================================== */

        .es-cc-page { background-color: #f5f7f0; color: #161a10; }
        .dark .es-cc-page { background-color: #0d100a; color: #eef2e6; }

        .es-cc-ink { color: #161a10; }
        .dark .es-cc-ink { color: #eef2e6; }
        .es-cc-muted { color: #4b5443; }
        .dark .es-cc-muted { color: #a3ad98; }
        .es-cc-accent { color: #3f6212; }
        .dark .es-cc-accent { color: #bef264; }

        .es-cc-rule { border-top: 1px solid rgba(22, 26, 16, 0.10); }
        .dark .es-cc-rule { border-top-color: rgba(238, 242, 230, 0.10); }

        .es-cc-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #3f6212;
        }
        .dark .es-cc-tag { color: #bef264; }

        .es-cc-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            border: 1.5px solid rgba(63, 98, 18, 0.35);
            font-size: 0.8125rem;
            font-weight: 800;
            color: #3f6212;
        }
        .dark .es-cc-mark { border-color: rgba(190, 242, 100, 0.35); color: #bef264; }

        .es-cc-panel {
            background-color: #ffffff;
            border: 1px solid rgba(22, 26, 16, 0.10);
            border-radius: 1rem;
        }
        .dark .es-cc-panel {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(238, 242, 230, 0.10);
        }

        /* ---- THE BOARD. Fixed in both modes; see the contract above. ---- */
        .es-cc-board {
            background-color: #fbfcf8;
            color: #161a10;
            border: 1px solid rgba(22, 26, 16, 0.12);
            border-radius: 1rem;
            box-shadow: 0 18px 45px rgba(22, 26, 16, 0.16);
        }
        .dark .es-cc-board { box-shadow: 0 18px 45px rgba(0, 0, 0, 0.55); }
        .es-cc-board-muted { color: #4b5443; }
        .es-cc-board-accent { color: #3f6212; }
        .es-cc-board-rule { border-top: 1px solid rgba(22, 26, 16, 0.08); }
        .es-cc-day {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            flex: none;
            border-radius: 0.6rem;
            background-color: #ecf2e1;
            color: #3f6212;
            line-height: 1;
        }
        .es-cc-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.1rem 0.55rem;
            font-size: 0.6875rem;
            font-weight: 700;
        }
        .es-cc-chip-live { background-color: #ecf2e1; color: #3f6212; }
        .es-cc-chip-wait { background-color: #fef3c7; color: #78350f; }

        .es-cc-band { background-color: #10150b; }
        .es-cc-band-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #bef264;
        }
        .es-cc-band-grad {
            background-image: linear-gradient(90deg, #bef264, #67e8f9);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .es-cc-band .grid-overlay { background-image:
            linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px); }
        .es-cc-band .es-claim:focus-within { border-color: rgba(190, 242, 100, 0.55); }

        #es-cc-page a:focus-visible,
        #es-cc-page summary:focus-visible,
        #es-cc-page button:focus-visible {
            outline: 2px solid #3f6212;
            outline-offset: 2px;
        }
        .dark #es-cc-page a:focus-visible,
        .dark #es-cc-page summary:focus-visible,
        .dark #es-cc-page button:focus-visible { outline-color: #bef264; }
    </style>

    <div id="es-cc-page" class="es-cc-page">

        <!-- ============================================================ -->
        <!-- 1. Hero: the noticeboard                                     -->
        <!-- ============================================================ -->
        <section id="top" class="relative scroll-mt-24 overflow-hidden py-16 lg:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                    <div class="min-w-0">
                        <h1 class="es-balance es-cc-ink text-4xl font-black tracking-tight md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            <x-marketing.hero-eyebrow class="block es-cc-tag mb-4">Community event calendar &middot; free forever</x-marketing.hero-eyebrow>
                            Everything on in town, <span class="es-cc-accent">in one place.</span>
                        </h1>
                        <p class="es-cc-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                            The library, the bowls club, the farmers market and the jazz night at the pub all have dates to share, and none of them should have to email you a poster. Give them a form to submit to, keep the say over what goes up, and put the result on the website your readers already visit.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#3f6212] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#365314]">
                                Start your calendar
                            </a>
                            <a href="{{ marketing_url('/docs/creating-schedules') }}#engagement-requests" class="es-cc-ink inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 font-semibold transition-colors hover:border-[#3f6212] dark:border-white/15">
                                How submissions work
                            </a>
                        </div>
                        <p class="es-cc-muted mt-4 text-sm" data-reveal style="--reveal-delay: 0.2s;">
                            For towns and councils, neighbourhood groups, local news sites, tourism boards and anyone keeping a place's calendar.
                        </p>
                    </div>

                    @php
                        // Fixed, never random, so the page renders identically on every request.
                        $ccBoard = [
                            ['Sat', '12', 'Farmers market', 'Riverside Market', 'live'],
                            ['Sat', '12', 'Storytime for under-fives', 'Central Library', 'live'],
                            ['Sun', '13', 'Litter pick, Mill Lane', 'Friends of the Park', 'live'],
                            ['Tue', '15', 'Planning committee, open session', 'Town Council', 'live'],
                            ['Thu', '17', 'Quiz night in aid of the scouts', 'Submitted by J. Patel', 'wait'],
                        ];
                    @endphp
                    <div class="es-cc-board mx-auto w-full max-w-md p-6 sm:p-8" data-reveal="panel" style="--reveal-delay: 0.1s;" aria-label="Example community calendar week">
                        <div class="flex items-baseline justify-between gap-4">
                            <p class="es-cc-board-muted text-xs font-semibold uppercase tracking-widest">This week in Millbrook</p>
                            <p class="es-cc-board-accent text-xs font-semibold">5 organizers</p>
                        </div>
                        <ul class="mt-5 space-y-3">
                            @foreach ($ccBoard as [$ccDow, $ccDate, $ccName, $ccFrom, $ccState])
                                <li class="flex items-center gap-3">
                                    <span class="es-cc-day" aria-hidden="true">
                                        <span class="text-[10px] font-bold uppercase">{{ $ccDow }}</span>
                                        <span class="text-base font-black">{{ $ccDate }}</span>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold">{{ $ccName }}</span>
                                        <span class="es-cc-board-muted block truncate text-xs">{{ $ccFrom }}</span>
                                    </span>
                                    @if ($ccState === 'wait')
                                        <span class="es-cc-chip es-cc-chip-wait">Awaiting approval</span>
                                    @else
                                        <span class="es-cc-chip es-cc-chip-live">Listed</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        <div class="es-cc-board-rule mt-5 pt-4">
                            <p class="es-cc-board-muted text-xs leading-relaxed">
                                Four came in from the organizers' own schedules. One was sent through the public form and waits for an editor before it goes up.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. How events arrive                                         -->
        <!-- ============================================================ -->
        <section id="intake" class="es-cc-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-cc-mark mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-cc-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Getting the events in</p>
                    <h2 class="es-balance es-cc-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Many hands feed it. <span class="es-cc-accent">Nobody retypes a poster.</span>
                    </h2>
                    <p class="es-cc-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A community calendar lives or dies on intake. If adding an event means emailing the editor, the editor becomes the bottleneck and the calendar goes stale by spring. These are the four ways events reach yours, and three of them need nothing from you at all.
                    </p>
                </div>

                @php
                    $ccIntake = [
                        ['A public submission form', 'Anyone with your request link can send an event. On the AI Import form they paste the text of an announcement or upload a photo of the flyer, and the details are read out of it for them to check. Or switch to the Booking Form, a plain form with the fields you choose to require.'],
                        ['Organizers\' own schedules', 'A curator schedule can list talent and venue schedules as event sources. Everything those schedules publish, past and upcoming, appears on your calendar on its own, usually within minutes. The library keeps its own calendar, and yours stays current without anyone copying it across.'],
                        ['Venues that name you', 'A venue or performer that follows your calendar can make it a default curator, and everything they schedule then lands on yours too, in the queue or straight through if you trust them.'],
                        ['You, in seconds', 'For the notice that arrives on paper, paste the text or drop a photo of it into the importer and the date, time, place and description are filled in for you to confirm. If an event is already on Event Schedule, you are offered the existing one, so it is listed once, not twice.'],
                    ];
                @endphp
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="80">
                    @foreach ($ccIntake as [$ciName, $ciBody])
                        <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-cc-ink text-base font-bold">{{ $ciName }}</h3>
                            <p class="es-cc-muted mt-2 text-sm leading-relaxed">{{ $ciBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. Keeping it clean                                          -->
        <!-- ============================================================ -->
        <section id="moderation" class="es-cc-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-cc-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-cc-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Keeping it trustworthy</p>
                    <h2 class="es-balance es-cc-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Open to everyone. <span class="es-cc-accent">Edited by you.</span>
                    </h2>
                    <p class="es-cc-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A calendar with the council's name on it cannot carry a spam listing or a cancelled fair. Every setting below is on the free plan.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="90">
                    <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-cc-ink text-base font-bold">An approval queue</h3>
                        <p class="es-cc-muted mt-2 text-sm leading-relaxed">
                            Require Approval is on by default. Submissions wait on the Requests tab until you accept or decline them, and nothing reaches the public calendar before then.
                        </p>
                    </div>
                    <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-cc-ink text-base font-bold">A name behind every request</h3>
                        <p class="es-cc-muted mt-2 text-sm leading-relaxed">
                            On a curator schedule, Require Account starts on, so a submitter signs in first. A first-time submitter sets up their account, their own schedule and the event on one page, with their email confirmed by a code.
                        </p>
                    </div>
                    <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-cc-ink text-base font-bold">A shortcut for the regulars</h3>
                        <p class="es-cc-muted mt-2 text-sm leading-relaxed">
                            Put the schedules you already trust, such as the library or the leisure centre, on your approved list and, while Require Account stays on, their events skip the queue. Everyone else still waits for you.
                        </p>
                    </div>
                </div>

                <div class="es-cc-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-cc-ink text-base font-bold">Request terms, in your words</h3>
                    <p class="es-cc-muted mt-2 text-sm leading-relaxed">
                        Say what the calendar will and will not list, such as events open to the public, within the district, and no commercial sales, and the terms show above the submit button. It saves you declining the same kind of request every week.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The first six weeks: launch and getting organizers in     -->
        <!-- ============================================================ -->
        @php
            $ccLaunch = [
                ['Week 1', 'List the twenty who run most of it', 'Write down the organizers behind most of what happens: the library, the leisure centre, the churches, the school parents\' groups, the market, the sports clubs, the pubs with live music and the council itself. In most places a short list of regulars accounts for most of the dates, and each one you win keeps feeding the calendar for years.'],
                ['Weeks 1 and 2', 'Fill it before anyone sees it', 'Aim for something on most days of the next six weeks before you tell a soul. Regular sessions do the heavy lifting: a weekly storytime, a fortnightly quiz and a repair cafe on the first Saturday of the month are one recurring event each, and together they put something on every week. Paste the rest in from posters and websites with the importer.'],
                ['Weeks 2 to 4', 'Ask each organizer once, by name', 'A general "send us your events" appeal on social media rarely brings in more than a few. A short message to a named person does better, because it can say their events are already listed and show them how to keep them right. Offer two ways in: the submission form, or their own free schedule listed as an event source.'],
                ['Weeks 4 to 6', 'Launch it where people already look', 'Embed the calendar on the homepage of the site that hosts it, not three menus deep. Download the QR code from the Followers tab, which opens the calendar with its email sign-up ready, and put it on the noticeboard, the library desk and the parish magazine, with the submission link beside it.'],
            ];
        @endphp
        <section id="launch" class="es-cc-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-cc-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-cc-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The first six weeks</p>
                    <h2 class="es-balance es-cc-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        An empty calendar <span class="es-cc-accent">teaches people not to come back.</span>
                    </h2>
                    <p class="es-cc-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Readers decide in a visit or two whether a calendar is worth checking, and a launch week with a dozen listings on it answers no. Fill it first, announce it second, and make posting easier for organizers than not posting.
                    </p>
                </div>

                <ol class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="80">
                    @foreach ($ccLaunch as [$clWhen, $clName, $clBody])
                        <li class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                            <p class="es-cc-tag mb-3">{{ $clWhen }}</p>
                            <h3 class="es-cc-ink text-base font-bold">{{ $clName }}</h3>
                            <p class="es-cc-muted mt-2 text-sm leading-relaxed">{{ $clBody }}</p>
                        </li>
                    @endforeach
                </ol>

                <div class="es-cc-panel mt-8 p-6 sm:p-8" data-reveal="panel">
                    <h3 class="es-cc-ink text-base font-bold">A message that gets an organizer posting</h3>
                    <p class="es-cc-muted mt-2 text-sm leading-relaxed">
                        Short, personal, and about their events rather than your project. Change the names and send it from your own address, one organizer at a time.
                    </p>
                    <div class="es-cc-board mt-5 p-5 text-sm leading-relaxed" style="box-shadow: none;">
                        <p>Hi Sam,</p>
                        <p class="mt-3">We have started a shared what's-on calendar for Millbrook, and the bowls club's Tuesday and Saturday sessions are already on it: [calendar link]. Could you check we have them right?</p>
                        <p class="mt-3">To add new dates, use this form, which can read the details off your poster: [submission link]. Or, if you would rather keep your own calendar, make a free schedule for the club and we will list everything you add there automatically.</p>
                        <p class="mt-3">Either way, residents see your events beside everything else in town, on the council website too.</p>
                    </div>
                    <p class="es-cc-muted mt-5 text-sm leading-relaxed">
                        Ask for a correction, not a favour: people answer "is this right?" more readily than "please send us things". Whoever replies has looked at the calendar, which is the first step to using it.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Organising and publishing                                 -->
        <!-- ============================================================ -->
        <section id="publish" class="es-cc-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-cc-mark mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-cc-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Getting the events out</p>
                    <h2 class="es-balance es-cc-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Put it where people <span class="es-cc-accent">already look.</span>
                    </h2>
                    <p class="es-cc-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Nobody bookmarks a calendar. They read the town website, the local paper and their own inbox, so the calendar has to show up in all three.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="80">
                    <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-cc-ink text-base font-bold">On your own website</h3>
                        <p class="es-cc-muted mt-2 text-sm leading-relaxed">
                            Choose Embed Schedule from the Actions menu and paste the iframe onto your events page. It loads live from the calendar, so an event approved this morning is on your site this morning. Pin the <x-link href="{{ marketing_url('/features/embed-calendar') }}">embedded calendar</x-link> to a month grid or a list, or give each page of your site the one section it is about. Embedding is free on every plan.
                        </p>
                    </div>
                    <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-cc-ink text-base font-bold">Sorted into sections</h3>
                        <p class="es-cc-muted mt-2 text-sm leading-relaxed">
                            <x-link href="{{ marketing_url('/features/sub-schedules') }}">Sub-schedules</x-link> split one calendar into neighbourhoods, or into family, sport, arts and meetings. Each has its own link and can be embedded on its own, so the parks page shows only park events. Each event source can file its events under one.
                        </p>
                    </div>
                    <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-cc-ink text-base font-bold">In people's inboxes</h3>
                        <p class="es-cc-muted mt-2 text-sm leading-relaxed">
                            Readers sign up with an email address from the panel on your calendar. Confirmed subscribers get a digest of the new events the calendar adds itself, at most one every 72 hours and outside your newsletter allowance; events that arrive from organizers' own schedules are not in it. For the whole week, set a <x-link href="{{ marketing_url('/features/newsletters') }}">newsletter</x-link> to repeat every week with its events block on the next 7 days. It lists up to 25 of the week's events from everything your calendar shows, sourced events included, within 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise with each recipient counting as one, or unlimited with your own email settings.
                        </p>
                    </div>
                    <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-cc-ink text-base font-bold">In their own calendars</h3>
                        <p class="es-cc-muted mt-2 text-sm leading-relaxed">
                            Any reader can subscribe to the whole calendar as a live feed, so a moved date updates in their phone's calendar by itself. If your office already keeps events in Google Calendar or Outlook, two-way <x-link href="{{ marketing_url('/features/calendar-sync') }}">calendar sync</x-link> keeps both in step.
                        </p>
                    </div>
                </div>

                <div class="es-cc-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-cc-ink text-base font-bold">And on social media</h3>
                    <p class="es-cc-muted mt-2 text-sm leading-relaxed">
                        Generate a graphic of the upcoming events, with the text to go with it, and post the week ahead without opening a design tool. <x-link href="{{ marketing_url('/features/event-graphics') }}">Schedule graphics</x-link> are free on every plan.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Running it week to week: policy, routine, sections        -->
        <!-- ============================================================ -->
        <section id="routine" class="es-cc-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-cc-mark mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-cc-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Running it week to week</p>
                    <h2 class="es-balance es-cc-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Write the rules down. <span class="es-cc-accent">Keep the week short.</span>
                    </h2>
                    <p class="es-cc-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        The community calendars that last are run on a written listing policy and a short, fixed routine, so a decision does not depend on who is editing that Monday, and the work fits in a lunch break.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="90">
                    <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-cc-ink text-base font-bold">What to list</h3>
                        <ul class="es-cc-muted mt-3 list-disc space-y-2 ps-5 text-sm leading-relaxed">
                            <li>Open to the public, or to a whole stated audience, such as every parent at a school.</li>
                            <li>Inside the area the calendar covers, or close enough that residents go.</li>
                            <li>A set date, start time and place, or a link for an online event.</li>
                            <li>Run by someone you can reach if a detail is wrong.</li>
                            <li>Free or paid, as long as the event is the point and not the sale.</li>
                        </ul>
                    </div>
                    <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-cc-ink text-base font-bold">What to leave off</h3>
                        <ul class="es-cc-muted mt-3 list-disc space-y-2 ps-5 text-sm leading-relaxed">
                            <li>Private parties, members-only meetings and weddings.</li>
                            <li>Sales, offers and opening hours dressed up as events.</li>
                            <li>Job adverts, and appeals for money with no event attached.</li>
                            <li>Anything you would not want printed under your organization's name.</li>
                        </ul>
                        <p class="es-cc-muted mt-auto pt-4 text-sm leading-relaxed">
                            Decide now how you treat political meetings, services of worship and paid classes, because each will turn up in the first month. Put the short version in Request Terms, which submitters read before they type.
                        </p>
                    </div>
                </div>

                <div class="es-cc-panel mt-8 p-6 sm:p-8" data-reveal="panel">
                    <h3 class="es-cc-ink text-base font-bold">The twenty-minute week</h3>
                    <ol class="es-cc-muted mt-3 list-decimal space-y-2 ps-5 text-sm leading-relaxed">
                        <li><span class="es-cc-ink font-semibold">Clear the Requests tab.</span> Accept what fits the policy and decline what does not. With the new event requests email on, in Settings under Notifications, each request reaches your inbox within minutes, or in the daily noon summary, so nothing has to sit until Monday.</li>
                        <li><span class="es-cc-ink font-semibold">Read the next seven days as a reader would.</span> A listing with no start time, no place or a title like "Event" gets a note to its organizer today, or comes off your calendar until it is fixed.</li>
                        <li><span class="es-cc-ink font-semibold">Look for doubles.</span> The pub and the quizmaster both sent the Thursday quiz. Keep the one from whoever runs it and remove the other from your schedule.</li>
                        <li><span class="es-cc-ink font-semibold">Check who has gone quiet.</span> A regular with nothing listed for a month has usually stopped updating, not stopped running. One message brings them back.</li>
                        <li><span class="es-cc-ink font-semibold">Let the week send itself.</span> A newsletter set to repeat every week, with its events block on the next 7 days, goes out with that week's events and skips a week that has none. Count it before you start: four or five sends to 200 readers is 800 to 1,000 emails a month, past Pro's 100 and inside Enterprise's 1,000, and unlimited on any plan once the schedule sends through your own email settings. On eventschedule.com a send to more than 50 people also needs those settings or a verified phone number.</li>
                    </ol>
                </div>

                <div class="es-cc-panel mt-4 p-6 sm:p-8" data-reveal="panel">
                    <h3 class="es-cc-ink text-base font-bold">Sub-schedules or categories?</h3>
                    <p class="es-cc-muted mt-2 text-sm leading-relaxed">
                        Both, for different jobs. Sub-schedules are the few big divisions you would give a page of their own: areas of town, or audiences such as families and sport. Each has its own address and its own embed, and an event sits in one sub-schedule on your calendar, so keep them to a handful that do not overlap. Categories describe the type of event, for the reader's filter: twelve come ready to use, you can rename them or add your own up to 32, and they are listed alphabetically. A good test for both: if a reader would never filter by it, it should not be one.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. What kills a community calendar                          -->
        <!-- ============================================================ -->
        @php
            $ccPitfalls = [
                ['Listings that outlive the event', 'A fair that was called off stays up, somebody stands in an empty field, and they stop trusting the calendar.', 'Let organizers own their dates. When the library cancels an event on its own schedule, it leaves your public calendar too, because you list their event, not a copy of it.'],
                ['A launch with nothing on it', 'Ten listings on the day it goes live, and the first readers never come back.', 'Fill six weeks before you announce, with recurring events doing most of the work.'],
                ['One editor, one inbox', 'Everything waits for one person, who goes on holiday in August.', 'Put the organizers you trust on your approved list, so what they send from their own schedules skips the queue. On Enterprise, add a second admin.'],
                ['Spam and sales pitches', 'One listing for a get-rich seminar and readers start scrolling past everything.', 'Keep Require Account and Require Approval on, as a curator schedule starts, and write Request Terms that say what you decline.'],
                ['A calendar nobody can find', 'It sits three clicks from the homepage, and most of its visits are the editor\'s.', 'Embed it where people already read, offer the email sign-up and the live calendar feed, and post the week ahead as a graphic.'],
                ['Too many boxes', 'Twenty sub-schedules, half of them empty, and a filter nobody touches.', 'A handful of sub-schedules that do not overlap and categories for the type of event. Fold any that stay empty for a season into another.'],
            ];
        @endphp
        <section id="pitfalls" class="es-cc-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-cc-mark mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-cc-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What kills a community calendar</p>
                    <h2 class="es-balance es-cc-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Six ways they die, <span class="es-cc-accent">and the fix for each.</span>
                    </h2>
                    <p class="es-cc-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Community calendars rarely fail because of the software. They fail because they go stale, fill with noise or sit where nobody looks.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                    @foreach ($ccPitfalls as [$cpName, $cpWhat, $cpFix])
                        <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-cc-ink text-base font-bold">{{ $cpName }}</h3>
                            <p class="es-cc-muted mt-2 text-sm leading-relaxed">{{ $cpWhat }}</p>
                            <div class="mt-auto pt-4">
                                <div class="es-cc-rule pt-4">
                                    <p class="es-cc-tag mb-2">The fix</p>
                                    <p class="es-cc-ink text-sm leading-relaxed">{{ $cpFix }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Who runs one                                              -->
        <!-- ============================================================ -->
        <section id="who" class="es-cc-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-cc-mark mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                    <p class="es-cc-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who keeps one</p>
                    <h2 class="es-balance es-cc-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One calendar, <span class="es-cc-accent">four kinds of keeper.</span>
                    </h2>
                </div>

                @php
                    $ccWho = [
                        ['Towns and councils', 'The official what\'s-on for the district, embedded on the council website, with the leisure centre, library and parks on the approved list and residents submitting through the form.'],
                        ['Neighbourhood groups', 'A residents\' association or a village hall committee running the calendar for a few streets, where the street party, the litter pick and the church fete all belong on one page.'],
                        ['Local news sites and blogs', 'The listings section every local paper used to print, fed by the organizers themselves and embedded beside the stories, with a weekly digest your readers can sign up for.'],
                        ['Tourism boards', 'What a visitor can do this weekend, split into sub-schedules for food, music, family and outdoors, and embedded on the visitor site and the partner pages that link to it.'],
                    ];
                @endphp
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="80">
                    @foreach ($ccWho as [$cwName, $cwBody])
                        <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-cc-ink text-base font-bold">{{ $cwName }}</h3>
                            <p class="es-cc-muted mt-2 text-sm leading-relaxed">{{ $cwBody }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="es-cc-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-cc-ink text-base font-bold">Running a scene guide instead?</h3>
                    <p class="es-cc-muted mt-2 text-sm leading-relaxed">
                        If the calendar is your own take on a city's music or comedy, rather than the record for a whole place, the page <x-link href="{{ marketing_url('/for-curators') }}">for curators</x-link> is written for you. If it is the programme of one building, see the page <x-link href="{{ marketing_url('/for-community-centers') }}">for community centers</x-link>.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Plans and ownership                                       -->
        <!-- ============================================================ -->
        <section id="plans" class="es-cc-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-cc-mark mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                    <p class="es-cc-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What it costs</p>
                    <h2 class="es-balance es-cc-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Free for the calendar. <span class="es-cc-accent">Pay for the team.</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="90">
                    <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                        <p class="es-cc-tag">Free &middot; {{ plan_price(0) }}</p>
                        <p class="es-cc-muted mt-3 text-sm leading-relaxed">
                            Unlimited events, submissions and approval, event sources, sub-schedules, the embed, the live feed, email sign-up with the automatic digest, calendar sync and graphics. One team member.
                        </p>
                    </div>
                    <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                        <p class="es-cc-tag">Pro &middot; {{ plan_price($proMonthly) }}/mo</p>
                        <p class="es-cc-muted mt-3 text-sm leading-relaxed">
                            Removes the Event Schedule branding, raises the newsletter allowance to 100 emails a month, and adds your own questions on the submission form, an announcement banner and sponsor logos.
                        </p>
                    </div>
                    <div class="es-cc-panel flex flex-col p-6" data-reveal="panel">
                        <p class="es-cc-tag">Enterprise &middot; {{ plan_price($entMonthly) }}/mo</p>
                        <p class="es-cc-muted mt-3 text-sm leading-relaxed">
                            More team members, so several editors can share the queue, with admins who run it day to day and read-only viewers. Your own domain, such as events.yourtown.gov, and 1,000 newsletter emails a month.
                        </p>
                    </div>
                </div>

                <div class="es-cc-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-cc-ink text-base font-bold">Keep it on your own servers</h3>
                    <p class="es-cc-muted mt-2 text-sm leading-relaxed">
                        Event Schedule is open source. A council or a newspaper with its own hosting can <x-link href="{{ marketing_url('/selfhost') }}">selfhost</x-link> it, which includes every Enterprise feature at no cost. A selfhosted install can also, once its administrator turns it on, share the public events of the schedules that opt in with the listings on eventschedule.com, each linking back to the event on your own site.
                    </p>
                </div>
            </div>
        </section>

        @include('marketing.partials.pricing-nudge')

        <!-- ============================================================ -->
        <!-- 10. FAQ                                                      -->
        <!-- ============================================================ -->
        @php
            $communityFaqs = [
                ['q' => 'Is a community event calendar on Event Schedule free?', 'a' => 'Yes. The calendar, public submissions, the approval queue, event sources, sub-schedules, embedding it on your website, the live calendar feed and email sign-up with an automatic digest are all free forever. Pro removes our branding and raises the newsletter allowance; Enterprise adds more team members and a custom domain.'],
                ['q' => 'What should a community event calendar include?', 'a' => 'Every listing needs a title that says what it is, a date and start time, a place or an online link, who runs it, and the price or the word free. Around the listings, the calendar needs a way for organizers to submit, a written policy on what you list, filters by area or type of event, and ways to follow it without visiting: an email sign-up and a calendar feed people can subscribe to.'],
                ['q' => 'How do I get local organizers to submit events?', 'a' => 'List their events yourself first, then ask each one personally whether you have them right and how they would like to keep them current. Make it easier than not posting: a form that reads the details off a poster, or their own free schedule listed as an event source, so they never submit anything at all. A general appeal on social media rarely brings in more than a few.'],
                ['q' => 'How do I keep a community calendar up to date?', 'a' => 'Give the dates to the people who know them. An organizer with their own schedule, listed as an event source, updates your calendar every time they update theirs, and an event they cancel leaves your public calendar too. For everything else, clear the request queue once a week and read the next seven days as a reader would, chasing any listing with no time or place.', 'link' => [marketing_url('/docs/creating-schedules').'#event-sources', 'Event sources in the guide']],
                ['q' => 'Who should be allowed to post on a community calendar?', 'a' => 'Anyone should be able to send an event, and someone should approve it before it appears. Ask submitters to sign in so every listing has a name behind it, keep the approval queue on, publish terms that say what you list, and let organizers with a good record skip the queue through your approved list.'],
                ['q' => 'How do local organizers add their events?', 'a' => 'Through your public request link. On the AI Import form they paste the announcement or upload a photo of the flyer and check the details it reads out. On the Booking Form they fill in the fields you ask for. Organizers who keep their own schedule on Event Schedule can also be listed as event sources, so their events appear on yours without them submitting anything.', 'link' => [marketing_url('/docs/creating-schedules').'#engagement-requests', 'Request settings in the guide']],
                ['q' => 'Can I approve events before they appear?', 'a' => 'Yes, and it is on by default. Submitted events wait on the Requests tab until you accept or decline them. Schedules you add to your approved list skip the queue while Require Account is on, as it is by default, and event sources you picked yourself are listed straight away.'],
                ['q' => 'Do people need an account to submit an event?', 'a' => 'On a curator schedule Require Account starts on, so every request has a name behind it, and a first-time submitter creates their account on the same page as the event. You can turn it off to take requests from guests instead.'],
                ['q' => 'Can I put the calendar on our existing website?', 'a' => 'Yes, on every plan. Choose Embed Schedule from the Actions menu, pick a month calendar or a list, and paste the iframe code into your page. The embedded calendar updates on its own whenever an event is added or approved.', 'link' => [marketing_url('/docs/sharing').'#embed', 'Embedding in the guide']],
                ['q' => 'Can residents get the events without visiting the site?', 'a' => 'Yes. They can subscribe to the whole calendar as a live feed in Google Calendar, Apple Calendar or Outlook, or leave an email address in the sign-up panel. Confirmed subscribers get a digest of the events the calendar adds itself, at most one every 72 hours. Events pulled in from organizers\' own schedules are left out of that digest, so to send the whole week, set a newsletter to repeat weekly with its events block on the next 7 days.'],
                ['q' => 'Can I split the calendar by neighbourhood or type of event?', 'a' => 'Yes, with sub-schedules. Each one has its own link and can be embedded on its own page, and visitors can filter the full calendar by them.'],
                ['q' => 'What if the same event is submitted twice?', 'a' => 'When you import an event that is already on Event Schedule, you are offered the existing one, so a single click lists it on your calendar instead of creating a copy that would drift out of date.'],
                ['q' => 'Can several people at the council or paper share the work?', 'a' => 'A free schedule has one team member. On Enterprise you can add more: admins who run the calendar day to day and viewers with read-only access. A selfhosted install includes it at no cost.'],
                ['q' => 'How is this different from the page for curators?', 'a' => 'The curator page is written for someone building their own guide to a scene, such as the comedy in one city. This page is for an organization keeping the shared calendar for a place, where the work is taking submissions from many organizers and publishing on a site you already run. Both use the same curator schedule.'],
            ];
        @endphp
        <section id="faq" class="es-cc-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-cc-ink mb-10 text-center text-3xl font-black tracking-tight md:text-4xl" data-reveal>Community calendar questions</h2>
                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($communityFaqs as $faq)
                        <details class="es-cc-panel group p-5" data-reveal="panel">
                            <summary class="es-cc-ink flex cursor-pointer items-center justify-between gap-4 text-base font-semibold">
                                {{ $faq['q'] }}
                                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-45" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                            </summary>
                            <p class="es-cc-muted mt-3 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                            @if (! empty($faq['link']))
                                <a href="{{ $faq['link'][0] }}" class="es-cc-accent mt-3 inline-block text-sm font-semibold hover:underline">{{ $faq['link'][1] }}</a>
                            @endif
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$communityFaqs" />

        <!-- ============================================================ -->
        <!-- 11. Claim                                                    -->
        <!-- ============================================================ -->
        <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="es-cc-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-20" data-reveal="panel">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="grid-overlay absolute inset-0 opacity-25"></div>
                    </div>

                    <div class="relative z-10">
                        <p class="es-cc-band-tag mb-6">Free forever</p>
                        <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                            Give the whole town <span class="es-cc-band-grad">one calendar</span>.
                        </h2>
                        <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                            Organizers submit, you approve, and your website shows the result.
                        </p>

                        <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-town" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg bg-white px-8 py-4 text-lg font-semibold text-[#10150b] transition-colors hover:bg-gray-100">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                        <p class="mt-6 text-sm text-gray-400">No credit card required</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <x-marketing.related-pages />

    {{-- Load-bearing, not decoration: marketing.css hides every [data-reveal] element behind
         html.es-anim, so a page that sets that class and never loads the reveal observer renders
         completely blank below the nav. --}}
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
