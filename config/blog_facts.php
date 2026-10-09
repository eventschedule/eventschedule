<?php

/*
|--------------------------------------------------------------------------
| What the blog may say about the product
|--------------------------------------------------------------------------
|
| The only things a generated blog post may state about Event Schedule (App\Services\Blog).
| Until 2026-10 the writing prompt carried one fact ("an open-source event management
| platform"), so posts either hedged ("a platform like Event Schedule often...") or invented:
| volunteer scheduling, member-only public events, automatic price changes.
|
| Each line: the plan it needs (`tier`), one true sentence (`says`), the marketing page that is
| about it (`url`, a key of config/marketing_keywords.php or /pricing), and, where the user
| guide explains how to do it, the guide page (`guide`, a file of resources/views/marketing/docs)
| with the words that mark its relevant sections (`match`). The guide's own text is what lets a
| post name a screen or a button; without it the model makes them up.
|
| `tier` is one of about, free, pro, enterprise, not. The "not" lines exist to stop a claim,
| not to be repeated to readers.
|
| docs/FEATURES.md is the source of truth for plans: a feature that moves plan there moves
| here in the same change. Prices are never typed: the :placeholders are filled from
| PlatformPricing at run time. BlogFactsTest holds the shape, the URLs and the guide pages.
|
*/

