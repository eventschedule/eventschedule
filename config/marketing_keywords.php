<?php

/*
|--------------------------------------------------------------------------
| Marketing keyword map
|--------------------------------------------------------------------------
|
| One primary search query per marketing page, so two pages never compete for the same one.
|
| This is the keyword map: before a page is written or retitled, look here for the query it
| owns, and before a new page ships, give it an entry (MarketingKeywordMapTest fails the build
| for a listed page with no entry, and for an entry whose page is gone).
|
| Every page's <title> and its <h1> must contain the keyword, compared case-insensitively
| after entities are decoded and whitespace is collapsed. The h1 usually carries it in the
| <x-marketing.hero-eyebrow> pill, so the tagline below can stay ad copy. `match` lists
| accepted variants for copy that reads better with a small change ("Event calendars for
| bars" in a title, "Replace Calendly" in a headline); either the keyword or one variant
| must appear, in each of the two places separately.
|
| No two pages may share a keyword. Two pages that genuinely answer the same query should be
| one page, or one of them should target something narrower, as /google-calendar,
| /outlook-calendar, /caldav and /features/calendar-sync do below.
|
| Not listed, deliberately: the docs (guides are allowed to sit behind the feature page for
| the same query, and their titles are phrased as guides so they do not compete with it),
| the legal pages, /blog (another host) and /search (noindex).
|
*/

