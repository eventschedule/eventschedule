<?php

/**
 * What the /use-cases directory adds to each audience in config/marketing_audiences.php.
 *
 * Keyed by the audience's path. Each entry:
 *
 *   type    the schedule type its own page sends to sign-up (/sign_up?type=...), so pressing
 *           "Start for free" beside an audience opens the form its page would have opened.
 *           UseCasesPageTest reads every audience page and fails if one disagrees.
 *   blurb   (nine venues only) the sentence this page has always printed for that venue, where
 *           it is not the one /for-venues prints from config/marketing_audiences.php.
 *   find    words a visitor might type that are not the name or one of the tags. Lower case.
 *           Never a feature the product does not have: a word here is a promise that the page
 *           it leads to is about that word.
 *   sample  a made-up schedule of that kind, drawn beside the directory: who (name, tagline,
 *           slug) and three dates, in the order they fall. A date is [weekday 1-7 of the coming
 *           week, title, when and where, what it costs or '' for nothing said, pill]. A title
 *           is written to fit one line of the stage (about 24 characters): what does not fit
 *           goes on the line under it. Nothing in a sample names a season, because the dates
 *           are always next week's. The last word says what kind of date it is (tickets, rsvp,
 *           free, few, sold, online, or '' for nothing said); the page turns it into the words
 *           a schedule's own page prints: the price, "Free entry", "Few left", "Sold Out".
 *
 * The same names turn up in more than one sample on purpose (the trio that plays The Blue Note,
 * the guide that lists it): the page below the directory follows that one night.
 *
 * Prices are mock data and deliberately none of the plan prices, which MarketingPriceTest
 * looks for beside the words "plan" and "month".
 */

