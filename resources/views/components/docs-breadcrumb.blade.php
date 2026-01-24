@props(['currentTitle'])

<nav aria-label="Breadcrumb" class="flex items-center gap-2 text-sm mb-6">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Documentation",
                "item": "{{ route('marketing.docs') }}"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "{{ $currentTitle }}"
            }
        ]
    }
    </script>
    <a href="{{ route('marketing.docs') }}" class="text-gray-400 hover:text-white transition-colors">Docs</a>
    <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-white">{{ $currentTitle }}</span>
</nav>
