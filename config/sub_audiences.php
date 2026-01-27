<?php

/**
 * Sub-audience configuration for marketing pages
 *
 * Each main audience page has a "Perfect For" section with 6 sub-audiences.
 * Each sub-audience can have an associated blog post (matched by slug).
 *
 * Structure:
 * - page: The marketing page route (e.g., 'for-musicians')
 * - title: Human-readable title for the audience
 * - sub_audiences: Array of sub-audiences with:
 *   - name: Display name for the card
 *   - slug: Blog post slug (e.g., 'for-solo-artists')
 *   - description: Card description text
 *   - blog_topic: Topic for AI blog generation
 *   - icon_color: Tailwind color for the card (cyan, teal, indigo, etc.)
 */

return [
    'musicians' => [
        'page' => 'for-musicians',
        'title' => 'Musicians',
        'sub_audiences' => [
            'solo-artists' => [
                'name' => 'Solo Artists',
                'slug' => 'for-solo-artists',
                'description' => 'Share your acoustic nights, open mics, and solo performances with your growing fanbase.',
                'blog_topic' => 'How solo artists can build a dedicated fanbase using event scheduling and direct fan communication',
                'icon_color' => 'cyan',
            ],
            'rock-pop-bands' => [
                'name' => 'Rock & Pop Bands',
                'slug' => 'for-rock-pop-bands',
                'description' => 'Coordinate your tour dates across the whole band and let fans follow along.',
                'blog_topic' => 'Tour management tips for rock and pop bands to fill more venues',
                'icon_color' => 'teal',
            ],
            'jazz-musicians' => [
                'name' => 'Jazz Musicians',
                'slug' => 'for-jazz-musicians',
                'description' => 'List your residencies, jam sessions, and special performances at clubs and festivals.',
                'blog_topic' => 'How jazz musicians can promote residencies and jam sessions to build loyal audiences',
                'icon_color' => 'indigo',
            ],
            'cover-bands' => [
                'name' => 'Cover Bands',
                'slug' => 'for-cover-bands',
                'description' => 'Show your weekly bar gigs and private events all in one professional calendar.',
                'blog_topic' => 'Marketing strategies for cover bands to book more gigs and build repeat customers',
                'icon_color' => 'violet',
            ],
            'tribute-acts' => [
                'name' => 'Tribute Acts',
                'slug' => 'for-tribute-acts',
                'description' => 'Build a dedicated fanbase for your tribute shows and special themed events.',
                'blog_topic' => 'How tribute acts can build dedicated fanbases and command premium booking fees',
                'icon_color' => 'purple',
            ],
            'session-musicians' => [
                'name' => 'Session Musicians',
                'slug' => 'for-session-musicians',
                'description' => 'Show your availability and let bands know when you\'re free for gigs and recording sessions.',
                'blog_topic' => 'Using event scheduling to manage session musician availability and attract more bookings',
                'icon_color' => 'amber',
            ],
        ],
    ],

    'bars' => [
        'page' => 'for-bars',
        'title' => 'Bars & Pubs',
        'sub_audiences' => [
            'craft-beer-bars' => [
                'name' => 'Craft Beer Bars',
                'slug' => 'for-craft-beer-bars',
                'description' => 'Tap takeovers, brewery events, and beer release parties. Build a following of craft beer enthusiasts.',
                'blog_topic' => 'How craft beer bars can use events to build loyal regulars and attract beer enthusiasts',
                'icon_color' => 'amber',
            ],
            'wine-bars' => [
                'name' => 'Wine Bars',
                'slug' => 'for-wine-bars',
                'description' => 'Wine tastings, vineyard dinners, and sommelier events. Educate and delight your wine-loving guests.',
                'blog_topic' => 'Event strategies for wine bars to attract oenophiles and increase tasting attendance',
                'icon_color' => 'rose',
            ],
            'sports-bars' => [
                'name' => 'Sports Bars',
                'slug' => 'for-sports-bars',
                'description' => 'Game day watch parties, trivia nights, and UFC events. Let fans know what\'s on the big screen.',
                'blog_topic' => 'How sports bars can maximize attendance for game days and watch parties',
                'icon_color' => 'green',
            ],
            'cocktail-lounges' => [
                'name' => 'Cocktail Lounges',
                'slug' => 'for-cocktail-lounges',
                'description' => 'Mixology classes, speakeasy nights, and cocktail competitions. Attract the craft cocktail crowd.',
                'blog_topic' => 'Building a sophisticated clientele for cocktail lounges through curated events',
                'icon_color' => 'violet',
            ],
            'irish-british-pubs' => [
                'name' => 'Irish & British Pubs',
                'slug' => 'for-irish-british-pubs',
                'description' => 'Pub quizzes, live traditional music, and St. Patrick\'s Day celebrations. Keep the craic alive.',
                'blog_topic' => 'Traditional pub events that build community and keep regulars coming back',
                'icon_color' => 'emerald',
            ],
            'dive-bars' => [
                'name' => 'Dive Bars & Neighborhood Bars',
                'slug' => 'for-dive-bars',
                'description' => 'Open mics, karaoke nights, and local band showcases. Your neighborhood\'s living room.',
                'blog_topic' => 'How neighborhood dive bars can become community hubs through authentic local events',
                'icon_color' => 'slate',
            ],
        ],
    ],

    'restaurants' => [
        'page' => 'for-restaurants',
        'title' => 'Restaurants',
        'sub_audiences' => [
            'fine-dining' => [
                'name' => 'Fine Dining',
                'slug' => 'for-fine-dining-restaurants',
                'description' => 'Wine pairing dinners, chef\'s table experiences, and exclusive tasting menus. Create memorable culinary events.',
                'blog_topic' => 'How fine dining restaurants can fill seats with exclusive culinary experiences and events',
                'icon_color' => 'rose',
            ],
            'wine-bars-tapas' => [
                'name' => 'Wine Bars & Tapas',
                'slug' => 'for-wine-bars-tapas',
                'description' => 'Wine flights, small plates pairings, and sommelier-led tastings. Perfect for social dining experiences.',
                'blog_topic' => 'Creating engaging wine and tapas events that encourage group dining',
                'icon_color' => 'purple',
            ],
            'farm-to-table' => [
                'name' => 'Farm-to-Table',
                'slug' => 'for-farm-to-table-restaurants',
                'description' => 'Harvest dinners, farmer meet-and-greets, and seasonal menu launches. Connect diners with your sources.',
                'blog_topic' => 'How farm-to-table restaurants can use events to tell their sourcing story',
                'icon_color' => 'emerald',
            ],
            'supper-clubs' => [
                'name' => 'Supper Clubs',
                'slug' => 'for-supper-clubs',
                'description' => 'Intimate dinner parties, themed evenings, and exclusive member events. Build your dining community.',
                'blog_topic' => 'Growing a loyal membership base for supper clubs through exclusive events',
                'icon_color' => 'amber',
            ],
            'casual-dining' => [
                'name' => 'Casual Dining',
                'slug' => 'for-casual-dining-restaurants',
                'description' => 'Happy hours, kids eat free nights, and live entertainment. Drive traffic during slower periods.',
                'blog_topic' => 'Event strategies for casual restaurants to boost weekday and off-peak traffic',
                'icon_color' => 'orange',
            ],
            'chefs-tables' => [
                'name' => 'Chef\'s Tables & Pop-ups',
                'slug' => 'for-chefs-tables',
                'description' => 'Limited seating experiences, guest chef collaborations, and popup events. Create exclusivity and buzz.',
                'blog_topic' => 'Marketing chef\'s table experiences and popup dinners to build culinary reputation',
                'icon_color' => 'teal',
            ],
        ],
    ],

    'nightclubs' => [
        'page' => 'for-nightclubs',
        'title' => 'Nightclubs',
        'sub_audiences' => [
            'dance-clubs-edm' => [
                'name' => 'Dance Clubs & EDM Venues',
                'slug' => 'for-dance-clubs-edm',
                'description' => 'Resident DJs, headliner nights, and themed parties. Build your electronic music community.',
                'blog_topic' => 'How EDM venues can build loyal followings and sell out headliner nights',
                'icon_color' => 'fuchsia',
            ],
            'hip-hop-urban' => [
                'name' => 'Hip-Hop & Urban Clubs',
                'slug' => 'for-hip-hop-clubs',
                'description' => 'Artist appearances, mixtape release parties, and culture nights. Connect with the urban scene.',
                'blog_topic' => 'Promoting hip-hop club nights and artist appearances to build authentic audiences',
                'icon_color' => 'violet',
            ],
            'latin-clubs' => [
                'name' => 'Latin Clubs',
                'slug' => 'for-latin-clubs',
                'description' => 'Salsa nights, reggaeton parties, and live Latin bands. Bring the energy of Latin music to your dance floor.',
                'blog_topic' => 'Building vibrant Latin club nights that celebrate culture and community',
                'icon_color' => 'orange',
            ],
            'rooftop-clubs' => [
                'name' => 'Rooftop & Day Clubs',
                'slug' => 'for-rooftop-clubs',
                'description' => 'Pool parties, sunset sessions, and brunch beats. Maximize your daytime and scenic venue.',
                'blog_topic' => 'Maximizing rooftop and day club events for seasonal success',
                'icon_color' => 'cyan',
            ],
            'underground-warehouse' => [
                'name' => 'Underground & Warehouse',
                'slug' => 'for-underground-clubs',
                'description' => 'After-hours, techno nights, and secret parties. Curate your exclusive underground community.',
                'blog_topic' => 'Building and maintaining an exclusive underground club scene',
                'icon_color' => 'slate',
            ],
            'vip-lounges' => [
                'name' => 'VIP Lounges',
                'slug' => 'for-vip-lounges',
                'description' => 'Bottle service, celebrity appearances, and exclusive events. Cater to your high-end clientele.',
                'blog_topic' => 'Event strategies for VIP lounges to attract premium clientele',
                'icon_color' => 'amber',
            ],
        ],
    ],

    'djs' => [
        'page' => 'for-djs',
        'title' => 'DJs',
        'sub_audiences' => [
            'resident-djs' => [
                'name' => 'Resident DJs',
                'slug' => 'for-resident-djs',
                'description' => 'Track your weekly slots and build loyal locals who know exactly where to find you.',
                'blog_topic' => 'How resident DJs can build loyal local followings and secure better residencies',
                'icon_color' => 'indigo',
            ],
            'touring-djs' => [
                'name' => 'Touring DJs',
                'slug' => 'for-touring-djs',
                'description' => 'Share your international dates with fans worldwide. They\'ll know when you\'re in their city.',
                'blog_topic' => 'Tour management and fan engagement strategies for touring DJs',
                'icon_color' => 'purple',
            ],
            'b2b-partners' => [
                'name' => 'B2B Partners',
                'slug' => 'for-b2b-djs',
                'description' => 'Show joint sets and collaborations. Both schedules stay synced automatically.',
                'blog_topic' => 'Coordinating B2B DJ sets and collaborative performances effectively',
                'icon_color' => 'fuchsia',
            ],
            'underground-djs' => [
                'name' => 'Underground DJs',
                'slug' => 'for-underground-djs',
                'description' => 'Warehouse parties, afters, secret locations. Share with your inner circle only.',
                'blog_topic' => 'Building and maintaining an underground DJ following while protecting exclusivity',
                'icon_color' => 'violet',
            ],
            'open-format-djs' => [
                'name' => 'Open Format DJs',
                'slug' => 'for-open-format-djs',
                'description' => 'Weddings, corporate gigs, private events. Keep your public and private bookings organized.',
                'blog_topic' => 'Managing diverse bookings for open format DJs from weddings to corporate events',
                'icon_color' => 'pink',
            ],
            'producers' => [
                'name' => 'Producers',
                'slug' => 'for-dj-producers',
                'description' => 'Live sets, album launches, listening parties. Show fans where to hear your music live.',
                'blog_topic' => 'Promoting live sets and album launches for DJ-producers',
                'icon_color' => 'amber',
            ],
        ],
    ],

    'comedians' => [
        'page' => 'for-comedians',
        'title' => 'Comedians',
        'sub_audiences' => [
            'stand-up-comics' => [
                'name' => 'Stand-Up Comics',
                'slug' => 'for-stand-up-comics',
                'description' => 'Share your sets and build a following. One link shows fans everywhere you\'re performing.',
                'blog_topic' => 'How stand-up comedians can build a following and fill more seats at shows',
                'icon_color' => 'rose',
            ],
            'improv-performers' => [
                'name' => 'Improv Performers',
                'slug' => 'for-improv-performers',
                'description' => 'Promote weekly shows with your troupe. Coordinate Harold nights and jam sessions.',
                'blog_topic' => 'Building audiences for improv troupes and coordinating ensemble schedules',
                'icon_color' => 'pink',
            ],
            'sketch-comedy' => [
                'name' => 'Sketch Comedy Groups',
                'slug' => 'for-sketch-comedy-groups',
                'description' => 'Coordinate ensemble schedules and share show runs. Everyone knows when the next performance is.',
                'blog_topic' => 'Marketing sketch comedy shows and managing ensemble performance schedules',
                'icon_color' => 'fuchsia',
            ],
            'open-mic-regulars' => [
                'name' => 'Open Mic Regulars',
                'slug' => 'for-open-mic-comics',
                'description' => 'Track spots across multiple venues. Never double-book a mic night again.',
                'blog_topic' => 'How aspiring comics can use scheduling to maximize open mic opportunities',
                'icon_color' => 'purple',
            ],
            'touring-headliners' => [
                'name' => 'Touring Headliners',
                'slug' => 'for-touring-comedians',
                'description' => 'National tours, theater shows, and festival appearances. One link for all your dates.',
                'blog_topic' => 'Tour management and fan engagement for touring comedy headliners',
                'icon_color' => 'violet',
            ],
            'comedy-podcasters' => [
                'name' => 'Comedy Podcasters',
                'slug' => 'for-comedy-podcasters',
                'description' => 'Live podcast recordings, meet-and-greets, and listener events. Connect with your audience IRL.',
                'blog_topic' => 'Turning comedy podcast listeners into live event attendees',
                'icon_color' => 'amber',
            ],
        ],
    ],

    'music-venues' => [
        'page' => 'for-music-venues',
        'title' => 'Music Venues',
        'sub_audiences' => [
            'small-clubs' => [
                'name' => 'Small Clubs & Listening Rooms',
                'slug' => 'for-small-music-clubs',
                'description' => 'Intimate shows, singer-songwriter nights, and emerging artist showcases. Curate your sound.',
                'blog_topic' => 'How small music venues can build reputation through curated programming',
                'icon_color' => 'cyan',
            ],
            'mid-size-venues' => [
                'name' => 'Mid-Size Venues',
                'slug' => 'for-mid-size-music-venues',
                'description' => 'Regional tours, local headliners, and multi-band bills. Fill your room with diverse programming.',
                'blog_topic' => 'Programming strategies for mid-size music venues to maximize attendance',
                'icon_color' => 'teal',
            ],
            'theaters-concert-halls' => [
                'name' => 'Theaters & Concert Halls',
                'slug' => 'for-concert-halls',
                'description' => 'National acts, seated shows, and premium experiences. Manage high-demand events professionally.',
                'blog_topic' => 'Event management best practices for theaters and concert halls',
                'icon_color' => 'indigo',
            ],
            'outdoor-amphitheaters' => [
                'name' => 'Outdoor Amphitheaters',
                'slug' => 'for-outdoor-amphitheaters',
                'description' => 'Summer concert series, festivals, and lawn shows. Maximize your seasonal programming.',
                'blog_topic' => 'Seasonal programming and weather contingency planning for outdoor venues',
                'icon_color' => 'emerald',
            ],
            'house-concerts' => [
                'name' => 'House Concerts',
                'slug' => 'for-house-concerts',
                'description' => 'Intimate living room shows, private performances, and exclusive gatherings. Create magic in small spaces.',
                'blog_topic' => 'Hosting successful house concerts and building an intimate music community',
                'icon_color' => 'amber',
            ],
            'multi-purpose-venues' => [
                'name' => 'Multi-Purpose Venues',
                'slug' => 'for-multi-purpose-venues',
                'description' => 'Concerts, comedy, theater, and private events. One calendar for all your programming.',
                'blog_topic' => 'Managing diverse programming for multi-purpose entertainment venues',
                'icon_color' => 'violet',
            ],
        ],
    ],

    'theaters' => [
        'page' => 'for-theaters',
        'title' => 'Theaters',
        'sub_audiences' => [
            'community-theaters' => [
                'name' => 'Community Theaters',
                'slug' => 'for-community-theaters',
                'description' => 'Local productions, volunteer casts, and neighborhood performances. Bring your community together.',
                'blog_topic' => 'Building engaged audiences for community theater productions',
                'icon_color' => 'rose',
            ],
            'regional-theaters' => [
                'name' => 'Regional Theaters',
                'slug' => 'for-regional-theaters',
                'description' => 'Professional productions, season subscriptions, and touring shows. Grow your subscriber base.',
                'blog_topic' => 'Subscription and single-ticket strategies for regional theaters',
                'icon_color' => 'purple',
            ],
            'black-box-theaters' => [
                'name' => 'Black Box Theaters',
                'slug' => 'for-black-box-theaters',
                'description' => 'Experimental work, new plays, and intimate staging. Push boundaries with your programming.',
                'blog_topic' => 'Marketing experimental theater and building audiences for new works',
                'icon_color' => 'slate',
            ],
            'dinner-theaters' => [
                'name' => 'Dinner Theaters',
                'slug' => 'for-dinner-theaters',
                'description' => 'Meal and show packages, murder mysteries, and interactive experiences. Combine dining with drama.',
                'blog_topic' => 'Creating memorable dinner theater experiences that drive repeat attendance',
                'icon_color' => 'amber',
            ],
            'childrens-theaters' => [
                'name' => 'Children\'s Theaters',
                'slug' => 'for-childrens-theaters',
                'description' => 'Family shows, school field trips, and educational programming. Inspire the next generation.',
                'blog_topic' => 'Programming and marketing children\'s theater for families and schools',
                'icon_color' => 'cyan',
            ],
            'outdoor-theaters' => [
                'name' => 'Outdoor & Shakespeare Theaters',
                'slug' => 'for-outdoor-theaters',
                'description' => 'Summer seasons, picnic performances, and classic works. Make the most of your open-air stage.',
                'blog_topic' => 'Seasonal programming strategies for outdoor and Shakespeare theaters',
                'icon_color' => 'emerald',
            ],
        ],
    ],

    'comedy-clubs' => [
        'page' => 'for-comedy-clubs',
        'title' => 'Comedy Clubs',
        'sub_audiences' => [
            'showcase-rooms' => [
                'name' => 'Showcase Rooms',
                'slug' => 'for-stand-up-comedy-clubs',
                'description' => 'Multi-comic lineups, variety shows, and talent development. Curate your comedy roster.',
                'blog_topic' => 'Building compelling showcase lineups that keep comedy audiences coming back',
                'icon_color' => 'rose',
            ],
            'sketch-comedy-venues' => [
                'name' => 'Sketch Comedy Venues',
                'slug' => 'for-sketch-comedy-venues',
                'description' => 'Revues, variety shows, and ensemble performances. Where sketch troupes shine.',
                'blog_topic' => 'Building audiences for sketch comedy venues and variety shows',
                'icon_color' => 'emerald',
            ],
            'improv-theaters' => [
                'name' => 'Improv Theaters',
                'slug' => 'for-improv-theaters',
                'description' => 'House teams, student shows, and improv jams. Build your improv community.',
                'blog_topic' => 'Growing improv theater audiences and developing house team followings',
                'icon_color' => 'pink',
            ],
            'open-mic-venues' => [
                'name' => 'Open Mic Venues',
                'slug' => 'for-open-mic-comedy-venues',
                'description' => 'Weekly mics, bringer shows, and new talent nights. Discover tomorrow\'s headliners.',
                'blog_topic' => 'Running successful comedy open mics that develop talent and draw crowds',
                'icon_color' => 'purple',
            ],
            'live-podcast-studios' => [
                'name' => 'Live Podcast Studios',
                'slug' => 'for-live-podcast-studios',
                'description' => 'Live recordings and audience participation shows. Bring podcasts to life.',
                'blog_topic' => 'Building audiences for live podcast recordings and studio events',
                'icon_color' => 'purple',
            ],
            'comedy-dinner-clubs' => [
                'name' => 'Comedy Dinner Clubs',
                'slug' => 'for-comedy-bars-restaurants',
                'description' => 'Food, drinks, and laughs. Full evening packages that maximize your revenue.',
                'blog_topic' => 'Creating profitable dinner-and-show packages for comedy clubs',
                'icon_color' => 'amber',
            ],
        ],
    ],

    'breweries-wineries' => [
        'page' => 'for-breweries-and-wineries',
        'title' => 'Breweries & Wineries',
        'sub_audiences' => [
            'craft-breweries' => [
                'name' => 'Craft Breweries',
                'slug' => 'for-craft-breweries',
                'description' => 'Taproom events, release parties, and brewery tours. Build your craft beer community.',
                'blog_topic' => 'Event strategies for craft breweries to build taproom loyalty',
                'icon_color' => 'amber',
            ],
            'wineries' => [
                'name' => 'Wineries',
                'slug' => 'for-wineries',
                'description' => 'Wine tastings, harvest festivals, and vineyard tours. Share your winemaking journey.',
                'blog_topic' => 'Creating memorable winery experiences through events and tastings',
                'icon_color' => 'rose',
            ],
            'cideries' => [
                'name' => 'Cideries',
                'slug' => 'for-cideries',
                'description' => 'Cider tastings, orchard events, and seasonal releases. Celebrate apple season year-round.',
                'blog_topic' => 'Building a cidery following through seasonal events and tastings',
                'icon_color' => 'red',
            ],
            'distilleries' => [
                'name' => 'Distilleries',
                'slug' => 'for-distilleries',
                'description' => 'Tasting rooms, cocktail classes, and distillery tours. Share your craft spirit story.',
                'blog_topic' => 'Event marketing strategies for craft distilleries',
                'icon_color' => 'orange',
            ],
            'meaderies' => [
                'name' => 'Meaderies',
                'slug' => 'for-meaderies',
                'description' => 'Mead tastings, Viking nights, and honey harvest events. Celebrate the oldest fermented drink.',
                'blog_topic' => 'Building a meadery community through themed events and tastings',
                'icon_color' => 'yellow',
            ],
            'brewpubs' => [
                'name' => 'Brewpubs',
                'slug' => 'for-brewpubs',
                'description' => 'Beer dinners, tap takeovers, and food pairings. Combine craft beer with great food.',
                'blog_topic' => 'Creating successful beer dinner and pairing events for brewpubs',
                'icon_color' => 'emerald',
            ],
        ],
    ],

    'art-galleries' => [
        'page' => 'for-art-galleries',
        'title' => 'Art Galleries',
        'sub_audiences' => [
            'contemporary-galleries' => [
                'name' => 'Contemporary Galleries',
                'slug' => 'for-contemporary-galleries',
                'description' => 'Opening receptions, artist talks, and exhibition tours. Engage with the art world.',
                'blog_topic' => 'Marketing contemporary art exhibitions and building collector relationships',
                'icon_color' => 'fuchsia',
            ],
            'photography-galleries' => [
                'name' => 'Photography Galleries',
                'slug' => 'for-photography-galleries',
                'description' => 'Photo exhibitions, portfolio reviews, and artist meet-and-greets. Celebrate visual storytelling.',
                'blog_topic' => 'Building photography gallery audiences through events and workshops',
                'icon_color' => 'slate',
            ],
            'craft-galleries' => [
                'name' => 'Craft & Artisan Galleries',
                'slug' => 'for-craft-galleries',
                'description' => 'Maker markets, demonstration days, and workshop events. Support local artisans.',
                'blog_topic' => 'Creating successful artisan markets and maker events',
                'icon_color' => 'amber',
            ],
            'pop-up-galleries' => [
                'name' => 'Pop-Up Galleries',
                'slug' => 'for-pop-up-galleries',
                'description' => 'Temporary exhibitions, art walks, and surprise shows. Create urgency and buzz.',
                'blog_topic' => 'Marketing pop-up art exhibitions and creating FOMO-driven attendance',
                'icon_color' => 'pink',
            ],
            'cooperative-galleries' => [
                'name' => 'Artist Cooperatives',
                'slug' => 'for-artist-cooperatives',
                'description' => 'Member exhibitions, group shows, and community events. Collective strength.',
                'blog_topic' => 'Building community through artist cooperative events and exhibitions',
                'icon_color' => 'teal',
            ],
            'museum-galleries' => [
                'name' => 'Museum Galleries',
                'slug' => 'for-museum-galleries',
                'description' => 'Special exhibitions, member events, and educational programming. Enrich your community.',
                'blog_topic' => 'Event strategies for museum galleries to increase membership and visits',
                'icon_color' => 'indigo',
            ],
        ],
    ],

    'community-centers' => [
        'page' => 'for-community-centers',
        'title' => 'Community Centers',
        'sub_audiences' => [
            'recreation-centers' => [
                'name' => 'Recreation Centers',
                'slug' => 'for-recreation-centers',
                'description' => 'Sports leagues, fitness classes, and youth programs. Keep your community active.',
                'blog_topic' => 'Managing diverse programming for recreation centers',
                'icon_color' => 'green',
            ],
            'senior-centers' => [
                'name' => 'Senior Centers',
                'slug' => 'for-senior-centers',
                'description' => 'Social events, health programs, and lifelong learning. Serve your senior community.',
                'blog_topic' => 'Event programming that engages and serves senior communities',
                'icon_color' => 'purple',
            ],
            'youth-centers' => [
                'name' => 'Youth Centers',
                'slug' => 'for-youth-centers',
                'description' => 'After-school programs, teen nights, and summer camps. Empower the next generation.',
                'blog_topic' => 'Creating engaging youth center programming that attracts teens',
                'icon_color' => 'cyan',
            ],
            'cultural-centers' => [
                'name' => 'Cultural Centers',
                'slug' => 'for-cultural-centers',
                'description' => 'Heritage celebrations, language classes, and cultural festivals. Preserve and share traditions.',
                'blog_topic' => 'Building cultural center attendance through heritage events',
                'icon_color' => 'rose',
            ],
            'neighborhood-houses' => [
                'name' => 'Neighborhood Houses',
                'slug' => 'for-neighborhood-houses',
                'description' => 'Community meetings, skill shares, and local gatherings. Be the heart of your neighborhood.',
                'blog_topic' => 'Turning neighborhood centers into community hubs through events',
                'icon_color' => 'amber',
            ],
            'faith-community-centers' => [
                'name' => 'Faith Community Centers',
                'slug' => 'for-faith-community-centers',
                'description' => 'Worship services, community outreach, and fellowship events. Serve your congregation.',
                'blog_topic' => 'Event management for faith-based community centers',
                'icon_color' => 'indigo',
            ],
        ],
    ],

    'circus-acrobatics' => [
        'page' => 'for-circus-acrobatics',
        'title' => 'Circus & Acrobatics',
        'sub_audiences' => [
            'aerial-artists' => [
                'name' => 'Aerial Artists',
                'slug' => 'for-aerialists',
                'description' => 'Silk performances, trapeze shows, and aerial yoga classes. Take your art to new heights.',
                'blog_topic' => 'Marketing aerial performances and building a following as an aerial artist',
                'icon_color' => 'fuchsia',
            ],
            'circus-troupes' => [
                'name' => 'Circus Troupes',
                'slug' => 'for-circus-troupes',
                'description' => 'Full circus productions, variety shows, and festival appearances. Coordinate your company.',
                'blog_topic' => 'Managing circus troupe schedules and building production audiences',
                'icon_color' => 'purple',
            ],
            'acrobats-contortionists' => [
                'name' => 'Acrobats & Contortionists',
                'slug' => 'for-contortionists',
                'description' => 'Solo acts, duo performances, and corporate entertainment. Bend the rules of possibility.',
                'blog_topic' => 'Building a career as an acrobat or contortionist through strategic bookings',
                'icon_color' => 'pink',
            ],
            'fire-performers' => [
                'name' => 'Fire Performers',
                'slug' => 'for-fire-performers',
                'description' => 'Fire dancing, poi spinning, and pyro shows. Light up the night safely.',
                'blog_topic' => 'Marketing fire performance acts and managing safety-conscious bookings',
                'icon_color' => 'orange',
            ],
            'jugglers-prop-artists' => [
                'name' => 'Jugglers & Prop Artists',
                'slug' => 'for-jugglers-prop-artists',
                'description' => 'List your juggling, poi, and object manipulation shows and workshops.',
                'blog_topic' => 'Building a following as a juggler or prop artist through strategic bookings',
                'icon_color' => 'amber',
            ],
            'stilt-walkers' => [
                'name' => 'Stilt Walkers',
                'slug' => 'for-stilt-walkers',
                'description' => 'Share your larger-than-life performances at parades, festivals, and corporate events.',
                'blog_topic' => 'Marketing stilt walking performances for parades, festivals, and corporate events',
                'icon_color' => 'amber',
            ],
        ],
    ],

    'magicians' => [
        'page' => 'for-magicians',
        'title' => 'Magicians',
        'sub_audiences' => [
            'close-up-magicians' => [
                'name' => 'Close-Up Magicians',
                'slug' => 'for-close-up-magicians',
                'description' => 'Table-hopping, corporate events, and private parties. Intimate magic that amazes.',
                'blog_topic' => 'Marketing close-up magic for corporate events and private parties',
                'icon_color' => 'purple',
            ],
            'stage-illusionists' => [
                'name' => 'Stage Illusionists',
                'slug' => 'for-stage-illusionists',
                'description' => 'Grand illusions, theater shows, and spectacular productions. Dream big, perform bigger.',
                'blog_topic' => 'Building audiences for stage illusion shows and theatrical magic',
                'icon_color' => 'violet',
            ],
            'mentalists' => [
                'name' => 'Mentalists',
                'slug' => 'for-mentalists',
                'description' => 'Mind reading shows, corporate entertainment, and psychological illusions. Read their minds.',
                'blog_topic' => 'Marketing mentalism shows and building a mysterious persona',
                'icon_color' => 'indigo',
            ],
            'childrens-magicians' => [
                'name' => 'Children\'s Magicians',
                'slug' => 'for-childrens-magicians',
                'description' => 'Birthday parties, school shows, and family entertainment. Create magical memories.',
                'blog_topic' => 'Building a children\'s magic business through parties and school shows',
                'icon_color' => 'cyan',
            ],
            'escape-artists' => [
                'name' => 'Escape Artists',
                'slug' => 'for-escape-artists',
                'description' => 'Escape performances, stunt shows, and public challenges. Thrill your audiences.',
                'blog_topic' => 'Marketing escape artist performances and building public anticipation',
                'icon_color' => 'orange',
            ],
            'comedy-magicians' => [
                'name' => 'Comedy Magicians',
                'slug' => 'for-comedy-magicians',
                'description' => 'Magic and laughs combined. Corporate shows, comedy clubs, and variety nights.',
                'blog_topic' => 'Blending comedy and magic for maximum entertainment value',
                'icon_color' => 'amber',
            ],
        ],
    ],

    'spoken-word' => [
        'page' => 'for-spoken-word',
        'title' => 'Spoken Word',
        'sub_audiences' => [
            'slam-poets' => [
                'name' => 'Slam Poets',
                'slug' => 'for-slam-poets',
                'description' => 'Slam competitions, open mics, and team bouts. Compete with your words.',
                'blog_topic' => 'Building a slam poetry career through competitions and performances',
                'icon_color' => 'rose',
            ],
            'storytellers' => [
                'name' => 'Storytellers',
                'slug' => 'for-spoken-word-artists',
                'description' => 'Moth-style shows, personal narratives, and story slams. Share your truth.',
                'blog_topic' => 'Marketing storytelling performances and building audience connections',
                'icon_color' => 'amber',
            ],
            'literary-readers' => [
                'name' => 'Literary Readers',
                'slug' => 'for-page-poets',
                'description' => 'Book readings, author events, and literary salons. Bring literature to life.',
                'blog_topic' => 'Promoting author readings and literary events',
                'icon_color' => 'emerald',
            ],
            'literary-curators' => [
                'name' => 'Literary Curators',
                'slug' => 'for-literary-curators',
                'description' => 'Organizing reading series, festivals, salon events. Aggregate your programming in one place.',
                'blog_topic' => 'Building successful literary reading series and salon events',
                'icon_color' => 'indigo',
            ],
            'workshop-leaders' => [
                'name' => 'Workshop Leaders',
                'slug' => 'for-poetry-workshop-leaders',
                'description' => 'Teaching craft, generative writing, masterclasses. Fill your workshops and manage enrollment.',
                'blog_topic' => 'Growing poetry and writing workshop enrollment through strategic promotion',
                'icon_color' => 'emerald',
            ],
            'spoken-word-hosts' => [
                'name' => 'Hosts & Emcees',
                'slug' => 'for-poetry-open-mic-hosts',
                'description' => 'Host open mics, curate lineups, and build your scene. Lead your community.',
                'blog_topic' => 'Building a reputation as a spoken word host and community leader',
                'icon_color' => 'cyan',
            ],
        ],
    ],

    'dance-groups' => [
        'page' => 'for-dance-groups',
        'title' => 'Dance Groups',
        'sub_audiences' => [
            'contemporary-dance' => [
                'name' => 'Contemporary Dance Companies',
                'slug' => 'for-contemporary-modern-dance',
                'description' => 'Performances, workshops, and repertory shows. Push the boundaries of movement.',
                'blog_topic' => 'Marketing contemporary dance performances and building company audiences',
                'icon_color' => 'fuchsia',
            ],
            'ballet-companies' => [
                'name' => 'Ballet Companies',
                'slug' => 'for-ballet-companies',
                'description' => 'Classical productions, Nutcracker seasons, and new works. Honor tradition while innovating.',
                'blog_topic' => 'Building ballet company audiences from Nutcracker to contemporary works',
                'icon_color' => 'pink',
            ],
            'hip-hop-dance' => [
                'name' => 'Hip-Hop Dance Crews',
                'slug' => 'for-hip-hop-crews',
                'description' => 'Battles, showcases, and competition prep. Bring the energy and win.',
                'blog_topic' => 'Building a hip-hop dance crew following through battles and showcases',
                'icon_color' => 'violet',
            ],
            'folk-cultural-dance' => [
                'name' => 'Folk & Cultural Dance Groups',
                'slug' => 'for-folk-cultural-dance',
                'description' => 'Cultural festivals, heritage celebrations, and community performances. Share your traditions.',
                'blog_topic' => 'Promoting folk and cultural dance through festivals and community events',
                'icon_color' => 'orange',
            ],
            'ballroom-latin-dance' => [
                'name' => 'Ballroom & Latin Dance',
                'slug' => 'for-ballroom-latin-studios',
                'description' => 'Social dances, competitions, and showcase events. Partner up and shine.',
                'blog_topic' => 'Growing ballroom and Latin dance events from socials to competitions',
                'icon_color' => 'rose',
            ],
            'dance-fitness-wellness' => [
                'name' => 'Dance Fitness & Wellness',
                'slug' => 'for-dance-fitness-wellness',
                'description' => 'Zumba, Barre, dance cardio, movement therapy. Class schedules that keep your community moving.',
                'blog_topic' => 'Building a loyal following for dance fitness and wellness classes',
                'icon_color' => 'pink',
            ],
        ],
    ],

    'theater-performers' => [
        'page' => 'for-theater-performers',
        'title' => 'Theater Performers',
        'sub_audiences' => [
            'musical-theater' => [
                'name' => 'Musical Theater Performers',
                'slug' => 'for-musical-theater-performers',
                'description' => 'Auditions, callbacks, and show schedules. Triple-threat your way to success.',
                'blog_topic' => 'Managing auditions and building a musical theater career',
                'icon_color' => 'fuchsia',
            ],
            'dramatic-actors' => [
                'name' => 'Dramatic Actors',
                'slug' => 'for-drama-actors',
                'description' => 'Stage plays, classical works, and new productions. Master your craft.',
                'blog_topic' => 'Building a dramatic acting career through strategic role selection',
                'icon_color' => 'purple',
            ],
            'community-theater' => [
                'name' => 'Community Theater',
                'slug' => 'for-community-theater-performers',
                'description' => 'Local productions, volunteer casts, neighborhood playhouses. Theater for everyone.',
                'blog_topic' => 'Building a community theater career and engaging local audiences',
                'icon_color' => 'emerald',
            ],
            'improv-sketch' => [
                'name' => 'Improv & Sketch',
                'slug' => 'for-improv-sketch-performers',
                'description' => 'Comedy troupes, improv nights, sketch shows, variety acts. Yes, and...',
                'blog_topic' => 'Growing an improv and sketch comedy performance career',
                'icon_color' => 'violet',
            ],
            'experimental-fringe' => [
                'name' => 'Experimental & Fringe',
                'slug' => 'for-experimental-fringe-theater',
                'description' => 'Avant-garde, site-specific, immersive theater, devised work. Pushing boundaries.',
                'blog_topic' => 'Marketing experimental and fringe theater to adventurous audiences',
                'icon_color' => 'cyan',
            ],
            'childrens-youth-theater' => [
                'name' => 'Children\'s & Youth Theater',
                'slug' => 'for-childrens-youth-theater',
                'description' => 'Family-friendly shows, school productions, youth ensembles. Inspiring the next generation.',
                'blog_topic' => 'Building audiences for children\'s and youth theater productions',
                'icon_color' => 'pink',
            ],
        ],
    ],

    'food-trucks-vendors' => [
        'page' => 'for-food-trucks-and-vendors',
        'title' => 'Food Trucks & Vendors',
        'sub_audiences' => [
            'food-trucks' => [
                'name' => 'Food Trucks',
                'slug' => 'for-food-trucks',
                'description' => 'Daily locations, festival appearances, and private events. Let customers find you.',
                'blog_topic' => 'Using scheduling to help food truck customers find your daily locations',
                'icon_color' => 'orange',
            ],
            'farmers-market-vendors' => [
                'name' => 'Farmers Market Vendors',
                'slug' => 'for-market-vendors',
                'description' => 'Weekly market schedules, seasonal products, and special offerings. Connect with your community.',
                'blog_topic' => 'Building customer loyalty as a farmers market vendor',
                'icon_color' => 'emerald',
            ],
            'pop-up-restaurants' => [
                'name' => 'Pop-Up Restaurants',
                'slug' => 'for-popup-kitchens',
                'description' => 'Temporary kitchens, guest chef nights, and surprise locations. Create dining adventures.',
                'blog_topic' => 'Marketing pop-up restaurant events for maximum attendance',
                'icon_color' => 'rose',
            ],
            'caterers' => [
                'name' => 'Caterers',
                'slug' => 'for-mobile-catering-businesses',
                'description' => 'Tastings, open houses, and showcase events. Win new clients with your food.',
                'blog_topic' => 'Using tasting events to grow a catering business',
                'icon_color' => 'amber',
            ],
            'coffee-beverage-carts' => [
                'name' => 'Coffee & Beverage Carts',
                'slug' => 'for-coffee-beverage-carts',
                'description' => 'Mobile espresso, smoothies, juice bars - let caffeine seekers find their morning fix.',
                'blog_topic' => 'Building a following for mobile coffee and beverage carts',
                'icon_color' => 'amber',
            ],
            'bbq-smoker-trucks' => [
                'name' => 'BBQ & Smoker Trucks',
                'slug' => 'for-bbq-smoker-trucks',
                'description' => 'Low and slow on the go. Share when the brisket\'s ready and where fans can find it.',
                'blog_topic' => 'Marketing BBQ and smoker truck locations to hungry fans',
                'icon_color' => 'red',
            ],
        ],
    ],
];
