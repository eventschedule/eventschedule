<x-marketing-layout>
    <x-slot name="title">Newsletter Builder for Events - Event Schedule</x-slot>
    <x-slot name="description">Send branded newsletters to followers and ticket buyers. Drag-and-drop editor, professional templates, audience segments, and delivery analytics.</x-slot>
    <x-slot name="breadcrumbTitle">Newsletters</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Newsletters",
        "description": "Send branded newsletters to followers and ticket buyers. Drag-and-drop editor, professional templates, audience segments, A/B testing, and delivery analytics.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Email Marketing"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Newsletters",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Email Marketing Software",
        "operatingSystem": "Web",
        "description": "Compose a newsletter from fourteen block types, choose who receives it, and send it yourself. Nothing is emailed to your followers automatically. Monthly allowances count recipients rather than sends.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "The newsletter builder is included on the free plan, with 10 recipients per month"
        },
        "featureList": [
            "Nothing is sent to followers automatically: you compose and send every newsletter",
            "Fourteen block types, dragged into order, cloned or deleted",
            "An events block that pulls your upcoming events into the email",
            "Five built-in templates, plus templates you save yourself",
            "Colours, five email-safe fonts, button shape and event layout",
            "Segments: all followers, ticket buyers, a manual list, sub-schedule buyers, waitlist",
            "Combined segments merged and deduplicated by email address",
            "Import addresses by form, paste, or CSV upload",
            "Preview in the browser and send a test to yourself",
            "Send now or schedule a date and time",
            "Open and click tracking per recipient, with top links",
            "A/B test the subject line or the content on a sample of the list",
            "One-click unsubscribe headers on every message",
            "Monthly allowance counts recipients: 10 free, 100 on Pro, 1,000 on Enterprise",
            "No limit when the schedule uses its own SMTP server, or on a selfhosted install"
        ],
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
           For-newsletters "The Send" styles.

           CONCEPT: the franking counter in a mailroom. Nothing leaves the
           building on its own. Somebody writes the letter, decides who it
           goes to, feeds it in and presses the lever - and the meter
           charges BY THE ENVELOPE, not by the letter that was written.

           Both halves of that object are the product, exactly:

             1. There is no automatic follower notification anywhere in
                this codebase. No job, no mailable, no scheduled command.
                The only follower-facing mailable is NewsletterEmail, and
                NewsletterController::send() runs from a button a human
                pressed. (The two automatic emails that DO exist run the
                other way: EventChangeNotifier tells TICKET BUYERS when
                their event changes, and NotifyRequestChanges emails the
                OWNER when a booking request lands.) So "nothing sends
                itself" is a literal statement about the code, and it is
                the spine of this page.

             2. Role::newsletterLimit() caps RECIPIENTS, not sends.
                NewsletterService::send() rejects the send when
                newslettersSentThisMonth() + $recipients->count() > limit,
                and newslettersSentThisMonth() sums sent_count. A
                newsletter to 40 followers spends 40. That unit has
                silently regressed on this site before, so the page states
                it as arithmetic and repeats the arithmetic three ways in
                a register the numbers are computed from.

           DEVICES: the outgoing docket (hero), the empty automatic tray
           set against the letter you wrote (01), the postage register
           plus a real <table> of allowances (02), the addressed list
           (03), the letter sheet folded into thirds (04), and a real
           <table> of per-recipient returns (05). The section marks are
           franked dies with a cancellation bar.

           NOT USED, deliberately: the first-wave version of this page ran
           three full-bleed layers of flowing dashes ("emails in flight").
           That reads as network chrome, says nothing true, and collided
           with /features/calendar-sync's sync pulse. It is gone. Nor an
           envelope OBJECT for 04: /gift-cards owns the flapped envelope
           (.es-gift-env), and 04 is the letter, not the postage. The word
           "envelope" still carries the allowance argument, which is the
           only place it belongs.

           COLOUR: the page keeps its inherited sky/cyan hue family, but
           pushed off the shared brand chrome (#4E81FA -> #0EA5E9 ->
           #22D3EE) into deep petrol ink, which is a mailroom machine
           rather than a product gradient. #0b5c78 is 194deg at 29%
           lightness against the chrome's 199deg at 47%. Not teal
           (for-djs, ~174deg) and not the bright sky the audience pages
           use as a highlight.

           MEASURED (never text-gray-500 on this tinted ground):
             light  ground #f2f5f7 | ink #111a20 16.09 | muted #4b5158
                    7.33 / 7.82 card / 6.74 sub | accent #0b5c78 6.81 /
                    7.26 card | heading stops #0a4d64 8.49 and #14738f
                    4.95 / 5.27 card | white on the #0b5c78 button 7.46 |
                    ink on the #cfd8dd register digit 12.17
             dark   ground #0b1014 | ink #e8eef2 16.33 | muted #a3aeb8
                    8.46 / 7.70 card / 6.86 sub | accent #7fd0ea 10.92 /
                    9.94 card | white on the #12708f button 5.62 | ink on
                    the #1b232a register digit 13.60
             band   (identical in both modes, ground #0c1318) white 18.72
                    | dim #a3aeb8 8.29 / 7.20 inner panel | lit #7fd0ea
                    10.71
           ============================================================== */

        /* --- Ground and ink ---------------------------------------- */
        .es-send-page { background-color: #f2f5f7; color: #111a20; }
        .dark .es-send-page { background-color: #0b1014; color: #e8eef2; }
        .es-send-ink { color: #111a20; }
        .dark .es-send-ink { color: #e8eef2; }
        .es-send-muted { color: #4b5158; }
        .dark .es-send-muted { color: #a3aeb8; }
        .es-send-accent { color: #0b5c78; }
        .dark .es-send-accent { color: #7fd0ea; }
        /* Always-lit pair, for the bands that do not change between modes. */
        .es-send-lit { color: #7fd0ea; }
        .es-send-dim { color: #a3aeb8; }

        .es-send-grad {
            background-image: linear-gradient(102deg, #0a4d64, #14738f);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-send-grad,
        .es-send-band .es-send-grad {
            background-image: linear-gradient(102deg, #a8e2f4, #6fc8e6);
        }

        /* --- Surfaces ---------------------------------------------- */
        .es-send-card {
            /* --es-ring-radius so the shared .es-ring-glow hover ring follows
               this frame instead of its 1.5rem default. */
            --es-ring-radius: 0.7rem;
            background-color: #fbfcfd;
            border: 1px solid rgba(17, 26, 32, 0.12);
            border-radius: 0.7rem;
        }
        .dark .es-send-card {
            background-color: #141b21;
            border-color: rgba(232, 238, 242, 0.13);
        }
        .es-send-sub {
            background-color: #e7ecef;
            border-radius: 0.45rem;
        }
        .dark .es-send-sub { background-color: #1c2530; }
        .es-send-tint {
            background-color: #e2ebef;
            border: 1px solid rgba(11, 92, 120, 0.24);
            border-radius: 0.7rem;
        }
        .dark .es-send-tint {
            background-color: #12242e;
            border-color: rgba(127, 208, 234, 0.26);
        }
        .es-send-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-send-hover:hover {
            border-color: rgba(11, 92, 120, 0.45);
            box-shadow: 0 12px 30px -20px rgba(17, 26, 32, 0.55);
        }
        .dark .es-send-hover:hover {
            border-color: rgba(127, 208, 234, 0.42);
            box-shadow: 0 12px 30px -20px rgba(0, 0, 0, 0.85);
        }
        /* Rules and dividers are page-local rather than arbitrary-value
           Tailwind borders: this campaign never runs a build, so a
           border-[rgba(17,26,32,0.1)] that is not already in the built
           marketing-app CSS paints nothing at all. */
        .es-send-rule-t { border-top: 1px solid rgba(17, 26, 32, 0.1); }
        .dark .es-send-rule-t { border-top-color: rgba(232, 238, 242, 0.1); }
        .es-send-split > * + * { border-top: 1px solid rgba(17, 26, 32, 0.1); }
        .dark .es-send-split > * + * { border-top-color: rgba(232, 238, 242, 0.1); }
        .es-send-micro { font-size: 0.64rem; }

        /* The dot-nav tooltip in the shared markup reaches for a hex
           background, so its surface is defined here instead. */
        .es-send-tip {
            background-color: #ffffff;
            border: 1px solid rgba(17, 26, 32, 0.14);
            color: #111a20;
        }
        .dark .es-send-tip {
            background-color: #141b21;
            border-color: rgba(232, 238, 242, 0.14);
            color: #e8eef2;
        }

        /* --- Eyebrow and numerals ---------------------------------- */
        .es-send-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #0b5c78;
        }
        .dark .es-send-tag { color: #7fd0ea; }
        .es-send-band .es-send-tag { color: #7fd0ea; }

        .es-send-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- The franked die: the section mark ---------------------
           A stamp with the cancellation bars struck across the top. The
           bars are a repeating gradient, not a drawing. */
        .es-send-die {
            position: relative;
            display: inline-flex;
            align-items: flex-end;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            padding-bottom: 0.4rem;
            border: 1px solid rgba(17, 26, 32, 0.22);
            border-radius: 0.3rem;
            background-color: rgba(17, 26, 32, 0.035);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 700;
            color: #111a20;
        }
        .dark .es-send-die {
            border-color: rgba(232, 238, 242, 0.22);
            background-color: rgba(232, 238, 242, 0.05);
            color: #e8eef2;
        }
        .es-send-band .es-send-die {
            border-color: rgba(232, 238, 242, 0.22);
            background-color: rgba(232, 238, 242, 0.05);
            color: #e8eef2;
        }
        .es-send-die::before {
            content: "";
            position: absolute;
            top: 0.42rem;
            left: 0.4rem;
            right: 0.4rem;
            height: 0.42rem;
            background-image: repeating-linear-gradient(90deg, #0b5c78 0 2px, transparent 2px 5px);
            opacity: 0.8;
        }
        .dark .es-send-die::before,
        .es-send-band .es-send-die::before {
            background-image: repeating-linear-gradient(90deg, #7fd0ea 0 2px, transparent 2px 5px);
        }

        /* --- The register: a postage meter counts pieces ----------- */
        .es-send-reg { display: inline-flex; gap: 0.3rem; }
        .es-send-digit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.2rem;
            padding: 0.5rem 0.35rem;
            border-radius: 0.28rem;
            /* A resolvable colour under the gradient, so the digit's ink is
               scored against the drum it actually sits on (13.17) rather than
               against whatever card is behind it. */
            background-color: #d8e0e4;
            background-image: linear-gradient(180deg, #eef2f4 0%, #cfd8dd 47%, #e3eaed 53%, #d4dde1 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75), inset 0 -1px 0 rgba(17, 26, 32, 0.14);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1;
            color: #111a20;
        }
        .dark .es-send-digit {
            background-color: #222b32;
            background-image: linear-gradient(180deg, #2c3740 0%, #1b232a 47%, #263038 53%, #1f272e 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08), inset 0 -1px 0 rgba(0, 0, 0, 0.5);
            color: #e8eef2;
        }

        .es-send-bar {
            height: 0.5rem;
            border-radius: 999px;
            background-color: rgba(17, 26, 32, 0.12);
            overflow: hidden;
        }
        .dark .es-send-bar { background-color: rgba(232, 238, 242, 0.14); }
        .es-send-fill {
            height: 100%;
            border-radius: 999px;
            background-image: linear-gradient(90deg, #0b5c78, #14738f);
        }
        .dark .es-send-fill { background-image: linear-gradient(90deg, #4fb2d4, #7fd0ea); }

        /* --- The franking impression on the docket ----------------- */
        .es-send-stamp {
            position: relative;
            border: 1px dashed rgba(11, 92, 120, 0.5);
            border-radius: 0.4rem;
            background-color: rgba(11, 92, 120, 0.07);
            padding: 0.85rem 1rem 0.85rem 1.6rem;
        }
        .dark .es-send-stamp {
            border-color: rgba(127, 208, 234, 0.45);
            background-color: rgba(127, 208, 234, 0.09);
        }
        .es-send-stamp::before {
            content: "";
            position: absolute;
            top: 0.5rem;
            bottom: 0.5rem;
            left: 0.6rem;
            width: 0.3rem;
            background-image: repeating-linear-gradient(180deg, #0b5c78 0 2px, transparent 2px 5px);
            opacity: 0.8;
        }
        .dark .es-send-stamp::before {
            background-image: repeating-linear-gradient(180deg, #7fd0ea 0 2px, transparent 2px 5px);
        }

        /* --- The empty tray: what leaves on its own ---------------- */
        .es-send-void {
            border: 1px dashed rgba(17, 26, 32, 0.28);
            border-radius: 0.6rem;
            background-color: rgba(17, 26, 32, 0.03);
            background-image: repeating-linear-gradient(135deg, rgba(17, 26, 32, 0.05) 0 6px, transparent 6px 14px);
        }
        .dark .es-send-void {
            border-color: rgba(232, 238, 242, 0.24);
            background-color: rgba(232, 238, 242, 0.02);
            background-image: repeating-linear-gradient(135deg, rgba(232, 238, 242, 0.05) 0 6px, transparent 6px 14px);
        }

        /* --- The letter: one sheet, folded into thirds -------------
           Deliberately NOT an envelope object. /gift-cards owns the
           flapped envelope (.es-gift-env, a clip-path flap folded over
           the card) and a second one here would be house furniture.
           This section is the LETTER anyway, which is the whole point
           of the page: you write ONE of these, and it becomes forty-two
           envelopes. The two creases are where a sheet folds to fit a
           business envelope, drawn as soft gradient shadows rather than
           an outline. */
        .es-send-sheet {
            --es-ring-radius: 0.3rem;
            position: relative;
            padding: 1.6rem 1.3rem 1.4rem;
            border: 1px solid rgba(17, 26, 32, 0.13);
            border-radius: 0.3rem;
            background-color: #fdfefe;
            box-shadow: 0 22px 46px -34px rgba(17, 26, 32, 0.55);
        }
        .dark .es-send-sheet {
            border-color: rgba(232, 238, 242, 0.12);
            background-color: #171f26;
            box-shadow: 0 22px 46px -30px rgba(0, 0, 0, 0.9);
        }
        .es-send-sheet::before,
        .es-send-sheet::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            height: 0.45rem;
            pointer-events: none;
            background-image: linear-gradient(180deg, rgba(17, 26, 32, 0.1), rgba(17, 26, 32, 0));
        }
        .dark .es-send-sheet::before,
        .dark .es-send-sheet::after {
            background-image: linear-gradient(180deg, rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0));
        }
        .es-send-sheet::before { top: 33.33%; }
        .es-send-sheet::after { top: 66.66%; }

        /* --- Block-type chips ------------------------------------- */
        .es-send-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(17, 26, 32, 0.14);
            background-color: rgba(17, 26, 32, 0.035);
            padding: 0.2rem 0.65rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: #111a20;
        }
        .dark .es-send-chip {
            border-color: rgba(232, 238, 242, 0.16);
            background-color: rgba(232, 238, 242, 0.05);
            color: #e8eef2;
        }

        /* --- Plan pills. Tiers ONLY, never a state badge. ---------- */
        .es-send-plan {
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
        .es-send-plan-free { border-color: rgba(17, 26, 32, 0.24); color: #4b5158; }
        .dark .es-send-plan-free { border-color: rgba(232, 238, 242, 0.26); color: #a3aeb8; }
        .es-send-plan-pro {
            border-color: rgba(11, 92, 120, 0.5);
            background-color: rgba(11, 92, 120, 0.09);
            color: #0b5c78;
        }
        .dark .es-send-plan-pro {
            border-color: rgba(127, 208, 234, 0.42);
            background-color: rgba(127, 208, 234, 0.1);
            color: #7fd0ea;
        }

        /* --- Records: two real tables ------------------------------ */
        .es-send-wrap { overflow-x: auto; }
        .es-send-table {
            width: 100%;
            min-width: 32rem;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        .es-send-table caption { text-align: start; }
        .es-send-table th,
        .es-send-table td {
            padding: 0.7rem 0.85rem;
            text-align: start;
            border-bottom: 1px solid rgba(17, 26, 32, 0.1);
            vertical-align: top;
        }
        .dark .es-send-table th,
        .dark .es-send-table td { border-bottom-color: rgba(232, 238, 242, 0.1); }
        .es-send-table thead th {
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #4b5158;
            white-space: nowrap;
        }
        .dark .es-send-table thead th { color: #a3aeb8; }
        .es-send-table tbody th { font-weight: 700; color: #111a20; white-space: nowrap; }
        .dark .es-send-table tbody th { color: #e8eef2; }
        .es-send-table td { color: #4b5158; }
        .dark .es-send-table td { color: #a3aeb8; }
        .es-send-table tfoot th,
        .es-send-table tfoot td {
            border-bottom: none;
            border-top: 2px solid rgba(11, 92, 120, 0.35);
            color: #111a20;
            font-weight: 700;
        }
        .dark .es-send-table tfoot th,
        .dark .es-send-table tfoot td {
            border-top-color: rgba(127, 208, 234, 0.4);
            color: #e8eef2;
        }

        /* --- Buttons ---------------------------------------------- */
        .es-send-btn {
            background-color: #0b5c78;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-send-btn:hover {
            background-color: #094c63;
            transform: translateY(-1px);
            box-shadow: 0 14px 28px -16px rgba(11, 92, 120, 0.9);
        }
        .dark .es-send-btn { background-color: #12708f; }
        .dark .es-send-btn:hover { background-color: #0b5c78; }
        .es-send-ghost {
            border: 1px solid rgba(17, 26, 32, 0.22);
            color: #111a20;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-send-ghost:hover { border-color: rgba(11, 92, 120, 0.5); background-color: rgba(11, 92, 120, 0.07); }
        .dark .es-send-ghost { border-color: rgba(232, 238, 242, 0.24); color: #e8eef2; }
        .dark .es-send-ghost:hover { border-color: rgba(127, 208, 234, 0.45); background-color: rgba(127, 208, 234, 0.09); }

        /* --- The bands: one physical machine, identical in both modes
           A resolvable background-color sits under the gradient so text
           over it is scored against something real. */
        .es-send-band {
            background-color: #0c1318;
            background-image:
                radial-gradient(ellipse 70% 55% at 50% 0%, rgba(11, 92, 120, 0.45), rgba(11, 92, 120, 0) 70%),
                linear-gradient(180deg, #101a20, #0c1318);
        }
        /* Nothing inside a band may change between colour modes. These
           three shared classes carry their own .dark rules in
           marketing.css and are invisible to a grep of this file. */
        .es-send-band .grid-overlay {
            background-image:
                linear-gradient(rgba(232, 238, 242, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(232, 238, 242, 0.05) 1px, transparent 1px);
        }
        .es-send-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-send-band .es-claim:focus-within {
            border-color: rgba(127, 208, 234, 0.75);
            box-shadow: 0 0 0 4px rgba(127, 208, 234, 0.22);
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(11, 92, 120, 0.65); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(127, 208, 234, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #0b5c78; }
        .dark .es-dot.is-active .es-dot-pip { background: #7fd0ea; }

        /* The one animation on the page: the ready lamp on the docket.
           Never set border-radius on a focus outline; an outline already
           follows the element's own radius. */
        .es-send-pulse {
            display: inline-block;
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 999px;
            background-color: #0b5c78;
            animation: es-send-lamp 2.6s ease-in-out infinite;
        }
        .dark .es-send-pulse { background-color: #7fd0ea; }
        @keyframes es-send-lamp {
            0%, 100% { opacity: 0.45; }
            50% { opacity: 1; }
        }

        #es-send-page a:focus-visible,
        #es-send-page summary:focus-visible,
        #es-send-page button:focus-visible,
        #es-send-page input:focus-visible {
            outline: 2px solid #0b5c78;
            outline-offset: 2px;
        }
        .dark #es-send-page a:focus-visible,
        .dark #es-send-page summary:focus-visible,
        .dark #es-send-page button:focus-visible,
        .dark #es-send-page input:focus-visible {
            outline-color: #7fd0ea;
        }
        .es-send-band a:focus-visible,
        .es-send-band summary:focus-visible,
        .es-send-band button:focus-visible,
        .es-send-band input:focus-visible {
            outline-color: #7fd0ea !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-send-pulse { animation: none; opacity: 1; }
            .es-send-btn:hover { transform: none; }
        }
    </style>

    @php
        // ONE send, and every figure on the page is derived from it, so the
        // docket, the register and the returns table cannot drift apart.
        // The arithmetic is the argument: envelopes == recipients.
        $send = [
            'subject'    => 'Three nights in November',
            'list'       => 'All followers',
            'recipients' => 42,
            'blocks'     => 6,
            'template'   => 'Modern',
            'allowance'  => 100,
        ];
        $usedPct = (int) round($send['recipients'] / $send['allowance'] * 100);
        $register = str_pad((string) $send['recipients'], 4, '0', STR_PAD_LEFT);

        // Three ways to spend the same allowance. Each pair multiplies out
        // in the label, so a reader can check the unit for themselves.
        $spends = [
            [1, 40, 'One newsletter to forty followers.'],
            [10, 4, 'Ten newsletters to four people each.'],
            [4, 25, 'Four newsletters to twenty-five people. A whole Pro month.'],
        ];

        // Role::newsletterLimit(). Null means no cap at all.
        $allowances = [
            ['Free', '10', 'The whole builder: blocks, templates, segments, scheduling, tracking, A/B tests.'],
            ['Pro, '.plan_price($proMonthly).' a month', '100', 'Also lets you upload image files into a newsletter rather than linking to them.'],
            ['Enterprise', '1,000', 'For a schedule sending to a large list every month.'],
            ['Your own SMTP', 'No limit', 'Point the schedule at your own mail server and the monthly cap comes off.'],
            ['Selfhosted', 'No limit', 'Your install, your mail service, no counting.'],
        ];

        // A sent newsletter's per-recipient record: NewsletterRecipient rows
        // carry sent_at, opened_at, open_count, clicked_at, click_count.
        // Fictional people on example.com; a real list is never public.
        $returns = [
            ['Dana Whitlock', 'dana@example.com', 'Nov 3, 9:02am', 'Nov 3, 9:14am', '2 links'],
            ['Marco Oyelaran', 'marco@example.com', 'Nov 3, 9:02am', 'Nov 3, 10:40am', null],
            ['Priya Raman', 'priya@example.com', 'Nov 3, 9:02am', null, null],
            ['Sam Beckley', 'sam@example.com', 'Nov 3, 9:03am', 'Nov 3, 9:07am', '1 link'],
            ['Ada Nkemelu', 'ada@example.com', 'Nov 3, 9:03am', null, null],
        ];
        $totals = ['delivered' => 42, 'opened' => 18, 'clickers' => 7];
        $openPct = (int) round($totals['opened'] / $totals['delivered'] * 100);
        $clickPct = (int) round($totals['clickers'] / $totals['delivered'] * 100);

        // The fourteen block types a schedule's builder offers, in the order it
        // lists them: _builder.blade.php availableBlockTypes (the non-admin arm).
        // 'Offer' is on the admin arm only, so it is deliberately not here.
        $blockTypes = [
            'Heading', 'Text', 'Events', 'Button', 'Image', 'Divider', 'Spacer',
            'Social links', 'Profile image', 'Header banner', 'Video',
            'Quote', 'Sponsors', 'Poll',
        ];

        $faqs = [
            [
                'q' => 'How do subscribers join my newsletter?',
                'a' => 'Visitors can follow your schedule directly from your public schedule page, and you can print or display the follower QR code, which is free on every plan. You can also target ticket buyers and manually add email addresses, one at a time, pasted in bulk, or uploaded as a CSV. All subscribers can unsubscribe with one click.',
            ],
            [
                'q' => 'Does adding an event email my followers?',
                'a' => 'No. Nothing is emailed to your followers automatically. There is no digest job and no new-event alert: you compose a newsletter, choose who receives it, and send it. The one automatic email in this area goes to ticket buyers, who are told when an event they bought for changes or is cancelled.',
            ],
            [
                'q' => 'How many newsletters can I send?',
                'a' => 'The limits count recipients rather than sends: a newsletter to 100 followers uses 100 of the monthly allowance. The free plan includes 10 newsletter emails per month per schedule, the Pro plan increases this to 100, and Enterprise to 1,000. A selfhosted install has no limit, and neither does a schedule that sends through its own SMTP server.',
            ],
            [
                'q' => 'Is the newsletter builder a paid feature?',
                'a' => 'No. The builder is on the free plan, and so are segments, the five templates, scheduling a send, test sends, open and click tracking, and A/B tests. A paid plan buys you more recipients per month, and Pro adds image file uploads inside a newsletter. An image URL works on any plan.',
            ],
            [
                'q' => 'How does email deliverability work?',
                'a' => 'Event Schedule handles email delivery infrastructure for you, and every message carries one-click unsubscribe headers, a bulk precedence header and a reply-to address pointing at your schedule. Any schedule can also configure its own SMTP server for maximum deliverability and branding control, which removes the monthly recipient limit.',
            ],
            [
                'q' => 'Can I see who opened it?',
                'a' => 'Yes. Every recipient gets their own tracked record, so the stats page shows who was delivered to, who opened, who clicked and which links they clicked, along with the top ten links and when opens and clicks arrived. You can see your followers by name and email address on your own screens; your public page never lists them.',
            ],
            [
                'q' => 'Can I test a newsletter before it goes out?',
                'a' => 'Yes. Preview it in the browser at any point, and send a test to your schedule\'s own email address to check how it renders in a real inbox. A test does not come off your monthly allowance. When you are happy, send it now or pick a date and time and let it go out on its own.',
            ],
        ];

        $dotSections = [
            ['top', 'The send'],
            ['nothing', 'Nothing sends itself'],
            ['meter', 'The allowance'],
            ['list', 'The list'],
            ['letter', 'The letter'],
            ['receipt', 'The returns'],
            ['run', 'How a send runs'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-send-page" class="es-send-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the outgoing docket                                 -->
    <!-- ============================================================ -->
    {{-- The nav overlays the top of the page, so the hero carries extra top
         padding rather than letting the docket sit under it. --}}
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 26% 30%, rgba(11, 92, 120, 0.22), rgba(11, 92, 120, 0) 62%); opacity: 0.55;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 76% 64%, rgba(127, 208, 234, 0.16), rgba(127, 208, 234, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:72px_72px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="es-send-tag es-fade-up es-d-1 mb-5">Newsletters</p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Nothing goes out</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">until <span class="es-send-grad">you send it</span>.</span></span>
                    </h1>

                    <p class="es-send-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        Your followers never get mail from this platform in your name. Adding a show
                        notifies nobody. A follow is permission to be written to, and you are the one
                        who writes: compose it, pick the list, press send.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-send-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="{{ route('marketing.docs.newsletters') }}" class="es-send-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Read the Newsletters guide
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The docket: one job on the counter, waiting for a person. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-send-card p-6 sm:p-8">
                        <div class="mb-5 flex items-center justify-between gap-3">
                            <p class="es-send-tag">Outgoing</p>
                            <span class="es-send-muted es-send-micro inline-flex items-center gap-2 font-bold uppercase tracking-[0.16em]">
                                <span class="es-send-pulse" aria-hidden="true"></span>
                                Waiting for you
                            </span>
                        </div>

                        <p class="es-send-ink mb-6 text-2xl font-black leading-tight sm:text-3xl">{{ $send['subject'] }}</p>

                        <dl class="es-send-split">
                            <div class="flex items-baseline justify-between gap-4 py-2.5">
                                <dt class="es-send-muted text-sm">List</dt>
                                <dd class="es-send-ink text-sm font-semibold">{{ $send['list'] }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4 py-2.5">
                                <dt class="es-send-muted text-sm">Recipients</dt>
                                <dd class="es-send-ink es-send-num text-sm font-semibold">{{ $send['recipients'] }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4 py-2.5">
                                <dt class="es-send-muted text-sm">Blocks</dt>
                                <dd class="es-send-ink es-send-num text-sm font-semibold">{{ $send['blocks'] }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4 py-2.5">
                                <dt class="es-send-muted text-sm">Template</dt>
                                <dd class="es-send-ink text-sm font-semibold">{{ $send['template'] }}</dd>
                            </div>
                        </dl>

                        <div class="es-send-stamp mt-6">
                            <p class="es-send-accent es-send-num text-lg font-black">{{ $send['recipients'] }} envelopes</p>
                            <p class="es-send-muted mt-0.5 text-sm">One per recipient. That is how the allowance counts.</p>
                        </div>
                    </div>

                    <p class="es-send-muted mt-5 text-xs">
                        Until somebody presses send, this sits in Drafts and no address is touched.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Nothing sends itself (01)                                 -->
    <!-- ============================================================ -->
    <section id="nothing" class="es-send-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-send-die mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                <p class="es-send-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The distinction</p>
                <h2 class="es-balance es-send-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Nothing <span class="es-send-grad">sends itself</span>.
                </h2>
                <p class="es-send-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Most tools in this category will mail your list on your behalf the moment you
                    change something. This one has no such thing to switch off.
                </p>
            </div>

            <!-- A duplex split, because the argument IS the split. -->
            <div class="grid gap-4 md:grid-cols-2" data-reveal-group="100">
                <div class="es-send-card p-6 sm:p-8" data-reveal="panel">
                    <p class="es-send-tag mb-4">Sent to followers automatically</p>
                    <div class="es-send-void px-6 py-10 text-center">
                        <p class="es-send-ink text-3xl font-black tracking-tight">Nothing.</p>
                        <p class="es-send-muted mx-auto mt-3 max-w-xs text-sm">
                            No digest, no new-event alert, no reminder. Publishing an event, editing
                            one, or cancelling one sends your followers no mail at all.
                        </p>
                    </div>
                    <p class="es-send-muted mt-5 text-sm">
                        Which means your name is never on a message you did not write, and a quiet
                        month is simply a quiet month.
                    </p>
                </div>

                <div class="es-send-card p-6 sm:p-8" data-reveal="panel">
                    <p class="es-send-tag mb-4">Sent because you decided to</p>
                    <div class="es-send-split">
                        @foreach ([
                            ['A newsletter you composed', 'Your words, your blocks, your subject line.'],
                            ['To a list you chose', 'All followers, ticket buyers, one sub-schedule, or addresses you brought with you.'],
                            ['At a moment you picked', 'Send it now, or set a date and time and let it go out on its own.'],
                            ['With your address to reply to', 'Replies land with the schedule, not with us.'],
                        ] as [$t, $d])
                            <div class="py-3.5">
                                <p class="es-send-ink text-sm font-bold">{{ $t }}</p>
                                <p class="es-send-muted mt-1 text-sm">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-5">
                        <span class="es-send-plan es-send-plan-free">Free</span>
                        <span class="es-send-muted ml-2 text-sm">Ten recipients a month before you pay for anything.</span>
                    </p>
                </div>
            </div>

            <div class="es-send-tint mt-4 p-6 sm:p-7" data-reveal>
                <p class="es-send-ink text-sm font-bold">The automatic mail that does exist runs the other way</p>
                <p class="es-send-muted mt-2 text-sm">
                    People who bought a ticket are told when that event changes or is cancelled, and
                    you are emailed when a booking request lands on your schedule. Neither of those is
                    a newsletter, and neither of them touches your follower list.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The allowance (02)                                        -->
    <!-- ============================================================ -->
    <section id="meter" class="es-send-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-send-die mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-send-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The allowance</p>
                <h2 class="es-balance es-send-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Counted by the envelope, <span class="es-send-grad">not the letter</span>.
                </h2>
                <p class="es-send-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    This is the one number on the page worth reading twice. The monthly allowance
                    counts recipients. A newsletter to forty followers spends forty of it.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-5" data-reveal-group="100">
                <!-- The register. Its digits and its bar come from the same figure. -->
                <div class="es-send-card flex flex-col p-6 sm:p-8 lg:col-span-2" data-reveal="panel">
                    <p class="es-send-tag mb-5">Envelopes this month</p>
                    <div class="es-send-reg" role="img" aria-label="{{ $send['recipients'] }} envelopes used this month">
                        @foreach (str_split($register) as $digit)
                            <span class="es-send-digit">{{ $digit }}</span>
                        @endforeach
                    </div>
                    <p class="es-send-muted es-send-num mt-4 text-sm">
                        {{ $send['recipients'] }} of {{ $send['allowance'] }} used
                    </p>
                    <div class="es-send-bar mt-3" aria-hidden="true">
                        <div class="es-send-fill" style="width: {{ $usedPct }}%;"></div>
                    </div>
                    <p class="es-send-muted mt-5 text-sm">
                        One send of {{ $send['recipients'] }} took {{ $send['recipients'] }} off a
                        Pro month. The counter resets at the start of each month.
                    </p>

                    {{-- Each row here is what NewsletterService::send() actually does before
                         it compares the count to the limit: merge the segments, unique() on
                         the lowercased email, drop the unsubscribes, then count. A test send
                         is a recipient row left at status 'test', which sent_count skips. --}}
                    <div class="es-send-rule-t mt-auto pt-5">
                        <p class="es-send-ink mb-1 text-sm font-bold">What spends one</p>
                        <dl class="es-send-split">
                            @foreach ([
                                ['One address on the list', 'One envelope'],
                                ['The same person in two segments', 'Counted once'],
                                ['Somebody who unsubscribed', 'Not counted'],
                                ['A test send to your own address', 'Not counted'],
                            ] as [$cK, $cV])
                                <div class="flex items-baseline justify-between gap-3 py-2">
                                    <dt class="es-send-muted text-sm">{{ $cK }}</dt>
                                    <dd class="es-send-ink shrink-0 text-sm font-semibold">{{ $cV }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>

                <!-- Same allowance, three ways to spend it. -->
                <div class="lg:col-span-3">
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach ($spends as [$sends, $each, $note])
                            <div class="es-send-card es-send-hover flex h-full flex-col p-5" data-reveal>
                                <p class="es-send-accent es-send-num text-2xl font-black">{{ $sends }} &times; {{ $each }} = {{ $sends * $each }}</p>
                                <p class="es-send-muted mt-2 text-sm">{{ $note }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="es-send-card mt-4 p-6 sm:p-7" data-reveal>
                        <div class="es-send-wrap">
                            <table class="es-send-table">
                                <caption class="es-send-muted mb-4 text-sm">
                                    Recipients each month, per schedule. Nothing else in the newsletter
                                    builder is gated by plan.
                                </caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Plan</th>
                                        <th scope="col">Recipients</th>
                                        <th scope="col">What that includes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($allowances as [$plan, $cap, $note])
                                        <tr>
                                            <th scope="row">{{ $plan }}</th>
                                            <td class="es-send-num whitespace-nowrap">{{ $cap }}</td>
                                            <td>{{ $note }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center" data-reveal>
                <span class="es-send-plan es-send-plan-free">Free</span>
                <span class="es-send-muted ml-2 text-sm">
                    Blocks, templates, segments, scheduling, test sends, open and click tracking and
                    A/B tests are all on the free plan.
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The list (03)                                             -->
    <!-- ============================================================ -->
    <section id="list" class="es-send-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-send-die mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-send-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The list</p>
                    <h2 class="es-balance es-send-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Addressed to <span class="es-send-grad">people who asked</span>.
                    </h2>
                    <p class="es-send-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Somebody follows your schedule from your public page, or scans the follower QR
                        code you printed, or you bring a list you already had. Ticket buyers are
                        reachable without doing anything at all.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['Combine and it deduplicates', 'Pick more than one segment and they are merged by lowercase email address, so nobody receives two copies of the same newsletter.'],
                            ['Unsubscribes are removed', 'Every message carries a one-click unsubscribe link. Anyone who uses it drops out of every later send for that schedule.'],
                            ['Your team is added too', 'Owners, admins and viewers on the schedule are added to the recipients whichever segment you choose, unless they have unsubscribed, so nobody on the inside is surprised by it.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-send-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-send-ink font-semibold">{{ $t }}</span> <span class="es-send-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="es-send-tint mt-8 p-5" data-reveal>
                        <p class="es-send-ink text-sm font-bold">Who can see the list</p>
                        <p class="es-send-muted mt-2 text-sm">
                            You can see your followers by name and email address on your own followers,
                            segment and stats screens. Your public schedule page never lists them, and
                            neither does an embedded calendar.
                        </p>
                    </div>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-send-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-send-ink text-lg font-bold">Segments</h3>
                            <span class="es-send-muted es-send-num text-xs">reusable, per schedule</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ([
                                ['All followers', 'Everyone following the schedule who has not opted out.', null],
                                ['Ticket buyers', 'Narrow to one event, or to a range of order dates.', null],
                                ['A manual list', 'Typed in, pasted in bulk, or uploaded as a CSV of up to ten thousand rows.', null],
                                ['Sub-schedule buyers', 'People who bought into one strand of your schedule.', null],
                                ['Waitlist', 'People waiting on a sold-out ticket.', 'Pro'],
                            ] as [$segName, $segWhat, $segPlan])
                                <div class="es-send-sub p-3.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="es-send-ink text-sm font-semibold">{{ $segName }}</p>
                                        @if ($segPlan)
                                            <span class="es-send-plan es-send-plan-pro">{{ $segPlan }}</span>
                                        @endif
                                    </div>
                                    <p class="es-send-muted mt-0.5 text-sm">{{ $segWhat }}</p>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-send-muted es-send-rule-t mt-5 pt-4 text-xs">
                            Save a segment once and pick it from a list next time. Imported addresses
                            join your followers, so they are yours to reach again.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The letter (04)                                           -->
    <!-- ============================================================ -->
    <section id="letter" class="es-send-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                        <div class="es-tilt-inner es-send-sheet">
                            <p class="es-send-muted es-send-micro mb-3 font-bold uppercase tracking-[0.18em]">One letter, written once</p>
                            <div class="space-y-2">
                                @foreach ([
                                    ['Profile image', 'Your schedule picture, centred.'],
                                    ['Heading', $send['subject']],
                                    ['Text', 'Markdown, so a link is a link and a list is a list.'],
                                    ['Events', 'Three upcoming dates, pulled in as cards.'],
                                    ['Button', 'See the full calendar'],
                                    ['Social links', 'Website, and wherever else you post.'],
                                ] as [$bName, $bWhat])
                                    {{-- Deliberately NOT the shared .es-ai-field stagger: its
                                         transition-delay grows with the item index, so with six
                                         blocks the last one was still fading in 1.4s after load
                                         for reduced-motion and no-JS visitors, and it measured
                                         mid-fade. The sheet reveals as one panel instead. --}}
                                    <div class="es-send-sub px-3.5 py-2.5">
                                        <p class="es-send-accent es-send-micro font-bold uppercase tracking-[0.14em]">{{ $bName }}</p>
                                        <p class="es-send-ink mt-0.5 text-sm">{{ $bWhat }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <p class="es-send-muted es-send-num mt-4 text-xs">{{ $send['blocks'] }} blocks &middot; template: {{ $send['template'] }}</p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-send-die mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-send-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The letter</p>
                    <h2 class="es-balance es-send-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Built from <span class="es-send-grad">fourteen kinds of block</span>.
                    </h2>
                    <p class="es-send-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Drag them into order, clone one, delete one. The events block reads your
                        upcoming dates, so a monthly digest does not mean retyping the calendar into
                        an email.
                    </p>

                    <div class="mb-8 flex flex-wrap gap-2" data-reveal>
                        @foreach ($blockTypes as $bt)
                            <span class="es-send-chip">{{ $bt }}</span>
                        @endforeach
                    </div>

                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['Five templates to start from', 'Modern, Classic, Minimal, Bold or Compact. Each one arrives with its own colours, typeface, button shape and event layout.'],
                            ['Then make it yours', 'Background, accent and text colours, five email-safe fonts, rounded or square buttons, events as cards or as a list, and your own footer line.'],
                            ['Save it as your template', 'Keep a finished newsletter as a starting point and the next one begins already dressed.'],
                        ] as [$t, $d])
                            <div class="es-send-card es-send-hover p-4" data-reveal>
                                <p class="es-send-ink text-sm font-bold">{{ $t }}</p>
                                <p class="es-send-muted mt-1 text-sm">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-7" data-reveal>
                        <span class="es-send-plan es-send-plan-pro">Pro</span>
                        <span class="es-send-muted ml-2 text-sm">
                            Uploading an image file into a newsletter is on Pro. Pointing the image
                            block at a URL works on any plan.
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The returns (05)                                          -->
    <!-- ============================================================ -->
    <section id="receipt" class="es-send-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-send-die mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-send-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The returns</p>
                <h2 class="es-balance es-send-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Every envelope <span class="es-send-grad">has a receipt</span>.
                </h2>
                <p class="es-send-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Each recipient gets their own record. Opens and clicks are written to it as they
                    land, which is what turns a send into something you can learn from.
                </p>
            </div>

            <!-- A record, so it is a table. -->
            <div class="es-send-card p-5 sm:p-7" data-reveal="panel">
                <div class="es-send-wrap">
                    <table class="es-send-table">
                        <caption class="es-send-muted mb-4 text-sm">
                            {{ $send['subject'] }} &middot; sortable by name, address, status, opened
                            or clicked, fifty rows to a page. Illustrative names.
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col">Recipient</th>
                                <th scope="col">Delivered</th>
                                <th scope="col">Opened</th>
                                <th scope="col">Clicked</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($returns as [$rName, $rEmail, $rSent, $rOpened, $rClicked])
                                <tr>
                                    <th scope="row">
                                        {{ $rName }}
                                        <span class="es-send-muted es-send-num block text-xs font-normal">{{ $rEmail }}</span>
                                    </th>
                                    <td class="es-send-num whitespace-nowrap">{{ $rSent }}</td>
                                    <td class="es-send-num whitespace-nowrap">{{ $rOpened ?? 'not yet' }}</td>
                                    <td class="whitespace-nowrap">{{ $rClicked ?? 'no' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="row">{{ $totals['delivered'] }} delivered</th>
                                <td class="es-send-num">{{ $totals['delivered'] }}</td>
                                <td class="es-send-num">{{ $totals['opened'] }} ({{ $openPct }}%)</td>
                                <td class="es-send-num">{{ $totals['clickers'] }} ({{ $clickPct }}%)</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <p class="es-send-muted es-send-rule-t mt-4 pt-4 text-xs">
                    Five of {{ $totals['delivered'] }} rows shown. The open and click figures in the
                    last row are computed from the two counts beside them, not written by hand.
                </p>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3" data-reveal-group="100">
                <div class="es-send-card es-send-hover flex h-full flex-col p-6" data-reveal>
                    <h3 class="es-send-ink mb-2 text-lg font-bold">Which links they used</h3>
                    <p class="es-send-muted mb-4 text-sm">The ten most clicked links in the newsletter, counted across everyone who received it.</p>
                    <div class="es-send-split mt-auto">
                        @foreach ([['/thebasement', 4], ['/tickets/nov-14', 2], ['instagram.com', 1]] as [$linkPath, $linkHits])
                            <div class="flex items-baseline justify-between gap-3 py-2">
                                <span class="es-send-ink es-send-num min-w-0 flex-1 truncate text-xs">{{ $linkPath }}</span>
                                <span class="es-send-accent es-send-num shrink-0 text-xs font-bold">{{ $linkHits }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="es-send-card es-send-hover flex h-full flex-col p-6" data-reveal>
                    <h3 class="es-send-ink mb-2 text-lg font-bold">When they read it</h3>
                    <p class="es-send-muted mb-4 text-sm">Opens and clicks are grouped by day, so you can see how long a send keeps working.</p>
                    <p class="es-send-muted es-send-num mt-auto text-sm">
                        11 on the day it went out, 4 the next day, then 2, then 1.
                    </p>
                </div>

                <div class="es-send-card es-send-hover flex h-full flex-col p-6" data-reveal>
                    <h3 class="es-send-ink mb-2 text-lg font-bold">Test two versions</h3>
                    <p class="es-send-muted mb-4 text-sm">Split the subject line or the content across a sample, then let the better one finish the job.</p>
                    <dl class="es-send-split mt-auto">
                        @foreach ([['Testing', 'Subject line'], ['Sample', '20% of the list'], ['Winner by', 'Open rate'], ['Wait', '4 hours'], ['Then', 'Winner goes to the rest']] as [$abK, $abV])
                            <div class="flex items-baseline justify-between gap-3 py-2">
                                <dt class="es-send-muted text-xs">{{ $abK }}</dt>
                                <dd class="es-send-ink text-xs font-semibold">{{ $abV }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>

            <div class="mt-8 text-center" data-reveal>
                <span class="es-send-muted text-sm">
                    Sample size runs from 5% to 50%, the wait from 1 to 72 hours, and the winner is
                    picked on open rate or click rate. Free plan included.
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. How a send runs (06, fixed dark band)                     -->
    <!-- ============================================================ -->
    <section id="run" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-send-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-send-die mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-send-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How a send runs</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Six steps, and <span class="es-send-grad">you are on all of them</span>.
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Compose', 'Add blocks, drag them into order, write the subject line. Save and come back to it as often as you like.'],
                        ['02', 'Choose the list', 'One segment or several. The count of recipients is shown before you commit to anything.'],
                        ['03', 'Preview and test', 'Open it in the browser, then send a test to yourself and read it in a real inbox first.'],
                        ['04', 'Send or schedule', 'Go now, or set a date and time. A scheduled newsletter can be cancelled up until it leaves.'],
                        ['05', 'It leaves in batches', 'Recipients are queued in batches of fifty, spaced out on the way to the mail server rather than fired in one burst.'],
                        ['06', 'Read the returns', 'Opens, clicks, top links and a per-recipient list, ready to shape whatever you send next.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-lg border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-send-lit es-send-num mb-3 text-sm font-bold">{{ $n }}</p>
                            <h3 class="mb-2 text-lg font-bold text-white">{{ $t }}</h3>
                            <p class="es-send-dim text-sm">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mx-auto mt-10 max-w-3xl rounded-lg border border-white/10 bg-white/[0.05] p-6 text-center" data-reveal>
                    <p class="text-sm font-bold text-white">One thing before your first send</p>
                    <p class="es-send-dim mt-2 text-sm">
                        On the hosted service, a schedule needs either its own SMTP settings or a
                        verified phone number on the account before it can send to a list. It is an
                        anti-spam gate, it happens once, and a selfhosted install does not have it.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Key features                                              -->
    <!-- ============================================================ -->
    <section class="es-send-rule-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-send-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card
                        name="Ticketing"
                        description="Sell tickets with QR check-in and zero platform fees, then write to the buyers"
                        :url="marketing_url('/features/ticketing')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Calendar Sync"
                        description="Two-way sync with Google Calendar, and add-to-calendar links for everyone else"
                        :url="marketing_url('/features/calendar-sync')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Recurring Events"
                        description="Set events to repeat on any schedule, then digest them in one email"
                        :url="marketing_url('/features/recurring-events')"
                        icon-color="green"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Fan Videos"
                        description="Let fans share videos and comments on your events"
                        :url="marketing_url('/features/fan-videos')"
                        icon-color="orange"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-send-accent inline-flex items-center font-medium hover:underline">
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
    <!-- 9. Keep exploring                                            -->
    <!-- ============================================================ -->
    <section class="es-send-rule-t py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-send-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Popular with</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3" data-reveal-group="70">
                @foreach ([
                    ['/for-musicians', 'Musicians'],
                    ['/for-bars', 'Bars'],
                    ['/for-venues', 'Venues'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-send-card es-send-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-send-muted text-sm">Event Schedule for</div>
                            <div class="es-send-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-send-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ route('marketing.docs.newsletters') }}" class="es-send-accent inline-flex items-center font-medium hover:underline">
                    Read the Newsletters guide
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- 10. FAQ                                                      -->
    <!-- ============================================================ -->
    <section id="faq" class="es-send-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-send-die mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-send-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-send-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked <span class="es-send-grad">at the counter</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-send-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-send-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-send-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-send-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    <!-- ============================================================ -->
    <!-- 11. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-send-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-send-tag mb-6">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Write it. <span class="es-send-grad">Then send it</span>.
                    </h2>
                    <p class="es-send-dim mx-auto mb-10 max-w-xl text-lg sm:text-xl">
                        The builder, the segments and the tracking are free. Ten envelopes a month
                        before you pay for anything, and nothing goes out that you did not write.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="es-send-dim shrink-0 select-none font-mono text-sm sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-send-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-send-dim mt-6 text-sm">No credit card required</p>
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
                        <span class="es-send-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
