<?php

/**
 * Shared marketing audience card data.
 *
 * The same twelve performer audiences are listed on /use-cases (the directory)
 * and on /for-talent (the Talent schedule-type hub). They lived inline in both
 * views, so a blurb or tag edited on one page silently drifted from the other.
 * The twelve venue audiences work the same way across /use-cases and /for-venues.
 *
 * Each entry: url, name, blurb, tags[], icon (raw SVG path markup, rendered
 * inside <x-marketing.audience-card>'s 24x24 stroke icon).
 *
 * Note: /use-cases still holds its own inline copies. As each page is rebuilt it
 * should be pointed here instead. The venue blurbs below are the rewritten,
 * venue-voiced set from the /for-venues rebuild, which is the canonical wording.
 */

return [

    'performers' => [
        [
            'url' => '/for-musicians',
            'name' => 'Musicians',
            'blurb' => 'Share your gigs, sync calendars, and let fans follow your shows.',
            'tags' => ['Solo Artists', 'Rock & Pop Bands', 'Jazz Musicians', 'Cover Bands', 'Tribute Acts', 'Session Musicians'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z" />',
        ],
        [
            'url' => '/for-djs',
            'name' => 'DJs',
            'blurb' => 'Promote your sets, manage bookings, and grow your following.',
            'tags' => ['Resident DJs', 'Touring DJs', 'B2B Partners', 'Underground DJs', 'Open Format DJs', 'Producers'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
        ],
        [
            'url' => '/for-comedians',
            'name' => 'Comedians',
            'blurb' => 'List your shows, sell tickets, and build your comedy brand.',
            'tags' => ['Stand-Up Comics', 'Improv Performers', 'Sketch Comedy Groups', 'Open Mic Regulars', 'Touring Headliners', 'Comedy Hosts & MCs'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />',
        ],
        [
            'url' => '/for-circus-acrobatics',
            'name' => 'Circus & Acrobatics',
            'blurb' => 'Showcase performances and manage tour dates with ease.',
            'tags' => ['Aerialists', 'Circus Troupes', 'Fire Performers', 'Contortionists', 'Jugglers & Prop Artists', 'Stilt Walkers'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />',
        ],
        [
            'url' => '/for-magicians',
            'name' => 'Magicians',
            'blurb' => 'Book more shows and let audiences find your next performance.',
            'tags' => ['Close-Up Magicians', 'Stage Illusionists', 'Mentalists', 'Children\'s Entertainers', 'Corporate Magicians', 'Variety Artists'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />',
        ],
        [
            'url' => '/for-spoken-word',
            'name' => 'Spoken Word',
            'blurb' => 'Share your readings, slams, and open mic nights with your community.',
            'tags' => ['Slam Poets', 'Spoken Word Artists', 'Page Poets', 'Open Mic Hosts', 'Literary Curators', 'Workshop Leaders'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />',
        ],
        [
            'url' => '/for-dance-groups',
            'name' => 'Dance Groups',
            'blurb' => 'Promote performances, classes, and recitals in one place.',
            'tags' => ['Ballet Companies', 'Hip-Hop Crews', 'Ballroom & Latin', 'Contemporary & Modern', 'Folk & Cultural', 'Dance Fitness'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />',
        ],
        [
            'url' => '/for-theater-performers',
            'name' => 'Theater Performers',
            'blurb' => 'Share your productions and auditions with theater fans.',
            'tags' => ['Musical Theater', 'Drama & Straight Plays', 'Community Theater', 'Improv & Sketch', 'Experimental & Fringe', 'Children\'s & Youth'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />',
        ],
        [
            'url' => '/for-food-trucks-and-vendors',
            'name' => 'Food Trucks & Vendors',
            'blurb' => 'Let customers know where to find you every day of the week.',
            'tags' => ['Food Trucks', 'Coffee & Beverage Carts', 'BBQ & Smoker Trucks', 'Catering Businesses', 'Pop-up Kitchens', 'Market Vendors'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />',
        ],
        [
            'url' => '/for-fitness-and-yoga',
            'name' => 'Fitness & Yoga Instructors',
            'blurb' => 'Share your class schedule and let students follow your sessions.',
            'tags' => ['Yoga Teachers', 'Personal Trainers', 'Pilates Instructors', 'CrossFit Coaches', 'Group Fitness', 'Meditation Guides'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />',
        ],
        [
            'url' => '/for-workshop-instructors',
            'name' => 'Workshop Instructors',
            'blurb' => 'List your workshops and courses to fill every seat.',
            'tags' => ['Cooking Classes', 'Pottery & Ceramics', 'Photography', 'Craft & Maker', 'Art Teachers', 'Music Lessons'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />',
        ],
        [
            'url' => '/for-visual-artists',
            'name' => 'Visual Artists',
            'blurb' => 'Announce exhibitions, open studios, and art fairs to collectors.',
            'tags' => ['Painters & Illustrators', 'Sculptors', 'Photographers', 'Printmakers', 'Mixed Media', 'Digital Artists'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />',
        ],
    ],

    'venues' => [
        [
            'url' => '/for-bars',
            'name' => 'Bars & Pubs',
            'blurb' => 'Promote live music nights, trivia, and special events to your regulars.',
            'tags' => ['Craft Beer Bars', 'Wine Bars', 'Sports Bars', 'Cocktail Lounges', 'Irish & British Pubs', 'Dive Bars'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />',
        ],
        [
            'url' => '/for-nightclubs',
            'name' => 'Nightclubs',
            'blurb' => 'Manage DJ lineups, themed nights, and guest list arrivals.',
            'tags' => ['Dance Clubs & EDM', 'Hip-Hop & Urban', 'Latin Clubs', 'Rooftop Clubs', 'Underground & Warehouse', 'VIP Lounges'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />',
        ],
        [
            'url' => '/for-music-venues',
            'name' => 'Music Venues',
            'blurb' => 'Book bands, sell tickets, and run your concert calendar in one place.',
            'tags' => ['Concert Halls', 'Live Music Bars', 'Jazz Clubs', 'Folk & Acoustic', 'Rock & Indie Venues', 'Outdoor Amphitheaters'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />',
        ],
        [
            'url' => '/for-theaters',
            'name' => 'Theaters',
            'blurb' => 'Schedule productions, manage show runs, and sell season tickets.',
            'tags' => ['Community Theaters', 'Regional Theaters', 'Black Box Theaters', 'Dinner Theaters', 'Children\'s Theaters', 'Outdoor Amphitheaters'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />',
        ],
        [
            'url' => '/for-comedy-clubs',
            'name' => 'Comedy Clubs',
            'blurb' => 'Book comedians, host open mics, and fill seats for headliners.',
            'tags' => ['Stand-up Clubs', 'Improv Theaters', 'Open Mic Venues', 'Comedy Bars', 'Sketch Comedy Venues', 'Live Podcast Studios'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        ],
        [
            'url' => '/for-restaurants',
            'name' => 'Restaurants',
            'blurb' => 'Promote wine tastings, chef\'s tables, and live entertainment.',
            'tags' => ['Fine Dining', 'Wine Bars & Tapas', 'Farm-to-Table', 'Supper Clubs', 'Casual Dining & Bistros', 'Chef\'s Tables'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18z" />',
        ],
        [
            'url' => '/for-breweries-and-wineries',
            'name' => 'Breweries & Wineries',
            'blurb' => 'Host tastings, tours, and live music in your taproom.',
            'tags' => ['Craft Breweries', 'Brewpubs & Taprooms', 'Wineries & Vineyards', 'Cideries & Orchards', 'Meaderies & Distilleries', 'Taproom-Only'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />',
        ],
        [
            'url' => '/for-art-galleries',
            'name' => 'Art Galleries',
            'blurb' => 'Announce openings, exhibitions, and artist meet-and-greets.',
            'tags' => ['Contemporary Art', 'Fine Art Studios', 'Photography Galleries', 'Craft & Maker Studios', 'Artist Collectives', 'Pop-up Spaces'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />',
        ],
        [
            'url' => '/for-community-centers',
            'name' => 'Community Centers',
            'blurb' => 'Organize classes, workshops, and community gatherings.',
            'tags' => ['Recreation Centers', 'Senior Centers', 'Youth Centers', 'Cultural Centers', 'Neighborhood Centers', 'Faith-Based Centers'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
        ],
        [
            'url' => '/for-farmers-markets',
            'name' => 'Farmers Markets',
            'blurb' => 'Share your market schedule and build a loyal shopper community.',
            'tags' => ['Weekly Farmers Markets', 'Artisan & Craft Markets', 'Flea Markets', 'Holiday Markets', 'Night Markets', 'Specialty Food Markets'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />',
        ],
        [
            'url' => '/for-hotels-and-resorts',
            'name' => 'Hotels & Resorts',
            'blurb' => 'Elevate the guest experience with activity calendars and events.',
            'tags' => ['Boutique Hotels', 'Beach Resorts', 'Conference Hotels', 'Spa & Wellness', 'Mountain Lodges', 'Casino Hotels'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />',
        ],
        [
            'url' => '/for-libraries',
            'name' => 'Libraries',
            'blurb' => 'Share programs, author events, and community activities with patrons.',
            'tags' => ['Public Libraries', 'University Libraries', 'Community Reading Rooms', 'Children\'s Libraries', 'Archive Centers', 'Mobile Libraries'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />',
        ],
    ],

];
