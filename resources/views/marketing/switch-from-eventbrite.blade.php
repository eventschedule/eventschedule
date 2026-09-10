<x-marketing-layout>
    <x-slot name="title">Switch from Eventbrite to Event Schedule: Migration Guide</x-slot>
    <x-slot name="description">What the Eventbrite import brings across, what stays behind, and how payments and refunds work once you sell from your own page with no platform fee.</x-slot>
    <x-slot name="breadcrumbTitle">Switch from Eventbrite</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "Move from Eventbrite to Event Schedule",
        "description": "Import your existing Eventbrite events into Event Schedule with their venues, ticket types and images, then sell from your own page with no platform fee.",
        "step": [
            {"@type": "HowToStep", "name": "Create your schedule", "text": "Sign up and create a schedule. It gets its own address straight away, and nothing is charged for it."},
            {"@type": "HowToStep", "name": "Paste an Eventbrite token", "text": "Open the import screen on your schedule and paste an Eventbrite private token. Event Schedule finds your organization and lists the events on it."},
            {"@type": "HowToStep", "name": "Pick what comes across", "text": "Choose the events you want. Each one arrives with its date and duration, its description, its venue and address, its ticket types with their prices and quantities, and its image."},
            {"@type": "HowToStep", "name": "Connect how you get paid", "text": "Connect your own Stripe or PayPal account so ticket money goes straight to you, with no platform fee taken by Event Schedule."}
        ]
    }
    </script>
    </x-slot>

    {{-- Motion gate: the hidden pre-reveal states below only apply when this class is present, so
         no-JS visitors, crawlers and reduced-motion users always see the whole page. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           Eventbrite migration "The Move" styles.

           CONCEPT: THE PACKING LIST. /eventbrite-alternative already
           argues the case for leaving; a person who has decided has a
           completely different question, and it is logistical: what
           comes with me, what does not, and what do I have to do on the
           first day. So the page is a packing list - three columns of
           it - and its whole job is that nobody arrives and finds
           something missing they assumed was in the box.

           WHY A SEPARATE PAGE FROM THE COMPARISON. Comparison pages are
           read while deciding; this is read while doing. Mixing them
           makes the comparison longer and buries the instructions.
           The two link to each other and neither restates the other:
           in particular the FEE ARITHMETIC lives on /compare, which
           derives every rate from one array, so quoting numbers here
           would be a second copy that could drift.

           DELIBERATELY NOT: /compare owns "The Scorecard" and the
           head-to-head; /replace owns the tool-replacement family;
           /features/promo-codes owns the receipt.

           THE CRATE IS A PHYSICAL OBJECT, so .es-move-crate is FIXED in
           both colour modes.

           COLOUR: the comparison family's blue, on purpose. This page is
           a SIBLING of /eventbrite-alternative, which renders through
           compare-single at #1d4ed8 / #9cc0ff, and a reader arriving
           from it should not feel they have left the section. Identity
           here comes from the STRUCTURE - a checklist with three states
           - rather than from a ninth hue in a family that has run out of
           them. State is carried by SHAPE (a tick, a hollow box, a
           struck box) so it reads without colour.

           Measured against the grounds this page actually paints:
             light ground #f4f5f7: ink #101319 17.05, muted #4b5563 6.93,
                                   accent #1d4ed8 6.14
             dark ground  #0a0c11: ink #e8eaf0 16.26, muted #99a0ad 7.44,
                                   accent #9cc0ff 10.62
             crate #13161d (fixed): ink #e8eaf0 15.05, muted #939aa7 6.40
           The crate deliberately paints NO accent: the three packing
           states are told apart by the shape of the box, so a colour
           there would be a fourth signal saying nothing new.
           text-gray-500 is never used on the tinted ground. Use
           .es-move-muted.
           ============================================================== */

        .es-move-page { background-color: #f4f5f7; color: #101319; }
        .dark .es-move-page { background-color: #0a0c11; color: #e8eaf0; }

        .es-move-ink { color: #101319; }
        .dark .es-move-ink { color: #e8eaf0; }
        .es-move-muted { color: #4b5563; }
        .dark .es-move-muted { color: #99a0ad; }
        .es-move-accent { color: #1d4ed8; }
        .dark .es-move-accent { color: #9cc0ff; }

        .es-move-rule { border-top: 1px solid rgba(16, 19, 25, 0.10); }
        .dark .es-move-rule { border-top-color: rgba(232, 234, 240, 0.10); }

        .es-move-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #1d4ed8;
        }
        .dark .es-move-tag { color: #9cc0ff; }

        .es-move-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            border: 1.5px solid rgba(29, 78, 216, 0.35);
            font-size: 0.8125rem;
            font-weight: 800;
            color: #1d4ed8;
        }
        .dark .es-move-mark { border-color: rgba(156, 192, 255, 0.35); color: #9cc0ff; }

        .es-move-panel {
            background-color: #ffffff;
            border: 1px solid rgba(16, 19, 25, 0.10);
            border-radius: 1rem;
        }
        .dark .es-move-panel {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(232, 234, 240, 0.10);
        }

        /* ---- THE CRATE. Fixed in both modes; see the contract above. ---- */
        .es-move-crate {
            background-color: #13161d;
            color: #e8eaf0;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 1rem;
            box-shadow: 0 18px 45px rgba(6, 8, 12, 0.45);
        }
        .es-move-crate-ink { color: #e8eaf0; }
        .es-move-crate-muted { color: #939aa7; }
        .es-move-crate-rule { border-top: 1px solid rgba(255, 255, 255, 0.09); }

        /* A line on the packing list. Three states, and the difference between them is
           the SHAPE of the box, so a mono screen and a colour-blind reader both read it:
           packed is filled, to-do is hollow, staying behind is struck through. */
        .es-move-box {
            position: relative;
            width: 1.05rem;
            height: 1.05rem;
            flex: none;
            border-radius: 0.28rem;
            border: 1.5px solid rgba(232, 234, 240, 0.35);
        }
        .es-move-box-packed { background-color: #9cc0ff; border-color: #9cc0ff; }
        .es-move-box-out::after {
            content: "";
            position: absolute;
            left: -0.15rem;
            right: -0.15rem;
            top: 50%;
            border-top: 1.5px solid rgba(232, 234, 240, 0.55);
            transform: rotate(-32deg);
        }

        .es-move-band { background-color: #0c1017; }
        .es-move-band-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #9cc0ff;
        }
        .es-move-band-grad {
            background-image: linear-gradient(90deg, #9cc0ff, #7dd3fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        /* Shared classes carry their own .dark rules in marketing.css and would
           otherwise change inside a band that has none. */
        .es-move-band .grid-overlay { background-image:
            linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px); }
        .es-move-band .es-claim:focus-within { border-color: rgba(156, 192, 255, 0.55); }

        /* marketing.css:248 only rings a.feature-card|bento-card|persona-card. */
        #es-move-page a:focus-visible,
        #es-move-page summary:focus-visible,
        #es-move-page button:focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 2px;
        }
        .dark #es-move-page a:focus-visible,
        .dark #es-move-page summary:focus-visible,
        .dark #es-move-page button:focus-visible { outline-color: #9cc0ff; }
    </style>

    <div id="es-move-page" class="es-move-page">

        <!-- ============================================================ -->
        <!-- 1. Hero: the packing list                                    -->
        <!-- ============================================================ -->
        <section id="top" class="relative scroll-mt-24 overflow-hidden py-16 lg:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <p class="es-move-tag mb-4" data-reveal>Moving from Eventbrite</p>
                        <h1 class="es-balance es-move-ink text-4xl font-black tracking-tight md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            Bring the events. <span class="es-move-accent">Leave the fee.</span>
                        </h1>
                        <p class="es-move-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                            If you have already decided, this is the practical half: what comes across in the import, what you have to do by hand, and what is different once you are here. Nothing on this page is an argument.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#1a44bb]">
                                Start for free
                            </a>
                            <a href="{{ marketing_url('/eventbrite-alternative') }}" class="es-move-ink inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 font-semibold transition-colors hover:border-[#1d4ed8] dark:border-white/15">
                                Still comparing?
                            </a>
                        </div>
                        <p class="es-move-muted mt-4 text-sm" data-reveal style="--reveal-delay: 0.2s;">
                            The import is a Pro feature. Creating the schedule and <a href="{{ marketing_url('/features/ticketing') }}" class="es-move-accent font-semibold hover:underline">selling up to 25 paid tickets a month</a> from it are not.
                        </p>
                    </div>

                    @php
                        // Exactly what EventbriteController brings across, and what it does not.
                        // Fixed, never random, for the band-diff verifier.
                        $movePacked = [
                            ['packed', 'Event name, description, start time and duration'],
                            ['packed', 'The venue, with its full address'],
                            ['packed', 'Ticket types, prices and quantities'],
                            ['packed', 'The event image and its currency'],
                            ['todo', 'Your Stripe or PayPal account, connected once'],
                            ['out', 'Past orders and attendee history'],
                            ['out', 'Discount codes, checkout questions and seat maps'],
                        ];
                    @endphp
                    <div class="es-move-crate p-6 sm:p-8" data-reveal="panel" style="--reveal-delay: 0.1s;">
                        <p class="es-move-crate-muted text-xs font-semibold uppercase tracking-widest">Packing list</p>
                        <ul class="mt-5 space-y-3.5">
                            @foreach ($movePacked as [$boxState, $boxLabel])
                                <li class="flex items-start gap-3">
                                    <span class="es-move-box {{ $boxState === 'packed' ? 'es-move-box-packed' : ($boxState === 'out' ? 'es-move-box-out' : '') }} mt-0.5" aria-hidden="true"></span>
                                    <span class="{{ $boxState === 'packed' ? 'es-move-crate-ink' : 'es-move-crate-muted' }} text-sm leading-relaxed">{{ $boxLabel }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="es-move-crate-rule mt-6 pt-4">
                            <p class="es-move-crate-muted text-xs leading-relaxed">
                                Filled is in the box. Hollow is a job for you. Struck through does not travel, and the section below says what to do about it.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The import                                                -->
        <!-- ============================================================ -->
        <section id="import" class="es-move-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-move-mark mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-move-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The import</p>
                    <h2 class="es-balance es-move-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A move, <span class="es-move-accent">not a sync.</span>
                    </h2>
                    <p class="es-move-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        It runs when you press the button and never on a timer, which is the right shape for a thing you are doing once. Nothing keeps reaching back into your Eventbrite account afterwards.
                    </p>
                </div>

                @php
                    $moveSteps = [
                        ['Paste a token', 'Find the private token in your Eventbrite account, under Account Settings, Developer Links, API Keys, and paste it into the import screen on your schedule. It finds your organization and lists what is on it.'],
                        ['Pick the events', 'Choose from what it found: it shows upcoming events, and past ones are one click away. You do not have to take everything, and you can come back and run it again for the ones you skipped.'],
                        ['They arrive furnished', 'Each event lands with its date and duration, its description, its venue and full address, its ticket types with their prices and quantities, its image, its currency and a category. An online event keeps a link back to its Eventbrite page.'],
                    ];
                @endphp
                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                    @foreach ($moveSteps as [$msName, $msBody])
                        <div class="es-move-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-move-ink text-base font-bold">{{ $msName }}</h3>
                            <p class="es-move-muted mt-2 text-sm leading-relaxed">{{ $msBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. What does not travel                                      -->
        <!-- ============================================================ -->
        <section id="gaps" class="es-move-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-move-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-move-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The gaps</p>
                    <h2 class="es-balance es-move-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        What you have to <span class="es-move-accent">carry yourself.</span>
                    </h2>
                    <p class="es-move-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Three things do not come out of the import, and it is better to know that before you start than to go looking for them afterwards.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                    <div class="es-move-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-move-ink text-base font-bold">People who already bought</h3>
                        <p class="es-move-muted mt-2 text-sm leading-relaxed">
                            Past orders and attendee history stay where they are. If you have a date that is already selling and you want those buyers to have a ticket here, export them from Eventbrite and use the bulk attendee import, a Pro feature that takes a CSV of up to 5,000 rows. Their payments stay with Eventbrite too, so a refund on one of those orders is made there.
                        </p>
                        <a href="{{ marketing_url('/docs/tickets#importing-attendees') }}" class="es-move-accent mt-auto pt-4 text-sm font-semibold hover:underline">
                            Importing attendees
                        </a>
                    </div>
                    <div class="es-move-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-move-ink text-base font-bold">Codes, questions and seat maps</h3>
                        <p class="es-move-muted mt-2 text-sm leading-relaxed">
                            The import reads the event, not the selling rules around it. Discount codes and checkout questions are set up again here, both on Pro, and a seat map is redrawn as a seating plan, on Enterprise for a venue schedule. Every event also arrives as a one-time date, so a series is worth setting up again as a recurring event, which is free.
                        </p>
                    </div>
                    <div class="es-move-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-move-ink text-base font-bold">How you get paid</h3>
                        <p class="es-move-muted mt-2 text-sm leading-relaxed">
                            You connect your own Stripe or PayPal account once, in Settings under Payment Methods, and pick which one an event uses. That is the change that matters: the money stops arriving as a payout from somebody else and starts arriving in an account that is already yours.
                        </p>
                        <div class="mt-auto flex flex-wrap gap-x-5 gap-y-1 pt-4">
                            <a href="{{ marketing_url('/stripe') }}" class="es-move-accent text-sm font-semibold hover:underline">Payments with Stripe</a>
                            <a href="{{ marketing_url('/paypal') }}" class="es-move-accent text-sm font-semibold hover:underline">Payments with PayPal</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. What is different once you are here                       -->
        <!-- ============================================================ -->
        <section id="after" class="es-move-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-move-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-move-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Afterwards</p>
                    <h2 class="es-balance es-move-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The first week <span class="es-move-accent">feels different.</span>
                    </h2>
                    <p class="es-move-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Not because there are more features, but because the things you were renting are now yours.
                    </p>
                </div>

                @php
                    // The third field is an optional [href, label] link for the foot of the card.
                    $moveAfter = [
                        ['No platform fee', 'Event Schedule takes nothing from a ticket sale on any plan. The only deduction is your payment processor\'s own, and it goes to them rather than through us.', null],
                        ['A page that is yours', 'Your own address, your own branding above the free tier, and a calendar people can subscribe to rather than a listing on somebody else\'s site.', null],
                        ['Your audience is yours', 'Followers and newsletter subscribers belong to the schedule, and you can email them yourself. Nothing sits between you and the people who came last time.', null],
                        ['You can leave again', 'It is open source, everything you make can be exported, and you can selfhost the whole thing at no cost. The next move, if there is one, is your decision rather than a negotiation.', null],
                        ['Refunds leave your own account', 'A refund is a button on the Sales page. On a Stripe or PayPal sale the money goes back through them, in full or in part, and a partial refund keeps the tickets valid. Cash and other methods are marked as refunded, which moves no money. Event Schedule does not email the buyer about a refund, so tell them yourself.', [marketing_url('/docs/tickets#managing-sales'), 'Managing sales and refunds']],
                        ['Demand you can count', 'An event with nothing on sale yet offers "Tell me when tickets go on sale". People leave an email address, with no account, and hear when tickets go on sale, if you cancel, and shortly before it starts. It is free on every plan, and the Tickets panel shows you how many are waiting.', [marketing_url('/docs/tickets#interest-list'), 'The interest list']],
                    ];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2" data-reveal-group="80">
                    @foreach ($moveAfter as [$maName, $maBody, $maLink])
                        <div class="es-move-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-move-ink text-base font-bold">{{ $maName }}</h3>
                            <p class="es-move-muted mt-2 text-sm leading-relaxed">{{ $maBody }}</p>
                            @if ($maLink)
                                <a href="{{ $maLink[0] }}" class="es-move-accent mt-auto pt-4 text-sm font-semibold hover:underline">{{ $maLink[1] }}</a>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="es-move-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-move-ink text-base font-bold">Want the numbers instead?</h3>
                    <p class="es-move-muted mt-2 text-sm leading-relaxed">
                        The fee arithmetic lives on the comparison, where a calculator works it out against published rates rather than being restated here where it could go stale.
                    </p>
                    <a href="{{ marketing_url('/eventbrite-alternative') }}" class="es-move-accent mt-4 inline-block text-sm font-semibold hover:underline">
                        Event Schedule compared with Eventbrite
                    </a>
                </div>
            </div>
        </section>

        @include('marketing.partials.pricing-nudge')

        <!-- ============================================================ -->
        <!-- 5. FAQ                                                       -->
        <!-- ============================================================ -->
        @php
            $moveFaqs = [
                ['q' => 'Does the import keep running in the background?', 'a' => 'No, and that is deliberate. It runs when you press the button. A background sync would keep an account you are leaving as the source of truth, which is the opposite of what a move is for.'],
                ['q' => 'What exactly comes across with an event?', 'a' => 'Its name and description, its start time and duration, its venue with the full address, its ticket types with their prices and quantities, its image, its currency and a mapped category. An online event also keeps a link back to its Eventbrite page. Discount codes, checkout questions and seat maps do not come across, and every event arrives as a one-time date.'],
                ['q' => 'What about people who already bought a ticket?', 'a' => 'They do not come with the import. Export them from Eventbrite and use the bulk attendee import, a Pro feature that takes a CSV of up to 5,000 rows, so people who already paid end up with a ticket and a QR code here as well. Their payments stay with Eventbrite, so a refund on one of those orders is made there.'],
                ['q' => 'Do I have to move everything at once?', 'a' => 'No. Pick the events you want, and run it again later for the ones you skipped. One way is to move the next season across and leave the current one where it is until it has finished.'],
                ['q' => 'Where does the ticket money go?', 'a' => 'Into your own Stripe or PayPal account, which you connect once. Event Schedule adds no fee of its own on any plan, so what you receive is the ticket price minus your processor\'s charge.'],
                ['q' => 'Can buyers pay with PayPal?', 'a' => 'Yes, on every plan. Connect your own PayPal account in Settings under Payment Methods and choose PayPal for the event, and the money goes to that account. Stripe is there for cards, and PayPal also works when a buyer takes tickets to several of your events in one checkout. Installment plans, a Pro feature, run on Stripe only.'],
                ['q' => 'How do refunds work once I have moved?', 'a' => 'From the Sales page. A Stripe or PayPal sale can be refunded in full or in part: the money goes back through that provider first, and only then does the sale change, and a partial refund leaves it paid with its tickets valid. A sale taken another way, such as cash or a payment link, is marked as refunded instead, which records it without moving money. Event Schedule does not email the buyer about a refund, so let them know yourself.'],
                ['q' => 'Can people be told when tickets go on sale?', 'a' => 'Yes. On an event with nothing on sale yet, a visitor can press "Tell me when tickets go on sale" and leave an email address, with no account. They get one email when tickets go on sale, one if you cancel the event and a reminder shortly before it starts, plus any notice you choose to send if the date or venue changes. It is free on every plan, it is not a subscription to your schedule, and the event\'s Tickets panel shows how many people are waiting.'],
                ['q' => 'Do I need a paid plan to move?', 'a' => 'The Eventbrite import and the bulk attendee import are Pro features. Creating a schedule, publishing events and selling tickets are not: the free plan sells up to 25 paid tickets a calendar month with no platform fee, so you can put a date on sale before you decide about a plan.'],
                ['q' => 'Can I take my data out again later?', 'a' => 'Yes. There is a backup and restore for everything you have created, the API and webhooks are there on Pro, and the whole application is open source and can be selfhosted at no cost. Being easy to leave is the point.'],
            ];
        @endphp
        <section id="faq" class="es-move-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-move-ink mb-10 text-center text-3xl font-black tracking-tight md:text-4xl" data-reveal>Questions</h2>
                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($moveFaqs as $faq)
                        <details class="es-move-panel group p-5" data-reveal="panel">
                            <summary class="es-move-ink flex cursor-pointer items-center justify-between gap-4 text-base font-semibold">
                                {{ $faq['q'] }}
                                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-45" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                            </summary>
                            <p class="es-move-muted mt-3 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$moveFaqs" />

        <!-- ============================================================ -->
        <!-- 6. Claim                                                     -->
        <!-- ============================================================ -->
        <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="es-move-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-20" data-reveal="panel">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="grid-overlay absolute inset-0 opacity-25"></div>
                    </div>

                    <div class="relative z-10">
                        <p class="es-move-band-tag mb-6">Free to start</p>
                        <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                            Take your calendar <span class="es-move-band-grad">with you</span>.
                        </h2>
                        <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                            Claim an address, bring the events across, and sell the next one from your own page.
                        </p>

                        <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg bg-white px-8 py-4 text-lg font-semibold text-[#0c1017] transition-colors hover:bg-gray-100">
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
