<x-marketing-layout :hp="true">
    <x-slot name="title">White-Label Ticketing Platform - Launch Your Own for Free</x-slot>
    <x-slot name="description">Launch a white-label ticketing platform for free. Open source, multi-tenant, Stripe billing built in. Set your prices; only Stripe's fee comes off. See the demo.</x-slot>
    <x-slot name="breadcrumbTitle">White-Label SaaS</x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule White-Label Ticketing Platform"
        description="Free, open source white-label ticketing platform with multi-tenant subscription billing built in. Selfhost it and set your own prices: only Stripe's fee comes off what your customers pay." />
    @php
        $howToSteps = [
            ['name' => 'Deploy the platform', 'text' => 'Install Event Schedule on your own server with Docker or the Softaculous one-click installer, then point wildcard DNS at it so every customer can get a subdomain.'],
            ['name' => 'Apply your branding', 'text' => 'Point APP_LOGO_LIGHT and APP_LOGO_DARK at your own logos, run the install on your own domain, and set APP_MARKETING_URL so the platform runs under your brand, bar the small attribution link the license asks for.'],
            ['name' => 'Connect Stripe and set your prices', 'text' => 'Connect your Stripe account, create prices for your Pro and Enterprise tiers, set the trial length with TRIAL_DAYS, and open sign-ups.'],
        ];
    @endphp
    <x-seo.howto-schema
        name="How to Launch a White-Label Ticketing SaaS"
        description="Deploy the open source Event Schedule platform, brand it, and start charging your own customers in three steps."
        :steps="$howToSteps" />
    </x-slot>

    @php
        // The four layers, top of the stack first. Each chapter of the page is one of them.
        $skLayers = [
            1 => ['id' => 'brand', 'name' => 'Brand', 'say' => 'Your logo, your domain'],
            2 => ['id' => 'tenants', 'name' => 'Tenants', 'say' => 'A subdomain per customer'],
            3 => ['id' => 'billing', 'name' => 'Billing', 'say' => 'Your prices, your Stripe'],
            4 => ['id' => 'infra', 'name' => 'Infra', 'say' => 'Your servers'],
        ];
        $skArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $skOut = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>';
        $skCheck = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>';
        $skGithub = '<svg aria-hidden="true" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>';
    @endphp

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           /saas: "The Stack" - own every layer.
           The page is the stack, read top to bottom: Brand, Tenants,
           Billing, Infra. Blue is the house; amber is money and nothing
           else. Everything here is page-own (sk-*), on the house kit.
           ============================================================== */

        #hp {
            --sk-amber: #b45309;
            --sk-amber-soft: rgba(245, 158, 11, 0.14);
            --sk-amber-line: rgba(217, 119, 6, 0.38);
            --sk-top: #ffffff;
            --sk-top-2: #f1f4fb;
            --sk-top-line: rgba(10, 16, 32, 0.13);
            --sk-1a: #7ea6ff; --sk-1b: #2b5fe3;
            --sk-2a: #6fd6f5; --sk-2b: #0b8fd8;
            --sk-3a: #fcd76a; --sk-3b: #dd8a06;
            --sk-4a: #2b3a6b; --sk-4b: #0a1124;
            --sk-0a: #dfe5f2; --sk-0b: #bcc7de;
            --sk-floor: rgba(47, 102, 234, 0.3);
            --sk-stage: #e9edf6;
            --sk-red: #b91c1c;
            --sk-green: #047857;
        }
        .dark #hp {
            --sk-amber: #fbbf24;
            --sk-amber-soft: rgba(251, 191, 36, 0.12);
            --sk-amber-line: rgba(251, 191, 36, 0.36);
            --sk-top: #131b33;
            --sk-top-2: #19223f;
            --sk-top-line: rgba(255, 255, 255, 0.16);
            --sk-1a: #8db0ff; --sk-1b: #3565e0;
            --sk-2a: #7dd3fc; --sk-2b: #0e86c9;
            --sk-3a: #fcd34d; --sk-3b: #c77d08;
            --sk-4a: #3b4d8c; --sk-4b: #1a2550;
            --sk-0a: #33405f; --sk-0b: #1f2942;
            --sk-floor: rgba(78, 129, 250, 0.5);
            --sk-stage: #0a0f1c;
            --sk-red: #f87171;
            --sk-green: #34d399;
        }

        /* Money rule: every amount of money on the page is amber. */
        #hp .sk-money { color: var(--sk-amber); }
        #hp .hp-dark .sk-money,
        #hp .hp-finale .sk-money,
        #hp .sk-night .sk-money { color: #fbbf24; }

        #hp .hp-alt .sk-tint { background: var(--hp-bg); }
        .dark #hp .hp-alt .sk-tint { background: var(--hp-bg-2); }
        #hp .sk-mono { font-family: var(--hp-mono); font-variation-settings: normal; }
        #hp .sk-block[id],
        #hp .sk-night { scroll-margin-top: 4.5rem; }
        /* Shown in a right-to-left language, the things that are code or addresses keep their order. */
        #hp :is(.sk-term, .sk-envfile, .sk-env, .sk-url, .sk-ten, .sk-tick, .sk-paid, .sk-range, .sk-flow, .sk-name-dom, .sk-finale-dom, .sk-f, .sk-field, .sk-ends, .sk-path-foot code, .sk-meter code) { direction: ltr; }
        /* Without script the two name boxes and the sliders would do nothing: they stay away. */
        html:not(.sk-js) #hp :is(.sk-name, .sk-finale-brand, .sk-knobs) { display: none; }
        #hp .sk-wrap-wide { width: min(100% - 2.5rem, 76rem); margin-inline: auto; }

        /* --------------------------------------------------------------
           The stack: four slabs in true isometric, each with thickness.
           No 3D is asked of the browser. Seen without perspective, an
           isometric plane is one 2D matrix (--ma to --md), and a rise of
           one unit is --mz of a unit straight up the screen. A slab is a
           face plus ten copies of its outline stepped down the screen;
           each copy is cut on its diagonal into a lit and a shaded half,
           so the left side catches the light and the right does not.
           Sizes are in --u, so the same rig is the hero, a chapter mark,
           a diagram and the finale. Slabs are written bottom first: the
           order they are painted in is the order they stand in.
           -------------------------------------------------------------- */
        #hp .sk-iso {
            --ma: 0.7071;
            --mb: -0.3954;
            --mc: 0.7071;
            --md: 0.3954;
            --mz: 0.829;
            position: absolute;
            left: 50%;
            top: calc(var(--u) * var(--h, 40) * 0.5);
            width: calc(var(--u) * 30);
            height: calc(var(--u) * 19);
            margin-top: calc(var(--u) * (var(--dy, 0) - 9.5));
            margin-left: calc(var(--u) * (var(--dx, 0) - 15));
        }
        #hp .sk-slab {
            position: absolute;
            inset: 0;
            transform: translate(calc(var(--u) * var(--out, 0) * var(--ma)), calc(var(--u) * (var(--out, 0) * var(--mb) - (var(--gap, 5.2) * var(--lvl, 0) + var(--lift, 0)) * var(--mz))));
            transition: transform 0.9s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.5s ease;
        }
        #hp .sk-slab > i {
            position: absolute;
            inset: 0;
            border-radius: calc(var(--u) * 1.5);
            background: linear-gradient(to bottom right, var(--sa) 49.4%, var(--sb) 50.6%);
            transform: translateY(calc(var(--u) * var(--k) * var(--thick, 0.15) * var(--mz))) matrix(var(--ma), var(--mb), var(--mc), var(--md), 0, 0);
        }
        #hp .sk-slab > i:nth-of-type(1),
        #hp .sk-city-slab > i:nth-of-type(1) { --k: 1; }
        #hp .sk-slab > i:nth-of-type(2),
        #hp .sk-city-slab > i:nth-of-type(2) { --k: 2; }
        #hp .sk-slab > i:nth-of-type(3),
        #hp .sk-city-slab > i:nth-of-type(3) { --k: 3; }
        #hp .sk-slab > i:nth-of-type(4),
        #hp .sk-city-slab > i:nth-of-type(4) { --k: 4; }
        #hp .sk-slab > i:nth-of-type(5),
        #hp .sk-city-slab > i:nth-of-type(5) { --k: 5; }
        #hp .sk-slab > i:nth-of-type(6),
        #hp .sk-city-slab > i:nth-of-type(6) { --k: 6; }
        #hp .sk-slab > i:nth-of-type(7),
        #hp .sk-city-slab > i:nth-of-type(7) { --k: 7; }
        #hp .sk-slab > i:nth-of-type(8),
        #hp .sk-city-slab > i:nth-of-type(8) { --k: 8; }
        #hp .sk-slab > i:nth-of-type(9),
        #hp .sk-city-slab > i:nth-of-type(9) { --k: 9; }
        #hp .sk-slab > i:nth-of-type(10),
        #hp .sk-city-slab > i:nth-of-type(10) { --k: 10; }
        #hp .sk-face {
            position: absolute;
            inset: 0;
            overflow: hidden;
            border-radius: calc(var(--u) * 1.5);
            background: var(--sk-top);
            box-shadow: inset 0 0 0 1px var(--sk-top-line);
            color: var(--hp-ink);
            font-size: calc(var(--u) * 1.2);
            line-height: 1.2;
            transform: matrix(var(--ma), var(--mb), var(--mc), var(--md), 0, 0);
        }
        #hp .sk-slab[data-l="1"] { --lvl: 3; --sa: var(--sk-1a); --sb: var(--sk-1b); }
        #hp .sk-slab[data-l="2"] { --lvl: 2; --sa: var(--sk-2a); --sb: var(--sk-2b); }
        #hp .sk-slab[data-l="3"] { --lvl: 1; --sa: var(--sk-3a); --sb: var(--sk-3b); }
        #hp .sk-slab[data-l="4"] { --lvl: 0; --sa: var(--sk-4a); --sb: var(--sk-4b); }
        #hp .sk-slab[data-l="4"] .sk-face { background: #0b1226; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.14); color: #e6ecff; }
        #hp .sk-floor {
            position: absolute;
            left: 50%;
            top: calc(var(--u) * var(--h, 40) * 0.5);
            width: calc(var(--u) * 30);
            height: calc(var(--u) * 10);
            margin-top: calc(var(--u) * (var(--dy, 0) + 3.5));
            margin-left: calc(var(--u) * (var(--dx, 0) - 15));
            border-radius: 50%;
            background: var(--sk-floor);
            filter: blur(calc(var(--u) * 2.4));
        }

        /* Hero rig */
        #hp .sk-hero { padding-block: clamp(1.75rem, 5vh, 4.5rem) clamp(2rem, 5vh, 4.5rem); }
        #hp .sk-hero-grid { position: relative; z-index: 10; display: grid; grid-template-columns: minmax(0, 1fr); gap: 0; text-align: center; }
        #hp .sk-copy { display: contents; }
        #hp .sk-hero-grid > .sk-stage { order: 5; }
        #hp .sk-copy > .hp-hero-actions { order: 4; width: 100%; max-width: 30rem; }
        #hp .sk-copy > .sk-chips { order: 6; margin-top: 1.5rem; }
        #hp .sk-copy .hp-h1 { font-size: clamp(2.3rem, 9.6vw, 4rem); }
        #hp .sk-stage { position: relative; container-type: inline-size; width: 100%; min-width: 0; max-width: 46rem; margin-inline: auto; }
        #hp .sk-rig { --u: calc(100cqi / 37); --gap: 8; --dy: 9; --h: 43; position: relative; padding-top: calc(var(--u) * 43); }
        #hp .sk-tags { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.5rem; max-width: 22rem; margin-inline: auto; padding-top: 0.75rem; }
        #hp .sk-tag {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 0.5rem;
            min-height: 2.75rem;
            padding: 0.6rem 0.9rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 999px;
            background: var(--hp-bg-2);
            font-size: 0.95rem;
            line-height: 1.4;
            color: var(--hp-ink-2);
            transition: border-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }
        #hp .sk-tag b { font-family: var(--hp-mono); font-size: 0.72rem; font-variation-settings: normal; letter-spacing: 0.06em; color: var(--hp-ink-3); }
        #hp .sk-tag strong { font-variation-settings: 'wght' 700; color: var(--hp-ink); }
        #hp .sk-tag span { display: none; }
        #hp .sk-tag svg { width: 0.9rem; height: 0.9rem; align-self: center; color: var(--hp-blue); }
        #hp .sk-tags-hint { display: none; }
        #hp .sk-tag:hover { border-color: var(--hp-blue); transform: translateY(-1px); }
        @container (min-width: 600px) {
            #hp .sk-rig { --u: calc(100cqi / 53); --dx: -8; height: calc(var(--u) * 43); padding-top: 0; }
            #hp .sk-tags { display: block; max-width: none; padding: 0; }
            #hp .sk-tag {
                position: absolute;
                left: calc(50% + var(--u) * 10.1);
                max-width: calc(var(--u) * 16.3);
                top: calc(var(--u) * (21.5 + var(--dy) - 2.9 - var(--lvl) * var(--gap) * 0.829));
                display: grid;
                grid-template-columns: auto auto;
                justify-content: start;
                column-gap: 0.55rem;
                min-height: 0;
                padding: 0.35rem 0.5rem 0.35rem calc(var(--u) * 2.6);
                border: 0;
                border-radius: 0.6rem;
                background: none;
                translate: 0 -50%;
                font-size: clamp(0.8rem, calc(var(--u) * 0.95), 0.98rem);
                text-align: start;
            }
            #hp .sk-tag::before {
                content: "";
                position: absolute;
                left: 0;
                top: 50%;
                width: calc(var(--u) * 2.1);
                height: 1px;
                background: repeating-linear-gradient(90deg, var(--hp-ink-3) 0 4px, transparent 4px 8px);
                opacity: 0.7;
            }
            #hp .sk-tag strong { font-size: clamp(1rem, calc(var(--u) * 1.25), 1.3rem); letter-spacing: -0.02em; }
            #hp .sk-tag span { display: block; grid-column: 1 / -1; color: var(--hp-ink-3); text-wrap: balance; }
            #hp .sk-tag { grid-template-columns: auto auto auto; }
            #hp .sk-tag svg { width: 0.95rem; height: 0.95rem; transition: transform 0.2s ease; }
            #hp .sk-tag:hover,
            #hp .sk-tag:focus-visible { background: var(--hp-bg-2); box-shadow: 0 0 0 1px var(--hp-line-2), var(--hp-card-shadow); }
            #hp .sk-tag:hover svg { transform: translateY(2px); }
            #hp .sk-tags-hint { position: absolute; left: calc(50% + var(--u) * 12.7); top: calc(var(--u) * (21.5 + var(--dy) - 2.9 - 3 * var(--gap) * 0.829) - 3.4rem); display: block; font-family: var(--hp-mono); font-size: 0.7rem; font-variation-settings: normal; letter-spacing: 0.12em; text-transform: uppercase; color: var(--hp-ink-3); white-space: nowrap; }
            #hp .sk-tag:hover { transform: none; }
            #hp .sk-tag:hover strong,
            #hp .sk-tag:focus-visible strong { color: var(--hp-blue); }
            #hp .sk-tag[data-l="1"] { --lvl: 3; }
            #hp .sk-tag[data-l="2"] { --lvl: 2; }
            #hp .sk-tag[data-l="3"] { --lvl: 1; }
            #hp .sk-tag[data-l="4"] { --lvl: 0; }
        }
        @media (min-width: 1100px) {
            #hp .sk-hero-grid { grid-template-columns: minmax(0, min(35.5rem, 47%)) minmax(0, 1fr); align-items: center; gap: clamp(1rem, 2vw, 2.5rem); text-align: start; }
            #hp .sk-stage { max-width: none; }
            #hp .sk-copy { display: block; width: auto; margin: 0; text-align: start; }
            #hp .sk-copy > .sk-chips { margin-top: 1.4rem; }
            #hp .sk-copy .hp-h1 { font-size: min(4.2vw, 4.3rem, 9.4vh); line-height: 1.01; }
            #hp .sk-copy .hp-eyebrow { margin-bottom: clamp(0.9rem, 2.4vh, 2rem); }
            #hp .sk-copy .hp-sub { margin-inline: 0; max-width: 36rem; }
            #hp .sk-copy .hp-hero-actions { justify-content: flex-start; margin-inline: 0; }
            #hp .sk-copy .sk-chips { justify-content: flex-start; }
            #hp .sk-copy .sk-name { margin-inline: 0; }
            #hp .sk-hero .hp-hero-sky::after { background-position: 100% 0; -webkit-mask-image: radial-gradient(ellipse 38rem 26rem at 72% 16rem, #000 10%, transparent 72%); mask-image: radial-gradient(ellipse 38rem 26rem at 72% 16rem, #000 10%, transparent 72%); }
        }
        /* A short laptop screen: the same hero with less air, so the buttons stay on the first screen. */
        @media (min-width: 1100px) and (max-height: 780px) {
            #hp .sk-hero { padding-block: 1.5rem 2rem; }
            #hp .sk-copy .hp-sub { margin-top: 0.9rem; font-size: 1.075rem; line-height: 1.45; }
            #hp .sk-copy .sk-name { margin-top: 1.1rem; }
            #hp .sk-copy .hp-hero-actions { margin-top: 1.1rem; }
            #hp .sk-copy .sk-chips { margin-top: 0.9rem; }
        }
        /* A layer answers the label that names it. */
        #hp .sk-rig:has(.sk-tag[data-l="1"]:is(:hover, :focus-visible)) .sk-slab[data-l="1"],
        #hp .sk-rig:has(.sk-tag[data-l="2"]:is(:hover, :focus-visible)) .sk-slab[data-l="2"],
        #hp .sk-rig:has(.sk-tag[data-l="3"]:is(:hover, :focus-visible)) .sk-slab[data-l="3"],
        #hp .sk-rig:has(.sk-tag[data-l="4"]:is(:hover, :focus-visible)) .sk-slab[data-l="4"] { --out: 4; transition-duration: 0.45s; }
        /* On load the slabs lie as one and then come apart, top first. */
        html.es-anim #hp .sk-rig.sk-unstack:not(.is-revealed) .sk-slab { --lvl: 0; }
        html.es-anim #hp .sk-rig.sk-unstack .sk-slab[data-l="1"] { transition-delay: 0.25s; }
        html.es-anim #hp .sk-rig.sk-unstack .sk-slab[data-l="2"] { transition-delay: 0.36s; }
        html.es-anim #hp .sk-rig.sk-unstack .sk-slab[data-l="3"] { transition-delay: 0.47s; }
        html.es-anim #hp .sk-rig.sk-unstack.is-settled .sk-slab { transition-delay: 0s; }
        html.es-anim #hp .sk-rig.sk-unstack:not(.is-revealed) :is(.sk-tag, .sk-tags-hint) { opacity: 0; }
        #hp .sk-rig.sk-unstack .sk-tag { transition: opacity 0.6s ease 0.9s, border-color 0.2s ease, transform 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease; }
        #hp .sk-rig.sk-unstack .sk-tags-hint { transition: opacity 0.6s ease 0.9s; }
        /* The shared reveal fades and lifts what it watches; the rig only comes apart. */
        html.es-anim #hp .sk-rig[data-reveal] { opacity: 1; transform: none; filter: none; }

        /* What is written on each slab (it is read at an angle, so it is large and little). */
        #hp .sk-f { position: absolute; inset: 0; padding: calc(var(--u) * 1.7) calc(var(--u) * 1.9); }
        #hp .sk-f-brand { display: grid; grid-template-columns: auto minmax(0, 1fr); align-content: center; align-items: center; gap: calc(var(--u) * 0.9) calc(var(--u) * 1.3); }
        #hp .sk-mark {
            display: inline-flex;
            flex: none;
            align-items: center;
            justify-content: center;
            width: calc(var(--u) * 5.6);
            aspect-ratio: 1;
            border-radius: calc(var(--u) * 1.4);
            background: linear-gradient(135deg, #2f66ea, #0aa5c4);
            color: #fff;
            font-size: calc(var(--u) * 2.15);
            font-weight: 700;
            font-variation-settings: 'wght' 850;
            letter-spacing: -0.03em;
            line-height: 1;
        }
        #hp .sk-f-brand .sk-word { display: block; overflow: hidden; font-size: calc(var(--u) * 2.7); font-weight: 700; font-variation-settings: 'wght' 840; letter-spacing: -0.04em; line-height: 1.05; white-space: nowrap; text-overflow: ellipsis; }
        #hp .sk-f-brand .sk-dom { display: block; overflow: hidden; margin-top: calc(var(--u) * 0.35); font-family: var(--hp-mono); font-size: calc(var(--u) * 1.3); font-variation-settings: normal; color: var(--hp-ink-3); white-space: nowrap; text-overflow: ellipsis; }
        #hp .sk-f-bars { grid-column: 1 / -1; display: flex; gap: calc(var(--u) * 0.6); }
        #hp .sk-f-bars b { height: calc(var(--u) * 0.6); border-radius: 99px; background: var(--hp-line-2); }
        #hp .sk-f-bars b:first-child { width: 34%; background: linear-gradient(90deg, #2f66ea, #38bdf8); }
        #hp .sk-f-bars b:nth-child(2) { width: 16%; }
        #hp .sk-f-bars b:nth-child(3) { width: 24%; }
        #hp .sk-f-ten { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); align-content: end; gap: calc(var(--u) * 0.7); padding-bottom: calc(var(--u) * 1.3); }
        #hp .sk-f-ten span { display: flex; align-items: center; gap: calc(var(--u) * 0.6); overflow: hidden; padding: calc(var(--u) * 0.7) calc(var(--u) * 0.8); border-radius: calc(var(--u) * 0.9); background: var(--sk-top-2); font-family: var(--hp-mono); font-size: calc(var(--u) * 1.25); font-variation-settings: normal; color: var(--hp-ink-2); white-space: nowrap; }
        #hp .sk-f-ten span::before { content: ""; flex: none; width: calc(var(--u) * 1.15); aspect-ratio: 1; border-radius: 28%; background: linear-gradient(135deg, #2f66ea, #38bdf8); }
        #hp .sk-f-ten span:last-child { color: var(--hp-ink-3); }
        #hp .sk-f-ten span:last-child::before { background: none; box-shadow: inset 0 0 0 1.5px var(--hp-line-2); }
        #hp .sk-f-bill { display: grid; align-content: end; gap: calc(var(--u) * 0.7); padding-bottom: calc(var(--u) * 1.3); }
        #hp .sk-f-bill > div { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: calc(var(--u) * 0.9); }
        #hp .sk-f-bill > div > span { padding: calc(var(--u) * 0.6) 0; border-radius: calc(var(--u) * 1); box-shadow: inset 0 0 0 1px var(--sk-top-line); text-align: center; }
        #hp .sk-f-bill small { display: block; font-size: calc(var(--u) * 1.05); font-weight: 700; font-variation-settings: 'wght' 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--hp-ink-3); }
        #hp .sk-f-bill b { display: block; margin-top: calc(var(--u) * 0.2); font-size: calc(var(--u) * 2.5); font-weight: 700; font-variation-settings: 'wght' 850; letter-spacing: -0.03em; line-height: 1; }
        #hp .sk-f-infra { display: grid; align-content: end; gap: calc(var(--u) * 1.1); padding-bottom: calc(var(--u) * 1.8); }
        #hp .sk-f-infra p { display: flex; align-items: center; gap: calc(var(--u) * 0.9); font-family: var(--hp-mono); font-size: calc(var(--u) * 1.3); font-variation-settings: normal; color: #cfd8f5; white-space: nowrap; }
        #hp .sk-led { flex: none; width: calc(var(--u) * 1); aspect-ratio: 1; border-radius: 50%; background: #34d399; box-shadow: 0 0 calc(var(--u) * 0.9) rgba(52, 211, 153, 0.9); }
        #hp .sk-f-infra em { margin-inline-start: auto; padding: calc(var(--u) * 0.35) calc(var(--u) * 0.9); border-radius: 99px; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.24); font-size: calc(var(--u) * 1.05); font-style: normal; color: #cfd8f5; }
        #hp .sk-f-infra div { display: flex; gap: calc(var(--u) * 0.6); }
        #hp .sk-f-infra div b { height: calc(var(--u) * 0.6); border-radius: 99px; background: rgba(255, 255, 255, 0.14); }
        #hp .sk-f-infra div b:first-child { width: 22%; background: rgba(52, 211, 153, 0.7); }
        #hp .sk-f-infra div b:nth-child(2) { width: 38%; }
        #hp .sk-f-infra div b:nth-child(3) { width: 14%; }

        /* Name your platform */
        #hp .sk-name { max-width: 30rem; margin: clamp(1.4rem, 3vh, 2rem) auto 0; text-align: start; }
        #hp .sk-name > label { margin-bottom: 0.6rem; }
        #hp .sk-name-hint { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.2rem 1rem; margin-top: 0.6rem; font-size: 0.95rem; color: var(--hp-ink-2); }
        #hp .sk-name-box {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.6rem 1rem 0.6rem 0.6rem;
            border: 1.5px solid var(--hp-line-2);
            cursor: text;
            border-radius: 1.1rem;
            background: var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        #hp .sk-name-box:focus-within { border-color: var(--hp-blue); box-shadow: 0 0 0 4px rgba(78, 129, 250, 0.3); }
        #hp .sk-name-box .sk-mark { --u: 0.5rem; width: 2.75rem; border-radius: 0.8rem; font-size: 1.05rem; }
        #hp .sk-name-box input {
            flex: 1 1 0;
            width: 0;
            min-width: 0;
            padding: 0;
            border: 0;
            background: transparent;
            box-shadow: none;
            outline: 0;
            font-family: inherit;
            font-size: 1.2rem;
            font-weight: 700;
            font-variation-settings: 'wght' 760;
            letter-spacing: -0.02em;
            color: var(--hp-ink);
        }
        #hp .sk-name-box input::placeholder { color: var(--hp-ink-3); opacity: 1; font-variation-settings: 'wght' 500; }
        #hp .sk-name-box input:focus { border: 0; box-shadow: none; outline: 0; }
        #hp .sk-name-box:hover { border-color: var(--hp-blue); }
        #hp .sk-name-box > svg { flex: none; width: 1.2rem; height: 1.2rem; color: var(--hp-ink-3); }
        #hp .sk-name-box:focus-within > svg { color: var(--hp-blue); }
        #hp .sk-name-dom { font-family: var(--hp-mono); font-size: 0.86rem; font-variation-settings: normal; color: var(--hp-blue); overflow-wrap: anywhere; }
        #hp .sk-chips { display: grid; grid-template-columns: repeat(2, max-content); justify-content: center; gap: 0.4rem 1.6rem; margin-top: 1.4rem; font-size: 0.92rem; color: var(--hp-ink-2); text-align: start; }
        #hp .sk-chips li { display: inline-flex; align-items: center; gap: 0.4rem; }
        #hp .sk-chips svg { width: 0.95rem; height: 0.95rem; color: var(--hp-blue); }

        /* --------------------------------------------------------------
           Chapters: one layer each. The mark is the stack with that
           layer drawn out of it.
           -------------------------------------------------------------- */
        #hp .sk-intro { padding-block: clamp(4rem, 8vw, 7rem) 0; }
        #hp .sk-ch { position: relative; padding-block: clamp(3rem, 6vw, 5.5rem) clamp(3.5rem, 7vw, 6.5rem); }
        #hp .sk-ch-head {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            align-items: center;
            gap: 0.4rem clamp(1rem, 3vw, 2.5rem);
            padding-top: clamp(1.75rem, 3vw, 2.5rem);
            border-top: 1px solid var(--hp-line-2);
        }
        #hp .sk-glyph { --u: 3px; --gap: 4.2; --thick: 0.22; --h: 36; --dy: 6; --dx: -3; position: relative; flex: none; width: calc(var(--u) * 44); height: calc(var(--u) * 36); }
        #hp .sk-glyph .sk-slab { --sa: var(--sk-0a); --sb: var(--sk-0b); }
        #hp .sk-glyph .sk-face { background: var(--hp-bg-2); }
        #hp .sk-glyph[data-at="1"] .sk-slab[data-l="1"] { --out: 8; --sa: var(--sk-1a); --sb: var(--sk-1b); }
        #hp .sk-glyph[data-at="2"] .sk-slab[data-l="2"] { --out: 8; --sa: var(--sk-2a); --sb: var(--sk-2b); }
        #hp .sk-glyph[data-at="3"] .sk-slab[data-l="3"] { --out: 8; --sa: var(--sk-3a); --sb: var(--sk-3b); }
        #hp .sk-glyph[data-at="4"] .sk-slab[data-l="4"] { --out: 8; --sa: var(--sk-4a); --sb: var(--sk-4b); }
        #hp .sk-glyph[data-at="1"] .sk-slab[data-l="1"] .sk-face { background: #dbe6ff; }
        #hp .sk-glyph[data-at="2"] .sk-slab[data-l="2"] .sk-face { background: #d5f1fc; }
        #hp .sk-glyph[data-at="3"] .sk-slab[data-l="3"] .sk-face { background: #fdebb3; }
        #hp .sk-glyph[data-at="4"] .sk-slab[data-l="4"] .sk-face { background: #0b1226; }
        #hp .sk-glyph .sk-slab[data-l="4"] .sk-face { box-shadow: inset 0 0 0 1px var(--sk-top-line); }
        .dark #hp .sk-glyph[data-at="1"] .sk-slab[data-l="1"] .sk-face { background: #2a4691; }
        .dark #hp .sk-glyph[data-at="2"] .sk-slab[data-l="2"] .sk-face { background: #14587f; }
        .dark #hp .sk-glyph[data-at="3"] .sk-slab[data-l="3"] .sk-face { background: #7a5410; }
        .dark #hp .sk-glyph[data-at="4"] .sk-slab[data-l="4"] { --sa: #6c84d6; --sb: #3b4d8c; }
        .dark #hp .sk-glyph[data-at="4"] .sk-slab[data-l="4"] .sk-face { background: #4a5fa8; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.45); }
        html.es-anim #hp [data-reveal]:not(.is-revealed) .sk-glyph .sk-slab { --out: 0; }
        #hp .sk-glyph .sk-slab { transition-delay: 0.25s; }
        #hp .sk-ch-no { display: flex; align-items: center; gap: 0.7rem; font-family: var(--hp-mono); font-size: 0.8rem; font-variation-settings: normal; letter-spacing: 0.14em; text-transform: uppercase; color: var(--hp-ink-3); }
        #hp .sk-ch-no b { color: var(--hp-ink); font-variation-settings: normal; }
        #hp .sk-ch-title { font-size: clamp(3.2rem, 9vw, 7.5rem); font-weight: 700; font-variation-settings: 'wght' 860; letter-spacing: -0.055em; line-height: 0.92; }
        #hp .sk-ch-say { grid-column: 1 / -1; max-width: 36rem; margin-top: 0.75rem; font-size: clamp(1.1rem, 0.5vw + 1rem, 1.35rem); line-height: 1.45; color: var(--hp-ink-2); }
        @media (min-width: 900px) {
            #hp .sk-glyph { --u: 4.6px; }
            #hp .sk-ch-head { grid-template-columns: auto minmax(0, 1fr) minmax(0, 24rem); }
            #hp .sk-ch-say { grid-column: auto; margin-top: 0; justify-self: end; text-align: end; }
        }
        #hp .sk-ch[data-l="1"] .sk-ch-title { color: var(--hp-blue); }
        #hp .sk-ch[data-l="2"] .sk-ch-title { color: #0b84c6; }
        .dark #hp .sk-ch[data-l="2"] .sk-ch-title { color: #5cc8f4; }
        #hp .sk-ch[data-l="3"] .sk-ch-title { color: var(--sk-amber); }
        #hp .sk-block { margin-top: clamp(2.5rem, 5vw, 4.5rem); }
        #hp .sk-block + .sk-block { margin-top: clamp(4rem, 8vw, 7.5rem); }
        #hp .sk-duo { display: grid; grid-template-columns: minmax(0, 1fr); align-items: center; gap: clamp(2rem, 4vw, 4.5rem); }
        @media (min-width: 1024px) {
            #hp .sk-duo { grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr); }
            #hp .sk-duo.is-even { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
        }
        #hp .sk-h3 { font-size: clamp(1.9rem, 2.4vw + 1rem, 3.1rem); font-weight: 700; font-variation-settings: 'wght' 820; letter-spacing: -0.04em; line-height: 1.04; text-wrap: balance; }
        #hp .sk-h4 { font-size: clamp(1.3rem, 0.7vw + 1.1rem, 1.6rem); font-weight: 700; font-variation-settings: 'wght' 790; letter-spacing: -0.03em; line-height: 1.12; }
        #hp .sk-p { margin-top: 0.9rem; font-size: 1.075rem; line-height: 1.6; color: var(--hp-ink-2); text-wrap: pretty; }
        #hp .sk-small { font-size: 0.92rem; line-height: 1.55; color: var(--hp-ink-3); }
        #hp .sk-module-no { display: block; margin-bottom: 0.8rem; font-family: var(--hp-mono); font-size: 0.74rem; font-variation-settings: normal; letter-spacing: 0.14em; text-transform: uppercase; color: var(--hp-blue); }

        /* A stage: the ground an object stands on (the house's seven columns under it). */
        #hp .sk-stg {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            border: 1px solid var(--hp-line);
            border-radius: 1.9rem;
            background: radial-gradient(34rem 16rem at 50% 0%, var(--hp-glow), transparent 70%), var(--sk-stage);
        }
        #hp .sk-stg::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background-image: linear-gradient(var(--hp-line) 1px, transparent 1px), linear-gradient(90deg, var(--hp-line) 1px, transparent 1px);
            background-size: calc(100% / 7) 5.5rem;
            -webkit-mask-image: radial-gradient(ellipse at 50% 0%, #000 10%, transparent 75%);
            mask-image: radial-gradient(ellipse at 50% 0%, #000 10%, transparent 75%);
        }
        #hp .sk-cap { margin-top: 0.9rem; font-family: var(--hp-mono); font-size: 0.74rem; font-variation-settings: normal; letter-spacing: 0.08em; text-transform: uppercase; color: var(--hp-ink-3); }

        /* Browser chrome for the mocks */
        #hp .sk-win { overflow: hidden; border: 1px solid var(--hp-line-2); border-radius: 1rem; background: var(--hp-bg-2); box-shadow: var(--hp-pop-shadow); }
        #hp .sk-win-bar { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.85rem; border-bottom: 1px solid var(--hp-line); background: var(--hp-bg); }
        #hp .sk-dots { display: flex; flex: none; gap: 0.35rem; }
        #hp .sk-dots i { width: 0.6rem; height: 0.6rem; border-radius: 50%; background: var(--hp-line-2); }
        #hp .sk-url { flex: 1 1 0; min-width: 0; overflow: hidden; padding: 0.3rem 0.75rem; border-radius: 0.5rem; background: var(--hp-bg-2); box-shadow: inset 0 0 0 1px var(--hp-line); font-family: var(--hp-mono); font-size: 0.78rem; font-variation-settings: normal; color: var(--hp-ink-2); white-space: nowrap; text-overflow: ellipsis; }
        #hp .sk-url s { color: var(--hp-ink-3); text-decoration: none; }

        @media (max-width: 639.98px) {
            #hp .sk-atsize { --lz: 1.6; }
            #hp .sk-ch-no span { display: none; }
            #hp .sk-url { font-size: 0.72rem; }
            #hp .sk-bigbar .sk-url { font-size: 0.86rem; white-space: normal; overflow-wrap: anywhere; }
            #hp .sk-bigbar .sk-url s { display: none; }
            #hp .sk-first strong { font-size: 0.86rem; }
        }
        /* ---- 01 Brand: the three places your name goes ---- */
        #hp .sk-surfaces { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; padding: clamp(1rem, 2.5vw, 2rem); }
        @media (min-width: 720px) {
            #hp .sk-surfaces { grid-template-columns: minmax(0, 0.82fr) minmax(0, 1.18fr); grid-template-rows: auto auto; }
            #hp .sk-surface.is-signin { grid-row: 1 / span 2; }
        }
        #hp .sk-surface { display: flex; flex-direction: column; min-width: 0; }
        #hp .sk-surface .sk-win { flex: 1 1 auto; display: flex; flex-direction: column; }
        #hp .sk-surface .sk-cap { display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem 0.75rem; }
        #hp .sk-cap code { padding: 0.15rem 0.45rem; border-radius: 0.4rem; background: var(--hp-bg-2); box-shadow: inset 0 0 0 1px var(--hp-line-2); color: var(--hp-blue); letter-spacing: 0; text-transform: none; }
        /* Sign-in page, cut on the diagonal: your light logo on one side, your dark one on the other. */
        #hp .sk-signin { position: relative; flex: 1 1 auto; min-height: 15rem; overflow: hidden; background: #f4f6fb; }
        #hp .sk-signin-in { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.9rem; padding: 1.25rem; }
        #hp .sk-signin-night { position: absolute; inset: 0; background: #0a0f1c; clip-path: polygon(62% 0, 100% 0, 100% 100%, 38% 100%); transition: clip-path 0.6s cubic-bezier(0.22, 1, 0.36, 1); }
        #hp .sk-surface.is-signin:hover .sk-signin-night { clip-path: polygon(30% 0, 100% 0, 100% 100%, 6% 100%); }
        #hp .sk-logo { display: inline-flex; align-items: center; gap: 0.65rem; max-width: 100%; color: #0a1020; }
        #hp .sk-logo .sk-mark { --u: 0.5rem; width: 2.6rem; border-radius: 0.75rem; font-size: 1rem; }
        #hp .sk-logo b { overflow: hidden; font-size: 1.5rem; font-variation-settings: 'wght' 840; letter-spacing: -0.04em; white-space: nowrap; text-overflow: ellipsis; }
        #hp .sk-signin-night .sk-logo { color: #fff; }
        #hp .sk-signin-night .sk-mark { background: linear-gradient(135deg, #7da5ff, #5eead4); color: #070a14; }
        #hp .sk-form { display: grid; gap: 0.5rem; width: min(100%, 15rem); padding: 0.9rem; border-radius: 0.9rem; background: #fff; box-shadow: 0 1px 2px rgba(10, 16, 32, 0.06), 0 14px 30px -18px rgba(10, 16, 32, 0.35); }
        #hp .sk-form i { height: 1.9rem; border-radius: 0.5rem; background: #eef1f8; box-shadow: inset 0 0 0 1px rgba(10, 16, 32, 0.08); }
        #hp .sk-form b { height: 2rem; border-radius: 0.5rem; background: #2f66ea; }
        #hp .sk-signin-night .sk-form { background: #131b33; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.1); }
        #hp .sk-signin-night .sk-form i { background: #0a0f1c; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.1); }
        #hp .sk-signin-night .sk-form b { background: #3565e0; }
        /* A customer's page on your domain */
        #hp .sk-addr { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.1rem; }
        #hp .sk-addr .sk-url { padding: 0.65rem 0.9rem; font-size: clamp(0.78rem, 0.5vw + 0.62rem, 0.95rem); font-weight: 700; color: var(--hp-ink); }
        /* The free tier's footer: your link, not ours */
        #hp .sk-strip-page { flex: 1 1 auto; display: flex; flex-direction: column; justify-content: flex-end; min-height: 6.5rem; background: linear-gradient(var(--hp-bg-2), var(--hp-bg-2)); }
        #hp .sk-strip-rows { display: grid; gap: 0.45rem; padding: 0.9rem 1.1rem; }
        #hp .sk-strip-rows i { height: 0.55rem; border-radius: 99px; background: var(--hp-line); }
        #hp .sk-strip-rows i:first-child { width: 62%; }
        #hp .sk-strip-rows i:nth-child(2) { width: 40%; }
        #hp .sk-strip { padding: 0.85rem 1rem; background: #1b2338; color: #f5f9fe; font-size: 0.92rem; text-align: center; }
        #hp .sk-strip b { color: #fff; font-variation-settings: 'wght' 700; text-decoration: underline; text-underline-offset: 0.2em; }

        /* The catch, at actual size */
        #hp .sk-catch { padding: clamp(1.5rem, 4vw, 3.5rem); border: 1px solid var(--hp-line); border-radius: 2.2rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
        #hp .sk-asks { display: grid; gap: 1.25rem; margin-top: 1.75rem; }
        #hp .sk-ask { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 1rem; }
        #hp .sk-ask .hp-ico { background: rgba(78, 129, 250, 0.14); color: var(--hp-blue); }
        #hp .sk-ask.is-link .hp-ico { background: var(--sk-amber-soft); color: var(--sk-amber); }
        #hp .sk-ask p { margin-top: 0.25rem; font-size: 0.98rem; line-height: 1.55; color: var(--hp-ink-2); }
        #hp .sk-pills { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-block: 1.5rem; }
        #hp .sk-catch .hp-actions { margin-top: 0; gap: 0.75rem; }
        #hp .sk-catch-note { margin-top: 1.5rem; font-size: 0.95rem; color: var(--hp-ink-3); }
        #hp .sk-atsize { position: relative; }
        #hp .sk-corner { position: relative; overflow: hidden; border: 1px solid var(--hp-line-2); border-radius: 1.25rem 1.25rem 0.4rem 1.25rem; background: #fff; color: #0a1020; box-shadow: var(--hp-pop-shadow); }
        #hp .sk-corner-in { padding: 1.25rem 1.25rem 0; }
        #hp .sk-corner-head { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
        #hp .sk-corner-head .sk-mark { --u: 0.5rem; width: 2.5rem; border-radius: 0.7rem; font-size: 0.95rem; }
        #hp .sk-corner-head > div { min-width: 0; }
        #hp .sk-corner-head strong { display: block; font-size: 1rem; letter-spacing: -0.01em; }
        #hp .sk-corner-head small { display: block; overflow: hidden; font-family: var(--hp-mono); font-size: 0.72rem; font-variation-settings: normal; color: #56617c; text-overflow: ellipsis; }
        #hp .sk-corner-head em { margin-inline-start: auto; padding: 0.3rem 0.8rem; border-radius: 99px; background: #2b5fe3; color: #fff; font-size: 0.74rem; font-style: normal; font-variation-settings: 'wght' 700; }
        #hp .sk-ev { display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem; border: 1px solid rgba(10, 16, 32, 0.1); border-radius: 0.8rem; }
        #hp .sk-ev + .sk-ev { margin-top: 0.6rem; }
        #hp .sk-ev > span { flex: none; width: 2.9rem; padding: 0.3rem 0; border-radius: 0.6rem; background: #e8eeff; text-align: center; }
        #hp .sk-ev > span small { display: block; font-size: 0.6rem; font-variation-settings: 'wght' 800; letter-spacing: 0.06em; text-transform: uppercase; color: #1e40af; }
        #hp .sk-ev > span b { display: block; font-size: 1.1rem; font-variation-settings: 'wght' 850; line-height: 1; }
        #hp .sk-ev strong { display: block; font-size: 0.95rem; }
        #hp .sk-ev div small { font-size: 0.8rem; color: #56617c; }
        /* The real chip, with the classes the guest layout gives it, so "actual size" is the truth. */
        #hp .sk-corner-foot { display: flex; justify-content: flex-end; padding: 7.2rem 1rem 1rem; }
        #hp .sk-chip { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; border-radius: 9999px; background: rgba(255, 255, 255, 0.8); box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.05); font-family: ui-sans-serif, system-ui, sans-serif; font-size: 0.75rem; font-weight: 500; font-variation-settings: normal; letter-spacing: 0; line-height: 1rem; color: #4b5563; }
        #hp .sk-chip i { display: flex; align-items: center; justify-content: center; width: 1rem; height: 1rem; border-radius: 5px; background: linear-gradient(135deg, #4e81fa, #22d3ee); color: #fff; font-size: 8px; font-style: normal; font-weight: 900; line-height: 1; }
        #hp .sk-loupe {
            position: absolute;
            left: 0;
            top: 0;
            z-index: 3;
            width: min(15.5rem, 82%);
            height: 5.6rem;
            overflow: hidden;
            border-radius: 1.5rem;
            border: 3px solid #2f66ea;
            background: #fff;
            box-shadow: 0 0 0 6px rgba(47, 102, 234, 0.16), 0 24px 44px -14px rgba(10, 16, 32, 0.5);
            pointer-events: none;
            transform: translate(calc(var(--lx, 0px) - 50%), calc(var(--ly, 0px) - 50%));
            transition: transform 0.5s cubic-bezier(0.22, 1, 0.36, 1);
        }
        #hp .sk-atsize.is-live .sk-loupe { transition: none; }
        #hp .sk-loupe > div { position: absolute; left: 50%; top: 50%; transform-origin: 0 0; transform: scale(var(--lz, 1.9)) translate(calc(var(--fx, 0px) * -1), calc(var(--fy, 0px) * -1)); }
        #hp .sk-atsize:not(.is-ready) .sk-loupe { display: none; }
        #hp .sk-atsize figcaption { margin-top: 1.1rem; font-size: 0.98rem; color: var(--hp-ink-2); text-align: center; }
        #hp .sk-atsize figcaption small { display: block; margin-top: 0.2rem; font-size: 0.86rem; color: var(--hp-ink-3); }
        #hp .sk-by-finger { display: none; }
        #hp .sk-atsize:not(.is-ready) :is(.sk-by-pointer, .sk-by-finger) { display: none; }
        @media (hover: none), (pointer: coarse) {
            #hp .sk-by-pointer { display: none; }
            #hp .sk-by-finger { display: inline; }
        }

        /* ---- 02 Tenants ---- */
        #hp .sk-bigbar { padding: clamp(1.25rem, 3vw, 2.25rem); }
        #hp .sk-bigbar .sk-win-bar { padding: clamp(0.8rem, 1.6vw, 1.2rem); border: 0; border-radius: 1rem; background: var(--hp-bg-2); box-shadow: var(--hp-pop-shadow); }
        #hp .sk-bigbar .sk-url { padding: 0.75rem 1rem; font-size: clamp(0.92rem, 1.6vw + 0.5rem, 1.5rem); font-weight: 700; color: var(--hp-ink); box-shadow: inset 0 0 0 1px var(--hp-line-2); background: var(--hp-bg); }
        #hp .sk-rows { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.6rem; max-height: 9.6rem; margin-top: clamp(1.25rem, 2.5vw, 2rem); padding-top: 3px; overflow: hidden; -webkit-mask-image: linear-gradient(to bottom, #000 58%, transparent); mask-image: linear-gradient(to bottom, #000 58%, transparent); }
        #hp .sk-ten { display: inline-flex; align-items: center; gap: 0.6rem; padding: 0.55rem 0.6rem 0.55rem 0.95rem; border: 1px solid var(--hp-line-2); border-radius: 999px; background: var(--hp-bg-2); font-family: var(--hp-mono); font-size: 0.86rem; font-variation-settings: normal; color: var(--hp-ink-2); white-space: nowrap; }
        #hp .sk-ten { max-width: 100%; }
        #hp .sk-ten > span { min-width: 0; overflow: hidden; text-overflow: ellipsis; }
        #hp .sk-ten[data-tenant] { transition: border-color 0.2s ease, color 0.2s ease, transform 0.2s ease; }
        html.sk-js #hp .sk-ten[data-tenant] { cursor: pointer; }
        #hp .sk-ten.is-picked { border-color: var(--hp-blue); color: var(--hp-ink); transform: translateY(-1px); }
        #hp .sk-url [data-sk-tenant] { color: var(--hp-blue); font-variation-settings: normal; }
        #hp .sk-ten b { padding: 0.15rem 0.55rem; border-radius: 99px; background: rgba(78, 129, 250, 0.14); color: var(--hp-blue); font-family: var(--hp-display); font-size: 0.72rem; font-variation-settings: 'wght' 700; }
        #hp .sk-ten b.is-ent { background: var(--sk-amber-soft); color: var(--sk-amber); }
        #hp .sk-ten b.is-free { background: var(--hp-line); color: var(--hp-ink-2); }
        #hp .sk-scale { display: grid; grid-template-columns: minmax(0, 1fr); align-items: baseline; gap: 0.5rem 2rem; margin-top: 1.25rem; }
        @media (min-width: 900px) {
            #hp .sk-scale { grid-template-columns: auto minmax(0, 1fr); }
        }
        /* Real screens of the product each customer gets */
        #hp .sk-shots { display: grid; grid-template-columns: minmax(0, 1fr); gap: clamp(1.25rem, 2.5vw, 2rem); margin-top: clamp(2rem, 4vw, 3.25rem); }
        @media (min-width: 900px) {
            #hp .sk-shots { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        #hp .sk-shot { display: flex; flex-direction: column; padding: clamp(1rem, 1.8vw, 1.5rem); border: 1px solid var(--hp-line); border-radius: 1.9rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
        #hp .sk-shot .sk-win { box-shadow: none; }
        #hp .sk-shot-crop { position: relative; overflow: hidden; aspect-ratio: 62 / 35; background: var(--hp-bg); }
        #hp .sk-shot-crop img { position: absolute; max-width: none; }
        #hp .sk-shot.is-admin .sk-shot-crop img { left: 0; top: 0; width: 206.45%; margin-left: -48.39%; margin-top: -12.9%; }
        #hp .sk-shot.is-guest .sk-shot-crop img { left: 0; top: 0; width: 147.81%; margin-left: -23.9%; margin-top: -30.95%; }
        #hp .sk-shot.is-admin .sk-shot-crop { -webkit-mask-image: linear-gradient(to right, #000 80%, transparent), linear-gradient(to bottom, #000 72%, transparent); -webkit-mask-composite: source-in; mask-image: linear-gradient(to right, #000 80%, transparent), linear-gradient(to bottom, #000 72%, transparent); mask-composite: intersect; }
        #hp .sk-shot-crop .is-night { display: none; }
        .dark #hp .sk-shot-crop .is-day { display: none; }
        .dark #hp .sk-shot-crop .is-night { display: block; }
        #hp .sk-shot-body { display: flex; flex: 1 1 auto; flex-direction: column; padding: 1.4rem 0.4rem 0.25rem; }
        #hp .sk-shot-body .sk-p { margin-bottom: 1.4rem; }
        #hp .sk-shot-body .hp-btn { margin-top: auto; align-self: flex-start; }
        #hp .sk-dock { margin-top: clamp(1.75rem, 3vw, 2.5rem); }
        #hp .sk-dock ul { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.6rem; max-width: 52rem; margin-inline: auto; }
        #hp .sk-feat { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.55rem 1rem; border: 1px solid var(--hp-line-2); border-radius: 999px; background: var(--hp-bg-2); font-size: 0.9rem; font-variation-settings: 'wght' 600; color: var(--hp-ink-2); white-space: nowrap; }
        #hp .sk-feat svg { width: 1rem; height: 1rem; color: var(--hp-blue); }
        #hp .sk-dock-cap { margin-top: 0.9rem; text-align: center; }
        /* Federation */
        #hp .sk-fed { position: relative; aspect-ratio: 17 / 10; }
        #hp .sk-fed svg { position: absolute; inset: 0; width: 100%; height: 100%; }
        #hp .sk-fed .sk-line { stroke: var(--hp-line-2); stroke-width: 1.5; stroke-dasharray: 5 7; }
        #hp .sk-pad { --w: clamp(2.1rem, 5.6vw, 3.1rem); --sa: var(--sk-2a); --sb: var(--sk-2b); position: absolute; left: var(--x); top: var(--y); width: var(--w); height: calc(var(--w) * 0.64); margin: calc(var(--w) * -0.32) 0 0 calc(var(--w) * -0.5); }
        #hp .sk-pad > i,
        #hp .sk-pad > b { position: absolute; inset: 0; border-radius: 22%; }
        #hp .sk-pad > i { background: linear-gradient(to bottom right, var(--sa) 49.4%, var(--sb) 50.6%); transform: translateY(calc(var(--w) * var(--k) * 0.03)) matrix(0.7071, -0.3954, 0.7071, 0.3954, 0, 0); }
        #hp .sk-pad > i:nth-of-type(1) { --k: 1; }
        #hp .sk-pad > i:nth-of-type(2) { --k: 2; }
        #hp .sk-pad > i:nth-of-type(3) { --k: 3; }
        #hp .sk-pad > i:nth-of-type(4) { --k: 4; }
        #hp .sk-pad > b { background: var(--sk-top); box-shadow: inset 0 0 0 1px var(--sk-top-line); transform: matrix(0.7071, -0.3954, 0.7071, 0.3954, 0, 0); }
        #hp .sk-pad.is-you { --w: clamp(2.8rem, 7.4vw, 4.2rem); --sa: var(--sk-1a); --sb: var(--sk-1b); }
        #hp .sk-pad.is-you > b { background: #dbe6ff; }
        .dark #hp .sk-pad.is-you > b { background: #2a4691; }
        #hp .sk-pad.is-hub { --w: clamp(3.6rem, 9.6vw, 5.6rem); --sa: var(--sk-4a); --sb: var(--sk-4b); }
        #hp .sk-pad.is-hub > b { background: #0b1226; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.2); }
        #hp .sk-fed-label { position: absolute; padding: 0.25rem 0.7rem; border: 1px solid var(--hp-line-2); border-radius: 99px; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); font-family: var(--hp-mono); font-size: clamp(0.66rem, 1.5vw, 0.8rem); font-variation-settings: normal; color: var(--hp-ink-2); white-space: nowrap; translate: -50% 0; }
        #hp .sk-fed-label.is-you { max-width: 42%; overflow: hidden; border-color: var(--hp-blue); color: var(--hp-blue); font-weight: 700; text-overflow: ellipsis; }
        #hp .sk-fed-dot { opacity: 0; }
        @supports (offset-path: path('M 0 0 H 10')) {
            #hp .sk-fed-dot { offset-rotate: 0deg; filter: drop-shadow(0 0 4px rgba(56, 189, 248, 0.9)); animation: sk-fed 4.5s ease-in-out infinite; animation-delay: var(--fd, 0s); }
            #hp .sk-fed-dot.is-back { animation-direction: reverse; }
        }
        @keyframes sk-fed {
            0% { offset-distance: 0%; opacity: 0; }
            12%, 82% { opacity: 1; }
            100% { offset-distance: 100%; opacity: 0; }
        }

        /* ---- 03 Billing ---- */
        #hp .sk-trio { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; }
        @media (min-width: 900px) {
            #hp .sk-trio { grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr); }
            #hp .sk-mod.is-plans { grid-row: span 2; }
        }
        #hp .sk-mod.is-side { flex-direction: row; align-items: center; justify-content: space-between; gap: 1.5rem; }
        #hp .sk-mod.is-side .sk-p { margin-bottom: 1.25rem; }
        #hp .sk-mod { display: flex; flex-direction: column; padding: clamp(1.4rem, 2.4vw, 2rem); }
        #hp .sk-mod .sk-p { margin-bottom: 1.5rem; font-size: 1rem; }
        #hp .sk-mod-obj { margin-top: auto; }
        #hp .sk-mod.is-plans .sk-matrix { flex: 1 1 auto; grid-auto-rows: 1fr; margin-top: 0; }
        #hp .sk-matrix > * { display: flex; align-items: center; justify-content: center; }
        #hp .sk-matrix > .is-name { justify-content: flex-start; }
        #hp .sk-matrix { display: grid; grid-template-columns: minmax(0, 1fr) repeat(3, 2.9rem); align-items: stretch; border: 1px solid var(--hp-line); border-radius: 1rem; overflow: hidden; font-size: 0.86rem; }
        #hp .sk-matrix > * { padding: 0.5rem 0.4rem; border-top: 1px solid var(--hp-line); text-align: center; }
        #hp .sk-matrix > .is-h { border-top: 0; font-size: 0.72rem; font-variation-settings: 'wght' 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--hp-ink-3); }
        #hp .sk-matrix > .is-name { min-width: 0; padding-inline: 0.85rem 0.4rem; color: var(--hp-ink-2); text-align: start; }
        #hp .sk-matrix > .is-name > span { line-height: 1.25; }
        #hp .sk-matrix > .is-mid { background: rgba(78, 129, 250, 0.08); }
        #hp .sk-matrix > .is-h.is-mid { color: var(--hp-blue); }
        #hp .sk-matrix u { display: inline-block; width: 0.55rem; height: 0.55rem; border-radius: 50%; box-shadow: inset 0 0 0 1.5px var(--hp-line-2); text-decoration: none; }
        #hp .sk-matrix u.is-yes { background: linear-gradient(135deg, #2f66ea, #22d3ee); box-shadow: none; }
        #hp .sk-matrix b { font-variation-settings: 'wght' 700; font-size: 0.8rem; color: var(--hp-ink); }
        #hp .sk-env { display: inline-block; padding: 0.7rem 0.95rem; border: 1px solid var(--hp-line-2); border-radius: 0.8rem; background: var(--hp-bg); font-family: var(--hp-mono); font-size: 0.92rem; font-variation-settings: normal; color: var(--hp-ink-2); white-space: nowrap; }
        #hp .sk-env b { color: var(--hp-blue); font-variation-settings: normal; font-weight: 400; }
        #hp .sk-env em { color: var(--sk-green); font-style: normal; }
        @property --sk-p { syntax: '<percentage>'; inherits: false; initial-value: 100%; }
        #hp .sk-ring { position: relative; display: grid; flex: none; place-items: center; width: clamp(6rem, 12vw, 8.5rem); aspect-ratio: 1; }
        #hp .sk-ring::before { content: ""; position: absolute; inset: 0; border-radius: 50%; background: conic-gradient(var(--hp-blue) var(--sk-p, 100%), var(--hp-line) 0); -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 10px), #000 calc(100% - 9px)); mask: radial-gradient(farthest-side, transparent calc(100% - 10px), #000 calc(100% - 9px)); }
        html.es-anim #hp [data-reveal]:not(.is-revealed) .sk-ring::before { --sk-p: 0%; }
        #hp .sk-ring::before { transition: --sk-p 1.6s cubic-bezier(0.22, 1, 0.36, 1) 0.3s; }
        #hp .sk-ring b { display: block; font-size: clamp(1.6rem, 3vw, 2.3rem); font-variation-settings: 'wght' 850; line-height: 1; text-align: center; }
        #hp .sk-ring small { display: block; font-size: 0.66rem; font-variation-settings: 'wght' 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--hp-ink-3); }
        #hp .sk-flow { display: flex; align-items: center; padding: 1.15rem 1rem; border: 1px solid var(--hp-line); border-radius: 1rem; background: var(--hp-bg); }
        #hp .sk-flow span { flex: none; padding: 0.4rem 0.8rem; border-radius: 99px; background: rgba(78, 129, 250, 0.14); color: var(--hp-blue); font-size: 0.82rem; font-variation-settings: 'wght' 700; }
        #hp .sk-flow span:last-child { background: var(--sk-amber-soft); color: var(--sk-amber); }
        #hp .sk-flow div { position: relative; flex: 1 1 0; height: 1px; margin-inline: 0.75rem; background: var(--hp-line-2); }
        #hp .sk-flow div i { position: absolute; inset: -0.25rem 0; opacity: 0; animation: sk-flow 3.2s ease-in-out infinite; animation-delay: var(--fd, 0s); }
        #hp .sk-flow div i::before { content: ""; position: absolute; top: 0; left: 0; width: 0.5rem; height: 0.5rem; border-radius: 50%; background: #f59e0b; box-shadow: 0 0 0.75rem rgba(245, 158, 11, 0.9); }
        @keyframes sk-flow {
            0% { transform: translateX(0); opacity: 0; }
            12% { opacity: 1; }
            58% { transform: translateX(calc(100% - 0.5rem)); opacity: 1; }
            70%, 100% { transform: translateX(calc(100% - 0.5rem)); opacity: 0; }
        }

        /* The night: every light is a customer. */
        #hp .sk-night { margin-top: clamp(3rem, 6vw, 5.5rem); }
        .dark #hp .sk-night::before {
            background:
                linear-gradient(to bottom, var(--hp-bg-3) 0, var(--hp-bg-3) 2px, rgba(17, 29, 90, 0) 9rem) top / 100% 9rem no-repeat,
                linear-gradient(to top, var(--hp-bg-3) 0, var(--hp-bg-3) 2px, rgba(17, 29, 90, 0) 9rem) bottom / 100% 9rem no-repeat,
                radial-gradient(60rem 34rem at 78% 34%, rgba(78, 129, 250, 0.5), transparent 72%),
                radial-gradient(40rem 26rem at 10% 80%, rgba(34, 211, 238, 0.2), transparent 70%);
        }
        .dark #hp .sk-night .sk-fine,
        .dark #hp .sk-night .sk-field-cap,
        .dark #hp .sk-night .sk-ends,
        .dark #hp .sk-night .sk-knobs-hint,
        .dark #hp .sk-night .sk-feed-cap { color: #c5cde2; }
        #hp .sk-money-grid { display: grid; grid-template-columns: minmax(0, 1fr); grid-template-areas: "sum" "city" "knobs" "takes" "bars"; gap: clamp(1.5rem, 3vw, 2.25rem) clamp(1.5rem, 4vw, 4rem); margin-top: clamp(1.25rem, 3vw, 2.5rem); }
        @media (min-width: 1024px) {
            #hp .sk-money-grid { grid-template-columns: minmax(0, 1.3fr) minmax(0, 0.9fr); grid-template-areas: "city sum" "city knobs" "takes bars"; align-items: center; }
            #hp .sk-sum { align-self: end; }
            #hp .sk-knobs { align-self: start; }
        }
        #hp .sk-city { grid-area: city; min-width: 0; container-type: inline-size; }
        #hp .sk-city-rig { --c: calc(100cqi / 33.5); position: relative; height: calc(var(--c) * 20.6); }
        #hp .sk-city-glow { position: absolute; left: 10%; right: 10%; top: 48%; height: 46%; border-radius: 50%; background: rgba(245, 158, 11, 0.3); filter: blur(calc(var(--c) * 2.4)); opacity: calc(0.45 + var(--lit, 0.05) * 0.9); transition: opacity 0.4s ease; }
        #hp .sk-city-bloom { position: absolute; left: 22%; right: 22%; top: 14%; height: 50%; border-radius: 50%; background: rgba(86, 160, 255, 0.5); filter: blur(calc(var(--c) * 3)); opacity: calc(var(--lit, 0.05) * 0.75); pointer-events: none; transition: opacity 0.4s ease; }
        #hp .sk-city-slab { position: absolute; left: 50%; top: 50%; width: calc(var(--c) * 25); height: calc(var(--c) * 20); margin: calc(var(--c) * -10.8) 0 0 calc(var(--c) * -12.5); }
        #hp .sk-city-slab > i { position: absolute; inset: 0; border-radius: calc(var(--c) * 1.1); background: linear-gradient(to bottom right, #fcd76a 49.6%, #c77d08 50.4%); transform: translateY(calc(var(--c) * var(--k) * 0.13)) matrix(0.7071, -0.3954, 0.7071, 0.3954, 0, 0); }
        #hp .sk-field {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-columns: repeat(25, minmax(0, 1fr));
            grid-template-rows: repeat(20, minmax(0, 1fr));
            gap: calc(var(--c) * 0.2);
            padding: calc(var(--c) * 0.6);
            border-radius: calc(var(--c) * 1.1);
            background: #0a1130;
            box-shadow: inset 0 0 0 1px rgba(252, 215, 106, 0.5);
            transform: matrix(0.7071, -0.3954, 0.7071, 0.3954, 0, 0);
        }
        #hp .sk-field i { border-radius: 50%; background-color: rgba(141, 176, 255, 0.34); transform: scale(0.3); transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.3s ease, box-shadow 0.3s ease; }
        #hp .sk-field i.is-on { background-color: var(--on, #8fc0ff); box-shadow: 0 0 calc(var(--c) * 0.9) rgba(120, 180, 255, 0.9); transform: scale(0.82); }
        #hp .sk-field i:nth-child(3n) { --on: #7dd3fc; }
        #hp .sk-field i:nth-child(5n) { --on: #dbeafe; }
        html.es-anim #hp .sk-field.is-live i.is-on { animation: sk-pay 9s ease-in-out infinite; animation-delay: var(--d, 0s); }
        #hp .sk-field i:nth-child(7n + 1) { --d: -1.3s; }
        #hp .sk-field i:nth-child(7n + 2) { --d: -4.1s; }
        #hp .sk-field i:nth-child(7n + 3) { --d: -6.4s; }
        #hp .sk-field i:nth-child(7n + 4) { --d: -2.7s; }
        #hp .sk-field i:nth-child(7n + 5) { --d: -8.2s; }
        #hp .sk-field i:nth-child(7n + 6) { --d: -5.3s; }
        #hp .sk-field i:nth-child(11n + 3) { --d: -7.4s; }
        #hp .sk-field i:nth-child(13n + 5) { --d: -3.5s; }
        @keyframes sk-pay {
            0%, 88%, 100% { background-color: var(--on, #8fc0ff); }
            93% { background-color: #fbbf24; }
        }
        #hp .sk-field-cap { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: center; gap: 0.4rem 2rem; margin-top: 0.25rem; font-family: var(--hp-mono); font-size: 0.78rem; font-variation-settings: normal; letter-spacing: 0.06em; text-transform: uppercase; color: #9fb1d6; }
        #hp .sk-field-cap b { color: #fff; font-variation-settings: normal; }
        #hp .sk-field-cap i { display: inline-block; width: 0.6rem; height: 0.6rem; margin-inline-end: 0.4rem; border-radius: 50%; background: #fbbf24; vertical-align: -0.02rem; }
        #hp .sk-knobs { grid-area: knobs; }
        #hp .sk-sum { grid-area: sum; min-width: 0; container-type: inline-size; }
        #hp .sk-takes { grid-area: takes; }
        #hp .sk-bars { grid-area: bars; }
        #hp .sk-knobs { display: grid; gap: 1.75rem; }
        #hp .sk-knob > div { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 0.6rem; }
        #hp .sk-knob label { font-size: 1.05rem; font-variation-settings: 'wght' 700; color: #fff; }
        #hp .sk-knob output { padding: 0.2rem 0.75rem; border-radius: 99px; background: rgba(255, 255, 255, 0.1); font-family: var(--hp-mono); font-size: 0.95rem; font-weight: 700; font-variation-settings: normal; font-variant-numeric: tabular-nums; color: #fff; }
        #hp .sk-knob output.sk-money { background: rgba(251, 191, 36, 0.14); }
        #hp .sk-ends { display: flex; justify-content: space-between; margin-top: 0.2rem; font-family: var(--hp-mono); font-size: 0.74rem; font-variation-settings: normal; color: #9fb1d6; }
        #hp .sk-range {
            -webkit-appearance: none;
            appearance: none;
            display: block;
            width: 100%;
            height: 28px;
            padding: 0;
            border: 0;
            border-radius: 99px;
            background: linear-gradient(90deg, #fbbf24 var(--fill, 50%), rgba(255, 255, 255, 0.16) var(--fill, 50%)) center / 100% 6px no-repeat;
            cursor: pointer;
        }
        #hp .sk-range::-webkit-slider-thumb { -webkit-appearance: none; width: 24px; height: 24px; border: 3px solid #fbbf24; border-radius: 50%; background: #070a14; box-shadow: 0 0 0 5px rgba(251, 191, 36, 0.18); cursor: grab; }
        #hp .sk-range::-moz-range-thumb { width: 18px; height: 18px; border: 3px solid #fbbf24; border-radius: 50%; background: #070a14; box-shadow: 0 0 0 5px rgba(251, 191, 36, 0.18); cursor: grab; }
        #hp .sk-range::-moz-range-track { background: transparent; }
        #hp .sk-range:focus-visible { outline: 3px solid #fbbf24; outline-offset: 4px; }
        #hp .sk-knobs-hint { font-size: 0.95rem; color: #9fb1d6; }
        #hp .sk-sum-brand { display: inline-flex; align-items: center; gap: 0.55rem; max-width: 100%; margin-bottom: 0.9rem; padding: 0.3rem 0.8rem 0.3rem 0.3rem; border: 1px solid rgba(255, 255, 255, 0.16); border-radius: 99px; background: rgba(255, 255, 255, 0.06); font-family: var(--hp-mono); font-size: 0.8rem; font-variation-settings: normal; color: #c5cde2; }
        #hp .sk-sum-brand .sk-mark { --u: 0.5rem; width: 1.6rem; border-radius: 0.5rem; font-size: 0.62rem; }
        #hp .sk-sum-brand span:last-child { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
        #hp .sk-sum-label { font-size: 1rem; color: #c5cde2; }
        #hp .sk-total { display: block; margin-top: 0.1rem; font-size: min(clamp(4rem, 10vw, 8.5rem), 23cqi); font-weight: 700; font-variation-settings: 'wght' 880; font-variant-numeric: tabular-nums; letter-spacing: -0.05em; line-height: 0.95; text-shadow: 0 0 3.5rem rgba(251, 191, 36, 0.4); }
        #hp .sk-year { margin-top: 0.6rem; font-size: 1.1rem; color: #c5cde2; }
        #hp .sk-year b { font-variation-settings: 'wght' 760; font-variant-numeric: tabular-nums; }
        #hp .sk-takes { display: flex; align-items: center; justify-content: space-between; gap: 1.25rem; padding: 1.25rem 1.4rem; border: 1px solid rgba(251, 191, 36, 0.3); border-radius: 1.25rem; background: rgba(251, 191, 36, 0.06); }
        #hp .sk-takes strong { display: block; font-size: 1.05rem; color: #fff; }
        #hp .sk-takes p { margin-top: 0.2rem; font-size: 0.92rem; color: #c5cde2; }
        #hp .sk-ghost { flex: none; font-size: 3.6rem; font-weight: 700; font-variation-settings: 'wght' 880; line-height: 1; color: transparent; -webkit-text-stroke: 2px #fbbf24; }
        #hp .sk-bars { display: grid; gap: 0.5rem; font-size: 0.86rem; font-variation-settings: 'wght' 650; color: #c5cde2; }
        #hp .sk-bars > div { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.2rem 1rem; }
        #hp .sk-bars > div + div { margin-top: 0.5rem; }
        #hp .sk-bars .is-lose { color: #fca5a5; }
        #hp .sk-bars > .sk-bar { display: block; margin-top: 0; }
        #hp .sk-bar { height: 0.75rem; overflow: hidden; border-radius: 99px; background: rgba(255, 255, 255, 0.1); }
        #hp .sk-bar i { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, #f59e0b, #fcd34d); transform-origin: left; transition: transform 1.1s cubic-bezier(0.22, 1, 0.36, 1) 0.3s; }
        #hp .sk-bar.is-lose i { width: 30%; background: linear-gradient(90deg, #dc2626, #f87171); transition-delay: 0.5s; }
        html.es-anim #hp [data-reveal]:not(.is-revealed) .sk-bar i { transform: scaleX(0.04); }
        #hp .sk-fine { margin-top: clamp(1.75rem, 3vw, 2.5rem); padding-top: 1.25rem; border-top: 1px solid rgba(255, 255, 255, 0.12); font-size: 0.84rem; line-height: 1.55; color: #9fb1d6; }
        #hp .sk-feed { margin-top: clamp(2rem, 4vw, 3rem); }
        #hp .sk-tick { display: inline-flex; align-items: center; gap: 0.55rem; padding: 0.5rem 1rem; border: 1px solid rgba(255, 255, 255, 0.14); border-radius: 999px; background: rgba(255, 255, 255, 0.05); font-size: 0.86rem; color: #c5cde2; white-space: nowrap; }
        #hp .sk-tick::before { content: ""; width: 0.4rem; height: 0.4rem; border-radius: 50%; background: #34d399; }
        #hp .sk-tick b { font-family: var(--hp-mono); font-variation-settings: normal; font-weight: 700; }
        #hp .sk-feed-cap { margin-top: 0.8rem; font-size: 0.86rem; color: #9fb1d6; text-align: center; }
        /* The other meters */
        #hp .sk-meters-head { display: grid; grid-template-columns: minmax(0, 1fr); align-items: end; gap: 1rem 2rem; }
        @media (min-width: 900px) {
            #hp .sk-meters-head { grid-template-columns: minmax(0, 1fr) auto; }
        }
        #hp .sk-meters { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; margin-top: clamp(1.75rem, 3vw, 2.5rem); }
        @media (min-width: 900px) {
            #hp .sk-meters { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        #hp .sk-meter { display: flex; flex-direction: column; padding: clamp(1.25rem, 2vw, 1.75rem); transition: border-color 0.3s ease, box-shadow 0.3s ease; }
        #hp .sk-meter.is-on { border-color: var(--sk-amber-line); box-shadow: 0 0 0 3px var(--sk-amber-soft), var(--hp-card-shadow); }
        #hp .sk-meter dt { display: flex; align-items: center; justify-content: space-between; gap: 1rem; font-size: 1.15rem; font-variation-settings: 'wght' 760; letter-spacing: -0.02em; }
        #hp .sk-meter dd { font-size: 0.95rem; line-height: 1.6; color: var(--hp-ink-2); }
        #hp .sk-meter dd.sk-meter-flag { margin-block: 0.7rem 1rem; }
        #hp .sk-meter-flag { display: flex; flex-wrap: wrap; gap: 0.4rem; }
        #hp .sk-meter code { display: inline-block; padding: 0.3rem 0.6rem; border-radius: 0.5rem; background: var(--hp-bg-2); box-shadow: inset 0 0 0 1px var(--hp-line-2); font-size: 0.74rem; color: var(--hp-ink-2); overflow-wrap: anywhere; }
        #hp .sk-meter code .is-on { display: none; color: var(--sk-amber); font-weight: 700; }
        #hp .sk-meter.is-on code .is-on { display: inline; }
        #hp .sk-meter.is-on code .is-off { display: none; }
        #hp .sk-switch { position: relative; flex: none; width: 3.4rem; height: 1.9rem; padding: 0; border: 0; border-radius: 99px; background: #6b7590; cursor: pointer; transition: background-color 0.25s ease; }
        #hp .sk-switch::after { content: ""; position: absolute; inset: -0.45rem 0; }
        #hp .sk-switch i { position: absolute; top: 0.2rem; left: 0.2rem; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(10, 16, 32, 0.35); transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); }
        #hp .sk-switch[aria-checked="true"] { background: #d97706; }
        #hp .sk-switch[aria-checked="true"] i { transform: translateX(1.5rem); }
        html:not(.sk-js) #hp .sk-switch { display: none; }
        #hp .sk-closing { max-width: 54rem; margin: clamp(3rem, 6vw, 5rem) auto 0; font-size: clamp(1.35rem, 1.5vw + 0.95rem, 2.1rem); font-variation-settings: 'wght' 700; letter-spacing: -0.03em; line-height: 1.28; color: var(--hp-ink); text-align: center; text-wrap: balance; }

        /* ---- 04 Infra ---- */
        #hp .sk-console {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            margin-top: clamp(2rem, 4vw, 3.25rem);
            padding: clamp(0.5rem, 1.2vw, 1rem);
            border-radius: 2.2rem;
            background:
                radial-gradient(46rem 22rem at 88% 0%, rgba(47, 102, 234, 0.42), transparent 70%),
                radial-gradient(30rem 18rem at 0% 100%, rgba(245, 158, 11, 0.13), transparent 70%),
                #0b1226;
            color: #eef2ff;
            box-shadow: 0 40px 90px -42px rgba(10, 16, 32, 0.75);
        }
        .dark #hp .sk-console { box-shadow: 0 0 0 1px rgba(125, 165, 255, 0.22), 0 40px 90px -42px rgba(0, 0, 0, 0.9); }
        #hp .sk-console::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background-image: linear-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.06) 1px, transparent 1px);
            background-size: calc(100% / 7) 5.5rem;
            -webkit-mask-image: radial-gradient(ellipse at 85% 0%, #000, transparent 70%);
            mask-image: radial-gradient(ellipse at 85% 0%, #000, transparent 70%);
        }
        #hp .sk-panes { display: grid; grid-template-columns: minmax(0, 1fr); }
        #hp .sk-pane { display: flex; flex-direction: column; min-width: 0; padding: clamp(1.1rem, 2vw, 1.75rem); }
        #hp .sk-pane + .sk-pane { border-top: 1px solid rgba(255, 255, 255, 0.1); }
        @media (min-width: 1024px) {
            #hp .sk-panes { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            #hp .sk-pane + .sk-pane { border-top: 0; border-inline-start: 1px solid rgba(255, 255, 255, 0.1); }
        }
        #hp .sk-pane { --sn: #7ea6ff; }
        #hp .sk-pane:nth-child(2) { --sn: #38bdf8; }
        #hp .sk-pane:nth-child(3) { --sn: #fbbf24; }
        #hp .sk-pane-head { display: flex; align-items: center; gap: 0.9rem; margin-bottom: 1.1rem; }
        #hp .sk-pane-line { flex: 1 1 0; height: 2px; border-radius: 2px; background: linear-gradient(90deg, var(--sn), transparent); opacity: 0.6; }
        #hp .sk-step-no { display: inline-flex; flex: none; align-items: center; justify-content: center; width: 2.6rem; height: 2.6rem; border-radius: 0.9rem; background: var(--sn); color: #070a14; font-size: 1.2rem; font-variation-settings: 'wght' 820; }
        #hp .sk-console .sk-h4 { color: #fff; }
        #hp .sk-console .sk-p { margin-top: 0.6rem; margin-bottom: 1.5rem; font-size: 1rem; color: #c5cde2; }
        #hp .sk-console .sk-p a { color: #a9c3ff; text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.2em; }
        #hp .sk-console .sk-money { color: #fbbf24; }
        #hp .sk-scene { margin-top: auto; min-height: 13.5rem; display: flex; flex-direction: column; justify-content: center; gap: 0.9rem; padding: 1rem; border-radius: 1.1rem; background: rgba(255, 255, 255, 0.04); box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.1); }
        #hp .sk-term { padding: 1.1rem 1.2rem; border-radius: 1.1rem; background: #050814; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.1); font-family: var(--hp-mono); font-size: clamp(0.72rem, 0.35vw + 0.62rem, 0.86rem); font-variation-settings: normal; color: #dbe4ff; }
        #hp .sk-term div { padding-inline-start: 1.15em; text-indent: -1.15em; overflow-wrap: break-word; line-height: 1.5; }
        #hp .sk-term div + div { margin-top: 0.4em; }
        #hp .sk-term s { color: #34d399; text-decoration: none; }
        #hp .sk-term q { color: #8fa0c7; quotes: none; }
        #hp .sk-envfile { padding: 0.9rem 1rem; border-radius: 0.9rem; background: #050814; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.1); font-family: var(--hp-mono); font-size: clamp(0.68rem, 0.3vw + 0.58rem, 0.76rem); font-variation-settings: normal; color: #c5cde2; }
        #hp .sk-envfile div { overflow-wrap: anywhere; line-height: 1.45; }
        #hp .sk-envfile div + div { margin-top: 0.35em; }
        #hp .sk-envfile b { color: #8db0ff; font-weight: 400; font-variation-settings: normal; }
        #hp .sk-envfile em { color: #34d399; font-style: normal; }
        #hp .sk-tiers { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem; }
        #hp .sk-tiers span { padding: 0.55rem 0.25rem; border-radius: 0.8rem; background: rgba(255, 255, 255, 0.06); box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12); text-align: center; }
        #hp .sk-tiers small { display: block; font-size: 0.72rem; font-variation-settings: 'wght' 700; color: #9fb1d6; }
        #hp .sk-tiers b { font-size: 1.1rem; font-variation-settings: 'wght' 850; }
        #hp .sk-tiers b small { display: inline; font-size: 0.7rem; color: inherit; }
        #hp .sk-first { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1rem; border-radius: 1rem; background: #131b33; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.14); color: #fff; }
        #hp .sk-first .sk-mark { --u: 0.5rem; width: 2.5rem; border-radius: 0.75rem; font-size: 1rem; }
        #hp .sk-first strong { display: block; overflow: hidden; font-size: 0.95rem; text-overflow: ellipsis; }
        #hp .sk-first small { font-size: 0.8rem; color: #9fb1d6; }
        #hp .sk-first > div { min-width: 0; }
        #hp .sk-first svg { flex: none; width: 1.25rem; height: 1.25rem; margin-inline-start: auto; color: #34d399; }
        #hp .sk-paid { align-self: center; padding: 0.55rem 1.1rem; border: 1px solid rgba(251, 191, 36, 0.4); border-radius: 999px; background: rgba(251, 191, 36, 0.1); font-family: var(--hp-mono); font-size: 0.86rem; font-weight: 700; font-variation-settings: normal; white-space: nowrap; }
        #hp .sk-scene > small { font-size: 0.82rem; color: #9fb1d6; text-align: center; }
        #hp .sk-docs { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.6rem; margin-top: clamp(1.75rem, 3vw, 2.5rem); }
        #hp .sk-docs a { display: inline-flex; align-items: center; gap: 0.4rem; min-height: 2.5rem; padding: 0.5rem 1rem; border: 1px solid var(--hp-line-2); border-radius: 999px; background: var(--hp-bg-2); font-size: 0.95rem; font-variation-settings: 'wght' 650; color: var(--hp-ink-2); transition: border-color 0.2s ease, color 0.2s ease; }
        #hp .sk-docs a:hover { border-color: var(--hp-blue); color: var(--hp-blue); }
        #hp .sk-docs svg { width: 0.9rem; height: 0.9rem; }
        /* Build, rent, own: three stacks */
        #hp .sk-ways { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; margin-top: clamp(2rem, 4vw, 3.25rem); }
        @media (min-width: 900px) {
            #hp .sk-ways { grid-template-columns: repeat(3, minmax(0, 1fr)); align-items: stretch; }
            #hp .sk-way.is-own { position: relative; top: -0.9rem; }
            /* The two pale cards are shorter than the chosen one: their spare room goes round the drawing. */
            #hp .sk-way:not(.is-own) .sk-mini { --u: 6.2px; margin-block: auto; }
            #hp .sk-way:not(.is-own) .sk-way-key { margin-top: 0.5rem; }
            #hp .sk-way:not(.is-own) .sk-verdict { margin-top: 0; }
        }
        @media (min-width: 900px) and (max-width: 1439.98px) {
            #hp .sk-way:not(.is-own) .sk-mini { --u: 6.6px; }
        }
        #hp .sk-way { display: flex; flex-direction: column; padding: clamp(1.4rem, 2.2vw, 2rem); }
        #hp .sk-way.is-own {
            border-color: rgba(125, 165, 255, 0.42);
            background: radial-gradient(30rem 16rem at 50% 0%, rgba(47, 102, 234, 0.55), transparent 70%), #0b1226;
            color: #eef2ff;
            box-shadow: 0 34px 80px -34px rgba(47, 102, 234, 0.75);
        }
        #hp .sk-way.is-own .sk-h4 { color: #fff; }
        #hp .sk-way.is-own .sk-way-key { color: #9fb1d6; }
        #hp .sk-way.is-own li,
        #hp .sk-way.is-own .sk-verdict { color: #c5cde2; }
        #hp .sk-way.is-own .sk-verdict { border-top-color: rgba(255, 255, 255, 0.14); }
        #hp .sk-way.is-own .sk-verdict a { color: #a9c3ff; }
        #hp .sk-way.is-own .sk-trade { border-color: rgba(251, 191, 36, 0.4); background: rgba(251, 191, 36, 0.1); color: #fde68a; }
        #hp .sk-way.is-own .sk-face { --sk-top-line: rgba(255, 255, 255, 0.22); }
        #hp .sk-way.is-own .sk-slab[data-l="1"] .sk-face { background: #2a4691; }
        #hp .sk-way.is-own .sk-slab[data-l="2"] .sk-face { background: #14587f; }
        #hp .sk-way.is-own .sk-slab[data-l="3"] .sk-face { background: #7a5410; }
        #hp .sk-way.is-own .sk-slab[data-l="4"] { --sa: #3b4d8c; --sb: #1a2550; }
        #hp .sk-way.is-own .sk-slab[data-l="4"] .sk-face { background: #2b3a6b; }
        #hp .sk-own-tag { align-self: center; margin: -0.6rem 0 1.1rem; padding: 0.25rem 0.9rem; border-radius: 99px; background: #2b5fe3; color: #fff; font-size: 0.74rem; font-variation-settings: 'wght' 760; letter-spacing: 0.1em; text-transform: uppercase; }
        #hp .sk-mini { --u: 4.4px; --gap: 4.4; --thick: 0.2; --dy: 6.5; --h: 40; position: relative; height: calc(var(--u) * 40); margin-bottom: 0.5rem; }
        #hp .sk-way.is-build .sk-slab > i { display: none; }
        #hp .sk-way.is-build .sk-face { background: color-mix(in srgb, var(--hp-ink) 9%, transparent); box-shadow: none; }
        #hp .sk-way.is-rent .sk-slab:not([data-l="1"]) { --sa: var(--sk-0a); --sb: var(--sk-0b); }
        #hp .sk-way.is-rent .sk-slab:not([data-l="1"]) .sk-face { background: repeating-linear-gradient(45deg, var(--sk-0a) 0 3px, var(--hp-bg-2) 3px 7px); box-shadow: inset 0 0 0 1px var(--sk-top-line); }
        #hp .sk-way.is-rent .sk-slab[data-l="1"] .sk-face { background: #dbe6ff; }
        .dark #hp .sk-way.is-rent .sk-slab[data-l="1"] .sk-face { background: #2a4691; }
        #hp .sk-own .sk-slab[data-l="1"] .sk-face { background: #dbe6ff; }
        #hp .sk-own .sk-slab[data-l="2"] .sk-face { background: #d5f1fc; }
        #hp .sk-own .sk-slab[data-l="3"] .sk-face { background: #fdebb3; }
        .dark #hp .sk-own .sk-slab[data-l="1"] .sk-face { background: #2a4691; }
        .dark #hp .sk-own .sk-slab[data-l="2"] .sk-face { background: #14587f; }
        .dark #hp .sk-own .sk-slab[data-l="3"] .sk-face { background: #7a5410; }
        #hp .sk-way-key { margin-bottom: 1.25rem; font-family: var(--hp-mono); font-size: 0.74rem; font-variation-settings: normal; letter-spacing: 0.1em; text-transform: uppercase; color: var(--hp-ink-3); text-align: center; }
        #hp .sk-way-top { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1rem; }
        #hp .sk-when { flex: none; padding: 0.25rem 0.75rem; border-radius: 99px; background: var(--hp-line); font-size: 0.78rem; font-variation-settings: 'wght' 700; color: var(--hp-ink-2); white-space: nowrap; }
        #hp .sk-way.is-own .sk-when { background: rgba(141, 176, 255, 0.2); color: #c3d4ff; }
        #hp .sk-way ul { display: grid; gap: 0.75rem; margin-bottom: 1.5rem; }
        #hp .sk-way li { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.65rem; font-size: 0.98rem; line-height: 1.5; color: var(--hp-ink-2); }
        #hp .sk-way li svg { width: 1.05rem; height: 1.05rem; margin-top: 0.22rem; color: var(--hp-ink-3); }
        #hp .sk-way.is-rent li svg { color: var(--sk-red); }
        #hp .sk-way.is-own li svg { color: #34d399; }
        #hp .sk-trade { margin-bottom: 1.25rem; padding: 0.8rem 1rem; border: 1px solid var(--sk-amber-line); border-radius: 0.9rem; background: var(--sk-amber-soft); font-size: 0.95rem; color: var(--hp-ink); }
        #hp .sk-verdict { margin-top: auto; padding-top: 1.1rem; border-top: 1px solid var(--hp-line); font-size: 0.98rem; font-variation-settings: 'wght' 650; color: var(--hp-ink-2); }
        #hp .sk-way-foot { max-width: 46rem; margin: clamp(1.75rem, 3vw, 2.5rem) auto 0; text-align: center; }
        /* Two ways to run it */
        #hp .sk-paths { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; margin-top: clamp(2rem, 4vw, 3rem); }
        @media (min-width: 800px) {
            #hp .sk-paths { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: stretch; }
        }
        #hp .sk-path { display: flex; flex-direction: column; padding: clamp(1.5rem, 2.5vw, 2.25rem); }
        #hp .sk-path .sk-p { margin-bottom: 1.4rem; }
        #hp .sk-path-tag { margin-bottom: 0.7rem; font-size: 0.95rem; font-variation-settings: 'wght' 700; color: var(--sk-green); }
        #hp .sk-path.is-here { border-color: var(--hp-blue); box-shadow: 0 0 0 3px rgba(78, 129, 250, 0.2), var(--hp-pop-shadow); }
        #hp .sk-path.is-here .sk-path-tag { color: var(--hp-blue); }
        #hp .sk-path-foot { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 1.25rem; margin-bottom: 1.5rem; }
        #hp .sk-path-foot .sk-mini { --u: 4.4px; --h: 38; flex: none; width: calc(var(--u) * 40); height: calc(var(--u) * 38); margin: 0; }
        #hp .sk-path-foot .sk-mini.is-one { --dy: 0; }
        #hp .sk-path-foot .sk-mini.is-one .sk-slab { --sa: #6ee7b7; --sb: #059669; }
        #hp .sk-path-foot .sk-mini.is-one .sk-face { background: #d1fae5; box-shadow: inset 0 0 0 1px var(--sk-top-line); }
        .dark #hp .sk-path-foot .sk-mini.is-one .sk-face { background: #065f46; }
        #hp .sk-path-foot code { padding: 0.45rem 0.7rem; border-radius: 0.6rem; background: #050814; color: #dbe4ff; font-size: 0.78rem; overflow-wrap: anywhere; }
        #hp .sk-path-foot code s { color: #34d399; text-decoration: none; }
        #hp .sk-path .hp-btn,
        #hp .sk-path .hp-more { margin-top: auto; align-self: flex-start; }
        #hp .sk-more-green { color: var(--sk-green); }

        /* FAQ and finale */
        #hp .hp-faq .faq-answer a svg { display: inline; }
        #hp .sk-finale { display: grid; grid-template-columns: minmax(0, 1fr); align-items: center; gap: 1rem; text-align: center; }
        #hp .sk-finale .sk-rig { --u: clamp(6.6px, 1.9vw, 12.5px); --dx: 0; width: calc(var(--u) * 37); height: calc(var(--u) * 43); margin-inline: auto; padding-top: 0; }
        #hp .sk-finale .sk-face { --sk-top: #131b33; --sk-top-2: #19223f; --sk-top-line: rgba(255, 255, 255, 0.18); --hp-line-2: rgba(255, 255, 255, 0.2); --hp-ink-2: #c5cde2; --hp-ink-3: #9fb1d6; color: #eef2ff; }
        #hp .sk-finale .sk-f-brand .sk-dom,
        #hp .sk-finale .sk-f-bill small { color: #9fb1d6; }
        #hp .sk-finale .sk-f-ten span { color: #c5cde2; }
        #hp .sk-finale .sk-slab[data-l="4"] { --sa: #3b4d8c; --sb: #1a2550; }
        #hp .sk-finale .sk-floor { background: rgba(78, 129, 250, 0.55); }
        @media (min-width: 1024px) {
            #hp .sk-finale { grid-template-columns: minmax(0, 0.82fr) minmax(0, 1.18fr); gap: clamp(1rem, 3vw, 3rem); text-align: start; }
            #hp .sk-finale .hp-h2,
            #hp .sk-finale .hp-lead { margin-inline: 0; }
            #hp .sk-finale .sk-finale-copy .hp-actions { justify-content: flex-start; }
            #hp .sk-finale .sk-flowline { justify-content: flex-start; }
        }
        #hp .sk-finale .hp-actions { justify-content: center; }
        #hp .sk-finale-brand { display: inline-flex; align-items: center; gap: 0.9rem; max-width: 100%; margin-bottom: 1.6rem; text-align: start; cursor: text; }
        #hp .sk-finale-brand .sk-mark { --u: 0.5rem; width: 3.4rem; border-radius: 1rem; font-size: 1.3rem; }
        #hp .sk-finale-brand > div { min-width: 0; }
        #hp .sk-finale-brand label { display: block; margin-bottom: 0.3rem; font-family: var(--hp-mono); font-size: 0.68rem; font-variation-settings: normal; letter-spacing: 0.14em; text-transform: uppercase; color: #d3dbf0; }
        #hp .sk-finale-brand > svg { flex: none; align-self: center; width: 1.2rem; height: 1.2rem; color: #c5cde2; }
        #hp .sk-finale-brand:focus-within > svg { color: #8db0ff; }
        #hp .sk-wordmark {
            display: block;
            width: 9.5em;
            max-width: 100%;
            padding: 0 0 0.2rem;
            cursor: text;
            border: 0;
            border-bottom: 1.5px dashed rgba(255, 255, 255, 0.4);
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            font-family: inherit;
            font-size: clamp(1.5rem, 2.4vw, 2.2rem);
            font-weight: 700;
            font-variation-settings: 'wght' 840;
            letter-spacing: -0.04em;
            line-height: 1.15;
            color: #fff;
        }
        #hp .sk-wordmark::placeholder { color: #dbe4ff; opacity: 1; }
        #hp .sk-wordmark:focus { border: 0; border-bottom: 1.5px solid #8db0ff; box-shadow: none; outline: 0; }
        #hp .sk-wordmark:focus-visible { outline: 3px solid #8db0ff; outline-offset: 6px; }
        #hp .sk-finale-dom { display: block; margin-top: 0.4rem; font-family: var(--hp-mono); font-size: 0.9rem; font-variation-settings: normal; color: #9fb1d6; overflow-wrap: anywhere; }
        #hp .sk-flowline { display: flex; align-items: center; justify-content: center; gap: 0.7rem; margin-bottom: 1.4rem; font-family: var(--hp-mono); font-size: 0.78rem; font-variation-settings: normal; letter-spacing: 0.16em; text-transform: uppercase; color: #9fb1d6; }
        #hp .sk-flowline svg { width: 0.9rem; height: 0.9rem; }
        #hp .sk-flowline b { color: #fbbf24; font-variation-settings: normal; }
        #hp .hp-finale .sk-btn-night { color: #eef2ff; border: 1px solid rgba(255, 255, 255, 0.32); background: transparent; }
        #hp .hp-finale .sk-btn-night:hover { background: rgba(255, 255, 255, 0.1); }
        /* The finale's stack is dropped into place, bottom slab first. */
        html.es-anim #hp .hp-finale:not(.is-revealed) .sk-slab { --lift: 22; opacity: 0; }
        #hp .sk-finale .sk-slab { transition-duration: 1.1s; }
        #hp .sk-finale .sk-slab[data-l="4"] { transition-delay: 0.2s; }
        #hp .sk-finale .sk-slab[data-l="3"] { transition-delay: 0.42s; }
        #hp .sk-finale .sk-slab[data-l="2"] { transition-delay: 0.64s; }
        #hp .sk-finale .sk-slab[data-l="1"] { transition-delay: 0.86s; }

        /* The margin stack: where you are in the four layers (wide windows only). */
        #hp .sk-layers { position: relative; }
        #hp .sk-rail { display: none; }
        @media (min-width: 1500px) {
            #hp .sk-rail { position: sticky; top: calc(50vh - 4.5rem); z-index: 6; display: block; height: 0; pointer-events: none; }
            #hp .sk-rail-in { position: absolute; top: 0; left: max(0.75rem, calc((100vw - 76rem) / 2 - 9.75rem)); width: 7.5rem; text-align: center; opacity: 0; transition: opacity 0.35s ease; }
            #hp .sk-rail.is-on .sk-rail-in { opacity: 1; }
            #hp .sk-rail .sk-glyph { --u: 2.4px; width: calc(var(--u) * 44); margin-inline: auto; }
            #hp .sk-rail p { margin-top: 0.3rem; font-family: var(--hp-mono); font-size: 0.7rem; font-variation-settings: normal; letter-spacing: 0.12em; text-transform: uppercase; color: var(--hp-ink-3); transition: color 0.3s ease; }
            #hp .sk-rail p b { display: block; color: var(--hp-ink); font-variation-settings: normal; }
            #hp .sk-rail.on-night p { color: #9fb1d6; }
            #hp .sk-rail.on-night p b { color: #fff; }
            #hp .sk-rail .sk-slab { transition-delay: 0s; transition-duration: 0.5s; }
            #hp .sk-rail [data-rail] { display: none; }
            #hp .sk-rail .sk-glyph[data-at="1"] ~ p [data-rail="1"],
            #hp .sk-rail .sk-glyph[data-at="2"] ~ p [data-rail="2"],
            #hp .sk-rail .sk-glyph[data-at="3"] ~ p [data-rail="3"],
            #hp .sk-rail .sk-glyph[data-at="4"] ~ p [data-rail="4"] { display: block; }
        }

        /* Engines with no container units: the two rigs take their size from the window instead. */
        @supports not (width: 1cqi) {
            #hp .sk-rig { --u: min(2.45vw, 0.82rem); }
            #hp .sk-city-rig { --c: min(2.7vw, 1.2rem); }
            #hp .sk-total { font-size: clamp(4rem, 10vw, 8.5rem); }
        }

        @media (prefers-reduced-motion: reduce) {
            #hp .sk-fed-dot,
            #hp .sk-flow div i,
            #hp .sk-field i { animation: none !important; }
            #hp .sk-flow div i,
            #hp .sk-fed-dot { opacity: 0; }
            #hp .sk-slab,
            #hp .sk-loupe,
            #hp .sk-signin-night,
            #hp .sk-bar i,
            #hp .sk-field i,
            #hp .sk-switch,
            #hp .sk-switch i,
            #hp .sk-tag svg,
            #hp .sk-ten,
            #hp .sk-city-glow,
            #hp .sk-city-bloom,
            #hp .sk-meter,
            #hp .sk-ring::before { transition: none !important; }
        }
    </style>

    {{-- Two words for the stylesheet. sk-js: script is here, so the controls that need it may show.
         es-anim, the motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        document.documentElement.classList.add('sk-js');
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    @php
        // The side planes of a slab, and a whole stack of four blank slabs (bottom first) for the marks and diagrams.
        $skSides = str_repeat('<i></i>', 10);
        $skSidesFew = str_repeat('<i></i>', 5);
        $skBlank = '';
        foreach ([4, 3, 2, 1] as $skL) {
            $skBlank .= '<div class="sk-slab" data-l="'.$skL.'">'.$skSidesFew.'<div class="sk-face"></div></div>';
        }
    @endphp
    {{-- The stack, written once and stood in two places: the hero and the finale. --}}
    @php
        $skTiers = [['Free', '$0'], ['Pro', '$29'], ['Ent', '$99']];
        ob_start();
    @endphp
    <div class="sk-floor" aria-hidden="true"></div>
    <div class="sk-iso" aria-hidden="true">
        <div class="sk-slab" data-l="4">{!! $skSides !!}
            <div class="sk-face"><div class="sk-f sk-f-infra">
                <div><b></b><b></b><b></b></div>
                <p><span class="sk-led"></span>docker compose up<em>your infra</em></p>
            </div></div>
        </div>
        <div class="sk-slab" data-l="3">{!! $skSides !!}
            <div class="sk-face"><div class="sk-f sk-f-bill">
                <div>
                    @foreach ($skTiers as $tier)
                        <span><small>{{ $tier[0] }}</small><b class="sk-money">{{ $tier[1] }}</b></span>
                    @endforeach
                </div>
            </div></div>
        </div>
        <div class="sk-slab" data-l="2">{!! $skSides !!}
            <div class="sk-face"><div class="sk-f sk-f-ten">
                @foreach ([['acme.', 0], ['venue.', 1], ['comedy.', 2], ['gigs.', 3], ['expo.', 4], ['+ next', 5]] as $tenant)
                    <span>{{ $tenant[0] }}</span>
                @endforeach
            </div></div>
        </div>
        <div class="sk-slab" data-l="1">{!! $skSides !!}
            <div class="sk-face"><div class="sk-f sk-f-brand">
                <span class="sk-mark" data-sk="mark">TP</span>
                <span>
                    <span class="sk-word" data-sk="name">TicketPilot</span>
                    <span class="sk-dom" data-sk="domain">yourdomain.com</span>
                </span>
                <span class="sk-f-bars"><b></b><b></b><b></b></span>
            </div></div>
        </div>
    </div>
    @php
        $skStack = ob_get_clean();
    @endphp

    <!-- ============================================================ -->
    <!-- Hero: the stack you own                                      -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero hp-hero sk-hero">
        <div class="hp-hero-sky" aria-hidden="true"></div>

        <div class="sk-wrap-wide sk-hero-grid">
            <div class="hp-hero-copy sk-copy">
                <h1 class="hp-h1">
                    <x-marketing.hero-eyebrow class="es-fade-up es-d-1 hp-eyebrow">
                        <span class="hp-live" aria-hidden="true"><i></i></span>
                        Free white-label ticketing platform
                    </x-marketing.hero-eyebrow>
                    <span class="es-mask"><span class="es-mask-line">Launch your own</span></span>
                    <span class="es-mask es-mask-2"><span class="es-mask-line">ticketing SaaS.</span></span>
                    <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="hp-ink-grad">Own every layer.</span></span></span>
                </h1>

                <p class="es-fade-up es-d-2 hp-sub">
                    Event Schedule is a free, open source white-label ticketing platform with the multi-tenant SaaS layer built in. Selfhost it under your brand: your servers, your Stripe, your prices, and what your customers pay stays yours, less only Stripe's own fee.
                </p>

                <div class="es-fade-up es-d-3 sk-name">
                    <label for="sk-name-input" class="hp-kicker">Name your platform</label>
                    <div class="sk-name-box" data-sk-box>
                        <span class="sk-mark" data-sk="mark" aria-hidden="true">TP</span>
                        <input id="sk-name-input" type="text" maxlength="22" placeholder="TicketPilot" autocomplete="off" autocapitalize="words" spellcheck="false" enterkeyhint="done" aria-describedby="sk-name-hint" data-sk-name>
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                    </div>
                    <p class="sk-name-hint" id="sk-name-hint"><span>Type a name and every mock on this page takes it.</span><span class="sk-name-dom" data-sk="domain" aria-hidden="true">yourdomain.com</span></p>
                </div>

                <div class="es-fade-up es-d-3 hp-hero-actions">
                    <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="hp-btn hp-btn-ghost is-still">
                        {!! $skGithub !!}
                        View on GitHub
                    </a>
                    <a href="{{ route('marketing.docs.saas.setup') }}" class="hp-btn hp-btn-primary">
                        Read the Setup Guide
                        {!! $skArrow !!}
                    </a>
                </div>

                <ul class="es-fade-up es-d-4 sk-chips">
                    @foreach (['100% open source', 'No per-ticket fees', 'Unlimited customers', 'No paid tier for operators'] as $chip)
                        <li>{!! $skCheck !!}{{ $chip }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="sk-stage">
                <p class="sr-only">Illustration of the platform stack you own: your brand on top, customer subdomains beneath it, subscription billing under that, and your own server at the base.</p>
                <div class="sk-rig sk-unstack" data-reveal data-sk-tilt>
                    {!! $skStack !!}
                    <nav class="sk-tags" aria-label="The four layers">
                        <p class="sk-tags-hint" aria-hidden="true">Go to a layer</p>
                        @foreach ($skLayers as $skNo => $skLayer)
                            <a href="#{{ $skLayer['id'] }}" class="sk-tag" data-l="{{ $skNo }}"><b>0{{ $skNo }}</b><strong>{{ $skLayer['name'] }}</strong><svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0l-6-6m6 6l6-6" /></svg><span>{{ $skLayer['say'] }}</span></a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Already built                                                -->
    <!-- ============================================================ -->
    <section id="machinery" class="sk-intro hp-alt">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <p class="hp-kicker" data-reveal>Already Built</p>
                <h2 class="hp-h2" data-reveal>Skip a year of <span class="hp-ink-grad">platform engineering</span></h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.1s;">The multi-tenant machinery that takes the longest to build is the part you get on day one.</p>
            </div>
        </div>
    </section>
    <div class="sk-layers">
    {{-- Where you are in the stack, in the margin of a wide window. Decoration: the chapters carry the same mark. --}}
    <div class="sk-rail" aria-hidden="true" data-sk-rail>
        <div class="sk-rail-in">
            <div class="sk-glyph" data-at="1"><div class="sk-iso">{!! $skBlank !!}</div></div>
            <p>
                @foreach ($skLayers as $skNo => $skLayer)
                    <span data-rail="{{ $skNo }}">0{{ $skNo }} / 04<b>{{ $skLayer['name'] }}</b></span>
                @endforeach
            </p>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- 01 Brand                                                     -->
    <!-- ============================================================ -->
    <section id="brand" class="sk-ch hp-alt" data-l="1" data-sk-ch="1">
        <div class="hp-wrap">
            <header class="sk-ch-head" data-reveal>
                <div class="sk-glyph" data-at="1" aria-hidden="true"><div class="sk-iso">{!! $skBlank !!}</div></div>
                <div>
                    <p class="sk-ch-no"><b>01</b> / 04 <span>Top layer</span></p>
                    <h2 class="sk-ch-title">Brand</h2>
                </div>
                <p class="sk-ch-say">What your customers see is yours: your logo, your domain, your site behind the links.</p>
            </header>

            <div class="sk-block sk-duo">
                <div data-reveal>
                    <span class="sk-module-no">Branding</span>
                    <h3 class="sk-h3">White-label branding</h3>
                    <p class="sk-p">Your light and dark logos, your domain, your marketing site behind the free-tier footer. Customers see your brand, and one small credit of ours in the corner of public pages.</p>
                </div>

                <div class="sk-stg" data-reveal="panel" aria-hidden="true">
                    <div class="sk-surfaces">
                        <div class="sk-surface is-signin">
                            <div class="sk-win">
                                <div class="sk-win-bar"><span class="sk-dots"><i></i><i></i><i></i></span><span class="sk-url">app.<span data-sk="domain">yourdomain.com</span></span></div>
                                <div class="sk-signin">
                                    <div class="sk-signin-in">
                                        <span class="sk-logo"><span class="sk-mark" data-sk="mark">TP</span><b data-sk="name">TicketPilot</b></span>
                                        <span class="sk-form"><i></i><i></i><b></b></span>
                                    </div>
                                    <div class="sk-signin-night">
                                        <div class="sk-signin-in">
                                            <span class="sk-logo"><span class="sk-mark" data-sk="mark">TP</span><b data-sk="name">TicketPilot</b></span>
                                            <span class="sk-form"><i></i><i></i><b></b></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="sk-cap">Your logos <code>APP_LOGO_DARK</code> <code>APP_LOGO_LIGHT</code></p>
                        </div>
                        <div class="sk-surface">
                            <div class="sk-win">
                                <div class="sk-addr"><span class="sk-url"><s>https://</s>acme.<span data-sk="domain">yourdomain.com</span></span></div>
                            </div>
                            <p class="sk-cap">Your domain <code>APP_URL</code></p>
                        </div>
                        <div class="sk-surface">
                            <div class="sk-win">
                                <div class="sk-strip-page">
                                    <div class="sk-strip-rows"><i></i><i></i></div>
                                    <div class="sk-strip">Create your free schedule at <b data-sk="domain">yourdomain.com</b></div>
                                </div>
                            </div>
                            <p class="sk-cap">Your site, on the free tier's footer <code>APP_MARKETING_URL</code></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- The catch, at actual size --}}
            <div id="catch" class="sk-block sk-catch" data-reveal="panel">
                <div class="sk-duo is-even">
                    <div>
                        <p class="hp-kicker">The Catch</p>
                        <h3 class="sk-h3" style="margin-top: 0.9rem;">Why is it <span class="hp-ink-grad">free?</span></h3>
                        <p class="sk-p">No expiring trial, no locked features, no surprise pricing later. We ask for two things instead.</p>

                        <div class="sk-asks">
                            <div class="sk-ask">
                                <span class="hp-ico"><svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg></span>
                                <div>
                                    <h4 class="sk-h4">The AAL license</h4>
                                    <p>Event Schedule is open source under the Attribution Assurance License. Use it commercially at no cost; just keep the attribution intact.</p>
                                </div>
                            </div>
                            <div class="sk-ask is-link">
                                <span class="hp-ico"><svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg></span>
                                <div>
                                    <h4 class="sk-h4">A small backlink</h4>
                                    <p>Public schedule pages carry a small, discreet link back to eventschedule.com, on every tier you sell, the paid ones included. Your free tier carries your own footer strip in its place, so no page shows two credits. That link is how the project grows, which keeps the software maintained for everyone.</p>
                                </div>
                            </div>
                        </div>

                        <div class="sk-pills" aria-hidden="true">
                            @foreach (['AAL Licensed', '100% Open Source', 'Free Forever'] as $chip)
                                <span class="hp-tag">{{ $chip }}</span>
                            @endforeach
                        </div>

                        @include('marketing.partials.github-star-badge')

                        <div class="hp-actions">
                            <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="hp-btn hp-btn-ghost is-small is-still">
                                {!! $skGithub !!}
                                Contribute on GitHub
                            </a>
                            <a href="https://github.com/eventschedule/eventschedule/issues" target="_blank" rel="noopener noreferrer" class="hp-btn hp-btn-ghost is-small is-still">
                                <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Report an issue
                            </a>
                        </div>
                        <p class="sk-catch-note">Read more about <a href="{{ route('marketing.open_source') }}" class="hp-inline">the open source project</a> or the full <a href="{{ route('marketing.self_hosting_terms') }}" class="hp-inline">self-hosting terms</a>.</p>
                    </div>

                    <figure class="sk-atsize" data-sk-loupe>
                        <p class="sr-only">A mock of the bottom corner of a paying customer's public page: three events, and in the corner a small pill reading Event Schedule, drawn at the size it has on a real page.</p>
                        <div class="sk-corner" aria-hidden="true">
                            <div class="sk-corner-in">
                                <div class="sk-corner-head">
                                    <span class="sk-mark">CC</span>
                                    <div>
                                        <strong>The Comedy Cellar</strong>
                                        <small>comedy.<wbr><span data-sk="domain">yourdomain.com</span></small>
                                    </div>
                                    <em>Follow</em>
                                </div>
                                @foreach ([['Aug', '02', 'Open Mic Night', '8:00 PM · Free'], ['Aug', '09', 'Headliner Showcase', '9:00 PM · $22'], ['Aug', '16', 'Improv Jam', '7:30 PM · $15']] as $ev)
                                    <div class="sk-ev">
                                        <span><small>{{ $ev[0] }}</small><b>{{ $ev[1] }}</b></span>
                                        <div><strong>{{ $ev[2] }}</strong><small>{{ $ev[3] }}</small></div>
                                    </div>
                                @endforeach
                            </div>
                            {{-- The real attribution shown on public schedule pages (see
                                 layouts/app-guest.blade.php), drawn with that layout's own sizes.
                                 This tenant has to stay a PAID one, and the $22/$15 ticket
                                 prices above are what say so. Role::creditChipReason() stands
                                 the chip down wherever the footer strip renders, so a free
                                 tenant here would show the operator's strip and no chip. --}}
                            <div class="sk-corner-foot"><span class="sk-chip" data-sk-chip><i>ES</i>Event Schedule</span></div>
                        </div>
                        <div class="sk-loupe" aria-hidden="true"><div data-sk-lens></div></div>
                        <figcaption>Shown at actual size. That is the whole catch.<small>The corner of a paying customer's page. <span class="sk-by-pointer">Move the glass to check.</span><span class="sk-by-finger">Tap it to move the glass.</span></small></figcaption>
                    </figure>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================================================ -->
    <!-- 02 Tenants                                                   -->
    <!-- ============================================================ -->
    <section id="tenants" class="sk-ch" data-l="2" data-sk-ch="2">
        <div class="hp-wrap">
            <header class="sk-ch-head" data-reveal>
                <div class="sk-glyph" data-at="2" aria-hidden="true"><div class="sk-iso">{!! $skBlank !!}</div></div>
                <div>
                    <p class="sk-ch-no"><b>02</b> / 04 <span>Second layer</span></p>
                    <h2 class="sk-ch-title">Tenants</h2>
                </div>
                <p class="sk-ch-say">Every customer in a space of their own, running a product worth paying you for.</p>
            </header>

            <div class="sk-block">
                <div class="sk-duo">
                    <div data-reveal>
                        <span class="sk-module-no">Provisioning</span>
                        <h3 class="sk-h3">Per-customer subdomains</h3>
                        <p class="sk-p">Every customer gets their own address the moment they sign up. Wildcard DNS in, tenant routing handled.</p>
                    </div>
                    <div class="sk-stg sk-bigbar" data-reveal="panel" aria-hidden="true">
                        <div class="sk-win-bar"><span class="sk-dots"><i></i><i></i><i></i></span><span class="sk-url"><s>https://</s><span data-sk-tenant>acme</span>.<span data-sk="domain">yourdomain.com</span></span></div>
                        @php
                            $skTenants = [['acme', 'Pro'], ['blues-bar', 'Enterprise'], ['startup', 'Free'], ['comedy-cellar', 'Pro'], ['gallery', 'Trial'], ['bookclub', 'Free'], ['expo', 'Enterprise'], ['openmic', 'Pro'], ['makerspace', 'Trial'], ['warehouse', 'Pro']];
                        @endphp
                        <div class="sk-rows">
                            @foreach ($skTenants as $chip)
                                <span @class(['sk-ten', 'is-picked' => $loop->first]) data-tenant="{{ $chip[0] }}"><span>{{ $chip[0] }}.<span data-sk="domain">yourdomain.com</span></span><b @class(['is-ent' => $chip[1] === 'Enterprise', 'is-free' => in_array($chip[1], ['Free', 'Trial'], true)])>{{ $chip[1] }}</b></span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="sk-scale" data-reveal>
                    <span class="sk-module-no" style="margin: 0;">Scale</span>
                    <p class="sk-p" style="margin: 0;">Unlimited customers, unlimited schedules, and no operator tier to buy: every feature ships in the box.</p>
                </div>
            </div>

            {{-- What you are selling --}}
            <div id="product" class="sk-block">
                <div class="hp-head is-center">
                    <p class="hp-kicker" data-reveal>For Your Customers</p>
                    <h3 class="hp-h2" data-reveal>A product worth <span class="hp-ink-grad">subscribing to</span></h3>
                    <p class="hp-lead" data-reveal style="--reveal-delay: 0.1s;">Each customer gets a complete scheduling and ticketing toolkit under your brand, on their own subdomain. Try both sides of it.</p>
                </div>

                <div class="sk-shots" data-reveal-group="90">
                    <div class="sk-shot is-admin" data-reveal="panel">
                        <div class="sk-win">
                            <div class="sk-win-bar" aria-hidden="true"><span class="sk-dots"><i></i><i></i><i></i></span><span class="sk-url">demo.eventschedule.com</span></div>
                            <div class="sk-shot-crop">
                                <picture class="is-day"><source srcset="{{ asset('images/docs/managing-schedules--schedule-tab.webp') }}" type="image/webp"><img src="{{ asset('images/docs/managing-schedules--schedule-tab.png') }}" width="1280" height="757" loading="lazy" decoding="async" alt="The admin portal in the live demo: a schedule's own page, with its tabs and its month of events."></picture>
                                <picture class="is-night"><source srcset="{{ asset('images/docs/managing-schedules--schedule-tab-dark.webp') }}" type="image/webp"><img src="{{ asset('images/docs/managing-schedules--schedule-tab-dark.png') }}" width="1280" height="757" loading="lazy" decoding="async" alt="The admin portal in the live demo: a schedule's own page, with its tabs and its month of events."></picture>
                            </div>
                        </div>
                        <div class="sk-shot-body">
                            <h4 class="sk-h4">The admin portal</h4>
                            <p class="sk-p">Where your customers create events, sell tickets through Stripe Connect or their own PayPal account, track and refund sales, send newsletters, and check attendees in.</p>
                            <a href="{{ demo_url() }}" target="_blank" rel="noopener noreferrer" class="hp-btn hp-btn-primary is-small is-still">
                                Open the Admin Demo
                                {!! $skOut !!}
                            </a>
                        </div>
                    </div>

                    <div class="sk-shot is-guest" data-reveal="panel">
                        <div class="sk-win">
                            <div class="sk-win-bar" aria-hidden="true"><span class="sk-dots"><i></i><i></i><i></i></span><span class="sk-url">simpsons.eventschedule.com</span></div>
                            <div class="sk-shot-crop">
                                <picture class="is-day"><source srcset="{{ asset('images/docs/sharing--guest-portal.webp') }}" type="image/webp"><img src="{{ asset('images/docs/sharing--guest-portal.png') }}" width="1280" height="757" loading="lazy" decoding="async" alt="The guest portal in the live demo: a public schedule page listing the day's events, each with its date, venue and flyer."></picture>
                                <picture class="is-night"><source srcset="{{ asset('images/docs/sharing--guest-portal-dark.webp') }}" type="image/webp"><img src="{{ asset('images/docs/sharing--guest-portal-dark.png') }}" width="1280" height="757" loading="lazy" decoding="async" alt="The guest portal in the live demo: a public schedule page listing the day's events, each with its date, venue and flyer."></picture>
                            </div>
                        </div>
                        <div class="sk-shot-body">
                            <h4 class="sk-h4">The guest portal</h4>
                            <p class="sk-p">The public schedule their attendees see: browse events, buy tickets, RSVP, and carry the QR code that gets scanned at the door.</p>
                            <a href="https://simpsons.eventschedule.com" target="_blank" rel="noopener noreferrer" class="hp-btn hp-btn-primary is-small is-still">
                                Open the Guest Demo
                                {!! $skOut !!}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="sk-dock" data-reveal>
                    <ul>
                        @foreach ([
                            ['Ticketing via Stripe or PayPal', 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                            ['QR check-in', 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z'],
                            ['Calendar sync', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                            ['Newsletters', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                            ['Analytics', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                            ['Schedule pages', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                            ['Recurring events', 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                            ['Embeds', 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                        ] as $feat)
                            <li class="sk-feat">
                                <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feat[1] }}" /></svg>
                                {{ $feat[0] }}
                            </li>
                        @endforeach
                    </ul>
                    <p class="sk-small sk-dock-cap">Every plan tier you sell is backed by the full event platform.</p>
                </div>
            </div>

            {{-- Federation --}}
            <div id="federation" class="sk-block sk-duo is-even">
                <div data-reveal>
                    <p class="hp-kicker">Free, opt-in</p>
                    <h3 class="sk-h3" style="margin-top: 0.9rem;"><span class="hp-ink-grad">Federation</span> sends traffic back to you</h3>
                    <p class="hp-lead" style="margin-top: 1rem;">An optional network that sends discovery traffic back to your platform.</p>
                    <p class="sk-p">Share your customers' public events, online and in person, to the eventschedule.com listings. Every listing links straight back to the event on your platform: extra reach and SEO for your customers. It stays off until you switch it on, and we review each install once before anything is published.</p>
                    <ul class="hp-checks">
                        @foreach (['Discovery traffic flows to your installation', 'Your customers reach a wider audience', 'You turn the network on, then each schedule opts in for itself'] as $li)
                            <li><span class="hp-ok">{!! $skCheck !!}</span>{{ $li }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="sk-stg" data-reveal="panel" aria-hidden="true">
                    <div class="sk-fed">
                        <svg viewBox="0 0 340 200" fill="none" preserveAspectRatio="none" aria-hidden="true">
                            <path class="sk-line" d="M48 46 L170 108" />
                            <path class="sk-line" d="M286 50 L170 108" />
                            <path class="sk-line" d="M44 156 L170 108" />
                            <path class="sk-line" d="M294 158 L170 108" />
                            <circle class="sk-fed-dot" r="3.5" fill="#38bdf8" style="offset-path: path('M48 46 L170 108'); --fd: 0s;" />
                            <circle class="sk-fed-dot" r="3.5" fill="#38bdf8" style="offset-path: path('M44 156 L170 108'); --fd: 1.5s;" />
                            <circle class="sk-fed-dot" r="3.5" fill="#38bdf8" style="offset-path: path('M294 158 L170 108'); --fd: 3s;" />
                            <circle class="sk-fed-dot" r="3.5" fill="#38bdf8" style="offset-path: path('M286 50 L170 108'); --fd: 0.8s;" />
                            <circle class="sk-fed-dot is-back" r="4.5" fill="#2f66ea" style="offset-path: path('M286 50 L170 108'); --fd: 2.6s;" />
                        </svg>
                        <span class="sk-pad" style="--x: 14.1%; --y: 23%;"><i></i><i></i><i></i><b></b></span>
                        <span class="sk-pad" style="--x: 12.9%; --y: 78%;"><i></i><i></i><i></i><b></b></span>
                        <span class="sk-pad" style="--x: 86.5%; --y: 79%;"><i></i><i></i><i></i><b></b></span>
                        <span class="sk-pad is-you" style="--x: 84.1%; --y: 25%;"><i></i><i></i><i></i><i></i><b></b></span>
                        <span class="sk-pad is-hub" style="--x: 50%; --y: 54%;"><i></i><i></i><i></i><i></i><b></b></span>
                        <span class="sk-fed-label" style="left: 50%; top: 73%;">eventschedule.com</span>
                        <span class="sk-fed-label is-you" style="left: 80%; top: 3%;" data-sk="domain">yourdomain.com</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================================================ -->
    <!-- 03 Billing                                                   -->
    <!-- ============================================================ -->
    <section id="billing" class="sk-ch hp-alt" data-l="3" data-sk-ch="3">
        <div class="hp-wrap">
            <header class="sk-ch-head" data-reveal>
                <div class="sk-glyph" data-at="3" aria-hidden="true"><div class="sk-iso">{!! $skBlank !!}</div></div>
                <div>
                    <p class="sk-ch-no"><b>03</b> / 04 <span>Third layer</span></p>
                    <h2 class="sk-ch-title">Billing</h2>
                </div>
                <p class="sk-ch-say">Your prices, your Stripe account. What your customers pay lands with you.</p>
            </header>

            <div class="sk-block sk-trio" data-reveal-group="80">
                <div class="hp-card sk-mod sk-tint is-plans" data-reveal="panel">
                    <span class="sk-module-no">Plans</span>
                    <h3 class="sk-h4">Plan tiers you control</h3>
                    <p class="sk-p">Free, Pro, and Enterprise are built in, with features gated per tier. The split ships with the platform; you set what each tier costs.</p>
                    @php
                        $skMatrix = [
                            ['Schedule pages', true, true, true],
                            ['Sell paid tickets', false, true, true],
                            ['API + webhooks', false, true, true],
                            ['Newsletter emails / month', '10', '100', '1,000'],
                            ['Team members', '1', '1', '5'],
                            ['Custom domains', false, false, true],
                        ];
                    @endphp
                    <div class="sk-mod-obj sk-matrix" aria-hidden="true">
                        <span class="is-h"></span><span class="is-h">Free</span><span class="is-h is-mid">Pro</span><span class="is-h">Ent</span>
                        @foreach ($skMatrix as $row)
                            <span class="is-name"><span>{{ $row[0] }}</span></span>
                            @foreach ([1, 2, 3] as $col)
                                <span @class(['is-mid' => $col === 2])>@if (is_string($row[$col]))<b>{{ $row[$col] }}</b>@else<u @class(['is-yes' => $row[$col]])></u>@endif</span>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <div class="hp-card sk-mod sk-tint is-side" data-reveal="panel">
                    <div>
                        <span class="sk-module-no">Trials</span>
                        <h3 class="sk-h4">Trials you control</h3>
                        <p class="sk-p">One environment variable sets the trial length for your whole platform. Set it to 0 for no trial.</p>
                        <code class="sk-env"><b>TRIAL_DAYS</b>=<em>14</em></code>
                    </div>
                    <div class="sk-ring" aria-hidden="true"><div><b>14</b><small>days</small></div></div>
                </div>

                <div class="hp-card sk-mod sk-tint" data-reveal="panel">
                    <span class="sk-module-no">Billing</span>
                    <h3 class="sk-h4">Subscription billing, wired</h3>
                    <p class="sk-p">Recurring billing runs through your own Stripe account. You create the products, the prices, and the currencies.</p>
                    <div class="sk-mod-obj sk-flow" aria-hidden="true">
                        <span>Customers</span>
                        <div><i style="--fd: 0s;"></i><i style="--fd: 1.1s;"></i><i style="--fd: 2.2s;"></i></div>
                        <span>Your Stripe</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- The math: the page's night. Every light is a customer on your platform. --}}
        <div id="revenue" class="hp-dark on-alt sk-night">
            <div class="hp-wrap">
                <div class="hp-head is-center">
                    <p class="hp-kicker" data-reveal>The Business Model</p>
                    <h3 class="hp-h2" data-reveal>The math works <span class="hp-ink-grad">in your favor</span></h3>
                    <p class="hp-lead" data-reveal style="--reveal-delay: 0.1s;">No license fees and no revenue share means what your customers pay is yours. Your real costs are a server and Stripe processing.</p>
                </div>

                <div id="es-calc" data-reveal>
                    {{-- The order the lights come on in is fixed, so a cached page and its script agree. --}}
                    @php
                        $skLights = (new \Random\Randomizer(new \Random\Engine\Mt19937(20261009)))->shuffleArray(range(0, 499));
                        $skLit = array_flip(array_slice($skLights, 0, 25));
                    @endphp
                    <div class="sk-money-grid">
                        {{-- The Billing slab, seen close: its face is the lights. --}}
                        <div class="sk-city" aria-hidden="true">
                            <div class="sk-city-rig">
                                <div class="sk-city-glow"></div>
                                <div class="sk-city-bloom"></div>
                                <div class="sk-city-slab">
                                    {!! $skSides !!}
                                    <div class="sk-field" id="sk-field" data-order='@json($skLights)'>@for ($i = 0; $i < 500; $i++)<i @class(['is-on' => isset($skLit[$i])])></i>@endfor</div>
                                </div>
                            </div>
                            <p class="sk-field-cap">
                                <span><b id="sk-out-lit">25</b> of 500 lit: one light, one customer</span>
                                <span><i></i>a payment landing in your Stripe</span>
                            </p>
                        </div>

                        <!-- Results -->
                        <div aria-live="polite" class="sk-sum">
                            <p class="sk-sum-brand" aria-hidden="true"><span class="sk-mark" data-sk="mark">TP</span><span data-sk="domain">yourdomain.com</span></p>
                            <div class="sk-sum-label">Your monthly revenue</div>
                            <b id="es-out-month" class="sk-total sk-money">$725</b>
                            <div class="sk-year">That is <b id="es-out-year" class="sk-money">$8,700</b> per year.</div>
                        </div>

                        <!-- Controls -->
                        <div class="sk-knobs">
                            <div class="sk-knob">
                                <div>
                                    <label for="es-r-customers">Customers on your platform</label>
                                    <output id="es-out-customers" for="es-r-customers" aria-live="off">25</output>
                                </div>
                                <input type="range" id="es-r-customers" class="sk-range" min="1" max="500" step="1" value="25" style="--fill: 4.8%;">
                                <div class="sk-ends"><span>1</span><span>500</span></div>
                            </div>
                            <div class="sk-knob">
                                <div>
                                    <label for="es-r-price">Your price per customer per month</label>
                                    <output id="es-out-price" for="es-r-price" class="sk-money" aria-live="off">$29</output>
                                </div>
                                <input type="range" id="es-r-price" class="sk-range" min="5" max="199" step="1" value="29" style="--fill: 12.4%;">
                                <div class="sk-ends"><span>$5</span><span>$199</span></div>
                            </div>
                            <p class="sk-knobs-hint">Slide to match your niche and your pricing. The defaults are just a starting point.</p>
                        </div>

                        <div class="sk-takes">
                            <div>
                                <strong>What Event Schedule takes</strong>
                                <p>No license fee, no revenue share, no per-ticket fees.</p>
                            </div>
                            <div class="sk-ghost" aria-hidden="true">$0</div>
                            <span class="sr-only">Zero dollars.</span>
                        </div>

                        <div class="sk-bars">
                            <div>
                                <span>Your platform</span><span class="sk-money">no revenue share</span>
                            </div>
                            <div class="sk-bar"><i></i></div>
                            <div>
                                <span>A typical 10-30% reseller share</span>
                                <span class="is-lose">hand over <span id="es-out-cut-low">$870</span> to <span id="es-out-cut-high">$2,610</span>/yr</span>
                            </div>
                            <div class="sk-bar is-lose"><i></i></div>
                        </div>
                    </div>
                    <p class="sk-fine">Estimates for illustration. Subscriptions are billed per schedule, so a customer running more than one pays for each. Stripe processing fees apply per transaction and vary by country. Your results depend on your pricing and your customers.</p>
                </div>

                <!-- Illustrative activity ticker -->
                <div class="sk-feed" data-reveal>
                    <div class="es-marquee-mask" aria-hidden="true">
                        <div class="es-marquee" data-marquee="-1">
                            <div class="es-marquee-track">
                                @for ($copy = 0; $copy < 2; $copy++)
                                    @foreach ([['acme upgraded to Pro', '+$29/mo'], ['2 trials started', ''], ['blues-bar renewed Enterprise', '+$99/mo'], ['comedy-cellar sold 40 tickets', ''], ['gallery subscribed to Pro', '+$29/mo'], ['expo added 3 team members', ''], ['makerspace converted from trial', '+$29/mo']] as $tick)
                                        <span class="sk-tick" @if ($copy === 1) data-loop-copy @endif>
                                            {{ $tick[0] }}
                                            @if ($tick[1] !== '')
                                                <b class="sk-money">{{ $tick[1] }}</b>
                                            @endif
                                        </span>
                                    @endforeach
                                @endfor
                            </div>
                        </div>
                    </div>
                    <p class="sk-feed-cap">A feed you could be running.</p>
                </div>
            </div>
        </div>

        <div class="hp-wrap">
            <div class="sk-block" data-reveal style="margin-top: clamp(3.5rem, 7vw, 6rem);">
                <div class="sk-meters-head">
                    <div class="hp-head">
                        <h3 class="sk-h3">
                            Subscriptions are not the only meter
                        </h3>
                        <p class="sk-p">
                            Three more rails ship in the box, all of them off until you switch them on, and none of them
                            available to us on eventschedule.com because we do not run them.
                        </p>
                    </div>
                    <a href="{{ marketing_url('/docs/saas/monetization') }}" class="hp-more">
                        How operator monetization works
                        {!! $skArrow !!}
                    </a>
                </div>
                @php
                    $saasMeters = [
                        [
                            'Ads on your free tier',
                            ['ADS_ENABLED'],
                            'Google AdSense on free-tier public pages only. Never on a paid tier, an embed, a checkout, a custom domain, or an event that is actively selling tickets, so an ad never lands beside an organizer\'s own buy button.',
                        ],
                        [
                            'Promotions between your tenants',
                            ['ADS_ENABLED', 'PROMOTIONS_ENGINE_ENABLED'],
                            'A paying customer buys placement for one of their events on your free tier\'s pages, prepaid by CPM or CPC with unspent budget refunded. You approve each campaign before it serves, and any schedule can decline to carry them.',
                        ],
                        [
                            'Accommodation affiliate',
                            ['STAY22_ENABLED'],
                            'A lodging map near the venue on public event pages, on every tier rather than just the free one. The commission is yours unless a customer supplies their own affiliate ID, which the settings page tells them plainly.',
                        ],
                    ];
                @endphp
                <p class="sr-only" id="sk-meter-note">A preview only: the switch shows the setting turned on and changes nothing.</p>
                <dl class="sk-meters">
                    @foreach ($saasMeters as $meterNo => [$meterName, $meterFlags, $meterBody])
                        <div class="hp-card sk-meter sk-tint">
                            <dt>
                                <span id="sk-meter-{{ $meterNo }}">{{ $meterName }}</span>
                                <button type="button" role="switch" aria-checked="false" aria-labelledby="sk-meter-{{ $meterNo }}" aria-describedby="sk-meter-note" class="sk-switch" data-sk-switch><i></i></button>
                            </dt>
                            <dd class="sk-meter-flag">
                                @foreach ($meterFlags as $meterFlag)
                                    <code class="sk-mono">{{ $meterFlag }}=<span class="is-off">false</span><span class="is-on">true</span></code>
                                @endforeach
                            </dd>
                            <dd>{{ $meterBody }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <p class="sk-closing" data-reveal>This is the technology half of starting an online ticketing business. The other half is finding customers, and you can spend your time there because the platform is already built.</p>
        </div>
    </section>
    <!-- ============================================================ -->
    <!-- 04 Infra                                                     -->
    <!-- ============================================================ -->
    <section id="infra" class="sk-ch" data-l="4" data-sk-ch="4">
        <div class="hp-wrap">
            <header class="sk-ch-head" data-reveal>
                <div class="sk-glyph" data-at="4" aria-hidden="true"><div class="sk-iso">{!! $skBlank !!}</div></div>
                <div>
                    <p class="sk-ch-no"><b>04</b> / 04 <span>The base</span></p>
                    <h2 class="sk-ch-title">Infra</h2>
                </div>
                <p class="sk-ch-say">Your servers and your database, under everything above. Live in three steps.</p>
            </header>

            {{-- Launch plan --}}
            <div id="launch" class="sk-block">
                <div class="hp-head is-center">
                    <p class="hp-kicker" data-reveal>Launch Plan</p>
                    <h3 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Live in <span class="hp-ink-grad">three steps</span>
                    </h3>
                    <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                        Most of the work is choosing your niche and your prices, not writing code.
                    </p>
                </div>

                {{-- One console, three panes: the base slab, opened. Dark in both modes. --}}
                <div class="sk-console" data-reveal="panel">
                    <ol class="sk-panes">
                        <li class="sk-pane">
                            <div class="sk-pane-head" aria-hidden="true"><span class="sk-step-no">1</span><span class="sk-pane-line"></span></div>
                            <h4 class="sk-h4">Deploy your platform</h4>
                            <p class="sk-p">Install with Docker or the Softaculous one-click installer and point wildcard DNS at your server. The <x-link href="{{ route('marketing.docs.saas.setup') }}">setup guide</x-link> covers every step, and the <x-link href="{{ route('marketing.selfhost') }}">selfhost page</x-link> compares install options.</p>
                            <div class="sk-scene" aria-hidden="true">
                                <div class="sk-term">
                                    <div><s>$</s> git clone https://github.com/<wbr>eventschedule/<wbr>dockerfiles.git</div>
                                    <div><s>$</s> cd dockerfiles</div>
                                    <div><q># set DB_PASSWORD and APP_URL in .env</q></div>
                                    <div><s>$</s> docker compose up --build -d</div>
                                </div>
                            </div>
                        </li>

                        <li class="sk-pane">
                            <div class="sk-pane-head" aria-hidden="true"><span class="sk-step-no">2</span><span class="sk-pane-line"></span></div>
                            <h4 class="sk-h4">Wire up the business</h4>
                            <p class="sk-p">Point the logo variables at your own light and dark artwork, connect Stripe, and create the prices for your Pro and Enterprise tiers. One variable sets your trial length.</p>
                            <div class="sk-scene" aria-hidden="true">
                                <div class="sk-envfile">
                                    <div><b>APP_URL</b>=https://app.<span data-sk="domain">yourdomain.com</span></div>
                                    <div><b>APP_LOGO_LIGHT</b>=/images/logo.png</div>
                                    <div><b>TRIAL_DAYS</b>=<em>14</em></div>
                                    <div><b>STRIPE_PLATFORM_SECRET</b>=sk_live_••••</div>
                                </div>
                                <div class="sk-tiers">
                                    @foreach ([['Free', '$0', 0], ['Pro', '$29', 1], ['Enterprise', '$99', 2]] as $tier)
                                        <span><small>{{ $tier[0] }}</small><b class="sk-money">{{ $tier[1] }}<small>/mo</small></b></span>
                                    @endforeach
                                </div>
                            </div>
                        </li>

                        <li class="sk-pane">
                            <div class="sk-pane-head" aria-hidden="true"><span class="sk-step-no">3</span><span class="sk-pane-line"></span></div>
                            <h4 class="sk-h4">Your first customer signs up</h4>
                            <p class="sk-p">They pick a subdomain, start on Free, then subscribe through your Stripe, with your trial length running before the first charge. From here on, growth is a sales problem, not an engineering one.</p>
                            <div class="sk-scene" aria-hidden="true">
                                <div class="sk-first">
                                    <span class="sk-mark">A</span>
                                    <div>
                                        <strong>acme.<wbr><span data-sk="domain">yourdomain.com</span></strong>
                                        <small>Trial started · 14 days</small>
                                    </div>
                                    {!! $skCheck !!}
                                </div>
                                <div class="sk-paid sk-money">+$0 today · $29/mo after trial</div>
                                <small>Straight into your Stripe account</small>
                            </div>
                        </li>
                    </ol>
                </div>

                <!-- Operator docs row -->
                <div class="sk-docs" data-reveal>
                    <span class="sk-module-no" style="margin: 0 0.4rem 0 0;">Operator docs</span>
                    @foreach ([['SaaS setup guide', route('marketing.docs.saas.setup')], ['Custom domains for customers', route('marketing.docs.saas.custom_domains')], ['Twilio SMS setup', route('marketing.docs.saas.twilio')]] as $doc)
                        <a href="{{ $doc[1] }}">
                            {{ $doc[0] }}
                            {!! $skArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Build vs rent vs own --}}
            <div id="compare" class="sk-block">
                <div class="hp-head is-center">
                    <p class="hp-kicker" data-reveal>The Decision</p>
                    <h3 class="hp-h2" data-reveal>Build it, rent it, or <span class="hp-ink-grad">own it</span></h3>
                    <p class="hp-lead" data-reveal style="--reveal-delay: 0.1s;">Three ways into the ticketing business. Only one gives you both speed and ownership.</p>
                </div>

                <div class="sk-ways" data-reveal-group="100">
                    <!-- Build -->
                    <div class="hp-card sk-way is-build" data-reveal>
                        <div class="sk-mini" aria-hidden="true"><div class="sk-iso">{!! $skBlank !!}</div></div>
                        <p class="sk-way-key" aria-hidden="true">Four layers to build</p>
                        <div class="sk-way-top">
                            <h4 class="sk-h4">Build from scratch</h4>
                            <span class="sk-when">12+ months</span>
                        </div>
                        <ul>
                            @foreach (['Multi-tenancy, billing, ticketing, check-in, calendar sync, GDPR: all on you', 'Every feature request lands on your backlog', 'Total control, eventually'] as $li)
                                <li><svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M5 12h14" /></svg>{{ $li }}</li>
                            @endforeach
                        </ul>
                        <p class="sk-verdict">Right if the platform itself is your moat.</p>
                    </div>

                    <!-- Rent -->
                    <div class="hp-card sk-way is-rent" data-reveal>
                        <div class="sk-mini" aria-hidden="true"><div class="sk-iso">{!! $skBlank !!}</div></div>
                        <p class="sk-way-key" aria-hidden="true">Their stack, your logo on top</p>
                        <div class="sk-way-top">
                            <h4 class="sk-h4">Reseller programs</h4>
                            <span class="sk-when">Days to launch</span>
                        </div>
                        <ul>
                            @foreach (['Typically a revenue share plus per-ticket fees, forever', 'No code access, and it is their roadmap', 'Your brand on a platform you can lose'] as $li)
                                <li><svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>{{ $li }}</li>
                            @endforeach
                        </ul>
                        <p class="sk-verdict">Fast, but you are renting your own business.</p>
                    </div>

                    <!-- Own (highlighted) -->
                    <div class="hp-card sk-way is-own sk-own" data-reveal>
                        <div class="sk-mini" aria-hidden="true"><div class="sk-iso">{!! $skBlank !!}</div></div>
                        <p class="sk-way-key" aria-hidden="true">All four layers, yours</p>
                        <p class="sk-own-tag">Own it</p>
                        <div class="sk-way-top">
                            <h4 class="sk-h4">Selfhost Event Schedule</h4>
                            <span class="sk-when">Days to launch</span>
                        </div>
                        <ul>
                            @php
                                $ownBullets = [
                                    '$0 license, no revenue share, no per-ticket fees',
                                    'Full code access, your data in your MySQL database',
                                    'Multi-tenant billing built in, white-label bar one credit',
                                ];
                            @endphp
                            @foreach ($ownBullets as $li)
                                <li><svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>{{ $li }}</li>
                            @endforeach
                        </ul>
                        <p class="sk-trade">The trade: you run the servers and keep a small attribution link.</p>
                        <p class="sk-verdict">Speed and ownership. <x-link href="{{ route('marketing.selfhost') }}">See how selfhosting works</x-link></p>
                    </div>
                </div>

                <p class="sk-small sk-way-foot" data-reveal>As far as we know, Event Schedule is one of the few open source ticketing platforms with the multi-tenant subscription layer built in. Don't take our word for it: <x-link href="https://github.com/eventschedule/eventschedule" target="_blank">read the code</x-link></p>
            </div>

            {{-- Two ways to run it --}}
            <div id="paths" class="sk-block">
                <div class="hp-head is-center">
                    <h3 class="hp-h2" data-reveal>Two ways to <span class="hp-ink-grad">run it</span></h3>
                </div>
                <div class="sk-paths" data-reveal-group="90">
                    <!-- Selfhost for yourself -->
                    <div class="hp-card sk-path" data-reveal>
                        <div class="sk-path-tag">Just want your own instance?</div>
                        <h4 class="sk-h4">Selfhost for yourself</h4>
                        <p class="sk-p">Run Event Schedule for your own events, with every Enterprise feature unlocked and zero platform fees.</p>
                        <div class="sk-path-foot" aria-hidden="true">
                            <div class="sk-mini is-one"><div class="sk-iso"><div class="sk-slab" data-l="4">{!! $skSidesFew !!}<div class="sk-face"></div></div></div></div>
                            <code class="sk-mono"><s>$</s> docker compose up --build -d</code>
                        </div>
                        <a href="{{ route('marketing.selfhost') }}" class="hp-more sk-more-green">
                            Explore selfhosting
                            {!! $skArrow !!}
                        </a>
                    </div>

                    <!-- Build a business (forward card last) -->
                    <div class="hp-card sk-path is-here" data-reveal>
                        <div class="sk-path-tag">Build a business on it</div>
                        <h4 class="sk-h4">You are on the right page</h4>
                        <p class="sk-p">Turn the same install into a white-label ticketing SaaS: multi-tenant subdomains, subscription billing, and plan gating included.</p>
                        <div class="sk-path-foot" aria-hidden="true">
                            <div class="sk-mini sk-own"><div class="sk-iso">{!! $skBlank !!}</div></div>
                            <span class="sk-cap" style="margin: 0;">the stack, yours</span>
                        </div>
                        <a href="{{ route('marketing.docs.saas.setup') }}" class="hp-btn hp-btn-primary is-small">
                            Read the SaaS Setup Guide
                            {!! $skArrow !!}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>
    <!-- ============================================================ -->
    <!-- FAQ                                                          -->
    <!-- ============================================================ -->
    @php
        $faqs = [
            [
                'q' => 'What is white-label ticketing software?',
                'a' => 'White-label ticketing software is an event ticketing platform you rebrand and sell as your own product. Event Schedule goes further than most: you selfhost the entire open source platform, so customers sign up on your domain and pay through your Stripe account. The only trace of us is a small attribution link on the public pages of the customers you charge.',
            ],
            [
                'q' => 'How do I start an online ticketing business?',
                'a' => 'The technology half is now the easy half: deploy Event Schedule on a server, connect Stripe, set your prices, and open sign-ups. That gives you your own Eventbrite-style platform with subscriptions, ticketing, and check-in built in. The real work is picking a niche and finding your first customers, and you can put your energy there.',
            ],
            [
                'q' => 'How does the free white-label license work?',
                'a' => 'Event Schedule is open source under the Attribution Assurance License (AAL). You can run it commercially at no cost as long as you keep the attribution, which appears as a small link on public schedule pages, on the tiers you charge for. There are no license fees, no revenue share, and no per-ticket fees.',
            ],
            [
                'q' => 'Can I set my own subscription prices?',
                'a' => 'Yes. You create the products and prices in your own Stripe account, and the built-in Free, Pro, and Enterprise tiers use them. You also control the trial length with a single TRIAL_DAYS setting. Whatever your customers pay lands in your Stripe account, and subscriptions are billed per schedule, so a customer who runs three pays for each one they upgrade.',
            ],
            [
                'q' => 'Is there a limit on customers or ticket sales?',
                'a' => 'There is no cap on the number of customers or schedules, and nobody takes a cut of a ticket sale. The built-in Free tier does have allowances your customers upgrade past: 10 newsletter emails a month (each recipient counts as one) and 25 fan photos. Selling a ticket that carries a price belongs to Pro and Enterprise, which is the upgrade most of your customers will buy, while free RSVPs and registration stay unlimited on every tier.',
            ],
            [
                'q' => 'How do my customers get paid for their tickets?',
                'a' => 'Straight into their own accounts. A customer connects Stripe through your platform\'s Stripe Connect, or connects their own PayPal account, which needs nothing set up on your side. Payfast (for rand), Invoice Ninja, a payment link and cash are there too, each connected by the customer. Nothing in the checkout takes a cut, so your revenue comes from subscriptions and the operator rails (ads, promotions, the accommodation map), not from your customers\' ticket sales. A Stripe or PayPal sale can be refunded from the Sales page, in full or in part, and the money goes back through the provider.',
                'more' => ['label' => 'How PayPal checkout works', 'href' => 'marketing.paypal'],
            ],
            [
                'q' => 'How is this different from a reseller or partner program?',
                'a' => 'Reseller programs rent you a brand skin on someone else\'s platform: typically a revenue share, per-ticket fees, and no code access. With Event Schedule you run the actual software on your own servers. Nobody can raise your rates, change your terms, or switch your platform off.',
            ],
            [
                'q' => 'What do I need to host it?',
                'a' => 'A server you control (a small VPS is enough to start), a domain with wildcard DNS for customer subdomains, and a Stripe account for billing. Install with Docker or the one-click Softaculous installer. The setup guide covers everything from environment variables to going live. One optional extra is worth knowing about: add Google Wallet issuer credentials once, and every customer\'s buyers get an Add to Google Wallet button with their tickets.',
                'more' => ['label' => 'Set up Google Wallet passes', 'href' => 'marketing.docs.selfhost.google_wallet'],
            ],
            [
                'q' => 'Who handles GDPR and data protection?',
                'a' => 'You do, because the platform runs on your infrastructure and you are the data controller for your customers. That is a responsibility, but also an advantage: the data stays on servers you choose, and the open source code means you can audit exactly how it is handled.',
            ],
            [
                'q' => 'Can my customers use their own domain?',
                'a' => 'Yes. Every customer gets a subdomain like acme.yourdomain.com out of the box, and a schedule on your Enterprise tier can additionally be served from the customer\'s own domain. The custom domains guide covers the DNS and proxy setup.',
                'more' => ['label' => 'Read the custom domains guide', 'href' => 'marketing.docs.saas.custom_domains'],
            ],
            [
                'q' => 'Do I get all features, or is there a paid tier for operators?',
                'a' => 'There is no paid tier for operators: you get the whole codebase and we never bill you. A single-tenant install runs with every Enterprise feature unlocked. In SaaS mode the tiers apply to every schedule on the platform, including your own, so grant yourself a plan from /admin to unlock Pro and Enterprise screens. The feature split ships with the platform; the prices are yours to set.',
            ],
            [
                'q' => 'What can I do from the admin panel?',
                'a' => 'Run the platform. See users, revenue, analytics and usage across every customer, grant any schedule a plan by hand, approve paid promotions before they serve, and manage your customers\' custom domains. You can also edit any schedule\'s name, subdomain and contact details, release a subdomain someone is sitting on, and restore that schedule later. Each of those schedule changes is written to the audit log.',
                'more' => ['label' => 'Read the admin panel guide', 'href' => 'marketing.docs.selfhost.admin'],
            ],
        ];
    @endphp
    <x-seo.faq-schema :items="$faqs" />
    <section id="faq" class="hp-sec hp-alt">
        <div class="hp-wrap">
            <div class="hp-faq-grid">
                <div class="hp-head">
                    <p class="hp-kicker" data-reveal>Common Questions</p>
                    <h2 class="hp-h2" data-reveal>Frequently asked <span class="hp-ink-grad">questions</span></h2>
                    <p class="hp-lead" data-reveal style="--reveal-delay: 0.1s;">What operators ask before they launch.</p>
                </div>
                <div data-reveal-group="60">
                    @foreach ($faqs as $faq)
                        <details name="faq" data-reveal class="hp-faq">
                            <summary>
                                <h3>{{ $faq['q'] }}</h3>
                                <i aria-hidden="true"></i>
                            </summary>
                            <div class="faq-answer">
                                <p>{{ $faq['a'] }}</p>
                                @isset($faq['more'])
                                    <p><x-link href="{{ route($faq['more']['href']) }}">{{ $faq['more']['label'] }}</x-link></p>
                                @endisset
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- Finale                                                       -->
    <!-- ============================================================ -->
    <section id="start" class="hp-sec">
        <div class="hp-wrap">
            <div class="hp-finale" data-reveal="panel">
                <div class="sk-finale">
                    <div class="sk-rig" aria-hidden="true">{!! $skStack !!}</div>
                    <div class="sk-finale-copy">
                        <div class="sk-finale-brand" data-sk-box>
                            <span class="sk-mark" data-sk="mark" aria-hidden="true">TP</span>
                            <div>
                                <label for="sk-name-again">Name your platform</label>
                                <input id="sk-name-again" class="sk-wordmark" type="text" maxlength="22" placeholder="TicketPilot" autocomplete="off" autocapitalize="words" spellcheck="false" enterkeyhint="done" data-sk-name>
                                <span class="sk-finale-dom" data-sk="domain" aria-hidden="true">yourdomain.com</span>
                            </div>
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                        </div>
                        <p class="sk-flowline" aria-hidden="true">
                            clone
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 7l5 5-5 5" /></svg>
                            configure
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 7l5 5-5 5" /></svg>
                            <b>charge</b>
                        </p>
                        <h2 class="hp-h2">
                            Ready to own <span class="hp-ink-grad">the whole stack?</span>
                        </h2>
                        <p class="hp-lead">
                            The setup guide takes you from a fresh server to live customer sign-ups. Stuck on anything? Open an issue on GitHub.
                        </p>

                        <div class="hp-actions">
                            <a href="{{ app_url('/sign_up') }}" class="hp-btn sk-btn-night">
                                Or use eventschedule.com instead
                            </a>
                            <a href="{{ route('marketing.docs.saas.setup') }}" class="hp-btn hp-btn-primary">
                                Read the Setup Guide
                                {!! $skArrow !!}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- The page's own behaviour: the name that runs through the mocks, the glass over the catch,
         the tenant chips and their address bar, the revenue sums and their lights, the three
         preview switches, and the margin stack. It answers the visitor; the one thing it does
         unasked is run the first slider up and back, once. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var root = document.getElementById('hp');
            if (!root) return;
            var calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            // The glass is filled first, so the name reaches the copy inside it too.
            var fig = root.querySelector('[data-sk-loupe]');
            if (fig) {
                var corner = fig.querySelector('.sk-corner');
                var lens = fig.querySelector('[data-sk-lens]');
                var glass = fig.querySelector('.sk-loupe');
                var chip = fig.querySelector('[data-sk-chip]');
                var copy = corner.cloneNode(true);
                copy.querySelectorAll('[data-sk-chip]').forEach(function (el) { el.removeAttribute('data-sk-chip'); });
                lens.appendChild(copy);
                var put = function (fx, fy, lx, ly) {
                    fig.style.setProperty('--fx', fx.toFixed(1) + 'px');
                    fig.style.setProperty('--fy', fy.toFixed(1) + 'px');
                    fig.style.setProperty('--lx', lx.toFixed(1) + 'px');
                    fig.style.setProperty('--ly', ly.toFixed(1) + 'px');
                };
                // Measured in layout units, not on screen: the panel is still scaled down while it reveals.
                var spot = function (el) {
                    var x = 0;
                    var y = 0;
                    while (el && el !== fig) { x += el.offsetLeft; y += el.offsetTop; el = el.offsetParent; }
                    return [x, y];
                };
                var park = function () {
                    var at = spot(chip);
                    var hw = glass.offsetWidth / 2;
                    var fx = at[0] + chip.offsetWidth / 2;
                    put(fx, at[1] + chip.offsetHeight / 2, Math.min(fx, fig.offsetWidth - hw + 10), at[1] - glass.offsetHeight / 2 - 14);
                };
                var fit = function () {
                    copy.style.width = corner.offsetWidth + 'px';
                    copy.style.height = corner.offsetHeight + 'px';
                    fig.classList.add('is-ready');
                    park();
                };
                fit();
                if ('ResizeObserver' in window) { new ResizeObserver(fit).observe(corner); }
                var look = function (e) {
                    var a = fig.getBoundingClientRect();
                    var k = a.width ? fig.offsetWidth / a.width : 1;
                    var hw = glass.offsetWidth / 2;
                    var x = (e.clientX - a.left) * k;
                    var y = (e.clientY - a.top) * k;
                    fig.classList.add('is-live');
                    put(x, y, Math.min(Math.max(x, hw - 10), fig.offsetWidth - hw + 10), Math.max(glass.offsetHeight / 2 - 10, y - glass.offsetHeight / 2 - 18));
                };
                corner.addEventListener('pointermove', function (e) { if (e.pointerType !== 'touch') { look(e); } });
                corner.addEventListener('pointerdown', function (e) { if (e.pointerType === 'touch') { look(e); fig.classList.remove('is-live'); } });
                corner.addEventListener('pointerleave', function (e) {
                    if (e.pointerType === 'touch') return;
                    fig.classList.remove('is-live');
                    park();
                });
            }

            // Name your platform: the logo and the domain take it, wherever they are drawn.
            var input = document.getElementById('sk-name-input');
            var initials = function (name) {
                var words = name.split(/[\s\-_.&]+/).filter(Boolean);
                var caps = name.match(/[A-Z0-9]/g) || [];
                var pair = words.length > 1
                    ? Array.from(words[0])[0] + Array.from(words[1])[0]
                    : (caps.length > 1 ? caps[0] + caps[1] : Array.from(name).slice(0, 2).join(''));
                return pair.toUpperCase();
            };
            var named = function (value) {
                var name = Array.from((value || '').replace(/\s+/g, ' ').trim()).slice(0, 22).join('');
                var plain = name.normalize ? name.normalize('NFKD') : name;
                var slug = plain.toLowerCase().replace(/[^a-z0-9]+/g, '').slice(0, 20);
                var mark = name ? initials(name) : '';
                root.querySelectorAll('[data-sk]').forEach(function (el) {
                    if (!el.hasAttribute('data-sk-default')) { el.setAttribute('data-sk-default', el.textContent); }
                    var kind = el.getAttribute('data-sk');
                    var fallback = el.getAttribute('data-sk-default');
                    var text = kind === 'name' ? (name || fallback) : (kind === 'mark' ? (mark || fallback) : (slug ? slug + '.com' : fallback));
                    if (el.textContent !== text) { el.textContent = text; }
                });
            };
            var boxes = Array.prototype.slice.call(root.querySelectorAll('[data-sk-name]'));
            boxes.forEach(function (box) {
                box.addEventListener('input', function () {
                    boxes.forEach(function (other) { if (other !== box) { other.value = box.value; } });
                    named(box.value);
                });
                var shell = box.closest('[data-sk-box]');
                if (shell) { shell.addEventListener('click', function (e) { if (e.target !== box) { box.focus(); } }); }
            });
            if (input && input.value) {
                boxes.forEach(function (other) { other.value = input.value; });
                named(input.value);
            }

            // Revenue: two sliders, the sums, and one light for every customer.
            var customers = document.getElementById('es-r-customers');
            var price = document.getElementById('es-r-price');
            var outMonth = document.getElementById('es-out-month');
            if (customers && price && outMonth) {
                var outCustomers = document.getElementById('es-out-customers');
                var outPrice = document.getElementById('es-out-price');
                var outYear = document.getElementById('es-out-year');
                var outCutLow = document.getElementById('es-out-cut-low');
                var outCutHigh = document.getElementById('es-out-cut-high');
                var outLit = document.getElementById('sk-out-lit');
                var field = document.getElementById('sk-field');
                var cells = field ? Array.prototype.slice.call(field.children) : [];
                var order = [];
                try { order = JSON.parse(field.getAttribute('data-order')).map(function (i) { return cells[i]; }); } catch (e) { order = cells; }
                var lit = cells.filter(function (cell) { return cell.classList.contains('is-on'); }).length;
                var glows = Array.prototype.slice.call(root.querySelectorAll('.sk-city-glow, .sk-city-bloom'));
                var fmt = new Intl.NumberFormat('en-US');
                var fill = function (range) {
                    var min = parseFloat(range.min) || 0;
                    var max = parseFloat(range.max) || 100;
                    var val = parseFloat(range.value) || 0;
                    range.style.setProperty('--fill', (((val - min) / (max - min)) * 100).toFixed(1) + '%');
                };
                var light = function (count) {
                    count = Math.min(count, order.length);
                    var k;
                    for (k = lit; k < count; k++) { order[k].classList.add('is-on'); }
                    for (k = count; k < lit; k++) { order[k].classList.remove('is-on'); }
                    lit = count;
                    glows.forEach(function (glow) { glow.style.setProperty('--lit', (count / order.length).toFixed(3)); });
                };
                var update = function () {
                    var c = parseInt(customers.value, 10) || 0;
                    var p = parseInt(price.value, 10) || 0;
                    var yearly = c * p * 12;
                    if (outCustomers) outCustomers.textContent = fmt.format(c);
                    if (outPrice) outPrice.textContent = '$' + fmt.format(p);
                    outMonth.textContent = '$' + fmt.format(c * p);
                    if (outYear) outYear.textContent = '$' + fmt.format(yearly);
                    if (outCutLow) outCutLow.textContent = '$' + fmt.format(Math.round(yearly * 0.1));
                    if (outCutHigh) outCutHigh.textContent = '$' + fmt.format(Math.round(yearly * 0.3));
                    if (outLit) outLit.textContent = fmt.format(c);
                    customers.setAttribute('aria-valuetext', fmt.format(c) + ' customers');
                    price.setAttribute('aria-valuetext', '$' + fmt.format(p) + ' a month');
                    fill(customers);
                    fill(price);
                    light(c);
                };
                customers.addEventListener('input', update);
                price.addEventListener('input', update);
                update();

                // Once, when the band first comes into view: the first slider runs up and back by
                // itself, so the lights and the sum show what they do. Any touch ends it.
                var panel = document.getElementById('es-calc');
                var touched = false;
                ['pointerdown', 'keydown', 'touchstart'].forEach(function (kind) {
                    customers.addEventListener(kind, function () { touched = true; }, { passive: true });
                    price.addEventListener(kind, function () { touched = true; }, { passive: true });
                });
                var sweep = function () {
                    if (calm || touched) return;
                    var rest = parseInt(customers.value, 10) || 25;
                    var live = panel.querySelectorAll('[aria-live="polite"]');
                    var began = 0;
                    live.forEach(function (el) { el.setAttribute('aria-live', 'off'); });
                    var step = function (now) {
                        if (!began) { began = now; }
                        var t = Math.min(1, (now - began) / 2600);
                        if (touched) { t = 1; } else {
                            customers.value = Math.round(rest + (300 - rest) * Math.pow(Math.sin(Math.PI * t), 1.4));
                            update();
                        }
                        if (t < 1) { window.requestAnimationFrame(step); return; }
                        if (!touched) { customers.value = rest; update(); }
                        live.forEach(function (el) { el.setAttribute('aria-live', 'polite'); });
                    };
                    window.setTimeout(function () { window.requestAnimationFrame(step); }, 500);
                };
                if (panel && !calm && 'MutationObserver' in window && document.documentElement.classList.contains('es-anim') && !panel.classList.contains('is-revealed')) {
                    new MutationObserver(function (changes, watcher) {
                        if (panel.classList.contains('is-revealed')) { watcher.disconnect(); sweep(); }
                    }).observe(panel, { attributes: true, attributeFilter: ['class'] });
                }
            }

            // A tenant chip puts its name in the address bar above it.
            var tenant = root.querySelector('[data-sk-tenant]');
            var tenants = tenant ? tenant.closest('.sk-bigbar') : null;
            if (tenants) {
                var pick = function (e) {
                    var chip = e.target && e.target.closest ? e.target.closest('[data-tenant]') : null;
                    if (!chip || chip.classList.contains('is-picked')) return;
                    tenants.querySelectorAll('[data-tenant].is-picked').forEach(function (el) { el.classList.remove('is-picked'); });
                    chip.classList.add('is-picked');
                    tenant.textContent = chip.getAttribute('data-tenant');
                };
                tenants.addEventListener('pointerover', pick);
                tenants.addEventListener('click', pick);
            }

            // The three other meters: each switch shows its setting on. A picture; nothing is set.
            root.addEventListener('click', function (e) {
                var toggle = e.target && e.target.closest ? e.target.closest('[data-sk-switch]') : null;
                if (!toggle) return;
                var on = toggle.getAttribute('aria-checked') !== 'true';
                toggle.setAttribute('aria-checked', on ? 'true' : 'false');
                var card = toggle.closest('.sk-meter');
                if (card) { card.classList.toggle('is-on', on); }
            });

            // The margin stack follows the chapter that crosses the middle of the window.
            var rail = root.querySelector('[data-sk-rail]');
            if (rail && 'IntersectionObserver' in window) {
                var mark = rail.querySelector('.sk-glyph');
                var middle = { rootMargin: '-50% 0px -50% 0px', threshold: 0 };
                var inside = 0;
                var openers = 0;
                var whole = 0;
                var show = function () { rail.classList.toggle('is-on', inside > 0 && openers === 0 && whole === 0); };
                var chapters = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        inside += entry.isIntersecting ? 1 : (entry.target.skSeen ? -1 : 0);
                        entry.target.skSeen = entry.isIntersecting;
                        if (entry.isIntersecting) { mark.setAttribute('data-at', entry.target.getAttribute('data-sk-ch')); }
                    });
                    show();
                }, middle);
                root.querySelectorAll('[data-sk-ch]').forEach(function (chapter) { chapters.observe(chapter); });
                var heads = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        openers += entry.isIntersecting ? 1 : (entry.target.skSeen ? -1 : 0);
                        entry.target.skSeen = entry.isIntersecting;
                    });
                    show();
                }, { rootMargin: '-64px 0px 0px 0px', threshold: 0 });
                root.querySelectorAll('.sk-ch-head').forEach(function (head) { heads.observe(head); });
                // "Build, rent or own" and "Two ways to run it" are about all four layers.
                var all = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        whole += entry.isIntersecting ? 1 : (entry.target.skSeen ? -1 : 0);
                        entry.target.skSeen = entry.isIntersecting;
                    });
                    show();
                }, middle);
                root.querySelectorAll('#compare, #paths').forEach(function (block) { all.observe(block); });
                var night = root.querySelector('.sk-night');
                if (night) {
                    new IntersectionObserver(function (entries) {
                        rail.classList.toggle('on-night', entries[0].isIntersecting);
                    }, middle).observe(night);
                }
            }
            var lights = document.getElementById('sk-field');
            if (lights && 'IntersectionObserver' in window) {
                new IntersectionObserver(function (entries) {
                    lights.classList.toggle('is-live', entries[0].isIntersecting);
                }, { threshold: 0 }).observe(lights);
            }

            // The hero's stack: once it has come apart its layers answer at once, and it
            // leans a little toward the pointer.
            var rig = root.querySelector('.sk-unstack');
            if (rig) {
                var settle = function () { window.setTimeout(function () { rig.classList.add('is-settled'); }, 1700); };
                if (rig.classList.contains('is-revealed') || !document.documentElement.classList.contains('es-anim')) {
                    settle();
                } else if ('MutationObserver' in window) {
                    new MutationObserver(function (changes, watcher) {
                        if (rig.classList.contains('is-revealed')) { watcher.disconnect(); settle(); }
                    }).observe(rig, { attributes: true, attributeFilter: ['class'] });
                }
                // The lean is the same matrix again, a few degrees round.
                var iso = rig.querySelector('.sk-iso');
                var hero = document.getElementById('top');
                if (iso && hero && !calm && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                    var frame = 0;
                    var want = [0, 0];
                    var have = [0, 0];
                    var lean = function () {
                        have[0] += (want[0] - have[0]) * 0.12;
                        have[1] += (want[1] - have[1]) * 0.12;
                        var z = (-45 + have[0] * -6) * Math.PI / 180;
                        var x = (56 + have[1] * -3) * Math.PI / 180;
                        iso.style.setProperty('--ma', Math.cos(z).toFixed(4));
                        iso.style.setProperty('--mb', (Math.sin(z) * Math.cos(x)).toFixed(4));
                        iso.style.setProperty('--mc', (-Math.sin(z)).toFixed(4));
                        iso.style.setProperty('--md', (Math.cos(z) * Math.cos(x)).toFixed(4));
                        frame = (Math.abs(want[0] - have[0]) + Math.abs(want[1] - have[1]) > 0.002) ? window.requestAnimationFrame(lean) : 0;
                    };
                    hero.addEventListener('pointermove', function (e) {
                        var box = hero.getBoundingClientRect();
                        want = [(e.clientX - box.left) / box.width - 0.5, (e.clientY - box.top) / box.height - 0.5];
                        if (!frame) { frame = window.requestAnimationFrame(lean); }
                    });
                    hero.addEventListener('pointerleave', function () {
                        want = [0, 0];
                        if (!frame) { frame = window.requestAnimationFrame(lean); }
                    });
                }
            }
        })();
    </script>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
