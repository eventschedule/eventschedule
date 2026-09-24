<x-marketing-layout>
    <x-slot name="title">Free Event Registration & RSVP | No Payment Step Needed</x-slot>
    <x-slot name="description">Free event registration and RSVP on every plan: a cap per date, a waitlist when it fills, a QR code in every confirmation and a form you can embed.</x-slot>
    <x-slot name="breadcrumbTitle">Registration</x-slot>

    {{-- Plan claims on this page, checked against docs/FEATURES.md:
         - Free registration / RSVP: unlimited on every tier, never counted against paid tickets
           (FEATURES "Free event registration / RSVP", Event::canAcceptRsvp()).
         - The RSVP waitlist, per-guest individual registration and the ?rsvp=true embed are free
           ("they always have been"). The ticket waitlist, individual TICKETS and the ticket embed
           are Pro.
         - Door scanning is free (TicketController::scan(), no plan check); the live check-in
           dashboard is Pro.
         - Custom questions on the RSVP form are Pro: event/rsvp.blade.php renders the event's
           custom fields only inside @if ($event->isPro()). The phone question (ask_phone /
           require_phone) is saved with no plan scrub, so it is free.
         - Webhooks and the sales CSV export are Pro.
         Behaviour, from TicketController::rsvp(): name and email are required; one registration
         per email per date; without individual registration a sign-up takes one place, with it a
         party of up to 20 (or the places left) each get their own row, email and QR; the cap is
         per date (rsvp_sold is keyed by date); a cancelled registration gives its place back
         (Sale::booted) and wakes the waitlist (NotifyWaitlist, 24-hour offer). Registration
         closes at the end of the event's day in the schedule's timezone. --}}

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule - Free Event Registration and RSVP"
        description="Free event registration with no payment step: a capacity for each date, a waitlist when it fills, a confirmation email with a QR code, self-cancellation, door scanning and an embeddable form, on every plan." />
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to set up free event registration",
        "description": "Turn on registration for an event, set a capacity and share the page.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Open the Tickets section",
                "text": "Edit the event and scroll to the Tickets section of the event editor."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Choose Registration",
                "text": "Select the Registration mode. There is no payment method to connect and nothing to price."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Set a limit if the room has one",
                "text": "Enter a Registration Limit. On a recurring event it applies to each date separately. Leave it blank for no cap."
            },
            {
                "@type": "HowToStep",
                "position": 4,
                "name": "Save and share",
                "text": "Save the event. Its page now shows a Register button, and every registration arrives in your Sales list with a QR code for the door."
            }
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
           Registration "The Door List" styles.

           CONCEPT: the list the person at the door holds. Registration
           exists so that on the night somebody knows who is coming and
           how many chairs to put out, and nobody paid anything to get on
           it. So the hero is the form a guest sees - a count of places
           left, two fields, one button - and the page follows a name
           from that form to the door.

           DELIBERATELY NOT: /features/waitlist owns the queue counter;
           /for-meetup-groups owns the RSVP meter; /features/check-in owns
           the live door dashboard; /features/ticketing owns priced ticket
           types. This page is the no-money half and says so.

           COLOUR: ochre. Amber-800 #92400e on the warm ground, amber-300
           #fcd34d on the dark one. Warm and plain, like a clipboard.
             light ground #faf7f2: ink #1c1712 16.9, muted #57534e 7.1,
                                   accent #92400e 7.6
             dark ground  #110f0c: ink #f3efe8 16.6, muted #a8a29e 7.9,
                                   accent #fcd34d 13.1
           The form card is FIXED white in both modes: it is a picture of
           the guest page, which the guest may see in either theme, and a
           fixed card keeps the device legible on both grounds.
           ============================================================== */

        .es-reg-page { background-color: #faf7f2; color: #1c1712; }
        .dark .es-reg-page { background-color: #110f0c; color: #f3efe8; }

        .es-reg-ink { color: #1c1712; }
        .dark .es-reg-ink { color: #f3efe8; }
        .es-reg-muted { color: #57534e; }
        .dark .es-reg-muted { color: #a8a29e; }
        .es-reg-accent { color: #92400e; }
        .dark .es-reg-accent { color: #fcd34d; }

        .es-reg-rule { border-top: 1px solid rgba(28, 23, 18, 0.10); }
        .dark .es-reg-rule { border-top-color: rgba(243, 239, 232, 0.10); }

        .es-reg-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #92400e;
        }
        .dark .es-reg-tag { color: #fcd34d; }

        .es-reg-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            border: 1.5px solid rgba(146, 64, 14, 0.35);
            font-size: 0.8125rem;
            font-weight: 800;
            color: #92400e;
        }
        .dark .es-reg-mark { border-color: rgba(252, 211, 77, 0.35); color: #fcd34d; }

        .es-reg-panel {
            background-color: #ffffff;
            border: 1px solid rgba(28, 23, 18, 0.10);
            border-radius: 1rem;
        }
        .dark .es-reg-panel {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(243, 239, 232, 0.10);
        }

        .es-reg-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.125rem 0.625rem;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .es-reg-pill-free { background-color: #fef3c7; color: #78350f; }
        .dark .es-reg-pill-free { background-color: rgba(252, 211, 77, 0.14); color: #fcd34d; }
        .es-reg-pill-pro { background-color: #e0f2fe; color: #075985; }
        .dark .es-reg-pill-pro { background-color: rgba(125, 211, 252, 0.14); color: #7dd3fc; }

        /* ---- THE FORM CARD. Fixed in both modes; see the contract above. ---- */
        .es-reg-card {
            background-color: #ffffff;
            color: #1c1712;
            border: 1px solid rgba(28, 23, 18, 0.12);
            border-radius: 1rem;
            box-shadow: 0 18px 45px rgba(28, 23, 18, 0.18);
        }
        .dark .es-reg-card { box-shadow: 0 18px 45px rgba(0, 0, 0, 0.55); }
        .es-reg-card-muted { color: #57534e; }
        .es-reg-card-accent { color: #92400e; }
        .es-reg-field {
            border: 1px solid #d6d3d1;
            border-radius: 0.5rem;
            background-color: #fafaf9;
            padding: 0.55rem 0.75rem;
            font-size: 0.875rem;
            color: #1c1712;
        }
        .es-reg-meter { height: 0.4rem; border-radius: 9999px; background-color: #f5f5f4; overflow: hidden; }
        .es-reg-meter > span { display: block; height: 100%; border-radius: 9999px; background-color: #b45309; }

        .es-reg-band { background-color: #16110a; }
        .es-reg-band-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #fcd34d;
        }
        .es-reg-band-grad {
            background-image: linear-gradient(90deg, #fcd34d, #7dd3fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .es-reg-band .grid-overlay { background-image:
            linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px); }
        .es-reg-band .es-claim:focus-within { border-color: rgba(252, 211, 77, 0.55); }

        #es-reg-page a:focus-visible,
        #es-reg-page summary:focus-visible,
        #es-reg-page button:focus-visible {
            outline: 2px solid #92400e;
            outline-offset: 2px;
        }
        .dark #es-reg-page a:focus-visible,
        .dark #es-reg-page summary:focus-visible,
        .dark #es-reg-page button:focus-visible { outline-color: #fcd34d; }
    </style>

    <div id="es-reg-page" class="es-reg-page">

        <!-- ============================================================ -->
        <!-- 1. Hero: the form a guest sees                               -->
        <!-- ============================================================ -->
        <section id="top" class="relative scroll-mt-24 overflow-hidden py-16 lg:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                    <div class="min-w-0">
                        <h1 class="es-balance es-reg-ink text-4xl font-black tracking-tight md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            <x-marketing.hero-eyebrow class="block es-reg-tag mb-4">Free event registration and RSVP &middot; every plan</x-marketing.hero-eyebrow>
                            Know who is coming. <span class="es-reg-accent">Charge nobody.</span>
                        </h1>
                        <p class="es-reg-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                            For the talk, the open day, the volunteer shift and the meetup that costs nothing to attend. Guests give a name and an email, you set how many the room holds, and everyone who signs up gets a confirmation with a QR code to show at the door.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#92400e] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#78350f]">
                                Start for free
                            </a>
                            <a href="{{ marketing_url('/docs/tickets') }}#registration" class="es-reg-ink inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 font-semibold transition-colors hover:border-[#92400e] dark:border-white/15">
                                Read the guide
                            </a>
                        </div>
                        <p class="es-reg-muted mt-4 text-sm" data-reveal style="--reveal-delay: 0.2s;">
                            Unlimited on every plan, including Free. No payment account to connect.
                        </p>
                    </div>

                    <div class="es-reg-card mx-auto w-full max-w-md p-6 sm:p-8" data-reveal="panel" style="--reveal-delay: 0.1s;" aria-label="Example registration form">
                        <p class="es-reg-card-muted text-xs font-semibold uppercase tracking-widest">Thu 14 Nov &middot; 6:30 PM</p>
                        <p class="mt-1 text-lg font-bold">Neighbourhood planning evening</p>

                        <div class="mt-5 flex items-baseline justify-between text-sm">
                            <span class="es-reg-card-muted">42 of 60 places taken</span>
                            <span class="es-reg-card-accent font-semibold">18 spots remaining</span>
                        </div>
                        <div class="es-reg-meter mt-2" aria-hidden="true"><span style="width: 70%;"></span></div>

                        <div class="mt-6 space-y-3">
                            <div>
                                <p class="es-reg-card-muted mb-1 text-xs font-medium">Name *</p>
                                <div class="es-reg-field">Amara Okafor</div>
                            </div>
                            <div>
                                <p class="es-reg-card-muted mb-1 text-xs font-medium">Email *</p>
                                <div class="es-reg-field" dir="ltr">amara@example.com</div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-lg bg-[#92400e] px-4 py-3 text-center text-sm font-semibold text-white">Register</div>
                        <p class="es-reg-card-muted mt-3 text-center text-xs">No card, no checkout. A confirmation with a QR code follows by email.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. What a registration is                                    -->
        <!-- ============================================================ -->
        <section id="how" class="es-reg-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-reg-mark mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-reg-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">From sign-up to the door</p>
                    <h2 class="es-balance es-reg-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A ticket, <span class="es-reg-accent">without the till.</span>
                    </h2>
                    <p class="es-reg-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A registration behaves like a ticket in every way that matters on the night. It has a holder, a date, a QR code and a place in your Sales list. It just never passes through a payment step, which is why it is free on every plan and there is no ceiling on how many you take.
                    </p>
                </div>

                @php
                    $regSteps = [
                        ['A Register button on the event page', 'Switch an event to Registration and its page swaps the buy button for Register. The form asks for a name and an email, and a phone number too if you ask for one, either as optional or required. Nothing else stands between a guest and a place.'],
                        ['A confirmation with a QR code', 'The moment someone registers, they get an email and a ticket page of their own with a QR code on it. Add registration notes to the event, such as directions, parking or what to bring, and they appear in that email and on the ticket.'],
                        ['A name in your Sales list', 'Every registration lands in the Sales list beside any paid sales you have, with the name, the email and the date it is for. The same email cannot register twice for the same date, so the list is not padded with duplicates.'],
                        ['A scan at the door', 'Open the Sales page on a phone, press Scan Ticket and point the camera at the code. Each registration admits once and a second scan warns you. Scanning is free on every plan, and any team member can do it, viewers included.'],
                    ];
                @endphp
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="80">
                    @foreach ($regSteps as [$rsName, $rsBody])
                        <div class="es-reg-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-reg-ink text-base font-bold">{{ $rsName }}</h3>
                            <p class="es-reg-muted mt-2 text-sm leading-relaxed">{{ $rsBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. Capacity, waitlist, cancellations                         -->
        <!-- ============================================================ -->
        <section id="capacity" class="es-reg-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-reg-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-reg-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Capacity</p>
                    <h2 class="es-balance es-reg-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The room has a size. <span class="es-reg-accent">So does the list.</span>
                    </h2>
                    <p class="es-reg-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Free events overfill in a way paid ones rarely do, because saying yes costs nothing. A limit, a waitlist and a way to back out keep the number you plan for close to the number who arrive.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="90">
                    <div class="es-reg-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-reg-ink text-base font-bold">A limit for each date</h3>
                        <p class="es-reg-muted mt-2 text-sm leading-relaxed">
                            Set a Registration Limit and the form shows how many spots remain. On a recurring event the limit applies to every date on its own, so a limit of 30 means 30 on each Tuesday, not 30 across the series. Leave it blank and the list has no cap.
                        </p>
                    </div>
                    <div class="es-reg-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-reg-ink text-base font-bold">A waitlist when it fills</h3>
                        <p class="es-reg-muted mt-2 text-sm leading-relaxed">
                            A full date shows Join Waitlist instead of Register. When a place frees up, the next person in line is emailed and has 24 hours to take it before the offer moves on. On a registration event this is free on every plan.
                        </p>
                    </div>
                    <div class="es-reg-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-reg-ink text-base font-bold">Guests can back out</h3>
                        <p class="es-reg-muted mt-2 text-sm leading-relaxed">
                            The ticket page linked from the confirmation email has a cancel option. Using it gives the place back to the count straight away, and on a full date it wakes the waitlist, so the chair goes to somebody who still wants it.
                        </p>
                    </div>
                </div>

                <div class="es-reg-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-reg-ink text-base font-bold">Registration closes when the day does</h3>
                    <p class="es-reg-muted mt-2 text-sm leading-relaxed">
                        Sign-ups stay open until the end of the event's day, worked out in your schedule's own timezone, so a guest can still register on the afternoon of an evening talk. A cancelled event stops taking registrations at once.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Parties, questions, the embed                             -->
        <!-- ============================================================ -->
        <section id="options" class="es-reg-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-reg-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-reg-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The details</p>
                    <h2 class="es-balance es-reg-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Groups, questions, <span class="es-reg-accent">and your own website.</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="80">
                    <div class="es-reg-panel flex flex-col p-6" data-reveal="panel">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="es-reg-ink text-base font-bold">Register a party, one QR each</h3>
                            <span class="es-reg-pill es-reg-pill-free">Free</span>
                        </div>
                        <p class="es-reg-muted mt-2 text-sm leading-relaxed">
                            By default one sign-up is one place. Turn on individual registration and a guest can register a group of up to 20 at once, or as many places as are left if that is fewer, giving a name and email for each person. Every one of them gets their own confirmation and their own QR code, so a family can arrive in two cars.
                        </p>
                    </div>
                    <div class="es-reg-panel flex flex-col p-6" data-reveal="panel">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="es-reg-ink text-base font-bold">Embed the form on your site</h3>
                            <span class="es-reg-pill es-reg-pill-free">Free</span>
                        </div>
                        <p class="es-reg-muted mt-2 text-sm leading-relaxed">
                            Add <code dir="ltr">?rsvp=true&amp;embed=true</code> to an event's address and put it in an iframe, and the registration form runs inside your own page. Registration completes in the frame, with nothing to pay and no redirect. On Pro the event editor hands you the snippet ready to copy.
                        </p>
                    </div>
                    <div class="es-reg-panel flex flex-col p-6" data-reveal="panel">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="es-reg-ink text-base font-bold">A phone number, if you need one</h3>
                            <span class="es-reg-pill es-reg-pill-free">Free</span>
                        </div>
                        <p class="es-reg-muted mt-2 text-sm leading-relaxed">
                            Ask for a phone number on the form, as optional or required, for the evening you might have to move at short notice. A box on the form also lets a guest opt in to hearing from your schedule by email. It starts unticked, so the list you build is one people chose to join.
                        </p>
                    </div>
                    <div class="es-reg-panel flex flex-col p-6" data-reveal="panel">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="es-reg-ink text-base font-bold">Your own registration questions</h3>
                            <span class="es-reg-pill es-reg-pill-pro">Pro</span>
                        </div>
                        <p class="es-reg-muted mt-2 text-sm leading-relaxed">
                            Dietary needs, an access requirement, which session they are coming for: <x-link href="{{ marketing_url('/features/custom-fields') }}">custom fields</x-link> put your own questions on the form, as text, a dropdown or a yes-or-no, and the answers sit beside each registration. This is one of the parts of registration that need the Pro plan.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Registration or tickets                                   -->
        <!-- ============================================================ -->
        <section id="or-tickets" class="es-reg-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-reg-mark mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-reg-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Registration or tickets</p>
                    <h2 class="es-balance es-reg-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Which one does <span class="es-reg-accent">this event need?</span>
                    </h2>
                    <p class="es-reg-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Both live in the same Tickets section of the event editor, and you pick one per event. The question is only whether anybody pays.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="80">
                    <div class="es-reg-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-reg-ink text-lg font-bold">Choose Registration when</h3>
                        <ul class="es-reg-muted mt-3 list-disc space-y-2 ps-5 text-sm leading-relaxed">
                            <li>Attendance is free and you only need a headcount and a way to reach people</li>
                            <li>There is one kind of place, rather than early bird, general and VIP</li>
                            <li>You want the waitlist, the embed and per-guest registration on the Free plan</li>
                            <li>It is a talk, a workshop, a volunteer shift, a community meeting or an open day</li>
                        </ul>
                    </div>
                    <div class="es-reg-panel flex flex-col p-6" data-reveal="panel">
                        <h3 class="es-reg-ink text-lg font-bold">Choose Tickets when</h3>
                        <ul class="es-reg-muted mt-3 list-disc space-y-2 ps-5 text-sm leading-relaxed">
                            <li>Anyone pays anything, which puts a price on a ticket type and needs the Pro plan</li>
                            <li>You want several ticket types, including a free one beside the paid ones</li>
                            <li>You need promo codes, add-ons, passes or reserved seating</li>
                            <li>You take payment by card, PayPal, a payment link or cash</li>
                        </ul>
                        <a href="{{ marketing_url('/features/ticketing') }}" class="es-reg-accent mt-auto inline-flex items-center pt-5 text-sm font-semibold hover:underline">
                            See how ticketing works
                            <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <div class="es-reg-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-reg-ink text-base font-bold">What stays free, and what Pro adds</h3>
                    <p class="es-reg-muted mt-2 text-sm leading-relaxed">
                        Free on every plan: registration with no cap on how many you take, the Registration Limit, the waitlist on a full registration date, per-guest registration, the embeddable form, confirmation emails with QR codes, self-cancellation and scanning at the door. Pro adds your own <x-link href="{{ marketing_url('/features/custom-fields') }}">registration questions</x-link>, the live <x-link href="{{ marketing_url('/features/check-in') }}">check-in dashboard</x-link>, the sales CSV export and webhooks for each registration. A selfhosted install includes all of it.
                    </p>
                </div>

                <div class="mt-10 text-center">
                    <a href="{{ marketing_url('/features') }}" class="es-reg-accent inline-flex items-center font-medium hover:underline">
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
        <!-- 6. FAQ                                                       -->
        <!-- ============================================================ -->
        @php
            $registrationFaqs = [
                ['q' => 'Is event registration really free?', 'a' => 'Yes. Free registration is unlimited on every plan, including Free, and it never counts against anything. There is no per-registration fee and no monthly ceiling. What costs money is putting a price on a ticket, which is the Pro plan.'],
                ['q' => 'How do I turn on registration for an event?', 'a' => 'Edit the event, go to the Tickets section and choose Registration. Set a Registration Limit if the room has one, then save. The event page now shows a Register button, and each registration arrives in your Sales list.', 'link' => [marketing_url('/docs/tickets').'#registration', 'Read the registration guide']],
                ['q' => 'What do guests have to fill in?', 'a' => 'A name and an email address. You can also ask for a phone number, as optional or required. On the hosted service a guest who is not signed in can create an account while registering, but they never have to.'],
                ['q' => 'Can one person register several people?', 'a' => 'Yes, with individual registration switched on, which is free. A guest can then register up to 20 people at once, or however many places are left if that is fewer, with a name and email for each. Every person gets their own confirmation email and QR code. Without it, each sign-up takes one place.'],
                ['q' => 'What happens when an event is full?', 'a' => 'The Register button becomes Join Waitlist. When someone cancels, the next person on the waitlist is emailed and has 24 hours to take the place before it passes to the next in line. The waitlist on a registration event is free on every plan.'],
                ['q' => 'Can guests cancel their registration?', 'a' => 'Yes. The ticket page linked from their confirmation email lets them cancel, which returns the place to the count immediately and, on a full date, offers it to the waitlist.'],
                ['q' => 'Does a recurring event share one limit across every date?', 'a' => 'No. The Registration Limit is per date. A weekly class with a limit of 12 takes 12 registrations on each date, and each date fills, and waitlists, on its own.'],
                ['q' => 'Can I ask my own questions on the registration form?', 'a' => 'Yes, on the Pro plan. Custom fields add your own questions to the form, and the answers show beside each registration. The phone number question is free on every plan.'],
                ['q' => 'Can I put the registration form on my own website?', 'a' => 'Yes, on every plan. Take the event page address, add ?rsvp=true&embed=true, and use it as the src of an iframe. The form works inside the frame. On Pro, the event editor has an Embed Registration link that writes the code for you.', 'link' => [marketing_url('/docs/tickets').'#embed-widget', 'How the embed works']],
                ['q' => 'How is this different from selling tickets?', 'a' => 'Registration has no payment step and one kind of place. Tickets let you set several ticket types with prices, promo codes and add-ons, and take payment through Stripe, PayPal and other methods; a priced ticket needs the Pro plan. Both scan the same way at the door.'],
            ];
        @endphp
        <section id="faq" class="es-reg-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-reg-ink mb-10 text-center text-3xl font-black tracking-tight md:text-4xl" data-reveal>Registration questions</h2>
                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($registrationFaqs as $faq)
                        <details class="es-reg-panel group p-5" data-reveal="panel">
                            <summary class="es-reg-ink flex cursor-pointer items-center justify-between gap-4 text-base font-semibold">
                                {{ $faq['q'] }}
                                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-45" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                            </summary>
                            <p class="es-reg-muted mt-3 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                            @if (! empty($faq['link']))
                                <a href="{{ $faq['link'][0] }}" class="es-reg-accent mt-3 inline-block text-sm font-semibold hover:underline">{{ $faq['link'][1] }}</a>
                            @endif
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$registrationFaqs" />

        <!-- ============================================================ -->
        <!-- 7. Claim                                                     -->
        <!-- ============================================================ -->
        <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="es-reg-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-20" data-reveal="panel">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="grid-overlay absolute inset-0 opacity-25"></div>
                    </div>

                    <div class="relative z-10">
                        <p class="es-reg-band-tag mb-6">Free to start</p>
                        <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                            Open the list. <span class="es-reg-band-grad">Keep the door.</span>
                        </h2>
                        <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                            Free registration for as many people as you can fit, on every plan.
                        </p>

                        <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-events" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg bg-white px-8 py-4 text-lg font-semibold text-[#16110a] transition-colors hover:bg-gray-100">
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
