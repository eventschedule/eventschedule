<x-marketing-layout :hp="true">
    <x-slot name="title">{{ __('marketing.selfhost_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.selfhost_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">Selfhost</x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule - Selfhosted"
        :description="__('marketing.selfhost_description')" />
    @php
        // One array drives the steps beside the install stage and this HowTo block, so the
        // markup and the schema can never drift apart.
        $howToSteps = [
            [
                'name' => 'Install the files',
                'short' => 'Install',
                'text' => 'Use the one-click Softaculous installer on a cPanel host, bring up the Docker Compose stack, or download the latest release and point your web server at the public directory.',
            ],
            [
                'name' => 'Run the setup wizard',
                'short' => 'Wizard',
                'text' => 'Leave APP_URL blank in .env and open your domain. The browser wizard tests your database connection, creates your admin account, runs the database migrations and writes the configuration back to .env for you.',
            ],
            [
                'name' => 'Add the cron entry',
                'short' => 'Cron entry',
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
    {{-- FAQ JSON-LD is emitted beside the visible FAQ near the end of the page, driven by one $selfhostFaqs array so the markup always matches the rendered content. --}}
    </x-slot>

    @php
        $ghPath = 'M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z';
        $checkPath = 'M5 13l4 4L19 7';
        $arrowPath = 'M13 7l5 5m0 0l-5 5m5-5H6';
        $outPath = 'M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14';

        // The address every screen on the page is drawn at until a visitor types their own.
        $placeDomain = 'your-domain.com';

        // Real version numbers, read from the updater config so the update screen on this page
        // can never drift from the shipped release.
        $installedVersion = config('self-update.version_installed', 'v1.0.121');
        $nextVersion = preg_replace_callback('/(\d+)$/', fn ($m) => (int) $m[1] + 1, $installedVersion);

        // Every command below is taken verbatim from the installation guide
        // (resources/views/marketing/docs/selfhost/installation.blade.php) or from the
        // dockerfiles repo README. Nothing here is invented.
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
        // The one the stage opens on: its three commands are the page's shortest honest answer
        // to "how hard is it".
        $firstMethod = 'docker';
        $cronLine = '* * * * * php /path/to/eventschedule/artisan schedule:run';
    @endphp

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           /selfhost: "Watch it come up".

           The page is one run, from an empty server to a platform that is
           yours: the install played on a stage, the plans switched on one by
           one until nothing is left dark, and then the switches an operator
           holds. A visitor who types a domain in the hero sees every screen
           on the page drawn at that address.

           Two colour rules carry the whole page, as they always have here:
             emerald = included, free, yours
             amber   = yours to run, and NOTHING else (the server, SSL, the
                       cron entry, backups, mail, disk)

           Nothing blinks and nothing loops: a row of blinking cursors was
           taken off this page by request. Every sequence plays once, when it
           is first seen, and ends on a state that is true without it.

           The house style (partials/hp-kit) gives the page its typeface,
           paper and navy. Everything below is this page's own, under sh-.
           ============================================================== */

        #hp {
            --sh-em: #047857;
            --sh-em-2: #0f766e;
            --sh-em-lit: #34d399;
            --sh-em-tint: rgba(16, 185, 129, 0.1);
            --sh-em-edge: rgba(5, 150, 105, 0.34);
            --sh-am: #92400e;
            --sh-am-lit: #fbbf24;
            --sh-am-tint: #fffbeb;
            --sh-am-edge: #fcd34d;
            --sh-term: #06100c;
            --sh-term-edge: rgba(52, 211, 153, 0.2);
            --sh-term-ink: #d1fae5;
            --sh-term-dim: #93b8a8;
            --sh-chrome: #e9edf6;
            --sh-chrome-edge: rgba(10, 16, 32, 0.1);
            --sh-screen: #f4f6fb;
            --sh-field: #ffffff;
        }
        .dark #hp {
            --sh-em: #34d399;
            --sh-em-2: #2dd4bf;
            --sh-em-tint: rgba(16, 185, 129, 0.13);
            --sh-em-edge: rgba(52, 211, 153, 0.32);
            --sh-am: #fbbf24;
            --sh-am-tint: rgba(245, 158, 11, 0.09);
            --sh-am-edge: rgba(251, 191, 36, 0.32);
            --sh-chrome: #111a2e;
            --sh-chrome-edge: rgba(255, 255, 255, 0.09);
            --sh-screen: #070a14;
            --sh-field: #0e1424;
        }

        /* Page accent: the lit phrase of a heading. The kit leaves this class alone by name. */
        .text-gradient-selfhost {
            background: linear-gradient(120deg, #059669 0%, #047857 45%, #0f766e 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            padding-inline-end: 0.05em;
        }
        .dark .text-gradient-selfhost,
        #hp .hp-dark .text-gradient-selfhost,
        #hp .hp-finale .text-gradient-selfhost,
        #hp .sh-pre .text-gradient-selfhost {
            background-image: linear-gradient(120deg, #6ee7b7 0%, #34d399 50%, #2dd4bf 100%);
        }

        /* Section opener: ~/eventschedule $ install */
        .sh-prompt {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            font-family: var(--hp-mono);
            font-size: 0.78rem;
            font-weight: 700;
            font-variation-settings: normal;
            letter-spacing: 0.08em;
            color: var(--sh-em);
        }
        .sh-prompt::before { content: ""; flex: none; width: 0.45rem; height: 0.45rem; border-radius: 0.14rem; background: currentColor; }
        .sh-prompt span { font-weight: 400; opacity: 0.75; }
        #hp .hp-dark .sh-prompt,
        #hp .hp-finale .sh-prompt,
        #hp .sh-pre .sh-prompt { color: var(--sh-em-lit); }
        #hp .hp-head .sh-prompt + .hp-h2 { margin-top: 1rem; }

        /* Small parts */
        .sh-exit {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            min-height: 1.6rem;
            padding: 0 0.65rem;
            border: 1px solid var(--sh-em-edge);
            border-radius: 999px;
            background: var(--sh-em-tint);
            font-family: var(--hp-mono);
            font-size: 0.72rem;
            font-weight: 700;
            font-variation-settings: normal;
            color: var(--sh-em);
            white-space: nowrap;
        }
        .sh-exit svg { width: 0.8rem; height: 0.8rem; }
        .sh-exit.is-lit { border-color: rgba(52, 211, 153, 0.4); background: rgba(16, 185, 129, 0.14); color: #6ee7b7; }
        .sh-own-tag {
            display: inline-flex;
            align-items: center;
            min-height: 1.6rem;
            padding: 0 0.65rem;
            border: 1px solid var(--sh-am-edge);
            border-radius: 999px;
            background: var(--sh-am-tint);
            font-family: var(--hp-mono);
            font-size: 0.7rem;
            font-weight: 700;
            font-variation-settings: normal;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--sh-am);
            white-space: nowrap;
        }
        .sh-own-tag.is-lit { border-color: rgba(251, 191, 36, 0.4); background: rgba(245, 158, 11, 0.12); color: var(--sh-am-lit); }
        .sh-only-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            align-self: flex-start;
            min-height: 1.75rem;
            padding: 0 0.75rem;
            border: 1px solid var(--sh-em-edge);
            border-radius: 999px;
            background: var(--sh-em-tint);
            font-family: var(--hp-mono);
            font-size: 0.7rem;
            font-weight: 700;
            font-variation-settings: normal;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--sh-em);
        }
        .sh-dom { unicode-bidi: isolate; }
        .sh-nw { white-space: nowrap; }

        /* The main button here is emerald, where the other pages' is blue. */
        #hp .hp-btn-own {
            color: #fff;
            background: linear-gradient(100deg, #047857, #0f766e);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.24), 0 14px 34px -12px rgba(5, 150, 105, 0.7), 0 0 0 1px rgba(4, 120, 87, 0.5);
        }
        #hp .hp-btn-own:hover {
            transform: translateY(-2px);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.24), 0 22px 44px -14px rgba(5, 150, 105, 0.85), 0 0 0 1px rgba(4, 120, 87, 0.6), 0 0 44px -6px rgba(45, 212, 191, 0.5);
        }
        #hp .hp-btn-ghost:hover { border-color: var(--sh-em); }
        #hp .hp-finale .hp-btn-onnight,
        #hp .sh-pre .hp-btn-onnight { color: #eef2ff; border: 1px solid rgba(255, 255, 255, 0.32); background: transparent; }
        #hp .hp-finale .hp-btn-onnight:hover,
        #hp .sh-pre .hp-btn-onnight:hover { background: rgba(255, 255, 255, 0.1); }
        #hp .sh-more { color: var(--sh-em); }
        #hp a:focus-visible,
        #hp button:focus-visible { outline-color: var(--sh-em); }
        #hp .hp-dark a:focus-visible,
        #hp .hp-dark button:focus-visible,
        #hp .sh-term button:focus-visible,
        #hp .sh-term a:focus-visible,
        #hp .sh-src a:focus-visible,
        #hp .sh-env button:focus-visible,
        #hp .sh-pre button:focus-visible,
        #hp .sh-pre a:focus-visible,
        #hp .hp-finale a:focus-visible { outline-color: var(--sh-em-lit); }

        /* ---- Hero ------------------------------------------------------ */
        #hp .hp-hero-sky {
            background:
                radial-gradient(62rem 34rem at 50% -9rem, rgba(16, 185, 129, 0.22), transparent 70%),
                radial-gradient(36rem 26rem at 8% 18rem, rgba(20, 184, 166, 0.14), transparent 70%),
                radial-gradient(36rem 26rem at 92% 22rem, rgba(5, 150, 105, 0.12), transparent 70%);
        }
        .dark #hp .hp-hero-sky {
            background:
                radial-gradient(62rem 34rem at 50% -9rem, rgba(16, 185, 129, 0.32), transparent 70%),
                radial-gradient(36rem 26rem at 8% 18rem, rgba(20, 184, 166, 0.14), transparent 70%),
                radial-gradient(36rem 26rem at 92% 22rem, rgba(5, 150, 105, 0.16), transparent 70%);
        }
        #hp .sh-hero { padding-bottom: clamp(2.5rem, 5vh, 3.75rem); }
        .sh-domain { max-width: 34rem; margin: clamp(1.5rem, 3.2vh, 2.1rem) auto 0; }
        .sh-domain-label {
            display: block;
            margin-bottom: 0.55rem;
            font-family: var(--hp-mono);
            font-size: 0.74rem;
            font-weight: 700;
            font-variation-settings: normal;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--hp-ink-3);
        }
        .sh-addr {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.95rem 1.15rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 1rem;
            background: var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
            font-family: var(--hp-mono);
            font-size: 1rem;
            font-variation-settings: normal;
            text-align: left;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .sh-addr:focus-within { border-color: var(--sh-em); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.25); }
        .sh-addr svg { flex: none; width: 1.05rem; height: 1.05rem; color: var(--hp-ink-3); transition: color 0.2s ease; }
        #hp.sh-has-domain .sh-addr svg { color: var(--sh-em); }
        .sh-addr-scheme { flex: none; color: var(--hp-ink-3); user-select: none; }
        #hp .sh-addr input {
            flex: 1 1 0;
            width: 0;
            min-width: 0;
            margin-inline-start: -0.6rem;
            border: 0;
            background: transparent;
            padding: 0;
            box-shadow: none;
            outline: 0;
            font-family: inherit;
            font-size: inherit;
            font-weight: 700;
            color: var(--hp-ink);
        }
        #hp .sh-addr input::placeholder { color: var(--hp-ink-3); font-weight: 400; opacity: 1; }
        #hp .sh-addr input:focus { border: 0; box-shadow: none; outline: 0; }
        .sh-domain-hint { min-height: 1.5em; margin-top: 0.65rem; font-size: 0.95rem; color: var(--hp-ink-3); }
        .sh-domain-hint b { color: var(--sh-em); overflow-wrap: anywhere; }

        /* ---- The install, played -------------------------------------- */
        .sh-stage-sec { position: relative; padding-block: 0 clamp(4rem, 8vw, 7rem); }
        .sh-stage-wrap { width: min(100% - 2.5rem, 88rem); margin-inline: auto; }
        .sh-stage { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; }
        .sh-stage-head .hp-h2 { margin-top: 1rem; }
        .sh-stage-head .hp-lead { margin-top: 1rem; }
        @media (min-width: 1024px) {
            /* The heading spans the first two rows so that the window can start straight under
               the three ways in, whatever the heading's height. */
            .sh-stage {
                grid-template-columns: minmax(0, 21rem) minmax(0, 1fr);
                grid-template-rows: auto auto auto 1fr;
                column-gap: clamp(2rem, 3.4vw, 3.75rem);
                row-gap: 0;
                align-items: start;
            }
            .sh-stage-head { grid-column: 1; grid-row: 1 / span 2; padding-block: 0.5rem 1.25rem; }
            .sh-methods { grid-column: 2; grid-row: 1; }
            .sh-stage-main { grid-column: 2; grid-row: 2 / span 3; }
            .sh-beats-wrap { grid-column: 1; grid-row: 3; }
            .sh-stage-links { grid-column: 1; grid-row: 4; align-self: end; padding-block: 0.9rem 0.2rem; }
        }
        .sh-step-text,
        .sh-beat-short { display: none; }
        @media (max-width: 1023px) {
            /* On a phone the window is taller than the screen, so the steps ride above it as a
               strip (the one in play says its name) and its sentence stands under the window. */
            .sh-stage.is-ready { gap: 0.75rem; }
            .sh-stage.is-ready .sh-stage-head { margin-bottom: 0.5rem; }
            .sh-stage.is-ready .sh-methods { margin-bottom: 0; }
            .sh-stage.is-ready .sh-step-text { display: block; min-height: 4.5em; margin-top: 0.75rem; padding-inline: 0.25rem; font-size: 0.95rem; line-height: 1.5; color: var(--hp-ink-2); }
            .sh-stage.is-ready .sh-stage-note { margin-top: 0.75rem; padding-inline: 0.25rem; text-align: start; }
            .sh-stage.is-ready .sh-beats { display: flex; gap: 0.4rem; }
            .sh-stage.is-ready .sh-beats > li { display: flex; flex: 0 0 auto; min-width: 0; }
            .sh-stage.is-ready .sh-beats > li:has([aria-current="step"]) { flex: 1 1 0; }
            #hp .sh-stage.is-ready .sh-beat { grid-template-columns: auto minmax(0, 1fr); align-items: center; gap: 0.45rem; padding: 0.3rem; border-color: var(--hp-line-2); border-radius: 0.85rem; background: var(--hp-bg-2); }
            .sh-stage.is-ready .sh-beat-n { width: 1.9rem; height: 1.9rem; border-radius: 0.55rem; }
            #hp .sh-stage.is-ready .sh-beat[aria-current="step"] { padding-inline-end: 0.6rem; }
            .sh-stage.is-ready .sh-beat:not([aria-current="step"]) > span:nth-child(2),
            .sh-stage.is-ready .sh-beat .sh-beat-text,
            .sh-stage.is-ready .sh-beat .sh-own-tag { display: none; }
            .sh-stage.is-ready .sh-beat-name { min-height: 0; flex-wrap: nowrap; }
            .sh-stage.is-ready .sh-beat-name strong { font-size: 0.95rem; white-space: nowrap; }
            .sh-stage.is-ready .sh-beat-long { display: none; }
            .sh-stage.is-ready .sh-beat-short { display: inline; }
            .sh-stage.is-ready .sh-beat-bar { inset-inline: 0.5rem; bottom: 0.15rem; }
        }
        @media (max-width: 559px) {
            /* The longest of the four sentences is five lines here; the page under the stage
               is not to move each time the step changes. */
            .sh-stage.is-ready .sh-step-text { min-height: 7.5em; }
        }
        @media (max-width: 359px) {
            .sh-method strong { font-size: 0.9rem; }
        }
        @media (min-width: 1280px) {
            .sh-stage { grid-template-columns: minmax(0, 25rem) minmax(0, 1fr); }
        }

        .sh-beats { display: grid; gap: 0.35rem; }
        #hp .sh-beat {
            position: relative;
            display: grid;
            grid-template-columns: 2.25rem minmax(0, 1fr);
            gap: 0.85rem;
            width: 100%;
            padding: 0.75rem 0.85rem;
            border: 1px solid transparent;
            border-radius: 1rem;
            text-align: start;
            color: inherit;
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .sh-stage.is-ready .sh-beat { cursor: pointer; }
        .sh-stage.is-ready .sh-beat:hover { background: var(--hp-bg-2); }
        .dark .sh-stage.is-ready .sh-beat:hover { background: var(--hp-bg-2); }
        #hp .sh-beat[aria-current="step"] { border-color: var(--hp-line-2); background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
        .sh-beat-n {
            display: grid;
            place-items: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.7rem;
            background: var(--sh-em-tint);
            font-family: var(--hp-mono);
            font-size: 0.85rem;
            font-weight: 700;
            font-variation-settings: normal;
            color: var(--sh-em);
        }
        .sh-beat-n svg { width: 1rem; height: 1rem; }
        .sh-beat.is-own .sh-beat-n { background: var(--sh-am-tint); box-shadow: inset 0 0 0 1px var(--sh-am-edge); color: var(--sh-am); }
        .sh-beat-name { display: flex; flex-wrap: wrap; align-items: center; gap: 0.3rem 0.6rem; min-height: 2.25rem; }
        .sh-beat-name strong { font-size: 1.06rem; letter-spacing: -0.02em; }
        .sh-beat-text { display: block; padding-bottom: 0.2rem; font-size: 0.93rem; line-height: 1.5; color: var(--hp-ink-2); }
        .sh-stage.is-ready .sh-beat:not([aria-current="step"]) .sh-beat-text { display: none; }
        /* The line under the step that is playing. */
        .sh-beat-bar { position: absolute; inset-inline: 0.85rem; bottom: 0.3rem; height: 2px; border-radius: 2px; overflow: hidden; }
        .sh-beat-bar::after { content: ""; position: absolute; inset: 0; border-radius: 2px; background: var(--sh-em); transform: scaleX(0); transform-origin: 0 50%; }
        [dir="rtl"] .sh-beat-bar::after { transform-origin: 100% 50%; }
        .sh-beat.is-own .sh-beat-bar::after { background: var(--sh-am); }
        .sh-stage.is-playing .sh-beat[aria-current="step"] .sh-beat-bar::after { animation: sh-fill var(--sh-dur, 3s) linear both; }
        @keyframes sh-fill { to { transform: scaleX(1); } }
        .sh-stage-links { display: flex; flex-wrap: wrap; align-items: center; gap: 0.35rem 1.5rem; padding-inline-start: 0.85rem; }
        #hp .sh-replay {
            display: none;
            align-items: center;
            gap: 0.45rem;
            min-height: 2.5rem;
            font-size: 0.95rem;
            font-weight: 700;
            font-variation-settings: 'wght' 700;
            color: var(--sh-em);
        }
        #hp .sh-stage.is-ready.can-play .sh-replay { display: inline-flex; }
        .sh-replay svg { width: 1rem; height: 1rem; }

        .sh-methods { display: none; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem; margin-bottom: 0.75rem; }
        .sh-stage.is-ready .sh-methods { display: grid; }
        #hp .sh-method {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
            min-height: 3.5rem;
            padding: 0.65rem 0.95rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 0.95rem;
            background: var(--hp-bg-2);
            text-align: start;
            color: var(--hp-ink-2);
            transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }
        #hp .sh-method:hover { transform: translateY(-1px); border-color: var(--sh-em); }
        .sh-method strong { font-size: 1.02rem; letter-spacing: -0.02em; color: var(--hp-ink); }
        .sh-method span { font-size: 0.84rem; line-height: 1.35; color: var(--hp-ink-3); }
        #hp .sh-method[aria-selected="true"] {
            border-color: var(--sh-em);
            background: linear-gradient(var(--sh-em-tint), var(--sh-em-tint)), var(--hp-bg-2);
            box-shadow: inset 0 2px 4px rgba(4, 120, 87, 0.12);
        }
        .sh-method[aria-selected="true"] strong { color: var(--sh-em); }
        @media (max-width: 559px) {
            #hp .sh-method { align-items: center; justify-content: center; padding-inline: 0.4rem; text-align: center; }
            .sh-method span { display: none; }
        }

        /* A desk with two windows on it: a terminal and a browser. Every scene is a window of
           its own. With the script running each kind shows one scene, the scene of the step in
           play comes to the front and the other waits behind it; without the script the scenes
           stand one under the other, in order. On a phone there is room for one window. */
        .sh-desk { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; }
        .sh-scene {
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            border: 1px solid var(--sh-term-edge);
            border-radius: 1.25rem;
            overflow: hidden;
            background: var(--sh-term);
            box-shadow: 0 2px 4px rgba(10, 16, 32, 0.08), 0 44px 90px -44px rgba(4, 120, 87, 0.6), 0 30px 70px -40px rgba(10, 16, 32, 0.5);
        }
        .sh-scene.sh-browser { border-color: var(--hp-line-2); background: var(--sh-screen); }
        .sh-stage.is-ready .sh-desk { gap: 0; }
        .sh-stage.is-ready .sh-scene { grid-area: 1 / 1; transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.25s ease, box-shadow 0.4s ease, visibility 0s linear 0s; }
        .sh-stage.is-ready .sh-scene:not(.is-shown),
        .sh-stage.is-ready .sh-scene.is-back { visibility: hidden; opacity: 0; transition: opacity 0.16s ease, visibility 0s linear 0.16s; }
        @media (min-width: 1024px) {
            .sh-stage.is-ready .sh-desk { padding-bottom: 1.75rem; }
            .sh-stage.is-ready .sh-scene.sh-browser { width: 90%; justify-self: end; align-self: stretch; transform-origin: 100% 0; }
            .sh-stage.is-ready .sh-scene.sh-term { width: 82%; justify-self: start; align-self: end; transform: translateY(1.75rem); transform-origin: 0 100%; }
            /* The running admin takes the height the wizard gave the desk, so the last frame
               covers what the one before it covered. */
            .sh-stage.is-ready .sh-scene[data-sh-scene="4"] .sh-browser-body { position: relative; min-height: 0; }
            .sh-stage.is-ready .sh-scene[data-sh-scene="4"] .sh-shot { position: absolute; inset: 0; aspect-ratio: auto; }
            .sh-stage.is-ready .sh-scene.is-front { z-index: 2; }
            .dark .sh-stage.is-ready .sh-scene.is-front { border-color: rgba(255, 255, 255, 0.16); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.5), 0 30px 60px -30px rgba(0, 0, 0, 0.95); }
            .sh-stage.is-ready .sh-scene.is-shown.is-back {
                z-index: 1;
                visibility: visible;
                opacity: 1;
                box-shadow: 0 1px 2px rgba(10, 16, 32, 0.08), 0 20px 40px -30px rgba(10, 16, 32, 0.5);
                transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.25s ease, box-shadow 0.4s ease, visibility 0s linear 0s;
            }
            .sh-stage.is-ready .sh-scene.sh-browser.is-back { transform: scale(0.97); }
            .sh-stage.is-ready .sh-scene.sh-term.is-back { transform: translateY(1.75rem) scale(0.97); }
            /* The window behind stands in the shade and says only what it is: a terminal keeps
               its title bar and nothing else, so no half line or half button shows past the
               window in front of it. */
            .sh-stage.is-ready .sh-scene.is-back::after { content: ""; position: absolute; inset: 0; z-index: 3; background: rgba(244, 246, 251, 0.55); }
            .dark .sh-stage.is-ready .sh-scene.is-back::after { background: rgba(7, 10, 20, 0.66); }
            .sh-stage.is-ready .sh-scene.sh-term.is-back::after,
            .dark .sh-stage.is-ready .sh-scene.sh-term.is-back::after { background: rgba(6, 16, 12, 0.35); }
            .sh-stage.is-ready .sh-scene.sh-term.is-back :is(.sh-term-body, .sh-term-foot, .sh-copy) { visibility: hidden; }
            /* Until the install has been through it, the browser has nothing to show: a blank
               page under the address that will be opened. */
            .sh-stage.is-ready .sh-scene.is-pending.is-back .sh-wiz { visibility: hidden; }
            .sh-stage.is-ready .sh-scene.is-pending.is-back .sh-wait { display: block; }
            /* The admin is not on screen until it arrives: behind the cron terminal the browser
               is its bar and a blank page. */
            .sh-stage.is-ready .sh-scene[data-sh-scene="4"].is-back .sh-shot { visibility: hidden; }
            .sh-stage.is-ready .sh-scene[data-sh-scene="4"].is-back .sh-wait { display: block; }
            .sh-stage.is-ready .sh-desk { cursor: pointer; }
            .sh-stage.is-ready .sh-scene.is-front { cursor: auto; }
        }
        @media (max-width: 1023px) {
            /* One window, and a film has one frame size: every scene is cut to it. The wizard
               is taller than the frame, so it moves up once when its second half wakes. Only
               where the film plays: with motion off each scene keeps its own height. */
            html.es-anim .sh-stage.is-ready .sh-desk { height: 28.5rem; }
            html.es-anim .sh-stage.is-ready .sh-scene { height: 100%; }
            html.es-anim .sh-stage.is-ready .sh-browser-body { align-items: start; overflow: hidden; }
            html.es-anim .sh-stage.is-ready .sh-wiz { align-self: start; }
            html.es-anim .sh-stage.is-ready .sh-scene.is-played .sh-wiz { animation: sh-pan 0.7s cubic-bezier(0.22, 1, 0.36, 1) 2.75s both; }
            html.es-anim .sh-stage.is-ready .sh-scene[data-sh-scene="4"] .sh-shot { height: 100%; aspect-ratio: auto; }
            .sh-stage.is-ready .sh-term-body { padding: 0.85rem 1rem 1rem; font-size: 0.76rem; line-height: 1.65; }
            .sh-stage.is-ready .sh-term-foot { gap: 0.6rem 1rem; padding: 0.8rem 1rem 0.9rem; }
            .sh-stage.is-ready .sh-term-foot p { flex-basis: 100%; font-size: 0.86rem; }
        }
        @keyframes sh-pan { to { transform: translateY(min(0px, calc(24.25rem - 100%))); } }
        .sh-scene-title { padding: 1rem 1.15rem 0; font-family: var(--hp-display); font-size: 1.05rem; color: #eef2ff; }
        .sh-browser .sh-scene-title { padding-bottom: 0.9rem; color: var(--hp-ink); }
        .sh-stage.is-ready .sh-scene-title { position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }

        .sh-bar {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            min-height: 2.75rem;
            padding: 0.4rem 1rem;
            border-bottom: 1px solid var(--sh-term-edge);
            background: rgba(16, 185, 129, 0.07);
            font-family: var(--hp-mono);
            font-size: 0.78rem;
            font-variation-settings: normal;
            color: var(--sh-term-dim);
        }
        .sh-bar > i { flex: none; width: 0.65rem; height: 0.65rem; border-radius: 999px; background: rgba(209, 250, 229, 0.2); }
        .sh-bar-title { min-width: 0; margin-inline-start: 0.5rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        #hp .sh-copy {
            margin-inline-start: auto;
            min-height: 1.9rem;
            padding: 0 0.7rem;
            border: 1px solid rgba(52, 211, 153, 0.3);
            border-radius: 0.5rem;
            font-family: var(--hp-mono);
            font-size: 0.74rem;
            font-weight: 700;
            font-variation-settings: normal;
            color: #6ee7b7;
            transition: background-color 0.2s ease;
        }
        #hp .sh-copy:hover { background: rgba(16, 185, 129, 0.16); }
        .sh-term-body {
            flex: 1 1 auto;
            padding: 1.15rem 1.2rem 1.3rem;
            font-family: var(--hp-mono);
            font-size: clamp(0.8rem, 0.35vw + 0.7rem, 0.98rem);
            font-variation-settings: normal;
            line-height: 1.8;
            color: var(--sh-term-ink);
        }
        .sh-line { display: flex; gap: 0.65rem; }
        #hp .sh-line > b { flex: none; font-weight: 400; font-variation-settings: normal; color: var(--sh-em-lit); user-select: none; }
        .sh-line > span { min-width: 0; overflow-wrap: anywhere; }
        .sh-line.is-note > span,
        .sh-line.is-out > span { color: var(--sh-term-dim); }
        .sh-line.is-out > span { color: #c2e3d4; }
        #hp .sh-line.is-own > b,
        .sh-line.is-own > span { color: var(--sh-am-lit); }
        .sh-line.is-end { margin-top: 0.7rem; }
        .sh-term-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.85rem 1.5rem;
            padding: 1rem 1.2rem 1.15rem;
            border-top: 1px solid var(--sh-term-edge);
            background: rgba(255, 255, 255, 0.025);
        }
        .sh-term-foot p { flex: 1 1 17rem; font-size: 0.95rem; line-height: 1.5; color: #c2e3d4; }
        #hp .sh-term-foot p strong { color: #ecfdf5; }
        #hp .sh-term-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-height: 2.75rem;
            padding: 0 1.1rem;
            border-radius: 0.8rem;
            background: #10b981;
            font-size: 0.95rem;
            font-weight: 700;
            font-variation-settings: 'wght' 700;
            color: #03120c;
            white-space: nowrap;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        #hp .sh-term-btn:hover { background: #34d399; transform: translateY(-1px); }
        .sh-term-btn svg { width: 1rem; height: 1rem; }

        /* The two scenes that are a browser: the wizard, and the install running. */
        .sh-browser { background: var(--sh-screen); color: var(--hp-ink); }
        .sh-browser-bar { display: flex; align-items: center; gap: 0.45rem; min-height: 2.75rem; padding: 0.4rem 1rem; border-bottom: 1px solid var(--sh-chrome-edge); background: var(--sh-chrome); }
        .sh-browser-bar > i { flex: none; width: 0.65rem; height: 0.65rem; border-radius: 999px; background: var(--hp-line-2); }
        .sh-url {
            display: flex;
            flex: 1 1 auto;
            align-items: center;
            gap: 0.4rem;
            min-width: 0;
            max-width: 30rem;
            min-height: 1.75rem;
            margin-inline: 0.5rem auto;
            padding: 0 0.75rem;
            border-radius: 0.55rem;
            background: var(--sh-field);
            font-family: var(--hp-mono);
            font-size: 0.76rem;
            font-variation-settings: normal;
            color: var(--hp-ink-3);
            white-space: nowrap;
            overflow: hidden;
        }
        .sh-url svg { flex: none; width: 0.8rem; height: 0.8rem; color: var(--sh-em); }
        .sh-url span { min-width: 0; overflow: hidden; text-overflow: ellipsis; }
        @media (max-width: 559px) {
            .sh-browser-bar > i,
            .sh-url .sh-path { display: none; }
            .sh-url { margin-inline-start: 0; }
        }
        .sh-url b { font-weight: 700; font-variation-settings: normal; color: var(--hp-ink); }
        .sh-browser-body { position: relative; flex: 1 1 auto; display: grid; }

        .sh-wait { display: none; position: absolute; inset: 2.6rem 1.5rem auto; font-family: var(--hp-mono); font-size: 0.95rem; font-variation-settings: normal; text-align: center; color: var(--hp-ink-2); overflow-wrap: anywhere; }
        #hp .sh-wait b { font-weight: 700; font-variation-settings: normal; color: var(--hp-ink); }
        .sh-wait-local,
        .sh-stage[data-method="docker"] .sh-wait-domain { display: none; }
        .sh-stage[data-method="docker"] .sh-wait-local { display: inline; }
        .sh-wiz { align-self: center; justify-self: center; width: min(100% - 2rem, 31rem); margin-block: 1.25rem; padding: 1.15rem 1.25rem 1.3rem; border: 1px solid var(--hp-line); border-radius: 1.1rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
        .sh-wiz-cap { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.6rem; font-family: var(--hp-mono); font-size: 0.7rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.1em; text-transform: uppercase; color: var(--hp-ink-3); }
        .sh-wiz-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.55rem 0.7rem; }
        .sh-f { min-width: 0; }
        .sh-f.is-wide { grid-column: 1 / -1; }
        .sh-f-label { display: block; font-size: 0.78rem; font-weight: 400; font-variation-settings: 'wght' 520; color: var(--hp-ink-2); }
        .sh-f-box { display: flex; align-items: center; min-height: 2.1rem; margin-top: 0.2rem; padding: 0 0.65rem; border: 1px solid var(--hp-line-2); border-radius: 0.55rem; background: var(--sh-screen); font-family: var(--hp-mono); font-size: 0.78rem; font-variation-settings: normal; color: var(--hp-ink); overflow: hidden; white-space: nowrap; }
        .sh-f-box > span { min-width: 0; overflow: hidden; text-overflow: ellipsis; }
        .sh-wiz-row { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-top: 0.7rem; }
        .sh-wiz-ok { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.86rem; font-weight: 700; font-variation-settings: 'wght' 650; color: var(--sh-em); }
        .sh-wiz-ok svg { width: 0.95rem; height: 0.95rem; }
        .sh-wiz-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 2.1rem; padding: 0 1rem; border-radius: 0.55rem; background: var(--hp-ink); font-size: 0.8rem; font-weight: 700; font-variation-settings: 'wght' 700; letter-spacing: 0.02em; color: var(--hp-bg-2); }
        .sh-wiz-btn.is-go { width: 100%; margin-top: 0.8rem; background: linear-gradient(100deg, #047857, #0f766e); color: #fff; }
        .sh-wiz-part + .sh-wiz-part { margin-top: 1rem; padding-top: 0.9rem; border-top: 1px solid var(--hp-line); }
        .sh-wiz-terms { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.7rem; font-size: 0.82rem; color: var(--hp-ink-2); }
        .sh-wiz-terms i { display: grid; place-items: center; flex: none; width: 1rem; height: 1rem; border-radius: 0.25rem; background: var(--sh-em); color: #fff; }
        .dark .sh-wiz-terms i { color: #03120c; }
        .sh-wiz-terms i svg { width: 0.7rem; height: 0.7rem; }

        @media (max-width: 559px) {
            /* The window is as tall as its tallest scene, which is this one: keep it short. */
            .sh-wiz { margin-block: 0.75rem; padding: 0.85rem 0.9rem 1rem; }
            .sh-wiz-grid { gap: 0.4rem 0.55rem; }
            .sh-f-box { min-height: 1.9rem; }
            .sh-wiz-row { margin-top: 0.55rem; }
            .sh-wiz-part + .sh-wiz-part { margin-top: 0.7rem; padding-top: 0.65rem; }
            .sh-wiz-terms { margin-top: 0.55rem; }
            .sh-wiz-btn.is-go { margin-top: 0.6rem; }
        }

        /* A guide screenshot is 1280 by 757 with a 288px sidebar and a 56px top bar. The frame
           shows the 992px beside the sidebar; on a phone the first 496px of that, which ends on
           the gutter between two of the app's own columns, at a size that can still be read. */
        .sh-shot { position: relative; overflow: hidden; aspect-ratio: 4 / 3; background: var(--sh-screen); direction: ltr; }
        .sh-shot { --px: 288; --py: 56; }
        .sh-shot img { display: block; max-width: none; width: 258.06%; height: auto; margin-left: calc(var(--px) * -100% / 496); margin-top: calc(var(--py) * -100% / 496); }
        @media (max-width: 639px) {
            .sh-shot.is-cut-right { -webkit-mask-image: linear-gradient(to right, #000 80%, transparent); mask-image: linear-gradient(to right, #000 80%, transparent); }
            .sh-shot.is-cut-left { -webkit-mask-image: linear-gradient(to left, #000 80%, transparent); mask-image: linear-gradient(to left, #000 80%, transparent); }
        }
        .sh-shot .is-night,
        .dark .sh-shot .is-day { display: none; }
        .dark .sh-shot .is-night { display: block; }
        @media (min-width: 640px) {
            .sh-shot { aspect-ratio: 62 / 35; }
            .sh-shot img { width: 129.03%; margin-left: -29.03%; margin-top: -5.65%; }
        }
        .sh-live {
            display: none;
            flex: none;
            align-items: center;
            gap: 0.35rem;
            min-height: 1.6rem;
            padding: 0 0.7rem;
            border-radius: 999px;
            background: #047857;
            font-family: var(--hp-mono);
            font-size: 0.72rem;
            font-weight: 700;
            font-variation-settings: normal;
            color: #fff;
            white-space: nowrap;
        }
        .dark .sh-live { background: #34d399; color: #03120c; }
        .sh-live svg { flex: none; width: 0.8rem; height: 0.8rem; }
        .sh-scene.is-front .sh-live,
        .sh-stage:not(.is-ready) .sh-live { display: inline-flex; }
        .sh-stage-note { margin-top: 0.9rem; font-size: 0.9rem; color: var(--hp-ink-3); text-align: center; }
        .sh-stage.is-ready:not([data-beat="4"]) .sh-stage-note { visibility: hidden; }

        /* The play: what arrives, and in what order, when a scene comes on. The order is set
           in the markup as --i; one step is 0.34s. Nothing here hides anything without
           .is-played, which only the script adds, and only where motion is welcome. */
        html.es-anim .sh-scene.is-played .sh-line,
        html.es-anim .sh-scene.is-played .sh-f-box > span,
        html.es-anim .sh-scene.is-played .sh-wiz-ok,
        html.es-anim .sh-scene.is-played .sh-wiz-part.is-second,
        html.es-anim .sh-scene.is-played .sh-live {
            animation: sh-arrive 0.3s ease-out both;
            animation-delay: calc(var(--i, 0) * 0.34s + 0.15s);
        }
        html.es-anim .sh-scene.is-played .sh-line.is-cmd > span { animation: sh-typed 0.42s steps(22, end) both; animation-delay: calc(var(--i, 0) * 0.34s + 0.15s); }
        html.es-anim .sh-scene.is-played .sh-wiz-part.is-second { animation-name: sh-wake; }
        html.es-anim .sh-scene.is-played .sh-wiz-btn { animation: sh-press 0.4s ease-out both; animation-delay: calc(var(--i, 0) * 0.34s + 0.15s); }
        html.es-anim .sh-scene.is-played .sh-shot img { animation: sh-load 0.7s cubic-bezier(0.22, 1, 0.36, 1) both; }
        html.es-anim .sh-stage.is-ready .sh-scene.is-pending .sh-f-box > span,
        html.es-anim .sh-stage.is-ready .sh-scene.is-pending .sh-wiz-ok { opacity: 0; }
        html.es-anim .sh-stage.is-ready .sh-scene.is-pending .sh-wiz-part.is-second { opacity: 0.25; }
        @keyframes sh-arrive { from { opacity: 0; transform: translateY(0.35rem); } }
        @keyframes sh-typed { from { clip-path: inset(0 100% 0 0); } to { clip-path: inset(0 0 0 0); } }
        @keyframes sh-wake { from { opacity: 0.25; } }
        @keyframes sh-press { 0% { transform: none; } 40% { transform: scale(0.94); filter: brightness(1.25); } 100% { transform: none; } }
        @keyframes sh-load { from { opacity: 0; transform: scale(1.03); } }

        /* What the server needs: a spec sheet under the stage. */
        .sh-req { margin-top: clamp(2rem, 4vw, 3.25rem); border: 1px solid var(--hp-line); border-radius: 1.5rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); overflow: hidden; }
        .sh-req-top .sh-prompt { white-space: nowrap; }
        @media (max-width: 479px) { .sh-req-top .sh-prompt { font-size: 0.7rem; letter-spacing: 0.04em; } }
        .sh-req-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.6rem 1rem; padding: 0.9rem 1.35rem; border-bottom: 1px solid var(--hp-line); }
        .sh-req-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1px; background: var(--hp-line); }
        @media (min-width: 560px) { .sh-req-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .sh-req-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .sh-req-cell { position: relative; min-height: 4.55rem; padding: 1.15rem 1.35rem; padding-inline-start: 4.45rem; background: var(--hp-bg-2); }
        .sh-req-ico { position: absolute; inset-inline-start: 1.35rem; top: 1.15rem; display: grid; place-items: center; width: 2.25rem; height: 2.25rem; border-radius: 0.7rem; background: var(--sh-em-tint); color: var(--sh-em); }
        .sh-req-ico svg { width: 1.15rem; height: 1.15rem; }
        .sh-req-cell.is-own .sh-req-ico { background: var(--sh-am-tint); box-shadow: inset 0 0 0 1px var(--sh-am-edge); color: var(--sh-am); }
        .sh-req-cell dt { font-family: var(--hp-mono); font-size: 0.7rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.1em; text-transform: uppercase; color: var(--hp-ink-3); }
        .sh-req-cell dd { margin-top: 0.2rem; font-weight: 700; font-variation-settings: 'wght' 680; }
        .sh-req-ext { display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem; padding: 1rem 1.35rem; border-top: 1px solid var(--hp-line); }
        .sh-req-ext > span { margin-inline-end: 0.35rem; font-size: 0.9rem; color: var(--hp-ink-3); }
        .sh-req-ext li { display: inline-flex; align-items: center; gap: 0.4rem; min-height: 1.6rem; padding: 0 0.6rem; border: 1px solid var(--hp-line-2); border-radius: 999px; font-family: var(--hp-mono); font-size: 0.72rem; font-variation-settings: normal; color: var(--hp-ink-2); }
        .sh-req-ext li::before { content: ""; width: 0.3rem; height: 0.3rem; border-radius: 999px; background: var(--sh-em); }
        .sh-req-ext ul { display: contents; }

        /* ---- The night: each plan lit in turn ----------------------------- */
        #hp .sh-night::before {
            background:
                linear-gradient(to bottom, var(--hp-bg) 0, var(--hp-bg) 2px, #c3e6dc 1.6rem, #4f9a8c 3.4rem, #12403c 5.4rem, rgba(5, 8, 20, 0) 8rem) top / 100% 8rem no-repeat,
                linear-gradient(to top, var(--hp-bg) 0, var(--hp-bg) 2px, #c3e6dc 1.6rem, #4f9a8c 3.2rem, #12403c 5.2rem, rgba(5, 8, 20, 0) 8rem) bottom / 100% 8rem no-repeat,
                radial-gradient(58rem 30rem at 50% 14rem, rgba(16, 185, 129, 0.24), transparent 70%),
                radial-gradient(36rem 24rem at 10% 82%, rgba(45, 212, 191, 0.12), transparent 70%);
        }
        .dark #hp .sh-night { background: linear-gradient(#081c2c, #081c2c) 0 2px / 100% calc(100% - 4px) no-repeat; }
        .dark #hp .sh-night::before {
            background:
                linear-gradient(to bottom, var(--hp-bg) 0, var(--hp-bg) 2px, rgba(8, 28, 44, 0) 7rem) top / 100% 7rem no-repeat,
                linear-gradient(to top, var(--hp-bg) 0, var(--hp-bg) 2px, rgba(8, 28, 44, 0) 7rem) bottom / 100% 7rem no-repeat,
                radial-gradient(60rem 32rem at 50% 14rem, rgba(16, 185, 129, 0.3), transparent 72%),
                radial-gradient(40rem 26rem at 12% 80%, rgba(45, 212, 191, 0.16), transparent 70%);
        }
        .sh-unlock { text-align: center; }
        .sh-unlock .hp-head { margin-inline: auto; }
        #hp .sh-unlock .hp-h2 { color: #f1f5ff; }
        .sh-dialrow { margin-top: clamp(1.75rem, 3vw, 2.5rem); }
        .sh-dial {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.3rem;
            max-width: 60rem;
            margin-inline: auto;
            padding: 0.35rem;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 1.4rem;
            background: #0b1226;
            box-shadow: 0 18px 40px -20px rgba(0, 0, 0, 0.9);
        }
        .dark .sh-dial { background: #0a1a2c; }
        #hp .sh-dial button {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.1rem;
            min-width: 0;
            min-height: 4rem;
            padding: 0.4rem 0.35rem;
            border-radius: 1.05rem;
            font-size: clamp(1rem, 0.8vw + 0.85rem, 1.35rem);
            font-weight: 700;
            font-variation-settings: 'wght' 760;
            letter-spacing: -0.02em;
            line-height: 1.15;
            color: #c5cde2;
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }
        .sh-dial button small { font-family: var(--hp-mono); font-size: 0.7rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.1em; text-transform: uppercase; color: #9fb1d6; }
        #hp .sh-dial button:hover { color: #fff; background: rgba(255, 255, 255, 0.07); }
        #hp .sh-dial button[aria-pressed="true"] { background: #eef2ff; color: #0a1020; box-shadow: inset 0 -3px 0 rgba(10, 16, 32, 0.14); }
        .sh-dial button[aria-pressed="true"] small { color: #3a4560; }
        #hp .sh-dial button[data-sh-plan="self"][aria-pressed="true"] { background: linear-gradient(100deg, #34d399, #2dd4bf); color: #03120c; box-shadow: 0 0 0 1px rgba(110, 231, 183, 0.5), 0 0 40px -4px rgba(52, 211, 153, 0.75); }
        .sh-dial button[data-sh-plan="self"][aria-pressed="true"] small { color: #064e3b; }
        @media (max-width: 767px) {
            /* On a phone the wall is longer than the window, so the dial rides along with it,
               as a bar of the night's own ground that the wall passes under. */
            .sh-dialrow { position: sticky; top: 4rem; z-index: 5; margin-inline: -1.25rem; padding: 0.55rem 0.75rem; border-bottom: 1px solid rgba(255, 255, 255, 0.12); background: #050814; box-shadow: 0 14px 22px -14px #050814; }
            .dark .sh-dialrow { background: #081c2c; box-shadow: 0 14px 22px -14px #081c2c; }
            .sh-dial { gap: 0.2rem; padding: 0.25rem; border-radius: 1rem; box-shadow: none; }
            #hp .sh-dial button { min-height: 2.9rem; padding-inline: 0.15rem; border-radius: 0.8rem; font-size: clamp(0.78rem, 3.6vw, 0.9rem); letter-spacing: -0.03em; }
            .sh-dial button small { display: none; }
        }

        .sh-meter {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1px;
            max-width: 60rem;
            margin: 1.25rem auto 0;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 1.4rem;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.12);
            text-align: start;
        }
        @media (min-width: 768px) { .sh-meter { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .sh-meter > div { padding: 1.05rem 1.2rem 1.15rem; background: #0a1022; }
        .dark .sh-meter > div { background: #0b1f30; }
        .sh-meter dt { font-family: var(--hp-mono); font-size: 0.7rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.1em; text-transform: uppercase; color: #9fb1d6; }
        .sh-meter dd { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.1rem 0.45rem; min-height: 4.4rem; margin-top: 0.3rem; font-size: clamp(1.9rem, 2.4vw + 1rem, 3.5rem); font-weight: 700; font-variation-settings: 'wght' 840; letter-spacing: -0.04em; line-height: 1.1; color: #eef2ff; }
        .sh-meter dd small { font-size: 0.86rem; font-weight: 400; font-variation-settings: 'wght' 500; letter-spacing: 0; color: #9fb1d6; }
        @media (max-width: 767px) {
            .sh-meter dt { min-height: 2.9em; }
            .sh-meter dd { min-height: 3.4rem; }
        }
        .sh-meter dd small.sh-was { flex-basis: 100%; font-size: 0.82rem; }
        .sh-v { display: none; }
        .sh-unlock[data-plan="free"] .sh-v[data-for="free"],
        .sh-unlock[data-plan="pro"] .sh-v[data-for="pro"],
        .sh-unlock[data-plan="ent"] .sh-v[data-for="ent"],
        .sh-unlock[data-plan="self"] .sh-v[data-for="self"] { display: inline-flex; flex-wrap: wrap; align-items: baseline; gap: 0.1rem 0.45rem; }
        .sh-v[data-for="self"] { color: #6ee7b7; }
        html.es-anim .sh-unlock.is-live .sh-v { animation: sh-roll 0.32s cubic-bezier(0.22, 1, 0.36, 1) both; }
        @keyframes sh-roll { from { opacity: 0; transform: translateY(0.45em); } }

        .sh-wall { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem; max-width: 74rem; margin: clamp(1.75rem, 3vw, 2.5rem) auto 0; }
        #hp .sh-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-height: 2.25rem;
            padding: 0 0.85rem;
            border: 1px solid rgba(52, 211, 153, 0.42);
            border-radius: 999px;
            background: rgba(16, 185, 129, 0.13);
            font-size: 0.92rem;
            font-weight: 400;
            font-variation-settings: 'wght' 560;
            line-height: 1.2;
            color: #dcfcec;
            transition: border-color 0.35s ease, background-color 0.35s ease, color 0.35s ease, box-shadow 0.35s ease;
            transition-delay: calc(var(--i, 0) * 7ms);
        }
        .sh-chip::before { content: ""; flex: none; width: 0.4rem; height: 0.4rem; border-radius: 999px; background: #34d399; box-shadow: 0 0 10px rgba(52, 211, 153, 0.9); transition: background-color 0.35s ease, box-shadow 0.35s ease; transition-delay: inherit; }
        #hp .sh-chip.is-big { min-height: 2.6rem; padding-inline: 1.05rem; font-size: 1.08rem; font-variation-settings: 'wght' 720; }
        @media (min-width: 1280px) {
            .sh-wall { gap: 0.6rem; }
            #hp .sh-chip { min-height: 2.5rem; padding-inline: 1rem; font-size: 1rem; }
            #hp .sh-chip.is-big { min-height: 2.9rem; padding-inline: 1.2rem; font-size: 1.2rem; }
        }
        @media (max-width: 559px) {
            .sh-wall { gap: 0.35rem; }
            #hp .sh-chip { min-height: 2rem; padding-inline: 0.65rem; gap: 0.4rem; font-size: 0.82rem; }
            #hp .sh-chip.is-big { min-height: 2.25rem; padding-inline: 0.8rem; font-size: 0.95rem; }
        }
        #hp a.sh-chip:hover { border-color: #6ee7b7; background: rgba(16, 185, 129, 0.24); color: #fff; }
        #hp .sh-chip[data-tier="s"] { border-color: #6ee7b7; background: linear-gradient(100deg, rgba(52, 211, 153, 0.3), rgba(45, 212, 191, 0.2)); box-shadow: 0 0 30px -6px rgba(52, 211, 153, 0.75); color: #fff; }
        .sh-chip em { padding: 0.1rem 0.45rem; border-radius: 999px; background: rgba(3, 18, 12, 0.55); font-family: var(--hp-mono); font-size: 0.7rem; font-style: normal; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.08em; text-transform: uppercase; color: #6ee7b7; }
        /* Off: what the plan picked on the dial does not include. */
        #hp .sh-unlock[data-plan="free"] .sh-chip:not([data-tier="f"]),
        #hp .sh-unlock[data-plan="pro"] .sh-chip:is([data-tier="e"], [data-tier="s"]),
        #hp .sh-unlock[data-plan="ent"] .sh-chip[data-tier="s"] { border-color: rgba(255, 255, 255, 0.12); background: transparent; box-shadow: none; color: #8f9bbb; }
        .sh-unlock[data-plan="free"] .sh-chip:not([data-tier="f"])::before,
        .sh-unlock[data-plan="pro"] .sh-chip:is([data-tier="e"], [data-tier="s"])::before,
        .sh-unlock[data-plan="ent"] .sh-chip[data-tier="s"]::before { background: transparent; box-shadow: inset 0 0 0 1px #66728f; }
        .sh-unlock[data-plan="free"] .sh-chip:not([data-tier="f"]) em,
        .sh-unlock[data-plan="pro"] .sh-chip[data-tier="s"] em,
        .sh-unlock[data-plan="ent"] .sh-chip[data-tier="s"] em { background: transparent; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.14); color: #8f9bbb; }
        #hp .sh-unlock[data-plan] a.sh-chip:hover { border-color: #6ee7b7; color: #fff; }
        .sh-chip-off { display: none; }
        .sh-unlock[data-plan="free"] .sh-chip:not([data-tier="f"]) .sh-chip-off,
        .sh-unlock[data-plan="pro"] .sh-chip:is([data-tier="e"], [data-tier="s"]) .sh-chip-off,
        .sh-unlock[data-plan="ent"] .sh-chip[data-tier="s"] .sh-chip-off { display: block; }
        /* The two chips that change their words: a hosted schedule's own domain, against an
           install that already stands on yours; and white label, which keeps one licence
           credit off the hosted service. */
        .sh-unlock:not([data-plan="self"]) .sh-chip .is-self,
        .sh-unlock[data-plan="self"] .sh-chip .is-hosted { display: none; }
        .sh-wall-foot { max-width: 46rem; margin: 1.5rem auto 0; font-size: 0.95rem; line-height: 1.55; color: #b4c0de; text-wrap: balance; }
        .sh-also { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 1.25rem 2.75rem; max-width: 60rem; margin: clamp(2rem, 4vw, 3rem) auto 0; padding-top: clamp(1.5rem, 3vw, 2.25rem); border-top: 1px solid rgba(255, 255, 255, 0.12); text-align: start; }
        .sh-also p { display: flex; align-items: center; gap: 0.85rem; }
        #hp .sh-also b { font-size: 2.4rem; font-weight: 700; font-variation-settings: 'wght' 860; letter-spacing: -0.05em; line-height: 1; color: #6ee7b7; }
        .sh-also span { font-size: 0.9rem; line-height: 1.4; color: #b4c0de; }
        .sh-also strong { display: block; font-size: 1rem; color: #eef2ff; }
        #hp .sh-also .hp-btn-ghost:hover { border-color: #6ee7b7; }

        /* ---- Only on your own server ----------------------------------- */
        .sh-only-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; margin-top: clamp(2rem, 4vw, 3.25rem); }
        @media (min-width: 900px) { .sh-only-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .sh-only { display: flex; flex-direction: column; padding: clamp(1.4rem, 2.6vw, 2.25rem); border: 1px solid var(--sh-em-edge); border-radius: 1.9rem; background: radial-gradient(34rem 18rem at 80% 0%, var(--sh-em-tint), transparent 70%), var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
        #hp .sh-only .hp-h3 { margin-top: 1rem; }
        .sh-only > p { margin-top: 0.7rem; color: var(--hp-ink-2); }
        .sh-app { margin-top: auto; border: 1px solid var(--hp-line-2); border-radius: 1.1rem; background: var(--sh-screen); overflow: hidden; }
        .sh-only .sh-app { margin-top: 1.5rem; }
        .sh-only > p + .sh-app-push { flex: 1 1 auto; min-height: 0.25rem; }
        .sh-app-top { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.8rem 1.1rem; border-bottom: 1px solid var(--hp-line); background: var(--hp-bg-2); }
        .sh-app-top strong { font-size: 1rem; letter-spacing: -0.02em; }
        .sh-app-body { padding: 1rem 1.1rem 1.15rem; }
        .sh-app-cap { font-size: 0.82rem; font-weight: 700; font-variation-settings: 'wght' 650; color: var(--hp-ink); }
        .sh-app-rows { display: grid; gap: 0.4rem; margin-top: 0.4rem; }
        .sh-app-row { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; min-height: 2.2rem; padding: 0 0.7rem; border: 1px solid var(--hp-line-2); border-radius: 0.6rem; background: var(--sh-field); font-family: var(--hp-mono); font-size: 0.78rem; font-variation-settings: normal; color: var(--hp-ink); }
        .sh-app-row > span:first-child { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sh-app-row small { flex: none; font-family: var(--hp-display); font-size: 0.76rem; color: var(--sh-em); }
        .sh-app-tags { display: flex; flex-wrap: wrap; gap: 0.35rem; margin-top: 0.4rem; }
        .sh-app-tags span { display: inline-flex; align-items: center; min-height: 1.7rem; padding: 0 0.65rem; border: 1px solid var(--hp-line-2); border-radius: 999px; background: var(--sh-field); font-size: 0.8rem; color: var(--hp-ink-2); }
        .sh-app-found { margin-top: 0.85rem; padding-top: 0.8rem; border-top: 1px dashed var(--hp-line-2); }
        .sh-app-found li { display: flex; justify-content: space-between; gap: 1rem; padding-block: 0.2rem; font-size: 0.86rem; }
        .sh-app-found li span:first-child { color: var(--hp-ink-3); }
        .sh-app-found li span:last-child { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 700; font-variation-settings: 'wght' 650; }
        #hp .sh-app-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 2.5rem; padding: 0 1rem; border: 1px solid var(--hp-line-2); border-radius: 0.6rem; background: var(--sh-field); font-size: 0.86rem; font-weight: 700; font-variation-settings: 'wght' 680; color: var(--hp-ink); transition: border-color 0.2s ease, transform 0.2s ease; }
        #hp button.sh-app-btn:hover { border-color: var(--sh-em); transform: translateY(-1px); }
        #hp:not(.sh-js) .sh-app-btn { border-style: dashed; background: transparent; font-weight: 400; font-variation-settings: 'wght' 560; color: var(--hp-ink-3); pointer-events: none; }
        html.es-anim .sh-app-found.is-run li { animation: sh-arrive 0.3s ease-out both; animation-delay: calc(var(--i, 0) * 0.16s + 0.25s); }

        /* The update screen: the real one's three figures, its mark, its warning, its two
           buttons. Update can be pressed. */
        .sh-up { container-type: inline-size; }
        @container (max-width: 27rem) {
            .sh-up-stats { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
            .sh-up-stats > div:last-child { grid-column: 1 / -1; }
        }
        .sh-up-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1px; background: var(--hp-line); border-bottom: 1px solid var(--hp-line); }
        .sh-up-stats > div { padding: 0.85rem 1rem; background: var(--hp-bg-2); min-width: 0; }
        .sh-up-stats b { display: block; font-family: var(--hp-mono); font-size: clamp(0.9rem, 0.6vw + 0.7rem, 1.15rem); font-weight: 700; font-variation-settings: normal; letter-spacing: -0.03em; line-height: 1.25; color: var(--hp-ink); white-space: nowrap; }
        .sh-up-stats span { display: block; margin-top: 0.15rem; font-size: 0.76rem; color: var(--hp-ink-3); }
        .sh-up-mark { display: inline-flex; align-items: center; gap: 0.4rem; min-height: 1.6rem; padding: 0 0.65rem; border: 1px solid var(--sh-am-edge); border-radius: 999px; background: var(--sh-am-tint); font-size: 0.78rem; font-weight: 700; font-variation-settings: 'wght' 680; color: var(--sh-am); white-space: nowrap; }
        .sh-up-mark::before { content: ""; width: 0.4rem; height: 0.4rem; border-radius: 999px; background: currentColor; }
        .sh-up-warn { display: flex; gap: 0.6rem; padding: 0.75rem 0.85rem; border: 1px solid var(--sh-am-edge); border-radius: 0.7rem; background: var(--sh-am-tint); font-size: 0.86rem; line-height: 1.45; color: var(--sh-am); }
        .sh-up-warn svg { flex: none; width: 1.15rem; height: 1.15rem; margin-top: 0.05rem; }
        .sh-up-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem; margin-top: 0.9rem; }
        #hp .sh-up-go { position: relative; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; min-width: 7rem; min-height: 2.75rem; padding: 0 1.25rem; border-radius: 0.7rem; background: linear-gradient(100deg, #047857, #0f766e); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.24), 0 12px 26px -12px rgba(5, 150, 105, 0.8); font-size: 0.95rem; font-weight: 700; font-variation-settings: 'wght' 700; color: #fff; overflow: hidden; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        #hp .sh-up-go:hover { transform: translateY(-1px); }
        .sh-up-go i { position: absolute; inset: auto 0 0 0; height: 3px; background: #6ee7b7; transform: scaleX(0); transform-origin: 0 50%; }
        [dir="rtl"] .sh-up-go i { transform-origin: 100% 50%; }
        .sh-up-hint { font-size: 0.84rem; color: var(--hp-ink-3); }
        .sh-up:not(.is-ready) .sh-up-hint,
        .sh-up-go .is-busy,
        .sh-up[data-state="busy"] .sh-up-go .is-idle { display: none; }
        .sh-up[data-state="busy"] .sh-up-go .is-busy { display: inline; }
        .sh-up .is-after { display: none; }
        .sh-up[data-state="busy"] .sh-up-go { pointer-events: none; }
        .sh-up[data-state="busy"] .sh-up-go i { transform: scaleX(1); transition: transform 1.5s cubic-bezier(0.3, 0, 0.3, 1); }
        #hp .sh-up[data-state="done"] .is-before { display: none; }
        .sh-up[data-state="done"] .is-after { display: revert; }
        .sh-up[data-state="done"] span.is-after,
        .sh-up[data-state="done"] b.is-after { display: block; }
        .sh-up[data-state="done"] .sh-up-mark { border-color: var(--sh-em-edge); background: var(--sh-em-tint); color: var(--sh-em); }
        .sh-up-done { display: none; align-items: center; gap: 0.5rem; padding: 0.75rem 0.85rem; border: 1px solid var(--sh-em-edge); border-radius: 0.7rem; background: var(--sh-em-tint); font-size: 0.9rem; color: var(--sh-em); }
        .sh-up-done svg { flex: none; width: 1.1rem; height: 1.1rem; }
        .sh-up[data-state="done"] .sh-up-done { display: flex; }
        .sh-up[data-state="done"] .sh-up-warn { display: none; }
        html.es-anim .sh-up[data-state="done"] b.is-after { animation: sh-roll 0.4s cubic-bezier(0.22, 1, 0.36, 1) both; }
        #hp .sh-up-again { min-height: 2.75rem; padding: 0 0.5rem; font-size: 0.9rem; font-weight: 700; font-variation-settings: 'wght' 680; color: var(--sh-em); text-decoration: underline; text-underline-offset: 0.2em; text-decoration-thickness: 1px; }
        .sh-cap,
        .sh-only > p.sh-cap { margin-top: 0.85rem; font-size: 0.86rem; line-height: 1.5; color: var(--hp-ink-3); }

        /* ---- The control room: real screens ---------------------------- */
        .sh-room { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; margin-top: clamp(2rem, 4vw, 3.25rem); }
        @media (min-width: 1200px) { .sh-room { grid-template-columns: minmax(0, 23rem) minmax(0, 1fr); gap: clamp(1.75rem, 3vw, 3rem); align-items: start; } }
        .sh-room-list { display: grid; gap: 0.4rem; }
        @media (max-width: 1199px) {
            .sh-room.is-ready .sh-room-list { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        .sh-room.is-ready .sh-room-tab:not([aria-selected="true"]) span { display: none; }
        #hp .sh-room-tab { display: block; width: 100%; padding: 0.85rem 1rem; border: 1px solid transparent; border-radius: 1rem; text-align: start; color: inherit; transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease; }
        .sh-room.is-ready .sh-room-tab { cursor: pointer; }
        #hp .sh-room.is-ready .sh-room-tab:hover { background: var(--hp-bg); }
        .dark #hp .sh-room.is-ready .sh-room-tab:hover { background: var(--hp-bg-2); }
        #hp .sh-room-tab[aria-selected="true"] { border-color: var(--hp-line-2); background: var(--hp-bg); box-shadow: var(--hp-card-shadow); }
        .dark #hp .sh-room-tab[aria-selected="true"] { background: var(--hp-bg-2); }
        .sh-room-tab strong { display: flex; align-items: center; gap: 0.6rem; font-size: 1.08rem; letter-spacing: -0.02em; }
        .sh-room-tab strong::before { content: ""; flex: none; width: 0.5rem; height: 0.5rem; border-radius: 0.15rem; background: var(--hp-line-2); transition: background-color 0.2s ease; }
        .sh-room-tab[aria-selected="true"] strong::before { background: var(--sh-em); }
        .sh-room-tab span { display: block; margin-top: 0.3rem; padding-inline-start: 1.1rem; font-size: 0.93rem; line-height: 1.5; color: var(--hp-ink-2); }
        @media (max-width: 1199px) {
            .sh-room.is-ready .sh-room-tab span { display: none; }
            .sh-room.is-ready .sh-room-tab { padding: 0.7rem 0.6rem; border-color: var(--hp-line); }
            .sh-room.is-ready .sh-room-tab strong { gap: 0.45rem; font-size: 0.98rem; }
        }
        .sh-frame { border: 1px solid var(--hp-line-2); border-radius: 1.25rem; overflow: hidden; background: var(--sh-screen); box-shadow: var(--hp-pop-shadow); }
        .sh-room-panel + .sh-room-panel { border-top: 1px solid var(--hp-line); }
        .sh-room.is-ready .sh-room-panel + .sh-room-panel { border-top: 0; }
        .sh-room-line { display: none; padding: 0.85rem 1.1rem 1rem; border-top: 1px solid var(--hp-line); background: var(--hp-bg-2); font-size: 0.95rem; color: var(--hp-ink-2); }
        @media (max-width: 1199px) { .sh-room.is-ready .sh-room-line { display: block; } }
        .sh-room-side { display: grid; gap: 1.25rem; align-content: start; min-width: 0; }
        @media (max-width: 1199px) {
            /* Where there is one column the screen stands straight under its three names; the
               way into the demos follows it. */
            .sh-room-side { display: contents; }
            .sh-room .sh-frame { order: 2; }
            .sh-room .sh-try { order: 3; }
        }
        .sh-try { padding: 1.15rem 1.2rem 1.25rem; border: 1px solid var(--hp-line); border-radius: 1.25rem; background: var(--hp-bg); }
        .dark .sh-try { background: var(--hp-bg-2); }
        .sh-try p { font-size: 0.95rem; line-height: 1.5; color: var(--hp-ink-2); }
        .sh-try p strong { display: block; margin-bottom: 0.25rem; font-size: 1.12rem; letter-spacing: -0.02em; color: var(--hp-ink); }
        .sh-try-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem; }
        @media (min-width: 1200px) {
            /* The frame is exactly its picture. The column beside it is stretched to the same
               height: names at the top, the way into the demos at the foot. */
            .sh-room-side { align-self: stretch; grid-template-rows: auto 1fr; }
            .sh-try { align-self: end; }
        }

        /* ---- Yours, and yours to run ----------------------------------- */
        .sh-own-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; margin-top: clamp(2rem, 4vw, 3.25rem); }
        @media (min-width: 900px) { .sh-own-grid { grid-template-columns: minmax(0, 0.82fr) minmax(0, 1.18fr); } }
        .sh-card { display: flex; flex-direction: column; padding: clamp(1.4rem, 2.6vw, 2.25rem); border: 1px solid var(--hp-line); border-radius: 1.9rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
        .sh-card.is-yours { border-color: var(--sh-em-edge); background: radial-gradient(34rem 18rem at 12% 0%, var(--sh-em-tint), transparent 70%), var(--hp-bg-2); }
        #hp .sh-card .hp-h3 { margin-top: 0.9rem; }
        #hp .sh-card > .hp-h3:first-child { margin-top: 0; }
        .sh-card > p { margin-top: 0.8rem; color: var(--hp-ink-2); }
        .sh-ticks { display: grid; gap: 0.8rem; margin-top: 1.5rem; }
        .sh-ticks li { display: flex; align-items: flex-start; gap: 0.75rem; color: var(--hp-ink); }
        .sh-ticks i { display: grid; place-items: center; flex: none; width: 1.4rem; height: 1.4rem; margin-top: 0.1rem; border-radius: 999px; background: var(--sh-em-tint); color: var(--sh-em); }
        .sh-ticks i svg { width: 0.85rem; height: 0.85rem; }
        .sh-dots { display: grid; gap: 0.55rem; margin-top: 1.25rem; font-size: 0.95rem; color: var(--hp-ink-2); }
        .sh-dots li { display: flex; align-items: flex-start; gap: 0.65rem; }
        .sh-dots li::before { content: ""; flex: none; width: 0.3rem; height: 0.3rem; margin-top: 0.6em; border-radius: 999px; background: var(--sh-em); }

        .sh-leave { margin-top: auto; padding-top: 1.75rem; }
        .sh-leave > p { padding-top: 1.4rem; border-top: 1px solid var(--hp-line-2); font-family: var(--hp-mono); font-size: 0.74rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.06em; line-height: 1.6; text-transform: uppercase; color: var(--hp-ink-3); }
        .sh-leave ul { display: grid; gap: 0.6rem; margin-top: 0.9rem; }
        .sh-leave li { display: flex; align-items: center; justify-content: space-between; gap: 0.9rem; padding: 0.8rem 1rem; border: 1px solid var(--hp-line-2); border-radius: 1rem; background: var(--hp-bg); font-size: 0.93rem; line-height: 1.45; color: var(--hp-ink-2); }
        .dark .sh-leave li { background: var(--hp-bg-3); }
        .sh-leave li strong { display: block; font-size: 1rem; color: var(--hp-ink); }
        .sh-state { flex: none; min-width: 2.75rem; padding: 0.2rem 0.55rem; border: 1px solid var(--hp-line-2); border-radius: 0.45rem; font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.1em; text-align: center; text-transform: uppercase; color: var(--hp-ink-3); }
        .sh-state.is-on { border-color: var(--sh-em-edge); background: var(--sh-em-tint); color: var(--sh-em); }

        /* Federation: a switch that is off, as it is on a new install. */
        .sh-fed { margin-top: 1.5rem; padding: 1rem 1.1rem 1.15rem; border: 1px solid var(--hp-line-2); border-radius: 1.25rem; background: var(--hp-bg); }
        .dark .sh-fed { background: var(--hp-bg-3); }
        .sh-fed-top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .sh-fed-top span { font-size: 0.98rem; font-weight: 700; font-variation-settings: 'wght' 680; }
        .sh-fed:not(.is-ready) .sh-fed-top small { display: none; }
        .sh-fed-top small { display: block; font-size: 0.82rem; font-weight: 400; font-variation-settings: 'wght' 450; color: var(--hp-ink-3); }
        #hp .sh-switch { position: relative; flex: none; width: 3.25rem; height: 1.9rem; border-radius: 999px; background: #8f9bb8; box-shadow: inset 0 2px 4px rgba(10, 16, 32, 0.3); transition: background-color 0.25s ease; }
        .dark #hp .sh-switch { background: #46516e; }
        .sh-switch::after { content: ""; position: absolute; top: 0.2rem; inset-inline-start: 0.2rem; width: 1.5rem; height: 1.5rem; border-radius: 999px; background: #fff; box-shadow: 0 2px 5px rgba(10, 16, 32, 0.35); transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1); }
        #hp .sh-switch[aria-checked="true"] { background: #059669; }
        .sh-switch[aria-checked="true"]::after { transform: translateX(1.35rem); }
        [dir="rtl"] .sh-switch[aria-checked="true"]::after { transform: translateX(-1.35rem); }
        [dir="rtl"] [dir="ltr"] .sh-switch[aria-checked="true"]::after { transform: translateX(1.35rem); }
        .sh-fed-map { display: grid; grid-template-columns: minmax(0, 1fr) minmax(2rem, 0.3fr) minmax(0, 1fr); align-items: center; gap: 0.5rem; margin-top: 1rem; }
        .sh-fed-node { min-width: 0; height: 100%; padding: 0.7rem 0.75rem 0.8rem; border: 1px solid var(--hp-line-2); border-radius: 0.9rem; background: var(--hp-bg-2); }
        .sh-fed-node.is-you { border-color: var(--sh-em-edge); background: linear-gradient(var(--sh-em-tint), var(--sh-em-tint)), var(--hp-bg-2); }
        .sh-fed-node strong { display: block; font-size: 0.92rem; }
        .sh-fed-node > span { display: block; font-family: var(--hp-mono); font-size: 0.7rem; font-variation-settings: normal; color: var(--hp-ink-3); overflow-wrap: anywhere; }
        .sh-fed-node ul { display: grid; gap: 0.3rem; margin-top: 0.6rem; }
        .sh-fed-node li { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.1rem 0.5rem; min-height: 1.9rem; padding: 0.2rem 0.55rem; border: 1px solid var(--hp-line); border-radius: 0.5rem; background: var(--hp-bg-2); font-size: 0.8rem; line-height: 1.3; color: var(--hp-ink-2); }
        .sh-fed-node li em { font-family: var(--hp-mono); font-size: 0.7rem; font-style: normal; font-variation-settings: normal; letter-spacing: 0.06em; text-transform: uppercase; color: var(--hp-ink-3); }
        .sh-fed-node li.is-off { border-style: dashed; color: var(--hp-ink-3); }
        .sh-fed[data-on="1"] .sh-fed-node li.is-public { border-color: var(--sh-em-edge); color: var(--hp-ink); }
        .sh-fed[data-on="1"] .sh-fed-node li.is-public em { color: var(--sh-em); }
        .sh-fed-rail { position: relative; height: 2px; border-radius: 2px; background: repeating-linear-gradient(90deg, var(--hp-line-2) 0 5px, transparent 5px 10px); }
        .sh-fed-rail::after { content: ""; position: absolute; top: 50%; left: 0; width: 0.55rem; height: 0.55rem; margin-top: -0.275rem; border-radius: 999px; background: #10b981; box-shadow: 0 0 12px rgba(16, 185, 129, 0.9); opacity: 0; }
        .sh-fed[data-on="1"] .sh-fed-rail { background: repeating-linear-gradient(90deg, #10b981 0 5px, transparent 5px 10px); }
        .sh-fed[data-on="1"] .sh-fed-rail::after { opacity: 1; left: calc(100% - 0.55rem); }
        html.es-anim .sh-fed[data-on="1"] .sh-fed-rail::after { animation: sh-send 0.9s cubic-bezier(0.5, 0, 0.2, 1) 1 both; }
        html.es-anim .sh-fed[data-on="1"] .sh-fed-node.is-them li.is-on { animation: sh-arrive 0.35s ease-out 0.8s both; }
        [dir="rtl"] .sh-fed-rail { transform: scaleX(-1); }
        @keyframes sh-send { from { left: 0; opacity: 0; } 15% { opacity: 1; } to { left: calc(100% - 0.55rem); opacity: 1; } }
        @media (max-width: 559px) {
            /* No room for two lists side by side: the bridge runs down the page instead. */
            .sh-fed-map { grid-template-columns: minmax(0, 1fr); }
            .sh-fed-rail { justify-self: center; width: 2px; height: 1.4rem; background: repeating-linear-gradient(180deg, var(--hp-line-2) 0 5px, transparent 5px 10px); }
            .sh-fed[data-on="1"] .sh-fed-rail { background: repeating-linear-gradient(180deg, #10b981 0 5px, transparent 5px 10px); }
            .sh-fed-rail::after { display: none; }
        }
        .sh-fed-say { margin-top: 0.9rem; font-size: 0.9rem; line-height: 1.5; color: var(--hp-ink-2); }
        .sh-fed .is-on,
        .sh-fed[data-on="1"] .is-off { display: none; }
        .sh-fed[data-on="1"] span.is-on { display: inline; }
        .sh-fed[data-on="1"] li.is-on { display: flex; }
        .sh-fed[data-on="1"] .sh-fed-node.is-them { border-color: var(--sh-em-edge); }

        /* Pre-flight: the six things that are yours to run. Amber until you say each is
           covered; when all six are, the sheet turns green. Dark in both modes. */
        .sh-pre { margin-top: 1.25rem; border: 1px solid rgba(251, 191, 36, 0.3); border-radius: 1.9rem; overflow: hidden; background: radial-gradient(40rem 20rem at 85% 0%, rgba(245, 158, 11, 0.13), transparent 70%), #0b0d12; color: #eef2ff; transition: border-color 0.4s ease; }
        .sh-pre[data-ready="1"] { border-color: rgba(52, 211, 153, 0.45); background: radial-gradient(40rem 20rem at 85% 0%, rgba(16, 185, 129, 0.2), transparent 70%), #06100c; }
        .sh-pre-top { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 1rem 2rem; padding: clamp(1.4rem, 2.6vw, 2.25rem) clamp(1.4rem, 2.6vw, 2.25rem) 0; }
        .sh-pre-top > div { flex: 1 1 24rem; max-width: 44rem; }
        #hp .sh-pre-top .hp-h3 { margin-top: 0.85rem; color: #fff; }
        .sh-pre-top p { margin-top: 0.6rem; color: #c5cde2; }
        .sh-pre-count { display: none; flex: none; font-family: var(--hp-mono); font-size: 0.8rem; font-variation-settings: normal; color: #c5cde2; }
        .sh-pre.is-ready .sh-pre-count { display: block; }
        .sh-pre-count b { font-size: 1.9rem; font-weight: 700; font-variation-settings: normal; letter-spacing: -0.04em; color: var(--sh-am-lit); }
        .sh-pre[data-ready="1"] .sh-pre-count b { color: #6ee7b7; }
        .sh-pre-list { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.5rem; padding: clamp(1.15rem, 2vw, 1.6rem) clamp(1.4rem, 2.6vw, 2.25rem); }
        @media (min-width: 700px) { .sh-pre-list { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1100px) { .sh-pre-list { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        #hp .sh-pre-item { display: grid; grid-template-columns: 1.5rem minmax(0, 1fr); gap: 0.8rem; width: 100%; height: 100%; padding: 0.95rem 1rem 1.05rem; border: 1px solid rgba(251, 191, 36, 0.2); border-radius: 1.1rem; background: rgba(245, 158, 11, 0.05); text-align: start; color: inherit; transition: border-color 0.25s ease, background-color 0.25s ease; }
        .sh-pre.is-ready .sh-pre-item { cursor: pointer; }
        #hp .sh-pre.is-ready .sh-pre-item:hover { border-color: rgba(251, 191, 36, 0.5); }
        .sh-pre-box { display: grid; place-items: center; width: 1.5rem; height: 1.5rem; margin-top: 0.05rem; border: 1.5px solid var(--sh-am-lit); border-radius: 0.4rem; color: transparent; transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease; }
        .sh-pre:not(.is-ready) .sh-pre-box,
        .sh-pre:not(.is-ready) .sh-pre-ask { display: none; }
        #hp .sh-pre:not(.is-ready) .sh-pre-item { grid-template-columns: minmax(0, 1fr); }
        .sh-pre-box svg { width: 0.95rem; height: 0.95rem; }
        .sh-pre-item strong { display: block; font-size: 1.05rem; letter-spacing: -0.02em; color: var(--sh-am-lit); transition: color 0.2s ease; }
        .sh-pre-text { display: block; margin-top: 0.25rem; font-size: 0.9rem; line-height: 1.5; color: #b9c2d8; }
        #hp .sh-pre-item[aria-pressed="true"] { border-color: rgba(52, 211, 153, 0.4); background: rgba(16, 185, 129, 0.08); }
        .sh-pre-item[aria-pressed="true"] .sh-pre-box { border-color: #34d399; background: #34d399; color: #03120c; }
        .sh-pre-item[aria-pressed="true"] strong { color: #6ee7b7; }
        .sh-pre-foot { display: none; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem 2rem; padding: 1.15rem clamp(1.4rem, 2.6vw, 2.25rem) 1.35rem; border-top: 1px solid rgba(255, 255, 255, 0.1); background: rgba(255, 255, 255, 0.03); }
        .sh-pre.is-ready .sh-pre-foot { display: flex; }
        .sh-pre-foot p { flex: 1 1 22rem; color: #c5cde2; }
        .sh-pre-foot p strong { color: #fff; }
        .sh-pre .is-yes,
        .sh-pre[data-ready="1"] .is-no { display: none; }
        .sh-pre[data-ready="1"] p.is-yes { display: block; }
        .sh-pre[data-ready="1"] a.is-yes { display: inline-flex; }

        /* ---- Should you ------------------------------------------------- */
        .sh-vs { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; margin-top: clamp(2rem, 4vw, 3.25rem); }
        @media (min-width: 800px) { .sh-vs { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .sh-vs-card { position: relative; display: flex; flex-direction: column; padding: clamp(1.4rem, 2.6vw, 2.25rem); border: 1px solid var(--hp-line); border-radius: 1.9rem; background: var(--hp-bg); }
        .dark .sh-vs-card { background: var(--hp-bg-2); }
        .sh-vs-card.is-lead { border-color: var(--sh-em); background: radial-gradient(34rem 18rem at 85% 0%, var(--sh-em-tint), transparent 70%), var(--hp-bg-2); box-shadow: 0 0 0 4px var(--sh-em-tint), var(--hp-card-shadow); }
        .sh-vs-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem 1rem; }
        .sh-vs-rows { display: grid; gap: 0.95rem; margin-top: 1.4rem; }
        .sh-vs-rows dt { font-family: var(--hp-mono); font-size: 0.7rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.1em; text-transform: uppercase; color: var(--hp-ink-3); }
        .sh-vs-rows dd { margin-top: 0.15rem; font-size: clamp(1.02rem, 0.35vw + 0.95rem, 1.2rem); line-height: 1.4; color: var(--hp-ink); }
        .sh-vs-verdict { margin-top: auto; padding-top: 1.25rem; }
        .sh-vs-verdict p { padding-top: 1.1rem; border-top: 1px solid var(--hp-line); font-weight: 700; font-variation-settings: 'wght' 620; color: var(--hp-ink-2); }
        .sh-here { position: absolute; top: -0.85rem; inset-inline-start: 1.5rem; display: inline-flex; align-items: center; min-height: 1.7rem; padding: 0 0.8rem; border-radius: 999px; background: linear-gradient(100deg, #047857, #0f766e); font-family: var(--hp-mono); font-size: 0.7rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.1em; text-transform: uppercase; color: #fff; }
        /* Once the six above have been touched, the mark moves to the card they point at. */
        .sh-here.is-pick,
        .sh-vs:not([data-pick=""]) .sh-here.is-none { display: none; }
        .sh-vs[data-pick="hosted"] [data-sh-vs-card="hosted"] .sh-here.is-pick,
        .sh-vs[data-pick="self"] [data-sh-vs-card="self"] .sh-here.is-pick { display: inline-flex; }
        .sh-vs[data-pick="hosted"] [data-sh-vs-card="hosted"] { border-color: var(--sh-em); background: radial-gradient(34rem 18rem at 85% 0%, var(--sh-em-tint), transparent 70%), var(--hp-bg-2); box-shadow: 0 0 0 4px var(--sh-em-tint), var(--hp-card-shadow); }
        .sh-vs[data-pick="hosted"] [data-sh-vs-card="self"] { border-color: var(--hp-line); background: var(--hp-bg); box-shadow: none; }
        .dark .sh-vs[data-pick="hosted"] [data-sh-vs-card="self"] { background: var(--hp-bg-2); }
        [data-sh-verdict] .is-some,
        [data-sh-verdict] .is-all,
        [data-sh-verdict][data-state="some"] .is-none,
        [data-sh-verdict][data-state="all"] .is-none { display: none; }
        [data-sh-verdict][data-state="some"] .is-some,
        [data-sh-verdict][data-state="all"] .is-all { display: inline; }
        #hp .sh-count { display: inline-flex; align-items: center; gap: 0.4rem; min-height: 2rem; padding: 0 0.85rem; border: 1px solid var(--sh-am-edge); border-radius: 999px; background: var(--sh-am-tint); font-family: var(--hp-mono); font-size: 0.78rem; font-variation-settings: normal; color: var(--sh-am); white-space: nowrap; }
        #hp .sh-count b { font-weight: 700; font-variation-settings: normal; }
        #hp .sh-count.is-all { border-color: var(--sh-em-edge); background: var(--sh-em-tint); color: var(--sh-em); }
        .dark .sh-vs-card:not(.is-lead) { border-color: var(--hp-line-2); }
        .sh-vs-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.9rem 1.5rem; margin-top: 2rem; color: var(--hp-ink-2); }

        /* ---- Or make it your own product -------------------------------- */
        .sh-saas { display: grid; grid-template-columns: minmax(0, 1fr); gap: clamp(1.75rem, 3vw, 3.5rem); align-items: center; }
        @media (min-width: 1000px) { .sh-saas { grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr); } }
        .sh-saas .hp-h2 { margin-top: 1rem; }
        .sh-saas .hp-lead { margin-top: 1.1rem; }
        .sh-board { padding: clamp(1.1rem, 2vw, 1.6rem); border: 1px solid var(--hp-line-2); border-radius: 1.9rem; background: radial-gradient(34rem 18rem at 80% 0%, var(--sh-em-tint), transparent 70%), var(--hp-bg-2); box-shadow: var(--hp-pop-shadow); }
        .sh-env { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.75rem 0.9rem 0.75rem 1.1rem; border-radius: 1rem; background: var(--sh-term); font-family: var(--hp-mono); font-size: clamp(0.86rem, 0.5vw + 0.75rem, 1.05rem); font-variation-settings: normal; color: var(--sh-term-ink); }
        .sh-env b { font-weight: 700; font-variation-settings: normal; color: var(--sh-term-dim); }
        .sh-board[data-on="1"] .sh-env b { color: #6ee7b7; }
        .sh-env .is-on,
        .sh-board[data-on="1"] .sh-env .is-off { display: none; }
        .sh-board[data-on="1"] .sh-env .is-on { display: inline; }
        .sh-board:not(.is-ready) .sh-switch { display: none; }
        .sh-tenants { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.55rem; margin-top: 0.9rem; }
        .sh-tenant { display: flex; align-items: center; justify-content: space-between; gap: 0.6rem; min-width: 0; min-height: 3.4rem; padding: 0.6rem 0.85rem; border: 1px solid var(--hp-line-2); border-radius: 0.95rem; background: var(--sh-screen); transition: opacity 0.35s ease, transform 0.45s cubic-bezier(0.22, 1, 0.36, 1); transition-delay: calc(var(--i, 0) * 70ms); }
        .sh-tenant > span:first-child { min-width: 0; font-family: var(--hp-mono); font-size: 0.8rem; font-variation-settings: normal; line-height: 1.35; color: var(--hp-ink-3); }
        .sh-tenant .sh-dom { display: inline-block; max-width: 100%; overflow-wrap: anywhere; }
        .sh-tenant > span:first-child b { font-weight: 700; font-variation-settings: normal; color: var(--hp-ink); }
        .sh-tenant small { flex: none; padding: 0.15rem 0.55rem; border: 1px solid var(--hp-line-2); border-radius: 999px; font-size: 0.72rem; font-weight: 700; font-variation-settings: 'wght' 650; color: var(--hp-ink-2); }
        .sh-tenant small.is-paid { border-color: var(--sh-em-edge); background: var(--sh-em-tint); color: var(--sh-em); }
        .sh-tenant.is-solo { border-color: var(--sh-em-edge); }
        .sh-board.is-ready:not([data-on="1"]) .sh-tenant:not(.is-solo) { opacity: 0; transform: translateY(-0.6rem) scale(0.97); pointer-events: none; }
        .sh-tenant .is-on,
        .sh-board[data-on="1"] .sh-tenant .is-off { display: none; }
        .sh-board[data-on="1"] .sh-tenant .is-on { display: inline; }
        .sh-board-say { margin-top: 0.9rem; font-size: 0.9rem; color: var(--hp-ink-3); }
        .sh-board-say .is-on,
        .sh-board[data-on="1"] .sh-board-say .is-off { display: none; }
        .sh-board[data-on="1"] .sh-board-say .is-on { display: inline; }

        /* ---- The manual, and the source --------------------------------- */
        .sh-shelf { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; }
        @media (min-width: 1000px) { .sh-shelf { grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr); align-items: stretch; } }
        .sh-man { padding: clamp(1.4rem, 2.6vw, 2.25rem); border: 1px solid var(--hp-line); border-radius: 1.9rem; background: var(--hp-bg); }
        .dark .sh-man { background: var(--hp-bg-2); }
        .sh-man .hp-h3 { margin-top: 0.9rem; }
        .sh-man-cols { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem 2rem; margin-top: 1.6rem; }
        @media (min-width: 640px) { .sh-man-cols { grid-template-columns: minmax(0, 1fr) minmax(0, 1.3fr) minmax(0, 1fr); } }
        #hp .sh-man-cols h3 { padding-bottom: 0.6rem; border-bottom: 2px solid var(--hp-ink); font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.14em; text-transform: uppercase; color: var(--hp-ink-3); }
        .sh-man-cols li { border-bottom: 1px solid var(--hp-line); }
        #hp .sh-man-cols a { display: flex; align-items: center; justify-content: space-between; gap: 0.6rem; min-height: 3.1rem; padding-block: 0.4rem; font-size: 1.06rem; font-weight: 700; font-variation-settings: 'wght' 640; color: var(--hp-ink); transition: color 0.2s ease; }
        #hp .sh-man-cols a:hover { color: var(--sh-em); }
        .sh-man-cols a svg { flex: none; width: 1rem; height: 1rem; color: var(--hp-ink-3); transition: transform 0.2s ease, color 0.2s ease; }
        .sh-man-cols a:hover svg { transform: translateX(3px); color: var(--sh-em); }
        [dir="rtl"] .sh-man-cols a svg { transform: scaleX(-1); }
        .sh-src { display: flex; flex-direction: column; padding: clamp(1.4rem, 2.6vw, 2.25rem); border-radius: 1.9rem; background: radial-gradient(30rem 18rem at 85% 0%, rgba(16, 185, 129, 0.3), transparent 70%), #050814; color: #eef2ff; }
        .dark .sh-src { box-shadow: 0 0 0 1px rgba(52, 211, 153, 0.25); }
        .sh-src-mark { width: 2.75rem; height: 2.75rem; color: #fff; }
        #hp .sh-src .hp-h3 { margin-top: 1.1rem; color: #fff; }
        .sh-src > p { margin-top: 0.8rem; color: #c5cde2; }
        .sh-src-star { margin-top: 1.25rem; }
        .sh-src-star > div { margin-bottom: 0; }
        .sh-src-links { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: auto; padding-top: 1.5rem; }
        #hp .sh-src-links a { display: inline-flex; align-items: center; gap: 0.5rem; min-height: 2.75rem; padding: 0 0.95rem; border: 1px solid rgba(255, 255, 255, 0.16); border-radius: 0.9rem; font-weight: 700; font-variation-settings: 'wght' 660; color: #eef2ff; transition: background-color 0.2s ease, border-color 0.2s ease; }
        #hp .sh-src-links a:hover { border-color: #6ee7b7; background: rgba(16, 185, 129, 0.14); }
        .sh-src-links svg { flex: none; width: 1rem; height: 1rem; color: #9fb1d6; }

        /* ---- The last panel ------------------------------------------- */
        #hp .hp-finale.is-own {
            background:
                radial-gradient(52rem 30rem at 50% -6rem, rgba(5, 150, 105, 0.55), transparent 70%),
                radial-gradient(30rem 20rem at 8% 100%, rgba(45, 212, 191, 0.18), transparent 70%),
                radial-gradient(30rem 20rem at 92% 100%, rgba(16, 185, 129, 0.18), transparent 70%),
                #050814;
            box-shadow: 0 40px 100px -40px rgba(5, 150, 105, 0.6);
        }
        #hp .hp-finale .hp-h2 { overflow-wrap: anywhere; }
        .dark #hp .hp-finale.is-own { box-shadow: 0 0 0 1px rgba(52, 211, 153, 0.28), 0 40px 100px -40px rgba(5, 150, 105, 0.6); }
        .sh-recap { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem; margin-top: 1.5rem; }
        .sh-recap li { display: inline-flex; align-items: center; gap: 0.45rem; min-height: 2rem; padding: 0 0.85rem; border: 1px solid rgba(52, 211, 153, 0.4); border-radius: 999px; background: rgba(16, 185, 129, 0.12); font-family: var(--hp-mono); font-size: 0.78rem; font-variation-settings: normal; color: #a7f3d0; overflow-wrap: anywhere; }
        .sh-recap li::before { content: ""; flex: none; width: 0.4rem; height: 0.4rem; border-radius: 999px; background: #34d399; }
        /* The house kicker's mark is blue; on this page every opener is green. */
        #hp #faq .hp-kicker::before { background: linear-gradient(135deg, #10b981, #2dd4bf); }
        #hp .hp-head.is-center .hp-lead { text-wrap: balance; }
        /* Without the script the page is the page it ends on, and it offers nothing that
           cannot be done: no field to type a domain in, no dial, no switch, no Copy, no Update. */
        #hp:not(.sh-js) :is(.sh-domain, .sh-js-only, .sh-dialrow, .sh-copy, .sh-up-actions, .sh-fed .sh-switch) { display: none; }
        .sh-room:not(.is-ready) .sh-room-line { display: block; }
        .sh-no-clip .sh-copy { display: none; }
        /* The hero's mark is the kit's, and the kit's pulses. Nothing on this page repeats. */
        #hp .sh-hero .hp-live::before { animation: none; opacity: 0; }
        /* A class that sets display outranks the attribute, so say it once for the page. */
        #hp [hidden] { display: none !important; }

        @media (prefers-reduced-motion: reduce) {
            #hp .sh-chip,
            .sh-chip::before,
            .sh-stage.is-ready .sh-scene,
            .sh-tenant,
            .sh-switch,
            .sh-switch::after,
            .sh-up-go i { transition: none !important; }
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
        // What can only be pressed with a script running is drawn only where one is.
        (function () { var page = document.getElementById('hp'); if (page) page.classList.add('sh-js'); })();
    </script>

    <!-- ============================================================ -->
    <!-- Hero: the claim, and the address you would run it at        -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero hp-hero sh-hero">
        <div class="hp-hero-sky" aria-hidden="true"></div>

        <div class="hp-hero-copy">
            <h1 class="hp-h1">
                <x-marketing.hero-eyebrow class="es-fade-up es-d-1 hp-eyebrow">
                    <span class="hp-live" aria-hidden="true"><i></i></span>
                    Selfhosted event calendar
                </x-marketing.hero-eyebrow>
                <span class="es-mask"><span class="es-mask-line">Selfhost the whole thing.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-selfhost">Nothing is held back</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 hp-sub">
                Run Event Schedule on your own infrastructure and every Pro and Enterprise feature is included, free. No platform fees, no seat counts, no data leaving your server.
            </p>

            {{-- hp-demo is a hook, not a style: the link-preview card leaves out whatever carries it. --}}
            <div class="es-fade-up es-d-3 sh-domain hp-demo">
                <label class="sh-domain-label" for="sh-domain-input">Where would yours live?</label>
                <div class="sh-addr" dir="ltr">
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    <span class="sh-addr-scheme" aria-hidden="true">https://</span>
                    <input id="sh-domain-input" type="text" inputmode="url" enterkeyhint="go" autocomplete="off" autocapitalize="none" autocorrect="off" spellcheck="false" maxlength="48" placeholder="your-domain.com" aria-describedby="sh-domain-hint" data-sh-domain-input>
                </div>
                <p class="sh-domain-hint" id="sh-domain-hint" data-sh-domain-hint data-typed="Scroll on. Everything below now runs on ">Type a domain and every screen below is drawn at that address.</p>
            </div>

            <div class="es-fade-up es-d-4 hp-hero-actions">
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="hp-btn hp-btn-ghost is-still">
                    <svg aria-hidden="true" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $ghPath }}"/></svg>
                    View on GitHub
                </a>
                <a href="#install" class="hp-btn hp-btn-own is-down" data-sh-go>
                    Install it
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- The install, played: terminal, wizard, cron, running        -->
    <!-- ============================================================ -->
    {{-- One window and four scenes. The steps beside it are the HowTo block's own, so the
         schema and the page cannot disagree. Progressive enhancement: without the script every
         scene stands in the page, one under the other, each with its heading, so a crawler
         and a no-JS visitor get every command. The script adds .is-ready, which stacks the
         scenes in one cell and shows the one for the step the stage is on. --}}
    <section id="install" class="sh-stage-sec">
        <div class="sh-stage-wrap">
            <div class="sh-stage" data-sh-stage data-method="{{ $firstMethod }}">
                <div class="sh-stage-head" data-reveal>
                    <div class="sh-prompt" aria-hidden="true"><span>~/eventschedule</span> $ install</div>
                    <h2 class="hp-h2">Watch it <span class="text-gradient-selfhost">come up</span></h2>
                    <p class="hp-lead">A shared cPanel host, a Docker stack or a plain zip on your own box. All three land on the same setup wizard. These are the real commands, the wizard's own fields and a real admin screen, with only the waiting cut out.</p>
                </div>

                <div class="sh-methods" role="tablist" aria-label="Installation method">
                    @foreach ($installMethods as $m)
                        <button type="button" class="sh-method" role="tab" id="tab-{{ $m['key'] }}" aria-controls="panel-{{ $m['key'] }}" aria-selected="{{ $m['key'] === $firstMethod ? 'true' : 'false' }}" tabindex="{{ $m['key'] === $firstMethod ? '0' : '-1' }}" data-sh-method="{{ $m['key'] }}">
                            <strong>{{ $m['label'] }}</strong>
                            <span>{{ $m['tagline'] }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="sh-beats-wrap">
                    <ol class="sh-beats">
                        @foreach ($howToSteps as $sIndex => $step)
                            <li>
                                <button type="button" class="sh-beat{{ ($step['own'] ?? false) ? ' is-own' : '' }}" data-sh-beat="{{ $sIndex + 1 }}" aria-label="Step {{ $sIndex + 1 }}: {{ $step['name'] }}" @if ($sIndex === 0) aria-current="step" @endif>
                                    <span class="sh-beat-n" aria-hidden="true">{{ $sIndex + 1 }}</span>
                                    <span>
                                        <span class="sh-beat-name">
                                            <strong><span class="sh-beat-long">{{ $step['name'] }}</span><span class="sh-beat-short" aria-hidden="true">{{ $step['short'] }}</span></strong>
                                            @if ($step['own'] ?? false)
                                                <span class="sh-own-tag">Yours to run</span>
                                            @endif
                                        </span>
                                        <span class="sh-beat-text">{{ $step['text'] }}</span>
                                    </span>
                                    <span class="sh-beat-bar" aria-hidden="true"></span>
                                </button>
                            </li>
                        @endforeach
                        <li>
                            <button type="button" class="sh-beat" data-sh-beat="4" aria-label="Step 4: It is running">
                                <span class="sh-beat-n" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $checkPath }}" /></svg></span>
                                <span>
                                    <span class="sh-beat-name"><strong><span class="sh-beat-long">It is running</span><span class="sh-beat-short" aria-hidden="true">Running</span></strong></span>
                                    <span class="sh-beat-text">Sign in with the account you made in the wizard. It is the instance admin, and the install is yours from here.</span>
                                </span>
                                <span class="sh-beat-bar" aria-hidden="true"></span>
                            </button>
                        </li>
                    </ol>
                </div>

                <div class="sh-stage-main">
                    <div class="sh-desk" data-sh-window>
                        {{-- Scene 1: the files, one terminal per way in. --}}
                        @foreach ($installMethods as $m)
                            <div class="sh-scene sh-term{{ $m['key'] === $firstMethod ? ' is-on' : '' }}" role="tabpanel" id="panel-{{ $m['key'] }}" aria-labelledby="tab-{{ $m['key'] }}" data-sh-scene="1" data-sh-panel="{{ $m['key'] }}" dir="ltr">
                                <h3 class="sh-scene-title">{{ $m['label'] }}: {{ $m['tagline'] }}</h3>
                                <div class="sh-bar">
                                    <i></i><i></i><i></i>
                                    <span class="sh-bar-title"><span class="sh-dom" data-sh-domain>{{ $placeDomain }}</span> : {{ $m['title'] }}</span>
                                    @if ($m['copy'])
                                        <button type="button" class="sh-copy" data-sh-copy="{{ $m['copy'] }}">Copy</button>
                                    @endif
                                </div>
                                <div class="sh-term-body">
                                    @foreach ($m['lines'] as $lIndex => [$kind, $text])
                                        @if ($kind === 'cmd')
                                            <div class="sh-line is-cmd" style="--i: {{ $lIndex }};"><b>$</b><span>{!! preg_replace('#/(?=[a-z])#', '/<wbr>', e($text)) !!}</span></div>
                                        @elseif ($kind === 'note')
                                            <div class="sh-line is-note" style="--i: {{ $lIndex }};"><b>&nbsp;</b><span>{{ $text }}</span></div>
                                        @else
                                            <div class="sh-line is-out" style="--i: {{ $lIndex }};"><b>{{ $lIndex + 1 }}</b><span>{{ $text }}</span></div>
                                        @endif
                                    @endforeach
                                    <div class="sh-line is-end" style="--i: {{ count($m['lines']) + 1 }};">
                                        <span class="sh-exit is-lit">
                                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $checkPath }}" /></svg>
                                            {{ $m['exit'] }}
                                        </span>
                                    </div>
                                </div>
                                <div class="sh-term-foot" dir="auto">
                                    <p>{{ $m['blurb'] }}</p>
                                    <a href="{{ $m['cta'][1] }}" @if ($m['cta'][2]) target="_blank" rel="noopener noreferrer" @endif class="sh-term-btn">
                                        {{ $m['cta'][0] }}
                                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $m['cta'][2] ? $outPath : $arrowPath }}" /></svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach

                        {{-- Scene 2: the wizard, with the fields it really asks for
                             (auth/register.blade.php while selfhost_needs_setup()). The account
                             half stays shut until Test answers "Connection successful". --}}
                        <div class="sh-scene sh-browser" data-sh-scene="2">
                            <h3 class="sh-scene-title">The setup wizard</h3>
                            <div class="sh-browser-bar" dir="ltr">
                                <i></i><i></i><i></i>
                                <span class="sh-url">
                                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    <span><b class="sh-dom" data-sh-domain>{{ $placeDomain }}</b><span class="sh-path">/sign_up</span></span>
                                </span>
                            </div>
                            <div class="sh-browser-body" aria-hidden="true">
                                <p class="sh-wait" dir="ltr">next: open <b class="sh-wait-domain sh-dom" data-sh-domain>{{ $placeDomain }}</b><b class="sh-wait-local">http://localhost:8080</b></p>
                                <div class="sh-wiz">
                                    <div class="sh-wiz-part">
                                        <div class="sh-wiz-cap"><span>Your database</span><span>1 of 2</span></div>
                                        <div class="sh-wiz-grid" dir="ltr">
                                            @foreach ([['MySQL Host', 'localhost', false], ['Port', '3306', false], ['Database', 'eventschedule', false], ['Username', 'eventschedule', false], ['Password', '••••••••••••', true]] as $fIndex => [$fLabel, $fValue, $fWide])
                                                <div class="sh-f{{ $fWide ? ' is-wide' : '' }}">
                                                    <span class="sh-f-label">{{ $fLabel }}</span>
                                                    <span class="sh-f-box"><span style="--i: {{ $fIndex }};">{{ $fValue }}</span></span>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="sh-wiz-row">
                                            <span class="sh-wiz-ok" style="--i: 7;">
                                                <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $checkPath }}" /></svg>
                                                Connection successful
                                            </span>
                                            <span class="sh-wiz-btn" style="--i: 6;">Test</span>
                                        </div>
                                    </div>
                                    <div class="sh-wiz-part is-second" style="--i: 8;">
                                        <div class="sh-wiz-cap"><span>Your account</span><span>2 of 2</span></div>
                                        <div class="sh-wiz-grid" dir="ltr">
                                            <div class="sh-f is-wide">
                                                <span class="sh-f-label">Email</span>
                                                <span class="sh-f-box"><span style="--i: 9;">you@<span class="sh-dom" data-sh-domain>{{ $placeDomain }}</span></span></span>
                                            </div>
                                            <div class="sh-f">
                                                <span class="sh-f-label">Full Name</span>
                                                <span class="sh-f-box"><span style="--i: 10;">Your name</span></span>
                                            </div>
                                            <div class="sh-f">
                                                <span class="sh-f-label">Password</span>
                                                <span class="sh-f-box"><span style="--i: 11;">••••••••••••</span></span>
                                            </div>
                                        </div>
                                        <div class="sh-wiz-terms">
                                            <i><svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $checkPath }}" /></svg></i>
                                            I accept the Terms of Service
                                        </div>
                                        <span class="sh-wiz-btn is-go" style="--i: 13;">Sign Up</span>
                                    </div>
                                </div>
                            </div>
                            <p class="sr-only">The wizard asks for five database values (MySQL Host, Port, Database, Username and Password) and tests them. Once the connection succeeds it asks for your email, full name and a password, then runs the migrations and writes the configuration.</p>
                        </div>

                        {{-- Scene 3: the cron entry. Amber: the one step that is yours. --}}
                        <div class="sh-scene sh-term" data-sh-scene="3" dir="ltr">
                            <h3 class="sh-scene-title">The cron entry</h3>
                            <div class="sh-bar">
                                <i></i><i></i><i></i>
                                <span class="sh-bar-title"><span class="sh-dom" data-sh-domain>{{ $placeDomain }}</span> : crontab</span>
                                <button type="button" class="sh-copy" data-sh-copy="{{ $cronLine }}">Copy</button>
                            </div>
                            <div class="sh-term-body">
                                <div class="sh-line is-cmd" style="--i: 0;"><b>$</b><span>crontab -e</span></div>
                                <div class="sh-line is-own" style="--i: 2;"><b>+</b><span>{{ $cronLine }}</span></div>
                                <div class="sh-line is-note" style="--i: 4;"><b>&nbsp;</b><span># reminder emails, calendar sync and expiring ticket reservations run from this line</span></div>
                                <div class="sh-line is-note" style="--i: 5;"><b>&nbsp;</b><span># on cPanel, add it under Cron Jobs instead of the command line</span></div>
                            </div>
                            <div class="sh-term-foot" dir="auto">
                                <p><strong>One line, once.</strong> This is the step the software cannot do for you. Without it, everything that happens on a timer stops.</p>
                                <span class="sh-own-tag is-lit">Yours to run</span>
                            </div>
                        </div>

                        {{-- Scene 4: it is running. A real screen from the user guide. --}}
                        <div class="sh-scene sh-browser" data-sh-scene="4">
                            <h3 class="sh-scene-title">It is running</h3>
                            <div class="sh-browser-bar" dir="ltr">
                                <i></i><i></i><i></i>
                                <span class="sh-url">
                                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    <span><b class="sh-dom" data-sh-domain>{{ $placeDomain }}</b><span class="sh-path">/admin/dashboard</span></span>
                                </span>
                                <span class="sh-live" style="--i: 2;">
                                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $checkPath }}" /></svg>
                                    running
                                </span>
                            </div>
                            <div class="sh-browser-body">
                                <p class="sh-wait" dir="ltr" aria-hidden="true">next: sign in at <b class="sh-dom" data-sh-domain>{{ $placeDomain }}</b></p>
                                <div class="sh-shot">
                                    <img class="is-day" src="{{ url('/images/docs/selfhost-admin--dashboard.webp') }}" width="1280" height="757" loading="lazy" decoding="async" alt="The admin dashboard of a selfhosted install: new organizers, active users, events added, upcoming events, recent schedules and recent events.">
                                    <img class="is-night" src="{{ url('/images/docs/selfhost-admin--dashboard-dark.webp') }}" width="1280" height="757" loading="lazy" decoding="async" alt="The admin dashboard of a selfhosted install: new organizers, active users, events added, upcoming events, recent schedules and recent events.">
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="sh-step-text" data-sh-step-text></p>
                    <p class="sh-stage-note">The admin screen is from the user guide, with demo data in it.</p>
                </div>

                <div class="sh-stage-links">
                    <button type="button" class="sh-replay" data-sh-replay>
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        <span data-sh-replay-label data-play="Play it again" data-pause="Pause">Play it again</span>
                    </button>
                    <a href="{{ demo_url() }}" target="_blank" rel="noopener noreferrer" class="hp-more sh-more">
                        Open the admin demo
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $outPath }}" /></svg>
                    </a>
                </div>
            </div>

            <script {!! nonce_attr() !!}>
                (function () {
                    var stage = document.querySelector('[data-sh-stage]');
                    if (!stage) return;
                    var method = stage.getAttribute('data-method');
                    Array.prototype.forEach.call(stage.querySelectorAll('[data-sh-scene]'), function (scene) {
                        var step = scene.getAttribute('data-sh-scene');
                        var front = step === '1' && scene.getAttribute('data-sh-panel') === method;
                        var back = step === '2';
                        scene.classList.toggle('is-shown', front || back);
                        scene.classList.toggle('is-front', front);
                        scene.classList.toggle('is-back', back);
                        scene.classList.toggle('is-pending', back);
                    });
                    stage.classList.add('is-ready');
                })();
            </script>

            <!-- What the server needs: a spec sheet rather than four bare tiles -->
            @php
                $requirements = [
                    ['PHP', '8.2 or newer', 'chip', false],
                    ['Database', 'MySQL 5.7+ or MariaDB 10.3+', 'db', false],
                    ['Web server', 'Apache or Nginx with rewrites', 'server', false],
                    // Amber: the certificate is one of the things that is yours to run.
                    ['SSL', 'Required, HTTPS only', 'shield', true],
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
            <div class="sh-req" data-reveal="panel">
                <div class="sh-req-top">
                    <span class="sh-prompt" aria-hidden="true"><span>~/eventschedule</span> $&nbsp;check-requirements</span>
                    <span class="sh-exit">
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $checkPath }}" /></svg>
                        Most shared hosts already pass
                    </span>
                </div>
                <dl class="sh-req-grid">
                    @foreach ($requirements as [$rLabel, $rValue, $rIcon, $rOwn])
                        <div class="sh-req-cell{{ $rOwn ? ' is-own' : '' }}">
                            <dt><span class="sh-req-ico"><svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $reqIcons[$rIcon] }}" /></svg></span>{{ $rLabel }}</dt>
                            <dd>{{ $rValue }}</dd>
                        </div>
                    @endforeach
                </dl>
                <div class="sh-req-ext">
                    <span>PHP extensions:</span>
                    <ul>
                        @foreach ($phpExtensions as $ext)
                            <li>{{ $ext }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- The night: each plan lit in turn, until nothing is dark     -->
    <!-- ============================================================ -->
    @php
        // Sourced from docs/FEATURES.md, which is the reference for every tier: f = Free,
        // p = Pro, e = Enterprise, s = exists only on a selfhosted install. A selfhosted
        // install returns true for both isPro() and isEnterprise(), so its column is all of
        // them. A gate that moves in FEATURES.md moves here in the same change.
        // [label, tier, route name or null, large]
        $wall = [
            ['Paid ticketing', 'p', 'marketing.ticketing', true],
            ['Recurring events', 'f', 'marketing.recurring_events', false],
            ['Allocated seating', 'e', 'marketing.allocated_seating', true],
            ['Promo codes', 'p', null, false],
            ['Calendar sync', 'f', 'marketing.calendar_sync', true],
            ['AI flyers', 'e', null, false],
            ['Stripe and PayPal checkout', 'p', 'marketing.paypal', false],
            ['QR scanning at the door', 'f', null, false],
            ['Team members', 'e', 'marketing.team_scheduling', true],
            ['Gift cards', 'p', 'marketing.gift_cards', false],
            ['Sub-schedules', 'f', 'marketing.sub_schedules', false],
            ['Refunds', 'p', null, false],
            ['Auto import from the web', 's', null, true],
            ['Newsletters', 'f', 'marketing.newsletters', true],
            ['REST API', 'p', null, true],
            ['Private events', 'e', 'marketing.private_events', false],
            ['Passes and subscriptions', 'p', null, false],
            ['Embed on your site', 'f', null, false],
            ['Webhooks', 'p', null, false],
            ['Box office', 'e', null, false],
            ['AI import', 'f', 'marketing.ai', true],
            ['Ticket add-ons', 'p', null, false],
            // The second chip whose words change: the licence credit stays on a selfhosted install.
            ['__label__', 'p', 'marketing.white_label', true],
            ['Free registration', 'f', null, false],
            ['Availability', 'e', null, false],
            ['Installments', 'p', null, false],
            ['Built-in analytics', 'f', 'marketing.analytics', false],
            ['Custom fields', 'p', 'marketing.custom_fields', false],
            ['WhatsApp event creation', 'e', null, false],
            ['Fan videos and photos', 'f', 'marketing.fan_videos', false],
            ['Check-in dashboard', 'p', null, false],
            ['Event polls', 'p', 'marketing.polls', false],
            ['Event graphics', 'f', 'marketing.event_graphics', true],
            ['Feeds', 'e', null, false],
            ['Ticket waitlist', 'p', null, false],
            ['Appointments', 'f', 'marketing.appointments', false],
            ['One-click updates', 's', null, true],
            ['Custom CSS', 'p', null, false],
            ['AI branding', 'e', null, false],
            ['Email subscribers', 'f', null, false],
            ['Per-guest tickets', 'p', null, false],
            ['Event templates', 'p', null, false],
            ['Import from a link', 'f', null, false],
            ['Agenda scanning', 'e', null, false],
            ['Ticket widget', 'p', null, false],
            ['Online events', 'f', null, false],
            ['Paid appointments', 'p', null, false],
            // The one chip whose words change: see the foot of the wall.
            ['__domain__', 'e', null, false],
            ['Backup and restore', 'f', null, false],
            ['Invoice Ninja', 'p', null, false],
            ['Post-event feedback', 'p', null, false],
            ['Calendar feeds', 'f', null, false],
            ['AI descriptions', 'e', null, false],
            ['Sales export', 'p', null, false],
            ['Event requests', 'f', null, false],
            ['Carpools', 'p', null, false],
            ['Scheduled graphic emails', 'e', null, false],
            ['Multi-event cart', 'f', null, false],
            ['Push notifications', 'p', null, false],
            ['Boost with ads', 'p', null, false],
            ['Venue maps', 'f', null, false],
            ['Attendee import', 'p', null, false],
            ['AI captions', 'e', null, false],
            ['Photo gallery', 'p', null, false],
            ['Unlimited events', 'f', null, false],
            ['Sponsor logos', 'p', null, false],
            ['Eventbrite import', 'p', null, false],
            ['Announcement banner', 'p', null, false],
        ];
        $tierCount = array_count_values(array_column($wall, 1));
        $wallOn = [
            'free' => $tierCount['f'],
            'pro' => $tierCount['f'] + $tierCount['p'],
            'ent' => $tierCount['f'] + $tierCount['p'] + $tierCount['e'],
            'self' => count($wall),
        ];

        // The two allowances that grow with the plan are read from the rate card /pricing and
        // /faq print, so this page cannot quote another number. Off the hosted service neither
        // is capped (Role::newsletterLimit(), RoleController::storeMember()).
        $rateRows = collect(\App\Utils\PlanRateCard::rows());
        $rateRow = fn (string $start, array $fallback) => array_slice($rateRows->first(fn ($r) => str_starts_with($r[0], $start)) ?? array_merge([''], $fallback), 1, 3);
        [$mailFree, $mailPro, $mailEnt] = $rateRow('Newsletter emails', ['10', '100', '1,000']);
        [$teamFree, $teamPro, $teamEnt] = $rateRow('Team members', ['1', '1', 'Up to 5']);

        $plans = [
            'free' => ['Free', plan_price(0)],
            'pro' => ['Pro', plan_price($proMonthly)],
            'ent' => ['Enterprise', plan_price($entMonthly)],
            'self' => ['Selfhosted', plan_price(0)],
        ];
    @endphp
    <section id="included" class="hp-dark sh-night">
        <div class="hp-wrap">
            {{-- The page is drawn on its last answer: without the script, and for a visitor who
                 wants no motion, every light is on. The script walks the dial up to it once. --}}
            <div class="sh-unlock" data-sh-unlock data-plan="self">
                <div class="hp-head" data-reveal>
                    <div class="sh-prompt" aria-hidden="true"><span>~/eventschedule</span> $ features --all</div>
                    <h2 class="hp-h2">Every Pro and Enterprise feature. <span class="text-gradient-selfhost">Free.</span></h2>
                    <p class="hp-lead">There is no selfhosted tier to upgrade from. A selfhosted install is treated as Enterprise everywhere in the code, so nothing on this page is behind a paywall.<span class="sh-js-only"> Turn the dial and see what each plan lights up.</span></p>
                </div>

                <div class="sh-lit">
                <div class="sh-dialrow">
                    <div class="sh-dial" role="group" aria-label="Light the features of a plan">
                        <button type="button" data-sh-plan="free" aria-pressed="false"><small>Hosted</small>Free</button>
                        <button type="button" data-sh-plan="pro" aria-pressed="false"><small>Hosted</small>Pro</button>
                        <button type="button" data-sh-plan="ent" aria-pressed="false"><small>Hosted</small>Enterprise</button>
                        <button type="button" data-sh-plan="self" aria-pressed="true"><small>Your server</small>Selfhosted</button>
                    </div>
                </div>

                <dl class="sh-meter">
                    <div>
                        <dt>What it costs</dt>
                        <dd>
                            @foreach ($plans as $pKey => [$pName, $pPrice])
                                @if ($pKey === 'self')
                                    {{-- At rest the read-out carries its own contrast: what the
                                         same features cost hosted, struck through. --}}
                                    <span class="sh-v" data-for="self">{{ $pPrice }} <small>forever</small><small class="sh-was"><span class="sr-only">instead of </span><s>{{ $plans['ent'][1] }} a month</s></small></span>
                                @else
                                    <span class="sh-v" data-for="{{ $pKey }}">{{ $pPrice }} <small>a month</small></span>
                                @endif
                            @endforeach
                        </dd>
                    </div>
                    <div>
                        <dt>Features lit</dt>
                        <dd>
                            @foreach ($wallOn as $pKey => $pOn)
                                <span class="sh-v" data-for="{{ $pKey }}">{{ $pOn }} <small>of {{ count($wall) }}</small></span>
                            @endforeach
                        </dd>
                    </div>
                    <div>
                        <dt>Newsletter emails a month</dt>
                        <dd>
                            <span class="sh-v" data-for="free">{{ $mailFree }}</span>
                            <span class="sh-v" data-for="pro">{{ $mailPro }}</span>
                            <span class="sh-v" data-for="ent">{{ $mailEnt }}</span>
                            <span class="sh-v" data-for="self">No limit</span>
                        </dd>
                    </div>
                    <div>
                        <dt>Team members</dt>
                        <dd>
                            <span class="sh-v" data-for="free">{{ $teamFree }}</span>
                            <span class="sh-v" data-for="pro">{{ $teamPro }}</span>
                            <span class="sh-v" data-for="ent">{{ $teamEnt }}</span>
                            <span class="sh-v" data-for="self">No limit</span>
                        </dd>
                    </div>
                </dl>

                <ul class="sh-wall" aria-label="Features, lit where the chosen plan includes them">
                    @foreach ($wall as $wIndex => [$wLabel, $wTier, $wRoute, $wBig])
                        <li>
                            @if ($wLabel === '__domain__')
                                <span class="sh-chip" data-tier="e" style="--i: {{ $wIndex }};"><span class="is-hosted">Custom domain per schedule</span><span class="is-self">Your own domain, install-wide</span><span class="sr-only sh-chip-off">, not included</span></span>
                            @elseif ($wLabel === '__label__')
                                <a href="{{ route($wRoute) }}" class="sh-chip is-big" data-tier="p" style="--i: {{ $wIndex }};"><span class="is-hosted">White label</span><span class="is-self">White label, bar one small credit</span><span class="sr-only sh-chip-off">, not included</span></a>
                            @elseif ($wRoute)
                                <a href="{{ route($wRoute) }}" class="sh-chip{{ $wBig ? ' is-big' : '' }}" data-tier="{{ $wTier }}" style="--i: {{ $wIndex }};">{{ $wLabel }}<span class="sr-only sh-chip-off">, not included</span></a>
                            @else
                                <span class="sh-chip{{ $wBig ? ' is-big' : '' }}" data-tier="{{ $wTier }}" style="--i: {{ $wIndex }};">{{ $wLabel }}@if ($wTier === 's') <em>Only here</em>@endif<span class="sr-only sh-chip-off">, not included</span></span>
                            @endif
                        </li>
                    @endforeach
                </ul>
                </div>

                <p class="sh-wall-foot">
                    Two things change shape rather than carrying over. A hosted schedule can have a custom domain of its own, and your install already stands on a domain you chose. And white label here keeps one small licence credit, which is the whole of what the software asks.
                </p>

                {{-- True whatever the dial says, hosted or not, so these stand outside it. The
                     third figure this row used to carry is the read-out above. --}}
                @php
                    $numberStats = [
                        ['0%', 'Platform fees on tickets', 'We never take a cut, on any plan'],
                        [(string) count(config('app.supported_languages')), 'Languages built in', 'Your guest pages, translated'],
                    ];
                @endphp
                <div class="sh-also" data-reveal>
                    @foreach ($numberStats as [$statValue, $statLabel, $statNote])
                        <p><b>{{ $statValue }}</b><span><strong>{{ $statLabel }}</strong>{{ $statNote }}</span></p>
                    @endforeach
                    <a href="{{ route('marketing.features') }}" class="hp-btn hp-btn-ghost is-small">
                        See the full feature list
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $arrowPath }}" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Only on your own server: auto import, one-click updates     -->
    <!-- ============================================================ -->
    <section id="updates" class="hp-sec">
        <div class="hp-wrap">
            <div class="hp-head" data-reveal>
                <div class="sh-prompt" aria-hidden="true"><span>~/eventschedule</span> $ app:update</div>
                <h2 class="hp-h2">Two features exist <span class="text-gradient-selfhost">only here</span></h2>
                <p class="hp-lead">The hosted service has neither. On your own server both are part of the install, and one of them is how the install stays current.</p>
            </div>

            <div class="sh-only-grid" data-reveal-group="110">
                {{-- ImportCuratorEvents reads the configured URLs only, once a day from the
                     scheduler. Configured cities are a filter on what those pages return, not a
                     search. The panel's labels are the schedule form's own (Auto Import). --}}
                <article class="sh-only" data-reveal="panel">
                    <span class="sh-only-tag">Selfhost only</span>
                    <h3 class="hp-h3">Auto import from the web</h3>
                    <p>Point Event Schedule at a list of URLs and AI pulls the events in once a day, keeping only the cities you name. It checks each site's robots.txt first.</p>
                    <div class="sh-app-push"></div>
                    <div class="sh-app" data-sh-import-box>
                        <div class="sh-app-top"><strong>Auto Import</strong><button type="button" class="sh-app-btn" data-sh-import>Test Import</button></div>
                        <div class="sh-app-body">
                            <div class="sh-app-cap">Import URLs</div>
                            <div class="sh-app-rows" dir="ltr">
                                <div class="sh-app-row"><span>thevenue.com/gigs</span></div>
                                <div class="sh-app-row"><span>jazzclub.example/calendar</span></div>
                            </div>
                            <div class="sh-app-cap" style="margin-top: 0.85rem;">Import Cities</div>
                            <div class="sh-app-tags"><span>Leeds</span><span>York</span></div>
                            <ul class="sh-app-found" aria-label="What a test run brought in">
                                @foreach ([['Event', 'Friday Night Jazz'], ['Date', 'Fri 14 Aug, 8:00 PM'], ['Venue', 'The Blue Room, Leeds']] as $afi => $af)
                                    <li style="--i: {{ $afi }};"><span>{{ $af[0] }}</span><span>{{ $af[1] }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <p class="sh-cap">The Auto Import panel of the schedule form, with example addresses and an example result in it.</p>
                </article>

                {{-- The real screen at /admin/app-update: its three figures, its mark, its
                     warning and its two buttons, in its own words (lang/en/messages.php). Update
                     can be pressed here, which is the whole of what it asks of an operator. --}}
                <article class="sh-only" data-reveal="panel">
                    <span class="sh-only-tag">Selfhost only</span>
                    <h3 class="hp-h3">Updates are one click, not one afternoon</h3>
                    <p>When a release lands, a notice appears in your admin panel. Click it and the update applies in seconds, migrations included. No SSH session, no maintenance window.</p>
                    <div class="sh-app-push"></div>
                    <div class="sh-app sh-up" data-sh-update data-state="ready">
                        <div class="sh-up-stats" dir="ltr">
                            <div><b class="is-before">{{ $installedVersion }}</b><b class="is-after">{{ $nextVersion }}</b><span>Installed Version</span></div>
                            <div><b>{{ $nextVersion }}</b><span>Latest Version</span></div>
                            <div><b class="is-before" data-sh-update-checked>2 minutes ago</b><b class="is-after">just now</b><span>Last Checked</span></div>
                        </div>
                        <div class="sh-app-top">
                            <strong>App Update</strong>
                            <span class="sh-up-mark"><span class="is-before">Update available</span><span class="is-after">You're up to date!</span></span>
                        </div>
                        <div class="sh-app-body">
                            <div class="sh-up-warn">
                                <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                                <span>This downloads the new release, installs it and runs any new database migrations. Take a backup first.</span>
                            </div>
                            <span class="sr-only" role="status" data-sh-update-say data-done="Release installed, migrations run."></span>
                            <div class="sh-up-done">
                                <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $checkPath }}" /></svg>
                                <span>Release installed, migrations run. That was the whole update.</span>
                            </div>
                            <div class="sh-up-actions">
                                <button type="button" class="sh-app-btn is-before" data-sh-update-check>Check for Updates</button>
                                <button type="button" class="sh-up-go is-before" data-sh-update-go><span class="is-idle">Update</span><span class="is-busy">Updating</span><i aria-hidden="true"></i></button>
                                <button type="button" class="sh-up-again is-after" data-sh-update-reset>Press it again</button>
                                <span class="sh-up-hint is-before">Go on, press it.</span>
                            </div>
                        </div>
                    </div>
                    <p class="sh-cap">The App Update screen, with the installed version live from this release. The real button asks before it runs.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- The control room: the operator's own screens                -->
    <!-- ============================================================ -->
    @php
        // Real screens from the user guide (public/images/docs, light and dark). Each line is
        // the screen's own lead, so the page says what the app says. Only screens that show the
        // thing working are here (the guide's Logs screen is an empty log), and the dashboard is
        // left out because the install stage above ends on it.
        // The last three numbers are for a phone, where the frame shows 496px of the 1280px
        // picture: where that cut starts (x, y), and which edge it is cut on mid-content and so
        // fades. Queue is cut from the right so that "last tick" is in it.
        $roomScreens = [
            ['queue', 'Queue', '/admin/queue', 'Whether the scheduler is running, how much work it still has to get through, and the jobs in the queue. This is where you see your cron entry tick.', 'The Queue screen: a Scheduler card reading last tick 1 minute ago, and the work waiting.', 768, 272, 'left'],
            ['audit-log', 'Audit log', '/admin/audit-log', 'Sign-ins, changes and payments across the installation, and who made them, newest first.', 'The Audit Log screen: totals, filters and a table of sign-ins by time, user, action and IP address.', 304, 250, 'right'],
            ['realtime', 'Realtime', '/admin/realtime', 'Who is on your install right now and the page views by the minute. Off until you switch it on in Settings.', 'The Realtime screen: visitors right now, where they are, and page views per minute.', 304, 200, 'right'],
        ];
    @endphp
    <section id="demo" class="hp-sec hp-alt">
        <div class="hp-wrap">
            <div class="hp-head" data-reveal>
                <div class="sh-prompt" aria-hidden="true"><span>~/eventschedule</span> $ open /admin</div>
                <h2 class="hp-h2">The control room <span class="text-gradient-selfhost">is yours too</span></h2>
                <p class="hp-lead">Whoever runs the install gets an operator's panel at /admin. The stage above ended on its dashboard. These are three more of its real screens, taken from the user guide.</p>
            </div>

            {{-- Without the script the screens stand one under the other, each under its
                 own heading; with it the list becomes tabs over one frame. --}}
            <div class="sh-room" data-sh-room>
                <div class="sh-room-side">
                <div class="sh-room-list" role="tablist" aria-label="Admin screens" aria-orientation="vertical">
                    @foreach ($roomScreens as $rIndex => [$rKey, $rName, $rPath, $rLine, $rAlt, $rX, $rY, $rFade])
                        <button type="button" class="sh-room-tab" role="tab" id="room-tab-{{ $rKey }}" aria-controls="room-{{ $rKey }}" aria-selected="{{ $rIndex === 0 ? 'true' : 'false' }}" tabindex="{{ $rIndex === 0 ? '0' : '-1' }}">
                            <strong>{{ $rName }}</strong>
                            <span>{{ $rLine }}</span>
                        </button>
                    @endforeach
                </div>
                <div class="sh-try">
                    <p><strong>Try it before you install anything</strong>Both halves of what you will be running: the admin portal you manage events in, and the public calendar your attendees see.</p>
                    <div class="sh-try-actions">
                        <a href="https://simpsons.eventschedule.com" target="_blank" rel="noopener noreferrer" class="hp-btn hp-btn-ghost is-small is-still">
                            Guest demo
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $outPath }}" /></svg>
                        </a>
                        <a href="{{ demo_url() }}" target="_blank" rel="noopener noreferrer" class="hp-btn hp-btn-own is-small is-still">
                            Admin demo
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $outPath }}" /></svg>
                        </a>
                    </div>
                </div>
                </div>

                <div class="sh-frame" data-reveal="panel">
                    @foreach ($roomScreens as $rIndex => [$rKey, $rName, $rPath, $rLine, $rAlt, $rX, $rY, $rFade])
                        <div class="sh-room-panel" role="tabpanel" id="room-{{ $rKey }}" aria-labelledby="room-tab-{{ $rKey }}">
                            <div class="sh-browser-bar" dir="ltr">
                                <i></i><i></i><i></i>
                                <span class="sh-url">
                                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    <span><b class="sh-dom" data-sh-domain>{{ $placeDomain }}</b>{{ $rPath }}</span>
                                </span>
                            </div>
                            <div class="sh-shot is-cut-{{ $rFade }}" style="--px: {{ $rX }}; --py: {{ $rY }};">
                                <img class="is-day" src="{{ url('/images/docs/selfhost-admin--'.$rKey.'.webp') }}" width="1280" height="757" loading="lazy" decoding="async" alt="{{ $rAlt }}">
                                <img class="is-night" src="{{ url('/images/docs/selfhost-admin--'.$rKey.'-dark.webp') }}" width="1280" height="757" loading="lazy" decoding="async" alt="{{ $rAlt }}">
                            </div>
                            <p class="sh-room-line">{{ $rLine }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </section>

    <!-- ============================================================ -->
    <!-- What you own: the data, the one bridge out, and the upkeep  -->
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
    <section id="data" class="hp-sec">
        <div class="hp-wrap">
            <div class="hp-head" data-reveal>
                <div class="sh-prompt" aria-hidden="true"><span>~/eventschedule</span> $ whoami</div>
                <h2 class="hp-h2">The data is <span class="text-gradient-selfhost">yours</span>, and so is the upkeep</h2>
                <p class="hp-lead">Nothing is shipped anywhere by default. Here is exactly what stays with you, what you can choose to share, and what you are on the hook for.</p>
            </div>

            <div class="sh-own-grid" data-reveal-group="110">
                <!-- Data ownership -->
                <div class="sh-card is-yours" data-reveal="panel">
                    <h3 class="hp-h3">It never leaves your server</h3>
                    <p>Your events, attendees, ticket sales and follower emails live in your database. Event Schedule cannot access, modify or remove selfhosted data, because there is no connection back to us to do it with.</p>
                    <ul class="sh-ticks">
                        @foreach (['Your database, your backups, your retention rules', 'Stripe and PayPal payments land in your own accounts', 'Your own Gemini or OpenAI key for the AI features', 'No telemetry, no phone-home, no usage reporting'] as $dItem)
                            <li>
                                <i aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $checkPath }}" /></svg></i>
                                <span>{{ $dItem }}</span>
                            </li>
                        @endforeach
                    </ul>
                    {{-- The FAQ's own sentence, drawn: two optional features send data to
                         eventschedule.com, and both start off. --}}
                    <div class="sh-leave">
                        <p>Two optional features can send data to eventschedule.com. Both start off.</p>
                        <ul>
                            <li><span><strong>Federation</strong>Your public events, once an admin switches it on.</span><span class="sh-state" data-sh-fed-state>off</span></li>
                            <li><span><strong>Translation sharing</strong>Wording you corrected, when an admin presses Share.</span><span class="sh-state">off</span></li>
                        </ul>
                    </div>
                </div>

                <!-- Federation: a switch, because "off by default" is the whole point and
                     prose kept burying it. It starts off, as it does on a new install. -->
                <div class="sh-card" data-reveal="panel">
                    <span class="sh-only-tag">Opt in, off by default</span>
                    <h3 class="hp-h3">Or share out, on your terms</h3>
                    <p>Federation is a bridge that carries your events to eventschedule.com, and it only exists if an admin switches it on. Your public events appear in the main listings, and every listing links back to the event on your own site.</p>

                    <div class="sh-fed" data-sh-fed data-on="0">
                        <div class="sh-fed-top">
                            <span id="sh-fed-label">Federation<small>A demonstration. Flip it to see what would be shared.</small></span>
                            <button type="button" class="sh-switch" role="switch" aria-checked="false" aria-labelledby="sh-fed-label" data-sh-fed-switch></button>
                        </div>
                        {{-- Three example events, so the rule can be seen: only the public one
                             crosses. --}}
                        <div class="sh-fed-map" aria-hidden="true">
                            <div class="sh-fed-node is-you">
                                <strong>Your server</strong><span class="sh-dom" dir="ltr" data-sh-domain>{{ $placeDomain }}</span>
                                <ul>
                                    <li class="is-public">Friday Night Jazz<em>public</em></li>
                                    <li>Members' preview<em>unlisted</em></li>
                                    <li>Spring line-up<em>draft</em></li>
                                </ul>
                            </div>
                            <div class="sh-fed-rail"></div>
                            <div class="sh-fed-node is-them">
                                <strong>Listings</strong><span dir="ltr">eventschedule.com</span>
                                <ul>
                                    <li class="is-off">Nothing of yours</li>
                                    <li class="is-on is-public">Friday Night Jazz<em>links back</em></li>
                                </ul>
                            </div>
                        </div>
                        <p class="sh-fed-say" aria-live="polite">
                            <span class="is-off">Off. Nothing is sent, and nothing about your install is listed anywhere.</span>
                            <span class="is-on">On. Once eventschedule.com has approved your install, public events of the schedules that said yes are listed, and every listing links back to your site.</span>
                        </p>
                    </div>

                    <ul class="sh-dots">
                        @foreach (['Only public events, never drafts, internal or unlisted ones', 'Enabled by an admin at /admin/settings, never automatically', 'Each schedule is listed only if someone who manages it says yes'] as $fItem)
                            <li><span>{{ $fItem }}</span></li>
                        @endforeach
                    </ul>
                    <p style="margin-top: 1.25rem;">
                        <a href="{{ route('marketing.docs.selfhost.federation') }}" class="hp-more sh-more">
                            Read the federation guide
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $arrowPath }}" /></svg>
                        </a>
                    </p>
                </div>
            </div>

            <!-- The amber half: what you are responsible for. Without the script it is the
                 list it always was; with it each line can be ticked, and six ticks turn it green. -->
            <div class="sh-pre" id="preflight" data-sh-pre data-ready="0" data-reveal="panel">
                <div class="sh-pre-top">
                    <div>
                        <span class="sh-own-tag is-lit">Yours to run</span>
                        <h3 class="hp-h3">Six things nobody does for you</h3>
                        <p>Selfhosting means you are the operator. It is not much, but it is honest to say it out loud.<span class="sh-pre-ask"> Tick what you already have covered.</span></p>
                    </div>
                    <p class="sh-pre-count" aria-live="polite" aria-atomic="true"><b data-sh-pre-count>0</b> of {{ count($responsibilities) }} covered</p>
                </div>
                <ul class="sh-pre-list">
                    @foreach ($responsibilities as [$rTitle, $rBody])
                        <li>
                            <button type="button" class="sh-pre-item" aria-pressed="false" data-sh-pre-item>
                                <span class="sh-pre-box" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $checkPath }}" /></svg></span>
                                <span><strong>{{ $rTitle }}</strong><span class="sh-pre-text">{{ $rBody }}</span></span>
                            </button>
                        </li>
                    @endforeach
                </ul>
                <div class="sh-pre-foot">
                    <p class="is-no"><strong>Not all six yet?</strong> The hosted version is free to start, and backup and restore is built in for the day you move.</p>
                    <p class="is-yes"><strong>All six covered.</strong> You are ready to run it yourself.</p>
                    <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-onnight is-small is-no">Try hosted</a>
                    <a href="#install" class="hp-btn hp-btn-own is-small is-down is-yes">
                        Install it
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Should you: hosted beside selfhosted                        -->
    <!-- ============================================================ -->
    @php
        // Two weighted cards, not a table: the selfhost card carries the emphasis the way
        // /pricing emphasises Pro. Amber stays out of it - the operator-responsibility rule is
        // spent on the sheet above, and reusing it here would read as a warning rather than a
        // comparison. Plan prices come from the marketing.* view composer as $proMonthly /
        // $entMonthly, so this page and /pricing can never quote different numbers.
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
                    ['Support', 'GitHub issues'],
                ],
                'verdict' => 'Right when the data has to be yours, or you are building on top of it.',
            ],
        ];
    @endphp
    <section id="compare" class="hp-sec hp-alt">
        <div class="hp-wrap">
            <div class="hp-head is-center" data-reveal>
                <div class="sh-prompt" aria-hidden="true"><span>~/eventschedule</span> $ diff hosted selfhost</div>
                <h2 class="hp-h2">Should you actually <span class="text-gradient-selfhost">selfhost?</span></h2>
                <p class="hp-lead">Honestly, most people should not. Selfhosting earns its keep when the data has to live on your own infrastructure, or when you are building something on top of it.</p>
            </div>

            <div class="sh-vs" data-sh-vs data-pick="" data-reveal-group="100">
                @foreach ($comparison as $col)
                    <div class="sh-vs-card{{ $col['lead'] ? ' is-lead' : '' }}" data-sh-vs-card="{{ $col['lead'] ? 'self' : 'hosted' }}" data-reveal>
                        @if ($col['lead'])
                            <span class="sh-here is-none">You are here</span>
                            <span class="sh-here is-pick">By your own count</span>
                        @else
                            <span class="sh-here is-pick">Start here, for now</span>
                        @endif
                        <div class="sh-vs-top">
                            <h3 class="hp-h3">{{ $col['title'] }}</h3>
                            <span class="{{ $col['lead'] ? 'sh-exit' : 'hp-tag' }}">{{ $col['chip'] }}</span>
                        </div>
                        <dl class="sh-vs-rows">
                            @foreach ($col['rows'] as [$aspect, $detail])
                                <div>
                                    <dt>{{ $aspect }}</dt>
                                    <dd>{{ $detail }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        <div class="sh-vs-verdict"><p>{{ $col['verdict'] }}</p></div>
                    </div>
                @endforeach
            </div>

            <div class="sh-vs-foot" data-reveal>
                <a href="#preflight" class="sh-count" data-sh-verdict-chip><b data-sh-verdict-n>0</b> of 6 covered</a>
                <p data-sh-verdict>
                    <span class="is-none">Tick the six above and the mark moves. Not sure? The hosted version is free to start.</span>
                    <span class="is-some">By your own count: start hosted, and move over when the rest is covered.</span>
                    <span class="is-all">By your own count, you are ready to selfhost.</span>
                </p>
                <a href="{{ marketing_url('/pricing') }}" class="hp-btn hp-btn-ghost is-small">Compare plans</a>
                <a href="{{ app_url('/sign_up') }}" class="hp-more sh-more">
                    Try hosted
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $arrowPath }}" /></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- White-label SaaS: the same install, one switch on           -->
    <!-- ============================================================ -->
    @php
        // The four names are the homepage's own mock-up schedules. What a customer is charged
        // is the operator's to set, so no figure stands here.
        $tenants = [
            ['blue-note', 'Pro', true],
            ['cellar-club', 'Free', false],
            ['stillpoint', 'Enterprise', true],
            ['riverside-fest', 'Pro', true],
        ];
    @endphp
    <section id="saas" class="hp-sec">
        <div class="hp-wrap">
            <div class="sh-saas">
                <div data-reveal="left">
                    <div class="sh-prompt" aria-hidden="true"><span>~/eventschedule</span> $ IS_HOSTED=true</div>
                    <h2 class="hp-h2">Or turn it into <span class="text-gradient-selfhost">your own product</span></h2>
                    <p class="hp-lead">The same install runs in multi-tenant mode. Give every customer a subdomain, set your own prices and bill them through your Stripe account, where only Stripe's own fee comes off.</p>
                    <ul class="sh-ticks">
                        {{-- "your own prices", not "your own tiers": the tier names are Free,
                             Pro and Enterprise in code; what you supply is the Stripe Price IDs. --}}
                        @foreach (['Multi-tenant subdomains built in', 'Stripe subscription billing at your own prices', 'White-label branding, bar one small licence credit', 'No licence fee and no revenue share'] as $item)
                            <li>
                                <i aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $checkPath }}" /></svg></i>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="hp-actions">
                        <a href="{{ marketing_url('/saas') }}" class="hp-btn hp-btn-own">
                            See the white-label setup
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $arrowPath }}" /></svg>
                        </a>
                    </div>
                </div>

                {{-- Drawn switched on: without the script the customers are simply there. The
                     script switches it off, and on again the first time it is seen. --}}
                <div class="sh-board" data-sh-board data-on="1" data-reveal="right">
                    <div class="sh-env" dir="ltr">
                        <span>IS_HOSTED=<b><span class="is-off">false</span><span class="is-on">true</span></b></span>
                        <button type="button" class="sh-switch" role="switch" aria-checked="true" aria-label="Multi-tenant mode" data-sh-board-switch></button>
                    </div>
                    <div class="sh-tenants" dir="ltr" aria-hidden="true">
                        <div class="sh-tenant is-solo">
                            <span><b class="sh-dom" data-sh-domain>{{ $placeDomain }}</b></span>
                            <small><span class="is-off">Your schedules</span><span class="is-on">Your platform</span></small>
                        </div>
                        @foreach ($tenants as $tIndex => [$tName, $tPlan, $tPaid])
                            <div class="sh-tenant" style="--i: {{ $tIndex }};">
                                <span><b>{{ $tName }}</b>.<wbr><span class="sh-dom" data-sh-domain>{{ $placeDomain }}</span></span>
                                <small class="{{ $tPaid ? 'is-paid' : '' }}">{{ $tPlan }}</small>
                            </div>
                        @endforeach
                    </div>
                    <p class="sh-board-say">
                        <span class="is-off">One install, one operator: every schedule lives at your address.</span>
                        <span class="is-on">One install, many customers: each on a subdomain of yours, on a plan you priced.</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- The manual, and the source                                  -->
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
                    ['Google Wallet passes', 'marketing.docs.selfhost.google_wallet'],
                ],
            ],
            [
                'heading' => 'Operations',
                'links' => [
                    ['Admin panel', 'marketing.docs.selfhost.admin'],
                    ['Federation', 'marketing.docs.selfhost.federation'],
                    ['Boost ads', 'marketing.docs.selfhost.boost'],
                    ['Accessibility', 'marketing.docs.selfhost.accessibility'],
                ],
            ],
        ];
    @endphp
    <section id="docs" class="hp-sec hp-alt">
        <div class="hp-wrap">
            <div class="sh-shelf" data-reveal-group="110">
                <div class="sh-man" data-reveal="panel">
                    <div class="sh-prompt" aria-hidden="true"><span>~/eventschedule</span> $ man selfhost</div>
                    <h2 class="hp-h3">The whole manual, <span class="text-gradient-selfhost">in one place</span></h2>
                    <div class="sh-man-cols">
                        @foreach ($docGroups as $docGroup)
                            <div>
                                <h3>{{ $docGroup['heading'] }}</h3>
                                <ul>
                                    @foreach ($docGroup['links'] as [$docLabel, $docRoute])
                                        <li>
                                            <a href="{{ route($docRoute) }}">
                                                {{ $docLabel }}
                                                <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $arrowPath }}" /></svg>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div id="source" class="sh-src" data-reveal="panel">
                    <svg class="sh-src-mark" aria-hidden="true" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $ghPath }}"/></svg>
                    <h2 class="hp-h3"><span class="sh-nw">Read it,</span> <span class="sh-nw">change it,</span> <span class="sh-nw text-gradient-selfhost">fork it</span></h2>
                    <p>Event Schedule is open source under the Attribution Assurance License. Inspect the code, send a pull request, or take it in your own direction. The AAL asks only that the original attribution stays in place.</p>
                    <div class="sh-src-star">
                        @include('marketing.partials.github-star-badge')
                    </div>
                    <div class="sh-src-links">
                        @foreach ([['Main repository', 'https://github.com/eventschedule/eventschedule'], ['Docker files', 'https://github.com/eventschedule/dockerfiles'], ['Issues', 'https://github.com/eventschedule/eventschedule/issues']] as [$repoLabel, $repoUrl])
                            <a href="{{ $repoUrl }}" target="_blank" rel="noopener noreferrer">
                                {{ $repoLabel }}
                                <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $outPath }}" /></svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- FAQ                                                         -->
    <!-- ============================================================ -->
    @php
        $selfhostFaqs = [
            ['q' => 'Is Event Schedule really free to selfhost?', 'a' => 'Yes. Event Schedule is open source under the Attribution Assurance License. There is no licence fee, no per-event charge and no platform fee on ticket sales. Your only costs are the server and the processing fees your payment provider charges.'],
            ['q' => 'Do I get the paid features when I selfhost?', 'a' => 'A selfhosted install is treated as Enterprise throughout the code, so ticketing, team members, the API, AI generation and everything else are included at no cost. Two features, auto import from URLs and one-click app updates, exist only on selfhosted installs. Per-schedule custom domains are the one thing that does not carry over, because they belong to hosted mode and your install already runs on a domain you chose.'],
            ['q' => 'What do I need on the server?', 'a' => 'PHP 8.2 or newer with the usual extensions, MySQL 5.7+ or MariaDB 10.3+, Apache or Nginx with rewrites enabled, and an SSL certificate. Most shared hosts already meet this.'],
            ['q' => 'Can I install it on shared hosting?', 'a' => 'Yes. If your host offers Softaculous, Event Schedule installs in one click with the database and configuration set up for you. Otherwise upload the release zip and point your document root at the public directory.'],
            ['q' => 'How do updates work?', 'a' => 'When a new version is released, a notice appears in your admin panel. One click applies the update in seconds, database migrations included. No terminal access is required.'],
            ['q' => 'Do I have to set up a cron job?', 'a' => 'Yes, one line: "* * * * * php /path/to/eventschedule/artisan schedule:run". It drives reminder emails, calendar sync and the release of expired ticket reservations. Without it those stop running.'],
            ['q' => 'Which payment methods work on a selfhosted install?', 'a' => 'Stripe, PayPal, Payfast (for rand), Invoice Ninja, a payment link and cash, with no platform fee on any of them. Stripe runs on the platform keys in your .env, which is the only Stripe rail a selfhost has. PayPal can be one account for the whole install, set with PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET, or each schedule owner can connect their own in Settings > Payment Methods. PayPal never has to call your server for an ordinary sale, so only its optional webhook needs a public address. A Stripe or PayPal sale can be refunded from the Sales page, in full or in part, and the money goes back through the provider.'],
            ['q' => 'Which features need my own accounts or keys?', 'a' => 'Anything that talks to another service needs your own account with it, and that part does nothing until you add the credentials: an SMTP service for email, Stripe or PayPal for payments, a Google or Microsoft app for calendar sync, a Gemini or OpenAI key for the AI features, a Google Wallet issuer account for the Add to Google Wallet button on tickets, and a OneSignal app for push notifications.'],
            ['q' => 'Does a selfhosted install send anything back to Event Schedule?', 'a' => 'Not unless an admin switches something on. There is no telemetry and no phone-home. Two optional features do send data to eventschedule.com, and both start off. Federation shares only your public events into the eventschedule.com listings, with every listing linking back to your own site, and a schedule is only listed once someone who manages it says yes. Translation sharing sends wording you corrected in the translation manager, when an admin presses Share or, with automatic sharing on, as it is saved.'],
            ['q' => 'Can I run it as a white-label SaaS for my own customers?', 'a' => 'Yes. Set IS_HOSTED=true and the same install runs multi-tenant, with a subdomain per customer, Stripe subscription billing and your own prices on the Pro and Enterprise tiers. You set the prices and keep the revenue. One thing to know before you price it: the licence credit stays on the public pages of every customer you charge. It is a small chip in the corner, a free schedule carries your own footer strip in its place, and it is the whole of what the software costs you.'],
            ['q' => 'Can I move from the hosted version to selfhosted?', 'a' => 'Yes. Backup and restore is built in, so you can export your schedule data, with images if you want them, and import it into your own install.'],
        ];
    @endphp
    <x-seo.faq-schema :items="$selfhostFaqs" />
    <x-marketing.hp-faq id="faq" :items="$selfhostFaqs" lead="Everything people ask before they install.">Frequently asked <span class="text-gradient-selfhost">questions</span></x-marketing.hp-faq>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- Finale                                                      -->
    <!-- ============================================================ -->
    <section id="claim" class="hp-sec">
        <div class="hp-wrap">
            <div class="hp-finale is-own" data-reveal="panel">
                <div class="sh-prompt" aria-hidden="true" style="margin-bottom: 1.25rem;"><span>~/eventschedule</span> $ ./start</div>
                <h2 class="hp-h2">
                    <span class="sh-dom" data-sh-domain data-sh-default="Your server">Your server</span> is <span class="text-gradient-selfhost">waiting</span>
                </h2>
                <p class="hp-lead">
                    Pick an install method and you can be running your own event platform this afternoon.
                </p>
                {{-- What the visitor did on the way down, said back: the way in they pressed and
                     the items they ticked (the address they typed is the heading). Empty until
                     they did. --}}
                <ul class="sh-recap" data-sh-recap hidden>
                    <li data-sh-recap-method hidden></li>
                    <li data-sh-recap-pre hidden></li>
                </ul>
                <div class="hp-actions" style="justify-content: center;">
                    <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-onnight">Or try the hosted version</a>
                    <a href="{{ route('marketing.docs.selfhost.installation') }}" class="hp-btn hp-btn-own" data-sh-finale-go="">
                        Read the installation guide
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $arrowPath }}" /></svg>
                    </a>
                    {{-- The same three links the stage's terminals carry, for a visitor who pressed one. --}}
                    @foreach ($installMethods as $m)
                        <a href="{{ $m['cta'][1] }}" @if ($m['cta'][2]) target="_blank" rel="noopener noreferrer" @endif class="hp-btn hp-btn-own" data-sh-finale-go="{{ $m['key'] }}" data-label="{{ $m['label'] }}" hidden>
                            {{ $m['cta'][0] }}
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $m['cta'][2] ? $outPath : $arrowPath }}" /></svg>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>


    <!-- The page's own behaviour: plain DOM script, no inline handlers. Every part of the page
         is whole without it; each block below only adds the part that moves. -->
    <script {!! nonce_attr() !!}>
        (function () {
            var root = document.getElementById('hp');
            if (!root) return;

            // Set in the head as well; said again here for a page whose head script did not run.
            root.classList.add('sh-js');
            if (!navigator.clipboard) root.classList.add('sh-no-clip');

            // The same gate the page's styles use: set in the head, only where motion is welcome.
            var motion = document.documentElement.classList.contains('es-anim');

            function all(selector, scope) { return Array.prototype.slice.call((scope || root).querySelectorAll(selector)); }
            function one(selector, scope) { return (scope || root).querySelector(selector); }

            // Run once, when the top of an element has come `rise` of the way up the window.
            function seen(element, rise, run) {
                if (!element || !('IntersectionObserver' in window)) { run(); return; }
                var watcher = new IntersectionObserver(function (entries) {
                    for (var i = 0; i < entries.length; i++) {
                        if (entries[i].isIntersecting) { watcher.disconnect(); run(); return; }
                    }
                }, { rootMargin: '0px 0px -' + rise + '% 0px', threshold: 0 });
                watcher.observe(element);
            }

            // Arrow keys along a row of tabs.
            function arrows(event, index, count, go) {
                var next = null;
                if (event.key === 'ArrowRight' || event.key === 'ArrowDown') next = (index + 1) % count;
                if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') next = (index - 1 + count) % count;
                if (event.key === 'Home') next = 0;
                if (event.key === 'End') next = count - 1;
                if (next === null) return;
                event.preventDefault();
                go(next);
            }

            // ---- What the visitor did on the way down, said back by the last panel and by the
            //      comparison: the address they typed, the way in they pressed, what they ticked.
            var visit = { domain: '', method: '', methodLabel: '', covered: null, of: 0 };

            function recall() {
                var list = one('[data-sh-recap]');
                if (list) {
                    var chips = [
                        [one('[data-sh-recap-method]', list), visit.methodLabel ? visit.methodLabel + ' install' : ''],
                        [one('[data-sh-recap-pre]', list), visit.covered === null ? '' : visit.covered + ' of ' + visit.of + ' covered'],
                    ];
                    var any = false;
                    chips.forEach(function (chip) {
                        if (!chip[0]) return;
                        chip[0].textContent = chip[1];
                        chip[0].hidden = !chip[1];
                        any = any || !!chip[1];
                    });
                    list.hidden = !any;
                }
                all('[data-sh-finale-go]').forEach(function (link) {
                    link.hidden = link.getAttribute('data-sh-finale-go') !== visit.method;
                });

                var versus = one('[data-sh-vs]');
                var verdict = one('[data-sh-verdict]');
                if (versus && verdict && visit.covered !== null) {
                    var ready = visit.covered === visit.of;
                    versus.setAttribute('data-pick', ready ? 'self' : 'hosted');
                    verdict.setAttribute('data-state', ready ? 'all' : 'some');
                    all('[data-sh-verdict-n]').forEach(function (count) { count.textContent = visit.covered; });
                    var chip = one('[data-sh-verdict-chip]');
                    if (chip) chip.classList.toggle('is-all', ready);
                }
            }

            // ---- The domain: typed once, shown on every screen of the page ----------------
            (function () {
                var input = one('[data-sh-domain-input]');
                var hint = one('[data-sh-domain-hint]');
                var spots = all('[data-sh-domain]');
                if (!input) return;

                spots.forEach(function (spot) {
                    if (!spot.hasAttribute('data-sh-default')) spot.setAttribute('data-sh-default', spot.textContent);
                });
                var untouched = hint ? hint.textContent : '';

                // A host and nothing else: no scheme, no path, nothing a browser would not
                // take in an address. It is only ever written back as text.
                function clean(value) {
                    return String(value || '').trim().toLowerCase()
                        .replace(/^[a-z][a-z0-9+.-]*:\/\//, '')
                        .replace(/[\/?#\s].*$/, '')
                        .replace(/:\d*$/, '')
                        .replace(/[^a-z0-9.-]/g, '')
                        .replace(/\.{2,}/g, '.')
                        .replace(/^[.-]+/, '')
                        .slice(0, 48);
                }

                function show(value) {
                    var domain = clean(value);
                    spots.forEach(function (spot) { spot.textContent = domain || spot.getAttribute('data-sh-default'); });
                    root.classList.toggle('sh-has-domain', !!domain);
                    visit.domain = domain;
                    recall();
                    if (!hint) return;
                    if (!domain) { hint.textContent = untouched; return; }
                    hint.textContent = hint.getAttribute('data-typed') || '';
                    var name = document.createElement('b');
                    name.textContent = domain;
                    hint.appendChild(name);
                    hint.appendChild(document.createTextNode('.'));
                }

                input.addEventListener('input', function () { show(input.value); });
                input.addEventListener('keydown', function (event) {
                    if (event.key !== 'Enter') return;
                    event.preventDefault();
                    input.blur();
                    var go = one('[data-sh-go]');
                    if (go) go.click();
                });
                // A browser puts the field's value back on a return visit.
                if (input.value) show(input.value);
            })();

            // ---- The stage: two windows, four steps, played once -------------------------
            (function () {
                var stage = one('[data-sh-stage]');
                if (!stage) return;

                var tabs = all('[data-sh-method]', stage);
                var scenes = all('[data-sh-scene]', stage);
                var beats = all('[data-sh-beat]', stage);
                var again = one('[data-sh-replay]', stage);
                var againLabel = one('[data-sh-replay-label]', stage);
                var desk = one('[data-sh-window]', stage);
                var caption = one('[data-sh-step-text]', stage);
                var method = stage.getAttribute('data-method');
                var timer = null;
                // Once somebody has pressed anything here, the stage is theirs: the film that
                // waits for the window to come into view does not start over them.
                var touched = false;
                // How long each step holds the stage before the cut to the next.
                var holds = { 1: 3600, 2: 6000, 3: 3800 };

                function number(scene) { return +scene.getAttribute('data-sh-scene'); }

                function sceneOf(step) {
                    for (var i = 0; i < scenes.length; i++) {
                        if (number(scenes[i]) !== step) continue;
                        if (step !== 1 || scenes[i].getAttribute('data-sh-panel') === method) return scenes[i];
                    }
                    return null;
                }

                // The terminal shows the files until the cron entry is due; the browser shows
                // the wizard until the install is through it. Steps 1 and 3 are the terminal's.
                function show(beat, play) {
                    var terminal = sceneOf(beat >= 3 ? 3 : 1);
                    var browser = sceneOf(beat >= 3 ? 4 : 2);
                    var front = beat === 1 || beat === 3 ? terminal : browser;

                    scenes.forEach(function (scene) {
                        var shown = scene === terminal || scene === browser;
                        scene.classList.remove('is-played', 'is-on');
                        scene.classList.toggle('is-shown', shown);
                        scene.classList.toggle('is-front', scene === front);
                        scene.classList.toggle('is-back', shown && scene !== front);
                        scene.classList.toggle('is-pending', number(scene) === 2 && beat < 2);
                        // What stands behind is to be looked at, not tabbed into.
                        scene.inert = shown && scene !== front;
                    });
                    if (play && motion && front) {
                        void front.offsetWidth;
                        front.classList.add('is-played');
                    }
                    beats.forEach(function (button) {
                        var on = +button.getAttribute('data-sh-beat') === beat;
                        if (on) button.setAttribute('aria-current', 'step');
                        else button.removeAttribute('aria-current');
                        if (on && caption) {
                            var words = one('.sh-beat-text', button);
                            caption.textContent = words ? words.textContent : '';
                        }
                    });
                    tabs.forEach(function (tab) {
                        var on = tab.getAttribute('data-sh-method') === method;
                        tab.setAttribute('aria-selected', on ? 'true' : 'false');
                        tab.setAttribute('tabindex', on ? '0' : '-1');
                    });
                    stage.setAttribute('data-beat', beat);
                }

                function playing(on) {
                    stage.classList.toggle('is-playing', on);
                    if (againLabel) againLabel.textContent = againLabel.getAttribute(on ? 'data-pause' : 'data-play');
                }

                function stop() {
                    if (timer) { clearTimeout(timer); timer = null; }
                    playing(false);
                }

                function run(beat) {
                    stop();
                    show(beat, true);
                    if (!holds[beat]) return;
                    stage.style.setProperty('--sh-dur', holds[beat] + 'ms');
                    void stage.offsetWidth;
                    playing(true);
                    timer = setTimeout(function () { run(beat + 1); }, holds[beat]);
                }

                function choose(index, focus) {
                    touched = true;
                    stop();
                    method = tabs[index].getAttribute('data-sh-method');
                    stage.setAttribute('data-method', method);
                    if (motion) run(1);
                    else show(1, false);
                    if (focus) tabs[index].focus();
                    visit.method = method;
                    visit.methodLabel = (one('strong', tabs[index]) || tabs[index]).textContent.trim();
                    recall();
                }

                tabs.forEach(function (tab, index) {
                    tab.addEventListener('click', function () { choose(index, false); });
                    tab.addEventListener('keydown', function (event) {
                        arrows(event, index, tabs.length, function (next) { choose(next, true); });
                    });
                });
                beats.forEach(function (button) {
                    button.addEventListener('click', function () {
                        touched = true;
                        stop();
                        show(+button.getAttribute('data-sh-beat'), true);
                    });
                });
                // One control, two jobs: it stops the film while it runs, and starts it again after.
                if (again) {
                    again.addEventListener('click', function () {
                        touched = true;
                        if (timer) stop();
                        else run(1);
                    });
                }
                // Somebody who has come into the stage with the keyboard is not to have the
                // scene they are in cut away. A mouse press inside a window says the same; a
                // finger on the glass may only be a scroll, and its tap arrives as a click.
                stage.addEventListener('focusin', function (event) {
                    // Not for the control that plays and pauses, nor for the ways in: pressing
                    // one of those is asking for the film.
                    if (event.target === again || (event.target.closest && event.target.closest('[data-sh-method]'))) return;
                    stop();
                });
                if (desk) {
                    desk.addEventListener('pointerdown', function (event) {
                        if (event.pointerType === 'mouse') stop();
                    });
                    // The window behind comes forward when it is pressed. It is inert, so the
                    // press lands on the desk; a browser that does not know `inert` hands it
                    // to the window itself.
                    desk.addEventListener('click', function (event) {
                        var behind = event.target.closest ? event.target.closest('.sh-scene.is-back') : null;
                        if (!behind && event.target === desk) {
                            for (var i = 0; i < scenes.length; i++) {
                                if (!scenes[i].classList.contains('is-back')) continue;
                                var box = scenes[i].getBoundingClientRect();
                                if (event.clientX >= box.left && event.clientX <= box.right && event.clientY >= box.top && event.clientY <= box.bottom) behind = scenes[i];
                            }
                        }
                        if (!behind) return;
                        touched = true;
                        stop();
                        show(number(behind), true);
                    });
                }

                stage.classList.add('is-ready');
                show(1, false);
                if (motion) {
                    stage.classList.add('can-play');
                    // The terminal the first lines arrive in is in view before they do.
                    seen(sceneOf(1) || desk, 30, function () { if (!touched) run(1); });
                }
            })();

            // ---- Copy buttons ---------------------------------------------------------------
            root.addEventListener('click', function (event) {
                var button = event.target.closest('[data-sh-copy]');
                if (!button || !navigator.clipboard) return;
                if (!button.hasAttribute('data-label')) button.setAttribute('data-label', button.textContent);
                navigator.clipboard.writeText(button.getAttribute('data-sh-copy') || '').then(function () {
                    button.textContent = 'Copied';
                    setTimeout(function () { button.textContent = button.getAttribute('data-label'); }, 2000);
                }).catch(function () {});
            });

            // ---- The dial: each plan lit in turn, ending on the one that lights everything --
            (function () {
                var unlock = one('[data-sh-unlock]');
                if (!unlock) return;

                var buttons = all('[data-sh-plan]', unlock);
                var order = ['free', 'pro', 'ent', 'self'];
                var timer = null;

                function set(plan) {
                    unlock.setAttribute('data-plan', plan);
                    buttons.forEach(function (button) {
                        button.setAttribute('aria-pressed', button.getAttribute('data-sh-plan') === plan ? 'true' : 'false');
                    });
                }

                var touched = false;

                buttons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        touched = true;
                        if (timer) { clearTimeout(timer); timer = null; }
                        unlock.classList.add('is-live');
                        set(button.getAttribute('data-sh-plan'));
                    });
                });

                // The climb needs motion and a way to know the wall is on screen. Without
                // either the page stays as it was drawn: everything lit.
                if (!motion || !('IntersectionObserver' in window)) return;

                set('free');
                seen(one('.sh-wall', unlock), 30, function () {
                    if (touched) return;
                    var at = 0;
                    unlock.classList.add('is-live');
                    (function step() {
                        at++;
                        timer = setTimeout(function () {
                            set(order[at]);
                            if (at < order.length - 1) step();
                            else timer = null;
                        }, at === 1 ? 700 : 950);
                    })();
                });
            })();

            // ---- Auto import: Test Import brings its example result in again -----------------
            all('[data-sh-import-box]').forEach(function (box) {
                var press = one('[data-sh-import]', box);
                var found = one('.sh-app-found', box);
                if (!press || !found) return;
                press.addEventListener('click', function () {
                    found.classList.remove('is-run');
                    void found.offsetWidth;
                    found.classList.add('is-run');
                });
            });

            // ---- The update: one press ------------------------------------------------------
            all('[data-sh-update]').forEach(function (box) {
                var go = one('[data-sh-update-go]', box);
                var again = one('[data-sh-update-reset]', box);
                if (!go) return;

                go.addEventListener('click', function () {
                    if (box.getAttribute('data-state') !== 'ready') return;
                    box.setAttribute('data-state', 'busy');
                    setTimeout(function () {
                        // The button that had the focus is about to go: hand it on, but only
                        // if the visitor is still on it.
                        var here = document.activeElement === go;
                        box.setAttribute('data-state', 'done');
                        if (here && again) again.focus({ preventScroll: true });
                        var say = one('[data-sh-update-say]', box);
                        if (say) say.textContent = say.getAttribute('data-done');
                    }, motion ? 1650 : 0);
                });
                if (again) {
                    again.addEventListener('click', function () {
                        box.setAttribute('data-state', 'ready');
                        var say = one('[data-sh-update-say]', box);
                        if (say) say.textContent = '';
                        go.focus({ preventScroll: true });
                        go.click();
                    });
                }
                var check = one('[data-sh-update-check]', box);
                var checked = one('[data-sh-update-checked]', box);
                if (check && checked) check.addEventListener('click', function () { checked.textContent = 'just now'; });
                box.classList.add('is-ready');
            });

            // ---- Federation: off, until somebody switches it on ------------------------------
            all('[data-sh-fed]').forEach(function (fed) {
                var toggle = one('[data-sh-fed-switch]', fed);
                if (!toggle) return;
                toggle.addEventListener('click', function () {
                    var on = toggle.getAttribute('aria-checked') !== 'true';
                    toggle.setAttribute('aria-checked', on ? 'true' : 'false');
                    fed.setAttribute('data-on', on ? '1' : '0');
                    // The card beside it says the same thing, so it says it at the same time.
                    all('[data-sh-fed-state]').forEach(function (mark) {
                        mark.textContent = on ? 'on' : 'off';
                        mark.classList.toggle('is-on', on);
                    });
                });
                fed.classList.add('is-ready');
            });

            // ---- Pre-flight: six ticks turn the sheet from amber to green --------------------
            all('[data-sh-pre]').forEach(function (sheet) {
                var items = all('[data-sh-pre-item]', sheet);
                var count = one('[data-sh-pre-count]', sheet);

                function tally(touched) {
                    var done = items.filter(function (item) { return item.getAttribute('aria-pressed') === 'true'; }).length;
                    if (count) count.textContent = done;
                    sheet.setAttribute('data-ready', done === items.length ? '1' : '0');
                    if (touched) {
                        visit.covered = done;
                        visit.of = items.length;
                        recall();
                    }
                }

                items.forEach(function (item) {
                    item.addEventListener('click', function () {
                        item.setAttribute('aria-pressed', item.getAttribute('aria-pressed') === 'true' ? 'false' : 'true');
                        tally(true);
                    });
                });
                sheet.classList.add('is-ready');
                tally(false);
            });

            // ---- Multi-tenant mode: the same install, one switch on --------------------------
            all('[data-sh-board]').forEach(function (board) {
                var toggle = one('[data-sh-board-switch]', board);

                function set(on) {
                    board.setAttribute('data-on', on ? '1' : '0');
                    if (toggle) toggle.setAttribute('aria-checked', on ? 'true' : 'false');
                }

                var touched = false;

                if (toggle) toggle.addEventListener('click', function () { touched = true; set(board.getAttribute('data-on') !== '1'); });
                board.classList.add('is-ready');
                if (motion && 'IntersectionObserver' in window) {
                    set(false);
                    seen(board, 30, function () { setTimeout(function () { if (!touched) set(true); }, 650); });
                }
            });

            // ---- The control room: real screens in one frame ---------------------------------
            all('[data-sh-room]').forEach(function (room) {
                var tabs = all('[role="tab"]', room);
                var panels = all('[role="tabpanel"]', room);
                if (!tabs.length || tabs.length !== panels.length) return;

                function pick(index, focus) {
                    tabs.forEach(function (tab, i) {
                        tab.setAttribute('aria-selected', i === index ? 'true' : 'false');
                        tab.setAttribute('tabindex', i === index ? '0' : '-1');
                        if (i === index && focus) tab.focus();
                    });
                    panels.forEach(function (panel, i) { panel.hidden = i !== index; });
                }

                tabs.forEach(function (tab, index) {
                    tab.addEventListener('click', function () { pick(index, false); });
                    tab.addEventListener('keydown', function (event) {
                        arrows(event, index, tabs.length, function (next) { pick(next, true); });
                    });
                });
                room.classList.add('is-ready');
                pick(0, false);
            });
        })();
    </script>

    <!-- Motion engines (reveals) -->
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
