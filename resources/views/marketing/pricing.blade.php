<x-marketing-layout :hp="true">
    <x-slot name="title">{{ __('marketing.pricing_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.pricing_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">Pricing</x-slot>

    @php
        // $proMonthly / $proYearly / $entMonthly / $entYearly come from the marketing.* view
        // composer, which reads PlatformPricing. Re-deriving them here would shadow the shared
        // values and quietly ignore whatever a super-admin set at /admin/settings.
        // Raw, not number_format'd: plan_price() formats to the platform currency's own
        // precision, which is zero decimals for JPY and friends.
        $proPerMonth = $proYearly / 12;
        $entPerMonth = $entYearly / 12;
        $saveMax = max(($proMonthly * 12) - $proYearly, ($entMonthly * 12) - $entYearly);

        // The trial's length is the app's own setting, the one SubscriptionController reads.
        $trialDays = (int) config('app.trial_days', 7);

        // The compare table under the cards: the rate card's rows, shared with /faq.
        $planRows = \App\Utils\PlanRateCard::rows();

        // Curated feature lists (CLAUDE.md, "Never modify WP pricing feature lists"). Wording
        // and order are fixed: style them, never edit them.
        $freeFeatures = [
            'Unlimited events and schedules',
            'Mobile-optimized, professional design',
            'Custom schedule URLs',
            'Venue location maps',
            'Google Calendar sync',
            'CalDAV sync',
            'Fan videos & comments on events',
            'Embed calendar on website',
            'Recurring events',
            'Free event registration',
            'Scan tickets at the door',
            'Appointment booking (1 free type)',
            'Built-in analytics',
            'Generate event graphics',
            'Sub-schedules',
            '10 ' . __('messages.newsletters_per_month'),
        ];
        $proFeatures = [
            'Everything in Free',
            'Remove Event Schedule branding',
            'Paid ticket sales & check-in dashboard',
            'Every payment method (Stripe, PayPal, Payfast, Invoice Ninja, link, cash)',
            'Refunds, full or partial',
            'Passes, subscriptions & individual tickets',
            'Unlimited appointment types, paid bookings & advanced scheduling',
            __('messages.feature_boost'),
            'Custom fields',
            'Custom CSS styling',
            'REST API & webhooks',
            'Event polls',
            'Post-event feedback',
            'Embed ticket widget',
            'Promo/discount codes',
            'Sales CSV export',
            '100 ' . __('messages.newsletters_per_month'),
        ];
        $enterpriseFeatures = [
            'Everything in Pro',
            'Allocated (reserved) seating',
            'Multiple team members per account',
            'Private & password-protected events',
            'WhatsApp event creation',
            'Custom domains',
            'Email scheduling',
            'Agenda scanning',
            'AI-powered content generation',
            'Availability management',
            'Priority support',
            '1,000 ' . __('messages.newsletters_per_month'),
        ];

        // "Which plan is mine?": seven things that decide a plan. Which plan each one needs is NOT
        // written here. It is read off the rate card: Free where Free's own cell says yes, and
        // otherwise the first plan whose cell differs from Free's. 'row' names that row, and
        // 'line' the line of a curated list that says the same thing, which is lit in the card
        // while the need is picked.
        $needs = [
            ['key' => 'free', 'label' => 'Free events', 'row' => 'Free registration with a capacity limit', 'line' => 'Free event registration'],
            ['key' => 'sell', 'label' => 'Paid tickets', 'row' => 'Sell tickets that carry a price', 'line' => 'Paid ticket sales & check-in dashboard'],
            ['key' => 'book', 'label' => 'Paid bookings', 'row' => 'Charge for an appointment booking', 'line' => 'Unlimited appointment types, paid bookings & advanced scheduling'],
            ['key' => 'brand', 'label' => 'Remove our branding', 'row' => 'Remove Event Schedule branding', 'line' => 'Remove Event Schedule branding'],
            ['key' => 'seats', 'label' => 'Reserved seating', 'row' => 'Reserved seating for venue schedules', 'line' => 'Allocated (reserved) seating'],
            ['key' => 'team', 'label' => 'A team', 'row' => 'Team members', 'line' => 'Multiple team members per account'],
            ['key' => 'domain', 'label' => 'Your own domain', 'row' => 'Custom domain, Internal and Unlisted events', 'line' => 'Custom domains'],
        ];
        $rowsByLabel = [];
        foreach ($planRows as $planRow) {
            $rowsByLabel[$planRow[0]] = $planRow;
        }
        foreach ($needs as $needIndex => $need) {
            $needRow = $rowsByLabel[$need['row']] ?? null;
            $needs[$needIndex]['plan'] = match (true) {
                $needRow === null => null,
                \App\Utils\PlanRateCard::includes($needRow[1]) => 'free',
                $needRow[2] !== $needRow[1] => 'pro',
                $needRow[3] !== $needRow[1] => 'enterprise',
                default => null,
            };
        }
        // A need whose row has left the rate card is not offered at all.
        $needs = array_values(array_filter($needs, fn ($need) => $need['plan'] !== null));
        $needByLine = array_column($needs, 'key', 'line');
        $needByRow = array_column($needs, 'key', 'row');

        // The rows that read the same on every plan: they step back once a plan is marked.
        $sameRows = [];
        foreach ($planRows as $planRow) {
            $sameRows[$planRow[0]] = $planRow[1] === $planRow[2] && $planRow[2] === $planRow[3];
        }

        $plans = [
            'free' => ['name' => 'Free', 'features' => $freeFeatures],
            'pro' => ['name' => 'Pro', 'features' => $proFeatures],
            'enterprise' => ['name' => 'Enterprise', 'features' => $enterpriseFeatures],
        ];

        // The cut. Everything comes from App\Utils\TicketFees, like /compare and /for-talent, and
        // the first paint is worked out here so the section is right with JavaScript off. The
        // script below recomputes from the same rates (data-rates) through the same formula.
        // A dollar calculator on purpose, our own column included: every other figure in it is a
        // platform's published US pricing, so plan_price() here would set "R9" beside "$688.00".
        $feeRates = \App\Utils\TicketFees::rates();
        $calcTickets = \App\Utils\TicketFees::EXAMPLE_TICKETS;
        $calcPrice = \App\Utils\TicketFees::EXAMPLE_PRICE;
        // Ticket Tailor is left out: its rate could not be re-checked (see TicketFees).
        $rivals = ['eventbrite', 'luma', 'ticketleap', 'universe', 'allevents', 'hi-events'];
        $rival = $rivals[0];
        $calcEs = \App\Utils\TicketFees::cost('eventschedule', $calcTickets, $calcPrice, $feeRates);
        $calcEb = \App\Utils\TicketFees::cost($rival, $calcTickets, $calcPrice, $feeRates);
        $calcSave = $calcEb - $calcEs;
        $calcGross = $calcTickets * $calcPrice;

        // One platform's take in three parts, each worked out by the ONE formula
        // (TicketFees::costOf) with a part of the rate switched off, so no rate is typed here:
        // 'fees' its own charge on each ticket, 'plan' its subscription, 'processing' the card
        // fee (its own, or Stripe's on your account).
        $feeParts = function (string $platform, $tickets, $price) use ($feeRates) {
            $rate = $feeRates[$platform];
            $stripe = $feeRates['stripe'];

            // Several plans (Luma's free and Plus): the cheaper one for this event, as costOf() picks.
            if (! empty($rate['plans'])) {
                $base = $rate;
                unset($base['plans']);
                $best = null;
                foreach ($rate['plans'] as $plan) {
                    $merged = array_merge($base, $plan);
                    $mergedCost = \App\Utils\TicketFees::costOf($merged, $stripe, $tickets, $price);
                    if ($best === null || $mergedCost < $best[0]) {
                        $best = [$mergedCost, $merged];
                    }
                }
                $rate = $best[1];
            }

            $total = \App\Utils\TicketFees::costOf($rate, $stripe, $tickets, $price);
            $own = array_merge($rate, ['processing' => 0, 'stripe' => false]);
            $platformTake = \App\Utils\TicketFees::costOf($own, $stripe, $tickets, $price);
            $fees = \App\Utils\TicketFees::costOf(array_merge($own, ['monthly' => 0]), $stripe, $tickets, $price);

            return ['total' => $total, 'fees' => $fees, 'plan' => $platformTake - $fees, 'processing' => $total - $platformTake];
        };
        $theirs = $feeParts($rival, $calcTickets, $calcPrice);
        $ours = $feeParts('eventschedule', $calcTickets, $calcPrice);

        // The first ticket from which we cost less. Found by asking the formula, not by algebra.
        $aheadAt = fn ($count) => \App\Utils\TicketFees::cost($rival, $count, $calcPrice, $feeRates)
            > \App\Utils\TicketFees::cost('eventschedule', $count, $calcPrice, $feeRates);
        $aheadFrom = null;
        if ($aheadAt($calcTickets)) {
            [$low, $high] = [1, $calcTickets];
            while ($low < $high) {
                $middle = intdiv($low + $high, 2);
                if ($aheadAt($middle)) {
                    $high = $middle;
                } else {
                    $low = $middle + 1;
                }
            }
            $aheadFrom = $low;
        }

        // Calculator dollars. A figure below nought keeps its sign in front of the symbol.
        $usd = fn ($amount) => (round($amount, 2) < 0 ? '−' : '').'$'.number_format(abs($amount), 2);
        // The same, without the cents when there are none: for a sentence, not a column.
        $usdShort = fn ($amount) => round($amount, 2) == round($amount) ? '$'.number_format($amount) : $usd($amount);
        // How many of the night's tickets a cost comes to: the first so many you sell pay for it.
        $worth = fn ($cost) => $calcPrice > 0 ? (int) min($calcTickets, ceil(round($cost / $calcPrice, 6))) : 0;
        $calcFree = $calcPrice <= 0;
        $calcWin = ! $calcFree && round($calcEb, 2) > round($calcEs, 2);
        $calcTie = ! $calcFree && round($calcEb, 2) === round($calcEs, 2);
        $share = fn ($amount) => $calcGross > 0 ? number_format($amount / $calcGross * 100, 4, '.', '') : '0';
        $each = fn ($amount) => $calcTickets > 0 ? $amount / $calcTickets : 0;

        $faqs = [
            ['q' => 'Is there really a free plan?', 'a' => 'Yes! The free plan includes unlimited events, all core features, one free appointment type with a public booking page, and unlimited free registration with QR check-in at the door. You only need to upgrade if you want to charge for a ticket or a booking, offer more appointment types, remove branding, or access advanced features.'],
            ['q' => 'How does the free trial work?', 'a' => 'When you sign up for Pro or Enterprise, you get a ' . $trialDays . '-day free trial, once per schedule. Enter your card to start, and you won\'t be charged until the trial ends; cancel before then and nothing is charged. After that, Pro is ' . plan_price($proMonthly) . '/month or ' . plan_price($proYearly) . '/year, and Enterprise is ' . plan_price($entMonthly) . '/month or ' . plan_price($entYearly) . '/year. You can cancel anytime.'],
            ['q' => 'What is the difference between Pro and Enterprise?', 'a' => 'The free plan already takes unlimited free registrations, scans tickets at the door and carries one free appointment type. Pro is what puts a price on a ticket, and brings the rest of the ticketing suite with it: every payment method (Stripe, PayPal, Payfast, Invoice Ninja, a payment link or cash), refunds in full or in part, the live check-in dashboard, passes and subscriptions, individual tickets, promo/discount codes, add-ons, gift cards, installment payments through Stripe, the ticket waitlist and sales CSV export. It also lifts the appointment type limit, puts a price on a booking, unlocks advanced scheduling (date overrides, buffers, minimum notice and approvals) and adds white-label branding, event boosting with ads, custom fields, custom CSS styling, REST API & webhooks, and 100 newsletter emails per month, each recipient counting as one. Enterprise adds allocated (reserved) seating for venue schedules, custom domains, private and password-protected events, up to five team members, WhatsApp event creation, email scheduling, agenda scanning, availability management, 1,000 newsletter emails per month, and priority support.'],
            // The free/paid line is Event::canSellPaidTickets(): the creator schedule's plan decides
            // and zero-price rows are always sellable via canOfferTickets(). This question gets
            // asked before anyone signs up, so it belongs here.
            ['q' => 'What can the free plan do with tickets?', 'a' => 'A great deal, as long as nothing has a price on it. Free registration is unlimited on every plan: publish a ticket tier at no charge and guests reserve a place, get a QR code by email and are scanned in at the door. You see who is coming, how many are left and who actually turned up. Putting a price on a ticket is the Pro line, and the same line applies to an appointment booking. An event that only needs a headcount and a guest list never has to pay us at all, and when you do start charging there is still no platform fee.'],
            // Downgrade: Cashier's cancel() keeps the paid period (SubscriptionController::cancel()),
            // paid rows then fail Event::canSellPaidTickets() while free rows keep rendering, and below
            // Enterprise EventRepo::saveEvent() turns Internal and Unlisted into a Draft on the next save.
            ['q' => 'What happens if I cancel or downgrade?', 'a' => 'You keep the plan until the end of the period you have already paid for, and nothing you created is deleted. Every ticket you have already sold stays valid and still scans at the door, and the sales records stay where they are. What stops is new sales of tickets that carry a price; free registration keeps running, so an event that mixes a free tier with paid ones keeps its buy button for the free one. A seating plan already attached to an event stays attached. Every appointment type you made is kept, and so is every scheduling rule on it: the oldest free one keeps taking bookings, priced ones pause until you upgrade, and your buffers and notice periods stay exactly as you set them. Internal and Unlisted events stay hidden and become Drafts the next time they are saved, rather than going public by accident, and your data stays exportable from Backup and Restore at any time.'],
            ['q' => 'Can I cancel anytime?', 'a' => 'Absolutely. You can cancel your subscription at any time and you\'ll keep access until the end of your billing period.'],
            ['q' => 'Do you take a cut of ticket sales?', 'a' => 'No. There is no platform fee on any plan, including free. Buyers pay your own Stripe or PayPal account directly, so the only deduction is the processor\'s own fee (Stripe\'s is 2.9% + $0.30 per transaction in the US, and the processor sets it, not us). We never hold your money, so there is nothing for us to take a cut of.'],
            // Taking money for a ticket is Event::canSellPaidTickets(), which is Pro - so the
            // gateways and refunds are listed on Pro, where they are reachable. The payments layer
            // itself carries no isPro() (docs/FEATURES.md), which is why a schedule that downgrades
            // can still refund what it already took. The interest list is EventInterestController,
            // ungated, bounded by canSendAudienceMail() and not newsletterLimit().
            ['q' => 'Which payment methods can I use?', 'a' => 'All of them, once you are on Pro and charging for a ticket. Stripe, PayPal, Payfast (South African rand only) and Invoice Ninja connect to your own account under Settings, Payment Methods, and you can also send buyers to a payment link of your own or take cash at the door. You pick the method per event. A free schedule never puts a price on a ticket, so it has nothing for a gateway to settle; what it does have is unlimited free registration. Whichever method you use, we add no platform fee on top.'],
            ['q' => 'Can I refund a ticket?', 'a' => 'Yes, and we charge nothing for it. Refunds sit with paid ticketing on Pro, because that is where the money is taken in the first place. From the Sales page, a Stripe or PayPal sale can be refunded in full or in part (an installment plan in full only), and the money goes back through the provider before the sale is marked refunded. A partial refund leaves the tickets valid; a full refund puts the tickets, and any seats, back on sale. A sale taken any other way, such as cash, a payment link, Invoice Ninja or Payfast, is marked as refunded instead, which records it while you return the money yourself. If you cancel Pro, you can still refund the sales you already took.'],
            ['q' => 'Does the “tell me when tickets go on sale” list cost anything?', 'a' => 'No, it is free on every plan. Switch on the “Notify me” card and a visitor leaves just an email address on the event page and gets one email when tickets go on sale, one if the event is cancelled, a reminder shortly before it starts, and any notice you choose to send if the date or venue changes. It does not use your newsletter allowance, and the event\'s Tickets panel shows you how many people are waiting.'],
        ];

        // The small print, said first. Each clause is a fact the FAQ or the rate card already
        // states; here it is set large.
        $clauses = [
            ['Free has no clock.', 'No card to start, no trial that runs out and nothing to cancel. Unlimited events, free registration and scanning at the door stay free.'],
            ['We take nothing from a ticket.', 'Buyers pay your own Stripe or PayPal account directly, and we never hold your money. The processor charges its own fee, and we add nothing to it.'],
            'per-schedule' => ['A plan belongs to one schedule.', 'Each schedule has its own plan and its own bill. Run three, and you pay only for the ones that need Pro or Enterprise.'],
            [$trialDays . ' days to try a plan.', 'Every schedule starts on Free. Choose Pro or Enterprise from its Plan tab and the first ' . $trialDays . ' days are free, once per schedule: a card starts the trial, and nothing is charged if you cancel before it ends.'],
            ['Cancel any time. Nothing is deleted.', 'You keep the plan to the end of the period you paid for. Tickets you have sold stay valid and still scan at the door, and your data stays yours to export.'],
            ['An email allowance counts recipients.', '10, 100 or 1,000 newsletter emails a month means recipients, not sends: one newsletter to 100 followers uses 100. Automatic new-event announcements are outside it.'],
        ];
    @endphp

    <x-slot name="structuredData">
    {{-- The plans themselves - Free, Pro and Enterprise, priced from PlatformPricing - are the
         offers on the layout's one product node, SeoUtils::softwareApplication(). --}}
    <x-seo.webpage
        name="Event Schedule pricing"
        :description="__('marketing.pricing_description')" />
    </x-slot>

    {{--
        /pricing, "Show the cut". The headline says we never take one, so the page shows it: the
        visitor's own ticket drawn to scale, torn on another platform and whole on ours.

        Page classes are pr-*, written as plain CSS on the house style's tokens (partials/hp-kit),
        because a utility class that is not already in the built stylesheet renders as nothing.
        bt-* (the billing toggle), pc-* (the compare table) and plan-disc keep their names: the
        table's class is what PlanRateCardTest finds it by.

        Colour means one thing each. Emerald is what you keep. Rose is what a platform takes.
        The hatch is card processing, which nobody escapes. A plan keeps its own colour: Free
        emerald, Pro blue, Enterprise amber.

        Two things are said on the page's wrapper (#hp) by the script, because parts of the page
        in different sections follow them: is-annual (the billing toggle) and data-pr-yours (the
        plan a visitor's picks point at).

        Nothing loops. Without JavaScript or with motion off every figure, list and row stands in
        the page as the server drew it, and the controls that only a script can work are not shown.
    --}}
    <style {!! nonce_attr() !!}>
        #hp [hidden] { display: none !important; }
        #hp:not(.pr-js) .pr-only-js { display: none !important; }
        {{-- An anchor that is not a section needs its own room under the fixed bar. --}}
        #hp .pr-card,
        #hp .pr-own,
        #hp .pr-compare,
        #hp .pr-print li { scroll-margin-top: 5.5rem; }

        {{-- ==============================================================
           Hero: this page's first screen is its plans, so it stands closer to them
           ============================================================== --}}
        #hp .hp-hero.is-short { padding-block: clamp(1.75rem, 4vh, 3rem) clamp(1rem, 2vh, 1.5rem); }
        #hp .hp-hero.is-short .hp-eyebrow { margin-bottom: clamp(0.9rem, 2vh, 1.4rem); }
        #hp .hp-hero.is-short .hp-sub { margin-top: clamp(0.8rem, 1.8vh, 1.25rem); }
        @media (min-width: 1024px) {
            #hp .hp-hero.is-short .hp-h1 { font-size: min(4.8vw, 5rem); }
        }
        {{-- A short laptop window: the headline gives a little more, so a price is on the first screen. --}}
        @media (min-width: 1024px) and (max-height: 859.98px) {
            #hp .hp-hero.is-short { padding-top: 1.25rem; }
            #hp .hp-hero.is-short .hp-h1 { font-size: min(4vw, 4rem); }
            #hp .hp-hero.is-short .hp-sub { font-size: 1.1rem; }
        }
        {{-- The claim in figures: a quiet line with two inked numbers and the way down. --}}
        #hp .pr-thesis { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.5rem 0.9rem; margin-top: clamp(0.75rem, 1.6vh, 1.1rem); font-size: 1rem; line-height: 1.5; color: var(--hp-ink-2); }
        #hp .pr-thesis b { font-variation-settings: 'wght' 800; color: var(--hp-ink); }
        #hp .pr-thesis a { display: inline-flex; align-items: center; gap: 0.35rem; min-height: 2.25rem; padding: 0 0.85rem; border: 1px solid var(--hp-line-2); border-radius: 999px; background: var(--hp-bg-2); font-size: 0.92rem; font-weight: 700; font-variation-settings: 'wght' 700; white-space: nowrap; color: var(--hp-blue); transition: border-color 0.2s ease, transform 0.2s ease; }
        #hp .pr-thesis a:hover { border-color: var(--hp-blue); transform: translateY(1px); }
        #hp .pr-thesis a svg { width: 0.9rem; height: 0.9rem; }

        {{-- The prices in one line, for a phone, where the cards stand one under another and
             the second price is two screens down. Each takes its plan's colour when it is yours. --}}
        #hp .pr-quick { display: none; }
        @media (max-width: 899.98px) {
            #hp .pr-quick { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.35rem; margin-top: 1.1rem; }
            #hp .pr-quick a { --tier: #047857; display: inline-flex; align-items: baseline; gap: 0.3rem; min-height: 2.5rem; padding: 0.5rem 0.65rem; border: 1px solid var(--hp-line-2); border-radius: 0.8rem; background: var(--hp-bg-2); font-size: 0.88rem; font-weight: 700; font-variation-settings: 'wght' 640; line-height: 1.4; color: var(--hp-ink-2); }
            #hp .pr-quick a[data-pr-quick="pro"] { --tier: #2456d6; }
            #hp .pr-quick a[data-pr-quick="enterprise"] { --tier: #b45309; }
            .dark #hp .pr-quick a { --tier: #6ee7b7; }
            .dark #hp .pr-quick a[data-pr-quick="pro"] { --tier: #8db0ff; }
            .dark #hp .pr-quick a[data-pr-quick="enterprise"] { --tier: #f5a623; }
            #hp .pr-quick a b { color: var(--hp-ink); font-variation-settings: 'wght' 820; }
            #hp .pr-quick a small { margin-inline-start: -0.22rem; font-size: 0.76rem; color: var(--hp-ink-3); }
            #hp[data-pr-yours="free"] .pr-quick a[data-pr-quick="free"],
            #hp[data-pr-yours="pro"] .pr-quick a[data-pr-quick="pro"],
            #hp[data-pr-yours="enterprise"] .pr-quick a[data-pr-quick="enterprise"] { border-color: var(--tier); box-shadow: 0 0 0 1px var(--tier); color: var(--hp-ink); }
        }

        {{-- ==============================================================
           Plans
           ============================================================== --}}
        #hp .pr-plans { position: relative; padding-block: 0.25rem clamp(3.5rem, 7vw, 6rem); }

        {{-- The bar over the cards, in reading order: the question, the things to pick, the
             answer, how you pay. From a tablet up the answer and the toggle share the question's
             line and the picks run under it. --}}
        #hp .pr-bar { display: grid; grid-template-columns: minmax(0, 1fr); grid-template-areas: "ask" "chips" "say" "bill"; gap: 0.75rem 1rem; align-items: center; margin-bottom: 1.25rem; }
        #hp .pr-ask { grid-area: ask; font-size: 1.2rem; font-variation-settings: 'wght' 800; letter-spacing: -0.02em; color: var(--hp-ink); }
        #hp .pr-say { grid-area: say; font-size: 1.05rem; line-height: 1.45; color: var(--hp-ink-2); }
        #hp .pr-say a { font-weight: 700; font-variation-settings: 'wght' 760; color: var(--hp-ink); text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.2em; }
        #hp .pr-say a[data-pr-if] { color: var(--hp-blue); white-space: nowrap; }
        @media (min-width: 900px) {
            #hp .pr-bar { grid-template-columns: auto minmax(0, 1fr) auto; grid-template-areas: "ask say bill" "chips chips chips"; }
            #hp .pr-ask,
            #hp .pr-say { display: flex; align-items: center; min-height: 2.75rem; }
            #hp .pr-say > span { display: block; }
        }
        #hp .pr-chips { grid-area: chips; display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
        #hp .pr-chip {
            --chip: #047857;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-height: 2.75rem;
            padding: 0 0.95rem 0 0.7rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 999px;
            background: var(--hp-bg-2);
            font-size: 0.98rem;
            font-weight: 700;
            font-variation-settings: 'wght' 640;
            line-height: 1.2;
            text-align: start;
            color: var(--hp-ink-2);
            cursor: pointer;
            transition: border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }
        #hp .pr-chip[data-pr-plan="pro"] { --chip: #2456d6; }
        #hp .pr-chip[data-pr-plan="enterprise"] { --chip: #b45309; }
        .dark #hp .pr-chip { --chip: #6ee7b7; }
        .dark #hp .pr-chip[data-pr-plan="pro"] { --chip: #8db0ff; }
        .dark #hp .pr-chip[data-pr-plan="enterprise"] { --chip: #f5a623; }
        #hp .pr-chip::before {
            content: "";
            flex: none;
            width: 1.15rem;
            height: 1.15rem;
            border: 1.5px solid var(--hp-ink-3);
            border-radius: 0.38rem;
            background: transparent center / 0.8rem no-repeat;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        #hp .pr-chip:hover { border-color: var(--chip); color: var(--hp-ink); transform: translateY(-1px); }
        #hp .pr-chip[aria-pressed="true"] { border-color: var(--chip); color: var(--hp-ink); box-shadow: 0 0 0 1px var(--chip), var(--hp-card-shadow); }
        #hp .pr-chip[aria-pressed="true"]::before {
            border-color: var(--chip);
            background-color: var(--chip);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23fff' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 13l4 4L19 7'/%3E%3C/svg%3E");
        }
        .dark #hp .pr-chip[aria-pressed="true"]::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23070a14' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 13l4 4L19 7'/%3E%3C/svg%3E");
        }
        @media (min-width: 1180px) {
            #hp .pr-chips { gap: 0.4rem; }
            #hp .pr-chip { padding: 0 0.8rem 0 0.6rem; }
        }
        #hp .pr-clear { min-height: 2.75rem; padding: 0 0.5rem; font-size: 0.95rem; font-weight: 700; font-variation-settings: 'wght' 700; color: var(--hp-blue); text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.2em; cursor: pointer; }
        {{-- A small laptop and a tablet: two even rows, four and three, with nothing left on its own. --}}
        @media (min-width: 600px) and (max-width: 1179.98px) {
            #hp .pr-chips { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.45rem; }
            #hp .pr-chip { padding-inline-end: 0.6rem; font-size: 0.92rem; }
            #hp .pr-clear { justify-self: start; }
        }
        {{-- A phone: the free answer across the top, the six that cost something in pairs. --}}
        @media (max-width: 599.98px) {
            #hp .pr-chips { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            #hp .pr-chip { padding-inline-end: 0.6rem; font-size: 0.92rem; }
            #hp .pr-chip[data-pr-plan="free"],
            #hp .pr-clear { grid-column: 1 / -1; }
            #hp .pr-clear { justify-self: start; }
        }

        {{-- Billing toggle: one class on the page's wrapper drives every price, note and period. --}}
        #hp .pr-bill { grid-area: bill; display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; }
        #hp .pr-seg { display: inline-flex; align-items: center; padding: 0.25rem; border: 1px solid var(--hp-line-2); border-radius: 1rem; background: var(--hp-bg-3); }
        .bt-seg { min-height: 2.75rem; padding: 0 1.25rem; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 700; font-variation-settings: 'wght' 680; color: var(--hp-ink-2); cursor: pointer; transition: background-color 0.2s, color 0.2s, box-shadow 0.2s; }
        .bt-seg-month { background: var(--hp-ink); color: var(--hp-bg); box-shadow: 0 10px 24px -12px rgba(10, 16, 32, 0.6); }
        #hp.is-annual .bt-seg-month { background: transparent; color: var(--hp-ink-2); box-shadow: none; }
        #hp.is-annual .bt-seg-year { background: var(--hp-ink); color: var(--hp-bg); box-shadow: 0 10px 24px -12px rgba(10, 16, 32, 0.6); }
        #hp .pr-save { display: inline-flex; align-items: center; gap: 0.4rem; min-height: 1.9rem; padding: 0 0.8rem; border-radius: 999px; background: rgba(16, 185, 129, 0.14); font-size: 0.82rem; font-weight: 700; font-variation-settings: 'wght' 680; color: #065f46; }
        .dark #hp .pr-save { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; }
        #hp .pr-save svg { width: 0.85rem; height: 0.85rem; }

        {{-- Price, note and period swapping. Both notes stay stacked in one grid cell and are
             hidden with visibility, not display, so the row is always as tall as the LONGER
             note and the cards cannot change height when the toggle flips. --}}
        .bt-price-month { display: flex; }
        .bt-price-year { display: none; }
        #hp.is-annual .bt-price-month { display: none; }
        #hp.is-annual .bt-price-year { display: flex; }
        .bt-note-month, .bt-note-year { grid-area: 1 / 1; transition: opacity 0.2s ease; }
        .bt-note-year { visibility: hidden; opacity: 0; }
        #hp.is-annual .bt-note-month { visibility: hidden; opacity: 0; }
        #hp.is-annual .bt-note-year { visibility: visible; opacity: 1; }
        .bt-period-year { display: none; }
        #hp.is-annual .bt-period-month { display: none; }
        #hp.is-annual .bt-period-year { display: inline; }

        {{-- The three cards. --}}
        #hp .pr-cards { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; }
        @media (min-width: 900px) {
            #hp .pr-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); align-items: stretch; }
        }
        #hp .pr-card {
            --tier: #047857;
            --tier-soft: rgba(16, 185, 129, 0.13);
            --tier-glow: rgba(16, 185, 129, 0.2);
            --pad: clamp(1.4rem, 2.1vw, 2rem);
            position: relative;
            isolation: isolate;
            display: flex;
            flex-direction: column;
            padding: 0 var(--pad) var(--pad);
            border: 1px solid var(--hp-line);
            border-radius: 1.9rem;
            background: var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
            {{-- The card's own fade-in is listed here too: a rule on the card outranks the shared reveal's. --}}
            transition: opacity 0.9s cubic-bezier(0.22, 1, 0.36, 1), transform 0.9s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.35s ease, border-color 0.35s ease;
            transition-delay: var(--reveal-delay, 0s), var(--reveal-delay, 0s), 0s, 0s;
        }
        #hp .pr-card[data-plan="pro"] { --tier: #2456d6; --tier-soft: rgba(78, 129, 250, 0.13); --tier-glow: rgba(78, 129, 250, 0.26); }
        #hp .pr-card[data-plan="enterprise"] { --tier: #b45309; --tier-soft: rgba(217, 119, 6, 0.13); --tier-glow: rgba(217, 119, 6, 0.22); }
        .dark #hp .pr-card { --tier: #6ee7b7; --tier-soft: rgba(52, 211, 153, 0.14); --tier-glow: rgba(52, 211, 153, 0.12); }
        .dark #hp .pr-card[data-plan="pro"] { --tier: #8db0ff; --tier-soft: rgba(125, 165, 255, 0.16); --tier-glow: rgba(78, 129, 250, 0.3); }
        .dark #hp .pr-card[data-plan="enterprise"] { --tier: #f5a623; --tier-soft: rgba(245, 166, 35, 0.14); --tier-glow: rgba(217, 119, 6, 0.2); }

        {{-- The head of a card: its name over a wash of its colour. --}}
        #hp .pr-cap { margin-inline: calc(var(--pad) * -1); padding: clamp(1.25rem, 1.8vw, 1.7rem) var(--pad) clamp(0.9rem, 1.4vw, 1.15rem); border-radius: 1.85rem 1.85rem 0 0; background: radial-gradient(26rem 12rem at 50% 0%, var(--tier-glow), transparent 72%); transition: background-color 0.3s ease, color 0.3s ease; }
        #hp .pr-card-top { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; min-height: 2rem; }
        #hp .pr-name { display: inline-flex; align-items: center; gap: 0.6rem; font-size: 1.4rem; font-weight: 700; font-variation-settings: 'wght' 820; letter-spacing: -0.03em; line-height: 1.1; }
        #hp .pr-name::before { content: ""; flex: none; width: 0.6rem; height: 0.6rem; border-radius: 0.18rem; background: var(--tier); transition: background-color 0.3s ease; }
        #hp .pr-mark { display: none; align-items: center; min-height: 1.75rem; padding: 0 0.7rem; border-radius: 999px; background: var(--tier); font-family: var(--hp-mono); font-size: 0.68rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.1em; text-transform: uppercase; white-space: nowrap; color: #fff; }
        .dark #hp .pr-mark { color: #070a14; }
        #hp .pr-mark.is-yours::before { content: ""; width: 0.8rem; height: 0.8rem; margin-inline-end: 0.35rem; background: currentColor; -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 13l4 4L19 7'/%3E%3C/svg%3E") center / contain no-repeat; mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 13l4 4L19 7'/%3E%3C/svg%3E") center / contain no-repeat; }
        #hp .pr-trial { margin-top: 0.35rem; font-family: var(--hp-mono); font-size: 0.74rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.12em; text-transform: uppercase; color: var(--tier); transition: color 0.3s ease; }

        {{-- Until the visitor has said what they need, Pro stands in the page's light. --}}
        #hp:not([data-pr-yours]) .pr-card[data-plan="pro"] { border-color: transparent; box-shadow: 0 0 0 2px var(--tier), 0 30px 70px -30px var(--tier), var(--hp-card-shadow); }
        #hp:not([data-pr-yours]) .pr-mark.is-popular { display: inline-flex; }

        {{-- Once they have, their plan takes its colour as a solid cap, as the plan cards here
             always wore one, and the other two step back: no shadow, and a quiet button. Nothing
             moves, so the three prices and the three buttons stay on their lines. --}}
        #hp[data-pr-yours] .pr-card { box-shadow: none; }
        #hp[data-pr-yours="free"] .pr-card[data-plan="free"],
        #hp[data-pr-yours="pro"] .pr-card[data-plan="pro"],
        #hp[data-pr-yours="enterprise"] .pr-card[data-plan="enterprise"] {
            border-color: transparent;
            box-shadow: 0 0 0 2px var(--tier), 0 36px 80px -30px var(--tier), var(--hp-card-shadow);
            animation: pr-yours 0.7s cubic-bezier(0.22, 1, 0.36, 1) 1;
        }
        @keyframes pr-yours {
            0% { box-shadow: 0 0 0 2px var(--tier), 0 0 0 2px var(--tier-soft), var(--hp-card-shadow); }
            45% { box-shadow: 0 0 0 2px var(--tier), 0 0 0 1.1rem var(--tier-soft), var(--hp-card-shadow); }
            100% { box-shadow: 0 0 0 2px var(--tier), 0 36px 80px -30px var(--tier), var(--hp-card-shadow); }
        }
        #hp[data-pr-yours="free"] .pr-card[data-plan="free"] .pr-cap,
        #hp[data-pr-yours="pro"] .pr-card[data-plan="pro"] .pr-cap,
        #hp[data-pr-yours="enterprise"] .pr-card[data-plan="enterprise"] .pr-cap { background: var(--tier); color: #fff; }
        #hp[data-pr-yours="free"] .pr-card[data-plan="free"] .pr-trial,
        #hp[data-pr-yours="pro"] .pr-card[data-plan="pro"] .pr-trial,
        #hp[data-pr-yours="enterprise"] .pr-card[data-plan="enterprise"] .pr-trial { color: rgba(255, 255, 255, 0.9); }
        #hp[data-pr-yours="free"] .pr-card[data-plan="free"] .pr-name::before,
        #hp[data-pr-yours="pro"] .pr-card[data-plan="pro"] .pr-name::before,
        #hp[data-pr-yours="enterprise"] .pr-card[data-plan="enterprise"] .pr-name::before { background: #fff; }
        #hp[data-pr-yours="free"] .pr-card[data-plan="free"] .pr-mark.is-yours,
        #hp[data-pr-yours="pro"] .pr-card[data-plan="pro"] .pr-mark.is-yours,
        #hp[data-pr-yours="enterprise"] .pr-card[data-plan="enterprise"] .pr-mark.is-yours { display: inline-flex; background: #fff; color: var(--tier); }
        .dark #hp[data-pr-yours="free"] .pr-card[data-plan="free"] .pr-cap,
        .dark #hp[data-pr-yours="pro"] .pr-card[data-plan="pro"] .pr-cap,
        .dark #hp[data-pr-yours="enterprise"] .pr-card[data-plan="enterprise"] .pr-cap { color: #070a14; }
        .dark #hp[data-pr-yours] .pr-card .pr-cap .pr-trial { color: inherit; }
        .dark #hp[data-pr-yours="free"] .pr-card[data-plan="free"] .pr-name::before,
        .dark #hp[data-pr-yours="pro"] .pr-card[data-plan="pro"] .pr-name::before,
        .dark #hp[data-pr-yours="enterprise"] .pr-card[data-plan="enterprise"] .pr-name::before { background: #070a14; }
        .dark #hp[data-pr-yours="free"] .pr-card[data-plan="free"] .pr-mark.is-yours,
        .dark #hp[data-pr-yours="pro"] .pr-card[data-plan="pro"] .pr-mark.is-yours,
        .dark #hp[data-pr-yours="enterprise"] .pr-card[data-plan="enterprise"] .pr-mark.is-yours { background: #070a14; color: var(--tier); }
        #hp[data-pr-yours="free"] .pr-card:not([data-plan="free"]) .pr-cta .hp-btn,
        #hp[data-pr-yours="pro"] .pr-card:not([data-plan="pro"]) .pr-cta .hp-btn,
        #hp[data-pr-yours="enterprise"] .pr-card:not([data-plan="enterprise"]) .pr-cta .hp-btn { border: 2px solid var(--hp-line-2); background: var(--hp-bg-2); background-image: none; box-shadow: none; color: var(--hp-ink-2); }

        #hp .pr-price { display: flex; align-items: flex-end; gap: 0.7rem; min-height: 4.1rem; margin-top: 0.4rem; }
        #hp .pr-price > span { align-items: flex-end; gap: 0.7rem; }
        #hp .pr-amount { font-size: clamp(3.2rem, 4.2vw, 4.3rem); font-weight: 700; font-variation-settings: 'wght' 860; letter-spacing: -0.055em; line-height: 0.9; font-variant-numeric: tabular-nums; color: var(--hp-ink); }
        #hp .pr-per { display: flex; flex-direction: column; padding-bottom: 0.15rem; font-size: 0.95rem; line-height: 1.3; color: var(--hp-ink-3); }
        #hp .pr-per b { font-variation-settings: 'wght' 680; color: var(--hp-ink-2); }
        #hp .pr-per a { text-decoration: underline dotted; text-decoration-thickness: 1px; text-underline-offset: 0.22em; }
        #hp .pr-per a:hover { color: var(--hp-blue); }
        #hp .pr-note { display: grid; align-content: start; min-height: 2.9em; margin-top: 0.6rem; font-size: 1rem; line-height: 1.45; color: var(--hp-ink-2); }

        #hp .pr-cta { margin-top: 0.9rem; }
        #hp .pr-cta .hp-btn { width: 100%; }
        #hp .pr-btn-free { color: #047857; background: var(--hp-bg-2); border: 2px solid rgba(5, 150, 105, 0.55); }
        #hp .pr-btn-free:hover { transform: translateY(-2px); border-color: #047857; background: rgba(16, 185, 129, 0.08); }
        .dark #hp .pr-btn-free { color: #6ee7b7; background: rgba(52, 211, 153, 0.06); border-color: rgba(52, 211, 153, 0.5); }
        #hp .pr-btn-ent { color: #fff; background: linear-gradient(100deg, #b45309, #92400e); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.22), 0 14px 34px -14px rgba(180, 83, 9, 0.75), 0 0 0 1px rgba(146, 64, 14, 0.5); }
        #hp .pr-btn-ent:hover { transform: translateY(-2px); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.22), 0 22px 44px -16px rgba(180, 83, 9, 0.9), 0 0 0 1px rgba(146, 64, 14, 0.6); }
        #hp .pr-fine { display: grid; align-content: start; min-height: 2.9em; margin-top: 0.8rem; font-size: 0.86rem; line-height: 1.45; text-align: center; color: var(--hp-ink-3); text-wrap: balance; }

        #hp .pr-holds { margin-top: 1.4rem; padding-top: 1.4rem; border-top: 1px solid var(--hp-line); }
        .plan-disc > summary { list-style: none; }
        .plan-disc > summary::-webkit-details-marker { display: none; }
        .plan-disc > summary::marker { content: ''; }
        .plan-disc[open] > summary .plan-disc-chev { transform: rotate(180deg); }
        #hp .pr-holds > summary { display: none; align-items: center; justify-content: space-between; min-height: 2.75rem; margin-bottom: 1rem; padding: 0 1rem; border-radius: 0.85rem; background: var(--hp-bg); font-size: 0.95rem; font-weight: 700; font-variation-settings: 'wght' 680; color: var(--hp-ink-2); cursor: pointer; }
        #hp .pr-holds:not([open]) > summary { margin-bottom: 0; }
        #hp .plan-disc-chev { width: 1rem; height: 1rem; color: var(--hp-ink-3); transition: transform 0.2s ease; }
        @media (max-width: 899.98px) {
            #hp .pr-holds > summary { display: flex; }
        }
        #hp .pr-list { display: grid; gap: 0.72rem; }
        {{-- A stacked card on a tablet is wide: its list runs down two columns, in its own order. --}}
        @media (min-width: 600px) and (max-width: 899.98px) {
            #hp .pr-list { display: block; columns: 2; column-gap: 2rem; }
            #hp .pr-list li { break-inside: avoid; margin-bottom: 0.72rem; }
        }
        #hp .pr-list li { position: relative; display: flex; align-items: flex-start; gap: 0.7rem; font-size: 0.98rem; line-height: 1.42; color: var(--hp-ink-2); transition: color 0.25s ease; }
        #hp .pr-list li::before {
            content: "";
            flex: none;
            width: 1.05rem;
            height: 1.05rem;
            margin-top: 0.17rem;
            background: var(--tier);
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 13l4 4L19 7'/%3E%3C/svg%3E") center / contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 13l4 4L19 7'/%3E%3C/svg%3E") center / contain no-repeat;
        }
        {{-- The line that answers a picked need. --}}
        #hp .pr-list li.is-need { color: var(--hp-ink); font-variation-settings: 'wght' 680; }
        #hp .pr-list li.is-need::after { content: ""; position: absolute; inset: -0.3rem -0.6rem; z-index: -1; border-radius: 0.7rem; background: var(--tier-soft); }
        {{-- The foot of a card: where the next plan up begins. It stands on the card's last line. --}}
        #hp .pr-next { margin-top: auto; padding-top: 1.5rem; font-size: 0.92rem; line-height: 1.5; color: var(--hp-ink-3); }
        #hp .pr-next a { font-weight: 700; font-variation-settings: 'wght' 700; color: var(--hp-ink-2); text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.2em; }
        #hp .pr-next a:hover { color: var(--hp-blue); }
        #hp .pr-jump { display: flex; justify-content: center; margin-top: 1.25rem; }

        {{-- The fourth price. --}}
        #hp .pr-own {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1.25rem 1.75rem;
            align-items: center;
            margin-top: 1.25rem;
            padding: clamp(1.4rem, 2.3vw, 2rem) clamp(1.4rem, 2.6vw, 2.25rem);
            border: 1px solid rgba(5, 150, 105, 0.3);
            border-radius: 1.9rem;
            background: radial-gradient(36rem 14rem at 0% 0%, rgba(16, 185, 129, 0.13), transparent 70%), var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
        }
        .dark #hp .pr-own { border-color: rgba(52, 211, 153, 0.28); }
        @media (min-width: 900px) {
            #hp .pr-own { grid-template-columns: auto minmax(0, 1fr) auto; }
        }
        #hp .pr-own-mark { display: inline-flex; align-items: center; justify-content: center; width: 3.5rem; height: 3.5rem; border-radius: 1.1rem; background: rgba(16, 185, 129, 0.14); color: #047857; }
        .dark #hp .pr-own-mark { color: #6ee7b7; }
        #hp .pr-own-mark svg { width: 1.9rem; height: 1.9rem; }
        #hp .pr-own .hp-h3 { font-size: clamp(1.45rem, 1.1vw + 1.05rem, 1.9rem); }
        #hp .pr-own .hp-h3 span { color: #047857; }
        .dark #hp .pr-own .hp-h3 span { color: #6ee7b7; }
        #hp .pr-own p { max-width: 44rem; margin-top: 0.5rem; color: var(--hp-ink-2); }
        #hp .pr-own-go { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem 1.4rem; }

        {{-- ==============================================================
           Compare plans: the same rows as the rate card on /faq (App\Utils\PlanRateCard)
           ============================================================== --}}
        #hp .pr-compare > summary { display: none; align-items: center; justify-content: space-between; min-height: 3.6rem; padding: 0 1.4rem; border: 1px solid var(--hp-line-2); border-radius: 1.25rem; background: var(--hp-bg-2); font-size: 1.05rem; font-weight: 700; font-variation-settings: 'wght' 720; cursor: pointer; }
        @media (max-width: 767.98px) {
            #hp .pr-compare > summary { display: flex; }
            #hp .pr-compare-head { display: none; }
            #hp .pr-compare[open] .pr-compare-body { margin-top: 1rem; }
        }
        #hp .pr-compare-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 0.75rem 2rem; margin-bottom: 1.5rem; }
        #hp .pr-compare-head p { color: var(--hp-ink-2); }
        #hp .pr-compare-body { padding: clamp(0.5rem, 1.4vw, 1.25rem) clamp(1rem, 2.4vw, 2rem) clamp(1.25rem, 2.4vw, 2rem); border: 1px solid var(--hp-line); border-radius: 1.9rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
        .pc-table { width: 100%; border-collapse: collapse; text-align: start; font-size: 0.98rem; }
        .pc-table th,
        .pc-table td { padding: 0.85rem 1rem; vertical-align: top; border-top: 1px solid var(--hp-line); text-align: start; transition: color 0.25s ease, background-color 0.25s ease; }
        .pc-table thead th {
            position: sticky;
            top: 4rem;
            z-index: 2;
            border-top: 0;
            box-shadow: inset 0 -1px 0 var(--hp-line-2);
            background: var(--hp-bg-2);
            font-size: 1.05rem;
            font-weight: 700;
            font-variation-settings: 'wght' 800;
            letter-spacing: -0.02em;
            color: var(--hp-ink);
            white-space: nowrap;
        }
        .pc-table thead th:first-child { font-family: var(--hp-mono); font-size: 0.72rem; font-variation-settings: normal; letter-spacing: 0.12em; text-transform: uppercase; color: var(--hp-ink-3); }
        .pc-table tbody tr:first-child th,
        .pc-table tbody tr:first-child td { border-top: 0; }
        .pc-table tbody th { font-weight: 700; font-variation-settings: 'wght' 640; color: var(--hp-ink); }
        .pc-table thead th:first-child,
        .pc-table tbody th { width: 40%; }
        {{-- A denial or a ceiling stays in neutral ink, so no limit reads as a feature. --}}
        .pc-yes { font-weight: 700; font-variation-settings: 'wght' 640; color: #047857; }
        .dark .pc-yes { color: #6ee7b7; }
        .pc-no { color: var(--hp-ink-3); }
        {{-- One column is tinted: Pro's until a plan is marked as the visitor's, then that plan's. --}}
        #hp:not([data-pr-yours]) .pc-table :is(th, td):nth-child(3),
        #hp[data-pr-yours="pro"] .pc-table :is(th, td):nth-child(3) { background-color: rgba(47, 102, 234, 0.06); }
        .dark #hp:not([data-pr-yours]) .pc-table :is(th, td):nth-child(3),
        .dark #hp[data-pr-yours="pro"] .pc-table :is(th, td):nth-child(3) { background-color: rgba(125, 165, 255, 0.09); }
        #hp:not([data-pr-yours]) .pc-table thead th:nth-child(3),
        #hp[data-pr-yours="pro"] .pc-table thead th:nth-child(3) { background: linear-gradient(rgba(47, 102, 234, 0.06), rgba(47, 102, 234, 0.06)), var(--hp-bg-2); }
        .dark #hp:not([data-pr-yours]) .pc-table thead th:nth-child(3),
        .dark #hp[data-pr-yours="pro"] .pc-table thead th:nth-child(3) { background: linear-gradient(rgba(125, 165, 255, 0.09), rgba(125, 165, 255, 0.09)), var(--hp-bg-2); }
        #hp[data-pr-yours="free"] .pc-table :is(th, td):nth-child(2) { background-color: rgba(16, 185, 129, 0.08); }
        #hp[data-pr-yours="free"] .pc-table thead th:nth-child(2) { background: linear-gradient(rgba(16, 185, 129, 0.08), rgba(16, 185, 129, 0.08)), var(--hp-bg-2); }
        #hp[data-pr-yours="enterprise"] .pc-table :is(th, td):nth-child(4) { background-color: rgba(217, 119, 6, 0.09); }
        #hp[data-pr-yours="enterprise"] .pc-table thead th:nth-child(4) { background: linear-gradient(rgba(217, 119, 6, 0.09), rgba(217, 119, 6, 0.09)), var(--hp-bg-2); }
        #hp .pc-table thead .pr-mark { margin-inline-start: 0.5rem; vertical-align: middle; background: #2456d6; color: #fff; }
        .dark #hp .pc-table thead .pr-mark { background: #8db0ff; color: #070a14; }
        #hp .pc-table thead th[data-pr-col="free"] .pr-mark { background: #047857; }
        .dark #hp .pc-table thead th[data-pr-col="free"] .pr-mark { background: #6ee7b7; }
        #hp .pc-table thead th[data-pr-col="enterprise"] .pr-mark { background: #b45309; }
        .dark #hp .pc-table thead th[data-pr-col="enterprise"] .pr-mark { background: #f5a623; }
        #hp[data-pr-yours="free"] .pc-table thead th[data-pr-col="free"] .pr-mark,
        #hp[data-pr-yours="pro"] .pc-table thead th[data-pr-col="pro"] .pr-mark,
        #hp[data-pr-yours="enterprise"] .pc-table thead th[data-pr-col="enterprise"] .pr-mark { display: inline-flex; }
        {{-- Once a plan is marked, the rows that read the same on every plan step back, and the
             row a picked need is decided by is lit from edge to edge. --}}
        #hp[data-pr-yours] .pc-table tr.is-same th,
        #hp[data-pr-yours] .pc-table tr.is-same td { color: var(--hp-ink-3); font-variation-settings: 'wght' 480; }
        #hp .pc-table tr.is-need th,
        #hp .pc-table tr.is-need td { background-color: rgba(78, 129, 250, 0.13); }
        .dark #hp .pc-table tr.is-need th,
        .dark #hp .pc-table tr.is-need td { background-color: rgba(125, 165, 255, 0.18); }
        #hp .pc-table tr.is-need th { color: var(--hp-ink); font-variation-settings: 'wght' 800; }
        #hp .pr-compare-foot { margin-top: 1.25rem; font-size: 0.92rem; color: var(--hp-ink-3); }
        {{-- On a phone each question is its own block, the three plans side by side under their
             names. The names come from data-label, and the head stays for a screen reader (the
             table roles are said outright in the markup, because a table laid out as blocks
             loses them). --}}
        @media (max-width: 639.98px) {
            .pc-table { display: block; }
            .pc-table thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
            .pc-table tbody { display: block; }
            .pc-table tbody tr { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.45rem 0.75rem; padding: 0.85rem 0.5rem; border-top: 1px solid var(--hp-line); border-radius: 0.6rem; }
            .pc-table tbody tr:first-child { border-top: 0; }
            .pc-table tbody th,
            .pc-table tbody td { display: block; width: auto; padding: 0; border-top: 0; }
            #hp .pc-table tbody :is(th, td) { background-color: transparent !important; }
            #hp .pc-table tbody tr.is-need { background-color: rgba(78, 129, 250, 0.13); }
            .pc-table tbody th { grid-column: 1 / -1; }
            .pc-table tbody td { overflow-wrap: anywhere; font-size: 0.9rem; }
            .pc-table tbody td::before { content: attr(data-label); display: block; margin-bottom: 0.15rem; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--hp-ink-3); }
        }

        {{-- ==============================================================
           The cut (the page's night: dark in both modes, literal colours)
           ============================================================== --}}
        #hp .pr-cut { --pr-keep: #6ee7b7; --pr-take: #fb7185; --pr-ours: #7da5ff; --pr-night: #060a1b; --pr-quiet: #9fb1d6; }
        .dark #hp .pr-cut { --pr-take: #fda4af; --pr-night: #17257a; --pr-quiet: #ccd6f3; }
        #hp .pr-cut .hp-head { max-width: 50rem; }

        {{-- Your event on one side, the proof on the other. --}}
        #hp .pr-desk { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; align-items: start; margin-top: clamp(2.25rem, 4.5vw, 3.75rem); }
        #hp .pr-panel { display: contents; }
        #hp .pr-dials,
        #hp .pr-against,
        #hp .pr-answer,
        #hp .pr-still { min-width: 0; }
        #hp .pr-proof { display: grid; grid-template-columns: minmax(0, 1fr); gap: clamp(2rem, 3.5vw, 3rem); min-width: 0; padding-inline-end: 1.1rem; }

        #hp .pr-dials { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.75rem; }
        #hp .pr-dials .pr-field { display: grid; grid-template-columns: 6.4rem auto minmax(0, 1fr); align-items: center; gap: 0.75rem; min-width: 0; }
        #hp .pr-field-name { display: grid; gap: 0.15rem; min-width: 0; }
        #hp .pr-field label,
        #hp .pr-against > span { display: block; font-family: var(--hp-mono); font-size: 0.7rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.12em; line-height: 1.35; text-transform: uppercase; color: var(--pr-quiet); }
        #hp .pr-field-name small { font-size: 0.74rem; line-height: 1.3; color: var(--pr-quiet); }
        #hp .pr-zero { justify-self: start; min-height: 1.5rem; padding: 0; font-size: 0.8rem; font-weight: 700; font-variation-settings: 'wght' 680; line-height: 1.3; text-align: start; color: #a9c3ff; text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.2em; cursor: pointer; }
        #hp .pr-zero[aria-pressed="true"] { color: var(--pr-keep); }
        #hp .pr-num { position: relative; flex: none; }
        #hp .pr-num input { width: 5.6rem; min-height: 2.9rem; padding: 0 0.7rem; border: 1px solid rgba(125, 165, 255, 0.4); border-radius: 0.85rem; background: #101831; font-family: var(--hp-mono); font-size: 1.1rem; font-weight: 700; font-variation-settings: normal; color: #fff; box-shadow: none; }
        #hp .pr-num.is-money input { padding-inline-start: 1.6rem; }
        #hp .pr-num i { position: absolute; top: 50%; inset-inline-start: 0.7rem; transform: translateY(-50%); font-family: var(--hp-mono); font-size: 1rem; font-style: normal; color: var(--pr-quiet); pointer-events: none; }
        #hp .pr-num input:focus { border-color: #8db0ff; box-shadow: 0 0 0 4px rgba(141, 176, 255, 0.35); outline: 0; }
        #hp .pr-range { width: 100%; min-width: 0; height: 2.75rem; margin: 0; background: transparent; -webkit-appearance: none; appearance: none; cursor: pointer; }
        #hp .pr-range::-webkit-slider-runnable-track { height: 0.4rem; border-radius: 999px; background: rgba(125, 165, 255, 0.28); }
        #hp .pr-range::-moz-range-track { height: 0.4rem; border-radius: 999px; background: rgba(125, 165, 255, 0.28); }
        #hp .pr-range::-webkit-slider-thumb { -webkit-appearance: none; appearance: none; width: 1.5rem; height: 1.5rem; margin-top: -0.55rem; border: 3px solid #050814; border-radius: 999px; background: #8db0ff; box-shadow: 0 0 0 1px #8db0ff, 0 6px 16px -4px rgba(78, 129, 250, 0.9); }
        #hp .pr-range::-moz-range-thumb { width: 1.2rem; height: 1.2rem; border: 3px solid #050814; border-radius: 999px; background: #8db0ff; box-shadow: 0 0 0 1px #8db0ff; }
        #hp .pr-range:focus-visible { outline: 3px solid #8db0ff; outline-offset: 2px; border-radius: 999px; }
        #hp .pr-dials-say { font-size: 1.05rem; font-weight: 700; font-variation-settings: 'wght' 760; letter-spacing: -0.02em; color: #fff; }
        #hp .pr-dials-say b { font-variant-numeric: tabular-nums; color: var(--pr-keep); }
        #hp .pr-dials-say b[data-pr-gap] { color: #fff; }

        #hp .pr-rivals { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.55rem; }
        #hp .pr-rival { min-height: 2.6rem; padding: 0 0.8rem; border: 1px solid rgba(125, 165, 255, 0.3); border-radius: 0.8rem; background: transparent; font-size: 0.92rem; font-weight: 700; font-variation-settings: 'wght' 640; color: #c5cde2; cursor: pointer; transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease; }
        #hp .pr-rival:hover { border-color: #8db0ff; color: #fff; }
        #hp .pr-rival[aria-pressed="true"] { border-color: var(--pr-take); background: rgba(251, 113, 133, 0.16); color: #fff; }
        #hp .pr-still { font-family: var(--hp-mono); font-size: 0.8rem; letter-spacing: 0.12em; line-height: 1.6; text-transform: uppercase; color: var(--pr-quiet); }
        #hp.pr-js .pr-still { display: none; }

        #hp .pr-verdict { font-size: clamp(1.45rem, 0.8vw + 1.1rem, 1.85rem); font-weight: 700; font-variation-settings: 'wght' 800; letter-spacing: -0.035em; line-height: 1.12; text-wrap: balance; color: #fff; }
        #hp .pr-verdict b { font-variation-settings: 'wght' 860; color: var(--pr-keep); font-variant-numeric: tabular-nums; }
        #hp .pr-verdict b[data-pr-gap] { color: #fff; }
        #hp .pr-ahead { margin-top: 0.8rem; font-size: 1.02rem; line-height: 1.5; color: #c5cde2; }
        #hp .pr-ahead b { color: #fff; }
        #hp .pr-answer .hp-btn { width: 100%; margin-top: 1.25rem; }

        {{-- One column: the two dials ride under the header with the answer in a line, so the
             tickets are on screen while the numbers move; the full answer follows the proof,
             and the line stands down once it is in view. --}}
        @media (max-width: 1099.98px) {
            #hp .pr-dials { position: sticky; top: 4rem; z-index: 6; order: 1; margin-inline: -0.25rem; padding: 0.8rem 0.9rem; border: 1px solid rgba(125, 165, 255, 0.3); border-top: 0; border-radius: 0 0 1.1rem 1.1rem; background: #0b1124; box-shadow: 0 18px 40px -18px rgba(0, 0, 0, 0.95); }
            .dark #hp .pr-dials { background: #0c1540; }
            #hp .pr-against,
            #hp .pr-still { order: 2; }
            #hp .pr-proof { order: 3; }
            #hp .pr-answer { order: 4; }
            #hp .pr-cut.is-answered .pr-dials-say { display: none; }
        }
        @media (min-width: 600px) and (max-width: 1099.98px) {
            #hp .pr-dials { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); align-items: center; gap: 0.5rem 1.5rem; }
            #hp .pr-dials-say { grid-column: 1 / -1; }
        }
        @media (max-width: 599.98px) {
            #hp .pr-dials .pr-field { grid-template-columns: 5.6rem auto minmax(0, 1fr); gap: 0.6rem; }
            #hp .pr-num input { width: 5rem; min-height: 2.75rem; }
        }
        @media (max-width: 1099.98px) and (max-height: 559.98px) {
            #hp .pr-dials { position: static; }
        }
        @media (min-width: 1100px) {
            #hp .pr-desk { grid-template-columns: minmax(19.5rem, 0.42fr) minmax(0, 1fr); gap: clamp(1.75rem, 3vw, 3rem); }
            #hp .pr-panel { display: block; position: sticky; top: 5.25rem; padding: 1.25rem; border: 1px solid rgba(125, 165, 255, 0.24); border-radius: 1.6rem; background: #0b1124; box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05), 0 30px 60px -30px rgba(0, 0, 0, 0.9); }
            .dark #hp .pr-panel { background: #0c1540; }
            #hp .pr-against { margin-top: 1rem; }
            #hp .pr-answer { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid rgba(125, 165, 255, 0.24); }
            #hp:not(.pr-js) .pr-answer { margin-top: 0.9rem; }
            #hp .pr-dials-say { display: none; }
        }
        {{-- A window too short for the whole panel: it scrolls with the page instead. --}}
        @media (min-width: 1100px) and (max-height: 699.98px) {
            #hp .pr-panel { position: static; }
        }

        {{-- Two tickets, each one ticket price wide. What is cut away is drawn to scale. --}}
        #hp .pr-tix { display: grid; grid-template-columns: minmax(0, 1fr); gap: clamp(1.6rem, 2.8vw, 2.5rem); }
        #hp .pr-rule { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; padding-bottom: 0.9rem; font-family: var(--hp-mono); font-size: 0.74rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.14em; text-transform: uppercase; color: var(--pr-quiet); background: repeating-linear-gradient(to right, rgba(159, 177, 214, 0.75) 0, rgba(159, 177, 214, 0.75) 1px, transparent 1px, transparent 10%) 0 100% / calc(100% + 1px) 0.45rem no-repeat, linear-gradient(rgba(159, 177, 214, 0.75), rgba(159, 177, 214, 0.75)) 0 100% / 100% 1px no-repeat; }
        #hp .pr-rule > span:first-child,
        #hp .pr-rule > span:last-child { color: #fff; }
        #hp .pr-tk { min-width: 0; }
        #hp .pr-tk-cap { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.3rem 1.5rem; margin-bottom: 0.8rem; }
        #hp .pr-tk-cap > b { font-size: clamp(1.15rem, 0.7vw + 1rem, 1.5rem); font-variation-settings: 'wght' 800; letter-spacing: -0.03em; color: #fff; }
        #hp .pr-tk-rate { font-family: var(--hp-mono); font-size: 0.82rem; font-variation-settings: normal; color: var(--pr-quiet); }

        #hp .pr-tk-row {
            --proc: 3;
            --plat: 10;
            --stub: clamp(3.5rem, 6vw, 5.5rem);
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            height: clamp(6.25rem, 10.5vw, 9.5rem);
            filter: drop-shadow(0 26px 30px rgba(0, 0, 0, 0.55));
        }
        #hp .pr-tk.is-ours .pr-tk-row { filter: drop-shadow(0 0 1px rgba(160, 190, 255, 0.95)) drop-shadow(0 22px 38px rgba(78, 129, 250, 0.6)) drop-shadow(0 0 60px rgba(34, 211, 238, 0.22)); }
        #hp .pr-tk.is-theirs .pr-tk-row { grid-template-columns: minmax(0, 1fr) minmax(3px, calc(var(--plat) * 1%)); }
        {{-- The body is the part still in your hand. --}}
        #hp .pr-tk-body {
            position: relative;
            display: grid;
            min-width: 0;
            overflow: hidden;
            border-radius: 1.15rem;
            background: linear-gradient(180deg, #ffffff, #eef2fb);
            color: #0a1020;
        }
        #hp .pr-tk.is-theirs .pr-tk-body { grid-template-columns: minmax(0, 1fr) minmax(3px, calc(var(--proc) / (100 - var(--plat)) * 100%)); border-start-end-radius: 0; border-end-end-radius: 0; }
        #hp .pr-tk.is-ours .pr-tk-body { grid-template-columns: minmax(0, 1fr) minmax(3px, calc(var(--proc) * 1%)) minmax(3px, calc(var(--plat) * 1%)); }
        #hp .pr-tk-face { display: flex; align-items: center; gap: clamp(0.75rem, 2vw, 1.75rem); min-width: 0; overflow: hidden; padding-inline-end: clamp(0.9rem, 2.4vw, 2.25rem); container-type: inline-size; }
        {{-- The stub torn at the door, with the two notches every ticket has. They belong to the
             stub: where there is no room for it, there are no notches either. --}}
        #hp .pr-tk-stub { position: relative; display: flex; flex: none; flex-direction: column; align-items: center; justify-content: center; gap: 0.55rem; align-self: stretch; width: var(--stub); border-inline-end: 2px dashed rgba(10, 16, 32, 0.28); }
        #hp .pr-tk-stub::before,
        #hp .pr-tk-stub::after { content: ""; position: absolute; inset-inline-end: -0.6rem; width: 1.1rem; height: 1.1rem; border-radius: 999px; background: var(--pr-night); }
        #hp .pr-tk-stub::before { top: -0.55rem; }
        #hp .pr-tk-stub::after { bottom: -0.55rem; }
        #hp .pr-tk-stub i { width: 52%; height: 44%; background: repeating-linear-gradient(to right, #0a1020 0 2px, transparent 2px 4px, #0a1020 4px 5px, transparent 5px 8px, #0a1020 8px 11px, transparent 11px 13px); opacity: 0.78; }
        #hp .pr-tk-stub small { font-family: var(--hp-mono); font-size: 0.6rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.08em; white-space: nowrap; color: #56617c; }
        #hp .pr-tk-admit { display: flex; flex-direction: column; gap: 0.35rem; min-width: 0; font-family: var(--hp-mono); font-variation-settings: normal; text-transform: uppercase; }
        #hp .pr-tk-admit small { font-size: 0.68rem; font-weight: 700; letter-spacing: 0.2em; color: #56617c; white-space: nowrap; }
        #hp .pr-tk-admit b { font-family: var(--hp-display); font-variation-settings: 'wght' 860; letter-spacing: -0.05em; line-height: 0.95; text-transform: none; font-variant-numeric: tabular-nums; white-space: nowrap; color: #0a1020; }
        #hp .pr-tk-yours { display: flex; flex-direction: column; align-items: flex-end; gap: 0.3rem; min-width: 0; text-align: end; }
        #hp .pr-tk-yours small { font-family: var(--hp-mono); font-size: 0.68rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.2em; text-transform: uppercase; color: #56617c; white-space: nowrap; }
        #hp .pr-tk-yours b { font-variation-settings: 'wght' 860; letter-spacing: -0.05em; line-height: 0.95; font-variant-numeric: tabular-nums; white-space: nowrap; color: #047857; }
        #hp .pr-tk-mid { flex: 1 1 0; min-width: 0.5rem; height: 0; border-top: 2px dotted rgba(10, 16, 32, 0.22); }
        {{-- The lettering is sized by the face it stands on, and gives way in steps as a fee
             leaves less of the ticket: first the barcode and the dotted line, then the price,
             and last the figure itself. --}}
        #hp .pr-tk-admit b,
        #hp .pr-tk-yours b { font-size: clamp(1.15rem, 7.2cqw, 3rem); }
        @container (max-width: 24rem) {
            #hp .pr-tk-stub,
            #hp .pr-tk-mid { display: none; }
            #hp .pr-tk-face .pr-tk-admit { margin-inline-start: clamp(0.8rem, 5cqw, 1.5rem); }
            #hp .pr-tk-face .pr-tk-yours { margin-inline-start: auto; }
            #hp .pr-tk-admit small,
            #hp .pr-tk-yours small { font-size: 0.6rem; letter-spacing: 0.12em; }
        }
        @container (max-width: 13rem) {
            #hp .pr-tk-admit { display: none; }
            #hp .pr-tk-face .pr-tk-yours { margin-inline: auto; align-items: center; }
        }
        @container (max-width: 6.5rem) {
            #hp .pr-tk-yours { display: none; }
        }
        {{-- Card processing: hatched, and still on the ticket, because every platform pays it. --}}
        #hp .pr-tk-proc { min-width: 0; border-inline-start: 2px dashed rgba(10, 16, 32, 0.4); background: repeating-linear-gradient(135deg, #b9c4e0 0 5px, #e3e8f5 5px 10px); }
        {{-- Our share: a blue line at the end of the ticket. --}}
        #hp .pr-tk-line { display: grid; place-items: center; min-width: 0; overflow: hidden; background: repeating-linear-gradient(135deg, #2f66ea 0 7px, #2a5cd8 7px 14px); container-type: inline-size; font-family: var(--hp-mono); font-size: clamp(0.8rem, 1.5vw, 1.25rem); font-weight: 700; font-variation-settings: normal; color: #fff; }
        #hp .pr-tk-line span { white-space: nowrap; }
        @container (max-width: 5rem) {
            #hp .pr-tk-line span { writing-mode: vertical-rl; font-size: 0.82rem; }
        }
        @container (max-width: 1.4rem) {
            #hp .pr-tk-line span { display: none; }
        }
        @container (min-width: 12rem) {
            #hp .pr-tk-line span { padding: 0.3rem 0.8rem; border-radius: 999px; background: rgba(5, 8, 20, 0.35); }
        }
        {{-- Theirs: the platform's cut, torn off. --}}
        #hp .pr-tk-cut {
            position: relative;
            display: grid;
            place-items: center;
            min-width: 0;
            overflow: hidden;
            border-start-end-radius: 1.15rem;
            border-end-end-radius: 1.15rem;
            background: linear-gradient(180deg, #ffe9ec, #ffd3da);
            font-family: var(--hp-mono);
            font-size: clamp(0.8rem, 1.5vw, 1.25rem);
            font-weight: 700;
            font-variation-settings: normal;
            color: #9f1239;
            container-type: inline-size;
            -webkit-mask: radial-gradient(circle at 0 0.45rem, transparent 0.28rem, #000 0.31rem) 0 0 / 100% 0.9rem;
            mask: radial-gradient(circle at 0 0.45rem, transparent 0.28rem, #000 0.31rem) 0 0 / 100% 0.9rem;
            transform: translate(0.6rem, calc(0.65rem * var(--tilt, 1))) rotate(calc(3.5deg * var(--tilt, 1)));
            transform-origin: 0 100%;
            transition: transform 1.1s cubic-bezier(0.22, 1, 0.36, 1) 0.55s;
        }
        #hp .pr-tk-cut span { white-space: nowrap; }
        @container (max-width: 4.75rem) {
            #hp .pr-tk-cut span { writing-mode: vertical-rl; font-size: 0.82rem; }
        }
        @container (max-width: 1.4rem) {
            #hp .pr-tk-cut span { display: none; }
        }
        {{-- The stub is still on the ticket until the two are seen; then it comes away, once. --}}
        html.es-anim #hp .pr-tix:not(.is-revealed) .pr-tk-cut { transform: none; }
        {{-- Three cases where nothing is torn. A sliver too thin to draw a torn edge on is a
             plain strip. A platform's subscription is not a cut of a ticket, so it stays on the
             ticket as our own month does. And fees larger than the ticket leave nothing to tear
             from: the parts then fill the drawing, which says it is not to scale. --}}
        #hp .pr-tk.is-sliver .pr-tk-cut,
        #hp .pr-tk.is-plan .pr-tk-cut,
        #hp .pr-tk.is-over .pr-tk-cut { transform: none; -webkit-mask: none; mask: none; }
        #hp .pr-tk.is-over .pr-tk-face { display: none; }
        #hp .pr-tk.is-over .pr-tk-proc { border-inline-start: 0; }
        #hp .pr-tk.is-theirs.is-over .pr-tk-row { grid-template-columns: minmax(3px, calc(var(--proc) * 1%)) minmax(0, 1fr); }
        #hp .pr-tk.is-theirs.is-over .pr-tk-body { grid-template-columns: minmax(0, 1fr); }
        #hp .pr-tk.is-ours.is-over .pr-tk-body { grid-template-columns: minmax(3px, calc(var(--proc) * 1%)) minmax(0, 1fr); }

        #hp .pr-key { display: flex; flex-wrap: wrap; gap: 0.4rem 1.75rem; margin-top: 1.1rem; font-size: 0.98rem; color: #c5cde2; }
        #hp .pr-tk.is-theirs .pr-key { margin-top: 1.5rem; }
        #hp .pr-tk.is-theirs:is(.is-sliver, .is-plan, .is-over) .pr-key { margin-top: 1.1rem; }
        #hp .pr-key li { display: inline-flex; align-items: baseline; gap: 0.55rem; }
        #hp .pr-key i { flex: none; align-self: center; width: 0.85rem; height: 0.85rem; border-radius: 0.2rem; background: #f7f9ff; }
        #hp .pr-key i.is-proc { background: repeating-linear-gradient(135deg, #c3cde6 0 3px, #8f9bbd 3px 6px); }
        #hp .pr-key i.is-take { background: #fb7185; }
        #hp .pr-key i.is-ours { background: #4e81fa; }
        #hp .pr-key i.is-keep { background: var(--pr-keep); }
        #hp .pr-key b { font-family: var(--hp-mono); font-variation-settings: normal; color: #fff; }
        {{-- "You keep" is on the ticket's face. Where the face has no room for it, it is said here. --}}
        #hp .pr-key li.pr-key-keep { display: none; }
        #hp .pr-tk:is(.is-tight, .is-over) .pr-key li.pr-key-keep { display: inline-flex; }

        {{-- The whole night as a roll of tickets, and how much of it each side's costs tear off. --}}
        #hp .pr-rolls-say { max-width: 44rem; font-size: clamp(1.2rem, 0.7vw + 1rem, 1.55rem); font-weight: 700; font-variation-settings: 'wght' 760; letter-spacing: -0.03em; line-height: 1.25; text-wrap: balance; color: #fff; }
        #hp .pr-rolls-say b { font-variation-settings: 'wght' 860; font-variant-numeric: tabular-nums; }
        #hp .pr-rolls-say b[data-pr-worth="theirs"] { color: var(--pr-take); }
        #hp .pr-rolls-say b[data-pr-worth="ours"] { color: var(--pr-keep); }
        #hp .pr-roll-pair { display: grid; gap: 1rem; margin-top: 1.25rem; }
        #hp .pr-roll-pair > div { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 0.4rem 1rem; align-items: baseline; }
        #hp .pr-roll-pair dt { font-size: 0.98rem; color: #c5cde2; }
        #hp .pr-roll-pair dd { margin: 0; }
        #hp .pr-roll-pair dd b { font-family: var(--hp-mono); font-size: 1rem; font-variation-settings: normal; font-variant-numeric: tabular-nums; color: #fff; }
        {{-- The roll is the night's sales: what you keep in emerald, what the night cost at its
             head, and one set of perforations over both. --}}
        #hp .pr-roll { position: relative; grid-column: 1 / -1; height: 1.6rem; border-radius: 0.4rem; overflow: hidden; background: linear-gradient(180deg, rgba(110, 231, 183, 0.55), rgba(52, 211, 153, 0.38)); }
        #hp .pr-roll::after { content: ""; position: absolute; inset: 0; background: repeating-linear-gradient(to right, transparent 0, transparent calc(2% - 2px), var(--pr-night) calc(2% - 2px), var(--pr-night) 2%); }
        #hp .pr-roll i { display: block; width: calc(var(--w) * 1%); min-width: 3px; height: 100%; background: var(--pr-take); transition: width 0.35s ease; }
        #hp .pr-roll-pair .is-ours .pr-roll i { background: repeating-linear-gradient(135deg, #d5dcf0 0 4px, #9aa6c8 4px 8px); }
        #hp .pr-roll-pair .is-theirs dd b { color: var(--pr-take); }
        #hp .pr-roll-pair .is-ours dd b { color: var(--pr-keep); }

        {{-- A free event: nothing is taken, so the two tickets, the roll and the settlement give
             way to one whole ticket and what the Free plan does for it. --}}
        #hp .pr-gratis { display: none; }
        #hp .pr-cut.is-free .pr-gratis { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; }
        #hp .pr-cut.is-free .pr-tix,
        #hp .pr-cut.is-free .pr-rolls,
        #hp .pr-cut.is-free .pr-sheet,
        #hp .pr-cut.is-free .pr-against,
        #hp .pr-cut.is-free .pr-feet-basis { display: none; }
        #hp .pr-gratis .pr-tk.is-ours .pr-tk-body { grid-template-columns: minmax(0, 1fr); }
        #hp .pr-gratis .pr-tk-admit b { font-size: clamp(1.15rem, 5.4cqw, 2.4rem); }
        #hp .pr-gratis-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 17rem), 1fr)); gap: 0.7rem 1.5rem; font-size: 1.02rem; color: #c5cde2; }
        #hp .pr-gratis-list li { display: flex; align-items: flex-start; gap: 0.65rem; }
        #hp .pr-gratis-list li::before { content: ""; flex: none; width: 1.05rem; height: 1.05rem; margin-top: 0.2rem; background: var(--pr-keep); -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 13l4 4L19 7'/%3E%3C/svg%3E") center / contain no-repeat; mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 13l4 4L19 7'/%3E%3C/svg%3E") center / contain no-repeat; }
        #hp .pr-gratis-then { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem 1.5rem; padding: 1.1rem 1.25rem; border: 1px solid rgba(125, 165, 255, 0.24); border-radius: 1.25rem; background: #0b1124; }
        .dark #hp .pr-gratis-then { background: #0c1540; }
        #hp .pr-gratis-then p { flex: 1 1 18rem; font-size: 1.02rem; line-height: 1.5; color: #c5cde2; }

        {{-- The settlement: the same night, added up twice. --}}
        #hp .pr-sheet { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid rgba(125, 165, 255, 0.22); border-radius: 1.5rem; background: rgba(11, 17, 36, 0.78); overflow: hidden; font-size: 0.98rem; }
        .dark #hp .pr-sheet { background: rgba(12, 21, 64, 0.82); }
        #hp .pr-sheet th,
        #hp .pr-sheet td { padding: 0.85rem 1.1rem; border-top: 1px solid rgba(255, 255, 255, 0.08); text-align: end; vertical-align: baseline; }
        #hp .pr-sheet thead th { border-top: 0; font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.12em; text-transform: uppercase; color: var(--pr-quiet); }
        #hp .pr-sheet thead th:last-child { color: #a9c3ff; }
        #hp .pr-sheet thead th:first-child { text-align: start; }
        #hp .pr-sheet tbody th { text-align: start; font-weight: 400; font-variation-settings: 'wght' 520; color: #c5cde2; }
        #hp .pr-sheet tbody th small { display: block; font-size: 0.82rem; color: var(--pr-quiet); }
        #hp .pr-sheet td { direction: ltr; unicode-bidi: isolate; font-family: var(--hp-mono); font-variation-settings: normal; font-variant-numeric: tabular-nums; white-space: nowrap; color: #eef2ff; }
        #hp .pr-sheet td:last-child,
        #hp .pr-sheet thead th:last-child { background: rgba(78, 129, 250, 0.12); }
        #hp .pr-sheet td.is-take { color: var(--pr-take); }
        #hp .pr-sheet td.is-none { color: var(--pr-keep); }
        #hp .pr-sheet tfoot th,
        #hp .pr-sheet tfoot td { padding-block: 1.1rem; border-top: 1px solid rgba(125, 165, 255, 0.35); font-size: 1.15rem; font-weight: 700; color: #fff; }
        #hp .pr-sheet tfoot th { text-align: start; font-variation-settings: 'wght' 760; }
        #hp .pr-sheet tfoot td:last-child { color: var(--pr-keep); }
        @media (max-width: 599.98px) {
            #hp .pr-sheet { font-size: 0.86rem; }
            #hp .pr-sheet th,
            #hp .pr-sheet td { padding: 0.7rem 0.6rem; }
            #hp .pr-sheet tfoot th,
            #hp .pr-sheet tfoot td { font-size: 0.98rem; }
            #hp .pr-proof { padding-inline-end: 0.9rem; }
            #hp .pr-tk-face { padding-inline-end: 0.8rem; }
            #hp .pr-tk-cut { transform: translate(0.35rem, calc(0.4rem * var(--tilt, 1))) rotate(calc(3deg * var(--tilt, 1))); }
        }
        #hp .pr-feet { max-width: 60rem; margin-top: clamp(2rem, 4vw, 3rem); font-size: 0.86rem; line-height: 1.6; color: var(--pr-quiet); }
        #hp .pr-feet p + p { margin-top: 0.6rem; }
        #hp .pr-feet a { color: #a9c3ff; text-decoration: underline; text-underline-offset: 0.2em; }

        {{-- ==============================================================
           The small print, in large print
           ============================================================== --}}
        #hp .pr-print { display: grid; grid-template-columns: minmax(0, 1fr); margin-top: clamp(2rem, 4vw, 3.5rem); border-top: 2px solid var(--hp-ink); counter-reset: clause; }
        @media (min-width: 900px) {
            #hp .pr-print { grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: clamp(2rem, 5vw, 5rem); }
        }
        #hp .pr-print li { position: relative; padding-block: clamp(1.5rem, 2.6vw, 2.25rem); padding-inline: 3.25rem 0; border-bottom: 1px solid var(--hp-line-2); counter-increment: clause; }
        #hp .pr-print li::before { content: counter(clause, decimal-leading-zero); position: absolute; inset-inline-start: 0; top: clamp(1.9rem, 3vw, 2.75rem); font-family: var(--hp-mono); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.12em; color: var(--hp-ink-3); }
        #hp .pr-print h3 { font-size: clamp(1.55rem, 1.5vw + 1rem, 2.35rem); font-weight: 700; font-variation-settings: 'wght' 820; letter-spacing: -0.04em; line-height: 1.06; text-wrap: balance; }
        #hp .pr-print p { max-width: 34rem; margin-top: 0.7rem; font-size: 1.02rem; line-height: 1.6; color: var(--hp-ink-2); }

        {{-- Motion off --}}
        @media (prefers-reduced-motion: reduce) {
            #hp .pr-card { animation: none !important; }
            #hp .pr-chip,
            #hp .pr-card,
            #hp .pr-cap,
            #hp .pr-tk-cut,
            #hp .pr-roll i,
            #hp .pr-rival,
            #hp .pr-thesis a,
            .pc-table th,
            .pc-table td,
            .bt-seg,
            .bt-note-month,
            .bt-note-year { transition: none; }
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present, so no-JS
         visitors, crawlers and reduced-motion users always see everything. The second class
         shows the controls that only a script can work. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
        (function () {
            var page = document.getElementById('hp');
            if (page) { page.classList.add('pr-js'); }
        })();
    </script>

    {{-- ============================================================
         Hero (text only: on a pricing page the cards are the way in)
         ============================================================ --}}
    <section id="top" class="es-hero hp-hero is-short">
        <div class="hp-hero-sky" aria-hidden="true"></div>

        <div class="hp-hero-copy">
            <h1 class="hp-h1">
                <x-marketing.hero-eyebrow class="es-fade-up es-d-1 hp-eyebrow">
                    <svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Event Schedule pricing, no hidden fees
                </x-marketing.hero-eyebrow>
                <span class="es-mask"><span class="es-mask-line">Pricing that never</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="hp-ink-grad">takes a cut</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 hp-sub">
                Start free and upgrade when you need more. No surprises, and never a cut of your ticket sales.
            </p>

            {{-- The claim in figures, and the way down to where it is worked out. They are the fee
                 calculator's own two totals for its opening example, card processing included on
                 both sides, so the line is only printed while that example comes out our way. --}}
            @if ($calcWin)
                <div class="es-fade-up es-d-3 pr-thesis">
                    <span>On {{ number_format($calcTickets) }} tickets at {{ $usdShort($calcPrice) }}, {{ $feeRates[$rival]['name'] }} costs you <b>{{ $usdShort($calcEb) }}</b>. Here the same night costs <b>{{ $usdShort($calcEs) }}</b>.</span>
                    <a href="#fees">
                        See it on your own ticket
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 9l-7 7-7-7" /></svg>
                    </a>
                </div>
            @endif

            {{-- A phone's first screen: the three prices, each a way down to its card. --}}
            <div class="es-fade-up es-d-3 pr-quick">
                <a href="#plan-free" data-pr-quick="free">Free <b>{{ plan_price(0) }}</b></a>
                <a href="#plan-pro" data-pr-quick="pro">Pro <b><span class="bt-period-month">{{ plan_price($proMonthly) }}</span><span class="bt-period-year">{{ plan_price($proYearly) }}</span></b><small><span class="bt-period-month">/mo</span><span class="bt-period-year">/yr</span></small></a>
                <a href="#plan-enterprise" data-pr-quick="enterprise">Enterprise <b><span class="bt-period-month">{{ plan_price($entMonthly) }}</span><span class="bt-period-year">{{ plan_price($entYearly) }}</span></b><small><span class="bt-period-month">/mo</span><span class="bt-period-year">/yr</span></small></a>
            </div>
        </div>
    </section>

    {{-- ============================================================
         Plans
         ============================================================ --}}
    <section id="pricing-plans" class="pr-plans">
        <div class="hp-wrap">
            <h2 class="sr-only">The plans</h2>

            {{-- The bar over the cards. Pick what you need and the plan that covers it is marked:
                 each answer is read off the rate card below, so the mark cannot disagree with
                 the table. --}}
            <div class="pr-bar pr-only-js">
                <b class="pr-ask" id="pr-ask">Which plan is mine?</b>

                <div class="pr-chips" role="group" aria-labelledby="pr-ask">
                    @foreach ($needs as $need)
                        <button type="button" class="pr-chip" aria-pressed="false" data-pr-need="{{ $need['key'] }}" data-pr-plan="{{ $need['plan'] }}">{{ $need['label'] }}</button>
                    @endforeach
                    <button type="button" class="pr-clear" data-pr-clear hidden>Start over</button>
                </div>

                <p class="pr-say" role="status">
                    <span data-pr-say="rest">Pick what you need and your plan is marked.</span>
                    <span data-pr-say="free" hidden>That's <a href="#plan-free">Free</a>: {{ plan_price(0) }}, with no card and no time limit.</span>
                    <span data-pr-say="pro" hidden>That's <a href="#plan-pro">Pro</a>: <span class="bt-period-month">{{ plan_price($proMonthly) }} a month</span><span class="bt-period-year">{{ plan_price($proYearly) }} a year</span> for one schedule, and we take nothing from your ticket sales. <a href="#fees" data-pr-if="sell" hidden>See it on your own ticket</a></span>
                    <span data-pr-say="enterprise" hidden>That's <a href="#plan-enterprise">Enterprise</a>: <span class="bt-period-month">{{ plan_price($entMonthly) }} a month</span><span class="bt-period-year">{{ plan_price($entYearly) }} a year</span> for one schedule, with everything in Pro. <span data-pr-if="seats" hidden>Seating plans belong to venue schedules.</span></span>
                </p>

                <div class="pr-bill">
                    <span class="pr-save">
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        Save up to {{ plan_price($saveMax) }} a year
                    </span>
                    <div class="pr-seg">
                        <button id="bt-monthly" type="button" aria-pressed="true" class="bt-seg bt-seg-month">Monthly</button>
                        <button id="bt-annual" type="button" aria-pressed="false" class="bt-seg bt-seg-year">Annual</button>
                    </div>
                </div>
            </div>

            {{-- Vertical reveal only: the horizontal variants translate 44px sideways, which
                 overflows a full-width card at 390px. --}}
            <div class="pr-cards" data-reveal-group="90">

                {{-- Free --}}
                <article id="plan-free" class="pr-card" data-plan="free" data-reveal>
                    <header class="pr-cap">
                        <div class="pr-card-top">
                            <h3 class="pr-name">Free</h3>
                            <span class="pr-mark is-yours">Your plan</span>
                        </div>
                        <p class="pr-trial">Forever free</p>
                    </header>

                    <div class="pr-price">
                        <span class="pr-amount">{{ plan_price(0) }}</span>
                        <span class="pr-per"><b>forever</b></span>
                    </div>
                    <p class="pr-note">Perfect for getting started</p>

                    <div class="pr-cta">
                        <a href="{{ app_url('/sign_up') }}" class="hp-btn pr-btn-free">Start for free</a>
                        <p class="pr-fine">No card. No expiry. Nothing to cancel.</p>
                    </div>

                    <details class="plan-disc pr-holds" open>
                        <summary>
                            What's included
                            <svg aria-hidden="true" class="plan-disc-chev" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <ul class="pr-list">
                            @foreach ($freeFeatures as $feature)
                                <li @isset($needByLine[$feature]) data-pr-line="{{ $needByLine[$feature] }}" @endisset>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    </details>

                    <p class="pr-next">Putting a price on a ticket or a booking is <a href="#plan-pro">Pro</a>.</p>
                </article>

                {{-- Pro (the recommendation) --}}
                <article id="plan-pro" class="pr-card" data-plan="pro" data-reveal>
                    <header class="pr-cap">
                        <div class="pr-card-top">
                            <h3 class="pr-name">Pro</h3>
                            <span class="pr-mark is-popular">Most popular</span>
                            <span class="pr-mark is-yours">Your plan</span>
                        </div>
                        <p class="pr-trial">{{ $trialDays }}-day free trial</p>
                    </header>

                    <div class="pr-price">
                        <span class="bt-price-year">
                            <span class="pr-amount">{{ plan_price($proYearly) }}</span>
                            <span class="pr-per"><b>/year</b><a href="#per-schedule">per schedule</a></span>
                        </span>
                        <span class="bt-price-month">
                            <span class="pr-amount">{{ plan_price($proMonthly) }}</span>
                            <span class="pr-per"><b>/month</b><a href="#per-schedule">per schedule</a></span>
                        </span>
                    </div>
                    <div class="pr-note">
                        <p class="bt-note-year">Just {{ plan_price($proPerMonth) }}/month, billed annually after your free trial</p>
                        <p class="bt-note-month">Billed monthly after your free trial</p>
                    </div>

                    <div class="pr-cta">
                        <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary">Start free trial</a>
                        <div class="pr-fine">
                            <p class="bt-note-year">{{ $trialDays }} days free, then {{ plan_price($proYearly) }} a year. Cancel any time and the schedule stays live.</p>
                            <p class="bt-note-month">{{ $trialDays }} days free, then {{ plan_price($proMonthly) }} a month. Cancel any time and the schedule stays live.</p>
                        </div>
                    </div>

                    <div class="pr-holds">
                        <ul class="pr-list">
                            @foreach ($proFeatures as $feature)
                                <li @isset($needByLine[$feature]) data-pr-line="{{ $needByLine[$feature] }}" @endisset>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <p class="pr-next">Reserved seating, a team and your own domain are <a href="#plan-enterprise">Enterprise</a>.</p>
                </article>

                {{-- Enterprise --}}
                <article id="plan-enterprise" class="pr-card" data-plan="enterprise" data-reveal>
                    <header class="pr-cap">
                        <div class="pr-card-top">
                            <h3 class="pr-name">Enterprise</h3>
                            <span class="pr-mark is-yours">Your plan</span>
                        </div>
                        <p class="pr-trial">{{ $trialDays }}-day free trial</p>
                    </header>

                    <div class="pr-price">
                        <span class="bt-price-year">
                            <span class="pr-amount">{{ plan_price($entYearly) }}</span>
                            <span class="pr-per"><b>/year</b><a href="#per-schedule">per schedule</a></span>
                        </span>
                        <span class="bt-price-month">
                            <span class="pr-amount">{{ plan_price($entMonthly) }}</span>
                            <span class="pr-per"><b>/month</b><a href="#per-schedule">per schedule</a></span>
                        </span>
                    </div>
                    <div class="pr-note">
                        <p class="bt-note-year">Just {{ plan_price($entPerMonth) }}/month, billed annually after your free trial</p>
                        <p class="bt-note-month">Billed monthly after your free trial</p>
                    </div>

                    <div class="pr-cta">
                        <a href="{{ app_url('/sign_up') }}" class="hp-btn pr-btn-ent">Start free trial</a>
                        <p class="pr-fine">{{ $trialDays }} days free. Or selfhost, where every line below is included at no cost.</p>
                    </div>

                    <details class="plan-disc pr-holds" open>
                        <summary>
                            What's included
                            <svg aria-hidden="true" class="plan-disc-chev" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <ul class="pr-list">
                            @foreach ($enterpriseFeatures as $feature)
                                {{-- "Everything in Pro" answers for a Pro need picked beside an Enterprise one. --}}
                                <li @isset($needByLine[$feature]) data-pr-line="{{ $needByLine[$feature] }}" @endisset @if ($loop->first) data-pr-carries="pro" @endif>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    </details>

                    <p class="pr-next">Rather run it yourself? The <a href="#selfhost">selfhosted edition</a> is just below.</p>
                </article>
            </div>

            <p class="pr-jump">
                <a href="#compare" class="hp-more">
                    Compare all {{ \Illuminate\Support\Number::spell(count($planRows)) }} rows
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </a>
            </p>

            {{-- The fourth price. --}}
            <aside id="selfhost" class="pr-own" data-reveal>
                <span class="pr-own-mark" aria-hidden="true">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="hp-h3">Or run it yourself. <span>Free, forever.</span></h2>
                    <p>Event Schedule is open source. Install it on your own server and every Enterprise feature is included at no cost, with your data staying entirely on your infrastructure.</p>
                </div>
                <div class="pr-own-go">
                    <a href="{{ marketing_url('/selfhost') }}" class="hp-btn hp-btn-ghost is-small">Selfhosting guide</a>
                    <a href="{{ marketing_url('/open-source') }}" class="hp-more">
                        View the source
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </div>
            </aside>
        </div>
    </section>

    {{-- ============================================================
         The cut: one ticket, drawn to scale, twice
         ============================================================ --}}
    @php
        // What the script needs to say about each platform it can be set against.
        $rivalInfo = [];
        foreach ($rivals as $rivalKey) {
            $rivalInfo[$rivalKey] = [
                'name' => $feeRates[$rivalKey]['name'],
                'label' => $feeRates[$rivalKey]['label'],
                'basis' => $feeRates[$rivalKey]['basis'],
            ];
        }
        $theirTake = $theirs['fees'] + $theirs['plan'];
        $ourTake = $ours['fees'] + $ours['plan'];
        $isFree = $calcFree;
        $isWin = $calcWin;
        $isTie = $calcTie;
        $isLose = ! $isFree && ! $isWin && ! $isTie;

        // A cost in the settlement: a minus sign in front, and a plain nought where there is none.
        $less = fn ($amount) => round($amount, 2) > 0 ? '−'.$usd($amount) : $usd(0);

        // One ticket's worth of each part. The smaller part is rounded and card processing is
        // what is left of the ticket, so the three figures always add up to the ticket's price.
        // When the fees are more than the ticket, each part is simply its own figure.
        $onOne = function (array $parts, float $take) use ($calcTickets, $calcPrice, $calcGross) {
            if ($calcTickets <= 0) {
                return ['keep' => 0.0, 'take' => 0.0, 'processing' => 0.0];
            }
            $keep = round(($calcGross - $parts['total']) / $calcTickets, 2);
            $takeEach = round($take / $calcTickets, 2);

            return ['keep' => $keep, 'take' => $takeEach, 'processing' => $keep > 0
                ? max(0, round($calcPrice - $keep - $takeEach, 2))
                : round($parts['processing'] / $calcTickets, 2)];
        };
        $theirOne = $onOne($theirs, $theirTake);
        $ourOne = $onOne($ours, $ourTake);

        // A share of one ticket that is real and rounds to nothing is given in tenths of a cent, not printed as a nought.
        $slice = fn (float $rounded, float $exact) => ($rounded > 0 || $exact <= 0 || $calcTickets <= 0)
            ? $usd($rounded)
            : number_format(max(1, round($exact / $calcTickets * 1000)) / 10, 1).'¢';

        // The settlement's processing line is likewise what is left of the total.
        $settled = fn (array $parts) => max(0, round(round($parts['total'], 2) - round($parts['fees'], 2) - round($parts['plan'], 2), 2));

        // How one side's ticket is drawn. Fees no larger than the ticket are cut from it to
        // scale. Fees larger than the ticket leave none of it: the two parts then fill the
        // drawing in their own proportion, and the page says it is no longer to scale.
        $drawn = function (array $parts, float $take) use ($calcGross) {
            $over = $calcGross > 0 && round($parts['total'], 2) >= round($calcGross, 2);
            if ($calcGross <= 0 || $parts['total'] <= 0) {
                return ['plat' => '0', 'proc' => '0', 'tilt' => '1', 'over' => false];
            }
            $plat = $over ? $take / $parts['total'] * 100 : $take / $calcGross * 100;
            $proc = $over ? 100 - $plat : $parts['processing'] / $calcGross * 100;

            return [
                'plat' => number_format($plat, 4, '.', ''),
                'proc' => number_format($proc, 4, '.', ''),
                // How far the torn piece swings: less the wider it is, so a large one stays on its row.
                'tilt' => number_format(min(1, 12 / max($plat, 1)), 3, '.', ''),
                'over' => $over,
            ];
        };
        $theirDraw = $drawn($theirs, $theirTake);
        $ourDraw = $drawn($ours, $ourTake);
        $anyOver = $theirDraw['over'] || $ourDraw['over'];

        // A platform whose cheapest way is a subscription takes no cut of a ticket either, so
        // its month is drawn as ours is: on the ticket, not torn from it.
        $theirPlanOnly = round($theirs['fees'], 2) <= 0 && round($theirs['plan'], 2) > 0;

        // The night as a roll of tickets: how much of it each side's costs come to.
        // Its sentence counts tickets only where there are tickets left over to count.
        $rollCounts = $calcTickets > 1 && $worth($calcEs) < $calcTickets;
        $rolled = fn ($cost) => $calcGross > 0 ? number_format(min(100, $cost / $calcGross * 100), 3, '.', '') : '0';
    @endphp
    <section id="fees" class="hp-dark pr-cut @if ($isFree) is-free @endif"
             data-fee-tickets="{{ $calcTickets }}" data-fee-price="{{ $calcPrice }}"
             data-rates="{{ json_encode(\App\Utils\TicketFees::forScript(array_merge(['eventschedule'], $rivals), $feeRates)) }}"
             data-pr-rivals="{{ json_encode($rivalInfo) }}"
             data-pr-rival="{{ $rival }}">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>What it costs</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Zero platform fees. <span class="hp-ink-grad">Here's the math.</span>
                </h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.14s;">
                    Most ticketing platforms take a cut of every ticket. We take none. Move the numbers and see what that means for your event.
                </p>
            </div>

            <div class="pr-desk">
                {{-- Your event, and what it comes to. On a laptop this is one panel that stays
                     beside the tickets. Where the page is one column the panel dissolves and its
                     two dials ride along under the header instead, with the answer in a line. --}}
                <div class="pr-panel">
                    <div class="pr-dials pr-only-js">
                        <div class="pr-field">
                            <div class="pr-field-name">
                                <label for="pf-tickets">Tickets sold</label>
                                <small>at one event</small>
                            </div>
                            <span class="pr-num"><input id="pf-tickets" type="number" inputmode="numeric" value="{{ $calcTickets }}" min="1" max="100000"></span>
                            <input class="pr-range" type="range" min="10" max="2000" step="10" value="{{ $calcTickets }}" aria-label="Tickets sold, by slider" aria-valuetext="{{ number_format($calcTickets) }} tickets" data-pr-range="tickets">
                        </div>
                        <div class="pr-field">
                            <div class="pr-field-name">
                                <label for="pf-price">Ticket price</label>
                                <button type="button" class="pr-zero" aria-pressed="{{ $isFree ? 'true' : 'false' }}" data-pr-zero>Free entry</button>
                            </div>
                            <span class="pr-num is-money"><i aria-hidden="true">$</i><input id="pf-price" type="number" inputmode="decimal" value="{{ $calcPrice }}" min="0" max="10000"></span>
                            <input class="pr-range" type="range" min="0" max="200" step="1" value="{{ $calcPrice }}" aria-label="Ticket price, by slider" aria-valuetext="{{ $usd($calcPrice) }}" data-pr-range="price">
                        </div>
                        <p class="pr-dials-say" aria-hidden="true">
                            <span data-pr-verdict="win" @if (! $isWin) hidden @endif>You keep <b data-pr-save>{{ $usd(max(0, $calcSave)) }}</b> more</span>
                            <span data-pr-verdict="lose" @if (! $isLose) hidden @endif><span data-pr-rival-name>{{ $feeRates[$rival]['name'] }}</span> costs <b data-pr-gap>{{ $usd(abs($calcSave)) }}</b> less</span>
                            <span data-pr-verdict="tie" @if (! $isTie) hidden @endif>The two cost the same</span>
                            <span data-pr-verdict="free" @if (! $isFree) hidden @endif>Nothing to pay</span>
                        </p>
                    </div>

                    <div class="pr-field pr-against pr-only-js">
                        <span id="pr-rival-label">Set against</span>
                        <div class="pr-rivals" role="group" aria-labelledby="pr-rival-label">
                            @foreach ($rivals as $rivalKey)
                                <button type="button" class="pr-rival" aria-pressed="{{ $rivalKey === $rival ? 'true' : 'false' }}" data-pr-pick="{{ $rivalKey }}">{{ $feeRates[$rivalKey]['name'] }}</button>
                            @endforeach
                        </div>
                    </div>
                    <p class="pr-still">{{ number_format($calcTickets) }} tickets at {{ $usd($calcPrice) }}, set against {{ $feeRates[$rival]['name'] }}</p>

                    <div class="pr-answer" data-reveal>
                        <p class="sr-only" role="status" data-pr-live></p>
                        <p class="pr-verdict">
                            <span data-pr-verdict="win" @if (! $isWin) hidden @endif>You keep <b id="pf-save">${{ number_format($calcSave, 2) }}</b> more on this one event.</span>
                            <span data-pr-verdict="lose" @if (! $isLose) hidden @endif>On this event, <span data-pr-rival-name>{{ $feeRates[$rival]['name'] }}</span> costs <b data-pr-gap>{{ $usd(abs($calcSave)) }}</b> less.</span>
                            <span data-pr-verdict="tie" @if (! $isTie) hidden @endif>On this event the two cost the same.</span>
                            <span data-pr-verdict="free" @if (! $isFree) hidden @endif>A free event costs nothing here, and needs no paid plan.</span>
                        </p>
                        <p class="pr-ahead">
                            <span data-pr-ahead-say="win" @if (! $isWin || $aheadFrom === null) hidden @endif>You are ahead from <b>ticket no. <span data-pr-ahead>{{ number_format((int) $aheadFrom) }}</span></b>.</span>
                            <span data-pr-ahead-say="lose" @if (! $isLose) hidden @endif>Pro is one flat price for the month, so the gap closes with every ticket you sell.</span>
                            <span data-pr-ahead-say="tie" @if (! $isTie) hidden @endif>Pro is one flat price for the month, so every ticket after this is in your favour.</span>
                            <span data-pr-ahead-say="free" @if (! $isFree) hidden @endif>Free registration, a guest list and scanning at the door are on the Free plan.</span>
                        </p>
                        <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary">
                            <span data-pr-go="paid" @if ($isFree) hidden @endif>Start your free trial</span>
                            <span data-pr-go="free" @if (! $isFree) hidden @endif>Start for free</span>
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <div class="pr-proof">
                    <div class="pr-tix" data-reveal>
                        {{-- One ticket price, end to end: what both tickets below are measured against. --}}
                        <div class="pr-rule" aria-hidden="true" dir="ltr">
                            <span>$0</span>
                            <span>
                                <span data-pr-scale="true" @if ($anyOver) hidden @endif>One ticket, drawn to scale</span>
                                <span data-pr-scale="false" @if (! $anyOver) hidden @endif>Fees past the ticket: not to scale</span>
                            </span>
                            <span data-pr-price>{{ $usd($calcPrice) }}</span>
                        </div>

                        {{-- Theirs --}}
                        <figure @class(['pr-tk', 'is-theirs', 'is-over' => $theirDraw['over'], 'is-plan' => $theirPlanOnly]) data-pr-tk="theirs">
                            <figcaption class="pr-tk-cap">
                                <b>On <span data-pr-rival-name>{{ $feeRates[$rival]['name'] }}</span></b>
                                <span class="pr-tk-rate" data-pr-rival-label>{{ $feeRates[$rival]['label'] }}</span>
                            </figcaption>
                            <div class="pr-tk-row" dir="ltr" style="--proc: {{ $theirDraw['proc'] }}; --plat: {{ $theirDraw['plat'] }}; --tilt: {{ $theirDraw['tilt'] }};">
                                <div class="pr-tk-body">
                                    <div class="pr-tk-face">
                                        <span class="pr-tk-stub" aria-hidden="true"><i></i><small>No. <span data-pr-serial>{{ str_pad((string) $calcTickets, 4, '0', STR_PAD_LEFT) }}</span></small></span>
                                        <span class="pr-tk-admit"><small>Admit one</small><b data-pr-price>{{ $usd($calcPrice) }}</b></span>
                                        <i class="pr-tk-mid" aria-hidden="true"></i>
                                        <span class="pr-tk-yours"><small>You keep</small><b data-pr-each="theirs-keep">{{ $usd($theirOne['keep']) }}</b></span>
                                    </div>
                                    <div class="pr-tk-proc"></div>
                                </div>
                                <div class="pr-tk-cut" aria-hidden="true"><span data-pr-each="theirs-take">{{ $slice($theirOne['take'], $theirTake) }}</span></div>
                            </div>
                            <ul class="pr-key">
                                <li class="pr-key-keep"><i class="is-keep" aria-hidden="true"></i><span>You keep <b data-pr-each="theirs-keep">{{ $usd($theirOne['keep']) }}</b></span></li>
                                <li><i class="is-take" aria-hidden="true"></i><span>
                                    <span data-pr-theirs="fee" @if ($theirPlanOnly) hidden @endif><span data-pr-rival-name>{{ $feeRates[$rival]['name'] }}</span> takes <b data-pr-each="theirs-take">{{ $slice($theirOne['take'], $theirTake) }}</b> of each ticket</span>
                                    <span data-pr-theirs="plan" @if (! $theirPlanOnly) hidden @endif>A month of its plan, spread over <span data-pr-count>{{ number_format($calcTickets) }}</span> <span data-pr-noun>{{ $calcTickets === 1 ? 'ticket' : 'tickets' }}</span>, comes to <b data-pr-each="theirs-take">{{ $slice($theirOne['take'], $theirTake) }}</b> a ticket</span>
                                </span></li>
                                <li><i class="is-proc" aria-hidden="true"></i><span>Card processing takes <b data-pr-each="theirs-processing">{{ $usd($theirOne['processing']) }}</b></span></li>
                            </ul>
                        </figure>

                        {{-- Ours --}}
                        <figure @class(['pr-tk', 'is-ours', 'is-over' => $ourDraw['over']]) data-pr-tk="ours">
                            <figcaption class="pr-tk-cap">
                                <b>On Event Schedule</b>
                                {{-- Deliberately a dollar sign, and NOT plan_price(). This label sits inside the
                                     fee calculator, whose totals are a USD unit (see the note on $feeRates). --}}
                                <span class="pr-tk-rate">${{ $feeRates['eventschedule']['monthly'] }}/month + Stripe, 0% platform fee</span>
                            </figcaption>
                            <div class="pr-tk-row" dir="ltr" style="--proc: {{ $ourDraw['proc'] }}; --plat: {{ $ourDraw['plat'] }};">
                                <div class="pr-tk-body">
                                    <div class="pr-tk-face">
                                        <span class="pr-tk-stub" aria-hidden="true"><i></i><small>No. <span data-pr-serial>{{ str_pad((string) $calcTickets, 4, '0', STR_PAD_LEFT) }}</span></small></span>
                                        <span class="pr-tk-admit"><small>Admit one</small><b data-pr-price>{{ $usd($calcPrice) }}</b></span>
                                        <i class="pr-tk-mid" aria-hidden="true"></i>
                                        <span class="pr-tk-yours"><small>You keep</small><b data-pr-each="ours-keep">{{ $usd($ourOne['keep']) }}</b></span>
                                    </div>
                                    <div class="pr-tk-proc"></div>
                                    <div class="pr-tk-line" aria-hidden="true"><span data-pr-each="ours-take">{{ $slice($ourOne['take'], $ourTake) }}</span></div>
                                </div>
                            </div>
                            <ul class="pr-key">
                                <li class="pr-key-keep"><i class="is-keep" aria-hidden="true"></i><span>You keep <b data-pr-each="ours-keep">{{ $usd($ourOne['keep']) }}</b></span></li>
                                <li><i class="is-ours" aria-hidden="true"></i><span>
                                    <span data-pr-spread="many" @if ($calcTickets === 1) hidden @endif>A month of Pro, spread over <span data-pr-count>{{ number_format($calcTickets) }}</span> tickets, comes to <b data-pr-each="ours-take">{{ $slice($ourOne['take'], $ourTake) }}</b> a ticket</span>
                                    <span data-pr-spread="one" @if ($calcTickets !== 1) hidden @endif>A month of Pro, on a single ticket, is <b data-pr-each="ours-take">{{ $slice($ourOne['take'], $ourTake) }}</b></span>
                                </span></li>
                                <li><i class="is-proc" aria-hidden="true"></i><span>Card processing takes <b data-pr-each="ours-processing">{{ $usd($ourOne['processing']) }}</b></span></li>
                            </ul>
                        </figure>
                    </div>

                    {{-- The whole night, as a roll of tickets: how many of them each side's costs
                         come to. The roll is the night's sales; what is torn from it is to scale. --}}
                    <div class="pr-rolls" data-reveal>
                        <p class="pr-rolls-say">
                            <span data-pr-roll-say="count" @if (! $rollCounts) hidden @endif>Of your <span data-pr-count>{{ number_format($calcTickets) }}</span> tickets, the first <b data-pr-worth="theirs">{{ number_format($worth($calcEb)) }}</b> go on fees on <span data-pr-rival-name>{{ $feeRates[$rival]['name'] }}</span>. Here, the first <b data-pr-worth="ours">{{ number_format($worth($calcEs)) }}</b>.</span>
                            <span data-pr-roll-say="plain" @if ($rollCounts) hidden @endif>What each takes out of the night's ticket sales.</span>
                        </p>
                        <dl class="pr-roll-pair">
                            <div class="is-theirs">
                                <dt>On <span data-pr-rival-name>{{ $feeRates[$rival]['name'] }}</span></dt>
                                <dd><b id="pf-eb" data-fee-total="{{ $rival }}">${{ number_format($calcEb, 2) }}</b></dd>
                                <dd class="pr-roll" aria-hidden="true" dir="ltr"><i data-pr-roll="theirs" style="--w: {{ $rolled($calcEb) }};"></i></dd>
                            </div>
                            <div class="is-ours">
                                <dt>On Event Schedule</dt>
                                <dd><b id="pf-es" data-fee-total="eventschedule">${{ number_format($calcEs, 2) }}</b></dd>
                                <dd class="pr-roll" aria-hidden="true" dir="ltr"><i data-pr-roll="ours" style="--w: {{ $rolled($calcEs) }};"></i></dd>
                            </div>
                        </dl>
                    </div>

                    {{-- A free event: there is nothing to draw as taken, so one whole ticket says so. --}}
                    <div class="pr-gratis">
                        <div class="pr-tk is-ours">
                            <div class="pr-tk-row" dir="ltr">
                                <div class="pr-tk-body">
                                    <div class="pr-tk-face">
                                        <span class="pr-tk-stub" aria-hidden="true"><i></i><small>No. <span data-pr-serial>{{ str_pad((string) $calcTickets, 4, '0', STR_PAD_LEFT) }}</span></small></span>
                                        <span class="pr-tk-admit"><small>Admit one</small><b>Free entry</b></span>
                                        <i class="pr-tk-mid" aria-hidden="true"></i>
                                        <span class="pr-tk-yours"><small>It costs you</small><b>{{ plan_price(0) }}</b></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <ul class="pr-gratis-list">
                            <li>Unlimited free registration</li>
                            <li>A QR code by email for every guest</li>
                            <li>Guests scanned in at the door</li>
                            <li>No card, and no time limit</li>
                        </ul>
                        <div class="pr-gratis-then">
                            <p>The day you put a price on a ticket, that is Pro: {{ plan_price($proMonthly) }} a month, and still nothing taken from the sale.</p>
                            <button type="button" class="hp-btn hp-btn-ghost is-small pr-only-js" data-pr-zero>See it with a price</button>
                        </div>
                    </div>

                    <table class="pr-sheet" data-reveal>
                        <caption class="sr-only">The same event, settled on each platform</caption>
                        <thead>
                            <tr>
                                <th scope="col">The night, settled</th>
                                <th scope="col">On <span data-pr-rival-name>{{ $feeRates[$rival]['name'] }}</span></th>
                                <th scope="col">On Event Schedule</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">Ticket sales <small><span data-pr-count>{{ number_format($calcTickets) }}</span> at <span data-pr-price>{{ $usd($calcPrice) }}</span></small></th>
                                <td data-pr-cell="gross">{{ $usd($calcGross) }}</td>
                                <td data-pr-cell="gross">{{ $usd($calcGross) }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Platform fee on tickets</th>
                                <td @class(['is-take' => round($theirs['fees'], 2) > 0]) data-pr-cell="theirs-fees">{{ $less($theirs['fees']) }}</td>
                                <td class="is-none" data-pr-cell="ours-fees">{{ $less($ours['fees']) }}</td>
                            </tr>
                            <tr>
                                <th scope="row">One month of the plan</th>
                                <td data-pr-cell="theirs-plan">{{ $less($theirs['plan']) }}</td>
                                <td data-pr-cell="ours-plan">{{ $less($ours['plan']) }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Card processing</th>
                                <td data-pr-cell="theirs-processing">{{ $less($settled($theirs)) }}</td>
                                <td data-pr-cell="ours-processing">{{ $less($settled($ours)) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="row">You keep</th>
                                <td data-pr-cell="theirs-keep">{{ $usd($calcGross - $theirs['total']) }}</td>
                                <td data-pr-cell="ours-keep">{{ $usd($calcGross - $ours['total']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="pr-feet">
                <p class="pr-feet-basis">
                    Estimates for an organizer who absorbs the fees rather than adding them to the buyer's price, with one ticket per order, at each platform's published US rates. Stripe processing ({{ $feeRates['stripe']['label'] }}) is included on the Event Schedule side, with one month of Pro. <span data-pr-basis>{{ $feeRates[$rival]['basis'] }}</span> Payouts go straight to your own Stripe account; connect PayPal instead and the money lands in your PayPal account the same way, at PayPal's own rate.
                </p>
                <p>
                    Weighing up a particular platform? The <x-link href="{{ marketing_url('/ticket-fee-calculator') }}">ticket fee calculator</x-link> sets several side by side at their published rates.
                </p>
            </div>
        </div>
    </section>

    {{-- ============================================================
         Compare plans
         ============================================================ --}}
    <section id="compare-plans" class="hp-sec is-tight">
        <div class="hp-wrap">
            {{-- Compare plans. The three cards say what each plan adds; this says, row by row,
                 what each plan has, including the rows that say no. The rows are the rate card's
                 (App\Utils\PlanRateCard), so this page and /faq cannot disagree. It ships open,
                 so a reader without JavaScript and a crawler get every row; the script below
                 closes it on a phone, where the summary is the way in. --}}
            <details id="compare" class="plan-disc pr-compare" open>
                <summary>
                    Compare plans, row by row
                    <svg aria-hidden="true" class="plan-disc-chev" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </summary>
                <div class="pr-compare-head" data-reveal>
                    <h2 class="hp-h3">Compare plans, row by row</h2>
                    <p>{{ ucfirst(\Illuminate\Support\Number::spell(count($planRows))) }} rows, including the ones that say no.</p>
                </div>
                <div class="pr-compare-body" data-reveal>
                    <table class="pc-table" role="table">
                        <caption class="sr-only">What each Event Schedule plan includes, with monthly and yearly prices</caption>
                        <thead role="rowgroup">
                            <tr role="row">
                                <th scope="col" role="columnheader">What you get</th>
                                <th scope="col" role="columnheader" data-pr-col="free">Free <span class="pr-mark is-yours">Your plan</span></th>
                                <th scope="col" role="columnheader" data-pr-col="pro">Pro <span class="pr-mark is-yours">Your plan</span></th>
                                <th scope="col" role="columnheader" data-pr-col="enterprise">Enterprise <span class="pr-mark is-yours">Your plan</span></th>
                            </tr>
                        </thead>
                        <tbody role="rowgroup">
                            @foreach ($planRows as [$rowLabel, $rowFree, $rowPro, $rowEnt])
                                <tr role="row">
                                    <th scope="row" role="rowheader" @isset($needByRow[$rowLabel]) data-pr-row="{{ $needByRow[$rowLabel] }}" @endisset @if ($sameRows[$rowLabel] ?? false) data-pr-same @endif>{{ $rowLabel }}</th>
                                    <td role="cell" data-label="Free" class="{{ \App\Utils\PlanRateCard::includes($rowFree) ? 'pc-yes' : 'pc-no' }}">{{ $rowFree }}</td>
                                    <td role="cell" data-label="Pro" class="{{ \App\Utils\PlanRateCard::includes($rowPro) ? 'pc-yes' : 'pc-no' }}">{{ $rowPro }}</td>
                                    <td role="cell" data-label="Enterprise" class="{{ \App\Utils\PlanRateCard::includes($rowEnt) ? 'pc-yes' : 'pc-no' }}">{{ $rowEnt }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="pr-compare-foot">A selfhosted install resolves to the Enterprise feature set at no cost.</p>
                </div>
            </details>
        </div>
    </section>

    {{-- ============================================================
         The small print, in large print
         ============================================================ --}}
    <section id="terms" class="hp-sec hp-alt">
        <div class="hp-wrap">
            <div class="hp-head">
                <span class="hp-kicker" data-reveal>The terms</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">
                    The small print, <span class="hp-ink-grad">in large print.</span>
                </h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.14s;">
                    {{ ucfirst(\Illuminate\Support\Number::spell(count($clauses))) }} things a pricing page usually leaves for the footnotes.
                </p>
            </div>

            <ol class="pr-print" data-reveal-group="70">
                @foreach ($clauses as $clauseId => [$clauseSays, $clauseMeans])
                    <li data-reveal @if (is_string($clauseId)) id="{{ $clauseId }}" @endif>
                        <h3>{{ $clauseSays }}</h3>
                        <p>{{ $clauseMeans }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ============================================================
         FAQ
         ============================================================ --}}
    <x-seo.faq-schema :items="$faqs" />
    <x-marketing.hp-faq id="faq" :items="$faqs" lead="Everything you need to know about pricing.">Frequently asked <span class="hp-ink-grad">questions</span></x-marketing.hp-faq>

    <x-marketing.related-pages />

    {{-- ============================================================
         Finale
         ============================================================ --}}
    <x-marketing.hp-finale lead="Create your free schedule in seconds. Start your free trial today." placeholder="your-schedule" :foot="false">
        Start sharing your events <span class="hp-ink-grad">today</span>
        <x-slot name="after">
            <p class="hp-finale-foot">Know other organizers? <a href="{{ route('marketing.docs.referral_program') }}">Earn free months with our referral program</a>.</p>
        </x-slot>
    </x-marketing.hp-finale>

    {{-- The billing toggle, the plan finder, the cut and the phone's plan disclosure. Plain DOM
         script: the page is server-rendered and edge-cached, and nothing here needs a framework. --}}
    @include('marketing.partials.ticket-fee-math')
    <script {!! nonce_attr() !!}>
        (function () {
            var page = document.getElementById('hp');
            var plans = document.getElementById('pricing-plans');
            if (!page || !plans) { return; }

            {{-- Monthly or annual: one class on the page's wrapper drives every price, note and period. --}}
            var monthBtn = document.getElementById('bt-monthly');
            var yearBtn = document.getElementById('bt-annual');
            if (monthBtn && yearBtn) {
                var setAnnual = function (annual) {
                    page.classList.toggle('is-annual', annual);
                    monthBtn.setAttribute('aria-pressed', annual ? 'false' : 'true');
                    yearBtn.setAttribute('aria-pressed', annual ? 'true' : 'false');
                };
                monthBtn.addEventListener('click', function () { setAnnual(false); });
                yearBtn.addEventListener('click', function () { setAnnual(true); });
            }

            {{-- The lists ship open, so a reader without JavaScript and a crawler see every item.
                 Where the cards stand one under another, Free and Enterprise fold away and Pro is
                 the open one, which is the phone's stand-in for the light it stands in on a desk.
                 This runs when the layout changes and at no other time, so it never closes what a
                 visitor opened. --}}
            var lists = plans.querySelectorAll('details.pr-holds');
            var table = document.getElementById('compare');
            var stacked = window.matchMedia ? window.matchMedia('(max-width: 899.98px)') : null;
            var narrow = window.matchMedia ? window.matchMedia('(max-width: 767.98px)') : null;
            function fold() {
                lists.forEach(function (list) {
                    if (stacked && stacked.matches) { list.removeAttribute('open'); } else { list.setAttribute('open', ''); }
                });
                if (table) {
                    if (narrow && narrow.matches) { table.removeAttribute('open'); } else { table.setAttribute('open', ''); }
                }
            }
            if (stacked && stacked.addEventListener) {
                stacked.addEventListener('change', fold);
                narrow.addEventListener('change', fold);
            }
            fold();
            {{-- A link to the table opens it where it is folded away. --}}
            document.querySelectorAll('a[href="#compare"]').forEach(function (link) {
                link.addEventListener('click', function () { if (table) { table.setAttribute('open', ''); } });
            });
            {{-- The rows that read the same on every plan are named once, for the stylesheet. --}}
            page.querySelectorAll('[data-pr-same]').forEach(function (cell) { cell.parentNode.classList.add('is-same'); });

            {{-- Which plan is mine. A picked need marks the plan that covers it (read off the
                 rate card on the server: data-pr-plan), and lights the line and the row that say
                 so. "Free events" is an answer of its own, so it and the six that cost something
                 take each other's place. With nothing picked the page is as it was drawn. --}}
            var chips = plans.querySelectorAll('[data-pr-need]');
            var says = plans.querySelectorAll('[data-pr-say]');
            var clear = plans.querySelector('[data-pr-clear]');
            function find() {
                var plan = null;
                var picked = {};
                chips.forEach(function (chip) {
                    var on = chip.getAttribute('aria-pressed') === 'true';
                    var key = chip.getAttribute('data-pr-need');
                    var needs = chip.getAttribute('data-pr-plan');
                    picked[key] = on;
                    if (on && needs === 'enterprise') { plan = 'enterprise'; }
                    if (on && needs === 'pro' && plan !== 'enterprise') { plan = 'pro'; }
                    if (on && needs === 'free' && plan === null) { plan = 'free'; }
                    page.querySelectorAll('[data-pr-line="' + key + '"]').forEach(function (line) { line.classList.toggle('is-need', on); });
                    page.querySelectorAll('[data-pr-row="' + key + '"]').forEach(function (cell) { cell.parentNode.classList.toggle('is-need', on); });
                });
                {{-- "Everything in Pro" answers for a Pro need picked beside an Enterprise one. --}}
                var carried = false;
                chips.forEach(function (chip) {
                    if (chip.getAttribute('aria-pressed') === 'true' && chip.getAttribute('data-pr-plan') === 'pro') { carried = true; }
                });
                page.querySelectorAll('[data-pr-carries="pro"]').forEach(function (line) { line.classList.toggle('is-need', plan === 'enterprise' && carried); });

                if (plan === null) { page.removeAttribute('data-pr-yours'); } else { page.setAttribute('data-pr-yours', plan); }
                says.forEach(function (line) { line.hidden = line.getAttribute('data-pr-say') !== (plan === null ? 'rest' : plan); });
                plans.querySelectorAll('[data-pr-if]').forEach(function (extra) { extra.hidden = !picked[extra.getAttribute('data-pr-if')]; });
                if (clear) { clear.hidden = plan === null; }
                {{-- Where the cards are stacked, the marked plan's list opens so its lit line can be
                     seen. Nothing the visitor opened is closed. --}}
                if (plan !== null && stacked && stacked.matches) {
                    var list = plans.querySelector('.pr-card[data-plan="' + plan + '"] details.pr-holds');
                    if (list) { list.setAttribute('open', ''); }
                }
            }
            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    var on = chip.getAttribute('aria-pressed') !== 'true';
                    var free = chip.getAttribute('data-pr-plan') === 'free';
                    if (on) {
                        chips.forEach(function (other) {
                            if ((other.getAttribute('data-pr-plan') === 'free') !== free) { other.setAttribute('aria-pressed', 'false'); }
                        });
                    }
                    chip.setAttribute('aria-pressed', on ? 'true' : 'false');
                    find();
                });
            });
            if (clear) {
                clear.addEventListener('click', function () {
                    chips.forEach(function (chip) { chip.setAttribute('aria-pressed', 'false'); });
                    find();
                    if (chips[0]) { chips[0].focus(); }
                });
            }
        })();

        (function () {
            var cut = document.getElementById('fees');
            var ticketsEl = document.getElementById('pf-tickets');
            var priceEl = document.getElementById('pf-price');
            if (!cut || !ticketsEl || !priceEl || !window.esTicketFeeCost) { return; }

            {{-- The same rates the server rendered with (App\Utils\TicketFees), through the same
                 formula, so this band, /compare and /for-talent cannot disagree. --}}
            var rates;
            var info;
            try {
                rates = JSON.parse(cut.getAttribute('data-rates'));
                info = JSON.parse(cut.getAttribute('data-pr-rivals'));
            } catch (e) { return; }
            if (!rates || !rates.stripe || !rates.eventschedule || !info) { return; }

            var rival = cut.getAttribute('data-pr-rival');
            var tickets = { theirs: cut.querySelector('[data-pr-tk="theirs"]'), ours: cut.querySelector('[data-pr-tk="ours"]') };
            var theirTotal = document.getElementById('pf-eb');
            var ourTotal = document.getElementById('pf-es');
            var saveEl = document.getElementById('pf-save');
            if (!tickets.theirs || !tickets.ours || !theirTotal || !ourTotal || !saveEl) { return; }
            var rows = { theirs: tickets.theirs.querySelector('.pr-tk-row'), ours: tickets.ours.querySelector('.pr-tk-row') };
            var live = cut.querySelector('[data-pr-live]');
            var ranges = { tickets: cut.querySelector('[data-pr-range="tickets"]'), price: cut.querySelector('[data-pr-range="price"]') };
            var settle = null;

            function cost(rate, count, price) { return window.esTicketFeeCost(rate, rates.stripe, count, price); }
            function copy(rate, changes) {
                var out = {};
                Object.keys(rate).forEach(function (key) { if (key !== 'plans') { out[key] = rate[key]; } });
                Object.keys(changes || {}).forEach(function (key) { out[key] = changes[key]; });
                return out;
            }
            {{-- A platform with several plans is charged at the cheaper one, as the formula picks. --}}
            function charged(rate, count, price) {
                if (!rate.plans || !rate.plans.length) { return rate; }
                var best = null;
                var bestCost = 0;
                rate.plans.forEach(function (plan) {
                    var merged = copy(rate, plan);
                    var mergedCost = cost(merged, count, price);
                    if (best === null || mergedCost < bestCost) { best = merged; bestCost = mergedCost; }
                });
                return best;
            }
            {{-- One platform's take in three parts, each from the ONE formula with a part of the
                 rate switched off: its fee on each ticket, its subscription, and card processing. --}}
            function parts(rate, count, price) {
                var used = charged(rate, count, price);
                var total = cost(used, count, price);
                var own = copy(used, { processing: 0, stripe: false });
                var take = cost(own, count, price);
                var fees = cost(copy(own, { monthly: 0 }), count, price);
                return { total: total, fees: fees, plan: take - fees, processing: total - take };
            }

            {{-- To the cent, half up, as the server's round() does: toFixed() alone reads 1.025
                 as 1.02 where the first paint said 1.03. --}}
            function cents(n) {
                if (!isFinite(n) || Math.abs(n) < 1e-7) { return 0; }
                var sign = n < 0 ? -1 : 1;
                return sign * Number(Math.round(Number(Math.abs(n) + 'e2')) + 'e-2');
            }
            function money(n) {
                var value = cents(n);
                return (value < 0 ? '−' : '') + '$' + Math.abs(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
            function less(n) { return cents(n) > 0 ? '−' + money(n) : money(0); }
            {{-- A share of one ticket that is real and rounds to nothing is given in tenths of a cent, not printed as a nought. --}}
            function slice(rounded, exact, count) {
                if (rounded > 0 || exact <= 0 || count <= 0) { return money(rounded); }
                var tenths = Math.max(1, Math.round(exact / count * 1000));
                return (tenths / 10).toFixed(1) + '¢';
            }
            function put(selector, text) {
                cut.querySelectorAll(selector).forEach(function (el) { el.textContent = text; });
            }
            function show(attribute, which) {
                cut.querySelectorAll('[' + attribute + ']').forEach(function (el) { el.hidden = el.getAttribute(attribute) !== which; });
            }

            function calc() {
                {{-- A field that is empty, or holds no ticket at all, is being retyped: the last
                     answer stands until there is a new one. --}}
                var typedTickets = parseFloat(ticketsEl.value);
                var typedPrice = parseFloat(priceEl.value);
                if (!rates[rival] || isNaN(typedTickets) || isNaN(typedPrice) || typedTickets < 1) { return; }
                var count = Math.min(100000, Math.floor(typedTickets));
                var price = Math.min(10000, Math.max(0, typedPrice));
                var gross = count * price;
                var theirs = parts(rates[rival], count, price);
                var ours = parts(rates.eventschedule, count, price);
                var theirTake = theirs.fees + theirs.plan;
                var ourTake = ours.fees + ours.plan;
                var anyOver = false;

                {{-- How one side's ticket is drawn. Fees no larger than the ticket are cut from it
                     to scale. Fees larger than the ticket leave none of it: the two parts then
                     fill the drawing in their own proportion, and the page says it is no longer
                     to scale. The wider the torn piece, the less it swings. --}}
                var draw = function (figure, row, side, take) {
                    var over = gross > 0 && cents(side.total) >= cents(gross);
                    var plat = 0;
                    var proc = 0;
                    if (gross > 0 && side.total > 0) {
                        plat = over ? take / side.total * 100 : take / gross * 100;
                        proc = over ? 100 - plat : side.processing / gross * 100;
                    }
                    row.style.setProperty('--plat', plat.toFixed(4));
                    row.style.setProperty('--proc', proc.toFixed(4));
                    row.style.setProperty('--tilt', Math.min(1, 12 / Math.max(plat, 1)).toFixed(3));
                    figure.classList.toggle('is-over', over);
                    anyOver = anyOver || over;
                    {{-- A piece under fourteen pixels has no room for a torn edge; a face with no
                         room for "You keep" hands the figure to the line under the ticket. --}}
                    var width = row.getBoundingClientRect().width;
                    figure.classList.toggle('is-sliver', !over && plat > 0 && width * plat / 100 < 14);
                    figure.classList.toggle('is-tight', !over && width * (100 - plat - proc) / 100 < 110);
                };
                draw(tickets.theirs, rows.theirs, theirs, theirTake);
                draw(tickets.ours, rows.ours, ours, ourTake);
                {{-- A platform whose cheapest way is a subscription takes no cut of a ticket. --}}
                var planOnly = cents(theirs.fees) <= 0 && cents(theirs.plan) > 0;
                tickets.theirs.classList.toggle('is-plan', planOnly);
                show('data-pr-theirs', planOnly ? 'plan' : 'fee');
                show('data-pr-scale', anyOver ? 'false' : 'true');
                show('data-pr-spread', count === 1 ? 'one' : 'many');

                {{-- One ticket's worth of each part: the smaller part rounded, card processing
                     what is left of the ticket, so the three always add up to its price. When the
                     fees are more than the ticket, each part is simply its own figure. --}}
                var onOne = function (side, take) {
                    var keep = cents((gross - side.total) / count);
                    var takeEach = cents(take / count);
                    return { keep: keep, take: takeEach, processing: keep > 0 ? Math.max(0, cents(price - keep - takeEach)) : cents(side.processing / count) };
                };
                var theirOne = onOne(theirs, theirTake);
                var ourOne = onOne(ours, ourTake);
                var settled = function (side) { return Math.max(0, cents(cents(side.total) - cents(side.fees) - cents(side.plan))); };

                put('[data-pr-price]', money(price));
                put('[data-pr-count]', count.toLocaleString('en-US'));
                put('[data-pr-noun]', count === 1 ? 'ticket' : 'tickets');
                put('[data-pr-serial]', String(count).length < 4 ? ('0000' + count).slice(-4) : String(count));
                put('[data-pr-each="theirs-keep"]', money(theirOne.keep));
                put('[data-pr-each="theirs-take"]', slice(theirOne.take, theirTake, count));
                put('[data-pr-each="theirs-processing"]', money(theirOne.processing));
                put('[data-pr-each="ours-keep"]', money(ourOne.keep));
                put('[data-pr-each="ours-take"]', slice(ourOne.take, ourTake, count));
                put('[data-pr-each="ours-processing"]', money(ourOne.processing));

                put('[data-pr-cell="gross"]', money(gross));
                put('[data-pr-cell="theirs-fees"]', less(theirs.fees));
                put('[data-pr-cell="theirs-plan"]', less(theirs.plan));
                put('[data-pr-cell="theirs-processing"]', less(settled(theirs)));
                put('[data-pr-cell="theirs-keep"]', money(gross - theirs.total));
                put('[data-pr-cell="ours-fees"]', less(ours.fees));
                put('[data-pr-cell="ours-plan"]', less(ours.plan));
                put('[data-pr-cell="ours-processing"]', less(settled(ours)));
                put('[data-pr-cell="ours-keep"]', money(gross - ours.total));
                cut.querySelectorAll('[data-pr-cell="theirs-fees"]').forEach(function (cell) { cell.classList.toggle('is-take', cents(theirs.fees) > 0); });

                {{-- The night as a roll of tickets: how much of it each side's costs come to, and
                     how many of the tickets that is. --}}
                theirTotal.textContent = money(theirs.total);
                theirTotal.setAttribute('data-fee-total', rival);
                ourTotal.textContent = money(ours.total);
                var rolled = function (key, total) {
                    cut.querySelectorAll('[data-pr-roll="' + key + '"]').forEach(function (roll) {
                        roll.style.setProperty('--w', (gross > 0 ? Math.min(100, total / gross * 100) : 0).toFixed(3));
                    });
                    put('[data-pr-worth="' + key + '"]', (price > 0 ? Math.min(count, Math.ceil(Number((total / price).toFixed(6)))) : 0).toLocaleString('en-US'));
                };
                rolled('theirs', theirs.total);
                rolled('ours', ours.total);
                show('data-pr-roll-say', (count > 1 && price > 0 && Math.ceil(Number((ours.total / price).toFixed(6))) < count) ? 'count' : 'plain');

                var save = cents(cents(theirs.total) - cents(ours.total));
                var state = price <= 0 ? 'free' : (save > 0 ? 'win' : (save < 0 ? 'lose' : 'tie'));
                saveEl.textContent = money(Math.max(0, save));
                put('[data-pr-save]', money(Math.max(0, save)));
                put('[data-pr-gap]', money(Math.abs(save)));
                show('data-pr-verdict', state);
                show('data-pr-go', state === 'free' ? 'free' : 'paid');
                cut.classList.toggle('is-free', state === 'free');

                {{-- The first ticket from which we cost less: asked of the formula, not worked out. --}}
                var from = null;
                if (state === 'win') {
                    var ahead = function (n) { return cost(rates[rival], n, price) > cost(rates.eventschedule, n, price); };
                    var low = 1;
                    var high = count;
                    if (ahead(high)) {
                        while (low < high) {
                            var middle = Math.floor((low + high) / 2);
                            if (ahead(middle)) { high = middle; } else { low = middle + 1; }
                        }
                        from = low;
                    }
                }
                put('[data-pr-ahead]', from === null ? '' : from.toLocaleString('en-US'));
                show('data-pr-ahead-say', state === 'win' && from === null ? 'none' : state);

                if (ranges.tickets) { ranges.tickets.setAttribute('aria-valuetext', count.toLocaleString('en-US') + (count === 1 ? ' ticket' : ' tickets')); }
                if (ranges.price) { ranges.price.setAttribute('aria-valuetext', money(price)); }
                cut.querySelectorAll('[data-pr-zero]').forEach(function (zero) { zero.setAttribute('aria-pressed', price > 0 ? 'false' : 'true'); });

                {{-- Said aloud once the numbers have stopped moving, not at every step of a slider. --}}
                if (live) {
                    clearTimeout(settle);
                    settle = setTimeout(function () {
                        var said = cut.querySelector('.pr-verdict [data-pr-verdict]:not([hidden])');
                        live.textContent = said ? said.textContent : '';
                    }, 900);
                }
            }

            {{-- A number and its slider are one value. The slider stops where it stops; the number goes on. --}}
            function pair(numberEl, rangeEl) {
                if (!rangeEl) { return; }
                rangeEl.addEventListener('input', function () { numberEl.value = rangeEl.value; calc(); });
                numberEl.addEventListener('input', function () {
                    var value = parseFloat(numberEl.value);
                    if (!isNaN(value)) { rangeEl.value = Math.min(parseFloat(rangeEl.max), Math.max(parseFloat(rangeEl.min), value)); }
                    calc();
                });
            }
            pair(ticketsEl, ranges.tickets);
            pair(priceEl, ranges.price);

            {{-- "Free entry" sets the price to nothing, and gives back the price it replaced. --}}
            var paid = parseFloat(priceEl.value) || parseFloat(cut.getAttribute('data-fee-price')) || 1;
            cut.querySelectorAll('[data-pr-zero]').forEach(function (zero) {
                zero.addEventListener('click', function () {
                    var now = parseFloat(priceEl.value) || 0;
                    if (now > 0) { paid = now; }
                    priceEl.value = now > 0 ? 0 : paid;
                    priceEl.dispatchEvent(new Event('input', { bubbles: true }));
                });
            });

            var picks = cut.querySelectorAll('[data-pr-pick]');
            picks.forEach(function (pick) {
                pick.addEventListener('click', function () {
                    var key = pick.getAttribute('data-pr-pick');
                    if (!rates[key] || !info[key]) { return; }
                    rival = key;
                    picks.forEach(function (other) { other.setAttribute('aria-pressed', other === pick ? 'true' : 'false'); });
                    put('[data-pr-rival-name]', info[key].name);
                    put('[data-pr-rival-label]', info[key].label);
                    put('[data-pr-basis]', info[key].basis);
                    calc();
                });
            });

            {{-- Where the dials ride under the header, their one-line answer stands down while the
                 full answer is on screen, so it is not said twice. --}}
            var answer = cut.querySelector('.pr-answer');
            if (answer && 'IntersectionObserver' in window) {
                new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) { cut.classList.toggle('is-answered', entry.isIntersecting); });
                }, { threshold: 0.35 }).observe(answer);
            }
        })();
    </script>

    {{-- Motion engines (the finale brings its own confetti) --}}
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
