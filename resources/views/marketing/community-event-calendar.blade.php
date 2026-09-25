<x-marketing-layout>
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
                "text": "Keep Accept requests on and share the submission link. Submissions wait for approval until you accept them, and schedules you trust can skip the queue."
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
                            Put the schedules you already trust, such as the library or the leisure centre, on your approved list and their events skip the queue, while everyone else still waits for you.
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
        <!-- 4. Organising and publishing                                 -->
        <!-- ============================================================ -->
        <section id="publish" class="es-cc-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-cc-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
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
                            Readers sign up with an email address from the panel on your calendar. Confirmed subscribers get a digest of the new events you publish, at most one every 72 hours and outside your newsletter allowance. Write a weekly <x-link href="{{ marketing_url('/features/newsletters') }}">newsletter</x-link> of your own on top, 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise with each recipient counting as one, or unlimited with your own email settings.
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
        <!-- 5. Who runs one                                              -->
        <!-- ============================================================ -->
        <section id="who" class="es-cc-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-cc-mark mb-6" data-reveal aria-hidden="true"><span>04</span></div>
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
        <!-- 6. Plans and ownership                                       -->
        <!-- ============================================================ -->
        <section id="plans" class="es-cc-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-cc-mark mb-6" data-reveal aria-hidden="true"><span>05</span></div>
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
        <!-- 7. FAQ                                                       -->
        <!-- ============================================================ -->
        @php
            $communityFaqs = [
                ['q' => 'Is a community event calendar on Event Schedule free?', 'a' => 'Yes. The calendar, public submissions, the approval queue, event sources, sub-schedules, embedding it on your website, the live calendar feed and email sign-up with an automatic digest are all free forever. Pro removes our branding and raises the newsletter allowance; Enterprise adds more team members and a custom domain.'],
                ['q' => 'How do local organizers add their events?', 'a' => 'Through your public request link. On the AI Import form they paste the announcement or upload a photo of the flyer and check the details it reads out. On the Booking Form they fill in the fields you ask for. Organizers who keep their own schedule on Event Schedule can also be listed as event sources, so their events appear on yours without them submitting anything.', 'link' => [marketing_url('/docs/creating-schedules').'#engagement-requests', 'Request settings in the guide']],
                ['q' => 'Can I approve events before they appear?', 'a' => 'Yes, and it is on by default. Submitted events wait on the Requests tab until you accept or decline them. Schedules you add to your approved list skip the queue, and event sources you picked yourself are listed straight away.'],
                ['q' => 'Do people need an account to submit an event?', 'a' => 'On a curator schedule Require Account starts on, so every request has a name behind it, and a first-time submitter creates their account on the same page as the event. You can turn it off to take requests from guests instead.'],
                ['q' => 'Can I put the calendar on our existing website?', 'a' => 'Yes, on every plan. Choose Embed Schedule from the Actions menu, pick a month calendar or a list, and paste the iframe code into your page. The embedded calendar updates on its own whenever an event is added or approved.', 'link' => [marketing_url('/docs/sharing').'#embed', 'Embedding in the guide']],
                ['q' => 'Can residents get the events without visiting the site?', 'a' => 'Yes. They can subscribe to the whole calendar as a live feed in Google Calendar, Apple Calendar or Outlook, or leave an email address in the sign-up panel. Confirmed subscribers get a digest of newly published events, at most one every 72 hours.'],
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
        <!-- 8. Claim                                                     -->
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
