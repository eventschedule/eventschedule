<x-marketing-layout>
    <x-slot name="title">Gift Cards - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Gift Cards</x-slot>
    <x-slot name="description">Sell gift cards that customers buy for someone else and redeem toward tickets for any event on your schedule. Set denominations, deliver by email, and track balances.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Gift Cards - Event Schedule",
        "description": "How to sell gift cards for your events: set denominations, let buyers send a card by email, and redeem the balance toward tickets at checkout.",
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
        "datePublished": "2026-07-16",
        "dateModified": "2026-07-16"
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
            <x-docs-breadcrumb currentTitle="Gift Cards" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                </div>
                <h1 class="es-fade-up es-balance text-3xl md:text-4xl font-black tracking-tight text-gray-900 dark:text-white"><span class="text-gradient-docs">Gift Cards</span></h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Let anyone buy a gift card for someone else. The recipient gets a code by email and redeems the balance toward tickets for any event on your schedule.
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
                        <a href="#overview" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">What is a gift card?</a>
                        <a href="#setup" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Step 1 - Enable gift cards</a>
                        <a href="#buying" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Step 2 - A customer buys one</a>
                        <a href="#redeeming" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Step 3 - Redeem at checkout</a>
                        <a href="#managing" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Step 4 - Track & manage</a>
                        <a href="#good-to-know" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Good to know</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">

                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                </svg>
                                What is a gift card?
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">A <strong class="text-gray-900 dark:text-white">gift card</strong> is a prepaid voucher one person buys as a present for someone else. The recipient receives a code and can spend the balance on tickets for <strong class="text-gray-900 dark:text-white">any event on your schedule</strong>.</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">You</strong> choose the amounts you sell (for example $25, $50, $100) and how buyers pay.</li>
                                <li><strong class="text-gray-900 dark:text-white">A buyer</strong> picks an amount, enters the recipient's name and email plus an optional message, and pays.</li>
                                <li><strong class="text-gray-900 dark:text-white">The recipient</strong> is emailed the gift card and enters its code at checkout to knock the balance off their order.</li>
                            </ul>
                            <div class="doc-callout doc-callout-info mb-6">
                                <div class="doc-callout-title">The balance carries over</div>
                                <p>Gift cards are balance-tracked. If an order costs less than the card, the remainder stays on the code for next time. If it costs more, the customer just pays the difference with any normal payment method.</p>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300">Selling gift cards is a <strong class="text-gray-900 dark:text-white">Pro</strong> feature. Cards that are already sold can always be redeemed, even if selling is later turned off.</p>
                        </section>

                        <!-- Setup -->
                        <section id="setup" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Step 1 - Enable gift cards
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Open your schedule's <strong class="text-gray-900 dark:text-white">Edit</strong> page and go to the <strong class="text-gray-900 dark:text-white">Gift Cards</strong> section. Turn on <strong class="text-gray-900 dark:text-white">Enable gift cards</strong> and set:</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Amounts</strong> - the denominations customers can buy. Add as many as you like.</li>
                                <li><strong class="text-gray-900 dark:text-white">Currency</strong> - gift cards can only be redeemed at events priced in this currency.</li>
                                <li><strong class="text-gray-900 dark:text-white">Valid for</strong> - how many days a card stays usable after purchase. Leave it empty and cards never expire.</li>
                                <li><strong class="text-gray-900 dark:text-white">Payment method</strong> - how buyers pay: Stripe, Invoice Ninja, a payment link, or cash (you confirm the payment manually).</li>
                            </ul>
                            <div class="doc-callout doc-callout-info mb-6">
                                <div class="doc-callout-title">Delivery is by email</div>
                                <p>Because the recipient receives the card by email, hosted schedules need email set up before gift cards can be enabled. Once you save, you'll get a shareable purchase link to post anywhere.</p>
                            </div>
                        </section>

                        <!-- Buying -->
                        <section id="buying" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                </svg>
                                Step 2 - A customer buys one
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">A <strong class="text-gray-900 dark:text-white">Gift cards</strong> button appears on your public schedule page, and a small link shows near the ticket selector on event pages. On the purchase page the buyer:</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Picks one of your amounts.</li>
                                <li>Enters their own name and email.</li>
                                <li>Enters the <strong class="text-gray-900 dark:text-white">recipient's</strong> name and email, and an optional personal message - or ticks <strong class="text-gray-900 dark:text-white">Send to myself</strong> to buy credit for their own use.</li>
                                <li>Pays with whichever method you chose.</li>
                            </ol>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">After payment, three emails go out: the <strong class="text-gray-900 dark:text-white">recipient</strong> gets the gift card with the code and message, the <strong class="text-gray-900 dark:text-white">buyer</strong> gets a receipt (with the code as a backup), and <strong class="text-gray-900 dark:text-white">you</strong> get a sale notification.</p>
                            <div class="doc-callout doc-callout-info mb-2">
                                <div class="doc-callout-title">Paying by cash</div>
                                <p>If you sell by cash, the card stays pending until you mark it paid on the Sales page. Only then is the recipient emailed the code.</p>
                            </div>
                        </section>

                        <!-- Redeeming -->
                        <section id="redeeming" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Step 3 - Redeem at checkout
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">When the recipient buys tickets to any event on your schedule, they enter their gift card code in the <strong class="text-gray-900 dark:text-white">gift card</strong> field at checkout. The balance is deducted from the order total right away, and they see how much was applied and how much is left.</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Order costs less than the card:</strong> the difference stays on the card for a future order.</li>
                                <li><strong class="text-gray-900 dark:text-white">Order costs more:</strong> the card covers what it can and the customer pays the rest normally.</li>
                                <li><strong class="text-gray-900 dark:text-white">Card covers the whole order:</strong> checkout completes with nothing left to pay.</li>
                            </ul>
                            <div class="doc-callout doc-callout-info mb-2">
                                <div class="doc-callout-title">Same currency, same schedule</div>
                                <p>A gift card only works at events on the schedule that sold it, and only when the event is priced in the card's currency.</p>
                            </div>
                        </section>

                        <!-- Managing -->
                        <section id="managing" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                                Step 4 - Track & manage
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">The <strong class="text-gray-900 dark:text-white">Gift cards</strong> tab on your <strong class="text-gray-900 dark:text-white">Sales</strong> page lists every card you've sold, with its balance, status, and total outstanding value. Open a card to see the buyer, recipient, message, and every order it was redeemed against. From there you can:</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Mark paid</strong> - activate a cash card once you've collected payment.</li>
                                <li><strong class="text-gray-900 dark:text-white">Resend email</strong> - send the card to the recipient again if they lost it.</li>
                                <li><strong class="text-gray-900 dark:text-white">Cancel</strong> or <strong class="text-gray-900 dark:text-white">Refund</strong> - stop a card from being redeemed. Past redemptions are kept.</li>
                            </ul>
                            <div class="doc-callout doc-callout-info mb-2">
                                <div class="doc-callout-title">Refunds move money manually</div>
                                <p>Marking a card refunded or cancelled stops it working, but does not move money in your payment provider - do that yourself. If a customer cancels an order that used a gift card, the redeemed amount is automatically returned to the card's balance.</p>
                            </div>
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
                                <li><strong class="text-gray-900 dark:text-white">One code, reused.</strong> A card works over and over until its balance runs out or it expires.</li>
                                <li><strong class="text-gray-900 dark:text-white">Schedule-wide.</strong> A card is valid at every event on the schedule that sold it, including events you add later.</li>
                                <li><strong class="text-gray-900 dark:text-white">Buy for yourself.</strong> The "Send to myself" option makes it easy to top up your own credit.</li>
                                <li><strong class="text-gray-900 dark:text-white">Expiry is shown up front.</strong> If you set a validity period, buyers see it before they pay.</li>
                                <li><strong class="text-gray-900 dark:text-white">Redemption keeps working.</strong> Even if you turn selling off or your plan lapses, already-sold cards can still be redeemed.</li>
                                <li><strong class="text-gray-900 dark:text-white">Plan.</strong> Selling gift cards requires a Pro plan.</li>
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
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Selling Tickets</a> - set up ticketing, payment methods, and ticket types</li>
                                <li><a href="{{ route('marketing.docs.subscriptions') }}" class="text-cyan-400 hover:text-cyan-300">Subscriptions &amp; Passes</a> - sell a multi-use pass across many events</li>
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - add the events your gift cards can be spent on</li>
                            </ul>
                        </section>

                    </div>

                    @include('marketing.docs.partials.navigation')
                </div>
            </div>
        </div>
    </section>
</x-marketing-layout>
