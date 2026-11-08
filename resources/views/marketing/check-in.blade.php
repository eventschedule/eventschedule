<x-marketing-layout :hp="true">
    <x-slot name="title">Event Check-in App: Free QR Code Check-in on Any Phone</x-slot>
    <x-slot name="description">Check guests in by scanning QR code tickets on any phone, free on every plan. How to staff the door, handle screenshots and wrong nights, and watch the count.</x-slot>
    <x-slot name="breadcrumbTitle">Check-in</x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Check-in App: QR Code Check-in on Any Phone"
        description="How QR code ticket check-in works, how many scanners a door needs, what to do with the awkward cases, and Event Schedule's free door scanner with its live Pro dashboard." />
    </x-slot>

    {{-- Motion gate: the hidden pre-reveal states below only apply when this class is present, so
         no-JS visitors, crawlers and reduced-motion users always see the whole page. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           Check-in "The Head Count" styles.

           CONCEPT: THE ONE NUMBER SOMEBODY IS WATCHING. Every other
           reporting surface in the product is READ, once, afterwards.
           This is the only one that is WATCHED - it refreshes itself
           every ten seconds while you stand at the door - and the thing
           being watched is a single figure going up against a known
           total. So the page is that figure, at the size it deserves,
           and everything else on the page is subordinate to it.

           WHY NOT A DOOR, A TURNSTILE OR A CLICKER. All three are
           taken, and all three are about ADMITTING one person.
           /features/ticketing owns "The Turnstile", /for-nightclubs owns
           "The Door" together with the clicker and the rope. The
           dashboard is not the act of admitting anyone; it is the count
           that results, which is why the object here is the SCREEN on
           the desk rather than the doorway in front of it.

           DELIBERATELY NOT a dashboard of panels: /features/analytics
           owns "The Dashboard" and is the after-the-fact read. The
           distinction is load-bearing and the page says it out loud.

           THE SCREEN IS A PHYSICAL OBJECT, so .es-head-screen is FIXED
           dark in both colour modes - a door tablet at eight in the
           evening is dark whatever the reader's theme. Only the room
           around it changes.

           COLOUR: ONE HUE ON THE WHOLE PAGE, and it is the brand's.
           Every other hue in the /features/* family is spent -
           analytics emerald, ticketing sky, newsletters cyan, boost and
           custom-fields orange, gift-cards / embed-tickets /
           allocated-seating blue, passes teal - and violet is banned.
           Rather than force a ninth, the identity here is the NUMBER
           and an achromatic ops surface. State is carried by SHAPE and
           VALUE (filled bar versus outline, solid versus dashed), never
           by a second colour, so it survives a mono screen and a
           colour-blind reader.

           Measured against the grounds this page actually paints:
             light ground #f3f4f6: ink #0f1115 17.17, muted #4b5563 6.87,
                                   accent #1e40af 7.93
             dark ground  #0c0e12: ink #e9eaee 16.07, muted #9aa1ad 7.43,
                                   accent #93c5fd 10.71
             screen #15181d (fixed): ink #e8eaed 14.76, muted #8f97a3 6.04,
                                   accent #93c5fd 9.87
           text-gray-500 is never used - #6b7280 measures 4.5 exactly on
           this ground, with no headroom. Use .es-head-muted.
           ============================================================== */

        .es-head-page { background-color: #f3f4f6; color: #0f1115; }
        .dark .es-head-page { background-color: #0c0e12; color: #e9eaee; }

        .es-head-ink { color: #0f1115; }
        .dark .es-head-ink { color: #e9eaee; }
        .es-head-muted { color: #4b5563; }
        .dark .es-head-muted { color: #9aa1ad; }
        .es-head-accent { color: #1e40af; }
        .dark .es-head-accent { color: #93c5fd; }

        .es-head-rule { border-top: 1px solid rgba(15, 17, 21, 0.09); }
        .dark .es-head-rule { border-top-color: rgba(233, 234, 238, 0.10); }

        .es-head-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #1e40af;
        }
        .dark .es-head-tag { color: #93c5fd; }

        .es-head-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            border: 1.5px solid rgba(30, 64, 175, 0.35);
            font-size: 0.8125rem;
            font-weight: 800;
            color: #1e40af;
        }
        .dark .es-head-mark { border-color: rgba(147, 197, 253, 0.35); color: #93c5fd; }

        .es-head-panel {
            background-color: #ffffff;
            border: 1px solid rgba(15, 17, 21, 0.10);
            border-radius: 1rem;
        }
        .dark .es-head-panel {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(233, 234, 238, 0.10);
        }

        /* ---- THE SCREEN. Fixed in both modes; see the contract above. ---- */
        .es-head-screen {
            background-color: #15181d;
            color: #e8eaed;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 1rem;
            box-shadow: 0 18px 45px rgba(6, 8, 12, 0.45);
        }
        .es-head-screen-ink { color: #e8eaed; }
        .es-head-screen-muted { color: #8f97a3; }
        .es-head-screen-accent { color: #93c5fd; }
        .es-head-screen-rule { border-top: 1px solid rgba(255, 255, 255, 0.09); }

        /* Tabular figures so the count cannot jiggle as it changes - the one
           typographic requirement a number being watched actually has. */
        .es-head-figure {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum" 1;
            letter-spacing: -0.03em;
        }

        /* The fill. Arrived is a solid bar; still expected is the dashed
           remainder of the same track, so the split reads without colour. */
        .es-head-track {
            position: relative;
            height: 0.75rem;
            border-radius: 9999px;
            border: 1px dashed rgba(255, 255, 255, 0.28);
            overflow: hidden;
        }
        .es-head-fill {
            position: absolute;
            inset-inline-start: 0;
            top: 0;
            bottom: 0;
            border-radius: 9999px;
            background-color: #93c5fd;
            transform-origin: left;
        }
        [dir="rtl"] .es-head-fill { transform-origin: right; }
        /* Transition on the ALWAYS-ACTIVE rule; only the pre-state is gated, so
           no-JS and reduced-motion rest FULL rather than empty. */
        .es-head-fill { transition: transform 1s ease; }
        html.es-anim [data-reveal]:not(.is-revealed) .es-head-fill { transform: scaleX(0); }

        .es-head-band { background-color: #0f1318; }
        .es-head-band-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #93c5fd;
        }
        .es-head-band-grad {
            background-image: linear-gradient(90deg, #93c5fd, #7dd3fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        /* Shared classes carry their own .dark rules in marketing.css and would
           otherwise change inside a band that has none. */
        .es-head-band .grid-overlay { background-image:
            linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px); }
        .es-head-band .es-claim:focus-within { border-color: rgba(147, 197, 253, 0.55); }

        /* marketing.css:248 only rings a.feature-card|bento-card|persona-card. */
        #es-head-page a:focus-visible,
        #es-head-page summary:focus-visible,
        #es-head-page button:focus-visible {
            outline: 2px solid #1e40af;
            outline-offset: 2px;
        }
        .dark #es-head-page a:focus-visible,
        .dark #es-head-page summary:focus-visible,
        .dark #es-head-page button:focus-visible { outline-color: #93c5fd; }
    </style>

    <div id="es-head-page" class="es-head-page">

        <!-- ============================================================ -->
        <!-- 1. Hero: the answer, and the screen                          -->
        <!-- ============================================================ -->
        <section id="top" class="relative scroll-mt-24 overflow-hidden py-16 lg:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <h1 class="es-balance es-head-ink text-4xl font-black tracking-tight md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            <x-marketing.hero-eyebrow class="block es-head-tag mb-4">Event check-in app &middot; QR code check-in, free on every plan</x-marketing.hero-eyebrow>
                            Scan tickets on any phone. <span class="es-head-accent">Know how many are in.</span>
                        </h1>
                        <p class="es-head-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                            Every ticket and every free registration carries a QR code. At the door, a phone's browser reads it, asks whether the order is paid, for tonight and not used yet, and answers in green, amber or red. One scan admits the whole order. There is no app to install and no scanner to rent.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1e40af] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#1b3894]">
                                Start for free
                            </a>
                            <a href="#door" class="es-head-ink inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 font-semibold transition-colors hover:border-[#1e40af] dark:border-white/15">
                                How to run the door
                            </a>
                        </div>
                        <p class="es-head-muted mt-4 text-sm" data-reveal style="--reveal-delay: 0.2s;">
                            Scanning is free on every plan, Free included. The live count beside it is Pro.
                        </p>
                    </div>

                    <div class="es-head-screen p-6 sm:p-8" data-reveal="panel" style="--reveal-delay: 0.1s;">
                        <div class="flex items-baseline justify-between gap-4">
                            <p class="es-head-screen-muted text-xs font-semibold uppercase tracking-widest">Saturday &middot; doors 19:30</p>
                            <p class="es-head-screen-muted text-xs">updated 4s ago</p>
                        </div>

                        <p class="es-head-figure es-head-screen-ink mt-5 text-6xl font-black leading-none sm:text-7xl">
                            184<span class="es-head-screen-muted text-3xl font-bold"> / 300</span>
                        </p>
                        <p class="es-head-screen-accent mt-2 text-sm font-semibold">61% checked in</p>

                        <div class="es-head-track mt-5" role="img" aria-label="184 of 300 attendees checked in, 61 per cent.">
                            <div class="es-head-fill" style="width: 61%;"></div>
                        </div>

                        @php
                            // Fixed, never random: the page has to render identically on every
                            // request for the band-diff verifier to mean anything.
                            $checkinTypes = [
                                ['General admission', 132, 210],
                                ['Balcony', 41, 60],
                                ['Guest list', 11, 30],
                            ];
                        @endphp
                        <div class="es-head-screen-rule mt-6 space-y-2.5 pt-5">
                            @foreach ($checkinTypes as [$typeName, $typeIn, $typeTotal])
                                <div class="flex items-baseline justify-between gap-4 text-sm">
                                    <span class="es-head-screen-muted">{{ $typeName }}</span>
                                    <span class="es-head-figure es-head-screen-ink font-semibold">{{ $typeIn }} / {{ $typeTotal }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="es-head-screen-rule mt-5 pt-4">
                            <p class="es-head-screen-muted text-xs">Last through the door &middot; 19:52 A. Okonkwo &middot; Balcony B12</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. How QR check-in works, end to end                         -->
        <!-- ============================================================ -->
        <section id="how-it-works" class="es-head-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-head-mark mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-head-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How QR ticket check-in works</p>
                    <h2 class="es-balance es-head-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The code is a key. <span class="es-head-accent">The record is the lock.</span>
                    </h2>
                    <p class="es-head-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A QR code on a ticket is only a long, unguessable address. It does not know whether it has been used. What keeps a door honest is the record of the first scan, kept in one place, that every scanning phone asks.
                    </p>
                </div>

                @php
                    $checkinSteps = [
                        ['1', 'The code is issued with the order', 'When somebody buys or registers, the confirmation email and their ticket page show a QR code unique to that order. It holds a long, unguessable link to that order\'s ticket page and nothing else, so treat a photo of it like the ticket itself.'],
                        ['2', 'A phone at the door reads it', 'Any phone with a camera and a browser will do. The scanner reads the code and sends it to the server, together with the event the door is set to.'],
                        ['3', 'The server checks four things', 'Is this an order for this event? For tonight? Paid, and not cancelled or refunded? Already used? The answer comes back with the name on the order and what it holds.'],
                        ['4', 'The answer is a colour', 'Green: in, and the scanner moves on to the next guest by itself. Amber: a person decides. Red: no, with the reason. The first green is written down, so the second phone to see that code knows.'],
                    ];
                @endphp
                <ol class="grid gap-4 md:grid-cols-2" data-reveal-group="90">
                    @foreach ($checkinSteps as [$stepNo, $stepTitle, $stepBody])
                        <li class="es-head-panel flex flex-col p-6" data-reveal="panel">
                            <p class="es-head-tag mb-3">Step {{ $stepNo }}</p>
                            <h3 class="es-head-ink text-lg font-bold">{{ $stepTitle }}</h3>
                            <p class="es-head-muted mt-2 text-sm leading-relaxed">{{ $stepBody }}</p>
                        </li>
                    @endforeach
                </ol>

                <div class="es-head-panel mt-8 p-6 sm:p-8" data-reveal="panel">
                    <h3 class="es-head-ink text-lg font-bold">Why a screenshot cannot get two people in</h3>
                    <p class="es-head-muted mt-2 text-sm leading-relaxed">
                        A screenshot of a code is the code, so there is no point trying to stop people taking one, and every reason to let them: a screenshot opens with no signal and no hunting through email. What stops a forwarded screenshot is the check in step 3. The first scan admits the order; the second says <span class="es-head-ink font-semibold">Already checked in</span> and the time it first came through. That only works if every phone asks the same record, which is why a door scanner that checks codes against a list stored on each phone can be beaten by two friends at two doors.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. How many people on the door                               -->
        <!-- ============================================================ -->
        @php
            // Rule of thumb, shown with its working: half the guests arrive in the busiest
            // 30 minutes, and a lane clears one guest every 8 seconds (7.5 a minute). So the
            // lanes needed at the peak are guests / 60 / 7.5 = guests / 450, rounded up.
            $laneRows = [
                [100, '1.7', '0.2', 1, 2],
                [300, '5', '0.7', 1, 2],
                [800, '13.3', '1.8', 2, 3],
                [2000, '33.3', '4.4', 5, 6],
                [5000, '83.3', '11.1', 12, 13],
            ];
        @endphp
        <section id="door" class="es-head-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-head-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-head-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Staffing the door</p>
                    <h2 class="es-balance es-head-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        How many scanners <span class="es-head-accent">does the door need?</span>
                    </h2>
                    <p class="es-head-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        The total number of guests matters less than how many arrive at once. Nobody queues at 18:00 for a 20:00 show; half the room turns up in the last half hour. Size the door for that half hour.
                    </p>
                </div>

                <div class="grid gap-4 lg:grid-cols-5" data-reveal-group="90">
                    <div class="es-head-panel flex flex-col p-6 lg:col-span-2" data-reveal="panel">
                        <p class="es-head-tag mb-3">The rule of thumb</p>
                        <p class="es-head-figure es-head-ink text-3xl font-black">guests &divide; 450</p>
                        <p class="es-head-muted mt-2 text-sm leading-relaxed">
                            rounded up, is how many scanning lanes the peak needs. Then add one, for the phone that dies, the guest who cannot find the email, and the break somebody will need.
                        </p>
                        <div class="es-head-rule mt-5 pt-5">
                            <p class="es-head-ink text-sm font-semibold">Where 450 comes from</p>
                            <ul class="es-head-muted mt-2 space-y-1.5 text-sm leading-relaxed">
                                <li>Half the guests arrive in the busiest 30 minutes, so the peak is guests &divide; 60 a minute.</li>
                                <li>One lane clears a guest about every 8 seconds, from stepping up to walking in: 7.5 a minute.</li>
                                <li>(guests &divide; 60) &divide; 7.5 = guests &divide; 450.</li>
                            </ul>
                        </div>
                        <div class="es-head-rule mt-auto pt-5">
                            <p class="es-head-ink text-sm font-semibold">Put your own numbers in</p>
                            <p class="es-head-muted mt-2 text-sm leading-relaxed">
                                lanes = guests &times; share arriving in the peak half hour &times; seconds a guest &divide; 1,800. A show with a hard start where 70% arrive at once is guests &divide; 320. Time the first ten guests at your next door and use what you saw.
                            </p>
                        </div>
                    </div>

                    <div class="es-head-panel flex flex-col overflow-x-auto p-6 lg:col-span-3" data-reveal="panel">
                        <table class="w-full text-left text-sm">
                            <caption class="es-head-muted mb-4 text-left text-sm">Lanes at the peak, worked with the rule of thumb. A rough guide, not a measurement.</caption>
                            <thead>
                                <tr class="es-head-ink">
                                    <th scope="col" class="pb-3 pe-3 font-semibold">Guests</th>
                                    <th scope="col" class="pb-3 pe-3 font-semibold">Arriving a minute at the peak</th>
                                    <th scope="col" class="pb-3 pe-3 font-semibold">Lanes busy</th>
                                    <th scope="col" class="pb-3 font-semibold">Scanners, with a spare</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($laneRows as [$laneGuests, $lanePerMinute, $laneLoad, $laneLanes, $laneStaff])
                                    <tr class="es-head-rule">
                                        <td class="es-head-figure es-head-ink py-3 pe-3 font-semibold">{{ number_format($laneGuests) }}</td>
                                        <td class="es-head-figure es-head-muted py-3 pe-3">{{ $lanePerMinute }}</td>
                                        <td class="es-head-figure es-head-muted py-3 pe-3">{{ $laneLoad }} &rarr; {{ $laneLanes }}</td>
                                        <td class="es-head-figure es-head-accent py-3 font-bold">{{ $laneStaff }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="es-head-muted mt-auto pt-5 text-sm leading-relaxed">
                            Two things move it in your favour. One scan admits a whole order, so a door of couples and groups clears faster than one guest per scan. And scanning is rarely the slowest step: if there is a bag check, an ID check or a coat rail at the same table, time that instead, because the scanner will be waiting on it.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Before the doors open                                     -->
        <!-- ============================================================ -->
        @php
            $doorChecklist = [
                ['Scan a real ticket at the door itself', 'Register yourself for the event and scan it where the door will be, an hour before. The office has signal. The doorway, the basement and the far end of a field often do not.'],
                ['Set every phone to the right event and night', 'The scanner opens on today\'s event with sales, and the choice is remembered on that phone. Check it anyway on a recurring night, where a ticket for the next night stops in amber.'],
                ['Sign in on each door phone', 'Each person scanning signs in as themselves and allows the camera once; after that the camera starts by itself on that phone.'],
                ['Charge everything, and bring a power bank', 'The screen stays on while the camera runs, which is what you want at a door and what empties a battery by the second hour.'],
                ['Plan for the dark', 'Where the phone offers it, use Light, the torch, in a dark doorway, and ask guests to turn their screen brightness up. A dim or cracked screen is the code that fails to read most often.'],
                ['Decide the connection', 'If mobile signal at the door is weak, put the door phones on the venue\'s wifi or a hotspot, and test that too. A scan needs a connection to check the code.'],
                ['Keep a list you can search', 'Open Sales on a laptop at the desk. On Pro, the check-in dashboard finds anyone by name, email or seat, and the attendee list exports as CSV for a printed copy.'],
                ['Agree the rules before the queue does', 'Who decides on a ticket for another night, a forwarded screenshot, a name that is not on the list, and re-entry. Amber answers wait for a person; red ones cannot be waved through.'],
            ];
        @endphp
        <section id="checklist" class="es-head-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-head-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-head-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Before the doors open</p>
                    <h2 class="es-balance es-head-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Eight checks, <span class="es-head-accent">an hour before.</span>
                    </h2>
                    <p class="es-head-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Most bad doors are decided before the first guest arrives: a phone at 20%, a scanner set to last week's event, a basement with no signal. None of it takes more than a few minutes to catch.
                    </p>
                </div>

                <ol class="grid gap-4 md:grid-cols-2" data-reveal-group="70">
                    @foreach ($doorChecklist as $i => [$checkTitle, $checkBody])
                        <li class="es-head-panel flex gap-4 p-6" data-reveal="panel">
                            <span class="es-head-figure es-head-accent shrink-0 text-lg font-black" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <h3 class="es-head-ink text-base font-bold">{{ $checkTitle }}</h3>
                                <p class="es-head-muted mt-1.5 text-sm leading-relaxed">{{ $checkBody }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. The awkward ones                                          -->
        <!-- ============================================================ -->
        @php
            $doorCases = [
                [
                    'title' => 'The same code, twice',
                    'what' => 'Usually a screenshot sent to a friend, or one phone held up a second time.',
                    'do' => 'The code got somebody in once. Ask the second person for the name on the order, and look at the time of the first scan: two minutes ago and a different face is a forwarded ticket.',
                    'shows' => 'Already checked in, with the time of the first scan in the schedule\'s timezone. Nothing changes.',
                ],
                [
                    'title' => 'A ticket for another night',
                    'what' => 'A weekly night, a run of shows, a festival: the guest has Saturday\'s ticket on Friday.',
                    'do' => 'Decide the policy before doors. Letting them in is fine if there is room; tell them their own night will read as used.',
                    'shows' => 'Amber, Ticket for another date, naming the date it is for. Admit anyway checks them in, and each use is recorded in the audit log. A ticket for a night that has already passed is red: its check-in period has ended.',
                ],
                [
                    'title' => '"I bought one, I promise"',
                    'what' => 'No email to show, or a name that does not come up.',
                    'do' => 'Ask which email they used: it is usually a work address or a partner\'s order. Search by that, not by name.',
                    'shows' => 'On Pro, Find someone at the door searches names, emails and seats. Search only finds them. To check them in, open their order from Sales and scan its QR code.',
                ],
                [
                    'title' => 'A group on one order',
                    'what' => 'One person bought four tickets, and the other three are still parking.',
                    'do' => 'If groups arrive together, one scan for all of them is the fastest door there is. If they arrive apart, give each guest a code of their own.',
                    'shows' => 'Admit 4 people. With Individual tickets turned on, each attendee gets their own email and code; that is free for registrations and Pro for paid tickets.',
                ],
                [
                    'title' => 'The guest list',
                    'what' => 'Comps, press, the band\'s plus-ones, sponsors: people who never went through checkout.',
                    'do' => 'Put them in the system so they count, rather than on a clipboard the count never sees.',
                    'shows' => 'On Pro, import them from a CSV file or type them in, up to 5,000 rows at a time. With Send Email on, each gets the same confirmation and code as a buyer.',
                ],
                [
                    'title' => 'Refunded, cancelled, unpaid',
                    'what' => 'The confirmation email does not vanish from their inbox when the order does.',
                    'do' => 'Nothing at the door: the scan asks for the order\'s status at that moment, not the one it had when the email went out.',
                    'shows' => 'Red, with the reason: not paid, cancelled, refunded or expired. A partial refund leaves the order paid, so it still scans.',
                ],
            ];
        @endphp
        <section id="awkward-cases" class="es-head-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-head-mark mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-head-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">At the door</p>
                    <h2 class="es-balance es-head-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The six awkward ones, <span class="es-head-accent">and what to do.</span>
                    </h2>
                    <p class="es-head-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Most guests are a green and a smile. The queue is made by the rest, so it pays to know the answer before somebody is standing in front of you.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                    @foreach ($doorCases as $case)
                        <div class="es-head-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-head-ink text-lg font-bold">{{ $case['title'] }}</h3>
                            <p class="es-head-muted mt-2 text-sm leading-relaxed">{{ $case['what'] }}</p>
                            <p class="es-head-ink mt-3 text-sm leading-relaxed"><span class="font-semibold">What to do.</span> {{ $case['do'] }}</p>
                            <div class="es-head-rule mt-auto pt-4">
                                <p class="es-head-tag mb-1.5">The scanner shows</p>
                                <p class="es-head-muted text-sm leading-relaxed">{{ $case['shows'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="es-head-muted mt-8 text-center text-sm">
                    Every answer the scanner can give, and what it means, is in the guide:
                    <x-link href="{{ marketing_url('/docs/tickets') }}#scan-results" class="text-blue-800 dark:text-blue-300">what the scanner shows</x-link>.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Signal, and staff who should not see sales                -->
        <!-- ============================================================ -->
        <section id="signal" class="es-head-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-head-mark mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-head-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The two questions everyone asks</p>
                    <h2 class="es-balance es-head-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        No signal, <span class="es-head-accent">and volunteers on the door.</span>
                    </h2>
                </div>

                <div class="grid gap-4 md:grid-cols-2" data-reveal-group="90">
                    <div class="es-head-panel flex flex-col p-6 sm:p-8" data-reveal="panel">
                        <h3 class="es-head-ink text-lg font-bold">When the venue has no signal</h3>
                        <p class="es-head-muted mt-2 text-sm leading-relaxed">
                            Each scan is checked against one shared record, which is what lets three phones on three doors agree that a code has been used. The price of that is a connection. When the door phone has none, the scanner says <span class="es-head-ink font-semibold">No connection</span> and records nothing, so nobody is left half checked in: scan again once it is back. There is no offline mode that stores scans on the phone and sorts them out later, because that is exactly where a forwarded screenshot gets in twice.
                        </p>
                        <p class="es-head-muted mt-3 text-sm leading-relaxed">
                            So treat signal as part of the set-up. Venue wifi or a phone's hotspot at the door fixes most basements; test it with a real scan before doors.
                            @if (\App\Services\Wallet\GoogleWalletService::isConfigured())
                                A ticket saved to Google Wallet opens on the guest's phone without signal, which keeps the line moving on their side.
                            @endif
                        </p>
                    </div>
                    <div class="es-head-panel flex flex-col p-6 sm:p-8" data-reveal="panel">
                        <h3 class="es-head-ink text-lg font-bold">Door staff who should not see your sales</h3>
                        <p class="es-head-muted mt-2 text-sm leading-relaxed">
                            A volunteer on the door needs to scan, not to read what tonight took. Add them to the team as a <span class="es-head-ink font-semibold">viewer</span>: they sign in on their own phone, can scan tickets and read the calendar, and see no sales at all. An <span class="es-head-ink font-semibold">admin</span> runs the schedule day to day and does see the sales and the check-in dashboard.
                        </p>
                        <p class="es-head-muted mt-3 text-sm leading-relaxed">
                            Team members are on Enterprise, and included on every selfhosted install. Each person scanning has their own sign-in, so nobody shares a password at the door. See <x-link href="{{ marketing_url('/docs/managing-schedules') }}#team" class="text-blue-800 dark:text-blue-300">team members in the guide</x-link>.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. The free half and the Pro half                            -->
        <!-- ============================================================ -->
        <section id="split" class="es-head-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-head-mark mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-head-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What check-in costs</p>
                    <h2 class="es-balance es-head-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The door is free. <span class="es-head-accent">The view is Pro.</span>
                    </h2>
                    <p class="es-head-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Getting people into the room costs nothing on any plan. What Pro adds is being able to see the room from behind the desk while it fills.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2" data-reveal-group="90">
                    <div class="es-head-panel flex flex-col p-6" data-reveal="panel">
                        <p class="es-head-tag mb-3">Every plan, including Free</p>
                        <h3 class="es-head-ink text-lg font-bold">Scan the ticket</h3>
                        <p class="es-head-muted mt-2 text-sm leading-relaxed">
                            Open Sales on a phone and tap Scan Ticket. It reads the QR on any ticket, any free registration and any subscription pass. Each code admits once, a second read says when it was first used, and there is no plan check anywhere in it: a Free schedule scans its registrations exactly as a Pro one scans its ticket holders. On a phone that can, it buzzes once for a check-in and several times for anything else, and a green moves on to the next guest by itself after three seconds.
                        </p>
                        <p class="es-head-muted mt-auto pt-4 text-xs uppercase tracking-widest">No cap, no gate, no extra app</p>
                    </div>
                    <div class="es-head-panel flex flex-col p-6" data-reveal="panel">
                        <p class="es-head-tag mb-3">Pro</p>
                        <h3 class="es-head-ink text-lg font-bold">Watch the count</h3>
                        <p class="es-head-muted mt-2 text-sm leading-relaxed">
                            The dashboard is the second screen: checked in, still to arrive and sold, the same split per ticket type, the last ten arrivals with their times, and a search for the person in front of you. It is what turns a queue into a decision: hold the doors, open the balcony, start on time.
                        </p>
                        <p class="es-head-muted mt-auto pt-4 text-xs uppercase tracking-widest">Included on Pro and on every selfhosted install</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. What is on the dashboard                                  -->
        <!-- ============================================================ -->
        <section id="dashboard" class="es-head-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-head-mark mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                    <p class="es-head-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">On the check-in dashboard</p>
                    <h2 class="es-balance es-head-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Everything the desk <span class="es-head-accent">needs, and nothing else.</span>
                    </h2>
                    <p class="es-head-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Every other report is something you read afterwards. This is the one you watch while it happens: a single figure going up against a total you already know. Nothing on it is a trend or a chart of last month; every line answers a question somebody is asking out loud right now.
                    </p>
                </div>

                @php
                    $checkinFeatures = [
                        ['The overall count', 'Checked in, still to arrive and tickets sold, as figures and as a percentage on one progress bar.'],
                        ['Per ticket type', 'The same split by type, so a sold-out balcony that has not arrived yet is visible before it becomes a queue.'],
                        ['The last ten in', 'A running feed of who just came through, with the time, and on a seated event the seat they are heading for.'],
                        ['Find someone at the door', 'Type a seat (C14), a name or an email and see who holds it and whether they have arrived. It only looks; admitting is still a scan.'],
                        ['Passes counted as people', 'Where a pass admits guests, a headcount counts the people, not the passes. Holders who booked a seat ahead sit in a reserved count until they arrive.'],
                        ['Pick the date', 'Filter by event and by date, which is what a recurring night needs: tonight is its own count, not the run\'s total.'],
                    ];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                    @foreach ($checkinFeatures as [$ciName, $ciBody])
                        <div class="es-head-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-head-ink text-base font-bold">{{ $ciName }}</h3>
                            <p class="es-head-muted mt-2 text-sm leading-relaxed">{{ $ciBody }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 grid gap-4 md:grid-cols-2" data-reveal-group="90">
                    <div class="es-head-panel p-6" data-reveal="panel">
                        <h3 class="es-head-ink text-base font-bold">It refreshes itself, but not constantly</h3>
                        <p class="es-head-muted mt-2 text-sm leading-relaxed">
                            Every ten seconds while the tab is in front of you, and not at all when it is not. That second half matters more than the first: a door screen that kept polling from inside somebody's pocket would flatten the phone that is also running the scanner.
                        </p>
                    </div>
                    <div class="es-head-panel p-6" data-reveal="panel">
                        <h3 class="es-head-ink text-base font-bold">Tonight means the venue's tonight</h3>
                        <p class="es-head-muted mt-2 text-sm leading-relaxed">
                            The counts are keyed to the venue's own calendar date, not to whoever is looking. An evening show west of UTC reports as one evening rather than splitting itself across two days at the wrong midnight.
                        </p>
                    </div>
                </div>
                <div class="mt-10 flex flex-wrap justify-center gap-x-8 gap-y-3 text-center">
                    <a href="{{ marketing_url('/docs/tickets') }}#check-in" class="es-head-accent inline-flex items-center font-medium hover:underline">
                        Read the check-in guide
                        <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                    <a href="{{ marketing_url('/features') }}" class="es-head-accent inline-flex items-center font-medium hover:underline">
                        See all features
                        <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        @include('marketing.partials.pricing-nudge')

        <!-- ============================================================ -->
        <!-- 9. FAQ                                                       -->
        <!-- ============================================================ -->
        @php
            $checkinFaqs = [
                ['q' => 'Is there a free event check-in app?', 'a' => 'Yes. Scanning QR codes at the door is free on every plan, Free included, and free registrations are unlimited, so a free event can run its whole door without paying anything. The scanner is a web page rather than an app. A ticket with a price needs Pro, and so does the live check-in dashboard.'],
                ['q' => 'Do I need to download an app to scan QR tickets?', 'a' => 'No. The scanner runs in the browser of any phone, tablet or laptop with a camera, in a current version of Chrome or Safari. Browsers only allow the camera on a secure address, so a selfhosted install has to be opened over https to scan.'],
                ['q' => 'Can guests show a screenshot of their ticket?', 'a' => 'Yes. A screenshot carries the same code as the email, and it opens without signal. A forwarded screenshot still gets in only once, because every scan asks whether that order has already been checked in.'],
                ['q' => 'What happens if the same ticket is scanned twice?', 'a' => 'The second scan answers Already checked in, with the time of the first scan in the schedule\'s timezone, and changes nothing. If two phones scan the same code at the same moment, only one of them admits it.'],
                ['q' => 'Can several people scan at the same door?', 'a' => 'Yes, as many phones as you need. Each one is signed in by somebody who may scan the event, and one person can be signed in on several phones. They all check against the same record, so a code admitted at one door reads as used at the others.'],
                ['q' => 'Does the scanner work without internet?', 'a' => 'No. Each scan is checked with the server, which is how several phones agree on what has been used. Without a connection the scanner says No connection and records nothing, so you scan again once it is back. Put door phones on the venue\'s wifi or a hotspot where mobile signal is weak.'],
                ['q' => 'Can guests leave and come back in?', 'a' => 'A second scan of the same code answers Already checked in with the time of the first, which tells you they have been in before. Whether that lets them back in is your rule: a hand stamp or a wristband is the usual way to make it quick.'],
                ['q' => 'How early can I start checking people in?', 'a' => 'From 24 hours before the event starts until it ends, which is its start time plus its duration, or two hours when no duration is set. A scan outside that window is refused with a message saying which side of it you are on.'],
                ['q' => 'Which tickets does the scanner turn away?', 'a' => 'An order that is unpaid, cancelled, fully refunded, expired or awaiting payment review, and a code that matches no order on the event. A partial refund leaves the order paid, so its tickets still scan. Three kinds stop in amber instead, for you to decide: a ticket for a different event than the one the scanner is set to, a ticket for a later night of the same run whose check-in has already opened, and a ticket on an installment plan that has fallen behind, which shows the name and the balance still owed. Admit anyway checks them in.'],
                ['q' => 'Does it work for free registrations as well as paid tickets?', 'a' => 'Yes. A free registration gets a QR code in its confirmation email on exactly the same terms as a paid ticket, so an RSVP event checks in the same way a ticketed one does.'],
                ['q' => 'How do I check in people who are not on a ticket, like a guest list?', 'a' => 'On Pro, import them into the event from a CSV file or type them in, up to 5,000 rows at a time. With Send Email on, each gets a confirmation with a QR code, and they are counted at the door like everyone else.'],
                ['q' => 'Can somebody work the door without seeing our sales?', 'a' => 'Yes, on Enterprise and on every selfhosted install. A team member set to viewer is read-only and sees no sales at all, but may still scan tickets at the door. An admin runs the schedule day to day and does see the sales and the check-in dashboard.'],
                ['q' => 'How do subscription passes appear on it?', 'a' => 'As people, not as passes. Where a pass admits more than one person the dashboard shows a headcount including guests beside the check-in count, and holders who booked a seat ahead are listed as reserved until they actually turn up.'],
                // Only where the install can issue a pass: GoogleWalletService::isConfigured() gates
                // the button itself, so without an issuer account there is no wallet pass to ask about.
                ...(\App\Services\Wallet\GoogleWalletService::isConfigured() ? [
                    ['q' => 'Does a wallet pass scan differently?', 'a' => 'No. A pass saved into Google Wallet carries the same QR code as the ticket page, so it scans exactly like any other ticket and it opens offline once saved. The scanner checks the order\'s live status either way, so a cancelled or fully refunded order is refused at the door even if the pass is still on the phone.'],
                ] : []),
                ['q' => 'Which plan do I need?', 'a' => 'Scanning at the door and unlimited free registration are on the Free plan. The check-in dashboard is on Pro, and on every selfhosted install at no cost. Charging for a ticket is the other Pro part, and there is no platform fee on it whatever you pay. Team members, including viewers for the door, are Enterprise.'],
            ];
        @endphp
        <section id="faq" class="es-head-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-head-ink mb-10 text-center text-3xl font-black tracking-tight md:text-4xl" data-reveal>Event check-in questions</h2>
                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($checkinFaqs as $faq)
                        <details class="es-head-panel group p-5" data-reveal="panel">
                            <summary class="es-head-ink flex cursor-pointer items-center justify-between gap-4 text-base font-semibold">
                                {{ $faq['q'] }}
                                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-45" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                            </summary>
                            <p class="es-head-muted mt-3 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$checkinFaqs" />

        <!-- ============================================================ -->
        <!-- 10. Claim                                                    -->
        <!-- ============================================================ -->
        <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="es-head-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-20" data-reveal="panel">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="grid-overlay absolute inset-0 opacity-25"></div>
                    </div>

                    <div class="relative z-10">
                        <p class="es-head-band-tag mb-6">Free to start</p>
                        <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                            Stop counting <span class="es-head-band-grad">on paper</span>.
                        </h2>
                        <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                            Take the registrations, scan them at the door, and know the number without asking anyone.
                        </p>

                        <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-venue" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg bg-white px-8 py-4 text-lg font-semibold text-[#0f1318] transition-colors hover:bg-gray-100">
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
