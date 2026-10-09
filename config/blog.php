<?php

/*
|--------------------------------------------------------------------------
| The blog's editorial settings
|--------------------------------------------------------------------------
|
| Read by App\Services\Blog\BlogWriter when it chooses and writes a post. What the blog may
| say about the product is in config/blog_facts.php; the prompts are in config/ai_prompts.php.
|
*/

return [
    /*
    | Where the daily post looks for its topic, one a day in turn. Each is close to something
    | the product does, so the advice in a post can end in something the reader can do. The
    | old topic prompt was given "event planning, community building, or hosting successful
    | events" and wrote six posts about volunteers and four about sponsors, subjects the
    | product has no feature for.
    */
    'directions' => [
        'pricing and selling tickets for small events: early-bird, tiers, door sales, refunds, free against paid',
        'getting people who signed up to actually turn up: reminders, no-shows, waitlists',
        'classes, workshops and anything that repeats: passes, a recurring series, sign-ups',
        'putting a calendar on a website and keeping it current',
        'building an email list from events and writing to it',
        'venues taking bookings from performers and managing a shared calendar',
        'running the door: check-in, capacity, guest lists, cash on the night',
        'curating a local what\'s-on listing from other people\'s events',
        'online and hybrid events: links, time zones, tickets for a stream',
        'moving off spreadsheets, Facebook events or a platform that takes a cut',
    ],

    /*
    | Subjects the blog has written enough about. `match` is a regular expression tried on every
    | published title; once `full_at` titles match, the planner is told the subject is full.
    */
    'subjects' => [
        'volunteers' => ['match' => 'volunteer', 'full_at' => 2],
        'sponsors' => ['match' => 'sponsor', 'full_at' => 2],
        'accessibility and inclusive events' => ['match' => 'accessib|inclusive', 'full_at' => 2],
        'hybrid events' => ['match' => 'hybrid', 'full_at' => 2],
        'speakers' => ['match' => 'speaker', 'full_at' => 1],
        'what to do after an event' => ['match' => 'post-event|advocates|beyond event|lasting', 'full_at' => 2],
        'event promotion in general' => ['match' => 'promotion|promote', 'full_at' => 4],
        'last-minute changes and cancellations' => ['match' => 'last-minute|cancellation', 'full_at' => 2],
        'registration form design' => ['match' => 'registration form', 'full_at' => 1],
    ],

    /*
    | Header pictures by section (the files of public/images/headers that BlogPost lists). They
    | are a stock set shared by every post, so the writer also avoids the last twelve used.
    */
    'pictures' => [
        'selling-tickets' => ['Lets_do_Business.png', 'Tradeshow_Expo.png', 'Arena.png'],
        'promotion' => ['People_of_the_World.png', 'Network_Summit.png', 'Summer_Events.png'],
        'on-the-day' => ['All_Hands_on_Deck.png', 'Warming_Up.png', 'Arena.png'],
        'calendars' => ['5am_Club.png', 'Chess_Vibrancy.png', 'Synergy.png'],
        'email' => ['Networking_and_Bagels.png', 'Literature.png', 'Synergy.png'],
        'classes' => ['Yoga_and_Wellness.png', 'Peaceful_Studio.png', 'Fitness_Morning.png', 'Meditation.png', 'Mindful.png'],
        'venues' => ['The_Stage_Awaits.png', 'Music_Potential.png', 'Chill_Evening.png'],
        'online' => ['Network_Summit.png', 'Synergy.png', 'Chess_Vibrancy.png'],
        'planning' => ['Lets_do_Business.png', 'Flowerful_Life.png', 'Nature_Calls.png'],
    ],

    /*
    | Seconds one model call may take, and the three calls together. A post took 75 to 95
    | seconds in the trial runs; past the budget the writer stops rather than hold the
    | scheduler any longer.
    */
    'call_timeout' => 100,
    'budget' => 330,
];