return [
    // Home and hubs
    '/' => ['keyword' => 'free event calendar'],
    '/features' => ['keyword' => 'event management software'],
    '/pricing' => ['keyword' => 'Event Schedule pricing'],
    '/about' => ['keyword' => 'open source event management platform'],
    '/examples' => ['keyword' => 'Event Schedule examples'],
    '/browse' => ['keyword' => 'upcoming events'],
    '/use-cases' => ['keyword' => 'event scheduling software'],
    '/faq' => ['keyword' => 'Event Schedule FAQ'],
    '/contact' => ['keyword' => 'contact Event Schedule'],
    '/why-create-account' => ['keyword' => 'free account'],
    '/compare' => ['keyword' => 'compare event platforms'],
    '/ticket-fee-calculator' => ['keyword' => 'ticket fee calculator'],
    '/replace' => ['keyword' => 'all-in-one event platform'],

    // Hosting and licensing
    '/selfhost' => ['keyword' => 'selfhosted event calendar'],
    '/open-source' => ['keyword' => 'open source event calendar'],
    '/saas' => ['keyword' => 'white-label ticketing platform'],

    // Integrations. Calendar sync is split by provider so the four pages do not compete.
    '/google-calendar' => ['keyword' => 'Google Calendar sync'],
    '/outlook-calendar' => ['keyword' => 'Outlook calendar sync', 'match' => ['Outlook & Microsoft 365 calendar sync', 'Outlook and Microsoft 365 calendar sync']],
    '/caldav' => ['keyword' => 'CalDAV calendar sync'],
    '/stripe' => ['keyword' => 'Stripe payments for tickets'],
    '/paypal' => ['keyword' => 'sell event tickets with PayPal'],
    '/invoiceninja' => ['keyword' => 'Invoice Ninja ticketing'],

    // Features
    '/features/ai' => ['keyword' => 'AI event import'],
    '/features/allocated-seating' => ['keyword' => 'allocated seating', 'match' => ['reserved seating']],
    '/features/analytics' => ['keyword' => 'event analytics'],
    '/features/appointments' => ['keyword' => 'appointment booking'],
    '/features/availability' => ['keyword' => 'availability calendar'],
    '/features/boost' => ['keyword' => 'Facebook and Instagram ads'],
    '/features/calendar-sync' => ['keyword' => 'two-way calendar sync'],
    '/features/carpool' => ['keyword' => 'event carpool'],
    '/features/check-in' => ['keyword' => 'QR ticket check-in', 'match' => ['QR code check-in']],
    '/features/custom-css' => ['keyword' => 'custom CSS'],
    '/features/custom-domain' => ['keyword' => 'custom domain'],
    '/features/custom-fields' => ['keyword' => 'custom fields'],
    '/features/custom-labels' => ['keyword' => 'custom labels'],
    '/features/embed-calendar' => ['keyword' => 'embed event calendar on website', 'match' => ['embed an event calendar']],
    '/features/embed-tickets' => ['keyword' => 'embed tickets'],
    '/features/event-graphics' => ['keyword' => 'event graphics'],
    '/features/fan-videos' => ['keyword' => 'fan photos and videos', 'match' => ['fan photos, videos']],
    '/features/feedback' => ['keyword' => 'post-event feedback'],
    '/features/gift-cards' => ['keyword' => 'sell gift cards'],
    '/features/installments' => ['keyword' => 'installment payments'],
    '/features/integrations' => ['keyword' => 'Event Schedule integrations'],
    '/features/newsletters' => ['keyword' => 'event newsletter builder'],
    '/features/online-events' => ['keyword' => 'online event hosting', 'match' => ['online & hybrid event hosting', 'online and hybrid event hosting']],
    '/features/passes' => ['keyword' => 'class packs and memberships'],
    '/features/polls' => ['keyword' => 'event polls'],
    '/features/private-events' => ['keyword' => 'private events'],
    '/features/promo-codes' => ['keyword' => 'ticket promo codes'],
    '/features/recurring-events' => ['keyword' => 'recurring events'],
    '/features/sub-schedules' => ['keyword' => 'sub-schedules'],
    '/features/team-scheduling' => ['keyword' => 'team scheduling'],
    '/features/ticketing' => ['keyword' => 'event ticketing software'],
    '/features/waitlist' => ['keyword' => 'ticket waitlist'],
    '/features/registration' => ['keyword' => 'free event registration'],
    '/features/booking-requests' => ['keyword' => 'booking request form'],
    '/features/white-label' => ['keyword' => 'remove branding', 'match' => ['remove Event Schedule branding']],

    // Audiences
    '/for-ai-agents' => ['keyword' => 'API for AI agents'],
    '/for-art-galleries' => ['keyword' => 'art gallery calendar'],
    '/for-bars' => ['keyword' => 'event calendar for bars', 'match' => ['event calendars for bars']],
    '/for-breweries-and-wineries' => ['keyword' => 'brewery and winery event calendar'],
    '/for-circus-acrobatics' => ['keyword' => 'event schedule for circus'],
    '/for-comedians' => ['keyword' => 'event schedule for comedians'],
    '/for-comedy-clubs' => ['keyword' => 'comedy club schedule'],
    '/for-community-centers' => ['keyword' => 'community center calendar'],
    '/for-curators' => ['keyword' => 'local events guide'],
    '/for-dance-groups' => ['keyword' => 'dance schedule'],
    '/for-djs' => ['keyword' => 'event schedule for DJs'],
    '/for-farmers-markets' => ['keyword' => 'farmers market calendar'],
    '/for-fitness-and-yoga' => ['keyword' => 'fitness class schedule'],
    '/for-food-trucks-and-vendors' => ['keyword' => 'food truck schedule'],
    '/for-hotels-and-resorts' => ['keyword' => 'guest activity calendar'],
    '/for-libraries' => ['keyword' => 'library program calendar'],
    '/for-churches' => ['keyword' => 'church event calendar'],
    '/for-schools' => ['keyword' => 'school event calendar'],
    '/for-nonprofits' => ['keyword' => 'nonprofit event management'],
    '/for-festivals' => ['keyword' => 'festival schedule'],
    '/for-sports-leagues' => ['keyword' => 'sports league schedule'],
    '/for-museums' => ['keyword' => 'museum event calendar'],
    '/for-meetup-groups' => ['keyword' => 'meetup group events'],
    '/community-event-calendar' => ['keyword' => 'community event calendar'],
    '/wordpress-event-calendar' => ['keyword' => 'WordPress event calendar'],
    '/event-landing-page' => ['keyword' => 'event landing page'],
    '/for-live-concerts' => ['keyword' => 'event schedule for live concerts'],
    '/for-live-qa-sessions' => ['keyword' => 'live Q&A sessions'],
    '/for-magicians' => ['keyword' => 'event schedule for magicians'],
    '/for-music-venues' => ['keyword' => 'music venue calendar'],
    '/for-musicians' => ['keyword' => 'band tour dates page', 'match' => ['tour dates page']],
    '/for-nightclubs' => ['keyword' => 'nightclub event calendar'],
    '/for-online-classes' => ['keyword' => 'event schedule for online classes'],
    '/for-restaurants' => ['keyword' => 'restaurant event ticketing'],
    '/for-spoken-word' => ['keyword' => 'open mic schedule', 'match' => ['open mic and reading schedule']],
    '/for-talent' => ['keyword' => 'gig calendar'],
    '/for-theater-performers' => ['keyword' => 'actor schedule'],
    '/for-theaters' => ['keyword' => 'theater calendar'],
    '/for-venues' => ['keyword' => 'venue calendar software'],
    '/for-virtual-conferences' => ['keyword' => 'virtual conference agenda'],
    '/for-visual-artists' => ['keyword' => 'artist exhibition calendar'],
    '/for-watch-parties' => ['keyword' => 'event schedule for watch parties'],
    '/for-webinars' => ['keyword' => 'event schedule for webinars'],
    '/for-workshop-instructors' => ['keyword' => 'workshop registration'],

    // Competitor alternatives (compare-single.blade.php). The eyebrow reads "{Name} alternative".
    '/eventbrite-alternative' => ['keyword' => 'Eventbrite alternative'],
    '/luma-alternative' => ['keyword' => 'Luma alternative'],
    '/ticket-tailor-alternative' => ['keyword' => 'Ticket Tailor alternative'],
    '/google-calendar-alternative' => ['keyword' => 'Google Calendar alternative'],
    '/meetup-alternative' => ['keyword' => 'Meetup alternative'],
    '/dice-alternative' => ['keyword' => 'DICE alternative'],
    '/brown-paper-tickets-alternative' => ['keyword' => 'Brown Paper Tickets alternative'],
    '/splash-alternative' => ['keyword' => 'Splash alternative'],
    '/sched-alternative' => ['keyword' => 'Sched alternative'],
    '/whova-alternative' => ['keyword' => 'Whova alternative'],
    '/accelevents-alternative' => ['keyword' => 'Accelevents alternative'],
    '/tito-alternative' => ['keyword' => 'Tito alternative'],
    '/addevent-alternative' => ['keyword' => 'AddEvent alternative'],
    '/pretix-alternative' => ['keyword' => 'Pretix alternative'],
    '/humanitix-alternative' => ['keyword' => 'Humanitix alternative'],
    '/eventzilla-alternative' => ['keyword' => 'Eventzilla alternative'],
    '/timely-alternative' => ['keyword' => 'Timely alternative'],
    '/the-events-calendar-alternative' => ['keyword' => 'The Events Calendar alternative'],
    '/bandsintown-alternative' => ['keyword' => 'Bandsintown alternative'],
    '/posh-alternative' => ['keyword' => 'Posh alternative'],
    '/partiful-alternative' => ['keyword' => 'Partiful alternative'],
    '/facebook-events-alternative' => ['keyword' => 'Facebook Events alternative'],
    '/zeffy-alternative' => ['keyword' => 'Zeffy alternative'],
    '/tockify-alternative' => ['keyword' => 'Tockify alternative'],
    '/hi-events-alternative' => ['keyword' => 'Hi.Events alternative'],
    '/mobilizon-alternative' => ['keyword' => 'Mobilizon alternative'],
    '/ticketleap-alternative' => ['keyword' => 'TicketLeap alternative'],
    '/switch-from-eventbrite' => ['keyword' => 'migrate from Eventbrite'],

    // Tool replacements (replace-single.blade.php). The eyebrow reads "{Tool} alternative for
    // events"; the titles say "Replace {Tool} for ...", hence the variant.
    '/calendly-replacement' => ['keyword' => 'Calendly alternative', 'match' => ['replace Calendly']],
    '/canva-replacement' => ['keyword' => 'Canva alternative', 'match' => ['replace Canva']],
    '/doodle-replacement' => ['keyword' => 'Doodle alternative', 'match' => ['replace Doodle']],
    '/google-forms-replacement' => ['keyword' => 'Google Forms alternative', 'match' => ['replace Google Forms']],
    '/google-sheets-replacement' => ['keyword' => 'Google Sheets alternative', 'match' => ['replace Google Sheets']],
    '/linktree-replacement' => ['keyword' => 'Linktree alternative', 'match' => ['replace Linktree']],
    '/mailchimp-replacement' => ['keyword' => 'Mailchimp alternative', 'match' => ['replace Mailchimp']],
    '/notion-replacement' => ['keyword' => 'Notion alternative', 'match' => ['replace Notion']],
    '/qr-code-generator-replacement' => ['keyword' => 'QR code alternative', 'match' => ['replace QR code generators']],
    '/squarespace-replacement' => ['keyword' => 'Squarespace alternative', 'match' => ['replace Squarespace']],
    '/surveymonkey-replacement' => ['keyword' => 'SurveyMonkey alternative', 'match' => ['replace SurveyMonkey']],
    '/trello-replacement' => ['keyword' => 'Trello alternative', 'match' => ['replace Trello']],
];
