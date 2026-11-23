<x-marketing-layout>
    <x-slot name="title">Craft Fair Vendor Map | Tables Makers Claim Themselves</x-slot>
    <x-slot name="description">A craft fair vendor map: draw your hall's tables or draft them from photos, send makers one link to claim their table, and give shoppers a map at the door.</x-slot>
    <x-slot name="breadcrumbTitle">For Craft Fairs</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Fraunces') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Karla') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Craft Fairs"
        description="A map of a craft fair's tables that makers claim from one link, with what each one makes, and a QR map for shoppers at the door."
        audience="Craft fairs, makers markets and art fairs"
        keywords="craft fair vendor map, craft fair layout, makers market map, craft fair table map, craft fair floor plan, artisan market vendors" />
    </x-slot>

    @php
        $vma = [
            'signup' => '/sign_up?type=venue&vendor_map=craft_fair',
            'cta' => 'Map your craft fair',
            'eyebrow' => 'Craft fair vendor map',
            'h1' => ['Sixty makers, one hall,', 'and a map everyone can read.'],
            'lede' => 'Every table on one map, with the maker\'s name and what they make. Makers claim their own table from a link in the group chat, and shoppers scan a code at the door to find the woodturner they met last year.',
            'spot' => 'table',
            'people' => 'makers',
            'screensTitle' => 'The plan of the hall, on your screen and theirs.',
            'screensLead' => 'You see every table and who is on it on each day of the fair. Shoppers see the same plan on their phone, with the makers\' names and what they make.',
            'shotsNote' => 'Shown on a farmers market. On a craft fair every screen says table and makers instead.',
            'howTitle' => 'From the floor plan to the first visitor.',
            'steps' => [
                ['Draw the hall, or photograph it', 'Add rows of tables down the hall and single tables where the layout breaks, or upload the floor plan you already send makers and let AI draft it.'],
                ['Send the makers one link', 'Each maker finds their own table, adds their name at the fair, what they make and a photo of their work. You approve them on the map.'],
                ['Hang the sign at the door', 'Print the entrance sign. Visitors scan it and see every maker, by table and by craft.'],
            ],
            'examplesKicker' => 'At the fair',
            'examplesTitle' => 'What a craft fair asks of a map.',
            'examplesLead' => 'A fair runs a day or two, a few times a year, with makers who know each other and visitors who come back for one stand.',
            'examples' => [
                ['A Christmas fair over two days', 'Set the fair as one event with its two dates. A maker who only does Saturday chooses Saturday, and their table is free for someone else on Sunday.'],
                ['The jeweller who always has table 4', 'Approve a regular for every date and they are on table 4 at every fair, without asking again each time.'],
                ['A waiting list for the hall', 'Turn the link off when the hall is full. Turn it on again when the next fair opens for applications.'],
                ['Workshops beside the stalls', 'Put a wreath or pottery workshop on the same schedule as an event of its own, with free sign-ups, or paid places on Pro.'],
                ['A new maker for one day', 'A guest from another town? Add them to an empty table for that day only, with a name and what they make, no account needed.'],
                ['Shoppers looking for a craft', 'Visitors search by what is made: ceramics, prints, candles, knitwear. Each maker with a page of their own is a tap away.'],
            ],
            'linkTitle' => 'Makers claim their own table.',
            'linkLead' => 'Post one link in the makers\' group. Each maker finds the table with their name on it, or a free one, and says what they make.',
            'linkChecks' => [
                'They choose Every day of the fair, or the dates they are coming.',
                'They add their name at the fair, what they make, and a photo of their work if they like.',
                'They confirm with a code sent to their email, or with Google. No password.',
                'You approve them on the map, or approve every request nobody else wants in one press.',
                'Afterwards the same link is their page: their dates, Not coming, and Share where to find me for their own followers.',
            ],
            'dayTitle' => 'The fair changes on the morning. The map keeps up.',
            'dayLead' => 'Pick the day on the Vendor map tab and every change you make is for that day only, unless you say otherwise.',
            'dayCards' => [
                ['Move a maker', 'Better light by the window? Move them for this day only, or from now on.'],
                ['Not coming', 'A maker who is ill taps Not coming on their own page. Their table reads "Not here today".'],
                ['Cancel a day', 'Snowed in? Cancel the day and the map tells visitors the fair is closed, and why.'],
            ],
            'faqTitle' => 'Craft fair questions',
            'faqs' => [
                ['q' => 'How do I lay out a craft fair?', 'a' => 'Start from the room: where people come in, where they leave, and the power points. Put tables in rows people can walk both sides of, keep the busiest makers away from the door so the entrance does not jam, and give food and drink their own corner. On the vendor map you draw it with rows of tables and single tables where the layout breaks, then number them in the order people walk past.'],
                ['q' => 'Can makers choose their own table?', 'a' => 'They ask for one. Each maker taps the table they want on the vendor link, and the request waits for you on the map. Two makers asking for the same table are shown side by side, so you choose, and you can move either of them before you approve.'],
                ['q' => 'Do makers need an account?', 'a' => 'A maker who claims a table from the link confirms with a code sent to their email, or with Google, so only they can change it later. Nobody sets a password. Makers you add yourself need no account at all.'],
                ['q' => 'Can I use the floor plan I already have?', 'a' => 'Yes, on Pro. Upload the plan you send makers, as a PDF or a photo, and AI drafts the map from it: the tables, their numbers and any names on it. A printed plan gives a near-final map, and you check it before saving.'],
                ['q' => 'Does it work for a fair that runs over a weekend?', 'a' => 'Yes. The map follows the event\'s dates, and each maker says which days they come. A table can belong to one maker on Saturday and another on Sunday.'],
                ['q' => 'Can I take table fees online?', 'a' => 'Not through the vendor map: it places makers, it does not charge them. If you charge for tables, a ticket type on the fair\'s event can take the fee, which needs Pro, as any ticket with a price does.'],
                ['q' => 'What do visitors need?', 'a' => 'A phone camera. They scan the code on the entrance sign and see every maker at the fair that day, searchable by name or by what they make. No app.'],
                ['q' => 'Which plan do I need?', 'a' => 'Drawing the map, adding makers yourself, the visitors\' map and the entrance sign are free on every plan. The vendor link, where makers claim their own table, and drafting the map from photos or a plan are part of Pro.'],
            ],
            'closing' => ['Put the whole hall on one map.', 'Draw it once, send the makers one link, and hang the sign at the door.'],
            'fonts' => ["'Fraunces'", "'Karla'"],
            'light' => ['bg' => '#fbf7f2', 'bg-2' => '#f3ebe1', 'card' => '#ffffff', 'line' => 'rgba(76, 45, 25, 0.14)', 'ink' => '#2a1a10', 'muted' => '#5f4a3c', 'accent' => '#a23a17', 'button' => '#a23a17', 'glow' => 'rgba(234, 140, 85, 0.22)', 'tint' => 'rgba(162, 58, 23, 0.12)', 'band-1' => '#3b1d10', 'band-2' => '#6b2c14'],
            'dark' => ['bg' => '#16100c', 'bg-2' => '#1f1711', 'card' => '#251c15', 'line' => 'rgba(255, 228, 206, 0.12)', 'ink' => '#f7ece3', 'muted' => '#cdb8a8', 'accent' => '#f4a27c', 'button' => '#b4441f', 'glow' => 'rgba(234, 140, 85, 0.14)', 'tint' => 'rgba(244, 162, 124, 0.16)', 'band-1' => '#2b150b', 'band-2' => '#5a2511'],
        ];
    @endphp

    @include('marketing.partials.vendor-map-audience', ['vma' => $vma])
</x-marketing-layout>
