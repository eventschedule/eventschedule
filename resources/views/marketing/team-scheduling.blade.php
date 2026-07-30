<x-marketing-layout>
    <x-slot name="title">Team Scheduling | Invite Members and Set Permissions</x-slot>
    <x-slot name="description">Put other people on your schedule with a named position. Admins run the calendar, viewers read it and scan tickets at the door, and only the owner changes who is on the card.</x-slot>
    <x-slot name="breadcrumbTitle">Team Scheduling</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Team Scheduling",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Invite colleagues onto one schedule with a named position. Admins create and edit events and open the settings page, viewers get read-only access to the schedule and can still scan tickets, and the owner alone changes levels, removes members and holds billing.",
        "featureList": [
            "Invite a member by name and email, with an optional phone number",
            "Three access levels: owner, admin and viewer",
            "Viewers get read-only access to the admin panel and can still scan tickets",
            "Only the owner changes a member's level, removes a member or holds billing",
            "Pending invitations can be resent from the team tab",
            "Per-member notification settings on the same schedule",
            "Each member syncs the schedule to their own Google Calendar",
            "Availability dates per member on a talent schedule, on the Enterprise plan",
            "Audit log of who did what, with time, member, action and detail"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free plan includes one team member. Multiple team members are on the Enterprise plan."
        },
        "url": "{{ url()->current() }}",
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
           Team-scheduling "The Lineup" styles.

           CONCEPT: THE LINEUP CARD. Before anybody plays, somebody
           writes a card: a fixed number of numbered slots, one name per
           slot, and a position code beside each name. That is exactly
           the shape of this product's data - role_user has one row per
           person with a `level` of owner, admin or viewer, and on
           eventschedule.com there are at most five of them
           (RoleController::storeMember caps at 5 when config('app.hosted')).
           So the metaphor and the feature story are one sentence: the
           card is finite, the position decides what the name may do,
           and only the person who wrote the card may change it.

           THE SIGNATURE DEVICE IS A SLOT, NOT A ROSTER OF AVATARS. The
           first-wave page drew a wave of popping avatar circles, which
           says "team!" and nothing else. A slot can be EMPTY, and an
           empty slot is the honest picture of the free plan: one name,
           four hollow rows, and a line saying what opens them. The tier
           truth is drawn rather than disclaimed.

           DELIBERATELY NOT A KEY, A DOOR OR A CREDENTIAL.
           /why-create-account owns engraved key tags ("The Keyring"),
           /ticketing owns the turnstile and /for-nightclubs the steel
           door. Nothing here is described as unlocking anything: a
           position is written on a card, not cut into metal, and the
           material is card stock and hairline rules rather than
           anodised plate.

           NEAREST NEIGHBOUR IS /for-spoken-word ("The Sign-Up Sheet"),
           which also numbers slots down a page, so the two are held
           apart on purpose. That sheet is a fixed ivory paper that
           STRANGERS fill top-down, and its argument is the queue. This
           card is mode-adaptive stock that ONE person writes, its
           argument is the position code beside each name (OW/AD/VW, a
           mark no other page uses), and its slots stay hollow to draw
           the plan limit. Do not import ivory paper, ballpoint/felt-tip
           inks, or a margin numeral column from that page.

           COLOUR: the page keeps its inherited cyan-to-teal family, but
           spent as INK ON CARD rather than as a glowing gradient. Deep
           #0b5f73 on the light card (6.67 on the page ground), bright
           #38d9ee in the dark. Sibling cyan pages get their identity
           from light; this one gets it from ruled paper, tabular
           numerals and two-letter position codes.

           NEVER text-gray-500 here: #6b7280 measures only 4.45 on this
           page's #f3f6f7 ground. Use .es-line-muted (7.34).

           BLADE RULE for this block: no @supports probes - a "#" hex
           inside a parenthesised at-rule condition breaks Blade
           compilation of every later parenthesised directive.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------ */
        .es-line-page { background-color: #f3f6f7; color: #0f1619; }
        .dark .es-line-page { background-color: #070d10; color: #e6eef1; }
        .es-line-ink { color: #0f1619; }
        .dark .es-line-ink { color: #e6eef1; }
        .es-line-muted { color: #4a5257; }
        .dark .es-line-muted { color: #93a3a9; }
        .es-line-accent { color: #0b5f73; }
        .dark .es-line-accent { color: #38d9ee; }
        /* Always-lit accent, for the bands that stay dark in both modes. */
        .es-line-lit { color: #5fe3f5; }

        /* --- Hairline rule ------------------------------------------- */
        .es-line-rule { border-top: 1px solid rgba(15, 22, 25, 0.12); }
        .dark .es-line-rule { border-top-color: rgba(230, 238, 241, 0.13); }

        /* --- Cards --------------------------------------------------- */
        .es-line-card {
            border: 1px solid rgba(15, 22, 25, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-line-card {
            border-color: rgba(230, 238, 241, 0.12);
            background: rgba(230, 238, 241, 0.04);
        }

        /* --- THE LINEUP CARD ----------------------------------------
           Card stock: a ruled sheet with a printed header band, a
           narrow numeral gutter, and one boxed slot per line. Filled
           slots carry a name; open slots are hollow, which is the free
           plan drawn rather than footnoted. ------------------------- */
        .es-line-sheet {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(15, 22, 25, 0.16);
            border-radius: 0.9rem;
            background: #ffffff;
            background-image: linear-gradient(180deg, rgba(11, 95, 115, 0.05) 0%, rgba(11, 95, 115, 0) 42%);
            box-shadow: 0 22px 44px -28px rgba(15, 22, 25, 0.4);
        }
        .dark .es-line-sheet {
            border-color: rgba(230, 238, 241, 0.16);
            background: #0d1417;
            background-image: linear-gradient(180deg, rgba(56, 217, 238, 0.07) 0%, rgba(56, 217, 238, 0) 42%);
            box-shadow: 0 22px 44px -28px rgba(0, 0, 0, 0.8);
        }
        .es-line-sheet-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.85rem 1.1rem;
            border-bottom: 1px solid rgba(15, 22, 25, 0.14);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #0b5f73;
        }
        .dark .es-line-sheet-head { border-bottom-color: rgba(230, 238, 241, 0.14); color: #38d9ee; }

        /* One line of the card. */
        .es-line-slot {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.7rem 1.1rem;
            border-bottom: 1px solid rgba(15, 22, 25, 0.09);
        }
        .dark .es-line-slot { border-bottom-color: rgba(230, 238, 241, 0.09); }
        .es-line-slot:last-child { border-bottom: 0; }
        /* An empty slot: hollow, ruled, waiting for a name. */
        .es-line-slot-open .es-line-name {
            border-bottom: 1px dashed rgba(15, 22, 25, 0.3);
            color: #4a5257;
        }
        .dark .es-line-slot-open .es-line-name {
            border-bottom-color: rgba(230, 238, 241, 0.28);
            color: #93a3a9;
        }

        /* The numeral gutter: tabular, so the column never shifts. */
        .es-line-no {
            flex: none;
            width: 1.5rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 800;
            color: #4a5257;
        }
        .dark .es-line-no { color: #93a3a9; }

        .es-line-name {
            flex: 1 1 auto;
            min-width: 0;
            font-size: 0.9rem;
            font-weight: 700;
            color: #0f1619;
            padding-bottom: 0.1rem;
        }
        .dark .es-line-name { color: #e6eef1; }
        .es-line-sub {
            display: block;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            color: #4a5257;
        }
        .dark .es-line-sub { color: #93a3a9; }

        /* The position code: a two-letter plate, like a position number
           written next to a name on a real card. */
        .es-line-code {
            flex: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.6rem;
            padding: 0.2rem 0.45rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(11, 95, 115, 0.45);
            background: rgba(11, 95, 115, 0.08);
            color: #0b5f73;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.12em;
        }
        .dark .es-line-code {
            border-color: rgba(56, 217, 238, 0.42);
            background: rgba(56, 217, 238, 0.09);
            color: #38d9ee;
        }
        .es-line-code-ad {
            border-color: rgba(14, 107, 98, 0.5);
            background: rgba(14, 107, 98, 0.09);
            color: #0d5f57;
        }
        .dark .es-line-code-ad {
            border-color: rgba(94, 234, 212, 0.4);
            background: rgba(94, 234, 212, 0.09);
            color: #5eead4;
        }
        .es-line-code-vw {
            border-color: rgba(15, 22, 25, 0.3);
            background: rgba(15, 22, 25, 0.05);
            color: #35404a;
        }
        .dark .es-line-code-vw {
            border-color: rgba(230, 238, 241, 0.3);
            background: rgba(230, 238, 241, 0.06);
            color: #c3ced4;
        }

        /* A slow sheen across the sheet, the way light moves over a
           laminated card. Gated: it only runs behind html.es-anim and
           dies entirely under prefers-reduced-motion. */
        .es-line-sheet::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(105deg, transparent 38%, rgba(11, 95, 115, 0.07) 50%, transparent 62%);
            transform: translateX(-30%);
            opacity: 0;
        }
        .dark .es-line-sheet::after {
            background: linear-gradient(105deg, transparent 38%, rgba(56, 217, 238, 0.08) 50%, transparent 62%);
        }
        html.es-anim .es-line-sheet::after {
            opacity: 1;
            animation: es-line-sheen 9s ease-in-out infinite;
        }
        @keyframes es-line-sheen {
            0%, 62% { transform: translateX(-40%); }
            88%, 100% { transform: translateX(40%); }
        }

        /* --- The access table --------------------------------------- */
        .es-line-matrix { width: 100%; border-collapse: collapse; }
        .es-line-matrix th,
        .es-line-matrix td { padding: 0.7rem 0.4rem; vertical-align: middle; }
        .es-line-matrix tbody tr { border-top: 1px solid rgba(15, 22, 25, 0.09); }
        .dark .es-line-matrix tbody tr { border-top-color: rgba(230, 238, 241, 0.09); }
        .es-line-matrix tbody th {
            text-align: start;
            font-size: 0.86rem;
            font-weight: 600;
            color: #0f1619;
        }
        .dark .es-line-matrix tbody th { color: #e6eef1; }
        .es-line-matrix td { text-align: center; width: 4.5rem; }

        /* A slot is either marked or it is not: a filled pip or a
           hollow ring, read the way a scorecard is read. */
        .es-line-pip {
            display: inline-block;
            width: 0.72rem;
            height: 0.72rem;
            border-radius: 9999px;
            background: #0b5f73;
        }
        .dark .es-line-pip { background: #38d9ee; }
        .es-line-pip-off {
            display: inline-block;
            width: 0.72rem;
            height: 0.72rem;
            border-radius: 9999px;
            border: 1.5px solid rgba(15, 22, 25, 0.28);
        }
        .dark .es-line-pip-off { border-color: rgba(230, 238, 241, 0.3); }

        /* --- The log strip ------------------------------------------- */
        .es-line-log {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            width: 100%;
            border-collapse: collapse;
        }
        .es-line-log-row { border-top: 1px solid rgba(15, 22, 25, 0.08); }
        .dark .es-line-log-row { border-top-color: rgba(230, 238, 241, 0.08); }
        .es-line-log-time {
            white-space: nowrap;
            padding: 0.55rem 0.7rem 0.55rem 0;
            font-size: 0.68rem;
            color: #4a5257;
        }
        .dark .es-line-log-time { color: #93a3a9; }
        .es-line-log-who {
            white-space: nowrap;
            padding: 0.55rem 0.7rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: #0f1619;
        }
        .dark .es-line-log-who { color: #e6eef1; }
        .es-line-act {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.1rem 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(15, 22, 25, 0.2);
            font-size: 0.63rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #35404a;
        }
        .dark .es-line-act { border-color: rgba(230, 238, 241, 0.22); color: #c3ced4; }
        .es-line-act-ev { border-color: rgba(11, 95, 115, 0.45); color: #0b5f73; }
        .dark .es-line-act-ev { border-color: rgba(56, 217, 238, 0.42); color: #38d9ee; }
        .es-line-act-sc { border-color: rgba(14, 107, 98, 0.5); color: #0d5f57; }
        .dark .es-line-act-sc { border-color: rgba(94, 234, 212, 0.4); color: #5eead4; }

        /* --- Fixed-dark band ---------------------------------------- */
        .es-line-band {
            /* Pin the INHERITED ink too, not just the painted surfaces: without
               this the band root inherits #0f1619 in light and #e6eef1 in dark
               from .es-line-page, so any text inside that does not set its own
               colour would flip, and the mode-diff probe reports the band as
               non-identical. Set on the band element itself, so there is no
               specificity contest with `.dark .es-line-page`. */
            color: #e6eef1;
            background-color: #0a1114;
            background-image: radial-gradient(120% 100% at 50% 0%, #101c21 0%, #0a1114 55%, #050a0c 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(230, 238, 241, 0.05);
        }
        /* Its descendant overrides live at the FOOT of this block, after every
           `.dark .es-line-*` rule they have to beat. A `.es-line-band .x` and a
           `.dark .x` selector carry the same specificity, so source order is the
           only thing deciding the winner, and a band declared up here loses. */

        /* --- Eyebrow, numeral, plan tag ------------------------------ */
        .es-line-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4a5257;
        }
        .dark .es-line-tag { color: #93a3a9; }

        .es-line-num {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.85rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(15, 22, 25, 0.18);
            background: #ffffff;
            color: #0f1619;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .dark .es-line-num {
            border-color: rgba(230, 238, 241, 0.2);
            background: rgba(230, 238, 241, 0.05);
            color: #e6eef1;
        }
        .es-line-num::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #0b5f73;
        }
        .dark .es-line-num::before { background: #38d9ee; }

        .es-line-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(11, 95, 115, 0.45);
            color: #0b5f73;
        }
        .dark .es-line-plan { border-color: rgba(56, 217, 238, 0.42); color: #38d9ee; }
        .es-line-plan-ent { border-color: rgba(15, 22, 25, 0.35); color: #0f1619; }
        .dark .es-line-plan-ent { border-color: rgba(230, 238, 241, 0.38); color: #e6eef1; }

        /* --- Step marker --------------------------------------------- */
        .es-line-step {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 1.5rem;
            font-weight: 900;
            line-height: 1;
            color: #0b5f73;
        }
        .dark .es-line-step { color: #38d9ee; }

        /* --- Chips (marquee) ----------------------------------------- */
        .es-line-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(15, 22, 25, 0.16);
            background: rgba(255, 255, 255, 0.7);
            color: #4a5257;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-line-chip {
            border-color: rgba(230, 238, 241, 0.16);
            background: rgba(230, 238, 241, 0.05);
            color: #aab7bd;
        }

        /* --- Links and buttons --------------------------------------- */
        .es-line-link { color: #0b5f73; }
        .es-line-link:hover { color: #0f1619; }
        .dark .es-line-link { color: #38d9ee; }
        .dark .es-line-link:hover { color: #e6eef1; }

        .es-line-btn {
            background-color: #0b5f73;
            box-shadow: 0 18px 36px -14px rgba(11, 95, 115, 0.55);
        }
        .es-line-btn:hover { background-color: #084a5a; box-shadow: 0 22px 44px -14px rgba(11, 95, 115, 0.65); }
        /* Dark mode flips the button to bright cyan, so the label has to go dark
           with it. This must be a real rule: an arbitrary Tailwind value like
           dark:text-[#070d10] is not in the built stylesheet and paints nothing. */
        .dark .es-line-btn { background-color: #38d9ee; color: #070d10; }
        .dark .es-line-btn:hover { background-color: #66e6f5; }

        /* --- Dot-nav tooltip ----------------------------------------- */
        .es-line-tip {
            border: 1px solid rgba(15, 22, 25, 0.14);
            background: #ffffff;
            color: #35404a;
        }
        .dark .es-line-tip {
            border-color: rgba(230, 238, 241, 0.14);
            background: #0d1417;
            color: #c3ced4;
        }

        /* --- Hover states on cards / FAQ / related ------------------- */
        .es-line-hover:hover { border-color: rgba(11, 95, 115, 0.45); }
        .dark .es-line-hover:hover { border-color: rgba(56, 217, 238, 0.45); }
        .es-line-hover:hover .es-line-hover-title,
        .es-line-hover:hover .es-line-hover-arrow { color: #0b5f73; }
        .dark .es-line-hover:hover .es-line-hover-title,
        .dark .es-line-hover:hover .es-line-hover-arrow { color: #38d9ee; }

        /* --- Shared-system recolours (brand blue by default) --------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(11, 95, 115, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(56, 217, 238, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(11, 95, 115, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(56, 217, 238, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #0b5f73; }
        .dark .es-dot.is-active .es-dot-pip { background: #38d9ee; }

        /* --- THE BAND'S FIXED INTERIOR ------------------------------
           A band that stays dark in both colour modes has to pin every
           class inside it that would otherwise flip. These rules must sit
           after all the `.dark .es-line-*` rules above: equal specificity
           means last-one-wins, and this is the "last one". Verified with
           the verifier's --bands=.es-line-band flag (0 diffs). ------- */
        .es-line-band .es-line-ink { color: #e6eef1; }
        .es-line-band .es-line-muted { color: #95a5ab; }
        .es-line-band .es-line-card {
            border-color: rgba(230, 238, 241, 0.13);
            background: rgba(230, 238, 241, 0.05);
        }
        .es-line-band .es-line-tag { color: #5fe3f5; }
        .es-line-band .es-line-num {
            border-color: rgba(230, 238, 241, 0.2);
            background: rgba(230, 238, 241, 0.05);
            color: #e6eef1;
        }
        .es-line-band .es-line-num::before { background: #5fe3f5; }
        .es-line-band .es-line-plan { border-color: rgba(95, 227, 245, 0.45); color: #5fe3f5; }
        .es-line-band .es-line-plan-ent { border-color: rgba(230, 238, 241, 0.38); color: #e6eef1; }
        /* The card reappears in the finale, so the whole sheet is pinned too:
           surface #111a1e (ink 15.01, muted 6.93, lit accent 11.59). */
        .es-line-band .es-line-sheet {
            border-color: rgba(230, 238, 241, 0.16);
            background: #111a1e;
            background-image: linear-gradient(180deg, rgba(95, 227, 245, 0.07) 0%, rgba(95, 227, 245, 0) 42%);
            box-shadow: 0 22px 44px -28px rgba(0, 0, 0, 0.8);
        }
        .es-line-band .es-line-sheet::after {
            background: linear-gradient(105deg, transparent 38%, rgba(95, 227, 245, 0.08) 50%, transparent 62%);
        }
        .es-line-band .es-line-sheet-head { border-bottom-color: rgba(230, 238, 241, 0.14); color: #5fe3f5; }
        .es-line-band .es-line-slot { border-bottom-color: rgba(230, 238, 241, 0.09); }
        .es-line-band .es-line-name { color: #e6eef1; }
        .es-line-band .es-line-slot-open .es-line-name {
            border-bottom-color: rgba(230, 238, 241, 0.28);
            color: #95a5ab;
        }
        .es-line-band .es-line-no { color: #95a5ab; }
        .es-line-band .es-line-code {
            border-color: rgba(95, 227, 245, 0.45);
            background: rgba(95, 227, 245, 0.09);
            color: #5fe3f5;
        }
        /* Shared classes carry their own .dark rules in marketing.css, so a
           band that must look identical in both modes has to pin them too. */
        .es-line-band .grid-overlay {
            background-image:
                linear-gradient(rgba(230, 238, 241, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(230, 238, 241, 0.05) 1px, transparent 1px);
        }
        .es-line-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-line-band .es-claim:focus-within {
            border-color: rgba(95, 227, 245, 0.75);
            box-shadow: 0 0 0 4px rgba(95, 227, 245, 0.22);
        }
        /* The finale CTA lives ON the fixed-dark band, so it must not follow the
           page mode: `.dark .es-line-btn` flipped it from deep teal with a white
           label to bright cyan with a dark one, which is a visible change to a
           surface that is the same dark object in both modes. Pinned to the lit
           treatment, which is the one that belongs on a dark ground:
           #070d10 on #38d9ee measures 10.74. The `color` here also drives the
           label span and the arrow SVG, both of which inherit currentColor. */
        .es-line-band .es-line-btn { background-color: #38d9ee; color: #070d10; }
        .es-line-band .es-line-btn:hover { background-color: #66e6f5; }

        /* --- Focus rings. No border-radius here: setting it would change
               the element's own shape on focus. --- */
        #es-line-page a:focus-visible,
        #es-line-page summary:focus-visible,
        #es-line-page button:focus-visible {
            outline: 2px solid #0b5f73;
            outline-offset: 3px;
        }
        .dark #es-line-page a:focus-visible,
        .dark #es-line-page summary:focus-visible,
        .dark #es-line-page button:focus-visible {
            outline-color: #38d9ee;
        }
        .es-line-band a:focus-visible,
        .es-line-band summary:focus-visible,
        .es-line-band button:focus-visible {
            outline-color: #5fe3f5 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            html.es-anim .es-line-sheet::after { animation: none !important; transform: none !important; opacity: 0 !important; }
        }
    </style>

    @php
        // The card itself. Slot 1 is always filled: every schedule has an owner
        // (RoleController stores level 'owner' for the creator). Slots 2 to 5
        // are the hosted Enterprise allowance, capped at 5 in
        // RoleController::storeMember when config('app.hosted') is true.
        $slots = [
            ['You', 'the person who created the schedule', 'OW', ''],
            ['', '', '', 'open'],
            ['', '', '', 'open'],
            ['', '', '', 'open'],
            ['', '', '', 'open'],
        ];

        // Each row is a surface in the admin panel; each column is a level.
        // true = the level can do it, false = it cannot.
        $matrix = [
            ['Open the schedule in the admin panel', true, true, true],
            ['Create, edit and delete events', true, true, false],
            ['Accept or decline event requests', true, true, false],
            ['Open the settings page, and change branding', true, true, false],
            ['Scan tickets at the door', true, true, true],
            ['Read the audit log', true, true, false],
            ['Invite a new member', true, true, false],
            ['Change a member\'s level, or remove somebody', true, false, false],
            ['Change the plan or billing', true, false, false],
            ['Delete the schedule', true, false, false],
        ];

        // An illustration of the audit log: it really does record time,
        // member, action and detail, and really is filterable.
        // 'ev' = an event action, 'sc' = a schedule action, '' = a sale action.
        // These are the real prefixes the audit log groups by.
        $logRows = [
            ['09:12:04', 'Dana R.', 'Event created', 'ev', 'Friday Session'],
            ['09:15:41', 'Dana R.', 'Event published', 'ev', 'Friday Session'],
            ['11:02:19', 'You', 'Member added', 'sc', 'Priya N.'],
            ['14:47:52', 'Priya N.', 'Event updated', 'ev', 'Sunday Matinee'],
            ['19:30:08', 'Sam O.', 'Checked in', '', 'Friday Session'],
        ];

        $faqs = [
            [
                'q' => 'How many team members can I have?',
                'a' => 'On the free plan a schedule has one member, which is you. Adding anybody else needs the Enterprise plan at $15 a month, and on eventschedule.com a team is capped at five members in total. A selfhosted install gets Enterprise features and has no member cap.',
            ],
            [
                'q' => 'What are the access levels?',
                'a' => 'Three: owner, admin and viewer. The owner is whoever created the schedule. An admin can create and edit events, change settings and invite other members. A viewer reads the schedule, the requests, the bookings and the team, and can still scan tickets at the door, but cannot open the settings page at all. Billing, deleting the schedule, and changing or removing another member are controls the Team tab shows to the owner only.',
            ],
            [
                'q' => 'How do I invite somebody?',
                'a' => 'Open the Team tab on your schedule, choose Add Member, and enter their name and email address. A phone number is optional. You pick Admin or Viewer at the same time. They get an email: if they are new they are sent to set a password, and if they already have an Event Schedule account it takes them straight to your schedule.',
            ],
            [
                'q' => 'What if they never accept the invitation?',
                'a' => 'Their row stays on the Team tab with a Resend invite button next to it until they finish signing up, so you can send it again rather than starting over. If you gave a phone number, eventschedule.com can also send the invite as a text, when text messaging is switched on there.',
            ],
            [
                'q' => 'Can two people edit the same schedule?',
                'a' => 'Yes. The owner and every admin work on one calendar rather than on copies of it, so an event added by one of them is the same event everybody else sees. There is no separate approval step between members and no conflict detection between events: the schedule is shared, and the audit log records who changed what.',
            ],
            [
                'q' => 'Does each member get their own settings?',
                'a' => 'Yes, on the same schedule. Notification preferences are stored per member, so one person can take the event-request emails while another takes none. Each member can also sync the schedule into their own Google Calendar from their schedules list. On a talent schedule, which is the type that carries an Availability tab, each member keeps their own unavailable dates on the Enterprise plan, and the whole team sees those days marked on the Schedule tab.',
            ],
            [
                'q' => 'Can somebody leave the team?',
                'a' => 'Yes. A member can remove themselves from a schedule at any time, and the owner can remove anybody else. The owner cannot be removed, which is deliberate: a schedule is never left without one.',
            ],
        ];

        $dotSections = [
            ['top', 'The lineup card'],
            ['seats', 'How many names'],
            ['positions', 'The three positions'],
            ['matrix', 'What each one opens'],
            ['invite', 'Writing a name in'],
            ['kit', 'Their own settings'],
            ['log', 'The log'],
            ['who', 'Who gets a slot'],
            ['faq', 'Questions'],
            ['claim', 'Start the card'],
        ];
    @endphp

    <div id="es-line-page" class="es-line-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the lineup card                                     -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 30%, rgba(11, 95, 115, 0.2), rgba(11, 95, 115, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 74% 42%, rgba(14, 107, 98, 0.16), rgba(14, 107, 98, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-line-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75h10.5M9 12h10.5M9 17.25h10.5M4.5 6.75h.008v.008H4.5V6.75Zm0 5.25h.008v.008H4.5V12Zm0 5.25h.008v.008H4.5v-.008Z" />
                        </svg>
                        <span class="es-line-muted text-sm font-medium tracking-wide">Team scheduling and permissions</span>
                    </div>

                    <h1 class="es-balance es-line-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Running a schedule</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">is a <span class="es-line-accent">named job.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-line-muted mb-8 max-w-xl text-lg sm:text-xl">
                        Put other people on the same schedule and write a position beside each name. Admins run the calendar, viewers read it and still scan tickets at the door, and the owner keeps billing and the last word on positions.
                    </p>

                    <p class="es-fade-up es-d-2 es-line-muted mb-10 max-w-xl text-base">
                        Being straight about it up front: the free plan is one name, and that name is yours. Slots two to five are the Enterprise plan.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#positions" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See the three positions
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-line-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Start for free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The lineup card. Five slots, one filled. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-line-sheet">
                        <div class="es-line-sheet-head">
                            <span>Team</span>
                            <span>5 slots</span>
                        </div>
                        @foreach ($slots as $slotIndex => [$slotName, $slotSub, $slotCode, $slotState])
                            <div class="es-line-slot @if ($slotState === 'open') es-line-slot-open @endif">
                                <span class="es-line-no" aria-hidden="true">{{ str_pad($slotIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                @if ($slotState === 'open')
                                    <span class="es-line-name">&nbsp;</span>
                                    <span class="es-line-plan es-line-plan-ent">Enterprise</span>
                                @else
                                    <span class="es-line-name">
                                        {{ $slotName }}
                                        <span class="es-line-sub">{{ $slotSub }}</span>
                                    </span>
                                    <span class="es-line-code">{{ $slotCode }}</span>
                                @endif
                            </div>
                        @endforeach
                        <p class="es-line-muted es-line-rule px-4 py-3 text-xs">
                            OW is owner. Slots 2 to 5 take an AD (admin) or a VW (viewer). A selfhosted install has no cap on the number of slots.
                        </p>
                    </div>
                </div>
            </div>

            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Venues', 'Curators', 'Bands', 'Festivals', 'Community groups', 'Agencies', 'Theaters', 'Clubs', 'Choirs', 'Schools'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-line-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. How many names fit on the card (fixed-dark band)          -->
    <!-- ============================================================ -->
    <section id="seats" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-line-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-line-num mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-line-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The count</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        How many names fit <span class="es-line-lit">on the card</span>
                    </h2>
                    <p class="es-line-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        This is the part most pages bury. Here it is second, because it decides whether the rest of the page applies to you.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-line-card p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <span class="es-line-plan">Free</span>
                        </div>
                        <p class="es-line-ink mb-2 text-4xl font-black" aria-hidden="true">1</p>
                        <h3 class="es-line-ink mb-2 text-lg font-bold">One member</h3>
                        <p class="es-line-muted text-sm">You. Nothing else on the free plan is metered by head count: unlimited events, calendar sync, analytics, and newsletters to ten recipients a month. The one thing it does not include is a second name on this card.</p>
                    </div>
                    <div class="es-line-card p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <span class="es-line-plan es-line-plan-ent">Enterprise</span>
                        </div>
                        <p class="es-line-ink mb-2 text-4xl font-black" aria-hidden="true">5</p>
                        <h3 class="es-line-ink mb-2 text-lg font-bold">Up to five, in total</h3>
                        <p class="es-line-muted text-sm">Fifteen dollars a month, or a hundred and fifty a year. Five is the whole card, owner included, so it is four other people rather than five. Per-member availability arrives with it, on talent schedules.</p>
                    </div>
                    <div class="es-line-card p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <span class="es-line-plan es-line-plan-ent">Selfhost</span>
                        </div>
                        <p class="es-line-ink mb-2 text-4xl font-black" aria-hidden="true">&infin;</p>
                        <h3 class="es-line-ink mb-2 text-lg font-bold">No cap at all</h3>
                        <p class="es-line-muted text-sm">A selfhosted install gets Enterprise features and skips the five-member limit entirely, because the limit only applies on the hosted service. Your server, your card.</p>
                    </div>
                </div>

                <p class="es-line-muted mt-10 text-center" data-reveal>
                    Not sure which plan you are on?
                    <a href="{{ marketing_url('/pricing') }}" class="es-line-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Compare the plans
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The three positions                                       -->
    <!-- ============================================================ -->
    <section id="positions" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-line-num mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-line-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The positions</p>
                <h2 class="es-balance es-line-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Three positions. <span class="es-line-accent">No custom ones.</span>
                </h2>
                <p class="es-line-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    You are not building a permission scheme. You pick one of three when you write the name in, and you can change it afterwards.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-line-card p-7" data-reveal="panel">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="es-line-code">OW</span>
                        <h3 class="es-line-ink text-lg font-bold">Owner</h3>
                    </div>
                    <p class="es-line-muted mb-4 text-sm">Whoever created the schedule. There is exactly one, and the position cannot be handed over from the Team tab.</p>
                    <ul class="es-line-muted space-y-2 text-sm">
                        <li class="flex gap-2"><span class="es-line-accent flex-none font-bold" aria-hidden="true">+</span><span>Everything an admin can do</span></li>
                        <li class="flex gap-2"><span class="es-line-accent flex-none font-bold" aria-hidden="true">+</span><span>Changes any member's position</span></li>
                        <li class="flex gap-2"><span class="es-line-accent flex-none font-bold" aria-hidden="true">+</span><span>Removes any member</span></li>
                        <li class="flex gap-2"><span class="es-line-accent flex-none font-bold" aria-hidden="true">+</span><span>Holds the plan and billing</span></li>
                        <li class="flex gap-2"><span class="es-line-accent flex-none font-bold" aria-hidden="true">+</span><span>Deletes the schedule, and cannot be removed from it</span></li>
                    </ul>
                </div>

                <div class="es-line-card p-7" data-reveal="panel">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="es-line-code es-line-code-ad">AD</span>
                        <h3 class="es-line-ink text-lg font-bold">Admin</h3>
                    </div>
                    <p class="es-line-muted mb-4 text-sm">The working position. An admin runs the calendar day to day and needs nothing from you to do it.</p>
                    <ul class="es-line-muted space-y-2 text-sm">
                        <li class="flex gap-2"><span class="es-line-accent flex-none font-bold" aria-hidden="true">+</span><span>Creates, edits and deletes events</span></li>
                        <li class="flex gap-2"><span class="es-line-accent flex-none font-bold" aria-hidden="true">+</span><span>Accepts or declines event requests</span></li>
                        <li class="flex gap-2"><span class="es-line-accent flex-none font-bold" aria-hidden="true">+</span><span>Changes settings, branding and sub-schedules</span></li>
                        <li class="flex gap-2"><span class="es-line-accent flex-none font-bold" aria-hidden="true">+</span><span>Invites another member</span></li>
                        <li class="flex gap-2"><span class="es-line-accent flex-none font-bold" aria-hidden="true">+</span><span>Reads the audit log</span></li>
                    </ul>
                </div>

                <div class="es-line-card p-7" data-reveal="panel">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="es-line-code es-line-code-vw">VW</span>
                        <h3 class="es-line-ink text-lg font-bold">Viewer</h3>
                    </div>
                    <p class="es-line-muted mb-4 text-sm">Read-only across the tabs it can reach, with one deliberate exception: a viewer can still work the door.</p>
                    <ul class="es-line-muted space-y-2 text-sm">
                        <li class="flex gap-2"><span class="es-line-accent flex-none font-bold" aria-hidden="true">+</span><span>Sees the schedule, requests, bookings and the team</span></li>
                        <li class="flex gap-2"><span class="es-line-accent flex-none font-bold" aria-hidden="true">+</span><span>Scans tickets and checks people in</span></li>
                        <li class="flex gap-2"><span class="es-line-muted flex-none font-bold" aria-hidden="true">&minus;</span><span>Cannot add or change an event</span></li>
                        <li class="flex gap-2"><span class="es-line-muted flex-none font-bold" aria-hidden="true">&minus;</span><span>Cannot open the settings page at all, or answer requests</span></li>
                        <li class="flex gap-2"><span class="es-line-muted flex-none font-bold" aria-hidden="true">&minus;</span><span>Cannot mark availability dates</span></li>
                    </ul>
                </div>
            </div>

            <p class="es-line-muted mx-auto mt-8 max-w-3xl text-center text-sm" data-reveal>
                A follower is not one of these. Following a schedule is a public action anybody can take on the guest page, and it gives no access to the admin panel at all.
                <a href="{{ marketing_url('/features/newsletters') }}" class="es-line-link font-medium hover:underline">What following actually does</a>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The access table                                          -->
    <!-- ============================================================ -->
    <section id="matrix" class="es-line-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-line-num mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-line-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The card, read across</p>
                <h2 class="es-balance es-line-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    What each position <span class="es-line-accent">actually opens</span>
                </h2>
                <p class="es-line-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Ten surfaces, three columns, and the rows that say no are the useful ones.
                </p>
            </div>

            <div class="es-line-card p-5 sm:p-8" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="es-line-matrix">
                        <caption class="sr-only">What the owner, admin and viewer positions can each do on a schedule</caption>
                        <thead>
                            <tr class="es-line-tag">
                                <th scope="col" class="pb-3 text-start font-bold">Surface</th>
                                <th scope="col" class="pb-3 font-bold">OW</th>
                                <th scope="col" class="pb-3 font-bold">AD</th>
                                <th scope="col" class="pb-3 font-bold">VW</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($matrix as [$rowLabel, $canOwner, $canAdmin, $canViewer])
                                <tr>
                                    <th scope="row">{{ $rowLabel }}</th>
                                    @foreach ([[$canOwner, 'owner'], [$canAdmin, 'admin'], [$canViewer, 'viewer']] as [$cellOn, $cellWho])
                                        <td>
                                            @if ($cellOn)
                                                <span class="es-line-pip" aria-hidden="true"></span>
                                                <span class="sr-only">{{ $cellWho }} can</span>
                                            @else
                                                <span class="es-line-pip-off" aria-hidden="true"></span>
                                                <span class="sr-only">{{ $cellWho }} cannot</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-line-muted es-line-rule mt-5 pt-4 text-xs">
                    A filled dot is yes, a hollow ring is no. The controls for the last three rows only appear on the Team tab for the owner. One row is missing because it needs no column: every name but the owner's can be taken off the card by the person it belongs to, at any time.
                </p>
            </div>

            <p class="es-line-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Worth saying plainly, because other calendars advertise it: there is no conflict detection between events. Nothing warns an admin that two events overlap. What the shared schedule gives you is one calendar instead of several, and a log of who changed it.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Writing a name in                                         -->
    <!-- ============================================================ -->
    <section id="invite" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-line-num mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-line-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The invitation</p>
                <h2 class="es-balance es-line-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Writing a name in <span class="es-line-accent">takes four fields</span>
                </h2>
                <p class="es-line-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    They do not need an Event Schedule account first. If they have one, the invitation attaches to it.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4" data-reveal-group="100">
                @foreach ([
                    ['01', 'Open the Team tab', 'Every schedule has one. Choose Add Member and you get a short form rather than a settings maze.'],
                    ['02', 'Name and email', 'Both required. A phone number is optional and is only used to reach them; nothing else on the form is mandatory.'],
                    ['03', 'Pick Admin or Viewer', 'A single dropdown with two entries, defaulting to Admin. Owner is not in the list, because there is already one.'],
                    ['04', 'They get an email', 'New to Event Schedule? The link sets their password. Already have an account? It takes them straight into your schedule.'],
                ] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-line-card flex flex-col p-7" data-reveal="panel">
                        <div class="es-line-step mb-3">{{ $stepNum }}</div>
                        <h3 class="es-line-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-line-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="es-line-card mt-8 p-6 sm:p-8" data-reveal="panel">
                <div class="grid gap-8 md:grid-cols-2 md:items-center">
                    <div>
                        <h3 class="es-line-ink mb-3 text-xl font-bold">The slot holds their place while you wait</h3>
                        <p class="es-line-muted mb-4">
                            An invited person appears on the Team tab straight away, whether or not they have finished signing up. Until they do, their row carries a Resend invite button instead of a position dropdown, so a lost email costs you one click rather than a re-invite.
                        </p>
                        <p class="es-line-muted mb-4 text-sm">
                            If you supplied a phone number, the same row can offer a second button that sends the invite as a text instead, on eventschedule.com with text messaging switched on.
                        </p>
                        <p class="es-line-muted text-sm">
                            Setting the schedule up in the first place is covered in the
                            <a href="{{ route('marketing.docs.creating_schedules') }}" class="es-line-link font-medium hover:underline">schedules guide</a>.
                        </p>
                    </div>

                    <!-- The card, mid-invitation. -->
                    <div class="es-line-sheet" aria-hidden="true">
                        <div class="es-line-sheet-head">
                            <span>Team</span>
                            <span>3 of 5</span>
                        </div>
                        <div class="es-line-slot">
                            <span class="es-line-no">01</span>
                            <span class="es-line-name">You<span class="es-line-sub">owner</span></span>
                            <span class="es-line-code">OW</span>
                        </div>
                        <div class="es-line-slot">
                            <span class="es-line-no">02</span>
                            <span class="es-line-name">Dana R.<span class="es-line-sub">dana@example.com</span></span>
                            <span class="es-line-code es-line-code-ad">AD</span>
                        </div>
                        <div class="es-line-slot es-line-slot-open">
                            <span class="es-line-no">03</span>
                            <span class="es-line-name">Priya N.<span class="es-line-sub">invited, not signed up yet</span></span>
                            <span class="es-line-plan">Resend</span>
                        </div>
                        <div class="es-line-slot es-line-slot-open">
                            <span class="es-line-no">04</span>
                            <span class="es-line-name">&nbsp;</span>
                        </div>
                        <div class="es-line-slot es-line-slot-open">
                            <span class="es-line-no">05</span>
                            <span class="es-line-name">&nbsp;</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. One schedule, separate settings                           -->
    <!-- ============================================================ -->
    <section id="kit" class="es-line-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-line-num mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-line-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Shared, not merged</p>
                <h2 class="es-balance es-line-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    One schedule. <span class="es-line-accent">Separate settings.</span>
                </h2>
                <p class="es-line-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Sharing a calendar should not mean sharing an inbox. Several things are stored against the member rather than against the schedule.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">

                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-line-card relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-line-ink text-xl font-bold">Each editor picks their own alerts</h3>
                                <span class="es-line-plan">Free</span>
                            </div>
                            <p class="es-line-muted mb-5">
                                Notification preferences live on the membership, not the schedule, so the person who handles bookings can take the request emails and nobody else has to. They go to the owner and to admins; a viewer is never emailed.
                            </p>
                            <div>
                                @foreach ([
                                    ['A new event request lands', 'free, on by default'],
                                    ['Fan photos or video need approving', 'free'],
                                    ['A ticket sells', 'Pro'],
                                    ['Post-event feedback arrives', 'Pro'],
                                    ['Somebody adds a poll option', 'Pro'],
                                ] as [$alertName, $alertNote])
                                    <div class="es-line-rule flex items-baseline justify-between gap-3 py-2">
                                        <span class="es-line-ink text-sm font-semibold">{{ $alertName }}</span>
                                        <span class="es-line-sub">{{ $alertNote }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <p class="es-line-muted es-line-rule mt-4 pt-3 text-xs">
                                The bottom three report on Pro features, and on eventschedule.com their toggles stay greyed out until the schedule fills in its own email settings, which any plan can do. The top two need neither.
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-line-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-line-ink text-xl font-bold">Their calendar, not yours</h3>
                                <span class="es-line-plan">Free</span>
                            </div>
                            <p class="es-line-muted">
                                A member picks Sync to my calendar beside the schedule in their own list, and the events land in the Google Calendar they already look at. Outlook and CalDAV sync sit on the schedule itself, so those run from the owner's account.
                            </p>
                        </div>
                        <p class="es-line-muted relative z-10 mt-auto pt-4 text-sm">
                            <a href="{{ marketing_url('/features/calendar-sync') }}" class="es-line-link font-medium hover:underline">How calendar sync works</a>
                        </p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-line-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-line-ink text-xl font-bold">Days they cannot do</h3>
                                <span class="es-line-plan es-line-plan-ent">Enterprise</span>
                            </div>
                            <p class="es-line-muted">
                                A talent schedule carries an Availability tab, where each member marks their own unavailable dates. Those days turn orange on the shared Schedule tab and name who is out, so nobody pencils in a date the sound engineer already blocked.
                            </p>
                        </div>
                        <p class="es-line-muted relative z-10 mt-auto pt-4 text-sm">
                            <a href="{{ marketing_url('/features/availability') }}" class="es-line-link font-medium hover:underline">More on availability</a>
                        </p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-line-card relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-line-ink text-xl font-bold">Names come off the card too</h3>
                                <span class="es-line-plan">Free</span>
                            </div>
                            <p class="es-line-muted mb-4">
                                A member can remove themselves from a schedule at any time without asking, and the owner can remove anybody else. Removing somebody takes their access away; it does not take away the events they created, which stay on the schedule where they belong.
                            </p>
                            <p class="es-line-muted text-sm">
                                The owner is the one name that cannot be removed. A schedule with nobody on it would be a schedule nobody can fix.
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
    <!-- 7. The log                                                   -->
    <!-- ============================================================ -->
    <section id="log" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-line-num mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                    <p class="es-line-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The log</p>
                    <h2 class="es-balance es-line-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        The card says who is on. <span class="es-line-accent">The log says what they did.</span>
                    </h2>
                    <p class="es-line-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Handing somebody edit access is easier when you can see the consequences. Every schedule keeps an audit log with four columns: when it happened, which member did it, what the action was, and which event or member it touched.
                    </p>
                    <ul class="es-line-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-line-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Events created, updated, published, cancelled and deleted, plus requests accepted or declined.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-line-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Members added and removed, settings changed, and plan changes.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-line-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Ticket checkouts, payments, refunds and check-ins, so a door shift is legible afterwards.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-line-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Filter by category, narrow to a date range, or search the text. Free on every plan, and open to the owner and admins.</span>
                        </li>
                    </ul>
                </div>

                <div data-reveal="panel">
                    <div class="es-line-card p-6 sm:p-7">
                        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-line-ink text-lg font-bold">Audit log</h3>
                            <span class="es-line-sub">Fri 12 Jun</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="es-line-log">
                                <caption class="sr-only">An example day in a schedule's audit log</caption>
                                <thead>
                                    <tr class="es-line-tag">
                                        <th scope="col" class="pb-2 pe-3 text-start font-bold">Time</th>
                                        <th scope="col" class="pb-2 pe-3 text-start font-bold">Member</th>
                                        <th scope="col" class="pb-2 text-start font-bold">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($logRows as [$logTime, $logWho, $logAction, $logKind, $logDetail])
                                        <tr class="es-line-log-row">
                                            <td class="es-line-log-time">{{ $logTime }}</td>
                                            <td class="es-line-log-who">{{ $logWho }}</td>
                                            <td class="py-2">
                                                <span class="es-line-act @if ($logKind === 'ev') es-line-act-ev @elseif ($logKind === 'sc') es-line-act-sc @endif">{{ $logAction }}</span>
                                                <span class="es-line-sub mt-1">{{ $logDetail }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="es-line-muted es-line-rule mt-4 pt-4 text-xs">
                            Illustrative rows. The real log carries every action on the schedule, newest first, fifty to a page.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Who gets a slot                                           -->
    <!-- ============================================================ -->
    <section id="who" class="es-line-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-line-num mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-line-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who gets a slot</p>
                <h2 class="es-balance es-line-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Four names worth <span class="es-line-accent">writing in</span>
                </h2>
                <p class="es-line-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Venues, curators, bands, agencies and community groups all end up with the same short list.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2" data-reveal-group="90">
                @foreach ([
                    ['The other programmer', 'AD', 'es-line-code-ad', 'The person who also books the room. Give them Admin and stop being the bottleneck between a confirmed date and a published one.'],
                    ['Whoever works the door', 'VW', 'es-line-code-vw', 'A viewer can scan tickets and check people in without being able to move a date or edit a price. It is the one thing a read-only position deliberately keeps.'],
                    ['The one who answers the requests', 'AD', 'es-line-code-ad', 'Admins can accept or decline event requests, and each member chooses their own alerts, so the request emails can go to them and only them.'],
                    ['Your cover while you are away', 'AD', 'es-line-code-ad', 'Write them in before the trip, take them off after it. Removing a member revokes their access and leaves everything they added on the schedule.'],
                ] as [$whoName, $whoCode, $whoCodeClass, $whoBody])
                    <div class="es-line-card flex flex-col p-7" data-reveal="panel">
                        <div class="mb-3 flex items-center gap-3">
                            <span class="es-line-code {{ $whoCodeClass }}">{{ $whoCode }}</span>
                            <h3 class="es-line-ink text-lg font-bold">{{ $whoName }}</h3>
                        </div>
                        <p class="es-line-muted text-sm leading-relaxed">{{ $whoBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mx-auto mt-10 grid max-w-3xl gap-3 sm:grid-cols-3" data-reveal-group="70">
                @foreach ([['/for-venues', 'Venues'], ['/for-theaters', 'Theaters'], ['/for-curators', 'Curators']] as [$popHref, $popName])
                    <a href="{{ marketing_url($popHref) }}" class="es-line-hover es-line-card group flex items-center justify-between p-4 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-line-hover-title es-line-ink text-sm font-semibold transition-colors">For {{ $popName }}</span>
                        <svg aria-hidden="true" class="es-line-hover-arrow es-line-muted h-4 w-4 transition-all group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Key features                                              -->
    <!-- ============================================================ -->
    <section class="es-line-rule py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-line-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card
                        name="Availability"
                        description="Each member marks the dates they cannot do, shared on one calendar"
                        :url="marketing_url('/features/availability')"
                        icon-color="teal"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Sub-schedules"
                        description="Group events into strands within one schedule"
                        :url="marketing_url('/features/sub-schedules')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Calendar Sync"
                        description="Each member can sync the shared schedule into their own Google Calendar"
                        :url="marketing_url('/features/calendar-sync')"
                        icon-color="emerald"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Ticketing"
                        description="QR check-in that a viewer on the door can run"
                        :url="marketing_url('/features/ticketing')"
                        icon-color="green"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Custom Fields"
                        description="Extra questions on the event request form, answered before an admin sees the row"
                        :url="marketing_url('/features/custom-fields')"
                        icon-color="amber"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Online Events"
                        description="One link field on the event, which any admin on the card can update"
                        :url="marketing_url('/features/online-events')"
                        icon-color="rose"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-line-link inline-flex items-center font-medium hover:underline">
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

    <section id="faq" class="es-line-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-line-num mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <h2 class="es-balance es-line-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-line-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Everything people ask before they hand somebody else the calendar.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-line-hover es-line-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-line-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-line-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-line-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-line-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-line-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>

            <p class="es-line-muted mt-8 text-center text-sm" data-reveal>
                Step-by-step instructions live in the guide:
                <a href="{{ route('marketing.docs.managing_schedules') }}#team" class="es-line-link font-medium hover:underline">managing your team</a>.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-line-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-line-tag mb-4">Slot one is free</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Start the card with <span class="es-line-lit">your own name.</span>
                    </h2>
                    <p class="es-line-muted mx-auto mb-10 max-w-2xl text-lg">
                        Build the schedule first. The other four slots are there when the job outgrows one person, and nothing about the calendar changes when you add them.
                    </p>

                    {{-- The card one last time, empty: slot 01 is the name you are about to
                         write, and 02 is what the plan opens. Pinned to the band's fixed
                         dark surface by the .es-line-band overrides at the foot of the
                         style block, so it renders identically in both colour modes. --}}
                    <div class="mx-auto mb-10 max-w-xs text-start">
                        <div class="es-line-sheet" aria-hidden="true">
                            <div class="es-line-sheet-head">
                                <span>Team</span>
                                <span>0 of 5</span>
                            </div>
                            <div class="es-line-slot es-line-slot-open">
                                <span class="es-line-no">01</span>
                                <span class="es-line-name">&nbsp;</span>
                                <span class="es-line-code">OW</span>
                            </div>
                            <div class="es-line-slot es-line-slot-open">
                                <span class="es-line-no">02</span>
                                <span class="es-line-name">&nbsp;</span>
                                <span class="es-line-plan es-line-plan-ent">Enterprise</span>
                            </div>
                        </div>
                    </div>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-team" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-line-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-line-muted mt-6 text-sm">No credit card required</p>
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
                        <span class="es-line-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
