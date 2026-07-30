<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Visual Artists | Exhibitions</x-slot>
    <x-slot name="description">Build your collector base directly. Announce exhibitions, sell tickets to openings, and email collectors. Zero platform fees. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Visual Artists</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Visual Artists",
        "description": "Build your collector base directly. Announce exhibitions, sell tickets to openings, and email collectors. Zero platform fees. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Visual Artists"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Visual Artists",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Artist Exhibition and Event Scheduling Software",
        "operatingSystem": "Web",
        "description": "Build your collector base directly. Announce exhibitions, sell tickets to openings, and email collectors. Zero platform fees. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Recurring open studio dates, with individual dates taken out",
            "Sub-schedules with their own colour and their own shareable link",
            "Draft events that stay off the public page until the gallery announces",
            "A header wall of the venue logos from your accepted public events",
            "Visitor photos, videos and comments on events, held for your approval",
            "Free RSVP with a capacity, counted per date",
            "Named ticket types with quantities counted per occurrence date",
            "Custom questions answered at checkout",
            "QR check-in at the door",
            "Zero platform fees on ticket sales through your own Stripe account",
            "Bookable studio visits with weekly hours and per-date overrides",
            "Direct newsletters to the people who follow your schedule",
            "A downloadable QR code for your schedule",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Embeddable calendar for your own portfolio site",
            "Auto-generated event graphics"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "artist exhibition calendar, visual artist scheduling, gallery show management, art event calendar, free artist scheduling",
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
           For-visual-artists "The Studio Wall" styles.

           CONCEPT: a painter's studio already has a schedule, and it is
           physical. It is the wall by the door: the gallery's card for
           the show that opens in March, the fair confirmation, the note
           saying which Saturdays the studio is open, the workshop list
           with places crossed off. Everything is pinned, nothing is
           filed, and it is only ever readable by people standing in the
           room. This page argues that Event Schedule is that wall with
           an address on it.

           THE METAPHOR AND THE FEATURE STORY ARE THE SAME SENTENCE:
           the wall is colour-coded because a SUB-SCHEDULE CARRIES A
           COLOUR. `Group` is fillable on name, name_en, slug and COLOR
           (app/Models/Group.php), and a group slug resolves as a public
           link of its own (routes/web.php /{slug}). So three pigments -
           vermilion for exhibitions, ochre for the studio, verdigris for
           teaching - are not decoration, they are the product's own
           organising device. Every strand on this page maps to one.

           WHAT A SUB-SCHEDULE IS NOT: it has no visibility flag, so it
           cannot hide anything. Hiding is Draft. The page says so.

           FIXED PHYSICAL OBJECTS, identical with .dark on and off:
             .es-brush-index - the cream index card. A card is a card.
             .es-brush-plate - the bronze gallery nameplate.
           Neither carries a `dark:` utility and neither contains a
           shared class that flips (es-glare, es-ring-glow and
           es-tilt-inner all carry their own .dark rules in
           marketing.css, so they are kept OUT of both objects).

           COLOUR: the page's existing three-pigment family, pulled down
           in lightness until it measures. The first-wave file ran
           #ef4444 / #d97706 / #14b8a6, which are all mid-tones that fail
           on a light ground; these are the same three hues as artists'
           pigments rather than as UI colours.

           Measured against the grounds this page actually paints:
             ink     #1a1613  16.38 canvas / 17.68 gesso / 14.48 sub
             muted   #57504a   7.21 canvas /  7.79 gesso /  6.38 sub
             verm    #a8351b   6.00 canvas /  6.48 gesso /  5.30 sub
             ochre   #7d5210   6.20 canvas /  6.69 gesso /  5.48 sub
             verd    #0d5f57   6.85 canvas /  7.40 gesso /  6.06 sub
             dink    #f2ece3  15.91 night  / 14.45 ncard / 16.17 band
             dmuted  #a99f94   7.18 night  /  6.52 ncard /  7.30 band
             verm-d  #f0937a   8.15 night  /  7.40 ncard /  8.28 band
             ochre-d #e8b84c  10.14 night  /  9.21 ncard / 10.30 band
             verd-d  #57cfbb   9.83 night  /  8.93 ncard /  9.99 band
             plate   #e8dcc9 on #4a3a30 8.00, #c9b79e on #4a3a30 5.54
             white on #a8351b 6.58, white on #0d5f57 7.52
           NEVER text-gray-500 on this ground - use .es-brush-muted.
           White at 70% or 80% over the vermilion fill measures 3.31 and
           4.00, so text on a pigment fill is full white only.
           ============================================================== */

        /* --- Ground and ink ---------------------------------------- */
        .es-brush-page { background-color: #f7f4ee; color: #1a1613; }
        .dark .es-brush-page { background-color: #141210; color: #f2ece3; }
        .es-brush-ink { color: #1a1613; }
        .dark .es-brush-ink { color: #f2ece3; }
        .es-brush-muted { color: #57504a; }
        .dark .es-brush-muted { color: #a99f94; }

        /* The three pigments. Each one names a strand of the practice. */
        .es-brush-verm { color: #a8351b; }
        .dark .es-brush-verm { color: #f0937a; }
        .es-brush-ochre { color: #7d5210; }
        .dark .es-brush-ochre { color: #e8b84c; }
        .es-brush-verd { color: #0d5f57; }
        .dark .es-brush-verd { color: #57cfbb; }
        /* Always-lit, for the fixed-dark bands in both colour modes. */
        .es-brush-lit { color: #e8b84c; }
        .es-brush-lit-verd { color: #57cfbb; }

        /* Ground-mixed heading ink: all three pigments, dark stops on
           the light ground and bright stops on the dark one, because a
           clipped gradient is scored stop by stop against the ground. */
        .es-brush-grad {
            background-image: linear-gradient(100deg, #9c3018 0%, #7d5210 52%, #0a544d 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-brush-grad,
        .es-brush-band .es-brush-grad {
            background-image: linear-gradient(100deg, #f4a58f 0%, #eec25c 52%, #6bd8c7 100%);
        }

        /* --- The wall ---------------------------------------------------
           A primed-canvas weave, not a drawing of one: two crossed
           repeating gradients plus a faint scuff at the top where the
           light falls. The wall is the only thing on the page allowed to
           change with the colour mode, because a studio at night really
           does look like this. */
        .es-brush-wall {
            background-color: #f2ede4;
            background-image:
                repeating-linear-gradient(90deg, rgba(26, 22, 19, 0.045) 0 1px, transparent 1px 6px),
                repeating-linear-gradient(0deg, rgba(26, 22, 19, 0.035) 0 1px, transparent 1px 6px),
                linear-gradient(180deg, rgba(255, 255, 255, 0.7), rgba(255, 255, 255, 0) 42%);
        }
        .dark .es-brush-wall {
            background-color: #17140f;
            background-image:
                repeating-linear-gradient(90deg, rgba(242, 236, 227, 0.045) 0 1px, transparent 1px 6px),
                repeating-linear-gradient(0deg, rgba(242, 236, 227, 0.03) 0 1px, transparent 1px 6px),
                linear-gradient(180deg, rgba(242, 236, 227, 0.05), rgba(242, 236, 227, 0) 42%);
        }

        /* --- Cards ------------------------------------------------------
           Opaque, always. A translucent surface makes the contrast probe
           walk past it to the page ground and score text against the
           wrong colour. */
        .es-brush-card {
            background-color: #fffdf8;
            border: 1px solid rgba(26, 22, 19, 0.13);
            border-radius: 0.35rem;
        }
        .dark .es-brush-card {
            background-color: #1f1c18;
            border-color: rgba(242, 236, 227, 0.13);
        }
        .es-brush-sub {
            background-color: #ece6db;
            border-radius: 0.25rem;
        }
        .dark .es-brush-sub { background-color: #2a2621; }
        .es-brush-band .es-brush-sub { background-color: #2b2620; }

        /* Section rules. These are page-local rather than an arbitrary-value
           Tailwind class because the marketing bundle is prebuilt: a colour
           Tailwind has never seen simply does not exist at runtime. */
        .es-brush-edge { border-color: rgba(26, 22, 19, 0.1); }
        .dark .es-brush-edge { border-color: rgba(242, 236, 227, 0.1); }

        .es-brush-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-brush-hover:hover {
            border-color: rgba(168, 53, 27, 0.45);
            box-shadow: 4px 6px 18px -10px rgba(26, 22, 19, 0.6);
        }
        .dark .es-brush-hover:hover {
            border-color: rgba(240, 147, 122, 0.42);
            box-shadow: 4px 6px 18px -10px rgba(0, 0, 0, 0.9);
        }

        /* --- The strand edge: the sub-schedule's own colour, painted as
               a wet stripe down the left of whatever it labels. -------- */
        .es-brush-strand { position: relative; overflow: hidden; }
        .es-brush-strand::before {
            content: "";
            position: absolute;
            top: 0; bottom: 0; left: 0;
            width: 4px;
            background: var(--strand, #7d5210);
        }
        .es-brush-strand-verm { --strand: #a8351b; }
        .es-brush-strand-ochre { --strand: #7d5210; }
        .es-brush-strand-verd { --strand: #0d5f57; }
        /* On a dark ground the pigment is thinned so it still reads. Written as
           compound selectors on purpose: .es-brush-index is a fixed object and
           must keep the full-strength pigment in both colour modes, so it must
           not be reachable by any of these. */
        .dark .es-brush-card.es-brush-strand-verm,
        .dark .es-brush-tape.es-brush-strand-verm { --strand: #d4593a; }
        .dark .es-brush-card.es-brush-strand-ochre,
        .dark .es-brush-tape.es-brush-strand-ochre { --strand: #b58324; }
        .dark .es-brush-card.es-brush-strand-verd,
        .dark .es-brush-tape.es-brush-strand-verd { --strand: #189184; }

        /* --- The pin -----------------------------------------------------
               Brass in both colour modes, because a pin is a pin. Two
               stops and a cast shadow, no outline drawing. */
        .es-brush-pin {
            position: absolute;
            top: -0.45rem;
            left: 50%;
            width: 0.85rem;
            height: 0.85rem;
            margin-left: -0.425rem;
            border-radius: 9999px;
            background: radial-gradient(circle at 34% 30%, #f2d9a4 0%, #c39a44 46%, #7a5c1c 100%);
            box-shadow: 0 2px 3px rgba(26, 22, 19, 0.45), inset 0 -1px 1px rgba(0, 0, 0, 0.35);
        }

        /* --- Pinned: a card hangs a degree or two off true and pulls
               square when you touch it. -------------------------------- */
        .es-brush-pinned {
            transform: rotate(var(--tilt, -1deg));
            transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .es-brush-pinned:hover { transform: rotate(0deg); }

        /* --- THE FIXED OBJECT: the cream index card. Identical with
               .dark on and off, so it must carry no dark: utility, no
               dark rule, and none of the shared tilt/glare classes. --- */
        .es-brush-index {
            background-color: #fffdf8;
            border: 1px solid rgba(26, 22, 19, 0.16);
            border-radius: 0.25rem;
            box-shadow: 5px 7px 20px -10px rgba(26, 22, 19, 0.55);
        }
        .es-brush-index-ink { color: #1a1613; }
        .es-brush-index-muted { color: #57504a; }
        .es-brush-index-rule { height: 1px; background: rgba(26, 22, 19, 0.14); }
        /* The three pigments keep their LIGHT values inside the card, in both
           colour modes. These sit after the .dark rules above and match their
           specificity, so the later rule wins and the card stays fixed. */
        .es-brush-index .es-brush-verm { color: #a8351b; }
        .es-brush-index .es-brush-ochre { color: #7d5210; }
        .es-brush-index .es-brush-verd { color: #0d5f57; }

        /* --- THE OTHER FIXED OBJECT: the engraved gallery nameplate.
               Bronze in both modes; the "engraving" is one inset shadow
               on the text, not an outline. ------------------------------ */
        .es-brush-plate {
            background-color: #4a3a30;
            background-image: linear-gradient(160deg, #5a483c 0%, #4a3a30 48%, #3d2f27 100%);
            border: 1px solid #6a5343;
            border-radius: 0.2rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.13), 0 2px 6px rgba(26, 22, 19, 0.4);
        }
        .es-brush-plate-name {
            color: #e8dcc9;
            letter-spacing: 0.11em;
            text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.55);
        }
        .es-brush-plate-note { color: #c9b79e; letter-spacing: 0.16em; }

        /* --- Eyebrow ---------------------------------------------------- */
        .es-brush-tag {
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #57504a;
        }
        .dark .es-brush-tag { color: #a99f94; }
        .es-brush-band .es-brush-tag { color: #e8b84c; }

        /* --- Section mark: a squeezed-out swatch of the section's own
               pigment, with the number scratched into it. -------------- */
        .es-brush-swatch {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.6rem;
            height: 2.2rem;
            border-radius: 0.15rem 0.6rem 0.2rem 0.5rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.82rem;
            font-weight: 800;
            color: #ffffff;
            box-shadow: inset 0 -3px 6px rgba(0, 0, 0, 0.28), 0 2px 5px rgba(26, 22, 19, 0.3);
        }
        .es-brush-swatch-verm { background-color: #a8351b; }
        .es-brush-swatch-ochre { background-color: #7d5210; }
        .es-brush-swatch-verd { background-color: #0d5f57; }

        /* --- Plan tags. Plan tiers only, never a state badge. --------- */
        .es-brush-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            border: 1px solid transparent;
            border-radius: 0.2rem;
            padding: 0.1rem 0.42rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.13em;
            text-transform: uppercase;
        }
        .es-brush-plan-free { border-color: rgba(26, 22, 19, 0.26); color: #57504a; }
        .dark .es-brush-plan-free { border-color: rgba(242, 236, 227, 0.28); color: #a99f94; }
        .es-brush-band .es-brush-plan-free { border-color: rgba(242, 236, 227, 0.28); color: #a99f94; }
        .es-brush-plan-pro { border-color: rgba(168, 53, 27, 0.5); color: #a8351b; background-color: rgba(168, 53, 27, 0.08); }
        .dark .es-brush-plan-pro { border-color: rgba(240, 147, 122, 0.45); color: #f0937a; background-color: rgba(240, 147, 122, 0.1); }
        .es-brush-band .es-brush-plan-pro { border-color: rgba(240, 147, 122, 0.45); color: #f0937a; background-color: rgba(240, 147, 122, 0.1); }
        /* The Pro tag sits on the fixed cream index card in the hero, so it
           must keep its light values in both colour modes. This has to come
           AFTER `.dark .es-brush-plan-pro`: the specificity is identical, so
           source order is what decides it. */
        .es-brush-index .es-brush-plan-pro {
            border-color: rgba(168, 53, 27, 0.5);
            color: #a8351b;
            background-color: rgba(168, 53, 27, 0.08);
        }

        /* --- The ledger: the working year as strips of tape ------------
               A single date is a torn square, a run is a torn strip. The
               left and width come from the same PHP row that prints the
               dates, so the tape and the text cannot disagree. */
        .es-brush-ledger { width: 100%; min-width: 34rem; border-collapse: collapse; text-align: left; }
        /* The "Set up as" column is the widest and the least essential, so it
           joins at md. Table cells default to table-cell, hence display:none
           below the breakpoint rather than a Tailwind pair. */
        .es-brush-md-cell { display: none; }
        @media (min-width: 768px) { .es-brush-md-cell { display: table-cell; } }
        .es-brush-track {
            position: relative;
            height: 1.1rem;
            border-radius: 0.15rem;
            background-color: #e6dfd2;
        }
        .dark .es-brush-track { background-color: #262220; }
        .es-brush-tape {
            position: absolute;
            top: 0; bottom: 0;
            overflow: hidden;
        }
        .es-brush-tape-fill {
            width: 100%;
            height: 100%;
            transform-origin: left center;
            transition: transform 1s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--tape-d, 0s);
            background-color: var(--strand, #7d5210);
            /* torn ends: the mask nibbles both edges, no outline shape */
            -webkit-mask-image: repeating-linear-gradient(0deg, #000 0 2px, rgba(0, 0, 0, 0.82) 2px 4px);
            mask-image: repeating-linear-gradient(0deg, #000 0 2px, rgba(0, 0, 0, 0.82) 2px 4px);
        }
        html.es-anim [data-reveal]:not(.is-revealed) .es-brush-tape-fill { transform: scaleX(0); }
        .es-brush-ruler {
            display: flex;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.56rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #57504a;
        }
        .dark .es-brush-ruler { color: #a99f94; }
        .es-brush-ruler span { flex: 1 1 0; min-width: 0; text-align: center; }

        /* --- The fixed-dark band: the studio after the opening -------- */
        .es-brush-band {
            background-color: #12100e;
            background-image:
                radial-gradient(ellipse 78% 56% at 50% 0%, rgba(168, 53, 27, 0.3), rgba(168, 53, 27, 0) 72%),
                linear-gradient(180deg, #1b1713 0%, #12100e 62%);
        }
        /* Shared classes that flip with the colour mode and would break
           the band's fixed rendering. They are invisible to a grep of
           this file, so they are overridden here, after the base rules. */
        .es-brush-band .grid-overlay {
            background-image:
                linear-gradient(rgba(242, 236, 227, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(242, 236, 227, 0.05) 1px, transparent 1px);
        }
        .es-brush-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-brush-band .es-claim:focus-within {
            border-color: rgba(232, 184, 76, 0.75);
            box-shadow: 0 0 0 4px rgba(232, 184, 76, 0.22);
        }

        /* --- Buttons and links ---------------------------------------- */
        .es-brush-btn {
            background-color: #a8351b;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 14px 28px -16px rgba(168, 53, 27, 0.85);
        }
        .es-brush-btn:hover { background-color: #9c3018; transform: translateY(-1px); box-shadow: 0 18px 34px -14px rgba(168, 53, 27, 0.9); }
        .es-brush-ghost {
            border: 1px solid rgba(26, 22, 19, 0.22);
            color: #1a1613;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-brush-ghost:hover { border-color: rgba(168, 53, 27, 0.5); background-color: rgba(168, 53, 27, 0.07); }
        .dark .es-brush-ghost { border-color: rgba(242, 236, 227, 0.24); color: #f2ece3; }
        .dark .es-brush-ghost:hover { border-color: rgba(240, 147, 122, 0.45); background-color: rgba(240, 147, 122, 0.08); }
        /* Dot-nav tooltip: one surface, two colour modes, no arbitrary
           Tailwind value that the prebuilt bundle would not contain. */
        .es-brush-tip {
            background-color: #ffffff;
            border: 1px solid rgba(26, 22, 19, 0.14);
            color: #1a1613;
        }
        .dark .es-brush-tip {
            background-color: #1f1c18;
            border-color: rgba(242, 236, 227, 0.14);
            color: #f2ece3;
        }

        .es-brush-link { color: #0d5f57; }
        .es-brush-link:hover { color: #1a1613; }
        .dark .es-brush-link { color: #57cfbb; }
        .dark .es-brush-link:hover { color: #f2ece3; }

        /* --- The brushstroke underline. One abstract stroke that draws
               itself when its heading arrives; rests drawn. ----------- */
        .es-brush-ul { position: relative; display: inline-block; }
        .es-brush-stroke {
            position: absolute;
            left: -0.03em;
            right: -0.03em;
            bottom: -0.22em;
            width: calc(100% + 0.06em);
            height: 0.4em;
            overflow: visible;
            pointer-events: none;
        }
        .es-brush-stroke path {
            fill: none;
            stroke: #7d5210;
            stroke-width: 6;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-dasharray: 1;
            stroke-dashoffset: 0;
        }
        .dark .es-brush-stroke path { stroke: #e8b84c; }
        .es-brush-band .es-brush-stroke path { stroke: #e8b84c; }
        html.es-anim [data-reveal]:not(.is-revealed) .es-brush-stroke path { stroke-dashoffset: 1; }
        html.es-anim [data-reveal].is-revealed .es-brush-stroke path { animation: es-brush-draw 0.95s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        @keyframes es-brush-draw { from { stroke-dashoffset: 1; } to { stroke-dashoffset: 0; } }

        /* --- Shared-system recolors (brand blue by default) ----------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(168, 53, 27, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(240, 147, 122, 0.11), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(168, 53, 27, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(240, 147, 122, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #a8351b; }
        .dark .es-dot.is-active .es-dot-pip { background: #f0937a; }

        /* --- Focus rings. No border-radius here: an outline already
               follows the element's own shape. ------------------------- */
        #es-brush-page a:focus-visible,
        #es-brush-page summary:focus-visible,
        #es-brush-page button:focus-visible,
        #es-brush-page input:focus-visible {
            outline: 2px solid #a8351b;
            outline-offset: 3px;
        }
        .dark #es-brush-page a:focus-visible,
        .dark #es-brush-page summary:focus-visible,
        .dark #es-brush-page button:focus-visible,
        .dark #es-brush-page input:focus-visible {
            outline-color: #f0937a;
        }
        .es-brush-band a:focus-visible,
        .es-brush-band summary:focus-visible,
        .es-brush-band button:focus-visible,
        .es-brush-band input:focus-visible {
            outline-color: #e8b84c !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-brush-pinned { transform: none; transition: none; }
            .es-brush-pinned:hover { transform: none; }
            .es-brush-btn:hover { transform: none; }
            .es-brush-tape-fill { transform: none !important; transition: none !important; }
            .es-brush-stroke path { animation: none !important; stroke-dashoffset: 0 !important; }
        }
    </style>

    @php
        // The three strands. A sub-schedule carries a colour (Group.color) and a
        // slug of its own, so the page's whole palette is the product's own
        // organising device rather than decoration. Full class strings, never
        // interpolated fragments, so Tailwind has nothing to generate.
        $strands = [
            'exhibitions' => ['Exhibitions', 'es-brush-strand-verm',  'es-brush-verm'],
            'studio'      => ['Studio',      'es-brush-strand-ochre', 'es-brush-ochre'],
            'teaching'    => ['Teaching',    'es-brush-strand-verd',  'es-brush-verd'],
        ];

        // The wall in the hero: three cards, one per strand, each a real product
        // configuration. Rotations are fixed here so the wall is the same wall on
        // every render.
        $wall = [
            [
                'strand' => 'exhibitions',
                'title'  => 'Ten Windows',
                'where'  => 'Bell Street Gallery',
                'when'   => 'Mar 6 to Apr 4',
                'note'   => 'One entry, on the gallery\'s page and on yours',
                'tilt'   => '-1.6deg',
                'plan'   => null,
            ],
            [
                'strand' => 'studio',
                'title'  => 'Open studio',
                'where'  => 'The studio, 11am to 5pm',
                'when'   => 'First Saturday, Apr to Nov',
                'note'   => 'One recurring event, two dates taken out',
                'tilt'   => '1.2deg',
                'plan'   => null,
            ],
            [
                'strand' => 'teaching',
                'title'  => 'Monotype workshop',
                'where'  => 'Eight places, three left',
                'when'   => 'Sat Jun 20, 10am',
                'note'   => 'Places sold in advance, counted for that date',
                'tilt'   => '-0.8deg',
                'plan'   => 'Pro',
            ],
        ];

        // The working year, March to December: ten months, so one month is ten
        // per cent. left/width are computed from the dates printed in the same
        // row, which is why the tape and the text cannot drift apart.
        $ledger = [
            ['Ten Windows',        'Bell Street Gallery', 'Mar 6 to Apr 4',      'exhibitions', 2,  10, 'One event, accepted onto both pages',        null],
            ['Open studio',        'The studio',          'First Sat, Apr to Nov', 'studio',    11,  79, 'One recurring event, two dates removed',     null],
            ['Spring Art Fair',    'Riverside Fair',      'May 8 to 11',         'exhibitions', 22,   3, 'One event, at the fair as the venue',        null],
            ['Monotype workshop',  'The studio',          'Sat Jun 20',          'teaching',    36,   3, 'Eight places, questions at checkout',        'Pro'],
            ['Slow Water',         'Kiln Room',           'Sep 4 to Oct 12',     'exhibitions', 61,  13, 'A Draft until the gallery announces it',     null],
            ['Studio visits',      'The studio',          'By appointment',      'studio',       1,  98, 'Bookable slots, not a fixed date',           'Pro'],
            ['Winter print sale',  'The studio',          'Dec 5 to 7',          'studio',      91,   3, 'Free to attend, with a capacity',            null],
        ];
        $months = ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // The header wall of venues, from Role::logoWallRoles(). Placeholder names
        // only: no third-party logo is drawn, and no real gallery is named.
        $plates = [
            ['Bell Street Gallery', 'Solo, Mar'],
            ['Kiln Room', 'Group, Sep'],
            ['Riverside Fair', 'Booth, May'],
            ['The Annexe', 'Group, Nov'],
            ['Harbour Print Room', 'Solo, Jan'],
            ['Fold Projects', 'Group, Jun'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for visual artists?',
                'a' => 'Yes. The wall itself costs nothing: your public page and its permanent link, recurring open studio dates with individual dates taken out, sub-schedules with their own colour and their own link, Drafts that stay off the page until you announce, the header wall of the venues you have shown with, two-way Google, Outlook and CalDAV calendar sync, an embeddable calendar, built-in analytics, a downloadable QR code for your schedule, free RSVP with a capacity, and up to 10 newsletter emails a month, counted per recipient rather than per send. Selling places, generating event graphics and bookable studio visits are on the Pro plan at $5 a month, and Event Schedule charges zero platform fees on ticket sales.',
            ],
            [
                'q' => 'Can I list exhibitions, open studios and art fairs together?',
                'a' => 'Yes, and you can keep them apart at the same time. Sub-schedules sort one page into strands, each with its own colour and its own link, so you can send a gallery the exhibitions and a school the workshops without splitting yourself into two pages. To be clear about what a sub-schedule is: it organises and colour-codes, it does not hide anything. If a show is not announced yet, keep the event as a Draft.',
            ],
            [
                'q' => 'How do collectors and art lovers find out about a new show?',
                'a' => 'Two ways, and you control both. Anyone can follow your schedule, which puts their name and email on your list, and you write and send the newsletter yourself when there is something to say. Nothing goes out in your name automatically, and nobody is emailed on your behalf. The other way is the link: your schedule has one permanent address you can put in a bio, print on a show card, hand out as a QR code, or embed in the portfolio site you already have.',
            ],
            [
                'q' => 'Do my open studio Saturdays have to be entered one at a time?',
                'a' => 'No. One recurring event covers the whole run: pick the day of the week and the hours, and give the recurrence an end, either a date or a number of dates. Date exceptions take out the weekends you are away without rebuilding anything, and every date is a real occurrence with its own page, its own iCal file and its own RSVP count.',
            ],
            [
                'q' => 'Can I sell places at a workshop or a ticketed opening?',
                'a' => 'Yes, on the Pro plan. Create as many named ticket types as the event needs, each with its own price and quantity. The quantity is counted per occurrence date, so a full March does not stop April selling. Add your own questions to be answered at checkout, check people in with a QR code at the door, and take the money through your own Stripe account. Event Schedule charges zero platform fees, so what you keep is the price less what Stripe charges.',
            ],
            [
                'q' => 'What happens to the photographs people take at the opening?',
                'a' => 'They can go on the event. Visitors can add photos, videos and comments with just a name and an email, and everything lands in an approval queue first, so nothing is public until you have looked at it. A per-schedule setting can require an account instead. Free schedules hold up to 25 photos; the Pro plan removes the cap and lets you download the lot as a zip.',
            ],
            [
                'q' => 'Can I show a gallery\'s exhibition without retyping it?',
                'a' => 'Yes. When a gallery lists you on their event, it arrives on your schedule and waits for you to accept it. Accept it and the same entry appears on both pages, so the dates cannot end up saying two different things. Nothing shows on your page that you have not agreed to.',
            ],
        ];

        $dotSections = [
            ['top', 'The wall'],
            ['year', 'The year'],
            ['pin', 'What a pin holds'],
            ['plates', 'Where you have shown'],
            ['opening', 'After the opening'],
            ['sell', 'Putting a price on it'],
            ['tell', 'Telling people'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-brush-page" class="es-brush-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the wall itself                                     -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero es-brush-wall noise relative scroll-mt-24 overflow-hidden pb-20 pt-28 lg:pb-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(168, 53, 27, 0.2), rgba(168, 53, 27, 0) 62%); opacity: 0.5;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 45%, rgba(13, 95, 87, 0.16), rgba(13, 95, 87, 0) 62%); opacity: 0.45;"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 30%, rgba(125, 82, 16, 0.14), rgba(125, 82, 16, 0) 60%); opacity: 0.45;"></div>
            <div class="es-spot absolute inset-0"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="es-brush-tag es-fade-up es-d-1 mb-5">For painters, illustrators and makers</p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.04] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Every date is already</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">pinned to <span class="es-brush-grad">your wall</span>.</span></span>
                    </h1>

                    <p class="es-brush-muted es-fade-up es-d-2 mb-6 max-w-xl text-lg sm:text-xl">
                        The card from the gallery. The fair confirmation. The note saying which
                        Saturdays the studio is open. It is all there by the door, and it is only
                        readable by people standing in the room.
                    </p>
                    <p class="es-brush-muted es-fade-up es-d-2 mb-9 max-w-xl text-base">
                        Event Schedule is that wall with an address on it.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="#year" class="es-brush-ghost inline-flex items-center justify-center gap-2 rounded-md px-7 py-4 text-base font-semibold">
                            See what goes on it
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-brush-btn inline-flex items-center justify-center gap-2 rounded-md px-7 py-4 text-base font-semibold">
                            Start your wall
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The wall. Three cream cards, one per strand, each pinned. -->
                <div class="es-fade-up es-d-4 space-y-6 pt-4">
                    @foreach ($wall as $w)
                        @php [$sName, $sEdge, $sInk] = $strands[$w['strand']]; @endphp
                        <div data-reveal class="es-brush-pinned relative" style="--tilt: {{ $w['tilt'] }};">
                            <span class="es-brush-pin" aria-hidden="true"></span>
                            <div class="es-brush-index es-brush-strand {{ $sEdge }} p-5 ps-6 sm:p-6 sm:ps-6">
                                <div class="mb-2 flex items-baseline justify-between gap-3">
                                    <span class="{{ $sInk }} text-[0.6rem] font-extrabold uppercase tracking-[0.2em]">{{ $sName }}</span>
                                    @if ($w['plan'])
                                        <span class="es-brush-plan es-brush-plan-pro">{{ $w['plan'] }}</span>
                                    @endif
                                </div>
                                <p class="es-brush-index-ink text-xl font-black tracking-tight">{{ $w['title'] }}</p>
                                <p class="es-brush-index-muted mt-1 text-sm">{{ $w['where'] }}</p>
                                <div class="es-brush-index-rule my-4" aria-hidden="true"></div>
                                <p class="es-brush-index-ink font-mono text-sm font-bold">{{ $w['when'] }}</p>
                                <p class="es-brush-index-muted mt-2 text-xs">{{ $w['note'] }}</p>
                            </div>
                        </div>
                    @endforeach

                    <p class="es-brush-muted pt-1 text-xs">
                        <span class="es-brush-verm font-bold">Exhibitions</span>,
                        <span class="es-brush-ochre font-bold">studio</span> and
                        <span class="es-brush-verd font-bold">teaching</span> are three sub-schedules.
                        The colours are the product's own way of sorting a page, not a decoration
                        on this one.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The year (01): the ledger, as strips of tape               -->
    <!-- ============================================================ -->
    <section id="year" class="scroll-mt-24 es-brush-edge border-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-brush-swatch es-brush-swatch-verm mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                <p class="es-brush-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The year</p>
                <h2 class="es-balance es-brush-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Nine months of work,
                    <span class="es-brush-ul"><span class="es-brush-grad">one wall</span><svg class="es-brush-stroke" viewBox="0 0 300 14" preserveAspectRatio="none" aria-hidden="true"><path pathLength="1" d="M5,9 C70,3 130,12 185,6 C235,1 275,9 296,5"></path></svg></span>.
                </h2>
                <p class="es-brush-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A solo show, a fair booth, a studio that opens once a month, a workshop with
                    eight places in it. Four different kinds of thing, pinned to the same board,
                    on one link you only ever have to hand out once.
                </p>
            </div>

            <div class="es-bento group relative" data-tilt="2.5" data-reveal="panel">
                <div class="es-tilt-inner es-brush-card overflow-hidden p-5 sm:p-7">
                    <div class="overflow-x-auto">
                        <table class="es-brush-ledger">
                            <caption class="sr-only">A working year, March to December: each show with where it is, its dates, how it is set up in Event Schedule, and a bar showing when it runs.</caption>
                            <thead>
                                <tr class="es-brush-tag">
                                    <th scope="col" class="pb-3 pe-3 font-extrabold">Show</th>
                                    <th scope="col" class="pb-3 pe-3 font-extrabold">Where</th>
                                    <th scope="col" class="pb-3 pe-3 font-extrabold">Dates</th>
                                    <th scope="col" class="es-brush-md-cell pb-3 font-extrabold">Set up as</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ledger as $i => [$lName, $lWhere, $lDates, $lStrand, $lLeft, $lWidth, $lSetup, $lPlan])
                                    @php [$sName, $sEdge, $sInk] = $strands[$lStrand]; @endphp
                                    <tr class="es-brush-edge border-t">
                                        <th scope="row" class="es-brush-ink py-3 pe-3 align-top text-sm font-bold">
                                            {{ $lName }}
                                            <span class="{{ $sInk }} block text-[0.6rem] font-extrabold uppercase tracking-[0.18em]">{{ $sName }}</span>
                                        </th>
                                        <td class="es-brush-muted py-3 pe-3 align-top text-xs">{{ $lWhere }}</td>
                                        <td class="es-brush-muted py-3 pe-3 align-top font-mono text-xs">{{ $lDates }}</td>
                                        <td class="es-brush-muted es-brush-md-cell py-3 align-top text-xs">
                                            {{ $lSetup }}
                                            @if ($lPlan)
                                                <span class="es-brush-plan es-brush-plan-pro ms-1">{{ $lPlan }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="pb-3">
                                            <div class="es-brush-track" aria-hidden="true">
                                                <div class="es-brush-tape {{ $sEdge }}" style="left: {{ $lLeft }}%; width: {{ $lWidth }}%;">
                                                    <div class="es-brush-tape-fill" style="--tape-d: {{ $i * 0.07 }}s;"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4">
                                        <div class="es-brush-ruler" aria-hidden="true">
                                            @foreach ($months as $m)
                                                <span>{{ $m }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <p class="es-brush-muted mt-5 border-t es-brush-edge pt-4 text-xs">
                        The tape is the colour of the strand it belongs to, and its position comes
                        from the same dates the row prints. Two of these seven rows are on the Pro
                        plan; the other five are free.
                    </p>

                    <div class="es-glare" aria-hidden="true"></div>
                    <div class="es-ring-glow" aria-hidden="true"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. What a pin holds (02)                                     -->
    <!-- ============================================================ -->
    <section id="pin" class="scroll-mt-24 es-brush-edge border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-brush-swatch es-brush-swatch-ochre mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-brush-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What a pin holds</p>
                <h2 class="es-balance es-brush-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    A date is not the only thing <span class="es-brush-grad">on the card</span>.
                </h2>
                <p class="es-brush-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Four things the wall in the studio already does, and how the page does them.
                    All four are on the free plan.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2" data-reveal-group="100">
                @foreach ([
                    ['studio', 'The Saturdays repeat', 'One recurring event covers the whole run: pick the day of the week and the hours, and give it an end, either a closing date or a number of dates. Date exceptions take out the two weekends you are away, so a change to the pattern is not a rebuild.'],
                    ['exhibitions', 'The show nobody has announced', 'Keep the event as a Draft and it stays off your public page until the gallery has sent the invitations. Then publish it. A sub-schedule cannot do this, because a sub-schedule has no visibility of its own; hiding is what Draft is for.'],
                    ['teaching', 'Prints, paintings and teaching, sorted', 'Sub-schedules split one page into strands, each with its own colour and its own link. Send a school the workshops and a gallery the exhibitions, from a page you only maintain once.'],
                    ['exhibitions', 'The gallery already typed it', 'When a gallery lists you on their event it arrives on your schedule and waits for you to accept it. Accept it and the same entry shows on both pages, so the dates cannot end up saying two different things.'],
                ] as $pi => [$pStrand, $pTitle, $pBody])
                    @php [$sName, $sEdge, $sInk] = $strands[$pStrand]; @endphp
                    <div data-reveal class="es-brush-pinned relative pt-2" style="--tilt: {{ $pi % 2 === 0 ? '-0.7deg' : '0.7deg' }};">
                        <span class="es-brush-pin" aria-hidden="true"></span>
                        <div class="es-brush-card es-brush-hover es-brush-strand {{ $sEdge }} h-full p-6 ps-6">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span class="{{ $sInk }} text-[0.6rem] font-extrabold uppercase tracking-[0.2em]">{{ $sName }}</span>
                                <span class="es-brush-plan es-brush-plan-free">Free</span>
                            </div>
                            <h3 class="es-brush-ink mb-2 text-lg font-bold">{{ $pTitle }}</h3>
                            <p class="es-brush-muted text-sm">{{ $pBody }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Where you have shown (03): the nameplate wall             -->
    <!-- ============================================================ -->
    <section id="plates" class="es-brush-wall scroll-mt-24 es-brush-edge border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-brush-swatch es-brush-swatch-verd mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-brush-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Where you have shown</p>
                    <h2 class="es-balance es-brush-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The galleries go up <span class="es-brush-grad">on the wall too</span>.
                    </h2>
                    <p class="es-brush-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Your page header can be a wall of the venues hosting your events, each one
                        linking to that venue's own schedule. It builds itself out of shows you have
                        already entered, so the exhibition history at the top of your page is a side
                        effect of keeping the dates straight.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['Only the shows you both agreed to', 'A venue appears once you have accepted their event, and a venue that runs its own schedule has to have accepted yours too. Nothing goes on your wall over somebody\'s objection.'],
                            ['Drafts stay off it', 'Draft, cancelled and unlisted events are excluded, so a show that has not been announced does not leak out through the header.'],
                            ['You set the order', 'Drag the ones that matter to the front. The rest fall in alphabetically behind them.'],
                        ] as [$plTitle, $plBody])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-brush-verd mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-brush-ink font-semibold">{{ $plTitle }}</span> <span class="es-brush-muted">- {{ $plBody }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-brush-plan es-brush-plan-free">Free</span>
                        <span class="es-brush-muted ms-2 text-sm">Every plan. It is a header option on your schedule, not an add-on.</span>
                    </p>
                </div>

                <!-- The plates. Bronze in both colour modes: a plate is a plate. -->
                <div data-reveal="panel">
                    <div class="grid grid-cols-2 gap-3 sm:gap-4">
                        @foreach ($plates as $pk => [$plateName, $plateNote])
                            <div class="es-brush-pinned" style="--tilt: {{ $pk % 2 === 0 ? '-0.6deg' : '0.6deg' }};">
                                <div class="es-brush-plate flex h-full flex-col justify-center px-4 py-5">
                                    <p class="es-brush-plate-name text-sm font-bold">{{ $plateName }}</p>
                                    <p class="es-brush-plate-note mt-1.5 text-[0.6rem] font-bold uppercase">{{ $plateNote }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="es-brush-muted mt-5 text-xs">
                        A venue needs a picture on its own schedule to appear, and the wall holds up
                        to thirty-six of them. Names here are illustrative.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. After the opening (04, fixed dark band)                   -->
    <!-- ============================================================ -->
    <section id="opening" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-brush-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-brush-swatch es-brush-swatch-verm mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-brush-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">After the opening</p>
                    <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The wall fills up
                        <span class="es-brush-ul"><span class="es-brush-grad">on its own</span><svg class="es-brush-stroke" viewBox="0 0 300 14" preserveAspectRatio="none" aria-hidden="true"><path pathLength="1" d="M5,9 C70,4 130,11 185,6 C235,2 275,8 296,5"></path></svg></span>.
                    </h2>
                    <p class="text-lg text-gray-300" data-reveal style="--reveal-delay: 0.15s;">
                        People photograph an opening whether you ask them to or not, and the pictures
                        end up somewhere you will never see them. They can go on the event instead.
                    </p>
                </div>

                <!-- Duplex: what visitors send, and what you let through. -->
                <div class="grid gap-6 lg:grid-cols-2" data-reveal-group="110">
                    <div class="es-brush-card rounded-lg p-7" data-reveal="panel" style="background-color: #231f1a; border-color: rgba(242, 236, 227, 0.14);">
                        <p class="es-brush-lit-verd mb-3 text-[0.6rem] font-extrabold uppercase tracking-[0.2em]">What they send</p>
                        <h3 class="mb-3 text-xl font-bold text-white">A name, an email, and a photograph</h3>
                        <p class="mb-5 text-sm text-gray-400">
                            Visitors add photos, videos and comments to the event without making an
                            account. If you would rather they signed in, a per-schedule setting asks
                            for an account instead.
                        </p>
                        <div class="space-y-2.5">
                            @foreach ([
                                ['Photographs', 'Of the room, the work, the crowd'],
                                ['Video', 'A short clip of the space'],
                                ['Comments', 'What people said about a piece'],
                            ] as [$fTitle, $fBody])
                                <div class="es-brush-sub p-3.5">
                                    <p class="text-sm font-semibold text-white">{{ $fTitle }}</p>
                                    <p class="text-xs text-gray-400">{{ $fBody }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="es-brush-card rounded-lg p-7" data-reveal="panel" style="background-color: #231f1a; border-color: rgba(242, 236, 227, 0.14);">
                        <p class="es-brush-lit mb-3 text-[0.6rem] font-extrabold uppercase tracking-[0.2em]">What you let through</p>
                        <h3 class="mb-3 text-xl font-bold text-white">Nothing is public until you say so</h3>
                        <p class="mb-5 text-sm text-gray-400">
                            Everything lands in an approval queue first. You are the editor of your
                            own page, which matters when the work in the photograph is yours.
                        </p>
                        <div class="space-y-2.5">
                            <div class="es-brush-sub flex items-baseline justify-between gap-3 p-3.5">
                                <span class="text-sm font-semibold text-white">Held for approval</span>
                                <span class="es-brush-lit font-mono text-xs font-bold">the default</span>
                            </div>
                            <div class="es-brush-sub flex items-baseline justify-between gap-3 p-3.5">
                                <span class="text-sm font-semibold text-white">Photos on a free schedule</span>
                                <span class="es-brush-lit font-mono text-xs font-bold">25</span>
                            </div>
                            <div class="es-brush-sub flex items-baseline justify-between gap-3 p-3.5">
                                <span class="text-sm font-semibold text-white">Photos on Pro, and a zip of the lot</span>
                                <span class="es-brush-lit font-mono text-xs font-bold">no cap</span>
                            </div>
                        </div>
                        <p class="mt-5 text-xs text-gray-400">
                            Star ratings and written feedback after the event are a separate Pro
                            feature, if you want to know what people actually thought.
                        </p>
                    </div>
                </div>

                <p class="mx-auto mt-8 max-w-2xl text-center text-sm text-gray-400" data-reveal>
                    <span class="es-brush-plan es-brush-plan-free">Free</span>
                    <span class="ms-2">Photos, videos and comments with the approval queue, on every plan.</span>
                    <span class="es-brush-plan es-brush-plan-pro ms-3">Pro</span>
                    <span class="ms-2">Removes the 25-photo cap and adds the zip download.</span>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Putting a price on it (05)                                -->
    <!-- ============================================================ -->
    <section id="sell" class="scroll-mt-24 es-brush-edge border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-brush-swatch es-brush-swatch-verd mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-brush-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Putting a price on it</p>
                <h2 class="es-balance es-brush-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    A free opening, a paid workshop, <span class="es-brush-grad">an hour in the studio</span>.
                </h2>
                <p class="es-brush-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Three different ways of counting people, and the honest answer about which
                    plan each one is on.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-brush-card es-brush-hover es-brush-strand es-brush-strand-verm flex flex-col p-6 ps-6" data-reveal>
                    <p class="es-brush-verm mb-2 text-[0.6rem] font-extrabold uppercase tracking-[0.2em]">Exhibitions</p>
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-brush-ink text-lg font-bold">The opening, free but counted</h3>
                        <span class="es-brush-plan es-brush-plan-free">Free</span>
                    </div>
                    <p class="es-brush-muted mb-5 text-sm">
                        Turn on registration and put a capacity on it. The count is kept per date, so
                        a full first Saturday leaves the next one untouched, and the page shows how
                        many places are left.
                    </p>
                    <div class="es-brush-sub mt-auto p-4" aria-hidden="true">
                        <p class="es-brush-muted text-[0.6rem] font-extrabold uppercase tracking-[0.18em]">Sat Mar 6, opening</p>
                        <p class="es-brush-ink mt-1 font-mono text-2xl font-black">54 <span class="es-brush-muted text-sm font-bold">of 80 registered</span></p>
                    </div>
                </div>

                <div class="es-brush-card es-brush-hover es-brush-strand es-brush-strand-verd flex flex-col p-6 ps-6" data-reveal>
                    <p class="es-brush-verd mb-2 text-[0.6rem] font-extrabold uppercase tracking-[0.2em]">Teaching</p>
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-brush-ink text-lg font-bold">The workshop, sold in advance</h3>
                        <span class="es-brush-plan es-brush-plan-pro">Pro</span>
                    </div>
                    <p class="es-brush-muted mb-5 text-sm">
                        Named ticket types with their own price and quantity, counted per occurrence
                        date. Ask your own questions at checkout, scan a QR code at the door, and
                        take the money through your own Stripe account.
                    </p>
                    <div class="es-brush-sub mt-auto p-4" aria-hidden="true">
                        <p class="es-brush-muted text-[0.6rem] font-extrabold uppercase tracking-[0.18em]">Platform fee</p>
                        <p class="es-brush-ink mt-1 font-mono text-2xl font-black">$0 <span class="es-brush-muted text-sm font-bold">on every sale</span></p>
                    </div>
                </div>

                <div class="es-brush-card es-brush-hover es-brush-strand es-brush-strand-ochre flex flex-col p-6 ps-6" data-reveal>
                    <p class="es-brush-ochre mb-2 text-[0.6rem] font-extrabold uppercase tracking-[0.2em]">Studio</p>
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-brush-ink text-lg font-bold">Studio visits, by appointment</h3>
                        <span class="es-brush-plan es-brush-plan-pro">Pro</span>
                    </div>
                    <p class="es-brush-muted mb-5 text-sm">
                        Publish bookable slots instead of a fixed date. Set your weekly hours, how
                        far apart slots start, a buffer between them, and per-date overrides for the
                        days you are away or installing.
                    </p>
                    <div class="es-brush-sub mt-auto p-4" aria-hidden="true">
                        <p class="es-brush-muted text-[0.6rem] font-extrabold uppercase tracking-[0.18em]">Thursdays</p>
                        <p class="es-brush-ink mt-1 font-mono text-2xl font-black">2pm <span class="es-brush-muted text-sm font-bold">to 5pm, 45 min</span></p>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center" data-reveal>
                <p class="es-brush-muted mx-auto max-w-2xl text-sm">
                    Event Schedule takes nothing out of a ticket price on any plan. You pay Stripe
                    what Stripe charges, and the rest arrives in your own account. There is no seat
                    map and no numbered seats anywhere in the product, so a workshop is places, not
                    positions.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Telling people (06)                                       -->
    <!-- ============================================================ -->
    <section id="tell" class="es-brush-wall scroll-mt-24 es-brush-edge border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-brush-swatch es-brush-swatch-ochre mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-brush-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Telling people</p>
                <h2 class="es-balance es-brush-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    One address, and a way <span class="es-brush-grad">to write to it</span>.
                </h2>
                <p class="es-brush-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Nothing here posts on your behalf. Somebody follows you, and then you decide
                    when there is something worth an email.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">
                @foreach ([
                    ['Free', 'Followers and newsletters', 'Following puts a name and an email on your list, and you write and send the newsletter yourself. Free covers 10 emails a month, Pro 100 and Enterprise 1,000, counted per recipient rather than per send.'],
                    ['Free', 'A QR code for the door', 'Download a QR code that opens your schedule and put it on the show card, the price list, or a card by the door of the studio. On every plan.'],
                    ['Free', 'Embedded in your own site', 'Drop the calendar into the portfolio site you already have. The dates on your site and the dates on your schedule are then the same dates.'],
                    ['Free', 'Google, Outlook and CalDAV', 'Two-way sync, so the install week, the opening and the fair sit in the calendar you actually look at, and a change in either place reaches the other.'],
                    ['Free', 'Who is reading', 'Built-in analytics on your schedule: which shows people opened, and how the page is being found.'],
                    ['Pro', 'A picture to post', 'Generate a shareable graphic from the event\'s own details, with the title, the venue and the date already set, so an announcement does not need a design session.'],
                ] as $ti => [$tPlan, $tTitle, $tBody])
                    <div data-reveal class="es-brush-pinned relative pt-2" style="--tilt: {{ $ti % 3 === 1 ? '0.6deg' : '-0.6deg' }};">
                        <span class="es-brush-pin" aria-hidden="true"></span>
                        <div class="es-brush-card es-brush-hover flex h-full flex-col p-6">
                            <div class="mb-3">
                                <span class="es-brush-plan {{ $tPlan === 'Pro' ? 'es-brush-plan-pro' : 'es-brush-plan-free' }}">{{ $tPlan }}</span>
                            </div>
                            <h3 class="es-brush-ink mb-2 text-lg font-bold">{{ $tTitle }}</h3>
                            <p class="es-brush-muted text-sm">{{ $tBody }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Perfect for (07)                                          -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 es-brush-edge border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-brush-swatch es-brush-swatch-verm mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-brush-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Perfect for</p>
                <h2 class="es-balance es-brush-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Built for every <span class="es-brush-grad">visual medium</span>
                </h2>
                <p class="es-brush-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Whether the work is oil, clay or pixels, the wall is the same wall.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Painters & Illustrators"
                    description="Gallery openings, studio shows and art walks. Publish the dates once and hand out one link for all of them."
                    icon-color="amber"
                    blog-slug="for-painters-illustrators"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Sculptors & Installation Artists"
                    description="Site-specific installations, gallery exhibitions and public unveilings. Tell people where the work actually is."
                    icon-color="slate"
                    blog-slug="for-sculptors-installation-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Photographers"
                    description="Photo exhibitions, gallery talks and portfolio reviews. Openings on the calendar, bookable reviews alongside them."
                    icon-color="sky"
                    blog-slug="for-photographers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Printmakers"
                    description="Print exhibitions, studio sales and edition releases. Put the sale weekend on the wall and let people register for it."
                    icon-color="teal"
                    blog-slug="for-printmakers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Mixed Media & Makers"
                    description="Interdisciplinary shows, pop-ups and collaborations. One page holds an eclectic year without flattening it."
                    icon-color="orange"
                    blog-slug="for-mixed-media-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Digital Artists"
                    description="Screenings, launches and gallery shows. An online event carries the link people join on, next to the ones with an address."
                    icon-color="emerald"
                    blog-slug="for-digital-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Key features                                              -->
    <!-- ============================================================ -->
    <section class="es-brush-edge border-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-brush-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Open studio dates as one entry, with the weekends you are away removed" :url="marketing_url('/features/recurring-events')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Fan Photos & Videos" description="What visitors shot at the opening, held for your approval" :url="marketing_url('/features/fan-videos')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Places at a workshop, with QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="red">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Appointments" description="Bookable studio visits with your own hours and days off" :url="marketing_url('/features/appointments')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="You write it, you send it, to the people who followed you" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-brush-link inline-flex items-center font-medium hover:underline">
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
    <!-- 10. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="es-brush-edge border-t py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-brush-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-art-galleries', 'Art Galleries'],
                    ['/for-dance-groups', 'Dance Groups'],
                    ['/for-circus-acrobatics', 'Circus & Acrobatics'],
                    ['/for-musicians', 'Musicians'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-brush-card es-brush-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-brush-muted text-sm">Event Schedule for</div>
                            <div class="es-brush-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-brush-verm h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-brush-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- 11. FAQ (08)                                                 -->
    <!-- ============================================================ -->
    <section id="faq" class="es-brush-wall scroll-mt-24 es-brush-edge border-t py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-brush-swatch es-brush-swatch-verd mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-brush-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-brush-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked <span class="es-brush-grad">in the studio</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-brush-card es-brush-hover group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-brush-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-brush-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-brush-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    <!-- ============================================================ -->
    <!-- 12. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-brush-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-brush-tag mb-6">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Give the wall <span class="es-brush-grad">an address</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        The page, the dates, the gallery plates and the calendar sync cost nothing.
                        Selling places is five dollars a month, and none of the price comes to us.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-md border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-studio" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-brush-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-md px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Start your wall
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

    <!-- Section dot navigation -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="es-brush-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
