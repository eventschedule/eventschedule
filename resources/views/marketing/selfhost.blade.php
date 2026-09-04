<x-marketing-layout>
    <x-slot name="title">{{ __('marketing.selfhost_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.selfhost_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">Selfhost</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "@id": "{{ url()->current() }}#software",
        "name": "Event Schedule - Selfhosted",
        "url": "{{ url()->current() }}",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Management Software",
        "operatingSystem": "Linux",
        "description": {!! \App\Utils\SeoUtils::jsonLd(__('marketing.selfhost_description')) !!},
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free and open source under the Attribution Assurance License"
        },
        "featureList": [
            "One-click Softaculous installation",
            "Docker Compose deployment",
            "Browser-based setup wizard",
            "One-click application updates",
            "Every Pro and Enterprise feature included",
            "AI-powered auto import from URLs",
            "Full data ownership",
            "White-label SaaS capability"
        ],
        "downloadUrl": "https://github.com/eventschedule/eventschedule",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        }
    }
    </script>
    @php
        // One array drives both the visible "Get it running" band and this HowTo
        // block, so the markup and the schema can never drift apart.
        $howToSteps = [
            [
                'name' => 'Install the files',
                'text' => 'Use the one-click Softaculous installer on a cPanel host, bring up the Docker Compose stack, or download the latest release and point your web server at the public directory.',
            ],
            [
                'name' => 'Run the setup wizard',
                'text' => 'Leave APP_URL blank in .env and open your domain. The browser wizard tests your database connection, creates your admin account, runs the database migrations and writes the configuration back to .env for you.',
            ],
            [
                'name' => 'Add the cron entry',
                'text' => 'Add "* * * * * php /path/to/eventschedule/artisan schedule:run" to your crontab so reminder emails, calendar sync and expiring ticket reservations keep running.',
                // Amber rule: the one step the software cannot do for you.
                'own' => true,
            ],
        ];
    @endphp
    <x-seo.howto-schema
        name="How to Selfhost Event Schedule"
        description="Install the open source Event Schedule platform on your own server in three steps."
        :steps="$howToSteps" />
    {{-- FAQ JSON-LD is emitted alongside the visible FAQ section near the end of the page, driven by one $selfhostFaqs array so the markup always matches the rendered content. --}}
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           /selfhost page theme: "The Terminal" - your server, your rules.

           Two colour rules carry the whole page:
             emerald/teal = included, free, yours (the page accent)
             amber        = your responsibility, and NOTHING else. The
                            server, SSL, the cron entry, backups and API
                            keys are the only amber things on this page,
                            so the honest tradeoffs read as a deliberate
                            voice rather than as a string of warnings.

           Restraint rule: exactly ONE section gets full terminal chrome
           (the install switcher). Everywhere else the motif is
           light-touch only - a mono $ eyebrow, an exit code, a mono act
           numeral. A terminal concept applied literally to every section
           just produces a page of dark mono boxes.

           The hero is deliberately text-only. /saas puts a built object
           beside its hero copy; here the built object is the install
           switcher, and repeating terminal chrome up top would spend the
           concept before the page has earned it.

           There used to be a decorative row of blinking terminal cursors
           on the hero, dark band and finale. Removed by request - the
           blinking read as distracting. Do not reintroduce it; the
           concept is carried by the typographic kit instead.

           Shared es-* primitives live in marketing.css; everything below
           is page-exclusive.
           ============================================================== */

        /* Page accent gradient (emerald to teal) */
        .text-gradient-selfhost {
            background: linear-gradient(135deg, #10b981 0%, #059669 50%, #0d9488 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-selfhost {
            background: linear-gradient(135deg, #34d399 0%, #10b981 50%, #14b8a6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* Panels that are dark in both modes need the bright stops in light mode too */
        .es-finale-panel .text-gradient-selfhost,
        .es-band-dark .text-gradient-selfhost {
            background: linear-gradient(135deg, #34d399 0%, #10b981 50%, #14b8a6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Ownership rule: every "this part is yours to run" marker is amber */
        .es-own { color: #b45309; }
        .dark .es-own { color: #fbbf24; }
        .es-band-dark .es-own { color: #fbbf24; }

        /* Mono kit. Same stack as /saas so the two pages agree. */
        .es-prompt,
        .es-term,
        .es-exit {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }

        /* Section eyebrow: ~/eventschedule $ install
           The leading pip gives section starts a shape to catch the eye. Mono
           text alone at this size was too quiet to punctuate the page. */
        .es-prompt {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            /* emerald-700, not -600: at 12px the -600 shade is 3.6:1 here. */
            color: #047857;
        }
        .es-prompt::before {
            content: "";
            width: 0.375rem;
            height: 0.375rem;
            border-radius: 9999px;
            background: currentColor;
            flex: 0 0 auto;
        }
        .dark .es-prompt { color: #34d399; }
        .es-band-dark .es-prompt,
        .es-finale-panel .es-prompt { color: #34d399; }
        .es-prompt .es-prompt-path { opacity: 0.72; }

        /* Exit-code chip: the "this worked" affirmation */
        .es-exit {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 9999px;
            border: 1px solid rgba(16, 185, 129, 0.35);
            background: rgba(16, 185, 129, 0.1);
            padding: 0.15rem 0.55rem;
            font-size: 0.68rem;
            font-weight: 600;
            color: #047857;
        }
        .dark .es-exit { color: #6ee7b7; }

        /* Bled act numeral. The dot nav is lg-only, so on phones this is the
           only "where am I" cue and it has to stay substantial.

           Positioning lives here rather than on four copies of the same span.
           From sm up it rides the vertical middle of the trailing lane, which
           the max-w-2xl copy beside it never reaches. On a phone there is no
           spare lane, so it shrinks and hugs the top edge beside the eyebrow
           instead of sitting behind the paragraph. Its section is
           overflow-hidden, so it never contributes to document scrollWidth. */
        .es-glyph {
            position: absolute;
            inset-inline-end: 0;
            top: 0;
            pointer-events: none;
            user-select: none;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-weight: 800;
            line-height: 0.75;
            font-size: 3.25rem;
            color: #10b981;
            opacity: 0.09;
        }
        .dark .es-glyph { opacity: 0.11; }
        /* The dark band is dark in both modes, so it takes the dark value always. */
        .es-band-dark .es-glyph { opacity: 0.11; }

        @media (min-width: 640px) {
            .es-glyph {
                top: 50%;
                transform: translateY(-50%);
                font-size: 7rem;
                opacity: 0.08;
            }
            .dark .es-glyph,
            .es-band-dark .es-glyph { opacity: 0.10; }
        }
        @media (min-width: 1024px) {
            .es-glyph { font-size: 13rem; }
        }

        /* ---- The one terminal frame -------------------------------------
           Deliberately dark in both colour modes. A light-mode terminal
           reads as a washed-out code block; a real one is dark, and the
           frame is small enough that it does not fight the light page. */
        .es-term {
            background: #0b0f0d;
            border: 1px solid rgba(16, 185, 129, 0.22);
            box-shadow: 0 24px 60px -30px rgba(4, 120, 87, 0.55);
        }
        .es-term-bar {
            background: rgba(16, 185, 129, 0.06);
            border-bottom: 1px solid rgba(16, 185, 129, 0.16);
        }
        .es-term-dot { width: 10px; height: 10px; border-radius: 9999px; }
        .es-term-body { color: #d1fae5; }
        .es-term-body .es-term-sigil { color: #34d399; user-select: none; }
        .es-term-body .es-term-note { color: rgba(209, 250, 229, 0.45); }
        .es-term-body .es-term-out { color: rgba(209, 250, 229, 0.7); }

        /* One-shot scanline sweep across the install panel when it arrives */
        .es-scan::after {
            content: "";
            position: absolute;
            inset-inline: 0;
            top: 0;
            height: 40%;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(52, 211, 153, 0), rgba(52, 211, 153, 0.09), rgba(52, 211, 153, 0));
            opacity: 0;
        }
        html.es-anim .es-scan.is-revealed::after {
            animation: es-scan-sweep 1.5s ease-out 0.15s 1;
        }
        @keyframes es-scan-sweep {
            0% { opacity: 0; transform: translateY(-60%); }
            25% { opacity: 1; }
            100% { opacity: 0; transform: translateY(280%); }
        }

        /* ---- Install switcher -------------------------------------------
           Progressive enhancement: the three panels ship stacked, visible
           and individually headed, so no-JS visitors and crawlers get every
           command. The IIFE at the foot of the page adds .is-ready, which
           reveals the tablist and lets JS hide the inactive panels. */
        .es-tablist { display: none; }
        .es-tabs.is-ready .es-tablist { display: inline-flex; }
        .es-tabs.is-ready .es-panel-title {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }
        .es-tab {
            border-radius: 0.75rem;
            padding: 0.5rem 1.1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #4b5563;
            transition: background-color 0.2s, color 0.2s;
        }
        .dark .es-tab { color: #9ca3af; }
        .es-tab:hover { color: #047857; }
        .dark .es-tab:hover { color: #6ee7b7; }
        .es-tab[aria-selected="true"] {
            background: #ffffff;
            color: #047857;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        }
        .dark .es-tab[aria-selected="true"] {
            background: rgba(16, 185, 129, 0.16);
            color: #6ee7b7;
            box-shadow: none;
        }
        /* The tablist uses a roving tabindex, so exactly one tab is reachable by
           Tab and the arrow keys move between them. Without an explicit ring the
           only feedback is the UA default over a fully custom control, which on
           the selected tab's light background is close to invisible.

           Arrow-key navigation moves focus with a programmatic .focus() call,
           and Chrome's :focus-visible heuristic does not reliably match that.
           So the script marks the tablist .is-kbd while the user is driving it
           from the keyboard, and plain :focus is honoured in that state. */
        .es-tab:focus-visible,
        .es-tabs.is-kbd .es-tab:focus,
        .es-copy:focus-visible {
            outline: 2px solid #10b981;
            outline-offset: 2px;
        }
        .dark .es-tab:focus-visible,
        .dark .es-tabs.is-kbd .es-tab:focus,
        .es-copy:focus-visible { outline-color: #34d399; }

        /* Federation pulse: the shared es-sync-dot travels between two tiles.
           It ships cyan and is already covered by the shared reduced-motion
           kill; only the colour needs repointing at the page accent. */
        .es-fed-dot {
            background: #34d399;
            box-shadow: 0 0 12px rgba(52, 211, 153, 0.9);
        }

        /* The shared odometer hardcodes the brand-blue gradient on each digit
           (the strip's translateY breaks background-clip inheritance, so the
           gradient cannot come from a parent). Repoint it at the page accent.

           background-image, NOT the background shorthand: the shorthand resets
           background-clip to border-box, which un-clips the digits while
           -webkit-text-fill-color: transparent stays set, so every digit
           renders as a solid gradient block instead of a number. */
        .es-od-strip span {
            background-image: linear-gradient(135deg, #10b981 0%, #059669 50%, #0d9488 100%);
        }
        .dark .es-od-strip span {
            background-image: linear-gradient(135deg, #34d399 0%, #10b981 50%, #14b8a6 100%);
        }

        /* The shared dot nav ships in brand blue; recolour it to the page accent. */
        .es-dot:hover .es-dot-pip { background-color: rgba(16, 185, 129, 0.7); }
        .es-dot.is-active .es-dot-pip {
            background: linear-gradient(180deg, #10b981, #14b8a6);
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-pulse-slow,
            .animate-float,
            .animate-ping { animation: none !important; }
            html.es-anim .es-scan.is-revealed::after { animation: none; opacity: 0; }
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- ============================================================ -->
    <!-- Hero                                                        -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero relative flex min-h-[calc(78svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(16, 185, 129, 0.3), rgba(16, 185, 129, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(20, 184, 166, 0.26), rgba(20, 184, 166, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(5, 150, 105, 0.14), rgba(5, 150, 105, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">100% open source, AAL licensed</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Selfhost the whole thing.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-selfhost">Nothing is held back</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Run Event Schedule on your own infrastructure and every Pro and Enterprise feature is included, free. No platform fees, no seat counts, no data leaving your server.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#install" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-emerald-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
                    Install it
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </a>
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    View on GitHub
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Marquee: what "everything included" actually means           -->
    <!-- ============================================================ -->
    @php
        $marqueeRows = [
            [
                ['Ticketing and QR check-in', 'bg-emerald-500', route('marketing.ticketing')],
                ['Private events', 'bg-teal-500', route('marketing.private_events')],
                ['AI import', 'bg-emerald-400', route('marketing.ai')],
                ['Newsletters', 'bg-cyan-500', route('marketing.newsletters')],
                ['Gift cards', 'bg-teal-400', route('marketing.gift_cards')],
                ['Calendar sync', 'bg-emerald-500', route('marketing.calendar_sync')],
                ['Event graphics', 'bg-teal-500', route('marketing.event_graphics')],
                ['Analytics', 'bg-cyan-400', route('marketing.analytics')],
            ],
            [
                ['Appointments', 'bg-teal-500', route('marketing.appointments')],
                ['White label', 'bg-emerald-500', route('marketing.white_label')],
                ['Team scheduling', 'bg-cyan-500', route('marketing.team_scheduling')],
                ['Custom fields', 'bg-emerald-400', route('marketing.custom_fields')],
                ['Event polls', 'bg-teal-400', route('marketing.polls')],
                ['Recurring events', 'bg-emerald-500', route('marketing.recurring_events')],
                ['Sub-schedules', 'bg-cyan-500', route('marketing.sub_schedules')],
                ['Fan videos', 'bg-teal-500', route('marketing.fan_videos')],
            ],
        ];
    @endphp
    <section class="relative overflow-hidden border-y border-gray-200 bg-white py-10 dark:border-white/10 dark:bg-[#0a0a0f]" aria-label="Included features">
        <h2 class="sr-only">Included features</h2>
        <div class="es-marquee-mask space-y-4">
            @foreach ($marqueeRows as $rowIndex => $row)
                <div class="es-marquee" data-marquee="{{ $rowIndex === 0 ? '1' : '-1' }}">
                    <div class="es-marquee-track">
                        @for ($i = 0; $i < 2; $i++)
                            @foreach ($row as [$label, $dot, $href])
                                <a href="{{ $href }}" @if ($i === 1) aria-hidden="true" tabindex="-1" @endif class="flex items-center gap-2.5 rounded-full border border-gray-200/70 bg-gray-100/80 px-6 py-3 text-lg font-semibold text-gray-800 transition-colors hover:text-emerald-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-200 dark:hover:text-emerald-400">
                                    <span class="h-2 w-2 rounded-full {{ $dot }}" aria-hidden="true"></span>
                                    {{ $label }}
                                </a>
                            @endforeach
                        @endfor
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- See it running                                              -->
    <!-- ============================================================ -->
    <section id="demo" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <div class="es-prompt mb-3" aria-hidden="true"><span class="es-prompt-path">~/eventschedule</span> $ demo</div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>Try it before you <span class="text-gradient-selfhost">install anything</span></h2>
                <p class="mx-auto mt-3 max-w-2xl text-lg text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">Both halves of what you will be running: the admin portal you manage events in, and the public calendar your attendees see.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2" data-reveal-group="80">
                @php
                    $demoCards = [
                        ['Admin portal', 'Create events, sell tickets, track sales, customise the calendar.', demo_url(), 'Open admin demo'],
                        ['Guest portal', 'The public calendar. Browse events, buy tickets, follow for updates.', 'https://simpsons.eventschedule.com', 'Open guest demo'],
                    ];
                @endphp
                @foreach ($demoCards as [$dTitle, $dBody, $dHref, $dCta])
                    <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                        <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                            <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">{{ $dTitle }}</h3>
                            <p class="mb-6 flex-grow text-gray-600 dark:text-gray-400">{{ $dBody }}</p>
                            <a href="{{ $dHref }}" target="_blank" rel="noopener noreferrer" class="mt-auto inline-flex items-center justify-center gap-2 self-start rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-2.5 font-medium text-emerald-800 transition-colors hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/20">
                                {{ $dCta }}
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                            <div class="es-glare" aria-hidden="true"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Act 01: Get it running                                      -->
    <!-- ============================================================ -->
    @php
        // Every command below is taken verbatim from the installation guide
        // (resources/views/marketing/docs/selfhost/installation.blade.php) or
        // from the dockerfiles repo README. Nothing here is invented.
        $installMethods = [
            [
                'key' => 'softaculous',
                'label' => 'Softaculous',
                'tagline' => 'One click, no terminal',
                'blurb' => 'Available on most cPanel hosts. The installer creates the database, writes the configuration and runs the migrations for you.',
                'title' => 'installer.log',
                'lines' => [
                    ['out', 'Open cPanel and find Event Schedule in Softaculous'],
                    ['out', 'Choose your domain and directory, then Install'],
                    ['out', 'Database created, environment configured, migrations run'],
                ],
                'exit' => 'No command line needed',
                'cta' => ['Open in Softaculous', 'https://www.softaculous.com/apps/calendars/Event_Schedule', true],
                'copy' => null,
            ],
            [
                'key' => 'docker',
                'label' => 'Docker',
                'tagline' => 'Containerised, on any VPS',
                'blurb' => 'Bring up the Compose stack from the dockerfiles repo. The first build takes a few minutes while dependencies install and assets compile.',
                'title' => 'bash',
                'lines' => [
                    ['cmd', 'git clone https://github.com/eventschedule/dockerfiles'],
                    ['cmd', 'cd dockerfiles'],
                    ['cmd', 'docker compose up --build -d'],
                    ['note', '# then open http://localhost:8080'],
                ],
                'exit' => 'Stack running',
                'cta' => ['View the Docker setup', 'https://github.com/eventschedule/dockerfiles', true],
                'copy' => "git clone https://github.com/eventschedule/dockerfiles\ncd dockerfiles\ndocker compose up --build -d",
            ],
            [
                'key' => 'manual',
                'label' => 'Manual',
                'tagline' => 'Full control, any host',
                'blurb' => 'Download the latest release and point your web server at the public directory. Leave APP_URL blank and the browser wizard does the rest.',
                'title' => 'bash',
                'lines' => [
                    ['cmd', 'cd /var/www'],
                    ['cmd', 'unzip eventschedule.zip'],
                    ['cmd', 'cp .env.example .env'],
                    ['cmd', 'chmod -R 755 storage'],
                    ['cmd', 'chown -R www-data:www-data storage bootstrap public .env'],
                    ['note', '# leave APP_URL blank, then open your domain'],
                ],
                'exit' => 'Setup wizard ready',
                'cta' => ['Read the full guide', route('marketing.docs.selfhost.installation'), false],
                'copy' => "cd /var/www\nunzip eventschedule.zip\ncp .env.example .env\nchmod -R 755 storage\nchown -R www-data:www-data storage bootstrap public .env",
            ],
        ];
    @endphp
    <section id="install" class="relative scroll-mt-24 overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 40%, rgba(16, 185, 129, 0.22), rgba(16, 185, 129, 0) 65%);"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="relative pb-6 lg:pb-10" data-reveal>
                <span aria-hidden="true" class="es-glyph">01</span>
                <div class="relative max-w-2xl">
                    <div class="es-prompt mb-3" aria-hidden="true"><span class="es-prompt-path">~/eventschedule</span> $ install</div>
                    <h2 class="es-balance mb-3 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl">Three ways in. <span class="text-gradient-selfhost">Pick one.</span></h2>
                    <p class="text-lg text-gray-500 dark:text-gray-400">A shared cPanel host, a Docker stack or a plain zip on your own box. All three land on the same setup wizard.</p>
                </div>
            </div>

            <div class="es-tabs es-scan relative overflow-hidden rounded-3xl border border-gray-200 bg-gray-50 p-5 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-8" data-reveal="panel">
                <div class="es-tablist mb-6 gap-1 rounded-2xl bg-gray-200/70 p-1 dark:bg-black/30" role="tablist" aria-label="Installation method">
                    @foreach ($installMethods as $mIndex => $m)
                        <button type="button" class="es-tab" role="tab" id="tab-{{ $m['key'] }}" aria-controls="panel-{{ $m['key'] }}" aria-selected="{{ $mIndex === 0 ? 'true' : 'false' }}" tabindex="{{ $mIndex === 0 ? '0' : '-1' }}">{{ $m['label'] }}</button>
                    @endforeach
                </div>

                <div class="space-y-10">
                    @foreach ($installMethods as $mIndex => $m)
                        <div class="es-tabpanel" role="tabpanel" id="panel-{{ $m['key'] }}" aria-labelledby="tab-{{ $m['key'] }}" tabindex="0">
                            <h3 class="es-panel-title mb-4 text-xl font-bold text-gray-900 dark:text-white">{{ $m['label'] }}</h3>

                            <div class="grid gap-6 lg:grid-cols-[1fr,1.15fr] lg:items-center">
                                <div>
                                    <div class="mb-2 text-sm font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">{{ $m['tagline'] }}</div>
                                    <p class="mb-5 text-gray-600 dark:text-gray-400">{{ $m['blurb'] }}</p>
                                    <a href="{{ $m['cta'][1] }}" @if ($m['cta'][2]) target="_blank" rel="noopener noreferrer" @endif class="group inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 font-medium text-white transition-all hover:from-emerald-500 hover:to-teal-500">
                                        {{ $m['cta'][0] }}
                                        <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </a>
                                </div>

                                <div class="es-term overflow-hidden rounded-2xl" dir="ltr">
                                    <div class="es-term-bar flex items-center gap-2 px-4 py-2.5">
                                        <span class="es-term-dot" style="background: rgba(239, 68, 68, 0.55);"></span>
                                        <span class="es-term-dot" style="background: rgba(245, 158, 11, 0.55);"></span>
                                        <span class="es-term-dot" style="background: rgba(16, 185, 129, 0.55);"></span>
                                        <span class="ms-2 text-xs" style="color: rgba(209, 250, 229, 0.5);">{{ $m['title'] }}</span>
                                        @if ($m['copy'])
                                            <button type="button" class="es-copy ms-auto rounded-md px-2 py-1 text-xs font-semibold text-emerald-300 transition-colors hover:bg-emerald-500/15" data-copy="{{ $m['copy'] }}">Copy</button>
                                        @endif
                                    </div>
                                    <div class="es-term-body space-y-1.5 px-4 py-4 text-[0.8rem] leading-relaxed sm:text-sm">
                                        @foreach ($m['lines'] as [$kind, $text])
                                            @if ($kind === 'cmd')
                                                <div class="flex gap-2"><span class="es-term-sigil">$</span><span class="break-all">{{ $text }}</span></div>
                                            @elseif ($kind === 'note')
                                                <div class="es-term-note break-all ps-4">{{ $text }}</div>
                                            @else
                                                <div class="es-term-out flex gap-2"><span class="es-term-sigil">-</span><span>{{ $text }}</span></div>
                                            @endif
                                        @endforeach
                                        <div class="pt-2">
                                            <span class="es-exit" style="color: #6ee7b7;">
                                                <svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                {{ $m['exit'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Requirements: a spec sheet rather than four bare label/value tiles -->
            @php
                $requirements = [
                    ['PHP', '8.2 or newer', 'chip'],
                    ['Database', 'MySQL 5.7+ or MariaDB 10.3+', 'db'],
                    ['Web server', 'Apache or Nginx with rewrites', 'server'],
                    ['SSL', 'Required, HTTPS only', 'shield'],
                ];
                // GD, not Imagick: image work is all GD and Imagick is not used anywhere in
                // the codebase, so listing it as an alternative would be a false claim.
                $phpExtensions = ['BCMath', 'Ctype', 'Fileinfo', 'Intl', 'JSON', 'Mbstring', 'OpenSSL', 'PDO (MySQL)', 'MySQLi', 'Tokenizer', 'XML', 'cURL', 'GD', 'Zip'];
                $reqIcons = [
                    'chip' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
                    'db' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4',
                    'server' => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01',
                    'shield' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                ];
            @endphp
            <div class="mt-10 overflow-hidden rounded-3xl border border-gray-200 bg-white dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                <div class="flex items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-5 py-3 dark:border-white/10 dark:bg-white/[0.04]">
                    <span class="es-prompt" aria-hidden="true"><span class="es-prompt-path">~/eventschedule</span> $ check-requirements</span>
                    <span class="es-exit">
                        <svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        Most shared hosts already pass
                    </span>
                </div>

                <div class="grid gap-px bg-gray-200 dark:bg-white/10 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($requirements as [$rLabel, $rValue, $rIcon])
                        <div class="group/req flex items-start gap-3 bg-white p-5 transition-colors hover:bg-emerald-50/60 dark:bg-[#0a0a0f] dark:hover:bg-emerald-500/[0.06]">
                            <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 transition-transform duration-200 group-hover/req:scale-105 dark:bg-emerald-500/15 dark:text-emerald-300">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $reqIcons[$rIcon] }}" /></svg>
                            </span>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $rLabel }}</div>
                                <div class="mt-0.5 font-semibold text-gray-900 dark:text-white">{{ $rValue }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center gap-2 border-t border-gray-200 px-5 py-4 dark:border-white/10">
                    <span class="me-1 text-sm text-gray-500 dark:text-gray-400">PHP extensions:</span>
                    @foreach ($phpExtensions as $ext)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 font-mono text-[11px] text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">
                            <span class="h-1 w-1 rounded-full bg-emerald-500 dark:bg-emerald-400" aria-hidden="true"></span>
                            {{ $ext }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Setup wizard (mirrors the HowTo schema above)               -->
    <!-- ============================================================ -->
    {{-- The wizard runs in a browser, so it is shown in browser chrome rather
         than described in three text cards. Frame markup follows
         components/marketing/feature-banner.blade.php so the two agree.

         Deliberately NOT the shared data-scene="steps" engine: that wants a
         280vh scroll-pinned stage, and this page's problem is already length. --}}
    <section class="relative overflow-hidden bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">

                <div data-reveal="left">
                    <div class="es-prompt mb-3" aria-hidden="true"><span class="es-prompt-path">~/eventschedule</span> $ open https://your-domain.com</div>
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">Set up in a browser, not a text editor</h2>
                    <p class="mb-8 text-lg text-gray-500 dark:text-gray-400">Leave <code class="rounded bg-gray-200 px-1.5 py-0.5 font-mono text-[0.85em] text-gray-800 dark:bg-white/10 dark:text-gray-100">APP_URL</code> blank and open your domain. The app generates its own key on first boot, and the wizard tests the database, runs the migrations and writes the configuration back itself. Mail settings are the one thing it leaves to you.</p>

                    <ol class="space-y-4" data-reveal-group="90">
                        @foreach ($howToSteps as $sIndex => $step)
                            <li data-reveal class="flex items-start gap-4">
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-sm font-bold {{ ($step['own'] ?? false) ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' }}">{{ $sIndex + 1 }}</span>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        {{ $step['name'] }}
                                        @if ($step['own'] ?? false)
                                            <span class="es-own ms-2 text-xs font-bold uppercase tracking-wider">Yours to run</span>
                                        @endif
                                    </h3>
                                    <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">{{ $step['text'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <div class="w-full" data-reveal="right">
                    <div class="es-bento relative" data-tilt="3">
                        <div class="es-tilt-inner overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl shadow-gray-900/5 dark:border-white/10 dark:bg-[#101016] dark:shadow-black/40">
                            <div class="flex items-center gap-1.5 border-b border-gray-200 bg-gray-50 px-3 py-2.5 dark:border-white/10 dark:bg-white/[0.04]">
                                <span class="h-2.5 w-2.5 rounded-full" style="background-color: #FF5F57;"></span>
                                <span class="h-2.5 w-2.5 rounded-full" style="background-color: #FEBC2E;"></span>
                                <span class="h-2.5 w-2.5 rounded-full" style="background-color: #28C840;"></span>
                                <span dir="ltr" class="mx-auto truncate rounded-md bg-white px-3 py-0.5 font-mono text-[11px] text-gray-500 dark:bg-white/5 dark:text-gray-400">your-domain.com/setup</span>
                            </div>

                            <div class="p-5 sm:p-6" aria-hidden="true">
                                <div class="mb-5 flex items-center gap-2.5">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-500 text-[11px] font-bold text-white">ES</span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Set up Event Schedule</span>
                                </div>

                                {{-- Progress rail: step 2 of 3, matching the copy beside it --}}
                                <div class="mb-5 flex items-center gap-2">
                                    @foreach ([true, true, false] as $done)
                                        <span class="h-1.5 flex-1 rounded-full {{ $done ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-white/10' }}"></span>
                                    @endforeach
                                </div>

                                {{-- es-ai-field is the shared "fields materialize" reveal. --}}
                                <div class="space-y-3">
                                    {{-- The wizard's real fields: the five DB values, then the admin
                                         account. It never asks for an app name or mail settings. --}}
                                    @foreach ([['Database host', 'localhost'], ['Database', 'eventschedule'], ['Database user', 'eventschedule'], ['Admin email', 'you@your-domain.com']] as $fi => $field)
                                        <div class="es-ai-field" style="--i: {{ $fi }};">
                                            <div class="mb-1 text-[10px] font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $field[0] }}</div>
                                            <div class="flex items-center justify-between gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-white/10 dark:bg-white/5">
                                                <span dir="ltr" class="truncate font-mono text-xs text-gray-700 dark:text-gray-300">{{ $field[1] }}</span>
                                                <svg aria-hidden="true" class="h-3.5 w-3.5 shrink-0 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 py-2.5 text-sm font-semibold text-white">
                                    Run migrations and finish
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                    <p class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">The wizard as it appears on a fresh install.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Act 02: What you get                                        -->
    <!-- ============================================================ -->
    @php
        // Sourced from docs/FEATURES.md. Selfhosted deployments return true
        // for both isPro() and isEnterprise(), so every Pro and Enterprise
        // feature is included. Capped at 18 items in three themed columns
        // rather than reprinting the whole tier table.
        $selfhostOnly = [
            [
                'title' => 'Auto import from the web',
                // ImportCuratorEvents scrapes the configured URLs only, once a day from the
                // scheduler. Configured cities are a filter on what those pages return, not
                // a search: the city branch is marked a placeholder in the command.
                'body' => 'Point Event Schedule at a list of URLs and AI pulls the events in once a day, keeping only the cities you name. It checks each site\'s robots.txt first.',
            ],
            [
                'title' => 'One-click app updates',
                'body' => 'When a release lands, a notice appears in your admin panel. One click applies it, migrations included. No terminal access needed.',
            ],
        ];

        // Real version numbers, read from the updater config so the two update
        // mocks on this page can never drift from the shipped release.
        $installedVersion = config('self-update.version_installed', 'v1.0.121');
        $nextVersion = preg_replace_callback('/(\d+)$/', fn ($m) => (int) $m[1] + 1, $installedVersion);

        // Icon chips rather than eighteen identical checkmarks. Each group
        // carries one tint so the three columns read as distinct without
        // eighteen competing colours - and amber stays reserved for the
        // operator-responsibility content further down the page.
        $ic = [
            'ticket' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',
            'card' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
            'badge' => 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0',
            'gift' => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zM5 12h14a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm0 0v7a2 2 0 002 2h10a2 2 0 002-2v-7',
            'list' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
            'code' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
            'users' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
            'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'lock' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
            'mail' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            'star' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
            'globe' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9',
            'sparkles' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
            'brush' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
            'photo' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
            'link' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1',
            'chat' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        ];

        $includedGroups = [
            [
                'heading' => 'Sell',
                'chip' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
                'items' => [
                    ['Ticketing with QR check-in', 'ticket'],
                    // NOT Connect: Connect is only used when IS_HOSTED=true. A single-tenant
                    // install charges with the platform keys in your own .env.
                    ['Stripe Checkout, no platform fees', 'card'],
                    ['Passes and subscriptions', 'badge'],
                    ['Promo codes and gift cards', 'gift'],
                    ['Waitlists and a check-in dashboard', 'list'],
                    ['Embeddable ticket widget', 'code'],
                ],
            ],
            [
                'heading' => 'Run',
                'chip' => 'bg-teal-100 text-teal-700 dark:bg-teal-500/15 dark:text-teal-300',
                'items' => [
                    ['Multiple team members', 'users'],
                    // The availability tab renders for talent schedules only (show-admin.blade.php).
                    ['Availability tracking on talent schedules', 'calendar'],
                    ['Appointment booking', 'clock'],
                    ['Internal and unlisted events', 'lock'],
                    ['Unlimited newsletter emails', 'mail'],
                    ['Post-event feedback and carpools', 'star'],
                ],
            ],
            [
                'heading' => 'Make it yours',
                'chip' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300',
                'items' => [
                    // NOT per-schedule custom domains: ResolveCustomDomain returns early when
                    // ! config('app.hosted'), so that setting is a hosted-mode feature. What is
                    // true here is that the whole install runs on a domain you chose.
                    ['Runs on your own domain', 'globe'],
                    ['White-label, bar one small licence credit', 'sparkles'],
                    ['Custom CSS, fields and labels', 'brush'],
                    ['Event graphics and AI generation', 'photo'],
                    ['REST API and webhooks', 'link'],
                    ['WhatsApp event creation', 'chat'],
                ],
            ],
        ];
    @endphp
    <section id="included" class="relative scroll-mt-24 overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative pb-6 lg:pb-10" data-reveal>
                <span aria-hidden="true" class="es-glyph">02</span>
                <div class="relative max-w-2xl">
                    <div class="es-prompt mb-3" aria-hidden="true"><span class="es-prompt-path">~/eventschedule</span> $ features --all</div>
                    <h2 class="es-balance mb-3 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl">Every Pro and Enterprise feature. <span class="text-gradient-selfhost">Free.</span></h2>
                    <p class="text-lg text-gray-500 dark:text-gray-400">There is no selfhosted tier to upgrade from. A selfhosted install is treated as Enterprise everywhere in the code, so nothing on this page is behind a paywall.</p>
                </div>
            </div>

            <!-- The two features that exist ONLY when you selfhost, given the lead.
                 Each carries a mock: these are the page's headline capabilities and
                 were the only "lead" cards on the page with nothing to look at. -->
            <div class="mb-4 grid gap-4 lg:grid-cols-2" data-reveal-group="80">
                @foreach ($selfhostOnly as $soIndex => $so)
                    <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                        <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50 p-8 dark:border-emerald-500/25 dark:from-emerald-900/25 dark:to-teal-900/20">
                            <span class="mb-4 inline-flex items-center gap-2 self-start rounded-full border border-emerald-300 bg-white/70 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-800 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-300">
                                Selfhost only
                            </span>
                            <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">{{ $so['title'] }}</h3>
                            <p class="mb-6 text-gray-600 dark:text-gray-300">{{ $so['body'] }}</p>

                            <div class="mt-auto rounded-2xl border border-emerald-200/70 bg-white/70 p-4 dark:border-white/10 dark:bg-black/20" aria-hidden="true">
                                @if ($soIndex === 0)
                                    {{-- URL in, parsed fields out: the shared es-ai-field reveal. --}}
                                    <div dir="ltr" class="mb-3 flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 font-mono text-[11px] text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                                        <svg aria-hidden="true" class="h-3.5 w-3.5 shrink-0 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                        <span class="truncate">thevenue.com/gigs</span>
                                    </div>
                                    <div class="space-y-1.5">
                                        @foreach ([['Event', 'Friday Night Jazz'], ['Date', 'Fri 14 Aug, 8:00 PM'], ['Venue', 'The Blue Room']] as $afi => $af)
                                            <div class="es-ai-field flex items-center justify-between gap-2 text-[11px]" style="--i: {{ $afi }};">
                                                <span class="text-gray-500 dark:text-gray-400">{{ $af[0] }}</span>
                                                <span class="truncate font-medium text-gray-800 dark:text-gray-200">{{ $af[1] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex items-center justify-between gap-3">
                                        <span dir="ltr" class="rounded-lg bg-gray-100 px-2.5 py-1 font-mono text-[11px] text-gray-700 dark:bg-white/10 dark:text-gray-400">{{ $installedVersion }}</span>
                                        <div class="relative h-px flex-1">
                                            <div class="h-px w-full bg-emerald-400/40"></div>
                                            <span class="es-sync-dot es-fed-dot"></span>
                                        </div>
                                        <span dir="ltr" class="rounded-lg bg-emerald-100 px-2.5 py-1 font-mono text-[11px] font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">{{ $nextVersion }}</span>
                                    </div>
                                    <div class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 py-2 text-[11px] font-semibold text-white">
                                        <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        Update now
                                    </div>
                                    <p class="mt-2 text-center text-[10px] text-gray-500 dark:text-gray-400">Migrations included.</p>
                                @endif
                            </div>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($includedGroups as $group)
                    <div data-reveal="panel" class="rounded-3xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-white/[0.04] sm:p-7">
                        <h3 class="mb-5 text-lg font-bold text-gray-900 dark:text-white">{{ $group['heading'] }}</h3>
                        {{-- The stagger belongs on the column, not on the wrapper of all
                             three: on the wrapper only the columns stepped and the rows
                             inside never did. A tight step keeps six rows under 350ms. --}}
                        <ul class="space-y-2" data-reveal-group="55">
                            @foreach ($group['items'] as [$itemLabel, $itemIcon])
                                <li data-reveal class="group/row flex items-center gap-3 rounded-xl p-1.5 transition-colors hover:bg-white dark:hover:bg-white/5">
                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg transition-transform duration-200 group-hover/row:scale-105 {{ $group['chip'] }}">
                                        <svg aria-hidden="true" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ic[$itemIcon] }}" />
                                        </svg>
                                    </span>
                                    <span class="text-sm text-gray-700 dark:text-gray-300 sm:text-[0.95rem]">{{ $itemLabel }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400" data-reveal>
                <x-link href="{{ route('marketing.features') }}">See the full feature list</x-link>
            </p>

            {{-- The numbers used to be a separate full-bleed band of flat white text.
                 Folded into this act and given the shared es-od odometer, which rolls
                 each digit on reveal and degrades to the printed value without JS.
                 es-ghost (the outlined numeral saas uses for its $0) is deliberately
                 not mixed in: a static outline beside two rolling odometers reads as
                 an inconsistency, not as variety. --}}
            @php
                $numberStats = [
                    ['0%', 'Platform fees on tickets', 'We never take a cut, on any plan'],
                    [plan_price(0), 'Forever, for any number of events', 'No licence fee and no seat count'],
                    [(string) count(config('app.supported_languages')), 'Languages built in', 'Your guest pages, translated'],
                ];
            @endphp
            <div class="mt-12 grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="120">
                @foreach ($numberStats as [$statValue, $statLabel, $statNote])
                    <div data-reveal="panel" class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50 p-8 text-center dark:border-emerald-500/20 dark:from-emerald-900/25 dark:to-teal-900/25">
                        <div class="es-od text-gradient-selfhost mb-3 justify-center text-5xl font-black lg:text-6xl" data-odometer="{{ $statValue }}">{{ $statValue }}</div>
                        <div class="mb-1 text-lg font-bold text-gray-900 dark:text-white">{{ $statLabel }}</div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $statNote }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Act 03: What you own (the one full-bleed dark band)         -->
    <!-- ============================================================ -->
    @php
        $responsibilities = [
            ['The server', 'A VPS, a shared host or a box under your desk. Your call, your bill.'],
            ['SSL and the domain', 'HTTPS is required. Most hosts issue a free certificate.'],
            ['The cron entry', 'One line, once. Without it reminders and calendar sync stop.'],
            ['Backups', 'Export and restore is built in, but scheduling it is on you.'],
            // The wizard writes everything except mail, and it is the step selfhosters
            // most often discover late, when a ticket confirmation does not arrive.
            ['Outbound email', 'The wizard configures everything but this. Point it at an SMTP service or tickets go nowhere.'],
            ['Disk for uploads', 'Flyers, fan photos and generated graphics sit on your filesystem, so size the volume for them.'],
        ];
    @endphp
    <section id="data" class="es-band-dark relative scroll-mt-24 overflow-hidden py-16 lg:py-24 noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(16, 185, 129, 0.28), rgba(16, 185, 129, 0) 62%);"></div>
            <div class="grid-overlay absolute inset-0 opacity-30"></div>

        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative pb-6 lg:pb-10" data-reveal>
                <span aria-hidden="true" class="es-glyph">03</span>
                <div class="relative max-w-2xl">
                    <div class="es-prompt mb-3" aria-hidden="true"><span class="es-prompt-path">~/eventschedule</span> $ whoami</div>
                    <h2 class="es-balance mb-3 text-3xl font-black tracking-tight text-white md:text-5xl">The data is <span class="text-gradient-selfhost">yours</span>, and so is the upkeep</h2>
                    <p class="text-lg text-gray-300">Nothing is shipped anywhere by default. Here is exactly what stays with you, what you can choose to share, and what you are on the hook for.</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <!-- Data ownership -->
                <div data-reveal="panel" class="rounded-3xl border border-white/10 bg-white/[0.04] p-8">
                    <h3 class="mb-4 text-2xl font-bold text-white">It never leaves your server</h3>
                    <p class="mb-6 text-gray-300">Your events, attendees, ticket sales and follower emails live in your database. Event Schedule cannot access, modify or remove selfhosted data, because there is no connection back to us to do it with.</p>
                    <ul class="space-y-3">
                        @foreach (['Your database, your backups, your retention rules', 'Stripe payouts go straight to your own account', 'Your own Gemini or OpenAI key for the AI features', 'No telemetry, no phone-home, no usage reporting'] as $dItem)
                            <li class="flex items-start gap-3 text-gray-200">
                                <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>{{ $dItem }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Federation: a diagram, because the opt-in direction of travel
                     is the whole point and prose kept burying it. es-sync-dot is
                     the shared pulse-between-tiles primitive (unused until now);
                     it is already in the shared reduced-motion kill list. -->
                <div data-reveal="panel" class="flex flex-col rounded-3xl border border-white/10 bg-white/[0.04] p-8">
                    <div class="mb-3 inline-flex items-center gap-2 self-start rounded-full border border-emerald-400/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-300">Opt in, off by default</div>
                    <h3 class="mb-4 text-2xl font-bold text-white">Or share out, on your terms</h3>
                    <p class="mb-7 text-gray-300">Federation is the one bridge back to eventschedule.com, and it only exists if an admin switches it on. Your public events appear in the main listings, and every listing links back to the event on your own site.</p>

                    <div class="es-fed relative mb-6 rounded-2xl border border-white/10 bg-black/25 p-5" aria-hidden="true">
                        <div class="relative flex items-center justify-between gap-2">
                            <div class="relative z-10 w-[33%] rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-2 py-3 text-center">
                                <svg aria-hidden="true" class="mx-auto mb-1.5 h-5 w-5 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                                <div class="text-xs font-semibold text-white">Your server</div>
                                <div class="font-mono text-[10px] text-emerald-300/70">your-domain.com</div>
                            </div>

                            {{-- The rail is drawn dashed so the path still reads when the
                                 pulse is mid-cycle or stopped by reduced motion. --}}
                            <div class="relative h-px flex-1" style="min-width: 4.5rem;">
                                <div class="h-px w-full" style="background-image: linear-gradient(to right, rgba(52, 211, 153, 0.55) 45%, transparent 45%); background-size: 7px 1px;"></div>
                                <span class="es-sync-dot es-fed-dot"></span>
                            </div>

                            <div class="relative z-10 w-[33%] rounded-xl border border-white/15 bg-white/5 px-2 py-3 text-center">
                                <svg aria-hidden="true" class="mx-auto mb-1.5 h-5 w-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                <div class="text-xs font-semibold text-white">Listings</div>
                                <div class="font-mono text-[10px] text-gray-500 dark:text-gray-400">eventschedule.com</div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-center gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                            <svg aria-hidden="true" class="h-3.5 w-3.5 rotate-180 text-emerald-400 rtl:rotate-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            every listing links back to your site
                        </div>
                    </div>

                    <ul class="mt-auto space-y-2.5 text-sm">
                        @foreach (['Only public events, never drafts, internal or unlisted ones', 'Enabled by an admin at /admin/settings, never automatically', 'Each schedule can opt out individually'] as $fItem)
                            <li class="flex items-start gap-2.5 text-gray-300">
                                <span class="mt-[7px] h-1 w-1 shrink-0 rounded-full bg-emerald-400"></span>
                                <span>{{ $fItem }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-5 text-sm">
                        <a href="{{ route('marketing.docs.selfhost.federation') }}" class="font-medium text-emerald-300 underline decoration-emerald-400/40 underline-offset-4 transition-colors hover:text-emerald-200">Read the federation guide</a>
                    </p>
                </div>
            </div>

            <!-- The amber half: what you are responsible for -->
            <div class="mt-4 rounded-3xl border border-amber-400/25 bg-amber-500/[0.06] p-8" data-reveal="panel">
                <h3 class="es-own mb-2 text-xs font-bold uppercase tracking-[0.18em]">Yours to run</h3>
                <p class="mb-6 max-w-3xl text-gray-300">Selfhosting means you are the operator. It is not much, but it is honest to say it out loud.</p>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                    @foreach ($responsibilities as [$rTitle, $rBody])
                        <div data-reveal>
                            <div class="es-own mb-1.5 font-semibold">{{ $rTitle }}</div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $rBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Keeping it current                                          -->
    <!-- ============================================================ -->
    <section id="updates" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div data-reveal>
                    <div class="es-prompt mb-3" aria-hidden="true"><span class="es-prompt-path">~/eventschedule</span> $ app:update</div>
                    <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">Updates are one click, not one afternoon</h2>
                    <p class="mb-8 text-xl text-gray-500 dark:text-gray-400">When a release lands, a notice appears in your admin panel. Click it and the update applies in seconds, migrations included. No SSH session, no maintenance window.</p>
                    <ul class="space-y-4">
                        @php
                            $updateFeatures = [
                                ['Database migrations included', 'Schema changes are applied as part of the update'],
                                ['No terminal access needed', 'The whole thing happens from the admin panel'],
                                ['Version notifications', 'You are told when a new release is available'],
                            ];
                        @endphp
                        @foreach ($updateFeatures as $uf)
                            <li class="flex items-start gap-4">
                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500/15">
                                    <svg aria-hidden="true" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $uf[0] }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $uf[1] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="relative" data-reveal style="--reveal-delay: 0.1s;" aria-hidden="true">
                    <div class="animate-float rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-6 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500">
                                    <span class="font-bold text-white">ES</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">Event Schedule</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $installedVersion }} installed</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Update available</span>
                        </div>
                        <div class="mb-4 rounded-xl bg-gray-100 p-4 dark:bg-[#0f0f14]">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-300">New version:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $nextVersion }}</span>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">New features, bug fixes, and security updates</div>
                        </div>
                        <div class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 py-3 font-medium text-white">
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Update now
                        </div>
                    </div>
                    <p class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">The update notice in your admin panel. Version numbers are live.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Act 04: Where it can go                                     -->
    <!-- ============================================================ -->
    @php
        // Two weighted cards, not a table. The old flat table gave both options
        // identical visual weight on a page whose whole job is selfhosting, and
        // needed a duplicated stacked block below md. One implementation now
        // covers every width, and the selfhost card carries the emphasis the
        // way /pricing emphasises Pro.
        //
        // Per-row icon says what kind of fact it is: 'them' = the hosted side
        // does it for you, 'you' = you do it. Amber stays out of it - the
        // operator-responsibility rule is spent on the dark band above, and
        // reusing it here would read as a warning rather than a comparison.
        // Plan prices come from the marketing.* view composer as $proMonthly / $entMonthly,
        // so this page and /pricing can never quote different numbers.
        $comparison = [
            [
                'title' => 'Hosted',
                'chip' => 'Under five minutes',
                'lead' => false,
                'rows' => [
                    ['Setup', 'We have it running before you finish your coffee'],
                    ['Infrastructure', 'We run the servers, backups and updates'],
                    ['Updates', 'Automatic, you never think about it'],
                    ['Features', 'Free, Pro at '.plan_price($proMonthly).'/mo or Enterprise at '.plan_price($entMonthly).'/mo'],
                    ['Your data', 'Hosted by us, exportable at any time'],
                    ['Support', 'Email support, priority on Enterprise'],
                ],
                'verdict' => 'Right for almost everyone. Start here unless you have a reason not to.',
            ],
            [
                'title' => 'Selfhosted',
                'chip' => 'An afternoon',
                'lead' => true,
                'rows' => [
                    ['Setup', 'One-click installer, Docker, or a zip and a wizard'],
                    ['Infrastructure', 'Your server, your SSL, your cron, your backups'],
                    ['Updates', 'One click in your admin panel, when you choose'],
                    ['Features', 'Every Pro and Enterprise feature, included'],
                    ['Your data', 'Entirely on your own infrastructure'],
                    ['Support', 'GitHub issues and discussions'],
                ],
                'verdict' => 'Right when the data has to be yours, or you are building on top of it.',
            ],
        ];
    @endphp
    <section id="compare" class="relative scroll-mt-24 overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="relative pb-6 lg:pb-10" data-reveal>
                <span aria-hidden="true" class="es-glyph">04</span>
                <div class="relative max-w-2xl">
                    <div class="es-prompt mb-3" aria-hidden="true"><span class="es-prompt-path">~/eventschedule</span> $ diff hosted selfhost</div>
                    <h2 class="es-balance mb-3 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl">Should you actually <span class="text-gradient-selfhost">selfhost?</span></h2>
                    <p class="text-lg text-gray-500 dark:text-gray-400">Honestly, most people should not. Selfhosting earns its keep when the data has to live on your own infrastructure, or when you are building something on top of it.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="100">
                @foreach ($comparison as $col)
                    <div data-reveal class="relative flex flex-col rounded-3xl border p-7 transition-all duration-200 sm:p-8 {{ $col['lead']
                        ? 'border-emerald-300 bg-white ring-2 ring-emerald-500/25 dark:border-emerald-500/40 dark:bg-white/[0.06] dark:ring-emerald-400/20'
                        : 'border-gray-200 bg-white dark:border-white/10 dark:bg-white/[0.04]' }}">

                        @if ($col['lead'])
                            <span class="absolute -top-3 rounded-full bg-gradient-to-r from-emerald-600 to-teal-500 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-white shadow-lg shadow-emerald-500/30 ltr:right-6 rtl:left-6">You are here</span>
                        @endif

                        <div class="mb-5 flex items-center justify-between gap-3">
                            <h3 class="text-xl font-bold {{ $col['lead'] ? 'text-gray-900 dark:text-white' : 'text-gray-900 dark:text-white' }}">{{ $col['title'] }}</h3>
                            <span class="whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold {{ $col['lead']
                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
                                : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' }}">{{ $col['chip'] }}</span>
                        </div>

                        <dl class="mb-6 space-y-3.5">
                            @foreach ($col['rows'] as [$aspect, $detail])
                                <div class="flex items-start gap-3">
                                    <svg aria-hidden="true" class="mt-1 h-4 w-4 shrink-0 {{ $col['lead'] ? 'text-emerald-500 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $aspect }}</dt>
                                        <dd class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $detail }}</dd>
                                    </div>
                                </div>
                            @endforeach
                        </dl>

                        <p class="mt-auto border-t border-gray-100 pt-4 text-sm font-medium text-gray-500 dark:border-white/5 dark:text-gray-400">{{ $col['verdict'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex flex-col items-center justify-center gap-3 text-center sm:flex-row" data-reveal>
                <p class="text-gray-500 dark:text-gray-400">Not sure? The hosted version is free to start.</p>
                <div class="flex items-center gap-3">
                    <a href="{{ marketing_url('/pricing') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 py-3 font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                        Compare plans
                    </a>
                    <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center gap-2 rounded-2xl px-4 py-3 font-semibold text-emerald-700 transition-all hover:gap-3 dark:text-emerald-400">
                        Try hosted
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- White-label SaaS                                            -->
    <!-- ============================================================ -->
    <section id="saas" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div data-reveal="panel" class="relative overflow-hidden rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 p-8 dark:border-emerald-500/20 dark:from-emerald-900/40 dark:via-teal-900/30 dark:to-cyan-900/20 md:p-12">
                <div class="pointer-events-none absolute right-0 top-0 h-64 w-64 rounded-full bg-emerald-500/20 blur-[100px]" aria-hidden="true"></div>
                <div class="pointer-events-none absolute bottom-0 left-0 h-64 w-64 rounded-full bg-teal-500/20 blur-[100px]" aria-hidden="true"></div>

                <div class="relative z-10 grid items-center gap-8 lg:grid-cols-2">
                    <div>
                        <div class="es-prompt mb-3" aria-hidden="true"><span class="es-prompt-path">~/eventschedule</span> $ IS_HOSTED=true</div>
                        <h2 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">Or turn it into your own product</h2>
                        <p class="mb-6 text-xl text-gray-600 dark:text-gray-300">The same install runs in multi-tenant mode. Give every customer a subdomain, set your own prices, bill them through your Stripe account and keep all of it.</p>
                        <ul class="mb-8 space-y-3">
                            {{-- "your own prices", not "your own tiers": the tier names are Free,
                                 Pro and Enterprise in code; what you supply is the Stripe Price IDs. --}}
                            @foreach (['Multi-tenant subdomains built in', 'Stripe subscription billing at your own prices', 'White-label branding, bar one small licence credit', 'No licence fee and no revenue share'] as $item)
                                <li class="flex items-center gap-3 text-gray-700 dark:text-gray-200">
                                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ marketing_url('/saas') }}" class="group inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3 font-medium text-white shadow-lg shadow-emerald-500/25 transition-all hover:from-emerald-500 hover:to-teal-500">
                            See the white-label setup
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>

                    <div class="relative" aria-hidden="true">
                        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-[#0f0f14]">
                            <div class="mb-6 flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600">
                                    <span class="text-sm font-bold text-white">YB</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">Your Brand</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">yourbrand.com</div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="rounded-xl border border-gray-100 bg-gray-100 p-4 dark:border-white/5 dark:bg-white/5">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Monthly subscribers</span>
                                        <span class="text-sm text-emerald-700 dark:text-emerald-400">+12%</span>
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">247</div>
                                </div>
                                <div class="rounded-xl border border-gray-100 bg-gray-100 p-4 dark:border-white/5 dark:bg-white/5">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Monthly revenue</span>
                                        <span class="text-sm text-emerald-700 dark:text-emerald-400">+8%</span>
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">$4,940</div>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach ([['Free', '142 users'], ['Pro', '89 users'], ['Enterprise', '16 users']] as [$tierName, $tierCount])
                                        <div class="rounded-lg bg-emerald-500/15 p-3 text-center">
                                            <div class="font-semibold text-gray-900 dark:text-white">{{ $tierName }}</div>
                                            <div class="text-xs text-emerald-700 dark:text-emerald-300">{{ $tierCount }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <p class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">A dashboard you could be running. Illustrative figures.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Docs map                                                    -->
    <!-- ============================================================ -->
    @php
        $docGroups = [
            [
                'heading' => 'Setup',
                'links' => [
                    ['Installation guide', 'marketing.docs.selfhost.installation'],
                    ['Email and SMTP', 'marketing.docs.selfhost.email'],
                    ['AI keys', 'marketing.docs.selfhost.ai'],
                    ['Overview', 'marketing.docs.selfhost'],
                ],
            ],
            [
                'heading' => 'Integrations',
                'links' => [
                    ['Stripe', 'marketing.docs.selfhost.stripe'],
                    ['Google Calendar', 'marketing.docs.selfhost.google_calendar'],
                    ['Outlook and Microsoft 365', 'marketing.docs.selfhost.microsoft_calendar'],
                    ['Boost ads', 'marketing.docs.selfhost.boost'],
                ],
            ],
            [
                'heading' => 'Operations',
                'links' => [
                    ['Admin panel', 'marketing.docs.selfhost.admin'],
                    ['Federation', 'marketing.docs.selfhost.federation'],
                    ['Accessibility', 'marketing.docs.selfhost.accessibility'],
                ],
            ],
        ];
    @endphp
    <section id="docs" class="scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center" data-reveal>
                <div class="es-prompt mb-3" aria-hidden="true"><span class="es-prompt-path">~/eventschedule</span> $ man selfhost</div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">The whole manual, <span class="text-gradient-selfhost">in one place</span></h2>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="80">
                @foreach ($docGroups as $docGroup)
                    <div data-reveal="panel" class="rounded-2xl border border-gray-200 bg-gray-50 p-6 transition-all duration-200 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-emerald-500/40">
                        <h3 class="mb-4 text-xs font-bold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">{{ $docGroup['heading'] }}</h3>
                        <ul class="space-y-2.5">
                            @foreach ($docGroup['links'] as [$docLabel, $docRoute])
                                <li>
                                    <a href="{{ route($docRoute) }}" class="group inline-flex items-center gap-2 font-medium text-gray-700 transition-colors hover:text-emerald-600 dark:text-gray-300 dark:hover:text-emerald-400">
                                        {{ $docLabel }}
                                        <svg aria-hidden="true" class="h-4 w-4 opacity-0 transition-all group-hover:translate-x-0.5 group-hover:opacity-100 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Open source                                                 -->
    <!-- ============================================================ -->
    <section id="source" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <div class="mx-auto mb-8 inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-gray-200 dark:bg-white/10" data-reveal>
                <svg aria-hidden="true" class="h-10 w-10 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                </svg>
            </div>
            <h2 class="es-balance mb-6 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Read it, change it, <span class="text-gradient-selfhost">fork it</span></h2>
            <p class="mx-auto mb-8 max-w-2xl text-lg text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">Event Schedule is open source under the Attribution Assurance License. Inspect the code, send a pull request, or take it in your own direction. The AAL asks only that the original attribution stays in place.</p>

            <div data-reveal>
                @include('marketing.partials.github-star-badge')
            </div>

            <div class="mb-10 grid gap-4 sm:grid-cols-3" data-reveal-group="80">
                @php
                    $osStats = [['100%', 'Open source'], ['Free', 'Forever'], ['AAL', 'Permissive licence']];
                @endphp
                @foreach ($osStats as $stat)
                    <div data-reveal class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50 p-6 transition-all duration-200 hover:-translate-y-1 hover:shadow-lg dark:border-emerald-500/20 dark:from-emerald-900/30 dark:to-teal-900/30">
                        <div class="mb-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stat[0] }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $stat[1] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap justify-center gap-4" data-reveal>
                @foreach ([['Main repository', 'https://github.com/eventschedule/eventschedule'], ['Docker files', 'https://github.com/eventschedule/dockerfiles'], ['Discussions', 'https://github.com/eventschedule/eventschedule/discussions']] as [$repoLabel, $repoUrl])
                    <a href="{{ $repoUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-gray-200 px-6 py-3 font-medium text-gray-900 transition-colors hover:bg-gray-300 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                        <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        {{ $repoLabel }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- FAQ                                                         -->
    <!-- ============================================================ -->
    @php
        $selfhostFaqs = [
            ['q' => 'Is Event Schedule really free to selfhost?', 'a' => 'Yes. Event Schedule is open source under the Attribution Assurance License. There is no licence fee, no per-event charge and no platform fee on ticket sales. Your only costs are the server and Stripe\'s own processing fees.'],
            ['q' => 'Do I get the paid features when I selfhost?', 'a' => 'A selfhosted install is treated as Enterprise throughout the code, so ticketing, team members, the API, AI generation and everything else are included at no cost. Two features, auto import from URLs and one-click app updates, exist only on selfhosted installs. Per-schedule custom domains are the one thing that does not carry over, because they belong to hosted mode and your install already runs on a domain you chose.'],
            ['q' => 'What do I need on the server?', 'a' => 'PHP 8.2 or newer with the usual extensions, MySQL 5.7+ or MariaDB 10.3+, Apache or Nginx with rewrites enabled, and an SSL certificate. Most shared hosts already meet this.'],
            ['q' => 'Can I install it on shared hosting?', 'a' => 'Yes. If your host offers Softaculous, Event Schedule installs in one click with the database and configuration set up for you. Otherwise upload the release zip and point your document root at the public directory.'],
            ['q' => 'How do updates work?', 'a' => 'When a new version is released, a notice appears in your admin panel. One click applies the update in seconds, database migrations included. No terminal access is required.'],
            ['q' => 'Do I have to set up a cron job?', 'a' => 'Yes, one line: "* * * * * php /path/to/eventschedule/artisan schedule:run". It drives reminder emails, calendar sync and the release of expired ticket reservations. Without it those stop running.'],
            ['q' => 'Does a selfhosted install send anything back to Event Schedule?', 'a' => 'No. There is no telemetry and no phone-home. The one optional exception is Federation, which is off by default and shares only your public events into the eventschedule.com listings, with every listing linking back to your own site. An admin has to switch it on, and each schedule can opt out.'],
            ['q' => 'Can I run it as a white-label SaaS for my own customers?', 'a' => 'Yes. Set IS_HOSTED=true and the same install runs multi-tenant, with a subdomain per customer, Stripe subscription billing and your own prices on the Pro and Enterprise tiers. You set the prices and keep the revenue. One thing to know before you price it: the licence credit stays on the public pages of every customer you charge. It is a small chip in the corner, a free schedule carries your own footer strip in its place, and it is the whole of what the software costs you.'],
            ['q' => 'Can I move from the hosted version to selfhosted?', 'a' => 'Yes. Backup and restore is built in, so you can export your schedule data, with images if you want them, and import it into your own install.'],
        ];
    @endphp
    <x-seo.faq-schema :items="$selfhostFaqs" />
    <section id="faq" class="scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-selfhost">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything people ask before they install.
                </p>
            </div>
            <div class="space-y-4" data-reveal-group="80">
                @foreach ($selfhostFaqs as $faq)
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-colors hover:border-emerald-300 dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-emerald-400/40">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 sm:p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-5 pb-5 text-gray-600 dark:text-gray-400 sm:px-6 sm:pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Finale                                                      -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-emerald-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(16, 185, 129, 0.3), rgba(16, 185, 129, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>

                </div>

                <div class="relative z-10">
                    <div class="es-prompt mb-5" aria-hidden="true"><span class="es-prompt-path">~/eventschedule</span> $ ./start</div>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your server is <span class="text-gradient-selfhost">waiting</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Pick an install method and you can be running your own event platform this afternoon.
                    </p>
                    <div class="flex flex-col flex-wrap items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ route('marketing.docs.selfhost.installation') }}" class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-emerald-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Read the installation guide
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center rounded-2xl border-2 border-white/30 px-8 py-4 text-lg font-semibold text-white transition-all hover:bg-white/10">
                            Or try the hosted version
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- Section dot navigation (desktop) -->
    @php
        $dotSections = [
            ['top', 'Top'],
            ['demo', 'See it running'],
            ['install', 'Install it'],
            ['included', 'What you get'],
            ['data', 'What you own'],
            ['compare', 'Hosted or selfhost'],
            ['docs', 'Docs'],
            ['faq', 'FAQ'],
            ['claim', 'Get started'],
        ];
    @endphp
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#15151c] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <!-- Install switcher + copy buttons (vanilla JS, no inline handlers) -->
    <script {!! nonce_attr() !!}>
        (function () {
            var wrap = document.querySelector('.es-tabs');
            if (!wrap) return;

            var tabs = Array.prototype.slice.call(wrap.querySelectorAll('[role="tab"]'));
            var panels = Array.prototype.slice.call(wrap.querySelectorAll('[role="tabpanel"]'));
            if (tabs.length !== panels.length || !tabs.length) return;

            function select(index, focus) {
                tabs.forEach(function (tab, i) {
                    var active = i === index;
                    tab.setAttribute('aria-selected', active ? 'true' : 'false');
                    tab.setAttribute('tabindex', active ? '0' : '-1');
                    if (active && focus) tab.focus();
                });
                panels.forEach(function (panel, i) {
                    if (i === index) {
                        panel.removeAttribute('hidden');
                    } else {
                        panel.setAttribute('hidden', '');
                    }
                });
            }

            tabs.forEach(function (tab, i) {
                tab.addEventListener('click', function () {
                    // Pointer-driven: drop the keyboard state so clicking a tab
                    // does not leave a focus ring behind.
                    wrap.classList.remove('is-kbd');
                    select(i, false);
                });
                tab.addEventListener('keydown', function (e) {
                    var next = null;
                    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') next = (i + 1) % tabs.length;
                    if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') next = (i - 1 + tabs.length) % tabs.length;
                    if (e.key === 'Home') next = 0;
                    if (e.key === 'End') next = tabs.length - 1;
                    if (next === null) return;
                    e.preventDefault();
                    // Arrow keys move focus programmatically, which Chrome does not
                    // reliably treat as :focus-visible, so flag the keyboard state.
                    wrap.classList.add('is-kbd');
                    select(next, true);
                });
            });

            // Only now do the panels start hiding: without JS all three stay visible.
            wrap.classList.add('is-ready');
            select(0, false);
        })();

        (function () {
            document.addEventListener('click', function (e) {
                var button = e.target.closest('.es-copy');
                if (!button || !navigator.clipboard) return;
                navigator.clipboard.writeText(button.getAttribute('data-copy') || '').then(function () {
                    var original = button.textContent;
                    button.textContent = 'Copied';
                    setTimeout(function () { button.textContent = original; }, 2000);
                }).catch(function () {});
            });
        })();
    </script>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
