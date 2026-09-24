<?php

/*
|--------------------------------------------------------------------------
| Marketing page -> guide map
|--------------------------------------------------------------------------
|
| The docs page a marketing page sends a reader to once they want the how, rendered as "Read the
| guide" beside the heading of that page's related strip (<x-marketing.related-pages />).
|
| Only the pages whose guide is a judgement call are listed here: the audience pages, where one
| venue's guide is its weekly programme and another's is its box office. A feature page needs no
| entry - DocsUtils::guideForPath() falls back to the docs page whose `feature` (config/docs.php)
| names it, which is also what makes that guide link back.
|
| Keyed by URL path, like config/marketing_keywords.php. Values are a docs manifest key with an
| optional #anchor. DocsManifestTest fails the build on a path that is not a marketing page, a key
| the manifest does not know and an anchor the page does not declare.
|
| Scalars only: this file is var_export()ed by `php artisan config:cache`.
|
*/

return [
    // Performers: the job is a page of dates that people find and follow, so the guide is sharing
    // it (links, embeds, feeds, followers).
    '/for-talent' => 'sharing',
    '/for-musicians' => 'sharing',
    '/for-djs' => 'sharing',
    '/for-comedians' => 'sharing',
    '/for-circus-acrobatics' => 'sharing',
    '/for-magicians' => 'sharing',
    '/for-spoken-word' => 'sharing',
    '/for-dance-groups' => 'sharing',
    '/for-theater-performers' => 'sharing',
    '/for-food-trucks-and-vendors' => 'sharing',
    '/for-visual-artists' => 'sharing',

    // Venues and communities that run a weekly programme: the guide is setting a repeat up once.
    '/for-bars' => 'creating-events#recurring',
    '/for-nightclubs' => 'creating-events#recurring',
    '/for-restaurants' => 'creating-events#recurring',
    '/for-breweries-and-wineries' => 'creating-events#recurring',
    '/for-art-galleries' => 'creating-events#recurring',
    '/for-community-centers' => 'creating-events#recurring',
    '/for-farmers-markets' => 'creating-events#recurring',
    '/for-hotels-and-resorts' => 'creating-events#recurring',
    '/for-libraries' => 'creating-events#recurring',
    '/for-churches' => 'creating-events#recurring',
    '/for-museums' => 'creating-events#recurring',
    '/for-sports-leagues' => 'creating-events#recurring',

    // Venues whose programme is sold by the ticket.
    '/for-venues' => 'tickets',
    '/for-music-venues' => 'tickets',
    '/for-theaters' => 'tickets',
    '/for-comedy-clubs' => 'tickets',
    '/for-festivals' => 'tickets',

    // Curating other people's events: sources and the requests that come in.
    '/for-curators' => 'creating-schedules#engagement-requests',
    '/community-event-calendar' => 'creating-schedules#engagement-requests',

    // Free sign-ups first, paid tickets only sometimes.
    '/for-meetup-groups' => 'tickets#registration',
    '/for-nonprofits' => 'tickets#registration',
    '/for-workshop-instructors' => 'tickets#registration',
    '/for-schools' => 'tickets#registration',

    // Class packs and memberships.
    '/for-fitness-and-yoga' => 'subscriptions',

    // Online events: the event form's online link and its settings.
    '/for-webinars' => 'creating-events',
    '/for-online-classes' => 'creating-events',
    '/for-virtual-conferences' => 'creating-events',
    '/for-live-qa-sessions' => 'creating-events',
    '/for-watch-parties' => 'creating-events',
    '/for-live-concerts' => 'creating-events',

    // The page teaches the iframe, so the guide is the embed section.
    '/wordpress-event-calendar' => 'sharing#embed',
];
