<x-marketing-layout>
    <x-slot name="title">White Label | Remove Branding - Event Schedule</x-slot>
    <x-slot name="description">Remove Event Schedule branding from your schedule for a fully white-labeled experience. Your brand, your schedule, no distractions.</x-slot>
    <x-slot name="breadcrumbTitle">White Label</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - White Label",
        "description": "Remove Event Schedule branding from your schedule for a fully white-labeled experience.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "featureList": [
            "Removes the Event Schedule strip from the foot of your public schedule",
            "Removes the Create your own event schedule card from your public event pages",
            "Embed snippets ship without a Powered by line",
            "The embedded ticket widget loses its Powered by footer",
            "Newsletter emails send without a Powered by footer",
            "Your own logo becomes the browser tab icon",
            "Public pages are never monetized above the free tier",
            "Nothing to configure: the check reads your plan",
            "Selfhosted installations are white-labeled by default, apart from a small corner credit the license asks for"
        ],
        "offers": {
            "@type": "Offer",
            "price": "5",
            "priceCurrency": "USD",
            "description": "Available on Pro plan"
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
           For white-label "The Blank Slate" styles.

           CONCEPT: an engraved slate plate. Everything on your public
           page is yours except one strip at the foot, which carries our
           name. White-label is not a decoration you add, it is a
           SUBTRACTION - the maker's mark comes off the plate and the
           space is left blank. The blank is the product, so the page
           renders the blank at full size rather than hiding it.

           THE DEVICE IS THE PLATE PAIR plus THE REGISTER. Two identical
           slate plates sit in the hero: the free one carries the strip,
           the white-labeled one carries an empty recess of exactly the
           same height. Then a real <table> audits every surface where
           our name can appear on something of yours, and what happens
           to each. That table is the argument: the feature is a finite,
           enumerable list, not a vibe.

           EVERY ROW IS CODE-CHECKED:
             - The foot strip: layouts/app-guest.blade.php gated on
               $role->showBranding(), text messages.try_event_schedule.
               On the nexus branch the same <p> also carries
               messages.supported_by (Invoice Ninja), so the strip holds
               TWO credits and the plate mock shows both.
             - The embed snippet's line: $embedBrandingLine in
               components/embed-modal.blade.php - baked into the HTML
               you paste, which is why the page tells you to re-copy it.
             - The ticket widget: it carries the line TWICE. Once
               server-rendered inside the frame
               (event/show-guest-ticket-embed.blade.php:78), which goes
               on its own, and once baked into the snippet you paste
               (components/embed-ticket-modal.blade.php:75), which does
               not. So BOTH embed snippets need re-copying; an earlier
               draft of this page claimed only the calendar one did.
             - The event-page card: event/show-guest.blade.php renders
               messages.create_your_own_event_schedule when
               $role->showBranding() - the same fact as the strip, so
               all seven rows now key off one tier. It used to read
               `! $event->isPro()`, which asked about every schedule on
               the bill; if you find a footnote here claiming this row
               is the odd one out, the footnote is stale.
             - The newsletter footer: NewsletterService passes
               showBranding into emails/newsletter.blade.php.
             - The tab icon: app-guest.blade.php uses profile_image_url
               as rel=icon when $role->isPro().
             - Monetization: Role::showAds() is false above free.
           WHAT STAYS, and the page says all of it. Head metadata on
           every install: the <title> in layouts/app is unconditionally
           "... | Event Schedule", og:site_name is hard-coded in
           app-guest.blade.php, and the BreadcrumbList JSON-LD names
           marketing_url() as the site root. Then the credit chip, whose
           three cases live in Role::creditChipReason(): an admin-granted
           Enterprise plan on the nexus ('granted_plan'), an operator's
           own free tier on a self-hosted SaaS ('saas_free'), and every
           schedule on a plain selfhost ('selfhost'), which carries it
           unconditionally as the Attribution Assurance License credit.
           Section 05 and the selfhost FAQ both have to say so - a page
           selling white-label that overclaims gets found out on day one.

           NOT USED HERE: a "before / after" toggle or a struck-through
           line. The removal is not an animation and there is no state
           to flip; both plates are shown at rest, side by side.

           NEAREST NEIGHBOUR, checked: /features/custom-domain owns
           "The Nameplate" (.es-plate), a small screwed brass plaque in
           a green family that holds one address line. This is a
           different object: a graphite slab, no fixings, that renders a
           whole schedule and whose only subject is the recess at its
           foot. Keep them apart - do not add screws, and do not let
           this plate shrink to a one-line plaque.

           COLOUR: the file's existing hue family was blue, so blue it
           stays - but pulled all the way down to graphite. Slate rock,
           #2c4a68 at 214deg / 41% / 29%: far too desaturated to read as
           the shared brand blue -> sky -> cyan chrome gradient, and
           nowhere near the saturated night navy /for-comedy-clubs took.
           The page is deliberately almost colourless, because that is
           what it is selling.

           Measured: #2c4a68 8.03 on the ground / 8.86 on a card; ink
           #14171a 15.74; muted #4c5257 6.93 on ground / 6.32 on sub.
           Dark: #a9c6dd 10.29 on ground / 9.31 on card; #eef1f3 16.13;
           #a3aab0 7.79 / 7.04 / 6.32. On the fixed slate plate:
           #dfe4e8 11.90, #b0b8bf 7.58, #a9c6dd 8.57. Never
           text-gray-500 - the tinted ground invalidates it.
           ============================================================== */

        /* --- Ground and ink --------------------------------------- */
        .es-slate2-page { background-color: #eef0f1; color: #14171a; }
        .dark .es-slate2-page { background-color: #131517; color: #eef1f3; }
        .es-slate2-ink { color: #14171a; }
        .dark .es-slate2-ink { color: #eef1f3; }
        .es-slate2-muted { color: #4c5257; }
        .dark .es-slate2-muted { color: #a3aab0; }
        .es-slate2-accent { color: #2c4a68; }
        .dark .es-slate2-accent { color: #a9c6dd; }
        /* Always-lit accent, for text sitting on the fixed slate. */
        .es-slate2-lit { color: #a9c6dd; }

        .es-slate2-grad {
            background-image: linear-gradient(100deg, #1f3a54, #3a6285);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-slate2-grad {
            background-image: linear-gradient(100deg, #b7d2e6, #8fb4d0);
        }

        /* --- Surfaces --------------------------------------------- */
        .es-slate2-card {
            background-color: #fafbfb;
            border: 1px solid rgba(20, 23, 26, 0.12);
            border-radius: 0.5rem;
        }
        .dark .es-slate2-card {
            background-color: #1c1f22;
            border-color: rgba(238, 241, 243, 0.13);
        }
        .es-slate2-sub {
            background-color: #e3e6e8;
            border-radius: 0.35rem;
        }
        .dark .es-slate2-sub { background-color: #24282b; }
        .es-slate2-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-slate2-hover:hover {
            border-color: rgba(44, 74, 104, 0.45);
            box-shadow: 0 12px 28px -20px rgba(20, 23, 26, 0.55);
        }
        .dark .es-slate2-hover:hover {
            border-color: rgba(169, 198, 221, 0.4);
            box-shadow: 0 12px 28px -20px rgba(0, 0, 0, 0.85);
        }
        /* Hairline section separator, used with border-t / border-y. */
        .es-slate2-rule { border-color: rgba(20, 23, 26, 0.1); }
        .dark .es-slate2-rule { border-color: rgba(238, 241, 243, 0.1); }

        /* --- Typography ------------------------------------------- */
        .es-slate2-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #2c4a68;
        }
        .dark .es-slate2-tag { color: #a9c6dd; }
        .es-slate2-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- The engraved section numeral -------------------------
           A small chip of slate with the numeral cut into it. Fixed:
           it is the same object in both colour modes. */
        .es-slate2-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 0.35rem;
            background-color: #22262a;
            background-image: linear-gradient(180deg, #292e33, #1b1f22);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.08),
                inset 0 0 0 1px rgba(0, 0, 0, 0.55),
                0 8px 16px -12px rgba(0, 0, 0, 0.7);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 700;
            color: #dfe4e8;
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.7);
        }

        /* --- Buttons ---------------------------------------------- */
        .es-slate2-btn {
            background-color: #2c4a68;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-slate2-btn:hover {
            background-color: #365a7d;
            transform: translateY(-1px);
            box-shadow: 0 16px 30px -18px rgba(20, 23, 26, 0.9);
        }
        .es-slate2-ghost {
            border: 1px solid rgba(20, 23, 26, 0.22);
            color: #14171a;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-slate2-ghost:hover { border-color: rgba(44, 74, 104, 0.55); background-color: rgba(44, 74, 104, 0.07); }
        .dark .es-slate2-ghost { border-color: rgba(238, 241, 243, 0.24); color: #eef1f3; }
        .dark .es-slate2-ghost:hover { border-color: rgba(169, 198, 221, 0.45); background-color: rgba(169, 198, 221, 0.09); }

        /* --- Plan tags. Tiers ONLY, never a state. ---------------- */
        .es-slate2-plan {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid transparent;
            padding: 0.1rem 0.5rem;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .es-slate2-plan-free { border-color: rgba(20, 23, 26, 0.24); color: #4c5257; }
        .dark .es-slate2-plan-free { border-color: rgba(238, 241, 243, 0.26); color: #a3aab0; }
        .es-slate2-plan-pro { border-color: rgba(44, 74, 104, 0.5); color: #2c4a68; background-color: rgba(44, 74, 104, 0.08); }
        .dark .es-slate2-plan-pro { border-color: rgba(169, 198, 221, 0.42); color: #a9c6dd; background-color: rgba(169, 198, 221, 0.1); }
        .es-slate2-plan-enterprise { border-color: rgba(20, 23, 26, 0.5); color: #14171a; background-color: rgba(20, 23, 26, 0.06); }
        .dark .es-slate2-plan-enterprise { border-color: rgba(238, 241, 243, 0.42); color: #eef1f3; background-color: rgba(238, 241, 243, 0.08); }

        /* --- Register outcome pills ------------------------------- */
        .es-slate2-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            border-radius: 999px;
            border: 1px solid transparent;
            padding: 0.15rem 0.55rem;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .es-slate2-pill-off { border-color: rgba(44, 74, 104, 0.5); color: #2c4a68; background-color: rgba(44, 74, 104, 0.08); }
        .dark .es-slate2-pill-off { border-color: rgba(169, 198, 221, 0.42); color: #a9c6dd; background-color: rgba(169, 198, 221, 0.1); }
        .es-slate2-pill-keep { border-color: rgba(20, 23, 26, 0.28); color: #4c5257; }
        .dark .es-slate2-pill-keep { border-color: rgba(238, 241, 243, 0.28); color: #a3aab0; }

        /* The register table needs a floor width to stay legible; the
           wrapper scrolls rather than the page body. */
        .es-slate2-reg { min-width: 40rem; }

        /* --- THE PLATE -------------------------------------------
           A piece of slate. The same physical object with .dark on and
           off, so nothing inside it may carry a colour-mode variant. */
        .es-slate2-plate {
            position: relative;
            overflow: hidden;
            border-radius: 0.55rem;
            background-color: #22262a;
            background-image: linear-gradient(180deg, #272c31 0%, #1f2327 58%, #191c20 100%);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.07),
                inset 0 0 0 1px rgba(0, 0, 0, 0.55),
                0 0 0 1px rgba(255, 255, 255, 0.07),
                0 22px 46px -26px rgba(20, 23, 26, 0.75);
        }
        /* Cleavage grain: slate splits along fine parallel planes. */
        .es-slate2-plate::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(103deg, rgba(255, 255, 255, 0.022) 0 1px, rgba(255, 255, 255, 0) 1px 7px);
            pointer-events: none;
        }
        .es-slate2-plate-cap {
            border-bottom: 1px solid rgba(0, 0, 0, 0.45);
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.05);
        }
        .es-slate2-row {
            border-top: 1px solid rgba(255, 255, 255, 0.055);
        }
        .es-slate2-engraved {
            color: #dfe4e8;
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.75), 0 -1px 0 rgba(255, 255, 255, 0.05);
        }
        .es-slate2-etch { color: #b0b8bf; text-shadow: 0 1px 0 rgba(0, 0, 0, 0.6); }

        /* The foot: a recess cut into the plate. On a free schedule it
           holds our strip; with white-label on it holds nothing, at the
           same height, which is the whole point of the page. */
        .es-slate2-foot {
            display: flex;
            align-items: center;
            min-height: 4.75rem;
            background-color: #1a1d20;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.6), inset 0 -1px 0 rgba(255, 255, 255, 0.04);
        }
        .es-slate2-blank { background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0)); }
        /* The same recess, standing on its own in the finale rather than cut
           into a plate, so it needs its own rounded edge and outer ring. */
        .es-slate2-recess {
            border-radius: 0.55rem;
            box-shadow:
                inset 0 2px 6px rgba(0, 0, 0, 0.7),
                inset 0 -1px 0 rgba(255, 255, 255, 0.04),
                0 0 0 1px rgba(255, 255, 255, 0.06);
        }
        .es-slate2-strip {
            width: 100%;
            background-color: #2c3136;
            color: #f5f9fe;
            text-align: center;
            font-size: 0.72rem;
        }
        /* The bullet between the two credits, and a wrapper that keeps the
           second credit whole when the strip wraps on a narrow plate. */
        .es-slate2-strip-sep { padding: 0 0.35rem; opacity: 0.6; }
        .es-slate2-strip-wrap { white-space: nowrap; }
        /* The date column in the plate rows. Set here rather than as an
           arbitrary Tailwind width, which is not in the built bundle. */
        .es-slate2-when { width: 5.5rem; }
        /* The event title in a plate row. `min-w-0 flex-1 truncate` is NOT
           enough: Chrome still counts the nowrap text into the flex row's
           min-content, which pushed the plate's own min-content to 347px and
           clipped its right edge inside the hero's overflow at 360px wide.
           A zero SPECIFIED width plus flex-grow gives a real 0 floor, so the
           ellipsis does its job and the plate edge stays intact. */
        .es-slate2-title {
            flex: 1 1 0%;
            width: 0;
            min-width: 0;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        /* The real credit chip, reproduced. The mark keeps its true
           brand gradient; it is a logo, not a page accent. */
        .es-slate2-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border-radius: 999px;
            background-color: #f2f4f6;
            padding: 0.35rem 0.7rem;
            font-size: 0.72rem;
            font-weight: 500;
            color: #4b5158;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25), inset 0 0 0 1px rgba(0, 0, 0, 0.07);
        }
        .es-slate2-chip-mark {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border-radius: 5px;
            background-image: linear-gradient(135deg, #4E81FA, #22D3EE);
        }

        /* Browser tab mock, for the one string that does not come off. */
        .es-slate2-tabbar {
            display: flex;
            align-items: flex-end;
            gap: 0.4rem;
            padding: 0.5rem 0.5rem 0;
            background-color: #171a1d;
            border-radius: 0.45rem 0.45rem 0 0;
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.5);
        }
        .es-slate2-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            max-width: 100%;
            border-radius: 0.4rem 0.4rem 0 0;
            background-color: #24282b;
            padding: 0.45rem 0.7rem;
            font-size: 0.72rem;
        }
        /* The tab icon: on Pro this is the schedule's own logo, so it
           stands in for the reader's mark, not ours. */
        .es-slate2-tabicon {
            display: inline-block;
            flex: 0 0 auto;
            width: 0.85rem;
            height: 0.85rem;
            border-radius: 0.2rem;
            background-color: #b45309;
        }

        /* Always-dark code specimen. */
        .es-slate2-code {
            background-color: #1a1d20;
            border-radius: 0.35rem;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.07);
            color: #dfe4e8;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }

        /* Accent swatches: the colour is the reader's, not the page's. */
        .es-slate2-swatch {
            display: inline-block;
            width: 1.35rem;
            height: 1.35rem;
            border-radius: 0.3rem;
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.25);
        }

        /* A slow polish sweeping across the slate. */
        .es-slate2-sheen {
            position: absolute;
            inset: -50% -15%;
            pointer-events: none;
            background-image: linear-gradient(100deg, rgba(255, 255, 255, 0) 42%, rgba(255, 255, 255, 0.055) 50%, rgba(255, 255, 255, 0) 58%);
            animation: es-slate2-sheen 11s ease-in-out infinite;
        }
        @keyframes es-slate2-sheen {
            0%, 100% { transform: translateX(-26%); opacity: 0; }
            40% { opacity: 0.9; }
            55% { transform: translateX(26%); opacity: 0; }
        }

        /* --- The fixed dark band --------------------------------- */
        .es-slate2-band {
            background-color: #14171a;
            background-image:
                radial-gradient(ellipse 75% 55% at 50% 0%, rgba(44, 74, 104, 0.4), rgba(44, 74, 104, 0) 70%),
                linear-gradient(180deg, #1b1f22, #14171a);
        }
        /* The band has no .dark variant, so anything inside it that HAS
           one would render differently on an identical ground. */
        .es-slate2-band .es-slate2-tag { color: #a9c6dd; }
        .es-slate2-band .es-slate2-grad { background-image: linear-gradient(100deg, #b7d2e6, #8fb4d0); }
        .es-slate2-band .es-slate2-muted { color: #a3aab0; }
        .es-slate2-band .es-slate2-card { background-color: #1c1f22; border-color: rgba(238, 241, 243, 0.13); }
        .es-slate2-band .es-slate2-pill-keep { border-color: rgba(238, 241, 243, 0.28); color: #a3aab0; }
        /* Shared classes that carry their own .dark rules in marketing.css
           and are invisible to a grep of this file. */
        .es-slate2-band .grid-overlay {
            background-image:
                linear-gradient(rgba(238, 241, 243, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(238, 241, 243, 0.05) 1px, transparent 1px);
        }
        .es-slate2-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-slate2-band .es-claim:focus-within {
            border-color: rgba(169, 198, 221, 0.75);
            box-shadow: 0 0 0 4px rgba(169, 198, 221, 0.22);
        }

        /* The dot-nav tooltip. Its ground and ink live here because a
           dark:bg-[#...] arbitrary value is not in the built bundle and
           would leave grey ink on a white pill in dark mode. */
        .es-slate2-tip {
            background-color: #ffffff;
            border: 1px solid rgba(20, 23, 26, 0.14);
            color: #4c5257;
        }
        .dark .es-slate2-tip {
            background-color: #1c1f22;
            border-color: rgba(238, 241, 243, 0.16);
            color: #a3aab0;
        }

        /* Shared dot nav is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(44, 74, 104, 0.65); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(169, 198, 221, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #2c4a68; }
        .dark .es-dot.is-active .es-dot-pip { background: #a9c6dd; }

        /* Focus rings. No border-radius here: an outline already follows
           the element's own radius. */
        #es-slate2-page a:focus-visible,
        #es-slate2-page summary:focus-visible,
        #es-slate2-page button:focus-visible,
        #es-slate2-page input:focus-visible {
            outline: 2px solid #2c4a68;
            outline-offset: 2px;
        }
        .dark #es-slate2-page a:focus-visible,
        .dark #es-slate2-page summary:focus-visible,
        .dark #es-slate2-page button:focus-visible,
        .dark #es-slate2-page input:focus-visible {
            outline-color: #a9c6dd;
        }
        .es-slate2-band a:focus-visible,
        .es-slate2-band summary:focus-visible,
        .es-slate2-band button:focus-visible,
        .es-slate2-band input:focus-visible {
            outline-color: #a9c6dd !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-slate2-sheen { animation: none !important; opacity: 0; }
            .es-slate2-btn:hover { transform: none; }
        }
    </style>

    @php
        // One mock schedule, used by both plates so the only difference
        // between them is the foot.
        $plateName = 'Northgate Hall';
        $plateRows = [
            ['FRI 12 SEP', 'Bell and Vine', '8:00pm'],
            ['SAT 13 SEP', 'The Long Room Quartet', '7:30pm'],
            ['THU 18 SEP', 'Late Session', '9:00pm'],
        ];

        // The plate pair. Full class strings live here rather than as
        // ternaries in the markup, so nothing is interpolated into a
        // class attribute.
        $plates = [
            [
                'branded'   => true,
                'label'     => 'Free plan',
                'plan'      => 'Free',
                'planClass' => 'es-slate2-plan es-slate2-plan-free',
                'note'      => 'The strip at the foot is ours. Everything above it is already yours.',
            ],
            [
                'branded'   => false,
                'label'     => 'White-label on',
                'plan'      => 'Pro',
                'planClass' => 'es-slate2-plan es-slate2-plan-pro',
                'note'      => 'The same recess, left blank. Nothing replaces it and nothing takes its place.',
            ],
        ];

        // The register. Every row is a real surface with a real gate;
        // see the header comment for the file each one lives in.
        $register = [
            [
                'surface' => 'The foot of your public schedule',
                'free'    => 'A dark strip holding two credits in one line: "Create your free schedule at eventschedule.com" and a credit to the project\'s sponsor.',
                'pill'    => 'Removed',
                'after'   => 'The whole strip goes. The page ends where your last event ends.',
            ],
            [
                'surface' => 'The card on your public event pages',
                'free'    => 'A panel headed "Create your own event schedule!" with a Create Schedule button, in the column beside your event details.',
                'pill'    => 'Removed',
                'after'   => 'The panel is not rendered and your own details take the space.',
            ],
            [
                'surface' => 'The calendar embed you paste on your own site',
                'free'    => 'The snippet ships with a small "Powered by Event Schedule" line under the iframe.',
                'pill'    => 'Not in the snippet',
                'after'   => 'Copy the snippet again and that line is simply not in it.',
            ],
            [
                'surface' => 'The ticket widget on your own site',
                'free'    => 'Twice over: a "Powered by Event Schedule" line under the purchase form inside the widget, and another under the iframe in the snippet you paste.',
                'pill'    => 'Both removed',
                'after'   => 'The one inside the widget goes on its own. The one in the snippet needs the snippet copied again.',
            ],
            [
                'surface' => 'The newsletters you send',
                'free'    => 'A "Powered by Event Schedule" line in the email footer, under your own footer text.',
                'pill'    => 'Removed',
                'after'   => 'Your footer text and unsubscribe link stay. Ours goes.',
            ],
            [
                'surface' => 'The browser tab icon on your pages',
                'free'    => 'The Event Schedule mark.',
                'pill'    => 'Becomes yours',
                'after'   => 'Your uploaded logo becomes the tab and home-screen icon.',
            ],
            [
                'surface' => 'Ads and promotions on your public pages',
                'free'    => 'A free schedule can carry one, but only on an install whose operator has switched ads on.',
                'pill'    => 'Never',
                'after'   => 'Nothing above the free tier is monetized, on any install.',
            ],
        ];

        $faqs = [
            [
                'q' => 'How do I remove Event Schedule branding?',
                'a' => 'Upgrade the schedule to Pro or Enterprise. There is no switch to find afterwards: the check reads the plan itself, so the strip at the foot of your page is gone as soon as the plan is active, on every surface at once.',
            ],
            [
                'q' => 'What exactly is removed?',
                'a' => 'Seven surfaces: the strip at the foot of your public schedule, which carries both our address and our sponsor credit; the "Create your own event schedule!" card beside your event details; the "Powered by Event Schedule" line in the calendar embed snippet; the same line on the embedded ticket widget, which carries it twice, once inside the widget and once in the snippet; the line in the footer of the newsletters you send; the Event Schedule tab icon, which your own logo replaces; and ads or promotions, which never appear above the free tier.',
            ],
            [
                'q' => 'Is anything left?',
                'a' => 'Nothing in the body of the page, and two things outside it. First, the metadata in the page head: the title in the browser tab still ends in "Event Schedule", the site name in a shared link preview still reads Event Schedule, and the breadcrumb data still names us as the site root. The tab icon itself becomes your logo. Second, if an admin granted your Enterprise plan by hand rather than you buying it, a small Event Schedule credit chip stays below the footer. Customers who pay through Stripe never carry that chip, and neither do plans earned through the referral programme.',
            ],
            [
                'q' => 'Do I need to change my embed after upgrading?',
                'a' => 'Yes, if you had already pasted one. Both snippets, calendar and tickets, bake the "Powered by" line into the HTML you copy, so that copy sits on your site rather than on ours and we cannot reach it. Copy each snippet again from the schedule and paste over the old one. The ticket widget also has a footer inside the frame itself, and that one is rendered by us, so it disappears without you touching anything.',
            ],
            [
                'q' => 'Is white labeling available on selfhosted installations?',
                'a' => 'Yes, with one exception, and it is a small one. Every schedule on a selfhosted install behaves like a paid one, so there is nothing to buy: no strip at the foot of your pages, no card beside your events, no line in either embed snippet or in your newsletters, and no ads. What stays is a small "Event Schedule" credit in the corner of your public pages. Event Schedule is given away under the Attribution Assurance License, which asks for the credit in return, so that one is not a plan you can upgrade past.',
            ],
            [
                'q' => 'Can I put my own branding in the space?',
                'a' => 'Yes, and most of it is free: a profile image, a header image, an accent colour, a font, and a solid, gradient or image background. On Pro you can also rename the words on the page with custom labels, add your own banner, and write Custom CSS. AI style generation, which produces a matching set of images, colour and font, is on Enterprise.',
            ],
            [
                'q' => 'Is a custom domain part of white label?',
                'a' => 'No, they are separate. Removing our branding is on Pro. Serving your schedule from your own domain is an Enterprise feature, and you can have either one without the other.',
            ],
        ];

        $dotSections = [
            ['top', 'The plate'],
            ['register', 'The register'],
            ['switch', 'No switch'],
            ['stays', 'What stays'],
            ['yours', 'Your space'],
            ['selfhost', 'Selfhost'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-slate2-page" class="es-slate2-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the plate pair                                      -->
    <!-- ============================================================ -->
    {{-- The nav overlays the top of the page, so the hero carries extra
         top padding rather than letting the plates sit under it. --}}
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 30%, rgba(44, 74, 104, 0.26), rgba(44, 74, 104, 0) 62%); opacity: 0.55;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 60%, rgba(169, 198, 221, 0.18), rgba(169, 198, 221, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:72px_72px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_36%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <p class="es-slate2-tag es-fade-up es-d-1 mb-5">White label</p>

                <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                    <span class="es-mask"><span class="es-mask-line">The only thing we add</span></span>
                    <span class="es-mask es-mask-2"><span class="es-mask-line">is <span class="es-slate2-grad">one strip</span> at the foot.</span></span>
                </h1>

                <p class="es-slate2-muted es-fade-up es-d-2 mx-auto mb-9 max-w-2xl text-lg sm:text-xl">
                    Everything above it is already yours. That strip carries our address and a credit
                    to the project's sponsor, and white-label takes the whole thing off: every surface
                    we render, the moment the plan is active, with nothing to switch on.
                </p>

                <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ app_url('/sign_up') }}" class="es-slate2-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                        Get started free
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                    <a href="#register" class="es-slate2-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                        See what comes off
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </div>
            </div>

            <!-- The plate pair. Identical slate, identical content,
                 identical foot height. One foot carries our strip and
                 one carries nothing. -->
            <div class="es-fade-up es-d-4 mt-14 grid gap-5 sm:grid-cols-2" data-reveal>
                @foreach ($plates as $plate)
                    @php $branded = $plate['branded']; @endphp
                    <div>
                        <div class="mb-3 flex items-baseline justify-between gap-3">
                            <p class="es-slate2-tag">{{ $plate['label'] }}</p>
                            <span class="{{ $plate['planClass'] }}">{{ $plate['plan'] }}</span>
                        </div>

                        <div class="es-slate2-plate">
                            @if (! $branded)
                                <div class="es-slate2-sheen" aria-hidden="true"></div>
                            @endif

                            <div class="es-slate2-plate-cap relative flex items-center justify-between gap-3 px-5 py-3.5">
                                <p class="es-slate2-engraved text-sm font-bold">{{ $plateName }}</p>
                                <p class="es-slate2-etch es-slate2-num text-[0.65rem] uppercase tracking-widest">September</p>
                            </div>

                            <div class="relative">
                                @foreach ($plateRows as $row)
                                    <div class="es-slate2-row flex items-baseline gap-3 px-5 py-3">
                                        <span class="es-slate2-etch es-slate2-num es-slate2-when shrink-0 text-[0.65rem] font-bold tracking-wide">{{ $row[0] }}</span>
                                        <span class="es-slate2-engraved es-slate2-title text-sm">{{ $row[1] }}</span>
                                        <span class="es-slate2-etch es-slate2-num shrink-0 text-[0.7rem]">{{ $row[2] }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div @class(['es-slate2-foot', 'relative', 'es-slate2-blank' => ! $branded])>
                                @if ($branded)
                                    {{-- The real strip on eventschedule.com carries two credits in
                                         one paragraph: messages.try_event_schedule and
                                         messages.supported_by. Both come off together. --}}
                                    <p class="es-slate2-strip px-4 py-4 leading-snug">
                                        Create your free schedule at <span class="underline">eventschedule.com</span>
                                        <span class="es-slate2-strip-sep" aria-hidden="true">&bull;</span>
                                        <span class="es-slate2-strip-wrap">Supported by <span class="underline">Invoice Ninja</span></span>
                                    </p>
                                @endif
                            </div>
                        </div>

                        <p class="es-slate2-muted mt-3 text-xs">{{ $plate['note'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The register (01)                                         -->
    <!-- ============================================================ -->
    <section id="register" class="es-slate2-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-slate2-mark mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                <p class="es-slate2-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The register</p>
                <h2 class="es-balance es-slate2-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Seven surfaces, <span class="es-slate2-grad">one decision</span>.
                </h2>
                <p class="es-slate2-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Every place the Event Schedule name lands on something of yours, what it says
                    while you are on the free plan, and what happens to it after that. All seven
                    read the same fact, your plan tier. What white-label does not remove is two
                    sections down, on the same page.
                </p>
            </div>

            <div class="es-slate2-card overflow-hidden p-5 sm:p-7" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="es-slate2-reg w-full border-collapse text-left">
                        <caption class="sr-only">Each surface that can carry Event Schedule branding, what it shows on the free plan, and what white-label does to it</caption>
                        <thead>
                            <tr class="es-slate2-tag">
                                <th scope="col" class="pb-3 pe-4 font-bold">Surface</th>
                                <th scope="col" class="pb-3 pe-4 font-bold">On the free plan</th>
                                <th scope="col" class="pb-3 font-bold">With white-label on</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($register as $reg)
                                <tr class="es-slate2-rule border-t">
                                    <th scope="row" class="es-slate2-ink w-1/4 py-4 pe-4 align-top text-sm font-bold">{{ $reg['surface'] }}</th>
                                    <td class="es-slate2-muted w-2/5 py-4 pe-4 align-top text-sm">{{ $reg['free'] }}</td>
                                    <td class="py-4 align-top">
                                        <span class="es-slate2-pill es-slate2-pill-off">
                                            <svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                            {{ $reg['pill'] }}
                                        </span>
                                        <span class="es-slate2-muted mt-1.5 block text-sm">{{ $reg['after'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-slate2-muted mt-4 text-xs sm:hidden">Scroll the table sideways to see the third column.</p>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2" data-reveal-group="90">
                <p class="es-slate2-muted text-sm" data-reveal>
                    <span class="es-slate2-ink font-semibold">On the last row:</span> eventschedule.com
                    does not run ads on any schedule at all, so nothing there carries them on any
                    plan. That row matters if you are looking at a selfhosted install, or at an
                    operator who runs their own free tier and has switched ads on.
                </p>
                <p class="es-slate2-muted text-sm" data-reveal>
                    <span class="es-slate2-ink font-semibold">One decision, seven rows:</span> all
                    seven key off one fact, whether this schedule's plan tier is free. There is no
                    row that reads something else, and none that reads a second schedule - a curator
                    page answers for itself, whoever else is on the bill. Event Schedule is
                    <a href="{{ marketing_url('/open-source') }}" class="es-slate2-accent font-medium underline">open source</a>,
                    so you can go and read the checks rather than take our word for them.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. No switch (02)                                            -->
    <!-- ============================================================ -->
    <section id="switch" class="es-slate2-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-slate2-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-slate2-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">No switch</p>
                    <h2 class="es-balance es-slate2-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        There is <span class="es-slate2-grad">nothing to turn on</span>.
                    </h2>
                    <p class="es-slate2-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        White-label is not a setting on a styling tab that you can forget to save.
                        The check reads your plan, so the answer changes the moment the plan does,
                        everywhere at once.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['It is the plan, not a checkbox', 'Nothing to find, nothing to save, nothing to get wrong on one page and right on another.'],
                            ['The surfaces move together', 'The footer strip, the event-page card, both embeds, the newsletter, the tab icon and the ad slot all read the plan rather than a saved preference.'],
                            ['It follows the plan both ways', 'Let a plan lapse and the strip comes back. Renew and it goes again. We would rather say so than have you find out.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-slate2-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-slate2-ink font-semibold">{{ $t }}</span> <span class="es-slate2-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-slate2-plan es-slate2-plan-pro">Pro</span>
                        <span class="es-slate2-muted ms-2 text-sm">Included on Pro at $5 a month, and on Enterprise.</span>
                    </p>
                </div>

                <!-- The one thing you do have to do. Which copy of the name
                     lives where decides whether we can take it off for you. -->
                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-slate2-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-slate2-ink text-lg font-bold">The one thing to redo</h3>
                            <span class="es-slate2-pill es-slate2-pill-keep">Your side</span>
                        </div>
                        <p class="es-slate2-muted mb-5 text-sm">
                            Both embed snippets, calendar and tickets, bake the "Powered by" line into
                            the HTML you copy. That copy lives on your site, not on ours, so we cannot
                            reach it. Copy each snippet again and paste over the old one.
                        </p>

                        <div class="es-slate2-code overflow-x-auto p-4 text-xs leading-relaxed" aria-hidden="true">
                            <p class="es-slate2-etch mb-2 text-[0.65rem] uppercase tracking-widest">What you paste, on Pro</p>
                            <p class="es-slate2-lit">&lt;iframe src="northgate.eventschedule.com?embed=true"&gt;</p>
                            <p class="es-slate2-lit">&lt;/iframe&gt;</p>
                        </div>

                        {{-- Where each copy of our name actually lives. This is the
                             whole reason one of them needs your hands and one does
                             not, and the ticket widget carries both. --}}
                        <dl class="mt-4 space-y-2">
                            <div class="es-slate2-sub flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 p-4">
                                <dt class="es-slate2-ink text-xs font-semibold">Inside the frame</dt>
                                <dd><span class="es-slate2-pill es-slate2-pill-off">Goes on its own</span></dd>
                                <dd class="es-slate2-muted w-full text-xs">Rendered by us on every load, so the ticket widget's own footer disappears with nothing for you to do.</dd>
                            </div>
                            <div class="es-slate2-sub flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 p-4">
                                <dt class="es-slate2-ink text-xs font-semibold">Outside it, in your HTML</dt>
                                <dd><span class="es-slate2-pill es-slate2-pill-keep">Re-copy</span></dd>
                                <dd class="es-slate2-muted w-full text-xs">One extra paragraph under the iframe, in both snippets. It stays until you paste the new snippet over it.</dd>
                            </div>
                        </dl>

                        <div class="es-slate2-rule mt-5 border-t pt-4">
                            <p class="es-slate2-muted text-xs">
                                Both embeds:
                                <a href="{{ marketing_url('/features/embed-calendar') }}" class="es-slate2-accent font-medium underline">calendar</a>
                                and
                                <a href="{{ marketing_url('/features/embed-tickets') }}" class="es-slate2-accent font-medium underline">tickets</a>.
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
    <!-- 4. What stays (03, fixed dark band)                          -->
    <!-- ============================================================ -->
    <section id="stays" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-slate2-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-slate2-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-slate2-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What stays</p>
                    <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        What we <span class="es-slate2-grad">do not take off</span>.
                    </h2>
                    <p class="es-slate2-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A page selling white-label that promises "no trace anywhere" gets found out on
                        the first afternoon. Nothing is left in the body of your page. Two things sit
                        outside it, and here they are.
                    </p>
                </div>

                <div class="grid gap-5 md:grid-cols-2" data-reveal-group="100">

                    <!-- The head metadata: title, share-card site name, breadcrumb root. -->
                    <div class="es-slate2-card flex flex-col p-6 sm:p-7" data-reveal>
                        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="text-lg font-bold text-white">The metadata in the page head</h3>
                            <span class="es-slate2-pill es-slate2-pill-keep">Stays</span>
                        </div>

                        <div class="es-slate2-tabbar mb-5" aria-hidden="true">
                            <span class="es-slate2-tab">
                                <span class="es-slate2-tabicon"></span>
                                <span class="es-slate2-engraved truncate">{{ $plateName }} <span class="es-slate2-lit">| Event Schedule</span></span>
                            </span>
                        </div>

                        <p class="es-slate2-muted mt-auto text-sm">
                            The icon on the tab becomes your uploaded logo on Pro. Three strings in the
                            head do not change: the title still ends in "Event Schedule", the site name
                            in a shared link preview still reads it, and the breadcrumb data still names
                            us as the site root. None of it appears in the page, but it is there, and we
                            are not going to pretend it is not.
                        </p>
                    </div>

                    <!-- The granted-plan credit -->
                    <div class="es-slate2-card flex flex-col p-6 sm:p-7" data-reveal>
                        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="text-lg font-bold text-white">A plan somebody handed you</h3>
                            <span class="es-slate2-pill es-slate2-pill-keep">One case</span>
                        </div>

                        <div class="mb-5 flex justify-end" aria-hidden="true">
                            <span class="es-slate2-chip">
                                <span class="es-slate2-chip-mark"></span>
                                <span>Event Schedule</span>
                            </span>
                        </div>

                        <p class="es-slate2-muted mt-auto text-sm">
                            If an admin granted your Enterprise plan by hand rather than you buying it,
                            a small credit chip like this one stays at the foot of your public pages.
                            Customers paying through Stripe never carry it, and neither do plans earned
                            through the referral programme. A gift keeps its label.
                        </p>
                    </div>
                </div>

                <p class="es-slate2-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                    That is the end of the list for your pages on this platform. A chip like the one
                    above also sits in the corner of every page on a selfhosted install, where the
                    licence rather than the plan puts it there - section 05 has that. One thing
                    beside all of it: mail leaves our server unless you point the schedule at your
                    own SMTP settings, which any plan can do. If any of it matters for your case,
                    <a href="{{ marketing_url('/contact') }}" class="es-slate2-lit font-medium underline">ask us</a>
                    before you pay rather than after.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Your space (04) - bento                                   -->
    <!-- ============================================================ -->
    <section id="yours" class="es-slate2-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-slate2-mark mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-slate2-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Your space</p>
                <h2 class="es-balance es-slate2-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    A blank slate is only <span class="es-slate2-grad">half the job</span>.
                </h2>
                <p class="es-slate2-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Taking our name off is the subtraction. This is what you can cut into the space
                    afterwards, and most of it costs nothing.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- 1: the free styling controls (md 2, lg 2) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner es-slate2-card relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <div class="mb-4 flex flex-wrap items-center gap-3">
                            <span class="es-slate2-plan es-slate2-plan-free">Free</span>
                            <p class="es-slate2-tag">The mark is yours</p>
                        </div>
                        <h3 class="es-slate2-ink mb-3 text-2xl font-bold lg:text-3xl">Your logo, your colour, your type</h3>
                        <p class="es-slate2-muted mb-6 lg:text-lg">
                            A profile image, a header image, an accent colour, a font, and a background
                            that can be a solid colour, a gradient or an image of your own. None of
                            that waits for an upgrade.
                        </p>

                        <div class="mt-auto flex flex-wrap items-center gap-3" aria-hidden="true">
                            @foreach (['#b45309', '#0f766e', '#7f1d1d', '#1e3a5f', '#4d7c0f', '#3f3f46'] as $sw)
                                <span class="es-slate2-swatch" style="background-color: {{ $sw }};"></span>
                            @endforeach
                            <span class="es-slate2-muted es-slate2-num text-xs">your accent colour</span>
                        </div>
                    </div>
                </div>

                <!-- 2: custom labels -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-slate2-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="mb-4 flex flex-wrap items-center gap-3">
                            <span class="es-slate2-plan es-slate2-plan-pro">Pro</span>
                            <p class="es-slate2-tag">Vocabulary</p>
                        </div>
                        <h3 class="es-slate2-ink mb-3 text-xl font-bold">Rename the words</h3>
                        <p class="es-slate2-muted mb-5 text-sm">
                            "Events" can read Classes, Services, Openings or Sessions. A white-labeled
                            page that still speaks our vocabulary is only half white-labeled.
                        </p>
                        <a href="{{ marketing_url('/features/custom-labels') }}" class="es-slate2-accent mt-auto inline-flex items-center gap-1.5 text-sm font-medium hover:underline">
                            Custom labels
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <!-- 3: banner -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-slate2-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="mb-4 flex flex-wrap items-center gap-3">
                            <span class="es-slate2-plan es-slate2-plan-pro">Pro</span>
                            <p class="es-slate2-tag">Top of page</p>
                        </div>
                        <h3 class="es-slate2-ink mb-3 text-xl font-bold">A banner of your own</h3>
                        <p class="es-slate2-muted text-sm">
                            Put your own announcement across the top of your guest pages. The strip at
                            the top is yours in the same way the strip at the foot stops being ours.
                        </p>
                        <div class="es-slate2-sub mt-auto p-3">
                            <p class="es-slate2-ink text-xs font-semibold">Doors at 7. Bar cash only tonight.</p>
                        </div>
                    </div>
                </div>

                <!-- 4: custom CSS (md 2) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner es-slate2-card relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <div class="flex flex-col gap-7 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-4 flex flex-wrap items-center gap-3">
                                    <span class="es-slate2-plan es-slate2-plan-pro">Pro</span>
                                    <p class="es-slate2-tag">Down to the pixel</p>
                                </div>
                                <h3 class="es-slate2-ink mb-3 text-2xl font-bold lg:text-3xl">Write the stylesheet yourself</h3>
                                <p class="es-slate2-muted mb-5 lg:text-lg">
                                    When the colour picker runs out, Custom CSS takes over. It loads on
                                    your public schedule pages, so a page with our name gone and your
                                    own stylesheet over the top does not look like ours at all.
                                </p>
                                <a href="{{ marketing_url('/features/custom-css') }}" class="es-slate2-accent inline-flex items-center gap-1.5 font-medium hover:underline">
                                    Custom CSS
                                    <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </a>
                            </div>
                            <div class="es-slate2-code w-full overflow-x-auto p-5 text-xs leading-relaxed lg:w-72" aria-hidden="true">
                                <p class="es-slate2-etch mb-2 text-[0.65rem] uppercase tracking-widest">custom.css</p>
                                <p class="es-slate2-lit">h1, h2 {</p>
                                <p class="es-slate2-engraved ps-4">letter-spacing: .06em;</p>
                                <p class="es-slate2-engraved ps-4">text-transform: uppercase;</p>
                                <p class="es-slate2-lit">}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5: AI style generation -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-slate2-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="mb-4 flex flex-wrap items-center gap-3">
                            <span class="es-slate2-plan es-slate2-plan-enterprise">Enterprise</span>
                            <p class="es-slate2-tag">One pass</p>
                        </div>
                        <h3 class="es-slate2-ink mb-3 text-xl font-bold">Generate a matching set</h3>
                        <p class="es-slate2-muted text-sm">
                            AI style generation produces a profile image, header image, background,
                            accent colour and font that go together, from a description of the place.
                            Useful if you are starting from genuinely nothing.
                        </p>
                    </div>
                </div>

                <!-- 6: the domain is a different thing (lg 2) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner es-slate2-card relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <div class="mb-4 flex flex-wrap items-center gap-3">
                            <span class="es-slate2-plan es-slate2-plan-enterprise">Enterprise</span>
                            <p class="es-slate2-tag">Not the same feature</p>
                        </div>
                        <h3 class="es-slate2-ink mb-3 text-2xl font-bold lg:text-3xl">The address is a separate question</h3>
                        <p class="es-slate2-muted mb-6 lg:text-lg">
                            Taking our name off the page is Pro. Serving the page from your own domain
                            is Enterprise, and the two are independent: a white-labeled schedule can
                            still sit on a subdomain, and a custom domain on its own would not remove
                            the strip at the foot.
                        </p>

                        <div class="mt-auto grid gap-3 sm:grid-cols-2">
                            <div class="es-slate2-sub p-4">
                                <p class="es-slate2-tag mb-1.5">Pro</p>
                                <p class="es-slate2-num es-slate2-ink break-all text-sm">northgate.eventschedule.com</p>
                                <p class="es-slate2-muted mt-1 text-xs">Our name off the page, on a subdomain.</p>
                            </div>
                            <div class="es-slate2-sub p-4">
                                <p class="es-slate2-tag mb-1.5">Enterprise</p>
                                <p class="es-slate2-num es-slate2-ink break-all text-sm">whatson.northgatehall.com</p>
                                <p class="es-slate2-muted mt-1 text-xs">
                                    Your own address as well.
                                    <a href="{{ marketing_url('/features/custom-domain') }}" class="es-slate2-accent font-medium underline">Custom domain</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Selfhost (05)                                             -->
    <!-- ============================================================ -->
    <section id="selfhost" class="es-slate2-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-slate2-mark mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-slate2-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Selfhost</p>
                <h2 class="es-balance es-slate2-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Run it yourself and it arrives <span class="es-slate2-grad">near enough blank</span>.
                </h2>
                <p class="es-slate2-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A selfhosted installation is white-labeled by default. There is no plan to buy,
                    no strip to remove, and every schedule on it behaves like a paid one. One credit
                    stays, in the corner, because the licence asks for it.
                </p>
            </div>

            {{-- Four cards, so 2x2 rather than the 3-up this section used with three. --}}
            <div class="grid gap-4 md:grid-cols-2" data-reveal-group="100">
                @foreach ([
                    ['Six of the seven, gone','The branding check answers no on an install that is not the hosted service, so the footer strip, the event-page card, both embed lines, the newsletter line and ads are never rendered in the first place.'],
                    ['The seventh: one credit, one corner', 'A small "Event Schedule" chip sits in the corner of your public pages. It is the Attribution Assurance License credit - the licence gives you the whole application and asks for the mention in return - so it is not gated on a plan and there is no setting that removes it. Nothing else on the page, and nothing in your email or your embeds, carries our name.'],
                    ['Every feature, not just this one', 'A selfhosted install resolves to the Enterprise tier throughout, so Custom CSS, custom labels and the banner come with it. The AI style generator is there too, but it calls an AI provider, so it stays hidden until you put your own API key in the environment file.'],
                    ['Your servers, your data', 'Run it on your own hardware for a client, a festival or a chain of rooms. The source is open, so the branding check and everything around it is there to read.'],
                ] as [$t, $d])
                    <div class="es-slate2-card es-slate2-hover p-6" data-reveal>
                        <h3 class="es-slate2-ink mb-2 text-lg font-bold">{{ $t }}</h3>
                        <p class="es-slate2-muted text-sm">{{ $d }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row" data-reveal>
                <a href="{{ marketing_url('/selfhost') }}" class="es-slate2-ghost inline-flex items-center justify-center gap-2 rounded-lg px-6 py-3 text-sm font-semibold">
                    How selfhosting works
                    <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
                <a href="{{ route('marketing.docs.schedule_styling') }}#remove-branding" class="es-slate2-ghost inline-flex items-center justify-center gap-2 rounded-lg px-6 py-3 text-sm font-semibold">
                    Read the styling guide
                    <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Three steps (06)                                          -->
    <!-- ============================================================ -->
    <section class="es-slate2-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-slate2-mark mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-slate2-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Start to finish</p>
                <h2 class="es-balance es-slate2-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Three steps, <span class="es-slate2-grad">none of them a setting</span>.
                </h2>
            </div>

            <div class="grid gap-5 md:grid-cols-3" data-reveal-group="110">
                @foreach ([
                    ['01', 'Upgrade the schedule', 'Pro at $5 a month, or Enterprise. The strip is gone on the next page load, with nothing else to do.'],
                    ['02', 'Re-copy your embed snippets', 'Only if you had already pasted one. Both snippets carry that line in your HTML, not ours. The widget footer inside the frame goes on its own.'],
                    ['03', 'Fill the space', 'Logo, colour, font and background are free. Custom labels, a banner and Custom CSS come with the same plan.'],
                ] as [$n, $t, $d])
                    <div class="es-slate2-card es-slate2-hover p-7" data-reveal="panel">
                        <p class="es-slate2-accent es-slate2-num mb-3 text-sm font-bold">{{ $n }}</p>
                        <h3 class="es-slate2-ink mb-2 text-lg font-bold">{{ $t }}</h3>
                        <p class="es-slate2-muted text-sm">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Related features                                          -->
    <!-- ============================================================ -->
    <section class="es-slate2-rule scroll-mt-24 border-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-slate2-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card
                        name="Custom CSS"
                        description="Write your own CSS for pixel-perfect schedule styling"
                        :url="marketing_url('/features/custom-css')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Custom Domain"
                        description="Use your own domain for your schedule URL"
                        :url="marketing_url('/features/custom-domain')"
                        icon-color="cyan"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Event Graphics"
                        description="Generate shareable images for social media"
                        :url="marketing_url('/features/event-graphics')"
                        icon-color="orange"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-slate2-accent inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ms-1 h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 9. Popular with                                              -->
    <!-- ============================================================ -->
    <section class="es-slate2-rule scroll-mt-24 border-t py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-slate2-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Popular with</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3" data-reveal-group="70">
                @foreach ([
                    ['/for-venues', 'Venues'],
                    ['/for-restaurants', 'Restaurants'],
                    ['/for-hotels-and-resorts', 'Hotels and Resorts'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-slate2-card es-slate2-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-slate2-muted text-xs">Event Schedule for</div>
                            <div class="es-slate2-ink text-base font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-slate2-accent h-5 w-5 shrink-0 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- 10. FAQ (07)                                                 -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-slate2-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-slate2-mark mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-slate2-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-slate2-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked before <span class="es-slate2-grad">upgrading</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-slate2-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-slate2-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-slate2-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-slate2-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-slate2-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-slate2-tag mb-6">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Take the name off <span class="es-slate2-grad">and put yours on</span>.
                    </h2>
                    <p class="es-slate2-muted mx-auto mb-10 max-w-xl text-lg sm:text-xl">
                        Claim the schedule for nothing, style it for nothing, and pay five dollars a
                        month when you want the last strip gone.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-slate2-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-slate2-muted mt-6 text-sm">No credit card required</p>

                    {{-- The finale closes the loop the hero opened: the last
                         object on the page is the recess itself, at the same
                         height as the hero's, with nothing cut into it. --}}
                    <div class="mx-auto mt-12 max-w-md">
                        <div class="es-slate2-foot es-slate2-blank es-slate2-recess" aria-hidden="true"></div>
                        <p class="es-slate2-muted mt-3 text-xs">Where our name would have been.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section dot navigation -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="es-slate2-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