return [
    // What it is
    'what' => ['tier' => 'about', 'says' => 'Event Schedule is an open-source event calendar and ticketing platform. An organizer makes a "schedule": a public page of their events at its own address (name.eventschedule.com). Events and schedules are unlimited on every plan.', 'url' => '/features'],
    'types' => ['tier' => 'about', 'says' => 'There are three kinds of schedule: Talent (a performer or teacher), Venue (a place) and Curator (a listing of other people\'s events).'],
    'plans' => ['tier' => 'about', 'says' => 'Plans: Free (:free). Pro (:pro_monthly a month or :pro_yearly a year). Enterprise (:enterprise_monthly a month or :enterprise_yearly a year). Selfhosted installs have every feature.', 'url' => '/pricing'],
    'fees' => ['tier' => 'about', 'says' => 'Event Schedule takes no platform fee or per-ticket fee on any plan. The payment processor (Stripe, PayPal) still charges its own card fee.', 'url' => '/ticket-fee-calculator'],

    // On every plan (Free included)
    'recurring' => ['tier' => 'free', 'says' => 'Recurring events: daily, weekly on chosen days, every N weeks, monthly by date or by weekday (the 2nd Tuesday), yearly, with single dates added or skipped.', 'url' => '/features/recurring-events', 'guide' => 'creating-events', 'match' => ['recurring', 'repeat', 'every']],
    'embed' => ['tier' => 'free', 'says' => 'Embed the calendar on your own website with a snippet from the Embed dialog. The embedded calendar updates when events change.', 'url' => '/features/embed-calendar', 'guide' => 'sharing', 'match' => ['embed']],
    'rsvp' => ['tier' => 'free', 'says' => 'Free registration (RSVP) for free events, with an optional capacity limit. Unlimited on every plan.', 'url' => '/features/registration', 'guide' => 'tickets', 'match' => ['registration', 'rsvp']],
    'free-tickets' => ['tier' => 'free', 'says' => 'Ticket types priced at zero can be offered on every plan.', 'guide' => 'tickets', 'match' => ['free']],
    'sales-window' => ['tier' => 'free', 'says' => 'Each ticket type can have a date sales open and a date sales close, and group-rate (volume) discounts. This is how an early-bird price is done: an early-bird ticket type that stops selling on a date.', 'guide' => 'tickets', 'match' => ['sales start', 'sales end', 'early', 'volume', 'group']],
    'scan' => ['tier' => 'free', 'says' => 'Scan the QR code on any ticket or registration at the door with a phone. Each ticket admits once and a second scan warns.', 'url' => '/features/check-in', 'guide' => 'tickets', 'match' => ['scan', 'check-in', 'check in']],
    'sync' => ['tier' => 'free', 'says' => 'Two-way sync with Google Calendar, Outlook / Microsoft 365 and CalDAV.', 'url' => '/features/calendar-sync'],
    'follow' => ['tier' => 'free', 'says' => 'Visitors can follow a schedule or leave an email address. Confirmed subscribers get an automatic email digest when the schedule publishes new events.', 'guide' => 'newsletters', 'match' => ['subscriber', 'follow', 'sign-up']],
    'newsletter' => ['tier' => 'free', 'says' => 'A newsletter builder for writing to followers and subscribers. The monthly allowance counts recipients: 10 on Free, 100 on Pro, 1,000 on Enterprise, unlimited when the schedule sends through its own email server.', 'url' => '/features/newsletters', 'guide' => 'newsletters', 'match' => ['newsletter', 'send', 'recipient']],
    'notify-me' => ['tier' => 'free', 'says' => 'An optional "Notify me" card on an event page: a visitor leaves an email and hears when that event\'s tickets go on sale or if it is cancelled.'],
    'calendar-add' => ['tier' => 'free', 'says' => 'Guests can add an event to their own calendar, or subscribe to a schedule\'s live calendar feed.', 'guide' => 'sharing', 'match' => ['calendar', 'subscribe']],
    'requests' => ['tier' => 'free', 'says' => 'A schedule can accept event requests or booking requests from the public through a form; the owner approves each one.', 'url' => '/features/booking-requests', 'guide' => 'creating-schedules', 'match' => ['request', 'booking form']],
    'curator' => ['tier' => 'free', 'says' => 'A curator schedule lists events from other schedules, and can name source schedules whose public events then appear on it automatically.', 'url' => '/for-curators'],
    'sub-schedules' => ['tier' => 'free', 'says' => 'Sub-schedules group the events inside one schedule (rooms, stages, class types). Visitors can filter by them.', 'url' => '/features/sub-schedules', 'guide' => 'creating-schedules', 'match' => ['sub-schedule']],
    'online' => ['tier' => 'free', 'says' => 'An event can be online: it carries the link to wherever the stream or meeting is hosted. Event Schedule does not host video.', 'url' => '/features/online-events', 'guide' => 'creating-events', 'match' => ['online']],
    'analytics' => ['tier' => 'free', 'says' => 'Built-in analytics: page views, followers and sales per schedule.', 'url' => '/features/analytics', 'guide' => 'analytics', 'match' => ['analytics', 'views']],
    'import' => ['tier' => 'free', 'says' => 'Create events by pasting text, a link or a flyer image; the details are read automatically (daily limits apply).', 'url' => '/features/ai', 'guide' => 'ai-import', 'match' => ['import', 'paste']],
    'graphics' => ['tier' => 'free', 'says' => 'Generate a shareable image of upcoming events for social media.', 'url' => '/features/event-graphics', 'guide' => 'event-graphics', 'match' => ['graphic']],
    'appointments' => ['tier' => 'free', 'says' => 'Appointment booking: one free appointment type on Free. More types, paid bookings and advanced rules need Pro.', 'url' => '/features/appointments', 'guide' => 'appointments', 'match' => ['appointment']],
    'fan-content' => ['tier' => 'free', 'says' => 'Guests can submit photos, videos and comments to an event; each goes through approval.', 'url' => '/features/fan-videos', 'guide' => 'creating-events', 'match' => ['fan content', 'photo', 'video']],
    'visibility' => ['tier' => 'free', 'says' => 'An event is Public or Draft on every plan.'],

    // On the Pro plan and above (not on Free)
    'paid-tickets' => ['tier' => 'pro', 'says' => 'Selling tickets that have a price: priced ticket types, paid through Stripe, PayPal, Payfast, Invoice Ninja, a payment link or cash at the door. A Free schedule can try selling for 7 days.', 'url' => '/features/ticketing', 'guide' => 'tickets', 'match' => ['ticket type', 'price', 'payment', 'stripe', 'sell tickets']],
    'refunds' => ['tier' => 'pro', 'says' => 'Full and partial refunds through Stripe and PayPal.', 'guide' => 'tickets', 'match' => ['refund']],
    'promo' => ['tier' => 'pro', 'says' => 'Promo codes: a percentage or a fixed amount off, with a usage limit and an expiry date.', 'url' => '/features/promo-codes', 'guide' => 'tickets', 'match' => ['promo']],
    'addons' => ['tier' => 'pro', 'says' => 'Add-ons sold beside a ticket, with their own stock.', 'guide' => 'tickets', 'match' => ['add-on']],
    'passes' => ['tier' => 'pro', 'says' => 'Passes: one purchase used across several events (class pack, visit pass, membership, season pass, festival pass), with use tracked.', 'url' => '/features/passes', 'guide' => 'subscriptions', 'match' => ['pass', 'subscription']],
    'gift-cards' => ['tier' => 'pro', 'says' => 'Gift cards sent to a recipient by email and redeemed toward tickets.', 'url' => '/features/gift-cards', 'guide' => 'gift-cards', 'match' => ['gift']],
    'installments' => ['tier' => 'pro', 'says' => 'Let a buyer split a ticket over monthly payments (Stripe only).', 'url' => '/features/installments', 'guide' => 'tickets', 'match' => ['installment']],
    'waitlist' => ['tier' => 'pro', 'says' => 'A waitlist on sold-out tickets that emails people when tickets free up. The waitlist for free registration is on every plan.', 'url' => '/features/waitlist', 'guide' => 'tickets', 'match' => ['waitlist']],
    'checkin-dashboard' => ['tier' => 'pro', 'says' => 'A live check-in dashboard showing who has arrived, by ticket type.', 'url' => '/features/check-in', 'guide' => 'tickets', 'match' => ['check-in', 'check in', 'dashboard']],
    'individual-tickets' => ['tier' => 'pro', 'says' => 'Collect each attendee\'s details so every guest gets their own ticket and QR code.', 'guide' => 'tickets', 'match' => ['individual', 'each guest', 'per-guest']],
    'embed-tickets' => ['tier' => 'pro', 'says' => 'Embed the ticket purchase form on another website.', 'url' => '/features/embed-tickets', 'guide' => 'tickets', 'match' => ['embed']],
    'custom-fields' => ['tier' => 'pro', 'says' => 'Custom questions on events and at checkout.', 'url' => '/features/custom-fields'],
    'polls' => ['tier' => 'pro', 'says' => 'Polls on an event page that guests vote in.', 'url' => '/features/polls', 'guide' => 'creating-events', 'match' => ['poll']],
    'feedback' => ['tier' => 'pro', 'says' => 'Post-event feedback: attendees are emailed for a star rating and a comment.', 'url' => '/features/feedback'],
    'carpool' => ['tier' => 'pro', 'says' => 'Carpool matching: attendees offer and request rides.', 'url' => '/features/carpool'],
    'boost' => ['tier' => 'pro', 'says' => 'Promote an event with Facebook and Instagram ads from inside the app.', 'url' => '/features/boost', 'guide' => 'boost', 'match' => ['boost', 'ad']],
    'sponsors' => ['tier' => 'pro', 'says' => 'Sponsor logos, in tiers, on the schedule page.', 'guide' => 'creating-schedules', 'match' => ['sponsor']],
    'branding' => ['tier' => 'pro', 'says' => 'Remove Event Schedule branding and add custom CSS.', 'url' => '/features/white-label'],
    'export' => ['tier' => 'pro', 'says' => 'Export sales as CSV. Import attendees in bulk from CSV.'],
    'api' => ['tier' => 'pro', 'says' => 'REST API and webhooks.'],
    'templates' => ['tier' => 'pro', 'says' => 'Save an event as a template.'],

    // On the Enterprise plan only
    'seating' => ['tier' => 'enterprise', 'says' => 'Reserved seating: seating plans, a seat picker for buyers and a box office console.', 'url' => '/features/allocated-seating', 'guide' => 'allocated-seating', 'match' => ['seat']],
    'custom-domain' => ['tier' => 'enterprise', 'says' => 'A custom domain for a schedule.', 'url' => '/features/custom-domain'],
    'private' => ['tier' => 'enterprise', 'says' => 'Internal events (team only) and Unlisted events (reachable only by link, with an optional password).', 'url' => '/features/private-events'],
    'team' => ['tier' => 'enterprise', 'says' => 'More than one team member on a schedule, as admin or viewer. Team members can mark their availability.', 'url' => '/features/team-scheduling'],
    'ai-content' => ['tier' => 'enterprise', 'says' => 'Generate event descriptions and flyer images.'],

    // What Event Schedule does not do (do not suggest it does)
    'no-volunteers' => ['tier' => 'not', 'says' => 'No volunteer sign-up sheets, shift scheduling, rotas or volunteer profiles.'],
    'no-crm' => ['tier' => 'not', 'says' => 'No contact database, donor management or sponsor management beyond followers, subscribers, ticket buyers and sponsor logos.'],
    'no-video' => ['tier' => 'not', 'says' => 'No built-in video streaming or webinar room.'],
    'no-gating' => ['tier' => 'not', 'says' => 'A pass or membership pays for entry; it does not hide events from non-members.'],
    'no-sms' => ['tier' => 'not', 'says' => 'No text-message marketing.'],
    'no-auto-price' => ['tier' => 'not', 'says' => 'Prices do not change by themselves; use ticket types with sales dates.'],
    'no-speakers' => ['tier' => 'not', 'says' => 'No speaker or exhibitor management.'],
];
