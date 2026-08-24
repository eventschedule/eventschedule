<x-marketing-layout>
    @php
        // $proMonthly comes from the marketing.* view composer.
        $trialDays = (int) config('app.trial_days', 7);

        // The wall in the hero: six upcoming events, each with its own flyer.
        // Two OPTIONAL settings are switched on in this drawing, and the caption
        // under the print says so, because neither is the default: the date
        // strip (date_position=overlay, a band across the TOP of the flyer per
        // GridDesign::addDateOverlay, showing AbstractEventDesign::
        // formatEventDate, "M j, Y"; the shipped default is None, no strip at
        // all) and the numbered badges (number_events, drawn in the schedule's
        // accent colour, top right in LTR, over the date band). May 16 2026 is a
        // Saturday, which is what the plate further down says.
        $pieces = [
            ['linear-gradient(150deg, #3a1f14 0%, #8f3a0d 100%)', 'May 16, 2026'],
            ['linear-gradient(150deg, #20211f 0%, #4c4a44 100%)', 'May 17, 2026'],
            ['linear-gradient(150deg, #4a1f0f 0%, #b3502a 100%)', 'May 21, 2026'],
            ['linear-gradient(150deg, #2b2118 0%, #7a5a35 100%)', 'May 22, 2026'],
            ['linear-gradient(150deg, #361a1a 0%, #a8410b 100%)', 'May 23, 2026'],
            ['linear-gradient(150deg, #1d2426 0%, #4a5a5c 100%)', 'May 27, 2026'],
        ];

        // What is hung and what is not. Every row is the query in
        // GraphicController::generateGraphicData(): the image is built from
        // events with a non-empty flyer_image_url, scopeUpcomingOrOngoing
        // (starts_at >= now, OR duration >= 24h and the end is still ahead, so
        // "still running" only covers multi-day events), accepted on this
        // schedule, not draft, not private, not cancelled and not password
        // protected, earliest first, capped by event_count (1 to 20, default
        // 20). The text runs the same query without the flyer requirement, and
        // "show all events" lifts the cap on the text only. max_per_schedule
        // then caps how many of those events any one linked talent or venue may
        // contribute (GraphicController::applyPerScheduleCap), backfilling from
        // further down the calendar - but it counts venues as well as talents and
        // is all-or-nothing, so a narrow line-up can still land under event_count.
        $ledger = [
            ['Has its own flyer image', 'yes', 'Required', 'no', 'Not needed'],
            ['Starts from now on, or is a multi-day event still running', 'yes', 'Required', 'yes', 'Required'],
            ['Accepted onto this schedule', 'yes', 'Required', 'yes', 'Required'],
            ['Still a draft', 'no', 'Never', 'no', 'Never'],
            ['Private, unlisted or password protected', 'no', 'Never', 'no', 'Never'],
            ['Cancelled', 'no', 'Never', 'no', 'Never'],
            ['A recurring event', 'no', 'Optional', 'no', 'Optional'],
        ];

        // EventGraphicGenerator::getAvailableLayouts(), plus the grid geometry
        // in GridDesign (400 by 480 tiles, centre cropped) and the list
        // geometry in ListDesign (200 by 135 thumbnail, details panel beside).
        $arrangements = [
            [
                'grid',
                'Grid',
                'Every flyer cropped to the same tile and centred, so the wall is even. The grid balances itself: three events make one row of three, four make a two by two. Flyers Per Row overrides it when you want a particular shape.',
            ],
            [
                'row',
                'Row',
                'One line, and every flyer keeps its own proportions. Use it when the artwork matters more than the tidiness, or when you have two or three pieces and want them large.',
            ],
            [
                'list',
                'List',
                'A small flyer, then the event name, its one-line summary, the venue and the date and time, with a separator between items. This is the arrangement that reads as a programme rather than a poster.',
            ],
        ];

        // AbstractEventDesign::SOCIAL_FORMATS. The finished graphic is
        // contain-scaled and centred onto the chosen canvas and the padding is
        // filled with the schedule's own background, so nothing is cropped or
        // stretched. Auto keeps the native, content-driven size.
        //
        // The widths below are ONE scale for all five mounts (1080 px = 5.4rem,
        // so 1200 px = 6rem), and the heights come from the real ratio. Drawing
        // them to a common height would have hidden the actual relationship:
        // three of the four fixed formats are the same 1080 px across, and a
        // story is nearly twice the height of a square.
        $mounts = [
            ['Square', '1080 × 1080', '1080 / 1080', '5.4rem', 'Feed posts'],
            ['Portrait', '1080 × 1350', '1080 / 1350', '5.4rem', 'The tallest a feed post gets'],
            ['Story', '1080 × 1920', '1080 / 1920', '5.4rem', 'Stories and statuses'],
            ['Landscape', '1200 × 630', '1200 / 630', '6rem', 'Link previews and email'],
        ];

        // A curated subset of EventTextGenerator::parseTemplate()'s variables.
        // The full list is documented in the user guide, and the same list is
        // documented on the Creating Schedules page: this page names a subset
        // and adds no variable of its own, so nothing here can drift.
        $vars = [
            ['{day_name}', 'Saturday, translated into your schedule\'s language'],
            ['{date_dmy}', '16/5, with a two-digit year added only when the event is in a different year than this one'],
            ['{time}', '20:00, or 8:00 PM if your schedule is not on 24-hour time'],
            ['{event_name}', 'Terra Nova Trio'],
            ['{short_description}', 'The one-line summary from the event'],
            ['{venue} | {city}', 'Casa Azul Jazz Club | Lisbon'],
            ['{url}', 'The event\'s own link, with or without https:// and with or without the event id'],
            ['{price}', 'The lowest ticket price, and blank when the event is free'],
            ['{number}', '1, 2, 3, matching the badges on the flyers'],
        ];

        $faqs = [
            [
                'q' => 'What do I actually get when I generate an event graphic?',
                'a' => 'Two things, side by side: one PNG image and one block of formatted text. The image is composed on the server from the flyer images of your upcoming events, at the shape you choose. The text is built from your template, one entry per event, ready to paste. You download the image and copy the text.',
            ],
            [
                'q' => 'Which of my events end up on the graphic?',
                'a' => 'Events that start from now on and have their own flyer image, earliest first, up to twenty, and you can lower that number. You can also cap how many events any one talent or venue contributes, so a busy act or room does not take the whole poster. A multi-day event still running counts as upcoming. Drafts, private, unlisted and password protected events, and cancelled events are never included, and recurring events can be excluded with one setting. Events without a flyer still appear in the text, because the text does not need artwork.',
            ],
            [
                'q' => 'What sizes can the image be?',
                'a' => 'Square at 1080 by 1080, portrait at 1080 by 1350, story at 1080 by 1920, or landscape at 1200 by 630. The finished graphic is scaled to fit inside the shape you pick and centred, and the padding around it is filled with your schedule\'s own background, so nothing is ever cropped or stretched. Leave it on Auto and the image keeps its natural size, which grows with the number of events.',
            ],
            [
                'q' => 'Can I put my own logo and wording on it?',
                'a' => 'Yes. Upload a header image and it runs across the top of every graphic at full width. Add header text for a headline and footer text for a sign-off of up to two lines, both of which accept variables like {schedule_name}, {month_name} and {first_event_date}. You can also switch on the date strip over each flyer and give it your own short template instead of the date. The background, the accent colour on the numbered badges and the colour of your header and footer wording all come from your schedule\'s appearance settings, so the graphic already looks like you.',
            ],
            [
                'q' => 'Can it post to Instagram for me?',
                'a' => 'No, and it does not pretend to. There is no social account connection here. What you get is an image file and a block of text, which is why it works everywhere: Instagram, WhatsApp, Telegram, Facebook, a newsletter, a printed sheet on the door. Asterisks around a word render as bold in WhatsApp and Telegram, which is why the default template uses them.',
            ],
            [
                'q' => 'Can the graphic be emailed out on a schedule?',
                'a' => 'On the Enterprise plan, yes. Set a daily, weekly or monthly cadence, the day and the hour, and a comma-separated list of recipients, then send yourself a test first to see exactly what lands. For designed campaigns to the people who follow your schedule, use newsletters instead.',
            ],
            [
                'q' => 'Is the event graphics feature free?',
                'a' => 'Generating event graphics is on the Pro plan, which is ' . plan_price($proMonthly) . ' a month with a ' . $trialDays . '-day free trial. Uploading a flyer to an event is free on every plan. The AI text prompt and the scheduled graphic emails are Enterprise.',
            ],
        ];

        $dotSections = [
            ['top', 'The wall'],
            ['what', 'What you get'],
            ['hang', 'What goes up'],
            ['arrange', 'Three hangs'],
            ['mount', 'The mount'],
            ['plate', 'The plate'],
            ['extras', 'The signature'],
            ['steps', 'Three steps'],
            ['plans', 'What it costs'],
            ['faq', 'Questions'],
            ['claim', 'Hang it'],
        ];
    @endphp

    <x-slot name="title">Event Graphics | Auto-Generated Images - Event Schedule</x-slot>
    <x-slot name="description">Auto-generate shareable event images and formatted text for Instagram, WhatsApp, email, and more. Custom header images and multiple output formats.</x-slot>
    <x-slot name="breadcrumbTitle">Event Graphics</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Event Graphics",
        "description": "Auto-generate shareable event images and formatted text for Instagram, WhatsApp, email, and more. Custom header images and multiple output formats.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Graphics Generation"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Event Graphics",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Graphics Generator",
        "operatingSystem": "Web",
        "description": "Auto-generate shareable event images and formatted text for Instagram, WhatsApp, email, and more. Custom header images and multiple output formats.",
        "offers": {
            "@type": "Offer",
            "price": "{{ $proMonthly }}",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Pro plan with {{ config('app.trial_days', 7) }}-day free trial"
        },
        "featureList": [
            "One PNG built from the flyer images of your upcoming events",
            "Grid, row and list arrangements",
            "Square, portrait, story and landscape output formats, or the native size",
            "Custom header image, header text and footer text with schedule variables",
            "Optional date strip across the top of each flyer, or on a bar above it",
            "Numbered badges that match the numbered caption",
            "A QR code on every flyer linking to that event's page",
            "Formatted text from your own template, ready to paste",
            "AI text transformations on the Enterprise plan",
            "Scheduled graphic emails on the Enterprise plan"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to generate an event graphic with Event Schedule",
        "description": "Give your events flyer images, open Event Graphics, and download the composed image with its caption.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Give your events a flyer",
                "text": "The graphic is built from event flyer images, so upload one to each event you want on the wall."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Open Event Graphics and choose the shape",
                "text": "Pick the arrangement, the output format, how many events to include, and add your header image, header text and footer text."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Download the image, copy the text",
                "text": "Download the PNG and copy the formatted caption, then paste both wherever you post."
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
           Event-graphics "The Gallery" styles.

           THE CONCEPT. This feature does not open an editor. It takes the
           flyers already sitting on your upcoming events and HANGS them:
           one wall, one frame, a plate underneath with the wording, a QR
           code in the corner of every piece, and the whole thing exported
           as a single PNG. So the page is a gallery, and the metaphor and
           the feature story are the same sentence: you do not design the
           poster, you decide how the work is hung.

           THE SIGNATURE OBJECT IS A PRINT, AND IT IS FIXED. .es-gal-print
           is the generated PNG, so it renders IDENTICALLY with .dark on and
           off: a file does not change because the reader's phone is in dark
           mode. Same for .es-gal-band (the room at night) and .es-gal-code
           (an always-dark caption surface, matching the docs shell). Verify
           with --bands=.es-gal-band,.es-gal-print,.es-gal-code and expect 0
           diffs. Nothing inside any of them may use a `dark:` utility or a
           shared class that carries its own .dark rule in marketing.css, so
           .grid-overlay, .animate-shimmer, .es-claim:focus-within and
           .es-gal-btn get band-scoped overrides AFTER the base rules, and
           .es-aurora / .glass are never used inside a band (they flip
           opacity and cannot be pinned). Ambient light inside a band is
           .es-gal-glow instead.

           DEVICES THIS PAGE MUST NOT BUILD. /for-visual-artists owns "The
           Studio Wall" (work in progress pinned up in a studio) and
           /for-musicians owns "The Tour Poster", so there is no torn paste
           up, no gig-poster typography and no tape. /for-libraries owns
           "The Catalog", so the plate here is a wall label, never a
           catalogue card. The frame is the FILE, which is the one thing
           this feature actually produces.

           COLOUR. The hue family this page already had stays: burnt orange.
           Deliberately not the amber/gold end of it, which seven other
           pages hold, and deliberately not the shared brand ramp. Values,
           measured, not guessed:
             #9a3d0c ink accent on the plaster ground #f5f2ee ....... 6.18
             #a8410b fill, white on it ............................... 6.13
             #fdba74 lit accent on the night ground #12100e ......... 11.26
             #17130f / #efe9e2 body ink ............... 16.56 / 15.75
             #56504a / #a89f95 muted ink ............... 7.12 / 7.28
             #575049 dim ink on the print's paper #fbf8f4 ........... 7.49
           NEVER text-gray-500 here: #6b7280 is 4.83 on pure white but only
           ~4.4 on this page's tinted ground. Use .es-gal-muted.

           NO ARBITRARY-VALUE TAILWIND for anything design critical: the
           build is not run during this campaign, so a class that is not
           already in public/build/assets/marketing-app-*.css silently does
           nothing. Every colour, size and material below is a real rule.

           BLADE RULE: no @supports() probe with a "#" hex in the condition,
           it breaks compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------- */
        .es-gal-page { background-color: #f5f2ee; color: #17130f; }
        .dark .es-gal-page { background-color: #12100e; color: #efe9e2; }
        .es-gal-ink { color: #17130f; }
        .dark .es-gal-ink { color: #efe9e2; }
        .es-gal-muted { color: #56504a; }
        .dark .es-gal-muted { color: #a89f95; }
        .es-gal-accent { color: #9a3d0c; }
        .dark .es-gal-accent { color: #fdba74; }
        .es-gal-mono {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-variant-numeric: tabular-nums;
        }
        .es-gal-rule { border-top: 1px solid rgba(23, 19, 15, 0.1); }
        .dark .es-gal-rule { border-top-color: rgba(239, 233, 226, 0.1); }
        .es-gal-xs { font-size: 0.66rem; line-height: 1.5; }

        /* --- Cards ---------------------------------------------------- */
        .es-gal-card {
            background-color: #ffffff;
            border: 1px solid rgba(23, 19, 15, 0.12);
            border-radius: 1rem;
        }
        .dark .es-gal-card { background-color: #1c1815; border-color: rgba(239, 233, 226, 0.12); }

        /* --- Section mark: a small empty frame, then the label --------- */
        .es-gal-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #56504a;
        }
        .es-gal-mark::before {
            content: "";
            flex: none;
            width: 1.05rem; height: 0.8rem;
            border: 2px solid #9a3d0c;
            border-radius: 2px;
        }
        .dark .es-gal-mark { color: #a89f95; }
        .dark .es-gal-mark::before { border-color: #fdba74; }
        .es-gal-band .es-gal-mark { color: #a89f95; }
        .es-gal-band .es-gal-mark::before { border-color: #fdba74; }

        /* ==============================================================
           THE PRINT. The generated PNG, drawn in CSS. Fixed in both
           colour modes on purpose: it is one file.
           ============================================================== */
        .es-gal-print {
            background-color: #fbf8f4;
            border: 1px solid rgba(23, 19, 15, 0.16);
            border-radius: 0.5rem;
            box-shadow: 0 30px 64px -30px rgba(26, 14, 4, 0.55);
        }
        /* The same print, hung small: the finale. */
        .es-gal-print-sm { width: 13rem; max-width: 100%; }
        /* The header image: a banner across the full width of the graphic. */
        .es-gal-print-band {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 2.5rem;
            border-radius: 0.25rem;
            background-image: linear-gradient(105deg, #2a1c12 0%, #55301a 55%, #7a3b16 100%);
        }
        .es-gal-print-wordmark {
            color: #ffffff;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.3em;
            text-transform: uppercase;
        }
        .es-gal-print-title {
            text-align: center;
            color: #17130f;
            font-size: 0.86rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .es-gal-print-rule { border-top: 1px solid rgba(23, 19, 15, 0.12); }
        .es-gal-print-foot {
            text-align: center;
            color: #575049;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.08em;
        }
        /* The hosted watermark, bottom right, exactly where the generator
           puts it. Real ink rather than a translucent gray: 5.64 on paper. */
        .es-gal-stamp {
            text-align: right;
            color: #6b625a;
            font-size: 0.5rem;
            letter-spacing: 0.06em;
        }

        /* The wall: flyers cropped to one common tile, 400 by 480 in the
           grid arrangement, so the hang is even. */
        .es-gal-wall {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.4rem;
        }
        .es-gal-piece {
            position: relative;
            aspect-ratio: 400 / 480;
            border-radius: 0.22rem;
            overflow: hidden;
            background-color: #2a211a;
            background-image: var(--pf, linear-gradient(150deg, #3a1f14, #8f3a0d));
        }
        /* The optional date strip. GridDesign::addDateOverlay and
           RowDesign::addDateOverlay draw it as a band across the TOP of the
           flyer (a ~60% black fill with centred white bold text), so it is
           drawn at the top here too. */
        .es-gal-piece-date {
            position: absolute;
            left: 0; right: 0; top: 0;
            padding: 0.18rem 0.3rem;
            background-color: rgba(18, 13, 9, 0.72);
            color: #ffffff;
            font-size: 0.44rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-align: center;
        }
        /* The numbered badge is drawn AFTER the date band in the generator, so
           it sits on top of it here as well. */
        .es-gal-badge {
            position: absolute;
            z-index: 2;
            top: 0.22rem;
            right: 0.22rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 0.9rem; height: 0.9rem;
            border-radius: 9999px;
            background-color: #a8410b;
            color: #ffffff;
            font-size: 0.45rem;
            font-weight: 800;
        }
        /* A QR code sits in the bottom left of every flyer and opens that
           event's page. Drawn as a texture, not an illustration. */
        /* Sized as a share of the tile, not in rem: the generator draws a 70px
           code inset 10px on a 400px flyer, so it stays 17.5% wide however
           large the print is drawn. */
        .es-gal-qr {
            position: absolute;
            bottom: 2.5%;
            left: 2.5%;
            width: 17.5%;
            aspect-ratio: 1;
            border-radius: 2px;
            background-color: #fdfcfa;
        }
        .es-gal-qr::after {
            content: "";
            position: absolute;
            inset: 2px;
            background-color: #fdfcfa;
            background-image: repeating-conic-gradient(#17130f 0% 25%, #fdfcfa 0% 50%);
            background-size: 3px 3px;
        }
        /* Staged hang: pieces settle into place when the frame reveals.
           The transition lives on the always-active rule and only the
           undrawn pre-state is gated, so no-JS and reduced-motion
           visitors see a finished wall. */
        .es-gal-hang {
            transition: opacity 0.6s cubic-bezier(0.22, 1, 0.36, 1), transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: calc(var(--i, 0) * 0.09s + 0.2s);
        }
        html.es-anim [data-reveal]:not(.is-revealed) .es-gal-hang {
            opacity: 0;
            transform: translateY(10px) scale(0.96);
        }
        .es-gal-frame-cap {
            margin-top: 0.75rem;
            text-align: center;
            color: #56504a;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.06em;
        }
        .dark .es-gal-frame-cap { color: #a89f95; }

        /* ==============================================================
           THE CAPTION SURFACE. Always dark in both colour modes, like the
           code blocks in the user guide.
           ============================================================== */
        .es-gal-code {
            background-color: #100e0c;
            border: 1px solid rgba(239, 233, 226, 0.14);
            border-radius: 0.6rem;
            color: #e7e1da;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.76rem;
            line-height: 1.95;
            overflow-x: auto;
        }
        .es-gal-code-b { color: #ffffff; font-weight: 700; }
        .es-gal-code-url { color: #fdba74; }
        .es-gal-code-dim { color: #a89f95; }
        .es-gal-code-tok { color: #fdba74; }
        .es-gal-code-head {
            color: #a89f95;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }

        /* ==============================================================
           THE ROOM AT NIGHT. Fixed-dark band.
           ============================================================== */
        .es-gal-band {
            background-color: #16120f;
            background-image: radial-gradient(120% 100% at 50% 0%, #241b14 0%, #16120f 55%, #0b0908 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(239, 233, 226, 0.05);
        }
        .es-gal-bright { color: #efe9e2; }
        .es-gal-dim { color: #a89f95; }
        .es-gal-lit { color: #fdba74; }
        .es-gal-band .es-gal-card { background-color: #1c1815; border-color: rgba(239, 233, 226, 0.12); }
        .es-gal-band .es-gal-btn {
            background-color: #fdba74;
            color: #1a120b;
            box-shadow: 0 18px 36px -14px rgba(168, 65, 11, 0.55);
        }
        .es-gal-band .es-gal-btn:hover { background-color: #ffd0a0; box-shadow: 0 22px 44px -14px rgba(168, 65, 11, 0.62); }
        .es-gal-band .grid-overlay {
            background-image:
                linear-gradient(rgba(239, 233, 226, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(239, 233, 226, 0.05) 1px, transparent 1px);
        }
        .es-gal-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-gal-band .es-claim:focus-within {
            border-color: rgba(253, 186, 116, 0.75);
            box-shadow: 0 0 0 4px rgba(253, 186, 116, 0.22);
        }
        /* Gallery light. Authored here rather than with .es-aurora, which
           flips its opacity between colour modes. */
        .es-gal-glow {
            position: absolute;
            border-radius: 9999px;
            filter: blur(90px);
            pointer-events: none;
        }
        .es-gal-drift { animation: es-gal-drift 30s ease-in-out infinite alternate; }
        @keyframes es-gal-drift {
            from { transform: translate3d(0, 0, 0) scale(1); }
            to   { transform: translate3d(50px, 40px, 0) scale(1.14); }
        }

        /* --- Chips ---------------------------------------------------- */
        .es-gal-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.3rem 0.8rem;
            border-radius: 9999px;
            border: 1px solid rgba(23, 19, 15, 0.16);
            background: rgba(255, 255, 255, 0.75);
            color: #56504a;
            font-size: 0.74rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-gal-chip {
            border-color: rgba(239, 233, 226, 0.16);
            background: rgba(239, 233, 226, 0.05);
            color: #b6ada2;
        }

        /* --- Plan pills ----------------------------------------------- */
        .es-gal-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.12rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(154, 61, 12, 0.45);
            color: #9a3d0c;
        }
        .dark .es-gal-plan { border-color: rgba(253, 186, 116, 0.45); color: #fdba74; }
        .es-gal-band .es-gal-plan { border-color: rgba(253, 186, 116, 0.45); color: #fdba74; }
        .es-gal-plan-enterprise { border-color: rgba(23, 19, 15, 0.35); color: #17130f; }
        .dark .es-gal-plan-enterprise { border-color: rgba(239, 233, 226, 0.38); color: #efe9e2; }

        /* --- The hanging ledger: pills that answer image / text ------- */
        .es-gal-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            border: 1px solid transparent;
        }
        .es-gal-pill-yes { background-color: rgba(154, 61, 12, 0.12); border-color: rgba(154, 61, 12, 0.35); color: #8a370b; }
        .dark .es-gal-pill-yes { background-color: rgba(253, 186, 116, 0.14); border-color: rgba(253, 186, 116, 0.35); color: #fdba74; }
        .es-gal-pill-no { border-color: rgba(23, 19, 15, 0.25); border-style: dashed; color: #56504a; }
        .dark .es-gal-pill-no { border-color: rgba(239, 233, 226, 0.28); color: #a89f95; }

        /* --- Tables --------------------------------------------------- */
        .es-gal-table { width: 100%; border-collapse: collapse; text-align: left; }
        .es-gal-table th,
        .es-gal-table td {
            padding: 0.7rem 0.7rem;
            vertical-align: top;
            font-size: 0.85rem;
            border-top: 1px solid rgba(23, 19, 15, 0.1);
        }
        .dark .es-gal-table th,
        .dark .es-gal-table td { border-color: rgba(239, 233, 226, 0.1); }
        .es-gal-table thead th {
            border-top: 0;
            padding-top: 0;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #56504a;
        }
        .dark .es-gal-table thead th { color: #a89f95; }
        .es-gal-scroll { overflow-x: auto; }

        /* --- The mounts: real proportions, so the shapes are the fact -- */
        /* max-content plus auto margins: the shelf centres itself when it fits
           and scrolls from its left edge when it does not (unlike
           justify-content: center, which would clip the first mount). */
        .es-gal-mounts {
            display: flex;
            align-items: flex-end;
            gap: 1.4rem;
            width: max-content;
            margin-left: auto;
            margin-right: auto;
            padding-bottom: 0.4rem;
        }
        .es-gal-mount { flex: none; }
        /* Width comes from the format's real pixel width at one shared scale and
           the height from its aspect ratio, so the five mounts stand on a common
           shelf at their true relative sizes. */
        .es-gal-mount-box {
            border-radius: 0.3rem;
            border: 1px solid rgba(23, 19, 15, 0.28);
            background-color: rgba(23, 19, 15, 0.05);
            background-image: linear-gradient(150deg, rgba(154, 61, 12, 0.32), rgba(154, 61, 12, 0.08));
        }
        .dark .es-gal-mount-box {
            border-color: rgba(239, 233, 226, 0.26);
            background-color: rgba(239, 233, 226, 0.05);
            background-image: linear-gradient(150deg, rgba(253, 186, 116, 0.3), rgba(253, 186, 116, 0.06));
        }
        .es-gal-mount-auto {
            border-style: dashed;
            background-image: none;
        }
        .es-gal-mount-name {
            margin-top: 0.5rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: #17130f;
        }
        .dark .es-gal-mount-name { color: #efe9e2; }
        .es-gal-mount-dim {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.68rem;
            color: #56504a;
        }
        .dark .es-gal-mount-dim { color: #a89f95; }

        /* --- Arrangement diagrams ------------------------------------- */
        .es-gal-diag {
            border-radius: 0.5rem;
            border: 1px solid rgba(23, 19, 15, 0.12);
            background-color: rgba(23, 19, 15, 0.04);
            padding: 0.6rem;
            /* One height for all three, so the three cards read as three
               versions of the same wall rather than three different objects. */
            min-height: 10.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .dark .es-gal-diag { border-color: rgba(239, 233, 226, 0.12); background-color: rgba(239, 233, 226, 0.04); }
        .es-gal-diag-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.3rem;
            width: 100%;
            max-width: 11.5rem;
            margin-left: auto;
            margin-right: auto;
        }
        .es-gal-diag-row { display: flex; align-items: stretch; gap: 0.3rem; height: 3.4rem; }
        .es-gal-diag-list { display: flex; flex-direction: column; }
        .es-gal-tile {
            border-radius: 0.15rem;
            background-image: linear-gradient(150deg, #3a1f14, #a8410b);
        }
        .es-gal-diag-grid .es-gal-tile { aspect-ratio: 400 / 480; }
        .es-gal-diag-row .es-gal-tile { height: 100%; }
        .es-gal-diag-item {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0;
            border-top: 1px solid rgba(23, 19, 15, 0.12);
        }
        .dark .es-gal-diag-item { border-top-color: rgba(239, 233, 226, 0.12); }
        .es-gal-diag-item:first-child { border-top: 0; }
        .es-gal-diag-thumb {
            flex: none;
            width: 2.6rem;
            aspect-ratio: 200 / 135;
            border-radius: 0.15rem;
            background-image: linear-gradient(150deg, #3a1f14, #a8410b);
        }
        .es-gal-diag-bar { height: 0.3rem; border-radius: 9999px; background-color: rgba(23, 19, 15, 0.22); }
        .dark .es-gal-diag-bar { background-color: rgba(239, 233, 226, 0.24); }

        /* --- Links and buttons ---------------------------------------- */
        .es-gal-link { color: #9a3d0c; }
        .es-gal-link:hover { color: #17130f; }
        .dark .es-gal-link { color: #fdba74; }
        .dark .es-gal-link:hover { color: #efe9e2; }

        .es-gal-btn {
            background-color: #a8410b;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(168, 65, 11, 0.5);
        }
        .es-gal-btn:hover { background-color: #8a370b; box-shadow: 0 22px 44px -14px rgba(168, 65, 11, 0.6); }
        .dark .es-gal-btn { background-color: #fdba74; color: #1a120b; }
        .dark .es-gal-btn:hover { background-color: #ffd0a0; }

        /* --- Card hover ----------------------------------------------- */
        .es-gal-hover:hover { border-color: rgba(154, 61, 12, 0.45); }
        .dark .es-gal-hover:hover { border-color: rgba(253, 186, 116, 0.45); }
        .es-gal-hover:hover .es-gal-hover-title,
        .es-gal-hover:hover .es-gal-hover-arrow { color: #9a3d0c; }
        .dark .es-gal-hover:hover .es-gal-hover-title,
        .dark .es-gal-hover:hover .es-gal-hover-arrow { color: #fdba74; }

        /* --- Shared-system recolours (brand blue by default) ---------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(154, 61, 12, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(253, 186, 116, 0.12), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(154, 61, 12, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(253, 186, 116, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #9a3d0c; }
        .dark .es-dot.is-active .es-dot-pip { background: #fdba74; }
        /* Dot-nav tooltip, as real rules in both modes. */
        .es-gal-tip { background-color: #ffffff; border-color: rgba(23, 19, 15, 0.12); color: #3b3630; }
        .dark .es-gal-tip { background-color: #1c1815; border-color: rgba(239, 233, 226, 0.14); color: #d6cfc7; }

        /* --- Focus rings. No border-radius here: setting it would change
               the element's own shape on focus. ---------------------- */
        #es-gal-page a:focus-visible,
        #es-gal-page summary:focus-visible,
        #es-gal-page button:focus-visible {
            outline: 2px solid #9a3d0c;
            outline-offset: 3px;
        }
        .dark #es-gal-page a:focus-visible,
        .dark #es-gal-page summary:focus-visible,
        .dark #es-gal-page button:focus-visible { outline-color: #fdba74; }
        .es-gal-band a:focus-visible,
        .es-gal-band summary:focus-visible,
        .es-gal-band button:focus-visible { outline-color: #fdba74 !important; }

        @media (prefers-reduced-motion: reduce) {
            .es-gal-drift { animation: none !important; }
            .es-gal-hang { transition: none !important; opacity: 1 !important; transform: none !important; }
        }
    </style>


    <div id="es-gal-page" class="es-gal-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the print, hung                                     -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-gal-glow es-gal-drift" style="width: 640px; height: 640px; left: -160px; top: -140px; background: radial-gradient(circle at 35% 35%, rgba(168, 65, 11, 0.24), rgba(168, 65, 11, 0) 65%);"></div>
            <div class="es-gal-glow es-gal-drift" style="width: 540px; height: 540px; right: -140px; top: 6%; background: radial-gradient(circle at 65% 40%, rgba(122, 59, 22, 0.2), rgba(122, 59, 22, 0) 65%); animation-delay: -12s;"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-gal-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="es-gal-muted text-sm font-medium tracking-wide">Event graphics &middot; Pro plan</span>
                    </div>

                    <h1 class="es-balance es-gal-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">You already have</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">the <span class="es-gal-accent">artwork.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-gal-muted mb-8 max-w-xl text-lg sm:text-xl">
                        Event Schedule hangs the flyers already sitting on your upcoming events into one image, and writes the caption to go with it. There is no editor to open, no template to fill in and no canvas to drag things around on. You decide how the work is hung, and press generate.
                    </p>

                    <div class="es-fade-up es-d-3 mb-9 flex flex-wrap gap-2">
                        <span class="es-gal-chip">One PNG</span>
                        <span class="es-gal-chip">One caption</span>
                        <span class="es-gal-chip">No design work</span>
                    </div>

                    <div class="es-fade-up es-d-4 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#hang" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See what goes up
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-gal-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Start for free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The print: the file this feature produces. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-gal-print p-4 sm:p-5">
                        <div class="es-gal-print-band mb-3">
                            <span class="es-gal-print-wordmark">Casa Azul Jazz Club</span>
                        </div>
                        <p class="es-gal-print-title mb-3">Spring lineup &middot; May 2026</p>
                        <div class="es-gal-wall">
                            @foreach ($pieces as $pi => [$field, $when])
                                <div class="es-gal-piece es-gal-hang" style="--pf: {{ $field }}; --i: {{ $pi }};">
                                    <span class="es-gal-badge" aria-hidden="true">{{ $pi + 1 }}</span>
                                    <span class="es-gal-qr" aria-hidden="true"></span>
                                    <span class="es-gal-piece-date">{{ $when }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="es-gal-print-rule mt-3 pt-2.5">
                            <p class="es-gal-print-foot">casaazul.eventschedule.com</p>
                            <p class="es-gal-stamp">eventschedule.com</p>
                        </div>
                    </div>
                    <p class="es-gal-frame-cap">One PNG at its native size: your banner, your wording, the date strip and the numbering switched on.</p>
                </div>
            </div>

            <!-- Where it goes. These are places you paste a file, not integrations. -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Instagram', 'WhatsApp', 'Telegram', 'Facebook', 'Newsletter', 'Group chat', 'Printed on the door', 'Your website'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-gal-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
                <p class="es-gal-muted mt-4 text-center text-sm">A file and a block of text go anywhere. Nothing here is an account connection.</p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. What you get: the image and the plate (fixed-dark band)    -->
    <!-- ============================================================ -->
    <section id="what" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-gal-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-gal-glow" style="width: 620px; height: 620px; left: 12%; top: -180px; background: radial-gradient(circle at 50% 50%, rgba(253, 186, 116, 0.14), rgba(253, 186, 116, 0) 65%);"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <p class="es-gal-mark mb-5" data-reveal>Two objects</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A picture and <span class="es-gal-lit">a plate.</span>
                    </h2>
                    <p class="es-gal-dim mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Both are generated in the same pass, from the same events, and both are yours to take away.
                    </p>
                </div>

                <div class="grid items-start gap-6 lg:grid-cols-2" data-reveal-group="110">
                    <!-- The picture -->
                    <div class="es-gal-card p-6 sm:p-7" data-reveal="panel">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <h3 class="es-gal-bright text-lg font-bold">The picture</h3>
                            <span class="es-gal-plan">Pro</span>
                        </div>
                        {{-- Nothing switched on: three flyers, their QR codes and, on
                             the hosted service, the credit. This is the graphic before
                             you touch a single setting. --}}
                        <div class="es-gal-print p-3">
                            <div class="es-gal-wall">
                                @foreach (array_slice($pieces, 0, 3) as $pi => [$field])
                                    <div class="es-gal-piece es-gal-hang" style="--pf: {{ $field }}; --i: {{ $pi }};">
                                        <span class="es-gal-qr" aria-hidden="true"></span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="es-gal-print-rule mt-2.5 pt-2">
                                <p class="es-gal-stamp">eventschedule.com</p>
                            </div>
                        </div>
                        <p class="es-gal-dim mt-4 text-sm">
                            One PNG, composed on the server from the flyer images on your events. Nothing is written over the artwork unless you ask: this is the whole graphic with every setting left alone. Your schedule's own background comes with it, a QR code in the bottom corner of each flyer opens that event's page, and on the hosted service a small eventschedule.com credit sits in the corner.
                        </p>
                    </div>

                    <!-- The plate -->
                    <div class="es-gal-card p-6 sm:p-7" data-reveal="panel">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <h3 class="es-gal-bright text-lg font-bold">The plate</h3>
                            <span class="es-gal-plan">Pro</span>
                        </div>
                        <div class="es-gal-code p-4">
                            <p class="es-gal-code-head mb-2">Generated text</p>
                            <div><span class="es-gal-code-b">*Saturday* 16/5 | 20:00</span></div>
                            <div><span class="es-gal-code-b">*Terra Nova Trio*:</span></div>
                            <div>Casa Azul Jazz Club | Lisbon</div>
                            <div><span class="es-gal-code-url">casaazul.eventschedule.com/terra-nova-trio</span></div>
                            <div class="es-gal-code-dim">&hellip; then the same four lines for every other event</div>
                        </div>
                        <p class="es-gal-dim mt-4 text-sm">
                            One entry per event, from a template you control. Asterisks are bold in WhatsApp and Telegram. If your schedule accepts event submissions, a short "want to see your event here?" line and its link are added at the end.
                        </p>
                    </div>
                </div>

                <p class="es-gal-dim mx-auto mt-10 max-w-2xl text-center" data-reveal>
                    Nothing is stored as a finished poster, so there is no stale copy to remember to redo. Add an event, generate again, and the wall is current.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. What goes up: the hanging ledger                          -->
    <!-- ============================================================ -->
    <section id="hang" class="es-gal-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <p class="es-gal-mark mb-5" data-reveal>The hang</p>
                <h2 class="es-balance es-gal-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    A curator decides <span class="es-gal-accent">what goes on the wall.</span>
                </h2>
                <p class="es-gal-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    You do it with settings you already have, not by picking events one at a time. The picture needs artwork; the plate does not.
                </p>
            </div>

            <div class="es-gal-card p-6 sm:p-8" data-reveal="panel">
                <div class="es-gal-scroll">
                    <table class="es-gal-table">
                        <caption class="sr-only">Which events appear on the generated image and in the generated text</caption>
                        <thead>
                            <tr>
                                <th scope="col">The event</th>
                                <th scope="col">On the picture</th>
                                <th scope="col">In the plate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ledger as [$rowLabel, $imgKind, $imgText, $txtKind, $txtText])
                                <tr>
                                    <th scope="row" class="es-gal-ink font-semibold">{{ $rowLabel }}</th>
                                    <td>
                                        @if ($imgKind === 'yes')
                                            <span class="es-gal-pill es-gal-pill-yes">{{ $imgText }}</span>
                                        @else
                                            <span class="es-gal-pill es-gal-pill-no">{{ $imgText }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($txtKind === 'yes')
                                            <span class="es-gal-pill es-gal-pill-yes">{{ $txtText }}</span>
                                        @else
                                            <span class="es-gal-pill es-gal-pill-no">{{ $txtText }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <th scope="row" class="es-gal-ink font-semibold">How many</th>
                                <td class="es-gal-muted">Up to 20, earliest first. Set any number from 1 to 20.</td>
                                <td class="es-gal-muted">The same, or every upcoming event with Show all events on.</td>
                            </tr>
                            <tr>
                                <th scope="row" class="es-gal-ink font-semibold">How many from one act or room</th>
                                <td class="es-gal-muted">Uncapped, or set 1 to 10 and the freed slots go to other talents and venues, so the poster can be shorter if there are not many.</td>
                                <td class="es-gal-muted">The same cap, so the caption matches the picture.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="es-gal-muted mt-5 text-sm">
                    Turn numbering on and the badges 1, 2, 3 are drawn on the flyers while the text is built from exactly that same list, so put <span class="es-gal-mono es-gal-accent">{number}</span> in your template and caption three belongs to badge three.
                </p>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-gal-card p-6" data-reveal="panel">
                    <h3 class="es-gal-ink mb-2 text-lg font-bold">No flyer, no tile</h3>
                    <p class="es-gal-muted text-sm">An event with no flyer image of its own is left off the picture rather than filled in with a placeholder. It still appears in the text, so nothing is lost.</p>
                </div>
                <div class="es-gal-card p-6" data-reveal="panel">
                    <h3 class="es-gal-ink mb-2 text-lg font-bold">Nothing unannounced</h3>
                    <p class="es-gal-muted text-sm">Drafts, private and unlisted events, anything behind a password, and cancelled events are all skipped. A graphic can only ever show what a visitor could already see.</p>
                </div>
                <div class="es-gal-card p-6" data-reveal="panel">
                    <h3 class="es-gal-ink mb-2 text-lg font-bold">One time zone</h3>
                    <p class="es-gal-muted text-sm">Dates are rendered in the schedule's own time zone, and the page warns you when an event on the wall is anchored to a different one.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Three hangs: grid, row, list                              -->
    <!-- ============================================================ -->
    <section id="arrange" class="es-gal-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <p class="es-gal-mark mb-5" data-reveal>Three hangs</p>
                <h2 class="es-balance es-gal-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Salon, single line, <span class="es-gal-accent">or programme.</span>
                </h2>
                <p class="es-gal-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Three arrangements, switched with one control. The same events, hung three ways.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                @foreach ($arrangements as [$aKind, $aName, $aBody])
                    <div class="es-gal-card flex flex-col p-6" data-reveal="panel">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <h3 class="es-gal-ink text-lg font-bold">{{ $aName }}</h3>
                            <span class="es-gal-mono es-gal-muted text-xs">{{ $aKind }}</span>
                        </div>

                        <div class="es-gal-diag mb-4" aria-hidden="true">
                            @if ($aKind === 'grid')
                                <div class="es-gal-diag-grid">
                                    @for ($gi = 0; $gi < 6; $gi++)
                                        <div class="es-gal-tile"></div>
                                    @endfor
                                </div>
                            @elseif ($aKind === 'row')
                                <div class="es-gal-diag-row">
                                    @foreach (['1 / 1', '3 / 4', '16 / 9', '2 / 3'] as $ratio)
                                        <div class="es-gal-tile" style="aspect-ratio: {{ $ratio }};"></div>
                                    @endforeach
                                </div>
                            @else
                                <div class="es-gal-diag-list">
                                    @for ($li = 0; $li < 3; $li++)
                                        <div class="es-gal-diag-item">
                                            <div class="es-gal-diag-thumb"></div>
                                            <div class="min-w-0 flex-1 space-y-1.5">
                                                <div class="es-gal-diag-bar" style="width: 72%;"></div>
                                                <div class="es-gal-diag-bar" style="width: 48%;"></div>
                                                <div class="es-gal-diag-bar" style="width: 60%;"></div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            @endif
                        </div>

                        <p class="es-gal-muted mt-auto text-sm">{{ $aBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-2" data-reveal-group="90">
                <div class="es-gal-card p-6" data-reveal="panel">
                    <h3 class="es-gal-ink mb-2 text-lg font-bold">The date: none, over the art, or above it</h3>
                    <p class="es-gal-muted text-sm">On the grid and the row the date strip is off to begin with, so the artwork arrives untouched. Switch it on and it is either a band across the top of each flyer, which keeps the wall tight, or its own strip above the flyer, which leaves the artwork uncovered.</p>
                </div>
                <div class="es-gal-card p-6" data-reveal="panel">
                    <h3 class="es-gal-ink mb-2 text-lg font-bold">Flyers per row</h3>
                    <p class="es-gal-muted text-sm">Left alone, the grid balances itself against the number of events. Set flyers per row and you get the shape you asked for, which is how a tall story image ends up two across and five down.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The mount: real proportions                               -->
    <!-- ============================================================ -->
    <section id="mount" class="es-gal-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <p class="es-gal-mark mb-5" data-reveal>The mount</p>
                <h2 class="es-balance es-gal-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Cut the mount, <span class="es-gal-accent">never the picture.</span>
                </h2>
                <p class="es-gal-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Pick a fixed shape and the finished graphic is scaled to fit inside it and centred, with the surrounding padding filled by your own background. It is a mount, not a crop: nothing is cut off and nothing is stretched.
                </p>
            </div>

            <div class="es-gal-card p-6 sm:p-8" data-reveal="panel">
                <div class="es-gal-scroll">
                    <div class="es-gal-mounts">
                        @foreach ($mounts as [$mName, $mDim, $mRatio, $mWidth, $mUse])
                            <div class="es-gal-mount">
                                <div class="es-gal-mount-box" style="aspect-ratio: {{ $mRatio }}; width: {{ $mWidth }};" aria-hidden="true"></div>
                                <p class="es-gal-mount-name">{{ $mName }}</p>
                                <p class="es-gal-mount-dim">{{ $mDim }}</p>
                                <p class="es-gal-muted es-gal-xs mt-0.5">{{ $mUse }}</p>
                            </div>
                        @endforeach
                        <div class="es-gal-mount">
                            <div class="es-gal-mount-box es-gal-mount-auto" style="aspect-ratio: 1080 / 1500; width: 5.4rem;" aria-hidden="true"></div>
                            <p class="es-gal-mount-name">Auto</p>
                            <p class="es-gal-mount-dim">Native</p>
                            <p class="es-gal-muted es-gal-xs mt-0.5">As tall as the wall needs</p>
                        </div>
                    </div>
                </div>
                <p class="es-gal-muted mt-5 text-sm">The five shapes above are drawn at their true proportions and at one shared scale, so 1080 pixels across is the same width whether the post is a square or a story. Auto is dashed because it has no fixed shape at all: the canvas grows with the number of events and the arrangement you chose.</p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The plate: the text template                              -->
    <!-- ============================================================ -->
    <section id="plate" class="es-gal-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <p class="es-gal-mark mb-5" data-reveal>The plate</p>
                <h2 class="es-balance es-gal-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The wording is <span class="es-gal-accent">a template, once.</span>
                </h2>
                <p class="es-gal-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Write the shape of one entry and every event is written the same way, in your schedule's language, forever.
                </p>
            </div>

            <div class="grid items-start gap-6 lg:grid-cols-2">
                <div class="es-gal-card p-6 sm:p-7" data-reveal="panel">
                    <h3 class="es-gal-ink mb-4 text-lg font-bold">Leave it blank and this is the plate</h3>
                    <div class="es-gal-code p-4">
                        <p class="es-gal-code-head mb-2">Default template</p>
                        <div><span class="es-gal-code-b">*<span class="es-gal-code-tok">{day_name}</span>*</span> <span class="es-gal-code-tok">{date_dmy}</span> | <span class="es-gal-code-tok">{time}</span></div>
                        <div><span class="es-gal-code-b">*<span class="es-gal-code-tok">{event_name}</span>*</span>:</div>
                        <div><span class="es-gal-code-tok">{venue}</span> | <span class="es-gal-code-tok">{city}</span></div>
                        <div><span class="es-gal-code-tok">{url}</span></div>
                    </div>
                    <div class="es-gal-code mt-4 p-4">
                        <p class="es-gal-code-head mb-2">What it prints</p>
                        <div><span class="es-gal-code-b">*Saturday* 16/5 | 20:00</span></div>
                        <div><span class="es-gal-code-b">*Terra Nova Trio*:</span></div>
                        <div>Casa Azul Jazz Club | Lisbon</div>
                        <div><span class="es-gal-code-url">casaazul.eventschedule.com/terra-nova-trio</span></div>
                    </div>
                    <p class="es-gal-muted mt-4 text-sm">
                        A variable with nothing behind it does not leave a stray separator: the line is tidied, and a line whose variables are all empty is dropped entirely.
                    </p>
                </div>

                <div class="es-gal-card p-6 sm:p-7" data-reveal="panel">
                    <h3 class="es-gal-ink mb-4 text-lg font-bold">The variables you will reach for</h3>
                    <div class="es-gal-scroll">
                        <table class="es-gal-table">
                            <caption class="sr-only">A subset of the variables available in the event graphics text template</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Variable</th>
                                    <th scope="col">Prints</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vars as [$vName, $vDesc])
                                    <tr>
                                        <th scope="row" class="es-gal-mono es-gal-accent whitespace-nowrap font-semibold">{{ $vName }}</th>
                                        <td class="es-gal-muted">{{ $vDesc }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="es-gal-muted mt-4 text-sm">
                        There are more: the other date formats, the end time and duration, the full address, currency and coupon code, the long description, and
                        <span class="es-gal-mono">{custom_1}</span> to <span class="es-gal-mono">{custom_10}</span> for your own custom fields.
                        <a href="{{ route('marketing.docs.event_graphics') }}#variables" class="es-gal-link font-semibold hover:underline">The full list is in the guide</a>.
                    </p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-gal-card p-6" data-reveal="panel">
                    <h3 class="es-gal-ink mb-2 text-lg font-bold">Links, your way</h3>
                    <p class="es-gal-muted text-sm">Keep or drop the <span class="es-gal-mono">https://</span> prefix, and keep or drop the event id so the link is a clean slug. Two checkboxes, applied to every entry.</p>
                </div>
                <div class="es-gal-card p-6" data-reveal="panel">
                    <h3 class="es-gal-ink mb-2 text-lg font-bold">Right to left, properly</h3>
                    <p class="es-gal-muted text-sm">A Hebrew or Arabic schedule gets its text marked so the whole message reads right to left when pasted, while a line that is only a link is left untouched so it stays tappable.</p>
                </div>
                <div class="es-gal-card p-6" data-reveal="panel">
                    <h3 class="es-gal-ink mb-2 text-lg font-bold">An English export</h3>
                    <p class="es-gal-muted text-sm">If your schedule is in another language and translates to English, one toggle produces the text and the dates on the graphic in English, using English event and venue names where they exist.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. The signature: bento                                      -->
    <!-- ============================================================ -->
    <section id="extras" class="es-gal-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-gal-mark mb-5" data-reveal>The signature</p>
                <h2 class="es-balance es-gal-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whose wall it is.
                </h2>
                <p class="es-gal-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Every one of these is set once, on the schedule, and every graphic from then on carries it. None of it is work you redo per post.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-gal-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-gal-ink text-xl font-bold">Your banner across the top</h3>
                                <span class="es-gal-plan">Pro</span>
                            </div>
                            <p class="es-gal-muted mb-4">Upload a header image and it runs the full width of every graphic, above the wall, in a band up to 200 pixels tall. A JPG, PNG, GIF or WebP; one upload, and every graphic from then on carries it.</p>
                            <p class="es-gal-muted text-sm">Leave it off and the graphic starts straight in on the events, over your schedule's own background.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-gal-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-gal-ink text-xl font-bold">A headline and a sign-off</h3>
                                <span class="es-gal-plan">Pro</span>
                            </div>
                            <p class="es-gal-muted">Header text is bold and shrinks itself to fit the width. Footer text is quieter and can run to two lines. Both take schedule variables, so <span class="es-gal-mono es-gal-accent">{month_name} {year}</span> or <span class="es-gal-mono es-gal-accent">{first_event_date}</span> writes itself every time.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-gal-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-gal-ink text-xl font-bold">What the tile says</h3>
                                <span class="es-gal-plan">Pro</span>
                            </div>
                            <p class="es-gal-muted">Nothing is written on the artwork unless you ask for it. Switch the strip on and it carries the event's date; give it a short template of its own and it says whatever you need instead, using the same event variables as the plate.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-gal-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-gal-ink text-xl font-bold">A code in every corner</h3>
                                <span class="es-gal-plan">Pro</span>
                            </div>
                            <p class="es-gal-muted mb-4">Each flyer carries a QR code that opens that event's own page, so a phone pointed at the image lands on the right event rather than on your calendar in general. Print the graphic and it still works.</p>
                            <p class="es-gal-muted text-sm">Number the flyers as well and the wall becomes a numbered list that the caption underneath can refer to.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-gal-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-gal-ink text-xl font-bold">Rewrite the plate with AI</h3>
                                <span class="es-gal-plan es-gal-plan-enterprise">Enterprise</span>
                            </div>
                            <p class="es-gal-muted">Give an instruction once and it runs on the generated text: add a calendar emoji before each date, translate to Spanish, add hashtags for Instagram. The template still does the work; this is the pass after it.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-gal-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-gal-ink text-xl font-bold">Have it emailed out for you</h3>
                                <span class="es-gal-plan es-gal-plan-enterprise">Enterprise</span>
                            </div>
                            <p class="es-gal-muted mb-4">Daily, weekly or monthly, on the day and at the hour you choose, to a comma-separated list of addresses. Send yourself a test first and you will see exactly what they get.</p>
                            <p class="es-gal-muted text-sm">
                                This is a small recurring email of the graphic, not a campaign.
                                <a href="{{ marketing_url('/features/newsletters') }}" class="es-gal-link font-semibold hover:underline">Newsletters</a>
                                are the designed ones, with open and click rates, and they start free at 10 emails a month, counted per recipient.
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
    <!-- 8. Three steps                                               -->
    <!-- ============================================================ -->
    <section id="steps" class="es-gal-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-gal-mark mb-5" data-reveal>Three steps</p>
                <h2 class="es-balance es-gal-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Hung in a minute.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([['01', 'Give the events a flyer', 'The wall is built from the artwork already on your events, so upload a flyer to each one you want hung. Everything else you have entered is already enough.'], ['02', 'Choose the hang and the mount', 'Grid, row or list. Square, portrait, story, landscape or auto. How many events, the date over the art or above it, and your header and footer wording.'], ['03', 'Download and paste', 'Take the PNG, copy the text, and post them wherever you post. Generate again next week and the wall is current.']] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-gal-card p-7" data-reveal="panel">
                        <div class="es-gal-accent es-gal-mono mb-3 text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-gal-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-gal-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. What it costs                                              -->
    <!-- ============================================================ -->
    <section id="plans" class="es-gal-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <p class="es-gal-mark mb-5" data-reveal>What it costs</p>
                <h2 class="es-balance es-gal-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Free to have the artwork. <span class="es-gal-accent">Pro to hang it.</span>
                </h2>
                <p class="es-gal-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Pro is {{ plan_price($proMonthly) }} a month with a {{ $trialDays }}-day free trial. Nothing on this page is charged per graphic.
                </p>
            </div>

            <div class="es-gal-card p-6 sm:p-8" data-reveal="panel">
                <div class="es-gal-scroll">
                    <table class="es-gal-table">
                        <caption class="sr-only">Which plan each part of event graphics belongs to</caption>
                        <thead>
                            <tr>
                                <th scope="col">Part</th>
                                <th scope="col">Plan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ([
                                ['Upload a flyer image to an event', 'Free', 'plan'],
                                ['A public page and its own link for every event', 'Free', 'plan'],
                                ['Compose the graphic and its text', 'Pro', 'plan'],
                                ['Header image, header and footer text, tile text', 'Pro', 'plan'],
                                ['Square, portrait, story and landscape formats', 'Pro', 'plan'],
                                ['Custom fields in the template', 'Pro', 'plan'],
                                ['AI rewrite of the generated text', 'Enterprise', 'plan-enterprise'],
                                ['Scheduled graphic emails', 'Enterprise', 'plan-enterprise'],
                            ] as [$pLabel, $pPlan, $pClass])
                                <tr>
                                    <th scope="row" class="es-gal-ink font-semibold">{{ $pLabel }}</th>
                                    <td>
                                        @if ($pClass === 'plan-enterprise')
                                            <span class="es-gal-plan es-gal-plan-enterprise">{{ $pPlan }}</span>
                                        @else
                                            <span class="es-gal-plan">{{ $pPlan }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-gal-muted mt-5 text-sm">
                    Selfhosted installs skip the plans entirely, and the graphic carries no eventschedule.com credit there.
                    <a href="{{ marketing_url('/pricing') }}" class="es-gal-link font-semibold hover:underline">See all plans</a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Related features                                          -->
    <!-- ============================================================ -->
    <section class="es-gal-rule py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-gal-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card
                        name="AI Features"
                        description="Import, generate content, create brand style, and more with AI"
                        :url="marketing_url('/features/ai')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Newsletters"
                        description="Send branded newsletters to followers and ticket buyers"
                        :url="marketing_url('/features/newsletters')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Embed Calendar"
                        description="Embed your schedule on any website"
                        :url="marketing_url('/features/embed-calendar')"
                        icon-color="emerald"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>

            <h3 class="es-gal-ink mb-4 mt-12 text-center text-lg font-bold" data-reveal>Popular with</h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3" data-reveal-group="70">
                @foreach ([['/for-musicians', 'Musicians'], ['/for-venues', 'Venues'], ['/for-bars', 'Bars &amp; Pubs']] as [$whoHref, $whoName])
                    <a href="{{ marketing_url($whoHref) }}" class="es-gal-hover es-gal-card group flex items-center justify-between p-4 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-gal-hover-title es-gal-ink text-sm font-semibold transition-colors">For {!! $whoName !!}</span>
                        <svg aria-hidden="true" class="es-gal-hover-arrow es-gal-muted h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                @endforeach
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('marketing.docs.event_graphics') }}" class="es-gal-link inline-flex items-center font-medium hover:underline">
                    Read the Event Graphics guide
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. FAQ                                                       -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-gal-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <p class="es-gal-mark mb-5" data-reveal>Questions</p>
                <h2 class="es-balance es-gal-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-gal-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Everything you need to know about event graphics.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-gal-hover es-gal-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-gal-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-gal-accent es-gal-mono flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-gal-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-gal-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-gal-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. Finale: the opening                                       -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-gal-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-gal-glow" style="width: 620px; height: 620px; left: 50%; top: -220px; margin-left: -310px; background: radial-gradient(circle at 50% 50%, rgba(253, 186, 116, 0.16), rgba(253, 186, 116, 0) 65%);"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    {{-- The same print as the hero, hung small and centred: the finale
                         shows the object the page has been describing, and the wall in
                         it is the six events the headline counts. --}}
                    <div class="es-gal-print es-gal-print-sm mx-auto mb-8 p-2.5" aria-hidden="true">
                        <div class="es-gal-wall">
                            @foreach ($pieces as $pi => [$field])
                                <div class="es-gal-piece es-gal-hang" style="--pf: {{ $field }}; --i: {{ $pi }};">
                                    <span class="es-gal-qr" aria-hidden="true"></span>
                                </div>
                            @endforeach
                        </div>
                        <div class="es-gal-print-rule mt-2 pt-1.5">
                            <p class="es-gal-stamp">eventschedule.com</p>
                        </div>
                    </div>
                    <p class="es-gal-mark mb-5 justify-center">The opening</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your next six shows, <span class="es-gal-lit">in one image.</span>
                    </h2>
                    <p class="es-gal-dim mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        Publishing your schedule is free forever. Event graphics are {{ plan_price($proMonthly) }} a month with a {{ $trialDays }}-day free trial, and no design work either way.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-gal-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-gal-dim mt-6 text-sm">No credit card required to start.</p>
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
                        <span class="es-gal-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
