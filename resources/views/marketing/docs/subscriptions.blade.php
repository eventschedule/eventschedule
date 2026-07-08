<x-marketing-layout>
    <x-slot name="title">Subscriptions & Passes - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Subscriptions & Passes</x-slot>
    <x-slot name="description">Sell one pass that a guest pays for once and reuses across many events. Set up visit passes, memberships, festival passes, and season passes, then redeem and track them.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Subscriptions & Passes - Event Schedule",
        "description": "How to sell a multi-use pass or subscription: one purchase, one QR code, valid across many events. Includes setup, redeeming at the door, and usage tracking.",
        "author": {
            "@type": "Organization",
            "name": "Event Schedule"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Event Schedule",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ config('app.url') }}/images/light_logo.png",
                "width": 712,
                "height": 140
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}"
        },
        "datePublished": "2026-06-11",
        "dateModified": "2026-06-11"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <style {!! nonce_attr() !!}>
        .text-gradient-docs {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-docs {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5 noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 20% 45%, rgba(6, 182, 212, 0.22), rgba(6, 182, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 80% 55%, rgba(20, 184, 166, 0.18), rgba(20, 184, 166, 0) 65%);"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Subscriptions & Passes" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776" />
                    </svg>
                </div>
                <h1 class="es-fade-up es-balance text-3xl md:text-4xl font-black tracking-tight text-gray-900 dark:text-white"><span class="text-gradient-docs">Subscriptions &amp; Passes</span></h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Sell one pass that a guest pays for once and reuses across many of your events - like a class pack, a membership, or a festival wristband.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="bg-white dark:bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">On this page</div>
                        <a href="#overview" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">What is a subscription?</a>
                        <a href="#how-it-works" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">How it works</a>
                        <a href="#example" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">A worked example</a>
                        <div class="doc-nav-group expanded">
                            <a href="#setup" class="doc-nav-group-header doc-nav-link">Step 1 - Create the pass <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#types" class="doc-nav-link">Subscription types</a>
                                <a href="#coverage" class="doc-nav-link">Covered events</a>
                            </div>
                        </div>
                        <a href="#buying" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Step 2 - Buyers purchase</a>
                        <a href="#admissions-per-event" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Admissions per event</a>
                        <div class="doc-nav-group">
                            <a href="#redeeming" class="doc-nav-group-header doc-nav-link">Step 3 - Scan at the door <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#scan-results" class="doc-nav-link">What the scanner shows</a>
                            </div>
                        </div>
                        <a href="#monitoring" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Step 4 - Track usage</a>
                        <a href="#good-to-know" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Good to know</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">

                        <!-- What is a subscription -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                </svg>
                                What is a subscription?
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">A <strong class="text-gray-900 dark:text-white">subscription</strong> (also called a <strong class="text-gray-900 dark:text-white">pass</strong>) is a special ticket your guest pays for <strong class="text-gray-900 dark:text-white">once</strong> and then reuses to get into <strong class="text-gray-900 dark:text-white">several of your events</strong> with a single QR code. Think of it like a gym membership, a 10-class punch card, or a festival wristband.</p>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">It helps to compare it to the two things you may already know:</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">A normal ticket</strong> gets one person into <em>one</em> event.</li>
                                <li><strong class="text-gray-900 dark:text-white">A subscription</strong> gets one person into <em>many</em> events - you decide how many visits it's worth and which events it covers.</li>
                            </ul>
                            <div class="doc-callout doc-callout-info mb-6">
                                <div class="doc-callout-title">It's a one-time purchase, not a recurring charge</div>
                                <p>The buyer pays once. Event Schedule does not automatically bill them again - a subscription here is a multi-use pass, not an auto-renewing card on file. When it runs out of visits or expires, they simply buy another.</p>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300">Subscriptions are part of ticketing, which is a <strong class="text-gray-900 dark:text-white">Pro</strong> feature.</p>
                        </section>

                        <!-- How it works -->
                        <section id="how-it-works" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                How it works
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">There are four stages, and the rest of this page walks through each one:</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li><strong class="text-gray-900 dark:text-white">You create the pass</strong> - add a ticket, mark it as a subscription, and choose how many visits it's worth and which events it covers.</li>
                                <li><strong class="text-gray-900 dark:text-white">A guest buys it once</strong> and receives a single QR code.</li>
                                <li><strong class="text-gray-900 dark:text-white">Your staff scan the QR</strong> at each event - every scan records one visit.</li>
                                <li><strong class="text-gray-900 dark:text-white">You watch the usage</strong> on the Subscriptions tab.</li>
                            </ol>
                            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-2">
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-0">Buy once &rarr; scan at Event A <span class="text-gray-400">(visit 1)</span> &rarr; scan at Event B <span class="text-gray-400">(visit 2)</span> &rarr; &hellip; until the visit limit or the expiry date is reached.</p>
                            </div>
                        </section>

                        <!-- Worked example -->
                        <section id="example" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                </svg>
                                A worked example
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Meet <strong class="text-gray-900 dark:text-white">Maria</strong>, who runs a yoga studio with classes most days. She wants to sell a <strong class="text-gray-900 dark:text-white">10-Class Pass</strong> for $120 instead of charging per class. Here's how she uses subscriptions:</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Maria creates an event called <strong class="text-gray-900 dark:text-white">"Class Passes"</strong> just to sell the pass on.</li>
                                <li>On that event she adds a ticket called <strong class="text-gray-900 dark:text-white">"10-Class Pass" ($120)</strong> and turns on <strong class="text-gray-900 dark:text-white">"This is a pass or subscription"</strong>.</li>
                                <li>She picks type <strong class="text-gray-900 dark:text-white">Visit pass</strong>, sets <strong class="text-gray-900 dark:text-white">10 visits</strong>, makes it <strong class="text-gray-900 dark:text-white">valid for 90 days</strong>, and sets coverage to <strong class="text-gray-900 dark:text-white">All events in this schedule</strong>.</li>
                                <li>A student buys the pass once and gets a QR code by email.</li>
                                <li>At each class, the front desk opens the scanner, picks the class as the "Scanning at event", and scans the student's QR - the screen shows <strong class="text-gray-900 dark:text-white">"Visit 1 of 10"</strong>, then <strong class="text-gray-900 dark:text-white">"Visit 2 of 10"</strong>, and so on. After ten classes it shows <strong class="text-gray-900 dark:text-white">"All visits used"</strong>.</li>
                                <li>Maria opens the <strong class="text-gray-900 dark:text-white">Subscriptions</strong> tab any time to see who bought a pass and how many classes they've attended.</li>
                            </ol>
                            <p class="text-gray-600 dark:text-gray-300">That's the whole feature in one story. The sections below explain each choice.</p>
                        </section>

                        <!-- Step 1: Create the pass -->
                        <section id="setup" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                                </svg>
                                Step 1 - Create the pass
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">A subscription is just a ticket type with a switch turned on. To create one:</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Edit the event you want to sell the pass on, and open the <strong class="text-gray-900 dark:text-white">Tickets</strong> section.</li>
                                <li>Choose the <strong class="text-gray-900 dark:text-white">Tickets</strong> mode and add a ticket type (give it a name and price).</li>
                                <li>Turn on <strong class="text-gray-900 dark:text-white">"This is a pass or subscription (multi-use)"</strong>.</li>
                                <li>Pick a <strong class="text-gray-900 dark:text-white">Subscription type</strong>, set the number of visits and/or how long it stays valid, and choose which <strong class="text-gray-900 dark:text-white">events it covers</strong>.</li>
                                <li>Save the event.</li>
                            </ol>

                            <h3 id="types" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subscription types</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">The type decides how many times the pass can be used:</p>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>What it does</th>
                                            <th>Use it for</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Visit pass</span></td>
                                            <td>A set number of visits (for example 10). Each visit is one event-day.</td>
                                            <td>Class packs, punch cards, "10 entries" bundles</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Membership</span></td>
                                            <td>Unlimited visits until an expiry date you set.</td>
                                            <td>Monthly or annual memberships</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Festival pass</span></td>
                                            <td>One visit to each covered event.</td>
                                            <td>Multi-day festivals, a conference series</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Season pass</span></td>
                                            <td>Every occurrence of one recurring event, once per date. Only offered on recurring events.</td>
                                            <td>A weekly class or a recurring show</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">You can also set <strong class="text-gray-900 dark:text-white">Valid for (days)</strong> - the pass expires that many days after purchase. Leave it blank for no expiry.</p>

                            <h3 id="coverage" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Covered events</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Coverage decides <em>which</em> events the pass works at:</p>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Coverage</th>
                                            <th>What it means</th>
                                            <th>Good to know</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">All events in this schedule</span></td>
                                            <td>The pass works at every event you run.</td>
                                            <td>Events you create later are covered automatically.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">All events in a sub-schedule</span></td>
                                            <td>The pass works at every event in a category you choose.</td>
                                            <td>Future events in that category are covered automatically.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Specific events</span></td>
                                            <td>The pass works only at the events you hand-pick.</td>
                                            <td>A fixed list - new events are not added automatically.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-tip mb-6">
                                <div class="doc-callout-title">The "Subscriptions event" pattern</div>
                                <p>The tidiest way to sell a pass is to create one event just for it - for example "Memberships" or "Class Passes" - put the pass ticket there, and set its coverage to your real events. Keep that selling event off your public schedule by marking it unlisted or private (Event &rarr; Settings &rarr; Privacy) so it doesn't look like a real event on your calendar.</p>
                            </div>
                            <div class="doc-callout doc-callout-info mb-6">
                                <div class="doc-callout-title">One pass per order</div>
                                <p>A pass is a single redeemable unit, so it's sold one at a time. To buy passes as gifts, place a separate order for each.</p>
                            </div>
                        </section>

                        <!-- Step 2: Buyers purchase -->
                        <section id="buying" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                </svg>
                                Step 2 - Buyers purchase
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">For the guest, buying a pass is exactly like buying a normal ticket - they pick it, pay, and get a confirmation email.</p>
                            <p class="text-gray-600 dark:text-gray-300 mb-2">Their ticket page (and the QR code on it) clearly shows what they bought:</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Visits remaining</strong> - for example "Visits used 2 / 10", or "Unlimited".</li>
                                <li><strong class="text-gray-900 dark:text-white">Valid until</strong> - the expiry date, if you set one.</li>
                                <li><strong class="text-gray-900 dark:text-white">Covered events</strong> - the list of events the pass works at, so they know where to use it.</li>
                            </ul>
                        </section>

                        <section id="advance-booking" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                Advance booking
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">By default a pass is scan-at-the-door: the holder just turns up and scans in. If you want holders to reserve a seat for specific dates ahead of time, turn on <strong class="text-gray-900 dark:text-white">"Let holders book seats in advance"</strong> on the pass.</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">One shared pool of seats</strong> - advance bookings and regular ticket sales draw from the same capacity, so you can never oversell. If the room seats 50 and 30 holders book ahead, 20 seats remain for regular buyers.</li>
                                <li><strong class="text-gray-900 dark:text-white">Optional per-date cap</strong> - limit how many seats holders may reserve per date, keeping some walk-up inventory aside.</li>
                                <li><strong class="text-gray-900 dark:text-white">Holders book from their pass page</strong> - the private link in their confirmation email lists upcoming dates with seats left; they book or cancel a date themselves, and each booking counts as one visit until they hit their limit.</li>
                                <li><strong class="text-gray-900 dark:text-white">Booked or attended</strong> - the Subscriptions tab shows which dates a holder has reserved versus actually attended, and the check-in screen shows how many seats are reserved for the date.</li>
                            </ul>
                        </section>

                        <!-- Admissions per event -->
                        <section id="admissions-per-event" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                                Admissions per event
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">By default a pass admits one person - the holder. To let the holder bring a guest (or a few), set <strong class="text-gray-900 dark:text-white">Admissions per event</strong> when you create the pass. It's the total number of people who can enter at each event, <strong class="text-gray-900 dark:text-white">including the holder</strong>: leave it at <strong class="text-gray-900 dark:text-white">1</strong> for holder-only, or set <strong class="text-gray-900 dark:text-white">2</strong> so they can bring one guest.</p>
                            <div class="doc-callout doc-callout-info mb-6">
                                <div class="doc-callout-title">A guest doesn't use up a visit</div>
                                <p>Party size is separate from the visit count. A Visit pass good for 10 visits with 2 admissions per event still counts each event as a <strong class="text-gray-900 dark:text-white">single</strong> visit - so the holder can bring a guest to all 10 events.</p>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">At the door it's one scan per person:</p>
                            <ul class="doc-list mb-6">
                                <li>Scan the QR once for each person, up to the limit. The scanner shows <strong class="text-gray-900 dark:text-white">"Admitted 1 of 2"</strong> with <strong class="text-gray-900 dark:text-white">"Scan again to admit the guest"</strong>, then <strong class="text-gray-900 dark:text-white">"Admitted 2 of 2"</strong> and <strong class="text-gray-900 dark:text-white">"All guests admitted"</strong>.</li>
                                <li>If the group arrives together, tap the <strong class="text-gray-900 dark:text-white">"Admit guest"</strong> button instead of pointing the camera at the same QR again.</li>
                                <li>Once every admission is used, a further scan reads <strong class="text-gray-900 dark:text-white">"All 2 admissions already used today"</strong> - and, as always, no extra visit is spent.</li>
                                <li>The <a href="{{ route('marketing.docs.tickets') }}#checkin-dashboard" class="text-cyan-400 hover:text-cyan-300">check-in dashboard</a> shows a headcount that includes guests alongside the pass count.</li>
                            </ul>
                            <div class="doc-callout doc-callout-tip mb-6">
                                <div class="doc-callout-title">Example: two membership tiers</div>
                                <p>Sell an <strong class="text-gray-900 dark:text-white">"Apprentice"</strong> pass with Admissions per event set to <strong class="text-gray-900 dark:text-white">1</strong> - the holder comes alone - and an <strong class="text-gray-900 dark:text-white">"Explorer"</strong> pass set to <strong class="text-gray-900 dark:text-white">2</strong> that lets them bring a friend to every event, all without using extra visits.</p>
                            </div>
                        </section>

                        <!-- Step 3: Scan at the door -->
                        <section id="redeeming" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                </svg>
                                Step 3 - Scan at the door
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Because a pass can be valid across many events, the scanner needs to know <em>which</em> event you're checking people into right now.</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales &rarr; Scan Tickets</strong> on your phone.</li>
                                <li>At the top, set <strong class="text-gray-900 dark:text-white">"Scanning at event"</strong> to the event happening now. The scanner remembers your choice between scans.</li>
                                <li>Point the camera at the guest's QR code. A visit is recorded and the result appears.</li>
                            </ol>
                            <div class="doc-callout doc-callout-info mb-6">
                                <div class="doc-callout-title">One scan per event, per day</div>
                                <p>Scanning the same person at the same event again on the same day shows "already checked in today" and does <strong class="text-gray-900 dark:text-white">not</strong> use another visit - so there's no harm in double-scanning.</p>
                            </div>

                            <h3 id="scan-results" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">What the scanner shows</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Result</th>
                                            <th>What it means</th>
                                            <th>What to do</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-green-600 dark:text-green-400">Welcome - checked in</span></td>
                                            <td>A visit was recorded (e.g. "Visit 3 of 10").</td>
                                            <td>Let them in.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-green-600 dark:text-green-400">Admitted 1 of 2</span></td>
                                            <td>A pass that admits more than one person let someone in and still has an admission left for this event.</td>
                                            <td>Scan the next person, or tap <strong class="text-gray-900 dark:text-white">Admit guest</strong>. No extra visit is used.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-amber-600 dark:text-amber-400">Already checked in today</span></td>
                                            <td>They already entered this event today.</td>
                                            <td>Let them in - no extra visit is used.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-red-600 dark:text-red-400">All visits used</span></td>
                                            <td>The pass has reached its visit limit.</td>
                                            <td>Sell a new pass or a single ticket.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-red-600 dark:text-red-400">This pass has expired</span></td>
                                            <td>The pass is past its valid-until date.</td>
                                            <td>Sell a new pass.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-red-600 dark:text-red-400">Not valid for this event</span></td>
                                            <td>This pass doesn't cover the event you're scanning at.</td>
                                            <td>Check the "Scanning at event" selector is set correctly; otherwise sell a ticket.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Step 4: Track usage -->
                        <section id="monitoring" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                                Step 4 - Track usage
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Open <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Sales</strong> and choose the <strong class="text-gray-900 dark:text-white">Subscriptions</strong> tab. For every pass holder you'll see:</p>
                            <ul class="doc-list mb-6">
                                <li>Their name and email, the pass type, and the status (active, used up, or expired).</li>
                                <li><strong class="text-gray-900 dark:text-white">Visits used</strong> out of the limit, and the expiry date.</li>
                                <li>An expandable list of <strong class="text-gray-900 dark:text-white">which events they attended and when</strong>.</li>
                            </ul>
                            <p class="text-gray-600 dark:text-gray-300">The real-time <a href="{{ route('marketing.docs.tickets') }}#checkin-dashboard" class="text-cyan-400 hover:text-cyan-300">Check-in dashboard</a> also counts pass scans alongside regular tickets.</p>
                        </section>

                        <!-- Good to know -->
                        <section id="good-to-know" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                                Good to know
                            </h2>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">One visit per event, per day.</strong> Re-scanning the same person at the same event the same day never uses a second visit.</li>
                                <li><strong class="text-gray-900 dark:text-white">Bring a guest.</strong> A pass can admit more than one person per event (set <strong class="text-gray-900 dark:text-white">Admissions per event</strong>) without using extra visits.</li>
                                <li><strong class="text-gray-900 dark:text-white">Sold on its own.</strong> A pass can't be added to the same order as normal single-date tickets - buy them separately.</li>
                                <li><strong class="text-gray-900 dark:text-white">Not auto-renewing.</strong> It's a one-time purchase; there's no recurring billing.</li>
                                <li><strong class="text-gray-900 dark:text-white">Refunds.</strong> Cancelling or refunding a pass stops it from being scanned, but its usage history is kept for your records.</li>
                                <li><strong class="text-gray-900 dark:text-white">Future events.</strong> "All events" and "sub-schedule" coverage automatically include events you create later; "Specific events" does not.</li>
                                <li><strong class="text-gray-900 dark:text-white">Plan.</strong> Subscriptions are part of ticketing, which requires a Pro plan.</li>
                            </ul>
                        </section>

                        <!-- See also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                                See Also
                            </h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Selling Tickets</a> - set up ticketing, payment, and ticket types</li>
                                <li><a href="{{ route('marketing.docs.tickets') }}#check-in" class="text-cyan-400 hover:text-cyan-300">Check-in at the Door</a> - scanning QR codes and the check-in dashboard</li>
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - add the events your pass covers</li>
                            </ul>
                        </section>

                    </div>

                    @include('marketing.docs.partials.navigation')
                </div>
            </div>
        </div>
    </section>
</x-marketing-layout>
