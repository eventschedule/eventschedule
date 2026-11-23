<x-marketing-layout>
    <x-slot name="title">Expo Floor Plan and Booth Map | Exhibitors Claim Booths</x-slot>
    <x-slot name="description">An expo floor plan exhibitors find their booth on from one link, and a searchable booth map visitors open from a QR code at the door. Free to draw.</x-slot>
    <x-slot name="breadcrumbTitle">For Conferences & Expos</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Sora') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('IBM Plex Sans') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Conferences and Expos"
        description="A booth map for a conference, trade show or expo: exhibitors claim their booth from one link and say what they show, and visitors search the floor plan by company from a QR code."
        audience="Conferences, trade shows, expos and career fairs"
        keywords="expo floor plan, exhibitor map, booth map, trade show floor plan, conference exhibitor list, career fair booth map" />
    </x-slot>

    @php
        $vma = [
            'signup' => '/sign_up?type=venue&vendor_map=expo',
            'cta' => 'Map your expo',
            'eyebrow' => 'Expo floor plan with a booth map',
            'h1' => ['Every booth on the floor plan,', 'and every exhibitor findable.'],
            'lede' => 'Draw the hall or upload the floor plan you already have. Exhibitors claim their own booth from one link and say what they show, and visitors scan a code at the door to find a company by name.',
            'spot' => 'booth',
            'people' => 'exhibitors',
            'screensTitle' => 'The floor plan, for you and for the visitors.',
            'screensLead' => 'You see every booth, who has claimed it and what is waiting for your answer. Visitors see the same plan on their phone and search it by company or by what they show.',
            'shotsNote' => 'Shown on a farmers market. On an expo every screen says booth and exhibitors, and sorts them by what they show.',
            'howTitle' => 'From the floor plan to the doors opening.',
            'steps' => [
                ['Upload the floor plan', 'The PDF from the venue gives a near-final map: AI reads the booths, their numbers and any names on it, and you check them. Or draw the rows yourself.'],
                ['Send exhibitors one link', 'Each exhibitor finds their booth, adds the company name and what they show, and you approve them on the map.'],
                ['Put the code at the door', 'Print the entrance sign, or put the map on your event website. Visitors search the hall by company or by topic.'],
            ],
            'examplesKicker' => 'On the floor',
            'examplesTitle' => 'What an expo asks of a map.',
            'examplesLead' => 'A trade show is a few days, a few hundred booths and a list of exhibitors that keeps changing until the doors open.',
            'examples' => [
                ['A three-day trade show', 'Set the expo as one event with its first and last day. An exhibitor who is there for one day chooses that day, and the booth shows as theirs only then.'],
                ['Sorted by what they show', 'Exhibitors tap what they show (technology, services, food and drink, health, education, design, nonprofit), and visitors filter the hall by it.'],
                ['Halls and zones', 'Group booths into areas, each with its own colour: Hall A, the start-up zone, the food court.'],
                ['A booth changes hands', 'Move an exhibitor to another booth for one day, or from now on. The next time they open their link they see the new number.'],
                ['A career fair', 'Employers claim a booth and say what they do. Students scan the code at the door and look the companies up by name.'],
                ['Talks beside the booths', 'Put the programme on the same schedule as events of their own, with free registration, or paid tickets on Pro.'],
            ],
            'linkTitle' => 'Exhibitors claim their own booth.',
            'linkLead' => 'Send one link to every exhibitor. Each finds the booth with their company on it, or a free one, and keeps it up to date until the doors open.',
            'linkChecks' => [
                'They choose Every day of the expo, or the days they are exhibiting.',
                'They add the company name, what they show and a logo or photo if they like.',
                'They confirm with a code sent to their email, or with Google. No password.',
                'You approve each booth on the map, or every request nobody else wants in one press.',
                'Afterwards the same link is their page: their days, Not coming, and Share where to find me for their own followers.',
            ],
            'dayTitle' => 'The plan you printed last week, kept true on the day.',
            'dayLead' => 'Pick a day of the expo on the Vendor map tab and change it without touching the others.',
            'dayCards' => [
                ['Move an exhibitor', 'Swap two booths, or move one for this day only.'],
                ['A booth left empty', 'An exhibitor who cannot make it marks Not coming. Visitors see "Not here today" on that booth.'],
                ['The sign at the door', 'A4, or a 300 dpi image for the print shop. The code opens the map with "You are here" where the sign hangs.'],
            ],
            'faqTitle' => 'Conference and expo questions',
            'faqs' => [
                ['q' => 'Can I use the floor plan the venue gave me?', 'a' => 'Yes, on Pro. Upload it as a PDF or a photo and AI drafts the map from it: the booths, their numbers and any company names printed on them. A printed floor plan gives a near-final map, and you check the names before saving. You can also draw the floor plan yourself on any plan.'],
                ['q' => 'How do exhibitors find their booth?', 'a' => 'You send one link. Each exhibitor sees the booths, with any names already on them, finds theirs by company name or booth number, and says what they show. The request waits for you on the map.'],
                ['q' => 'Do exhibitors need an account?', 'a' => 'An exhibitor who claims a booth from the link confirms with a code sent to their email, or with Google. Nobody sets a password. Exhibitors you add yourself need no account.'],
                ['q' => 'Can visitors search the exhibitors?', 'a' => 'Yes. The map has a search box for a company\'s name or what it shows, and buttons for each category. A booth with an exhibitor who has a page of their own links to it.'],
                ['q' => 'Can I put the floor plan on our event website?', 'a' => 'Yes. The Vendor map tab gives you a short snippet to paste into your own site, and it shows the same live floor plan as the QR code.'],
                ['q' => 'Can I charge for booths?', 'a' => 'Not through the vendor map: it places exhibitors, it does not invoice them. A ticket type on the expo\'s event can take a booth fee online, which needs Pro, as any ticket with a price does.'],
                ['q' => 'Does it sell visitor tickets?', 'a' => 'The map does not, but the expo\'s event can: free registration on every plan, paid tickets on Pro, and QR check-in at the door.'],
                ['q' => 'Which plan do I need?', 'a' => 'Drawing the floor plan, adding exhibitors yourself, the visitors\' map, the embed and the entrance sign are free on every plan. The vendor link, where exhibitors claim their own booth, and drafting the map from a floor plan or photos are part of Pro.'],
            ],
            'closing' => ['Put every booth where visitors can find it.', 'Upload the floor plan, send exhibitors one link, and put the code at the door.'],
            'fonts' => ["'Sora'", "'IBM Plex Sans'"],
            'light' => ['bg' => '#f5f8fc', 'bg-2' => '#e9eff7', 'card' => '#ffffff', 'line' => 'rgba(15, 35, 70, 0.13)', 'ink' => '#0d1b2e', 'muted' => '#45556c', 'accent' => '#0b5cad', 'button' => '#0b5cad', 'glow' => 'rgba(56, 152, 236, 0.22)', 'tint' => 'rgba(11, 92, 173, 0.12)', 'band-1' => '#0a1a33', 'band-2' => '#0d3566'],
            'dark' => ['bg' => '#0a0f18', 'bg-2' => '#0f1622', 'card' => '#141d2b', 'line' => 'rgba(210, 225, 250, 0.12)', 'ink' => '#e8eef8', 'muted' => '#aebbd0', 'accent' => '#7cc0ff', 'button' => '#0b5cad', 'glow' => 'rgba(56, 152, 236, 0.14)', 'tint' => 'rgba(124, 192, 255, 0.14)', 'band-1' => '#06101f', 'band-2' => '#0b2b55'],
        ];
    @endphp

    @include('marketing.partials.vendor-map-audience', ['vma' => $vma])
</x-marketing-layout>
