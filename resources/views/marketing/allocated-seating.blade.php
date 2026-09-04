<x-marketing-layout>
    <x-slot name="title">Allocated Seating | Reserved Seat Maps for Venues</x-slot>
    <x-slot name="description">Draw your venue once and sell the seats in it. A reusable seating plan, a buyer-facing seat picker, and a box office console for single seats.</x-slot>
    <x-slot name="breadcrumbTitle">Allocated Seating</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Allocated Seating",
        "description": "Reserved seating for venues: a reusable seating plan of levels, sections, rows, tables and standing areas, a buyer-facing seat picker, and a box office console.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "featureList": [
            "Drag and drop seating plan designer",
            "Multiple levels for stalls, circle and balcony",
            "Rows and seats, round and rectangular tables, and standing sections",
            "Wheelchair spaces and companion seats",
            "One reusable plan across every date of a run",
            "Edit a single date without touching the others",
            "Buyers choose their own seats from the map",
            "Best available seats chosen automatically",
            "Box office console to hold back, move and release single seats",
            "Take a booking over the phone against the map",
            "Printable seating plan report and CSV"
        ],
        "offers": {
            "@type": "Offer",
            "price": "{{ $entMonthly }}",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Available on Enterprise plan"
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
           Allocated-seating "The House Plan" styles.

           CONCEPT: THE PENCIL HOUSE PLAN A BOX OFFICE KEEPS. Before any
           of this was software it was a sheet of paper with the room
           drawn on it, seats crossed off as they went. That sheet is
           EXACTLY what the feature is - a drawing of the room that the
           selling happens against - so the page is drawn as one:
           squared paper, a hand-ruled grid, seats as small circles that
           fill in as they sell. The metaphor is not decoration; it is
           the data model.

           WHY SQUARED PAPER AND NOT AN ARCHITECT'S BLUEPRINT. A
           blueprint is a building; this is an INVENTORY. The plan does
           not care that the room is 14 metres wide, only that row C has
           twelve seats and a gangway after the sixth. Squared paper is
           the honest picture of a coordinate grid holding a count.

           DELIBERATELY NOT: an engraved plate (/features/custom-domain
           owns "The Nameplate"), a steel door or an exit-sign glow
           (/for-nightclubs), a keyring (/why-create-account), a playbill
           (/for-theaters), a spot ledger (/carpool, which owns
           .es-seat-* and is a COUNT of car spots, never a chooser).
           Nothing here is a ticket stub either - /features/ticketing
           already spends that.

           NO DECORATIVE LINE DRAWINGS. Per the house rule, the only
           strokes on this page are the grid itself and the seat circles,
           both of which carry information. There is no outline sketch of
           an auditorium, no velvet rope, no proscenium arch.

           COLOUR: the page takes the WP blue family and spends it as
           pencil-on-paper. Measured against the grounds this page
           actually paints, not against pure white:
             light ground #f5f7fb: ink #101522 16.68, muted #4a5468 7.31,
                                   accent #1d4ed8 7.06
             dark ground  #0a0d14: ink #e8ecf4 16.32, muted #9aa6bd 8.31,
                                   accent #7ea6ff 10.14
             sold seat fill #dc2626 on paper: its LABEL is white 4.83 on
                                   the fill, so seat labels sit OUTSIDE
                                   the circles, never on them.
           text-gray-500 is never used: it measures 4.83 on pure white
           but ~4.4 on these grounds. Use .es-house-muted.

           STATUS IS SHAPE, NOT ONLY COLOUR - the same rule the printable
           report enforces, for the same reason. Available is an open
           ring, sold is a filled disc with a cross, held back is hatched.
           A colour-blind reader and a mono printer both still read it.
           ============================================================== */

        .es-house-page { background-color: #f5f7fb; color: #101522; }
        .dark .es-house-page { background-color: #0a0d14; color: #e8ecf4; }

        .es-house-ink { color: #101522; }
        .dark .es-house-ink { color: #e8ecf4; }
        .es-house-muted { color: #4a5468; }
        .dark .es-house-muted { color: #9aa6bd; }
        .es-house-accent { color: #1d4ed8; }
        .dark .es-house-accent { color: #7ea6ff; }

        /* The paper: squared, with a slightly heavier rule every fifth line,
           exactly like the pad a box office actually uses. */
        .es-house-paper {
            background-color: #fdfefe;
            background-image:
                linear-gradient(to right, rgba(16, 21, 34, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(16, 21, 34, 0.05) 1px, transparent 1px),
                linear-gradient(to right, rgba(16, 21, 34, 0.09) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(16, 21, 34, 0.09) 1px, transparent 1px);
            background-size: 12px 12px, 12px 12px, 60px 60px, 60px 60px;
            border: 1px solid rgba(16, 21, 34, 0.10);
        }
        .dark .es-house-paper {
            background-color: #10141d;
            background-image:
                linear-gradient(to right, rgba(232, 236, 244, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(232, 236, 244, 0.05) 1px, transparent 1px),
                linear-gradient(to right, rgba(232, 236, 244, 0.09) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(232, 236, 244, 0.09) 1px, transparent 1px);
            border-color: rgba(232, 236, 244, 0.12);
        }

        .es-house-card {
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(16, 21, 34, 0.10);
            border-radius: 1rem;
        }
        .dark .es-house-card {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(232, 236, 244, 0.10);
        }

        .es-house-tag {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #1d4ed8;
        }
        .dark .es-house-tag { color: #7ea6ff; }

        /* The section number, written in the margin the way a plan is annotated. */
        .es-house-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(29, 78, 216, 0.35);
            color: #1d4ed8;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
        }
        .dark .es-house-mark { border-color: rgba(126, 166, 255, 0.35); color: #7ea6ff; }

        .es-house-rule { border-top: 1px solid rgba(16, 21, 34, 0.08); }
        .dark .es-house-rule { border-top-color: rgba(232, 236, 244, 0.08); }

        /* Seat glyphs. Shape carries the status; colour only reinforces it. */
        .es-house-seat { fill: none; stroke: #64748b; stroke-width: 1.5; }
        .dark .es-house-seat { stroke: #94a3b8; }
        .es-house-seat-sold { fill: #dc2626; stroke: #b91c1c; }
        .es-house-seat-mine { fill: #1d4ed8; stroke: #1e40af; }
        .dark .es-house-seat-mine { fill: #3b82f6; stroke: #60a5fa; }
        .es-house-seat-held { fill: url(#houseHatch); stroke: #64748b; }
        .es-house-seat-x { stroke: #ffffff; stroke-width: 1.6; stroke-linecap: round; }
        .es-house-hatch-base { fill: #e2e8f0; }
        .dark .es-house-hatch-base { fill: #1e2635; }
        .es-house-hatch-line { stroke: #64748b; }
        .dark .es-house-hatch-line { stroke: #7c8aa3; }
        .es-house-row { fill: #4a5468; font-size: 7px; font-weight: 700; }
        .dark .es-house-row { fill: #9aa6bd; }

        /* The claim band is a FIXED dark object: identical with .dark on and off, so the
           band-diff verifier reads a stable snapshot. No aurora inside it - .dark .es-aurora
           changes opacity, which is a diff. */
        .es-house-band { background: linear-gradient(160deg, #0e1626 0%, #131d33 55%, #0b1220 100%); }
        .es-house-band-tag {
            font-size: 0.75rem; font-weight: 700; letter-spacing: 0.18em;
            text-transform: uppercase; color: #93b4ff;
        }
        .es-house-band-grad {
            background-image: linear-gradient(90deg, #7ea6ff, #22d3ee);
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
    </style>

    <div class="es-house-page">

        <!-- ============================================================ -->
        <!-- 1. Hero: the sheet itself                                    -->
        <!-- ============================================================ -->
        <section id="top" class="relative scroll-mt-24 overflow-hidden py-16 lg:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <p class="es-house-tag mb-4" data-reveal>Allocated seating &middot; Enterprise</p>
                        <h1 class="es-balance es-house-ink text-4xl font-black tracking-tight md:text-6xl" data-reveal style="--reveal-delay: 0.05s;">
                            Draw the room once. <span class="es-house-accent">Sell the seats in it.</span>
                        </h1>
                        <p class="es-house-muted mt-6 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                            Most ticketing sells by the number: ten left, take three. A seated house sells the seats themselves. Build your plan once, attach it to an event, and the buyer picks row C seat 14 off a map of your actual room.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal style="--reveal-delay: 0.15s;">
                            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#1e40af] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] focus-visible:ring-offset-2">
                                Start free trial
                            </a>
                            <a href="{{ marketing_url('/docs/allocated-seating') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 font-semibold es-house-ink transition-colors hover:border-[#1d4ed8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:border-white/15">
                                Read the guide
                            </a>
                        </div>
                        <p class="es-house-muted mt-4 text-sm" data-reveal style="--reveal-delay: 0.2s;">
                            Included on Enterprise, and on every selfhosted install at no cost.
                        </p>
                    </div>

                    <div class="es-house-paper rounded-2xl p-5 sm:p-7" data-reveal="panel" style="--reveal-delay: 0.1s;">
                        <div class="mb-4 flex items-baseline justify-between">
                            <p class="es-house-ink text-sm font-bold">The Aztec &middot; Stalls</p>
                            <p class="es-house-muted text-xs">Sat 14 Nov</p>
                        </div>
                        {{-- 6 rows of 12 with a centre gangway, which is the shape the designer
                             actually produces. Status is drawn as SHAPE first: open ring, filled
                             disc with a cross, hatched. --}}
                        <svg viewBox="0 0 240 118" class="w-full" role="img" aria-label="A seating plan of six rows of twelve seats with a centre gangway. Most seats are available, eleven are sold and two are held back.">
                            <defs>
                                <pattern id="houseHatch" width="4" height="4" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                                    {{-- Classed, not a literal fill: on the dark paper a pale
                                         base reads as a bright disc rather than a held seat. --}}
                                    <rect width="4" height="4" class="es-house-hatch-base" />
                                    <line x1="0" y1="0" x2="0" y2="4" class="es-house-hatch-line" stroke-width="1.4" />
                                </pattern>
                            </defs>
                            @php
                                // Which seats are drawn as taken. Fixed, not random: the page must
                                // render identically on every request for the band-diff verifier.
                                $sold = ['A' => [3, 4, 5], 'B' => [4, 5], 'C' => [7, 8, 9, 10], 'D' => [1, 2]];
                                $held = ['F' => [6, 7]];
                                $mine = ['C' => [3, 4]];
                            @endphp
                            @foreach (range('A', 'F') as $ri => $row)
                                <text x="4" y="{{ 14 + $ri * 18 }}" class="es-house-row" dominant-baseline="middle">{{ $row }}</text>
                                @for ($n = 1; $n <= 12; $n++)
                                    @php
                                        $cx = 16 + ($n - 1) * 17 + ($n > 6 ? 12 : 0);
                                        $cy = 14 + $ri * 18;
                                        $isSold = in_array($n, $sold[$row] ?? [], true);
                                        $isHeld = in_array($n, $held[$row] ?? [], true);
                                        $isMine = in_array($n, $mine[$row] ?? [], true);
                                        $cls = $isSold ? 'es-house-seat es-house-seat-sold'
                                            : ($isMine ? 'es-house-seat es-house-seat-mine'
                                            : ($isHeld ? 'es-house-seat es-house-seat-held' : 'es-house-seat'));
                                    @endphp
                                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="5.5" class="{{ $cls }}" />
                                    @if ($isSold)
                                        <line x1="{{ $cx - 2.6 }}" y1="{{ $cy - 2.6 }}" x2="{{ $cx + 2.6 }}" y2="{{ $cy + 2.6 }}" class="es-house-seat-x" />
                                        <line x1="{{ $cx + 2.6 }}" y1="{{ $cy - 2.6 }}" x2="{{ $cx - 2.6 }}" y2="{{ $cy + 2.6 }}" class="es-house-seat-x" />
                                    @endif
                                @endfor
                            @endforeach
                        </svg>
                        <div class="es-house-rule mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 pt-4 text-xs">
                            <span class="es-house-muted inline-flex items-center gap-1.5"><svg width="12" height="12" viewBox="0 0 12 12" aria-hidden="true"><circle cx="6" cy="6" r="4.5" class="es-house-seat" /></svg>Available</span>
                            <span class="es-house-muted inline-flex items-center gap-1.5"><svg width="12" height="12" viewBox="0 0 12 12" aria-hidden="true"><circle cx="6" cy="6" r="4.5" class="es-house-seat es-house-seat-mine" /></svg>Yours</span>
                            <span class="es-house-muted inline-flex items-center gap-1.5"><svg width="12" height="12" viewBox="0 0 12 12" aria-hidden="true"><circle cx="6" cy="6" r="4.5" class="es-house-seat es-house-seat-sold" /><line x1="3.8" y1="3.8" x2="8.2" y2="8.2" class="es-house-seat-x" /><line x1="8.2" y1="3.8" x2="3.8" y2="8.2" class="es-house-seat-x" /></svg>Sold</span>
                            <span class="es-house-muted inline-flex items-center gap-1.5"><svg width="12" height="12" viewBox="0 0 12 12" aria-hidden="true"><circle cx="6" cy="6" r="4.5" class="es-house-seat es-house-seat-held" /></svg>Held back</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. One plan, every date                                      -->
        <!-- ============================================================ -->
        <section id="reuse" class="es-house-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-house-mark mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-house-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">One plan</p>
                    <h2 class="es-balance es-house-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A plan is a room, <span class="es-house-accent">not a night.</span>
                    </h2>
                    <p class="es-house-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Draw the auditorium once and every performance of the run sells from it. Nothing is copied by hand, and nothing drifts out of step between dates.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">Reused, not duplicated</h3>
                        <p class="es-house-muted text-sm leading-relaxed">Attach one plan to a recurring event and every date inherits it. Fix a mislabelled row in the plan and every future date is fixed with it.</p>
                    </div>
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">One date, on its own</h3>
                        <p class="es-house-muted text-sm leading-relaxed">The piano takes out the front row on Thursday only. Modify that date alone; the rest of the run never notices, and you can put it back at any point.</p>
                    </div>
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">Sold seats are safe</h3>
                        <p class="es-house-muted text-sm leading-relaxed">Keep editing after you go on sale. A seat somebody has bought will not be deleted, and neither will one a customer is choosing at that moment.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The designer                                              -->
        <!-- ============================================================ -->
        <section id="designer" class="es-house-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-house-mark mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-house-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The designer</p>
                    <h2 class="es-balance es-house-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Rows, tables, tiers, <span class="es-house-accent">and the gangway.</span>
                    </h2>
                    <p class="es-house-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Start from a layout and adjust it, or start from a blank sheet. Drag a section to move it; click a seat, or shift-click a handful, to say what they are.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="80">
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">Levels</h3>
                        <p class="es-house-muted text-sm leading-relaxed">Stalls, circle, balcony. A ground-floor room needs one; a Victorian theatre needs three, and each gets its own drawing.</p>
                    </div>
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">Rows and seats</h3>
                        <p class="es-house-muted text-sm leading-relaxed">Add rows in blocks: how many rows, how many seats, how they are labelled. Curve them if the room curves. Number the seats or leave them bare.</p>
                    </div>
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">Tables</h3>
                        <p class="es-house-muted text-sm leading-relaxed">Round or rectangular, with a seat count each. Sell them as single seats, as a whole table only, or let the buyer decide which.</p>
                    </div>
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">Standing too</h3>
                        <p class="es-house-muted text-sm leading-relaxed">A standing section carries a capacity instead of seats and sells by the number. Rows at the front and a floor at the back is one plan, priced as two bands.</p>
                    </div>
                </div>

                <div class="mt-8 grid gap-4 md:grid-cols-2" data-reveal-group="90">
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">A gangway is not decoration</h3>
                        <p class="es-house-muted text-sm leading-relaxed">Mark an aisle after a seat and the two seats either side of it stop being neighbours. The picker will not offer them as a pair, and best-available will not seat a couple across the aisle from each other.</p>
                    </div>
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">Wheelchair spaces are held properly</h3>
                        <p class="es-house-muted text-sm leading-relaxed">A wheelchair space only sells from a section marked accessibility only, so it is never handed out as the next free seat. The companion seat beside it is held too, and unlocks with the space it belongs to.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. What the buyer does                                       -->
        <!-- ============================================================ -->
        <section id="buyer" class="es-house-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-house-mark mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-house-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The buyer</p>
                    <h2 class="es-balance es-house-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Pick your seats, <span class="es-house-accent">or let us pick them.</span>
                    </h2>
                    <p class="es-house-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Say how many you want and take the best available together, or open the map and choose. Either way the seats are held while you pay.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">Held while they pay</h3>
                        <p class="es-house-muted text-sm leading-relaxed">Chosen seats are held for about twelve minutes, with the time left on screen and a button to ask for more. Nobody pays for a seat and then finds it gone.</p>
                    </div>
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">The map is live</h3>
                        <p class="es-house-muted text-sm leading-relaxed">Seats other people take grey out while a buyer is looking. The box office sees the same map at the same time, so the phone and the website never sell the same seat twice.</p>
                    </div>
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">No stranded singles</h3>
                        <p class="es-house-muted text-sm leading-relaxed">A selection that would leave one seat alone between two bookings is refused, because a lone seat mid-row rarely sells. The rule lifts itself once the house is nearly full.</p>
                    </div>
                </div>

                <div class="es-house-card mt-8 p-6" data-reveal="panel">
                    <h3 class="es-house-ink mb-2 text-base font-bold">The seat number follows the booking everywhere</h3>
                    <p class="es-house-muted text-sm leading-relaxed">On the ticket, in the confirmation email, on the door scanner, in the check-in feed as each guest walks in, and in the sales export. Front of house never has to look a seat up.</p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. The box office                                            -->
        <!-- ============================================================ -->
        <section id="boxoffice" class="es-house-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-house-mark mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-house-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The box office</p>
                    <h2 class="es-balance es-house-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The same map, <span class="es-house-accent">with the names on.</span>
                    </h2>
                    <p class="es-house-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Click a seat and it tells you who has it. Type "C14" or a customer's name and the map jumps to them, because staff on a phone type faster than they click.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2" data-reveal-group="80">
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">Hold seats back</h3>
                        <p class="es-house-muted text-sm leading-relaxed">House seats, production, accessibility or box office, each with an internal note only your team sees. A staff hold never lapses on its own.</p>
                    </div>
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">Take it over the phone</h3>
                        <p class="es-house-muted text-sm leading-relaxed">Select the seats the caller wants and sell them, paid or awaiting payment. Leave the amount blank for the list price, or enter zero to comp them.</p>
                    </div>
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">Move one seat</h3>
                        <p class="es-house-muted text-sm leading-relaxed">Somebody wants the aisle. Move that booking to a different seat without touching the rest of their order or reissuing anything.</p>
                    </div>
                    <div class="es-house-card p-6" data-reveal="panel">
                        <h3 class="es-house-ink mb-2 text-base font-bold">Release one seat</h3>
                        <p class="es-house-muted text-sm leading-relaxed">Three of four are coming. Put the fourth back on sale by clicking it; the rest of the booking stands. Refund in your payment provider as usual.</p>
                    </div>
                </div>

                <div class="es-house-card mt-8 p-6" data-reveal="panel">
                    <h3 class="es-house-ink mb-2 text-base font-bold">A sheet for the night</h3>
                    <p class="es-house-muted text-sm leading-relaxed">The seating plan report prints every seat, its status and who holds it, section by section. Status is drawn as a shape and not only a colour, so it survives the black and white printer front of house actually has. There is a CSV if you would rather have a spreadsheet.</p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Plan band                                                 -->
        <!-- ============================================================ -->
        <section id="plan" class="es-house-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                <p class="es-house-tag mb-4" data-reveal>Which plan</p>
                <h2 class="es-balance es-house-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Allocated seating is <span class="es-house-accent">Enterprise.</span>
                </h2>
                <p class="es-house-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Everything else about ticketing - the types, the check-in dashboard, promo codes, waitlists - sits below it. Seat maps are the one part that needs the top plan, and they come with every selfhosted install at no cost.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3" data-reveal style="--reveal-delay: 0.15s;">
                    <a href="{{ route('marketing.pricing') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#1e40af] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] focus-visible:ring-offset-2">
                        See pricing
                    </a>
                    <a href="{{ route('marketing.ticketing') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 font-semibold es-house-ink transition-colors hover:border-[#1d4ed8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:border-white/15">
                        All of ticketing
                    </a>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. FAQ                                                       -->
        <!-- ============================================================ -->
        @php
            $seatingFaqs = [
                ['q' => 'Can one plan cover every date of a run?', 'a' => 'Yes, and that is the point of it. A plan is a drawing of the room, so a recurring event uses the same one for every performance. Each date keeps its own bookings, and a single date can be changed on its own without touching the others.'],
                ['q' => 'Can I mix seated and standing?', 'a' => 'Yes. A seated section carries individual seats the buyer chooses; a standing section carries a capacity and sells by the number, like an ordinary ticket type. Rows at the front and a standing floor at the back is one plan with two price bands.'],
                ['q' => 'How do wheelchair spaces work?', 'a' => 'Mark a section accessibility only and the wheelchair spaces in it are never handed out as the next available seat. The companion seat beside a space is held back too, and becomes bookable together with the space, so a wheelchair user and their companion are not seated three rows apart.'],
                ['q' => 'Can my box office still sell over the phone?', 'a' => 'Yes. The box office console is the same map with the names on. Select the seats the caller wants and book them, marked paid or awaiting payment, at the list price or comped. Staff can also hold seats back with an internal note, move a booking to another seat, and release a single seat from an order.'],
                ['q' => 'What happens if I edit a plan that is already selling?', 'a' => 'You can keep editing. A seat somebody has bought will not be removed, and neither will one a customer is choosing at that moment. Everything else moves freely around them.'],
                ['q' => 'Do buyers have to pick their own seats?', 'a' => 'No. They choose how many they want and can take the best available together, which keeps a party seated as a block. Choosing from the map is the other option, not the only one.'],
                ['q' => 'Does it work with tables?', 'a' => 'Yes. Give a section round or rectangular tables and say how many sit at each. A table can sell as single seats, as a whole table only, or either way, which is the difference between a cabaret night and a fundraising dinner.'],
                // The Seating tab is rendered on venue schedules only, which is the single
                // most surprising thing about this feature if you run a talent schedule.
                ['q' => 'Which kind of schedule can build a plan?', 'a' => 'A venue schedule. Plans describe a room, so the Seating tab appears on the schedule that has the room and not on a talent or curator one. That does not shut anyone else out of a seated night: a talent or curator schedule listing a seated event still sells from the map and still gets the box office console for it. If you promote in a room you do not own, the venue draws the plan once and every show in it uses the same drawing.'],
                ['q' => 'What happens to a seated event if my plan lapses?', 'a' => 'The show carries on. A plan already attached to an event stays attached and keeps selling seats, and the box office keeps working on it, because pulling a seating chart out from under a run that is on sale would be indefensible. What Enterprise gates is attaching a new plan to another event.'],
                ['q' => 'Which plan do I need?', 'a' => 'Allocated seating is on the Enterprise plan, and on every selfhosted install at no cost. The rest of ticketing - types, check-in, promo codes, waitlists, the sales export - is available below it.'],
            ];
        @endphp
        <section id="faq" class="es-house-rule scroll-mt-24 py-20 lg:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="es-house-ink mb-10 text-center text-3xl font-black tracking-tight md:text-4xl" data-reveal>Questions</h2>
                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($seatingFaqs as $faq)
                        <details class="es-house-card group p-5" data-reveal="panel">
                            <summary class="es-house-ink flex cursor-pointer items-center justify-between gap-4 text-base font-semibold">
                                {{ $faq['q'] }}
                                <svg class="h-5 w-5 shrink-0 transition-transform group-open:rotate-45" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                </svg>
                            </summary>
                            <p class="es-house-muted mt-3 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$seatingFaqs" />

        <!-- ============================================================ -->
        <!-- 8. Claim                                                     -->
        <!-- ============================================================ -->
        <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="es-house-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-20" data-reveal="panel">
                    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div class="grid-overlay absolute inset-0 opacity-25"></div>
                    </div>

                    <div class="relative z-10">
                        <p class="es-house-band-tag mb-6">Free to start</p>
                        <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                            Put your room <span class="es-house-band-grad">on sale</span>.
                        </h2>
                        <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                            Draw the plan once, attach it to the run, and let people choose where they sit.
                        </p>

                        <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-venue" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg bg-white px-8 py-4 text-lg font-semibold text-[#101522] transition-colors hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#101522]">
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
         completely blank below the nav. The layout says so in as many words. --}}
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
