@props(['currentTitle', 'section' => null, 'sectionTitle' => null, 'sectionRoute' => null])

<nav aria-label="Breadcrumb" class="flex items-center gap-2 text-sm mb-6">
    {{-- Built as a payload and emitted through SeoUtils::jsonLd(), the same way
         layouts/marketing.blade.php does it. Hand-written quotes around a {{ }} do NOT work here:
         Blade's {{ }} is e(), i.e. HTML escaping, and the contents of an ld+json element is RAW
         TEXT that nothing HTML-decodes - so config/docs.php's "Subscriptions & Passes" reached
         Google's breadcrumb as the literal "Subscriptions &amp; Passes", and a title carrying a
         quote or </script> would have broken the block outright. This component was the one place
         the "escape JSON-LD through one helper" pass missed, and it matters more than the others
         because the layout now SKIPS its own BreadcrumbList for every /docs/ page and defers to
         this - so there is no correct copy alongside it. --}}
    @php
        $docsCrumbs = [[
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Documentation',
            'item' => route('marketing.docs'),
        ]];

        if ($section) {
            $docsCrumbs[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $sectionTitle,
                'item' => route($sectionRoute),
            ];
        }

        $docsCrumbs[] = [
            '@type' => 'ListItem',
            'position' => count($docsCrumbs) + 1,
            'name' => $currentTitle,
        ];
    @endphp
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {!! \App\Utils\SeoUtils::jsonLd([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $docsCrumbs,
    ]) !!}
    </script>
    <a href="{{ route('marketing.docs') }}" class="-my-1 py-1 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Docs</a>
    <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    @if($section)
        <a href="{{ route($sectionRoute) }}" class="-my-1 py-1 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">{{ $sectionTitle }}</a>
        <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    @endif
    <span class="text-gray-900 dark:text-white">{{ $currentTitle }}</span>
</nav>
