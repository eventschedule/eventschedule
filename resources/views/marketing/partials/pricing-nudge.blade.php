{{-- Shared plan band, rendered on ~60 audience and feature pages.

     Because it is shared, a wrong tier here is wrong sixty times: it used to bill
     "QR check-in" as the Pro column when TicketController::scan() has no plan check
     at all. The Pro half is the live check-in DASHBOARD (CheckInController), and the
     free plan really does sell - Role::ticketSaleLimit() allows 25 paid tickets a
     calendar month per schedule. Both columns say so now.

     $proMonthly / $entMonthly come from AppServiceProvider's marketing.* composer,
     via PlatformPricing, so an operator's own prices flow through. Never print a
     bare currency symbol here: plan_price() carries platform_currency(). --}}
<section class="relative bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto mb-10 max-w-2xl text-center">
            <h2 class="es-balance text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>
                Free forever. Upgrade when you're ready.
            </h2>
            <p class="mt-3 text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.08s;">
                Zero platform fees on ticket sales, on every plan. The only deduction is your payment
                processor's own.
            </p>
        </div>

        @php
            // Each row: [tier, price, cadence, one-line summary, three specifics].
            $nudgePlans = [
                [
                    'Free',
                    plan_price(0),
                    'forever, no card',
                    'Everything you need to publish a schedule, and enough selling to find out whether you need more.',
                    [
                        'Unlimited events, sub-schedules and recurring dates',
                        'Two-way Google, Outlook and CalDAV sync',
                        '25 paid tickets a month, each one scanned at the door',
                    ],
                    false,
                ],
                [
                    'Pro',
                    plan_price($proMonthly),
                    'per month',
                    'The ceilings come off, and the rest of the selling kit arrives with them.',
                    [
                        'Unlimited ticket sales and the live check-in dashboard',
                        'Promo codes, add-ons, passes and gift cards',
                        'Event graphics, the REST API, and no Event Schedule branding',
                    ],
                    true,
                ],
                [
                    'Enterprise',
                    plan_price($entMonthly),
                    'per month',
                    'For a room with a seating chart, a team, and a domain of its own.',
                    [
                        'Reserved seating, drawn once and reused every date',
                        'Your own domain, and up to five team members',
                        'Internal and unlisted events, and 1,000 newsletter recipients',
                    ],
                    false,
                ],
            ];
        @endphp

        <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
            @foreach ($nudgePlans as [$nudgeTier, $nudgePrice, $nudgeCadence, $nudgeLede, $nudgePoints, $nudgeFeatured])
                <div @class([
                        'flex flex-col rounded-2xl border p-6 transition-all duration-200 hover:-translate-y-1 hover:shadow-lg',
                        'border-blue-300 bg-blue-50/40 dark:border-blue-500/40 dark:bg-blue-500/[0.07]' => $nudgeFeatured,
                        'border-gray-200 bg-white dark:border-white/10 dark:bg-white/[0.03]' => ! $nudgeFeatured,
                    ]) data-reveal="panel">
                    <div class="mb-3 flex items-baseline gap-2">
                        <span class="text-sm font-bold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400">{{ $nudgeTier }}</span>
                        @if ($nudgeFeatured)
                            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Most picked</span>
                        @endif
                    </div>
                    <div class="mb-1 flex items-baseline gap-1">
                        <span class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">{{ $nudgePrice }}</span>
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">{{ $nudgeCadence }}</span>
                    </div>
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">{{ $nudgeLede }}</p>
                    <ul class="mb-6 space-y-2">
                        @foreach ($nudgePoints as $nudgePoint)
                            <li class="flex gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <svg class="mt-0.5 h-4 w-4 flex-none text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ $nudgePoint }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-auto text-xs text-gray-500 dark:text-gray-400">
                        @if ($nudgeTier === 'Free')
                            No expiry, and no card asked for.
                        @elseif ($nudgeTier === 'Pro')
                            7-day free trial, cancel any time.
                        @else
                            Included free on every selfhosted install.
                        @endif
                    </p>
                </div>
            @endforeach
        </div>

        <p class="mt-8 text-center" data-reveal>
            <a href="{{ marketing_url('/pricing') }}" class="group inline-flex items-center gap-1.5 font-medium text-blue-600 transition-all hover:gap-2.5 dark:text-blue-400">
                Compare the plans in full
                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </p>
    </div>
</section>
