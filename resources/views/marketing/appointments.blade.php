<x-marketing-layout>
    <x-slot name="title">Appointment Booking - Event Schedule</x-slot>
    <x-slot name="description">Appointment booking built into your schedule. Write your hours down once, share one link, and guests pick an open time in their own timezone.</x-slot>
    <x-slot name="breadcrumbTitle">Appointments</x-slot>

    @php
        // ── One Thursday in the book ────────────────────────────────────────
        // Every time on this page comes out of these arrays, so the ruled
        // column, the list a guest is shown and the register cannot disagree.
        // Modelled on a real appointment type: 30 minute duration, start times
        // every 30 minutes, hours 09:00 to 13:00 and 14:00 to 16:00 in the
        // schedule's timezone, and a guest reading it two hours ahead.
        $scheduleTz = 'America/Denver';
        $guestTz = 'America/New_York';

        // state: open | pen (confirmed booking) | pencil (waiting on approval)
        //        busy (something already on the schedule) | cont (a line the
        //        entry above it is still running through)
        $day = [
            ['at' => '09:00', 'state' => 'open',   'span' => 1, 'label' => 'Open'],
            ['at' => '09:30', 'state' => 'pen',    'span' => 1, 'label' => 'Intro call, Jamie R.'],
            ['at' => '10:00', 'state' => 'open',   'span' => 1, 'label' => 'Open'],
            ['at' => '10:30', 'state' => 'busy',   'span' => 2, 'label' => 'Studio session, synced in from Google Calendar'],
            ['at' => '11:00', 'state' => 'cont',   'span' => 0, 'label' => ''],
            ['at' => '11:30', 'state' => 'open',   'span' => 1, 'label' => 'Open'],
            ['at' => '12:00', 'state' => 'pencil', 'span' => 2, 'label' => 'Consult, Dana W., waiting on you'],
            ['at' => '12:30', 'state' => 'cont',   'span' => 0, 'label' => ''],
        ];

        // The open lines above, plus the afternoon range the sheet names but does
        // not draw. This is the ONLY source for the guest's list below.
        $openLocal = array_values(array_map(
            fn ($line) => $line['at'],
            array_filter($day, fn ($line) => $line['state'] === 'open')
        ));
        $openLocal = array_merge($openLocal, ['14:00', '14:30', '15:00', '15:30']);

        // Grouped exactly the way the booking page groups them: the guest's own hour,
        // morning under 12, afternoon under 17, evening after that.
        $guestTimes = [];
        foreach ($openLocal as $local) {
            [$h, $m] = explode(':', $local);
            $gh = (int) $h + 2; // the guest is two hours ahead of the schedule
            $guestTimes[] = [
                'guest' => sprintf('%02d:%02d', $gh, $m),
                'local' => $local,
                'part' => $gh < 12 ? 'Morning' : ($gh < 17 ? 'Afternoon' : 'Evening'),
            ];
        }
        $guestParts = ['Morning', 'Afternoon', 'Evening'];
        $earliestGuest = $guestTimes[0]['guest'] ?? null;

        // The card the guest keeps leads with THEIR clock and a start-to-end range,
        // exactly the way every guest-facing appointment mail renders a booking, with
        // the schedule's own zone underneath. Derived from the same array so the card
        // cannot drift from the line it was copied off.
        $stubLocal = '11:30';
        $stubGuest = $stubLocal;
        foreach ($guestTimes as $t) {
            if ($t['local'] === $stubLocal) {
                $stubGuest = $t['guest'];
            }
        }
        [$sh, $sm] = explode(':', $stubGuest);
        $stubEndMinutes = ((int) $sh) * 60 + (int) $sm + 30;
        $stubGuestEnd = sprintf('%02d:%02d', intdiv($stubEndMinutes, 60) % 24, $stubEndMinutes % 60);

        // The hours as they are written down: up to four ranges a day, and days
        // you simply leave blank.
        $hours = [
            ['day' => 'Monday',    'at' => '09:00 to 13:00, 14:00 to 16:00'],
            ['day' => 'Tuesday',   'at' => '09:00 to 13:00'],
            ['day' => 'Wednesday', 'at' => null],
            ['day' => 'Thursday',  'at' => '09:00 to 13:00, 14:00 to 16:00'],
            ['day' => 'Friday',    'at' => '09:00 to 11:00'],
            ['day' => 'Saturday',  'at' => null],
            ['day' => 'Sunday',    'at' => null],
        ];

        $settings = [
            ['k' => 'Duration', 'v' => '30 minutes'],
            ['k' => 'Start times every', 'v' => '30 minutes'],
            ['k' => 'Buffer before and after', 'v' => 'None on this type'],
            ['k' => 'Minimum notice', 'v' => '12 hours'],
            ['k' => 'Booking window', 'v' => '60 days ahead'],
            ['k' => 'Override', 'v' => 'Friday 28 August, closed'],
        ];

        // The pitch of the ruled lines IS the start-time interval. Every column rules the
        // SAME two hours, 09:00 to 11:00, for the same 30 minute appointment, so the count
        // of lines actually on offer is the whole argument. Each row is [time, state, label]
        // and the counts are what the real slot loop would hand back:
        //   col 1: starts every 30 min, nothing booked                        -> 4
        //   col 2: starts every 15 min; 10:45 is ruled but never offered
        //          because 30 minutes from 10:45 runs past 11:00              -> 7
        //   col 3: starts every 30 min, 10 minute buffer after, 09:30 taken.
        //          09:00 is out because its own trailing buffer reaches 09:40,
        //          and 10:00 is out because it starts inside the buffer that
        //          runs to 10:10                                              -> 1
        $pitchDemo = [
            [
                'k' => 'Start times every 30 minutes',
                'count' => '4 on offer',
                'note' => 'A 30 minute call offered every 30 minutes. Four lines on two hours of page, and every one of them is free.',
                'pitch' => '2.7rem',
                'rows' => [
                    ['09:00', 'open', 'Open'], ['09:30', 'open', 'Open'],
                    ['10:00', 'open', 'Open'], ['10:30', 'open', 'Open'],
                ],
            ],
            [
                'k' => 'Start times every 15 minutes',
                'count' => '7 on offer',
                'note' => 'The same 30 minute call, offered twice as often: seven starts, not four. The last quarter hour is ruled and left blank, because half an hour from 10:45 would run past eleven.',
                'pitch' => '1.35rem',
                'rows' => [
                    ['09:00', 'tick', ''], ['09:15', 'tick', ''], ['09:30', 'tick', ''], ['09:45', 'tick', ''],
                    ['10:00', 'tick', ''], ['10:15', 'tick', ''], ['10:30', 'tick', ''], ['10:45', 'none', ''],
                ],
            ],
            [
                'k' => 'One booked, 10 minute buffer',
                'count' => '1 on offer',
                'note' => 'A single booking and the ten minutes of padding behind it cost three of the four lines: 09:00 because its own padding would reach into the entry, 10:00 because it starts inside the padding.',
                'pitch' => '2.7rem',
                'rows' => [
                    ['09:00', 'pad', 'Blocked'], ['09:30', 'pen', 'Booked'],
                    ['10:00', 'pad', 'Blocked'], ['10:30', 'open', 'Open'],
                ],
            ],
        ];

        $steps = [
            ['Write the hours in', 'Open the Appointments tab, add a type, and set its duration, its start-time interval, and the hours you keep on each day. Buffers, minimum notice and how far ahead guests may book live on the same form.'],
            ['Say where, and what it costs', 'In person with an address, online with a meeting link, or by phone with a number. Leave the price at zero, or set a price and take it by Stripe, a payment link, or cash.'],
            ['Share one link', 'Your booking page sits at /book on your schedule, and every type has its own address under it. A Book a Time button appears on your public page as well.'],
            ['They pick a line', 'A guest books an open time in their own timezone. You are emailed, the time is held, and the booking joins your calendar without joining your public page.'],
        ];

        // The Bookings tab. Four states, exactly the four the list can label: confirmed,
        // request sent, awaiting payment, cancelled. "Moved" is not a fifth state, it is a
        // marker a confirmed booking carries once it has actually been moved, so it rides
        // on the confirmed row rather than pretending to be its own.
        $register = [
            [
                'guest' => 'Jamie R.',
                'type' => 'Intro call, 30 min, free',
                'when' => 'Thu 6 Aug, 09:30',
                'state' => 'Confirmed',
                'tone' => 'ok',
                'slot' => 'Held, and pushed to your connected calendar',
            ],
            [
                'guest' => 'Dana W.',
                'type' => 'Consult, 60 min, $120',
                'when' => 'Thu 6 Aug, 12:00',
                'state' => 'Waiting on you',
                'tone' => 'hold',
                'slot' => 'Held while you decide, so you cannot be booked over it',
            ],
            [
                'guest' => 'Priya S.',
                'type' => 'Consult, 60 min, $120',
                'when' => 'Fri 7 Aug, 10:00',
                'state' => 'Awaiting payment',
                'tone' => 'wait',
                'slot' => 'Held while they pay, released if the hold lapses',
            ],
            [
                'guest' => 'Marc T.',
                'type' => 'Intro call, 30 min, free',
                'when' => 'Mon 10 Aug, 14:00',
                'state' => 'Confirmed, moved',
                'tone' => 'moved',
                'slot' => 'Same booking, new time. The old one went back on sale',
            ],
            [
                'guest' => 'Ana L.',
                'type' => 'Consult, 60 min, $120',
                'when' => 'Tue 11 Aug, 11:00',
                'state' => 'Cancelled',
                'tone' => 'off',
                'slot' => 'Released. Refunding is a step you take yourself',
            ],
        ];

        $money = [
            [
                'k' => 'Free',
                'v' => 'Leave the price at zero. The line is written in the moment a guest books, or lands as a request if the type asks for your approval.',
            ],
            [
                'k' => 'Card or payment link',
                'v' => 'Stripe Checkout, or your own payment link. The line is held while they pay and lets go if they never do: an hour for Stripe, a day for a payment link.',
            ],
            [
                'k' => 'Cash',
                'v' => 'The line is held straight away and you mark the sale paid when you have actually been paid. Paid bookings sit with the rest of your sales.',
            ],
        ];

        $facts = [
            ['Bookings stay out of the shop window', 'Every booking is created as a private event, so it never shows on your public schedule, your iCal feed, your RSS feed or your event graphics. It still holds the time.'],
            ['Email has to be working', 'On the hosted platform appointment mail goes out through your own email settings, so confirmations come from your address. Until those are set up nothing is sent, and the Appointments tab says so.'],
            ['Not the same as Availability', 'Availability is a separate tab, on Enterprise talent schedules, where members cross out whole dates they cannot be booked for events. Appointments hand out one specific slot at a time, free with one type on any schedule type, and uncapped on Pro.'],
            ['Turn a type off, do not delete it', 'Untick Active and the type leaves your booking page. Bookings already on it keep their time, their emails and their reminders, though nothing can be moved onto a type that is switched off.'],
            ['One guest, one time', 'The same email address cannot hold two bookings at the same moment on your schedule.'],
            ['Reminders chase confirmed bookings only', 'A request still waiting on you, or a card booking still waiting on payment, does not get one.'],
        ];

        $faqs = [
            [
                'q' => 'How does appointment booking work?',
                'a' => 'Create an appointment type with a duration and the weekly hours you take bookings for. Guests open your booking page, pick an open time, and book it. You can confirm bookings automatically or approve each request yourself.',
            ],
            [
                'q' => 'Can I be booked twice at the same time?',
                'a' => 'No. Open times respect your weekly hours, your buffers and your minimum notice, and any time already taken on your schedule is removed. That includes events synced in from Google Calendar, Outlook or CalDAV, bookings for your other appointment types, and requests still waiting on your approval.',
            ],
            [
                'q' => 'Can I charge for appointments?',
                'a' => 'Yes. A type can be free or priced, and paid types take payment by Stripe, a payment link, or cash. Free bookings are confirmed at once, a paid type stays hidden from guests until a working payment method is connected, and Event Schedule takes no cut of what you charge.',
            ],
            [
                'q' => 'Can I approve bookings before they are confirmed?',
                'a' => 'Turn on approval for a type and new bookings arrive as requests. The booking page tells the guest that nothing is confirmed until you say so, the slot stays blocked while the request is open, and the guest is emailed either way when you accept or decline.',
            ],
            [
                'q' => 'What do guests get after booking?',
                'a' => 'A confirmation email with a calendar invite, and a private link that lets them move the booking to another time or cancel it while the appointment is still ahead of them. A reminder email follows about a day before it starts.',
            ],
            [
                'q' => 'Can a booking be moved instead of cancelled?',
                'a' => 'Yes, from either side. The guest uses Reschedule on their private link; you use Reschedule on the booking row, and your picker ignores the minimum notice and the booking window so you can move something to later today. The booking keeps its payment, its private link and its place in your sales, and the invite in the guest\'s calendar moves rather than doubling up.',
            ],
            [
                'q' => 'Do the emails send themselves?',
                'a' => 'On the hosted platform appointment mail goes out through your schedule\'s own email settings, so it arrives from your address rather than ours. Until those are configured guests get no confirmations or reminders, and the Appointments tab warns you about it. Selfhosted deployments use whatever mail transport you have set up.',
            ],
            [
                'q' => 'Which plan includes appointment booking?',
                'a' => 'Booking is on the free plan, with one appointment type. Everything about that type is fully featured: weekly hours, per-date overrides, buffers, approvals and payment. Pro is what lets you run several types side by side, and a selfhosted deployment has no cap at all. If a hosted Pro plan lapses you keep every type you created and the oldest bookable one stays bookable, so nothing already booked is ever lost and every guest\'s private link still opens.',
            ],
        ];

        $dotSections = [
            ['top', 'The book'],
            ['spread', 'Two readings'],
            ['lines', 'The lines'],
            ['how-it-works', 'How it works'],
            ['register', 'The register'],
            ['card', 'Their copy'],
            ['money', 'Charging'],
            ['know', 'Marginalia'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Appointments",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Appointment booking built into your schedule. Set weekly hours, start-time intervals and buffers, take free or paid bookings, and never offer a time you are already busy.",
        "featureList": [
            "Bookable appointment types with a duration and weekly hours",
            "Start-time interval, buffers, minimum notice and a booking window",
            "Per-date overrides for holidays and one-off hours",
            "In person, online or phone appointments",
            "Free or paid bookings by Stripe, a payment link, or cash",
            "Open times exclude anything already on your schedule, including synced calendar events",
            "Approval or instant confirmation",
            "Confirmation email with a calendar invite, a reminder, and timezone-aware times",
            "Guests can move or cancel a booking from a private link"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}"
        },
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    <x-seo.faq-schema :items="$faqs" />
    </x-slot>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           Appointments "The Appointment Book" styles.

           CONCEPT: a hardbound appointment book on a desk. The hours you
           keep are PRINTED on the page; the pitch of the ruled lines is
           the start-time interval; an entry already written in is why a
           line is not on offer; the buffer is the blank rule left either
           side of it. Nobody hands the guest the book - they get the one
           page with the empty lines on it, and a stiff little card with
           their own line copied onto it. That is the product argument in
           one object: the booking page is a READING of the same book your
           own events are already written in, which is why a synced
           Google/Outlook/CalDAV event silently removes a line.

           FIVE DEVICES:
             1. The ruled day column (.es-book-day). A CSS grid whose row
                height AND rule pitch both come from --bk-pitch, so the
                interval literally sets the spacing of the lines. Entries
                are placed by grid-row from PHP and can span rows.
             2. The two-page spread (.es-book-spread): verso = what you
                write in, recto = what a guest is offered, one gutter
                between them. Both pages are rendered from the same array.
             3. The pitch demo: three columns ruling the SAME two hours
                (09:00 to 11:00) for the SAME 30 minute appointment, so
                only the count on offer changes - 4 at a 30 minute step,
                7 at a 15 minute step (10:45 is ruled but never offered,
                because 30 minutes from it runs past the window: see
                computeDays()'s `$s + duration <= $wEnd`), and 1 once a
                single booking plus a 10 minute trailing buffer knocks out
                both its neighbours (overlapsAny() against a candidate
                padded by ITS OWN buffers, so 09:00 goes too - do not
                "fix" that back to Open). Line COUNT carries the argument,
                so deliberately NOT a proportional-height time axis -
                those belong to /for-music-venues, /for-watch-parties and
                /for-webinars. Equal-pitch rules are also the honest shape
                for a slot grid, where steps are fixed.
             4. The appointment card (.es-book-stub): the tear-off stub the
                guest keeps, perforated edge and all.
             5. The finale closes the object: bookcloth weave, a spine
                (.es-book-close-spine), the fore-edge of the page block
                (.es-book-close-fore) and the same stacked .es-book-edge
                every sheet above it sits on, widened for the cover.

           PAPER IS PINNED. .es-book-paper is a real physical object: cream
           stock, blue rules, graphite pencil. It renders IDENTICALLY with
           .dark on and off (verified with --bands=.es-book-paper, 0 diffs).
           Nothing inside a paper element may carry a dark: utility. The
           mode-aware surfaces (.es-book-card, the register, the finale) are
           the APP side of the story, and those are designed twice.

           COLOUR: the page keeps its inherited blue, re-cut as ledger ink
           rather than the shared brand-blue-to-cyan chrome gradient. Cyan
           and sky belong to /for-djs, /for-venues and /for-dance-groups, so
           the bright end of that ramp is gone: this is #14418f / #1a45b8
           ink on cream, and #8ab6ff on near-black.

           MEASURED (ratio : ground):
             paper #faf7ef  - ink #171c26 15.94, muted #4c525e 7.33,
                              accent #14418f 8.99, graphite #3f4753 8.77,
                              white on the #14418f fill 9.62
             light ground #f0ece2 - ink 14.47, muted #4c525e 6.65,
                              accent #14418f 8.16
             tab #e3ded1 / button #e8e2d4 - accent 7.16 / 7.45
             on-paper fills - #14418f on #eef1f7 8.50, #3f4753 on
                              #efece3 7.95, #464c57 on #eeeade 7.18
             dark ground #0b0f18  - ink #eef1f6 16.93, muted #a3adbd 8.46,
                              accent #8ab6ff 9.32
             dark card #141a26    - ink 15.39, muted 7.69, accent 8.47
           NEVER text-gray-500 on these grounds; use .es-book-muted.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------- */
        .es-book-page { background-color: #f0ece2; color: #171c26; }
        .dark .es-book-page { background-color: #0b0f18; color: #eef1f6; }
        .es-book-ink { color: #171c26; }
        .dark .es-book-ink { color: #eef1f6; }
        .es-book-muted { color: #4c525e; }
        .dark .es-book-muted { color: #a3adbd; }
        .es-book-accent { color: #14418f; }
        .dark .es-book-accent { color: #8ab6ff; }
        /* Always-bright accent, for the always-dark closing band. */
        .es-book-lit { color: #a9c8ff; }

        .es-book-grad {
            background-image: linear-gradient(104deg, #14418f, #1a45b8);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-book-grad,
        .es-book-close .es-book-grad {
            background-image: linear-gradient(104deg, #a9c8ff, #8ab6ff);
        }

        .es-book-seam { border-top: 1px solid rgba(23, 28, 38, 0.11); }
        .dark .es-book-seam { border-top-color: rgba(238, 241, 246, 0.11); }

        /* Faint ruled wash: the whole page is a page. */
        .es-book-rulewash {
            background-image: repeating-linear-gradient(
                to bottom,
                transparent 0, transparent 2.35rem,
                rgba(23, 28, 38, 0.055) 2.35rem, rgba(23, 28, 38, 0.055) calc(2.35rem + 1px));
        }
        .dark .es-book-rulewash {
            background-image: repeating-linear-gradient(
                to bottom,
                transparent 0, transparent 2.35rem,
                rgba(238, 241, 246, 0.05) 2.35rem, rgba(238, 241, 246, 0.05) calc(2.35rem + 1px));
        }

        /* --- Section marks: fore-edge tabs ---------------------------- */
        .es-book-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.28rem 0.95rem 0.28rem 0.7rem;
            border-radius: 0.25rem 0.95rem 0.95rem 0.25rem;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            background-color: #e3ded1;
            border: 1px solid rgba(20, 65, 143, 0.26);
            color: #14418f;
        }
        .dark .es-book-tab {
            background-color: #18202e;
            border-color: rgba(138, 182, 255, 0.28);
            color: #8ab6ff;
        }
        .es-book-tab-no {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            letter-spacing: 0;
            opacity: 0.75;
        }
        .es-book-eyebrow {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4c525e;
        }
        .dark .es-book-eyebrow { color: #a3adbd; }
        .es-book-no {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 1.6rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            line-height: 1;
            color: #14418f;
        }
        .dark .es-book-no { color: #8ab6ff; }

        /* --- Mode-aware surfaces: the app side ------------------------ */
        .es-book-card {
            background-color: #fdfcf8;
            border: 1px solid rgba(23, 28, 38, 0.12);
            border-radius: 0.75rem;
        }
        .dark .es-book-card {
            background-color: #141a26;
            border-color: rgba(238, 241, 246, 0.13);
        }
        .es-book-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease; }
        .es-book-hover:hover {
            border-color: rgba(20, 65, 143, 0.42);
            box-shadow: 0 12px 30px -20px rgba(23, 28, 38, 0.55);
        }
        .dark .es-book-hover:hover {
            border-color: rgba(138, 182, 255, 0.4);
            box-shadow: 0 12px 30px -20px rgba(0, 0, 0, 0.85);
        }

        /* --- PAPER. A fixed physical object: identical in both modes. -- */
        .es-book-paper {
            position: relative;
            background-color: #faf7ef;
            color: #171c26;
            border: 1px solid rgba(23, 28, 38, 0.18);
            border-radius: 0.35rem;
            box-shadow: 0 20px 45px -30px rgba(23, 28, 38, 0.7);
        }
        .es-book-spine {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 0.85rem;
            border-radius: 0.35rem 0 0 0.35rem;
            background-image: linear-gradient(to right,
                rgba(23, 28, 38, 0.16) 0%,
                rgba(23, 28, 38, 0.05) 45%,
                rgba(23, 28, 38, 0) 100%);
        }
        /* Stacked page edges under a sheet. */
        .es-book-edge {
            height: 7px;
            margin: 0 0.55rem;
            border-radius: 0 0 0.4rem 0.4rem;
            background-color: #ece6d8;
            background-image: linear-gradient(to bottom,
                rgba(23, 28, 38, 0.16) 0 1px, #f2eee2 1px 3px,
                rgba(23, 28, 38, 0.13) 3px 4px, #eee9dc 4px 6px,
                rgba(23, 28, 38, 0.1) 6px 7px);
            box-shadow: 0 6px 14px -10px rgba(23, 28, 38, 0.55);
        }
        .es-book-sheet { padding: 1.15rem 1.15rem 1.25rem 1.6rem; }
        .es-book-sheet-head {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding-bottom: 0.6rem;
            margin-bottom: 0.75rem;
            border-bottom: 2px solid rgba(20, 65, 143, 0.35);
        }
        .es-book-sheet-day { font-size: 1.05rem; font-weight: 800; letter-spacing: -0.01em; color: #171c26; }
        .es-book-sheet-date {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8rem;
            font-weight: 600;
            color: #14418f;
        }
        .es-book-sheet-tz {
            margin-left: auto;
            font-size: 0.66rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #4c525e;
        }
        .es-book-sheet-foot {
            margin-top: 0.8rem;
            font-size: 0.72rem;
            line-height: 1.45;
            color: #4c525e;
        }

        /* --- The ruled day column ------------------------------------- */
        .es-book-day {
            --bk-pitch: 2.45rem;
            --bk-gutter: 3.4rem;
            position: relative;
            display: grid;
            grid-template-columns: var(--bk-gutter) 1fr;
            grid-auto-rows: var(--bk-pitch);
        }
        .es-book-day::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-image:
                repeating-linear-gradient(to bottom,
                    transparent 0, transparent calc(var(--bk-pitch) - 1px),
                    rgba(23, 28, 38, 0.15) calc(var(--bk-pitch) - 1px), rgba(23, 28, 38, 0.15) var(--bk-pitch)),
                linear-gradient(to right,
                    transparent 0, transparent calc(var(--bk-gutter) - 0.75rem),
                    rgba(20, 65, 143, 0.3) calc(var(--bk-gutter) - 0.75rem), rgba(20, 65, 143, 0.3) calc(var(--bk-gutter) - 0.75rem + 1px),
                    transparent calc(var(--bk-gutter) - 0.75rem + 1px));
        }
        .es-book-time {
            align-self: center;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            font-variant-numeric: tabular-nums;
            color: #4c525e;
        }
        .es-book-open,
        .es-book-pen,
        .es-book-pencil,
        .es-book-busy,
        .es-book-pad {
            align-self: center;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            min-height: 1.5rem;
            padding: 0.22rem 0.55rem;
            border-radius: 0.3rem;
            font-size: 0.72rem;
            font-weight: 600;
            line-height: 1.25;
        }
        .es-book-open {
            align-self: center;
            justify-self: start;
            justify-content: flex-start;
            min-width: 5.5rem;
            border: 1px dashed rgba(20, 65, 143, 0.55);
            background-color: #eef1f7;
            color: #14418f;
        }
        .es-book-pen {
            background-color: #14418f;
            color: #ffffff;
            box-shadow: 0 3px 10px -6px rgba(20, 65, 143, 0.9);
        }
        .es-book-pencil {
            border: 1px dashed rgba(63, 71, 83, 0.6);
            background-color: #efece3;
            color: #3f4753;
            font-style: italic;
            font-weight: 500;
        }
        .es-book-busy,
        .es-book-pad {
            align-items: center;
            background-color: #eeeade;
            background-image: repeating-linear-gradient(135deg,
                rgba(23, 28, 38, 0.1) 0 5px, rgba(23, 28, 38, 0) 5px 10px);
            border: 1px solid rgba(23, 28, 38, 0.18);
            color: #464c57;
            font-weight: 500;
        }
        .es-book-pad { font-size: 0.68rem; }
        /* The thin 15-minute mark: a rule, not a labelled pill. */
        .es-book-tick {
            align-self: center;
            height: 0.4rem;
            width: 62%;
            border-radius: 999px;
            background-color: #14418f;
            opacity: 0.55;
        }

        /* A line the grid rules but never offers, because the appointment would not
           fit inside the window. Same geometry as the tick, hollow instead of inked. */
        .es-book-blank {
            align-self: center;
            height: 0.4rem;
            width: 62%;
            border-radius: 999px;
            border: 1px dashed rgba(23, 28, 38, 0.3);
        }

        .es-book-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem 1rem;
            font-size: 0.68rem;
            font-weight: 600;
            color: #4c525e;
        }
        /* The legend sits on the page ground, not on paper, so it is one of the
           mode-aware pieces. The swatches stay paper-coloured: they are samples. */
        .dark .es-book-legend { color: #a3adbd; }
        .es-book-sw {
            display: inline-block;
            width: 0.7rem;
            height: 0.7rem;
            margin-right: 0.35rem;
            border-radius: 0.15rem;
            vertical-align: -1px;
        }
        .es-book-sw-open { border: 1px dashed rgba(20, 65, 143, 0.65); background-color: #eef1f7; }
        .es-book-sw-pen { background-color: #14418f; }
        .es-book-sw-pencil { border: 1px dashed rgba(63, 71, 83, 0.7); background-color: #efece3; }
        .es-book-sw-busy {
            border: 1px solid rgba(23, 28, 38, 0.28);
            background-color: #eeeade;
            background-image: repeating-linear-gradient(135deg,
                rgba(23, 28, 38, 0.22) 0 3px, rgba(23, 28, 38, 0) 3px 6px);
        }

        /* --- The two-page spread -------------------------------------- */
        .es-book-spread {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0;
            padding: 1.1rem;
        }
        @media (min-width: 900px) {
            .es-book-spread { grid-template-columns: 1fr 1.6rem 1fr; padding: 1.4rem; }
        }
        .es-book-gutter {
            position: relative;
            min-height: 1.25rem;
            background-image: linear-gradient(to right,
                rgba(23, 28, 38, 0) 0%,
                rgba(23, 28, 38, 0.14) 45%,
                rgba(23, 28, 38, 0.2) 50%,
                rgba(23, 28, 38, 0.14) 55%,
                rgba(23, 28, 38, 0) 100%);
        }
        @media (max-width: 899px) {
            .es-book-gutter {
                min-height: 1px;
                margin: 1.25rem 0;
                background-image: linear-gradient(to bottom,
                    rgba(23, 28, 38, 0) 0%,
                    rgba(23, 28, 38, 0.2) 50%,
                    rgba(23, 28, 38, 0) 100%);
            }
        }
        .es-book-leaf { padding: 0.2rem 0.6rem; }
        .es-book-leaf-title {
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #171c26;
        }
        .es-book-leaf-sub { font-size: 0.72rem; color: #4c525e; line-height: 1.5; }

        .es-book-hrow {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.4rem 0;
            border-bottom: 1px solid rgba(23, 28, 38, 0.12);
            font-size: 0.76rem;
        }
        .es-book-hday { font-weight: 700; color: #171c26; }
        .es-book-hat {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            font-variant-numeric: tabular-nums;
            text-align: right;
            color: #14418f;
        }
        .es-book-hoff {
            font-size: 0.7rem;
            font-style: italic;
            color: #4c525e;
        }
        .es-book-srow {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.28rem 0;
            font-size: 0.72rem;
            color: #4c525e;
        }
        .es-book-sv {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            font-weight: 600;
            text-align: right;
            color: #171c26;
        }
        .es-book-group {
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #4c525e;
        }
        .es-book-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 4.2rem;
            padding: 0.42rem 0.6rem;
            border: 1px solid rgba(20, 65, 143, 0.45);
            border-radius: 0.3rem;
            background-color: #f1f0e8;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.76rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: #14418f;
        }
        .es-book-chip-next {
            background-color: #14418f;
            border-color: #14418f;
            color: #ffffff;
        }
        .es-book-note {
            border-left: 2px solid rgba(20, 65, 143, 0.4);
            padding-left: 0.7rem;
            font-size: 0.72rem;
            line-height: 1.55;
            color: #4c525e;
        }

        /* --- The appointment card the guest keeps --------------------- */
        .es-book-stub { padding: 1.1rem 1.2rem 1.2rem 1.6rem; }
        .es-book-perf {
            position: absolute;
            top: 0.5rem;
            bottom: 0.5rem;
            left: 0.85rem;
            width: 1px;
            background-image: repeating-linear-gradient(to bottom,
                rgba(23, 28, 38, 0.5) 0 3px, rgba(23, 28, 38, 0) 3px 8px);
        }
        .es-book-stub-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.4rem 0;
            border-bottom: 1px dotted rgba(23, 28, 38, 0.22);
            font-size: 0.76rem;
        }
        .es-book-stub-k {
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #4c525e;
        }
        .es-book-stub-v { font-weight: 700; text-align: right; color: #171c26; }
        .es-book-stub-act {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.32rem 0.6rem;
            border: 1px solid rgba(20, 65, 143, 0.4);
            border-radius: 0.3rem;
            background-color: #f1f0e8;
            font-size: 0.7rem;
            font-weight: 700;
            color: #14418f;
        }

        /* --- The register: a real table ------------------------------- */
        .es-book-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        .es-book-table th {
            padding: 0.6rem 0.8rem;
            border-bottom: 2px solid rgba(20, 65, 143, 0.32);
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            text-align: left;
            white-space: nowrap;
            color: #4c525e;
        }
        .dark .es-book-table th { border-bottom-color: rgba(138, 182, 255, 0.35); color: #a3adbd; }
        .es-book-table td {
            padding: 0.7rem 0.8rem;
            border-bottom: 1px solid rgba(23, 28, 38, 0.1);
            vertical-align: top;
            color: #171c26;
        }
        .dark .es-book-table td { border-bottom-color: rgba(238, 241, 246, 0.1); color: #eef1f6; }
        .es-book-tnum {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .es-book-state {
            display: inline-block;
            padding: 0.16rem 0.5rem;
            border-radius: 0.3rem;
            font-size: 0.7rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .es-book-state-ok { background-color: #d8f0e0; color: #14532d; }
        .dark .es-book-state-ok { background-color: #10291b; color: #86efac; }
        .es-book-state-hold { background-color: #e7ecf8; color: #14418f; }
        .dark .es-book-state-hold { background-color: #172033; color: #8ab6ff; }
        .es-book-state-wait { background-color: #f6e9cf; color: #7a4f00; }
        .dark .es-book-state-wait { background-color: #2b2110; color: #fbbf24; }
        .es-book-state-moved { background-color: #e6e6e0; color: #3f4753; }
        .dark .es-book-state-moved { background-color: #232a38; color: #c3cbd8; }
        .es-book-state-off { background-color: #eceae4; color: #565d69; }
        .dark .es-book-state-off { background-color: #1c222e; color: #a3adbd; }

        /* --- Buttons -------------------------------------------------- */
        .es-book-btn {
            background-image: linear-gradient(135deg, #1a45b8, #14418f);
            color: #ffffff;
            border-radius: 0.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 14px 30px -18px rgba(20, 65, 143, 0.95);
        }
        .es-book-btn:hover { transform: translateY(-2px); box-shadow: 0 20px 40px -20px rgba(20, 65, 143, 1); }
        .es-book-btn2 {
            border: 1px solid rgba(20, 65, 143, 0.4);
            background-color: #e8e2d4;
            color: #14418f;
            border-radius: 0.5rem;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .es-book-btn2:hover { transform: translateY(-2px); border-color: rgba(20, 65, 143, 0.75); }
        .dark .es-book-btn2 {
            border-color: rgba(138, 182, 255, 0.4);
            background-color: #161d2a;
            color: #8ab6ff;
        }
        .dark .es-book-btn2:hover { border-color: rgba(138, 182, 255, 0.75); }

        /* --- The closing band: the book shut, cover up ----------------
           The finale is the same object as every sheet above it, closed:
           bookcloth weave, a spine down one edge, the fore-edge of the
           page block down the other, and the same stacked page edges the
           sheets sit on running underneath. */
        .es-book-close {
            background-color: #0b1120;
            background-image:
                radial-gradient(115% 130% at 50% 0%, rgba(26, 69, 184, 0.4) 0%, rgba(11, 17, 32, 0.97) 62%),
                repeating-linear-gradient(90deg, rgba(255, 255, 255, 0.022) 0 2px, transparent 2px 4px),
                repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.022) 0 2px, transparent 2px 4px);
        }
        /* The spine: cloth rolled over the boards, with the hinge crease. */
        .es-book-close-spine {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 1.15rem;
            background-image:
                repeating-linear-gradient(to bottom,
                    transparent 0 5.2rem,
                    rgba(255, 255, 255, 0.09) 5.2rem calc(5.2rem + 2px),
                    transparent calc(5.2rem + 2px) 6rem),
                linear-gradient(to right,
                    rgba(255, 255, 255, 0.16) 0 1px,
                    rgba(0, 0, 0, 0.55) 1px 52%,
                    rgba(255, 255, 255, 0.14) 52% calc(52% + 1px),
                    rgba(0, 0, 0, 0.4) calc(52% + 1px) 100%);
        }
        /* The fore-edge of the block: the closed page stack seen end on. */
        .es-book-close-fore {
            position: absolute;
            top: 1.4rem;
            bottom: 1.4rem;
            right: 0;
            width: 0.7rem;
            border-radius: 0.25rem 0 0 0.25rem;
            background-color: #e8e2d4;
            background-image: repeating-linear-gradient(to bottom,
                rgba(23, 28, 38, 0.2) 0 1px, rgba(255, 255, 255, 0.55) 1px 3px,
                rgba(23, 28, 38, 0.12) 3px 4px, rgba(255, 255, 255, 0.3) 4px 6px);
            box-shadow: inset 2px 0 6px -3px rgba(23, 28, 38, 0.75);
        }
        @media (min-width: 640px) {
            .es-book-close-spine { width: 1.6rem; }
            .es-book-close-fore { width: 0.95rem; }
        }
        /* Page-edge stack for the closed book rather than a single sheet: taller and
           with stronger rules, because the cover's own drop shadow falls across it. */
        .es-book-edge-lg {
            height: 11px;
            margin: 0 2.25rem;
            border-radius: 0 0 1.4rem 1.4rem;
            background-color: #e6e0d1;
            background-image: linear-gradient(to bottom,
                rgba(23, 28, 38, 0.34) 0 1px, #f4f0e5 1px 4px,
                rgba(23, 28, 38, 0.24) 4px 5px, #ece6d8 5px 8px,
                rgba(23, 28, 38, 0.18) 8px 9px, #e4ddcd 9px 11px);
            box-shadow: 0 10px 20px -12px rgba(23, 28, 38, 0.65);
        }
        /* The fore-edge tab, in the always-dark band. */
        .es-book-tab-dark {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.28rem 0.95rem 0.28rem 0.7rem;
            border-radius: 0.25rem 0.95rem 0.95rem 0.25rem;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            background-color: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(169, 200, 255, 0.32);
            color: #a9c8ff;
        }

        /* --- Focus rings --------------------------------------------- */
        .es-book-page a:focus-visible,
        .es-book-page summary:focus-visible,
        .es-book-page input:focus-visible {
            outline: 2px solid #14418f;
            outline-offset: 2px;
        }
        .dark .es-book-page a:focus-visible,
        .dark .es-book-page summary:focus-visible,
        .dark .es-book-page input:focus-visible {
            outline-color: #8ab6ff;
        }
        .es-book-close a:focus-visible,
        .es-book-close input:focus-visible { outline-color: #a9c8ff; }

        @media (prefers-reduced-motion: reduce) {
            .es-book-btn:hover,
            .es-book-btn2:hover,
            .es-book-hover:hover { transform: none; }
        }
    </style>

    <div class="es-book-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the book, open on Thursday                          -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative scroll-mt-24 overflow-hidden pb-16 pt-28 lg:pb-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(26, 69, 184, 0.24), rgba(26, 69, 184, 0) 62%); opacity: 0.55;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(20, 65, 143, 0.18), rgba(20, 65, 143, 0) 62%); opacity: 0.5;"></div>
            <div class="es-book-rulewash absolute inset-0 opacity-70"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-[1.05fr_1fr] lg:gap-16">

                <div>
                    <p class="es-book-tab es-fade-up es-d-1 mb-6"><span class="es-book-tab-no">01</span> Appointments</p>

                    <h1 class="es-balance mb-6 text-[2.5rem] font-black leading-[1.04] tracking-tight sm:text-5xl lg:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Appointment booking,</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-book-grad">one line at a time</span></span></span>
                    </h1>

                    <p class="es-book-muted es-fade-up es-d-2 mb-4 max-w-xl text-lg sm:text-xl">
                        Write your hours into the book once. Guests are shown the lines that are still
                        empty, in their own timezone, and pick one.
                    </p>
                    <p class="es-book-muted es-fade-up es-d-2 mb-9 max-w-xl">
                        It is the same book your events are already written in, which is why a session
                        synced in from Google Calendar quietly takes a line off the page.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
                        <a href="{{ app_url('/sign_up') }}" class="es-book-btn inline-flex items-center justify-center gap-2 px-7 py-4 text-base font-bold">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="#how-it-works" class="es-book-btn2 inline-flex items-center justify-center gap-2 px-7 py-4 text-base font-bold">
                            See how it works
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div data-reveal="right">
                    <div class="es-book-paper">
                        <div class="es-book-spine" aria-hidden="true"></div>
                        <div class="es-book-sheet">
                            <div class="es-book-sheet-head">
                                <span class="es-book-sheet-day">Thursday</span>
                                <span class="es-book-sheet-date">6 August</span>
                                <span class="es-book-sheet-tz">{{ $scheduleTz }}</span>
                            </div>

                            <div class="es-book-day">
                                @foreach ($day as $i => $line)
                                    <div class="es-book-time" style="grid-row: {{ $i + 1 }}; grid-column: 1;">{{ $line['at'] }}</div>
                                    @if ($line['state'] !== 'cont')
                                        <div @class([
                                                'es-book-open' => $line['state'] === 'open',
                                                'es-book-pen' => $line['state'] === 'pen',
                                                'es-book-pencil' => $line['state'] === 'pencil',
                                                'es-book-busy' => $line['state'] === 'busy',
                                                'es-book-pad' => $line['state'] === 'pad',
                                            ]) style="grid-row: {{ $i + 1 }} / span {{ $line['span'] }}; grid-column: 2;">
                                            {{ $line['label'] }}
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <p class="es-book-sheet-foot">
                                Second range today, 14:00 to 16:00, not drawn. Three empty lines this
                                morning, four this afternoon.
                            </p>
                        </div>
                    </div>
                    <div class="es-book-edge" aria-hidden="true"></div>

                    <div class="es-book-legend mt-4">
                        <span><span class="es-book-sw es-book-sw-open" aria-hidden="true"></span>Open</span>
                        <span><span class="es-book-sw es-book-sw-pen" aria-hidden="true"></span>Booked</span>
                        <span><span class="es-book-sw es-book-sw-pencil" aria-hidden="true"></span>Waiting on you</span>
                        <span><span class="es-book-sw es-book-sw-busy" aria-hidden="true"></span>Already busy</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The spread: one book, two readings                         -->
    <!-- ============================================================ -->
    <section id="spread" class="es-book-seam scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <p class="es-book-tab mb-5" data-reveal><span class="es-book-tab-no">02</span> Two readings</p>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight md:text-[2.75rem]" data-reveal>
                    One book, <span class="es-book-grad">two readings</span>
                </h2>
                <p class="es-book-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    The left page is yours: the hours you keep, and the handful of rules that turn them
                    into slots. The right page is the only thing a guest ever sees, worked out from the
                    left one and converted to their clock. Both pages here are rendered from the same
                    figures.
                </p>
            </div>

            <div class="es-book-paper" data-reveal="panel">
                <div class="es-book-spread">

                    <!-- Verso: what you write in -->
                    <div class="es-book-leaf">
                        <p class="es-book-leaf-title">What you write in</p>
                        <p class="es-book-leaf-sub mt-1 mb-4">
                            Appointments tab. Tick the days, set the hours, and a day can hold up to
                            four ranges so the middle of the afternoon can stay yours.
                        </p>

                        <div>
                            @foreach ($hours as $row)
                                <div class="es-book-hrow">
                                    <span class="es-book-hday">{{ $row['day'] }}</span>
                                    @if ($row['at'])
                                        <span class="es-book-hat">{{ $row['at'] }}</span>
                                    @else
                                        <span class="es-book-hoff">no bookings</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <p class="es-book-group mt-5 mb-1">The rules on this type</p>
                        <div>
                            @foreach ($settings as $set)
                                <div class="es-book-srow">
                                    <span>{{ $set['k'] }}</span>
                                    <span class="es-book-sv">{{ $set['v'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-book-note mt-5">
                            Hours are wall clock in your schedule's timezone, anchored per date, so the
                            clocks changing does not shift your morning. An override replaces a single
                            date's hours rather than adding to them, which is what a public holiday or
                            one late evening actually is.
                        </p>
                    </div>

                    <div class="es-book-gutter" aria-hidden="true"></div>

                    <!-- Recto: what a guest is offered -->
                    <div class="es-book-leaf">
                        <p class="es-book-leaf-title">What a guest is offered</p>
                        <p class="es-book-leaf-sub mt-1 mb-4">
                            Thursday 6 August, in {{ $guestTz }}, because that is the clock on their
                            laptop. Your own timezone is named underneath so nobody does the arithmetic
                            in their head, and they can switch to any other one. The filled chip is the
                            earliest line still open.
                        </p>

                        @foreach ($guestParts as $partIndex => $part)
                            @php $partTimes = array_values(array_filter($guestTimes, fn ($t) => $t['part'] === $part)); @endphp
                            @if (count($partTimes))
                                <p class="es-book-group mb-2 {{ $partIndex ? 'mt-5' : '' }}">{{ $part }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($partTimes as $slot)
                                        <span class="es-book-chip {{ $slot['guest'] === $earliestGuest ? 'es-book-chip-next' : '' }}">{{ $slot['guest'] }}</span>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach

                        <p class="es-book-note mt-5">
                            Morning, afternoon and evening are split on the guest's own hour, which is why
                            one 09:00 line in your book turns up in their morning and the rest do not.
                            Behind each chip is an instant, not a wall-clock string, so the booking lands
                            on the same minute in your book as it does in their calendar. Pick a month with
                            nothing left in it and the page offers a jump to the next date that has
                            something.
                        </p>

                        <p class="es-book-group mt-6 mb-2">The same times, in your book</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($guestTimes as $slot)
                                <span class="es-book-chip">{{ $slot['local'] }}</span>
                            @endforeach
                        </div>

                        <p class="es-book-leaf-sub mt-4">
                            Nothing else on this page leaks. Guests see a type, a duration and a price,
                            never who has taken the other lines.
                        </p>
                    </div>

                </div>
            </div>
            <div class="es-book-edge" aria-hidden="true"></div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The lines: how the pitch is set                            -->
    <!-- ============================================================ -->
    <section id="lines" class="es-book-seam scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <p class="es-book-tab mb-5" data-reveal><span class="es-book-tab-no">03</span> The lines</p>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight md:text-[2.75rem]" data-reveal>
                    You rule the lines, <span class="es-book-grad">not the day</span>
                </h2>
                <p class="es-book-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    A duration says how long an appointment runs. The start-time interval says how often
                    a line is offered, and it is the one setting that changes the shape of the page.
                    All three columns below rule the same two hours for the same 30 minute appointment.
                    Only the count of lines actually on offer changes.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                @foreach ($pitchDemo as $col)
                    <div data-reveal>
                        <div class="es-book-paper">
                            <div class="es-book-spine" aria-hidden="true"></div>
                            <div class="es-book-sheet">
                                <div class="es-book-sheet-head">
                                    <span class="es-book-sheet-day" style="font-size: 0.9rem;">{{ $col['k'] }}</span>
                                    <span class="es-book-sheet-tz">{{ $col['count'] }}</span>
                                </div>
                                <div class="es-book-day" style="--bk-pitch: {{ $col['pitch'] }};">
                                    @foreach ($col['rows'] as $ri => $row)
                                        <div class="es-book-time" style="grid-row: {{ $ri + 1 }}; grid-column: 1;">{{ $row[0] }}</div>
                                        @if ($row[1] === 'tick')
                                            <div class="es-book-tick" style="grid-row: {{ $ri + 1 }}; grid-column: 2;"></div>
                                        @elseif ($row[1] === 'none')
                                            <div class="es-book-blank" style="grid-row: {{ $ri + 1 }}; grid-column: 2;"></div>
                                        @else
                                            <div @class([
                                                    'es-book-open' => $row[1] === 'open',
                                                    'es-book-pen' => $row[1] === 'pen',
                                                    'es-book-pad' => $row[1] === 'pad',
                                                ]) style="grid-row: {{ $ri + 1 }}; grid-column: 2;">{{ $row[2] }}</div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="es-book-edge" aria-hidden="true"></div>
                        <p class="es-book-muted mt-4 text-sm">{{ $col['note'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="es-book-card mt-8 p-6 lg:p-8" data-reveal>
                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <h3 class="es-book-ink mb-2 text-base font-bold">A line can be taken by anything</h3>
                        <p class="es-book-muted text-sm">
                            Open times are worked out against everything already on the schedule: your own
                            events, bookings on your other appointment types, and events synced in from
                            Google Calendar, Outlook or CalDAV. A request still waiting on you holds its
                            line too. An entry with no length on it blocks two hours rather than nothing,
                            and an all-day entry with no time at all takes no line.
                        </p>
                    </div>
                    <div>
                        <h3 class="es-book-ink mb-2 text-base font-bold">Notice and window</h3>
                        <p class="es-book-muted text-sm">
                            Minimum notice keeps guests off the next few hours. The booking window stops
                            them wandering into next spring. Both are guest-side rules: when you move a
                            booking yourself, your picker ignores them.
                        </p>
                    </div>
                    <div>
                        <h3 class="es-book-ink mb-2 text-base font-bold">Set your timezone first</h3>
                        <p class="es-book-muted text-sm">
                            Slot maths runs in your schedule's timezone. Leave it unset and the
                            application default is used instead, which is why the Appointments tab nags
                            you until you fix it under Details.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. How it works                                              -->
    <!-- ============================================================ -->
    <section id="how-it-works" class="es-book-seam scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <p class="es-book-tab mb-5" data-reveal><span class="es-book-tab-no">04</span> How it works</p>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight md:text-[2.75rem]" data-reveal>
                    Four things to <span class="es-book-grad">write down</span>
                </h2>
                <p class="es-book-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Setting a type up is one form and one link. After that the book keeps itself.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="90">
                @foreach ($steps as $si => $step)
                    <div class="es-book-card es-book-hover flex h-full flex-col p-6" data-reveal>
                        <p class="es-book-no mb-4">{{ sprintf('%02d', $si + 1) }}</p>
                        <h3 class="es-book-ink mb-2 text-lg font-bold">{{ $step[0] }}</h3>
                        <p class="es-book-muted text-sm">{{ $step[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The register: the Bookings tab as a record                 -->
    <!-- ============================================================ -->
    <section id="register" class="es-book-seam scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <p class="es-book-tab mb-5" data-reveal><span class="es-book-tab-no">05</span> The register</p>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight md:text-[2.75rem]" data-reveal>
                    Every line, <span class="es-book-grad">and what it is doing</span>
                </h2>
                <p class="es-book-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    The Bookings view filters by Upcoming, Pending, Past and Cancelled. What matters in
                    a book is not the name, it is whether the time is spoken for.
                </p>
            </div>

            <div class="es-book-card overflow-x-auto p-2 sm:p-4" data-reveal>
                <table class="es-book-table">
                    <caption class="es-book-muted px-3 pb-3 text-left text-sm">
                        A schedule's Bookings list: the four states a booking can be labelled with, plus
                        the marker one carries once it has been moved.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col">Guest</th>
                            <th scope="col">Appointment type</th>
                            <th scope="col">When</th>
                            <th scope="col">State</th>
                            <th scope="col">What the line is doing</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($register as $row)
                            <tr>
                                <td class="font-semibold">{{ $row['guest'] }}</td>
                                <td>{{ $row['type'] }}</td>
                                <td class="es-book-tnum">{{ $row['when'] }}</td>
                                <td>
                                    <span @class([
                                        'es-book-state',
                                        'es-book-state-ok' => $row['tone'] === 'ok',
                                        'es-book-state-hold' => $row['tone'] === 'hold',
                                        'es-book-state-wait' => $row['tone'] === 'wait',
                                        'es-book-state-moved' => $row['tone'] === 'moved',
                                        'es-book-state-off' => $row['tone'] === 'off',
                                    ])>{{ $row['state'] }}</span>
                                </td>
                                <td><span class="es-book-muted">{{ $row['slot'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <div class="es-book-card es-book-hover flex h-full flex-col p-6" data-reveal>
                    <h3 class="es-book-ink mb-2 text-base font-bold">Approve, or do not</h3>
                    <p class="es-book-muted text-sm">
                        With approval on, bookings arrive on your Requests tab next to submitted events.
                        Accept and the guest gets their confirmation and invite; decline and they are
                        told they are welcome to pick another time. On a card or link type, accepting
                        does not confirm on its own: the payment does that.
                    </p>
                </div>
                <div class="es-book-card es-book-hover flex h-full flex-col p-6" data-reveal>
                    <h3 class="es-book-ink mb-2 text-base font-bold">Move it, do not redo it</h3>
                    <p class="es-book-muted text-sm">
                        Rescheduling moves the booking in place, so the payment, the guest's private link
                        and the row in your sales all survive. Whoever did not start the move is emailed,
                        and the guest's updated invite shifts the entry already in their calendar instead
                        of adding a second one.
                    </p>
                </div>
                <div class="es-book-card es-book-hover flex h-full flex-col p-6" data-reveal>
                    <h3 class="es-book-ink mb-2 text-base font-bold">Cancel releases the line</h3>
                    <p class="es-book-muted text-sm">
                        Cancel a booking and the guest is emailed and the time goes back on offer.
                        Money does not move on its own: refund it with your payment provider, then mark
                        the sale refunded. The cancellation email to you carries the amount and the
                        reference so you have both to hand.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Their copy: the appointment card                           -->
    <!-- ============================================================ -->
    <section id="card" class="es-book-seam scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <p class="es-book-tab mb-5" data-reveal><span class="es-book-tab-no">06</span> Their copy</p>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight md:text-[2.75rem]" data-reveal>
                    The guest walks off <span class="es-book-grad">with the card</span>
                </h2>
                <p class="es-book-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    You keep the book. They get one line copied out, and everything they need to change
                    their mind without emailing you about it.
                </p>
            </div>

            <div class="grid items-start gap-10 lg:grid-cols-2 lg:gap-14">
                <div data-reveal="left">
                    <div class="es-book-paper">
                        <div class="es-book-perf" aria-hidden="true"></div>
                        <div class="es-book-stub">
                            <div class="es-book-sheet-head">
                                <span class="es-book-sheet-day">Intro call</span>
                                <span class="es-book-sheet-date">30 min</span>
                                <span class="es-book-sheet-tz">confirmed</span>
                            </div>

                            <div class="es-book-stub-row">
                                <span class="es-book-stub-k">With</span>
                                <span class="es-book-stub-v">Rowan Ellis Studio</span>
                            </div>
                            <div class="es-book-stub-row">
                                <span class="es-book-stub-k">When</span>
                                <span class="es-book-stub-v">Thu 6 Aug, {{ $stubGuest }} to {{ $stubGuestEnd }}</span>
                            </div>
                            <div class="es-book-stub-row">
                                <span class="es-book-stub-k">Your clock</span>
                                <span class="es-book-stub-v">{{ $guestTz }}</span>
                            </div>
                            <div class="es-book-stub-row">
                                <span class="es-book-stub-k">In their book</span>
                                <span class="es-book-stub-v">{{ $stubLocal }}, {{ $scheduleTz }}</span>
                            </div>
                            <div class="es-book-stub-row">
                                <span class="es-book-stub-k">Where</span>
                                <span class="es-book-stub-v">Online, link in your email</span>
                            </div>
                            <div class="es-book-stub-row">
                                <span class="es-book-stub-k">Price</span>
                                <span class="es-book-stub-v">Free</span>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="es-book-stub-act">Add to calendar</span>
                                <span class="es-book-stub-act">Move to another time</span>
                                <span class="es-book-stub-act">Cancel</span>
                            </div>
                            <p class="es-book-sheet-foot">
                                A private link, not a login. Moving and cancelling stay open to them
                                right up until the appointment starts.
                            </p>
                        </div>
                    </div>
                    <div class="es-book-edge" aria-hidden="true"></div>
                </div>

                <div class="space-y-4" data-reveal-group="90">
                    <div class="es-book-card es-book-hover p-6" data-reveal>
                        <h3 class="es-book-ink mb-2 text-base font-bold">A calendar invite, in the email</h3>
                        <p class="es-book-muted text-sm">
                            The confirmation carries an invite, so one tap puts the appointment in their
                            calendar with the address, the meeting link or the phone number already on it.
                        </p>
                    </div>
                    <div class="es-book-card es-book-hover p-6" data-reveal>
                        <h3 class="es-book-ink mb-2 text-base font-bold">A reminder about a day out</h3>
                        <p class="es-book-muted text-sm">
                            Confirmed bookings get a reminder roughly 24 hours before they start. A
                            request still waiting on you does not get one, and neither does a card
                            booking that has not been paid.
                        </p>
                    </div>
                    <div class="es-book-card es-book-hover p-6" data-reveal>
                        <h3 class="es-book-ink mb-2 text-base font-bold">They can move it themselves</h3>
                        <p class="es-book-muted text-sm">
                            The same link offers the times anyone else would see. Their old line is
                            released the moment they take a new one, and they are told that before they
                            commit. On a type that needs approval, a guest move comes back to you. A card
                            booking they have not paid for yet has to be paid before it can be moved,
                            because the hold it is sitting on runs out on its own clock.
                        </p>
                    </div>
                    <div class="es-book-card es-book-hover p-6" data-reveal>
                        <h3 class="es-book-ink mb-2 text-base font-bold">You hear about all of it</h3>
                        <p class="es-book-muted text-sm">
                            New bookings, new requests, moves and guest cancellations all email you, and
                            the whole booking sits in your admin with the guest's notes attached.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Charging for the line                                      -->
    <!-- ============================================================ -->
    <section id="money" class="es-book-seam scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <p class="es-book-tab mb-5" data-reveal><span class="es-book-tab-no">07</span> Charging</p>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight md:text-[2.75rem]" data-reveal>
                    A line can have <span class="es-book-grad">a price on it</span>
                </h2>
                <p class="es-book-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    A free intro call and a paid session can sit side by side with their own hours,
                    prices and rules. What you charge is yours: Event Schedule takes no cut of it.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ($money as $m)
                    <div class="es-book-card es-book-hover flex h-full flex-col p-6" data-reveal>
                        <p class="es-book-accent mb-2 text-sm font-bold uppercase tracking-widest">{{ $m['k'] }}</p>
                        <p class="es-book-muted text-sm">{{ $m['v'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="es-book-card mt-4 p-6 lg:p-8" data-reveal>
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <h3 class="es-book-ink mb-2 text-base font-bold">A paid type hides itself until it can be paid</h3>
                        <p class="es-book-muted text-sm">
                            Priced types stay off your booking page until a working payment method is
                            connected, and the Appointments tab tells you which one is being held back.
                            Connect Stripe or add a payment link under Account Settings and it appears.
                        </p>
                    </div>
                    <div>
                        <h3 class="es-book-ink mb-2 text-base font-bold">Refunds are a decision, not a side effect</h3>
                        <p class="es-book-muted text-sm">
                            Cancelling a paid booking never moves money. Refund it in Stripe or your
                            provider, then mark the sale refunded, so the money and the record only ever
                            change because you said so.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Good to know                                               -->
    <!-- ============================================================ -->
    <section id="know" class="es-book-seam scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <p class="es-book-tab mb-5" data-reveal><span class="es-book-tab-no">08</span> Marginalia</p>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight md:text-[2.75rem]" data-reveal>
                    Notes in <span class="es-book-grad">the margin</span>
                </h2>
                <p class="es-book-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    The small print that saves an email later.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                @foreach ($facts as $fact)
                    <div class="es-book-card es-book-hover flex h-full flex-col p-6" data-reveal>
                        <h3 class="es-book-ink mb-2 text-base font-bold">{{ $fact[0] }}</h3>
                        <p class="es-book-muted text-sm">{{ $fact[1] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="es-book-card mt-6 p-6" data-reveal>
                <p class="es-book-muted text-sm">
                    <span class="es-book-ink font-bold">Booking is on the free plan, with one appointment type</span>,
                    and that one type is the whole feature: hours, overrides, buffers, approvals and payment.
                    Pro is what lets you offer several side by side, and a selfhosted deployment is uncapped.
                    Everything the book leans on is free too: your public schedule, two-way Google, Outlook
                    and CalDAV calendar sync, and the calendar you can embed on your own site.
                </p>
            </div>
        </div>
    </section>

    {{-- The shared plan band. Every one of these pages states its tiers inline
         with Free/Pro chips, but none of them offered a way to see what the
         tiers cost, or even a link to /pricing - a reader who got this far had
         to go back to the header. --}}
    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 9. Related features                                           -->
    <!-- ============================================================ -->
    <section class="es-book-seam py-16">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <p class="es-book-eyebrow mb-5" data-reveal>Nearby in the book</p>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card name="Availability" description="Whole dates a talent-schedule member crosses out, on the Enterprise plan." :url="route('marketing.availability')" iconColor="sky">
                        <x-slot:icon>
                            <svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google, Outlook and CalDAV, so busy stays busy." :url="marketing_url('/features/calendar-sync')" iconColor="blue">
                        <x-slot:icon>
                            <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell tickets to your events with zero platform fees." :url="marketing_url('/features/ticketing')" iconColor="emerald">
                        <x-slot:icon>
                            <svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            {{-- The shared <x-link> paints brand blue #2563eb, which measures 4.38 on
                 this page's ground and 5.03 on the card, so the note sits on a card. --}}
            <div class="es-book-card mt-6 px-4 py-3 text-center" data-reveal>
                <p class="es-book-muted text-sm">
                    Want the details? <x-link href="{{ route('marketing.docs.appointments') }}">Read the Appointments guide</x-link>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. FAQ                                                       -->
    <!-- ============================================================ -->
    <section id="faq" class="es-book-seam scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <p class="es-book-tab mb-5" data-reveal><span class="es-book-tab-no">09</span> Questions</p>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight md:text-[2.75rem]" data-reveal>
                    Frequently asked <span class="es-book-grad">questions</span>
                </h2>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-book-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-5">
                            <h3 class="es-book-ink text-base font-bold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-book-accent h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-book-muted faq-answer px-5 pb-5 text-sm">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Finale: close the book                                    -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-book-close noise relative overflow-hidden rounded-[2rem] px-8 py-16 text-center shadow-2xl sm:px-14 lg:py-24" data-confetti data-reveal="panel">
                <div class="es-book-close-spine" aria-hidden="true"></div>
                <div class="es-book-close-fore" aria-hidden="true"></div>
                <div class="relative z-10">
                    <p class="es-book-tab-dark mb-6"><span class="es-book-tab-no">10</span> Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Open the book, <span class="es-book-grad">write in the hours</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        One link, your own hours, and no more asking people what time suits them.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-300 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-book-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden px-8 py-4 text-lg font-bold">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-book-lit mt-6 text-sm font-semibold">Free with one type, uncapped on Pro, and unlimited on every selfhosted deployment</p>
                </div>
            </div>
            {{-- The same page-edge stack every sheet on this page sits on, sized for the
                 closed book. Last beat of the concept: the object shuts on the CTA. --}}
            <div class="es-book-edge es-book-edge-lg" aria-hidden="true"></div>
        </div>
    </section>

    <!-- Section dot navigation -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-500/60 dark:bg-white/30"></span>
                        <span class="es-book-card pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap px-3 py-1 text-xs font-semibold opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
