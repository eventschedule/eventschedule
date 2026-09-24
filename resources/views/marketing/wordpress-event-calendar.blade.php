<x-marketing-layout>
    <x-slot name="title">WordPress Event Calendar | Add One Without a Plugin</x-slot>
    <x-slot name="description">Add a WordPress event calendar with one Custom HTML block: step-by-step embed code, free RSVP, ticket forms on Pro, and nothing to install, update or patch.</x-slot>
    <x-slot name="breadcrumbTitle">WordPress Event Calendar</x-slot>

    {{-- There is no Event Schedule WordPress plugin, in this repo or anywhere else, and this page
         must never imply one. What it teaches is the iframe embed, which works on any WordPress
         site that allows the tag.

         The steps are read off the code, not the other pages:
         - The schedule snippet is components/embed-modal.blade.php: Actions > Embed Schedule, a
           Layout picker, and '<iframe src="...?embed=true" width="100%" height="800"
           frameborder="0" style="border: none;"></iframe>', plus a "Powered by Event Schedule"
           line OUTSIDE the frame when Role::showBranding() (a Free hosted schedule).
         - The event snippet is components/embed-ticket-modal.blade.php: the event URL plus
           ?tickets=true&embed=true or ?rsvp=true&embed=true. The editor's link to it is Pro-only
           (event/edit.blade.php), so Free users type the RSVP URL themselves (docs/tickets
           #embed-widget). The ticket widget is Pro (Event::hasProTicketingPlan()); the RSVP one is
           free.
         - Events in an embedded calendar open their page in a new tab (role/partials/
           calendar.blade.php, target="_blank" when $embed).
         - The month grid needs about 768px of frame width; below it the embed shows the agenda
           (docs/sharing #embed).
         WordPress facts: iframes survive the Custom HTML block only for users with the
         unfiltered_html capability (Administrators and Editors on a single site, Super Admins on
         multisite). On WordPress.com the tag needs a paid plan with hosting features active
         (wordpress.com/support/wordpress-editor/blocks/custom-html-block/, checked 2026-09-23).

         Differentiation: /features/embed-calendar owns "embed event calendar on website" for any
         site; /the-events-calendar-alternative is the head-to-head with that plugin. This page is
         the WordPress how-to, and links to both. --}}

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule - Event Calendar for WordPress"
        description="A hosted event calendar you add to any WordPress page with a Custom HTML block: an embedded month calendar or list, an embeddable registration or ticket form, and no plugin to install or update." />
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to add an event calendar to a WordPress site",
        "description": "Copy the embed code from Event Schedule and paste it into a Custom HTML block in WordPress.",
        "totalTime": "PT10M",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Create your schedule",
                "text": "Sign up for Event Schedule, create a schedule and add your events, by hand or by pasting an announcement or a flyer into the AI importer."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Copy the embed code",
                "text": "In the admin panel, open the Actions menu and choose Embed Schedule. Pick a layout and copy the Iframe Code."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Add a Custom HTML block",
                "text": "Edit the WordPress page where the calendar belongs, add a Custom HTML block and paste the code into it. In the classic editor, paste it on the Text tab."
            },
            {
                "@type": "HowToStep",
                "position": 4,
                "name": "Preview and publish",
                "text": "Preview the page, adjust the height in the code if the frame needs more room, then publish or update the page."
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
           WordPress event calendar "The Block" styles.

           CONCEPT: one block in the editor. The entire integration is a
           Custom HTML block holding four lines of code, and the page's
           job is to make that feel as small as it is. So the hero is the
           block itself, code and all, and the steps below it are numbered
           like an instruction sheet.

           DELIBERATELY NOT: /features/embed-calendar owns the pasteboard
           device and the any-website pitch; /the-events-calendar-
           alternative is the comparison table. No WordPress logo, no
           plugin imagery: there is no plugin.

           COLOUR: harbour blue. Sky-800 #075985 light, sky-300 #7dd3fc
           dark. Cool and plain, like an admin screen.
             light ground #f3f7fa: ink #0f172a 17.0, muted #475569 7.2,
                                   accent #075985 7.4
             dark ground  #0a0f14: ink #e8eef4 16.7, muted #9fb0c0 8.1,
                                   accent #7dd3fc 12.2
           The code block is FIXED dark in both modes, as editors show it.
           ============================================================== */

        .es-wp-page { background-color: #f3f7fa; color: #0f172a; }
        .dark .es-wp-page { background-color: #0a0f14; color: #e8eef4; }

        .es-wp-ink { color: #0f172a; }
        .dark .es-wp-ink { color: #e8eef4; }
        .es-wp-muted { color: #475569; }
        .dark .es-wp-muted { color: #9fb0c0; }
        .es-wp-accent { color: #075985; }
        .dark .es-wp-accent { color: #7dd3fc; }

        .es-wp-rule { border-top: 1px solid rgba(15, 23, 42, 0.10); }
        .dark .es-wp-rule { border-top-color: rgba(232, 238, 244, 0.10); }

        .es-wp-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #075985;
        }
        .dark .es-wp-tag { color: #7dd3fc; }

        .es-wp-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            border: 1.5px solid rgba(7, 89, 133, 0.35);
            font-size: 0.8125rem;
            font-weight: 800;
            color: #075985;
        }
        .dark .es-wp-mark { border-color: rgba(125, 211, 252, 0.35); color: #7dd3fc; }

        .es-wp-panel {
            background-color: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.10);
            border-radius: 1rem;
        }
        .dark .es-wp-panel {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(232, 238, 244, 0.10);
        }

        .es-wp-step {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            flex: none;
            border-radius: 9999px;
            background-color: #075985;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 800;
        }
        .dark .es-wp-step { background-color: #7dd3fc; color: #0a0f14; }

        /* ---- THE CODE BLOCK. Fixed dark in both modes; see the contract above. ---- */
        .es-wp-code {
            background-color: #0f172a;
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0.75rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8125rem;
            line-height: 1.6;
            overflow-x: auto;
            white-space: pre;
        }
        .es-wp-code .k { color: #7dd3fc; }
        .es-wp-code .s { color: #bef264; }
        .es-wp-code .c { color: #94a3b8; }

        .es-wp-editor {
            background-color: #ffffff;
            color: #0f172a;
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 1rem;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.16);
        }
        .dark .es-wp-editor { box-shadow: 0 18px 45px rgba(0, 0, 0, 0.55); }
        .es-wp-editor-muted { color: #475569; }
        .es-wp-editor-bar { border-bottom: 1px solid rgba(15, 23, 42, 0.08); }

        .es-wp-band { background-color: #0b1620; }
        .es-wp-band-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #7dd3fc;
        }
        .es-wp-band-grad {
            background-image: linear-gradient(90deg, #7dd3fc, #22d3ee);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .es-wp-band .grid-overlay { background-image:
            linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px); }
        .es-wp-band .es-claim:focus-within { border-color: rgba(125, 211, 252, 0.55); }

        #es-wp-page a:focus-visible,
        #es-wp-page summary:focus-visible,
        #es-wp-page button:focus-visible {
            outline: 2px solid #075985;
            outline-offset: 2px;
        }
        .dark #es-wp-page a:focus-visible,
        .dark #es-wp-page summary:focus-visible,
        .dark #es-wp-page button:focus-visible { outline-color: #7dd3fc; }
    </style>

    @php
        // The same URL shape the Embed Schedule dialog writes, on an example schedule name.
        $wpExampleUrl = route('role.view_guest', ['subdomain' => 'your-schedule']);
    @endphp

    <div id="es-wp-page" class="es-wp-page">

        <!-- ============================================================ -->
        <!-- 1. Hero: the block                                           -->
        <!-- ============================================================ -->
        <section id="top" class="relative scroll-mt-24 overflow-hidden py-16 lg:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                    <div class="min-w-0">
                        <h1 class="es-balance es-wp-ink text-4xl font-black tracking-tight md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            <x-marketing.hero-eyebrow class="block es-wp-tag mb-4">WordPress event calendar &middot; no plugin</x-marketing.hero-eyebrow>
                            One block. <span class="es-wp-accent">Nothing to update.</span>
                        </h1>
                        <p class="es-wp-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                            Keep your events on Event Schedule and show them on your WordPress site with a single Custom HTML block. There is no plugin to install, no database tables added to your site and no update that breaks your theme. Change an event once and your site shows it straight away.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#075985] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#0c4a6e]">
                                Start for free
                            </a>
                            <a href="#steps" class="es-wp-ink inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 font-semibold transition-colors hover:border-[#075985] dark:border-white/15">
                                See the steps
                            </a>
                        </div>
                        <p class="es-wp-muted mt-4 text-sm" data-reveal style="--reveal-delay: 0.2s;">
                            Embedding the calendar is free on every plan. Takes about ten minutes.
                        </p>
                    </div>

                    <div class="es-wp-editor mx-auto w-full min-w-0 max-w-md overflow-hidden" data-reveal="panel" style="--reveal-delay: 0.1s;" aria-label="Example Custom HTML block">
                        <div class="es-wp-editor-bar flex items-center justify-between px-5 py-3">
                            <span class="text-sm font-semibold">Custom HTML</span>
                            <span class="es-wp-editor-muted text-xs">Page: Events</span>
                        </div>
                        <div class="p-5">
                            <pre class="es-wp-code p-4" dir="ltr"><span class="k">&lt;iframe</span>
  src=<span class="s">"{{ $wpExampleUrl }}?embed=true"</span>
  width=<span class="s">"100%"</span> height=<span class="s">"800"</span>
  frameborder=<span class="s">"0"</span> style=<span class="s">"border: none;"</span><span class="k">&gt;&lt;/iframe&gt;</span></pre>
                            <p class="es-wp-editor-muted mt-4 text-xs leading-relaxed">
                                Copied from Event Schedule, pasted here, published. The frame loads your live calendar every time the page is viewed.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. Step by step                                              -->
        <!-- ============================================================ -->
        <section id="steps" class="es-wp-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-wp-mark mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-wp-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Step by step</p>
                    <h2 class="es-balance es-wp-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        From no calendar <span class="es-wp-accent">to a published page.</span>
                    </h2>
                    <p class="es-wp-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Four steps, the last two inside WordPress. Nothing here needs a developer, FTP access or a child theme.
                    </p>
                </div>

                @php
                    $wpSteps = [
                        ['Create your schedule and add events', 'Sign up and create a schedule for your venue, group or organization. Add events by hand, set weekly or monthly ones to repeat, or paste an announcement or drop a flyer into the AI importer and check what it fills in. If your events already live in Google Calendar or Outlook, two-way sync brings them across.'],
                        ['Copy the embed code', 'In the admin panel, open the Actions menu and choose Embed Schedule. Pick a layout: leave it on your schedule\'s default, or pin this frame to the month Calendar or the List. The preview reloads as you choose. Then press the copy button beside Iframe Code.'],
                        ['Paste it into a Custom HTML block', 'In WordPress, edit the page that should show your events, such as Events or What\'s On. Press the plus button, search for Custom HTML, add the block and paste the code into it. In the classic editor, switch to the Text tab first, because the Visual tab escapes the code instead of running it.'],
                        ['Preview, then publish', 'Switch the block to Preview, or preview the page, to see your calendar in place. If the frame shows a scrollbar, raise the height number in the code, for example to 1000. Then press Update or Publish. From now on, you edit events on Event Schedule and never touch this block again.'],
                    ];
                @endphp
                <ol class="space-y-4" data-reveal-group="80">
                    @foreach ($wpSteps as $wsIndex => [$wsName, $wsBody])
                        <li class="es-wp-panel flex gap-4 p-6" data-reveal="panel">
                            <span class="es-wp-step" aria-hidden="true">{{ $wsIndex + 1 }}</span>
                            <div class="min-w-0">
                                <h3 class="es-wp-ink text-base font-bold">{{ $wsName }}</h3>
                                <p class="es-wp-muted mt-2 text-sm leading-relaxed">{{ $wsBody }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>

                <div class="es-wp-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-wp-ink text-base font-bold">Not using the block editor?</h3>
                    <p class="es-wp-muted mt-2 text-sm leading-relaxed">
                        The code is plain HTML, so it goes anywhere WordPress accepts HTML. Put it in a Custom HTML widget to show upcoming events in a sidebar or footer, in a Custom HTML block inside the site editor's templates, or in the HTML or code element most page builders offer. The steps are the same: paste, preview, publish.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. Options in the URL                                        -->
        <!-- ============================================================ -->
        <section id="options" class="es-wp-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-wp-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-wp-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Fitting your theme</p>
                    <h2 class="es-balance es-wp-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Tune it with <span class="es-wp-accent">the address, not a settings page.</span>
                    </h2>
                    <p class="es-wp-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Everything the frame can do is set by adding to the address inside the code, after <code dir="ltr">?embed=true</code> and separated by <code dir="ltr">&amp;</code>. Your colours, background and font come from your schedule's own style settings.
                    </p>
                </div>

                @php
                    $wpParams = [
                        ['layout=calendar or layout=list', 'Pin the month grid or the list, whatever your schedule\'s default is. The grid needs about 768 pixels of width, so in a narrow sidebar or on a phone the frame shows a day-by-day agenda instead.'],
                        ['schedule=slug', 'Show one sub-schedule, such as only the workshops, so each page of your site can carry just the events it is about.'],
                        ['dark=true', 'Force dark mode for a dark theme. Leave it off and the frame follows the theme the visitor chose, or their system setting.'],
                        ['lang=xx', 'Show the frame in your schedule\'s second language, for a bilingual site with a page in each language.'],
                    ];
                @endphp
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="80">
                    @foreach ($wpParams as [$wpName, $wpBody])
                        <div class="es-wp-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-wp-ink text-base font-bold"><code dir="ltr">{{ $wpName }}</code></h3>
                            <p class="es-wp-muted mt-2 text-sm leading-relaxed">{{ $wpBody }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="es-wp-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-wp-ink text-base font-bold">What visitors get inside the frame</h3>
                    <p class="es-wp-muted mt-2 text-sm leading-relaxed">
                        Your events only, with no header, footer or ads. Clicking an event opens its full page in a new tab, with the details, the map, add-to-calendar and the Register or Buy button, so your WordPress page stays open behind it. On a Free schedule the copied code adds a small "Powered by Event Schedule" line under the frame, outside it, where you can see and delete it; <x-link href="{{ marketing_url('/features/white-label') }}">removing our branding</x-link> from your pages is part of Pro. The full list of options is in the <x-link href="{{ marketing_url('/docs/sharing') }}#embed-parameters">embedding guide</x-link>.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Registration and tickets                                  -->
        <!-- ============================================================ -->
        <section id="tickets" class="es-wp-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-wp-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-wp-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Sign-ups on the page</p>
                    <h2 class="es-balance es-wp-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Let people register <span class="es-wp-accent">without leaving your site.</span>
                    </h2>
                    <p class="es-wp-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        The calendar embed lists everything. For one event with its own WordPress page, such as the annual gala or the spring workshop, you can embed its sign-up form instead.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="80">
                    <div class="es-wp-panel flex flex-col p-6" data-reveal="panel">
                        <p class="es-wp-tag">Free on every plan</p>
                        <h3 class="es-wp-ink mt-2 text-base font-bold">The registration form</h3>
                        <p class="es-wp-muted mt-2 text-sm leading-relaxed">
                            Turn on <x-link href="{{ marketing_url('/features/registration') }}">free registration</x-link> for the event, open its page on Event Schedule, and copy its address. Add <code dir="ltr">?rsvp=true&amp;embed=true</code> to the end and use it as the src of the same kind of iframe, in a Custom HTML block on your event's WordPress page. Guests register inside the frame and get their confirmation and QR code by email.
                        </p>
                        <pre class="es-wp-code mt-4 p-4" dir="ltr"><span class="k">&lt;iframe</span> src=<span class="s">"…/your-event?rsvp=true&amp;embed=true"</span>
  width=<span class="s">"100%"</span> height=<span class="s">"700"</span> style=<span class="s">"border: none;"</span><span class="k">&gt;&lt;/iframe&gt;</span></pre>
                    </div>
                    <div class="es-wp-panel flex flex-col p-6" data-reveal="panel">
                        <p class="es-wp-tag">Pro</p>
                        <h3 class="es-wp-ink mt-2 text-base font-bold">The ticket checkout</h3>
                        <p class="es-wp-muted mt-2 text-sm leading-relaxed">
                            Selling tickets with a price is a Pro feature, and so is the <x-link href="{{ marketing_url('/features/embed-tickets') }}">ticket widget</x-link>. On Pro, the event editor has an Embed Tickets link next to the Tickets heading that hands you the code. Buyers choose tickets inside the frame; card and PayPal payments open in the full browser window, because payment pages refuse to load inside another site's frame, and bring the buyer back to their ticket afterwards. There is no platform fee on any ticket.
                        </p>
                        <a href="{{ marketing_url('/features/ticketing') }}" class="es-wp-accent mt-auto inline-flex items-center pt-5 text-sm font-semibold hover:underline">
                            How ticketing works
                            <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Embed or plugin                                           -->
        <!-- ============================================================ -->
        <section id="why" class="es-wp-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-wp-mark mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-wp-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Embed or plugin</p>
                    <h2 class="es-balance es-wp-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Why keep the calendar <span class="es-wp-accent">outside WordPress?</span>
                    </h2>
                    <p class="es-wp-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        An events plugin runs inside your site, which is its strength and its cost. An embedded calendar runs somewhere else and shows up on your page. Here is what that trade gets you.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="80">
                    <div class="es-wp-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-wp-ink text-base font-bold">No plugin to keep alive</h3>
                        <p class="es-wp-muted mt-2 text-sm leading-relaxed">
                            There is nothing on your WordPress server to update, and nothing that stops working after a WordPress, PHP or theme upgrade. Your site stays as lean as it was, and the events never touch your WordPress database.
                        </p>
                    </div>
                    <div class="es-wp-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-wp-ink text-base font-bold">Your events outlive your theme</h3>
                        <p class="es-wp-muted mt-2 text-sm leading-relaxed">
                            Redesign the site, move hosts or leave WordPress altogether and the calendar comes with you: paste the same code into the new site. The schedule also has its own public page, so it works even with no website at all.
                        </p>
                    </div>
                    <div class="es-wp-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-wp-ink text-base font-bold">More than a list of dates</h3>
                        <p class="es-wp-muted mt-2 text-sm leading-relaxed">
                            The same schedule takes registrations, sells tickets on Pro, scans QR codes at the door, syncs both ways with Google Calendar and Outlook, and emails a digest of new events to people who sign up. None of it needs an add-on.
                        </p>
                    </div>
                    <div class="es-wp-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-wp-ink text-base font-bold">Your page still ranks</h3>
                        <p class="es-wp-muted mt-2 text-sm leading-relaxed">
                            The address inside the frame is served as noindex, so it never competes with your WordPress page in search. Your schedule's own public page, with a page for each event, is there for search engines to find as well.
                        </p>
                    </div>
                </div>

                <div class="es-wp-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-wp-ink text-base font-bold">When a plugin is the better fit</h3>
                    <p class="es-wp-muted mt-2 text-sm leading-relaxed">
                        If you want every event stored as a WordPress post and styled by your own theme's templates, an events plugin is built for that and an iframe is not. We compare the most popular one, feature by feature, on the page for <x-link href="{{ marketing_url('/the-events-calendar-alternative') }}">The Events Calendar alternative</x-link>. For embedding on any other website builder, see <x-link href="{{ marketing_url('/features/embed-calendar') }}">embedding an event calendar</x-link>.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Troubleshooting                                           -->
        <!-- ============================================================ -->
        <section id="troubleshooting" class="es-wp-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-wp-mark mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-wp-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">If it does not show</p>
                    <h2 class="es-balance es-wp-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Four things <span class="es-wp-accent">worth checking.</span>
                    </h2>
                </div>

                @php
                    $wpFixes = [
                        ['The block saved, but the calendar vanished', 'WordPress strips iframe code from users without the "unfiltered HTML" permission. On a normal site that means Administrators and Editors can embed it and Authors and Contributors cannot; on a multisite network only a Super Admin can. Ask someone with that role to paste the code.'],
                        ['It is a WordPress.com site', 'WordPress.com keeps iframe code only on a paid plan with its hosting features switched on. On other plans, link to your schedule\'s page from a button or menu item instead, which works on every plan.'],
                        ['The frame is blank or refused', 'Almost always a missing ?embed=true. Every other Event Schedule address refuses to load inside a frame, so copy the code from the Embed Schedule dialog rather than your browser\'s address bar. A security plugin that restricts which sites may be framed can do the same.'],
                        ['It is cut off or shows a scrollbar', 'The frame has the height written in the code and does not resize itself. Raise the height, for example from 800 to 1000, and keep the width at 100% so it fills the column on phones as well.'],
                    ];
                @endphp
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="80">
                    @foreach ($wpFixes as [$wfName, $wfBody])
                        <div class="es-wp-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-wp-ink text-base font-bold">{{ $wfName }}</h3>
                            <p class="es-wp-muted mt-2 text-sm leading-relaxed">{{ $wfBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        @include('marketing.partials.pricing-nudge')

        <!-- ============================================================ -->
        <!-- 7. FAQ                                                       -->
        <!-- ============================================================ -->
        @php
            $wordpressFaqs = [
                ['q' => 'Is there an Event Schedule WordPress plugin?', 'a' => 'No, and you do not need one. The calendar goes onto a WordPress page with the embed code from Event Schedule, pasted into a Custom HTML block. Because nothing is installed on your site, there is nothing to update or keep compatible.'],
                ['q' => 'How do I add an event calendar to a WordPress page?', 'a' => 'In Event Schedule, open the Actions menu, choose Embed Schedule and copy the Iframe Code. In WordPress, edit the page, add a Custom HTML block, paste the code and publish. In the classic editor, paste it on the Text tab.', 'link' => [marketing_url('/docs/sharing').'#embed', 'Embedding in the guide']],
                ['q' => 'Is it free?', 'a' => 'Yes. Embedding the calendar is free on every plan, and so is embedding a registration form for a free event. On a Free schedule the copied code includes a small "Powered by Event Schedule" line under the frame. Selling priced tickets and the ticket widget are on the Pro plan.'],
                ['q' => 'Will the calendar update on my site automatically?', 'a' => 'Yes. The frame loads your schedule live every time the page is viewed, so an event you add, move or cancel is right on your WordPress page at once. You never paste the code a second time.'],
                ['q' => 'Why did my iframe disappear after I saved the page?', 'a' => 'WordPress removes iframe code for users who lack the unfiltered HTML permission, which on a single site means anyone below Editor, and on a multisite network anyone who is not a Super Admin. Have an Administrator paste it. On WordPress.com, iframe code needs a paid plan with hosting features active.'],
                ['q' => 'Can I show a calendar in the sidebar or footer?', 'a' => 'Yes. Add a Custom HTML widget, or a Custom HTML block in the site editor, and paste the same code with a smaller height. Add layout=list to the address, since a narrow frame shows the list-style agenda anyway.'],
                ['q' => 'Can people register or buy tickets without leaving my site?', 'a' => 'Registration, yes, on every plan: add ?rsvp=true&embed=true to an event\'s address and embed it. The ticket widget is Pro. Buyers choose tickets in the frame, and online payments open in the full window before returning them to their ticket.'],
                ['q' => 'Does it work with Elementor, Divi or other page builders?', 'a' => 'Anything that accepts an HTML or code element can hold the embed, because it is a standard iframe tag. Add that element where the calendar should go and paste the code into it.'],
                ['q' => 'Will it match my theme?', 'a' => 'The frame takes its colours, background and font from your schedule\'s style settings, which you can set to suit your site, and dark=true forces dark mode. Custom CSS on the Pro plan applies inside the frame too.'],
                ['q' => 'How does this compare with The Events Calendar plugin?', 'a' => 'The Events Calendar runs inside WordPress and stores events as posts. Event Schedule runs outside it and is embedded, with registration, ticketing and two-way calendar sync built in. The full comparison is on our page for The Events Calendar alternative.', 'link' => [marketing_url('/the-events-calendar-alternative'), 'See the comparison']],
            ];
        @endphp
        <section id="faq" class="es-wp-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-wp-ink mb-10 text-center text-3xl font-black tracking-tight md:text-4xl" data-reveal>WordPress calendar questions</h2>
                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($wordpressFaqs as $faq)
                        <details class="es-wp-panel group p-5" data-reveal="panel">
                            <summary class="es-wp-ink flex cursor-pointer items-center justify-between gap-4 text-base font-semibold">
                                {{ $faq['q'] }}
                                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-45" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                            </summary>
                            <p class="es-wp-muted mt-3 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                            @if (! empty($faq['link']))
                                <a href="{{ $faq['link'][0] }}" class="es-wp-accent mt-3 inline-block text-sm font-semibold hover:underline">{{ $faq['link'][1] }}</a>
                            @endif
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$wordpressFaqs" />

        <!-- ============================================================ -->
        <!-- 8. Claim                                                     -->
        <!-- ============================================================ -->
        <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="es-wp-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-20" data-reveal="panel">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="grid-overlay absolute inset-0 opacity-25"></div>
                    </div>

                    <div class="relative z-10">
                        <p class="es-wp-band-tag mb-6">Free to start</p>
                        <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                            Your events, <span class="es-wp-band-grad">on your WordPress site</span>.
                        </h2>
                        <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                            Make the schedule here. Paste one block there.
                        </p>

                        <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-site" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg bg-white px-8 py-4 text-lg font-semibold text-[#0b1620] transition-colors hover:bg-gray-100">
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
