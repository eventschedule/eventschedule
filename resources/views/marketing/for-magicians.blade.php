<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Magicians | Gigs, Residencies</x-slot>
    <x-slot name="description">Every show, residency and private booking on one link. Sell tickets with zero platform fees and keep corporate gigs off your public schedule. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Magicians</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Oranienbaum') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Cinzel') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Jost') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Italianno') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Magicians"
        description="Every show, residency and private booking on one link. Sell tickets with zero platform fees and keep corporate gigs off your public schedule. Free forever."
        audience="Magicians"
        keywords="magician schedule, magic show calendar, magician booking platform, magic event management, free magician scheduling, private event magician booking, close-up magic schedule, corporate magician calendar, mentalist show scheduling" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How magicians get their performance schedule online with Event Schedule",
        "description": "Get your performance schedule online in three steps.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add your shows",
                "text": "Paste a booking email and AI parsing drafts the event for you, or import from Google Calendar. Set a weekly residency once as a recurring event."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Share one link",
                "text": "Add your schedule link to your bio, EPK, and booking website, or embed the calendar on any page. Planners see your dates and send a booking request from the same link."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Fill the room",
                "text": "Fans who sign up with their email get a digest automatically when you add shows, at most one every three days. The newsletters you write reach their inboxes directly."
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
           For-magicians "Now You See It" styles. A bare stage: bone and
           black, one red, a great deal of air. The decoration is the
           act itself, and every act is plain CSS:

             the inversion   a disc that turns type inside out as it passes
             the vanish      one word of the headline that leaves and returns
             levitation      cards that hang over their own shadows
             the saw         a word cut through the middle and put back
             the turn        six flaps that turn over, three cards that
                             come back marked once you have picked them
             the rings       three solid rings that link as you scroll
             lights down     a stage whose words only show in your light
             the prestige    a card that takes your signature

           Everything is scoped under #mg. Each trick rests in its
           finished, readable state without JS and under reduced motion.
           ============================================================== */

        @property --mg-pitch {
            syntax: '<length>';
            inherits: true;
            initial-value: 0px;
        }

        #mg {
            --mg-ground: #f6f4ef;
            --mg-ground-2: #eeebe3;
            --mg-card: #ffffff;
            --mg-ink: #0b0b0c;
            --mg-ink-2: #3a3936;
            --mg-ink-3: #605e5a;
            --mg-line: rgba(11, 11, 12, 0.2);
            --mg-red: #c8102e;
            --mg-on-red: #ffffff;
            --mg-floor: rgba(11, 11, 12, 0.36);
            --mg-display: 'Oranienbaum', 'Didot', 'Bodoni MT', 'Playfair Display', Georgia, serif;
            --mg-caps: 'Cinzel', 'Trajan Pro', Georgia, serif;
            --mg-text: 'Jost', 'Futura', 'Century Gothic', 'Avenir Next', system-ui, sans-serif;
            --mg-hand: 'Italianno', 'Snell Roundhand', 'Segoe Script', cursive;
            position: relative;
            background: var(--mg-ground);
            color: var(--mg-ink);
            font-family: var(--mg-text);
            font-size: 1.125rem;
            line-height: 1.6;
        }
        .dark #mg {
            --mg-ground: #0b0b0c;
            --mg-ground-2: #131314;
            --mg-card: #18181a;
            --mg-ink: #f1ede4;
            --mg-ink-2: #c9c5bc;
            --mg-ink-3: #9a968e;
            --mg-line: rgba(241, 237, 228, 0.22);
            --mg-red: #f0443a;
            --mg-on-red: #0b0b0c;
            --mg-floor: rgba(241, 237, 228, 0.17);
        }

        /* The bar above is part of the same bare stage. */
        body > header.sticky {
            background-color: rgba(246, 244, 239, 0.88);
            border-bottom-color: rgba(11, 11, 12, 0.14);
        }
        .dark body > header.sticky {
            background-color: rgba(11, 11, 12, 0.88);
            border-bottom-color: rgba(241, 237, 228, 0.14);
        }

        #mg ::selection { background: var(--mg-red); color: var(--mg-on-red); }
        #mg a:focus-visible,
        #mg summary:focus-visible,
        #mg button:focus-visible,
        #mg input:focus-visible {
            outline: 2px solid var(--mg-red);
            outline-offset: 4px;
        }

        .mg-wrap { width: min(100% - 3rem, 76rem); margin-inline: auto; }
        .mg-section { padding-block: clamp(5.5rem, 12vw, 11rem); }
        .mg-alt { background: var(--mg-ground-2); }

        /* Three voices: the bill's serif, the engraved small caps, the plain text. */
        .mg-d { font-family: var(--mg-display); font-weight: 400; letter-spacing: -0.015em; line-height: 0.98; }
        .mg-k {
            font-family: var(--mg-caps);
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            line-height: 1.5;
            color: var(--mg-ink-3);
        }
        .mg-h2 { font-size: clamp(2.7rem, 6.4vw, 6.25rem); text-wrap: balance; }
        .mg-red { color: var(--mg-red); }
        .mg-head { display: grid; gap: 1.6rem; max-width: 66rem; }
        .mg-sub { max-width: 34rem; color: var(--mg-ink-2); font-size: 1.2rem; }
        .mg-rule { height: 1px; background: var(--mg-line); }

        /* The button fills with red from the hand that holds it. */
        .mg-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.9rem;
            padding: 1.25rem 1.9rem 1.15rem;
            background: linear-gradient(var(--mg-red), var(--mg-red)) 0 0 / 0% 100% no-repeat, var(--mg-ink);
            color: var(--mg-ground);
            font-family: var(--mg-caps);
            font-weight: 700;
            font-size: 0.8125rem;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            line-height: 1;
            transition: background-size 0.55s cubic-bezier(0.2, 0.7, 0.2, 1), color 0.3s ease 0.1s;
        }
        .mg-btn:hover { background-size: 100% 100%, auto; color: var(--mg-on-red); }
        .mg-btn svg, .mg-link svg { width: 1.05rem; height: 1.05rem; flex: none; transition: translate 0.4s cubic-bezier(0.2, 0.7, 0.2, 1); }
        .mg-btn:hover svg { translate: 0.3rem 0; }
        .mg-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            padding-block: 0.6rem 0.5rem;
            font-family: var(--mg-caps);
            font-weight: 700;
            font-size: 0.8125rem;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            line-height: 1;
            color: var(--mg-ink);
        }
        /* A hairline that a red one is drawn over, from the left. */
        .mg-link::before,
        .mg-link::after { content: ""; position: absolute; inset: auto 0 0 0; }
        .mg-link::before { height: 1px; background: var(--mg-line); }
        .mg-link::after { height: 2px; background: var(--mg-red); scale: 0 1; transform-origin: 0 50%; transition: scale 0.5s cubic-bezier(0.2, 0.7, 0.2, 1); }
        .mg-link:hover::after { scale: 1 1; }
        .mg-link:hover svg { translate: 0.3rem 0; }
        .mg-link-down:hover svg { translate: 0 0.3rem; }

        /* ---------------------------------------------------------------
           Hero: the inversion and the vanish
           --------------------------------------------------------------- */
        .mg-hero { position: relative; overflow: clip; }
        .mg-hero-in {
            position: relative;
            container-type: inline-size;
            background: var(--mg-ground);
            padding-block: clamp(2.5rem, 6vw, 5rem) clamp(3rem, 6vw, 5.5rem);
        }
        @media (min-width: 1024px) {
            .mg-hero-in { min-height: calc(94svh - 4rem - 4.5rem); display: flex; flex-direction: column; justify-content: center; }
        }
        .mg-act { position: absolute; top: clamp(2.5rem, 6vw, 5rem); right: 0; display: none; }
        .mg-act b { margin-inline-end: 0.8em; color: var(--mg-red); font-weight: 700; }
        @media (min-width: 900px) { .mg-act { display: block; } }
        .mg-eyebrow { display: inline-block; max-width: 24rem; margin-bottom: clamp(1.75rem, 4cqi, 3.5rem); }
        .mg-h1 { font-size: clamp(3.9rem, 18.4cqi, 16.5rem); line-height: 0.92; letter-spacing: -0.03em; }
        #mg .mg-h1 .es-mask { padding-bottom: 0.24em; margin-bottom: -0.24em; }
        .mg-h1 .es-mask-line { white-space: nowrap; }
        .mg-line-2 { position: relative; z-index: 3; margin-inline-start: 0.62em; }
        /* On a narrow stage the first line runs almost wall to wall. */
        @container (max-width: 44rem) {
            .mg-h1 { font-size: 22.6cqi; }
            .mg-line-2 { margin-inline-start: 0.2em; }
            #mg .es-spot { left: var(--mx, 81%); top: var(--my, 25%); width: 37cqi; }
        }

        /* The disc is plain white set to difference: over the ground it reads black
           (bone at night), and any type it passes over turns inside out. The shared
           pointer engine hands it --mx and --my; it glides there, never jumps. */
        #mg .es-spot {
            display: block;
            position: absolute;
            left: var(--mx, 79%);
            top: var(--my, 40%);
            z-index: 2;
            width: clamp(11rem, 31cqi, 27rem);
            aspect-ratio: 1;
            border-radius: 50%;
            background: #fff;
            mix-blend-mode: difference;
            translate: -50% -50%;
            opacity: 1 !important;
            pointer-events: none;
            transition: left 0.9s cubic-bezier(0.2, 0.8, 0.2, 1), top 0.9s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        @media (hover: none) {
            html.es-anim #mg .es-spot { animation: mg-drift 16s ease-in-out infinite alternate; }
        }
        @keyframes mg-drift {
            from { translate: -80% -66%; }
            to { translate: -24% -46%; }
        }

        /* One word leaves, in no hurry, and is back before you can say where it went. */
        .mg-vanish { display: inline-block; transform-origin: 0 60%; }
        html.es-anim #mg .mg-vanish { animation: mg-vanish 13s cubic-bezier(0.5, 0, 0.3, 1) 4s infinite; }
        @keyframes mg-vanish {
            0%, 68% { opacity: 1; filter: blur(0); transform: none; }
            76% { opacity: 0; filter: blur(0.14em); transform: translateY(-0.12em) scaleX(1.18); }
            86% { opacity: 0; filter: blur(0.14em); transform: translateY(0.1em) scaleX(0.9); }
            94%, 100% { opacity: 1; filter: blur(0); transform: none; }
        }

        .mg-hero-foot {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 2rem;
            margin-top: clamp(2rem, 5cqi, 4.5rem);
        }
        @media (min-width: 900px) {
            .mg-hero-foot { grid-template-columns: minmax(0, 1fr) auto; align-items: end; gap: 4rem; }
        }
        .mg-lede { max-width: 31rem; font-size: clamp(1.15rem, 1.5vw, 1.35rem); color: var(--mg-ink-2); }
        .mg-cta { display: flex; flex-wrap: wrap; align-items: center; gap: 1.25rem 2.25rem; }

        .mg-ticker { border-block: 1px solid var(--mg-line); padding-block: 1.15rem 1rem; }
        .mg-ticker .es-marquee-track { gap: 0; padding-right: 0; align-items: center; }
        .mg-show { display: inline-flex; align-items: center; gap: 2.2rem; padding-inline-end: 2.2rem; white-space: nowrap; color: var(--mg-ink-2); }
        .mg-show::after { content: ""; width: 0.42rem; aspect-ratio: 1; border-radius: 50%; background: var(--mg-red); }
        @media (prefers-reduced-motion: reduce) {
            .mg-ticker .es-marquee-track { row-gap: 0.75rem; }
        }

        /* ---------------------------------------------------------------
           The gigs on the table: levitation
           --------------------------------------------------------------- */
        .mg-gigs { display: grid; gap: 4.5rem 2.5rem; margin-top: clamp(5rem, 9vw, 8rem); }
        @media (min-width: 900px) { .mg-gigs { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 3rem; } }
        .mg-gig { position: relative; padding-bottom: 3.25rem; }
        .mg-gig-card {
            position: relative;
            z-index: 1;
            height: 100%;
            padding: 2rem 1.9rem 2.1rem;
            background: var(--mg-card);
            border: 1px solid var(--mg-ink);
            translate: 0 -0.9rem;
            transition: translate 0.9s cubic-bezier(0.2, 0.7, 0.2, 1);
        }
        .mg-gig-card h3 { margin-top: 1.4rem; font-size: clamp(1.9rem, 2.6vw, 2.5rem); }
        .mg-gig-card p { margin-top: 0.9rem; color: var(--mg-ink-2); }
        /* The shadow is its own thing on the floor: the higher the card, the smaller and fainter. */
        .mg-floor {
            position: absolute;
            left: 9%;
            right: 9%;
            bottom: 0.6rem;
            height: 1.6rem;
            border-radius: 50%;
            background: radial-gradient(closest-side, var(--mg-floor), transparent);
            filter: blur(5px);
            transition: scale 0.9s cubic-bezier(0.2, 0.7, 0.2, 1), opacity 0.9s ease;
        }
        html.es-anim #mg .mg-gig-card { animation: mg-rise var(--t, 6.5s) ease-in-out var(--w, 0s) infinite alternate; }
        html.es-anim #mg .mg-gig .mg-floor { animation: mg-shade var(--t, 6.5s) ease-in-out var(--w, 0s) infinite alternate; }
        @keyframes mg-rise { from { translate: 0 -0.5rem; } to { translate: 0 -2.3rem; } }
        @keyframes mg-shade { from { scale: 1; opacity: 1; } to { scale: 0.66 0.8; opacity: 0.5; } }
        .mg-gig:hover .mg-gig-card { animation-play-state: paused; }
        .mg-gig:hover .mg-floor { animation-play-state: paused; }
        .mg-after { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.75rem 2rem; margin-top: 1.75rem; padding-top: 2rem; border-top: 1px solid var(--mg-ink); color: var(--mg-ink-2); }

        /* ---------------------------------------------------------------
           The saw: a word cut through the middle, and restored
           --------------------------------------------------------------- */
        .mg-saw {
            position: relative;
            container-type: inline-size;
            padding-block: clamp(3.5rem, 8vw, 7rem);
            border-block: 1px solid var(--mg-line);
            overflow: clip;
            view-timeline: --mg-saw block;
        }
        .mg-saw-word {
            position: relative;
            width: max-content;
            margin-inline: auto;
            font-size: clamp(3.4rem, 19cqi, 17rem);
            line-height: 1;
            letter-spacing: -0.03em;
            white-space: nowrap;
        }
        .mg-saw-top { display: block; clip-path: inset(-10% -10% 48% -10%); translate: -0.045em -0.19em; }
        .mg-saw-bot { position: absolute; inset: 0; clip-path: inset(52% -10% -20% -10%); translate: 0.045em 0.19em; }
        .mg-saw-cap {
            position: absolute;
            inset: 50% 0 auto 0;
            translate: 0 -50%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            color: var(--mg-red);
            white-space: nowrap;
        }
        .mg-saw-cap::before,
        .mg-saw-cap::after { content: ""; flex: 1; height: 1px; background: var(--mg-red); }
        @container (max-width: 44rem) {
            .mg-saw-cap { gap: 0; font-size: 0; }
        }
        @supports (animation-timeline: view()) {
            html.es-anim #mg .mg-saw-top { animation: mg-saw-up linear both; animation-timeline: --mg-saw; animation-range: entry 25% exit 75%; }
            html.es-anim #mg .mg-saw-bot { animation: mg-saw-down linear both; animation-timeline: --mg-saw; animation-range: entry 25% exit 75%; }
            html.es-anim #mg .mg-saw-cap { animation: mg-saw-cap linear both; animation-timeline: --mg-saw; animation-range: entry 25% exit 75%; }
        }
        @keyframes mg-saw-up { 0%, 14% { translate: 0 0; } 40%, 62% { translate: -0.045em -0.19em; } 90%, 100% { translate: 0 0; } }
        @keyframes mg-saw-down { 0%, 14% { translate: 0 0; } 40%, 62% { translate: 0.045em 0.19em; } 90%, 100% { translate: 0 0; } }
        @keyframes mg-saw-cap { 0%, 20% { opacity: 0; scale: 0.2 1; } 40%, 62% { opacity: 1; scale: 1 1; } 84%, 100% { opacity: 0; scale: 0.2 1; } }

        /* ---------------------------------------------------------------
           The deal: six flaps, face down, that turn as they arrive
           --------------------------------------------------------------- */
        .mg-deal { display: grid; gap: clamp(1rem, 2vw, 1.6rem); margin-top: clamp(3.5rem, 7vw, 6rem); }
        .mg-flap { perspective: 1700px; }
        html.es-anim #mg [data-reveal="turn"] { opacity: 1; }
        .mg-flap-in {
            position: relative;
            transform-style: preserve-3d;
            transition: transform 1.5s cubic-bezier(0.2, 0.75, 0.15, 1);
            transition-delay: 0.15s;
        }
        html.es-anim #mg [data-reveal="turn"]:not(.is-revealed) .mg-flap-in { transform: rotateX(-180deg); }
        .mg-flap-face,
        .mg-flap-back { -webkit-backface-visibility: hidden; backface-visibility: hidden; }
        .mg-flap-face {
            display: grid;
            gap: 1.25rem;
            padding: 2rem 1.6rem 2.1rem;
            background: var(--mg-card);
            border: 1px solid var(--mg-ink);
        }
        @media (min-width: 900px) {
            .mg-flap-face {
                grid-template-columns: 6.5rem minmax(0, 1fr) auto;
                align-items: center;
                gap: 2.5rem;
                padding: 2.6rem 3rem 2.7rem 2.6rem;
            }
        }
        .mg-flap-no { font-size: clamp(2.2rem, 3.6vw, 3.4rem); color: var(--mg-ink-3); line-height: 1; }
        .mg-flap-face h3 { font-size: clamp(1.9rem, 3vw, 2.8rem); }
        .mg-flap-face p { margin-top: 0.75rem; max-width: 40rem; color: var(--mg-ink-2); }
        .mg-flap-back {
            position: absolute;
            inset: 0;
            transform: rotateX(180deg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-inline: clamp(1.6rem, 4vw, 3rem);
            background: var(--mg-ink);
            color: var(--mg-ground);
            box-shadow: inset 0 0 0 0.6rem var(--mg-ink), inset 0 0 0 calc(0.6rem + 1px) color-mix(in srgb, var(--mg-ground) 45%, transparent);
        }
        .mg-flap-back .mg-k { color: inherit; opacity: 0.8; }
        .mg-flap-back .mg-k:last-child { rotate: 180deg; }
        .mg-flap-back i { width: 0.9rem; aspect-ratio: 1; border-radius: 50%; background: var(--mg-red); }

        /* ---------------------------------------------------------------
           Pick a card: the turn, and the card comes back marked
           --------------------------------------------------------------- */
        .mg-pick { display: grid; gap: 2.5rem 2rem; max-width: 25rem; margin: clamp(3.5rem, 7vw, 6rem) auto 0; }
        @media (min-width: 900px) { .mg-pick { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 2.5rem; max-width: 64rem; margin-inline: auto; } }
        .mg-pcard { position: relative; display: block; perspective: 1500px; padding-bottom: 2.75rem; }
        .mg-pcard-in {
            position: relative;
            z-index: 1;
            height: 100%;
            transform-style: preserve-3d;
            transition: transform 1.1s cubic-bezier(0.25, 0.8, 0.2, 1), translate 0.7s cubic-bezier(0.2, 0.7, 0.2, 1);
        }
        @media (min-width: 900px) { .mg-pcard-in { aspect-ratio: 5 / 7; } }
        .mg-pcard-face,
        .mg-pcard-back { -webkit-backface-visibility: hidden; backface-visibility: hidden; border: 1px solid var(--mg-ink); }
        .mg-pcard-face {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 1.9rem 1.75rem 1.75rem;
            background: var(--mg-card);
        }
        .mg-pcard-face h3 { margin-top: 1.25rem; font-size: clamp(1.9rem, 2.5vw, 2.4rem); }
        .mg-pcard-face p { margin-block: 0.9rem 1.75rem; color: var(--mg-ink-2); font-size: 1.0625rem; }
        .mg-pcard-go { margin-top: auto; align-self: flex-start; }
        .mg-pcard:hover .mg-pcard-go::after { scale: 1 1; }
        .mg-pcard:hover .mg-pcard-go svg { translate: 0.3rem 0; }
        .mg-pcard-back {
            position: absolute;
            inset: 0;
            transform: rotateY(180deg);
            display: grid;
            place-items: center;
            background: var(--mg-ink);
            box-shadow: inset 0 0 0 0.7rem var(--mg-ink), inset 0 0 0 calc(0.7rem + 1px) color-mix(in srgb, var(--mg-ground) 45%, transparent);
        }
        .mg-pcard-back i { position: absolute; width: 3.4rem; aspect-ratio: 1; border-radius: 50%; }
        .mg-pcard-ring { border: 1px solid color-mix(in srgb, var(--mg-ground) 70%, transparent); }
        /* The latch: the mark arrives at once and takes a very long time to think
           about leaving, so a card you have looked at comes back with a red spot. */
        .mg-pcard-mark { background: var(--mg-red); opacity: 0; scale: 0.4; transition: opacity 0s linear 99999s, scale 0s linear 99999s; }
        .mg-pcard:hover .mg-pcard-mark,
        .mg-pcard:focus-visible .mg-pcard-mark { opacity: 1; scale: 1; transition-delay: 0s; }
        @media (hover: hover) and (pointer: fine) {
            html.es-anim #mg .mg-pcard:not(:hover):not(:focus-visible) .mg-pcard-in { transform: rotateY(180deg); }
            html.es-anim #mg .mg-pcard:hover .mg-pcard-in,
            html.es-anim #mg .mg-pcard:focus-visible .mg-pcard-in { translate: 0 -1rem; }
            html.es-anim #mg .mg-pcard:hover .mg-floor,
            html.es-anim #mg .mg-pcard:focus-visible .mg-floor { scale: 0.74 0.8; opacity: 0.55; }
        }
        .mg-pick-foot { margin: clamp(3rem, 5vw, 4.5rem) auto 0; max-width: 40rem; text-align: center; color: var(--mg-ink-2); }

        /* ---------------------------------------------------------------
           The routine: three solid rings, linked
           --------------------------------------------------------------- */
        .mg-rings {
            --mg-d: clamp(5.4rem, 22vw, 13.5rem);
            --mg-pitch: calc(var(--mg-d) * 0.73);
            position: relative;
            width: calc(var(--mg-d) * 3.4);
            max-width: 100%;
            height: var(--mg-d);
            margin: clamp(3.5rem, 7vw, 6rem) auto 0;
            view-timeline: --mg-rings block;
        }
        .mg-ring {
            position: absolute;
            top: 0;
            left: calc(50% - var(--mg-d) / 2 + (var(--i) - 1) * var(--mg-pitch));
            width: var(--mg-d);
            height: var(--mg-d);
            display: grid;
            place-items: center;
            font-size: calc(var(--mg-d) * 0.36);
            color: var(--c, var(--mg-ink));
        }
        .mg-ring::before,
        .mg-ring::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: max(3px, calc(var(--mg-d) * 0.028)) solid var(--c, var(--mg-ink));
        }
        /* Each ring is drawn twice: whole, and again as its upper right quarter laid
           over the next ring. Over at the top, under at the bottom: linked. */
        .mg-ring::before { z-index: calc(var(--i) + 1); }
        .mg-ring::after { z-index: calc(var(--i) + 5); clip-path: inset(0 0 50% 50%); }
        @supports (animation-timeline: view()) {
            html.es-anim #mg .mg-rings {
                animation: mg-link linear both;
                animation-timeline: --mg-rings;
                animation-range: entry 10% cover 48%;
            }
        }
        @keyframes mg-link {
            from { --mg-pitch: calc(var(--mg-d) * 1.2); }
            to { --mg-pitch: calc(var(--mg-d) * 0.73); }
        }
        .mg-moves { display: grid; gap: 3rem 3.5rem; margin-top: clamp(3.5rem, 6vw, 5.5rem); }
        @media (min-width: 900px) { .mg-moves { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .mg-move { padding-top: 1.5rem; border-top: 1px solid var(--mg-ink); }
        .mg-move h3 { margin-top: 1rem; font-size: clamp(1.9rem, 2.6vw, 2.5rem); }
        .mg-move p { margin-top: 0.8rem; color: var(--mg-ink-2); }

        /* ---------------------------------------------------------------
           Sleight of hand: lights down. Black in both modes; the words
           are there all along, and only show in the light you carry.
           --------------------------------------------------------------- */
        .mg-stage {
            position: relative;
            overflow: clip;
            background-color: #0b0b0c;
            color: #f1ede4;
        }
        .mg-stage::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at var(--sx, 50%) var(--sy, 40%), #f6f4ef 0, #f6f4ef 8.5rem, rgba(246, 244, 239, 0) 18rem);
            opacity: 0;
            transition: opacity 0.8s ease;
            pointer-events: none;
        }
        .mg-stage.is-dark::before { opacity: 1; }
        .mg-stage > .mg-wrap { position: relative; }
        .mg-stage .mg-k { color: #9a968e; }
        .mg-stage .mg-sub, .mg-stage .mg-unseen p { color: #c9c5bc; }
        .mg-stage .mg-red { color: #f0443a; }
        .mg-stage .mg-k,
        .mg-stage .mg-h2,
        .mg-stage .mg-red,
        .mg-stage .mg-sub,
        .mg-stage .mg-unseen,
        .mg-stage .mg-unseen p,
        .mg-stage .mg-unseen-no { transition: color 0.8s ease, border-color 0.8s ease; }
        .mg-stage.is-dark .mg-k,
        .mg-stage.is-dark .mg-h2,
        .mg-stage.is-dark .mg-sub,
        .mg-stage.is-dark .mg-unseen,
        .mg-stage.is-dark .mg-unseen p { color: #0b0b0c; }
        .mg-stage.is-dark .mg-red,
        .mg-stage.is-dark .mg-unseen-no { color: #c8102e; }
        .mg-stage.is-dark .mg-unseen { border-color: #0b0b0c; }
        .mg-stage-head { display: grid; gap: 2rem; align-items: end; }
        @media (min-width: 900px) { .mg-stage-head { grid-template-columns: minmax(0, 1fr) auto; } }
        .mg-lights {
            justify-self: start;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.95rem 1.2rem 0.85rem;
            background: #f1ede4;
            color: #0b0b0c;
            font-family: var(--mg-caps);
            font-weight: 700;
            font-size: 0.72rem;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            line-height: 1;
            cursor: pointer;
        }
        .mg-lights[hidden] { display: none; }
        .mg-lights i { width: 0.6rem; aspect-ratio: 1; border-radius: 50%; border: 1px solid #0b0b0c; }
        .mg-lights[aria-pressed="true"] i { background: #c8102e; border-color: #c8102e; }
        #mg .mg-stage .mg-lights:focus-visible { outline-color: #f0443a; }
        .mg-unseens { display: grid; gap: 0 3.5rem; margin-top: clamp(3.5rem, 6vw, 5.5rem); }
        @media (min-width: 720px) { .mg-unseens { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1100px) { .mg-unseens { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .mg-unseen { padding-block: 1.75rem 2.6rem; border-top: 1px solid rgba(241, 237, 228, 0.3); }
        .mg-stage .mg-unseen-no { color: #f0443a; }
        .mg-unseen h3 { margin-top: 0.9rem; font-size: clamp(1.7rem, 2.2vw, 2.1rem); }
        .mg-unseen p { margin-top: 0.7rem; font-size: 1.0625rem; }
        .mg-stage-hint { margin-top: 1.5rem; color: #f0443a; visibility: hidden; }
        .mg-stage.can-dark .mg-stage-hint { visibility: visible; }

        /* ---------------------------------------------------------------
           Every kind of act: the bill
           --------------------------------------------------------------- */
        .mg-bill { display: grid; margin-top: clamp(3.5rem, 6vw, 5.5rem); border-top: 1px solid var(--mg-ink); }
        @media (min-width: 900px) { .mg-bill { grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 5rem; } }
        .mg-act-row {
            position: relative;
            display: grid;
            grid-template-columns: 2.6rem minmax(0, 1fr);
            gap: 0.5rem 1.25rem;
            padding-block: 2rem 2.2rem;
            border-bottom: 1px solid var(--mg-line);
        }
        .mg-act-row .mg-k { padding-top: 0.9rem; }
        .mg-act-row h3 { font-size: clamp(2rem, 3vw, 2.9rem); transition: translate 0.6s cubic-bezier(0.2, 0.7, 0.2, 1); }
        .mg-act-row p { grid-column: 2; max-width: 30rem; color: var(--mg-ink-2); }
        .mg-act-row a { grid-column: 2; justify-self: start; margin-top: 0.6rem; }
        /* A small production: the red ball was not there, and then it is. */
        .mg-act-row::before {
            content: "";
            position: absolute;
            left: 3.85rem;
            top: 2.95rem;
            width: 0.7rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background: var(--mg-red);
            scale: 0;
            transition: scale 0.5s cubic-bezier(0.3, 1.5, 0.5, 1);
        }
        .mg-act-row:hover::before { scale: 1; }
        .mg-act-row:hover h3 { translate: 1.5rem 0; }

        /* ---------------------------------------------------------------
           The programme: key features
           --------------------------------------------------------------- */
        .mg-prog-grid { display: grid; gap: 2.5rem 5rem; align-items: start; }
        @media (min-width: 960px) { .mg-prog-grid { grid-template-columns: minmax(0, 0.62fr) minmax(0, 1.38fr); } }
        .mg-prog { border-top: 1px solid var(--mg-ink); }
        .mg-prog a {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.4rem 1.5rem;
            padding-block: 1.6rem 1.7rem;
            border-bottom: 1px solid var(--mg-line);
        }
        .mg-prog strong { font-family: var(--mg-display); font-weight: 400; font-size: clamp(1.9rem, 2.8vw, 2.6rem); line-height: 1; letter-spacing: -0.015em; transition: color 0.4s ease; }
        .mg-prog small { grid-column: 1; font-size: 1.0625rem; color: var(--mg-ink-2); }
        .mg-prog svg { grid-row: 1 / span 2; grid-column: 2; width: 1.6rem; height: 1.6rem; transition: translate 0.5s cubic-bezier(0.2, 0.7, 0.2, 1), color 0.4s ease; }
        .mg-prog a:hover strong, .mg-prog a:hover svg { color: var(--mg-red); }
        .mg-prog a:hover svg { translate: 0.5rem 0; }
        .mg-prog-more { margin-top: 2rem; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the dress changes.
           --------------------------------------------------------------- */
        #mg .mg-plans > section { background: var(--mg-ground-2); }
        #mg .mg-plans h2 { font-family: var(--mg-display); font-weight: 400; letter-spacing: -0.015em; line-height: 1; font-size: clamp(2.4rem, 5vw, 4.2rem); color: var(--mg-ink); }
        #mg .mg-plans h2 + p { color: var(--mg-ink-2); font-size: 1.125rem; }
        #mg .mg-plans .grid > div { background: var(--mg-card); border: 1px solid var(--mg-ink); border-radius: 0; box-shadow: none; color: var(--mg-ink); }
        #mg .mg-plans .grid > div:hover { box-shadow: 0 1.6rem 1.6rem -1.5rem var(--mg-floor); }
        #mg .mg-plans .grid > div:nth-child(2) { outline: 1px solid var(--mg-red); outline-offset: -0.5rem; }
        #mg .mg-plans .grid > div span,
        #mg .mg-plans .grid > div p,
        #mg .mg-plans .grid > div li { color: var(--mg-ink-2); }
        #mg .mg-plans .grid > div .text-3xl { font-family: var(--mg-display); font-weight: 400; font-size: 3.2rem; letter-spacing: -0.02em; color: var(--mg-ink); }
        #mg .mg-plans .grid > div .uppercase { font-family: var(--mg-caps); letter-spacing: 0.24em; color: var(--mg-ink); }
        #mg .mg-plans .grid > div .rounded-full { background: var(--mg-red); color: var(--mg-on-red); border-radius: 0; font-family: var(--mg-caps); letter-spacing: 0.14em; }
        #mg .mg-plans .grid > div svg { color: var(--mg-red); }
        #mg .mg-plans a.font-medium { color: var(--mg-ink); font-family: var(--mg-caps); font-weight: 700; font-size: 0.8125rem; letter-spacing: 0.2em; text-transform: uppercase; border-bottom: 1px solid var(--mg-red); padding-bottom: 0.3rem; }
        #mg .mg-plans a.rounded-2xl { background: var(--mg-ink); color: var(--mg-ground); border-radius: 0; box-shadow: none; font-family: var(--mg-caps); font-weight: 700; font-size: 0.8125rem; letter-spacing: 0.24em; text-transform: uppercase; padding: 1.25rem 1.9rem 1.15rem; }
        #mg .mg-plans a.rounded-2xl:hover { background: var(--mg-red); color: var(--mg-on-red); transform: none; }

        #mg .mg-keep > section { background: var(--mg-ground); border-top: 1px solid var(--mg-line); }
        #mg .mg-keep h2 { font-family: var(--mg-display); font-weight: 400; letter-spacing: -0.015em; font-size: clamp(2.2rem, 4vw, 3.2rem); line-height: 1; color: var(--mg-ink); }
        #mg .mg-keep p.uppercase { font-family: var(--mg-caps); font-weight: 700; letter-spacing: 0.3em; font-size: 0.75rem; color: var(--mg-red); }
        #mg .mg-keep .grid > a { background: var(--mg-card); border: 1px solid var(--mg-ink); border-radius: 0; }
        #mg .mg-keep .grid > a:hover { border-color: var(--mg-ink); box-shadow: 0 1.6rem 1.6rem -1.5rem var(--mg-floor); }
        #mg .mg-keep .grid > a > span:first-child { display: none; }
        #mg .mg-keep .grid > a h3 { font-family: var(--mg-display); font-weight: 400; font-size: 1.5rem; line-height: 1.1; color: var(--mg-ink); }
        #mg .mg-keep .grid > a p { color: var(--mg-ink-2); }
        #mg .mg-keep .grid > a > span:last-child,
        #mg .mg-keep a.self-start { color: var(--mg-red); }

        /* ---------------------------------------------------------------
           Also on the bill: related pages
           --------------------------------------------------------------- */
        .mg-others-head { display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between; gap: 1.5rem; }
        .mg-others { display: grid; margin-top: clamp(2.5rem, 5vw, 4rem); border-top: 1px solid var(--mg-ink); }
        @media (min-width: 700px) { .mg-others { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1100px) { .mg-others { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .mg-others a { display: grid; gap: 0.8rem; align-content: space-between; padding: 1.75rem 1.5rem 1.6rem 0; border-bottom: 1px solid var(--mg-line); }
        @media (min-width: 700px) {
            .mg-others a { min-height: 11rem; padding-inline: 1.75rem; border-inline-start: 1px solid var(--mg-line); }
            .mg-others a:nth-child(odd) { padding-inline-start: 0; border-inline-start: 0; }
        }
        @media (min-width: 1100px) {
            .mg-others a:nth-child(odd) { padding-inline-start: 1.75rem; border-inline-start: 1px solid var(--mg-line); }
            .mg-others a:first-child { padding-inline-start: 0; border-inline-start: 0; }
        }
        .mg-others strong { font-family: var(--mg-display); font-weight: 400; font-size: clamp(1.8rem, 2.4vw, 2.3rem); line-height: 1.02; letter-spacing: -0.015em; text-wrap: balance; transition: color 0.4s ease; }
        .mg-others a:hover strong { color: var(--mg-red); }

        /* ---------------------------------------------------------------
           The secrets we can tell: questions
           --------------------------------------------------------------- */
        .mg-faq-grid { display: grid; gap: 3rem 5rem; align-items: start; }
        @media (min-width: 1000px) {
            .mg-faq-grid { grid-template-columns: minmax(0, 0.7fr) minmax(0, 1.3fr); }
            .mg-faq-head { position: sticky; top: 7rem; }
        }
        .mg-faq-head .mg-h2 { margin-block: 1.5rem 1.4rem; font-size: clamp(2.6rem, 4.8vw, 4.5rem); }
        .mg-qa { border-top: 1px solid var(--mg-ink); }
        .mg-qa details { border-bottom: 1px solid var(--mg-line); }
        .mg-qa summary { display: grid; grid-template-columns: minmax(0, 1fr) 1.1rem; align-items: center; gap: 1.5rem; padding-block: 1.6rem 1.5rem; cursor: pointer; }
        .mg-qa h3 { font-family: var(--mg-display); font-size: clamp(1.45rem, 2vw, 1.8rem); line-height: 1.15; letter-spacing: -0.01em; }
        /* An empty ring that is somehow holding the ball when the answer opens. */
        .mg-qa summary i { width: 1.1rem; aspect-ratio: 1; border-radius: 50%; border: 1px solid var(--mg-ink); background: radial-gradient(circle, var(--mg-red) 0 99%, transparent 100%) center / 0% 0% no-repeat; transition: background-size 0.5s cubic-bezier(0.3, 1.5, 0.5, 1), border-color 0.4s ease; }
        .mg-qa details[open] summary i { background-size: 100% 100%; border-color: var(--mg-red); }
        .mg-qa details p { max-width: 46rem; padding-bottom: 2rem; color: var(--mg-ink-2); }

        /* ---------------------------------------------------------------
           The prestige: black in both modes, one white card, signed
           --------------------------------------------------------------- */
        .mg-prestige { position: relative; overflow: clip; background-color: #0b0b0c; color: #f1ede4; padding-block: clamp(5.5rem, 12vw, 10.5rem); }
        .mg-prestige .mg-k { color: #9a968e; }
        .mg-prestige .mg-red { color: #f0443a; }
        /* The one column is minmax(0, 1fr), not auto: the signature never wraps, so a long name
           typed on a phone widened an auto track and pushed the text and the box off the side. */
        .mg-prestige-in { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3.5rem; }
        @media (min-width: 1000px) {
            .mg-prestige-in { grid-template-columns: minmax(0, 1.12fr) minmax(0, 0.88fr); grid-template-rows: auto auto; column-gap: 5rem; row-gap: 3rem; align-items: start; }
            .mg-yours { grid-column: 2; grid-row: 1 / span 2; align-self: center; }
        }
        .mg-prestige .mg-h2 { margin-top: 1.5rem; font-size: clamp(3.2rem, 8.4vw, 8.2rem); }
        .mg-prestige .mg-sub { margin-top: 1.75rem; color: #c9c5bc; }
        /* The iris is cut into the CHILDREN, never into the element the reveal observer watches:
           Chrome counts an element's own clip-path when it asks whether the element is on screen,
           so a watched element clipped to nothing is never seen and never revealed. */
        #mg [data-reveal="appear"] { transition: opacity 0.6s ease 0.2s; }
        #mg [data-reveal="appear"] > * { clip-path: circle(150% at 50% 50%); transition: clip-path 1.9s cubic-bezier(0.2, 0.7, 0.2, 1) 0.2s; }
        html.es-anim #mg [data-reveal="appear"]:not(.is-revealed) > * { clip-path: circle(0% at 50% 50%); }
        .mg-claim-form { display: grid; gap: 1rem; }
        .mg-claim-row { display: grid; gap: 1rem; }
        @media (min-width: 620px) { .mg-claim-row { grid-template-columns: minmax(0, 1fr) auto; } }
        /* Where the finale first goes to two columns the left one cannot hold the box and the
           button side by side: the name was left 12px at 1000 wide and 26px at 1024. */
        @media (min-width: 1000px) and (max-width: 1199px) { .mg-claim-row { grid-template-columns: minmax(0, 1fr); } }
        #mg .mg-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 1.2rem;
            border: 1px solid rgba(241, 237, 228, 0.55);
            background: transparent;
            font-size: 1.125rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        #mg .mg-claim:focus-within { border-color: #f1ede4; box-shadow: 0 0 0 3px rgba(240, 68, 58, 0.55); }
        #mg .mg-claim input {
            flex: 1;
            min-width: 0;
            border: 0;
            background: transparent;
            padding-inline: 0;
            text-align: right;
            font: inherit;
            color: #f1ede4;
            box-shadow: none;
            outline: none;
        }
        #mg .mg-claim input::placeholder { color: #9a968e; }
        .mg-claim span { flex: none; color: #9a968e; user-select: none; }
        #mg .mg-prestige .mg-btn { background: linear-gradient(#f0443a, #f0443a) 0 0 / 0% 100% no-repeat, #f1ede4; color: #0b0b0c; }
        #mg .mg-prestige .mg-btn:hover { background-size: 100% 100%, auto; color: #0b0b0c; }
        #mg .mg-prestige a:focus-visible, #mg .mg-prestige input:focus-visible { outline-color: #f0443a; }
        .mg-note { color: #9a968e; font-size: 1rem; }
        .mg-yours { position: relative; width: min(78%, 21rem); margin-inline: auto; padding-bottom: 3.5rem; }
        .mg-yours-card {
            position: relative;
            z-index: 1;
            aspect-ratio: 5 / 7;
            display: flex;
            flex-direction: column;
            padding: 1.6rem 1.5rem 1.5rem;
            background: #f6f4ef;
            color: #0b0b0c;
            rotate: -4deg;
            translate: 0 -1.2rem;
        }
        .mg-yours-card .mg-k { color: #605e5a; }
        .mg-yours-card i { align-self: flex-end; margin-top: -1rem; width: 1rem; aspect-ratio: 1; border-radius: 50%; background: #c8102e; }
        .mg-sign-box { margin-block: auto; min-width: 0; text-align: center; }
        .mg-sign {
            display: block;
            max-width: 100%;
            overflow: hidden;
            padding: 0.1em 0.12em 0.05em;
            font-family: var(--mg-hand);
            font-size: clamp(2.6rem, 9vw, 3.9rem);
            line-height: 1.05;
            color: #c8102e;
            white-space: nowrap;
            text-overflow: ellipsis;
            rotate: -5deg;
        }
        .mg-sign.is-writing { animation: mg-write 0.5s cubic-bezier(0.3, 0.6, 0.3, 1); }
        @keyframes mg-write { from { clip-path: inset(0 100% 0 0); } to { clip-path: inset(0 0 0 0); } }
        .mg-sign-line { display: block; height: 1px; margin-top: 0.4rem; background: rgba(11, 11, 12, 0.3); }
        .mg-yours-url { display: block; margin-top: 0.6rem; font-size: 0.95rem; color: #605e5a; }
        .mg-yours .mg-floor { background: radial-gradient(closest-side, rgba(241, 237, 228, 0.22), transparent); left: 12%; right: 12%; }
        html.es-anim #mg .mg-yours-card { animation: mg-hang 7s ease-in-out infinite alternate; }
        html.es-anim #mg .mg-yours .mg-floor { animation: mg-shade 7s ease-in-out infinite alternate; }
        @keyframes mg-hang { from { translate: 0 -0.6rem; } to { translate: 0 -2.4rem; } }

        @media (prefers-reduced-motion: reduce) {
            #mg .es-spot,
            .mg-btn, .mg-btn svg, .mg-link::after, .mg-link svg,
            .mg-flap-in, .mg-pcard-in, .mg-gig-card, .mg-floor,
            .mg-act-row::before, .mg-act-row h3, .mg-prog svg,
            .mg-stage::before, .mg-qa summary i,
            #mg [data-reveal="appear"], #mg [data-reveal="appear"] > * { transition: none; }
            .mg-sign.is-writing { animation: none; }
        }
    </style>

    @php
        $mgArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16m0 0l-6-6m6 6l-6 6" /></svg>';
        $mgDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m0 0l-6-6m6 6l6-6" /></svg>';
        $mgRoman = ['I', 'II', 'III', 'IV', 'V', 'VI'];
    @endphp

    <div id="mg">

        <!-- ============================================================ -->
        <!-- 1. Hero: the inversion, and one word that vanishes           -->
        <!-- ============================================================ -->
        <section class="mg-hero es-hero" id="top">
            <div class="mg-wrap mg-hero-in">
                <div class="es-spot" aria-hidden="true"></div>
                <p class="mg-k mg-act es-fade-up es-d-1" aria-hidden="true"><b>I</b>The pledge</p>

                <h1 class="mg-d mg-h1">
                    <x-marketing.hero-eyebrow class="mg-k mg-eyebrow es-fade-up es-d-1">
                        Event schedule for magicians, mentalists, and illusionists
                    </x-marketing.hero-eyebrow>
                    <span class="es-mask"><span class="es-mask-line">Pick a card.</span></span>
                    <span class="es-mask es-mask-2 mg-line-2"><span class="es-mask-line"><span class="mg-red"><span class="mg-vanish" data-contrast-skip>Any</span> card.</span></span></span>
                </h1>

                <div class="mg-hero-foot">
                    <p class="mg-lede es-fade-up es-d-2">
                        Every magic show, residency, and private booking on one schedule link. Planners request a booking from it. Fans never miss the reveal.
                    </p>
                    <div class="mg-cta es-fade-up es-d-3">
                        <a href="#deal" class="mg-link mg-link-down">
                            See the deal
                            {!! $mgDown !!}
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="mg-btn">
                            Create your schedule
                            {!! $mgArrow !!}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Show-type marquee -->
            <div class="mg-ticker es-fade-up es-d-4">
                <div class="es-marquee" data-marquee="1">
                    <div class="es-marquee-track">
                        @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                            @foreach (['Close-Up', 'Parlor', 'Stage', 'Corporate', 'Weddings', 'Kids Shows', 'Mentalism', 'Trade Shows', 'Street Magic', 'Cruise Ships'] as $chip)
                                <span @if ($chipCopy === 1) aria-hidden="true" @endif class="mg-k mg-show">{{ $chip }}</span>
                            @endforeach
                        @endfor
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The stakes: three gigs, hanging in the air                -->
        <!-- ============================================================ -->
        <section id="stakes" class="mg-section" style="scroll-margin-top: 4rem;">
            <div class="mg-wrap">
                <div class="mg-head">
                    <p class="mg-k" data-reveal>The gigs on the table</p>
                    <h2 class="mg-d mg-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Your next three gigs live in three inboxes. <span class="mg-red">Deal them onto one table.</span>
                    </h2>
                </div>

                <div class="mg-gigs" data-reveal-group="140">
                    <div class="mg-gig" data-reveal style="--t: 6.2s; --w: 0s;">
                        <div class="mg-gig-card">
                            <span class="mg-k" aria-hidden="true">Inbox one &middot; Email</span>
                            <h3 class="mg-d">The corporate inquiry</h3>
                            <p>Sitting in an email thread from three weeks ago. Subject line: Re: Re: Fwd: Holiday party?</p>
                        </div>
                        <i class="mg-floor" aria-hidden="true"></i>
                    </div>
                    <div class="mg-gig" data-reveal style="--t: 7.4s; --w: -2.5s;">
                        <div class="mg-gig-card">
                            <span class="mg-k" aria-hidden="true">Inbox two &middot; A message</span>
                            <h3 class="mg-d">The wedding close-up set</h3>
                            <p>An Instagram DM you starred so you would not lose it. You lost it.</p>
                        </div>
                        <i class="mg-floor" aria-hidden="true"></i>
                    </div>
                    <div class="mg-gig" data-reveal style="--t: 6.8s; --w: -4.6s;">
                        <div class="mg-gig-card">
                            <span class="mg-k" aria-hidden="true">Inbox three &middot; A napkin</span>
                            <h3 class="mg-d">The Tuesday residency</h3>
                            <p>On a napkin behind the bar. The bar knows the date. Your fans do not.</p>
                        </div>
                        <i class="mg-floor" aria-hidden="true"></i>
                    </div>
                </div>

                <p class="mg-after" data-reveal>
                    <span>One schedule holds every gig, and shows each audience only what they should see.</span>
                    <a href="#deal" class="mg-link mg-link-down">
                        Watch the deal
                        {!! $mgDown !!}
                    </a>
                </p>
            </div>
        </section>

        <!-- The saw: act two -->
        <div class="mg-saw" aria-hidden="true">
            <div class="mg-d mg-saw-word">
                <span class="mg-saw-top">The turn</span>
                <span class="mg-saw-bot">The turn</span>
            </div>
            <span class="mg-k mg-saw-cap">Act two</span>
        </div>

        <!-- ============================================================ -->
        <!-- 3. The deal: six features, face down, that turn              -->
        <!-- ============================================================ -->
        <section id="deal" class="mg-section" style="scroll-margin-top: 4rem;">
            <div class="mg-wrap">
                <div class="mg-head">
                    <p class="mg-k" data-reveal>The deal</p>
                    <h2 class="mg-d mg-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Six cards, face down. <span class="mg-red">Watch them turn.</span>
                    </h2>
                    <p class="mg-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Everything a working magician needs, dealt one card at a time.
                    </p>
                </div>

                @php
                    $dealCards = [
                        [
                            'title' => 'Zero-fee ticketing',
                            'copy' => 'Take payment through Stripe or PayPal, or cash at the door, for general admission, VIP, and meet-and-greet tickets. Every ticket carries a QR code for the door, and Event Schedule takes no platform fee.',
                            'url' => '/features/ticketing', 'link' => 'Sell tickets',
                        ],
                        [
                            'title' => 'Show posters, produced',
                            'copy' => 'Every event auto-generates a poster sized for socials. Post the date, not a blank story.',
                            'url' => '/features/event-graphics', 'link' => 'See event graphics',
                        ],
                        [
                            'title' => 'Residencies on repeat',
                            'copy' => 'Set the Tuesday parlor show once. Day-of-week recurrence, with date exceptions for the weeks you tour.',
                            'url' => '/features/recurring-events', 'link' => 'Set up recurring shows',
                        ],
                        [
                            'title' => 'Private stays private',
                            'copy' => 'Unlimited free drafts keep corporate dates off your public schedule. Enterprise adds internal and unlisted events with an optional password.',
                            'url' => '/pricing', 'link' => 'Compare plans',
                        ],
                        [
                            'title' => 'Passes and gift cards',
                            'copy' => 'On Pro, sell a season pass for the parlor run, and balance-tracked gift cards fans send by email. Zero platform fees on both.',
                            'url' => '/features/gift-cards', 'link' => 'Sell gift cards',
                        ],
                        [
                            'title' => 'Calendars that agree',
                            'copy' => 'Two-way Google, Outlook, and CalDAV sync. Every gig lands in the calendar you already check, so a booked Saturday looks booked before you say yes.',
                            'url' => '/features/calendar-sync', 'link' => 'Sync your calendar',
                        ],
                    ];
                @endphp

                <ol class="mg-deal">
                    @foreach ($dealCards as $cardIndex => $card)
                        <li class="mg-flap" data-reveal="turn">
                            <div class="mg-flap-in">
                                <article class="mg-flap-face">
                                    <span class="mg-d mg-flap-no" aria-hidden="true">{{ $mgRoman[$cardIndex] }}</span>
                                    <div>
                                        <h3 class="mg-d">{{ $card['title'] }}</h3>
                                        <p>{{ $card['copy'] }}</p>
                                    </div>
                                    <a href="{{ marketing_url($card['url']) }}" class="mg-link" style="justify-self: start;">
                                        {{ $card['link'] }}
                                        {!! $mgArrow !!}
                                    </a>
                                </article>
                                <div class="mg-flap-back" aria-hidden="true">
                                    <span class="mg-k">{{ $mgRoman[$cardIndex] }}</span>
                                    <i></i>
                                    <span class="mg-k">{{ $mgRoman[$cardIndex] }}</span>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Pick a card: the interactive moment                       -->
        <!-- ============================================================ -->
        <section id="pick" class="mg-section mg-alt" style="scroll-margin-top: 4rem;">
            <div class="mg-wrap">
                <div class="mg-head" style="margin-inline: auto; text-align: center; justify-items: center;">
                    <p class="mg-k" data-reveal>Your routine</p>
                    <h2 class="mg-d mg-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Pick a card. <span class="mg-red">Go on.</span>
                    </h2>
                    <p class="mg-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Hover, or tab across with your keyboard. Whichever gig you run, there is a setup for it.
                    </p>
                </div>

                <div class="mg-pick" data-reveal-group="120">
                    <a href="{{ marketing_url('/pricing') }}" class="mg-pcard" data-reveal>
                        <div class="mg-pcard-in">
                            <div class="mg-pcard-face">
                                <span class="mg-k" aria-hidden="true">The first card</span>
                                <h3 class="mg-d">The corporate gala</h3>
                                <p>Internal and unlisted events, password-protected pages, and the days your team marks as unavailable. The gig nobody hears about until the invoice clears.</p>
                                <span class="mg-link mg-pcard-go">See Enterprise {!! $mgArrow !!}</span>
                            </div>
                            <div class="mg-pcard-back" aria-hidden="true"><i class="mg-pcard-ring"></i><i class="mg-pcard-mark"></i></div>
                        </div>
                        <i class="mg-floor" aria-hidden="true"></i>
                    </a>
                    <a href="{{ marketing_url('/features/recurring-events') }}" class="mg-pcard" data-reveal>
                        <div class="mg-pcard-in">
                            <div class="mg-pcard-face">
                                <span class="mg-k" aria-hidden="true">The second card</span>
                                <h3 class="mg-d">The residency</h3>
                                <p>A recurring Tuesday show, a season pass for the regulars, and a poster that makes itself every week.</p>
                                <span class="mg-link mg-pcard-go">Set the pattern {!! $mgArrow !!}</span>
                            </div>
                            <div class="mg-pcard-back" aria-hidden="true"><i class="mg-pcard-ring"></i><i class="mg-pcard-mark"></i></div>
                        </div>
                        <i class="mg-floor" aria-hidden="true"></i>
                    </a>
                    <a href="{{ marketing_url('/features/ticketing') }}" class="mg-pcard" data-reveal>
                        <div class="mg-pcard-in">
                            <div class="mg-pcard-face">
                                <span class="mg-k" aria-hidden="true">The third card</span>
                                <h3 class="mg-d">The parlor show</h3>
                                <p>Twenty seats, sold through your own link. QR check-in at the door, and on Pro a waitlist for when the room is full.</p>
                                <span class="mg-link mg-pcard-go">Sell the room {!! $mgArrow !!}</span>
                            </div>
                            <div class="mg-pcard-back" aria-hidden="true"><i class="mg-pcard-ring"></i><i class="mg-pcard-mark"></i></div>
                        </div>
                        <i class="mg-floor" aria-hidden="true"></i>
                    </a>
                </div>

                <p class="mg-pick-foot" data-reveal>
                    On phones the cards are already face up. A magician never repeats a trick. A schedule should.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. The routine: how it works, on three linked rings          -->
        <!-- ============================================================ -->
        <section id="routine" class="mg-section" style="scroll-margin-top: 4rem;">
            <div class="mg-wrap">
                <div class="mg-head" style="margin-inline: auto; text-align: center; justify-items: center;">
                    <p class="mg-k" data-reveal>The routine</p>
                    <h2 class="mg-d mg-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Three moves. <span class="mg-red">No sleight required.</span>
                    </h2>
                </div>

                <div class="mg-rings mg-d" aria-hidden="true">
                    <span class="mg-ring" style="--i: 0;">1</span>
                    <span class="mg-ring" style="--i: 1; --c: var(--mg-red);">2</span>
                    <span class="mg-ring" style="--i: 2;">3</span>
                </div>

                <div class="mg-moves" data-reveal-group="140">
                    <div class="mg-move" data-reveal>
                        <span class="mg-k" aria-hidden="true">Move one</span>
                        <h3 class="mg-d">Add your shows</h3>
                        <p>Paste a booking email and AI parsing drafts the event for you, or import from Google Calendar. Set a weekly residency once as a recurring event.</p>
                    </div>
                    <div class="mg-move" data-reveal>
                        <span class="mg-k" aria-hidden="true">Move two</span>
                        <h3 class="mg-d">Share one link</h3>
                        <p>Add your schedule link to your bio, EPK, and booking website, or embed the calendar on any page. Planners see your dates and send a booking request from the same link.</p>
                    </div>
                    <div class="mg-move" data-reveal>
                        <span class="mg-k" aria-hidden="true">Move three</span>
                        <h3 class="mg-d">Fill the room</h3>
                        <p>Fans who sign up with their email get a digest automatically when you add shows, at most one every three days. The newsletters you write reach their inboxes directly.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Sleight of hand: lights down                              -->
        <!-- ============================================================ -->
        <section id="sleight" class="mg-section mg-stage" style="scroll-margin-top: 4rem;">
            <div class="mg-wrap">
                <div class="mg-stage-head">
                    <div class="mg-head">
                        <p class="mg-k">Sleight of hand</p>
                        <h2 class="mg-d mg-h2">
                            The moves the audience <span class="mg-red">never sees.</span>
                        </h2>
                        <p class="mg-sub">
                            Small utilities that handle the prep, so the audience only sees the act.
                        </p>
                    </div>
                    <button type="button" class="mg-lights" id="mg-lights" aria-pressed="false" hidden><i aria-hidden="true"></i>House lights</button>
                </div>
                <p class="mg-k mg-stage-hint" aria-hidden="true">Step in and the lights go down. Your pointer carries the only light.</p>

                <div class="mg-unseens">
                    @foreach ([
                        ['Event templates', 'Load the trick once. On Pro, save any show as a template and produce the next one in two clicks.'],
                        ['Custom fields', 'On Pro, track what only you need: stage size, mic setup, table count, load-in time.'],
                        ['AI event parsing', 'Paste a booking email and a draft event appears, date and venue filled in. Included on every plan.'],
                        ['Ticket waitlist', 'Sold-out parlor show? On Pro, the waitlist tells the next fan in line when a seat frees up.'],
                        ['Embed ticket widget', 'On Pro, sell tickets from your own website with an embedded checkout.'],
                        ['Availability management', 'On Enterprise, you and your team mark the days you cannot work, and see them on the calendar before you accept a date.'],
                    ] as $moveIndex => [$moveTitle, $moveCopy])
                        <div class="mg-unseen">
                            <span class="mg-k mg-unseen-no" aria-hidden="true">No. {{ $moveIndex + 1 }}</span>
                            <h3 class="mg-d">{{ $moveTitle }}</h3>
                            <p>{{ $moveCopy }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. Perfect for: the bill                                     -->
        <!-- ============================================================ -->
        <section class="mg-section">
            <div class="mg-wrap">
                <div class="mg-head">
                    <p class="mg-k" data-reveal>Every kind of act</p>
                    <h2 class="mg-d mg-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Perfect for <span class="mg-red">every performer.</span>
                    </h2>
                    <p class="mg-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Close-up or grand illusion, one schedule carries the whole act.
                    </p>
                </div>

                @php
                    $mgActs = [
                        ['Close-Up Magicians', 'Card tricks, coin magic, sleight of hand for intimate gatherings and table-hopping at events.', 'for-close-up-magicians'],
                        ['Stage Illusionists', 'Large-scale illusions and theatrical magic shows that fill theaters and wow audiences.', 'for-stage-illusionists'],
                        ['Mentalists', 'Mind reading, predictions, and psychological entertainment that leaves audiences amazed.', 'for-mentalists'],
                        ['Children\'s Entertainers', 'Birthday parties, school shows, and family events with fun, interactive magic for kids.', 'for-childrens-entertainers'],
                        ['Corporate Magicians', 'Trade shows, conferences, and product launches with customized magic presentations.', 'for-corporate-magicians'],
                        ['Variety Artists', 'Ventriloquists, escape artists, hypnotists, and specialty acts that defy categorization.', 'for-variety-artists'],
                    ];
                @endphp

                <div class="mg-bill" data-reveal-group="70">
                    @foreach ($mgActs as $actIndex => [$actName, $actDesc, $actSlug])
                        @php $mgPost = get_sub_audience_blog($actSlug); @endphp
                        <article class="mg-act-row" data-reveal>
                            <span class="mg-k" aria-hidden="true">{{ str_pad($actIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="mg-d">{{ $actName }}</h3>
                            <p>{{ $actDesc }}</p>
                            @if ($mgPost)
                                <a href="{{ blog_url('/' . $mgPost->slug) }}" class="mg-link" aria-label="Learn more about Event Schedule for {{ $actName }}">
                                    Learn more
                                    {!! $mgArrow !!}
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Key features: the programme                               -->
        <!-- ============================================================ -->
        <section class="mg-section mg-alt">
            <div class="mg-wrap mg-prog-grid">
                <div>
                    <p class="mg-k" data-reveal>The programme</p>
                    <h2 class="mg-d mg-h2" data-reveal style="--reveal-delay: 0.08s; margin-top: 1.5rem;">Key <span class="mg-red">features</span></h2>
                    <p class="mg-prog-more" data-reveal style="--reveal-delay: 0.16s;">
                        <a href="{{ marketing_url('/features') }}" class="mg-link">
                            See all features
                            {!! $mgArrow !!}
                        </a>
                    </p>
                </div>

                <div class="mg-prog" data-reveal-group="70">
                    @foreach ([
                        ['Ticketing', 'Sell tickets with QR check-in and zero platform fees', '/features/ticketing'],
                        ['Event Graphics', 'Show posters generated from your events', '/features/event-graphics'],
                        ['Newsletters', 'Send event updates directly to followers\' inboxes', '/features/newsletters'],
                        ['Calendar Sync', 'Two-way sync with Google, Outlook and CalDAV', '/features/calendar-sync'],
                    ] as [$progName, $progDesc, $progUrl])
                        <a href="{{ marketing_url($progUrl) }}" data-reveal>
                            <strong>{{ $progName }}</strong>
                            <small>{{ $progDesc }}</small>
                            {!! $mgArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="mg-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 9. Related pages: also on the bill                           -->
        <!-- ============================================================ -->
        <section class="mg-section">
            <div class="mg-wrap">
                <div class="mg-others-head">
                    <div>
                        <p class="mg-k" data-reveal>Also on the bill</p>
                        <h2 class="mg-d mg-h2" data-reveal style="--reveal-delay: 0.08s; margin-top: 1.5rem;">Related <span class="mg-red">pages</span></h2>
                    </div>
                    <a href="{{ marketing_url('/use-cases') }}" class="mg-link" data-reveal>
                        See all use cases
                        {!! $mgArrow !!}
                    </a>
                </div>

                <div class="mg-others" data-reveal-group="80">
                    @foreach ([['/for-comedians', 'Comedians'], ['/for-circus-acrobatics', 'Circus & Acrobatics'], ['/for-theater-performers', 'Theater Performers'], ['/for-spoken-word', 'Spoken Word Artists']] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" data-reveal>
                            <span class="mg-k">Event Schedule for</span>
                            <strong>{{ $relName }}</strong>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. FAQ: the secrets we can tell                             -->
        <!-- ============================================================ -->
        <section id="faq" class="mg-section mg-alt" style="scroll-margin-top: 4rem;">
            <div class="mg-wrap mg-faq-grid">
                <div class="mg-faq-head">
                    <p class="mg-k" data-reveal>The secrets we can tell</p>
                    <h2 class="mg-d mg-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Frequently asked <span class="mg-red">questions</span>
                    </h2>
                    <p class="mg-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Everything magicians ask about Event Schedule.
                    </p>
                </div>

                <div class="mg-qa" data-reveal>
                    @php
                        $faqs = [
                            ['q' => 'Is Event Schedule free for magicians?', 'a' => 'Yes. Event Schedule is free forever for sharing your show schedule, building a following, and syncing with Google Calendar. Free registration is unlimited on it, and the QR on each place is scanned at the door on any plan. Putting a price on a ticket is the Pro half. Newsletters are free at 10 a month, counted per recipient rather than per send. Zero platform fees on ticket sales, on any plan.'],
                            ['q' => 'Can I keep private and corporate bookings off my public schedule?', 'a' => 'Yes. Save any booking as a draft and it stays off your public schedule until you publish it. Drafts are free and unlimited, so you can hold close-up gigs and corporate dates privately. On the Enterprise plan you can also make events internal or unlisted with an optional password for private and corporate clients.'],
                            ['q' => 'Can I sell gift cards or season passes for my shows?', 'a' => 'Yes. On the Pro plan, once your schedule has its own email settings on eventschedule.com, you can sell balance-tracked gift cards that buyers send to a recipient by email, redeemable toward tickets for any show on your schedule. You can also sell multi-use passes like a parlor-show season pass, with usage tracked automatically. Zero platform fees apply to both.'],
                            ['q' => 'Can I sell tickets to my magic shows?', 'a' => 'Yes, on the Pro plan, which is what a ticket carrying a price needs; free registration for a show you are not charging for is unlimited without it. Take payment through Stripe or PayPal straight to your own account, or through Payfast (rand only), Invoice Ninja, a payment link or cash. Create ticket types for general admission, VIP, and meet-and-greet packages, each with a QR code for check-in at the door. On Pro, a waitlist tells fans when a sold-out show frees a seat. If a show is called off, a Stripe or PayPal sale can be refunded in full or in part from the Sales page, and the money goes back through the provider. Zero platform fees, so the only deduction is your payment provider\'s own.'],
                            ['q' => 'Can I run a weekly residency without re-entering the same show?', 'a' => 'Yes. Set up your show once as a recurring event with a day-of-week pattern, and add date exceptions for the weeks you are away. On the Pro plan you can also save any event as a template, so repeat corporate formats take two clicks instead of a blank form.'],
                            ['q' => 'How do planners and fans find my shows?', 'a' => 'Share one schedule link in your bio, EPK, and booking website, or embed the calendar on any page, and planners send a booking request from the same link. Fans who sign up for email get a digest automatically when you add a show, and newsletters reach their inboxes directly. Fans who would rather not give an email can subscribe to your calendar feed instead. On a single show, once you switch on the "Notify me" card, anyone can leave just an email address to hear when its tickets go on sale, if it is cancelled, and shortly before it starts, plus any change notice you send. Two-way Google, Outlook, and CalDAV sync keeps your own calendar current.'],
                            ['q' => 'A venue listed my show before I signed up. Is there a page for me already?', 'a' => 'There may be. When a venue or promoter names an act that is not on Event Schedule, its event page still shows that act on the bill by name, and the app creates a page for the act. That page says which schedule created it and that you have not claimed it, credits each date to the schedule that added it, and stays out of search engines until it is claimed. If it carries your email address, create an account or sign in with that address and press Claim this page: it becomes your schedule, and the venues that already listed you keep listing you without asking again, while anyone new sends a request you accept. If it is not you, This is not me takes it down.'],
                        ];
                    @endphp
                    @foreach ($faqs as ['q' => $q, 'a' => $a])
                        <details name="faq">
                            <summary>
                                <h3>{{ $q }}</h3>
                                <i aria-hidden="true"></i>
                            </summary>
                            <p>{{ $a }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$faqs" />

        <!-- The saw: act three -->
        <div class="mg-saw" aria-hidden="true">
            <div class="mg-d mg-saw-word">
                <span class="mg-saw-top">The prestige</span>
                <span class="mg-saw-bot">The prestige</span>
            </div>
            <span class="mg-k mg-saw-cap">Act three</span>
        </div>

        <!-- ============================================================ -->
        <!-- 11. Finale: is this your card?                               -->
        <!-- ============================================================ -->
        <section id="claim" class="mg-prestige" style="scroll-margin-top: 4rem;">
            <div class="mg-wrap mg-prestige-in">
                <div>
                    <p class="mg-k" data-reveal>The reveal</p>
                    <h2 class="mg-d mg-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Is this <span class="mg-red">your card?</span>
                    </h2>
                    <p class="mg-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Type a name and watch the signature appear. Your schedule link works the same way: one card, always yours.
                    </p>
                </div>

                <!-- The signed card, hanging in the air -->
                <div class="mg-yours" aria-hidden="true" data-reveal="appear">
                    <div class="mg-yours-card">
                        <span class="mg-k">Signed by</span>
                        <div class="mg-sign-box">
                            <span class="mg-sign" id="mg-sign">your-name</span>
                            <span class="mg-sign-line"></span>
                            <span class="mg-yours-url">.eventschedule.com</span>
                        </div>
                        <i></i>
                    </div>
                    <i class="mg-floor"></i>
                </div>

                <div class="mg-claim-form" data-reveal="appear">
                    <label for="es-claim-input" class="mg-k">Your schedule name</label>
                    <div class="mg-claim-row">
                        <div dir="ltr" class="es-claim mg-claim">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30">
                            <span>.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="mg-btn">
                            Claim your card
                            {!! $mgArrow !!}
                        </a>
                    </div>
                    <p class="mg-note">No credit card required. Well. One card.</p>
                </div>
            </div>
        </section>

        <div class="mg-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    {{-- Two small hands off stage. The first signs the card: it mirrors the claimed name,
         applying the same slug transform as the shared claim-input sanitizer. The second
         works the lights over "Sleight of hand", and only for a fine pointer that has not
         asked for less motion; everyone else simply reads the section with the lights up. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            var input = document.getElementById('es-claim-input');
            var sign = document.getElementById('mg-sign');
            if (input && sign) {
                var fallback = sign.textContent;
                input.addEventListener('input', function () {
                    var slug = input.value.toLowerCase()
                        .replace(/['’]/g, '')
                        .replace(/[^a-z0-9-]+/g, '-')
                        .replace(/-{2,}/g, '-')
                        .replace(/^-+/, '')
                        .slice(0, 30);
                    sign.textContent = slug || fallback;
                    if (!still) {
                        sign.classList.remove('is-writing');
                        void sign.offsetWidth;
                        sign.classList.add('is-writing');
                    }
                });
            }

            var stage = document.getElementById('sleight');
            var lights = document.getElementById('mg-lights');
            if (!stage || !lights || still || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                return;
            }
            var houseLights = false;
            stage.classList.add('can-dark');
            lights.hidden = false;
            var carry = function (event) {
                var box = stage.getBoundingClientRect();
                stage.style.setProperty('--sx', (event.clientX - box.left) + 'px');
                stage.style.setProperty('--sy', (event.clientY - box.top) + 'px');
            };
            stage.addEventListener('pointerenter', function (event) {
                carry(event);
                if (!houseLights) {
                    stage.classList.add('is-dark');
                }
            });
            stage.addEventListener('pointermove', carry);
            stage.addEventListener('pointerleave', function () {
                stage.classList.remove('is-dark');
            });
            lights.addEventListener('click', function () {
                houseLights = !houseLights;
                lights.setAttribute('aria-pressed', houseLights ? 'true' : 'false');
                stage.classList.toggle('is-dark', !houseLights && stage.matches(':hover'));
            });
        })();
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