return [

    // ---- Performers & Artists -------------------------------------------------------------

    '/for-musicians' => [
        'type' => 'talent',
        'find' => ['band', 'bands', 'gig', 'gigs', 'singer', 'songwriter', 'guitarist', 'tour dates', 'orchestra', 'choir', 'rapper', 'duo', 'trio', 'quartet', 'music', 'tribute night'],
        'sample' => [
            'name' => 'The Marlowe Trio', 'tagline' => 'Jazz, three nights a week', 'slug' => 'marlowe-trio',
            'rows' => [
                [5, 'Jazz Night', '8:00 PM · The Blue Note', '$25', 'tickets'],
                [6, 'Late Set', '10:30 PM · Vinyl Room', '$10', 'tickets'],
                [7, 'Brunch Session', '11:00 AM · Cafe Odeon', 'Free', 'free'],
            ],
        ],
    ],
    '/for-djs' => [
        'type' => 'talent',
        'find' => ['dj', 'deejay', 'set times', 'residency', 'club night', 'mixes', 'techno', 'house music', 'turntable'],
        'sample' => [
            'name' => 'DJ Nova', 'tagline' => 'House and disco', 'slug' => 'dj-nova',
            'rows' => [
                [5, 'Residency', '11:00 PM · Vault', '$14', 'tickets'],
                [6, 'B2B with Kairo', '11:30 PM · Warehouse 9', '$20', 'few'],
                [7, 'Rooftop sunset set', '5:00 PM', 'Free', 'free'],
            ],
        ],
    ],
    '/for-comedians' => [
        'type' => 'talent',
        'find' => ['comic', 'comedy', 'stand up', 'standup', 'improv', 'sketch', 'open mic', 'roast'],
        'sample' => [
            'name' => 'Dana Reyes', 'tagline' => 'Stand-up. A new hour in progress', 'slug' => 'dana-reyes',
            'rows' => [
                [2, 'Open mic', '8:00 PM · The Cellar Club', 'Free', 'free'],
                [5, 'Late Laughs', '9:30 PM · The Cellar Club', '$18', 'tickets'],
                [6, 'Work in progress', '7:00 PM · The Parlour', '$10', 'few'],
            ],
        ],
    ],
    '/for-circus-acrobatics' => [
        'type' => 'talent',
        'find' => ['circus', 'acrobat', 'acrobats', 'aerial', 'trapeze', 'juggler', 'fire show', 'stilts', 'troupe', 'big top'],
        'sample' => [
            'name' => 'Cirque Lumina', 'tagline' => 'Aerial and fire, on tour', 'slug' => 'cirque-lumina',
            'rows' => [
                [5, 'Big top, opening night', '7:30 PM · Riverside Fields', '$22', 'tickets'],
                [6, 'Family matinee', '2:00 PM', '$14', 'tickets'],
                [7, 'Aerial workshop', '11:00 AM', '$30', 'few'],
            ],
        ],
    ],
    '/for-magicians' => [
        'type' => 'talent',
        'find' => ['magic', 'magician', 'illusionist', 'mentalist', 'card tricks', 'party entertainer', 'kids entertainer'],
        'sample' => [
            'name' => 'Felix Hart', 'tagline' => 'Close-up and stage magic', 'slug' => 'felix-hart',
            'rows' => [
                [4, 'Parlour residency', '8:00 PM · The Parlour', '$30', 'tickets'],
                [6, 'Family show', '3:00 PM · Town Hall', '$10', 'tickets'],
                [7, 'An hour of card magic', '6:00 PM', '$20', 'sold'],
            ],
        ],
    ],
    '/for-spoken-word' => [
        'type' => 'talent',
        'find' => ['poet', 'poetry', 'poem', 'slam', 'reading', 'readings', 'storyteller', 'storytelling', 'author', 'writer', 'literary'],
        'sample' => [
            'name' => 'Inkwell Open Mic', 'tagline' => 'Poems and stories, first Thursdays', 'slug' => 'inkwell',
            'rows' => [
                [4, 'Open mic', '7:30 PM · list opens at 7', 'Free', 'free'],
                [6, 'Slam final', '8:00 PM', '$8', 'tickets'],
                [7, 'Chapbook launch', '4:00 PM', 'Free', 'rsvp'],
            ],
        ],
    ],
    '/for-dance-groups' => [
        'type' => 'talent',
        'find' => ['dance', 'dancer', 'dancers', 'ballet', 'salsa', 'tango', 'swing', 'ballroom', 'recital', 'choreographer', 'dance studio', 'dance school'],
        'sample' => [
            'name' => 'Meridian Dance Company', 'tagline' => 'Classes, rehearsals and shows', 'slug' => 'meridian-dance',
            'rows' => [
                [1, 'Company class', '6:30 PM', '$14', 'tickets'],
                [3, 'Open rehearsal', '7:00 PM', 'Free', 'rsvp'],
                [6, 'Showcase night', '7:30 PM', '$20', 'few'],
            ],
        ],
    ],
    '/for-theater-performers' => [
        'type' => 'talent',
        'find' => ['actor', 'actress', 'acting', 'drama', 'musical theatre', 'musical theater', 'cabaret', 'audition', 'understudy'],
        'sample' => [
            'name' => 'Sam Okafor', 'tagline' => 'Actor. Stage and musical theater', 'slug' => 'sam-okafor',
            'rows' => [
                [1, 'Cabaret night', '8:00 PM · The Green Room', '$14', 'tickets'],
                [5, 'Twelfth Night', '7:30 PM · Harbor Playhouse', '$28', 'tickets'],
                [6, 'Twelfth Night, matinee', '2:00 PM · Harbor Playhouse', '$22', 'few'],
            ],
        ],
    ],
    '/for-food-trucks-and-vendors' => [
        'type' => 'talent',
        'find' => ['food truck', 'truck', 'vendor', 'vendors', 'catering', 'caterer', 'pop-up', 'popup', 'stall', 'street food', 'coffee cart', 'ice cream van', 'bbq'],
        'sample' => [
            'name' => 'Smoke & Salt BBQ', 'tagline' => 'Wherever the truck is today', 'slug' => 'smoke-and-salt',
            'rows' => [
                [4, 'Market Square', '11:00 AM to 2:00 PM', '', ''],
                [5, 'Brewery Row', '5:00 PM to 9:00 PM', '', ''],
                [6, 'Riverside Farmers Market', '8:00 AM to 1:00 PM', '', ''],
            ],
        ],
    ],
    '/for-fitness-and-yoga' => [
        'type' => 'talent',
        'find' => ['yoga', 'pilates', 'fitness', 'personal trainer', 'bootcamp', 'spin', 'crossfit', 'zumba', 'meditation', 'class timetable', 'studio'],
        'sample' => [
            'name' => 'Stillpoint Yoga', 'tagline' => 'Classes from sunrise to sundown', 'slug' => 'stillpoint',
            'rows' => [
                [1, 'Sunrise flow', '6:30 AM', '$14', 'tickets'],
                [3, 'Community class', '6:00 PM', 'Free', 'rsvp'],
                [5, 'Night Flow', '7:00 PM · candlelit', '$18', 'few'],
            ],
        ],
    ],
    '/for-workshop-instructors' => [
        'type' => 'talent',
        'find' => ['workshop', 'workshops', 'class', 'classes', 'teacher', 'instructor', 'pottery', 'cooking class', 'craft', 'crafts', 'woodworking', 'lessons', 'course', 'tutor', 'sewing', 'painting class'],
        'sample' => [
            'name' => 'Clay & Kiln Studio', 'tagline' => 'One class, ten Saturdays', 'slug' => 'clay-and-kiln',
            'rows' => [
                [2, 'Glazing evening', '6:30 PM', '$35', 'tickets'],
                [6, 'Wheel throwing, week 3', '10:00 AM', '$40', 'few'],
                [7, 'Family clay morning', '11:00 AM', '$18', 'tickets'],
            ],
        ],
    ],
    '/for-visual-artists' => [
        'type' => 'talent',
        'find' => ['artist', 'painter', 'sculptor', 'photographer', 'illustrator', 'exhibition', 'open studio', 'art fair', 'muralist', 'ceramicist'],
        'sample' => [
            'name' => 'Noor Haddad', 'tagline' => 'Painter. Openings and open studios', 'slug' => 'noor-haddad',
            'rows' => [
                [4, 'Artist talk', '7:00 PM · Gallery 41', '$8', 'tickets'],
                [5, 'Opening: Salt Lines', '6:00 PM · Gallery 41', 'Free', 'rsvp'],
                [6, 'Open studio', '12:00 PM', 'Free', 'free'],
            ],
        ],
    ],

    // ---- Venues & Event Spaces ------------------------------------------------------------

    '/for-bars' => [
        'type' => 'venue',
        'blurb' => 'Keep your entertainment calendar fresh and bring in crowds.',
        'find' => ['bar', 'pub', 'tavern', 'quiz', 'quiz night', 'trivia', 'karaoke', 'happy hour', 'live music', 'cocktail bar', 'saloon'],
        'sample' => [
            'name' => 'The Anchor', 'tagline' => 'Something on every night', 'slug' => 'the-anchor',
            'rows' => [
                [2, 'Quiz night', '8:00 PM', 'Free', 'free'],
                [5, 'The Marlowe Trio, live', '9:00 PM', 'Free', 'free'],
                [7, 'Folk session', '1:00 PM · with Sunday roast', 'Free', 'rsvp'],
            ],
        ],
    ],
    '/for-nightclubs' => [
        'type' => 'venue',
        'blurb' => 'Promote DJ lineups, themed nights, and special events.',
        'find' => ['club', 'nightclub', 'night club', 'clubbing', 'guest list', 'dj night', 'rave', 'lounge', 'afterparty', 'dance floor'],
        'sample' => [
            'name' => 'Vault', 'tagline' => 'Fridays and Saturdays until four', 'slug' => 'vault',
            'rows' => [
                [4, 'Student night', '10:00 PM', 'Free', 'rsvp'],
                [5, 'DJ Nova, residency', '11:00 PM', '$14', 'tickets'],
                [6, 'Disco Inferno', '11:00 PM', '$20', 'few'],
            ],
        ],
    ],
    '/for-music-venues' => [
        'type' => 'venue',
        'blurb' => 'Manage concert schedules and sell tickets for every show.',
        'find' => ['venue', 'concert hall', 'live music', 'gig venue', 'jazz club', 'amphitheater', 'listening room', 'stage', 'auditorium'],
        'sample' => [
            'name' => 'The Blue Note', 'tagline' => 'Live jazz, five nights a week', 'slug' => 'blue-note',
            'rows' => [
                [5, 'Jazz Night', '8:00 PM', '$25', 'tickets'],
                [6, 'Open Mic', '7:30 PM', 'Free', 'rsvp'],
                [7, 'Blues & Brews', '9:00 PM', '$18', 'tickets'],
            ],
        ],
    ],
    '/for-theaters' => [
        'type' => 'venue',
        'blurb' => 'Share your season schedule and sell tickets for every production.',
        'find' => ['theater', 'theatre', 'playhouse', 'play', 'plays', 'musical', 'season', 'box office', 'opera house', 'performing arts', 'pantomime'],
        'sample' => [
            'name' => 'Harbor Playhouse', 'tagline' => 'A season of six productions', 'slug' => 'harbor-playhouse',
            'rows' => [
                [5, 'Twelfth Night', '7:30 PM', '$28', 'tickets'],
                [6, 'Twelfth Night, matinee', '2:00 PM', '$22', 'few'],
                [7, 'Backstage tour', '11:00 AM', '$10', 'tickets'],
            ],
        ],
    ],
    '/for-comedy-clubs' => [
        'type' => 'venue',
        'blurb' => 'Fill seats with a lineup calendar your audience will love.',
        'find' => ['comedy club', 'comedy night', 'comedy venue', 'stand up club', 'improv theater', 'improv theatre', 'laughs'],
        'sample' => [
            'name' => 'The Cellar Club', 'tagline' => 'Stand-up every Friday and Saturday', 'slug' => 'cellar-club',
            'rows' => [
                [2, 'New Material Night', '8:00 PM', 'Free', 'rsvp'],
                [5, 'Late Laughs', '9:30 PM', '$18', 'tickets'],
                [6, 'Improv Jam', '9:00 PM', '$10', 'few'],
            ],
        ],
    ],
    '/for-restaurants' => [
        'type' => 'venue',
        'blurb' => 'Promote special dinners, live music nights, and tasting events.',
        'find' => ['restaurant', 'cafe', 'coffee shop', 'bistro', 'supper club', 'tasting menu', 'wine dinner', 'chef', 'brunch', 'diner', 'pizzeria', 'kitchen'],
        'sample' => [
            'name' => 'Olive & Ember', 'tagline' => 'Supper clubs and tasting nights', 'slug' => 'olive-and-ember',
            'rows' => [
                [4, 'Five-course tasting', '7:00 PM', '$60', 'tickets'],
                [6, 'Live jazz dinner', '8:00 PM', 'Free', 'rsvp'],
                [7, 'Pasta class', '3:00 PM · with the chef', '$45', 'sold'],
            ],
        ],
    ],
    '/for-breweries-and-wineries' => [
        'type' => 'venue',
        'blurb' => 'Share tastings, tap takeovers, live music, and seasonal events.',
        'find' => ['brewery', 'winery', 'taproom', 'tap room', 'vineyard', 'cidery', 'distillery', 'tasting room', 'beer', 'wine tasting', 'brewpub', 'tap takeover'],
        'sample' => [
            'name' => 'Copper Kettle Brewing', 'tagline' => 'Taproom open every day', 'slug' => 'copper-kettle',
            'rows' => [
                [3, 'Trivia night', '7:00 PM', 'Free', 'free'],
                [5, 'Tap takeover', '5:00 PM · Hazy Days', 'Free', 'free'],
                [6, 'Tour and tasting', '2:00 PM', '$18', 'tickets'],
            ],
        ],
    ],
    '/for-art-galleries' => [
        'type' => 'venue',
        'blurb' => 'Promote exhibitions, openings, and artist talks to collectors and fans.',
        'find' => ['gallery', 'art gallery', 'exhibition', 'opening', 'vernissage', 'art space', 'private view', 'collectors'],
        'sample' => [
            'name' => 'Gallery 41', 'tagline' => 'A new show every six weeks', 'slug' => 'gallery-41',
            'rows' => [
                [4, 'Artist talk', '7:00 PM · Noor Haddad', '$8', 'tickets'],
                [5, 'Opening: Salt Lines', '6:00 PM', 'Free', 'rsvp'],
                [6, 'Collectors\' preview', '5:00 PM', 'Free', 'few'],
            ],
        ],
    ],
    '/for-community-centers' => [
        'type' => 'venue',
        'blurb' => 'Keep your community informed about classes, meetings, and events.',
        'find' => ['community center', 'community centre', 'rec center', 'recreation', 'village hall', 'hall hire', 'youth club', 'senior center', 'ymca', 'scout hut'],
        'sample' => [
            'name' => 'Eastside Community Center', 'tagline' => 'Classes, clubs and hall hire', 'slug' => 'eastside-center',
            'rows' => [
                [1, 'Coffee morning', '10:00 AM · for seniors', 'Free', 'free'],
                [3, 'Kids\' art club', '4:00 PM · 12 spots', 'Free', 'rsvp'],
                [6, 'Repair cafe', '11:00 AM', 'Free', 'free'],
            ],
        ],
    ],
    '/for-farmers-markets' => [
        'type' => 'venue',
        'find' => ['farmers market', 'market', 'flea market', 'craft fair', 'night market', 'makers market', 'bazaar', 'stallholders', 'car boot'],
        'sample' => [
            'name' => 'Riverside Farmers Market', 'tagline' => 'Every Saturday morning, rain or shine', 'slug' => 'riverside-market',
            'rows' => [
                [3, 'Night market', '5:00 PM to 9:00 PM', '', ''],
                [6, 'Market day', '8:00 AM to 1:00 PM · 42 stalls', '', ''],
                [7, 'Makers\' fair', '10:00 AM', 'Free', 'free'],
            ],
        ],
    ],
    '/for-hotels-and-resorts' => [
        'type' => 'venue',
        'find' => ['hotel', 'resort', 'inn', 'lodge', 'guest activities', 'spa', 'bed and breakfast', 'retreat'],
        'sample' => [
            'name' => 'Saltwater Resort', 'tagline' => 'This week at the resort', 'slug' => 'saltwater',
            'rows' => [
                [1, 'Sunrise yoga', '7:00 AM · on the deck', 'Free', 'rsvp'],
                [3, 'Wine tasting', '6:00 PM · in the cellar', '$30', 'few'],
                [5, 'Live music', '8:00 PM · by the pool', 'Free', 'free'],
            ],
        ],
    ],
    '/for-libraries' => [
        'type' => 'venue',
        'find' => ['library', 'story time', 'storytime', 'book club', 'author talk', 'reading group', 'archive'],
        'sample' => [
            'name' => 'Maple Street Library', 'tagline' => 'Programs for every age', 'slug' => 'maple-street-library',
            'rows' => [
                [2, 'Story time', '10:30 AM', 'Free', 'free'],
                [4, 'Author talk', '6:30 PM · Ines Calder', 'Free', 'rsvp'],
                [6, 'Teen coding club', '2:00 PM · 8 spots', 'Free', 'few'],
            ],
        ],
    ],

    // ---- Curators & Promoters -------------------------------------------------------------

    '/for-curators' => [
        'type' => 'curator',
        'find' => ['curator', 'promoter', 'blogger', 'event guide', 'listings', 'scene', 'city guide', 'whats on', 'local media', 'tourism board', 'aggregator', 'magazine', 'newspaper', 'radio station'],
        'sample' => [
            'name' => 'Eastside Tonight', 'tagline' => 'What\'s on in the East End', 'slug' => 'eastside-tonight',
            'rows' => [
                [5, 'Night Flow', '7:00 PM · Stillpoint Yoga', '$18', 'few'],
                [5, 'Jazz Night', '8:00 PM · The Blue Note', '$25', 'tickets'],
                [5, 'Late Laughs', '9:30 PM · The Cellar Club', '$18', 'tickets'],
            ],
        ],
    ],

    // ---- Online Events --------------------------------------------------------------------

    '/for-webinars' => [
        'type' => 'talent',
        'find' => ['webinar', 'webinars', 'zoom', 'online event', 'virtual event', 'live session', 'product demo', 'training session', 'lecture'],
        'sample' => [
            'name' => 'The Pricing Desk', 'tagline' => 'Live sessions on pricing, every week', 'slug' => 'pricing-desk',
            'rows' => [
                [2, 'Teardown: 3 pricing pages', '1:00 PM', 'Free', 'online'],
                [3, 'Panel: what we got wrong', '11:00 AM', 'Free', 'online'],
                [4, 'Workshop: your first tiers', '12:00 PM', '$20', 'online'],
            ],
        ],
    ],
    '/for-online-classes' => [
        'type' => 'talent',
        'find' => ['online class', 'online course', 'online lessons', 'tutoring', 'teach online', 'cohort', 'e-learning', 'remote class', 'language lessons'],
        'sample' => [
            'name' => 'Lingua Live', 'tagline' => 'Spanish in twelve live lessons', 'slug' => 'lingua-live',
            'rows' => [
                [1, 'Lesson 4: ordering food', '6:00 PM', '$14', 'online'],
                [3, 'Lesson 5: asking the way', '6:00 PM', '$14', 'online'],
                [6, 'Conversation hour', '10:00 AM', 'Free', 'online'],
            ],
        ],
    ],
    '/for-virtual-conferences' => [
        'type' => 'talent',
        'find' => ['conference', 'summit', 'virtual conference', 'agenda', 'speakers', 'hybrid event', 'all-hands', 'symposium', 'convention', 'unconference'],
        'sample' => [
            'name' => 'DevNorth Online', 'tagline' => 'Two days, one ticket', 'slug' => 'devnorth',
            'rows' => [
                [4, 'Day one: keynotes', '9:00 AM', '$60', 'online'],
                [5, 'Day two: workshops', '9:00 AM', '$60', 'online'],
                [5, 'Closing fireside', '4:30 PM', 'Free', 'online'],
            ],
        ],
    ],
    '/for-live-qa-sessions' => [
        'type' => 'talent',
        'find' => ['q&a', 'q and a', 'qa', 'ama', 'ask me anything', 'town hall', 'fireside', 'office hours', 'panel'],
        'sample' => [
            'name' => 'Ask the Founder', 'tagline' => 'A live Q&A every other Thursday', 'slug' => 'ask-the-founder',
            'rows' => [
                [2, 'Town hall', '3:00 PM', 'Free', 'online'],
                [4, 'AMA: your first hires', '5:00 PM', 'Free', 'online'],
                [5, 'Office hours', '12:00 PM', 'Free', 'online'],
            ],
        ],
    ],
    '/for-watch-parties' => [
        'type' => 'talent',
        'find' => ['watch party', 'movie night', 'screening', 'film club', 'premiere', 'watch along', 'viewing party', 'cinema club', 'game night stream'],
        'sample' => [
            'name' => 'Reel Club', 'tagline' => 'We press play together', 'slug' => 'reel-club',
            'rows' => [
                [5, 'Movie night: Casablanca', '8:00 PM', 'Free', 'online'],
                [6, 'Derby day watch party', '3:00 PM', '$8', 'online'],
                [7, 'Finale watch-along', '9:00 PM', 'Free', 'online'],
            ],
        ],
    ],
    '/for-live-concerts' => [
        'type' => 'talent',
        'find' => ['concert', 'concerts', 'livestream', 'live stream', 'hybrid show', 'streamed gig', 'album release', 'festival stream', 'tour'],
        'sample' => [
            'name' => 'Northern Lights Tour', 'tagline' => 'In the room and on the stream', 'slug' => 'northern-lights',
            'rows' => [
                [5, 'Live from The Blue Note', '8:00 PM', '$25', 'tickets'],
                [6, 'Acoustic set', '7:00 PM · streamed', '$10', 'online'],
                [7, 'Album release show', '8:00 PM', '$30', 'few'],
            ],
        ],
    ],

    // ---- Organizations & Communities ------------------------------------------------------

    '/for-churches' => [
        'type' => 'venue',
        'find' => ['church', 'parish', 'chapel', 'synagogue', 'mosque', 'temple', 'congregation', 'ministry', 'bible study', 'worship', 'sunday service', 'faith', 'sunday school'],
        'sample' => [
            'name' => 'Grace Street Church', 'tagline' => 'Services, groups and sign-ups', 'slug' => 'grace-street',
            'rows' => [
                [3, 'Youth group', '6:30 PM', 'Free', 'rsvp'],
                [6, 'Food drive', '9:00 AM · 40 spots', 'Free', 'few'],
                [7, 'Sunday service', '10:00 AM', '', ''],
            ],
        ],
    ],
    '/for-schools' => [
        'type' => 'venue',
        'find' => ['school', 'pta', 'college', 'university', 'campus', 'parents evening', 'parent evening', 'term dates', 'academy', 'kindergarten', 'students', 'nursery', 'student union'],
        'sample' => [
            'name' => 'Riverside School', 'tagline' => 'Term dates, shows and parent nights', 'slug' => 'riverside-school',
            'rows' => [
                [2, 'Parent night, 6th grade', '5:00 PM', 'Free', 'rsvp'],
                [4, 'School concert', '6:30 PM', '$8', 'tickets'],
                [5, 'Field day', '9:30 AM', '', ''],
            ],
        ],
    ],
    '/for-nonprofits' => [
        'type' => 'curator',
        'find' => ['nonprofit', 'non-profit', 'charity', 'fundraiser', 'fundraising', 'gala', 'volunteer', 'volunteers', 'ngo', 'foundation', 'cause', 'benefit'],
        'sample' => [
            'name' => 'Harbor Relief', 'tagline' => 'Galas, volunteer days and campaigns', 'slug' => 'harbor-relief',
            'rows' => [
                [4, 'Volunteer induction', '6:00 PM', 'Free', 'rsvp'],
                [5, 'Annual gala', '7:00 PM', '$45', 'tickets'],
                [6, 'Beach clean-up', '9:00 AM · 60 spots', 'Free', 'few'],
            ],
        ],
    ],
    '/for-festivals' => [
        'type' => 'curator',
        'find' => ['festival', 'fest', 'fair', 'fringe', 'carnival', 'street party', 'weekend pass', 'lineup', 'stages', 'film festival', 'food festival'],
        'sample' => [
            'name' => 'Riverside Fest', 'tagline' => 'Music, food and color by the river', 'slug' => 'riverside-fest',
            'rows' => [
                [5, 'Gates open', '6:00 PM', '$35', 'tickets'],
                [6, 'Family Day', '11:00 AM', '$10', 'tickets'],
                [7, 'Closing Party', '9:00 PM', '$25', 'few'],
            ],
        ],
    ],
    '/for-sports-leagues' => [
        'type' => 'curator',
        'find' => ['league', 'sports', 'sport', 'team', 'teams', 'fixtures', 'soccer', 'football', 'basketball', 'baseball', 'hockey', 'cricket', 'netball', 'tournament', 'match', 'matches', 'running club', 'cycling club'],
        'sample' => [
            'name' => 'Eastside Sunday League', 'tagline' => 'Fixtures, teams and season passes', 'slug' => 'eastside-league',
            'rows' => [
                [3, 'Cup semi-final', '7:30 PM', '$8', 'tickets'],
                [6, 'Rovers v United', '2:00 PM', '', ''],
                [7, 'Harriers v Colts', '10:00 AM · under-12s', '', ''],
            ],
        ],
    ],
    '/for-museums' => [
        'type' => 'venue',
        'find' => ['museum', 'heritage', 'science center', 'science centre', 'guided tour', 'historic house', 'planetarium', 'gallery talk'],
        'sample' => [
            'name' => 'Tidewater Museum', 'tagline' => 'Tours, talks and family days', 'slug' => 'tidewater-museum',
            'rows' => [
                [4, 'Late opening', '6:30 PM · with a talk', '$14', 'tickets'],
                [6, 'Curator\'s tour', '11:00 AM', '$10', 'few'],
                [7, 'Family day', '1:00 PM · build a boat', 'Free', 'rsvp'],
            ],
        ],
    ],
    '/for-meetup-groups' => [
        'type' => 'curator',
        'find' => ['meetup', 'meet-up', 'club', 'group', 'society', 'hiking group', 'book club', 'board games', 'networking', 'user group', 'hobby', 'social club', 'supper group'],
        'sample' => [
            'name' => 'Eastside Board Gamers', 'tagline' => 'Every other Tuesday, all welcome', 'slug' => 'eastside-gamers',
            'rows' => [
                [2, 'Games night', '7:00 PM · 24 chairs', 'Free', 'rsvp'],
                [4, 'Language exchange', '6:30 PM', 'Free', 'rsvp'],
                [6, 'Sunrise hike', '7:30 AM', 'Free', 'few'],
            ],
        ],
    ],
    '/community-event-calendar' => [
        'type' => 'curator',
        'find' => ['town', 'city', 'council', 'neighbourhood', 'neighborhood', 'village', 'chamber of commerce', 'tourism', 'local events', 'community calendar', 'whats on', 'parish council', 'downtown'],
        'sample' => [
            'name' => 'Riverside Community Calendar', 'tagline' => 'What\'s on in Riverside', 'slug' => 'riverside',
            'rows' => [
                [5, 'Jazz Night', '8:00 PM · The Blue Note', '$25', 'tickets'],
                [6, 'Market day', '8:00 AM · Farmers Market', '', ''],
                [7, 'Sunday service', '10:00 AM · Grace Street', '', ''],
            ],
        ],
    ],

    // ---- Developers & AI Agents -----------------------------------------------------------

    '/for-ai-agents' => [
        'type' => null,
        'find' => ['api', 'developer', 'developers', 'webhook', 'webhooks', 'agent', 'agents', 'ai', 'llm', 'openapi', 'integration', 'automation', 'json', 'rest', 'code', 'script'],
        'sample' => null,
    ],
];
