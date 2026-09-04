<x-marketing-layout>
    <x-slot name="title">Custom Domain | Use Your Own Domain - Event Schedule</x-slot>
    <x-slot name="description">Serve your whole schedule from events.yourdomain.com. HTTPS is issued for you once the CNAME resolves, and the links you send out carry your domain, not ours.</x-slot>
    <x-slot name="breadcrumbTitle">Custom Domain</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Custom Domain",
        "description": "Serve your event schedule on your own domain, with HTTPS provisioned automatically in Direct mode or a Cloudflare redirect in Redirect mode.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "featureList": [
            "Direct mode: the schedule is served on your own domain",
            "Automatic HTTPS certificate in Direct mode",
            "Redirect mode: a Cloudflare 301 from your domain to your schedule URL",
            "One CNAME record at your registrar",
            "Domain status shown as setting up, active or failed",
            "Canonical URL and sitemap on your domain in Direct mode",
            "No ads on a custom domain"
        ],
        "offers": {
            "@type": "Offer",
            "price": "{{ $entMonthly }}",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Available on Enterprise plan"
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
           Custom-domain "The Nameplate" styles.

           CONCEPT: THE ENGRAVED NAMEPLATE BESIDE A DOOR. A custom domain
           does not move your schedule anywhere. ResolveCustomDomain
           matches the incoming host, rewrites the Host header so the
           SAME routes serve the page, and then rewrites every address
           the response hands back. The room behind the door is
           unchanged; what changes is the plate on the front. So the
           metaphor and the feature story are one sentence, and the two
           modes become two physical acts: Direct fixes the plate to the
           door itself (the pages are served there, so Role::
           servesOnCustomDomain makes it the canonical URL), Redirect
           hangs a sign in the lobby that points at the room (a 301, so
           the eventschedule.com URL stays canonical).

           THE PROVISIONING RAIL SHOWS THE STAGES AS OBJECTS (review
           pass): each of the three states carries the thing actually on
           the door at that point - the sticker (still the address), a
           BLANK plate (nothing cut yet), the engraved plate. That is the
           honest picture of custom_domain_status, because until
           SyncDomainStatuses flips it to 'active' the subdomain is still
           what serves. .es-plate-stage bottom-aligns the three from md
           up. Also in that pass: the three house rules moved out of the
           Redirect column, where they read as redirect-only, into their
           own panel, and the Redirect step card gained the page-rule
           sign so it mirrors the CNAME plate opposite.

           THE PLATE IS A FIXED PHYSICAL OBJECT. .es-plate and
           .es-plate-strip render IDENTICALLY with .dark on and off, and
           nothing inside them animates, so the band-diff verifier reads
           a stable snapshot. Anything mode-flipping that gets nested in
           .es-plate-band needs an override AFTER the base rule
           (.grid-overlay, .animate-shimmer, .es-claim:focus-within all
           carry their own .dark rules in marketing.css). Auroras and
           es-rays are deliberately NOT used inside the pinned bands:
           .dark .es-aurora changes opacity, which is a diff.

           DELIBERATELY NOT A DOOR, A KEY OR A SIGN THAT LIGHTS UP.
           /for-nightclubs owns the steel door, /why-create-account owns
           engraved key tags ("The Keyring") and /for-nightclubs owns the
           exit-sign glow. Nothing here unlocks or switches on: a plate
           is screwed to a fixing that already exists, which is precisely
           the honest picture of this feature.

           COLOUR: the page keeps its inherited emerald family, spent as
           a MATERIAL rather than a gradient - deep vitreous green enamel
           with pale engraved lettering. Measured against the grounds
           this page actually paints, not against pure white:
             light ground #f4f8f6: ink #101815 16.85, muted #48544e 7.39,
                                   accent #065f46 7.17
             dark ground  #0a1210: ink #e6ede9 15.95, muted #9fb3aa 8.58,
                                   accent #6ee7b7 12.45
             plate       #0e2b21: engraved #cfe8dc 11.71, label #a8cfbd 8.89
             sticker     #eef1ee: ink #1a2420 14.01, label #59645e 5.41
           text-gray-500 is never used: it measures 4.83 on pure white but
           only ~4.4 on this ground. Use .es-plate-muted.

           BLADE RULE for this block: no @supports() probes. A "#" hex
           inside a parenthesized at-rule condition breaks Blade
           compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-plate-page { background-color: #f4f8f6; color: #101815; }
        .dark .es-plate-page { background-color: #0a1210; color: #e6ede9; }
        .es-plate-ink { color: #101815; }
        .dark .es-plate-ink { color: #e6ede9; }
        .es-plate-muted { color: #48544e; }
        .dark .es-plate-muted { color: #9fb3aa; }
        .es-plate-accent { color: #065f46; }
        .dark .es-plate-accent { color: #6ee7b7; }
        /* Always-lit accent, for the bands that stay dark in both modes. */
        .es-plate-lit { color: #6ee7b7; }

        /* --- Hairline separators --- */
        .es-plate-rule { border-top: 1px solid rgba(16, 24, 21, 0.09); }
        .dark .es-plate-rule { border-top-color: rgba(230, 237, 233, 0.09); }

        /* --------------------------------------------------------------
           THE PLATE. Deep enamelled green, two fixing screws, engraved
           lettering. Identical in both colour modes by design: no .dark
           rule below this line touches it.
           -------------------------------------------------------------- */
        .es-plate {
            position: relative;
            border-radius: 0.85rem;
            padding: 1.15rem 2.6rem;
            background-color: #0e2b21;
            background-image:
                linear-gradient(158deg, #16392c 0%, #0e2b21 46%, #071c16 100%),
                radial-gradient(120% 160% at 12% 0%, rgba(255, 255, 255, 0.09), rgba(255, 255, 255, 0) 55%);
            border: 1px solid rgba(255, 255, 255, 0.13);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.16),
                inset 0 -1px 0 rgba(0, 0, 0, 0.45),
                0 18px 34px -18px rgba(4, 20, 14, 0.75);
        }
        /* The two fixing screws. Abstract CSS discs with a cut slot, not an
           outline illustration of anything. */
        .es-plate::before,
        .es-plate::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 12px;
            height: 12px;
            margin-top: -6px;
            border-radius: 9999px;
            background-image:
                linear-gradient(145deg, rgba(255, 255, 255, 0.34), rgba(255, 255, 255, 0) 62%),
                linear-gradient(90deg, rgba(0, 0, 0, 0) 44%, rgba(0, 0, 0, 0.55) 44%, rgba(0, 0, 0, 0.55) 56%, rgba(0, 0, 0, 0) 56%);
            background-color: #0a231b;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.14), 0 1px 2px rgba(0, 0, 0, 0.5);
        }
        .es-plate::before { left: 12px; }
        .es-plate::after { right: 12px; }

        .es-plate-lg { padding: 1.6rem 3rem; border-radius: 1.05rem; }
        /* Narrow column variant: the screws end at 24px, so 1.9rem still clears them. */
        .es-plate-sm { padding: 1rem 1.9rem; }

        .es-plate-label {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: #a8cfbd;
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.5);
        }
        /* Engraved: a dark shadow below the glyph, a hair of light above it. */
        .es-plate-url {
            display: block;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #cfe8dc;
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.62), 0 -1px 0 rgba(255, 255, 255, 0.07);
            word-break: break-all;
        }
        .es-plate-url-lg { font-size: 1.15rem; }
        .es-plate-url-sm { font-size: 0.8rem; }
        .es-plate-note { font-size: 0.78rem; color: #a8cfbd; }
        .es-plate-hair {
            height: 1px;
            background: linear-gradient(90deg, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0));
        }

        /* The default subdomain: an adhesive label, not an engraving. Also a
           fixed object, so it carries no .dark rule either. */
        .es-plate-strip {
            border-radius: 0.35rem;
            padding: 0.6rem 0.9rem;
            background-color: #eef1ee;
            background-image: linear-gradient(180deg, #f7f9f7, #e7ebe8);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.18), inset 0 0 0 1px rgba(16, 24, 21, 0.08);
        }
        .es-plate-strip-label {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #59645e;
        }
        .es-plate-strip-url {
            display: block;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.82rem;
            font-weight: 600;
            color: #1a2420;
            word-break: break-all;
        }
        /* The fixing between the two: an abstract stroke, centred. */
        .es-plate-drop {
            width: 2px;
            height: 2.1rem;
            margin: 0 auto;
            border-radius: 1px;
            background: linear-gradient(180deg, rgba(16, 24, 21, 0.18), rgba(6, 95, 70, 0.75));
        }
        .dark .es-plate-drop { background: linear-gradient(180deg, rgba(230, 237, 233, 0.16), rgba(110, 231, 183, 0.75)); }

        /* Holds the object a provisioning state actually has on the door. Pushed to
           the foot of its column from md up so the three objects bottom-align
           whatever the length of the prose above them. Own rule rather than
           md:mt-auto/md:pt-6 utilities: no Tailwind rebuild is possible here. */
        .es-plate-stage { margin-top: 1.25rem; }
        @media (min-width: 768px) {
            .es-plate-stage { margin-top: auto; padding-top: 1.5rem; }
        }

        /* Holds the plate or the sticker at the top of a mode card, so the two
           headings below them sit on the same line whichever object is used. */
        .es-plate-objrow {
            display: flex;
            align-items: center;
            min-height: 5.5rem;
        }
        .es-plate-objrow > * { width: 100%; }

        /* The finale's claim field is a blank plate: type a name and it appears
           engraved. Same material as .es-plate, so it needs no dark variant. */
        .es-plate-engravable {
            background-color: #0e2b21;
            background-image: linear-gradient(158deg, #16392c 0%, #0e2b21 46%, #071c16 100%);
            border-color: rgba(255, 255, 255, 0.16);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.16), inset 0 -1px 0 rgba(0, 0, 0, 0.45);
        }
        /* Review pass: the typed name was plain white, so it sat ON the plate
           instead of being CUT INTO it. Same letterpress as .es-plate-url, and
           the fixed suffix takes the plate's own label ink rather than a grey
           borrowed from the page chrome. Both are mode-invariant, like the plate. */
        .es-plate-engravable input { color: #cfe8dc; text-shadow: 0 1px 0 rgba(0, 0, 0, 0.62), 0 -1px 0 rgba(255, 255, 255, 0.07); }
        .es-plate-engravable input::placeholder { color: #8fb3a2; text-shadow: none; }
        .es-plate-suffix { color: #a8cfbd; text-shadow: 0 1px 0 rgba(0, 0, 0, 0.5); }

        /* --- Section numeral: a miniature plate --- */
        .es-plate-num {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.9rem;
            border-radius: 0.4rem;
            background-color: #0e2b21;
            background-image: linear-gradient(158deg, #16392c, #071c16);
            border: 1px solid rgba(255, 255, 255, 0.13);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14);
            color: #cfe8dc;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.16em;
        }

        /* --- Cards --- */
        .es-plate-card {
            border: 1px solid rgba(16, 24, 21, 0.11);
            border-radius: 1rem;
            background-color: #ffffff;
        }
        .dark .es-plate-card {
            border-color: rgba(230, 237, 233, 0.11);
            background-color: #141d1a;
        }
        .es-plate-band .es-plate-card {
            border-color: rgba(230, 237, 233, 0.13);
            background-color: #10231c;
        }

        /* --- Eyebrow --- */
        .es-plate-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #48544e;
        }
        .dark .es-plate-tag { color: #9fb3aa; }
        .es-plate-band .es-plate-tag { color: #6ee7b7; }

        /* --- Chips and pills --- */
        .es-plate-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.32rem 0.8rem;
            border-radius: 9999px;
            border: 1px solid rgba(16, 24, 21, 0.14);
            background-color: rgba(255, 255, 255, 0.7);
            color: #48544e;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.04em;
        }
        .dark .es-plate-chip {
            border-color: rgba(230, 237, 233, 0.15);
            background-color: rgba(230, 237, 233, 0.05);
            color: #9fb3aa;
        }
        .es-plate-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.2rem 0.6rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(6, 95, 70, 0.4);
            color: #065f46;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-plate-pill { border-color: rgba(110, 231, 183, 0.42); color: #6ee7b7; }

        .es-plate-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.85rem;
            color: #101815;
            word-break: break-all;
        }
        .dark .es-plate-mono { color: #e6ede9; }
        .es-plate-band .es-plate-mono { color: #cfe8dc; }

        /* --- The mode table: a real record, scrolls inside itself --- */
        .es-plate-tablewrap { overflow-x: auto; }
        .es-plate-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.92rem;
        }
        .es-plate-table caption {
            caption-side: top;
            text-align: start;
            padding-bottom: 0.75rem;
            color: #48544e;
            font-size: 0.85rem;
        }
        .dark .es-plate-table caption { color: #9fb3aa; }
        .es-plate-table th,
        .es-plate-table td {
            padding: 0.85rem 1rem;
            text-align: start;
            vertical-align: top;
            border-bottom: 1px solid rgba(16, 24, 21, 0.09);
        }
        .dark .es-plate-table th,
        .dark .es-plate-table td { border-bottom-color: rgba(230, 237, 233, 0.09); }
        .es-plate-table thead th {
            color: #101815;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .dark .es-plate-table thead th { color: #e6ede9; }
        .es-plate-table tbody th {
            color: #48544e;
            font-weight: 600;
            white-space: nowrap;
        }
        .dark .es-plate-table tbody th { color: #9fb3aa; }
        .es-plate-table td { color: #101815; }
        .dark .es-plate-table td { color: #e6ede9; }
        .es-plate-table tbody tr:last-child th,
        .es-plate-table tbody tr:last-child td { border-bottom: 0; }

        /* --- The provisioning rail: saved, setting up, active --- */
        .es-plate-rail { position: relative; }
        .es-plate-rail-line {
            position: absolute;
            top: 0.53rem;
            left: 0.5rem;
            /* Stops at the centre of the third node rather than running off the
               edge: three equal columns with a 2rem gap. */
            right: calc(33.333% - 1.9rem);
            height: 2px;
            background: linear-gradient(90deg, rgba(6, 95, 70, 0.55), rgba(6, 95, 70, 0.18));
        }
        .dark .es-plate-rail-line { background: linear-gradient(90deg, rgba(110, 231, 183, 0.6), rgba(110, 231, 183, 0.18)); }
        .es-plate-node {
            position: relative;
            z-index: 1;
            width: 1.1rem;
            height: 1.1rem;
            border-radius: 9999px;
            border: 2px solid rgba(6, 95, 70, 0.55);
            background-color: #f4f8f6;
        }
        .dark .es-plate-node { border-color: rgba(110, 231, 183, 0.55); background-color: #0a1210; }
        .es-plate-node-on { background-color: #065f46; border-color: #065f46; }
        .dark .es-plate-node-on { background-color: #6ee7b7; border-color: #6ee7b7; }
        .es-plate-node-wait { animation: es-plate-breathe 2.4s ease-in-out infinite; }

        @keyframes es-plate-breathe {
            0%, 100% { box-shadow: 0 0 0 0 rgba(6, 95, 70, 0.35); }
            50% { box-shadow: 0 0 0 6px rgba(6, 95, 70, 0); }
        }

        /* --- Buttons and links --- */
        .es-plate-btn {
            background-color: #065f46;
            color: #ffffff;
            box-shadow: 0 18px 34px -16px rgba(6, 95, 70, 0.55);
        }
        .es-plate-btn:hover { background-color: #04503b; box-shadow: 0 22px 42px -16px rgba(6, 95, 70, 0.62); }
        .dark .es-plate-btn { background-color: #6ee7b7; color: #062a1e; }
        .dark .es-plate-btn:hover { background-color: #a7f3d0; }
        /* The finale panel is dark in both modes, so its button is the lit one. */
        .es-plate-band .es-plate-btn { background-color: #6ee7b7; color: #062a1e; }
        .es-plate-band .es-plate-btn:hover { background-color: #a7f3d0; }

        .es-plate-link { color: #065f46; }
        .es-plate-link:hover { color: #101815; }
        .dark .es-plate-link { color: #6ee7b7; }
        .dark .es-plate-link:hover { color: #e6ede9; }

        /* --- Hover treatment shared by FAQ rows and link cards --- */
        .es-plate-hover:hover { border-color: rgba(6, 95, 70, 0.45); }
        .dark .es-plate-hover:hover { border-color: rgba(110, 231, 183, 0.45); }
        .es-plate-hover:hover .es-plate-hover-title,
        .es-plate-hover:hover .es-plate-hover-arrow { color: #065f46; }
        .dark .es-plate-hover:hover .es-plate-hover-title,
        .dark .es-plate-hover:hover .es-plate-hover-arrow { color: #6ee7b7; }

        /* --------------------------------------------------------------
           Bands that stay dark in BOTH colour modes, plus the overrides
           the shared classes nested inside them need.
           -------------------------------------------------------------- */
        .es-plate-band {
            background-color: #0b1a15;
            background-image: radial-gradient(120% 100% at 50% 0%, #123128 0%, #0c1f19 55%, #07120f 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(230, 237, 233, 0.05);
        }
        .es-plate-band .grid-overlay {
            background-image:
                linear-gradient(rgba(230, 237, 233, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(230, 237, 233, 0.05) 1px, transparent 1px);
        }
        .es-plate-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-plate-band .es-claim:focus-within {
            border-color: rgba(110, 231, 183, 0.75);
            box-shadow: 0 0 0 4px rgba(110, 231, 183, 0.22);
        }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(6, 95, 70, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(110, 231, 183, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(6, 95, 70, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(110, 231, 183, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #065f46; }
        .dark .es-dot.is-active .es-dot-pip { background: #6ee7b7; }

        /* --- Focus rings. No border-radius here: setting it would change the
               element's own shape on focus, and outlines already follow it. --- */
        #es-plate-page a:focus-visible,
        #es-plate-page summary:focus-visible,
        #es-plate-page button:focus-visible {
            outline: 2px solid #065f46;
            outline-offset: 3px;
        }
        .dark #es-plate-page a:focus-visible,
        .dark #es-plate-page summary:focus-visible,
        .dark #es-plate-page button:focus-visible {
            outline-color: #6ee7b7;
        }
        .es-plate-band a:focus-visible,
        .es-plate-band summary:focus-visible,
        .es-plate-band button:focus-visible {
            outline-color: #6ee7b7 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-plate-node-wait { animation: none !important; }
        }
    </style>

    @php
        // The two modes, as a record. Rows are the questions an owner actually
        // asks; the columns are custom_domain_mode = 'direct' | 'redirect'
        // (RoleUpdateRequest: 'in:redirect,direct').
        $modeRows = [
            ['What visitors see', 'Your domain, on every page of the schedule.', 'Your domain for one hop, then your eventschedule.com URL.'],
            ['Where pages are served', 'On your domain. The request is matched to your schedule and served by the same routes as before.', 'On your eventschedule.com URL. Cloudflare forwards the request with a 301.'],
            ['HTTPS', 'A certificate is issued for you once the DNS record resolves.', 'Included free through Cloudflare\'s free plan.'],
            ['DNS you add', 'One CNAME record at your registrar.', 'Cloudflare nameservers, two proxied A records, and a 301 forwarding rule.'],
            ['Canonical URL for search', 'Your domain, once the status reads Active.', 'Your eventschedule.com URL. The redirect points at it, so it stays the address of record.'],
        ];

        // Direct mode, exactly the four steps the settings panel lists.
        $directSteps = [
            ['01', 'Name the domain', 'In your schedule settings, type the domain you own and choose Direct.'],
            ['02', 'Open DNS at your registrar', 'GoDaddy, Namecheap, Cloudflare, anywhere. You are looking for the DNS records screen.'],
            ['03', 'Add one CNAME', 'Name it @ for the root, or a label like events. Point it at the hostname the settings panel shows you.'],
            ['04', 'Wait for the record', 'Usually minutes, up to 48 hours. HTTPS is provisioned automatically once the record resolves.'],
        ];

        // Redirect mode, summarised from the setup guide.
        $redirectSteps = [
            ['01', 'Name the domain', 'In your schedule settings, type the domain you own and choose Redirect.'],
            ['02', 'Move DNS to Cloudflare', 'Add the domain to Cloudflare and point your registrar at the nameservers it gives you. The free plan is enough.'],
            ['03', 'Add proxied A records', 'One for the root and one for the wildcard, both proxied, so Cloudflare can answer for the domain.'],
            ['04', 'Forward with a 301', 'A page rule that forwards to your schedule URL and keeps the path, so a link to one event still lands on that event.'],
        ];

        // custom_domain_status, as written by RoleController and read by the
        // settings panel: null -> pending -> active, or failed.
        // The fourth column is the physical stage: which object is actually on the
        // door at that state. The sticker is still the address until the plate is on,
        // and the plate carries no letters until the record resolves.
        $railStates = [
            ['Saved', 'You entered the domain and chose Direct. The domain is registered with the platform that terminates HTTPS.', true,
                ['sticker', 'Still the address', 'myschedule.eventschedule.com']],
            ['Setting up', 'The settings panel shows a Setting up badge while the record propagates and the certificate is issued.', false,
                ['blank', 'Nothing cut yet', 'Waiting on the DNS record']],
            ['Active', 'The plate is on. Your domain now serves the schedule, and it becomes the canonical URL for search.', false,
                ['engraved', 'Engraved', 'events.yourdomain.com']],
        ];

        $enterpriseTiles = [
            ['label' => 'Custom domain', 'path' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9'],
            ['label' => 'AI features', 'path' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
            ['label' => 'Internal and unlisted events', 'path' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
            ['label' => 'Up to five team members', 'path' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ];

        // Plain text, so the visible copy and the FAQ schema cannot drift.
        $faqs = [
            [
                'q' => 'How do I set up a custom domain?',
                'a' => 'There are two modes. In Direct mode you enter the domain in your schedule settings, choose Direct, and add a single CNAME record at your registrar pointing at the hostname the settings panel shows you. HTTPS is provisioned automatically once that record resolves. In Redirect mode you move the domain\'s DNS to Cloudflare, add proxied A records for the root and the wildcard, and create a 301 forwarding page rule to your schedule URL that keeps the path.',
            ],
            [
                'q' => 'Do I need to buy a domain separately?',
                'a' => 'Yes. You need to own a domain name from any domain registrar. Event Schedule does not sell domains, but any domain you own can be used. Two limits are worth knowing before you start: an eventschedule.com host is rejected, and a domain can only be attached to one schedule at a time.',
            ],
            [
                'q' => 'Is SSL/HTTPS included?',
                'a' => 'Yes, and at no extra cost in either mode. In Direct mode a certificate is provisioned automatically once your CNAME record resolves. In Redirect mode HTTPS is included free through Cloudflare\'s free plan.',
            ],
            [
                'q' => 'Which plan includes custom domains?',
                'a' => 'Custom domains are available on the Enterprise plan, which is '.plan_price($entMonthly).' a month. Free and Pro plans use the default eventschedule.com subdomain. You can upgrade at any time from your account settings. Removing the Event Schedule badge is a separate thing and starts on Pro.',
            ],
            [
                'q' => 'How long does setup take, and what if it fails?',
                'a' => 'DNS usually propagates in minutes but can take up to 48 hours. While it does, the domain reads Setting up in your schedule settings, and the status is re-checked for you until the certificate is live. If the record cannot be verified the status reads Setup failed, so you can correct the record and save the domain again to retry.',
            ],
            [
                'q' => 'Does a custom domain change how search engines see my schedule?',
                'a' => 'In Direct mode, yes, and that is the point: once the status reads Active, your domain is the canonical URL for the schedule and its event pages, and the sitemap is generated on your host. Redirect mode is different. It sends a 301 to your eventschedule.com URL, so that URL stays the address of record and the custom domain is a doorway to it.',
            ],
            [
                'q' => 'Are ads ever shown on my custom domain?',
                'a' => 'No. Paid plans never carry ads at all, and requests that arrive on a custom domain are excluded from ads regardless of plan, so a lapsed subscription cannot put someone else\'s advert on a domain you own.',
            ],
        ];

        $dotSections = [
            ['top', 'The nameplate'],
            ['room', 'Behind the door'],
            ['modes', 'Two ways to fix it'],
            ['engrave', 'Cutting the letters'],
            ['status', 'Until the plate is on'],
            ['covers', 'What it covers'],
            ['plan', 'Enterprise'],
            ['faq', 'Questions'],
            ['claim', 'Your name'],
        ];
    @endphp

    <div id="es-plate-page" class="es-plate-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the plate on the door                               -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(6, 95, 70, 0.2), rgba(6, 95, 70, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(110, 231, 183, 0.14), rgba(110, 231, 183, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 es-plate-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <span class="es-plate-muted text-sm font-medium tracking-wide">Custom domain</span>
                        <span class="es-plate-pill">Enterprise</span>
                    </div>

                    <h1 class="es-balance es-plate-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Your own domain</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-plate-accent">on the door.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-plate-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Every schedule starts on an eventschedule.com subdomain. Enterprise lets you fix your own plate over it: serve the schedule directly on <strong class="es-plate-ink font-semibold">events.yourdomain.com</strong> with HTTPS issued for you, or point the domain at it through Cloudflare. Behind the door, nothing about your schedule changes.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                        <a href="{{ app_url('/sign_up') }}" class="es-plate-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="{{ route('marketing.docs.creating_schedules') }}#custom-domain" class="es-plate-link inline-flex items-center gap-2 text-lg font-semibold hover:underline">
                            Read the setup guide
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The object: a sticker, a fixing, and the plate that replaces it -->
                <div class="es-fade-up es-d-4 mx-auto w-full max-w-sm" aria-hidden="true">
                    <div class="es-plate-strip mx-auto max-w-xs">
                        <span class="es-plate-strip-label">Default</span>
                        <span class="es-plate-strip-url">myschedule.eventschedule.com</span>
                    </div>
                    <div class="es-plate-drop"></div>
                    <div class="es-plate es-plate-lg text-center">
                        <span class="es-plate-label">Custom domain</span>
                        <span class="es-plate-url es-plate-url-lg mt-2">events.yourdomain.com</span>
                        <div class="es-plate-hair my-4"></div>
                        <p class="es-plate-note">Direct mode. HTTPS issued automatically.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Behind the door, nothing moves (fixed-dark band)          -->
    <!-- ============================================================ -->
    <section id="room" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="mx-auto max-w-6xl">
            <div class="es-plate-band noise relative overflow-hidden rounded-[2.5rem] px-6 py-16 sm:px-12 lg:py-20" data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <div class="mx-auto mb-12 max-w-3xl text-center">
                        <div class="es-plate-num mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                        <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                            Behind the door, <span class="es-plate-lit">nothing moves</span>
                        </h2>
                        <p class="mx-auto max-w-2xl text-lg text-gray-300" data-reveal style="--reveal-delay: 0.15s;">
                            A custom domain is not a migration and not a second copy of anything. A request arriving on your domain is matched to your schedule and served by the same routes as before. What changes is the address on the way back out: links, feeds, the JSON the calendar loads, and the page a card payment returns to are all rewritten to your domain.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="90">
                        <div class="es-plate-card p-6" data-reveal="panel">
                            <p class="es-plate-tag mb-3">Your links</p>
                            <p class="es-plate-mono mb-3">events.yourdomain.com/summer-social</p>
                            <p class="text-sm leading-relaxed text-gray-400">Event pages keep the slug you set on the event. Only the host in front of it is different.</p>
                        </div>
                        <div class="es-plate-card p-6" data-reveal="panel">
                            <p class="es-plate-tag mb-3">Your feeds</p>
                            <p class="es-plate-mono mb-3">events.yourdomain.com/feed/ical</p>
                            <p class="text-sm leading-relaxed text-gray-400">The iCal and RSS feeds serve from your domain, and every event inside them links back to it.</p>
                        </div>
                        <div class="es-plate-card p-6" data-reveal="panel">
                            <p class="es-plate-tag mb-3">Your checkout</p>
                            <p class="es-plate-mono mb-3">events.yourdomain.com/...</p>
                            <p class="text-sm leading-relaxed text-gray-400">A ticket buyer is handed back to your domain when payment finishes, not to a subdomain they have never seen.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Two ways to fix a plate                                   -->
    <!-- ============================================================ -->
    <section id="modes" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-plate-num mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <h2 class="es-balance es-plate-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Two ways to <span class="es-plate-accent">fix a plate</span>
                </h2>
                <p class="es-plate-muted mt-5 text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    One screws the plate to the door itself. The other hangs a sign in the lobby that points at the room. Both put your domain in front of your audience; they differ in what happens after the click.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="110">
                <div class="es-plate-card flex flex-col p-7 lg:p-9" data-reveal="panel">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <p class="es-plate-tag">Direct mode</p>
                        <span class="es-plate-chip">Recommended</span>
                    </div>
                    <div class="es-plate-objrow mb-6" aria-hidden="true">
                        <div class="es-plate">
                            <span class="es-plate-label">On the door</span>
                            <span class="es-plate-url mt-1">events.yourdomain.com</span>
                        </div>
                    </div>
                    <h3 class="es-plate-ink mb-3 text-2xl font-bold">The plate on the door</h3>
                    <p class="es-plate-muted mb-4 leading-relaxed">
                        Requests land on your domain and are served there. One CNAME record at your registrar is the whole DNS job, and the certificate is issued for you once that record resolves. Your domain stays in the address bar on every page of the schedule, so it becomes the address search engines record for it.
                    </p>
                    <p class="es-plate-muted mt-auto text-sm">
                        Status moves from Setting up to Active on its own. Nothing to install and no proxy to maintain.
                    </p>
                </div>

                <div class="es-plate-card flex flex-col p-7 lg:p-9" data-reveal="panel">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <p class="es-plate-tag">Redirect mode</p>
                        <span class="es-plate-chip">Cloudflare</span>
                    </div>
                    <div class="es-plate-objrow mb-6" aria-hidden="true">
                        <div class="es-plate-strip">
                            <span class="es-plate-strip-label">In the lobby</span>
                            <span class="es-plate-strip-url">events.yourdomain.com &rarr; myschedule.eventschedule.com</span>
                        </div>
                    </div>
                    <h3 class="es-plate-ink mb-3 text-2xl font-bold">The sign in the lobby</h3>
                    <p class="es-plate-muted mb-4 leading-relaxed">
                        Cloudflare answers for your domain and forwards each request, path and all, to your schedule URL with a 301. Past naming the domain in your settings, the work happens in your Cloudflare dashboard, HTTPS comes with their free plan, and a link to one event still lands on that event.
                    </p>
                    <p class="es-plate-muted mt-auto text-sm">
                        Because visitors end up on your eventschedule.com URL, that URL stays the canonical one for search.
                    </p>
                </div>
            </div>

            <div class="es-plate-card mt-8 p-6 sm:p-8" data-reveal="panel">
                <div class="es-plate-tablewrap">
                    <table class="es-plate-table">
                        <caption>The two modes, side by side. Both are Enterprise, and you can switch between them by saving a different mode.</caption>
                        <thead>
                            <tr>
                                <th scope="col">&nbsp;</th>
                                <th scope="col">Direct</th>
                                <th scope="col">Redirect</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($modeRows as [$rowLabel, $directCell, $redirectCell])
                                <tr>
                                    <th scope="row">{{ $rowLabel }}</th>
                                    <td>{{ $directCell }}</td>
                                    <td>{{ $redirectCell }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Cutting the letters: the setup                            -->
    <!-- ============================================================ -->
    <section id="engrave" class="es-plate-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-plate-num mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <h2 class="es-balance es-plate-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Cutting the <span class="es-plate-accent">letters</span>
                </h2>
                <p class="es-plate-muted mt-5 text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Four steps in either mode, and the only thing you ever type into Event Schedule is the domain itself. Everything else happens where your DNS lives.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="100">
                <div class="es-plate-card flex flex-col p-7 lg:p-9" data-reveal="panel">
                    <div class="mb-6 flex items-center gap-3">
                        <p class="es-plate-tag">Direct mode</p>
                        <span class="es-plate-chip">One CNAME</span>
                    </div>
                    <ol class="space-y-5">
                        @foreach ($directSteps as [$stepNum, $stepTitle, $stepBody])
                            <li class="flex gap-4">
                                <span class="es-plate-accent flex-none font-mono text-sm font-black tabular-nums">{{ $stepNum }}</span>
                                <div>
                                    <h3 class="es-plate-ink mb-1 font-bold">{{ $stepTitle }}</h3>
                                    <p class="es-plate-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    <div class="mt-auto pt-7">
                        <div class="es-plate" aria-hidden="true">
                            <span class="es-plate-label">The record</span>
                            <div class="es-plate-hair my-3"></div>
                            <span class="es-plate-url">CNAME</span>
                            <p class="es-plate-note mt-2">Name: @ for the root, or a label like events</p>
                            <p class="es-plate-note">Value: the hostname shown in your schedule settings</p>
                        </div>
                    </div>
                </div>

                <div class="es-plate-card flex flex-col p-7 lg:p-9" data-reveal="panel">
                    <div class="mb-6 flex items-center gap-3">
                        <p class="es-plate-tag">Redirect mode</p>
                        <span class="es-plate-chip">Free Cloudflare plan</span>
                    </div>
                    <ol class="space-y-5">
                        @foreach ($redirectSteps as [$stepNum, $stepTitle, $stepBody])
                            <li class="flex gap-4">
                                <span class="es-plate-accent flex-none font-mono text-sm font-black tabular-nums">{{ $stepNum }}</span>
                                <div>
                                    <h3 class="es-plate-ink mb-1 font-bold">{{ $stepTitle }}</h3>
                                    <p class="es-plate-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    {{-- The redirect object is the adhesive sign, so the rule it carries is
                         printed on one rather than engraved. Mirrors the CNAME plate opposite. --}}
                    <div class="mt-auto pt-7">
                        <div class="es-plate-strip" aria-hidden="true">
                            <span class="es-plate-strip-label">The page rule</span>
                            <span class="es-plate-strip-url">*yourdomain.com/*</span>
                            <span class="es-plate-strip-url">301 &rarr; https://myschedule.eventschedule.com/$2</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Generic to both modes, so it sits under both columns rather than inside one. --}}
            <div class="es-plate-card mt-6 p-7 lg:p-8" data-reveal="panel">
                <h3 class="es-plate-ink mb-5 text-sm font-bold uppercase tracking-widest">Three house rules, whichever mode you pick</h3>
                <ul class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    @foreach ([
                        'The domain has to be one you own. Buy it wherever you like; Event Schedule does not sell domains.',
                        'An eventschedule.com host is rejected, and a domain can only be attached to one schedule at a time.',
                        'Delete the schedule and the domain is released from the platform, so you can point it somewhere else.',
                    ] as $rule)
                        <li class="flex gap-3">
                            <svg aria-hidden="true" class="es-plate-accent mt-0.5 h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="es-plate-muted text-sm leading-relaxed">{{ $rule }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="es-plate-muted mt-8 text-center text-sm" data-reveal>
                Both routes are written out step by step in the
                <a href="{{ route('marketing.docs.creating_schedules') }}#custom-domain" class="es-plate-link font-medium hover:underline">Custom Domain Setup guide</a>,
                including the exact Cloudflare records and page rule.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Until the plate is on: the provisioning rail              -->
    <!-- ============================================================ -->
    <section id="status" class="es-plate-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-plate-num mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <h2 class="es-balance es-plate-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Not live until the <span class="es-plate-accent">plate is on</span>
                </h2>
                <p class="es-plate-muted mt-5 text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Direct mode has a state, and your schedule settings show it plainly rather than leaving you to guess. Until it reads Active, your subdomain keeps serving the schedule exactly as before: the old sticker is still the address right up to the moment the plate goes on, so nothing is ever offline while DNS catches up.
                </p>
            </div>

            <div class="es-plate-card p-7 lg:p-10" data-reveal="panel">
                <div class="es-plate-rail grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div class="es-plate-rail-line hidden md:block" aria-hidden="true"></div>
                    @foreach ($railStates as $railIndex => [$stateName, $stateBody, $stateDone, [$objKind, $objLabel, $objLine]])
                        <div class="relative flex flex-col">
                            <span @class(['es-plate-node', 'es-plate-node-on' => $stateDone, 'es-plate-node-wait' => $railIndex === 1, 'mb-4', 'block']) aria-hidden="true"></span>
                            <h3 class="es-plate-ink mb-2 font-bold">{{ $stateName }}</h3>
                            <p class="es-plate-muted text-sm leading-relaxed">{{ $stateBody }}</p>

                            {{-- What is actually on the door at this state. --}}
                            <div class="es-plate-stage" aria-hidden="true">
                                @if ($objKind === 'sticker')
                                    <div class="es-plate-strip">
                                        <span class="es-plate-strip-label">{{ $objLabel }}</span>
                                        <span class="es-plate-strip-url">{{ $objLine }}</span>
                                    </div>
                                @elseif ($objKind === 'blank')
                                    <div class="es-plate es-plate-sm">
                                        <span class="es-plate-label">{{ $objLabel }}</span>
                                        <div class="es-plate-hair my-2"></div>
                                        <p class="es-plate-note">{{ $objLine }}</p>
                                    </div>
                                @else
                                    <div class="es-plate es-plate-sm">
                                        <span class="es-plate-label">{{ $objLabel }}</span>
                                        <span class="es-plate-url es-plate-url-sm mt-1">{{ $objLine }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="es-plate-rule mt-9 grid grid-cols-1 gap-6 pt-8 sm:grid-cols-2">
                    <div>
                        <h3 class="es-plate-ink mb-2 text-sm font-bold uppercase tracking-widest">If it takes a while</h3>
                        <p class="es-plate-muted text-sm leading-relaxed">DNS usually propagates in minutes and can take up to 48 hours. The status is re-checked for you every few minutes, so Setting up becomes Active without you refreshing anything.</p>
                    </div>
                    <div>
                        <h3 class="es-plate-ink mb-2 text-sm font-bold uppercase tracking-widest">If it fails</h3>
                        <p class="es-plate-muted text-sm leading-relaxed">A record that cannot be verified shows Setup failed next to your schedule URL. Fix the record, save the domain again, and the request to provision it is made again.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. What the plate covers: bento                              -->
    <!-- ============================================================ -->
    <section id="covers" class="es-plate-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-plate-num mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <h2 class="es-balance es-plate-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    What the plate <span class="es-plate-accent">covers</span>
                </h2>
                <p class="es-plate-muted mt-5 text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    A domain is only worth having if everything your audience touches sits behind it.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- b1 -->
                <div class="es-bento group relative md:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner es-plate-card relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <p class="es-plate-tag mb-4">The whole guest portal</p>
                        <h3 class="es-plate-ink mb-3 text-2xl font-bold lg:text-3xl">Every page an audience sees</h3>
                        <p class="es-plate-muted mb-6 text-lg leading-relaxed">The schedule itself, every event page, and the guest actions on them: RSVPs against your per-date capacity, ticket checkout, a shared link to one date. In Direct mode all of it is served on your domain, not framed or proxied from somewhere else.</p>
                        <div class="mt-auto flex flex-wrap gap-2">
                            <span class="es-plate-chip">Schedule page</span>
                            <span class="es-plate-chip">Event pages</span>
                            <span class="es-plate-chip">RSVP</span>
                            <span class="es-plate-chip">Checkout</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- b2 -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-plate-card relative flex h-full flex-col overflow-hidden p-7">
                        <p class="es-plate-tag mb-4">Subscriptions</p>
                        <h3 class="es-plate-ink mb-3 text-xl font-bold">Feeds on your host</h3>
                        <p class="es-plate-muted mb-6 leading-relaxed">The iCal and RSS feeds are served from your domain, and each event they carry links back to your domain too.</p>
                        <div class="mt-auto flex flex-wrap gap-2">
                            <span class="es-plate-chip">/feed/ical</span>
                            <span class="es-plate-chip">/feed/rss</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- b3 -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-plate-card relative flex h-full flex-col overflow-hidden p-7">
                        <p class="es-plate-tag mb-4">Payments</p>
                        <h3 class="es-plate-ink mb-3 text-xl font-bold">Checkout comes home</h3>
                        <p class="es-plate-muted mb-6 leading-relaxed">Card payment finishes and the buyer is returned to your domain, so the receipt page is on the address they trusted.</p>
                        <div class="mt-auto flex flex-wrap gap-2">
                            <span class="es-plate-chip">Success page</span>
                            <span class="es-plate-chip">Cancelled page</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- b4 -->
                <div class="es-bento group relative md:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner es-plate-card relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <p class="es-plate-tag mb-4">Search</p>
                        <h3 class="es-plate-ink mb-3 text-2xl font-bold lg:text-3xl">One address, recorded once</h3>
                        <p class="es-plate-muted mb-6 text-lg leading-relaxed">Two hosts serving the same listings is the classic way to split your own search results. Event Schedule picks one and states it: in Direct mode, once the status is Active, your domain is the canonical URL for the schedule and its events, and the sitemap is generated on your host. In Redirect mode the 301 points at your eventschedule.com URL, so that one stays canonical and your domain is a doorway.</p>
                        <div class="mt-auto flex flex-wrap gap-2">
                            <span class="es-plate-chip">Canonical tag</span>
                            <span class="es-plate-chip">Sitemap</span>
                            <span class="es-plate-chip">Open Graph URL</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- b5 -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-plate-card relative flex h-full flex-col overflow-hidden p-7">
                        <p class="es-plate-tag mb-4">No ads</p>
                        <h3 class="es-plate-ink mb-3 text-xl font-bold">Nobody else advertises here</h3>
                        <p class="es-plate-muted mb-6 leading-relaxed">Paid plans carry no ads at all, and a request on a custom domain is excluded from ads whatever the plan says.</p>
                        <div class="mt-auto flex flex-wrap gap-2">
                            <span class="es-plate-chip">No AdSense</span>
                            <span class="es-plate-chip">No promotions</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- b6 -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner es-plate-card relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <p class="es-plate-tag mb-4">The last badge</p>
                        <h3 class="es-plate-ink mb-3 text-2xl font-bold lg:text-3xl">Take our name off the page too</h3>
                        <p class="es-plate-muted mb-6 text-lg leading-relaxed">The domain handles the address bar. Two more settings handle what is inside the frame: white label removes the Powered by badge, and custom CSS lets you finish the styling yourself. Both start on the Pro plan and are included with Enterprise.</p>
                        <div class="mt-auto flex flex-wrap gap-4">
                            <a href="{{ marketing_url('/features/white-label') }}" class="es-plate-link inline-flex items-center gap-1.5 font-medium hover:underline">
                                White label
                                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                            <a href="{{ marketing_url('/features/custom-css') }}" class="es-plate-link inline-flex items-center gap-1.5 font-medium hover:underline">
                                Custom CSS
                                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Enterprise (fixed-dark band)                              -->
    <!-- ============================================================ -->
    <section id="plan" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="mx-auto max-w-6xl">
            <div class="es-plate-band noise relative overflow-hidden rounded-[2.5rem] px-6 py-16 sm:px-12 lg:py-20" data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-20"></div>
                </div>

                <div class="relative z-10">
                    <div class="mx-auto mb-12 max-w-3xl text-center">
                        <div class="es-plate-num mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                        <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                            Part of <span class="es-plate-lit">Enterprise</span>
                        </h2>
                        <p class="mx-auto max-w-2xl text-lg text-gray-300" data-reveal style="--reveal-delay: 0.15s;">
                            Custom domains sit on the Enterprise plan at {{ plan_price($entMonthly) }} a month, next to the AI features, internal and unlisted events, and a team of up to five people. Free and Pro schedules use their eventschedule.com subdomain, which is a perfectly good address to publish from while you decide.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4" data-reveal-group="80">
                        @foreach ($enterpriseTiles as $tile)
                            <div class="es-plate-card flex flex-col items-center p-6 text-center transition-all duration-300 hover:-translate-y-1" data-reveal>
                                <svg aria-hidden="true" class="es-plate-lit mb-3 h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $tile['path'] }}" />
                                </svg>
                                <div class="es-plate-lit text-sm font-medium">{{ $tile['label'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-10 text-center text-sm text-gray-400" data-reveal>
                        Removing the Powered by badge starts on Pro at {{ plan_price($proMonthly) }} a month.
                        <a href="{{ marketing_url('/pricing') }}" class="es-plate-lit font-medium hover:underline">Compare the plans</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Guides and who uses this                                  -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">

                <a href="{{ route('marketing.docs.creating_schedules') }}#custom-domain" class="es-plate-card es-plate-hover group flex flex-col p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-md" data-reveal>
                    <div class="es-plate mb-6 self-start" aria-hidden="true">
                        <span class="es-plate-label">Guide</span>
                    </div>
                    <h3 class="es-plate-hover-title es-plate-ink mb-3 text-2xl font-bold transition-colors">Set it up</h3>
                    <p class="es-plate-muted mb-4">Both modes, step by step, including the exact Cloudflare records and the 301 page rule.</p>
                    <span class="es-plate-hover-arrow es-plate-link mt-auto inline-flex items-center gap-2 font-medium transition-all group-hover:gap-3">
                        Read the guide
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.docs.saas.custom_domains') }}" class="es-plate-card es-plate-hover group flex flex-col p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-md" data-reveal>
                    <div class="es-plate mb-6 self-start" aria-hidden="true">
                        <span class="es-plate-label">Selfhost</span>
                    </div>
                    <h3 class="es-plate-hover-title es-plate-ink mb-3 text-2xl font-bold transition-colors">Running your own instance</h3>
                    <p class="es-plate-muted mb-4">Offering custom domains to your own customers on a selfhosted install: environment setup, provisioning and troubleshooting.</p>
                    <span class="es-plate-hover-arrow es-plate-link mt-auto inline-flex items-center gap-2 font-medium transition-all group-hover:gap-3">
                        Operator guide
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <div class="es-plate-card flex flex-col p-8" data-reveal>
                    <h3 class="es-plate-ink mb-4 text-xl font-bold">Popular with</h3>
                    <div class="space-y-3">
                        @foreach ([['/for-venues', 'Venues'], ['/for-hotels-and-resorts', 'Hotels & Resorts'], ['/for-restaurants', 'Restaurants']] as [$popHref, $popName])
                            <a href="{{ marketing_url($popHref) }}" class="es-plate-hover es-plate-card group/link flex items-center justify-between p-3 transition-all">
                                <span class="es-plate-hover-title es-plate-ink font-medium transition-colors">{{ $popName }}</span>
                                <svg aria-hidden="true" class="es-plate-hover-arrow es-plate-muted h-4 w-4 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Related features                                          -->
    <!-- ============================================================ -->
    <section class="es-plate-rule py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-plate-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card
                        name="White Label"
                        description="Remove Event Schedule branding for a fully branded experience"
                        :url="marketing_url('/features/white-label')"
                        icon-color="emerald"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Custom CSS"
                        description="Write your own CSS for pixel-perfect schedule styling"
                        :url="marketing_url('/features/custom-css')"
                        icon-color="teal"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Team Scheduling"
                        description="Add multiple team members to collaborate on your schedule"
                        :url="marketing_url('/features/team-scheduling')"
                        icon-color="green"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-plate-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 10. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-plate-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-plate-num mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <h2 class="es-balance es-plate-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-plate-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Everything you need to know about custom domains.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-plate-card es-plate-hover group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-plate-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-plate-accent flex-none font-mono text-sm font-bold tabular-nums" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-plate-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-plate-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-plate-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
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
            <div class="es-plate-band noise relative overflow-hidden rounded-[2.5rem] px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-plate-tag mb-4">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Take the name first. <span class="es-plate-lit">Add the plate later.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300">
                        Claim your subdomain now and publish from it for as long as you like. When you want your own domain on the front, Enterprise screws it on and the schedule behind it never moves.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim es-plate-engravable flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 px-5 py-4 transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold focus:outline-none focus:ring-0 sm:text-base">
                            <span class="es-plate-suffix shrink-0 select-none font-mono text-sm sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-plate-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">No credit card required</p>
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#141d1a] dark:text-gray-300">{{ $sectionLabel }}</span>
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
