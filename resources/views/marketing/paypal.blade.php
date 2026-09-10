<x-marketing-layout>
    <x-slot name="title">Sell Event Tickets with PayPal | Free on Every Plan</x-slot>
    <x-slot name="description">Sell tickets through your own PayPal account on any plan, with no platform fee. Every payment is read back from PayPal, and refunds can be full or partial.</x-slot>
    <x-slot name="breadcrumbTitle">PayPal</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - PayPal Integration",
        "description": "Sell event tickets through your own PayPal business account. Available on every plan with no platform fee, with credentials verified before they are stored and every payment confirmed by reading the capture back from PayPal.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "featureList": [
            "Connect your own PayPal business account",
            "Available on every plan, including Free",
            "No platform fee on ticket sales",
            "Credentials verified with PayPal before they are stored",
            "The seats are re-checked before the money is taken",
            "Payment confirmed by reading the capture back from PayPal",
            "Seats held, not resold, while PayPal reviews a payment",
            "Refunds, full or partial, issued against the real capture",
            "One order and one capture across a multi-event cart",
            "Works alongside Stripe, Payfast, Invoice Ninja, a payment link or cash"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free on every plan, with no platform fee on ticket sales"
        }
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
           PayPal "The Last Check" styles.

           CONCEPT: THE BEAT BETWEEN YES AND PAID. Every other gateway
           here is called after the money has already moved. PayPal is
           the one where the capture IS the money, so there is a real
           moment - after the buyer approves and before anything is
           taken - in which the room can be read again. Event Schedule
           uses it: if the seats went while the buyer was away, nothing
           is captured. The page is that moment, drawn as the short
           ladder of steps it actually is.

           WHY NOT "the button" or "the wallet". The obvious PayPal page
           is its checkout button, and it would be a LIE by omission:
           Event.payment_method is a single select, so an event has ONE
           gateway and there is no row of buttons at checkout. Anything
           built on "give buyers more ways to pay AT CHECKOUT" would be
           describing a product we do not ship.

           DELIBERATELY NOT: /stripe owns "The Payout" and the money
           arriving; /invoiceninja owns "The Ledger"; /features/ticketing
           owns "The Turnstile"; /features/check-in owns the ops screen.
           This page owns the VERIFICATION, which is the one thing the
           PayPal driver genuinely does differently from its siblings.

           THE SLIP IS A PHYSICAL OBJECT - a terminal slip is dark
           whatever the reader's theme - so .es-pp-slip is FIXED in both
           colour modes and only the room around it changes.

           COLOUR: amber-800, and the reasoning is a neighbour test
           rather than a free choice. The money family is spent -
           /stripe navy #0f4c81, /invoiceninja green #0a6a44,
           /features/ticketing sky #075985, /outlook-calendar azure
           #005a9e, /google-calendar blue #1558c0 - and PayPal's own
           blue would read as a fourth blue in a row of blues. The
           nearest orange pages, /features/boost #c2410c and
           /features/custom-fields #9a3412, sit ~23deg away, are far more
           saturated, and are in a different family with no link either
           way.

           Measured against the grounds this page actually paints:
             light ground #f6f5f2: ink #14120e 17.16, muted #4b5563 6.93,
                                   accent #854d0e 6.28
             dark ground  #0d0c0a: ink #e9e6e0 15.70, muted #a09a90 7.00,
                                   accent #fcd34d 13.56
             slip #16140f (fixed): ink #e9e6e0 14.78, muted #9a938a 6.06,
                                   accent #fcd34d 12.76
           amber-700 #a16207 was rejected at 4.52 on this ground - it
           passes AA with no headroom, which is how text-gray-500 got
           onto pages it should not have been on. Use .es-pp-muted.
           ============================================================== */

        .es-pp-page { background-color: #f6f5f2; color: #14120e; }
        .dark .es-pp-page { background-color: #0d0c0a; color: #e9e6e0; }

        .es-pp-ink { color: #14120e; }
        .dark .es-pp-ink { color: #e9e6e0; }
        .es-pp-muted { color: #4b5563; }
        .dark .es-pp-muted { color: #a09a90; }
        .es-pp-accent { color: #854d0e; }
        .dark .es-pp-accent { color: #fcd34d; }

        .es-pp-rule { border-top: 1px solid rgba(20, 18, 14, 0.10); }
        .dark .es-pp-rule { border-top-color: rgba(233, 230, 224, 0.10); }

        .es-pp-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #854d0e;
        }
        .dark .es-pp-tag { color: #fcd34d; }

        .es-pp-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            border: 1.5px solid rgba(133, 77, 14, 0.35);
            font-size: 0.8125rem;
            font-weight: 800;
            color: #854d0e;
        }
        .dark .es-pp-mark { border-color: rgba(252, 211, 77, 0.35); color: #fcd34d; }

        .es-pp-panel {
            background-color: #ffffff;
            border: 1px solid rgba(20, 18, 14, 0.10);
            border-radius: 1rem;
        }
        .dark .es-pp-panel {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(233, 230, 224, 0.10);
        }

        /* ---- THE SLIP. Fixed in both modes; see the contract above. ---- */
        .es-pp-slip {
            background-color: #16140f;
            color: #e9e6e0;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 1rem;
            box-shadow: 0 18px 45px rgba(9, 8, 6, 0.45);
        }
        .es-pp-slip-ink { color: #e9e6e0; }
        .es-pp-slip-muted { color: #9a938a; }
        .es-pp-slip-accent { color: #fcd34d; }
        .es-pp-slip-rule { border-top: 1px solid rgba(255, 255, 255, 0.09); }

        /* The ladder of steps. The current rung is marked by SHAPE - a solid
           pip against hollow ones - so it survives a mono screen. */
        .es-pp-step {
            width: 0.6rem;
            height: 0.6rem;
            border-radius: 9999px;
            border: 1.5px solid rgba(233, 230, 224, 0.45);
            flex: none;
        }
        .es-pp-step-now { background-color: #fcd34d; border-color: #fcd34d; }
        .es-pp-step-done { background-color: rgba(233, 230, 224, 0.55); border-color: rgba(233, 230, 224, 0.55); }

        .es-pp-band { background-color: #17130c; }
        .es-pp-band-tag {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #fcd34d;
        }
        .es-pp-band-grad {
            background-image: linear-gradient(90deg, #fcd34d, #fdba74);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        /* Shared classes carry their own .dark rules in marketing.css and would
           otherwise change inside a band that has none. */
        .es-pp-band .grid-overlay { background-image:
            linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px); }
        .es-pp-band .es-claim:focus-within { border-color: rgba(252, 211, 77, 0.55); }

        /* marketing.css:248 only rings a.feature-card|bento-card|persona-card. */
        #es-pp-page a:focus-visible,
        #es-pp-page summary:focus-visible,
        #es-pp-page button:focus-visible {
            outline: 2px solid #854d0e;
            outline-offset: 2px;
        }
        .dark #es-pp-page a:focus-visible,
        .dark #es-pp-page summary:focus-visible,
        .dark #es-pp-page button:focus-visible { outline-color: #fcd34d; }
    </style>

    <div id="es-pp-page" class="es-pp-page">

        <!-- ============================================================ -->
        <!-- 1. Hero: the slip                                            -->
        <!-- ============================================================ -->
        <section id="top" class="relative scroll-mt-24 overflow-hidden py-16 lg:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <p class="es-pp-tag mb-4" data-reveal>PayPal &middot; free on every plan</p>
                        <h1 class="es-balance es-pp-ink text-4xl font-black tracking-tight md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            Take the money <span class="es-pp-accent">into your own account.</span>
                        </h1>
                        <p class="es-pp-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                            Connect the PayPal business account you already have and sell tickets through it. Event Schedule takes no platform fee on any plan, including Free, and never holds the money on its way to you.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#854d0e] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#6f4009]">
                                Start for free
                            </a>
                            <a href="{{ marketing_url('/docs/tickets') }}#paypal" class="es-pp-ink inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 font-semibold transition-colors hover:border-[#854d0e] dark:border-white/15">
                                Read the guide
                            </a>
                        </div>
                        <p class="es-pp-muted mt-4 text-sm" data-reveal style="--reveal-delay: 0.2s;">
                            PayPal's own processing fee is the only deduction, and it is PayPal's.
                        </p>
                    </div>

                    @php
                        // The real handleReturn() sequence, in order. Fixed, never random: the page
                        // has to render identically on every request for the band-diff verifier.
                        $ppSteps = [
                            ['done', 'Buyer approves the order at PayPal'],
                            ['now', 'The seats are checked again, before anything is taken'],
                            ['todo', 'The capture is requested'],
                            ['todo', 'The result is read back from PayPal and the sale settles'],
                        ];
                    @endphp
                    <div class="es-pp-slip p-6 sm:p-8" data-reveal="panel" style="--reveal-delay: 0.1s;">
                        <p class="es-pp-slip-muted text-xs font-semibold uppercase tracking-widest">Checkout &middot; returning from PayPal</p>
                        <p class="es-pp-slip-ink mt-4 text-2xl font-black leading-tight">Nothing is taken on trust</p>
                        <ol class="mt-6 space-y-4">
                            @foreach ($ppSteps as [$stepState, $stepLabel])
                                <li class="flex items-start gap-3">
                                    <span class="es-pp-step {{ $stepState === 'now' ? 'es-pp-step-now' : ($stepState === 'done' ? 'es-pp-step-done' : '') }} mt-1.5" aria-hidden="true"></span>
                                    <span class="{{ $stepState === 'now' ? 'es-pp-slip-accent font-semibold' : 'es-pp-slip-muted' }} text-sm leading-relaxed">{{ $stepLabel }}</span>
                                </li>
                            @endforeach
                        </ol>
                        <div class="es-pp-slip-rule mt-6 pt-4">
                            <p class="es-pp-slip-muted text-xs leading-relaxed">
                                Every other gateway here is called once the money has already moved. On PayPal the capture is the money, so the check has to come first.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. What is checked, and when                                 -->
        <!-- ============================================================ -->
        <section id="checks" class="es-pp-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-pp-mark mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-pp-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Verification</p>
                    <h2 class="es-balance es-pp-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Checked <span class="es-pp-accent">three times over.</span>
                    </h2>
                    <p class="es-pp-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A payment integration has three places it can quietly go wrong: the keys, the moment of capture, and afterwards. Each one is checked against PayPal itself rather than assumed.
                    </p>
                </div>

                @php
                    $ppChecks = [
                        ['Your keys, before they are stored', 'Paste a client ID and secret and they are tried against PayPal before the form will save them. A typo is refused there and then, by you, instead of surfacing later as a buyer who cannot pay. If the check itself cannot run, the keys are saved and you are told plainly that we could not verify them - unknown is not the same as wrong.'],
                        ['The seats, before the capture', 'Between approving at PayPal and coming back, a buyer can close the tab, wander off, and return after the hold expired. The sale is re-checked first, and if it can no longer be paid nothing is captured. Taking the payment and only then discovering the seats had gone is the outcome this exists to prevent.'],
                        ['The payment, after the fact', 'The sale settles from the capture read back from PayPal, not from a message we were sent. That is also why a refreshed return page or a webhook arriving first is harmless: an order already captured is read back and settled from the capture that exists, rather than treated as a failure and refunded to nobody.'],
                    ];
                @endphp
                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                    @foreach ($ppChecks as [$ckName, $ckBody])
                        <div class="es-pp-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-pp-ink text-base font-bold">{{ $ckName }}</h3>
                            <p class="es-pp-muted mt-2 text-sm leading-relaxed">{{ $ckBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. Where it sits among the others                            -->
        <!-- ============================================================ -->
        <section id="place" class="es-pp-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-pp-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-pp-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">One per event</p>
                    <h2 class="es-balance es-pp-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Pick it <span class="es-pp-accent">event by event.</span>
                    </h2>
                    <p class="es-pp-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Connect the accounts you use in Settings, then choose which one an event takes money through. An event uses one method at a time, so this is a decision you make per event rather than a row of buttons at the checkout.
                    </p>
                </div>

                @php
                    $ppFacts = [
                        ['Free on every plan', 'There is no plan check anywhere in the payments code. PayPal is available on Free exactly as it is on Enterprise, and Event Schedule adds no fee of its own to any of them.'],
                        ['Refunds move real money', 'From the Sales page, a full or partial refund is issued against the capture PayPal actually took, and the sale only changes once the money has gone back. A partial refund leaves the sale paid and its tickets valid.'],
                        ['One order, one capture', 'A multi-event cart pays as a single amount, so a buyer taking tickets for three of your nights approves once and is charged once.'],
                        ['What it does not do', 'Installment plans are Stripe-only, and gift cards and appointment bookings cannot be paid through PayPal either. HUF, JPY and TWD are excluded deliberately, because PayPal rejects a decimal amount in all three and our pricing path can produce one.'],
                    ];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2" data-reveal-group="80">
                    @foreach ($ppFacts as [$fName, $fBody])
                        <div class="es-pp-panel flex flex-col p-6" data-reveal="panel">
                            <h3 class="es-pp-ink text-base font-bold">{{ $fName }}</h3>
                            <p class="es-pp-muted mt-2 text-sm leading-relaxed">{{ $fBody }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="es-pp-panel mt-8 p-6" data-reveal="panel">
                    <h3 class="es-pp-ink text-base font-bold">Selfhosting? One account for the whole install</h3>
                    <p class="es-pp-muted mt-2 text-sm leading-relaxed">
                        An operator can supply PayPal credentials in the environment and every schedule on the install uses them by default. It is a default rather than an override: a schedule owner who connects their own account keeps using theirs. The webhook id is optional, because a payment is confirmed by reading the capture back rather than by trusting a signature. The listener itself is not: it is registered for owners who connect their own account, but with install-wide credentials the operator adds it, and without one a payment PayPal holds for review stays unresolved.
                    </p>
                </div>
            </div>
        </section>

        @include('marketing.partials.pricing-nudge')

        <!-- ============================================================ -->
        <!-- 4. FAQ                                                       -->
        <!-- ============================================================ -->
        @php
            $ppFaqs = [
                ['q' => 'Does Event Schedule take a cut of my ticket sales?', 'a' => 'No. There is no platform fee on any plan. The only deduction is PayPal\'s own processing fee, which is between you and PayPal, and the money goes into your own PayPal account rather than through ours.'],
                ['q' => 'Do I need a paid plan to use PayPal?', 'a' => 'No. Every payment gateway is available on every tier - there is no plan check anywhere in the payments code. The free plan sells up to 25 paid tickets a calendar month; Pro removes that ceiling. The gateway itself is never what you are paying for.'],
                ['q' => 'Can one event offer both PayPal and Stripe?', 'a' => 'No. An event uses one payment method at a time, chosen on the event itself, so you pick per event rather than showing a row of buttons at checkout. You can connect several accounts and use different ones on different events.'],
                ['q' => 'What happens if I paste the wrong secret?', 'a' => 'The settings form refuses it. Your client ID and secret are tried against PayPal before they are stored, so a typo is caught by you rather than by the first person who tries to buy a ticket. If the check cannot be run at all, the keys are saved and you are told that we could not verify them.'],
                ['q' => 'What if a buyer approves the payment and then disappears?', 'a' => 'Nothing is captured. The sale is re-checked when they come back, and if it has expired or been released in the meantime, the payment is not taken. This matters more on PayPal than on other gateways, because the capture is the moment the money moves rather than something that already happened.'],
                ['q' => 'What if PayPal holds a payment for review?', 'a' => 'The sale waits instead of expiring. PayPal sometimes reviews a payment before completing it, which is routine on a new merchant account, and by then the money has left the buyer, so their seats stay held rather than going back on sale. The buyer is told the payment is under review, and their ticket page stops offering to take it again. When PayPal completes it, the sale settles and the ticket is emailed, which is the one step that arrives by webhook.'],
                ['q' => 'Do I have to set up webhooks?', 'a' => 'No. A listener is registered for you where it can be, but the ordinary return path settles the sale on its own by reading the capture back from PayPal. The webhook only earns its keep in the late cases - a capture that completes later, or one whose response was lost.'],
                ['q' => 'Which currencies work?', 'a' => 'PayPal\'s own list, minus HUF, JPY and TWD. Those three are excluded on purpose: PayPal rejects a decimal amount in all of them, and a discounted ticket can produce one, which would leave money captured and a ticket withheld. PayPal does not settle South African rand at all, so an event priced in rand can use Payfast instead.'],
                ['q' => 'Can I refund through it?', 'a' => 'Yes, in full or in part, from the Sales page. The refund is issued against the capture PayPal took and the sale changes only once the money has actually gone back. A partial refund leaves the sale paid with its tickets valid, and the Sales page shows how much has been returned so far. A PayPal sale that was marked paid by hand has no capture to refund against, so it offers Mark as Refunded instead, which records the refund without moving money. Refund here rather than in PayPal: a refund raised in PayPal\'s own dashboard is not reported back.'],
            ];
        @endphp
        <section id="faq" class="es-pp-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-pp-ink mb-10 text-center text-3xl font-black tracking-tight md:text-4xl" data-reveal>Questions</h2>
                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($ppFaqs as $faq)
                        <details class="es-pp-panel group p-5" data-reveal="panel">
                            <summary class="es-pp-ink flex cursor-pointer items-center justify-between gap-4 text-base font-semibold">
                                {{ $faq['q'] }}
                                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-45" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                            </summary>
                            <p class="es-pp-muted mt-3 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$ppFaqs" />

        <!-- ============================================================ -->
        <!-- 5. Claim                                                     -->
        <!-- ============================================================ -->
        <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="es-pp-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-20" data-reveal="panel">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="grid-overlay absolute inset-0 opacity-25"></div>
                    </div>

                    <div class="relative z-10">
                        <p class="es-pp-band-tag mb-6">Free to start</p>
                        <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                            Your account, <span class="es-pp-band-grad">your money</span>.
                        </h2>
                        <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                            Connect PayPal, put the tickets on sale, and keep every cent we would otherwise have taken.
                        </p>

                        <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg bg-white px-8 py-4 text-lg font-semibold text-[#17130c] transition-colors hover:bg-gray-100">
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
