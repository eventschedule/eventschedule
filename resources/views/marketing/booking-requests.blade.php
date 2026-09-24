<x-marketing-layout>
    <x-slot name="title">Booking Request Form: Let Acts and Promoters Ask for a Date</x-slot>
    <x-slot name="description">A free booking request form for your schedule page: promoters ask a performer for a date, acts ask a venue, and each request arrives with the sender's details.</x-slot>
    <x-slot name="breadcrumbTitle">Booking Requests</x-slot>

    {{-- Claims on this page, checked against the code and docs/FEATURES.md ("Event requests"):
         - The form, its required-field choices, the Online option, the phone field, Request Terms,
           Require Approval, Approved Schedules and the Requests tab are free on every plan. Asking
           your own questions is the Pro custom fields feature: EventController::bookingRequest()
           reads $role->getRequestFormCustomFields() only when $role->isPro().
         - A talent schedule ALWAYS uses the Booking Form (Role::usesBookingForm()) and always
           reviews requests (Role::getRequireApprovalAttribute() is forced true), and its form never
           forces an account (Role::bookingFormRequiresAccount()). A venue or curator chooses the
           AI Import form or the Booking Form, and can switch Require Approval off, in which case a
           request goes straight onto the schedule (bookingRequest(): is_accepted true). So never
           say every request waits for approval.
         - The sender's name and email, and phone when asked, are stored on the event and shown on
           the Requests tab, owner-facing only. Accept and Decline email the sender only when they
           sent it signed in: requestDecisionRecipient() returns nobody for a guest submission.
         - Anti-abuse: a honeypot on the form, and on the hosted service a daily cap on new events
           per schedule (Role::canCreateEvent(), null on selfhost). --}}

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule - Booking Request Form"
        description="A booking request form on your Event Schedule page: promoters ask a performer for a date, acts ask a venue, and the community submits events to a curator, with the sender's name and email on every request."
        audience="Performers, venues and event curators"
        keywords="booking request form, band booking form, performer booking request, venue booking request, event submission form, request to book" />
    </x-slot>

    @php
        $bookingFaqs = [
            ['q' => 'What is a booking request form?', 'a' => 'A form on your schedule page that lets someone ask you for a date. On a performer\'s schedule it sits behind a Request to Book button, for promoters and venues who want to book the act. On a venue or curator schedule it is one of two ways visitors can submit an event, beside a form that reads a pasted listing or a flyer, from the band asking for a Friday to the neighbour listing a market. Requests that need your approval wait on the Requests tab of your schedule.'],
            ['q' => 'Is it free?', 'a' => 'Yes, on every plan: the form itself, choosing which fields are required, the online option, the phone number field, your request terms, approval and the Requests tab. The one part that needs Pro is asking questions of your own, which uses custom fields.'],
            ['q' => 'Do requests go live straight away?', 'a' => 'Not on a performer\'s schedule: a request to book a performer always waits for you to accept it. On a venue or curator schedule, Require Approval is on by default. Turn it off and requests go straight onto your schedule, or leave it on and name approved schedules whose requests skip the queue.'],
            ['q' => 'Does the person asking need an account?', 'a' => 'Not on a performer\'s schedule, where making one is left to them. A venue or curator schedule can require one. Where the site accepts new accounts, someone sending a request as a guest can choose to create one as they send it.'],
            ['q' => 'How do I reply to a request?', 'a' => 'Each request shows the name and email of whoever sent it, and their phone number if your form asks for one, so you can write back before you decide. Accept and Decline email your decision to anyone who sent the request while signed in; a guest hears from you directly.'],
            ['q' => 'Can I ask my own questions?', 'a' => 'Yes, on Pro. Any custom field marked for the request form appears on it: the backline an act needs as a checklist, a reference number checked against a pattern, an expected head count. The answers show on the request, and on the event once you accept it.'],
            ['q' => 'How do I stop spam requests?', 'a' => 'The form carries a hidden field that catches the bots that fill in every box, and on eventschedule.com a schedule can only take so many new events in a day. You can also turn requests off, require an account on a venue or curator schedule, and set request terms that say what you will and will not take.'],
        ];
    @endphp

    {{-- Motion gate: the hidden pre-reveal states below only apply when this class is present, so
         no-JS visitors, crawlers and reduced-motion users always see the whole page. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           Booking requests: "The Hold".

           CONCEPT: a date penciled in. A request is a date somebody wants,
           held in the diary until the owner writes it in ink or rubs it
           out, so the page runs from the form a visitor fills in to the
           inbox the owner answers from. No drawing: the concept is carried
           by the two cards, the form and the request, and by the HOLD mark
           they both carry until somebody decides.

           DELIBERATELY NOT: /for-venues owns the booking-inbox banner
           inside a whole venue story; /features/registration owns the free
           sign-up form. This page is the request form and its inbox, for
           all three kinds of schedule.

           COLOUR: emerald, the colour the product already uses for an
           accepted request.
             light ground #ffffff: accent #047857 5.5, ink #111827 17.7
             dark ground  #0a0a0f: accent #6ee7b7 13.9
           The form and request cards are FIXED white in both modes: they
           are pictures of pages the visitor or owner may see in either
           theme.
           ============================================================== */

        .es-hold-accent { color: #047857; }
        .dark .es-hold-accent { color: #6ee7b7; }

        .es-hold-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #047857;
        }
        .dark .es-hold-tag { color: #6ee7b7; }

        .text-gradient-hold {
            background: linear-gradient(135deg, #047857 0%, #0d9488 55%, #0284c7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-hold,
        .es-finale-panel .text-gradient-hold {
            background: linear-gradient(135deg, #6ee7b7 0%, #5eead4 55%, #7dd3fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .es-hold-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.125rem 0.625rem;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .es-hold-pill-free { background-color: #d1fae5; color: #065f46; }
        .dark .es-hold-pill-free { background-color: rgba(110, 231, 183, 0.14); color: #6ee7b7; }
        .es-hold-pill-pro { background-color: #e0f2fe; color: #075985; }
        .dark .es-hold-pill-pro { background-color: rgba(125, 211, 252, 0.14); color: #7dd3fc; }

        /* ---- THE CARDS. Fixed in both modes; see the contract above. ---- */
        .es-hold-card {
            background-color: #ffffff;
            color: #111827;
            border: 1px solid rgba(17, 24, 39, 0.12);
            border-radius: 1rem;
            box-shadow: 0 18px 45px rgba(17, 24, 39, 0.16);
        }
        .dark .es-hold-card { box-shadow: 0 18px 45px rgba(0, 0, 0, 0.55); }
        .es-hold-card-muted { color: #4b5563; }
        .es-hold-field {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background-color: #f9fafb;
            padding: 0.55rem 0.75rem;
            font-size: 0.875rem;
            color: #111827;
        }
        .es-hold-stamp {
            border: 2px solid #047857;
            color: #047857;
            border-radius: 0.375rem;
            padding: 0.125rem 0.5rem;
            font-size: 0.6875rem;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            transform: rotate(-6deg);
        }
        .es-hold-stamp-wait { border-color: #b45309; color: #b45309; }

        .es-hold-btn { background-color: #047857; color: #ffffff; }
        .es-hold-btn:hover { background-color: #065f46; }
        .es-hold-ghost { border-color: #d1d5db; color: #111827; }
        .es-hold-ghost:hover { border-color: #047857; }
        .dark .es-hold-ghost { border-color: rgba(255, 255, 255, 0.15); color: #ffffff; }
        .dark .es-hold-ghost:hover { border-color: #6ee7b7; }
        .es-hold-num { background-color: #d1fae5; color: #065f46; }
        .dark .es-hold-num { background-color: rgba(110, 231, 183, 0.14); color: #6ee7b7; }

        #es-hold-page a:focus-visible,
        #es-hold-page summary:focus-visible {
            outline: 2px solid #047857;
            outline-offset: 2px;
        }
        .dark #es-hold-page a:focus-visible,
        .dark #es-hold-page summary:focus-visible { outline-color: #6ee7b7; }
    </style>

    <div id="es-hold-page">

        <!-- ============================================================ -->
        <!-- 1. Hero: the form a visitor fills in                          -->
        <!-- ============================================================ -->
        <section id="top" class="es-hero noise relative scroll-mt-24 overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 22% 68%, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0) 65%);"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 78% 30%, rgba(14, 165, 233, 0.16), rgba(14, 165, 233, 0) 65%);"></div>
                <div class="grid-pattern absolute inset-0 [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                    <div class="min-w-0">
                        <h1 class="es-balance text-4xl font-black tracking-tight text-gray-900 dark:text-white md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            <x-marketing.hero-eyebrow class="block es-hold-tag mb-4">Booking request form &middot; free on every plan</x-marketing.hero-eyebrow>
                            Let them ask. <span class="text-gradient-hold">You decide.</span>
                        </h1>
                        <p class="mt-6 text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                            A promoter wants your act for a Friday. A band wants a date at your venue. Somebody wants their market in your local guide. They fill in one form on your schedule page, and by default the request waits on your Requests tab, with their name and email on it, until you accept or decline it.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up') }}" class="es-hold-btn inline-flex items-center gap-2 rounded-xl px-6 py-3 font-semibold transition-colors">
                                Start for free
                            </a>
                            <a href="{{ marketing_url('/docs/creating-schedules') }}#engagement-requests" class="es-hold-ghost inline-flex items-center gap-2 rounded-xl border px-6 py-3 font-semibold transition-colors">
                                Read the guide
                            </a>
                        </div>
                    </div>

                    <div class="es-hold-card mx-auto w-full max-w-md p-6 sm:p-8" data-reveal="panel" style="--reveal-delay: 0.1s;" aria-label="Example booking request form">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="es-hold-card-muted text-xs font-semibold uppercase tracking-widest">Booking request</p>
                                <p class="mt-1 text-lg font-bold">The Paper Lanterns</p>
                            </div>
                            <span class="es-hold-stamp es-hold-stamp-wait mt-1" aria-hidden="true">Hold</span>
                        </div>

                        <div class="mt-5 space-y-3">
                            <div>
                                <p class="es-hold-card-muted mb-1 text-xs font-medium">Event name *</p>
                                <div class="es-hold-field">Harvest Festival, main stage</div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="es-hold-card-muted mb-1 text-xs font-medium">Date *</p>
                                    <div class="es-hold-field">Sat 10 Oct</div>
                                </div>
                                <div>
                                    <p class="es-hold-card-muted mb-1 text-xs font-medium">Start time *</p>
                                    <div class="es-hold-field">19:30</div>
                                </div>
                            </div>
                            <div>
                                <p class="es-hold-card-muted mb-1 text-xs font-medium">Location</p>
                                <div class="es-hold-field">Riverside Park, Hudson</div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="es-hold-card-muted mb-1 text-xs font-medium">Name *</p>
                                    <div class="es-hold-field">Dana Okoro</div>
                                </div>
                                <div>
                                    <p class="es-hold-card-muted mb-1 text-xs font-medium">Email *</p>
                                    <div class="es-hold-field truncate" dir="ltr">dana@example.com</div>
                                </div>
                            </div>
                        </div>

                        <div class="es-hold-btn mt-6 rounded-lg px-4 py-3 text-center text-sm font-semibold">Submit</div>
                        <p class="es-hold-card-muted mt-3 text-center text-xs">No account needed to ask. The schedule sees your details so it can reply.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. Who asks whom                                             -->
        <!-- ============================================================ -->
        @php
            $askers = [
                [
                    'Talent',
                    'Promoters and venues ask to book you',
                    'Your schedule page carries a Request to Book button. The form never makes anyone create an account, and every request waits for you: a date on a performer\'s calendar is always a person\'s decision.',
                    '/for-talent',
                    'Event Schedule for talent',
                ],
                [
                    'Venue',
                    'Acts ask you for a date',
                    'Choose the booking form, or the AI import form that reads a pasted listing or a flyer. Requests wait for approval by default; switch that off, or name the schedules you trust so theirs go straight on.',
                    '/for-venues',
                    'Event Schedule for venues',
                ],
                [
                    'Curator',
                    'The community sends you events',
                    'A local guide, a festival or a community calendar takes submissions the same way, with the same choice of form. Keep approval on and nothing appears in public until you say so.',
                    '/for-curators',
                    'Event Schedule for curators',
                ],
            ];
        @endphp
        <section id="who" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <p class="es-hold-tag mb-4" data-reveal>Who asks whom</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                        One form, <span class="text-gradient-hold">three kinds of request</span>
                    </h2>
                    <p class="mt-5 text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                        Every schedule on Event Schedule is talent, a venue or a curator, and each takes requests from the people who would naturally ask it.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="80">
                    @foreach ($askers as [$askType, $askHead, $askBody, $askPath, $askLink])
                        <div class="flex flex-col rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                            <p class="es-hold-tag">{{ $askType }}</p>
                            <h3 class="mt-2 text-lg font-bold text-gray-900 dark:text-white">{{ $askHead }}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $askBody }}</p>
                            <a href="{{ marketing_url($askPath) }}" class="es-hold-accent group mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold transition-all hover:gap-2">
                                {{ $askLink }}
                                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The form                                                  -->
        <!-- ============================================================ -->
        @php
            $formOptions = [
                ['Choose what is required', 'The form asks for an event name, a date and start time, a description and a location. Nothing is required until you tick it, so a venue that needs the date and a description can insist on exactly those.', 'Free'],
                ['In person or online', 'The location is a venue name, an address or a city, or a tick in the Online box with a link. Take online events off the form and every request is for a real room. A venue\'s own form never asks where: the venue is the place.', 'Free'],
                ['A phone number, if you want one', 'Ask for a phone number as optional or required. Signed-in visitors are asked too, because their account has a name and an email but no phone. On the request it is a link you can tap to call.', 'Free'],
                ['Your request terms', 'Set out your booking policy, your technical needs or what you will not take. The terms sit just above the submit button, where they are read before a request is sent.', 'Free'],
                ['Your own questions', 'Add custom fields to the form: a checklist of the backline an act needs, a reference number checked against a pattern, an expected head count. The answers show on the request and on the event once you accept it.', 'Pro'],
                ['An account, or not', 'On a performer\'s schedule nobody needs an account to ask. A venue or curator can require one, and where the site accepts new accounts a guest can choose to create one as they send the request.', 'Free'],
            ];
        @endphp
        <section id="form" class="scroll-mt-24 bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <p class="es-hold-tag mb-4" data-reveal>The form</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                        Ask for what you <span class="text-gradient-hold">need to know</span>
                    </h2>
                    <p class="mt-5 text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                        The fields come ready, and you decide which of them a request cannot be sent without.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                    @foreach ($formOptions as [$optTitle, $optBody, $optTier])
                        <div class="flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $optTitle }}</h3>
                                <span class="es-hold-pill {{ $optTier === 'Pro' ? 'es-hold-pill-pro' : 'es-hold-pill-free' }}">{{ $optTier }}</span>
                            </div>
                            <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $optBody }}</p>
                        </div>
                    @endforeach
                </div>

                <p class="mx-auto mt-10 max-w-3xl text-center text-gray-600 dark:text-gray-400" data-reveal>
                    Your own questions are <x-link href="{{ marketing_url('/features/custom-fields') }}">custom fields</x-link> marked for the request form, the one part of this page that needs Pro.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The inbox                                                 -->
        <!-- ============================================================ -->
        @php
            $inboxSteps = [
                ['It lands on the Requests tab', 'The tab appears while something is waiting and carries the count. Each request shows the event, the date, and the name and email of whoever sent it, with their phone number and your custom answers when you asked for them.'],
                ['You hear about it', 'Owners and admins get an email when new requests arrive, unless they switch it off under Settings, Notifications. Viewers can open a request but not decide it.'],
                ['Accept or decline', 'Accept puts the event on your public schedule; Decline removes it, after you confirm. Anyone who sent the request signed in is emailed your decision. Accept All clears the list in one go; declining is one at a time, on purpose.'],
                ['Or let it straight through', 'On a venue or curator schedule, switch Require Approval off and requests go straight onto the schedule, or keep it on and name approved schedules whose requests skip the queue. A performer\'s requests always wait.'],
            ];
        @endphp
        <section id="inbox" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                    <div class="es-hold-card order-last mx-auto w-full max-w-md p-6 lg:order-first" data-reveal="panel" aria-label="Example request on the Requests tab">
                        <div class="mb-4 flex items-center justify-between">
                            <p class="text-sm font-bold">Requests</p>
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-800">2 waiting</span>
                        </div>
                        <div class="relative rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <p class="text-base font-bold">Harvest Festival, main stage</p>
                            <p class="es-hold-card-muted mt-1 text-xs">Sat 10 Oct &middot; 19:30 &middot; Riverside Park, Hudson</p>
                            <div class="es-hold-card-muted mt-3 space-y-1 text-xs">
                                <p><span class="font-semibold text-gray-800">From:</span> Dana Okoro, dana@example.com</p>
                                <p><span class="font-semibold text-gray-800">Backline needed:</span> Drums, two vocal mics</p>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <span class="es-hold-btn flex-1 rounded-lg py-1.5 text-center text-xs font-semibold">Accept</span>
                                <span class="flex-1 rounded-lg border border-gray-300 py-1.5 text-center text-xs font-semibold text-gray-700">Decline</span>
                            </div>
                            <span class="es-hold-stamp es-hold-stamp-wait absolute" style="right: 12px; top: 12px;" aria-hidden="true">Hold</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 text-xs">
                            <span class="font-medium">Open mic, back room</span>
                            <span class="es-hold-card-muted">Thu 15 Oct</span>
                        </div>
                    </div>

                    <div>
                        <p class="es-hold-tag mb-4" data-reveal>The inbox</p>
                        <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                            The requests, <span class="text-gradient-hold">in one place</span>
                        </h2>
                        <ol class="mt-8 space-y-6" data-reveal-group="80">
                            @foreach ($inboxSteps as $stepIndex => [$stepTitle, $stepBody])
                                <li class="flex gap-4" data-reveal>
                                    <span class="es-hold-num flex h-9 w-9 flex-none items-center justify-center rounded-full text-sm font-bold" aria-hidden="true">{{ $stepIndex + 1 }}</span>
                                    <div>
                                        <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $stepTitle }}</h3>
                                        <p class="mt-1 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $stepBody }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. FAQ                                                       -->
        <!-- ============================================================ -->
        <x-seo.faq-schema :items="$bookingFaqs" />
        <section id="faq" class="scroll-mt-24 bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-balance mb-10 text-center text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>Booking request questions</h2>
                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($bookingFaqs as $faq)
                        <details name="faq" class="group rounded-2xl border border-gray-200 bg-white p-5 transition-colors hover:border-emerald-300 dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-emerald-500/40" data-reveal="panel">
                            <summary class="flex cursor-pointer items-center justify-between gap-4 text-base font-semibold text-gray-900 dark:text-white">
                                {{ $faq['q'] }}
                                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-45" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                            </summary>
                            <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Claim                                                     -->
        <!-- ============================================================ -->
        <section id="claim" class="relative scroll-mt-24 bg-gray-50 px-2 py-16 dark:bg-[#0f0f14] sm:px-4 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-20" data-reveal="panel">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(16, 185, 129, 0.24), rgba(16, 185, 129, 0) 60%); opacity: 0.7;"></div>
                        <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    </div>

                    <div class="relative z-10">
                        <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                            Open the diary. <span class="text-gradient-hold">Keep the pen.</span>
                        </h2>
                        <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                            A booking request form on your schedule page, free on every plan.
                        </p>

                        <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-white px-8 py-4 text-lg font-semibold text-gray-900 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl">
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
