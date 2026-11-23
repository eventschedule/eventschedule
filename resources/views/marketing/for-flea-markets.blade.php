<x-marketing-layout>
    <x-slot name="title">Flea Market Vendor Map | Who Is Selling Today</x-slot>
    <x-slot name="description">A flea market vendor map: sellers claim a stall from one link and say which Sundays they come, and shoppers scan a QR code to see who is selling today.</x-slot>
    <x-slot name="breadcrumbTitle">For Flea Markets</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Archivo Black') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Space Grotesk') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Flea Markets"
        description="A stall map for a flea market whose sellers change from week to week: sellers claim their stall from one link, mark the weeks they miss, and shoppers see who is selling today."
        audience="Flea markets, car boot sales, swap meets and vintage markets"
        keywords="flea market vendor map, flea market stall map, car boot sale map, swap meet vendors, vintage market sellers, flea market layout" />
    </x-slot>

    @php
        $vma = [
            'signup' => '/sign_up?type=venue&vendor_map=flea_market',
            'cta' => 'Map your flea market',
            'eyebrow' => 'Flea market vendor map',
            'h1' => ['Different sellers every Sunday.', 'One map that knows who came.'],
            'lede' => 'A flea market never looks the same twice. Sellers claim a stall from one link and say which weeks they come, and shoppers scan a code at the gate to see who is selling today, and what.',
            'spot' => 'stall',
            'people' => 'sellers',
            'screensTitle' => 'This week\'s stalls, on your screen and theirs.',
            'screensLead' => 'Pick a date and you see who is coming, who is not, and which stalls are still empty. Shoppers see the same map for today, with the sellers\' names on it.',
            'shotsNote' => 'Shown on a farmers market. On a flea market every screen says stall and sellers.',
            'howTitle' => 'From an empty yard to a full one.',
            'steps' => [
                ['Mark out the stalls', 'Draw rows of stalls across the yard or the car park, number them the way you walk them, and add the gate, the toilets and the parking.'],
                ['Send the sellers one link', 'Regulars claim their usual stall for every market day. Occasional sellers choose the dates they are coming.'],
                ['Hang the sign at the gate', 'Shoppers scan it and see who is here today: the record dealer, the vintage rail, the man with the tools.'],
            ],
            'examplesKicker' => 'At the market',
            'examplesTitle' => 'What a flea market asks of a map.',
            'examplesLead' => 'Half the sellers are regulars and half come when they have stock. The map has to be right for this Sunday, not for the season.',
            'examples' => [
                ['Regulars and the occasional seller', 'A regular is approved for every market day. Someone clearing a loft asks for the dates they want, and only those.'],
                ['The empty stall at eight o\'clock', 'Somebody turns up with a van and a table. Add them to an empty stall for that day: a name and what they sell, no account needed.'],
                ['A seller who cannot make it', 'They tap Not coming for that Sunday. Their stall reads "Not here today", and the following week they are back on the map.'],
                ['Rained off', 'Cancel the day from the Vendor map tab. The date comes off your calendar and the map tells shoppers there is no market that day, and why.'],
                ['A night market as well', 'Run the evening market as its own schedule with its own map, so the Sunday sellers and the night sellers each see their own stalls.'],
                ['Shoppers hunting for one thing', 'Search by what is sold: records, furniture, clothes, tools. A seller with a page of their own is a tap away.'],
            ],
            'linkTitle' => 'Sellers keep their own stall up to date.',
            'linkLead' => 'Post one link in the sellers\' group. Each seller finds their stall, says what they sell and which weeks they come, and you approve them on the map.',
            'linkChecks' => [
                'They choose Every market day, or the dates they are coming.',
                'They tap what they sell (clothing, crafts, food and more) and add a line of their own.',
                'They confirm with a code sent to their email, or with Google. No password.',
                'Two sellers asking for the same stall are shown side by side, so you choose.',
                'The same link is their page afterwards: their dates, Not coming, and Stop coming, which frees the stall for someone else.',
            ],
            'dayTitle' => 'The yard on the morning, as it really is.',
            'dayLead' => 'Everything you change on a market day is for that day only, unless you choose From now on.',
            'dayCards' => [
                ['Move a seller', 'Two neighbours want to swap? Move one onto the other and they trade stalls.'],
                ['Who is missing', 'The tab counts who is coming, who is not and which stalls are empty, before the gate opens.'],
                ['The sign at the gate', 'Print it once. The code always opens today\'s map, whoever turned up.'],
            ],
            'faqTitle' => 'Flea market questions',
            'faqs' => [
                ['q' => 'How do I keep track of which sellers come each week?', 'a' => 'Each seller says it themselves. On the vendor link they choose every market day or the dates they are coming, and they tap Not coming when they will miss a week. The Vendor map tab shows any date you pick with who is coming, who is not and which stalls are empty.'],
                ['q' => 'What about sellers who just turn up?', 'a' => 'Tap an empty stall on that day and add them for that day only, with a name and what they sell. They need no account, and they are not on next week\'s map.'],
                ['q' => 'Do sellers need an account?', 'a' => 'A seller who claims a stall from the link confirms with a code sent to their email, or with Google, so only they can change their stall later. Nobody sets a password. Sellers you add yourself need no account.'],
                ['q' => 'What happens when the market is rained off?', 'a' => 'Press Cancel this day on the Vendor map tab and choose why. The date comes off your calendar, and the map tells shoppers there is no market that day and why.'],
                ['q' => 'Can I charge sellers for a pitch?', 'a' => 'Not through the vendor map: it places sellers, it does not charge them. If you take pitch fees online, a ticket type on the market\'s event can take them, which needs Pro, as any ticket with a price does.'],
                ['q' => 'Can I draw the map on my phone?', 'a' => 'Yes. The designer works on a phone: add a row of stalls, place single ones, number them in walking order and add the gate and the toilets, standing in the yard if you like.'],
                ['q' => 'What do shoppers need?', 'a' => 'A phone camera. They scan the code on the sign at the gate and see who is selling today, searchable by name or by what they sell. No app.'],
                ['q' => 'Which plan do I need?', 'a' => 'Drawing the map, adding sellers yourself, Not coming, cancelling a day, the shoppers\' map and the sign are free on every plan. The vendor link, where sellers claim their own stall, and drafting the map from photos are part of Pro.'],
            ],
            'closing' => ['Know who is coming before the gate opens.', 'Mark out the stalls once, send the sellers one link, and let shoppers see today\'s market from the gate.'],
            'fonts' => ["'Archivo Black'", "'Space Grotesk'"],
            'light' => ['bg' => '#f6f7f4', 'bg-2' => '#eceee7', 'card' => '#ffffff', 'line' => 'rgba(20, 40, 35, 0.14)', 'ink' => '#141f1c', 'muted' => '#46534f', 'accent' => '#0f6b62', 'button' => '#0f6b62', 'glow' => 'rgba(45, 212, 191, 0.2)', 'tint' => 'rgba(15, 107, 98, 0.12)', 'band-1' => '#0b2a27', 'band-2' => '#0f4a43'],
            'dark' => ['bg' => '#0d1312', 'bg-2' => '#131b19', 'card' => '#18221f', 'line' => 'rgba(210, 240, 232, 0.12)', 'ink' => '#eaf3f0', 'muted' => '#b3c4bf', 'accent' => '#5eead4', 'button' => '#0f766e', 'glow' => 'rgba(45, 212, 191, 0.12)', 'tint' => 'rgba(94, 234, 212, 0.14)', 'band-1' => '#071d1b', 'band-2' => '#0c3c36'],
        ];
    @endphp

    @include('marketing.partials.vendor-map-audience', ['vma' => $vma])
</x-marketing-layout>
